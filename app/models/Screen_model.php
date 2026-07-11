<?php
/**
 * Created by PhpStorm.
 * User: ravi
 * Date: 10/26/2017
 * Time: 9:41 AM
 */
class Screen_model extends CI_Model{

    public function __construct()
    {
        parent::__construct();
        //$this->load->language('cron');
    }
    function find($id){

        $this->db->from('division');
        $this->db->where("id",$id);
        $query = $this->db->get();
        return $query->row();
    }

    function delivered($id,$data){
        $this->db->where("id",$id);
        return $this->db->update('suspended_items',$data);
    }

    public function getAllSuspendedBills($parm = array()){
    if ($parm['id'] == 0) {
        $parm['id'] = "SELECT id FROM " . $this->db->dbprefix('division');
    }

    $query = "
        SELECT 
            p.divisionid,sb.kot_tokan,sb.suspend_note,sb.customer,sb.created_by,u.group_id,d.name AS division_name,t.name AS table_name,sbi.*,(sbi.quantity - sbi.isdelivered) AS balance_quantity,
            c.id AS category_id, 
            c.name AS category_name,

            CASE 
                WHEN sbi.option_id IS NOT NULL AND sbi.option_id > 0 
                    THEN IFNULL(wpv.quantity, 0)
                ELSE IFNULL(wp.quantity, 0)
            END AS current_stock,

            IF(
                pv.name IS NULL,
                sbi.product_name,
                CONCAT(
                    sbi.product_name, 
                    ' (', 
                    IF(sbi.note = '', pv.name, CONCAT(pv.name, ':', sbi.note)), 
                    ')'
                )
            ) AS product_name

        FROM " . $this->db->dbprefix('suspended_bills') . " sb
        INNER JOIN " . $this->db->dbprefix('suspended_items') . " sbi 
            ON sbi.suspend_id = sb.id
        INNER JOIN " . $this->db->dbprefix('products') . " p 
            ON p.id = sbi.product_id
        INNER JOIN " . $this->db->dbprefix('division') . " d 
            ON d.id = p.divisionid
        LEFT JOIN " . $this->db->dbprefix('users') . " u 
            ON u.id = sb.created_by

        LEFT JOIN " . $this->db->dbprefix('product_variants') . " pv 
            ON pv.id = sbi.option_id AND pv.name IS NOT NULL
        LEFT JOIN " . $this->db->dbprefix('warehouses_products') . " wp 
            ON wp.product_id = sbi.product_id AND wp.warehouse_id = sbi.warehouse_id
        LEFT JOIN " . $this->db->dbprefix('warehouses_products_variants') . " wpv 
            ON wpv.product_id = sbi.product_id AND wpv.option_id = sbi.option_id AND wpv.warehouse_id = sbi.warehouse_id
        LEFT JOIN " . $this->db->dbprefix('categories') . " c 
            ON c.id = p.category_id
        LEFT JOIN " . $this->db->dbprefix('restaurant_tables') . " t 
            ON t.id = sb.table_id

        WHERE sb.date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) 
          AND p.divisionid IN ({$parm['id']}) 
          AND sbi.isdelivered != sbi.quantity";

        // Add user filtering - only show KOT items created by current user
        if (isset($parm['created_by']) && $parm['created_by']) {
            $query .= " AND sb.created_by = " . $parm['created_by'];
        }

        // Add role-based filtering if user_group_id is provided (optional)
        if (isset($parm['user_group_id']) && $parm['user_group_id'] && $parm['user_group_id'] !== 'all') {
            $query .= " AND (u.group_id = {$parm['user_group_id']} OR u.group_id IS NULL)";
        }

        $query .= " ";

    if ($this->pos_settings->display_category == 1) {
        $query .= " ORDER BY c.id DESC ";
    }

    $q = $this->db->query($query);
    return $q->result();
}

    function kot(){
        $parm['id'] = 0;
        // Add current user ID filter for KOT display - but NOT for restaurant and bakery POS types
        $CI =& get_instance();
        $current_user_id = $CI->session->userdata('user_id');
        $pos_settings = $CI->settings_model->getSettings();
        
        // Only apply user filtering if POS type is NOT restaurant or bakery
        if ($current_user_id && !in_array($pos_settings->pos_type, ['restaurant', 'bakery'])) {
            $parm['created_by'] = $current_user_id;
        }
        $rows = $this->getAllSuspendedBills($parm);
        $rows = $this->objToArray($rows);

        $arr = array();
        foreach($rows as $key => $item){
            $arr[$item['table_name']][$key] = $item;
        }

        ksort($arr, SORT_NUMERIC);
        //print_r($arr);exit;
        return $arr;
    }

    function objToArray($obj, &$arr){

        if(!is_object($obj) && !is_array($obj)){
            $arr = $obj;
            return $arr;
        }

        foreach ($obj as $key => $value)
        {
            if (!empty($value)){
                $arr[$key] = array();
                $this->objToArray($value, $arr[$key]);
            }else{
                $arr[$key] = $value;
            }
        }
        return $arr;
    }


      /**
     * 
     * @param type $product_id
     */
    public function getComboProduct($product_id = NULL){
        $combo = $this->db->where(['product_id'=> $product_id])->get('sma_combo_items')->result();
        foreach ($combo as $key => $val){
           $get_product =  $this->db->select('name')->where(['code' => $val->item_code])->get('sma_products')->row();
            
            $combo_items .= $get_product->name.' '.  round($val->quantity).' + '; 
        }
        return  '( '.rtrim($combo_items,' + ').' )';
    }
    
    /**
     * Check  Printer Setting Combo Product List
     * @return type
     */
    public function getComboItemShow(){
        $getprinter = $this->db->select('default_printer')->where(['setting_id'=> '1'])->get('sma_settings')->row();
        $printer_setting = $this->db->select('*')->where(['id' => $getprinter->default_printer])->get('sma_printer_bill')->row();
        return $printer_setting;  
    }
    // delete suspend after print on kitchen printer screen
    function deleteSuspendAfterPrint($suspend_item_id) {
        // Get the suspended item record
        $item = $this->db->get_where('sma_suspended_items', ['id' => $suspend_item_id])->row();

        // Get the corresponding suspended bill
        $suspend_bill = $this->db->get_where('sma_suspended_bills', ['id' => $item->suspend_id])->row();

        // If order type is 'Dine In', skip deletion
        if (strtolower($suspend_bill->order_type) === 'dine in') {
            return true; 
        }

        // Otherwise, delete both bill and item
        $this->db->where('id', $suspend_bill->id)->delete('sma_suspended_bills');
        $this->db->where('id', $suspend_item_id)->delete('sma_suspended_items');

        return true;
    }
}