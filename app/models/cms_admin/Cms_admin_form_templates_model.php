<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * Reusable contact/form templates stored in sma_cms_webshop_contact_forms.
 */
class Cms_admin_form_templates_model extends Cms_admin_base_model
{
    /**
     * @return bool
     */
    public function ensure_table()
    {
        $this->load->model('webshop_api_model');
        if (method_exists($this->webshop_api_model, 'ensure_webshop_contact_forms_table')) {
            return (bool) $this->webshop_api_model->ensure_webshop_contact_forms_table();
        }
        return $this->db->table_exists('sma_cms_webshop_contact_forms');
    }

    /**
     * @param object|null $settings
     * @return string
     */
    public function theme_slug($settings = null)
    {
        $theme = '';
        if (is_object($settings) && isset($settings->theme)) {
            $theme = trim((string) $settings->theme);
        }
        if ($theme === '') {
            $theme = 'default';
        }
        return strtolower(preg_replace('/[^a-z0-9_.-]/', '', $theme));
    }

    /**
     * @param string|null $theme_slug
     * @return array<int,array<string,mixed>>
     */
    public function list_templates($theme_slug = null)
    {
        if (!$this->ensure_table() || !$this->db->table_exists('sma_cms_webshop_contact_forms')) {
            return array();
        }
        if ($theme_slug === null || $theme_slug === '') {
            $theme_slug = $this->theme_slug();
        }
        $q = $this->db
            ->from('sma_cms_webshop_contact_forms')
            ->where('theme_slug', $theme_slug)
            ->order_by('form_name', 'ASC')
            ->order_by('id', 'DESC')
            ->get();
        if (!$q || $q->num_rows() === 0) {
            return array();
        }
        $rows = array();
        foreach ($q->result_array() as $row) {
            $rows[] = $this->hydrate_row($row);
        }
        return $rows;
    }

    /**
     * @param int $id
     * @return array<string,mixed>|null
     */
    public function get_template($id)
    {
        $id = (int) $id;
        if ($id <= 0 || !$this->ensure_table() || !$this->db->table_exists('sma_cms_webshop_contact_forms')) {
            return null;
        }
        $q = $this->db->get_where('sma_cms_webshop_contact_forms', array('id' => $id), 1);
        if (!$q || $q->num_rows() === 0) {
            return null;
        }
        return $this->hydrate_row($q->row_array());
    }

    /**
     * @param int|null $id
     * @param array<string,mixed> $data
     * @return array{ok:bool,message?:string,id?:int}
     */
    public function save_template($id, array $data)
    {
        if (!$this->ensure_table()) {
            return array('ok' => false, 'message' => 'Form templates table could not be created.');
        }

        $theme_slug = isset($data['theme_slug']) ? (string) $data['theme_slug'] : $this->theme_slug();
        $form_key = $this->normalize_form_key(isset($data['form_key']) ? $data['form_key'] : '');
        if ($form_key === '') {
            return array('ok' => false, 'message' => 'Form key is required (letters, numbers, underscore, hyphen).');
        }

        $form_name = trim(strip_tags((string) (isset($data['form_name']) ? $data['form_name'] : '')));
        if ($form_name === '') {
            $form_name = ucfirst(str_replace(array('-', '_'), ' ', $form_key));
        }

        $page_url = $this->normalize_page_url(isset($data['page_url']) ? $data['page_url'] : '');
        $is_active = !empty($data['is_active']) ? 1 : 0;

        $config = $this->build_config(isset($data['config']) && is_array($data['config']) ? $data['config'] : array());
        if (empty($config['fields'])) {
            return array('ok' => false, 'message' => 'Add at least one form field.');
        }

        $encoded = json_encode($config, JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            return array('ok' => false, 'message' => 'Could not encode form configuration.');
        }

        $table = 'sma_cms_webshop_contact_forms';
        $now = date('Y-m-d H:i:s');

        if ($this->form_key_exists($form_key, $theme_slug, (int) $id)) {
            return array('ok' => false, 'message' => 'Form key "' . $form_key . '" already exists for this theme.');
        }

        $row = array(
            'theme_slug'  => $theme_slug,
            'form_key'    => $form_key,
            'page_url'    => $page_url !== '' ? $page_url : null,
            'form_name'   => $form_name,
            'config_json' => $encoded,
            'is_active'   => $is_active,
            'updated_at'  => $now,
        );

        $id = (int) $id;
        if ($id > 0) {
            $existing = $this->get_template($id);
            if (!$existing) {
                return array('ok' => false, 'message' => 'Template not found.');
            }
            $this->db->where('id', $id)->update($table, $row);
            return array('ok' => true, 'message' => 'Form template updated.', 'id' => $id);
        }

        $row['created_at'] = $now;
        $this->db->insert($table, $row);
        $new_id = (int) $this->db->insert_id();
        if ($new_id <= 0) {
            return array('ok' => false, 'message' => 'Failed to save form template.');
        }
        return array('ok' => true, 'message' => 'Form template created.', 'id' => $new_id);
    }

