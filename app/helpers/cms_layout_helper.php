<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CMS page layout: split sections into header/footer/banner/logo/body buckets.
 */

if (!function_exists('cms_layout_normalize_flag')) {
    function cms_layout_normalize_flag($value)
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, array('1', 'true', 'yes', 'on'), true);
    }
}

if (!function_exists('cms_layout_section_config')) {
    /**
     * @param array $section
     * @return array
     */
    function cms_layout_section_config(array $section)
    {
        $raw = '';
        if (!empty($section['section_contain'])) {
            $raw = (string) $section['section_contain'];
        } elseif (!empty($section['config_json'])) {
            $raw = (string) $section['config_json'];
        }
        if ($raw === '') {
            return array();
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('cms_layout_section_type')) {
    function cms_layout_section_type(array $section)
    {
        if (!empty($section['section_type'])) {
            return strtolower(trim((string) $section['section_type']));
        }
        return '';
    }
}

if (!function_exists('cms_layout_section_in_placement')) {
    /**
     * Whether a section belongs in a layout bucket (header, footer, banner, logo).
     *
     * @param array  $section
     * @param array  $config
     * @param string $placement header|footer|banner|logo
     */
    function cms_layout_section_in_placement(array $section, array $config, $placement)
    {
        $placement = strtolower(trim((string) $placement));
        $type = cms_layout_section_type($section);

        if ($placement === 'header' && $type === 'header') {
            return true;
        }
        if ($placement === 'footer' && $type === 'footer') {
            return true;
        }
        if ($placement === 'banner' && in_array($type, array('banner', 'hero_banner'), true)) {
            return true;
        }
        if ($placement === 'logo' && $type === 'logo') {
            return true;
        }

        $flag = $placement;
        if (isset($section[$flag]) && cms_layout_normalize_flag($section[$flag])) {
            return true;
        }
        if (isset($config[$flag]) && cms_layout_normalize_flag($config[$flag])) {
            return true;
        }
        $show_flag = 'show_' . $placement;
        if (isset($section[$show_flag]) && cms_layout_normalize_flag($section[$show_flag])) {
            return true;
        }
        if (isset($config[$show_flag]) && cms_layout_normalize_flag($config[$show_flag])) {
            return true;
        }

        return false;
    }
}

if (!function_exists('cms_layout_resolve_page_chrome_profile_slug')) {
    /**
     * Storefront profile slug assigned on a page (highest sort_order header/footer section wins).
     *
     * Stored in page_section_mapping.section_contain JSON:
     *   header_mode|footer_mode = storefront_profile
     *   storefront_profile = layout slug (site, test2, …)
     *
     * @param array  $sections
     * @param string $chrome header|footer
     * @return string
     */
    function cms_layout_resolve_page_chrome_profile_slug(array $sections, $chrome)
    {
        $chrome = strtolower(trim((string) $chrome));
        if (!in_array($chrome, array('header', 'footer'), true)) {
            return '';
        }
        $mode_key = ($chrome === 'footer') ? 'footer_mode' : 'header_mode';
        $best_slug = '';
        $best_order = -1;

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            if (cms_layout_section_type($section) !== $chrome) {
                continue;
            }
            $config = cms_layout_section_config($section);
            $mode = isset($config[$mode_key]) ? strtolower(trim((string) $config[$mode_key])) : '';
            if ($mode !== 'storefront_profile') {
                continue;
            }
            $prof = isset($config['storefront_profile'])
                ? cms_storefront_normalize_profile_slug($config['storefront_profile'])
                : '';
            if ($prof === '') {
                continue;
            }
            $order = isset($section['sort_order']) ? (int) $section['sort_order'] : 0;
            if ($order >= $best_order) {
                $best_order = $order;
                $best_slug = $prof;
            }
        }

        return $best_slug;
    }
}

if (!function_exists('cms_layout_pick_canonical_chrome_section')) {
    /**
     * When a page has multiple header/footer sections, use the highest sort_order row only.
     *
     * @param array<int,array{section:array,html:string,sort_order:int}> $candidates
     * @return array{section:array,html:string,sort_order:int}|null
     */
    function cms_layout_pick_canonical_chrome_section(array $candidates)
    {
        if (empty($candidates)) {
            return null;
        }
        $best = null;
        foreach ($candidates as $row) {
            if (!is_array($row) || empty($row['section'])) {
                continue;
            }
            $order = isset($row['sort_order']) ? (int) $row['sort_order'] : 0;
            if ($best === null || $order >= (int) $best['sort_order']) {
                $best = $row;
            }
        }
        return $best;
    }
}

if (!function_exists('cms_build_page_layout_sections')) {
    /**
     * @param array         $sections  Page section rows from DB
     * @param Cms_renderer|null $renderer
     * @param array         $page_context Optional runtime context (product_id, etc.)
     * @return array{header_html:string,footer_html:string,banner_html:string,logo_html:string,body_html:string,show_header:bool,show_footer:bool}
     */
    function cms_build_page_layout_sections(array $sections, $renderer = null, array $page_context = array())
    {
        $CI =& get_instance();
        if ($renderer === null) {
            $CI->load->library('cms_renderer');
            $renderer = $CI->cms_renderer;
        }
        if (!empty($page_context) && method_exists($renderer, 'set_page_context')) {
            $renderer->set_page_context($page_context);
        }

        $header_html = '';
        $footer_html = '';
        $banner_html = '';
        $logo_html = '';
        $body_html = '';
        $show_header = false;
        $show_footer = false;
        $header_candidates = array();
        $footer_candidates = array();

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            $config = cms_layout_section_config($section);
            $html = $renderer->render_section($section);
            $sort_order = isset($section['sort_order']) ? (int) $section['sort_order'] : 0;

            if (cms_layout_section_in_placement($section, $config, 'header')) {
                $show_header = true;
                $header_candidates[] = array(
                    'section'     => $section,
                    'html'        => $html,
                    'sort_order'  => $sort_order,
                );
            } elseif (cms_layout_section_in_placement($section, $config, 'footer')) {
                $show_footer = true;
                $footer_candidates[] = array(
                    'section'     => $section,
                    'html'        => $html,
                    'sort_order'  => $sort_order,
                );
            } elseif (cms_layout_section_in_placement($section, $config, 'banner')) {
                $banner_html .= $html;
            } elseif (cms_layout_section_in_placement($section, $config, 'logo')) {
                $logo_html .= $html;
            } else {
                $body_html .= $html;
            }
        }

        $canonical_header = cms_layout_pick_canonical_chrome_section($header_candidates);
        if ($canonical_header !== null) {
            $header_html = (string) $canonical_header['html'];
        }
        $canonical_footer = cms_layout_pick_canonical_chrome_section($footer_candidates);
        if ($canonical_footer !== null) {
            $footer_html = (string) $canonical_footer['html'];
        }

        if (!$show_header && $header_html === '') {
            $show_header = true;
        }
        if (!$show_footer && $footer_html === '') {
            $show_footer = true;
        }

        return array(
            'header_html' => $header_html,
            'footer_html' => $footer_html,
            'banner_html' => $banner_html,
            'logo_html'   => $logo_html,
            'body_html'   => $body_html,
            'show_header' => $show_header,
            'show_footer' => $show_footer,
        );
    }
}

if (!function_exists('cms_storefront_normalize_profile_slug')) {
    function cms_storefront_normalize_profile_slug($slug)
    {
        $slug = strtolower(trim((string) $slug));
        $slug = preg_replace('/[^a-z0-9_-]+/', '-', $slug);
        return trim($slug, '-');
    }
}

if (!function_exists('cms_storefront_header_footer_has_layoutname_column')) {
    /**
     * Whether sma_cms_webshop_header_footer has layoutname column.
     */
    function cms_storefront_header_footer_has_layoutname_column($force_refresh = false)
    {
        static $cache = null;
        if (!$force_refresh && $cache !== null) {
            return $cache;
        }
        $CI =& get_instance();
        if (!isset($CI->webshop_settings_model)) {
            $CI->load->model('webshop_settings_model');
        }
        if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
            $cache = false;
            return false;
        }
        $cache = $CI->db->field_exists('layoutname', 'sma_cms_webshop_header_footer');
        return $cache;
    }
}

if (!function_exists('cms_storefront_resolve_layoutname')) {
    /**
     * Derive layout profile slug for a header/footer row.
     *
     * @param string $field_key
     * @param string $value
     * @param string $section header|footer
     * @return string normalized slug or empty for system rows
     */
    function cms_storefront_resolve_layoutname($field_key, $value = '', $section = 'header')
    {
        $field_key = strtolower(trim((string) $field_key));
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $value = cms_storefront_normalize_profile_slug($value);

        if ($field_key === 'header_profile' || $field_key === 'footer_profile') {
            return $value;
        }
        if ($field_key === 'layout_default_profile') {
            return '';
        }

        $pos = strpos($field_key, '__');
        if ($pos !== false) {
            $slug = cms_storefront_normalize_profile_slug(substr($field_key, 0, $pos));
            if ($slug !== '') {
                return $slug;
            }
        }

        if ($field_key !== '' && ($section === 'header' || $section === 'footer')) {
            if (function_exists('cms_storefront_row_matches_header_profile')
                && cms_storefront_row_matches_header_profile($field_key, cms_layout_builder_bootstrap_profile_slug())) {
                return cms_layout_builder_bootstrap_profile_slug();
            }
        }

        return '';
    }
}

if (!function_exists('cms_storefront_row_layoutname')) {
    /**
     * Read layoutname from a normalized row (column, else infer from field_key/value).
     *
     * @param array $row
     * @return string
     */
    function cms_storefront_row_layoutname(array $row)
    {
        if (!empty($row['layoutname'])) {
            return cms_storefront_normalize_profile_slug($row['layoutname']);
        }
        return cms_storefront_resolve_layoutname(
            isset($row['field_key']) ? $row['field_key'] : '',
            isset($row['value']) ? $row['value'] : '',
            isset($row['section_type']) ? $row['section_type'] : 'header'
        );
    }
}

if (!function_exists('cms_storefront_validate_profile_slug')) {
    /**
     * Validate a new/edited design profile slug (header or footer).
     *
     * @param string $slug normalized slug
     * @param string $section_type header|footer
     * @param bool   $allow_default allow slug "default" (edit only)
     * @return string empty if valid, else error message
     */
    function cms_storefront_validate_profile_slug($slug, $section_type = 'header', $allow_default = false)
    {
        $slug = cms_storefront_normalize_profile_slug($slug);
        $section_type = strtolower(trim((string) $section_type));

        if ($slug === '') {
            return 'Design ID is required. Use letters, numbers, hyphen, or underscore (e.g. home, landing).';
        }
        if (strlen($slug) < 2) {
            return 'Design ID must be at least 2 characters.';
        }
        if (strlen($slug) > 32) {
            return 'Design ID must be 32 characters or fewer.';
        }
        if (!preg_match('/^[a-z0-9][a-z0-9_-]*$/', $slug)) {
            return 'Design ID must start with a letter or number and contain only a-z, 0-9, hyphen, underscore.';
        }
        if (strpos($slug, '__') !== false) {
            return 'Design ID cannot contain "__" — double underscore is reserved for content items (e.g. home__logo_image), not design names.';
        }
        if (!$allow_default && $slug === 'default') {
            return 'The ID "default" is reserved for legacy rows. Choose another name (e.g. main, home).';
        }
        $reserved = array('header_profile', 'footer_profile');
        if (in_array($slug, $reserved, true)) {
            return 'That ID is reserved by the system.';
        }
        return '';
    }
}

if (!function_exists('cms_storefront_field_key_suffix')) {
    /**
     * Item key without profile prefix (landing__footer_tagline → footer_tagline).
     *
     * @param string $field_key
     * @return string
     */
    function cms_storefront_field_key_suffix($field_key)
    {
        $field_key = strtolower(trim((string) $field_key));
        $pos = strpos($field_key, '__');
        if ($pos !== false) {
            return substr($field_key, $pos + 2);
        }
        return $field_key;
    }
}

if (!function_exists('cms_storefront_profile_exists')) {
    /**
     * @param string $section_type header|footer
     * @param string $profile_slug
     * @return bool
     */
    function cms_storefront_profile_exists($section_type, $profile_slug)
    {
        $section_type = strtolower(trim((string) $section_type));
        $profile_slug = cms_storefront_normalize_profile_slug($profile_slug);
        if ($profile_slug === '') {
            return false;
        }
        if ($profile_slug === 'default') {
            return true;
        }
        if (cms_layout_builder_profile_has_config($profile_slug, $section_type)) {
            return true;
        }
        $profiles = $section_type === 'footer'
            ? cms_storefront_get_footer_profiles()
            : cms_storefront_get_header_profiles();
        foreach ($profiles as $p) {
            if (isset($p['slug']) && cms_storefront_normalize_profile_slug($p['slug']) === $profile_slug) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('cms_storefront_row_matches_header_profile')) {
    /**
     * Header row belongs to profile when field_key is {profile}__{item} or unprefixed for default.
     *
     * @param string $field_key
     * @param string $profile
     */
    function cms_storefront_row_matches_header_profile($field_key, $profile)
    {
        $field_key = strtolower(trim((string) $field_key));
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($field_key === '' || $field_key === 'header_profile') {
            return false;
        }

        $pos = strpos($field_key, '__');
        if ($pos === false) {
            return ($profile === '' || $profile === 'default');
        }

        $prefix = substr($field_key, 0, $pos);
        return $prefix === $profile;
    }
}

if (!function_exists('cms_storefront_get_header_profiles')) {
    /**
     * @return array<int,array{slug:string,label:string}>
     */
    function cms_storefront_get_header_profiles()
    {
        $CI =& get_instance();
        if (!isset($CI->webshop_settings_model)) {
            $CI->load->model('webshop_settings_model');
        }
        if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
            return array(array('slug' => 'default', 'label' => 'Default'));
        }

        $profiles = array();
        $CI->db->where('section_type', 'header');
        $CI->db->where('field_key', 'header_profile');
        $CI->db->where('is_active', 1);
        $CI->db->order_by('sort_order', 'ASC');
        $CI->db->order_by('label', 'ASC');
        $q = $CI->db->get('sma_cms_webshop_header_footer');
        if ($q && $q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $slug = cms_storefront_normalize_profile_slug(isset($row['value']) ? $row['value'] : '');
                if ($slug === '') {
                    continue;
                }
                $profiles[] = array(
                    'slug'  => $slug,
                    'label' => trim((string) (isset($row['label']) && $row['label'] !== '' ? $row['label'] : $slug)),
                );
            }
        }

        if (empty($profiles)) {
            $CI->db->select('field_key');
            $CI->db->where('section_type', 'header');
            $CI->db->where('is_active', 1);
            $CI->db->like('field_key', '__', 'both');
            $q2 = $CI->db->get('sma_cms_webshop_header_footer');
            $seen = array();
            if ($q2 && $q2->num_rows() > 0) {
                foreach ($q2->result_array() as $row) {
                    $fk = strtolower(trim((string) $row['field_key']));
                    $pos = strpos($fk, '__');
                    if ($pos === false) {
                        continue;
                    }
                    $slug = cms_storefront_normalize_profile_slug(substr($fk, 0, $pos));
                    if ($slug === '' || isset($seen[$slug])) {
                        continue;
                    }
                    $seen[$slug] = true;
                    $profiles[] = array('slug' => $slug, 'label' => ucfirst(str_replace(array('-', '_'), ' ', $slug)));
                }
            }
        }

        if (empty($profiles)) {
            $profiles[] = array('slug' => 'default', 'label' => 'Default (all header rows)');
        }

        $site = cms_layout_builder_bootstrap_profile_slug();
        $has_site = false;
        foreach ($profiles as $p) {
            if (isset($p['slug']) && cms_storefront_normalize_profile_slug($p['slug']) === $site) {
                $has_site = true;
                break;
            }
        }
        if (!$has_site) {
            array_unshift($profiles, array('slug' => $site, 'label' => 'Site (Storefront layout)'));
        }

        $seen = array();
        foreach ($profiles as $p) {
            if (isset($p['slug'])) {
                $seen[cms_storefront_normalize_profile_slug($p['slug'])] = true;
            }
        }
        $CI->db->select('field_key');
        $CI->db->where('section_type', 'header');
        $CI->db->where('is_active', 1);
        $CI->db->like('field_key', '__header_builder_config', 'before');
        $bq = $CI->db->get('sma_cms_webshop_header_footer');
        if ($bq && $bq->num_rows() > 0) {
            foreach ($bq->result_array() as $row) {
                $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
                $pos = strrpos($fk, '__header_builder_config');
                if ($pos === false) {
                    continue;
                }
                $slug = cms_storefront_normalize_profile_slug(substr($fk, 0, $pos));
                if ($slug === '' || isset($seen[$slug])) {
                    continue;
                }
                $seen[$slug] = true;
                $profiles[] = array(
                    'slug'  => $slug,
                    'label' => ucfirst(str_replace(array('-', '_'), ' ', $slug)),
                );
            }
        }

        return $profiles;
    }
}

if (!function_exists('cms_layout_builder_load_all_json_configs')) {
    /**
     * Load every active layout-builder JSON config for a section in one query.
     *
     * @param string $section header|footer
     * @return array<string,array<string,mixed>>
     */
    function cms_layout_builder_load_all_json_configs($section)
    {
        $section = strtolower(trim((string) $section));
        $field = $section === 'footer' ? 'footer_builder_config' : 'header_builder_config';
        $suffix = '__' . $field;
        $site = cms_layout_builder_bootstrap_profile_slug();
        $out = array();

        $CI =& get_instance();
        if (!isset($CI->webshop_settings_model)) {
            $CI->load->model('webshop_settings_model');
        }
        if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
            return array();
        }

        $CI->db->select('field_key, value');
        $CI->db->where('section_type', $section);
        $CI->db->where('is_active', 1);
        $CI->db->group_start();
        $CI->db->like('field_key', $suffix, 'before');
        $CI->db->or_where('field_key', $field);
        $CI->db->group_end();
        $CI->db->order_by('id', 'DESC');
        $q = $CI->db->get('sma_cms_webshop_header_footer');
        if (!$q || $q->num_rows() === 0) {
            return array();
        }

        $seen = array();
        foreach ($q->result_array() as $row) {
            $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
            if ($fk === '') {
                continue;
            }
            if ($fk === $field) {
                $slug = $site;
            } else {
                $pos = strrpos($fk, $suffix);
                if ($pos === false) {
                    continue;
                }
                $slug = cms_storefront_normalize_profile_slug(substr($fk, 0, $pos));
            }
            if ($slug === '' || isset($seen[$slug])) {
                continue;
            }
            $raw = isset($row['value']) ? (string) $row['value'] : '';
            if ($raw === '') {
                continue;
            }
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                continue;
            }
            $seen[$slug] = true;
            $out[$slug] = $decoded;
        }

        return $out;
    }
}

if (!function_exists('cms_get_header_builder_configs_by_profile_for_api')) {
    /**
     * All layout-builder header JSON configs keyed by profile slug (for webshop API).
     *
     * @return array<string,array<string,mixed>>
     */
    function cms_get_header_builder_configs_by_profile_for_api()
    {
        $CI =& get_instance();
        if (!isset($CI->webshop_settings_model)) {
            $CI->load->model('webshop_settings_model');
        }
        if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
            return array();
        }

        $out = array();
        foreach (cms_layout_builder_load_all_json_configs('header') as $slug => $cfg) {
            $out[$slug] = cms_sanitize_header_builder_config($cfg);
        }

        return $out;
    }
}

if (!function_exists('cms_get_footer_builder_configs_by_profile_for_api')) {
    /**
     * All layout-builder footer JSON configs keyed by profile slug (for webshop API).
     *
     * @return array<string,array<string,mixed>>
     */
    function cms_get_footer_builder_configs_by_profile_for_api()
    {
        $CI =& get_instance();
        if (!isset($CI->webshop_settings_model)) {
            $CI->load->model('webshop_settings_model');
        }
        if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
            return array();
        }

        $out = array();
        foreach (cms_layout_builder_load_all_json_configs('footer') as $slug => $cfg) {
            $out[$slug] = cms_sanitize_footer_builder_config($cfg);
        }

        return $out;
    }
}

if (!function_exists('cms_storefront_get_header_rows_for_profile')) {
    /**
     * @param string $profile
     * @return array<int,array>
     */
    function cms_storefront_get_header_rows_for_profile($profile)
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            $profile = 'default';
        }

        $CI =& get_instance();
        if (!isset($CI->webshop_settings_model)) {
            $CI->load->model('webshop_settings_model');
        }
        if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
            return array();
        }

        $CI->db->where('section_type', 'header');
        $CI->db->where('is_active', 1);
        $CI->db->where('field_key !=', 'header_profile');
        $CI->db->order_by('sort_order', 'ASC');
        $CI->db->order_by('field_key', 'ASC');
        $q = $CI->db->get('sma_cms_webshop_header_footer');
        if (!$q || $q->num_rows() === 0) {
            return array();
        }

        $out = array();
        foreach ($q->result_array() as $row) {
            $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
            if (!cms_storefront_row_matches_header_profile($fk, $profile)) {
                continue;
            }
            $out[] = $row;
        }
        return $out;
    }
}

if (!function_exists('cms_render_storefront_header_profile_html')) {
    /**
     * @param string $profile
     * @param string $uploads_base Full URL to customer uploads (trailing slash optional)
     * @return string
     */
    function cms_render_storefront_header_profile_html($profile, $uploads_base = '')
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === 'default' || $profile === '') {
            $profile = cms_layout_builder_resolve_profile_slug('default', 'header');
        }
        $decoded = cms_layout_builder_load_json_config('header', $profile);
        if (is_array($decoded)) {
            return cms_render_header_builder_html($decoded, $uploads_base, $profile);
        }

        $rows = cms_storefront_get_header_rows_for_profile($profile);
        if (empty($rows)) {
            return '';
        }

        $uploads_base = rtrim((string) $uploads_base, '/') . '/';
        $inner_parts = array();

        foreach ($rows as $row) {
            $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
            $val = isset($row['value']) ? trim((string) $row['value']) : '';
            $label = isset($row['label']) ? trim((string) $row['label']) : $fk;
            if ($val === '') {
                continue;
            }

            if (preg_match('/\.(jpe?g|png|gif|webp)$/i', $val) && strpos($val, 'webshop/') === 0 && $uploads_base !== '/') {
                $src = $uploads_base . $val;
                $inner_parts[] = '<img class="cms-hdr-preview-logo" src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">';
                continue;
            }

            if (preg_match('/<script|googletagmanager/i', $val)) {
                continue;
            }

            if (preg_match('#^https?://#i', $val) || (strpos($val, '/') === 0 && strpos($val, '<') === false)) {
                $inner_parts[] = '<a class="cms-hdr-preview-link" href="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
                continue;
            }

            if (strpos($val, '<') !== false) {
                $inner_parts[] = '<div class="cms-hdr-preview-html">' . $val . '</div>';
                continue;
            }

            $inner_parts[] = '<span class="cms-hdr-preview-text">' . nl2br(htmlspecialchars($val, ENT_QUOTES, 'UTF-8')) . '</span>';
        }

        if (empty($inner_parts)) {
            return '';
        }

        return '<div class="gp-cms-header-strip cms-storefront-header-wrap" data-header-profile="'
            . htmlspecialchars($profile, ENT_QUOTES, 'UTF-8') . '"><div class="container gp-cms-header-inner gp-cms-hstrip-body">'
            . implode('', $inner_parts) . '</div></div>';
    }
}

