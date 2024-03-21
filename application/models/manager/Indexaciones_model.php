<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Indexaciones_model extends CI_Model
{

    public function grabar_datos($tabla, $data) {

        try {
        $this->db->insert($tabla, $data);
    } catch (Exception $e) {
        // this will not catch DB related errors. But it will include them, because this is more general. 
        var_dump($e->getMessage());
  
    }
    
    }


	public function get_indexaciones($nro_cuenta=null)
	{

		$draw = intval(2);
		$start = intval(0);
		$length = intval(0);
		$dependencia  ='';
        $programa  ='';
		
		$this->db->select(
		'_indexaciones.id as id_index,
		_indexaciones.*,
		_programas.id_interno as prog_id_interno,
		_proyectos.id_interno as proy_id_interno,
		_secretarias.secretaria as secretaria,
		_dependencias.dependencia as dependencia,
		_proveedores.nombre as proveedor,
		_tipo_pago.tip_nombre as tipo_pago
		');
		$this->db->where('nro_cuenta', $nro_cuenta);
		$this->db->join('_programas', '_programas.id = _indexaciones.id_programa');
		$this->db->join('_proyectos', '_proyectos.id = _indexaciones.id_proyecto','LEFT');
		$this->db->join('_secretarias', '_secretarias.id = _indexaciones.id_secretaria','');
		$this->db->join('_dependencias', '_dependencias.id = _indexaciones.id_dependencia','');
		$this->db->join('_proveedores', '_proveedores.id = _indexaciones.id_proveedor','');
		$this->db->join('_tipo_pago', '_tipo_pago.tip_id = _indexaciones.tipo_pago','');
		$this->db->from('_indexaciones');

		$datos = [];

		$acciones = 'acciones';
		foreach ($result = $this->db->get()->result() as $r) {
			$datos[] = array(
				$r->expediente,
				$r->nro_cuenta,
				$r->secretaria,
				$r->dependencia,
				$r->prog_id_interno,
				$r->proy_id_interno,
				$r->proveedor,
				$r->tipo_pago,
			
			);
			
		}

		$result = array(
			"draw" => $draw,
			"recordsTotal" => count($result),
			"recordsFiltered" =>  count($result),
			"data" => $datos
		);

		return json_encode($result);
	}
	public function list_dt()
	{

		$draw = intval(2);
		$start = intval(0);
		$length = intval(0);
        $dependencia  ='';
        $programa  ='';

		$query = $this->db->select("*")->get('_indexaciones');

		$datos = [];



		foreach ($query->result() as $r) {



            $secretaria = $this->Manager_model->get_data('_secretarias',$r->id_secretaria);
            if($dependencia = $this->Manager_model->get_data('_dependencias',$r->id_dependencia)){
                $dependencia =  $dependencia->dependencia;
            }
            // $dependencia = $this->Manager_model->get_data('_dependencias',$r->id_dependencia);
            if($programa = $this->Manager_model->get_data('_programas',$r->id_programa)){
				$programa = $programa->descripcion;
			}
            $proveedor = $this->Manager_model->get_data('_proveedores',$r->id_proveedor);
			
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
				$r->nro_cuenta,
				$secretaria->secretaria,
				$dependencia,
				$programa,
				$r->id_proyecto,
				$proveedor->nombre,
				get_tipoPago($r->tipo_pago),
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