    /**
     * @param int $id
     * @return array{ok:bool,message?:string}
     */
    public function delete_template($id)
    {
        $id = (int) $id;
        if ($id <= 0 || !$this->db->table_exists('sma_cms_webshop_contact_forms')) {
            return array('ok' => false, 'message' => 'Template not found.');
        }
        $this->db->where('id', $id)->delete('sma_cms_webshop_contact_forms');
        return array('ok' => true, 'message' => 'Form template deleted.');
    }

    /**
     * Clone an existing template (inactive copy, no page URL).
     *
     * @param int $id
     * @return array{ok:bool,message?:string,id?:int}
     */
    public function duplicate_template($id)
    {
        $id = (int) $id;
        $template = $this->get_template($id);
        if (!$template) {
            return array('ok' => false, 'message' => 'Template not found.');
        }

        $theme_slug = isset($template['theme_slug']) ? (string) $template['theme_slug'] : $this->theme_slug();
        $base_key = $this->normalize_form_key((isset($template['form_key']) ? $template['form_key'] : 'form') . '_copy');
        if ($base_key === '') {
            $base_key = 'form_copy';
        }
        $form_key = $base_key;
        $suffix = 2;
        while ($this->form_key_exists($form_key, $theme_slug, 0)) {
            $form_key = $base_key . '_' . $suffix;
            $suffix++;
        }

        $form_name = trim((string) (isset($template['form_name']) ? $template['form_name'] : 'Form'));
        if ($form_name === '') {
            $form_name = 'Form copy';
        } else {
            $form_name .= ' (Copy)';
        }

        return $this->save_template(0, array(
            'theme_slug' => $theme_slug,
            'form_key'   => $form_key,
            'form_name'  => $form_name,
            'page_url'   => '',
            'is_active'  => 0,
            'config'     => isset($template['config']) && is_array($template['config']) ? $template['config'] : $this->default_config(),
        ));
    }

