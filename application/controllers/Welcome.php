<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends front_controller
{

	public function perimayus(){
		$dataACT = $this->Manager_model->get_alldata('_consolidados');
		// echo '<pre>';
		// var_dump( $dataACT ); 
		// echo '</pre>';
		// die();
		
		foreach ($dataACT as $reg) {


			$dataUpdate['periodo_contable'] = strtoupper( $reg->periodo_contable);

			$this->db->where('id', $reg->id);
			$this->db->update('_consolidados', $dataUpdate);
		}

	}
	
	public function importes()
	{

		$dataACT = $this->Manager_model->get_alldata('_consolidados');
		/*
ALTER TABLE `_datos_api` ADD `nombre_archivo_temp` INT(255) NOT NULL AFTER `proximo_vencimiento`, ADD `importe_1` DECIMAL(10,2) NOT NULL AFTER `nombre_archivo_temp`;
*/

		foreach ($dataACT as $reg) {



			// modificacion campo importe_1 pasa de string total_importe a double 10.2
			switch ($reg->id_proveedor) {
					// case 1: //AYSA
				case 4: //EDENOR
					$importe = trim($reg->importe);

					$importe = str_replace(',', '.', str_replace('.', '', $importe));
					$numero_decimal = number_format($importe, 2, '.', '');
					// die();
					break;

				case 8: //TELECOM INTER
				case 6: //TELECOM INTER


					$importe =  floatval(trim($reg->importe));
					$numero_decimal = number_format($importe, 2, '.', '');
					// die();
					break;
				default:


					$numero_decimal = trim($reg->importe);
			}
			if ($numero_decimal == "")
				$numero_decimal = 99.99;

			$dataUpdate['importe_1'] = $numero_decimal;
			$dataUpdate['importe'] = $numero_decimal;

			$this->db->where('id', $reg->id);
			$this->db->update('_consolidados', $dataUpdate);
		}
	}

	public function periodos()
	{

		$dataACT = $this->Manager_model->get_alldata('_consolidados');
		/*
ALTER TABLE `_datos_api` ADD `nombre_archivo_temp` INT(255) NOT NULL AFTER `proximo_vencimiento`, ADD `importe_1` DECIMAL(10,2) NOT NULL AFTER `nombre_archivo_temp`;
*/

		foreach ($dataACT as $reg) {


			$dataUpdate['periodo_contable'] = strtoupper($reg->periodo_contable);
			

			$this->db->where('id', $reg->id);
			$this->db->update('_consolidados', $dataUpdate);
		}
	}

	public function id_secretaria()
	{

		$dataACT = $this->Manager_model->get_alldata('_consolidados');
		/*
ALTER TABLE `_datos_api` ADD `nombre_archivo_temp` INT(255) NOT NULL AFTER `proximo_vencimiento`, ADD `importe_1` DECIMAL(10,2) NOT NULL AFTER `nombre_archivo_temp`;
*/

		foreach ($dataACT as $reg) {
			echo $reg->secretaria; 
			echo '<br>';	
			$this->db->select('trim(secretaria),_secretarias.*');
			$this->db->like('trim(secretaria)', trim($reg->secretaria),'COLLATE utf8_bin');
			$this->db->from('_secretarias');
			$secretaria =   $this->db->get()->result();

			// echo $this->db->last_query();
			// die();
if($secretaria){

	// echo '<pre>';
	// var_dump( $secretaria[0] ); 
	// var_dump( $secretaria[0]->id ); 
	// echo '</pre>';
	// die();
	
	$dataUpdate['id_secretaria'] =  $secretaria[0]->id;
	
	$this->db->where('id', $reg->id);
	$this->db->update('_consolidados', $dataUpdate);
}
		}
	}
}
