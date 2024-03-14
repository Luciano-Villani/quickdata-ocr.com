<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Programas extends backend_controller
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
			$this->table = '_programas';

			// $this->output->enable_profiler(TRUE);
		}
	}

	public function get_programas()
	{

		if ($this->input->is_ajax_request()) {
			$query = $this->db->select('id,id_interno, descripcion')
				->where('id_secretaria', $this->input->post('id'))
				->get('_programas');

			if ($query->result() > 0) {

				$respuesta = array(
					'data' => $query->result()
				);


				echo json_encode($respuesta);
			}
		}
	}

	public function delete()
	{
		try {
			$this->db->where('id', $_REQUEST['id']);
			$this->db->delete('_programas');

			$response = array(
				'mensaje' => 'Datos borrados',
				'title' => 'Programas',
				'status' => 'success',
			);
		} catch (Exception $e) {
			$response = array(
				'mensaje' => 'Error: ' . $e->getMessage(),
				'title' => 'Programas',
				'status' => 'error',
			);
		}

		// $grabar_datos_session = array(
		// 	'seccion' => 'Lectura de Documentos',
		// 	'mensaje' => 'El archivo ya existe - ' . $_POST['name'],
		// 	'estado' => 'error',
		// );

		// $this->session->set_userdata('save_data', $grabar_datos_session);

		echo json_encode($response);
		exit();
	}

	public function list_dt()
	{
		$memData = $this->Manager_model->getRows($_POST);
		$data = $row = array();

		foreach ($memData as $r) {

			$estado  = 0;  // para permitir borrar o no
			$btnClass = 'text-success-600';
			$index = $this->Manager_model->getWhere('_indexaciones', 'id_programa="' . $r->id_programa . '"');

			if ($index) {
				$estado  = 1;
				$btnClass = 'text-danger-600';
			}

			$acciones = '<ul class="icons-list">
			<li class="text-primary-600"><a data-id="' . $r->id_programa . '" class="edit_dato" href="#"><i class="icon-pencil7"></i></a></li>
			<li class="' . $btnClass . '"><a class="borrar_dato" data-estado="' . $estado . '" data-id="' . $r->id_programa . '" href="#"><i class="icon-trash"></i></a></li>
		</ul>';

			$data[] = array(
				$r->prog_id_interno,
				$r->prog_descripcion,
				$r->secretaria,
				$acciones,
			);
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->Manager_model->countAll(),
			"recordsFiltered" => $this->Manager_model->countFiltered($_POST),
			"data" => $data,
		);
		echo json_encode($output);
		exit();
	}

	public function edit()
	{
		if ($this->input->is_ajax_request()) {

			$data = $this->Manager_model->getWhere('_programas', 'id="' . $_REQUEST['id'] . '"');

			if ($data) {
				$response = array(
					'mensaje' => $_REQUEST['id'],
					'data' => $data,
					'status' => 'success',
				);
			} else {
				$response = array(
					'mensaje' => $_REQUEST['id'],
					'title' => 'EDITAR ' . $this->router->fetch_class() . ' - dato inexistente',
					'status' => 'error',
				);
			}


			echo json_encode($response);
			exit();
		}
	}
	public function listados()
	{


		// $this->BtnText = 'Editar';
		$script = array(
			base_url('assets/manager/js/plugins/tables/datatables/datatables.js'),

			base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
			base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
		);


		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;

		$this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
		$this->data['select_dependencias'] = $this->Manager_model->obtener_contenido_select('_dependencias', 'SELECCIONE DEPENDENCIA', 'dependencia', 'id ASC');

		if ($_SERVER['REQUEST_METHOD'] === "POST") {



			$this->form_validation->set_rules('secretaria', 'secretaría', 'trim|in_select[0]');
			//			$this->form_validation->set_rules('dependencia', 'Dependencia', 'trim|in_select[0]');
			$this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');


			if ($this->form_validation->run() != FALSE) {

				if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {

					$proy = $_REQUEST['id'];
					unset($_REQUEST['id']);

					$grabar_datos_array = array(
						'seccion' => 'Actualización datos ' . $this->router->fetch_class(),
						'mensaje' => 'Datos Actualizados ',
						'estado' => 'success',
					);


					try {
						$this->db->update($this->table, strtoupper($_REQUEST), array('id' => $proy));
					} catch (Exception $e) {
						$grabar_datos_array['estado'] = 'error';
						$grabar_datos_array['mensaje'] = $e->getMessage();
					}
					$this->session->set_userdata('save_data', $grabar_datos_array);
					redirect(base_url('Admin/Programas'));
				} else {
					$datos = array(
						'id_secretaria' => $this->input->post('id_secretaria'),
						'id_dependencia' => $this->input->post('id_dependencia'),
						'id_interno' => $this->input->post('id_interno'),
						'descripcion' => strtoupper($this->input->post('descripcion')),
					);
					$this->Manager_model->grabar_datos($this->table, $datos);
					redirect(base_url('Admin/Programas'));
				}
			}
		}

		$this->data['content'] = $this->load->view('manager/secciones/programas/' . $this->router->fetch_method(), $this->data, TRUE);

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

		$this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
		$this->data['select_dependencias'] = $this->Manager_model->obtener_contenido_select('_dependencias', 'SELECCIONE DEPENDENCIA', 'dependencia', 'id ASC');

		// $this->form_validation->set_rules('secretaria', 'secretaria', 'trim|greater_than[0]');
		$this->form_validation->set_rules('secretaria', 'secretaria', 'trim|greater_than[0]');
		// $this->form_validation->set_rules('dependencia', 'Dependencia', 'trim|greater_than[0]');
		$this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');
		$this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');

		if ($this->form_validation->run() == FALSE) {

			$this->data['content'] = $this->load->view('manager/secciones/programas/' . $this->router->fetch_method(), $this->data, TRUE);

			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);
		} else {
			$datos = array(
				'id_secretaria' => $this->input->post('id_secretaria'),
				'id_dependencia' => $this->input->post('id_dependencia'),
				'id_interno' => $this->input->post('id_interno'),
				'descripcion' => $this->input->post('descripcion'),
			);

			$this->Programas_model->grabar_datos("_programas", $this->input->post());
			redirect(base_url('Admin/Programas'));
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
