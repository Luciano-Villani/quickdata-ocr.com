<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alertas extends backend_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('manager/Vencimientos_model', 'vencimientos');
    }

    public function vencimientos_topbar($modulo = 'proveedores')
    {
        $modulo = $modulo === 'electromecanica' ? 'electromecanica' : 'proveedores';
        $alertas = $this->vencimientos->get_alertas_topbar($modulo);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => 'success', 'data' => $alertas)));
    }
}
