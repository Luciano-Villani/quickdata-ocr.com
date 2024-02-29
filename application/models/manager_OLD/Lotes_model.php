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


		echo '<pre>';
		var_dump($_REQUEST);
		echo '</pre>';
		die();

		$datos = [];


		if ($_REQUEST['id_proveedor']) {
			$columns = array(
				0 => 'id',
				1 => 'fecha_add',
			);
			$where = "";

			$totalRecordsSql = "SELECT count(*) as total FROM " . $_REQUEST['table'] . " " . $where;

			$total = $this->db->query($totalRecordsSql);

			if (!empty($_REQUEST['search']['value'])) {
				$where .= " WHERE  ( code LIKE '" . $_REQUEST['search']['value'] . "%' ";
				$where .= " OR user_add LIKE '" . $_REQUEST['search']['value'] . "%' ";
				$where .= " AND id_proveedor = '" . $_REQUEST['id_proveedor'] . " )";
			}


			$sql = "SELECT " . $_REQUEST['table'] . ". *, _proveedores.nombre as proveedor";
			$sql .= " FROM " . $_REQUEST['table'] . " $where";

			$sql .= " JOIN _proveedores ON _proveedores.id = " . $_REQUEST['table'] . ".id_proveedor";
			$sql .= " ORDER BY " . $columns[$_REQUEST['order'][0]['column']] . "   " . $_REQUEST['order'][0]['dir'] . "  LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'];


			$query = $this->db->query($sql);
		} else {
			$query = $this->db->select("*")->get($_REQUEST['table']);
		}


		foreach ($query->result() as $r) {

			$user = $this->ion_auth->user($r->user_add)->row();



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
				$r->status,
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

	public function crearLote()
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


			// echo $this->db->last_query();
// echo '<pre>';
// var_dump( $totalFiles ); 
// echo '</pre>';
// die();
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
}
