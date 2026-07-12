<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * CMS pages, sections, and page-level SEO tags (admin panel).
 */
class Cms_admin_pages_model extends Cms_admin_base_model
{
    public function getAdminPages()
    {
        $this->ensureNavOrderColumn();
        $this->ensureNavVisibilityColumns();
        $this->ensureParentPageColumn();
        $this->ensureSubmenuOrderColumn();
        $this->backfillNavOrdersIfNeeded();

        $select = array('id', 'page_name', 'url', 'status', 'updated_at');
        if ($this->hasPageColumn('nav_order')) {
            $select[] = 'nav_order';
        }
        if ($this->hasPageColumn('show_in_header')) {
            $select[] = 'show_in_header';
        }
        if ($this->hasPageColumn('show_in_footer')) {
            $select[] = 'show_in_footer';
        }
        if ($this->hasPageColumn('banner_image')) {
            $select[] = 'banner_image';
        }
        if ($this->hasPageColumn('parent_page_id')) {
            $select[] = 'parent_page_id';
        }
        if ($this->hasPageColumn('submenu_order')) {
            $select[] = 'submenu_order';
        }
        $this->db->select(implode(', ', $select));
        $this->db->from('sma_cms_pages');
        if ($this->hasPageColumn('nav_order')) {
            $this->db->order_by('nav_order', 'ASC');
        }
        $this->db->order_by('id', 'ASC');
        $q = $this->db->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    public function getPageById($id)
    {
        $q = $this->db
            ->where('id', (int) $id)
            ->get('sma_cms_pages');

        return $q->num_rows() > 0 ? $q->row_array() : false;
    }

    public function updatePageById($id, $data)
    {
        $id = (int) $id;
        if ($id <= 0 || empty($data)) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update('sma_cms_pages', $data);

        // Treat "no row changed" as success (same data submitted again).
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        $check = $this->getPageById($id);
        if (!$check) {
            return false;
        }

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $check)) {
                continue;
            }
            $existing = $check[$key];
            if ((string) $existing !== (string) $value && $existing != $value) {
                return false;
            }
        }

        return true;
    }

    public function addPage($data)
    {
        if (!(bool) $this->db->insert('sma_cms_pages', $data)) {
            return false;
        }
        return (int) $this->db->insert_id();
    }

    public function ensureParentPageColumn()
    {
        if ($this->hasPageColumn('parent_page_id')) {
            return true;
        }
        $table = 'sma_cms_pages';
        $sql = 'ALTER TABLE `' . $table . '` ADD COLUMN `parent_page_id` INT NULL DEFAULT NULL';
        return (bool) $this->db->query($sql);
    }

    public function ensureSubmenuOrderColumn()
    {
        if ($this->hasPageColumn('submenu_order')) {
            return true;
        }
        $table = 'sma_cms_pages';
        $sql = 'ALTER TABLE `' . $table . '` ADD COLUMN `submenu_order` INT NOT NULL DEFAULT 0';
        return (bool) $this->db->query($sql);
    }

    /**
     * Published CMS pages marked for the header menu (Show In Header + nav order).
     *
     * @return array<int,array{label:string,url:string,page_id:int,parent_page_id:int}>
     */
    public function getHeaderMenuNavItems()
    {
        $this->ensureNavVisibilityColumns();
        $this->ensureNavOrderColumn();
        $this->ensureParentPageColumn();
        $this->backfillNavOrdersIfNeeded();

        $select = array('id', 'page_name', 'url', 'status');
        if ($this->hasPageColumn('nav_order')) {
            $select[] = 'nav_order';
        }
        if ($this->hasPageColumn('parent_page_id')) {
            $select[] = 'parent_page_id';
        }

        $this->db->select(implode(', ', $select));
        $this->db->from('sma_cms_pages');
        $this->db->where('status', 'published');
        if ($this->hasPageColumn('show_in_header')) {
            $this->db->where('show_in_header', 1);
        }
        if ($this->hasPageColumn('nav_order')) {
            $this->db->order_by('nav_order', 'ASC');
        }
        $this->db->order_by('page_name', 'ASC');
        $this->db->order_by('id', 'ASC');
        $q = $this->db->get();

        $this->load->helper('cms_layout');
        $out = array();
        if (!$q || $q->num_rows() === 0) {
            return $out;
        }
        foreach ($q->result_array() as $row) {
            $label = isset($row['page_name']) ? trim((string) $row['page_name']) : '';
            if ($label === '') {
                continue;
            }
            $page_url = isset($row['url']) ? (string) $row['url'] : '';
            $out[] = array(
                'page_id'         => (int) $row['id'],
                'parent_page_id'  => isset($row['parent_page_id']) ? (int) $row['parent_page_id'] : 0,
                'label'           => $label,
                'url'             => cms_page_public_url($page_url),
            );
        }
        return $out;
    }

    /**
     * All pages for admin UI (which appear / could appear in header menu).
     *
     * @return array<int,array>
     */
    public function getPagesHeaderMenuAdminList()
    {
        $this->ensureNavVisibilityColumns();
        $this->ensureNavOrderColumn();
        $this->ensureParentPageColumn();

        $select = array('id', 'page_name', 'url', 'status');
        if ($this->hasPageColumn('show_in_header')) {
            $select[] = 'show_in_header';
        }
        if ($this->hasPageColumn('nav_order')) {
            $select[] = 'nav_order';
        }
        if ($this->hasPageColumn('parent_page_id')) {
            $select[] = 'parent_page_id';
        }

        $this->db->select(implode(', ', $select));
        $this->db->from('sma_cms_pages');
        if ($this->hasPageColumn('nav_order')) {
            $this->db->order_by('nav_order', 'ASC');
        }
        $this->db->order_by('page_name', 'ASC');
        $q = $this->db->get();

        return ($q && $q->num_rows() > 0) ? $q->result_array() : array();
    }

    public function getParentPageOptions($exclude_page_id = 0)
    {
        $this->ensureParentPageColumn();
        $exclude_page_id = (int) $exclude_page_id;

        $select = array('id', 'page_name', 'url');
        if ($this->hasPageColumn('nav_order')) {
            $select[] = 'nav_order';
        }
        $this->db->select(implode(', ', $select));
        $this->db->from('sma_cms_pages');
        if ($exclude_page_id > 0) {
            $this->db->where('id !=', $exclude_page_id);
        }
        if ($this->hasPageColumn('nav_order')) {
            $this->db->order_by('nav_order', 'ASC');
        }
        $this->db->order_by('page_name', 'ASC');
        $this->db->order_by('id', 'ASC');
        $q = $this->db->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    public function deletePageById($id)
    {
        return (bool) $this->db
            ->where('id', (int) $id)
            ->delete('sma_cms_pages');
    }

    public function deletePageSectionsByPageId($page_id)
    {
        return (bool) $this->db
            ->where('page_id', (int) $page_id)
            ->delete('sma_cms_page_section_mapping');
    }

    public function hasPageColumn($column_name)
    {
        return (bool) $this->db->field_exists($column_name, 'sma_cms_pages');
    }

    public function hasPageSectionColumn($column_name)
    {
        return (bool) $this->db->field_exists($column_name, 'sma_cms_page_section_mapping');
    }

    /**
     * Column that stores section JSON (section_contain or legacy config_json).
     *
     * @return string
     */
    public function pageSectionConfigColumn()
    {
        return $this->hasPageSectionColumn('section_contain') ? 'section_contain' : 'config_json';
    }

    /**
     * Write section JSON to every mapping column the table supports (avoids stale config_json).
     *
     * @param array $data
     * @return array
     */
    public function packPageSectionConfigPayload(array $data)
    {
        if (!isset($data['section_contain'])) {
            return $data;
        }
        $json = $data['section_contain'];
        unset($data['section_contain']);
        foreach (array('section_contain', 'config_json') as $col) {
            if ($this->hasPageSectionColumn($col)) {
                $data[$col] = $json;
            }
        }
        return $data;
    }

    public function ensurePageMediaColumns()
    {
        $table = 'sma_cms_pages';
        $alter_parts = array();

        if (!$this->hasPageColumn('banner_image')) {
            $alter_parts[] = 'ADD COLUMN `banner_image` VARCHAR(255) NULL';
        }
        if (!$this->hasPageColumn('logo_image')) {
            $alter_parts[] = 'ADD COLUMN `logo_image` VARCHAR(255) NULL';
        }

        if (empty($alter_parts)) {
            return true;
        }

        $sql = 'ALTER TABLE `' . $table . '` ' . implode(', ', $alter_parts);
        return (bool) $this->db->query($sql);
    }

    public function ensureNavOrderColumn()
    {
        if ($this->hasPageColumn('nav_order')) {
            return true;
        }
        $table = 'sma_cms_pages';
        $sql = 'ALTER TABLE `' . $table . '` ADD COLUMN `nav_order` INT NOT NULL DEFAULT 0';
        return (bool) $this->db->query($sql);
    }

    /**
     * Webshop header/footer nav visibility flags on pages list.
     */
    public function ensureNavVisibilityColumns()
    {
        $table = 'sma_cms_pages';
        $alter_parts = array();

        if (!$this->hasPageColumn('show_in_header')) {
            $alter_parts[] = 'ADD COLUMN `show_in_header` TINYINT(1) NOT NULL DEFAULT 0';
        }
        if (!$this->hasPageColumn('show_in_footer')) {
            $alter_parts[] = 'ADD COLUMN `show_in_footer` TINYINT(1) NOT NULL DEFAULT 0';
        }

        if (empty($alter_parts)) {
            return true;
        }

        $sql = 'ALTER TABLE `' . $table . '` ' . implode(', ', $alter_parts);
        return (bool) $this->db->query($sql);
    }

    /**
     * @param int    $page_id
     * @param string $field show_in_header|show_in_footer
     * @param int    $is_enabled 0|1
     */
    public function setPageNavVisibility($page_id, $field, $is_enabled)
    {
        $page_id = (int) $page_id;
        $allowed = array('show_in_header', 'show_in_footer');
        if ($page_id <= 0 || !in_array($field, $allowed, true) || !$this->hasPageColumn($field)) {
            return false;
        }

        if (!$this->getPageById($page_id)) {
            return false;
        }

        return (bool) $this->db
            ->where('id', $page_id)
            ->update('sma_cms_pages', array($field => $is_enabled ? 1 : 0));
    }

    public function backfillNavOrdersIfNeeded()
    {
        if (!$this->hasPageColumn('nav_order')) {
            return false;
        }
        $table = 'sma_cms_pages';
        $q = $this->db->select('COUNT(*) AS c', false)
            ->where('nav_order >', 0)
            ->get($table);
        if ($q->num_rows() > 0 && (int) $q->row()->c > 0) {
            return true;
        }
        $pages = $this->db->select('id')->order_by('id', 'ASC')->get($table)->result_array();
        $order = 1;
        foreach ($pages as $page) {
            $this->db->where('id', (int) $page['id'])->update($table, array('nav_order' => $order));
            $order++;
        }
        return true;
    }

    public function getNextNavOrder()
    {
        if (!$this->hasPageColumn('nav_order')) {
            return 1;
        }
        $table = 'sma_cms_pages';
        $row = $this->db->select_max('nav_order')->get($table)->row_array();
        $max = isset($row['nav_order']) ? (int) $row['nav_order'] : 0;
        return $max > 0 ? $max + 1 : 1;
    }

    public function updatePageNavOrders(array $page_orders)
    {
        if (empty($page_orders) || !$this->hasPageColumn('nav_order')) {
            return false;
        }
        $table = 'sma_cms_pages';
        $this->db->trans_start();
        foreach ($page_orders as $page_id => $nav_order) {
            $page_id = (int) $page_id;
            $nav_order = (int) $nav_order;
            if ($page_id <= 0 || $nav_order <= 0) {
                continue;
            }
            $this->db->where('id', $page_id)->update($table, array('nav_order' => $nav_order));
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function getSectionMasters()
    {
        $q = $this->db
            ->select('id, section_name, section_type, is_dynamic, util_function')
            ->order_by('section_name', 'ASC')
            ->get('sma_cms_sections_master');

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * @param int $section_id sma_sections_master.id
     * @return array|null
     */
    public function getSectionMasterById($section_id)
    {
        $section_id = (int) $section_id;
        if ($section_id <= 0) {
            return null;
        }
        $q = $this->db
            ->where('id', $section_id)
            ->limit(1)
            ->get('sma_cms_sections_master');

        return $q->num_rows() > 0 ? $q->row_array() : null;
    }

    public function ensureHeaderFooterSectionMasters()
    {
        $table = 'sma_cms_sections_master';
        $defaults = array(
            array(
                'section_name'  => 'Header',
                'section_type'  => 'header',
                'ui_component'  => 'HeaderComponent',
                'util_function' => 'getHeaderData',
                'config_schema' => null,
                'is_dynamic'    => 0,
            ),
            array(
                'section_name'  => 'Footer',
                'section_type'  => 'footer',
                'ui_component'  => 'FooterComponent',
                'util_function' => 'getFooterData',
                'config_schema' => null,
                'is_dynamic'    => 0,
            ),
            array(
                'section_name'  => 'Contact Us Form',
                'section_type'  => 'contact_us_form',
                'ui_component'  => 'ContactUsFormComponent',
                'util_function' => 'getContactUsFormData',
                'config_schema' => null,
                'is_dynamic'    => 0,
            ),
        );
        foreach ($defaults as $row) {
            $exists = (int) $this->db
                ->where('section_type', $row['section_type'])
                ->count_all_results($table);
            if ($exists > 0) {
                continue;
            }
            $this->db->insert($table, $row);
        }
    }

    /**
     * Seed CMS dynamic section types (blog, testimonials) when missing.
     * Idempotent — safe on every page edit load.
     *
     * @return void
     */
    public function ensureDynamicSectionMasters()
    {
        $table = 'sma_cms_sections_master';
        $defaults = array(
            array(
                'section_name'  => 'Blog Grid',
                'section_type'  => 'blog_grid',
                'ui_component'  => 'BlogGridComponent',
                'util_function' => 'getBlogGridData',
                'config_schema' => '{"title":"","limit":12,"columns_desktop":3,"posts_per_page":12}',
                'is_dynamic'    => 1,
            ),
            array(
                'section_name'  => 'Testimonials Grid',
                'section_type'  => 'testimonials_grid',
                'ui_component'  => 'TestimonialsGridComponent',
                'util_function' => 'getTestimonialsGridData',
                'config_schema' => '{"title":"What Our Clients Say","limit":6,"columns_desktop":3}',
                'is_dynamic'    => 1,
            ),
        );
        foreach ($defaults as $row) {
            $exists = (int) $this->db
                ->where('section_type', $row['section_type'])
                ->count_all_results($table);
            if ($exists > 0) {
                continue;
            }
            $this->db->insert($table, $row);
        }
    }

    /**
     * Page FAQ accordion section (content from sma_cms_pages_faqs).
     *
     * @return void
     */
    public function ensureFaqSectionMaster()
    {
        $table = 'sma_cms_sections_master';
        $row = array(
            'section_name'  => 'FAQ',
            'section_type'  => 'page_faq',
            'ui_component'  => 'PageFaqComponent',
            'util_function' => 'getPageFaqData',
            'config_schema' => '{"title":"Frequently Asked Questions"}',
            'is_dynamic'    => 1,
        );
        $exists = (int) $this->db
            ->where('section_type', $row['section_type'])
            ->count_all_results($table);
        if ($exists > 0) {
            return;
        }
        $this->db->insert($table, $row);
    }

    /**
     * Header, footer, contact form, FAQ, and CMS dynamic sections for page builder dropdown.
     *
     * @return void
     */
    public function ensureAllSectionMasters()
    {
        $this->ensureHeaderFooterSectionMasters();
        $this->ensureDynamicSectionMasters();
        $this->ensureFaqSectionMaster();
        $this->ensureRecommendationSectionMasters();
    }

    /**
     * Recent viewed, related products, and weekly top categories for storefront CMS.
     *
     * @return void
     */
    public function ensureRecommendationSectionMasters()
    {
        $table = 'sma_cms_sections_master';
        $defaults = array(
            array(
                'section_name'  => 'Recent View Products',
                'section_type'  => 'recent_viewed_products',
                'ui_component'  => 'RecentViewedProductsComponent',
                'util_function' => 'getRecentViewedProductsData',
                'config_schema' => '{"title":"Recently Viewed","limit":15}',
                'is_dynamic'    => 1,
            ),
            array(
                'section_name'  => 'Related Products',
                'section_type'  => 'related_products',
                'ui_component'  => 'RelatedProductsComponent',
                'util_function' => 'getRelatedProductsData',
                'config_schema' => '{"title":"Related Products","limit":8}',
                'is_dynamic'    => 1,
            ),
            array(
                'section_name'  => 'Top Categories This Week',
                'section_type'  => 'top_categories_this_week',
                'ui_component'  => 'TopCategoriesThisWeekComponent',
                'util_function' => 'getTopCategoriesThisWeekData',
                'config_schema' => '{"title":"Top Categories This Week","limit":8,"days":7}',
                'is_dynamic'    => 1,
            ),
        );
        foreach ($defaults as $row) {
            $exists = (int) $this->db
                ->where('section_type', $row['section_type'])
                ->count_all_results($table);
            if ($exists > 0) {
                continue;
            }
            $this->db->insert($table, $row);
        }
    }

    public function getPageSectionById($mapping_id, $page_id)
    {
        $q = $this->db
            ->where('id', (int) $mapping_id)
            ->where('page_id', (int) $page_id)
            ->get('sma_cms_page_section_mapping');

        return $q->num_rows() > 0 ? $q->row_array() : false;
    }

    public function ensureSectionEnabledColumn()
    {
        $table = 'sma_cms_page_section_mapping';
        if ($this->db->field_exists('is_enabled', $table)) {
            return true;
        }
        $sql = 'ALTER TABLE `' . $table . '` ADD COLUMN `is_enabled` TINYINT(1) NOT NULL DEFAULT 1';
        return (bool) $this->db->query($sql);
    }

    public function setPageSectionEnabled($mapping_id, $page_id, $is_enabled)
    {
        $this->ensureSectionEnabledColumn();

        $table = 'sma_cms_page_section_mapping';
        $mapping_id = (int) $mapping_id;
        $page_id = (int) $page_id;
        $flag = $is_enabled ? 1 : 0;

        if (!$this->getPageSectionById($mapping_id, $page_id)) {
            return false;
        }

        return (bool) $this->db
            ->where('id', $mapping_id)
            ->where('page_id', $page_id)
            ->update($table, array('is_enabled' => $flag));
    }

    public function getAdminPageSections($page_id)
    {
        $section_column = $this->hasPageSectionColumn('section_contain') ? 'section_contain' : 'config_json';
        $q = $this->db
            ->select('psm.id, psm.sort_order, psm.is_enabled, psm.' . $section_column . ' AS section_contain, sm.section_name, sm.section_type, sm.is_dynamic, sm.util_function')
            ->from('sma_cms_page_section_mapping' . ' psm')
            ->join('sma_cms_sections_master' . ' sm', 'sm.id = psm.section_id', 'left')
            ->where('psm.page_id', (int) $page_id)
            ->order_by('psm.sort_order', 'ASC')
            ->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * @param string $section_type header|footer
     * @return int
     */
    public function getSectionMasterIdByType($section_type)
    {
        $section_type = strtolower(trim((string) $section_type));
        if ($section_type === '') {
            return 0;
        }
        $this->ensureAllSectionMasters();
        $q = $this->db
            ->select('id')
            ->where('section_type', $section_type)
            ->limit(1)
            ->get('sma_cms_sections_master');
        return ($q && $q->num_rows() > 0) ? (int) $q->row()->id : 0;
    }

    /**
     * First header/footer mapping row for a CMS page.
     *
     * @param int    $page_id
     * @param string $section_type header|footer
     * @return array|null
     */
    public function getPageSectionMappingBySectionType($page_id, $section_type)
    {
        $rows = $this->getPageSectionMappingsBySectionType($page_id, $section_type);
        return !empty($rows) ? $rows[0] : null;
    }

    /**
     * All header/footer mapping rows for a page (lowest sort_order first).
     *
     * @param int    $page_id
     * @param string $section_type header|footer
     * @return array<int,array>
     */
    public function getPageSectionMappingsBySectionType($page_id, $section_type)
    {
        $page_id = (int) $page_id;
        $section_type = strtolower(trim((string) $section_type));
        if ($page_id <= 0 || !in_array($section_type, array('header', 'footer'), true)) {
            return array();
        }
        $q = $this->db
            ->select('psm.*, sm.section_type')
            ->from('sma_cms_page_section_mapping' . ' psm')
            ->join('sma_cms_sections_master' . ' sm', 'sm.id = psm.section_id', 'inner')
            ->where('psm.page_id', $page_id)
            ->where('sm.section_type', $section_type)
            ->order_by('psm.sort_order', 'ASC')
            ->order_by('psm.id', 'ASC')
            ->get();
        return ($q && $q->num_rows() > 0) ? $q->result_array() : array();
    }

    /**
     * Assigned layout slug from page header/footer section JSON (highest sort_order wins).
     *
     * @param int    $page_id
     * @param string $section_type header|footer
     * @return string
     */
    public function getPageStorefrontProfileSlug($page_id, $section_type)
    {
        $this->load->helper('cms_layout');
        $page_id = (int) $page_id;
        $section_type = strtolower(trim((string) $section_type));
        if ($page_id <= 0 || !in_array($section_type, array('header', 'footer'), true)) {
            return '';
        }

        $sections = $this->getPageSectionMappingsBySectionType($page_id, $section_type);
        if (empty($sections)) {
            return '';
        }

        return cms_layout_resolve_page_chrome_profile_slug($sections, $section_type);
    }

    /**
     * CMS pages that already use this storefront profile on a header/footer section.
     *
     * @param string $section_type header|footer
     * @param string $profile_slug
     * @return array<int,array{id:int,page_name:string,url:string,mapping_id:int}>
     */
    public function getPagesUsingStorefrontProfile($section_type, $profile_slug)
    {
        $this->load->helper('cms_layout');
        $section_type = strtolower(trim((string) $section_type));
        $profile_slug = cms_storefront_normalize_profile_slug($profile_slug);
        if ($profile_slug === '' || !in_array($section_type, array('header', 'footer'), true)) {
            return array();
        }

        $mode_key = ($section_type === 'footer') ? 'footer_mode' : 'header_mode';
        $section_column = $this->hasPageSectionColumn('section_contain') ? 'section_contain' : 'config_json';
        $q = $this->db
            ->select('psm.id AS mapping_id, psm.' . $section_column . ' AS section_contain, p.id, p.page_name, p.url, sm.section_type')
            ->from('sma_cms_page_section_mapping' . ' psm')
            ->join('sma_cms_sections_master' . ' sm', 'sm.id = psm.section_id', 'inner')
            ->join('sma_cms_pages' . ' p', 'p.id = psm.page_id', 'inner')
            ->where('sm.section_type', $section_type)
            ->get();

        $out = array();
        if (!$q || $q->num_rows() === 0) {
            return $out;
        }

        foreach ($q->result_array() as $row) {
            $raw = isset($row['section_contain']) ? (string) $row['section_contain'] : '';
            if ($raw === '') {
                continue;
            }
            $config = json_decode($raw, true);
            if (!is_array($config)) {
                continue;
            }
            $mode = isset($config[$mode_key]) ? (string) $config[$mode_key] : '';
            $prof = isset($config['storefront_profile']) ? cms_storefront_normalize_profile_slug($config['storefront_profile']) : '';
            if ($mode !== 'storefront_profile' || $prof !== $profile_slug) {
                continue;
            }
            $out[] = array(
                'id'          => (int) $row['id'],
                'page_name'   => isset($row['page_name']) ? (string) $row['page_name'] : '',
                'url'         => isset($row['url']) ? (string) $row['url'] : '',
                'mapping_id'  => (int) $row['mapping_id'],
            );
        }
        return $out;
    }

    /**
     * Attach or update header/footer section on a page to use a storefront profile.
     *
     * @param int    $page_id
     * @param string $section_type header|footer
     * @param string $profile_slug
     * @return array{ok:bool,message:string,created:bool}
     */
    public function assignStorefrontProfileSection($page_id, $section_type, $profile_slug)
    {
        $this->load->helper('cms_layout');
        $page_id = (int) $page_id;
        $section_type = strtolower(trim((string) $section_type));
        $profile_slug = cms_storefront_normalize_profile_slug($profile_slug);

        if ($page_id <= 0) {
            return array('ok' => false, 'message' => 'Please choose a CMS page.', 'created' => false);
        }
        if ($profile_slug === '') {
            return array('ok' => false, 'message' => 'Invalid design profile.', 'created' => false);
        }
        if (!in_array($section_type, array('header', 'footer'), true)) {
            return array('ok' => false, 'message' => 'Invalid section type.', 'created' => false);
        }
        if (!$this->getPageById($page_id)) {
            return array('ok' => false, 'message' => 'CMS page not found.', 'created' => false);
        }

        $this->load->helper('cms_layout');
        $profile_ok = cms_storefront_profile_exists($section_type, $profile_slug)
            || cms_layout_builder_profile_has_config($profile_slug);
        if (!$profile_ok) {
            return array(
                'ok'      => false,
                'message' => ucfirst($section_type) . ' design "' . $profile_slug . '" not found. Create it in Storefront layout first.',
                'created' => false,
            );
        }

        $section_id = $this->getSectionMasterIdByType($section_type);
        if ($section_id <= 0) {
            return array('ok' => false, 'message' => 'Header/Footer section types are not set up in the database.', 'created' => false);
        }

        $yes = 'yes';
        $no = 'no';
        $config = array(
            'show_header' => $section_type === 'header' ? $yes : $no,
            'show_footer' => $section_type === 'footer' ? $yes : $no,
            'show_banner' => $no,
            'show_logo'   => $no,
            'content'     => '',
            'storefront_profile' => $profile_slug,
        );
        if ($section_type === 'header') {
            $config['header_mode'] = 'storefront_profile';
        } else {
            $config['footer_mode'] = 'storefront_profile';
        }

        $section_contain = json_encode($config);
        $section_column = $this->hasPageSectionColumn('section_contain') ? 'section_contain' : 'config_json';
        $existing_rows = $this->getPageSectionMappingsBySectionType($page_id, $section_type);
        $visibility = $this->buildSectionVisibilityRow($section_type);

        if (!empty($existing_rows)) {
            $ok = true;
            foreach ($existing_rows as $existing) {
                if (empty($existing['id'])) {
                    continue;
                }
                $update = array(
                    $section_column => $section_contain,
                    'is_enabled'    => 1,
                );
                $update = array_merge($update, $visibility);
                $ok = $this->updatePageSectionById((int) $existing['id'], $page_id, $update) && $ok;
            }
            return array(
                'ok'      => $ok,
                'message' => $ok
                    ? 'Page updated — it now uses this ' . $section_type . ' design.'
                    : 'Could not update the page section.',
                'created' => false,
            );
        }

        $sort_order = $section_type === 'header' ? 1 : $this->getNextPageSectionSortOrder($page_id);
        if ($section_type === 'header' && $this->isPageSectionSortOrderExists($page_id, 1)) {
            $sort_order = $this->getNextPageSectionSortOrder($page_id);
        }

        $insert = array(
            'page_id'         => $page_id,
            'section_id'      => $section_id,
            'sort_order'      => $sort_order,
            'is_enabled'      => 1,
            $section_column   => $section_contain,
        );
        $insert = array_merge($insert, $visibility);
        $this->ensureSectionEnabledColumn();
        $ok = $this->addPageSection($insert);

        $label = $section_type === 'footer' ? 'Footer' : 'Header';
        return array(
            'ok'      => $ok,
            'message' => $ok
                ? $label . ' section added to the page with this design.'
                : 'Could not add section to the page.',
            'created' => $ok,
        );
    }

    /**
     * Remove storefront header profile from a page (Page header design = None).
     *
     * @param int $page_id
     * @return array{ok:bool,message:string}
     */
    public function clearPageHeaderDesignAssignment($page_id)
    {
        $this->load->helper('cms_layout');
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            return array('ok' => false, 'message' => 'Invalid page.');
        }

        $existing_rows = $this->getPageSectionMappingsBySectionType($page_id, 'header');
        if (empty($existing_rows)) {
            return array('ok' => true, 'message' => 'Page header design cleared.');
        }

        $section_column = $this->hasPageSectionColumn('section_contain') ? 'section_contain' : 'config_json';
        $yes = 'yes';
        $no = 'no';
        $config = array(
            'show_header' => $yes,
            'show_footer' => $no,
            'show_banner'  => $no,
            'show_logo'    => $no,
            'content'      => '',
            'header_mode'  => 'custom',
        );
        $update = array(
            $section_column => json_encode($config),
            'is_enabled'      => 1,
        );
        $update = array_merge($update, $this->buildSectionVisibilityRow('header'));
        $ok = true;
        foreach ($existing_rows as $existing) {
            if (empty($existing['id'])) {
                continue;
            }
            $ok = $this->updatePageSectionById((int) $existing['id'], $page_id, $update) && $ok;
        }

        return array(
            'ok'      => $ok,
            'message' => $ok ? 'Page header design cleared.' : 'Could not clear page header design.',
        );
    }

    /**
     * Remove storefront footer profile assignment from the page footer section.
     *
     * @param int $page_id
     * @return array{ok:bool,message:string}
     */
    public function clearPageFooterDesignAssignment($page_id)
    {
        $this->load->helper('cms_layout');
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            return array('ok' => false, 'message' => 'Invalid page.');
        }

        $existing_rows = $this->getPageSectionMappingsBySectionType($page_id, 'footer');
        if (empty($existing_rows)) {
            return array('ok' => true, 'message' => 'Page footer design cleared.');
        }

        $section_column = $this->hasPageSectionColumn('section_contain') ? 'section_contain' : 'config_json';
        $yes = 'yes';
        $no = 'no';
        $config = array(
            'show_header' => $no,
            'show_footer' => $yes,
            'show_banner'  => $no,
            'show_logo'    => $no,
            'content'      => '',
            'footer_mode'  => 'custom',
        );
        $update = array(
            $section_column => json_encode($config),
            'is_enabled'      => 1,
        );
        $update = array_merge($update, $this->buildSectionVisibilityRow('footer'));
        $ok = true;
        foreach ($existing_rows as $existing) {
            if (empty($existing['id'])) {
                continue;
            }
            $ok = $this->updatePageSectionById((int) $existing['id'], $page_id, $update) && $ok;
        }

        return array(
            'ok'      => $ok,
            'message' => $ok ? 'Page footer design cleared.' : 'Could not clear page footer design.',
        );
    }

    /**
     * @param string $section_type header|footer
     * @return array<string,string>
     */
    private function buildSectionVisibilityRow($section_type)
    {
        $section_type = strtolower(trim((string) $section_type));
        $show_header = $section_type === 'header' ? 'yes' : 'no';
        $show_footer = $section_type === 'footer' ? 'yes' : 'no';
        $show_banner = 'no';
        $show_logo = 'no';
        $data = array();
        foreach (array('header', 'footer', 'banner', 'logo') as $col) {
            if ($this->hasPageSectionColumn($col)) {
                $val = $col === 'header' ? $show_header : ($col === 'footer' ? $show_footer : ($col === 'banner' ? $show_banner : $show_logo));
                $data[$col] = $val;
            }
        }
        foreach (array('show_header', 'show_footer', 'show_banner', 'show_logo') as $col) {
            if ($this->hasPageSectionColumn($col)) {
                $val = $col === 'show_header' ? $show_header : ($col === 'show_footer' ? $show_footer : ($col === 'show_banner' ? $show_banner : $show_logo));
                $data[$col] = $val;
            }
        }
        return $data;
    }

    public function addPageSection($data)
    {
        $data = $this->packPageSectionConfigPayload($data);
        return (bool) $this->db->insert('sma_cms_page_section_mapping', $data);
    }

    public function isPageSectionSortOrderExists($page_id, $sort_order, $exclude_mapping_id = 0)
    {
        $this->db->from('sma_cms_page_section_mapping');
        $this->db->where('page_id', (int) $page_id);
        $this->db->where('sort_order', (int) $sort_order);
        if ((int) $exclude_mapping_id > 0) {
            $this->db->where('id !=', (int) $exclude_mapping_id);
        }
        return $this->db->count_all_results() > 0;
    }

    public function getNextPageSectionSortOrder($page_id)
    {
        $row = $this->db
            ->select_max('sort_order')
            ->where('page_id', (int) $page_id)
            ->get('sma_cms_page_section_mapping')
            ->row_array();

        $max_sort = isset($row['sort_order']) ? (int) $row['sort_order'] : 0;
        return $max_sort + 1;
    }

    public function updatePageSectionById($mapping_id, $page_id, $data)
    {
        $data = $this->packPageSectionConfigPayload($data);
        return (bool) $this->db
            ->where('id', (int) $mapping_id)
            ->where('page_id', (int) $page_id)
            ->update('sma_cms_page_section_mapping', $data);
    }

    public function deletePageSectionById($mapping_id, $page_id)
    {
        return (bool) $this->db
            ->where('id', (int) $mapping_id)
            ->where('page_id', (int) $page_id)
            ->delete('sma_cms_page_section_mapping');
    }

    public function updatePageSectionSortOrders($page_id, array $mapping_orders)
    {
        if (empty($mapping_orders)) {
            return false;
        }

        $table = 'sma_cms_page_section_mapping';
        $page_id = (int) $page_id;

        $all_rows = $this->db
            ->select('id, sort_order')
            ->where('page_id', $page_id)
            ->get($table)
            ->result_array();

        $this->db->trans_start();

        $temp_seed = 1000000;
        foreach ($all_rows as $idx => $row) {
            $this->db
                ->where('id', (int) $row['id'])
                ->update($table, array('sort_order' => $temp_seed + $idx));
        }

        foreach ($mapping_orders as $mapping_id => $sort_order) {
            $this->db
                ->where('id', (int) $mapping_id)
                ->where('page_id', $page_id)
                ->update($table, array('sort_order' => (int) $sort_order));
        }

        $mapped_ids = array_map('intval', array_keys($mapping_orders));
        $max_order = empty($mapping_orders) ? 0 : (int) max($mapping_orders);
        $extra_offset = 1;
        foreach ($all_rows as $row) {
            $row_id = (int) $row['id'];
            if (!in_array($row_id, $mapped_ids, true)) {
                $this->db
                    ->where('id', $row_id)
                    ->where('page_id', $page_id)
                    ->update($table, array('sort_order' => $max_order + $extra_offset));
                $extra_offset++;
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function ensureTagsMasterVisibilityColumns()
    {
        $table = 'sma_cms_tags_master';
        if (!$this->db->table_exists($table)) {
            return false;
        }

        if (!$this->db->field_exists('show_on_page', $table)) {
            $this->db->query(
                'ALTER TABLE `' . $table . '` ADD COLUMN `show_on_page` TINYINT(1) NOT NULL DEFAULT 1 AFTER `page_type`'
            );
        }
        if (!$this->db->field_exists('show_on_entity', $table)) {
            $this->db->query(
                'ALTER TABLE `' . $table . '` ADD COLUMN `show_on_entity` TINYINT(1) NOT NULL DEFAULT 0 AFTER `show_on_page`'
            );
        }

        $this->backfillTagsMasterVisibility();
        return true;
    }

    /**
     * One-time style backfill for show_on_page / show_on_entity flags.
     */
    public function backfillTagsMasterVisibility()
    {
        $table = 'sma_cms_tags_master';
        if (!$this->db->table_exists($table) || !$this->db->field_exists('show_on_page', $table)) {
            return;
        }

        $entity_only = array(
            'product_schema', 'article_schema', 'category_schema',
            'review_schema', 'product_review_schema',
        );
        if (!empty($entity_only)) {
            $this->db->where_in('tag_name', $entity_only);
            $this->db->update($table, array('show_on_page' => 0, 'show_on_entity' => 1));
        }

        $page_only = array(
            'pharmacy_schema', 'organization_schema', 'website_schema', 'rss_feed',
        );
        if (!empty($page_only)) {
            $this->db->where_in('tag_name', $page_only);
            $this->db->update($table, array('show_on_page' => 1, 'show_on_entity' => 0));
        }

        $hidden = array(
            'ai_entity', 'ai_summary', 'ai_category', 'ai_industry',
            'ai_brand', 'ai_purpose', 'ai_keyphrase', 'ai_context',
        );
        if (!empty($hidden)) {
            $this->db->where_in('tag_name', $hidden);
            $this->db->update($table, array('show_on_page' => 0, 'show_on_entity' => 0));
        }

        // Shared SEO tags (page_type = all) belong on both CMS Pages and Entity Tags forms.
        $exclude = array_merge($entity_only, $page_only, $hidden);
        $this->db->where('page_type', 'all');
        $this->db->where_not_in('tag_name', $exclude);
        $this->db->update($table, array('show_on_page' => 1, 'show_on_entity' => 1));
    }

    /**
     * Ensure product/category/blog schema rows exist for Entity Tag forms.
     */
    public function ensureEntitySchemaTagsSeeded()
    {
        $this->ensureTagsMasterVisibilityColumns();
        $this->load->helper('cms_head_tag_import');
        $catalog = cms_tag_master_catalog();
        foreach (array('product_schema', 'category_schema', 'article_schema') as $key) {
            if (!isset($catalog[$key]) || !is_array($catalog[$key])) {
                continue;
            }
            $def = $catalog[$key];
            $def['tag_name'] = $key;
            $this->ensureTagMaster($def);
        }
        $this->backfillTagsMasterVisibility();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getTagsMasterRaw()
    {
        $this->ensureTagsMasterVisibilityColumns();
        $table = 'sma_cms_tags_master';
        $select = 'id, tag_name, tag_type, category, page_type';
        if ($this->db->field_exists('show_on_page', $table)) {
            $select .= ', show_on_page, show_on_entity';
        }
        $q = $this->db->select($select, false)->order_by('tag_name', 'ASC')->get($table);

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * All tags (legacy callers).
     *
     * @return array
     */
    public function getTagsMaster()
    {
        return $this->getTagsMasterRaw();
    }

    /**
     * Ensure sma_cms_pages.page_type exists (static|home|category|product|blog).
     */
    public function ensurePageTypeColumn()
    {
        $table = 'sma_cms_pages';
        if (!$this->db->table_exists($table)) {
            return false;
        }
        if (!$this->db->field_exists('page_type', $table)) {
            $this->db->query(
                'ALTER TABLE `' . $table . '` ADD COLUMN `page_type` VARCHAR(32) NOT NULL DEFAULT \'static\''
            );
        }

        return true;
    }

    /**
     * @param int $page_id
     * @return string
     */
    public function getPageTypeForPage($page_id)
    {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            return 'static';
        }
        $this->ensurePageTypeColumn();
        $page = $this->getPageById($page_id);
        $this->load->helper('cms_tags');

        return cms_sanitize_page_type(
            is_array($page) && isset($page['page_type']) ? (string) $page['page_type'] : 'static'
        );
    }

    /**
     * Tags for CMS Pages edit form — show_on_page + page_type scope (same rules as Entity Tags).
     *
     * @param string $page_type static|home|category|product|blog
     * @return array
     */
    public function getTagsMasterForPage($page_type = 'static')
    {
        $this->load->helper('cms_tags');
        $this->ensureTagsMasterVisibilityColumns();
        $page_type = cms_sanitize_page_type($page_type);
        $all = $this->getTagsMasterRaw();

        return cms_filter_tags_master_for_form($all, 'page', $page_type);
    }

    /**
     * Find a tags_master row by tag_name (optional tag_type filter).
     *
     * @param string      $tag_name
     * @param string|null $tag_type
     * @return array|null
     */
    public function getTagMasterByName($tag_name, $tag_type = null)
    {
        $tag_name = trim((string) $tag_name);
        if ($tag_name === '') {
            return null;
        }

        $this->db
            ->select('id, tag_name, tag_type, category')
            ->where('tag_name', $tag_name);

        if ($tag_type !== null && trim((string) $tag_type) !== '') {
            $this->db->where('tag_type', trim((string) $tag_type));
        }

        $row = $this->db->get('sma_cms_tags_master', 1)->row_array();

        return is_array($row) && !empty($row['id']) ? $row : null;
    }

    /**
     * Enable imported tags on the active admin form (patch legacy show_on_* flags).
     *
     * @param int        $tag_id
     * @param array      $definition
     * @param array|null $import_context
     */
    private function applyTagMasterImportContext($tag_id, array $definition, array $import_context = null)
    {
        $tag_id = (int) $tag_id;
        if ($tag_id < 1 || empty($import_context) || !is_array($import_context)) {
            return;
        }

        $table = 'sma_cms_tags_master';
        if (!$this->db->table_exists($table) || !$this->db->field_exists('show_on_page', $table)) {
            return;
        }

        $form = isset($import_context['form']) ? strtolower(trim((string) $import_context['form'])) : '';
        if ($form !== 'page' && $form !== 'entity') {
            return;
        }

        $update = array();
        if ($form === 'page') {
            $update['show_on_page'] = 1;
        } else {
            $update['show_on_entity'] = 1;
        }

        $scope = isset($import_context['scope_code']) ? strtolower(trim((string) $import_context['scope_code'])) : '';
        $def_scope = isset($definition['page_type']) ? strtolower(trim((string) $definition['page_type'])) : 'all';
        if ($form === 'entity' && $scope !== '' && $def_scope === 'all') {
            $row = $this->db->select('page_type, tag_name')->from($table)->where('id', $tag_id)->get()->row_array();
            if (is_array($row)) {
                $current_scope = isset($row['page_type']) ? strtolower(trim((string) $row['page_type'])) : 'all';
                $entity_only = array(
                    'product_schema', 'article_schema', 'category_schema',
                    'review_schema', 'product_review_schema',
                );
                $tag_name = isset($row['tag_name']) ? (string) $row['tag_name'] : '';
                if ($current_scope !== 'all' && $current_scope !== $scope && !in_array($tag_name, $entity_only, true)) {
                    $update['page_type'] = 'all';
                }
            }
        }

        $this->db->where('id', $tag_id)->update($table, $update);
    }

    /**
     * Insert tags_master row when missing (used by head-script import on CMS pages).
     *
     * @param array      $definition      tag_name, tag_type, category, page_type, template, implementation_code
     * @param array|null $import_context  optional form + scope_code from head-script import
     * @return array{id:int,tag_name:string,category:string,created:bool,error:string}
     */
    public function ensureTagMaster(array $definition, array $import_context = null)
    {
        $this->load->helper('cms_head_tag_import');
        if (!empty($definition['tag_name'])) {
            $definition['tag_name'] = trim((string) $definition['tag_name']);
            $definition = cms_apply_tag_visibility_defaults($definition);
        }

        $tag_name = isset($definition['tag_name']) ? trim((string) $definition['tag_name']) : '';
        $tag_type = isset($definition['tag_type']) ? trim((string) $definition['tag_type']) : 'meta';
        if ($tag_name === '' || $tag_type === '') {
            return array('id' => 0, 'tag_name' => $tag_name, 'category' => '', 'created' => false, 'error' => 'invalid_definition');
        }

        $existing = $this->getTagMasterByName($tag_name, $tag_type);
        if ($existing) {
            $this->applyTagMasterImportContext((int) $existing['id'], $definition, $import_context);
            return array(
                'id' => (int) $existing['id'],
                'tag_name' => (string) $existing['tag_name'],
                'category' => (string) $existing['category'],
                'created' => false,
                'error' => '',
            );
        }

        // Same tag_name may exist under a different tag_type in legacy data.
        $by_name = $this->getTagMasterByName($tag_name, null);
        if ($by_name) {
            $this->applyTagMasterImportContext((int) $by_name['id'], $definition, $import_context);
            return array(
                'id' => (int) $by_name['id'],
                'tag_name' => (string) $by_name['tag_name'],
                'category' => (string) $by_name['category'],
                'created' => false,
                'error' => '',
            );
        }

        $table = 'sma_cms_tags_master';
        $category = isset($definition['category']) ? trim((string) $definition['category']) : 'SEO';
        $page_type = isset($definition['page_type']) ? trim((string) $definition['page_type']) : 'all';
        $template = isset($definition['template']) ? (string) $definition['template'] : '';
        $implementation = isset($definition['implementation_code']) ? (string) $definition['implementation_code'] : '';
        $implementation = $implementation !== ''
            ? $implementation
            : '<meta name="' . $tag_name . '" content="{' . $tag_name . '}">';

        $insert = array(
            'tag_name' => $tag_name,
            'tag_type' => $tag_type,
            'category' => $category !== '' ? $category : 'SEO',
            'page_type' => $page_type !== '' ? $page_type : 'all',
            'template' => $template,
            'implementation_code' => $implementation,
        );
        if ($this->db->field_exists('show_on_page', $table)) {
            $insert['show_on_page'] = array_key_exists('show_on_page', $definition)
                ? ((int) !empty($definition['show_on_page'])) : 1;
            $insert['show_on_entity'] = array_key_exists('show_on_entity', $definition)
                ? ((int) !empty($definition['show_on_entity'])) : 1;
        }
        $inserted = $this->db->insert($table, $insert);

        $new_id = (int) $this->db->insert_id();
        if ($inserted && $new_id > 0) {
            $this->applyTagMasterImportContext($new_id, $definition, $import_context);
            return array(
                'id' => $new_id,
                'tag_name' => $tag_name,
                'category' => $category !== '' ? $category : 'SEO',
                'created' => true,
                'error' => '',
            );
        }

        // Concurrent insert or duplicate — fetch again before giving up.
        $retry = $this->getTagMasterByName($tag_name, $tag_type);
        if (!$retry) {
            $retry = $this->getTagMasterByName($tag_name, null);
        }
        if ($retry) {
            $this->applyTagMasterImportContext((int) $retry['id'], $definition, $import_context);
            return array(
                'id' => (int) $retry['id'],
                'tag_name' => (string) $retry['tag_name'],
                'category' => (string) $retry['category'],
                'created' => false,
                'error' => '',
            );
        }

        $db_error = $this->db->error();
        $error_msg = is_array($db_error) && !empty($db_error['message'])
            ? (string) $db_error['message']
            : 'insert_failed';

        return array(
            'id' => 0,
            'tag_name' => $tag_name,
            'category' => '',
            'created' => false,
            'error' => $error_msg,
        );
    }

    public function getPageTagMappings($page_id)
    {
        $q = $this->db
            ->select('ptm.id, ptm.tag_id, ptm.property_name, ptm.value, tm.tag_name, tm.tag_type')
            ->from('sma_cms_page_tag_mapping' . ' ptm')
            ->join('sma_cms_tags_master' . ' tm', 'tm.id = ptm.tag_id', 'left')
            ->where('ptm.page_id', (int) $page_id)
            ->order_by('ptm.id', 'DESC')
            ->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    public function upsertPageTagValue($page_id, $tag_id, $property_name, $value)
    {
        $table = 'sma_cms_page_tag_mapping';
        $where = array(
            'page_id'       => (int) $page_id,
            'tag_id'        => (int) $tag_id,
            'property_name' => $property_name,
        );

        $exists = $this->db->where($where)->get($table)->row_array();
        if ($exists) {
            return (bool) $this->db->where('id', (int) $exists['id'])->update($table, array('value' => $value));
        }

        return (bool) $this->db->insert($table, array(
            'page_id'       => (int) $page_id,
            'tag_id'        => (int) $tag_id,
            'property_name' => $property_name,
            'value'         => $value,
            'is_dynamic'    => 1,
        ));
    }
}
