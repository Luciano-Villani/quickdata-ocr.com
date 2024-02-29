<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class TipoPago_model extends CI_Model
{


    public function getTiposPagos(){
        $query = $this->db->select("*")->get('_tipo_pago');
        return $query->result();
    }
}