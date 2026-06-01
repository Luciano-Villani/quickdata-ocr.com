<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vencimientos_model extends CI_Model
{
    private function tablas($modulo)
    {
        if ($modulo === 'electromecanica') {
            return array(
                'indexaciones' => '_indexaciones_canon',
                'proveedores' => '_proveedores_canon',
                'dependencias' => '_dependencias_canon',
                'consolidados' => '_consolidados_canon',
                'datos_api' => '_datos_api_canon',
                'detalle_lectura' => 'Electromecanica/Lecturas/Views/',
                'detalle_consolidado' => 'Electromecanica/Consolidados/ver/',
            );
        }

        return array(
            'indexaciones' => '_indexaciones',
            'proveedores' => '_proveedores',
            'dependencias' => '_dependencias',
            'consolidados' => '_consolidados',
            'datos_api' => '_datos_api',
            'detalle_lectura' => 'Admin/Lecturas/Views/',
            'detalle_consolidado' => 'Admin/Consolidados/ver/',
        );
    }

    public function get_calendario($modulo, $anio = null, $mes = null)
    {
        $anio = $anio ? (int) $anio : (int) date('Y');
        $mes = $mes ? (int) $mes : (int) date('n');

        $tablas = $this->tablas($modulo);
        $inicioMes = new DateTime(sprintf('%04d-%02d-01', $anio, $mes));
        $finMes = clone $inicioMes;
        $finMes->modify('last day of this month');
        $hoy = new DateTime(date('Y-m-d'));
        $limiteAlerta = clone $hoy;
        $limiteAlerta->modify('+7 days');

        $cuentas = $this->get_cuentas($tablas);
        $ids = array_keys($cuentas);
        $consolidadosAnio = $this->get_consolidados_anio($tablas, $ids, $anio);
        $ultimos = $this->get_ultimos_consolidados_hasta($tablas, $ids, $finMes);
        $lecturasAnio = $this->get_lecturas_pendientes_anio($tablas, $cuentas, $anio);
        $facturasCuenta = $this->get_facturas_cuenta($tablas, $cuentas, $anio);

        $filas = array();
        $historicos = array();
        $kpis = array(
            'obligaciones' => 0,
            'consolidadas' => 0,
            'subidas' => 0,
            'por_consolidar' => 0,
        );
        $issues = array(
            'vencen_7' => 0,
            'vencidas' => 0,
            'sin_actividad' => 0,
        );

        foreach ($cuentas as $idIndexador => $cuenta) {
            $claveCuenta = $this->clave_cuenta($cuenta->nro_cuenta);
            $consolidadoMes = isset($consolidadosAnio[$idIndexador][$mes]) ? $consolidadosAnio[$idIndexador][$mes] : null;
            $lecturaMes = isset($lecturasAnio[$claveCuenta][$mes]) ? $lecturasAnio[$claveCuenta][$mes] : null;
            $ultimo = isset($ultimos[$idIndexador]) ? $ultimos[$idIndexador] : null;
            $fechaUltimoPago = $ultimo ? $this->fecha_valida($ultimo->fecha_vencimiento) : null;
            $fechaEsperada = null;
            $ciclosPendientes = 0;
            $origen = 'estimada';
            $url = '';
            $estado = '';
            $estadoLabel = '';
            $fechaSubida = '';
            $fechaConsolidado = '';
            $fechaUltimaConsolidacion = $ultimo ? $this->normalizar_fecha_salida($ultimo->fecha_consolidado) : '';
            $nroFactura = '';
            $importe = '';

            if ($consolidadoMes) {
                $fechaEsperada = $this->fecha_valida($consolidadoMes->fecha_vencimiento);
                $estado = 'consolidada';
                $estadoLabel = 'Consolidada';
                $origen = 'consolidada';
                $url = base_url($tablas['detalle_consolidado'] . $consolidadoMes->id);
                $fechaSubida = $this->normalizar_fecha_salida($consolidadoMes->fecha_alta);
                $fechaConsolidado = $this->normalizar_fecha_salida($consolidadoMes->fecha_consolidado);
                $fechaUltimaConsolidacion = $fechaConsolidado;
                $nroFactura = $consolidadoMes->nro_factura;
                $importe = $consolidadoMes->importe;
                $kpis['consolidadas']++;
                $kpis['subidas']++;
            } elseif ($lecturaMes) {
                $fechaEsperada = $this->fecha_valida($lecturaMes->vencimiento_del_pago);
                if (!$fechaEsperada && $fechaUltimoPago) {
                    $fechaEsperada = $this->fecha_esperada_hasta_mes($fechaUltimoPago, (int) $cuenta->periodicidad_meses, $finMes, $ciclosPendientes);
                }
                if (!$fechaEsperada) {
                    $fechaEsperada = clone $inicioMes;
                }
                $estado = $fechaEsperada < $hoy ? 'vencida_subida' : 'subida_pendiente';
                $estadoLabel = $fechaEsperada < $hoy ? 'Subida vencida sin consolidar' : 'Subida sin consolidar';
                $origen = 'ocr';
                $url = base_url($tablas['detalle_lectura'] . $lecturaMes->id);
                $fechaSubida = $this->normalizar_fecha_salida($lecturaMes->fecha_alta);
                $nroFactura = $lecturaMes->nro_factura;
                $importe = $lecturaMes->total_importe;
                $kpis['subidas']++;
                $kpis['por_consolidar']++;
            } elseif ($fechaUltimoPago) {
                $cobertura = $this->cobertura_ciclos(
                    isset($facturasCuenta[$idIndexador]) ? $facturasCuenta[$idIndexador] : array(),
                    (int) $cuenta->periodicidad_meses,
                    $finMes
                );
                if (!empty($cobertura['cubierta'])) {
                    continue;
                }

                $fechaEsperada = $this->fecha_esperada_hasta_mes($fechaUltimoPago, (int) $cuenta->periodicidad_meses, $finMes, $ciclosPendientes);
                if (!$fechaEsperada || $ciclosPendientes < 1) {
                    continue;
                }

                if ($ciclosPendientes >= 2) {
                    $estado = 'sin_actividad';
                    $estadoLabel = $ciclosPendientes . ' ciclos sin factura';
                    $fechaEsperada = $this->primer_fecha_pendiente($fechaUltimoPago, (int) $cuenta->periodicidad_meses);
                } elseif ($fechaEsperada < $hoy) {
                    $estado = 'vencida_sin_subir';
                    $estadoLabel = 'Vencida sin subir';
                } else {
                    $estado = 'sin_subir';
                    $estadoLabel = 'Sin subir';
                }
                $url = $ultimo ? base_url($tablas['detalle_consolidado'] . $ultimo->id) : '';
            } else {
                continue;
            }

            if (!$fechaEsperada) {
                continue;
            }

            $sinActividad = $estado === 'sin_actividad';
            $enMes = !$sinActividad && $fechaEsperada->format('Y-m') === $inicioMes->format('Y-m');
            $alerta7 = $enMes && $estado !== 'consolidada' && $fechaEsperada >= $hoy && $fechaEsperada <= $limiteAlerta;
            $vencida = $enMes && in_array($estado, array('vencida_sin_subir', 'vencida_subida'), true);

            if ($alerta7) {
                $issues['vencen_7']++;
            }
            if ($vencida) {
                $issues['vencidas']++;
            }
            if ($sinActividad) {
                $issues['sin_actividad']++;
            }

            $fila = $this->fila_base($cuenta, $modulo);
            $fila['id_evento'] = $modulo . '-' . $idIndexador . '-' . $anio . '-' . $mes;
            $fila['fecha_esperada'] = $fechaEsperada->format('Y-m-d');
            $fila['dia_mes'] = (int) $fechaEsperada->format('j');
            $fila['en_mes'] = $enMes;
            $fila['fecha_ultimo_pago'] = $fechaUltimaConsolidacion;
            $fila['fecha_subida'] = $fechaSubida;
            $fila['fecha_consolidado'] = $fechaConsolidado;
            $fila['estado'] = $estado;
            $fila['estado_label'] = $estadoLabel;
            $fila['alerta_7'] = $alerta7;
            $fila['vencida'] = $vencida;
            $fila['sin_actividad'] = $sinActividad;
            $fila['origen'] = $origen;
            $fila['url'] = $url;
            $fila['nro_factura'] = $nroFactura;
            $fila['importe'] = $importe;
            $fila['ciclos_pendientes'] = $ciclosPendientes;

            $filas[] = $fila;
            $historicos[$idIndexador] = $this->historico_cuenta($cuenta, $consolidadosAnio, $lecturasAnio, $anio, $tablas);
        }

        usort($filas, array($this, 'ordenar_filas'));
        foreach ($filas as $fila) {
            if (empty($fila['sin_actividad'])) {
                $kpis['obligaciones']++;
            }
        }

        return array(
            'anio' => $anio,
            'mes' => $mes,
            'nombre_mes' => $this->nombre_mes($mes),
            'filas' => $filas,
            'dias' => $this->resumen_dias($filas, $anio, $mes),
            'kpis' => $kpis,
            'issues' => $issues,
            'historicos' => $historicos,
            'filtros' => $this->filtros($filas),
        );
    }

    public function get_alertas_topbar($modulo)
    {
        $calendario = $this->get_calendario($modulo);
        $issues = $calendario['issues'];
        $vencidas = isset($issues['vencidas']) ? (int) $issues['vencidas'] : 0;
        $vencen7 = isset($issues['vencen_7']) ? (int) $issues['vencen_7'] : 0;
        $base = $modulo === 'electromecanica' ? 'Electromecanica/Vencimientos' : 'Admin/Vencimientos';

        return array(
            'modulo' => $modulo,
            'total' => $vencidas + $vencen7,
            'vencidas' => $vencidas,
            'vencen_7' => $vencen7,
            'url_vencidas' => base_url($base . '?estado=vencidas'),
            'url_vencen_7' => base_url($base . '?estado=vencen_7'),
            'url_calendario' => base_url($base),
            'firma' => $modulo . ':vencidas:' . $vencidas . '|vencen_7:' . $vencen7 . '|' . date('Y-m-d'),
            'actualizado' => date('d/m/Y H:i'),
        );
    }

    private function get_cuentas($tablas)
    {
        $this->db->select('I.id AS id_indexador, I.id_proveedor, I.nro_cuenta, I.acuerdo_pago, I.periodicidad_meses, I.control_vencimiento, I.dias_alerta, P.nombre AS proveedor, TP.tip_nombre AS tipo_pago, S.secretaria, D.dependencia');
        $this->db->from($tablas['indexaciones'] . ' I');
        $this->db->join($tablas['proveedores'] . ' P', 'P.id = I.id_proveedor', 'left');
        $this->db->join('_tipo_pago TP', 'TP.tip_id = I.tipo_pago', 'left');
        $this->db->join('_secretarias S', 'S.id = I.id_secretaria', 'left');
        $this->db->join($tablas['dependencias'] . ' D', 'D.id = I.id_dependencia', 'left');
        $this->db->where('I.control_vencimiento', 1);
        $this->db->where("(UPPER(TRIM(TP.tip_nombre)) = 'DEBITO' OR UPPER(TRIM(TP.tip_nombre)) LIKE 'OP%')", null, false);
        $this->db->order_by('P.nombre', 'ASC');
        $this->db->order_by('I.nro_cuenta', 'ASC');

        $cuentas = array();
        foreach ($this->db->get()->result() as $cuenta) {
            $cuentas[$cuenta->id_indexador] = $cuenta;
        }
        return $cuentas;
    }

    private function get_consolidados_anio($tablas, $ids, $anio)
    {
        if (empty($ids)) {
            return array();
        }

        $this->db->select('C.id, C.id_indexador, C.id_lectura_api, C.fecha_vencimiento, C.fecha_alta, C.fecha_consolidado, C.nro_factura, C.importe, C.nro_cuenta, C.periodo_contable');
        $this->db->from($tablas['consolidados'] . ' C');
        $this->db->where_in('C.id_indexador', $ids);
        $this->db->where('YEAR(C.fecha_vencimiento)', $anio);
        $this->db->order_by('C.fecha_vencimiento', 'DESC');
        $this->db->order_by('C.id', 'DESC');

        $resultado = array();
        foreach ($this->db->get()->result() as $consolidado) {
            $fecha = $this->fecha_valida($consolidado->fecha_vencimiento);
            if (!$fecha) {
                continue;
            }
            $mes = (int) $fecha->format('n');
            if (!isset($resultado[$consolidado->id_indexador])) {
                $resultado[$consolidado->id_indexador] = array();
            }
            if (!isset($resultado[$consolidado->id_indexador][$mes])) {
                $resultado[$consolidado->id_indexador][$mes] = $consolidado;
            }
        }
        return $resultado;
    }

    private function get_ultimos_consolidados_hasta($tablas, $ids, DateTime $hasta)
    {
        if (empty($ids)) {
            return array();
        }

        $this->db->select('C.id, C.id_indexador, C.id_lectura_api, C.fecha_vencimiento, C.fecha_consolidado');
        $this->db->from($tablas['consolidados'] . ' C');
        $this->db->where_in('C.id_indexador', $ids);
        $this->db->where('C.fecha_vencimiento <=', $hasta->format('Y-m-d'));
        $this->db->order_by('C.id_indexador', 'ASC');
        $this->db->order_by('C.fecha_vencimiento', 'DESC');
        $this->db->order_by('C.id', 'DESC');

        $ultimos = array();
        foreach ($this->db->get()->result() as $consolidado) {
            if (!isset($ultimos[$consolidado->id_indexador]) && $this->fecha_valida($consolidado->fecha_vencimiento)) {
                $ultimos[$consolidado->id_indexador] = $consolidado;
            }
        }
        return $ultimos;
    }

    private function get_lecturas_pendientes_anio($tablas, $cuentas, $anio)
    {
        if (empty($cuentas)) {
            return array();
        }

        $numeros = array();
        foreach ($cuentas as $cuenta) {
            $numeros[] = $cuenta->nro_cuenta;
        }

        $this->db->select('id, nro_cuenta, vencimiento_del_pago, fecha_alta, fecha_consolidado, nro_factura, total_importe');
        $this->db->from($tablas['datos_api']);
        $this->db->where('consolidado', 0);
        $this->db->where_in('nro_cuenta', $numeros);
        $this->db->where('YEAR(vencimiento_del_pago)', $anio);
        $this->db->order_by('vencimiento_del_pago', 'DESC');
        $this->db->order_by('id', 'DESC');

        $resultado = array();
        foreach ($this->db->get()->result() as $lectura) {
            $fecha = $this->fecha_valida($lectura->vencimiento_del_pago);
            if (!$fecha) {
                continue;
            }
            $clave = $this->clave_cuenta($lectura->nro_cuenta);
            $mes = (int) $fecha->format('n');
            if (!isset($resultado[$clave])) {
                $resultado[$clave] = array();
            }
            if (!isset($resultado[$clave][$mes])) {
                $resultado[$clave][$mes] = $lectura;
            }
        }
        return $resultado;
    }

    private function get_facturas_cuenta($tablas, $cuentas, $anio)
    {
        if (empty($cuentas)) {
            return array();
        }

        $ids = array_keys($cuentas);
        $mapaCuentas = array();
        foreach ($cuentas as $idIndexador => $cuenta) {
            $mapaCuentas[$this->clave_proveedor_cuenta($cuenta->id_proveedor, $cuenta->nro_cuenta)] = $idIndexador;
        }

        $facturas = array();

        $this->db->select('C.id, C.id_indexador, C.fecha_vencimiento, C.fecha_alta, C.fecha_consolidado, C.nro_factura');
        $this->db->from($tablas['consolidados'] . ' C');
        $this->db->where_in('C.id_indexador', $ids);
        $this->db->group_start();
        $this->db->where('YEAR(C.fecha_vencimiento) >=', $anio - 1);
        $this->db->where('YEAR(C.fecha_vencimiento) <=', $anio + 1);
        $this->db->or_where('YEAR(C.fecha_alta)', $anio);
        $this->db->group_end();

        foreach ($this->db->get()->result() as $row) {
            $fecha = $this->fecha_valida($row->fecha_vencimiento);
            if (!$fecha) {
                continue;
            }
            $facturas[$row->id_indexador][] = (object) array(
                'id' => $row->id,
                'origen' => 'consolidada',
                'fecha_vencimiento' => $fecha,
                'fecha_alta' => $this->fecha_valida($row->fecha_alta),
                'fecha_consolidado' => $this->fecha_valida($row->fecha_consolidado),
                'nro_factura' => $row->nro_factura,
            );
        }

        $this->db->select('D.id, D.id_proveedor, D.nro_cuenta, D.vencimiento_del_pago, D.fecha_alta, D.fecha_consolidado, D.nro_factura');
        $this->db->from($tablas['datos_api'] . ' D');
        $this->db->where('D.consolidado', 0);
        $this->db->group_start();
        $this->db->where('YEAR(D.vencimiento_del_pago) >=', $anio - 1);
        $this->db->where('YEAR(D.vencimiento_del_pago) <=', $anio + 1);
        $this->db->or_where('YEAR(D.fecha_alta)', $anio);
        $this->db->group_end();

        foreach ($this->db->get()->result() as $row) {
            $idIndexador = isset($mapaCuentas[$this->clave_proveedor_cuenta($row->id_proveedor, $row->nro_cuenta)])
                ? $mapaCuentas[$this->clave_proveedor_cuenta($row->id_proveedor, $row->nro_cuenta)]
                : null;
            $fecha = $this->fecha_valida($row->vencimiento_del_pago);
            if (!$idIndexador || !$fecha) {
                continue;
            }
            $facturas[$idIndexador][] = (object) array(
                'id' => $row->id,
                'origen' => 'ocr',
                'fecha_vencimiento' => $fecha,
                'fecha_alta' => $this->fecha_valida($row->fecha_alta),
                'fecha_consolidado' => $this->fecha_valida($row->fecha_consolidado),
                'nro_factura' => $row->nro_factura,
            );
        }

        foreach ($facturas as &$items) {
            usort($items, array($this, 'ordenar_facturas_por_vencimiento'));
        }
        unset($items);

        return $facturas;
    }

    private function cobertura_ciclos($facturas, $periodicidad, DateTime $finMes)
    {
        if (empty($facturas)) {
            return array('cubierta' => false, 'esperadas' => 0, 'registradas' => 0);
        }

        $periodicidad = max(1, (int) $periodicidad);
        $anio = (int) $finMes->format('Y');
        $primerFacturaAnio = null;
        $ultimaAnterior = null;
        $registradas = 0;

        foreach ($facturas as $factura) {
            if (empty($factura->fecha_vencimiento)) {
                continue;
            }

            $fecha = $factura->fecha_vencimiento;
            if ((int) $fecha->format('Y') === $anio && (!$primerFacturaAnio || $fecha < $primerFacturaAnio)) {
                $primerFacturaAnio = clone $fecha;
            }
            if ($fecha < new DateTime($anio . '-01-01') && (!$ultimaAnterior || $fecha > $ultimaAnterior)) {
                $ultimaAnterior = clone $fecha;
            }
            if ((int) $fecha->format('Y') === $anio && ($fecha <= $finMes || (!empty($factura->fecha_alta) && $factura->fecha_alta <= $finMes))) {
                $registradas++;
            }
        }

        if ($primerFacturaAnio) {
            $inicio = $primerFacturaAnio;
        } elseif ($ultimaAnterior) {
            $inicio = clone $ultimaAnterior;
            do {
                $inicio->modify('+' . $periodicidad . ' month');
            } while ($inicio < new DateTime($anio . '-01-01'));
        } else {
            return array('cubierta' => true, 'esperadas' => 0, 'registradas' => $registradas);
        }

        $esperadas = 0;
        $cursor = clone $inicio;
        while ($cursor <= $finMes) {
            $esperadas++;
            $cursor->modify('+' . $periodicidad . ' month');
        }

        return array(
            'cubierta' => $registradas >= $esperadas,
            'esperadas' => $esperadas,
            'registradas' => $registradas,
        );
    }

    private function fecha_esperada_hasta_mes(DateTime $fechaUltimoPago, $periodicidad, DateTime $finMes, &$ciclosPendientes)
    {
        $periodicidad = max(1, (int) $periodicidad);
        $siguiente = clone $fechaUltimoPago;
        $siguiente->modify('+' . $periodicidad . ' month');
        $ultimaEsperada = null;
        $ciclosPendientes = 0;

        while ($siguiente <= $finMes) {
            $ultimaEsperada = clone $siguiente;
            $ciclosPendientes++;
            $siguiente->modify('+' . $periodicidad . ' month');
        }

        return $ultimaEsperada;
    }

    private function primer_fecha_pendiente(DateTime $fechaUltimoPago, $periodicidad)
    {
        $primera = clone $fechaUltimoPago;
        $primera->modify('+' . max(1, (int) $periodicidad) . ' month');
        return $primera;
    }

    private function historico_cuenta($cuenta, $consolidadosAnio, $lecturasAnio, $anio, $tablas)
    {
        $idIndexador = $cuenta->id_indexador;
        $claveCuenta = $this->clave_cuenta($cuenta->nro_cuenta);
        $historico = array();

        for ($mes = 1; $mes <= 12; $mes++) {
            $consolidado = isset($consolidadosAnio[$idIndexador][$mes]) ? $consolidadosAnio[$idIndexador][$mes] : null;
            $lectura = isset($lecturasAnio[$claveCuenta][$mes]) ? $lecturasAnio[$claveCuenta][$mes] : null;

            $estado = 'sin_datos';
            $estadoLabel = 'Sin datos';
            $fechaVencimiento = '';
            $fechaSubida = '';
            $fechaConsolidado = '';
            $nroFactura = '';
            $importe = '';
            $url = '';

            if ($consolidado) {
                $estado = 'consolidada';
                $estadoLabel = 'Consolidada';
                $fechaVencimiento = $this->normalizar_fecha_salida($consolidado->fecha_vencimiento);
                $fechaSubida = $this->normalizar_fecha_salida($consolidado->fecha_alta);
                $fechaConsolidado = $this->normalizar_fecha_salida($consolidado->fecha_consolidado);
                $nroFactura = $consolidado->nro_factura;
                $importe = $consolidado->importe;
                $url = base_url($tablas['detalle_consolidado'] . $consolidado->id);
            } elseif ($lectura) {
                $estado = 'subida_pendiente';
                $estadoLabel = 'Subida sin consolidar';
                $fechaVencimiento = $this->normalizar_fecha_salida($lectura->vencimiento_del_pago);
                $fechaSubida = $this->normalizar_fecha_salida($lectura->fecha_alta);
                $nroFactura = $lectura->nro_factura;
                $importe = $lectura->total_importe;
                $url = base_url($tablas['detalle_lectura'] . $lectura->id);
            }

            $historico[] = array(
                'anio' => $anio,
                'mes' => $mes,
                'mes_label' => substr($this->nombre_mes($mes), 0, 3),
                'estado' => $estado,
                'estado_label' => $estadoLabel,
                'fecha_vencimiento' => $fechaVencimiento,
                'fecha_subida' => $fechaSubida,
                'fecha_consolidado' => $fechaConsolidado,
                'nro_factura' => $nroFactura,
                'importe' => $importe,
                'url' => $url,
            );
        }

        return $historico;
    }

    private function fila_base($cuenta, $modulo)
    {
        return array(
            'modulo' => $modulo,
            'id_indexador' => (int) $cuenta->id_indexador,
            'proveedor' => $cuenta->proveedor ?: 'Sin proveedor',
            'nro_cuenta' => $cuenta->nro_cuenta,
            'acuerdo_pago' => $cuenta->acuerdo_pago,
            'tipo_pago' => $cuenta->tipo_pago ?: 'S/D',
            'secretaria' => $cuenta->secretaria,
            'dependencia' => $cuenta->dependencia,
            'periodicidad_meses' => max(1, (int) $cuenta->periodicidad_meses),
            'fecha_ultimo_pago' => '',
            'fecha_esperada' => '',
        );
    }

    private function resumen_dias($filas, $anio, $mes)
    {
        $inicio = new DateTime(sprintf('%04d-%02d-01', $anio, $mes));
        $fin = clone $inicio;
        $fin->modify('last day of this month');
        $dias = array();

        for ($dia = 1; $dia <= (int) $fin->format('j'); $dia++) {
            $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
            $dias[$fecha] = array(
                'fecha' => $fecha,
                'dia' => $dia,
                'total' => 0,
                'consolidadas' => 0,
                'subidas' => 0,
                'por_consolidar' => 0,
                'sin_subir' => 0,
                'vencidas' => 0,
                'vencen_7' => 0,
                'clase' => 'sin-compromisos',
            );
        }

        foreach ($filas as $fila) {
            if (empty($fila['en_mes']) || !isset($dias[$fila['fecha_esperada']])) {
                continue;
            }
            $dia = &$dias[$fila['fecha_esperada']];
            $dia['total']++;
            if ($fila['estado'] === 'consolidada') {
                $dia['consolidadas']++;
            } elseif (in_array($fila['estado'], array('subida_pendiente', 'vencida_subida'), true)) {
                $dia['subidas']++;
                $dia['por_consolidar']++;
            } else {
                $dia['sin_subir']++;
            }
            if (!empty($fila['vencida'])) {
                $dia['vencidas']++;
            }
            if (!empty($fila['alerta_7'])) {
                $dia['vencen_7']++;
            }
            unset($dia);
        }

        foreach ($dias as &$dia) {
            if ($dia['total'] === 0) {
                $dia['clase'] = 'sin-compromisos';
            } elseif ($dia['vencidas'] > 0) {
                $dia['clase'] = 'dia-rojo';
            } elseif ($dia['vencen_7'] > 0 || $dia['por_consolidar'] > 0) {
                $dia['clase'] = 'dia-amarillo';
            } elseif ($dia['total'] === $dia['consolidadas']) {
                $dia['clase'] = 'dia-verde';
            } else {
                $dia['clase'] = 'dia-azul';
            }
        }
        unset($dia);

        return array_values($dias);
    }

    private function filtros($filas)
    {
        $proveedores = array();
        $pagos = array();
        foreach ($filas as $fila) {
            $proveedores[$fila['proveedor']] = $fila['proveedor'];
            $pagos[$fila['tipo_pago']] = $fila['tipo_pago'];
        }
        natcasesort($proveedores);
        natcasesort($pagos);
        return array('proveedores' => array_values($proveedores), 'pagos' => array_values($pagos));
    }

    private function fecha_valida($valor)
    {
        if (empty($valor) || in_array(strtoupper(trim($valor)), array('S/D', 'N/A', 'ERROR DE LECTURA', '0000-00-00', '0000-00-00 00:00:00'), true)) {
            return null;
        }
        $timestamp = strtotime($valor);
        if ($timestamp === false) {
            return null;
        }
        $fecha = new DateTime(date('Y-m-d', $timestamp));
        $anio = (int) $fecha->format('Y');
        if ($anio < 2020 || $anio > ((int) date('Y') + 2)) {
            return null;
        }
        return $fecha;
    }

    private function normalizar_fecha_salida($valor)
    {
        $fecha = $this->fecha_valida($valor);
        return $fecha ? $fecha->format('Y-m-d') : '';
    }

    private function clave_cuenta($valor)
    {
        return strtoupper(trim($valor));
    }

    private function clave_proveedor_cuenta($idProveedor, $nroCuenta)
    {
        return (int) $idProveedor . '|' . $this->clave_cuenta($nroCuenta);
    }

    private function mes_periodo_contable($periodo, $anio)
    {
        $periodo = strtoupper(trim((string) $periodo));
        if ($periodo === '' || strpos($periodo, (string) $anio) === false) {
            return null;
        }

        $meses = array(
            'ENERO' => 1,
            'FEBRERO' => 2,
            'MARZO' => 3,
            'ABRIL' => 4,
            'MAYO' => 5,
            'JUNIO' => 6,
            'JULIO' => 7,
            'AGOSTO' => 8,
            'SEPTIEMBRE' => 9,
            'SETIEMBRE' => 9,
            'OCTUBRE' => 10,
            'NOVIEMBRE' => 11,
            'DICIEMBRE' => 12,
        );

        foreach ($meses as $nombre => $mes) {
            if (strpos($periodo, $nombre) !== false) {
                return $mes;
            }
        }

        return null;
    }

    private function nombre_mes($mes)
    {
        $meses = array(1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre');
        return isset($meses[(int) $mes]) ? $meses[(int) $mes] : '';
    }

    public function ordenar_filas($a, $b)
    {
        if (!empty($a['sin_actividad']) && empty($b['sin_actividad'])) {
            return 1;
        }
        if (empty($a['sin_actividad']) && !empty($b['sin_actividad'])) {
            return -1;
        }
        if ($a['fecha_esperada'] === $b['fecha_esperada']) {
            $peso = array('vencida_subida' => 1, 'vencida_sin_subir' => 2, 'sin_actividad' => 3, 'subida_pendiente' => 4, 'sin_subir' => 5, 'consolidada' => 9);
            $pa = isset($peso[$a['estado']]) ? $peso[$a['estado']] : 8;
            $pb = isset($peso[$b['estado']]) ? $peso[$b['estado']] : 8;
            if ($pa === $pb) {
                return strcasecmp($a['proveedor'], $b['proveedor']);
            }
            return $pa - $pb;
        }
        return strcmp($a['fecha_esperada'], $b['fecha_esperada']);
    }

    public function ordenar_facturas_por_vencimiento($a, $b)
    {
        $fechaA = !empty($a->fecha_vencimiento) ? $a->fecha_vencimiento->format('Y-m-d') : '';
        $fechaB = !empty($b->fecha_vencimiento) ? $b->fecha_vencimiento->format('Y-m-d') : '';
        if ($fechaA === $fechaB) {
            return (int) $a->id - (int) $b->id;
        }
        return strcmp($fechaA, $fechaB);
    }
}
