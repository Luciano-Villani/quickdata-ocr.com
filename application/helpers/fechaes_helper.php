<?php

// echo '<pre>';
// var_dump( phpinfo() ); 
// echo '</pre>';
// die();
    if (!function_exists('fecha_es')) {




    /**
		 * Formateo de fechas 
     * la funcion revice 3 paramentros, la fecha , el formato y si muestra la hora (TRUE) o no,
		 * por defecto es FALSE.
		 * debajo del swith esta la salida de la fecha
     */
    function fecha_es($fecha_mysql, $formato = "d/m/a", $incluir_hora = FALSE) {

        $fecha_es = false;
        $fecha_en = strtotime($fecha_mysql);

        // echo '<pre>';
        // var_dump( date('Y-m-d',$fecha_en) ); 
        // echo '</pre>';
        // die();

        $dia = date("l", $fecha_en); // Sunday
        $ndia = date("d", $fecha_en); // 01-31
        $mes = date("m", $fecha_en); // 01-12
        $ano = date("Y", $fecha_en); // 2014
        $hora = date("H:i:s", $fecha_en); // H-i-s (Hora, minutos, segundos)

        $dias = array('Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miercoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sabado', 'Sunday' => 'Domingo');
        $meses = array('01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre');

        switch ($formato) {
            case "m":
                $fecha_es = date("m", $fecha_en);
                //Resultado: 06
                break;            
            case "Y":
                $fecha_es = date("Y", $fecha_en);
                //Resultado: 2014
                break;
            case "d/m/a":

                $fecha_es = date("d/m/Y", $fecha_en);
                //Resultado: 25/06/2014
                break;
            case "d-m-a":
                $fecha_es = date("d-m-Y", $fecha_en);
                //Resultado: 25-06-2014
                break; 
            case "Y-m-d":
                $fecha_es = date('Y-m-d', $fecha_en);
                //Resultado: 2014-06-14
                break;
            case "d.m.a":
                $fecha_es = date("d.m.Y", $fecha_en);
                //Resultado: 25.06.2014
                break;
            case "d M a":
                $fecha_es = $ndia . " " . substr($meses[$mes], 0, 3) . " " . $ano;
                //Resultado: 25 Jun 2014
                break;
            case "d F a":
                $fecha_es = $ndia . " " . $meses[$mes] . " " . $ano;
                //Resultado: 25 Junio 2014
                break;
            case "D d M a":
                $fecha_es = substr($dias[$dia], 0, 3) . " " . $ndia . " " . substr($meses[$mes], 0, 3) . " " . $ano;
                //Resultado: Mar 25 Jun 2014
                break;
            case "L d F a":
                $fecha_es = $dias[$dia] . ", " . $ndia . " de
								" . $meses[$mes] . " " . $ano;
                //Resultado: Martes 25 Junio 2014
                break;
            case "F a":
                $fecha_es =  $meses[$mes] . " " . $ano;
                //Resultado: Junio 2014
                break;
        }

        if ($incluir_hora) {
            $fecha_es .= " " . $hora;
        }
        if(!$fecha_es){
return $fecha_mysql;
        }else{
            return $fecha_es;

        }

    }

}

?>
