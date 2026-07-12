<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CMS blog categories (sma_cms_blog_categories).
 */
class Cms_blog_categories_model extends CI_Model {

    /**
     * @return bool
     */
    public function ensure_table() {
        if ($this->db->table_exists('sma_cms_blog_categories')) {
            return true;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `sma_cms_blog_categories` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_cms_blog_categories_slug` (`slug`),
            KEY `idx_cms_blog_categories_active` (`is_active`),
            KEY `idx_cms_blog_categories_sort` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return (bool) $this->db->query($sql);
    }

    /**
     * @param bool $activeOnly
     * @return array<int,array<string,mixed>>
     */
    public function list_all($activeOnly = false) {
        if (!$this->ensure_table()) {
            return array();
        }
        $this->db->from('sma_cms_blog_categories');
        if ($activeOnly) {
            $this->db->where('is_active', 1);
        }
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('name', 'ASC');
        $q = $this->db->get();
        if (!$q || $q->num_rows() === 0) {
            return array();
        }
        $rows = array();
        foreach ($q->result_array() as $row) {
            $rows[] = $this->normalize_row($row);
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
        $q = $this->db->get_where('sma_cms_blog_categories', array('id' => $id), 1);
        return ($q && $q->num_rows() > 0) ? $this->normalize_row($q->row_array()) : null;
    }

    /**
     * @param array $data
     * @return array{ok:bool,id:int,message:string}
     */
    public function save(array $data) {
        if (!$this->ensure_table()) {
            return array('ok' => false, 'id' => 0, 'message' => 'Category table could not be created.');
        }
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        if ($name === '') {
            return array('ok' => false, 'id' => $id, 'message' => 'Category name is required.');
        }
        $slug = isset($data['slug']) ? trim((string) $data['slug']) : '';
        if ($slug === '') {
            $slug = $this->slugify($name);
        } else {
            $slug = $this->slugify($slug);
        }
        $slug = $this->ensure_unique_slug($slug, $id);
        if ($slug === '') {
            return array('ok' => false, 'id' => $id, 'message' => 'Could not generate category slug.');
        }

        $row = array(
            'name'        => $name,
            'slug'        => $slug,
            'description' => isset($data['description']) ? trim((string) $data['description']) : '',
            'sort_order'  => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            'is_active'   => !empty($data['is_active']) ? 1 : 0,
        );
        $table = 'sma_cms_blog_categories';
        if ($id > 0) {
            $this->db->where('id', $id)->update($table, $row);
            return array('ok' => true, 'id' => $id, 'message' => 'Category updated.');
        }
        $this->db->insert($table, $row);
        $newId = (int) $this->db->insert_id();
        return array(
            'ok'      => $newId > 0,
            'id'      => $newId,
            'message' => $newId > 0 ? 'Category created.' : 'Insert failed.',
        );
    }

    /**
     * @param int $id
     * @return array{ok:bool,message:string}
     */
    public function delete($id) {
        $id = (int) $id;
        if ($id < 1 || !$this->db->table_exists('sma_cms_blog_categories')) {
            return array('ok' => false, 'message' => 'Category not found.');
        }
        $this->db->where('id', $id)->delete('sma_cms_blog_categories');
        return array('ok' => $this->db->affected_rows() > 0, 'message' => 'Category deleted.');
    }

    /**
     * @param array<int,int> $ids
     * @return array<int,array<string,mixed>>
     */
    public function map_by_ids(array $ids) {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids) || !$this->ensure_table()) {
            return array();
        }
        $q = $this->db->where_in('id', $ids)->get('sma_cms_blog_categories');
        if (!$q || $q->num_rows() === 0) {
            return array();
        }
        $map = array();
        foreach ($q->result_array() as $row) {
            $norm = $this->normalize_row($row);
            $map[(int) $norm['id']] = $norm;
        }
        return $map;
    }

    /**
     * @param array $row
     * @return array<string,mixed>
     */
    public function normalize_row(array $row) {
        return array(
            'id'          => isset($row['id']) ? (int) $row['id'] : 0,
            'name'        => isset($row['name']) ? (string) $row['name'] : '',
            'slug'        => isset($row['slug']) ? (string) $row['slug'] : '',
            'description' => isset($row['description']) ? (string) $row['description'] : '',
            'sort_order'  => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
            'is_active'   => !empty($row['is_active']),
            'created_at'  => isset($row['created_at']) ? (string) $row['created_at'] : '',
            'updated_at'  => isset($row['updated_at']) ? (string) $row['updated_at'] : '',
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
        $table = 'sma_cms_blog_categories';
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
}
