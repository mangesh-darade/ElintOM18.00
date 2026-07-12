<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * Storefront header/footer identity rows (sma_cms_webshop_header_footer).
 */
class Cms_admin_storefront_model extends Cms_admin_base_model
{
    /** @var bool|null */
    private $_layoutname_column = null;

    public function schema_ready()
    {
        return $this->db->table_exists('sma_cms_webshop_header_footer');
    }

    /**
     * @return bool
     */
    public function has_layoutname_column()
    {
        if (!$this->schema_ready()) {
            return false;
        }
        if ($this->_layoutname_column === null) {
            $this->load->helper('cms_layout');
            $this->_layoutname_column = cms_storefront_header_footer_has_layoutname_column(true);
        }
        return $this->_layoutname_column;
    }

    /**
     * Add layoutname column + index when missing (WAMP/local auto-migrate).
     *
     * @return bool
     */
    public function ensure_layoutname_column()
    {
        if (!$this->schema_ready() || $this->has_layoutname_column()) {
            return $this->has_layoutname_column();
        }
        $table = 'sma_cms_webshop_header_footer';
        $sql = 'ALTER TABLE `' . $table . '` '
            . 'ADD COLUMN `layoutname` VARCHAR(64) NULL DEFAULT NULL COMMENT \'Header/footer layout profile slug\' AFTER `field_key`, '
            . 'ADD KEY `idx_whhf_section_layoutname` (`section_type`, `layoutname`)';
        if (!$this->db->query($sql)) {
            return false;
        }
        $this->_layoutname_column = true;
        cms_storefront_header_footer_has_layoutname_column(true);
        return $this->backfill_layoutname_column();
    }

    /**
     * Populate layoutname for existing rows from field_key/value.
     *
     * @return bool
     */
    public function backfill_layoutname_column()
    {
        if (!$this->schema_ready() || !$this->has_layoutname_column()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $q = $this->db->get('sma_cms_webshop_header_footer');
        if (!$q || $q->num_rows() === 0) {
            return true;
        }
        foreach ($q->result_array() as $row) {
            $row = $this->normalize_row($row);
            if (empty($row['id'])) {
                continue;
            }
            $current = isset($row['layoutname']) ? cms_storefront_normalize_profile_slug($row['layoutname']) : '';
            $resolved = cms_storefront_resolve_layoutname(
                isset($row['field_key']) ? $row['field_key'] : '',
                isset($row['value']) ? $row['value'] : '',
                isset($row['section_type']) ? $row['section_type'] : 'header'
            );
            if ($resolved === $current) {
                continue;
            }
            $this->db->where('id', (int) $row['id'])->update('sma_cms_webshop_header_footer', array(
                'layoutname' => $resolved !== '' ? $resolved : null,
            ));
        }
        return true;
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function prepare_row_with_layoutname(array $data)
    {
        if (!$this->has_layoutname_column()) {
            return $data;
        }
        $this->load->helper('cms_layout');
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
     * Whether a layout profile slug exists for a section (layoutname, marker, or builder JSON).
     *
     * @param string $section header|footer
     * @param string $profile
     * @return bool
     */
    public function layout_profile_slug_registered($section, $profile)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        if ($profile === $this->layout_builder_bootstrap_profile()) {
            return true;
        }
        $marker_key = ($section === 'footer') ? 'footer_profile' : 'header_profile';
        if ($this->get_profile_marker_row($section, $profile, $marker_key)) {
            return true;
        }
        if ($this->has_layoutname_column()) {
            $this->db->where('section_type', $section);
            $this->db->where('layoutname', $profile);
            if ($this->db->count_all_results('sma_cms_webshop_header_footer') > 0) {
                return true;
            }
        }
        $field = ($section === 'footer') ? 'footer_builder_config' : 'header_builder_config';
        return (bool) $this->get_builder_config_row($section, $field, $profile);
    }

    /**
     * @param string $section header|footer
     * @param string $profile
     * @param string $marker_key header_profile|footer_profile
     * @return array|null
     */
    private function get_profile_marker_row($section, $profile, $marker_key)
    {
        if (!$this->schema_ready()) {
            return null;
        }
        $this->load->helper('cms_layout');
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return null;
        }
        $this->db->where('section_type', $section);
        $this->db->where('field_key', $marker_key);
        if ($this->has_layoutname_column()) {
            $this->db->group_start();
            $this->db->where('layoutname', $profile);
            $this->db->or_where('value', $profile);
            $this->db->group_end();
        } else {
            $this->db->where('value', $profile);
        }
        $q = $this->db->limit(1)->get('sma_cms_webshop_header_footer');
        return ($q && $q->num_rows() > 0) ? $this->normalize_row($q->row_array()) : null;
    }

    /**
     * @param string $section header|footer
     * @param string $profile
     * @return bool
     */
    private function delete_rows_for_layout_profile($section, $profile)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        if ($this->has_layoutname_column()) {
            $this->db->where('section_type', $section);
            $this->db->where('layoutname', $profile);
            return (bool) $this->db->delete('sma_cms_webshop_header_footer');
        }
        $marker_key = ($section === 'footer') ? 'footer_profile' : 'header_profile';
        $this->db->where('section_type', $section);
        $this->db->where('field_key', $marker_key);
        $this->db->where('value', $profile);
        $this->db->delete('sma_cms_webshop_header_footer');
        foreach ($this->fetch_design_items_for_profile($section, $profile) as $row) {
            if (!empty($row['id'])) {
                $this->db->where('id', (int) $row['id'])->delete('sma_cms_webshop_header_footer');
            }
        }
        return true;
    }

    public function get_all_rows()
    {
        if (!$this->schema_ready()) {
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
            $out[] = $this->normalize_row($row);
        }
        return $out;
    }

    public function get_by_id($id)
    {
        if (!$this->schema_ready()) {
            return null;
        }
        $q = $this->db->where('id', (int) $id)->limit(1)->get('sma_cms_webshop_header_footer');
        if ($q->num_rows() === 0) {
            return null;
        }
        return $this->normalize_row($q->row_array());
    }