if (!function_exists('cms_footer_strip_html_from_rows')) {
    /**
     * Shared footer markup for admin preview and live CMS pages.
     *
     * @param array<int,array> $rows
     * @param string $uploads_base
     * @param string $wrapper_class
     * @return string
     */
    function cms_footer_strip_html_from_rows(array $rows, $uploads_base = '', $wrapper_class = 'gp-cms-footer-strip')
    {
        if (empty($rows)) {
            return '';
        }

        $uploads_base = rtrim((string) $uploads_base, '/') . '/';
        $columns = array('_misc' => array('title' => '', 'links' => array()));
        $top_bits = array();
        $copyright = '';

        foreach ($rows as $row) {
            $fk = cms_storefront_field_key_suffix(isset($row['field_key']) ? $row['field_key'] : '');
            $val = isset($row['value']) ? trim((string) $row['value']) : '';
            $lab = isset($row['label']) ? trim((string) $row['label']) : '';
            if ($val === '' || preg_match('/<script|googletagmanager/i', $val)) {
                continue;
            }

            if ($fk === 'footer_copyright') {
                $copyright = $val;
                continue;
            }
            if ($fk === 'footer_tagline') {
                $top_bits[] = '<p class="cms-ftr-preview-tagline">' . nl2br(htmlspecialchars($val, ENT_QUOTES, 'UTF-8')) . '</p>';
                continue;
            }
            if (strpos($fk, 'footer_heading_') === 0) {
                $col = substr($fk, strlen('footer_heading_'));
                if (!isset($columns[$col])) {
                    $columns[$col] = array('title' => $val !== '' ? $val : $lab, 'links' => array());
                } else {
                    $columns[$col]['title'] = $val !== '' ? $val : $lab;
                }
                continue;
            }
            if (strpos($fk, 'footer_link_') === 0) {
                $columns['_misc']['links'][] = array(
                    'label' => $lab !== '' ? $lab : $val,
                    'href'  => $val,
                );
                continue;
            }
            if (strpos($fk, 'footer_social_') === 0) {
                $top_bits[] = '<a class="cms-ftr-preview-social" href="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($lab !== '' ? $lab : $fk, ENT_QUOTES, 'UTF-8') . '</a>';
                continue;
            }

            if (preg_match('/\.(jpe?g|png|gif|webp)$/i', $val) && strpos($val, 'webshop/') === 0 && $uploads_base !== '/') {
                $top_bits[] = '<img class="cms-ftr-preview-logo" src="' . htmlspecialchars($uploads_base . $val, ENT_QUOTES, 'UTF-8') . '" alt="">';
                continue;
            }

            $top_bits[] = '<span class="cms-ftr-preview-misc">' . htmlspecialchars($lab !== '' ? $lab : $val, ENT_QUOTES, 'UTF-8') . '</span>';
        }

        $html = '<div class="' . htmlspecialchars($wrapper_class, ENT_QUOTES, 'UTF-8') . ' cms-ftr-preview-strip">';
        if (!empty($top_bits)) {
            $html .= '<div class="cms-ftr-preview-top">' . implode('', $top_bits) . '</div>';
        }
        if (!empty($columns)) {
            $html .= '<div class="cms-ftr-preview-columns">';
            foreach ($columns as $key => $col) {
                if ($key === '_misc' && empty($col['title']) && empty($col['links'])) {
                    continue;
                }
                $html .= '<div class="cms-ftr-preview-col">';
                if (!empty($col['title'])) {
                    $html .= '<h4>' . htmlspecialchars($col['title'], ENT_QUOTES, 'UTF-8') . '</h4>';
                }
                if (!empty($col['links'])) {
                    $html .= '<ul>';
                    foreach ($col['links'] as $lnk) {
                        $html .= '<li><a href="' . htmlspecialchars($lnk['href'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($lnk['label'], ENT_QUOTES, 'UTF-8') . '</a></li>';
                    }
                    $html .= '</ul>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        if ($copyright !== '') {
            $html .= '<div class="cms-ftr-preview-copy">' . htmlspecialchars(str_replace('{year}', date('Y'), $copyright), ENT_QUOTES, 'UTF-8') . '</div>';
        }
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('cms_header_design_preview_strip_html')) {
    /**
     * Webshop-like strip markup for admin preview (gp-cms-header-strip).
     *
     * @param string $profile
     * @param string $uploads_base
     * @return string
     */
    function cms_header_design_preview_strip_html($profile, $uploads_base = '')
    {
        $rows = cms_storefront_get_header_rows_for_profile($profile);
        if (empty($rows)) {
            return '<div class="cms-hdr-preview-empty"><p>No active items in this design yet.</p></div>';
        }

        $uploads_base = rtrim((string) $uploads_base, '/') . '/';
        $title = '';
        $body_parts = array();

        foreach ($rows as $row) {
            $fk = isset($row['field_key']) ? strtolower((string) $row['field_key']) : '';
            $val = isset($row['value']) ? trim((string) $row['value']) : '';
            $lab = isset($row['label']) ? trim((string) $row['label']) : '';
            if ($val === '' || preg_match('/<script|googletagmanager/i', $val)) {
                continue;
            }

            if (preg_match('/\.(jpe?g|png|gif|webp)$/i', $val) && strpos($val, 'webshop/') === 0) {
                $src = $uploads_base . $val;
                $body_parts[] = '<img class="cms-hdr-preview-logo" src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="">';
                continue;
            }

            if (preg_match('#^https?://#i', $val) || (strpos($val, '/') === 0 && strpos($val, '<') === false)) {
                $body_parts[] = '<a class="cms-hdr-preview-link" href="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '">'
                    . htmlspecialchars($lab !== '' ? $lab : $val, ENT_QUOTES, 'UTF-8') . '</a>';
                continue;
            }

            if (strpos($val, '<') !== false) {
                $body_parts[] = '<div class="cms-hdr-preview-html">' . $val . '</div>';
                continue;
            }

            if ($title === '' && $lab !== '' && strlen($val) < 80) {
                $title = $lab;
            }
            $body_parts[] = '<span class="cms-hdr-preview-text">' . nl2br(htmlspecialchars($val, ENT_QUOTES, 'UTF-8')) . '</span>';
        }

        if (empty($body_parts)) {
            return '<div class="cms-hdr-preview-empty"><p>Items exist but have no displayable content.</p></div>';
        }

        $html = '<div class="gp-cms-header-strip cms-hdr-preview-strip">';
        $html .= '<div class="container gp-cms-header-inner">';
        if ($title !== '') {
            $html .= '<span class="gp-cms-hstrip-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</span>';
        }
        $html .= '<div class="gp-cms-hstrip-body">' . implode('', $body_parts) . '</div>';
        $html .= '</div></div>';
        return $html;
    }
}

if (!function_exists('cms_header_design_preview_document')) {
    /**
     * Full HTML document for iframe preview (WordPress-customizer style).
     *
     * @param string $profile
     * @param string $profile_label
     * @param string $uploads_base
     * @param bool   $compact Smaller chrome for list thumbnails
     * @return string
     */
    function cms_header_design_preview_document($profile, $profile_label, $uploads_base = '', $compact = false)
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        $strip = cms_header_design_preview_strip_html($profile, $uploads_base);
        $label = trim((string) $profile_label);
        if ($label === '') {
            $label = $profile;
        }

        $compact_class = $compact ? ' is-compact' : '';
        $page_hint = $compact ? '' : '<div class="cms-hdr-preview-page-mock"><span>Page content (preview)</span></div>';

        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Preview: ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</title>'
            . '<style>' . cms_header_design_preview_css() . '</style></head>'
            . '<body class="cms-hdr-preview-body' . $compact_class . '">'
            . '<div class="cms-hdr-preview-chrome">'
            . '<div class="cms-hdr-preview-site-bar">'
            . '<span class="cms-hdr-preview-site-logo"></span>'
            . '<nav class="cms-hdr-preview-site-nav"><span>Home</span><span>Shop</span><span>Contact</span></nav>'
            . '</div>'
            . '<div class="cms-hdr-preview-label">Assigned header: <strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</strong>'
            . ' <code>' . htmlspecialchars($profile, ENT_QUOTES, 'UTF-8') . '</code></div>'
            . $strip
            . $page_hint
            . '</div></body></html>';
    }
}

if (!function_exists('cms_header_design_preview_css')) {
    function cms_header_design_preview_css()
    {
        return 'body.cms-hdr-preview-body{margin:0;font-family:Inter,Segoe UI,sans-serif;background:#f1f5f9;color:#0f172a;}'
            . '.cms-hdr-preview-chrome{max-width:100%;margin:0 auto;}'
            . '.cms-hdr-preview-site-bar{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:#0f172a;color:#fff;}'
            . '.cms-hdr-preview-site-logo{width:100px;height:28px;background:rgba(255,255,255,.2);border-radius:4px;}'
            . '.cms-hdr-preview-site-nav span{margin-left:14px;font-size:13px;opacity:.85;}'
            . '.cms-hdr-preview-label{padding:8px 16px;font-size:12px;background:#e0e7ff;color:#3730a3;}'
            . '.cms-hdr-preview-label code{background:#fff;padding:2px 6px;border-radius:4px;margin-left:6px;}'
            . '.gp-cms-header-strip,.cms-hdr-preview-strip{background:linear-gradient(90deg,#1e40af,#2563eb);color:#fff;padding:10px 0;}'
            . '.gp-cms-header-inner{display:flex;flex-wrap:wrap;align-items:center;gap:12px;padding:0 16px;max-width:1200px;margin:0 auto;}'
            . '.gp-cms-hstrip-title{font-weight:700;font-size:14px;}'
            . '.gp-cms-hstrip-body{display:flex;flex-wrap:wrap;align-items:center;gap:10px;font-size:13px;}'
            . '.cms-hdr-preview-logo{max-height:36px;width:auto;}'
            . '.cms-hdr-preview-link{color:#fff;text-decoration:underline;}'
            . '.cms-hdr-preview-empty{padding:24px;text-align:center;color:#64748b;background:#fff;border:1px dashed #cbd5e1;margin:12px;border-radius:8px;}'
            . '.cms-hdr-preview-page-mock{min-height:120px;background:#fff;margin:0;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:13px;}'
            . 'body.is-compact .cms-hdr-preview-site-bar{padding:8px 12px;}'
            . 'body.is-compact .cms-hdr-preview-label{display:none;}'
            . 'body.is-compact .cms-hdr-preview-page-mock{display:none;min-height:0;}';
    }
}

if (!function_exists('cms_storefront_get_footer_rows_for_profile')) {
    /**
     * @param string $profile
     * @return array<int,array>
     */
    function cms_storefront_get_footer_rows_for_profile($profile)
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            $profile = 'default';
        }

        $CI =& get_instance();
        if (!isset($CI->webshop_settings_model)) {
            $CI->load->model('webshop_settings_model');
        }
        if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
            return array();
        }

        $CI->db->where('section_type', 'footer');
        $CI->db->where('is_active', 1);
        $CI->db->where('field_key !=', 'footer_profile');
        $CI->db->order_by('sort_order', 'ASC');
        $CI->db->order_by('field_key', 'ASC');
        $q = $CI->db->get('sma_cms_webshop_header_footer');
        if (!$q || $q->num_rows() === 0) {
            return array();
        }

        $out = array();
        foreach ($q->result_array() as $row) {
            $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
            if (!cms_storefront_row_matches_header_profile($fk, $profile)) {
                continue;
            }
            $out[] = $row;
        }
        return $out;
    }
}

if (!function_exists('cms_storefront_get_footer_profiles')) {
    /**
     * @return array<int,array{slug:string,label:string}>
     */
    function cms_storefront_get_footer_profiles()
    {
        $CI =& get_instance();
        if (!isset($CI->webshop_settings_model)) {
            $CI->load->model('webshop_settings_model');
        }
        if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
            return array(array('slug' => 'default', 'label' => 'Default'));
        }

        $profiles = array();
        $CI->db->where('section_type', 'footer');
        $CI->db->where('field_key', 'footer_profile');
        $CI->db->where('is_active', 1);
        $CI->db->order_by('sort_order', 'ASC');
        $q = $CI->db->get('sma_cms_webshop_header_footer');
        if ($q && $q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
                $slug = cms_storefront_normalize_profile_slug(isset($row['value']) ? $row['value'] : '');
                if ($slug === '') {
                    continue;
                }
                $profiles[] = array(
                    'slug'  => $slug,
                    'label' => trim((string) (isset($row['label']) && $row['label'] !== '' ? $row['label'] : $slug)),
                );
            }
        }

        if (empty($profiles)) {
            $CI->db->select('field_key');
            $CI->db->where('section_type', 'footer');
            $CI->db->where('is_active', 1);
            $CI->db->like('field_key', '__', 'both');
            $q2 = $CI->db->get('sma_cms_webshop_header_footer');
            $seen = array();
            if ($q2 && $q2->num_rows() > 0) {
                foreach ($q2->result_array() as $row) {
                    $fk = strtolower(trim((string) $row['field_key']));
                    $pos = strpos($fk, '__');
                    if ($pos === false) {
                        continue;
                    }
                    $slug = cms_storefront_normalize_profile_slug(substr($fk, 0, $pos));
                    if ($slug === '' || isset($seen[$slug])) {
                        continue;
                    }
                    $seen[$slug] = true;
                    $profiles[] = array('slug' => $slug, 'label' => ucfirst(str_replace(array('-', '_'), ' ', $slug)));
                }
            }
        }

        if (empty($profiles)) {
            $profiles[] = array('slug' => 'default', 'label' => 'Default (all footer rows)');
        }

        $site = cms_layout_builder_bootstrap_profile_slug();
        $has_site = false;
        foreach ($profiles as $p) {
            if (isset($p['slug']) && cms_storefront_normalize_profile_slug($p['slug']) === $site) {
                $has_site = true;
                break;
            }
        }
        if (!$has_site) {
            array_unshift($profiles, array('slug' => $site, 'label' => 'Site (Storefront layout)'));
        }

        return $profiles;
    }
}

if (!function_exists('cms_footer_design_preview_strip_html')) {
    /**
     * Footer strip for admin live preview.
     *
     * @param string $profile
     * @param string $uploads_base
     * @return string
     */
    function cms_footer_design_preview_strip_html($profile, $uploads_base = '')
    {
        $rows = cms_storefront_get_footer_rows_for_profile($profile);
        if (empty($rows)) {
            return '<div class="cms-hdr-preview-empty"><p>No active items in this footer design yet.</p></div>';
        }
        $strip = cms_footer_strip_html_from_rows($rows, $uploads_base, 'gp-cms-footer-strip');
        return $strip !== '' ? $strip : '<div class="cms-hdr-preview-empty"><p>Items exist but have no displayable content.</p></div>';
    }
}

if (!function_exists('cms_footer_design_preview_document')) {
    /**
     * @param string $profile
     * @param string $profile_label
     * @param string $uploads_base
     * @param bool   $compact
     * @return string
     */
    function cms_footer_design_preview_document($profile, $profile_label, $uploads_base = '', $compact = false)
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        $strip = cms_footer_design_preview_strip_html($profile, $uploads_base);
        $label = trim((string) $profile_label);
        if ($label === '') {
            $label = $profile;
        }

        $compact_class = $compact ? ' is-compact is-footer' : ' is-footer';
        $page_hint = $compact ? '' : '<div class="cms-hdr-preview-page-mock cms-ftr-preview-page-mock"><span>Page content area</span></div>';

        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Footer preview: ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</title>'
            . '<style>' . cms_header_design_preview_css() . cms_footer_design_preview_css_extra() . '</style></head>'
            . '<body class="cms-hdr-preview-body' . $compact_class . '">'
            . '<div class="cms-hdr-preview-chrome cms-ftr-preview-chrome">'
            . $page_hint
            . '<div class="cms-hdr-preview-label cms-ftr-preview-label">Assigned footer: <strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</strong>'
            . ' <code>' . htmlspecialchars($profile, ENT_QUOTES, 'UTF-8') . '</code></div>'
            . $strip
            . '</div></body></html>';
    }
}

if (!function_exists('cms_footer_design_preview_css_extra')) {
    function cms_footer_design_preview_css_extra()
    {
        return 'body.is-footer .cms-hdr-preview-chrome{display:flex;flex-direction:column;min-height:100vh;}'
            . '.cms-ftr-preview-page-mock{flex:1;min-height:200px;}'
            . '.gp-cms-footer-strip,.cms-ftr-preview-strip{background:#0f172a;color:#e2e8f0;padding:24px 16px;margin-top:auto;}'
            . '.cms-ftr-preview-label{background:#1e293b;color:#94a3b8;}'
            . '.cms-ftr-preview-columns{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;max-width:1100px;margin:0 auto;}'
            . '.cms-ftr-preview-col h4{margin:0 0 8px;font-size:13px;color:#fff;}'
            . '.cms-ftr-preview-col ul{margin:0;padding:0;list-style:none;font-size:12px;}'
            . '.cms-ftr-preview-col a{color:#93c5fd;text-decoration:none;}'
            . '.cms-ftr-preview-top{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px;max-width:1100px;margin-left:auto;margin-right:auto;}'
            . '.cms-ftr-preview-tagline{font-size:14px;margin:0;max-width:320px;}'
            . '.cms-ftr-preview-copy{text-align:center;font-size:11px;color:#64748b;margin-top:20px;padding-top:12px;border-top:1px solid #334155;}'
            . 'body.is-compact.is-footer .cms-ftr-preview-page-mock{display:none;min-height:80px;}';
    }
}

if (!function_exists('cms_render_storefront_footer_profile_html')) {
    /**
     * @param string $profile
     * @param string $uploads_base
     * @return string
     */
    function cms_render_storefront_footer_profile_html($profile, $uploads_base = '')
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === 'default' || $profile === '') {
            $profile = cms_layout_builder_resolve_profile_slug('default', 'footer');
        }
        $decoded = cms_layout_builder_load_json_config('footer', $profile);
        if (is_array($decoded)) {
            return '<div class="cms-page-footer-block cms-page-footer-block--profile">' . cms_render_footer_builder_html($decoded, $uploads_base, $profile) . '</div>';
        }

        $rows = cms_storefront_get_footer_rows_for_profile($profile);
        if (empty($rows)) {
            return '';
        }
        $strip = cms_footer_strip_html_from_rows($rows, $uploads_base, 'cms-storefront-footer-profile');
        if ($strip === '') {
            return '';
        }
        return '<div class="cms-page-footer-block cms-page-footer-block--profile" data-footer-profile="'
            . htmlspecialchars($profile, ENT_QUOTES, 'UTF-8') . '">' . $strip . '</div>';
    }
}

if (!function_exists('cms_layout_builder_bootstrap_profile_slug')) {
    /**
     * Built-in layout profile (always exists; legacy unprefixed DB keys). Not the same as "default assignment".
     *
     * @return string
     */
    function cms_layout_builder_bootstrap_profile_slug()
    {
        return 'site';
    }
}

if (!function_exists('cms_layout_builder_default_profile_slug')) {
    /**
     * Storefront default layout slug for header or footer (CMS "Default Store Profile", API fallback).
     *
     * @param string $section header|footer
     * @return string
     */
    function cms_layout_builder_default_profile_slug($section = 'header')
    {
        static $cache = array();
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        if (isset($cache[$section])) {
            return $cache[$section];
        }

        $bootstrap = cms_layout_builder_bootstrap_profile_slug();
        $cache[$section] = $bootstrap;

        $CI =& get_instance();
        if (!isset($CI->webshop_settings_model)) {
            $CI->load->model('webshop_settings_model');
        }
        if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
            return $bootstrap;
        }

        $CI->db->where('section_type', $section);
        $CI->db->where('field_key', 'layout_default_profile');
        $CI->db->where('is_active', 1);
        $CI->db->limit(1);
        $q = $CI->db->get('sma_cms_webshop_header_footer');
        if ($q && $q->num_rows() > 0) {
            $row = $q->row_array();
            $slug = cms_storefront_normalize_profile_slug(isset($row['value']) ? $row['value'] : '');
            if ($slug !== '') {
                $cache[$section] = $slug;
            }
        }

        return $cache[$section];
    }
}

if (!function_exists('cms_layout_builder_resolve_profile_slug')) {
    /**
     * Map empty/default sentinel to the configured default layout for a section.
     *
     * @param string $profile
     * @param string $section header|footer
     * @return string
     */
    function cms_layout_builder_resolve_profile_slug($profile, $section = 'header')
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '' || $profile === 'default') {
            return cms_layout_builder_default_profile_slug($section);
        }
        return $profile;
    }
}

if (!function_exists('cms_page_design_uses_store_default_profile')) {
    /**
     * Whether a page chrome assignment should show as "Default Store Header/Footer" in admin UI.
     *
     * @param string $assigned_slug from page section JSON (default, site, landing, …)
     * @param string $section     header|footer
     * @return bool
     */
    function cms_page_design_uses_store_default_profile($assigned_slug, $section = 'header')
    {
        $assigned_slug = cms_storefront_normalize_profile_slug($assigned_slug);
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $default = cms_layout_builder_default_profile_slug($section);
        if ($assigned_slug === '' || $assigned_slug === 'default') {
            return true;
        }
        if ($assigned_slug === $default) {
            return true;
        }
        $bootstrap = cms_layout_builder_bootstrap_profile_slug();
        if ($assigned_slug === 'site' && ($default === $bootstrap || $default === 'site')) {
            return true;
        }
        return false;
    }
}

if (!function_exists('cms_page_design_default_option_label')) {
    /**
     * Dropdown label for the store default layout option on CMS page edit.
     *
     * @param string              $section  header|footer
     * @param array<int,array>     $profiles from get_layout_builder_profiles_for_section
     * @return string
     */
    function cms_page_design_default_option_label($section, array $profiles = array())
    {
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $slug = cms_layout_builder_default_profile_slug($section);
        $name = $slug;
        foreach ($profiles as $p) {
            if (!is_array($p)) {
                continue;
            }
            $ps = isset($p['slug']) ? cms_storefront_normalize_profile_slug($p['slug']) : '';
            if ($ps === $slug) {
                $lbl = isset($p['label']) ? trim((string) $p['label']) : '';
                if ($lbl !== '') {
                    $name = $lbl;
                }
                break;
            }
        }
        $kind = ($section === 'footer') ? 'Footer' : 'Header';
        if ($name !== '' && strcasecmp($name, $slug) !== 0) {
            return '— Default Store ' . $kind . ' (' . $name . ' / ' . $slug . ') —';
        }
        return '— Default Store ' . $kind . ' (' . $slug . ') —';
    }
}

if (!function_exists('cms_page_design_profile_skip_in_list')) {
    /**
     * Profiles listed separately as the store default option should not repeat in the list.
     *
     * @param string $slug
     * @param string $section header|footer
     * @return bool
     */
    function cms_page_design_profile_skip_in_list($slug, $section = 'header')
    {
        $slug = cms_storefront_normalize_profile_slug($slug);
        if ($slug === '' || $slug === 'default') {
            return true;
        }
        if ($slug === cms_layout_builder_default_profile_slug($section)) {
            return true;
        }
        $bootstrap = cms_layout_builder_bootstrap_profile_slug();
        $default = cms_layout_builder_default_profile_slug($section);
        if ($slug === 'site' && ($default === $bootstrap || $default === 'site')) {
            return true;
        }
        return false;
    }
}

if (!function_exists('cms_layout_builder_profile_slug')) {
    /**
     * Default header layout profile (backward compatible alias).
     *
     * @return string
     */
    function cms_layout_builder_profile_slug()
    {
        return cms_layout_builder_default_profile_slug('header');
    }
}

if (!function_exists('cms_layout_builder_profile_has_config')) {
    /**
     * Whether this profile uses the visual layout builder (JSON row exists).
     *
     * @param string $profile
     * @return bool
     */
    function cms_layout_builder_profile_has_config($profile, $section = null)
    {
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            return false;
        }
        $sections = array('header', 'footer');
        if ($section !== null && $section !== '') {
            $sections = array(strtolower(trim((string) $section)));
        }
        foreach ($sections as $sec) {
            $sec = strtolower(trim((string) $sec));
            if ($sec !== 'header' && $sec !== 'footer') {
                continue;
            }
            $field = $sec === 'footer' ? 'footer_builder_config' : 'header_builder_config';
            $keys = array();
            if ($profile !== 'default') {
                $keys[] = $profile . '__' . $field;
            }
            if ($profile === cms_layout_builder_bootstrap_profile_slug()) {
                $keys[] = $field;
            }

            $CI =& get_instance();
            if (!isset($CI->webshop_settings_model)) {
                $CI->load->model('webshop_settings_model');
            }
            if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
                continue;
            }

            foreach ($keys as $fk) {
                $CI->db->where('section_type', $sec);
                $CI->db->where('field_key', $fk);
                $CI->db->where('is_active', 1);
                $CI->db->limit(1);
                $q = $CI->db->get('sma_cms_webshop_header_footer');
                if ($q && $q->num_rows() > 0) {
                    $row = $q->row_array();
                    if (trim((string) (isset($row['value']) ? $row['value'] : '')) !== '') {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}

if (!function_exists('cms_layout_builder_load_json_config')) {
    /**
     * Load header/footer builder JSON from DB (works on frontend without admin model).
     *
     * @param string      $section header|footer
     * @param string|null $profile profile slug; null = default site profile
     * @return array|null decoded config or null
     */
    function cms_layout_builder_load_json_config($section, $profile = null)
    {
        $section = strtolower(trim((string) $section));
        $field = $section === 'footer' ? 'footer_builder_config' : 'header_builder_config';
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            $profile = cms_layout_builder_default_profile_slug($section);
        }
        $keys = array();
        if ($profile !== '' && $profile !== 'default') {
            $keys[] = $profile . '__' . $field;
        }
        if ($profile === cms_layout_builder_bootstrap_profile_slug()) {
            $keys[] = $field;
        }

        $CI =& get_instance();
        if (!isset($CI->webshop_settings_model)) {
            $CI->load->model('webshop_settings_model');
        }
        if (!$CI->webshop_settings_model->header_footer_schema_ready()) {
            return null;
        }

        foreach ($keys as $fk) {
            $CI->db->where('section_type', $section);
            $CI->db->where('field_key', $fk);
            $CI->db->where('is_active', 1);
            $CI->db->order_by('id', 'DESC');
            $CI->db->limit(1);
            $q = $CI->db->get('sma_cms_webshop_header_footer');
            if ($q && $q->num_rows() > 0) {
                $row = $q->row_array();
                $raw = isset($row['value']) ? (string) $row['value'] : '';
                if ($raw !== '') {
                    $decoded = json_decode($raw, true);
                    return is_array($decoded) ? $decoded : null;
                }
            }
        }
        return null;
    }
}

if (!function_exists('cms_webshop_clear_getsettings_cache')) {
    /**
     * Drop cached Webshop API getsettings payload after layout/header changes.
     */
    function cms_webshop_clear_getsettings_cache()
    {
        $cache_file = APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'webshop_api_getsettings.cache.php';
        if (is_file($cache_file)) {
            @unlink($cache_file);
        }
    }
}

if (!function_exists('cms_webshop_refresh_storefront_cms_cache')) {
    /**
     * Bust remote webshop CMS page + nav disk/session caches after CMS admin saves.
     *
     * @param string|null $storefront_base
     * @return void
     */
    function cms_webshop_refresh_storefront_cms_cache($storefront_base = null)
    {
        if ($storefront_base === null) {
            if (!function_exists('cms_site_settings_storefront_base_url')) {
                $CI = function_exists('get_instance') ? get_instance() : null;
                if ($CI) {
                    $CI->load->helper('cms_site_settings');
                }
            }
            $storefront_base = function_exists('cms_site_settings_storefront_base_url')
                ? cms_site_settings_storefront_base_url()
                : '';
        }

        $base = rtrim(trim((string) $storefront_base), '/');
        if ($base === '') {
            return;
        }

        $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 6,
                'ignore_errors' => true,
            ),
        ));
        @file_get_contents($base . '/?refresh_cms=1', false, $ctx);
    }
}

if (!function_exists('cms_header_builder_default_config')) {
    /**
     * @return array<string,mixed>
     */
    function cms_header_builder_default_config()
    {
        $defaults = array(
            'show_logo'      => true,
            'show_cart'      => true,
            'show_wishlist'  => true,
            'show_account'   => true,
            'show_search'    => false,
            'show_nav'       => true,
            'show_phone'     => false,
            'show_promo'     => false,
            'show_button'    => false,
            'logo_image'     => '',
            'favicon_image'  => '',
            'phone'          => '',
            'promo_text'     => '',
            'button_text'    => 'Click Here',
            'button_link'    => '',
            'google_analytics'   => '',
            'custom_header_tags' => array(),
            'bg_color'       => '#0f172a',
            'text_color'     => '#ffffff',
            'accent_color'   => '#3b82f6',
            'icon_bg'        => 'transparent',
            'promo_align'      => 'center',
            'logo_align'       => 'left',
            'nav_align'        => 'center',
            'phone_align'      => 'right',
            'search_align'     => 'right',
            'wishlist_align'   => 'right',
            'cart_align'       => 'right',
            'account_align'    => 'right',
            'button_align'     => 'right',
            'bar_align'        => 'split',
            'nav_active_underline' => true,
        );
        $style_fields = array_keys(cms_layout_builder_element_style_field_map());
        foreach (array('promo', 'logo', 'nav', 'phone', 'search', 'wishlist', 'cart', 'account', 'button') as $ek) {
            foreach ($style_fields as $sf) {
                $defaults[$ek . '_' . $sf] = '';
            }
        }
        return $defaults;
    }
}

if (!function_exists('cms_header_builder_blank_config')) {
    /**
     * Empty header layout for a newly created profile (no copy from site).
     *
     * @return array<string,mixed>
     */
    function cms_header_builder_blank_config()
    {
        $cfg = cms_header_builder_default_config();
        foreach (array('show_logo', 'show_cart', 'show_wishlist', 'show_account', 'show_search', 'show_nav', 'show_phone', 'show_promo', 'show_button') as $k) {
            $cfg[$k] = false;
        }
        $cfg['logo_image'] = '';
        $cfg['favicon_image'] = '';
        $cfg['phone'] = '';
        $cfg['promo_text'] = '';
        $cfg['button_text'] = '';
        $cfg['button_link'] = '';
        $cfg['google_analytics'] = '';
        $cfg['custom_header_tags'] = array();
        return $cfg;
    }
}

if (!function_exists('cms_layout_builder_uploads_preview_url')) {
    /**
     * Public URL for a stored uploads path (webshop/, cms_media/, or legacy filename).
     *
     * @param string $stored_path Relative path under uploads/ (e.g. webshop/logo.png, cms_media/favicons/icon.png)
     * @param string $upload_base Absolute uploads base URL with trailing slash
     * @return string
     */
    function cms_layout_builder_uploads_preview_url($stored_path, $upload_base)
    {
        $path = trim(str_replace('\\', '/', (string) $stored_path));
        $upload_base = rtrim((string) $upload_base, '/');
        if ($path === '' || $upload_base === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        if (strpos($path, 'webshop/') === 0 || strpos($path, 'cms_media/') === 0) {
            return $upload_base . '/' . ltrim($path, '/');
        }
        return $upload_base . '/webshop/' . ltrim($path, '/');
    }
}

if (!function_exists('cms_header_builder_normalize_custom_header_tags')) {
    /**
     * @param mixed $items
     * @return array<int,array{name:string,code:string}>
     */
    function cms_header_builder_normalize_custom_header_tags($items)
    {
        if (is_object($items)) {
            $items = (array) $items;
        }
        if (!is_array($items)) {
            return array();
        }
        $out = array();
        foreach ($items as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            $row = is_array($row) ? $row : array();
            $name = isset($row['name']) ? trim((string) $row['name']) : '';
            $code = isset($row['code']) ? trim((string) $row['code']) : '';
            if ($code === '') {
                continue;
            }
            $out[] = array(
                'name' => $name,
                'code' => $code,
            );
        }
        return $out;
    }
}

if (!function_exists('cms_header_builder_get_custom_header_tags')) {
    /**
     * @param array<string,mixed> $cfg
     * @return array<int,array{name:string,code:string}>
     */
    function cms_header_builder_get_custom_header_tags(array $cfg)
    {
        if (!array_key_exists('custom_header_tags', $cfg) || !is_array($cfg['custom_header_tags'])) {
            return array();
        }
        return cms_header_builder_normalize_custom_header_tags($cfg['custom_header_tags']);
    }
}

if (!function_exists('cms_header_builder_looks_like_json_ld')) {
    /**
     * @param string $raw
     * @return bool
     */
    function cms_header_builder_looks_like_json_ld($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '' || $raw[0] !== '{') {
            return false;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded)
            && json_last_error() === JSON_ERROR_NONE
            && (isset($decoded['@context']) || isset($decoded['@type']));
    }
}

if (!function_exists('cms_header_builder_wrap_json_ld_for_head')) {
    /**
     * @param string $raw
     * @return string|false
     */
    function cms_header_builder_wrap_json_ld_for_head($raw)
    {
        $decoded = json_decode(trim((string) $raw), true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        $json = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }
        return '<script type="application/ld+json">' . $json . '</script>';
    }
}

if (!function_exists('cms_header_builder_prepare_custom_header_tag_code')) {
    /**
     * Validate/normalize a custom head snippet (title, meta, link, script, noscript, or bare JSON-LD).
     * Saved value is output in webshop <head> exactly as returned here.
     *
     * @param string $raw
     * @return string|false
     */
    function cms_header_builder_prepare_custom_header_tag_code($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return '';
        }
        if (cms_header_builder_custom_tag_code_is_dangerous($raw)) {
            return false;
        }
        if (preg_match('/<(?:script|meta|link|noscript|title)\b/i', $raw)) {
            return $raw;
        }
        if (cms_header_builder_looks_like_json_ld($raw)) {
            $wrapped = cms_header_builder_wrap_json_ld_for_head($raw);
            return $wrapped === false ? false : $wrapped;
        }
        return false;
    }
}

if (!function_exists('cms_header_builder_custom_tag_code_is_dangerous')) {
    /**
     * Block javascript: URLs and inline event handlers (onclick=), not meta content= attributes.
     *
     * @param string $raw
     * @return bool
     */
    function cms_header_builder_custom_tag_code_is_dangerous($raw)
    {
        return (bool) preg_match('/javascript:/i', $raw)
            || (bool) preg_match('/\bon[a-z]+\s*=/i', $raw);
    }
}

if (!function_exists('cms_header_builder_parse_custom_header_tags_from_post')) {
    /**
     * @param mixed $post
     * @return array<int,array{name:string,code:string}>
     */
    function cms_header_builder_parse_custom_header_tags_from_post($post)
    {
        if (!is_array($post)) {
            return array();
        }
        $rows = array();
        foreach ($post as $row) {
            if (!is_array($row)) {
                continue;
            }
            $name = isset($row['name']) ? trim((string) $row['name']) : '';
            $code = isset($row['code']) ? trim((string) $row['code']) : '';
            $prepared = cms_header_builder_prepare_custom_header_tag_code($code);
            if ($prepared === false || $prepared === '') {
                continue;
            }
            $rows[] = array(
                'name' => $name,
                'code' => (string) $prepared,
            );
        }
        return cms_header_builder_normalize_custom_header_tags($rows);
    }
}

if (!function_exists('cms_layout_builder_render_custom_header_tags_editor')) {
    /**
     * @param array<string,mixed> $cfg
     * @return string
     */
    function cms_layout_builder_render_custom_header_tags_editor(array $cfg)
    {
        $items = cms_header_builder_get_custom_header_tags($cfg);
        if (empty($items)) {
            $items = array(array('name' => '', 'code' => ''));
        }
        $html = '<div id="cmsHeaderCustomTags" class="cms-lb-custom-header-tags">';
        foreach ($items as $i => $item) {
            $name = isset($item['name']) ? (string) $item['name'] : '';
            $code = isset($item['code']) ? (string) $item['code'] : '';
            $html .= '<div class="cms-lb-custom-header-tag-item" data-tag-index="' . (int) $i . '">'
                . '<div class="cms-lb-custom-header-tag-item__head">'
                . '<label class="cms-lb-custom-header-tag-item__label">Tag name</label>'
                . '<input type="text" name="custom_header_tags[' . (int) $i . '][name]" class="form-control input-sm cms-lb-custom-header-tag-name" '
                . 'placeholder="e.g. Meta Pixel, Hotjar" value="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">'
                . '<button type="button" class="btn btn-default btn-xs cms-lb-custom-header-tag-remove" title="Remove tag"><i class="fa fa-trash-o"></i></button>'
                . '</div>'
                . '<label class="cms-lb-custom-header-tag-item__label">Header code</label>'
                . '<textarea name="custom_header_tags[' . (int) $i . '][code]" class="form-control input-sm cms-lb-custom-header-tag-code" rows="8" '
                . 'placeholder="Paste &lt;title&gt;, &lt;meta&gt;, &lt;link&gt;, &lt;script&gt;, JSON-LD {&quot;@context&quot;:...}, or &lt;noscript&gt;. Shown in webshop &lt;head&gt; on every page.">' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</textarea>'
                . '</div>';
        }
        $html .= '<button type="button" class="btn btn-default btn-sm" id="cmsLbAddCustomHeaderTag"><i class="fa fa-plus"></i> Add custom tag</button>'
            . '</div>';
        return $html;
    }
}

