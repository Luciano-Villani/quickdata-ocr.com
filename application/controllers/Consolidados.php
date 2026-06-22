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
		$archivo = isset($_REQUEST['file']) ? trim($_REQUEST['file']) : '';
		$codigo_lote = isset($_REQUEST['lote']) ? trim($_REQUEST['lote']) : '';

		if ($archivo === '' || $codigo_lote === '') {
			echo json_encode([
				'mensaje' => 'Faltan datos para identificar la lectura consolidada',
				'title' => 'Error',
				'status' => 'error',
			]);
			exit();
		}

		$lote = $this->db->select('id')->from('_lotes')->where('code', $codigo_lote)->get()->row();
		if (!$lote) {
			echo json_encode([
				'mensaje' => 'No se encontro el lote indicado',
				'title' => 'Error',
				'status' => 'error',
			]);
			exit();
		}

		$lecturas = $this->db->select('id')
			->from('_datos_api')
			->where('id_lote', (int) $lote->id)
			->where('nombre_archivo', $archivo)
			->get()->result();

		if (!$lecturas) {
			echo json_encode([
				'mensaje' => 'No se encontro la lectura dentro del lote',
				'title' => 'Error',
				'status' => 'error',
			]);
			exit();
		}

		$ids_lectura = array_map(function ($lectura) {
			return (int) $lectura->id;
		}, $lecturas);

		$this->load->model('manager/Lecturas_model');
		$this->db->trans_start();
		$this->db->where_in('id_lectura_api', $ids_lectura)->delete('_consolidados');
		$this->db->where_in('id', $ids_lectura)->update('_datos_api', [
			'consolidado' => 0,
			'user_consolidado' => null,
			'fecha_consolidado' => null,
		]);
		$this->Lecturas_model->actualizar_resumen_lote((int) $lote->id);
		$this->db->trans_complete();

		$response = [
			'mensaje' => $this->db->trans_status() === false
				? 'No se pudo eliminar el consolidado'
				: 'Consolidado eliminado y lote actualizado',
			'title' => $this->db->trans_status() === false ? 'Error' : '_consolidados',
			'status' => $this->db->trans_status() === false ? 'error' : 'success',
		];

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
		//$this->data['select_periodo_contable'] = $this->Manager_model->obtener_contenido_select('_consolidados', '', 'periodo_contable', 'periodo_contable DESC', false);
        $this->data['select_periodo_contable'] = $this->Consolidados_model->get_periodos_ordenados();
        $this->data['select_expedientes'] = $this->Consolidados_model->get_expedientes_ordenados();

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
        
        // Asumiendo que $this->Consolidados_model ya está cargado
        $registros = $this->Consolidados_model->get_seguimiento_proveedores_list(); 
        
        // 1. 💡 Definir la URL base para el módulo Proveedores
        $base_url_ver = 'Admin/Consolidados/ver/'; 
        
        // 2. 💡 Pasar la URL base junto con los registros a la vista
        $data = [
            'registros' => $registros,
            'base_url_ver' => $base_url_ver 
        ];

        // Cargar la vista con el array $data
        $html_listado = $this->load->view(
            'manager/etiquetas/lista_seguimiento_ajax', 
            $data, // Usar el nuevo array $data
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
public function descargar_pdfs()
{
    // Asegúrate de que la clase ZipArchive esté habilitada
    if (!class_exists('ZipArchive')) {
        // En CodeIgniter, loggear es mejor que un simple die()
        log_message('error', "La extensión ZipArchive de PHP no está habilitada.");
        die("Error: La extensión ZipArchive de PHP no está habilitada.");
    }
    
    // 1. Recolectar y limpiar los filtros (Usando CodeIgniter Input)
    $id_proveedor = $this->input->get('id_proveedor', TRUE);
    $tipo_pago = $this->input->get('tipo_pago'); // Array de textos (ej. ['Contado', 'Crédito'])
    $periodo_contable = $this->input->get('periodo_contable', TRUE);
    $id_secretaria = $this->input->get('id_secretaria', TRUE);
    $fecha_rango = $this->input->get('fecha', TRUE);

    // 2. Procesar Rango de Fechas (DD/MM/YYYY a YYYY-MM-DD)
    $fechas = null;
    if ($fecha_rango) {
        $partes = explode(' - ', $fecha_rango);
        if (count($partes) === 2) {
            // Utilizamos la función de ayuda para la conversión
            $fechas = [
                $this->_format_date_to_db($partes[0]), 
                $this->_format_date_to_db($partes[1])
            ];
        }
    }

    // 3. Obtener las RUTAS DE ARCHIVOS del Modelo
    $filtros_db = [
        // Convertimos a array si es un solo valor para que where_in funcione correctamente
        'id_proveedor' => is_string($id_proveedor) ? [$id_proveedor] : $id_proveedor, 
        'tipo_pago' => $tipo_pago,
        'periodo_contable' => is_string($periodo_contable) ? [$periodo_contable] : $periodo_contable,
        'id_secretaria' => is_string($id_secretaria) ? [$id_secretaria] : $id_secretaria,
        'fechas' => $fechas
    ];
    
    // Llama al modelo (get_archivos_por_filtros ya tiene el límite de 500)
    $archivos_db = $this->Consolidados_model->get_archivos_por_filtros($filtros_db);

    $archivos_encontrados = count($archivos_db);
    
    // 🚨 4. LÓGICA DE CONTROL DE LÍMITE (Feedback Interno)
    if ($archivos_encontrados >= 500) {
        // Loggear si se alcanzó el límite. El usuario no lo verá, pero el administrador sí.
        log_message('warning', "Descarga de PDFs truncada a {$archivos_encontrados} archivos. Se alcanzó el límite de 500 registros.");
    }
    // FIN LÓGICA DE CONTROL DE LÍMITE

    if (empty($archivos_db)) {
        die("<script>alert('No se encontraron archivos PDF para los filtros aplicados.'); window.close();</script>");
    }

    // 5. Crear el Archivo ZIP
    $zip = new ZipArchive();
    $zip_filename = 'Reporte_Consolidados_PDFs_' . date('Ymd_His') . '.zip';
    $zip_path = sys_get_temp_dir() . '/' . $zip_filename; 

    if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die("No se pudo crear el archivo ZIP temporal.");
    }

    $file_count = 0;
    // 🚨 RUTA BASE (Ajustada para que no tenga doble barra si FCPATH ya termina en /) 🚨
    // FCPATH ya apunta a la raíz de tu proyecto (public_html o similar)
    $base_upload_path = FCPATH;

    foreach ($archivos_db as $registro) {
        // La columna 'nombre_archivo' debería contener la ruta relativa (ej: 'uploads/pdfs/doc.pdf')
        $ruta_relativa = $registro->nombre_archivo;
        
        $ruta_absoluta = $base_upload_path . $ruta_relativa;
        
        if (file_exists($ruta_absoluta)) {
            $nombre_archivo = basename($ruta_absoluta);
            // El segundo parámetro es el nombre que tendrá el archivo DENTRO del ZIP
            $zip->addFile($ruta_absoluta, $nombre_archivo);
            $file_count++;
        } else {
            // Loggear el archivo que no se encontró para diagnóstico futuro
            log_message('error', "PDF no encontrado en la ruta: {$ruta_absoluta}");
        }
    }

    $zip->close();
    
    // 6. Forzar la Descarga
    if ($file_count > 0 && file_exists($zip_path)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($zip_path));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($zip_path);
        
        // Limpiar el archivo temporal
        unlink($zip_path);
        exit;
    } else {
        die("<script>alert('No se encontró ningún archivo para descargar. Verifique las rutas o permisos.'); window.close();</script>");
    }
}

