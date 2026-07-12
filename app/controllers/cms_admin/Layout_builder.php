<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * Storefront header/footer layout builder (multiple named profiles).
 */
class Layout_builder extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('storefront');
    }

    protected function cms_storefront()
    {
        if (!isset($this->cms_storefront_model)) {
            $this->load_cms_model('storefront');
        }
        return $this->cms_storefront_model;
    }

    protected function load_layout_helper()
    {
        $this->load->helper('cms_layout');
    }

    public function index()
    {
        $tab = strtolower(trim((string) $this->input->get('tab')));
        if ($tab !== 'footer') {
            $tab = 'header';
        }

        if ($this->input->post('save_layout_builder')) {
            $this->save_builder($tab);
            return;
        }

        $this->load_layout_helper();

        $profiles = $this->cms_storefront()->schema_ready()
            ? $this->resolve_tab_profiles()
            : $this->resolve_tab_profiles_fast();
        $header_profile = $profiles['header'];
        $footer_profile = $profiles['footer'];
        $profile = ($tab === 'footer') ? $footer_profile : $header_profile;

        if ($this->cms_storefront()->schema_ready()) {
            $req_header = cms_storefront_normalize_profile_slug($this->input->get('header_profile'));
            $req_footer = cms_storefront_normalize_profile_slug($this->input->get('footer_profile'));
            $req_tab = strtolower(trim((string) $this->input->get('tab')));
            if ($req_tab !== 'footer') {
                $req_tab = 'header';
            }
            $canonical = array(
                'header_profile' => $header_profile,
                'footer_profile' => $footer_profile,
            );
            if ($tab === 'footer') {
                $canonical['tab'] = 'footer';
            }
            if (($req_header !== '' && $req_header !== $header_profile)
                || ($req_footer !== '' && $req_footer !== $footer_profile)
                || ($tab === 'footer' && $req_tab !== 'footer')
                || ($tab !== 'footer' && $req_tab === 'footer')) {
                redirect(site_url('cms_admin/layout_builder?' . http_build_query($canonical)));
            }
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('Home')),
            array('link' => '#', 'page' => 'Storefront layout'),
        );
        $meta = array('page_title' => 'Storefront layout builder', 'bc' => $bc);

        $this->data['active_tab'] = $tab;
        $this->data['profile_slug'] = $profile;
        $this->data['header_profile_slug'] = $header_profile;
        $this->data['footer_profile_slug'] = $footer_profile;
        $this->data['preview_url'] = site_url('cms_admin/layout_builder/preview');
        $this->data['page_url_base'] = site_url('cms_admin/layout_builder');
        $this->data['cms_layout_builder'] = true;

        $meta = $this->cms_prepare_page_meta($meta, $this->data);
        $this->cms_release_session_lock();
        $this->cms_layout_builder_construct($meta, $this->data);
    }

    /**
     * Heavy layout editor markup (profiles + form + preview) for async injection.
     */
    public function workspace()
    {
        $this->cms_release_session_lock();

        if (!$this->cms_storefront()->schema_ready()) {
            $this->output->set_content_type('text/html; charset=UTF-8')->set_output(
                '<p class="alert alert-warning cms-layout-builder__loading">'
                . '<strong>Setup required.</strong> Table <code>sma_cms_webshop_header_footer</code> is missing. '
                . 'Rename legacy <code>sma_webshop_header_footer</code> or import the CMS schema, then refresh.'
                . '</p>'
            );
            return;
        }

        $this->load_layout_helper();

        $tab = strtolower(trim((string) $this->input->get('tab')));
        if ($tab !== 'footer') {
            $tab = 'header';
        }

        $profiles = $this->resolve_tab_profiles();
        $header_profile = $profiles['header'];
        $footer_profile = $profiles['footer'];
        $profile = ($tab === 'footer') ? $footer_profile : $header_profile;

        $req_header = cms_storefront_normalize_profile_slug($this->input->get('header_profile'));
        $req_footer = cms_storefront_normalize_profile_slug($this->input->get('footer_profile'));
        $req_tab = strtolower(trim((string) $this->input->get('tab')));
        if ($req_tab !== 'footer') {
            $req_tab = 'header';
        }
        if (($req_header !== '' && $req_header !== $header_profile)
            || ($req_footer !== '' && $req_footer !== $footer_profile)
            || ($tab === 'footer' && $req_tab !== 'footer')
            || ($tab !== 'footer' && $req_tab === 'footer')) {
            $canonical = array(
                'header_profile' => $header_profile,
                'footer_profile' => $footer_profile,
            );
            if ($tab === 'footer') {
                $canonical['tab'] = 'footer';
            }
            redirect(site_url('cms_admin/layout_builder/workspace?' . http_build_query($canonical)));
        }

        $upload_base = base_url('assets/mdata/' . (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost') . '/uploads/');

        $layout_profiles = $this->cms_storefront()->get_layout_builder_profiles_for_section($tab);
        $layout_profiles = $this->cms_storefront()->merge_active_layout_profile_option($layout_profiles, $profile, $tab);

        $data = array(
            'active_tab'            => $tab,
            'profile_slug'          => $profile,
            'header_profile_slug'   => $header_profile,
            'footer_profile_slug'   => $footer_profile,
            'default_profile_slug'  => $this->cms_storefront()->layout_builder_default_profile($tab),
            'bootstrap_profile_slug'=> $this->cms_storefront()->layout_builder_bootstrap_profile(),
            'layout_profiles'       => $layout_profiles,
            'upload_base'           => $upload_base,
            'preview_url'           => site_url('cms_admin/layout_builder/preview'),
            'page_url_base'         => site_url('cms_admin/layout_builder'),
        );

        if ($tab === 'footer') {
            $data['header_config'] = array();
            $data['footer_config'] = $this->cms_storefront()->get_footer_builder_config($footer_profile);
            $data['footer_link_pages'] = function_exists('cms_footer_builder_link_page_options')
                ? cms_footer_builder_link_page_options()
                : array();
        } else {
            $data['header_config'] = $this->cms_storefront()->get_header_builder_config($header_profile);
            $data['footer_config'] = array();
            $data['footer_link_pages'] = array();
        }

        $footer_json = htmlspecialchars(json_encode($data['footer_link_pages']), ENT_QUOTES, 'UTF-8');
        $html = '<script type="application/json" id="cmsLbFooterPagesJson">' . $footer_json . '</script>'
            . $this->load->view($this->theme . 'cms_admin/layout_builder/_profiles', $data, true)
            . $this->load->view($this->theme . 'cms_admin/layout_builder/_workspace', $data, true);

        $this->output->set_content_type('text/html; charset=UTF-8')->set_output($html);
    }

    public function create_profile()
    {
        if (!$this->cms_storefront()->schema_ready()) {
            $this->session->set_flashdata('error', 'Database schema not ready.');
            redirect('cms_admin/layout_builder');
        }

        $this->load_layout_helper();
        $tab = $this->resolve_layout_builder_tab();
        $slug = cms_storefront_normalize_profile_slug($this->input->post('profile_slug'));
        $label = trim((string) $this->input->post('profile_label'));
        $copy = $this->input->post('copy_from_current') === '1';
        $from = $this->resolve_active_profile();

        $err = cms_storefront_validate_profile_slug($slug, 'header', false);
        if ($err !== '') {
            $this->session->set_flashdata('error', $err);
            redirect($this->layout_builder_url($tab, $from));
        }
        if ($this->cms_storefront()->profile_slug_in_use($slug)
            || $this->cms_storefront()->layout_builder_profile_exists($slug)) {
            $this->session->set_flashdata('error', 'A layout with ID "' . $slug . '" already exists.');
            redirect($this->layout_builder_url($tab, $from));
        }

        if (!$this->cms_storefront()->create_layout_builder_profile_for_tab($slug, $label, $tab, $from, $copy)) {
            $this->session->set_flashdata('error', 'Could not create layout.');
            redirect($this->layout_builder_url($tab, $from));
        }

        $this->session->set_flashdata('message', 'Layout "' . $slug . '" created. Customize and save, then assign it on CMS pages.');
        redirect($this->layout_builder_url($tab, $slug));
    }

    public function duplicate_profile()
    {
        if (!$this->cms_storefront()->schema_ready()) {
            redirect('cms_admin/layout_builder');
        }

        $this->load_layout_helper();
        $tab = $this->resolve_layout_builder_tab();
        $from = $this->resolve_active_profile();
        $slug = cms_storefront_normalize_profile_slug($this->input->post('duplicate_slug'));
        $label = trim((string) $this->input->post('duplicate_label'));

        $err = cms_storefront_validate_profile_slug($slug, 'header', false);
        if ($err !== '') {
            $this->session->set_flashdata('error', $err);
            redirect($this->layout_builder_url($tab, $from));
        }
        if ($this->cms_storefront()->profile_slug_in_use($slug)
            || !$this->cms_storefront()->duplicate_layout_builder_profile($from, $slug, $label)) {
            $this->session->set_flashdata('error', 'Could not duplicate layout (ID may already exist).');
            redirect($this->layout_builder_url($tab, $from));
        }

        $this->session->set_flashdata('message', 'Layout duplicated as "' . $slug . '".');
        redirect($this->layout_builder_url($tab, $slug));
    }

    /**
     * @param string|null $profile_slug
     */
    public function delete_profile($profile_slug = null)
    {
        if (!$this->cms_storefront()->schema_ready()) {
            redirect('cms_admin/layout_builder');
        }

        $profile = cms_storefront_normalize_profile_slug(rawurldecode((string) $profile_slug));
        if ($profile === '' || $profile === $this->cms_storefront()->layout_builder_bootstrap_profile()) {
            $this->session->set_flashdata('error', 'The main site layout cannot be deleted.');
            redirect('cms_admin/layout_builder');
        }

        $this->load_cms_model('pages');
        $pages_h = $this->cms_pages_model->getPagesUsingStorefrontProfile('header', $profile);
        $pages_f = $this->cms_pages_model->getPagesUsingStorefrontProfile('footer', $profile);
        if (!empty($pages_h) || !empty($pages_f)) {
            $this->session->set_flashdata('error', 'This layout is still assigned to one or more CMS pages. Change those pages first, then delete.');
            redirect($this->layout_builder_url('header', $profile));
        }

        if (!$this->cms_storefront()->delete_layout_builder_profile($profile)) {
            $this->session->set_flashdata('error', 'Could not delete layout.');
            redirect($this->layout_builder_url('header', $profile));
        }

        $this->session->set_flashdata('message', 'Layout "' . $profile . '" deleted.');
        redirect('cms_admin/layout_builder');
    }

    public function preview()
    {
        if (!$this->cms_storefront()->schema_ready()) {
            show_404();
        }

        $this->load_layout_helper();

        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Pragma: no-cache');

        $upload_base = base_url('assets/mdata/' . (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost') . '/uploads/');
        $tab = $this->input->get('tab') === 'footer' ? 'footer' : 'header';
        $profile = $this->resolve_preview_profile();

        if ($tab === 'footer') {
            $cfg = $this->cms_storefront()->get_footer_builder_config($profile);
            if ($this->input->get('live') === '1') {
                $live = $this->decode_live_preview_config('footer');
                if ($live !== null && function_exists('cms_footer_builder_merge_live_footer_preview')) {
                    $cfg = cms_footer_builder_merge_live_footer_preview($cfg, $live);
                } elseif ($live !== null) {
                    $cfg = $live;
                }
            }
            $compact = $this->input->get('compact') === '1';
            $this->output
                ->set_content_type('text/html; charset=UTF-8')
                ->set_output(cms_footer_builder_preview_document($cfg, $upload_base, $profile, $compact));
            return;
        }

        $cfg = $this->cms_storefront()->get_header_builder_config($profile);
        if ($this->input->get('live') === '1') {
            $live = $this->decode_live_preview_config('header');
            if ($live !== null) {
                $cfg = $live;
            }
        }

        $this->output
            ->set_content_type('text/html; charset=UTF-8')
            ->set_output(cms_header_builder_preview_document($cfg, $upload_base, $profile));
    }

    public function set_default_profile()
    {
        if (!$this->cms_storefront()->schema_ready()) {
            redirect('cms_admin/layout_builder');
        }

        $tab = strtolower(trim((string) $this->input->post('tab')));
        if ($tab !== 'footer') {
            $tab = 'header';
        }

        $profile = cms_storefront_normalize_profile_slug($this->input->post('layout_profile'));
        $profiles = $this->resolve_tab_profiles();
        $header_keep = cms_storefront_normalize_profile_slug($this->input->post('header_layout_profile'));
        $footer_keep = cms_storefront_normalize_profile_slug($this->input->post('footer_layout_profile'));
        if ($header_keep !== '') {
            $profiles['header'] = $header_keep;
        }
        if ($footer_keep !== '') {
            $profiles['footer'] = $footer_keep;
        }

        if ($profile === '' || !$this->cms_storefront()->layout_builder_section_has_profile($tab, $profile)) {
            $this->session->set_flashdata('error', 'Could not set default: layout not found.');
            redirect($this->layout_builder_url($tab, $profiles[$tab], $profiles['header'], $profiles['footer']));
        }

        if ($this->cms_storefront()->set_layout_default_profile($tab, $profile)) {
            $this->session->set_flashdata('message', ucfirst($tab) . ' default layout set to "' . $profile . '".');
        } else {
            $this->session->set_flashdata('error', 'Could not set default layout.');
        }

        redirect($this->layout_builder_url($tab, $profile, $profiles['header'], $profiles['footer']));
    }

    /**
     * URL-only profile slugs for the fast layout-builder shell (no DB).
     *
     * @return array{header:string,footer:string}
     */
    private function resolve_tab_profiles_fast()
    {
        $legacy = cms_storefront_normalize_profile_slug($this->input->get('profile'));
        $header = cms_storefront_normalize_profile_slug($this->input->get('header_profile'));
        $footer = cms_storefront_normalize_profile_slug($this->input->get('footer_profile'));
        if ($header === '' && $legacy !== '') {
            $header = $legacy;
        }
        if ($footer === '' && $legacy !== '') {
            $footer = $legacy;
        }
        if ($header === '') {
            $header = 'site';
        }
        if ($footer === '') {
            $footer = 'site';
        }
        return array('header' => $header, 'footer' => $footer);
    }

    /**
     * Resolve a requested layout slug from GET/POST against DB rows (after sync/ensure).
     *
     * @param string $section header|footer
     * @param string $requested normalized slug from query/body (may be empty)
     * @param string $default     fallback when missing or invalid
     * @return string
     */
    private function finalize_layout_builder_profile_slug($section, $requested, $default)
    {
        $section = strtolower(trim((string) $section)) === 'footer' ? 'footer' : 'header';
        $requested = cms_storefront_normalize_profile_slug($requested);
        $default = cms_storefront_normalize_profile_slug($default);
        if ($default === '') {
            $default = $this->cms_storefront()->layout_builder_bootstrap_profile();
        }

        if ($requested === '') {
            return $default;
        }

        if ($this->cms_storefront()->layout_profile_slug_registered($section, $requested)) {
            return $requested;
        }

        $bootstrap = $this->cms_storefront()->layout_builder_bootstrap_profile();
        if ($requested === $bootstrap) {
            return $requested;
        }
        if ($this->cms_storefront()->layout_builder_section_has_profile($section, $requested)) {
            return $requested;
        }
        if ($this->cms_storefront()->layout_profile_marker_exists($section, $requested)) {
            return $requested;
        }
        if ($this->cms_storefront()->layout_builder_profile_exists($requested)) {
            return $requested;
        }
        if (cms_storefront_validate_profile_slug($requested, $section, false) === '') {
            return $requested;
        }

        return $default;
    }

    /**
     * @return array{header:string,footer:string}
     */
    private function resolve_tab_profiles()
    {
        $this->load_layout_helper();
        $legacy = cms_storefront_normalize_profile_slug($this->input->get('profile'));

        $header = cms_storefront_normalize_profile_slug($this->input->get('header_profile'));
        $footer = cms_storefront_normalize_profile_slug($this->input->get('footer_profile'));

        if ($header === '' && $this->input->post('header_layout_profile')) {
            $header = cms_storefront_normalize_profile_slug($this->input->post('header_layout_profile'));
        }
        if ($footer === '' && $this->input->post('footer_layout_profile')) {
            $footer = cms_storefront_normalize_profile_slug($this->input->post('footer_layout_profile'));
        }
        if ($header === '' && $this->input->post('layout_profile')) {
            $tab = strtolower(trim((string) $this->input->get('tab')));
            if ($tab !== 'footer') {
                $header = cms_storefront_normalize_profile_slug($this->input->post('layout_profile'));
            }
        }
        if ($footer === '' && $this->input->post('layout_profile')) {
            $tab = strtolower(trim((string) $this->input->get('tab')));
            if ($tab === 'footer') {
                $footer = cms_storefront_normalize_profile_slug($this->input->post('layout_profile'));
            }
        }

        if ($header === '' && $legacy !== '') {
            $header = $legacy;
        }
        if ($footer === '' && $legacy !== '') {
            $footer = $legacy;
        }
        $default = $this->cms_storefront()->layout_builder_default_profile('header');
        $footer_default = $this->cms_storefront()->layout_builder_default_profile('footer');

        if ($this->cms_storefront()->schema_ready()) {
            $header = $this->finalize_layout_builder_profile_slug('header', $header, $default);
            $footer = $this->finalize_layout_builder_profile_slug('footer', $footer, $footer_default);
        } else {
            if ($header === '') {
                $header = $default !== '' ? $default : 'site';
            }
            if ($footer === '') {
                $footer = $footer_default !== '' ? $footer_default : 'site';
            }
        }

        return array('header' => $header, 'footer' => $footer);
    }

    /**
     * @return string
     */
    /**
     * Active builder tab from POST (form submit) or GET.
     *
     * @return string header|footer
     */
    private function resolve_layout_builder_tab()
    {
        $tab = strtolower(trim((string) $this->input->post('tab')));
        if ($tab === '') {
            $tab = strtolower(trim((string) $this->input->get('tab')));
        }
        return ($tab === 'footer') ? 'footer' : 'header';
    }

    private function resolve_active_profile()
    {
        $profiles = $this->resolve_tab_profiles();
        $tab = $this->resolve_layout_builder_tab();
        return ($tab === 'footer') ? $profiles['footer'] : $profiles['header'];
    }

    /**
     * Preview may use any slug that exists; falls back to site.
     *
     * @return string
     */
    private function resolve_preview_profile()
    {
        $profiles = $this->resolve_tab_profiles();
        $tab = $this->input->get('tab') === 'footer' ? 'footer' : 'header';
        $profile = ($tab === 'footer') ? $profiles['footer'] : $profiles['header'];
        if (!$this->cms_storefront()->layout_builder_profile_exists($profile)) {
            $profile = $this->cms_storefront()->layout_builder_default_profile($tab === 'footer' ? 'footer' : 'header');
        }
        return $profile;
    }

    /**
     * @param string $tab header|footer
     * @return array<string,mixed>|null
     */
    private function decode_live_preview_config($tab)
    {
        $raw = (string) $this->input->get('cfg');
        if ($raw === '') {
            return null;
        }
        $raw = strtr($raw, '-_', '+/');
        $pad = strlen($raw) % 4;
        if ($pad > 0) {
            $raw .= str_repeat('=', 4 - $pad);
        }
        $decoded = json_decode(base64_decode($raw), true);
        if (!is_array($decoded)) {
            return null;
        }
        return $tab === 'footer'
            ? cms_sanitize_footer_builder_config($decoded)
            : cms_sanitize_header_builder_config($decoded);
    }

    /**
     * @param string $tab
     * @param string $profile
     * @return string
     */
    /**
     * @param string      $tab
     * @param string      $profile profile being edited on $tab
     * @param string|null $header_profile
     * @param string|null $footer_profile
     * @return string
     */
    private function layout_builder_url($tab, $profile, $header_profile = null, $footer_profile = null)
    {
        $profiles = $this->resolve_tab_profiles();
        $profile = cms_storefront_normalize_profile_slug($profile);
        if ($profile === '') {
            $profile = ($tab === 'footer') ? $profiles['footer'] : $profiles['header'];
        }

        $header = cms_storefront_normalize_profile_slug($header_profile);
        $footer = cms_storefront_normalize_profile_slug($footer_profile);
        if ($header === '') {
            $header = $profiles['header'];
        }
        if ($footer === '') {
            $footer = $profiles['footer'];
        }
        if ($tab === 'footer') {
            $footer = $profile;
        } else {
            $header = $profile;
        }
        if ($header === '') {
            $header = $this->cms_storefront()->layout_builder_default_profile('header');
        }
        if ($footer === '') {
            $footer = $this->cms_storefront()->layout_builder_default_profile('footer');
        }

        $q = array(
            'header_profile' => $header,
            'footer_profile' => $footer,
        );
        if ($tab === 'footer') {
            $q['tab'] = 'footer';
        }
        return 'cms_admin/layout_builder?' . http_build_query($q);
    }

    /**
     * @param string $tab header|footer
     */
    private function save_builder($tab)
    {
        $this->load_layout_helper();

        $profile = cms_storefront_normalize_profile_slug($this->input->post('layout_profile'));
        $header_post = cms_storefront_normalize_profile_slug($this->input->post('header_layout_profile'));
        $footer_post = cms_storefront_normalize_profile_slug($this->input->post('footer_layout_profile'));
        if ($tab === 'footer') {
            if ($footer_post !== '') {
                $profile = $footer_post;
            }
        } elseif ($header_post !== '') {
            $profile = $header_post;
        }
        if ($profile === '' || !$this->cms_storefront()->is_layout_builder_profile($profile)) {
            $profile = $this->cms_storefront()->layout_builder_default_profile($tab === 'footer' ? 'footer' : 'header');
        }

        $saved_profiles = $this->resolve_tab_profiles();
        $header_keep = cms_storefront_normalize_profile_slug($this->input->post('header_layout_profile'));
        $footer_keep = cms_storefront_normalize_profile_slug($this->input->post('footer_layout_profile'));
        if ($header_keep !== '') {
            $saved_profiles['header'] = $header_keep;
        }
        if ($footer_keep !== '') {
            $saved_profiles['footer'] = $footer_keep;
        }
        if ($tab === 'footer') {
            $saved_profiles['footer'] = $profile;
        } else {
            $saved_profiles['header'] = $profile;
        }

        if ($tab === 'footer') {
            $config = array(
                'tagline'                => $this->input->post('tagline'),
                'copyright'              => $this->input->post('copyright'),
                'newsletter_title'       => $this->input->post('newsletter_title'),
                'newsletter_desc'        => $this->input->post('newsletter_desc'),
                'newsletter_placeholder' => $this->input->post('newsletter_placeholder'),
                'legal_link_1_label'     => $this->input->post('legal_link_1_label'),
                'legal_link_1_url'       => $this->input->post('legal_link_1_url'),
                'legal_link_2_label'     => $this->input->post('legal_link_2_label'),
                'legal_link_2_url'       => $this->input->post('legal_link_2_url'),
                'show_floating_whatsapp'    => cms_layout_builder_flag_from_post('show_floating_whatsapp'),
                'floating_whatsapp_phone'   => $this->input->post('floating_whatsapp_phone'),
                'floating_whatsapp_message' => $this->input->post('floating_whatsapp_message'),
                'bg_color'               => $this->input->post('footer_bg_color'),
                'text_color'             => $this->input->post('footer_text_color'),
                'accent_color'           => $this->input->post('footer_accent_color'),
                'logo_image'             => $this->input->post('logo_image'),
            );
            if (!isset($this->cms_model)) {
                $this->load->model('cms_model');
            }
            $config['footer_sections'] = cms_footer_builder_parse_sections_from_post($this->input->post('footer_sections'));
            $config = cms_footer_builder_merge_section_toggles_from_post($config);

            $config['social_items'] = cms_footer_builder_parse_social_items_from_post($this->input->post('social_items'));

            $order_raw = $this->input->post('footer_element_order');
            if (is_string($order_raw) && $order_raw !== '') {
                $decoded_order = json_decode($order_raw, true);
                if (is_array($decoded_order)) {
                    $config['footer_element_order'] = $decoded_order;
                }
            }

            foreach (cms_footer_builder_show_element_defs($config) as $def) {
                if (!empty($def['is_footer_section'])) {
                    continue;
                }
                $config[$def['show_key']] = cms_layout_builder_flag_from_post($def['show_key']);
                $config[$def['align_key']] = $this->input->post($def['align_key']);
                if (!empty($def['title_align_key'])) {
                    $config[$def['title_align_key']] = $this->input->post($def['title_align_key']);
                }
            }

            if (is_array($config['social_items'])) {
                foreach ($config['social_items'] as $si => $soc) {
                    $file_key = 'social_icon_file_' . (int) $si;
                    if (!empty($_FILES[$file_key]['name'])) {
                        $up = $this->do_upload($file_key, '', true);
                        if ($up['status'] === 'success') {
                            $config['social_items'][$si]['icon_image'] = 'webshop/' . $up['upload_data']['file_name'];
                        }
                    }
                }
            }
            if (!empty($_FILES['logo_file']['name'])) {
                $up = $this->do_upload('logo_file', '', true);
                if ($up['status'] === 'success') {
                    $config['logo_image'] = 'webshop/' . $up['upload_data']['file_name'];
                } else {
                    $this->session->set_flashdata('error', strip_tags($up['error']));
                    redirect($this->layout_builder_url('footer', $profile, $saved_profiles['header'], $saved_profiles['footer']));
                }
            }
            $config = cms_layout_builder_merge_element_colors_from_post($config, 'footer');
            $config = cms_layout_builder_merge_element_styles_from_post($config, 'footer');
            $ok = $this->cms_storefront()->save_footer_builder_config($config, $profile);
            if ($ok && function_exists('cms_layout_builder_generate_and_save_css')) {
                cms_layout_builder_generate_and_save_css();
            }
            if ($ok) {
                if (function_exists('cms_webshop_clear_getsettings_cache')) {
                    cms_webshop_clear_getsettings_cache();
                }
                if (function_exists('cms_webshop_refresh_storefront_cms_cache')) {
                    cms_webshop_refresh_storefront_cms_cache();
                }
            }
            $this->session->set_flashdata($ok ? 'message' : 'error', $ok ? 'Footer saved.' : 'Could not save footer.');
            redirect($this->layout_builder_url('footer', $profile, $saved_profiles['header'], $saved_profiles['footer']));
        }

        $existing = $this->cms_storefront()->get_header_builder_config($profile);

        $config = array(
            'show_logo'      => cms_layout_builder_flag_from_post('show_logo'),
            'show_cart'      => cms_layout_builder_flag_from_post('show_cart'),
            'show_wishlist'  => cms_layout_builder_flag_from_post('show_wishlist'),
            'show_account'   => cms_layout_builder_flag_from_post('show_account'),
            'show_search'    => cms_layout_builder_flag_from_post('show_search'),
            'show_nav'       => cms_layout_builder_flag_from_post('show_nav'),
            'show_phone'     => cms_layout_builder_flag_from_post('show_phone'),
            'show_promo'     => cms_layout_builder_flag_from_post('show_promo'),
            'show_button'    => cms_layout_builder_flag_from_post('show_button'),
            'logo_image'     => $this->input->post('logo_image'),
            'favicon_image'  => $this->input->post('favicon_image'),
            'phone'          => $this->input->post('phone'),
            'promo_text'     => $this->input->post('promo_text', false),
            'button_text'    => $this->input->post('button_text', false),
            'button_link'    => $this->input->post('button_link'),
            'bg_color'       => $this->input->post('bg_color'),
            'text_color'     => $this->input->post('text_color'),
            'accent_color'   => $this->input->post('accent_color'),
            'icon_bg'        => $this->input->post('icon_bg'),
            'promo_align'    => $this->input->post('promo_align'),
            'logo_align'     => $this->input->post('logo_align'),
            'nav_align'      => $this->input->post('nav_align'),
            'phone_align'    => $this->input->post('phone_align'),
            'search_align'   => $this->input->post('search_align'),
            'wishlist_align' => $this->input->post('wishlist_align'),
            'cart_align'     => $this->input->post('cart_align'),
            'account_align'  => $this->input->post('account_align'),
            'button_align'   => $this->input->post('button_align'),
        );

        if (!empty($_FILES['logo_file']['name'])) {
            $up = $this->do_upload('logo_file', '', true);
            if ($up['status'] === 'success') {
                $config['logo_image'] = 'webshop/' . $up['upload_data']['file_name'];
            } else {
                $this->session->set_flashdata('error', strip_tags($up['error']));
                redirect($this->layout_builder_url('header', $profile, $saved_profiles['header'], $saved_profiles['footer']));
            }
        }
        
        if (!empty($_FILES['favicon_file']['name'])) {
            $up = $this->do_upload('favicon_file', '', true);
            if ($up['status'] === 'success') {
                $config['favicon_image'] = 'webshop/' . $up['upload_data']['file_name'];
            } else {
                $this->session->set_flashdata('error', strip_tags($up['error']));
                redirect($this->layout_builder_url('header', $profile, $saved_profiles['header'], $saved_profiles['footer']));
            }
        }

        if ($this->input->post('google_analytics_present')) {
            $ga_raw = $this->input->post('google_analytics', false);
            if ($ga_raw === null && isset($_POST['google_analytics'])) {
                $ga_raw = $_POST['google_analytics'];
            }
            $ga_raw = trim((string) $ga_raw);
            $ga_prepared = cms_header_builder_prepare_google_analytics_value($ga_raw);
            if ($ga_raw !== '' && $ga_prepared === false) {
                $this->session->set_flashdata(
                    'error',
                    'Invalid Google Analytics script. Paste the full Google tag (gtag.js) snippet from Google Analytics (comment + both script blocks).'
                );
                redirect($this->layout_builder_url('header', $profile, $saved_profiles['header'], $saved_profiles['footer']));
            }
            $config['google_analytics'] = ($ga_prepared === false) ? '' : (string) $ga_prepared;
        }

        // Bypass XSS filter so <script> blocks are preserved (same as google_analytics).
        $custom_tags_raw = $this->input->post('custom_header_tags', false);
        if (!is_array($custom_tags_raw) && isset($_POST['custom_header_tags']) && is_array($_POST['custom_header_tags'])) {
            $custom_tags_raw = $_POST['custom_header_tags'];
        }
        if ($this->input->post('custom_header_tags_present')) {
            if (is_array($custom_tags_raw)) {
                $parsed_tags = array();
                foreach ($custom_tags_raw as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $tag_name = isset($row['name']) ? trim((string) $row['name']) : '';
                    $tag_code = isset($row['code']) ? trim((string) $row['code']) : '';
                    if ($tag_code === '') {
                        continue;
                    }
                    $tag_prepared = cms_header_builder_prepare_custom_header_tag_code($tag_code);
                    if ($tag_prepared === false) {
                        $label = $tag_name !== '' ? '"' . $tag_name . '"' : 'custom header tag';
                        $this->session->set_flashdata(
                            'error',
                            'Invalid ' . $label . '. Use a full &lt;title&gt;, &lt;meta&gt;, &lt;link&gt;, &lt;script&gt;, &lt;noscript&gt; tag, or a schema.org JSON-LD object ({&quot;@context&quot;:…}). Plain text only is not allowed.'
                        );
                        redirect($this->layout_builder_url('header', $profile, $saved_profiles['header'], $saved_profiles['footer']));
                    }
                    $parsed_tags[] = array(
                        'name' => $tag_name,
                        'code' => (string) $tag_prepared,
                    );
                }
                $config['custom_header_tags'] = cms_header_builder_normalize_custom_header_tags($parsed_tags);
            } else {
                $config['custom_header_tags'] = cms_header_builder_get_custom_header_tags($existing);
            }
        } else {
            $config['custom_header_tags'] = cms_header_builder_get_custom_header_tags($existing);
        }

        $config = array_merge(is_array($existing) ? $existing : array(), $config);
        $config = cms_layout_builder_merge_element_colors_from_post($config, 'header');
        $config = cms_layout_builder_merge_element_styles_from_post($config, 'header');
        $ok = $this->cms_storefront()->save_header_builder_config($config, $profile);
        if ($ok) {
            if (function_exists('cms_webshop_clear_getsettings_cache')) {
                cms_webshop_clear_getsettings_cache();
            }
            if (function_exists('cms_webshop_refresh_storefront_cms_cache')) {
                cms_webshop_refresh_storefront_cms_cache();
            }
        }
        $this->session->set_flashdata($ok ? 'message' : 'error', $ok ? 'Header saved. Preview updated on the right.' : 'Could not save header.');
        redirect($this->layout_builder_url('header', $profile, $saved_profiles['header'], $saved_profiles['footer']));
    }
}
