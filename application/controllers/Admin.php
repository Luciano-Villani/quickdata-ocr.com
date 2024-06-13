<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends backend_controller
{
	function __construct()
	{
		parent::__construct();

		// include APPPATH . 'third_party/Mindee/Client.php';

		$this->data['page_title'] = 'Admin';

		$this->page_datail = 'escturctura base';
		if (!$this->ion_auth->is_admin()) {
			redirect('Login');
		}
		$query = $this->db->query('SELECT periodo_contable FROM u117285061_mvl_ocr_db._consolidados group by periodo_contable  order by id desc');
		$this->miimporte = [];
		$this->gperiodos = [];
		if ($query->result() > 0) {
			foreach ($query->result_array() as $key => $reg) {
				$this->gperiodos[$key] = array($reg['periodo_contable']);
			};
		}
	}

	public function mindee()
	{
		$mindeeClient = new Client("f4b6ebe406cdb615674ae37aabc48929");


		echo '<pre>';
		var_dump($mindeeClient);
		echo '</pre>';
		die();
	}

	function agruparPorTrimestre($datos)
	{

		$agrupados = [];
		foreach ($datos as $dato) {
			$trimestre = strtoupper($dato[0]);
			if (!isset($agrupados[$trimestre])) {
				$agrupados[$trimestre] = [];
			}
			$agrupados[$trimestre][] = $dato;
		}
		return $agrupados;
	}

	public function GraphsGr()
	{

		$memData = $this->Graph_model->getRows($_POST);
	}
	public function Graphs()
	{


		$totalesPorServicio[] = [];
		$periodos_contables = [];
		$gperiodos = [];
		$series = [];
		if ($this->input->is_ajax_request()) {



			
			$memData = $this->Graph_model->getRows($_POST);

			$datos = [];


			$title = '';
			$proveedor_grafico = '';
			$data_datatable=[];
			if ((isset($_POST['secretaria'])) && $_POST['secretaria'] != 'false' && (isset($_POST['secretaria']) && $_POST['secretaria'] != '')) {
                   
				$title = $_POST['secretaria'];
			}
			
			$dataz = $row = array();
			$valores = array();
			foreach ($memData as $r) {

				$data_datatable[] = array(
					strtoupper($r->periodo_contable),
					strtoupper($r->proveedor),
					$r->total,

				);

				$proveedor_grafico = $r->proveedor;
				$valores[] = $r->total;
			}


			$newPeri = [];
			$proveeedor = [];


			foreach ($dataz as $item) {
				if (!isset($proveeedor[trim($item[1])])) {
					$proveeedor[trim($item[1])] = '';
				}

				$proveeedor[$item[1]] = trim($item[1]);

				// $proceso = ;
				foreach ($this->gperiodos as $key => $val) {

					$claveProveedor = array_search(trim($item[1]), $newPeri); // $clave  Periododo;
					if (!isset($newPeri[trim($item[1])])) {
						$newPeri[$item[1]] = [];
					}

					$clavePeriodo = array_search($val[0], $item); // $clave  Periododo;

					if (is_int($clavePeriodo)) {

						if (!isset($newPeri[trim($item[1])][$clavePeriodo])) {
							$newPeri[trim($item[1])][$clavePeriodo] = 0;
						}
						$total = $newPeri[trim($item[1])][$clavePeriodo] + $item[2];
						$newPeri[trim($item[1])][$clavePeriodo] =  $total;
					} else {
						if (!isset($newPeri[trim($item[1])][$key])) {
							$newPeri[trim($item[1])][$key] = 0;
						}
						$total = $newPeri[trim($item[1])][$key] + $item[2];
						$newPeri[trim($item[1])][$clavePeriodo] = $total;
					}
				};
			};


			$elementos = array(
				'name' => $proveedor_grafico,
				'type' => 'bar',
				'emphasis' => array(
					'focus' => 'series'
				),
				'data' => $valores

			);
			// {
			// 	name: 'Video Ads',
			// 	type: 'bar',
			// 	stack: 'Ad',
			// 	emphasis: {
			// 	  focus: 'series'
			// 	},
			// 	data: [150000, 2320000, 2010000, 1540000, 1900000]
			//   }



			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => count($data_datatable),
				"recordsFiltered" => $this->Graph_model->countFiltered($_POST),
				"data" => $data_datatable,
				"Glegen" => $periodos_contables,
				'gperiodos' =>  $this->gperiodos,
				'Gseries' => $series,
				'test' => $newPeri,
				'elementos' => $elementos,
				'title' => $title,

			);
			// Output to JSON format
			echo json_encode($output);
		}
	}
	public function index()
	{

		if ($this->input->is_ajax_request()) {
			$data = $row = array();
			$memData = $this->Graph_model->getRows($_POST);

			foreach ($memData as $r) {

				$indexador = $this->Manager_model->getWhere('_indexaciones', 'nro_cuenta="' . $r->nro_cuenta . '"');

				$accionesVer = '<a title="ver archivo" href="/Admin/Lecturas/Views/' . $r->id_lectura_api . '"  class=" "><i class="icon-eye4" title="ver archivo"></i> </a> ';
				$accionesDelete = '<span class="borrar_dato acciones" data-lote="' . $r->lote . '" data-file="' . $r->nombre_archivo . '"><a title="Borrar lote" href="#"  class=""><i class=" text-danger icon-trash " title="Borrar Datos"></i> </a> </span>';
				$punto = ".";

				if (strlen($r->id_interno_programa) == 1) {
					$r->id_interno_programa = '0' . $r->id_interno_programa;
				}
				if ($r->id_interno_proyecto != '0') {

					if (strlen($r->id_interno_proyecto) == 1) {

						$r->id_interno_proyecto = ".0" . strval($r->id_interno_proyecto);
					} else {
						$r->id_interno_proyecto = "." . $r->id_interno_proyecto;
					}
				} else {
					$r->id_interno_proyecto = '';
				}

				$data[] = array(
					strtoupper($r->periodo_contable),
					$r->proveedora,
					$r->expediente,
					$r->secretaria,
					$r->jurisdiccion,
					$r->id_interno_programa . $r->id_interno_proyecto,
					$r->jurisdiccion,
					$r->objeto,
					$r->dependencia,
					$r->dependencia_direccion,
					$r->tipo_pago,
					$r->nro_cuenta,
					$r->nro_factura,
					$r->periodo_del_consumo,
					fecha_es($r->fecha_vencimiento, 'd-m-a', false),
					fecha_es($r->preventivas, 'd-m-a', false),
					$r->importe,
					$accionesVer . $accionesDelete
				);
			}
			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->Manager_model->countAll(),
				"recordsFiltered" => $this->Manager_model->countFiltered($_POST),
				"data" => $data,
			);
			// Output to JSON format
			echo json_encode($output);
			exit();
		}





		$dataACT = $this->Manager_model->get_alldata('_consolidados');
		/*
		ALTER TABLE `_datos_api` ADD `nombre_archivo_temp` INT(255) NOT NULL AFTER `proximo_vencimiento`, ADD `importe_1` DECIMAL(10,2) NOT NULL AFTER `nombre_archivo_temp`;
		*/

		foreach ($dataACT as $reg) {
			// echo $reg->secretaria;
			// 			$secre = $this->Manager_model->getWhere('_secretarias','secretaria LIKE='.$reg->secretaria);
			// 			echo '<pre>';
			// 			var_dump( $secre ); 
			// 			echo '</pre>';
			// 			die();
		}
		// 		$fecha_actual = date("Y-m-d");
		// // Inicializar un arreglo para almacenar los nombres de los meses
		// $meses_ant = array();

		// // Obtener los nombres de los 7 meses anteriores
		// for ($i = 0; $i < 7; $i++) {
		//     // Restar $i meses a la fecha actual
		//     $fecha_mes_ant = date("Y-m-d", strtotime("-$i months", strtotime($fecha_actual)));
		//     // Obtener el nombre del mes
		//     $nombre_mes = date("F", strtotime($fecha_mes_ant));
		//     // Agregar el nombre del mes al arreglo
		//     $meses_ant[] = fecha_es($nombre_mes,'F a');
		// }

		// // Imprimir los nombres de los meses anteriores
		// echo "Los 7 meses anteriores al actual, incluido, son: ";
		// echo '<pre>';
		// var_dump( $meses_ant ); 
		// echo '</pre>';

		// echo fechasMesNombre();

		$script = array(
			// 'https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js	',
			base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
			// base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/chart.js'),
		);


		$this->data['select_periodo_contable'] = $this->Manager_model->obtener_contenido_select('_consolidados', '', 'periodo_contable', 'periodo_contable DESC', false);
		$this->data['select_secretarias'] = $this->Graph_model->obtener_contenido_select('_secretarias', '', 'secretaria', 'id ASC', false);
		$this->data['select_proveedores'] = $this->Manager_model->obtener_contenido_select('_proveedores', '', 'nombre', 'id ASC', false);

		$this->data['select_tipo_pago'] = $this->Manager_model->obtener_contenido_select('_tipo_pago', '', 'tip_nombre', 'tip_id ASC', false);
		$this->data['select_periodo_contable'] = $this->Manager_model->obtener_contenido_select('_consolidados', '', 'periodo_contable', 'periodo_contable DESC', false);

		$this->data['css_common'] = $this->css_common;

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;
		$this->data['content'] = $this->load->view('manager/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method(), $this->data, TRUE);
		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
		// die('fafa');
	}
}