/**
 * Función auxiliar para convertir la fecha del frontend (DD/MM/YYYY) al formato DB (YYYY-MM-DD).
 * Puedes mover esta función a un helper si lo usas en otros lugares.
 * @param string $date La fecha en formato DD/MM/YYYY.
 * @return string La fecha en formato YYYY-MM-DD.
 */
public function reporte_final_preview()
{
    if (!$this->input->is_ajax_request()) {
        show_404();
        return;
    }

    $filtros = $this->_get_reporte_final_filtros('post');
    $reporte = $this->Consolidados_model->get_reporte_final($filtros);

    echo json_encode(array(
        'status' => 'success',
        'titulo' => $this->_titulo_reporte_final($reporte['filas']),
        'cantidad' => $reporte['cantidad'],
        'total' => $reporte['total'],
        'filas' => $reporte['filas'],
    ));
    exit;
}

public function reporte_final_opciones()
{
    if (!$this->input->is_ajax_request()) {
        show_404();
        return;
    }

    $filtros = $this->_get_reporte_final_filtros('post');
    echo json_encode(array(
        'status' => 'success',
        'opciones' => $this->Consolidados_model->get_reporte_final_opciones($filtros),
    ));
    exit;
}

public function descargar_reporte_final()
{
    $filtros = $this->_get_reporte_final_filtros('get');
    $reporte = $this->Consolidados_model->get_reporte_final($filtros);

    if (empty($reporte['filas'])) {
        die("<script>alert('No se encontraron registros para los filtros aplicados.'); window.close();</script>");
    }

    $filename = $this->_filename_reporte_final($reporte['filas'], $filtros);
    $xlsx = $this->_xlsx_reporte_final($reporte['filas']);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Length: ' . strlen($xlsx));

    echo $xlsx;
    exit;
}

