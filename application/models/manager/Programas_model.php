<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Programas_model extends CI_Model
{

    public function grabar_datos($tabla, $data) {

        try {
        $this->db->insert($tabla, $data);
    } catch (Exception $e) {
        // this will not catch DB related errors. But it will include them, because this is more general. 
        var_dump($e->getMessage());
  
    }
    
    }

	public function getIdInterno(){
		$queryTotal = $this->db->select("id, id_interno")->order_by('id asc')->get('_programas');
		return $queryTotal->result_array();
	}

	public function list_dt()
	{


		// echo '<pre>';
		// var_dump( $_REQUEST ); 
		// echo '</pre>';
		// die();
		$draw = intval(2);
		$start = intval(0);
		$length = intval(0);
		$dependencia  ='';
        $programa  ='';
		$queryTotal = $this->db->select("*")->get('_programas');
		$query = $this->db->select("*")->limit($_REQUEST['length'],$_REQUEST['start'])->get('_programas');
		// $this;
		// $this->db->get('_programas');

		$datos = [];
		$query->result();
		// echo $this->db->last_query();

		foreach ($query->result() as $r) {



            $secretaria = $this->Manager_model->get_data('_secretarias',$r->id_secretaria);
			if($dependencia = $this->Manager_model->get_data('_dependencias',$r->id_dependencia)){
                $dependencia =  $dependencia->dependencia;
            }
		// echo $secretaria->secretaria;
		// echo $dependencia->dependencia;
		
			$acciones = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown" aria-expanded="false"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(22px, 19px, 0px);"><a href="Admin/usuarios/agregar/'.$r->id.'" class="dropdown-item"><i class="icon-file-pdf"></i>Editar</a></div></div></div>';
                       

			$datos[] = array(
				// $r->id,
				$r->id_interno,
				$r->descripcion,
				$secretaria->major.' - '.$secretaria->secretaria,
				// $dependencia,
				// $r->fecha_alta,
				 'acciones',
			);
		}

		$result = array(
			"draw" => $draw,
			"recordsTotal" => $query->num_rows(),
			"recordsFiltered" => $queryTotal->num_rows(),
			"data" => $datos
		);

		echo json_encode($result);
	}


	public function list_profiles_dt()
	{

		$draw = intval(2);
		$start = intval(0);
		$length = intval(0);

		$query = $this->db->select("*")->get('groups');

		$datos = [];


		foreach ($query->result() as $r) {

			

			$roles = '';
		
				$roles .= '<span class="ml-1 badge badge-flat border-' . $r->color . ' text-' . $r->color . '">' . $r->description . '</span>';
			
			//				$user_groups ='';
			if ($r->active == '1') {
				$estado = '<span class="badge badge-success">activo</span>';
			} else {
				$estado = '<span class="badge badge-danger">inactivo</span>';
			}
			;

			$acciones = '<div class="list-icons">
										<div class="dropdown">
											<a href="#" class="list-icons-item" data-toggle="dropdown" aria-expanded="false">
												<i class="icon-menu9"></i>
											</a>

											<div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(22px, 19px, 0px);">
												<a href="#" class="dropdown-item"><i class="icon-file-pdf"></i> Export to .pdf</a>
												<a href="#" class="dropdown-item"><i class="icon-file-excel"></i> Export to .csv</a>
												<a href="#" class="dropdown-item"><i class="icon-file-word"></i> Export to .doc</a>
											</div>
										</div>
									</div>';

			$datos[] = array(
				$r->id,
				$r->name,
				$r->description,
				$roles,
				$r->estado = $estado,
				$r->acciones = $acciones,
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