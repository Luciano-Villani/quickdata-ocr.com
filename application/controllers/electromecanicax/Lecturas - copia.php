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

	
	
	
	
	
	
	
	
	
	public function leerApi_carlos()
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
			
			$response = array(
				'mensaje' => $_POST['file'],
				'title' => 'Lecturas',
				'status' => 'success',
			);
			echo json_encode($response);
			exit();
		}
	}

	public function leerApi()
{
    $file = str_replace(base_url(), '', $_POST['file']);
    $proveedor = $this->Electromecanica_model->getwhere('_proveedores_canon', 'id="' . $_POST['id_proveedor'] . '"');

    $request = array(
        'full_path' => $_POST['file']
    );

    $procesar_por = isset($proveedor->procesar_por) ? $proveedor->procesar_por : 'local';

    $dataApi = apiRest($request, $proveedor->urlapi, $procesar_por);

    $updateData = array(
        'dato_api' => json_encode($dataApi),
    );

    $this->db->where("nombre_archivo_temp", $_POST['file']);
    $this->db->update('_datos_api_canon', $updateData);

    $this->getDato($_POST['file'], $proveedor->id);

    if (is_file($_POST['file'])) {
        unlink($_POST['file']);
    }

    $response = array(
        'mensaje' => $_POST['file'],
        'title' => 'LOTES521',
        'status' => 'success',
    );
    echo json_encode($response);
    exit();
}

