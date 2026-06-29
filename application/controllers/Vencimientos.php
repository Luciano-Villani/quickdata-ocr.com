<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vencimientos extends backend_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('manager/Vencimientos_model', 'vencimientos');
    }

    public function index($modulo = 'proveedores')
    {
        $modulo = $modulo === 'electromecanica' ? 'electromecanica' : 'proveedores';

        if ($modulo === 'proveedores' && $this->ion_auth->is_electro() && !$this->ion_auth->is_admin() && !$this->ion_auth->is_super()) {
            redirect('Electromecanica/Vencimientos');
        }

        $anio = (int) $this->input->get('anio');
        $mes = (int) $this->input->get('mes');
        $anioActual = (int) date('Y');
        if ($anio < 2020 || $anio > $anioActual + 2) {
            $anio = null;
        }
        if ($mes < 1 || $mes > 12) {
            $mes = null;
        }

        $this->data['modulo'] = $modulo;
        $this->data['calendario'] = $this->vencimientos->get_calendario($modulo, $anio, $mes);
        $this->data['titulo_pagina'] = $modulo === 'electromecanica'
            ? 'Calendario de vencimientos - Electromecanica'
            : 'Calendario de vencimientos - Proveedores';
        $this->data['css_common'] = $this->css_common;
        $this->data['css'] = array();
        $this->data['script_common'] = $this->script_common;
        $this->data['script'] = array(
            base_url('assets/manager/js/secciones/vencimientos/calendario.js'),
        );

        $this->data['content'] = $this->load->view('manager/secciones/vencimientos/calendario', $this->data, true);
        $this->load->view('manager/head', $this->data);
        $this->load->view('manager/index', $this->data);
        $this->load->view('manager/footer', $this->data);
    }
}
