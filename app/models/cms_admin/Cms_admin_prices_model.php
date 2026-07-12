<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * E-commerce pricing (eshop_name, eshop_price, eshop_mrp) for admin panel.
 */
class Cms_admin_prices_model extends Cms_admin_base_model
{
    /**
     * Nested category tree keyed by parent_id (same shape as legacy manage_price).
     *
     * @return array
     */
    public function get_category_tree()
    {
        $categories = array();
        $this->load->model('products_model');
        $all = $this->products_model->getCategories();
        if ((bool) $all) {
            foreach ($all as $category) {
                $categories[$category['parent_id']][] = $category;
            }
        }
        return $categories;
    }

    /**
     * Products (+ variants) for price editor by category filter.
     *
     * @param array|null $filter
     * @return array|false
     */
    public function get_filter_products($filter = null)
    {
        if ($filter === null) {
            return false;
        }
        $this->load->model('products_model');
        return $this->products_model->getFilterProducts($filter);
    }

    /**
     * @param array $products_data rows keyed for update_batch on products.id
     * @param array $variants_data rows keyed for update_batch on product_variants.id
     * @return bool
     */
    public function save_eshop_prices(array $products_data, array $variants_data)
    {
        $ok = true;
        if (!empty($products_data)) {
            $ok = (bool) $this->db->update_batch('products', $products_data, 'id') && $ok;
        }
        if (!empty($variants_data)) {
            $ok = (bool) $this->db->update_batch('product_variants', $variants_data, 'id') && $ok;
        }
        return $ok;
    }

    /**
     * Active categories only (additive; original get_category_tree() unchanged).
     *
     * @return array
     */
    public function get_active_category_tree()
    {
        $tree = $this->get_category_tree();
        if (empty($tree)) {
            return $tree;
        }
        $filtered = array();
        foreach ($tree as $parent_id => $items) {
            if (!is_array($items)) {
                continue;
            }
            foreach ($items as $category) {
                if ($this->is_active_category_row($category)) {
                    $filtered[$parent_id][] = $category;
                }
            }
        }
        return $filtered;
    }

    /**
     * Active e-shop products for price grid (additive wrapper).
     *
     * @param array|null $filter
     * @return array|false
     */
    public function get_active_filter_products($filter = null)
    {
        $products = $this->get_filter_products($filter);
        if (!$products || !is_array($products)) {
            return false;
        }
        $out = array();
        foreach ($products as $id => $row) {
            if ($this->is_active_product_row($row)) {
                $out[$id] = $row;
            }
        }
        return !empty($out) ? $out : false;
    }

    /**
     * Category is listed when enabled for e-shop (in_eshop) and not disabled (is_active).
     */
    protected function is_active_category_row($category)
    {
        if (!is_array($category)) {
            return false;
        }
        if (isset($category['in_eshop']) && (int) $category['in_eshop'] !== 1) {
            return false;
        }
        if (isset($category['is_active']) && (int) $category['is_active'] !== 1) {
            return false;
        }
        return true;
    }

    protected function is_active_product_row($row)
    {
        if (isset($row['in_eshop']) && (int) $row['in_eshop'] !== 1) {
            return false;
        }
        if (isset($row['is_active']) && (int) $row['is_active'] !== 1) {
            return false;
        }
        return true;
    }

}
