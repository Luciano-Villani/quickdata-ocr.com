<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Consolidados extends backend_controller
{
	function __construct()
	{
		parent::__construct();


		if (!$this->ion_auth->logged_in()) {
			redirect('Login');
		} else {
			$this->load->model('Ion_auth_model');
			$this->load->model('manager/Usuarios_model');
			$this->load->model('manager/Secretarias_model');
			$this->load->model('manager/Dependencias_model');
			$this->load->model('manager/Proyectos_model');
			$this->load->model('manager/Obras_model');

			$this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
			$this->data['select_dependencias'] = $this->Manager_model->obtener_contenido_select('_dependencias', 'SELECCIONE DEPENDENCIA', 'dependencia', 'id ASC');
			$this->data['select_proyectos'] = $this->Manager_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');

		}

	}


	public function delete()
	{
		$this->db->where('nombre_archivo', $_REQUEST['file']);
		$this->db->delete('_consolidados');

		$data = array(
			'consolidado' =>0
		);

		$this->db->where('nombre_archivo', $_REQUEST['file']);
		$this->db->update('_datos_api', $data);

		$this->db->where('code', $_REQUEST['lote']);
		$this->db->update('_lotes', $data);

		$response = array(
			'mensaje' => 'Datos borrados',
			'title' => '_consolidados',
			'status' => 'success',
		);

		echo json_encode($response);
		exit();
	}
	public function list_dt($tipo = null, $tabla = null, $search = '')
{
    if ($this->input->is_ajax_request()) {

        $data = $row = array();
        // Asumo que Manager_model->getRows($_POST) ya trae la columna 'seguimiento' de la tabla _consolidados
        $memData = $this->Manager_model->getRows($_POST); 

        $request = $_REQUEST;
        $consulta = $this->db->last_query();

        foreach ($memData as $r) {
            
            // -----------------------------------------------------------
            // 🌟🌟🌟 LÓGICA DEL SEMÁFORO DE SEGUIMIENTO 🌟🌟🌟
            // -----------------------------------------------------------
            $icono_seguimiento = '';
            $clase_icono = 'icon-alarm'; // Usaremos el icono de alarma o warning para los tres estados.

            // 1. Caso: Sin Comentarios (GRIS)
            if (empty($r->comentarios)) {
                $clase_color = 'text-muted'; // Gris
                $titulo = 'Sin comentarios de seguimiento';
            
            // 2. Caso: Con Comentarios Y En Seguimiento (ROJO) -> seguimiento = 1
            } elseif ($r->seguimiento == 1) {
                $clase_color = 'text-danger'; // Rojo
                $titulo = '¡En Seguimiento!';

            // 3. Caso: Con Comentarios Y Resuelto/Archivado (VERDE) -> seguimiento = 0
            } else { 
                $clase_color = 'text-success'; // Verde
                $titulo = 'Seguimiento resuelto/archivado';
            }

            // Construimos el HTML final del ícono de seguimiento
            $icono_seguimiento = '<i class="' . $clase_icono . ' ' . $clase_color . '" title="' . $titulo . '"></i> ';
            
            // -----------------------------------------------------------
            // 🌟🌟🌟 CORRECCIÓN DEL ENLACE "VER" 🌟🌟🌟
            // Mantenemos la ruta correcta que creamos: Admin/Consolidados/ver/ID
            // NOTA: Colocamos el ícono ANTES de la acción "Ver"
            $accionesVer = '<a title="Ver detalles y seguimiento" href="' . base_url('Admin/Consolidados/ver/' . $r->id) . '" class=" ">' . $icono_seguimiento . '<i class="icon-eye4" title="Ver archivo"></i> </a> ';
            
            $accionesDelete = '<span class="borrar_dato acciones" data-lote="' . $r->lote . '" data-file="' . $r->nombre_archivo . '"><a title="Borrar lote" href="#" class=""><i class=" text-danger icon-trash " title="Borrar Datos"></i> </a> </span>';
            $punto =".";
            
            // ... (Tu lógica de formato de programa y proyecto) ...
            if(strlen($r->id_interno_programa) == 1){
                $r->id_interno_programa = '0'.$r->id_interno_programa;
            }
            if($r->id_interno_proyecto != '0' ){

                if(strlen($r->id_interno_proyecto) == 1){
                    
                    $r->id_interno_proyecto = ".0".strval($r->id_interno_proyecto);
                }else{
                    $r->id_interno_proyecto = ".".$r->id_interno_proyecto;
                }
                
            }else{
                $r->id_interno_proyecto = '';
            }

            if($r->acuerdo_pago == ''){
                $acuerdo = 'SIN ACUERDO';
                }else{

                    $acuerdo = $r->acuerdo_pago;
                }
            
            // Tu array de datos
            $data[] = array(
                strtoupper($r->periodo_contable),
                $r->proveedora,
                $r->expediente,
                $r->secretaria,
                $r->jurisdiccion,
                $r->id_interno_programa.$r->id_interno_proyecto,
                $r->jurisdiccion,
                $r->objeto,
                $r->dependencia,
                $r->dependencia_direccion,
                $r->tipo_pago,
                $acuerdo,
                $r->nro_cuenta,
                $r->nro_factura,
                $r->periodo_del_consumo,
                fecha_es($r->fecha_vencimiento,'d-m-a', false),
                fecha_es($r->preventivas,'d-m-a', false),
                $r->importe,
                // Columna final con las acciones y el ícono
                $accionesVer . $accionesDelete
            );
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Manager_model->countAll(),
            "recordsFiltered" => $this->Manager_model->countFiltered($_POST),
            "data" => $data,
            "consulta" => $consulta,
            "mirequest" => $request,
        );
        // Output to JSON format
        echo json_encode($output);
    }
}

	public function listados()
	{
		$css = array(
			base_url('assets/manager/js/plugins/daterange-picker/daterange-picker.css'),
		);

		$script = array(
			base_url('assets/manager/js/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method() . '.js'),
		);


		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = $css;

		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;
		$this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'id ASC');
		$this->data['select_programa'] = $this->Manager_model->obtener_contenido_select('_programas', 'SELECCIONE PROGRAMA', 'descripcion', 'id ASC');
		;
		$this->data['select_proyecto'] = $this->Manager_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');
		;
		$this->data['select_proveedores'] = $this->Manager_model->obtener_contenido_select('_proveedores', '', 'nombre', 'id ASC', false);
		$this->data['select_tipo_pago'] = $this->Manager_model->obtener_contenido_select('_tipo_pago', '', 'tip_nombre', 'tip_id ASC', false);
		$this->data['select_periodo_contable'] = $this->Manager_model->obtener_contenido_select('_consolidados', '', 'periodo_contable', 'periodo_contable DESC', false);


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
				$this->Manager_model->grabar_datos("_obras", $datos);
				redirect(base_url('Admin/Obras'));

			}

		}


		$this->data['content'] = $this->load->view('manager/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method(), $this->data, TRUE);

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index', $this->data);
		$this->load->view('manager/footer', $this->data);
	}

	public function agregar($id = NULL)
	{

		$this->data['css_common'] = $this->css_common;
		$this->data['css'] = '';

		$script = array(
			base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
			base_url('assets/manager/js/plugins/forms/styling/uniform.min.js'),
			base_url('assets/manager/js/secciones/' . strtolower($this->router->fetch_class()) . '/' . $this->router->fetch_method() . '.js'),
			// base_url('assets/manager/js/secciones/'.$this->router->fetch_class().'.js'),
		);
		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;


		if ($this->form_validation->run() == FALSE) {

			$this->data['content'] = $this->load->view('manager/secciones/proyectos/' . $this->router->fetch_method(), $this->data, TRUE);

			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);

		} else {
			$datos = array(
				'id_secretaria' => $this->input->post('id_secretaria'),
				'id_programa' => $this->input->post('id_programa'),
				'id_interno' => $this->input->post('id_interno'),
				'descripcion' => $this->input->post('descripcion'),
			);

			$this->Proyectos_model->grabar_datos("_proyectos", $datos);
			redirect(base_url('Admin/Proyectos'));

		}



	}


	public function check_username($str)
	{
		if (!$this->ion_auth->username_check($str)) {
			return TRUE;
		} else {
			$this->form_validation->set_message('check_username', 'El usuario ya se encuentra registrado');
			return FALSE;
		}
	}
	public function check_email($str)
	{
		if (!$this->ion_auth->email_check($str)) {
			return TRUE;
		} else {
			$this->form_validation->set_message('check_email', 'El email ya se encuentra registrado');
			return FALSE;
		}
	}

	// application/controllers/Consolidados.php

