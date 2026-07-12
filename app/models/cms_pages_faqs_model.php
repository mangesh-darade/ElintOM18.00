<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Per-page FAQs (sma_cms_pages_faqs) for storefront API.
 */
class Cms_pages_faqs_model extends CI_Model {

    /**
     * @return bool
     */
    public function ensure_table()
    {
        if ($this->db->table_exists('sma_cms_pages_faqs')) {
            return true;
        }

        $sql = 'CREATE TABLE IF NOT EXISTS `sma_cms_pages_faqs` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `page_id` BIGINT UNSIGNED NOT NULL,
            `category` VARCHAR(150) NOT NULL DEFAULT \'\',
            `question` TEXT NOT NULL,
            `answer` TEXT NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_cms_pages_faqs_page` (`page_id`),
            KEY `idx_cms_pages_faqs_sort` (`page_id`, `sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        return (bool) $this->db->query($sql);
    }

    /**
     * @param int $page_id
     * @return array<int,array<string,mixed>>
     */
    public function list_by_page_id($page_id)
    {
        $page_id = (int) $page_id;
        if ($page_id < 1 || !$this->ensure_table()) {
            return array();
        }

        $q = $this->db
            ->from('sma_cms_pages_faqs')
            ->where('page_id', $page_id)
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'ASC')
            ->get();

        if (!$q || $q->num_rows() === 0) {
            return array();
        }

        $rows = array();
        foreach ($q->result_array() as $row) {
            $rows[] = array(
                'id'         => isset($row['id']) ? (int) $row['id'] : 0,
                'page_id'    => isset($row['page_id']) ? (int) $row['page_id'] : 0,
                'category'   => isset($row['category']) ? (string) $row['category'] : '',
                'question'   => isset($row['question']) ? (string) $row['question'] : '',
                'answer'     => isset($row['answer']) ? (string) $row['answer'] : '',
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
            );
        }

        return $rows;
    }
}
