<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dependencias extends backend_controller
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
			$this->BtnText = 'Agregar';
			$this->table  = '_dependencias';
		}

	}


	public function get_dependencias(){

	if($this->input->is_ajax_request())
    {
		$query = $this->db->select('id,dependencia')
		->where('id_secretaria',$this->input->post('id') )
		->get('_dependencias');

		if ($query->result() > 0) {

			$respuesta = array(
				'data'=>$query->result()
			);
			echo json_encode($respuesta);
			
		}
	}


	}
	public function index()
	{
		die('index');
	}
	public function register()
	{
	}

	public function list_dt()
	{

		if ($this->input->is_ajax_request()) {
			$data = $row = array();

			$memData = $this->Manager_model->getRows($_POST);
		

			$estado = '<span class="acciones"><i class="text-success icon-check2 "></i></span>';
			foreach ($memData as $r) {

				$accionesVer = '<span class="acciones"><a title="ver archivo" href="/Admin/s/viewBatch/' . $r->id . '"  class=""><i class="icon-eye4" title="ver"></i> </a></span> ';

				$accionesView = '<button type="button" class="btn btn-primary btn-sm" data-popup="tooltip" title="" data-placement="left" id="left" data-original-title="Left tooltip">Tooltip <i class="icon-play3 position-right"></i></button>';
				$accionesEdit = '<span data-id_="' . $r->id . '" class="editar_ acciones" ><a title="Editar" href="/Admin/'.ucfirst($this->router->fetch_class()).'/editar/'.$r->id_dependencia.'"  class=""><i class=" text-warningr  icon-pencil4 " title="Editar "></i> </a> </span>';
				$accionesDelete = '<span data-id_="' . $r->id . '" class="borrar_ acciones" ><a title="Borrar " href="#"  class=""><i class=" text-danger icon-trash " title="Borrar "></i> </a> </span>';
				// $user = $this->ion_auth->user($r->user_add)->row();

				$data[] = array(
					// $r->id_dependencia,
					$r->secretaria,
					$r->dependencia,
					$r->direccion,
					// $r->last_name . ' ' . $r->first_name,
					$accionesEdit
				);
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->Dependencias_model->countAll(),
				"recordsFiltered" => $this->Dependencias_model->countFiltered($_POST),
				"data" => $data,
			);

			// Output to JSON format
			echo json_encode($output);exit();
		
		}
		$data = $this->Dependencias_model->list_dt();

		return $data;
	}

	public function listados($id=NULL )
	{
		$script = array(
			base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
		);

		if($id && $id !=NULL){

			$this->BtnText = 'Editar';
	
			$editData = $this->Manager_model->get_data('_dependencias', $id);

			$this->data['dependencia'] = $editData->dependencia;
			$this->data['id_secretaria'] = $editData->id_secretaria;
			$this->data['id_dependencia'] = $id;
			$this->data['direccion'] =$editData->direccion;

		}

		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;

		$this->data['select_secretarias'] = $this->Secretarias_model->obtener_contenido_select('_secretarias', 'id ASC');

		if($_SERVER['REQUEST_METHOD'] === "POST"){
			$this->form_validation->set_rules('id_secretaria', 'secretaria', 'trim|in_select[0]');
			$this->form_validation->set_rules('dependencia', 'Nombre dependencia', 'trim|required');
			$this->form_validation->set_rules('direccion', 'Dirección dependencia', 'trim|required');
	
	
			if ($this->form_validation->run() != FALSE) {

				$datos = array(
					'id_secretaria'=> $this->input->post('id_secretaria') ,
					'dependencia' => $this->input->post('dependencia') ,
					'direccion' => $this->input->post('direccion') ,
				);

				// preparo para editar
				if(isset($_REQUEST['id_dependencia']) && $_REQUEST['id_dependencia'] !=NULL){
					$depend = $_REQUEST['id_dependencia'];
					unset($_REQUEST['id_dependencia']);

					$grabar_datos_array = array(
						'seccion' => 'Actualización datos '.$this->router->fetch_class(),
						'mensaje' => 'Datos Actualizados ',
						'estado' => 'success',
					);

					$this->session->set_userdata('save_data', $grabar_datos_array);

					$this->db->update($this->table,$_REQUEST, array('id' =>$depend));
		
					redirect(base_url('Admin/Dependencias'));
				}

				$this->Manager_model->grabar_datos($this->table,$datos); 
				redirect(base_url('Admin/Dependencias'));
			}
		}

		

		$this->data['content'] = $this->load->view('manager/secciones/dependencias/' . $this->router->fetch_method(), $this->data, TRUE);

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

        $this->data['select_secretarias'] = $this->Secretarias_model->obtener_contenido_select('_secretarias', 'id ASC');


		// $this->form_validation->set_rules('secretaria', 'secretaria', 'trim|greater_than[0]');
		$this->form_validation->set_rules('secretaria', 'secretaria', 'trim|greater_than[0]');
		$this->form_validation->set_rules('dependencia', 'Nombre dependencia', 'trim|required');

		if ($this->form_validation->run() == FALSE) {


			$this->data['content'] = $this->load->view('manager/secciones/dependencias/' . $this->router->fetch_method(), $this->data, TRUE);

			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);

		} else {
			$datos = array(
				'id_secretaria'=> $this->input->post('secretaria') ,
				'dependencia' => $this->input->post('dependencia') ,
			);



			$this->Dependencias_model->grabar_datos("_dependencias",$datos); 
			redirect(base_url('Admin/Dependencias'));

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