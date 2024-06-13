<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class MY_controller extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

		// if (!$this->ion_auth->logged_in()) {
        //     redirect(base_url());
        // }
		// $this->output->enable_profiler(TRUE);
		$this->page_title = '';
		$this->page_datail = 'escturctura base';
		$this->data['page_title'] =  ucfirst($this->router->fetch_class());

		$this->user = $this->ion_auth->user()->row();

		$fecha = date("Y-m-d");
		$this->fecha = fecha_es($fecha, "L d F a"); //Resultado: dia 25 mes completo 2014
		$this->fecha_now = date('Y-m-d H:i:s');
		$this->BtnText = 'Agregar';
		if (!$this->input->is_ajax_request()) {
			// $this->output->enable_profiler(TRUE);
		}
		$this->lote = '';
		if(isset($_POST['search']['value'])){
			$_POST['search']['value'] = strtoupper($_POST['search']['value']);

		}


		$this->css_common = array(
		
			base_url('assets/manager/css/icons/icomoon/styles.min.css'),
			base_url('assets/manager/css/bootstrap.min.css'),
			base_url('assets/manager/css/bootstrap_limitless.css'),
			base_url('assets/manager/css/layout.css'),
			base_url('assets/manager/css/components.css'),
			base_url('assets/manager/css/colors.min.css'),
			base_url('assets/manager/css/core.css'),
			base_url('assets/manager/css/animate.min.css'),
			base_url('assets/manager/js/plugins/dropzone.min.css'),
			'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
			base_url('assets/manager/js/plugins/tables/datatables/extensions/buttons.dataTables.css'),
			base_url('assets/manager/js/plugins/tables/datatables/extensions/responsive.dataTables.css'),
			base_url('assets/manager/js/plugins/notifications/jquery-confirm.css'),
			base_url('assets/css/icons/fontawesome/styles.min.css'),

		);

		// script de particulares de cada hoja
		$this->script = array();
		$this->script_common = array(
			base_url('assets/manager/js/plugins/daterange-picker/moment.min.js'),
			base_url('assets/manager/js/plugins/daterange-picker/daterangepicker.js'),
			base_url('assets/manager/js/plugins/tables/datatables/jquery.dataTables.min.js'),
			base_url('assets/manager/js/plugins/tables/datatables/extensions/dataTables.buttons.js'),
			base_url('assets/manager/js/plugins/tables/datatables/extensions/fixed_columns.min.js'),
			base_url('assets/manager/js/plugins/tables/datatables/extensions/jszip/jszip.min.js'),
			base_url('assets/manager/js/plugins/tables/datatables/extensions/pdfmake/pdfmake.min.js'),
			base_url('assets/manager/js/plugins/tables/datatables/extensions/pdfmake/vfs_fonts.min.js'),
			base_url('assets/manager/js/plugins/tables/datatables/extensions/dataTables.buttonshtml5.js'),
			base_url('assets/manager/js/plugins/tables/datatables/extensions/print.js'),
			base_url('assets/manager/js/plugins/tables/datatables/extensions/responsive.min.js'),
			base_url('assets/manager/js/plugins/tables/datatables/extensions/col_vis.min.js'),
			base_url('assets/manager/js/plugins/loaders/blockui.min.js'),
			base_url('assets/manager/js/plugins/forms/styling/uniform.min.js'),
			base_url('assets/manager/js/plugins/forms/validation/validate.min.js'),
			base_url('assets/manager/js/plugins/pickers/anytime.min.js'),
			'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
			base_url('assets/manager/js/bootstrap.bundle.min.js'),
			base_url('assets/manager/js/session_timeout.min.js'),
			base_url('assets/manager/js/plugins/notifications/pnotify.min.js'),
			base_url('assets/manager/js/plugins/notifications/jquery-confirm.js'),
			base_url('assets/manager/js/plugins/dropzone.min.js'),
			base_url('assets/manager/js/app.js'),
			base_url('assets/manager/js/confirm.js'),
			// base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),

		);


		$this->data['css_common'] = $this->css_common;
        $this->data['script_common'] = $this->script_common;
		/*BARRA DE NAVEGACION Y FOOTER GLOBAL*/

		$menu_act = $this->uri->segment(3);

		$data = array(
			'proveedores' => $this->Manager_model->getProveedores(),
			'script' => '',
			'page_title' => 'MY_CONTROLLER_CI_template',
			'page_datail' => 'Administrador',
			'class_act' => $this->router->fetch_class(),
			'method_act' => $this->router->fetch_method(),
		);

		if ($this->ion_auth->is_electro()){
			$this->nav = $this->load->view('manager/etiquetas/nav_electro', $data, TRUE);
		}else{
			$this->nav = $this->load->view('manager/etiquetas/nav2', $data, TRUE);
		}

		$this->footer = $this->load->view('manager/etiquetas/footer', $data, TRUE);
	}
}

class backend_controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			redirect('Login');
		} else {
			$this->load->helper(array('form', 'url'));
			$this->load->model('manager/Usuarios_model');
			$this->load->model('manager/manager_model');
			$this->load->library('form_validation');
			$this->user = $this->ion_auth->user()->row();
			$this->data['proveedores'] = $this->manager_model->getProveedores();
			$this->data['grupos'] = $this->ion_auth->groups()->result();
			$this->user->groups = $this->ion_auth->get_users_groups($this->user->id)->result();
		}
	}
}

class front_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->css_common = array(
			base_url('assets/web/css/plugins.css'),
			base_url('assets/web/css/style.css'),
		);


		$this->script_common = array(
			base_url('assets/web/js/plugins.js'),
			base_url('assets/web/js/theme.js'),
			base_url('assets/web/js/theme.js'),

		);
	}

}
