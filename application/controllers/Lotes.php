<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lotes extends backend_controller
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

	public function getDato($file, $id_proveedor)
	{

		// $dato = $this->Manager_model->getWhere('_datos_api', 'nombre_archivo="' . $id . '"');
		// $this->db->where("nombre_archivo LIKE '%" . $file . "%' ESCAPE '!'");

		$query = "SELECT id, dato_api FROM _datos_api WHERE nombre_archivo = '" . $file . "'";
		$valor = $file;

		// Escapar el valor usando escape() de la clase db
		$valor_escapado = $valor;

		// Ejecutar la consulta con el valor escapado
		$resultado = $this->db->query($query, array($valor_escapado));
		$mires = $resultado->result();

		// Procesar el resultado
		if ($resultado) {

			$a = json_decode($mires[0]->dato_api);

			switch ($id_proveedor) {
				case 1: // ASYSA 3232

					$totalIndices = count($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values);
					$fecha_emision = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$fecha_emision .= trim($a->document->inference->pages[0]->prediction->fecha_emision->values[$paso]->content);
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values);
					$vencimiento_del_pago = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$vencimiento_del_pago .= trim($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[$paso]->content);
					}
					// echo '<pre>';
					// echo $fecha_emision;
					// echo '<pre>';
					// echo fecha_es($fecha_emision,'Y-m-d'); 
					// echo '</pre>';
					// // die();
					$nro_cuenta = '';
					//calculo indices para el periodo
					$totalIndices = count($a->document->inference->pages[0]->prediction->periodo_del_consumo->values);
					$periodo_del_consumo = '';
					$monto_subsidio = '';
					$consumo = '';

					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[$paso]->content;
					}

					if ($a->document->inference->pages[0]->prediction->nro_medidor->values) {
						$medidor = $a->document->inference->pages[0]->prediction->nro_medidor->values[0]->content;
					} else {
						$medidor = 'N/A';
					}
					if ($a->document->inference->pages[0]->prediction->monto_subsidio->values) {
						$monto_subsidio = $a->document->inference->pages[0]->prediction->monto_subsidio->values[0]->content;
					} else {
						$monto_subsidio = 'N/A';
					}
					if ($a->document->inference->pages[0]->prediction->consumo->values) {
						$consumo = $a->document->inference->pages[0]->prediction->consumo->values[0]->content;
					} else {
						$consumo = 'N/A';
					}
					$dataUpdate = array(
						'nro_cuenta' => trim($a->document->inference->pages[0]->prediction->nro_cuenta->values[0]->content),
						'nro_medidor' => trim($medidor),
						'nro_factura' => trim($a->document->inference->pages[0]->prediction->nro_factura->values[0]->content),
						'periodo_del_consumo' => trim($periodo_del_consumo),
						'fecha_emision' => trim($fecha_emision),
						'vencimiento_del_pago' => trim($vencimiento_del_pago),
						'total_importe' => trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content),
						'total_vencido' => trim($a->document->inference->pages[0]->prediction->total_vencido->values[0]->content),
						'monto_subsidio' => trim($monto_subsidio),
						'consumo' => trim($consumo),
					);

					break;
				case 2: //NATURGY 4399


					$totalIndices = count($a->document->inference->pages[0]->prediction->periodo_del_consumo->values);
					$periodo_del_consumo = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[$paso]->content;
					}
					if ($a->document->inference->pages[0]->prediction->nro_medidor->values) {
						$medidor = $a->document->inference->pages[0]->prediction->nro_medidor->values[0]->content;
					} else {
						$medidor = 'N/A';
					}
					if ($a->document->inference->pages[0]->prediction->nro_cuenta->values) {
						$nro_cuenta = $a->document->inference->pages[0]->prediction->nro_cuenta->values[0]->content;
					} else {
						$nro_cuenta = 'N/A';
					}
					if ($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values) {
						$vencimiento_del_pago = $a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[0]->content;
					} else {
						$vencimiento_del_pago = 'N/A';
					}
					if ($a->document->inference->pages[0]->prediction->consumo->values) {
						$consumo = $a->document->inference->pages[0]->prediction->consumo->values[0]->content;
					} else {
						$consumo = 'N/A';
					}

					$dataUpdate = array(
						'nro_cuenta' => trim($nro_cuenta),
						'nro_medidor' => trim($medidor),
						'nro_factura' => trim($a->document->inference->pages[0]->prediction->nro_factura->values[0]->content),
						'periodo_del_consumo' => trim($periodo_del_consumo),
						'fecha_emision' => trim($a->document->inference->pages[0]->prediction->fecha_emision->values[0]->content),
						'vencimiento_del_pago' => trim($vencimiento_del_pago),
						'total_importe' => trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content),
						'total_vencido' => trim($a->document->inference->pages[0]->prediction->total_vencido->values[0]->content),
						'consumo' => trim($consumo),
					);



					break;

				case 3: //FLOW 3480

					$totalIndices = count($a->document->inference->pages[0]->prediction->nro_cuenta->values);
					$nro_cuenta = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_cuenta .= $a->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
					}
					$numero_de_factura = '';
					$totalIndices = count($a->document->inference->pages[0]->prediction->numero_de_factura->values);
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$numero_de_factura .= $a->document->inference->pages[0]->prediction->numero_de_factura->values[$paso]->content;
					}

					$fecha_emision = '';
					$totalIndices = count($a->document->inference->pages[0]->prediction->fecha_emision->values);
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$fecha_emision .= $a->document->inference->pages[0]->prediction->fecha_emision->values[$paso]->content;
					}

					$total_importe = '';
					$totalIndices = count($a->document->inference->pages[0]->prediction->total_importe->values);
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$total_importe .= $a->document->inference->pages[0]->prediction->total_importe->values[$paso]->content;
					}


					$vencimiento_del_pago = '';
					$totalIndices = count($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values);
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$vencimiento_del_pago .= $a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[$paso]->content;
					}



					$periodo_del_consumo = '';
					$totalIndices = count($a->document->inference->pages[0]->prediction->periodo_del_consumo->values);
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[$paso]->content;
					}


					$dataUpdate = array(
						'nro_cuenta' => trim($nro_cuenta),
						'nro_factura' => trim($numero_de_factura),
						'fecha_emision' => trim($fecha_emision),
						'total_importe' => trim($total_importe),
						'vencimiento_del_pago' => trim($vencimiento_del_pago),
						'periodo_del_consumo' => trim($periodo_del_consumo),
						// 'nro_medidor' => trim('N/A'),
						// 'total_vencido' => trim($total_vencido),
						// 'consumo' => trim($consumo),
					);


					break;

				case 4: //3857 EDENOR

					$totalIndices = count($a->document->inference->pages[0]->prediction->periodo_del_consumo->values);
					$periodo_del_consumo = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[$paso]->content;
					}
					if ($a->document->inference->pages[0]->prediction->nro_medidor->values) {
						$medidor = $a->document->inference->pages[0]->prediction->nro_medidor->values[0]->content;
					} else {
						$medidor = 'N/A';
					}

					$dataUpdate = array(
						'nro_cuenta' => trim($a->document->inference->pages[0]->prediction->nro_cuenta->values[0]->content),
						'nro_medidor' => trim($medidor),
						'nro_factura' => trim($a->document->inference->pages[0]->prediction->nro_factura->values[0]->content),
						'periodo_del_consumo' => trim($periodo_del_consumo),
						'fecha_emision' => trim($a->document->inference->pages[0]->prediction->fecha_emision->values[0]->content),
						'vencimiento_del_pago' => trim($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[0]->content),
						'total_importe' => trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content),
						'consumo' => trim($a->document->inference->pages[0]->prediction->consumo->values[0]->content),
					);
					break;

				case 5: //3480 PERSONAL

					$totalIndices = count($a->document->inference->pages[0]->prediction->periodo_del_consumo->values);
					$periodo_del_consumo = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[$paso]->content;
					}

					$dataUpdate = array(
						'vencimiento_del_pago' => trim($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[0]->content),
						'nro_medidor' => trim('N/A'),
						'periodo_del_consumo' => trim($periodo_del_consumo),
						'nro_cuenta' => trim($a->document->inference->pages[0]->prediction->nro_cuenta->values[0]->content),
						'nro_factura' => trim($a->document->inference->pages[0]->prediction->numero_de_factura->values[0]->content),
						'fecha_emision' => trim($a->document->inference->pages[0]->prediction->fecha_emision->values[0]->content),
						'total_importe' => trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content),
						'proximo_vencimiento' => trim($a->document->inference->pages[0]->prediction->proximo_vencimiento->values[0]->content),
						'consumo' => trim('S/D'),
						'total_vencido' => trim('S/D'),
					);
					break;
				case 6: // 6198 CLARO ARGENTINA
					$totalIndices = count($a->document->inference->pages[0]->prediction->nro_cuenta->values);
					$nro_cuenta = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_cuenta .= $a->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
					}
					$periodo_del_consumo = '';
					$totalIndices = count($a->document->inference->pages[0]->prediction->periodo_del_consumo->values);
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[$paso]->content;
					}
					$consumo = '';
					$totalIndices = count($a->document->inference->pages[0]->prediction->consumo->values);
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$consumo .= $a->document->inference->pages[0]->prediction->consumo->values[$paso]->content;
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->total_vencido->values);
					$total_vencido = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$total_vencido .= $a->document->inference->pages[0]->prediction->total_vencido->values[$paso]->content;
					}

					$dataUpdate = array(
						'periodo_del_consumo' => str_replace('desde','',trim($periodo_del_consumo)),
						'nro_cuenta' => trim($nro_cuenta),
						'nro_medidor' => trim('N/A'),
						'nro_factura' => trim($a->document->inference->pages[0]->prediction->numero_de_factura->values[0]->content),
						'fecha_emision' => trim($a->document->inference->pages[0]->prediction->fecha_emision->values[0]->content),
						'vencimiento_del_pago' => trim($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[0]->content),
						'total_vencido' => trim($total_vencido),
						'total_importe' => trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content),
						'consumo' => trim($consumo),
					);


					break;
				case 7: //  TELECENTRO 3959

					$totalIndices = count($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values);
					$fecha_emision = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$fecha_emision .= trim($a->document->inference->pages[0]->prediction->fecha_emision->values[$paso]->content);
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values);
					$vencimiento_del_pago = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$vencimiento_del_pago .= trim($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[$paso]->content);
					}

					$periodo_del_consumo = '';
					$totalIndices = count($a->document->inference->pages[0]->prediction->periodo_del_consumo->values);
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->periodo_del_consumo->values[$paso]->content;
					}			
					
					$numero_de_factura = '';
					$totalIndices = count($a->document->inference->pages[0]->prediction->numero_de_factura->values);
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$numero_de_factura .=$a->document->inference->pages[0]->prediction->numero_de_factura->values[$paso]->content;
					}

					$dataUpdate = array(
						'nro_cuenta' => trim($a->document->inference->pages[0]->prediction->nro_cuenta->values[0]->content),
						'fecha_emision' => trim($fecha_emision),
						'total_importe' => trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content),
						'nro_factura' => trim($numero_de_factura),
						'periodo_del_consumo' => str_replace(':','',trim($periodo_del_consumo)),
						'vencimiento_del_pago' => trim($vencimiento_del_pago),
						'nro_medidor' => trim('N/A'),
						// 'total_vencido' => trim($total_vencido),
						'consumo' =>'Tel/Int Pyme corp.',
					);

					break;
				case 8: //3480 TELECOM INTERNET - DIGITAL

					$totalIndices = count($a->document->inference->pages[0]->prediction->nro_cuenta->values);
					$nro_cuenta = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$nro_cuenta .= $a->document->inference->pages[0]->prediction->nro_cuenta->values[$paso]->content;
					}
					// $totalIndices = count($a->document->inference->pages[0]->prediction->nro_medidor->values);
					// if ($a->document->inference->pages[0]->prediction->nro_medidor->values) {
					// 	$medidor = $a->document->inference->pages[0]->prediction->nro_medidor->values[0]->content;
					// } else {
					// 	$medidor = 'N/A';
					// }			


					$totalIndices = count($a->document->inference->pages[0]->prediction->priodo_del_consumo->values);
					$periodo_del_consumo = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$periodo_del_consumo .= ' ' . $a->document->inference->pages[0]->prediction->priodo_del_consumo->values[$paso]->content;
					}

					$totalIndices = count($a->document->inference->pages[0]->prediction->detalle_de_servicio->values);
					$detalle_de_servicio = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$detalle_de_servicio .= ' ' . $a->document->inference->pages[0]->prediction->detalle_de_servicio->values[$paso]->content;
					}

					$dataUpdate = array(
						'nro_cuenta' => trim($nro_cuenta),
						'nro_medidor' => trim('N/A'),
						'nro_factura' => trim($a->document->inference->pages[0]->prediction->nro_de_factura->values[0]->content),
						'periodo_del_consumo' => trim($periodo_del_consumo),
						'fecha_emision' => trim($a->document->inference->pages[0]->prediction->fecha_emision->values[0]->content),
						'vencimiento_del_pago' => trim($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[0]->content),
						'total_importe' => trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content),
						'consumo' => trim($detalle_de_servicio),
					);
					break;
				case 10: //3480 TELECOM TELEFONIA FIJA

					$totalIndices = count($a->document->inference->pages[0]->prediction->consumo->values);
					$consumo = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$consumo .= ' ' . $a->document->inference->pages[0]->prediction->consumo->values[$paso]->content;
					}
					$totalIndices = count($a->document->inference->pages[0]->prediction->total_vencido->values);
					$total_vencido = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$total_vencido .= ' ' . $a->document->inference->pages[0]->prediction->total_vencido->values[$paso]->content;
					}
					
					
					$totalIndices = count($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values);
					$vencimiento_del_pago = '';
					for ($paso = 0; $paso < $totalIndices; $paso++) {
						$vencimiento_del_pago .=$a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[$paso]->content;
					}


			
					$dataUpdate = array(
						'nro_cuenta' => trim($a->document->inference->pages[0]->prediction->nro_cuenta->values[0]->content),
						'nro_medidor' => trim('N/A'),
						'nro_factura' => trim($a->document->inference->pages[0]->prediction->numero_de_factura->values[0]->content),
						'fecha_emision' => trim($a->document->inference->pages[0]->prediction->fecha_emision->values[0]->content),
						'vencimiento_del_pago' => trim($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[0]->content),
						'periodo_del_consumo' => trim($a->document->inference->pages[0]->prediction->vencimiento_del_pago->values[0]->content),
						'total_vencido' => trim($total_vencido),
						'total_importe' => trim($a->document->inference->pages[0]->prediction->total_importe->values[0]->content),
						'consumo' => trim($consumo),
					);

					break;
			}

			$this->db->where('id', $mires[0]->id);
			$this->db->update('_datos_api', $dataUpdate);
		} else {
			die('error leyendo datos api en base');
		}
	}
	public function delete_lote()
	{
		$archivos = $this->Lotes_model->getBatchFiles($_REQUEST['code']);
		$total = 0;
		foreach ($archivos as $data) {
			if (is_file($data->nombre_archivo)) {
				if (unlink($data->nombre_archivo)) {
					$total++;
					$this->db->where('nombre_archivo', $data->nombre_archivo);
					$this->db->delete('_datos_api');
				}
			} else {
				$this->db->where('nombre_archivo', $data->nombre_archivo);
				$this->db->delete('_datos_api');
				// die('no');
			}
		}
		$this->db->where('code', $_REQUEST['code']);
		$this->db->delete('_lotes');

		$response = array(
			'mensaje' => "Archivos borrados",
			'title' => 'LOTES ',
			'status' => 'success'
		);
		echo json_encode($response);
	}


	public function Buscar_archivos($lote, $proveedor)
	{

		if ($this->input->is_ajax_request()) {

			$proveedor = $this->Manager_model->getwhere('_proveedores', 'id="' . $proveedor . '"');

			$files = $this->Lotes_model->getBatchFiles($lote);



			$response = array(
				'mensaje' => 'Listado de archivos a leer',
				'files' => $files,
				'title' => 'Lectuas API',
				'status' => 'success',
			);
			echo json_encode($response);
			exit();
		}
	}

	public function leerApi2()
	{


		$response = array(
			'mensaje' =>  $_REQUEST['file'],
			'title' => 'Consulta API',
			'status' => 'error',
		);

		sleep(3);
		echo json_encode($response);
		exit();
	}
	public function leerApi()
	{

		$file = str_replace(base_url(), '', $_POST['file']);

		$proveedor = $this->Manager_model->getwhere('_proveedores', 'id="' . $_POST['id_proveedor'] . '"');

		$request = array(
			'full_path' => $_POST['file']
		);

		// sleep(3);
		$dataApi = apiRest($request, $proveedor->urlapi);

		// echo '<pre>';
		// var_dump( $dataApi ); 
		// echo '</pre>';
		// die();

		// // Escapar los valores de los datos actualizados
		// $datos_actualizados_escapados = array();
		// foreach ($datos_actualizados as $columna => $valor) {
		// 	$datos_actualizados_escapados[$columna] = $this->db->escape($valor);
		// }

		// // Construir la consulta UPDATE
		// $this->db->set($datos_actualizados_escapados);
		// $this->db->where('condicion_columna', 'valor_condicion');

		// // Ejecutar la consulta UPDATE
		// $this->db->update('tabla');

		// // Verificar si la consulta se ejecutó correctamente
		// if ($this->db->affected_rows() > 0) {
		// 	// La consulta se ejecutó correctamente
		// } else {
		// 	// La consulta no se ejecutó o no afectó filas
		// }


		$updateData = array(
			'dato_api' => json_encode($dataApi),
		);
		// sleep(1);

		// $this->db->where("nombre_archivo LIKE '%uploader/files/".$proveedor->codigo. "/". $dataApi['document']['name']  . "%' ESCAPE '!'");
		$this->db->where("nombre_archivo", "uploader/files/" . $proveedor->codigo . "/" . $dataApi['document']['name']);
		$this->db->update('_datos_api', $updateData);

		$this->getDato("uploader/files/" . $proveedor->codigo . "/" . $dataApi['document']['name'], $proveedor->id);


		$response = array(
			'mensaje' => $_POST['file'],
			'title' => 'LOTES 242',
			'status' => 'success',
		);
		echo json_encode($response);
		exit();
	}




	public function upload($id = null)
	{


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
				$config['max_size'] = '2024'; // max_size in kb 
				$config['file_name'] = $nombre_archivodb;

				// Load upload library 
				$this->load->library('upload', $config);


				$response = array(
					'mensaje' => 'inicoi',
					'title' => 'LOTES 301',
					'status' => 'error',
				);
				// File upload
				if ($this->upload->do_upload('file')) {
					// Get data about the file

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


					// $data = apiRest($uploadData, $proveedor->urlapi);


					// if ($data['api_request']['error']) {
					// 	$response = array(
					// 		'mensaje' => '<strongs>' . $proveedor->nombre . '</strong><br>' . $data['api_request']['error']['code'] . '<br> ' . $data['api_request']['error']['message'],
					// 		'title' => 'Consulta API',
					// 		'status' => 'error',
					// 	);
					// 	echo json_encode($response);
					// 	exit();
					// }


					$lote = $this->Lotes_model->crearLote();

					$texto = ' ';
					$saveData = array(
						'id_lote' => $lote[0]->id,
						'code_lote' => $lote[0]->code,
						'id_proveedor' => $proveedor->id,
						'nombre_proveedor' => $proveedor->nombre,
						// 'nombre_proveedor' => $data['document']['inference']['pages'][0]['prediction']['nombre_proveedor']['values'][0]['content'],
						// 'dato_api' => json_encode($data),
						'nombre_archivo' => $nombre_archivodb,
					);

					if ($this->Manager_model->grabar_datos("_datos_api", $saveData)) {

						// ESTA FUNCION ES LA QUE ACTUALIZA LA TABLA CON LOS DATOS RECIBIDOS DESDE LA API
						// 	$this->getDato($this->db->insert_id(), $proveedor->id);

						$response = array(
							'mensaje' => 'Archivo: ' . $filename . ' Lote: ' . $lote[0]->code,
							'title' => 'Grabar Archivos',
							'status' => 'success',
							'file' => $filename,
							'path' => $fullpath,
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
					'title' => 'Grabar dsdsdsdsdsdsd',
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
	public function crearLote($data = null)
	{
		$lote = $this->Lotes_model->crearLote();

		echo json_encode($this->load->view('manager/etiquetas/panel', $lote, TRUE));
	}

	public function checkFile()
	{
		$arc = explode('.', $_POST['name']);
		$nuevoNOmbre = limpiar_caracteres($arc[0]);
		$nombre_archivodb = $nuevoNOmbre . '.' . $arc[1];

		$proveedor = $this->Manager_model->get_data('_proveedores', $_POST['id_proveedor']);
		$nombre_fichero = 'uploader/files/' . strtolower($proveedor->codigo) . '/' . $nombre_archivodb;

		$datoDb = $this->Manager_model->getWhere('_datos_api', 'nombre_archivo="' . $nombre_archivodb . '"');

		if (file_exists($nombre_fichero)) {

			$response = array(
				"status" => 'error'
			);
			echo json_encode($response);
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




	public function lotes_dt($id = null)
	{

		if ($this->input->is_ajax_request()) {

			$memData = $this->Manager_model->getRows($_POST);


			// echo $this->db->last_query();
			// die();
			$i = $_POST['start'];

			$data = [];
			$estado = '<span class="acciones"><i class="text-success icon-check2 "></i></span>';
			foreach ($memData as $r) {


				$classTextMerge = 'text-danger';


				$disableMerge = '';
				$classMerge = '';
				$archivos = $this->Lotes_model->getBatchFiles($r->code);
				$classTextMerge = 'text-success';
				$error = 0;


				foreach ($archivos as $dato) {

					if (!$this->Manager_model->get_indexacion('_indexaciones', $dato->nro_cuenta)) {

						$error++;
					}
				}
				$classMerge = 'mergelote';
				if ($error > 0) {
					$classTextMerge = 'text-warning';


					$disableMerge = ' disabled="disabled ';
					$classMerge = 'mergelote';
				}

				$consolidado = '<span class="acciones"><i class="text-danger icon-cross2 "></i></span>';
				if ($r->consolidado == 1) {
					$consolidado = '<span class="acciones"><i class="text-warnin icon-check2 "></i></span>';
					$classTextMerge = 'text-info';
					// $consolidado = 'OK';
				}
				// echo '<pre>';
				// var_dump( $archivos ); 
				// echo '</pre>';
				// die();


				$i++;
				$accionesVer = '<span class="acciones"><a title="ver archivo" href="/Admin/Lotes/viewBatch/' . $r->code . '"  class=""><i class="icon-eye4" title="ver"></i> </a></span> ';


				$accionesMerge = '<span data-consolidado="' . $r->consolidado . '"  data-errores="' . $error . '" data-code="' . $r->code . '" data-id_lote="' . $r->id_lote . '" class="' . $classMerge . '"><a ' . $disableMerge . ' title="ver archivo" href="#"  class=""><i class="' . $classTextMerge . ' icon-merge " title="Consolidar"></i> </a></span> ';
				$accionesEdit = '<span data-id_lote="' . $r->id_lote . '" data-code="' . $r->code . '"class="d-none editar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Editar lote" href="#"  class=""><i class=" text-warningr  icon-pencil4 " title="Editar Lote"></i> </a> </span>';
				$accionesDelete = '<span data-id_lote="' . $r->id_lote . '" data-code="' . $r->code . '"class="borrar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Borrar lote" href="#"  class=""><i class=" text-danger icon-trash " title="Borrar Lote"></i> </a> </span>';
				$proveedor = $this->proveedores_model->get_proveedor($r->id_proveedor);
				// $user = $this->ion_auth->user($r->user_add)->row();

				$data[] = array(
					// $r->id,
					$r->nombre,
					$r->codigo,
					fecha_es($r->fecha_add, 'd/m/a', false),
					count($archivos),
					$error,
					// $estado,
					$consolidado,
					$r->last_name . ' ' . $r->first_name,
					$accionesVer . $accionesMerge . $accionesEdit . $accionesDelete
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
	public function deletefile()
	{
		$this->Manager_model->delete();
	}

	public function viewBatch($id = null)
	{
		$data = array();

		$this->lote = $id;

		if ($this->input->is_ajax_request()) {
			// $data = $row = array();

			$LotesData = $this->Lotes_model->getFileRows($_POST);

			$i = $_POST['start'];

			foreach ($LotesData as $r) {

				// echo '<pre>';
				// var_dump( $ ); 
				// echo '</pre>';
				// die();

				// $arr = json_decode($r->dato_api);

				// echo '<pre>';
				// var_dump($arr->document ); 
				// echo '</pre>';
				// die();


				$classAccionMerge = 'mergefile';
				$archivo = explode('/', $r->nombre_archivo);
				if ($indexador = $this->Manager_model->getWhere('_indexaciones', 'nro_cuenta="' . $r->nro_cuenta . '"')) {
					$indexador = $indexador->id;
				} else {
					$indexador = '0';
				}


				$iconTextMerge = '';

				$disableMerge = '';

				if ($this->Manager_model->get_indexacion('_indexaciones', $r->nro_cuenta)) {

					$iconTextMerge = 'text-success';
				} else {
					$iconTextMerge = 'text-danger';
				}
				if ($r->consolidado == 1) {
					$iconTextMerge = 'text-defautl';
				}

				$accionesVer = '<span class="acciones"><a title="ver archivo" href="/Admin/Lecturas/Views/' . $r->id . '"  class=""><i class="icon-eye4" title="ver"></i> </a></span> ';
				$accionesMerge = '<span data-file="' . $archivo[3] . '" data-consolidado="' . $r->consolidado . '"  data-indexador="' . $indexador . '" data-code="' . $r->code_lote . '" data-id_file="' . $r->id . '" class="' . $classAccionMerge . '"><a ' . $disableMerge . ' title="ver archivo" href="#"  class=""><i class="' . $iconTextMerge . ' icon-merge " title="Consolidar"></i> </a></span> ';
				$accionesEdit = '<span data-id_lote="' . $r->id . '" data-code="' . $r->code_lote . '"class="editar_lote acciones" data-consolidado="' . $r->consolidado . '"><a title="Editar lote" href="#"  class=""><i class=" text-warningr  icon-pencil4 " title="Editar "></i> </a> </span>';
				$accionesDelete = '<span data-tabla="_datos_api" data-id_file="' . $r->id . '" class="borrar-file acciones" ><a title="Borrar file" href="#"  class=""><i class=" text-danger icon-trash " title="Borrar "></i> </a> </span>';

				$data[] = array(
					$r->nro_cuenta,
					$r->nro_medidor,
					$r->nro_factura,
					$r->periodo_del_consumo,
					$r->fecha_emision,
					// fecha_es($r->fecha_emision, 'd/m/a', false),
					// fecha_es($r->vencimiento_del_pago, 'd/m/a', false),
					$r->vencimiento_del_pago,
					$r->total_importe,
					$r->total_vencido,
					$r->consumo,
					$indexador,
					$archivo[3],
					$accionesVer . $accionesMerge . $accionesDelete
				);
			}


			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->Lotes_model->countAllFiles(),
				"recordsFiltered" => $this->Lotes_model->countFilteredFiles($_POST),
				"data" => $data,
			);

			// Output to JSON format
			echo json_encode($output);
			exit();
		}

		if ($id) {


			$script = array(
				base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
			);


			$this->data['css_common'] = $this->css_common;
			$this->data['css'] = '';

			$this->data['script_common'] = $this->script_common;
			$this->data['script'] = $script;

			// $this->data['proveedor'] = $this->Manager_model->get_data('_proveedores', $_POST['id_proveedor']);



			$hoy = getdate();
			$code = substr(str_replace(array('=', '-'), '', $this->encrypt->encode($hoy[0])), 0, -22);

			$this->data['code'] = $code;

			$this->data['dropzone'] = $this->load->view('manager/etiquetas/dropzone', $this->data, TRUE);
			$this->data['content'] = $this->load->view('manager/secciones/lotes/' . $this->router->fetch_method(), $this->data, TRUE);
			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);
		}
	}

	public function views($id)
	{
		// $myDato = $this->encrypt->decode(urldecode($id));
		$myDato = $id;

		$registro_api = $this->Manager_model->get_data_api('_datos_api', $myDato);

		$script = array(
			base_url('assets/manager/js/plugins/tables/datatables/datatables.js'),
			base_url('assets/manager/js/plugins/dropzone.min.js'),
			base_url('assets/manager/js/secciones/lecturas/views.js?ver=' . time()),
		);

		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;
		$this->data['result'] = $registro_api;


		// $this->data['nro_cuenta'] = $resultData->nro_cuenta;

		$this->data['indexaciones'] = $this->Indexaciones_model->get_indexaciones($registro_api->nro_cuenta);

		$this->data['content'] = $this->load->view('manager/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method(), $this->data, TRUE);

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
	}
	public function listados()
	{
		$script = array(
			base_url('assets/manager/js/plugins/tables/datatables/datatables.js'),
			base_url('assets/manager/js/plugins/dropzone.min.js'),
			//			base_url('assets/manager/js/plugins/tables/datatables/datatables.min.js'),
			//			base_url('assets/manager/js/plugins/tables/datatables/datatables_advanced.js'),
			base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
			base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
		);


		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;
		$this->data['select_proveedores'] = $this->Manager_model->obtener_contenido_select('_proveedores', 'SELECCIONE PROVEEDOR', 'nombre', 'id ASC');

		$this->data['content'] = $this->load->view('manager/secciones/Lotes/' . $this->router->fetch_method(), $this->data, TRUE);

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

}
