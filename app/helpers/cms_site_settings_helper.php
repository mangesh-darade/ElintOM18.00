<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('cms_site_settings_storefront_base_url')) {
    /**
     * @return string
     */
    function cms_site_settings_storefront_base_url()
    {
        $CI = function_exists('get_instance') ? get_instance() : null;
        if ($CI) {
            $CI->load->helper('cms_llms_txt');
        } elseif (!function_exists('cms_llms_txt_resolve_storefront_base_url')) {
            return rtrim((string) base_url(), '/');
        }
        return cms_llms_txt_resolve_storefront_base_url();
    }
}

if (!function_exists('cms_site_settings_is_html_response')) {
    /**
     * Detect error pages / HTML returned instead of plain text.
     *
     * @param string $body
     * @return bool
     */
    function cms_site_settings_is_html_response($body)
    {
        $body = ltrim((string) $body);
        if ($body === '') {
            return false;
        }

        // XML sitemaps and other XML responses are not HTML error pages.
        if (stripos($body, '<?xml') === 0) {
            return false;
        }

        return stripos($body, '<!DOCTYPE') !== false
            || stripos($body, '<html') !== false
            || stripos($body, '<head') !== false
            || stripos($body, '<body') !== false;
    }
}

if (!function_exists('cms_site_settings_is_valid_robots_txt')) {
    /**
     * @param string $body
     * @return bool
     */
    function cms_site_settings_is_valid_robots_txt($body)
    {
        $body = trim((string) $body);
        if ($body === '' || cms_site_settings_is_html_response($body)) {
            return false;
        }

        return (bool) preg_match('/^(User-agent|Sitemap|Disallow|Allow|Host|#)/mi', $body);
    }
}

if (!function_exists('cms_site_settings_fetch_storefront_text')) {
    /**
     * @param string $base
     * @param string $path e.g. /robots.txt
     * @param string $accept
     * @return string
     */
    function cms_site_settings_fetch_storefront_text($base, $path, $accept = 'text/plain')
    {
        $base = rtrim(trim((string) $base), '/');
        $path = '/' . ltrim(trim((string) $path), '/');
        if ($base === '') {
            return '';
        }

        $url = $base . $path;
        $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 8,
                'ignore_errors' => true,
                'header' => 'Accept: ' . $accept . "\r\n",
            ),
        ));
        $body = @file_get_contents($url, false, $ctx);
        if (!is_string($body) || trim($body) === '') {
            return '';
        }

        if (cms_site_settings_is_html_response($body)) {
            return '';
        }

        return $body;
    }
}

if (!function_exists('cms_site_settings_fetch_robots_txt')) {
    /**
     * @param string $base
     * @return string
     */
    function cms_site_settings_fetch_robots_txt($base)
    {
        $body = cms_site_settings_fetch_storefront_text($base, '/robots.txt');
        return cms_site_settings_is_valid_robots_txt($body) ? $body : '';
    }
}

if (!function_exists('cms_site_settings_robots_default_template')) {
    /**
     * Editable robots.txt template with placeholders (stored in DB / shown in CMS).
     *
     * @return string
     */
    function cms_site_settings_robots_default_template()
    {
        return implode("\n", array(
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /logout',
            'Disallow: /register',
            'Disallow: /forgot_password',
            'Disallow: /webshop/login',
            'Disallow: /webshop/logout',
            'Disallow: /webshop/register',
            'Disallow: /webshop/forgot_password',
            'Disallow: /webshop/cart',
            'Disallow: /webshop/checkout',
            'Disallow: /webshop/your_account',
            'Disallow: /webshop/your_orders',
            'Disallow: /webshop/your_tracking',
            'Disallow: /webshop/webshop_request',
            'Allow: /assets/',
            'Allow: /themes/',
            'Sitemap: {BASE_URL}/sitemap.xml',
            'Sitemap: {BASE_URL}/sitemap-index.xml',
            'Host: {HOST}',
            '# LLM index: {BASE_URL}/llms.txt',
        )) . "\n";
    }
}

if (!function_exists('cms_site_settings_sanitize_robots_content')) {
    /**
     * @param string $content
     * @return string
     */
    function cms_site_settings_sanitize_robots_content($content)
    {
        $content = str_replace(array("\r\n", "\r"), "\n", (string) $content);
        $content = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $content);
        $content = preg_replace('/<\?php.*?\?>/is', '', $content);

        return trim($content) === '' ? '' : trim($content) . "\n";
    }
}

