<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Consolidados extends backend_controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('manager/Electromecanica_model');

        $this->data['select_secretarias'] = $this->Electromecanica_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
        $this->data['select_dependencias'] = $this->Electromecanica_model->obtener_contenido_select('_dependencias', 'SELECCIONE DEPENDENCIA', 'dependencia', 'id ASC');
        $this->data['select_proyectos'] = $this->Electromecanica_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');
    }

    public function delete()
    {
        $this->db->where('nombre_archivo', $_REQUEST['file']);
        $this->db->delete('_consolidados_canon');

        $data = array(
            'consolidado' => 0
        );

        $this->db->where('nombre_archivo', $_REQUEST['file']);
        $this->db->update('_datos_api_canon', $data);

        $this->db->where('code', $_REQUEST['lote']);
        $this->db->update('_lotes_canon', $data);

        $response = array(
            'mensaje' => 'Datos borrados',
            'title' => '_consolidados_canon',
            'status' => 'success',
        );

        echo json_encode($response);
        exit();
    }


       

    

    public function list_dt_canon($tipo = null, $tabla = null, $search = '')
    {
        
        if ($this->input->is_ajax_request()) {
            $data = array();
            
            // Obtener los datos a mostrar en la tabla
            $memData = $this->Electromecanica_model->getRows($_POST);
    
            // Información de depuración
            $request = $_REQUEST;
            $consulta = $this->db->last_query();
            
    
            foreach ($memData as $r) {
                // Obtener datos adicionales según la lógica de la aplicación
                $indexador = $this->Electromecanica_model->getWhere('_indexaciones_canon', 'nro_cuenta="' . $r->nro_cuenta . '"');
    
                // Definir acciones (botones/ver/editar)
                $accionesVer = '<a title="ver archivo" href="/Electromecanica/Lecturas/Views/' . $r->id_lectura_api . '" class=" "><i class="icon-eye4" title="ver archivo"></i></a> ';
                $accionesDelete = '<span class="borrar_dato acciones" data-lote="' . $r->lote . '" data-file="' . $r->nombre_archivo . '"><a title="Borrar lote" href="#" class=""><i class="text-danger icon-trash" title="Borrar Datos"></i></a></span>';
    
                // Lógica para mostrar los valores correctos en caso de que los datos sean nulos o vacíos
                $acuerdo = $r->acuerdo_pago == '' ? 'SIN ACUERDO' : $r->acuerdo_pago;

                
    
                // Ajustar los datos para que correspondan con las columnas requeridas
                $data[] = array(
                    $r->proveedora,                      // Proveedor
                    $r->nro_factura,                     // Nro Factura
                    $r->nro_cuenta,                      // Nro Cuenta
                    $r->nro_medidor,                     // Nro Medidor
                    $r->dependencia,                     // Dependencia
                    $r->dependencia_direccion,           // Dirección de Consumo
                    $r->nombre_cliente,                  // Nombre Cliente
                    $r->consumo,                         // Consumo
                    $r->unidad_medida,                   // U. Med
                    $r->cosfi,                           // Cosfi
                    $r->tgfi,                            // Tgfi
                    $r->importe,                         // Importe Total
                    $r->mes_fc,  // Mes Fc (convertido a número de mes)
                    $r->anio_fc,  // Año Fc (convertido a número de año)
                    fecha_es($r->fecha_vencimiento, 'd-m-a', false), // Vencimiento
                    $accionesVer . $accionesDelete,
                    $r->id_proveedor, //id del proveedor
                    

                );
            }
    
            // Configuración de respuesta para DataTable
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $this->Electromecanica_model->countAll(),
                "recordsFiltered" => $this->Electromecanica_model->countFiltered($_POST),
                "data" => $data,
                "consulta" => $consulta,
                "mirequest" => $request,
            );
    
            // Enviar los datos en formato JSON
            echo json_encode($output);
        }
    }
    

    public function listados()
    {
        $css = array(
            base_url('assets/manager/js/plugins/daterange-picker/daterange-picker.css'),
        );

        $script = array(
            base_url('assets/manager/js/secciones/electromecanica/consolidados/listados.js')
        );

        $this->data['css_common'] = $this->css_common;
        $this->data['css'] = $css;
        $this->data['script_common'] = $this->script_common;
        $this->data['script'] = $script;

        $this->data['select_secretarias'] = $this->Electromecanica_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
        $this->data['select_programa'] = $this->Electromecanica_model->obtener_contenido_select('_programas', 'SELECCIONE PROGRAMA', 'descripcion', 'id ASC');
        $this->data['select_proyecto'] = $this->Electromecanica_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');
        $this->data['select_proveedores'] = $this->Electromecanica_model->obtener_contenido_select('_proveedores_canon', '', 'nombre', 'id ASC', false);
        $this->data['select_anios'] = $this->Electromecanica_model->obtener_contenido_select('_consolidados_canon', '', 'anio_fc', 'anio_fc ASC', false);
       // $this->data['select_tipo_pago'] = $this->Electromecanica_model->obtener_contenido_select('_tipo_pago', '', 'tip_nombre', 'tip_id ASC', false);
       // $this->data['select_periodo_contable'] = $this->Electromecanica_model->obtener_contenido_select('_consolidados_canon', '', 'periodo_contable', 'periodo_contable DESC', false);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $this->form_validation->set_rules('id_proyecto', 'ID Proyecto', 'trim|in_select[0]');
            $this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');
            $this->form_validation->set_rules('descripcion', 'Descripción', 'trim|required');

            if ($this->form_validation->run() != FALSE) {
                $datos = array(
                    'id_proyecto' => $this->input->post('id_proyecto'),
                    'id_interno' => $this->input->post('id_interno'),
                    'descripcion' => $this->input->post('descripcion'),
                );
                $this->Electromecanica_model->grabar_datos("_obras", $datos);
                redirect(base_url('Admin/Obras'));
            }
        }

        $this->data['content'] = $this->load->view('manager/secciones/electromecanica/consolidados/listados', $this->data, TRUE);
        $this->load->view('manager/head', $this->data);
        $this->load->view('manager/index', $this->data);
        $this->load->view('manager/footer', $this->data);
    }

    public function agregar($id = NULL)
    {
        $this->data['css_common'] = $this->css_common;
        $this->data['css'] = '';

        $this->data['script_common'] = $this->script_common;
        $this->data['script'] = '';

        if ($id) {
            $this->data['obra'] = $this->Electromecanica_model->getWhere('_obras', 'id = ' . $id, true);
        }

        $this->data['content'] = $this->load->view('manager/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method(), $this->data, TRUE);
        $this->load->view('manager/head', $this->data);
        $this->load->view('manager/index', $this->data);
        $this->load->view('manager/footer', $this->data);
    }



	public function debug_query() {
        // Asegúrate de cargar la base de datos
        $this->load->database();

        // Preparar la consulta
        $this->db->select(
            'CONCAT(_consolidados_canon.proveedor, " (", _consolidados_canon.codigo_proveedor,")") as proveedora,
             CONCAT(_consolidados_canon.jurisdiccion, " ", _consolidados_canon.id_programa) as sumajuris,
             _consolidados_canon.id as id_consolidado,
             UPPER(_consolidados_canon.secretaria),
             _consolidados_canon.proveedor,
             _consolidados_canon.*'
        );
        
        $this->db->from('_consolidados_canon');
        $this->db->order_by('_consolidados_canon.id', 'DESC');
        
        // Obtener la consulta compilada
        $compiled_query = $this->db->get_compiled_select();
        
        // Mostrar la consulta generada
        echo '<pre>' . htmlspecialchars($compiled_query) . '</pre>';
    }

    public function obtener_datos_grafico() {
        $datos_grafico = $this->Electromecanica_model->contar_registros_por_proveedor_canon();
        echo json_encode($datos_grafico);
    }

}