public function guardar_seguimiento() { 
    
    // Aseguramos que la solicitud sea POST y que el ID del registro exista
    if ($this->input->post('id_registro')) {
        
        $id_consolidado = $this->input->post('id_registro'); 
        $comentarios = $this->input->post('comentarios');
        
        // El checkbox se llama 'en_seguimiento' en la vista que creamos (ver_consolidado_proveedor.php)
        // Si el checkbox está marcado, el valor es 'on' (o lo que envíe el navegador), si no, es NULL.
        $en_seguimiento = $this->input->post('en_seguimiento'); 
        
        // Lógica de Semáforo: 1 si está marcado (en seguimiento), 0 si está desmarcado (resuelto).
        $seguimiento = ($en_seguimiento == 'on' || $en_seguimiento == 1) ? 1 : 0; 

        // Creamos el array de datos para el modelo.
        $data = [
            'id_registro' => $id_consolidado, // Usaremos esto para el WHERE en el modelo
            'comentarios' => $comentarios,
            'seguimiento' => $seguimiento, 
        ];

        // Llama al modelo para guardar/actualizar los datos.
        // ASUMO que tienes un método en el modelo llamado 'guardar_seguimiento_proveedor' 
        // o similar para evitar conflictos con otras secciones. 
        // Si no tienes uno, usaremos 'guardar_comentarios' (del paso anterior).
        if ($this->Consolidados_model->guardar_comentarios($data)) { 
            
            // Redirección CORRECTA, apunta al controlador/función que carga la vista de detalle
            $this->session->set_flashdata('mensaje', 'Seguimiento guardado correctamente.');
            redirect('Admin/Consolidados/ver/' . $id_consolidado); 
            
        } else {
            // Error en la DB
            $this->session->set_flashdata('error', 'Error al guardar el seguimiento en la base de datos.');
            redirect('Admin/Consolidados/ver/' . $id_consolidado);
        }
    } else {
        // Si no se envió el ID del registro (acceso directo o formulario incompleto)
        $this->session->set_flashdata('error', 'Acceso no autorizado o datos incompletos.');
        redirect('Admin/Consolidados'); 
    }
}

    public function editar_comentario($id_consolidado) {
        // Obtener los datos del comentario y seguimiento desde la base de datos
        $comentario = $this->Electromecanica_model->get_comentario_por_id($id_consolidado);
    
        // Si no existe el consolidado, mostrar error
        if (!$comentario) {
            show_404();
        }
    
        // Cargar la vista con los datos
        $data['comentario'] = $comentario;
        $data['id'] = $id_consolidado;  // Pasar el ID al formulario
    
        $this->load->view('editar_comentario', $data);
    }

	// application/controllers/Consolidados.php

