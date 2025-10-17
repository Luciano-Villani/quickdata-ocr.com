<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Manager_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $sql = "SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));";
        $this->db->query($sql );
        // $this->order='';
            
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

            case '_proveedores':
                $this->db->select('*');


                $my_column_order = array(
                    '_proveedores.id',
                    '_proveedores.codigo',
                    '_proveedores.detalle_gasto',
                    '_proveedores.objeto_gasto',
                    '_proveedores.nombre',
                    '_proveedores.fecha_alta',
                );
                $my_column_search = array(
                    '_proveedores.codigo',
                    '_proveedores.detalle_gasto',
                    '_proveedores.objeto_gasto',
                    '_proveedores.nombre',
                );


                $this->order = array('_proveedores.id' => 'desc');
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

            case '_consolidados':

                $this->db->select(
                    'CONCAT(_consolidados.proveedor,
                    " (", _consolidados.codigo_proveedor,")" ) as proveedora,
                    CONCAT(_consolidados. jurisdiccion," ",_consolidados.id_programa ) as sumajuris,
                    _consolidados.id as id_consolidado,
                    UPPER(_consolidados.secretaria), UPPER(_consolidados.acuerdo_pago),
                    _consolidados.proveedor,_consolidados.*, 
                 ',
                );

                // $this->db->join('_tipo_pago', '_consolidados.tipo_pago = _tipo_pago.tip_nombre', '');

                $my_column_order = array(
                    '_consolidados.id',
                    '_consolidados.periodo_contable',
                    '_consolidados.proveedor',
                    '_consolidados.expediente',
                    '_consolidados.secretaria',
                    '_consolidados.jurisdiccion',
                    '_consolidados.id_programa',
                    '_consolidados.programa',
                    'sumajuris',
                    '_consolidados.objeto',
                    '_consolidados.dependencia',
                    '_consolidados.dependencia_direccion',
                    '_consolidados.tipo_pago',
                    'UPPER(_consolidados.acuerdo_pago)',
                    '_consolidados.nro_cuenta',
                    '_consolidados.nro_factura',
                    '_consolidados.preventivas',
                    '_consolidados.preventivas',
                    '_consolidados.dependencia',
                    '_consolidados.fecha_vencimiento',
                );
                $my_column_search = array(
                    '_consolidados.periodo_contable',
                    'UPPER(_consolidados.proveedor)',
                    'UPPER(_consolidados.expediente)',
                    '_consolidados.secretaria',
                    '_consolidados.jurisdiccion',
                    '_consolidados.programa',
                    '_consolidados.objeto',
                    '_consolidados.dependencia',
                    '_consolidados.dependencia_direccion',
                    '_consolidados.tipo_pago',
                    'UPPER(_consolidados.acuerdo_pago)',
                    '_consolidados.nro_cuenta',
                    '_consolidados.nro_factura',
                    '_consolidados.id',
                    '_consolidados.preventivas',
                    '_consolidados.dependencia',
                    '_consolidados.fecha_vencimiento',
                );

                $this->order = array(
                    '_consolidados.id' => 'desc'
                );



                if ((isset($postData['id_proveedor'])) && $postData['id_proveedor'] != 'false' && (isset($postData['id_proveedor']) && $postData['id_proveedor'] != '')) {
                    $this->db->group_start();
                    foreach ($postData['id_proveedor'] as $prove) {

                        $this->db->or_where('id_proveedor', $prove);
                    }
                    $this->db->group_end();
                }
                if ((isset($postData['tipo_pago']) && $postData['tipo_pago'] != 'false' &&  $postData['tipo_pago'] != '')) {


                    $this->db->group_start();
                    foreach ($postData['tipo_pago'] as $tipo) {

                        $this->db->or_where('_consolidados.tipo_pago', $tipo);
                    }
                    $this->db->group_end();
                }

                if ((isset($postData['periodo_contable']) && $postData['periodo_contable'] != 'false' &&  $postData['periodo_contable'] != '')) {
                    
                    $this->db->group_start();
                    foreach ($postData['periodo_contable'] as $peri) {
                        
                        $this->db->or_where('_consolidados.periodo_contable', $peri);
                    }
                    $this->db->group_end();
                }
                
                if ((isset($postData['fecha']) && $postData['fecha'] != 'false' &&  $postData['fecha'] != '')) {
                    $dates = explode('-', $postData['fecha']);

                    $this->db->where("_consolidados.fecha_consolidado >= '" . fecha_es(trim(str_replace('/','-',$dates[0])),"Y-m-d", false) . " 00:00:01'  AND _consolidados.fecha_consolidado <= '" . fecha_es(trim(str_replace('/','-',$dates[1])),"Y-m-d", false) . " 23:59:59'");
                }
                break;


            case '_dependencias':

                $this->db->select('_dependencias.*,_dependencias.id as id_dependencia , _secretarias.secretaria');
                $this->db->join('_secretarias', '_secretarias.id = _dependencias.id_secretaria', '');


                $my_column_order = array(
                    'id_dependencia',
                    '_dependencias.id',
                    '_dependencias.dependencia',
                    '_dependencias.direccion',
                );
                $my_column_search = array(
                    '_secretarias.secretaria',
                    '_dependencias.dependencia',
                    '_dependencias.direccion',
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
                $my_order = array('id' => 'desc');
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

            case '_lotes':
                $this->db->select(
                    '_lotes.*,
                    _lotes.id as id_lote ,
                     _proveedores.nombre,
                     _proveedores.codigo as codigo,
                     users.*,
                    _datos_api.nro_cuenta'
                    );
                $this->db->join('_proveedores', '_proveedores.id = _lotes.id_proveedor', '');
                $this->db->join('users', 'users.id = _lotes.user_add', '');
                $this->db->join('_datos_api', '_datos_api.code_lote = _lotes.code', 'RIGHT', false);
                $this->db->group_by('_lotes.id','desc');
                $my_column_order = array(
                    '_lotes.id', '_proveedores.codigo', 
                    '_proveedores.nombre', 
                    '_lotes.fecha_add', 
                    '', 
                    '', 
                    '_lotes.consolidado', 
                    '_lotes.user_add'
                );
                $my_column_search = array(
                    '_proveedores.codigo', 
                    '_proveedores.nombre', 
                    '_lotes.consolidado', 
                    '_lotes.user_add', 
                    '_datos_api.nro_cuenta', 
                    '_datos_api.nro_factura', 
                    '_datos_api.nro_medidor', 
                    '_lotes.fecha_add'
                );
                $my_order = array('_lotes.id' => 'desc');
                break;

            case '_indexaciones':

                $this->db->from($_POST['table']);
                $query = $this->db->get();
                // $datos = $query->result();
                $this->db->select(
                    '_indexaciones.*
                    ,_secretarias.secretaria as nombre_secretaria,
                    _dependencias.dependencia as nombre_dependencia,
                    _proveedores.nombre as nom_proveedor,
                    _programas.descripcion as descr_programa,
                    _proyectos.id as id_proyecto,
                    _programas.id_interno as prog_id_interno,
                    _proyectos.id_interno as proy_id_interno,
                    _proyectos.descripcion as descr_proyecto,
                    '
                );

                $this->db->join('_secretarias', '_secretarias.id = _indexaciones.id_secretaria', 'rigth', true);
                $this->db->join('_proveedores', '_proveedores.id = _indexaciones.id_proveedor', '');
                $this->db->join('_dependencias', '_dependencias.id = _indexaciones.id_dependencia', 'left');
                $this->db->join('_programas', ' _indexaciones.id_programa = _programas.id', 'LEFT');
                $this->db->join('_proyectos', '_indexaciones.id_proyecto = _proyectos.id', 'left');

                $my_column_order = array(
                    '',
                    '_indexaciones.id',
                    '_proveedores.nombre',
                    '',
                    '_secretarias.secretaria',
                    '_dependencias.dependencia',
                    '_indexaciones.expediente',
                    '_programas.descripcion',
                    '_proyectos.descripcion'
                );

                $my_column_search = array(
                    '_proveedores.nombre',
                    '_indexaciones.nro_cuenta',
                    '_secretarias.secretaria',
                    '_dependencias.dependencia',
                    '_programas.descripcion',
                    'UPPER(_proyectos.descripcion)',
                    '_indexaciones.expediente',
                );
                $this->order = array(
                    '_indexaciones.id' => 'desc'
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


    public function grabar_datos($tabla, $data)
    {
        $data['user_add'] = $this->user->id;

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
        $query = $this->db->select("*")->get('_proveedores');
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
    public function delete()
    {

        try {


            if ($_REQUEST['deletefile'] == 'true') {


               
                $file = $this->get_data('_datos_api', intval($_REQUEST['id']));

                if (is_file($file->nombre_archivo)) {
                    if (unlink($file->nombre_archivo)) {

                        $this->db->where('id', $file->id);
                        $this->db->delete('_datos_api');
                    }
                } else {
                    $this->db->where('id', $file->id);
                    $this->db->delete('_datos_api');
                }

                $totalFiles = $this->Lotes_model->countFiles($file->code_lote);

                if ($totalFiles > 0) {
                    $this->db->set('cant', $totalFiles);
                    $this->db->where('id', $file->id_lote);
                    $this->db->update('_lotes');
                } else {
                    $this->db->where('id', $file->id_lote);
                    $this->db->delete('_lotes');
                }
            } else {

                $file = $this->get_data('_datos_api', intval($_REQUEST['id']));
               
                $this->db->where('id', $file->id);
                $this->db->delete('_datos_api');

                $totalFiles = $this->Lotes_model->countFiles($file->code_lote);

                if ($totalFiles > 0) {
                    $this->db->set('cant', $totalFiles);
                    $this->db->where('id', $file->id_lote);
                    $this->db->update('_lotes');
                } else {
                    $this->db->where('id', $file->id_lote);
                    $this->db->delete('_lotes');
                }
            }
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

        // $this->db->where($_REQUEST['campo'], $_REQUEST['id']);
        // if ($this->db->delete($_REQUEST['tabla'])) {
        //     $response = array(
        //         'mensaje' => 'Datos borrados',
        //         'title' => str_replace('_', '', $_REQUEST['tabla']),
        //         'status' => 'success',
        //     );
        // };
        echo json_encode($response);
        exit();
    }
    public function delete_OLD()
    {



        try {

            if (isset($_REQUEST['deletefile'])) {
                $file = $this->get_data('_datos_api', intval($_REQUEST['id']));

                if (is_file($file->nombre_archivo)) {
                    if (unlink($file->nombre_archivo)) {

                        $this->db->where('id', $file->id);
                        $this->db->delete('_datos_api');
                    }
                } else {
                    $this->db->where('id', $file->id);
                    $this->db->delete('_datos_api');
                }

                $totalFiles = $this->Lotes_model->countFiles($file->code_lote);

                if ($totalFiles > 0) {
                    $this->db->set('cant', $totalFiles);
                    $this->db->where('id', $file->id_lote);
                    $this->db->update('_lotes');
                } else {
                    $this->db->where('id', $file->id_lote);
                    $this->db->delete('_lotes');
                }
            } else {
                $this->db->where('id', $file->id);
                $this->db->delete('_datos_api');

                $totalFiles = $this->Lotes_model->countFiles($file->code_lote);

                if ($totalFiles > 0) {
                    $this->db->set('cant', $totalFiles);
                    $this->db->where('id', $file->id_lote);
                    $this->db->update('_lotes');
                } else {
                    $this->db->where('id', $file->id_lote);
                    $this->db->delete('_lotes');
                }
            }
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



        // echo '<pre>';
        // var_dump( $file ); 
        // echo '</pre>';
        // die();
        // $total = 0;
        // foreach ($file as $data) {
        // 	if (is_file($data->nombre_archivo)) {
        // 		if (unlink($data->nombre_archivo)) {
        // 			$total++;
        // 			$this->db->where('nombre_archivo', $data->nombre_archivo);
        // 			$this->db->delete('_datos_api');
        // 		}
        // 	} else {
        // 		$this->db->where('nombre_archivo', $data->nombre_archivo);
        // 		$this->db->delete('_datos_api');
        // 		// die('no');
        // 	}
        // }
        // $this->db->where('code', $_REQUEST['code']);
        // $this->db->delete('_lotes');
        // $response = array(
        // 	'total' => $total,
        // 	'status' => 'success'
        // );
        // echo json_encode($response);



        $this->db->where($_REQUEST['campo'], $_REQUEST['id']);
        if ($this->db->delete($_REQUEST['tabla'])) {
            $response = array(
                'mensaje' => 'Datos borrados',
                'title' => str_replace('_', '', $_REQUEST['tabla']),
                'status' => 'success',
            );
        };
        echo json_encode($response);
        exit();
    }
}
