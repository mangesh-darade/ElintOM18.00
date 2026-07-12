<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cms_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get complete page data by URL
     */
    public function getPageData($url) {
         
        $this->db->where('url', $url);
        $this->db->where('status', 'published');
        $q = $this->db->get('sma_cms_pages');
        if ($q->num_rows() > 0) {
            $page = $q->row_array();
            $page_id = $page['id'];
            $page_type = $page['page_type'];

            return [
                'page' => $page,
                'sections' => $this->getSectionsByPageId($page_id),
                'meta' => $this->getTagsByPageId($page_id, $page_type)
            ];
        }
        return FALSE;
    }

    /**
     * List published CMS pages for navigation.
     *
     * @param array       $page_types
     * @param string|null $placement header|footer — filter by show_in_header / show_in_footer; null = all published
     * @return array
     */
    public function getPublishedPages($page_types = array('static', 'category'), $placement = null) {
        $this->ensureNavOrderColumn();
        $this->ensureNavVisibilityColumns();
        $this->ensureParentPageColumn();
        $this->ensureSubmenuOrderColumn();
        $this->backfillNavOrdersIfNeeded();

        $placement = strtolower(trim((string) $placement));

        $select = array('id', 'page_name', 'page_type', 'url', 'status', 'updated_at');
        if ($this->hasPageColumn('nav_order')) {
            $select[] = 'nav_order';
        }
        if ($this->hasPageColumn('show_in_header')) {
            $select[] = 'show_in_header';
        }
        if ($this->hasPageColumn('show_in_footer')) {
            $select[] = 'show_in_footer';
        }
        if ($this->hasPageColumn('parent_page_id')) {
            $select[] = 'parent_page_id';
        }
        if ($this->hasPageColumn('submenu_order')) {
            $select[] = 'submenu_order';
        }
        $this->db->select(implode(', ', $select));
        $this->db->from('sma_cms_pages');
        $this->db->where('status', 'published');
        if ($placement === 'header' && $this->hasPageColumn('show_in_header')) {
            $this->db->where('show_in_header', 1);
        } elseif ($placement === 'footer' && $this->hasPageColumn('show_in_footer')) {
            $this->db->where('show_in_footer', 1);
        }
        if (is_array($page_types) && !empty($page_types)) {
            $this->db->where_in('page_type', $page_types);
        }
        if ($this->hasPageColumn('nav_order')) {
            $this->db->order_by('nav_order', 'ASC');
        }
        $this->db->order_by('page_name', 'ASC');
        $this->db->order_by('id', 'ASC');
        $q = $this->db->get();
        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Single source for CMS admin + webshop API nav: published pages + nested menu tree.
     *
     * @param string|null $placement header|footer|null (all published rows for tree building)
     * @param array       $page_types
     * @return array{pages:array<int,array<string,mixed>>,menu_tree:array<int,array<string,mixed>>}
     */
    public function getPublishedNavPayload($placement = null, $page_types = array('static', 'category')) {
        $all = $this->getPublishedPages($page_types, null);
        $placement = strtolower(trim((string) $placement));
        if (!in_array($placement, array('header', 'footer'), true)) {
            $placement = null;
        }
        $pages = $placement === null ? $all : $this->filterNavPagesForPlacement($all, $placement);
        return array(
            'pages'     => $pages,
            'menu_tree' => $this->buildNavMenuTree($pages),
        );
    }

    /**
     * Header/footer nav rows: flagged pages plus submenu children under visible parents.
     *
     * @param array<int,array<string,mixed>> $pages
     * @param string                         $placement header|footer
     * @return array<int,array<string,mixed>>
     */
    public function filterNavPagesForPlacement(array $pages, $placement) {
        $placement = strtolower(trim((string) $placement));
        if (!in_array($placement, array('header', 'footer'), true)) {
            return $pages;
        }

        $flagField = $placement === 'header' ? 'show_in_header' : 'show_in_footer';
        $byId = array();
        foreach ($pages as $row) {
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id > 0) {
                $byId[$id] = $row;
            }
        }

        $include = array();
        foreach ($byId as $id => $row) {
            if ($this->navVisibilityEnabled(isset($row[$flagField]) ? $row[$flagField] : 0)) {
                $include[$id] = true;
            }
        }
        foreach ($byId as $id => $row) {
            if (isset($include[$id])) {
                continue;
            }
            $parentId = isset($row['parent_page_id']) ? (int) $row['parent_page_id'] : 0;
            while ($parentId > 0 && isset($byId[$parentId])) {
                if (isset($include[$parentId])) {
                    $include[$id] = true;
                    break;
                }
                $parentId = isset($byId[$parentId]['parent_page_id']) ? (int) $byId[$parentId]['parent_page_id'] : 0;
            }
        }

        $out = array();
        foreach ($pages as $row) {
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id > 0 && isset($include[$id])) {
                $out[] = $row;
            }
        }
        return $out;
    }

    /**
     * Nested menu tree from flat published nav rows.
     *
     * @param array<int,array<string,mixed>> $pages
     * @return array<int,array<string,mixed>>
     */
    public function buildNavMenuTree(array $pages) {
        $nodes = array();
        $childrenMap = array();
        foreach ($pages as $row) {
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id <= 0) {
                continue;
            }
            $parentId = isset($row['parent_page_id']) ? (int) $row['parent_page_id'] : 0;
            if ($parentId === $id || $parentId < 0) {
                $parentId = 0;
            }
            $row['parent_page_id'] = $parentId;
            $row['children'] = array();
            $nodes[$id] = $row;
            if (!isset($childrenMap[$parentId])) {
                $childrenMap[$parentId] = array();
            }
            $childrenMap[$parentId][] = $id;
        }

        $build = function ($parentId) use (&$build, &$nodes, &$childrenMap) {
            $items = array();
            if (!isset($childrenMap[$parentId])) {
                return $items;
            }
            foreach ($childrenMap[$parentId] as $id) {
                if (!isset($nodes[$id])) {
                    continue;
                }
                $node = $nodes[$id];
                $node['children'] = $build((int) $node['id']);
                $items[] = $node;
            }
            usort($items, array($this, 'compareNavChildRows'));
            return $items;
        };

        $roots = $build(0);
        usort($roots, array($this, 'compareNavRootRows'));
        return $roots;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function navVisibilityEnabled($value) {
        if ($value === true || $value === 1) {
            return true;
        }
        return in_array(strtolower(trim((string) $value)), array('1', 'true', 'yes', 'on'), true);
    }

    /**
     * @param array<string,mixed> $a
     * @param array<string,mixed> $b
     * @return int
     */
    public function compareNavRootRows($a, $b) {
        $oa = isset($a['nav_order']) ? (int) $a['nav_order'] : 0;
        $ob = isset($b['nav_order']) ? (int) $b['nav_order'] : 0;
        if ($oa !== $ob) {
            return ($oa < $ob) ? -1 : 1;
        }
        return strcasecmp(isset($a['page_name']) ? (string) $a['page_name'] : '', isset($b['page_name']) ? (string) $b['page_name'] : '');
    }

    /**
     * @param array<string,mixed> $a
     * @param array<string,mixed> $b
     * @return int
     */
    public function compareNavChildRows($a, $b) {
        $sa = isset($a['submenu_order']) ? (int) $a['submenu_order'] : 0;
        $sb = isset($b['submenu_order']) ? (int) $b['submenu_order'] : 0;
        if ($sa !== $sb) {
            return ($sa < $sb) ? -1 : 1;
        }
        $oa = isset($a['nav_order']) ? (int) $a['nav_order'] : 0;
        $ob = isset($b['nav_order']) ? (int) $b['nav_order'] : 0;
        if ($oa !== $ob) {
            return ($oa < $ob) ? -1 : 1;
        }
        return strcasecmp(isset($a['page_name']) ? (string) $a['page_name'] : '', isset($b['page_name']) ? (string) $b['page_name'] : '');
    }

    public function ensureParentPageColumn() {
        if ($this->hasPageColumn('parent_page_id')) {
            return true;
        }
        $table = 'sma_cms_pages';
        $sql = "ALTER TABLE `{$table}` ADD COLUMN `parent_page_id` INT NULL DEFAULT NULL";
        return (bool) $this->db->query($sql);
    }

    public function ensureSubmenuOrderColumn() {
        if ($this->hasPageColumn('submenu_order')) {
            return true;
        }
        $table = 'sma_cms_pages';
        $sql = "ALTER TABLE `{$table}` ADD COLUMN `submenu_order` INT NOT NULL DEFAULT 0";
        return (bool) $this->db->query($sql);
    }

    /**
     * Get all CMS pages for admin listing.
     *
     * @return array
     */
    public function getAdminPages() {
        $this->ensureNavOrderColumn();
        $this->backfillNavOrdersIfNeeded();

        $select = array('id', 'page_name', 'page_type', 'url', 'status', 'updated_at');
        if ($this->hasPageColumn('nav_order')) {
            $select[] = 'nav_order';
        }
        if ($this->hasPageColumn('banner_image')) {
            $select[] = 'banner_image';
        }
        if ($this->hasPageColumn('logo_image')) {
            $select[] = 'logo_image';
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

    /**
     * Get single CMS page by id.
     *
     * @param int $id
     * @return array|false
     */
    public function getPageById($id) {
        $q = $this->db
            ->where('id', (int) $id)
            ->get('sma_cms_pages');

        return $q->num_rows() > 0 ? $q->row_array() : false;
    }

    /**
     * Update editable CMS page fields.
     *
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function updatePageById($id, $data) {
        $this->db->where('id', (int) $id);
        return (bool) $this->db->update('sma_cms_pages', $data);
    }

    public function addPage($data) {
        if (!(bool) $this->db->insert('sma_cms_pages', $data)) {
            return false;
        }
        return (int) $this->db->insert_id();
    }

    public function deletePageById($id) {
        return (bool) $this->db
            ->where('id', (int) $id)
            ->delete('sma_cms_pages');
    }

    public function deletePageSectionsByPageId($page_id) {
        return (bool) $this->db
            ->where('page_id', (int) $page_id)
            ->delete('sma_cms_page_section_mapping');
    }

    public function hasPageColumn($column_name) {
        return (bool) $this->db->field_exists($column_name, 'sma_cms_pages');
    }

    public function hasPageSectionColumn($column_name) {
        return (bool) $this->db->field_exists($column_name, 'sma_cms_page_section_mapping');
    }

    public function ensurePageMediaColumns() {
        $table = 'sma_cms_pages';
        $alter_parts = array();

        if (!$this->hasPageColumn('banner_image')) {
            $alter_parts[] = "ADD COLUMN `banner_image` VARCHAR(255) NULL";
        }
        if (!$this->hasPageColumn('logo_image')) {
            $alter_parts[] = "ADD COLUMN `logo_image` VARCHAR(255) NULL";
        }

        if (empty($alter_parts)) {
            return true;
        }

        $sql = "ALTER TABLE `" . $table . "` " . implode(', ', $alter_parts);
        return (bool) $this->db->query($sql);
    }

    /**
     * Menu order for webshop header nav (getcmspages). Lower numbers appear first.
     */
    public function ensureNavOrderColumn() {
        if ($this->hasPageColumn('nav_order')) {
            return true;
        }
        $table = 'sma_cms_pages';
        $sql = "ALTER TABLE `{$table}` ADD COLUMN `nav_order` INT NOT NULL DEFAULT 0";
        return (bool) $this->db->query($sql);
    }

    /**
     * Header/footer nav visibility (cms_admin Show In Header / Show In Footer toggles).
     */
    public function ensureNavVisibilityColumns() {
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
     * Assign nav_order 1, 2, 3… by page id when every row is still 0.
     */
    public function backfillNavOrdersIfNeeded() {
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

    public function getNextNavOrder() {
        if (!$this->hasPageColumn('nav_order')) {
            return 1;
        }
        $table = 'sma_cms_pages';
        $row = $this->db->select_max('nav_order')->get($table)->row_array();
        $max = isset($row['nav_order']) ? (int) $row['nav_order'] : 0;
        return $max > 0 ? $max + 1 : 1;
    }

    /**
     * @param array<int,int> $page_orders page_id => nav_order
     */
    public function updatePageNavOrders(array $page_orders) {
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

    /**
     * Get active section master records for admin dropdown.
     *
     * Header/Footer section types are now selectable from the dropdown so admins can
     * pin a CMS-driven header strip / footer block onto specific pages (consumed by
     * webshopapi via the standard sections payload).
     *
     * @return array
     */
    public function getSectionMasters() {
        $q = $this->db
            ->select('id, section_name, section_type, is_dynamic, util_function')
            ->order_by('section_name', 'ASC')
            ->get('sma_cms_sections_master');

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Seed Header/Footer rows in sections_master if missing.
     *
     * Idempotent — mirrors the ensurePageMediaColumns() pattern. Safe to call on
     * every admin CMS-page-edit load. Relies on UNIQUE KEY uk_section_type so a
     * concurrent insert would no-op rather than duplicate.
     *
     * @return void
     */
    public function ensureHeaderFooterSectionMasters() {
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
     * Seed CMS dynamic section types when missing (blog, testimonials).
     *
     * @return void
     */
    public function ensureDynamicSectionMasters() {
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

    public function ensureAllSectionMasters() {
        $this->ensureHeaderFooterSectionMasters();
        $this->ensureDynamicSectionMasters();
    }

    /**
     * Get mapped sections by page id for admin list.
     *
     * @param int $page_id
     * @return array
     */
    public function getAdminPageSections($page_id) {
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
     * Add section mapping to page.
     *
     * @param array $data
     * @return bool
     */
    public function addPageSection($data) {
        return (bool) $this->db->insert('sma_cms_page_section_mapping', $data);
    }

    public function isPageSectionSortOrderExists($page_id, $sort_order, $exclude_mapping_id = 0) {
        $this->db->from('sma_cms_page_section_mapping');
        $this->db->where('page_id', (int) $page_id);
        $this->db->where('sort_order', (int) $sort_order);
        if ((int) $exclude_mapping_id > 0) {
            $this->db->where('id !=', (int) $exclude_mapping_id);
        }
        return $this->db->count_all_results() > 0;
    }

    public function getNextPageSectionSortOrder($page_id) {
        $row = $this->db
            ->select_max('sort_order')
            ->where('page_id', (int) $page_id)
            ->get('sma_cms_page_section_mapping')
            ->row_array();

        $max_sort = isset($row['sort_order']) ? (int) $row['sort_order'] : 0;
        return $max_sort + 1;
    }

    public function updatePageSectionById($mapping_id, $page_id, $data) {
        return (bool) $this->db
            ->where('id', (int) $mapping_id)
            ->where('page_id', (int) $page_id)
            ->update('sma_cms_page_section_mapping', $data);
    }

    public function deletePageSectionById($mapping_id, $page_id) {
        return (bool) $this->db
            ->where('id', (int) $mapping_id)
            ->where('page_id', (int) $page_id)
            ->delete('sma_cms_page_section_mapping');
    }

    public function updatePageSectionSortOrders($page_id, array $mapping_orders) {
        if (empty($mapping_orders)) {
            return false;
        }

        $table = 'sma_cms_page_section_mapping';
        $page_id = (int) $page_id;

        // Fetch every row for this page (including header/footer sections that the
        // admin list excludes from display). We need them so we can park them at
        // temporary values and then safely restore them without hitting the
        // uk_page_section_order unique constraint.
        $all_rows = $this->db
            ->select('id, sort_order')
            ->where('page_id', $page_id)
            ->get($table)
            ->result_array();

        $this->db->trans_start();

        // Step 1: Move EVERY row for this page to collision-safe temporary values so
        // that none of the real sort_order slots are occupied while we reassign.
        $temp_seed = 1000000;
        foreach ($all_rows as $idx => $row) {
            $this->db
                ->where('id', (int) $row['id'])
                ->update($table, array('sort_order' => $temp_seed + $idx));
        }

        // Step 2: Apply the new sort orders for the user-reordered sections.
        foreach ($mapping_orders as $mapping_id => $sort_order) {
            $this->db
                ->where('id', (int) $mapping_id)
                ->where('page_id', $page_id)
                ->update($table, array('sort_order' => (int) $sort_order));
        }

        // Step 3: For rows that were NOT part of the reorder payload (e.g. header /
        // footer sections hidden from the admin list), restore them to sort_orders
        // that follow all the user-ordered sections so they stay out of the way.
        $mapped_ids   = array_map('intval', array_keys($mapping_orders));
        $max_order    = empty($mapping_orders) ? 0 : (int) max($mapping_orders);
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

    /**
     * Get all tags from tags master.
     *
     * @return array
     */
    public function getTagsMaster() {
        $q = $this->db
            ->select('id, tag_name, tag_type, category')
            ->order_by('tag_name', 'ASC')
            ->get('sma_cms_tags_master');

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get page specific mapped tags only.
     *
     * @param int $page_id
     * @return array
     */
    public function getPageTagMappings($page_id) {
        $q = $this->db
            ->select('ptm.id, ptm.tag_id, ptm.property_name, ptm.value, tm.tag_name, tm.tag_type')
            ->from('sma_cms_page_tag_mapping' . ' ptm')
            ->join('sma_cms_tags_master' . ' tm', 'tm.id = ptm.tag_id', 'left')
            ->where('ptm.page_id', (int) $page_id)
            ->order_by('ptm.id', 'DESC')
            ->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Insert or update page tag mapping.
     *
     * @param int    $page_id
     * @param int    $tag_id
     * @param string $property_name
     * @param string $value
     * @return bool
     */
    public function upsertPageTagValue($page_id, $tag_id, $property_name, $value) {
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

    /**
     * Active entity masters (product/category/page/brand etc).
     *
     * @return array
     */
    public function getEntityMasters() {
        $q = $this->db
            ->select('id, entity_code, entity_name, description')
            ->from('sma_cms_entities_master')
            ->where('is_active', 1)
            ->group_by('id')
            ->order_by('entity_name', 'ASC')
            ->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Resolve one entity master by id.
     *
     * @param int $entity_master_id
     * @return array|null
     */
    public function getEntityMasterById($entity_master_id) {
        $row = $this->db
            ->select('id, entity_code, entity_name')
            ->from('sma_cms_entities_master')
            ->where('id', (int) $entity_master_id)
            ->where('is_active', 1)
            ->get()
            ->row_array();
        return $row ? $row : null;
    }

    /**
     * Fetch entities list by master code.
     *
     * @param string $entity_code
     * @return array
     */
    public function getEntitiesByMasterCode($entity_code) {
        $entity_code = strtolower(trim((string) $entity_code));
        if ($entity_code === 'product') {
            $q = $this->db
                ->select('id, name')
                ->from('products')
                ->where('is_active', 1)
                ->where('in_eshop', 1)
                ->order_by('name', 'ASC')
                ->get();
            $rows = $q->num_rows() > 0 ? $q->result_array() : array();
            $out = array();
            foreach ($rows as $row) {
                $out[] = array(
                    'id' => (int) $row['id'],
                    'name' => (string) $row['name'],
                );
            }
            return $out;
        }

        if ($entity_code === 'category') {
            $q = $this->db
                ->select('id, name')
                ->from('categories')
                ->where('is_active', 1)
                ->where('in_eshop', 1)
                ->order_by('name', 'ASC')
                ->get();
            $rows = $q->num_rows() > 0 ? $q->result_array() : array();
            $out = array();
            foreach ($rows as $row) {
                $out[] = array(
                    'id' => (int) $row['id'],
                    'name' => (string) $row['name'],
                );
            }
            return $out;
        }

        return array();
    }

    /**
     * Entity tag mappings for selected entity.
     *
     * @param int $entity_master_id
     * @param int $entity_id
     * @return array
     */
    public function getEntityTagMappings($entity_master_id, $entity_id) {
        $q = $this->db
            ->select('etm.id, etm.tag_id, etm.property_name, etm.value, tm.tag_name, tm.tag_type')
            ->from('sma_cms_entity_tag_mapping' . ' etm')
            ->join('sma_cms_tags_master' . ' tm', 'tm.id = etm.tag_id', 'left')
            ->where('etm.entity_master_id', (int) $entity_master_id)
            ->where('etm.entity_id', (int) $entity_id)
            ->order_by('etm.id', 'DESC')
            ->get();

        return $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Insert or update entity tag value.
     *
     * @param int $entity_master_id
     * @param int $entity_id
     * @param int $tag_id
     * @param string $property_name
     * @param string $value
     * @return bool
     */
    public function upsertEntityTagValue($entity_master_id, $entity_id, $tag_id, $property_name, $value) {
        $table = 'sma_cms_entity_tag_mapping';
        $where = array(
            'entity_master_id' => (int) $entity_master_id,
            'entity_id'        => (int) $entity_id,
            'tag_id'           => (int) $tag_id,
            'property_name'    => $property_name,
        );

        $exists = $this->db->where($where)->get($table)->row_array();
        if ($exists) {
            return (bool) $this->db->where('id', (int) $exists['id'])->update($table, array('value' => $value));
        }

        return (bool) $this->db->insert($table, array(
            'entity_master_id' => (int) $entity_master_id,
            'entity_id'        => (int) $entity_id,
            'tag_id'           => (int) $tag_id,
            'property_name'    => $property_name,
            'value'            => $value,
            'is_dynamic'       => 1,
        ));
    }

    /**
     * Get sections mapped to a page
     */
    public function getSectionsByPageId($page_id) {
        $this->db->select('psm.*, sm.section_name, sm.section_type, sm.ui_component, sm.util_function, sm.is_dynamic');
        $this->db->from('sma_cms_page_section_mapping' . ' psm');
        $this->db->join('sma_cms_sections_master' . ' sm', 'sm.id = psm.section_id');
        $this->db->where('psm.page_id', (int) $page_id);
        // Only active sections render on the webshop (is_enabled = 1).
        $this->db->where('COALESCE(psm.is_enabled, 0) =', 1, false);
        $this->db->order_by('psm.sort_order', 'ASC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return [];
    }

    /**
     * Get tags for a page with fallback logic (Page -> Page Type -> Global)
     */
    public function getTagsByPageId($page_id, $page_type) {
        
        // 1. Get global defaults (Lowest priority)
        $this->db->select('gtd.property_name, gtd.value, tm.tag_name, tm.tag_type, tm.template, tm.implementation_code');
        $this->db->from('sma_cms_global_tag_defaults' . ' gtd');
        $this->db->join('sma_cms_tags_master' . ' tm', 'tm.id = gtd.tag_id');
        $q = $this->db->get();
        $global_tags = $q->result_array();

        // 2. Get page type defaults
        $this->db->select('pttd.property_name, pttd.value, tm.tag_name, tm.tag_type, tm.template, tm.implementation_code');
        $this->db->from('sma_cms_page_type_tag_defaults' . ' pttd');
        $this->db->join('sma_cms_tags_master' . ' tm', 'tm.id = pttd.tag_id');
        $this->db->where('pttd.page_type', $page_type);
        $q = $this->db->get();
        $type_tags = $q->result_array();

        // 3. Get page specific tags (Highest priority)
        $this->db->select('ptm.property_name, ptm.value, tm.tag_name, tm.tag_type, tm.template, tm.implementation_code');
        $this->db->from('sma_cms_page_tag_mapping' . ' ptm');
        $this->db->join('sma_cms_tags_master' . ' tm', 'tm.id = ptm.tag_id');
        $this->db->where('ptm.page_id', $page_id);
        $q = $this->db->get();
        $page_tags = $q->result_array();

        // Merge tags with priority
        $final_tags = [];
        
        foreach($global_tags as $t) {
            $key = $t['tag_name'] . '_' . $t['property_name'];
            $final_tags[$key] = $t;
        }
        foreach($type_tags as $t) {
            $key = $t['tag_name'] . '_' . $t['property_name'];
            $final_tags[$key] = $t;
        }
        foreach($page_tags as $t) {
            $key = $t['tag_name'] . '_' . $t['property_name'];
            $final_tags[$key] = $t;
        }

        return array_values($final_tags);
    }

    // ============================================================
    // UTIL FUNCTIONS FOR DYNAMIC COMPONENTS
    // ============================================================

    /**
     * Util function to get data for product grid/carousel
     */
    public function getProductGridData($config = []) {
        $limit = isset($config['limit']) ? (int) $config['limit'] : 8;
        if ($limit <= 0) {
            $limit = 8;
        }
        $category_id = isset($config['category_id']) ? $config['category_id'] : null;

        $this->db->select('id, name, code, price, image');
        $this->db->where('in_eshop', 1);
        $this->db->where('is_active', 1);
        if ($category_id !== null && $category_id !== '') {
            $this->db->group_start();
            $this->db->where('category_id', (int) $category_id);
            $this->db->or_where('subcategory_id', (int) $category_id);
            $this->db->group_end();
        }
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        $q = $this->db->get('products');

        $products = $q ? $q->result_array() : array();

        foreach ($products as &$p) {
            $p['image'] = base_url('assets/uploads/' . (!empty($p['image']) ? $p['image'] : 'no_image.png'));
        }

        return array('products' => $products);
    }

    /**
     * Same product payload as grid; carousel view applies different markup.
     */
    public function getProductCarouselData($config = []) {
        return $this->getProductGridData($config);
    }

    /**
     * Top-level eshop categories for category grid component.
     * When no root categories exist (everything nested under parent_id > 0), falls back to a flat list.
     */
    public function getCategoryGridData($config = []) {
        $limit = isset($config['limit']) ? (int) $config['limit'] : 0;

        $categories = $this->_cms_fetch_root_eshop_categories($limit);

        if (empty($categories)) {
            $fallback_limit = $limit > 0 ? $limit : 48;
            $categories = $this->_cms_fetch_flat_eshop_categories($fallback_limit);
        }

        foreach ($categories as &$c) {
            $c['image'] = !empty($c['image']) ? $c['image'] : '';
        }
        unset($c);

        return array('categories' => $categories);
    }

    /**
     * @param int $limit 0 = no limit (still capped internally on flat fallback path).
     * @return array
     */
    private function _cms_fetch_root_eshop_categories($limit) {
        $this->db->select('id, name, image');
        $this->db->from('categories');
        $this->db->where('is_active', 1);
        $this->db->where('in_eshop', 1);
        $this->db->where('(parent_id IS NULL OR parent_id = 0)', null, false);
        $this->db->order_by('name', 'ASC');
        if ($limit > 0) {
            $this->db->limit($limit);
        }
        $q = $this->db->get();

        return ($q && $q->num_rows() > 0) ? $q->result_array() : array();
    }

    /**
     * All active eshop categories ordered by hierarchy hint then name (storefronts that only use subcategories).
     *
     * @param int $limit
     * @return array
     */
    private function _cms_fetch_flat_eshop_categories($limit) {
        $cap = max(1, min(200, (int) $limit));
        $this->db->select('id, name, image');
        $this->db->from('categories');
        $this->db->where('is_active', 1);
        $this->db->where('in_eshop', 1);
        $this->db->order_by('parent_id', 'ASC');
        $this->db->order_by('name', 'ASC');
        $this->db->limit($cap);
        $q = $this->db->get();

        return ($q && $q->num_rows() > 0) ? $q->result_array() : array();
    }

    /**
     * Same as category grid; carousel view uses horizontal layout.
     */
    public function getCategoryCarouselData($config = []) {
        return $this->getCategoryGridData($config);
    }

    /**
     * Optional data for footer component (static defaults).
     */
    public function getFooterData($config = []) {
        return array(
            'copyright' => isset($config['copyright']) ? $config['copyright'] : ('&copy; ' . date('Y') . ' All rights reserved.'),
        );
    }

    /**
     * Util function to get data for header (menu, logo)
     */
    public function getHeaderData($config = []) {
        // This could fetch from settings or a menu manager
        return [
            'logo' => base_url('assets/images/logo.png'),
            'menu' => [
                ['name' => 'Home', 'url' => base_url()],
                ['name' => 'Products', 'url' => base_url('webshop/products')],
                ['name' => 'About Us', 'url' => base_url('about-us')],
            ]
        ];
    }

    /**
     * Published CMS page id used as the product-detail recommendations template.
     *
     * @return int
     */
    public function resolveProductDetailTemplatePageId()
    {
        $q = $this->db
            ->select('id')
            ->from('sma_cms_pages')
            ->where('status', 'published')
            ->where('page_type', 'product')
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get();
        if ($q && $q->num_rows() > 0) {
            return (int) $q->row()->id;
        }

        $q = $this->db
            ->select('psm.page_id')
            ->from('sma_cms_page_section_mapping psm')
            ->join('sma_cms_sections_master sm', 'sm.id = psm.section_id')
            ->join('sma_cms_pages p', 'p.id = psm.page_id')
            ->where('p.status', 'published')
            ->where_in('sm.section_type', array('related_products', 'recent_viewed_products'))
            ->where('COALESCE(psm.is_enabled, 0) =', 1, false)
            ->order_by('psm.page_id', 'ASC')
            ->limit(1)
            ->get();
        if ($q && $q->num_rows() > 0) {
            return (int) $q->row()->page_id;
        }

        return 0;
    }

    /**
     * @param array $sections
     * @return array
     */
    public function filterRecommendationSections(array $sections)
    {
        $allowed = array('related_products', 'recent_viewed_products');
        $out = array();
        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            $type = strtolower(trim((string) (isset($section['section_type']) ? $section['section_type'] : '')));
            if (in_array($type, $allowed, true)) {
                $out[] = $section;
            }
        }
        return $out;
    }

    /**
     * Render related + recently-viewed CMS sections for a product detail page.
     *
     * @param int          $product_id
     * @param Cms_renderer $renderer
     * @return string HTML
     */
    public function renderProductDetailRecommendationsHtml($product_id, $renderer = null)
    {
        $product_id = (int) $product_id;
        if ($product_id <= 0) {
            return '';
        }

        $page_id = $this->resolveProductDetailTemplatePageId();
        if ($page_id <= 0) {
            return '';
        }

        $sections = $this->filterRecommendationSections($this->getSectionsByPageId($page_id));
        if (empty($sections)) {
            return '';
        }

        $CI =& get_instance();
        if ($renderer === null) {
            $CI->load->library('cms_renderer');
            $renderer = $CI->cms_renderer;
        }

        $product_row = $this->db
            ->select('id, category_id, subcategory_id')
            ->where('id', $product_id)
            ->get('sma_products')
            ->row_array();

        $context = array(
            'product_id'     => $product_id,
            'page_type'      => 'product',
            'category_id'    => is_array($product_row) && isset($product_row['category_id']) ? (int) $product_row['category_id'] : 0,
            'subcategory_id' => is_array($product_row) && isset($product_row['subcategory_id']) ? (int) $product_row['subcategory_id'] : 0,
        );

        $CI->load->helper('cms_layout');
        $layout = cms_build_page_layout_sections($sections, $renderer, $context);

        return isset($layout['body_html']) ? (string) $layout['body_html'] : '';
    }

    /**
     * Related products for the current product (same category/subcategory).
     *
     * @param array $config
     * @param array $context product_id required
     * @return array
     */
    public function getRelatedProductsData($config = array(), $context = array())
    {
        $config = is_array($config) ? $config : array();
        $context = is_array($context) ? $context : array();
        $title = $this->_cms_section_title($config, 'Related Products');
        $limit = isset($config['limit']) ? (int) $config['limit'] : 8;
        if ($limit <= 0) {
            $limit = 8;
        }

        $product_id = isset($context['product_id']) ? (int) $context['product_id'] : 0;
        if ($product_id <= 0 && isset($config['product_id'])) {
            $product_id = (int) $config['product_id'];
        }

        $this->load->model('webshop_model');

        // CMS Products page (no single product): show catalog carousel.
        if ($product_id <= 0) {
            $category_id = isset($config['category_id']) ? (int) $config['category_id'] : 0;
            if ($category_id > 0) {
                $result = $this->webshop_model->get_products_list(
                    'category',
                    md5((string) $category_id),
                    true,
                    $limit
                );
            } else {
                $result = $this->webshop_model->get_products_list(null, null, false, $limit);
            }
            $items = (is_array($result) && isset($result['items']) && is_array($result['items']))
                ? $result['items']
                : array();

            return array(
                'title'    => $title,
                'products' => array_slice($items, 0, $limit),
            );
        }

        $row = $this->db
            ->select('id, category_id, subcategory_id')
            ->where('id', $product_id)
            ->get('sma_products')
            ->row_array();
        if (!$row) {
            return array('title' => $title, 'products' => array());
        }

        $category_id = !empty($row['subcategory_id']) ? (int) $row['subcategory_id'] : (int) $row['category_id'];
        if ($category_id <= 0) {
            return array('title' => $title, 'products' => array());
        }

        $categoryHash = md5((string) $category_id);
        $fetch_limit = $limit + 5;
        $result = $this->webshop_model->get_products_list('category', $categoryHash, true, $fetch_limit);
        $items = (is_array($result) && isset($result['items']) && is_array($result['items'])) ? $result['items'] : array();

        $products = array();
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if ((int) $item['id'] === $product_id) {
                continue;
            }
            $products[] = $item;
            if (count($products) >= $limit) {
                break;
            }
        }

        return array(
            'title'    => $title,
            'products' => $products,
        );
    }

    /**
     * Recently viewed products for the current visitor (IP or logged-in user).
     *
     * @param array $config
     * @param array $context optional product_id to exclude from list
     * @return array
     */
    public function getRecentViewedProductsData($config = array(), $context = array())
    {
        $config = is_array($config) ? $config : array();
        $context = is_array($context) ? $context : array();
        $title = $this->_cms_section_title($config, 'Recently Viewed');
        $limit = isset($config['limit']) ? (int) $config['limit'] : 15;
        if ($limit <= 0) {
            $limit = 15;
        }

        $exclude_id = isset($context['product_id']) ? (int) $context['product_id'] : 0;

        $this->load->model('webshop_model');
        $recent = $this->webshop_model->get_recent_viewed_product();
        if (!is_array($recent) && !($recent instanceof Traversable)) {
            $recent = $recent ? array($recent) : array();
        }

        $products = array();
        foreach ((array) $recent as $row) {
            $item = $this->_cms_normalize_recent_product_row($row);
            if (empty($item)) {
                continue;
            }
            if ($exclude_id > 0 && (int) $item['id'] === $exclude_id) {
                continue;
            }
            $products[] = $item;
            if (count($products) >= $limit) {
                break;
            }
        }

        return array(
            'title'    => $title,
            'products' => $products,
        );
    }

    /**
     * @param array  $config
     * @param string $default
     * @return string
     */
    private function _cms_section_title(array $config, $default)
    {
        foreach (array('title', 'heading') as $key) {
            if (!empty($config[$key]) && trim((string) $config[$key]) !== '') {
                return trim((string) $config[$key]);
            }
        }
        return $default;
    }

    /**
     * @param mixed $row
     * @return array
     */
    private function _cms_normalize_recent_product_row($row)
    {
        if (is_array($row)) {
            $data = $row;
        } elseif (is_object($row)) {
            $data = (array) $row;
        } else {
            return array();
        }

        if (empty($data['id'])) {
            return array();
        }

        if (!empty($data['variant_id']) && empty($data['variants'])) {
            $data['variants'] = array(
                array(
                    'id'             => (int) $data['variant_id'],
                    'name'           => isset($data['variant_name']) ? (string) $data['variant_name'] : '',
                    'price'          => isset($data['variant_price']) ? $data['variant_price'] : 0,
                    'quantity'       => 1,
                    'unit_quantity'  => 1,
                ),
            );
        }

        return $data;
    }
}
