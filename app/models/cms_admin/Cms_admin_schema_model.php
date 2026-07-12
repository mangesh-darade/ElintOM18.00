<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * CMS Admin DB bootstrap — auto-create tables/columns on first panel entry.
 * Settings column (active_cms_admin_panel) is ensured on Settings screen too.
 */
class Cms_admin_schema_model extends Cms_admin_base_model
{
    const SCHEMA_VERSION = 3;

    /** ERP settings table (CI prefix → sma_settings). */
    const TBL_SETTINGS = 'settings';

    /** Toggle: show CMS Admin Panel in ERP menu. */
    const COL_ACTIVE_CMS_ADMIN_PANEL = 'active_cms_admin_panel';

    /**
     * Lightweight — call from System Settings before read/save.
     *
     * @return bool
     */
    public function ensure_settings_columns()
    {
        if (!$this->db->table_exists(self::TBL_SETTINGS)) {
            return false;
        }

        return $this->ensure_tinyint_column(
            self::TBL_SETTINGS,
            self::COL_ACTIVE_CMS_ADMIN_PANEL,
            0
        );
    }

    /**
     * Full CMS schema bootstrap (cms_admin routes). Idempotent.
     *
     * @return bool
     */
    public function ensure_all()
    {
        $this->ensure_settings_columns();

        $CI =& get_instance();
        $cached = (int) $CI->session->userdata('cms_admin_schema_v');
        if ($cached >= self::SCHEMA_VERSION) {
            return true;
        }

        $this->ensure_core_tables();
        $this->ensure_cms_column_patches();
        $this->ensure_optional_tables();
        $this->seed_defaults();

        $CI->session->set_userdata('cms_admin_schema_v', self::SCHEMA_VERSION);
        return true;
    }