public function getDato($file = '', $id_proveedor = '')
	{
		$dataACT = $this->Electromecanica_model->get_alldata('_consolidados_canon');
		//var_dump($id_proveedor);
		

		foreach ($dataACT as $reg) {


		}


		if ($this->input->is_ajax_request()) {
			$file = $_REQUEST['file'];
			$id_proveedor = $_REQUEST['id_proveedor'];
		}

		$query = "SELECT id, dato_api FROM _datos_api_canon WHERE nombre_archivo_temp = '" . $file . "'";
		$valor = $file;
		$filename = $file;


		$valor_escapado = $valor;

		$resultado = $this->db->query($query, array($valor_escapado));
		$mires = $resultado->result();


		if ($resultado) {

			$a = json_decode($mires[0]->dato_api);
		

			switch ($id_proveedor) {
				case 1: //3857 EDENOR T1

					$importe = trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content);

					// Elimina el separador de miles y ajusta el separador decimal
					$importe = str_replace(',', '.', str_replace('.', '', $importe));

					// Convierte la cadena en un valor numérico (float)
					$importe_numerico = floatval($importe);

					// Aplica number_format para formatear a 2 decimales
					$numero_decimal = number_format($importe_numerico, 2, '.', '');

					$totalIndices = count($a->document->inference->pages[0]->prediction->periodo_del_consumo->values);
					$periodo_del_consumo = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[$paso]->content;
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->tipo_de_tarifa->values);
					$tipo_de_tarifa = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$tipo_de_tarifa .= ' ' . $a->document->inference->pages[0]->prediction->tipo_de_tarifa->values[$paso]->content;
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->domicilio_de_consumo->values);
					$domicilio_de_consumo = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
  						$domicilio_de_consumo .= ' ' . trim($a->document->inference->pages[0]->prediction->domicilio_de_consumo->values[$paso]->content);
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->dias_comprendidos->values);
					$dias_comprendidos = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$dias_comprendidos .= ' ' . trim($a->document->inference->pages[0]->prediction->dias_comprendidos->values[$paso]->content);
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->nombre_cliente->values);
					$nombre_cliente = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nombre_cliente .= ' ' . trim($a->document->inference->pages[0]->prediction->nombre_cliente->values[$paso]->content);
					}


					// Verificación para nro_factura
					if ($a->document->inference->pages[0]->prediction->nro_factura->values) {
						$nro_factura = $a->document->inference->pages[0]->prediction->nro_factura->values[0]->content;
					} else {
						$nro_factura = 'N/A';
					}

					// Verificación para fecha_emision
					if ($a->document->inference->pages[0]->prediction->fecha_emision->values) {
						$fecha_emision = $a->document->inference->pages[0]->prediction->fecha_emision->values[0]->content;
					} else {
						$fecha_emision = 'N/A';
					}

					// Verificación para nro_cuenta
					if ($a->document->inference->pages[0]->prediction->nro_cuenta->values) {
						$nro_cuenta = $a->document->inference->pages[0]->prediction->nro_cuenta->values[0]->content;
					} else {
						$nro_cuenta = '0.00';
					}

					
					// Verificación para vencimiento_del_pago
					if ($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values) {
						$vencimiento_del_pago = $a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[0]->content;
					} else {
						$vencimiento_del_pago = 'N/A';
					}

					
					// Verificación para proximo_vencimiento
					//if ($a->document->inference->pages[0]->prediction->proximo_vencimiento->values) {
					//	$proximo_vencimiento = $a->document->inference->pages[0]->prediction->proximo_vencimiento->values[0]->content;
					//} else {
					//	$proximo_vencimiento = 'N/A';
					//}

					

					// Verificación para nro_medidor
					if ($a->document->inference->pages[0]->prediction->nro_medidor->values) {
						$nro_medidor = $a->document->inference->pages[0]->prediction->nro_medidor->values[0]->content;
					} else {
						$nro_medidor = '0.00';
					}

					// Verificación para periodo_del_consumo
					if ($a->document->inference->pages[0]->prediction->periodo_del_consumo->values) {
						$totalIndices = count($a->document->inference->pages[0]->prediction->periodo_del_consumo->values);
						$periodo_del_consumo = '';
						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[$paso]->content;
						}
					} else {
						$periodo_del_consumo = 'N/A';
					}

					// Verificación para consumo
					if ($a->document->inference->pages[0]->prediction->consumo->values) {
						$consumo = $a->document->inference->pages[0]->prediction->consumo->values[0]->content;
					} else {
						$consumo = '0.00';
					}

					// Verificación para dias_de_consumo
					if ($a->document->inference->pages[0]->prediction->dias_de_consumo->values) {
						$dias_de_consumo = $a->document->inference->pages[0]->prediction->dias_de_consumo->values[0]->content;
					} else {
						$dias_de_consumo = 'N/A';
					}

					// Verificación para dias_comprendidos
					if ($a->document->inference->pages[0]->prediction->dias_comprendidos->values) {
						$totalIndices = count($a->document->inference->pages[0]->prediction->dias_comprendidos->values);
						$dias_comprendidos = '';
						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$dias_comprendidos .= ' ' . $a->document->inference->pages[0]->prediction->dias_comprendidos->values[$paso]->content;
						}
					} else {
						$dias_comprendidos = 'N/A';
					}

					// Verificación para consumo_dias_comprendidos
					if ($a->document->inference->pages[0]->prediction->consumo_dias_comprendidos->values) {
						$consumo_dias_comprendidos = $a->document->inference->pages[0]->prediction->consumo_dias_comprendidos->values[0]->content;
					} else {
						$consumo_dias_comprendidos = '0.00';
					}

					// Verificación para cargo_variable_hasta
					if ($a->document->inference->pages[0]->prediction->cargo_variable_hasta->values) {
						$cargo_variable_hasta = $a->document->inference->pages[0]->prediction->cargo_variable_hasta->values[0]->content;
					} else {
						$cargo_variable_hasta = '0.00';
					}

					// Verificación para cargo_fijo
					if ($a->document->inference->pages[0]->prediction->cargo_fijo->values) {
						$cargo_fijo = $a->document->inference->pages[0]->prediction->cargo_fijo->values[0]->content;
					} else {
						$cargo_fijo = '0.00';
					}

					// Verificación para monto_car_var_hasta
					if ($a->document->inference->pages[0]->prediction->monto_car_var_hasta->values) {
						$monto_car_var_hasta = $a->document->inference->pages[0]->prediction->monto_car_var_hasta->values[0]->content;
					} else {
						$monto_car_var_hasta = '0.00';
					}

					// Verificación para moto_var_mayor
					if ($a->document->inference->pages[0]->prediction->moto_var_mayor->values) {
						$moto_var_mayor = $a->document->inference->pages[0]->prediction->moto_var_mayor->values[0]->content;
					} else {
						$moto_var_mayor = '0.00';
					}

					// Verificación para otros_conseptos
					if ($a->document->inference->pages[0]->prediction->otros_conseptos->values) {
						$otros_conseptos = $a->document->inference->pages[0]->prediction->otros_conseptos->values[0]->content;
					} else {
						$otros_conseptos = '0.00';
					}

					// Verificación para conceptos_electricos
					//if ($a->document->inference->pages[0]->prediction->conseptos_electricos->values) {
				//		$conceptos_electricos = $a->document->inference->pages[0]->prediction->conseptos_electricos->values[0]->content;
				//	} else {
				//		$conceptos_electricos = '0.00';
				//	}

					// Verificación para impuestos
					if ($a->document->inference->pages[0]->prediction->impuestos->values) {
						$impuestos = $a->document->inference->pages[0]->prediction->impuestos->values[0]->content;
					} else {
						$impuestos = '0.00';
					}

					// Verificación para subsidio
					if ($a->document->inference->pages[0]->prediction->subsidio->values) {
						$subsidio = $a->document->inference->pages[0]->prediction->subsidio->values[0]->content;
					} else {
						$subsidio = '0.00';
					}

					// Verificación para domicilio_de_consumo
					if ($a->document->inference->pages[0]->prediction->domicilio_de_consumo->values) {
						$totalIndices = count($a->document->inference->pages[0]->prediction->domicilio_de_consumo->values);
						$domicilio_de_consumo = '';
						for ($paso = 0; $paso < $totalIndices; $paso++) {
							$domicilio_de_consumo .= ' ' . trim($a->document->inference->pages[0]->prediction->domicilio_de_consumo->values[$paso]->content);
						}
					} else {
						$domicilio_de_consumo = 'N/A';
					}

					// Verificación para energia_inyectada
					if ($a->document->inference->pages[0]->prediction->energia_inyectada->values) {
						$energia_inyectada = $a->document->inference->pages[0]->prediction->energia_inyectada->values[0]->content;
					} else {
						$energia_inyectada = '0.00';
					}



					$dataUpdate = array(

											
						'nro_cuenta' => isset($nro_cuenta) ? trim($nro_cuenta) : 'N/A',
						'tipo_de_tarifa' => isset($tipo_de_tarifa) ? trim($tipo_de_tarifa) : 'N/A',
						'nombre_cliente' => isset($nombre_cliente) ? trim($nombre_cliente) : 'N/A',
						'nro_medidor' => isset($nro_medidor) ? trim($nro_medidor) : 'N/A',
						'nro_factura' => isset($nro_factura) ? trim($nro_factura) : 'N/A',
						'periodo_del_consumo' => isset($periodo_del_consumo) ? trim($periodo_del_consumo) : 'N/A',
						'fecha_emision' => isset($fecha_emision) ? trim($fecha_emision) : 'N/A',
						'vencimiento_del_pago' => isset($vencimiento_del_pago) ? trim($vencimiento_del_pago) : 'N/A',
						'proximo_vencimiento' => 'N/A',
						'total_importe' => isset($numero_decimal) ? trim($numero_decimal) : 'N/A',
						'importe_1' => isset($numero_decimal) ? trim($numero_decimal) : 'N/A', // 'importe_1' igual que 'total_importe'
						'consumo' => isset($consumo) ? trim($consumo) : 'N/A',
						'total_vencido' => 'S/D', // Valor predeterminado fijo por ahora
						'domicilio_de_consumo' => isset($domicilio_de_consumo) ? trim($domicilio_de_consumo) : 'N/A',
						'dias_comprendidos' => isset($dias_comprendidos) ? trim($dias_comprendidos) : 'N/A',
						'dias_de_consumo' => isset($dias_de_consumo) ? trim($dias_de_consumo) : 'N/A',
						'consumo_dias_comprendidos' => isset($consumo_dias_comprendidos) ? trim($consumo_dias_comprendidos) : 'N/A',
						'nombre_proveedor' => 'EDENOR CANON - T1', 
						'cargo_variable_hasta' => isset($cargo_variable_hasta) ? trim($cargo_variable_hasta) : 'N/A',
						'cargo_fijo' => isset($cargo_fijo) ? trim($cargo_fijo) : 'N/A',
						'monto_car_var_hasta' => isset($monto_car_var_hasta) ? trim($monto_car_var_hasta) : 'N/A',
						'moto_var_mayor' => isset($moto_var_mayor) ? trim($moto_var_mayor) : 'N/A',
						'otros_conseptos' => isset($otros_conseptos) ? trim($otros_conseptos) : 'N/A',
						'conceptos_electricos' => isset($conceptos_electricos) ? trim($conceptos_electricos) : 'N/A',
						'impuestos' => isset($impuestos) ? trim($impuestos) : 'N/A',
						'subsidio' => isset($subsidio) ? trim($subsidio) : 'N/A',
						'energia_inyectada' => isset($energia_inyectada) ? trim($energia_inyectada) : 'N/A',
						'p_contratada' => 'N/A',
						'p_registrada' => 'N/A',
						'p_excedida' => 'N/A',
						'pot_punta' => 'N/A',
						'pot_fuera_punta_cons' => 'N/A',
						'ener_punta_act' => 'N/A',
						'ener_punta_cons' => 'N/A',
						'ener_resto_act' => 'N/A',
						'ener_resto_cons' => 'N/A',
						'ener_valle_act' => 'N/A',
						'ener_valle_cons' => 'N/A',
						'ener_reac_act' => 'N/A',
						'ener_reac_cons' => 'N/A',
						'e_reactiva' => 'N/A',
						'tgfi' => 'N/A',
						'cargo_pot_contratada' => 'N/A',
						'cargo_pot_ad' => 'N/A',
						'cargo_pot_excd' => 'N/A',
						'recargo_tgfi' => 'N/A',
						'consumo_pico_ant' => 'N/A',
						'consumo_pico_vig' => 'N/A',
						'cargo_pico' => 'N/A',
						'consumo_resto_ant' => 'N/A',
						'consumo_resto_vig' => 'N/A',
						'cargo_resto' => 'N/A',
						'consumo_valle_ant' => 'N/A',
						'consumo_valle_vig' => 'N/A',
						'cargo_valle' => 'N/A',
						'e_actual' => 'N/A',
						'cargo_contr' => 'N/A',
						'cargo_adq' => 'N/A',
						'cargo_exc' => 'N/A',
						'cargo_var' => 'N/A',
						'bimestre' => 'N/A',
						'ener_generada' => 'N/A',
						'conseptos_electricos' => 'N/A',
						'cosfi' => 'N/A',

						


					);
					
					
					break;

				case 2: //NATURGY 4399


					$importe = trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content);
			
					$importe = str_replace(',', '.', str_replace('.', '', $importe));
		
				
					$numero_decimal = number_format($importe, 2, '.', '');



					
					//periodo de consumo
					$totalIndices = count($a->document->inference->pages[0]->prediction->periodo_del_consumo->values);
					$periodo_del_consumo = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[$paso]->content;
					}
					$totalIndices = count($a->document->inference->pages[0]->prediction->domicilio_de_consumo->values);
					$domicilio_de_consumo = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
  						$domicilio_de_consumo .= ' ' . trim($a->document->inference->pages[0]->prediction->domicilio_de_consumo->values[$paso]->content);
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->tipo_de_tarifa->values);
					$tipo_de_tarifa = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$tipo_de_tarifa .= ' ' . $a->document->inference->pages[0]->prediction->tipo_de_tarifa->values[$paso]->content;
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->nombre_cliente->values);
					$nombre_cliente = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nombre_cliente .= ' ' . trim($a->document->inference->pages[0]->prediction->nombre_cliente->values[$paso]->content);
					}



					// nro_factura
					if ($a->document->inference->pages[0]->prediction->nro_factura->values) {
						$nro_factura = trim($a->document->inference->pages[0]->prediction->nro_factura->values[0]->content);
					} else {
						$nro_factura = 'N/A';
					}

					// fecha_emision
					if ($a->document->inference->pages[0]->prediction->fecha_emision->values) {
						$fecha_emision = trim($a->document->inference->pages[0]->prediction->fecha_emision->values[0]->content);
					} else {
						$fecha_emision = 'N/A';
					}

					// nro_cuenta
					if ($a->document->inference->pages[0]->prediction->nro_cuenta->values) {
						$nro_cuenta = trim($a->document->inference->pages[0]->prediction->nro_cuenta->values[0]->content);
					} else {
						$nro_cuenta = 'N/A';
					}

					
					// vencimiento_del_pago
					if ($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values) {
						$vencimiento_del_pago = trim($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[0]->content);
					} else {
						$vencimiento_del_pago = 'N/A';
					}

					// nro_medidor
					if ($a->document->inference->pages[0]->prediction->nro_medidor->values) {
						$nro_medidor = trim($a->document->inference->pages[0]->prediction->nro_medidor->values[0]->content);
					} else {
						$nro_medidor = 'N/A';
					}

					// proximo_vencimiento
					if ($a->document->inference->pages[0]->prediction->proximo_vencimiento->values) {
						$proximo_vencimiento = trim($a->document->inference->pages[0]->prediction->proximo_vencimiento->values[0]->content);
					} else {
						$proximo_vencimiento = 'N/A';
					}


					// contratada
					if ($a->document->inference->pages[0]->prediction->contratada->values) {
						$contratada = trim($a->document->inference->pages[0]->prediction->contratada->values[0]->content);
					} else {
						$contratada = 'N/A';
					}

					// consumida
					if ($a->document->inference->pages[0]->prediction->consumida->values) {
						$consumida = trim($a->document->inference->pages[0]->prediction->consumida->values[0]->content);
					} else {
						$consumida = 'N/A';
					}

					// consumo_act
					if ($a->document->inference->pages[0]->prediction->consumo_act->values) {
						$consumo_act = trim($a->document->inference->pages[0]->prediction->consumo_act->values[0]->content);
					} else {
						$consumo_act = 'N/A';
					}

					// consumo_reac
					if ($a->document->inference->pages[0]->prediction->consumo_reac->values) {
						$consumo_reac = trim($a->document->inference->pages[0]->prediction->consumo_reac->values[0]->content);
					} else {
						$consumo_reac = 'N/A';
					}

					

					// cargo_fijo
					if ($a->document->inference->pages[0]->prediction->cargo_fijo->values) {
						$cargo_fijo = trim($a->document->inference->pages[0]->prediction->cargo_fijo->values[0]->content);
					} else {
						$cargo_fijo = 'N/A';
					}

					// cargo_contr
					if ($a->document->inference->pages[0]->prediction->cargo_contr->values) {
						$cargo_contr = trim($a->document->inference->pages[0]->prediction->cargo_contr->values[0]->content);
					} else {
						$cargo_contr = 'N/A';
					}

					// cargo_adq
					if ($a->document->inference->pages[0]->prediction->cargo_adq->values) {
						$cargo_adq = trim($a->document->inference->pages[0]->prediction->cargo_adq->values[0]->content);
					} else {
						$cargo_adq = 'N/A';
					}

					// cargo_exc
					if ($a->document->inference->pages[0]->prediction->cargo_exc->values) {
						$cargo_exc = trim($a->document->inference->pages[0]->prediction->cargo_exc->values[0]->content);
					} else {
						$cargo_exc = 'N/A';
					}

					// cargo_var
					if ($a->document->inference->pages[0]->prediction->cargo_var->values) {
						$cargo_var = trim($a->document->inference->pages[0]->prediction->cargo_var->values[0]->content);
					} else {
						$cargo_var = 'N/A';
					}

					// subsidio
					if ($a->document->inference->pages[0]->prediction->subsidio->values) {
						$subsidio = trim($a->document->inference->pages[0]->prediction->subsidio->values[0]->content);
					} else {
						$subsidio = 'N/A';
					}
					

					// energia_inyectada
					if ($a->document->inference->pages[0]->prediction->energia_inyectada->values) {
						$energia_inyectada = trim($a->document->inference->pages[0]->prediction->energia_inyectada->values[0]->content);
					} else {
						$energia_inyectada = 'N/A';
					}


					$dataUpdate = array(
						'nombre_proveedor' => 'EDENOR CANON - T2', 
						'nro_cuenta' => isset($nro_cuenta) ? trim($nro_cuenta) : 'N/A',
						'tipo_de_tarifa' => isset($tipo_de_tarifa) ? trim($tipo_de_tarifa) : 'N/A',
						'nombre_cliente' => isset($nombre_cliente) ? trim($nombre_cliente) : 'N/A',
						'nro_medidor' => isset($nro_medidor) ? trim($nro_medidor) : 'N/A',
						'nro_factura' => isset($nro_factura) ? trim($nro_factura) : 'N/A',
						'periodo_del_consumo' => isset($periodo_del_consumo) ? trim($periodo_del_consumo) : 'N/A',
						'fecha_emision' => isset($fecha_emision) ? trim($fecha_emision) : 'N/A',
						'vencimiento_del_pago' => isset($vencimiento_del_pago) ? trim($vencimiento_del_pago) : 'N/A',
						'proximo_vencimiento' => isset($proximo_vencimiento) ? trim($proximo_vencimiento) : 'N/A',
						'total_importe' => isset($numero_decimal) ? trim($numero_decimal) : '0.00',
						'importe_1' => isset($numero_decimal) ? trim($numero_decimal) : '0.00', // 'importe_1' igual que 'total_importe'
						'consumo' => isset($consumida) ? trim($consumida) : '0.00',
						'total_vencido' => 'S/D', // Valor predeterminado fijo por ahora
						'domicilio_de_consumo' => isset($domicilio_de_consumo) ? trim($domicilio_de_consumo) : 'N/A',
						'dias_comprendidos' => isset($dias_comprendidos) ? trim($dias_comprendidos) : 'N/A',
						'dias_de_consumo' => isset($dias_de_consumo) ? trim($dias_de_consumo) : 'N/A',
						'consumo_dias_comprendidos' => isset($consumo_dias_comprendidos) ? trim($consumo_dias_comprendidos) : 'N/A',
						'cargo_variable_hasta' => isset($cargo_variable_hasta) ? trim($cargo_variable_hasta) : '0.00',
						'cargo_fijo' => isset($cargo_fijo) ? trim($cargo_fijo) : '0.00',
						'monto_car_var_hasta' => isset($monto_car_var_hasta) ? trim($monto_car_var_hasta) : '0.00',
						'moto_var_mayor' => isset($moto_var_mayor) ? trim($moto_var_mayor) : '0.00',
						'otros_conseptos' => isset($otros_conseptos) ? trim($otros_conseptos) : '0.00',
						'conceptos_electricos' => isset($conceptos_electricos) ? trim($conceptos_electricos) : '0.00',
						'impuestos' => isset($impuestos) ? trim($impuestos) : '0.00',
						'subsidio' => isset($subsidio) ? trim($subsidio) : '0.00',
						'energia_inyectada' => isset($energia_inyectada) ? trim($energia_inyectada) : '0.00',
						'p_contratada' => '0.00',
						'p_registrada' => '0.00',
						'p_excedida' => '0.00',
						'pot_punta' => '0.00',
						'pot_fuera_punta_cons' => '0.00',
						'ener_punta_act' => '0.00',
						'ener_punta_cons' => '0.00',
						'ener_resto_act' => '0.00',
						'ener_resto_cons' => '0.00',
						'ener_valle_act' => '0.00',
						'ener_valle_cons' => '0.00',
						'ener_reac_act' => '0.00',
						'ener_reac_cons' => '0.00',
						'e_reactiva' => '0.00',
						'tgfi' => '0.00',
						'cargo_pot_contratada' => '0.00',
						'cargo_pot_ad' => '0.00',
						'cargo_pot_excd' => '0.00',
						'recargo_tgfi' => '0.00',
						'consumo_pico_ant' => '0.00',
						'consumo_pico_vig' => '0.00',
						'cargo_pico' => '0.00',
						'consumo_resto_ant' => '0.00',
						'consumo_resto_vig' => '0.00',
						'cargo_resto' => '0.00',
						'consumo_valle_ant' => '0.00',
						'consumo_valle_vig' => '0.00',
						'cargo_valle' => '0.00',
						'e_actual' => '0.00',
						'cargo_contr' => isset($cargo_contr) ? trim($cargo_contr) : '0.00',
						'cargo_adq' => isset($cargo_adq) ? trim($cargo_adq) : '0.00',
						'cargo_exc' => isset($cargo_exc) ? trim($cargo_exc) : '0.00',
						'cargo_var' => isset($cargo_var) ? trim($cargo_var) : '0.00',
						'bimestre' => 'N/A',
						'ener_generada' => '0.00',
						'conseptos_electricos' => '0.00',
						'cosfi' => isset($energia_inyectada) ? trim($energia_inyectada) : '0.00',
						
						
					);
					



					break;


				case 3: // T3

					$importe = trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content);
			
					//$importe = str_replace(',', '.', str_replace('.', '', $importe));
		
				
					//$numero_decimal = number_format($importe, 2, '.', '');

					$totalIndices = count($a->document->inference->pages[0]->prediction->domicilio_de_consumo->values);
					$domicilio_de_consumo = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
  						$domicilio_de_consumo .= ' ' . trim($a->document->inference->pages[0]->prediction->domicilio_de_consumo->values[$paso]->content);
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->tipo_de_tarifa->values);
					$tipo_de_tarifa = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$tipo_de_tarifa .= ' ' . $a->document->inference->pages[0]->prediction->tipo_de_tarifa->values[$paso]->content;
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->nombre_cliente->values);
					$nombre_cliente = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nombre_cliente .= ' ' . trim($a->document->inference->pages[0]->prediction->nombre_cliente->values[$paso]->content);
					}


					// nro_factura
					if ($a->document->inference->pages[0]->prediction->nro_factura->values) {
						$nro_factura = $a->document->inference->pages[0]->prediction->nro_factura->values[0]->content;
					} else {
						$nro_factura = 'N/A';
					}

					// fecha_emision
					if ($a->document->inference->pages[0]->prediction->fecha_emision->values) {
						$fecha_emision = $a->document->inference->pages[0]->prediction->fecha_emision->values[0]->content;
					} else {
						$fecha_emision = 'N/A';
					}

					// nro_cuenta
					if ($a->document->inference->pages[0]->prediction->nro_cuenta->values) {
						$nro_cuenta = $a->document->inference->pages[0]->prediction->nro_cuenta->values[0]->content;
					} else {
						$nro_cuenta = 'N/A';
					}

					// vencimiento_del_pago
					if ($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values) {
						$vencimiento_del_pago = $a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[0]->content;
					} else {
						$vencimiento_del_pago = 'N/A';
					}

					// proximo_vencimiento
					if ($a->document->inference->pages[0]->prediction->proximo_vencimiento->values) {
						$proximo_vencimiento = $a->document->inference->pages[0]->prediction->proximo_vencimiento->values[0]->content;
					} else {
						$proximo_vencimiento = 'N/A';
					}

					

					// periodo_del_consumo
					if ($a->document->inference->pages[0]->prediction->periodo_del_consumo->values) {
						$periodo_del_consumo = $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[0]->content;
					} else {
						$periodo_del_consumo = 'N/A';
					}


					// p_contratada
					if ($a->document->inference->pages[0]->prediction->p_contratada->values) {
						$p_contratada = $a->document->inference->pages[0]->prediction->p_contratada->values[0]->content;
					} else {
						$p_contratada = '0.00';
					}

					// p_registrada
					if ($a->document->inference->pages[0]->prediction->p_registrada->values) {
						$p_registrada = $a->document->inference->pages[0]->prediction->p_registrada->values[0]->content;
					} else {
						$p_registrada = '0.00';
					}

					// p_excedida
					if ($a->document->inference->pages[0]->prediction->p_excedida->values) {
						$p_excedida = $a->document->inference->pages[0]->prediction->p_excedida->values[0]->content;
					} else {
						$p_excedida = '0.00';
					}

					// pot_punta
					if ($a->document->inference->pages[0]->prediction->pot_punta->values) {
						$pot_punta = $a->document->inference->pages[0]->prediction->pot_punta->values[0]->content;
					} else {
						$pot_punta = '0.00';
					}

					// pot_fuera_punta_cons
					if ($a->document->inference->pages[0]->prediction->pot_fuera_punta_cons->values) {
						$pot_fuera_punta_cons = $a->document->inference->pages[0]->prediction->pot_fuera_punta_cons->values[0]->content;
					} else {
						$pot_fuera_punta_cons = '0.00';
					}

					// ener_punta_act
					if ($a->document->inference->pages[0]->prediction->ener_punta_act->values) {
						$ener_punta_act = $a->document->inference->pages[0]->prediction->ener_punta_act->values[0]->content;
					} else {
						$ener_punta_act = '0.00';
					}

					// ener_punta_cons
					if ($a->document->inference->pages[0]->prediction->ener_punta_cons->values) {
						$ener_punta_cons = $a->document->inference->pages[0]->prediction->ener_punta_cons->values[0]->content;
					} else {
						$ener_punta_cons = '0.00';
					}

					// ener_resto_act
					if ($a->document->inference->pages[0]->prediction->ener_resto_act->values) {
						$ener_resto_act = $a->document->inference->pages[0]->prediction->ener_resto_act->values[0]->content;
					} else {
						$ener_resto_act = '0.00';
					}

					// ener_resto_cons
					if ($a->document->inference->pages[0]->prediction->ener_resto_cons->values) {
						$ener_resto_cons = $a->document->inference->pages[0]->prediction->ener_resto_cons->values[0]->content;
					} else {
						$ener_resto_cons = '0.00';
					}

					// ener_valle_act
					if ($a->document->inference->pages[0]->prediction->ener_valle_act->values) {
						$ener_valle_act = $a->document->inference->pages[0]->prediction->ener_valle_act->values[0]->content;
					} else {
						$ener_valle_act = '0.00';
					}

					// ener_valle_cons
					if ($a->document->inference->pages[0]->prediction->ener_valle_cons->values) {
						$ener_valle_cons = $a->document->inference->pages[0]->prediction->ener_valle_cons->values[0]->content;
					} else {
						$ener_valle_cons = '0.00';
					}

					// ener_reac_act
					if ($a->document->inference->pages[0]->prediction->ener_reac_act->values) {
						$ener_reac_act = $a->document->inference->pages[0]->prediction->ener_reac_act->values[0]->content;
					} else {
						$ener_reac_act = '0.00';
					}

					// ener_reac_cons
					if ($a->document->inference->pages[0]->prediction->ener_reac_cons->values) {
						$ener_reac_cons = $a->document->inference->pages[0]->prediction->ener_reac_cons->values[0]->content;
					} else {
						$ener_reac_cons = '0.00';
					}

					// nro_medidor
					if ($a->document->inference->pages[0]->prediction->nro_medidor->values) {
						$nro_medidor = $a->document->inference->pages[0]->prediction->nro_medidor->values[0]->content;
					} else {
						$nro_medidor = 'N/A';
					}

					// e_reactiva
					if ($a->document->inference->pages[0]->prediction->e_reactiva->values) {
						$e_reactiva = $a->document->inference->pages[0]->prediction->e_reactiva->values[0]->content;
					} else {
						$e_reactiva = '0.00';
					}

					// tgfi
					if ($a->document->inference->pages[0]->prediction->tgfi->values) {
						$tgfi = $a->document->inference->pages[0]->prediction->tgfi->values[0]->content;
					} else {
						$tgfi = '0.00';
					}

					// cargo_fijo
					if ($a->document->inference->pages[0]->prediction->cargo_fijo->values) {
						$cargo_fijo = $a->document->inference->pages[0]->prediction->cargo_fijo->values[0]->content;
					} else {
						$cargo_fijo = '0.00';
					}

					// cargo_pot_contratada
					if ($a->document->inference->pages[0]->prediction->cargo_pot_contratada->values) {
						$cargo_pot_contratada = $a->document->inference->pages[0]->prediction->cargo_pot_contratada->values[0]->content;
					} else {
						$cargo_pot_contratada = '0.00';
					}

					// cargo_pot_ad
					if ($a->document->inference->pages[0]->prediction->cargo_pot_ad->values) {
						$cargo_pot_ad = $a->document->inference->pages[0]->prediction->cargo_pot_ad->values[0]->content;
					} else {
						$cargo_pot_ad = '0.00';
					}

					// cargo_pot_excd
					if ($a->document->inference->pages[0]->prediction->cargo_pot_excd->values) {
						$cargo_pot_excd = $a->document->inference->pages[0]->prediction->cargo_pot_excd->values[0]->content;
					} else {
						$cargo_pot_excd = '0.00';
					}

					// recargo_tgfi
					if ($a->document->inference->pages[0]->prediction->recargo_tgfi->values) {
						$recargo_tgfi = $a->document->inference->pages[0]->prediction->recargo_tgfi->values[0]->content;
					} else {
						$recargo_tgfi = '0.00';
					}

					// consumo_pico_ant
					if ($a->document->inference->pages[0]->prediction->consumo_pico_ant->values) {
						$consumo_pico_ant = $a->document->inference->pages[0]->prediction->consumo_pico_ant->values[0]->content;
					} else {
						$consumo_pico_ant = '0.00';
					}

					// consumo_pico_vig
					if ($a->document->inference->pages[0]->prediction->consumo_pico_vig->values) {
						$consumo_pico_vig = $a->document->inference->pages[0]->prediction->consumo_pico_vig->values[0]->content;
					} else {
						$consumo_pico_vig = '0.00';
					}

					// cargo_pico
					if ($a->document->inference->pages[0]->prediction->cargo_pico->values) {
						$cargo_pico = $a->document->inference->pages[0]->prediction->cargo_pico->values[0]->content;
					} else {
						$cargo_pico = '0.00';
					}

					// consumo_resto_ant
					if ($a->document->inference->pages[0]->prediction->consumo_resto_ant->values) {
						$consumo_resto_ant = $a->document->inference->pages[0]->prediction->consumo_resto_ant->values[0]->content;
					} else {
						$consumo_resto_ant = '0.00';
					}

					// consumo_resto_vig
					if ($a->document->inference->pages[0]->prediction->consumo_resto_vig->values) {
						$consumo_resto_vig = $a->document->inference->pages[0]->prediction->consumo_resto_vig->values[0]->content;
					} else {
						$consumo_resto_vig = '0.00';
					}

					// cargo_resto
					if ($a->document->inference->pages[0]->prediction->cargo_resto->values) {
						$cargo_resto = $a->document->inference->pages[0]->prediction->cargo_resto->values[0]->content;
					} else {
						$cargo_resto = '0.00';
					}

					// consumo_valle_ant
					if ($a->document->inference->pages[0]->prediction->consumo_valle_ant->values) {
						$consumo_valle_ant = $a->document->inference->pages[0]->prediction->consumo_valle_ant->values[0]->content;
					} else {
						$consumo_valle_ant = '0.00';
					}

					// consumo_valle_vig
					if ($a->document->inference->pages[0]->prediction->consumo_valle_vig->values) {
						$consumo_valle_vig = $a->document->inference->pages[0]->prediction->consumo_valle_vig->values[0]->content;
					} else {
						$consumo_valle_vig = '0.00';
					}

					// cargo_valle
					if ($a->document->inference->pages[0]->prediction->cargo_valle->values) {
						$cargo_valle = $a->document->inference->pages[0]->prediction->cargo_valle->values[0]->content;
					} else {
						$cargo_valle = '0.00';
					}

					// subsidio
					if ($a->document->inference->pages[0]->prediction->subsidio->values) {
						$subsidio = $a->document->inference->pages[0]->prediction->subsidio->values[0]->content;
					} else {
						$subsidio = '0.00';
					}

					// e_actual
					if ($a->document->inference->pages[0]->prediction->e_actual->values) {
						$e_actual = $a->document->inference->pages[0]->prediction->e_actual->values[0]->content;
					} else {
						$e_actual = '0.00';
					}

					// impuestos
					if ($a->document->inference->pages[0]->prediction->impuestos->values) {
						$impuestos = $a->document->inference->pages[0]->prediction->impuestos->values[0]->content;
					} else {
						$impuestos = '0.00';
					}

					
					$dataUpdate = array(
						'nombre_proveedor' => 'EDENOR CANON - T3', 
						'nro_cuenta' => isset($nro_cuenta) ? trim($nro_cuenta) : 'N/A',
						'tipo_de_tarifa' => isset($tipo_de_tarifa) ? trim($tipo_de_tarifa) : 'N/A',
						'nombre_cliente' => isset($nombre_cliente) ? trim($nombre_cliente) : 'N/A',
						'nro_medidor' => isset($nro_medidor) ? trim($nro_medidor) : 'N/A',
						'nro_factura' => isset($nro_factura) ? trim($nro_factura) : 'N/A',
						'periodo_del_consumo' => isset($periodo_del_consumo) ? trim($periodo_del_consumo) : 'N/A',
						'fecha_emision' => isset($fecha_emision) ? trim($fecha_emision) : 'N/A',
						'vencimiento_del_pago' => isset($vencimiento_del_pago) ? trim($vencimiento_del_pago) : 'N/A',
						'proximo_vencimiento' => isset($proximo_vencimiento) ? trim($proximo_vencimiento) : 'N/A',
						'total_importe' => isset($importe) ? trim($importe) : '0.00',
						'importe_1' => isset($importe) ? trim($importe) : '0.00', // 'importe_1' igual que 'total_importe'
						'consumo'  => '0.00', // Valor predeterminado fijo por ahora
						'total_vencido' => 'S/D', // Valor predeterminado fijo por ahora
						'domicilio_de_consumo' => isset($domicilio_de_consumo) ? trim($domicilio_de_consumo) : 'N/A',
						'dias_comprendidos' => 'S/D', // Valor predeterminado fijo por ahora
						'dias_de_consumo' => 'S/D', // Valor predeterminado fijo por ahora
						'consumo_dias_comprendidos' => 'S/D', // Valor predeterminado fijo por ahora
						'cargo_variable_hasta' => '0.00', // Valor predeterminado fijo por ahora
						'cargo_fijo' => isset($cargo_fijo) ? trim($cargo_fijo) : '0.00',
						'monto_car_var_hasta' => '0.00', // Valor predeterminado fijo por ahora
						'moto_var_mayor' => '0.00', // Valor predeterminado fijo por ahora
						'otros_conseptos' => '0.00', // Valor predeterminado fijo por ahora
						'conceptos_electricos' => '0.00', // Valor predeterminado fijo por ahora
						'impuestos' => isset($impuestos) ? trim($impuestos) : '0.00',
						'subsidio' => isset($subsidio) ? trim($subsidio) : '0.00',
						'energia_inyectada' => '0.00', // Valor predeterminado fijo por ahora
						'p_contratada' => isset($p_contratada) ? trim($p_contratada) : '0.00',
						'p_registrada' => isset($p_registrada) ? trim($p_registrada) : '0.00',
						'p_excedida' => isset($p_excedida) ? trim($p_excedida) : '0.00',
						'pot_punta' => isset($pot_punta) ? trim($pot_punta) : '0.00',
						'pot_fuera_punta_cons' => isset($pot_fuera_punta_cons) ? trim($pot_fuera_punta_cons) : '0.00',
						'ener_punta_act' => isset($ener_punta_act) ? trim($ener_punta_act) : '0.00',
						'ener_punta_cons' => isset($ener_punta_cons) ? trim($ener_punta_cons) : '0.00',
						'ener_resto_act' => isset($ener_resto_act) ? trim($ener_resto_act) : '0.00',
						'ener_resto_cons' => isset($ener_resto_cons) ? trim($ener_resto_cons) : '0.00',
						'ener_valle_act' => isset($ener_valle_act) ? trim($ener_valle_act) : '0.00',
						'ener_valle_cons' => isset($ener_valle_cons) ? trim($ener_valle_cons) : '0.00',
						'ener_reac_act' => isset($ener_reac_act) ? trim($ener_reac_act) : '0.00',
						'ener_reac_cons' => isset($ener_reac_cons) ? trim($ener_reac_cons) : '0.00',
						'e_reactiva' => isset($e_reactiva) ? trim($e_reactiva) : '0.00',
						'tgfi' => isset($tgfi) ? trim($tgfi) : '0.00',
						'cargo_pot_contratada' => isset($cargo_pot_contratada) ? trim($cargo_pot_contratada) : '0.00',
						'cargo_pot_ad' => isset($cargo_pot_ad) ? trim($cargo_pot_ad) : '0.00',
						'cargo_pot_excd' => isset($cargo_pot_excd) ? trim($cargo_pot_excd) : '0.00',
						'recargo_tgfi' => isset($recargo_tgfi) ? trim($recargo_tgfi) : '0.00',
						'consumo_pico_ant' => isset($consumo_pico_ant) ? trim($consumo_pico_ant) : '0.00',
						'consumo_pico_vig' => isset($consumo_pico_vig) ? trim($consumo_pico_vig) : '0.00',
						'cargo_pico' => isset($cargo_pico) ? trim($cargo_pico) : '0.00',
						'consumo_resto_ant' => isset($consumo_resto_ant) ? trim($consumo_resto_ant) : '0.00',
						'consumo_resto_vig' => isset($consumo_resto_vig) ? trim($consumo_resto_vig) : '0.00',
						'cargo_resto' => isset($cargo_resto) ? trim($cargo_resto) : '0.00',
						'consumo_valle_ant' => isset($consumo_valle_ant) ? trim($consumo_valle_ant) : '0.00',
						'consumo_valle_vig' => isset($consumo_valle_vig) ? trim($consumo_valle_vig) : '0.00',
						'cargo_valle' => isset($cargo_valle) ? trim($cargo_valle) : '0.00',
						'e_actual' => isset($e_actual) ? trim($e_actual) : '0.00',
						'cargo_contr' => '0.00', // Valor predeterminado fijo por ahora
						'cargo_adq' => '0.00', // Valor predeterminado fijo por ahora
						'cargo_exc' => '0.00', // Valor predeterminado fijo por ahora
						'cargo_var' => '0.00', // Valor predeterminado fijo por ahora
						'bimestre' => 'N/A', // Valor predeterminado fijo por ahora
						'ener_generada' => '0.00', // Valor predeterminado fijo por ahora
						'conseptos_electricos' => '0.00', // Valor predeterminado fijo por ahora
						'cosfi' => isset($energia_inyectada) ? trim($energia_inyectada) : '0.00',
					);
					
					break;

			}

			$this->db->where('id', $mires[0]->id);
			$this->db->update('_datos_api_canon', $dataUpdate);
		} else {
			die('error leyendo datos api en base');
		}
	}






	public function leerApi_old()
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

				die();

				switch ($proveedor->id) {

					case 1://EDENOR CANON - T1
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


					case 2: //EDENOR CANON - T2

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

					case 3: //EDENOR CANON - T3

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