if (!function_exists('cms_site_settings_apply_robots_placeholders')) {
    /**
     * @param string $content
     * @param string $base
     * @return string
     */
    function cms_site_settings_apply_robots_placeholders($content, $base)
    {
        $base = rtrim(trim((string) $base), '/');
        $host = parse_url($base, PHP_URL_HOST);
        $content = str_replace('{BASE_URL}', $base, (string) $content);
        $content = str_replace('{HOST}', is_string($host) ? $host : '', $content);

        return cms_site_settings_sanitize_robots_content($content);
    }
}

if (!function_exists('cms_site_settings_build_robots_fallback')) {
    /**
     * @param string $base
     * @return string
     */
    function cms_site_settings_build_robots_fallback($base)
    {
        return cms_site_settings_apply_robots_placeholders(
            cms_site_settings_robots_default_template(),
            $base
        );
    }
}

if (!function_exists('cms_site_settings_refresh_storefront_settings_cache')) {
    /**
     * Bust remote webshop getsettings cache after CMS robots.txt save.
     *
     * @param string $storefront_base
     * @return void
     */
    function cms_site_settings_refresh_storefront_settings_cache($storefront_base)
    {
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
        foreach (array('/robots.txt', '/llms.txt') as $path) {
            @file_get_contents($base . $path . '?refresh_settings=1', false, $ctx);
        }
    }
}

if (!function_exists('cms_site_settings_sitemap_variants')) {
    /**
     * @return array<string,array{label:string,path:string,description:string}>
     */
    function cms_site_settings_sitemap_variants()
    {
        return array(
            'index'      => array(
                'label'       => 'Sitemap index',
                'path'        => '/sitemap-index.xml',
                'description' => 'Lists pages, categories, and products sitemaps.',
            ),
            'main'       => array(
                'label'       => 'Combined sitemap',
                'path'        => '/sitemap.xml',
                'description' => 'All storefront URLs in one urlset.',
            ),
            'pages'      => array(
                'label'       => 'Pages sitemap',
                'path'        => '/sitemap-pages.xml',
                'description' => 'CMS and static storefront pages.',
            ),
            'categories' => array(
                'label'       => 'Categories sitemap',
                'path'        => '/sitemap-categories.xml',
                'description' => 'Product category URLs.',
            ),
            'products'   => array(
                'label'       => 'Products sitemap',
                'path'        => '/sitemap-products.xml',
                'description' => 'Product detail URLs.',
            ),
        );
    }
}

if (!function_exists('cms_site_settings_is_valid_sitemap_xml')) {
    /**
     * @param string $body
     * @return bool
     */
    function cms_site_settings_is_valid_sitemap_xml($body)
    {
        $body = trim((string) $body);
        if ($body === '' || cms_site_settings_is_html_response($body)) {
            return false;
        }

        if (stripos($body, '<?xml') !== 0) {
            return false;
        }

        return stripos($body, '<urlset') !== false || stripos($body, '<sitemapindex') !== false;
    }
}

if (!function_exists('cms_site_settings_fetch_sitemap_xml')) {
    /**
     * @param string $base
     * @param string $path
     * @return string
     */
    function cms_site_settings_fetch_sitemap_xml($base, $path)
    {
        $body = cms_site_settings_fetch_storefront_text($base, $path, 'application/xml,text/xml,*/*');
        return cms_site_settings_is_valid_sitemap_xml($body) ? $body : '';
    }
}

if (!function_exists('cms_site_settings_webshop_url')) {
    /**
     * Storefront public URL (clean paths at domain root, matching live webshop routing).
     *
     * @param string $base Storefront base URL
     * @param string $path  Page slug or path segment
     * @return string
     */
    function cms_site_settings_webshop_url($base, $path = '')
    {
        $base = rtrim(trim((string) $base), '/');
        $path = trim((string) $path, '/');
        if ($path === '' || strtolower($path) === 'index') {
            return $base;
        }

        return $base . '/' . $path;
    }
}

