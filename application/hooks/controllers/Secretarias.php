<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Secretarias extends backend_controller
{
	function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			redirect('Login');
		} else {
			$this->load->model('Ion_auth_model');
			$this->load->model('/Manager/Usuarios_model');
			$this->load->model('/Manager/Secretarias_model');
			// $this->output->enable_profiler(TRUE);
		}
		$this->table = '_secretarias';
	}


	public function edit()
	{
		if ($this->input->is_ajax_request()) {

			$data = $this->Manager_model->getWhere('_secretarias', 'id="' . $_REQUEST['id'] . '"');

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
	public function delete()
	{
		try {
			$this->db->where('id', $_REQUEST['id']);
			$this->db->delete('_secretarias');

			$response = array(
				'mensaje' => 'Datos borrados',
				'title' => 'Secretarias',
				'status' => 'success',
			);
		} catch (Exception $e) {
			$response = array(
				'mensaje' => 'Error: ' . $e->getMessage(),
				'title' => 'Programas',
				'status' => 'error',
			);
		}

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
			$index = $this->Manager_model->getWhere('_indexaciones','id_secretaria="'.$r->id.'"');

			if($index){
				$estado  = 1; 
				$btnClass = "text-danger-600";
			}

			$acciones = '<ul class="icons-list">
			<li class="text-primary-600">
				<a class="edit_dato" data-id="' . $r->id . '" href="#"><i class="icon-pencil7"></i></a>
			</li>
			<li class="'.$btnClass.'">
				<a data-estado="'. $estado.'" data-id="'. $r->id.'" href="#" class="borrar_dato"><i class="icon-trash"></i></a>
			</li>
			</ul>';

			$data[] = array(
				$r->major,
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
		exit();;
	}

	public function listados()
	{


		$grabar_datos_session = array(
			'error_form' => '',
			'cardCollapsed' => '',
		);

		if ($_SERVER['REQUEST_METHOD'] === "POST") {

			// $this->form_validation->set_rules('rafam', 'Jurisdiccion - Rafam', 'trim|required|callback_check_username');
			$this->form_validation->set_rules('major', 'Jurisdiccion - Major', 'trim|required');
			$this->form_validation->set_rules('secretaria', 'Jurisdiccion descripción', 'trim|required');

			if ($this->form_validation->run() != FALSE) {
				if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {

					$proy = $_REQUEST['id'];
					unset($_REQUEST['id']);
	
					$grabar_datos_array = array(
						'seccion' => 'Actualización datos ' . $this->router->fetch_class(),
						'mensaje' => 'Datos Actualizados ',
						'status' => 'success',
						'estado' => 'success',
					);
	
	
					try {
						$this->db->update($this->table, $_REQUEST, array('id' => $proy));
					} catch (Exception $e) {
						$grabar_datos_array['estado'] = 'error';
						$grabar_datos_array['mensaje'] = $e->getMessage();
					}
					$this->session->set_userdata('save_data', $grabar_datos_array);
					redirect(base_url('Admin/'.ucfirst($this->router->fetch_class())));
				} else {

					$grabar_datos_array = array(
						'seccion' 	=> $this->router->fetch_class(),
						'mensaje' => 'Datos Guardados ',
						'status' => 'success',
						'estado' => 'success',
					);
					$this->session->set_userdata('save_data', $grabar_datos_array);

					$datos = array(
						'major' => $this->input->post('major'),
						'secretaria' => $this->input->post('secretaria'),
					);
	
					$this->Manager_model->grabar_datos("_secretarias", $datos);
	
					redirect(base_url('Admin/Secretarias'));
				}

			} else {
			}
		}

		$script = array(
			base_url('assets/manager/js/plugins/forms/styling/uniform.min.js'),
			base_url('assets/manager/js/plugins/tables/datatables/datatables.js'),
			//			base_url('assets/manager/js/plugins/tables/datatables/datatables.min.js'),
			//			base_url('assets/manager/js/plugins/tables/datatables/datatables_advanced.js'),
			base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
			base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
		);

		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;


		$this->data['content'] = $this->load->view('manager/secciones/secretarias/' . $this->router->fetch_method(), $this->data, TRUE);

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


		$this->form_validation->set_rules('rafam', 'Jurisdiccion - Rafam', 'trim|required|callback_check_username');
		$this->form_validation->set_rules('major', 'Jurisdiccion - Major', 'trim|required');
		$this->form_validation->set_rules('secretaria', 'Jurisdiccion descripción', 'trim|required');

		if ($this->form_validation->run() == FALSE) {

			if ($id) {
				if ($this->data['usuario'] = $this->ion_auth->user($id)->result()) {
				}
			}

			$this->data['content'] = $this->load->view('manager/secciones/secretarias/' . $this->router->fetch_method(), $this->data, TRUE);

			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);
		} else {

			if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {

				$proy = $_REQUEST['id'];
				unset($_REQUEST['id']);

				$grabar_datos_array = array(
					'seccion' => 'Actualización datos ' . $this->router->fetch_class(),
					'mensaje' => 'Datos Actualizados ',
					'estado' => 'success',
				);


				try {
					$this->db->update($this->table, $_REQUEST, array('id' => $proy));
				} catch (Exception $e) {
					$grabar_datos_array['estado'] = 'error';
					$grabar_datos_array['mensaje'] = $e->getMessage();
				}
				$this->session->set_userdata('save_data', $grabar_datos_array);
				redirect(base_url('Admin/'.$this->router->fetch_class()));
			} else {

				$grabar_datos_array = array(
					'seccion' => $this->router->fetch_class(),
					'mensaje' => 'Datos Guardados ',
					'estado' => 'success',
				);
				$this->session->set_userdata('save_data', $grabar_datos_array);
				$datos = array(
					'rafam' => $this->input->post('rafam'),
					'major' => $this->input->post('major'),
					'secretaria' => $this->input->post('secretaria'),
				);
				$this->Secretarias_model->grabar_datos("_secretarias", $datos);
				redirect(base_url('Admin/Secretarias'));
			}
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
