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

            $this->data['select_proyectos'] = $this->Manager_model->obtener_contenido_select('_proyectos','SELECCIONE PROYECTO','descripcion' ,'id ASC');
            $this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA','secretaria','id ASC');
            $this->data['select_dependencias'] = $this->Manager_model->obtener_contenido_select('_dependencias','SELECCIONE DEPENDENCIA','dependencia' ,'id ASC');
            $this->data['select_programas'] = $this->Manager_model->obtener_contenido_select('_programas','SELECCIONE PROGRAMA','descripcion' ,'id ASC');
            $this->data['select_proveedores'] = $this->Manager_model->obtener_contenido_select('_proveedores','SELECCIONE PROVEEDOR','nombre' ,'id ASC');
    		$this->data['select_tipo_pago'] = $this->Manager_model->obtener_contenido_select('_tipo_pago', 'Seleccionar Tipo Pago','tip_nombre','tip_id' );


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

		if ($this->input->is_ajax_request()) {
			$data = $row = array();

			$memData = $this->Manager_model->getRows($_POST);

			$estadoSucces = '<span class="acciones"><i class="text-success icon-check2 "></i></span>';
			foreach ($memData as $r) {

				$accionesVer = '<span class="acciones"><a title="ver archivo" href="/Admin/s/viewBatch/' . $r->id . '"  class=""><i class="icon-eye4" title="ver"></i> </a></span> ';
				$accionesEdit = '<span data-id_="' . $r->id . '" class="editar_ acciones" ><a title="Editar" href="/Admin/'.ucfirst($this->router->fetch_class()).'/editar/'.$r->id_dependencia.'"  class=""><i class=" text-warningr  icon-pencil4 " title="Editar "></i> </a> </span>';
				$accionesDelete = '<span data-id_="' . $r->id . '" class="borrar_ acciones" ><a title="Borrar " href="#"  class=""><i class=" text-danger icon-trash " title="Borrar "></i> </a> </span>';
				// $user = $this->ion_auth->user($r->user_add)->row();

				$data[] = array(
					$r->id_programa .' '. $r->id_proyecto,
					$r->id,
					$r->nro_cuenta,
					$r->nombre_secretaria .'' .$r->id_secretaria,
					$r->nombre_dependencia.'' .$r->id_dependencia,
					$r->id_programa .$r->descr_programa,
					$r->id_proyecto.$r->descr_proyecto,
					// $r->id_secretaria,
					// $r->id_dependencia,
					// $r->descr_programa,
					// $r->descr_proyecto,
					// $r->descr_proyecto,
					// $r->id_proveedor,
					// $r->tipo_pago,
					// $accionesEdit
				);
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->Manager_model->countAll(),
				"recordsFiltered" => $this->Manager_model->countFiltered($_POST),
				"data" => $data,
			);

			// Output to JSON format
			echo json_encode($output);exit();
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


		$this->data['content'] = $this->load->view('manager/secciones/indexaciones/' . $this->router->fetch_method(), $this->data, TRUE);
		// $this->data['select_tipo_pago'] = $this->Manager_model->obtener_contenido_select('_tipo_pago', 'Seleccionar Tipo Pago','tip_nombre','tip_id' );

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


		// $this->form_validation->set_rules('secretaria', 'secretaria', 'trim|greater_than[0]');
		$this->form_validation->set_rules('id_secretaria', 'Secretaria', 'trim|in_select[0]');
		$this->form_validation->set_rules('id_proveedor', 'Proveedor', 'trim|in_select[0]');
		$this->form_validation->set_rules('nro_cuenta', 'Nro de cuenta', 'trim|required');

		// $this->form_validation->set_rules('id_dependencia', 'Dependencia', '');
		// $this->form_validation->set_rules('id_programa', 'ID Programa', 'trim|in_select[0]');
		// $this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');

		if ($this->form_validation->run() == FALSE) {


			$this->data['content'] = $this->load->view('manager/secciones/indexaciones/' . $this->router->fetch_method(), $this->data, TRUE);

			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);

		} else {
			$datos = array(
				'id_secretaria'=> $this->input->post('id_secretaria') ,
				'id_programa' => $this->input->post('id_programa') ,
				'id_interno' => $this->input->post('id_interno') ,
				'descripcion' => $this->input->post('descripcion') ,
			);

			$this->Proyectos_model->grabar_datos("_indexaciones",$_POST); 
			redirect(base_url('Admin/Indexaciones'));

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