if (!function_exists('cms_site_settings_sitemap_loc_dedup_key')) {
    /**
     * Canonical dedup key for sitemap locs (trailing slash + hyphen/underscore variants).
     *
     * @param string $loc
     * @return string
     */
    function cms_site_settings_sitemap_loc_dedup_key($loc)
    {
        $loc = trim((string) $loc);
        if ($loc === '') {
            return '';
        }
        $parts = parse_url($loc);
        $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : '';
        $host = isset($parts['host']) ? strtolower((string) $parts['host']) : '';
        $port = isset($parts['port']) ? (int) $parts['port'] : 0;
        $path = isset($parts['path']) ? (string) $parts['path'] : '/';
        $segments = array();
        foreach (explode('/', trim($path, '/')) as $segment) {
            if ($segment === '') {
                continue;
            }
            $segments[] = str_replace('_', '-', strtolower($segment));
        }
        $path = $segments === array() ? '' : '/' . implode('/', $segments);
        $authority = $host;
        if ($port > 0 && $port !== 80 && $port !== 443) {
            $authority .= ':' . $port;
        }

        return $scheme . '://' . $authority . $path;
    }
}

if (!function_exists('cms_site_settings_render_sitemap_urlset_xml')) {
    /**
     * @param array  $urls rows with loc, lastmod, changefreq, priority
     * @param string $base Storefront base URL (for XSL href)
     * @return string
     */
    function cms_site_settings_render_sitemap_urlset_xml(array $urls, $base)
    {
        $base = rtrim(trim((string) $base), '/');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . $base . '/sitemap.xsl"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        foreach ($urls as $item) {
            if (!is_array($item) || empty($item['loc'])) {
                continue;
            }
            $loc = htmlspecialchars((string) $item['loc'], ENT_QUOTES, 'UTF-8');
            $lastmod = htmlspecialchars(isset($item['lastmod']) ? (string) $item['lastmod'] : date('Y-m-d'), ENT_QUOTES, 'UTF-8');
            $changefreq = htmlspecialchars(isset($item['changefreq']) ? (string) $item['changefreq'] : 'weekly', ENT_QUOTES, 'UTF-8');
            $priority = htmlspecialchars(isset($item['priority']) ? (string) $item['priority'] : '0.5', ENT_QUOTES, 'UTF-8');
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . $loc . "</loc>\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="en" href="' . $loc . "\" />\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . $loc . "\" />\n";
            $xml .= '    <lastmod>' . $lastmod . "</lastmod>\n";
            $xml .= '    <changefreq>' . $changefreq . "</changefreq>\n";
            $xml .= '    <priority>' . $priority . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}

if (!function_exists('cms_site_settings_render_sitemap_index_xml')) {
    /**
     * @param string $base
     * @return string
     */
    function cms_site_settings_render_sitemap_index_xml($base)
    {
        $base = rtrim(trim((string) $base), '/');
        $today = date('Y-m-d');
        $sitemaps = array(
            $base . '/sitemap-pages.xml',
            $base . '/sitemap-categories.xml',
            $base . '/sitemap-products.xml',
        );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . $base . '/sitemap.xsl"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($sitemaps as $loc) {
            $xml .= "  <sitemap>\n";
            $xml .= '    <loc>' . htmlspecialchars($loc, ENT_QUOTES, 'UTF-8') . "</loc>\n";
            $xml .= '    <lastmod>' . $today . "</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }
        $xml .= '</sitemapindex>';

        return $xml;
    }
}

if (!function_exists('cms_site_settings_collect_sitemap_page_urls')) {
    /**
     * Build page URLs from CMS database (fallback when storefront is offline).
     *
     * @param string $base
     * @return array<int,array<string,string>>
     */
    function cms_site_settings_collect_sitemap_page_urls($base)
    {
        $base = rtrim(trim((string) $base), '/');
        $today = date('Y-m-d');
        $urls = array();
        $seen = array();

        $add = function ($loc, $changefreq, $priority, $lastmod = '') use (&$urls, &$seen, $today, $base) {
            $loc = rtrim(trim((string) $loc), '/');
            if ($loc === '') {
                $loc = rtrim(trim((string) $base), '/');
            }
            $key = cms_site_settings_sitemap_loc_dedup_key($loc);
            if ($key === '' || isset($seen[$key])) {
                return;
            }
            $seen[$key] = true;
            $urls[] = array(
                'loc'        => $loc,
                'lastmod'    => $lastmod !== '' ? $lastmod : $today,
                'changefreq' => $changefreq,
                'priority'   => $priority,
            );
        };

        $add(cms_site_settings_webshop_url($base), 'daily', '1.0');

        $legalSlugs = array(
            'privacy-policy', 'privacy_policy', 'privacy',
            'terms', 'terms-of-service', 'terms_of_service', 'terms-and-conditions',
            'legal', 'cookie-policy', 'cookies',
        );

        $CI = function_exists('get_instance') ? get_instance() : null;
        if ($CI && isset($CI->db)) {
            $table = 'sma_cms_pages';
            if ($CI->db->table_exists($table)) {
                $rows = $CI->db
                    ->select('page_name, url, updated_at', false)
                    ->from($table)
                    ->where('status', 'published')
                    ->order_by('id', 'ASC')
                    ->get()
                    ->result_array();

                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $cmsUrl = isset($row['url']) ? trim((string) $row['url']) : '';
                    if ($cmsUrl === '') {
                        continue;
                    }
                    $slug = strtolower(ltrim(str_replace('_', '-', $cmsUrl), '/'));
                    if (in_array($slug, $legalSlugs, true) || in_array(str_replace('-', '_', $slug), $legalSlugs, true)) {
                        continue;
                    }
                    $lastmod = $today;
                    if (!empty($row['updated_at'])) {
                        $ts = strtotime((string) $row['updated_at']);
                        if ($ts) {
                            $lastmod = date('Y-m-d', $ts);
                        }
                    }
                    $add(cms_site_settings_webshop_url($base, ltrim($cmsUrl, '/')), 'weekly', '0.7', $lastmod);
                }
            }
        }

        return $urls;
    }
}

