<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CMS blog posts (sma_cms_blogs) for storefront API and CMS admin.
 */
class Cms_blogs_model extends CI_Model {

    /**
     * @return bool
     */
    public function ensure_table() {
        if ($this->db->table_exists('sma_cms_blogs')) {
            return true;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `sma_cms_blogs` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `subtitle` VARCHAR(255) NULL DEFAULT NULL,
            `slug` VARCHAR(255) NOT NULL,
            `image` VARCHAR(255) NULL DEFAULT NULL,
            `short_description` TEXT NULL,
            `long_description` TEXT NULL,
            `html_content` MEDIUMTEXT NULL,
            `text_content` TEXT NULL,
            `button_text` VARCHAR(120) NOT NULL DEFAULT 'Read Full Story',
            `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
            `sort_order` INT NOT NULL DEFAULT 0,
            `published_at` DATETIME NULL DEFAULT NULL,
            `category_id` BIGINT UNSIGNED NULL DEFAULT NULL,
            `created_by` INT NULL DEFAULT NULL,
            `updated_by` INT NULL DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_cms_blogs_slug` (`slug`),
            KEY `idx_cms_blogs_status` (`status`),
            KEY `idx_cms_blogs_active` (`is_active`),
            KEY `idx_cms_blogs_sort` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return (bool) $this->db->query($sql);
    }

    /**
     * @return bool
     */
    public function ensure_category_id_column() {
        if (!$this->db->table_exists('sma_cms_blogs')) {
            return false;
        }
        $this->ensure_published_at_column();
        $table = 'sma_cms_blogs';
        if ($this->db->field_exists('category_id', $table)) {
            return true;
        }
        $after = $this->db->field_exists('published_at', $table) ? 'published_at' : 'sort_order';
        $sql = 'ALTER TABLE `' . $table . '` ADD COLUMN `category_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `' . $after . '`';
        return (bool) $this->db->query($sql);
    }

    /**
     * @param array $row
     * @param array<int,array<string,mixed>> $categoryMap
     * @return array
     */
    protected function attach_category_fields(array $row, array $categoryMap = array()) {
        $categoryId = isset($row['category_id']) ? (int) $row['category_id'] : 0;
        $row['category_id'] = $categoryId;
        $row['category_name'] = '';
        $row['category_slug'] = '';
        if ($categoryId > 0 && isset($categoryMap[$categoryId])) {
            $row['category_name'] = (string) $categoryMap[$categoryId]['name'];
            $row['category_slug'] = (string) $categoryMap[$categoryId]['slug'];
        }
        return $row;
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @return array<int,array<string,mixed>>
     */
    protected function attach_categories_to_rows(array $rows) {
        if (empty($rows)) {
            return $rows;
        }
        $this->load->model('Cms_blog_categories_model', 'cms_blog_categories_model');
        $ids = array();
        foreach ($rows as $row) {
            $cid = isset($row['category_id']) ? (int) $row['category_id'] : 0;
            if ($cid > 0) {
                $ids[] = $cid;
            }
        }
        $map = $this->cms_blog_categories_model->map_by_ids($ids);
        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->attach_category_fields($row, $map);
        }
        return $out;
    }

    /**
     * @return bool
     */
    public function ensure_published_at_column() {
        if (!$this->db->table_exists('sma_cms_blogs')) {
            return false;
        }
        $table = 'sma_cms_blogs';
        if ($this->db->field_exists('published_at', $table)) {
            return true;
        }
        $sql = 'ALTER TABLE `' . $table . '` ADD COLUMN `published_at` DATETIME NULL DEFAULT NULL AFTER `sort_order`';
        return (bool) $this->db->query($sql);
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param int $categoryId
     * @return array{items: array, total_items: int, page: int, per_page: int, total_pages: int}
     */
    public function list_published($page = 1, $perPage = 12, $categoryId = 0) {
        $empty = array('items' => array(), 'total_items' => 0, 'page' => 1, 'per_page' => 12, 'total_pages' => 1);
        if (!$this->ensure_table()) {
            return $empty;
        }
        $this->ensure_published_at_column();
        $this->ensure_category_id_column();
        $page = max(1, (int) $page);
        $perPage = max(1, min(100, (int) $perPage));
        $categoryId = (int) $categoryId;
        $table = 'sma_cms_blogs';

        $this->db->from($table);
        $this->db->where('status', 'published');
        $this->db->where('is_active', 1);
        if ($categoryId > 0 && $this->db->field_exists('category_id', $table)) {
            $this->db->where('category_id', $categoryId);
        }
        $total = (int) $this->db->count_all_results();

        $this->db->from($table);
        $this->db->where('status', 'published');
        $this->db->where('is_active', 1);
        if ($categoryId > 0 && $this->db->field_exists('category_id', $table)) {
            $this->db->where('category_id', $categoryId);
        }
        $this->db->order_by('sort_order', 'ASC');
        if ($this->db->field_exists('published_at', $table)) {
            $this->db->order_by('published_at', 'DESC');
        }
        $this->db->order_by('updated_at', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit($perPage, ($page - 1) * $perPage);
        $q = $this->db->get();

        $items = array();
        if ($q && $q->num_rows() > 0) {
            $raw = $q->result_array();
            $raw = $this->attach_categories_to_rows($raw);
            foreach ($raw as $row) {
                $items[] = $this->format_list_row($row);
            }
        }

        $totalPages = $total > 0 ? (int) max(1, ceil($total / $perPage)) : 1;
        return array(
            'items'       => $items,
            'total_items' => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
        );
    }

    /**
     * @param int    $id
     * @param string $slug
     * @return array|null
     */
    public function get_published($id = 0, $slug = '') {
        if (!$this->ensure_table()) {
            return null;
        }
        $table = 'sma_cms_blogs';
        $id = (int) $id;
        $slug = trim((string) $slug);
        $slug = ltrim($slug, '/');

        $this->db->from($table);
        $this->db->where('status', 'published');
        $this->db->where('is_active', 1);
        if ($id > 0) {
            $this->db->where('id', $id);
        } elseif ($slug !== '') {
            $this->db->where('slug', $slug);
        } else {
            return null;
        }
        $this->db->limit(1);
        $q = $this->db->get();
        if (!$q || $q->num_rows() === 0) {
            return null;
        }
        $row = $this->attach_categories_to_rows(array($q->row_array()));
        return $this->format_detail_row($row[0]);
    }

    /**
     * @param int $limit
     * @return array<int,array<string,mixed>>
     */
    public function list_all_admin($limit = 5000) {
        if (!$this->ensure_table()) {
            return array();
        }
        $this->ensure_category_id_column();
        $this->db->from('sma_cms_blogs');
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('updated_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit((int) $limit);
        }
        $q = $this->db->get();
        if (!$q || $q->num_rows() === 0) {
            return array();
        }
        $raw = $q->result_array();
        $raw = $this->attach_categories_to_rows($raw);
        $rows = array();
        foreach ($raw as $row) {
            $rows[] = $this->normalize_admin_row($row);
        }
        return $rows;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function get_by_id($id) {
        $id = (int) $id;
        if ($id < 1 || !$this->ensure_table()) {
            return null;
        }
        $q = $this->db->get_where('sma_cms_blogs', array('id' => $id), 1);
        if (!$q || $q->num_rows() === 0) {
            return null;
        }
        $row = $this->attach_categories_to_rows(array($q->row_array()));
        return $this->normalize_admin_row($row[0]);
    }

    /**
     * @param array $data
     * @param int   $userId
     * @return array{ok:bool,id:int,message:string}
     */
    public function save(array $data, $userId = 0) {
        if (!$this->ensure_table()) {
            return array('ok' => false, 'id' => 0, 'message' => 'Blog table could not be created.');
        }
        $this->ensure_published_at_column();
        $this->ensure_category_id_column();
        $table = 'sma_cms_blogs';
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $title = isset($data['title']) ? trim((string) $data['title']) : '';
        if ($title === '') {
            return array('ok' => false, 'id' => $id, 'message' => 'Title is required.');
        }

        $slug = isset($data['slug']) ? trim((string) $data['slug']) : '';
        if ($slug === '') {
            $slug = $this->slugify($title);
        } else {
            $slug = $this->slugify($slug);
        }
        $slug = $this->ensure_unique_slug($slug, $id);
        if ($slug === '') {
            return array('ok' => false, 'id' => $id, 'message' => 'Could not generate a valid URL slug.');
        }

        $status = isset($data['status']) ? strtolower(trim((string) $data['status'])) : 'draft';
        if (!in_array($status, array('draft', 'published'), true)) {
            $status = 'draft';
        }

        $content = $this->normalize_blog_content_fields($data);

        $publishedAt = $this->normalize_published_at(isset($data['published_at']) ? $data['published_at'] : '');
        if ($status === 'published' && $publishedAt === null) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $row = array(
            'title'              => $title,
            'subtitle'           => isset($data['subtitle']) ? trim((string) $data['subtitle']) : '',
            'slug'               => $slug,
            'image'              => isset($data['image']) ? trim((string) $data['image']) : '',
            'short_description'  => isset($data['short_description']) ? trim((string) $data['short_description']) : '',
            'long_description'   => '',
            'html_content'       => $content['html_content'],
            'text_content'       => $content['text_content'],
            'button_text'        => isset($data['button_text']) && trim((string) $data['button_text']) !== ''
                ? trim((string) $data['button_text'])
                : 'Read Full Story',
            'status'             => $status,
            'sort_order'         => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            'published_at'       => $publishedAt,
            'is_active'          => !empty($data['is_active']) ? 1 : 0,
            'updated_by'         => (int) $userId > 0 ? (int) $userId : null,
        );
        if ($this->db->field_exists('category_id', $table)) {
            $categoryId = isset($data['category_id']) ? (int) $data['category_id'] : 0;
            $row['category_id'] = $categoryId > 0 ? $categoryId : null;
        }

        if ($id > 0) {
            $this->db->where('id', $id)->update($table, $row);
            return array('ok' => true, 'id' => $id, 'message' => 'Blog post updated.');
        }

        $row['created_by'] = (int) $userId > 0 ? (int) $userId : null;
        $this->db->insert($table, $row);
        $newId = (int) $this->db->insert_id();
        return array(
            'ok'      => $newId > 0,
            'id'      => $newId,
            'message' => $newId > 0 ? 'Blog post created.' : 'Insert failed.',
        );
    }

    /**
     * @param int $id
     * @return array{ok:bool,message:string}
     */
    public function delete($id) {
        $id = (int) $id;
        if ($id < 1 || !$this->db->table_exists('sma_cms_blogs')) {
            return array('ok' => false, 'message' => 'Blog post not found.');
        }
        $this->db->where('id', $id)->delete('sma_cms_blogs');
        return array('ok' => $this->db->affected_rows() > 0, 'message' => 'Blog post deleted.');
    }

    /**
     * @param array $row
     * @return array<string,mixed>
     */
    public function format_list_row(array $row) {
        $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
        $imageUrl = $this->build_image_url(isset($row['image']) ? $row['image'] : '');
        $displayDate = '';
        if (!empty($row['published_at'])) {
            $displayDate = (string) $row['published_at'];
        } elseif (!empty($row['updated_at'])) {
            $displayDate = (string) $row['updated_at'];
        }
        return array(
            'id'                => isset($row['id']) ? (int) $row['id'] : 0,
            'title'             => isset($row['title']) ? (string) $row['title'] : '',
            'subtitle'          => isset($row['subtitle']) ? (string) $row['subtitle'] : '',
            'page_name'         => isset($row['title']) ? (string) $row['title'] : '',
            'slug'              => $slug,
            'url'               => '/' . ltrim($slug, '/'),
            'excerpt'           => isset($row['short_description']) ? (string) $row['short_description'] : '',
            'short_description' => isset($row['short_description']) ? (string) $row['short_description'] : '',
            'button_text'       => isset($row['button_text']) ? (string) $row['button_text'] : 'Read Full Story',
            'banner_image'      => isset($row['image']) ? (string) $row['image'] : '',
            'banner_image_url'  => $imageUrl,
            'published_at'      => isset($row['published_at']) ? (string) $row['published_at'] : '',
            'category_id'       => isset($row['category_id']) ? (int) $row['category_id'] : 0,
            'category_name'     => isset($row['category_name']) ? (string) $row['category_name'] : '',
            'category_slug'     => isset($row['category_slug']) ? (string) $row['category_slug'] : '',
            'updated_at'        => $displayDate,
        );
    }

    /**
     * @param array $row
     * @return array<string,mixed>
     */
    public function format_detail_row(array $row) {
        $list = $this->format_list_row($row);
        $html = isset($row['html_content']) ? trim((string) $row['html_content']) : '';
        $long = isset($row['long_description']) ? trim((string) $row['long_description']) : '';
        $text = isset($row['text_content']) ? trim((string) $row['text_content']) : '';
        if ($html === '' && $long !== '') {
            $html = '<p>' . nl2br(htmlspecialchars($long, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
        if ($html === '' && $text !== '') {
            $html = '<p>' . nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) . '</p>';
        }

        return array(
            'page' => array(
                'id'                => $list['id'],
                'page_name'         => $list['title'],
                'title'             => $list['title'],
                'subtitle'          => $list['subtitle'],
                'url'               => $list['url'],
                'page_type'         => 'blog',
                'updated_at'        => $list['updated_at'],
                'published_at'      => $list['published_at'],
                'short_description' => $list['short_description'],
                'long_description'  => $long,
                'text_content'      => $text,
                'button_text'       => $list['button_text'],
                'category_id'       => isset($list['category_id']) ? (int) $list['category_id'] : 0,
                'category_name'     => isset($list['category_name']) ? (string) $list['category_name'] : '',
                'category_slug'     => isset($list['category_slug']) ? (string) $list['category_slug'] : '',
            ),
            'sections'              => array(),
            'meta_tags_html'        => '',
            'content_html'          => $html,
            'excerpt'               => $list['excerpt'],
            'long_description'      => $long,
            'text_content'          => $text,
            'html_content'          => isset($row['html_content']) ? (string) $row['html_content'] : '',
            'subtitle'              => $list['subtitle'],
            'button_text'           => $list['button_text'],
            'page_banner_image_url' => $list['banner_image_url'],
            'slug'                  => $list['slug'],
            'category_id'           => isset($list['category_id']) ? (int) $list['category_id'] : 0,
            'category_name'         => isset($list['category_name']) ? (string) $list['category_name'] : '',
            'category_slug'         => isset($list['category_slug']) ? (string) $list['category_slug'] : '',
        );
    }

    /**
     * @param array $row
     * @return array<string,mixed>
     */
    protected function normalize_admin_row(array $row) {
        return array(
            'id'                => isset($row['id']) ? (int) $row['id'] : 0,
            'title'             => isset($row['title']) ? (string) $row['title'] : '',
            'subtitle'          => isset($row['subtitle']) ? (string) $row['subtitle'] : '',
            'slug'              => isset($row['slug']) ? (string) $row['slug'] : '',
            'image'             => isset($row['image']) ? (string) $row['image'] : '',
            'short_description' => isset($row['short_description']) ? (string) $row['short_description'] : '',
            'long_description'  => isset($row['long_description']) ? (string) $row['long_description'] : '',
            'html_content'      => isset($row['html_content']) ? (string) $row['html_content'] : '',
            'text_content'      => isset($row['text_content']) ? (string) $row['text_content'] : '',
            'button_text'       => isset($row['button_text']) ? (string) $row['button_text'] : 'Read Full Story',
            'status'            => isset($row['status']) ? (string) $row['status'] : 'draft',
            'sort_order'        => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
            'published_at'      => isset($row['published_at']) ? (string) $row['published_at'] : '',
            'category_id'       => isset($row['category_id']) ? (int) $row['category_id'] : 0,
            'category_name'     => isset($row['category_name']) ? (string) $row['category_name'] : '',
            'category_slug'     => isset($row['category_slug']) ? (string) $row['category_slug'] : '',
            'is_active'         => !empty($row['is_active']),
            'created_at'        => isset($row['created_at']) ? (string) $row['created_at'] : '',
            'updated_at'        => isset($row['updated_at']) ? (string) $row['updated_at'] : '',
        );
    }

    /**
     * @param string $text
     * @return string
     */
    public function slugify($text) {
        $text = strtolower(trim((string) $text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * @param string $slug
     * @param int    $excludeId
     * @return string
     */
    public function ensure_unique_slug($slug, $excludeId = 0) {
        $slug = $this->slugify($slug);
        if ($slug === '') {
            return '';
        }
        $base = $slug;
        $n = 0;
        $table = 'sma_cms_blogs';
        while (true) {
            $candidate = $n === 0 ? $base : $base . '-' . $n;
            $this->db->from($table);
            $this->db->where('slug', $candidate);
            if ((int) $excludeId > 0) {
                $this->db->where('id !=', (int) $excludeId);
            }
            if ($this->db->count_all_results() === 0) {
                return $candidate;
            }
            $n++;
            if ($n > 200) {
                return $base . '-' . time();
            }
        }
    }

    /**
     * @param string $file
     * @return string
     */
    public function build_image_url($file) {
        $file = trim((string) $file);
        if ($file === '') {
            return '';
        }
        if (strpos($file, 'http://') === 0 || strpos($file, 'https://') === 0) {
            return $file;
        }
        $customer_assets = 'localhost';
        if (isset($this->Settings->customer_assets) && $this->Settings->customer_assets !== '') {
            $customer_assets = (string) $this->Settings->customer_assets;
        }
        if (function_exists('cms_media_public_url')) {
            $this->load->helper('cms_media');
            return cms_media_public_url($file, $customer_assets);
        }
        return base_url('assets/mdata/' . $customer_assets . '/uploads/webshop/' . ltrim($file, '/'));
    }

    /**
     * Unified blog body — accepts HTML or plain text in html_content.
     *
     * @param array $data
     * @return array{html_content:string,text_content:string}
     */
    public function normalize_blog_content_fields(array $data) {
        $html = isset($data['html_content']) ? trim((string) $data['html_content']) : '';
        if ($html === '' && isset($data['text_content'])) {
            $html = trim((string) $data['text_content']);
        }
        $plain = trim(strip_tags($html));
        if ($plain === '') {
            return array('html_content' => '', 'text_content' => '');
        }
        if ($html === $plain) {
            return array(
                'html_content' => $html,
                'text_content' => $plain,
            );
        }
        return array(
            'html_content' => $html,
            'text_content' => $plain,
        );
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    public function normalize_published_at($value) {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            $raw .= ' 00:00:00';
        }
        $ts = strtotime($raw);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    /**
     * @param array $row
     * @return string
     */
    public function admin_blog_content_display(array $row) {
        $html = isset($row['html_content']) ? trim((string) $row['html_content']) : '';
        if ($html !== '') {
            return $html;
        }
        $text = isset($row['text_content']) ? trim((string) $row['text_content']) : '';
        if ($text !== '') {
            return $text;
        }
        return isset($row['long_description']) ? trim((string) $row['long_description']) : '';
    }

    /**
     * @param string $dbValue
     * @return string Y-m-d for date input
     */
    public function published_at_input_value($dbValue) {
        $dbValue = trim((string) $dbValue);
        if ($dbValue === '') {
            return '';
        }
        $ts = strtotime($dbValue);
        return $ts ? date('Y-m-d', $ts) : '';
    }
}
