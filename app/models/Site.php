<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->setSettings();
    }

    // public function get_total_qty_alerts() {
    //     $this->db->where('quantity < alert_quantity', NULL, FALSE)->where('track_quantity', 1);
    //     return $this->db->count_all_results('products');
    // }
    public function get_total_qty_alerts() {
        // Count products/variants with low stock (matching quantity alerts page logic)
        // Counts products and variants separately, then adds them together
        // Example: 10 products + 5 variants = 15 total alerts
        
        // Count products (without variants or where variant_id = 0)
        $this->db->select('COUNT(DISTINCT sma_products.id) as product_count', FALSE)
            ->from('sma_products')
            ->join('sma_product_variants', 'sma_product_variants.product_id = sma_products.id AND sma_product_variants.group_id = 1', 'left')
            ->join('sma_warehouses_products_variants', 'sma_warehouses_products_variants.option_id = sma_product_variants.id', 'left')
            ->join('sma_warehouses_products', 'sma_warehouses_products.product_id = sma_products.id', 'left')
            ->where('sma_products.track_quantity', 1)
            ->where('sma_products.alert_quantity >', 0)
            ->where('IFNULL(sma_product_variants.id, 0) =', 0);
            // ->where('(COALESCE(sma_warehouses_products.quantity, sma_products.quantity, 0) <= sma_products.alert_quantity)', NULL, FALSE);
        
        $q_products = $this->db->get();
        $product_count = ($q_products->num_rows() > 0) ? (int) $q_products->row()->product_count : 0;
        
        // Count variants (where variant_id > 0)
        $this->db->select('COUNT(DISTINCT sma_product_variants.id) as variant_count', FALSE)
            ->from('sma_products')
            ->join('sma_product_variants', 'sma_product_variants.product_id = sma_products.id AND sma_product_variants.group_id = 1', 'inner')
            ->join('sma_warehouses_products_variants', 'sma_warehouses_products_variants.option_id = sma_product_variants.id', 'left')
            ->join('sma_warehouses_products', 'sma_warehouses_products.product_id = sma_products.id', 'left')
            ->where('sma_products.track_quantity', 1)
            ->where('sma_products.alert_quantity >', 0)
            ->where('sma_product_variants.id >', 0);
            // ->where('(COALESCE(sma_warehouses_products_variants.quantity, sma_warehouses_products.quantity, sma_product_variants.quantity, sma_products.quantity, 0) <= sma_products.alert_quantity)', NULL, FALSE);
        
        $q_variants = $this->db->get();
        $variant_count = ($q_variants->num_rows() > 0) ? (int) $q_variants->row()->variant_count : 0;
        
        // Total = products + variants
        return $product_count + $variant_count;
    }

    // public function get_expiring_qty_alerts() {
    //     $date = date('Y-m-d', strtotime('+3 months'));
    //     $this->db->select('SUM(quantity_balance) as alert_num')
    //             ->where('expiry !=', NULL)->where('expiry !=', '0000-00-00')
    //             ->where('expiry <', $date);
    //     $q = $this->db->get('purchase_items');
    //     if ($q->num_rows() > 0) {
    //         $res = $q->row();
    //         return (INT) $res->alert_num;
    //     }
    //     return FALSE;
    // }
    public function get_expiring_qty_alerts(){
        $date = date('Y-m-d', strtotime('+3 months'));
        $this->db->select('COUNT(*) AS total', FALSE)
            ->from('sma_purchase_items')
            ->where('expiry IS NOT NULL', NULL, FALSE)
            ->where('expiry !=', '0000-00-00')
            ->where('expiry <', $date);

        $q = $this->db->get();
        return (int) $q->row()->total;
    }

    public function get_setting() {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function get_pos_setting() {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDateFormat($id) {
        $q = $this->db->get_where('date_format', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getActivePaymentMethods()
    {
        $this->load->model('pos_model');
        $methods = $this->pos_model->get_active_payment_methods();
        $result = [];
        if ($methods) {
            foreach ($methods as $method) {
                $obj = new stdClass();
                $obj->type = $method['code'];
                $obj->display_name = $method['name'];
                $obj->is_default = isset($method['is_default']) ? $method['is_default'] : 0;
                $result[] = $obj;
            }
        }
        return $result;
    }

    public function getAllCompanies($group_name) {
        $this->db->order_by('name', 'ASC');
        $q = $this->db->get_where('companies', array('group_name' => $group_name));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyByID($id) {
        $q = $this->db->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCustomerGroupByID($id) {
        $q = $this->db->get_where('customer_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCustomerGroupByName($name) {
        $q = $this->db->get_where('customer_groups', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getUser($id = NULL) {
        if (!$id) {
            $id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getUserById($id = NULL) {

        $sql = "SELECT `sma_users`.`id` as `id`, `first_name`, `last_name`, `email`, `company`, `sma_groups`.`name`, `active` FROM `sma_users` LEFT JOIN `sma_groups` ON `sma_users`.`group_id`=`sma_groups`.`id` WHERE `sma_users`.`id` ='$id' and `company_id` IS NULL GROUP BY `sma_users`.`id`";
        $q = $this->db->query($sql);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllUser() {

        $sql = 'SELECT `sma_users`.`id` as `id`, `first_name`, `last_name`, `email`, `company`, `sma_groups`.`name`, `active` FROM `sma_users` LEFT JOIN `sma_groups` ON `sma_users`.`group_id`=`sma_groups`.`id` WHERE `company_id` IS NULL GROUP BY `sma_users`.`id`';
        $q = $this->db->query($sql);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return FALSE;
    }

    public function getProductByID($id)
    {
        $user_warehouse = $this->site->getUser();

        if ($this->Owner || $this->Admin) {

            $q = $this->db->get_where('products', ['id' => $id], 1);

        } else {

            // 1️⃣ Convert comma-separated warehouse_id to array
            $warehouse_ids = array_map(
                'intval',
                explode(',', $user_warehouse->warehouse_id)
            );

            $this->db->select('products.*, sum(wp.quantity) as quantity');
            $this->db->from('products');

            // 2️⃣ Join without warehouse condition
            $this->db->join(
                'warehouses_products wp',
                'wp.product_id = products.id',
                'left'
            );

            // 3️⃣ Apply WHERE IN explicitly
            $this->db->where_in('wp.warehouse_id', $warehouse_ids);

            $this->db->where('products.id', $id);
            // $this->db->limit(1);

            $q = $this->db->get();
        }

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllCurrencies() {
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCurrencyByCode($code) {
        $q = $this->db->get_where('currencies', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllTaxRates() {
        $q = $this->db->where('is_substitutable', '0')->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTaxRateByID($id) {
        $q = $this->db->get_where('tax_rates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTaxIdByRate($rate) {
        $q = $this->db->get_where('tax_rates', array('rate' => $rate, 'is_substitutable' => 0), 1);
        if ($q->num_rows() > 0) {
            return (int) $q->row()->id;
        }
        $q = $this->db->get_where('tax_rates', array('rate' => $rate), 1);
        if ($q->num_rows() > 0) {
            return (int) $q->row()->id;
        }
        return 0;
    }

    public function isInternalCustomer($customer) {
        return !empty($customer) && !empty($customer->is_internal_customer) && strtolower($customer->is_internal_customer) === 'yes';
    }

    /**
     * Zero product tax for internal customers. Must run after product/category tax resolution.
     */
    public function applyInternalCustomerZeroTax($customer, &$row, &$tax_rate, &$fixtaxrate = null, &$varaibletax = null) {
        if (!$this->isInternalCustomer($customer)) {
            return false;
        }
        $row->tax_rate = 0;
        $tax_rate = false;
        if ($fixtaxrate !== null) {
            $fixtaxrate = false;
        }
        if ($varaibletax !== null) {
            $varaibletax = false;
        }
        return true;
    }

    public function getAllWarehouses() {
        $q = $this->db->where(["is_deleted" => 0, "is_disabled" => 0])->order_by('name', 'ASC')->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllActiveWarehouses() {
        $q = $this->db->where(["is_active" => 1, "is_deleted" => 0, "is_disabled" => 0])->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function isWarehouseActive($warehouse_id) {
        $q = $this->db->select('is_active, is_deleted, is_disabled')->where(["id" => $warehouse_id])->get('warehouses', 1);
        if ($q->num_rows() > 0) {
            $result = $q->row();

            if ($result->is_active == 1 && $result->is_deleted == 0 && $result->is_disabled == 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        return FALSE;
    }

    public function getWarehousesByLocationType($location_type_name) {
        $this->db->select('warehouses.*');
        $this->db->from('warehouses');
        $this->db->join('sma_location_type', 'warehouses.location_type = sma_location_type.id', 'left');
        $this->db->where('sma_location_type.type', $location_type_name);
        $this->db->where('warehouses.is_deleted', 0);
        $this->db->where('warehouses.is_disabled', 0);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseByID($id) {

        if (!is_numeric($id)) {
            $q = $this->db->get_where('warehouses', ['id' => $id], 1);
            if ($q->num_rows() > 0) {
                $data = $q->row();
                return $data;
            }
        } else {
            return $this->getWarehouseByIDs($id);
        }
        return FALSE;
    }
     public function getWarehouseID($id) {
        if (is_numeric($id)) {
            $q = $this->db->get('warehouses');
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[$row->id] = $row;
                }
                return $data;
            }
        } else {
            return $this->getWarehouseByIDs($id);
        }
        return FALSE;
    }

    /* 11-28-2019 */

    public function getWarehouseBy_ID($id) {

        if (is_numeric($id)) {
            $q = $this->db->get_where('warehouses', array('id' => $id));
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        } else {
            return $this->getWarehouseByIDs($id);
        }
        return FALSE;
    }

    public function getWarehouseByIDs($id) {

        $wrids = explode(',', $id);

        $q = $this->db->where_in('id', $wrids)->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProdCategories() {

        $sql = "SELECT * FROM `sma_categories` WHERE `id` in (SELECT distinct `category_id` FROM `sma_products`) ORDER BY `name`";
        $q = $this->db->query($sql);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getwarehouses($id) {

        // $wrids = explode(',', $id);

        $q = $this->db->where_in('id', $id)->get('sma_users');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data = $row->warehouse_id;
                $array = explode(",", $data);
            }
            return $array;
        }
        return FALSE;
    }
    public function getProdCategoriesWithDefaultCategory($default_category) {

        $user = $this->session->userdata('user_id'); 
        $warehouse =  $this->getwarehouses($user);  
        $setting=  $this->get_pos_setting();
        // $sql = "SELECT * FROM `sma_categories` WHERE `id` IN (SELECT DISTINCT `category_id` FROM `sma_products`) AND `flag_visible` = 1 ORDER BY CASE WHEN rank IS NULL OR rank = '' THEN 999 ELSE CAST(rank AS UNSIGNED) END ASC";
        $warehouse_ids_str = implode(",", $warehouse);
       
        if ($setting->categorylocation == 1) {
           if ($warehouse_ids_str) {
            
                // $sql = "SELECT * FROM `sma_categories` 
                // WHERE `id` IN (SELECT DISTINCT `category_id` FROM `sma_products`) 
                // AND `flag_visible` = 1
                // AND (FIND_IN_SET($warehouse_ids_str, `warehouse_id`) > 0 OR `warehouse_id` = '*All') 
                // ORDER BY CASE WHEN rank IS NULL OR rank = '' THEN 999 ELSE CAST(rank AS UNSIGNED) END ASC";

                $find_in_set_conditions = implode(" OR ", array_map(function($id) {
                    return "FIND_IN_SET('$id', warehouse_id)";
                }, $warehouse));

                $sql = "SELECT * FROM sma_categories 
                    WHERE id IN (SELECT DISTINCT category_id FROM sma_products) 
                    AND flag_visible = 1
                    AND ( ($find_in_set_conditions) OR warehouse_id = '*All') 
                    ORDER BY CASE WHEN rank IS NULL OR rank = '' THEN 999 ELSE CAST(rank AS UNSIGNED) END ASC";
           } else {
            $sql = "SELECT * 
            FROM `sma_categories` 
            WHERE `id` IN (
                SELECT DISTINCT `category_id` 
                FROM `sma_products`
            ) 
            AND `flag_visible` = 1 
            -- AND `warehouse_id` IN ('No') 
            ORDER BY 
                CASE 
                    WHEN `rank` IS NULL OR `rank` = '' THEN 999 
                    ELSE CAST(`rank` AS UNSIGNED) 
                END ASC";
           }                              
        }else {
            $sql = "SELECT * 
            FROM `sma_categories` 
            WHERE `id` IN (
                SELECT DISTINCT `category_id` 
                FROM `sma_products`
            ) 
            AND `flag_visible` = 1 
            -- AND `warehouse_id` IN ('No') 
            ORDER BY 
                CASE 
                    WHEN `rank` IS NULL OR `rank` = '' THEN 999 
                    ELSE CAST(`rank` AS UNSIGNED) 
                END ASC";
    
        }
       
        $q = $this->db->query($sql);
        
        if ($q->num_rows() > 0) {
            $data = $q->result();
            $default_cat = null;
            $remaining_cats = [];
    
            foreach ($data as $row) {
                if ($row->id == $default_category) {
                    $default_cat = $row;
                } else {
                    $remaining_cats[] = $row;
                }
            }
            $result = [];
            if ($default_cat) {
                $result[] = $default_cat;
            }
            $result = array_merge($result, $remaining_cats);
    
            return $result;
        }
    
        return FALSE;
    }
    public function getAllCategories() {

        $this->db->where('parent_id', NULL)->or_where('parent_id', 0)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProdSubCategories($parent_id) {

        $sql = "SELECT * FROM `sma_categories` WHERE `id` IN ( SELECT distinct `subcategory_id` FROM `sma_products` WHERE `category_id` = '$parent_id')";

        $q = $this->db->query($sql);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSubCategories($parent_id) {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPrdCategories($id) {
        $this->db->where(array('category_id' => $id));
        $q = $this->db->get("products");

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPrdSubCategories($id) {
        $this->db->where(array('subcategory_id' => $id));
        $q = $this->db->get("products");

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPrdUnit($id) {
        $this->db->where(array('sale_unit' => $id))->or_where('purchase_unit', $id)->or_where('unit', $id);
        $q = $this->db->get("products");

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCategoryByID($id) {
        $q = $this->db->get_where('categories', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getGiftCardByID($id) {
        $q = $this->db->get_where('gift_cards', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getGiftCardByNO($no) {
        $q = $this->db->get_where('gift_cards', array('card_no' => $no), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateInvoiceStatus() {
        $date = date('Y-m-d');
        $q = $this->db->get_where('invoices', array('status' => 'unpaid'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if ($row->due_date < $date) {
                    $this->db->update('invoices', array('status' => 'due'), array('id' => $row->id));
                }
            }
            $this->db->update('settings', array('update' => $date), array('setting_id' => '1'));
            return true;
        }
    }

   public function modal_js() {
        $filePath = $this->data['assets'] . 'js/modal.js';
        
        if (file_exists($filePath)) {
            $jsContent = file_get_contents($filePath);
            if ($jsContent !== false) {
                return '<script type="text/javascript">' . $jsContent . '</script>';
            } else {
                return '<!-- Error: Unable to read file content -->';
            }
        } else {
            return '<!-- Error: File not found -->';
        }
    }

    /**
     * Original Function Defination.
     * Devided in multiple function
     */
    /*
      public function getReference($field) {
      $q = $this->db->get_where('order_ref', array('ref_id' => '1'), 1);
      if ($q->num_rows() > 0) {
      $ref = $q->row();

      switch ($field) {
      case 'so':
      $prefix = $this->Settings->sales_prefix;
      break;
      case 'pos':
      $prefix = isset($this->Settings->sales_prefix) ? $this->Settings->sales_prefix . '/POS' : '';
      break;
      case 'qu':
      $prefix = $this->Settings->quote_prefix;
      break;
      case 'po':
      $prefix = $this->Settings->purchase_prefix;
      break;
      case 'to':
      $prefix = $this->Settings->transfer_prefix;
      break;
      case 'do':
      $prefix = $this->Settings->delivery_prefix;
      break;
      case 'pay':
      $prefix = $this->Settings->payment_prefix;
      break;
      case 'ppay':
      $prefix = $this->Settings->ppayment_prefix;
      break;
      case 'ex':
      $prefix = $this->Settings->expense_prefix;
      break;
      case 're':
      $prefix = $this->Settings->return_prefix;
      break;
      case 'rep':
      $prefix = $this->Settings->returnp_prefix;
      break;
      case 'qa':
      $prefix = $this->Settings->returnp_prefix;
      break;
      case 'offapp':
      $prefix = 'OFFLINE/SALE';
      break;
      case 'eshop':
      $prefix = 'ESHOP/SALE';
      break;
      default:
      $prefix = '';
      }

      $ref_no = (!empty($prefix)) ? $prefix . '/' : '';

      if ($this->Settings->reference_format == 1) {
      $ref_no .= date('Y') . "/" . sprintf("%04s", $ref->{$field});
      } elseif ($this->Settings->reference_format == 2) {
      $ref_no .= date('Y') . "/" . date('m') . "/" . sprintf("%04s", $ref->{$field});
      } elseif ($this->Settings->reference_format == 3) {
      $ref_no .= sprintf("%04s", $ref->{$field});
      } else {
      $ref_no .= $this->getRandomReference();
      }

      return $ref_no;
      }
      return FALSE;
      }
     */

    /**
     * Function Modified by sunil dated on: 08-09-2017
     */
    public function getReference($field) {

        $refnum = $this->getNextReference($field);

        if ($refnum) {

            $ref_no = $this->getReferenceFormat($field, $refnum);

            return $ref_no;
        }

        return false;
    }

    public function getReferenceNumber($field, $refFormat = '') {

        $refnum = $this->getNextReference($field);

        if ($refnum) {

            return $this->getReferenceFormat($field, $refnum, $refFormat);
        }

        return false;
    }

    public function getReferenceFormat($field, $refnum, $refFormat = '') {

        $prefix = $this->getReferencePrefix($field);

        $ref_no = (!empty($prefix)) ? $prefix . '/' : '';

        $refFormat = (empty($refFormat)) ? $this->Settings->reference_format : $refFormat;

        if ($refFormat == 1) {
            $ref_no .= date('Y') . "/" . sprintf("%04s", $refnum);
        } elseif ($refFormat == 2) {
            $ref_no .= date('Y') . "/" . date('m') . "/" . sprintf("%04s", $refnum);
        } elseif ($refFormat == 3) {
            $ref_no .= sprintf("%04s", $refnum);
        } else {
            $ref_no .= $this->getRandomReference();
        }

        return $ref_no;
    }

    public function getNextReference($field) {

        $q = $this->db->get_where('order_ref', array('ref_id' => '1'), 1);

        if ($q->num_rows() > 0) {
            $ref = (array) $q->row();
            return $ref[$field];
        }
        return false;
    }

    public function setSettings() {

        if (!isset($this->Settings)) {
            $this->Settings = $this->get_setting();
        }
    }

    public function getReferencePrefix($field) {

        switch ($field) {
            case 'so':
                $prefix = $this->Settings->sales_prefix;
                break;
            case 'pos':
                $prefix = isset($this->Settings->sales_prefix) ? $this->Settings->sales_prefix . '/POS' : '';
                break;
            case 'qu':
                $prefix = $this->Settings->quote_prefix;
                break;
            case 'po':
                $prefix = $this->Settings->purchase_prefix;
                break;
            case 'to':
                $prefix = $this->Settings->transfer_prefix;
                break;
            case 'do':
                $prefix = $this->Settings->delivery_prefix;
                break;
            case 'pay':
                $prefix = $this->Settings->payment_prefix;
                break;
            case 'ppay':
                $prefix = $this->Settings->ppayment_prefix;
                break;
            case 'ex':
                $prefix = $this->Settings->expense_prefix;
                break;
            case 're':
                $prefix = $this->Settings->return_prefix;
                break;
            case 'rep':
                $prefix = $this->Settings->returnp_prefix;
                break;
            case 'qa':
                $prefix = $this->Settings->returnp_prefix;
                break;
            case 'offapp':
                $prefix = 'OFFLINE/SALE';
                break;
            case 'eshop':
                $prefix = 'ESHOP/SALE';
                break;
            case 'up':
                $prefix = 'UP/SALE';
                break;
            case 'ordr':
                $prefix = 'ORDER';
                break;
            case 'treq':
                $prefix = 'REQUEST';
                break;
            case 'pu':
                $prefix = 'PU';   // for procurement order convert into sales
                break;
            case 're_ordr':
                $prefix = 'RE/ORDER';
                break;
            case 're_ordr':
                $prefix = 'RE/CHALLAN';
                break;
            default:
                $prefix = '';
                break;
        }

        return $prefix;
    }

    public function getRandomReference($len = 12) {
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= mt_rand(0, 9);
        }

        if ($this->getSaleByReference($result)) {
            $this->getRandomReference();
        }

        return $result;
    }

    public function getSaleByReference($ref) {
        $this->db->like('reference_no', $ref, 'before');
        $q = $this->db->get('sales', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateReference($field) {

        $q = $this->db->get_where('order_ref', array('ref_id' => '1'), 1);

        if ($q->num_rows() > 0) {
            $ref = $q->row();
            $this->db->update('order_ref', array($field => $ref->{$field} + 1), array('ref_id' => '1'));
            return TRUE;
        } else {
            echo $this->db->error();
            return FALSE;
        }
    }

    public function checkPermissions() {
        $q = $this->db->get_where('permissions', array('group_id' => $this->session->userdata('group_id')), 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return FALSE;
    }

    public function getNotifications() {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where("from_date <=", $date);
        $this->db->where("till_date >=", $date);
        if (!$this->Owner) {
            if ($this->Supplier) {
                $this->db->where('scope', 4);
            } elseif ($this->Customer) {
                $this->db->where('(scope = 1 or scope = 3)');
                //$this->db->where('scope', 1)->or_where('scope', 3);
            } elseif (!$this->Customer && !$this->Supplier) {
                $this->db->where('(scope = 2 or scope = 3)');
                // $this->db->where('scope', 2)->or_where('scope', 3);
            }
        }
        $q = $this->db->get("notifications");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getUpcomingEvents() {
        $dt = date('Y-m-d');
        $this->db->where('start >=', $dt)->order_by('start')->limit(5);
        if ($this->Settings->restrict_calendar) {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        }

        $q = $this->db->get('calendar');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUserGroup($user_id = false) {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $group_id = $this->getUserGroupID($user_id);
        $q = $this->db->get_where('groups', array('id' => $group_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getUserGroupID($user_id = false) {
        $user = $this->getUser($user_id);
        return $user->group_id;
    }

    public function getWarehouseProductsVariants($option_id, $warehouse_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchasedItem($where_clause) {

        $product_storage_type = $where_clause['product_id'] ? $this->getProductStorageType($where_clause['product_id']) : 'packed';

        $orderby = ($this->Settings->accounting_method > 0) ? 'desc' : 'asc';

        if (!$this->Settings->overselling) {
            $this->db->order_by('date', $orderby);
            $this->db->order_by('purchase_id', $orderby);
            $this->db->where('quantity_balance > 0');
        } else {
            $this->db->order_by('quantity_balance', 'desc');
            $this->db->order_by('date', $orderby);
            $this->db->order_by('purchase_id', $orderby);
        }

        if ($where_clause['option_id']) {
            $this->db->where('option_id', $where_clause['option_id']);
        }
        unset($where_clause['option_id']);

        if ($this->Settings->product_batch_setting > 0 && $where_clause['batch_number']) {
            $this->db->where('batch_number', $where_clause['batch_number']);
        }
        unset($where_clause['batch_number']);

        if ($where_clause['status']) {
            $this->db->where('status', $where_clause['status']);
            unset($where_clause['status']);
        } else {
            $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        }

        $this->db->where($where_clause);

        $q = $this->db->get('purchase_items');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /*
      public function syncVariantQty($variant_id, $warehouse_id, $product_id = NULL) {
      $balance_qty = $this->getBalanceVariantQuantity($variant_id);
      $wh_balance_qty = $this->getBalanceVariantQuantity($variant_id, $warehouse_id);


      // Return product variant and warehouse variant Qty
      $balance_return_qty = abs($this->getBalanceVariantQuantityretrun($variant_id));
      $wh_balance_qty_returnqty =abs($this->getBalanceVariantQuantityretrun($variant_id, $warehouse_id));
      // End

      if ($this->db->update('product_variants', array('quantity' => $balance_qty - $balance_return_qty), array('id' => $variant_id))) {
      if ($this->getWarehouseProductsVariants($variant_id, $warehouse_id)) {
      $this->db->update('warehouses_products_variants', array('quantity' => $wh_balance_qty - $wh_balance_qty_returnqty), array('option_id' => $variant_id, 'warehouse_id' => $warehouse_id));
      } else {
      if($wh_balance_qty) {
      $this->db->insert('warehouses_products_variants', array('quantity' => $wh_balance_qty - $wh_balance_qty_returnqty, 'option_id' => $variant_id, 'warehouse_id' => $warehouse_id, 'product_id' => $product_id));
      }
      }
      return TRUE;
      }
      return FALSE;
      }
     */
    public function syncVariantQty($variant_id, $warehouse_id, $product_id = NULL, $batch_number = NULL) {
        $balance_qty = $this->getBalanceVariantQuantity($variant_id);
        $wh_balance_qty = $this->getBalanceVariantQuantity($variant_id, $warehouse_id);
        
        // Batch-wise stock management
        if ($batch_number && $this->Settings->product_batch_setting != 0) {
            return $this->syncVariantBatchWiseQty($variant_id, $warehouse_id, $product_id, $batch_number);
        }

        if ($this->db->update('product_variants', array('quantity' => $balance_qty), array('id' => $variant_id))) {
            $wpv = $this->getWarehouseProductsVariants($variant_id, $warehouse_id);
            
            if ($wpv) {
                $this->db->update('warehouses_products_variants', 
                    array('quantity' => $wh_balance_qty), 
                    array('option_id' => $variant_id, 'warehouse_id' => $warehouse_id)
                );
            } else {
                if ($wh_balance_qty != 0) {
                    $this->db->insert('warehouses_products_variants', array(
                        'quantity' => $wh_balance_qty, 
                        'option_id' => $variant_id, 
                        'warehouse_id' => $warehouse_id, 
                        'product_id' => $product_id
                    ));
                }
            }
            return TRUE;
        }
        return FALSE;
    }

    public function syncProductBatchQty($batch_no, $product_id, $warehouse_id, $variant_id = 0) {

        if (!$batch_no) {
            return FALSE;
        }
//        $balance_qty = $this->getBalanceVariantQuantity($variant_id);
//        $wh_balance_qty = $this->getBalanceVariantQuantity($variant_id, $warehouse_id);
//        if ($this->db->update('product_variants', array('quantity' => $balance_qty), array('id' => $variant_id))) {
//            if ($this->getWarehouseProductsVariants($variant_id, $warehouse_id)) {
//                $this->db->update('warehouses_products_variants', array('quantity' => $wh_balance_qty), array('option_id' => $variant_id, 'warehouse_id' => $warehouse_id));
//            } else {
//                if ($wh_balance_qty) {
//                    $this->db->insert('warehouses_products_variants', array('quantity' => $wh_balance_qty, 'option_id' => $variant_id, 'warehouse_id' => $warehouse_id, 'product_id' => $product_id));
//                }
//            }
//            return TRUE;
//        }
        return FALSE;
    }

    public function getWarehouseProducts($product_id, $warehouse_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncProductQty($product_id, $warehouse_id, $batch_number = NULL) {
        $balance_qty = $this->getBalanceQuantity($product_id);
        $wh_balance_qty = $this->getBalanceQuantity($product_id, $warehouse_id);
        
        // *Batch-Wise Stock Management*
        if ($batch_number && $this->Settings->product_batch_setting != 0) {
            $this->syncProductBatchWiseQty($product_id, $warehouse_id,$batch_number);
            return TRUE;
        }
        if ($this->db->update('products', array('quantity' => $balance_qty), array('id' => $product_id))) {
            if ($this->getWarehouseProducts($product_id, $warehouse_id)) {
                $this->db->update('warehouses_products', array('quantity' => $wh_balance_qty), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id));
            } else {
                if (!$wh_balance_qty) {
                    $wh_balance_qty = 0;
                }
                $product = $this->site->getProductByID($product_id);
                if (!empty($product)) {
                    $this->db->insert('warehouses_products', array('quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => $product->cost));
                }
            }
            return TRUE;
        }
        return FALSE;
    }

    public function getSaleByID($id) {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSaleByIDEshop($id) {
        $q = $this->db->get_where('orders', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getChallanRecordByID($id) {
        $q = $this->db->get_where('delivery_challan', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSaleBySaleInvoice($id) {
        $q = $this->db->get_where('orders', array('sale_invoice_no' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getOrderByID($id) {
        $q = $this->db->get_where('orders', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSalePayments($sale_id) {
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getChallanPayments($challan_id) {
        $q = $this->db->get_where('payments', array('challan_id' => $challan_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getOrderPayments($order_id) {
        $q = $this->db->get_where('payments', array('order_id' => $order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncSaleActionPayments($id, $sale_action = '') {

        if ($sale_action == 'eshop_order') {
            return $this->syncOrderPayments($id);
        } elseif ($sale_action == 'chalan') {
            return $this->syncChallanPayments($id);
        } else {
            return $this->syncSalePayments($id);
        }
    }

    public function syncSalePayments($id) {
        $sale = $this->getSaleByID($id);
        $payments = $this->getSalePayments($id);
        $paid = 0;
        $returnAmount = ($sale->return_sale_total?abs($sale->return_sale_total): 0);
       
        $grand_total = $sale->grand_total + $sale->rounding - $returnAmount;
        if (!empty($payments)) {
            foreach ($payments as $payment) {
                $paid += $payment->amount;
            }
        }
        $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
        // updated  by SW for partial 03022017
        $abs_paid = abs($paid);
        $abs_grand_total = abs($grand_total);
        if ($abs_paid == 0) {
            $payment_status = 'due';
        } elseif ($abs_paid < $abs_grand_total) {
            $payment_status = 'partial';
        } else {
            $payment_status = 'paid';
        }
        if ($this->sma->formatDecimal($abs_grand_total) <= $this->sma->formatDecimal($abs_paid)) {
            
            $payment_status = 'paid';
        } elseif ($abs_paid != 0) {
            $payment_status = 'partial';
        } elseif ($sale->due_date <= date('Y-m-d') && !$sale->sale_id) {
            $payment_status = 'due';
        }

        if ($this->db->update('sales', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }

        return FALSE;
    }

    public function syncChallanPayments($id) {
        $sale = $this->getChallanRecordByID($id);
        $payments = $this->getChallanPayments($id);
        $paid = 0;
        $returnAmount = (isset($sale->return_sale_total) ? abs($sale->return_sale_total) : 0);

        $grand_total = $sale->grand_total + $sale->rounding - $returnAmount;
        if (!empty($payments)) {
            foreach ($payments as $payment) {
                $paid += $payment->amount;
            }
        }
        $payment_status = $paid == 0 ? 'pending' : (isset($sale->payment_status) ? $sale->payment_status : 'pending');
        $abs_paid = abs($paid);
        $abs_grand_total = abs($grand_total);
        if ($abs_paid == 0) {
            $payment_status = 'due';
        } elseif ($abs_paid < $abs_grand_total) {
            $payment_status = 'partial';
        } else {
            $payment_status = 'paid';
        }
        if ($this->sma->formatDecimal($abs_grand_total) <= $this->sma->formatDecimal($abs_paid)) {
            $payment_status = 'paid';
        } elseif ($abs_paid != 0) {
            $payment_status = 'partial';
        }

        if ($this->db->update('delivery_challan', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }

        return FALSE;
    }

    public function syncOrderPayments($id) {
        $sale = $this->getOrderByID($id);
        $payments = $this->getOrderPayments($id);
        $paid = 0;
        $grand_total = $sale->grand_total + $sale->rounding;
        if (!empty($payments)) {
            foreach ($payments as $payment) {
                $paid += $payment->amount;
            }
        }
        $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
        // updated  by SW for partial 03022017
        if ($this->sma->formatDecimal($grand_total) <= $this->sma->formatDecimal($paid)) {
            $payment_status = 'paid';
        } elseif ($paid != 0) {
            $payment_status = 'partial';
        } elseif ($sale->due_date <= date('Y-m-d') && !$sale->sale_id) {
            $payment_status = 'due';
        }

        if ($this->db->update('orders', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }

        return FALSE;
    }

    public function getPurchaseWarehouseByID($id) {
        $q = $this->db->select('warehouse_id')->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchaseByID($id) {
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchasePayments($purchase_id) {
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncPurchasePayments($id) {
        $purchase = $this->getPurchaseByID($id);
        $payments = $this->getPurchasePayments($id);
        $paid = 0;
        if (is_array($payments)) {
            foreach ($payments as $payment) {
                $paid += $payment->amount;
            }
        }

        $payment_status = $paid <= 0 ? 'pending' : $purchase->payment_status;
        if ($this->sma->formatDecimal($purchase->grand_total - abs($purchase->rounding)) > $this->sma->formatDecimal($paid) && $paid > 0) {
            $payment_status = 'partial';
        } elseif ($this->sma->formatDecimal($purchase->grand_total - abs($purchase->rounding)) <= $this->sma->formatDecimal($paid)) {
            $payment_status = 'paid';
        }

        if ($this->db->update('purchases', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }

        return FALSE;
    }

    private function getBalanceQuantity($product_id, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        // $this->db->where('product_id', $product_id)->where('quantity_balance !=', 0);
        $this->db->where('product_id', $product_id);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
//        if (!$this->Settings->overselling) {
//            $this->db->where('(purchase_id IS NOT NULL OR transfer_id IS NOT NULL OR adjustment_id IS NOT NULL)');
//        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    private function getBalanceVariantQuantity($variant_id = 0, $warehouse_id = NULL) {
        $variant_id = $variant_id ? $variant_id : 0;
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('option_id', $variant_id);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
//        if (!$this->Settings->overselling) {
//            $this->db->where('(purchase_id IS NOT NULL OR transfer_id IS NOT NULL OR adjustment_id IS NOT NULL)');
//        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    /**
     *  04-12-2019
     * @param type $variant_id
     * @param type $warehouse_id
     * @return int
     * @param type $variant_id
     * @param type $warehouse_id
     * @return int Get Return Qty 
     */
    private function getBalanceVariantQuantityretrun($variant_id = 0, $warehouse_id = NULL) {
        $variant_id = $variant_id ? $variant_id : 0;
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('option_id', $variant_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
//        if (!$this->Settings->overselling) {
//            $this->db->where('(purchase_id IS NOT NULL OR transfer_id IS NOT NULL OR adjustment_id IS NOT NULL)');
//        }
        $this->db->group_start()->where('status', 'returned')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    /**
     * End Get return qty 
     */
    public function calculateAVCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id = 0, $item_quantity, $batch_number = null) {
        $real_item_qty = $quantity;
        $product = $this->getProductByID($product_id);
        //$product_avg_cost = $this->db->select('cost')->where('id', $product_id)->get('products')->row();
        $product_avg_cost = $product->cost;
        $option_id = ( $this->Settings->attributes == 1 && $product->storage_type == 'packed' && $option_id ) ? $option_id : 0;
        $unit_quantity = 1;
        if($option_id){
            $wp_details = $this->getWarehouseProductsVar($option_id, $warehouse_id);
        }else{
            $wp_details = $this->getWarehouseProduct($warehouse_id, $product_id);
        }
        if ($product->storage_type == 'packed') {
          
            $pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id, $batch_number);
        } else {
            if ($product->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1) {
                $variants = $this->getVerientById($option_id);
                $unit_quantity = $variants['unit_quantity'] ? $variants['unit_quantity'] : 1;
                $item_quantity = ($real_item_qty * $unit_quantity);
            }
            $option_id = 0;  //No Variant stocks available for loose products.          
            $pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id, $batch_number);
        }//end else       

        if ($pis) {
            $cost_row = array();
            $quantity = $item_quantity;
            $balance_qty = $quantity;            
            $avg_net_unit_cost = $wp_details->avg_cost;
            $avg_unit_cost     = $wp_details->avg_cost;
            foreach ($pis as $pi) {
                
                if ($pi->quantity_balance <= 0) {
                    continue;
                }//Avoide Zero Quantity Purchase Records.
                if (!empty($pi) && $pi->quantity_balance > 0 && $balance_qty <= $quantity && $quantity != 0) {
                    $tax_amt = ($pi->item_tax / $pi->unit_quantity);
                    if ($pi->quantity_balance >= $quantity && $quantity != 0) {
                        $balance_qty = $pi->quantity_balance - $quantity;                        
                        $cost_row = ['date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => ($avg_net_unit_cost - $tax_amt), 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id];
                        $quantity = 0;
                    } elseif ($quantity != 0) {
                        $quantity = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $cost_row = ['date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => ($avg_net_unit_cost - $tax_amt), 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id];
                    }
                }
                if (empty($cost_row)) {
                    break;
                }
                $cost[] = $cost_row;
                if ($quantity == 0) {
                    break;
                }
            }
        }
        $positiveValue = abs($quantity);
        if ($positiveValue  < 0 && !$this->Settings->overselling) {
            $msg = sprintf(lang("quantity_out_of_stock_for_%s"), ($pi->product_name ? $pi->product_name : $product_name));
            $this->session->set_flashdata('error', $msg);
            if (!$this->input->is_ajax_request()) {
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $response['status'] = 'error';
                $response['message'] = $msg;
                echo json_encode($response);
                exit;
            }
        } elseif ($quantity != 0) {
            $cost[] = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => is_array($wp_details) ? $wp_details->avg_cost : $product_avg_cost, 'purchase_unit_cost' => is_array($wp_details) ? $wp_details->avg_cost : $product_avg_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => (0 - $quantity), 'overselling' => 1, 'inventory' => 1);
            $cost[] = array('pi_overselling' => 1, 'product_id' => $product_id, 'quantity_balance' => (0 - $quantity), 'warehouse_id' => $warehouse_id, 'option_id' => $option_id, 'batch_number' => $batch_number);
        }
        return $cost;
    }






    public function calculateCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id = 0, $item_quantity, $batch_number = null) {
        $option_id = $option_id ? $option_id : 0;
        $batch_number = $batch_number ? $batch_number : NULL;
        $unit_quantity = 1;
        $product = $this->getProductByID($product_id);
        $real_item_qty = $quantity;
        
        if ($this->Settings->attributes == 1 && $product->storage_type == 'packed') {
            $pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id, $batch_number);
        } else {
            if ($product->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1) {
                $variants = $this->getVerientById($option_id);
                $unit_quantity = $variants['unit_quantity'] ? $variants['unit_quantity'] : 1;
                $item_quantity = $real_item_qty * $unit_quantity;
            }
            $option_id = 0; //No Variant stocks available for loose products 
            $pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id, $batch_number);
        }//end else
        
        $quantity = $item_quantity;
        $balance_qty = $quantity;
        foreach ($pis as $pi) {
            if ($pi->quantity_balance <= 0) {
                continue;
            }//Avoide Zero Quantity Purchase Records.
            $cost_row = NULL;
            if (!empty($pi) && $balance_qty <= $quantity && $quantity > 0) {
                $purchase_unit_cost = $pi->unit_cost ? $pi->unit_cost : ($pi->net_unit_cost + ($pi->item_tax / $pi->quantity));
                if ($pi->quantity_balance >= $quantity && $quantity > 0) {
                    $balance_qty = $pi->quantity_balance - $quantity;
                    $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                    $quantity = 0;
                } elseif ($quantity > 0) {
                    $quantity = $quantity - $pi->quantity_balance;
                    $balance_qty = $quantity;
                    $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id);
                }
            }
            $cost[] = $cost_row;
            if ($quantity == 0) {
                break;
            }
        }
        if ($quantity > 0) {
            $msg = sprintf(lang("quantity_out_of_stock_for_%s"), ($pi->product_name ? $pi->product_name : $product_name));
            $this->session->set_flashdata('error', $msg);
            if (!$this->input->is_ajax_request()) {
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $response['status'] = 'error';
                $response['message'] = $msg;
                echo json_encode($response);
                exit;
            }
        }
        return $cost;
    }

    public function getPurchasedItems($product_id, $warehouse_id, $option_id = 0, $batchNo = NULL) {
        $orderby = ($this->Settings->accounting_method ) ? 'desc' : 'asc';
        $product_storage_type = $product_id ? $this->getProductStorageType($product_id) : 'packed';

        $this->db->select('id, quantity, quantity_balance, net_unit_cost, unit_cost, item_tax, unit_quantity, batch_number, option_id, warehouse_id');
        $this->db->where('product_id', $product_id);

        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($batchNo != NULL && $this->Settings->product_batch_setting > 0) {
            $this->db->where('batch_number', $batchNo);
        }
        if ($batchNo != NULL && $this->Settings->product_batch_setting == 0 && $this->Settings->enable_module_production_unit == 1) {
            $this->db->where('batch_number', $batchNo);
        }
//        if (!$this->Settings->overselling) {
//            $this->db->where('(purchase_id IS NOT NULL OR transfer_id IS NOT NULL OR adjustment_id IS NOT NULL)');
//        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
        $this->db->group_by('id');
        $this->db->order_by('date', $orderby);
        $this->db->order_by('id', $orderby);
        $q = $this->db->get('purchase_items');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductComboItems($pid, $warehouse_id = NULL) {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, combo_items.unit_price as unit_price, warehouses_products.quantity as quantity')
                ->join('products', 'products.code=combo_items.item_code', 'left')
                ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
                ->group_by('combo_items.id');
        if ($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }

    public function item_costing($item, $pi = NULL) {
              
        $item_quantity = $pi ? $item['aquantity'] : $item['unit_quantity'];

        $item['batch_number'] = (isset($item['batch_number']) && $item['batch_number']) ? $item['batch_number'] : '';
        $item['option_id'] = $item['option_id'] ? $item['option_id'] : 0;

        //If accounting method is not Avarage Cost
        if ($this->Settings->accounting_method != 2 && !$this->Settings->overselling) {

            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $unit = $this->getUnitByID($item['product_unit_id']);
                    $item['net_unit_price'] = $this->convertToBase($unit, $item['net_unit_price']);
                    $item['unit_price'] = $this->convertToBase($unit, $item['unit_price']);
                    $cost = $this->calculateCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity, $item['batch_number']);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price = $combo_item->unit_price;
                            } else {
                                $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price = $combo_item->unit_price;
                        }
                        if ($pr->type == 'standard') {
                            $cost[] = $this->calculateCost($pr->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $pr->name, NULL, $item_quantity, NULL);
                        } else {
                            $cost[] = array(array('date' => date('Y-m-d'), 'product_id' => $pr->id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => ($combo_item->qty * $item['quantity']), 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $combo_item->unit_price, 'sale_unit_price' => $combo_item->unit_price, 'quantity_balance' => NULL, 'inventory' => NULL));
                        }
                    }
                } else {
                    $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
            }
        } else {
            //If Accounting method is Avarage Cost
            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $cost = $this->calculateAVCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity, $item['batch_number']);
                } 
                elseif ($item['product_type'] == 'combo') {                   
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $cost[] = $this->calculateAVCost($combo_item->id, $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], ($combo_item->qty * $item['quantity']), $item['product_name'], $item['option_id'], $item_quantity, $item['batch_number']);
                    }
                } else {
                    $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
            }
        }
        return $cost;
    }

    public function costing($items) {
        $citems = array();
        foreach ($items as $item) {
            $item['batch_number'] = (isset($item['batch_number']) && $item['batch_number']) ? $item['batch_number'] : '';
            $item['option_id'] = (isset($item['option_id']) && $item['option_id']) ? $item['option_id'] : 0;
            $pr = $this->getProductByID($item['product_id']);
            
            if (isset($item['packing_size']) && $item['packing_size'] > 0) {
                $item['unit_quantity'] = $item['unit_quantity'] * $item['packing_size'];
                $item['quantity'] = $item['quantity'] * $item['packing_size'];
                
                // We should also adjust the price per unit, so the total amount remains the same
                // otherwise unit_price * quantity will artificially inflate the value
                $item['unit_price'] = $item['unit_price'] / $item['packing_size'];
                $item['net_unit_price'] = $item['net_unit_price'] / $item['packing_size'];
            }
            
            if ($pr->type == 'standard') {                
            
                if ($pr->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1) {
                    $item['option_id'] = 0;
                }
                
                $ckey = 'p' . $item['product_id'] . 'o' . $item['option_id'];
                if ($item['batch_number']) {
                   $ckey .=  'b' . $item['batch_number'];
                }
            
                // if (isset($citems[$ckey]['aquantity']) && $citems[$ckey]['aquantity'] > 0) {
                if (isset($citems[$ckey])) {
                    $citems[$ckey]['quantity']  += $item['unit_quantity'];
                    $citems[$ckey]['aquantity'] += $item['unit_quantity'];
                    $citems[$ckey]['unit_price'] += $item['unit_price'] * $item['quantity'];
                    $citems[$ckey]['net_unit_price'] += $item['net_unit_price'] * $item['quantity'];
                } else {
                    $citems[$ckey] = $item;            
                    $citems[$ckey]['quantity']  = $item['unit_quantity'];
                    $citems[$ckey]['aquantity'] = $item['unit_quantity'];
                    $citems[$ckey]['unit_price'] = $item['unit_price'] * $item['quantity'];
                    $citems[$ckey]['net_unit_price'] = $item['net_unit_price'] * $item['quantity'];
                }
            
            } 
            elseif ($pr->type == 'combo') {
                $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                            
                foreach ($combo_items as $combo_item) {                    
                    
                    if ($combo_item->type == 'standard') {
                        
                        $cikey = 'p' . $combo_item->id . 'o' . $item['option_id'];
                        if ($item['batch_number']) {
                           $cikey .=  'b' . $item['batch_number'];
                        }
            
                        if (isset($citems[$cikey])) {
            
                            $citems[$cikey]['aquantity'] += ($combo_item->qty * $item['unit_quantity']);
                            $citems[$cikey]['quantity']  += ($combo_item->qty * $item['unit_quantity']);
            
                        } else {
                            $cpr = $this->getProductByID($combo_item->id);
                            if ($cpr->tax_rate) {
                                $cpr_tax = $this->getTaxRateByID($cpr->tax_rate);
                                if ($cpr->tax_method) {
                                    $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / (100 + $cpr_tax->rate));
                                    $net_unit_price = $combo_item->unit_price - $item_tax;
                                    $unit_price = $combo_item->unit_price;
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / 100);
                                    $net_unit_price = $combo_item->unit_price;
                                    $unit_price = $combo_item->unit_price + $item_tax;
                                }
                            } else {
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price;
                            }
                            $cproduct = array('product_id' => $combo_item->id, 'product_name' => $cpr->name, 'product_type' => $combo_item->type, 'quantity' => ($combo_item->qty * $item['unit_quantity']), 'net_unit_price' => $net_unit_price, 'unit_price' => $unit_price, 'warehouse_id' => $item['warehouse_id'], 'item_tax' => $item_tax, 'tax_rate_id' => $cpr->tax_rate, 'tax' => ($cpr_tax->type == 1 ? $cpr_tax->rate . '%' : $cpr_tax->rate), 'option_id' => 0);
            
                            $citems[$cikey] = $cproduct;
                            $citems[$cikey]['aquantity'] = ($combo_item->qty * $item['unit_quantity']);                            
                        }//end else
                    }
                }
            }
        }
            
        $cost = array();
        foreach ($citems as $item) {
            $item['batch_number'] = (isset($item['batch_number']) && $item['batch_number']) ? $item['batch_number'] : '';
            $item['option_id'] = ($item['option_id']) ? $item['option_id'] : 0;
            
            $ikey = 'p' . $item['product_id'] . 'o' . $item['option_id'] ;
            if($item['batch_number']){
                $ikey .=  'b' . $item['batch_number'];
            }
            
            $item['aquantity']      = $citems[$ikey]['aquantity'];
            $item['unit_price']     = $citems[$ikey]['unit_price'] / $citems[$ikey]['aquantity'];
            $item['net_unit_price'] = $citems[$ikey]['net_unit_price'] / $citems[$ikey]['aquantity'];
            
            $cost[] = $this->item_costing($item, TRUE);
        }

        return $cost;
    }

    public function syncQuantity($sale_id = NULL, $purchase_id = NULL, $oitems = NULL, $product_id = NULL, $order_id = NULL, $batch_number = NULL, $challan_id = NULL) {

        if ($sale_id) {
            $sale_items = $this->getAllSaleItems($sale_id);
            foreach ($sale_items as $item) {
                $item->option_id = (isset($item->option_id) && $item->option_id) ? $item->option_id : 0;
                if ($item->product_type == 'standard') {
                    $this->syncProductQty($item->product_id, $item->warehouse_id, $item->batch_number);
                    if ($item->option_id) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id, $item->batch_number);
                    }
                    if (isset($item->batch_no) && $item->batch_no) {
                        // $this->syncProductBatchQty($item->batch_no, $item->product_id, $item->warehouse_id, $item->option_id );
                    }
                } elseif ($item->product_type == 'combo') {
                    $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') {
                            $this->syncProductQty($combo_item->id, $item->warehouse_id, $item->batch_number);
                        }
                    }
                }
            }
        } elseif ($challan_id) {
            $items = $this->getAllChallanItems($challan_id);
            if ($items) {
                foreach ($items as $item) {
                    $item->option_id = (isset($item->option_id) && $item->option_id) ? $item->option_id : 0;
                    if ($item->product_type == 'standard') {
                        $this->syncProductQty($item->product_id, $item->warehouse_id, $item->batch_number);
                        if ($item->option_id) {
                            $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id, $item->batch_number);
                        }
                    } elseif ($item->product_type == 'combo') {
                        $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if ($combo_item->type == 'standard') {
                                $this->syncProductQty($combo_item->id, $item->warehouse_id, $item->batch_number);
                            }
                        }
                    }
                }
            }
        } elseif ($order_id) {
            $items = $this->getAllOrderItems($order_id);
            if ($items) {
                foreach ($items as $item) {
                    $item->option_id = (isset($item->option_id) && $item->option_id) ? $item->option_id : 0;
                    if ($item->product_type == 'standard') {
                        $this->syncProductQty($item->product_id, $item->warehouse_id, $item->batch_number);
                        if ($item->option_id) {
                            $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id, $item->batch_number);
                        }
                    } elseif ($item->product_type == 'combo') {
                        $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if ($combo_item->type == 'standard') {
                                $this->syncProductQty($combo_item->id, $item->warehouse_id, $item->batch_number);
                            }
                        }
                    }
                }
            }
        } elseif ($purchase_id) {

            $purchase_items = $this->getAllPurchaseItems($purchase_id);
            foreach ($purchase_items as $item) {
                $item->option_id = (isset($item->option_id) && $item->option_id) ? $item->option_id : 0;
                $this->syncProductQty($item->product_id, $item->warehouse_id, $item->batch_number);
                if ($item->option_id) {
                    $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id, $item->batch_number);
                    $this->db->get_where(
                        'warehouses_products_variants',
                        array('product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id)
                    )->row_array();
                }
                if ($item->batch_no) {
                    // $this->syncProductBatchQty($item->batch_no, $item->product_id, $item->warehouse_id, $item->option_id );
                }
            }
        } elseif ($oitems) {

            foreach ($oitems as $item) {
                $item->option_id = (isset($item->option_id) && $item->option_id) ? $item->option_id : 0;
                if (isset($item->product_type)) {
                    if ($item->product_type == 'standard') {
                        $this->syncProductQty($item->product_id, $item->warehouse_id);
                        if ($item->option_id) {
                            $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                        }
                    } elseif ($item->product_type == 'combo') {
                        $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if ($combo_item->type == 'standard') {
                                $this->syncProductQty($combo_item->id, $item->warehouse_id);
                            }
                        }
                    }
                } else {
                    $this->syncProductQty($item->product_id, $item->warehouse_id);
                    if ($item->option_id) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                }
            }
        } elseif ($product_id) {
            $warehouses = $this->getAllWarehouses();
            foreach ($warehouses as $warehouse) {
                $this->syncProductQty($product_id, $warehouse->id, $batch_number);
                if ($product_variants = $this->getProductVariants($product_id)) {
                    foreach ($product_variants as $pv) {
                        $this->syncVariantQty($pv->id, $warehouse->id, $product_id);
                    }
                }
            }
        }
    }

    public function getProductVariants($product_id, $array_key_id = false) {
        //$q = $this->db->get_where('product_variants', ['product_id' => $product_id]);
        $this->db->get_where('product_variants', ['product_id' => $product_id]);
        $this->db->select('product_variants.*, manage_variant.variant_name');
        $this->db->from('product_variants');
        $this->db->join('manage_variant', 'product_variants.name = manage_variant.variant_name', 'left'); 
        $this->db->where('product_variants.product_id', $product_id);
        $this->db->order_by('manage_variant.id', 'ASC');
        $q = $this->db->get(); 
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if ($array_key_id) {
                    $data[$row->id] = $row;
                } else {
                    $data[] = $row;
                }
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllSaleItems($sale_id) {
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllOrderItems($order_id) {
        $q = $this->db->get_where('order_items', array('sale_id' => $order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllChallanItems($order_id) {
        $q = $this->db->get_where('delivery_challan_items', array('challan_id' => $order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllPurchaseItems($purchase_id) {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllQuoteItems($quote_id) {
        $q = $this->db->get_where('sma_quote_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncPurchaseItems($data = array()) {
        if (!empty($data)) {
            foreach ($data as $items) {
                foreach ($items as $item) {
                    if (isset($item['pi_overselling'])) {
                        unset($item['pi_overselling']);
                        $batch_number = (isset($item['batch_number']) && !empty($item['batch_number'])) ? $item['batch_number'] : NULL;
                        $option_id = (isset($item['option_id']) && !empty($item['option_id'])) ? $item['option_id'] : 0;
                        $clause = array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'option_id' => $option_id, 'batch_number' => $batch_number);
                        if ($pi = $this->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance + $item['quantity_balance'];
                            $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                        } else {
                            $clause['quantity'] = 0;
                            $clause['item_tax'] = 0;
                            $clause['quantity_balance'] = $item['quantity_balance'];
                            $clause['status'] = 'received';
                            $clause['option_id'] = !empty($clause['option_id']) && is_numeric($clause['option_id']) ? $clause['option_id'] : 0;
                            $clause['batch_number'] = !empty($clause['batch_number']) && ($clause['batch_number']) ? $clause['batch_number'] : NULL;
                            $this->db->insert('purchase_items', $clause);
                        }
                    } elseif (!isset($item['overselling']) || empty($item['overselling'])) {
                        if ($item['inventory']) {
                            $this->db->update('purchase_items', array('quantity_balance' => $item['quantity_balance']), array('id' => $item['purchase_item_id']));
                        }
                    }
                }
            }
            return TRUE;
        }
        return FALSE;
    }

    public function getProductByCode($code) {
        $q = $this->db->select('products.*, categories.id as category_id, categories.name as category_name', FALSE)
                ->join('categories', 'categories.id=products.category_id', 'left')
                ->get_where('products', array('products.code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function check_customer_deposit($customer_id, $amount) {
        $customer = $this->getCompanyByID($customer_id);
        return $customer->deposit_amount >= $amount;
    }

    public function getWarehouseProduct($warehouse_id, $product_id) {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllBaseUnits() {
        //$q = $this->db->get_where("units", array('base_unit' => NULL));
        $q = $this->db->get("units");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUnitsByBUID($base_unit) {
        $this->db->where('id', $base_unit)->or_where('base_unit', $base_unit);
        $q = $this->db->get("units");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUnitByID($id) {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPriceGroupByID($id) {
        $q = $this->db->get_where('price_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductGroupPrice($product_id, $group_id) {
      
        $q = $this->db->get_where('product_prices', array('price_group_id' => $group_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
          
            return $q->row();
        }
        return FALSE;
    }

    public function getAllBrands() {
        $this->db->order_by('name', 'ASC');
        $q = $this->db->get("brands");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getBrandByID($id) {
        $q = $this->db->get_where('brands', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function convertToBase($unit, $value) {
        switch ($unit->operator) {
            case '*':
                return $value / $unit->operation_value;
                break;
            case '/':
                return $value * $unit->operation_value;
                break;
            case '+':
                return $value - $unit->operation_value;
                break;
            case '-':
                return $value + $unit->operation_value;
                break;
            default:
                return $value;
        }
    }

    public function getEshopPaymentDueOrder() {
        $this->db->select('count(*) as cnt');
        $q = $this->db->get_where('sales', array('eshop_sale' => 1, 'sale_status' => 'completed', 'payment_status' => 'due'));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    //--------------------------- Printer Function ------------------------------//
    public function getAllPrinterFields() {
        $q = $this->db->get_where('printer_bill_fields', array('is_deleted' => 0));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPrinterByID($id) {
        $q = $this->db->get_where('printer_bill', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPrinterFieldByID($id) {
        $q = $this->db->get_where('printer_bill_fields', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPrinter() {
        $q = $this->db->get_where('printer_bill', array('is_deleted' => 0));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPrinter($data = array()) {
        if ($this->db->insert('printer_bill', $data)) {
            $cid = $this->db->insert_id();
            return $cid;
        }
        return false;
    }

    public function updatePrinter($id, $data = array()) {
        $this->db->where('id', $id);
        if ($this->db->update('printer_bill', $data)) {
            return $id;
        }
        return false;
    }

    public function defaultPrinterOption($id) {
        if ((int) $id > 0):
            $printer = $this->getPrinterByID($id);

            if (!$printer):
                return false;
            endif;
            $optionArr = array();
            $id_str = $printer->column_id_str;
            $id_arr = explode(',', $id_str);
            foreach ($id_arr as $id) {
                $obj = $this->getPrinterFieldByID($id);
                if ($obj):
                    $optionArr[$id] = $obj;
                endif;
            }
            $printer->optionDetails = $optionArr;
            return $printer;
        endif;
    }

    public function getAllContactGroup($GID = NULL) {

        $q = $this->db->get_where('contact_group');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllContactGroupByID($id) {
        $q = $this->db->get_where('contact_group', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllContactGroupMember($GID) {
        $arr = array();
        if (!empty($GID)):
            $arr['group_id'] = $GID;
        endif;
        $q = $this->db->get_where('contact_group_member', $arr);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllContactGroupMemberDetails($GID) {
        $arr = array();
        if (!empty($GID)):
            $arr['group_id'] = $GID;
        endif;
        $this->db->select('C.name,C.email,C.phone,C.id');
        $this->db->from('contact_group_member GM');
        $this->db->join('contact_group G', 'GM.group_id =  G.id', 'left');
        $this->db->join('companies C', 'GM.customer_id =  C.id', 'left');
        $this->db->where('G.id', $GID);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getContactGroupMemberCount($id) {
        $this->db->select('count(*) as cnt');
        $q = $this->db->get_where('contact_group_member', array('group_id' => $id), 1);
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->cnt;
        }
        return FALSE;
    }

    public function getBirthDayTemplate($type) {
        $q = $this->db->get_where('contact_template', array('is_default' => '1', 'event_type' => 1, 'template_type' => $type), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAnniversaryTemplate($type) {
        $q = $this->db->get_where('contact_template', array('is_default' => '1', 'event_type' => 2, 'template_type' => $type), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getContactTemplateByID($id) {
        $q = $this->db->get_where('contact_template', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllContactTemplate($type = NULL, $isDefault = 0) {
        $typeArr = array();
        $typeArr = in_array((int) $type, array(1, 2, 3)) ? $typeArr['template_type'] = $type : NULL;
       // $typeArr['is_default'] = $isDefault;
        $q = $this->db->get_where('contact_template', $typeArr);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    //---------------------Add Contact Group  --------------------//
    public function addContactGroup($data = array()) {
        if ($this->db->insert('contact_group', $data)) {
            $cid = $this->db->insert_id();

            return $cid;
        }
        return false;
    }

    //---------------------Update Contact Group  --------------------//
    public function updateContactGroup($id, $data = array()) {
        $this->db->where('id', $id);
        if ($this->db->update('contact_group', $data)) {
            return true;
        }
        return false;
    }

    //---------------------Delete Contact Group --------------------//
    public function deleteContactGroup($id) {
        if ($this->db->delete('contact_group', array('id' => $id))) {
            $this->deleteContactGroupMember($id);
            return true;
        }
        return FALSE;
    }

    //---------------------Add Contact Group Member--------------------//
    public function addContactGroupMember($data = array()) {
        if ($this->db->insert('contact_group_member', $data)) {
            $cid = $this->db->insert_id();
            return $cid;
        }
        return false;
    }

    //---------------------Delete Contact Group Member--------------------//
    public function deleteContactGroupMember($group_id, $customer_id = NULL) {

        $whereArr = array('group_id' => $group_id);
        if (!empty((int) $customer_id)):
            $whereArr['customer_id'] = $member_id;
        endif;
        if ($this->db->delete('contact_group_member', $whereArr)) {
            return true;
        }
        return FALSE;
    }

    //---------------------Add Contact Template --------------------//
    public function addContactTemplate($data = array()) {
        if ($this->db->insert('contact_template', $data)) {
            $cid = $this->db->insert_id();

            return $cid;
        }
        return false;
    }

    //---------------------Update Contact Template--------------------//
    public function updateContactTemplate($id, $data = array()) {
        $this->db->where('id', $id);
        if ($this->db->update('contact_template', $data)) {
            return true;
        }
        return false;
    }

    //---------------------Delete Contact Template --------------------//
    public function deleteContactTemplate($id) {
        if ($this->db->delete('contact_template', array('id' => $id))) {
            $this->deleteContactGroupMember($group_id);
            return true;
        }
        return FALSE;
    }

    public function customerName($param) {
        $where = array();
        $phone = isset($param['phone']) && !empty($param['phone']) ? $param['phone'] : NULL;
        $email = isset($param['email']) && !empty($param['email']) ? $param['email'] : NULL;
        if (empty($phone) && empty($email)):
            return false;
        endif;

        if ($phone):
            $where['phone'] = $phone;
        endif;

        if ($email):
            $where['email'] = $email;
        endif;

        $q = $this->db->get_where('companies', $where, 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    //--------------------- GST --------------------//	
    public function getTaxAttr() {
        $q = $this->db->get('tax_attr');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function tax_rate_js() {
        $url = $this->data['assets'] . 'js/tax_rate.js';

        return '<script type="text/javascript" src="' . $url . '"></script>';
    }

    public function add_tax_attr_amount($data) {
        if ($this->db->insert('sales_items_tax', $data)):
            $sale_id = $this->db->insert_id();
            return $sale_id;
        else:
            return false;
        endif;
    }

    public function add_tax_attr_amount_purchase($data) {
        if ($this->db->insert('purchase_items_tax', $data)):
            $purchase_items_tax = $this->db->insert_id();
            return $purchase_items_tax;
        else:
            return false;
        endif;
    }

    public function taxAttrPercentageBySaleTaxId($taxCode, $taxID, $sale_id) {
        $this->db->select("it.attr_per as percentage");
        $this->db->where_in('it.sale_id', array((int) $sale_id));
        $this->db->where('i.tax_rate_id', $taxID);
        $this->db->where('it.attr_code', $taxCode);
        $this->db->from('sales_items_tax it');
        $this->db->join('sale_items i', 'it.item_id=i.id', 'left');
        $this->db->group_by('it.attr_code');
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->percentage;
        }
        return FALSE;
    }

    public function taxAttrPercentageByPurchaseTaxId($taxCode, $taxID, $purchase_id) {
        $this->db->select("it.attr_per as percentage");
        $this->db->where_in('it.purchase_id', array((int) $purchase_id));
        $this->db->where('i.tax_rate_id', $taxID);
        $this->db->where('it.attr_code', $taxCode);
        $this->db->from('purchase_items_tax it');
        $this->db->join('purchase_items i', 'it.item_id=i.id', 'left');
        $this->db->group_by('it.attr_code');
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->percentage;
        }
        return FALSE;
    }

    public function isGstSale($id) {
        $q = $this->db->get_where('sales_items_tax', array('sale_id' => $id), 1);
        // echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return FALSE;
    }

    public function isGstPurchase($id) {
        $q = $this->db->get_where('purchase_items_tax', array('purchase_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return FALSE;
    }

    public function add_tax_attr_amount_quote($data) {
        if ($this->db->insert('quote_items_tax', $data)):
            $quote_items_tax = $this->db->insert_id();
            return $quote_items_tax;
        else:
            return false;
        endif;
    }

    public function taxAttrPercentageByQuoteTaxId($taxCode, $taxID, $quote_id) {
        $this->db->select("it.attr_per as percentage");
        $this->db->where_in('it.quote_id', array((int) $quote_id));
        $this->db->where('i.tax_rate_id', $taxID);
        $this->db->where('it.attr_code', $taxCode);
        $this->db->from('quote_items_tax it');
        $this->db->join('quote_items i', 'it.item_id=i.id', 'left');
        $this->db->group_by('it.attr_code');
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->percentage;
        }
        return FALSE;
    }

    /* 24-10-2019  total amount show */

    public function taxAttrAmtByQuoteTaxId($taxCode, $taxID, $quote_id) {
        $this->db->select("ROUND(it.tax_amount,2) as amt");
        $this->db->where_in('it.quote_id', array((int) $quote_id));
        $this->db->where('i.tax_rate_id', $taxID);
        $this->db->where('it.attr_code', $taxCode);
        $this->db->from('quote_items_tax it');
        $this->db->join('quote_items i', 'it.item_id=i.id', 'left');
        $this->db->group_by('it.attr_code');
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->amt;
        }
        return FALSE;
    }

    /**/

    public function isGstQuote($id) {
        $q = $this->db->get_where('quote_items_tax', array('quote_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return FALSE;
    }

    public function resetExpirePromos() {

        $expirePromos = $this->geteExpirePromos();
        if (!$expirePromos):
            return false;
        elseif (!is_array($expirePromos)):
            return false;
        endif;
        $pro = 0;
        foreach ($expirePromos as $prd) {
            $this->db->update('products', array('promotion' => 0), array('id' => $prd->id, 'promotion' => 1));
            $pro++;
        }
        return true;
    }

    private function geteExpirePromos() {

        $ci = get_instance();
        $config = $ci->config;
        $IST_OFFSET = isset($config->config['IST_OFFSET']) ? $config->config['IST_OFFSET'] : 0;
        $datetime = date("Y-m-d H:i:s", time() + $IST_OFFSET);
        $this->db->select('id');
        $q = $this->db->get_where('products', array('promotion' => 1, 'end_date !=' => NULL, 'end_date <=' => $datetime));


        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllStates() {
        $q = $this->db->get('state_master');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStateFromStateCode($code) {
        $q = $this->db->get_where('state_master', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->name;
        }
        return FALSE;
    }

    public function getStateCodeFromName($name) {

        if (empty($name)) {
            return NULL;
        }

        $q = $this->db->get_where('state_master', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->code;
        }
        return FALSE;
    }
    public function getGstStateCodeFromName($name)
    {
        if (empty($name)) {
            return NULL;
        }

        $q = $this->db->get_where('state_master', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return isset($res->gst_state_code) ? $res->gst_state_code : NULL;
        }

        return FALSE;
    }

    public function addCronMember($data) {
        $dt = date("Y-m-d H:i:s");
        if (count($data) > 0) {
            $this->db->delete('cron_user', array('1' => '1'));
            foreach ($data as $key => $dataVal) {

                if ($this->db->insert('cron_user', array('customer_id' => $dataVal['customer_id'], 'postdate' => $dt))) {
                    $id = $this->db->insert_id();
                }
            }
            return true;
        }
        return FALSE;
    }

    public function getAllCronCustomer() {
        $this->db->select("customer_id");
        $q = $this->db->get_where('cron_user');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row->customer_id;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllDivision() {
        $q = $this->db->get("division");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getDivisionByID($id) {
        $q = $this->db->get_where('division', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllRestaurantTables() {
      $q = $this->db->where(['parent_id'=>'0'])->get('restaurant_tables');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if(in_array($row->id, $this->get_booked_tables())){
                    $suspendData = $this->getSuspend($row->id);
                    $row->status = "Booked";
                    $row->suspended_id = $suspendData['id']; // $this->getSuspend($row->id);
                    $row->suspended_note = $suspendData['table_name'];
                    
                }else{
                    $row->status = "Available";
                }
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
     public function getAllRestaurantTablesByUser($user_id = null) {
        $q = $this->db->where(['parent_id'=>'0'])->get('restaurant_tables');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if(in_array($row->id, $this->get_booked_tables())){
                    $suspendData = $this->getSuspendByUser($row->id, $user_id);
                    if ($suspendData) {
                        $row->status = "Booked";
                        $row->suspended_id = $suspendData['id'];
                        $row->suspended_note = $suspendData['table_name'];
                    } else {
                        $row->status = "Available";
                    }
                }else{
                    $row->status = "Available";
                }
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTableData($tablename) {

        $q = $this->db->get($tablename);

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTabledataCondition() { //Note : 0. Table name, 1. get Field, 2. Condition  3. order Field, 4. Order Conditon 
        $getdata = func_get_args();

        $this->db->select($getdata[1]);
        if ($getdata[2]) {
            $this->db->where($getdata[2]);
        }
        if ($getdata[3] && $getdata[4]) {
            $this->db->order_by($getdata[3], $getdata[4]);
        }
        $q = $this->db->get($getdata[0]);
        if ($q->num_rows() > 0) {

            return $q->result();
        }
        return FALSE;
    }

    public function getOfferCategories() {
        $q = $this->db->select('id,offer_keyword,offer_category')->where(['is_active' => 1, 'is_delete' => 0])->get('offers_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    // Get Themes 17-04-19
    public function getpostheme() {
        return $this->db->select('theme_name,theme_label')->where(['is_active' => '1', 'is_delete' => '0'])->order_by('theme_name', 'ASC')->get('sma_themes')->result();
    }

    // End Get Themes
    public function getCompanyDetailsByGroupID($id) { // call this function pos/index
        $q = $this->db->get_where('companies', array('group_id' => $id));
        return $q->result_array();
    }

    /* 8-11-2019 */

    public function customerDepositAmt($customer_id) {
        $customer = $this->getCompanyByID($customer_id);
        return $customer->deposit_amount;
    }

    /* 20-02-2020 for deleted data */

    public function getTableDatas($TableName, $columnId, $DeletedId) {
        $q = $this->db->get_where($TableName, array($columnId => $DeletedId));
        return $q->result_array();
    }

    public function insertTableData($Data) {
        if ($this->db->insert('deleted_data', $Data)) {
            return true;
        }
        return false;
    }

    public function deleteTableDataById($TableName, $ColumnId, $id) {
        if ($this->db->delete('deleted_data', array('table_name' => $TableName, $ColumnId => $id))) {
            return true;
        }
        return FALSE;
    }

    /* end 20-02-2020 for deleted data */

    //03-04-2020 eshop order
    public function order_costing($items) {
        $citems = array();
        foreach ($items as $item) {
            $item['option_id'] = (isset($item['option_id']) && $item['option_id']) ? $item['option_id'] : 0;
            $pr = $this->getProductByID($item['product_id']);
            if ($pr->type == 'standard') {

                if ($pr->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1) {
                    $item['option_id'] = 0;
                } 
                
               /* if (isset($citems['p' . $item['product_id'] . 'o' . $item['option_id']])) {
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] += $item['quantity'];
                } else {
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']] = $item;
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] = $item['quantity'];
                }*/

                if (isset($citems[$ckey]['aquantity']) && $citems[$ckey]['aquantity'] > 0) {
                    $citems[$ckey]['quantity']  += $item['quantity'];
                    $citems[$ckey]['aquantity'] += $item['quantity'];
                    $citems[$ckey]['unit_price'] += $item['unit_price'] * $item['quantity'];
                    $citems[$ckey]['net_unit_price'] += $item['net_unit_price'] * $item['quantity'];
                } else {
                    $citems[$ckey] = $item;            
                    $citems[$ckey]['quantity']  = $item['quantity'];
                    $citems[$ckey]['aquantity'] = $item['quantity'];
                    $citems[$ckey]['unit_price'] = $item['unit_price'] * $item['quantity'];
                    $citems[$ckey]['net_unit_price'] = $item['net_unit_price'] * $item['quantity'];
                }
            } elseif ($pr->type == 'combo') {
                $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                foreach ($combo_items as $combo_item) {
                    if ($combo_item->type == 'standard') {
                        if (isset($citems['p' . $combo_item->id . 'o' . $item['option_id']])) {
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] += ($combo_item->qty * $item['quantity']);
                        } else {
                            $cpr = $this->getProductByID($combo_item->id);
                            if ($cpr->tax_rate) {
                                $cpr_tax = $this->getTaxRateByID($cpr->tax_rate);
                                if ($cpr->tax_method) {
                                    $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / (100 + $cpr_tax->rate));
                                    $net_unit_price = $combo_item->unit_price - $item_tax;
                                    $unit_price = $combo_item->unit_price;
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / 100);
                                    $net_unit_price = $combo_item->unit_price;
                                    $unit_price = $combo_item->unit_price + $item_tax;
                                }
                            } else {
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price;
                            }
                            $cproduct = array('product_id' => $combo_item->id, 'product_name' => $cpr->name, 'product_type' => $combo_item->type, 'quantity' => ($combo_item->qty * $item['unit_quantity']), 'net_unit_price' => $net_unit_price, 'unit_price' => $unit_price, 'warehouse_id' => $item['warehouse_id'], 'item_tax' => $item_tax, 'tax_rate_id' => $cpr->tax_rate, 'tax' => ($cpr_tax->type == 1 ? $cpr_tax->rate . '%' : $cpr_tax->rate), 'option_id' => NULL);
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']] = $cproduct;
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] = ($combo_item->qty * $item['quantity']);
                        }
                    }
                }
            }
        }
        // $this->sma->print_arrays($combo_items, $citems);
        $cost = array();
        foreach ($citems as $item) {
            $item['option_id'] = (isset($item['option_id']) && $item['option_id']) ? $item['option_id'] : 0;
            $item['aquantity'] = $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'];
            $cost[] = $this->order_item_costing($item, TRUE);
        }
        return $cost;
    }

    public function order_item_costing($item, $pi = NULL) {
        $item_quantity = $pi ? $item['aquantity'] : $item['quantity'];
        if (!isset($item['option_id']) || empty($item['option_id']) || $item['option_id'] == 'null') {
            $item['option_id'] = 0;
        }

        if ($this->Settings->accounting_method != 2 && !$this->Settings->overselling) {

            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $unit = $this->getUnitByID($item['product_unit_id']);
                    $item['net_unit_price'] = $this->convertToBase($unit, $item['net_unit_price']);
                    $item['unit_price'] = $this->convertToBase($unit, $item['unit_price']);
                    $cost = $this->calculateOrderCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price = $combo_item->unit_price;
                            } else {
                                $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price = $combo_item->unit_price;
                        }
                        if ($pr->type == 'standard') {
                            $cost[] = $this->calculateOrderCost($pr->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $pr->name, NULL, $item_quantity);
                        } else {
                            $cost[] = array(array('date' => date('Y-m-d'), 'product_id' => $pr->id, 'order_item_id' => 'order_items.id', 'purchase_item_id' => NULL, 'quantity' => ($combo_item->qty * $item['quantity']), 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $combo_item->unit_price, 'sale_unit_price' => $combo_item->unit_price, 'quantity_balance' => NULL, 'inventory' => NULL));
                        }
                    }
                } else {
                    $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'order_item_id' => 'order_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'order_item_id' => 'order_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
            }
        } else {

            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $cost = $this->calculateOrderAVCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $cost = $this->calculateOrderAVCost($combo_item->id, $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], ($combo_item->qty * $item['quantity']), $item['product_name'], $item['option_id'], $item_quantity);
                    }
                } else {
                    $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'order_item_id' => 'order_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'order_item_id' => 'order_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
            }
        }
        return $cost;
    }

    public function calculateOrderCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id = 0, $item_quantity) {
        $option_id = $option_id ? $option_id : 0;

        $real_item_qty = $quantity;
        $quantity = $item_quantity;
        $balance_qty = $quantity;

        $unit_quantity = 1;
        $product = $this->getProductByID($product_id);
        if ($this->Settings->attributes == 1 && $product->storage_type == 'packed') {
            $pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id);
        } else {
            if ($product->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1) {
                $variants = $this->getVerientById($option_id);
                $unit_quantity = $variants['unit_quantity'] ? $variants['unit_quantity'] : 1;
                $item_quantity = $real_item_qty * $unit_quantity;
            }
            $option_id = 0; //No Variant stocks available for loose products 
            $pis = $this->getPurchasedItems($product_id, $warehouse_id);
        }//end else

        if ($pis) {
            foreach ($pis as $pi) {
                if ($pi->quantity_balance <= 0) {
                    continue;
                }//Avoide Zero Quantity Purchase Records.
                $cost_row = NULL;
                if (!empty($pi) && $balance_qty <= $quantity && $quantity > 0) {
                    $purchase_unit_cost = $pi->unit_cost ? $pi->unit_cost : ($pi->net_unit_cost + ($pi->item_tax / $pi->quantity));
                    if ($pi->quantity_balance >= $quantity && $quantity > 0) {
                        $balance_qty = $pi->quantity_balance - $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'order_item_id' => 'order_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                        $quantity = 0;
                    } elseif ($quantity > 0) {
                        $quantity = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'order_item_id' => 'order_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id);
                    }
                }
                $cost[] = $cost_row;
                if ($quantity == 0) {
                    break;
                }
            }
        }
        if ($quantity > 0) {
            $msg = sprintf(lang("quantity_out_of_stock_for_%s"), ($pi->product_name ? $pi->product_name : $product_name));
            $this->session->set_flashdata('error', $msg);
            if (!$this->input->is_ajax_request()) {
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $response['status'] = 'error';
                $response['message'] = $msg;
                echo json_encode($response);
                exit;
            }
        }
        return $cost;
    }

    public function calculateOrderAVCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id = 0, $item_quantity) {
        $option_id = $option_id ? $option_id : 0;
        $real_item_qty = $quantity;
        $wp_details = $this->getWarehouseProduct($warehouse_id, $product_id);
        //  $product_avg_cost = $this->db->select('cost')->where('id', $product_id)->get('products')->row();

        $unit_quantity = 1;
        $product = $this->getProductByID($product_id);
        $product_avg_cost = $product->cost;
        if ($this->Settings->attributes == 1 && $product->storage_type == 'packed') {
            $pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id, $batch_number);
        } else {
            if ($product->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1) {
                $variants = $this->getVerientById($option_id);
                $unit_quantity = $variants['unit_quantity'] ? $variants['unit_quantity'] : 1;
                $item_quantity = $real_item_qty * $unit_quantity;
            }
            $option_id = 0; //No Variant stocks available for loose products 
            $pis = $this->getPurchasedItems($product_id, $warehouse_id);
        }//end else


        if ($pis) {
            $cost_row = array();
            $quantity = $item_quantity;
            $balance_qty = $quantity;
            $avg_net_unit_cost = $wp_details->avg_cost;
            $avg_unit_cost = $wp_details->avg_cost;
            foreach ($pis as $pi) {
                if ($pi->quantity_balance <= 0) {
                    continue;
                }//Avoide Zero Quantity Purchase Records.
                if (!empty($pi) && $pi->quantity_balance > 0 && $balance_qty <= $quantity && $quantity > 0) {
                    if ($pi->quantity_balance >= $quantity && $quantity > 0) {
                        $balance_qty = $pi->quantity_balance - $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'order_item_id' => 'order_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                        $quantity = 0;
                    } elseif ($quantity > 0) {
                        $quantity = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'order_item_id' => 'order_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id);
                    }
                }
                if (empty($cost_row)) {
                    break;
                }
                $cost[] = $cost_row;
                if ($quantity == 0) {
                    break;
                }
            }
        }
        if ($quantity > 0 && !$this->Settings->overselling) {
            $msg = sprintf(lang("quantity_out_of_stock_for_%s"), ($pi->product_name ? $pi->product_name : $product_name));
            $this->session->set_flashdata('error', $msg);
            if (!$this->input->is_ajax_request()) {
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $response['status'] = 'error';
                $response['message'] = $msg;
                echo json_encode($response);
                exit;
            }
        } elseif ($quantity > 0) {
            $cost[] = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'order_item_id' => 'order_items.id', 'purchase_item_id' => NULL, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => is_array($wp_details) ? $wp_details->avg_cost : $product_avg_cost->cost, 'purchase_unit_cost' => is_array($wp_details) ? $wp_details->avg_cost : $product_avg_cost->cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => NULL, 'overselling' => 1, 'inventory' => 1);
            $cost[] = array('pi_overselling' => 1, 'product_id' => $product_id, 'quantity_balance' => (0 - $quantity), 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
        }
        return $cost;
    }

    /* 15-5-2020 New CSI TAX show Function */

    public function getSItemsTaxes($saleId, $taxRate) {

        $query = "SELECT gst_rate,sum(cgst) AS CGST,sum(sgst) AS SGST,sum(igst) AS IGST
            FROM  " . $this->db->dbprefix('sale_items') . "  WHERE `sale_id` =  '$saleId' AND  gst_rate = '$taxRate' GROUP BY `gst_rate` ";

        $q = $this->db->query($query, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTotalItemsTaxes($saleId) {

        $query = "SELECT sum(cgst) AS CGST,sum(sgst) AS SGST,sum(igst) AS IGST
            FROM  " . $this->db->dbprefix('sale_items') . "  WHERE `sale_id` =  '$saleId' ";

        $q = $this->db->query($query, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchaseItemsTaxes($purchaseId, $taxRate) {

        $query = "SELECT gst_rate,sum(cgst) AS CGST,sum(sgst) AS SGST,sum(igst) AS IGST
            FROM  " . $this->db->dbprefix('purchase_items') . "  WHERE `purchase_id` =  '$purchaseId' AND  gst_rate = '$taxRate' GROUP BY `gst_rate` ";

        $q = $this->db->query($query, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchaseTotalItemsTaxes($purchaseId) {

        $query = "SELECT sum(cgst) AS CGST,sum(sgst) AS SGST,sum(igst) AS IGST
            FROM  " . $this->db->dbprefix('purchase_items') . "  WHERE `purchase_id` =  '$purchaseId' ";

        $q = $this->db->query($query, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getQuoteItemsTaxes($quoteId, $taxRate) {

        $query = "SELECT gst_rate,sum(cgst) AS CGST,sum(sgst) AS SGST,sum(igst) AS IGST
            FROM  " . $this->db->dbprefix('quote_items') . "  WHERE `quote_id` =  '$quoteId' AND  gst_rate = '$taxRate' GROUP BY `gst_rate` ";

        $q = $this->db->query($query, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getQuoteTotalItemsTaxes($quoteId) {

        $query = "SELECT sum(cgst) AS CGST,sum(sgst) AS SGST,sum(igst) AS IGST
            FROM  " . $this->db->dbprefix('quote_items') . "  WHERE `quote_id` =  '$quoteId' ";

        $q = $this->db->query($query, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getReturnSaleTaxes($saleId) {

        $query = "SELECT sum(cgst) AS CGST,sum(sgst) AS SGST,sum(igst) AS IGST
            FROM  " . $this->db->dbprefix('sales') . "  WHERE `sale_id` =  '$saleId' ";

        $q = $this->db->query($query, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSaleTaxes($saleId) {

        $query = "SELECT sum(cgst) AS CGST,sum(sgst) AS SGST,sum(igst) AS IGST
            FROM  " . $this->db->dbprefix('sales') . "  WHERE `id` =  '$saleId' ";

        $q = $this->db->query($query, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getOItemsTaxes($saleId, $taxRate) {

        $query = "SELECT gst_rate,sum(cgst) AS CGST,sum(sgst) AS SGST,sum(igst) AS IGST
            FROM  " . $this->db->dbprefix('order_items') . "  WHERE `sale_id` =  '$saleId' AND  gst_rate = '$taxRate' GROUP BY `gst_rate` ";

        $q = $this->db->query($query, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTotalOrderItemsTaxes($saleId) {

        $query = "SELECT sum(cgst) AS CGST,sum(sgst) AS SGST,sum(igst) AS IGST
            FROM  " . $this->db->dbprefix('order_items') . "  WHERE `sale_id` =  '$saleId' ";

        $q = $this->db->query($query, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getVerientById($variant_id = null) {

        $q = $this->db->get_where('product_variants', ['id' => $variant_id]);

        if ($q->num_rows() > 0) {

            $data = $q->result();

            return (array) $data[0];
        }

        return false;
    }

    /**
     * 
     * @param type $groupname
     * @param type $mobileno
     * @return boolean
     */
    public function checkMobileno($groupname, $mobileno) {
        $checkmobileno = $this->db->where(['group_name' => $groupname, 'phone' => $mobileno])->get('companies')->row();
        if ($checkmobileno) {
            return $checkmobileno;
        } else {
            return FALSE;
        }
    }

    /**
     * Get Country
     * @return boolean
     */
    public function getCountry() {
        $getcountry = $this->db->get('country_master')->result();
        if ($getcountry) {
            return $getcountry;
        } else {
            return false;
        }
    }
    public function getCountryDataByCountryName($countryname) {
       $getcountry = $this->db->where('name', $countryname)->get('country_master')->result();
        if ($getcountry) {
            return $getcountry;
        } else {
            return false;
        }
    }

    /**
     * Add Country
     */
    public function addCountry($data) {
        $this->db->insert('country_master', $data);
    }

    /**
     * 
     * @param type $country
     * @return boolean
     */
    public function getstates($country) {
        $getcoutry = $this->db->select('id')->where(['name' => $country])->get('country_master')->row();
        if (!$getcoutry || empty($getcoutry->id)) {
            return false;
        }

        $statedata = $this->db->where(['country_id' => $getcoutry->id])->get('state_master')->result();
        if ($statedata) {
            return $statedata;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $countryname
     * @return type
     */
    public function getCountryId($countryname) {
        $result = $this->db->select('id')->where(['name' => $countryname])->get('country_master')->row();
        return $result->id;
    }

    /**
     * 
     * @param type $data
     */
    public function addstate($data) {
        $this->db->insert('state_master', $data);
    }

    /*     * *02-09-2020** */

    public function getCreditNoteByID($id) {
        $q = $this->db->get_where('credit_note', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCreditNoteByNO($no) {
        $q = $this->db->get_where('credit_note', array('card_no' => $no), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /*     * *02-09-2020** */

    function showVariantFilter() {
        return "'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'";
    }

    /*     * *02-11-2020** */

    function addUserActionLog($data) {
        if ($this->db->insert('user_action_logs', $data)) {
            $cid = $this->db->insert_id();
            return $cid;
        }
        return false;
    }

    function getProductBatchData($batch_number, $product_id, $item_option = 0) {

        $this->db->select("id, batch_no, product_id, option_id, cost, price, mrp, expiry_date");
        $this->db->where(array('is_active' => 1, 'is_delete' => 0,));

        if ($batch_number) {
            $this->db->where('batch_no', $batch_number);
        }
        if ($product_id) {
            $this->db->where('product_id', $product_id);
        }
        if ($item_option) {
            $this->db->where('option_id', $item_option);
        }
        $q = $this->db->get('product_batches', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    function addBatchInfo($data) {

        if ($this->db->insert('product_batches', $data)) {
            return TRUE;
        }
        return FALSE;
    }

    function getProductStorageType($product_id) {

        $row = $this->db->select('storage_type')->where('id', $product_id)->get('products')->row();
        return $row->storage_type;
    }

    public function getWarehouseProductStocks($warehouse_id, $product_id, $batchStock = TRUE) {

        $this->db->select('id, product_id, option_id, batch_number, quantity_balance');

        /* if ($batchStock) {
          $this->db->group_by('batch_number , option_id');
          } else {
          $this->db->group_by('option_id');
          } */

        if (!$this->Settings->overselling) {
            $this->db->where('(purchase_id IS NOT NULL OR transfer_id IS NOT NULL OR adjustment_id IS NOT NULL)');
        }

        $this->db->where(['product_id' => $product_id, 'warehouse_id' => $warehouse_id]);

        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();

        $q = $this->db->get_where('purchase_items');

        if ($q->num_rows() > 0) {

            foreach (($q->result()) as $row) {

                $product_option = $product_id . "_" . $row->option_id;
                if ($row->batch_number) {
                    $data[$product_option]['batch_stocks'][$row->batch_number] += $row->quantity_balance;
                }

                $data[$product_option]['variant_stocks'] += $row->quantity_balance;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCustomeFieldsLabel($cf_type = null) {

        $default_types = array('product', 'customer', 'supplier', 'biller', 'employee');
        $types = $cf_type ? array($cf_type) : $default_types;
        $data = array();

        foreach ($types as $type) {
            $row = (object) array('type' => $type);
            for ($i = 1; $i <= 6; $i++) {
                $row->{'cf' . $i} = null;
                $row->{'cf' . $i . '_input_type'} = null;
                $row->{'cf' . $i . '_input_options'} = null;
            }
            $data[$type] = $row;
        }

        if ($cf_type) {
            $q = $this->db->where('type', $cf_type)->get('settings_custom_fields');
        } else {
            $q = $this->db->get('settings_custom_fields');
        }

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[$row->type] = $row;
            }
        }

        return $data;
    }

    public function getCustomerCustomFieldsForDisplay($customer, $custome_fields) {
        $fields = array();
        if (!$customer) {
            return $fields;
        }

        if (!$custome_fields) {
            $cfields = $this->getCustomeFieldsLabel('customer');
            $custome_fields = isset($cfields['customer']) ? $cfields['customer'] : null;
        }

        // Legacy save bug fallback: older POS flows stored cf1/cf2/cf3 values into cf6/cf4/cf5.
        $legacy_fallback_map = array(
            1 => 6,
            2 => 4,
            3 => 5,
        );

        for ($i = 1; $i <= 6; $i++) {
            $cf_key = 'cf' . $i;
            $label = !empty($custome_fields->$cf_key) ? trim(rtrim((string) $custome_fields->$cf_key, '*')) : '';
            if ($label === '') {
                // Do not print unconfigured custom fields.
                continue;
            }

            $value = isset($customer->$cf_key) ? trim((string) $customer->$cf_key) : '';

            if ($value === '' && isset($legacy_fallback_map[$i])) {
                $legacy_key = 'cf' . $legacy_fallback_map[$i];
                $legacy_value = isset($customer->$legacy_key) ? trim((string) $customer->$legacy_key) : '';
                if ($legacy_value !== '') {
                    $value = $legacy_value;
                }
            }

            if ($value === '') {
                continue;
            }

            $fields[] = array(
                'label' => $label,
                'value' => $value,
            );
        }

        return $fields;
    }

    public function getCustomFieldLabelText($custome_fields, $index, $fallback_lang_key = '') {
        $cf_key = 'cf' . (int) $index;
        $label = !empty($custome_fields->$cf_key) ? trim(rtrim((string) $custome_fields->$cf_key, '*')) : '';
        if ($label !== '') {
            return $label;
        }

        return $fallback_lang_key !== '' ? lang($fallback_lang_key) : '';
    }

    public function getProductCostings(array $where_clause) {
        
        $this->db->where($where_clause);

        $q = $this->db->get('costing');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
 
 public function getProductOptionsByShapeId($id, $product_id)
    {
			
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id, 'id'=>$id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
   
    /**
     * 
     * @return type
     */        
    // function get_booked_tables(){
    //     $query = "SELECT sb.*
    //     FROM ".$this->db->dbprefix('suspended_bills')." sb
    //     LEFT JOIN ".$this->db->dbprefix('restaurant_tables')." t ON t.id = sb.table_id
    //     WHERE sb.date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    //     ";

    //     $q = $this->db->query($query);
    //     $booked_tables = array();
    //     if ($q->num_rows() > 0) {
    //         $result = $q->result();
    //         foreach ($result as $key=>$row) {
    //             $booked_tables[] = $row->table_id;
    //         }
    //     }   
    //     return $booked_tables;
    // }       
    function get_booked_tables(){
        $query = "SELECT sb.*
            FROM ".$this->db->dbprefix('suspended_bills')." sb
            LEFT JOIN ".$this->db->dbprefix('restaurant_tables')." t ON t.id = sb.table_id";
        $q = $this->db->query($query);
        $booked_tables = array();
        if ($q->num_rows() > 0) {
            $result = $q->result();
            foreach ($result as $key=>$row) {
                $booked_tables[] = $row->table_id;
            }
        }   
        return $booked_tables;
    }       
    
    
    /**
     * 
     * @param type $tableId
     * @return boolean
     * @param type $tableId
     * @return boolean
     */
    // public function getSuspend($tableId){
       
    //     $query = "SELECT sb.*
    //     FROM ".$this->db->dbprefix('suspended_bills')." sb
    //     LEFT JOIN ".$this->db->dbprefix('restaurant_tables')." t ON t.id = sb.table_id
    //     WHERE sb.date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) and sb.table_id = $tableId
    //     ";
         
    //     $q = $this->db->query($query);
    //     if ($q->num_rows() > 0) {
    //         $result = $q->row();
           
    //         $response = [
    //             'id' => $result->id,
    //             'table_name' => $result->suspend_note                
    //         ];
    //         //return $result->id;
    //         return $response;
    //     }
    //     return false;
    // }
    public function getSuspend($tableId){
        $query = "SELECT sb.*
            FROM ".$this->db->dbprefix('suspended_bills')." sb
            LEFT JOIN ".$this->db->dbprefix('restaurant_tables')." t ON t.id = sb.table_id
            WHERE sb.table_id = $tableId
        ";
         
        $q = $this->db->query($query);
        if ($q->num_rows() > 0) {
            $result = $q->row();
           
            $response = [
                'id' => $result->id,
                'table_name' => $result->suspend_note                
            ];
            //return $result->id;
            return $response;
        }
        return false;
    }
    
    /**
     * Get Restaurant Tables List
     * @return type
     */
    public function getRestaurantTables(){
       $tables = $this->db->get('restaurant_tables')->result();
       return $tables;
    }
      
     /**
     * Get Customer Groups
     * @return type
     */
    public function getcustomerGroup(){
        $groups = $this->db->get('customer_groups')->result();
        return $groups;
    }      
     //Warehouse price group
     public function getWarehousesID($id) {

        if (is_numeric($id)) {
            
            $q = $this->db->get_where('warehouses', ['id' => $id], 1);
            if ($q->num_rows() > 0) {
                $data = $q->row();
                return $data;
            }
        } else {
            
            return $this->getWarehouseByIDs($id);
        }  
    }
    public function calculateTax($rows, $product_price, $order_quantity, $interStateTax) {
    
        // Tax calculation
        if (isset($rows)) {
            $tax_details = $this->site->getTaxRateByID($rows->tax_rate);
            $tax = $tax_details->rate . "%";
    
            if ($tax_details->rate != 0) {
                if ($tax_details->type == 1) {
                    // Exclusive tax method calculation
                    if ($rows && $rows->tax_method == 1) {
                        $item_tax = $this->sma->formatDecimal(($product_price * $tax_details->rate) / 100, 4);
                        $unit_price = $this->sma->formatDecimal($product_price + $item_tax, 4);
                    } else {
                        // Inclusive tax method calculation
                        $item_tax = $this->sma->formatDecimal(($product_price * $tax_details->rate) / (100 + $tax_details->rate), 4);
                        $unit_price = $this->sma->formatDecimal($product_price, 4);
                        $item_net_price = $this->sma->formatDecimal($product_price - $item_tax, 4);
                    }
                } elseif ($tax_details->type == 2) {
                    if ($rows && $rows->tax_method == 1) {
                        $item_tax = $this->sma->formatDecimal(($product_price * $tax_details->rate) / 100, 4);
                        $unit_price = $this->sma->formatDecimal($product_price + $item_tax, 4);
                    } else {
                        $item_tax = $this->sma->formatDecimal(($product_price * $tax_details->rate) / (100 + $tax_details->rate), 4);
                        $unit_price = $this->sma->formatDecimal($product_price, 4);
                        $item_net_price = $this->sma->formatDecimal($product_price - $item_tax, 4);
                    }
                }
            } else {
                $unit_price = $this->sma->formatDecimal($product_price, 4);
            }
    
            $item_tax = $item_tax ?: 0;
            $pr_item_tax = $this->sma->formatDecimal($item_tax * $order_quantity, 4);
        } else {
            $unit_price = $product_price;
        } // Tax calculation end
    
        $product_tax = $pr_item_tax;
        $subtotal = $this->sma->formatDecimal(($unit_price * $order_quantity), 4);
        
        if ($interStateTax) {
            $item_gst = $tax_details->rate;
            $item_cgst = 0;
            $item_sgst = 0;
            $item_igst = $pr_item_tax;
        } else {
            $item_gst = $this->sma->formatDecimal($tax_details->rate / 2, 4);
            $item_cgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
            $item_sgst = $this->sma->formatDecimal($pr_item_tax / 2, 4);
            $item_igst = 0;
        }

        return [ 'unit_price' => $unit_price, 'item_tax' => $item_tax, 'tax' => $tax, 'pr_item_tax' => $pr_item_tax, 'subtotal' => $subtotal,         'item_cgst' => $calculated['item_cgst'], 'item_sgst' => $calculated['item_sgst'], 'item_igst' => $calculated['item_igst'] ];
    } 

    // Return 

    public function getProductsDataById($id) {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function get_all_season() {
        $q = $this->db->get('seasons');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function warehouseById($id) {

        if (is_numeric($id)) {
           
            $q = $this->db->get_where('categories', ['id' => $id], 1);
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data = $row->warehouse_id;
                }
                return $data;
            }
            return FALSE;
            // if ($q->num_rows() > 0) { 
            //     $data = $q->row();
            //     return $data->warehouse_id;
            // }
        } else {
            
            return $this->getWarehouseByIDs($id);
        }  
    }
    public function checkMobilenoForCheerio($mobileno) { 
        $checkmobileno = $this->db->where('phone', $mobileno)->get('companies')->row(); 
        if ($checkmobileno) {
            return $checkmobileno;  // Mobile number exists
        } else {
            return FALSE;  // Mobile number doesn't exist
        }
    }

    public function getProductTypeByID($id){ //call this function pos/index
        $q = $this->db->get_where('product_type', array('id' => $id));
		return $q->result_array();
    }
      /////////////////////////// Print Barcode //////////////////////////////////////////

      public function getProductByIDwithBatchAndWarehouse($pid, $warehouseId) {
        $this->db->select('products.*, product_batches.batch_no, warehouses_products.quantity AS warehouse_quantity');
        $this->db->from('products');
        $this->db->join('product_batches', 'products.id = product_batches.product_id', 'left');
        $this->db->join('warehouses_products', 'products.id = warehouses_products.product_id AND warehouses_products.warehouse_id = ' . (int)$warehouseId, 'left'); 
        $this->db->where('products.id', $pid);
        $this->db->order_by('product_batches.id', 'ASC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    //////////////////////////////////Customer family relation/////////////////////////
    public function getstatesCRM($country) {
        $getcoutry = $this->db->select('id')->where(['name' => $country])->get('country_master')->row();
        if (!$getcoutry || empty($getcoutry->id)) {
            return false;
        }

        $statedata = $this->db->where(['country_id' => $getcoutry->id])->get('state_master')->result();
        if ($statedata) {
            return $statedata;
        } else {
            return false;
        }
    }
    // Bulk Discount Functionality : Calculate category level tax rate as per product
    public function getCategoryTax($categoryId){
        $result =  $this->db->select('*')->where(['id'=>$categoryId])->get('categories')->row();
        return  $result;
    }
    public function getVariableTax($categoryid){
        $result =  $this->db->select('tax_slabs')->where(['id'=>$categoryid])->get('categories')->row();
       
        if($result){
            if($result->tax_slabs){
              return unserialize($result->tax_slabs);
            }else {
                return FALSE;
            }
        }else {
            return FALSE;
        }
    }
    // Calculate category level tax rate as per product
    public function CalculateCategoryLevelTaxRate($product_id, $unit_price) {

        $product_details = $this->getProductByID($product_id);     
        $itemCategory = ($product_details->subcategory_id) ? $product_details->subcategory_id : $product_details->category_id;
        $categoryTax = FALSE;
        if ($itemCategory) {
            $categoryTax = $this->getCategoryTax($itemCategory);
        }

        if ($categoryTax) {
            if (strtolower($categoryTax->fix_tax_rate) == 'fix tax') {
                $gettax = $this->site->getTaxRateByID($categoryTax->tax_rate);
                $fixtax = $gettax->id . '~' . $gettax->rate;
                $category_tax = False;
                $tax_details->type = 1;
            } else {
                $category_tax = $this->getVariableTax((($product_details->subcategory_id) ? $product_details->subcategory_id : $product_details->category_id));
                $fixtax = False;
                $tax_details->type = 1;
            }
        }
        // $pr_tax = $product_details->tax_rate;
        // $tax_details = $this->getTaxRateByID($pr_tax);
        // $tax = $tax_details->rate . "%";

        if ($tax_details->type == 1) {
            if ($fixtax) {
                $exptax = explode("~", $fixtax);
                $tax_rate = $exptax[0];
            } 
            else {
                if ($category_tax) {
                    foreach ($category_tax as $categorytax) {
                        
                        $uptocheck = $categorytax['upto'];
                        if ($categorytax['condition'] == "less_than" && $unit_price <= $categorytax['price']) {

                            if ($uptocheck) {
                                if ($categorytax['price'] >= $unit_price && $unit_price <= $uptocheck) {
                                    $taxvalue = $categorytax['taxratevalue'];
                                    $exptax = explode("~", $taxvalue);
                                    $tax_rate = $exptax[0];
                                } else {
                                    $taxvalue = $categorytax['taxratevalue'];
                                    $exptax = explode("~", $taxvalue);
                                    $tax_rate = $exptax[0];
                                }
                            } else {
                                $taxvalue = $categorytax['taxratevalue'];
                                $exptax = explode("~", $taxvalue);
                                $tax_rate = $exptax[0];
                            }
                        } else if ($categorytax['condition'] == "greater_than" && $unit_price >= $categorytax['price']) {
                            
                            if ($uptocheck) {
                                if ($categorytax['price'] >= $unit_price && $unit_price <= $uptocheck) {
                                    $taxvalue = $categorytax['taxratevalue'];
                                    $exptax = explode("~", $taxvalue);
                                    $tax_rate = $exptax[0];
                                } else {
                                    $taxvalue = $categorytax['taxratevalue'];
                                    $exptax = explode("~", $taxvalue);
                                    $tax_rate = $exptax[0];
                                }
                            } else {
                                $taxvalue = $categorytax['taxratevalue'];
                                $exptax = explode("~", $taxvalue);
                                $tax_rate = $exptax[0];
                            }
                        }
                    }

                }
            }
        } elseif ($tax_details->type == 2) {
            if ($fixtax) {
                $exptax = explode("~", $fixtax);
                $tax_rate = $exptax[0];
            }
                else {
                if ($category_tax) {
                    foreach ($category_tax as $categorytax) {

                        $uptocheck = $categorytax['upto'];
                        if ($categorytax['condition'] == "less_than" && $unit_price <= $categorytax['price']) {
                            if ($uptocheck) {
                                if ($categorytax['price'] >= $unit_price && $unit_price <= $uptocheck) {
                                    $taxvalue = $categorytax['taxratevalue'];
                                } else {
                                    $taxvalue = $categorytax['taxratevalue'];
                                }
                            } else {
                                $taxvalue = $categorytax['taxratevalue'];
                            }
                        } else if ($categorytax['condition'] == "greater_than" && $unit_price >= $categorytax['price']) {
                            if ($uptocheck) {
                                if ($categorytax['price'] >= $unit_price && $unit_price <= $uptocheck) {
                                    $taxvalue = $categorytax['taxratevalue'];
                                } else {
                                    $taxvalue = $categorytax['taxratevalue'];
                                }
                            } else {
                                $taxvalue = $categorytax['taxratevalue'];
                            }
                        }
            
                        if (isset($taxvalue)) {
                            $exptax = explode("~", $taxvalue);
                            $tax_rate = $exptax[0];
                        }
                    }
                }
            }
                
        }//end else.
        return $tax_rate;
    }
  //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getPrefixByBillerFromLocation($location_id = null, $biller_id = null) {
        if ($this->Settings->use_invoice_number_prefix) {
            $this->db->select('bill_prefix, primary_biller_id, other_biller_id');
            $this->db->from('sma_warehouses');
            if ($location_id === null || $biller_id === null) {
                return null;
            }
            // Apply filters
            if ($location_id !== null) {
                $this->db->where('id', $location_id);
            }
            $query = $this->db->get();
            // Check if a record is found
            if ($query->num_rows() > 0) {
                $row = $query->row();
                // If $biller_id is set but does not match the record, return false
                if ($biller_id !== null && empty($row->primary_biller_id )) {
                    if ($row->other_biller_id) {
                        return $row->bill_prefix ? $row->bill_prefix : $row->other_biller_id;
                    }
                    return false;
                }
                // Return bill_prefix or fallback to biller_id
                return $row->bill_prefix ? $row->bill_prefix : $biller_id;
            }
        }
        return null; // No matching location
    }
     public function getBillerFromLocation($location_id = null) {
        $this->db->select('bill_prefix, primary_biller_id, other_biller_id');
        $this->db->from('sma_warehouses');
       
        if ($location_id === null) {
            return null;
        }
        // Apply filters
        if ($location_id !== null) {
            $this->db->where('id', $location_id);
        }
        $query = $this->db->get();
        // Check if a record is found
        if ($query->num_rows() > 0) {

            $row = $query->row();
            
            // If $biller_id is set but does not match the record, return false
            if (($row->primary_biller_id > 0)) {
                if ($row->other_biller_id) {
                    return $row->bill_prefix ? $row->bill_prefix : $row->other_biller_id;
                }
                return false;
            }
            // Return bill_prefix or fallback to biller_id
            return $row->bill_prefix ? $row->bill_prefix :'';
        }

        return null; // No matching location
    }
    public function getBillerByWarehouseId($warehouse_id) {
        $this->db->where('id', $warehouse_id);
        $query = $this->db->get('warehouses');
        $warehouse = $query->row(); // object
        if ($warehouse) {
            $primary_id = $warehouse->primary_biller_id;
         
            if (!$this->Settings->use_invoice_number_prefix) {
                return $primary_id;
            }else {
                $other_ids = $warehouse->other_biller_id; // assuming this is a comma-separated string like "2,3,5"
                $combined_ids = $primary_id;
                if (!empty($other_ids)) {
                    $combined_ids .= ',' . $other_ids;
                }
                return $combined_ids;
            }
           
        }

        return null; // or return '' if preferred
    }
    public function getBillerByIds($id_string) {
        $ids = explode(',', $id_string);
        $ids = array_filter($ids, function($id) {
            return is_numeric($id);
        });
        if (empty($ids)) {
            return [];
        }
        $this->db->where_in('id', $ids);
        $query = $this->db->get('sma_companies');
        return $query->result(); // returns array of objects
    }
    public function getAllBillers($group_name) {
        $q = $this->db->get_where('companies', array('group_name' => $group_name));
        if ($q->num_rows() > 0) {
            return $q->result_array(); // returns array of associative arrays
        }
        return [];
    }
    public function getPrimaryBillerById($biller_id) {
        $this->db->where('id', $biller_id);
        $query = $this->db->get('warehouses');
        return $query->row()->primary_biller_id; // returns a single object
    }

  //////////////////////////////////////////////////////Multiple Biller functionality END////////////////////////////////////////////////////
    public function getWarehouseProductsVar($option_id, $warehouse_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllcountrys() {
        $getcountry = $this->db->get('country_master')->result();
        if ($getcountry) {
            return $getcountry;
        } else {
            return false;
        }
    }
    public function getWarehouseStock($warehouse_id, $product_id, $option_id = NULL){
        if ($option_id) {
            // Check product variant stock
            $this->db->where('warehouse_id', $warehouse_id);
            $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id), 1);
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        } else {
            // Check product stock
            $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id), 1);
            if ($q->num_rows() > 0) {
                return $q->row();
            }
        }
        return FALSE;
    }  
      public function getWarehouseProductQuantity($warehouse_id, $product_id) {
        $q = $this->db->get_where('sma_warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
     // Insert or update data in purchase_items table against product and warehouse while adding batch from manager dashboard screen
    
   
    public function syncPurchaseItemsForProductionUnit($product_id, $location_id, $batchQuantity, $batchNumber = null) {

        if (!empty($product_id)) {

            $purchase_items = null;
            if ($batchNumber !== null && $batchNumber !== '') {
                $purchase_items = $this->site->getPurchasedItems($product_id, $location_id, 0, $batchNumber);
            } else {
                $purchase_items = $this->site->getPurchasedItems($product_id, $location_id);
            }

            if ($purchase_items) {
                foreach ($purchase_items as $purchase_item) {

                    $quantity_balance = $purchase_item->quantity_balance + $batchQuantity;
                    $quantity = $purchase_item->quantity;

                    if ($purchase_item->quantity == 0 && !$purchase_item->purchase_id) {
                        $quantity = $purchase_item->quantity + $batchQuantity;
                    }
                    $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance, 'quantity' => $quantity), array('id' => $purchase_item->id));
                }
            } else {

                $pr = $this->site->getProductByID($product_id);
                $item = array(
                    'product_id' => $product_id,
                    'product_code' => $pr->code,
                    'product_name' => $pr->name,
                    'net_unit_cost' => $pr->cost,
                    'unit_cost' => $pr->cost,
                    'real_unit_cost' => $pr->cost,
                    'quantity' => $batchQuantity,
                    'option_id' => 0,
                    'quantity_balance' => $batchQuantity,
                    'item_tax' => 0,
                    'tax_rate_id' => 1,
                    'tax' => 0,
                    'tax_method' => $pr->tax_method,
                    'subtotal' => ($pr->cost * $batchQuantity),
                    'warehouse_id' => $location_id,
                    'date' => date('Y-m-d'),
                    'status' => 'received',
                    'expiry' => null,
                    'batch_number' => $batchNumber,
                    'product_unit_id' => $pr->purchase_unit,
                    'hsn_code' => $pr->hsn_code,
                    'unit_quantity' => $batchQuantity ? $batchQuantity : 1
                );
                $this->db->insert('purchase_items', $item);
            }
        }
    }
    // update total quantity in products table for production unit
    public function syncProductQtyForPU($product_id, $location_id) {
        $balance_qty = $this->getBalanceQuantity($product_id);
        // $wh_balance_qty = $this->getBalanceQuantity($product_id, $location_id);
        if ($this->db->update('products', array('quantity' => $balance_qty), array('id' => $product_id))) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Variant product: products.quantity = SUM of all variant stock (warehouses_products_variants) at location.
     * Call after each variant save so multi-variant batch updates parent product in one sum.
     */
    public function syncVariantProductQtyForPU($product_id, $location_id)
    {
        $product_id = (int) $product_id;
        $location_id = (int) $location_id;

        if ($product_id <= 0) {
            return false;
        }

        // Sum all variant rows at this warehouse (each option_id may have its own row)
        $this->db->select('COALESCE(SUM(wpv.quantity), 0) AS total_stock', false);
        $this->db->from('sma_warehouses_products_variants wpv');
        $this->db->where('wpv.product_id', $product_id);
        $this->db->where('wpv.option_id >', 0);
        if ($location_id > 0) {
            $this->db->where('wpv.warehouse_id', $location_id);
        }
        $row = $this->db->get()->row();
        $total_stock = $row ? (float) $row->total_stock : 0;

        if ($this->db->update('products', array('quantity' => $total_stock), array('id' => $product_id))) {
            return true;
        }
        return false;
    }
    ////////////////////////////////////////// *Batch-Wise Stock Management Start* ////////////////////////////
    public function syncProductBatchWiseQty($product_id, $warehouse_id, $batch_number) {
        // Get total quantity across all batches for this product in the warehouse
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as total_stock', False);
        $this->db->where('product_id', $product_id);
        $this->db->where('warehouse_id', $warehouse_id);
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
        $q = $this->db->get('purchase_items');
        $total_qty = $q->num_rows() > 0 ? (float)$q->row()->total_stock : 0;
        
        // Update the main product quantity
        $this->db->update('products', array('quantity' => $this->getBalanceQuantity($product_id)), array('id' => $product_id));
        
        // Get the current warehouse product
        $warehouse_product = $this->getWarehouseProducts($product_id, $warehouse_id);
        
        if ($warehouse_product) {
            // If warehouse product exists, update it
            $this->db->update('warehouses_products', 
                array('quantity' => $total_qty), 
                array('product_id' => $product_id, 'warehouse_id' => $warehouse_id)
            );
        } else {
            // If warehouse product doesn't exist, create it
            $product = $this->getProductByID($product_id);
            if ($product) {
                $this->db->insert('warehouses_products', array(
                    'quantity' => $total_qty, 
                    'product_id' => $product_id, 
                    'warehouse_id' => $warehouse_id, 
                    'avg_cost' => $product->cost
                ));
            }
        }
        
        return TRUE;
    }
    public function getBalanceQuantityAgainstBatch($product_id, $warehouse_id = NULL, $batch_number = NULL) {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('product_id', $product_id);
        $this->db->where('batch_number', $batch_number);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }
    public function getBatchwiseWarehouseProducts($product_id, $warehouse_id = NULL, $batch_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($batch_id) {
            $this->db->where('batch_id', $batch_id);
        }
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    // *Variant Batch-Wise Stock Management*
    public function syncVariantBatchWiseQty($variant_id, $warehouse_id, $product_id, $batch_number) {
        // Get the total quantity for this variant across all batches
        $balance_qty = $this->getBalanceVariantQuantity($variant_id);
        
        // Get the warehouse-specific quantity for this variant
        $wh_balance_qty = $this->getBalanceVariantQuantity($variant_id, $warehouse_id);
        
        // Update the main variant quantity
        $this->db->update('product_variants', 
            array('quantity' => $balance_qty), 
            array('id' => $variant_id)
        );
        
        // Get batch-wise quantity for this variant
        $this->db->select('SUM(quantity_balance) as batch_qty', FALSE);
        $this->db->where('option_id', $variant_id);
        $this->db->where('warehouse_id', $warehouse_id);
        $this->db->where('batch_number', $batch_number);
        $q = $this->db->get('purchase_items');
        $batch_qty = $q->row()->batch_qty;
        
        // Update or insert warehouse product variant
        $wpv = $this->getWarehouseProductsVariants($variant_id, $warehouse_id);
        if ($wpv) {
            $this->db->update('warehouses_products_variants', 
                array('quantity' => $wh_balance_qty), 
                array('option_id' => $variant_id, 'warehouse_id' => $warehouse_id)
            );
        } else {
            $this->db->insert('warehouses_products_variants', array(
                'quantity' => $wh_balance_qty,
                'option_id' => $variant_id,
                'warehouse_id' => $warehouse_id,
                'product_id' => $product_id
            ));
        }
        
        return TRUE;
    }
    Public function getBalanceVariantQuantityAgainstBatch($variant_id = 0, $warehouse_id = NULL, $batch_number = NULL) {
        $variant_id = $variant_id ? $variant_id : 0;
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('option_id', $variant_id);
        $this->db->where('batch_number', $batch_number);

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }
    public function getBatchWiseWarehouseProductsVariants($option_id, $warehouse_id = NULL, $batch_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($batch_id) {
            $this->db->where('batch_id', $batch_id);
        }
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    // Auto generate batch number while creating purchase(Batch settings : Add Batch while transactions)
    public function getLatestBatch() {
        
        $this->db->select('batch_no');
        $this->db->from('product_batches'); 
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row();
    }

    // Get last batch number for a specific product for manager dashboard screen
    public function getLastBatchNumber($product_id) {
        $this->db->select('batch_no, id, updated_at');
        $this->db->from('product_batches');
        $this->db->where('product_id', $product_id);
        // Order by updated_at descending to pick the most recently modified batch
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row();
    }
     ////////////////////////////////////////// *Batch-Wise Stock Management END* ////////////////////////////
    
     //************************************** 3P Orders Receipt*********************//
     public function taxAttrPercentageByorderTaxId($taxCode, $taxID, $order_id) {
        $this->db->select("it.attr_per as percentage");
        $this->db->where_in('it.order_id', array((int) $order_id));
        $this->db->where('i.tax_rate_id', $taxID);
        $this->db->where('it.attr_code', $taxCode);
        $this->db->from('delivery_challan_items_tax it');
        $this->db->join('delivery_challan_items i', 'it.item_id=i.id', 'left');
        $this->db->group_by('it.attr_code');
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->percentage;
        }
        return FALSE;
    }
    public function syncordersalepayments($id) {
        $sale = $this->getOrderByID($id);
        $payments = $this->getOrderPayments($id);
        $paid = 0;
        $grand_total = $sale->grand_total + $sale->rounding;
        if (!empty($payments)) {
            foreach ($payments as $payment) {
                $paid += $payment->amount;
            }
        }
        $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
        // updated  by SW for partial 03022017
        if ($this->sma->formatDecimal($grand_total) <= $this->sma->formatDecimal($paid)) {
            $payment_status = 'paid';
        } elseif ($paid != 0) {
            $payment_status = 'partial';
        } elseif ($sale->due_date <= date('Y-m-d') && !$sale->sale_id) {
            $payment_status = 'due';
        }

        if ($this->db->update('orders', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
           
           if ($this->db->update('sales', array('paid' => $paid, 'payment_status' => $payment_status), array('order_no' => $sale->invoice_no))) {
            return true;
             }
            return true;
        }

        return FALSE;
    }
    public function get_all_transaction_types() {
        $this->db->order_by('type', 'ASC');
        $query = $this->db->get('sma_coinage_transaction_type');
        return $query->result();
    }
    //////////////////////////// Generate Variant PO ////////////////////////////
    public function getNonVendorActiveWarehouses() {
        // Get vendor location type ID
        $vendor_type = $this->db->select('id')
                                ->where('type', 'Vendor')
                                ->get('sma_location_type')
                                ->row();
        $vendor_type_id = $vendor_type ? $vendor_type->id : 0;
    
        // Base filters for active warehouses
        $this->db->where('is_active', 1);
        $this->db->where('is_deleted', 0);
        $this->db->where('is_disabled', 0);
    
        // Exclude vendor warehouses (or include NULLs)
        if ($vendor_type_id) {
            $this->db->group_start()
                     ->where('location_type !=', $vendor_type_id)
                     ->or_where('location_type IS NULL', null, false)
                     ->group_end();
        }
    
        $q = $this->db->get('sma_warehouses');
    
        if ($q->num_rows() > 0) {
            $data = [];
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
    
        return false;
    }
    public function getAllWarehousesExcludingLocationType($location_type_name) {
        // Get location type ID
        $location_type = $this->db->select('id')->where('type', $location_type_name)->get('sma_location_type')->row();
        $location_type_id = $location_type ? $location_type->id : 0;
    
        // Get all warehouses that are NOT of the specified location type
        $this->db->where('is_deleted', 0);
        $this->db->where('is_disabled', 0);
        if ($location_type_id) {
            $this->db->where('location_type !=', $location_type_id);
        }
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllVendorWarehouses()
    {
        $this->db->select('w.*');
        $this->db->from('warehouses w');
        $this->db->join('sma_location_type lt', 'lt.id = w.location_type', 'left');

        // Existing conditions
        $this->db->where([
            "w.is_deleted" => 0,
            "w.is_disabled" => 0
        ]);

        // New condition → Only Vendor warehouses
        $this->db->where("lt.type", "Vendor");

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    // Fetch data for creating sale in production unit
    public function getCustomerByCompanyame($company) {

        $this->db->where('company', $company);
        $this->db->where('group_name', 'customer');  
        $this->db->limit(1);

        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    // Fetch data fro creating  purchase in production unit
    public function getSupplierByCompanyame($company) {

        $this->db->where('company', $company);
        $this->db->where('group_name', 'supplier');  
        $this->db->limit(1);

        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    // fetch sales staff    
    public function getAllSalesPersons(){
        $q = $this->db->where('group_id', '5')->get('companies');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSalesPersonsByWarehouse($warehouse_ids = array()) {
        try {
            if (empty($warehouse_ids)) {
                return $this->getCompanyDetailsByGroupID(5);
            }
            
            $this->db->where('group_id', '5');
            
            // Filter sales persons by warehouse assignments
            $warehouse_conditions = array();
            foreach ($warehouse_ids as $warehouse_id) {
                $warehouse_conditions[] = "FIND_IN_SET('" . $warehouse_id . "', location_id) > 0";
            }
            
            if (!empty($warehouse_conditions)) {
                $this->db->where('(' . implode(' OR ', $warehouse_conditions) . ')');
            }
            
            $q = $this->db->get('companies');
            if ($q->num_rows() > 0) {
                return $q->result_array(); // Return as arrays like getCompanyDetailsByGroupID
            }
            return array(); // Return empty array instead of FALSE
        } catch (Exception $e) {
            // Fallback to getCompanyDetailsByGroupID if there's any error
            return $this->getCompanyDetailsByGroupID(5);
        }
    }
    /**
     * Labels for sale source / order type dropdown — sma_order_type only (no hardcoded list).
     */
    public function getOrderTypes() {
        if (!$this->db->table_exists('order_type')) {
            return array();
        }
        if ($this->db->field_exists('sort_order', 'order_type')) {
            $this->db->order_by('sort_order', 'asc');
        }
        $this->db->order_by('id', 'asc');
        $q = $this->db->get('order_type');
        return ($q->num_rows() > 0) ? $q->result() : array();
    }

    public function syncProductTransactionHistory($action, $data = array()) {
        $history_data = array();
        
        $transaction = null;
        $items = null;
        
        if (strpos($action, 'purchase') !== false) {
            $transaction = isset($data['purchase']) ? $data['purchase'] : null;
            $items = isset($data['items']) ? $data['items'] : [];
        } elseif (strpos($action, 'sale') !== false) {
            $transaction = isset($data['sale']) ? $data['sale'] : null;
            $items = isset($data['items']) ? $data['items'] : [];
        } elseif (strpos($action, 'transfer') !== false) {
            $transaction = isset($data['transfer']) ? $data['transfer'] : null;
            $items = isset($data['items']) ? $data['items'] : [];
        } elseif (strpos($action, 'adjustment') !== false) {
            $transaction = isset($data['adjustment']) ? $data['adjustment'] : null;
            $items = isset($data['items']) ? $data['items'] : [];
        } elseif (strpos($action, 'challan') !== false) {
            $transaction = isset($data['challan']) ? $data['challan'] : null;
            $items = isset($data['items']) ? $data['items'] : [];
        }

        if (!$transaction || empty($items)) {
            return;
        }

        $action_id = isset($transaction['id']) ? $transaction['id'] : null;
        
        $base_action = str_replace(array('edit_', 'update_'), 'add_', $action);
        

        $transaction_date = isset($transaction['date']) ? $transaction['date'] : date('Y-m-d H:i:s');
        $global_warehouse_id = isset($transaction['warehouse_id']) ? $transaction['warehouse_id'] : null;
        if (strpos($action, 'transfer') !== false && isset($transaction['to_warehouse_id'])) {
            $global_warehouse_id = $transaction['to_warehouse_id'];
        } elseif (!$global_warehouse_id && isset($transaction['from_warehouse_id'])) {
            $global_warehouse_id = $transaction['from_warehouse_id'];
        }
        $created_by = isset($transaction['created_by']) ? $transaction['created_by'] : $this->session->userdata('user_id');

        foreach ($items as $item) {
            $item = (array) $item;
            $product_id = isset($item['product_id']) ? $item['product_id'] : null;
            if (!$product_id) continue;
            
            $variant_id = isset($item['option_id']) ? $item['option_id'] : null;
            $item_warehouse_id = isset($item['warehouse_id']) ? $item['warehouse_id'] : $global_warehouse_id;
            if (strpos($action, 'transfer') !== false) {
                // PU dispatch transfer: stock already left source warehouse on allot; log outgoing at from_warehouse.
                if (strpos($action, 'pu_add') !== false && isset($transaction['from_warehouse_id'])) {
                    $item_warehouse_id = $transaction['from_warehouse_id'];
                } elseif (isset($transaction['to_warehouse_id'])) {
                    $item_warehouse_id = $transaction['to_warehouse_id'];
                }
            }
            
            $qty_before_transaction = $this->getCachedQtyBeforeTransaction($product_id, $item_warehouse_id, $variant_id);
            
            $raw_quantity = isset($item['quantity']) ? $item['quantity'] : (isset($item['unit_quantity']) ? $item['unit_quantity'] : 0);
            if (strpos($action, 'purchase') !== false) {
                $status = isset($transaction['status']) ? $transaction['status'] : '';
                if ($status == 'received' || $status == 'returned' || $status == 'partial') {
                    $raw_quantity = isset($item['quantity_received']) && $item['quantity_received'] > 0 ? $item['quantity_received'] : $raw_quantity;
                }
            }

            $raw_quantity = abs($raw_quantity);
            $signed_quantity = $raw_quantity;

            if (strpos($action, 'sale') !== false || strpos($action, 'challan') !== false) {
                $signed_quantity = -$raw_quantity;
            } elseif (strpos($action, 'adjustment') !== false) {
                $type = isset($item['type']) ? $item['type'] : '';
                if ($type == 'subtraction') {
                    $signed_quantity = -$raw_quantity;
                }
            } elseif (strpos($action, 'transfer') !== false) {
                if ($item_warehouse_id == (isset($transaction['from_warehouse_id']) ? $transaction['from_warehouse_id'] : null)) {
                    $signed_quantity = -$raw_quantity;
                }
            }

            if (strpos($action, 'return') !== false) {
                $signed_quantity = -$signed_quantity;
            }

            // Sum all batch rows — PU batch stock can have multiple warehouses_products rows per product+warehouse.
            $balance_qty = $this->getWarehouseStockSum($product_id, $item_warehouse_id, $variant_id);
            
            $quantity = $signed_quantity; 
            if ((strpos($action, 'sale') !== false || strpos($action, 'pu_add_transfer') !== false) && strpos($action, 'return') === false) {
                $quantity = abs($signed_quantity);
            }

            // Prefer net unit values (pre-tax) for history; unit_price/unit_cost often include tax.
            $price = isset($item['net_unit_price']) ? $item['net_unit_price'] : (isset($item['real_unit_price']) ? $item['real_unit_price'] : (isset($item['unit_price']) ? $item['unit_price'] : (isset($item['price']) ? $item['price'] : 0)));
            $cost = isset($item['net_unit_cost']) ? $item['net_unit_cost'] : (isset($item['real_unit_cost']) ? $item['real_unit_cost'] : (isset($item['unit_cost']) ? $item['unit_cost'] : (isset($item['cost']) ? $item['cost'] : 0)));
            $mrp = isset($item['mrp']) ? $item['mrp'] : 0;
            $tax_rate_id = isset($item['tax_rate_id']) ? $item['tax_rate_id'] : null;
            $total_tax_amt = isset($item['item_tax']) ? $item['item_tax'] : (isset($item['tax']) ? $item['tax'] : 0);
            
            $product_details = $this->db->get_where('products', ['id' => $product_id])->row();
            if ($product_details) {
                if (!$price) $price = $product_details->price;
                if (!$cost) $cost = $product_details->cost;
                if (!$mrp) $mrp = $product_details->mrp;
                if (!$tax_rate_id) $tax_rate_id = $product_details->tax_rate;
            }
            
            if (strpos($action, 'challan') !== false) {
                $mrp = $price;
            }

            $total_paid = isset($item['subtotal']) ? $item['subtotal'] : 0;
            
            $net_price = $raw_quantity * $price;
            $net_cost = $raw_quantity * $cost;
            $net_mrp = $raw_quantity * $mrp;
            
            if (isset($item['net_unit_price'])) {
                $net_price = $raw_quantity * $item['net_unit_price'];
            }
            if (isset($item['net_unit_cost'])) {
                $net_cost = $raw_quantity * $item['net_unit_cost'];
            }

            if (!$total_paid) {
                if (strpos($action, 'sale') !== false || strpos($action, 'challan') !== false) {
                    $total_paid = $net_price + $total_tax_amt;
                } else {
                    $total_paid = $net_cost + $total_tax_amt;
                }
            }

            // Apply matrix logic:
            if (strpos($action, 'sale') !== false || strpos($action, 'challan') !== false) {
                $cost = 0;
                $net_cost = 0;
            } else {
                // For purchase, adjustment, transfer
                $price = 0;
                $net_price = 0;
                $mrp = 0;
                $net_mrp = 0;
            }

            $history_data[] = array(
                'transaction_date' => $transaction_date,
                'action' => $action,
                'action_id' => $action_id,
                'warehouse_id' => $item_warehouse_id,
                'product_id' => $product_id,
                'option_id' => $variant_id,
                'supplier_id' => isset($transaction['supplier_id']) ? $transaction['supplier_id'] : null,
                'biller_id' => isset($transaction['biller_id']) ? $transaction['biller_id'] : null,
                'customer_id' => isset($transaction['customer_id']) ? $transaction['customer_id'] : null,
                'qty_before_transaction' => $qty_before_transaction,
                'quantity' => $quantity,
                'balance_qty' => $balance_qty,
                'price' => $price,
                'net_price' => $net_price,
                'cost' => $cost,
                'net_cost' => $net_cost,
                'mrp' => $mrp,
                'net_mrp' => $net_mrp,
                'tax_rate_id' => $tax_rate_id,
                'total_tax_amt' => $total_tax_amt,
                'total_paid' => $total_paid,
                'created_by' => $created_by,
                'created_at' => date('Y-m-d H:i:s')
            );
        }
        
        if (!empty($history_data)) {
            $this->db->insert_batch('products_transaction_history', $history_data);
        }
    }
    
    public $qty_before_cache = [];

    public function prefetchQtyBeforeTransaction($items, $default_wh_id) {
        $this->qty_before_cache = []; // Clear cache for new transaction
        foreach ($items as $item) {
            $item = (array) $item;
            if (empty($item['product_id'])) continue;
            
            $wh_id = isset($item['warehouse_id']) ? $item['warehouse_id'] : $default_wh_id;
            $var_id = isset($item['option_id']) ? $item['option_id'] : null;
            
            $key = $item['product_id'] . '_' . $wh_id . '_' . $var_id;
            if (!isset($this->qty_before_cache[$key])) {
                $this->qty_before_cache[$key] = $this->getQtyBeforeTransaction($item['product_id'], $wh_id, $var_id);
            }
        }
    }

    public function getCachedQtyBeforeTransaction($product_id, $warehouse_id, $variant_id = null) {
        $key = $product_id . '_' . $warehouse_id . '_' . $variant_id;
        if (isset($this->qty_before_cache[$key])) {
            return $this->qty_before_cache[$key];
        }
        return $this->getQtyBeforeTransaction($product_id, $warehouse_id, $variant_id);
    }

    // Total warehouse stock for a product (sums all batch rows in warehouses_products / warehouses_products_variants).
    public function getWarehouseStockSum($product_id, $warehouse_id, $variant_id = null)
    {
        if (!empty($variant_id)) {
            $this->db->select('COALESCE(SUM(quantity), 0) AS total_qty', false);
            $this->db->from('warehouses_products_variants');
            $this->db->where('product_id', $product_id);
            $this->db->where('option_id', $variant_id);
            $this->db->where('warehouse_id', $warehouse_id);
        } else {
            $this->db->select('COALESCE(SUM(quantity), 0) AS total_qty', false);
            $this->db->from('warehouses_products');
            $this->db->where('product_id', $product_id);
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get();
        return ($q->num_rows() > 0) ? (float) $q->row()->total_qty : 0;
    }

    public function getQtyBeforeTransaction($product_id, $warehouse_id, $variant_id = null)
    {
        return $this->getWarehouseStockSum($product_id, $warehouse_id, $variant_id);
    }
    /**
     * Variant batch: same structure as syncPurchaseItemsForProductionUnit (batch + location + product),keyed by option_id instead of option_id = 0.
     */
    public function syncVariantPurchaseItemsForProductionUnit($product_id, $location_id, $batchQuantity, $batchNumber = null, $option_id = 0)
    {
        $option_id = (int) $option_id;

        if (!empty($product_id) && $option_id > 0) {

            $purchase_items = null;
            if ($batchNumber !== null && $batchNumber !== '') {
                $purchase_items = $this->getPurchasedItems($product_id, $location_id, $option_id, $batchNumber);
            } else {
                $purchase_items = $this->getPurchasedItems($product_id, $location_id, $option_id);
            }

            if ($purchase_items) {
                foreach ($purchase_items as $purchase_item) {

                    $quantity_balance = $purchase_item->quantity_balance + $batchQuantity;
                    $quantity = $purchase_item->quantity;

                    if ($purchase_item->quantity == 0 && !$purchase_item->purchase_id) {
                        $quantity = $purchase_item->quantity + $batchQuantity;
                    }
                    $this->db->update(
                        'purchase_items',
                        array('quantity_balance' => $quantity_balance, 'quantity' => $quantity),
                        array('id' => $purchase_item->id)
                    );
                }
            } else {

                $pr = $this->getProductByID($product_id);
                $unit_cost = $pr->cost;
                $variant = $this->getVerientById($option_id);
                if ($variant && isset($variant->cost) && $variant->cost !== null && $variant->cost !== '') {
                    $unit_cost = $variant->cost;
                }

                $item = array(
                    'product_id'       => $product_id,
                    'product_code'     => $pr->code,
                    'product_name'     => $pr->name,
                    'net_unit_cost'    => $unit_cost,
                    'unit_cost'        => $unit_cost,
                    'real_unit_cost'   => $unit_cost,
                    'quantity'         => $batchQuantity,
                    'option_id'        => $option_id,
                    'quantity_balance' => $batchQuantity,
                    'item_tax'         => 0,
                    'tax_rate_id'      => 1,
                    'tax'              => 0,
                    'tax_method'       => $pr->tax_method,
                    'subtotal'         => ($unit_cost * $batchQuantity),
                    'warehouse_id'     => $location_id,
                    'date'             => date('Y-m-d'),
                    'status'           => 'received',
                    'expiry'           => null,
                    'batch_number'     => $batchNumber,
                    'product_unit_id'  => $pr->purchase_unit,
                    'hsn_code'         => $pr->hsn_code,
                    'unit_quantity'    => $batchQuantity ? $batchQuantity : 1,
                );
                $this->db->insert('purchase_items', $item);
            }
        }
    }
    // get customer billing and shipping addresses
    public function getCustomerBillingShippingAddresses($company_id) {
        $shipping = array();
        $billing = array();
        $untyped = array();
        $this->db->where('company_id', $company_id);
        $this->db->order_by('is_default', 'desc');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get('addresses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $address_parts = array($row->address_name, $row->line1, $row->line2, $row->city, $row->postal_code, $row->state, $row->country);
                $item = array(
                    'id' => $row->id,
                    'label' => implode(', ', array_filter($address_parts)),
                    'is_default' => isset($row->is_default) ? (int) $row->is_default : 0,
                );
                $type = isset($row->type) ? strtolower(trim($row->type)) : '';
                if ($type == 'shipping') {
                    $shipping[] = $item;
                } elseif ($type == 'billing') {
                    $billing[] = $item;
                } else {
                    $untyped[] = $item;
                }
            }
        }
        if (empty($shipping) && empty($billing) && !empty($untyped)) {
            $shipping = $untyped;
            $billing = $untyped;
        }
        if (empty($shipping) && !empty($billing)) {
            $shipping[] = $billing[0];
        }
        if (empty($billing) && !empty($shipping)) {
            $billing[] = $shipping[0];
        }
        return array('shipping' => $shipping, 'billing' => $billing);
    }

    public function getDefaultCustomerAddress($addresses) {
        if (empty($addresses)) {
            return array('id' => '', 'label' => '');
        }
        foreach ($addresses as $address) {
            if (!empty($address['is_default'])) {
                return $address;
            }
        }
        return $addresses[0];
    }

    public function getCustomerAddressById($id) {
        if (empty($id)) {
            return array('id' => '', 'label' => '');
        }
        $q = $this->db->get_where('addresses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            $row = $q->row();
            $address_parts = array($row->address_name, $row->line1, $row->line2, $row->city, $row->postal_code, $row->state, $row->country);
            return array(
                'id' => $row->id,
                'label' => implode(', ', array_filter($address_parts)),
                'is_default' => isset($row->is_default) ? (int) $row->is_default : 0,
            );
        }
        return array('id' => '', 'label' => '');
    }
    // Prefetch qty_before when warehouse stock was already reduced before history write
    public function prefetchQtyBeforeTransactionRestoringSaleQty($items, $default_wh_id) {
        $this->qty_before_cache = [];
        foreach ($items as $item) {
            $item = (array) $item;
            if (empty($item['product_id'])) continue;

            $wh_id = isset($item['warehouse_id']) ? $item['warehouse_id'] : $default_wh_id;
            $var_id = isset($item['option_id']) ? $item['option_id'] : null;
            $key = $item['product_id'] . '_' . $wh_id . '_' . $var_id;
            if (isset($this->qty_before_cache[$key])) {
                continue;
            }

            $current_qty = $this->getQtyBeforeTransaction($item['product_id'], $wh_id, $var_id);
            $sale_qty = isset($item['quantity']) ? $item['quantity'] : (isset($item['unit_quantity']) ? $item['unit_quantity'] : 0);
            $this->qty_before_cache[$key] = (float) $current_qty + abs((float) $sale_qty);
        }
    }


}
