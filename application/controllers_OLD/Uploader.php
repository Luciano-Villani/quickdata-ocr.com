<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Uploader extends backend_controller
{
	function __construct()
	{
		parent::__construct();
        $this->load->helper('file');
		if (!$this->ion_auth->logged_in()) {
			redirect('Login');
		} else {

			$this->load->model('/Manager/TipoPago_model');
			$this->load->model('/Manager/Uploader_model');

		}

	}

	public function list_profiles_dt()
	{

		$usuarios = $this->Usuarios_model->list_profiles_dt();

		return $usuarios;
	}
	public function list_usuarios_dt()
	{

		$usuarios = $this->Usuarios_model->list_usuarios_dt();

		return $usuarios;
	}

	public function upload(){

        $data = array(); 
        if(!empty($_FILES['file']['name'])){ 
           // Set preference 
           $uploadPath = 'uploader/files/';
           $config["remove_spaces"] = TRUE;
           $config["overwrite"] = TRUE;
           $config['upload_path'] ='uploader/files';
        //    $config['allowed_types'] = 'jpg|jpeg|png|gif'; 
           $config['allowed_types'] = '*'; 
          $config['max_size'] = '1024'; // max_size in kb 
           $config['file_name'] = $_FILES['file']['name']; 
  
           // Load upload library 
           $this->load->library('upload',$config); 
     
           // File upload
           if($this->upload->do_upload('file')){ 
              // Get data about the file
              $uploadData = $this->upload->data(); 

			  	sleep(2);
				$data =  apiRest($uploadData);

			//  echo '<pre>';
			//  var_dump( $data); 
			// echo '</pre>';
			// die(); 

			$direccion = array_column($data['document']['inference']['pages'][0]['prediction']['direccion']['values'], 'content');
			$titular = array_column($data['document']['inference']['pages'][0]['prediction']['titular']['values'], 'content');


			$cadena_titular = implode(" ", $titular); 
			$cadena_direccion = implode(" ", $direccion); 
			echo '<br>';
			echo $cadena_titular; 
			echo '<br>';
			echo $cadena_direccion; 

			
			  die(); 
              $filename = $uploadData['file_name']; 
              $data['response'] = 'successfully uploaded '.$filename; 

           }else{ 
            echo 'error ->'.$this->upload->display_errors();   
              $data['response'] = 'failed'; 
           } 
        }else{ 
           $data['response'] = 'failed'; 
        }
        echo json_encode($data);	
    }
	public function index()
	{
		//$pagos = $this->TipoPago_model->getTiposPagos();
		$this->data['page_title'] = 'Uploader';
		$script = array(
			base_url('assets/manager/js/plugins/dropzone.min.js'),
//			base_url('assets/manager/js/plugins/tables/datatables/datatables.min.js'),
//			base_url('assets/manager/js/plugins/tables/datatables/datatables_advanced.js'),
			base_url('assets/manager/js/secciones/' . strtolower ($this->router->fetch_class() ). '/' . $this->router->fetch_method() . '.js?ver='.time()),
		);

		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;

		$this->data['content'] = $this->load->view('manager/secciones/'.strtolower ($this->router->fetch_class()).'/' . $this->router->fetch_method(), $this->data, TRUE);

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
	}
	public function listados()
	{

		$this->data['page_title'] = 'Usuarios';
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

	public function agregar()
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

	// functiones callback validacion de formularios

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