<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Lotes_model extends CI_Model
{

	function __construct()
	{
		parent::__construct();
		// Set table name
		$this->table = '_lotes';
		// Set orderable column fields
		$this->column_order = array('_proveedores.codigo','_proveedores.nombre', '_lotes.fecha_add','','','_lotes.consolidado','_lotes.user_add');
		$this->column_search = array( '_proveedores.codigo','_proveedores.nombre', '_lotes.consolidado','_lotes.user_add', '_lotes.fecha_add');
		
		$this->files_column_search = array('nro_factura', 'nro_cuenta','nro_medidor', 'user_add');
		$this->files_column_order = array( 'nro_factura','nro_cuenta','nro_medidor', 'user_add');
		$this->order = array('id' => 'desc');
	}


	public function getRows($postData){
        $this->_get_datatables_query($postData);


        if($postData['length'] != -1){
            $this->db->limit($postData['length'], $postData['start']);
        }
        $query = $this->db->get();

        return $query->result();
    }	
	public function getFileRows($postData){
        $this->_get_datatables_files_query($postData);


        if($postData['length'] != -1){
            $this->db->limit($postData['length'], $postData['start']);
        }
        $query = $this->db->get();

        return $query->result();
    }
	

	public function countAll(){
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }
	public function countAllFiles(){
        $this->db->from('_datos_api');
        return $this->db->count_all_results();
    }




    public function countFiltered($postData){
        $this->_get_datatables_query($postData);
        $query = $this->db->get();
        return $query->num_rows();
    }   
	public function countFilteredFiles($postData){
        $this->_get_datatables_files_query($postData);
        $query = $this->db->get();
        return $query->num_rows();
    }


	private function _get_datatables_files_query($postData){

		$postData['code_lote']=1;
        $this->db->from('_datos_api');
        $this->db->where('code_lote',$postData['id_lote']);
 
        $i = 0;
        // loop searchable columns 
        foreach($this->files_column_search as $item){


			// echo '<pre>';
			// var_dump( $item ); 
			// echo '</pre>';
			// // die();
            // if datatable send POST for search
            if($postData['search']['value']){
                // first loop
                if($i===0){
                    // open bracket
                    $this->db->group_start();
                    $this->db->like($item, $postData['search']['value']);
                }else{
                    $this->db->or_like($item, $postData['search']['value']);
                }
                
                // last loop
                if(count($this->files_column_search) - 1 == $i){
                    // close bracket
                    $this->db->group_end();
                }
            }
            $i++;
        }
         
        if(isset($postData['order'])){
            $this->db->order_by($this->files_column_order[$postData['order']['0']['column']], $postData['order']['0']['dir']);
        }else if(isset($this->order)){
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }

		
    }
	private function _get_datatables_query($postData){
	       

        $this->db->select('_lotes.* , _proveedores.*,users.*');
		$this->db->join('_proveedores','_proveedores.id = _lotes.id_proveedor','');
		$this->db->join('users','users.id = _lotes.user_add','');
		
        $this->db->from($this->table);
 
        $i = 0;
        // loop searchable columns 
        foreach($this->column_search as $item){


			// echo '<pre>';
			// var_dump( $item ); 
			// echo '</pre>';
			// // die();
            // if datatable send POST for search
            if($postData['search']['value']){
                // first loop
                if($i===0){
                    // open bracket
                    $this->db->group_start();
                    $this->db->like($item, $postData['search']['value']);
                }else{
                    $this->db->or_like($item, $postData['search']['value']);
                }
                
                // last loop
                if(count($this->column_search) - 1 == $i){
                    // close bracket
                    $this->db->group_end();
                }
            }
            $i++;
        }
         
        if(isset($postData['order'])){
            $this->db->order_by($this->column_order[$postData['order']['0']['column']], $postData['order']['0']['dir']);
        }else if(isset($this->order)){
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

	// neuve
	public function countFiles($codeLote)
	{
		$this->db->like('code_lote', $codeLote);
		$this->db->from('_datos_api');
		return  $this->db->count_all_results();
	}

	public function getBatchFiles($codeLote)
	{
		$this->db->select('_datos_api.*,_datos_api.id as id_file');
		$this->db->like('code_lote', $codeLote);
		$this->db->from('_datos_api');
		return  $this->db->get()->result();
	}


	public function batch_dt($id)
	{

		$draw = intval(2);
		$start = intval(0);
		$length = intval(0);

		$indexaciones  = '-';

		if ($id) {
			$query = $this->db->select('*')
				->where('id_lote', $id)
				->get('_datos_api');
		} else {
			$query = $this->db->select("*")->get('_datos_api');
		}

		$query->result();

		foreach ($query->result() as $r) {


			$indexaciones = $this->Manager_model->count_data('_indexaciones', $r->nro_cuenta);


			$acciones = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown" aria-expanded="false"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(22px, 19px, 0px);"><a href="/Admin/Lecturas/Views/' . $r->id . '" class="dropdown-item"><i class="icon-file-pdf"></i>Editar</a></div></div></div>';

			$archivo = explode('/', $r->nombre_archivo, 4);


			$textoDataConsolidar = 'PROVEEDOR: ' . $r->nombre_proveedor . ' - CUENTA: ' . $r->nro_cuenta;
			$indexacion = '';
			$accionIndexar = '';
			if ($indexacion = $this->Manager_model->get_indexacion('_indexaciones', $r->nro_cuenta)) {

				$indexacion = $indexacion->id;
				$accionIndexar = '<a data-id_indexador="' . $indexacion . '" data-id_lectura_api="' . $r->id . '" data-data_cons="' . $textoDataConsolidar . '" id="consolidar" title="Consolidar archivo" href="/Admin/Lecturas/Indexar/' . $r->id . '" class=" text-success "><i class="icon-database-add" title="Consolidar archivo"></i> </a>';
			};


			$accionesVer = '<a title="ver archivo" href="/Admin/Lecturas/Views/' . $r->id . '"  class=" "><i class="icon-eye4" title="ver archivo"></i> </a> ';


			$datos[] = array(
				$r->nro_cuenta,
				$r->nro_medidor,
				$r->nro_factura,
				$r->periodo_del_consumo,
				$r->fecha_emision,
				$r->vencimiento_del_pago,
				$r->total_importe,
				$r->total_vencido,
				$r->consumo,
				$indexaciones,
				$archivo[3],
				$accionesVer . $accionIndexar,
			);
		}



		$resultx = array(
			"draw" => $draw,
			"recordsTotal" => 15,
			"recordsFiltered" => 8,
			"data" => $datos
		);

		echo json_encode($resultx);
		exit();
	}

	public function lotes_dt()
{
    // CÓDIGO ACTUAL (QUITAR EL DEBUG)
    /*
    echo '<pre>';
    var_dump($_REQUEST);
    echo '</pre>';
    die();
    */
    
    $datos = [];

    if ($_REQUEST['id_proveedor']) {
        $columns = array(
            0 => 'id',
            1 => 'fecha_add',
        );
        $where = "";
        
        // Asumiendo que $_REQUEST['table'] es '_lotes'
        $tableName = $_REQUEST['table']; 

        $totalRecordsSql = "SELECT count(*) as total FROM " . $tableName . " " . $where;
        $total = $this->db->query($totalRecordsSql);

        if (!empty($_REQUEST['search']['value'])) {
            $where .= " WHERE ( code LIKE '" . $_REQUEST['search']['value'] . "%' ";
            $where .= " OR user_add LIKE '" . $_REQUEST['search']['value'] . "%' )"; 
            // ⚠️ Nota: Revisé tu AND y lo moví fuera del OR por seguridad. Ajusta si es necesario.
            if (strpos($where, 'WHERE') === false) {
                 $where .= " WHERE ";
            } else {
                 $where .= " AND ";
            }
            $where .= " id_proveedor = '" . $_REQUEST['id_proveedor'] . "'";
        } else {
             $where .= " WHERE id_proveedor = '" . $_REQUEST['id_proveedor'] . "'";
        }


        // ------------------------------------------------------------------------------------------------
        // ✅ CÓDIGO CORREGIDO: SELECCIÓN CON DATOS DE RESUMEN Y CONSOLIDADOS
        // ------------------------------------------------------------------------------------------------
        $sql = "
            SELECT 
                t1.*, 
                t2.total_archivos,
                t1.consolidado AS lote_consolidado_flag,
                (
                    SELECT COUNT(id) 
                    FROM _datos_api 
                    WHERE id_lote = t1.id 
                    AND consolidado = 1
                ) AS consolidados_lote,
                _proveedores.nombre as proveedor
            FROM " . $tableName . " t1 
            LEFT JOIN _lotes_resumen t2 ON t1.id = t2.id_lote
            JOIN _proveedores ON _proveedores.id = t1.id_proveedor
            " . $where . "
            ORDER BY " . $columns[$_REQUEST['order'][0]['column']] . " " . $_REQUEST['order'][0]['dir'] . " 
            LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];
        // ------------------------------------------------------------------------------------------------

        $query = $this->db->query($sql);
    } else {
        $query = $this->db->select("*")->get($_REQUEST['table']);
        // ⚠️ NOTA: Si esta sección es para "todos los proveedores" también debe unirse a _lotes_resumen.
        // Si no se usa, ignora.
    }


    // El resto de la función es donde se usan los datos

    foreach ($query->result() as $r) {

        $user = $this->ion_auth->user($r->user_add)->row();
        
        // ------------------------------------------------------------------
        // ✅ NUEVA LÓGICA DE LA COLUMNA CONSOLIDADO (ÍNDICE 6 EN TU ARRAY)
        // ------------------------------------------------------------------
        
        $total = (int)$r->total_archivos;
        $consolidados = (int)$r->consolidados_lote; 
        $consolidado_flag = (int)$r->lote_consolidado_flag; // Estado final de _lotes
        
        $clase_texto = 'text-warning'; 
        $contenido_columna = "{$total} | {$consolidados}";
        $title_accion = "Pendiente: {$consolidados} de {$total}";

        if ($total == 0) {
            $contenido_columna = '0 | 0';
            $clase_texto = 'text-muted';
            $title_accion = 'Sin archivos';
        } else if ($consolidado_flag == 1) {
            // Lote 100% Consolidado (Bandera Final)
            $contenido_columna = '<i class="icon-checkmark3"></i>'; // Mostrar Tilde (V)
            $clase_texto = 'text-success'; 
            $title_accion = "Lote 100% Consolidado";
        } else if ($consolidados > 0) {
            // Consolidación en progreso (Ej: 20|05)
            $clase_texto = 'text-info';
            $title_accion = "Consolidación en progreso";
        } else {
            // Ninguno consolidado aún (Ej: 20|00, antes la 'X')
            $clase_texto = 'text-danger';
        }

        $columna_consolidado = '
            <span 
                data-code="' . $r->code . '" 
                class="' . $clase_texto . ' consolidar-accion"
                title="' . $title_accion . '"
            >
                <a href="javascript:void(0)" class="btn-consolidar-lote">
                    ' . $contenido_columna . '
                </a>
            </span>';
            
        // ------------------------------------------------------------------

        // ... (El resto del código de acciones no cambia, solo su uso)
        $iniAcciones = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown" aria-expanded="false"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(22px, 19px, 0px);">';
        $accView = '<a href="/Admin/Lotes/viewBatch/' . $r->id . '" class="dropdown-item"><i class=" icon-zoomin3"></i>Ver Lote</a>';
        $accConsolidar = '<a href="/Admin/Lotes/Consolidar/' . $r->id . '" class="dropdown-item"><i class="icon-folder-plus2"></i>Consolidar</a>';
        $finAcciones = '</div></div></div>';
        $accionesVer = '<a title="ver archivo" href="/Admin/Lotes/viewBatch/' . $r->id . '"  class=" "><i class="icon-eye4" title="ver"></i> </a> ';
        $acciones = $iniAcciones . $accView . $accConsolidar . $finAcciones;
        
        $datos[] = array(
            $r->id,
            $r->id_proveedor,
            $r->code,
            fecha_es($r->fecha_add, "d/m/a", true),
            $r->indexado,
            // ⚠️ AQUÍ DEBE IR EL CONTADOR. Asumo que es el ÍNDICE 5 (el campo 'status' original es el 5to dato)
            $columna_consolidado, 
            $r->status, // Si esta columna era la 6, puede que debas moverla/revisarla.
            $this->countFiles($r->code),
            $user->username,
            $accionesVer
        );
    }

    $resulta = array(
        "draw" => intval($_REQUEST['draw']),
        "recordsTotal" => $total->result()[0]->total,
        "recordsFiltered" => $total->result()[0]->total,
        "data" => $datos
    );

    echo json_encode($resulta);
}

	public function crearLote_old()
	{

		$data['user_add'] = $this->user->id;
		$data['id_proveedor'] = $_POST['id_proveedor'];
		$data['cant'] = $_POST['cant'];
		$data['code'] = $_POST['code_lote'];

		try {

			$query = $this->db->get_where('_lotes', array('code' => $_POST['code_lote']));
			$currLote = $query->result();

			if ($currLote) {
				//urrLote->cant ++;

			} else {
				$this->db->insert('_lotes', $data);
				$insertId = $this->db->insert_id();
				$query = $this->db->get_where('_lotes', array('id' => $insertId));
				$currLote = $query->result();
			}

			$this->db->where('code_lote',$_POST['code_lote']);
			$this->db->from('_datos_api	');
			$totalFiles =  $this->db->count_all_results();


			$this->db->set('cant',$totalFiles );
			$this->db->where('code', $_POST['code_lote']);
			$this->db->update('_lotes');


			$query = $this->db->get_where('_lotes', array('code' => $_POST['code_lote']));
			$currLote = $query->result();

			return $currLote;
		} catch (Exception $e) {
			var_dump($e->getMessage());
		}
	}
	public function crearLote()
{
    $data['user_add'] = $this->user->id;
    $data['id_proveedor'] = $_POST['id_proveedor'];
    // El valor de $_POST['cant'] no parece usarse en la BD, lo ignoramos por ahora.
    // $data['cant'] = $_POST['cant'];
    $data['code'] = $_POST['code_lote'];

    try {
        $query = $this->db->get_where('_lotes', array('code' => $_POST['code_lote']));
        $currLote = $query->result();
        $id_lote = null;

        if ($currLote) {
            // Lote ya existe, obtenemos su ID
            $id_lote = $currLote[0]->id;
        } else {
            // Lote NO existe, lo insertamos
            $this->db->insert('_lotes', $data);
            $id_lote = $this->db->insert_id();
            $query = $this->db->get_where('_lotes', array('id' => $id_lote));
            $currLote = $query->result();
        }

        // 1. Contar archivos actuales en _datos_api
        $this->db->where('code_lote', $_POST['code_lote']);
        $this->db->from('_datos_api ');
        $totalFiles = $this->db->count_all_results();

        // 2. Actualizar el campo 'cant' en _lotes (Lógica existente)
        $this->db->set('cant', $totalFiles);
        $this->db->where('code', $_POST['code_lote']);
        $this->db->update('_lotes');

        // --------------------------------------------------------
        // 3. MANTENIMIENTO: Insertar/Actualizar _lotes_resumen (Inicialización/Recálculo)
        // Usamos la inserción con duplicado para cubrir los dos casos:
        // a) Lote nuevo: Inserta con totalFiles y el mismo valor para sin_indexar (ya que no hay indexaciones aún).
        // b) Lote existente: Actualiza total_archivos (en caso de que se suban más).

        if ($id_lote) {
            $this->db->query("
                INSERT INTO _lotes_resumen (id_lote, total_archivos, archivos_sin_indexar)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    total_archivos = VALUES(total_archivos),
                    -- Mantenemos archivos_sin_indexar igual al total al inicio, 
                    -- pero solo si el registro es nuevo. Si ya existe, NO lo tocamos aquí.
                    -- Mejor usar la función de recálculo completa.
                    archivos_sin_indexar = IF(total_archivos = 0, VALUES(total_archivos), archivos_sin_indexar)",
                [
                    $id_lote, 
                    $totalFiles, 
                    $totalFiles // Se asume que totalFiles = archivos_sin_indexar en este punto.
                ]
            );
            
            /* * Opción más limpia:
            * Llamar a tu función de recálculo completa para garantizar precisión
            * $this->load->model('Lecturas_model');
            * $this->Lecturas_model->actualizar_resumen_lote($id_lote); 
            */
        }
        // --------------------------------------------------------

        $query = $this->db->get_where('_lotes', array('code' => $_POST['code_lote']));
        $currLote = $query->result();

        return $currLote;
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }
}
}
