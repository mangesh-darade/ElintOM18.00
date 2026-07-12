<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Parse pasted &lt;head&gt; markup / scripts into tags_master tag_name => value pairs.
 */

if (!function_exists('cms_tag_master_catalog')) {
    /**
     * Known tags with defaults for auto-create (aligned with cms_schema_reference.sql).
     *
     * @return array tag_name => definition
     */
    function cms_tag_master_catalog()
    {
        static $catalog = null;
        if ($catalog !== null) {
            return $catalog;
        }

        $catalog = array(
            'title' => array(
                'tag_type' => 'meta', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{page_title}',
                'implementation_code' => '<title>{page_title}</title>',
            ),
            'meta_description' => array(
                'tag_type' => 'meta', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{meta_description}',
                'implementation_code' => '<meta name="description" content="{meta_description}">',
            ),
            'meta_keywords' => array(
                'tag_type' => 'meta', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{meta_keywords}',
                'implementation_code' => '<meta name="keywords" content="{meta_keywords}">',
            ),
            'canonical' => array(
                'tag_type' => 'link', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{canonical_url}',
                'implementation_code' => '<link rel="canonical" href="{canonical_url}">',
            ),
            'robots' => array(
                'tag_type' => 'meta', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{robots}',
                'implementation_code' => '<meta name="robots" content="{robots}">',
            ),
            'viewport' => array(
                'tag_type' => 'meta', 'category' => 'SEO', 'page_type' => 'all',
                'template' => 'width=device-width, initial-scale=1.0',
                'implementation_code' => '<meta name="viewport" content="width=device-width, initial-scale=1.0">',
            ),
            'hreflang' => array(
                'tag_type' => 'link', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{hreflang_url}',
                'implementation_code' => '<link rel="alternate" hreflang="en" href="{hreflang_url}">',
            ),
            'theme_color' => array(
                'tag_type' => 'meta', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{theme_color}',
                'implementation_code' => '<meta name="theme-color" content="{theme_color}">',
            ),
            'last_modified' => array(
                'tag_type' => 'meta', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{last_modified}',
                'implementation_code' => '<meta name="last-modified" content="{last_modified}">',
            ),
            'copyright' => array(
                'tag_type' => 'meta', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{copyright}',
                'implementation_code' => '<meta name="copyright" content="{copyright}">',
            ),
            'og:site_name' => array(
                'tag_type' => 'og', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{og_site_name}',
                'implementation_code' => '<meta property="og:site_name" content="{og_site_name}">',
            ),
            'og:type' => array(
                'tag_type' => 'og', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{og_type}',
                'implementation_code' => '<meta property="og:type" content="{og_type}">',
            ),
            'og:title' => array(
                'tag_type' => 'og', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{og_title}',
                'implementation_code' => '<meta property="og:title" content="{og_title}">',
            ),
            'og:description' => array(
                'tag_type' => 'og', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{og_description}',
                'implementation_code' => '<meta property="og:description" content="{og_description}">',
            ),
            'og:url' => array(
                'tag_type' => 'og', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{og_url}',
                'implementation_code' => '<meta property="og:url" content="{og_url}">',
            ),
            'og:image' => array(
                'tag_type' => 'og', 'category' => 'SEO', 'page_type' => 'all',
                'template' => '{og_image}',
                'implementation_code' => '<meta property="og:image" content="{og_image}">',
            ),
            'geo.region' => array(
                'tag_type' => 'meta', 'category' => 'GEO', 'page_type' => 'all',
                'template' => '{geo_region}',
                'implementation_code' => '<meta name="geo.region" content="{geo_region}">',
            ),
            'geo.position' => array(
                'tag_type' => 'meta', 'category' => 'GEO', 'page_type' => 'all',
                'template' => '{geo_position}',
                'implementation_code' => '<meta name="geo.position" content="{geo_position}">',
            ),
            'ICBM' => array(
                'tag_type' => 'meta', 'category' => 'GEO', 'page_type' => 'all',
                'template' => '{geo_lat},{geo_lng}',
                'implementation_code' => '<meta name="ICBM" content="{geo_lat},{geo_lng}">',
            ),
            'pharmacy_schema' => array(
                'tag_type' => 'schema', 'category' => 'Schema', 'page_type' => 'home',
                'show_on_page' => 1, 'show_on_entity' => 0,
                'template' => '{"@context":"https://schema.org","@type":"Pharmacy","name":"{site_name}"}',
                'implementation_code' => '<script type="application/ld+json">{schema_json}</script>',
            ),
            'organization_schema' => array(
                'tag_type' => 'schema', 'category' => 'Schema', 'page_type' => 'home',
                'show_on_page' => 1, 'show_on_entity' => 0,
                'template' => '{"@context":"https://schema.org","@type":"Organization","name":"{site_name}"}',
                'implementation_code' => '<script type="application/ld+json">{schema_json}</script>',
            ),
            'website_schema' => array(
                'tag_type' => 'schema', 'category' => 'Schema', 'page_type' => 'home',
                'show_on_page' => 1, 'show_on_entity' => 0,
                'template' => '{"@context":"https://schema.org","@type":"WebSite","url":"{site_url}"}',
                'implementation_code' => '<script type="application/ld+json">{schema_json}</script>',
            ),
            'product_schema' => array(
                'tag_type' => 'schema', 'category' => 'Schema', 'page_type' => 'product',
                'show_on_page' => 0, 'show_on_entity' => 1,
                'template' => '{"@context":"https://schema.org","@type":"Product","name":"{product_name}"}',
                'implementation_code' => '<script type="application/ld+json">{schema_json}</script>',
            ),
            'article_schema' => array(
                'tag_type' => 'schema', 'category' => 'Schema', 'page_type' => 'blog',
                'show_on_page' => 0, 'show_on_entity' => 1,
                'template' => '{"@context":"https://schema.org","@type":"Article","headline":"{headline}"}',
                'implementation_code' => '<script type="application/ld+json">{schema_json}</script>',
            ),
            'category_schema' => array(
                'tag_type' => 'schema', 'category' => 'Schema', 'page_type' => 'category',
                'show_on_page' => 0, 'show_on_entity' => 1,
                'template' => '{"@context":"https://schema.org","@type":"CollectionPage","name":"{category_name}"}',
                'implementation_code' => '<script type="application/ld+json">{schema_json}</script>',
            ),
            'schema_json' => array(
                'tag_type' => 'schema', 'category' => 'Schema', 'page_type' => 'all',
                'show_on_page' => 1, 'show_on_entity' => 1,
                'template' => '{schema_json}',
                'implementation_code' => '<script type="application/ld+json">{schema_json}</script>',
            ),
        );

        return $catalog;
    }
}