/**
 * Muestra la vista de edición/detalle para un registro consolidado de Proveedores.
 * Carga el registro, el PDF, y los datos de seguimiento (comentarios, etc.).
 * Esta función es llamada por la ruta: Admin/Consolidados/ver/ID
 *
 * @param int $id ID del registro en la tabla _consolidados.
 */

public function ver($id = NULL)
{
    if (is_null($id)) {
        show_404(); 
        return;
    }
    $id = intval($id);

    // Obtener los detalles del consolidado de la tabla _consolidados.
    // get_comentario_por_id en el modelo de Proveedores trae toda la fila, incluyendo los campos de seguimiento.
    $consolidado = $this->Consolidados_model->get_comentario_por_id($id); 

    if (!$consolidado) {
        show_404(); 
        return;
    }

    // Pasamos el objeto consolidado con todos sus datos como $result para la nueva vista.
    // La nueva vista utilizará $consolidado = $result; para imitar la estructura de Electromecánica.
    $this->data['result'] = $consolidado; 
    
    // Si necesitas cargar select_proveedores u otros datos para la vista, hazlo aquí.

    $this->data['css_common'] = $this->css_common;
    $this->data['script_common'] = $this->script_common;

    // 🌟🌟🌟 CARGA DE LA NUEVA VISTA 🌟🌟🌟
    // Asegúrate de usar la ruta correcta a tu nueva vista.
    $this->data['content'] = $this->load->view('manager/secciones/consolidados/ver_consolidado_proveedor', $this->data, TRUE); 

    // Cargar las vistas de cabecera, cuerpo y pie
    $this->load->view('manager/head', $this->data);
    $this->load->view('manager/index', $this->data);
    $this->load->view('manager/footer', $this->data);
}
/**
 * Devuelve la lista de registros de Proveedores en seguimiento. Llamada por AJAX para el menú desplegable.
 * URL: /Consolidados/obtener_lista_seguimiento_ajax
 */
// application/controllers/Consolidados.php

// application/controllers/Consolidados.php

public function obtener_lista_seguimiento_ajax()
{
    if ($this->input->is_ajax_request()) {
        
        $registros = $this->Consolidados_model->get_seguimiento_proveedores_list(); 
        
        // 🌟 Revertimos a cargar la vista y obtener el HTML
        $html_listado = $this->load->view(
            'manager/etiquetas/lista_seguimiento_ajax', // <-- ¡Tu ruta corregida!
            ['registros' => $registros], 
            TRUE // Retorna el contenido como string
        );

        // Devolvemos el HTML generado bajo la clave 'html'
        echo json_encode(['status' => 'success', 'html' => $html_listado]);
        
    } else {
        show_404();
    }
}
// En Consolidados.php

public function get_seguimiento_count_ajax()
{
    // Verifica que la solicitud sea AJAX
    if (!$this->input->is_ajax_request()) {
        show_404();
    }
    
    // Asume que Consolidados_model ya está cargado en el constructor del controlador,
    // o cárgalo aquí si es necesario: $this->load->model('Consolidados_model');
    
    // Llama a la función de conteo desde el modelo.
    // Usaremos la función que estaba en Manager_model para la prueba:
    $conteo = $this->Consolidados_model->get_seguimiento_proveedores_count(); 
    
    // Devuelve el conteo en formato JSON
    echo json_encode(['count' => $conteo]);
    exit();
}

}

?>