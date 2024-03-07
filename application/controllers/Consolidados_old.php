<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Consolidados extends backend_controller
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
			$this->load->model('manager/Obras_model');


			$this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
			$this->data['select_dependencias'] = $this->Manager_model->obtener_contenido_select('_dependencias', 'SELECCIONE DEPENDENCIA', 'dependencia', 'id ASC');
			$this->data['select_proyectos'] = $this->Manager_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');

			// $this->output->enable_profiler(TRUE);
		}

	}


	public function delete()
	{
		$this->db->where('nombre_archivo', $_REQUEST['file']);
		$this->db->delete('_consolidados');

		$data = array(
			'consolidado' =>0
		);

		$this->db->where('nombre_archivo', $_REQUEST['file']);
		$this->db->update('_datos_api', $data);

		$this->db->where('code', $_REQUEST['lote']);
		$this->db->update('_lotes', $data);

		$response = array(
			'mensaje' => 'Datos borrados',
			'title' => '_consolidados',
			'status' => 'success',
		);

		echo json_encode($response);
		exit();
	}
	public function list_dt($tipo = null, $tabla = null, $search = '')
	{
		// $data=array();
		if ($this->input->is_ajax_request()) {

			$data = $row = array();
			
			$memData = $this->Manager_model->getRows($_POST);

			foreach ($memData as $r) {

				// $prov = $this->Manager_model->getWhere('_proveedores', 'nombre LIKE "%'.$r->proveedor.'%"');

				// $data = array(
				// 	'periodo_contable'=> fecha_es($r->fecha_consolidado,'F a', false)
				// );
				// $this->db->where('id', $r->id);
				// $this->db->update('_consolidados', $data);
				// echo $this->db->last_query();
				// die();
				// echo '<pre>';
				// var_dump( $prov->id ); 
				// echo '</pre>';
				// die();

				$indexador = $this->Manager_model->getWhere('_indexaciones', 'nro_cuenta="'. $r->nro_cuenta . '"');
				//$r->expediente = $indexador->expediente;

				$accionesVer = '<a title="ver archivo" href="/Admin/Lecturas/Views/' . $r->id_lectura_api . '"  class=" "><i class="icon-eye4" title="ver archivo"></i> </a> ';
				$accionesDelete = '<span class="borrar_dato acciones" data-lote="' . $r->lote . '" data-file="' . $r->nombre_archivo . '"><a title="Borrar lote" href="#"  class=""><i class=" text-danger icon-trash " title="Borrar Datos"></i> </a> </span>';
				$punto =".";
		
				if(strlen($r->id_programa) == 1){
					$r->id_programa = '0'.$r->id_programa;
				}
				if($r->id_proyecto != '0' ){

					if(strlen($r->id_proyecto) == 1){
						
						$r->id_proyecto = ".0".intval($r->id_proyecto);
					}else{
						$r->id_proyecto = ".".$r->id_proyecto;
					}
					
				}else{
					$r->id_proyecto = '';
				}
	
				$data[] = array(
					$r->id_consolidado,
					$r->codigo_proveedor,
					$r->id_proyecto,
					$r->periodo_contable,
					$r->proveedor .'('.$r->codigo_proveedor.')',
					$r->expediente,
					$r->secretaria,
					$r->jurisdiccion,
					$r->id_programa,
					$r->jurisdiccion,
					$r->objeto,
					$r->dependencia,
					$r->dependencia_direccion,
					$r->tipo_pago,
					$r->nro_cuenta,
					$r->nro_factura,
					$r->periodo_del_consumo,
					fecha_es($r->fecha_vencimiento, 'd/m/a', false),
					fecha_es($r->preventivas, 'd/m/a', false),
					$r->importe,
					$accionesVer . $accionesDelete
				);
			}
			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->Manager_model->countAll(),
				"recordsFiltered" => $this->Manager_model->countFiltered($_POST),
				"data" => $data,
			);
			// Output to JSON format
			echo json_encode($output);
		}
	}

	public function listados()
	{
		$css = array(
			base_url('assets/manager/js/plugins/daterange-picker/daterange-picker.css'),
		);

		$script = array(
			base_url('assets/manager/js/plugins/daterange-picker/moment.min.js'),
			base_url('assets/manager/js/plugins/daterange-picker/daterangepicker.js'),
			base_url('assets/manager/js/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method() . '.js'),
		);


		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = $css;

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;
		$this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
		$this->data['select_programa'] = $this->Manager_model->obtener_contenido_select('_programas', 'SELECCIONE PROGRAMA', 'descripcion', 'id ASC');
		;
		$this->data['select_proyecto'] = $this->Manager_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');
		;
		$this->data['select_proveedores'] = $this->Manager_model->obtener_contenido_select('_proveedores', '', 'nombre', 'id ASC', false);
		$this->data['select_tipo_pago'] = $this->Manager_model->obtener_contenido_select('_tipo_pago', '', 'tip_nombre', 'tip_id ASC', false);
		$this->data['select_periodo_contable'] = $this->Manager_model->obtener_contenido_select('_consolidados', '', 'periodo_contable', 'periodo_contable DESC', false);


		if ($_SERVER['REQUEST_METHOD'] === "POST") {
			$this->form_validation->set_rules('id_proyecto', 'ID Proyecto', 'trim|in_select[0]');
			$this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');
			$this->form_validation->set_rules('descripcion', 'Descripción', 'trim|required');



			if ($this->form_validation->run() != FALSE) {
				$datos = array(
					'id_proyecto' => $this->input->post('id_proyecto'),
					'id_interno' => $this->input->post('id_interno'),
					'descripcion' => $this->input->post('descripcion'),
				);
				$this->Manager_model->grabar_datos("_obras", $datos);
				redirect(base_url('Admin/Obras'));

			}

		}


		$this->data['content'] = $this->load->view('manager/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method(), $this->data, TRUE);

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
			base_url('assets/manager/js/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method() . '.js'),
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