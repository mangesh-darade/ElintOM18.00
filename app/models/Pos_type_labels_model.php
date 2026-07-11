<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model for sma_pos_type_labels table.
 * Fetches menu/label text by pos_type and label_key for use by lang() via MY_Lang.
 */
class Pos_type_labels_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Check if pos_type_labels table exists (avoids error if SQL not run yet).
     * @return bool
     */
    private function _table_exists() {
        return $this->db->table_exists('pos_type_labels');
    }

    /**
     * Get label value for a pos_type and label_key.
     *
     * @param string $pos_type e.g. 'restaurant', 'pharma'
     * @param string $label_key e.g. 'sales', 'products'
     * @return string|null label_value if found, null otherwise
     */
    public function get_label($pos_type, $label_key) {
    if (empty($pos_type) || empty($label_key) || !$this->_table_exists()) {
        return null;
    }

    // Use a separate DB connection to avoid interfering with any active query builder
    $db = $this->load->database('default', TRUE);

    $q = $db->get_where('pos_type_labels', array(
        'pos_type'  => $pos_type,
        'label_key' => $label_key
    ), 1);

    if ($q && $q->num_rows() > 0) {
        $row = $q->row();
        return $row->label_value;
    }

    return null;
}

    /**
     * Get all labels for a pos_type (for caching).
     *
     * @param string $pos_type
     * @return array [ label_key => label_value ]
     */
    public function get_all_by_pos_type($pos_type) {
        if (empty($pos_type) || !$this->_table_exists()) {
            return array();
        }
        $q = $this->db->get_where('pos_type_labels', array('pos_type' => $pos_type));
        $out = array();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $out[$row->label_key] = $row->label_value;
            }
        }
        return $out;
    }

    /**
     * Add or update a label for pos_type and label_key.
     *
     * @param string $pos_type
     * @param string $label_key
     * @param string $label_value
     * @return bool
     */
    public function set_label($pos_type, $label_key, $label_value) {
        if (empty($pos_type) || empty($label_key) || !$this->_table_exists()) {
            return false;
        }
        $label_value = trim($label_value);
        $existing = $this->db->get_where('pos_type_labels', array(
            'pos_type'  => $pos_type,
            'label_key' => $label_key
        ), 1);
        if ($existing->num_rows() > 0) {
            return $this->db->where(array('pos_type' => $pos_type, 'label_key' => $label_key))
                ->update('pos_type_labels', array('label_value' => $label_value));
        }
        return $this->db->insert('pos_type_labels', array(
            'pos_type'   => $pos_type,
            'label_key'  => $label_key,
            'label_value'=> $label_value
        ));
    }

    /**
     * Delete a label by id.
     *
     * @param int $id
     * @return bool
     */
    public function delete_label($id) {
        if (!$this->_table_exists()) {
            return false;
        }
        return $this->db->where('id', (int) $id)->limit(1)->delete('pos_type_labels');
    }

    /**
     * Get full rows for a pos_type (for listing in settings).
     *
     * @param string $pos_type
     * @return array of objects (id, pos_type, label_key, label_value)
     */
    public function get_rows_by_pos_type($pos_type) {
        if (empty($pos_type) || !$this->_table_exists()) {
            return array();
        }
        $q = $this->db->order_by('label_key', 'asc')
            ->get_where('pos_type_labels', array('pos_type' => $pos_type));
        return $q->num_rows() > 0 ? $q->result() : array();
    }
}




