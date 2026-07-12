<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CMS testimonials (sma_cms_testimonials) for storefront API and CMS admin.
 */
class Cms_testimonials_model extends CI_Model {

    /**
     * @return bool
     */
    public function ensure_table() {
        if ($this->db->table_exists('sma_cms_testimonials')) {
            return true;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `sma_cms_testimonials` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `person_name` VARCHAR(255) NOT NULL,
            `photo` VARCHAR(255) NULL DEFAULT NULL,
            `comments` TEXT NOT NULL,
            `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_by` INT NULL DEFAULT NULL,
            `updated_by` INT NULL DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `idx_cms_testimonials_status` (`status`),
            KEY `idx_cms_testimonials_active` (`is_active`),
            KEY `idx_cms_testimonials_sort` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return (bool) $this->db->query($sql);
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return array{items: array, total_items: int, page: int, per_page: int, total_pages: int}
     */
    public function list_published($page = 1, $perPage = 12) {
        $empty = array('items' => array(), 'total_items' => 0, 'page' => 1, 'per_page' => 12, 'total_pages' => 1);
        if (!$this->ensure_table()) {
            return $empty;
        }
        $page = max(1, (int) $page);
        $perPage = max(1, min(100, (int) $perPage));
        $table = 'sma_cms_testimonials';

        $this->db->from($table);
        $this->db->where('status', 'published');
        $this->db->where('is_active', 1);
        $total = (int) $this->db->count_all_results();

        $this->db->from($table);
        $this->db->where('status', 'published');
        $this->db->where('is_active', 1);
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('updated_at', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit($perPage, ($page - 1) * $perPage);
        $q = $this->db->get();

        $items = array();
        if ($q && $q->num_rows() > 0) {
            foreach ($q->result_array() as $row) {
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
     * @param int $limit
     * @return array<int,array<string,mixed>>
     */
    public function list_all_admin($limit = 5000) {
        if (!$this->ensure_table()) {
            return array();
        }
        $this->db->from('sma_cms_testimonials');
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('updated_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit((int) $limit);
        }
        $q = $this->db->get();
        if (!$q || $q->num_rows() === 0) {
            return array();
        }
        $rows = array();
        foreach ($q->result_array() as $row) {
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
        $q = $this->db->get_where('sma_cms_testimonials', array('id' => $id), 1);
        if (!$q || $q->num_rows() === 0) {
            return null;
        }
        return $this->normalize_admin_row($q->row_array());
    }

    /**
     * @param array $data
     * @param int   $userId
     * @return array{ok:bool,id:int,message:string}
     */
    public function save(array $data, $userId = 0) {
        if (!$this->ensure_table()) {
            return array('ok' => false, 'id' => 0, 'message' => 'Testimonials table could not be created.');
        }
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $personName = isset($data['person_name']) ? trim((string) $data['person_name']) : '';
        if ($personName === '') {
            return array('ok' => false, 'id' => $id, 'message' => 'Person name is required.');
        }
        $comments = isset($data['comments']) ? trim((string) $data['comments']) : '';
        if ($comments === '') {
            return array('ok' => false, 'id' => $id, 'message' => 'Comments are required.');
        }

        $status = isset($data['status']) ? strtolower(trim((string) $data['status'])) : 'draft';
        if (!in_array($status, array('draft', 'published'), true)) {
            $status = 'draft';
        }

        $row = array(
            'person_name' => $personName,
            'photo'       => isset($data['photo']) ? trim((string) $data['photo']) : '',
            'comments'    => $comments,
            'status'      => $status,
            'sort_order'  => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            'is_active'   => !empty($data['is_active']) ? 1 : 0,
            'updated_by'  => (int) $userId > 0 ? (int) $userId : null,
        );

        $table = 'sma_cms_testimonials';
        if ($id > 0) {
            $this->db->where('id', $id)->update($table, $row);
            return array('ok' => true, 'id' => $id, 'message' => 'Testimonial updated.');
        }

        $row['created_by'] = (int) $userId > 0 ? (int) $userId : null;
        $this->db->insert($table, $row);
        $newId = (int) $this->db->insert_id();
        return array(
            'ok'      => $newId > 0,
            'id'      => $newId,
            'message' => $newId > 0 ? 'Testimonial created.' : 'Insert failed.',
        );
    }

    /**
     * @param int $id
     * @return array{ok:bool,message:string}
     */
    public function delete($id) {
        $id = (int) $id;
        if ($id < 1 || !$this->db->table_exists('sma_cms_testimonials')) {
            return array('ok' => false, 'message' => 'Testimonial not found.');
        }
        $this->db->where('id', $id)->delete('sma_cms_testimonials');
        return array('ok' => $this->db->affected_rows() > 0, 'message' => 'Testimonial deleted.');
    }

    /**
     * @param array $row
     * @return array<string,mixed>
     */
    public function format_list_row(array $row) {
        $photoUrl = $this->build_photo_url(isset($row['photo']) ? $row['photo'] : '');
        return array(
            'id'           => isset($row['id']) ? (int) $row['id'] : 0,
            'person_name'  => isset($row['person_name']) ? (string) $row['person_name'] : '',
            'comments'     => isset($row['comments']) ? (string) $row['comments'] : '',
            'photo'        => isset($row['photo']) ? (string) $row['photo'] : '',
            'photo_url'    => $photoUrl,
            'updated_at'   => isset($row['updated_at']) ? (string) $row['updated_at'] : '',
        );
    }

    /**
     * @param array $row
     * @return array<string,mixed>
     */
    protected function normalize_admin_row(array $row) {
        return array(
            'id'          => isset($row['id']) ? (int) $row['id'] : 0,
            'person_name' => isset($row['person_name']) ? (string) $row['person_name'] : '',
            'photo'       => isset($row['photo']) ? (string) $row['photo'] : '',
            'comments'    => isset($row['comments']) ? (string) $row['comments'] : '',
            'status'      => isset($row['status']) ? (string) $row['status'] : 'draft',
            'sort_order'  => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
            'is_active'   => !empty($row['is_active']),
            'created_at'  => isset($row['created_at']) ? (string) $row['created_at'] : '',
            'updated_at'  => isset($row['updated_at']) ? (string) $row['updated_at'] : '',
        );
    }

    /**
     * @param string $file
     * @return string
     */
    public function build_photo_url($file) {
        $file = trim((string) $file);
        if ($file === '') {
            return '';
        }
        if (strpos($file, 'http://') === 0 || strpos($file, 'https://') === 0) {
            return $file;
        }
        if (function_exists('cms_media_public_url')) {
            $this->load->helper('cms_media');
            $customer_assets = 'localhost';
            if (isset($this->Settings->customer_assets) && $this->Settings->customer_assets !== '') {
                $customer_assets = (string) $this->Settings->customer_assets;
            }
            return cms_media_public_url($file, $customer_assets);
        }
        return base_url('assets/mdata/localhost/uploads/webshop/cms_pages/' . ltrim($file, '/'));
    }
}