if (!function_exists('cms_normalize_imported_meta_name')) {
    /**
     * Map HTML meta name/property to tags_master.tag_name.
     *
     * @param string $name
     * @return string
     */
    function cms_normalize_imported_meta_name($name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '';
        }
        $lower = strtolower($name);
        $map = array(
            'description' => 'meta_description',
            'keywords' => 'meta_keywords',
            'theme-color' => 'theme_color',
            'last-modified' => 'last_modified',
            'ai-entity' => 'ai_entity',
            'ai-summary' => 'ai_summary',
            'ai-category' => 'ai_category',
            'ai-industry' => 'ai_industry',
            'ai-brand' => 'ai_brand',
            'ai-purpose' => 'ai_purpose',
            'ai-keyphrase' => 'ai_keyphrase',
            'ai-context' => 'ai_context',
        );
        if (isset($map[$lower])) {
            return $map[$lower];
        }
        if (strpos($name, 'og:') === 0 || strpos($name, 'twitter:') === 0) {
            return $name;
        }
        if ($lower === 'geo.region') {
            return 'geo.region';
        }
        if ($lower === 'geo.position') {
            return 'geo.position';
        }
        if ($lower === 'icbm') {
            return 'ICBM';
        }
        return str_replace('-', '_', $lower);
    }
}

if (!function_exists('cms_read_head_markup_from_post')) {
    /**
     * Read raw head markup from POST (base64 preferred to avoid XSS/WAF stripping).
     *
     * @param object|null $ci CodeIgniter instance with input loaded
     * @return string
     */
    function cms_read_head_markup_from_post($ci = null)
    {
        $b64 = null;
        if (isset($_POST['head_markup_b64'])) {
            $b64 = $_POST['head_markup_b64'];
        } elseif ($ci !== null && isset($ci->input)) {
            $b64 = $ci->input->post('head_markup_b64', false);
        }

        if (is_string($b64) && trim($b64) !== '') {
            $decoded = base64_decode($b64, true);
            if ($decoded !== false && trim($decoded) !== '') {
                return $decoded;
            }
        }

        $raw = null;
        if (isset($_POST['head_markup'])) {
            $raw = $_POST['head_markup'];
        } elseif ($ci !== null && isset($ci->input)) {
            $raw = $ci->input->post('head_markup', false);
        }

        return cms_prepare_head_markup_for_import(is_string($raw) ? $raw : '');
    }
}

