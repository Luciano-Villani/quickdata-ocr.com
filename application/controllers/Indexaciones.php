<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Indexaciones extends backend_controller
{
	function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			redirect('Login');
		} else {
			$this->bloquear_financiero('No tiene permisos para administrar indexadores.');
			$this->load->model('Ion_auth_model');
			$this->load->model('manager/Usuarios_model');
			$this->load->model('manager/Secretarias_model');
			$this->load->model('manager/Dependencias_model');
			$this->load->model('manager/Proyectos_model');
			$this->load->model('manager/Manager_model');

			$this->data['select_proyectos'] = $this->Manager_model->obtener_contenido_select('_proyectos', 'SELECCIONE PROYECTO', 'descripcion', 'id ASC');
			$this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'secretaria ASC');
			$this->data['select_dependencias'] = $this->Manager_model->obtener_contenido_select('_dependencias', 'SELECCIONE DEPENDENCIA', 'dependencia', 'id ASC');
			$this->data['select_programas'] = $this->Manager_model->obtener_contenido_select('_programas', 'SELECCIONE PROGRAMA', 'descripcion', '');
			$this->data['select_proveedores'] = $this->Manager_model->obtener_contenido_select('_proveedores', 'SELECCIONE PROVEEDOR', 'nombre', 'nombre ASC');
			$this->data['select_tipo_pago'] = $this->Manager_model->obtener_contenido_select('_tipo_pago', 'Seleccionar Tipo Pago', 'tip_nombre', 'tip_id');
			$this->data['tabla'] = '_indexaciones';
			$this->BtnText = 'Agregar';
			// $this->output->enable_profiler(TRUE);
		}
	}

	public function edit()
	{

		if ($this->input->is_ajax_request()) {

			$data = $this->Manager_model->getWhere('_indexaciones', 'id="' . $_REQUEST['id'] . '"');



			if ($data) {
				$response = array(
					'mensaje' => $_REQUEST['id'],
					'data' => $data,
					'status' => 'success',
				);
			} else {
				$response = array(
					'mensaje' => $_REQUEST['id'],
					'title' => 'EDITAR ' . $this->router->fetch_class() . ' - dato inexistente',
					'status' => 'error',
				);
			}


			echo json_encode($response);
			exit();
		}
	}

    public function migrar()
    {
        $script = array(
            base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
            base_url('assets/manager/js/secciones/indexaciones/migrar.js'),
        );

        $this->data['css_common'] = $this->css_common;
        $this->data['css'] = '';
        $this->data['script_common'] = $this->script_common;
        $this->data['script'] = $script;
        $this->data['select_secretarias'] = $this->Manager_model->obtener_contenido_select('_secretarias', 'SELECCIONE SECRETARÍA', 'secretaria', 'secretaria ASC');
        $this->data['content'] = $this->load->view('manager/secciones/indexaciones/migrar', $this->data, TRUE);

        $this->load->view('manager/head', $this->data);
        $this->load->view('manager/index', $this->data);
        $this->load->view('manager/footer', $this->data);
    }

    public function buscar_migracion()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $nroCuenta = trim((string) $this->input->post('nro_cuenta', TRUE));
        if ($nroCuenta === '') {
            return $this->_json_migracion(array(
                'status' => 'error',
                'mensaje' => 'Ingrese un número de cuenta.',
            ));
        }

        $indexacion = $this->_get_indexacion_migracion_por_cuenta($nroCuenta);
        if (!$indexacion) {
            return $this->_json_migracion(array(
                'status' => 'error',
                'mensaje' => 'No se encontró una indexación activa para esa cuenta.',
            ));
        }

        $cuentasDependencia = array();
        if (!empty($indexacion->id_dependencia)) {
            $cuentasDependencia = $this->_get_cuentas_dependencia_migracion($indexacion->id_dependencia);
        }

        return $this->_json_migracion(array(
            'status' => 'success',
            'data' => array(
                'indexacion' => $indexacion,
                'cuentas_dependencia' => $cuentasDependencia,
                'total_cuentas_dependencia' => count($cuentasDependencia),
            ),
        ));
    }

    public function opciones_migracion()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $idSecretaria = (int) $this->input->post('id_secretaria');
        $idPrograma = (int) $this->input->post('id_programa');

        $programas = array();
        $dependencias = array();
        $proyectos = array();

        if ($idSecretaria > 0) {
            $programas = $this->db
                ->select('id, id_interno, descripcion')
                ->where('id_secretaria', $idSecretaria)
                ->order_by('id_interno ASC, descripcion ASC')
                ->get('_programas')
                ->result();

            $dependencias = $this->db
                ->select('id, dependencia, direccion')
                ->where('id_secretaria', $idSecretaria)
                ->order_by('dependencia ASC')
                ->get('_dependencias')
                ->result();
        }

        if ($idPrograma > 0) {
            $proyectos = $this->db
                ->select('id, id_interno, descripcion')
                ->where('id_programa', $idPrograma)
                ->order_by('id_interno ASC, descripcion ASC')
                ->get('_proyectos')
                ->result();
        }

        return $this->_json_migracion(array(
            'status' => 'success',
            'data' => array(
                'programas' => $programas,
                'dependencias' => $dependencias,
                'proyectos' => $proyectos,
            ),
        ));
    }

    public function guardar_migracion()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $idIndexacion = (int) $this->input->post('id_indexacion');
        $alcance = $this->input->post('alcance', TRUE) === 'dependencia' ? 'dependencia' : 'cuenta';
        $idSecretariaNueva = (int) $this->input->post('id_secretaria');
        $idProgramaNuevo = (int) $this->input->post('id_programa');
        $idProyectoNuevo = (int) $this->input->post('id_proyecto');
        $idDependenciaNueva = (int) $this->input->post('id_dependencia');
        $moverDependencia = (int) $this->input->post('mover_dependencia') === 1 ? 1 : 0;
        $observacion = trim((string) $this->input->post('observacion', TRUE));

        $origen = $this->_get_indexacion_migracion_por_id($idIndexacion);
        if (!$origen) {
            return $this->_json_migracion(array('status' => 'error', 'mensaje' => 'La indexación de origen no existe.'));
        }

        $validacion = $this->_validar_destino_migracion($idSecretariaNueva, $idProgramaNuevo, $idProyectoNuevo, $idDependenciaNueva, $moverDependencia, $origen);
        if ($validacion['status'] !== 'success') {
            return $this->_json_migracion($validacion);
        }

        if ($moverDependencia) {
            $idDependenciaNueva = (int) $origen->id_dependencia;
            $alcance = 'dependencia';
        }

        $afectadas = $alcance === 'dependencia'
            ? $this->_get_indexaciones_por_dependencia_migracion($origen->id_dependencia)
            : array($origen);

        if (empty($afectadas)) {
            return $this->_json_migracion(array('status' => 'error', 'mensaje' => 'No hay cuentas para migrar.'));
        }

        $idsRecalculo = array();
        $grupoMigracion = date('YmdHis') . '-' . $this->user->id . '-' . substr(md5(uniqid('', true)), 0, 8);
        $this->db->trans_begin();

        if ($moverDependencia && (int) $origen->id_dependencia > 0) {
            $this->db->where('id', $origen->id_dependencia);
            $this->db->update('_dependencias', array('id_secretaria' => $idSecretariaNueva));
        }

        foreach ($afectadas as $actual) {
            $idsRecalculo[] = $actual->nro_cuenta;

            $audit = array(
                'id_indexacion' => $actual->id,
                'alcance' => $alcance,
                'nro_cuenta' => $actual->nro_cuenta,
                'id_proveedor' => $actual->id_proveedor,
                'id_secretaria_anterior' => $actual->id_secretaria,
                'id_programa_anterior' => $actual->id_programa,
                'id_proyecto_anterior' => $actual->id_proyecto,
                'id_dependencia_anterior' => $actual->id_dependencia,
                'id_secretaria_nueva' => $idSecretariaNueva,
                'id_programa_nuevo' => $idProgramaNuevo,
                'id_proyecto_nuevo' => $idProyectoNuevo,
                'id_dependencia_nueva' => $idDependenciaNueva,
                'mover_dependencia' => $moverDependencia,
                'expediente_anterior' => $actual->expediente,
                'tipo_pago_anterior' => $actual->tipo_pago,
                'observacion' => $observacion,
                'grupo_migracion' => $grupoMigracion,
                'user_add' => $this->user->id,
            );
            $this->db->insert('_indexaciones_migraciones', $audit);

            $this->db->where('id', $actual->id);
            $this->db->update('_indexaciones', array(
                'id_secretaria' => $idSecretariaNueva,
                'id_programa' => $idProgramaNuevo,
                'id_proyecto' => $idProyectoNuevo,
                'id_dependencia' => $idDependenciaNueva,
                'user_mod' => $this->user->id,
                'fecha_mod' => date('Y-m-d H:i:s'),
            ));
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->_json_migracion(array('status' => 'error', 'mensaje' => 'No se pudo guardar la migración.'));
        }

        $this->db->trans_commit();
        $this->_recalcular_lotes_por_cuentas_migradas(array_unique($idsRecalculo));

        return $this->_json_migracion(array(
            'status' => 'success',
            'mensaje' => 'Migración guardada correctamente.',
            'data' => array('cuentas_afectadas' => count($afectadas)),
        ));
    }

    public function historial_migracion()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $nroCuenta = trim((string) $this->input->post('nro_cuenta', TRUE));
        $idIndexacion = (int) $this->input->post('id_indexacion');

        if ($nroCuenta === '' && $idIndexacion <= 0) {
            return $this->_json_migracion(array('status' => 'error', 'mensaje' => 'Seleccione una cuenta para ver el historial.'));
        }

        if (!$this->db->table_exists('_indexaciones_migraciones')) {
            return $this->_json_migracion(array('status' => 'success', 'data' => array('historial' => array())));
        }

        $this->db
            ->select('
                m.*,
                sant.secretaria AS secretaria_anterior_nombre,
                snue.secretaria AS secretaria_nueva_nombre,
                pant.id_interno AS programa_anterior_codigo,
                pant.descripcion AS programa_anterior_nombre,
                pnue.id_interno AS programa_nuevo_codigo,
                pnue.descripcion AS programa_nuevo_nombre,
                pray.id_interno AS proyecto_anterior_codigo,
                pray.descripcion AS proyecto_anterior_nombre,
                prny.id_interno AS proyecto_nuevo_codigo,
                prny.descripcion AS proyecto_nuevo_nombre,
                dant.dependencia AS dependencia_anterior_nombre,
                dnue.dependencia AS dependencia_nueva_nombre,
                p.nombre AS proveedor_nombre
            ')
            ->from('_indexaciones_migraciones m')
            ->join('_proveedores p', 'p.id = m.id_proveedor', 'left')
            ->join('_secretarias sant', 'sant.id = m.id_secretaria_anterior', 'left')
            ->join('_secretarias snue', 'snue.id = m.id_secretaria_nueva', 'left')
            ->join('_programas pant', 'pant.id = m.id_programa_anterior', 'left')
            ->join('_programas pnue', 'pnue.id = m.id_programa_nuevo', 'left')
            ->join('_proyectos pray', 'pray.id = m.id_proyecto_anterior', 'left')
            ->join('_proyectos prny', 'prny.id = m.id_proyecto_nuevo', 'left')
            ->join('_dependencias dant', 'dant.id = m.id_dependencia_anterior', 'left')
            ->join('_dependencias dnue', 'dnue.id = m.id_dependencia_nueva', 'left');

        if ($idIndexacion > 0) {
            $this->db->where('m.id_indexacion', $idIndexacion);
        } else {
            $this->db->where('m.nro_cuenta', $nroCuenta);
        }

        $historial = $this->db
            ->order_by('m.fecha_add DESC, m.id DESC')
            ->limit(50)
            ->get()
            ->result();

        return $this->_json_migracion(array('status' => 'success', 'data' => array('historial' => $historial)));
    }

    public function revertir_migracion()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $idMigracion = (int) $this->input->post('id_migracion');
        $observacion = trim((string) $this->input->post('observacion', TRUE));

        if ($idMigracion <= 0) {
            return $this->_json_migracion(array('status' => 'error', 'mensaje' => 'MigraciÃ³n invÃ¡lida.'));
        }

        $migracion = $this->db->where('id', $idMigracion)->get('_indexaciones_migraciones')->row();
        if (!$migracion) {
            return $this->_json_migracion(array('status' => 'error', 'mensaje' => 'No se encontrÃ³ la migraciÃ³n.'));
        }
        if ((int) $migracion->revertida === 1) {
            return $this->_json_migracion(array('status' => 'error', 'mensaje' => 'Esta migraciÃ³n ya fue revertida.'));
        }

        if (!empty($migracion->grupo_migracion)) {
            $migraciones = $this->db
                ->where('grupo_migracion', $migracion->grupo_migracion)
                ->where('revertida', 0)
                ->get('_indexaciones_migraciones')
                ->result();
        } else {
            $migraciones = array($migracion);
        }

        if (empty($migraciones)) {
            return $this->_json_migracion(array('status' => 'error', 'mensaje' => 'No hay movimientos pendientes para revertir.'));
        }

        foreach ($migraciones as $mov) {
            $actual = $this->db->where('id', $mov->id_indexacion)->get('_indexaciones')->row();
            if (!$actual) {
                return $this->_json_migracion(array('status' => 'error', 'mensaje' => 'No se puede revertir: una cuenta migrada ya no existe.'));
            }

            if ((int) $actual->id_secretaria !== (int) $mov->id_secretaria_nueva
                || (int) $actual->id_programa !== (int) $mov->id_programa_nuevo
                || (int) $actual->id_proyecto !== (int) $mov->id_proyecto_nuevo
                || (int) $actual->id_dependencia !== (int) $mov->id_dependencia_nueva) {
                return $this->_json_migracion(array(
                    'status' => 'error',
                    'mensaje' => 'No se puede revertir porque una cuenta ya tuvo cambios posteriores. Revise el historial antes de continuar.',
                ));
            }
        }

        $cuentasRecalculo = array();
        $this->db->trans_begin();

        foreach ($migraciones as $mov) {
            $cuentasRecalculo[] = $mov->nro_cuenta;

            $this->db->where('id', $mov->id_indexacion);
            $this->db->update('_indexaciones', array(
                'id_secretaria' => $mov->id_secretaria_anterior,
                'id_programa' => $mov->id_programa_anterior,
                'id_proyecto' => $mov->id_proyecto_anterior,
                'id_dependencia' => $mov->id_dependencia_anterior,
                'user_mod' => $this->user->id,
                'fecha_mod' => date('Y-m-d H:i:s'),
            ));
        }

        if ((int) $migracion->mover_dependencia === 1 && (int) $migracion->id_dependencia_anterior > 0) {
            $this->db->where('id', $migracion->id_dependencia_anterior);
            $this->db->update('_dependencias', array('id_secretaria' => $migracion->id_secretaria_anterior));
        }

        $idsMigraciones = array_map(function ($item) {
            return (int) $item->id;
        }, $migraciones);

        $this->db->where_in('id', $idsMigraciones);
        $this->db->update('_indexaciones_migraciones', array(
            'revertida' => 1,
            'fecha_reversion' => date('Y-m-d H:i:s'),
            'user_reversion' => $this->user->id,
            'observacion_reversion' => $observacion,
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->_json_migracion(array('status' => 'error', 'mensaje' => 'No se pudo revertir la migraciÃ³n.'));
        }

        $this->db->trans_commit();
        $this->_recalcular_lotes_por_cuentas_migradas(array_unique($cuentasRecalculo));

        return $this->_json_migracion(array(
            'status' => 'success',
            'mensaje' => 'MigraciÃ³n revertida correctamente.',
            'data' => array('cuentas_afectadas' => count($migraciones)),
        ));
    }

    private function _get_indexacion_migracion_por_cuenta($nroCuenta)
    {
        $this->db->where('_indexaciones.nro_cuenta', $nroCuenta);
        $this->db->limit(1);
        return $this->_query_indexacion_migracion()->row();
    }

    private function _get_indexacion_migracion_por_id($idIndexacion)
    {
        $this->db->where('_indexaciones.id', $idIndexacion);
        $this->db->limit(1);
        return $this->_query_indexacion_migracion()->row();
    }

    private function _get_indexaciones_por_dependencia_migracion($idDependencia)
    {
        if ((int) $idDependencia <= 0) {
            return array();
        }
        $this->db->where('_indexaciones.id_dependencia', $idDependencia);
        return $this->_query_indexacion_migracion()->result();
    }

    private function _query_indexacion_migracion()
    {
        return $this->db
            ->select('
                _indexaciones.*,
                _proveedores.nombre AS proveedor_nombre,
                _secretarias.secretaria AS secretaria_nombre,
                _programas.id_interno AS programa_codigo,
                _programas.descripcion AS programa_nombre,
                _proyectos.id_interno AS proyecto_codigo,
                _proyectos.descripcion AS proyecto_nombre,
                _dependencias.dependencia AS dependencia_nombre,
                _dependencias.direccion AS dependencia_direccion,
                _tipo_pago.tip_nombre AS tipo_pago_nombre
            ')
            ->from('_indexaciones')
            ->join('_proveedores', '_proveedores.id = _indexaciones.id_proveedor', 'left')
            ->join('_secretarias', '_secretarias.id = _indexaciones.id_secretaria', 'left')
            ->join('_programas', '_programas.id = _indexaciones.id_programa', 'left')
            ->join('_proyectos', '_proyectos.id = _indexaciones.id_proyecto', 'left')
            ->join('_dependencias', '_dependencias.id = _indexaciones.id_dependencia', 'left')
            ->join('_tipo_pago', '_tipo_pago.tip_id = _indexaciones.tipo_pago', 'left')
            ->get();
    }

    private function _get_cuentas_dependencia_migracion($idDependencia)
    {
        return $this->db
            ->select('_indexaciones.id, _indexaciones.nro_cuenta, _proveedores.nombre AS proveedor_nombre')
            ->from('_indexaciones')
            ->join('_proveedores', '_proveedores.id = _indexaciones.id_proveedor', 'left')
            ->where('_indexaciones.id_dependencia', $idDependencia)
            ->order_by('_proveedores.nombre ASC, _indexaciones.nro_cuenta ASC')
            ->get()
            ->result();
    }

    private function _validar_destino_migracion($idSecretaria, $idPrograma, $idProyecto, $idDependencia, $moverDependencia, $origen)
    {
        if ($idSecretaria <= 0 || !$this->Manager_model->get_data('_secretarias', $idSecretaria)) {
            return array('status' => 'error', 'mensaje' => 'Seleccione una secretaría destino válida.');
        }

        $programa = $this->db->where('id', $idPrograma)->where('id_secretaria', $idSecretaria)->get('_programas')->row();
        if (!$programa) {
            return array('status' => 'error', 'mensaje' => 'Seleccione un programa válido para la secretaría destino.');
        }

        if ($idProyecto > 0) {
            $proyecto = $this->db->where('id', $idProyecto)->where('id_programa', $idPrograma)->where('id_secretaria', $idSecretaria)->get('_proyectos')->row();
            if (!$proyecto) {
                return array('status' => 'error', 'mensaje' => 'Seleccione un proyecto válido para el programa destino.');
            }
        }

        if ($moverDependencia) {
            if ((int) $origen->id_dependencia <= 0) {
                return array('status' => 'error', 'mensaje' => 'La cuenta actual no tiene dependencia para mover.');
            }
            return array('status' => 'success');
        }

        $dependencia = $this->db->where('id', $idDependencia)->where('id_secretaria', $idSecretaria)->get('_dependencias')->row();
        if (!$dependencia) {
            return array('status' => 'error', 'mensaje' => 'Seleccione una dependencia válida para la secretaría destino.');
        }

        return array('status' => 'success');
    }

    private function _recalcular_lotes_por_cuentas_migradas($cuentas)
    {
        if (empty($cuentas)) {
            return;
        }

        $this->load->model('manager/Lecturas_model');
        $this->db->select('t1.id_lote')
            ->from('_datos_api t1')
            ->join('_lotes t2', 't1.id_lote = t2.id', 'inner')
            ->where_in('t1.nro_cuenta', $cuentas)
            ->where('t2.consolidado', 0)
            ->distinct();

        $lotes = $this->db->get()->result();
        foreach ($lotes as $lote) {
            $this->Lecturas_model->actualizar_resumen_lote($lote->id_lote);
        }
    }

    private function _json_migracion($payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

	public function list_dt($id = null)
	{

		if ($this->input->is_ajax_request()) {


			$data = $row = array();

			$memData = $this->Manager_model->getRows($_POST);


			// echo $this->db->last_query();
			// die();
			$estadoSucces = '<span class="acciones"><i class="text-success icon-check2 "></i></span>';
			foreach ($memData as $r) {


				$accionesVer = '<span class="acciones"><a title="ver archivo" href="/Admin/s/viewBatch/' . $r->id . '"  class=""><i class="icon-eye4" title="ver"></i> </a></span> ';
				$accionesEdit = '<span data-id="' . $r->id . '" class="editar_ acciones" ><a title="Editar" href="/Admin/' . ucfirst($this->router->fetch_class()) . '/editar/' . $r->id_dependencia . '"  class=""><i class=" text-warningr  icon-pencil4 " title="Editar "></i> </a> </span>';
				$accionesDelete = '<span data-id_="' . $r->id . '" class="borrar_ acciones" ><a title="Borrar " href="#"  class=""><i class=" text-danger icon-trash " title="Borrar "></i> </a> </span>';
				// $user = $this->ion_auth->user($r->user_add)->row();

				//	
				$acciones = '<ul class="icons-list">
				<li class="text-primary-600"><a class="edit_dato" data-id="'.$r->id.'" href="#"><i class="icon-pencil7"></i></a></li>
				<li  class=" text-danger-600"><a class="borrar_file" data-id="' . $r->id . '" href="#"><i class="icon-trash"></i></a></li>
			</ul>';
				$data[] = array(
					$r->id_programa.' '.$r->id_proyecto,
					$r->id,
					$r->nom_proveedor,
					$r->expediente,
					$r->nro_cuenta,
					$r->nombre_secretaria,
					$r->prog_id_interno. "  " . $r->descr_programa,
					$r->proy_id_interno . "  " . $r->descr_proyecto,
					$r->nombre_dependencia,
					((int) $r->periodicidad_meses === 2 ? 'BIMESTRAL' : 'MENSUAL'),
					((int) $r->control_vencimiento === 1 ? '<span class="badge badge-success">SI</span>' : '<span class="badge badge-secondary">NO</span>'),
					$acciones,
				);
			}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->Manager_model->countAll(),
				"recordsFiltered" => $this->Manager_model->countFiltered($_POST),
				"data" => $data,
			);

			// Output to JSON format
			echo json_encode($output);
			exit();
		}
	}

	public function delete()
{
    // El ID del registro a eliminar se espera en $_REQUEST['id']
    $id_indexacion = $_REQUEST['id']; 
    
    // 1. 🔑 OBTENER el nro_cuenta ANTES DE BORRAR el indexador 🔑
    //    Este paso es CRUCIAL para saber qué lotes recalcular.
    $registro_indexador = $this->db->select('nro_cuenta')
                                   ->get_where('_indexaciones', ['id' => $id_indexacion])
                                   ->row();
    $nro_cuenta_afectado = $registro_indexador ? $registro_indexador->nro_cuenta : null;

    try {
        
        // 2. EJECUTAR la eliminación del indexador
        $this->db->where('id', $id_indexacion);
        // Se asume que $_REQUEST['tabla'] contiene el nombre '_indexaciones'
        $this->db->delete($_REQUEST['tabla']); 
        
        
        // 3. 🚨 INICIO MANTENIMIENTO: Recálculo de _lotes_resumen 🚨
        if (!empty($nro_cuenta_afectado)) {
            
            // Obtener los IDs de lotes asociados a este nro_cuenta que NO están consolidados.
            // Utilizamos la lógica JOIN corregida para buscar el 'consolidado = 0' en _lotes.
            $this->db->select('t1.id_lote')
                     ->from('_datos_api t1')
                     ->join('_lotes t2', 't1.id_lote = t2.id', 'inner')
                     ->where('t1.nro_cuenta', $nro_cuenta_afectado)
                     ->where('t2.consolidado', 0) 
                     ->distinct();

            $lotes_a_recalcular = $this->db->get()->result(); 
            
            // Iterar y ejecutar el mantenimiento solo si se encontraron lotes
            if ($lotes_a_recalcular) {
                 // Cargar el modelo con la ruta correcta (confirmada en el paso anterior)
                 $this->load->model('manager/Lecturas_model');
                 foreach ($lotes_a_recalcular as $lote) {
                     $this->Lecturas_model->actualizar_resumen_lote($lote->id_lote);
                 }
            }
        }
        // --------------------------------------------------------
        // FIN MANTENIMIENTO

        $response = array(
            'mensaje' => 'Datos borrados',
            'title' => str_replace('_', '', $_REQUEST['tabla']),
            'status' => 'success',
        );
    } catch (Exception $e) {
        $response = array(
            'mensaje' => 'Error: ' . $e->getMessage(),
            'title' => str_replace('_', '', $_REQUEST['tabla']),
            'status' => 'error',
        );
    }

    echo json_encode($response);
    exit();
}

	public function listados($id = NULL)
{

    $this->data['collapse'] = 'collapse';
    $script = array(
        base_url('assets/manager/js/plugins/tables/datatables/datatables.js'),
        base_url('assets/manager/js/plugins/forms/selects/select2.min.js'),
        base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
    );
    $this->data['css_common'] = $this->css_common;
    $this->data['css'] = '';

    $this->data['script_common'] = $this->script_common;
    $this->data['script'] = $script;

    $this->data['programas_id_interno'] = $this->Programas_model->getIdInterno();
    $newdata = [];
    $pasada = 1;
    foreach ($this->data['programas_id_interno'] as $data) {
        $newdata[$data['id']]['id'] = $data['id'];
        $newdata[$data['id']]['id_interno'] = $data['id_interno'];
        $pasada++;
    };
    $this->data['programas_id_interno'] = $newdata;
    $this->data['periodicidad_meses'] = 1;
    $this->data['control_vencimiento'] = 1;
    $this->data['dias_alerta'] = 7;


    if ($id && $id != NULL) {

        $this->BtnText = 'Editar';
        $editData = $this->Manager_model->get_data('_indexaciones', $id);

        // $program = $this->Manager_model->getWhere('_programas', "id_secretaria = " . $editData->id_secretaria . " AND id_interno=" . $editData->id_programa);
        $program = 0;
        if ($program = $this->Manager_model->getWhere('_programas', "id_secretaria = " . $editData->id_secretaria . " AND id_interno=" . $editData->id_programa)) {

            $program = $program->id;
        };


        $this->data['indexador'] = $editData;
        $this->data['id_proveedor'] = $editData->id_proveedor;
        $this->data['nro_cuenta'] = $editData->nro_cuenta;
        $this->data['id_secretaria'] = $editData->id_secretaria;
        $this->data['id_dependencia'] = $editData->id_dependencia;
        $this->data['id_indexacion'] = $id;
        $this->data['id_programa'] = $editData->id_programa;
        $this->data['id_proyecto'] = $editData->id_proyecto;
        $this->data['tipo_pago'] = $editData->tipo_pago;
        $this->data['periodicidad_meses'] = $editData->periodicidad_meses;
        $this->data['control_vencimiento'] = $editData->control_vencimiento;
        $this->data['dias_alerta'] = $editData->dias_alerta;
        $this->data['seleccion_programa'] = $program;
    }

    if ($_SERVER['REQUEST_METHOD'] === "POST") {

        $_REQUEST['control_vencimiento'] = $this->input->post('control_vencimiento') ? 1 : 0;

        $this->form_validation->set_rules('id_secretaria', 'Secretaria', 'trim|in_select[0]');
        $this->form_validation->set_rules('id_proveedor', 'Proveedor', 'trim|in_select[0]');
        if (!isset($_REQUEST['id_indexacion']) && $_REQUEST['id_indexacion'] == NULL) {
            $this->form_validation->set_rules('nro_cuenta', 'Nro de cuenta', 'trim|required|callback_check_nro_cuenta');
        }
        $this->form_validation->set_rules('expediente', 'Expediente', 'trim|required');
        $this->form_validation->set_rules('tipo_pago', 'Tipo de pago', 'trim|required|in_select[0]');
        $this->form_validation->set_rules('periodicidad_meses', 'Periodicidad', 'trim|required|in_list[1,2]');
        $this->form_validation->set_rules('dias_alerta', 'Dias de alerta', 'trim|required|integer|greater_than[0]');


        if ($this->form_validation->run() != FALSE) {

            // 🔑 1. OBTENER el nro_cuenta afectado ANTES de la redirección
            $nro_cuenta_afectado = $this->input->post('nro_cuenta');

            if (isset($_REQUEST['id_indexacion']) && $_REQUEST['id_indexacion'] != NULL) {
                // Lógica de EDICIÓN
                $indexacion = $_POST["id_indexacion"];
                unset($_REQUEST["id_indexacion"]);

                $grabar_datos_array = array(
                    'seccion' => 'Actualización datos ' . $this->router->fetch_class(),
                    'mensaje' => 'Datos Actualizados ',
                    'estado' => 'success',
                    'status' => 'success',
                );
                $this->session->set_userdata('save_data', $grabar_datos_array);
                $_REQUEST['user_mod'] = $this->user->id;
                $this->db->update($this->data['tabla'], $_REQUEST, array('id' => $indexacion));
            } else {
                // Lógica de ALTA
                unset($_REQUEST["id_indexacion"]);

                $this->Manager_model->grabar_datos($this->data['tabla'], $_REQUEST);
                
                $grabar_datos_array = array(
                    'seccion' => 'Alta nuevas ' . $this->router->fetch_class(),
                    'mensaje' => 'Datos Grabados ',
                    'estado' => 'success',
                    'status' => 'success',
                );
                $this->session->set_userdata('save_data', $grabar_datos_array);
            }
            
            // --------------------------------------------------------
            // 🚨 INICIO MANTENIMIENTO: Recálculo de _lotes_resumen 🚨
            // --------------------------------------------------------
            if (!empty($nro_cuenta_afectado)) {
                
                // 2. Obtener los IDs de lotes asociados a este nro_cuenta que NO están consolidados
                //    Se usa JOIN para verificar la columna 'consolidado' en la tabla '_lotes'.
                $this->db->select('t1.id_lote')
                         ->from('_datos_api t1')
                         ->join('_lotes t2', 't1.id_lote = t2.id', 'inner')
                         ->where('t1.nro_cuenta', $nro_cuenta_afectado)
                         ->where('t2.consolidado', 0) // Filtro contra la tabla de lotes
                         ->distinct();

                $lotes_a_recalcular = $this->db->get()->result(); // Ejecutar la consulta
                
                // 3. Iterar y ejecutar el mantenimiento solo si se encontraron lotes
                if ($lotes_a_recalcular) {
                     // Cargamos el modelo que contiene la función de mantenimiento (si no está cargado globalmente)
                     $this->load->model('manager/Lecturas_model'); 
                     foreach ($lotes_a_recalcular as $lote) {
                          $this->Lecturas_model->actualizar_resumen_lote($lote->id_lote);
                     }
                }
            }
            // --------------------------------------------------------
            // FIN MANTENIMIENTO

            redirect(base_url('Admin/Indexaciones'));
        }
        $this->data['collapse'] = '';
    }
    $this->data['content'] = $this->load->view('manager/secciones/indexaciones/' . $this->router->fetch_method(), $this->data, TRUE);
    // $this->data['select_tipo_pago'] = $this->Manager_model->obtener_contenido_select('_tipo_pago', 'Seleccionar Tipo Pago','tip_nombre','tip_id' );

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
			base_url('assets/manager/js/secciones/' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . '.js'),
			// base_url('assets/manager/js/secciones/'.$this->router->fetch_class().'.js'),
		);
		$this->data['script_common'] = $this->script_common;
		$this->data['script'] = $script;


		// $this->form_validation->set_rules('secretaria', 'secretaria', 'trim|greater_than[0]');
		$this->form_validation->set_rules('id_secretaria', 'Secretaria', 'trim|in_select[0]');
		$this->form_validation->set_rules('id_proveedor', 'Proveedor', 'trim|in_select[0]');
		$this->form_validation->set_rules('nro_cuenta', 'Nro de cuenta', 'trim|required|callback_check_nro_cuenta');

		// $this->form_validation->set_rules('id_dependencia', 'Dependencia', '');in_select
		// $this->form_validation->set_rules('id_programa', 'ID Programa', 'trim|[0]');
		// $this->form_validation->set_rules('id_interno', 'ID interno', 'trim|required');

		if ($this->form_validation->run() == FALSE) {


			$this->data['content'] = $this->load->view('manager/secciones/indexaciones/' . $this->router->fetch_method(), $this->data, TRUE);

			$this->load->view('manager/head', $this->data);
			$this->load->view('manager/index', $this->data);
			$this->load->view('manager/footer', $this->data);
		} else {
			$datos = array(
				'id_programa' => $this->input->post('id_programa'),
				'id_interno' => $this->input->post('id_interno'),
				'descripcion' => $this->input->post('descripcion'),
			);

			$this->Proyectos_model->grabar_datos("_indexaciones", $_POST);
			redirect(base_url('Admin/Indexaciones'));
		}
	}

	public function check_nro_cuenta($str)
	{
		if ($data = $this->Manager_model->getwhere('_indexaciones', 'nro_cuenta ="' . $str . '"')) {
			$this->form_validation->set_message('check_nro_cuenta', 'El Nro de cuenta se encuentra registrado');
			return FALSE;
		} else {
			return true;
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
}
