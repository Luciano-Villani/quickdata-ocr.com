<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auditoria_edenor extends backend_controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->ion_auth->logged_in() || !($this->ion_auth->is_admin() || $this->ion_auth->is_super() || $this->ion_auth->is_electro())) {
            redirect('Login');
        }

        $this->load->model('manager/Edenor_auditoria_model', 'edenor_auditoria');
    }

    public function index()
    {
        $this->data['css_common'] = $this->css_common;
        $this->data['css'] = array();
        $this->data['script_common'] = $this->script_common;
        $this->data['script'] = array(
            base_url('assets/manager/js/secciones/electromecanica/auditoria_edenor.js'),
        );
        $this->data['periodos'] = $this->edenor_auditoria->periodos();

        $this->data['content'] = $this->load->view('manager/secciones/electromecanica/auditoria_edenor', $this->data, true);
        $this->load->view('manager/head', $this->data);
        $this->load->view('manager/index', $this->data);
        $this->load->view('manager/footer', $this->data);
    }

    public function importar()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (empty($_FILES['archivo_txt']['tmp_name'])) {
            return $this->json(array(
                'status' => 'error',
                'mensaje' => 'Seleccione un archivo TXT de Edenor.',
            ));
        }

        $nombre = $_FILES['archivo_txt']['name'];
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if ($extension !== 'txt') {
            return $this->json(array(
                'status' => 'error',
                'mensaje' => 'El archivo debe tener extension .txt.',
            ));
        }

        $contenido = file_get_contents($_FILES['archivo_txt']['tmp_name']);
        $reemplazar = (int) $this->input->post('reemplazar') === 1;
        $user_id = isset($this->user->id) ? (int) $this->user->id : null;

        $resultado = $this->edenor_auditoria->importar_txt($nombre, $contenido, $user_id, $reemplazar);
        if ($resultado['status'] === 'success' && !empty($resultado['periodo'])) {
            $archivo_guardado = $this->guardar_txt_original($_FILES['archivo_txt']['tmp_name'], $resultado['periodo']);
            if ($archivo_guardado) {
                $this->edenor_auditoria->actualizar_archivo_original($resultado['periodo'], $archivo_guardado);
                $resultado['archivo_original'] = $archivo_guardado;
            } else {
                $resultado['warning'] = 'La auditoria se guardo, pero no se pudo conservar el TXT original.';
            }
        }

        $resultado['periodos'] = $this->edenor_auditoria->periodos();
        return $this->json($resultado);
    }

    public function comparar()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        return $this->json(array(
            'status' => 'success',
            'data' => $this->edenor_auditoria->comparar(
                $this->input->post('periodo_actual', true),
                $this->input->post('periodo_base', true)
            ),
            'periodos' => $this->edenor_auditoria->periodos(),
        ));
    }

    public function evolutivo()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        return $this->json(array(
            'status' => 'success',
            'data' => $this->edenor_auditoria->evolutivo(),
        ));
    }

    public function reporte()
    {
        $periodo_actual = $this->input->get('periodo_actual', true);
        $periodo_base = $this->input->get('periodo_base', true);
        $comparacion = $this->edenor_auditoria->comparar($periodo_actual, $periodo_base);

        if (empty($comparacion['periodo_actual'])) {
            die("<script>alert('No hay periodos importados para generar el reporte.'); window.close();</script>");
        }

        $filename = 'auditoria_edenor_' . $this->slug_filename($comparacion['periodo_actual_label'])
            . '_vs_' . $this->slug_filename($comparacion['periodo_base_label']) . '.xls';
        $html = $this->html_reporte($comparacion);

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF" . $html;
        exit;
    }

    private function json($payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function guardar_txt_original($tmp_path, $periodo)
    {
        $directorio = FCPATH . 'uploader/edenor_auditoria';
        if (!is_dir($directorio) && !mkdir($directorio, 0777, true)) {
            return false;
        }

        $archivo_relativo = 'uploader/edenor_auditoria/VL' . $periodo . '.txt';
        $archivo_destino = FCPATH . $archivo_relativo;

        if (!copy($tmp_path, $archivo_destino)) {
            return false;
        }

        return $archivo_relativo;
    }

    private function html_reporte($data)
    {
        $kpis = isset($data['kpis']) ? $data['kpis'] : array();
        $titulo = 'Auditoria Datos Edenor';
        $subtitulo = $data['periodo_actual_label'] . ' vs ' . $data['periodo_base_label'];

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
            . 'body{font-family:Arial,sans-serif;color:#061a4f;}'
            . 'h1{font-size:24px;margin:0 0 4px;}h2{font-size:16px;margin:22px 0 8px;color:#0b2a66;}'
            . '.muted{color:#526385;font-size:12px;}'
            . '.kpi-table td{border:1px solid #dce6f5;padding:10px 14px;vertical-align:top;}'
            . '.kpi-label{font-size:11px;color:#526385;font-weight:bold;text-transform:uppercase;}'
            . '.kpi-value{font-size:24px;font-weight:bold;color:#061a4f;}'
            . 'table{border-collapse:collapse;width:100%;margin-top:8px;}'
            . 'th{background:#c6d2e3;color:#061a4f;font-weight:bold;border:1px solid #9fb0c7;padding:7px;}'
            . 'td{border:1px solid #d8dee8;padding:7px;}'
            . '.alta{color:#16804a;font-weight:bold}.baja{color:#d93232;font-weight:bold}.bimestral{color:#b66b00;font-weight:bold}.recategorizada{color:#075cf7;font-weight:bold}'
            . '.footer{margin-top:22px;font-size:11px;color:#526385;}'
            . '</style></head><body>';

        $html .= '<h1>' . $this->html($titulo) . '</h1>';
        $html .= '<div class="muted">Reporte de control mensual de cuentas Edenor - ' . $this->html($subtitulo) . '</div>';
        $html .= '<div class="muted">Generado: ' . date('d/m/Y H:i') . '</div>';

        $html .= '<h2>Resumen</h2>';
        $html .= '<table class="kpi-table"><tr>'
            . $this->kpi_cell('Total ' . $this->periodo_corto($data['periodo_actual_label']), $kpis['actual_total'])
            . $this->kpi_cell('Total ' . $this->periodo_corto($data['periodo_base_label']), $kpis['base_total'])
            . $this->kpi_cell('Altas', $kpis['nuevas'])
            . $this->kpi_cell('Bajas', $kpis['faltantes'])
            . $this->kpi_cell('Bimestrales', $kpis['bimestrales'])
            . $this->kpi_cell('Recategorizadas', $kpis['cambios_tarifa'])
            . '</tr></table>';

        $html .= '<h2>Resultado por tarifa</h2>';
        $html .= '<table><thead><tr><th>Tarifa</th><th>' . $this->html($this->periodo_corto($data['periodo_base_label'])) . '</th><th>' . $this->html($this->periodo_corto($data['periodo_actual_label'])) . '</th><th>Resultado</th></tr></thead><tbody>';
        foreach ($data['tarifas_comparativo'] as $row) {
            $html .= '<tr><td>' . $this->html($row['tarifa']) . '</td><td>' . (int) $row['base'] . '</td><td>' . (int) $row['actual'] . '</td><td>' . $this->html($this->variacion_label($row['variacion'])) . '</td></tr>';
        }
        $html .= '</tbody></table>';

        $html .= '<h2>Altas, bajas, bimestrales y recategorizadas</h2>';
        $html .= '<table><thead><tr><th>Movimiento</th><th>Cuenta</th><th>Tarifa anterior</th><th>Tarifa actual</th><th>Factura</th></tr></thead><tbody>';
        if (empty($data['movimientos'])) {
            $html .= '<tr><td colspan="5">Sin movimientos para mostrar</td></tr>';
        } else {
            foreach ($data['movimientos'] as $row) {
                $tipo = isset($row['tipo_movimiento']) ? $row['tipo_movimiento'] : '-';
                $class = strtolower($this->slug_filename($tipo));
                $html .= '<tr>'
                    . '<td class="' . $this->html($class) . '">' . $this->html($tipo) . '</td>'
                    . '<td>' . $this->html($row['nro_cuenta']) . '</td>'
                    . '<td>' . $this->html($row['tarifa_anterior']) . '</td>'
                    . '<td>' . $this->html($row['tarifa_actual']) . '</td>'
                    . '<td>' . $this->html($row['nro_factura']) . '</td>'
                    . '</tr>';
            }
        }
        $html .= '</tbody></table>';

        $html .= '<div class="footer">Nota: las cuentas bimestrales se determinan usando periodicidad_meses configurado en _indexaciones_canon. Si el dato operativo cambia, el resultado del reporte cambia con esa configuracion.</div>';
        $html .= '</body></html>';

        return $html;
    }

    private function kpi_cell($label, $value)
    {
        return '<td><div class="kpi-label">' . $this->html($label) . '</div><div class="kpi-value">' . $this->html($value) . '</div></td>';
    }

    private function variacion_label($value)
    {
        $value = (int) $value;
        if ($value > 0) {
            return '+' . $value;
        }

        return (string) $value;
    }

    private function periodo_corto($label)
    {
        $parts = explode(' ', (string) $label);
        return isset($parts[0]) && $parts[0] !== '' ? $parts[0] : $label;
    }

    private function html($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function slug_filename($valor)
    {
        $valor = trim((string) $valor);
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
        if ($convertido !== false) {
            $valor = $convertido;
        }
        $valor = preg_replace('/[^A-Za-z0-9]+/', '_', $valor);
        $valor = trim($valor, '_');
        return $valor !== '' ? strtolower($valor) : 'reporte';
    }
}