if (!function_exists('cms_prepare_head_markup_for_import')) {
    /**
     * Normalize pasted / POST markup (entity-encoded HTML, xss_clean output, BOM).
     *
     * @param string $html
     * @return string
     */
    function cms_prepare_head_markup_for_import($html)
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $html = preg_replace('/^\xEF\xBB\xBF/', '', $html);
        $html = str_replace(array('＜', '＞'), array('<', '>'), $html);

        // CodeIgniter xss_clean encodes tags and wraps script JSON in [removed]...[/removed].
        if (strpos($html, '&lt;') !== false || strpos($html, '&gt;') !== false || strpos($html, '&quot;') !== false || strpos($html, '&#') !== false) {
            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (stripos($html, '[removed]') !== false) {
            $html = preg_replace('/\[removed\](.*?)\[\/removed\]/is', '<script type="application/ld+json">$1</script>', $html);
            $html = preg_replace('/\[removed\](.*?)\[removed\]/is', '<script type="application/ld+json">$1</script>', $html);
        }

        return trim($html);
    }
}

if (!function_exists('cms_schema_tag_name_from_json')) {
    /**
     * @param array $decoded
     * @return string tags_master tag_name or empty to skip
     */
    function cms_schema_tag_name_from_json(array $decoded)
    {
        $type = '';
        if (isset($decoded['@type'])) {
            $type = is_array($decoded['@type']) ? (string) reset($decoded['@type']) : (string) $decoded['@type'];
        } elseif (isset($decoded['@graph']) && is_array($decoded['@graph']) && !empty($decoded['@graph'][0]['@type'])) {
            $type = (string) $decoded['@graph'][0]['@type'];
        }
        $type = strtolower($type);

        if ($type === 'product') {
            return 'product_schema';
        }
        if ($type === 'pharmacy' || $type === 'localbusiness') {
            return 'pharmacy_schema';
        }
        if ($type === 'article' || $type === 'blogposting' || $type === 'newsarticle') {
            return 'article_schema';
        }
        if ($type === 'collectionpage' || $type === 'itemlist') {
            return 'category_schema';
        }
        if ($type === 'breadcrumblist') {
            return '';
        }

        return 'schema_json';
    }
}

