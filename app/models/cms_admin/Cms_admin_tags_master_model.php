<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * Admin CRUD for sma_cms_tags_master (visibility + scope).
 */
class Cms_admin_tags_master_model extends Cms_admin_base_model
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public function get_all_for_admin()
    {
        if (!isset($this->cms_pages_model)) {
            $this->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_model');
        }
        $this->cms_pages_model->ensureTagsMasterVisibilityColumns();

        $table = 'sma_cms_tags_master';
        $select = 'id, tag_name, tag_type, category, page_type';
        if ($this->db->field_exists('show_on_page', $table)) {
            $select .= ', show_on_page, show_on_entity';
        }
        if ($this->db->field_exists('updated_at', $table)) {
            $select .= ', updated_at';
        }

        $q = $this->db
            ->select($select, false)
            ->from($table)
            ->order_by('category', 'ASC')
            ->order_by('tag_name', 'ASC')
            ->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function get_by_id($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }
        if (!isset($this->cms_pages_model)) {
            $this->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_model');
        }
        $this->cms_pages_model->ensureTagsMasterVisibilityColumns();

        $table = 'sma_cms_tags_master';
        $row = $this->db->where('id', $id)->get($table, 1)->row_array();

        return is_array($row) && !empty($row['id']) ? $row : null;
    }

    /**
     * @param int   $id
     * @param array $in
     * @return bool
     */
    public function update_tag($id, array $in)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }

        $this->load->helper('cms_tags');
        if (!isset($this->cms_pages_model)) {
            $this->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_model');
        }
        $this->cms_pages_model->ensureTagsMasterVisibilityColumns();

        $table = 'sma_cms_tags_master';
        $existing = $this->get_by_id($id);
        if (!$existing) {
            return false;
        }

        $update = array();
        if (isset($in['category'])) {
            $update['category'] = trim((string) $in['category']);
        }
        if (isset($in['tag_type'])) {
            $update['tag_type'] = trim((string) $in['tag_type']);
        }
        if (isset($in['page_type'])) {
            $update['page_type'] = cms_sanitize_tags_scope($in['page_type']);
        }
        if (array_key_exists('show_on_page', $in)) {
            $update['show_on_page'] = !empty($in['show_on_page']) ? 1 : 0;
        }
        if (array_key_exists('show_on_entity', $in)) {
            $update['show_on_entity'] = !empty($in['show_on_entity']) ? 1 : 0;
        }

        if (empty($update)) {
            return true;
        }

        if ($this->db->field_exists('updated_at', $table)) {
            $update['updated_at'] = date('Y-m-d H:i:s');
        }

        return (bool) $this->db->where('id', $id)->update($table, $update);
    }
}
