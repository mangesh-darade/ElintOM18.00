<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Entity_mapping_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_entity_types()
    {
        $q = $this->db
            ->select('id, entity_code, entity_name')
            ->from('sma_cms_entities_master')
            ->where('is_active', 1)
            ->group_by('id')
            ->order_by('entity_name', 'ASC')
            ->get();
        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    public function get_entities_by_type($entity_master_id)
    {
        $entity_master_id = (int) $entity_master_id;
        if ($entity_master_id <= 0) {
            return array();
        }
        $type = $this->db
            ->select('id, entity_code')
            ->from('sma_cms_entities_master')
            ->where('id', $entity_master_id)
            ->where('is_active', 1)
            ->get()
            ->row_array();
        if (!$type) {
            return array();
        }

        $code = strtolower(trim((string) $type['entity_code']));
        if ($code === 'product') {
            $products_table = 'products';
            $product_name_expr = $this->get_product_name_expr($products_table);
            $q = $this->db
                ->select('id, ' . $product_name_expr . ' AS name', false)
                ->from($products_table)
                ->order_by('name', 'ASC')
                ->get();
            $rows = $q->num_rows() > 0 ? $q->result_array() : array();
            $out = array();
            foreach ($rows as $row) {
                $out[] = array('id' => (int) $row['id'], 'name' => (string) $row['name']);
            }
            return $out;
        }
        if ($code === 'category') {
            $q = $this->db->select('id, name')->from('categories')->order_by('name', 'ASC')->get();
            $rows = $q->num_rows() > 0 ? $q->result_array() : array();
            $out = array();
            foreach ($rows as $row) {
                $out[] = array('id' => (int) $row['id'], 'name' => (string) $row['name']);
            }
            return $out;
        }
        return array();
    }

    public function get_tags_master()
    {
        $q = $this->db
            ->select('id, tag_name, tag_type, category')
            ->from('sma_cms_tags_master')
            ->order_by('tag_name', 'ASC')
            ->get();
        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    public function save_mapping($entity_master_id, $entity_id, array $tag_rows)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id <= 0 || $entity_id <= 0) {
            return false;
        }

        $insert_rows = $this->prepare_rows($entity_master_id, $entity_id, $tag_rows);
        if (empty($insert_rows)) {
            return false;
        }

        $table = 'sma_cms_entity_tag_mapping';
        $this->db->trans_start();
        foreach ($insert_rows as $row) {
            $exists = $this->db->where(array(
                'entity_master_id' => $row['entity_master_id'],
                'entity_id'        => $row['entity_id'],
                'tag_id'           => $row['tag_id'],
                'property_name'    => $row['property_name'],
            ))->get($table)->row_array();
            if ($exists) {
                $this->db->where('id', (int) $exists['id'])->update($table, array(
                    'value' => $row['value'],
                    'is_dynamic' => 1,
                ));
            } else {
                $this->db->insert($table, $row);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function update_mapping($old_entity_master_id, $old_entity_id, $new_entity_master_id, $new_entity_id, array $tag_rows)
    {
        $old_entity_master_id = (int) $old_entity_master_id;
        $old_entity_id = (int) $old_entity_id;
        $new_entity_master_id = (int) $new_entity_master_id;
        $new_entity_id = (int) $new_entity_id;
        if ($new_entity_master_id <= 0 || $new_entity_id <= 0) {
            return false;
        }
        $insert_rows = $this->prepare_rows($new_entity_master_id, $new_entity_id, $tag_rows);
        if (empty($insert_rows)) {
            return false;
        }

        $table = 'sma_cms_entity_tag_mapping';
        $this->db->trans_start();
        $this->db->where('entity_master_id', $old_entity_master_id)->where('entity_id', $old_entity_id)->delete($table);
        foreach ($insert_rows as $row) {
            $exists = $this->db->where(array(
                'entity_master_id' => $row['entity_master_id'],
                'entity_id'        => $row['entity_id'],
                'tag_id'           => $row['tag_id'],
                'property_name'    => $row['property_name'],
            ))->get($table)->row_array();
            if ($exists) {
                $this->db->where('id', (int) $exists['id'])->update($table, array(
                    'value' => $row['value'],
                    'is_dynamic' => 1,
                ));
            } else {
                $this->db->insert($table, $row);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_mapping_list()
    {
        $table = 'sma_cms_entity_tag_mapping';
        $master = 'sma_cms_entities_master';
        $products = 'products';
        $categories = 'categories';
        $product_name_expr = $this->get_product_name_expr($products, 'p');

        $q = $this->db
            ->select(
                'etm.entity_master_id, etm.entity_id, MIN(etm.id) AS id, COUNT(etm.id) AS total_tags, MIN(etm.created_at) AS created_at,' .
                ' em.entity_name, em.entity_code,' .
                ' ' . $product_name_expr . ' AS product_name, c.name AS category_name',
                false
            )
            ->from($table . ' etm')
            ->join($master . ' em', 'em.id = etm.entity_master_id', 'left')
            ->join($products . ' p', 'p.id = etm.entity_id AND em.entity_code = "product"', 'left')
            ->join($categories . ' c', 'c.id = etm.entity_id AND em.entity_code = "category"', 'left')
            ->group_by(array('etm.entity_master_id', 'etm.entity_id'))
            ->order_by('id', 'DESC')
            ->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    public function get_mapping_details($entity_master_id, $entity_id)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id <= 0 || $entity_id <= 0) {
            return null;
        }

        $type = $this->db
            ->select('id, entity_code, entity_name')
            ->from('sma_cms_entities_master')
            ->where('id', $entity_master_id)
            ->get()
            ->row_array();
        if (!$type) {
            return null;
        }

        $rows = $this->db
            ->select('id, entity_master_id, entity_id, tag_id, property_name, value')
            ->from('sma_cms_entity_tag_mapping')
            ->where('entity_master_id', $entity_master_id)
            ->where('entity_id', $entity_id)
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
        if (empty($rows)) {
            return null;
        }

        return array(
            'entity_master_id' => $entity_master_id,
            'entity_id' => $entity_id,
            'entity_code' => $type['entity_code'],
            'entity_name' => $type['entity_name'],
            'rows' => $rows,
        );
    }

    public function delete_mapping($entity_master_id, $entity_id)
    {
        return (bool) $this->db
            ->where('entity_master_id', (int) $entity_master_id)
            ->where('entity_id', (int) $entity_id)
            ->delete('sma_cms_entity_tag_mapping');
    }

    private function prepare_rows($entity_master_id, $entity_id, array $tag_rows)
    {
        $prepared = array();
        foreach ($tag_rows as $row) {
            $tag_id = isset($row['tag_id']) ? (int) $row['tag_id'] : 0;
            $property_name = isset($row['property_name']) ? trim((string) $row['property_name']) : '';
            $value = isset($row['value']) ? trim((string) $row['value']) : '';
            if ($tag_id <= 0 || $property_name === '' || $value === '') {
                continue;
            }
            $key = $entity_master_id . '|' . $entity_id . '|' . $tag_id . '|' . strtolower($property_name);
            $prepared[$key] = array(
                'entity_master_id' => (int) $entity_master_id,
                'entity_id'        => (int) $entity_id,
                'tag_id'           => $tag_id,
                'property_name'    => $property_name,
                'value'            => $value,
                'is_dynamic'       => 1,
            );
        }
        return array_values($prepared);
    }

    private function get_product_name_expr($products_table, $alias = '')
    {
        $prefix = $alias !== '' ? $alias . '.' : '';
        $has_product_name = $this->db->field_exists('product_name', $products_table);
        $has_name = $this->db->field_exists('name', $products_table);

        if ($has_product_name && $has_name) {
            return 'COALESCE(' . $prefix . 'product_name, ' . $prefix . 'name)';
        }
        if ($has_product_name) {
            return $prefix . 'product_name';
        }
        if ($has_name) {
            return $prefix . 'name';
        }
        return "''";
    }
}

