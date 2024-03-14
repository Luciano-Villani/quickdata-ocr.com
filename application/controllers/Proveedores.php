<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Proveedores extends backend_controller
{
	function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			redirect('Login');
		} else {
			$this->load->model('Ion_auth_model');
			$this->load->model('manager/Proveedores_model');

            $this->data['page_title'] =  ucfirst($this->router->fetch_class());
		}

	}

	public function index()
	{
		
	}
	public function register()
	{
	}
	public function list_profiles_dt()
	{

		$usuarios = $this->Usuarios_model->list_profiles_dt();

		return $usuarios;
	}
	public function list_proveedores_dt()
	{

		$proveedores = $this->Proveedores_model->list_proveedores_dt();

		return $proveedores;
	}

	public function profiles()
	{
		$this->data['page_title'] = 'Perfiles';

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


		$this->data['content'] = $this->load->view('manager/secciones/usuarios/' . $this->router->fetch_method(), $this->data, TRUE);

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
	}
	public function listados()
	{

		//$this->data['page_title'] = 'Secretarias';
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
			$this->form_validation->set_rules('codigo', 'Código de proveedor', 'trim|required');
			$this->form_validation->set_rules('nombre', 'Nombre de proveedor', 'trim|required');
			$this->form_validation->set_rules('objeto_gasto', 'Objeto del gasto', 'trim|required');
			$this->form_validation->set_rules('detalle_gasto', 'Detalle del gasto', 'trim|required');
			$this->form_validation->set_rules('urlapi', 'URL API PROVEEDOR', 'trim|required');
			$this->form_validation->set_rules('unidad_medida', 'Unidad de Medido / Plan', 'trim|required');
			if ($this->form_validation->run() != FALSE) {
				$datos = array(
					'codigo' => $this->input->post('codigo'),
					'nombre' => strtoupper($this->input->post('nombre')),
					'objeto_gasto' => $this->input->post('objeto_gasto'),
					'detalle_gasto' => strtoupper($this->input->post('detalle_gasto')),
					'urlapi' => $this->input->post('urlapi'),
					'unidad_medida' => strtoupper($this->input->post('unidad_medida')),
					
				);
				$this->Manager_model->grabar_datos("_proveedores", $datos);
				redirect(base_url('Admin/Proveedores'));

			}

		}


		$this->data['content'] = $this->load->view('manager/secciones/'.$this->router->fetch_class() .'/' . $this->router->fetch_method(), $this->data, TRUE);

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


		$this->form_validation->set_rules('username', 'Username', 'trim|required|callback_check_username');
		$this->form_validation->set_rules('first_name', 'Nombre', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Apellido', 'trim|required');
		// $this->form_validation->set_rules('password', 'Password', 'trim|required');
		//$this->form_validation->set_rules('password_2', 'Password Confirmación', 'trim|required|matches[password]');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|callback_check_email');
		$this->form_validation->set_rules('grupos[]', 'Seleccione un Grupo', 'required');
		if ($this->form_validation->run() == FALSE) {

			if($id){
				if($this->data['usuario'] = $this->ion_auth->user($id)->result()){
				}
			}

			$this->data['content'] = $this->load->view('manager/secciones/usuarios/' . $this->router->fetch_method(), $this->data, TRUE);

			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);

		} else {

			$groups = array();
			foreach ($this->input->post('grupos') as $key => $value) {
				array_push($groups, $value);
			}


			$additional_data = array(
				'first_name' => $this->input->post('first_name'),
				'last_name' => $this->input->post('last_name'),
			);

			$this->ion_auth->register($this->input->post('username'), $this->input->post('password'), $this->input->post('email'), $additional_data, $groups);
			redirect(base_url('Manager/secciones/usuarios/usuarios/'));

		}



	}



	public function checkApiUrl()
	{
		$proveedor = $this->Manager_model->getWhere('_proveedores', 'id='. $_POST['id_proveedor']);
		if($proveedor->urlapi !=''){
			$status = 'true';
		}else{
			$status = 'false';
		}
		$response = array(
			'status'=>$status,
			'proveedor'=>$proveedor
		);
		echo json_encode($response);
	}
}


?>