if (!function_exists('cms_site_settings_collect_sitemap_category_urls')) {
    /**
     * @param string $base
     * @return array<int,array<string,string>>
     */
    function cms_site_settings_collect_sitemap_category_urls($base)
    {
        $base = rtrim(trim((string) $base), '/');
        $today = date('Y-m-d');
        $urls = array();
        $seen = array();

        $CI = function_exists('get_instance') ? get_instance() : null;
        if (!$CI || !isset($CI->db)) {
            return $urls;
        }

        $table = $CI->db->dbprefix('categories');
        if (!$CI->db->table_exists($table)) {
            return $urls;
        }

        $select = array('id');
        if ($CI->db->field_exists('in_eshop', $table)) {
            $select[] = 'in_eshop';
        }
        if ($CI->db->field_exists('is_active', $table)) {
            $select[] = 'is_active';
        }

        $q = $CI->db->select(implode(', ', $select), false)->from($table)->order_by('id', 'ASC')->get();
        foreach ($q->result_array() as $row) {
            if (!is_array($row) || empty($row['id'])) {
                continue;
            }
            if (array_key_exists('in_eshop', $row) && (string) $row['in_eshop'] !== '1') {
                continue;
            }
            if (array_key_exists('is_active', $row) && (string) $row['is_active'] !== '1') {
                continue;
            }
            $loc = cms_site_settings_webshop_url($base, 'category_products/' . (int) $row['id']);
            $key = cms_site_settings_sitemap_loc_dedup_key($loc);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $urls[] = array(
                'loc'        => $loc,
                'lastmod'    => $today,
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            );
        }

        return $urls;
    }
}

if (!function_exists('cms_site_settings_collect_sitemap_product_urls')) {
    /**
     * @param string $base
     * @return array<int,array<string,string>>
     */
    function cms_site_settings_collect_sitemap_product_urls($base)
    {
        $base = rtrim(trim((string) $base), '/');
        $today = date('Y-m-d');
        $urls = array();
        $seen = array();

        $CI = function_exists('get_instance') ? get_instance() : null;
        if (!$CI || !isset($CI->db)) {
            return $urls;
        }

        $table = $CI->db->dbprefix('products');
        if (!$CI->db->table_exists($table)) {
            return $urls;
        }

        $select = array('id');
        if ($CI->db->field_exists('updated_at', $table)) {
            $select[] = 'updated_at';
        }

        $q = $CI->db->select(implode(', ', $select), false)->from($table)->order_by('id', 'ASC')->limit(5000)->get();
        foreach ($q->result_array() as $row) {
            if (!is_array($row) || empty($row['id'])) {
                continue;
            }
            $lastmod = $today;
            if (!empty($row['updated_at'])) {
                $ts = strtotime((string) $row['updated_at']);
                if ($ts) {
                    $lastmod = date('Y-m-d', $ts);
                }
            }
            $loc = cms_site_settings_webshop_url($base, 'product_details/' . md5((string) $row['id']));
            $key = cms_site_settings_sitemap_loc_dedup_key($loc);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $urls[] = array(
                'loc'        => $loc,
                'lastmod'    => $lastmod,
                'changefreq' => 'weekly',
                'priority'   => '0.9',
            );
        }

        return $urls;
    }
}