    /**
     * Core sma_cms_* tables required by CMS Admin + storefront API.
     *
     * @return bool
     */
    protected function ensure_core_tables()
    {
        $ok = true;

        $ok = $this->create_table_if_missing('sma_cms_sections_master', "
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `section_name` VARCHAR(100) NOT NULL,
            `section_type` VARCHAR(100) NOT NULL,
            `ui_component` VARCHAR(100) NOT NULL,
            `util_function` VARCHAR(100) NOT NULL,
            `config_schema` JSON NULL,
            `is_dynamic` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_section_type` (`section_type`)
        ") && $ok;

        $ok = $this->create_table_if_missing('sma_cms_tags_master', "
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tag_name` VARCHAR(100) NOT NULL,
            `tag_type` VARCHAR(50) NOT NULL,
            `category` VARCHAR(50) NULL DEFAULT NULL,
            `page_type` VARCHAR(50) NULL DEFAULT NULL,
            `template` TEXT NULL,
            `implementation_code` TEXT NOT NULL,
            `show_on_page` TINYINT(1) NOT NULL DEFAULT 1,
            `show_on_entity` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_tag_name_type` (`tag_name`, `tag_type`)
        ") && $ok;

        $ok = $this->create_table_if_missing('sma_cms_pages', "
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `page_name` VARCHAR(150) NOT NULL,
            `page_type` VARCHAR(50) NOT NULL DEFAULT 'static',
            `reference_id` BIGINT NULL DEFAULT NULL,
            `url` VARCHAR(255) NOT NULL,
            `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
            `version` INT NOT NULL DEFAULT 1,
            `banner_image` VARCHAR(255) NULL DEFAULT NULL,
            `logo_image` VARCHAR(255) NULL DEFAULT NULL,
            `parent_page_id` INT NULL DEFAULT NULL,
            `submenu_order` INT NOT NULL DEFAULT 0,
            `nav_order` INT NOT NULL DEFAULT 0,
            `show_in_header` TINYINT(1) NOT NULL DEFAULT 0,
            `show_in_footer` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_url` (`url`),
            KEY `idx_page_type` (`page_type`),
            KEY `idx_reference_id` (`reference_id`)
        ") && $ok;

        $ok = $this->create_table_if_missing('sma_cms_page_section_mapping', "
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `page_id` BIGINT UNSIGNED NOT NULL,
            `section_id` BIGINT UNSIGNED NOT NULL,
            `sort_order` INT NOT NULL,
            `section_contain` JSON NULL,
            `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `header` ENUM('yes','no') NOT NULL DEFAULT 'no',
            `footer` ENUM('yes','no') NOT NULL DEFAULT 'no',
            `banner` ENUM('yes','no') NOT NULL DEFAULT 'no',
            `logo` ENUM('yes','no') NOT NULL DEFAULT 'no',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_page_section_order` (`page_id`, `sort_order`),
            KEY `idx_page_sections` (`page_id`)
        ") && $ok;

        $ok = $this->create_table_if_missing('sma_cms_page_tag_mapping', "
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `page_id` BIGINT UNSIGNED NOT NULL,
            `tag_id` BIGINT UNSIGNED NOT NULL,
            `property_name` VARCHAR(100) NOT NULL,
            `value` TEXT NULL,
            `is_dynamic` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_page_tag_property` (`page_id`, `tag_id`, `property_name`),
            KEY `idx_page_tags` (`page_id`)
        ") && $ok;

        $ok = $this->create_table_if_missing('sma_cms_global_tag_defaults', "
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tag_id` BIGINT UNSIGNED NOT NULL,
            `property_name` VARCHAR(100) NOT NULL,
            `value` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_global_tag` (`tag_id`, `property_name`)
        ") && $ok;

        $ok = $this->create_table_if_missing('sma_cms_page_type_tag_defaults', "
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `page_type` VARCHAR(50) NOT NULL,
            `tag_id` BIGINT UNSIGNED NOT NULL,
            `property_name` VARCHAR(100) NOT NULL,
            `value` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_page_type_tag` (`page_type`, `tag_id`, `property_name`)
        ") && $ok;

        $ok = $this->create_table_if_missing('sma_cms_entities_master', "
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `entity_code` VARCHAR(50) NOT NULL,
            `entity_name` VARCHAR(150) NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_entity_code` (`entity_code`)
        ") && $ok;

        $ok = $this->create_table_if_missing('sma_cms_entity_tag_mapping', "
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `entity_master_id` BIGINT UNSIGNED NOT NULL,
            `entity_id` BIGINT UNSIGNED NOT NULL,
            `tag_id` BIGINT UNSIGNED NOT NULL,
            `property_name` VARCHAR(100) NOT NULL,
            `value` TEXT NULL,
            `is_dynamic` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_entity_tag_property` (`entity_master_id`, `entity_id`, `tag_id`, `property_name`),
            KEY `idx_entity` (`entity_master_id`, `entity_id`)
        ") && $ok;

        $ok = $this->create_table_if_missing('sma_cms_webshop_header_footer', "
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `section_type` ENUM('header','footer') NOT NULL,
            `field_key` VARCHAR(100) NOT NULL,
            `layoutname` VARCHAR(64) NULL DEFAULT NULL,
            `label` VARCHAR(255) NOT NULL,
            `value` TEXT NULL,
            `icons` VARCHAR(255) NULL DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_section_field` (`section_type`, `field_key`),
            KEY `idx_whhf_section_layoutname` (`section_type`, `layoutname`)
        ") && $ok;

        return $ok;
    }

    /**
     * Patch columns on existing CMS tables (legacy DBs).
     *
     * @return void
     */
    protected function ensure_cms_column_patches()
    {
        $this->ensure_enum_columns('sma_cms_page_section_mapping', array(
            'header' => "ENUM('yes','no') NOT NULL DEFAULT 'no'",
            'footer' => "ENUM('yes','no') NOT NULL DEFAULT 'no'",
            'banner' => "ENUM('yes','no') NOT NULL DEFAULT 'no'",
            'logo'   => "ENUM('yes','no') NOT NULL DEFAULT 'no'",
        ));

        $this->load->model('cms_model');
        $this->cms_model->ensurePageMediaColumns();
        $this->cms_model->ensureNavOrderColumn();
        $this->cms_model->ensureNavVisibilityColumns();
        $this->cms_model->ensureParentPageColumn();
        $this->cms_model->ensureSubmenuOrderColumn();
        $this->cms_model->ensureAllSectionMasters();

        $this->load->model('cms_admin/Cms_admin_pages_model', 'cms_pages_schema_model');
        $this->cms_pages_schema_model->ensurePageTypeColumn();
        $this->cms_pages_schema_model->ensureTagsMasterVisibilityColumns();
        $this->cms_pages_schema_model->ensureSectionEnabledColumn();
        $this->cms_pages_schema_model->ensureAllSectionMasters();
        $this->cms_pages_schema_model->ensureEntitySchemaTagsSeeded();

        $this->load->model('cms_admin/Cms_admin_storefront_model', 'cms_storefront_schema_model');
        if ($this->cms_storefront_schema_model->schema_ready()) {
            $this->cms_storefront_schema_model->ensure_layoutname_column();
        }

        $this->load->model('cms_admin/Cms_admin_llms_txt_model', 'cms_llms_schema_model');
        $this->cms_llms_schema_model->ensureLlmsTxtColumns();
    }

    /**
     * Tables that ship their own ensure_table() helpers.
     *
     * @return void
     */
    protected function ensure_optional_tables()
    {
        $this->load->model('cms_admin/Cms_admin_pages_faqs_model', 'cms_pages_faqs_schema_model');
        $this->cms_pages_faqs_schema_model->ensure_table();

        $this->load->model('cms_admin/Cms_admin_entity_faqs_model', 'cms_entity_faqs_schema_model');
        $this->cms_entity_faqs_schema_model->ensure_table();

        $this->load->model('Cms_blogs_model', 'cms_blogs_schema_model');
        $this->cms_blogs_schema_model->ensure_table();

        $this->load->model('Cms_blog_categories_model', 'cms_blog_categories_schema_model');
        $this->cms_blog_categories_schema_model->ensure_table();

        $this->load->model('cms_testimonials_model');
        $this->cms_testimonials_model->ensure_table();

        $this->load->model('webshop_api_model');
        $this->webshop_api_model->ensure_webshop_contact_forms_table();
        $this->webshop_api_model->ensure_newsletter_subscriber_table();
    }

    /**
     * @return void
     */
    protected function seed_defaults()
    {
        $this->seed_entities_master();
        $this->seed_default_sections_if_empty();
    }

    /**
     * @return void
     */
    protected function seed_entities_master()
    {
        $table = 'sma_cms_entities_master';
        if (!$this->db->table_exists($table) || (int) $this->db->count_all($table) > 0) {
            return;
        }
        foreach (array(
            array('entity_code' => 'product', 'entity_name' => 'Product', 'is_active' => 1),
            array('entity_code' => 'category', 'entity_name' => 'Category', 'is_active' => 1),
            array('entity_code' => 'blog', 'entity_name' => 'Blog', 'is_active' => 1),
        ) as $row) {
            $this->db->insert($table, $row);
        }
    }

    /**
     * @return void
     */
    protected function seed_default_sections_if_empty()
    {
        $table = 'sma_cms_sections_master';
        if (!$this->db->table_exists($table) || (int) $this->db->count_all($table) > 0) {
            return;
        }
        foreach (array(
            array('section_name' => 'HTML Component', 'section_type' => 'html_block', 'ui_component' => 'HtmlBlockComponent', 'util_function' => 'getHtmlBlockData', 'config_schema' => null, 'is_dynamic' => 0),
            array('section_name' => 'Header', 'section_type' => 'header', 'ui_component' => 'HeaderComponent', 'util_function' => 'getHeaderData', 'config_schema' => null, 'is_dynamic' => 0),
            array('section_name' => 'Footer', 'section_type' => 'footer', 'ui_component' => 'FooterComponent', 'util_function' => 'getFooterData', 'config_schema' => null, 'is_dynamic' => 0),
        ) as $row) {
            $this->db->insert($table, $row);
        }
    }

    /**
     * @param string $table   Logical name (settings) or full name (sma_cms_pages).
     * @param string $column
     * @param int    $default
     * @return bool
     */
    protected function ensure_tinyint_column($table, $column, $default = 0)
    {
        if ($this->db->field_exists($column, $table)) {
            return true;
        }
        $this->load->dbforge();
        $this->dbforge->add_column($table, array(
            $column => array(
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => (int) $default,
                'null'       => false,
            ),
        ));

        return (bool) $this->db->field_exists($column, $table);
    }

    /**
     * @param string $table Full table name including sma_ prefix.
     * @param string $body  Column + key definitions (no CREATE TABLE wrapper).
     * @return bool
     */
    protected function create_table_if_missing($table, $body)
    {
        if ($this->db->table_exists($table)) {
            return true;
        }
        $sql = 'CREATE TABLE IF NOT EXISTS `' . $table . '` (' . $body . ') '
            . 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        return (bool) $this->db->query($sql);
    }

    /**
     * @param string $table
     * @param array  $columns column => "TYPE ... DEFAULT ..."
     * @return bool
     */
    protected function ensure_enum_columns($table, array $columns)
    {
        if (!$this->db->table_exists($table)) {
            return false;
        }
        $parts = array();
        foreach ($columns as $name => $definition) {
            if (!$this->db->field_exists($name, $table)) {
                $parts[] = 'ADD COLUMN `' . $name . '` ' . $definition;
            }
        }
        if (empty($parts)) {
            return true;
        }

        return (bool) $this->db->query('ALTER TABLE `' . $table . '` ' . implode(', ', $parts));
    }
}
