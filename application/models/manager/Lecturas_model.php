<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Lecturas_model extends CI_Model
{

	public function lotes_dt($id_proveedor = null)
{
    // 💡 INICIO - MARCA DE TIEMPO
    $start_time = microtime(true);
    
    $datos = [];
    $table_lotes = '_lotes';
    $table_proveedores = '_proveedores';
    $table_users = 'users';
    $table_lecturas = '_datos_api'; 
    
    if(isset($_REQUEST['table'])) {
        $table_lotes = $_REQUEST['table'];
    }

    // --- 1. PREPARACIÓN DE CONDICIONES ---
    $where_main = " WHERE 1=1 ";
    if ($id_proveedor) {
        $where_main .= " AND t1.id_proveedor = " . $this->db->escape($id_proveedor);
    }
    
    $where_search = "";
    $search_val = isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '';

    if (!empty($search_val)) {
        $safe_search_val = $this->db->escape_like_str($search_val);
        $where_search = " AND ( t1.code LIKE '%" . $safe_search_val . "%' ";
        $where_search .= " OR u.username LIKE '%" . $safe_search_val . "%' ";
        $where_search .= " OR p.nombre LIKE '%" . $safe_search_val . "%' )";
    }

    // Definición de columnas para ORDER
    $columns = array(
        0 => 't1.id',
        1 => 'p.nombre',
        2 => 't1.code',
        3 => 't1.fecha_add',
        4 => 'cant',
        5 => 'sin_indexar',
        6 => 't1.consolidado',
        7 => 'u.username',
        8 => 't1.id',
    );
    
    // --- 2. CONSULTA TOTAL (RecordsTotal) ---
    $totalRecordsSql = "SELECT count(*) as total FROM {$table_lotes} t1 {$where_main}";
    $total = $this->db->query($totalRecordsSql)->row()->total;
    
    $count_total_end_time = microtime(true);

    // --- 3. CONSULTA FILTRADA Y PAGINADA (Data) ---
    $sql = "SELECT t1.*, p.nombre as proveedor, u.username as username_add,";
    $sql .= " COUNT(t2.id) as cant, ";
    $sql .= " SUM(CASE WHEN t2.consolidado = 0 THEN 1 ELSE 0 END) as sin_indexar ";
    $sql .= " FROM {$table_lotes} t1";
    $sql .= " JOIN {$table_proveedores} p ON p.id = t1.id_proveedor";
    $sql .= " JOIN {$table_users} u ON u.id = t1.user_add";
    $sql .= " LEFT JOIN {$table_lecturas} t2 ON t2.id_lote = t1.id"; 
    $sql .= $where_main . $where_search;
    $sql .= " GROUP BY t1.id";
    
    $order_column = $columns[$_REQUEST['order'][0]['column']];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $start = (int)$_REQUEST['start'];
    $length = (int)$_REQUEST['length'];
    
    $sql .= " ORDER BY {$order_column} {$order_dir} LIMIT {$start}, {$length}";

    $query = $this->db->query($sql);
    $data = $query->result();

    $query_end_time = microtime(true); 

    // --- 4. CALCULAR RECORDS FILTERED ---
    $recordsFiltered = (int)$total;
    if (!empty($search_val)) {
        $sql_filtered_count = "SELECT COUNT(t1.id) as total_filtered FROM {$table_lotes} t1";
        $sql_filtered_count .= " JOIN {$table_proveedores} p ON p.id = t1.id_proveedor";
        $sql_filtered_count .= " JOIN {$table_users} u ON u.id = t1.user_add"; 
        $sql_filtered_count .= $where_main . $where_search;
        $recordsFiltered = (int)$this->db->query($sql_filtered_count)->row()->total_filtered;
    }
    
    $count_filtered_end_time = microtime(true); 
    $build_start_time = microtime(true); 

    // --- 5. ARMADO DEL ARRAY FINAL DE DATA ---
    foreach ($data as $r) {
        
        $checkbox = '<input id="' . $r->id . '" class="checkbox" type="checkbox">';
        $sin_indexado = (int)$r->sin_indexar;
        $consolidado_status = $r->consolidado ? 
            '<span class="acciones"><i class="text-warnin icon-check2 "></i></span>' : 
            '<span class="acciones"><i class="text-danger icon-cross2 "></i></span>';

        $acciones_full = '<span class="acciones"><a title="ver archivo" href="/Admin/Lotes/viewBatch/' . $r->code . '" class=""><i class="icon-eye4" title="ver"></i></a></span>' .
                         '<span data-consolidado="' . $r->consolidado . '" data-errores="' . $r->sin_indexar . '" data-code="' . $r->code . '" data-id_lote="' . $r->id . '" class="mergelote"><a title="Consolidar" href="#"><i class="text-info icon-merge " title="Consolidar"></i></a></span>' .
                         '<span data-id_lote="' . $r->id . '" data-code="' . $r->code . '" class="d-none editar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Editar lote" href="#"><i class=" text-warningr icon-pencil4 " title="Editar Lote"></i></a></span>' .
                         '<span data-id_lote="' . $r->id . '" data-code="' . $r->code . '" class="borrar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Borrar lote" href="#"><i class=" text-danger icon-trash " title="Borrar Lote"></i></a></span>';
        
        $datos[] = array(
            $checkbox, $r->proveedor, $r->code, fecha_es($r->fecha_add, "d/m/a", true), 
            (int)$r->cant, $sin_indexado, $consolidado_status, $r->username_add, $acciones_full
        );
    }
    
    $build_end_time = microtime(true); 

    // --- 6. DEVUELVE EL ARRAY SIN JSON_ENCODE ---
    return [
        "draw" => intval($_REQUEST['draw']),
        "recordsTotal" => (int)$total,
        "recordsFiltered" => (int)$recordsFiltered,
        "data" => $datos,
        // Tiempos para el diagnóstico
        "diagnostico_data" => [
            "tiempo_count_total_ms" => round(($count_total_end_time - $start_time) * 1000, 2),
            "tiempo_query_paginada_ms" => round(($query_end_time - $count_total_end_time) * 1000, 2),
            "tiempo_count_filtrado_ms" => round(($count_filtered_end_time - $query_end_time) * 1000, 2),
            "tiempo_foreach_ms" => round(($build_end_time - $build_start_time) * 1000, 2),
            "tiempo_total_servidor_ms" => round(($build_end_time - $start_time) * 1000, 2),
        ]
    ];
}


	public function list_dt($id)
	{

		$datos = [];
		$draw = intval(2);
		$start = intval(0);
		$length = intval(0);
		$dependencia  = '';
		$programa  = '';
		$indexaciones  = '-';

		if ($id) {
			$query = $this->db->select('*')
				->where('id_proveedor', $id)
				->get('_datos_api');
		} else {
			$query = $this->db->select("*")->get('_datos_api');
		}

		$query->result();

		foreach ($query->result() as $r) {

			$textoDataConsolidar = 'PROVEEDOR: ' . $r->nombre_proveedor . ' - CUENTA: ' . $r->nro_cuenta;
			$indexacion = '';
			$accionIndexar = '';
			if ($indexacion = $this->Manager_model->get_indexacion('_indexaciones', $r->nro_cuenta)) {
				$indexacion = $indexacion->id;
				if($r->consolidado !=0){
					$accionIndexar = '<a data-text="Borrar consolidado" data-accion="Consolidar" data-id_indexador="' . $indexacion . '" data-id_lectura_api="' . $r->id . '" data-data_cons="' . $textoDataConsolidar . '" id="consolidar" title="Consolidar archivo" href="/Admin/Lecturas/Indexar/' . $r->id . '" class=" text-danger "><i class="icon-database-remove" title="Consolidar archivo"></i> </a>';

				}else{

					$accionIndexar = '<a data-text="Consolidar archivo" data-accion="Consolidar" data-id_indexador="' . $indexacion . '" data-id_lectura_api="' . $r->id . '" data-data_cons="' . $textoDataConsolidar . '" id="consolidar" title="Consolidar archivo" href="/Admin/Lecturas/Indexar/' . $r->id . '" class=" text-success "><i class="icon-database-add" title="Consolidar archivo"></i> </a>';
				}
			};


			$accionesVer = '<a title="ver archivo" href="/Admin/Lecturas/Views/' . $r->id . '"  class=" "><i class="icon-eye4" title="ver archivo"></i> </a> ';

			$archivo = explode('/', $r->nombre_archivo, 4);
			$datos[] = array(
				$r->nombre_proveedor,
				$r->nro_cuenta,
				$r->nro_medidor,
				$r->nro_factura,
				$r->periodo_del_consumo,
				fecha_es($r->fecha_emision, 'd/m/a', false),
				fecha_es($r->vencimiento_del_pago, 'd/m/a', false),
				'$ ' . $r->total_importe,
				$r->total_vencido,
				$r->proximo_vencimiento,
				$archivo[3],
				$accionesVer . $accionIndexar
			);
		}


		$resultx = array(
			"draw" => $draw,
			"recordsTotal" => $query->num_rows(),
			"recordsFiltered" => $query->num_rows(),
			"data" => $datos
		);

		echo json_encode($resultx);
	}

	// En Lecturas_model.php
// ¡Recupera la última versión de lotes_dt() que te di, renómbrala!

public function get_lotes_data($id_proveedor = null)
{
    // 💡 INICIO - MARCA DE TIEMPO
    $start_time = microtime(true);
    
    $datos = [];
    $table_lotes = '_lotes';
    $table_proveedores = '_proveedores';
    $table_users = 'users';
    // Tabla de Resumen (NUEVA)
    $table_resumen = '_lotes_resumen'; 

    if(isset($_REQUEST['table'])) {
        $table_lotes = $_REQUEST['table'];
    }

    // --- 1. PREPARACIÓN DE CONDICIONES ---
    $where_main = " WHERE 1=1 ";
    if ($id_proveedor) {
        $where_main .= " AND t1.id_proveedor = " . $this->db->escape($id_proveedor);
    }
    
    $where_search = "";
    $search_val = isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '';

    if (!empty($search_val)) {
        $safe_search_val = $this->db->escape_like_str($search_val);
        $where_search = " AND ( t1.code LIKE '%" . $safe_search_val . "%' ";
        $where_search .= " OR CONCAT(u.first_name, ' ', u.last_name) LIKE '%" . $safe_search_val . "%' "; // Búsqueda en nombre completo del usuario
        $where_search .= " OR p.nombre LIKE '%" . $safe_search_val . "%' )";
    }

    // Definición de columnas para ORDER
    $columns = array(
    0 => 't1.id',                 // 0. Checkbox/ID (Oculto)
    1 => 'p.nombre',              // 1. Proveedor
    2 => 't1.fecha_add',          // 2. Fecha (ANTES ERA 't1.code', ahora es 't1.fecha_add')
    3 => 'r.total_archivos',      // 3. Facturas
    4 => 'r.archivos_sin_indexar',// 4. Sin Index
    5 => 't1.consolidado',        // 5. Consolidado
    6 => 'u.username',            // 6. Usuario
    7 => 't1.id',                 // 7. Acciones (Último índice)
);
    
    // --- 2. CONSULTA TOTAL (RecordsTotal) ---
    $totalRecordsSql = "SELECT count(*) as total FROM {$table_lotes} t1 {$where_main}";
    $total = $this->db->query($totalRecordsSql)->row()->total;
    
    $count_total_end_time = microtime(true);

    // -------------------------------------------------------------------------------------------------------------------------------------
    // --- 3. CONSULTA FILTRADA Y PAGINADA (Data) - OPTIMIZADA CON TABLA DE RESUMEN ---
    // ¡Aquí eliminamos los JOIN a _datos_api y _indexaciones y el costoso GROUP BY!
    $sql = "SELECT 
                t1.*, 
                p.nombre as proveedor, 
                CONCAT(u.first_name, ' ', u.last_name) as username_add,
                r.total_archivos as cant,               
                r.archivos_sin_indexar as sin_indexar  
            ";
    
    $sql .= " FROM {$table_lotes} t1";
    
    // Unir a Proveedor y Usuario
    $sql .= " JOIN {$table_proveedores} p ON p.id = t1.id_proveedor";
    $sql .= " JOIN {$table_users} u ON u.id = t1.user_add";
    
    // ¡NUEVO! JOIN RÁPIDO a la Tabla de Resumen
    $sql .= " LEFT JOIN {$table_resumen} r ON r.id_lote = t1.id"; 
    
    $sql .= $where_main . $where_search;
    // IMPORTANTE: El GROUP BY ha sido ELIMINADO.
    
    $order_column = $columns[$_REQUEST['order'][0]['column']];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $start = (int)$_REQUEST['start'];
    $length = (int)$_REQUEST['length'];
    
    $sql .= " ORDER BY {$order_column} {$order_dir} LIMIT {$start}, {$length}";

    $query = $this->db->query($sql);
    $data = $query->result();

    $query_end_time = microtime(true); 
    // -------------------------------------------------------------------------------------------------------------------------------------

    // --- 4. CALCULAR RECORDS FILTERED ---
    $recordsFiltered = (int)$total;
    if (!empty($search_val)) {
        // Esta consulta de conteo filtrado ahora también es más rápida, ya que no necesita GROUP BY
        $sql_filtered_count = "SELECT COUNT(t1.id) as total_filtered FROM {$table_lotes} t1";
        $sql_filtered_count .= " JOIN {$table_proveedores} p ON p.id = t1.id_proveedor";
        $sql_filtered_count .= " JOIN {$table_users} u ON u.id = t1.user_add"; 
        $sql_filtered_count .= $where_main . $where_search;
        $recordsFiltered = (int)$this->db->query($sql_filtered_count)->row()->total_filtered;
    }
    
    $count_filtered_end_time = microtime(true); 
    $build_start_time = microtime(true); 

    // --- 5. ARMADO DEL ARRAY FINAL DE DATA ---
    foreach ($data as $r) {
        
        $checkbox = '<input id="' . $r->id . '" class="checkbox" type="checkbox">';
        // Los valores $r->cant y $r->sin_indexar vienen de la tabla de resumen
        $sin_indexado = (int)$r->sin_indexar; 
        $consolidado_status = $r->consolidado ? 
            '<span class="acciones"><i class="text-warnin icon-check2 "></i></span>' : 
            '<span class="acciones"><i class="text-danger icon-cross2 "></i></span>';

        $acciones_full = '<span class="acciones"><a title="ver archivo" href="/Admin/Lotes/viewBatch/' . $r->code . '" class=""><i class="icon-eye4" title="ver"></i></a></span>' .
                         '<span data-consolidado="' . $r->consolidado . '" data-errores="' . $r->sin_indexar . '" data-code="' . $r->code . '" data-id_lote="' . $r->id . '" class="mergelote"><a title="Consolidar" href="#"><i class="text-info icon-merge " title="Consolidar"></i></a></span>' .
                         '<span data-id_lote="' . $r->id . '" data-code="' . $r->code . '" class="d-none editar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Editar lote" href="#"><i class=" text-warningr icon-pencil4 " title="Editar Lote"></i></a></span>' .
                         '<span data-id_lote="' . $r->id . '" data-code="' . $r->code . '" class="borrar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Borrar lote" href="#"><i class=" text-danger icon-trash " title="Borrar Lote"></i></a></span>';
        
        $datos[] = array(
            $checkbox, $r->proveedor, fecha_es($r->fecha_add, "d/m/a"), 
            (int)$r->cant, $sin_indexado, $consolidado_status, $r->username_add, $acciones_full
        );
    }
    
    $build_end_time = microtime(true); 

    // --- 6. DEVUELVE EL ARRAY CON DIAGNÓSTICO ---
    return [
        "draw" => intval($_REQUEST['draw']),
        "recordsTotal" => (int)$total,
        "recordsFiltered" => (int)$recordsFiltered,
        "data" => $datos,
        "diagnostico" => [ 
            
            "tiempo_total_servidor_ms" => round(($build_end_time - $start_time) * 1000, 2),
        ]
    ];
}
// En Lecturas_model.php (o el modelo adecuado)

public function actualizar_resumen_lote($id_lote) 
{
    // Consulta que recalcula el total de archivos y el total sin indexar para un lote específico
    $resumen_data = $this->db->query("
        SELECT 
            COUNT(t2.id) AS total_archivos,
            -- El nro_cuenta de _datos_api (t2) es 'sin indexar' si no tiene coincidencia en _indexaciones (t3)
            SUM(CASE WHEN t3.id IS NULL THEN 1 ELSE 0 END) AS archivos_sin_indexar 
        FROM _datos_api t2 
        LEFT JOIN _indexaciones t3 ON t3.nro_cuenta = t2.nro_cuenta
        WHERE t2.id_lote = ?", [$id_lote])->row_array();

    // Actualizar la tabla de resumen
    $this->db->where('id_lote', $id_lote)->update('_lotes_resumen', [
        'total_archivos' => (int)$resumen_data['total_archivos'],
        'archivos_sin_indexar' => (int)$resumen_data['archivos_sin_indexar']
    ]);
    
    // Si la actualización no afectó ninguna fila (lote eliminado, por ejemplo), se puede considerar una inserción si es necesario, 
    // pero para este caso el UPDATE es suficiente porque el registro ya se creó en cerrarLote().
}
}