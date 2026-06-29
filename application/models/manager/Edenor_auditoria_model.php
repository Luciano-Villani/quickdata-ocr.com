<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Edenor_auditoria_model extends CI_Model
{
    private $tabla = '_electro_edenor_auditorias';
    private $tabla_detalle = '_electro_edenor_auditorias_detalle';

    public function parsear_txt($contenido)
    {
        $lineas = preg_split('/\r\n|\r|\n/', (string) $contenido);
        $detalles = array();
        $errores = array();
        $periodo_detectado = null;

        foreach ($lineas as $nro => $linea) {
            $linea = trim($linea);
            if ($linea === '') {
                continue;
            }

            if (!preg_match('/^VL(?P<periodo>\d{6})-(?P<tarifa>[^_]+)_(?P<fecha>\d{8})_(?P<importe>[+-]?\d+(?:\.\d+)?)_(?P<cuenta>\d+)_(?P<factura>.+)\.pdf$/i', $linea, $m)) {
                $errores[] = array(
                    'linea' => $nro + 1,
                    'texto' => $linea,
                    'error' => 'Formato no reconocido',
                );
                continue;
            }

            $periodo = $m['periodo'];
            if ($periodo_detectado === null) {
                $periodo_detectado = $periodo;
            } elseif ($periodo_detectado !== $periodo) {
                $errores[] = array(
                    'linea' => $nro + 1,
                    'texto' => $linea,
                    'error' => 'El archivo contiene mas de un periodo',
                );
                continue;
            }

            $fecha = substr($m['fecha'], 0, 4) . '-' . substr($m['fecha'], 4, 2) . '-' . substr($m['fecha'], 6, 2);
            $detalles[] = array(
                'periodo' => $periodo,
                'tarifa' => strtoupper(trim($m['tarifa'])),
                'fecha_archivo' => $fecha,
                'importe' => (float) $m['importe'],
                'nro_cuenta' => trim($m['cuenta']),
                'nro_factura' => trim($m['factura']),
                'nombre_pdf' => $linea,
                'linea_original' => $linea,
            );
        }

        return array(
            'periodo' => $periodo_detectado,
            'detalles' => $detalles,
            'errores' => $errores,
            'resumen' => $this->resumen_detalles($detalles),
        );
    }

    public function existe_periodo($periodo)
    {
        return $this->db->where('periodo', $periodo)->count_all_results($this->tabla) > 0;
    }

    public function actualizar_archivo_original($periodo, $archivo_original)
    {
        return $this->db
            ->where('periodo', $periodo)
            ->update($this->tabla, array('archivo_original' => $archivo_original));
    }

    public function importar_txt($nombre_archivo, $contenido, $user_id = null, $reemplazar = false)
    {
        $parseo = $this->parsear_txt($contenido);

        if (!$parseo['periodo'] || empty($parseo['detalles'])) {
            return array(
                'status' => 'error',
                'mensaje' => 'No se encontraron lineas validas para importar.',
                'errores' => $parseo['errores'],
            );
        }

        if (!empty($parseo['errores'])) {
            return array(
                'status' => 'error',
                'mensaje' => 'El archivo contiene lineas con formato no reconocido.',
                'errores' => $parseo['errores'],
                'periodo' => $parseo['periodo'],
            );
        }

        $periodo = $parseo['periodo'];
        if ($this->existe_periodo($periodo) && !$reemplazar) {
            return array(
                'status' => 'exists',
                'mensaje' => 'Ya existe una auditoria para el periodo ' . $this->periodo_label($periodo) . '.',
                'periodo' => $periodo,
                'resumen' => $parseo['resumen'],
            );
        }

        $cuentas = array();
        $cuentas_factura = array();
        foreach ($parseo['detalles'] as $detalle) {
            $clave_unica = $detalle['nro_cuenta'] . '|' . $detalle['nro_factura'];
            if (isset($cuentas_factura[$clave_unica])) {
                return array(
                    'status' => 'error',
                    'mensaje' => 'El TXT contiene duplicada la cuenta ' . $detalle['nro_cuenta'] . ' con la factura ' . $detalle['nro_factura'] . '.',
                    'periodo' => $periodo,
                );
            }
            $cuentas_factura[$clave_unica] = true;
            $cuentas[$detalle['nro_cuenta']] = true;
        }

        $this->db->trans_start();

        if ($reemplazar) {
            $auditoria_anterior = $this->db->where('periodo', $periodo)->get($this->tabla)->row();
            if ($auditoria_anterior) {
                $this->db->where('id_auditoria', (int) $auditoria_anterior->id)->delete($this->tabla_detalle);
                $this->db->where('id', (int) $auditoria_anterior->id)->delete($this->tabla);
            }
        }

        $anio = (int) substr($periodo, 0, 4);
        $mes = (int) substr($periodo, 4, 2);
        $this->db->insert($this->tabla, array(
            'periodo' => $periodo,
            'anio' => $anio,
            'mes' => $mes,
            'nombre_archivo' => $nombre_archivo,
            'archivo_original' => $nombre_archivo,
            'total_archivos' => count($parseo['detalles']),
            'total_cuentas' => count($cuentas),
            'user_add' => $user_id,
        ));

        $id_auditoria = (int) $this->db->insert_id();
        $batch = array();
        foreach ($parseo['detalles'] as $detalle) {
            $detalle['id_auditoria'] = $id_auditoria;
            $batch[] = $detalle;
        }
        $this->db->insert_batch($this->tabla_detalle, $batch);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return array(
                'status' => 'error',
                'mensaje' => 'No se pudo guardar la auditoria en la base.',
            );
        }

        return array(
            'status' => 'success',
            'mensaje' => 'Auditoria importada correctamente.',
            'periodo' => $periodo,
            'periodo_label' => $this->periodo_label($periodo),
            'resumen' => $parseo['resumen'],
        );
    }

    public function periodos()
    {
        $rows = $this->db
            ->select('periodo, anio, mes, total_archivos, total_cuentas, fecha_add')
            ->from($this->tabla)
            ->order_by('periodo', 'DESC')
            ->get()->result_array();

        foreach ($rows as &$row) {
            $row['label'] = $this->periodo_label($row['periodo']);
        }

        return $rows;
    }

    public function periodo_anterior($periodo)
    {
        $row = $this->db
            ->select('periodo')
            ->from($this->tabla)
            ->where('periodo <', $periodo)
            ->order_by('periodo', 'DESC')
            ->limit(1)
            ->get()->row();

        return $row ? $row->periodo : null;
    }

    public function comparar($periodo_actual, $periodo_base = null)
    {
        if (!$periodo_actual) {
            $ultimo = $this->db->select('periodo')->from($this->tabla)->order_by('periodo', 'DESC')->limit(1)->get()->row();
            $periodo_actual = $ultimo ? $ultimo->periodo : null;
        }

        if (!$periodo_actual) {
            return $this->comparacion_vacia();
        }

        if (!$periodo_base) {
            $periodo_base = $this->periodo_anterior($periodo_actual);
        }

        $actual = $this->detalle_periodo($periodo_actual);
        $base = $periodo_base ? $this->detalle_periodo($periodo_base) : array();
        $map_actual = $this->mapear_por_cuenta($actual);
        $map_base = $this->mapear_por_cuenta($base);
        $map_actual = $this->agregar_periodicidad_indexaciones($map_actual);
        $map_base = $this->agregar_periodicidad_indexaciones($map_base);

        $nuevas = array();
        $faltantes = array();
        $bimestrales = array();
        $cambios_tarifa = array();

        foreach ($map_actual as $cuenta => $row) {
            if (!isset($map_base[$cuenta])) {
                $nuevas[] = $row;
                continue;
            }

            if ($row['tarifa'] !== $map_base[$cuenta]['tarifa']) {
                $tmp = $row;
                $tmp['tarifa_anterior'] = $map_base[$cuenta]['tarifa'];
                $cambios_tarifa[] = $tmp;
            }
        }

        foreach ($map_base as $cuenta => $row) {
            if (!isset($map_actual[$cuenta])) {
                if ($this->es_faltante_por_periodicidad($row, $periodo_base, $periodo_actual)) {
                    $bimestrales[] = $row;
                } else {
                    $faltantes[] = $row;
                }
            }
        }

        return array(
            'periodo_actual' => $periodo_actual,
            'periodo_actual_label' => $this->periodo_label($periodo_actual),
            'periodo_base' => $periodo_base,
            'periodo_base_label' => $periodo_base ? $this->periodo_label($periodo_base) : 'Sin periodo comparable',
            'kpis' => array(
                'actual_total' => count($map_actual),
                'base_total' => count($map_base),
                'variacion' => count($map_actual) - count($map_base),
                'nuevas' => count($nuevas),
                'faltantes' => count($faltantes),
                'bimestrales' => count($bimestrales),
                'cambios_tarifa' => count($cambios_tarifa),
            ),
            'tarifas_actual' => $this->resumen_tarifas(array_values($map_actual)),
            'tarifas_base' => $this->resumen_tarifas(array_values($map_base)),
            'tarifas_comparativo' => $this->resumen_tarifas_comparativo($map_actual, $map_base),
            'nuevas' => array_values($nuevas),
            'faltantes' => array_values($faltantes),
            'bimestrales' => array_values($bimestrales),
            'cambios_tarifa' => array_values($cambios_tarifa),
            'movimientos' => $this->movimientos_operativos($nuevas, $faltantes, $bimestrales, $cambios_tarifa),
            'actual' => array_values($map_actual),
            'base' => array_values($map_base),
        );
    }

    public function evolutivo()
    {
        $periodos = array_reverse($this->periodos());
        $rows = array();
        $previo = null;

        foreach ($periodos as $periodo) {
            $detalle = $this->detalle_periodo($periodo['periodo']);
            $map_detalle = $this->mapear_por_cuenta($detalle);
            $resumen = $this->resumen_tarifas(array_values($map_detalle));
            $comparacion = $previo ? $this->comparar($periodo['periodo'], $previo) : null;
            $rows[] = array(
                'periodo' => $periodo['periodo'],
                'label' => $periodo['label'],
                'total' => count($map_detalle),
                'AP' => isset($resumen['AP']) ? $resumen['AP']['cantidad'] : 0,
                'T1' => isset($resumen['T1']) ? $resumen['T1']['cantidad'] : 0,
                'T2' => isset($resumen['T2']) ? $resumen['T2']['cantidad'] : 0,
                'T3' => isset($resumen['T3']) ? $resumen['T3']['cantidad'] : 0,
                'nuevas' => $comparacion ? $comparacion['kpis']['nuevas'] : 0,
                'faltantes' => $comparacion ? $comparacion['kpis']['faltantes'] : 0,
                'bimestrales' => $comparacion ? $comparacion['kpis']['bimestrales'] : 0,
                'cambios_tarifa' => $comparacion ? $comparacion['kpis']['cambios_tarifa'] : 0,
            );
            $previo = $periodo['periodo'];
        }

        return array_reverse($rows);
    }

    private function detalle_periodo($periodo)
    {
        return $this->db
            ->select('periodo, tarifa, fecha_archivo, importe, nro_cuenta, nro_factura, nombre_pdf')
            ->from($this->tabla_detalle)
            ->where('periodo', $periodo)
            ->order_by('tarifa ASC, nro_cuenta ASC')
            ->get()->result_array();
    }

    private function mapear_por_cuenta($rows)
    {
        $map = array();
        foreach ($rows as $row) {
            $cuenta = $row['nro_cuenta'];
            $tarifa_operativa = $this->tarifa_operativa($row['tarifa']);
            if (!isset($map[$cuenta])) {
                $row['importe'] = (float) $row['importe'];
                $row['_tarifas'] = array();
                if ($tarifa_operativa !== null) {
                    $row['_tarifas'][$tarifa_operativa] = true;
                    $row['tarifa'] = $tarifa_operativa;
                }
                $row['_facturas'] = array($row['nro_factura']);
                $map[$cuenta] = $row;
                continue;
            }

            $map[$cuenta]['importe'] += (float) $row['importe'];
            if ($tarifa_operativa !== null) {
                $map[$cuenta]['_tarifas'][$tarifa_operativa] = true;
            }
            $map[$cuenta]['_facturas'][] = $row['nro_factura'];
            if (!empty($map[$cuenta]['_tarifas'])) {
                $map[$cuenta]['tarifa'] = implode(' / ', array_keys($map[$cuenta]['_tarifas']));
            }
            $map[$cuenta]['nro_factura'] = implode(' / ', $map[$cuenta]['_facturas']);
        }
        return $map;
    }

    private function agregar_periodicidad_indexaciones($map)
    {
        if (empty($map)) {
            return $map;
        }

        $cuentas = array_keys($map);
        $rows = $this->db
            ->select('nro_cuenta, MAX(periodicidad_meses) AS periodicidad_meses')
            ->from('_indexaciones_canon')
            ->where_in('nro_cuenta', $cuentas)
            ->group_by('nro_cuenta')
            ->get()->result_array();

        $periodicidades = array();
        foreach ($rows as $row) {
            $periodicidades[$row['nro_cuenta']] = max(1, (int) $row['periodicidad_meses']);
        }

        foreach ($map as $cuenta => &$row) {
            $row['periodicidad_meses'] = isset($periodicidades[$cuenta]) ? $periodicidades[$cuenta] : 1;
        }
        unset($row);

        return $map;
    }

    private function es_faltante_por_periodicidad($row, $periodo_base, $periodo_actual)
    {
        $periodicidad = isset($row['periodicidad_meses']) ? (int) $row['periodicidad_meses'] : 1;
        if ($periodicidad <= 1 || !$periodo_base || !$periodo_actual) {
            return false;
        }

        $diferencia_meses = $this->diferencia_meses_periodo($periodo_base, $periodo_actual);
        if ($diferencia_meses <= 0) {
            return false;
        }

        return ($diferencia_meses % $periodicidad) !== 0;
    }

    private function diferencia_meses_periodo($periodo_base, $periodo_actual)
    {
        if (strlen((string) $periodo_base) !== 6 || strlen((string) $periodo_actual) !== 6) {
            return 0;
        }

        $anio_base = (int) substr($periodo_base, 0, 4);
        $mes_base = (int) substr($periodo_base, 4, 2);
        $anio_actual = (int) substr($periodo_actual, 0, 4);
        $mes_actual = (int) substr($periodo_actual, 4, 2);

        return (($anio_actual * 12) + $mes_actual) - (($anio_base * 12) + $mes_base);
    }

    private function resumen_detalles($detalles)
    {
        return array(
            'total_archivos' => count($detalles),
            'total_cuentas' => count($this->mapear_por_cuenta($detalles)),
            'tarifas' => $this->resumen_tarifas($detalles),
        );
    }

    private function resumen_tarifas($detalles)
    {
        $resumen = array();
        foreach ($detalles as $detalle) {
            $tarifa = $this->tarifa_operativa($detalle['tarifa']);
            if ($tarifa === null) {
                continue;
            }
            if (!isset($resumen[$tarifa])) {
                $resumen[$tarifa] = array('tarifa' => $tarifa, 'cantidad' => 0, 'importe' => 0);
            }
            $resumen[$tarifa]['cantidad']++;
            $resumen[$tarifa]['importe'] += (float) $detalle['importe'];
        }
        ksort($resumen);
        return $resumen;
    }

    private function resumen_tarifas_comparativo($actual, $base)
    {
        $tarifas = array('AP', 'T1', 'T2', 'T3');
        $resumen_actual = $this->resumen_tarifas(array_values($actual));
        $resumen_base = $this->resumen_tarifas(array_values($base));
        $rows = array();

        foreach ($tarifas as $tarifa) {
            $cant_actual = isset($resumen_actual[$tarifa]) ? (int) $resumen_actual[$tarifa]['cantidad'] : 0;
            $cant_base = isset($resumen_base[$tarifa]) ? (int) $resumen_base[$tarifa]['cantidad'] : 0;
            $rows[] = array(
                'tarifa' => $tarifa,
                'actual' => $cant_actual,
                'base' => $cant_base,
                'variacion' => $cant_actual - $cant_base,
            );
        }

        return $rows;
    }

    private function movimientos_operativos($nuevas, $faltantes, $bimestrales, $cambios_tarifa)
    {
        $rows = array();
        foreach ($nuevas as $row) {
            $row['tipo_movimiento'] = 'Alta';
            $row['tarifa_anterior'] = '-';
            $row['tarifa_actual'] = $row['tarifa'];
            $rows[] = $row;
        }
        foreach ($faltantes as $row) {
            $row['tipo_movimiento'] = 'Baja';
            $row['tarifa_anterior'] = $row['tarifa'];
            $row['tarifa_actual'] = '-';
            $rows[] = $row;
        }
        foreach ($bimestrales as $row) {
            $row['tipo_movimiento'] = 'Bimestral';
            $row['tarifa_anterior'] = $row['tarifa'];
            $row['tarifa_actual'] = '-';
            $rows[] = $row;
        }
        foreach ($cambios_tarifa as $row) {
            $row['tipo_movimiento'] = 'Recategorizada';
            $row['tarifa_actual'] = $row['tarifa'];
            $rows[] = $row;
        }

        return $rows;
    }

    private function tarifa_operativa($tarifa)
    {
        $tarifa = strtoupper(trim((string) $tarifa));
        if ($tarifa === '' || strpos($tarifa, 'OTROS') !== false) {
            return null;
        }
        return $tarifa;
    }

    private function periodo_label($periodo)
    {
        if (!$periodo || strlen($periodo) !== 6) {
            return '-';
        }

        $meses = array(
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        );

        $anio = substr($periodo, 0, 4);
        $mes = (int) substr($periodo, 4, 2);
        return (isset($meses[$mes]) ? $meses[$mes] : $mes) . ' ' . $anio;
    }

    private function comparacion_vacia()
    {
        return array(
            'periodo_actual' => null,
            'periodo_actual_label' => '-',
            'periodo_base' => null,
            'periodo_base_label' => '-',
            'kpis' => array(
                'actual_total' => 0,
                'base_total' => 0,
                'variacion' => 0,
                'nuevas' => 0,
                'faltantes' => 0,
                'bimestrales' => 0,
                'cambios_tarifa' => 0,
            ),
            'tarifas_actual' => array(),
            'tarifas_base' => array(),
            'tarifas_comparativo' => array(),
            'nuevas' => array(),
            'faltantes' => array(),
            'bimestrales' => array(),
            'cambios_tarifa' => array(),
            'movimientos' => array(),
            'actual' => array(),
            'base' => array(),
        );
    }
}