if (!function_exists('cms_header_builder_show_element_defs')) {
    /**
     * Header show toggles + align keys (layout builder UI).
     *
     * @return array<int,array{key:string,show_key:string,label:string,icon:string,align_key:string,default:string}>
     */
    function cms_header_builder_show_element_defs()
    {
        return array(
            array('key' => 'promo', 'show_key' => 'show_promo', 'label' => 'Promo bar', 'icon' => 'fa-bullhorn', 'align_key' => 'promo_align', 'default' => 'center'),
            array('key' => 'logo', 'show_key' => 'show_logo', 'label' => 'Logo', 'icon' => 'fa-picture-o', 'align_key' => 'logo_align', 'default' => 'left'),
            array('key' => 'nav', 'show_key' => 'show_nav', 'label' => 'Nav bar', 'icon' => 'fa-bars', 'align_key' => 'nav_align', 'default' => 'center'),
            array('key' => 'phone', 'show_key' => 'show_phone', 'label' => 'Phone', 'icon' => 'fa-phone', 'align_key' => 'phone_align', 'default' => 'right'),
            array('key' => 'search', 'show_key' => 'show_search', 'label' => 'Search', 'icon' => 'fa-search', 'align_key' => 'search_align', 'default' => 'right'),
            array('key' => 'wishlist', 'show_key' => 'show_wishlist', 'label' => 'Wishlist', 'icon' => 'fa-heart-o', 'align_key' => 'wishlist_align', 'default' => 'right'),
            array('key' => 'cart', 'show_key' => 'show_cart', 'label' => 'Cart', 'icon' => 'fa-shopping-cart', 'align_key' => 'cart_align', 'default' => 'right'),
            array('key' => 'account', 'show_key' => 'show_account', 'label' => 'Account', 'icon' => 'fa-user', 'align_key' => 'account_align', 'default' => 'right'),
            array('key' => 'button', 'show_key' => 'show_button', 'label' => 'Button', 'icon' => 'fa-external-link', 'align_key' => 'button_align', 'default' => 'right'),
        );
    }
}

if (!function_exists('cms_footer_builder_default_sections')) {
    /**
     * Default footer menu columns (editable in Layout Builder — not from CMS Pages).
     *
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_default_sections()
    {
        return array(
            array(
                'id'          => 'solutions',
                'title'       => 'Solutions',
                'show'        => true,
                'title_align' => 'left',
                'links_align' => 'left',
                'links'       => array(
                    array('label' => 'Trade Insights', 'url' => ''),
                    array('label' => 'Case Studies', 'url' => ''),
                ),
            ),
            array(
                'id'          => 'services',
                'title'       => 'Services',
                'show'        => true,
                'title_align' => 'left',
                'links_align' => 'left',
                'links'       => array(
                    array('label' => 'Market Entry', 'url' => ''),
                    array('label' => 'Compliance', 'url' => ''),
                ),
            ),
            array(
                'id'          => 'resources',
                'title'       => 'Resources',
                'show'        => true,
                'title_align' => 'left',
                'links_align' => 'left',
                'links'       => array(
                    array('label' => 'Blog', 'url' => ''),
                    array('label' => 'Guides', 'url' => ''),
                ),
            ),
            array(
                'id'          => 'company',
                'title'       => 'Company',
                'show'        => true,
                'title_align' => 'left',
                'links_align' => 'left',
                'links'       => array(
                    array('label' => 'About', 'url' => ''),
                    array('label' => 'Contact', 'url' => ''),
                ),
            ),
        );
    }
}

if (!function_exists('cms_footer_builder_section_storage_key')) {
    /**
     * @param string $section_id
     * @return string e.g. section_solutions
     */
    function cms_footer_builder_section_storage_key($section_id)
    {
        $id = preg_replace('/[^a-zA-Z0-9_-]/', '', trim((string) $section_id));
        return $id === '' ? 'section_unknown' : ('section_' . $id);
    }
}

if (!function_exists('cms_footer_builder_enrich_footer_link')) {
    /**
     * Fill label/url from page_id when a CMS page was selected but text fields were empty.
     *
     * @param array<string,mixed> $link
     * @return array<string,mixed>
     */
    function cms_footer_builder_enrich_footer_link(array $link)
    {
        if ((!isset($link['page_id']) || (int) $link['page_id'] <= 0) && isset($link['cms_page_key'])) {
            $page_key = trim((string) $link['cms_page_key']);
            if ($page_key !== '' && $page_key !== '__other__') {
                $link['page_id'] = (int) $page_key;
            }
        }
        $page_id = isset($link['page_id']) ? (int) $link['page_id'] : 0;
        if ($page_id <= 0) {
            if (isset($link['url'])) {
                $link['url'] = cms_footer_builder_link_storage_path($link['url']);
            }
            return $link;
        }
        $page = cms_footer_builder_get_link_page_option_by_id($page_id);
        if ($page !== null) {
            if (!isset($link['label']) || trim((string) $link['label']) === '') {
                $link['label'] = isset($page['label']) ? (string) $page['label'] : ('Page ' . $page_id);
            }
            $link['url'] = isset($page['path']) ? (string) $page['path'] : cms_footer_builder_link_storage_path(isset($page['url']) ? $page['url'] : '');
        }
        return $link;
    }
}

if (!function_exists('cms_footer_builder_match_page_option_by_title')) {
    /**
     * Match a footer section title to a published CMS page name (e.g. "Solutions").
     *
     * @param string $title
     * @return array<string,mixed>|null
     */
    function cms_footer_builder_match_page_option_by_title($title)
    {
        $title = trim((string) $title);
        if ($title === '') {
            return null;
        }
        foreach (cms_footer_builder_link_page_options() as $p) {
            $label = isset($p['label']) ? trim((string) $p['label']) : '';
            if ($label !== '' && strcasecmp($label, $title) === 0) {
                return $p;
            }
        }
        return null;
    }
}

if (!function_exists('cms_footer_builder_autofill_section_links_from_title')) {
    /**
     * When a section has a title but no links, link to the CMS page with the same name if found.
     *
     * @param string              $title
     * @param array<int,array<string,mixed>> $links
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_autofill_section_links_from_title($title, array $links)
    {
        if (!empty($links)) {
            return $links;
        }
        $match = cms_footer_builder_match_page_option_by_title($title);
        if (!$match) {
            return $links;
        }
        $pid = isset($match['id']) ? (int) $match['id'] : 0;
        if ($pid <= 0) {
            return $links;
        }
        return array(array(
            'label'   => isset($match['label']) ? (string) $match['label'] : $title,
            'url'     => isset($match['path']) ? (string) $match['path'] : cms_footer_builder_link_storage_path(isset($match['url']) ? $match['url'] : ''),
            'page_id' => $pid,
        ));
    }
}

if (!function_exists('cms_footer_builder_normalize_sections')) {
    /**
     * @param array<int,mixed> $sections
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_normalize_sections(array $sections)
    {
        $out = array();
        $seen = array();
        foreach ($sections as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = isset($row['id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', trim((string) $row['id'])) : '';
            if ($id === '') {
                $id = 'sec_' . substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
            }
            while (isset($seen[$id])) {
                $id .= '_' . substr(md5(uniqid('', true)), 0, 4);
            }
            $seen[$id] = true;

            $title = isset($row['title']) ? trim((string) $row['title']) : '';
            $links = array();
            if (isset($row['links']) && is_array($row['links'])) {
                foreach ($row['links'] as $link) {
                    if (!is_array($link)) {
                        continue;
                    }
                    $link = cms_footer_builder_enrich_footer_link($link);
                    $label = isset($link['label']) ? trim((string) $link['label']) : '';
                    $url = isset($link['url']) ? trim((string) $link['url']) : '';
                    if ($label === '' && $url === '' && empty($link['page_id'])) {
                        continue;
                    }
                    if ($label === '') {
                        $label = 'Link';
                    }
                    $link_row = array('label' => $label, 'url' => $url);
                    if (isset($link['page_id']) && (int) $link['page_id'] > 0) {
                        $link_row['page_id'] = (int) $link['page_id'];
                    }
                    $links[] = $link_row;
                }
            }
            $links = cms_footer_builder_autofill_section_links_from_title($title, $links);

            $out[] = array(
                'id'          => $id,
                'title'       => $title,
                'show'        => !isset($row['show']) || !empty($row['show']),
                'title_align' => cms_layout_builder_sanitize_align(isset($row['title_align']) ? $row['title_align'] : '', 'left'),
                'links_align' => cms_layout_builder_sanitize_align(isset($row['links_align']) ? $row['links_align'] : '', 'left'),
                'links'       => $links,
            );
        }
        return $out;
    }
}

if (!function_exists('cms_footer_builder_get_sections')) {
    /**
     * @param array<string,mixed> $cfg
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_get_sections(array $cfg)
    {
        if (array_key_exists('footer_sections', $cfg) && is_array($cfg['footer_sections'])) {
            return cms_footer_builder_normalize_sections($cfg['footer_sections']);
        }
        return cms_footer_builder_default_sections();
    }
}

if (!function_exists('cms_footer_builder_sync_sections_to_flat_keys')) {
    /**
     * Mirror section show/align into flat config keys for sidebar toggles and colors.
     *
     * @param array<string,mixed> $cfg
     * @return array<string,mixed>
     */
    function cms_footer_builder_sync_sections_to_flat_keys(array $cfg)
    {
        if (array_key_exists('footer_sections', $cfg) && is_array($cfg['footer_sections'])) {
            $sections = cms_footer_builder_normalize_sections($cfg['footer_sections']);
        } else {
            $sections = cms_footer_builder_default_sections();
        }
        $cfg['footer_sections'] = $sections;

        $active = array();
        foreach ($sections as $sec) {
            $skey = cms_footer_builder_section_storage_key($sec['id']);
            $active[$skey] = true;
            $cfg['show_' . $skey] = !empty($sec['show']);
            $cfg[$skey . '_align'] = isset($sec['links_align']) ? $sec['links_align'] : 'left';
            $cfg[$skey . '_title_align'] = isset($sec['title_align']) ? $sec['title_align'] : 'left';
        }

        foreach (array_keys($cfg) as $key) {
            if (preg_match('/^show_(section_[a-zA-Z0-9_-]+)$/', $key, $m) && !isset($active[$m[1]])) {
                unset($cfg[$key]);
            } elseif (preg_match('/^(section_[a-zA-Z0-9_-]+)_(align|title_align|title_color|text_color)$/', $key, $m) && !isset($active[$m[1]])) {
                unset($cfg[$key]);
            }
        }

        if (isset($cfg['footer_element_order']) && is_array($cfg['footer_element_order'])) {
            $cfg['footer_element_order'] = array_values(array_filter(
                $cfg['footer_element_order'],
                function ($order_key) use ($active) {
                    $order_key = (string) $order_key;
                    if (strpos($order_key, 'section_') !== 0) {
                        return true;
                    }
                    return isset($active[$order_key]);
                }
            ));
        }

        return $cfg;
    }
}

if (!function_exists('cms_footer_builder_parse_sections_from_post')) {
    /**
     * @param mixed $post
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_parse_sections_from_post($post)
    {
        if (!is_array($post) || empty($post)) {
            return array();
        }
        $rows = array();
        foreach ($post as $row) {
            if (!is_array($row)) {
                continue;
            }
            $rows[] = $row;
        }
        return cms_footer_builder_normalize_sections($rows);
    }
}

if (!function_exists('cms_footer_builder_merge_section_toggles_from_post')) {
    /**
     * Apply sidebar show/align posts onto footer_sections.
     *
     * @param array<string,mixed> $cfg
     * @return array<string,mixed>
     */
    function cms_footer_builder_merge_section_toggles_from_post(array $cfg)
    {
        if (!isset($cfg['footer_sections']) || !is_array($cfg['footer_sections'])) {
            return $cfg;
        }
        $CI =& get_instance();
        foreach ($cfg['footer_sections'] as $i => $sec) {
            if (!is_array($sec) || empty($sec['id'])) {
                continue;
            }
            $skey = cms_footer_builder_section_storage_key($sec['id']);
            $show_key = 'show_' . $skey;
            if ($CI->input->post($show_key) !== false) {
                $cfg['footer_sections'][$i]['show'] = cms_layout_builder_flag_from_post($show_key);
            } elseif (!isset($cfg['footer_sections'][$i]['show'])) {
                $cfg['footer_sections'][$i]['show'] = true;
            }
            $align = $CI->input->post($skey . '_align');
            if ($align !== false && $align !== null && $align !== '') {
                $cfg['footer_sections'][$i]['links_align'] = cms_layout_builder_sanitize_align($align, 'left');
            }
            $title_align = $CI->input->post($skey . '_title_align');
            if ($title_align !== false && $title_align !== null && $title_align !== '') {
                $cfg['footer_sections'][$i]['title_align'] = cms_layout_builder_sanitize_align($title_align, 'left');
            }
        }
        return cms_footer_builder_sync_sections_to_flat_keys($cfg);
    }
}

if (!function_exists('cms_footer_builder_brand_element_keys')) {
    function cms_footer_builder_brand_element_keys()
    {
        return array('logo', 'tagline', 'social', 'newsletter');
    }
}

if (!function_exists('cms_footer_builder_bottom_element_keys')) {
    function cms_footer_builder_bottom_element_keys()
    {
        return array('copyright', 'legal');
    }
}

if (!function_exists('cms_footer_builder_default_social_items')) {
    /**
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_default_social_items()
    {
        return array(
            array('id' => 'linkedin', 'title' => 'LinkedIn', 'url' => '', 'icon' => 'fa-linkedin-square', 'icon_image' => ''),
            array('id' => 'youtube', 'title' => 'YouTube', 'url' => '', 'icon' => 'fa-youtube-play', 'icon_image' => ''),
            array('id' => 'twitter', 'title' => 'X (Twitter)', 'url' => '', 'icon' => 'fa-twitter', 'icon_image' => ''),
            array('id' => 'email', 'title' => 'Email', 'url' => '', 'icon' => 'fa-envelope-o', 'icon_image' => ''),
        );
    }
}

if (!function_exists('cms_footer_builder_migrate_legacy_social_to_items')) {
    /**
     * @param array<string,mixed> $cfg
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_migrate_legacy_social_to_items(array $cfg)
    {
        $map = array(
            array('id' => 'linkedin', 'title' => 'LinkedIn', 'url_key' => 'social_linkedin', 'icon' => 'fa-linkedin-square'),
            array('id' => 'youtube', 'title' => 'YouTube', 'url_key' => 'social_youtube', 'icon' => 'fa-youtube-play'),
            array('id' => 'twitter', 'title' => 'X (Twitter)', 'url_key' => 'social_twitter', 'icon' => 'fa-twitter'),
            array('id' => 'email', 'title' => 'Email', 'url_key' => 'social_email', 'icon' => 'fa-envelope-o'),
        );
        $out = array();
        foreach ($map as $row) {
            $out[] = array(
                'id'         => $row['id'],
                'title'      => $row['title'],
                'url'        => isset($cfg[$row['url_key']]) ? trim((string) $cfg[$row['url_key']]) : '',
                'icon'       => $row['icon'],
                'icon_image' => '',
            );
        }
        return $out;
    }
}

if (!function_exists('cms_footer_builder_normalize_social_items')) {
    /**
     * @param array<int,mixed> $items
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_normalize_social_items(array $items)
    {
        $out = array();
        $seen = array();
        foreach ($items as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = isset($row['id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', trim((string) $row['id'])) : '';
            if ($id === '') {
                $id = 'soc_' . substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
            }
            while (isset($seen[$id])) {
                $id .= '_' . substr(md5(uniqid('', true)), 0, 4);
            }
            $seen[$id] = true;
            $icon = isset($row['icon']) ? trim((string) $row['icon']) : '';
            $icon = preg_replace('/[^a-zA-Z0-9_-]/', '', $icon);
            $icon_image = isset($row['icon_image']) ? trim((string) $row['icon_image']) : '';
            $out[] = array(
                'id'         => $id,
                'title'      => isset($row['title']) ? trim((string) $row['title']) : 'Social',
                'url'        => isset($row['url']) ? trim((string) $row['url']) : '',
                'icon'       => $icon,
                'icon_image' => $icon_image,
            );
        }
        return $out;
    }
}

if (!function_exists('cms_footer_builder_get_social_items')) {
    /**
     * @param array<string,mixed> $cfg
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_get_social_items(array $cfg)
    {
        if (array_key_exists('social_items', $cfg) && is_array($cfg['social_items'])) {
            return cms_footer_builder_normalize_social_items($cfg['social_items']);
        }
        return cms_footer_builder_normalize_social_items(cms_footer_builder_migrate_legacy_social_to_items($cfg));
    }
}

if (!function_exists('cms_footer_builder_parse_social_items_from_post')) {
    /**
     * @param mixed $post
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_parse_social_items_from_post($post)
    {
        if (!is_array($post) || empty($post)) {
            return array();
        }
        $rows = array();
        foreach ($post as $row) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }
        return cms_footer_builder_normalize_social_items($rows);
    }
}

if (!function_exists('cms_footer_builder_default_element_order')) {
    /**
     * @param array<string,mixed> $cfg
     * @return array<int,string>
     */
    function cms_footer_builder_default_element_order(array $cfg = array())
    {
        $order = array_merge(
            cms_footer_builder_brand_element_keys(),
            array('copyright', 'legal')
        );
        foreach (cms_footer_builder_get_sections($cfg) as $sec) {
            $order[] = cms_footer_builder_section_storage_key($sec['id']);
        }
        if (function_exists('cms_get_footer_nav_menus_from_cms_pages')) {
            foreach (cms_get_footer_nav_menus_from_cms_pages() as $group_id => $menu) {
                $order[] = 'cms_menu_' . (int) $group_id;
            }
        }
        return $order;
    }
}

if (!function_exists('cms_footer_builder_normalize_element_order')) {
    /**
     * @param array<int,mixed>    $order
     * @param array<string,mixed> $cfg
     * @return array<int,string>
     */
    function cms_footer_builder_normalize_element_order(array $order, array $cfg = array())
    {
        $valid = array();
        foreach (cms_footer_builder_show_element_defs($cfg) as $def) {
            if (!empty($def['key'])) {
                $valid[(string) $def['key']] = true;
            }
        }
        $out = array();
        $seen = array();
        foreach ($order as $key) {
            $key = trim((string) $key);
            if ($key === '' || !isset($valid[$key]) || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $key;
        }
        foreach (array_keys($valid) as $key) {
            if (!isset($seen[$key])) {
                $out[] = $key;
            }
        }
        return $out;
    }
}

if (!function_exists('cms_footer_builder_get_element_order')) {
    /**
     * @param array<string,mixed> $cfg
     * @return array<int,string>
     */
    function cms_footer_builder_get_element_order(array $cfg)
    {
        if (isset($cfg['footer_element_order']) && is_array($cfg['footer_element_order']) && !empty($cfg['footer_element_order'])) {
            return cms_footer_builder_normalize_element_order($cfg['footer_element_order'], $cfg);
        }
        return cms_footer_builder_default_element_order($cfg);
    }
}

if (!function_exists('cms_footer_builder_sort_element_defs')) {
    /**
     * @param array<int,array<string,mixed>> $defs
     * @param array<string,mixed>            $cfg
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_sort_element_defs(array $defs, array $cfg)
    {
        $order = cms_footer_builder_get_element_order($cfg);
        $by_key = array();
        foreach ($defs as $def) {
            if (!empty($def['key'])) {
                $by_key[(string) $def['key']] = $def;
            }
        }
        $sorted = array();
        foreach ($order as $key) {
            if (isset($by_key[$key])) {
                $sorted[] = $by_key[$key];
                unset($by_key[$key]);
            }
        }
        foreach ($by_key as $def) {
            $sorted[] = $def;
        }
        return $sorted;
    }
}

if (!function_exists('cms_footer_builder_render_menu_column_html')) {
    /**
     * @param string              $skey
     * @param string              $title
     * @param array<int,array{label:string,url:string}> $links
     * @param array<string,mixed> $cfg
     * @param string              $title_align
     * @param string              $links_align
     * @return string
     */
    function cms_footer_builder_render_menu_column_html($skey, $title, array $links, array $cfg, $title_align = 'left', $links_align = 'left')
    {
        $title = trim((string) $title);
        if ($title === '' && empty($links)) {
            return '';
        }
        if ($title === '') {
            $title = 'Links';
        }
        $title_color = cms_layout_builder_resolve_color($cfg, $skey . '_title_color', 'text_color');
        $link_color = cms_layout_builder_resolve_color($cfg, $skey . '_text_color', 'text_color');
        $menu_style = cms_layout_builder_merge_inline_style_attr(
            '--cms-ftr-menu-title:' . $title_color . ';--cms-ftr-menu-text:' . $link_color,
            $skey,
            $cfg
        );
        $section_font_key = $skey . '_font_size';
        if (!empty($cfg[$section_font_key])) {
            $section_font = cms_layout_builder_format_element_style_value('font_size', $cfg[$section_font_key]);
            if ($section_font !== '') {
                $menu_style .= ';--cms-ftr-menu-title-size:' . $section_font . ';--cms-ftr-menu-text-size:' . $section_font;
            }
        }
        $html = '<div class="cms-ws-footer__menu cms-ws-footer__menu--' . htmlspecialchars($skey, ENT_QUOTES, 'UTF-8') . ' '
            . cms_footer_builder_align_class($links_align, 'cms-ws-footer__menu') . '" style="' . htmlspecialchars($menu_style, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<h4 class="cms-ws-footer__menu-title ' . cms_footer_builder_align_class($title_align, 'cms-ws-footer__menu-title') . '">'
            . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h4>';
        $html .= '<ul class="cms-ws-footer__menu-list ' . cms_footer_builder_align_class($links_align, 'cms-ws-footer__menu-list') . '">';
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            $label = isset($link['label']) ? trim((string) $link['label']) : '';
            $raw_url = isset($link['url']) ? trim((string) $link['url']) : '';
            if ($label === '' && $raw_url === '') {
                continue;
            }
            if ($label === '') {
                $label = 'Link';
            }
            $url = cms_footer_builder_resolve_public_url($raw_url);
            $html .= '<li><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a></li>';
        }
        $html .= '</ul></div>';
        return $html;
    }
}

if (!function_exists('cms_footer_builder_format_plain_text_html')) {
    /**
     * Plain-text footer fields (tagline, descriptions): strip pasted HTML, then escape.
     *
     * @param string $text
     * @return string
     */
    function cms_footer_builder_format_plain_text_html($text)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }
        if (strpos($text, '<') !== false) {
            $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = trim($text);
        }
        if ($text === '') {
            return '';
        }
        return nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }
}

if (!function_exists('cms_layout_builder_is_legacy_placeholder_logo')) {
    /**
     * @param string $path
     * @return bool
     */
    function cms_layout_builder_is_legacy_placeholder_logo($path)
    {
        $path = strtolower(trim(str_replace('\\', '/', (string) $path)));
        return in_array($path, array('webshop/logo.png', 'logo.png'), true);
    }
}

if (!function_exists('cms_footer_builder_collect_fragments')) {
    /**
     * Build HTML fragments keyed by footer element id.
     *
     * @param array<string,mixed> $cfg
     * @param string              $uploads_base
     * @return array<string,string>
     */
    function cms_footer_builder_collect_fragments(array $cfg, $uploads_base = '')
    {
        $uploads_base = rtrim((string) $uploads_base, '/') . '/';
        $fragments = array();

        if (!empty($cfg['show_logo'])) {
            $logo_url = cms_header_builder_logo_home_url();
            $logo_src = '';
            $logo_image = trim((string) (isset($cfg['logo_image']) ? $cfg['logo_image'] : ''));
            if ($logo_image !== '' && function_exists('cms_layout_builder_is_legacy_placeholder_logo')
                && cms_layout_builder_is_legacy_placeholder_logo($logo_image)) {
                $logo_image = '';
            }
            if ($logo_image !== '') {
                $logo_src = cms_layout_builder_uploads_preview_url($logo_image, $uploads_base);
                $logo_src = htmlspecialchars($logo_src, ENT_QUOTES, 'UTF-8');
            }
            $logo_style = htmlspecialchars(cms_layout_builder_build_element_style_attr_from_cfg($cfg, 'logo'), ENT_QUOTES, 'UTF-8');
            $logo_style_attr = $logo_style !== '' ? ' style="' . $logo_style . '"' : '';
            if ($logo_src !== '') {
                $fragments['logo'] = '<a href="' . htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8') . '" class="cms-ws-footer__brand-link"><img src="' . $logo_src . '" alt="Logo" class="cms-ws-footer__logo"' . $logo_style_attr . '></a>';
            } else {
                $fragments['logo'] = '<a href="' . htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8') . '" class="cms-ws-footer__brand-link"><span class="cms-ws-footer__logo-placeholder"' . $logo_style_attr . '>Logo</span></a>';
            }
        }

        if (!empty($cfg['show_tagline']) && $cfg['tagline'] !== '') {
            $tagline_align = cms_layout_builder_sanitize_align(isset($cfg['tagline_align']) ? $cfg['tagline_align'] : '', 'left');
            $tagline_style = htmlspecialchars(cms_layout_builder_build_element_style_attr_from_cfg($cfg, 'tagline'), ENT_QUOTES, 'UTF-8');
            $tagline_style_attr = $tagline_style !== '' ? ' style="' . $tagline_style . '"' : '';
            $fragments['tagline'] = '<div class="cms-ws-footer__tagline ' . cms_footer_builder_align_class($tagline_align, 'cms-ws-footer__tagline') . '"' . $tagline_style_attr . '>'
                . cms_footer_builder_format_plain_text_html($cfg['tagline']) . '</div>';
        }

        if (!empty($cfg['show_social'])) {
            $social_align = cms_layout_builder_sanitize_align(isset($cfg['social_align']) ? $cfg['social_align'] : '', 'left');
            $social_inner = cms_footer_builder_render_social_html($cfg, $uploads_base);
            $social_style = htmlspecialchars(cms_layout_builder_build_element_style_attr_from_cfg($cfg, 'social'), ENT_QUOTES, 'UTF-8');
            if ($social_style !== '') {
                $social_inner = str_replace(
                    'class="cms-ws-footer__social"',
                    'class="cms-ws-footer__social" style="' . $social_style . '"',
                    $social_inner
                );
            }
            $fragments['social'] = '<div class="' . cms_footer_builder_align_class($social_align, 'cms-ws-footer__social-wrap') . '">'
                . $social_inner . '</div>';
        }

        if (!empty($cfg['show_newsletter'])) {
            $nl_title = isset($cfg['newsletter_title']) ? trim((string) $cfg['newsletter_title']) : 'Stay Updated';
            $nl_desc = isset($cfg['newsletter_desc']) ? trim((string) $cfg['newsletter_desc']) : '';
            $nl_placeholder = isset($cfg['newsletter_placeholder']) ? trim((string) $cfg['newsletter_placeholder']) : 'Enter your email';
            $nl_align = cms_layout_builder_sanitize_align(isset($cfg['newsletter_align']) ? $cfg['newsletter_align'] : '', 'left');
            $nl_style = htmlspecialchars(cms_layout_builder_build_element_style_attr_from_cfg($cfg, 'newsletter'), ENT_QUOTES, 'UTF-8');
            $nl_style_attr = $nl_style !== '' ? ' style="' . $nl_style . '"' : '';
            $nl_html = '<div class="cms-ws-footer__newsletter ' . cms_footer_builder_align_class($nl_align, 'cms-ws-footer__newsletter') . '"' . $nl_style_attr . '">';
            if ($nl_title !== '') {
                $nl_html .= '<h4 class="cms-ws-footer__newsletter-title">' . htmlspecialchars($nl_title, ENT_QUOTES, 'UTF-8') . '</h4>';
            }
            if ($nl_desc !== '') {
                $nl_html .= '<p class="cms-ws-footer__newsletter-desc">' . cms_footer_builder_format_plain_text_html($nl_desc) . '</p>';
            }
            $nl_html .= '<form class="cms-ws-footer__newsletter-form" data-newsletter-subscribe-form="1" novalidate>';
            $nl_html .= '<input type="email" name="email" autocomplete="email" placeholder="' . htmlspecialchars($nl_placeholder, ENT_QUOTES, 'UTF-8') . '" required class="cms-ws-footer__newsletter-input" aria-label="Email address">';
            $nl_html .= '<button type="submit" class="cms-ws-footer__newsletter-btn" aria-label="Subscribe"><i class="fa fa-arrow-right"></i></button>';
            $nl_html .= '</form>';
            $nl_html .= '<p class="cms-ws-footer__newsletter-feedback" role="status" aria-live="polite" hidden></p></div>';
            $fragments['newsletter'] = $nl_html;
        }

        foreach (cms_footer_builder_get_sections($cfg) as $sec) {
            $section_title = isset($sec['title']) ? trim((string) $sec['title']) : '';
            $section_links = isset($sec['links']) && is_array($sec['links']) ? $sec['links'] : array();
            if ($section_title === '' && empty($section_links)) {
                continue;
            }
            $skey = cms_footer_builder_section_storage_key(isset($sec['id']) ? $sec['id'] : '');
            $show_key = 'show_' . $skey;
            $sec_show = !isset($sec['show']) || !empty($sec['show']);
            $flat_show = !isset($cfg[$show_key]) || !empty($cfg[$show_key]);
            if (!$sec_show || !$flat_show) {
                continue;
            }
            $fragments[$skey] = cms_footer_builder_render_menu_column_html(
                $skey,
                $section_title,
                $section_links,
                $cfg,
                isset($sec['title_align']) ? $sec['title_align'] : 'left',
                isset($sec['links_align']) ? $sec['links_align'] : 'left'
            );
        }

        if (function_exists('cms_get_footer_nav_menus_from_cms_pages')) {
            foreach (cms_get_footer_nav_menus_from_cms_pages() as $group_id => $menu) {
                $mkey = 'cms_menu_' . (int) $group_id;
                $show_key = 'show_' . $mkey;
                if (isset($cfg[$show_key]) && empty($cfg[$show_key])) {
                    continue;
                }
                if (empty($menu['links'])) {
                    continue;
                }
                $title_align = cms_layout_builder_sanitize_align(isset($cfg[$mkey . '_title_align']) ? $cfg[$mkey . '_title_align'] : '', 'left');
                $links_align = cms_layout_builder_sanitize_align(isset($cfg[$mkey . '_align']) ? $cfg[$mkey . '_align'] : '', 'left');
                $fragments[$mkey] = cms_footer_builder_render_menu_column_html(
                    $mkey,
                    isset($menu['title']) ? (string) $menu['title'] : '',
                    $menu['links'],
                    $cfg,
                    $title_align,
                    $links_align
                );
            }
        }

        if (!empty($cfg['show_copyright']) && $cfg['copyright'] !== '') {
            $copy_align = cms_layout_builder_sanitize_align(isset($cfg['copyright_align']) ? $cfg['copyright_align'] : '', 'left');
            $copy_style = htmlspecialchars(cms_layout_builder_build_element_style_attr_from_cfg($cfg, 'copyright'), ENT_QUOTES, 'UTF-8');
            $copy_style_attr = $copy_style !== '' ? ' style="' . $copy_style . '"' : '';
            $fragments['copyright'] = '<div class="cms-ws-footer__copy ' . cms_footer_builder_align_class($copy_align, 'cms-ws-footer__copy') . '"' . $copy_style_attr . '>'
                . htmlspecialchars(str_replace('{year}', date('Y'), $cfg['copyright']), ENT_QUOTES, 'UTF-8') . '</div>';
        }

        if (!empty($cfg['show_legal'])) {
            $legal_align = cms_layout_builder_sanitize_align(isset($cfg['legal_align']) ? $cfg['legal_align'] : '', 'right');
            $legal_html = cms_footer_builder_render_legal_html($cfg);
            if ($legal_html !== '') {
                $legal_style = htmlspecialchars(cms_layout_builder_build_element_style_attr_from_cfg($cfg, 'legal'), ENT_QUOTES, 'UTF-8');
                $legal_style_attr = $legal_style !== '' ? ' style="' . $legal_style . '"' : '';
                $fragments['legal'] = str_replace(
                    'class="cms-ws-footer__legal"',
                    'class="cms-ws-footer__legal ' . cms_footer_builder_align_class($legal_align, 'cms-ws-footer__legal') . '"' . $legal_style_attr,
                    $legal_html
                );
            }
        }

        return $fragments;
    }
}

