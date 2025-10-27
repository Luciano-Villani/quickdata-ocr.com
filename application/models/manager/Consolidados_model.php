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
