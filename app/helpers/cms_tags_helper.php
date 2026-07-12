<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CMS tag master — form scoping (page vs entity) and friendly labels.
 */

if (!function_exists('cms_tags_scope_options')) {
    /**
     * Allowed sma_cms_tags_master.page_type values (admin Tag Master + filters).
     *
     * @return array<string,string>
     */
    function cms_tags_scope_options()
    {
        return array(
            'all'      => 'All types (global)',
            'static'   => 'Static CMS pages',
            'home'     => 'Homepage',
            'category' => 'Category',
            'product'  => 'Product',
            'blog'     => 'Blog',
        );
    }
}

if (!function_exists('cms_sanitize_tags_scope')) {
    /**
     * @param string $scope
     * @return string
     */
    function cms_sanitize_tags_scope($scope)
    {
        $scope = strtolower(trim((string) $scope));
        $allowed = array_keys(cms_tags_scope_options());
        return in_array($scope, $allowed, true) ? $scope : 'all';
    }
}

if (!function_exists('cms_page_type_options')) {
    /**
     * Page types for CMS Pages (matches sma_cms_pages.page_type + tags_master.page_type).
     *
     * @return array<string,string> value => label
     */
    function cms_page_type_options()
    {
        return array(
            'static'   => 'Static page (About, Privacy, Terms, …)',
            'home'     => 'Homepage',
            'category' => 'Category listing template',
            'product'  => 'Product listing template',
            'blog'     => 'Blog / articles',
        );
    }
}

if (!function_exists('cms_sanitize_page_type')) {
    /**
     * @param string $page_type
     * @return string
     */
    function cms_sanitize_page_type($page_type)
    {
        $page_type = strtolower(trim((string) $page_type));
        $allowed = array_keys(cms_page_type_options());
        return in_array($page_type, $allowed, true) ? $page_type : 'static';
    }
}

if (!function_exists('cms_sanitize_entity_scope_code')) {
    /**
     * @param string $entity_code
     * @return string
     */
    function cms_sanitize_entity_scope_code($entity_code)
    {
        return strtolower(trim((string) $entity_code));
    }
}

if (!function_exists('cms_tags_scope_matches')) {
    /**
     * Whether a tag row applies to the given scope code (page_type or entity_code).
     *
     * @param array  $tag
     * @param string $scope_code
     * @return bool
     */
    function cms_tags_scope_matches(array $tag, $scope_code)
    {
        $scope_code = strtolower(trim((string) $scope_code));
        $tag_scope = isset($tag['page_type']) ? strtolower(trim((string) $tag['page_type'])) : 'all';
        if ($tag_scope === '' || $tag_scope === 'all') {
            return true;
        }
        return $tag_scope === $scope_code;
    }
}

if (!function_exists('cms_filter_tags_master_for_page_form')) {
    /**
     * Tags for CMS Pages add/edit — show_on_page only (no page-type picker on the form).
     *
     * @param array $tags
     * @return array
     */
    function cms_filter_tags_master_for_page_form(array $tags)
    {
        $out = array();

        foreach ($tags as $tag) {
            if (!is_array($tag) || empty($tag['id'])) {
                continue;
            }
            $show = array_key_exists('show_on_page', $tag)
                ? (int) $tag['show_on_page']
                : 1;
            if ($show !== 1) {
                continue;
            }
            $out[] = $tag;
        }

        return $out;
    }
}

if (!function_exists('cms_filter_tags_master_for_form')) {
    /**
     * Filter tags_master rows for CMS admin forms.
     *
     * @param array  $tags
     * @param string $form       page|entity
     * @param string $scope_code page_type or entity_code
     * @return array
     */
    function cms_filter_tags_master_for_form(array $tags, $form, $scope_code)
    {
        $form = strtolower(trim((string) $form));
        $scope_code = strtolower(trim((string) $scope_code));
        if ($scope_code === '') {
            return array();
        }

        $flag_key = ($form === 'entity') ? 'show_on_entity' : 'show_on_page';
        $out = array();

        foreach ($tags as $tag) {
            if (!is_array($tag) || empty($tag['id'])) {
                continue;
            }
            $show = array_key_exists($flag_key, $tag)
                ? (int) $tag[$flag_key]
                : ($form === 'entity' ? 0 : 1);
            if ($show !== 1) {
                continue;
            }
            if (!cms_tags_scope_matches($tag, $scope_code)) {
                continue;
            }
            $out[] = $tag;
        }

        return $out;
    }
}

