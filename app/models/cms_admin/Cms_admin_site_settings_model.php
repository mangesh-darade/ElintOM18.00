<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * Storefront robots.txt, sitemap.xml, and llms.txt previews for CMS Site Settings.
 */
class Cms_admin_site_settings_model extends Cms_admin_base_model
{
    /**
     * Add robots_txt columns when missing.
     *
     * @return bool
     */
    public function ensureRobotsTxtColumns()
    {
        $table = 'webshop_settings';
        if (!$this->db->table_exists($table)) {
            return false;
        }

        if (!$this->db->field_exists('robots_txt', $table)) {
            $this->db->query(
                'ALTER TABLE `' . $table . '` ADD COLUMN `robots_txt` MEDIUMTEXT NULL'
            );
        }
        if (!$this->db->field_exists('robots_txt_updated_at', $table)) {
            $this->db->query(
                'ALTER TABLE `' . $table . '` ADD COLUMN `robots_txt_updated_at` DATETIME NULL'
            );
        }

        return true;
    }

    /**
     * @return array{content:string,updated_at:string,storefront_url:string,live_url:string,live_preview:string}
     */
    public function get_robots_for_admin()
    {
        $this->load->helper('cms_site_settings');
        $this->ensureRobotsTxtColumns();

        $base = cms_site_settings_storefront_base_url();
        $out = array(
            'content'       => cms_site_settings_robots_default_template(),
            'updated_at'    => '',
            'storefront_url'=> $base,
            'live_url'      => rtrim($base, '/') . '/robots.txt',
            'live_preview'  => '',
        );

        $table = 'webshop_settings';
        if ($this->db->table_exists($table) && $this->db->field_exists('robots_txt', $table)) {
            $select = array('robots_txt');
            if ($this->db->field_exists('robots_txt_updated_at', $table)) {
                $select[] = 'robots_txt_updated_at';
            }

            $row = $this->db->select(implode(', ', $select), false)
                ->from($table)
                ->where('id', 1)
                ->limit(1)
                ->get()
                ->row_array();

            if (is_array($row)) {
                if (isset($row['robots_txt']) && trim((string) $row['robots_txt']) !== '') {
                    $out['content'] = (string) $row['robots_txt'];
                }
                if (!empty($row['robots_txt_updated_at'])) {
                    $out['updated_at'] = (string) $row['robots_txt_updated_at'];
                }
            }
        }

        $computed = cms_site_settings_apply_robots_placeholders($out['content'], $base);
        $fetched = cms_site_settings_fetch_robots_txt($base);
        $out['live_preview'] = $fetched !== '' ? $fetched : $computed;
        $out['preview_from_live'] = $fetched !== '';

        return $out;
    }

    /**
     * @param string $content
     * @return bool
     */
    public function save_robots($content)
    {
        $this->load->helper('cms_site_settings');
        $this->ensureRobotsTxtColumns();

        $table = 'webshop_settings';
        if (!$this->db->table_exists($table)) {
            return false;
        }

        $data = array(
            'robots_txt'            => cms_site_settings_sanitize_robots_content($content),
            'robots_txt_updated_at' => date('Y-m-d H:i:s'),
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
     * Drop cached getsettings + warm storefront after CMS site-settings save.
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
            cms_site_settings_refresh_storefront_settings_cache(cms_site_settings_storefront_base_url());
        }
    }

    /**
     * @deprecated Use clear_storefront_settings_cache()
     * @return void
     */
    protected function clear_storefront_robots_cache()
    {
        $this->clear_storefront_settings_cache();
    }

    /**
     * @return array{content:string,live_url:string,storefront_url:string,fetched:bool}
     */
    public function get_robots_preview()
    {
        $this->load->helper('cms_site_settings');
        $base = cms_site_settings_storefront_base_url();
        $liveUrl = rtrim($base, '/') . '/robots.txt';
        $content = cms_site_settings_fetch_robots_txt($base);
        $fetched = $content !== '';
        if (!$fetched) {
            $content = cms_site_settings_build_robots_fallback($base);
        }

        return array(
            'content'        => $content,
            'live_url'       => $liveUrl,
            'storefront_url' => $base,
            'fetched'        => $fetched,
        );
    }

    /**
     * @param string $variant index|main|pages|categories|products
     * @return array{variant:string,label:string,path:string,content:string,live_url:string,storefront_url:string,fetched:bool,url_count:int}
     */
    public function get_sitemap_preview($variant = 'index')
    {
        $this->load->helper('cms_site_settings');
        $variants = cms_site_settings_sitemap_variants();
        $variant = isset($variants[$variant]) ? $variant : 'index';
        $meta = $variants[$variant];
        $base = cms_site_settings_storefront_base_url();
        $path = $meta['path'];
        $liveUrl = rtrim($base, '/') . $path;
        $fetched = cms_site_settings_fetch_sitemap_xml($base, $path);
        $computed = cms_site_settings_build_sitemap_fallback($variant, $base);
        $content = $fetched !== '' ? $fetched : $computed;
        $content = cms_site_settings_format_xml($content);
        $table = cms_site_settings_parse_sitemap_table_rows($content);

        return array(
            'variant'          => $variant,
            'label'            => $meta['label'],
            'path'             => $path,
            'description'      => $meta['description'],
            'content'          => $content,
            'live_url'         => $liveUrl,
            'storefront_url'   => $base,
            'fetched'          => $fetched !== '',
            'preview_from_live'=> $fetched !== '',
            'url_count'        => $this->count_sitemap_urls($content),
            'variants'         => $variants,
            'table_type'       => isset($table['type']) ? (string) $table['type'] : '',
            'table_rows'       => isset($table['rows']) && is_array($table['rows']) ? $table['rows'] : array(),
        );
    }

    /**
     * @param string $xml
     * @return int
     */
    protected function count_sitemap_urls($xml)
    {
        $xml = trim((string) $xml);
        if ($xml === '') {
            return 0;
        }
        if (preg_match_all('/<loc>/i', $xml, $m)) {
            return count($m[0]);
        }
        return 0;
    }
}
