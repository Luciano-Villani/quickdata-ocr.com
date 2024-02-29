<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Proyectos extends backend_controller
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

			$this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
			$this->data['select_dependencias'] = $this->Manager_model->obtener_contenido_select('_dependencias', 'SELECCIONE DEPENDENCIA', 'dependencia', 'id ASC');
			$this->data['select_programas'] = $this->Manager_model->obtener_contenido_select('_programas', 'SELECCIONE PROGRAMA', 'descripcion', 'id ASC');
			$this->data['select_proyectos'] = $this->Manager_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');

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
	public function list_dt()
	{

		$data = $this->Proyectos_model->list_dt();

		return $data;
	}
	public function get_proyectos(){


		if($this->input->is_ajax_request())
		{
			$query = $this->db->select('id,id_interno, descripcion')
			->where('id_programa',$this->input->post('id') )
			->get('_proyectos');
	
			if ($query->result() > 0) {
	
				$respuesta = array(
					'data'=>$query->result()
				);

	
				echo json_encode($respuesta);
				
			}
		}
	
	
		}
	public function listados()
	{

		$script = array(
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

		if ($_SERVER['REQUEST_METHOD'] === "POST") {
			// $this->form_validation->set_rules('secretaria', 'secretaria', 'trim|greater_than[0]');
			$this->form_validation->set_rules('id_secretaria', 'Secretaria', 'trim|in_select[0]');
			$this->form_validation->set_rules('id_dependencia', 'Dependencia', '');
			$this->form_validation->set_rules('id_programa', 'ID Programa', 'trim|in_select[0]');
			$this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');
			$this->form_validation->set_rules('descripcion', 'Descripción', 'trim|required');

			if ($this->form_validation->run() != FALSE) {
				$datos = array(
					'id_secretaria' => $this->input->post('id_secretaria'),
					'id_programa' => $this->input->post('id_programa'),
					'id_interno' => $this->input->post('id_interno'),
					'descripcion' => $this->input->post('descripcion'),
				);
				$this->Manager_model->grabar_datos("_proyectos", $datos);
				redirect(base_url('Admin/Proyectos'));

			}

		}


		$this->data['content'] = $this->load->view('manager/secciones/proyectos/' . $this->router->fetch_method(), $this->data, TRUE);

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





		if ($this->form_validation->run() == FALSE) {


			$this->data['content'] = $this->load->view('manager/secciones/proyectos/' . $this->router->fetch_method(), $this->data, TRUE);

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

			$this->Proyectos_model->grabar_datos("_proyectos", $datos);
			redirect(base_url('Admin/Proyectos'));

		}



	}



}


?>