if (!function_exists('cms_footer_builder_show_element_defs')) {
    /**
     * @param array<string,mixed> $cfg
     * @return array<int,array<string,mixed>>
     */
    function cms_footer_builder_show_element_defs(array $cfg = array())
    {
        $defs = array(
            array('key' => 'logo', 'show_key' => 'show_logo', 'label' => 'Logo', 'icon' => 'fa-picture-o', 'align_key' => 'logo_align', 'default' => 'left'),
            array('key' => 'tagline', 'show_key' => 'show_tagline', 'label' => 'Tagline', 'icon' => 'fa-quote-left', 'align_key' => 'tagline_align', 'default' => 'left'),
            array('key' => 'copyright', 'show_key' => 'show_copyright', 'label' => 'Copyright bar', 'icon' => 'fa-copyright', 'align_key' => 'copyright_align', 'default' => 'left'),
            array('key' => 'legal', 'show_key' => 'show_legal', 'label' => 'Legal links', 'icon' => 'fa-balance-scale', 'align_key' => 'legal_align', 'default' => 'right'),
            array('key' => 'social', 'show_key' => 'show_social', 'label' => 'Social Links', 'icon' => 'fa-share-alt', 'align_key' => 'social_align', 'default' => 'left'),
            array('key' => 'newsletter', 'show_key' => 'show_newsletter', 'label' => 'Newsletter', 'icon' => 'fa-envelope-o', 'align_key' => 'newsletter_align', 'default' => 'left'),
        );
        foreach (cms_footer_builder_get_sections($cfg) as $sec) {
            $skey = cms_footer_builder_section_storage_key($sec['id']);
            $label = isset($sec['title']) && trim((string) $sec['title']) !== ''
                ? trim((string) $sec['title'])
                : 'Footer section';
            $defs[] = array(
                'key'              => $skey,
                'show_key'         => 'show_' . $skey,
                'label'            => $label,
                'icon'             => 'fa-list',
                'align_key'        => $skey . '_align',
                'title_align_key'  => $skey . '_title_align',
                'default'          => 'left',
                'is_footer_section'=> true,
                'section_id'       => $sec['id'],
            );
        }
        if (function_exists('cms_get_footer_nav_menus_from_cms_pages')) {
            foreach (cms_get_footer_nav_menus_from_cms_pages() as $group_id => $menu) {
                $mkey = 'cms_menu_' . (int) $group_id;
                $label = isset($menu['title']) && trim((string) $menu['title']) !== ''
                    ? trim((string) $menu['title'])
                    : ('CMS menu ' . (int) $group_id);
                $defs[] = array(
                    'key'             => $mkey,
                    'show_key'        => 'show_' . $mkey,
                    'label'           => $label . ' (CMS)',
                    'icon'            => 'fa-file-text-o',
                    'align_key'       => $mkey . '_align',
                    'title_align_key' => $mkey . '_title_align',
                    'default'         => 'left',
                    'is_cms_menu'     => true,
                    'group_id'        => (int) $group_id,
                );
            }
        }
        return $defs;
    }
}

if (!function_exists('cms_footer_nav_resolve_group_page')) {
    /**
     * Load CMS page row for a footer column header (any status — title only).
     *
     * @param int $group_id
     * @return array<string,mixed>|null
     */
    function cms_footer_nav_resolve_group_page($group_id)
    {
        $group_id = (int) $group_id;
        if ($group_id <= 0) {
            return null;
        }
        $CI =& get_instance();
        if (!isset($CI->cms_model)) {
            $CI->load->model('cms_model');
        }
        if (isset($CI->cms_model) && method_exists($CI->cms_model, 'getPageById')) {
            $row = $CI->cms_model->getPageById($group_id);
            if (is_array($row) && !empty($row)) {
                return $row;
            }
        }
        if (!isset($CI->cms_pages_model) && file_exists(APPPATH . 'models/cms_admin/Cms_admin_pages_model.php')) {
            $CI->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_model');
        }
        if (isset($CI->cms_pages_model) && method_exists($CI->cms_pages_model, 'getPageById')) {
            $row = $CI->cms_pages_model->getPageById($group_id);
            if (is_array($row) && !empty($row)) {
                return $row;
            }
        }
        return null;
    }
}

if (!function_exists('cms_footer_nav_resolve_group_title')) {
    /**
     * Dynamic footer section name from parent/top-level CMS page.
     *
     * @param int                             $group_id
     * @param array<int,array<string,mixed>>  $all_pages_by_id
     * @return string
     */
    function cms_footer_nav_resolve_group_title($group_id, array $all_pages_by_id = array())
    {
        $group_id = (int) $group_id;
        if ($group_id > 0 && isset($all_pages_by_id[$group_id]['page_name'])) {
            $name = trim((string) $all_pages_by_id[$group_id]['page_name']);
            if ($name !== '') {
                return $name;
            }
        }
        $row = cms_footer_nav_resolve_group_page($group_id);
        if (is_array($row) && !empty($row['page_name'])) {
            return trim((string) $row['page_name']);
        }
        return $group_id > 0 ? ('Section ' . $group_id) : 'Links';
    }
}

if (!function_exists('cms_footer_builder_align_class')) {
    /**
     * @param string $align
     * @param string $prefix e.g. cms-ws-footer__menu
     * @return string
     */
    function cms_footer_builder_align_class($align, $prefix)
    {
        $align = cms_layout_builder_sanitize_align($align, 'left');
        $prefix = trim((string) $prefix);
        if ($prefix === '') {
            return '';
        }
        return $prefix . '--align-' . $align;
    }
}

if (!function_exists('cms_footer_nav_menu_sort_value')) {
    /**
     * @param array<string,mixed> $row
     * @return int
     */
    function cms_footer_nav_menu_sort_value(array $row)
    {
        if (isset($row['submenu_order']) && (int) $row['submenu_order'] > 0) {
            return (int) $row['submenu_order'];
        }
        if (isset($row['nav_order']) && (int) $row['nav_order'] > 0) {
            return (int) $row['nav_order'];
        }
        return 9999;
    }
}

if (!function_exists('cms_get_footer_nav_menus_from_cms_pages')) {
    /**
     * Build footer menu columns (Swasthe-style): each top-level CMS page is a column;
     * submenu pages become links under their parent's column.
     *
     * @return array<int,array{title:string,links:array<int,array{label:string,url:string}>,nav_order:int}>
     */
    function cms_get_footer_nav_menus_from_cms_pages()
    {
        $CI =& get_instance();
        $pages = array();
        if (!isset($CI->cms_model)) {
            $CI->load->model('cms_model');
        }
        if (isset($CI->cms_model) && method_exists($CI->cms_model, 'getPublishedPages')) {
            $pages = $CI->cms_model->getPublishedPages(array('static', 'category'), 'footer');
        } elseif (!isset($CI->cms_pages_model)) {
            if (file_exists(APPPATH . 'models/cms_admin/Cms_admin_pages_model.php')) {
                $CI->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_model');
                if (method_exists($CI->cms_pages_model, 'getAdminPages')) {
                    $all = $CI->cms_pages_model->getAdminPages();
                    foreach ($all as $p) {
                        if (isset($p['status']) && $p['status'] === 'published' && !empty($p['show_in_footer'])) {
                            $pages[] = $p;
                        }
                    }
                }
            }
        }

        $all_pages_by_id = array();
        if (isset($CI->cms_model) && method_exists($CI->cms_model, 'getPublishedPages')) {
            foreach ($CI->cms_model->getPublishedPages(null, null) as $p) {
                $all_pages_by_id[(int) $p['id']] = $p;
            }
        }
        foreach ($pages as $p) {
            $id = isset($p['id']) ? (int) $p['id'] : 0;
            if ($id > 0) {
                $all_pages_by_id[$id] = $p;
            }
        }

        $column_ids = array();
        foreach ($pages as $p) {
            $id = isset($p['id']) ? (int) $p['id'] : 0;
            $parent_id = isset($p['parent_page_id']) ? (int) $p['parent_page_id'] : 0;
            if ($id <= 0) {
                continue;
            }
            $column_ids[$parent_id > 0 ? $parent_id : $id] = true;
        }

        foreach (array_keys($column_ids) as $gid) {
            $gid = (int) $gid;
            if ($gid > 0) {
                $resolved = cms_footer_nav_resolve_group_page($gid);
                if (is_array($resolved)) {
                    $all_pages_by_id[$gid] = $resolved;
                }
            }
        }

        $menus = array();
        foreach (array_keys($column_ids) as $group_id) {
            $group_id = (int) $group_id;
            $header = isset($all_pages_by_id[$group_id]) ? $all_pages_by_id[$group_id] : cms_footer_nav_resolve_group_page($group_id);
            if (is_array($header) && $group_id > 0) {
                $all_pages_by_id[$group_id] = $header;
            }
            $title = cms_footer_nav_resolve_group_title($group_id, $all_pages_by_id);

            $child_rows = array();
            foreach ($pages as $p) {
                $id = isset($p['id']) ? (int) $p['id'] : 0;
                $parent_id = isset($p['parent_page_id']) ? (int) $p['parent_page_id'] : 0;
                if ($parent_id === $group_id) {
                    $child_rows[] = $p;
                }
            }

            usort($child_rows, function ($a, $b) {
                $cmp = cms_footer_nav_menu_sort_value($a) - cms_footer_nav_menu_sort_value($b);
                if ($cmp !== 0) {
                    return $cmp;
                }
                $an = isset($a['page_name']) ? (string) $a['page_name'] : '';
                $bn = isset($b['page_name']) ? (string) $b['page_name'] : '';
                return strcasecmp($an, $bn);
            });

            $links = array();
            foreach ($child_rows as $child) {
                $label = !empty($child['page_name']) ? (string) $child['page_name'] : 'Page';
                $links[] = array(
                    'label' => $label,
                    'url'   => cms_page_public_url(isset($child['url']) ? $child['url'] : ''),
                );
            }

            if (empty($links) && $header && !empty($header['show_in_footer'])) {
                $links[] = array(
                    'label' => !empty($header['page_name']) ? (string) $header['page_name'] : 'Page',
                    'url'   => cms_page_public_url(isset($header['url']) ? $header['url'] : ''),
                );
            }

            if (empty($links)) {
                continue;
            }

            $nav_order = $header ? cms_footer_nav_menu_sort_value($header) : 9999;
            $menus[$group_id] = array(
                'title'     => $title,
                'links'     => $links,
                'nav_order' => $nav_order,
            );
        }

        uasort($menus, function ($a, $b) {
            $cmp = (int) $a['nav_order'] - (int) $b['nav_order'];
            if ($cmp !== 0) {
                return $cmp;
            }
            return strcasecmp((string) $a['title'], (string) $b['title']);
        });

        return $menus;
    }
}

if (!function_exists('cms_header_builder_element_position_defs')) {
    function cms_header_builder_element_position_defs()
    {
        return cms_header_builder_show_element_defs();
    }
}

if (!function_exists('cms_header_builder_bar_align_to_element_aligns')) {
    /**
     * Map legacy bar_align preset to per-element aligns.
     *
     * @param string $bar_align
     * @return array<string,string> align_key => left|center|right
     */
    function cms_header_builder_bar_align_to_element_aligns($bar_align)
    {
        $bar_align = cms_layout_builder_sanitize_bar_align($bar_align);
        $out = array();
        foreach (cms_header_builder_element_position_defs() as $def) {
            $out[$def['align_key']] = $def['default'];
        }
        if ($bar_align === 'split') {
            $out['logo_align'] = 'left';
            $out['nav_align'] = 'center';
            foreach (array('phone_align', 'search_align', 'wishlist_align', 'cart_align', 'account_align') as $k) {
                $out[$k] = 'right';
            }
            return $out;
        }
        $grouped = ($bar_align === 'center' || $bar_align === 'right') ? $bar_align : 'left';
        foreach (cms_header_builder_element_position_defs() as $def) {
            if ($def['align_key'] === 'promo_align') {
                continue;
            }
            $out[$def['align_key']] = $grouped;
        }
        return $out;
    }
}

if (!function_exists('cms_header_builder_apply_element_aligns')) {
    /**
     * @param array<string,mixed> $in
     * @param array<string,mixed> $defaults from cms_header_builder_default_config()
     * @return array<string,string>
     */
    function cms_header_builder_apply_element_aligns(array $in, array $defaults)
    {
        $has_per_element = false;
        foreach (cms_header_builder_element_position_defs() as $def) {
            $k = $def['align_key'];
            if (isset($in[$k]) && trim((string) $in[$k]) !== '') {
                $has_per_element = true;
                break;
            }
        }

        $mapped = array();
        if (!$has_per_element && isset($in['bar_align'])) {
            $mapped = cms_header_builder_bar_align_to_element_aligns($in['bar_align']);
        }

        $out = array();
        foreach (cms_header_builder_element_position_defs() as $def) {
            $k = $def['align_key'];
            $fallback = isset($mapped[$k]) ? $mapped[$k] : (isset($defaults[$k]) ? $defaults[$k] : $def['default']);
            $out[$k] = cms_layout_builder_sanitize_align(isset($in[$k]) ? $in[$k] : '', $fallback);
        }
        return $out;
    }
}

if (!function_exists('cms_layout_builder_sanitize_color_value')) {
    /**
     * @param string $value
     * @param string $fallback
     * @return string
     */
    function cms_layout_builder_sanitize_color_value($value, $fallback = '#ffffff')
    {
        $value = trim((string) $value);
        if (strtolower($value) === 'transparent') {
            return 'transparent';
        }
        if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $value)) {
            return $value;
        }
        $fallback = trim((string) $fallback);
        if (strtolower($fallback) === 'transparent') {
            return 'transparent';
        }
        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $fallback) ? $fallback : '#ffffff';
    }
}

if (!function_exists('cms_layout_builder_resolve_color')) {
    /**
     * Per-element color with fallback to a global layout color key.
     *
     * @param array<string,mixed> $cfg
     * @param string              $specific_key e.g. promo_bg_color
     * @param string              $fallback_key e.g. accent_color
     * @return string
     */
    function cms_layout_builder_resolve_color(array $cfg, $specific_key, $fallback_key)
    {
        if (isset($cfg[$specific_key]) && trim((string) $cfg[$specific_key]) !== '') {
            return cms_layout_builder_sanitize_color_value($cfg[$specific_key], '');
        }
        if ($fallback_key !== '' && isset($cfg[$fallback_key])) {
            return cms_layout_builder_sanitize_color_value($cfg[$fallback_key], '#ffffff');
        }
        return '#ffffff';
    }
}

if (!function_exists('cms_layout_builder_is_warm_accent_bg')) {
    /**
     * @param string $bg_color
     * @return bool
     */
    function cms_layout_builder_is_warm_accent_bg($bg_color)
    {
        $bg = strtolower(preg_replace('/\s+/', '', (string) $bg_color));
        if ($bg === '') {
            return false;
        }
        foreach (array('#b9860b', '#db1901', 'b9860b', 'db1901') as $token) {
            if (strpos($bg, $token) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('cms_layout_builder_button_label_on_bg')) {
    /**
     * @param string $bg_color
     * @param string $configured_text
     * @return string
     */
    function cms_layout_builder_button_label_on_bg($bg_color, $configured_text)
    {
        if (cms_layout_builder_is_warm_accent_bg($bg_color)) {
            return '#ffffff';
        }
        return trim((string) $configured_text) !== '' ? (string) $configured_text : '#ffffff';
    }
}

if (!function_exists('cms_layout_builder_color_picker_value')) {
    /**
     * Value for &lt;input type="color"&gt; (no transparent).
     *
     * @param array<string,mixed> $cfg
     * @param string              $key
     * @param string              $fallback_key
     * @param string              $default_hex
     * @return string
     */
    function cms_layout_builder_color_picker_value(array $cfg, $key, $fallback_key, $default_hex = '#ffffff')
    {
        $resolved = cms_layout_builder_resolve_color($cfg, $key, $fallback_key);
        if ($resolved === 'transparent') {
            return $default_hex;
        }
        return $resolved;
    }
}

if (!function_exists('cms_layout_builder_element_color_spec')) {
    /**
     * Color fields for one header/footer element.
     *
     * @param string $element_key
     * @param string $section     header|footer
     * @return array<int,array{key:string,label:string,fallback:string}>
     */
    function cms_layout_builder_element_color_spec($element_key, $section = 'header')
    {
        $element_key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $element_key));
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';

        if ($section === 'footer' && (strpos($element_key, 'section_') === 0 || strpos($element_key, 'cms_menu_') === 0 || strpos($element_key, 'menu_') === 0)) {
            return array(
                array('key' => $element_key . '_title_color', 'label' => 'Title', 'fallback' => 'text_color'),
                array('key' => $element_key . '_text_color', 'label' => 'Links', 'fallback' => 'text_color'),
            );
        }

        static $header_specs = null;
        static $footer_specs = null;
        if ($header_specs === null) {
            $header_specs = array(
                'promo' => array(
                    array('key' => 'promo_bg_color', 'label' => 'Bar bg', 'fallback' => 'accent_color'),
                    array('key' => 'promo_text_color', 'label' => 'Text', 'fallback' => 'text_color'),
                ),
                'nav' => array(
                    array('key' => 'nav_text_color', 'label' => 'Links', 'fallback' => 'text_color'),
                    array('key' => 'nav_active_color', 'label' => 'Underline', 'fallback' => 'button_bg_color'),
                ),
                'phone' => array(
                    array('key' => 'phone_text_color', 'label' => 'Text', 'fallback' => 'text_color'),
                ),
                'search' => array(
                    array('key' => 'search_text_color', 'label' => 'Icon', 'fallback' => 'text_color'),
                    array('key' => 'search_bg_color', 'label' => 'Icon bg', 'fallback' => 'icon_bg'),
                ),
                'wishlist' => array(
                    array('key' => 'wishlist_text_color', 'label' => 'Icon', 'fallback' => 'text_color'),
                    array('key' => 'wishlist_bg_color', 'label' => 'Icon bg', 'fallback' => 'icon_bg'),
                ),
                'cart' => array(
                    array('key' => 'cart_text_color', 'label' => 'Icon', 'fallback' => 'text_color'),
                    array('key' => 'cart_bg_color', 'label' => 'Icon bg', 'fallback' => 'icon_bg'),
                ),
                'account' => array(
                    array('key' => 'account_text_color', 'label' => 'Icon', 'fallback' => 'text_color'),
                    array('key' => 'account_bg_color', 'label' => 'Icon bg', 'fallback' => 'icon_bg'),
                ),
                'button' => array(
                    array('key' => 'button_bg_color', 'label' => 'Bg', 'fallback' => 'accent_color'),
                    array('key' => 'button_hover_color', 'label' => 'Hover', 'fallback' => 'button_bg_color'),
                    array('key' => 'button_text_color', 'label' => 'Text', 'fallback' => 'text_color'),
                ),
            );
            $footer_specs = array(
                'logo' => array(),
                'tagline' => array(
                    array('key' => 'tagline_text_color', 'label' => 'Text', 'fallback' => 'text_color'),
                ),
                'copyright' => array(
                    array('key' => 'copyright_text_color', 'label' => 'Text', 'fallback' => 'text_color'),
                ),
                'social' => array(
                    array('key' => 'social_text_color', 'label' => 'Icons', 'fallback' => 'accent_color'),
                ),
                'newsletter' => array(
                    array('key' => 'newsletter_text_color', 'label' => 'Text', 'fallback' => 'text_color'),
                    array('key' => 'newsletter_btn_bg_color', 'label' => 'Button', 'fallback' => 'accent_color'),
                ),
                'legal' => array(
                    array('key' => 'legal_text_color', 'label' => 'Links', 'fallback' => 'text_color'),
                ),
            );
        }

        $map = ($section === 'footer') ? $footer_specs : $header_specs;
        return isset($map[$element_key]) ? $map[$element_key] : array();
    }
}

if (!function_exists('cms_layout_builder_collect_color_field_specs')) {
    /**
     * @param string $section header|footer
     * @return array<int,array{key:string,label:string,fallback:string}>
     */
    function cms_layout_builder_collect_color_field_specs($section, array $cfg = array())
    {
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $specs = array();
        $defs = ($section === 'footer') ? cms_footer_builder_show_element_defs($cfg) : cms_header_builder_show_element_defs();
        foreach ($defs as $def) {
            $ek = isset($def['key']) ? (string) $def['key'] : '';
            foreach (cms_layout_builder_element_color_spec($ek, $section) as $s) {
                $specs[] = $s;
            }
        }
        return $specs;
    }
}

if (!function_exists('cms_layout_builder_apply_element_colors')) {
    /**
     * @param array<string,mixed> $cfg
     * @param string              $section
     * @return array<string,mixed>
     */
    function cms_layout_builder_apply_element_colors(array $cfg, $section)
    {
        $defaults = ($section === 'footer') ? cms_footer_builder_default_config() : cms_header_builder_default_config();
        foreach (cms_layout_builder_collect_color_field_specs($section, $cfg) as $spec) {
            $key = $spec['key'];
            if (array_key_exists($key, $cfg)) {
                $cfg[$key] = cms_layout_builder_sanitize_color_value(
                    $cfg[$key],
                    cms_layout_builder_resolve_color($defaults, $key, $spec['fallback'])
                );
            }
        }
        return $cfg;
    }
}

if (!function_exists('cms_layout_builder_merge_element_colors_from_array')) {
    /**
     * Copy per-element color keys from POST/JSON into config before sanitize.
     *
     * @param array<string,mixed> $cfg
     * @param array<string,mixed> $source
     * @param string              $section header|footer
     * @return array<string,mixed>
     */
    function cms_layout_builder_merge_element_colors_from_array(array $cfg, array $source, $section)
    {
        $defaults = ($section === 'footer') ? cms_footer_builder_default_config() : cms_header_builder_default_config();
        foreach (cms_layout_builder_collect_color_field_specs($section, $cfg) as $spec) {
            $key = $spec['key'];
            if (!array_key_exists($key, $source)) {
                continue;
            }
            $posted = $source[$key];
            if ($posted === null || trim((string) $posted) === '') {
                continue;
            }
            $cfg[$key] = cms_layout_builder_sanitize_color_value(
                $posted,
                cms_layout_builder_resolve_color($defaults, $key, $spec['fallback'])
            );
        }
        return $cfg;
    }
}

if (!function_exists('cms_layout_builder_merge_element_colors_from_post')) {
    /**
     * @param array<string,mixed> $cfg
     * @param string              $section
     * @return array<string,mixed>
     */
    function cms_layout_builder_merge_element_colors_from_post(array $cfg, $section)
    {
        $CI =& get_instance();
        $posted = array();
        foreach (cms_layout_builder_collect_color_field_specs($section, $cfg) as $spec) {
            $key = $spec['key'];
            $val = $CI->input->post($key);
            if ($val !== false && $val !== null) {
                $posted[$key] = $val;
            }
        }
        return cms_layout_builder_merge_element_colors_from_array($cfg, $posted, $section);
    }
}

if (!function_exists('cms_layout_builder_element_style_field_map')) {
    /**
     * @return array<string,string>
     */
    function cms_layout_builder_element_style_field_map()
    {
        return array(
            'font_size'      => 'font-size',
            'width'          => 'width',
            'height'         => 'height',
            'padding_top'    => 'padding-top',
            'padding_bottom' => 'padding-bottom',
            'position'       => 'position',
            'top'            => 'top',
            'bottom'         => 'bottom',
            'left'           => 'left',
            'right'          => 'right',
        );
    }
}

if (!function_exists('cms_layout_builder_footer_element_keys')) {
    /**
     * Footer element ids that support per-element style fields.
     *
     * @param array<string,mixed> $cfg
     * @return array<int,string>
     */
    function cms_layout_builder_footer_element_keys(array $cfg = array())
    {
        $keys = array('logo', 'tagline', 'copyright', 'legal', 'social', 'newsletter');
        foreach (cms_footer_builder_get_sections($cfg) as $sec) {
            if (!is_array($sec) || empty($sec['id'])) {
                continue;
            }
            $keys[] = cms_footer_builder_section_storage_key($sec['id']);
        }
        if (function_exists('cms_get_footer_nav_menus_from_cms_pages')) {
            foreach (cms_get_footer_nav_menus_from_cms_pages() as $group_id => $menu) {
                $keys[] = 'cms_menu_' . (int) $group_id;
            }
        }
        return $keys;
    }
}

if (!function_exists('cms_layout_builder_format_element_style_value')) {
    /**
     * @param string $field
     * @param mixed  $val
     * @return string
     */
    function cms_layout_builder_format_element_style_value($field, $val)
    {
        $val = trim((string) $val);
        if ($val === '') {
            return '';
        }
        if ($field === 'position') {
            return $val;
        }
        if (in_array($field, array('font_size', 'width', 'height', 'padding_top', 'padding_bottom', 'top', 'bottom', 'left', 'right'), true)
            && is_numeric($val)) {
            return $val . 'px';
        }
        return $val;
    }
}

if (!function_exists('cms_layout_builder_build_element_style_attr_from_cfg')) {
    /**
     * Inline style for one element (menu columns + dynamic sections).
     *
     * @param array<string,mixed> $cfg
     * @param string              $element_key
     * @return string
     */
    function cms_layout_builder_build_element_style_attr_from_cfg(array $cfg, $element_key)
    {
        $element_key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $element_key));
        if (!cms_layout_builder_element_styles_enabled($cfg, $element_key)) {
            return '';
        }
        $parts = array();
        foreach (cms_layout_builder_element_style_field_map() as $sf => $css_prop) {
            $k = $element_key . '_' . $sf;
            if (empty($cfg[$k])) {
                continue;
            }
            $formatted = cms_layout_builder_format_element_style_value($sf, $cfg[$k]);
            if ($formatted !== '') {
                $parts[] = $css_prop . ':' . $formatted;
            }
        }
        return implode(';', $parts);
    }
}

if (!function_exists('cms_layout_builder_merge_inline_style_attr')) {
    /**
     * @param string              $existing
     * @param string              $element_key
     * @param array<string,mixed> $cfg
     * @return string
     */
    function cms_layout_builder_merge_inline_style_attr($existing, $element_key, array $cfg)
    {
        $extra = cms_layout_builder_build_element_style_attr_from_cfg($cfg, $element_key);
        if ($extra === '') {
            return (string) $existing;
        }
        $existing = trim((string) $existing);
        if ($existing !== '' && substr($existing, -1) !== ';') {
            $existing .= ';';
        }
        return $existing . $extra;
    }
}

