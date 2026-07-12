<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * Per-entity FAQs (sma_cms_entity_faqs) — category, question, answer rows tied to an entity.
 */
class Cms_admin_entity_faqs_model extends Cms_admin_base_model
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

        if (!(bool) $this->db->query($sql)) {
            return false;
        }

        return $this->db->table_exists('sma_cms_entity_faqs');
    }

    /**
     * @param int $entity_master_id
     * @param int $entity_id
     * @return array<int,array<string,mixed>>
     */
    public function getFaqsByEntity($entity_master_id, $entity_id)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id < 1 || $entity_id < 1 || !$this->ensure_table()) {
            return array();
        }

        $entity_code = $this->resolve_entity_code_by_master_id($entity_master_id);
        $items = $this->fetch_faq_rows($entity_master_id, $entity_id);
        if (empty($items) && $entity_code === 'blog') {
            $altId = $this->resolve_blog_alternate_entity_id($entity_id);
            if ($altId > 0 && $altId !== $entity_id) {
                $items = $this->fetch_faq_rows($entity_master_id, $altId);
            }
        }

        $rows = array();
        foreach ($items as $row) {
            $rows[] = $this->normalize_row($row);
        }
        return $rows;
    }

    /**
     * @param int   $entity_master_id
     * @param int   $entity_id
     * @param array $rows each: id?, category, question, answer
     * @return array{ok:bool,saved:int,message:string}
     */
    public function saveFaqsForEntity($entity_master_id, $entity_id, array $rows)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id < 1 || $entity_id < 1) {
            return array('ok' => false, 'saved' => 0, 'message' => 'Invalid entity selection.');
        }
        if (!$this->ensure_table()) {
            return array('ok' => false, 'saved' => 0, 'message' => 'FAQ table could not be created.');
        }

        $table = 'sma_cms_entity_faqs';
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
                'entity_master_id' => $entity_master_id,
                'entity_id'        => $entity_id,
                'category'         => isset($row['category']) ? trim((string) $row['category']) : '',
                'question'         => $question,
                'answer'           => $answer,
                'sort_order'       => $sort,
            );

            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id > 0) {
                $check = $this->db->get_where($table, array(
                    'id'               => $id,
                    'entity_master_id' => $entity_master_id,
                    'entity_id'        => $entity_id,
                ), 1);
                if ($check && $check->num_rows() > 0) {
                    $this->db->where('id', $id)
                        ->where('entity_master_id', $entity_master_id)
                        ->where('entity_id', $entity_id)
                        ->update($table, $payload);
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
            $this->db->where('entity_master_id', $entity_master_id);
            $this->db->where('entity_id', $entity_id);
            $this->db->where_not_in('id', $kept_ids);
            $this->db->delete($table);
        } else {
            $this->db->where('entity_master_id', $entity_master_id);
            $this->db->where('entity_id', $entity_id);
            $this->db->delete($table);
        }

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return array('ok' => false, 'saved' => 0, 'message' => 'Failed to save FAQs.');
        }

        return array(
            'ok'      => true,
            'saved'   => $saved,
            'message' => $saved > 0 ? ($saved . ' FAQ row(s) saved.') : 'FAQs cleared for this entity.',
        );
    }

    /**
     * @param int $entity_master_id
     * @param int $entity_id
     * @return bool
     */
    public function deleteFaqsForEntity($entity_master_id, $entity_id)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id < 1 || $entity_id < 1 || !$this->db->table_exists('sma_cms_entity_faqs')) {
            return false;
        }
        $this->db->where('entity_master_id', $entity_master_id);
        $this->db->where('entity_id', $entity_id);
        return (bool) $this->db->delete('sma_cms_entity_faqs');
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function get_faq_list()
    {
        if (!$this->ensure_table()) {
            return array();
        }

        $table = 'sma_cms_entity_faqs';
        $master = 'sma_cms_entities_master';
        $products = 'products';
        $categories = 'categories';
        $pages = 'sma_cms_pages';
        $blogsTable = $this->db->table_exists('sma_cms_blogs') ? 'sma_cms_blogs' : '';
        $product_name_expr = $this->product_name_expr($products, 'p');

        $select = 'ef.entity_master_id, ef.entity_id, MIN(ef.id) AS id, COUNT(ef.id) AS total_faqs,'
            . ' MIN(ef.created_at) AS created_at, em.entity_name, em.entity_code,'
            . ' ' . $product_name_expr . ' AS product_name, c.name AS category_name';
        if ($blogsTable !== '' && $this->db->field_exists('title', $blogsTable)) {
            if ($this->db->table_exists($pages) && $this->db->field_exists('page_name', $pages)) {
                $select .= ', COALESCE(cb.title, pg.page_name) AS blog_name';
            } else {
                $select .= ', cb.title AS blog_name';
            }
        } elseif ($this->db->table_exists($pages) && $this->db->field_exists('page_name', $pages)) {
            $select .= ', pg.page_name AS blog_name';
        } else {
            $select .= ', NULL AS blog_name';
        }

        $this->db->select($select, false);
        $this->db->from($table . ' ef');
        $this->db->join($master . ' em', 'em.id = ef.entity_master_id', 'left');
        $this->db->join($products . ' p', 'p.id = ef.entity_id AND em.entity_code = "product"', 'left');
        $this->db->join($categories . ' c', 'c.id = ef.entity_id AND em.entity_code = "category"', 'left');
        if ($blogsTable !== '') {
            $this->db->join($blogsTable . ' cb', 'cb.id = ef.entity_id AND em.entity_code = "blog"', 'left');
        }
        if ($this->db->table_exists($pages)) {
            $this->db->join($pages . ' pg', 'pg.id = ef.entity_id AND em.entity_code = "blog"', 'left');
        }
        $this->db->group_by(array('ef.entity_master_id', 'ef.entity_id'));
        $this->db->order_by('id', 'DESC');
        $q = $this->db->get();

        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * @param array $row list row
     * @return string
     */
    public function entity_label_from_list_row(array $row)
    {
        $code = isset($row['entity_code']) ? strtolower((string) $row['entity_code']) : '';
        if ($code === 'product' && !empty($row['product_name'])) {
            return (string) $row['product_name'];
        }
        if ($code === 'category' && !empty($row['category_name'])) {
            return (string) $row['category_name'];
        }
        if ($code === 'blog' && !empty($row['blog_name'])) {
            return (string) $row['blog_name'];
        }
        $entity_id = isset($row['entity_id']) ? (int) $row['entity_id'] : 0;
        return $entity_id > 0 ? ('Entity #' . $entity_id) : '-';
    }

    /**
     * @param int $entity_master_id
     * @return string
     */
    protected function resolve_entity_code_by_master_id($entity_master_id)
    {
        $entity_master_id = (int) $entity_master_id;
        if ($entity_master_id < 1) {
            return '';
        }
        $master = $this->db
            ->select('entity_code')
            ->from('sma_cms_entities_master')
            ->where('id', $entity_master_id)
            ->get()
            ->row_array();
        return is_array($master) && !empty($master['entity_code'])
            ? strtolower(trim((string) $master['entity_code']))
            : '';
    }

    /**
     * @param int $entity_master_id
     * @param int $entity_id
     * @return array<int,array<string,mixed>>
     */
    protected function fetch_faq_rows($entity_master_id, $entity_id)
    {
        $entity_master_id = (int) $entity_master_id;
        $entity_id = (int) $entity_id;
        if ($entity_master_id < 1 || $entity_id < 1) {
            return array();
        }

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
     * Cross-lookup between sma_cms_blogs.id and sma_cms_pages.id for blog FAQs.
     *
     * @param int $entity_id
     * @return int
     */
    protected function resolve_blog_alternate_entity_id($entity_id)
    {
        $entity_id = (int) $entity_id;
        if ($entity_id < 1) {
            return 0;
        }

        $blogsTable = 'sma_cms_blogs';
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
     * @param array $row
     * @return array<string,mixed>
     */
    protected function normalize_row(array $row)
    {
        return array(
            'id'               => isset($row['id']) ? (int) $row['id'] : 0,
            'entity_master_id' => isset($row['entity_master_id']) ? (int) $row['entity_master_id'] : 0,
            'entity_id'        => isset($row['entity_id']) ? (int) $row['entity_id'] : 0,
            'category'         => isset($row['category']) ? (string) $row['category'] : '',
            'question'         => isset($row['question']) ? (string) $row['question'] : '',
            'answer'           => isset($row['answer']) ? (string) $row['answer'] : '',
            'sort_order'       => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
        );
    }

    /**
     * @param string $products_table
     * @param string $alias
     * @return string
     */
    private function product_name_expr($products_table, $alias = '')
    {
        $prefix = $alias !== '' ? $alias . '.' : '';
        $has_product_name = $this->db->field_exists('product_name', $products_table);
        $has_name = $this->db->field_exists('name', $products_table);

        if ($has_product_name && $has_name) {
            return 'COALESCE(' . $prefix . 'product_name, ' . $prefix . 'name)';
        }
        if ($has_product_name) {
            return $prefix . 'product_name';
        }
        if ($has_name) {
            return $prefix . 'name';
        }
        return "''";
    }
}
