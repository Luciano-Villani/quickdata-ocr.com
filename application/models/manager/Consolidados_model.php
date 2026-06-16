<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Consolidados_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('manager/Lotes_model');
		$this->load->model('manager/Proveedores_model', 'proveedores');

		$this->periodos = array(
			'Enero',
			'Febrero',
			'Marzo',
			'Abril',
			'Mayo',
			'Junio',
			'Julio',
			'Agosto',
			'Septiembre',
			'Octubre',
			'Noviembre',
			'Diciembre',
		);
	}
	public function consolidar_datos()
{
    // Cargar el modelo de Lecturas para el mantenimiento del resumen
    // ⚠️ Importante: Asegúrate de que esta ruta sea correcta: models/manager/Lecturas_model.php
    $this->load->model('manager/Lecturas_model'); 
    
    $id_lote = null; 

    if (isset($_REQUEST['id_file']) && $_POST['id_file'] != null) {
        // Consolidación Individual
        $data = $this->Manager_model->getwhere('_datos_api', 'id=' . $_POST['id_file']);
        
        if ($data) {
             // 🔑 Obtenemos el ID del lote para el mantenimiento
             $id_lote = $data->id_lote; // Asumiendo que _datos_api tiene la columna id_lote
        }
        
        $files = array($data);
    } else {
        // Consolidación de Lote Completo (si esta lógica es usada para consolidar todo el lote)
        $files = $this->Lotes_model->getBatchFiles($_POST['code_lote']);

        if (!empty($files)) {
            // 🔑 Obtenemos el ID del lote
            $id_lote = $files[0]->id_lote; 
        }
    }

    if ($id_lote && empty($_REQUEST['id_file'])) {
        $this->Lecturas_model->actualizar_resumen_lote($id_lote);
        $resumen_lote = $this->db->get_where('_lotes_resumen', ['id_lote' => $id_lote])->row();

        if ($resumen_lote && (int)$resumen_lote->archivos_sin_indexar > 0) {
            echo json_encode([
                'status' => 'error',
                'estado' => 'error',
                'title' => 'CONSOLIDAR LOTE',
                'mensaje' => 'El lote posee lecturas sin indexacion. Revisalas antes de consolidar.'
            ]);
            die();
        }

        if ($resumen_lote && (int)$resumen_lote->archivos_error_lectura > 0) {
            echo json_encode([
                'status' => 'error',
                'estado' => 'error',
                'title' => 'CONSOLIDAR LOTE',
                'mensaje' => 'El lote posee lecturas con datos criticos faltantes. Revisalas antes de consolidar.'
            ]);
            die();
        }
    }

    if (!empty($_REQUEST['id_file']) && !empty($files[0]) && $this->Lecturas_model->tiene_error_lectura_bloqueante($files[0])) {
        echo json_encode([
            'status' => 'error',
            'estado' => 'error',
            'title' => 'CONSOLIDAR ARCHIVO',
            'mensaje' => 'La lectura posee datos criticos faltantes. Corregila antes de consolidar.'
        ]);
        die();
    }

    try {
        $error = true;
        foreach ($files as $file) {
            
            // Asumo que $file es un objeto de base de datos
            $id_lote_actual = isset($file->id_lote) ? $file->id_lote : $id_lote;

            if (checkConsolidar($file->id)) {
                $error = false;

                // --- Lógica de obtención de Indexador, Proveedor, Secretaria, etc. (SIN CAMBIOS) ---
                $indexador = $this->Manager_model->getWhere('_indexaciones', 'nro_cuenta="' . $file->nro_cuenta . '"');
                $proveedor = $this->proveedores->get_proveedor($indexador->id_proveedor);
                $secretaria = $this->Manager_model->get_data('_secretarias', $indexador->id_secretaria);
                
                $dependencia_dependencia = '';
                $dependencia_direccion = '';
                if ($dependencia = $this->Manager_model->getWhere('_dependencias', '_dependencias.id="'.$indexador->id_dependencia .'" AND _dependencias.id_secretaria = "'.$indexador->id_secretaria.'"')) {
                    $dependencia_dependencia = $dependencia->dependencia;
                    $dependencia_direccion = $dependencia->direccion;
                }

                $id_programa = '';
                $programa_descripcion = '';
                $id_interno_programa = '';
                if ($programa = $this->Manager_model->getWhere('_programas','id="' . $indexador->id_programa .'"' )) {
                    $id_programa = $programa->id;
                    $programa_descripcion = $programa->descripcion;
                    $id_interno_programa = $programa->id_interno;
                }

                $id_proyecto = '';
                $proyecto_descripcion = '';
                $id_interno_proyecto = '';
                if ($proyecto = $this->Manager_model->getWhere('_proyectos','id="' . $indexador->id_proyecto.'"')) {
                    $id_proyecto = $proyecto->id;
                    $proyecto_descripcion = $proyecto->descripcion;
                    $id_interno_proyecto = $proyecto->id_interno;
                }
                
                $fechaVencimeinto =$file->vencimiento_del_pago;
                $mesVencimiento = explode('-', $fechaVencimeinto);
                $indicePeriodoContable = str_replace('0', '', $mesVencimiento[1]);

                // setear el perioddo contable para graficos
                $grPeriodos = getPeriodos();
                $clavePeriodo = array_search(strtoupper(fecha_es(date("Y-m-d H:i:s"), 'F a', false)), $grPeriodos); 

                // --- Fin Lógica de obtención ---
                
                $dataBatch = array(
                    'id_lectura_api' => $file->id,
                    'id_indexador' => $indexador->id,
                    'id_proveedor' => $proveedor->id,
                    'proveedor' => $proveedor->nombre,
                    'expediente' => $indexador->expediente,
                    'secretaria' => $secretaria->secretaria,
                    'id_secretaria' => $secretaria->id,
                    'jurisdiccion' => $secretaria->major,
                    'programa' => $programa_descripcion,
                    'id_interno_programa' => $id_interno_programa,
                    'id_programa' => $id_programa,
                    'id_proyecto' => $id_proyecto,
                    'id_interno_proyecto' => $id_interno_proyecto,
                    'proyecto' => $proyecto_descripcion,
                    'objeto' => $proveedor->objeto_gasto,
                    'dependencia' => $dependencia_dependencia,
                    'dependencia_direccion' => $dependencia_direccion,
                    'nro_factura' => $file->nro_factura,
                    'codigo_proveedor' => $proveedor->codigo,
                    'tipo_pago' => get_tipoPago($indexador->tipo_pago),
                    'nro_cuenta' => $indexador->nro_cuenta,
                    'periodo_del_consumo' => $file->periodo_del_consumo,
                    'fecha_vencimiento' => $file->vencimiento_del_pago,
                    'mes_vencimiento' => $mesVencimiento[1],
                    'preventivas' => date("Y-m-d H:i:s"),
                    'importe' => $file->total_importe,
                    'periodo_contable' => strtoupper(fecha_es(date("Y-m-d H:i:s"), 'F a', false)),
                    'lote' => $_POST['code_lote'],
                    'user_consolidado' => $this->user->id,
                    'fecha_consolidado' => $this->fecha_now,
                    'nombre_archivo' => $file->nombre_archivo,
                    'importe_1' => $file->total_importe,
                    'acuerdo_pago' => $indexador->acuerdo_pago,
                    'periodo' => $clavePeriodo,
                    'mes_fc' => $file->mes_fc,
                    'anio_fc' => $file->anio_fc,
                    'unidad_medida' => $proveedor->unidad_medida,
                );

                // GRABAR el registro en _consolidados
                $this->Manager_model->grabar_datos('_consolidados', $dataBatch);
    
                $data = array(
                    'consolidado' => 1,
                    'user_consolidado' => $this->user->id,
                    'fecha_consolidado' => $this->fecha_now,
                );
                
                // 1. ACTUALIZAR _datos_api (CORRECTO: consolida el registro individual)
                $this->db->update('_datos_api', $data, array('id' => $file->id));

                // 2. ❌ LÍNEA ELIMINADA: Ya NO actualizamos _lotes aquí.
                // $this->db->update('_lotes', $data, array('code' => $_POST['code_lote'])); 
                
                // 3. ✅ LLAMAR AL MANTENIMIENTO: Esto forzará la verificación del LOTE
                if ($id_lote_actual) {
                    $this->Lecturas_model->actualizar_resumen_lote($id_lote_actual);
                }
                
            } else {
                $error = true;
            }
        }
        
        if($error){
            $response = array(
                'estado' => 'error',
                'title' => 'CONSOLIDACIONES',
                'mensaje' => 'Archivos anteriormente Consolidados'
            );
            echo json_encode($response);die();
        }else{
            $response = array(
                'status' => 'succes',
                'title' => 'CONSOLIDACIONES',
                'mensaje' => 'Archivo Consolidado'
            );
            echo json_encode($response);die();
        }

    } catch (Exception $e) {
        $response = array(
            'status' => 'error',
            'title' => 'ERROR DE CONSOLIDACIÓN',
            'mensaje' => 'Error al procesar: ' . $e->getMessage()
        );
        echo json_encode($response);
        die();
    }
}
	public function grabar_datos($tabla, $data)
	{

		try {
			$this->db->insert($tabla, $data);
		} catch (Exception $e) {
			// this will not catch DB related errors. But it will include them, because this is more general. 
			var_dump($e->getMessage());
		}
	}


	public function guardar_comentarios($data) {
    // La tabla de Proveedores es: _consolidados
     $this->db->where('id', $data['id_registro']); // <-- Usa esta clave
    $this->db->update('_consolidados', [ // <-- TABLA CORREGIDA
        'comentarios' => $data['comentarios'],
        'seguimiento' => $data['seguimiento'] // Guardamos el estado de seguimiento
    ]);
    
    // Verifica si la actualización fue exitosa
    return $this->db->affected_rows() > 0;
}

