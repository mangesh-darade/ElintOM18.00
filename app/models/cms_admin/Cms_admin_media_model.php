<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

class Cms_admin_media_model extends Cms_admin_base_model
{
    /**
     * @return array<string,array<string,mixed>>
     */
    public function get_presets()
    {
        $this->load->helper('cms_media');
        return cms_media_presets();
    }

    /**
     * @param string $customer_assets
     * @param array<string,mixed> $options
     * @return array{items:array<int,array<string,mixed>>,total:int}
     */
    public function list_files($customer_assets, array $options = array())
    {
        $this->load->helper('cms_media');
        return cms_media_scan_files($customer_assets, $options);
    }

    /**
     * @param string $field_name
     * @param string $preset_slug
     * @param string $customer_assets
     * @return array{status:string,error?:string,stored_path?:string,url?:string,upload_data?:array}
     */
    public function upload($field_name, $preset_slug, $customer_assets)
    {
        $this->load->helper('cms_media');
        $preset = cms_media_preset($preset_slug);
        if (!$preset) {
            $preset_slug = 'misc';
            $preset = cms_media_presets()['misc'];
        }

        $folder = (string) $preset['folder'];
        $relative_upload_dir = 'assets/mdata/' . $customer_assets . '/uploads/webshop/' . $folder . '/';
        $absolute_upload_dir = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative_upload_dir);
        if (!is_dir($absolute_upload_dir)) {
            @mkdir($absolute_upload_dir, 0777, true);
        }

        $config = array(
            'upload_path'   => $absolute_upload_dir,
            'allowed_types' => implode('|', cms_media_allowed_extensions()),
            'overwrite'     => false,
            'max_size'      => '2048000',
            'encrypt_name'  => true,
        );

        $CI = get_instance();
        $CI->load->library('upload', $config);
        if (!$CI->upload->do_upload($field_name)) {
            $err = strip_tags($CI->upload->display_errors('', ''));
            if ($err === '') {
                $err = 'Upload failed. Check file type and size (max 2 MB).';
            }
            return array(
                'status' => 'fail',
                'error'  => $err,
            );
        }

        $data = $CI->upload->data();
        $stored_path = cms_media_stored_path($preset_slug, $data['file_name']);

        return array(
            'status'      => 'success',
            'stored_path' => $stored_path,
            'url'         => cms_media_public_url($stored_path, $customer_assets),
            'upload_data' => $data,
            'preset'      => $preset_slug,
        );
    }

    /**
     * @param string $stored_path
     * @param string $customer_assets
     * @return array{status:string,error?:string}
     */
    public function delete_file($stored_path, $customer_assets)
    {
        $this->load->helper('cms_media');
        $stored_path = cms_media_normalize_stored_path($stored_path);
        if ($stored_path === '') {
            return array('status' => 'fail', 'error' => 'Invalid file path.');
        }
        if (!cms_media_is_deletable_path($stored_path)) {
            return array('status' => 'fail', 'error' => 'Only files uploaded to the Media Library can be deleted here.');
        }

        $disk = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'mdata'
            . DIRECTORY_SEPARATOR . trim((string) $customer_assets) . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $stored_path);

        if (!is_file($disk)) {
            return array('status' => 'fail', 'error' => 'File not found.');
        }

        if (!@unlink($disk)) {
            return array('status' => 'fail', 'error' => 'Could not delete file.');
        }

        return array('status' => 'success');
    }
}
