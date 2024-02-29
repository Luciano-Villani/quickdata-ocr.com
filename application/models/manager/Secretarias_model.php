<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Secretarias_model extends CI_Model
{

	public function grabar_datos($tabla, $data)
	{

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

		$query = $this->db->select("*")->get('_secretarias');

		$datos = [];


		foreach ($query->result() as $r) {



			$acciones = '<div class="list-icons">
										<div class="dropdown">
											<a href="#" class="list-icons-item" data-toggle="dropdown" aria-expanded="false">
												<i class="icon-menu9"></i>
											</a>

											<div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(22px, 19px, 0px);">
												<a href="Admin/usuarios/agregar/' . $r->id . '" class="dropdown-item"><i class="icon-file-pdf"></i>Editar</a>
				
											</div>
										</div>
									</div>';


			$datos[] = array(
				$r->id,
				$r->rafam,
				$r->major,
				$r->secretaria,
				$r->fecha_alta,
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

	public function obtener_contenido_select($tabla,$orden = 'id DESC')
	{
		$query = $this->db->select('id,secretaria')
			->order_by($orden)
			->get('_secretarias');

		if ($query->result() > 0) {

			$my_array = array();
			$my_array[0] = 'SELECCIONAR SECRETARIA';
			foreach ($query->result() as $data) {
				$my_array[$data->id] = $data->secretaria;

			}
			return $my_array;
		}

		return FALSE;
	}

}