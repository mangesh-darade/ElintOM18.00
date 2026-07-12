<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * E-shop catalog visibility (categories / products / variants in_eshop).
 * Webshop storefront reads in_eshop = 1 (see Webshop_model).
 */
class Cms_admin_catalog_model extends Cms_admin_base_model
{
    public function update_eshop_status($tableName, array $where, array $data)
    {
        if (!$this->db->table_exists($tableName)) {
            return false;
        }
        $this->db->where($where)->update($tableName, $data);
        $err = $this->db->error();
        if (!empty($err['code'])) {
            return false;
        }
        return true;
    }

    public function cascade_eshop_off_category_branch($root_category_id)
    {
        $root = (int) $root_category_id;
        if ($root < 1) {
            return true;
        }
        $categories = 'categories';
        $products = 'products';
        $desc = $this->category_descendant_ids($root);

        if (!empty($desc)) {
            $this->db->where_in('id', $desc);
            $this->db->update($categories, array('in_eshop' => 0));
            $err = $this->db->error();
            if (!empty($err['code'])) {
                return false;
            }
        }
        $this->db->where('category_id', $root);
        $this->db->update($products, array('in_eshop' => 0));
        if (!empty($desc)) {
            $this->db->where_in('subcategory_id', $desc);
            $this->db->update($products, array('in_eshop' => 0));
            $err = $this->db->error();
            if (!empty($err['code'])) {
                return false;
            }
            $this->db->where_in('category_id', $desc);
            $this->db->update($products, array('in_eshop' => 0));
            $err = $this->db->error();
            if (!empty($err['code'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * All category ids in branch (descendants only, not including root).
     *
     * @param int $root_id
     * @return int[]
     */
    public function category_descendant_ids($root_id)
    {
        $root = (int) $root_id;
        $out = array();
        if ($root < 1) {
            return $out;
        }
        $categories = 'categories';
        $queue = array($root);
        while (!empty($queue)) {
            $pid = (int) array_shift($queue);
            $rows = $this->db->select('id')->from($categories)->where('parent_id', $pid)->get()->result_array();
            foreach ($rows as $r) {
                $cid = isset($r['id']) ? (int) $r['id'] : 0;
                if ($cid < 1 || isset($out[$cid])) {
                    continue;
                }
                $out[$cid] = $cid;
                $queue[] = $cid;
            }
        }
        return array_values($out);
    }

    /**
     * Products for a category and its subcategories (admin list).
     *
     * @param int $category_id
     * @return array|false
     */
    public function get_products_for_branch($category_id)
    {
        $category_id = (int) $category_id;
        if ($category_id < 1) {
            return false;
        }
        $products = 'products';
        $branch_ids = $this->category_descendant_ids($category_id);
        $branch_ids[] = $category_id;

        $this->db->from($products);
        $this->db->group_start();
        $this->db->where_in('category_id', $branch_ids);
        $this->db->or_where_in('subcategory_id', $branch_ids);
        $this->db->group_end();
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    /**
     * Map category id => in_eshop (0|1) for UI visibility.
     *
     * @param array|false $root_categories
     * @param array|false $subcategories
     * @return array
     */
    public function build_category_eshop_map($root_categories, $subcategories = null)
    {
        $map = array();
        if (is_array($root_categories)) {
            foreach ($root_categories as $c) {
                $id = isset($c['id']) ? (int) $c['id'] : 0;
                if ($id > 0) {
                    $map[$id] = $this->is_eshop_on(isset($c['in_eshop']) ? $c['in_eshop'] : 0);
                }
            }
        }
        if (is_array($subcategories)) {
            foreach ($subcategories as $s) {
                $id = isset($s['id']) ? (int) $s['id'] : 0;
                if ($id > 0) {
                    $map[$id] = $this->is_eshop_on(isset($s['in_eshop']) ? $s['in_eshop'] : 0);
                }
            }
        }
        return $map;
    }

    public function is_eshop_on($value)
    {
        return in_array((int) $value, array(1), true) || $value === '1' || $value === true;
    }

    public function get_root_categories()
    {
        $this->load->model('products_model');
        return $this->products_model->getCategories('', 'parent_id=0');
    }

    public function get_subcategories($category_id)
    {
        $this->load->model('products_model');
        return $this->products_model->getCategories($category_id);
    }

    /**
     * Summary counts for catalog dashboard mini-cards.
     *
     * @return array{total_categories:int,total_products:int,active_products:int,hidden_products:int}
     */
    public function get_catalog_summary_stats()
    {
        $stats = array(
            'total_categories' => 0,
            'total_products'   => 0,
            'active_products'  => 0,
            'hidden_products'  => 0,
        );

        $categories = 'categories';
        $products = 'products';
        if (!$this->db->table_exists($categories) || !$this->db->table_exists($products)) {
            return $stats;
        }

        $stats['total_categories'] = (int) $this->db->where('parent_id', 0)->count_all_results($categories);
        $stats['total_products'] = (int) $this->db->count_all($products);
        $stats['active_products'] = (int) $this->db->where('in_eshop', 1)->count_all_results($products);
        $stats['hidden_products'] = max(0, $stats['total_products'] - $stats['active_products']);

        return $stats;
    }

    /**
     * Summary counts limited to a category branch (selected category + descendants).
     *
     * @param int $category_id
     * @return array{total_categories:int,total_products:int,active_products:int,hidden_products:int}
     */
    public function get_catalog_branch_summary_stats($category_id)
    {
        $stats = array(
            'total_categories' => 0,
            'total_products'   => 0,
            'active_products'  => 0,
            'hidden_products'  => 0,
        );

        $category_id = (int) $category_id;
        if ($category_id < 1) {
            return $stats;
        }

        $categories = 'categories';
        $products = 'products';
        if (!$this->db->table_exists($categories) || !$this->db->table_exists($products)) {
            return $stats;
        }

        $branch_ids = $this->category_descendant_ids($category_id);
        $branch_ids[] = $category_id;
        $branch_ids = array_values(array_unique(array_map('intval', $branch_ids)));
        if (empty($branch_ids)) {
            return $stats;
        }

        $stats['total_categories'] = count($branch_ids);

        // Base product set for this branch (regardless of e-shop flags).
        $this->db->from($products);
        $this->db->group_start();
        $this->db->where_in('category_id', $branch_ids);
        $this->db->or_where_in('subcategory_id', $branch_ids);
        $this->db->group_end();
        $stats['total_products'] = (int) $this->db->count_all_results();

        // Active products for webshop = product in_eshop = 1 AND
        // category in_eshop = 1 AND (subcategory is either null/0 or in_eshop = 1).
        $this->db->from($products . ' p');
        $this->db->join($categories . ' c', 'c.id = p.category_id', 'left');
        $this->db->join($categories . ' s', 's.id = p.subcategory_id', 'left');
        $this->db->where('p.in_eshop', 1);
        $this->db->where_in('p.category_id', $branch_ids);
        $this->db->group_start();
        $this->db->where('c.in_eshop', 1);
        $this->db->group_end();
        $this->db->group_start();
        $this->db->where('(p.subcategory_id IS NULL OR p.subcategory_id = 0)');
        $this->db->or_group_start();
        $this->db->where_in('p.subcategory_id', $branch_ids);
        $this->db->where('s.in_eshop', 1);
        $this->db->group_end();
        $this->db->group_end();
        $stats['active_products'] = (int) $this->db->count_all_results();

        $stats['hidden_products'] = max(0, $stats['total_products'] - $stats['active_products']);

        return $stats;
    }

    /**
     * Product payload for CMS admin details modal (additive helper).
     *
     * @param int $product_id
     * @return array|null
     */
    public function get_product_details($product_id)
    {
        $product_id = (int) $product_id;
        if ($product_id < 1) {
            return null;
        }

        $this->load->model('products_model');
        $product = $this->products_model->getProductByID($product_id);
        if (!$product) {
            return null;
        }

        $category = null;
        $subcategory = null;
        if (!empty($product->category_id)) {
            $category = $this->products_model->getCategoryById($product->category_id);
        }
        if (!empty($product->subcategory_id)) {
            $subcategory = $this->products_model->getCategoryById($product->subcategory_id);
        }

        $variants = array();
        $pv = 'product_variants';
        if ($this->db->table_exists($pv)) {
            $vq = $this->db
                ->select('id, name, price, mrp, eshop_price, eshop_mrp, eshop_name, unit_quantity, in_eshop')
                ->from($pv)
                ->where('product_id', $product_id)
                ->order_by('id', 'ASC')
                ->get();
            if ($vq->num_rows() > 0) {
                $variants = $vq->result_array();
            }
        }

        $stock_rows = array();
        $total_qty = 0;
        $wh = $this->products_model->getAllWarehousesWithPQ($product_id);
        if (!empty($wh)) {
            foreach ($wh as $row) {
                $qty = isset($row->quantity) ? (float) $row->quantity : 0;
                $total_qty += $qty;
                $stock_rows[] = array(
                    'warehouse' => isset($row->name) ? (string) $row->name : '',
                    'quantity'  => $qty,
                );
            }
        }

        $photos = $this->products_model->getProductPhotos($product_id);
        if (!is_array($photos)) {
            $photos = array();
        }
        $gallery = array();
        foreach ($photos as $photo) {
            if (!empty($photo->photo)) {
                $gallery[] = (string) $photo->photo;
            }
        }
        $main_image = !empty($product->image) ? (string) $product->image : 'no_image.png';
        if ($main_image !== 'no_image.png' && !in_array($main_image, $gallery, true)) {
            array_unshift($gallery, $main_image);
        } elseif (empty($gallery)) {
            $gallery[] = $main_image;
        }

        return array(
            'product'     => $product,
            'category'    => $category,
            'subcategory' => $subcategory,
            'variants'    => $variants,
            'stock_rows'  => $stock_rows,
            'total_qty'   => $total_qty,
            'gallery'     => $gallery,
        );
    }

    /**
     * schema.org Product JSON-LD array for entity tag preview / suggestions.
     *
     * @param int         $product_id
     * @param string      $image_url  Absolute image URL
     * @param string      $currency   ISO currency code
     * @return array|null
     */
    public function build_product_schema_array($product_id, $image_url = '', $currency = 'AED')
    {
        $details = $this->get_product_details($product_id);
        if (!$details || empty($details['product'])) {
            return null;
        }

        $p = $details['product'];
        $name = !empty($p->eshop_name) ? trim((string) $p->eshop_name) : trim((string) $p->name);
        if ($name === '') {
            $name = trim((string) $p->name);
        }

        $schema = array(
            '@context' => 'https://schema.org',
            '@type'    => 'Product',
            'name'     => $name,
        );

        $code = isset($p->code) ? trim((string) $p->code) : '';
        if ($code !== '') {
            $schema['sku'] = $code;
        }

        if ($image_url !== '') {
            $schema['image'] = $image_url;
        }

        if (!empty($p->details)) {
            $desc = trim(strip_tags((string) $p->details));
            if ($desc !== '') {
                $schema['description'] = $desc;
            }
        }

        $price = 0.0;
        if (isset($p->eshop_price) && (float) $p->eshop_price > 0) {
            $price = (float) $p->eshop_price;
        } elseif (isset($p->price)) {
            $price = (float) $p->price;
        }

        $schema['offers'] = array(
            '@type'         => 'Offer',
            'price'         => number_format($price, 2, '.', ''),
            'priceCurrency' => strtoupper(trim((string) $currency) !== '' ? trim((string) $currency) : 'AED'),
            'availability'  => ((float) $details['total_qty'] > 0)
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
        );

        if (!empty($details['category']) && !empty($details['category']->name)) {
            $schema['category'] = (string) $details['category']->name;
        }

        return $schema;
    }

    /**
     * Category row + counts for entity tag preview panel.
     *
     * @param int $category_id
     * @return array|null
     */
    public function get_category_details($category_id)
    {
        $category_id = (int) $category_id;
        if ($category_id < 1) {
            return null;
        }

        $this->load->model('products_model');
        $category = $this->products_model->getCategoryById($category_id);
        if (!$category) {
            return null;
        }

        $categories = 'categories';
        $products = 'products';
        $parent_name = '';
        $parent_id = isset($category->parent_id) ? (int) $category->parent_id : 0;
        if ($parent_id > 0) {
            $parent = $this->products_model->getCategoryById($parent_id);
            if ($parent && !empty($parent->name)) {
                $parent_name = (string) $parent->name;
            }
        }

        $product_count = 0;
        $subcategory_count = 0;
        if ($this->db->table_exists($products)) {
            $product_count = (int) $this->db->where('category_id', $category_id)->count_all_results($products);
        }
        if ($this->db->table_exists($categories)) {
            $subcategory_count = (int) $this->db->where('parent_id', $category_id)->count_all_results($categories);
        }

        $image = !empty($category->image) ? (string) $category->image : 'no_image.png';

        return array(
            'category'          => $category,
            'parent_name'       => $parent_name,
            'product_count'     => $product_count,
            'subcategory_count' => $subcategory_count,
            'image'             => $image,
        );
    }

    /**
     * Blog row for entity tag preview panel (sma_cms_blogs / sma_cms_pages).
     *
     * @param int $blog_id
     * @return array|null
     */
    public function get_blog_page_details($blog_id)
    {
        $blog_id = (int) $blog_id;
        if ($blog_id < 1) {
            return null;
        }

        if ($this->db->table_exists('sma_cms_blogs')) {
            $blog = $this->db->from('sma_cms_blogs')->where('id', $blog_id)->get()->row_array();
            if (is_array($blog) && !empty($blog['id'])) {
                $slug = isset($blog['slug']) ? trim((string) $blog['slug'], '/') : '';
                return array(
                    'page' => array(
                        'id'         => (int) $blog['id'],
                        'page_name'  => isset($blog['title']) ? (string) $blog['title'] : '',
                        'url'        => $slug !== '' ? '/' . $slug : '',
                        'status'     => isset($blog['status']) ? (string) $blog['status'] : '',
                        'page_type'  => 'blog',
                        'updated_at' => isset($blog['updated_at']) ? (string) $blog['updated_at'] : '',
                    ),
                );
            }
        }

        if (!isset($this->cms_pages_model)) {
            $this->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_model');
        }

        $page = $this->cms_pages_model->getPageById($blog_id);
        if (!$page) {
            return null;
        }

        if ($this->cms_pages_model->hasPageColumn('page_type')) {
            $page_type = isset($page['page_type']) ? strtolower(trim((string) $page['page_type'])) : '';
            if ($page_type !== '' && $page_type !== 'blog') {
                return null;
            }
        }

        return array('page' => $page);
    }

    /**
     * Update selected editable product fields from CMS quick editor.
     *
     * @param int $product_id
     * @param array $payload
     * @return bool
     */
    public function update_product_inline_details($product_id, array $payload)
    {
        $product_id = (int) $product_id;
        if ($product_id < 1) {
            return false;
        }

        $products = 'products';
        if (!$this->db->table_exists($products)) {
            return false;
        }

        $update = array();
        if (array_key_exists('eshop_name', $payload) && $this->db->field_exists('eshop_name', $products)) {
            $update['eshop_name'] = trim((string) $payload['eshop_name']);
        }
        if (array_key_exists('price', $payload) && $this->db->field_exists('price', $products)) {
            $update['price'] = (float) $payload['price'];
        }
        if (array_key_exists('mrp', $payload) && $this->db->field_exists('mrp', $products)) {
            $update['mrp'] = (float) $payload['mrp'];
        }
        if (array_key_exists('eshop_price', $payload) && $this->db->field_exists('eshop_price', $products)) {
            $update['eshop_price'] = (float) $payload['eshop_price'];
        }
        if (array_key_exists('eshop_mrp', $payload) && $this->db->field_exists('eshop_mrp', $products)) {
            $update['eshop_mrp'] = (float) $payload['eshop_mrp'];
        }

        if (empty($update)) {
            return false;
        }

        $this->db->where('id', $product_id)->update($products, $update);
        $err = $this->db->error();
        return empty($err['code']);
    }
}