if (!function_exists('cms_layout_builder_append_element_style_css_vars')) {
    /**
     * @param array<string,mixed> $cfg
     * @param array<int,string>   $element_keys
     * @param string              $var_prefix e.g. cms-ftr-el
     * @param array<int,string>   $parts
     */
    function cms_layout_builder_append_element_style_css_vars(array $cfg, array $element_keys, $var_prefix, array &$parts)
    {
        foreach ($element_keys as $ek) {
            $ek = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $ek));
            if (!cms_layout_builder_element_styles_enabled($cfg, $ek)) {
                continue;
            }
            foreach (cms_layout_builder_element_style_field_map() as $sf => $css_prop) {
                $k = $ek . '_' . $sf;
                if (empty($cfg[$k])) {
                    continue;
                }
                $formatted = cms_layout_builder_format_element_style_value($sf, $cfg[$k]);
                if ($formatted === '') {
                    continue;
                }
                $var_name = '--' . preg_replace('/[^a-z0-9-]/', '', $var_prefix) . '-' . str_replace('_', '-', $ek) . '-' . $css_prop;
                $parts[] = $var_name . ':' . htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

if (!function_exists('cms_layout_builder_footer_element_style_css_rules')) {
    /**
     * CSS rules mapping footer style vars to static element selectors.
     *
     * @return string
     */
    function cms_layout_builder_footer_element_style_css_rules()
    {
        $rules = array(
            'logo'        => '.cms-ws-footer__logo',
            'tagline'     => '.cms-ws-footer__tagline',
            'copyright'   => '.cms-ws-footer__copy',
            'legal'       => '.cms-ws-footer__legal',
            'social'      => '.cms-ws-footer__social',
            'newsletter'  => '.cms-ws-footer__newsletter',
        );
        $css = '';
        foreach ($rules as $ek => $selector) {
            $decl = array();
            foreach (cms_layout_builder_element_style_field_map() as $sf => $css_prop) {
                $var = '--cms-ftr-el-' . str_replace('_', '-', $ek) . '-' . $css_prop;
                $decl[] = $css_prop . ':var(' . $var . ',auto)';
            }
            $css .= $selector . '{' . implode(';', $decl) . '}';
        }
        return $css;
    }
}

if (!function_exists('cms_layout_builder_merge_element_styles_from_post')) {
    /**
     * @param array<string,mixed> $cfg
     * @param string              $section header|footer
     * @return array<string,mixed>
     */
    function cms_layout_builder_merge_element_styles_from_post(array $cfg, $section)
    {
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $CI =& get_instance();
        $style_fields = array_keys(cms_layout_builder_element_style_field_map());
        $elements = ($section === 'footer')
            ? cms_layout_builder_footer_element_keys($cfg)
            : array('promo', 'logo', 'nav', 'phone', 'search', 'wishlist', 'cart', 'account', 'button');
        foreach ($elements as $ek) {
            $enabled_key = cms_layout_builder_element_styles_enabled_key($ek);
            $enabled = cms_layout_builder_flag_from_post($enabled_key);
            $cfg[$enabled_key] = $enabled ? 1 : 0;
            if (!$enabled) {
                $cfg = cms_layout_builder_clear_element_style_fields($cfg, $ek);
                continue;
            }
            foreach ($style_fields as $sf) {
                $k = $ek . '_' . $sf;
                $val = $CI->input->post($k);
                if ($val !== false && $val !== null) {
                    $cfg[$k] = trim((string) $val);
                }
            }
        }
        return $cfg;
    }
}

if (!function_exists('cms_layout_builder_color_key_to_css_var')) {
    /**
     * promo_bg_color → --cms-hdr-el-promo-bg
     *
     * @param string $config_key
     * @param string $prefix     cms-hdr-el|cms-ftr-el
     * @return string
     */
    function cms_layout_builder_color_key_to_css_var($config_key, $prefix = 'cms-hdr-el')
    {
        $config_key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $config_key));
        $s = preg_replace('/_color$/', '', $config_key);
        $s = str_replace('_', '-', $s);
        return '--' . preg_replace('/[^a-z0-9-]/', '', $prefix) . '-' . $s;
    }
}

if (!function_exists('cms_layout_builder_build_element_color_style_vars')) {
    /**
     * @param array<string,mixed> $cfg
     * @param string              $section header|footer
     * @return string semicolon-separated --var:value pairs
     */
    function cms_layout_builder_build_element_color_style_vars(array $cfg, $section)
    {
        $prefix = ($section === 'footer') ? 'cms-ftr-el' : 'cms-hdr-el';
        $parts = array();
        foreach (cms_layout_builder_collect_color_field_specs($section, $cfg) as $spec) {
            $key = $spec['key'];
            if ($key === 'button_hover_color' && (!array_key_exists($key, $cfg) || trim((string) $cfg[$key]) === '')) {
                continue;
            }
            $val = cms_layout_builder_resolve_color($cfg, $key, $spec['fallback']);
            if ($val === '' || $val === 'transparent') {
                continue;
            }
            $var_name = cms_layout_builder_color_key_to_css_var($key, $prefix);
            $parts[] = $var_name . ':' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        }

        if ($section === 'header') {
            cms_layout_builder_append_element_style_css_vars(
                $cfg,
                array('promo', 'logo', 'nav', 'phone', 'search', 'wishlist', 'cart', 'account', 'button'),
                'cms-hdr-el',
                $parts
            );
        } elseif ($section === 'footer') {
            cms_layout_builder_append_element_style_css_vars(
                $cfg,
                cms_layout_builder_footer_element_keys($cfg),
                'cms-ftr-el',
                $parts
            );
        }

        return implode(';', $parts);
    }
}

if (!function_exists('cms_layout_builder_render_element_color_row')) {
    /**
     * @param array<string,mixed> $cfg
     * @param string              $element_key
     * @param string              $section
     * @param string              $css_class
     * @return string
     */
    function cms_layout_builder_render_element_color_row(array $cfg, $element_key, $section, $css_class = 'cms-lb-live')
    {
        $specs = cms_layout_builder_element_color_spec($element_key, $section);
        if (empty($specs)) {
            return '';
        }
        $defaults = ($section === 'footer') ? cms_footer_builder_default_config() : cms_header_builder_default_config();
        $html = '<div class="cms-lb-element-color-row"><span class="cms-lb-element-color-row__label"><i class="fa fa-eyedropper"></i> Colors</span><div class="cms-lb-element-color-row__pickers">';
        foreach ($specs as $spec) {
            $key = $spec['key'];
            $fb = isset($defaults[$spec['fallback']]) ? (string) $defaults[$spec['fallback']] : '#ffffff';
            $picker_default = ($spec['fallback'] === 'icon_bg') ? '#334155' : (($fb !== '' && $fb !== 'transparent') ? $fb : '#ffffff');
            $val = cms_layout_builder_color_picker_value($cfg, $key, $spec['fallback'], $picker_default);
            $live = ($section === 'footer') ? 'cms-lb-ft-color' : trim((string) $css_class);
            if ($live === '') {
                $live = 'cms-lb-live';
            }
            $html .= '<label class="cms-lb-element-color">' . htmlspecialchars($spec['label'], ENT_QUOTES, 'UTF-8')
                . ' <input type="color" name="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '"'
                . ' class="cms-lb-color ' . htmlspecialchars($live, ENT_QUOTES, 'UTF-8') . '" data-el-color="1" data-preview-key="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '"></label>';
        }
        return $html . '</div></div>';
    }
}

if (!function_exists('cms_layout_builder_element_styles_enabled_key')) {
    /**
     * @param string $element_key
     * @return string
     */
    function cms_layout_builder_element_styles_enabled_key($element_key)
    {
        $element_key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $element_key));
        return $element_key === '' ? '' : ($element_key . '_styles_enabled');
    }
}

if (!function_exists('cms_layout_builder_element_has_styles')) {
    /**
     * True when any per-element style field has a saved value (legacy fallback).
     *
     * @param array<string,mixed> $cfg
     * @param string              $element_key
     * @return bool
     */
    function cms_layout_builder_element_has_styles(array $cfg, $element_key)
    {
        $element_key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $element_key));
        if ($element_key === '') {
            return false;
        }
        foreach (array_keys(cms_layout_builder_element_style_field_map()) as $sf) {
            $k = $element_key . '_' . $sf;
            if (!empty($cfg[$k]) && trim((string) $cfg[$k]) !== '') {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('cms_layout_builder_element_styles_enabled')) {
    /**
     * Whether the Styles panel is enabled for an element (persisted on save).
     *
     * @param array<string,mixed> $cfg
     * @param string              $element_key
     * @return bool
     */
    function cms_layout_builder_element_styles_enabled(array $cfg, $element_key)
    {
        $flag = cms_layout_builder_element_styles_enabled_key($element_key);
        if ($flag === '') {
            return false;
        }
        if (array_key_exists($flag, $cfg)) {
            return cms_layout_normalize_flag($cfg[$flag]);
        }
        return cms_layout_builder_element_has_styles($cfg, $element_key);
    }
}

if (!function_exists('cms_layout_builder_clear_element_style_fields')) {
    /**
     * @param array<string,mixed> $cfg
     * @param string              $element_key
     * @return array<string,mixed>
     */
    function cms_layout_builder_clear_element_style_fields(array $cfg, $element_key)
    {
        $element_key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $element_key));
        if ($element_key === '') {
            return $cfg;
        }
        foreach (array_keys(cms_layout_builder_element_style_field_map()) as $sf) {
            $cfg[$element_key . '_' . $sf] = '';
        }
        return $cfg;
    }
}

if (!function_exists('cms_layout_builder_render_element_styles_row')) {
    /**
     * Per-element style fields with a checkbox on the Styles line to expand/collapse inputs.
     *
     * @param array<string,mixed> $cfg
     * @param string              $element_key
     * @param string              $css_class
     * @return string
     */
    function cms_layout_builder_render_element_styles_row(array $cfg, $element_key, $css_class = 'cms-lb-live')
    {
        $element_key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $element_key));
        $css_class = trim((string) $css_class);
        $enabled = cms_layout_builder_element_styles_enabled($cfg, $element_key);
        $enabled_key = cms_layout_builder_element_styles_enabled_key($element_key);
        $checked = $enabled ? ' checked' : '';
        $body_class = $enabled ? ' is-expanded' : '';

        $html = '<div class="cms-lb-element-pos-row cms-lb-element-pos-row--styles">'
            . '<input type="hidden" name="' . htmlspecialchars($enabled_key, ENT_QUOTES, 'UTF-8') . '" value="' . ($enabled ? '1' : '0') . '" class="cms-lb-styles-enabled-flag" data-style-for="' . htmlspecialchars($element_key, ENT_QUOTES, 'UTF-8') . '">'
            . '<div class="cms-lb-element-styles-head">'
            . '<label class="cms-lb-element-styles-toggle">'
            . '<input type="checkbox" class="cms-lb-styles-toggle" data-style-for="' . htmlspecialchars($element_key, ENT_QUOTES, 'UTF-8') . '"' . $checked . '>'
            . '<span class="cms-lb-element-pos-row__label"><i class="fa fa-paint-brush"></i> Styles</span>'
            . '</label>'
            . '</div>'
            . '<div class="cms-lb-element-styles-body' . $body_class . '">'
            . '<div class="cms-lb-element-color-row__pickers">';

        $fields = array(
            'font_size'      => 'Font size',
            'width'          => 'Width',
            'height'         => 'Height',
            'padding_top'    => 'Pad Top',
            'padding_bottom' => 'Pad Bot',
        );

        foreach ($fields as $prop => $label) {
            $key = $element_key . '_' . $prop;
            $val = isset($cfg[$key]) ? $cfg[$key] : '';
            $html .= '<label class="cms-lb-element-color">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                . ' <input type="text" name="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '"'
                . ' class="form-control input-sm ' . htmlspecialchars($css_class, ENT_QUOTES, 'UTF-8') . '" placeholder="auto" data-preview-key="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" style="width:60px; display:inline-block; margin-left:5px; height:24px; padding:2px 5px; font-size:11px;"></label>';
        }

        $pos_key = $element_key . '_position';
        $pos_val = isset($cfg[$pos_key]) ? $cfg[$pos_key] : '';
        $html .= '<label class="cms-lb-element-color">Position <select name="' . htmlspecialchars($pos_key, ENT_QUOTES, 'UTF-8') . '" class="form-control input-sm ' . htmlspecialchars($css_class, ENT_QUOTES, 'UTF-8') . '" data-preview-key="' . htmlspecialchars($pos_key, ENT_QUOTES, 'UTF-8') . '" style="width:70px; display:inline-block; margin-left:5px; height:24px; padding:2px; font-size:11px;">';
        $html .= '<option value="">Static</option>';
        $html .= '<option value="relative"' . ($pos_val === 'relative' ? ' selected' : '') . '>Relative</option>';
        $html .= '<option value="absolute"' . ($pos_val === 'absolute' ? ' selected' : '') . '>Absolute</option>';
        $html .= '</select></label>';

        $pos_fields = array('top' => 'Top', 'bottom' => 'Bot', 'left' => 'Left', 'right' => 'Right');
        foreach ($pos_fields as $prop => $label) {
            $key = $element_key . '_' . $prop;
            $val = isset($cfg[$key]) ? $cfg[$key] : '';
            $html .= '<label class="cms-lb-element-color">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                . ' <input type="text" name="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '"'
                . ' class="form-control input-sm ' . htmlspecialchars($css_class, ENT_QUOTES, 'UTF-8') . '" placeholder="auto" data-preview-key="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" style="width:50px; display:inline-block; margin-left:5px; height:24px; padding:2px 5px; font-size:11px;"></label>';
        }

        $html .= '</div></div></div>';
        return $html;
    }
}

if (!function_exists('cms_layout_builder_render_element_align_mini')) {
    /**
     * Compact L/C/R radios for one element row.
     *
     * @param string $name
     * @param string $current
     * @param string $css_class
     * @return string
     */
    function cms_layout_builder_render_element_align_mini($name, $current = 'left', $css_class = 'cms-lb-live')
    {
        $name = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $name));
        $current = cms_layout_builder_sanitize_align($current);
        $css_class = trim((string) $css_class);
        $html = '<div class="cms-lb-segment" role="group" aria-label="Alignment" data-align-name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
        foreach (array(
            'left'   => array('fa-align-left', 'Left'),
            'center' => array('fa-align-center', 'Center'),
            'right'  => array('fa-align-right', 'Right'),
        ) as $val => $meta) {
            $id = $name . '_' . $val;
            $checked = ($current === $val) ? ' checked' : '';
            $html .= '<label class="cms-lb-segment__opt' . ($current === $val ? ' is-active' : '') . '" for="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($meta[1], ENT_QUOTES, 'UTF-8') . '">'
                . '<input type="radio" id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . $val . '" class="cms-lb-align-input' . ($css_class !== '' ? ' ' . htmlspecialchars($css_class, ENT_QUOTES, 'UTF-8') : '') . '"' . $checked . '>'
                . '<i class="fa ' . htmlspecialchars($meta[0], ENT_QUOTES, 'UTF-8') . '"></i>'
                . '<span class="cms-lb-segment__text">' . htmlspecialchars($meta[1], ENT_QUOTES, 'UTF-8') . '</span></label>';
        }
        return $html . '</div>';
    }
}

if (!function_exists('cms_layout_builder_flag_from_post')) {
    /**
     * Read 0/1 layout-builder flag from POST (hidden field set by UI toggle).
     *
     * @param string $key e.g. show_logo
     * @return bool
     */
    function cms_layout_builder_flag_from_post($key)
    {
        $CI =& get_instance();
        $v = $CI->input->post($key);
        if ($v === null || $v === false || $v === '') {
            return false;
        }
        return ($v === '1' || $v === 1 || $v === true);
    }
}

if (!function_exists('cms_layout_builder_render_flag_switch')) {
    /**
     * Toggle switch UI (no native checkbox) + hidden 0/1 for form POST.
     *
     * @param string $name     field name e.g. show_logo
     * @param string $label    aria-label
     * @param bool   $on
     * @return string
     */
    function cms_layout_builder_render_flag_switch($name, $label, $on)
    {
        $name = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $name));
        $on = !empty($on);
        $label = trim((string) $label);
        return '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . ($on ? '1' : '0') . '" class="cms-lb-flag" data-flag="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">'
            . '<button type="button" class="cms-lb-header-el__switch' . ($on ? ' is-on' : '') . '" role="switch" aria-checked="' . ($on ? 'true' : 'false') . '" data-flag="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">'
            . '<span class="cms-lb-header-el__track" aria-hidden="true"></span></button>';
    }
}

if (!function_exists('cms_layout_builder_render_header_show_elements')) {
    /**
     * Show checkbox per element; position (L/C/R) appears directly below when checked.
     *
     * @param array<string,mixed> $cfg
     * @param string            $css_class
     * @return string
     */
    function cms_layout_builder_render_header_show_elements(array $cfg, $css_class = 'cms-lb-live')
    {
        $html = '<div class="cms-lb-header-elements">'
            . '<div class="cms-lb-preset-bar">'
            . '<button type="button" class="cms-lb-preset-btn" id="cmsLbPresetSplit">'
            . '<i class="fa fa-magic"></i> Quick setup: logo left · menu center · icons right</button>'
            . '</div>'
            . '<p class="cms-lb-header-elements__hint">Turn on each part, set alignment (left / center / right), optional colors, and tick <strong>Styles</strong> to edit font size, width, height, padding, and position per element. Empty colors use the global <strong>Colors</strong> section below.</p>'
            . '<div class="cms-lb-header-elements__list">';

        foreach (cms_header_builder_show_element_defs() as $def) {
            $show_key = $def['show_key'];
            $on = !empty($cfg[$show_key]);
            $align_key = $def['align_key'];
            $cur = isset($cfg[$align_key]) ? (string) $cfg[$align_key] : $def['default'];
            $vis = $on ? ' is-visible' : '';

            $html .= '<div class="cms-lb-header-el' . ($on ? ' is-active' : '') . '" data-show-field="' . htmlspecialchars($show_key, ENT_QUOTES, 'UTF-8') . '">';

            $html .= '<div class="cms-lb-header-el__head">'
                . cms_layout_builder_render_flag_switch($show_key, $def['label'], $on)
                . '<span class="cms-lb-header-el__meta" data-flag="' . htmlspecialchars($show_key, ENT_QUOTES, 'UTF-8') . '"><i class="fa ' . htmlspecialchars($def['icon'], ENT_QUOTES, 'UTF-8') . '"></i> '
                . '<span class="cms-lb-header-el__name">' . htmlspecialchars($def['label'], ENT_QUOTES, 'UTF-8') . '</span></span>'
                . '</div>';

            $html .= '<div class="cms-lb-element-pos-row' . $vis . '" data-pos-for="' . htmlspecialchars($show_key, ENT_QUOTES, 'UTF-8') . '">'
                . '<span class="cms-lb-element-pos-row__label"><i class="fa fa-arrows-h"></i> Align</span>'
                . cms_layout_builder_render_element_align_mini($align_key, $cur, $css_class)
                . '</div>';

            $color_row = cms_layout_builder_render_element_color_row($cfg, $def['key'], 'header', $css_class);
            if ($color_row !== '') {
                $html .= '<div class="cms-lb-element-pos-row cms-lb-element-pos-row--colors' . $vis . '">' . $color_row . '</div>';
            }
            
            $style_row = cms_layout_builder_render_element_styles_row($cfg, $def['key'], $css_class);
            if ($style_row !== '') {
                $html .= str_replace(
                    'cms-lb-element-pos-row cms-lb-element-pos-row--styles',
                    'cms-lb-element-pos-row cms-lb-element-pos-row--styles' . $vis,
                    $style_row
                );
            }

            if ($def['key'] === 'nav') {
                $nav_active_on = !isset($cfg['nav_active_underline']) || !empty($cfg['nav_active_underline']);
                $html .= '<div class="cms-lb-element-pos-row cms-lb-element-pos-row--nav-active' . $vis . '">'
                    . '<span class="cms-lb-element-pos-row__label"><i class="fa fa-minus"></i> Active page</span>'
                    . '<div class="cms-lb-element-pos-row__inline">'
                    . cms_layout_builder_render_flag_switch('nav_active_underline', 'Show active menu underline', $nav_active_on)
                    . '<span class="cms-lb-element-pos-row__hint-text">Underline current page in the menu bar</span>'
                    . '</div></div>';
            }

            $html .= '</div>';
        }

        return $html . '</div></div>';
    }
}

if (!function_exists('cms_layout_builder_render_footer_show_elements')) {
    /**
     * Show checkbox per footer element; position (L/C/R) appears directly below when checked.
     *
     * @param array<string,mixed> $cfg
     * @param string            $css_class
     * @return string
     */
    function cms_layout_builder_render_footer_show_elements(array $cfg, $css_class = 'cms-lb-ft-live')
    {
        $order_json = htmlspecialchars(json_encode(cms_footer_builder_get_element_order($cfg)), ENT_QUOTES, 'UTF-8');
        $html = '<div class="cms-lb-header-elements">'
            . '<p class="cms-lb-header-elements__hint">Drag <i class="fa fa-bars"></i> to reorder footer blocks. Toggle on/off, set alignment, colors, and tick <strong>Styles</strong> to edit sizing and position per element — same as the header tab. <strong>CMS columns</strong> appear when a page has <strong>Show In Footer</strong> on. Custom columns are in <em>Footer menu sections</em> below.</p>'
            . '<input type="hidden" name="footer_element_order" id="cmsFooterElementOrder" value="' . $order_json . '">'
            . '<div class="cms-lb-header-elements__list cms-lb-header-elements__list--sortable" id="cmsFooterElementsSortable">';

        foreach (cms_footer_builder_sort_element_defs(cms_footer_builder_show_element_defs($cfg), $cfg) as $def) {
            $show_key = $def['show_key'];
            if ((!empty($def['is_footer_section']) || !empty($def['is_cms_menu'])) && !isset($cfg[$show_key])) {
                $on = true;
            } else {
                $on = !empty($cfg[$show_key]);
            }
            $align_key = $def['align_key'];
            $cur = isset($cfg[$align_key]) ? (string) $cfg[$align_key] : $def['default'];
            $vis = $on ? ' is-visible' : '';
            $ek = isset($def['key']) ? (string) $def['key'] : '';

            $ek_attr = htmlspecialchars(isset($def['key']) ? (string) $def['key'] : '', ENT_QUOTES, 'UTF-8');
            $html .= '<div class="cms-lb-header-el' . ($on ? ' is-active' : '') . '" data-show-field="' . htmlspecialchars($show_key, ENT_QUOTES, 'UTF-8') . '" data-element-key="' . $ek_attr . '">';

            $html .= '<div class="cms-lb-header-el__head">'
                . '<span class="cms-lb-header-el__drag cms-drag-handle" title="Drag to reorder"><i class="fa fa-bars"></i></span>'
                . cms_layout_builder_render_flag_switch($show_key, $def['label'], $on)
                . '<span class="cms-lb-header-el__meta" data-flag="' . htmlspecialchars($show_key, ENT_QUOTES, 'UTF-8') . '"><i class="fa ' . htmlspecialchars($def['icon'], ENT_QUOTES, 'UTF-8') . '"></i> '
                . '<span class="cms-lb-header-el__name">' . htmlspecialchars($def['label'], ENT_QUOTES, 'UTF-8') . '</span></span>'
                . '</div>';

            if (!empty($def['is_footer_section']) || !empty($def['is_cms_menu'])) {
                $title_align_key = isset($def['title_align_key']) ? (string) $def['title_align_key'] : ($ek . '_title_align');
                $title_cur = isset($cfg[$title_align_key]) ? (string) $cfg[$title_align_key] : $cur;
                $html .= '<div class="cms-lb-element-pos-row' . $vis . '" data-pos-for="' . htmlspecialchars($show_key, ENT_QUOTES, 'UTF-8') . '">'
                    . '<span class="cms-lb-element-pos-row__label"><i class="fa fa-font"></i> Section title align</span>'
                    . cms_layout_builder_render_element_align_mini($title_align_key, $title_cur, $css_class)
                    . '</div>';
                $html .= '<div class="cms-lb-element-pos-row' . $vis . '" data-pos-for="' . htmlspecialchars($show_key, ENT_QUOTES, 'UTF-8') . '">'
                    . '<span class="cms-lb-element-pos-row__label"><i class="fa fa-link"></i> Links align</span>'
                    . cms_layout_builder_render_element_align_mini($align_key, $cur, $css_class)
                    . '</div>';
            } else {
                $html .= '<div class="cms-lb-element-pos-row' . $vis . '" data-pos-for="' . htmlspecialchars($show_key, ENT_QUOTES, 'UTF-8') . '">'
                    . '<span class="cms-lb-element-pos-row__label"><i class="fa fa-arrows-h"></i> Align</span>'
                    . cms_layout_builder_render_element_align_mini($align_key, $cur, $css_class)
                    . '</div>';
            }

            $color_row = cms_layout_builder_render_element_color_row($cfg, $ek, 'footer', $css_class);
            if ($color_row !== '') {
                $html .= '<div class="cms-lb-element-pos-row cms-lb-element-pos-row--colors' . $vis . '">' . $color_row . '</div>';
            }

            $style_row = cms_layout_builder_render_element_styles_row($cfg, $ek, $css_class);
            if ($style_row !== '') {
                $html .= str_replace(
                    'cms-lb-element-pos-row cms-lb-element-pos-row--styles',
                    'cms-lb-element-pos-row cms-lb-element-pos-row--styles' . $vis,
                    $style_row
                );
            }

            if (!empty($def['is_cms_menu'])) {
                $html .= '<p class="cms-hd-field-help" style="margin:4px 0 0;">Links from <a href="' . htmlspecialchars(site_url('cms_admin/pages'), ENT_QUOTES, 'UTF-8') . '">CMS Pages</a> (<strong>Show In Footer</strong>). Turn off in CMS to remove from this list.</p>';
            }

            $html .= '</div>';
        }

        return $html . '</div></div>';
    }
}

if (!function_exists('cms_layout_builder_render_social_items_editor')) {
    /**
     * @param array<string,mixed> $cfg
     * @param string              $upload_base
     * @return string
     */
    function cms_layout_builder_render_social_items_editor(array $cfg, $upload_base = '')
    {
        $items = cms_footer_builder_get_social_items($cfg);
        $upload_base = rtrim((string) $upload_base, '/');
        $html = '<div class="cms-lb-ft-social-items" id="cmsFooterSocialItems">';
        foreach ($items as $i => $item) {
            $id = isset($item['id']) ? (string) $item['id'] : ('soc_' . $i);
            $title = isset($item['title']) ? (string) $item['title'] : '';
            $url = isset($item['url']) ? (string) $item['url'] : '';
            $icon = isset($item['icon']) ? (string) $item['icon'] : '';
            $icon_image = isset($item['icon_image']) ? (string) $item['icon_image'] : '';
            $icon_preview = '';
            if ($icon_image !== '' && strpos($icon_image, 'webshop/') === 0 && $upload_base !== '') {
                $icon_preview = $upload_base . '/' . ltrim($icon_image, '/');
            }
            $html .= '<div class="cms-lb-ft-social-item" data-social-index="' . (int) $i . '">'
                . '<div class="cms-lb-ft-social-item__head">'
                . '<span class="cms-lb-ft-social-item__drag cms-drag-handle"><i class="fa fa-bars"></i></span>'
                . '<input type="hidden" name="social_items[' . (int) $i . '][id]" class="cms-lb-ft-social-id" value="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">'
                . '<input type="text" name="social_items[' . (int) $i . '][title]" class="form-control input-sm cms-lb-ft-social-input" placeholder="Label (e.g. LinkedIn)" value="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">'
                . '<button type="button" class="btn btn-danger btn-xs cms-lb-ft-remove-social" title="Remove"><i class="fa fa-trash-o"></i></button>'
                . '</div>'
                . '<div class="cms-lb-ft-social-item__row">'
                . '<input type="text" name="social_items[' . (int) $i . '][url]" class="form-control input-sm cms-lb-ft-social-input" placeholder="URL or mailto:..." value="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">'
                . '</div>'
                . '<div class="cms-lb-ft-social-item__row cms-lb-ft-social-item__icon-row">'
                . '<input type="text" name="social_items[' . (int) $i . '][icon]" class="form-control input-sm cms-lb-ft-social-input" placeholder="FA icon e.g. fa-linkedin-square" value="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '">'
                . '<input type="hidden" name="social_items[' . (int) $i . '][icon_image]" class="cms-lb-ft-social-icon-path" value="' . htmlspecialchars($icon_image, ENT_QUOTES, 'UTF-8') . '">'
                . '<label class="btn btn-default btn-xs cms-lb-ft-social-upload-btn"><i class="fa fa-image"></i> Icon<input type="file" name="social_icon_file_' . (int) $i . '" accept=".ico,.jpg,.jpeg,.png,.gif,.webp,.svg" class="cms-lb-ft-social-icon-file" hidden></label>'
                . '</div>';
            if ($icon_preview !== '') {
                $html .= '<p class="cms-lb-ft-social-preview"><img src="' . htmlspecialchars($icon_preview, ENT_QUOTES, 'UTF-8') . '" alt="" style="max-height:24px;"></p>';
            }
            $html .= '</div>';
        }
        $html .= '</div>'
            . '<button type="button" class="btn btn-default btn-sm btn-block" id="cmsLbAddSocialItem"><i class="fa fa-plus"></i> Add social icon</button>';
        return $html;
    }
}

if (!function_exists('cms_footer_builder_link_storage_path')) {
    /**
     * Canonical CMS page path for storage (same slug the header uses), e.g. /services
     *
     * @param string $url
     * @return string
     */
    function cms_footer_builder_link_storage_path($url)
    {
        $u = trim((string) $url);
        if ($u === '' || $u === '/') {
            return '/';
        }
        if (preg_match('#^https?://#i', $u)) {
            $path = parse_url($u, PHP_URL_PATH);
            $u = is_string($path) && $path !== '' ? $path : '/';
        }
        if (preg_match('#/webshop(?:/cms_page)?(/.*)?$#i', $u, $m)) {
            $u = isset($m[1]) && $m[1] !== '' ? $m[1] : '/';
        }
        if (preg_match('#/cmspage(/.*)?$#i', $u, $m)) {
            $u = isset($m[1]) && $m[1] !== '' ? $m[1] : '/';
        }
        $cms_path = parse_url(site_url('cmspage'), PHP_URL_PATH);
        if (is_string($cms_path) && $cms_path !== '' && strpos($u, $cms_path) === 0) {
            $u = substr($u, strlen($cms_path));
        }
        if ($u === '' || $u === '/') {
            return '/';
        }
        if ($u[0] !== '/') {
            $u = '/' . $u;
        }
        return $u;
    }
}

if (!function_exists('cms_footer_builder_get_link_page_option_by_id')) {
    /**
     * @param int $page_id
     * @return array<string,mixed>|null
     */
    function cms_footer_builder_get_link_page_option_by_id($page_id)
    {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            return null;
        }
        foreach (cms_footer_builder_link_page_options() as $p) {
            if (isset($p['id']) && (int) $p['id'] === $page_id) {
                return $p;
            }
        }
        return null;
    }
}