if (!function_exists('cms_group_tags_by_category')) {
    /**
     * @param array $tags
     * @return array<string,array>
     */
    function cms_group_tags_by_category(array $tags)
    {
        $grouped = array();
        foreach ($tags as $tag) {
            if (!is_array($tag)) {
                continue;
            }
            $cat = !empty($tag['category']) ? trim((string) $tag['category']) : 'General';
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = array();
            }
            $grouped[$cat][] = $tag;
        }
        return $grouped;
    }
}

if (!function_exists('cms_tags_master_index_by_id')) {
    /**
     * @param array $tags
     * @return array<int,array>
     */
    function cms_tags_master_index_by_id(array $tags)
    {
        $index = array();
        foreach ($tags as $tag) {
            if (!is_array($tag) || empty($tag['id'])) {
                continue;
            }
            $index[(int) $tag['id']] = $tag;
        }
        return $index;
    }
}

if (!function_exists('cms_tags_form_scope_label')) {
    /**
     * Human label for the tag form scope banner.
     *
     * @param string $form       page|entity
     * @param string $scope_code
     * @return string
     */
    function cms_tags_form_scope_label($form, $scope_code)
    {
        $form = strtolower(trim((string) $form));
        $scope_code = strtolower(trim((string) $scope_code));

        if ($form === 'page' && $scope_code === '') {
            return 'Page-level SEO & schema fields';
        }

        if ($form === 'entity') {
            $map = array(
                'product'  => 'Product',
                'category' => 'Category',
                'blog'     => 'Blog post',
            );
            $name = isset($map[$scope_code]) ? $map[$scope_code] : ucfirst($scope_code);
            return 'SEO & schema fields for: ' . $name;
        }

        $options = cms_page_type_options();
        if (isset($options[$scope_code])) {
            return 'SEO & schema fields for: ' . $options[$scope_code];
        }
        return 'SEO & schema fields for: ' . ucfirst($scope_code);
    }
}

if (!function_exists('cms_tags_form_scope_hint')) {
    /**
     * Short help text under the scope banner.
     *
     * @param string $form
     * @param int    $tag_count
     * @param string $scope_code page_type or entity_code (product|category|blog)
     * @return string
     */
    function cms_tags_form_scope_hint($form, $tag_count, $scope_code = '')
    {
        $tag_count = (int) $tag_count;
        $form = strtolower(trim((string) $form));
        $scope_code = strtolower(trim((string) $scope_code));

        if ($tag_count === 0) {
            if ($scope_code !== '') {
                $flag = $form === 'entity' ? 'Show on Entity' : 'Show on Page';

                return 'No tags match page_type "' . $scope_code . '" or "all" with ' . $flag . ' = Yes in Tag Master. '
                    . 'Enable tags in Tag Master or use Import from head script below.';
            }

            return 'No tags are configured for this type in Tag Master. Paste head HTML below to auto-create tags, or enable fields in Tag Master.';
        }

        if ($form === 'entity' && $scope_code !== '') {
            return 'Showing ' . $tag_count . ' field(s) where page_type is "all" or "' . $scope_code . '" and Show on Entity = Yes. '
                . 'Home-only tags (e.g. pharmacy_schema) are hidden here.';
        }

        if ($form === 'page' && $scope_code !== '') {
            return 'Showing ' . $tag_count . ' field(s) where page_type is "all" or "' . $scope_code . '" and Show on Page = Yes (Tag Master). '
                . 'Per-item product/category/blog schema is edited under Entity Tag Mapping.';
        }

        return 'Showing ' . $tag_count . ' field(s) enabled for CMS Pages in Tag Master. Per-item schema is edited under Entity Tag Mapping.';
    }
}

if (!function_exists('cms_tag_display_name')) {
    /**
     * @param string $tag_name
     * @return string
     */
    function cms_tag_display_name($tag_name)
    {
        return preg_replace('/\s+/', ' ', trim(str_replace('_', ' ', (string) $tag_name)));
    }
}
