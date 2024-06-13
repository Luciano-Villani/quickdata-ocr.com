<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Proveedores extends backend_controller
{

    public function __construct()
    {
        parent::__construct();


        if (!$this->ion_auth->logged_in() && (!$this->ion_auth->is_admin() || !$this->ion_auth->is_electro())) {

            redirect('Login');
        } else {
            $this->load->helper('file');
            $this->load->model('manager/Lecturas_model');
            $this->load->model('manager/Electromecanica_model', 'electromecanica');
        }
    }



    public function list_dt()
    {

        // $query = $this->db->select('*')->get('_proveedores');

        // foreach ($query->result()  as $r) {

        // 	$this->db->set('detalle_gasto', strtoupper($r->detalle_gasto));
        // 	$this->db->where('id', $r->id);
        // 	$this->db->update('_proveedores');
        // }


        $memData = $this->electromecanica->getRows($_POST);


        $data = $row = array();

        foreach ($memData as $r) {

            // chequeo si el proveedor esta indexado
            $indexado = 0;
            $claseindexado = 'success';
            if ($this->electromecanica->checkProveedor($r->id)) {
                $indexado = 1;
                $claseindexado = 'danger';
            }


            if ($r->activo == '1' && $r->urlapi != '') {
                $estado = '<span class="badge badge-success">activo</span>';
            } else {
                $estado = '<span class="badge badge-danger">inactivo</span>';
            }

            $fecha_alta = fecha_es($r->fecha_alta, TRUE);
            $accionesEdit = '<span data-id_proveedor="' . $r->id . '" class="editar_proveedor acciones" data-indexado="' . $indexado . '"><a title="Editar lot" href="#"  class=""><i class=" text-warningr  icon-pencil " title="Editar"></i> </a> </span>';
            $accionesDelete = '<span data-id_proveedor="' . $r->id . '" class="borrar_proveedor acciones" data-indexado="' . $indexado . '"><a title="Borrar " href="#"  class=""><i class=" text-' . $claseindexado . ' icon-trash " title="Borrar"></i> </a> </span>';

            $data[] = array(
                $r->id,
                $r->codigo,
                $r->nombre,
                $r->objeto_gasto,
                $r->detalle_gasto,
                $fecha_alta,
                $estado,
                $accionesEdit . $accionesDelete
            );
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->electromecanica->countAll(),
            "recordsFiltered" => $this->electromecanica->countFiltered($_POST),
            "data" => $data,
        );

        echo json_encode($output);
    }

    public function index()
    {

        $this->data['tabla'] = '_proveedores_canon';
        $this->data['page_title'] = 'Proveedores CANON';
        $script = array(
            base_url('assets/manager/js/plugins/tables/datatables/datatables.js'),
            base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
            base_url('assets/manager/js/secciones/electromecanica/' . $this->router->fetch_class() . '.js'),
        );
        
        $this->data['css_common'] = $this->css_common;
        $this->data['css'] = '';
        
        $this->data['script_common'] = $this->script_common;
        $this->data['script'] = $script;

        if (isset($_REQUEST['id']) && $_REQUEST['id'] != NULL && $_REQUEST['id'] != '') {
            
            // $this->BtnText = 'Editar';
            $editData = $this->electromecanica->get_data('_proveedores_canon', $_REQUEST['id']);
            $this->data['codigo'] = $editData->codigo;
            $this->data['detalle_gasto'] = $editData->detalle_gasto;
            $this->data['nombre'] = $editData->nombre;
            $this->data['objeto_gasto'] = $editData->objeto_gasto;
            $this->data['unidad_medida'] = $editData->unidad_medida;
            $this->data['urlapi'] = $editData->urlapi;
        }
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            
            $this->form_validation->set_rules('codigo', 'Código de proveedor', 'trim|required');
            $this->form_validation->set_rules('nombre', 'Nombre de proveedor', 'trim|required');
            $this->form_validation->set_rules('objeto_gasto', 'Objeto del gasto', 'trim|required');
            $this->form_validation->set_rules('detalle_gasto', 'Detalle del gasto', 'trim|required');
            $this->form_validation->set_rules('urlapi', 'URL API PROVEEDOR', 'trim|required');
            $this->form_validation->set_rules('unidad_medida', 'Unidad de Medido / Plan', 'trim|required');
            
            if ($this->form_validation->run() != FALSE) {
                
                if (isset($_REQUEST['id']) && $_REQUEST['id'] != NULL) {
                    
                    $id = $_POST["id"];
                    unset($_REQUEST["id"]);
                    $grabar_datos_array = array(
                        'seccion' => 'Actualización datos ' . $this->router->fetch_class(),
                        'mensaje' => 'Datos Actualizados ',
                        'estado' => 'success',
                        'status' => 'success',
                    );
                    $this->session->set_userdata('save_data', $grabar_datos_array);
                    $this->db->update($this->data['tabla'], $_REQUEST, array('id' => $id));
                    $_REQUEST = '';
               
                } else {
            
                    unset($_REQUEST["id"]);
                    $datos = array(
                        'codigo' => $this->input->post('codigo'),
                        'nombre' => strtoupper($this->input->post('nombre')),
                        'objeto_gasto' => $this->input->post('objeto_gasto'),
                        'detalle_gasto' => strtoupper($this->input->post('detalle_gasto')),
                        'urlapi' => $this->input->post('urlapi'),
                        'unidad_medida' => strtoupper($this->input->post('unidad_medida')),
                    );
                    $this->electromecanica->grabar_datos("_proveedores_canon", $datos);
                    redirect(base_url('Electromecanica/Proveedores'));
                }
            }
            $this->BtnText = 'Agregar';
        }

        $this->data['content'] = $this->load->view('manager/secciones/electromecanica/' . $this->router->fetch_class(), $this->data, TRUE);

        $this->load->view('manager/head', $this->data);
        $this->load->view('manager/index', $this->data);
        $this->load->view('manager/footer', $this->data);
    }


    public function checkApiUrl()
    {
        $proveedor = $this->Manager_model->getWhere('_proveedores_canon', 'id=' . $_POST['id_proveedor']);
        if ($proveedor->urlapi != '') {
            $status = 'true';
        } else {
            $status = 'false';
        }
        $response = array(
            'status' => $status,
            'proveedor' => $proveedor
        );
        echo json_encode($response);
    }
}