if (!function_exists('cms_footer_builder_link_page_options')) {
    /**
     * Published CMS pages for footer section link dropdown (header pages first — same URLs as header nav).
     *
     * @return array<int,array{id:int,label:string,path:string,url:string,in_header:bool}>
     */
    function cms_footer_builder_link_page_options()
    {
        static $cached = null;
        if (is_array($cached)) {
            return $cached;
        }

        $CI =& get_instance();
        $header_pages = array();
        $all_pages = array();
        if (!isset($CI->cms_model)) {
            $CI->load->model('cms_model');
        }
        if (isset($CI->cms_model) && method_exists($CI->cms_model, 'getPublishedPages')) {
            $header_pages = $CI->cms_model->getPublishedPages(array('static', 'category'), 'header');
            $all_pages = $CI->cms_model->getPublishedPages(null, null);
        } elseif (!isset($CI->cms_pages_model) && file_exists(APPPATH . 'models/cms_admin/Cms_admin_pages_model.php')) {
            $CI->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_model');
        }
        if (empty($all_pages) && isset($CI->cms_pages_model) && method_exists($CI->cms_pages_model, 'getAdminPages')) {
            foreach ($CI->cms_pages_model->getAdminPages() as $p) {
                if (isset($p['status']) && $p['status'] === 'published') {
                    $all_pages[] = $p;
                }
            }
        }

        $header_ids = array();
        foreach ($header_pages as $p) {
            $id = isset($p['id']) ? (int) $p['id'] : 0;
            if ($id > 0) {
                $header_ids[$id] = true;
            }
        }

        $ordered = array();
        $seen = array();
        foreach (array_merge($header_pages, $all_pages) as $p) {
            $id = isset($p['id']) ? (int) $p['id'] : 0;
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $ordered[] = $p;
        }

        $out = array();
        foreach ($ordered as $p) {
            $id = isset($p['id']) ? (int) $p['id'] : 0;
            if ($id <= 0) {
                continue;
            }
            $name = isset($p['page_name']) ? trim((string) $p['page_name']) : '';
            if ($name === '') {
                $name = 'Page ' . $id;
            }
            $raw_url = isset($p['url']) ? trim((string) $p['url']) : '';
            $path = cms_footer_builder_link_storage_path($raw_url);
            $out[] = array(
                'id'        => $id,
                'label'     => $name,
                'path'      => $path,
                'url'       => function_exists('cms_page_public_url') ? cms_page_public_url($path) : $path,
                'in_header' => isset($header_ids[$id]),
            );
        }
        $cached = $out;
        return $out;
    }
}

if (!function_exists('cms_footer_builder_normalize_footer_link_url_key')) {
    /**
     * @param string $url
     * @return string
     */
    function cms_footer_builder_normalize_footer_link_url_key($url)
    {
        return cms_footer_builder_link_storage_path($url);
    }
}

if (!function_exists('cms_footer_builder_resolve_footer_link_page_key')) {
    /**
     * @param array<string,mixed>              $link
     * @param array<int,array<string,mixed>>   $page_options
     * @return string page id or __other__
     */
    function cms_footer_builder_resolve_footer_link_page_key(array $link, array $page_options)
    {
        if (isset($link['page_id']) && (int) $link['page_id'] > 0) {
            return (string) (int) $link['page_id'];
        }
        $url_key = cms_footer_builder_normalize_footer_link_url_key(isset($link['url']) ? $link['url'] : '');
        if ($url_key === '') {
            return '__other__';
        }
        foreach ($page_options as $p) {
            $pid = isset($p['id']) ? (int) $p['id'] : 0;
            if ($pid <= 0) {
                continue;
            }
            $page_url_key = isset($p['path'])
                ? cms_footer_builder_link_storage_path($p['path'])
                : cms_footer_builder_normalize_footer_link_url_key(isset($p['url']) ? $p['url'] : '');
            if ($page_url_key !== '' && $page_url_key === $url_key) {
                return (string) $pid;
            }
        }
        return '__other__';
    }
}

if (!function_exists('cms_layout_builder_render_footer_section_link_row')) {
    /**
     * @param int                              $section_index
     * @param int                              $link_index
     * @param array<string,mixed>              $link
     * @param array<int,array<string,mixed>>   $page_options
     * @return string
     */
    function cms_layout_builder_render_footer_section_link_row($section_index, $link_index, array $link, array $page_options = array())
    {
        if (empty($page_options)) {
            $page_options = cms_footer_builder_link_page_options();
        }
        $link = cms_footer_builder_enrich_footer_link(is_array($link) ? $link : array());
        $label = isset($link['label']) ? (string) $link['label'] : '';
        $url = isset($link['url']) ? (string) $link['url'] : '';
        $selected = cms_footer_builder_resolve_footer_link_page_key($link, $page_options);
        $is_other = ($selected === '__other__');
        $resolved_public = $is_other
            ? $url
            : cms_page_public_url(cms_footer_builder_link_storage_path($url));

        $html = '<div class="cms-lb-ft-link-row' . ($is_other ? ' is-other' : '') . '">'
            . '<select name="footer_sections[' . (int) $section_index . '][links][' . (int) $link_index . '][cms_page_key]" class="form-control input-sm cms-lb-ft-link-page-select cms-lb-ft-section-input" title="Select CMS page or Other">'
            . '<option value="">— Select page —</option>';
        foreach ($page_options as $p) {
            $pid = isset($p['id']) ? (int) $p['id'] : 0;
            if ($pid <= 0) {
                continue;
            }
            $plabel = isset($p['label']) ? (string) $p['label'] : ('Page ' . $pid);
            $ppath = isset($p['path']) ? (string) $p['path'] : cms_footer_builder_link_storage_path(isset($p['url']) ? $p['url'] : '');
            $purl = isset($p['url']) ? (string) $p['url'] : cms_page_public_url($ppath);
            $sel = ((string) $pid === $selected) ? ' selected' : '';
            $header_mark = !empty($p['in_header']) ? ' ★' : '';
            $html .= '<option value="' . $pid . '" data-label="' . htmlspecialchars($plabel, ENT_QUOTES, 'UTF-8') . '" data-path="'
                . htmlspecialchars($ppath, ENT_QUOTES, 'UTF-8') . '" data-url="'
                . htmlspecialchars($purl, ENT_QUOTES, 'UTF-8') . '"' . $sel . '>'
                . htmlspecialchars($plabel, ENT_QUOTES, 'UTF-8') . $header_mark . '</option>';
        }
        $page_id_val = (!$is_other && $selected !== '') ? (int) $selected : '';
        $html .= '<option value="__other__"' . ($is_other ? ' selected' : '') . '>Other (custom link)</option>'
            . '</select>'
            . '<input type="hidden" name="footer_sections[' . (int) $section_index . '][links][' . (int) $link_index . '][page_id]" class="cms-lb-ft-link-page-id" value="' . ($page_id_val > 0 ? (int) $page_id_val : '') . '">'
            . '<div class="cms-lb-ft-link-custom">'
            . '<input type="text" name="footer_sections[' . (int) $section_index . '][links][' . (int) $link_index . '][label]" class="form-control input-sm cms-lb-ft-section-input cms-lb-ft-link-label" placeholder="Link label" value="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">'
            . '<input type="text" name="footer_sections[' . (int) $section_index . '][links][' . (int) $link_index . '][url]" class="form-control input-sm cms-lb-ft-section-input cms-lb-ft-link-url" placeholder="/page or https://..." value="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">'
            . '</div>'
            . '<button type="button" class="btn btn-default btn-xs cms-lb-ft-remove-link" title="Remove link"><i class="fa fa-times"></i></button>'
            . '</div>'
            . '<p class="cms-lb-ft-link-resolved cms-hd-field-help"' . ($is_other || $selected === '' ? ' style="display:none;"' : '') . '>Opens: <code class="cms-lb-ft-link-resolved-url">'
            . htmlspecialchars($resolved_public, ENT_QUOTES, 'UTF-8') . '</code> <span class="text-muted">(same as header nav for this page)</span></p>';
        return $html;
    }
}

if (!function_exists('cms_layout_builder_render_footer_section_row')) {
    /**
     * @param array<string,mixed> $sec
     * @param int                 $index
     * @return string
     */
    function cms_layout_builder_render_footer_section_row(array $sec, $index, array $page_options = array())
    {
        if (empty($page_options)) {
            $page_options = cms_footer_builder_link_page_options();
        }
        $id = isset($sec['id']) ? (string) $sec['id'] : ('sec_' . $index);
        $title = isset($sec['title']) ? (string) $sec['title'] : '';
        $links = isset($sec['links']) && is_array($sec['links']) ? $sec['links'] : array();
        if (empty($links)) {
            $links = array(array('label' => '', 'url' => ''));
        }

        $html = '<div class="cms-lb-ft-section" data-section-index="' . (int) $index . '">'
            . '<div class="cms-lb-ft-section__head">'
            . '<span class="cms-lb-ft-section__drag"><i class="fa fa-bars"></i></span>'
            . '<input type="hidden" name="footer_sections[' . (int) $index . '][id]" class="cms-lb-ft-section-id" value="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">'
            . '<input type="text" name="footer_sections[' . (int) $index . '][title]" class="form-control input-sm cms-lb-ft-section-title cms-lb-ft-section-input" placeholder="Section name (e.g. Solutions)" value="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">'
            . '<button type="button" class="btn btn-danger btn-xs cms-lb-ft-remove-section" title="Remove section"><i class="fa fa-trash-o"></i></button>'
            . '</div>'
            . '<div class="cms-lb-ft-section__links">';
        foreach ($links as $li => $link) {
            $html .= cms_layout_builder_render_footer_section_link_row($index, $li, is_array($link) ? $link : array(), $page_options);
        }
        $html .= '</div>'
            . '<button type="button" class="btn btn-default btn-xs cms-lb-ft-add-link"><i class="fa fa-plus"></i> Add link</button>'
            . '</div>';
        return $html;
    }
}

if (!function_exists('cms_layout_builder_render_footer_sections_editor')) {
    /**
     * @param array<string,mixed> $cfg
     * @return string
     */
    function cms_layout_builder_render_footer_sections_editor(array $cfg, $page_options = null)
    {
        $sections = cms_footer_builder_get_sections($cfg);
        if (!is_array($page_options)) {
            $page_options = cms_footer_builder_link_page_options();
        }
        $html = '<div class="cms-lb-ft-sections" id="cmsFooterSections">';
        foreach ($sections as $i => $sec) {
            $html .= cms_layout_builder_render_footer_section_row(is_array($sec) ? $sec : array(), $i, $page_options);
        }
        $html .= '</div>'
            . '<button type="button" class="btn btn-default btn-sm btn-block" id="cmsLbAddFooterSection"><i class="fa fa-plus"></i> Add footer section</button>';

        return $html;
    }
}

if (!function_exists('cms_layout_builder_render_header_element_positions')) {
    function cms_layout_builder_render_header_element_positions(array $cfg, $css_class = 'cms-lb-live')
    {
        return cms_layout_builder_render_header_show_elements($cfg, $css_class);
    }
}

if (!function_exists('cms_header_builder_logo_home_url')) {
    /**
     * Logo click target: first header menu CMS page, else CMS home.
     *
     * @return string
     */
    function cms_header_builder_logo_home_url()
    {
        $links = cms_get_header_nav_links_from_cms_pages();
        if (!empty($links[0]['url'])) {
            return (string) $links[0]['url'];
        }
        return site_url('cmspage');
    }
}

if (!function_exists('cms_page_public_url')) {
    /**
     * Public URL for a CMS page row (routes via cmspage).
     *
     * @param string $url stored url code e.g. /about
     * @return string
     */
    function cms_page_public_url($url)
    {
        $u = trim((string) $url);
        if ($u === '' || $u === '/') {
            return site_url('cmspage');
        }
        if ($u[0] !== '/') {
            $u = '/' . $u;
        }
        return site_url('cmspage') . $u;
    }
}

if (!function_exists('cms_storefront_nav_href_is_current')) {
    /**
     * Whether a nav href matches the current request (storefront or CMS preview).
     *
     * @param string $href
     * @return bool
     */
    function cms_storefront_nav_href_is_current($href)
    {
        $href = trim((string) $href);
        if ($href === '' || $href === '#') {
            return false;
        }

        if (preg_match('#^https?://#i', $href)) {
            $path = parse_url($href, PHP_URL_PATH);
        } else {
            $path = $href;
        }
        if (!is_string($path) || $path === '') {
            return false;
        }

        $path = '/' . ltrim(strtolower($path), '/');
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $cur = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        $cur = parse_url($cur, PHP_URL_PATH);
        if (!is_string($cur) || $cur === '') {
            $cur = '/';
        }
        $cur = rtrim(strtolower($cur), '/');
        if ($cur === '') {
            $cur = '/';
        }

        if ($path === $cur) {
            return true;
        }
        if ($path !== '/' && strlen($path) <= strlen($cur) && substr($cur, -strlen($path)) === $path) {
            return true;
        }

        return false;
    }
}

if (!function_exists('cms_render_header_nav_anchor')) {
    /**
     * @param string $url
     * @param string $label
     * @return string
     */
    function cms_render_header_nav_anchor($url, $label)
    {
        $url = trim((string) $url);
        $label = trim((string) $label);
        $attrs = 'href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"';
        $is_current = function_exists('webshop_storefront_nav_href_is_current')
            ? webshop_storefront_nav_href_is_current($url)
            : cms_storefront_nav_href_is_current($url);
        if ($is_current) {
            $attrs .= ' class="is-current" aria-current="page"';
        }

        return '<a ' . $attrs . '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
    }
}

if (!function_exists('cms_layout_builder_header_nav_active_style_vars')) {
    /**
     * CSS variables for active nav underline (controlled from Layout Builder).
     *
     * @param array<string,mixed> $cfg
     * @return string
     */
    function cms_layout_builder_header_nav_active_style_vars(array $cfg)
    {
        $underline = !isset($cfg['nav_active_underline']) || !empty($cfg['nav_active_underline']);

        return '--cms-hdr-el-nav-active-border-width:' . ($underline ? '2px' : '0');
    }
}

if (!function_exists('cms_get_header_nav_links_from_cms_pages')) {
    /**
     * Menu items from CMS pages (published + Show In Header).
     *
     * @return array<int,array{label:string,url:string}>
     */
    function cms_get_header_nav_links_from_cms_pages()
    {
        $CI =& get_instance();
        $pages = array();

        if (!isset($CI->cms_model)) {
            $CI->load->model('cms_model');
        }
        if (isset($CI->cms_model) && method_exists($CI->cms_model, 'getPublishedNavPayload')) {
            $payload = $CI->cms_model->getPublishedNavPayload('header', array('static', 'category'));
            $pages = isset($payload['pages']) && is_array($payload['pages']) ? $payload['pages'] : array();
        } elseif (isset($CI->cms_model) && method_exists($CI->cms_model, 'getPublishedPages')) {
            $pages = $CI->cms_model->getPublishedPages(array('static', 'category'), 'header');
        } elseif (!isset($CI->cms_pages_model)) {
            if (file_exists(APPPATH . 'models/cms_admin/Cms_admin_pages_model.php')) {
                $CI->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_model');
            }
        }
        if (empty($pages) && isset($CI->cms_pages_model) && method_exists($CI->cms_pages_model, 'getHeaderMenuNavItems')) {
            foreach ($CI->cms_pages_model->getHeaderMenuNavItems() as $item) {
                $pages[] = array(
                    'page_name'      => isset($item['label']) ? $item['label'] : '',
                    'url'            => isset($item['url']) ? preg_replace('#^.*?cmspage#', '', $item['url']) : '/',
                    'parent_page_id' => isset($item['parent_page_id']) ? (int) $item['parent_page_id'] : 0,
                );
            }
        }

        $page_ids = array();
        foreach ($pages as $row) {
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id > 0) {
                $page_ids[$id] = true;
            }
        }

        $links = array();
        foreach ($pages as $row) {
            $parent = isset($row['parent_page_id']) ? (int) $row['parent_page_id'] : 0;
            if ($parent > 0 && isset($page_ids[$parent])) {
                continue;
            }
            $label = isset($row['page_name']) ? trim((string) $row['page_name']) : '';
            if ($label === '') {
                continue;
            }
            $links[] = array(
                'label' => $label,
                'url'   => cms_page_public_url(isset($row['url']) ? $row['url'] : ''),
            );
        }
        return $links;
    }
}

if (!function_exists('cms_header_builder_filter_nav_links_for_bar')) {
    /**
     * Drop nav items only when they link to the same URL as the layout-builder CTA button.
     *
     * @param array<int,array{label:string,url:string}> $nav_links
     * @param array<string,mixed>                     $cfg
     * @return array<int,array{label:string,url:string}>
     */
    function cms_header_builder_filter_nav_links_for_bar(array $nav_links, array $cfg)
    {
        if (empty($nav_links) || empty($cfg['show_button']) || trim(strip_tags((string) $cfg['button_text'])) === '') {
            return $nav_links;
        }

        $btn_paths = array();
        $btn_raw = trim((string) (isset($cfg['button_link']) ? $cfg['button_link'] : ''));
        if ($btn_raw !== '') {
            $btn_paths[] = strtolower(ltrim($btn_raw, '/'));
        }

        $out = array();
        foreach ($nav_links as $lnk) {
            $url = strtolower(trim((string) (isset($lnk['url']) ? $lnk['url'] : '')));
            $url_path = '';
            if ($url !== '' && preg_match('~/cmspage/([^?#]+)~i', $url, $m)) {
                $url_path = strtolower(trim((string) $m[1], '/'));
            }

            $dup_url = false;
            foreach ($btn_paths as $bp) {
                if ($bp !== '' && $url_path !== '' && $url_path === $bp) {
                    $dup_url = true;
                    break;
                }
            }
            if ($dup_url) {
                continue;
            }

            $out[] = $lnk;
        }
        return $out;
    }
}

if (!function_exists('cms_header_builder_resolve_nav_links')) {
    /**
     * Header nav always from CMS Pages (show_in_header + published).
     *
     * @param array<string,mixed> $cfg unused; kept for call-site compatibility
     * @return array<int,array{label:string,url:string}>
     */
    function cms_header_builder_resolve_nav_links(array $cfg = array())
    {
        return cms_get_header_nav_links_from_cms_pages();
    }
}

if (!function_exists('cms_layout_builder_sanitize_align')) {
    /**
     * @param string $value
     * @param string $default left|center|right
     * @return string
     */
    function cms_layout_builder_sanitize_align($value, $default = 'left')
    {
        $v = strtolower(trim((string) $value));
        $default = strtolower(trim((string) $default));
        if (!in_array($default, array('left', 'center', 'right'), true)) {
            $default = 'left';
        }
        return in_array($v, array('left', 'center', 'right'), true) ? $v : $default;
    }
}

if (!function_exists('cms_layout_builder_sanitize_bar_align')) {
    /**
     * Header bar layout: grouped (left/center/right) or split (logo | nav | icons).
     *
     * @param string $value
     * @param string $default
     * @return string
     */
    function cms_layout_builder_sanitize_bar_align($value, $default = 'split')
    {
        $v = strtolower(trim((string) $value));
        $default = strtolower(trim((string) $default));
        $allowed = array('left', 'center', 'right', 'split');
        if (!in_array($default, $allowed, true)) {
            $default = 'split';
        }
        return in_array($v, $allowed, true) ? $v : $default;
    }
}

if (!function_exists('cms_layout_builder_render_bar_layout_radios')) {
    /**
     * Header bar: split (logo left, menu center, icons right) or grouped alignment.
     *
     * @param string $current
     * @param string $css_class
     * @return string
     */
    function cms_layout_builder_render_bar_layout_radios($current = 'split', $css_class = 'cms-lb-live')
    {
        $current = cms_layout_builder_sanitize_bar_align($current);
        $css_class = trim((string) $css_class);
        $options = array(
            'split'  => 'Split',
            'left'   => 'All left',
            'center' => 'All center',
            'right'  => 'All right',
        );
        $html = '<div class="cms-lb-align cms-lb-align--bar" data-align-name="bar_align">';
        foreach ($options as $val => $lbl) {
            $id = 'bar_align_' . $val;
            $checked = ($current === $val) ? ' checked' : '';
            $html .= '<label class="cms-lb-align__opt' . ($current === $val ? ' is-active' : '') . '" for="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars(cms_layout_builder_bar_layout_hint($val), ENT_QUOTES, 'UTF-8') . '">'
                . '<input type="radio" id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" name="bar_align" value="' . $val . '" class="cms-lb-align-input' . ($css_class !== '' ? ' ' . htmlspecialchars($css_class, ENT_QUOTES, 'UTF-8') : '') . '"' . $checked . '>'
                . '<span>' . htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8') . '</span></label>';
        }
        return $html . '<p class="cms-hd-field-help cms-lb-bar-layout-hint">' . htmlspecialchars(cms_layout_builder_bar_layout_hint($current), ENT_QUOTES, 'UTF-8') . '</p></div>';
    }
}

if (!function_exists('cms_layout_builder_bar_layout_hint')) {
    /**
     * @param string $layout
     * @return string
     */
    function cms_layout_builder_bar_layout_hint($layout)
    {
        $layout = cms_layout_builder_sanitize_bar_align($layout);
        switch ($layout) {
            case 'split':
                return 'Logo on the left, menu in the center, cart/icons on the right (recommended).';
            case 'center':
                return 'Logo, menu, and icons grouped together in the center.';
            case 'right':
                return 'Logo, menu, and icons grouped on the right.';
            default:
                return 'Logo, menu, and icons grouped on the left.';
        }
    }
}

if (!function_exists('cms_layout_builder_render_align_radios')) {
    /**
     * Left / center / right radio group for layout builder forms.
     *
     * @param string $name     input name
     * @param string $current  selected value
     * @param string $css_class extra classes on inputs (e.g. cms-lb-live)
     * @return string
     */
    function cms_layout_builder_render_align_radios($name, $current = 'left', $css_class = 'cms-lb-live')
    {
        $name = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $name));
        $current = cms_layout_builder_sanitize_align($current);
        $css_class = trim((string) $css_class);
        $html = '<div class="cms-lb-align" data-align-name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
        foreach (array('left' => 'Left', 'center' => 'Center', 'right' => 'Right') as $val => $lbl) {
            $id = $name . '_' . $val;
            $checked = ($current === $val) ? ' checked' : '';
            $html .= '<label class="cms-lb-align__opt' . ($current === $val ? ' is-active' : '') . '" for="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">'
                . '<input type="radio" id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . $val . '" class="cms-lb-align-input' . ($css_class !== '' ? ' ' . htmlspecialchars($css_class, ENT_QUOTES, 'UTF-8') : '') . '"' . $checked . '>'
                . '<span>' . htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8') . '</span></label>';
        }
        return $html . '</div>';
    }
}

if (!function_exists('cms_footer_builder_default_config')) {
    /**
     * @return array<string,mixed>
     */
    function cms_footer_builder_default_config()
    {
        return array(
            'show_logo'       => true,
            'show_tagline'    => true,
            'show_copyright'  => true,
            'show_legal'      => true,
            'show_social'     => true,
            'show_newsletter' => true,
            'show_menu_1'     => false,
            'show_menu_2'     => false,
            'logo_image'      => '',
            'tagline'         => 'Your U.S. Market Entry Partner for Global Manufacturers.',
            'copyright'       => '© {year} Your Company. All rights reserved.',
            'newsletter_title'       => 'Stay Updated',
            'newsletter_desc'        => 'Get the latest insights on U.S. market entry, growth strategies, and trends.',
            'newsletter_placeholder' => 'Enter your email',
            'social_linkedin' => '',
            'social_youtube'  => '',
            'social_twitter'  => '',
            'social_email'    => '',
            'legal_link_1_label' => 'Privacy Policy',
            'legal_link_1_url'   => '',
            'legal_link_2_label' => 'Terms of Service',
            'legal_link_2_url'   => '',
            'bg_color'        => '#ffffff',
            'text_color'      => '#1e293b',
            'accent_color'    => '#c28913',
            'tagline_align'   => 'left',
            'copyright_align' => 'left',
            'legal_align'     => 'right',
            'social_align'    => 'left',
            'newsletter_align'=> 'left',
            'footer_sections'      => cms_footer_builder_default_sections(),
            'social_items'         => cms_footer_builder_default_social_items(),
            'footer_element_order' => array(),
            'show_floating_whatsapp'    => false,
            'floating_whatsapp_phone'   => '',
            'floating_whatsapp_message' => 'Hi, I would like to know more about your products.',
        );
    }
}

if (!function_exists('cms_footer_builder_blank_config')) {
    /**
     * Empty footer layout for a newly created profile (no sample columns or social rows).
     *
     * @return array<string,mixed>
     */
    function cms_footer_builder_blank_config()
    {
        $cfg = cms_footer_builder_default_config();
        foreach (array('show_logo', 'show_tagline', 'show_copyright', 'show_legal', 'show_social', 'show_newsletter', 'show_menu_1', 'show_menu_2') as $k) {
            $cfg[$k] = false;
        }
        $cfg['logo_image'] = '';
        $cfg['tagline'] = '';
        $cfg['copyright'] = '';
        $cfg['newsletter_title'] = '';
        $cfg['newsletter_desc'] = '';
        $cfg['newsletter_placeholder'] = '';
        $cfg['legal_link_1_label'] = '';
        $cfg['legal_link_1_url'] = '';
        $cfg['legal_link_2_label'] = '';
        $cfg['legal_link_2_url'] = '';
        $cfg['social_linkedin'] = '';
        $cfg['social_youtube'] = '';
        $cfg['social_twitter'] = '';
        $cfg['social_email'] = '';
        $cfg['footer_sections'] = array();
        $cfg['social_items'] = array();
        $cfg['footer_element_order'] = array();
        $cfg['show_floating_whatsapp'] = false;
        $cfg['floating_whatsapp_phone'] = '';
        $cfg['floating_whatsapp_message'] = '';
        return $cfg;
    }
}

if (!function_exists('cms_normalize_whatsapp_phone')) {
    /**
     * Digits only for wa.me links (include country code, no +).
     *
     * @param string $phone
     * @return string
     */
    function cms_normalize_whatsapp_phone($phone)
    {
        return preg_replace('/\D+/', '', trim((string) $phone));
    }
}

if (!function_exists('cms_floating_whatsapp_phone_from_config')) {
    /**
     * @param array<string,mixed> $footer_cfg
     * @param string              $profile_slug
     * @return string
     */
    function cms_floating_whatsapp_phone_from_config(array $footer_cfg, $profile_slug = '')
    {
        $phone = cms_normalize_whatsapp_phone(
            isset($footer_cfg['floating_whatsapp_phone']) ? $footer_cfg['floating_whatsapp_phone'] : ''
        );
        if ($phone !== '') {
            return $phone;
        }

        if (function_exists('webshop_get_header_builder_config_for_profile')) {
            $header_cfg = webshop_get_header_builder_config_for_profile($profile_slug !== '' ? $profile_slug : null);
            if (is_array($header_cfg) && !empty($header_cfg['phone'])) {
                return cms_normalize_whatsapp_phone($header_cfg['phone']);
            }
        }

        return '';
    }
}

if (!function_exists('cms_render_floating_whatsapp_html')) {
    /**
     * Fixed bottom-right WhatsApp chat link (site-wide chrome).
     *
     * @param array<string,mixed> $cfg footer builder config
     * @param string              $profile_slug
     * @return string
     */
    function cms_render_floating_whatsapp_html(array $cfg, $profile_slug = '')
    {
        $cfg = cms_sanitize_footer_builder_config($cfg);
        if (empty($cfg['show_floating_whatsapp'])) {
            return '';
        }

        $profile_slug = cms_storefront_normalize_profile_slug($profile_slug);
        $phone = cms_floating_whatsapp_phone_from_config($cfg, $profile_slug);
        if ($phone === '') {
            return '';
        }

        $message = isset($cfg['floating_whatsapp_message']) ? trim((string) $cfg['floating_whatsapp_message']) : '';
        $href = 'https://wa.me/' . $phone;
        if ($message !== '') {
            $href .= '?text=' . rawurlencode($message);
        }

        $label = $message !== '' ? $message : 'Chat with us on WhatsApp';

        return '<div class="cms-floating-whatsapp" role="complementary" aria-label="WhatsApp chat">'
            . '<a class="cms-floating-whatsapp__btn" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"'
            . ' target="_blank" rel="noopener noreferrer"'
            . ' title="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '"'
            . ' aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">'
            . '<i class="fa fa-whatsapp" aria-hidden="true"></i>'
            . '</a></div>';
    }
}

if (!function_exists('cms_sanitize_header_builder_config')) {
    /**
     * @param array<string,mixed> $in
     * @return array<string,mixed>
     */
    function cms_sanitize_header_builder_config(array $in)
    {
        $d = cms_header_builder_default_config();
        $out = $d;
        foreach (array('show_logo', 'show_cart', 'show_wishlist', 'show_account', 'show_search', 'show_nav', 'show_phone', 'show_promo', 'show_button') as $k) {
            $out[$k] = !empty($in[$k]);
        }
        $out['nav_active_underline'] = array_key_exists('nav_active_underline', $in)
            ? !empty($in['nav_active_underline'])
            : !empty($d['nav_active_underline']);
        $out['logo_image'] = isset($in['logo_image']) ? trim((string) $in['logo_image']) : '';
        $out['favicon_image'] = isset($in['favicon_image']) ? trim((string) $in['favicon_image']) : '';
        $out['phone'] = isset($in['phone']) ? trim((string) $in['phone']) : '';
        $out['promo_text'] = isset($in['promo_text']) ? trim((string) $in['promo_text']) : '';
        $out['button_text'] = isset($in['button_text']) ? trim((string) $in['button_text']) : $d['button_text'];
        $out['button_link'] = isset($in['button_link']) ? trim((string) $in['button_link']) : '';
        if (array_key_exists('google_analytics', $in)) {
            $ga = trim((string) $in['google_analytics']);
            if ($ga === '' || cms_header_builder_prepare_google_analytics_value($ga) === false) {
                $out['google_analytics'] = '';
            } else {
                $out['google_analytics'] = $ga;
            }
        }
        if (array_key_exists('custom_header_tags', $in)) {
            $out['custom_header_tags'] = cms_header_builder_parse_custom_header_tags_from_post($in['custom_header_tags']);
        }
        foreach (array('bg_color', 'text_color', 'accent_color', 'icon_bg') as $k) {
            $v = isset($in[$k]) ? trim((string) $in[$k]) : $d[$k];
            $out[$k] = preg_match('/^#[0-9a-fA-F]{3,8}$/', $v) ? $v : $d[$k];
        }
        foreach (cms_header_builder_apply_element_aligns($in, $d) as $align_key => $align_val) {
            $out[$align_key] = $align_val;
        }
        
        $style_fields = array_keys(cms_layout_builder_element_style_field_map());
        foreach (array('promo', 'logo', 'nav', 'phone', 'search', 'wishlist', 'cart', 'account', 'button') as $ek) {
            $enabled_key = cms_layout_builder_element_styles_enabled_key($ek);
            $enabled = array_key_exists($enabled_key, $in)
                ? cms_layout_normalize_flag($in[$enabled_key])
                : cms_layout_builder_element_has_styles($in, $ek);
            $out[$enabled_key] = $enabled ? 1 : 0;
            foreach ($style_fields as $sf) {
                $k = $ek . '_' . $sf;
                $out[$k] = ($enabled && isset($in[$k])) ? trim((string) $in[$k]) : '';
            }
        }

        $out['bar_align'] = cms_layout_builder_sanitize_bar_align(isset($in['bar_align']) ? $in['bar_align'] : '', $d['bar_align']);
        $out = cms_layout_builder_merge_element_colors_from_array($out, $in, 'header');
        return cms_layout_builder_apply_element_colors($out, 'header');
    }
}

