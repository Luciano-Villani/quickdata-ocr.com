<?php
defined('BASEPATH') or exit('No direct script access allowed');

// chequea que no este consolidado
function checkConsolidar($id_api)
{
    $CI = &get_instance();
    $registro_api = $CI->Manager_model->getWhere('_datos_api', 'id=' . $id_api);

    if ($registro_api->consolidado == 0) {
       return true;
    }else{
       return false;
    }
}
function get_indexaciones($nro_cuenta)
{

    $query = $this->db->select('*')
        ->where('nro_cuenta', $nro_cuenta)
        ->get('_indexaciones');


    return $query->result();
}

function get_tipoPago($id, $datos = false)
{
    $CI = &get_instance();
    $query = $CI->db->select('tip_nombre')->where('tip_id', $id)->get('_tipo_pago');
    $pago = $query->row();
    if ($pago) {
        return  $pago->tip_nombre;
    } else {
        return 'sin dato ';
    }
}

function format_dates($date)
{
    $CI = &get_instance();
    $date = new DateTime($date);
    return $date->format('d/m h:m');
}

function limpiar_caracteres($value = '')
{

    $value = str_replace(' ', '_', $value);
    $textoLimpio = strtolower(preg_replace('([^A-Za-z0-9-_])', '', $value));

    $textoLimpio = utf8_encode($textoLimpio);
    $textoLimpio = str_replace(
        array('',',','+','á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('-','-','-','a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $textoLimpio
    );

    $textoLimpio = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $textoLimpio
    );

    $textoLimpio = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $textoLimpio
    );

    $textoLimpio = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $textoLimpio
    );

    $textoLimpio = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $textoLimpio
    );

    $textoLimpio = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C'),
        $textoLimpio
    );
    return $textoLimpio;
}
