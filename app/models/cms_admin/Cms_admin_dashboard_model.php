<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * Dashboard summary counts for CMS Admin Panel home.
 */
class Cms_admin_dashboard_model extends Cms_admin_base_model
{
    const MODULE_COUNT = 5;

    /**
     * Raw counts from database.
     *
     * @return array
     */
    public function get_stats()
    {
        $products = 'products';
        $categories = 'categories';
        $pages = 'sma_cms_pages';

        $stats = array(
            'modules_count'           => self::MODULE_COUNT,
            'products_in_eshop'       => 0,
            'categories_in_eshop'     => 0,
            'cms_pages'               => 0,
            'cms_pages_published'     => 0,
            'products_with_eshop_price' => 0,
            'storefront_rows'         => 0,
            'entity_mappings'         => 0,
        );

        if ($this->db->table_exists($products) && $this->db->field_exists('in_eshop', $products)) {
            $stats['products_in_eshop'] = $this->count_where($products, array('in_eshop' => 1));
        }

        if ($this->db->table_exists($categories) && $this->db->field_exists('in_eshop', $categories)) {
            $stats['categories_in_eshop'] = $this->count_where($categories, array('in_eshop' => 1));
        }

        if ($this->db->table_exists($pages)) {
            $stats['cms_pages'] = $this->count_where($pages);
            if ($this->db->field_exists('status', $pages)) {
                $stats['cms_pages_published'] = $this->count_where($pages, array('status' => 'published'));
            } else {
                $stats['cms_pages_published'] = $stats['cms_pages'];
            }
        }

        if ($this->db->table_exists($products) && $this->db->field_exists('eshop_price', $products)) {
            $stats['products_with_eshop_price'] = $this->count_products_with_eshop_price($products);
        }

        if ($this->db->table_exists('sma_cms_webshop_header_footer')) {
            $stats['storefront_rows'] = (int) $this->db->count_all('sma_cms_webshop_header_footer');
        }

        $mapping_table = 'sma_cms_entity_tag_mapping';
        if ($this->db->table_exists($mapping_table)) {
            $stats['entity_mappings'] = $this->count_entity_mapping_groups($mapping_table);
        }

        return $stats;
    }

    /**
     * Stat tiles for dashboard view (value, label, icon, url).
     *
     * @return array
     */
    public function get_stat_cards()
    {
        $s = $this->get_stats();

        return array(
            array(
                'value' => number_format($s['products_in_eshop']),
                'label' => 'Products online',
                'hint'  => $s['categories_in_eshop'] . ' categories enabled',
                'icon'  => 'fa-cubes',
                'url'   => 'cms_admin/catalog',
            ),
            array(
                'value' => number_format($s['cms_pages']),
                'label' => 'CMS pages',
                'hint'  => $s['cms_pages_published'] . ' published',
                'icon'  => 'fa-file-text-o',
                'url'   => 'cms_admin/pages',
            ),
            array(
                'value' => number_format($s['products_with_eshop_price']),
                'label' => 'E-shop prices set',
                'hint'  => 'Products with online price',
                'icon'  => 'fa-inr',
                'url'   => 'cms_admin/prices',
            ),
            array(
                'value' => number_format($s['entity_mappings']),
                'label' => 'Entity tag maps',
                'hint'  => $s['storefront_rows'] . ' header/footer rows',
                'icon'  => 'fa-tags',
                'url'   => 'cms_admin/entity_tags',
            ),
        );
    }

    protected function count_where($table, array $where = array())
    {
        if (!$this->db->table_exists($table)) {
            return 0;
        }
        $this->db->from($table);
        foreach ($where as $col => $val) {
            $this->db->where($col, $val);
        }
        return (int) $this->db->count_all_results();
    }

    protected function count_products_with_eshop_price($products_table)
    {
        $this->db->from($products_table);
        $this->db->where('eshop_price >', 0);
        return (int) $this->db->count_all_results();
    }

    protected function count_entity_mapping_groups($mapping_table)
    {
        $q = $this->db
            ->select('entity_master_id, entity_id')
            ->from($mapping_table)
            ->group_by(array('entity_master_id', 'entity_id'))
            ->get();

        return $q->num_rows();
    }
}