// -------------------------------------------------------------------------
// 2. Replicar la función de OBTENCIÓN (get_comentario_por_id)
// -------------------------------------------------------------------------
/**
 * Obtiene los datos del consolidado (incluyendo comentario y seguimiento) por ID.
 * @param int $id El ID del registro en _consolidados.
 * @return object|bool El registro si se encuentra, False en caso contrario.
 */
public function get_comentario_por_id($id) {
    // Obtener el comentario y seguimiento por ID
    $this->db->where('id', $id);
    $query = $this->db->get('_consolidados'); // <-- TABLA CORREGIDA

    // Si existe el registro, retornar los resultados
    if ($query->num_rows() > 0) {
        return $query->row(); // Devuelve una sola fila
    }

    return false; // Si no existe el registro
}
// application/models/manager/Consolidados_model.php

/**
 * Cuenta y recupera los registros de Proveedores que están "En Seguimiento" (seguimiento = 1).
 * @param bool $count_only Si es TRUE, devuelve solo el conteo. Si es FALSE, devuelve los registros.
 * @return mixed Conteo (int) o array de objetos (registros).
 */


// application/models/manager/Consolidados_model.php

// application/models/manager/Consolidados_model.php

public function get_seguimiento_proveedores_list()
{
    // 1. SELECT y ORDER BY
    $this->db->select('id, nro_cuenta, proveedor'); 
    $this->db->order_by('id', 'DESC');
    
    // 2. CONDICIONES DE FILTRO
    // Replicamos la condición que probamos y que SÍ FUNCIONÓ en tu base de datos:
    $this->db->where('seguimiento', 1);
    $this->db->where("comentarios IS NOT NULL AND TRIM(comentarios) <> ''", NULL, FALSE);
    
    // 3. EJECUTAR la consulta (similar a tu get_comentario_por_id)
    $query = $this->db->get('_consolidados'); 
    
    // 4. Devolver los resultados, incluso si son 0
    return $query->result(); 
}

