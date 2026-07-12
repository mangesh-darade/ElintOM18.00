<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * Central media library for webshop/CMS images.
 */
class Media extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('cms_media');
        $this->load_cms_model('media');
    }

    public function index()
    {
        $preset = strtolower(trim((string) $this->input->get('preset')));
        $q = trim((string) $this->input->get('q'));
        $scan = $this->cms_media_model->list_files($this->Customer_assets, array(
            'preset' => $preset !== '' ? $preset : 'all',
            'q'      => $q,
            'limit'  => 500,
        ));

        $this->data['presets'] = $this->cms_media_model->get_presets();
        $this->data['items'] = isset($scan['items']) ? $scan['items'] : array();
        $this->data['total'] = isset($scan['total']) ? (int) $scan['total'] : 0;
        $this->data['active_preset'] = $preset !== '' ? $preset : 'all';
        $this->data['search_q'] = $q;
        $this->data['upload_base'] = cms_media_uploads_base_url($this->Customer_assets);

        $meta = array('page_title' => 'Media Library');
        $this->cms_page_construct('cms_admin/media/index', $meta, $this->data);
    }

    public function list_json()
    {
        $this->cms_release_session_lock();
        $preset = strtolower(trim((string) $this->input->get('preset')));
        $q = trim((string) $this->input->get('q'));
        $limit = (int) $this->input->get('limit');
        $offset = (int) $this->input->get('offset');
        if ($limit <= 0) {
            $limit = 60;
        }

        $scan = $this->cms_media_model->list_files($this->Customer_assets, array(
            'preset' => $preset !== '' ? $preset : 'all',
            'q'      => $q,
            'limit'  => $limit,
            'offset' => $offset,
        ));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success'  => true,
                'items'    => isset($scan['items']) ? $scan['items'] : array(),
                'total'    => isset($scan['total']) ? (int) $scan['total'] : 0,
                'presets'  => array_values($this->cms_media_model->get_presets()),
            )));
    }

    public function upload()
    {
        $this->cms_release_session_lock();
        if (strtoupper($this->input->method()) !== 'POST') {
            return $this->json_error('Invalid request method.', 405);
        }

        $preset = strtolower(trim((string) $this->input->post('preset')));
        if ($preset === '' || !cms_media_preset($preset)) {
            $preset = 'misc';
        }

        if (empty($_FILES['media_file']['name'])) {
            return $this->json_error('No file selected.');
        }

        $result = $this->cms_media_model->upload('media_file', $preset, $this->Customer_assets);
        if ($result['status'] !== 'success') {
            return $this->json_error(isset($result['error']) ? $result['error'] : 'Upload failed.');
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success'     => true,
                'stored_path' => $result['stored_path'],
                'url'         => $result['url'],
                'preset'      => $result['preset'],
                'message'     => 'File uploaded.',
            )));
    }

    public function delete()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            return $this->json_error('Invalid request method.', 405);
        }

        $stored_path = cms_media_normalize_stored_path($this->input->post('stored_path'));
        if ($stored_path === '') {
            return $this->json_error('Invalid file path.');
        }

        $result = $this->cms_media_model->delete_file($stored_path, $this->Customer_assets);
        if ($result['status'] !== 'success') {
            return $this->json_error(isset($result['error']) ? $result['error'] : 'Delete failed.');
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => true,
                'message' => 'File deleted.',
            )));
    }

    /**
     * @param string $message
     * @param int    $code
     */
    protected function json_error($message, $code = 400)
    {
        return $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => false,
                'error'   => (string) $message,
            )));
    }
}
