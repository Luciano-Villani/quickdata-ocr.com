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
        'objeto' => 'objeto',
        'unidad_medida' => 'unidad_medida',
    );

    private $drilldown = array(
        'secretaria',
        'programa',
        'proyecto',
        'dependencia',
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
            ),
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

        $mes_hasta = $this->mes_corte_ytd_representativo($filtros);
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
        $previos = array();

        foreach ($rows as $row) {
            $mes = (int) $row['mes'];
            $total = (float) $row['total'];
            $facturas = (int) $row['facturas'];
            $es_representativo = true;

            if (count($previos) >= 3) {
                $avg_total = array_sum(array_column($previos, 'total')) / count($previos);
                $avg_facturas = array_sum(array_column($previos, 'facturas')) / count($previos);

                if ($avg_total > 0 && $avg_facturas > 0) {
                    $es_representativo = $total >= ($avg_total * 0.25) || $facturas >= ($avg_facturas * 0.25);
                }
            }

            if (!$es_representativo) {
                continue;
            }

            $mes_corte = $mes;
            $previos[] = array(
                'total' => $total,
                'facturas' => $facturas,
            );
        }

        return $mes_corte ?: $this->max_mes_con_datos($filtros);
    }
}
