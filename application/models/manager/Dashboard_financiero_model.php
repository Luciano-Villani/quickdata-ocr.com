<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Dashboard_financiero_model extends CI_Model
{
    private $anio_minimo = 2024;
    private $anio_maximo = 2050;

    private $dimensiones = array(
        'origen' => 'origen',
        'proveedor' => 'proveedor',
        'secretaria' => 'secretaria',
        'programa' => 'programa',
        'proyecto' => 'proyecto',
        'dependencia' => 'dependencia',
        'cuenta' => 'nro_cuenta',
        'factura' => 'nro_factura',
        'objeto' => 'objeto',
        'unidad_medida' => 'unidad_medida',
    );

    private $drilldown = array(
        'secretaria',
        'programa',
        'proyecto',
        'dependencia',
        'cuenta',
        'factura',
        'proveedor',
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_anios()
    {
        $sql = "
            SELECT anio_fc
            FROM (" . $this->universo_sql() . ") U
            GROUP BY anio_fc
            ORDER BY anio_fc DESC
        ";

        $anios = array();
        foreach ($this->db->query($sql)->result() as $row) {
            $anios[(int) $row->anio_fc] = (string) $row->anio_fc;
        }

        return $anios;
    }

    public function get_proveedores($filtros = array())
    {
        return $this->get_opciones_dimension('proveedor', $filtros, 'Todos los proveedores');
    }

    public function get_origenes($filtros = array())
    {
        return $this->get_opciones_dimension('origen', $filtros, 'Todo MVL');
    }

    public function get_tipo_pago()
    {
        return array('' => 'Debito y OP');
    }

    public function get_dashboard($filtros)
    {
        return array(
            'kpis' => $this->get_kpis($filtros),
            'evolucion_mensual' => $this->get_evolucion_mensual($filtros),
            'top_proveedores' => $this->get_ranking('proveedor', $filtros, 10),
            'por_tipo_pago' => $this->get_ranking('origen', $filtros, 5),
            'por_secretaria' => $this->get_ranking('secretaria', $filtros, 12),
            'resumen' => $this->get_resumen($filtros),
        );
    }

    public function get_api_resumen($filtros)
    {
        $filtros_actual = $this->filtros_resumen_con_corte($filtros);
        $actual = $this->get_kpis($filtros_actual);

        $filtros_mes_anterior = $this->filtros_mes_anterior($filtros_actual);
        $mes_anterior = $filtros_mes_anterior ? $this->get_kpis($filtros_mes_anterior) : $this->kpis_vacios();

        $filtros_anio_anterior = $this->filtros_anio_anterior($filtros_actual);
        $anio_anterior = $filtros_anio_anterior ? $this->get_kpis($filtros_anio_anterior) : $this->kpis_vacios();

        return array(
            'actual' => $actual,
            'comparativas' => array(
                'mes_anterior' => $this->comparar_totales($actual, $mes_anterior),
                'anio_anterior' => $this->comparar_totales($actual, $anio_anterior),
            ),
            'por_origen' => $this->get_ranking('origen', $filtros_actual, 10),
            'corte' => array(
                'mes' => !empty($filtros_actual['mes']) ? (int) $filtros_actual['mes'] : null,
                'mes_desde' => !empty($filtros_actual['mes_desde']) ? (int) $filtros_actual['mes_desde'] : null,
                'mes_hasta' => !empty($filtros_actual['mes_hasta']) ? (int) $filtros_actual['mes_hasta'] : null,
            ) + $this->info_corte_ytd_representativo($filtros),
        );
    }

    public function get_kpis($filtros)
    {
        $filtros = $this->normalizar_filtros($filtros);
        $sql = "
            SELECT
                COUNT(*) AS facturas,
                SUM(importe) AS total,
                AVG(importe) AS promedio,
                COUNT(DISTINCT proveedor) AS proveedores,
                COUNT(DISTINCT secretaria) AS secretarias
            FROM (" . $this->universo_sql() . ") U
            " . $this->where_sql($filtros);

        $row = $this->db->query($sql)->row_array();

        return array(
            'facturas' => (int) ($row['facturas'] ?? 0),
            'total' => (float) ($row['total'] ?? 0),
            'promedio' => (float) ($row['promedio'] ?? 0),
            'proveedores' => (int) ($row['proveedores'] ?? 0),
            'secretarias' => (int) ($row['secretarias'] ?? 0),
        );
    }

    public function get_evolucion_mensual($filtros)
    {
        $filtros = $this->normalizar_filtros($filtros);
        $sql = "
            SELECT
                mes_fc AS mes,
                SUM(importe) AS total,
                COUNT(*) AS facturas
            FROM (" . $this->universo_sql() . ") U
            " . $this->where_sql($filtros) . "
            GROUP BY mes_fc
            ORDER BY mes_fc ASC
        ";

        return $this->db->query($sql)->result_array();
    }

    public function get_evolucion_comparativa($filtros)
    {
        $filtros = $this->normalizar_filtros($filtros);
        $anio = !empty($filtros['anio']) ? (int) $filtros['anio'] : (int) date('Y');

        $actual = $filtros;
        $actual['anio'] = $anio;

        $anterior = $filtros;
        $anterior['anio'] = $anio - 1;

        return array(
            'anio_actual' => $anio,
            'anio_anterior' => $anio - 1,
            'actual' => $this->get_evolucion_mensual($actual),
            'anterior' => $this->get_evolucion_mensual($anterior),
        );
    }

    public function get_ranking($dimension, $filtros, $limite = 10)
    {
        $dimension = $this->resolver_dimension($dimension);
        $filtros = $this->normalizar_filtros($filtros);
        $limite = max(1, min((int) $limite, 100));
        $campo = $this->dimensiones[$dimension];

        $sql = "
            SELECT
                {$campo} AS id,
                {$campo} AS nombre,
                SUM(importe) AS total,
                COUNT(*) AS facturas,
                SUM(consumo) AS consumo,
                GROUP_CONCAT(DISTINCT unidad_medida ORDER BY unidad_medida SEPARATOR ', ') AS unidad_medida
            FROM (" . $this->universo_sql() . ") U
            " . $this->where_sql($filtros) . "
            GROUP BY {$campo}
            ORDER BY total DESC
            LIMIT {$limite}
        ";

        return $this->db->query($sql)->result_array();
    }

    public function get_drilldown($nivel, $filtros, $limite = 50)
    {
        $filtros = $this->normalizar_filtros($filtros);
        $nivel = max(0, (int) $nivel);
        $dimension = $this->drilldown[$nivel] ?? 'secretaria';

        return array(
            'nivel' => $nivel,
            'dimension' => $dimension,
            'siguiente_dimension' => $this->drilldown[$nivel + 1] ?? null,
            'breadcrumb' => $this->breadcrumb($filtros),
            'rows' => $this->get_ranking($dimension, $filtros, $limite),
        );
    }

    public function get_filtros($filtros = array())
    {
        $filtros = $this->normalizar_filtros($filtros);

        return array(
            'anios' => $this->opciones_api($this->get_anios()),
            'meses_por_anio' => $this->get_meses_por_anio(),
            'origenes' => $this->opciones_api($this->get_opciones_dimension('origen', $filtros, 'Todo MVL')),
            'proveedores' => $this->opciones_api($this->get_opciones_dimension('proveedor', $filtros, 'Todos los proveedores')),
            'secretarias' => $this->opciones_api($this->get_opciones_dimension('secretaria', $filtros, 'Todas las secretarias')),
            'programas' => $this->opciones_api($this->get_opciones_dimension('programa', $filtros, 'Todos los programas')),
            'proyectos' => $this->opciones_api($this->get_opciones_dimension('proyecto', $filtros, 'Todos los proyectos')),
            'dependencias' => $this->opciones_api($this->get_opciones_dimension('dependencia', $filtros, 'Todas las dependencias')),
            'objetos' => $this->opciones_api($this->get_opciones_dimension('objeto', $filtros, 'Todos los objetos')),
            'unidades_medida' => $this->opciones_api($this->get_opciones_dimension('unidad_medida', $filtros, 'Todas las unidades')),
        );
    }

    private function get_meses_por_anio()
    {
        $sql = "
            SELECT anio_fc AS anio, mes_fc AS mes
            FROM (" . $this->universo_sql() . ") U
            WHERE anio_fc IS NOT NULL
              AND mes_fc IS NOT NULL
              AND mes_fc BETWEEN 1 AND 12
            GROUP BY anio_fc, mes_fc
            ORDER BY anio_fc DESC, mes_fc ASC
        ";

        $meses = array();
        foreach ($this->db->query($sql)->result() as $row) {
            $anio = (string) (int) $row->anio;
            if (!isset($meses[$anio])) {
                $meses[$anio] = array();
            }
            $meses[$anio][] = (int) $row->mes;
        }

        return $meses;
    }

    public function get_consumos_medibles($filtros, $dimension = 'proveedor', $limite = 20)
    {
        $dimension = $this->resolver_dimension($dimension);
        $filtros = $this->normalizar_filtros($filtros);
        $limite = max(1, min((int) $limite, 100));
        $campo = $this->dimensiones[$dimension];

        $sql = "
            SELECT
                {$campo} AS id,
                {$campo} AS nombre,
                unidad_medida,
                SUM(importe) AS total,
                SUM(consumo) AS consumo,
                CASE WHEN SUM(consumo) > 0 THEN SUM(importe) / SUM(consumo) ELSE NULL END AS costo_unitario,
                COUNT(*) AS facturas
            FROM (" . $this->universo_sql() . ") U
            " . $this->where_sql($filtros, true) . "
            GROUP BY {$campo}, unidad_medida
            HAVING consumo > 0
            ORDER BY total DESC
            LIMIT {$limite}
        ";

        return $this->db->query($sql)->result_array();
    }

    public function get_composicion_servicios($filtros)
    {
        $filtros = $this->normalizar_filtros($filtros);
        $where = $this->where_sql($filtros);
        $servicio_case = $this->servicio_case_sql();

        $sql = "
            SELECT
                secretaria,
                servicio,
                SUM(importe) AS total,
                COUNT(*) AS facturas
            FROM (
                SELECT
                    secretaria,
                    {$servicio_case} AS servicio,
                    importe
                FROM (" . $this->universo_sql() . ") U
                {$where}
            ) S
            GROUP BY secretaria, servicio
            ORDER BY secretaria ASC, total DESC
        ";

        $rows = $this->db->query($sql)->result_array();
        $por_secretaria = array();
        $totales_secretaria = array();
        $total_general = 0;

        foreach ($rows as $row) {
            $secretaria = $row['secretaria'];
            $importe = (float) $row['total'];
            if (!isset($por_secretaria[$secretaria])) {
                $por_secretaria[$secretaria] = array();
                $totales_secretaria[$secretaria] = 0;
            }
            $por_secretaria[$secretaria][] = array(
                'servicio' => $row['servicio'],
                'total' => $importe,
                'facturas' => (int) $row['facturas'],
            );
            $totales_secretaria[$secretaria] += $importe;
            $total_general += $importe;
        }

        $comparativo = array();
        foreach ($por_secretaria as $secretaria => $items) {
            usort($items, function ($a, $b) {
                return $b['total'] <=> $a['total'];
            });

            $principal = $items[0] ?? array('servicio' => 'Sin datos', 'total' => 0, 'facturas' => 0);
            $segundo = $items[1] ?? array('servicio' => 'Sin datos', 'total' => 0, 'facturas' => 0);
            $total_secretaria = (float) $totales_secretaria[$secretaria];
            $resto_total = max(0, $total_secretaria - (float) $principal['total'] - (float) $segundo['total']);

            $comparativo[] = array(
                'secretaria' => $secretaria,
                'total' => $total_secretaria,
                'porcentaje_total' => $total_general > 0 ? ($total_secretaria / $total_general) * 100 : 0,
                'principal' => array(
                    'servicio' => $principal['servicio'],
                    'total' => (float) $principal['total'],
                    'porcentaje' => $total_secretaria > 0 ? ((float) $principal['total'] / $total_secretaria) * 100 : 0,
                    'facturas' => (int) $principal['facturas'],
                ),
                'segundo' => array(
                    'servicio' => $segundo['servicio'],
                    'total' => (float) $segundo['total'],
                    'porcentaje' => $total_secretaria > 0 ? ((float) $segundo['total'] / $total_secretaria) * 100 : 0,
                    'facturas' => (int) $segundo['facturas'],
                ),
                'resto' => array(
                    'servicio' => 'Resto',
                    'total' => $resto_total,
                    'porcentaje' => $total_secretaria > 0 ? ($resto_total / $total_secretaria) * 100 : 0,
                    'facturas' => 0,
                ),
                'servicios' => $items,
            );
        }

        usort($comparativo, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        return array(
            'total' => $total_general,
            'comparativo' => array_slice($comparativo, 0, 10),
            'detalle' => $comparativo,
        );
    }

    public function get_crecimiento_yoy($dimension, $filtros, $limite = 10)
    {
        $dimension = $this->resolver_dimension($dimension);
        $filtros = $this->normalizar_filtros($filtros);
        $limite = max(1, min((int) $limite, 100));
        $campo = $this->dimensiones[$dimension];
        $anio = (int) $filtros['anio'];
        $anio_anterior = $anio - 1;
        $mes_sql = '';

        if (!empty($filtros['mes'])) {
            $mes_sql = ' AND mes_fc = ' . (int) $filtros['mes'];
        } elseif (!empty($filtros['mes_desde']) || !empty($filtros['mes_hasta'])) {
            $desde = !empty($filtros['mes_desde']) ? (int) $filtros['mes_desde'] : 1;
            $hasta = !empty($filtros['mes_hasta']) ? (int) $filtros['mes_hasta'] : 12;
            $mes_sql = ' AND mes_fc BETWEEN ' . $desde . ' AND ' . $hasta;
        } else {
            $mes_hasta = $this->max_mes_con_datos($filtros);
            if ($mes_hasta > 0) {
                $mes_sql = ' AND mes_fc BETWEEN 1 AND ' . $mes_hasta;
            }
        }

        $filtros_base = $filtros;
        unset($filtros_base['anio'], $filtros_base['mes'], $filtros_base['mes_desde'], $filtros_base['mes_hasta']);

        $where_base = $this->where_sql($filtros_base);
        $where_base = preg_replace('/^\s*WHERE\s+/i', ' AND ', $where_base);

        $sql = "
            SELECT
                actual.id,
                actual.nombre,
                actual.total_actual,
                COALESCE(anterior.total_anterior, 0) AS total_anterior,
                actual.total_actual - COALESCE(anterior.total_anterior, 0) AS variacion_absoluta,
                CASE
                    WHEN COALESCE(anterior.total_anterior, 0) > 0
                    THEN ((actual.total_actual - anterior.total_anterior) / anterior.total_anterior) * 100
                    ELSE NULL
                END AS variacion_porcentual,
                actual.facturas_actual,
                COALESCE(anterior.facturas_anterior, 0) AS facturas_anterior
            FROM (
                SELECT {$campo} AS id, {$campo} AS nombre, SUM(importe) AS total_actual, COUNT(*) AS facturas_actual
                FROM (" . $this->universo_sql() . ") U
                WHERE anio_fc = {$anio} {$mes_sql} {$where_base}
                GROUP BY {$campo}
            ) actual
            LEFT JOIN (
                SELECT {$campo} AS id, SUM(importe) AS total_anterior, COUNT(*) AS facturas_anterior
                FROM (" . $this->universo_sql() . ") U
                WHERE anio_fc = {$anio_anterior} {$mes_sql} {$where_base}
                GROUP BY {$campo}
            ) anterior ON anterior.id = actual.id
            ORDER BY variacion_absoluta DESC
            LIMIT {$limite}
        ";

        return $this->db->query($sql)->result_array();
    }

    public function get_pareto($dimension, $filtros, $limite = 10)
    {
        $dimension = $this->resolver_dimension($dimension);
        $filtros = $this->normalizar_filtros($filtros);
        $limite = max(1, min((int) $limite, 100));
        $ranking = $this->get_ranking($dimension, $filtros, $limite);
        $total = $this->get_kpis($filtros)['total'];
        $acumulado = 0;
        $rows = array();

        foreach ($ranking as $row) {
            $importe = (float) $row['total'];
            $acumulado += $importe;
            $row['porcentaje_total'] = $total > 0 ? ($importe / $total) * 100 : 0;
            $row['porcentaje_acumulado'] = $total > 0 ? ($acumulado / $total) * 100 : 0;
            $rows[] = $row;
        }

        return array(
            'dimension' => $dimension,
            'total' => $total,
            'limite' => $limite,
            'rows' => $rows,
        );
    }

    public function get_forecast_anual($filtros)
    {
        $filtros = $this->normalizar_filtros($filtros);
        $evolucion = $this->get_evolucion_mensual($filtros);
        $meses = array();
        $total = 0;

        foreach ($evolucion as $row) {
            $mes = (int) $row['mes'];
            $importe = (float) $row['total'];
            if ($mes >= 1 && $mes <= 12 && $importe > 0) {
                $meses[$mes] = $importe;
                $total += $importe;
            }
        }

        $meses_con_datos = count($meses);
        $promedio = $meses_con_datos > 0 ? $total / $meses_con_datos : 0;
        $forecast = $promedio * 12;

        return array(
            'anio' => (int) $filtros['anio'],
            'meses_con_datos' => $meses_con_datos,
            'total_actual' => $total,
            'promedio_mensual' => $promedio,
            'forecast_cierre_anual' => $forecast,
            'meses' => $evolucion,
            'metodo' => 'promedio simple de meses con datos validos',
        );
    }

    public function get_resumen($filtros, $limite = 200)
    {
        $filtros = $this->normalizar_filtros($filtros);
        $limite = max(1, min((int) $limite, 500));

        $sql = "
            SELECT
                origen,
                proveedor,
                secretaria,
                programa,
                proyecto,
                dependencia,
                SUM(importe) AS total,
                COUNT(*) AS facturas
            FROM (" . $this->universo_sql() . ") U
            " . $this->where_sql($filtros) . "
            GROUP BY origen, proveedor, secretaria, programa, proyecto, dependencia
            ORDER BY total DESC
            LIMIT {$limite}
        ";

        return $this->db->query($sql)->result_array();
    }

    public function get_comparativo($filtros)
    {
        $input_comparativo = is_array($filtros) ? $filtros : array();
        $filtros = $this->normalizar_filtros($filtros);
        $anio_actual = (int) $filtros['anio'];
        $anios = array_values(array_filter(array($anio_actual - 2, $anio_actual - 1, $anio_actual), function ($anio) {
            return $anio >= $this->anio_minimo && $anio <= $this->anio_maximo;
        }));

        $alcance = $this->comparativo_alcance($filtros);
        $kpis = array();
        $evolucion = array();

        foreach ($anios as $anio) {
            $filtro_anio = $alcance;
            $filtro_anio['anio'] = $anio;

            $kpis[$anio] = $this->get_kpis($filtro_anio);
            $serie = $this->get_evolucion_mensual($filtro_anio);
            foreach ($serie as &$row) {
                $row['anio'] = $anio;
            }
            unset($row);
            $evolucion[$anio] = $serie;
        }

        $anio_anterior = $anio_actual - 1;
        $total_actual = (float) ($kpis[$anio_actual]['total'] ?? 0);
        $total_anterior = (float) ($kpis[$anio_anterior]['total'] ?? 0);
        $variacion = $total_anterior > 0 ? (($total_actual - $total_anterior) / $total_anterior) * 100 : null;

        return array(
            'anios' => $anios,
            'anio_actual' => $anio_actual,
            'alcance' => array(
                'mes' => !empty($alcance['mes']) ? (int) $alcance['mes'] : null,
                'mes_desde' => !empty($alcance['mes_desde']) ? (int) $alcance['mes_desde'] : null,
                'mes_hasta' => !empty($alcance['mes_hasta']) ? (int) $alcance['mes_hasta'] : null,
                'label' => $this->comparativo_alcance_label($alcance, $anio_actual),
            ),
            'kpis' => $kpis,
            'variacion_actual_vs_anterior' => $variacion,
            'evolucion' => $evolucion,
            'secretarias' => $this->comparativo_ranking('secretaria', $alcance, $anios, 10),
            'proveedores' => $this->comparativo_ranking('proveedor', $alcance, $anios, 10),
            'dependencias' => $this->comparativo_ranking('dependencia', $alcance, $anios, 10),
            'periodos' => $this->get_comparativo_periodos($input_comparativo),
            'cuentas' => $this->comparativo_cuentas_periodos($input_comparativo, 30),
            'facturas' => $this->comparativo_facturas_periodos($input_comparativo, 80),
            'aumentos' => $this->get_crecimiento_yoy('dependencia', $alcance, 10),
        );
    }

    public function get_eficiencia_energetica($input)
    {
        $filtros = $this->normalizar_filtros_eficiencia($input);
        if ($filtros['modo'] === 'operativa') {
            return $this->get_eficiencia_operativa($filtros);
        }

        $universo = $this->universo_eficiencia_sql();
        $where = $this->where_sql_eficiencia($filtros);
        $perdida_sql = '(recargo_tgfi + cargo_pot_excd + cargo_exc)';
        $observado_sql = "(
            recargo_tgfi > 0
            OR cargo_pot_excd > 0
            OR cargo_exc > 0
            OR p_excedida > 0
            OR tgfi > 0
            OR (cosfi > 0 AND cosfi < 0.85)
        )";

        $kpis = $this->db->query("
            SELECT
                COUNT(*) AS facturas,
                MAX((anio_fc * 100) + mes_fc) AS corte_operativo,
                COUNT(DISTINCT clave_medidor) AS medidores,
                COUNT(DISTINCT CASE WHEN {$observado_sql} THEN clave_medidor END) AS medidores_observados,
                COUNT(DISTINCT CASE
                    WHEN cargo_pot_excd > 0 OR cargo_exc > 0 OR p_excedida > 0
                    THEN clave_medidor
                END) AS medidores_potencia_excedida,
                COUNT(DISTINCT CASE
                    WHEN cosfi > 0 AND cosfi < 0.85
                    THEN clave_medidor
                END) AS medidores_cosfi_critico,
                COUNT(DISTINCT CASE
                    WHEN recargo_tgfi > 0
                    THEN clave_medidor
                END) AS medidores_con_tgfi,
                COUNT(DISTINCT CASE
                    WHEN segmento = 'Edificios y dependencias'
                        AND p_contratada > 0
                        AND p_registrada > 0
                        AND (p_registrada / p_contratada) < 0.60
                    THEN clave_medidor
                END) AS contratos_sobredimensionados,
                SUM(importe) AS importe_total,
                SUM(consumo_kwh) AS consumo_kwh,
                SUM(recargo_tgfi) AS penalidad_tgfi,
                SUM(cargo_pot_excd) AS exceso_potencia_t3,
                SUM(cargo_exc) AS exceso_potencia_t2,
                SUM({$perdida_sql}) AS impacto_identificado,
                COUNT(DISTINCT CASE WHEN {$perdida_sql} >= 1000000 THEN clave_medidor END) AS suministros_criticos,
                SUM(CASE WHEN segmento = 'Alumbrado Publico' THEN importe ELSE 0 END) AS importe_alumbrado_publico,
                COUNT(DISTINCT CASE WHEN segmento = 'Alumbrado Publico' THEN clave_medidor END) AS medidores_alumbrado_publico,
                COUNT(DISTINCT CASE WHEN secretaria = 'SIN ASIGNAR' THEN clave_medidor END) AS medidores_sin_secretaria
            FROM ({$universo}) E
            {$where}
        ")->row_array();

        $importe_total = (float) ($kpis['importe_total'] ?? 0);
        $consumo_total = (float) ($kpis['consumo_kwh'] ?? 0);
        $impacto = (float) ($kpis['impacto_identificado'] ?? 0);
        $kpis['costo_unitario'] = $consumo_total > 0 ? $importe_total / $consumo_total : null;
        $kpis['impacto_sobre_gasto_pct'] = $importe_total > 0 ? ($impacto / $importe_total) * 100 : null;

        $ahorro = $this->db->query("
            SELECT
                COALESCE(SUM(ahorro_estimado), 0) AS ahorro_potencial,
                COUNT(*) AS contratos_con_oportunidad
            FROM (
                SELECT
                    clave_medidor,
                    COUNT(DISTINCT CONCAT(anio_fc, '-', LPAD(mes_fc, 2, '0'))) AS meses,
                    MAX(p_contratada) AS potencia_contratada,
                    MAX(p_registrada) AS pico_registrado,
                    SUM(cargo_pot_contratada) AS cargo_contratado,
                    CASE
                        WHEN COUNT(DISTINCT CONCAT(anio_fc, '-', LPAD(mes_fc, 2, '0'))) >= 6
                            AND MAX(p_contratada) > 0
                            AND MAX(p_registrada) > 0
                            AND (MAX(p_registrada) / MAX(p_contratada)) < 0.60
                            AND (MAX(p_registrada) * 1.15) < MAX(p_contratada)
                        THEN SUM(cargo_pot_contratada)
                            * (MAX(p_contratada) - (MAX(p_registrada) * 1.15))
                            / MAX(p_contratada)
                        ELSE 0
                    END AS ahorro_estimado
                FROM ({$universo}) E
                {$where}
                    AND segmento = 'Edificios y dependencias'
                GROUP BY clave_medidor
            ) O
            WHERE ahorro_estimado > 0
        ")->row_array();
        $kpis['ahorro_potencial'] = (float) ($ahorro['ahorro_potencial'] ?? 0);
        $kpis['contratos_con_oportunidad'] = (int) ($ahorro['contratos_con_oportunidad'] ?? 0);

        $evolucion = $this->db->query("
            SELECT
                anio_fc AS anio,
                mes_fc AS mes,
                SUM(importe) AS importe,
                SUM(consumo_kwh) AS consumo_kwh,
                SUM(recargo_tgfi) AS penalidad_tgfi,
                SUM(cargo_pot_excd + cargo_exc) AS exceso_potencia,
                SUM({$perdida_sql}) AS impacto_identificado,
                CASE WHEN SUM(consumo_kwh) > 0 THEN SUM(importe) / SUM(consumo_kwh) ELSE NULL END AS costo_unitario
            FROM ({$universo}) E
            {$where}
            GROUP BY anio_fc, mes_fc
            ORDER BY anio_fc ASC, mes_fc ASC
        ")->result_array();

        $evolucion_operativa = array();
        if ($filtros['modo'] === 'operativa') {
            $universo_historico = $this->universo_eficiencia_sql();
            $where_tendencia = $this->where_sql_eficiencia_tendencia($filtros);
            $evolucion_operativa = $this->db->query("
                SELECT
                    anio_fc AS anio,
                    mes_fc AS mes,
                    COUNT(DISTINCT clave_medidor) AS medidores_analizados,
                    COUNT(DISTINCT CASE WHEN {$observado_sql} THEN clave_medidor END) AS medidores_observados,
                    COUNT(DISTINCT CASE WHEN cargo_pot_excd > 0 OR cargo_exc > 0 OR p_excedida > 0 THEN clave_medidor END) AS potencia_excedida,
                    COUNT(DISTINCT CASE WHEN cosfi > 0 AND cosfi < 0.85 THEN clave_medidor END) AS cosfi_critico,
                    COUNT(DISTINCT CASE WHEN recargo_tgfi > 0 THEN clave_medidor END) AS tgfi_aplicado,
                    COUNT(DISTINCT CASE
                        WHEN segmento = 'Edificios y dependencias'
                            AND p_contratada > 0
                            AND p_registrada > 0
                            AND (p_registrada / p_contratada) < 0.60
                        THEN clave_medidor
                    END) AS sobredimensionados
                FROM ({$universo_historico}) E
                {$where_tendencia}
                GROUP BY anio_fc, mes_fc
                ORDER BY anio_fc ASC, mes_fc ASC
            ")->result_array();
        }

        $composicion_costo = $this->db->query("
            SELECT
                SUM(importe) AS importe_total,
                SUM(cargo_fijo) AS cargo_fijo,
                SUM(cargo_pot_contratada) AS potencia_contratada,
                SUM(cargo_pot_adquirida) AS potencia_adquirida,
                SUM(cargo_pot_excd + cargo_exc) AS potencia_excedida,
                SUM(cargo_variable) AS energia_variable,
                SUM(recargo_tgfi) AS tgfi
            FROM ({$universo}) E
            {$where}
        ")->row_array();
        $componentes_conocidos = (float) ($composicion_costo['cargo_fijo'] ?? 0)
            + (float) ($composicion_costo['potencia_contratada'] ?? 0)
            + (float) ($composicion_costo['potencia_adquirida'] ?? 0)
            + (float) ($composicion_costo['potencia_excedida'] ?? 0)
            + (float) ($composicion_costo['energia_variable'] ?? 0)
            + (float) ($composicion_costo['tgfi'] ?? 0);
        $composicion_costo['otros_impuestos'] = max(0, (float) ($composicion_costo['importe_total'] ?? 0) - $componentes_conocidos);
        $composicion_costo['no_consumo_pct'] = (float) ($composicion_costo['importe_total'] ?? 0) > 0
            ? (((float) ($composicion_costo['cargo_fijo'] ?? 0)
                + (float) ($composicion_costo['potencia_contratada'] ?? 0)
                + (float) ($composicion_costo['potencia_adquirida'] ?? 0)
                + (float) ($composicion_costo['potencia_excedida'] ?? 0)
                + (float) ($composicion_costo['tgfi'] ?? 0)) / (float) $composicion_costo['importe_total']) * 100
            : null;

        $top_dependencias = $this->db->query("
            SELECT
                dependencia,
                COUNT(DISTINCT clave_medidor) AS medidores,
                SUM(importe) AS importe,
                SUM(consumo_kwh) AS consumo_kwh,
                SUM(recargo_tgfi) AS penalidad_tgfi,
                SUM(cargo_pot_excd + cargo_exc) AS exceso_potencia,
                SUM({$perdida_sql}) AS impacto_identificado
            FROM ({$universo}) E
            {$where}
                AND segmento = 'Edificios y dependencias'
            GROUP BY dependencia
            HAVING impacto_identificado > 0
            ORDER BY impacto_identificado DESC
            LIMIT 10
        ")->result_array();

        $criticas = $this->db->query("
            SELECT COUNT(*) AS total
            FROM (
                SELECT dependencia
                FROM ({$universo}) E
                {$where}
                    AND segmento = 'Edificios y dependencias'
                GROUP BY dependencia
                HAVING SUM({$perdida_sql}) >= 1000000
            ) C
        ")->row_array();
        $kpis['dependencias_criticas'] = (int) ($criticas['total'] ?? 0);

        $top_medidores = $this->db->query("
            SELECT
                dependencia,
                nro_cuenta,
                nro_medidor,
                tipo_de_tarifa,
                segmento,
                COUNT(*) AS facturas,
                SUM(importe) AS importe,
                SUM(consumo_kwh) AS consumo_kwh,
                MAX(p_contratada) AS p_contratada,
                MAX(p_registrada) AS p_registrada,
                MAX(p_excedida) AS p_excedida,
                SUM(recargo_tgfi) AS penalidad_tgfi,
                SUM(cargo_pot_excd + cargo_exc) AS exceso_potencia,
                SUM({$perdida_sql}) AS impacto_identificado
            FROM ({$universo}) E
            {$where}
            GROUP BY dependencia, nro_cuenta, nro_medidor, tipo_de_tarifa, segmento
            HAVING impacto_identificado > 0
            ORDER BY impacto_identificado DESC
            LIMIT 15
        ")->result_array();

        $operativa = $this->db->query("
            SELECT
                dependencia,
                secretaria,
                nro_cuenta,
                nro_medidor,
                tipo_de_tarifa,
                segmento,
                COUNT(*) AS facturas,
                SUM(importe) AS importe,
                SUM(consumo_kwh) AS consumo_kwh,
                MAX(p_contratada) AS p_contratada,
                MAX(p_registrada) AS p_registrada,
                MAX(p_excedida) AS p_excedida,
                COUNT(DISTINCT CONCAT(anio_fc, '-', LPAD(mes_fc, 2, '0'))) AS meses_analizados,
                SUM(cargo_pot_contratada) AS cargo_pot_contratada,
                MAX(cosfi) AS cosfi,
                MAX(tgfi) AS tgfi,
                SUM(recargo_tgfi) AS penalidad_tgfi,
                SUM(cargo_pot_excd) AS exceso_potencia_t3,
                SUM(cargo_exc) AS exceso_potencia_t2,
                SUM({$perdida_sql}) AS impacto_identificado,
                CASE
                    WHEN COUNT(DISTINCT CONCAT(anio_fc, '-', LPAD(mes_fc, 2, '0'))) >= 6
                        AND MAX(p_contratada) > 0
                        AND MAX(p_registrada) > 0
                        AND (MAX(p_registrada) * 1.15) < MAX(p_contratada)
                    THEN SUM(cargo_pot_contratada)
                        * (MAX(p_contratada) - (MAX(p_registrada) * 1.15))
                        / MAX(p_contratada)
                    ELSE 0
                END AS ahorro_potencial,
                CASE
                    WHEN SUM(recargo_tgfi) > 0 THEN 'Factor de potencia / TGFI'
                    WHEN SUM(cargo_pot_excd) > 0 OR MAX(p_excedida) > 0 THEN 'Potencia excedida T3'
                    WHEN SUM(cargo_exc) > 0 THEN 'Exceso de potencia T2'
                    WHEN MAX(cosfi) > 0 AND MAX(cosfi) < 0.85 THEN 'CosFi bajo'
                    WHEN MAX(p_contratada) > 0
                        AND MAX(p_registrada) > 0
                        AND (MAX(p_registrada) / MAX(p_contratada)) < 0.60
                    THEN 'Contrato sobredimensionado'
                    ELSE 'Revisar datos tecnicos'
                END AS problema_principal,
                CASE
                    WHEN SUM(recargo_tgfi) > 0 THEN 'Revisar factor de potencia y banco de capacitores'
                    WHEN SUM(cargo_pot_excd) > 0 OR MAX(p_excedida) > 0 THEN 'Revisar potencia contratada y picos registrados'
                    WHEN SUM(cargo_exc) > 0 THEN 'Analizar ampliacion contractual o control de demanda'
                    WHEN MAX(p_contratada) > 0 AND MAX(p_registrada) > 0
                        AND (MAX(p_registrada) / MAX(p_contratada)) < 0.60
                    THEN 'Evaluar reduccion de potencia contratada'
                    ELSE 'Validar datos tecnicos del suministro'
                END AS accion_sugerida,
                CASE
                    WHEN SUM({$perdida_sql}) >= 1000000 THEN 'Alta'
                    WHEN SUM({$perdida_sql}) >= 250000 THEN 'Media'
                    WHEN SUM({$perdida_sql}) > 0 THEN 'Baja'
                    ELSE 'Revisar'
                END AS prioridad
            FROM ({$universo}) E
            {$where}
                AND segmento = 'Edificios y dependencias'
            GROUP BY dependencia, secretaria, nro_cuenta, nro_medidor, tipo_de_tarifa, segmento
            HAVING impacto_identificado > 0
                OR (
                    MAX(p_contratada) > 0
                    AND MAX(p_registrada) > 0
                    AND (MAX(p_registrada) / MAX(p_contratada)) < 0.60
                )
            ORDER BY impacto_identificado DESC, importe DESC
            LIMIT 50
        ")->result_array();

        $where_historico = $this->where_sql_eficiencia_historico($filtros);
        $generacion_kpis = $this->db->query("
            SELECT
                SUM(energia_inyectada) AS energia_inyectada,
                COUNT(*) AS registros,
                COUNT(DISTINCT nro_cuenta) AS cuentas_generadoras,
                MIN(CONCAT(anio_fc, '-', LPAD(mes_fc, 2, '0'))) AS periodo_desde,
                MAX(CONCAT(anio_fc, '-', LPAD(mes_fc, 2, '0'))) AS periodo_hasta,
                CASE WHEN SUM(consumo_kwh) > 0 THEN SUM(importe) / SUM(consumo_kwh) ELSE NULL END AS costo_unitario_referencia
            FROM ({$universo}) E
            {$where_historico}
                AND energia_inyectada > 0
        ")->row_array();
        $generacion_kpis['valor_equivalente_estimado'] = (float) ($generacion_kpis['energia_inyectada'] ?? 0)
            * (float) ($generacion_kpis['costo_unitario_referencia'] ?? 0);

        $generacion_ranking = $this->db->query("
            SELECT nro_cuenta, dependencia, SUM(energia_inyectada) AS energia_inyectada,
                SUM(consumo_kwh) AS consumo_kwh,
                CASE WHEN SUM(consumo_kwh) > 0 THEN SUM(energia_inyectada) / SUM(consumo_kwh) * 100 ELSE NULL END AS autogeneracion_pct
            FROM ({$universo}) E
            {$where_historico}
                AND energia_inyectada > 0
            GROUP BY nro_cuenta, dependencia
            ORDER BY energia_inyectada DESC
        ")->result_array();

        $generacion_evolucion = $this->db->query("
            SELECT anio_fc AS anio, mes_fc AS mes, SUM(energia_inyectada) AS energia_inyectada
            FROM ({$universo}) E
            {$where_historico}
                AND energia_inyectada > 0
            GROUP BY anio_fc, mes_fc
            ORDER BY anio_fc, mes_fc
        ")->result_array();

        return array(
            'filtros_aplicados' => $filtros,
            'opciones' => $this->opciones_eficiencia($filtros),
            'kpis' => $kpis,
            'causas' => array(
                array('id' => 'tgfi', 'nombre' => 'TGFI / factor de potencia', 'total' => (float) ($kpis['penalidad_tgfi'] ?? 0)),
                array('id' => 't3', 'nombre' => 'Potencia excedida T3', 'total' => (float) ($kpis['exceso_potencia_t3'] ?? 0)),
                array('id' => 't2', 'nombre' => 'Exceso de potencia T2', 'total' => (float) ($kpis['exceso_potencia_t2'] ?? 0)),
            ),
            'evolucion' => $evolucion,
            'evolucion_operativa' => $evolucion_operativa,
            'composicion_costo' => $composicion_costo,
            'top_dependencias' => $top_dependencias,
            'top_medidores' => $top_medidores,
            'operativa' => $operativa,
            'generacion' => array(
                'kpis' => $generacion_kpis,
                'ranking' => $generacion_ranking,
                'evolucion' => $generacion_evolucion,
            ),
            'criterio' => array(
                'impacto_identificado' => 'recargo_tgfi + cargo_pot_excd + cargo_exc',
                'alumbrado_publico' => 'T1-AP se incluye en totales financieros y se separa de rankings operativos de edificios.',
            ),
        );
    }

    private function get_eficiencia_operativa($filtros)
    {
        $universo = $this->universo_eficiencia_operativa_sql($filtros);
        $where = $this->where_sql_eficiencia($filtros);
        $observado = "(
            recargo_tgfi > 0
            OR cargo_pot_excd > 0
            OR cargo_exc > 0
            OR p_excedida > 0
            OR tgfi > 0
            OR (cosfi > 0 AND cosfi < 0.85)
        )";

        $kpis = $this->db->query("
            SELECT
                MAX((anio_fc * 100) + mes_fc) AS corte_operativo,
                COUNT(DISTINCT clave_medidor) AS medidores,
                COUNT(DISTINCT CASE WHEN {$observado} THEN clave_medidor END) AS medidores_observados,
                COUNT(DISTINCT CASE WHEN cargo_pot_excd > 0 OR cargo_exc > 0 OR p_excedida > 0 THEN clave_medidor END) AS medidores_potencia_excedida,
                COUNT(DISTINCT CASE WHEN cosfi > 0 AND cosfi < 0.85 THEN clave_medidor END) AS medidores_cosfi_critico,
                COUNT(DISTINCT CASE WHEN recargo_tgfi > 0 THEN clave_medidor END) AS medidores_con_tgfi,
                COUNT(DISTINCT CASE
                    WHEN segmento = 'Edificios y dependencias'
                        AND p_contratada > 0
                        AND p_registrada > 0
                        AND (p_registrada / p_contratada) < 0.60
                    THEN clave_medidor
                END) AS contratos_sobredimensionados
            FROM ({$universo}) E
            {$where}
        ")->row_array();

        $base = $this->universo_eficiencia_sql();
        $where_tendencia = $this->where_sql_eficiencia_tendencia($filtros);
        $evolucion = $this->db->query("
            SELECT
                anio_fc AS anio,
                mes_fc AS mes,
                COUNT(DISTINCT clave_medidor) AS medidores_analizados,
                COUNT(DISTINCT CASE WHEN {$observado} THEN clave_medidor END) AS medidores_observados,
                COUNT(DISTINCT CASE WHEN cargo_pot_excd > 0 OR cargo_exc > 0 OR p_excedida > 0 THEN clave_medidor END) AS potencia_excedida,
                COUNT(DISTINCT CASE WHEN cosfi > 0 AND cosfi < 0.85 THEN clave_medidor END) AS cosfi_critico,
                COUNT(DISTINCT CASE WHEN recargo_tgfi > 0 THEN clave_medidor END) AS tgfi_aplicado,
                COUNT(DISTINCT CASE
                    WHEN segmento = 'Edificios y dependencias'
                        AND p_contratada > 0
                        AND p_registrada > 0
                        AND (p_registrada / p_contratada) < 0.60
                    THEN clave_medidor
                END) AS sobredimensionados
            FROM ({$base}) E
            {$where_tendencia}
            GROUP BY anio_fc, mes_fc
            ORDER BY anio_fc ASC, mes_fc ASC
        ")->result_array();

        $option_rows = $this->db->query("
            SELECT segmento, tipo_de_tarifa, dependencia, nro_cuenta, nro_medidor
            FROM ({$universo}) E
            " . $this->where_sql_eficiencia($filtros, 'problema') . "
            GROUP BY segmento, tipo_de_tarifa, dependencia, nro_cuenta, nro_medidor
            ORDER BY dependencia, nro_cuenta, nro_medidor
        ")->result_array();

        $filtros_12 = $filtros;
        $filtros_12['ventana'] = 12;
        $where_12 = $this->where_sql_eficiencia_tendencia($filtros_12);
        $action_rows = $this->db->query("
            SELECT
                E.dependencia,
                E.secretaria,
                E.nro_cuenta,
                E.nro_medidor,
                E.tipo_de_tarifa,
                E.segmento,
                E.anio_fc,
                E.mes_fc,
                E.p_contratada,
                E.p_registrada,
                E.p_excedida,
                E.cosfi,
                E.tgfi,
                E.recargo_tgfi,
                E.cargo_pot_excd,
                E.cargo_exc,
                COALESCE(O.ahorro_potencial, 0) AS ahorro_potencial
            FROM ({$universo}) E
            LEFT JOIN (
                SELECT
                    clave_medidor,
                    COUNT(DISTINCT CONCAT(anio_fc, '-', LPAD(mes_fc, 2, '0'))) AS meses,
                    CASE
                        WHEN COUNT(DISTINCT CONCAT(anio_fc, '-', LPAD(mes_fc, 2, '0'))) >= 6
                            AND MAX(p_contratada) > 0
                            AND MAX(p_registrada) > 0
                            AND (MAX(p_registrada) / MAX(p_contratada)) < 0.60
                            AND (MAX(p_registrada) * 1.15) < MAX(p_contratada)
                        THEN SUM(cargo_pot_contratada)
                            * (MAX(p_contratada) - (MAX(p_registrada) * 1.15))
                            / MAX(p_contratada)
                        ELSE 0
                    END AS ahorro_potencial
                FROM ({$base}) H
                {$where_12}
                    AND segmento = 'Edificios y dependencias'
                GROUP BY clave_medidor
            ) O ON O.clave_medidor = E.clave_medidor
            {$where}
        ")->result_array();

        $operativa = array();
        $distribucion = array();
        $dependencias = array();
        $cuentas = array();
        foreach ($action_rows as $row) {
            $potencia = (float) $row['cargo_pot_excd'] + (float) $row['cargo_exc'];
            $tgfi_importe = (float) $row['recargo_tgfi'];
            $ahorro = (float) $row['ahorro_potencial'];
            $problema = null;
            $accion = null;
            $detalle = '';
            $impacto = 0;

            if (($filtros['problema'] === 'potencia' || (empty($filtros['problema']) && $potencia >= $tgfi_importe))
                && ($potencia > 0 || (float) $row['p_excedida'] > 0)) {
                $problema = 'Potencia excedida';
                $accion = 'Revisar potencia contratada y picos registrados';
                $detalle = (float) $row['p_excedida'] > 0 ? number_format((float) $row['p_excedida'], 2, ',', '.') . ' kW' : 'cargo por excedente';
                $impacto = $potencia * 12;
            } elseif (($filtros['problema'] === 'tgfi' || empty($filtros['problema']))
                && ($tgfi_importe > 0 || (float) $row['tgfi'] > 0)) {
                $problema = 'TGFI aplicado';
                $accion = 'Revisar factor de potencia y banco de capacitores';
                $detalle = 'recargo por bajo factor';
                $impacto = $tgfi_importe * 12;
            } elseif (($filtros['problema'] === 'cosfi' || empty($filtros['problema']))
                && (float) $row['cosfi'] > 0 && (float) $row['cosfi'] < 0.85) {
                $problema = 'CosFi critico';
                $accion = 'Evaluar correccion del factor de potencia';
                $detalle = 'CosFi ' . number_format((float) $row['cosfi'], 2, ',', '.');
            } elseif (($filtros['problema'] === 'sobredimensionado' || empty($filtros['problema']))
                && ($ahorro > 0 || ((float) $row['p_contratada'] > 0 && (float) $row['p_registrada'] > 0 && ((float) $row['p_registrada'] / (float) $row['p_contratada']) < 0.60))) {
                $problema = 'Contrato sobredimensionado';
                $accion = 'Evaluar reduccion de potencia contratada';
                $utilizacion = (float) $row['p_contratada'] > 0 ? ((float) $row['p_registrada'] / (float) $row['p_contratada']) * 100 : 0;
                $detalle = 'utilizacion ' . number_format($utilizacion, 1, ',', '.') . '%';
                $impacto = $ahorro;
            }

            if ($problema === null) {
                continue;
            }

            $prioridad = $impacto >= 1000000 ? 'Alta' : ($impacto >= 250000 ? 'Media' : 'Baja');
            $item = array(
                'dependencia' => $row['dependencia'],
                'secretaria' => $row['secretaria'],
                'nro_cuenta' => $row['nro_cuenta'],
                'nro_medidor' => $row['nro_medidor'],
                'tipo_de_tarifa' => $row['tipo_de_tarifa'],
                'periodo' => sprintf('%04d-%02d', (int) $row['anio_fc'], (int) $row['mes_fc']),
                'problema_principal' => $problema,
                'detalle_problema' => $detalle,
                'accion_sugerida' => $accion,
                'impacto_anual_estimado' => $impacto,
                'prioridad' => $prioridad,
                'estado' => 'Detectado',
            );
            $operativa[] = $item;

            if (!isset($distribucion[$problema])) {
                $distribucion[$problema] = array('problema' => $problema, 'medidores' => 0, 'impacto' => 0);
            }
            $distribucion[$problema]['medidores']++;
            $distribucion[$problema]['impacto'] += $impacto;

            $dep = (string) $row['dependencia'];
            if (!isset($dependencias[$dep])) {
                $dependencias[$dep] = array('dependencia' => $dep, 'medidores' => 0, 'impacto' => 0);
            }
            $dependencias[$dep]['medidores']++;
            $dependencias[$dep]['impacto'] += $impacto;

            $cuenta = (string) $row['nro_cuenta'];
            if (!isset($cuentas[$cuenta])) {
                $cuentas[$cuenta] = array('cuenta' => $cuenta, 'medidores' => 0, 'impacto' => 0);
            }
            $cuentas[$cuenta]['medidores']++;
            $cuentas[$cuenta]['impacto'] += $impacto;
        }

        usort($operativa, function ($a, $b) { return $b['impacto_anual_estimado'] <=> $a['impacto_anual_estimado']; });
        $distribucion = array_values($distribucion);
        usort($distribucion, function ($a, $b) { return $b['medidores'] <=> $a['medidores']; });
        $dependencias = array_values($dependencias);
        usort($dependencias, function ($a, $b) { return $b['impacto'] <=> $a['impacto']; });
        $cuentas = array_values($cuentas);
        usort($cuentas, function ($a, $b) { return $b['impacto'] <=> $a['impacto']; });

        return array(
            'filtros_aplicados' => $filtros,
            'opciones' => $this->opciones_eficiencia_desde_rows($option_rows),
            'kpis' => $kpis,
            'evolucion' => array(),
            'evolucion_operativa' => $evolucion,
            'causas' => array(),
            'composicion_costo' => array(),
            'distribucion_problemas' => $distribucion,
            'impacto_problemas' => array_values(array_filter($distribucion, function ($item) {
                return (float) $item['impacto'] > 0;
            })),
            'top_dependencias' => array_slice($dependencias, 0, 10),
            'top_cuentas' => array_slice($cuentas, 0, 10),
            'top_medidores' => array_slice($operativa, 0, 15),
            'operativa' => $operativa,
            'generacion' => array('kpis' => array(), 'ranking' => array(), 'evolucion' => array()),
            'criterio' => array(
                'foto_operativa' => 'Ultimo registro conocido por medidor dentro de los ultimos 12 meses con datos.',
                'evolucion_operativa' => 'Medidores con incidencias detectadas por mes; no representa aun estados resueltos.',
            ),
        );
    }

    private function opciones_eficiencia_desde_rows($rows)
    {
        $config = array(
            'segmentos' => array('campo' => 'segmento', 'placeholder' => 'Todos los segmentos'),
            'tarifas' => array('campo' => 'tipo_de_tarifa', 'placeholder' => 'Todas las tarifas'),
            'dependencias' => array('campo' => 'dependencia', 'placeholder' => 'Todas las dependencias'),
            'cuentas' => array('campo' => 'nro_cuenta', 'placeholder' => 'Todas las cuentas'),
            'medidores' => array('campo' => 'nro_medidor', 'placeholder' => 'Todos los medidores'),
        );
        $opciones = array();

        foreach ($config as $nombre => $item) {
            $valores = array();
            foreach ($rows as $row) {
                $valor = isset($row[$item['campo']]) ? trim((string) $row[$item['campo']]) : '';
                if ($valor !== '') {
                    $valores[$valor] = $valor;
                }
            }
            natcasesort($valores);
            $lista = array(array('value' => '', 'label' => $item['placeholder']));
            foreach ($valores as $valor) {
                $lista[] = array('value' => $valor, 'label' => $valor);
            }
            $opciones[$nombre] = $lista;
        }

        return $opciones;
    }

    private function universo_eficiencia_sql()
    {
        return "
            SELECT
                id,
                CAST(anio_fc AS UNSIGNED) AS anio_fc,
                CAST(mes_fc AS UNSIGNED) AS mes_fc,
                COALESCE(NULLIF(TRIM(secretaria), ''), 'SIN ASIGNAR') AS secretaria,
                COALESCE(NULLIF(TRIM(dependencia), ''), 'SIN DEPENDENCIA') AS dependencia,
                COALESCE(NULLIF(TRIM(nro_cuenta), ''), 'SIN CUENTA') AS nro_cuenta,
                COALESCE(NULLIF(TRIM(nro_medidor), ''), 'SIN MEDIDOR') AS nro_medidor,
                CONCAT(
                    COALESCE(NULLIF(TRIM(nro_cuenta), ''), 'SIN CUENTA'),
                    '|',
                    COALESCE(NULLIF(TRIM(nro_medidor), ''), 'SIN MEDIDOR')
                ) AS clave_medidor,
                COALESCE(NULLIF(TRIM(tipo_de_tarifa), ''), 'SIN TARIFA') AS tipo_de_tarifa,
                CASE
                    WHEN REPLACE(REPLACE(UPPER(COALESCE(tipo_de_tarifa, '')), ' ', ''), '-', '') LIKE '%T1AP%'
                    THEN 'Alumbrado Publico'
                    ELSE 'Edificios y dependencias'
                END AS segmento,
                COALESCE(importe_1, importe, 0) AS importe,
                COALESCE(NULLIF(e_activa, 0), NULLIF(consumo, 0), NULLIF(consumo_act, 0), 0) AS consumo_kwh,
                COALESCE(NULLIF(p_contratada, 0), NULLIF(contratada, 0), 0) AS p_contratada,
                COALESCE(NULLIF(p_registrada, 0), NULLIF(consumida, 0), 0) AS p_registrada,
                COALESCE(p_excedida, 0) AS p_excedida,
                COALESCE(NULLIF(cosfi, 0), NULLIF(Cos_resultante, 0), 0) AS cosfi,
                COALESCE(tgfi, 0) AS tgfi,
                COALESCE(recargo_tgfi, 0) AS recargo_tgfi,
                COALESCE(cargo_fijo, 0) AS cargo_fijo,
                COALESCE(NULLIF(cargo_pot_contratada, 0), NULLIF(cargo_contr, 0), 0) AS cargo_pot_contratada,
                COALESCE(NULLIF(cargo_pot_ad, 0), NULLIF(cargo_adq, 0), 0) AS cargo_pot_adquirida,
                COALESCE(NULLIF(cargo_var, 0), NULLIF(cargo_variable_hasta, 0), 0) AS cargo_variable,
                COALESCE(cargo_pot_excd, 0) AS cargo_pot_excd,
                COALESCE(cargo_exc, 0) AS cargo_exc,
                COALESCE(energia_inyectada, 0) AS energia_inyectada
            FROM _consolidados_canon
            WHERE anio_fc REGEXP '^[0-9]{4}$'
                AND mes_fc REGEXP '^[0-9]{1,2}$'
                AND CAST(anio_fc AS UNSIGNED) BETWEEN 2023 AND 2050
                AND CAST(mes_fc AS UNSIGNED) BETWEEN 1 AND 12
        ";
    }

    private function universo_eficiencia_operativa_sql($filtros)
    {
        $base = $this->universo_eficiencia_sql();
        $corte = $this->resolver_corte_eficiencia($filtros);
        $desde = $corte - 11;

        return "
            SELECT FOTO.*
            FROM (
                SELECT
                    E.*,
                    ROW_NUMBER() OVER (
                        PARTITION BY E.clave_medidor
                        ORDER BY E.anio_fc DESC, E.mes_fc DESC, E.id DESC
                    ) AS fila_reciente
                FROM ({$base}) E
                WHERE ((E.anio_fc * 12) + E.mes_fc) BETWEEN {$desde} AND {$corte}
            ) FOTO
            WHERE FOTO.fila_reciente = 1
        ";
    }

    private function resolver_corte_eficiencia($filtros)
    {
        $base = $this->universo_eficiencia_sql();
        $corte_solicitado = ((int) $filtros['anio'] * 12) + (int) $filtros['mes_hasta'];
        $row = $this->db->query("
            SELECT MAX((anio_fc * 12) + mes_fc) AS corte
            FROM ({$base}) C
            WHERE ((anio_fc * 12) + mes_fc) <= ?
        ", array($corte_solicitado))->row_array();

        return !empty($row['corte']) ? (int) $row['corte'] : $corte_solicitado;
    }

    private function normalizar_filtros_eficiencia($input)
    {
        $filtros = array(
            'anio' => !empty($input['anio']) ? (int) $input['anio'] : (int) date('Y'),
            'mes' => !empty($input['mes']) ? (int) $input['mes'] : null,
            'mes_desde' => !empty($input['mes_desde']) ? (int) $input['mes_desde'] : 1,
            'mes_hasta' => !empty($input['mes_hasta']) ? (int) $input['mes_hasta'] : 12,
            'secretaria' => !empty($input['secretaria']) ? trim($input['secretaria']) : null,
            'dependencia' => !empty($input['dependencia']) ? trim($input['dependencia']) : null,
            'cuenta' => !empty($input['cuenta']) ? trim($input['cuenta']) : null,
            'medidor' => !empty($input['medidor']) ? trim($input['medidor']) : null,
            'tarifa' => !empty($input['tarifa']) ? trim($input['tarifa']) : null,
            'segmento' => !empty($input['segmento']) ? trim($input['segmento']) : null,
            'modo' => !empty($input['modo']) && $input['modo'] === 'operativa' ? 'operativa' : 'financiera',
            'ventana' => !empty($input['ventana']) ? (int) $input['ventana'] : 36,
            'problema' => !empty($input['problema']) ? trim($input['problema']) : null,
        );

        if ($filtros['mes']) {
            $filtros['mes_desde'] = $filtros['mes'];
            $filtros['mes_hasta'] = $filtros['mes'];
        }

        $filtros['mes_desde'] = max(1, min(12, (int) $filtros['mes_desde']));
        $filtros['mes_hasta'] = max($filtros['mes_desde'], min(12, (int) $filtros['mes_hasta']));
        $filtros['ventana'] = in_array($filtros['ventana'], array(12, 24, 36), true) ? $filtros['ventana'] : 36;

        return $filtros;
    }

    private function where_sql_eficiencia($filtros, $ignorar = null)
    {
        $condiciones = array('1 = 1');
        if ($filtros['modo'] !== 'operativa') {
            $condiciones[] = 'anio_fc = ' . (int) $filtros['anio'];
            $condiciones[] = 'mes_fc >= ' . (int) $filtros['mes_desde'];
            $condiciones[] = 'mes_fc <= ' . (int) $filtros['mes_hasta'];
        }

        $map = array(
            'secretaria' => 'secretaria',
            'dependencia' => 'dependencia',
            'cuenta' => 'nro_cuenta',
            'medidor' => 'nro_medidor',
            'tarifa' => 'tipo_de_tarifa',
            'segmento' => 'segmento',
        );

        foreach ($map as $key => $campo) {
            if ($key !== $ignorar && !empty($filtros[$key])) {
                $condiciones[] = $campo . ' = ' . $this->db->escape($filtros[$key]);
            }
        }

        if ($ignorar !== 'problema' && !empty($filtros['problema'])) {
            $problemas = array(
                'potencia' => '(cargo_pot_excd > 0 OR cargo_exc > 0 OR p_excedida > 0)',
                'cosfi' => '(cosfi > 0 AND cosfi < 0.85)',
                'tgfi' => '(recargo_tgfi > 0)',
                'sobredimensionado' => '(segmento = \'Edificios y dependencias\' AND p_contratada > 0 AND p_registrada > 0 AND (p_registrada / p_contratada) < 0.60)',
            );
            if (isset($problemas[$filtros['problema']])) {
                $condiciones[] = $problemas[$filtros['problema']];
            }
        }

        return ' WHERE ' . implode(' AND ', $condiciones);
    }

    private function where_sql_eficiencia_historico($filtros)
    {
        $condiciones = array('anio_fc >= 2023');
        $map = array(
            'secretaria' => 'secretaria',
            'dependencia' => 'dependencia',
            'cuenta' => 'nro_cuenta',
            'medidor' => 'nro_medidor',
            'tarifa' => 'tipo_de_tarifa',
            'segmento' => 'segmento',
        );

        foreach ($map as $key => $campo) {
            if (!empty($filtros[$key])) {
                $condiciones[] = $campo . ' = ' . $this->db->escape($filtros[$key]);
            }
        }

        return ' WHERE ' . implode(' AND ', $condiciones);
    }

    private function where_sql_eficiencia_tendencia($filtros)
    {
        $corte = $this->resolver_corte_eficiencia($filtros);
        $desde = $corte - max(1, (int) $filtros['ventana']) + 1;
        $condiciones = array("((anio_fc * 12) + mes_fc) BETWEEN {$desde} AND {$corte}");
        $map = array(
            'dependencia' => 'dependencia',
            'cuenta' => 'nro_cuenta',
            'medidor' => 'nro_medidor',
            'tarifa' => 'tipo_de_tarifa',
            'segmento' => 'segmento',
        );

        foreach ($map as $key => $campo) {
            if (!empty($filtros[$key])) {
                $condiciones[] = $campo . ' = ' . $this->db->escape($filtros[$key]);
            }
        }

        return ' WHERE ' . implode(' AND ', $condiciones);
    }

    private function opciones_eficiencia($filtros)
    {
        $universo = $this->universo_eficiencia_sql();
        $config = array(
            'tarifas' => array('filtro' => 'tarifa', 'campo' => 'tipo_de_tarifa', 'placeholder' => 'Todas las tarifas'),
            'dependencias' => array('filtro' => 'dependencia', 'campo' => 'dependencia', 'placeholder' => 'Todas las dependencias'),
            'cuentas' => array('filtro' => 'cuenta', 'campo' => 'nro_cuenta', 'placeholder' => 'Todas las cuentas'),
            'medidores' => array('filtro' => 'medidor', 'campo' => 'nro_medidor', 'placeholder' => 'Todos los medidores'),
            'segmentos' => array('filtro' => 'segmento', 'campo' => 'segmento', 'placeholder' => 'Todos los segmentos'),
        );
        $opciones = array();

        foreach ($config as $nombre => $item) {
            $rows = $this->db->query("
                SELECT {$item['campo']} AS valor
                FROM ({$universo}) E
                " . $this->where_sql_eficiencia($filtros, $item['filtro']) . "
                GROUP BY {$item['campo']}
                ORDER BY {$item['campo']} ASC
            ")->result_array();

            $lista = array(array('value' => '', 'label' => $item['placeholder']));
            foreach ($rows as $row) {
                $lista[] = array('value' => (string) $row['valor'], 'label' => (string) $row['valor']);
            }
            $opciones[$nombre] = $lista;
        }

        return $opciones;
    }

    private function get_comparativo_periodos($input)
    {
        $periodo_a = $this->periodo_comparativo_desde_input($input, 'periodo_a', array(
            'anio' => (int) $input['anio'],
            'mes_desde' => !empty($input['mes']) ? (int) $input['mes'] : (!empty($input['mes_desde']) ? (int) $input['mes_desde'] : 1),
            'mes_hasta' => !empty($input['mes']) ? (int) $input['mes'] : (!empty($input['mes_hasta']) ? (int) $input['mes_hasta'] : null),
        ));

        if (empty($periodo_a['mes_hasta'])) {
            $corte = $this->info_corte_ytd_representativo($input);
            $periodo_a['mes_hasta'] = (int) ($corte['mes_corte'] ?? 12);
        }

        $periodo_b = $this->periodo_comparativo_desde_input($input, 'periodo_b', array(
            'anio' => (int) $periodo_a['anio'] - 1,
            'mes_desde' => (int) $periodo_a['mes_desde'],
            'mes_hasta' => (int) $periodo_a['mes_hasta'],
        ));

        $filtro_a = $this->filtros_periodo_comparativo($input, $periodo_a);
        $filtro_b = $this->filtros_periodo_comparativo($input, $periodo_b);
        $kpi_a = $this->get_kpis($filtro_a);
        $kpi_b = $this->get_kpis($filtro_b);

        $variacion = array(
            'total_comparado' => (float) ($kpi_a['total'] ?? 0),
            'delta' => (float) ($kpi_b['total'] ?? 0) - (float) ($kpi_a['total'] ?? 0),
            'porcentaje' => (float) ($kpi_a['total'] ?? 0) > 0 ? (((float) ($kpi_b['total'] ?? 0) - (float) ($kpi_a['total'] ?? 0)) / (float) ($kpi_a['total'] ?? 0)) * 100 : null,
        );
        $objetos = $this->comparativo_ranking_periodos('objeto', $filtro_a, $filtro_b, 10);

        return array(
            'a' => array(
                'label' => $this->periodo_label($periodo_a),
                'filtros' => $periodo_a,
                'kpis' => $kpi_a,
            ),
            'b' => array(
                'label' => $this->periodo_label($periodo_b),
                'filtros' => $periodo_b,
                'kpis' => $kpi_b,
            ),
            'equivalentes' => ((int) $periodo_a['mes_hasta'] - (int) $periodo_a['mes_desde']) === ((int) $periodo_b['mes_hasta'] - (int) $periodo_b['mes_desde']),
            'variacion' => $variacion,
            'evolucion' => array(
                'a' => $this->get_evolucion_mensual($filtro_a),
                'b' => $this->get_evolucion_mensual($filtro_b),
            ),
            'secretarias' => $this->comparativo_ranking_periodos('secretaria', $filtro_a, $filtro_b, 10),
            'programas' => $this->comparativo_ranking_periodos('programa', $filtro_a, $filtro_b, 10),
            'proyectos' => $this->comparativo_ranking_periodos('proyecto', $filtro_a, $filtro_b, 10),
            'proveedores' => $this->comparativo_ranking_periodos('proveedor', $filtro_a, $filtro_b, 10),
            'dependencias' => $this->comparativo_ranking_periodos('dependencia', $filtro_a, $filtro_b, 10),
            'objetos' => $objetos,
            'principal_variacion' => $this->principal_variacion_periodos($objetos),
        );
    }

    private function periodo_comparativo_desde_input($input, $prefix, $default)
    {
        $anio = !empty($input[$prefix . '_anio']) ? (int) $input[$prefix . '_anio'] : (int) $default['anio'];
        $desde = !empty($input[$prefix . '_mes_desde']) ? (int) $input[$prefix . '_mes_desde'] : (int) $default['mes_desde'];
        $hasta = !empty($input[$prefix . '_mes_hasta'])
            ? (int) $input[$prefix . '_mes_hasta']
            : (!empty($default['mes_hasta']) ? (int) $default['mes_hasta'] : null);

        $desde = max(1, min($desde, 12));
        if ($hasta !== null) {
            $hasta = max(1, min($hasta, 12));
        }
        if ($hasta !== null && $desde > $hasta) {
            $tmp = $desde;
            $desde = $hasta;
            $hasta = $tmp;
        }

        return array(
            'anio' => $anio,
            'mes_desde' => $desde,
            'mes_hasta' => $hasta,
        );
    }

    private function filtros_periodo_comparativo($base, $periodo)
    {
        $filtros = $this->normalizar_filtros($base);
        $filtros['anio'] = (int) $periodo['anio'];
        $filtros['mes'] = null;
        $filtros['mes_desde'] = (int) $periodo['mes_desde'];
        $filtros['mes_hasta'] = (int) $periodo['mes_hasta'];
        return $filtros;
    }

    private function periodo_label($periodo)
    {
        $meses = array('', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
        $desde = (int) $periodo['mes_desde'];
        $hasta = (int) $periodo['mes_hasta'];
        $anio = (int) $periodo['anio'];
        return ($desde === $hasta ? $meses[$desde] : $meses[$desde] . '-' . $meses[$hasta]) . ' ' . $anio;
    }

    private function comparativo_ranking_periodos($dimension, $filtro_a, $filtro_b, $limite = 10)
    {
        $dimension = $this->resolver_dimension($dimension);
        $map = array();

        foreach ($this->get_ranking($dimension, $filtro_a, 200) as $row) {
            $key = (string) $row['nombre'];
            $map[$key] = array('id' => $key, 'nombre' => $key, 'total_a' => (float) $row['total'], 'total_b' => 0, 'facturas_a' => (int) $row['facturas'], 'facturas_b' => 0);
        }

        foreach ($this->get_ranking($dimension, $filtro_b, 200) as $row) {
            $key = (string) $row['nombre'];
            if (!isset($map[$key])) {
                $map[$key] = array('id' => $key, 'nombre' => $key, 'total_a' => 0, 'total_b' => 0, 'facturas_a' => 0, 'facturas_b' => 0);
            }
            $map[$key]['total_b'] = (float) $row['total'];
            $map[$key]['facturas_b'] = (int) $row['facturas'];
        }

        $rows = array_values($map);
        foreach ($rows as &$row) {
            $row['variacion_absoluta'] = $row['total_b'] - $row['total_a'];
            $row['variacion_porcentual'] = $row['total_a'] > 0 ? (($row['total_b'] - $row['total_a']) / $row['total_a']) * 100 : null;
        }
        unset($row);

        usort($rows, function ($a, $b) {
            return abs($b['variacion_absoluta']) <=> abs($a['variacion_absoluta']);
        });

        return array_slice($rows, 0, max(1, min((int) $limite, 50)));
    }

    private function principal_variacion_periodos($rows)
    {
        if (empty($rows)) {
            return null;
        }

        usort($rows, function ($a, $b) {
            return abs($b['variacion_absoluta']) <=> abs($a['variacion_absoluta']);
        });

        $row = $rows[0];
        return array(
            'dimension' => 'objeto',
            'nombre' => $row['nombre'],
            'delta' => (float) $row['variacion_absoluta'],
            'porcentaje' => $row['variacion_porcentual'],
        );
    }

    private function comparativo_cuentas_periodos($input, $limite = 30)
    {
        $periodos = $this->get_comparativo_periodos($input);
        $filtro_a = $this->filtros_periodo_comparativo($input, $periodos['a']['filtros']);
        $filtro_b = $this->filtros_periodo_comparativo($input, $periodos['b']['filtros']);
        $map = array();

        foreach ($this->comparativo_cuentas_periodo_rows($filtro_a) as $row) {
            $key = $row['dependencia'] . '|' . $row['nro_cuenta'] . '|' . $row['proveedor'];
            $map[$key] = $row + array('total_a' => (float) $row['total'], 'total_b' => 0, 'facturas_a' => (int) $row['facturas'], 'facturas_b' => 0);
        }

        foreach ($this->comparativo_cuentas_periodo_rows($filtro_b) as $row) {
            $key = $row['dependencia'] . '|' . $row['nro_cuenta'] . '|' . $row['proveedor'];
            if (!isset($map[$key])) {
                $map[$key] = $row + array('total_a' => 0, 'total_b' => 0, 'facturas_a' => 0, 'facturas_b' => 0);
            }
            $map[$key]['total_b'] = (float) $row['total'];
            $map[$key]['facturas_b'] = (int) $row['facturas'];
        }

        $rows = array_values($map);
        foreach ($rows as &$row) {
            $row['variacion_absoluta'] = (float) $row['total_b'] - (float) $row['total_a'];
            $row['variacion_porcentual'] = (float) $row['total_a'] > 0 ? ($row['variacion_absoluta'] / (float) $row['total_a']) * 100 : null;
        }
        unset($row);

        usort($rows, function ($a, $b) {
            return abs($b['variacion_absoluta']) <=> abs($a['variacion_absoluta']);
        });

        return array_slice($rows, 0, max(1, min((int) $limite, 200)));
    }

    private function comparativo_cuentas_periodo_rows($filtros)
    {
        $sql = "
            SELECT dependencia, nro_cuenta, proveedor, SUM(importe) AS total, COUNT(*) AS facturas
            FROM (" . $this->universo_sql() . ") U
            " . $this->where_sql($filtros) . "
            GROUP BY dependencia, nro_cuenta, proveedor
        ";

        return $this->db->query($sql)->result_array();
    }

    private function comparativo_facturas_periodos($input, $limite = 80)
    {
        if (empty($input['cuenta'])) {
            return array();
        }

        $periodos = $this->get_comparativo_periodos($input);
        $filtro_a = $this->filtros_periodo_comparativo($input, $periodos['a']['filtros']);
        $filtro_b = $this->filtros_periodo_comparativo($input, $periodos['b']['filtros']);
        $rows = array();

        foreach ($this->comparativo_facturas_periodo_rows($filtro_a) as $row) {
            $row['periodo'] = 'A';
            $rows[] = $row;
        }

        foreach ($this->comparativo_facturas_periodo_rows($filtro_b) as $row) {
            $row['periodo'] = 'B';
            $rows[] = $row;
        }

        usort($rows, function ($a, $b) {
            if ($a['anio_fc'] === $b['anio_fc']) {
                return $a['mes_fc'] <=> $b['mes_fc'];
            }
            return $a['anio_fc'] <=> $b['anio_fc'];
        });

        return array_slice($rows, 0, max(1, min((int) $limite, 200)));
    }

    private function comparativo_facturas_periodo_rows($filtros)
    {
        $sql = "
            SELECT
                dependencia,
                nro_cuenta,
                proveedor,
                nro_factura,
                periodo_del_consumo,
                anio_fc,
                mes_fc,
                fecha_vencimiento,
                fecha_consolidado,
                importe AS total
            FROM (" . $this->universo_sql() . ") U
            " . $this->where_sql($filtros) . "
            ORDER BY anio_fc ASC, mes_fc ASC, fecha_consolidado ASC, nro_factura ASC
        ";

        return $this->db->query($sql)->result_array();
    }

    private function comparativo_alcance($filtros)
    {
        $filtros = $this->normalizar_filtros($filtros);

        if (!empty($filtros['mes'])) {
            $filtros['mes_desde'] = null;
            $filtros['mes_hasta'] = null;
            return $filtros;
        }

        $corte = $this->info_corte_ytd_representativo($filtros);
        $mes_hasta = (int) ($corte['mes_corte'] ?? 0);
        if ($mes_hasta > 0) {
            $filtros['mes_desde'] = 1;
            $filtros['mes_hasta'] = $mes_hasta;
        }

        return $filtros;
    }

    private function comparativo_alcance_label($filtros, $anio)
    {
        $meses = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

        if (!empty($filtros['mes'])) {
            return ($meses[(int) $filtros['mes']] ?? 'Mes') . ' ' . $anio;
        }

        if (!empty($filtros['mes_hasta'])) {
            return 'Ene-' . substr($meses[(int) $filtros['mes_hasta']] ?? 'Mes', 0, 3) . ' ' . $anio;
        }

        return 'YTD ' . $anio;
    }

    private function comparativo_ranking($dimension, $filtros, $anios, $limite = 10)
    {
        $dimension = $this->resolver_dimension($dimension);
        $anio_actual = max($anios);
        $anio_anterior = $anio_actual - 1;
        $map = array();

        foreach ($anios as $anio) {
            $filtro_anio = $filtros;
            $filtro_anio['anio'] = $anio;
            $rows = $this->get_ranking($dimension, $filtro_anio, 200);

            foreach ($rows as $row) {
                $key = (string) $row['nombre'];
                if (!isset($map[$key])) {
                    $map[$key] = array(
                        'id' => $key,
                        'nombre' => $key,
                        'totales' => array(),
                        'facturas' => array(),
                    );
                }

                $map[$key]['totales'][$anio] = (float) $row['total'];
                $map[$key]['facturas'][$anio] = (int) $row['facturas'];
            }
        }

        $rows = array_values($map);
        foreach ($rows as &$row) {
            $actual = (float) ($row['totales'][$anio_actual] ?? 0);
            $anterior = (float) ($row['totales'][$anio_anterior] ?? 0);
            $row['total_actual'] = $actual;
            $row['total_anterior'] = $anterior;
            $row['variacion_absoluta'] = $actual - $anterior;
            $row['variacion_porcentual'] = $anterior > 0 ? (($actual - $anterior) / $anterior) * 100 : null;
        }
        unset($row);

        usort($rows, function ($a, $b) {
            return $b['total_actual'] <=> $a['total_actual'];
        });

        return array_slice($rows, 0, max(1, min((int) $limite, 50)));
    }

    private function get_opciones_dimension($dimension, $filtros, $placeholder)
    {
        $dimension = $this->resolver_dimension($dimension);
        $filtros = $this->normalizar_filtros($filtros);
        $campo = $this->dimensiones[$dimension];
        unset($filtros[$dimension]);

        $sql = "
            SELECT {$campo} AS valor
            FROM (" . $this->universo_sql() . ") U
            " . $this->where_sql($filtros) . "
            GROUP BY {$campo}
            ORDER BY {$campo} ASC
        ";

        $opciones = array('' => $placeholder);
        foreach ($this->db->query($sql)->result() as $row) {
            if ($row->valor !== '') {
                $opciones[$row->valor] = $row->valor;
            }
        }

        return $opciones;
    }

    private function opciones_api($opciones)
    {
        $items = array();
        foreach ($opciones as $value => $label) {
            $items[] = array(
                'value' => (string) $value,
                'label' => (string) $label,
            );
        }
        return $items;
    }

    private function universo_sql()
    {
        $anio_minimo = (int) $this->anio_minimo;
        $anio_maximo = (int) $this->anio_maximo;

        return "
            SELECT *
            FROM (
                SELECT
                    'proveedores' AS origen,
                    CASE
                        WHEN UPPER(COALESCE(NULLIF(TRIM(proveedor), ''), 'SIN PROVEEDOR')) LIKE 'EDENOR%' THEN 'EDENOR'
                        ELSE COALESCE(NULLIF(TRIM(proveedor), ''), 'SIN PROVEEDOR')
                    END AS proveedor,
                    COALESCE(NULLIF(TRIM(secretaria), ''), 'SIN SECRETARIA') AS secretaria,
                    COALESCE(NULLIF(TRIM(programa), ''), 'SIN PROGRAMA') AS programa,
                    COALESCE(NULLIF(TRIM(proyecto), ''), 'SIN PROYECTO') AS proyecto,
                    COALESCE(NULLIF(TRIM(dependencia), ''), 'SIN DEPENDENCIA') AS dependencia,
                    COALESCE(NULLIF(TRIM(objeto), ''), 'SIN OBJETO') AS objeto,
                    COALESCE(NULLIF(TRIM(nro_cuenta), ''), 'SIN CUENTA') AS nro_cuenta,
                    COALESCE(NULLIF(TRIM(nro_factura), ''), 'SIN FACTURA') AS nro_factura,
                    COALESCE(NULLIF(TRIM(periodo_del_consumo), ''), '') AS periodo_del_consumo,
                    CAST(anio_fc AS UNSIGNED) AS anio_fc,
                    CAST(mes_fc AS UNSIGNED) AS mes_fc,
                    fecha_vencimiento,
                    fecha_consolidado,
                    COALESCE(importe_1, CAST(importe AS DECIMAL(15,2)), 0) AS importe,
                    0 AS consumo,
                    UPPER(COALESCE(NULLIF(TRIM(unidad_medida), ''), 'SIN UNIDAD')) AS unidad_medida
                FROM _consolidados
                WHERE anio_fc REGEXP '^[0-9]{4}$'
                    AND mes_fc REGEXP '^[0-9]{1,2}$'
                    AND CAST(anio_fc AS UNSIGNED) BETWEEN {$anio_minimo} AND {$anio_maximo}
                    AND CAST(mes_fc AS UNSIGNED) BETWEEN 1 AND 12

                UNION ALL

                SELECT
                    'electromecanica' AS origen,
                    CASE
                        WHEN UPPER(COALESCE(NULLIF(TRIM(proveedor), ''), 'SIN PROVEEDOR')) LIKE 'EDENOR%' THEN 'EDENOR'
                        ELSE COALESCE(NULLIF(TRIM(proveedor), ''), 'SIN PROVEEDOR')
                    END AS proveedor,
                    COALESCE(NULLIF(TRIM(secretaria), ''), 'SIN SECRETARIA') AS secretaria,
                    COALESCE(NULLIF(TRIM(programa), ''), 'SIN PROGRAMA') AS programa,
                    COALESCE(NULLIF(TRIM(proyecto), ''), 'SIN PROYECTO') AS proyecto,
                    COALESCE(NULLIF(TRIM(dependencia), ''), 'SIN DEPENDENCIA') AS dependencia,
                    COALESCE(NULLIF(TRIM(objeto), ''), 'SIN OBJETO') AS objeto,
                    COALESCE(NULLIF(TRIM(nro_cuenta), ''), 'SIN CUENTA') AS nro_cuenta,
                    COALESCE(NULLIF(TRIM(nro_factura), ''), 'SIN FACTURA') AS nro_factura,
                    COALESCE(NULLIF(TRIM(periodo_del_consumo), ''), '') AS periodo_del_consumo,
                    CAST(anio_fc AS UNSIGNED) AS anio_fc,
                    CAST(mes_fc AS UNSIGNED) AS mes_fc,
                    fecha_vencimiento,
                    fecha_consolidado,
                    COALESCE(importe_1, importe, 0) AS importe,
                    CASE
                        WHEN COALESCE(consumo, 0) > 0 THEN consumo
                        WHEN COALESCE(e_activa, 0) > 0 THEN e_activa
                        ELSE 0
                    END AS consumo,
                    UPPER(COALESCE(NULLIF(TRIM(unidad_medida), ''), 'SIN UNIDAD')) AS unidad_medida
                FROM _consolidados_canon
                WHERE anio_fc REGEXP '^[0-9]{4}$'
                    AND mes_fc REGEXP '^[0-9]{1,2}$'
                    AND CAST(anio_fc AS UNSIGNED) BETWEEN {$anio_minimo} AND {$anio_maximo}
                    AND CAST(mes_fc AS UNSIGNED) BETWEEN 1 AND 12
            ) BASE
        ";
    }

    private function where_sql($filtros, $solo_medibles = false)
    {
        $condiciones = array('1 = 1');

        if (!empty($filtros['anio'])) {
            $condiciones[] = 'anio_fc = ' . (int) $filtros['anio'];
        }

        if (!empty($filtros['mes'])) {
            $condiciones[] = 'mes_fc = ' . (int) $filtros['mes'];
        }

        if (!empty($filtros['mes_desde'])) {
            $condiciones[] = 'mes_fc >= ' . (int) $filtros['mes_desde'];
        }

        if (!empty($filtros['mes_hasta'])) {
            $condiciones[] = 'mes_fc <= ' . (int) $filtros['mes_hasta'];
        }

        foreach ($this->dimensiones as $key => $campo) {
            if (!empty($filtros[$key])) {
                $condiciones[] = $campo . ' = ' . $this->db->escape($filtros[$key]);
            }
        }

        if ($solo_medibles) {
            $condiciones[] = "unidad_medida IN ('M3', 'KWH', 'KW/H')";
            $condiciones[] = 'consumo > 0';
        }

        return ' WHERE ' . implode(' AND ', $condiciones);
    }

    private function servicio_case_sql()
    {
        return "
            CASE
                WHEN UPPER(proveedor) LIKE '%EDENOR%' OR unidad_medida IN ('KWH', 'KW/H') THEN 'Electricidad'
                WHEN UPPER(proveedor) LIKE '%AYSA%' THEN 'Agua'
                WHEN UPPER(proveedor) LIKE '%NATURGY%' THEN 'Gas'
                WHEN UPPER(proveedor) LIKE '%TELECOM%'
                    OR UPPER(proveedor) LIKE '%CLARO%'
                    OR UPPER(proveedor) LIKE '%FLOW%'
                    OR UPPER(proveedor) LIKE '%CABLEVISION%'
                    OR UPPER(proveedor) LIKE '%TELMEX%'
                    OR UPPER(proveedor) LIKE '%TELECENTRO%'
                THEN 'Telecom'
                ELSE 'Otros servicios'
            END
        ";
    }

    public function normalizar_filtros($input)
    {
        $filtros = array();

        $filtros['anio'] = !empty($input['anio']) ? (int) $input['anio'] : (int) date('Y');
        $filtros['mes'] = !empty($input['mes']) ? (int) $input['mes'] : null;
        $filtros['mes_desde'] = !empty($input['mes_desde']) ? (int) $input['mes_desde'] : null;
        $filtros['mes_hasta'] = !empty($input['mes_hasta']) ? (int) $input['mes_hasta'] : null;

        if ($filtros['mes'] < 1 || $filtros['mes'] > 12) {
            $filtros['mes'] = null;
        }
        if ($filtros['mes_desde'] < 1 || $filtros['mes_desde'] > 12) {
            $filtros['mes_desde'] = null;
        }
        if ($filtros['mes_hasta'] < 1 || $filtros['mes_hasta'] > 12) {
            $filtros['mes_hasta'] = null;
        }

        foreach ($this->dimensiones as $key => $campo) {
            $filtros[$key] = !empty($input[$key]) ? trim($input[$key]) : null;
        }

        if (!empty($input['id_proveedor']) && empty($filtros['proveedor'])) {
            $filtros['proveedor'] = trim($input['id_proveedor']);
        }

        return $filtros;
    }

    private function resolver_dimension($dimension)
    {
        return isset($this->dimensiones[$dimension]) ? $dimension : 'secretaria';
    }

    private function breadcrumb($filtros)
    {
        $items = array();
        foreach ($this->drilldown as $dimension) {
            if (!empty($filtros[$dimension])) {
                $items[] = array(
                    'dimension' => $dimension,
                    'valor' => $filtros[$dimension],
                );
            }
        }
        return $items;
    }

    private function filtros_mes_anterior($filtros)
    {
        if (empty($filtros['mes'])) {
            return null;
        }

        $nuevo = $filtros;
        $mes = (int) $filtros['mes'];
        $anio = (int) $filtros['anio'];

        if ($mes === 1) {
            $nuevo['mes'] = 12;
            $nuevo['anio'] = $anio - 1;
        } else {
            $nuevo['mes'] = $mes - 1;
        }

        return $nuevo;
    }

    private function filtros_anio_anterior($filtros)
    {
        if (empty($filtros['anio'])) {
            return null;
        }

        $nuevo = $filtros;
        $nuevo['anio'] = (int) $filtros['anio'] - 1;
        return $nuevo;
    }

    private function filtros_resumen_con_corte($filtros)
    {
        $filtros = $this->normalizar_filtros($filtros);

        if (!empty($filtros['mes']) || !empty($filtros['mes_desde']) || !empty($filtros['mes_hasta'])) {
            return $filtros;
        }

        $corte = $this->info_corte_ytd_representativo($filtros);
        $mes_hasta = (int) ($corte['mes_corte'] ?? 0);
        if ($mes_hasta > 0) {
            $filtros['mes_desde'] = 1;
            $filtros['mes_hasta'] = $mes_hasta;
        }

        return $filtros;
    }

    private function comparar_totales($actual, $comparado)
    {
        $actual_total = (float) ($actual['total'] ?? 0);
        $comparado_total = (float) ($comparado['total'] ?? 0);
        $delta = $actual_total - $comparado_total;
        $porcentaje = $comparado_total > 0 ? ($delta / $comparado_total) * 100 : null;

        return array(
            'total_comparado' => $comparado_total,
            'delta' => $delta,
            'porcentaje' => $porcentaje,
        );
    }

    private function kpis_vacios()
    {
        return array(
            'facturas' => 0,
            'total' => 0,
            'promedio' => 0,
            'proveedores' => 0,
            'secretarias' => 0,
        );
    }

    private function max_mes_con_datos($filtros)
    {
        $base = $filtros;
        unset($base['mes'], $base['mes_desde'], $base['mes_hasta']);
        $where = $this->where_sql($base);

        $sql = "
            SELECT MAX(mes_fc) AS mes
            FROM (" . $this->universo_sql() . ") U
            {$where}
        ";

        $row = $this->db->query($sql)->row();
        return $row && $row->mes ? (int) $row->mes : 0;
    }

    private function mes_corte_ytd_representativo($filtros)
    {
        $corte = $this->info_corte_ytd_representativo($filtros);
        return (int) ($corte['mes_corte'] ?? 0);
    }

    private function info_corte_ytd_representativo($filtros)
    {
        $base = $filtros;
        unset($base['mes'], $base['mes_desde'], $base['mes_hasta']);
        $where = $this->where_sql($base);

        $sql = "
            SELECT
                mes_fc AS mes,
                COUNT(*) AS facturas,
                SUM(importe) AS total
            FROM (" . $this->universo_sql() . ") U
            {$where}
            GROUP BY mes_fc
            ORDER BY mes_fc ASC
        ";

        $rows = $this->db->query($sql)->result_array();
        $mes_corte = 0;
        $mes_maximo_datos = 0;
        $meses_excluidos = array();
        $previos = array();
        $umbral_total = 0.60;

        foreach ($rows as $row) {
            $mes = (int) $row['mes'];
            $total = (float) $row['total'];
            $facturas = (int) $row['facturas'];
            $es_representativo = true;
            $promedio_total = null;
            $porcentaje_vs_promedio = null;

            if ($mes > $mes_maximo_datos) {
                $mes_maximo_datos = $mes;
            }

            if (count($previos) >= 3) {
                $avg_total = array_sum(array_column($previos, 'total')) / count($previos);

                if ($avg_total > 0) {
                    $promedio_total = $avg_total;
                    $porcentaje_vs_promedio = ($total / $avg_total) * 100;
                    $es_representativo = $total >= ($avg_total * $umbral_total);
                }
            }

            if (!$es_representativo) {
                $meses_excluidos[] = array(
                    'mes' => $mes,
                    'total' => $total,
                    'facturas' => $facturas,
                    'promedio_total_previo' => $promedio_total,
                    'porcentaje_vs_promedio' => $porcentaje_vs_promedio,
                );
                continue;
            }

            $mes_corte = $mes;
            $previos[] = array(
                'total' => $total,
                'facturas' => $facturas,
            );
        }

        if (!$mes_corte) {
            $mes_corte = $this->max_mes_con_datos($filtros);
        }

        return array(
            'mes_corte' => $mes_corte,
            'mes_maximo_datos' => $mes_maximo_datos,
            'meses_excluidos' => $meses_excluidos,
            'hay_meses_parciales' => $mes_maximo_datos > $mes_corte,
            'criterio_corte' => 'Se excluyen meses cuyo gasto esta por debajo del 60% del promedio de los meses representativos anteriores.',
        );
    }
}
