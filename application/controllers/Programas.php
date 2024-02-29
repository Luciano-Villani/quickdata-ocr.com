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

			// $this->output->enable_profiler(TRUE);
		}

	}

	public function get_programas(){


		if($this->input->is_ajax_request())
		{
			$query = $this->db->select('id,id_interno, descripcion')
			->where('id_secretaria',$this->input->post('id') )
			->get('_programas');
	
			if ($query->result() > 0) {
	
				$respuesta = array(
					'data'=>$query->result()
				);

	
				echo json_encode($respuesta);
				
			}
		}
	
	
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

		$data = $this->Programas_model->list_dt();

		return $data;
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

		$this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA','secretaria','id ASC');
        $this->data['select_dependencias'] = $this->Manager_model->obtener_contenido_select('_dependencias','SELECCIONE DEPENDENCIA','dependencia' ,'id ASC');

		if($_SERVER['REQUEST_METHOD'] === "POST"){

			$this->form_validation->set_rules('secretaria', 'secretaría', 'trim|in_select[0]');
//			$this->form_validation->set_rules('dependencia', 'Dependencia', 'trim|in_select[0]');
			$this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');

	
			if ($this->form_validation->run() != FALSE) {
				$datos = array(
					'id_secretaria'=> $this->input->post('id_secretaria') ,
					'id_dependencia' => $this->input->post('id_dependencia') ,
					'id_interno' => $this->input->post('id_interno') ,
					'descripcion' => $this->input->post('descripcion') ,
				);
	
				$this->Manager_model->grabar_datos("_programas",$datos); 
				redirect(base_url('Admin/Programas'));
			}
		}

		$this->data['content'] = $this->load->view('manager/secciones/programas/' . $this->router->fetch_method(), $this->data, TRUE);

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
	}

	public function agregar($id=NULL)
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

        $this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA','secretaria','id ASC');
        $this->data['select_dependencias'] = $this->Manager_model->obtener_contenido_select('_dependencias','SELECCIONE DEPENDENCIA','dependencia' ,'id ASC');

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
				'id_secretaria'=> $this->input->post('id_secretaria') ,
				'id_dependencia' => $this->input->post('id_dependencia') ,
				'id_interno' => $this->input->post('id_interno') ,
				'descripcion' => $this->input->post('descripcion') ,
			);

			$this->Programas_model->grabar_datos("_programas",$this->input->post()); 
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


?>