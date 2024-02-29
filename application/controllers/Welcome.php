<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends front_controller {

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