    public function insert_row(array $data)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $data = $this->prepare_row_with_layoutname($data);
        return (bool) $this->db->insert('sma_cms_webshop_header_footer', $data);
    }

    public function update_row($id, array $data)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        if ($this->has_layoutname_column()) {
            $data = $this->prepare_row_with_layoutname($data);
        }
        $this->db->where('id', (int) $id);
        return (bool) $this->db->update('sma_cms_webshop_header_footer', $data);
    }

    public function delete_row($id)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->db->where('id', (int) $id);
        return (bool) $this->db->delete('sma_cms_webshop_header_footer');
    }

    public function set_active($id, $is_active)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->db->where('id', (int) $id);
        return (bool) $this->db->update('sma_cms_webshop_header_footer', array(
            'is_active' => (int) $is_active ? 1 : 0,
        ));
    }

    /**
     * Header design profiles for CMS Admin → Header designs screen.
     *
     * @return array<int,array{slug:string,label:string,item_count:int,marker_id:int}>
     */
    public function get_header_design_profiles()
    {
        if (!$this->schema_ready()) {
            return array();
        }

        $this->load->helper('cms_layout');
        $profiles = cms_storefront_get_header_profiles();
        $counts = array();

        $this->db->where('section_type', 'header');
        $this->db->where('field_key !=', 'header_profile');
        $q = $this->db->get('sma_cms_webshop_header_footer');
        if ($q && $q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row = $this->normalize_row($row);
                $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
                if (strpos($fk, 'builder_config') !== false) {
                    continue;
                }
                $slug = cms_storefront_row_layoutname($row);
                if ($slug === '') {
                    $slug = 'default';
                    $pos = strpos($fk, '__');
                    if ($pos !== false) {
                        $slug = cms_storefront_normalize_profile_slug(substr($fk, 0, $pos));
                    }
                    if ($slug === '') {
                        $slug = 'default';
                    }
                }
                if (!isset($counts[$slug])) {
                    $counts[$slug] = 0;
                }
                $counts[$slug]++;
            }
        }

        $marker_ids = array();
        $this->db->where('section_type', 'header');
        $this->db->where('field_key', 'header_profile');
        $mq = $this->db->get('sma_cms_webshop_header_footer');
        if ($mq && $mq->num_rows() > 0) {
            foreach ($mq->result_array() as $row) {
                $row = $this->normalize_row($row);
                $slug = cms_storefront_row_layoutname($row);
                if ($slug === '' && isset($row['value'])) {
                    $slug = cms_storefront_normalize_profile_slug($row['value']);
                }
                if ($slug !== '') {
                    $marker_ids[$slug] = (int) $row['id'];
                }
            }
        }

        $out = array();
        foreach ($profiles as $p) {
            $slug = isset($p['slug']) ? cms_storefront_normalize_profile_slug($p['slug']) : '';
            if ($slug === '') {
                continue;
            }
            $out[] = array(
                'slug'        => $slug,
                'label'       => isset($p['label']) ? (string) $p['label'] : $slug,
                'item_count'  => isset($counts[$slug]) ? (int) $counts[$slug] : 0,
                'marker_id'   => isset($marker_ids[$slug]) ? (int) $marker_ids[$slug] : 0,
            );
        }

        return $out;
    }

    /**
     * @param string $profile
     * @return array<int,array>
     */
    public function get_header_design_items($profile)
    {
        if (!$this->schema_ready()) {
            return array();
        }
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return array();
        }
        $out = array();
        foreach ($this->fetch_design_items_for_profile('header', $profile) as $row) {
            $out[] = $this->normalize_row($row);
        }
        return $out;
    }

    /**
     * @param string $profile
     * @return array|null marker row
     */
    public function get_header_profile_marker($profile)
    {
        return $this->get_profile_marker_row('header', $profile, 'header_profile');
    }

    /**
     * @param string $profile
     * @param string $label
     * @return bool
     */
    public function ensure_header_profile_marker($profile, $label = '')
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        if ($this->get_header_profile_marker($profile)) {
            return true;
        }
        if ($label === '') {
            $label = ucfirst(str_replace(array('-', '_'), ' ', $profile));
        }
        return $this->insert_row(array(
            'section_type' => 'header',
            'field_key'    => 'header_profile',
            'layoutname'   => $profile,
            'label'        => $label,
            'value'        => $profile,
            'icons'        => null,
            'sort_order'   => 0,
            'is_active'    => 1,
        ));
    }

    /**
     * @param string $profile
     * @param string $item_key short key (announcement, logo_image, …)
     * @return string full field_key stored in DB
     */
    public function build_header_profile_field_key($profile, $item_key)
    {
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        $item_key = strtolower(trim((string) $item_key));
        $item_key = preg_replace('/[^a-z0-9_]+/', '_', $item_key);
        $item_key = trim($item_key, '_');
        if ($item_key === '') {
            return '';
        }
        if ($profile === '' || $profile === 'default') {
            return $item_key;
        }
        return $profile . '__' . $item_key;
    }

    /**
     * @param string $profile
     * @return bool
     */
    public function profile_slug_in_use($profile)
    {
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        if ($this->get_header_profile_marker($profile)) {
            return true;
        }
        if (!$this->schema_ready()) {
            return false;
        }
        if ($this->has_layoutname_column()) {
            $this->db->where('section_type', 'header');
            $this->db->where('layoutname', $profile);
            $this->db->where('field_key !=', 'header_profile');
            if ($this->db->count_all_results('sma_cms_webshop_header_footer') > 0) {
                return true;
            }
        }
        $like = $this->db->escape_like_str($profile . '__');
        $this->db->where('section_type', 'header');
        $this->db->like('field_key', $like, 'after');
        return $this->db->count_all_results('sma_cms_webshop_header_footer') > 0;
    }

    /**
     * @param string $profile
     * @return bool
     */
    /**
     * Duplicate a header design (marker + all items) to a new slug.
     *
     * @param string $from_profile
     * @param string $to_slug
     * @param string $to_label
     * @return bool
     */
    public function duplicate_header_profile($from_profile, $to_slug, $to_label = '')
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $from_profile = cms_storefront_normalize_profile_slug($from_profile);
        $to_slug = cms_storefront_normalize_profile_slug($to_slug);
        if ($from_profile === '' || $to_slug === '' || $from_profile === $to_slug) {
            return false;
        }
        if ($this->profile_slug_in_use($to_slug)) {
            return false;
        }

        $this->db->trans_start();
        if (!$this->ensure_header_profile_marker($to_slug, $to_label)) {
            $this->db->trans_complete();
            return false;
        }

        foreach ($this->get_header_design_items($from_profile) as $row) {
            $item_key = isset($row['field_key']) ? (string) $row['field_key'] : '';
            if ($from_profile !== 'default' && strpos($item_key, $from_profile . '__') === 0) {
                $item_key = substr($item_key, strlen($from_profile) + 2);
            }
            $new_fk = $this->build_header_profile_field_key($to_slug, $item_key);
            if ($new_fk === '' || $this->field_key_exists('header', $new_fk)) {
                continue;
            }
            $this->insert_row(array(
                'section_type' => 'header',
                'field_key'    => $new_fk,
                'label'        => isset($row['label']) ? $row['label'] : '',
                'value'        => isset($row['value']) ? $row['value'] : '',
                'icons'        => isset($row['icons']) ? $row['icons'] : null,
                'sort_order'   => isset($row['sort_order']) ? (int) $row['sort_order'] : 100,
                'is_active'    => isset($row['is_active']) ? (int) $row['is_active'] : 1,
            ));
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete_header_profile($profile)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }

        $this->db->trans_start();
        $ok = $this->delete_rows_for_layout_profile('header', $profile);
        $this->db->trans_complete();
        return $ok && $this->db->trans_status();
    }

    /**
     * @return array<int,array{slug:string,label:string,item_count:int,marker_id:int}>
     */
    public function get_footer_design_profiles()
    {
        if (!$this->schema_ready()) {
            return array();
        }

        $this->load->helper('cms_layout');
        $profiles = cms_storefront_get_footer_profiles();
        $counts = array();

        $this->db->where('section_type', 'footer');
        $this->db->where('field_key !=', 'footer_profile');
        $q = $this->db->get('sma_cms_webshop_header_footer');
        if ($q && $q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $row = $this->normalize_row($row);
                $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
                if (strpos($fk, 'builder_config') !== false) {
                    continue;
                }
                $slug = cms_storefront_row_layoutname($row);
                if ($slug === '') {
                    $slug = 'default';
                    $pos = strpos($fk, '__');
                    if ($pos !== false) {
                        $slug = cms_storefront_normalize_profile_slug(substr($fk, 0, $pos));
                    }
                    if ($slug === '') {
                        $slug = 'default';
                    }
                }
                if (!isset($counts[$slug])) {
                    $counts[$slug] = 0;
                }
                $counts[$slug]++;
            }
        }

        $marker_ids = array();
        $this->db->where('section_type', 'footer');
        $this->db->where('field_key', 'footer_profile');
        $mq = $this->db->get('sma_cms_webshop_header_footer');
        if ($mq && $mq->num_rows() > 0) {
            foreach ($mq->result_array() as $row) {
                $row = $this->normalize_row($row);
                $slug = cms_storefront_row_layoutname($row);
                if ($slug === '' && isset($row['value'])) {
                    $slug = cms_storefront_normalize_profile_slug($row['value']);
                }
                if ($slug !== '') {
                    $marker_ids[$slug] = (int) $row['id'];
                }
            }
        }

        $out = array();
        foreach ($profiles as $p) {
            $slug = isset($p['slug']) ? cms_storefront_normalize_profile_slug($p['slug']) : '';
            if ($slug === '') {
                continue;
            }
            $out[] = array(
                'slug'       => $slug,
                'label'      => isset($p['label']) ? (string) $p['label'] : $slug,
                'item_count' => isset($counts[$slug]) ? (int) $counts[$slug] : 0,
                'marker_id'  => isset($marker_ids[$slug]) ? (int) $marker_ids[$slug] : 0,
            );
        }

        return $out;
    }

    /**
     * @param string $profile
     * @return array<int,array>
     */
    public function get_footer_design_items($profile)
    {
        if (!$this->schema_ready()) {
            return array();
        }
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return array();
        }
        $out = array();
        foreach ($this->fetch_design_items_for_profile('footer', $profile) as $row) {
            $out[] = $this->normalize_row($row);
        }
        return $out;
    }

    public function get_footer_profile_marker($profile)
    {
        return $this->get_profile_marker_row('footer', $profile, 'footer_profile');
    }

    public function ensure_footer_profile_marker($profile, $label = '')
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        if ($this->get_footer_profile_marker($profile)) {
            return true;
        }
        if ($label === '') {
            $label = ucfirst(str_replace(array('-', '_'), ' ', $profile));
        }
        return $this->insert_row(array(
            'section_type' => 'footer',
            'field_key'    => 'footer_profile',
            'layoutname'   => $profile,
            'label'        => $label,
            'value'        => $profile,
            'icons'        => null,
            'sort_order'   => 0,
            'is_active'    => 1,
        ));
    }

    public function build_footer_profile_field_key($profile, $item_key)
    {
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        $item_key = strtolower(trim((string) $item_key));
        $item_key = preg_replace('/[^a-z0-9_]+/', '_', $item_key);
        $item_key = trim($item_key, '_');
        if ($item_key === '') {
            return '';
        }
        if ($profile === '' || $profile === 'default') {
            return $item_key;
        }
        return $profile . '__' . $item_key;
    }

    public function footer_profile_slug_in_use($profile)
    {
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        if ($this->get_footer_profile_marker($profile)) {
            return true;
        }
        if (!$this->schema_ready()) {
            return false;
        }
        if ($this->has_layoutname_column()) {
            $this->db->where('section_type', 'footer');
            $this->db->where('layoutname', $profile);
            $this->db->where('field_key !=', 'footer_profile');
            if ($this->db->count_all_results('sma_cms_webshop_header_footer') > 0) {
                return true;
            }
        }
        $like = $this->db->escape_like_str($profile . '__');
        $this->db->where('section_type', 'footer');
        $this->db->like('field_key', $like, 'after');
        return $this->db->count_all_results('sma_cms_webshop_header_footer') > 0;
    }

    public function duplicate_footer_profile($from_profile, $to_slug, $to_label = '')
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $from_profile = cms_storefront_normalize_profile_slug($from_profile);
        $to_slug = cms_storefront_normalize_profile_slug($to_slug);
        if ($from_profile === '' || $to_slug === '' || $from_profile === $to_slug) {
            return false;
        }
        if ($this->footer_profile_slug_in_use($to_slug)) {
            return false;
        }

        $this->db->trans_start();
        if (!$this->ensure_footer_profile_marker($to_slug, $to_label)) {
            $this->db->trans_complete();
            return false;
        }

        foreach ($this->get_footer_design_items($from_profile) as $row) {
            $item_key = isset($row['field_key']) ? (string) $row['field_key'] : '';
            if ($from_profile !== 'default' && strpos($item_key, $from_profile . '__') === 0) {
                $item_key = substr($item_key, strlen($from_profile) + 2);
            }
            $new_fk = $this->build_footer_profile_field_key($to_slug, $item_key);
            if ($new_fk === '' || $this->field_key_exists('footer', $new_fk)) {
                continue;
            }
            $this->insert_row(array(
                'section_type' => 'footer',
                'field_key'    => $new_fk,
                'label'        => isset($row['label']) ? $row['label'] : '',
                'value'        => isset($row['value']) ? $row['value'] : '',
                'icons'        => isset($row['icons']) ? $row['icons'] : null,
                'sort_order'   => isset($row['sort_order']) ? (int) $row['sort_order'] : 100,
                'is_active'    => isset($row['is_active']) ? (int) $row['is_active'] : 1,
            ));
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete_footer_profile($profile)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }

        $this->db->trans_start();
        $ok = $this->delete_rows_for_layout_profile('footer', $profile);
        $this->db->trans_complete();
        return $ok && $this->db->trans_status();
    }

    /**
     * All rows for a design profile (including inactive) for CMS admin editors.
     *
     * @param string $section header|footer
     * @param string $profile
     * @return array<int,array>
     */
    private function fetch_design_items_for_profile($section, $profile)
    {
        if (!$this->schema_ready()) {
            return array();
        }
        $this->load->helper('cms_layout');
        $section = strtolower(trim((string) $section));
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return array();
        }
        $marker_key = ($section === 'footer') ? 'footer_profile' : 'header_profile';

        if ($this->has_layoutname_column()) {
            $this->db->where('section_type', $section);
            $this->db->where('layoutname', $profile);
            $this->db->where('field_key !=', $marker_key);
            $this->db->order_by('sort_order', 'ASC');
            $this->db->order_by('field_key', 'ASC');
            $q = $this->db->get('sma_cms_webshop_header_footer');
            if (!$q || $q->num_rows() === 0) {
                return array();
            }
            $out = array();
            $builder_suffix = ($section === 'footer') ? 'footer_builder_config' : 'header_builder_config';
            foreach ($q->result_array() as $row) {
                $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
                if (substr($fk, -strlen($builder_suffix)) === $builder_suffix) {
                    continue;
                }
                $out[] = $row;
            }
            return $out;
        }

        $this->db->where('section_type', $section);
        $this->db->where('field_key !=', $marker_key);
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('field_key', 'ASC');
        $q = $this->db->get('sma_cms_webshop_header_footer');
        if (!$q || $q->num_rows() === 0) {
            return array();
        }

        $out = array();
        foreach ($q->result_array() as $row) {
            $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
            if (!cms_storefront_row_matches_header_profile($fk, $profile)) {
                continue;
            }
            $builder_suffix = ($section === 'footer') ? 'footer_builder_config' : 'header_builder_config';
            if (substr($fk, -strlen($builder_suffix)) === $builder_suffix) {
                continue;
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * @param string $section header|footer
     * @param string $profile
     * @param string $field_key
     * @return bool
     */
    /**
     * Built-in profile slug (always exists; cannot delete).
     *
     * @return string
     */
    public function layout_builder_bootstrap_profile()
    {
        $this->load->helper('cms_layout');
        return cms_layout_builder_bootstrap_profile_slug();
    }

    /**
     * Assigned default layout for header or footer (CMS "Default Store Profile").
     *
     * @param string|null $section header|footer
     * @return string
     */
    public function layout_builder_default_profile($section = 'header')
    {
        $this->load->helper('cms_layout');
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        return cms_layout_builder_default_profile_slug($section);
    }

    /**
     * @param string $section header|footer
     * @return array|null
     */
    public function get_layout_default_profile_row($section)
    {
        if (!$this->schema_ready()) {
            return null;
        }
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $q = $this->db
            ->where('section_type', $section)
            ->where('field_key', 'layout_default_profile')
            ->limit(1)
            ->get('sma_cms_webshop_header_footer');
        return ($q && $q->num_rows() > 0) ? $this->normalize_row($q->row_array()) : null;
    }

    public function ensure_layout_default_profile_markers()
    {
        if (!$this->schema_ready()) {
            return;
        }
        $bootstrap = $this->layout_builder_bootstrap_profile();
        foreach (array('header', 'footer') as $section) {
            if (!$this->get_layout_default_profile_row($section)) {
                $this->set_layout_default_profile($section, $bootstrap);
            }
        }
    }

    /**
     * @param string $section header|footer
     * @param string $slug
     * @return bool
     */
    public function set_layout_default_profile($section, $slug)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $slug = cms_storefront_normalize_profile_slug($slug);
        if ($slug === '' || !$this->layout_builder_section_has_profile($section, $slug)) {
            return false;
        }

        $label = ($section === 'footer') ? 'Default footer layout' : 'Default header layout';
        $existing = $this->get_layout_default_profile_row($section);
        $payload = array(
            'section_type' => $section,
            'field_key'    => 'layout_default_profile',
            'label'        => $label,
            'value'        => $slug,
            'icons'        => null,
            'sort_order'   => 0,
            'is_active'    => 1,
        );
        if ($existing && !empty($existing['id'])) {
            return $this->update_row((int) $existing['id'], array(
                'value'     => $slug,
                'is_active' => 1,
            ));
        }
        return $this->insert_row($payload);
    }

    /**
     * @param string|null $profile
     * @return string
     */
    public function normalize_layout_builder_profile($profile = null)
    {
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            $profile = $this->layout_builder_default_profile('header');
        }
        return $profile;
    }

    public function ensure_layout_builder_site_profile()
    {
        if (!$this->schema_ready()) {
            return;
        }
        $this->ensure_layoutname_column();
        $site = $this->layout_builder_bootstrap_profile();
        if (!$this->layout_builder_profile_exists($site)) {
            $this->create_layout_builder_profile($site, 'Site header', false);
            return;
        }
        if (!$this->get_builder_config_row('header', 'header_builder_config', $site)) {
            $this->save_header_builder_config(cms_header_builder_default_config(), $site);
        }
        if (!$this->get_builder_config_row('footer', 'footer_builder_config', $site)) {
            $this->save_footer_builder_config(cms_footer_builder_default_config(), $site);
        }
        $this->ensure_header_profile_marker($site, 'Site header');
        $this->ensure_footer_profile_marker($site, 'Site footer');
        $this->ensure_layout_default_profile_markers();
    }

    /**
     * Create header/footer builder JSON for a profile that only has legacy header_profile markers.
     *
     * @param string $slug
     * @param string $label
     * @return bool
     */
    public function ensure_layout_builder_profile_ready($slug, $label = '', $copy_from_site = true)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $slug = cms_storefront_normalize_profile_slug($slug);
        if ($slug === '' || $slug === 'default') {
            return false;
        }

        $this->ensure_layout_builder_site_profile();
        $label = trim((string) $label);
        if ($label === '') {
            $label = ucfirst(str_replace(array('-', '_'), ' ', $slug));
        }
        $this->ensure_header_profile_marker($slug, $label);
        $this->ensure_footer_profile_marker($slug, $label);

        $site = $this->layout_builder_bootstrap_profile();
        if (!$this->get_builder_config_row('header', 'header_builder_config', $slug)) {
            if ($slug === $site) {
                $header_cfg = cms_header_builder_default_config();
            } elseif ($copy_from_site) {
                $header_cfg = $this->get_header_builder_config($site);
            } else {
                $header_cfg = cms_header_builder_blank_config();
            }
            if (!$this->save_header_builder_config($header_cfg, $slug)) {
                return false;
            }
        }
        if (!$this->get_builder_config_row('footer', 'footer_builder_config', $slug)) {
            if ($slug === $site) {
                $footer_cfg = cms_footer_builder_default_config();
            } elseif ($copy_from_site) {
                $footer_cfg = $this->get_footer_builder_config($site);
            } else {
                $footer_cfg = cms_footer_builder_blank_config();
            }
            if (!$this->save_footer_builder_config($footer_cfg, $slug)) {
                return false;
            }
        }

        return true;
    }

    /**
     * All profile marker rows for a section (admin: includes inactive markers).
     *
     * @param string $section header|footer
     * @return array<int,array{slug:string,label:string}>
     */
    public function get_layout_profile_markers($section)
    {
        if (!$this->schema_ready()) {
            return array();
        }
        $this->load->helper('cms_layout');
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $marker_key = $section === 'footer' ? 'footer_profile' : 'header_profile';
        $q = $this->db
            ->where('section_type', $section)
            ->where('field_key', $marker_key)
            ->order_by('sort_order', 'ASC')
            ->order_by('label', 'ASC')
            ->get('sma_cms_webshop_header_footer');
        if (!$q || $q->num_rows() === 0) {
            return array();
        }
        $out = array();
        $seen = array();
        foreach ($q->result_array() as $row) {
            $row = $this->normalize_row($row);
            $slug = cms_storefront_row_layoutname($row);
            if ($slug === '' && isset($row['value'])) {
                $slug = cms_storefront_normalize_profile_slug($row['value']);
            }
            if ($slug === '' || $slug === 'default' || isset($seen[$slug])) {
                continue;
            }
            $seen[$slug] = true;
            $out[] = array(
                'slug'  => $slug,
                'label' => trim((string) (isset($row['label']) && $row['label'] !== '' ? $row['label'] : $slug)),
            );
        }
        return $out;
    }

    /**
     * @param string $section header|footer
     * @param string $profile
     * @return bool
     */
    public function layout_profile_marker_exists($section, $profile)
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        foreach ($this->get_layout_profile_markers($section) as $marker) {
            if (isset($marker['slug']) && $marker['slug'] === $profile) {
                return true;
            }
        }
        return false;
    }

    /**
     * Legacy header/footer design markers without layout-builder JSON (orphans).
     */
    public function sync_layout_builder_profiles_from_markers()
    {
        if (!$this->schema_ready()) {
            return;
        }
        $this->load->helper('cms_layout');
        $this->ensure_layout_builder_site_profile();

        $seen = array();
        foreach (array('header', 'footer') as $section) {
            foreach ($this->get_layout_profile_markers($section) as $p) {
                $slug = isset($p['slug']) ? cms_storefront_normalize_profile_slug($p['slug']) : '';
                if ($slug === '' || $slug === 'default' || isset($seen[$slug])) {
                    continue;
                }
                $seen[$slug] = true;
                $field = $section === 'footer' ? 'footer_builder_config' : 'header_builder_config';
                if (!$this->get_builder_config_row($section, $field, $slug)) {
                    $label = isset($p['label']) ? (string) $p['label'] : '';
                    $this->ensure_layout_builder_profile_ready($slug, $label);
                }
            }
        }
    }

    /**
     * @param string $profile
     * @return bool
     */
    public function layout_builder_profile_exists($profile)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        if ($this->get_builder_config_row('header', 'header_builder_config', $profile)) {
            return true;
        }
        if ($this->get_builder_config_row('footer', 'footer_builder_config', $profile)) {
            return true;
        }
        return (bool) $this->get_header_profile_marker($profile) || (bool) $this->get_footer_profile_marker($profile);
    }

    /**
     * @return array<int,array{slug:string,label:string,is_default:bool}>
     */
    /**
     * Layouts assignable on CMS pages (same list as Storefront layout builder).
     *
     * @return array<int,array{slug:string,label:string,is_default?:bool}>
     */
    public function get_assignable_layout_profiles()
    {
        return $this->get_layout_builder_profiles();
    }

    /**
     * Layout profiles for one section (header or footer) with is_default flag.
     *
     * @param string $section header|footer
     * @return array<int,array{slug:string,label:string,is_default:bool}>
     */
    public function get_layout_builder_profiles_for_section($section)
    {
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $default = $this->layout_builder_default_profile($section);
        $out = array();
        foreach ($this->get_layout_builder_profiles() as $p) {
            $slug = isset($p['slug']) ? cms_storefront_normalize_profile_slug($p['slug']) : '';
            if ($slug === '') {
                continue;
            }
            if ($slug === $default || $this->layout_builder_section_has_profile($section, $slug)) {
                $out[] = array(
                    'slug'       => $slug,
                    'label'      => isset($p['label']) ? (string) $p['label'] : $slug,
                    'is_default' => ($slug === $default),
                );
            }
        }
        usort($out, function ($a, $b) {
            if (!empty($a['is_default'])) {
                return -1;
            }
            if (!empty($b['is_default'])) {
                return 1;
            }
            return strcmp($a['slug'], $b['slug']);
        });
        return $out;
    }

    /**
     * @param string $section header|footer
     * @param string $profile
     * @return bool
     */
    public function layout_builder_section_has_profile($section, $profile)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        if ($profile === $this->layout_builder_bootstrap_profile()) {
            return true;
        }
        $field = $section === 'footer' ? 'footer_builder_config' : 'header_builder_config';
        if ($this->get_builder_config_row($section, $field, $profile)) {
            return true;
        }
        if ($this->layout_profile_marker_exists($section, $profile)) {
            return true;
        }
        if ($section === 'header' && function_exists('cms_storefront_get_header_rows_for_profile')) {
            return !empty(cms_storefront_get_header_rows_for_profile($profile));
        }
        if ($section === 'footer' && function_exists('cms_storefront_get_footer_rows_for_profile')) {
            return !empty(cms_storefront_get_footer_rows_for_profile($profile));
        }
        return false;
    }

    public function get_layout_builder_profiles()
    {
        if (!$this->schema_ready()) {
            return array();
        }
        $this->load->helper('cms_layout');

        $by_slug = array();
        foreach (array('header' => 'header_builder_config', 'footer' => 'footer_builder_config') as $section => $suffix) {
            $this->db->select('field_key');
            $this->db->where('section_type', $section);
            $this->db->like('field_key', '__' . $suffix, 'before');
            $q = $this->db->get('sma_cms_webshop_header_footer');
            if (!$q || $q->num_rows() === 0) {
                continue;
            }
            foreach ($q->result_array() as $row) {
                $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
                $needle = '__' . $suffix;
                $pos = strrpos($fk, $needle);
                if ($pos === false) {
                    continue;
                }
                $slug = cms_storefront_normalize_profile_slug(substr($fk, 0, $pos));
                if ($slug !== '') {
                    $by_slug[$slug] = true;
                }
            }
        }

        $default = $this->layout_builder_default_profile('header');
        foreach (array(
            array('header', 'header_builder_config'),
            array('footer', 'footer_builder_config'),
        ) as $legacy) {
            $this->db->where('section_type', $legacy[0]);
            $this->db->where('field_key', $legacy[1]);
            $this->db->limit(1);
            $lq = $this->db->get('sma_cms_webshop_header_footer');
            if ($lq && $lq->num_rows() > 0) {
                $by_slug[$default] = true;
                break;
            }
        }
        if (empty($by_slug)) {
            return array();
        }

        $labels = array();
        foreach (array('header' => 'header_profile', 'footer' => 'footer_profile') as $section => $marker_key) {
            $this->db->where('section_type', $section);
            $this->db->where('field_key', $marker_key);
            $mq = $this->db->get('sma_cms_webshop_header_footer');
            if ($mq && $mq->num_rows() > 0) {
                foreach ($mq->result_array() as $row) {
                    $row = $this->normalize_row($row);
                    $slug = cms_storefront_normalize_profile_slug(isset($row['value']) ? $row['value'] : '');
                    if ($slug === '' || $slug === 'default') {
                        continue;
                    }
                    $by_slug[$slug] = true;
                    if (!isset($labels[$slug]) || $labels[$slug] === '') {
                        $labels[$slug] = trim((string) (isset($row['label']) ? $row['label'] : $slug));
                    }
                }
            }
        }

        foreach (cms_storefront_get_header_profiles() as $p) {
            $slug = isset($p['slug']) ? cms_storefront_normalize_profile_slug($p['slug']) : '';
            if ($slug === '' || $slug === 'default') {
                continue;
            }
            $by_slug[$slug] = true;
            if (!isset($labels[$slug]) && !empty($p['label'])) {
                $labels[$slug] = trim((string) $p['label']);
            }
        }

        foreach (cms_storefront_get_footer_profiles() as $p) {
            $slug = isset($p['slug']) ? cms_storefront_normalize_profile_slug($p['slug']) : '';
            if ($slug === '' || $slug === 'default') {
                continue;
            }
            $by_slug[$slug] = true;
            if (!isset($labels[$slug]) && !empty($p['label'])) {
                $labels[$slug] = trim((string) $p['label']);
            }
        }

        $out = array();
        foreach (array_keys($by_slug) as $slug) {
            $out[] = array(
                'slug'       => $slug,
                'label'      => isset($labels[$slug]) && $labels[$slug] !== '' ? $labels[$slug] : ucfirst(str_replace(array('-', '_'), ' ', $slug)),
                'is_default' => ($slug === $default),
            );
        }
        usort($out, function ($a, $b) {
            if (!empty($a['is_default'])) {
                return -1;
            }
            if (!empty($b['is_default'])) {
                return 1;
            }
            return strcmp($a['slug'], $b['slug']);
        });
        return $out;
    }

    /**
     * Ensure the active profile appears in admin dropdown options.
     *
     * @param array  $profiles from get_layout_builder_profiles_for_section
     * @param string $slug     active profile slug
     * @param string $section  header|footer
     * @return array<int,array{slug:string,label:string,is_default:bool}>
     */
    public function merge_active_layout_profile_option(array $profiles, $slug, $section = 'header')
    {
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $slug = cms_storefront_normalize_profile_slug($slug);
        if ($slug === '') {
            return $profiles;
        }
        foreach ($profiles as $p) {
            $ps = isset($p['slug']) ? cms_storefront_normalize_profile_slug($p['slug']) : '';
            if ($ps === $slug) {
                return $profiles;
            }
        }
        $label = ucfirst(str_replace(array('-', '_'), ' ', $slug));
        foreach ($this->get_layout_profile_markers($section) as $marker) {
            if (isset($marker['slug']) && $marker['slug'] === $slug) {
                $label = $marker['label'];
                break;
            }
        }
        $default = $this->layout_builder_default_profile($section);
        $profiles[] = array(
            'slug'       => $slug,
            'label'      => $label,
            'is_default' => ($slug === $default),
        );
        usort($profiles, function ($a, $b) {
            if (!empty($a['is_default'])) {
                return -1;
            }
            if (!empty($b['is_default'])) {
                return 1;
            }
            return strcmp($a['slug'], $b['slug']);
        });
        return $profiles;
    }

    /**
     * @param string $slug
     * @param string $label
     * @param bool   $copy_from_site
     * @return bool
     */
    public function create_layout_builder_profile($slug, $label = '', $copy_from_site = true)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $slug = cms_storefront_normalize_profile_slug($slug);
        $label = trim((string) $label);
        if ($slug === '') {
            return false;
        }
        if ($this->get_builder_config_row('header', 'header_builder_config', $slug)
            && $this->get_builder_config_row('footer', 'footer_builder_config', $slug)) {
            return false;
        }
        if ($label === '') {
            $label = ucfirst(str_replace(array('-', '_'), ' ', $slug));
        }

        $site = $this->layout_builder_bootstrap_profile();
        if ($copy_from_site && $slug !== $site) {
            $header_cfg = $this->get_header_builder_config($site);
            $footer_cfg = $this->get_footer_builder_config($site);
        } elseif ($slug === $site) {
            $header_cfg = cms_header_builder_default_config();
            $footer_cfg = cms_footer_builder_default_config();
        } else {
            $header_cfg = cms_header_builder_blank_config();
            $footer_cfg = cms_footer_builder_blank_config();
        }

        $this->ensure_header_profile_marker($slug, $label);
        $this->ensure_footer_profile_marker($slug, $label);
        $ok = true;
        if (!$this->get_builder_config_row('header', 'header_builder_config', $slug)) {
            $ok = $this->save_header_builder_config($header_cfg, $slug) && $ok;
        }
        if (!$this->get_builder_config_row('footer', 'footer_builder_config', $slug)) {
            $ok = $this->save_footer_builder_config($footer_cfg, $slug) && $ok;
        }
        return $ok;
    }

    /**
     * Create a layout profile; optionally copy only the active tab (header or footer) from a source profile.
     *
     * @param string $slug
     * @param string $label
     * @param string $tab           header|footer
     * @param string $from_profile
     * @param bool   $copy_section
     * @return bool
     */
    public function create_layout_builder_profile_for_tab($slug, $label, $tab, $from_profile, $copy_section = false)
    {
        $tab = strtolower(trim((string) $tab)) === 'footer' ? 'footer' : 'header';
        $from_profile = cms_storefront_normalize_profile_slug($from_profile);
        if (!$this->create_layout_builder_profile($slug, $label, false)) {
            return false;
        }
        if (!$copy_section || $from_profile === '') {
            return true;
        }
        if ($tab === 'footer') {
            return $this->save_footer_builder_config($this->get_footer_builder_config($from_profile), $slug);
        }
        return $this->save_header_builder_config($this->get_header_builder_config($from_profile), $slug);
    }

    /**
     * @param string $profile
     * @param string $label
     * @return bool
     */
    public function update_layout_builder_profile_label($profile, $label)
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        $label = trim((string) $label);
        if ($profile === '' || $label === '') {
            return false;
        }
        $ok = true;
        $marker = $this->get_header_profile_marker($profile);
        if ($marker && !empty($marker['id'])) {
            $ok = $this->update_row((int) $marker['id'], array('label' => $label)) && $ok;
        } else {
            $ok = $this->ensure_header_profile_marker($profile, $label) && $ok;
        }
        $fmarker = $this->get_footer_profile_marker($profile);
        if ($fmarker && !empty($fmarker['id'])) {
            $ok = $this->update_row((int) $fmarker['id'], array('label' => $label)) && $ok;
        } else {
            $ok = $this->ensure_footer_profile_marker($profile, $label) && $ok;
        }
        return $ok;
    }

    /**
     * @param string $from
     * @param string $to_slug
     * @param string $to_label
     * @return bool
     */
    public function duplicate_layout_builder_profile($from, $to_slug, $to_label = '')
    {
        $from = cms_storefront_normalize_profile_slug($from);
        $to_slug = cms_storefront_normalize_profile_slug($to_slug);
        if ($from === '' || $to_slug === '' || $from === $to_slug) {
            return false;
        }
        if ($this->layout_builder_profile_exists($to_slug) || !$this->layout_builder_profile_exists($from)) {
            return false;
        }
        if ($to_label === '') {
            $to_label = ucfirst(str_replace(array('-', '_'), ' ', $to_slug));
        }
        if (!$this->create_layout_builder_profile($to_slug, $to_label, false)) {
            return false;
        }
        return $this->save_header_builder_config($this->get_header_builder_config($from), $to_slug)
            && $this->save_footer_builder_config($this->get_footer_builder_config($from), $to_slug);
    }

    /**
     * @param string $profile
     * @return bool
     */
    public function delete_layout_builder_profile($profile)
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '' || $profile === $this->layout_builder_bootstrap_profile()) {
            return false;
        }
        foreach (array(
            array('header', 'header_builder_config'),
            array('footer', 'footer_builder_config'),
        ) as $pair) {
            $row = $this->get_builder_config_row($pair[0], $pair[1], $profile);
            if ($row && !empty($row['id'])) {
                $this->db->where('id', (int) $row['id'])->delete('sma_cms_webshop_header_footer');
            }
        }
        $this->delete_header_profile($profile);
        $this->delete_footer_profile($profile);
        return true;
    }

    /**
     * Slugs managed by the visual layout builder (for save validation).
     *
     * @return array<int,string>
     */
    public function get_layout_builder_profile_slugs()
    {
        $slugs = array();
        foreach ($this->get_layout_builder_profiles() as $p) {
            if (!empty($p['slug'])) {
                $slugs[] = (string) $p['slug'];
            }
        }
        return $slugs;
    }

    /**
     * @param string $profile
     * @return bool
     */
    public function is_layout_builder_profile($profile)
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        return in_array($profile, $this->get_layout_builder_profile_slugs(), true);
    }

    /**
     * Visual header builder JSON for a profile.
     *
     * @param string|null $profile
     * @return array<string,mixed>
     */
    public function get_header_builder_config($profile = null)
    {
        $this->load->helper('cms_layout');
        $profile = $this->normalize_layout_builder_profile($profile);
        $row = $this->get_builder_config_row('header', 'header_builder_config', $profile);
        if (!$row || empty($row['value'])) {
            return cms_header_builder_blank_config();
        }
        $decoded = json_decode((string) $row['value'], true);
        return cms_sanitize_header_builder_config(is_array($decoded) ? $decoded : array());
    }

    /**
     * @param array<string,mixed> $config
     * @param string|null         $profile
     * @return bool
     */
    public function save_header_builder_config(array $config, $profile = null)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $profile = $this->normalize_layout_builder_profile($profile);
        $config = cms_sanitize_header_builder_config($config);
        $label = ($profile === $this->layout_builder_bootstrap_profile()) ? 'Site (main)' : ucfirst(str_replace(array('-', '_'), ' ', $profile));
        $marker = $this->get_header_profile_marker($profile);
        if ($marker && !empty($marker['label'])) {
            $label = (string) $marker['label'];
        }
        $this->ensure_header_profile_marker($profile, $label);
        return $this->upsert_builder_config_row('header', 'header_builder_config', json_encode($config), $profile);
    }

    /**
     * @param string|null $profile
     * @return array<string,mixed>
     */
    public function get_footer_builder_config($profile = null)
    {
        $this->load->helper('cms_layout');
        $profile = $this->normalize_layout_builder_profile($profile);
        $row = $this->get_builder_config_row('footer', 'footer_builder_config', $profile);
        if (!$row || empty($row['value'])) {
            return cms_footer_builder_blank_config();
        }
        $decoded = json_decode((string) $row['value'], true);
        return cms_sanitize_footer_builder_config(is_array($decoded) ? $decoded : array());
    }

    /**
     * @param array<string,mixed> $config
     * @param string|null         $profile
     * @return bool
     */
    public function save_footer_builder_config(array $config, $profile = null)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $profile = $this->normalize_layout_builder_profile($profile);
        $config = cms_sanitize_footer_builder_config($config);
        $label = ($profile === $this->layout_builder_bootstrap_profile()) ? 'Site (main)' : ucfirst(str_replace(array('-', '_'), ' ', $profile));
        $marker = $this->get_footer_profile_marker($profile);
        if ($marker && !empty($marker['label'])) {
            $label = (string) $marker['label'];
        }
        $this->ensure_footer_profile_marker($profile, $label);
        return $this->upsert_builder_config_row('footer', 'footer_builder_config', json_encode($config), $profile);
    }

    /**
     * @param string      $section header|footer
     * @param string      $field_key
     * @param string|null $profile
     * @return array|null
     */
    public function get_builder_config_row($section, $field_key, $profile = null)
    {
        if (!$this->schema_ready()) {
            return null;
        }
        $this->load->helper('cms_layout');
        $profile = $this->normalize_layout_builder_profile($profile);
        $fk = $profile === 'default' ? $field_key : ($profile . '__' . $field_key);
        $q = $this->db
            ->where('section_type', $section)
            ->where('field_key', $fk)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('sma_cms_webshop_header_footer');
        if ($q && $q->num_rows() > 0) {
            return $this->normalize_row($q->row_array());
        }
        if ($profile === $this->layout_builder_bootstrap_profile()) {
            $q2 = $this->db
                ->where('section_type', $section)
                ->where('field_key', $field_key)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get('sma_cms_webshop_header_footer');
            return ($q2 && $q2->num_rows() > 0) ? $this->normalize_row($q2->row_array()) : null;
        }
        return null;
    }

    /**
     * @param string      $section
     * @param string      $field_key short key
     * @param string      $json_value
     * @param string|null $profile
     * @return bool
     */
    public function upsert_builder_config_row($section, $field_key, $json_value, $profile = null)
    {
        $this->load->helper('cms_layout');
        $profile = $this->normalize_layout_builder_profile($profile);
        $full_key = $this->build_header_profile_field_key($profile, $field_key);
        if ($section === 'footer') {
            $full_key = $this->build_footer_profile_field_key($profile, $field_key);
        }

        $existing = $this->get_builder_config_row($section, $field_key, $profile);
        $payload = array(
            'section_type' => $section,
            'field_key'    => $full_key,
            'layoutname'   => $profile,
            'label'        => ucfirst(str_replace('_', ' ', $field_key)),
            'value'        => $json_value,
            'icons'        => null,
            'sort_order'   => 1,
            'is_active'    => 1,
        );

        if ($existing && !empty($existing['id'])) {
            return $this->update_row((int) $existing['id'], array(
                'value'     => $json_value,
                'is_active' => 1,
            ));
        }
        return $this->insert_row($payload);
    }

    /**
     * @param string $section header|footer
     * @param string $profile
     * @return bool
     */
    public function row_belongs_to_profile($section, $profile, $field_key)
    {
        $this->load->helper('cms_layout');
        $section = strtolower(trim((string) $section));
        $profile = cms_storefront_normalize_profile_slug($profile);
        $field_key = strtolower(trim((string) $field_key));
        if ($field_key === '' || $profile === '') {
            return false;
        }
        return cms_storefront_row_matches_header_profile($field_key, $profile);
    }

    /**
     * @param string $profile
     * @return bool
     */
    public function seed_header_starter_items($profile)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        $this->ensure_header_profile_marker($profile);

        $starters = array(
            array('announcement', 'Promo', 'Welcome — add your message here', 5, null),
            array('phone', 'Phone', '+1 (000) 000-0000', 20, 'phone'),
            array('home_url', 'Home', '/', 30, null),
        );

        foreach ($starters as $s) {
            $fk = $this->build_header_profile_field_key($profile, $s[0]);
            if ($fk === '' || $this->field_key_exists('header', $fk)) {
                continue;
            }
            $this->insert_row(array(
                'section_type' => 'header',
                'field_key'    => $fk,
                'label'        => $s[1],
                'value'        => $s[2],
                'icons'        => $s[4],
                'sort_order'   => (int) $s[3],
                'is_active'    => 1,
            ));
        }
        return true;
    }

    /**
     * @param string $profile
     * @return bool
     */
    public function seed_footer_starter_items($profile)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $this->load->helper('cms_layout');
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        $this->ensure_footer_profile_marker($profile);

        $year = date('Y');
        $starters = array(
            array('footer_tagline', 'About', 'Your company tagline goes here.', 10, null),
            array('footer_heading_shop', 'Shop', 'Shop', 20, null),
            array('footer_link_1', 'Contact', '/contact', 30, null),
            array('footer_copyright', 'Copyright', '© ' . $year . ' Your Company. All rights reserved.', 200, null),
        );

        foreach ($starters as $s) {
            $fk = $this->build_footer_profile_field_key($profile, $s[0]);
            if ($fk === '' || $this->field_key_exists('footer', $fk)) {
                continue;
            }
            $this->insert_row(array(
                'section_type' => 'footer',
                'field_key'    => $fk,
                'label'        => $s[1],
                'value'        => $s[2],
                'icons'        => $s[4],
                'sort_order'   => (int) $s[3],
                'is_active'    => 1,
            ));
        }
        return true;
    }

    public function field_key_exists($section, $field_key, $exclude_id = 0)
    {
        if (!$this->schema_ready()) {
            return false;
        }
        $section = strtolower(trim((string) $section));
        $field_key = trim((string) $field_key);
        $this->db->where('section_type', $section);
        $this->db->where(
            'LOWER(`field_key`) = ' . $this->db->escape(strtolower($field_key)),
            null,
            false
        );
        if ((int) $exclude_id > 0) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('sma_cms_webshop_header_footer') > 0;
    }

    private function normalize_row(array $row)
    {
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
}
