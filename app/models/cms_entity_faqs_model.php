<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Per-entity FAQs (sma_cms_entity_faqs) for storefront API.
 */
class Cms_entity_faqs_model extends CI_Model
{
    /**
     * @return bool
     */
    public function ensure_table()
    {
        if ($this->db->table_exists('sma_cms_entity_faqs')) {
            return true;
        }

        $sql = 'CREATE TABLE IF NOT EXISTS `sma_cms_entity_faqs` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `entity_master_id` BIGINT UNSIGNED NOT NULL,
            `entity_id` BIGINT UNSIGNED NOT NULL,
            `category` VARCHAR(150) NOT NULL DEFAULT \'\',
            `question` TEXT NOT NULL,
            `answer` TEXT NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_cms_entity_faqs_entity` (`entity_master_id`, `entity_id`),
            KEY `idx_cms_entity_faqs_sort` (`entity_master_id`, `entity_id`, `sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        return (bool) $this->db->query($sql);
    }

    /**
     * @param int    $entity_master_id
     * @param int    $entity_id
     * @param string $entity_code optional — enables blog ID cross-lookup
     * @return array<int,array<string,mixed>>
     */
    public function list_by_entity($entity_master_id, $entity_id, $entity_code = '')
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id < 1 || $entity_id < 1 || !$this->ensure_table()) {
            return array();
        }

        $items = $this->fetch_rows($entity_master_id, $entity_id);
        $entity_code = strtolower(trim((string) $entity_code));
        if (empty($items) && $entity_code === 'blog') {
            $altId = $this->resolve_blog_faq_alternate_entity_id($entity_id);
            if ($altId > 0 && $altId !== $entity_id) {
                $items = $this->fetch_rows($entity_master_id, $altId);
            }
        }

        return $items;
    }

    /**
     * @param string $entity_code
     * @param int    $entity_id
     * @return array<int,array<string,mixed>>
     */
    public function list_for_entity_code($entity_code, $entity_id)
    {
        $entity_code = strtolower(trim((string) $entity_code));
        $entity_id = (int) $entity_id;
        if ($entity_code === '' || $entity_id < 1) {
            return array();
        }
        $entity_master_id = $this->resolve_entity_master_id($entity_code);
        if ($entity_master_id < 1) {
            return array();
        }
        return $this->list_by_entity($entity_master_id, $entity_id, $entity_code);
    }

    /**
     * Blog FAQs may be stored against sma_cms_blogs.id or matching sma_cms_pages.id.
     *
     * @param int $entity_id
     * @return int
     */
    protected function resolve_blog_faq_alternate_entity_id($entity_id)
    {
        $entity_id = (int) $entity_id;
        if ($entity_id < 1) {
            return 0;
        }

        $blogsTable = $this->db->table_exists('sma_cms_blogs') ? 'sma_cms_blogs' : '';
        $pagesTable = 'sma_cms_pages';

        if ($blogsTable !== '') {
            $blog = $this->db->select('id, slug')->from($blogsTable)->where('id', $entity_id)->get()->row_array();
            if (is_array($blog) && !empty($blog['slug']) && $pagesTable !== '') {
                $slug = trim((string) $blog['slug'], '/');
                if ($slug !== '') {
                    $this->db->reset_query();
                    $this->db->select('id', false);
                    $this->db->from($pagesTable);
                    if ($this->db->field_exists('page_type', $pagesTable)) {
                        $this->db->where('page_type', 'blog');
                    }
                    $this->db->group_start();
                    $this->db->where('url', '/' . $slug);
                    $this->db->or_where('url', '/blog/' . $slug);
                    $this->db->or_like('url', '/' . $slug, 'before');
                    $this->db->group_end();
                    $this->db->limit(1);
                    $page = $this->db->get()->row_array();
                    if (is_array($page) && !empty($page['id'])) {
                        return (int) $page['id'];
                    }
                }
            }
        }

        if ($pagesTable !== '' && $blogsTable !== '') {
            $this->db->reset_query();
            $this->db->from($pagesTable);
            $this->db->where('id', $entity_id);
            if ($this->db->field_exists('page_type', $pagesTable)) {
                $this->db->where('page_type', 'blog');
            }
            $page = $this->db->get()->row_array();
            if (is_array($page) && !empty($page['url'])) {
                $url = trim((string) $page['url'], '/');
                $parts = explode('/', $url);
                $slug = trim((string) end($parts));
                if ($slug !== '') {
                    $this->db->reset_query();
                    $blog = $this->db->select('id')->from($blogsTable)->where('slug', $slug)->get()->row_array();
                    if (is_array($blog) && !empty($blog['id'])) {
                        return (int) $blog['id'];
                    }
                }
            }
        }

        return 0;
    }

    /**
     * @param int $entity_master_id
     * @param int $entity_id
     * @return array<int,array<string,mixed>>
     */
    protected function fetch_rows($entity_master_id, $entity_id)
    {
        $q = $this->db
            ->from('sma_cms_entity_faqs')
            ->where('entity_master_id', $entity_master_id)
            ->where('entity_id', $entity_id)
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'ASC')
            ->get();

        if (!$q || $q->num_rows() === 0) {
            return array();
        }

        $rows = array();
        foreach ($q->result_array() as $row) {
            $rows[] = array(
                'id'               => isset($row['id']) ? (int) $row['id'] : 0,
                'entity_master_id' => $entity_master_id,
                'entity_id'        => $entity_id,
                'category'         => isset($row['category']) ? (string) $row['category'] : '',
                'question'         => isset($row['question']) ? (string) $row['question'] : '',
                'answer'           => isset($row['answer']) ? (string) $row['answer'] : '',
                'sort_order'       => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
            );
        }

        return $rows;
    }

    /**
     * @param string $entity_code
     * @param int    $entity_id
     * @return int
     */
    public function resolve_entity_master_id($entity_code)
    {
        $entity_code = strtolower(trim((string) $entity_code));
        if ($entity_code === '') {
            return 0;
        }

        if (!$this->db->table_exists('sma_cms_entities_master')) {
            return 0;
        }

        $row = $this->db
            ->select('id')
            ->from('sma_cms_entities_master')
            ->where('entity_code', $entity_code)
            ->get()
            ->row_array();

        return is_array($row) && !empty($row['id']) ? (int) $row['id'] : 0;
    }
}
