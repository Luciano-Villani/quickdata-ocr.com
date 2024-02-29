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

		$data = $this->Secretarias_model->list_dt();

		return $data;
	}

	public function listados()
	{
		$grabar_datos_session = array(
			'error_form' => '',
			'cardCollapsed' => '',
		);

		if($_SERVER['REQUEST_METHOD'] === "POST"){

			// $this->form_validation->set_rules('rafam', 'Jurisdiccion - Rafam', 'trim|required|callback_check_username');
			$this->form_validation->set_rules('major', 'Jurisdiccion - Major', 'trim|required');
			$this->form_validation->set_rules('secretaria', 'Jurisdiccion descripción', 'trim|required');

				if ($this->form_validation->run() != FALSE) {
					$datos = array(
						// 'rafam'=> $this->input->post('rafam') ,
						'major' => $this->input->post('major') ,
						'secretaria' => $this->input->post('secretaria') ,
					);
		
					$this->Manager_model->grabar_datos("_secretarias",$datos); 
			
					redirect(base_url('Admin/Secretarias'));
		
				}else{
					
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
		
		
		$this->form_validation->set_rules('rafam', 'Jurisdiccion - Rafam', 'trim|required|callback_check_username');
		$this->form_validation->set_rules('major', 'Jurisdiccion - Major', 'trim|required');
		$this->form_validation->set_rules('secretaria', 'Jurisdiccion descripción', 'trim|required');

		if ($this->form_validation->run() == FALSE) {

			if($id){
				if($this->data['usuario'] = $this->ion_auth->user($id)->result()){
				}
			}

			$this->data['content'] = $this->load->view('manager/secciones/secretarias/' . $this->router->fetch_method(), $this->data, TRUE);

			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);

		} else {
			$datos = array(
				'rafam'=> $this->input->post('rafam') ,
				'major' => $this->input->post('major') ,
				'secretaria' => $this->input->post('secretaria') ,
			);



			$this->Secretarias_model->grabar_datos("_secretarias",$datos); 
			redirect(base_url('Admin/Secretarias'));

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