<?php

class Urban_piper_model  extends CI_Model
{

    /**
     * 
     * @param type $key
     * @param type $table
     * @param type $whercondition Array Format [columnname => columndata]
     * @param type $fielddata  Array format [columnname => columndata]
     * @return boolean
     */
    public function action_database($key, $table, $whercondition = NULL, $fielddata = NULL)
    {
        //        $get_arguments = func_get_args();
        //Note: 0. action_key, 1. table name, 2. where condition array format, 3. field data Array format

        $msg = false;
        switch ($key) {

            case 'Insert':
                $rec = $this->db->insert($table, $fielddata);
                $msg = ($rec) ? true : false;
                break;

            case 'Update':
                $rec = $this->db->where($whercondition)->update($table, $fielddata);
                $msg = ($rec) ? true : false;
                break;

            case 'Delete':
                $rec = $this->db->where($whercondition)->delete($table);
                $msg = ($rec) ? true : false;
                break;

            default:
                $msg = false;
                break;
        }

        return $msg;
    }

    public function updateSettingsOrder($data)
    {

        $rec = $this->db->where(['setting_id' => '1'])->update('sma_settings', $data);
        if ($rec) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 
     * @param type $tablename
     * @param type $wharecondition array()
     * @param type $getdata
     * @return type
     */
    public function check_dependancy($tablename, $wharecondition, $getdata)
    {
        // Note: 0. Table name, 1. where condition, 2. getdata
        $return_data  =  $this->db->select($getdata)->where($wharecondition)->get($tablename)->row();
        return ($this->db->affected_rows() > 0) ? $return_data : false;
    }

    public function getStoreCategories($store_id)
    {

        $sql = "SELECT  `id` ,  `name` ,  `code` ,  `parent_id` 
            FROM  `sma_categories` 
            WHERE  `id` 
            IN (
                SELECT `category_id` 
                FROM `sma_up_stores_categories` 
                WHERE `store_id` = '$store_id'
                AND `up_added` = '1'
            )
            ORDER BY  `name`";

        $results = $this->db->query($sql)->result();

        if ($results) {
            foreach ($results as $key => $catdata) {
                $data[$catdata->id] = $catdata;
            }
            return $data;
        }
        return false;
    }


    public function getallstore($id = '')
    {

        if ($id) {
            $get_data = $this->db->get_where('sma_up_stores', ['id' => $id])->result();
        } else {
            $get_data = $this->db->get('sma_up_stores')->result();
        }
        return $get_data;
    }

    public function getStoreByReffId($ref_id)
    {

        $get_data = $this->db->get_where('sma_up_stores', ['ref_id' => $ref_id])->result();

        return $get_data[0];
    }


    public function getrecords()
    {
        // Note : - 0. Tablename, 1. getfields (array format), 2. retrun type data (row,row_array,result,result_array), 3. Where Condition (array() format), 4. oredr field, 5. order field type(ASC/DESC)
        $getarg = func_get_args(); // get function arguments
        $this->db->select($getarg[1]);
        if ($getarg[3]) {
            $this->db->where($getarg[3]);
        } // Where condition
        if ($getarg[4] && $getarg[5]) {
            $this->db->order_by($getarg[4], $getarg[5]);
        } // Data order by
        $getdata = $this->db->get($getarg[0]); // Table Name
        switch ($getarg[2]) { // return data format
            case 'row':
                $data = $getdata->row();
                break;

            case 'row_array':
                $data = $getdata->row_array();
                break;

            case 'result': // object type data
                $data = $getdata->result();
                break;

            case 'result_array': // array type data
                $data = $getdata->result_array();
                break;

            default: // object type data
                $data = $getdata->result();
                break;
        }
        return  $data;
    }

    public function getcategory($where_condition)
    {
        $this->db->select('t1.*, t2.code as parent_code');
        $this->db->from('sma_categories AS t1')->where($where_condition);
        $this->db->join('sma_categories AS t2', 't1.parent_id = t2.id', 'left');
        return $this->db->get()->result();
    }


    public function updateStoreCategoryStatus($category_id, $store_id, $action = 'Add_category')
    {

        switch ($action) {
            case 'Add_category':

            case 'Edit_category':
                $statusData = " up_is_active='1', up_added='1' ";
                break;
            case 'Enable_category':
                $statusData =  " up_is_active ='1' ";
                break;
            case 'Disable_category':
                $statusData =  " up_is_active='0' ";
                break;
            case 'Delete_category':
                $statusData =  " up_is_active='0', up_added='0' ";
                break;
            default:
                $statusData = " up_is_active='1', up_added='1' ";
        } //end switch.

        $categoryIds = is_array($category_id) ? join(',', $category_id) : $category_id;

        $sql = "UPDATE `sma_up_stores_categories` SET $statusData WHERE `store_id`='$store_id' AND `category_id` IN ($categoryIds) ";
        $this->db->query($sql);

        /* $q = $this->db->where('store_id' , $store_id)
                ->where_in('category_id' , $categoryIds)
                ->update('sma_up_stores_categories', $statusData);
        */
        /* if($this->db->affected_rows()){
            return true;
        } else {
            return false; //$this->db->_error_message();
        }*/

        return true;
    }

    public function getMasterCategories($catid = '')
    {

        if ($catid) {
            $catid = (is_array($catid)) ? join(',', $catid) : $catid;
            $sql = "SELECT c.`id`,c.`name`,c.`image`,c.`parent_id`,c.`up_description`, upc.`store_id`, upc.`category_ref_id` , upc.`store_ref_id`, upc.parent_ref_id, upc.up_added, upc.up_is_active "
                . "FROM `sma_categories` as c LEFT JOIN `sma_up_stores_categories` as upc on c.`id` = upc.`category_id` "
                . "WHERE c.`id` IN ($catid) group by upc.category_id ";
            $results = $this->db->query($sql)->result();
        } else {
            $results = $this->db->from('sma_categories')->order_by('parent_id', 'asc')->get()->result();
        }
        if ($results) {
            foreach ($results as $row) {
                $data[$row->id] = $row;
                $data[$row->id]->up_description = $data[$row->id]->name;
            }
        }
        return $data;
    }


    public function importNotStoreCategory($store_id)
    {

        $query = "SELECT id, code, parent_id FROM sma_categories WHERE id NOT IN ( SELECT category_id from sma_up_stores_categories WHERE store_id = '$store_id' ) order by parent_id asc ";

        $results = $this->db->query($query)->result();

        if (count($results)) {

            $storedata = $this->getallstore($store_id);

            foreach ($results as $row) {
                $upcategory[$row->id]['category_id'] = $row->id;
                $upcategory[$row->id]['category_ref_id'] = $row->code;
                $upcategory[$row->id]['parent_id'] = $row->parent_id;
                $upcategory[$row->id]['parent_ref_id'] = $upcategory[$row->parent_id]['category_ref_id'];
                $upcategory[$row->id]['store_id'] = $store_id;
                $upcategory[$row->id]['store_ref_id'] = $storedata[0]->ref_id;
                $upcategory[$row->id]['up_is_active'] = 0;
                $upcategory[$row->id]['up_added'] = 0;
            }

            return $upcategory;
        }

        return [];
    }

    public function getUpStoreCategory($store_id)
    {

        $query = "SELECT c.id, c.code, c.name, c.image, upc.parent_id, upc.parent_ref_id, c.up_category, c.up_description, upc.up_is_active, upc.up_added, upc.store_ref_id "
            . "FROM sma_categories as c "
            . "Left Join sma_up_stores_categories upc ON c.id = upc.category_id "
            . "WHERE upc.store_id = '$store_id' "
            . "ORDER BY upc.parent_id ASC ";

        $results = $this->db->query($query)->result();

        //$results = $this->db->get_where('sma_up_stores_categories', ['store_id'=>$store_id])->result();

        if (count($results)) {

            foreach ($results as $row) {
                $upcategory[$row->id] = $row;
                //$upcategory[$row->id]->parent_category = ($row->parent_id) ?  $results[$row->parent_id]->name : '';
            }

            return $upcategory;
        }

        return [];
    }

    public function getStoreCategoryProducts($store = null, $category = '')
    {

        if ($category) {

            $sql2 = "SELECT `id`, `code`, `name`, `image`, `category_id`, `subcategory_id`, "
                . " (SELECT name FROM `sma_categories` WHERE `id` = `category_id`   ) AS category_name, "
                . " (SELECT name FROM `sma_categories` WHERE `id` = `subcategory_id`  ) AS subcategory_name  "
                . "FROM `sma_products` WHERE `up_items` = '1' AND (`category_id`='$category' OR `subcategory_id`='$category')  "
                . "GROUP BY  `id` ORDER BY  `name` ";

            $upProduct = $this->db->query($sql2)->result();

            foreach ($upProduct as $key => $upproduct) {

                $data[$upproduct->id] = $upproduct;
            }

            $sql = "SELECT p.`id`, p.`code`, p.`name`, p.`image`,  p.`category_id`, p.`subcategory_id`, uppp.active_status, uppp.add_status, uppp.up_store_id, "
                . " (SELECT name FROM `sma_categories` WHERE `id` = `category_id` ) AS category_name, "
                . " (SELECT name FROM `sma_categories` WHERE `id` = `subcategory_id` ) AS subcategory_name  "
                . "FROM `sma_products` p  "
                . "LEFT JOIN `sma_up_products_platform` uppp ON p.id = uppp.product_id  "
                . "WHERE `up_items` = '1' AND (`category_id`='$category' OR `subcategory_id`='$category') AND ( uppp.up_store_id = '$store' ) "
                . "GROUP BY p.`id` ORDER BY p.`name` ";


            $resultProduct = $this->db->query($sql)->result();
            if ($resultProduct) {
                foreach ($resultProduct as $key => $product) {

                    $data[$product->id] = $product;
                }
            }
            return $data;
        } else {

            $sql = "SELECT p.`id`, p.`code`, p.`name`,p.`image`, p.`category_id`, p.`subcategory_id`, uppp.active_status, uppp.add_status, uppp.up_store_id, "
                . " (SELECT name FROM `sma_categories` WHERE `id` = p.`category_id`) AS category_name, "
                . " (SELECT code FROM `sma_categories` WHERE `id` = p.`category_id`) AS category_code,  "
                . " (SELECT name FROM `sma_categories` WHERE `id` = p.`subcategory_id`) AS subcategory_name, "
                . " (SELECT code FROM `sma_categories` WHERE `id` = p.`subcategory_id`) AS subcategory_code  "
                . "FROM `sma_products` p "
                . "LEFT JOIN `sma_up_products_platform` uppp ON p.id = uppp.product_id "
                . "WHERE `up_items` = '1' AND uppp.up_store_id = '$store' "
                . "GROUP BY p.`id` ORDER BY p.`name` ";

            $resultProduct = $this->db->query($sql)->result();
            if ($resultProduct) {
                foreach ($resultProduct as $key => $product) {

                    $data[$product->id] = $product;
                }
                return $data;
            } else {
                return false;
            }
        }
    }


    public function getproduct($where_condition)
    {
        $this->db->select('t1.*,t0.price as up_price,t0.food_type_id as food_type, t0.active_status as up_status, t0.add_status as up_add_status, t0.plat_urbanpiper,t0.plat_zomato,t0.plat_foodpanda,t0.plat_swiggy  ,t0.plat_ubereats,t2.code as category_code,t2.name as category_name ,t3.code as sub_category_code,t3.name as sub_category_name');
        $this->db->from('sma_products AS t1')->where($where_condition);
        $this->db->join('sma_up_products AS t0', 't1.id = t0.product_id', 'rigth');
        $this->db->join('sma_categories AS t2', 't1.category_id = t2.id', 'left');
        $this->db->join('sma_categories AS t3', 't1.subcategory_id = t3.id', 'left');
        return $this->db->get()->result();
    }

    public function getproductsingle($where_condition)
    {
        $this->db->select('t1.*,t0.price as up_price,t0.food_type_id as food_type, t0.active_status as up_status, t0.add_status as up_add_status, t0.plat_urbanpiper,t0.plat_zomato,t0.plat_foodpanda,t0.plat_swiggy ,t0.plat_ubereats,t2.code as category_code,t2.name as category_name ,t3.code as sub_category_code,t3.name as sub_category_name');
        $this->db->from('sma_products AS t1')->where($where_condition);
        $this->db->join('sma_up_products AS t0', 't1.id = t0.product_id', 'rigth');
        $this->db->join('sma_categories AS t2', 't1.category_id = t2.id', 'left');
        $this->db->join('sma_categories AS t3', 't1.subcategory_id = t3.id', 'left');
        return $this->db->get()->row();
    }

    //    public function getproductplatformdata(array $product_ids, $store_id){
    //       
    //         $q = $this->db->where(['up_store_id'=>$store_id])
    //                 ->where_in(['product_id'=>$product_ids])
    //                 ->get('sma_up_products_platform');
    //         
    //         if($q->num_rows()){
    //            foreach ($q->result() as $products) {
    //                
    //                $data[$products->product_id] = $products;
    //                
    //            }
    //            return $data;
    //         }
    //         
    //         return false;
    //    }

    public function getproductplatformdata($product_ids)
    {

        // $q = $this->db->select('`plat_urbanpiper` as tag_urbanpiper, `plat_zomato` as tag_zomato, `plat_foodpanda` as tag_foodpanda, `plat_swiggy` as tag_swiggy, `plat_ubereats` as tag_ubereats, default_tag,  `available` , `sold_at_store`, `recommended`, `product_id`, `product_code`, manage_stock')
        $q = $this->db->select('`available` , `sold_at_store`, `recommended`, `product_id`, `product_code`, manage_stock')   
        ->where_in(['product_id' => $product_ids])
            ->get('sma_up_products');

        if ($q->num_rows()) {
            foreach ($q->result() as $products) {
                $data[$products->product_id] = $products;
            }
            return $data;
        }

        return false;
    }


    /**
     * 
     * @param type $where_condition
     * @return type
     */
    public function getproduct_allup($where_condition)
    {

        $this->db->select('t1.*,t0.id as upproduct_id,t0.price as up_price,t0.food_type_id as food_type, t0.active_status as up_status, t0.add_status as up_add_status, t0.plat_urbanpiper,t0.plat_zomato,t0.plat_foodpanda,t0.plat_swiggy ,t0.plat_ubereats ,t0.manage_stock,t2.code as category_code,t2.name as category_name ,t3.code as sub_category_code,t3.name as sub_category_name');
        $this->db->from('sma_products AS t1')->where_in('t1.id', $where_condition);
        $this->db->join('sma_up_products AS t0', 't1.id = t0.product_id', 'rigth');
        $this->db->join('sma_categories AS t2', 't1.category_id = t2.id', 'left');
        $this->db->join('sma_categories AS t3', 't1.subcategory_id = t3.id', 'left');

        return $this->db->get()->result();
    }


    public function count_new_sales()
    {

        $wherehouse = $this->session->userdata()['warehouse_id'];

        if (!$this->Owner && !$this->Admin) {
            $flag = TRUE;
        } else {
            $getData =  $this->db->select('order_notification_admin')->where(['id' => 1])->get('sma_up_settings')->row();

            if ($getData->order_notification_admin) {
                $flag = TRUE;
            } else {
                $flag = FALSE;
            }
        }
        if ($flag) {


            $data['num'] = 0;
            $data['notify'] = 0;
            $data['new_order'] = 0;

            $this->db->select('id, sale_status, up_sales, up_sales_notification')
                ->where('up_sales_notification', '1');

            if ($wherehouse) {
                $warehouseExp = explode(",", $wherehouse);
                $this->db->where_in('warehouse_id', $warehouseExp);
            }
            $q = $this->db->get('sma_sales');


            if ($q->num_rows()) {
                foreach ($q->result() as $sale) {
                    if ($sale->sale_status == 'cancle' || $sale->up_sales_notification > 1) continue;
                    $data['num']++;
                    $data['notify'] += ($sale->up_sales_notification == 1) ? 1 : 0;
                    $data['new_order'] += ($sale->up_sales_notification == 1) ? 1 : 0;
                    //$data['sales'][] = $sale;
                }
                return $data;
            }
        }
    }


    public function upmnotifiy()
    {
        $this->db->where('up_sales_notification', '1')->update('sma_sales', array('up_sales_notification' => '2'));
    }


    public function orderStatuslist()
    {

        $q = $this->db->select('id,title')->get_where('up_status', ['is_active' => 1, 'is_delete' => 0]);

        if ($q->num_rows()) {
            foreach ($q->result() as $key => $row) {
                $data[$row->title] = $row->title;
            }
            return $data;
        }
        return false;
    }

    public function getOrders($saleid = null)
    {

        $select = 's.`id` as sale_id, s.`total`,s.`sale_status`,s.`delivery_status`,'
            . 's.`up_channel`,s.`up_response`,s.`up_status`,s.`up_sales`,s.`up_item_level_total_charges`,s.`up_order_id`,s.`up_delivery_datetime`,'
            . 's.`up_coupon`,s.`up_next_status`, s.`up_prev_state`,s.`up_state_timestamp`, s.`up_message`, s.`up_status_response`, s.`up_sales_notification`, '
            . 's.`up_order_level_total_charges`,s.`customer_id`, s.`customer`, '
            . 'upor.id as order_rider_id, upor.channel_order_id, upor.current_state, upor.name, upor.phone, upor.comments, '
            . 'upor.order_status, upor.created, upor.up_order_rider_response';

        $wheresales = ($saleid) ? " AND s.`id` = '$saleid' " : '';

        $sql = "SELECT $select FROM `sma_sales` s "
            . "LEFT JOIN `sma_up_orderrider` upor ON s.up_order_id = upor.up_order_id "
            . "WHERE s.`up_sales` = '1' " . $wheresales
            . "ORDER BY s.id DESC ";

        $q = $this->db->query($sql);

        if ($q->num_rows()) {
            foreach ($q->result() as $row) {
                $data[$row->sale_id] = $row;
            }
        }

        return $data;
    }

    public function getUpOrders($type = 'active')
    {

        $select = 's.`id` as sale_id, s.`date`, s.`total`,s.`sale_status`,s.`delivery_status`,'
            . 's.`up_channel`,s.`up_response`,s.`up_status`,s.`up_sales`,s.`up_item_level_total_charges`,s.`up_order_id`,s.`up_delivery_datetime`,'
            . 's.`up_coupon`,s.`up_next_status`, s.`up_prev_state`,s.`up_state_timestamp`, s.`up_message`, s.`up_status_response`, s.`up_sales_notification`, '
            . 's.`up_order_level_total_charges`,s.`customer_id`, s.`customer`, '
            . 'upor.id as order_rider_id, upor.channel_order_id, upor.current_state, upor.name, upor.phone, upor.comments, '
            . 'upor.order_status, upor.created, upor.up_order_rider_response';

        $where = ($type == 'active') ? " AND s.`sale_status` NOT IN ( 'Completed','Cancelled' ) " : " AND s.`sale_status` IN ( 'Completed','Cancelled' ) ";

        // if (!$this->Owner && !$this->Admin) { //&& !$this->session->userdata('view_right')
        //     $where .= ' AND warehouse_id =  ' . $this->session->userdata('warehouse_id') . ' ';
        // }

        $sql = "SELECT $select FROM `sma_sales` s "
            . "LEFT JOIN `sma_up_orderrider` upor ON s.up_order_id = upor.up_order_id "
            . "WHERE s.`up_sales` = '1' " . $where
            . "ORDER BY s.id DESC ";

        $q = $this->db->query($sql);
        $data = [];
        if ($q->num_rows()) {
            foreach ($q->result() as $row) {
                $data[$row->sale_id] = $row;
            }
        }

        return $data;
    }
    // public function getUpOrdersfor3p($type = 'active')
    // {

    //     $select = 's.`id` as sale_id, s.`date`, s.`total`,s.`sale_status`,s.`delivery_status`,'
    //         . 's.`up_channel`,s.`up_response`,s.`up_status`,s.`up_sales`,s.`up_item_level_total_charges`,s.`up_order_id`,s.`up_delivery_datetime`,'
    //         . 's.`up_coupon`,s.`up_next_status`, s.`up_prev_state`,s.`up_state_timestamp`, s.`up_message`, s.`up_status_response`, s.`up_sales_notification`, '
    //         . 's.`up_order_level_total_charges`,s.`customer_id`, s.`customer`, '
    //         . 'upor.id as order_rider_id, upor.channel_order_id, upor.current_state, upor.name, upor.phone, upor.comments, '
    //         . 'upor.order_status, upor.created, upor.up_order_rider_response';

    //     $where = ($type == 'active') ? " AND s.`sale_status` NOT IN ( 'Completed','Cancelled','Dispatched' ) " : " AND s.`sale_status` IN ( 'Completed','Cancelled','Dispatched' ) ";

    //     // if (!$this->Owner && !$this->Admin) { //&& !$this->session->userdata('view_right')
    //     //     $where .= ' AND warehouse_id =  ' . $this->session->userdata('warehouse_id') . ' ';
    //     // }

    //     $sql = "SELECT $select FROM `sma_sales` s "
    //         . "LEFT JOIN `sma_up_orderrider` upor ON s.up_order_id = upor.up_order_id "
    //         . "WHERE s.`up_sales` = '1' " . $where
    //         . "ORDER BY s.id DESC ";

    //     $q = $this->db->query($sql);
    //     $data = [];
    //     if ($q->num_rows()) {
    //         foreach ($q->result() as $row) {
    //             $data[$row->sale_id] = $row;
    //         }
    //     }

    //     return $data;
    // }
    public function getUpOrdersfor3p($type = 'active', $include_dispatched = false)
    {

        $select = 's.`id` as sale_id, s.`date`, s.`total`,s.`sale_status`,s.`delivery_status`,'
            . 's.`up_channel`,s.`up_response`,s.`up_status`,s.`up_sales`,s.`up_item_level_total_charges`,s.`up_order_id`,s.`up_delivery_datetime`,'
            . 's.`up_coupon`,s.`up_next_status`, s.`up_prev_state`,s.`up_state_timestamp`, s.`up_message`, s.`up_status_response`, s.`up_sales_notification`, '
            . 's.`up_order_level_total_charges`,s.`customer_id`, s.`customer`, '
            . 'upor.id as order_rider_id, upor.channel_order_id, upor.current_state, upor.name, upor.phone, upor.comments, '
            . 'upor.order_status, upor.created, upor.up_order_rider_response';

         $conditions = ["s.`up_sales` = '1'"];
            if ($type == 'active') {
                if ($include_dispatched) {
                    $conditions[] = "(s.sale_status IN ('Dispatched', 'Completed', 'Cancelled') AND DATE(s.date) = CURDATE())";
                } else {
                    $conditions[] = "(s.sale_status NOT IN ('Dispatched', 'Completed', 'Cancelled') AND DATE(s.date) = CURDATE())";
                }
            } else {
                $conditions[] = "(s.sale_status IN ('Placed', 'Acknowledged', 'Food Ready') AND DATE(s.date) = CURDATE())";
            }
            
        // Combine all conditions into one WHERE clause
        $where = implode(' AND ', $conditions);

        $sql = "SELECT $select FROM `sma_sales` s "
            . "LEFT JOIN `sma_up_orderrider` upor ON s.up_order_id = upor.up_order_id "
            . "WHERE $where ORDER BY s.id DESC";

        $q = $this->db->query($sql);

        $data = [];
        if ($q->num_rows()) {
            foreach ($q->result() as $row) {
                $data[$row->sale_id] = $row;
            }
        }

        return $data;
    }
    public function getUpOrdersforAmazon($type = 'active', $include_dispatched = false)
    {
        if ($type == 'active') {
            if ($include_dispatched) {
                // Include today's dispatched orders
                $where = "WHERE ao.order_status = 'Dispatched' 
                          AND DATE(ao.last_update_date) = CURDATE()";
            } else {
                // Exclude dispatched, only active orders
                $where = "WHERE ao.order_status NOT IN ('Dispatched', 'Cancelled', 'Completed')
                          AND DATE(ao.purchase_date) = CURDATE()";
            }
        } else {
            // Fallback for other active states
            $where = "WHERE ao.order_status IN ('Pending', 'Shipped', 'Delivered')
                      AND DATE(ao.purchase_date) = CURDATE()";
        }
    
        $sql = "SELECT 
                    ao.id,
                    ao.amazon_order_id,
                    ao.purchase_date,
                    ao.order_status,
                    ao.fulfillment_channel,
                    ao.ship_service_level,
                    ao.shipping_address_City AS city,
                    ao.shipping_address_StateOrRegion AS state,
                    ao.shipping_address_PostalCode AS postal_code,
                    ao.shipping_address_CountryCode AS country,
                    ao.order_total_amount AS total_amount,
                    ao.order_total_currency_code AS currency_code,
                    ao.buyerinfo_email
                FROM sma_Amazon_Orders_ep ao
                ORDER BY ao.id DESC";
    
        $q = $this->db->query($sql);
        $data = [];
    
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                // ✅ Use amazon_order_id as key to match the structure of other sources
                $data[$row->amazon_order_id] = $row;
            }
        }
        return $data;
    }
    
    public function getAmazonOrderById($amazon_order_id)
    {
        return $this->db->where('amazon_order_id', $amazon_order_id)
                        ->get('amazon_order_details')
                        ->row();
    }

    public function getAmazonOrderItems($amazon_order_id)
    {
        return $this->db->where('amazon_order_id', $amazon_order_id)
                        ->get('amazon_order_items')
                        ->result();
    }
    public function getOrderItems($saleid)
    {

        $q = $this->db->where(['sale_id' => $saleid])->get('sale_items');

        if ($q->num_rows()) {
            foreach ($q->result() as $row) {
                $data[$row->product_code] = $row;
            }
        }

        return $data;
    }

    public function setCallbackLog(array $callbackdata)
    {

        if ($this->db->insert('sma_up_callback_log', $callbackdata)) {
            return true;
        }

        return false;
    }

    public function get_foodtype()
    {

        $q = $this->db->get('sma_food_type');

        if ($q->num_rows()) {
            foreach ($q->result() as $row) {
                $data[$row->id] = $row->food_type;
            }
        }

        return $data;
    }

    public function set_notification_order_status($status = 1)
    {

        if ($status == 1) {
            return $this->db->where(['up_sales' => '1', 'up_sales_notification' => '0'])
                ->update('sma_sales', ['up_sales_notification' => $status]);
        }
        if ($status == 2) {
            return $this->db->where(['up_sales' => '1', 'up_sales_notification' => '1'])
                ->update('sma_sales', ['up_sales_notification' => $status]);
        }
    }

    public function updateUpOrderPackage($order)
    {

        return $this->db->query('UPDATE `sma_up_package_logs` SET  `balance_order` = (  `balance_order` - ' . $order . ' ) WHERE  `balance_order` > 0 ORDER BY `created_at` ASC LIMIT 1');
    }

    public function delete_up_store($id)
    {
        if ($this->db->delete("up_stores", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }



    /**
     * Manage Quantity
     * @param type $sale_id
     */
    public function syncQuantity($sale_id)
    {
        if ($sale_items = $this->getAllInvoiceItems($sale_id)) {
            foreach ($sale_items as $item) {
                $managestock  =  $this->check_dependancy('sma_up_products ', ['product_id' => $item->product_id], 'manage_stock');
                if ($managestock->manage_stock == '1') {
                    $this->site->syncProductQty($item->product_id, $item->warehouse_id);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->site->syncVariantQty($item->option_id, $item->warehouse_id);
                    }
                }
            }
        }
    }

    /**
     * Get All Invoice Items
     * @param type $sale_id
     * @param type $return_id
     * @return boolean
     */
    public function getAllInvoiceItems($sale_id, $return_id = NULL)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant, products.image , product_variants.price as variant_price, products.hsn_code as hsncode, sales.rounding as rounding')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('sales', 'sales.id=sale_items.sale_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_items.sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_items.sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
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
     * @param type $type
     * @param type $action
     * @param type $error
     * @param type $refid
     * @param type $category_status
     * @return boolean
     */
    public function cateloginjectionManage($type,  $action, $error, $refid, $category_status = NULL)
    {

        switch ($type) {
            case 'category':

                if ($error) {

                    switch ($action) {
                        case 'A':
                            $result =  $this->manageAction('sma_up_stores_categories', ['category_ref_id' => $refid], ['up_added' => '0', 'up_is_active' => '0']);
                            return $result;
                            break;

                        case 'U':
                            $status = ($category_status) ? '0' : '1';
                            $result =  $this->manageAction('sma_up_stores_categories', ['category_ref_id' => $refid], ['up_is_active' => $status]);
                            return $result;
                            break;

                        case 'D':

                            $result =  $this->manageAction('sma_up_stores_categories', ['category_ref_id' => $refid], ['up_added' => '0', 'up_is_active' => '0']);
                            return $result;
                            break;

                        default:
                            return FALSE;
                            break;
                    }
                }


                break;

            default:
                return FALSE;
                break;
        }
    }


    /**
     * 
     * @param type $tablename
     * @param type $wherecondition
     * @param type $data
     * @return type
     */
    public function manageAction($tablename, $wherecondition, $data)
    {
        $this->db->where($wherecondition)->update($tablename, $data);
        return ($this->db->affected_rows() ? TRUE : FALSE);
    }




    /*************************************
     *  Urbanpiper Order Costing
     *************************************/

    /**
     * 
     * @param type $items
     * @return type
     */
    public function uporder_costing($items)
    {
        $citems = array();
        foreach ($items as $item) {
            $managestock = $this->db->select('manage_stock')->where(['product_id' => $item['product_id']])->get('sma_up_products')->row();
            if ($managestock->manage_stock == '1') {


                $item['option_id'] = (isset($item['option_id']) && $item['option_id']) ? $item['option_id'] : 0;
                $pr = $this->site->getProductByID($item['product_id']);
                if ($pr->type == 'standard') {

                    if ($pr->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1) {
                        $item['option_id'] = 0;
                    }

                                      if (isset($citems['p' . $item['product_id'] . 'o' . $item['option_id']])) {
                                           $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] += $item['unit_quantity'];
                                       } else {
                                           $citems['p' . $item['product_id'] . 'o' . $item['option_id']] = $item;
                                         $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] = $item['unit_quantity'];
                                      }

                   
                } elseif ($pr->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') {
                            if (isset($citems['p' . $combo_item->id . 'o' . $item['option_id']])) {
                                $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] += ($combo_item->qty * $item['unit_quantity']);
                            } else {
                                $cpr = $this->site->getProductByID($combo_item->id);
                                if ($cpr->tax_rate) {
                                    $cpr_tax = $this->site->getTaxRateByID($cpr->tax_rate);
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
        }
        $cost = array();

        foreach ($citems as $item) {
            $managestock = $this->db->select('manage_stock')->where(['product_id' => $item['product_id']])->get('sma_up_products')->row();
            if ($managestock->manage_stock == '1') {
                $item['option_id'] = (isset($item['option_id']) && $item['option_id']) ? $item['option_id'] : 0;
                $item['aquantity'] = $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'];

                $cost[] = $this->uporder_item_costing($item, TRUE);
            }
        }

        return $cost;
    }


    /**
     * 
     * @param type $item
     * @param type $pi
     * @return array
     */
    public function uporder_item_costing($item, $pi = NULL)
    {

        $item_quantity = $pi ? $item['aquantity'] : $item['unit_quantity'];
        if (!isset($item['option_id']) || empty($item['option_id']) || $item['option_id'] == 'null') {
            $item['option_id'] = 0;
        }

        if ($this->Settings->accounting_method != 2 && !$this->Settings->overselling) {

            if ($this->site->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $unit = $this->site->getUnitByID($item['product_unit_id']);
                    $item['net_unit_price'] = $this->site->convertToBase($unit, $item['net_unit_price']);
                    $item['unit_price'] = $this->site->convertToBase($unit, $item['unit_price']);
                    $cost = $this->calculateUPOrderCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->site->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->site->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->site->getTaxRateByID($pr->tax_rate);
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
                            $cost[] = $this->calculateUPOrderCost($pr->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $pr->name, NULL, $item_quantity);
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

            if ($this->site->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $cost = $this->calculateUPOrderAVCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->site->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $cost = $this->calculateUPOrderAVCost($combo_item->id, $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], ($combo_item->qty * $item['quantity']), $item['product_name'], $item['option_id'], $item_quantity);
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


    /**
     * 
     * @param type $product_id
     * @param type $warehouse_id
     * @param type $net_unit_price
     * @param type $unit_price
     * @param type $quantity
     * @param type $product_name
     * @param type $option_id
     * @param type $item_quantity
     * @return type
     */
    public function calculateUPOrderCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id = 0, $item_quantity)
    {
        $option_id = $option_id ? $option_id : 0;

        $real_item_qty = $quantity;
        $quantity = $item_quantity;
        $balance_qty = $quantity;

        $unit_quantity = 1;
        $product = $this->site->getProductByID($product_id);
        if ($this->Settings->attributes == 1 &&  $product->storage_type == 'packed') {
            $pis = $this->site->getPurchasedItems($product_id, $warehouse_id, $option_id);
        } else {
            if ($product->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1) {
                $variants = $this->site->getVerientById($option_id);
                $unit_quantity = $variants['unit_quantity'] ? $variants['unit_quantity'] : 1;
                $item_quantity = $item_quantity * $unit_quantity;
            }
            $option_id = 0; //No Variant stocks available for loose products 
            $pis = $this->site->getPurchasedItems($product_id, $warehouse_id);
        } //end else

        if ($pis) {
            foreach ($pis as $pi) {
                if ($pi->quantity_balance <= 0) {
                    continue;
                } //Avoide Zero Quantity Purchase Records.
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

    /**
     * 
     * @param type $product_id
     * @param type $warehouse_id
     * @param type $net_unit_price
     * @param type $unit_price
     * @param type $quantity
     * @param type $product_name
     * @param type $option_id
     * @param type $item_quantity
     * @return type
     */
    public function calculateUPOrderAVCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id = 0, $item_quantity)
    {
        $option_id = $option_id ? $option_id : 0;
        $real_item_qty = $quantity;
        $wp_details = $this->site->getWarehouseProduct($warehouse_id, $product_id);
        //  $product_avg_cost = $this->db->select('cost')->where('id', $product_id)->get('products')->row();

        $unit_quantity = 1;
        $product = $this->site->getProductByID($product_id);
        $product_avg_cost = $product->cost;
        if ($this->Settings->attributes == 1 &&  $product->storage_type == 'packed') {
            $pis = $this->site->getPurchasedItems($product_id, $warehouse_id, $option_id, $batch_number);
        } else {
            if ($product->storage_type == 'loose' && $this->Settings->sale_loose_products_with_variants == 1) {
                $variants = $this->site->getVerientById($option_id);
                $unit_quantity = $variants['unit_quantity'] ? $variants['unit_quantity'] : 1;
                $item_quantity = $item_quantity * $unit_quantity;
            }
            $option_id = 0; //No Variant stocks available for loose products 
            $pis = $this->site->getPurchasedItems($product_id, $warehouse_id);
        } //end else


        if ($pis) {
            $cost_row = array();
            $quantity = $item_quantity;
            $balance_qty = $quantity;
            $avg_net_unit_cost = $wp_details->avg_cost;
            $avg_unit_cost = $wp_details->avg_cost;
            foreach ($pis as $pi) {
                if ($pi->quantity_balance <= 0) {
                    continue;
                } //Avoide Zero Quantity Purchase Records.
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


    /**
     * sync Urbnpiper Purchase Items
     * @param type $data
     * @return boolean
     */
    public function syncUPPurchaseItems($data = array())
    {
        if (!empty($data)) {
            foreach ($data as $items) {
                foreach ($items as $item) {
                    $managestock =  $this->db->select('manage_stock')->where(['product_id' => $item['product_id']])->get('sma_up_products')->row();
                    if ($managestock->manage_stock == '1') {

                        if (isset($item['pi_overselling'])) {
                            unset($item['pi_overselling']);
                            $batch_number = (isset($item['batch_number']) && !empty($item['batch_number'])) ? $item['batch_number'] : NULL;
                            $option_id = (isset($item['option_id']) && !empty($item['option_id'])) ? $item['option_id'] : 0;
                            $clause = array('purchase_id' => null, 'transfer_id' => null, 'product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'option_id' => $option_id, 'batch_number' => $batch_number);
                            if ($pi = $this->site->getPurchasedItem($clause)) {
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
            }
            return TRUE;
        }
        return FALSE;
    }



    /**
     * Manage Product Stock in Urbanpiper Portal
     * @param type $productId array type
     * @param type $warehouseId
     */
    // Working API
    public function Product_out_of_stock($productId, $warehouseId)
    {
        $urbanpiperdata = $this->getrecords('sma_up_settings', '*', 'row', array('id' => '1', 'is_active' => '1'));

        if ($urbanpiperdata) {

            $storeinfo = $this->db->where(['warehouse_id' => $warehouseId])->get('sma_up_stores')->row();

            if ($storeinfo) {
                $getWarehousestock = $this->db->where_in('product_id', $productId)->where(['warehouse_id' => $storeinfo->warehouse_id])->get('sma_warehouses_products')->result();

                if (!empty($getWarehousestock)) {

                    $item_ref_ids = array();
                    $optiongroup = array();
                    foreach ($getWarehousestock as $warehouseProducts) {
                        $upproducts = $this->db->select('manage_stock, product_id, product_code')->where(['product_id' => $warehouseProducts->product_id])->get('sma_up_products')->row();

                        if ($upproducts && $upproducts->manage_stock) {
                            if ($this->db->where(['product_id' => $upproducts->product_id, 'up_store_id' => $storeinfo->id])->get('sma_up_products_platform')->row()) {
                                if ($warehouseProducts->quantity < '1') {
                                    $item_ref_ids['disable'][] = $upproducts->product_code;

                                    $optionGP = $this->db->where([
                                        'product_id' => $upproducts->product_id,
                                        'warehouse_id' => $storeinfo->warehouse_id,
                                    ])->get('sma_warehouses_products_variants')->result();

                                    if (!empty($optionGP)) {
                                        foreach ($optionGP as $itemOption) {
                                            if ($itemOption->quantity < '1') {
                                                $optiongroup['disable_option'][] = $itemOption->option_id;
                                            }
                                        }
                                    }
                                } else {
                                    $pManage = $this->db->where(['product_id' => $upproducts->product_id, 'up_store_id' => $storeinfo->id])->get('sma_up_products_platform')->row();
                                    if (!empty($pManage)) {
                                        if ($pManage->active_status != '1') {
                                            $item_ref_ids['enable'][] = $upproducts->product_code;

                                            $optionGP = $this->db->where([
                                                'product_id' => $upproducts->product_id,
                                                'warehouse_id' => $storeinfo->warehouse_id,
                                            ])->get('sma_warehouses_products_variants')->result();

                                            if (!empty($optionGP)) {
                                                foreach ($optionGP as $itemOption) {
                                                    if ($itemOption->quantity > '0') {
                                                        $optiongroup['enable_option'][] = $itemOption->option_id;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }



                    if (isset($item_ref_ids['disable']) && !empty($item_ref_ids['disable'])) {
                        $passdata = array(
                            "location_ref_id"   => $storeinfo->ref_id,
                            "item_ref_ids"      => (isset($item_ref_ids['disable']) ? $item_ref_ids['disable'] : []),
                            "option_ref_ids"   => (isset($optiongroup['disable_option']) ? $optiongroup['disable_option'] : []),
                            "action"            => 'disable',
                        );


                        $URL    = 'hub/api/v1/items/';
                        $getresponse = $this->call_urbanpiper($URL, $passdata);

                        // Manage Output
                        $phpObject = json_decode($getresponse);

                        if ($phpObject->status == 'success') {
                            $this->db->where_in('product_code', $item_ref_ids['disable'])->where(['up_store_id' => $storeinfo->id])->update('sma_up_products_platform', ['active_status' => '0']);
                        }
                    }


                    if (isset($item_ref_ids['enable']) && !empty($item_ref_ids['enable'])) {

                        $passdata = array(
                            "location_ref_id"   => $storeinfo->ref_id,
                            "item_ref_ids"      => (isset($item_ref_ids['enable']) ? $item_ref_ids['enable'] : []),
                            "option_ref_ids"   => (isset($optiongroup['enable_option']) ? $optiongroup['enable_option'] : []),
                            "action"            => 'enable',
                        );

                        $URL    = 'hub/api/v1/items/';
                        $getresponse = $this->call_urbanpiper($URL, $passdata);

                        // Manage Output
                        $phpObject = json_decode($getresponse);

                        if ($phpObject->status == 'success') {
                            $this->db->where_in('product_code', $item_ref_ids['enable'])->where(['up_store_id' => $storeinfo->id])->update('sma_up_products_platform', ['active_status' => '1']);
                        }
                    }
                }
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }


    /**
     * 
     * @param type $URL
     * @param type $data
     * @return type
     */
    public function call_urbanpiper($URL, $data)
    {

        $ci = get_instance();
        $config = $ci->config;

        $URL = $config->config['UP_QUINT_URL'] . $URL;
        $urbanpiperdata = $this->getrecords('sma_up_settings', '*', 'row', array('id' => '1', 'is_active' => '1'));
        $api_key = $urbanpiperdata->api_key;
        $data_json = json_encode($data);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization:' . $api_key));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        $err = curl_error($ch);

        curl_close($ch);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    /**
     * Get Up Products 
     */
    public function getupProducts()
    {
        $products =  $this->db->get('sma_up_products')->result();
        $productsId = array();
        foreach ($products as $pitems) {
            $productsId[] = $pitems->product_id;
        }

        $getStore = $this->db->where(['store_add_urbanpiper' => '1'])->get('sma_up_stores')->result();
        foreach ($getStore as $itemstore) {
            $this->Product_out_of_stock($productId, $itemstore->warehouse_id);
        }
    }


    /**
     * 
     * @param type $productcode
     * @param type $warehouseId
     * @return boolean
     */
    public function productStockcheck($productcode, $warehouseId)
    {
        $productinfo =  $this->db->where(['product_code' => $productcode])->get('sma_up_products')->row();
        $warehouseQty = $this->db->select('quantity')->where(['product_id' => $productinfo->product_id, 'warehouse_id' => $warehouseId])->get('sma_warehouses_products')->row();

        if ($warehouseQty->quantity > '0') {
            return TRUE;
        } else {
            return FALSE;
        }
    }



    /**
     * Get Product Variant Details
     * 
     * @param type $id
     * @return boolean
     */

    public function product_variants_Details($id)
    {
        $productVariant = $this->db->select('*')->where(['id' => $id])->get('sma_product_variants')->row();
        if ($this->db->affected_rows()) {
            return $productVariant;
        }
        return false;
    }
//****************************  Webshop  ************************************* */
// public function getUpOrdersforwebshop($type = 'active')
// {
//     $where = ($type == 'active')  ? " WHERE s.`sale_status` NOT IN ('Dispatched')" : " WHERE s.`sale_status` IN ('Received','In progress','Ready')";

//     $sql = " SELECT s.*, c.name AS customer_name, c.phone AS customer_phone
//         FROM `sma_orders` s
//         LEFT JOIN `sma_companies` c ON s.customer_id = c.id $where
//         ORDER BY s.id DESC";
//     $q = $this->db->query($sql);
//     $data = [];
//     if ($q->num_rows()) {
//         foreach ($q->result() as $row) {
//             $data[$row->id] = $row; 
//         }
//     }
//     return $data;
// }
public function getUpOrdersforwebshop($type = 'active', $include_dispatched = false)
{
    if ($type == 'active') {
        if ($include_dispatched) {
            // Filter today's dispatched orders based on updated_at
            $where = " WHERE s.sale_status = 'Dispatched' AND DATE(s.updated_at) = CURDATE() AND s.payment_status != 'Failed'";
        } else {
            // All except dispatched
            $where = " WHERE s.sale_status NOT IN ('Dispatched') AND DATE(s.date) = CURDATE() AND s.payment_status != 'Failed'";
        }
    } else {
        // Fallback for other cases
        $where = " WHERE s.sale_status IN ('Received', 'In progress', 'Ready') AND DATE(s.date) = CURDATE() AND s.payment_status != 'Failed'";
    }

    // Use CURDATE() to filter by today's date for 'Dispatched' status orders
    $sql = "SELECT s.*, c.name AS customer_name, c.phone AS customer_phone
            FROM sma_orders s
            LEFT JOIN sma_companies c ON s.customer_id = c.id $where
            ORDER BY s.id DESC";

    $q = $this->db->query($sql);
    $data = [];
    if ($q->num_rows()) {
        foreach ($q->result() as $row) {
            $data[$row->id] = $row;
        }
    }
    return $data;
}
public function orderStatuslistwebshop()
    {
        $q = $this->db->select('id,title')->get_where('webshop_status', ['is_active' => 1, 'is_delete' => 0]);
        if ($q->num_rows()) {
            foreach ($q->result() as $key => $row) {
                $data[$row->title] = $row->title;
            }
            return $data;
        }
        return false;
    }
public function getOrdersforwebshop($saleid = null)
{
    $wheresales = ($saleid) ? " AND s.`id` = '$saleid' " : '';
    $sql = "SELECT s.*, c.name AS customer_name, c.phone AS customer_phone, c.email AS customer_email,c.address AS customer_address,c.city AS customer_city,
                c.state AS customer_state,c.country AS customer_country
            FROM `sma_orders` s
            LEFT JOIN `sma_companies` c ON s.customer_id = c.id
            WHERE 1=1 $wheresales
            ORDER BY s.id DESC";
    $q = $this->db->query($sql);
    $data = [];
    if ($q->num_rows()) {
        foreach ($q->result() as $row) {
            $data[$row->id] = $row;
        }
    }
    return $data;
}

public function getOrderItemsforwebshop($saleid)
    {
        $q = $this->db->where(['sale_id' => $saleid])->get('order_items');
        if ($q->num_rows()) {
            foreach ($q->result() as $row) {
                $data[$row->product_code] = $row;
            }
        }
        return $data;
    }
    public function updateDeliveringStatus($sale_id, array $updateItemsDelivery, $saleDeliveryStatus,$orderstatus) {
        $this->db->update('orders', ['sale_status' => $orderstatus], array('id' => $sale_id));
        if ($this->db->update('sales', ['delivery_status' => $saleDeliveryStatus], array('id' => $sale_id))) {
            if (is_array($updateItemsDelivery)) {
                foreach ($updateItemsDelivery as $itm_id => $itemsStatus) {
                    $this->db->update('sale_items', $itemsStatus, array('id' => $itm_id));
                }//end foreach
            }//end if
            return true;
        }//end if
        return false;
    }
    public function getAllEshopOrderItemsbyid($order_id)
    {
        $this->db->where('sale_id', $order_id);
        $query = $this->db->get('sma_order_items');
        return $query->result(); 
    }
   
    public function update_webshop_order_and_items($id, $data, $items = array(), $payment, $flag = NULL)
    {  
        $this->orders_model->resetEshopOrderActions($id, FALSE, TRUE);
        $customer_id = $data['customer_id'];
        $customer_state_code = $this->sma->getstatecode($customer_id);
        $billers_id  = $data['biller_id'];
        $billers_state_code = $this->sma->getstatecode($billers_id);
        $GSTType = ($customer_state_code == $billers_state_code)? 'GST':'IGST';	
        $order = $data ;
        if($flag =='flag_paynow'){
            $order['sale_status'] = 'Received';
        }else{
            $order['sale_status'] = 'In Progress';
        }
        if ($data['sale_status'] == 'completed') {
            $cost = $this->site->order_costing($items);
        }
        if ($this->db->update('orders', $order, array('id' => $id)) && 
            $this->db->delete('order_items', array('sale_id' => $id)) && 
            $this->db->delete('costing', array('order_id' => $id))) {
	        $this->db->delete('orders_items_tax', array('order_id' => $id));
            if(!empty($items)){
                foreach ($items as $item) {
                $item['sale_id'] = $id;
                $this->db->insert('order_items', $item);
                $sale_item_id = $this->db->insert_id();
                $_taxSaleID = $id;
                $_tax_type = 'o';
                $taxAtrr = $this->sma->taxAtrrClassification($item['tax_rate_id'],$item['net_unit_price'],$item['unit_quantity'],$sale_item_id,$_taxSaleID,$_tax_type);
                $tax_ItemAtrr = $this->sma->taxArr_rate_gst($item['tax_rate_id'], $item['net_unit_price'], $item['quantity'], $sale_item_id, $_taxSaleID,$GSTType);
                if($GSTType != 'IGST'){
                $cgst = $tax_ItemAtrr[0]['CGST'] !="" ? $tax_ItemAtrr[0]['CGST'] : 0;
                $sgst = $tax_ItemAtrr[1]['SGST'] !="" ? $tax_ItemAtrr[1]['SGST'] : 0;
                $igst = 0;
                }else{
                $cgst = 0;
                $sgst = 0;
                $igst = $tax_ItemAtrr[0]['IGST'] !="" ? $tax_ItemAtrr[0]['IGST'] : 0;   
                } 
                $this->db->update('order_items', array('gst_rate' => $tax_ItemAtrr[0]['attr_per'], 'cgst' => $cgst,'sgst' => $sgst, 'igst' =>  $igst), array('id' => $sale_item_id));
                if ($data['sale_status'] == 'completed' && $this->site->getProductByID($item['product_id'])) {
                    $item_costs = $this->site->order_item_costing($item);
                    if(!empty($item_costs)) {
                        foreach($item_costs as $item_cost) {
                        if(isset($item_cost['date'])) {
                            $item_cost['order_item_id']  = $sale_item_id;
                            $item_cost['order_id']       = $id;
                            if(!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            if(!empty($item_cost) && (is_array($item_cost) || is_object($item_cost))){                                 
                                    foreach ($item_cost as $key=>$ic) {                               
                                        $item_cost['order_item_id'] = $sale_item_id;
                                        $item_cost['order_id'] = $id;
                                        if(! isset($item_cost['pi_overselling'])) {                                           
                                            $this->db->insert('costing', $item_cost);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            $total_cgst  = $total_cgst + $cgst;
            $total_sgst  = $total_sgst + $sgst;
            $total_igst  = $total_igst + $igst;
            }
            $this->db->update('orders', array('cgst' => $total_cgst, 'sgst' => $total_sgst,'igst' => $total_igst), array('id' => $id));
        }
        if ($data['sale_status'] == 'completed') {
            $this->site->syncPurchaseItems($cost);
        }
		// if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
        //         if (empty($payment['reference_no'])) {
        //             $payment['reference_no'] = $this->site->getReference('pay');
        //         }
        //         $payment['order_id'] = $id;
        //         if ($payment['paid_by'] == 'gift_card') {
        //             $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
        //             unset($payment['gc_balance']);
        //             $sqlpayment = $this->db->where(['order_id'=>$id])->get('payments')->row();
        //             if($sqlpayment){
        //                 $this->db->where(['order_id'=>$id])->update('payments',$payment);
        //             }else{
        //             $this->db->insert('payments', $payment);
        //             }
        //         } else {
        //             if ($payment['paid_by'] == 'deposit') {
        //                 $customer = $this->site->getCompanyByID($data['customer_id']);
        //                 $this->db->update('companies', array('deposit_amount' => $payment['cc_holder']), array('id' => $data['customer_id']));
        //             }
        //             $sqlpayment = $this->db->where(['order_id'=>$id])->get('payments')->row();
        //             if($sqlpayment){
        //                 $this->db->where(['order_id'=>$id])->update('payments',$payment);
        //             }else{
        //             $this->db->insert('payments', $payment);
        //             }
        //         }
        //         if ($this->site->getReference('pay') == $payment['reference_no']) {
        //             $this->site->updateReference('pay');
        //         }
        //     }
			$this->site->syncSaleActionPayments($id, 'eshop_order');
            $this->site->syncQuantity( NULL, NULL, NULL, NULL, $id );
            // $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            return true;
        }
        return false;
    }
    public function getUpOrdersforpos(){
        $today = date('Y-m-d'); 

        $this->db->select('s.*, si.*, c.*');
        $this->db->from('sma_sales s');
        $this->db->join('sma_sale_items si', 'si.sale_id = s.id', 'left');
        $this->db->join('sma_companies c', 'c.id = s.customer_id', 'left');
        $this->db->where('s.order_type', 'Delivery');
        $this->db->where('DATE(s.date)', $today); // Show only today's orders
        $this->db->group_by('s.id');
        $this->db->order_by('s.id', 'DESC');

        return $this->db->get()->result();
    }
    public function getOrdersforPos($saleid = null)
{
    $data = [];

    $select = 'c.phone AS customer_phone,c.email AS customer_email,c.address AS customer_address,s.`id` as sale_id, s.`total`, s.`sale_status`, s.`delivery_status`,s.`grand_total`,s.total_tax AS product_tax,s.`date`,s.`order_type`,
        s.`up_channel`, s.`up_response`, s.`up_status`, s.`up_sales`,
        s.`up_item_level_total_charges`, s.`up_order_id`, s.`up_delivery_datetime`,
        s.`up_coupon`, s.`up_next_status`, s.`up_prev_state`,
        s.`up_state_timestamp`, s.`up_message`, s.`up_status_response`,
        s.`up_sales_notification`, s.`up_order_level_total_charges`,
        s.`customer_id`, s.`customer`,s.`billing_address_id`,s.`shipping_address_id`,
        upor.id as order_rider_id, upor.channel_order_id, upor.current_state,
        upor.name, upor.phone, upor.comments, upor.order_status,
        upor.created, upor.up_order_rider_response, p.paid_by';

    $this->db->select($select);
    $this->db->from('sma_sales s');
    $this->db->join('sma_up_orderrider upor','s.up_order_id = upor.up_order_id','left');
    $this->db->join('sma_companies c','s.customer_id = c.id','left');
    $this->db->join('sma_payments p','s.id = p.sale_id','left');

    if ($saleid !== null) {
        $this->db->where('s.id', $saleid);
    }

    $this->db->order_by('s.id', 'DESC');

    $q = $this->db->get();

    if ($q->num_rows() > 0) {
        foreach ($q->result() as $row) {
            $data[$row->sale_id] = $row;
        }
    }

    return $data;
}


}
