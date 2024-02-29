<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Obras_model extends CI_Model
{

	public function list_dt()
	{

		$draw = intval(2);
		$start = intval(0);
		$length = intval(0);
        $dependencia  ='';

		$query = $this->db->select("*")->get('_obras');

		$datos = [];



		foreach ($query->result() as $r) {



			$proyecto = $this->Manager_model->get_data('_proyectos',$r->id_proyecto);
			$programa = $this->Manager_model->get_data('_proyectos',$proyecto->id_programa);

			if($programa =='' OR $programa == null){
				$programa->descripcion = 'lcoakl';
			}
			$secretaria = $this->Manager_model->get_data('_secretarias',$proyecto->id_secretaria);

		
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
                   
									$accionEditar = '<a title="Editar" href="/Admin/'.ucfirst($this->router->fetch_class()).'/editar/'.$r->id.'" class=" text-i´nfo"><i class="icon-database-edit2" title="Editar"></i> </a>';

			$datos[] = array(
				$r->id,
				$r->id_interno,
				$r->descripcion ,
				$proyecto->id_interno.' - '.$proyecto->descripcion,
				$programa->id_interno.' - '.$programa->descripcion,
				$secretaria->major .' - '.$secretaria->secretaria,
				 $accionEditar,
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