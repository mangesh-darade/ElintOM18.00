<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('cms_guide_step')) {
    /**
     * One guide step with optional screenshot and highlight box (% positions).
     *
     * @param string     $text
     * @param string     $image   Filename under themes/default/assets/cms_admin/guide/
     * @param array|null $highlight top, left, width, height, label (all % except label)
     * @param string     $type    normal|difficulty|journey
     * @return array<string,mixed>
     */
    function cms_guide_step($text, $image = '', $highlight = null, $type = 'normal')
    {
        $step = array(
            'text'  => $text,
            'image' => (string) $image,
            'type'  => (string) $type,
        );
        if (is_array($highlight) && !empty($highlight)) {
            $step['highlight'] = $highlight;
        }
        return $step;
    }
}

if (!function_exists('cms_guide_difficulty_step')) {
    /**
     * Step that explains a common new-user problem and how to fix it.
     */
    function cms_guide_difficulty_step($problem, $solution, $image = '', $highlight = null)
    {
        $text = '<span class="cms-guide-difficulty-tag">Common difficulty</span> '
            . $problem
            . ' <span class="cms-guide-difficulty-fix"><strong>Fix:</strong> ' . $solution . '</span>';
        return cms_guide_step($text, $image, $highlight, 'difficulty');
    }
}

if (!function_exists('cms_guide_journey_step')) {
    /**
     * First-time setup path step.
     */
    function cms_guide_journey_step($text, $image = '', $highlight = null)
    {
        return cms_guide_step($text, $image, $highlight, 'journey');
    }
}

if (!function_exists('cms_guide_hl')) {
    /**
     * Highlight rectangle on a guide screenshot (percentages of image size).
     */
    function cms_guide_hl($top, $left, $width, $height, $label = '')
    {
        return array(
            'top'    => (float) $top,
            'left'   => (float) $left,
            'width'  => (float) $width,
            'height' => (float) $height,
            'label'  => (string) $label,
        );
    }
}

/** Sidebar width at 1440px viewport (260px). */
if (!defined('CMS_GUIDE_SB_W')) {
    define('CMS_GUIDE_SB_W', 18.06);
}

if (!function_exists('cms_guide_sidebar_hl')) {
    function cms_guide_sidebar_hl($label = 'Sidebar')
    {
        return cms_guide_hl(0, 0, CMS_GUIDE_SB_W, 100, $label);
    }
}

if (!function_exists('cms_guide_nav_hl')) {
    /**
     * Sidebar nav link highlight by menu index (0 = Dashboard).
     */
    function cms_guide_nav_hl($index, $label)
    {
        $tops = array(
            0  => 11.2,
            1  => 15.4,
            2  => 19.6,
            3  => 23.8,
            4  => 28.0,
            5  => 32.2,
            6  => 36.4,
            7  => 40.6,
            8  => 44.8,
            9  => 49.0,
            10 => 53.2,
            11 => 57.4,
            12 => 61.6,
            13 => 65.8,
            14 => 70.0,
        );
        $top = isset($tops[$index]) ? $tops[$index] : 11.2;
        return cms_guide_hl($top, 0.3, 17.5, 3.25, $label);
    }
}

if (!function_exists('cms_guide_search_hl')) {
    /** Toolbar search input (pages list, newsletter, etc.). */
    function cms_guide_search_hl($label = 'Search')
    {
        return cms_guide_hl(11.8, 58.5, 19.5, 4.0, $label);
    }
}

if (!function_exists('cms_guide_field_hl')) {
    /** Single form field row on edit screens. */
    function cms_guide_field_hl($top, $left, $width, $label, $height = 4.2)
    {
        return cms_guide_hl($top, $left, $width, $height, $label);
    }
}

if (!function_exists('cms_guide_prepare_highlight')) {
    /**
     * Tighten spotlight box and flag compact/lite styling for small UI targets.
     *
     * @param array<string,mixed>|null $hl
     * @return array<string,mixed>|null
     */
    function cms_guide_prepare_highlight($hl)
    {
        if (!is_array($hl) || empty($hl)) {
            return $hl;
        }

        $top = isset($hl['top']) ? (float) $hl['top'] : 0;
        $left = isset($hl['left']) ? (float) $hl['left'] : 0;
        $width = isset($hl['width']) ? (float) $hl['width'] : 10;
        $height = isset($hl['height']) ? (float) $hl['height'] : 5;
        $compact = false;

        // Sidebar nav item
        if ($left <= 1.5 && $width >= 16 && $width <= 19 && $height <= 5) {
            $height = 3.25;
            $top += 0.35;
            $compact = true;
        }
        // Toolbar button or search
        elseif ($top < 22 && $height <= 6.5 && ($left >= 55 || $width <= 18)) {
            if ($left >= 55 && $width >= 18) {
                $width = min($width, 20);
                $height = 4.0;
                $top = max($top, 11.5);
            } else {
                $height = min($height, 4.2);
            }
            $compact = true;
        }
        // Single-line row control (checkbox, toggle, tab, small button)
        elseif ($height >= 5 && $height <= 8 && $width <= 22) {
            $height = min($height, 4.8);
            $compact = true;
        }
        // Tab strip on tag editor
        elseif ($height <= 6 && $width >= 10 && $width <= 16 && $top >= 55) {
            $height = min($height, 4.2);
            $compact = true;
        }

        $hl['top'] = $top;
        $hl['left'] = $left;
        $hl['width'] = $width;
        $hl['height'] = $height;
        if ($compact) {
            $hl['compact'] = true;
        }

        return $hl;
    }
}

