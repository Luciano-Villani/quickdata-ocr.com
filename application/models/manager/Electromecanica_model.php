<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Electromecanica_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        // $this->order='';

    }


    public function get_indexaciones($_dataREQUEST)
    {
          // $this->db->from($_POST['table']);

          $this->db->select(
            '_indexaciones_canon.*
            ,_secretarias.secretaria as nombre_secretaria,
            _dependencias_canon.dependencia as nombre_dependencia,
            _proveedores_canon.nombre as nom_proveedor,
            _programas.descripcion as descr_programa,
            _proyectos.id as id_proyecto,
            _programas.id_interno as prog_id_interno,
            _proyectos.id_interno as proy_id_interno,
            _proyectos.descripcion as descr_proyecto,
            '
        );

        $this->db->join('_secretarias', '_secretarias.id = _indexaciones_canon.id_secretaria', 'rigth', true);
        $this->db->join('_proveedores_canon', '_proveedores_canon.id = _indexaciones_canon.id_proveedor', '');
        $this->db->join('_dependencias_canon', '_dependencias_canon.id = _indexaciones_canon.id_dependencia', 'left');
        $this->db->join('_programas', ' _indexaciones_canon.id_programa = _programas.id', '');
        $this->db->join('_proyectos', '_indexaciones_canon.id_proyecto = _proyectos.id', '');

        $my_column_order = array(
            '',
            '_indexaciones_canon.id',
            '_proveedores_canon.nombre',
            '',
            '_secretarias.secretaria',
            '_dependencias_canon.dependencia',
            '_indexaciones_canon.expediente',
            '_programas.descripcion',
            '_proyectos.descripcion'
        );

        $my_column_search = array(
            '_proveedores_canon.nombre',
            '_indexaciones_canon.nro_cuenta',
            '_secretarias.secretaria',
            '_dependencias_canon.dependencia',
            '_programas.descripcion',
            'UPPER(_proyectos.descripcion)',
            '_indexaciones_canon.expediente',
        );


           $query =  $this->db->where('nro_cuenta', $_dataREQUEST['nro_cuenta'])->get('_indexaciones_canon');

    
        return $query->result();
    }

    public function get_alldata($tabla, $where=false){
        $this->db->select('*');
        if($where){
            $this->db->where($where);
        }
        $query = $this->db->get($tabla);
        return $query->result();
 
     if ($query->result() > 0) {
         return $query->result();
     }
     return false;
     }

    public function get_dato_api_blanco($id_lote)
    {
        $this->db->select('_datos_api_canon.*,_datos_api_canon.id as id_file');
        $this->db->where('id_lote', $id_lote);
        $this->db->where('dato_api ','');
        $this->db->from('_datos_api_canon');
        return  $this->db->get()->result();
       
    }
    public function getBatchFilesbyId($id_lote)
    {
        $this->db->select('_datos_api_canon.*,_datos_api_canon.id as id_file');
        $this->db->where('id_lote', $id_lote);
        $this->db->from('_datos_api_canon');
        return  $this->db->get()->result();
    }

    public function getBatchFiles($codeLote)
    {
        $this->db->select('_datos_api_canon.*,_datos_api_canon.id as id_file');
        $this->db->like('code_lote', $codeLote);
        $this->db->from('_datos_api_canon');
        return  $this->db->get()->result();
    }
    public function getRows($postData)
    {
        $this->_get_datatables_query($postData);

        if ($postData['length'] != -1) {
            $this->db->limit($postData['length'], $postData['start']);
        }
        $query = $this->db->get();

        return $query->result();
    }
    public function countFiltered($postData)
    {
        $this->_get_datatables_query($postData);
        $query = $this->db->get();
        // echo $this->db->last_query();
        // die();

        return $query->num_rows();
    }
    private function _get_datatables_query($postData)
    {
        switch ($postData['table']) {

            case '_datos_api_canon':


                $this->db->select('*');

                $my_column_order = array(
                    '_datos_api_canon.id',
                    '_datos_api_canon.nro_cuenta',
                    '_datos_api_canon.code_lote',
                );
                $my_column_search = array(
                    '_datos_api_canon.nro_cuenta',
                    '_datos_api_canon.id',
                    '_datos_api_canon.code_lote',

                );

                    

                $this->order = array('_datos_api_canon.id' => 'desc');
                break;
            case '_proveedores_canon':
                $this->db->select('*');

                $my_column_order = array(
                    '_proveedores_canon.id',
                    '_proveedores_canon.codigo',
                    '_proveedores_canon.detalle_gasto',
                    '_proveedores_canon.objeto_gasto',
                    '_proveedores_canon.nombre',
                    '_proveedores_canon.fecha_alta',
                );
                $my_column_search = array(
                    '_proveedores_canon.id ',
                    '_proveedores_canon.codigo',
                    '_proveedores_canon.detalle_gasto',
                    '_proveedores_canon.objeto_gasto',
                    '_proveedores_canon.nombre',
                );

                $this->order = array('_proveedores_canon.id' => 'desc');
                break;

            case '_secretarias':
                $this->db->select('*');


                $my_column_order = array(
                    '',
                    '_secretarias.major',
                    '_secretarias.secretaria',
                );
                $my_column_search = array(
                    '_secretarias.major',
                    '_secretarias.secretaria',
                );


                $this->order = array('_secretarias.id' => 'desc');
                break;
            case '_programas':
                $this->db->select(
                    '_programas.descripcion,
                   _secretarias.secretaria,
                   _programas.id_interno as prog_id_interno,
                    _programas.id as id_programa,
                    UPPER(_programas.descripcion) as prog_descripcion,
                    UPPER(_secretarias.secretaria) as secretaria
                    '
                );

                // $this->db->join('_dependencias', '_proyectos.id_dependencia = _dependencias.id', '');
                $this->db->join('_secretarias', '_programas.id_secretaria = _secretarias.id ', '');

                $my_column_order = array(
                    '_programas.id_interno',
                    '_secretarias.secretaria',
                    '_programas.descripcion',
                );
                $my_column_search = array(
                    '_programas.id_interno',
                    '_programas.descripcion',
                    '_secretarias.secretaria',
                );

                $this->order = array('_programas.id' => 'desc');
                break;

                case '_consolidados_canon':

                    $this->db->select(
                        'CONCAT(_consolidados_canon.proveedor,
                        " (", _consolidados_canon.codigo_proveedor,")" ) as proveedora,
                        CONCAT(_consolidados_canon.jurisdiccion," ",_consolidados_canon.id_programa ) as sumajuris,
                        _consolidados_canon.id as id_consolidado,
                        UPPER(_consolidados_canon.secretaria), UPPER(_consolidados_canon.acuerdo_pago),
                        _consolidados_canon.proveedor,_consolidados_canon.*, 
                     ',
                    );
                
                    // $this->db->join('_tipo_pago', '_consolidados_canon.tipo_pago = _tipo_pago.tip_nombre', '');
                
                    $my_column_order = array(
                        '_consolidados_canon.id',
                        '_consolidados_canon.periodo_contable',
                        '_consolidados_canon.proveedor',
                        '_consolidados_canon.expediente',
                        '_consolidados_canon.secretaria',
                        '_consolidados_canon.jurisdiccion',
                        '_consolidados_canon.id_programa',
                        '_consolidados_canon.programa',
                        'sumajuris',
                        '_consolidados_canon.objeto',
                        '_consolidados_canon.dependencia',
                        '_consolidados_canon.dependencia_direccion',
                        //'_consolidados_canon.tipo_pago',
                        'UPPER(_consolidados_canon.acuerdo_pago)',
                        '_consolidados_canon.nro_cuenta',
                        '_consolidados_canon.nro_factura',
                        '_consolidados_canon.preventivas',
                        '_consolidados_canon.preventivas',
                        '_consolidados_canon.dependencia',
                        '_consolidados_canon.fecha_vencimiento',
                    );
                    $my_column_search = array(
                        '_consolidados_canon.periodo_contable',
                        'UPPER(_consolidados_canon.proveedor)',
                        'UPPER(_consolidados_canon.expediente)',
                        '_consolidados_canon.secretaria',
                        '_consolidados_canon.jurisdiccion',
                        '_consolidados_canon.programa',
                        '_consolidados_canon.objeto',
                        '_consolidados_canon.dependencia',
                        '_consolidados_canon.dependencia_direccion',
                        //'_consolidados_canon.tipo_pago',
                        'UPPER(_consolidados_canon.acuerdo_pago)',
                        '_consolidados_canon.nro_cuenta',
                        '_consolidados_canon.nro_factura',
                        '_consolidados_canon.id',
                        '_consolidados_canon.preventivas',
                        '_consolidados_canon.dependencia',
                        '_consolidados_canon.fecha_vencimiento',
                    );
                
                    $this->order = array(
                        '_consolidados_canon.id' => 'desc'
                    );
                
                    if ((isset($postData['id_proveedor'])) && $postData['id_proveedor'] != 'false' && (isset($postData['id_proveedor']) && $postData['id_proveedor'] != '')) {
                        $this->db->group_start();
                        foreach ($postData['id_proveedor'] as $prove) {
                            $this->db->or_where('id_proveedor', $prove);
                        }
                        $this->db->group_end();
                    }
                   // if ((isset($postData['tipo_pago']) && $postData['tipo_pago'] != 'false' &&  $postData['tipo_pago'] != '')) {
                    //    $this->db->group_start();
                    //    foreach ($postData['tipo_pago'] as $tipo) {
                    //        $this->db->or_where('_consolidados_canon.tipo_pago', $tipo);
                    //    }
                    //    $this->db->group_end();
                   // }
                
                    //if ((isset($postData['periodo_contable']) && $postData['periodo_contable'] != 'false' &&  $postData['periodo_contable'] != '')) {
                    //    $this->db->group_start();
                    //    foreach ($postData['periodo_contable'] as $peri) {
                     //       $this->db->or_where('_consolidados_canon.periodo_contable', $peri);
                    //    }
                     //   $this->db->group_end();
                   // }

                    //filtro de mes fc

                    if (isset($postData['mes_fc']) && $postData['mes_fc'] != 'false' && $postData['mes_fc'] != '') {
                        // Si mes_fc es un string, lo agregamos directamente
                        if (is_array($postData['mes_fc'])) {
                            // Manejo del caso en que mes_fc es un array
                            $this->db->group_start();
                            foreach ($postData['mes_fc'] as $mes) {
                                $this->db->or_where('_consolidados_canon.mes_fc', $mes);
                            }
                            $this->db->group_end();
                        } else {
                            // Aquí agregamos el string directamente
                            $this->db->where('_consolidados_canon.mes_fc', $postData['mes_fc']);
                        }
                    }

                    //filtro de año fc

                 
                    if (isset($postData['anio_fc']) && !empty($postData['anio_fc'])) {
                        if (is_array($postData['anio_fc'])) {
                            $this->db->group_start(); // Inicia un grupo para la consulta
                            foreach ($postData['anio_fc'] as $anio) {
                                $this->db->or_where('_consolidados_canon.anio_fc', $anio);
                            }
                            $this->db->group_end(); // Cierra el grupo
                        } else {
                            $this->db->where('_consolidados_canon.anio_fc', $postData['anio_fc']);
                        }
                    }

                 // Filtro registros donde 'cos_fi' es menor a 0.95

                 if (!empty($postData['cos_fi']) && $postData['cos_fi'] === 'true') {
                    $this->db->group_start(); // Iniciar un grupo para aislar la condición
                    $this->db->where('_consolidados_canon.cosfi <', 0.95); // Filtrar valores menores a 0.95
                    $this->db->where('_consolidados_canon.cosfi >', 0.0); // Filtrar valores mayores a 0.0
                    $this->db->group_end(); // Cerrar el grupo
                }

                // Filtro para Cos Fi menores a 0.095
                if (!empty($postData['tg_fi']) && $postData['tg_fi'] === 'true') {
                    $this->db->group_start(); // Iniciar un grupo para aislar la condición
                    $this->db->where('_consolidados_canon.tgfi >', 0.33); // Filtrar valores mayores a 0.33
                    $this->db->group_end(); // Cerrar el grupo
                }
            
                
    
                
                    if ((isset($postData['fecha']) && $postData['fecha'] != 'false' &&  $postData['fecha'] != '')) {
                        $dates = explode('-', $postData['fecha']);
                
                        $this->db->where("_consolidados_canon.fecha_consolidado >= '" . fecha_es(trim(str_replace('/', '-', $dates[0])), "Y-m-d", false) . " 00:00:01'  AND _consolidados_canon.fecha_consolidado <= '" . fecha_es(trim(str_replace('/', '-', $dates[1])), "Y-m-d", false) . " 23:59:59'");
                    }
                    break;
                
                

            case '_dependencias_canon':

                $this->db->select('_dependencias_canon.*,_dependencias_canon.id as id_dependencia , _secretarias.secretaria');
                $this->db->join('_secretarias', '_secretarias.id = _dependencias_canon.id_secretaria', '');


                $my_column_order = array(
                    'id_dependencia',
                    '_dependencias_canon.id',
                    '_dependencias_canon.dependencia',
                    '_dependencias_canon.direccion',
                );
                $my_column_search = array(
                    '_secretarias.secretaria',
                    '_dependencias_canon.dependencia',
                    '_dependencias_canon.direccion',
                );

                if ($postData['data_search'] != 'false' && (isset($postData['data_search']) && $postData['data_search'] != '')) {
                    //busca filtro secretaria
                    $this->db->group_start();
                    switch ($postData['type']) {

                        case 4:
                            $this->db->where("_secretarias.secretaria= '" . $postData['data_search'] . "'");
                            break;
                    }
                    $this->db->group_end();
                }
                $my_order = array('_dependencias_canon.id' => 'desc');
                break;

            case '_proyectos':

                // id actualizacion id de tabla
                $this->db->select(
                    '_proyectos.descripcion as p_descripcion, 
                    _proyectos.id ,
                    _proyectos.id_interno as p_id_interno,
                     _secretarias.id as id_secretaria,
                     _secretarias.secretaria,
                     _programas.descripcion as prog_descripcion,
                     _programas.id_interno as prog_id_interno,
         
                   
                    '
                );

                // $this->db->join('_dependencias', '_proyectos.id_dependencia = _dependencias.id', '');
                $this->db->join('_secretarias', '_proyectos.id_secretaria = _secretarias.id ', '');
                $this->db->join('_programas', '_programas.id = _proyectos.id_programa ', '');

                // $this->db->select('
                // _proyectos.*,
                // UPPER(_proyectos.descripcion),
                // UPPER(_proyectos.descripcion) as secretaria,
                //  _secretarias.secretaria,
                //  UPPER(_programas.descripcion) as programa'

                // );
                // $this->db->join('_secretarias', '_secretarias.id = _proyectos.id_secretaria', '');
                // $this->db->join('_programas', '_programas.id_interno = _proyectos.id_programa', '');


                $my_column_order = array(
                    '',
                    '_proyectos.descripcion',
                    '_programas.descripcion',
                    '_secretarias.secretaria',
                );
                $my_column_search = array(
                    '_proyectos.id_interno',
                    '_secretarias.secretaria',
                    '_proyectos.descripcion',
                    '_programas.descripcion',
                    // '_dependencias.dependencia', 
                    // '_dependencias.direccion', 
                );

                if ($postData['data_search'] != 'false' && (isset($postData['data_search']) && $postData['data_search'] != '')) {

                    die('acas');
                    //busca filtro secretaria
                    $this->db->group_start();
                    switch ($postData['type']) {

                        case 4:
                            // $this->db->where("_secretarias.secretaria= '" . $postData['data_search'] . "'");
                            break;
                    }
                    $this->db->group_end();
                }

                // $this->db->group_by('_proyectos.id_interno');
                // $my_order = array('_proyectos.id' => 'desc');

                $this->order = array('_proyectos.id' => 'desc');
                break;

            case '_lotes_canon':
                $this->db->select(
                    '_lotes_canon.*,
                    _lotes_canon.id as id_lote ,
                     _proveedores_canon.nombre,
                     _proveedores_canon.codigo as codigo,
                     users.*,
                    _datos_api_canon.nro_cuenta'
                );
                $this->db->join('_proveedores_canon', '_proveedores_canon.id = _lotes_canon.id_proveedor', '');
                $this->db->join('users', 'users.id = _lotes_canon.user_add', '');
                $this->db->join('_datos_api_canon', '_datos_api_canon.code_lote = _lotes_canon.code', 'RIGHT', false);
                $this->db->group_by('_lotes_canon.id', 'desc');
                $my_column_order = array(
                    '_lotes_canon.id',
                    '_proveedores_canon.codigo',
                    '_proveedores_canon.nombre',
                    '_lotes_canon.fecha_add',
                    '',
                    '',
                    '_lotes_canon.consolidado',
                    '_lotes_canon.user_add'
                );
                $my_column_search = array(
                    '_proveedores_canon.codigo',
                    '_proveedores_canon.nombre',
                    '_lotes_canon.consolidado',
                    '_lotes_canon.user_add',
                    '_datos_api_canon.nro_cuenta',
                    '_datos_api_canon.nro_factura',
                    '_datos_api_canon.nro_medidor',
                    '_lotes_canon.fecha_add'
                );
                $my_order = array('_lotes_canon.id' => 'desc');
                break;

            case '_indexaciones_canon':

                // $this->db->from($_POST['table']);

                $this->db->select(
                    '_indexaciones_canon.*
                    ,_secretarias.secretaria as nombre_secretaria,
                    _dependencias_canon.dependencia as nombre_dependencia,
                    _proveedores_canon.nombre as nom_proveedor,
                    _programas.descripcion as descr_programa,
                    _proyectos.id as id_proyecto,
                    _programas.id_interno as prog_id_interno,
                    _proyectos.id_interno as proy_id_interno,
                    _proyectos.descripcion as descr_proyecto,
                    '
                );

                $this->db->join('_secretarias', '_secretarias.id = _indexaciones_canon.id_secretaria', 'rigth', true);
                $this->db->join('_proveedores_canon', '_proveedores_canon.id = _indexaciones_canon.id_proveedor', '');
                $this->db->join('_dependencias_canon', '_dependencias_canon.id = _indexaciones_canon.id_dependencia', 'left');
                $this->db->join('_programas', ' _indexaciones_canon.id_programa = _programas.id', '');
                $this->db->join('_proyectos', '_indexaciones_canon.id_proyecto = _proyectos.id', '');

                $my_column_order = array(
                    '',
                    '_indexaciones_canon.id',
                    '_proveedores_canon.nombre',
                    '',
                    '_secretarias.secretaria',
                    '_dependencias_canon.dependencia',
                    '_indexaciones_canon.expediente',
                    '_programas.descripcion',
                    '_proyectos.descripcion'
                );

                $my_column_search = array(
                    '_proveedores_canon.nombre',
                    '_indexaciones_canon.nro_cuenta',
                    '_secretarias.secretaria',
                    '_dependencias_canon.dependencia',
                    '_programas.descripcion',
                    'UPPER(_proyectos.descripcion)',
                    '_indexaciones_canon.expediente',
                );
                if (isset($postData['data_search'])){
                
                    if ($postData['data_search'] != 'false' && (isset($postData['data_search']) && $postData['data_search'] != '')) {
                        
                        $this->db->group_start();
                        
                        $this->db->where("_indexaciones_canon.nro_cuenta= '" . $postData['data_search'] . "'");
                        
                        $this->db->group_end();
                    }
                    }
                $this->order = array(
                    '_indexaciones_canon.id' => 'desc'
                );

                break;
        }

        $i = 0;


        foreach ($my_column_search as $item) {

            if (isset($postData['search']['value']) &&  $postData['search']['value'] != '') {
                // first loop
                if ($i === 0) {
                    // open bracket
                    $this->db->group_start();
                    $this->db->like($item, $postData['search']['value']);
                } else {
                    $this->db->or_like($item, $postData['search']['value']);
                }

                // last loop
                if (count($my_column_search) - 1 == $i) {
                    // close bracket
                    $this->db->group_end();
                }
            }
            $i++;
        }
        if (isset($postData['order'])) {
            $this->db->order_by($my_column_order[$postData['order']['0']['column']], $postData['order']['0']['dir']);
        } else if (isset($this->order)) {

            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }

        // $this->db->from($_POST['table']);
        $this->db->from($_POST['table']);
    }
    public function countAll()
    {
        $this->db->from($_POST['table']);
        return $this->db->count_all_results();
    }
    public function cerrarLote()
    {

        $data = array(
            'cant' => $_POST['cant'],
            'code' => $_POST['code_lote'],

        );

        $this->db->where('id', $_POST['id_lote']);
        $this->db->update('_lotes', $data);
    }
    public function crearLote()
    {

        $data['user_add'] = $this->user->id;
        $data['id_proveedor'] = $_POST['id_proveedor'];
        $data['cant'] = $_POST['cant'];
        $data['code'] = $_POST['code_lote'];

        try {

            $query = $this->db->get_where('_lotes_canon', array('code' => $_POST['code_lote']));
            $currLote = $query->result();

            if ($currLote) {
                //urrLote->cant ++;

            } else {
                $this->db->insert('_lotes_canon', $data);
                $insertId = $this->db->insert_id();
                $query = $this->db->get_where('_lotes_canon', array('id' => $insertId));
                $currLote = $query->result();
            }

            $this->db->where('code_lote', $_POST['code_lote']);
            $this->db->from('_datos_api_canon');
            $totalFiles =  $this->db->count_all_results();

            $this->db->set('cant', $totalFiles);
            $this->db->where('code', $_POST['code_lote']);
            $this->db->update('_lotes_canon');


            $query = $this->db->get_where('_lotes_canon', array('code' => $_POST['code_lote']));
            $currLote = $query->result();

            return $currLote;
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function grabar_datos($tabla, $data, $electro = false)
    {
        if ($electro) {
            $data['user_add' . $tabla] = $this->user->id;
        } else {
            $data['user_add'] = $this->user->id;
        }

        try {
            $this->db->insert($tabla, $data);

            return true;
        } catch (Exception $e) {
            $response = array(
                'mensaje' => 'Error: ' . $e->getMessage(),
                'title' => 'Grabar Archivos',
                'status' => 'error',
            );
            echo json_encode($response);
            exit();

            return false;
        }
    }

    public function obtener_contenido_select($tabla, $defTxt = 'SELECCIONAR', $campo = NULL, $orden = 'id DESC', $title = true)
    {
        // Verificar si la tabla es '_consolidados_canon' para obtener años únicos
    if ($tabla === '_consolidados_canon') {
        $this->db->select('anio_fc'); // Seleccionar el campo de año
        $this->db->from($tabla);
        $this->db->where('anio_fc IS NOT NULL'); // Asegurarte de que no sean nulos
        $this->db->where('anio_fc !=', ''); // Asegurarte de que no estén vacíos
        $this->db->group_by('anio_fc'); // Agrupar por el campo anio_fc
        $this->db->order_by('anio_fc', 'ASC'); // Ordenar por el año
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $my_array = array();
            if ($title) {
                $my_array[0] = strtoupper($defTxt);
            }

            foreach ($query->result_array() as $data) {
                $my_array[$data['anio_fc']] = $data['anio_fc']; // Usar el array asociativo
            }

            return $my_array;
        }

        return FALSE;


    }
        // Seleccionar todos los campos, incluyendo procesar_por si existe
        $query = $this->db->select('*')
            ->order_by($orden)
            ->get($tabla);
    
        if ($query->result() > 0) {
    
            $my_array = array();
            if ($title) {
                $my_array[0] = strtoupper($defTxt);
            }
    
            foreach ($query->result_array() as $data) {
                switch ($tabla) {
                    case "_programas":
                    case "_proyectos":
                        $my_array[$data['id']] = $data['id'] . ' - * ' . strtoupper($data[$campo]);
                        break;
                    case "_secretarias":
                        $my_array[$data['id']] = $data['rafam'] . '  -  ' . strtoupper($data[$campo]);
                        break;
                    case "_tipo_pago":
                        $my_array[$data['tip_id']] = strtoupper($data[$campo]);
                        break;
                    case "_consolidados":
                        $my_array[$data['periodo_contable']] = strtoupper($data[$campo]);
                        break;
                    case "_proveedores":
                        // Para proveedores, agregar el campo procesar_por
                        $my_array[$data['id']] = [
                            'nombre' => strtoupper($data[$campo]),
                            'procesar_por' => $data['procesar_por']
                        ];
                        break;
                    default:
                        $my_array[$data['id']] = strtoupper($data[$campo]);
                }
            }
    
            return $my_array;
        }
    
        return FALSE;
    }


    public function getProveedores()
    {
        $query = $this->db->select("*")->get('_proveedores_canon');
        return $query->result();
    }

    public function getWhere($tabla, $where)
    {
        $query = $this->db->select('*')
            ->where($where)
            ->get($tabla);

        if ($query->result() > 0) {
            return $query->row();
        }

        return false;
    }
    public function getalldata($tabla, $where = false)
    {
        $this->db->select('*');
        if ($where) {
            $this->db->where($where);
        }
        $query = $this->db->get($tabla);
        return $query->result();

        if ($query->result() > 0) {
            return $query->result();
        }
        return false;
    }
    public function get_data($tabla, $id)
    {
        $query = $this->db->select('*')
            ->where('id', $id)
            ->get($tabla);

        if ($query->result() > 0) {
            return $query->row();
        }
        return false;
    }
    public function _get_data($tabla, $id)
    {

        $query = $this->db->select('*')
            ->where('id', $id)
            ->get($tabla);

        if ($query->result() > 0) {
            return $query->row();
        }
        return false;
    }

    public function get_data_api($tabla, $id)
    {
        $query = $this->db->select('*')
            ->where('id', $id)
            ->get($tabla);

        if ($query->result() > 0) {
            return $query->row();
        }
        return false;
    }
    public function count_data($tabla, $nro_cuenta)
    {

        $query = $this->db->select('*')
            ->where('nro_cuenta', $nro_cuenta)
            ->get($tabla);
        return $query->num_rows();
    }
    public function checkProveedor($id)
    {
        $query = $this->db->select('*')
            ->where('id_proveedor', $id)
            ->get('_indexaciones_canon');
        return $query->result();
    }
    public function get_indexacion($tabla, $nro_cuenta)
    {
        $query = $this->db->select('*')
            ->where('nro_cuenta', $nro_cuenta)
            ->get($tabla);

        if ($query->result() > 0) {
            return $query->row();
        } else {
            return false;
        }
    }

    
    public function consolidar_datos()
{
	if (isset($_REQUEST['id_file']) && $_POST['id_file'] != null) {
		$data = $this->Electromecanica_model->getwhere('_datos_api_canon', 'id=' . $_POST['id_file']);
		$files = array($data);
	} else {
		$files = $this->Electromecanica_model->getBatchFiles($_POST['code_lote']);
	}

	try {
		$error = true;
		foreach ($files as $file) {

			if (checkConsolidarCanon($file->id)) {
				$error = false;

				// Definir variables y obtener datos adicionales
				$dependencia = '';
				$id_proyecto = '';
				$id_programa = '';
				$programa_descripcion = '';
				$proyecto_descripcion = '';
				$id_interno_programa = '';
				$id_interno_proyecto = '';
				$dependencia_dependencia = '';
				$dependencia_direccion = '';
				
				$indexador = $this->Electromecanica_model->getWhere('_indexaciones_canon', 'nro_cuenta="' . $file->nro_cuenta . '"');
				$proveedor = $this->Electromecanica_model->get_proveedor_canon($indexador->id_proveedor);
				$secretaria = $this->Electromecanica_model->get_data('_secretarias', $indexador->id_secretaria);

				if ($dependencia = $this->Electromecanica_model->getWhere('_dependencias_canon', '_dependencias_canon.id="'.$indexador->id_dependencia .'" AND _dependencias_canon.id_secretaria = "'.$indexador->id_secretaria.'"')) {
					$dependencia_dependencia =  $dependencia->dependencia;
					$dependencia_direccion = $dependencia->direccion;
				}

				if ($programa = $this->Electromecanica_model->getWhere('_programas','id="' . $indexador->id_programa .'"' )) {
					$id_programa = $programa->id;
					$programa_descripcion = $programa->descripcion;
					$id_interno_programa = $programa->id_interno;
				}
				if ($proyecto = $this->Electromecanica_model->getWhere('_proyectos','id="' . $indexador->id_proyecto.'"')) {
					$id_proyecto = $proyecto->id;
					$proyecto_descripcion = $proyecto->descripcion;
					$id_interno_proyecto = $proyecto->id_interno;
				}

				$fechaVencimeinto = $file->vencimiento_del_pago;
				$mesVencimiento = explode('-', $fechaVencimeinto);
				$indicePeriodoContable = str_replace('0', '', $mesVencimiento[1]);
				$grPeriodos =  getPeriodos();
				$clavePeriodo = array_search(strtoupper(fecha_es(date("Y-m-d H:i:s"), 'F a', false)), $grPeriodos);

				// Array de consolidación con campos adicionales
				$dataBatch = array(
					'id_lectura_api' => $file->id,
					'id_indexador' => $indexador->id,
					'id_proveedor' => $proveedor->id,
					'proveedor' => $proveedor->nombre,
					'expediente' => $indexador->expediente,
					'secretaria' => $secretaria->secretaria,
					'id_secretaria' => $secretaria->id,
					'jurisdiccion' => $secretaria->major,
					'programa' => $programa_descripcion,
					'id_interno_programa' => $id_interno_programa,
					'id_programa' => $id_programa,
					'id_proyecto' => $id_proyecto,
					'id_interno_proyecto' => $id_interno_proyecto,
					'proyecto' => $proyecto_descripcion,
					'objeto' => $proveedor->objeto_gasto,
					'dependencia' =>  $dependencia_dependencia,
					'dependencia_direccion' =>  $dependencia_direccion,
					'nro_factura' => $file->nro_factura,
					'codigo_proveedor' => $proveedor->codigo,
					'tipo_pago' => get_tipoPago($indexador->tipo_pago),
					'nro_cuenta' => $indexador->nro_cuenta,
					'periodo_del_consumo' => $file->periodo_del_consumo,
					'fecha_vencimiento' => $file->vencimiento_del_pago,
					'mes_vencimiento' => $mesVencimiento[1],
					'preventivas' => date("Y-m-d H:i:s"),
					'importe' => $file->total_importe,
					'nro_medidor' => $file->nro_medidor,
					'periodo_contable' => strtoupper(fecha_es(date("Y-m-d H:i:s"), 'F a', false)),
					'lote' => $_POST['code_lote'],
					'user_consolidado' => $this->user->id,
					'fecha_consolidado' => $this->fecha_now,
					'nombre_archivo' => $file->nombre_archivo,
					'importe_1' => $file->total_importe,
					'acuerdo_pago' => isset($indexador->acuerdo_pago) ? $indexador->acuerdo_pago : '',
					'periodo' => $clavePeriodo,
					'mes_fc' => $file->mes_fc,
					'anio_fc' => $file->anio_fc,
					'unidad_medida' => $proveedor->unidad_medida,

					// Nuevos campos a consolidar
					'tipo_de_tarifa' => $file->tipo_de_tarifa,
					'consumo' => $file->consumo,
					'e_activa' => $file->e_activa,
					'e_reactiva' => $file->e_reactiva,
					'tgfi' => $file->tgfi,
					'cosfi' => isset($file->cosfi) ? str_replace(',', '.', $file->cosfi) : null,
					'nombre_cliente' => $file->nombre_cliente,
					'domicilio_de_consumo' => $file->domicilio_de_consumo,
					'p_contratada' => $file->p_contratada,
					'p_registrada' => $file->p_registrada,
					'p_excedida' => $file->p_excedida,
				);

				$this->Electromecanica_model->grabar_datos('_consolidados_canon', $dataBatch);

				$data = array(
					'consolidado' => 1,
					'user_consolidado' => $this->user->id,
					'fecha_consolidado' => $this->fecha_now,
				);
				$this->db->update('_datos_api_canon', $data, array('id' => $file->id));
				$this->db->update('_lotes_canon', $data, array('code' => $_POST['code_lote']));

			} else {
				$error = true;
			}
		}
		if($error){
			$response = array(
				'estado' => 'error',
				'title' => 'CONSOLIDACIONES',
				'mensaje' => 'Archivos anteriormente Consolidados'
			);
			echo json_encode($response);die();
		}else{
			$response = array(
				'status' => 'succes',
				'title' => 'CONSOLIDACIONES',
				'mensaje' => 'Archivo Consolidado'
			);
			echo json_encode($response);die();
		}
	} catch (Exception $e) {
		die('error');
	}
}


    
    
    public function get_proveedor_canon($id_proveedor) {
        $query = $this->db->select('*')
                          ->where('id', $id_proveedor)
                          ->get('_proveedores_canon');
    
        if ($query->result() > 0){
            return $query->row();
        } else {
            return null; // En caso de que no encuentre el proveedor
        }
    }

    public function countFilesCanon($codeLote)
	{
		$this->db->like('code_lote', $codeLote);
		$this->db->from('_datos_api_canon');
		return  $this->db->count_all_results();
	}
    public function contar_registros_por_proveedor_canon() {
        $this->db->select('id_proveedor, COUNT(*) as cantidad');
        $this->db->group_by('id_proveedor');
        $query = $this->db->get('_consolidados_canon');
        return $query->result();
    }
    
	
}
