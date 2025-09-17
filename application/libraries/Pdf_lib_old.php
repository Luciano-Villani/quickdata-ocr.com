<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/fpdf/fpdf.php';
require APPPATH . 'libraries/fpdi/src/autoload.php';
// require APPPATH . 'libraries/fpdi/src/FpdfTpl.php';
// require APPPATH . 'libraries/fpdi/src/FpdfTplTrait.php';
// require APPPATH . 'libraries/fpdf/fpdf.php';
// require APPPATH . 'libraries/fpdi/src/fpdi.php';


use  \setasign\Fpdi\Fpdi;
use  \setasign\Fpdi\Fpdi2;

class Pdf_lib
{
    public function __construct()
    {
        //    die('DebugPHPMailer class is loaded.');
    }
    function test($full_path, $destino)
    {

        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($full_path);

        // for($pageNro =1 ;$pageNro <= $pageCount;$pageNro++){

        $pdfd = new Fpdi2();

        $pdf->AddPage();
        $template = $pdf->importPage(1);
        $pdf->useTemplate($template, 0, 0);
        $pdf->Output("F", $destino);
        // }

        // $pdf->Output();
        return;
    }

    function test2($full_path, $destino)
    {
        // Copiar el archivo original sin modificarlo
        if (!copy($full_path, $destino)) {
            // Manejar el error si la copia falla
            throw new Exception("Error al copiar el archivo PDF.");
        }
    
        return;
    }


    function test3($full_path, $destino)
    {
        // Crear una nueva instancia de FPDI
        $pdf = new Fpdi();
    
        // Obtener el número de páginas del PDF
        $pageCount = $pdf->setSourceFile($full_path);
    
        // Crear un nuevo PDF donde guardaremos hasta las dos primeras páginas
        for ($pageNro = 1; $pageNro <= min(2, $pageCount); $pageNro++) {
            $pdf->AddPage();
            $template = $pdf->importPage($pageNro);
            $pdf->useTemplate($template, 0, 0);
        }
    
        // Guardar el nuevo PDF en el destino especificado
        $pdf->Output("F", $destino);
    
        return;
    }
}
