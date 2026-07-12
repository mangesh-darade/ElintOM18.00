<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * llms.txt — plain markdown for AI assistants (/llms.txt on storefront).
 */

if (!function_exists('cms_llms_txt_sanitize_markdown')) {
    /**
     * @param string $markdown
     * @return string
     */
    function cms_llms_txt_sanitize_markdown($markdown)
    {
        $markdown = str_replace(array("\r\n", "\r"), "\n", (string) $markdown);
        $markdown = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $markdown);
        $markdown = preg_replace('/<\?php.*?\?>/is', '', $markdown);

        return trim($markdown);
    }
}

if (!function_exists('cms_llms_txt_is_invalid_stored_content')) {
    /**
     * Detect SQL migrations, PHP, or other non-llms.txt content pasted by mistake.
     *
     * @param string $content
     * @return bool
     */
    function cms_llms_txt_is_invalid_stored_content($content)
    {
        $content = trim((string) $content);
        if ($content === '') {
            return false;
        }

        $markers = array(
            'ALTER TABLE',
            'ADD COLUMN',
            'UPDATE sma_',
            'sma_cms_tags_master',
            'Tag form visibility',
            '-- =============================================================================',
            'CREATE TABLE',
            'DROP TABLE',
            '<?php',
            'defined(\'BASEPATH\')',
        );
        foreach ($markers as $marker) {
            if (stripos($content, $marker) !== false) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('cms_llms_txt_fix_storefront_urls_in_markdown')) {
    /**
     * Fix common wrong local dev URLs (nested /ElintOm/ElintOm_Webshop_PHP_8.4).
     *
     * @param string $content
     * @param string $storefront_url
     * @return string
     */
    function cms_llms_txt_fix_storefront_urls_in_markdown($content, $storefront_url)
    {
        $content = (string) $content;
        $storefront_url = rtrim(trim((string) $storefront_url), '/');
        if ($content === '' || $storefront_url === '') {
            return $content;
        }

        $wrongPath = '/ElintOm/ElintOm_Webshop_PHP_8.4';
        $rightPath = parse_url($storefront_url, PHP_URL_PATH);
        if (!is_string($rightPath) || $rightPath === '') {
            $rightPath = '/ElintOm_Webshop_PHP_8.4';
        }
        $rightPath = rtrim($rightPath, '/');

        return str_replace($wrongPath, $rightPath, $content);
    }
}

if (!function_exists('cms_llms_txt_prepare_stored_content')) {
    /**
     * Sanitize, reject invalid pasted content, fix storefront URLs.
     *
     * @param string $content
     * @param string $site_name
     * @param string $storefront_url
     * @return string
     */
    function cms_llms_txt_prepare_stored_content($content, $site_name, $storefront_url)
    {
        $content = cms_llms_txt_sanitize_markdown($content);
        if (cms_llms_txt_is_invalid_stored_content($content)) {
            return cms_llms_txt_default_template($site_name, $storefront_url);
        }

        return cms_llms_txt_fix_storefront_urls_in_markdown($content, $storefront_url);
    }
}

if (!function_exists('cms_llms_txt_default_template')) {
    /**
     * Starter block (Gulf Pharmacy–style guide example).
     *
     * @param string $site_name
     * @param string $base_url Storefront base URL without trailing slash
     * @return string
     */
    function cms_llms_txt_default_template($site_name, $base_url)
    {
        $site_name = trim((string) $site_name);
        if ($site_name === '') {
            $site_name = 'Your Store';
        }
        $base_url = rtrim(trim((string) $base_url), '/');
        if ($base_url === '') {
            $base_url = 'https://yourdomain.com';
        }

        $shop = $base_url . '/webshop';

        return implode("\n", array(
            '## About',
            $site_name . ' — online pharmacy and wellness store.',
            '',
            '## Products',
            'Vitamins, Skincare, Medical Devices',
            '',
            '## Key URLs',
            '- [Home](' . $base_url . '/)',
            '- [Shop](' . $shop . ')',
            '- [Contact](' . $shop . '/contact_us)',
        ));
    }
}

if (!function_exists('cms_llms_txt_fetch_storefront_preview')) {
    /**
     * Live llms.txt from the storefront (auto-generated, no custom block when disabled in DB).
     *
     * @param string $storefront_url
     * @return string
     */
    function cms_llms_txt_fetch_storefront_preview($storefront_url)
    {
        $storefront_url = rtrim(trim((string) $storefront_url), '/');
        if ($storefront_url === '') {
            return '';
        }

        $url = $storefront_url . '/llms.txt';
        $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 5,
                'ignore_errors' => true,
                'header' => "Accept: text/plain\r\n",
            ),
        ));
        $body = @file_get_contents($url, false, $ctx);
        if (!is_string($body) || trim($body) === '') {
            return '';
        }

        $body = trim($body);
        if (function_exists('cms_site_settings_is_html_response') && cms_site_settings_is_html_response($body)) {
            return '';
        }

        return cms_llms_txt_is_valid_preview($body) ? $body : '';
    }
}