if (!function_exists('cms_guide_main_hl')) {
    /**
     * Main content area highlight (right of sidebar).
     */
    function cms_guide_main_hl($top, $height, $label, $left = 18.8, $width = 79.5)
    {
        return cms_guide_hl($top, $left, $width, $height, $label);
    }
}

if (!function_exists('cms_guide_btn_hl')) {
    /** Top-right action button in CMS toolbar. */
    function cms_guide_btn_hl($top, $label, $left = 73.5, $width = 15.5, $height = 4.2)
    {
        return cms_guide_hl($top, $left, $width, $height, $label);
    }
}

if (!function_exists('cms_guide_normalize_step')) {
    /**
     * @param string|array<string,mixed> $step
     * @param string                       $fallback_image
     * @return array<string,mixed>
     */
    function cms_guide_normalize_step($step, $fallback_image = '')
    {
        if (is_string($step)) {
            return array(
                'text'      => $step,
                'image'     => $fallback_image,
                'highlight' => null,
            );
        }
        if (!is_array($step)) {
            return array('text' => '', 'image' => $fallback_image, 'highlight' => null);
        }
        if (empty($step['image'])) {
            $step['image'] = $fallback_image;
        }
        if (!isset($step['highlight'])) {
            $step['highlight'] = null;
        }
        if (!isset($step['type'])) {
            $step['type'] = 'normal';
        }
        return $step;
    }
}

if (!function_exists('cms_guide_count_steps')) {
    /**
     * @param array<string,mixed> $sec
     */
    function cms_guide_count_steps($sec)
    {
        $count = 0;
        if (!empty($sec['workflows']) && is_array($sec['workflows'])) {
            foreach ($sec['workflows'] as $wf) {
                if (!empty($wf['steps']) && is_array($wf['steps'])) {
                    $count += count($wf['steps']);
                }
            }
        } elseif (!empty($sec['steps']) && is_array($sec['steps'])) {
            $count = count($sec['steps']);
        }
        return $count;
    }
}

if (!function_exists('cms_guide_module_nav')) {
    /**
     * Previous / next module for sequential navigation.
     *
     * @param array<int,array<string,mixed>> $sections
     * @param string                           $current_id
     * @return array{prev:array<string,mixed>|null,next:array<string,mixed>|null,index:int,total:int}
     */
    function cms_guide_module_nav(array $sections, $current_id)
    {
        $prev = null;
        $next = null;
        $index = 0;
        $total = count($sections);
        foreach ($sections as $i => $section) {
            if (!isset($section['id']) || (string) $section['id'] !== (string) $current_id) {
                continue;
            }
            $index = $i + 1;
            if ($i > 0) {
                $prev = $sections[$i - 1];
            }
            if ($i < $total - 1) {
                $next = $sections[$i + 1];
            }
            break;
        }
        return array(
            'prev'  => $prev,
            'next'  => $next,
            'index' => $index,
            'total' => $total,
        );
    }
}

if (!function_exists('cms_guide_section_has_step_images')) {
    /**
     * @param array<string,mixed> $sec
     */
    function cms_guide_section_has_step_images($sec)
    {
        if (!empty($sec['workflows']) && is_array($sec['workflows'])) {
            foreach ($sec['workflows'] as $wf) {
                if (empty($wf['steps']) || !is_array($wf['steps'])) {
                    continue;
                }
                foreach ($wf['steps'] as $step) {
                    $n = cms_guide_normalize_step($step, '');
                    if (!empty($n['image'])) {
                        return true;
                    }
                }
            }
        }
        if (!empty($sec['steps']) && is_array($sec['steps'])) {
            foreach ($sec['steps'] as $step) {
                $n = cms_guide_normalize_step($step, '');
                if (!empty($n['image'])) {
                    return true;
                }
            }
        }
        return false;
    }
}
