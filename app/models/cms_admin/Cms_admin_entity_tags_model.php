<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * Entity tag mapping (products / categories → tags_master).
 */
class Cms_admin_entity_tags_model extends Cms_admin_base_model
{
    public function get_entity_types()
    {
        $table = 'sma_cms_entities_master';
        if (!$this->db->table_exists($table)) {
            return array();
        }

        $this->ensure_entities_master_seed($table);
        $rows = $this->fetch_entity_master_rows($table, true);
        if (empty($rows)) {
            $rows = $this->fetch_entity_master_rows($table, false);
        }

        return $this->normalize_entity_type_rows($rows);
    }

    /**
     * @param string $table
     * @param bool   $active_only
     * @return array
     */
    private function fetch_entity_master_rows($table, $active_only)
    {
        $select = $this->entities_master_select_expr($table);
        $this->db->select($select, false);
        $this->db->from($table);
        if ($active_only && $this->db->field_exists('is_active', $table)) {
            $this->db->group_start();
            $this->db->where('is_active', 1);
            $this->db->or_where('is_active IS NULL', null, false);
            $this->db->group_end();
        }
        $order_col = $this->db->field_exists('entity_name', $table) ? 'entity_name' : ($this->db->field_exists('name', $table) ? 'name' : 'id');
        $this->db->order_by($order_col, 'ASC');
        $q = $this->db->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * @param string $table
     * @return string
     */
    private function entities_master_select_expr($table)
    {
        $id = 'id';
        if ($this->db->field_exists('entity_code', $table)) {
            $code = 'entity_code';
        } elseif ($this->db->field_exists('code', $table)) {
            $code = 'code AS entity_code';
        } else {
            $code = "'' AS entity_code";
        }
        if ($this->db->field_exists('entity_name', $table)) {
            $name = 'entity_name';
        } elseif ($this->db->field_exists('name', $table)) {
            $name = 'name AS entity_name';
        } else {
            $name = 'entity_code AS entity_name';
        }

        return $id . ', ' . $code . ', ' . $name;
    }

    /**
     * @param string $table
     */
    private function ensure_entities_master_seed($table)
    {
        if ((int) $this->db->count_all($table) > 0) {
            return;
        }
        $defaults = array(
            array('entity_code' => 'product', 'entity_name' => 'Product'),
            array('entity_code' => 'category', 'entity_name' => 'Category'),
        );
        foreach ($defaults as $row) {
            $insert = $row;
            if ($this->db->field_exists('is_active', $table)) {
                $insert['is_active'] = 1;
            }
            if ($this->db->field_exists('created_at', $table)) {
                $insert['created_at'] = date('Y-m-d H:i:s');
            }
            $this->db->insert($table, $insert);
        }
    }

    /**
     * @param array $rows
     * @return array
     */
    private function normalize_entity_type_rows(array $rows)
    {
        $out = array();
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id < 1) {
                continue;
            }
            $code = isset($row['entity_code']) ? trim((string) $row['entity_code']) : '';
            $name = isset($row['entity_name']) ? trim((string) $row['entity_name']) : '';
            if ($name === '' && $code !== '') {
                $name = ucfirst($code);
            }
            if ($name === '') {
                $name = 'Entity #' . $id;
            }
            $out[] = array(
                'id'          => $id,
                'entity_code' => $code,
                'entity_name' => $name,
            );
        }