if (!function_exists('cms_site_settings_build_sitemap_fallback')) {
    /**
     * @param string $variant index|main|pages|categories|products
     * @param string $base
     * @return string
     */
    function cms_site_settings_build_sitemap_fallback($variant, $base)
    {
        $variant = trim((string) $variant);
        if ($variant === 'index') {
            return cms_site_settings_render_sitemap_index_xml($base);
        }

        if ($variant === 'pages') {
            return cms_site_settings_render_sitemap_urlset_xml(
                cms_site_settings_collect_sitemap_page_urls($base),
                $base
            );
        }

        if ($variant === 'categories') {
            return cms_site_settings_render_sitemap_urlset_xml(
                cms_site_settings_collect_sitemap_category_urls($base),
                $base
            );
        }

        if ($variant === 'products') {
            return cms_site_settings_render_sitemap_urlset_xml(
                cms_site_settings_collect_sitemap_product_urls($base),
                $base
            );
        }

        $urls = array_merge(
            cms_site_settings_collect_sitemap_page_urls($base),
            cms_site_settings_collect_sitemap_category_urls($base),
            cms_site_settings_collect_sitemap_product_urls($base)
        );

        return cms_site_settings_render_sitemap_urlset_xml($urls, $base);
    }
}

if (!function_exists('cms_site_settings_format_xml')) {
    /**
     * Pretty-print XML for admin preview (readability).
     *
     * @param string $xml
     * @return string
     */
    function cms_site_settings_format_xml($xml)
    {
        $xml = trim((string) $xml);
        if ($xml === '') {
            return '';
        }

        if (!class_exists('DOMDocument')) {
            return $xml;
        }

        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        if (@$dom->loadXML($xml)) {
            $formatted = $dom->saveXML();
            return is_string($formatted) ? trim($formatted) : $xml;
        }

        return $xml;
    }
}

if (!function_exists('cms_site_settings_parse_sitemap_table_rows')) {
    /**
     * Parse sitemap XML into rows for admin table preview (matches live sitemap.xsl layout).
     *
     * @param string $xml
     * @return array{type:string,rows:array<int,array<string,string>>}
     */
    function cms_site_settings_parse_sitemap_table_rows($xml)
    {
        $out = array('type' => '', 'rows' => array());
        $xml = trim((string) $xml);
        if ($xml === '' || !class_exists('SimpleXMLElement')) {
            return $out;
        }

        libxml_use_internal_errors(true);
        $sx = @simplexml_load_string($xml);
        if ($sx === false) {
            return $out;
        }

        $ns = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        $root = $sx->getName();

        if ($root === 'sitemapindex') {
            $out['type'] = 'index';
            foreach ($sx->children($ns)->sitemap as $item) {
                $child = $item->children($ns);
                $loc = trim((string) $child->loc);
                if ($loc === '') {
                    continue;
                }
                $out['rows'][] = array(
                    'loc'     => $loc,
                    'lastmod' => trim((string) $child->lastmod),
                );
            }

            if (empty($out['rows']) && preg_match_all('/<loc>([^<]+)<\/loc>\s*<lastmod>([^<]*)<\/lastmod>/i', $xml, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $out['rows'][] = array(
                        'loc'     => trim(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8')),
                        'lastmod' => trim($match[2]),
                    );
                }
            }

            return $out;
        }

        if ($root === 'urlset') {
            $out['type'] = 'urlset';
            foreach ($sx->children($ns)->url as $item) {
                $child = $item->children($ns);
                $loc = trim((string) $child->loc);
                if ($loc === '') {
                    continue;
                }
                $out['rows'][] = array(
                    'loc'        => $loc,
                    'lastmod'    => trim((string) $child->lastmod),
                    'changefreq' => trim((string) $child->changefreq),
                    'priority'   => trim((string) $child->priority),
                );
            }

            if (empty($out['rows']) && preg_match_all('/<url>\s*<loc>([^<]+)<\/loc>.*?<lastmod>([^<]*)<\/lastmod>.*?<changefreq>([^<]*)<\/changefreq>.*?<priority>([^<]*)<\/priority>\s*<\/url>/is', $xml, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $out['rows'][] = array(
                        'loc'        => trim(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8')),
                        'lastmod'    => trim($match[2]),
                        'changefreq' => trim($match[3]),
                        'priority'   => trim($match[4]),
                    );
                }
            }
        }

        return $out;
    }
}
