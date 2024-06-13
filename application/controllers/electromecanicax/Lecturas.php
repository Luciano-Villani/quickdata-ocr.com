<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lecturas extends backend_controller
{

	public function __construct()
	{
		parent::__construct();
		if (!$this->ion_auth->logged_in() && (!$this->ion_auth->is_admin() || !$this->ion_auth->is_electro())) {

			redirect('Login');
		} else {
			$this->load->helper('file');
			$this->load->model('manager/Electromecanica_model', 'electromecanica');
		}
	}

	public function views($id= null)
	{
		// $myDato = $this->encrypt->decode(urldecode($id));
		$myDato = $id;
		
		if ($id == 0 && $_SERVER['REQUEST_METHOD'] === "POST") {


			// // $_POST['fecha_emision']  = date(trim('Y-m-d',$_POST['fecha_emision']));
			// $_POST['fecha_emision']  = fecha_es(trim($_POST['fecha_emision']), 'Y-m-d', false);
			// $_POST['vencimiento_del_pago']  = fecha_es(trim($_POST['vencimiento_del_pago']), 'Y-m-d', false);


			$myDato = $_POST['id'];
			$this->form_validation->set_rules('proveedor', 'Proveedor', 'trim|in_select[0]');
			$this->form_validation->set_rules('nro_cuenta', 'Cuenta', 'trim|required');

			//$this->form_validation->set_rules('nro_medidor', 'Medidor', 'trim|required');
			$this->form_validation->set_rules('nro_factura', 'Factura', 'trim|required');
			// $this->form_validation->set_rules('periodo_del_consumo', 'Período', 'trim|required');
			$this->form_validation->set_rules('fecha_emision', 'Fecha emisión', 'trim|required');
			$this->form_validation->set_rules('total_importe', 'Importe', 'trim|required');

			if ($this->form_validation->run() != FALSE) {
				$id = $_REQUEST['id'];
				unset($_REQUEST['id']);

				//campo fecha vencimiento

				// $timestamp = strtotime(trim($_REQUEST['vencimiento_del_pago']) );

				if (($timestamp = strtotime($_REQUEST['vencimiento_del_pago'])) === false) {
					$_REQUEST['vencimiento_del_pago']='error de lectura';
					
				} else {
					$_REQUEST['vencimiento_del_pago']= date('Y-m-d', $timestamp);
				}

				//campo fecha_emision
				// $timestamp = strtotime(trim($_REQUEST['fecha_emision']) );
				if (($timestamp = strtotime($_REQUEST['fecha_emision'])) === false) {
					$_REQUEST['fecha_emision']='error de lectura';
					
				} else {
					$_REQUEST['fecha_emision']= date('Y-m-d', $timestamp);
				}

				if ($this->db->update('_datos_api_canon', $_REQUEST, array('id' => $_POST['id']))) {


					redirect('Electromecanica/Lecturas/Views/'.$id);
				};
			}
		}

		$registro_api = $this->electromecanica->get_data_api('_datos_api_canon', $myDato);


		$script = array(
			// base_url('assets/manager/js/plugins/tables/datatables/datatables.js'),
			base_url('assets/manager/js/secciones/electromecanica/views.js'),
		);

		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;
		$this->data['result'] = $registro_api;


		// $this->data['nro_cuenta'] = $resultData->nro_cuenta;

		$this->data['indexaciones'] = $this->Indexaciones_model->get_indexaciones($registro_api->nro_cuenta);
		$this->data['select_proveedores'] = $this->electromecanica->obtener_contenido_select('_proveedores_canon', 'SELECCIONE PROVEEDOR', 'nombre', 'id ASC');

		$this->data['content'] = $this->load->view('manager/secciones/electromecanica/' . $this->router->fetch_method(), $this->data, TRUE);

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
	}
	public function checkFile()
	{
		$arc = explode('.', $_POST['name']);
		$nuevoNOmbre = limpiar_caracteres($arc[0]);
		$nombre_archivodb = $nuevoNOmbre . '.' . $arc[1];

		$proveedor = $this->Electromecanica_model->get_data('_proveedores_canon', $_POST['id_proveedor']);
		$nombre_fichero = 'uploader/canon/' . strtolower($proveedor->tipo_proveedor) . '/' . $proveedor->codigo . '/' . $nombre_archivodb;

		$datoDb = $this->Electromecanica_model->getWhere('_datos_api_canon', 'nombre_archivo LIKE "%sadwsdw' . $nombre_archivodb . '%"');


		if (file_exists($nombre_fichero) || $datoDb = NULL) {


			$response = array(
				"status" => 'error'
			);
			echo json_encode($response);
		} else {

			$response = array(
				"status" => 'success'
			);
			echo json_encode($response);
		};
	}
	public function delete_lote()
	{
		$archivos = $this->Electromecanica_model->getBatchFiles($_REQUEST['code']);
		$total = 0;
		foreach ($archivos as $data) {

			if (is_file($data->nombre_archivo_temp)) {
				unlink($data->nombre_archivo_temp);
			}
			if (is_file($data->nombre_archivo)) {

				if (unlink($data->nombre_archivo)) {


					$total++;
					$this->db->where('nombre_archivo', $data->nombre_archivo);
					$this->db->delete('_datos_api_canon');
				} else {
				}
			} else {

				$this->db->where('nombre_archivo', $data->nombre_archivo);
				$this->db->delete('_datos_api_canon');
				// die('no');
			}
		}
		$this->db->where('code', $_REQUEST['code']);
		$this->db->delete('_lotes_canon');

		$response = array(
			'mensaje' => "Archivos borrados",
			'title' => 'LOTES ',
			'status' => 'success'
		);
		echo json_encode($response);
	}
	public function indexaciones_cuenta()
	{


		if ($this->input->is_ajax_request()) {

			$memData = $this->Electromecanica_model->get_indexaciones($_REQUEST);

			$i = $_POST['start'];

			$data = [];
			$estado = '<span class="acciones"><i class="text-success icon-check2 "></i></span>';
			foreach ($memData as $r) {


				$accionesVer = '<span class="acciones"><a title="ver archivo" href="#"  class=""><i class="icon-eye4" title="ver"></i> </a></span> ';
				$accionesMerge = '<span class=""><a title="ver archivo" href="#"  class=""><i  icon-merge " title="Consolidar"></i> </a></span> ';
				$accionesEdit = '<span class="d-none editar_lote acciones" ><a title="Editar lote" href="#"  class=""><i class=" text-warningr  icon-pencil4 " title="Editar Lote"></i> </a> </span>';
				$accionesDelete = '<span class="borrar_lote acciones" ><a title="Borrar lote" href="#"  class=""><i class=" text-danger icon-trash " title="Borrar Lote"></i> </a> </span>';
				$accionesReload = '<span class="reload-lote acciones" ><a title="Recargar datos API" href="#"  class=""><i class=" text-warningr  fa fa-download" title="Reload"></i> </a> </span>';

	
				$proveedor = $this->electromecanica->checkProveedor($r->id_proveedor);
				// $user = $this->ion_auth->user($r->user_add)->row();

				$data[] = array(
					// '<input id="" class="checkbox" type="checkbox">',
					$r->id_interno,
					$r->nro_cuenta,
					$r->nombre_secretaria,
					$r->nombre_dependencia,
					$r->descr_programa,
					$r->descr_proyecto,
					$r->nom_proveedor,
					fecha_es($r->fecha_alta, 'd/m/a', false),
					''
					// $accionesVer . $accionesReload . $accionesMerge . $accionesEdit . $accionesDelete,
				);
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => 1,
				"recordsFiltered" =>1,
				"data" => $data,
			);

			// Output to JSON format
			echo json_encode($output);
		}
	}
	public function indexaciones_dt($id = null)
	{

		$_REQUEST['search']['value'] = $_POST['table'];


		if ($this->input->is_ajax_request()) {

			$memData = $this->Electromecanica_model->getRows($_POST);
			$i = $_POST['start'];

			$data = [];
			$estado = '<span class="acciones"><i class="text-success icon-check2 "></i></span>';
			foreach ($data as $r) {
				// foreach ($memData as $r) {

				$classTextMerge = 'text-danger';

				$disableMerge = '';
				$classMerge = '';
				$archivos = $this->Electromecanica_model->getBatchFiles($r->code);

				$classTextMerge = 'text-success';
				$error = 0;


				foreach ($archivos as $dato) {


					if (!$this->Electromecanica_model->get_indexacion('_indexaciones_canon', $dato->nro_cuenta)) {

						$error++;
					}
				}
				$classMerge = 'mergelote';
				if ($error > 0) {
					$classTextMerge = 'text-warning';

					$disableMerge = ' disabled="disabled ';
					$classMerge = 'mergelote';
				}

				$consolidado = '<span class="acciones"><i class="text-danger icon-cross2 "></i></span>';
				if ($r->consolidado == 1) {
					$consolidado = '<span class="acciones"><i class="text-warnin icon-check2 "></i></span>';
					$classTextMerge = 'text-info';
					// $consolidado = 'OK';
				}

				$i++;
				$accionesVer = '<span class="acciones"><a title="ver archivo" href="/Electromecanica/Lecturas/viewBatch/' . $r->code . '"  class=""><i class="icon-eye4" title="ver"></i> </a></span> ';
				$accionesMerge = '<span data-consolidado="' . $r->consolidado . '"  data-errores="' . $error . '" data-code="' . $r->code . '" data-id_lote="' . $r->id_lote . '" class="' . $classMerge . '"><a ' . $disableMerge . ' title="ver archivo" href="#"  class=""><i class="' . $classTextMerge . ' icon-merge " title="Consolidar"></i> </a></span> ';
				$accionesEdit = '<span data-id_lote="' . $r->id_lote . '" data-code="' . $r->code . '"class="d-none editar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Editar lote" href="#"  class=""><i class=" text-warningr  icon-pencil4 " title="Editar Lote"></i> </a> </span>';
				$accionesDelete = '<span data-id_lote="' . $r->id_lote . '" data-code="' . $r->code . '"class="borrar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Borrar lote" href="#"  class=""><i class=" text-danger icon-trash " title="Borrar Lote"></i> </a> </span>';
				$accionesReload = '<span data-id_proveedor="' . $r->id_proveedor . '" data-id_lote="' . $r->id_lote . '" data-code="' . $r->code . '"class="reload-lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Recargar datos API" href="#"  class=""><i class=" text-warningr  fa fa-download" title="Reload"></i> </a> </span>';

				$proveedor = $this->electromecanica->get_proveedor($r->id_proveedor);
				// $user = $this->ion_auth->user($r->user_add)->row();

				$data[] = array(
					'<input id="' . $r->id_lote . '" class="checkbox" type="checkbox">',
					$r->nombre,
					$r->codigo,
					fecha_es($r->fecha_add, 'd/m/a', false),
					count($archivos),
					$error,
					// $estado,
					$consolidado,
					$r->last_name . ' ' . $r->first_name,
					$accionesVer . $accionesReload . $accionesMerge . $accionesEdit . $accionesDelete,
					$r->id_lote
				);
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->electromecanica->countAll(),
				"recordsFiltered" => $this->electromecanica->countFiltered($_POST),
				"data" => $data,
			);

			// Output to JSON format
			echo json_encode($output);
		}
	}
	public function lotes_dt($id = null)
	{

		if ($this->input->is_ajax_request()) {

			$memData = $this->Electromecanica_model->getRows($_POST);


			// echo $this->db->last_query();
			// die();
			$i = $_POST['start'];

			$data = [];
			$estado = '<span class="acciones"><i class="text-success icon-check2 "></i></span>';
			foreach ($memData as $r) {

				$classTextMerge = 'text-danger';

				$disableMerge = '';
				$classMerge = '';
				$archivos = $this->Electromecanica_model->getBatchFiles($r->code);

				$classTextMerge = 'text-success';
				$error = 0;


				foreach ($archivos as $dato) {

					if (!$this->Electromecanica_model->get_indexacion('_indexaciones_canon', $dato->nro_cuenta)) {

						$error++;
					}
				}
				$classMerge = 'mergelote';
				if ($error > 0) {
					$classTextMerge = 'text-warning';

					$disableMerge = ' disabled="disabled ';
					$classMerge = 'mergelote';
				}

				$consolidado = '<span class="acciones"><i class="text-danger icon-cross2 "></i></span>';
				if ($r->consolidado == 1) {
					$consolidado = '<span class="acciones"><i class="text-warnin icon-check2 "></i></span>';
					$classTextMerge = 'text-info';
					// $consolidado = 'OK';
				}

				$i++;
				$accionesVer = '<span class="acciones"><a title="ver archivo" href="/Electromecanica/Lecturas/viewBatch/' . $r->code . '"  class=""><i class="icon-eye4" title="ver"></i> </a></span> ';
				$accionesMerge = '<span data-file="---" data-consolidado="' . $r->consolidado . '"  data-errores="' . $error . '" data-code="' . $r->code . '" data-id_lote="' . $r->id_lote . '" class="' . $classMerge . ' d-none"><a ' . $disableMerge . ' title="ver archivo" href="#"  class=""><i class="' . $classTextMerge . ' icon-merge " title="Consolidar"></i> </a></span> ';
				$accionesEdit = '<span data-id_lote="' . $r->id_lote . '" data-code="' . $r->code . '"class="d-none editar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Editar lote" href="#"  class=""><i class=" text-warningr  icon-pencil4 " title="Editar Lote"></i> </a> </span>';
				$accionesDelete = '<span data-id_lote="' . $r->id_lote . '" data-code="' . $r->code . '"class="borrar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Borrar lote" href="#"  class=""><i class=" text-danger icon-trash " title="Borrar Lote"></i> </a> </span>';
				$accionesReload = '<span data-id_proveedor="' . $r->id_proveedor . '" data-id_lote="' . $r->id_lote . '" data-code="' . $r->code . '"class="reload-lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Recargar datos API" href="#"  class=""><i class=" text-warningr  fa fa-download" title="Reload"></i> </a> </span>';

				$proveedor = $this->proveedores_model->get_proveedor($r->id_proveedor);
				// $user = $this->ion_auth->user($r->user_add)->row();

				$dato_campo_api = '<i class=" text-success  fa fa-check-square-o" title="OK"></i> ';
				$dato_url_error = $this->electromecanica->get_dato_api_blanco($r->id_lote);


				if (count($dato_url_error) > 0) {
					$dato_campo_api = count($dato_url_error);
				}

				$data[] = array(
					'<input id="' . $r->id_lote . '" class="checkbox" type="checkbox">',
					$r->nombre,
					$r->codigo,
					fecha_es($r->fecha_add, 'd/m/a', false),
					count($archivos) . ' | ' . $dato_campo_api,
					$error,
					// $estado,
					$consolidado,
					$r->last_name . ' ' . $r->first_name,
					$accionesVer . $accionesReload . $accionesMerge . $accionesEdit . $accionesDelete,
					$r->id_lote
				);
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->Electromecanica_model->countAll(),
				"recordsFiltered" => $this->Electromecanica_model->countFiltered($_POST),
				"data" => $data,
			);

			// Output to JSON format
			echo json_encode($output);
		}
	}
	public function index()
	{

		$script = array(
			base_url('assets/manager/js/secciones/electromecanica/' . strtolower($this->router->fetch_class()) . '.js'),
		);

		$this->data['script'] = $script;

		$hoy = getdate();
		$code = substr(str_replace(array('=', '-'), '', $this->encrypt->encode($hoy[0])), 0, -22);
		$this->data['code'] = $code;
		$this->data['select_proveedores'] = $this->Electromecanica_model->obtener_contenido_select('_proveedores_canon', 'SELECCIONE PROVEEDOR', 'nombre', 'id ASC');

		$this->data['content'] = $this->load->view('manager/secciones/electromecanica/' . strtolower($this->router->fetch_class()), $this->data, TRUE);

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
	}

	public function viewBatch($id = null)
	{


		$_POST['search']['value'] = $id;

		$this->lote = $id;

		if ($this->input->is_ajax_request()) {

			$files = $this->Electromecanica_model->getRows($_POST);

			$i = $_POST['start'];

			foreach ($files as $r) {

				$classAccionMerge = 'mergefile';
				$archivo = explode('/', $r->nombre_archivo);
				if ($indexador = $this->Electromecanica_model->getWhere('_indexaciones_canon', 'nro_cuenta="' . $r->nro_cuenta . '"')) {
					$indexador = $indexador->id;
				} else {
					$indexador = '0';
				}

				$iconTextMerge = '';

				$disableMerge = '';

				if ($this->Electromecanica_model->get_indexacion('_indexaciones_canon', $r->nro_cuenta)) {

					$iconTextMerge = 'text-success';
				} else {
					$iconTextMerge = 'text-danger';
				}
				if ($r->consolidado == 1) {
					$iconTextMerge = 'text-defautl';
				}

				$accionesVer = '<span class="acciones"><a title="ver archivo" href="/Electromecanica/Lecturas/Views/' . $r->id . '"  class=""><i class="icon-eye4" title="ver"></i> </a></span> ';
				$accionesMerge = '<span data-file="' . $archivo[3] . '" data-consolidado="' . $r->consolidado . '"  data-indexador="' . $indexador . '" data-code="' . $r->code_lote . '" data-id_file="' . $r->id . '" class="' . $classAccionMerge . '"><a ' . $disableMerge . ' title="ver archivo" href="#"  class=""><i class="' . $iconTextMerge . ' icon-merge " title="Consolidar"></i> </a></span> ';
				$accionesReload = '<span data-id_proveedor="' . $r->id_proveedor . '"data-file="' . $r->nombre_archivo_temp . '"data-id_lote="' . $r->id . '" data-code="' . $r->code_lote . '"class="reload-lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Recargar datos API" href="#"  class=""><i class=" text-warningr  fa fa-download" title="Reload"></i> </a> </span>';
				$accionesDelete = '<span data-tabla="_datos_api" data-id_file="' . $r->id . '" class="borrar-file acciones" ><a title="Borrar file" href="#"  class=""><i class=" text-danger icon-trash " title="Borrar "></i> </a> </span>';

				$data[] = array(
					'',
					$r->nro_cuenta,
					$r->nro_medidor,
					$r->nro_factura,
					$r->periodo_del_consumo,
					$r->fecha_emision,
					// fecha_es($r->fecha_emision, 'd/m/a', false),
					// fecha_es($r->vencimiento_del_pago, 'd/m/a', false),
					$r->vencimiento_del_pago,
					$r->total_importe,
					$r->total_vencido,
					$r->consumo,
					$indexador,
					$archivo[3],
					$accionesVer . $accionesMerge . $accionesReload . $accionesDelete,
					$r->id
				);
			}


			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->electromecanica->countAll(),
				"recordsFiltered" => $this->Electromecanica_model->countFiltered($_POST),
				"data" => $data,
			);

			// Output to JSON format
			echo json_encode($output);
			exit();
		}

		if ($id) {


			$script = array(
				base_url('assets\manager\js\plugins\tables\datatables\extensions/select.min.js'),
				base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
				base_url('assets/manager/js/secciones/electromecanica/' . $this->router->fetch_method() . '.js'),
				// base_url('assets/manager/js/secciones/Electromecanica/' . $this->router->fetch_class() . '/modulo.js'),
			);


			$this->data['css_common'] = $this->css_common;
			$this->data['css'] = '';

			$this->data['script_common'] = $this->script_common;
			$this->data['script'] = $script;

			// $this->data['proveedor'] = $this->Manager_model->get_data('_proveedores', $_POST['id_proveedor']);



			$hoy = getdate();
			$code = substr(str_replace(array('=', '-'), '', $this->encrypt->encode($hoy[0])), 0, -22);

			$this->data['code'] = $code;

			$this->data['dropzone'] = $this->load->view('manager/etiquetas/dropzone', $this->data, TRUE);
			$this->data['content'] = $this->load->view('manager/secciones/electromecanica/' . $this->router->fetch_method(), $this->data, TRUE);
			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);
		}
	}

	public function leerApi()
	{
		$response = [];
		$API_KEY = 'f4b6ebe406cdb615674ae37aabc48929';

		$proveedor = $this->Electromecanica_model->getwhere('_proveedores_canon', 'id ="' . $_POST['id_proveedor'] . '"');


		if (isset($_REQUEST['id_lote'])) {

			$files = $this->electromecanica->getBatchFilesbyId($_POST['id_lote']);
			$index = 0;
			foreach ($files as $file) {
				sleep(1);
				$fileUrl = $file->nombre_archivo;
				$dato_api = json_decode($file->dato_api, true);

				$curl = curl_init();
				curl_setopt_array($curl, array(
					// CURLOPT_URL => 'https://api.mindee.net/v1/products/quickdata-mvl/edenor_canon_t_1/v1/predict',
					CURLOPT_URL => $proveedor->urlapi,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS => array('document' => new CURLFILE($fileUrl)),
					CURLOPT_HTTPHEADER => array(
						'Authorization: Token f4b6ebe406cdb615674ae37aabc48929',
						'Content-Type: multipart/form-data'
					),
				));

				$curlresponsejson = curl_exec($curl);

				if (curl_errno($curl))
					$response['status'] = 'error';
				$response['log'] = curl_error($curl);

				curl_close($curl);

				$curlresponse = json_decode($curlresponsejson);
				$data = file_get_contents("application/config/mindee/electromecanica_t" . $proveedor->tipo_proveedor . "_config.json");
				$campos = json_decode($data, true);
				$updateData = [];

				
				$updateData['dato_api'] = $curlresponsejson;

	
				foreach ($campos['selector']['features'] as $selector) {

					$elem = trim($selector['name']);

					$totalIndices = count( $curlresponse->document->inference->pages[0]->prediction->$elem->values);
			
					$valorCampo = '';
		
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$valorCampo .=' '. trim($curlresponse->document->inference->pages[0]->prediction->$elem->values[$paso]->content);
					}
					$updateData[$elem] = $valorCampo;
			
				}
		
				$this->db->where("nombre_archivo",  $fileUrl);
				$this->db->update('_datos_api_canon', $updateData);

				$index++;
			}
			$response = array(
				'mensaje' => 'Lesturas API - LOTES archivos: ' . $index,
				'title' => 'Lecturas',
				'status' => 'success',
			);
			echo json_encode($response);
		} else {

			$curl = curl_init();
			$response = [];
			curl_setopt_array($curl, array(
				// CURLOPT_URL => 'https://api.mindee.net/v1/products/quickdata-mvl/edenor_canon_t_1/v1/predict',
				CURLOPT_URL => $proveedor->urlapi,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => array('document' => new CURLFILE($_POST['file'])),
				CURLOPT_HTTPHEADER => array(
					'Authorization: Token f4b6ebe406cdb615674ae37aabc48929',
					'Content-Type: multipart/form-data'
				),
			));

			$curlresponsejson = curl_exec($curl);

			if (curl_errno($curl))
				$response['status'] = 'error';
			$response['log'] = curl_error($curl);

			curl_close($curl);

			$curlresponse = json_decode($curlresponsejson);

			// echo '<pre>';
			// var_dump( $curlresponse ); 
			// echo '</pre>';
			// die();
			$data = file_get_contents("application/config/mindee/electromecanica_t" . $proveedor->tipo_proveedor . "_config.json");
			$campos = json_decode($data, true);
			$updateData = [];

			
			$updateData['dato_api'] = $curlresponsejson;


			foreach ($campos['selector']['features'] as $selector) {

			$elem = trim($selector['name']);

				$totalIndices = count( $curlresponse->document->inference->pages[0]->prediction->$elem->values);
		
				$valorCampo = '';
	
				for ($paso = 0; $paso < $totalIndices; $paso++) {
					$valorCampo .=' '. trim($curlresponse->document->inference->pages[0]->prediction->$elem->values[$paso]->content);
				}
				$updateData[$elem] = $valorCampo;
		
			}
	
			$this->db->where("nombre_archivo_temp",  $_POST['file']);
			$this->db->update('_datos_api_canon', $updateData);
			// switch ($proveedor->id) {

			// 	case 1:
			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values);
			// 		$nro_cuenta = '';

			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$nro_cuenta .= $curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
			// 		}

			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_medidor->values);
			// 		$nro_medidor = '';

			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$nro_medidor .= $curlresponse->document->inference->pages[0]->prediction->nro_medidor->values[$paso]->content;
			// 		}

			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_factura->values);
			// 		$nro_factura = '';
			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$nro_factura .= $curlresponse->document->inference->pages[0]->prediction->nro_factura->values[$paso]->content;
			// 		}

			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->total_importe->values);
			// 		$total_importe = '';
			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$total_importe .= $curlresponse->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
			// 		}


			// 		$total_importe = str_replace(',', '.', str_replace('.', '', $total_importe));

			// 		$numero_decimal = number_format($total_importe, 2, '.', '');
			// 		$updateData = array(
			// 			'nro_medidor' => $nro_cuenta,
			// 			'nro_cuenta' => $nro_cuenta,
			// 			'nro_factura' => $nro_factura,
			// 			'total_importe' => $total_importe,
			// 			'importe_1' => $numero_decimal,
			// 			'dato_api' => json_encode($curlresponse),
			// 		);

			// 		echo '<pre>';
			// 		var_dump( $updateData ); 
			// 		echo '</pre>';
			// 		die();
			// 		$this->db->where("nombre_archivo_temp",  $_POST['file']);
			// 		$this->db->update('_datos_api_canon', $updateData);
			// 		break;
			// 	case 2:
			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values);
			// 		$nro_cuenta = '';

			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$nro_cuenta .= $curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
			// 		}

			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_medidor->values);
			// 		$nro_medidor = '';

			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$nro_medidor .= $curlresponse->document->inference->pages[0]->prediction->nro_medidor->values[$paso]->content;
			// 		}

			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_factura->values);
			// 		$nro_factura = '';
			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$nro_factura .= $curlresponse->document->inference->pages[0]->prediction->nro_factura->values[$paso]->content;
			// 		}

			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->total_importe->values);
			// 		$total_importe = '';
			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$total_importe .= $curlresponse->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
			// 		}


			// 		$total_importe = str_replace(',', '.', str_replace('.', '', $total_importe));


			// 		$numero_decimal = number_format($total_importe, 2, '.', '');
			// 		$updateData = array(
			// 			'nro_medidor' => $nro_cuenta,
			// 			'nro_cuenta' => $nro_cuenta,
			// 			'nro_factura' => $nro_factura,
			// 			'total_importe' => $total_importe,
			// 			'importe_1' => $numero_decimal,
			// 			'dato_api' => json_encode($curlresponse),
			// 		);

			// 		$this->db->where("nombre_archivo_temp",  $_POST['file']);
			// 		$this->db->update('_datos_api_canon', $updateData);

			// 		break;
			// 	case 3:

			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values);
			// 		$nro_cuenta = '';

			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$nro_cuenta .= $curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
			// 		}

			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_medidor->values);
			// 		$nro_medidor = '';

			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$nro_medidor .= $curlresponse->document->inference->pages[0]->prediction->nro_medidor->values[$paso]->content;
			// 		}

			// 		$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_factura->values);
			// 		$nro_factura = '';
			// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 			$nro_factura .= $curlresponse->document->inference->pages[0]->prediction->nro_factura->values[$paso]->content;
			// 		}

			// 		$total_importe = 'N/A';
			// 		$numero_decimal = '00.00';

			// 		if ($curlresponse->document->inference->pages[0]->prediction->total_importe->confidence != 0) {

			// 			$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->total_importe->values);
			// 			$total_importe = '';
			// 			for ($paso = 0; $paso < $totalIndices; $paso++) {
			// 				$total_importe .= $curlresponse->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
			// 			}
			// 			$total_importe = str_replace(',', '.', str_replace('.', '', $total_importe));
			// 			$numero_decimal = number_format($total_importe, 2, '.', '');
			// 		}

			// 		$updateData = array(
			// 			'nro_medidor' => $nro_cuenta,
			// 			'nro_cuenta' => $nro_cuenta,
			// 			'nro_factura' => $nro_factura,
			// 			'total_importe' => $total_importe,
			// 			'importe_1' => $numero_decimal,
			// 			'dato_api' => json_encode($curlresponse),
			// 		);

			// 		$this->db->where("nombre_archivo_temp",  $_POST['file']);
			// 		$this->db->update('_datos_api_canon', $updateData);

			// 		break;
			// }
			$response = array(
				'mensaje' => $_POST['file'],
				'title' => 'Lecturas',
				'status' => 'success',
			);
			echo json_encode($response);
			exit();
		}
	}

	public function leerApi_ORIGINAL()
	{
		$API_KEY = 'f4b6ebe406cdb615674ae37aabc48929';

		$proveedor = $this->Electromecanica_model->getwhere('_proveedores_canon', 'id ="' . $_POST['id_proveedor'] . '"');


		if (isset($_REQUEST['id_lote'])) {

			$files = $this->electromecanica->getBatchFilesbyId($_POST['id_lote']);
			$index = 0;
			foreach ($files as $file) {
				sleep(1);
				$fileUrl = $file->nombre_archivo_temp;

				$curl = curl_init();
				$response = [];
				curl_setopt_array($curl, array(
					// CURLOPT_URL => 'https://api.mindee.net/v1/products/quickdata-mvl/edenor_canon_t_1/v1/predict',
					CURLOPT_URL => $proveedor->urlapi,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS => array('document' => new CURLFILE($fileUrl)),
					CURLOPT_HTTPHEADER => array(
						'Authorization: Token f4b6ebe406cdb615674ae37aabc48929',
						'Content-Type: multipart/form-data'
					),
				));

				$curlresponse = curl_exec($curl);

				if (curl_errno($curl))
					$response['status'] = 'error';
				$response['log'] = curl_error($curl);

				curl_close($curl);

				$curlresponse = json_decode($curlresponse);
				$data = file_get_contents("application/config/mindee/electromecanica_t" . $proveedor->tipo_proveedor . "_config.json");
				$campos = json_decode($data, true);

				$updateData = [];
				foreach ($campos['selector']['features'] as $campo) {

					echo '<pre>';
					var_dump($curlresponse->document->inference->pages[0]->prediction->$campo['name']->values);
					echo '</pre>';
					die();

					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->$campo['name']->values);
					$valorCampo = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$valorCampo .= $curlresponse->document->inference->pages[0]->prediction->$campo['name']->values[$paso]->content;
					}

					array_push($updateData, [$campo['name'] => $valorCampo]);
				}

				// echo '<pre>updatedata';
				// var_dump( $updateData ); 
				// echo '</pre>';
				die();

				switch ($proveedor->id) {

					case 1:
						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values);
						$nro_cuenta = '';

						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$nro_cuenta .= $curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
						}

						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_medidor->values);
						$nro_medidor = '';

						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$nro_medidor .= $curlresponse->document->inference->pages[0]->prediction->nro_medidor->values[$paso]->content;
						}

						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_factura->values);
						$nro_factura = '';
						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$nro_factura .= $curlresponse->document->inference->pages[0]->prediction->nro_factura->values[$paso]->content;
						}

						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->total_importe->values);
						$total_importe = '';
						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$total_importe .= $curlresponse->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
						}


						$total_importe = str_replace(',', '.', str_replace('.', '', $total_importe));

						$numero_decimal = number_format($total_importe, 2, '.', '');
						$updateData = array(
							'nro_medidor' => $nro_cuenta,
							'nro_cuenta' => $nro_cuenta,
							'nro_factura' => $nro_factura,
							'total_importe' => $total_importe,
							'importe_1' => $numero_decimal,
							'dato_api' => json_encode($curlresponse),
						);

						$this->db->where("nombre_archivo_temp",  $fileUrl);
						$this->db->update('_datos_api_canon', $updateData);
						break;
					case 2:
						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values);
						$nro_cuenta = '';

						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$nro_cuenta .= $curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
						}

						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_medidor->values);
						$nro_medidor = '';

						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$nro_medidor .= $curlresponse->document->inference->pages[0]->prediction->nro_medidor->values[$paso]->content;
						}

						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_factura->values);
						$nro_factura = '';
						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$nro_factura .= $curlresponse->document->inference->pages[0]->prediction->nro_factura->values[$paso]->content;
						}

						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->total_importe->values);
						$total_importe = '';
						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$total_importe .= $curlresponse->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
						}


						$total_importe = str_replace(',', '.', str_replace('.', '', $total_importe));


						$numero_decimal = number_format($total_importe, 2, '.', '');
						$updateData = array(
							'nro_medidor' => $nro_cuenta,
							'nro_cuenta' => $nro_cuenta,
							'nro_factura' => $nro_factura,
							'total_importe' => $total_importe,
							'importe_1' => $numero_decimal,
							'dato_api' => json_encode($curlresponse),
						);

						$this->db->where("nombre_archivo_temp",  $fileUrl);
						$this->db->update('_datos_api_canon', $updateData);

						break;
					case 3:

						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values);
						$nro_cuenta = '';

						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$nro_cuenta .= $curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
						}

						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_medidor->values);
						$nro_medidor = '';

						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$nro_medidor .= $curlresponse->document->inference->pages[0]->prediction->nro_medidor->values[$paso]->content;
						}

						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_factura->values);
						$nro_factura = '';
						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$nro_factura .= $curlresponse->document->inference->pages[0]->prediction->nro_factura->values[$paso]->content;
						}

						$total_importe = 'N/A';
						$numero_decimal = '00.00';

						if ($curlresponse->document->inference->pages[0]->prediction->total_importe->confidence != 0) {

							$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->total_importe->values);
							$total_importe = '';
							for ($paso = 0; $paso < $totalIndices; $paso++) {
								$total_importe .= $curlresponse->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
							}
							$total_importe = str_replace(',', '.', str_replace('.', '', $total_importe));
							$numero_decimal = number_format($total_importe, 2, '.', '');
						}

						$updateData = array(
							'nro_medidor' => $nro_cuenta,
							'nro_cuenta' => $nro_cuenta,
							'nro_factura' => $nro_factura,
							'total_importe' => $total_importe,
							'importe_1' => $numero_decimal,
							'dato_api' => json_encode($curlresponse),
						);

						$this->db->where("nombre_archivo_temp",  $fileUrl);
						$this->db->update('_datos_api_canon', $updateData);

						break;
				}

				$index++;

				// echo json_encode($response);
				// exit();


			}
			$response = array(
				'mensaje' => 'Lesturas API - LOTES archivos: ' . $index,
				'title' => 'Lecturas',
				'status' => 'success',
			);
			echo json_encode($response);
		} else {

			$curl = curl_init();
			$response = [];
			curl_setopt_array($curl, array(
				// CURLOPT_URL => 'https://api.mindee.net/v1/products/quickdata-mvl/edenor_canon_t_1/v1/predict',
				CURLOPT_URL => $proveedor->urlapi,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => array('document' => new CURLFILE($_POST['file'])),
				CURLOPT_HTTPHEADER => array(
					'Authorization: Token f4b6ebe406cdb615674ae37aabc48929',
					'Content-Type: multipart/form-data'
				),
			));

			$curlresponse = curl_exec($curl);

			if (curl_errno($curl))
				$response['status'] = 'error';
			$response['log'] = curl_error($curl);

			curl_close($curl);

			$curlresponse = json_decode($curlresponse);

			switch ($proveedor->id) {

				case 1:
					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values);
					$nro_cuenta = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_cuenta .= $curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
					}

					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_medidor->values);
					$nro_medidor = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_medidor .= $curlresponse->document->inference->pages[0]->prediction->nro_medidor->values[$paso]->content;
					}

					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_factura->values);
					$nro_factura = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_factura .= $curlresponse->document->inference->pages[0]->prediction->nro_factura->values[$paso]->content;
					}

					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->total_importe->values);
					$total_importe = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$total_importe .= $curlresponse->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
					}


					$total_importe = str_replace(',', '.', str_replace('.', '', $total_importe));

					$numero_decimal = number_format($total_importe, 2, '.', '');
					$updateData = array(
						'nro_medidor' => $nro_cuenta,
						'nro_cuenta' => $nro_cuenta,
						'nro_factura' => $nro_factura,
						'total_importe' => $total_importe,
						'importe_1' => $numero_decimal,
						'dato_api' => json_encode($curlresponse),
					);

					$this->db->where("nombre_archivo_temp",  $_POST['file']);
					$this->db->update('_datos_api_canon', $updateData);
					break;
				case 2:
					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values);
					$nro_cuenta = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_cuenta .= $curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
					}

					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_medidor->values);
					$nro_medidor = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_medidor .= $curlresponse->document->inference->pages[0]->prediction->nro_medidor->values[$paso]->content;
					}

					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_factura->values);
					$nro_factura = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_factura .= $curlresponse->document->inference->pages[0]->prediction->nro_factura->values[$paso]->content;
					}

					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->total_importe->values);
					$total_importe = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$total_importe .= $curlresponse->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
					}


					$total_importe = str_replace(',', '.', str_replace('.', '', $total_importe));


					$numero_decimal = number_format($total_importe, 2, '.', '');
					$updateData = array(
						'nro_medidor' => $nro_cuenta,
						'nro_cuenta' => $nro_cuenta,
						'nro_factura' => $nro_factura,
						'total_importe' => $total_importe,
						'importe_1' => $numero_decimal,
						'dato_api' => json_encode($curlresponse),
					);

					$this->db->where("nombre_archivo_temp",  $_POST['file']);
					$this->db->update('_datos_api_canon', $updateData);

					break;
				case 3:

					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values);
					$nro_cuenta = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_cuenta .= $curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
					}

					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_medidor->values);
					$nro_medidor = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_medidor .= $curlresponse->document->inference->pages[0]->prediction->nro_medidor->values[$paso]->content;
					}

					$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_factura->values);
					$nro_factura = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_factura .= $curlresponse->document->inference->pages[0]->prediction->nro_factura->values[$paso]->content;
					}

					$total_importe = 'N/A';
					$numero_decimal = '00.00';

					if ($curlresponse->document->inference->pages[0]->prediction->total_importe->confidence != 0) {

						$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->total_importe->values);
						$total_importe = '';
						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$total_importe .= $curlresponse->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
						}
						$total_importe = str_replace(',', '.', str_replace('.', '', $total_importe));
						$numero_decimal = number_format($total_importe, 2, '.', '');
					}

					$updateData = array(
						'nro_medidor' => $nro_cuenta,
						'nro_cuenta' => $nro_cuenta,
						'nro_factura' => $nro_factura,
						'total_importe' => $total_importe,
						'importe_1' => $numero_decimal,
						'dato_api' => json_encode($curlresponse),
					);

					$this->db->where("nombre_archivo_temp",  $_POST['file']);
					$this->db->update('_datos_api_canon', $updateData);

					break;
			}
			$response = array(
				'mensaje' => $_POST['file'],
				'title' => 'LOTES521',
				'status' => 'success',
			);
			echo json_encode($response);
			exit();
		}
	}
	public function leerApiLOTES()
	{


		echo '<pre>';
		var_dump($_REQUEST);
		echo '</pre>';
		die();
		$API_KEY = 'f4b6ebe406cdb615674ae37aabc48929';

		// $file = str_replace(base_url(), '', $_POST['file']);

		$proveedor = $this->Electromecanica_model->getwhere('_proveedores', 'id ="' . $_POST['id_proveedor'] . '"');
		$files = $this->Electromecanica_model->getalldata('_datos_api_canon', 'id_lote="' . $_POST['id_lote'] . '"');

		$curl = curl_init();
		$a = 0;
		foreach ($files as $file) {

			sleep(2);

			$response = [];
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://api.mindee.net/v1/products/quickdata-mvl/edenor_canon_t_1/v1/predict',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => array('document' => new CURLFILE(base_url($file->nombre_archivo_temp))),
				CURLOPT_HTTPHEADER => array(
					'Authorization: Token f4b6ebe406cdb615674ae37aabc48929',
					'Content-Type: multipart/form-data'
				),
			));

			$curlresponse = curl_exec($curl);

			if (curl_errno($curl))
				$response['status'] = 'error';
			$response['log'] = curl_error($curl);

			curl_close($curl);

			$curlresponse = json_decode($curlresponse);

			$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values);
			$nro_cuenta = '';

			for ($paso = 0; $paso < $totalIndices; $paso++) {
				$nro_cuenta .= $curlresponse->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
			}

			$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_medidor->values);
			$nro_medidor = '';

			for ($paso = 0; $paso < $totalIndices; $paso++) {
				$nro_medidor .= $curlresponse->document->inference->pages[0]->prediction->nro_medidor->values[$paso]->content;
			}

			$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->nro_factura->values);
			$nro_factura = '';
			for ($paso = 0; $paso < $totalIndices; $paso++) {
				$nro_factura .= $curlresponse->document->inference->pages[0]->prediction->nro_factura->values[$paso]->content;
			}

			$totalIndices = count($curlresponse->document->inference->pages[0]->prediction->total_importe->values);
			$total_importe = '';
			for ($paso = 0; $paso < $totalIndices; $paso++) {
				$total_importe .= $curlresponse->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
			}

			$total_importe = str_replace(',', '.', str_replace('.', '', $total_importe));
			$numero_decimal = number_format($total_importe, 2, '.', '');
			$updateData = array(
				'nro_medidor' => $nro_cuenta,
				'nro_cuenta' => $nro_cuenta,
				'nro_factura' => $nro_factura,
				'total_importe' => $total_importe,
				'importe_1' => $numero_decimal,
				'dato_api' => json_encode($curlresponse),
			);

			$this->db->where("nombre_archivo_temp",  $file->nombre_archivo_temp);
			$this->db->update('_datos_api_canon', $updateData);

			$a++;
			$response['cantidad'] = $a;
			$response['data'] = $curlresponse;
			echo json_encode($response);
			exit();
		}
	}

	public function upload($id = null)
	{

		if ($this->input->is_ajax_request()) {

			if (!empty($_FILES['file']['name']) && !empty($_POST['id_proveedor'])) {

				$arc = explode('.', $_FILES['file']['name']);
				$nuevoNOmbre = limpiar_caracteres($arc[0]);
				$nombre_archivodb = $nuevoNOmbre . '.' . $arc[1];

				$proveedor = $this->Electromecanica_model->get_data('_proveedores_canon', $_POST['id_proveedor']);

				$db_tabla = "_t" . $proveedor->tipo_proveedor;

				$nombre_fichero = 'uploader/canon/' . $proveedor->tipo_proveedor . '/' . strtolower($proveedor->codigo);

				if (!file_exists(strtolower($nombre_fichero))) {
					mkdir($nombre_fichero, 0777, true);
				}

				$data = array();
				// Set preference 
				$uploadPath = $nombre_fichero;
				$config["remove_spaces"] = TRUE;
				$config["overwrite"] = TRUE;
				$config['upload_path'] = $uploadPath;
				//    $config['allowed_types'] = 'jpg|jpeg|png|gif'; 
				$config['allowed_types'] = '*';
				$config['max_size'] = '10000'; // max_size in kb 
				$config['file_name'] = $nombre_archivodb;

				$this->load->library('upload', $config);

				$response = array(
					'mensaje' => 'inico',
					'title' => 'LOTES 227',
					'status' => 'error',
				);

				if ($this->upload->do_upload('file')) {

					$nombre_archivo_temp = $nuevoNOmbre . '_splitter.' . $arc[1];

					$destino = $nombre_fichero  . "/" . $nombre_archivo_temp;

					$this->load->library('pdf_lib');

					$pdf = $this->pdf_lib->test($this->upload->data('full_path'), $destino);

					if (!file_exists(strtolower($nombre_fichero . '/' . $nuevoNOmbre))) {
					}
					try {
						//actrualizo tabla lotes cantidad de archivos
						if (isset($_REQUEST['id_lote'])) {

							$lote = $this->Electromecanica_model->getwhere('_lotes_canon', 'id=' . $_REQUEST['id_lote']);
							$dataLote = array(
								'cant' => $lote->cant + $_REQUEST['cant']
							);
							$this->db->where('id', $_REQUEST['id_lote']);
							$this->db->update('_lotes_canon', $dataLote);
						}
					} catch (Exception $e) {

						// this will not catch DB related errors. But it will include them, because this is more general. 
						echo $e->getMessage();
						$response = array(
							'mensaje' => $e->getMessage(),
							'title' => 'Consulta API 218',
							'status' => 'error',
						);
						echo json_encode($response);
						exit();
					}

					$uploadData = $this->upload->data();

					$filename = $uploadData['file_name'];
					$fullpath = $uploadData['full_path'];

					$nombre_archivodb = $config['upload_path'] . '/' . $config['file_name'];

					$lote = $this->Electromecanica_model->crearLote();

					$texto = ' ';
					// $saveData = array(
					// 	'id_lote'.$db_tabla => $lote[0]->id,
					// 	'code_lote'.$db_tabla => $lote[0]->code,
					// 	'id_proveedor'.$db_tabla => $proveedor->id,
					// 	'tipo_proveedor'.$db_tabla => $proveedor->tipo_proveedor,
					// 	'nombre_proveedor'.$db_tabla => $proveedor->nombre,
					// 	'nombre_archivo'.$db_tabla => $nombre_archivodb,
					// 	'nombre_archivo_temp'.$db_tabla => $destino,
					// );
					$saveData = array(
						'id_lote' => $lote[0]->id,
						'code_lote' => $lote[0]->code,
						'id_proveedor' => $proveedor->id,
						'tipo_proveedor' => $proveedor->tipo_proveedor,
						'nombre_proveedor' => $proveedor->nombre,
						'nombre_archivo' => $nombre_archivodb,
						'nombre_archivo_temp' => $destino,
					);

					// if ($this->Electromecanica_model->grabar_datos("_t".$proveedor->tipo_proveedor, $saveData)) {
					if ($this->Electromecanica_model->grabar_datos("_datos_api_canon", $saveData)) {


						$response = array(
							'mensaje' => 'Archivo: ' . $filename . ' Lote: ' . $lote[0]->code,
							'title' => 'Grabar Archivos',
							'status' => 'success',
							// 'file' => $filename,
							'file' => $filename,
							'path' => $fullpath,
							'pathw' => $destino,
						);
						echo json_encode($response);
						exit();
					} else {
						$response = array(
							'mensaje' => 'Archivo: ' . $filename . ' Lote: ' . $lote[0]->code,
							'title' => 'Grabar Archivos',
							'status' => 'error',
						);
						echo json_encode($response);
						exit();
					}
				} else {
					// echo 'error ->' . $this->upload->display_errors();
					$data['response'] = 'failed';
					$response = array(
						'mensaje' => 'Archivo: ' . $this->upload->display_errors() . '<br>' . $nombre_archivodb . ' Lote: ' . $_POST['code_lote'],
						'title' => 'Grabar Archivos',
						'status' => 'error',
					);

					echo json_encode($response);
					exit();
				}
			} else {
				$response = array(
					'mensaje' => 'Archivo: vacio',
					'title' => 'Grabar ',
					'status' => 'error',
				);

				echo json_encode($response);
				exit();
			}
		}

		// ACA LLEGA NORMAL

		if (!empty($_POST['id_proveedor'])) {


			$script = array(
				base_url('assets/manager/js/plugins/tables/datatables/datatables.min.js'),
				base_url('assets/manager/js/plugins/dropzone.min.js'),
				base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
				base_url('assets/manager/js/secciones/' . $this->router->fetch_class()  . '.js'),
			);


			$this->data['css_common'] = $this->css_common;
			$this->data['css'] = '';

			$this->data['script_common'] = $this->script_common;
			$this->data['script'] = $script;

			$this->data['proveedor'] = $this->Manager_model->get_data('_proveedores_canon', $_POST['id_proveedor']);


			$this->form_validation->set_rules('id_proveedor', 'proveedor', 'callback_url_check');


			if ($this->form_validation->run() == FALSE) {
				redirect('Electromecanica/Lecturas');
			}

			$hoy = getdate();

			$code = substr(str_replace(array('=', '-', '/s'), '', $this->encrypt->encode($hoy[0])), 0, 6);

			$this->data['code'] = urlencode($code);

			$this->data['dropzone'] = $this->load->view('manager/etiquetas/dropzone', $this->data, TRUE);
			$this->data['content'] = $this->load->view('manager/secciones/lotes/' . $this->router->fetch_method(), $this->data, TRUE);
			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);
		} else {
			die('aca 216');
		}
	}
}

/* End of file Lecturas.php and path \application\controllers\electromecanica\Lecturas.php */