if (!function_exists('cms_header_builder_push_zone')) {
    /**
     * @param array<string,array<int,string>> $zones
     * @param string                            $align
     * @param string                            $html
     */
    function cms_header_builder_push_zone(&$zones, $align, $html)
    {
        $html = trim((string) $html);
        if ($html === '') {
            return;
        }
        $align = cms_layout_builder_sanitize_align($align);
        if (!isset($zones[$align])) {
            $zones[$align] = array();
        }
        $zones[$align][] = $html;
    }
}

if (!function_exists('cms_header_builder_render_zones_html')) {
    /**
     * @param array<string,array<int,string>> $zones
     * @return string
     */
    function cms_header_builder_render_zones_html($zones)
    {
        $grid_style = function_exists('webshop_header_builder_zones_grid_style_attr')
            ? webshop_header_builder_zones_grid_style_attr()
            : 'display:grid;grid-template-columns:minmax(0,max-content) minmax(0,1fr) minmax(0,max-content);align-items:center;width:100%;max-width:1280px;margin:0 auto;gap:12px clamp(16px,2vw,28px);min-height:48px';
        $html = '<div class="cms-ws-header__inner cms-ws-header__inner--zones" style="' . htmlspecialchars($grid_style, ENT_QUOTES, 'UTF-8') . '">';
        foreach (array('left', 'center', 'right') as $side) {
            $html .= '<div class="cms-ws-header__zone cms-ws-header__zone--' . $side . '">';
            if (!empty($zones[$side])) {
                $html .= implode('', $zones[$side]);
            }
            $html .= '</div>';
        }
        return $html . '</div>';
    }
}

if (!function_exists('cms_footer_builder_config_uses_legacy_menus')) {
    /**
     * Old footer builder rows (menu_1 / menu_2) before footer_sections JSON.
     *
     * @param array<string,mixed> $in
     * @return bool
     */
    function cms_footer_builder_config_uses_legacy_menus(array $in)
    {
        return isset($in['show_menu_1']) || isset($in['show_menu_2'])
            || isset($in['menu_1_align']) || isset($in['menu_2_align']);
    }
}

if (!function_exists('cms_footer_builder_sections_have_links')) {
    /**
     * @param array<int,array<string,mixed>> $sections
     * @return bool
     */
    function cms_footer_builder_sections_have_links(array $sections)
    {
        foreach ($sections as $sec) {
            if (!is_array($sec) || empty($sec['links']) || !is_array($sec['links'])) {
                continue;
            }
            foreach ($sec['links'] as $link) {
                if (!is_array($link)) {
                    continue;
                }
                $label = isset($link['label']) ? trim((string) $link['label']) : '';
                $url = isset($link['url']) ? trim((string) $link['url']) : '';
                if ($label !== '' || $url !== '' || !empty($link['page_id'])) {
                    return true;
                }
            }
        }
        return false;
    }
}

if (!function_exists('cms_footer_builder_merge_live_footer_preview')) {
    /**
     * Merge saved DB footer config with live preview POST/JSON (DB sections win when live has no links).
     *
     * @param array<string,mixed> $db_cfg
     * @param array<string,mixed> $live_cfg
     * @return array<string,mixed>
     */
    function cms_footer_builder_merge_live_footer_preview(array $db_cfg, array $live_cfg)
    {
        $db_cfg = cms_sanitize_footer_builder_config(is_array($db_cfg) ? $db_cfg : array());
        $live_cfg = is_array($live_cfg) ? $live_cfg : array();
        $merged = array_replace_recursive($db_cfg, $live_cfg);

        $live_sections = array();
        if (isset($live_cfg['footer_sections']) && is_array($live_cfg['footer_sections'])) {
            $live_sections = cms_footer_builder_normalize_sections($live_cfg['footer_sections']);
        }
        $db_sections = cms_footer_builder_get_sections($db_cfg);

        if (!empty($live_sections)) {
            $merged['footer_sections'] = $live_sections;
        } elseif (!empty($db_sections)) {
            $merged['footer_sections'] = $db_sections;
        } else {
            $merged['footer_sections'] = array();
        }

        return cms_sanitize_footer_builder_config($merged);
    }
}

if (!function_exists('cms_sanitize_footer_builder_config')) {
    /**
     * @param array<string,mixed> $in
     * @return array<string,mixed>
     */
    function cms_sanitize_footer_builder_config(array $in)
    {
        $d = cms_footer_builder_default_config();
        $out = $d;

        if (array_key_exists('footer_sections', $in) && is_array($in['footer_sections'])) {
            $out['footer_sections'] = cms_footer_builder_normalize_sections($in['footer_sections']);
        } elseif (cms_footer_builder_config_uses_legacy_menus($in)) {
            $out['footer_sections'] = cms_footer_builder_default_sections();
        } else {
            $out['footer_sections'] = array();
        }

        $defs = cms_footer_builder_show_element_defs($out);

        foreach ($defs as $def) {
            if (!empty($def['is_footer_section'])) {
                continue;
            }
            $show_key = $def['show_key'];
            $align_key = $def['align_key'];
            $out[$show_key] = !empty($in[$show_key]);
            if (isset($in[$align_key])) {
                $out[$align_key] = cms_layout_builder_sanitize_align($in[$align_key], $def['default']);
            } elseif (!isset($out[$align_key])) {
                $out[$align_key] = $def['default'];
            }
            if (!empty($def['is_cms_menu']) && !empty($def['title_align_key'])) {
                $title_align_key = (string) $def['title_align_key'];
                $fallback = isset($out[$align_key]) ? $out[$align_key] : $def['default'];
                $out[$title_align_key] = cms_layout_builder_sanitize_align(
                    isset($in[$title_align_key]) ? $in[$title_align_key] : '',
                    $fallback
                );
            }
        }

        foreach ($out['footer_sections'] as $i => $sec) {
            if (!is_array($sec) || empty($sec['id'])) {
                continue;
            }
            $skey = cms_footer_builder_section_storage_key($sec['id']);
            $show_key = 'show_' . $skey;
            $row_show = null;
            if (isset($in['footer_sections']) && is_array($in['footer_sections']) && isset($in['footer_sections'][$i]['show'])) {
                $row_show = !empty($in['footer_sections'][$i]['show']);
            } elseif (isset($sec['show'])) {
                $row_show = !empty($sec['show']);
            }
            if (array_key_exists($show_key, $in)) {
                $flat_show = !empty($in[$show_key]);
                if ($row_show === true) {
                    $out['footer_sections'][$i]['show'] = true;
                } else {
                    $out['footer_sections'][$i]['show'] = $flat_show;
                }
            } elseif ($row_show !== null) {
                $out['footer_sections'][$i]['show'] = $row_show;
            } elseif (!isset($out['footer_sections'][$i]['show'])) {
                $out['footer_sections'][$i]['show'] = true;
            }
            $align_key = $skey . '_align';
            if (isset($in[$align_key])) {
                $out['footer_sections'][$i]['links_align'] = cms_layout_builder_sanitize_align($in[$align_key], 'left');
            }
            $title_align_key = $skey . '_title_align';
            if (isset($in[$title_align_key])) {
                $fallback = isset($out['footer_sections'][$i]['links_align']) ? $out['footer_sections'][$i]['links_align'] : 'left';
                $out['footer_sections'][$i]['title_align'] = cms_layout_builder_sanitize_align($in[$title_align_key], $fallback);
            }
        }

        $out = cms_footer_builder_sync_sections_to_flat_keys($out);

        if (array_key_exists('social_items', $in) && is_array($in['social_items'])) {
            $out['social_items'] = cms_footer_builder_normalize_social_items($in['social_items']);
        } else {
            $out['social_items'] = cms_footer_builder_get_social_items($in);
        }

        $order_raw = isset($in['footer_element_order']) ? $in['footer_element_order'] : array();
        if (is_string($order_raw) && $order_raw !== '') {
            $decoded_order = json_decode($order_raw, true);
            $order_raw = is_array($decoded_order) ? $decoded_order : array();
        }
        $out['footer_element_order'] = cms_footer_builder_normalize_element_order(
            is_array($order_raw) ? $order_raw : array(),
            $out
        );

        $out['logo_image'] = isset($in['logo_image']) ? trim((string) $in['logo_image']) : $d['logo_image'];
        foreach (array(
            'tagline', 'copyright', 'newsletter_title', 'newsletter_desc', 'newsletter_placeholder',
            'social_linkedin', 'social_youtube', 'social_twitter', 'social_email',
            'legal_link_1_label', 'legal_link_1_url', 'legal_link_2_label', 'legal_link_2_url',
            'floating_whatsapp_phone', 'floating_whatsapp_message',
        ) as $k) {
            $out[$k] = isset($in[$k]) ? trim((string) $in[$k]) : (isset($d[$k]) ? $d[$k] : '');
        }

        $out['show_floating_whatsapp'] = !empty($in['show_floating_whatsapp']);

        foreach (array('bg_color', 'text_color', 'accent_color') as $k) {
            $v = isset($in[$k]) ? trim((string) $in[$k]) : $d[$k];
            $out[$k] = preg_match('/^#[0-9a-fA-F]{3,8}$/', $v) ? $v : $d[$k];
        }

        $style_fields = array_keys(cms_layout_builder_element_style_field_map());
        foreach (cms_layout_builder_footer_element_keys($out) as $ek) {
            $enabled_key = cms_layout_builder_element_styles_enabled_key($ek);
            $enabled = array_key_exists($enabled_key, $in)
                ? cms_layout_normalize_flag($in[$enabled_key])
                : cms_layout_builder_element_has_styles($in, $ek);
            $out[$enabled_key] = $enabled ? 1 : 0;
            foreach ($style_fields as $sf) {
                $k = $ek . '_' . $sf;
                $out[$k] = ($enabled && isset($in[$k])) ? trim((string) $in[$k]) : '';
            }
        }

        $out = cms_layout_builder_merge_element_colors_from_array($out, $in, 'footer');
        return cms_layout_builder_apply_element_colors($out, 'footer');
    }
}

if (!function_exists('cms_render_header_builder_html')) {
    /**
     * Visual storefront header from builder JSON config.
     *
     * @param array<string,mixed> $cfg
     * @param string $uploads_base
     * @return string
     */
    function cms_render_header_builder_html(array $cfg, $uploads_base = '', $profile_slug = '')
    {
        $cfg = cms_sanitize_header_builder_config($cfg);
        $uploads_base = rtrim((string) $uploads_base, '/') . '/';
        $profile_slug = cms_storefront_normalize_profile_slug($profile_slug);

        $bg = htmlspecialchars($cfg['bg_color'], ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars($cfg['text_color'], ENT_QUOTES, 'UTF-8');
        $accent = htmlspecialchars($cfg['accent_color'], ENT_QUOTES, 'UTF-8');
        $icon_bg = htmlspecialchars($cfg['icon_bg'], ENT_QUOTES, 'UTF-8');

        $promo_align = cms_layout_builder_sanitize_align(isset($cfg['promo_align']) ? $cfg['promo_align'] : '', 'center');
        $profile_attr = $profile_slug !== ''
            ? ' data-header-profile="' . htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8') . '"'
            : '';

        $el_vars = cms_layout_builder_build_element_color_style_vars($cfg, 'header');
        $nav_active_vars = cms_layout_builder_header_nav_active_style_vars($cfg);
        $html = '<header class="cms-ws-header"' . $profile_attr . ' style="--cms-hdr-bg:' . $bg . ';--cms-hdr-text:' . $text . ';--cms-hdr-accent:' . $accent . ';--cms-hdr-icon-bg:' . $icon_bg . ';' . $el_vars . ';' . $nav_active_vars . ';">';

        if ($cfg['promo_text'] !== '' && !empty($cfg['show_promo'])) {
            $html .= '<div class="cms-ws-header__promo cms-ws-header__promo--' . htmlspecialchars($promo_align, ENT_QUOTES, 'UTF-8') . '">'
                . $cfg['promo_text'] . '</div>';
        }

        $zones = array('left' => array(), 'center' => array(), 'right' => array());

        if (!empty($cfg['show_logo'])) {
            $logo_align = cms_layout_builder_sanitize_align(isset($cfg['logo_align']) ? $cfg['logo_align'] : '', 'left');
            $brand = '<div class="cms-ws-header__brand cms-ws-header__item">';
            $home = htmlspecialchars(cms_header_builder_logo_home_url(), ENT_QUOTES, 'UTF-8');
            $brand .= '<a href="' . $home . '" class="cms-ws-header__logo-link">';
            if ($cfg['logo_image'] !== '' && strpos($cfg['logo_image'], 'webshop/') === 0) {
                $src = htmlspecialchars($uploads_base . $cfg['logo_image'], ENT_QUOTES, 'UTF-8');
                $brand .= '<img src="' . $src . '" alt="Logo" class="cms-ws-header__logo">';
            } else {
                $brand .= '<span class="cms-ws-header__logo-placeholder">Logo</span>';
            }
            $brand .= '</a></div>';
            cms_header_builder_push_zone($zones, $logo_align, $brand);
        }

        $nav_links = cms_header_builder_resolve_nav_links($cfg);
        if (function_exists('cms_header_builder_filter_nav_links_for_bar')) {
            $nav_links = cms_header_builder_filter_nav_links_for_bar($nav_links, $cfg);
        }
        if (!empty($cfg['show_nav']) && !empty($nav_links)) {
            $nav_align = cms_layout_builder_sanitize_align(isset($cfg['nav_align']) ? $cfg['nav_align'] : '', 'center');
            $nav = '<nav class="cms-ws-header__nav cms-ws-header__item" aria-label="Main navigation">';
            foreach ($nav_links as $lnk) {
                $nav .= cms_render_header_nav_anchor(
                    isset($lnk['url']) ? (string) $lnk['url'] : '',
                    isset($lnk['label']) ? (string) $lnk['label'] : ''
                );
            }
            $nav .= '</nav>';
            cms_header_builder_push_zone($zones, $nav_align, $nav);
        }

        if ($cfg['phone'] !== '' && !empty($cfg['show_phone'])) {
            $phone_align = cms_layout_builder_sanitize_align(isset($cfg['phone_align']) ? $cfg['phone_align'] : '', 'right');
            $tel = preg_replace('/[^0-9+]/', '', $cfg['phone']);
            $phone = '<a class="cms-ws-header__phone cms-ws-header__item" href="tel:' . htmlspecialchars($tel, ENT_QUOTES, 'UTF-8') . '">'
                . '<i class="fa fa-phone"></i> ' . htmlspecialchars($cfg['phone'], ENT_QUOTES, 'UTF-8') . '</a>';
            cms_header_builder_push_zone($zones, $phone_align, $phone);
        }
        if (!empty($cfg['show_search'])) {
            $search_align = cms_layout_builder_sanitize_align(isset($cfg['search_align']) ? $cfg['search_align'] : '', 'right');
            cms_header_builder_push_zone($zones, $search_align,
                '<a class="cms-ws-header__icon cms-ws-header__icon--search cms-ws-header__item" href="#" title="Search" aria-label="Search"><i class="fa fa-search"></i></a>');
        }
        if (!empty($cfg['show_wishlist'])) {
            $wishlist_align = cms_layout_builder_sanitize_align(isset($cfg['wishlist_align']) ? $cfg['wishlist_align'] : '', 'right');
            cms_header_builder_push_zone($zones, $wishlist_align,
                '<a class="cms-ws-header__icon cms-ws-header__icon--wishlist cms-ws-header__item" href="' . htmlspecialchars(base_url('webshop/wishlist'), ENT_QUOTES, 'UTF-8') . '" title="Wishlist"><i class="fa fa-heart-o"></i></a>');
        }
        if (!empty($cfg['show_cart'])) {
            $cart_align = cms_layout_builder_sanitize_align(isset($cfg['cart_align']) ? $cfg['cart_align'] : '', 'right');
            cms_header_builder_push_zone($zones, $cart_align,
                '<a class="cms-ws-header__icon cms-ws-header__icon--cart cms-ws-header__item" href="' . htmlspecialchars(base_url('webshop/cart'), ENT_QUOTES, 'UTF-8') . '" title="Cart"><i class="fa fa-shopping-cart"></i><span class="cms-ws-header__badge">0</span></a>');
        }
        if (!empty($cfg['show_account'])) {
            $account_align = cms_layout_builder_sanitize_align(isset($cfg['account_align']) ? $cfg['account_align'] : '', 'right');
            cms_header_builder_push_zone($zones, $account_align,
                '<a class="cms-ws-header__icon cms-ws-header__icon--account cms-ws-header__item" href="' . htmlspecialchars(base_url('webshop/your_account'), ENT_QUOTES, 'UTF-8') . '" title="Account"><i class="fa fa-user"></i></a>');
        }

        $has_bar = false;
        foreach ($zones as $items) {
            if (!empty($items)) {
                $has_bar = true;
                break;
            }
        }
        if (!empty($cfg['show_button']) && trim(strip_tags((string) $cfg['button_text'])) !== '') {
            $btn_url = cms_page_public_url($cfg['button_link']);
            $btn_bg = cms_layout_builder_resolve_color($cfg, 'button_bg_color', 'accent_color');
            $btn_text = cms_layout_builder_button_label_on_bg(
                $btn_bg,
                cms_layout_builder_resolve_color($cfg, 'button_text_color', 'text_color')
            );
            $btn_style = 'color:' . htmlspecialchars($btn_text, ENT_QUOTES, 'UTF-8') . '!important'
                . ';background:' . htmlspecialchars($btn_bg, ENT_QUOTES, 'UTF-8')
                . ';-webkit-text-fill-color:' . htmlspecialchars($btn_text, ENT_QUOTES, 'UTF-8') . '!important';
            $btn_html = '<a href="' . htmlspecialchars($btn_url, ENT_QUOTES, 'UTF-8') . '" class="cms-ws-header__button cms-ws-header__item" style="' . $btn_style . '">'
                . (string) $cfg['button_text'] . '</a>';
            cms_header_builder_push_zone($zones, isset($cfg['button_align']) ? $cfg['button_align'] : 'right', $btn_html);
            $has_bar = true;
        }
        
        if ($has_bar) {
            $html .= '<div class="cms-ws-header__bar">' . cms_header_builder_render_zones_html($zones) . '</div>';
        }
        $html .= '</header>';

        return $html;
    }
}

if (!function_exists('cms_footer_builder_resolve_public_url')) {
    /**
     * @param string $raw
     * @return string
     */
    function cms_footer_builder_resolve_public_url($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '' || $raw === '#') {
            return '#';
        }
        if (strpos($raw, 'mailto:') === 0) {
            return $raw;
        }
        if (preg_match('#^https?://#i', $raw)) {
            $path = cms_footer_builder_link_storage_path($raw);
            return cms_page_public_url($path);
        }
        return cms_page_public_url(cms_footer_builder_link_storage_path($raw));
    }
}

if (!function_exists('cms_footer_builder_render_social_html')) {
    /**
     * @param array<string,mixed> $cfg
     * @param string              $uploads_base
     * @return string
     */
    function cms_footer_builder_render_social_html(array $cfg, $uploads_base = '')
    {
        $uploads_base = rtrim((string) $uploads_base, '/') . '/';
        $items = cms_footer_builder_get_social_items($cfg);
        $html = '<div class="cms-ws-footer__social">';
        $has = false;
        foreach ($items as $item) {
            $url = cms_footer_builder_resolve_public_url(isset($item['url']) ? $item['url'] : '');
            if ($url === '#') {
                continue;
            }
            $has = true;
            $title = isset($item['title']) ? (string) $item['title'] : 'Social';
            $icon_inner = '<i class="fa fa-link"></i>';
            $icon_image = isset($item['icon_image']) ? trim((string) $item['icon_image']) : '';
            if ($icon_image !== '') {
                $src = $icon_image;
                if (strpos($src, 'webshop/') === 0 && $uploads_base !== '/') {
                    $src = $uploads_base . $src;
                }
                $icon_inner = '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="" class="cms-ws-footer__social-icon-img">';
            } elseif (!empty($item['icon'])) {
                $icon_inner = '<i class="fa ' . htmlspecialchars((string) $item['icon'], ENT_QUOTES, 'UTF-8') . '"></i>';
            }
            $html .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="cms-ws-footer__social-link" title="'
                . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' . $icon_inner . '</a>';
        }
        return $html . '</div>';
    }
}

if (!function_exists('cms_footer_builder_render_legal_html')) {
    /**
     * @param array<string,mixed> $cfg
     * @return string
     */
    function cms_footer_builder_render_legal_html(array $cfg)
    {
        $pairs = array(
            array(
                'label' => isset($cfg['legal_link_1_label']) ? $cfg['legal_link_1_label'] : '',
                'url'   => isset($cfg['legal_link_1_url']) ? $cfg['legal_link_1_url'] : '',
            ),
            array(
                'label' => isset($cfg['legal_link_2_label']) ? $cfg['legal_link_2_label'] : '',
                'url'   => isset($cfg['legal_link_2_url']) ? $cfg['legal_link_2_url'] : '',
            ),
        );
        $html = '<div class="cms-ws-footer__legal">';
        $has = false;
        foreach ($pairs as $pair) {
            $label = trim((string) $pair['label']);
            if ($label === '') {
                continue;
            }
            $url = cms_footer_builder_resolve_public_url($pair['url']);
            $has = true;
            $html .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="cms-ws-footer__legal-link">'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
        }
        return $has ? $html . '</div>' : '';
    }
}

if (!function_exists('cms_render_footer_builder_html')) {
    /**
     * Multi-column footer (brand + menu columns + bottom legal bar).
     *
     * @param array<string,mixed> $cfg
     * @param string              $uploads_base
     * @param string              $profile_slug
     * @return string
     */
    function cms_render_footer_builder_html(array $cfg, $uploads_base = '', $profile_slug = '')
    {
        $cfg = cms_sanitize_footer_builder_config($cfg);
        $uploads_base = rtrim((string) $uploads_base, '/') . '/';
        $bg = htmlspecialchars($cfg['bg_color'], ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars($cfg['text_color'], ENT_QUOTES, 'UTF-8');
        $profile_slug = cms_storefront_normalize_profile_slug($profile_slug);
        $profile_attr = $profile_slug !== ''
            ? ' data-footer-profile="' . htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8') . '"'
            : '';

        $accent = htmlspecialchars(isset($cfg['accent_color']) ? $cfg['accent_color'] : '#c28913', ENT_QUOTES, 'UTF-8');
        $ftr_el_vars = cms_layout_builder_build_element_color_style_vars($cfg, 'footer');
        $html = '<footer class="cms-ws-footer gp-cms-ws-footer cms-ws-footer--columns"' . $profile_attr
            . ' style="--cms-ftr-bg:' . $bg . ';--cms-ftr-text:' . $text . ';--cms-ftr-accent:' . $accent . ';' . $ftr_el_vars . ';">';
        $html .= '<div class="cms-ws-footer__inner">';

        $fragments = cms_footer_builder_collect_fragments($cfg, $uploads_base);
        $brand_keys = cms_footer_builder_brand_element_keys();
        $bottom_keys = cms_footer_builder_bottom_element_keys();
        $brand_parts = array();
        $menu_parts = array();
        $bottom_parts = array();

        foreach (cms_footer_builder_get_element_order($cfg) as $key) {
            if (!isset($fragments[$key]) || $fragments[$key] === '') {
                continue;
            }
            if (in_array($key, $brand_keys, true)) {
                $brand_parts[] = $fragments[$key];
            } elseif (in_array($key, $bottom_keys, true)) {
                $bottom_parts[] = $fragments[$key];
            } else {
                $menu_parts[] = $fragments[$key];
            }
        }

        $has_brand = !empty($brand_parts);
        $has_menus = !empty($menu_parts);
        if ($has_brand || $has_menus) {
            $main_class = 'cms-ws-footer__main';
            if ($has_brand && $has_menus) {
                $main_class .= ' cms-ws-footer__main--split';
            } elseif ($has_menus) {
                $main_class .= ' cms-ws-footer__main--menus-only';
            } else {
                $main_class .= ' cms-ws-footer__main--brand-only';
            }
            $html .= '<div class="' . $main_class . '">';
            if ($has_brand) {
                $html .= '<div class="cms-ws-footer__brand">' . implode('', $brand_parts) . '</div>';
            }
            if ($has_menus) {
                $menu_cols = count($menu_parts);
                $menus_style = $menu_cols > 1
                    ? ' style="grid-template-columns:repeat(' . min($menu_cols, 6) . ',minmax(0,1fr))"'
                    : '';
                $html .= '<div class="cms-ws-footer__menus"' . $menus_style . '>' . implode('', $menu_parts) . '</div>';
            }
            $html .= '</div>';
        }

        if (!empty($bottom_parts)) {
            $bottom_class = 'cms-ws-footer__bottom';
            foreach ($bottom_parts as $bottom_part) {
                if (strpos((string) $bottom_part, 'cms-ws-footer__copy--align-center') !== false) {
                    $bottom_class .= ' cms-ws-footer__bottom--copy-center';
                    break;
                }
            }
            $html .= '<div class="' . $bottom_class . '">' . implode('', $bottom_parts) . '</div>';
        }

        $html .= '</div></footer>';
        return $html;
    }
}

if (!function_exists('cms_header_builder_prepare_google_analytics_value')) {
    /**
     * Validate GA4 measurement id or gtag snippet for layout builder save.
     *
     * @param string $raw
     * @return string|false Empty string, sanitized value, or false when invalid
     */
    function cms_header_builder_prepare_google_analytics_value($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return '';
        }
        if (!function_exists('storefront_value_has_invalid_ga_snippet')) {
            $CI =& get_instance();
            $CI->load->helper('storefront');
        }
        if (!preg_match('/<script/i', $raw) || !preg_match('/googletagmanager|gtag\s*\(/i', $raw)) {
            return false;
        }
        if (cms_header_builder_custom_tag_code_is_dangerous($raw)) {
            return false;
        }
        return storefront_value_has_invalid_ga_snippet($raw) ? false : $raw;
    }
}

if (!function_exists('cms_render_google_analytics_head_snippet')) {
    /**
     * @param string $raw Measurement id (G-…) or pasted gtag snippet
     * @return string
     */
    function cms_render_google_analytics_head_snippet($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '' || cms_header_builder_prepare_google_analytics_value($raw) === false) {
            return '';
        }
        return $raw . "\n";
    }
}

if (!function_exists('cms_render_custom_header_tags_head_snippet')) {
    /**
     * @param array<string,mixed> $cfg
     * @return string
     */
    function cms_render_custom_header_tags_head_snippet(array $cfg)
    {
        $html = '';
        foreach (cms_header_builder_get_custom_header_tags($cfg) as $item) {
            $code = isset($item['code']) ? trim((string) $item['code']) : '';
            $prepared = cms_header_builder_prepare_custom_header_tag_code($code);
            if ($prepared === false || $prepared === '') {
                continue;
            }
            $name = isset($item['name']) ? trim((string) $item['name']) : '';
            if ($name !== '') {
                $html .= '<!-- Custom header tag: ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . " -->\n";
            }
            $html .= $prepared . "\n";
        }
        return $html;
    }
}

if (!function_exists('cms_header_builder_preview_document')) {
    /**
     * @param array<string,mixed> $cfg
     * @param string $uploads_base
     * @return string
     */
    function cms_header_builder_preview_document(array $cfg, $uploads_base = '', $profile_slug = '')
    {
        $strip = cms_render_header_builder_html($cfg, $uploads_base, $profile_slug);
        $css = cms_header_builder_frontend_css();
        $ga_html = cms_render_google_analytics_head_snippet(isset($cfg['google_analytics']) ? $cfg['google_analytics'] : '');
        $custom_tags_html = cms_render_custom_header_tags_head_snippet($cfg);
        $preview_fit_css = 'html,body{margin:0;padding:0;height:auto;line-height:1;overflow:hidden;}'
            . '.cms-builder-preview-body--header-only{overflow-x:hidden;overflow-y:hidden;min-height:0!important;height:auto!important;line-height:1;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header{margin:0;display:block;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header{width:100%;max-width:100%;overflow:hidden;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header__bar{padding:8px 12px;overflow:hidden;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header__bar .cms-ws-header__inner--zones{max-width:100%!important;width:100%!important;gap:8px 12px;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header__zone{min-width:0;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header__zone--center{min-width:0;overflow:hidden;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header__nav{gap:8px;flex-wrap:nowrap;overflow:hidden;min-width:0;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header__nav a{font-size:12px;white-space:nowrap;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header__logo{max-height:36px;max-width:min(140px,22vw);object-fit:contain;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header__button{font-size:11px;line-height:1.2;padding:6px 10px;max-width:min(200px,30vw);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header__zone--right{flex-shrink:1;min-width:0;}';

        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . $ga_html
            . $custom_tags_html
            . '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">'
            . '<title>Header preview</title><style>' . $css . $preview_fit_css . '</style></head><body class="cms-builder-preview-body cms-builder-preview-body--header-only">'
            . $strip
            . '</body></html>';
    }
}