if (!function_exists('cms_parse_head_markup_for_tags')) {
    /**
     * @param string $html
     * @return array tag_name => value
     */
    function cms_parse_head_markup_for_tags($html)
    {
        $html = cms_prepare_head_markup_for_import($html);
        if ($html === '') {
            return array();
        }

        $out = array();

        if (preg_match('/<title\b[^>]*>(.*?)<\/title>/is', $html, $m)) {
            $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
            if ($title !== '') {
                $out['title'] = $title;
            }
        }

        if (preg_match_all('/<meta\b[^>]*>/is', $html, $metaMatches)) {
            foreach ($metaMatches[0] as $metaTag) {
                $name = '';
                $content = '';
                if (preg_match('/\bname\s*=\s*["\']([^"\']+)["\']/i', $metaTag, $nm)) {
                    $name = cms_normalize_imported_meta_name($nm[1]);
                } elseif (preg_match('/\bproperty\s*=\s*["\']([^"\']+)["\']/i', $metaTag, $pm)) {
                    $name = cms_normalize_imported_meta_name($pm[1]);
                }
                if (preg_match('/\bcontent\s*=\s*["\']([^"\']*)["\']/i', $metaTag, $cm)) {
                    $content = trim(html_entity_decode($cm[1], ENT_QUOTES, 'UTF-8'));
                }
                if ($name !== '' && $content !== '') {
                    $out[$name] = $content;
                }
            }
        }

        if (preg_match('/<link\b[^>]*\brel\s*=\s*["\']canonical["\'][^>]*>/i', $html, $linkTag)
            || preg_match('/<link\b[^>]*\bhref\s*=\s*["\']([^"\']+)["\'][^>]*\brel\s*=\s*["\']canonical["\'][^>]*>/i', $html, $linkTag)) {
            if (preg_match('/\bhref\s*=\s*["\']([^"\']+)["\']/i', $linkTag[0], $hm)) {
                $href = trim(html_entity_decode($hm[1], ENT_QUOTES, 'UTF-8'));
                if ($href !== '') {
                    $out['canonical'] = $href;
                }
            }
        }

        if (preg_match_all('/<script\b[^>]*type\s*=\s*["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $scripts)) {
            foreach ($scripts[1] as $jsonRaw) {
                $jsonRaw = trim($jsonRaw);
                if ($jsonRaw === '') {
                    continue;
                }
                $decoded = json_decode($jsonRaw, true);
                if (!is_array($decoded)) {
                    continue;
                }
                $schemaTag = cms_schema_tag_name_from_json($decoded);
                if ($schemaTag !== '') {
                    $out[$schemaTag] = $jsonRaw;
                }
            }
        }

        // Pasted JSON-LD without script wrapper.
        if (preg_match('/^\s*[\{\[]/', $html)) {
            $jsonRaw = trim($html);
            $decoded = json_decode($jsonRaw, true);
            if (is_array($decoded)) {
                $schemaTag = cms_schema_tag_name_from_json($decoded);
                if ($schemaTag !== '' && !isset($out[$schemaTag])) {
                    $out[$schemaTag] = $jsonRaw;
                }
            }
        }

        return $out;
    }
}

if (!function_exists('cms_apply_tag_visibility_defaults')) {
    /**
     * @param array $def
     * @return array
     */
    function cms_apply_tag_visibility_defaults(array $def)
    {
        if (array_key_exists('show_on_page', $def) && array_key_exists('show_on_entity', $def)) {
            return $def;
        }

        $name = isset($def['tag_name']) ? (string) $def['tag_name'] : '';
        $entity_only = array(
            'product_schema', 'article_schema', 'category_schema',
            'review_schema', 'product_review_schema',
        );
        $page_only = array(
            'pharmacy_schema', 'organization_schema', 'website_schema', 'rss_feed',
        );
        if (in_array($name, $entity_only, true)) {
            $def['show_on_page'] = 0;
            $def['show_on_entity'] = 1;
        } elseif (in_array($name, $page_only, true)) {
            $def['show_on_page'] = 1;
            $def['show_on_entity'] = 0;
        } else {
            // Shared SEO (title, meta, OG, canonical, …) — both CMS Pages and Entity Tags.
            $def['show_on_page'] = 1;
            $def['show_on_entity'] = 1;
        }

        return $def;
    }
}

if (!function_exists('cms_head_import_context')) {
    /**
     * @param string $form       page|entity
     * @param string $scope_code page_type or entity_code
     * @return array{form:string,scope_code:string}
     */
    function cms_head_import_context($form, $scope_code = '')
    {
        return array(
            'form'       => strtolower(trim((string) $form)),
            'scope_code' => strtolower(trim((string) $scope_code)),
        );
    }
}

if (!function_exists('cms_tag_master_definition')) {
    /**
     * @param string $tag_name
     * @return array|null
     */
    function cms_tag_master_definition($tag_name)
    {
        $catalog = cms_tag_master_catalog();
        if (isset($catalog[$tag_name])) {
            $def = $catalog[$tag_name];
            $def['tag_name'] = $tag_name;
            return cms_apply_tag_visibility_defaults($def);
        }
        if (strpos($tag_name, 'ai:') === 0 || strpos($tag_name, 'ai_') === 0) {
            return null;
        }
        if (strpos($tag_name, 'og:') === 0) {
            return cms_apply_tag_visibility_defaults(array(
                'tag_name' => $tag_name,
                'tag_type' => 'og',
                'category' => 'SEO',
                'page_type' => 'all',
                'template' => '{' . str_replace(':', '_', $tag_name) . '}',
                'implementation_code' => '<meta property="' . $tag_name . '" content="{' . str_replace(':', '_', $tag_name) . '}">',
            ));
        }
        if (strpos($tag_name, 'twitter:') === 0) {
            return cms_apply_tag_visibility_defaults(array(
                'tag_name' => $tag_name,
                'tag_type' => 'twitter',
                'category' => 'Social',
                'page_type' => 'all',
                'template' => '{' . str_replace(':', '_', $tag_name) . '}',
                'implementation_code' => '<meta name="' . $tag_name . '" content="{' . str_replace(':', '_', $tag_name) . '}">',
            ));
        }
        return cms_apply_tag_visibility_defaults(array(
            'tag_name' => $tag_name,
            'tag_type' => 'meta',
            'category' => 'Technical',
            'page_type' => 'all',
            'template' => '{' . $tag_name . '}',
            'implementation_code' => '<meta name="' . $tag_name . '" content="{' . $tag_name . '}">',
        ));
    }
}
