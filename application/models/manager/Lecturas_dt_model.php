<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lecturas_dt_model extends CI_Model
{
    // Define la tabla principal
    var $table = '_datos_api';
    
    // Columnas que DataTables puede buscar (searchable)
    // Asegúrate de que estas columnas existan en la tabla _datos_api
    var $column_search = array('t1.nro_cuenta', 't1.nro_factura', 't1.fecha_emision', 't1.total_importe', 'p.nombre'); 
    
    // Columnas que DataTables puede ordenar (orderable)
    // El índice 0 es típicamente para la columna No. (sin ordenar)
    var $column_order = array(null, 'p.nombre', 't1.nro_cuenta', 't1.nro_medidor', 't1.nro_factura', 't1.periodo_del_consumo', 't1.fecha_emision', 't1.vencimiento_del_pago', 't1.total_importe', 't1.total_vencido', 't1.proximo_vencimiento', 't1.nombre_archivo', null);
    
    // Orden por defecto
    var $order = array('t1.id' => 'desc'); 

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function _get_datatables_query()
    {
        // 1. SELECT y JOINS
        $this->db->select('t1.*, p.nombre as nombre_proveedor');
        $this->db->from($this->table . ' t1');
        // Asumiendo que _datos_api tiene una columna 'id_proveedor' para el JOIN
        $this->db->join('_proveedores p', 'p.id = t1.id_proveedor', 'left');

        // 2. Lógica de Búsqueda (Search)
        $i = 0;
        if (isset($_POST['search']['value']) && !empty($_POST['search']['value'])) {
            $value = $_POST['search']['value'];
            $this->db->group_start(); 
            foreach ($this->column_search as $item) {
                if ($i === 0) {
                    $this->db->like($item, $value);
                } else {
                    $this->db->or_like($item, $value);
                }
                $i++;
            }
            $this->db->group_end();
        }

        // 3. Lógica de Ordenación (Order)
        if (isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    // Obtener los datos paginados
    public function get_datatables()
    {
        $this->_get_datatables_query();
        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']); // 💡 Paginación en SQL
        }
        $query = $this->db->get();
        return $query->result();
    }

    // Contar registros después de aplicar el filtro
    public function count_filtered()
    {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    // Contar el total de registros en la tabla
    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }
}
