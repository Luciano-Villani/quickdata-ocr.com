<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lecturas extends backend_controller
{
	function __construct()
	{
		parent::__construct();


		$this->load->helper('file');
		if (!$this->ion_auth->logged_in()) {
			redirect('Login');
		} else {

			$this->load->model('manager/Lecturas_model');
			$this->load->model('manager/Uploader_model');
		}
	}

	public function Consolidar()
	{
		if ($this->input->is_ajax_request()) {
			$return = $this->Consolidados_model->consolidar_datos($_POST);
			echo json_encode($return);
		}
	}
	public function cerrarLote()
	{
		// Actualizacion tabla _lotes


		$data = array(
			'cant' => $_POST['cant'],
			'status' => 1,

		);
		$data['user_add'] = $this->user->id;
		$this->db->where('id', $_POST['id_lote']);
		$this->db->update('_lotes', $data);
		echo json_encode(array('data' => 'OK'));
	}
	public function getInfoPanel($data = null)
	{


		$lote = $this->Manager_model->crearLote();



		echo json_encode($this->load->view('manager/etiquetas/panel', $lote, TRUE));
	}

	public function checkFile()
	{



		$proveedor = $this->Manager_model->get_data('_proveedores', $_POST['id_proveedor']);
		$nombre_fichero = 'uploader/files/' . strtolower($proveedor->codigo) . '/' . $_POST['name'];

		if (file_exists($nombre_fichero)) {
			// echo 'The file "'.$nombre_fichero.'/'.$_FILES['file']['name'].'" exists.';die();
			$data['response'] = 'El archivo ya existe -' . urlencode($_POST['name']);
			$data['status'] = 'error';
			$grabar_datos_array = array(
				'seccion' => 'Lectura de Documentos',
				'mensaje' => 'El archivo ya existe - ' . $_POST['name'],
				'estado' => 'error',
			);

			$this->session->set_userdata('save_data', $grabar_datos_array);
			$response = array(
				"status" => 'error'
			);
			echo json_encode($response);
			// $this->session->unset_userdata('save_data');
		} else {
			$response = array(
				"status" => 'success'
			);
			echo json_encode($response);
		};
	}
	public function url_check()
	{
		if ($this->data['proveedor']->urlapi === "") {
			return false;
		}
		return TRUE;
	}

	public function list_dt($id = null)
	{

		$this->Lecturas_model->list_dt($id);
	}
	public function lotes_dt($id = null)
	{


		if ($this->input->is_ajax_request()) {
			$this->Lecturas_model->lotes_dt($id);
		}
	}

	public function uploads()
	{

		$data = array();
		if (!empty($_FILES['file']['name'])) {
			// Set preference 
			$uploadPath = 'uploader/files/';
			$config["remove_spaces"] = TRUE;
			$config["overwrite"] = TRUE;
			$config['upload_path'] = 'uploader/files';
			//    $config['allowed_types'] = 'jpg|jpeg|png|gif'; 
			$config['allowed_types'] = '*';
			$config['max_size'] = '1024'; // max_size in kb 
			$config['file_name'] = $_FILES['file']['name'];

			// Load upload library 
			$this->load->library('upload', $config);

			// File upload
			if ($this->upload->do_upload('file')) {
				// Get data about the file
				$uploadData = $this->upload->data();

				sleep(2);
				$data = apiRest($uploadData);

				//  echo '<pre>DATA';
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

				$filename = $uploadData['file_name'];
				$data['response'] = 'successfully uploaded ' . $filename;
			} else {

				echo '<pre>';
				var_dump($this->upload->display_errors());
				echo '</pre>';
				die();
				echo 'error ->' . $this->upload->display_errors();
				$data['response'] = 'failed';
			}
		} else {
			$data['response'] = 'failed';
		}
		echo json_encode($data);
	}
	public function indexaciones_dt($nro_cuenta = null)
	{

		$my_nro_cuenta = urldecode($_POST['nro_cuenta']);

		$datps = $this->Indexaciones_model->get_indexaciones($my_nro_cuenta);

		echo $datps;
	}
	public function views($id = 0)
	{


		// $myDato = $this->encrypt->decode(urldecode($id));
		$myDato = $id;

		if ($id == 0 && $_SERVER['REQUEST_METHOD'] === "POST") {



			// $_POST['fecha_emision']  = date(trim('Y-m-d',$_POST['fecha_emision']));
			$_POST['fecha_emision']  = fecha_es(trim($_POST['fecha_emision']), 'Y-m-d', false);
			$_POST['vencimiento_del_pago']  = fecha_es(trim($_POST['vencimiento_del_pago']), 'Y-m-d', false);

			$myDato = $_POST['id'];
			$this->form_validation->set_rules('proveedor', 'Proveedor', 'trim|in_select[0]');
			$this->form_validation->set_rules('nro_cuenta', 'Cuenta', 'trim|required');
			$this->form_validation->set_rules('nro_medidor', 'Medidor', 'trim|required');
			$this->form_validation->set_rules('nro_factura', 'Factura', 'trim|required');
			// $this->form_validation->set_rules('periodo_del_consumo', 'Período', 'trim|required');
			$this->form_validation->set_rules('fecha_emision', 'Fecha emisión', 'trim|required');
			$this->form_validation->set_rules('total_importe', 'Importe', 'trim|required');

			if ($this->form_validation->run() != FALSE) {
				$id = $_REQUEST['id'];
				unset($_REQUEST['id']);
				if ($this->db->update('_datos_api', $_REQUEST, array('id' => $_POST['id']))) {


					redirect('Admin/Lecturas/Views/'.$id);
				};
			}
		}


		$registro_api = $this->Manager_model->get_data_api('_datos_api', $myDato);

		$script = array(
			base_url('assets/manager/js/secciones/lecturas/views.js?ver=' . time()),
		);
		$this->data['select_proveedores'] = $this->Manager_model->obtener_contenido_select('_proveedores', 'SELECCIONE PROVEEDOR', 'nombre', 'id ASC');
		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;
		$this->data['result'] = $registro_api;


		// $this->data['nro_cuenta'] = $resultData->nro_cuenta;

		if ($registro_api) {
			$this->data['indexaciones'] = $this->Indexaciones_model->get_indexaciones($registro_api->nro_cuenta);
		}


		$this->data['content'] = $this->load->view('manager/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method(), $this->data, TRUE);

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
	}
	function send()
	{
		// Load PHPMailer library
		$this->load->library('phpmailer_lib');

		// PHPMailer object
		$mail = $this->phpmailer_lib->load();
		// SMTP configuration
		//$mail->isSMTP();
		$mail->Host     = 'smtp.hostinger.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'no-reply@quickdata.com.ar';
		$mail->Password = '487Racatengarospidea!';
		// $mail->SMTPSecure = 'ssl';
		$mail->SMTPSecure = 'tls';
		$mail->Port     = 465;
		$mail->Port     = 587;

		$mail->setFrom('mvl@quickdata.com.ar', 'CodexWorld');
		$mail->addReplyTo('mvl@quickdata.com.ar', 'CodexWorld');

		// Add a recipient
		$mail->addAddress('tutinocarlos@gmail.com');

		// Add cc or bcc 
		// $mail->addCC('cc@example.com');
		// $mail->addBCC('bcc@example.com');

		// Email subject
		$mail->Subject = 'Send Email via SMTP using PHPMailer in CodeIgniter';

		// Set email format to HTML
		$mail->isHTML(true);

		// Email body content
		$mailContent = "<h1>Send HTML Email using SMTP in CodeIgniter</h1>
				<p>This is a test email sending using SMTP mail server with PHPMailer.</p>";
		$mail->Body = $mailContent;

		// Send email
		if (!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
			die();
		} else {
			echo 'Message has been sent';
			die();
		}
	}
	public function listados()
	{


		$script = array(

			base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
			base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
		);


		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;
		$this->data['select_proveedores'] = $this->Manager_model->obtener_contenido_select('_proveedores', 'SELECCIONE PROVEEDOR', 'nombre', 'id ASC');
		$this->data['dropzone'] = $this->load->view('manager/etiquetas/dropzone', $this->data, TRUE);

		$hoy = getdate();
		$code = substr(str_replace(array('=', '-'), '', $this->encrypt->encode($hoy[0])), 0, -22);

		$this->data['code'] = $code;
		$this->data['content'] = $this->load->view('manager/secciones/lecturas/' . $this->router->fetch_method(), $this->data, TRUE);

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

	public function test($filename = 'uploader/files/3857/3870979451_06_2023_factura_08_01_2024-07_53_11.pdf')
	{


		$pdf = new PDFMerger; // or use $pdf = new \PDFMerger; for Laravel

		$pdf->addPDF($filename, '1');
		$pdf->merge('file', 'samplepdfs/TEST2.pdf'); // generate the file

		$pdf->merge('download', 'samplepdfs/test.pdf'); // force download

	}
}
