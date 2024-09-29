<?php
defined('BASEPATH') or exit('No direct script access allowed');



function getPeriodos(){

    $mesesEspanol = [
        1 => 'ENERO', 
        2 => 'FEBRERO', 
        3 => 'MARZO', 
        4 => 'ABRIL', 
        5 => 'MAYO', 
        6 => 'JUNIO', 
        7 => 'JULIO', 
        8 => 'AGOSTO', 
        9 => 'SEPTIEMBRE', 
        10 => 'OCTUBRE', 
        11 => 'NOVIEMBRE', 
        12 => 'DICIEMBRE'
    ];
    
    $fechaInicial = new DateTime('2024-01-01');
    $fechaActual = new DateTime();
    
    $periodosContables = [];
    
    while ($fechaInicial <= $fechaActual) {
        // Obtener el mes y el año
        $mes = (int) $fechaInicial->format('n'); // Número del mes (1-12)
        $anio = $fechaInicial->format('Y'); // Año
    
        // Formatear el mes en español usando el array de meses
        $periodo = $mesesEspanol[$mes] . ' ' . $anio;
        $periodosContables[] = $periodo;
    
        // Avanzar al siguiente mes
        $fechaInicial->modify('+1 month');
    }
    
    // Mostrar el array
    return $periodosContables;
}
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
function checkConsolidarCanon($id_api)
{
    $CI = &get_instance();
    $registro_api = $CI->Electromecanica_model->getWhere('_datos_api_canon', 'id=' . $id_api);

    if ($registro_api->consolidado == 0) {
        return true;
    } else {
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