private function _get_reporte_final_filtros($method = 'post')
{
    $input = $method === 'get' ? 'get' : 'post';
    $idProveedor = $this->input->{$input}('id_proveedor');
    $tipoPago = $this->input->{$input}('tipo_pago');
    $periodoContable = $this->input->{$input}('periodo_contable');
    $idSecretaria = $this->input->{$input}('id_secretaria');
    $fechaRango = $this->input->{$input}('fecha');

    $fechas = null;
    if ($fechaRango) {
        $partes = explode(' - ', $fechaRango);
        if (count($partes) === 2) {
            $fechas = array(
                $this->_format_date_to_db($partes[0]),
                $this->_format_date_to_db($partes[1]),
            );
        }
    }

    return array(
        'id_proveedor' => $this->_normalizar_array_filtro($idProveedor),
        'tipo_pago' => $this->_normalizar_array_filtro($tipoPago),
        'periodo_contable' => $this->_normalizar_array_filtro($periodoContable),
        'id_secretaria' => $this->_normalizar_array_filtro($idSecretaria),
        'fechas' => $fechas,
    );
}

private function _normalizar_array_filtro($valor)
{
    if (empty($valor) || $valor === 'false') {
        return array();
    }

    return is_array($valor) ? array_filter($valor, 'strlen') : array($valor);
}

private function _titulo_reporte_final($filas)
{
    foreach ($filas as $fila) {
        if ($fila['tipo'] === 'detalle') {
            return 'EXPEDIENTE: ' . $fila['expediente'] . '                    ' . $fila['proveedor'] . '                    ' . $fila['tipo_pago'];
        }
    }

    return 'REPORTE FINAL';
}