/**
 * Obtiene el número de registros de Proveedores en seguimiento.
 * Esta función es llamada por el controlador principal (para el conteo del globo).
 * @return int Conteo de registros.
 */
public function get_seguimiento_proveedores_count()
{
    // 1. CONDICIONES DE FILTRO (son las mismas que arriba)
    $this->db->where('seguimiento', 1);
    $this->db->where("comentarios IS NOT NULL AND TRIM(comentarios) <> ''", NULL, FALSE);
    
    // 2. Ejecutar el conteo
    return $this->db->count_all_results('_consolidados'); 
}

public function get_archivos_por_filtros($filtros)
{
    // Usamos la tabla '_consolidados' y seleccionamos la columna 'nombre_archivo'
    $this->db->select('t1.id, t1.nombre_archivo, t1.periodo_contable, t1.id_proveedor');
    $this->db->from('_consolidados t1'); 

    // --- Aplicación de Filtros ---

    // 1. FILTRO: Proveedor (IDs)
    if (!empty($filtros['id_proveedor'])) {
        $this->db->where_in('t1.id_proveedor', $filtros['id_proveedor']);
    }

    // 2. FILTRO: Tipo de Pago (Textos)
    if (!empty($filtros['tipo_pago'])) {
        // Asumiendo que '_consolidados' tiene una columna para el nombre del Tipo de Pago
        $this->db->where_in('t1.tipo_pago', $filtros['tipo_pago']); 
    }

    // 3. FILTRO: Período Contable 
    if (!empty($filtros['periodo_contable'])) {
        $this->db->where_in('t1.periodo_contable', $filtros['periodo_contable']);
    }

    if (!empty($filtros['id_secretaria'])) {
        $this->db->where_in('t1.id_secretaria', $filtros['id_secretaria']);
    }

    // 4. FILTRO: Rango de Fechas
    if (!empty($filtros['fechas'])) {
		
		$fecha_inicio = $filtros['fechas'][0]; // Formato: YYYY-MM-DD
		$fecha_fin_dia = $filtros['fechas'][1]; // Formato: YYYY-MM-DD

        // 🚨 AJUSTE CRÍTICO PARA DATETIME 🚨
        // Extendemos el rango de la fecha final hasta el último segundo del día.
        $fecha_inicio_db = $fecha_inicio . ' 00:00:00';
        $fecha_fin_db = $fecha_fin_dia . ' 23:59:59';
        
		// Usamos el nombre de la columna corregido

		$this->db->where("t1.fecha_consolidado BETWEEN '{$fecha_inicio_db}' AND '{$fecha_fin_db}'");
	
	}


    // --- Restricción de Archivos ---

    // CRÍTICO: Solo seleccionar los registros que realmente tienen una ruta de archivo.
    $this->db->where('t1.nombre_archivo IS NOT NULL');
    $this->db->where('t1.nombre_archivo !=', '');

	// 🚨 RESTRICCIÓN DE LÍMITE DE 500 ARCHIVOS 🚨
    $this->db->limit(500);

    $query = $this->db->get();
    return $query->result(); 
}

