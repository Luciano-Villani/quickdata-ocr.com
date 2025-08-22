<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lecturas extends backend_controller
{
	function __construct()
	{
		parent::__construct();
		// if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_super() || $this->ion_auth->is_members()) {
		if (!$this->ion_auth->logged_in()) {
			redirect('Login');
		} else {
			$this->load->helper('file');
			$this->load->model('manager/Lecturas_model');
			$this->load->model('manager/Uploader_model');
			$this->load->model('manager/Manager_model');
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
		// $this->Lecturas_model->list_dt($id);
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
        $config['upload_path'] = $uploadPath;
        $config['allowed_types'] = '*';
        $config['max_size'] = '1024'; // max_size in kb 
        $config['file_name'] = $_FILES['file']['name'];

        // Load upload library 
        $this->load->library('upload', $config);

        // File upload
        if ($this->upload->do_upload('file')) {
            // Get data about the file
            $uploadData = $this->upload->data();

            // Obtener proveedor seleccionado
            $proveedorId = $this->input->post('id_proveedor');
            $proveedor = $this->manager_model->obtener_contenido_select('_proveedores')[$proveedorId];
			// Debug: Verificar valor de procesar_por
			// Log para verificar valor de procesar_por
			log_message('error', 'Valor de procesar_por: ' . var_export($proveedor['procesar_por'], true));
			echo "Logging realizado"; // Para verificar que la línea se ejecuta
            // Determinar a qué sistema enviar el archivo
            if ($proveedor['procesar_por'] === 'azure') {
                // Llamada a apiRest para Azure
                $data = apiRest($uploadData, 'azure');
            } else {
                // Llamada a apiRest para Mindee (u otros)
                $data = apiRest($uploadData);
            }

            // Procesar la respuesta de la API
            $direccion = array_column($data['document']['inference']['pages'][0]['prediction']['direccion']['values'], 'content');
            $titular = array_column($data['document']['inference']['pages'][0]['prediction']['titular']['values'], 'content');

            $cadena_titular = implode(" ", $titular);
            $cadena_direccion = implode(" ", $direccion);

            echo '<br>' . $cadena_titular . '<br>' . $cadena_direccion;

            $filename = $uploadData['file_name'];
            $data['response'] = 'successfully uploaded ' . $filename;
        } else {
            // Handle upload error
            echo '<pre>';
            var_dump($this->upload->display_errors());
            echo '</pre>';
            die();
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

public function copy($id = 0)
{
    // Bloque para solicitudes AJAX
    if ($this->input->is_ajax_request()) {
        // Configurar respuesta como JSON
        header('Content-Type: application/json');
        
        try {
            // 1. Validación y preparación de datos
            $id_registro = trim($this->input->post('id_registro'));
            $nro_cuenta = trim(str_replace(' ', '', $this->input->post('nro_cuenta')));
            $total = $this->input->post('total');
            
            if (empty($id_registro) || empty($nro_cuenta) || empty($total)) {
                throw new Exception('Datos incompletos para procesar la solicitud');
            }

            // 2. Obtener datos principales
            $datoleido = $this->Manager_model->get_data_api('_datos_api', $id_registro, true);
            if (!$datoleido) {
                throw new Exception('Registro no encontrado en la base de datos');
            }

            // 3. Validar duplicados
            $this->db->where('nro_factura', $datoleido->nro_factura);
            $this->db->where('nro_cuenta', $nro_cuenta);
            $existe = $this->db->count_all_results('_datos_api') > 0;
            
            if ($existe) {
                throw new Exception('Esta cuenta ya está registrada para la factura '.$datoleido->nro_factura);
            }

            // 4. Procesar transacción
            $this->db->trans_start();
            
            $datoTotalesMultiple = $this->Manager_model->get_alldata('_datos_multiple', 'id_datos_api="'.$id_registro.'"');
            
            if (empty($datoTotalesMultiple)) {
                // Insertar en _datos_multiple
                $this->db->insert('_datos_multiple', [
                    'id_datos_api' => $datoleido->id,
                    'importe_1' => $total,
                    'total_importe' => $total,
                    'nro_factura' => $datoleido->nro_factura
                ]);
                
                // Actualizar registro principal
                $this->db->where('id', $id_registro);
                $this->db->update('_datos_api', [
                    'total_importe' => $total,
                    'importe_1' => $total,
                    'nro_cuenta' => $nro_cuenta
                ]);
            } else {
                // Crear copia del registro
                unset($datoleido->id);
                $datoleido->nro_cuenta = $nro_cuenta;
                $datoleido->total_importe = $total;
                $datoleido->importe_1 = $total;
                $this->db->insert('_datos_api', (array)$datoleido);
            }
            
            $this->db->trans_complete();

            // 5. Obtener datos actualizados para la respuesta
            $registro_factura = $this->Manager_model->get_alldata('_datos_api', 'nro_factura = "'.$datoleido->nro_factura.'"');
            
            // Extraer total factura del JSON de forma robusta
            $a = json_decode($registro_factura[0]->dato_api);
            $totalFacturaJson = '0.00';
            
            if ($a && is_array($a) && !empty($a)) {
                $primerItem = $a[0];
                
                if (isset($primerItem->fields->total_importe)) {
                    $totalData = $primerItem->fields->total_importe;
                    
                    if (isset($totalData->content)) {
                        // Formatear valor (ej: "204.151,20" => 204151.20)
                        $totalFacturaJson = str_replace(['.', ','], ['', '.'], $totalData->content);
                    } elseif (isset($totalData->valueNumber)) {
                        $totalFacturaJson = $totalData->valueNumber;
                    }
                    
                    $totalFacturaJson = number_format($totalFacturaJson, 2, '.', '');
                }
            }

            // Calcular total ingresado
            $resultIngresado = 0;
            foreach ($registro_factura as $reg) {
                $resultIngresado += $reg->importe_1;
            }

            // 6. Generar vista parcial
            $html = $this->load->view('manager/etiquetas/lineas', [
                'lineas' => $registro_factura,
                'totalFactura' => $totalFacturaJson,
                'resultIngresado' => $resultIngresado
            ], TRUE);

            // 7. Retornar respuesta exitosa
            echo json_encode([
                'status' => 'success',
                'html' => $html,
                'totalFactura' => $totalFacturaJson,
                'resultIngresado' => $resultIngresado
            ]);
            exit();

        } catch (Exception $e) {
            // Registrar error y devolver mensaje
            log_message('error', 'Error en copy(): '.$e->getMessage());
            
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit();
        }
    }

    // Bloque para solicitudes POST normales (no AJAX)
    if ($id == 0 && $_SERVER['REQUEST_METHOD'] === "POST") {
        $this->form_validation->set_rules('proveedor', 'Proveedor', 'trim|in_select[0]');
        $this->form_validation->set_rules('nro_cuenta', 'Cuenta', 'trim|required');
        $this->form_validation->set_rules('nro_factura', 'Factura', 'trim|required');
        $this->form_validation->set_rules('fecha_emision', 'Fecha emisión', 'trim|required');
        $this->form_validation->set_rules('total_importe', 'Importe', 'trim|required');

        if ($this->form_validation->run() != FALSE) {
            $id = $this->input->post('id');
            $post_data = $this->input->post();
            unset($post_data['id']);

            // Procesamiento de fechas
            $post_data['vencimiento_del_pago'] = $this->parseDate($post_data['vencimiento_del_pago']);
            $post_data['fecha_emision'] = $this->parseDate($post_data['fecha_emision']);

            if ($this->db->update('_datos_api', $post_data, ['id' => $id])) {
                redirect('Admin/Lecturas/copy/'.$id);
            }
        }
    }

    // Bloque para mostrar la vista normal
    $myDato = $id;
    $datoleido = $this->Manager_model->get_data_api('_datos_api', $myDato, true);

    // Extraer total factura para la vista normal
    $totalFacturaJson = '0.00';
    if ($datoleido) {
        $a = json_decode($datoleido->dato_api);
        
        if ($a && is_array($a) && !empty($a)) {
            $primerItem = $a[0];
            
            if (isset($primerItem->fields->total_importe)) {
                $totalData = $primerItem->fields->total_importe;
                
                if (isset($totalData->content)) {
                    $totalFacturaJson = str_replace(['.', ','], ['', '.'], $totalData->content);
                } elseif (isset($totalData->valueNumber)) {
                    $totalFacturaJson = $totalData->valueNumber;
                }
                
                $totalFacturaJson = number_format($totalFacturaJson, 2, '.', '');
            }
        }
    }

    // Obtener datos para la vista
    $datoTotalesMultiple = $this->Manager_model->get_alldata('_datos_multiple', 'id_datos_api="'.$myDato.'"');
    $importe_total = !empty($datoTotalesMultiple) ? $datoTotalesMultiple[0]->total_importe : 0.00;

    $registro_facturas = $this->Manager_model->get_alldata('_datos_api', 'nro_factura = "'.$datoleido->nro_factura.'"');
    $resultIngresado = array_sum(array_column($registro_facturas, 'importe_1'));

    // Configurar assets
    $this->data['css_common'] = $this->css_common;
    $this->data['script_common'] = $this->script_common;
    $this->data['script'] = [base_url('assets/manager/js/secciones/lecturas/copy.js?ver='.time())];
    $this->data['result'] = $datoleido;

    // Preparar datos para la vista
    $this->data['lineas'] = $this->load->view('manager/etiquetas/lineas', [
        'lineas' => $registro_facturas,
        'result' => $datoleido,
        'resultMulti' => $importe_total,
        'totalFactura' => $totalFacturaJson,
        'resultIngresado' => $resultIngresado
    ], TRUE);

    $this->data['importe_total'] = $importe_total;
    
    if ($datoleido) {
        $this->data['indexaciones'] = $this->Indexaciones_model->get_indexaciones($datoleido->nro_cuenta);
    }

    // Cargar vistas
    $this->data['content'] = $this->load->view('manager/secciones/'.strtolower($this->router->fetch_class()).'/'.$this->router->fetch_method(), $this->data, TRUE);
    $this->load->view('manager/head', $this->data);
    $this->load->view('manager/index', $this->data);
    $this->load->view('manager/footer', $this->data);
}

// Función auxiliar para parseo de fechas
protected function parseDate($dateString)
{
    $timestamp = strtotime($dateString);
    return ($timestamp === false) ? 'error de lectura' : date('Y-m-d', $timestamp);
}


	public function resetfile()
{
    if ($this->input->is_ajax_request()) {
        $id_registro = trim($_REQUEST['id']);
        $datoleido = $this->Manager_model->get_data_api('_datos_api', $id_registro, true);
        
        // Verifica si el registro principal existe
        if (!$datoleido) {
            echo json_encode(array('result' => false, 'message' => 'Registro principal no encontrado.'));
            return;
        }
        
        $a = json_decode($datoleido->dato_api);

        // INICIALIZACIÓN DE VARIABLES para evitar NULL en la DB
        $total_importe = '0.00';
        $nro_cuenta = 'S/D';

        // Lógica de lectura segura del JSON para 'total_importe'
        if (isset($a->document->inference->pages[0]->prediction->total_importe)) {
            $total_importe_obj = $a->document->inference->pages[0]->prediction->total_importe;
            
            if (isset($total_importe_obj->values) && is_array($total_importe_obj->values) && count($total_importe_obj->values) > 0) {
                $total_importe = $total_importe_obj->values[0]->content;
            } else {
                $total_importe = $total_importe_obj->content ?? '0.00';
            }

            // Normalización del formato numérico (reemplaza coma por punto)
            $total_importe = str_replace('.', '', $total_importe);
            $total_importe = str_replace(',', '.', $total_importe);
        }

        // Lógica de lectura segura del JSON para 'nro_cuenta'
        if (isset($a->document->inference->pages[0]->prediction->nro_cuenta)) {
            $nro_cuenta_obj = $a->document->inference->pages[0]->prediction->nro_cuenta;
            
            if (isset($nro_cuenta_obj->values) && is_array($nro_cuenta_obj->values)) {
                $nro_cuenta = ''; // Reinicia la variable para concatenar
                foreach ($nro_cuenta_obj->values as $value) {
                    $nro_cuenta .= $value->content;
                }
            } else {
                $nro_cuenta = $nro_cuenta_obj->content ?? 'S/D';
            }
        }

        $hizo = false;
        $this->db->trans_start();

        // 1. Eliminar los registros duplicados asociados a la factura
        $this->db->where('nro_factura', $datoleido->nro_factura);
        $this->db->where('id !=', $id_registro);
        $this->db->delete('_datos_api');

        // 2. Actualizar el registro principal con los datos originales del JSON
        $reloadData = array(
            'total_importe' => $total_importe,
            'importe_1' => $total_importe,
            'nro_cuenta' => $nro_cuenta
        );

        $this->db->where('id', $id_registro);
        $this->db->update('_datos_api', $reloadData);

        // 3. Eliminar los registros de la tabla '_datos_multiple'
        $this->db->where('id_datos_api', $id_registro);
        $this->db->delete('_datos_multiple');

        $this->db->trans_complete();

        $hizo = $this->db->trans_status();

        $result = array(
            'result' => $hizo,
            'message' => $hizo ? 'El registro se ha reseteado correctamente.' : 'Hubo un error al resetear el registro.'
        );

        echo json_encode($result);
    }
}
	public function views($id = 0)
	{
		// $myDato = $this->encrypt->decode(urldecode($id));
		$myDato = $id;

		if ($id == 0 && $_SERVER['REQUEST_METHOD'] === "POST") {



			// // $_POST['fecha_emision']  = date(trim('Y-m-d',$_POST['fecha_emision']));
			// $_POST['fecha_emision']  = fecha_es(trim($_POST['fecha_emision']), 'Y-m-d', false);
			// $_POST['vencimiento_del_pago']  = fecha_es(trim($_POST['vencimiento_del_pago']), 'Y-m-d', false);


			$myDato = $_POST['id'];
			$this->form_validation->set_rules('proveedor', 'Proveedor', 'trim|in_select[0]');
			$this->form_validation->set_rules('nro_cuenta', 'Cuenta', 'trim|required');

			//$this->form_validation->set_rules('nro_medidor', 'Medidor', 'trim|required');
			$this->form_validation->set_rules('nro_factura', 'Factura', 'trim|required');
			// $this->form_validation->set_rules('periodo_del_consumo', 'Período', 'trim|required');
			$this->form_validation->set_rules('fecha_emision', 'Fecha emisión', 'trim|required');
			$this->form_validation->set_rules('total_importe', 'Importe', 'trim|required');

			if ($this->form_validation->run() != FALSE) {
				$id = $_REQUEST['id'];
				unset($_REQUEST['id']);

				//campo fecha vencimiento

				// $timestamp = strtotime(trim($_REQUEST['vencimiento_del_pago']) );

				if (($timestamp = strtotime($_REQUEST['vencimiento_del_pago'])) === false) {
					$_REQUEST['vencimiento_del_pago'] = 'error de lectura';
				} else {
					$_REQUEST['vencimiento_del_pago'] = date('Y-m-d', $timestamp);
				}

				//campo fecha_emision
				// $timestamp = strtotime(trim($_REQUEST['fecha_emision']) );
				if (($timestamp = strtotime($_REQUEST['fecha_emision'])) === false) {
					$_REQUEST['fecha_emision'] = 'error de lectura';
				} else {
					$_REQUEST['fecha_emision'] = date('Y-m-d', $timestamp);
				}

				if ($this->db->update('_datos_api', $_REQUEST, array('id' => $_POST['id']))) {


					redirect('Admin/Lecturas/Views/' . $id);
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

	function split_pdf($filename, $dir = false)
	{
		require_once(APPPATH . 'third_party/pdf2/fpdf.php');
		require_once(APPPATH . 'third_party/pdf2/fpdi.php');

		$data = $this->Manager_model->getWhere('_datos_api', "id=315");


		$dir = $dir ? $dir : './';
		$filename = base_url($data->nombre_archivo);

		$pdf = new FPDI();
		$pagecount = $pdf->setSourceFile($filename);


		echo '<pre>';
		var_dump($pagecount);
		echo '</pre>';
		die();

		// Split each page into a new PDF
		for ($i = 1; $i <= $pagecount; $i++) {
			$new_pdf = new FPDI();
			$new_pdf->AddPage();
			$new_pdf->setSourceFile($filename);
			$new_pdf->useTemplate($new_pdf->importPage($i));
			try {
				$new_filename = $dir . str_replace('.pdf', '', $filename) . '_' . $i . ".pdf";
				$new_pdf->Output($new_filename, "F");
				echo "Page " . $i . " split into " . $new_filename . "<br />\n";
			} catch (Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
		}
	}
	public function listados()
	{
		$script = array(
			base_url('assets\manager\js\plugins\tables\datatables\extensions/select.min.js'),
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



	public function azureApi() {


$curl = curl_init();

// Cargar el archivo PDF
$data = file_get_contents('C:/electro.pdf');

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://mvl.cognitiveservices.azure.com/formrecognizer/documentModels/electro_t1:analyze?api-version=2023-07-31&features=queryFields&queryFields=nro_de_factura,fecha_emision,nro_cuenta,nombre_cliente,vencimiento_del_pago',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HEADER => true,  // Incluir las cabeceras en la salida
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/pdf',
    'Ocp-Apim-Subscription-Key: a49c210e168941658db7c33e33218733'
  ),
));

$response = curl_exec($curl);

if (curl_errno($curl)) {
    echo 'Curl error: ' . curl_error($curl);
} else {
    // Separar las cabeceras del cuerpo de la respuesta
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    // Mostrar cabeceras para ver que onda
    echo "Cabeceras de respuesta:\n$header\n";
    echo "Cuerpo de respuesta:\n$body\n";

    // Buscar la URL de Operation-Location en las cabeceras para hacer el get
    if (preg_match('/Operation-Location:\s*(.+)\r\n/', $header, $matches)) {
        $operation_url = trim($matches[1]);

        // Realizar solicitudes GET hasta obtener un resultado final
        do {
            sleep(5); // Esperar 5 segundos entre consultas

            curl_setopt_array($curl, array(
              CURLOPT_URL => $operation_url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_HEADER => true,  // Incluir las cabeceras en la salida
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array(
                'Ocp-Apim-Subscription-Key: a49c210e168941658db7c33e33218733'
              ),
            ));
            
            $result = curl_exec($curl);

            if (curl_errno($curl)) {
                echo 'Curl error: ' . curl_error($curl);
                break;
            } else {
                // Separar las cabeceras del cuerpo de la respuesta GET
                $header_size_get = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
                $header_get = substr($result, 0, $header_size_get);
                $body_get = substr($result, $header_size_get);

                echo "Respuesta de la solicitud GET (cabeceras):\n$header_get\n";
                echo "Respuesta de la solicitud GET (cuerpo):\n$body_get\n";

                $result_data = json_decode($body_get, true);

                // Verifica si el resultado está vacío o si la clave 'status' si no existe
                if (is_null($result_data) || !isset($result_data['status'])) {
                    echo "Error: No se pudo obtener el estado de la operación.\n";
                    var_dump($result_data); 
                    break;
                }

                echo "Estado: " . $result_data['status'] . "\n";
            }
        } while ($result_data['status'] === 'running'); // Continuar mientras el estado sea "running"

        // mostrar el el result
        echo "Resultado de la operación:\n";
        var_dump($result_data);
    } else {
        echo "No se encontró la cabecera Operation-Location en la respuesta.\n";
    }
}

curl_close($curl);


		


	}








public function azureGet(){

	$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://mvl.cognitiveservices.azure.com/formrecognizer/documentModels/electro_t1/analyzeResults/5180059a-90c7-414f-af9b-4f51495c3ac4?api-version=2023-07-31',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Ocp-Apim-Subscription-Key: a49c210e168941658db7c33e33218733'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
var_dump($response);
}

}


