<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

class Catalog extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('catalog');
    }

    public function index($category_id = null)
    {
        $this->data['products'] = null;
        $this->data['subcategories'] = array();
        $this->data['category_id'] = null;

        $this->data['categories'] = $this->cms_catalog_model->get_root_categories();
        if (!$this->data['categories']) {
            $this->data['categories'] = array();
        }

        if ($category_id) {
            $this->data['category_id'] = (int) $category_id;
            $subcategories = $this->cms_catalog_model->get_subcategories($category_id);
            $this->data['subcategories'] = $subcategories ? $subcategories : array();
            $this->data['products'] = $this->cms_catalog_model->get_products_for_branch($category_id);
        }

        $this->data['cat_eshop'] = $this->cms_catalog_model->build_category_eshop_map(
            $this->data['categories'],
            $this->data['subcategories']
        );
        if ($category_id) {
            $this->data['catalog_stats'] = $this->cms_catalog_model->get_catalog_branch_summary_stats((int) $category_id);
        } else {
            $this->data['catalog_stats'] = $this->cms_catalog_model->get_catalog_summary_stats();
        }

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => '#', 'page' => lang('products')),
        );
        $this->data['cms_enhancements'] = true;
        $meta = array('page_title' => 'Manage Products For E-Shop', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/catalog/manage_products', $meta, $this->data);
    }

    public function ajax()
    {
        $action = $this->input->post('action');
        $postData = $this->input->post();

        switch ($action) {
            case 'manage_eshop_category':
                $this->manage_eshop_category($postData);
                break;
            case 'manage_eshop_product':
                $this->manage_eshop_product($postData);
                break;
            default:
                break;
        }
    }

    public function manage_eshop_category($postData)
    {
        $category_id = isset($postData['category_id']) ? $postData['category_id'] : false;
        $parent_id = isset($postData['parent_id']) ? (int) $postData['parent_id'] : 0;
        $eshop_status = isset($postData['eshop_status']) ? (int) $postData['eshop_status'] : 0;
        $data = array('in_eshop' => $eshop_status);
        $where = null;

        if ((bool) $category_id) {
            $where['id'] = $category_id;
        } elseif (!(bool) $category_id && (bool) $parent_id) {
            $where['parent_id'] = $parent_id;
        }

        $response = array(
            'status_code' => 500,
            'status' => 'ERROR',
            'messages' => 'Failed',
        );

        if ($where) {
            if ($this->cms_catalog_model->update_eshop_status('categories', $where, $data)) {
                $response = array(
                    'status_code' => 200,
                    'status' => 'SUCCESS',
                    'messages' => 'Updated',
                );
                if ($eshop_status === 0 && $parent_id === 0 && (bool) $category_id) {
                    $this->cms_catalog_model->cascade_eshop_off_category_branch((int) $category_id);
                }
            }
        }

        echo json_encode($response);
    }

    public function manage_eshop_product($postData)
    {
        $product_id = isset($postData['product_id']) ? $postData['product_id'] : false;
        $variant_id = isset($postData['variant_id']) ? $postData['variant_id'] : 0;
        $eshop_status = isset($postData['eshop_status']) ? $postData['eshop_status'] : 0;
        $data = array('in_eshop' => $eshop_status);
        $where = null;
        $tablename = 'products';

        if ((bool) $product_id) {
            if ((bool) $variant_id) {
                $where['id'] = $variant_id;
                $where['product_id'] = $product_id;
                $tablename = 'product_variants';
            } else {
                $where['id'] = $product_id;
                $tablename = 'products';
            }
        }

        $response = array(
            'status_code' => 500,
            'status' => 'ERROR',
            'messages' => 'Failed',
        );

        if ($where && $this->cms_catalog_model->update_eshop_status($tablename, $where, $data)) {
            $response = array(
                'status_code' => 200,
                'status' => 'SUCCESS',
                'messages' => 'Updated',
            );
        }

        echo json_encode($response);
    }

    /**
     * AJAX: product details HTML for reusable modal (additive endpoint).
     *
     * @param int|null $product_id
     */
    public function getProductDetailsAjax($product_id = null)
    {
        $product_id = (int) $product_id;
        $details = $this->cms_catalog_model->get_product_details($product_id);
        if (!$details) {
            $this->output->set_status_header(404);
            echo '<div class="alert alert-warning">Product not found.</div>';
            return;
        }
        $this->data['details'] = $details;
        $this->data['uploads_base'] = 'assets/mdata/' . $this->Customer_assets . '/uploads/';
        $this->load->view($this->theme . 'cms_admin/catalog/_product_details_modal', $this->data);
    }

    /**
     * AJAX: compact JSON summary for entity tag forms (additive endpoint).
     *
     * @param int|null $product_id
     */
    public function product_summary_ajax($product_id = null)
    {
        $product_id = (int) $product_id;
        $details = $this->cms_catalog_model->get_product_details($product_id);
        if (!$details) {
            $this->output->set_status_header(404)->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false)));
            return;
        }
        $p = $details['product'];
        $cat = $details['category'];
        $sub = $details['subcategory'];
        $image = !empty($details['gallery'][0]) ? $details['gallery'][0] : 'no_image.png';
        $variants = array();
        if (!empty($details['variants']) && is_array($details['variants'])) {
            foreach ($details['variants'] as $v) {
                $variants[] = array(
                    'id'          => isset($v['id']) ? (int) $v['id'] : 0,
                    'name'        => isset($v['name']) ? (string) $v['name'] : '',
                    'eshop_name'  => isset($v['eshop_name']) ? (string) $v['eshop_name'] : '',
                    'mrp'         => isset($v['mrp']) ? (float) $v['mrp'] : 0,
                    'eshop_mrp'   => isset($v['eshop_mrp']) ? (float) $v['eshop_mrp'] : 0,
                    'price'       => isset($v['price']) ? (float) $v['price'] : 0,
                    'eshop_price' => isset($v['eshop_price']) ? (float) $v['eshop_price'] : 0,
                    'unit_qty'    => isset($v['unit_quantity']) ? (float) $v['unit_quantity'] : 0,
                    'in_eshop'    => isset($v['in_eshop']) ? (int) $v['in_eshop'] : 0,
                );
            }
        }
        $image_url = site_url('assets/mdata/' . $this->Customer_assets . '/uploads/' . $image);
        $currency = isset($this->Settings->default_currency) ? (string) $this->Settings->default_currency : 'AED';
        $schema_array = $this->cms_catalog_model->build_product_schema_array($product_id, $image_url, $currency);
        $schema_json = '';
        if (is_array($schema_array)) {
            $schema_json = json_encode(
                $schema_array,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        }

        $payload = array(
            'ok'            => true,
            'id'            => (int) $p->id,
            'name'          => (string) $p->name,
            'eshop_name'    => !empty($p->eshop_name) ? (string) $p->eshop_name : '',
            'code'          => (string) $p->code,
            'image_url'     => $image_url,
            'schema_json'   => $schema_json,
            'mrp'           => (float) $p->mrp,
            'eshop_mrp'     => isset($p->eshop_mrp) ? (float) $p->eshop_mrp : (float) $p->mrp,
            'price'         => isset($p->price) ? (float) $p->price : 0,
            'eshop_price'   => (float) $p->eshop_price,
            'in_eshop'      => isset($p->in_eshop) ? (int) $p->in_eshop : 0,
            'category'      => $cat ? (string) $cat->name : '',
            'subcategory'   => $sub ? (string) $sub->name : '',
            'total_qty'     => (float) $details['total_qty'],
            'variant_count' => count($details['variants']),
            'stock_rows'    => isset($details['stock_rows']) && is_array($details['stock_rows']) ? $details['stock_rows'] : array(),
            'variants'      => $variants,
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    /**
     * AJAX: update editable product details from entity tags panel.
     *
     * @param int|null $product_id
     */
    public function update_product_summary_ajax($product_id = null)
    {
        $product_id = (int) $product_id;
        if ($product_id < 1) {
            $this->output->set_status_header(400)->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'message' => 'Invalid product.')));
            return;
        }

        $eshop_name = trim((string) $this->input->post('eshop_name'));
        $price = (float) $this->input->post('price');
        $mrp = (float) $this->input->post('mrp');
        $eshop_price = (float) $this->input->post('eshop_price');
        $eshop_mrp = (float) $this->input->post('eshop_mrp');

        if ($mrp < 0 || $eshop_price < 0 || $eshop_mrp < 0 || $price < 0) {
            $this->output->set_status_header(422)->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'message' => 'Prices must be zero or positive.')));
            return;
        }

        $saved = $this->cms_catalog_model->update_product_inline_details($product_id, array(
            'eshop_name'  => $eshop_name,
            'price'       => $price,
            'mrp'         => $mrp,
            'eshop_price' => $eshop_price,
            'eshop_mrp'   => $eshop_mrp,
        ));

        if (!$saved) {
            $this->output->set_status_header(500)->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'message' => 'Failed to save product details.')));
            return;
        }

        $details = $this->cms_catalog_model->get_product_details($product_id);
        if (!$details) {
            $this->output->set_status_header(200)->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => true, 'message' => 'Saved successfully.')));
            return;
        }

        $p = $details['product'];
        $payload = array(
            'ok'          => true,
            'message'     => 'Saved successfully.',
            'eshop_name'  => !empty($p->eshop_name) ? (string) $p->eshop_name : '',
            'price'       => isset($p->price) ? (float) $p->price : 0,
            'mrp'         => isset($p->mrp) ? (float) $p->mrp : 0,
            'eshop_price' => isset($p->eshop_price) ? (float) $p->eshop_price : 0,
            'eshop_mrp'   => isset($p->eshop_mrp) ? (float) $p->eshop_mrp : 0,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    /**
     * AJAX: compact JSON summary for category entity tag forms.
     *
     * @param int|null $category_id
     */
    public function category_summary_ajax($category_id = null)
    {
        $category_id = (int) $category_id;
        $details = $this->cms_catalog_model->get_category_details($category_id);
        if (!$details) {
            $this->output->set_status_header(404)->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false)));
            return;
        }

        $c = $details['category'];
        $image = !empty($details['image']) ? (string) $details['image'] : 'no_image.png';
        $payload = array(
            'ok'                => true,
            'id'                => (int) $c->id,
            'name'              => (string) $c->name,
            'code'              => isset($c->code) ? (string) $c->code : '',
            'image_url'         => site_url('assets/mdata/' . $this->Customer_assets . '/uploads/' . $image),
            'parent_name'       => (string) $details['parent_name'],
            'in_eshop'          => isset($c->in_eshop) ? (int) $c->in_eshop : 0,
            'product_count'     => (int) $details['product_count'],
            'subcategory_count' => (int) $details['subcategory_count'],
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    /**
     * AJAX: compact JSON summary for blog entity tag forms (sma_cms_blogs or legacy pages).
     *
     * @param int|null $blog_id
     */
    public function blog_summary_ajax($page_id = null)
    {
        $page_id = (int) $page_id;
        $details = $this->cms_catalog_model->get_blog_page_details($page_id);
        if (!$details) {
            $this->output->set_status_header(404)->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false)));
            return;
        }

        $p = $details['page'];
        $payload = array(
            'ok'         => true,
            'id'         => (int) $p['id'],
            'page_name'  => isset($p['page_name']) ? (string) $p['page_name'] : '',
            'url'        => isset($p['url']) ? (string) $p['url'] : '',
            'status'     => isset($p['status']) ? (string) $p['status'] : '',
            'page_type'  => isset($p['page_type']) ? (string) $p['page_type'] : 'blog',
            'updated_at' => isset($p['updated_at']) ? (string) $p['updated_at'] : '',
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }
}