public function get_expedientes_ordenados()
{
    $this->db->select('expediente');
    $this->db->from('_consolidados');
    $this->db->where("expediente IS NOT NULL AND TRIM(expediente) <> ''", NULL, FALSE);
    $this->db->group_by('expediente');
    $this->db->order_by('MAX(fecha_consolidado)', 'DESC');

    $query = $this->db->get();
    $expedientes = array();

    foreach ($query->result() as $row) {
        $expedientes[$row->expediente] = strtoupper($row->expediente);
    }

    return $expedientes;
}

public function get_reporte_final($filtros)
{
    $this->db->select(
        'id, proveedor, codigo_proveedor, expediente, secretaria, dependencia,
        jurisdiccion, id_interno_programa, id_interno_proyecto, objeto,
        tipo_pago, nro_cuenta, nro_factura, periodo_del_consumo,
        fecha_vencimiento, importe, importe_1, periodo_contable'
    );
    $this->db->from('_consolidados');
    $this->aplicar_filtros_reporte_final($filtros);
    $this->db->order_by('proveedor', 'ASC');
    $this->db->order_by('tipo_pago', 'ASC');
    $this->db->order_by('jurisdiccion', 'ASC');
    $this->db->order_by('CAST(id_interno_programa AS UNSIGNED)', 'ASC', FALSE);
    $this->db->order_by('CAST(id_interno_proyecto AS UNSIGNED)', 'ASC', FALSE);
    $this->db->order_by('expediente', 'ASC');
    $this->db->order_by('dependencia', 'ASC');
    $this->db->order_by('nro_cuenta', 'ASC');

    $query = $this->db->get();
    $registros = $query->result();

    return $this->armar_filas_reporte_final($registros);
}

