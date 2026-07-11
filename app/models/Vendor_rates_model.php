<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vendor_rates_model extends CI_Model
{
    /**
     * Dropdown: Vendors (suppliers) whose location is Vendor-type warehouse.
     */
    public function get_vendor_suppliers()
    {
        $companies = $this->db->dbprefix('companies');
        $warehouses = $this->db->dbprefix('warehouses');
        $location_type = $this->db->dbprefix('location_type');

        $this->db->select('c.id, c.company', FALSE);
        $this->db->from($companies . ' c');
        $this->db->join($warehouses . ' w', 'w.id = c.location_id', 'inner');
        $this->db->join($location_type . ' lt', 'lt.id = w.location_type', 'left');
        $this->db->where('c.group_name', 'supplier');
        $this->db->where('lt.type', 'Vendor');
        $this->db->order_by('c.company', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Dropdown: Job Works
     */
    public function get_job_works()
    {
        $this->db->select('id, items', FALSE);
        $this->db->from('standard_job_works');
        $this->db->order_by('items', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Dropdown: Products
     */
    public function get_products()
    {
        $this->db->select('id, name', FALSE);
        $this->db->from('products');
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Dependent dropdown: Product variants by product_id
     */
    public function get_variants_by_product($product_id)
    {
        $product_id = (int) $product_id;
        if ($product_id <= 0) {
            return array();
        }
        $this->db->select('id, name', FALSE);
        $this->db->from('product_variants');
        $this->db->where('product_id', $product_id);
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Create vendor rate row.
     * Enforces uniqueness by (vendor, job_work, product_id, variant_id).
     * If exists, updates rate_per_item instead of insert.
     *
     * @return array [status(bool), id(int|null), message(string)]
     */
    public function upsert_vendor_rate($vendor_id, $job_work_id, $product_id, $variant_id, $rate_per_item)
    {
        $vendor_id = (int) $vendor_id;
        $job_work_id = (int) $job_work_id;
        $product_id = (int) $product_id;
        $variant_id = (int) $variant_id; // 0 allowed (NA)

        $this->db->select('id');
        $this->db->from('vendor_rates');
        $this->db->where(array(
            'vendor' => $vendor_id,
            'job_work' => $job_work_id,
            'product_id' => $product_id,
            'variant_id' => $variant_id,
        ));
        $this->db->limit(1);
        $existing = $this->db->get()->row();

        if ($existing && !empty($existing->id)) {
            $this->db->where('id', (int) $existing->id);
            $ok = $this->db->update('vendor_rates', array('rate_per_item' => $rate_per_item));
            return array(
                'status' => (bool) $ok,
                'id' => (int) $existing->id,
                'message' => $ok ? 'Rate updated' : 'Rate not updated'
            );
        }

        $data = array(
            'vendor' => $vendor_id,
            'job_work' => $job_work_id,
            'product_id' => $product_id,
            'variant_id' => $variant_id,
            'rate_per_item' => $rate_per_item,
            'created_at' => date('Y-m-d H:i:s'),
        );

        $ok = $this->db->insert('vendor_rates', $data);
        return array(
            'status' => (bool) $ok,
            'id' => $ok ? (int) $this->db->insert_id() : null,
            'message' => $ok ? 'Rate created' : 'Rate not created'
        );
    }

    /**
     * Fetch vendor rate rows for the grid.
     * - LEFT JOIN products + variants
     * - Handle NULL variant_id by returning variant_name = 'NA' in PHP
     */
    public function get_all_rates()
    {
        $vr = $this->db->dbprefix('vendor_rates');
        $companies = $this->db->dbprefix('companies');
        $products = $this->db->dbprefix('products');
        // Product-wise variants table
        $variants = $this->db->dbprefix('product_variants');
        $job_works = $this->db->dbprefix('standard_job_works');
        $warehouses = $this->db->dbprefix('warehouses');
        $location_type = $this->db->dbprefix('location_type');

        $this->db->select("
            vr.id,
            vr.vendor as vendor_id,
            c.company as vendor,
            vr.job_work,
            jw.items as job_work_items,
            vr.product_id,
            p.name as product_name,
            vr.variant_id,
            pv.name as variant_name,
            vr.rate_per_item
        ", FALSE);

        $this->db->from($vr . " vr");
        $this->db->join($companies . " c", "c.id = vr.vendor", "left");
        // Only show suppliers having location_id mapped to a warehouse whose location_type is Vendor
        $this->db->join($warehouses . " w", "w.id = c.location_id", "inner");
        $this->db->join($location_type . " lt", "lt.id = w.location_type", "left");
        $this->db->join($job_works . " jw", "jw.id = vr.job_work", "left");
        $this->db->join($products . " p", "p.id = vr.product_id", "left");
        $this->db->join($variants . " pv", "pv.id = vr.variant_id", "left");

        $this->db->where('c.group_name', 'supplier');
        $this->db->where('lt.type', 'Vendor');

        $this->db->order_by('c.company', 'ASC');
        $this->db->order_by('jw.items', 'ASC');
        $this->db->order_by('p.name', 'ASC');
        $this->db->order_by('pv.name', 'ASC');

        $rows = $this->db->get()->result();

        // PHP fallback for NULL variants.
        foreach ($rows as $row) {
            if (empty($row->variant_id) || (int)$row->variant_id === 0 || empty($row->variant_name)) {
                $row->variant_name = 'NA';
            }
            if (empty($row->job_work) || empty($row->job_work_items)) {
                $row->job_work_items = 'NA';
            }
        }

        return $rows;
    }

    /**
     * Update rate_per_item for a single vendor_rates row.
     *
     * @param int $id
     * @param float $rate
     * @return bool
     */
    public function update_rate($id, $rate)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return FALSE;
        }

        $exists = $this->db->select('id')
            ->from('vendor_rates')
            ->where('id', $id)
            ->limit(1)
            ->get()
            ->row();

        if (!$exists) {
            return FALSE;
        }

        $this->db->where('id', $id);
        $ok = $this->db->update('vendor_rates', array('rate_per_item' => $rate));
        return (bool) $ok;
    }
}

