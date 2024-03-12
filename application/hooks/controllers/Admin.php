<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends backend_controller {
	function __construct()
	{

	parent::__construct();
		$this->page_title = 'CI_tempalte';
		$this->page_datail = 'escturctura base';
	

	}
	public function index()
	{
redirect('Admin/Lecturas');
// 		$this->load->library('encrypt');
// 		$msg = 'My secret message';

// $encrypted_string = $this->encrypt->encode($msg);

//  echo '<pre>';
//  var_dump( $encrypted_string); 
//  var_dump($this->encrypt->decode($encrypted_string)); 
// echo '</pre>';
// die(); 
		$this->data['css_common']= $this->css_common;
		
		$this->data['script_common']= $this->script_common;
		
		$this->data['content']= $this->load->view('manager/secciones/home',$this->data, TRUE);
		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index',$this->data);
		$this->load->view('manager/footer',$this->data);
		// die('fafa');
	}
}
