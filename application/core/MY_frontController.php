<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class MY_FrontController extends CI_Controller {
 
    function __construct(){
    parent::__construct();
			$this->page_title = 'CI_tempalte';
			$this->page_datail = 'escturctura base';
			$this->user = $this->ion_auth->user()->row();

			$fecha = date("Y-m-d");
			$this->fecha = fecha_es($fecha, "L d F a"); //Resultado: dia 25 mes completo 2014

			$this->css_common = array(
			// base_url('assets/manager/css/icons/icomoon/styles.min.css?ver='.time()),
			// base_url('assets/manager/css/bootstrap.min.css?ver='.time()),
			// base_url('assets/manager/css/bootstrap_limitless.css?ver='.time()),
			// base_url('assets/manager/css/layout.css?ver='.time()),
			// base_url('assets/manager/css/components.css?ver='.time()),
			// base_url('assets/manager/css/colors.min.css?ver='.time()),
			);


			$this->script_common = array(
			// base_url('assets/manager/js/jquery.min.js?ver='.time()),
			// base_url('assets/manager/js/bootstrap.bundle.min.js?ver='.time()),
			// base_url('assets/manager/js/plugins/loaders/blockui.min.js?ver='.time()),
			// base_url('assets/manager/js/plugins/notifications/sweet_alert.min.js?ver='.time()),
			// base_url('assets/manager/js/app.js?ver='.time()),

			);

			/*BARRA DE NAVEGACION Y FOOTER GLOBAL*/

			$data = array(
			'script' => '',
			'page_title' => 'CI_template',
			'page_datail' => 'Administrador',
			);

			$this->nav = $this->load->view('manager/etiquetas/nav', $data, TRUE);

			$this->footer = $this->load->view('manager/etiquetas/footer', $data, TRUE);
			
 		}

		
		 
		 public function index()
		 {
			 
		$data = array(
			'css_common' => $this->css_common,
			'script_common' => $this->script_common
		);
		
		$this->load->view('web/head', $data);
		$this->load->view('web/secciones/home',$data);
		$this->load->view('web/footer',$data);
	}
	
	
}
?>
