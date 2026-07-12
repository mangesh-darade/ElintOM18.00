<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * Per-page FAQs (sma_cms_pages_faqs) — category, question, answer rows tied to a CMS page.
 */
class Cms_admin_pages_faqs_model extends Cms_admin_base_model
{
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

        if (!(bool) $this->db->query($sql)) {
            return false;
        }

        return $this->db->table_exists('sma_cms_pages_faqs');
    }

    /**
     * @param int $page_id
     * @return array<int,array<string,mixed>>
     */
    public function getFaqsByPageId($page_id)
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
            $rows[] = $this->normalize_row($row);
        }
        return $rows;
    }

    /**
     * Replace FAQ rows for a page from admin form POST data.
     *
     * @param int   $page_id
     * @param array $rows each: id?, category, question, answer
     * @return array{ok:bool,saved:int,message:string}
     */
    public function saveFaqsForPage($page_id, array $rows)
    {
        $page_id = (int) $page_id;
        if ($page_id < 1) {
            return array('ok' => false, 'saved' => 0, 'message' => 'Invalid page.');
        }
        if (!$this->ensure_table()) {
            return array('ok' => false, 'saved' => 0, 'message' => 'FAQ table could not be created.');
        }

        $table = 'sma_cms_pages_faqs';
        $kept_ids = array();
        $saved = 0;
        $sort = 0;

        $this->db->trans_start();

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $question = isset($row['question']) ? trim((string) $row['question']) : '';
            $answer = isset($row['answer']) ? trim((string) $row['answer']) : '';
            if ($question === '' && $answer === '') {
                continue;
            }
            if ($question === '') {
                continue;
            }

            $sort++;
            $payload = array(
                'page_id'    => $page_id,
                'category'   => isset($row['category']) ? trim((string) $row['category']) : '',
                'question'   => $question,
                'answer'     => $answer,
                'sort_order' => $sort,
            );

            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id > 0) {
                $check = $this->db->get_where($table, array('id' => $id, 'page_id' => $page_id), 1);
                if ($check && $check->num_rows() > 0) {
                    $this->db->where('id', $id)->where('page_id', $page_id)->update($table, $payload);
                    $kept_ids[] = $id;
                    $saved++;
                    continue;
                }
            }

            $this->db->insert($table, $payload);
            $new_id = (int) $this->db->insert_id();
            if ($new_id > 0) {
                $kept_ids[] = $new_id;
                $saved++;
            }
        }

        if (!empty($kept_ids)) {
            $this->db->where('page_id', $page_id);
            $this->db->where_not_in('id', $kept_ids);
            $this->db->delete($table);
        } else {
            $this->db->where('page_id', $page_id)->delete($table);
        }

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return array('ok' => false, 'saved' => 0, 'message' => 'Failed to save FAQs.');
        }

        return array(
            'ok'      => true,
            'saved'   => $saved,
            'message' => $saved > 0 ? ($saved . ' FAQ row(s) saved.') : 'FAQs cleared for this page.',
        );
    }

    /**
     * @param int $id
     * @param int $page_id
     * @return bool
     */
    public function deleteFaq($id, $page_id)
    {
        $id = (int) $id;
        $page_id = (int) $page_id;
        if ($id < 1 || $page_id < 1 || !$this->db->table_exists('sma_cms_pages_faqs')) {
            return false;
        }
        $this->db->where('id', $id)->where('page_id', $page_id);
        return (bool) $this->db->delete('sma_cms_pages_faqs');
    }

    /**
     * @param array $row
     * @return array<string,mixed>
     */
    protected function normalize_row(array $row)
    {
        return array(
            'id'         => isset($row['id']) ? (int) $row['id'] : 0,
            'page_id'    => isset($row['page_id']) ? (int) $row['page_id'] : 0,
            'category'   => isset($row['category']) ? (string) $row['category'] : '',
            'question'   => isset($row['question']) ? (string) $row['question'] : '',
            'answer'     => isset($row['answer']) ? (string) $row['answer'] : '',
            'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
        );
    }
}