public function get_reporte_final_opciones($filtros)
{
    return array(
        'tipo_pago' => $this->opciones_tipo_pago_reporte($filtros),
        'periodo_contable' => $this->opciones_distintas_reporte('periodo_contable', $filtros, 'periodo_contable'),
        'secretaria' => $this->opciones_secretarias_reporte($filtros),
    );
}

private function opciones_tipo_pago_reporte($filtros)
{
    $this->db->select('C.tipo_pago, TP.tip_id');
    $this->db->from('_consolidados C');
    $this->db->join('_tipo_pago TP', 'TP.tip_nombre = C.tipo_pago', 'left');
    $this->aplicar_filtros_reporte_final_alias($filtros, 'C', 'tipo_pago');
    $this->db->where("C.tipo_pago IS NOT NULL AND TRIM(C.tipo_pago) <> ''", NULL, FALSE);
    $this->db->group_by('C.tipo_pago, TP.tip_id');
    $this->db->order_by('C.tipo_pago', 'ASC');

    $query = $this->db->get();
    $opciones = array();

    foreach ($query->result() as $row) {
        $opciones[] = array(
            'id' => $row->tip_id ? (string) $row->tip_id : $row->tipo_pago,
            'text' => strtoupper($row->tipo_pago),
        );
    }

    return $opciones;
}

private function opciones_secretarias_reporte($filtros)
{
    $this->db->select('C.id_secretaria, C.secretaria');
    $this->db->from('_consolidados C');
    $this->aplicar_filtros_reporte_final_alias($filtros, 'C', 'id_secretaria');
    $this->db->where("C.id_secretaria IS NOT NULL AND C.id_secretaria <> ''", NULL, FALSE);
    $this->db->where("C.secretaria IS NOT NULL AND TRIM(C.secretaria) <> ''", NULL, FALSE);
    $this->db->group_by('C.id_secretaria, C.secretaria');
    $this->db->order_by('C.secretaria', 'ASC');

    $query = $this->db->get();
    $opciones = array();

    foreach ($query->result() as $row) {
        $opciones[] = array(
            'id' => (string) $row->id_secretaria,
            'text' => strtoupper($row->secretaria),
        );
    }

    return $opciones;
}

private function opciones_distintas_reporte($campo, $filtros, $excluir)
{
    $this->db->select('C.' . $campo . ' AS valor');
    $this->db->from('_consolidados C');
    $this->aplicar_filtros_reporte_final_alias($filtros, 'C', $excluir);
    $this->db->where("C.{$campo} IS NOT NULL AND TRIM(C.{$campo}) <> ''", NULL, FALSE);
    $this->db->group_by('C.' . $campo);
    $this->db->order_by('MAX(C.fecha_consolidado)', 'DESC');

    $query = $this->db->get();
    $opciones = array();

    foreach ($query->result() as $row) {
        $opciones[] = array(
            'id' => $row->valor,
            'text' => strtoupper($row->valor),
        );
    }

    return $opciones;
}

