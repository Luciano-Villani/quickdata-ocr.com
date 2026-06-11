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
    0 => 't1.id',
    1 => 'p.nombre',
    2 => 't1.fecha_add',
    3 => 'r.total_archivos',
    4 => 'r.archivos_sin_indexar',
    5 => 'r.archivos_error_lectura',
    6 => 't1.consolidado',
    7 => 'u.username',
    8 => 't1.id',
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
                r.archivos_sin_indexar as sin_indexar,
                r.archivos_error_lectura as error_lectura
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
        $error_lectura = (int)$r->error_lectura;
        $sin_index_badge = $sin_indexado > 0
            ? '<a href="/Admin/Lotes/viewBatch/' . $r->code . '?filtro=sin_index" class="badge badge-danger" title="Ver lecturas sin indexacion">' . $sin_indexado . '</a>'
            : '<span class="badge badge-success">0</span>';
        $error_lectura_badge = $error_lectura > 0
            ? '<a href="/Admin/Lotes/viewBatch/' . $r->code . '?filtro=errores" class="badge badge-danger" title="Ver lecturas con errores">' . $error_lectura . '</a>'
            : '<span class="badge badge-success">0</span>';
        $consolidado_status = $r->consolidado ? 
            '<span class="acciones"><i class="text-warnin icon-check2 "></i></span>' : 
            '<span class="acciones"><i class="text-danger icon-cross2 "></i></span>';

        $acciones_full = '<span class="acciones"><a title="ver archivo" href="/Admin/Lotes/viewBatch/' . $r->code . '" class=""><i class="icon-eye4" title="ver"></i></a></span>' .
                         '<span data-consolidado="' . $r->consolidado . '" data-errores="' . $sin_indexado . '" data-error-lectura="' . $error_lectura . '" data-code="' . $r->code . '" data-id_lote="' . $r->id . '" class="mergelote"><a title="Consolidar" href="#"><i class="text-info icon-merge " title="Consolidar"></i></a></span>' .
                         '<span data-id_lote="' . $r->id . '" data-code="' . $r->code . '" class="d-none editar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Editar lote" href="#"><i class=" text-warningr icon-pencil4 " title="Editar Lote"></i></a></span>' .
                         '<span data-id_lote="' . $r->id . '" data-code="' . $r->code . '" class="borrar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Borrar lote" href="#"><i class=" text-danger icon-trash " title="Borrar Lote"></i></a></span>';
        
        $datos[] = array(
            $checkbox, $r->proveedor, fecha_es($r->fecha_add, "d/m/a"), 
            (int)$r->cant, $sin_index_badge, $error_lectura_badge, $consolidado_status, $r->username_add, $acciones_full
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
    // 1. Recalcula el total de archivos, el total sin indexar y el total de archivos consolidados.
    $resumen_data = $this->db->query("
        SELECT 
            COUNT(t2.id) AS total_archivos,
            -- Archivos sin indexar: cuenta vacia/invalida o sin coincidencia en indexadores.
            SUM(CASE
                WHEN t2.nro_cuenta IS NULL
                    OR TRIM(t2.nro_cuenta) IN ('', 'S/D', 'SD', '-', 'error de lectura')
                    OR NOT EXISTS (
                        SELECT 1
                        FROM _indexaciones ix
                        WHERE ix.nro_cuenta = t2.nro_cuenta
                        LIMIT 1
                    )
                THEN 1 ELSE 0
            END) AS archivos_sin_indexar,
            SUM(" . $this->sql_error_lectura_case('t2') . ") AS archivos_error_lectura,
            -- Archivos PENDIENTES de consolidar (t2.consolidado = 0)
            SUM(CASE WHEN t2.consolidado = 0 THEN 1 ELSE 0 END) AS archivos_pendientes_consolidar
        FROM _datos_api t2 
        WHERE t2.id_lote = ?", [$id_lote])->row_array();

    // 2. Determinar el estado de consolidación del LOTE
    $archivos_pendientes_consolidar = (int)$resumen_data['archivos_pendientes_consolidar'];
    $estado_consolidacion_lote = 0; // Por defecto: No consolidado
    
    // Si la cuenta de archivos pendientes de consolidar es CERO, el lote completo está consolidado.
    if ($archivos_pendientes_consolidar == 0) {
        $estado_consolidacion_lote = 1; 
    }

    // 3. Crear/actualizar la tabla de resumen (_lotes_resumen)
    $this->db->query("
        INSERT INTO _lotes_resumen (id_lote, total_archivos, archivos_sin_indexar, archivos_error_lectura)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            total_archivos = VALUES(total_archivos),
            archivos_sin_indexar = VALUES(archivos_sin_indexar),
            archivos_error_lectura = VALUES(archivos_error_lectura)",
        array(
            (int)$id_lote,
            (int)$resumen_data['total_archivos'],
            (int)$resumen_data['archivos_sin_indexar'],
            (int)$resumen_data['archivos_error_lectura']
        )
    );
    
    // 4. Actualizar el estado 'consolidado' en la tabla principal del LOTE (_lotes)
    // ESTA ES LA ACCIÓN CLAVE que corrige el error de lógica
    $this->db->where('id', $id_lote)->update('_lotes', [
        'consolidado' => $estado_consolidacion_lote
    ]);
    
    // El comentario sobre la inserción es válido: el UPDATE es suficiente aquí.
}

    public function sql_error_lectura_case($alias = '')
    {
        $prefix = $alias ? $alias . '.' : '';

        return "CASE
            WHEN {$prefix}nro_cuenta IS NULL OR TRIM({$prefix}nro_cuenta) IN ('', 'S/D', 'SD', '-', 'error de lectura')
                OR {$prefix}nro_factura IS NULL OR TRIM({$prefix}nro_factura) IN ('', 'S/D', 'SD', '-', 'error de lectura')
                OR {$prefix}periodo_del_consumo IS NULL OR TRIM({$prefix}periodo_del_consumo) IN ('', 'S/D', 'SD', '-', 'error de lectura')
                OR {$prefix}fecha_emision IS NULL OR TRIM({$prefix}fecha_emision) IN ('', 'S/D', 'SD', '-', '0000-00-00', 'error de lectura')
                OR {$prefix}vencimiento_del_pago IS NULL OR TRIM({$prefix}vencimiento_del_pago) IN ('', 'S/D', 'SD', '-', '0000-00-00', 'error de lectura')
                OR {$prefix}total_importe IS NULL OR TRIM({$prefix}total_importe) IN ('', 'S/D', 'SD', '-', 'error de lectura')
                OR TRIM({$prefix}total_importe) NOT REGEXP '^-?[0-9]+([,.][0-9]+)?$'
                OR CAST(REPLACE(TRIM({$prefix}total_importe), ',', '.') AS DECIMAL(18,2)) < 0
                OR (
                    CAST(REPLACE(TRIM({$prefix}total_importe), ',', '.') AS DECIMAL(18,2)) = 0
                    AND COALESCE({$prefix}consolidado, 0) = 0
                )
            THEN 1
            ELSE 0
        END";
    }

    public function errores_lectura($lectura)
    {
        $errores = [];

        if ($this->valor_vacio($lectura->nro_cuenta)) {
            $errores[] = 'Sin cuenta';
        }
        if ($this->valor_vacio($lectura->nro_factura)) {
            $errores[] = 'Sin factura';
        }
        if ($this->valor_vacio($lectura->periodo_del_consumo)) {
            $errores[] = 'Sin periodo';
        }
        if ($this->fecha_invalida($lectura->fecha_emision)) {
            $errores[] = 'Sin fecha emision';
        }
        if ($this->fecha_invalida($lectura->vencimiento_del_pago)) {
            $errores[] = 'Sin vencimiento';
        }
        if ($this->importe_vacio_o_invalido($lectura->total_importe)) {
            $errores[] = 'Sin importe';
        } elseif ($this->importe_cero($lectura->total_importe)) {
            $errores[] = 'Importe 0.00';
        }

        return $errores;
    }

    public function tiene_error_lectura($lectura)
    {
        return count($this->errores_lectura($lectura)) > 0;
    }

    public function errores_lectura_bloqueantes($lectura)
    {
        $errores = $this->errores_lectura($lectura);
        return array_values(array_filter($errores, function ($error) {
            return $error !== 'Importe 0.00';
        }));
    }

    public function tiene_error_lectura_bloqueante($lectura)
    {
        return count($this->errores_lectura_bloqueantes($lectura)) > 0;
    }

    private function valor_vacio($valor)
    {
        $valor = trim((string)$valor);
        return $valor === '' || in_array(strtoupper($valor), ['S/D', 'SD', '-', 'ERROR DE LECTURA'], true);
    }

    private function fecha_invalida($valor)
    {
        if ($this->valor_vacio($valor) || trim((string)$valor) === '0000-00-00') {
            return true;
        }

        return strtotime($valor) === false;
    }

    private function importe_vacio_o_invalido($valor)
    {
        if ($this->valor_vacio($valor)) {
            return true;
        }

        $normalizado = str_replace(',', '.', trim((string)$valor));
        return !is_numeric($normalizado) || (float)$normalizado < 0;
    }

    private function importe_cero($valor)
    {
        $normalizado = str_replace(',', '.', trim((string)$valor));
        return is_numeric($normalizado) && (float)$normalizado == 0.0;
    }
}
