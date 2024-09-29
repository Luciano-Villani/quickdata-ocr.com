<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Scripts extends backend_controller
{
    function __construct()
    {
        parent::__construct();
    }

    public function agregarcampos()
    {
        $_tablas = array(
            '_datos_api',
            '_datos_api_canon',
            '_consolidados_canon',
            '_consolidados',
        );

        // Agregar columnas necesarias
        $this->agregar_columna($_tablas, 'mes_fc', 'VARCHAR(100) NOT NULL');
        $this->agregar_columna($_tablas, 'anio_fc', 'VARCHAR(100) NOT NULL');
        $this->agregar_columna($_tablas, 'unidad_medida', 'VARCHAR(100) NOT NULL');
    }

    private function agregar_columna($tablas, $campo, $tipo)
    {
        foreach ($tablas as $t) {
            $query = $this->db->query("SHOW COLUMNS FROM " . $t . " WHERE Field = '" . $campo . "'");
            if (!$query->result()) {
                $this->db->query("ALTER TABLE " . $t . " ADD " . $campo . " " . $tipo);
                echo "Columna " . $campo . " añadida a la tabla " . $t . "<br>";
            }
        }
    }

    public function recorrerdatos()
    {
        $this->procesarDatos('_datos_api', '_consolidados');
    }

    public function recorrerdatos_canon()
    {
        $this->procesarDatos('_datos_api_canon', '_consolidados_canon');
    }

    private function procesarDatos($tablaDatos, $tablaConsolidados)
    {
        $datos = $this->Manager_model->get_alldata($tablaDatos, false);

        foreach ($datos as $a) {
            $proveedor = $this->Manager_model->get_data('_proveedores' . ($tablaDatos === '_datos_api_canon' ? '_canon' : ''), $a->id_proveedor);
            $fecha_dato_mes = 'N/A';
            $fecha_dato_anio = 'N/A';

            if ($a->fecha_emision && $a->fecha_emision != "01-01-1970") {
                $fecha_dato_mes = fecha_es($a->fecha_emision, 'm');
                $fecha_dato_anio = fecha_es($a->fecha_emision, 'Y');
            }

            $dataUpdate = array(
                'mes_fc' => $fecha_dato_mes,
                'anio_fc' => $fecha_dato_anio,
                'unidad_medida' => $proveedor->unidad_medida
            );

            $this->db->where('id', $a->id);
            $this->db->update($tablaDatos, $dataUpdate);

            $this->db->where('id_lectura_api', $a->id);
            $this->db->update($tablaConsolidados, $dataUpdate);

            echo "<br>id - " . $a->id . " - " . $a->fecha_emision . " - " . $fecha_dato_mes . " - " . $fecha_dato_anio;
        }
    }

    private function extraerFechaDesdeJson($datoApi)
    {
        if (!is_null($datoApi) && $datoApi !== 'null' && $datoApi !== 'NULL') {
            $json = json_decode($datoApi);

            if (isset($json->api_request->status_code) && $json->api_request->status_code != 500) {
                $fecha_json = '';

                if (isset($json->document->inference->pages[0]->prediction->fecha_emision->values)) {
                    foreach ($json->document->inference->pages[0]->prediction->fecha_emision->values as $value) {
                        $fecha_json .= trim($value->content);
                    }
                }

                return $fecha_json;
            }
        }

        return 'N/A';
    }
}