private function _html_reporte_final($filas, $titulo)
{
    $tituloPartes = $this->_titulo_reporte_final_partes($filas);
    $headers = array(
        'Proveedor', 'Expediente', 'Secretaria', 'Dependencia', 'Juridiccion',
        'Programa', 'O del gasto', 'Tipo Pago', 'Nro cuenta', 'Nro factura',
        'Periodo', 'Vencimiento', 'Importe factura'
    );

    $html = '<html><head><meta charset="UTF-8"><style>
        table { border-collapse: collapse; font-family: Calibri, Arial, sans-serif; font-size: 11pt; table-layout: fixed; }
        td, th { border: 1px solid #000; padding: 5px; text-align: center; vertical-align: middle; mso-number-format:"\@"; white-space: normal; }
        .titulo { background: #f4b183; font-family: "Arial Black", Arial, sans-serif; font-size: 18pt; font-weight: bold; }
        .titulo-left { text-align: left; }
        .titulo-center { text-align: center; }
        .titulo-right { text-align: right; }
        .header { background: #fce4d6; font-weight: bold; }
        .subtotal { background: #fce4d6; font-weight: bold; }
        .importe { mso-number-format:"$ #,##0.00"; text-align: right; }
    </style></head><body><table>';

    $html .= '<colgroup>
        <col style="width:150px"><col style="width:115px"><col style="width:145px"><col style="width:145px">
        <col style="width:125px"><col style="width:100px"><col style="width:105px"><col style="width:105px">
        <col style="width:110px"><col style="width:120px"><col style="width:125px"><col style="width:110px"><col style="width:135px">
    </colgroup>';
    $html .= '<tr><td class="titulo titulo-left" colspan="4">' . html_escape($tituloPartes['expediente']) . '</td><td class="titulo titulo-center" colspan="5">' . html_escape($tituloPartes['proveedor']) . '</td><td class="titulo titulo-right" colspan="4">' . html_escape($tituloPartes['tipo_pago']) . '</td></tr>';
    $html .= '<tr>';
    foreach ($headers as $header) {
        $html .= '<th class="header">' . html_escape($header) . '</th>';
    }
    $html .= '</tr>';

    foreach ($filas as $fila) {
        if ($fila['tipo'] === 'detalle') {
            $html .= '<tr>';
            $html .= $this->_td_excel($fila['proveedor']);
            $html .= $this->_td_excel($fila['expediente']);
            $html .= $this->_td_excel($fila['secretaria']);
            $html .= $this->_td_excel($fila['dependencia']);
            $html .= $this->_td_excel($fila['jurisdiccion']);
            $html .= $this->_td_excel($fila['programa']);
            $html .= $this->_td_excel($fila['objeto']);
            $html .= $this->_td_excel($fila['tipo_pago']);
            $html .= $this->_td_excel($fila['nro_cuenta']);
            $html .= $this->_td_excel($fila['nro_factura']);
            $html .= $this->_td_excel($fila['periodo']);
            $html .= $this->_td_excel($fila['vencimiento']);
            $html .= '<td class="importe">' . number_format((float) $fila['importe'], 2, '.', '') . '</td>';
            $html .= '</tr>';
        } elseif ($fila['tipo'] === 'subtotal_programa') {
            $html .= '<tr class="subtotal"><td colspan="5"></td><td>' . html_escape($fila['programa']) . '</td><td colspan="6"></td><td class="importe">' . number_format((float) $fila['importe'], 2, '.', '') . '</td></tr>';
        } elseif ($fila['tipo'] === 'subtotal_jurisdiccion' || $fila['tipo'] === 'total_general') {
            $html .= '<tr class="subtotal"><td colspan="4"></td><td>' . html_escape($fila['jurisdiccion']) . '</td><td colspan="7"></td><td class="importe">' . number_format((float) $fila['importe'], 2, '.', '') . '</td></tr>';
        }
    }

    $html .= '</table></body></html>';
    return $html;
}

private function _td_excel($valor)
{
    return '<td>' . html_escape($valor) . '</td>';
}

private function _xlsx_reporte_final($filas)
{
    if (!class_exists('ZipArchive')) {
        die("<script>alert('El servidor no tiene habilitada la extension ZIP para generar XLSX.'); window.close();</script>");
    }

    $tmp = tempnam(sys_get_temp_dir(), 'mvl_reporte_');
    $zip = new ZipArchive();

    if ($zip->open($tmp, ZipArchive::OVERWRITE) !== TRUE) {
        die("<script>alert('No se pudo generar el archivo XLSX temporal.'); window.close();</script>");
    }

    $zip->addFromString('[Content_Types].xml', $this->_xlsx_content_types_xml());
    $zip->addFromString('_rels/.rels', $this->_xlsx_root_rels_xml());
    $zip->addFromString('xl/workbook.xml', $this->_xlsx_workbook_xml());
    $zip->addFromString('xl/_rels/workbook.xml.rels', $this->_xlsx_workbook_rels_xml());
    $zip->addFromString('xl/styles.xml', $this->_xlsx_styles_xml());
    $zip->addFromString('xl/worksheets/sheet1.xml', $this->_xlsx_sheet_xml($filas));
    $zip->close();

    $contenido = file_get_contents($tmp);
    unlink($tmp);

    return $contenido;
}

private function _xlsx_sheet_xml($filas)
{
    $tituloPartes = $this->_titulo_reporte_final_partes($filas);
    $headers = array(
        'Proveedor', 'Expediente', 'Secretaría', 'Dependencia', 'Jurisdicción',
        'Programa', 'O del gasto', 'Tipo Pago', 'Nro cuenta', 'Nro factura',
        'Período', 'Vencimiento', 'Importe factura'
    );
    $cols = array(15, 13, 16, 15, 13, 13, 12, 12, 13, 13, 13, 13, 16);

    $rows = array();
    $mergeCells = array('A1:D1', 'E1:I1', 'J1:M1');
    $rowIndex = 1;

    $rows[] = '<row r="1" ht="28" customHeight="1">'
        . $this->_xlsx_cell('A', $rowIndex, $tituloPartes['expediente'], 1)
        . $this->_xlsx_cell('E', $rowIndex, $tituloPartes['proveedor'], 1)
        . $this->_xlsx_cell('J', $rowIndex, $tituloPartes['tipo_pago'], 1)
        . '</row>';

    $rowIndex++;
    $headerCells = '';
    foreach ($headers as $i => $header) {
        $headerCells .= $this->_xlsx_cell($this->_xlsx_col($i + 1), $rowIndex, $header, 2);
    }
    $rows[] = '<row r="2" ht="34" customHeight="1">' . $headerCells . '</row>';

    foreach ($filas as $fila) {
        $rowIndex++;

        if ($fila['tipo'] === 'detalle') {
            $valores = array(
                $fila['proveedor'], $fila['expediente'], $fila['secretaria'], $fila['dependencia'],
                $fila['jurisdiccion'], $fila['programa'], $fila['objeto'], $fila['tipo_pago'],
                $this->_xlsx_wrap_codigo($fila['nro_cuenta']), $this->_xlsx_wrap_codigo($fila['nro_factura']),
                $fila['periodo'], $fila['vencimiento'], (float) $fila['importe']
            );
            $cells = '';
            foreach ($valores as $i => $valor) {
                $style = $i === 12 ? 4 : 3;
                $cells .= $this->_xlsx_cell($this->_xlsx_col($i + 1), $rowIndex, $valor, $style, $i === 12);
            }
            $rows[] = '<row r="' . $rowIndex . '" ht="55" customHeight="1">' . $cells . '</row>';
        } elseif ($fila['tipo'] === 'subtotal_programa') {
            $cells = '';
            for ($i = 1; $i <= 13; $i++) {
                $valor = '';
                $style = $i === 13 ? 6 : 5;
                $numeric = false;
                if ($i === 6) {
                    $valor = $fila['programa'];
                } elseif ($i === 13) {
                    $valor = (float) $fila['importe'];
                    $numeric = true;
                }
                $cells .= $this->_xlsx_cell($this->_xlsx_col($i), $rowIndex, $valor, $style, $numeric);
            }
            $rows[] = '<row r="' . $rowIndex . '" ht="21" customHeight="1">' . $cells . '</row>';
        } elseif ($fila['tipo'] === 'subtotal_jurisdiccion' || $fila['tipo'] === 'total_general') {
            $cells = '';
            for ($i = 1; $i <= 13; $i++) {
                $valor = '';
                $style = $i === 13 ? 6 : 5;
                $numeric = false;
                if ($i === 5) {
                    $valor = $fila['jurisdiccion'];
                } elseif ($i === 13) {
                    $valor = (float) $fila['importe'];
                    $numeric = true;
                }
                $cells .= $this->_xlsx_cell($this->_xlsx_col($i), $rowIndex, $valor, $style, $numeric);
            }
            $rows[] = '<row r="' . $rowIndex . '" ht="45" customHeight="1">' . $cells . '</row>';
        }
    }

    $colsXml = '<cols>';
    foreach ($cols as $i => $width) {
        $col = $i + 1;
        $colsXml .= '<col min="' . $col . '" max="' . $col . '" width="' . $width . '" customWidth="1"/>';
    }
    $colsXml .= '</cols>';

    $mergeXml = '<mergeCells count="' . count($mergeCells) . '">';
    foreach ($mergeCells as $merge) {
        $mergeXml .= '<mergeCell ref="' . $merge . '"/>';
    }
    $mergeXml .= '</mergeCells>';

    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="2" topLeftCell="A3" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
        . $colsXml
        . '<sheetData>' . implode('', $rows) . '</sheetData>'
        . $mergeXml
        . '</worksheet>';
}

private function _xlsx_cell($col, $row, $valor, $style, $numeric = false)
{
    $ref = $col . $row;
    if ($numeric) {
        return '<c r="' . $ref . '" s="' . $style . '"><v>' . $valor . '</v></c>';
    }

    return '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $this->_xlsx_xml($valor) . '</t></is></c>';
}

private function _xlsx_wrap_codigo($valor)
{
    $valor = (string) $valor;
    if (strpos($valor, ':') !== false) {
        return str_replace(':', ':' . "\n", $valor);
    }
    if (strpos($valor, '-') !== false && strlen($valor) > 9) {
        return preg_replace('/-/', "-\n", $valor, 1);
    }
    return $valor;
}

private function _xlsx_col($numero)
{
    $col = '';
    while ($numero > 0) {
        $mod = ($numero - 1) % 26;
        $col = chr(65 + $mod) . $col;
        $numero = (int) (($numero - $mod) / 26);
    }
    return $col;
}

private function _xlsx_xml($valor)
{
    return htmlspecialchars((string) $valor, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

private function _xlsx_content_types_xml()
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
        . '</Types>';
}

private function _xlsx_root_rels_xml()
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>';
}

private function _xlsx_workbook_xml()
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets><sheet name="Liquidacion" sheetId="1" r:id="rId1"/></sheets>'
        . '</workbook>';
}

private function _xlsx_workbook_rels_xml()
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
        . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
        . '</Relationships>';
}

private function _xlsx_styles_xml()
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;$&quot; #,##0.00"/></numFmts>'
        . '<fonts count="3">'
        . '<font><sz val="11"/><name val="Calibri"/></font>'
        . '<font><b/><sz val="18"/><name val="Arial Black"/></font>'
        . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
        . '</fonts>'
        . '<fills count="4">'
        . '<fill><patternFill patternType="none"/></fill>'
        . '<fill><patternFill patternType="gray125"/></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FFF4B183"/><bgColor indexed="64"/></patternFill></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FFFCE4D6"/><bgColor indexed="64"/></patternFill></fill>'
        . '</fills>'
        . '<borders count="2">'
        . '<border><left/><right/><top/><bottom/><diagonal/></border>'
        . '<border><left style="thin"><color rgb="FF000000"/></left><right style="thin"><color rgb="FF000000"/></right><top style="thin"><color rgb="FF000000"/></top><bottom style="thin"><color rgb="FF000000"/></bottom><diagonal/></border>'
        . '</borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="7">'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
        . '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
        . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
        . '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
        . '<xf numFmtId="164" fontId="2" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
        . '</cellXfs>'
        . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
        . '</styleSheet>';
}

private function _titulo_reporte_final_partes($filas)
{
    foreach ($filas as $fila) {
        if ($fila['tipo'] === 'detalle') {
            $proveedor = preg_replace('/\s*\([^)]*\)\s*$/', '', $fila['proveedor']);
            return array(
                'expediente' => 'EXPEDIENTE: ' . $fila['expediente'],
                'proveedor' => $proveedor,
                'tipo_pago' => $fila['tipo_pago'],
            );
        }
    }

    return array(
        'expediente' => 'REPORTE FINAL',
        'proveedor' => '',
        'tipo_pago' => '',
    );
}

private function _filename_reporte_final($filas, $filtros)
{
    $expediente = 'REPORTE';
    $proveedor = 'FINAL';

    foreach ($filas as $fila) {
        if ($fila['tipo'] === 'detalle') {
            $expediente = $fila['expediente'];
            $proveedor = preg_replace('/\s*\([^)]*\)\s*$/', '', $fila['proveedor']);
            break;
        }
    }

    $periodos = isset($filtros['periodo_contable']) ? $filtros['periodo_contable'] : array();
    $periodo = count($periodos) === 1 ? reset($periodos) : 'VARIOS_PERIODOS';

    $expedienteSlug = $this->_slug_reporte_final_filename($expediente, true);
    $proveedorSlug = $this->_slug_reporte_final_filename($proveedor, false);
    $periodoSlug = $this->_slug_reporte_final_filename($periodo, false);

    return $expedienteSlug . $proveedorSlug . '_' . $periodoSlug . '.xlsx';
}

private function _slug_reporte_final_filename($valor, $preservarGuion = false)
{
    $valor = trim((string) $valor);
    $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
    if ($convertido !== false) {
        $valor = $convertido;
    }

    if ($preservarGuion) {
        $valor = str_replace(array('/', '\\'), '-', $valor);
    }

    $valor = strtoupper($valor);
    $patron = $preservarGuion ? '/[^A-Z0-9-]+/' : '/[^A-Z0-9]+/';
    $valor = preg_replace($patron, '_', $valor);
    $valor = trim($valor, '_-');

    return $valor !== '' ? $valor : 'REPORTE';
}

private function _format_date_to_db($date)
{
    // Usamos DateTime::createFromFormat para manejar el formato de entrada
    $dt = DateTime::createFromFormat('d/m/Y', $date);
    // Retornamos el formato de base de datos o la cadena original si falla.
    return $dt ? $dt->format('Y-m-d') : $date;
}
}

?>