    /**
     * @return array<string,mixed>
     */
    public function default_config()
    {
        return array(
            'title'       => '',
            'subtitle'    => '',
            'button_text' => 'Send Message',
            'source'      => 'webshop_contact_form',
            'fields'      => $this->default_fields(),
            'style'       => $this->default_style(),
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function default_fields()
    {
        return array(
            array('name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'map' => 'name', 'placeholder' => 'Enter your name'),
            array('name' => 'phone', 'label' => 'Phone No', 'type' => 'tel', 'required' => true, 'map' => 'phone', 'placeholder' => 'Enter phone number'),
            array('name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => false, 'map' => 'email', 'placeholder' => 'Enter email address'),
            array('name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => false, 'map' => 'message', 'placeholder' => 'Write your message', 'rows' => 4),
        );
    }

    /**
     * @return array<string,string>
     */
    public function default_style()
    {
        return array(
            'container_bg'       => '#ffffff',
            'container_border'   => '#f3f4f6',
            'container_radius'   => '16',
            'container_padding'  => '32',
            'title_color'        => '#0a1d37',
            'title_size'         => '24',
            'subtitle_color'     => '#64748b',
            'label_color'        => '#0a1d37',
            'input_border'       => '#e5e7eb',
            'input_radius'       => '6',
            'input_focus'        => '#b9860b',
            'button_bg'          => '#b9860b',
            'button_hover'       => '#9a7209',
            'button_text_color'  => '#ffffff',
            'grid_columns'       => '1',
            'card_position'      => 'full',
            'custom_css'         => '',
            'preset_key'         => 'default',
        );
    }

    /**
     * Built-in style presets for the form template builder.
     *
     * @return array<string,array<string,mixed>>
     */
    public function style_presets()
    {
        return array(
            'default' => array(
                'label' => 'Swasthe Audit (Navy + Gold)',
                'style' => $this->default_style(),
            ),
            'minimal' => array(
                'label' => 'Minimal Blue',
                'style' => array_merge($this->default_style(), array(
                    'preset_key'        => 'minimal',
                    'container_bg'      => '#f8fafc',
                    'container_border'  => '#e2e8f0',
                    'title_color'       => '#0f172a',
                    'label_color'       => '#0f172a',
                    'button_bg'         => '#2563eb',
                    'button_hover'      => '#1d4ed8',
                    'input_focus'       => '#2563eb',
                )),
            ),
            'dark' => array(
                'label' => 'Dark',
                'style' => array_merge($this->default_style(), array(
                    'preset_key'        => 'dark',
                    'container_bg'      => '#0f172a',
                    'container_border'  => '#1e293b',
                    'title_color'       => '#f8fafc',
                    'subtitle_color'    => '#94a3b8',
                    'label_color'       => '#e2e8f0',
                    'input_border'      => '#334155',
                    'input_focus'       => '#38bdf8',
                    'button_bg'         => '#38bdf8',
                    'button_hover'      => '#0ea5e9',
                    'button_text_color' => '#0f172a',
                )),
            ),
            'emerald' => array(
                'label' => 'Emerald',
                'style' => array_merge($this->default_style(), array(
                    'preset_key'        => 'emerald',
                    'container_bg'      => '#ecfdf5',
                    'container_border'  => '#a7f3d0',
                    'title_color'       => '#064e3b',
                    'subtitle_color'    => '#047857',
                    'label_color'       => '#065f46',
                    'input_border'      => '#6ee7b7',
                    'input_focus'       => '#059669',
                    'button_bg'         => '#059669',
                    'button_hover'      => '#047857',
                    'button_text_color' => '#ffffff',
                )),
            ),
        );
    }

    /**
     * Design summary for list table (label + color swatches).
     *
     * @param array<string,mixed> $config
     * @return array{label:string,preset_key:string,swatches:array<int,string>}
     */
    public function design_info_from_config(array $config)
    {
        $style = isset($config['style']) && is_array($config['style'])
            ? $this->sanitize_style($config['style'])
            : $this->default_style();
        $preset_key = isset($style['preset_key']) ? trim((string) $style['preset_key']) : 'default';
        if ($preset_key === '') {
            $preset_key = 'default';
        }

        $label = 'Custom';
        if ($preset_key !== 'custom') {
            $presets = $this->style_presets();
            if (isset($presets[$preset_key]['label'])) {
                $label = (string) $presets[$preset_key]['label'];
            } else {
                $label = ucfirst(str_replace('_', ' ', $preset_key));
            }
        }

        $swatches = array();
        foreach (array('container_bg', 'title_color', 'button_bg') as $key) {
            if (isset($style[$key]) && $style[$key] !== '') {
                $swatches[] = (string) $style[$key];
            }
        }

        return array(
            'label'      => $label,
            'preset_key' => $preset_key,
            'swatches'   => $swatches,
        );
    }

    /**
     * @param array<string,mixed> $raw
     * @return array<string,mixed>
     */
    public function build_config(array $raw)
    {
        $fields = isset($raw['fields']) && is_array($raw['fields'])
            ? $this->sanitize_fields($raw['fields'])
            : array();
        $style = isset($raw['style']) && is_array($raw['style'])
            ? $this->sanitize_style($raw['style'])
            : $this->default_style();

        $title = trim(strip_tags((string) (isset($raw['title']) ? $raw['title'] : '')));
        $subtitle = trim(strip_tags((string) (isset($raw['subtitle']) ? $raw['subtitle'] : '')));
        $button_text = trim(strip_tags((string) (isset($raw['button_text']) ? $raw['button_text'] : 'Send Message')));
        if ($button_text === '') {
            $button_text = 'Send Message';
        }
        $source = trim(strip_tags((string) (isset($raw['source']) ? $raw['source'] : 'webshop_contact_form')));
        if ($source === '') {
            $source = 'webshop_contact_form';
        }

        $recaptcha_site_key = trim(strip_tags((string) (isset($raw['recaptcha_site_key']) ? $raw['recaptcha_site_key'] : '')));
        $recaptcha_secret_key = trim(strip_tags((string) (isset($raw['recaptcha_secret_key']) ? $raw['recaptcha_secret_key'] : '')));

        return array(
            'title'                => $title,
            'subtitle'             => $subtitle,
            'button_text'          => $button_text,
            'source'               => $source,
            'fields'               => $fields,
            'style'                => $style,
            'recaptcha_site_key'   => $recaptcha_site_key,
            'recaptcha_secret_key' => $recaptcha_secret_key,
        );
    }

    /**
     * @param array<int,mixed> $fields
     * @return array<int,array<string,mixed>>
     */
    public function sanitize_fields(array $fields)
    {
        $allowedTypes = array('text', 'email', 'tel', 'textarea', 'select', 'hidden', 'date', 'country');
        $allowedMaps = array('name', 'phone', 'email', 'message', 'country', 'extra');
        $out = array();
        $usedNames = array();

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }
            $name = preg_replace('/[^a-z0-9_]/', '', strtolower(trim((string) (isset($field['name']) ? $field['name'] : ''))));
            if ($name === '' || isset($usedNames[$name])) {
                continue;
            }
            $usedNames[$name] = true;

            $type = isset($field['type']) ? strtolower(trim((string) $field['type'])) : 'text';
            if (!in_array($type, $allowedTypes, true)) {
                $type = 'text';
            }
            $map = isset($field['map']) ? strtolower(trim((string) $field['map'])) : 'extra';
            if (!in_array($map, $allowedMaps, true)) {
                $map = 'extra';
            }

            $row = array(
                'name'     => $name,
                'label'    => trim(strip_tags((string) (isset($field['label']) ? $field['label'] : $name))),
                'type'     => $type,
                'required' => !empty($field['required']),
                'map'      => $map,
            );
            if ($row['label'] === '') {
                $row['label'] = ucfirst(str_replace('_', ' ', $name));
            }
            if (isset($field['placeholder']) && trim((string) $field['placeholder']) !== '') {
                $row['placeholder'] = trim(strip_tags((string) $field['placeholder']));
            }
            if ($type === 'textarea') {
                $rows = isset($field['rows']) ? (int) $field['rows'] : 4;
                $row['rows'] = $rows > 0 ? $rows : 4;
            }
            if ($type === 'select') {
                $row['options'] = $this->sanitize_select_options(isset($field['options']) ? $field['options'] : array());
            }
            if ($type === 'country') {
                $row['options_source'] = 'country_master';
            }
            if ($type === 'hidden' && isset($field['value'])) {
                $row['value'] = trim(strip_tags((string) $field['value']));
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param mixed $options
     * @return array<int,array{value:string,label:string}>
     */
    public function sanitize_select_options($options)
    {
        $out = array();
        if (!is_array($options)) {
            return $out;
        }
        $seen = array();
        foreach ($options as $opt) {
            if (is_string($opt)) {
                $line = trim($opt);
                if ($line === '') {
                    continue;
                }
                $value = $line;
                $label = $line;
                if (strpos($line, '|') !== false) {
                    list($value, $label) = array_map('trim', explode('|', $line, 2));
                }
            } elseif (is_array($opt)) {
                $value = trim((string) (isset($opt['value']) ? $opt['value'] : ''));
                $label = trim(strip_tags((string) (isset($opt['label']) ? $opt['label'] : $value)));
            } else {
                continue;
            }
            if ($value === '' || isset($seen[$value])) {
                continue;
            }
            $seen[$value] = true;
            $out[] = array(
                'value' => $value,
                'label' => $label !== '' ? $label : $value,
            );
        }
        return $out;
    }

    /**
     * @param array<string,mixed> $style
     * @return array<string,string>
     */
    public function sanitize_style(array $style)
    {
        $defaults = $this->default_style();
        $out = $defaults;

        $colorKeys = array(
            'container_bg', 'container_border', 'title_color', 'subtitle_color',
            'label_color', 'input_border', 'input_focus', 'button_bg', 'button_hover', 'button_text_color',
        );
        foreach ($colorKeys as $key) {
            if (!isset($style[$key])) {
                continue;
            }
            $val = trim((string) $style[$key]);
            if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $val)) {
                $out[$key] = $val;
            }
        }

        $sizeKeys = array('container_radius', 'container_padding', 'title_size', 'input_radius');
        foreach ($sizeKeys as $key) {
            if (!isset($style[$key])) {
                continue;
            }
            $val = preg_replace('/[^0-9.]/', '', (string) $style[$key]);
            if ($val !== '') {
                $out[$key] = $val;
            }
        }

        if (isset($style['grid_columns'])) {
            $cols = (int) $style['grid_columns'];
            if ($cols >= 1 && $cols <= 4) {
                $out['grid_columns'] = (string) $cols;
            }
        }

        if (isset($style['card_position'])) {
            $card_position = strtolower(trim((string) $style['card_position']));
            if (in_array($card_position, array('full', 'left', 'right'), true)) {
                $out['card_position'] = $card_position;
            }
        }

        if (isset($style['custom_css'])) {
            $css = (string) $style['custom_css'];
            $css = preg_replace('/<\/style/i', '', $css);
            $css = preg_replace('/expression\s*\(/i', '', $css);
            $css = preg_replace('/javascript\s*:/i', '', $css);
            $out['custom_css'] = trim($css);
        }

        if (isset($style['preset_key'])) {
            $preset_key = strtolower(preg_replace('/[^a-z0-9_-]/', '', trim((string) $style['preset_key'])));
            if ($preset_key !== '') {
                $out['preset_key'] = $preset_key;
            }
        }

        return $out;
    }

    /**
     * @param array $row
     * @return array<string,mixed>
     */
    protected function hydrate_row(array $row)
    {
        $cfg = array();
        if (isset($row['config_json'])) {
            $decoded = json_decode((string) $row['config_json'], true);
            if (is_array($decoded)) {
                $cfg = $this->build_config($decoded);
            }
        }
        if (empty($cfg)) {
            $cfg = $this->default_config();
        }

        return array(
            'id'          => isset($row['id']) ? (int) $row['id'] : 0,
            'theme_slug'  => isset($row['theme_slug']) ? (string) $row['theme_slug'] : '',
            'form_key'    => isset($row['form_key']) ? (string) $row['form_key'] : '',
            'page_url'    => isset($row['page_url']) ? (string) $row['page_url'] : '',
            'form_name'   => isset($row['form_name']) ? (string) $row['form_name'] : '',
            'is_active'   => !empty($row['is_active']),
            'created_at'  => isset($row['created_at']) ? (string) $row['created_at'] : '',
            'updated_at'  => isset($row['updated_at']) ? (string) $row['updated_at'] : '',
            'config'      => $cfg,
        );
    }

    /**
     * @param string $form_key
     * @param string $theme_slug
     * @param int $exclude_id
     * @return bool
     */
    protected function form_key_exists($form_key, $theme_slug, $exclude_id = 0)
    {
        if (!$this->db->table_exists('sma_cms_webshop_contact_forms')) {
            return false;
        }
        $this->db->from('sma_cms_webshop_contact_forms');
        $this->db->where('theme_slug', $theme_slug);
        $this->db->where('form_key', $form_key);
        if ((int) $exclude_id > 0) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return (int) $this->db->count_all_results() > 0;
    }

    /**
     * @param string $form_key
     * @return string
     */
    public function normalize_form_key($form_key)
    {
        return strtolower(preg_replace('/[^a-z0-9_.-]/', '', trim((string) $form_key)));
    }

    /**
     * @param string $page_url
     * @return string
     */
    protected function normalize_page_url($page_url)
    {
        $page_url = trim((string) $page_url);
        if ($page_url === '') {
            return '';
        }
        $page_url = '/' . ltrim($page_url, '/');
        return $page_url === '//' ? '/' : $page_url;
    }

    /**
     * Public customer-facing webshop base URL (no trailing slash).
     *
     * @return string
     */
    public function resolve_storefront_base_url()
    {
        $CI = function_exists('get_instance') ? get_instance() : null;
        if ($CI) {
            $CI->load->config('elintom_api', false, true);
            $raw = $CI->config->item('webshop_storefront_base_url', 'elintom_api');
            if (is_string($raw) && trim($raw) !== '') {
                return rtrim(trim($raw), '/');
            }
        }

        return rtrim((string) base_url(), '/');
    }

    /**
     * Iframe URL + copy snippet for CMS embed panel.
     *
     * @param array<string,mixed> $template
     * @return array<string,mixed>
     */
    public function build_embed_links(array $template)
    {
        $form_key = $this->normalize_form_key(isset($template['form_key']) ? $template['form_key'] : '');
        $form_name = trim((string) (isset($template['form_name']) ? $template['form_name'] : ''));
        $title = $form_name !== '' ? $form_name : $form_key;
        $base = $this->resolve_storefront_base_url();
        $path = ($base === rtrim((string) base_url(), '/')) ? 'webshop/embed_form/' : 'embed_form/';
        $direct = $form_key !== '' ? $base . '/' . $path . rawurlencode($form_key) : '';

        $iframe_html = '<iframe src="' . htmlspecialchars($direct, ENT_QUOTES, 'UTF-8') . '"'
            . ' title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"'
            . ' width="100%" height="560" scrolling="no" data-embed-form-resize'
            . ' allowtransparency="true"'
            . ' style="border:0;max-width:720px;display:block;overflow:hidden;background:transparent;" loading="lazy"'
            . ' allow="clipboard-write"></iframe>' . "\n"
            . '<script>window.addEventListener("message",function(e){var d=e.data;'
            . 'if(!d||d.type!=="elintom:embedFormHeight"||!d.height){return;}'
            . 'var f=document.querySelectorAll("iframe[data-embed-form-resize]");'
            . 'for(var i=0;i<f.length;i++){if(f[i].contentWindow===e.source){f[i].style.height=Math.ceil(d.height)+"px";}}'
            . '});</script>';

        return array(
            'form_key'        => $form_key,
            'form_name'       => $form_name,
            'is_active'       => !empty($template['is_active']),
            'direct_url'      => $direct,
            'iframe_html'     => $iframe_html,
            'cms_section_key' => $form_key,
        );
    }
}
