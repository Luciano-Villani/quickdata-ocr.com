<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Proyectos_model extends CI_Model
{

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

		$draw = intval(2);
		$start = intval(0);
		$length = intval(0);
        $dependencia  ='';
		$programa = '';

		$query = $this->db->select("*")->get('_proyectos');

		$datos = [];



		foreach ($query->result() as $r) {

            $secretaria = $this->Manager_model->get_data('_secretarias',$r->id_secretaria);
            if($dependencia = $this->Manager_model->get_data('_dependencias',$r->id_dependencia)){
                $dependencia =  $dependencia->dependencia;
            }
            // $dependencia = $this->Manager_model->get_data('_dependencias',$r->id_dependencia);
            $programa = $this->Manager_model->get_data('_programas',$r->id_programa);

		
				if(!$programa){
					$programa=new stdClass()	;
					$programa->descripcion = '';
					$programa->id = '';
				}

		
			$acciones = '<div class="list-icons">
										<div class="dropdown">
											<a href="#" class="list-icons-item" data-toggle="dropdown" aria-expanded="false">
												<i class="icon-menu9"></i>
											</a>

											<div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(22px, 19px, 0px);">
												<a href="Admin/usuarios/agregar/'.$r->id.'" class="dropdown-item"><i class="icon-file-pdf"></i>Editar</a>
				
											</div>
										</div>
									</div>';
                   

			$datos[] = array(
				$r->id,
				$r->id_interno,
				$r->descripcion,
				$programa->id. ' - '.$programa->descripcion,
				$secretaria->major. ' - '.$secretaria->secretaria,
				// $r->fecha_alta,
				 $acciones,
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