if (!function_exists('cms_llms_txt_is_valid_preview')) {
    /**
     * @param string $body
     * @return bool
     */
    function cms_llms_txt_is_valid_preview($body)
    {
        $body = trim((string) $body);
        if ($body === '') {
            return false;
        }

        if (function_exists('cms_site_settings_is_html_response') && cms_site_settings_is_html_response($body)) {
            return false;
        }

        return substr($body, 0, 1) === '#' || stripos($body, '## ') !== false;
    }
}

if (!function_exists('cms_llms_txt_build_admin_live_preview')) {
    /**
     * Build llms.txt output preview from auto-generated block + optional custom markdown.
     *
     * @param string $auto_preview
     * @param string $custom_content
     * @param bool   $include_custom
     * @return string
     */
    function cms_llms_txt_build_admin_live_preview($auto_preview, $custom_content, $include_custom = true)
    {
        $auto_preview = trim((string) $auto_preview);
        $custom_content = trim((string) $custom_content);

        if ($auto_preview === '') {
            return $custom_content === '' ? '' : $custom_content . "\n";
        }

        if (!$include_custom || $custom_content === '') {
            return $auto_preview . "\n";
        }

        return rtrim($auto_preview) . "\n\n" . $custom_content . "\n";
    }
}

if (!function_exists('cms_llms_txt_build_auto_generated_preview')) {
    /**
     * Fallback preview when storefront URL cannot be fetched (matches live llms.txt shape).
     *
     * @param string $site_name
     * @param string $storefront_url
     * @param array  $cms_pages rows with page_name, url
     * @return string
     */
    function cms_llms_txt_build_auto_generated_preview($site_name, $storefront_url, array $cms_pages = array())
    {
        $site_name = trim((string) $site_name);
        if ($site_name === '') {
            $site_name = 'Store';
        }
        $base = rtrim(trim((string) $storefront_url), '/');
        if ($base === '') {
            $base = 'https://yourdomain.com';
        }

        $lines = array(
            '# ' . $site_name,
            '',
            '> Online store — product catalog, CMS pages, and sitemaps for AI assistants.',
            '',
            'Curated links for AI assistants. Search crawlers should use [robots.txt](' . $base . '/robots.txt) and [sitemap.xml](' . $base . '/sitemap.xml).',
            '',
            '## Pages',
            '- [Home](' . $base . '/): Storefront landing',
            '- [Shop](' . $base . '/webshop): Product catalog home',
            '- [About](' . $base . '/webshop/about_us): About us',
            '- [Contact](' . $base . '/webshop/contact_us): Contact information',
        );

        $legalSlugs = array(
            'privacy-policy', 'privacy_policy', 'privacy',
            'terms', 'terms-of-service', 'terms_of_service', 'terms-and-conditions',
            'legal', 'cookie-policy', 'cookies',
        );
        $optional = array();
        $seen = array(
            $base . '/' => true,
            $base . '/webshop' => true,
            $base . '/webshop/about_us' => true,
            $base . '/webshop/contact_us' => true,
        );

        foreach ($cms_pages as $row) {
            if (!is_array($row)) {
                continue;
            }
            $title = isset($row['page_name']) ? trim((string) $row['page_name']) : '';
            $cmsUrl = isset($row['url']) ? trim((string) $row['url']) : '';
            if ($title === '' || $cmsUrl === '') {
                continue;
            }
            $href = $base . '/webshop/' . ltrim($cmsUrl, '/');
            if (isset($seen[$href])) {
                continue;
            }
            $seen[$href] = true;
            $slug = strtolower(ltrim(str_replace('_', '-', $cmsUrl), '/'));
            $isLegal = in_array($slug, $legalSlugs, true) || in_array(str_replace('-', '_', $slug), $legalSlugs, true);
            $item = '- [' . $title . '](' . $href . '): ' . ($isLegal ? 'Legal page' : 'CMS page');
            if ($isLegal) {
                $optional[] = $item;
            } else {
                $lines[] = $item;
            }
        }

        if (!empty($optional)) {
            $lines[] = '';
            $lines[] = '## Optional';
            foreach ($optional as $item) {
                $lines[] = $item;
            }
        }

        $lines[] = '';
        $lines[] = '## Catalog';
        $lines[] = '- [Categories sitemap](' . $base . '/sitemap-categories.xml): Product category URLs';
        $lines[] = '- [Products sitemap](' . $base . '/sitemap-products.xml): Product detail URLs';

        return implode("\n", $lines);
    }
}