        return $out;
    }

    public function get_entities_by_type($entity_master_id)
    {
        $entity_master_id = (int) $entity_master_id;
        if ($entity_master_id <= 0) {
            return array();
        }
        $master_table = 'sma_cms_entities_master';
        if (!$this->db->table_exists($master_table)) {
            return array();
        }
        $this->db->select($this->entities_master_select_expr($master_table), false);
        $this->db->from($master_table);
        $this->db->where('id', $entity_master_id);
        if ($this->db->field_exists('is_active', $master_table)) {
            $this->db->group_start();
            $this->db->where('is_active', 1);
            $this->db->or_where('is_active IS NULL', null, false);
            $this->db->group_end();
        }
        $type = $this->db->get()->row_array();
        if (!$type) {
            $this->db->select($this->entities_master_select_expr($master_table), false);
            $this->db->from($master_table);
            $this->db->where('id', $entity_master_id);
            $type = $this->db->get()->row_array();
        }
        if (!$type) {
            return array();
        }

        $code = strtolower(trim((string) $type['entity_code']));
        if ($code === 'product') {
            $products_table = 'products';
            $product_name_expr = $this->product_name_expr($products_table);
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
        if ($code === 'blog') {
            if ($this->db->table_exists('sma_cms_blogs')) {
                $this->db->select('id, title AS name', false);
                $this->db->from('sma_cms_blogs');
                if ($this->db->field_exists('is_active', 'sma_cms_blogs')) {
                    $this->db->where('is_active', 1);
                }
                if ($this->db->field_exists('status', 'sma_cms_blogs')) {
                    $this->db->where('status', 'published');
                }
                if ($this->db->field_exists('title', 'sma_cms_blogs')) {
                    $this->db->order_by('title', 'ASC');
                } else {
                    $this->db->order_by('id', 'ASC');
                }
                $q = $this->db->get();
                $rows = $q && $q->num_rows() > 0 ? $q->result_array() : array();
                $out = array();
                foreach ($rows as $row) {
                    $label = isset($row['name']) ? trim((string) $row['name']) : '';
                    if ($label === '') {
                        $label = 'Blog #' . (int) $row['id'];
                    }
                    $out[] = array('id' => (int) $row['id'], 'name' => $label);
                }
                return $out;
            }
            $pages = 'sma_cms_pages';
            if (!$this->db->table_exists($pages)) {
                return array();
            }
            $this->db->select('id, page_name AS name', false);
            $this->db->from($pages);
            if ($this->db->field_exists('page_type', $pages)) {
                $this->db->where('page_type', 'blog');
            }
            if ($this->db->field_exists('page_name', $pages)) {
                $this->db->order_by('page_name', 'ASC');
            } else {
                $this->db->order_by('id', 'ASC');
            }
            $q = $this->db->get();
            $rows = $q->num_rows() > 0 ? $q->result_array() : array();
            $out = array();
            foreach ($rows as $row) {
                $label = isset($row['name']) ? trim((string) $row['name']) : '';
                if ($label === '') {
                    $label = 'Page #' . (int) $row['id'];
                }
                $out[] = array('id' => (int) $row['id'], 'name' => $label);
            }
            return $out;
        }
        return array();
    }

    public function get_tags_master()
    {
        if (!isset($this->cms_pages_model)) {
            $this->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_model');
        }
        return $this->cms_pages_model->getTagsMasterRaw();
    }

    /**
     * @param string $entity_code product|category|blog
     * @return array
     */
    public function get_tags_master_for_entity($entity_code)
    {
        $this->load->helper('cms_tags');
        if (!isset($this->cms_pages_model)) {
            $this->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_model');
        }
        $entity_code = cms_sanitize_entity_scope_code($entity_code);
        if ($entity_code === '') {
            return array();
        }
        $this->cms_pages_model->ensureEntitySchemaTagsSeeded();
        $all = $this->cms_pages_model->getTagsMasterRaw();

        return cms_filter_tags_master_for_form($all, 'entity', $entity_code);
    }

    /**
     * @param int $entity_master_id
     * @return string
     */
    public function get_entity_code_by_id($entity_master_id)
    {
        $entity_master_id = (int) $entity_master_id;
        if ($entity_master_id <= 0) {
            return '';
        }
        $row = $this->db
            ->select('entity_code')
            ->from('sma_cms_entities_master')
            ->where('id', $entity_master_id)
            ->get()
            ->row_array();
        return is_array($row) && !empty($row['entity_code'])
            ? strtolower(trim((string) $row['entity_code']))
            : '';
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
        $product_name_expr = $this->product_name_expr($products, 'p');

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

    /**
     * Insert or update one entity tag mapping row (head-script import).
     *
     * @param int    $entity_master_id
     * @param int    $entity_id
     * @param int    $tag_id
     * @param string $property_name
     * @param string $value
     * @return bool
     */
    public function upsertEntityTagValue($entity_master_id, $entity_id, $tag_id, $property_name, $value)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        $tag_id = (int) $tag_id;
        $property_name = trim((string) $property_name);
        $value = trim((string) $value);

        if ($entity_master_id <= 0 || $entity_id <= 0 || $tag_id <= 0 || $property_name === '' || $value === '') {
            return false;
        }

        $table = 'sma_cms_entity_tag_mapping';
        $where = array(
            'entity_master_id' => $entity_master_id,
            'entity_id'        => $entity_id,
            'tag_id'           => $tag_id,
            'property_name'    => $property_name,
        );

        $exists = $this->db->where($where)->get($table, 1)->row_array();
        if ($exists) {
            return (bool) $this->db->where('id', (int) $exists['id'])->update($table, array(
                'value'      => $value,
                'is_dynamic' => 1,
            ));
        }

        return (bool) $this->db->insert($table, array(
            'entity_master_id' => $entity_master_id,
            'entity_id'        => $entity_id,
            'tag_id'           => $tag_id,
            'property_name'    => $property_name,
            'value'            => $value,
            'is_dynamic'       => 1,
        ));
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

    private function product_name_expr($products_table, $alias = '')
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
