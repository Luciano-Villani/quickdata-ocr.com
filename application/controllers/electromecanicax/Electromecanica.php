<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Electromecanica extends backend_controller
{
	function __construct()
	{

		parent::__construct();
		if (!$this->ion_auth->logged_in() && (!$this->ion_auth->is_admin() || !$this->ion_auth->is_electro())) {
			redirect('Login');
		} else {
			$this->load->helper('file');
			$this->data['css_common'] = $this->css_common;
			$this->data['script_common'] = $this->script_common;
			$this->load->model('manager/Electromecanica_model', 'electromecanica');
		}
	}

	public function indexaciones_dt($nro_cuenta = null)
	{

		$my_nro_cuenta = urldecode($_POST['nro_cuenta']);

		$datps = $this->electromecanica->get_indexaciones($my_nro_cuenta);

		echo $datps;
	}
	public function get_edit()
	{
		if ($this->input->is_ajax_request()) {

			$data = $this->electromecanica->getWhere($_REQUEST['tabla'], 'id="' . $_REQUEST['id'] . '"');

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
	public function delete_old()
	{


		try {

			$this->db->where('id', $_REQUEST['id']);
			$this->db->delete($_REQUEST['table']);

			$cant_reg = $this->electromecanica->countAll($_REQUEST['table']);

			// echo '<pre>REQUEST';
			// var_dump( $_REQUEST ); 
			// echo '</pre>';
			// die();
			//ALTER TABLE users AUTO_INCREMENT=1001;
			// $query = $this->db->query('ALTER TABLE users AUTO_INCREMENT='.$cant_reg);
			// $query->result();
			$response = array(
				'mensaje' => 'Datos borrados',
				'title' => str_replace('_', ' ', $_REQUEST['table']),
				'status' => 'success',
			);
		} catch (Exception $e) {
			$response = array(
				'mensaje' => 'Error: ' . $e->getMessage(),
				'title' => str_replace('_', ' ', $_REQUEST['table']),
				'status' => 'error',
			);
		}

		echo json_encode($response);
		exit();
	}


	public function delete()
{
    // Verificar si 'file' y 'lote' están presentes en la solicitud
    if (isset($_REQUEST['file']) && isset($_REQUEST['lote'])) {
        // Eliminar el registro de la tabla _consolidados_canon
        $this->db->where('nombre_archivo', $_REQUEST['file']);
        $this->db->delete('_consolidados_canon');

        // Actualizar el campo consolidado a 0 en la tabla _datos_api_canon
        $data = array('consolidado' => 0);

        $this->db->where('nombre_archivo', $_REQUEST['file']);
        $this->db->update('_datos_api_canon', $data);

        // Actualizar el campo consolidado a 0 en la tabla _lotes_canon
        $this->db->where('code', $_REQUEST['lote']);
        $this->db->update('_lotes_canon', $data);

        $response = array(
            'mensaje' => 'Datos borrados',
            'title' => '_consolidados_canon',
            'status' => 'success',
        );
    } else {
        // Si los parámetros no están presentes, devolver un error
        $response = array(
            'mensaje' => 'Faltan parámetros en la solicitud',
            'title' => 'Error',
            'status' => 'error',
        );
    }

    // Devolver la respuesta como JSON
    echo json_encode($response);
    exit();
}
	

	public function index()
	{
		// $x = 1;
		// $data = file_get_contents("application/config/mindee/electromecanica_t" . $x . "_config.json");
		// $products = json_decode($data, true);

		// $file = $this->electromecanica->get_data('_datos_api_canon', 40);

		// $dato_api = json_decode($file->dato_api, true);

		// echo '<pre>';
		// var_dump( $dato_api['document']['inference']['pages'][0]['prediction'] ); 
		// echo '</pre>';
		// die();

	// 	$resp = [];


	// 	foreach ($products['selector']['features'] as $selector) {

	// 		$totalIndices = count($dato_api['document']['inference']['pages'][0]['prediction'][$selector['name']]['values']);
	
	// 		$valorCampo = '';

	// 		for ($paso = 0; $paso < $totalIndices; $paso++) {
	// 			$valorCampo .= $dato_api['document']['inference']['pages'][0]['prediction'][$selector["name"]]['values'][$paso]['content'];
	// 		}
	// 		array_push($resp, [$selector["name"] => $valorCampo]);
	// echo "<BR>";
	// echo $selector["name"] .' ---->'. $valorCampo;
	// 	}

		// echo '<pre>';
		// var_dump( $resp ); 
		// echo '</pre>';
		// die();
		// die();

		// $this->load->dbforge();

		// // echo '<pre>';
		// // var_dump( $driver ); 
		// // echo '</pre>';
		// // die();

		// //$tables = $this->db->list_tables();
		// //CREATE TABLE `u117285061_mvl_ocr_db`.`_t1` () ENGINE = InnoDB;
		// for ($x = 1; $x <= 3; $x++) {
		// 	if (!$this->db->table_exists('_t' . $x)) {

		// 		$my_query =  $this->db->query('CREATE TABLE `u117285061_mvl_ocr_db`.`_t' . $x . '` ( `id_t' . $x . '` INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`id_t' . $x . '`)) ENGINE = InnoDB;');

		// 		$fields = array(
		// 			'fecha_mod_t' . $x . ' datetime default current_timestamp on update CURRENT_TIMESTAMP',
		// 			'fecha_alta_t' . $x . ' datetime default current_timestamp',
		// 			'user_add_t' . $x => array(
		// 				'type' => 'int',
		// 				'constraint' => 11,
		// 				'default' =>  $this->user->id
		// 			),
		// 			'id_lote_t' . $x => array(
		// 				'type' => 'int', 'constraint' => 11,
		// 			),
		// 			'code_lote_t' . $x => array(
		// 				'type' => 'VARCHAR', 'constraint' => 255,
		// 			),		
		// 			'nombre_proveedor_t' . $x => array(
		// 				'type' => 'VARCHAR', 'constraint' => 255,
		// 			),
		// 			'nombre_archivo_temp_t' . $x => array(
		// 				'type' => 'VARCHAR', 'constraint' => 255,
		// 			),	
		// 			'nombre_archivo_t' . $x => array(
		// 				'type' => 'VARCHAR', 'constraint' => 255,
		// 			),
		// 			'id_proveedor_t' . $x => array(
		// 				'type' => 'int', 'constraint' => 11,
		// 			),
		// 			'tipo_proveedor_t' . $x => array(
		// 				'type' => 'int', 'constraint' => 11,
		// 			),
		// 			'importe_1_t' . $x => array(
		// 				'type' => 'DECIMAL(10,2)',

		// 			)

		// 		);
		// 		$this->dbforge->add_column('_t' . $x, $fields);
		// 	} else {



		// foreach ($products['selector']['features'] as $selector) {


		// 			if (!$this->db->field_exists($selector['name'] . "_t" . $x, "_t" . $x)) {


		// 				$fields = array(
		// 					$selector['name'] . '_t' . $x => array(
		// 						'type' => 'VARCHAR',
		// 						'constraint' => 100
		// 					),


		// 				);
		// 				$this->dbforge->add_column('_t' . $x, $fields);
		// 			}
		// 		}
		// }
		// }
		// foreach ($tables as $table) {
		//     echo $table;
		// }


		// var_dump( $products['selector']['features']); 
		// echo '</pre>';
		// die();
		$script = array(
			base_url('assets/manager/js/secciones/electromecanica/' . strtolower($this->router->fetch_class()) . '.js'),
		);
		$this->data['script'] = $script;
		$this->data['content'] = $this->load->view('manager/secciones/electromecanica/' . strtolower($this->router->fetch_class()), $this->data, TRUE);
		// var_dump('manager/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method());
		// die();

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
	}
	public function upload($id = null)
	{



		echo '<pre>';
		var_dump($_FILES);
		echo '</pre>';
		die();
		if ($this->input->is_ajax_request()) {

			if (!empty($_FILES['file']['name']) && !empty($_POST['id_proveedor'])) {

				$arc = explode('.', $_FILES['file']['name']);
				$nuevoNOmbre = limpiar_caracteres($arc[0]);
				$nombre_archivodb = $nuevoNOmbre . '.' . $arc[1];

				$proveedor = $this->Manager_model->get_data('_proveedores', $_POST['id_proveedor']);
				$nombre_fichero = 'uploader/files/' . strtolower($proveedor->codigo);

				if (!file_exists(strtolower($nombre_fichero))) {
					mkdir($nombre_fichero, 0777, true);
				}

				$data = array();
				// Set preference 
				$uploadPath = $nombre_fichero;
				$config["remove_spaces"] = TRUE;
				$config["overwrite"] = TRUE;
				$config['upload_path'] = $uploadPath;
				//    $config['allowed_types'] = 'jpg|jpeg|png|gif'; 
				$config['allowed_types'] = '*';
				$config['max_size'] = '10000'; // max_size in kb 
				$config['file_name'] = $nombre_archivodb;

				$this->load->library('upload', $config);

				$response = array(
					'mensaje' => 'inico',
					'title' => 'LOTES 562',
					'status' => 'error',
				);

				if ($this->upload->do_upload('file')) {

					$nombre_archivo_temp = $nuevoNOmbre . '_splitter.' . $arc[1];

					$destino = $nombre_fichero  . "/" . $nombre_archivo_temp;

					$this->load->library('pdf_lib');

					$pdf = $this->pdf_lib->test($this->upload->data('full_path'), $destino);

					if (!file_exists(strtolower($nombre_fichero . '/' . $nuevoNOmbre))) {
					}
					try {
						//actrualizo tabla lotes cantidad de archivos
						if (isset($_REQUEST['id_lote'])) {

							$dataLote = array(
								'cant' => $_REQUEST['cant']
							);

							$this->db->where('id', $_REQUEST['id_lote']);
							$this->db->update('_lotes', $dataLote);
						}
					} catch (Exception $e) {

						// this will not catch DB related errors. But it will include them, because this is more general. 
						echo $e->getMessage();
						$response = array(
							'mensaje' => $e->getMessage(),
							'title' => 'Consulta API 218',
							'status' => 'error',
						);
						echo json_encode($response);
						exit();
					}

					$uploadData = $this->upload->data();

					$filename = $uploadData['file_name'];
					$fullpath = $uploadData['full_path'];

					$nombre_archivodb = $config['upload_path'] . '/' . $config['file_name'];

					$lote = $this->Lotes_model->crearLote();

					$texto = ' ';
					$saveData = array(
						'id_lote' => $lote[0]->id,
						'code_lote' => $lote[0]->code,
						'id_proveedor' => $proveedor->id,
						'nombre_proveedor' => $proveedor->nombre,
						'nombre_archivo' => $nombre_archivodb,
						'nombre_archivo_temp' => $destino,
					);

					if ($this->Manager_model->grabar_datos("_datos_api", $saveData)) {


						$response = array(
							'mensaje' => 'Archivo: ' . $filename . ' Lote: ' . $lote[0]->code,
							'title' => 'Grabar Archivos',
							'status' => 'success',
							// 'file' => $filename,
							'file' => $filename,
							'path' => $fullpath,
							'pathw' => $destino,
						);
						echo json_encode($response);
						exit();
					} else {
						$response = array(
							'mensaje' => 'Archivo: ' . $filename . ' Lote: ' . $lote[0]->code,
							'title' => 'Grabar Archivos',
							'status' => 'error',
						);
						echo json_encode($response);
						exit();
					}
				} else {
					// echo 'error ->' . $this->upload->display_errors();
					$data['response'] = 'failed';
					$response = array(
						'mensaje' => 'Archivo: ' . $this->upload->display_errors() . '<br>' . $nombre_archivodb . ' Lote: ' . $_POST['code_lote'],
						'title' => 'Grabar Archivos',
						'status' => 'error',
					);

					echo json_encode($response);
					exit();
				}
			} else {
				$response = array(
					'mensaje' => 'Archivo: vacio',
					'title' => 'Grabar ',
					'status' => 'error',
				);

				echo json_encode($response);
				exit();
			}
		}

		// ACA LLEGA NORMAL

		if (!empty($_POST['id_proveedor'])) {


			$script = array(
				base_url('assets/manager/js/plugins/tables/datatables/datatables.min.js'),
				base_url('assets/manager/js/plugins/dropzone.min.js'),
				//			base_url('assets/manager/js/plugins/tables/datatables/datatables.min.js'),
				//			base_url('assets/manager/js/plugins/tables/datatables/datatables_advanced.js'),
				base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
				base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
				// base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/datatable.js'),
			);


			$this->data['css_common'] = $this->css_common;
			$this->data['css'] = '';

			$this->data['script_common'] = $this->script_common;
			$this->data['script'] = $script;

			$this->data['proveedor'] = $this->Manager_model->get_data('_proveedores', $_POST['id_proveedor']);


			$this->form_validation->set_rules('id_proveedor', 'proveedor', 'callback_url_check');


			if ($this->form_validation->run() == FALSE) {
				redirect('Admin/Lecturas');
			}

			$hoy = getdate();

			$code = substr(str_replace(array('=', '-', '/s'), '', $this->encrypt->encode($hoy[0])), 0, 6);

			$this->data['code'] = urlencode($code);

			$this->data['dropzone'] = $this->load->view('manager/etiquetas/dropzone', $this->data, TRUE);
			$this->data['content'] = $this->load->view('manager/secciones/lotes/' . $this->router->fetch_method(), $this->data, TRUE);
			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);
		} else {
			die('aca 216');
		}
	}

	public function proveedores($id = null)
	{
		$script = array(
			base_url('assets/manager/js/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method() . '.js'),
		);
		$this->data['script'] = $script;
	}
}