private function aplicar_filtros_reporte_final($filtros)
{
    if (!empty($filtros['id_proveedor'])) {
        $this->db->where_in('id_proveedor', $filtros['id_proveedor']);
    }

    if (!empty($filtros['tipo_pago'])) {
        $this->db->where_in('tipo_pago', $filtros['tipo_pago']);
    }

    if (!empty($filtros['periodo_contable'])) {
        $this->db->where_in('periodo_contable', $filtros['periodo_contable']);
    }

    if (!empty($filtros['id_secretaria'])) {
        $this->db->where_in('id_secretaria', $filtros['id_secretaria']);
    }

    if (!empty($filtros['fechas']) && count($filtros['fechas']) === 2) {
        $fechaInicio = $this->db->escape($filtros['fechas'][0] . ' 00:00:00');
        $fechaFin = $this->db->escape($filtros['fechas'][1] . ' 23:59:59');
        $this->db->where("fecha_consolidado BETWEEN {$fechaInicio} AND {$fechaFin}", NULL, FALSE);
    }
}

private function aplicar_filtros_reporte_final_alias($filtros, $alias, $excluir = '')
{
    $prefix = $alias . '.';

    if ($excluir !== 'id_proveedor' && !empty($filtros['id_proveedor'])) {
        $this->db->where_in($prefix . 'id_proveedor', $filtros['id_proveedor']);
    }

    if ($excluir !== 'tipo_pago' && !empty($filtros['tipo_pago'])) {
        $this->db->where_in($prefix . 'tipo_pago', $filtros['tipo_pago']);
    }

    if ($excluir !== 'periodo_contable' && !empty($filtros['periodo_contable'])) {
        $this->db->where_in($prefix . 'periodo_contable', $filtros['periodo_contable']);
    }

    if ($excluir !== 'id_secretaria' && !empty($filtros['id_secretaria'])) {
        $this->db->where_in($prefix . 'id_secretaria', $filtros['id_secretaria']);
    }

    if (!empty($filtros['fechas']) && count($filtros['fechas']) === 2) {
        $fechaInicio = $this->db->escape($filtros['fechas'][0] . ' 00:00:00');
        $fechaFin = $this->db->escape($filtros['fechas'][1] . ' 23:59:59');
        $this->db->where($prefix . "fecha_consolidado BETWEEN {$fechaInicio} AND {$fechaFin}", NULL, FALSE);
    }
}

private function armar_filas_reporte_final($registros)
{
    $filas = array();
    $programaActual = null;
    $codigoProgramaActual = null;
    $jurisdiccionActual = null;
    $programaInicio = 0;
    $jurisdiccionInicio = 0;
    $subtotalPrograma = 0;
    $subtotalJurisdiccion = 0;
    $totalGeneral = 0;
    $cantidadDetalle = 0;

    foreach ($registros as $registro) {
        $codigoPrograma = $this->codigo_programa_reporte($registro);
        $jurisdiccion = (string) $registro->jurisdiccion;
        $programaKey = $jurisdiccion . '|' . $codigoPrograma;

        if ($programaActual !== null && $programaKey !== $programaActual) {
            $filas[] = $this->fila_subtotal_programa($codigoProgramaActual, $subtotalPrograma, $programaInicio, count($filas));
            $subtotalPrograma = 0;
            $programaInicio = count($filas);
        }

        if ($jurisdiccionActual !== null && $jurisdiccion !== $jurisdiccionActual) {
            $filas[] = $this->fila_subtotal_jurisdiccion($jurisdiccionActual, $subtotalJurisdiccion, $jurisdiccionInicio, count($filas));
            $subtotalJurisdiccion = 0;
            $jurisdiccionInicio = count($filas);
            $programaInicio = count($filas);
        }

        if ($programaActual === null || $programaKey !== $programaActual) {
            $programaActual = $programaKey;
            $codigoProgramaActual = $codigoPrograma;
        }
        if ($jurisdiccionActual === null || $jurisdiccion !== $jurisdiccionActual) {
            $jurisdiccionActual = $jurisdiccion;
        }

        $importe = $this->importe_reporte($registro);
        $subtotalPrograma += $importe;
        $subtotalJurisdiccion += $importe;
        $totalGeneral += $importe;
        $cantidadDetalle++;

        $filas[] = array(
            'tipo' => 'detalle',
            'proveedor' => $registro->proveedor . ' (' . $registro->codigo_proveedor . ')',
            'expediente' => $registro->expediente,
            'secretaria' => $registro->secretaria,
            'dependencia' => $registro->dependencia,
            'jurisdiccion' => $jurisdiccion,
            'programa' => $codigoPrograma,
            'objeto' => $registro->objeto,
            'tipo_pago' => $registro->tipo_pago,
            'nro_cuenta' => $registro->nro_cuenta,
            'nro_factura' => $registro->nro_factura,
            'periodo' => $registro->periodo_del_consumo,
            'vencimiento' => $this->fecha_reporte($registro->fecha_vencimiento),
            'importe' => $importe,
        );
    }

    if ($programaActual !== null) {
        $filas[] = $this->fila_subtotal_programa($codigoProgramaActual, $subtotalPrograma, $programaInicio, count($filas));
    }

    if ($jurisdiccionActual !== null) {
        $filas[] = $this->fila_subtotal_jurisdiccion($jurisdiccionActual, $subtotalJurisdiccion, $jurisdiccionInicio, count($filas));
    }

    if ($cantidadDetalle > 0) {
        $filas[] = array(
            'tipo' => 'total_general',
            'jurisdiccion' => 'TOTAL GENERAL',
            'importe' => $totalGeneral,
            'formula_inicio' => 3,
            'formula_fin' => max(3, count($filas) + 1),
        );
    }

    return array(
        'filas' => $filas,
        'total' => $totalGeneral,
        'cantidad' => $cantidadDetalle,
    );
}

