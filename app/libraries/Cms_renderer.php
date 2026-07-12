<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cms_renderer {

    protected $CI;

    /** @var array Runtime context (e.g. product_id on product detail page). */
    protected $page_context = array();

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->helper('cms_layout');
    }

    /**
     * @param array $context
     * @return $this
     */
    public function set_page_context(array $context = array())
    {
        $this->page_context = is_array($context) ? $context : array();
        return $this;
    }

    /**
     * @return array
     */
    public function get_page_context()
    {
        return $this->page_context;
    }

    /**
     * Render all sections for a page
     */
    public function render_sections($sections) {
        $html = '';
        foreach ($sections as $section) {
            $html .= $this->render_section($section);
        }
        return $html;
    }

    /**
     * Resolve theme component view path (themes/{theme}/views/components/{type}.php).
     *
     * @param string $section_type
     * @return string CI view path or empty
     */
    protected function component_view_path($section_type)
    {
        $section_type = preg_replace('/[^a-z0-9_]/', '', strtolower(trim((string) $section_type)));
        if ($section_type === '') {
            return '';
        }

        $theme = 'default';
        if (isset($this->CI->Settings->theme) && trim((string) $this->CI->Settings->theme) !== '') {
            $theme = trim((string) $this->CI->Settings->theme);
        }

        $themes_to_try = array($theme);
        if ($theme !== 'default') {
            $themes_to_try[] = 'default';
        }

        foreach ($themes_to_try as $try_theme) {
            $relative = $try_theme . '/views/components/' . $section_type;
            $candidates = array(
                FCPATH . 'themes/' . $relative . '.php',
                VIEWPATH . $relative . '.php',
            );

            foreach ($candidates as $file) {
                if (is_file($file)) {
                    return $relative;
                }
            }

            if ($section_type === 'hero_banner' && is_file(FCPATH . 'themes/' . $try_theme . '/views/components/banner.php')) {
                return $try_theme . '/views/components/banner';
            }
        }

        return '';
    }

    /**
     * Render a single section
     */
    public function render_section($section) {
        if (!is_array($section)) {
            return '';
        }

        $section_type = isset($section['section_type']) ? (string) $section['section_type'] : '';
        $raw_section_contain = isset($section['section_contain']) ? $section['section_contain'] : (isset($section['config_json']) ? $section['config_json'] : null);
        $config = json_decode($raw_section_contain, true);

        if (!is_array($config)) {
            $config = array();
        }

        $data = array();
        if (!empty($section['is_dynamic']) && !empty($section['util_function'])) {
            $util_function = $section['util_function'];
            if ($this->CI->load->model('cms_model') && method_exists($this->CI->cms_model, $util_function)) {
                $data = $this->_invoke_section_util($util_function, $config);
            }
        }

        $view_data = array(
            'section' => $section,
            'config'  => $config,
            'data'    => $data,
        );

        $view_path = $this->component_view_path($section_type);
        if ($view_path === '') {
            return '<!-- Component view not found: ' . htmlspecialchars($section_type, ENT_QUOTES, 'UTF-8') . ' -->';
        }

        return $this->CI->load->view($view_path, $view_data, true);
    }

    /**
     * Call cms_model util; pass page_context only when the method accepts it.
     *
     * @param string $util_function
     * @param array  $config
     * @return array
     */
    protected function _invoke_section_util($util_function, array $config)
    {
        $model = $this->CI->cms_model;
        try {
            $ref = new ReflectionMethod($model, $util_function);
            if ($ref->getNumberOfParameters() >= 2) {
                return $model->$util_function($config, $this->page_context);
            }
        } catch (ReflectionException $e) {
            return array();
        }

        return $model->$util_function($config);
    }
}
