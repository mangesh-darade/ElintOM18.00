<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

/**
 * CMS blog posts (sma_cms_blogs) — manage articles shown in webshop blog_grid sections.
 */
class Blogs extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Cms_blogs_model', 'cms_blogs_model');
        $this->load->model('Cms_blog_categories_model', 'cms_blog_categories_model');
    }

    public function index()
    {
        if ($this->input->post('save_blog_category')) {
            $this->handle_save_category(0);
            return;
        }

        $this->data['rows'] = $this->cms_blogs_model->list_all_admin();
        $this->data['categories'] = $this->cms_blog_categories_model->list_all();
        $this->data['schema_ready'] = $this->cms_blogs_model->ensure_table();
        $this->data['categories_ready'] = $this->cms_blog_categories_model->ensure_table();

        $meta = array('page_title' => 'Blog Posts');
        $this->data['cms_list_datatable'] = true;
        $this->data['cms_enhancements'] = true;
        $this->cms_page_construct('cms_admin/blogs/index', $meta, $this->data);
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
        $post = $is_new ? null : $this->cms_blogs_model->get_by_id($id);

        if (!$is_new && !$post) {
            $this->session->set_flashdata('error', 'Blog post not found.');
            $this->cms_redirect('blogs');
        }

        if ($this->input->post('save_blog_category')) {
            $this->handle_save_category($is_new ? 0 : $id);
            return;
        }

        $repopulate_from_post = false;
        if ($this->input->post('save_cms_blog')) {
            $result = $this->save_from_post($id);
            if (!empty($result['ok'])) {
                $this->session->set_flashdata('message', isset($result['message']) ? $result['message'] : 'Saved.');
                $redirect_id = isset($result['id']) ? (int) $result['id'] : $id;
                $this->cms_redirect('blogs/edit/' . $redirect_id);
            }
            $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Save failed.');
            $repopulate_from_post = true;
        }

        if ($repopulate_from_post) {
            $this->data['post'] = $this->post_from_request($id);
        } elseif ($is_new) {
            $this->data['post'] = array(
                'id'                => 0,
                'title'             => '',
                'subtitle'          => '',
                'slug'              => '',
                'image'             => '',
                'short_description' => '',
                'html_content'      => '',
                'button_text'       => 'Read Full Story',
                'status'            => 'draft',
                'sort_order'        => 0,
                'published_at'      => '',
                'category_id'       => 0,
                'is_active'         => true,
            );
        } else {
            $this->data['post'] = $post;
        }

        $this->data['categories'] = $this->cms_blog_categories_model->list_all();
        $this->data['schema_ready'] = $this->cms_blogs_model->ensure_table();
        $this->data['categories_ready'] = $this->cms_blog_categories_model->ensure_table();
        $this->data['is_new'] = $is_new;
        $this->data['cms_enhancements'] = true;
        $this->data['blog_return_url'] = site_url('cms_admin/blogs/' . ($is_new ? 'add' : 'edit/' . $id));

        $meta = array('page_title' => $is_new ? 'Add Blog Post' : 'Edit Blog Post');
        $this->cms_page_construct('cms_admin/blogs/edit', $meta, $this->data);
    }

    /**
     * @param int $blog_id
     */
    protected function handle_save_category($blog_id = 0)
    {
        $blog_id = (int) $blog_id;
        $returnUrl = trim((string) $this->input->post('return_url'));
        if ($returnUrl === '') {
            $returnUrl = site_url('cms_admin/blogs/' . ($blog_id > 0 ? 'edit/' . $blog_id : 'add'));
        }

        $result = $this->cms_blog_categories_model->save(array(
            'name'       => $this->input->post('category_name'),
            'slug'       => $this->input->post('category_slug'),
            'is_active'  => 1,
        ));
        if (!empty($result['ok']) && !empty($result['id']) && $blog_id > 0) {
            $this->cms_blogs_model->ensure_category_id_column();
            $this->db->where('id', $blog_id)->update(
                'sma_cms_blogs',
                array('category_id' => (int) $result['id'])
            );
        }
        $this->session->set_flashdata(
            !empty($result['ok']) ? 'message' : 'error',
            isset($result['message']) ? $result['message'] : 'Could not save category.'
        );
        redirect($returnUrl);
    }

    public function ajax_save_category()
    {
        if (!$this->input->is_ajax_request() && !$this->input->post('category_name')) {
            show_404();
        }

        $blog_id = (int) $this->input->post('blog_id');
        $result = $this->cms_blog_categories_model->save(array(
            'name'      => $this->input->post('category_name'),
            'slug'      => $this->input->post('category_slug'),
            'is_active' => 1,
        ));

        $category = null;
        if (!empty($result['ok']) && !empty($result['id'])) {
            $row = $this->cms_blog_categories_model->get_by_id((int) $result['id']);
            if ($row) {
                $category = array(
                    'id'   => (int) $row['id'],
                    'name' => (string) $row['name'],
                    'slug' => (string) $row['slug'],
                );
            }
            if ($blog_id > 0) {
                $this->cms_blogs_model->ensure_category_id_column();
                $this->db->where('id', $blog_id)->update(
                    'sma_cms_blogs',
                    array('category_id' => (int) $result['id'])
                );
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'ok'        => !empty($result['ok']),
                'message'   => isset($result['message']) ? (string) $result['message'] : 'Could not save category.',
                'category'  => $category,
                'csrf_hash' => $this->security->get_csrf_hash(),
            )));
    }

    public function delete_category($id = null)
    {
        $id = (int) $id;
        $returnUrl = trim((string) $this->input->post('return_url'));
        if ($returnUrl === '') {
            $returnUrl = site_url('cms_admin/blogs');
        }
        if ($id < 1) {
            $this->session->set_flashdata('error', 'Invalid category.');
            redirect($returnUrl);
        }

        $this->cms_blogs_model->ensure_category_id_column();
        $this->db->where('category_id', $id)->update('sma_cms_blogs', array('category_id' => null));

        $result = $this->cms_blog_categories_model->delete($id);
        $this->session->set_flashdata(
            !empty($result['ok']) ? 'message' : 'error',
            isset($result['message']) ? $result['message'] : 'Delete failed.'
        );
        redirect($returnUrl);
    }

    public function delete($id = null)
    {
        $id = (int) $id;
        if ($id < 1) {
            $this->session->set_flashdata('error', 'Invalid blog post.');
            $this->cms_redirect('blogs');
        }
        $result = $this->cms_blogs_model->delete($id);
        $this->session->set_flashdata(
            !empty($result['ok']) ? 'message' : 'error',
            isset($result['message']) ? $result['message'] : 'Delete failed.'
        );
        $this->cms_redirect('blogs');
    }

    /**
     * @param int $id
     * @return array{ok:bool,id:int,message:string}
     */
    protected function save_from_post($id)
    {
        $userId = (int) $this->session->userdata('user_id');
        $data = $this->post_from_request($id);

        if (!empty($_FILES['image_file']['name'])) {
            $upload = $this->do_upload('image_file', 'cms_blogs', true);
            if (!empty($upload['error']) || (isset($upload['status']) && $upload['status'] === 'fail')) {
                return array('ok' => false, 'id' => $id, 'message' => isset($upload['error']) ? (string) $upload['error'] : 'Image upload failed.');
            }
            if (!empty($upload['upload_data']['file_name'])) {
                $data['image'] = 'cms_blogs/' . $upload['upload_data']['file_name'];
            }
        }

        return $this->cms_blogs_model->save($data, $userId);
    }

    /**
     * @param int $id
     * @return array<string,mixed>
     */
    protected function post_from_request($id)
    {
        $htmlContent = $this->input->post('html_content', false);
        return array(
            'id'                => (int) $id,
            'title'             => $this->input->post('title'),
            'subtitle'          => $this->input->post('subtitle'),
            'slug'              => $this->input->post('slug'),
            'image'             => $this->input->post('image'),
            'short_description' => $this->input->post('short_description'),
            'html_content'      => is_string($htmlContent) ? $htmlContent : '',
            'button_text'       => $this->input->post('button_text'),
            'status'            => $this->input->post('status_published') ? 'published' : 'draft',
            'sort_order'        => $this->input->post('sort_order'),
            'published_at'      => $this->input->post('published_at'),
            'category_id'       => (int) $this->input->post('category_id'),
            'is_active'         => $this->input->post('is_active') ? 1 : 0,
        );
    }
}