private function fila_subtotal_programa($codigoPrograma, $subtotal, $inicio, $fin)
{
    return array(
        'tipo' => 'subtotal_programa',
        'programa' => 'Total ' . $codigoPrograma,
        'importe' => $subtotal,
        'formula_inicio' => $inicio + 3,
        'formula_fin' => $fin + 2,
    );
}

private function fila_subtotal_jurisdiccion($jurisdiccion, $subtotal, $inicio, $fin)
{
    return array(
        'tipo' => 'subtotal_jurisdiccion',
        'jurisdiccion' => 'Total ' . $jurisdiccion,
        'importe' => $subtotal,
        'formula_inicio' => $inicio + 3,
        'formula_fin' => $fin + 2,
    );
}

private function codigo_programa_reporte($registro)
{
    $programa = trim((string) $registro->id_interno_programa);
    $proyecto = trim((string) $registro->id_interno_proyecto);

    if ($programa !== '' && strlen($programa) === 1) {
        $programa = '0' . $programa;
    }

    if ($proyecto !== '' && $proyecto !== '0') {
        $programa .= '.' . (strlen($proyecto) === 1 ? '0' . $proyecto : $proyecto);
    }

    return $programa;
}

private function importe_reporte($registro)
{
    if ($registro->importe_1 !== null && $registro->importe_1 !== '') {
        return (float) $registro->importe_1;
    }

    return (float) str_replace(',', '.', $registro->importe);
}

private function fecha_reporte($valor)
{
    if (empty($valor) || $valor === '0000-00-00' || $valor === '0000-00-00 00:00:00') {
        return '';
    }

    $timestamp = strtotime($valor);
    return $timestamp ? date('d-m-Y', $timestamp) : $valor;
}
// En Consolidados_model.php
public function get_periodos_ordenados()
{
    $this->db->select('periodo_contable'); 
    $this->db->from('_consolidados');
    
    // Agrupa por el nombre del período (Ej: 'OCTUBRE 2025')
    $this->db->group_by('periodo_contable'); 
    
    // CRÍTICO: Ordena por la fecha real (la más reciente)
    $this->db->order_by('MAX(fecha_consolidado)', 'DESC'); 
    
    $query = $this->db->get();
    $results = $query->result(); // Obtener array de objetos

    $periodos_formateados = [];

    // Formatear los resultados a un array asociativo simple
    // La clave (valor) y el valor (texto) son la misma columna: 'periodo_contable'
    foreach ($results as $row) {
        $periodos_formateados[$row->periodo_contable] = $row->periodo_contable;
    }

    return $periodos_formateados;
}
  


}
