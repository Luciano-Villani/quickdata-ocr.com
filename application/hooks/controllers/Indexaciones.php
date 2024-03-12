<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Indexaciones extends backend_controller
{
	function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			redirect('Login');
		} else {
			$this->load->model('Ion_auth_model');
			$this->load->model('manager/Usuarios_model');
			$this->load->model('manager/Secretarias_model');
			$this->load->model('manager/Dependencias_model');
			$this->load->model('manager/Proyectos_model');
			$this->load->model('manager/Manager_model');

			$this->data['select_proyectos'] = $this->Manager_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');
			$this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'secretaria ASC');
			$this->data['select_dependencias'] = $this->Manager_model->obtener_contenido_select('_dependencias', 'SELECCIONE DEPENDENCIA', 'dependencia', 'id ASC');
			$this->data['select_programas'] = $this->Manager_model->obtener_contenido_select('_programas', 'SELECCIONE PROGRAMA', 'descripcion', '');
			$this->data['select_proveedores'] = $this->Manager_model->obtener_contenido_select('_proveedores', 'SELECCIONE PROVEEDOR', 'nombre', 'nombre ASC');
			$this->data['select_tipo_pago'] = $this->Manager_model->obtener_contenido_select('_tipo_pago', 'Seleccionar Tipo Pago', 'tip_nombre', 'tip_id');
			$this->data['tabla'] = '_indexaciones';
			$this->BtnText = 'Agregar';
			// $this->output->enable_profiler(TRUE);
		}
	}

	public function index()
	{
		die('index');
	}
	public function register()
	{
	}
	public function list_profiles_dt()
	{

		$usuarios = $this->Usuarios_model->list_profiles_dt();

		return $usuarios;
	}
	public function list_dt($id = null)
	{

		if ($this->input->is_ajax_request()) {


			$data = $row = array();

			$memData = $this->Manager_model->getRows($_POST);

			$estadoSucces = '<span class="acciones"><i class="text-success icon-check2 "></i></span>';
			foreach ($memData as $r) {


				$accionesVer = '<span class="acciones"><a title="ver archivo" href="/Admin/s/viewBatch/' . $r->id . '"  class=""><i class="icon-eye4" title="ver"></i> </a></span> ';
				$accionesEdit = '<span data-id_="' . $r->id . '" class="editar_ acciones" ><a title="Editar" href="/Admin/' . ucfirst($this->router->fetch_class()) . '/editar/' . $r->id_dependencia . '"  class=""><i class=" text-warningr  icon-pencil4 " title="Editar "></i> </a> </span>';
				$accionesDelete = '<span data-id_="' . $r->id . '" class="borrar_ acciones" ><a title="Borrar " href="#"  class=""><i class=" text-danger icon-trash " title="Borrar "></i> </a> </span>';
				// $user = $this->ion_auth->user($r->user_add)->row();

				//	<li class="text-primary-600"><a href="/Admin/Indexaciones/editar/' . $r->id . '"><i class="icon-pencil7"></i></a></li>
				$acciones = '<ul class="icons-list">
			
				<li  class=" text-danger-600"><a class="borrar_file" data-id="' . $r->id . '" href="#"><i class="icon-trash"></i></a></li>
			</ul>';
				$data[] = array(
					$r->id_programa.' '.$r->id_proyecto,
					$r->id,
					$r->nom_proveedor,
					$r->nro_cuenta,
					$r->nombre_secretaria,
					$r->nombre_dependencia,
					$r->id_programa . "  " . $r->descr_programa,
					$r->id_proyecto . "  " . $r->descr_proyecto,
					$acciones
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
	}

	public function delete()
	{

		try {

			$this->db->where('id', $_REQUEST['id']);
			$this->db->delete($_REQUEST['tabla']);

			$response = array(
				'mensaje' => 'Datos borrados',
				'title' => str_replace('_', '', $_REQUEST['tabla']),
				'status' => 'success',
			);
		} catch (Exception $e) {
			$response = array(
				'mensaje' => 'Error: ' . $e->getMessage(),
				'title' => str_replace('_', '', $_REQUEST['tabla']),
				'status' => 'error',
			);
		}

		echo json_encode($response);
		exit();
	}

	public function listados($id = NULL)
	{

		$this->data['collapse'] = 'collapse';
		$script = array(
			base_url('assets/manager/js/plugins/tables/datatables/datatables.js'),
			base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
			base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
		);
		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;




		$this->data['programas_id_interno'] = $this->Programas_model->getIdInterno();
		$newdata = [];
		$pasada = 1;
		foreach ($this->data['programas_id_interno'] as $data) {
			$newdata[$data['id']]['id'] = $data['id'];
			$newdata[$data['id']]['id_interno'] = $data['id_interno'];
			$pasada++;
		};
		$this->data['programas_id_interno'] = $newdata;


		if ($id && $id != NULL) {

			$this->BtnText = 'Editar';
			$editData = $this->Manager_model->get_data('_indexaciones', $id);

			// $program = $this->Manager_model->getWhere('_programas', "id_secretaria = " . $editData->id_secretaria . " AND id_interno=" . $editData->id_programa);
			$program = 0;
			if ($program = $this->Manager_model->getWhere('_programas', "id_secretaria = " . $editData->id_secretaria . " AND id_interno=" . $editData->id_programa)) {

				$program = $program->id;
			};


			$this->data['indexador'] = $editData;
			$this->data['id_proveedor'] = $editData->id_proveedor;
			$this->data['nro_cuenta'] = $editData->nro_cuenta;
			$this->data['id_secretaria'] = $editData->id_secretaria;
			$this->data['id_dependencia'] = $editData->id_dependencia;
			$this->data['id_indexacion'] = $id;
			$this->data['id_programa'] = $editData->id_programa;
			$this->data['id_proyecto'] = $editData->id_proyecto;
			$this->data['tipo_pago'] = $editData->tipo_pago;
			$this->data['seleccion_programa'] = $program;
		}

		if ($_SERVER['REQUEST_METHOD'] === "POST") {


			$this->form_validation->set_rules('id_secretaria', 'Secretaria', 'trim|in_select[0]');
			$this->form_validation->set_rules('id_proveedor', 'Proveedor', 'trim|in_select[0]');
			$this->form_validation->set_rules('nro_cuenta', 'Nro de cuenta', 'trim|required|callback_check_nro_cuenta');
			$this->form_validation->set_rules('expediente', 'Expediente', 'trim|required');
			$this->form_validation->set_rules('tipo_pago', 'Tipo de pago', 'trim|required|in_select[0]');


			// $this->form_validation->set_rules('id_dependencia', 'Dependencia', '');
			// $this->form_validation->set_rules('id_programa', 'ID Programa', 'trim|in_select[0]');
			// $this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');

			if ($this->form_validation->run() != FALSE) {


				if (isset($_REQUEST['id_indexacion']) && $_REQUEST['id_indexacion'] != NULL) {
					$indexacion = $_POST["id_indexacion"];
					unset($_REQUEST["id_indexacion"]);

					$grabar_datos_array = array(
						'seccion' => 'Actualización datos ' . $this->router->fetch_class(),
						'mensaje' => 'Datos Actualizados ',
						'estado' => 'success',
						'status' => 'success',
					);
					$this->session->set_userdata('save_data', $grabar_datos_array);
					$_REQUEST['user_mod'] = $this->user->id;
					$this->db->update($this->data['tabla'], $_REQUEST, array('id' => $indexacion));
				} else {
					unset($_REQUEST["id_indexacion"]);

					$this->Manager_model->grabar_datos($this->data['tabla'], $_REQUEST);
					$grabar_datos_array = array(
						'seccion' => 'Alta nuevas ' . $this->router->fetch_class(),
						'mensaje' => 'Datos Grabados ',
						'estado' => 'success',
						'status' => 'success',
					);
					$this->session->set_userdata('save_data', $grabar_datos_array);
				}

				redirect(base_url('Admin/Indexaciones'));
				// $this->BtnText = 'Agregar';
			}
			$this->data['collapse'] = '';
		}
		$this->data['content'] = $this->load->view('manager/secciones/indexaciones/' . $this->router->fetch_method(), $this->data, TRUE);
		// $this->data['select_tipo_pago'] = $this->Manager_model->obtener_contenido_select('_tipo_pago', 'Seleccionar Tipo Pago','tip_nombre','tip_id' );

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
	}

	public function agregar($id = NULL)
	{



		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$script = array(
			base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
			base_url('assets/manager/js/plugins/forms/styling/uniform.min.js'),
			base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
			// base_url('assets/manager/js/secciones/'.$this->router->fetch_class().'.js'),
		);
		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;


		// $this->form_validation->set_rules('secretaria', 'secretaria', 'trim|greater_than[0]');
		$this->form_validation->set_rules('id_secretaria', 'Secretaria', 'trim|in_select[0]');
		$this->form_validation->set_rules('id_proveedor', 'Proveedor', 'trim|in_select[0]');
		$this->form_validation->set_rules('nro_cuenta', 'Nro de cuenta', 'trim|required|callback_check_nro_cuenta');

		// $this->form_validation->set_rules('id_dependencia', 'Dependencia', '');in_select
		// $this->form_validation->set_rules('id_programa', 'ID Programa', 'trim|[0]');
		// $this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');

		if ($this->form_validation->run() == FALSE) {


			$this->data['content'] = $this->load->view('manager/secciones/indexaciones/' . $this->router->fetch_method(), $this->data, TRUE);

			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);
		} else {
			$datos = array(
				'id_secretaria' => $this->input->post('id_secretaria'),
				'id_programa' => $this->input->post('id_programa'),
				'id_interno' => $this->input->post('id_interno'),
				'descripcion' => $this->input->post('descripcion'),
			);

			$this->Proyectos_model->grabar_datos("_indexaciones", $_POST);
			redirect(base_url('Admin/Indexaciones'));
		}
	}


	public function check_nro_cuenta($str)
	{

		if ($data = $this->Manager_model->getwhere('_indexaciones', 'nro_cuenta ="' . $str . '"')) {


			$this->form_validation->set_message('check_nro_cuenta', 'El Nro de cuenta se encuentra registrado');
			return FALSE;
		} else {

			return true;
		}
	}
	public function check_username($str)
	{
		if (!$this->ion_auth->username_check($str)) {
			return TRUE;
		} else {
			$this->form_validation->set_message('check_username', 'El usuario ya se encuentra registrado');
			return FALSE;
		}
	}
	public function check_email($str)
	{
		if (!$this->ion_auth->email_check($str)) {
			return TRUE;
		} else {
			$this->form_validation->set_message('check_email', 'El email ya se encuentra registrado');
			return FALSE;
		}
	}
}
