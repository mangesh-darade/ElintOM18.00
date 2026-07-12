<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Webshop_settings_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getWebshopSettings() {
        $q = $this->db->get('webshop_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateWebshopSettings($data) {

        $this->db->where('id', '1');
        if ($this->db->update('webshop_settings', $data)) {
            return true;
        }
        return false;
    }

    public function getThemeSections($theme = "theme_1") {


        $this->db->select("`id`, `section_name`, `section_title`, `display_status`, `display_order`");

        $this->db->from("webshop_homepage_sections");

        $this->db->where(["is_active" => 1, "$theme" => 1]);

        $this->db->order_by("display_order", 'asc');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getActiveSections($theme = "theme_1") {


        $this->db->select("`id`, `section_name`, `section_title`, `section_data`");

        $this->db->from("webshop_homepage_sections");

        $this->db->where(["is_active" => 1, "$theme" => 1, "display_status" => 1]);

        $this->db->order_by("display_order", 'asc');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function updateWebshopSections($data) {

        if ($this->db->update_batch('webshop_homepage_sections', $data, 'id')) {
            return TRUE;
        }
        return FALSE;
    }

    public function updateFeatures($data) {

        if ($this->db->update_batch('webshop_features', $data, 'id')) {
            return TRUE;
        }
        return FALSE;
    }

    public function get_categories($eshop=null) {

        $where['is_active'] = 1;

        if((bool)$eshop){
            $where['in_eshop'] = 1;
        }
        
        $parent_id = null;
        if((bool)$parent_id){
            $where['parent_id'] = $parent_id;
        }
        
        $q = $this->db->select('id, code, name, image, parent_id')->where($where)->order_by('name', 'asc')->get('categories');

        if ($q->num_rows() > 0) {

            foreach ($q->result() as $row) {

                if ((int) $row->parent_id > 0) {
                    $data[$row->parent_id][$row->id] = $row;
                } else {
                    $data['main'][$row->id] = $row;
                }
            }

            return $data;
        }
        return false;
    }

    public function get_category_products($category_id = null) {
        
        $where = ['in_eshop'=>1, 'is_active'=>1];
        
        if($category_id){
            $where['category_id'] = $category_id;
        }
        
        $q = $this->db->select('id, name, code, image, category_id, subcategory_id')
                ->where($where)
                ->get('products');
        
        if ($q->num_rows() > 0) {

            foreach ($q->result() as $row) {

                if ((int)$row->subcategory_id > 0) {
                    $data[$row->category_id][$row->subcategory_id][] = $row;
                } else {
                    $data[$row->category_id][] = $row;
                }
            }

            return $data;
        }
        return false;
    }
    
    public function get_features() {

        $q = $this->db->select('id, title, subtitle, icon, is_active')
                ->from('webshop_features')
                ->get();

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function get_sliders() {

        $q = $this->db->get('webshop_sliders');

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[$row->slide_key] = (array) $row;
            }
            return $data;
        }
        return false;
    }

    public function updateWebshopSliderSettings($data) {

        if ($this->db->update_batch('webshop_sliders', $data, 'slide_key')) {
            return TRUE;
        }
        return FALSE;
    }
    
    
    
    public function getCustomPages($page_key=null) {
        
        if($page_key){
            $q = $this->db->where(['md5(id)' => $page_key])->get('webshop_static_pages');
        } else {
            $q = $this->db->order_by('is_active', 'desc')->get('webshop_static_pages');
        }
        
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[$row->page_key] = (array) $row;
            }
            return $data;
        }
        return false;
    }
    
    
    public function get_warehouses() {
        
       return $this->db->select('id, code, name')->where(['in_eshop'=>1, 'is_active'=>1, 'is_deleted'=>0, 'is_disabled'=>0, ])->get('warehouses')->result();
    }
    
    public function get_billers() {
        
       return $this->db->select('id, company, name')->where(['group_name'=>'biller'])->get('companies')->result();
    }
    
    /*
     * To update categories & products eshop status
     */
    public function update_eshop_status(string $tableName , array $Where, array $Data) {
    
        $this->db->where($Where)->update($tableName, $Data);
    
        return $this->db->affected_rows();
    }
    
    
    
    
    
    
    

    /**
     * Normalize sma_website_setting row keys: mysqli may return `ID` / `Fields` etc.; views expect lowercase keys.
     *
     * @param array $row
     * @return array
     */
    private function _normalize_storefront_identity_row(array $row) {
        $pk = null;
        foreach (array('id', 'ID', 'Id', 'setting_id', 'SETTING_ID') as $k) {
            if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                $pk = (int) $row[$k];
                break;
            }
        }
        if ($pk !== null && $pk > 0) {
            $row['id'] = $pk;
        }
        foreach (array(array('fields', 'Fields', 'FIELDS'), array('value', 'Value', 'VALUE'), array('icons', 'Icons', 'ICONS')) as $aliases) {
            $canonical = strtolower($aliases[0]);
            if (!isset($row[$canonical]) || $row[$canonical] === null) {
                foreach ($aliases as $k) {
                    if ($k === $canonical) {
                        continue;
                    }
                    if (array_key_exists($k, $row)) {
                        $row[$canonical] = $row[$k];
                        break;
                    }
                }
            }
        }
        return $row;
    }

    /**
     * True when sma_cms_webshop_header_footer exists.
     */
    public function header_footer_schema_ready() {
        return $this->db->table_exists('sma_cms_webshop_header_footer');
    }

    private function _normalize_webshop_header_footer_row(array $row) {
        $pk = null;
        foreach (array('id', 'ID') as $k) {
            if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                $pk = (int) $row[$k];
                break;
            }
        }
        if ($pk !== null && $pk > 0) {
            $row['id'] = $pk;
        }
        $aliases = array(
            'section_type' => array('Section_type', 'SECTION_TYPE'),
            'field_key'    => array('Field_key', 'FIELD_KEY'),
            'layoutname'   => array('Layoutname', 'LAYOUTNAME'),
            'label'        => array('Label', 'LABEL'),
            'value'        => array('Value', 'VALUE'),
            'icons'        => array('Icons', 'ICONS'),
            'sort_order'   => array('Sort_order', 'SORT_ORDER'),
            'is_active'    => array('Is_active', 'IS_ACTIVE'),
        );
        foreach ($aliases as $canon => $alts) {
            if (!isset($row[$canon]) || $row[$canon] === null) {
                foreach ($alts as $alt) {
                    if (isset($row[$alt])) {
                        $row[$canon] = $row[$alt];
                        break;
                    }
                }
            }
        }
        if (isset($row['sort_order'])) {
            $row['sort_order'] = (int) $row['sort_order'];
        }
        if (isset($row['is_active'])) {
            $row['is_active'] = (int) $row['is_active'] ? 1 : 0;
        }
        if (isset($row['layoutname']) && $row['layoutname'] !== null && $row['layoutname'] !== '') {
            $this->load->helper('cms_layout');
            $row['layoutname'] = cms_storefront_normalize_profile_slug($row['layoutname']);
        }
        return $row;
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function _prepare_webshop_header_footer_layoutname(array $data) {
        if (!$this->header_footer_schema_ready()) {
            return $data;
        }
        $this->load->helper('cms_layout');
        if (!cms_storefront_header_footer_has_layoutname_column()) {
            return $data;
        }
        if (!empty($data['layoutname'])) {
            $data['layoutname'] = cms_storefront_normalize_profile_slug($data['layoutname']);
            return $data;
        }
        $ln = cms_storefront_resolve_layoutname(
            isset($data['field_key']) ? $data['field_key'] : '',
            isset($data['value']) ? $data['value'] : '',
            isset($data['section_type']) ? $data['section_type'] : 'header'
        );
        $data['layoutname'] = $ln !== '' ? $ln : null;
        return $data;
    }

    /**
     * Storefront identity list (sma_cms_webshop_header_footer).
     */
    public function get_storefront_identity_rows() {
        if (!$this->header_footer_schema_ready()) {
            return array();
        }
        $this->db->order_by('section_type', 'ASC');
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('field_key', 'ASC');
        $q = $this->db->get('sma_cms_webshop_header_footer');
        if ($q->num_rows() === 0) {
            return array();
        }
        $out = array();
        foreach ($q->result_array() as $row) {
            $out[] = $this->_normalize_webshop_header_footer_row($row);
        }
        return $out;
    }

    public function get_storefront_identity_by_id($id) {
        if (!$this->header_footer_schema_ready()) {
            return null;
        }
        $q = $this->db->where('id', (int) $id)->limit(1)->get('sma_cms_webshop_header_footer');
        if ($q->num_rows() === 0) {
            return null;
        }
        return $this->_normalize_webshop_header_footer_row($q->row_array());
    }

    /**
     * @param array $data keys: section_type, field_key, label, value, icons (optional), sort_order, is_active
     */
    public function insert_storefront_identity(array $data) {
        if (!$this->header_footer_schema_ready()) {
            return false;
        }
        $data = $this->_prepare_webshop_header_footer_layoutname($data);
        return (bool) $this->db->insert('sma_cms_webshop_header_footer', $data);
    }

    public function update_storefront_identity($id, array $data) {
        if (!$this->header_footer_schema_ready()) {
            return false;
        }
        if ($this->db->field_exists('layoutname', 'sma_cms_webshop_header_footer')) {
            $data = $this->_prepare_webshop_header_footer_layoutname($data);
        }
        $this->db->where('id', (int) $id);
        return (bool) $this->db->update('sma_cms_webshop_header_footer', $data);
    }

    public function delete_storefront_identity($id) {
        if (!$this->header_footer_schema_ready()) {
            return false;
        }
        $this->db->where('id', (int) $id);
        return (bool) $this->db->delete('sma_cms_webshop_header_footer');
    }

    /**
     * Duplicate check per section + field_key.
     */
    public function storefront_identity_field_key_exists($section, $fields, $exclude_id = 0) {
        if (!$this->header_footer_schema_ready()) {
            return false;
        }
        $section = strtolower(trim((string) $section));
        $fields = strtolower(trim((string) $fields));
        $this->db->where('section_type', $section);
        $this->db->where('field_key', $fields);
        if ((int) $exclude_id > 0) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('sma_cms_webshop_header_footer') > 0;
    }

    /**
     * Map one DB row to API row (fields/value/icons + section metadata).
     *
     * @param object $r Query row from webshop_header_footer
     * @return stdClass
     */
    private function _webshop_header_footer_row_to_api_object($r) {
        $o = new stdClass();
        $o->fields = (string) $r->field_key;
        $o->value = isset($r->value) ? (string) $r->value : '';
        $o->icons = isset($r->icons) && $r->icons !== null && $r->icons !== '' ? (string) $r->icons : null;
        $sec = isset($r->section_type) ? strtolower(trim((string) $r->section_type)) : '';
        $o->section_type = ($sec === 'header' || $sec === 'footer') ? $sec : '';
        $o->label = isset($r->label) ? (string) $r->label : '';
        $o->sort_order = isset($r->sort_order) ? (int) $r->sort_order : 0;
        return $o;
    }

    /**
     * Rows shaped like legacy website_setting objects for getsettings API / webshopapi.
     * Only active rows are exposed. Includes section_type / label / sort_order for storefront rows.
     *
     * @return array<int, stdClass>
     */
    public function get_header_footer_contain_as_website_setting_objects() {
        if (!$this->header_footer_schema_ready()) {
            return array();
        }
        $this->db->where('is_active', 1);
        $this->db->order_by('section_type', 'ASC');
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('field_key', 'ASC');
        $q = $this->db->get('sma_cms_webshop_header_footer');
        $out = array();
        foreach ($q->result() as $r) {
            $fk = isset($r->field_key) ? strtolower(trim((string) $r->field_key)) : '';
            if ($fk !== '' && strpos($fk, 'builder_config') !== false) {
                continue;
            }
            $out[] = $this->_webshop_header_footer_row_to_api_object($r);
        }
        return $out;
    }

    /**
     * Same rows as above, grouped by section for themes/API consumers.
     *
     * @return array{header: array<int, stdClass>, footer: array<int, stdClass>}
     */
    public function get_website_setting_sections_for_api() {
        $out = array('header' => array(), 'footer' => array());
        if (!$this->header_footer_schema_ready()) {
            return $out;
        }
        $this->db->where('is_active', 1);
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('field_key', 'ASC');
        $q = $this->db->get('sma_cms_webshop_header_footer');
        foreach ($q->result() as $r) {
            $fk = isset($r->field_key) ? strtolower(trim((string) $r->field_key)) : '';
            if ($fk !== '' && strpos($fk, 'builder_config') !== false) {
                continue;
            }
            $o = $this->_webshop_header_footer_row_to_api_object($r);
            $sec = isset($o->section_type) ? $o->section_type : '';
            if ($sec === 'header') {
                $out['header'][] = $o;
            } elseif ($sec === 'footer') {
                $out['footer'][] = $o;
            }
        }
        if (!function_exists('cms_webshop_inject_header_builder_strip_rows')) {
            $this->load->helper('cms_layout');
        }
        if (function_exists('cms_webshop_inject_header_builder_strip_rows')) {
            $out['header'] = cms_webshop_inject_header_builder_strip_rows($out['header']);
        }
        return $out;
    }

    /**
     * Return storefront identity rows for API/webshop payloads.
     * Legacy sma_website_setting is intentionally ignored.
     *
     * @param array $legacy_objects Unused (kept for backwards compatibility).
     * @return array<int, stdClass>
     */
    public function merge_website_setting_for_api($legacy_objects) {
        $rows = $this->get_header_footer_contain_as_website_setting_objects();
        if (!function_exists('cms_webshop_inject_header_builder_strip_rows')) {
            $this->load->helper('cms_layout');
        }
        if (function_exists('cms_webshop_inject_header_builder_strip_rows')) {
            $rows = cms_webshop_inject_header_builder_strip_rows($rows);
        }
        return $rows;
    }

    /**
     * Header logo row from sma_cms_webshop_header_footer (active + inactive) for webshop show/hide.
     *
     * @return array{configured:bool,active:bool,field_key:string,value:string}
     */
    public function get_storefront_header_logo_status() {
        $empty = array(
            'configured' => false,
            'active'     => false,
            'field_key'  => '',
            'value'      => '',
        );
        if (!$this->header_footer_schema_ready()) {
            return $empty;
        }
        $keys = array('logo_image', 'site_logo', 'header_logo', 'store_logo');
        foreach ($keys as $field_key) {
            $q = $this->db
                ->where('section_type', 'header')
                ->where('field_key', $field_key)
                ->limit(1)
                ->get('sma_cms_webshop_header_footer');
            if ($q->num_rows() === 0) {
                continue;
            }
            $r = $q->row();
            return array(
                'configured' => true,
                'active'     => ((int) $r->is_active) === 1,
                'field_key'  => $field_key,
                'value'      => isset($r->value) ? trim((string) $r->value) : '',
            );
        }
        return $empty;
    }

}

//end class
