<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Graph_model extends Manager_model
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('manager/Manager_model');
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


        return $query->num_rows();
    }
    private function _get_datatables_query($postData)
    {

        switch ($postData['table']) {

            case '_consolidadosGr':
         
                $this->db->select(
                    'SUM(_consolidados.importe_1) as total, secretaria,
                     proveedor, 
                     periodo, 
                     periodo_contable'

                );
           
                // $this->db->or_where('proveedor', "EDENOR");
               
                // // $this->db->or_where('id_proveedor', 10);
                // $this->db->or_where('id_proveedor', 4);
                if ((isset($postData['id_proveedor'])) && $postData['id_proveedor'] != 'false' && (isset($postData['id_proveedor'])&& $postData['id_proveedor'] != '' && $postData['id_proveedor'] != 0))
                {


                    $this->db->group_start();

                        $this->db->where('id_proveedor', $postData['id_proveedor']);
                   
                    $this->db->group_end();
                }

                if (isset($postData['id_secretaria']) && $postData['id_secretaria'] != 'false' && (isset($postData['id_secretaria']) && $postData['id_secretaria'] != 0 && $postData['id_secretaria'] != '')) {

                    $title = $postData['id_secretaria'];

                    $this->db->group_start();

                    $this->db->where('id_secretaria', $postData['id_secretaria']);

                    $this->db->
                    group_end();
                }

                if ((isset($postData['periodo_contable'])  && $postData['periodo_contable'] != "PERIODO CONTABLE") ) {

                       $this->db->group_start();

                    $this->db->where('periodo_contable', $postData['periodo_contable']);

                    $this->db->group_end();
                }
                if (isset($postData['id_proyecto']) && $postData['id_proyecto'] != 0) {

                       $this->db->group_start();

                    $this->db->where('id_proyecto', $postData['id_proyecto']);

                    $this->db->group_end();
                }
                if (isset($postData['id_programa']) && $postData['id_programa'] != 0) {

                       $this->db->group_start();

                    $this->db->where('id_programa', $postData['id_programa']);

                    $this->db->group_end();
                }


                $this->db->group_by('periodo, proveedor');
                $my_column_order = array(
                    '_consolidados.periodo',
                    '_consolidados.id',
                    '_consolidados.proveedor',
                    '_consolidados.importe',
                    '_consolidados.fecha_consolidado',
                    '_consolidados.fecha_alta',

                );
                $my_column_search = array(
                    'UPPER(_consolidados.proveedor)',
                    '_consolidados.periodo_contable',
                    '_consolidados.importe',
                    '_consolidados.secretaria',
                );
            
                break;



        }

        $i = 0;


        foreach ($my_column_search as $item) {

            if (isset($postData['search']['value']) && $postData['search']['value'] != '') {
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


        // $this->db->from($_REQUEST['table']);
        if ($_REQUEST['table'] == '_consolidadosGr') {
            $this->db->from('_consolidados');
        } else {
            $this->db->from($_REQUEST['table']);
        }
    }
    public function countAll()
    {
        if ($_REQUEST['table'] == '_graph_api') {
            $this->db->from('_consolidados');
        } else {
            $this->db->from($_REQUEST['table']);
        }
        return $this->db->count_all_results();
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
    public function cerrarLote()
    {

        $data = array(
            'cant' => $_REQUEST['cant'],
            'code' => $_REQUEST['code_lote'],

        );

        $this->db->where('id', $_REQUEST['id_lote']);
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
                        $my_array[$data['id']] = $data['id_interno'] . ' - * ' . strtoupper($data[$campo]);
                        break;
                    case "_secretarias":
                        $my_array[$data['id']] = strtoupper($data[$campo]);
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

    public function getProveedores($array = false)
    {
        $query = $this->db->select("*")->get('_proveedores');
        if ($array) {
            return $query->result_array();
        }
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
        }
        ;
        echo json_encode($response);
        exit();
    }
}