if (!function_exists('cms_llms_txt_resolve_storefront_base_url')) {
    /**
     * @return string
     */
    function cms_llms_txt_resolve_storefront_base_url()
    {
        $CI = function_exists('get_instance') ? get_instance() : null;
        if ($CI) {
            $CI->load->config('elintom_api', false, true);
            $raw = $CI->config->item('webshop_storefront_base_url', 'elintom_api');
            if (is_string($raw) && trim($raw) !== '') {
                return rtrim(trim($raw), '/');
            }
        }

        $erpBase = rtrim((string) base_url(), '/');
        $parsed = parse_url($erpBase);
        if (empty($parsed['scheme']) || empty($parsed['host'])) {
            return $erpBase;
        }

        $port = isset($parsed['port']) ? ':' . (int) $parsed['port'] : '';
        $erpPath = isset($parsed['path']) ? rtrim((string) $parsed['path'], '/') : '';
        $webshopFolder = 'ElintOm_Webshop_PHP_8.4';
        $candidates = array(
            dirname(rtrim(FCPATH, '/\\')) . DIRECTORY_SEPARATOR . $webshopFolder,
            rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $webshopFolder,
            rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . $webshopFolder,
        );

        foreach ($candidates as $path) {
            if (!is_string($path) || $path === '') {
                continue;
            }
            $real = realpath($path);
            if ($real === false || !is_dir($real)) {
                continue;
            }

            $erpReal = realpath(rtrim(FCPATH, '/\\'));
            $erpParent = $erpReal !== false ? dirname($erpReal) : '';
            $webshopParent = dirname($real);

            // Sibling folders under www (e.g. /ElintOm + /ElintOm_Webshop_PHP_8.4 on WAMP).
            if ($erpParent !== '' && $webshopParent === $erpParent) {
                return $parsed['scheme'] . '://' . $parsed['host'] . $port . '/' . basename($real);
            }

            // Nested inside the ERP install folder.
            if ($erpReal !== false && strpos($real, $erpReal) === 0) {
                $rel = trim(str_replace('\\', '/', substr($real, strlen($erpReal))), '/');
                if ($rel !== '') {
                    $pathPart = $erpPath !== '' ? $erpPath . '/' . $rel : '/' . $rel;

                    return $parsed['scheme'] . '://' . $parsed['host'] . $port . $pathPart;
                }
            }
        }

        return $erpBase;
    }
}
