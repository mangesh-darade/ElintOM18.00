<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Products_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('settings_model');
    }

    public function getAllProducts() {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCategoryProducts($category_id) {
        $q = $this->db->get_where('products', array('category_id' => $category_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSubCategoryProducts($subcategory_id) {
        $q = $this->db->get_where('products', array('subcategory_id' => $subcategory_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function get_category_names_by_ids($ids) {
    if (empty($ids)) {
        return [];
    }
     // Make sure all values are integers
     $ids = array_map('intval', $ids);

     // Create placeholders: ?, ?, ?, ...
      $placeholders = implode(',', array_fill(0, count($ids), '?'));

     // Build the query
      $q = "SELECT name FROM sma_categories WHERE id IN ($placeholders)";
      $query = $this->db->query($q, $ids);
      return $query->result_array();
   }

    public function getProductOptions($pid) {
        $user = $this->site->getUser();
        if (!$user || !$user->warehouse_id)
        {
            $settings = $this->settings_model->getSettings();
            $warehouse_ids = $settings->default_warehouse;
        }
        else{
            $warehouse_ids = $user->warehouse_id;
        }
        // $warehouse_id = $user->warehouse_id;
       
        $this->db->select('product_variants.*, sma_warehouses_products_variants.quantity AS warehouse_quant');
        $this->db->from('product_variants');
        $this->db->join(
            'sma_warehouses_products_variants',
            'sma_warehouses_products_variants.option_id = product_variants.id AND sma_warehouses_products_variants.product_id = product_variants.product_id AND sma_warehouses_products_variants.warehouse_id =' . (int)$warehouse_ids,
            'left'
        );
        $this->db->where('product_variants.product_id', $pid);
        $q = $this->db->get(); 
        // $q = $this->db->get_where('product_variants', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[$row->id] = $row;
            }
           
            return $data;
        }
        return FALSE;
    }
    // public function getProductOptions($pid) {
    //     $this->db->select('product_variants.*, manage_variant.variant_name');
    //     $this->db->from('product_variants');
    //     $this->db->join('manage_variant', 'product_variants.name = manage_variant.variant_name', 'left'); 
    //     $this->db->where('product_variants.product_id', $pid);
    //     $this->db->order_by('manage_variant.id', 'ASC');
    //     $q = $this->db->get(); 
    //     // $q = $this->db->get_where('product_variants', ['product_id' => $pid]);
    //     if ($q->num_rows() > 0) {
    //         foreach ($q->result() as $row) {
    //             $data[$row->id] = $row;
    //         }
    //         return $data;
    //     }
    //     return FALSE;
    // }
    public function getProductVariant($pid) {
        $q = $this->db->get_where('product_variants', ['product_id' => $pid]);
        $this->db->select('product_variants.*, manage_variant.variant_name');
        $this->db->from('product_variants');
        $this->db->join('manage_variant', 'product_variants.name = manage_variant.variant_name', 'left'); 
        $this->db->where('product_variants.product_id', $pid);
        $this->db->order_by('manage_variant.id', 'ASC');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                // $data[$row->id] = $row;
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

   public function getProductOptionsWithWH($pid, $GroupId='',$warehouse_ids = [])
    {
		if($GroupId!='')
			$this->db->where(array('product_variants.group_id' => $GroupId));
        $this->db->select($this->db->dbprefix('product_variants') . '.*, ' . $this->db->dbprefix('warehouses') . '.name as wh_name, ' . $this->db->dbprefix('warehouses') . '.id as warehouse_id, ' . $this->db->dbprefix('warehouses_products_variants') . '.quantity as wh_qty,'. $this->db->dbprefix('warehouses_products_variants') . '.avg_cost as avg_cost')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->join('warehouses', 'warehouses.id=warehouses_products_variants.warehouse_id', 'left')
            ->group_by(array('' . $this->db->dbprefix('product_variants') . '.id', '' . $this->db->dbprefix('warehouses_products_variants') . '.warehouse_id'))
            ->order_by('product_variants.id');
            if (!empty($warehouse_ids) && is_array($warehouse_ids)) {
                $this->db->where_in('warehouses_products_variants.warehouse_id', $warehouse_ids);
            }
        $q = $this->db->get_where('product_variants', array('product_variants.product_id' => $pid, 'warehouses_products_variants.quantity !=' => NULL));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductComboItems($pid) {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('combo_items') . '.unit_price as price,'. $this->db->dbprefix('combo_items') . '.option_id as variant_id')->join('products', 'products.code=combo_items.item_code', 'left')->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }

    public function getProductByID($id) {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductWithCategory($id) {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('categories') . '.name as category, ' . $this->db->dbprefix('brands') . '.name as brannd_name')
                ->join('categories', 'categories.id=products.category_id', 'left')
                ->join('sma_brands', 'sma_brands.id=products.brand', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function has_purchase($product_id, $warehouse_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('purchase_items', array('product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function getProductDetails($id) {
        $this->db->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . '.name, ' . $this->db->dbprefix('categories') . '.code as category_code, cost, price, quantity, alert_quantity')
                ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductDetail($id) {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('tax_rates') . '.name as tax_rate_name, ' . $this->db->dbprefix('tax_rates') . '.code as tax_rate_code, c.code as category_code, sc.code as subcategory_code', FALSE)
                ->join('tax_rates', 'tax_rates.id=products.tax_rate', 'left')
                ->join('categories c', 'c.id=products.category_id', 'left')
                ->join('categories sc', 'sc.id=products.subcategory_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSubCategories($parent_id) {
        $this->db->select('id as id, name as text')
                ->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCategoryTaxrate($category_id) {

        $this->db->select('id,tax_rate');

        $this->db->where('id', $category_id);

        $q = $this->db->get("categories");

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    public function getCategoryName($category_id) {

        $this->db->select('id,name,code');

        if ($category_id) {
            $ids = explode(',', $category_id);
            $this->db->where_in('id', $ids);
        }

        $this->db->order_by('name');

        $q = $this->db->get("categories");

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id]['id'] = $row->id;
                $data[$row->id]['name'] = $row->name;
                $data[$row->id]['code'] = $row->code;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCategoryIdByName($category_name) {

        $this->db->select('id,name,code');

        if ($category_name) {
            $categories = explode(',', $category_name);

            $this->db->where_in('name', $categories);
        }

        $this->db->order_by('name');

        $q = $this->db->get("categories");

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    public function getProductByCategoryID($id) {

        $q = $this->db->get_where('products', array('category_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return FALSE;
    }

    public function getAllWarehousesWithPQ($product_id, $warehouse_ids = []){
            
            $wp_table = $this->db->dbprefix('warehouses_products'); // sma_warehouses_products
            $w_table  = $this->db->dbprefix('warehouses');          // sma_warehouses
            $lt_table = $this->db->dbprefix('location_type');       // sma_location_type

            $this->db->select("
                w.*,
                IFNULL(SUM(wp.quantity), 0) AS quantity,
                wp.rack,
                lt.type AS location_type
            ", false)
            ->from("{$w_table} AS w")
            ->join("{$lt_table} AS lt", "w.location_type = lt.id", "left")
            ->join("{$wp_table} AS wp", "wp.warehouse_id = w.id AND wp.product_id = " . $this->db->escape($product_id), "left")
        //  Exclude Vendor warehouses, include NULL location_type (no type assigned)
        ->group_start()
            ->where("lt.type !=", "Vendor")
            ->or_where("lt.type IS NULL")
        ->group_end()
        ->group_by("w.id");

        if (!empty($warehouse_ids) && is_array($warehouse_ids)) {
            $this->db->where_in('w.id', $warehouse_ids);
        }

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    // Get warehouses with product quantity filtered by location type (vendor warehouses)
    public function getVendorWarehousesWithPQ($product_id, $warehouse_ids) {
            $wp_table = $this->db->dbprefix('warehouses_products'); // sma_warehouses_products
            $w_table  = $this->db->dbprefix('warehouses');          // sma_warehouses
            $lt_table = $this->db->dbprefix('location_type');       // sma_location_type

            $this->db->select("w.*, IFNULL(SUM(wp.quantity), 0) AS quantity, wp.rack, lt.type as location_type_name", false)
                    ->from("{$w_table} AS w")
                    ->join("{$lt_table} AS lt", "w.location_type = lt.id", "left")
                    ->join("{$wp_table} AS wp", "wp.warehouse_id = w.id AND wp.product_id = " . $this->db->escape($product_id), "left")
                    ->where("lt.type", "Vendor")
                    ->group_by("w.id");

            if (!empty($warehouse_ids) && is_array($warehouse_ids)) {
                $this->db->where_in('w.id', $warehouse_ids);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                return $q->result();
            }
            return FALSE;
        }

    public function getProductPhotos($id) {
        $q = $this->db->get_where("product_photos", array('product_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductByCode($code) {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductStorageType($product_id) {
        $q = $this->db->select('storage_type')->get_where('products', array('id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row()->storage_type;
        }
        return FALSE;
    }

    public function addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $postype_data = NULL) {

        if ($this->db->insert('products', $data)) {

            $product_id = $this->db->insert_id();
            $DatalogArr = array('product_data' => $data, 'items' => $items, 'warehouse_qty' => $warehouse_qty, 'product_attributes' => $product_attributes, 'photos' => $photos, 'postype_data' => $postype_data);
            $DataLog = array(
                'action_type' => 'Add',
                'product_id' => $product_id,
                'quantity' => '',
                'action_reff_id' => $product_id,
                'action_affected_data' => json_encode($DatalogArr),
                'action_comment' => 'Add products',
            );
            $this->sma->setUserActionLog($DataLog);
           // Urbanpiper

           if (!empty($data['up_items']) && $data['up_items'] == '1' && is_array($postype_data) && (($postype_data['pos_type'] == 'restaurant' || $postype_data['pos_type'] == 'bakery'))) {

            $postype_data['product_id'] = $product_id;
            unset($postype_data['pos_type']);
            $postype_data_ep = array(
                'product_id' => $postype_data['product_id'],
                'product_code' => $postype_data['product_code'],
                'price' => $postype_data['price'],
                'food_type_id' => $postype_data['food_type_id'],
                'available' => $postype_data['available'],
                'sold_at_store' => $postype_data['sold_at_store'],
                'recommended' => $postype_data['recommended'],
                'plat_zomato' => $postype_data['plat_zomato'],
                'plat_swiggy' => $postype_data['plat_swiggy'],
                'plat_foodpanda' => $postype_data['plat_foodpanda'],
                'plat_ubereats' => $postype_data['plat_ubereats'],
                //'default_tag' => $postype_data['default_tag'],
                'manage_stock' => $postype_data['manage_stock'],  
            );
       
                $this->db->insert('up_products', $postype_data_ep); 
        }
        // End Urbanpiper
            if ($items) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }

            $warehouses = $this->site->getAllWarehouses();
            if ($data['type'] == 'combo' || $data['type'] == 'Bundle' || $data['type'] == 'service') {
                foreach ($warehouses as $warehouse) {
                    $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0));
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);
            /*
              if ($warehouse_qty && !empty($warehouse_qty)) {
              foreach ($warehouse_qty as $wh_qty) {
              if (isset($wh_qty['quantity']) && !empty($wh_qty['quantity'])) {
              $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => $wh_qty['quantity'], 'rack' => $wh_qty['rack'], 'avg_cost' => $data['cost']));

              if (!$product_attributes) {
              $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
              $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
              $unit_cost = $data['cost'];
              if ($tax_rate) {
              if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
              if ($data['tax_method'] == '0') {
              $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
              $net_item_cost = $data['cost'] - $pr_tax_val;
              $item_tax = $pr_tax_val * $wh_qty['quantity'];
              } else {
              $net_item_cost = $data['cost'];
              $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
              $unit_cost = $data['cost'] + $pr_tax_val;
              $item_tax = $pr_tax_val * $wh_qty['quantity'];
              }
              } else {
              $net_item_cost = $data['cost'];
              $item_tax = $tax_rate->rate;
              }
              } else {
              $net_item_cost = $data['cost'];
              $item_tax = 0;
              }

              $subtotal = (($net_item_cost * $wh_qty['quantity']) + $item_tax);

              $item = array(
              'product_id' => $product_id,
              'product_code' => $data['code'],
              'product_name' => $data['name'],
              'net_unit_cost' => $net_item_cost,
              'unit_cost' => $unit_cost,
              'real_unit_cost' => $unit_cost,
              'quantity' => $wh_qty['quantity'],
              'quantity_balance' => $wh_qty['quantity'],
              'item_tax' => $item_tax,
              'tax_rate_id' => $tax_rate_id,
              'tax' => $tax,
              'subtotal' => $subtotal,
              'warehouse_id' => $wh_qty['warehouse_id'],
              'date' => date('Y-m-d'),
              'status' => 'received',
              );
              $this->db->insert('purchase_items', $item);
              $this->site->syncProductQty($product_id, $wh_qty['warehouse_id']);
              }
              }
              }
              } */

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr_details = $this->getPrductVariantByPIDandName($product_id, $pr_attr['name']);

                    $pr_attr['product_id'] = $product_id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    if ($pr_attr_details) {
                        $option_id = $pr_attr_details->id;
                    } else {
                        $this->db->insert('product_variants', $pr_attr);
                        $option_id = $this->db->insert_id();
                    }
                    /* if ($pr_attr['quantity']) {
                      if (!$this->getWarehouseProductVariant($variant_warehouse_id, $product_id, $option_id)) {
                      $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']));
                      }
                      $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
                      $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
                      $unit_cost = $data['cost'];
                      if ($tax_rate) {
                      if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                      if ($data['tax_method'] == '0') {
                      $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                      $net_item_cost = $data['cost'] - $pr_tax_val;
                      $item_tax = $pr_tax_val * $pr_attr['quantity'];
                      } else {
                      $net_item_cost = $data['cost'];
                      $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                      $unit_cost = $data['cost'] + $pr_tax_val;
                      $item_tax = $pr_tax_val * $pr_attr['quantity'];
                      }
                      } else {
                      $net_item_cost = $data['cost'];
                      $item_tax = $tax_rate->rate;
                      }
                      } else {
                      $net_item_cost = $data['cost'];
                      $item_tax = 0;
                      }

                      $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                      $item = array(
                      'product_id' => $product_id,
                      'product_code' => $data['code'],
                      'product_name' => $data['name'],
                      'net_unit_cost' => $net_item_cost,
                      'unit_cost' => $unit_cost,
                      'quantity' => $pr_attr['quantity'],
                      'option_id' => $option_id,
                      'quantity_balance' => $pr_attr['quantity'],
                      'item_tax' => $item_tax,
                      'tax_rate_id' => $tax_rate_id,
                      'tax' => $tax,
                      'subtotal' => $subtotal,
                      'warehouse_id' => $variant_warehouse_id,
                      'date' => date('Y-m-d'),
                      'status' => 'received',
                      );
                      $this->db->insert('purchase_items', $item);
                      }

                      foreach ($warehouses as $warehouse) {
                      if (!$this->getWarehouseProductVariant($warehouse->id, $product_id, $option_id)) {
                      $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0));
                      }
                      } */

                    // $this->site->syncVariantQty($option_id, $variant_warehouse_id);
                }
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', array('product_id' => $product_id, 'photo' => $photo));
                }
            }

            return $product_id;
        }
        return false;
    }

    public function getPrductVariantByPIDandName($product_id, $name) {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id, 'name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addAjaxProduct($data) {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            return $this->getProductByID($product_id);
        }
        return false;
    }

    public function add_products($products = array()) {
        if (!empty($products)) {
            foreach ($products as $product) {

                if (!empty($product['variants'])) {
                    $variants = explode('|', $product['variants']);
                }
                unset($product['variants']);

                if ($this->db->insert('products', $product)) {
                    $product_id = $this->db->insert_id();
                    if (is_array($variants)) {
                        foreach ($variants as $variant) {
                            if ($variant && trim($variant) != '') {
                                $vat = array('product_id' => $product_id, 'name' => trim($variant));
                                $this->db->insert('product_variants', $vat);
                            }
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function add_import_csv_products($products = array(),$product_with_variants = array(), $product_with_color = array()) {
        if (!empty($products)) {
            foreach ($products as $product) {
                $variants = explode(',', $product['variants']); // Variants 
                // $color = explode(',', $product['color']); 
                $variant_costs  = explode(',', $product['cost']);
                $variant_prices = explode(',', $product['price']);
                $variant_mrps   = explode(',', $product['mrp']);
                $variant_discounts = explode(',', $product_with_variants['variant_discount_on_mrp']);   
                    unset($product['variants']);   
                    unset($product['color']);        
                    $has_variants = !empty(array_filter($variants));

            if ($has_variants) {
                $product['cost'] = 0;
                $product['price'] = 0;
                $product['mrp'] = 0;
                $product['discount_on_mrp'] = 0;
            }

                $warehouse_arr = array();
                $product_attributes_arr = array();

                if (isset($product['warehouse'])) {
                    if (!empty($product['warehouse'])) {
                        $warehouse_arr['warehouse_id'] = $product['warehouse'];
                        $warehouse_arr['avg_cost'] = $product['cost'];
                    }
                    unset($product['warehouse']);
                }

                if (isset($product['quantity'])) {
                    if (!empty($product['quantity']) && $product['quantity'] > 0) {
                        $warehouse_arr['quantity'] = $product['quantity'];
                    } else {
                        $warehouse_arr['quantity'] = 0;
                        $product['quantity'] = 0;
                    }
                }

                if (!isset($warehouse_arr['warehouse_id']) && isset($product['quantity'])) {
                    unset($product['quantity']);
                }

                if (isset($warehouse_arr['warehouse_id']) && !isset($product['quantity'])) {
                    $warehouse_arr['quantity'] = 0;
                    $product['quantity'] = 0;
                }
                $StoreRow = $this->db->select('id, ref_id')->where('store_add_urbanpiper', 1)->get('sma_up_stores')->row();
                $available = $product['available'];
                unset($product['available']);
                if ($this->db->insert('products', $product)) {
                    $product_id = $this->db->insert_id();

                    // Urbanpiper

                    if ($product['up_items'] == '1') {
                        $field = array(
                            'product_id' => $product_id,
                            'product_code' => $product['code'],
                            'price' => $product['up_price'],
                            'food_type_id' => $product['food_type_id'],
                        );
                        $this->db->insert('sma_up_products', $field);
                        $fieldPlatform = array(
                            'product_id' => $product_id,
                            'product_code' => $product['code'],
                            'available' => $available,
                            'up_store_id' => $StoreRow->id,
                            'up_store_ref_id' => $StoreRow->ref_id,
                        );
                        $this->db->insert('sma_up_products_platform', $fieldPlatform);
                    }

                    // End Urbanpiper

                    if (isset($warehouse_arr['warehouse_id'])) {
                        $warehouse_arr['product_id'] = $product_id;
                        if ($this->db->insert('warehouses_products', $warehouse_arr)) {
                            $warehouses_products_id = $this->db->insert_id();

                            $tax_details = $this->site->getTaxRateByID($product['tax_rate']);

                            if ($tax_details) {

                                $tax_rate_id = $tax_details ? $tax_details->id : NULL;
                                $tax = $tax_details ? (($tax_details->type == 1) ? $tax_details->rate . "%" : $tax_details->rate) : NULL;
                                $unit_cost = $product['cost'];
                                if ($tax_details) {
                                    if ($tax_details->type == 1 && $tax_details->rate != 0) {
                                        if ($product['tax_method'] == 0) {
                                            $pr_tax_val = ($product['cost'] * $tax_details->rate) / (100 + $tax_details->rate);
                                            $net_item_cost = $product['cost'] - $pr_tax_val;
                                            $item_tax = $pr_tax_val * $product['quantity'];
                                        } else {
                                            $net_item_cost = $product['cost'];
                                            $pr_tax_val = ($product['cost'] * $tax_details->rate) / 100;
                                            $unit_cost = $product['cost'] + $pr_tax_val;
                                            $item_tax = $pr_tax_val * $product['quantity'];
                                        }
                                    } else {
                                        $net_item_cost = $product['cost'];
                                        $item_tax = $tax_details->rate;
                                    }
                                } else {
                                    $net_item_cost = $product['cost'];
                                    $item_tax = 0;
                                }

                                $subtotal = (($net_item_cost * $product['quantity']) + $item_tax);
                                $item = array(
                                    'product_id' => $product_id,
                                    'product_code' => $product['code'],
                                    'product_name' => $product['name'],
                                    'net_unit_cost' => $net_item_cost,
                                    'unit_cost' => $unit_cost,
                                    'quantity' => $product['quantity'],
                                    'quantity_balance' => $product['quantity'],
                                    'item_tax' => $item_tax,
                                    'tax_rate_id' => $tax_rate_id,
                                    'tax' => $tax,
                                    'subtotal' => $subtotal,
                                    'warehouse_id' => $warehouse_arr['warehouse_id'],
                                    'date' => date('Y-m-d'),
                                    'status' => 'received',
                                );

                                if ($this->db->insert('purchase_items', $item)) {
                                    $purchase_items_id = $this->db->insert_id();
                                }
                            } else {

                                $subtotal = $product['cost'] * $product['quantity'];
                                $item = array(
                                    'product_id' => $product_id,
                                    'product_code' => $product['code'],
                                    'product_name' => $product['name'],
                                    'net_unit_cost' => $product['cost'],
                                    'unit_cost' => $product['cost'],
                                    'quantity' => $product['quantity'],
                                    'quantity_balance' => $product['quantity'],
                                    'item_tax' => 0,
                                    'tax_rate_id' => 0,
                                    'tax' => '',
                                    'subtotal' => $subtotal,
                                    'warehouse_id' => $warehouse_arr['warehouse_id'],
                                    'date' => date('Y-m-d'),
                                    'status' => 'received',
                                );

                                if ($this->db->insert('purchase_items', $item)) {
                                    $purchase_items_id = $this->db->insert_id();
                                }
                            }
                        }
                    }

                    $warehouses = $this->site->getAllWarehouses();

                    if ($has_variants) {
                        $variant_discounts = [];
                        foreach ($product_with_variants as $variant_data) {
                            if ($variant_data['product_code'] == $product['code']) {
                                $variant_discounts[] = $variant_data['variant_discount_on_mrp'];
                            }
                        }
                        foreach ($variants as $index => $variant) {
                            $variant = trim($variant);                                            
                        if ($variant && trim($variant) != '') {                            
                            $vat = array(
                                'product_id' => $product_id,
                                'name' => $variant,
                                'cost' => isset($variant_costs[$index]) ? trim($variant_costs[$index]) : 0,
                                'mrp' => isset($variant_mrps[$index]) ? trim($variant_mrps[$index]) : 0,
                                'price' => isset($variant_prices[$index]) ? trim($variant_prices[$index]) : 0,
                                'quantity' => isset($product['quantity']) ? $product['quantity'] : 0,
                                'group_id' => 1,
                                'variant_discount_on_mrp' => isset($variant_discounts[$index]) ? trim($variant_discounts[$index]) : 0

                            );                            
                            if (!empty($product_data)) {
    
                                $vat = array_merge($vat, $product_with_variants);
                            }

                            if ($this->db->insert('product_variants', $vat)) {
                            
                                $option_id = $this->db->insert_id();

                                if ($product['quantity'] != 0) {
                                    $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_arr['warehouse_id'], 'quantity' => $product['quantity']));
                                    $warehouses_products_variants_id = $this->db->insert_id();
                                }

                                foreach ($warehouses as $warehouse) {
                                    if (!$this->getWarehouseProductVariant($warehouse->id, $product_id, $option_id)) {
                                        $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0));
                                        $warehouses_products_variants_id = $this->db->insert_id();
                                    }
                                    //else{
                                    //	$this->site->syncVariantQty($option_id, $warehouse->id);
                                    //}
                                }
                            }
                        }
                    }
                }
                if(!empty($product_with_color)){
                    foreach ($product_with_color as $variant_data) {
                        // Only add color variant if it belongs to this product
                        if(isset($variant_data['product_code']) && $variant_data['product_code'] == $product['code']) {
                            unset($variant_data['product_code']);  // Remove the product_code as it's not a column in the table
                            $variant_data['product_id'] = $product_id;
                            
                            if ($this->db->insert('product_variants', $variant_data)) {
                                $option_id = $this->db->insert_id();
                                if (isset($warehouse_arr['quantity']) && $warehouse_arr['quantity'] != 0) {
                                    $this->db->insert('warehouses_products_variants', array(
                                        'option_id' => $option_id, 
                                        'product_id' => $product_id, 
                                        'warehouse_id' => $warehouse_arr['warehouse_id'], 
                                        'quantity' => $warehouse_arr['quantity']
                                    ));
                                }
                                
                                // Add to all warehouses if needed
                                if(isset($warehouses) && is_array($warehouses)) {
                                    foreach ($warehouses as $warehouse) {
                                        if (!$this->getWarehouseProductVariant($warehouse->id, $product_id, $option_id)) {
                                            $this->db->insert('warehouses_products_variants', array(
                                                'option_id' => $option_id, 
                                                'product_id' => $product_id, 
                                                'warehouse_id' => $warehouse->id, 
                                                'quantity' => 0
                                            ));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                    $DatalogArr = array('product_data' => $product, 'items' => $item, 'warehouse_qty' => $warehouse_arr, 'product_attributes' => $variants, 'photos' => '', 'type' => 'import_csv');
                    $DataLog = array(
                        'action_type' => 'Add',
                        'product_id' => $product_id,
                        'quantity' => '',
                        'action_reff_id' => $product_id,
                        'action_affected_data' => json_encode($DatalogArr),
                        'action_comment' => 'Add products',
                    );
                    $this->sma->setUserActionLog($DataLog);
                }
            }
            return true;
        }
        return false;
    }

    public function getProductNames($term, $limit = 20) {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('product_variants') . '.name as vname')
                ->where("type != 'combo' AND "
                        . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");

        $this->db->join('product_variants', 'product_variants.product_id=products.id', 'left')
                // ->where('' . $this->db->dbprefix('product_variants') . '.name', NULL)
                ->group_by('products.id')->limit($limit);

        $q = $this->db->get('products');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getQASuggestions($term, $limit = 50, $warehouse_id) {
        // First, try to find exact match for product code
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, COALESCE(' . $this->db->dbprefix('warehouses_products') . '.quantity, 0) as product_qty', FALSE)
                ->from('products')
                ->join('warehouses_products', 'products.id = warehouses_products.product_id AND warehouses_products.warehouse_id = ' . (int)$warehouse_id, 'left')
                ->where("type != 'combo'")
                ->where('products.code', $term)
                ->group_by('products.id, code, ' . $this->db->dbprefix('products') . '.name')
                ->limit(1);
        
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result(); // Return exact match only
        }
        
        // If no exact match, try partial matches
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, COALESCE(' . $this->db->dbprefix('warehouses_products') . '.quantity, 0) as product_qty', FALSE)
                ->from('products')
                ->join('warehouses_products', 'products.id = warehouses_products.product_id AND warehouses_products.warehouse_id = ' . (int)$warehouse_id, 'left')
                ->where("type != 'combo'")
                ->group_start()
                ->like('products.name', $term)
                ->or_like('products.code', $term)
                ->or_like('products.article_code', $term)
                ->or_like("CONCAT(" . $this->db->dbprefix('products') . ".name, ' (', code, ')')", $term)
                ->group_end()
                ->order_by('' . $this->db->dbprefix('products') . '.name', 'ASC')
                ->group_by('products.id, code, ' . $this->db->dbprefix('products') . '.name')
                ->limit(200);
        
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function getProductsForPrinting($term, $limit = 5) {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price')
                ->where("(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
                ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductBatchesForBarcode($product_id, $warehouse_ids = null, $option_id = null)
    {
        // Note: We intentionally do NOT filter by warehouse_id here so that
        // all warehouses' batches are visible on the barcode screen.
        // To avoid mixing quantities from different warehouses into a single
        // number, we keep warehouse_id in the grouping.
        $this->db->select('warehouse_id, batch_number, expiry, SUM(quantity_balance) AS quantity', false)
                 ->from('purchase_items')
                 ->where('product_id', (int) $product_id)
                 ->where('quantity_balance >', 0)
                 ->where('(batch_number IS NOT NULL AND batch_number != "")'); // Exclude empty batch numbers

        if ($option_id !== null) {
            $this->db->where('option_id', (int) $option_id);
        }

        $this->db->group_by('warehouse_id, batch_number, expiry');
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result();
        }

        return array();
    }

    public function getVariantTotalQuantityAcrossWarehouses($product_id, $option_id)
    {
        $this->db->select('SUM(quantity) AS total_qty', false)
                 ->from('warehouses_products_variants')
                 ->where('product_id', (int) $product_id)
                 ->where('option_id', (int) $option_id);

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return $row->total_qty !== null ? (float) $row->total_qty : 0.0;
        }

        return 0.0;
    }

    // public function updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants, $postype_data = NULL) {
    //     $DatalogArr = array('product_data' => $data, 'items' => $items, 'warehouse_qty' => $warehouse_qty, 'product_attributes' => $product_attributes, 'photos' => $photos, 'postype_data' => $postype_data, 'variants' => $update_variants);
    //     $DataLog = array(
    //         'action_type' => 'Edit',
    //         'product_id' => $id,
    //         'quantity' => '',
    //         'action_reff_id' => $id,
    //         'action_affected_data' => json_encode($DatalogArr),
    //         'action_comment' => 'Edit products',
    //     );
    //     $this->sma->setUserActionLog($DataLog);
    //     if ($this->db->update('products', $data, array('id' => $id))) {

    //       // Urbanpiper for restaurant

    //       if (($data['up_items'] == '1' && $postype_data['pos_type'] == 'restaurant') || $data['up_items'] == '1' && $postype_data['pos_type'] == 'bakery') {

    //         $update_id = $postype_data['up_update_id'];
    //         unset($postype_data['pos_type']);
    //         unset($postype_data['up_update_id']);

    //         $this->db->where('id', $update_id)->update('sma_up_products', $postype_data);
    //     }
    //     // End Urbanpiper     

    //         if ($items) {
    //             $this->db->delete('combo_items', array('product_id' => $id));
    //             foreach ($items as $item) {
    //                 $item['product_id'] = $id;
    //                 $this->db->insert('combo_items', $item);
    //             }
    //         }

    //         $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

    //         if ($warehouse_qty && !empty($warehouse_qty)) {
    //             foreach ($warehouse_qty as $wh_qty) {
    //                 $this->db->update('warehouses_products', array('rack' => $wh_qty['rack']), array('product_id' => $id, 'warehouse_id' => $wh_qty['warehouse_id']));
    //             }
    //         }

    //         if ($update_variants) {
    //             $this->db->update_batch('product_variants', $update_variants, 'id');
    //         }

    //         if ($photos) {
    //             foreach ($photos as $photo) {
    //                 $this->db->insert('product_photos', array('product_id' => $id, 'photo' => $photo));
    //             }
    //         }

    //         if ($product_attributes) {
    //             foreach ($product_attributes as $pr_attr) {

    //                 $pr_attr['product_id'] = $id;
    //                 $variant_warehouse_id = $pr_attr['warehouse_id'];
    //                 unset($pr_attr['warehouse_id']);
    //                 $this->db->where('product_id', $pr_attr['product_id']);
    //                 $query = $this->db->get('product_variants');
    //                 if ($query->num_rows() > 0) {
    //                     $this->db->where('product_id', $pr_attr['product_id']);
    //                     $this->db->where('group_id', $pr_attr['group_id']);
    //                     $this->db->update('product_variants', ['name' => $pr_attr['name']]);
    //                 } else {
    //                     // Insert new
    //                     $this->db->insert('product_variants', $pr_attr);
    //                     $option_id = $this->db->insert_id();
    //                 }
    //                 if ($pr_attr['quantity'] != 0) {
    //                     $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']));

    //                     $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
    //                     $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
    //                     $unit_cost = $data['cost'];
    //                     if ($tax_rate) {
    //                         if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
    //                             if ($data['tax_method'] == '0') {
    //                                 $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
    //                                 $net_item_cost = $data['cost'] - $pr_tax_val;
    //                                 $item_tax = $pr_tax_val * $pr_attr['quantity'];
    //                             } else {
    //                                 $net_item_cost = $data['cost'];
    //                                 $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
    //                                 $unit_cost = $data['cost'] + $pr_tax_val;
    //                                 $item_tax = $pr_tax_val * $pr_attr['quantity'];
    //                             }
    //                         } else {
    //                             $net_item_cost = $data['cost'];
    //                             $item_tax = $tax_rate->rate;
    //                         }
    //                     } else {
    //                         $net_item_cost = $data['cost'];
    //                         $item_tax = 0;
    //                     }

    //                     $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
    //                     $item = array(
    //                         'product_id' => $id,
    //                         'product_code' => $data['code'],
    //                         'product_name' => $data['name'],
    //                         'net_unit_cost' => $net_item_cost,
    //                         'unit_cost' => $unit_cost,
    //                         'quantity' => $pr_attr['quantity'],
    //                         'option_id' => $option_id,
    //                         'quantity_balance' => $pr_attr['quantity'],
    //                         'item_tax' => $item_tax,
    //                         'tax_rate_id' => $tax_rate_id,
    //                         'tax' => $tax,
    //                         'subtotal' => $subtotal,
    //                         'warehouse_id' => $variant_warehouse_id,
    //                         'date' => date('Y-m-d'),
    //                         'status' => 'received',
    //                     );
    //                     $this->db->insert('purchase_items', $item);
    //                 }
    //             }
    //         }

    //         $this->site->syncQuantity(NULL, NULL, NULL, $id);
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }
    public function updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants, $postype_data = NULL) {
        $DatalogArr = array('product_data' => $data, 'items' => $items, 'warehouse_qty' => $warehouse_qty, 'product_attributes' => $product_attributes, 'photos' => $photos, 'postype_data' => $postype_data, 'variants' => $update_variants);
        $DataLog = array(
            'action_type' => 'Edit',
            'product_id' => $id,
            'quantity' => '',
            'action_reff_id' => $id,
            'action_affected_data' => json_encode($DatalogArr),
            'action_comment' => 'Edit products',
        );
        $this->sma->setUserActionLog($DataLog);
        if ($this->db->update('products', $data, array('id' => $id))) {

          // Urbanpiper for restaurant

          if (($data['up_items'] == '1' && $postype_data['pos_type'] == 'restaurant') || $data['up_items'] == '1' && $postype_data['pos_type'] == 'bakery') {

            $update_id = $postype_data['up_update_id'];
            unset($postype_data['pos_type']);
            unset($postype_data['up_update_id']);

            $this->db->where('id', $update_id)->update('sma_up_products', $postype_data);
        }
        // End Urbanpiper     

            if ($items) {
                $this->db->delete('combo_items', array('product_id' => $id));
                foreach ($items as $item) {
                    $item['product_id'] = $id;
                    $this->db->insert('combo_items', $item);
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    $this->db->update('warehouses_products', array('rack' => $wh_qty['rack']), array('product_id' => $id, 'warehouse_id' => $wh_qty['warehouse_id']));
                }
            }

            if ($update_variants) {
                $this->db->update_batch('product_variants', $update_variants, 'id');
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', array('product_id' => $id, 'photo' => $photo));
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {

                    $pr_attr['product_id'] = $id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    // $this->db->where('product_id', $pr_attr['product_id']);
                    // $query = $this->db->get('product_variants');
                    if($pr_attr['group_id']== '1'){
                        $this->db->where('product_id', $pr_attr['product_id']);
                        $this->db->where('group_id', $pr_attr['group_id']);
                        $this->db->where('name', $pr_attr['name']);
                        $query = $this->db->get('product_variants');
                        if ($query->num_rows() > 0) {
                            // Update existing
                            $row = $query->row();
                            $option_id = $row->id;
                            $this->db->where('id', $option_id);
                            $this->db->update('product_variants', $pr_attr);
                        } else {
                            // Insert new variant — create job-work raw products immediately (same as product add)
                            $this->db->insert('product_variants', $pr_attr);
                            $option_id = $this->db->insert_id();
                            $job_work_ids = $this->getJobWorkIdsForMainProduct($id);
                            if (!empty($job_work_ids)) {
                                $this->createJobWorkRawProductsForVariants($id, $data, $job_work_ids, array($pr_attr));
                            }
                        }
                    }else{
                        $this->db->where('product_id', $pr_attr['product_id']);
                        $this->db->where('group_id', $pr_attr['group_id']);
                        $query = $this->db->get('product_variants');
                        if ($query->num_rows() > 0) {
                            $this->db->where('product_id', $pr_attr['product_id']);
                            // $this->db->where('name', $pr_attr['name']);
                            $this->db->where('group_id', $pr_attr['group_id']);
                            $this->db->update('product_variants', ['name' => $pr_attr['name']]);
                        } else {
                            $this->db->insert('product_variants', $pr_attr);
                            $option_id = $this->db->insert_id();
                        }
                    }
                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']));

                        $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
                        $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
                        $unit_cost = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost = $data['cost'] + $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item = array(
                            'product_id' => $id,
                            'product_code' => $data['code'],
                            'product_name' => $data['name'],
                            'net_unit_cost' => $net_item_cost,
                            'unit_cost' => $unit_cost,
                            'quantity' => $pr_attr['quantity'],
                            'option_id' => $option_id,
                            'quantity_balance' => $pr_attr['quantity'],
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $tax_rate_id,
                            'tax' => $tax,
                            'subtotal' => $subtotal,
                            'warehouse_id' => $variant_warehouse_id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
                        );
                        $this->db->insert('purchase_items', $item);
                    }
                }
            }

            $this->site->syncQuantity(NULL, NULL, NULL, $id);
            return true;
        } else {
            return false;
        }
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id) {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            if ($this->db->update('warehouses_products_variants', array('quantity' => $quantity), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function updatePrice($data = array()) {
        $varaint = array();
        foreach (array_keys($data) as $key) {
            if ($data[$key]['Variants_Name']) {
                $varaint[$key]['product_code'] = $data[$key]['code'];
                $varaint[$key]['Variants_Name'] = $data[$key]['Variants_Name'];
                $varaint[$key]['Variants_Price'] = $data[$key]['Variants_Price'];
                $varaint[$key]['Variants_Mrp'] = $data[$key]['Variants_Mrp'];
                $varaint[$key]['Variants_Discount_on_mrp'] = $data[$key]['Variants_Discount_on_mrp'];
            }
            unset($data[$key]['Variants_Name']);
            unset($data[$key]['Variants_Price']);
            unset($data[$key]['Product_Name']);
            unset($data[$key]['Variants_Discount_on_mrp']);
            unset($data[$key]['Variants_Mrp']);
        }
        if (!empty($varaint)) {
            $this->updateVariantPrice($varaint);
        }

        if ($this->db->update_batch('products', $data, 'code')) {
            return true;
        }
        return false;
    }

    /**
     * Varaint Price Updated
     * @param type $data
     * @return boolean
     */
    public function updateVariantPrice($data = array()) {
        foreach ($data as $varaintdata) {
            $getproductid = $this->db->select('id')->where(['code' => $varaintdata['product_code']])->get('products')->row();
            if (!$getproductid) {
                continue;
            }
            $productid = $getproductid->id;

            $expvariant = explode(",", $varaintdata['Variants_Name']);
            $extprice = explode(",", $varaintdata['Variants_Price']);
            $Variants_Mrp = explode(",", $varaintdata['Variants_Mrp']);
            $Variants_Discount_on_mrp = explode(",", $varaintdata['Variants_Discount_on_mrp']);
            if (is_array($expvariant)) {
                foreach ($expvariant as $key => $expv) {
                    $updatedata = ["price" => (isset($extprice[$key]) ? $extprice[$key] : 0),"mrp" => (isset($Variants_Mrp[$key]) ? $Variants_Mrp[$key] : 0),"variant_discount_on_mrp" => (isset($Variants_Discount_on_mrp[$key]) ? $Variants_Discount_on_mrp[$key] : 0), "updated_at" => date('Y-m-d H:i:s')];
                    $variantname = rtrim(ltrim($expv));
                    $this->db->where(["product_id" => $productid, "name" => $variantname])->update('product_variants', $updatedata);
                }
            }
        }
    }

    public function updateUPProductPrice($data = array()) {
        if ($this->db->update_batch('up_products', $data, 'product_code')) {
            return true;
        }
        return false;
    }

    public function deleteProduct($id) {
        if ($this->db->delete('products', array('id' => $id)) && $this->db->delete('warehouses_products', array('product_id' => $id))) {
            $this->db->delete('warehouses_products_variants', array('product_id' => $id));
            $this->db->delete('product_variants', array('product_id' => $id));
            $this->db->delete('product_photos', array('product_id' => $id));
            $this->db->delete('product_prices', array('product_id' => $id));
            return true;
        }
        return FALSE;
    }

    public function totalCategoryProducts($category_id) {
        $q = $this->db->get_where('products', array('category_id' => $category_id));
        return $q->num_rows();
    }

    public function getCategoryByCode($code) {
        $q = $this->db->get_where('categories', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWarehouseIdByWarehouseCode($code) {
        $q = $this->db->get_where('warehouses', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTaxRateByName($name) {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAdjustmentByID($id) {
        $q = $this->db->get_where('adjustments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAdjustmentItems($adjustment_id) {
        $this->db->select('adjustment_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details, product_variants.name as variant')
                ->join('products', 'products.id=adjustment_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=adjustment_items.option_id', 'left')
                ->group_by('adjustment_items.id')
                ->order_by('id', 'asc');

        $this->db->where('adjustment_id', $adjustment_id);

        $q = $this->db->get('adjustment_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $getProdutQty = $this->db->select('quantity')->where(['product_id' => $row->product_id, 'warehouse_id' => $row->warehouse_id])->get('sma_warehouses_products')->row();
                $row->product_qty = $getProdutQty->quantity;
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    //Backup Function
    public function syncAdjustment($data = array()) {
        if (!empty($data)) {
            $where_clause = array('product_id' => $data['product_id'], 'option_id' => $data['option_id'], 'warehouse_id' => $data['warehouse_id'], 'status' => 'received', 'batch_number' => $data['batch_number']);
            /* if ($purchase_item = $this->site->getPurchasedItem($where_clause)) {
              if ($purchase_item->quantity == 0) {
              $quantity_balance = ($data['type'] == 'subtraction') ? $purchase_item->quantity_balance - $data['quantity'] : $purchase_item->quantity_balance + $data['quantity'];
              $quantity = ($data['type'] == 'subtraction') ? $purchase_item->quantity_balance : $purchase_item->quantity + $data['quantity'];

              $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance, 'quantity' => $quantity,), array('id' => $purchase_item->id));
              } else {
              $quantity_balance = ($data['type'] == 'subtraction') ? $purchase_item->quantity_balance - $data['quantity'] : $purchase_item->quantity_balance + $data['quantity'];

              $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $purchase_item->id));
              }
              } else { */
            $pr = $this->site->getProductByID($data['product_id']);
            $product_cost = isset($data['net_unit_cost']) && $data['net_unit_cost'] !== '' ? $data['net_unit_cost'] : ($pr->cost !== null ? $pr->cost : 0);
            $real_unit_cost = isset($data['real_unit_cost']) && $data['real_unit_cost'] !== '' ? $data['real_unit_cost'] : $product_cost;
            $unit_cost = $product_cost;
            $item_net_cost = $unit_cost;
            $item_tax = 0;
            $unit_tax = 0;
            $pr_item_tax = 0;
            $tax = '';
            $tax_rate_id = (!empty($data['tax_rate_id']) && is_numeric($data['tax_rate_id'])) ? $data['tax_rate_id'] : $pr->tax_rate;
            $tax_method = (isset($data['tax_method']) && $data['tax_method'] !== '') ? $data['tax_method'] : $pr->tax_method;
            if (isset($tax_rate_id) && $tax_rate_id != 0) {
                $tax_details = $this->site->getTaxRateByID($tax_rate_id);
                if ($tax_details) {
                    $taxmethod = ($tax_method === '' || $tax_method === null) ? $pr->tax_method : $tax_method;
                    if ($tax_details->type == 1 && $tax_details->rate != 0) {
                        if ($taxmethod == 1) {
                            $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                            $tax = $tax_details->rate . "%";
                        } else {
                            $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                            $tax = $tax_details->rate . "%";
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                    } elseif ($tax_details->type == 2) {
                        if ($taxmethod == 1) {
                            $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                            $tax = $tax_details->rate . "%";
                        } else {
                            $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                            $tax = $tax_details->rate . "%";
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $item_tax = $this->sma->formatDecimal($tax_details->rate);
                        $tax = $tax_details->rate;
                    }
                    $item_tax = $item_tax ? $item_tax : 0;
                    $pr_item_tax = $this->sma->formatDecimal($item_tax * $data['quantity'], 4);
                    $unit_tax = $item_tax;
                }
            }
            $product_unit_id = $data['unit_id'] ? $data['unit_id'] : ($data['product_unit_id'] ? $data['product_unit_id'] : $pr->purchase_unit);
            $unit = $this->site->getUnitByID($product_unit_id);
            if (!$unit) {
                $unit = $this->site->getUnitByID($pr->unit);
            }
            if (!$unit && !empty($data['product_unit_id']) && !is_numeric($data['product_unit_id'])) {
                $q = $this->db->get_where('units', array('name' => $data['product_unit_id']), 1);
                $unit = $q->num_rows() > 0 ? $q->row() : FALSE;
            }
            $item = array(
                'product_id' => $data['product_id'],
                'product_code' => $pr->code,
                'product_name' => $pr->name,
                'net_unit_cost' => $item_net_cost,
                'adjustment_id' => !empty($data['adjustment_id']) ? $data['adjustment_id'] : null,
                'unit_cost' => $this->sma->formatDecimal($item_net_cost + $unit_tax, 4),
                'real_unit_cost' => $real_unit_cost,
                'quantity' => !empty($data['adjustment_id']) ? (($data['type'] == 'subtraction') ? (0 - $data['quantity']) : $data['quantity']) : 0,
                'option_id' => $data['option_id'] ? $data['option_id'] : 0,
                'quantity_balance' => ($data['type'] == 'subtraction') ? (0 - $data['quantity']) : $data['quantity'],
                'item_tax' => $pr_item_tax,
                'unit_tax' => $unit_tax,
                'tax_rate_id' => $tax_rate_id,
                'tax' => $tax,
                'tax_method' => $tax_method,
                'subtotal' => $this->sma->formatDecimal(($item_net_cost * $data['quantity']) + $pr_item_tax, 4),
                'warehouse_id' => $data['warehouse_id'],
                'date' => date('Y-m-d'),
                'status' => 'received',
                'expiry' => $data['expiry'] ? $data['expiry'] : null,
                'batch_number' => $data['batch_number'] ? $data['batch_number'] : null,
                'product_unit_id' => $unit ? $unit->id : (is_numeric($product_unit_id) ? $product_unit_id : $pr->purchase_unit),
                'product_unit_code' => $unit ? $unit->code : NULL,
                'hsn_code' => $pr->hsn_code,
                'unit_quantity' => !empty($data['unit_quantity']) ? $data['unit_quantity'] : 1,
            );
            $this->db->insert('purchase_items', $item);
            // }

            $this->site->syncProductQty($data['product_id'], $data['warehouse_id'], $data['batch_number']);
            if ($data['option_id']) {
                $this->site->syncVariantQty($data['option_id'], $data['warehouse_id'], $data['product_id'], $data['batch_number']);
            }

            /* // Urbanpiper Stock Manage 
              if($this->Settings->pos_type == 'restaurant'){
              $this->load->model("Urban_piper_model","UPM");

              $this->UPM->Product_out_of_stock([$data['product_id']], $data['warehouse_id']);
              } */
        }
    }

    public function getPurchasedItem($where_clause, $quantity, $adjustment_type) {

        $orderby = ($this->Settings->accounting_method == 1) ? 'desc' : 'asc';
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);

        if ($this->Settings->product_batch_setting > 0 && $where_clause['batch_number']) {
            $this->db->where('batch_number', $where_clause['batch_number']);
        }
        unset($where_clause['batch_number']);

        if ($where_clause['adjustment_id'] == TRUE) {
            $this->db->where('(adjustment_id IS NOT NULL)');
            unset($where_clause['adjustment_id']);
        } else if (!$this->Settings->overselling) {
            $this->db->where('(purchase_id IS NOT NULL OR transfer_id IS NOT NULL OR adjustment_id IS NOT NULL)');
        }

        if ($where_clause['status']) {
            $this->db->where('status', $where_clause['status']);
            unset($where_clause['status']);
        } else {
            $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        }

        if ($adjustment_type = 'subtraction') {
            $this->db->where(" ( `quantity_balance` - $quantity ) >= '0' ");
        } else {
            $this->db->where(" ( `quantity_balance` + $quantity ) <= `quantity` OR `quantity` == '0' ");
        }

        $this->db->where($where_clause);

        $q = $this->db->get('purchase_items');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function syncAdjustment_new($data = array()) {

        if (!empty($data)) {

            $where_clause = array('product_id' => $data['product_id'], 'option_id' => $data['option_id'], 'warehouse_id' => $data['warehouse_id'], 'status' => 'received', 'batch_number' => $data['batch_number']);

            if ($purchase_item = $this->getPurchasedItem($where_clause, $data['quantity'], $data['type'])) {

                $quantity_balance = ($data['type'] == 'subtraction') ? $purchase_item->quantity_balance - $data['quantity'] : $purchase_item->quantity_balance + $data['quantity'];
                $quantity = $purchase_item->quantity;

                if ($purchase_item->quantity == 0 && !$purchase_item->purchase_id) {

                    $quantity = ($data['type'] == 'subtraction') ? $purchase_item->quantity_balance : $purchase_item->quantity + $data['quantity'];
                }

                $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance, 'quantity' => $quantity), array('id' => $purchase_item->id));
            } else {

                $pr = $this->site->getProductByID($data['product_id']);
                $item = array(
                    'adjustment_id' => $data['adjustment_id'],
                    'product_id' => $data['product_id'],
                    'product_code' => $pr->code,
                    'product_name' => $pr->name,
                    'net_unit_cost' => $data['net_unit_cost'],
                    'unit_cost' => $data['cost'],
                    'real_unit_cost' => $data['real_unit_cost'],
                    'quantity' => ($data['type'] == 'subtraction') ? (0 - $data['quantity']) : $data['quantity'],
                    'option_id' => $data['option_id'] ? $data['option_id'] : 0,
                    'quantity_balance' => ($data['type'] == 'subtraction') ? (0 - $data['quantity']) : $data['quantity'],
                    'item_tax' => 0,
                    'tax_rate_id' => 1,
                    'tax' => 0,
                    'tax_method' => $data['tax_method'] ? $data['tax_method'] : $pr->tax_method,
                    'subtotal' => ($data['net_unit_cost'] * $data['quantity']),
                    'warehouse_id' => $data['warehouse_id'],
                    'date' => date('Y-m-d'),
                    'status' => 'received',
                    'expiry' => $data['expiry'] ? $data['expiry'] : null,
                    'batch_number' => $data['batch_number'] ? $data['batch_number'] : null,
                    'product_unit_id' => $data['product_unit_id'] ? $data['product_unit_id'] : $pr->purchase_unit,
                    'hsn_code' => $pr->hsn_code,
                    'unit_quantity' => $data['unit_quantity'] ? $data['unit_quantity'] : 1
                );
                $this->db->insert('purchase_items', $item);
            }

            $this->site->syncProductQty($data['product_id'], $data['warehouse_id']);
            if ($data['option_id']) {
                $this->site->syncVariantQty($data['option_id'], $data['warehouse_id'], $data['product_id']);
            }
        }
    }

    public function reverseAdjustment($id) {
        if ($products = $this->getAdjustmentItems($id)) {
            foreach ($products as $adjustment) {
                $where_clause = array('product_id' => $adjustment->product_id, 'warehouse_id' => $adjustment->warehouse_id, 'option_id' => $adjustment->option_id, 'status' => 'received');
                if ($purchase_item = $this->site->getPurchasedItem($where_clause)) {
                    $quantity_balance = $adjustment->type == 'subtraction' ? $purchase_item->quantity_balance + $adjustment->quantity : $purchase_item->quantity_balance - $adjustment->quantity;
                    $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $purchase_item->id));
                }

                $this->site->syncProductQty($adjustment->product_id, $adjustment->warehouse_id);
                if ($adjustment->option_id) {
                    $this->site->syncVariantQty($adjustment->option_id, $adjustment->warehouse_id, $adjustment->product_id);
                }
            }
        }
    }

    public function addAdjustment($data, $products) {

        if (isset($products) && is_array($products)) {
            $wh_id = isset($data['warehouse_id']) ? $data['warehouse_id'] : (isset($data['from_warehouse_id']) ? $data['from_warehouse_id'] : null);
            $this->site->prefetchQtyBeforeTransaction($products, $wh_id);
        }


        if ($this->db->insert('adjustments', $data)) {
            $adjustment_id = $this->db->insert_id();

            foreach ($products as $product) {
                $product['adjustment_id'] = $adjustment_id;

                $adjustment_item = array(
                    'adjustment_id' => $adjustment_id,
                    'product_id' => $product['product_id'],
                    'option_id' => $product['option_id'],
                    'batch_number' => $product['batch_number'] ? $product['batch_number'] : NULL,
                    'quantity' => $product['quantity'],
                    'warehouse_id' => $product['warehouse_id'],
                    'serial_no' => $product['serial_no'],
                    'type' => $product['type'],
                    'shade_id' => $product['shade_id']
                );

                $this->db->insert('adjustment_items', $adjustment_item);
                $adjustment_items_id = $this->db->insert_id();
                $this->syncAdjustment($product);

                /* Products Action Logs */
                $DatalogArr = array('adjustment_items_id' => $adjustment_items_id, 'adjustment_item' => $item);
                $DataLog = array(
                    'action_type' => 'Quantity Adjustments ',
                    'product_id' => $product['product_id'],
                    'option_id' => $product['option_id'],
                    'batch_number' => $product['batch_number'] ? $product['batch_number'] : NULL,
                    'quantity' => ($product['type'] == 'subtraction') ? 0 - $product['quantity'] : $product['quantity'],
                    'action_reff_id' => "sma_adjustments.id:$adjustment_id",
                    'action_affected_data' => json_encode($product),
                    'action_comment' => $product['type'] . ' | ' . $data['note']
                );
                $this->sma->setUserActionLog($DataLog);
                /* //Products Action Logs */
            }
            if ($this->site->getReference('qa') == $data['reference_no']) {
                $this->site->updateReference('qa');
            }
            $data['id'] = $adjustment_id;
            $this->site->syncProductTransactionHistory('add_adjustment', array('adjustment' => $data, 'items' => $products));
            return true;
        }
        return false;
    }

    public function updateAdjustment($id, $data, $products) {

        if (isset($products) && is_array($products)) {
            $wh_id = isset($data['warehouse_id']) ? $data['warehouse_id'] : (isset($data['from_warehouse_id']) ? $data['from_warehouse_id'] : null);
            $this->site->prefetchQtyBeforeTransaction($products, $wh_id);
        }

        $this->reverseAdjustment($id);
        if ($this->db->update('adjustments', $data, array('id' => $id)) &&
                $this->db->delete('adjustment_items', array('adjustment_id' => $id))) {
            $DatalogArr = array('data' => $data, 'products' => $products);
            $DataLog = array(
                'action_type' => 'Edit',
                'product_id' => '',
                'quantity' => '',
                'action_reff_id' => $id,
                'action_affected_data' => json_encode($DatalogArr),
                'action_comment' => 'Edit adjustments',
            );
            $this->sma->setUserActionLog($DataLog);
            foreach ($products as $product) {
                $product['adjustment_id'] = $id;

                $adjustment_item = array(
                    'adjustment_id' => $id,
                    'product_id' => $product['product_id'],
                    'option_id' => $product['option_id'],
                    'batch_number' => $product['batch_number'] ? $product['batch_number'] : NULL,
                    'quantity' => $product['quantity'],
                    'warehouse_id' => $product['warehouse_id'],
                    'serial_no' => $product['serial_no'],
                    'type' => $product['type'],
                    'shade_id' => $product['shade_id']
                );

                $this->db->insert('adjustment_items', $adjustment_item);
                $this->syncAdjustment($product);
            }
            $data['id'] = $id;
            $this->site->syncProductTransactionHistory('edit_adjustment', array('adjustment' => $data, 'items' => $products));
            return true;
        }
        return false;
    }

    public function deleteAdjustment($id) {
        $this->reverseAdjustment($id);
        if ($this->db->delete('adjustments', array('id' => $id)) &&
                $this->db->delete('adjustment_items', array('adjustment_id' => $id))) {
            return true;
        }
        return false;
    }

    public function getProductQuantity($product_id, $warehouse) {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return FALSE;
    }

    public function addQuantity($product_id, $warehouse_id, $quantity, $rack = NULL) {

        if ($this->getProductQuantity($product_id, $warehouse_id)) {
            if ($this->updateQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return TRUE;
            }
        } else {
            if ($this->insertQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity, $rack = NULL) {
        $product = $this->site->getProductByID($product_id);
        if ($this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity, 'rack' => $rack, 'avg_cost' => $product->cost))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity, $rack = NULL) {
        $data = $rack ? array('quantity' => $quantity, 'rack' => $rack) : $data = array('quantity' => $quantity);
        if ($this->db->update('warehouses_products', $data, array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function products_count($category_id, $subcategory_id = NULL) {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function fetch_products($category_id, $limit, $start, $subcategory_id = NULL) {

        $this->db->limit($limit, $start);
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->order_by("id", "asc");
        $query = $this->db->get("products");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id) {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function syncVariantQty($option_id) {
        $wh_pr_vars = $this->getProductWarehouseOptions($option_id);
        $qty = 0;
        foreach ($wh_pr_vars as $row) {
            $qty += $row->quantity;
        }
        if ($this->db->update('product_variants', array('quantity' => $qty), array('id' => $option_id))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getProductWarehouseOptions($option_id) {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function setRack($data) {
        if ($this->db->update('warehouses_products', array('rack' => $data['rack']), array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getSoldQty($id) {
        $this->db->select("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('sale_items') . ".quantity ) as sold, SUM( " . $this->db->dbprefix('sale_items') . ".subtotal ) as amount")
                ->from('sales')
                ->join('sale_items', 'sales.id=sale_items.sale_id', 'left')
                ->group_by("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%m')")
                ->where($this->db->dbprefix('sale_items') . '.product_id', $id)
                //->where('DATE(NOW()) - INTERVAL 1 MONTH')
                ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
                ->order_by("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchasedQty($id) {
        $this->db->select("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('purchase_items') . ".quantity ) as purchased, SUM( " . $this->db->dbprefix('purchase_items') . ".subtotal ) as amount")
                ->from('purchases')
                ->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left')
                ->group_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m')")
                ->where($this->db->dbprefix('purchase_items') . '.product_id', $id)
                //->where('DATE(NOW()) - INTERVAL 1 MONTH')
                ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
                ->order_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchasedQtyStatus($id) {
        $this->db->select("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('purchase_items') . ".quantity ) as purchased, SUM( " . $this->db->dbprefix('purchase_items') . ".subtotal ) as amount")
                ->from('purchases')
                ->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left')
                ->group_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m')")
                ->where($this->db->dbprefix('purchase_items') . '.product_id', $id)
                //->where('DATE(NOW()) - INTERVAL 1 MONTH')
                ->where($this->db->dbprefix('purchases') . '.status', 'received')
                //->group_start()->where($this->db->dbprefix('purchases') . '.status', 'received')->or_where($this->db->dbprefix('purchases') . '.status', 'partial')->or_where($this->db->dbprefix('purchases') . '.status', 'returned')->group_end()
                ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
                ->order_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllVariants() {
        $q = $this->db->get('variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseProductVariant($warehouse_id, $product_id, $option_id = NULL) {

        $this->db->where(['product_id' => $product_id, 'warehouse_id' => $warehouse_id]);
        if ($option_id) {
            $this->db->where(['option_id' => $option_id]);
            $q = $this->db->get('warehouses_products_variants', 1);
        } else {
            $q = $this->db->get('warehouses_products_variants');
        }

        $num_rows = $q->num_rows();

        if ($num_rows == 1) {
            return $q->row();
        } elseif ($num_rows > 1) {
            foreach (($q->result()) as $row) {
                $data[$row->option_id] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchaseItems($purchase_id) {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getBarcodeItemQtySum($TableName, $data) {
        $q = $this->db->get_where($TableName, $data);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTransferItems($transfer_id) {
        $q = $this->db->get_where('purchase_items', array('transfer_id' => $transfer_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUnitByCode($code) {
        $q = $this->db->get_where("units", array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getUnitById($id) {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getBrandByName($name) {
        $q = $this->db->get_where('brands', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getStockCountProducts($warehouse_id, $type, $categories = NULL, $brands = NULL) {
        $this->db->select("{$this->db->dbprefix('products')}.id as id, "
                         . "{$this->db->dbprefix('products')}.code as code, "
                         . "{$this->db->dbprefix('products')}.name as name, "
                         . "{$this->db->dbprefix('products')}.cost as cost, "
                         . "{$this->db->dbprefix('products')}.price as price, "
                         . "{$this->db->dbprefix('products')}.mrp as mrp, "
                         . "{$this->db->dbprefix('warehouses_products')}.quantity as quantity")
                ->join('warehouses_products', 'warehouses_products.product_id = products.id', 'left')
                ->where('warehouses_products.warehouse_id', $warehouse_id)
                ->where('products.type', 'standard')
                ->order_by('products.code', 'asc');
        if ($categories) {
            $r = 1;
            $this->db->group_start();
            foreach ($categories as $category) {
                if ($r == 1) {
                    $this->db->where('products.category_id', $category);
                } else {
                    $this->db->or_where('products.category_id', $category);
                }
                $r++;
            }
            $this->db->group_end();
        }
        if ($brands) {
            $r = 1;
            $this->db->group_start();
            foreach ($brands as $brand) {
                if ($r == 1) {
                    $this->db->where('products.brand', $brand);
                } else {
                    $this->db->or_where('products.brand', $brand);
                }
                $r++;
            }
            $this->db->group_end();
        }

        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStockCountProductVariants($warehouse_id, $product_id) {
        $this->db->select("{$this->db->dbprefix('product_variants')}.id, {$this->db->dbprefix('product_variants')}.name, {$this->db->dbprefix('warehouses_products_variants')}.quantity as quantity")
                ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left');
        $q = $this->db->get_where('product_variants', array('product_variants.product_id' => $product_id, 'warehouses_products_variants.warehouse_id' => $warehouse_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function addStockCount($data) {
        if ($this->db->insert('stock_counts', $data)) {
            return TRUE;
        }
        return FALSE;
    }

    public function finalizeStockCount($id, $data, $products) {
        if ($this->db->update('stock_counts', $data, array('id' => $id))) {
            foreach ($products as $product) {
                $this->db->insert('stock_count_items', $product);
            }
            return TRUE;
        }
        return FALSE;
    }

    public function getStouckCountByID($id) {
        $q = $this->db->get_where("stock_counts", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getStockCountItems($stock_count_id) {
        $q = $this->db->get_where("stock_count_items", array('stock_count_id' => $stock_count_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return NULL;
    }

    public function getAdjustmentByCountID($count_id) {
        $q = $this->db->get_where('adjustments', array('count_id' => $count_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductVariantID($product_id, $name) {
        $q = $this->db->get_where("product_variants", array('product_id' => $product_id, 'name' => $name), 1);
        if ($q->num_rows() > 0) {
            $variant = $q->row();
            return $variant->id;
        }
        return NULL;
    }

    public function getProductVariantByID($variant_id) {
        $q = $this->db->get_where("product_variants", array('id' => $variant_id), 1);
        if ($q->num_rows() > 0) {
            $variant = $q->row();
            return $variant;
        }
        return NULL;
    }

    //-------------------------- Create API MODEL For SHOP-----------------------//

    public function getCategories($parent_id = null, $param = null) {

        $this->db->select('id , code ,name ,image ,id as cat_id, in_eshop, is_active, (select count(*) from sma_categories where parent_id=cat_id) as subcat_count ,IF(parent_id IS NULL,0,parent_id) as parent_id ');

        //------------------Parent ID ---------------------//
        if ($parent_id !== null):
            $parent_id = !empty($parent_id) ? $parent_id : 0;
            $this->db->where('parent_id', $parent_id);
        endif;

        //------------------Keyword---------------------//
        if (isset($param) && is_array($param)):
            $seach_keyword = isset($param['keyword']) && !empty($param['keyword']) ? $param['keyword'] : NULL;
            if (!empty($seach_keyword)):
                $this->db->where('name', $seach_keyword);
            endif;
        endif;

        $this->db->order_by('id');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return FALSE;
    }

    public function getAllProduct($param = null) {

        $this->db->select('products.*,units.id as unit_id ,units.code as unit_code ,units.name as unit_name');
        if (is_array($param)):
            //------------------Keyword---------------------//
            $seach_keyword = isset($param['keyword']) && !empty($param['keyword']) ? $param['keyword'] : NULL;
            if (!empty($seach_keyword)):
                $this->db->like('products.name', $seach_keyword);
            endif;

            //------------------Keyword---------------------//
            $category_id = isset($param['category_id']) && !empty($param['category_id']) ? $param['category_id'] : NULL;
            if (!empty($category_id)):
                $this->db->where('products.category_id', $category_id);
            endif;

            //------------------Keyword---------------------//
            $subcategory_id = isset($param['subcategory_id']) && !empty($param['subcategory_id']) ? $param['subcategory_id'] : NULL;
            if (!empty($subcategory_id)):
                $this->db->where('products.subcategory_id', $subcategory_id);
            endif;

            //------------------Limit ---------------------//
            $seach_offset = isset($param['offset']) && $param['offset'] !== null ? (int) $param['offset'] : NULL;
            $seach_limit = isset($param['limit']) && $param['limit'] !== null ? (int) $param['limit'] : NULL;
            if ($seach_offset !== null && $seach_limit !== null):
                $this->db->limit($seach_limit, $seach_offset);
            endif;

        endif;


        $this->db->order_by('products.name');
        $this->db->join('units', 'products.sale_unit =  units.id', 'left');
        $this->db->join('product_variants', 'products.id =  product_variants.product_id', 'left');
        $this->db->select('product_variants.id as variant_id, product_variants.name AS variant_name, product_variants.cost AS variant_cost, product_variants.price AS variant_price, product_variants.quantity AS variant_quantity');
        $q = $this->db->get("products");
        //echo $this->db->last_query(); 
        if ($q->num_rows() > 0) {
            $products_modified = $q->result_array();
            $arr = array();
            foreach ($products_modified as $key => $value) {
                if (in_array($value['id'], $arr)) {
                    $key1 = array_search($value['id'], $arr);
                    if ($value['variant_id']) {
                        $products_modified[$key1]['variants'][] = array('variant_id' => $value['variant_id'], 'variant_name' => $value['variant_name'], 'variant_cost' => $value['variant_cost'], 'variant_price' => $value['variant_price'], 'variant_quantity' => $value['variant_quantity']);
                    }
                    unset($products_modified[$key]);
                } else {
                    $arr[$key] = $value['id'];
                    if ($value['variant_id']) {
                        $products_modified[$key]['variants'][] = array('variant_id' => $value['variant_id'], 'variant_name' => $value['variant_name'], 'variant_cost' => $value['variant_cost'], 'variant_price' => $value['variant_price'], 'variant_quantity' => $value['variant_quantity']);
                    }
                    unset($products_modified[$key]['variant_id']);
                    unset($products_modified[$key]['variant_name']);
                    unset($products_modified[$key]['variant_cost']);
                    unset($products_modified[$key]['variant_price']);
                    unset($products_modified[$key]['variant_quantity']);
                }
            }
            return $products_modified;
        }
        return FALSE;
    }

    public function products_count_eshop($seach_keyword, $category_id, $subcategory_id = NULL) {
        if (!empty($category_id)) {

            $this->db->where('category_id', $category_id);
        }
        if (!empty($subcategory_id)) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        if (!empty($seach_keyword)):
            $this->db->like('products.name', $seach_keyword);
        endif;
        $this->db->from('products');
        $cnt = $this->db->count_all_results();
        return $cnt;
    }

    public function setFavourites($id) {
        $data = array('is_featured' => 1);
        if ($id) {
            $this->db->update('products', $data, array('id' => $id));
            return true;
        } else {
            return false;
        }
    }

    public function unsetFavourites($id) {
        $data = array('is_featured' => 0);
        if ($id) {
            $this->db->update('products', $data, array('id' => $id));
            return true;
        } else {
            return false;
        }
    }

    public function getWherehousProducts($warehouse_id = NULL, $categories = 0, $listbycategory = 0) {
        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.type,{$this->db->dbprefix('products')}.category_id,{$this->db->dbprefix('products')}.subcategory_id, {$this->db->dbprefix('warehouses_products')}.warehouse_id,{$this->db->dbprefix('warehouses_products')}.quantity as quantity");
        $this->db->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left');
        if ($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }

        if ($categories) {
            $r = 1;
            $this->db->group_start();
            $categoryIds = explode(',', $categories);
            foreach ($categoryIds as $category) {
                if ($r == 1) {
                    $this->db->where('products.category_id', $category);
                } else {
                    $this->db->or_where('products.category_id', $category);
                }
                $r++;
            }
            $this->db->group_end();
        }

        $q = $this->db->get('products');

        if ($q->num_rows() > 0) {

            foreach (($q->result()) as $row) {

                $products[$row->id]['id'] = $row->id;
                $products[$row->id]['name'] = $row->name;
                $products[$row->id]['code'] = $row->code;
                $products[$row->id]['type'] = $row->type;
                $products[$row->id]['category_id'] = $row->category_id;
                $products[$row->id]['subcategory_id'] = $row->subcategory_id;
                $products[$row->id]['wherehouse'][$row->warehouse_id] = $row->quantity;
            }
            foreach ($products as $id => $wherehouses) {

                $products[$id]['total'] = 0;

                foreach ($wherehouses['wherehouse'] as $key => $value) {
                    $products[$id]['total'] += $value;
                }
            }

            if ($listbycategory) {

                foreach ($products as $id => $prod) {
                    $stocks[$prod['category_id']][$id]['id'] = $prod['id'];
                    $stocks[$prod['category_id']][$id]['name'] = $prod['name'];
                    $stocks[$prod['category_id']][$id]['code'] = $prod['code'];
                    $stocks[$prod['category_id']][$id]['type'] = $prod['type'];
                    $stocks[$prod['category_id']][$id]['category_id'] = $prod['category_id'];
                    $stocks[$prod['category_id']][$id]['subcategory_id'] = $prod['subcategory_id'];
                    $stocks[$prod['category_id']][$id]['wherehouse'] = $prod['wherehouse'];
                    $stocks[$prod['category_id']][$id]['total'] = $prod['total'];
                }
            } else {

                $stocks = $products;
            }

            return $stocks;
        } else {
            return $q->num_rows();
        }
    }

    public function getAllProductStock($param = null) {

        $this->db->select('products.*,units.id as unit_id ,units.code as unit_code ,units.name as unit_name');
        if (is_array($param)):
            //------------------Keyword---------------------//
            $seach_keyword = isset($param['keyword']) && !empty($param['keyword']) ? $param['keyword'] : NULL;
            if (!empty($seach_keyword)):
                $this->db->like('products.name', $seach_keyword);
            endif;

            //------------------Keyword---------------------//
            $category_id = isset($param['category_id']) && !empty($param['category_id']) ? $param['category_id'] : NULL;
            if (!empty($category_id)):
                $this->db->where('products.category_id', $category_id);
            endif;

            //------------------Keyword---------------------//
            $subcategory_id = isset($param['subcategory_id']) && !empty($param['subcategory_id']) ? $param['subcategory_id'] : NULL;
            if (!empty($subcategory_id)):
                $this->db->where('products.subcategory_id', $subcategory_id);
            endif;

            //------------------Limit ---------------------//
            $seach_offset = isset($param['offset']) && $param['offset'] !== null ? (int) $param['offset'] : NULL;
            $seach_limit = isset($param['limit']) && $param['limit'] !== null ? (int) $param['limit'] : NULL;
            if ($seach_offset !== null && $seach_limit !== null):
                $this->db->limit($seach_limit, $seach_offset);
            endif;

        endif;

        $this->db->order_by('products.name');
        $this->db->join('units', 'products.sale_unit =  units.id', 'left');
        $this->db->join('product_variants', 'products.id =  product_variants.product_id', 'left');
        $this->db->select('product_variants.id AS variant_id,product_variants.name AS variant_name, product_variants.cost AS variant_cost, product_variants.price AS variant_price, product_variants.quantity AS variant_quantity');
        $q = $this->db->get("products");
        //echo $this->db->last_query(); 
        if ($q->num_rows() > 0) {
            $products_modified = $q->result_array();
            $arr = array();
            // $wherehouseData = $this->getWherehousProducts(NULL, $category_id);

            foreach ($products_modified as $key => $value) {
                if (in_array($value['id'], $arr)) {
                    $key1 = array_search($value['id'], $arr);
                    $products_modified[$key1]['variants'][] = array('variant_id' => $value['variant_id'], 'variant_name' => $value['variant_name'], 'variant_cost' => $value['variant_cost'], 'variant_price' => $value['variant_price'], 'variant_quantity' => $value['variant_quantity']);
                    unset($products_modified[$key]);
                } else {
                    $arr[$key] = $value['id'];
                    $products_modified[$key]['variants'][] = array('variant_id' => $value['variant_id'], 'variant_name' => $value['variant_name'], 'variant_cost' => $value['variant_cost'], 'variant_price' => $value['variant_price'], 'variant_quantity' => $value['variant_quantity']);
                    unset($products_modified[$key]['variant_id']);
                    unset($products_modified[$key]['variant_name']);
                    unset($products_modified[$key]['variant_cost']);
                    unset($products_modified[$key]['variant_price']);
                    unset($products_modified[$key]['variant_quantity']);
                }

//                if(isset($wherehouseData[$value['id']])) {
//                    $products_modified[$key]['stocks'] = $wherehouseData[$value['id']];
//                }
            }
            return $products_modified;
        }
        return FALSE;
    }

    function getVariantDetails($VarientId, $ProductId, $WarehouseId) {
        //echo "select pv.* from sma_warehouses_products wp inner join sma_product_variants pv on pv.product_id=wp.product_id where pv.product_id='$ProductId' and pv.name='$VarientName' and wp.warehouse_id='$WarehouseId'";
        /*
          $this->db->where('product_variants.product_id', $ProductId);
          $this->db->where('product_variants.id', $VarientId);
          $this->db->where('warehouses_products.warehouse_id', $WarehouseId);
          $this->db->join('product_variants ', 'product_variants.product_id=warehouses_products.product_id','inner');
          $this->db->select('product_variants.*');
          $q = $this->db->get("warehouses_products "); */

        $this->db->where('product_id', $ProductId);
        $this->db->where('option_id', $VarientId);
        $this->db->where('warehouse_id', $WarehouseId);
        $this->db->select('*');
        $q = $this->db->get("warehouses_products_variants"); //25-09-2019 according to warehousesvariants
        return $q->result();
    }

    // Get Product List
    // function get_product_list() {
    //     $get_arg = func_get_args(); // get Werehouse id 0 index 
    //     /*
    //       $this->db->select('warehouses_products.id,products.id as product_id,warehouses_products.quantity,products.name,products.code')
    //       ->join('warehouses_products', 'products.id = warehouses_products.product_id and warehouses_products.warehouse_id = '.$get_arg[0],'left')
    //       ->order_by('products.name','ASC');
    //      */

    //     $this->db->select('warehouses_products.id,products.id as product_id,warehouses_products.quantity,products.name,products.code')
    //             ->join('warehouses_products', 'products.id = warehouses_products.product_id', 'left')
    //             ->where(['warehouses_products.warehouse_id' => $get_arg[0]])->order_by('products.name', 'ASC');


    //     $get_data = $this->db->get('products')->result();

    //     foreach ($get_data as $row_value) {

    //         //$q = $this->db->where(array('product_id' => $row_value->product_id))->get('product_variants');
    //         $q = $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.cost as cost, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
    //                         ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
    //                         ->where('product_variants.product_id', $row_value->product_id)
    //                         ->where('warehouses_products_variants.warehouse_id', $get_arg[0])->get('product_variants');
    //         $data = [];
    //         $quantity = 0;
    //         if ($q->num_rows() > 0) {
    //             foreach (($q->result()) as $row) {
    //                 $data[] = $row;
    //                 $quantity = $quantity + $row->quantity;
    //             }
    //             $variant = (object) $data;
    //             $product_quantity = $quantity;
    //         } else {
    //             $data = '';
    //             $variant = '';
    //             $product_quantity = '';
    //         }
    //         $passdata[] = array('item' => $row_value, 'variant' => $variant, 'quantity' => $product_quantity);
    //     }
    //     return $passdata;
    // }
    public function get_product_list($warehouse_id)
    {
        $start = microtime(true);
    
        // 1) Fetch ONLY products that exist in this warehouse
        //    Use INNER JOIN (not LEFT) so we don't fetch all products in DB.
        $products = $this->db->select('wp.id,
                                       p.id AS product_id,
                                       wp.quantity,
                                       p.name,
                                       p.code')
                             ->from('products p')
                             ->join('warehouses_products wp', 'wp.product_id = p.id', 'inner')
                             ->where('wp.warehouse_id', (int)$warehouse_id)
                             ->order_by('p.name', 'ASC')
                             ->get()
                             ->result();
    
        // If no products in this warehouse → return early (prevents IN ())
        if (empty($products)) {
            return [];
        }
    
        // 2) Build a clean, unique, non-empty product id list
        $productIds = array_unique(
            array_filter(
                array_map('intval', array_column($products, 'product_id')),
                function($v){ return $v > 0; }
            )
        );
    
        // Safety: if something weird yields empty IDs, skip variant query
        $variants = [];
        if (!empty($productIds)) {
            // 3) Fetch ALL variants for those products in ONE query
            $variants = $this->db->select('pv.product_id,
                                           pv.id AS variant_id,
                                           pv.name,
                                           pv.cost,
                                           wpv.quantity')
                                 ->from('product_variants pv')
                                 ->join('warehouses_products_variants wpv',
                                        'wpv.option_id = pv.id AND wpv.warehouse_id = '.(int)$warehouse_id,
                                        'left')
                                 ->where_in('pv.product_id', $productIds)
                                 ->get()
                                 ->result();
        }
    
        // 4) Group variants by product_id
        $variantMap = [];
        foreach ($variants as $v) {
            $variantMap[$v->product_id][] = $v;
        }
    
        // 5) Build final response (keep your original shape)
        $response = [];
        foreach ($products as $p) {
            $pv = isset($variantMap[$p->product_id]) ? $variantMap[$p->product_id] : [];
    
            $totalQty = 0;
            foreach ($pv as $v) {
                $totalQty += (float)$v->quantity;
            }
    
            $response[] = [
                'item'     => $p,
                'variant'  => $pv ?: '',
                // If there are variant rows, sum their qty; otherwise use item qty
                'quantity' => ($pv ? $totalQty : $p->quantity),
            ];
        }
    
        // Optional timing (for debugging only)
        // $elapsed = microtime(true) - $start;
        // log_message('debug', 'get_product_list('.$warehouse_id.') took '.round($elapsed, 4).'s');
    
        return $response;
    }

    // End Get Product List   
    // Get Foodtype
    public function getfoodstype() {
        return $this->db->where(array('is_active' => '1', 'is_delete' => '0'))->get('sma_food_type')->result();
    }

    // End Get Foodtype
    // Urbanpiper data get  28-05-19
    public function getupnproduct($productid) {
        return $this->db->select('*')->where('product_id', $productid)->get('sma_up_products')->row();
    }

    public function setupnproduct($product) {

        $data['product_id'] = $product->id;
        $data['product_code'] = $product->code;
        $data['price'] = $product->price;
        $data['food_type_id'] = $product->food_type_id;

        $objdata = new stdClass();

        if ($this->db->insert('sma_up_products', $data)) {
            $objdata->id = $this->db->insert_id();
        }

        $objdata->product_id = $data['product_id'];
        $objdata->product_code = $data['product_code'];
        $objdata->price = $data['price'];
        $objdata->food_type_id = $data['food_type_id'];
        return $objdata;
    }

    public function get_custom_product_field($Field, $Type) {
        $q = $this->db->get_where('product_custom_field', array($Field => $Type));
        if ($q->num_rows() > 0) {
            foreach (($q->result_array()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

// Get warehouseProduct List
    function get_warehousesproduct_list() {
        $get_arg = func_get_args(); // get Werehouse id 0 index 

        $this->db->select('warehouses_products.id,products.id as product_id,warehouses_products.quantity,products.name,products.code, product_variants.name as option,product_variants.id as varentid')
                ->join('product_variants', 'product_variants.product_id=products.id', 'left')
                ->join('warehouses_products', 'products.id = warehouses_products.product_id and warehouses_products.warehouse_id = ' . $get_arg[0], 'left')
                ->order_by('products.name', 'ASC');

        $get_data = $this->db->get('products')->result();
        $warehouse_id = $get_arg[0];
        foreach ($get_data as $row_value) {

            if ($row_value->option) {
                $qty1 = $this->db->select('quantity')->where(['product_id' => $row_value->product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $row_value->varentid])->get('sma_warehouses_products_variants')->row(); //Shock  Quantity Warehouse 1 query With Varent
            } else {
                $qty1 = $this->db->select('quantity')->where(['product_id' => $row_value->product_id, 'warehouse_id' => $warehouse_id])->get('sma_warehouses_products')->row(); //Shock Quantity Warehouse 1 query Without Varent
            }

            $data = [];
            //$product_quantity = $qty1->quantity;
            $row_value->quantity = $qty1->quantity;
            $q = $this->db->where(array('product_id' => $row_value->product_id))->get('product_variants');
            $quantity = 0;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                    $quantity = $quantity + $row->quantity;
                }
                $variant = (object) $data;
                $product_quantity = $quantity;
            } else {
                $data = '';
                $variant = '';
                $product_quantity = '';
            }
            $passdata[] = array('item' => $row_value, 'variant' => $variant, 'quantity' => $product_quantity);
        }
        return $passdata;
    }

    // End Get warehouseProduct List   

    /**
     * Get Biller Details
     * @return type
     */
    public function getBillerDetails() {
        $biller_id = $this->db->select('default_biller')->where(['pos_id' => '1'])->get('sma_pos_settings')->row();
        $billerDetails = $this->db->select('*')->where(['id' => $biller_id->default_biller])->get('sma_companies')->row();
        return $billerDetails;
    }

    /**
     * Bulk Image Upload
     * @param type $data
     */
    public function bulkimageUpload($data) {
        foreach ($data as $imagevalue) {
            $getproductid = $this->db->select('id')->where(['code' => $imagevalue['code']])->get('products')->row();
            $productid = $getproductid->id;
            if (!empty($imagevalue['Image'])) {
                $prductimage = ['image' => $imagevalue['Image']];
                $this->db->where(['id' => $productid])->update('products', $prductimage);
            }

            if (!empty($imagevalue['Gallery_1']) || !empty($imagevalue['Gallery_2']) || !empty($imagevalue['Gallery_3']) || !empty($imagevalue['Gallery_4']) || !empty($imagevalue['Gallery_5'])) {

                $uploadGalaryImage = array();

                if ($imagevalue['Gallery_1']) {
                    $uploadGalaryImage[] = [
                        'product_id' => $productid,
                        'photo' => $imagevalue['Gallery_1'],
                    ];
                }
                if ($imagevalue['Gallery_2']) {
                    $uploadGalaryImage[] = [
                        'product_id' => $productid,
                        'photo' => $imagevalue['Gallery_2'],
                    ];
                }
                if ($imagevalue['Gallery_3']) {
                    $uploadGalaryImage [] = [
                        'product_id' => $productid,
                        'photo' => $imagevalue['Gallery_3'],
                    ];
                }
                if ($imagevalue['Gallery_4']) {
                    $uploadGalaryImage[] = [
                        'product_id' => $productid,
                        'photo' => $imagevalue['Gallery_4'],
                    ];
                }
                if ($imagevalue['Gallery_5']) {
                    $uploadGalaryImage[] = [
                        'product_id' => $productid,
                        'photo' => $imagevalue['Gallery_5'],
                    ];
                }
            }
            if ($imagevalue['Variants_Name'] && $imagevalue['Variants_Images']) {
                $exp_variant = explode(",", $imagevalue['Variants_Name']);
                $exp_variantImage = explode(",", $imagevalue['Variants_Images']);

                foreach ($exp_variant as $key => $variantval) {
                    $variantname = rtrim(ltrim($variantval));
                    $pr_val = $this->db->select('id')->where(["product_id" => $productid, "name" => $variantname])->get('product_variants')->row();
                    $prvar_id = $pr_val->id;

                    $uploadGalaryVaraint[] = [
                        'product_id' => $productid,
                        'variant_id' => $prvar_id,
                        'photo' => rtrim(ltrim($exp_variantImage[$key])),
                    ];
                }

                $this->db->insert_batch('product_photos', $uploadGalaryVaraint);
            }

            if (!empty($uploadGalaryImage)) {
                $this->db->insert_batch('product_photos', $uploadGalaryImage);
            }
        }
    }

    /*     * Delete Var* */

    public function deleteVarient($id) {
        if ($this->db->delete('product_variants', array('id' => $id))) {
            $this->db->delete('warehouses_products_variants', array('option_id' => $id));

            return true;
        }
        return FALSE;
    }

    /**
     * Get Manage Barcode
     * @return type
     */
    public function getManagebarcode() {
        $data = $this->db->order_by('id', 'ASC')->get('manage_barcode')->result();
        return $data;
    }

    public function getProductStockDetails($product_id) {

        $orderby = ($this->Settings->accounting_method == 1) ? 'desc' : 'asc';

        $q = $this->db->select("`id`,`date`,`product_id`,`product_name`,`product_code`,`option_id`,`purchase_id`,`transfer_id`,`adjustment_id`,`quantity`,`quantity_received`,`expiry`,`quantity_balance`,`unit_quantity`,`warehouse_id`,`status`,`batch_number`,`updated_at`")
                ->where(array('product_id' => $product_id))
                ->group_start()->where('status', 'received')->or_where('status', 'partial')->or_where('status', 'returned')->group_end()
                ->order_by('date', $orderby)
                ->get('purchase_items');

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductSaleInfo($product_id) {
        $q = $this->db->select("`product_name`, `option_id`, sum(`quantity`) quantity, sum(`unit_quantity`) unit_quantity ")
                ->where(array('product_id' => $product_id))
                ->group_by('option_id')
                ->get('sale_items');

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    /* Manage Products Batches Functions */

    public function createBatch($data) {
        $this->db->insert('sma_product_batches', $data);
        if ($this->db->affected_rows()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateBatch($data, $id) {
        $this->db->where(['id' => $id])->update('sma_product_batches', $data);
        if ($this->db->affected_rows()) {
            return true;
        } else {
            return false;
        }
    }

    public function batchDetails($id) {
        return $this->db->where(['id' => $id])->get('sma_product_batches')->row();
    }

    public function deleteBatch($id) {
        $this->db->where(['id' => $id])->delete('sma_product_batches');
        if ($this->db->affected_rows()) {
            return true;
        } else {
            return false;
        }
    }

    public function getProducts() {
        return $this->db->select('id,name,code')->order_by('name', 'asc')->get('sma_products')->result();
    }

    public function getSpareParts() {
        $this->db->select('sma_products.id, sma_products.name, sma_products.code')
                 ->from('sma_products')
                 ->join('sma_categories', 'sma_categories.id = sma_products.category_id', 'left')
                 ->like('sma_categories.name', 'spare')
                 ->or_like('sma_categories.code', 'spare')
                 ->order_by('sma_products.name', 'asc');
        return $this->db->get()->result();
    }

    public function getProductBatch($product_id, $option_id = 0) {

        $where = ['b.product_id' => $product_id, 'b.is_active' => 1, 'b.is_delete' => 0];

        if ($option_id) {
            $where['b.option_id'] = $option_id;
        }

        $batchNo = $this->db->select('b.*, v.name as variant_name')
                ->where($where)
                ->order_by('b.batch_no', 'desc')
                ->from('product_batches AS b')
                ->join('product_variants AS v', 'v.id=b.option_id', 'left')
                ->order_by('b.option_id')
                ->get()
                ->result();

        if ($this->db->affected_rows()) {
            $response = array();
            foreach ($batchNo as $batchva) {
                $response[$batchva->id] = $batchva;

                if ($batchva->expiry_date != '' && $batchva->expiry_date !== '0000-00-00') {
                    $expiry_strtotime = strtotime($batchva->expiry_date);
                    $response[$batchva->id]->expiry = date("d-m-Y", $expiry_strtotime);
                } else {
                    $response[$batchva->id]->expiry = '';
                }
            }
            return $response;
        } else {
            return false;
        }
    }

    public function getProductVariantsBatch($product_id, $warehouse_id = null) {
    $this->db->select('product_batches.*, purchase_items.warehouse_id')
             ->from('product_batches')
             ->join('purchase_items', 'purchase_items.batch_number = product_batches.batch_no AND purchase_items.product_id = ' . $product_id, 'left')
             ->where(['product_batches.product_id' => $product_id, 
                     'product_batches.is_active' => 1, 
                     'product_batches.is_delete' => 0]);
    
    if ($warehouse_id) {
        $this->db->where('purchase_items.warehouse_id', $warehouse_id);
    }
    
    $batchNo = $this->db->group_by('product_batches.id')
                       ->order_by('product_batches.batch_no', 'desc')
                       ->get()
                       ->result();

    if ($this->db->affected_rows() && count($batchNo) > 0) {
        $response = array();
        foreach ($batchNo as $batchva) {
            if ($batchva->expiry_date != '' && $batchva->expiry_date !== '0000-00-00') {
                $expiry_strtotime = strtotime($batchva->expiry_date);
                $batchva->expiry = date("d-m-Y", $expiry_strtotime);
            } else {
                $batchva->expiry = '';
            }
            $response[$batchva->option_id][$batchva->id] = $batchva;
        }
        return $response;
    }
    return false;
}

    public function getProductBatchById($batch_id) {

        $batchNo = $this->db->where(['id' => $batch_id])
                ->get('product_batches')
                ->result();

        if ($this->db->affected_rows()) {
            return $batchNo;
        } else {
            return false;
        }
    }

    // Get batch no list and qty
    public function getProductBatchWithQty($product_id) {
        $batchNo = $this->db->select('sma_product_batches.id, sma_product_batches.batch_no, sma_product_batches.cost, sma_product_batches.price, sma_product_batches.mrp, IF(sum(`sma_purchase_items`.`quantity_balance`),sum(`sma_purchase_items`.`quantity_balance`) ,0) as qty ')
                        ->join('sma_purchase_items', 'sma_purchase_items.batch_number = sma_product_batches.batch_no', 'left')
                        ->where(['sma_product_batches.product_id' => $product_id])
                        ->group_by('sma_product_batches.batch_no')
                        ->order_by('sma_product_batches.id', 'ASC')->get('sma_product_batches')->result();
        if ($this->db->affected_rows()) {
            $response = array();
            foreach ($batchNo as $batchva) {
                if (!$this->Settings->overselling) {
                    if ($batchva->qty > 0) {
                        $response[$batchva->id] = $batchva;
                    }
                } else {
                    $response[$batchva->id] = $batchva;
                }
            }
            return $response;
        } else {
            return false;
        }
    }

    public function get_batch_in_used($id) {

        return TRUE;
    }

    /* End Manage Products Batches Functions */

    public function getProductOptionByID($id) {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /*     * ******************************************************************
     * Purchases Notification
     * ****************************************************************** */

    /**
     * New Master pos data store
     * @param type $unitData
     * @return type
     */
    public function getUnitCheck($unitData) {
        $getData = $this->db->select('id')->where(['code' => $unitData['code']])->get('units')->row();
        if ($this->db->affected_rows()) {
            return $getData->id;
        } else {

            $unitData['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert('units', $unitData);
            return $this->db->insert_id();
        }
    }

    /**
     * New Master pos data store category Check
     * @param type $unitData
     * @return type
     */
    public function getCategoryCheck($category) {

        $getData = $this->db->select('id')->where(['code' => $category['code']])->get('categories')->row();
        if ($this->db->affected_rows()) {
            return $getData->id;
        } else {
            $category['updated_at'] = date('Y-m-d H:i:s');

            $this->db->insert('categories', $category);
            return $this->db->insert_id();
        }
    }

    /**
     * New Master pos data store Brand Check
     * @param type $unitData
     * @return type
     */
    public function getBrandCheck($brands) {
        $getData = $this->db->select('id')->where(['code' => $brands['code']])->get('brands')->row();
        if ($this->db->affected_rows()) {
            return $getData->id;
        } else {
            $brands['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert('brands', $brands);
            return $this->db->insert_id();
        }
    }

    /**
     * Store New Product for master pos
     * @param type $data
     * @return type
     */
    public function store_newproduct($data) {
        $getData = $this->db->select('id')->where(['code' => $data['code']])->get('products')->row();
        if ($this->db->affected_rows()) {
            return $getData->id;
        } else {
            $this->db->insert('products', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Store New Product Variants for master pos
     * @param type $options
     * @return type
     */
    public function store_newOption($options) {
        $getData = $this->db->select('id')->where(['product_id' => $options['product_id'], 'name' => $options['name']])->get('product_variants')->row();
        if ($this->db->affected_rows()) {
            return TRUE;
        } else {
            $this->db->insert('product_variants', $options);
            return ($this->db->affected_rows()) ? TRUE : FALSE;
        }
    }

    /**
     * Check product codes
     * @param type $barcode
     * @return type
     */
    public function getProductCode($barcode) {
        $getProductDetails = $this->db->select('*')->where(['code' => $barcode])->get('products')->row();
        return $getProductDetails;
    }

    /*     * ******************************************************************
     * End Purchases Notification
     * ****************************************************************** */

    /**
     * Bulk Product Mark on Favourite
     * @param type $productIds
     * @return type
     */
    public function productsMarkFavourite($productIds) {
        $this->db->where_in('id', $productIds)->update('products', ['is_featured' => 1]);
        return ($this->db->affected_rows()) ? TRUE : FALSE;
    }

    /**
     * 
     * @return type
     */
    public function poscategory() {
        $category = $this->db->select('id')->where(['code' => 'POSCOMBO'])->get('categories')->row();
        if ($this->db->affected_rows()) {
            return $category->id;
        } else {
            $feild = [
                'code' => 'POSCOMBO',
                'name' => 'POS Combo',
            ];
            $this->db->insert('categories', $feild);
            return $this->db->insert_id();
        }
    }

    public function getFilterProducts($filter = null, $limit = null, $page = 1) {

        if ($filter == null) {
            return false;
        }


        $selectFields = 'p.id, p.code, p.name, p.image, p.price, p.mrp, p.primary_variant, p.in_eshop, p.eshop_price, p.eshop_name, p.is_active, ';
        $selectFields .= 'pv.id as variant_id, pv.name as variant_name, pv.price as variant_price, pv.unit_quantity as variant_unit_quantity, pv.eshop_name as variant_eshop_name, '
                . 'pv.eshop_mrp as variant_eshop_mrp,  pv.eshop_price as variant_eshop_price ';

        $this->db->select($selectFields);
        $this->db->from('products AS p');
        $this->db->join('product_variants AS pv', 'p.id = pv.product_id', 'left');

        if ((bool) $filter['category_id']) {
            $this->db->where(['p.category_id' => $filter['category_id']]);
        }

        if ((bool) $filter['subcategory_id']) {
            $this->db->where(['p.subcategory_id' => $filter['subcategory_id']]);
        }

        $result = $this->db->get()->result();

        if (count($result)) {
            foreach ($result as $key => $product) {

                $data[$product->id]['id'] = $product->id;
                $data[$product->id]['name'] = $product->name;
                $data[$product->id]['image'] = $product->image;
                $data[$product->id]['mrp'] = $product->mrp;
                $data[$product->id]['eshop_price'] = $product->eshop_price;
                $data[$product->id]['eshop_name'] = $product->eshop_name;
                $data[$product->id]['in_eshop'] = $product->in_eshop;

                if ($product->variant_id) {
                    $data[$product->id]['primary_variant'] = $product->primary_variant;
                    $data[$product->id]['varants'][] = [
                        'variant_id' => $product->variant_id,
                        'variant_name' => $product->variant_name,
                        'variant_price' => $product->variant_price,
                        'variant_unit_quantity' => $product->variant_unit_quantity,
                        'variant_eshop_name' => $product->variant_eshop_name,
                        'variant_eshop_mrp' => $product->variant_eshop_mrp,
                        'variant_eshop_price' => $product->variant_eshop_price,
                    ];
                } else {
                    $data[$product->id]['varants'] = null;
                }
            }

            return $data;
        }

        return false;
    }
    public function getCategoryById($id)
    {
        $q = $this->db->get_where('categories', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCategoryByProducts($category_id)
    {
        $q = $this->db->get_where('products', array('category_id' => $category_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


    public function productAddQuick($productsdata,$product_attributes =[]){
        $this->db->insert('products',$productsdata);
        $product_id =  $this->db->insert_id();
        
        $colors_post = (array) $this->input->post('AttrColor');
        $p_color = !empty($colors_post) ? $colors_post[0] : '';
        if (!empty($p_color)) {
            $color = [
                'product_id' => $product_id,
                'name' => $p_color,
                'group_id' => '2',
            ];
            $this->db->insert('product_variants', $color);
            $color_id =  $this->db->insert_id();
        }
            
        //     if(!empty($this->input->post('attributesInput'))){
        //         $size = array();

        //         foreach ($this->input->post('attributesInput') as $productsize){
        //             $size []= [
        //                 'product_id' => $product_id,
        //                 'name' => $productsize,
        //                 'group_id' => '1',
        //             ];
                    
        //         }
                
        //         $this->db->insert_batch('product_variants',$size);
        //     }
            
        //     $result = $this->db->select('code')->where(['id'=>$product_id])->get('products')->row();
        //     if(!empty($this->input->post('sizename'))){
        //         $variant_id = [];
        //         $qty= $this->input->post('sizeqty');
        //         foreach ($this->input->post('sizename') as $key => $val){
        //            $sizename =  $this->db->select('id')->where(['name'=>$val, 'product_id' =>$product_id  ])->get('sma_product_variants')->row();
        //            $variant_id [] = array('product_code' => $result->code.'_'.$sizename->id.(isset($color_id)?'_'.$color_id:''), 'qty' =>$qty[$key]);
        //          }
        //         return $variant_id;
        //     }else{
        //         return $result->code;
        //     }

        $withvariantdata = [];
        if (!empty($product_attributes)) {
            foreach ($product_attributes as $product_attribute) {  
            $withvar_data = [
                'product_id' => $product_id,
                'quantity' => $product_attribute['quantity'],
                'cost' => $product_attribute['cost'],
                'price' => $product_attribute['price'],
                'mrp' => $product_attribute['mrp'],
                'variant_discount_on_mrp' => $product_attribute['variant_discount_on_mrp'],
                'name' => $product_attribute['name'],
                'unit_quantity' => $product_attribute['unit_quantity'],
                'unit_weight' => $product_attribute['unit_weight'],
                'group_id' => '1',
            ];
            $this->db->insert('product_variants', $withvar_data);

            $withvariantdata[] = $this->db->insert_id(); 

        }
    }

        
    
    $result = $this->db->select('code')->where(['id' => $product_id])->get('products')->row();

    
    
    if (!empty($this->input->post('attr_name'))) {
        $variant_id = [];
    
        foreach ($this->input->post('attr_name') as $key => $val) {
            $attribute = $this->db->select('id')
                ->where(['name' => $val, 'product_id' => $product_id])
                ->get('product_variants')
                ->row();
    
            if ($attribute) {
                $variant_id[] = [
                    'product_code' => $result->code . '_' . $attribute->id
                ];
            }
        }
    
        return $variant_id;
    } else {
        return $result->code;
    }
    
}

public function getAllVariants1($GroupId='')
    {
		if($GroupId!=''){
			$this->db->where('group_id', $GroupId);
		}
			
        $q = $this->db->get('variants');
		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	
	
public function getManageVariant(){
        $variant = $this->db->select('variant_id as id, variant_name as name')->get('sma_manage_variant')->result();
        return $variant;
    }

    public function getProductNamesforcombo($term, $limit = 20) {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, '  . $this->db->dbprefix('products') . '.mrp as mrp, ' . $this->db->dbprefix('products') . '.cost as cost, '  . $this->db->dbprefix('product_variants') . '.name as vname')
                ->where("type != 'combo' AND "
                        . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");

        $this->db->join('product_variants', 'product_variants.product_id=products.id', 'left')
                ->where('' . $this->db->dbprefix('product_variants') . '.name', NULL)
                ->group_by('products.id')->limit($limit);

        $q = $this->db->get('products');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
 ///////////////////////////////////////////////////// Print Barcode //////////////////////////////////////
 public function getProductOptionsByName($pid,$option_name) {
    $this->db->select('product_variants.*, manage_variant.variant_name');
    $this->db->from('product_variants');
    $this->db->join('manage_variant', 'product_variants.name = manage_variant.variant_name', 'left'); 
    $this->db->where('product_variants.product_id', $pid);
    $this->db->where('product_variants.name', $option_name);
    // $this->db->order_by('manage_variant.id', 'ASC');
    $q = $this->db->get(); 
    // $q = $this->db->get_where('product_variants', ['product_id' => $pid]);
    if ($q->num_rows() > 0) {
        foreach ($q->result() as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }
    return FALSE;
}
public function getProductOptionswithbatchAndWarehous($pid, $warehouseId, $GroupId = '') {
    $this->db->select('product_variants.*, product_batches.batch_no, warehouses_products_variants.quantity AS warehouse_quantity, sma_manage_variant.id AS manage_variant_pk');
    $this->db->from('product_variants');
    $this->db->join('product_batches', 'product_variants.id = product_batches.option_id', 'left');
    $this->db->join('warehouses_products_variants', 'product_variants.id = warehouses_products_variants.option_id AND warehouses_products_variants.warehouse_id = ' . (int)$warehouseId, 'left');
    // Align variant ordering with Manage Variant configuration
    $this->db->join('sma_manage_variant', 'sma_manage_variant.variant_name = product_variants.name', 'left');
    $this->db->where('product_variants.product_id', $pid);
    $this->db->where('product_variants.group_id', $GroupId);
    // Order by manage variant sequence first; unmatched variants go last, then by numeric name, name, and id
    $this->db->order_by(
        'COALESCE(sma_manage_variant.id, 1000000000) ASC, '
        . 'CAST(' . $this->db->dbprefix('product_variants') . '.name AS UNSIGNED) ASC, '
        . $this->db->dbprefix('product_variants') . '.name ASC, '
        . $this->db->dbprefix('product_variants') . '.id ASC, '
        . $this->db->dbprefix('product_batches') . '.id ASC',
        '',
        FALSE
    );
    $q = $this->db->get(); 
    if ($q->num_rows() > 0) {
        $data = [];
        foreach ($q->result() as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }
    return FALSE;
}
public function getProductOptionswithbatchAndWarehous_bulkAction($pid, $warehouseId, $GroupId = '') {
    $this->db->select('product_variants.*, product_batches.batch_no, warehouses_products_variants.quantity AS w_qaunt');
    $this->db->from('product_variants');
    $this->db->join('product_batches', 'product_variants.id = product_batches.option_id', 'left');

    // Handle warehouse ID condition
    if (is_array($warehouseId)) {
        $this->db->join('warehouses_products_variants', 'product_variants.id = warehouses_products_variants.option_id', 'left');
        $this->db->where_in('warehouses_products_variants.warehouse_id', $warehouseId);
    } else {
        $this->db->join('warehouses_products_variants', 'product_variants.id = warehouses_products_variants.option_id AND warehouses_products_variants.warehouse_id = ' . (int)$warehouseId, 'left');
    }
    $this->db->where('product_variants.product_id', $pid);
    $this->db->where('product_variants.group_id', $GroupId);
    $this->db->order_by('product_batches.id', 'ASC');
    $q = $this->db->get();
    if ($q->num_rows() > 0) {
        $data = [];
        foreach ($q->result() as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }
    return FALSE;
}
public function getVariantforPurchase(){
    $variant = $this->db->select('id as id, name as name')->get('sma_variants')->result();
    return $variant;
}
public function getAllVariantNames() {
    $q = $this->db->select('name')->get('variants');
    if ($q->num_rows() > 0) {
        $variant_names = [];
        foreach ($q->result() as $row) {
            $variant_names[] = trim($row->name); // ← no strtolower()

        }
        return $variant_names;
    }
    return [];
}
public function getProductOptionsAdjustment($pid, $warehouse_id = null, $GroupId = '') {
    $user = $this->site->getUser();
    
    if (!$warehouse_id) {
        $warehouse_id = $user->warehouse_id ? $user->warehouse_id : $this->settings_model->getSettings()->default_warehouse;
    }

    $this->db->select('product_variants.*, sma_warehouses_products_variants.warehouse_id, sma_warehouses_products_variants.quantity AS warehouse_quant');
    $this->db->from('product_variants');
    $this->db->join(
        'sma_warehouses_products_variants',
        'sma_warehouses_products_variants.option_id = product_variants.id 
         AND sma_warehouses_products_variants.product_id = product_variants.product_id 
         AND sma_warehouses_products_variants.warehouse_id = ' . (int)$warehouse_id,
        'left'
    );
    $this->db->where('product_variants.product_id', $pid);
    $this->db->where('product_variants.group_id', $GroupId);

    $q = $this->db->get();

    if ($q->num_rows() > 0) {
        $data = [];
        foreach ($q->result() as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    return FALSE;
}
public function getProductOptionsByGroupId($pid, $GroupId = '') {
        $user = $this->site->getUser();
        if(!$user->warehouse_id)
        {
            $settings = $this->settings_model->getSettings();
            $warehouse_ids = $settings->default_warehouse;
        }
        else{
            $warehouse_ids = $user->warehouse_id;
        }
        // $warehouse_id = $user->warehouse_id;
       
        $this->db->select('product_variants.*, sma_warehouses_products_variants.quantity AS warehouse_quant, sma_manage_variant.id AS manage_variant_pk');
        $this->db->from('product_variants');
        $this->db->join(
            'sma_warehouses_products_variants',
            'sma_warehouses_products_variants.option_id = product_variants.id AND sma_warehouses_products_variants.product_id = product_variants.product_id AND sma_warehouses_products_variants.warehouse_id =' . (int)$warehouse_ids,
            'left'
        );
        // Join manage variant table to get the configured ordering for variants (e.g., sizes/colors)
        $this->db->join('sma_manage_variant', 'sma_manage_variant.variant_name = product_variants.name', 'left');
        $this->db->where('product_variants.product_id', $pid);
        $this->db->where('product_variants.group_id', $GroupId);
        // Order by manage variant configured order (insertion order via PK); items without a match go last
        $this->db->order_by('COALESCE(sma_manage_variant.id, 1000000000) ASC, ' . $this->db->dbprefix('product_variants') . '.id ASC', '', FALSE);
        $q = $this->db->get(); 
        // $q = $this->db->get_where('product_variants', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[$row->id] = $row;
            }
           
            return $data;
        }
        return FALSE;
    }
    public function getProductByID_Production_unit_printBarcode($id, $wh) {
        if (empty($wh)) {
            $wh = $this->Settings->default_warehouse;
        }
        $this->db->select("products.*, FORMAT(COALESCE(quantity_sum, 0), 2) as wp_quantity, is_featured", FALSE);
        $this->db->from('products');
        if ($this->Settings->display_all_products) {
            $this->datatables->join("( SELECT product_id, rack, warehouse_id, SUM(quantity) as quantity_sum FROM {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id IN( {$wh}) AND quantity != 0 GROUP BY product_id, rack ) wp", 'products.id=wp.product_id', 'left');
            $this->datatables->where('wp.warehouse_id IS NOT NULL');
        } else {
            $this->datatables->join("( SELECT product_id, SUM(quantity) as quantity_sum FROM {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id IN({$wh}) AND quantity != 0 GROUP BY product_id ) wp", 'products.id=wp.product_id', 'left');
        }
        $this->db->where('products.id', $id);
        $this->db->group_by('products.id');
    
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getProductQuantity_by_warehouse($product_id, $warehouse) {
        if (strpos($warehouse, ',') !== false) {
            $warehouse_ids = array_map('trim', explode(',', $warehouse));
        } else {
            $warehouse_ids = [$warehouse];
        }
        // Prepare placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($warehouse_ids), '?'));
        $sql = "
            SELECT SUM(quantity) AS total_quantity 
            FROM {$this->db->dbprefix('warehouses_products')} 
            WHERE product_id = ? 
            AND warehouse_id IN ($placeholders)
        ";
        $binds = array_merge([$product_id], $warehouse_ids);
        $query = $this->db->query($sql, $binds);
        if ($query->num_rows() > 0) {
            return $query->row_array();  
        }
        return ['total_quantity' => 0];
    }
    public function variantUsedInTransactions($id){
        // Check sale items
        $sale = $this->db->where('option_id', $id)->limit(1)->get('sma_sale_items')->num_rows();

        // Check purchase items
        $purchase = $this->db->where('option_id', $id)->limit(1)->get('sma_purchase_items')->num_rows();

        // Check adjustment items
        $adjustment = $this->db->where('option_id', $id)->limit(1)->get('sma_adjustment_items')->num_rows();

        if ($sale > 0 || $purchase > 0 || $adjustment > 0) {
            return true; 
        }
        return false; 
    }

    public function productsUsedInTransactions($id){
        // Check sale items
        $sale = $this->db->where('product_id', $id)->limit(1)->get('sma_sale_items')->num_rows();

        // Check purchase items
        $purchase = $this->db->where('product_id', $id)->limit(1)->get('sma_purchase_items')->num_rows();

        // Check adjustment items
        $adjustment = $this->db->where('product_id', $id)->limit(1)->get('sma_adjustment_items')->num_rows();

        if ($sale > 0 || $purchase > 0 || $adjustment > 0) {
            return true; 
        }
        return false; 
    }
    ////////////////////////////////////////////////// Job Works////////////////////////////////////
    public function getJobWorkProductsByMainId($main_product_id) {
        return $this->db->where('mainproduct_id', $main_product_id)
                       ->get('products')
                       ->result();
    }
    
    public function deleteJobWorkProductsByMainId($main_product_id) {
        $main_product_id = (int) $main_product_id;
        $this->db->where('product_id', $main_product_id)->delete('sma_product_dcc_stages');
        return $this->db->where('mainproduct_id', $main_product_id)->delete('products');
    }
    
    public function generateUniqueProductCode($base_code) {
        $original_code = $base_code;
        $counter = 1;
        
        while ($this->isProductCodeExists($base_code)) {
            $base_code = $original_code . $counter;
            $counter++;
        }
        
        return $base_code;
    }
    
    private function isProductCodeExists($code) {
        return $this->db->where('code', $code)
                       ->from('products')
                       ->count_all_results() > 0;
    }
    public function getAllProductInvertTypes(){
        $q = $this->db->get('sma_product_invert_type');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getJobWorkInwardTypeId() {
        static $job_work_inward_id = null;
        if ($job_work_inward_id !== null) {
            return $job_work_inward_id;
        }
        $job_work_inward_id = 2;
        $types = $this->getAllProductInvertTypes();
        if (!empty($types)) {
            foreach ($types as $row) {
                $label = strtolower(trim($row->type));
                if ($label === 'job works' || $label === 'job work') {
                    $job_work_inward_id = (int) $row->id;
                    break;
                }
            }
        }
        return $job_work_inward_id;
    }
    public function getAllJobWorkitems(){
        $q = $this->db->get('sma_standard_job_works');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
    public function getJobWorkByID($id){
        return $this->db ->where('id', $id) ->get('sma_standard_job_works') ->row();
    }

    /**
     * DCC stages are stored against the main sellable product id (not raw job-work child rows).
     */
    public function getDccStagesByMainProductId($main_product_id) {
        $main_product_id = (int) $main_product_id;
        if ($main_product_id <= 0) {
            return array();
        }
        $this->db
            ->select('d.id, d.step, d.product_id, d.job_work, d.input, d.output, jw.items as job_work_name, jw.suffix as job_work_suffix, p.name as main_product_name')
            ->from('sma_product_dcc_stages d')
            ->join('products p', 'p.id = d.product_id', 'inner')
            ->join('sma_standard_job_works jw', 'jw.id = d.job_work', 'left')
            ->where('d.product_id', $main_product_id)
            ->order_by('d.id', 'asc');
        $q = $this->db->get();
        return $q->num_rows() > 0 ? $q->result() : array();
    }

    private function jobwork_raw_product_exists($main_product_id, $expected_name) {
        $this->db->where('mainproduct_id', (int) $main_product_id);
        $this->db->where('name', $expected_name);
        return $this->db->count_all_results('products') > 0;
    }

    private function insert_jobwork_raw_product_if_missing($main_product_id, $data, $jw_id, $suffix, $pr_attr = null) {
        $main_product_id = (int) $main_product_id;
        $eshop_base = !empty($data['eshop_name']) ? $data['eshop_name'] : $data['name'];
        $variant_name = ($pr_attr && !empty($pr_attr['name'])) ? trim($pr_attr['name']) : '';
        $variant_suffix = $variant_name !== '' ? '_' . $variant_name : '';
        $expected_name = $data['name'] . '_' . $suffix . $variant_suffix;
        if ($this->jobwork_raw_product_exists($main_product_id, $expected_name)) {
            return false;
        }
        $variant_code = '';
        if ($variant_name !== '') {
            $variant_row = $this->getPrductVariantByPIDandName($main_product_id, $variant_name);
            $variant_code = $variant_row ? $variant_row->id : (isset($pr_attr['code']) ? $pr_attr['code'] : '');
        }
        $base_code = $variant_name !== ''
            ? $data['code'] . '0' . $jw_id . '0' . $variant_code
            : $data['code'] . '0' . $jw_id;
        $new_product = $this->prepareJobWorkProductInsert($data, array(
            'name' => $expected_name,
            'eshop_name' => $eshop_base . '_' . $suffix . $variant_suffix,
            'type' => 'raw',
            'mainproduct_id' => $main_product_id,
            'code' => $this->generateUniqueProductCode($base_code),
        ));
        $this->db->insert('products', $new_product);
        return true;
    }

    /**
     * Insert raw job-work child products for given variants (no DCC rows).
     */
    public function createJobWorkRawProductsForVariants($main_product_id, $data, $job_work_ids, $product_attributes = array()) {
        $main_product_id = (int) $main_product_id;
        $job_work_ids = array_values(array_unique(array_filter(array_map('intval', (array) $job_work_ids))));
        if (empty($job_work_ids) || empty($product_attributes)) {
            return false;
        }

        foreach ($job_work_ids as $jw_id) {
            $jobwork = $this->getJobWorkByID($jw_id);
            if (!$jobwork) {
                continue;
            }
            $suffix = isset($jobwork->suffix) ? trim((string) $jobwork->suffix) : '';
            if ($suffix === '') {
                continue;
            }
            foreach ($product_attributes as $pr_attr) {
                if (isset($pr_attr['group_id']) && (string) $pr_attr['group_id'] !== '1') {
                    continue;
                }
                $this->insert_jobwork_raw_product_if_missing($main_product_id, $data, $jw_id, $suffix, $pr_attr);
            }
        }

        return true;
    }

    public function getJobWorkIdsForMainProduct($main_product_id) {
        $main_product_id = (int) $main_product_id;
        $job_work_ids = array();
        $stages = $this->getDccStagesByMainProductId($main_product_id);
        foreach ($stages as $stage) {
            if (!empty($stage->job_work)) {
                $job_work_ids[] = (int) $stage->job_work;
            }
        }
        if (!empty($job_work_ids)) {
            return array_values(array_unique($job_work_ids));
        }

        $parent = $this->getProductByID($main_product_id);
        $children = $this->getJobWorkProductsByMainId($main_product_id);
        if (empty($parent) || empty($children)) {
            return array();
        }

        $parent_code = (string) $parent->code;
        $prefix = $parent_code . '0';
        foreach ($children as $child) {
            if (strpos((string) $child->code, $prefix) !== 0) {
                continue;
            }
            $remainder = substr((string) $child->code, strlen($prefix));
            if ($remainder === '' || $remainder[0] === '') {
                continue;
            }
            $jw_part = strtok($remainder, '0');
            if ($jw_part !== false && $jw_part !== '' && ctype_digit($jw_part)) {
                $job_work_ids[] = (int) $jw_part;
            }
        }

        return array_values(array_unique($job_work_ids));
    }

    public function create_jobwork_products($main_product_id, $data, $job_work_ids, $product_attributes = []){
        $main_product_id = (int) $main_product_id;
        $size_variants = $this->getJobWorkSizeVariants($main_product_id, $product_attributes);
        $step = 1;
        foreach ($job_work_ids as $jw_id) {
            $jobwork = $this->getJobWorkByID($jw_id);
            if (!$jobwork) {
                continue;
            }
            // No suffix: still record DCC stage, but do not create raw child products (no "_items" product rows).
            $suffix = isset($jobwork->suffix) ? trim((string) $jobwork->suffix) : '';
            if ($suffix === '') {
                $dcc_label = isset($jobwork->items) ? trim((string) $jobwork->items) : '';
                if ($dcc_label === '') {
                    $dcc_label = 'items';
                }
                $step_code = 'DCC ' . str_pad($step, 2, '0', STR_PAD_LEFT);
                $output_name = $this->buildDccStageOutputForMainProductInsert($data['name'], $dcc_label);
                $this->db->insert('sma_product_dcc_stages', array(
                    'step' => $step_code,
                    'product_id' => $main_product_id,
                    'job_work' => $jw_id,
                    'output' => $output_name
                ));
                $step++;
                continue;
            }

            if (!empty($size_variants)) {
                foreach ($size_variants as $pr_attr) {
                    $this->insert_jobwork_raw_product_if_missing($main_product_id, $data, $jw_id, $suffix, $pr_attr);
                }
                $step_code = 'DCC ' . str_pad($step, 2, '0', STR_PAD_LEFT);
                $output_name = $this->buildDccStageOutputForMainProductInsert($data['name'], $suffix);
                $this->db->insert('sma_product_dcc_stages', array(
                    'step' => $step_code,
                    'product_id' => $main_product_id,
                    'job_work' => $jw_id,
                    'output' => $output_name
                ));
                $step++;
            } else {
                $this->insert_jobwork_raw_product_if_missing($main_product_id, $data, $jw_id, $suffix);
                $step_code = 'DCC ' . str_pad($step, 2, '0', STR_PAD_LEFT);
                $output_name = $this->buildDccStageOutputForMainProductInsert($data['name'], $suffix);
                $this->db->insert('sma_product_dcc_stages', array(
                    'step' => $step_code,
                    'product_id' => $main_product_id,
                    'job_work' => $jw_id,
                    'output' => $output_name
                ));
                $step++;
            }
        }
        return true;
    }

    /**
     * Edit flow: after variants change, create only missing raw job-work rows from DB variants.
     * Does not duplicate existing children or DCC stages.
     */
    public function sync_missing_jobwork_raw_products($main_product_id, $data, $job_work_ids) {
        $main_product_id = (int) $main_product_id;
        if ($main_product_id <= 0) {
            return true;
        }
        if (empty($job_work_ids)) {
            $job_work_ids = $this->getJobWorkIdsForMainProduct($main_product_id);
        }
        if (empty($job_work_ids)) {
            return true;
        }
        $size_variants = $this->getJobWorkSizeVariants($main_product_id, array());
        if (empty($size_variants)) {
            return true;
        }
        return $this->createJobWorkRawProductsForVariants($main_product_id, $data, $job_work_ids, $size_variants);
    }

    /**
     * Builds DCC stage output for inserts into sma_product_dcc_stages using only the main product
     * name from the create payload (not product id lookups), so child/raw job-work rows cannot pull
     * the wrong name if id referred to a variant or child product.
     */
    private function buildDccStageOutputForMainProductInsert($main_product_name, $jobwork_suffix) {
        $name = trim((string) $main_product_name);
        $suffix = !empty($jobwork_suffix) ? $jobwork_suffix : 'items';
        return $name . '-' . $suffix;
    }

    /**
     * Same output shape as product add (create_jobwork_products + buildDccStageOutputForMainProductInsert):
     * configured suffix wins; else job work items name; else '' so insert helper falls back to "items".
     */
    private function buildDccOutputValue($main_product_name, $jobwork_suffix, $jobwork_name = '', $main_product_id = '') {
        $name = trim((string) $this->getmainproductname($main_product_id));
        $label = trim((string) $jobwork_suffix);
        if ($label === '') {
            $label = trim((string) $jobwork_name);
        }

        return $this->buildDccStageOutputForMainProductInsert($name, $label);
    }
    public function getmainproductname($main_product_id){
        return $this->db->where('id', $main_product_id)->select('name')->get('products')->row()->name;
    }
   


    public function updateDccStageInput($stage_id, $main_product_id, $input_value) {
        $stage = $this->db
            ->select('d.id, d.job_work, d.output, jw.items as job_work_name, jw.suffix as job_work_suffix, p.name as main_product_name')
            ->from('sma_product_dcc_stages d')
            ->join('products p', 'p.id = d.product_id', 'inner')
            ->join('sma_standard_job_works jw', 'jw.id = d.job_work', 'left')
            ->where('d.id', (int) $stage_id)
            ->where('d.product_id', (int) $main_product_id)
            ->get()
            ->row();

        if (!$stage) {
            return false;
        }

        $input_value = trim((string) $input_value);
        $main_product_name = !empty($stage->main_product_name) ? trim($stage->main_product_name) : '';

        // Persist blank input while preserving existing output.
        if ($input_value === '') {
            $output_value = isset($stage->output) ? $stage->output : '';
        } else {
            $output_value = $this->buildDccOutputValue($main_product_name, $stage->job_work_suffix, $stage->job_work_name, $main_product_id);
        }
       
        $this->db->where('id', (int) $stage_id)->update('sma_product_dcc_stages', array(
            'input' => $input_value,
            'output' => $output_value,
        ));

        return $this->db->affected_rows() >= 0 ? $output_value : false;
    }
    
    /**
     * Size/model variants (group_id = 1) used when spawning raw job-work child products.
     * DB is the source of truth after add/update so edit matches add behaviour (e.g. Name_Stitched_Medium).
     */
    private function getJobWorkSizeVariants($main_product_id, $product_attributes = []) {
        $by_name = array();
        if (!empty($product_attributes) && is_array($product_attributes)) {
            foreach ($product_attributes as $pa) {
                if (isset($pa['group_id']) && (string) $pa['group_id'] !== '1') {
                    continue;
                }
                if (!empty($pa['name'])) {
                    $code = isset($pa['code']) ? $pa['code'] : (isset($pa['id']) ? $pa['id'] : '');
                    $by_name[$pa['name']] = array('name' => $pa['name'], 'code' => $code);
                }
            }
        }
        $q = $this->db->get_where('product_variants', array(
            'product_id' => (int) $main_product_id,
            'group_id' => 1,
        ));
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $by_name[$row->name] = array(
                    'name' => $row->name,
                    'code' => $row->id,
                );
            }
        }
        return array_values($by_name);
    }
    private function prepareJobWorkProductInsert($data, $overrides = array()) {
        $base_fields = array(
            'code', 'article_code', 'barcode_symbology', 'weight', 'storage_type', 'name',
            'hsn_code', 'type', 'brand', 'category_id', 'subcategory_id', 'cost', 'price', 'mrp',
            'unit', 'sale_unit', 'purchase_unit', 'tax_rate', 'tax_method', 'alert_quantity',
            'track_quantity', 'details', 'product_details', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6',
            'eshop_name', 'eshop_price', 'shelf_life', 'storage_conditions', 'season_id', 'rank',
            'flag_visible', 'discount_on_mrp', 'other_categories', 'product_inward_type', 'divisionid', 'quantity',
        );
        $new_product = array();
        foreach ($base_fields as $field) {
            if (array_key_exists($field, $data)) {
                $new_product[$field] = $data[$field];
            }
        }
        if (!isset($new_product['season_id']) && isset($data['Season_id'])) {
            $new_product['season_id'] = $data['Season_id'];
        }
        $new_product = array_merge($new_product, $overrides);
        $new_product['primary_variant'] = 0;
        if (empty($new_product['eshop_name']) && !empty($new_product['name'])) {
            $new_product['eshop_name'] = $new_product['name'];
        }
        return $new_product;
    }
}
