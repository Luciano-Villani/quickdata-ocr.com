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
		$this->gelementos = [];

		if ($query->result() > 0) {
			foreach ($query->result_array() as $key => $reg) {
				array_push($this->gperiodos, $reg['periodo_contable']);
			}
			;
		}


		// quito periodos contables con datos erroneos, enero, febreo, marzo
		// $this->peri = array_slice($this->gperiodos, -1, null, true);
		// foreach ($this->peri as $key => $val) {
		// 	unset($this->gperiodos[$key]);
		// }

	}



	public function GraphsGr()
	{

		$memData = $this->Graph_model->getRows($_POST);
	}


	/*
		$elementos = array(
					  'name' => $proveedor_grafico,
					  'type' => 'bar',
					  'emphasis' => array(
						  'focus' => 'series'
					  ),
					  'data' => $valores

				  );
	   */

	function cube($d)
	{

		$previoNombre = [];
		$previoimporte = [];

		$claveProveedor = array_search($d->proveedor, $this->gelementos);

		if ($claveProveedor) {
			if (!array_key_exists($d->proveedor, $this->gelementos)) {
				$this->gelementos[$d->proveedor] = [];
			}
			array_push($this->gelementos[$d->proveedor], $d->total);
		} else {


			if (!array_key_exists($d->proveedor, $this->gelementos)) {
				$this->gelementos[$d->proveedor] = [];
			}

			array_push($this->gelementos[$d->proveedor], $d->total);

			if (array_key_exists($d->proveedor, $this->gelementos)) {
				// array_push($this->gelementos[$d->proveedor], '444444');
				// echo '<pre>'.$d->proveedor;
				// var_dump($this->gelementos ); 
				// echo '</pre>';
				// die('si existe');
			} else {

				// die('no es');
			}
		}

		// if ($claveProveedor && is_int($claveProveedor)) {

		// 	// die('si');

		// 	if (array_search($claveProveedor, $this->gelementos)) {
		// 		array_push($this->gelementos[$claveProveedor], $d->total);


		// 	}else{
		// 		// $this->gelementos[$claveProveedor]=[];
		// 		array_push($this->gelementos[$claveProveedor], $d->total);
		// 		// $this->gelementos[$claveProveedor]=[$d->proveedor];
		// 	}


		// 	// array_push($this->gelementos[$claveProveedor], $d->total);


		// 	// var_dump( $this->gelementos ); 
		// 	// echo '</pre>';
		// 	// die();
		// 	// if(array_search($d->proveedor,array_column($this->proveedoresdb, 'proveedor'))){

		// 	// 	echo 'si';
		// 	// 	echo '<pre>';

		// 	// 	var_dump( $this->gelementos ); 
		// 	// 	echo '</pre>';

		// 	// }else{
		// 	// 	echo 'no'.$d->proveedor;
		// 	// 	$this->gelementos['nombre']=[$d->proveedor];
		// 	// 	array_push($this->gelementos,$d->total);

		// 	// }
		// 	// array_push($this->gelementos[$claveProveedor], $d->total);
		// } else {




		// 	// array_push($this->gelementos[$d->proveedor],$d->total);
		// 	// $this->gelementos[$claveProveedor] = $d->proveedor;
		// 	//  var_dump( $this->gelementos ); 


		// }

		// echo '<pre>final->';
		// var_dump($this->gelementos);
		// echo '</pre>';
		// // die();
		// // die();
		return $this->gelementos;
	}


	public function setPeriodos($e)
	{

		// echo '<pre>mando';
		// var_dump( $e ); 
		// echo '</pre>';
		$peroi = $this->gperiodos;


		echo '<pre>';
		var_dump($peroi);
		echo '</pre>';
		die();
		foreach ($e as $i => $val) {
			$found_key = array_search($val['periodo'], $peroi);
			// echo '<br>clave->' . $found_key;
			// echo $i['periodo'];

			if (is_int($found_key)) {
				// echo '<br>Eliminara'.$peroi[$found_key];
				// unset($peroi[$found_key]);
			}
		}



		// echo '<pre>--->';
		// // var_dump( $e ); 
		// var_dump($peroi);
		// echo '</pre>';
		// die();

		foreach ($peroi as $i => $val) {
			// echo '<br>' . $i;
			// echo '<br>' . $val;

			// die();
			array_push($e[$i], array('periodo' => $val, 'total' => 0));
		}


		return $e;
	}


	public function Graphs()
	{


		$periodos_contables = getPeriodos();


		$totalesPorServicio[] = [];
		$periodos_contables = [];
		$gperiodos = [];
		$series = [];
		$this->proveedoresdb = $this->Graph_model->getProveedores(true);

		if ($this->input->is_ajax_request()) {


			$_POST['periodos'] = $this->gperiodos;
			$memData = $this->Graph_model->getRows($_POST);


			foreach ($memData as $x) {

				if (array_key_exists(trim($x->proveedor), $this->gelementos)) {

					$found_key = array_search($x->periodo_contable, array_column($this->gelementos[trim($x->proveedor)], 'periodo'));

					if (is_int($found_key)) {
						$this->gelementos[trim($x->proveedor)][$found_key]['total'] = $x->total;
					}


				} else {

					foreach ($this->gperiodos as $key => $val) {
						if (!array_key_exists($key, $this->gelementos)) {
							$this->gelementos[trim($x->proveedor)][$key] = array('periodo' => $val, 'total' => 00.00);
						}
					}

					$found_key = array_search($x->periodo_contable, array_column($this->gelementos[trim($x->proveedor)], 'periodo'));
					if (is_int($found_key)) {
						$this->gelementos[trim($x->proveedor)][$found_key]['total'] = $x->total;
					}


				}
			}


			$finales = [];


			foreach ($this->gelementos as $key => $val) {

				$misvalores = [];
				foreach ($this->gelementos[$key] as $r) {
					$misvalores[] = floatval($r['total']);
				}

				$data = array(
					'name' => $key,
					'type' => 'bar',
					'emphasis' => array(
						'focus' => 'series'
					),
					'barWidth' => '10%',
					'data' => $misvalores,
					// 'label'=>array(
					// 	'show'=>true,
					// 	'position'=>'inside',
					// 	'rotate'=> 90,	
					// )
				);


				array_push($finales, $data);
			}
			;


			$datos = [];

			$text = 'GRAFICO';
			$subtext = 'v1.0';
			$proveedor_grafico = '';
			$data_datatable = [];

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
						$newPeri[trim($item[1])][$clavePeriodo] = $total;
					} else {
						if (!isset($newPeri[trim($item[1])][$key])) {
							$newPeri[trim($item[1])][$key] = 0;
						}
						$total = $newPeri[trim($item[1])][$key] + $item[2];
						$newPeri[trim($item[1])][$clavePeriodo] = $total;
					}
				}
				;
			}
			;

			array_unshift($this->gperiodos, 'este_lo_borrare');


			unset($this->gperiodos[0]);



			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => count($data_datatable),
				"recordsFiltered" => $this->Graph_model->countFiltered($_POST),
				"data" => $data_datatable,
				"Glegen" => $periodos_contables,
				'gperiodos' => getPeriodos(),
				'Gseries' => $series,
				'test' => $newPeri,
				'finales' => $finales,
				'text' => $text,
				'subtext' => $subtext,
				'queri' => trim($this->db->last_query()),
				'request' => $_REQUEST
				,

			);
			// Output to JSON format
			echo json_encode($output);
		}
	}
	public function Graphs2()
	{
		$periodos_contables = getPeriodos();
		// $datos = $this->Graph_model->get_alldata('_consolidados', false);


		// foreach($datos as $dato){

		// 			 $clave = array_search($dato->periodo_contable, $periodos_contables); 


		// 			 $data = array(
		// 				'periodo' => $clave,

		// 			);

		// 			$this->db->where('id', $dato->id);
		// 			$this->db->update('_consolidados', $data);

		// }


		$cambiaTitulo = false;
		$text = '';
		$subtext = '';
		if ($_POST['id_proveedor'] != 0) {
			$cambiaTitulo = true;
			$proveedor = $this->Graph_model->get_data('_proveedores', $_POST['id_proveedor']);
			$text .= $proveedor->nombre;
		}
		if ($_POST['id_secretaria'] != 0) {
			$cambiaTitulo = true;
			$secretaria = $this->Graph_model->get_data('_secretarias', $_POST['id_secretaria']);
			$text .= "\n" . $secretaria->secretaria;
		}
		if ($_POST['periodo_contable'] != 0 && $_POST['periodo_contable'] != 'PERIODO CONTABLE') {
			$cambiaTitulo = true;
			$text .= $_POST['periodo_contable'];
		}
		if ($_POST['id_programa'] != 0) {
			$cambiaTitulo = true;
			$programa = $this->Graph_model->get_data('_programas', $_POST['id_programa']);
			$text .= "\n" . strtoupper($programa->descripcion);
		}


		if ($_POST['id_proyecto'] != 0) {
			$cambiaTitulo = true;
			$proyecto = $this->Graph_model->get_data('_proyectos', $_POST['id_proyecto']);
			$text .= "\n" . strtoupper($proyecto->descripcion);
		}
		if (!$cambiaTitulo) {
			$text = 'Resumen de Gastos';
			$subtext = '-';
		}



		$totalesPorServicio[] = [];

		$gperiodos = [];
		$series = [];
		$this->proveedoresdb = $this->Graph_model->getProveedores(true);

		$this->gperiodos = getPeriodos();
		if ($this->input->is_ajax_request()) {
			$_POST['periodos'] = getPeriodos();
			$memData = $this->Graph_model->getRows($_POST);

			foreach ($memData as $x) {



				//  if (array_key_exists(trim($x->proveedor), $this->gelementos)) {
				// 	 die('si');
				// }else{
				// 	die('no');
				// }



				if (array_key_exists(trim($x->proveedor), $this->gelementos)) {

					$found_key = array_search($x->periodo_contable, array_column($this->gelementos[trim($x->proveedor)], 'periodo'));

					if (is_int($found_key)) {
						$this->gelementos[trim($x->proveedor)][$found_key]['total'] = $x->total;
					}

				} else {

					foreach ($periodos_contables as $key => $val) {
						if (!array_key_exists($key, $this->gelementos)) {
							$this->gelementos[trim($x->proveedor)][$key] = array('periodo' => $val, 'total' => 00.0);
						}
					}

					$found_key = array_search($x->periodo_contable, array_column($this->gelementos[trim($x->proveedor)], 'periodo'));
					if (is_int($found_key)) {
						$this->gelementos[trim($x->proveedor)][$found_key]['total'] = $x->total;
					}
				}
			}


			$finales = [];


			foreach ($this->gelementos as $key => $val) {


				//  echo '<pre> ';
				//  var_dump( $this->gelementos); 
				//  echo '</pre>';
				//  die(); 
				$misvalores = [];
				foreach ($this->gelementos[$key] as $r) {
					$misvalores[] = floatval($r['total']);
					if (floatval($r['total']) > 0) {

					}
				}

				$data = array(
					'name' => $key,
					'type' => 'bar',
					// 'stack'=> 'total',
					"label"=> "labelOption",
					'emphasis' => array(
						'focus' => 'series'
					),
					'barWidth' => '15%',
					'data' => $misvalores,
					// 'label'=>array(
					// 	'show'=>true,
					// 	'position'=>'inside',
					// 	'rotate'=> 90,	
					// )
				);


				array_push($finales, $data);
			}
			;



			$proveedor_grafico = '';
			$data_datatable = [];

			$dataz = $row = array();
			$valores = array();
			foreach ($memData as $r) {

				$data_datatable[] = array(
					strtoupper($r->periodo_contable),
					strtoupper($r->proveedor),
					strtoupper($r->periodo),
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
						$newPeri[trim($item[1])][$clavePeriodo] = $total;
					} else {
						if (!isset($newPeri[trim($item[1])][$key])) {
							$newPeri[trim($item[1])][$key] = 0;
						}
						$total = $newPeri[trim($item[1])][$key] + $item[2];
						$newPeri[trim($item[1])][$clavePeriodo] = $total;
					}
				}
				;
			}
			;

			array_unshift($this->gperiodos, 'este_lo_borrare');


			unset($this->gperiodos[0]);

			if (count($data_datatable) == 0) {
				$text .= "\nNO HAY DATOS";
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => count($data_datatable),
				"recordsFiltered" => $this->Graph_model->countFiltered($_POST),
				"data" => $data_datatable,
				// "Glegen" => $periodos_contables,
				'gperiodos' => getPeriodos(),
				// 'Gseries' => $series,
				// 'test' => $newPeri,
				'finales' => $finales,
				'text' => $text,
				'subtext' => $subtext,
				'queri' => trim($this->db->last_query()),
				'request' => $_REQUEST
				,

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
			base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
		);

		$this->data['select_periodo_contable'] = $this->Manager_model->obtener_contenido_select('_consolidados', '', 'periodo_contable', 'periodo_contable DESC', false);
		$this->data['select_secretarias'] = $this->Graph_model->obtener_contenido_select('_secretarias', 'SECRETARIA', 'secretaria', 'id ASC', true);
		$this->data['select_proveedores'] = $this->Manager_model->obtener_contenido_select('_proveedores', 'PORVEEDOR', 'nombre', 'id ASC', true);
		$this->data['select_programas'] = $this->Manager_model->obtener_contenido_select('_programas', 'SELECCIONE PROGRAMA', 'descripcion', '');
		$this->data['select_proyectos'] = $this->Manager_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC'); // Agregado
		$this->data['select_tipo_pago'] = $this->Manager_model->obtener_contenido_select('_tipo_pago', '', 'tip_nombre', 'tip_id ASC', false);
		$this->data['select_periodo_contable'] = $this->Manager_model->obtener_contenido_select('_consolidados', 'PERIODO CONTABLE', 'periodo_contable', 'periodo_contable DESC', true);
		// $this->data['select_periodo_contable'] =getPeriodos();

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
