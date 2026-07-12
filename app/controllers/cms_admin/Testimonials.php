<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * CMS testimonials (sma_cms_testimonials) — manage client quotes for webshop sections.
 */
class Testimonials extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cms_testimonials_model');
    }

    public function index()
    {
        $this->data['rows'] = $this->cms_testimonials_model->list_all_admin();
        $this->data['schema_ready'] = $this->cms_testimonials_model->ensure_table();

        $meta = array('page_title' => 'Testimonials');
        $this->data['cms_list_datatable'] = true;
        $this->cms_page_construct('cms_admin/testimonials/index', $meta, $this->data);
    }

    public function add()
    {
        $this->edit_form(0);
    }

    public function edit($id = null)
    {
        $this->edit_form((int) $id);
    }

    /**
     * @param int $id
     */
    protected function edit_form($id)
    {
        $id = (int) $id;
        $is_new = $id <= 0;
        $post = $is_new ? null : $this->cms_testimonials_model->get_by_id($id);

        if (!$is_new && !$post) {
            $this->session->set_flashdata('error', 'Testimonial not found.');
            $this->cms_redirect('testimonials');
        }

        $repopulate_from_post = false;
        if ($this->input->post('save_cms_testimonial')) {
            $result = $this->save_from_post($id);
            if (!empty($result['ok'])) {
                $this->session->set_flashdata('message', isset($result['message']) ? $result['message'] : 'Saved.');
                $redirect_id = isset($result['id']) ? (int) $result['id'] : $id;
                $this->cms_redirect('testimonials/edit/' . $redirect_id);
            }
            $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Save failed.');
            $repopulate_from_post = true;
        }

        if ($repopulate_from_post) {
            $this->data['post'] = $this->post_from_request($id);
        } elseif ($is_new) {
            $this->data['post'] = array(
                'id'          => 0,
                'person_name' => '',
                'photo'       => '',
                'comments'    => '',
                'status'      => 'draft',
                'sort_order'  => 0,
                'is_active'   => true,
            );
        } else {
            $this->data['post'] = $post;
        }

        $this->data['schema_ready'] = $this->cms_testimonials_model->ensure_table();
        $this->data['is_new'] = $is_new;

        $meta = array('page_title' => $is_new ? 'Add Testimonial' : 'Edit Testimonial');
        $this->cms_page_construct('cms_admin/testimonials/edit', $meta, $this->data);
    }

    public function delete($id = null)
    {
        $id = (int) $id;
        if ($id < 1) {
            $this->session->set_flashdata('error', 'Invalid testimonial.');
            $this->cms_redirect('testimonials');
        }
        $result = $this->cms_testimonials_model->delete($id);
        $this->session->set_flashdata(
            !empty($result['ok']) ? 'message' : 'error',
            isset($result['message']) ? $result['message'] : 'Delete failed.'
        );
        $this->cms_redirect('testimonials');
    }

    /**
     * @param int $id
     * @return array{ok:bool,id:int,message:string}
     */
    protected function save_from_post($id)
    {
        $userId = (int) $this->session->userdata('user_id');
        $data = $this->post_from_request($id);

        if (!empty($_FILES['photo_file']['name'])) {
            $upload = $this->do_upload('photo_file', 'cms_testimonials', true);
            if (!empty($upload['error']) || (isset($upload['status']) && $upload['status'] === 'fail')) {
                return array('ok' => false, 'id' => $id, 'message' => isset($upload['error']) ? (string) $upload['error'] : 'Photo upload failed.');
            }
            if (!empty($upload['upload_data']['file_name'])) {
                $data['photo'] = 'cms_testimonials/' . $upload['upload_data']['file_name'];
            }
        }

        return $this->cms_testimonials_model->save($data, $userId);
    }

    /**
     * @param int $id
     * @return array<string,mixed>
     */
    protected function post_from_request($id)
    {
        return array(
            'id'          => (int) $id,
            'person_name' => $this->input->post('person_name'),
            'photo'       => $this->input->post('photo'),
            'comments'    => $this->input->post('comments'),
            'status'      => $this->input->post('status'),
            'sort_order'  => $this->input->post('sort_order'),
            'is_active'   => $this->input->post('is_active') ? 1 : 0,
        );
    }
}
