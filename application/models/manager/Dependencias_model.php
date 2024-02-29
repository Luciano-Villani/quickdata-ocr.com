<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Dependencias_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		// Set table name
		$this->table = '_dependencias';
		// Set orderable column fields
		$this->column_order = array('id_dependencia','_dependencias.dependencia','_dependencias.direccion','users.last_name','users.first_name');
		$this->column_search =array('_secretarias.secretaria','_dependencias.dependencia','_dependencias.direccion','users.last_name','users.first_name');
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
	private function _get_datatables_query($postData){
	       

        $this->db->select('_dependencias.*,_dependencias.id as id_dependencia , _secretarias.secretaria,users.*, CONCAT(users.first_name," ", users.last_name) as user_add');
		$this->db->join('_secretarias','_secretarias.id = _dependencias.id_secretaria','');
		$this->db->join('users','users.id = _dependencias.user_add','');
		

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

		$this->db->from($this->table);
		
    }
	public function countAll(){
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }
	public function countAllFiles(){
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }




    public function countFiltered($postData){
        $this->_get_datatables_query($postData);
        $query = $this->db->get();
        return $query->num_rows();
    } 

    public function grabar_datos($tabla, $data) {

        try {
        $this->db->insert($tabla, $data);
    } catch (Exception $e) {
        // this will not catch DB related errors. But it will include them, because this is more general. 
        var_dump($e->getMessage());
  
    }
    
    }

	public function list_dt()
	{
		$this->load->model('Manager_model');
		$draw = intval(2);
		$start = intval(0);
		$length = intval(0);

		$query = $this->db->select("*")->get('_dependencias');

		$datos = [];


		foreach ($query->result() as $r) {

			$secretaria = $this->Manager_model->get_data('_secretarias',$r->id_secretaria);

			$acciones = '<div class="list-icons">
										<div class="dropdown">
											<a href="#" class="list-icons-item" data-toggle="dropdown" aria-expanded="false">
												<i class="icon-menu9"></i>
											</a>

											<div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(22px, 19px, 0px);">
												<a href="/Admin/'.ucfirst($this->router->fetch_class()).'/editar/'.$r->id.'" class="dropdown-item"><i class="icon-pencil5"></i>Editar</a>
											</div>
										</div>
									</div>';
                       
									$accionEditar = '<a title="Editar" href="/Admin/'.ucfirst($this->router->fetch_class()).'/editar/'.$r->id.'" class=" text-i´nfo"><i class="icon-database-edit2" title="Editar"></i> </a>';
			$datos[] = array(
				$r->id,
				$secretaria->secretaria,
				$r->dependencia,
				$r->direccion,
				$r->fecha_alta,
				$accionEditar
			);
		}

		$result = array(
			"draw" => $draw,
			"recordsTotal" => $query->num_rows(),
			"recordsFiltered" => $query->num_rows(),
			"data" => $datos
		);

		echo json_encode($result);
	}


}