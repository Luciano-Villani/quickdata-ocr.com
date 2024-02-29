<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Manager_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
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
        return $query->num_rows();
    }
    private function _get_datatables_query($postData)
    {

        switch ($postData['table']) {

            case '_consolidados':

  
                $this->db->select(
                    '_tipo_pago.*, CONCAT(_consolidados.proveedor," (", _consolidados.codigo_proveedor,")" ) as proveedora,CONCAT(_consolidados. jurisdiccion," ", _consolidados.id_programa ) as sumajuris,_consolidados.id as id_consolidado,LOWER(_consolidados.secretaria),LOWER(_consolidados.proveedor),_consolidados.*, CONCAT(users.first_name," ", users.last_name) as user_add');

                $this->db->join('users', 'users.id = _consolidados.user_add', '');
                $this->db->join('_tipo_pago', '_tipo_pago.tip_nombre = _consolidados.tipo_pago', '');


                $my_column_order = array(
                    '_consolidados.id',
                    '',
                    '',
                    '_consolidados.periodo_contable',
                    'proveedora',
                    '_consolidados.expediente',
                    '_consolidados.secretaria',
                    '_consolidados.jurisdiccion',
                    '_consolidados.programa',
                    'sumajuris',
                    '_consolidados.objeto',
                    '_consolidados.dependencia',
                    '_consolidados.dependencia_direccion',
                    '_consolidados.tipo_pago',
                    '_consolidados.nro_cuenta',
                    '_consolidados.nro_factura',
                    '_consolidados.id',
                    '_consolidados.preventivas',
                    '_consolidados.preventivas',
                    '_consolidados.dependencia',
                    '_consolidados.fecha_vencimiento',
                );
                $my_column_search = array('
                _consolidados.nro_factura',
                    '_consolidados.importe',
                    '_consolidados.proveedor',
                    '_consolidados.codigo_proveedor',
                    '_consolidados.id_programa',
                    '_consolidados.objeto',
                    '_consolidados.jurisdiccion',
                    '_consolidados.secretaria',
                    '_consolidados.preventivas',
                    '_consolidados.dependencia',
                    '_consolidados.dependencia_direccion',
                    '_consolidados.fecha_vencimiento'
                );

                $this->order = array('id_consolidado' => 'desc');

                if ($postData['data_search'] != 'false' && (isset($postData['data_search']) && $postData['data_search'] != '')) {

                    //busca filtro fecha
                    switch ($postData['type']) {
                        case 1:
                            $dates = explode('@', $postData['data_search']);
                            $this->db->where("_consolidados.fecha_vencimiento >= '" . $dates[0] . "'  AND _consolidados.fecha_vencimiento <= '" . $dates[1] . " 00:00:01'");
                            break;
                        case 2:
                            $this->db->where("_consolidados.proveedor = '" . $postData['data_search'] . "'");
                            break; 
                        case 3:
                            $this->db->where("_consolidados.tipo_pago = '" . $postData['data_search'] . "'");
                            break;      
                        case 4:
                    
                            $this->db->where("_consolidados.periodo_contable = '" . $postData['data_search'] . "'");
                            break;
                    }
                }

        
                if ((isset($postData['id_proveedor'])) && $postData['id_proveedor'] != 'false' && (isset($postData['id_proveedor']) && $postData['id_proveedor'] != '')) 
                {
                    $this->db->group_start();
                    foreach($postData['id_proveedor'] as $prove){

                        $this->db->or_where('id_proveedor',$prove);
                    }
                    $this->db->group_end();
                }          
                if ((isset($postData['tipo_pago']) && $postData['tipo_pago'] != 'false' &&  $postData['tipo_pago'] != '')) 
                {

                    $this->db->group_start();
                    foreach($postData['tipo_pago'] as $tipo){

                        $this->db->or_where('_tipo_pago.tip_id',$tipo);
                    }
                    $this->db->group_end();
                }                
                
                if ((isset($postData['periodo_contable']) && $postData['periodo_contable'] != 'false' &&  $postData['periodo_contable'] != '')) 
                {

                    $this->db->group_start();
                    foreach($postData['periodo_contable'] as $peri){

                        $this->db->or_where('_consolidados.periodo_contable',$peri);
                    }
                    $this->db->group_end();
                }
                break;


            case '_dependencias':

                $this->db->select('_dependencias.*,_dependencias.id as id_dependencia , _secretarias.secretaria,users.*, CONCAT(users.first_name," ", users.last_name) as user_add');
                $this->db->join('_secretarias', '_secretarias.id = _dependencias.id_secretaria', '');
                $this->db->join('users', 'users.id = _dependencias.user_add', '');

                $my_column_order = array('id_dependencia', '_dependencias.dependencia', '_dependencias.direccion', 'users.last_name', 'users.first_name');
                $my_column_search = array('_secretarias.secretaria', '_dependencias.dependencia', '_dependencias.direccion', 'users.last_name', 'users.first_name');
                $my_order = array('id' => 'desc');
                break;
            case '_lotes':

                $this->db->select('_lotes.*,_lotes.id as id_lote , _proveedores.*,users.*');
                $this->db->join('_proveedores', '_proveedores.id = _lotes.id_proveedor', '');
                $this->db->join('users', 'users.id = _lotes.user_add', '');

                $my_column_order = array('_lotes.id', '_proveedores.codigo', '_proveedores.nombre', '_lotes.fecha_add', '', '', '_lotes.consolidado', '_lotes.user_add');
                $my_column_search = array('_proveedores.codigo', '_proveedores.nombre', '_lotes.consolidado', '_lotes.user_add', '_lotes.fecha_add');
                $my_order = array('id_lote' => 'desc');
                break;
            case '_indexaciones':

                // $this->db->select('_indexaciones.*,');

                $this->db->from($_POST['table']);
                $query = $this->db->get();
                $datos = $query->result();


                foreach ($datos as $r) {

                    if (($r->id_dependencia == 0 || is_null($r->id_dependencia)) && ($r->direccion_dependencia != "" && $r->nombre_dependencia != "")) {
                        //die('no');
                        $this->db->select('_dependencias.*');
                        $this->db->like('_dependencias.dependencia', $r->nombre_dependencia);
                        $this->db->like('_dependencias.direccion', $r->direccion_dependencia);
                        $this->db->from('_dependencias');
                        $id = $this->db->get();

                        $id = $id->row()->id;

                        if ($id) {
                            $this->db->set('id_dependencia', $id);
                            $this->db->where('id', $r->id);
                            $this->db->update('_indexaciones');
                            $result = $this->db->affected_rows();
                        }
                    }
                }
                ;
                /*
_dependencias.dependencia as nombre_dependencia,
                _programas.descripcion as descr_programa,
                _proyectos.descripcion as descr_proyecto,
                _obras.descripcion as descr_obra
                ,_secretarias.secretaria as nombre_secretaria,
                    _dependencias.dependencia as nombre_dependencia,
*/
                $this->db->select(
                    '_indexaciones.*
                    ,_secretarias.secretaria as nombre_secretaria,
                    _dependencias.dependencia as nombre_dependencia,
                    _programas.descripcion as descr_programa,
                    _proyectos.descripcion as descr_proyecto,
                '
                );

                // $this->db->select('_indexaciones.*,_dependencias.direccion as dire_depe,_dependencias.id as id_dependencia , _secretarias.secretaria,users.*, CONCAT(users.first_name," ", users.last_name) as user_add');
                $this->db->join('_secretarias', '_secretarias.id = _indexaciones.id_secretaria', 'rigth', true);
                $this->db->join('_dependencias', '_dependencias.id = _indexaciones.id_dependencia', 'left');
                $this->db->join('_programas', '_programas.id_interno = _indexaciones.id_programa AND _programas.id_secretaria = _indexaciones.id_secretaria', 'left');
                $this->db->join('_proyectos', '_proyectos.id_interno = _indexaciones.id_proyecto AND _proyectos.id_programa = _indexaciones.id_programa AND _proyectos.id_secretaria = _indexaciones.id_secretaria', 'LEFT');

                // $this->db->join('_programas a', 'a.id_secretaria = _indexaciones.id_secretaria', 'left');
                // $this->db->join('users','users.id = _dependencias.user_add','');
                $my_column_order = array('_indexaciones.id', '_secretarias.secretaria', '_dependencias.dependencia', '_programas.descripcion');
                $my_column_search = array('_indexaciones.nro_cuenta', '_secretarias.secretaria', '_dependencias.dependencia', '_programas.descripcion');
                $my_order = array('_indexaciones.id' => 'desc');
                $this->db->group_by('_indexaciones.id');
                break;
        }

        $i = 0;

        foreach ($my_column_search as $item) {

            if ($postData['search']['value']) {
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
       
            die($this->order);
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

    public function obtener_contenido_select($tabla, $defTxt = 'SELECCIONAR', $campo = NULL, $orden = 'id DESC', $title=true)
    {
        $query = $this->db->select('*')
            ->order_by($orden)
            ->get($tabla);

        if ($query->result() > 0) {

            $my_array = array();
            if($title){
                $my_array[0] = $defTxt;
            }
            foreach ($query->result_array() as $data) {

                switch ($tabla) {
                    case "_programas":
                    case "_proyectos":
                        $my_array[$data['id']] = $data['id_interno'] . ' - ' . $data[$campo];
                        break;
                    case "_secretarias":

                        $my_array[$data['id']] = $data['rafam'] . '  -  ' . $data[$campo];
                        break;
                    case "_tipo_pago":

                        $my_array[$data['tip_id']] = $data[$campo];
                        break;
                    case "_consolidados":
                        $my_array[$data['periodo_contable']] = $data[$campo];
                        break;
                    default:
                        $my_array[$data['id']] = $data[$campo];
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
}
