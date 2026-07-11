<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Quotes_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function isRawProduct($code){
            return $this->db
                ->where('code', $code)
                ->where('type', 'raw')
                ->get('products')
                ->row();
        }

    public function getProductNames($term, $warehouse_id, $limit = 20)
    {
        $this->db->select('products.*, warehouses_products.quantity')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
            $this->db->where('products.type !=', 'raw');

           /* $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR article_code LIKE '%" . $term . "%'  OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')"); */
           $this->db->where("(IF(name LIKE '%" . $term . "%',name LIKE '%" . $term . "%' , Replace(coalesce(name,''), ' ','') LIKE '%".str_replace(" ","",$term)."%') OR code LIKE '%" . $term . "%' OR article_code LIKE '%" . $term . "%'  OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
 
 
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWHProduct($id)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        $q = $this->db->get_where('products', array('warehouses_products.product_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('quote_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllQuoteItemsWithDetails($quote_id)
    {
        $this->db->select('quote_items.id, quote_items.product_name, quote_items.product_code, quote_items.quantity, quote_items.serial_no, quote_items.tax,quote_items.net_unit_price,quote_items.unit_quantity, quote_items.unit_price, quote_items.val_tax, quote_items.discount_val, quote_items.gross_total, products.details');
        $this->db->join('products', 'products.id=quote_items.product_id', 'left');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('quotes_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    //existing function

    // public function getAllQuoteItems($quote_id)
    // {
    //     $this->db->select('quote_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, product_variants.name as variant')
    //         ->join('products', 'products.id=quote_items.product_id', 'left')
    //         ->join('product_variants', 'product_variants.id=quote_items.option_id', 'left')
    //         ->join('tax_rates', 'tax_rates.id=quote_items.tax_rate_id', 'left')
    //         ->group_by('quote_items.id')
    //         ->order_by('id', 'asc');
    //     $q = $this->db->get_where('quote_items', array('quote_id' => $quote_id));
    //     if ($q->num_rows() > 0) {
    //         foreach (($q->result()) as $row) {
    //             $data[] = $row;
    //         }
    //         return $data;
    //     }
    //     return FALSE;
    // }


    //new function

    public function getAllQuoteItems($quote_id){
    $this->db->select('quote_items.*, 
        tax_rates.code as tax_code, 
        tax_rates.name as tax_name, 
        tax_rates.rate as tax_rate, 
        products.unit, 
        products.image, 
        products.details as details, 
        product_variants.name as variant,
        pv_shade.name as shade_name') // added shade name
        ->join('products', 'products.id=quote_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=quote_items.option_id', 'left')
        ->join('product_variants pv_shade', 'pv_shade.id=quote_items.shade_id', 'left') // join shade_id
        ->join('tax_rates', 'tax_rates.id=quote_items.tax_rate_id', 'left')
        ->group_by('quote_items.id')
        ->order_by('id', 'asc');

    $q = $this->db->get_where('quote_items', array('quote_id' => $quote_id));
    if ($q->num_rows() > 0) {
        foreach (($q->result()) as $row) {
            $data[] = $row;
        }
        return $data;
    }
    return FALSE;
}


    public function addQuote($data = array(), $items = array()){
        $customer_id = $data['customer_id'];
        $customer_state_code = $this->sma->getstatecode($customer_id);
        $billers_id  = $data['biller_id'];
        $billers_state_code = $this->sma->getstatecode($billers_id);
        if (empty($customer_state_code)) {
            $customer_state_code = $billers_state_code;
        }
        $customer_state_code = strtoupper(trim($customer_state_code));
        $billers_state_code = strtoupper(trim($billers_state_code));
        $GSTType = ($customer_state_code == $billers_state_code && !empty($customer_state_code))? 'GST':'IGST';
        if ($this->db->insert('quotes', $data)) {
            $quote_id = $this->db->insert_id();
            if ($this->site->getReference('qu') == $data['reference_no']) {
                $this->site->updateReference('qu');
            }
             $total_cgst = 0;$total_sgst = 0;$total_igst = 0;
            foreach ($items as $item) {
                $item['quote_id'] = $quote_id;
                $this->db->insert('quote_items', $item);
                $quote_item_id = $this->db->insert_id();
                // $taxAtrr = $this->sma->taxAtrrClassification($item['tax_rate_id'],$item['net_unit_price'],$item['unit_quantity'],$quote_item_id,$quote_id,'q');
                
              /*Add New field to quote_items,quotes cgst,igst,sgst 17-1-2020*/
                //$tax_ItemAtrr = $this->sma->taxArr_rate_attr($item['tax_rate_id'],$item['net_unit_price'],$item['unit_quantity'],$quote_item_id,$quote_id);
              
                 $tax_ItemAtrr = $this->sma->taxArr_rate_gst($item['tax_rate_id'], $item['net_unit_price'], $item['quantity'], $quote_item_id, $quote_id,$GSTType);
           
                //if($tax_ItemAtrr[0]['attr_code'] != 'IGST'){
                if($GSTType != 'IGST'){
                $cgst = $tax_ItemAtrr[0]['CGST'] !="" ? $tax_ItemAtrr[0]['CGST'] : 0;
                $sgst = $tax_ItemAtrr[1]['SGST'] !="" ? $tax_ItemAtrr[1]['SGST'] : 0;
                $igst =  0;
                }else{
                $cgst = 0;
                $sgst = 0;
                $igst = $tax_ItemAtrr[0]['IGST'] !="" ? $tax_ItemAtrr[0]['IGST'] : 0;   
                }
                $this->db->update('quote_items', array('gst_rate' => $tax_ItemAtrr[0]['attr_per'], 'cgst' => $cgst,'sgst' => $sgst, 'igst' => $igst), array('id' => $quote_item_id));
                $total_cgst  = $total_cgst + $cgst;
                $total_sgst  = $total_sgst + $sgst;
                $total_igst  = $total_igst + $igst;
                $taxAtrr = $this->sma->taxAtrrClassification($item['tax_rate_id'],$item['net_unit_price'],$item['unit_quantity'],$quote_item_id,$quote_id,'q');
            }
            $this->db->update('quotes', array('cgst' => $total_cgst, 'sgst' => $total_sgst,'igst' => $total_igst), array('id' =>  $quote_id));
          
            return true;
        }
        return false;
    }

    public function updateQuote($id, $data, $items = array()){
        $customer_id = $data['customer_id'];
        $customer_state_code = $this->sma->getstatecode($customer_id);
        $billers_id  = $data['biller_id'];
        $billers_state_code = $this->sma->getstatecode($billers_id);
        if (empty($customer_state_code)) {
            $customer_state_code = $billers_state_code;
        }
        $customer_state_code = strtoupper(trim($customer_state_code));
        $billers_state_code = strtoupper(trim($billers_state_code));
        $GSTType = ($customer_state_code == $billers_state_code && !empty($customer_state_code))? 'GST':'IGST';

        if ($this->db->update('quotes', $data, array('id' => $id)) && $this->db->delete('quote_items', array('quote_id' => $id))) {
            $this->db->delete('quote_items_tax', array('quote_id' => $id));
             $total_cgst = 0;$total_sgst = 0;$total_igst = 0;
            foreach ($items as $item) {
                $item['quote_id'] = $id;
                $this->db->insert('quote_items', $item);
                $quote_item_id = $this->db->insert_id();
                $taxAtrr = $this->sma->taxAtrrClassification($item['tax_rate_id'],$item['net_unit_price'],$item['unit_quantity'],$quote_item_id,$id,'q');
                /*Add New field to quote_items,quotes cgst,igst,sgst 17-1-2020*/
                $tax_ItemAtrr = $this->sma->taxArr_rate_gst($item['tax_rate_id'], $item['net_unit_price'], $item['quantity'], $quote_item_id, $id,$GSTType);
                //$tax_ItemAtrr = $this->sma->taxArr_rate($item['tax_rate_id'],$item['net_unit_price'],$item['unit_quantity'],$quote_item_id,$id);
             
                  //if($tax_ItemAtrr[0]['attr_code'] != 'IGST'){
                if($GSTType != 'IGST'){
                $cgst = $tax_ItemAtrr[0]['CGST'] !="" ? $tax_ItemAtrr[0]['CGST'] : 0;
                $sgst = $tax_ItemAtrr[1]['SGST'] !="" ? $tax_ItemAtrr[1]['SGST'] : 0;
                $igst = 0;
                }else{
                $cgst = 0;
                $sgst = 0;
                $igst = $tax_ItemAtrr[0]['IGST'] !="" ? $tax_ItemAtrr[0]['IGST'] : 0;   
                }
                $this->db->update('quote_items', array('gst_rate' => $tax_ItemAtrr[0]['attr_per'], 'cgst' => $cgst,'sgst' => $sgst, 'igst' => $igst), array('id' => $quote_item_id));
                $total_cgst  = $total_cgst + $cgst;
                $total_sgst  = $total_sgst + $sgst;
                $total_igst  = $total_igst + $igst;
            }
            $this->db->update('quotes', array('cgst' => $total_cgst, 'sgst' => $total_sgst,'igst' => $total_igst), array('id' =>  $id));
          
            return true;
        }
        return false;
    }

    public function updateStatus($id, $status, $note)
    {
        if ($this->db->update('quotes', array('status' => $status, 'note' => $note), array('id' => $id))) {
            return true;
        }
        return false;
    }


    public function deleteQuote($id)
    {
        if ($this->db->delete('quote_items', array('quote_id' => $id)) && $this->db->delete('quotes', array('id' => $id))) {
         $this->db->delete('quote_items_tax', array('quote_id' => $id));
            return true;
        }
        return FALSE;
    }

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductComboItems($pid, $warehouse_id)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }

public function getProductOptions($product_id, $warehouse_id)
    {
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)
            ->where('warehouses_products_variants.quantity >', 0)
            ->group_by('product_variants.id');
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }



    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getAllTaxItems($quote_id,$return_id,$itemId=NULL)  {
        $this->db->select("attr_code,attr_name,attr_per, `tax_amount`  AS `amt`,item_id");
        $this->db->where_in('quote_id', array($quote_id,$return_id)); 
        $q =  $this->db->get('quote_items_tax'); 
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
               $data[$row->item_id][$row->attr_code] = $row;
            }
            return $data;
        }
        return FALSE;
    }
     
    public function getAllTaxItemsGroup($quote_id,$return_id=NULL)  {
        $this->db->select("attr_code,attr_name,attr_per,sum(`tax_amount`) AS `amt`");
        $this->db->where_in('quote_id', array((int)$quote_id,(int)$return_id)); 
        $this->db->group_by('attr_code'); 
        $this->db->order_by('id', 'asc'); 
        $q =  $this->db->get('quote_items_tax');    
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function validateRecieptQuote($TransKey = NULL, $User_id = NULL)
    {
        $this->db->select('quotes.id');
        $this->db->from('quotes');

        if( ! empty($TransKey)):
            $this->db->where("MD5(CONCAT('quote_reciept',id))", $TransKey);
        endif;

        $this->db->order_by("id ", "desc");
        $q = $this->db->get();

        if($q->num_rows() > 0) :
            return $q->result_array();
        endif;
        return FALSE;
    }

    public function getUnitById($id)
    {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductcolorOptions($product_id, $warehouse_id, $type = null) {
    $this->db->select('*')
             ->from('product_variants')
             ->where('product_id', $product_id)
             ->where('warehouse_id', $warehouse_id);
    
    if ($type !== null) {
        $this->db->where('type', $type);
    }
    
    $this->db->order_by('name', 'ASC');
    $q = $this->db->get();
    
    if ($q->num_rows() > 0) {
        foreach ($q->result() as $row) {
            $data[] = $row;
        }
        return $data;
    }
    return false;
}

public function getProductVariantsWithoutcolor($product_id, $variant_popup = null, $GroupId='') {
        $this->db->select('product_variants.*, manage_variant.variant_name');
        $this->db->from('product_variants');
        $this->db->join('manage_variant', 'product_variants.name = manage_variant.variant_name', 'left'); 
        $this->db->where('product_variants.product_id', $product_id);
        $this->db->where('product_variants.group_id', $GroupId);
        $this->db->order_by('manage_variant.id', 'ASC');
        $q = $this->db->get(); 
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row;
            }
            // return ($variant_popup == 1) ? $data : array_values($data);
            return $data;
        }
        return false;
    }

public function getProductoptioncolor($id) {
        
        $this->db->order_by('name', 'ASC'); 
        $q = $this->db->get_where('product_variants', ['id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function getProductOptionscolor($product_id, $warehouse_id, $GroupId='') {
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', FALSE)
                ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
                ->where('product_variants.product_id', $product_id)
                ->where('product_variants.group_id', $GroupId)
                ->group_by('product_variants.id');
        if (!$this->Settings->overselling && !$all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getProductSizes($product_id) {
    $this->db->select('id, name');
    $this->db->where('product_id', $product_id);
    $this->db->where('group_id', 1); // size
    $q = $this->db->get('product_variants');
    return $q->result();
}
public function getProductColors($product_id) {
    $this->db->select('id, name');
    $this->db->where('product_id', $product_id);
    $this->db->where('group_id', 2); // color
    $q = $this->db->get('product_variants');
    return $q->result();
}

    public function getBomDetailsByProductId($product_id)
    {
        $this->db->select('produnit_bill_of_materials.*')
            ->from('produnit_bill_of_materials')
            ->where('produnit_bill_of_materials.product_id', $product_id)
            ->where('produnit_bill_of_materials.is_active', 1);
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getBomMaterialItems($product_id, $location_id)
    {
        $sql = "
            SELECT 
                bom_items.material_id,
                bom_items.quantity_required,
                p.name AS raw_material,
                p.code AS product_code,
                u.name AS unit_name,
                pb.sales_units,
                pb.min_batch_qty,
                batch_unit.name AS batch_unit_name
            FROM sma_produnit_bill_of_material_items AS bom_items
            JOIN sma_produnit_bill_of_materials AS bom ON bom_items.bom_id = bom.id
            JOIN sma_products p ON bom_items.material_id = p.id
            LEFT JOIN (
                SELECT pb1.product_id, pb1.sales_units, pb1.min_batch_qty, pb1.batch_uom
                FROM sma_produnit_products_in_batches pb1
                INNER JOIN (
                    SELECT product_id, MAX(id) AS max_id
                    FROM sma_produnit_products_in_batches
                    GROUP BY product_id
                ) pb2 ON pb1.product_id = pb2.product_id AND pb1.id = pb2.max_id
            ) AS pb ON pb.product_id = bom.product_id
            LEFT JOIN sma_units u ON bom_items.uom = u.id
            LEFT JOIN sma_units batch_unit ON pb.batch_uom = batch_unit.id
            WHERE bom.product_id = ?
            AND bom.version_no = (
                SELECT MAX(CAST(version_no AS UNSIGNED))
                FROM sma_produnit_bill_of_materials
                WHERE product_id = ? AND is_active = 1
            )
            AND bom.is_active = 1
            GROUP BY bom_items.material_id
        ";
        return $this->db->query($sql, [(int)$product_id, (int)$product_id])->result_array();
    }


}
