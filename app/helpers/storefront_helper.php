<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Storefront / website_setting helpers (sma_cms_webshop_header_footer rows).
 */

if (!function_exists('storefront_ga_example_measurement_id')) {
    function storefront_ga_example_measurement_id() {
        return 'G-XXXXXXXXXX';
    }
}

if (!function_exists('storefront_normalize_field_key')) {
    function storefront_normalize_field_key($raw) {
        $key = trim((string) $raw);
        return ($key === '' || strlen($key) > 64) ? '' : $key;
    }
}

if (!function_exists('storefront_normalize_ga_measurement_id')) {
    function storefront_normalize_ga_measurement_id($raw) {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/googletagmanager\.com\/gtag\/js\?id=([A-Za-z0-9_-]+)/i', $raw, $m)) {
            $raw = $m[1];
        } elseif (preg_match("/gtag\s*\(\s*['\"]config['\"]\s*,\s*['\"]([A-Za-z0-9_-]+)/i", $raw, $m)) {
            $raw = $m[1];
        }
        $raw = trim($raw);
        return preg_match('/^(G|GT|AW|UA)-[A-Z0-9]+$/i', $raw) ? strtoupper($raw) : '';
    }
}

if (!function_exists('storefront_sanitize_field_value')) {
    /**
     * @param string $field_key Unused
     * @param string $value
     * @return string|false
     */
    function storefront_sanitize_field_value($field_key, $value) {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (preg_match('/<script|googletagmanager/i', $value)) {
            return storefront_value_has_invalid_ga_snippet($value) ? false : $value;
        }
        $id = storefront_normalize_ga_measurement_id($value);
        return $id !== '' ? $id : $value;
    }
}

if (!function_exists('storefront_resolve_storefront_label')) {
    function storefront_resolve_storefront_label($label, $value, $field_key = '') {
        $label = trim((string) $label);
        if ($label !== '') {
            return $label;
        }
        $value = trim((string) $value);
        if ($value !== '' && storefront_normalize_ga_measurement_id($value) !== '') {
            return 'Google Analytics code';
        }
        $field_key = trim((string) $field_key);
        return $field_key !== '' ? $field_key : 'Storefront row';
    }
}

if (!function_exists('storefront_value_has_invalid_ga_snippet')) {
    function storefront_value_has_invalid_ga_snippet($value) {
        $value = trim((string) $value);
        if ($value === '' || !preg_match('/googletagmanager|gtag\s*\(/i', $value)) {
            return false;
        }
        if (storefront_normalize_ga_measurement_id($value) !== '') {
            return false;
        }
        return !(preg_match('/<script/i', $value) && preg_match('/googletagmanager\.com/i', $value));
    }
}

if (!function_exists('storefront_render_tracking_head_html')) {
    /**
     * Head tracking from CMS header Content Value (getsettings rows or DB).
     *
     * @param array|false|null $website_setting
     * @return string
     */
    function storefront_render_tracking_head_html($website_setting = null) {
        $parts = array();
        $seen = array();

        $append = function ($raw) use (&$parts, &$seen) {
            $raw = trim((string) $raw);
            if ($raw === '' || isset($seen[$raw])) {
                return;
            }
            if (preg_match('/<script/i', $raw) && preg_match('/googletagmanager/i', $raw)) {
                if (!preg_match('/javascript:|on\w+\s*=/i', $raw) && preg_match('/gtag\s*\(/i', $raw)) {
                    $h = md5($raw);
                    if (!isset($seen[$h])) {
                        $seen[$h] = true;
                        $parts[] = $raw;
                    }
                    $seen[$raw] = true;
                    return;
                }
                if (preg_match_all('/<script\b[^>]*>[\s\S]*?<\/script>/i', $raw, $blocks)) {
                    foreach ($blocks[0] as $block) {
                        if (!preg_match('/googletagmanager|gtag\s*\(/i', $block) || preg_match('/javascript:|on\w+\s*=/i', $block)) {
                            continue;
                        }
                        $h = md5($block);
                        if (!isset($seen[$h])) {
                            $seen[$h] = true;
                            $parts[] = $block;
                        }
                    }
                }
                $seen[$raw] = true;
                return;
            }
            $ga = storefront_normalize_ga_measurement_id($raw);
            if ($ga === '') {
                return;
            }
            $ga_esc = htmlspecialchars($ga, ENT_QUOTES, 'UTF-8');
            $markup = '<!-- Google Analytics code -->' . "\n"
                . '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $ga_esc . '"></script>' . "\n"
                . '<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}'
                . "gtag('js', new Date());\ngtag('config', '" . $ga_esc . "');\n</script>";
            if (!isset($seen[$markup])) {
                $seen[$markup] = true;
                $parts[] = $markup;
            }
            $seen[$raw] = true;
        };

        if (is_array($website_setting)) {
            foreach ($website_setting as $item) {
                $value = '';
                $sec = '';
                if (is_object($item)) {
                    $value = isset($item->value) ? trim((string) $item->value) : '';
                    $sec = isset($item->section_type) ? strtolower(trim((string) $item->section_type)) : '';
                } elseif (is_array($item)) {
                    $value = isset($item['value']) ? trim((string) $item['value']) : '';
                    $sec = isset($item['section_type']) ? strtolower(trim((string) $item['section_type'])) : '';
                }
                if ($value === '' || ($sec !== '' && $sec !== 'header')) {
                    continue;
                }
                $append($value);
            }
        }

        if (empty($parts)) {
            $CI =& get_instance();
            if (!isset($CI->webshop_settings_model)) {
                $CI->load->model('webshop_settings_model');
            }
            if ($CI->webshop_settings_model->header_footer_schema_ready()) {
                $CI->db->where('is_active', 1);
                $CI->db->where('section_type', 'header');
                $CI->db->order_by('sort_order', 'ASC');
                foreach ($CI->db->get('sma_cms_webshop_header_footer')->result() as $r) {
                    $append(isset($r->value) ? (string) $r->value : '');
                }
            }
        }

        return empty($parts) ? '' : implode("\n", $parts) . "\n";
    }
}

if (!function_exists('storefront_render_gtag_head_html')) {
    function storefront_render_gtag_head_html($website_setting = null) {
        return storefront_render_tracking_head_html($website_setting);
    }
}

if (!function_exists('storefront_require_gtag_head')) {
    function storefront_require_gtag_head($website_setting = null) {
        $html = storefront_render_tracking_head_html($website_setting);
        if ($html !== '') {
            echo $html;
        }
    }
}