if (!function_exists('cms_footer_builder_preview_document')) {
    /**
     * Footer preview document. Compact mode stacks vertically for the layout-builder side iframe;
     * full mode shows the storefront footer layout (Open in new tab).
     *
     * @param array<string,mixed> $cfg
     * @param string $uploads_base
     * @param string $profile_slug
     * @param bool   $compact
     * @return string
     */
    function cms_footer_builder_preview_document(array $cfg, $uploads_base = '', $profile_slug = '', $compact = false)
    {
        $strip = cms_render_footer_builder_html($cfg, $uploads_base, $profile_slug);
        $css = cms_header_builder_frontend_css();
        $shared_css = 'html,body{margin:0;padding:0;line-height:1.45;}'
            . '.cms-builder-preview-body--footer-only .cms-ws-footer{margin:0;width:100%;}'
            . '.cms-builder-preview-body--footer-only .cms-ws-footer__tagline{word-wrap:break-word;overflow-wrap:anywhere;}'
            . '.cms-builder-preview-body--footer-only .cms-ws-footer__logo{max-height:44px;max-width:min(200px,100%);width:auto;object-fit:contain;display:block;}'
            . '.cms-builder-preview-body--footer-only .cms-ws-footer__menu-title,.cms-builder-preview-body--footer-only .cms-ws-footer__menu-list a{word-wrap:break-word;overflow-wrap:anywhere;}';

        if ($compact) {
            $preview_fit_css = $shared_css
                . 'body{height:auto;overflow:hidden;}'
                . '.cms-builder-preview-body--footer-only{overflow-x:hidden;min-height:0!important;height:auto!important;background:#f1f5f9;}'
                . '.cms-builder-preview-body--footer-only .cms-ws-footer__inner{max-width:100%;padding:20px 16px 16px;}'
                . '.cms-builder-preview-body--footer-only .cms-ws-footer__main,.cms-builder-preview-body--footer-only .cms-ws-footer__main--split{'
                . 'display:flex!important;flex-direction:column!important;grid-template-columns:1fr!important;gap:20px!important;}'
                . '.cms-builder-preview-body--footer-only .cms-ws-footer__menus{'
                . 'display:flex!important;flex-direction:column!important;grid-template-columns:1fr!important;gap:18px!important;width:100%;}'
                . '.cms-builder-preview-body--footer-only .cms-ws-footer__menu{width:100%;}'
                . '.cms-builder-preview-body--footer-only .cms-ws-footer__bottom{margin-top:8px;padding-top:16px;}'
                . '.cms-builder-preview-body--footer-only .cms-ws-footer__logo-placeholder{display:inline-block;max-width:140px;}';
            $body_class = 'cms-builder-preview-body cms-builder-preview-body--footer-only cms-builder-preview-body--footer-compact';
            $body_html = $strip;
        } else {
            $preview_fit_css = $shared_css
                . 'body.cms-builder-preview-body--footer-only{display:flex;flex-direction:column;min-height:100vh;background:#f1f5f9;}'
                . '.cms-builder-preview-page-hint{flex:1 1 auto;min-height:240px;display:flex;align-items:center;justify-content:center;'
                . 'color:#94a3b8;font-size:14px;background:#f8fafc;border-bottom:1px solid #e2e8f0;}'
                . '.cms-builder-preview-body--footer-only .cms-ws-footer{margin-top:auto;}'
                . '.cms-builder-preview-body--footer-only .cms-ws-footer__inner{max-width:1280px;margin:0 auto;}';
            $body_class = 'cms-builder-preview-body cms-builder-preview-body--footer-only';
            $body_html = '<div class="cms-builder-preview-page-hint">Page content area</div>' . $strip;
        }

        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">'
            . '<title>Footer preview</title><style>' . $css . $preview_fit_css . '</style></head>'
            . '<body class="' . $body_class . '">'
            . $body_html
            . '</body></html>';
    }
}

if (!function_exists('cms_webshop_inject_header_builder_strip_rows')) {
    /**
     * Expose layout-builder promo/phone on webshop header strips (API + local webshop).
     *
     * @param array<int, stdClass> $rows website_setting-shaped objects
     * @return array<int, stdClass>
     */
    function cms_webshop_inject_header_builder_strip_rows(array $rows)
    {
        $cfg = cms_layout_builder_load_json_config('header');
        if (!is_array($cfg)) {
            return $rows;
        }
        $cfg = cms_sanitize_header_builder_config($cfg);

        $has_promo = false;
        $has_phone = false;
        foreach ($rows as $r) {
            if (!is_object($r)) {
                continue;
            }
            $fk = isset($r->fields) ? strtolower(trim((string) $r->fields)) : '';
            if ($fk === 'promo_bar' || $fk === 'announcement_bar' || $fk === 'promo_text') {
                $has_promo = true;
            }
            if ($fk === 'phone_strip' || $fk === 'header_phone' || $fk === 'phone') {
                $has_phone = true;
            }
        }

        if (!$has_promo && !empty($cfg['show_promo']) && $cfg['promo_text'] !== '') {
            $o = new stdClass();
            $o->fields = 'promo_bar';
            $o->value = (string) $cfg['promo_text'];
            $o->icons = null;
            $o->section_type = 'header';
            $o->label = 'Promo bar';
            $o->sort_order = 2;
            $rows[] = $o;
        }
        if (!$has_phone && !empty($cfg['show_phone']) && $cfg['phone'] !== '') {
            $o = new stdClass();
            $o->fields = 'phone_strip';
            $o->value = (string) $cfg['phone'];
            $o->icons = null;
            $o->section_type = 'header';
            $o->label = 'Phone';
            $o->sort_order = 3;
            $rows[] = $o;
        }

        return $rows;
    }
}

if (!function_exists('cms_header_builder_frontend_css')) {
    function cms_header_builder_frontend_css()
    {
        return '.cms-builder-preview-body{margin:0;font-family:Inter,Segoe UI,sans-serif;background:#f1f5f9;}'
            . '.cms-builder-preview-body--header-only{background:#fff;min-height:0;height:auto;}'
            . '.cms-builder-preview-body--header-only .cms-ws-header{position:relative;}'
            . '.cms-builder-preview-page{min-height:240px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:14px;}'
            . '.cms-ws-header{background:var(--cms-hdr-bg,#0f172a);color:var(--cms-hdr-text,#fff);width:100%;box-shadow:0 2px 8px rgba(0,0,0,.12);}'
            . '.cms-ws-header__promo{background:var(--cms-hdr-el-promo-bg,var(--cms-hdr-accent,#3b82f6));color:var(--cms-hdr-el-promo-text,#fff);padding:6px 12px;font-size:var(--cms-hdr-el-promo-font-size,12px);font-weight:600;}'
            . '.cms-ws-header__promo--left{text-align:left;}'
            . '.cms-ws-header__promo--center{text-align:center;}'
            . '.cms-ws-header__promo--right{text-align:right;}'
            . '.cms-ws-header__bar{padding:12px 20px;}'
            . '.cms-ws-header__inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;gap:20px;flex-wrap:wrap;}'
            . '.cms-ws-header__inner--left{justify-content:flex-start;}'
            . '.cms-ws-header__inner--left .cms-ws-header__nav{flex:1 1 auto;}'
            . '.cms-ws-header__inner--left .cms-ws-header__actions{margin-left:auto;}'
            . '.cms-ws-header__inner--center{justify-content:center;}'
            . '.cms-ws-header__inner--center .cms-ws-header__nav{flex:0 1 auto;}'
            . '.cms-ws-header__inner--center .cms-ws-header__actions{margin-left:0;}'
            . '.cms-ws-header__inner--right{justify-content:flex-end;}'
            . '.cms-ws-header__inner--right .cms-ws-header__nav{flex:0 1 auto;}'
            . '.cms-ws-header__inner--right .cms-ws-header__actions{margin-left:0;}'
            . '.cms-ws-header__inner--split{display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);align-items:center;gap:16px;}'
            . '.cms-ws-header__inner--split .cms-ws-header__brand{justify-self:start;}'
            . '.cms-ws-header__inner--split .cms-ws-header__nav{flex:none;justify-self:center;justify-content:center;margin:0;}'
            . '.cms-ws-header__inner--split .cms-ws-header__actions{justify-self:end;margin-left:0;}'
            . '.cms-ws-header__bar .cms-ws-header__inner--zones{display:grid;grid-template-columns:minmax(0,max-content) minmax(0,1fr) minmax(0,max-content);align-items:center;width:100%;max-width:1280px;margin:0 auto;gap:12px clamp(16px,2vw,28px);min-height:48px;}'
            . '.cms-ws-header__bar .cms-ws-header__inner--zones .cms-ws-header__zone--left{grid-column:1;justify-self:start;}'
            . '.cms-ws-header__bar .cms-ws-header__inner--zones .cms-ws-header__zone--center{grid-column:2;justify-self:center;justify-content:center;width:100%;min-width:0;max-width:100%;overflow:hidden;position:static;transform:none;pointer-events:auto;}'
            . '.cms-ws-header__bar .cms-ws-header__inner--zones .cms-ws-header__zone--right{grid-column:3;justify-self:end;}'
            . '.cms-ws-header__bar .cms-ws-header__inner--zones .cms-ws-header__zone--center .cms-ws-header__nav{max-width:100%;overflow-x:auto;}'
            . '.cms-ws-header__zone{display:flex;flex-wrap:nowrap;align-items:center;gap:8px 12px;min-height:44px;min-width:0;}'
            . '.cms-ws-header__zone--left{justify-content:flex-start;}'
            . '.cms-ws-header__zone--center{justify-content:center;}'
            . '.cms-ws-header__zone--right{justify-content:flex-end;}'
            . '.cms-ws-header__inner--zones .cms-ws-header__zone--center .cms-ws-header__nav,.cms-ws-header__inner--zones .cms-ws-header__zone--right .cms-ws-header__button{position:static;width:auto;max-width:100%;}'
            . '.cms-ws-header__inner--zones .cms-ws-header__zone--right .cms-ws-header__button{margin-left:12px;}'
            . '@media(max-width:900px){.cms-ws-header__bar .cms-ws-header__inner--zones{grid-template-columns:minmax(0,1fr) auto;grid-template-areas:"logo actions" "nav nav";gap:8px 12px;}.cms-ws-header__bar .cms-ws-header__inner--zones .cms-ws-header__zone--left{grid-area:logo;grid-column:auto;}.cms-ws-header__bar .cms-ws-header__inner--zones .cms-ws-header__zone--right{grid-area:actions;grid-column:auto;}.cms-ws-header__bar .cms-ws-header__inner--zones .cms-ws-header__zone--center{grid-area:nav;grid-column:1/-1;justify-self:stretch;justify-content:flex-start;}}'
            . '.cms-ws-header__item{flex-shrink:0;}'
            . '.cms-ws-header__brand{flex-shrink:0;}'
            . '.cms-ws-header__logo{max-height:var(--cms-hdr-el-logo-height, 44px);width:var(--cms-hdr-el-logo-width, auto);display:block;}'
            . '.cms-ws-header__logo-placeholder{display:inline-block;width:100px;height:36px;background:rgba(255,255,255,.15);border-radius:6px;line-height:36px;text-align:center;font-size:11px;}'
            . '.cms-ws-header__nav{display:flex;flex-wrap:wrap;gap:16px;align-items:center;}'
            . '.cms-ws-header__nav a{color:var(--cms-hdr-el-nav-text,var(--cms-hdr-text,#fff));text-decoration:none;font-size:var(--cms-hdr-el-nav-font-size,14px);font-weight:500;opacity:.9;}'
            . '.cms-ws-header__nav a:hover,.cms-ws-header__nav a:focus-visible{color:var(--cms-hdr-el-nav-text,var(--cms-hdr-text,#0f172a));background:transparent;border-bottom:none;text-decoration:none;opacity:.85;}'
            . '.cms-ws-header__nav a.is-current,.cms-ws-header__nav a[aria-current="page"]{color:var(--cms-hdr-el-nav-text,var(--cms-hdr-text,#0f172a))!important;font-weight:500;border-top:none;border-left:none;border-right:none;border-bottom:var(--cms-hdr-el-nav-active-border-width,2px) solid var(--cms-hdr-el-nav-active,var(--cms-hdr-el-button-bg,#b9860b));border-radius:0;padding-bottom:6px;background:transparent;opacity:1;text-decoration:none!important;}'
            . '.cms-ws-header__actions{display:flex;align-items:center;gap:10px;margin-left:auto;}'
            . '.cms-ws-header__phone{color:var(--cms-hdr-el-phone-text,var(--cms-hdr-text));font-size:var(--cms-hdr-el-phone-font-size,13px);text-decoration:none;white-space:nowrap;}'
            . '.cms-ws-header__icon{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:8px;background:var(--cms-hdr-icon-bg,transparent);color:var(--cms-hdr-text);text-decoration:none;font-size:18px;position:relative;}'
            . '.cms-ws-header__icon--search{color:var(--cms-hdr-el-search-text,var(--cms-hdr-text));background:var(--cms-hdr-el-search-bg,var(--cms-hdr-icon-bg,transparent));font-size:var(--cms-hdr-el-search-font-size,18px);}'
            . '.cms-ws-header__icon--wishlist{color:var(--cms-hdr-el-wishlist-text,var(--cms-hdr-text));background:var(--cms-hdr-el-wishlist-bg,var(--cms-hdr-icon-bg,transparent));font-size:var(--cms-hdr-el-wishlist-font-size,18px);}'
            . '.cms-ws-header__icon--cart{color:var(--cms-hdr-el-cart-text,var(--cms-hdr-text));background:var(--cms-hdr-el-cart-bg,var(--cms-hdr-icon-bg,transparent));font-size:var(--cms-hdr-el-cart-font-size,18px);}'
            . '.cms-ws-header__icon--account{color:var(--cms-hdr-el-account-text,var(--cms-hdr-text));background:var(--cms-hdr-el-account-bg,var(--cms-hdr-icon-bg,transparent));font-size:var(--cms-hdr-el-account-font-size,18px);}'
            . '.cms-ws-header__icon:hover{background:rgba(255,255,255,.1);}'
            . '.cms-ws-header__badge{position:absolute;top:4px;right:4px;background:var(--cms-hdr-accent);color:#fff;font-size:10px;min-width:16px;height:16px;line-height:16px;border-radius:8px;text-align:center;padding:0 4px;}'
            . '.cms-ws-header__button{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 18px;border-radius:8px;background:var(--cms-hdr-el-button-bg,var(--cms-hdr-accent,#3b82f6));color:var(--cms-hdr-el-button-text,#fff);font-size:var(--cms-hdr-el-button-font-size,14px);font-weight:600;text-decoration:none;line-height:1.3;white-space:nowrap;transition:opacity .2s,transform .15s;}'
            . '.cms-ws-header__button:hover,.cms-ws-header__button:focus,.cms-ws-header__button:visited{color:var(--cms-hdr-el-button-text,#fff);-webkit-text-fill-color:var(--cms-hdr-el-button-text,#fff);text-decoration:none;}'
            . '.cms-ws-header__button:hover{background:var(--cms-hdr-el-button-hover,color-mix(in srgb,var(--cms-hdr-el-button-bg,var(--cms-hdr-accent,#3b82f6)) 82%,#000))!important;opacity:1;}'
            . '.cms-ws-header__button *,.cms-ws-header__button strong,.cms-ws-header__button b,.cms-ws-header__button span{color:inherit!important;-webkit-text-fill-color:inherit!important;font-weight:700;}'
            . '.cms-ws-header__button i.fa{margin-right:2px;}'
            . '.cms-ws-footer{background:var(--cms-ftr-bg,#fff);color:var(--cms-ftr-text,#1e293b);padding:48px 24px 28px;margin-top:auto;border-top:1px solid #f1f5f9;}'
            . '.cms-ws-footer__inner{max-width:1280px;margin:0 auto;}'
            . '.cms-ws-footer__main{display:grid;grid-template-columns:minmax(240px,1.2fr) minmax(0,2.8fr);gap:48px 40px;align-items:start;padding-bottom:32px;}'
            . '.cms-ws-footer__brand{display:flex;flex-direction:column;align-items:flex-start;gap:0;}'
            . '.cms-ws-footer__brand-link{display:inline-block;margin-bottom:20px;text-decoration:none;}'
            . '.cms-ws-footer__logo{max-height:44px;width:auto;display:block;}'
            . '.cms-ws-footer__logo-placeholder{display:inline-block;min-width:120px;height:40px;background:#f1f5f9;border-radius:6px;line-height:40px;text-align:center;font-size:12px;color:#64748b;}'
            . '.cms-ws-footer__tagline{font-size:var(--cms-ftr-el-tagline-font-size,13px);margin:0 0 20px;line-height:1.6;max-width:280px;color:var(--cms-ftr-el-tagline-text,#64748b);}'
            . '.cms-ws-footer__social{display:flex;gap:14px;margin:0 0 24px;}'
            . '.cms-ws-footer__social-link{color:var(--cms-ftr-el-social-text,#94a3b8);font-size:var(--cms-ftr-el-social-font-size,22px);text-decoration:none;transition:color .2s;}'
            . '.cms-ws-footer__social-link:hover{color:var(--cms-ftr-accent,#c28913);}'
            . '.cms-ws-footer__social-icon-img{width:22px;height:22px;object-fit:contain;display:block;}'
            . '.cms-ws-footer__newsletter{margin-top:8px;max-width:280px;width:100%;}'
            . '.cms-ws-footer__newsletter-title{font-size:var(--cms-ftr-el-newsletter-font-size,0.875rem);font-weight:700;margin:0 0 0.5rem;color:var(--cms-ftr-el-newsletter-text,var(--cms-ftr-text));}'
            . '.cms-ws-footer__newsletter-desc{font-size:10px;margin:0 0 16px;line-height:1.6;color:#64748b;}'
            . '.cms-ws-footer__newsletter-form{display:flex;background:#fff;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;}'
            . '.cms-ws-footer__newsletter-input{flex:1;border:none;padding:10px 12px;font-size:12px;outline:none;background:transparent;width:100%;min-width:0;color:#334155;}'
            . '.cms-ws-footer__newsletter-btn{background:var(--cms-ftr-el-newsletter-btn-bg,var(--cms-ftr-accent,#c28913));color:#fff;border:none;padding:0 14px;cursor:pointer;font-size:14px;transition:background .2s;display:flex;align-items:center;justify-content:center;}'
            . '.cms-ws-footer__newsletter-btn:hover{filter:brightness(.92);}'
            . '.cms-ws-footer__menus{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:28px 32px;align-items:start;min-width:0;width:100%;}'
            . '.cms-ws-footer__main--split{display:grid;grid-template-columns:minmax(240px,1.2fr) minmax(0,2.8fr);}'
            . '.cms-ws-footer__main--menus-only,.cms-ws-footer__main--brand-only{display:grid;grid-template-columns:1fr;}'
            . '.cms-ws-footer--columns .cms-ws-footer__menus{display:grid;width:100%;}'
            . '.cms-builder-preview-body .cms-ws-footer__main--split{grid-template-columns:minmax(200px,1fr) minmax(0,2.2fr)!important;}'
            . '.cms-builder-preview-body .cms-ws-footer__menus{display:grid!important;width:100%!important;min-width:0!important;}'
            . '.cms-ws-footer__menu{display:flex;flex-direction:column;min-width:0;}'
            . '.cms-ws-footer__menu-title{font-size:var(--cms-ftr-menu-title-size,15px);font-weight:700;margin:0 0 18px;color:var(--cms-ftr-menu-title,var(--cms-ftr-text));}'
            . '.cms-ws-footer__menu-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px;}'
            . '.cms-ws-footer__menu-title--align-left,.cms-ws-footer__tagline--align-left,.cms-ws-footer__newsletter--align-left,.cms-ws-footer__copy--align-left{text-align:left;}'
            . '.cms-ws-footer__menu-title--align-center,.cms-ws-footer__tagline--align-center,.cms-ws-footer__newsletter--align-center,.cms-ws-footer__copy--align-center{text-align:center;}'
            . '.cms-ws-footer__menu-title--align-right,.cms-ws-footer__tagline--align-right,.cms-ws-footer__newsletter--align-right,.cms-ws-footer__copy--align-right{text-align:right;}'
            . '.cms-ws-footer__menu-list--align-left{align-items:flex-start;}'
            . '.cms-ws-footer__menu-list--align-center{align-items:center;}'
            . '.cms-ws-footer__menu-list--align-right{align-items:flex-end;}'
            . '.cms-ws-footer__menu--align-left{align-items:flex-start;}'
            . '.cms-ws-footer__menu--align-center{align-items:center;}'
            . '.cms-ws-footer__menu--align-right{align-items:flex-end;}'
            . '.cms-ws-footer__social-wrap--align-left .cms-ws-footer__social{justify-content:flex-start;}'
            . '.cms-ws-footer__social-wrap--align-center .cms-ws-footer__social{justify-content:center;}'
            . '.cms-ws-footer__social-wrap--align-right .cms-ws-footer__social{justify-content:flex-end;}'
            . '.cms-ws-footer__legal--align-left{justify-content:flex-start;margin-left:0;margin-right:auto;}'
            . '.cms-ws-footer__legal--align-center{justify-content:center;margin-left:auto;margin-right:auto;}'
            . '.cms-ws-footer__legal--align-right{justify-content:flex-end;margin-left:auto;margin-right:0;}'
            . '.cms-ws-footer__menu-list a{color:var(--cms-ftr-menu-text,#64748b);text-decoration:none;font-size:var(--cms-ftr-menu-text-size,12px);line-height:1.4;transition:color .2s;}'
            . '.cms-ws-footer__menu-list a:hover{color:var(--cms-ftr-accent,#c28913);}'
            . '.cms-ws-footer__bottom{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px 24px;padding-top:24px;border-top:1px solid #f1f5f9;}'
            . '.cms-ws-footer__copy{font-size:var(--cms-ftr-el-copyright-font-size,11px);margin:0;color:var(--cms-ftr-el-copyright-text,#94a3b8);}'
            . '.cms-ws-footer__legal{display:flex;flex-wrap:wrap;gap:20px;align-items:center;}'
            . '.cms-ws-footer__legal-link{font-size:var(--cms-ftr-el-legal-font-size,11px);color:var(--cms-ftr-el-legal-text,#94a3b8);text-decoration:none;transition:color .2s;}'
            . '.cms-ws-footer__legal-link:hover{color:var(--cms-ftr-accent,#c28913);}'
            . '@media(max-width:900px){.cms-ws-footer__main:not(.cms-ws-footer__main--split){grid-template-columns:1fr;gap:32px;}.cms-ws-footer__main--split{grid-template-columns:1fr;gap:32px;}.cms-ws-footer__menus{grid-template-columns:repeat(2,minmax(0,1fr));}}'
            . '.cms-builder-preview-body .cms-ws-footer__main--split{grid-template-columns:minmax(200px,1fr) minmax(0,2.2fr)!important;}'
            . '@media(max-width:560px){.cms-ws-footer{padding:36px 16px 24px;}.cms-ws-footer__menus{grid-template-columns:1fr;}.cms-ws-footer__bottom{flex-direction:column;align-items:flex-start;}}'
            . '.cms-ws-header__promo { width: var(--cms-hdr-el-promo-width, auto); height: var(--cms-hdr-el-promo-height, auto); padding-top: var(--cms-hdr-el-promo-padding-top, 6px); padding-bottom: var(--cms-hdr-el-promo-padding-bottom, 6px); font-size: var(--cms-hdr-el-promo-font-size, 12px); position: var(--cms-hdr-el-promo-position, static); top: var(--cms-hdr-el-promo-top, auto); bottom: var(--cms-hdr-el-promo-bottom, auto); left: var(--cms-hdr-el-promo-left, auto); right: var(--cms-hdr-el-promo-right, auto); }'
            . '.cms-ws-header__brand { width: var(--cms-hdr-el-logo-width, auto); height: var(--cms-hdr-el-logo-height, auto); padding-top: var(--cms-hdr-el-logo-padding-top, 0); padding-bottom: var(--cms-hdr-el-logo-padding-bottom, 0); font-size: var(--cms-hdr-el-logo-font-size, inherit); position: var(--cms-hdr-el-logo-position, static); top: var(--cms-hdr-el-logo-top, auto); bottom: var(--cms-hdr-el-logo-bottom, auto); left: var(--cms-hdr-el-logo-left, auto); right: var(--cms-hdr-el-logo-right, auto); }'
            . '.cms-ws-header__nav { width: var(--cms-hdr-el-nav-width, auto); height: var(--cms-hdr-el-nav-height, auto); padding-top: var(--cms-hdr-el-nav-padding-top, 0); padding-bottom: var(--cms-hdr-el-nav-padding-bottom, 0); font-size: var(--cms-hdr-el-nav-font-size, inherit); position: var(--cms-hdr-el-nav-position, static); top: var(--cms-hdr-el-nav-top, auto); bottom: var(--cms-hdr-el-nav-bottom, auto); left: var(--cms-hdr-el-nav-left, auto); right: var(--cms-hdr-el-nav-right, auto); }'
            . '.cms-ws-header__phone { width: var(--cms-hdr-el-phone-width, auto); height: var(--cms-hdr-el-phone-height, auto); padding-top: var(--cms-hdr-el-phone-padding-top, 0); padding-bottom: var(--cms-hdr-el-phone-padding-bottom, 0); font-size: var(--cms-hdr-el-phone-font-size, 13px); position: var(--cms-hdr-el-phone-position, static); top: var(--cms-hdr-el-phone-top, auto); bottom: var(--cms-hdr-el-phone-bottom, auto); left: var(--cms-hdr-el-phone-left, auto); right: var(--cms-hdr-el-phone-right, auto); }'
            . '.cms-ws-header__icon--search { width: var(--cms-hdr-el-search-width, 40px); height: var(--cms-hdr-el-search-height, 40px); padding-top: var(--cms-hdr-el-search-padding-top, 0); padding-bottom: var(--cms-hdr-el-search-padding-bottom, 0); font-size: var(--cms-hdr-el-search-font-size, 18px); position: var(--cms-hdr-el-search-position, relative); top: var(--cms-hdr-el-search-top, auto); bottom: var(--cms-hdr-el-search-bottom, auto); left: var(--cms-hdr-el-search-left, auto); right: var(--cms-hdr-el-search-right, auto); }'
            . '.cms-ws-header__icon--wishlist { width: var(--cms-hdr-el-wishlist-width, 40px); height: var(--cms-hdr-el-wishlist-height, 40px); padding-top: var(--cms-hdr-el-wishlist-padding-top, 0); padding-bottom: var(--cms-hdr-el-wishlist-padding-bottom, 0); font-size: var(--cms-hdr-el-wishlist-font-size, 18px); position: var(--cms-hdr-el-wishlist-position, relative); top: var(--cms-hdr-el-wishlist-top, auto); bottom: var(--cms-hdr-el-wishlist-bottom, auto); left: var(--cms-hdr-el-wishlist-left, auto); right: var(--cms-hdr-el-wishlist-right, auto); }'
            . '.cms-ws-header__icon--cart { width: var(--cms-hdr-el-cart-width, 40px); height: var(--cms-hdr-el-cart-height, 40px); padding-top: var(--cms-hdr-el-cart-padding-top, 0); padding-bottom: var(--cms-hdr-el-cart-padding-bottom, 0); font-size: var(--cms-hdr-el-cart-font-size, 18px); position: var(--cms-hdr-el-cart-position, relative); top: var(--cms-hdr-el-cart-top, auto); bottom: var(--cms-hdr-el-cart-bottom, auto); left: var(--cms-hdr-el-cart-left, auto); right: var(--cms-hdr-el-cart-right, auto); }'
            . '.cms-ws-header__icon--account { width: var(--cms-hdr-el-account-width, 40px); height: var(--cms-hdr-el-account-height, 40px); padding-top: var(--cms-hdr-el-account-padding-top, 0); padding-bottom: var(--cms-hdr-el-account-padding-bottom, 0); font-size: var(--cms-hdr-el-account-font-size, 18px); position: var(--cms-hdr-el-account-position, relative); top: var(--cms-hdr-el-account-top, auto); bottom: var(--cms-hdr-el-account-bottom, auto); left: var(--cms-hdr-el-account-left, auto); right: var(--cms-hdr-el-account-right, auto); }'
            . '.cms-ws-header__button { width: var(--cms-hdr-el-button-width, auto); height: var(--cms-hdr-el-button-height, auto); padding-top: var(--cms-hdr-el-button-padding-top, 10px); padding-bottom: var(--cms-hdr-el-button-padding-bottom, 10px); font-size: var(--cms-hdr-el-button-font-size, 14px); position: var(--cms-hdr-el-button-position, static); top: var(--cms-hdr-el-button-top, auto); bottom: var(--cms-hdr-el-button-bottom, auto); left: var(--cms-hdr-el-button-left, auto); right: var(--cms-hdr-el-button-right, auto); }'
            . '.cms-ws-header__nav a { font-size: var(--cms-hdr-el-nav-font-size, 14px); }'
            . cms_layout_builder_footer_element_style_css_rules();
    }
}

if (!function_exists('cms_read_html_field_from_post')) {
    /**
     * Read raw HTML from POST (base64 field preferred — avoids XSS filter / WAF stripping style tags).
     *
     * @param string      $field POST key, e.g. page_text
     * @param object|null $ci    CodeIgniter instance
     * @return string
     */
    function cms_read_html_field_from_post($field, $ci = null)
    {
        $field = trim((string) $field);
        if ($field === '') {
            return '';
        }

        $b64_field = $field . '_b64';
        $b64 = null;
        if (isset($_POST[$b64_field])) {
            $b64 = $_POST[$b64_field];
        } elseif ($ci !== null && isset($ci->input)) {
            $b64 = $ci->input->post($b64_field, false);
        }

        if (is_string($b64) && trim($b64) !== '') {
            $decoded = base64_decode($b64, true);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        $raw = null;
        if (isset($_POST[$field])) {
            $raw = $_POST[$field];
        } elseif ($ci !== null && isset($ci->input)) {
            $raw = $ci->input->post($field, false);
        }

        return is_string($raw) ? $raw : '';
    }
}
