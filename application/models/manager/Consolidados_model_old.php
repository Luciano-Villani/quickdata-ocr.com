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
					$id_programa = '';
					$programa_descripcion = '';
					$proyecto_descripcion = '';
					$id_interno_programa = '';
					$id_interno_proyecto = '';

					$dependencia_dependencia = '';
					$dependencia_direccion = '';
					$indexador = $this->Manager_model->getWhere('_indexaciones', 'nro_cuenta="' . $file->nro_cuenta . '"');
					$proveedor = $this->proveedores->get_proveedor($indexador->id_proveedor);
					$secretaria = $this->Manager_model->get_data('_secretarias', $indexador->id_secretaria);


	

					if ($dependencia = $this->Manager_model->getWhere('_dependencias', '_dependencias.id="'.$indexador->id_dependencia .'" AND _dependencias.id_secretaria = "'.$indexador->id_secretaria.'"')) {
						$dependencia_dependencia =  $dependencia->dependencia;
						$dependencia_direccion = $dependencia->direccion;
					}

					if ($programa = $this->Manager_model->getWhere('_programas','id="' . $indexador->id_programa .'"' )) {
						$id_programa = $programa->id;
						$programa_descripcion = $programa->descripcion;
						$id_interno_programa = $programa->id_interno;
					}
					if ($proyecto = $this->Manager_model->getWhere('_proyectos','id="' . $indexador->id_proyecto.'"')) {
						$id_proyecto = $proyecto->id;
						$proyecto_descripcion = $proyecto->descripcion;
						$id_interno_proyecto = $proyecto->id_interno;
					}
					// if ($obra = $this->Manager_model->get_data('_obras', $indexador->id_obra != null)) {
					// 	$obra = $obra->descripcion;
					// }
					$fechaVencimeinto =$file->vencimiento_del_pago;

					$mesVencimiento = explode('-', $fechaVencimeinto);
					$indicePeriodoContable = str_replace('0', '', $mesVencimiento[1]);

					// setear el perioddo contable para graficos
					$grPeriodos =  getPeriodos();
					$clavePeriodo = array_search(strtoupper(fecha_es(date("Y-m-d H:i:s"), 'F a', false)), $grPeriodos); 

					
					$dataBatch = array(
						'id_lectura_api' => $file->id,
						'id_indexador' => $indexador->id,
						'id_proveedor' => $proveedor->id,
						'proveedor' => $proveedor->nombre,
						'expediente' => $indexador->expediente,
						'secretaria' => $secretaria->secretaria,
						'id_secretaria' => $secretaria->id,
						'jurisdiccion' => $secretaria->major,
						'programa' => $programa_descripcion,
						'id_interno_programa' => $id_interno_programa,
						'id_programa' => $id_programa,
						'id_proyecto' => $id_proyecto,
						'id_interno_proyecto' => $id_interno_proyecto,
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
						'periodo_contable' => strtoupper(fecha_es(date("Y-m-d H:i:s"), 'F a', false)),
						'lote' => $_POST['code_lote'],
						'user_consolidado' => $this->user->id,
						'fecha_consolidado' => $this->fecha_now,
						'nombre_archivo' => $file->nombre_archivo,
						'importe_1' => $file->total_importe,
						'acuerdo_pago' => $indexador->acuerdo_pago,
						'periodo' => $clavePeriodo,
						'mes_fc' => $file->mes_fc,
						'anio_fc' => $file->anio_fc,
						'unidad_medida' => $proveedor->unidad_medida,
					);

					$this->Manager_model->grabar_datos('_consolidados', $dataBatch);
	
					$data = array(
						'consolidado' => 1,
						'user_consolidado' => $this->user->id,
						'fecha_consolidado' => $this->fecha_now,
					);
					$this->db->update('_datos_api', $data, array('id' => $file->id));

					$this->db->update('_lotes', $data, array('code' => $_POST['code_lote']));

					
				} else {
					$error = true;
				}
			}
			if($error){
				$response = array(
					'estado' => 'error',
					'title' => 'CONSOLIDACIONES',
					'mensaje' => 'Archivos anteriormente Consolidados'
				);
				echo json_encode($response);die();
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
