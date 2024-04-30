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
}
