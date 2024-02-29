<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Proveedores_model extends CI_Model
{
    public function list_proveedores_dt()
    {
        $draw = intval(2);
        $start = intval(0);
        $length = intval(0);

        if (isset($_POST['search']['value']) and $_POST['search']['value'] != NULL) {
            $query = "select * from _proveedores where nombre like '%".$_POST['search']['value']."%' or codigo like '%".$_POST['search']['value']."%' or objeto_gasto like '%".$_POST['search']['value']."%'";
            $resultados = $this->db->query($query);
        }else{
            $query = "SELECT * FROM _proveedores";
            $resultados = $this->db->query($query);
        }


        

        $datos = [];

        foreach ($resultados->result() as $r) {
            if ($r->activo == '1' && $r->urlapi != '') {
                $estado = '<span class="badge badge-success">activo</span>';
            } else {
                $estado = '<span class="badge badge-danger">inactivo</span>';
            }

            $fecha_alta = fecha_es($r->fecha_alta, TRUE);

            $datos[] = array(
                $r->id,
                $r->codigo,
                $r->nombre,
                $r->objeto_gasto,
                $r->detalle_gasto,
                $fecha_alta,
                $estado,
            );
        }

        $result = array(
            "draw" => $draw,
            "recordsTotal" => $resultados->num_rows(),
            "recordsFiltered" => $resultados->num_rows(),
            "data" => $datos
        );

        echo json_encode($result);
    }

    public function get_proveedor($id){

        $query = $this->db->select('*')
                          ->where('id', $id)
                          ->get('_proveedores');

      if ($query->result() > 0){
 					
          return  $query->row(); 
 
      }
    }

}