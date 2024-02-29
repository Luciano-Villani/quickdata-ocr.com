<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Consolidados_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('manager/Lotes_model');
		$this->load->model('manager/Proveedores_model', 'proveedores');

		$this->periodos = array(
			'Enero',
			'Febrero',
			'Marzo',
			'Abril',
			'Mayo',
			'Junio',
			'Julio',
			'Agosto',
			'Septiembre',
			'Octubre',
			'Noviembre',
			'Diciembre',
		);
	}
	public function consolidar_datos()
	{

		if (isset($_REQUEST['id_file']) && $_POST['id_file'] != null) {
			$data = $this->Manager_model->getwhere('_datos_api', 'id=' . $_POST['id_file']);
			$files = array(
				$data
			);
		} else {
			$files = $this->Lotes_model->getBatchFiles($_POST['code_lote']);
		}

		try {

			$error = true;

			foreach ($files as $file) {


				if (checkConsolidar($file->id)) {
					$error = false;

					$dependencia = '';
					$id_proyecto = '';
					$obra = '';
					$proyecto_descripcion = '';
					$dependencia_dependencia = '';
					$dependencia_direccion = '';
					$indexador = $this->Manager_model->getWhere('_indexaciones', 'nro_cuenta="' . $file->nro_cuenta . '"');
					$proveedor = $this->proveedores->get_proveedor($indexador->id_proveedor);
					$secretaria = $this->Manager_model->get_data('_secretarias', $indexador->id_secretaria);


					if ($dependencia = $this->Manager_model->getWhere('_dependencias', '_dependencias.id="'.$indexador->id_dependencia .'" AND _dependencias.id_secretaria = "'.$indexador->id_secretaria.'"')) {
						$dependencia_dependencia =  $dependencia->dependencia;
						$dependencia_direccion = $dependencia->direccion;
					}

					if (!$programa = $this->Manager_model->getWhere('_programas','id_interno="' . $indexador->id_programa . '" AND _programas.id_secretaria = "'.$indexador->id_secretaria.'"' )) {
						
					}
					if ($proyecto = $this->Manager_model->getWhere('_proyectos','id_interno="' . $indexador->id_proyecto.'" AND _proyectos.id_secretaria = "'.$indexador->id_secretaria.'" AND _proyectos.id_programa = "'.$indexador->id_programa.'"')) {
						$id_proyecto = $proyecto->id_interno;
						$proyecto_descripcion = $proyecto->id_interno;
					}
					// if ($obra = $this->Manager_model->get_data('_obras', $indexador->id_obra != null)) {
					// 	$obra = $obra->descripcion;
					// }
					$fechaVencimeinto = fecha_es($file->vencimiento_del_pago, 'd-m-a', false);

					$mesVencimiento = explode('-', $fechaVencimeinto);
					$indicePeriodoContable = str_replace('0', '', $mesVencimiento[1]);

					// <th>Empresa</th>
					// <th>Expediente</th>
					// <th>Secretaría</th>
					// <th>Juridicción</th>
					// <th>Programa</th>
					// <th>Programa + jurisdicción</th>
					// <th>Objeto del gasto</th>
					// <th>Dependencia</th>
					// <th>Direccion</th>
					// <th>Nro factura</th>
					// <th>Período</th>
					// <th>Vencimiento del pago</th>
					// <th>Pasar a Preventivas</th>
					// <th>Importe factura</th>
					
					
					$dataBatch = array(
						'id_lectura_api' => $file->id,
						'id_indexador' => $indexador->id,
						'id_proveedor' => $proveedor->id,
						'proveedor' => $proveedor->nombre,
						'expediente' => $indexador->expediente,
						'secretaria' => $secretaria->secretaria,
						'jurisdiccion' => $secretaria->rafam,
						'programa' => $programa->descripcion,
						'id_programa' => $programa->id_interno,
						'id_proyecto' => $id_proyecto,
						'proyecto' => $proyecto_descripcion,
						'objeto' => $proveedor->objeto_gasto,
						'dependencia' =>  $dependencia_dependencia,
						'dependencia_direccion' =>  $dependencia_direccion,
						'nro_factura' => $file->nro_factura,
						'codigo_proveedor' => $proveedor->codigo,
						'tipo_pago' => get_tipoPago($indexador->tipo_pago),
						'nro_cuenta' => $indexador->nro_cuenta,
						'periodo_del_consumo' => $file->periodo_del_consumo,
						'fecha_vencimiento' => $file->vencimiento_del_pago,
						'mes_vencimiento' => $mesVencimiento[1],
						'preventivas' => date("Y-m-d H:i:s"),
						'importe' => $file->total_importe,
						'periodo_contable' => fecha_es(date("Y-m-d H:i:s"), 'F a', false),
						'lote' => $_POST['code_lote'],
						'user_consolidado' => $this->user->id,
						'fecha_consolidado' => $this->fecha_now,
						'nombre_archivo' => $file->nombre_archivo,

					);

/*
 <pre>Consolidados modelarray(26) {
  ["id_lectura_api"]=>
  string(2) "29"
  ["id_indexador"]=>
  string(3) "227"
  ["proveedor"]=>
  string(7) "NATURGY"
  ["expediente"]=>
  string(3) "exp"
  ["secretaria"]=>
  string(32) "SECRETARIA DE SERVICIOS PUBLICOS"
  ["jurisdiccion"]=>
  string(10) "1110144000"
  ["programa"]=>
  string(25) "Subsecretaria De Transito"
  ["id_programa"]=>
  string(2) "16"
  ["proyecto"]=>
  string(1) "1"
  ["objeto"]=>
  string(5) "3.1.3"
  ["dependencia"]=>
  string(21) "DIRECCION DE TRANSITO"
  ["direccion"]=>
  string(21) "ROQUE SAENZ PE?A 1779"
  ["nro_factura"]=>
  string(13) "0004739012982"
  ["codigo_proveedor"]=>
  string(4) "4399"
  ["tipo_pago"]=>
  string(6) "DEBITO"
  ["nro_cuenta"]=>
  string(8) "984117/9"
  ["periodo"]=>
  string(4) "23/1"
  ["periodo_del_consumo"]=>
  string(4) "23/1"
  ["fecha_vencimiento"]=>
  string(10) "2024-01-09"
  ["mes_vencimiento"]=>
  string(2) "01"
  ["importe"]=>
  string(7) "3959.90"
  ["periodo_contable"]=>
  string(5) "Enero"
  ["lote"]=>
  string(5) "AjxTY"
  ["user_consolidado"]=>
  string(2) "76"
  ["fecha_consolidado"]=>
  string(19) "2024-01-30 02:06:49"
  ["nombre_archivo"]=>
  string(37) "uploader/files/4399/0984117-23-12.pdf"
}
</pre>
 */


					$this->Manager_model->grabar_datos('_consolidados', $dataBatch);

					$data = array(
						'consolidado' => 1,
						'user_consolidado' => $this->user->id,
						'fecha_consolidado' => $this->fecha_now,
					);
					$this->db->update('_datos_api', $data, array('id' => $file->id));

					$this->db->update('_lotes', $data, array('code' => $_POST['code_lote']));

					
				} else {
					$error = false;
					
					// $data = array(
					// 	'consolidado' => 0
					// );
					// $this->db->update('_datos_api', $data, array('id' => $_POST['id_lectura_api']));
					// $this->db->delete('_consolidados', array('id_lectura_api' => $_POST['id_lectura_api']));
				}
			}
			if($error){
				$response = array(
					'estado' => 'error',
					'title' => 'CONSOLIDACIONES',
					'mensaje' => 'Archivos anteriormente Consolidados'
				);
				 return $response;
			}else{
				$response = array(
					'status' => 'succes',
					'title' => 'CONSOLIDACIONES',
					'mensaje' => 'Archivo Consolidado'
				);
				echo json_encode($response);die();
			}
		} catch (Exception $e) {
			die('error');
		}
	}
	public function grabar_datos($tabla, $data)
	{

		try {
			$this->db->insert($tabla, $data);
		} catch (Exception $e) {
			// this will not catch DB related errors. But it will include them, because this is more general. 
			var_dump($e->getMessage());
		}
	}


}
