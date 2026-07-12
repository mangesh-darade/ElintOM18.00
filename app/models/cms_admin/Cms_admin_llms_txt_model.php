<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * Admin-editable llms.txt custom markdown (sma_webshop_settings).
 */
class Cms_admin_llms_txt_model extends Cms_admin_base_model
{
    /**
     * @return string
     */
    protected function settings_table()
    {
        return 'webshop_settings';
    }

    /**
     * Add llms_txt_* columns when missing (WAMP / shared hosting friendly).
     */
    public function ensureLlmsTxtColumns()
    {
        $table = $this->settings_table();
        if (!$this->db->table_exists($table)) {
            return false;
        }

        if (!$this->db->field_exists('llms_txt_include_custom', $table)) {
            $this->db->query(
                'ALTER TABLE `' . $table . '` ADD COLUMN `llms_txt_include_custom` TINYINT(1) NOT NULL DEFAULT 1'
            );
        }
        if (!$this->db->field_exists('llms_txt_custom', $table)) {
            $this->db->query(
                'ALTER TABLE `' . $table . '` ADD COLUMN `llms_txt_custom` MEDIUMTEXT NULL'
            );
        }
        if (!$this->db->field_exists('llms_txt_updated_at', $table)) {
            $this->db->query(
                'ALTER TABLE `' . $table . '` ADD COLUMN `llms_txt_updated_at` DATETIME NULL'
            );
        }

        return true;
    }

    /**
     * @return array{enabled:bool,content:string,updated_at:string,file_fallback:string,storefront_url:string}
     */
    public function get_for_admin()
    {
        $this->load->helper('cms_llms_txt');
        $this->ensureLlmsTxtColumns();

        $out = array(
            'enabled'         => true,
            'content'         => '',
            'updated_at'      => '',
            'file_fallback'   => '',
            'storefront_url'  => cms_llms_txt_resolve_storefront_base_url(),
        );

        $table = $this->settings_table();
        if (!$this->db->table_exists($table)) {
            return $out;
        }

        $select = array('id');
        if ($this->db->field_exists('llms_txt_include_custom', $table)) {
            $select[] = 'llms_txt_include_custom';
        }
        if ($this->db->field_exists('llms_txt_custom', $table)) {
            $select[] = 'llms_txt_custom';
        }
        if ($this->db->field_exists('llms_txt_updated_at', $table)) {
            $select[] = 'llms_txt_updated_at';
        }

        $row = $this->db->select(implode(', ', $select), false)
            ->from($table)
            ->where('id', 1)
            ->limit(1)
            ->get()
            ->row_array();

        if (is_array($row)) {
            if (array_key_exists('llms_txt_include_custom', $row)) {
                $out['enabled'] = (int) $row['llms_txt_include_custom'] === 1;
            }
            if (isset($row['llms_txt_custom'])) {
                $out['content'] = (string) $row['llms_txt_custom'];
            }
            if (!empty($row['llms_txt_updated_at'])) {
                $out['updated_at'] = (string) $row['llms_txt_updated_at'];
            }
        }

        $out['file_fallback'] = $this->read_legacy_file_markdown();

        if (trim($out['content']) === '' && trim($out['file_fallback']) !== '') {
            $out['content'] = $out['file_fallback'];
        }

        $site_name = 'Store';
        $ci = function_exists('get_instance') ? get_instance() : null;
        if ($ci && isset($ci->Settings) && is_object($ci->Settings) && !empty($ci->Settings->site_name)) {
            $site_name = trim((string) $ci->Settings->site_name);
        }

        $out['auto_generated_preview'] = $this->get_auto_generated_preview($site_name);

        return $out;
    }

    /**
     * Auto-generated llms.txt (storefront live file or CMS-built fallback).
     *
     * @param string $site_name
     * @return string
     */
    public function get_auto_generated_preview($site_name = '')
    {
        $this->load->helper('cms_llms_txt');
        $base = cms_llms_txt_resolve_storefront_base_url();
        $fetched = cms_llms_txt_fetch_storefront_preview($base);
        if ($fetched !== '') {
            return $fetched;
        }

        return cms_llms_txt_build_auto_generated_preview(
            $site_name,
            $base,
            $this->get_published_pages_for_llms_preview()
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    protected function get_published_pages_for_llms_preview()
    {
        $table = 'sma_cms_pages';
        if (!$this->db->table_exists($table)) {
            return array();
        }

        $q = $this->db
            ->select('page_name, url, status', false)
            ->from($table)
            ->where('status', 'published')
            ->order_by('id', 'ASC')
            ->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * @param string $content
     * @param bool   $enabled
     * @return bool
     */
    public function save($content, $enabled)
    {
        $this->load->helper('cms_llms_txt');
        $this->ensureLlmsTxtColumns();

        $table = $this->settings_table();
        if (!$this->db->table_exists($table)) {
            return false;
        }

        $storefront_url = cms_llms_txt_resolve_storefront_base_url();
        $site_name = 'Store';
        $ci = function_exists('get_instance') ? get_instance() : null;
        if ($ci && isset($ci->Settings) && is_object($ci->Settings) && !empty($ci->Settings->site_name)) {
            $site_name = trim((string) $ci->Settings->site_name);
        }
        $data = array(
            'llms_txt_include_custom' => $enabled ? 1 : 0,
            'llms_txt_custom'         => cms_llms_txt_prepare_stored_content($content, $site_name, $storefront_url),
            'llms_txt_updated_at'     => date('Y-m-d H:i:s'),
        );

        $exists = $this->db->where('id', 1)->count_all_results($table) > 0;
        $ok = false;
        if ($exists) {
            $this->db->where('id', 1);
            $ok = (bool) $this->db->update($table, $data);
        } else {
            $data['id'] = 1;
            $ok = (bool) $this->db->insert($table, $data);
        }

        if ($ok) {
            $this->clear_storefront_settings_cache();
        }

        return $ok;
    }

    /**
     * Drop cached getsettings + warm storefront after CMS llms.txt save.
     *
     * @return void
     */
    protected function clear_storefront_settings_cache()
    {
        if (!function_exists('cms_webshop_clear_getsettings_cache')) {
            $this->load->helper('cms_layout');
        }
        if (function_exists('cms_webshop_clear_getsettings_cache')) {
            cms_webshop_clear_getsettings_cache();
        }

        $this->load->helper('cms_site_settings');
        if (function_exists('cms_site_settings_refresh_storefront_settings_cache')) {
            cms_site_settings_refresh_storefront_settings_cache(cms_llms_txt_resolve_storefront_base_url());
        }
    }

    /**
     * Legacy file-based custom block (webshop application/config/llms_txt_custom.md).
     *
     * @return string
     */
    protected function read_legacy_file_markdown()
    {
        $paths = array(
            FCPATH . '../ElintOm_Webshop_PHP_8.4/application/config/llms_txt_custom.md',
            dirname(FCPATH) . DIRECTORY_SEPARATOR . 'ElintOm_Webshop_PHP_8.4/application/config/llms_txt_custom.md',
            FCPATH . 'application/config/llms_txt_custom.md',
        );
        foreach ($paths as $path) {
            if (is_string($path) && $path !== '' && is_file($path) && is_readable($path)) {
                return trim((string) file_get_contents($path));
            }
        }

        return '';
    }
}
