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

			$this->table = "_proyectos";
			// $this->output->enable_profiler(TRUE);
		}
	}

	public function list_select()
	{


		// envia tambien los proyectos
		if ($this->input->is_ajax_request()) {


			if ($this->input->post('id') != 0) {
				$this->db->where('id_programa', $this->input->post('id'));
				
			}
			$query = $this->db->select('id, id_interno,descripcion')->get('_proyectos');

		

			if ($query->result() > 0) {

				$proyectos = $query->result();
			}

			$respuesta = array(
				'proyectos' => $proyectos,

			);


			echo json_encode($respuesta);
		}
	}
	public function list_dt()
	{
		// $query = $this->db->select('*')->get('_programas');

		// foreach ($query->result()  as $r) {

		// 	$this->db->set('descripcion', strtoupper($r->descripcion));
		// 	$this->db->where('id', $r->id);
		// 	$this->db->update('_programas');
		// }

		// $query = $this->db->select('*')->get('_proyectos');

		// foreach ($query->result()  as $r) {
		// 	$this->db->set('descripcion', strtoupper($r->descripcion));
		// 	$this->db->where('id', $r->id);
		// 	$this->db->update('_proyectos');
		// }

		$memData = $this->Manager_model->getRows($_POST);
		// echo $this->db->last_query();
		// die();
		$data = $row = array();
		//	<li class="text-primary-600"><a href="#"><i class="icon-pencil7"></i></a></li>

		foreach ($memData as $r) {

			$estado  = 0;  // para permitir borrar o no
			$btnClass = 'text-success-600';
			$index = $this->Manager_model->getWhere('_indexaciones', 'id_proyecto="' . $r->p_id_interno . '"');
			if ($index) {
				$estado  = 1;
				$btnClass = 'text-danger-600';
			}

			$acciones = '<ul class="icons-list">
			<li class="text-primary-600"><a class="edit_dato" data-id="' . $r->id . '" href="#"><i class="icon-pencil7"></i></a></li>
			<li class="' . $btnClass . '"><a data-estado="' . $estado . '" class="borrar_dato" data-id="' . $r->id . '" href="#"><i class="icon-trash"></i></a></li>
		</ul>';

			$data[] = array(

				$r->p_id_interno,
				$r->p_id_interno . ' ' . $r->p_descripcion,
				$r->prog_id_interno . ' ' . $r->prog_descripcion,
				$r->id_secretaria . ' ' . $r->secretaria,
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

			$data = $this->Manager_model->getWhere('_proyectos', 'id="' . $_REQUEST['id'] . '"');

			if ($data) {
				$response = array(
					'mensaje' => $_REQUEST['id'],
					'data' => $data,
					'status' => 'success',
				);
			} else {
				$response = array(
					'mensaje' => $_REQUEST['id'],
					'title' => 'EDITAR Proyectos - dato inexistente',
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
			$this->db->delete('_proyectos');

			$response = array(
				'mensaje' => 'Datos borrados',
				'title' => 'Proyectos',
				'status' => 'success',
			);
		} catch (Exception $e) {
			$response = array(
				'mensaje' => 'Error: ' . $e->getMessage(),
				'title' => 'Proyectos',
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

	public function get_proyectos()
	{

		if ($this->input->is_ajax_request()) {
			$query = $this->db->select('id,id_interno, descripcion')
				->where('id_programa', $this->input->post('id'))
				->get('_proyectos');

			if ($query->result() > 0) {

				$respuesta = array(
					'data' => $query->result()
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

				if (isset($_REQUEST['id']) && $_REQUEST['id'] !='') {

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
					redirect(base_url('Admin/Proyectos'));
				} else {
					$datos = array(
						'id_secretaria' => $this->input->post('id_secretaria'),
						'id_programa' => $this->input->post('id_programa'),
						'id_interno' => $this->input->post('id_interno'),
						'descripcion' => strtoupper($this->input->post('descripcion')),
					);
					$this->Manager_model->grabar_datos("_proyectos", $datos);
					redirect(base_url('Admin/Proyectos'));
				}
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
