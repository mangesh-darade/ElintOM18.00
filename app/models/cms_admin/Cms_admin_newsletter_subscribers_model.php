<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/cms_admin/Cms_admin_base_model.php';

/**
 * Footer / webshop newsletter signups (sma_cms_newsletter_subscriber).
 */
class Cms_admin_newsletter_subscribers_model extends Cms_admin_base_model
{
    /**
     * @return bool
     */
    public function ensure_table()
    {
        $this->load->model('webshop_api_model');
        if (method_exists($this->webshop_api_model, 'ensure_newsletter_subscriber_table')) {
            return (bool) $this->webshop_api_model->ensure_newsletter_subscriber_table();
        }
        return $this->db->table_exists('sma_cms_newsletter_subscriber');
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function list_subscribers($limit = 5000)
    {
        if (!$this->ensure_table() || !$this->db->table_exists('sma_cms_newsletter_subscriber')) {
            return array();
        }

        $this->db
            ->from('sma_cms_newsletter_subscriber')
            ->order_by('created_at', 'DESC')
            ->order_by('id', 'DESC');

        if ($limit > 0) {
            $this->db->limit((int) $limit);
        }

        $q = $this->db->get();
        if (!$q || $q->num_rows() === 0) {
            return array();
        }

        $rows = array();
        foreach ($q->result_array() as $row) {
            $rows[] = array(
                'id'         => isset($row['id']) ? (int) $row['id'] : 0,
                'email'      => isset($row['email']) ? trim((string) $row['email']) : '',
                'source'     => isset($row['source']) ? trim((string) $row['source']) : '',
                'status'     => isset($row['status']) ? trim((string) $row['status']) : '',
                'ip_address' => isset($row['ip_address']) ? trim((string) $row['ip_address']) : '',
                'created_at' => isset($row['created_at']) ? trim((string) $row['created_at']) : '',
            );
        }
        return $rows;
    }
}
