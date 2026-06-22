<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard_financiero extends backend_controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->ion_auth->is_super() && !$this->is_financiero_only()) {
            show_error('No tiene permisos para ver el dashboard financiero.', 403);
        }

        $this->load->model('manager/Dashboard_financiero_model', 'dashboard_financiero');
    }

    public function index()
    {
        $anios = $this->dashboard_financiero->get_anios();
        $anio_default = !empty($anios) ? key($anios) : (int) date('Y');

        $filtros = array(
            'anio' => (int) $this->input->get('anio') ?: $anio_default,
            'id_proveedor' => $this->input->get('id_proveedor', true),
            'origen' => $this->input->get('origen', true),
        );

        $this->data['css_common'] = $this->css_common;
        $this->data['css'] = array();
        $this->data['script_common'] = $this->script_common;
        $this->data['script'] = array();
        $this->data['filtros'] = $filtros;
        $this->data['select_anios'] = $anios;

        $this->load->view('manager/head', $this->data);
        $this->load->view('manager/secciones/dashboard_financiero/index', $this->data);
        $this->load->view('manager/footer', $this->data);
    }

    public function api_filtros()
    {
        $this->json_response(array(
            'status' => 'success',
            'filtros' => $this->dashboard_financiero->get_filtros($this->api_filtros_input()),
        ));
    }

    public function api_resumen()
    {
        $this->json_response(array(
            'status' => 'success',
            'data' => $this->dashboard_financiero->get_api_resumen($this->api_filtros_input()),
        ));
    }

    public function api_evolucion()
    {
        $this->json_response(array(
            'status' => 'success',
            'data' => $this->dashboard_financiero->get_evolucion_comparativa($this->api_filtros_input()),
        ));
    }

    public function api_ranking()
    {
        $dimension = $this->input->get('dimension', true) ?: 'secretaria';
        $limite = (int) ($this->input->get('limite', true) ?: 10);

        $this->json_response(array(
            'status' => 'success',
            'dimension' => $dimension,
            'data' => $this->dashboard_financiero->get_ranking($dimension, $this->api_filtros_input(), $limite),
        ));
    }

    public function api_drilldown()
    {
        $nivel = (int) ($this->input->get('nivel', true) ?: 0);
        $limite = (int) ($this->input->get('limite', true) ?: 50);

        $this->json_response(array(
            'status' => 'success',
            'data' => $this->dashboard_financiero->get_drilldown($nivel, $this->api_filtros_input(), $limite),
        ));
    }

    public function api_consumos()
    {
        $dimension = $this->input->get('dimension', true) ?: 'proveedor';
        $limite = (int) ($this->input->get('limite', true) ?: 20);

        $this->json_response(array(
            'status' => 'success',
            'dimension' => $dimension,
            'data' => $this->dashboard_financiero->get_consumos_medibles($this->api_filtros_input(), $dimension, $limite),
        ));
    }

    public function api_crecimiento()
    {
        $dimension = $this->input->get('dimension', true) ?: 'secretaria';
        $limite = (int) ($this->input->get('limite', true) ?: 10);

        $this->json_response(array(
            'status' => 'success',
            'dimension' => $dimension,
            'data' => $this->dashboard_financiero->get_crecimiento_yoy($dimension, $this->api_filtros_input(), $limite),
        ));
    }

    public function api_pareto()
    {
        $dimension = $this->input->get('dimension', true) ?: 'proveedor';
        $limite = (int) ($this->input->get('limite', true) ?: 10);

        $this->json_response(array(
            'status' => 'success',
            'data' => $this->dashboard_financiero->get_pareto($dimension, $this->api_filtros_input(), $limite),
        ));
    }

    public function api_forecast()
    {
        $this->json_response(array(
            'status' => 'success',
            'data' => $this->dashboard_financiero->get_forecast_anual($this->api_filtros_input()),
        ));
    }

    public function api_servicios()
    {
        $this->json_response(array(
            'status' => 'success',
            'data' => $this->dashboard_financiero->get_composicion_servicios($this->api_filtros_input()),
        ));
    }

    public function api_comparativo()
    {
        $this->json_response(array(
            'status' => 'success',
            'data' => $this->dashboard_financiero->get_comparativo($this->api_filtros_input()),
        ));
    }

    public function api_eficiencia()
    {
        $this->json_response(array(
            'status' => 'success',
            'data' => $this->dashboard_financiero->get_eficiencia_energetica($this->api_filtros_input()),
        ));
    }

    private function api_filtros_input()
    {
        return array(
            'anio' => $this->input->get('anio', true),
            'mes' => $this->input->get('mes', true),
            'mes_desde' => $this->input->get('mes_desde', true),
            'mes_hasta' => $this->input->get('mes_hasta', true),
            'periodo_a_anio' => $this->input->get('periodo_a_anio', true),
            'periodo_a_mes_desde' => $this->input->get('periodo_a_mes_desde', true),
            'periodo_a_mes_hasta' => $this->input->get('periodo_a_mes_hasta', true),
            'periodo_b_anio' => $this->input->get('periodo_b_anio', true),
            'periodo_b_mes_desde' => $this->input->get('periodo_b_mes_desde', true),
            'periodo_b_mes_hasta' => $this->input->get('periodo_b_mes_hasta', true),
            'origen' => $this->input->get('origen', true),
            'proveedor' => $this->input->get('proveedor', true),
            'id_proveedor' => $this->input->get('id_proveedor', true),
            'secretaria' => $this->input->get('secretaria', true),
            'programa' => $this->input->get('programa', true),
            'proyecto' => $this->input->get('proyecto', true),
            'dependencia' => $this->input->get('dependencia', true),
            'cuenta' => $this->input->get('cuenta', true),
            'medidor' => $this->input->get('medidor', true),
            'modo' => $this->input->get('modo', true),
            'ventana' => $this->input->get('ventana', true),
            'problema' => $this->input->get('problema', true),
            'tarifa' => $this->input->get('tarifa', true),
            'segmento' => $this->input->get('segmento', true),
            'objeto' => $this->input->get('objeto', true),
            'unidad_medida' => $this->input->get('unidad_medida', true),
        );
    }

    private function json_response($payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }
}
