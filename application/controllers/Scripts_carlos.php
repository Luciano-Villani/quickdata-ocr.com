<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Scripts extends backend_controller
{
    function __construct()
    {
        parent::__construct();

        // include APPPATH . 'third_party/Mindee/Client.php';


    }

    public function agregarcampos()
    {
        $_tablas = array(
            '_datos_api',
            '_datos_api_canon',
            '_consolidados_canon',
            '_consolidados',
        );


        foreach ($_tablas as $t) {
            $query = $this->db->query("SHOW COLUMNS from " . $t . " WHERE Field = 'mes_fc'");
            if (!$query->result()) {
                $this->db->query("ALTER TABLE " . $t . " ADD mes_fc VARCHAR(100) NOT NULL");
                $query->result();
            } $query = $this->db->query("SHOW COLUMNS from " . $t . " WHERE Field = 'unidad_medida'");
            if (!$query->result()) {
                $this->db->query("ALTER TABLE " . $t . " ADD unidad_medida VARCHAR(100) NOT NULL");
                $query->result();
            }
            $query = $this->db->query("SHOW COLUMNS from " . $t . " WHERE Field = 'anio_fc'");
            if (!$query->result()) {
                $this->db->query("ALTER TABLE " . $t . " ADD anio_fc VARCHAR(100) NOT NULL");
                $query->result();
            }
        }
    }

    public function recorrerdatos()
    {
        $datos = $this->Manager_model->get_alldata('_datos_api', false);


        foreach ($datos as $a) {
            $proveedor = $this->Manager_model->get_data('_proveedores', $a->id_proveedor);
            $fecha_dato_mes = 'N/A';
            $fecha_dato_anio = 'N/A';
            $fecha_json = '';

            if ($a->fecha_emision) {
                if ($a->fecha_emision != "01-01-1970") {
                    $fecha_dato_mes = fecha_es($a->fecha_emision, 'm');
                    $fecha_dato_anio = fecha_es($a->fecha_emision, 'Y');
                }
            }
            // no utilizamos el dato de json, solo los de campo fecha_emision
            if (!is_null($a->dato_api) && $a->dato_api != 'null' && $a->dato_api != 'NULL') {

                $json = json_decode($a->dato_api);

                if ($json->api_request->status_code != 500) {

                    $totalIndices = count($json->document->inference->pages[0]->prediction->fecha_emision->values);

                    for ($paso = 0; $paso < $totalIndices; $paso++) {
                        $fecha_json .= trim($json->document->inference->pages[0]->prediction->fecha_emision->values[$paso]->content);
                    }
                }

            } else {
                $fecha_json = 'N/A';
            }


            $dataUpdate = array(
                // 'mes_fc'=>$fecha_dato_mes,
                // 'anio_fc'=>$fecha_dato_anio,
                'unidad_medida'=>$proveedor->unidad_medida
            );

            $this->db->where('id', $a->id);
			$this->db->update('_datos_api', $dataUpdate);


            $this->db->where('id_lectura_api', $a->id);
			$this->db->update('_consolidados', $dataUpdate);


            echo "<BR>" . "id -" . $a->id . " - " . $a->fecha_emision . " - " . $fecha_dato_mes . " - " . $fecha_dato_anio;
        }


        die();
    }

    public function recorrerdatos_canon()
    {
        $datos = $this->Manager_model->get_alldata('_datos_api_canon', false);


        foreach ($datos as $a) {
            $proveedor = $this->Manager_model->get_data('_proveedores_canon', $a->id_proveedor);
            $fecha_dato_mes = 'N/A';
            $fecha_dato_anio = 'N/A';
            $fecha_json = '';

            if ($a->fecha_emision) {
                if ($a->fecha_emision != "01-01-1970") {
                    $fecha_dato_mes = fecha_es($a->fecha_emision, 'm');
                    $fecha_dato_anio = fecha_es($a->fecha_emision, 'Y');
                }
            }
            // no utilizamos el dato de json, solo los de campo fecha_emision
            if (!is_null($a->dato_api) && $a->dato_api != 'null' && $a->dato_api != 'NULL') {

                $json = json_decode($a->dato_api);

                if ($json->api_request->status_code != 500) {

                    $totalIndices = count($json->document->inference->pages[0]->prediction->fecha_emision->values);

                    for ($paso = 0; $paso < $totalIndices; $paso++) {
                        $fecha_json .= trim($json->document->inference->pages[0]->prediction->fecha_emision->values[$paso]->content);
                    }
                }

            } else {
                $fecha_json = 'N/A';
            }


            $dataUpdate = array(
                'mes_fc'=>$fecha_dato_mes,
                'anio_fc'=>$fecha_dato_anio,
                'unidad_medida'=>$proveedor->unidad_medida
            );

            $this->db->where('id', $a->id);
			$this->db->update('_datos_api_canon', $dataUpdate);


            $this->db->where('id_lectura_api', $a->id);
			$this->db->update('_consolidados_canon', $dataUpdate);


            echo "<BR>" . "id -" . $a->id . " - " . $a->fecha_emision . " - " . $fecha_dato_mes . " - " . $fecha_dato_anio;
        }


        die();
    }
}
