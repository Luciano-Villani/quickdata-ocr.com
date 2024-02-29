<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Lecturas_model extends CI_Model
{

	public function lotes_dt($id_proveedor)
	{


		$datos = [];


		if ($id_proveedor) {
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
			$sql .= " ORDER BY " . $columns[$_REQUEST['order'][0]['column']] . "   " . $_REQUEST['order'][0]['dir'] . "  LIMIT " . $_REQUEST['start'] . " ," . $_REQUEST['length'] . "   ";


			$query = $this->db->query($sql);

			// // echo '<pre>';
			// // var_dump($query->result() ); 
			// // echo '</pre>';
			// // die();

		} else {
			$query = $this->db->select("*")->get($_REQUEST['table']);
		}


		foreach ($query->result() as $r) {
			$user = $this->ion_auth->user($r->user_add)->row();



			$acciones = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown" aria-expanded="false"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(22px, 19px, 0px);"><a href="/Admin/Lecturas/Views/' . $r->id . '" class="dropdown-item"><i class="icon-file-pdf"></i>Editar</a></div></div></div>';
			$datos[] = array(
				$r->id,
				$r->proveedor,
				$r->code,
				fecha_es($r->fecha_add, "d/m/a", true),
				$r->indexado,
				$r->status,
				$r->cant,
				$user->username,
				$acciones
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
}
