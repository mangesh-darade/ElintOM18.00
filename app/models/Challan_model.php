<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Challan_model extends CI_Model
{
    private $challans;

    public function __construct()
    {
        parent::__construct();
        $this->challans = [];
        $this->load->model('sales_model');
    }

    public function addChallan($data = array(), $items = array(), $payment = array(), $si_return = array(), $extrasPara = array())
    {
        $challan_id_orig = isset($data['challan_id']) ? $data['challan_id'] : NULL;
        $si_return = (!empty($si_return)) ? $si_return : ((isset($extrasPara['si_return']) && !empty($extrasPara['si_return'])) ? $extrasPara['si_return'] : array());
        // Allow costing for returns to restore stock via item_costing (subtraction of negative quantity)
        if (!isset($data['challan_status']) || $data['challan_status'] != 'pending') {
            $cost = $this->site->costing($items);
        }
         
        $sale_action    = $extrasPara['sale_action'] ? $extrasPara['sale_action'] : 'chalan';
        $challan_id_orig = $extrasPara['challan_id'] ? $extrasPara['challan_id'] : ($extrasPara['order_id'] ? $extrasPara['order_id'] : null);
        $syncQuantity   = $extrasPara['syncQuantity'];
            
        $data['sale_as_chalan'] = 1;

        if ($this->db->insert('delivery_challan', $data)) {
            $challan_id = $this->db->insert_id();

            $todaydate = $data['date'] ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d');
            
            //Get formated Invoice No
            $biller_invoice_prefix = $this->site->getPrefixByBillerFromLocation($data['warehouse_id'],$data['biller_id']);
            if ($biller_invoice_prefix) {
                if ($sale_action == 'chalan' || $sale_action == 'return') {
                    $invoice_no = $challan_id;
                } else {
                    $formatted_no = $this->sma->biller_invoice_format($challan_id, $todaydate, $biller_invoice_prefix, true);
                    $invoice_no = $biller_invoice_prefix .'/'. $formatted_no;
                }
            } else {
                $invoice_no = $this->sma->invoice_format($challan_id, $todaydate, true);
            }
            $this->db->where(['id' => $challan_id])->update('delivery_challan', ['invoice_no' => NULL, 'challan_no' => $invoice_no]);

            if ($this->site->getReference('ordr') == $data['reference_no']) {
                $this->site->updateReference('ordr');
            }
            if (isset($data['return_sale_ref']) && $this->site->getReference('re_ordr') == $data['return_sale_ref']) {
               $this->site->updateReference('re_ordr');
            } elseif (isset($data['return_challan_ref']) && $this->site->getReference('re_ordr') == $data['return_challan_ref']) {
               $this->site->updateReference('re_ordr');
            }
            
            foreach ($items as $item) {
                $item['challan_id'] = $challan_id;
                $this->db->insert('delivery_challan_items', $item);
                $sale_item_id = $this->db->insert_id();
                    
                $_taxSaleID =  $challan_id;
                $_tax_type = 'dc'; 
                
                $this->sma->taxAtrrClassification($item['tax_rate_id'], $item['net_unit_price'], $item['unit_quantity'], $sale_item_id, $_taxSaleID, $_tax_type);
                
                /* Add GST fields per item */
                $tax_ItemAtrr = $this->sma->taxArr_rate($item['tax_rate_id'], $item['net_unit_price'], $item['unit_quantity'], $sale_item_id, $_taxSaleID);
                if ($tax_ItemAtrr[0]['attr_code'] != 'IGST') {
                    $cgst = $tax_ItemAtrr[0]['CGST'] != "" ? $tax_ItemAtrr[0]['CGST'] : 0;
                    $sgst = $tax_ItemAtrr[1]['SGST'] != "" ? $tax_ItemAtrr[1]['SGST'] : 0;
                    $igst = $tax_ItemAtrr[2]['IGST'] != "" ? $tax_ItemAtrr[2]['IGST'] : 0;
                } else {
                    $cgst = 0;
                    $sgst = 0;
                    $igst = $tax_ItemAtrr[0]['IGST'] != "" ? $tax_ItemAtrr[0]['IGST'] : 0;
                }
                $this->db->update('delivery_challan_items', array('gst_rate' => $tax_ItemAtrr[0]['attr_per'], 'cgst' => $cgst, 'sgst' => $sgst, 'igst' => $igst), array('id' => $sale_item_id));

                if ((isset($data['sale_status']) ? $data['sale_status'] : (isset($data['challan_status']) ? $data['challan_status'] : '')) == 'completed') {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date'])) { 
                            $item_cost['order_item_id'] = $sale_item_id;
                            $item_cost['order_id'] = $challan_id;
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                if(is_array($ic)):
                                    $ic['order_item_id'] = $sale_item_id;
                                    $ic['order_id']      = $challan_id;
                                    if(! isset($ic['pi_overselling'])) {
                                        $this->db->insert('costing', $ic);
                                    }
                                endif;
                            }
                        }
                    }
                }                         
            }

            if (((isset($data['sale_status']) && in_array($data['sale_status'], ['completed', 'returned'])) || (isset($data['challan_status']) && in_array($data['challan_status'], ['completed', 'returned']))) && $syncQuantity) {
                $this->site->syncPurchaseItems($cost);
            }
            
            if (!empty($si_return)) {
                foreach ($si_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);
                    if ($product->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            $this->sales_model->updatePurchaseItem(NULL, ($return_item['quantity'] * $combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                        }
                    } else {
                        $this->sales_model->updatePurchaseItem(NULL, $return_item['quantity'], $return_item['id'], $return_item['product_id'], $return_item['warehouse_id'], $return_item['option_id']);
                    }
                }
                $return_ref = isset($data['return_challan_ref']) ? $data['return_challan_ref'] : (isset($data['return_sale_ref']) ? $data['return_sale_ref'] : NULL);
                $this->db->update('delivery_challan', array('return_challan_ref' => $return_ref, 'surcharge' => $data['surcharge'], 'return_challan_total' => $data['grand_total'], 'return_id' => $challan_id), array('id' => $challan_id_orig));
            }

            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['challan_id'] = $challan_id; 
                $this->db->insert('payments', $payment);
                
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncSaleActionPayments($challan_id, 'chalan');
            }
            
            if($syncQuantity) {
                $this->site->syncQuantity( NULL, NULL, NULL, NULL, NULL, NULL, $challan_id );

                // Urbanpiper Stock Manage 
                if($this->Settings->pos_type == 'restaurant'){
                    $this->load->model("Urban_piper_model","UPM");
                    $productids = array();
                    foreach($items as $upproduct){
                        $productids[] = $upproduct['product_id'];
                    }
                    $this->UPM->Product_out_of_stock($productids, $data['warehouse_id']);
                }

                $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            }            
            
            $data['id'] = $challan_id;
            $this->site->syncProductTransactionHistory('return_challan', array('challan' => $data, 'items' => $items));
            return $challan_id;
        }
        return false;
    }

    public function getChallanByID($id)
    {
        $q = $this->db->get_where('delivery_challan', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getChallanPayments($challan_id) {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', array('challan_id' => $challan_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getPaymentsForChallan($challan_id) {
        $this->db->select('payments.id,payments.date, payments.paid_by, payments.amount,payments.transaction_id, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
                ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('challan_id' => $challan_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getPaymentByID($id) {
        $q = $this->db->get_where('payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllChallanItems($challan_id)
    {
        $this->db->select('delivery_challan_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant, product_variants.price as variant_price, products.hsn_code as hsncode, delivery_challan.rounding as rounding, delivery_challan_items.seller_id, delivery_challan_items.seller, products.category_id as category_id, categories.name as category_name')
            ->join('products', 'products.id=delivery_challan_items.product_id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->join('product_variants', 'product_variants.id=delivery_challan_items.option_id', 'left')
            ->join('delivery_challan', 'delivery_challan.id=delivery_challan_items.challan_id', 'left')
            ->join('tax_rates', 'tax_rates.id=delivery_challan_items.tax_rate_id', 'left')
            ->group_by('delivery_challan_items.id')
            ->order_by('id', 'asc');
        
        $q = $this->db->get_where('delivery_challan_items', array('delivery_challan_items.challan_id' => $challan_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllTaxChallanItems($challan_id, $return_id = NULL)
    {
        $this->db->select("attr_code,attr_name,attr_per, `tax_amount`  AS `amt`,item_id");
        $this->db->where_in('challan_id', array($challan_id, $return_id)); 
        $q =  $this->db->get('delivery_challan_items_tax'); 
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
               $data[$row->item_id][$row->attr_code] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function deleteChallan($id, $syncQuantity = 0, $sale_id = null)
    {
        if($syncQuantity) {
            $items = $this->resetChallanActions($id);
        }
        if ($this->db->delete('delivery_challan_items', array('challan_id' => $id)) && $this->db->delete('delivery_challan', array('id' => $id))) {
            $this->db->delete('delivery_challan_items_tax', array('challan_id' => $id));
            
            if($sale_id){
                $this->db->update('payments', array('challan_id' => null, 'sale_id' => $sale_id), array('challan_id' => $id));
                $this->db->update('costing', array('order_id' => null, 'sale_id' => $sale_id, 'order_item_id' => null ), array('order_id' => $id));
            } else {
                $this->db->delete('payments', array('challan_id' => $id));
                $this->db->delete('costing', array('order_id' => $id));
            }
            
            if($syncQuantity && isset($items)) {
                $this->site->syncQuantity(NULL, NULL, $items);
            }
            return true;
        }
        return FALSE;
    }

    public function updateChallan($id, $data, $items = array()) {

        if (isset($items) && is_array($items)) {
            $wh_id = isset($data['warehouse_id']) ? $data['warehouse_id'] : (isset($data['from_warehouse_id']) ? $data['from_warehouse_id'] : null);
            $this->site->prefetchQtyBeforeTransaction($items, $wh_id);
        }

        $this->resetChallanActions($id, FALSE, TRUE);

        $syncQuantity = true;

        if ((isset($data['sale_status']) ? $data['sale_status'] : (isset($data['challan_status']) ? $data['challan_status'] : '')) == 'completed') {
            $cost = $this->site->costing($items);
        }

        if ($this->db->update('delivery_challan', $data, array('id' => $id)) &&
                $this->db->delete('delivery_challan_items', array('challan_id' => $id)) &&
                $this->db->delete('costing', array('order_id' => $id))) {
            $this->db->delete('delivery_challan_items_tax', array('challan_id' => $id));
            if (!empty($items)) {
                $total_cgst = 0;
                $total_sgst = 0;
                $total_igst = 0;
                foreach ($items as $item) {
                    $item['challan_id'] = $id;
                    $this->db->insert('delivery_challan_items', $item);
                    $sale_item_id = $this->db->insert_id();

                    $_taxSaleID = $id;
                    $_tax_type = 'dc';

                    $this->sma->taxAtrrClassification($item['tax_rate_id'], $item['net_unit_price'], $item['unit_quantity'], $sale_item_id, $_taxSaleID, $_tax_type);
                    
                    // /* Add New field to Sale_items Code cgst,igst,sgst 17-1-2020 */
                    // $tax_ItemAtrr = $this->sma->taxArr_rate($item['tax_rate_id'], $item['net_unit_price'], $item['unit_quantity'], $sale_item_id, $_taxSaleID);
                    // if ($tax_ItemAtrr[0]['attr_code'] != 'IGST') {
                    //     $cgst = $tax_ItemAtrr[0]['CGST'] != "" ? $tax_ItemAtrr[0]['CGST'] : 0;
                    //     $sgst = $tax_ItemAtrr[1]['SGST'] != "" ? $tax_ItemAtrr[1]['SGST'] : 0;
                    //     $igst = $tax_ItemAtrr[2]['IGST'] != "" ? $tax_ItemAtrr[2]['IGST'] : 0;
                    // } else {
                    //     $cgst = 0;
                    //     $sgst = 0;
                    //     $igst = $tax_ItemAtrr[0]['IGST'] != "" ? $tax_ItemAtrr[0]['IGST'] : 0;
                    // }
                    // $this->db->update('delivery_challan_items', array('gst_rate' => $tax_ItemAtrr[0]['attr_per'], 'cgst' => $cgst, 'sgst' => $sgst, 'igst' => $igst), array('id' => $sale_item_id));

                    if (((isset($data['sale_status']) ? $data['sale_status'] : (isset($data['challan_status']) ? $data['challan_status'] : '')) == 'completed') && $this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        if (!empty($item_costs)) {
                            foreach ($item_costs as $item_cost) {
                                if (isset($item_cost['date'])) {
                                    $item_cost['order_item_id'] = $sale_item_id;
                                    $item_cost['order_id'] = $id;
                                    if (!isset($item_cost['pi_overselling'])) {
                                        $this->db->insert('costing', $item_cost);
                                    }
                                } else {
                                    if (!empty($item_cost) && (is_array($item_cost) || is_object($item_cost))) {
                                        foreach ($item_cost as $key => $ic) {
                                            $item_cost['order_item_id'] = $sale_item_id;
                                            $item_cost['order_id'] = $id;

                                            if (!isset($item_cost['pi_overselling'])) {
                                                $this->db->insert('costing', $item_cost);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $total_cgst = $total_cgst + $cgst;
                    $total_sgst = $total_sgst + $sgst;
                    $total_igst = $total_igst + $igst;
                }
                $this->db->update('delivery_challan', array('cgst' => $total_cgst, 'sgst' => $total_sgst, 'igst' => $total_igst), array('id' => $id));
            }
            if ((isset($data['sale_status']) ? $data['sale_status'] : (isset($data['challan_status']) ? $data['challan_status'] : '')) == 'completed') {
                $this->site->syncPurchaseItems($cost);
            }
            $this->site->syncSaleActionPayments($id, 'chalan');

            if ($syncQuantity) {
                $this->site->syncQuantity(NULL, NULL, NULL, NULL, NULL, NULL, $id);

                // Urbanpiper Stock Manage 
                if($this->Settings->pos_type == 'restaurant'){
                    $this->load->model("Urban_piper_model","UPM");
                    $orderWerehouse = $this->db->select('warehouse_id')->where(['id'=>$id])->get('delivery_challan')->row();
                    $productids = array();
                    foreach($items as $upproduct){
                        $productids[] = $upproduct['product_id'];
                    }
                    $this->UPM->Product_out_of_stock($productids, $orderWerehouse->warehouse_id);
                }

                $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            }

            $data['id'] = $id;
            $this->site->syncProductTransactionHistory('edit_challan', array('challan' => $data, 'items' => $items));
            return true;
        }
        return false;
    }

    public function resetChallanActions($id, $return_id = NULL, $check_return = NULL) {
        if ($sale = $this->getChallanByID($id)) {
            if ($check_return && ((isset($sale->sale_status) && $sale->sale_status == 'returned') || (isset($sale->challan_status) && $sale->challan_status == 'returned'))) {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
            }

            if ((isset($sale->challan_status) ? $sale->challan_status : (isset($sale->sale_status) ? $sale->sale_status : '')) == 'completed') {
                $items = $this->getAllChallanItems($id);
                foreach ($items as $item) {
                    if ($item->product_type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if ($combo_item->type == 'standard') {
                                $qty = ($item->quantity * $combo_item->qty);
                                $this->sales_model->updatePurchaseItem(NULL, $qty, NULL, $combo_item->id, $item->warehouse_id);
                            }
                        }
                    } else {
                        $option_id = isset($item->option_id) && !empty($item->option_id) ? $item->option_id : NULL;
                        $this->sales_model->updatePurchaseItem(NULL, $item->quantity, $item->id, $item->product_id, $item->warehouse_id, $option_id);
                    }
                }
                if ($sale->return_id || $return_id) {
                    $rid = $return_id ? $return_id : $sale->return_id;
                    $returned_items = $this->getAllChallanItems(FALSE, $rid);
                    foreach ($returned_items as $item) {

                        if ($item->product_type == 'combo') {
                            $combo_items = $this->site->getProductComboItems($item->product_id, $item->warehouse_id);
                            foreach ($combo_items as $combo_item) {
                                if ($combo_item->type == 'standard') {
                                    $qty = ($item->quantity * $combo_item->qty);
                                    $this->sales_model->updatePurchaseItem(NULL, $qty, NULL, $combo_item->id, $item->warehouse_id);
                                }
                            }
                        } else {
                            $option_id = isset($item->option_id) && !empty($item->option_id) ? $item->option_id : NULL;
                            $this->sales_model->updatePurchaseItem(NULL, $item->quantity, $item->id, $item->product_id, $item->warehouse_id, $option_id);
                        }
                    }
                }
                $this->site->syncQuantity(NULL, NULL, $items);
                $this->sma->update_award_points($sale->grand_total, $sale->customer_id, $sale->created_by, TRUE);

              // Urbanpiper Stock Manage 
               if($this->Settings->pos_type == 'restaurant'){
                    $this->load->model("Urban_piper_model","UPM");
                    $productids = array();
                    foreach($items as $upproduct){
                        $productids[] = $upproduct->product_id;
                    }
                    $this->UPM->Product_out_of_stock($productids, $sale->warehouse_id);
                }
                return $items;
            }
        }
    }
    public function getAllReturnChallanByID($id)
    {
        $q = $this->db->get_where('delivery_challan', array('return_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result_array()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllReturnChallanItems($challan_id)
    {
        $this->db->select('delivery_challan_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant, product_variants.price as variant_price, products.hsn_code as hsncode, delivery_challan.rounding as rounding, products.category_id as category_id, categories.name as category_name')
            ->join('products', 'products.id=delivery_challan_items.product_id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->join('product_variants', 'product_variants.id=delivery_challan_items.option_id', 'left')
            ->join('delivery_challan', 'delivery_challan.id=delivery_challan_items.challan_id', 'left')
            ->join('tax_rates', 'tax_rates.id=delivery_challan_items.tax_rate_id', 'left')
            ->group_by('delivery_challan_items.id')
            ->order_by('id', 'asc');
        
        $this->db->where('delivery_challan.return_id', $challan_id);
        $q = $this->db->get('delivery_challan_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllTaxItemsGroup($challan_id, $return_id = NULL) {
        $this->db->select("attr_code,attr_name,attr_per,sum(`tax_amount`) AS `amt`")
                 ->where_in('challan_id', array((int) $challan_id, (int) $return_id))
                 ->group_by('attr_code')
                 ->order_by('id', 'asc');
        $q = $this->db->get('delivery_challan_items_tax');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    /**
     * Billing/shipping address text for challan invoices (delivery_challan).
     *
     * @param object $challan delivery_challan row
     * @param object $customer Customer company row
     * @return array|null Keys: billing_address_text, shipping_address_text, show_receipt_addresses
     */
    public function getChallanReceiptAddresses($challan, $customer) {
        if (!$challan || !$customer) {
            return null;
        }

        $CI =& get_instance();
        $CI->load->model('site');

        $company_label = (!empty($customer->company) && $customer->company != '-') ? $customer->company : $customer->name;
        $company_fallback = trim(implode(', ', array_filter(array(
            $company_label,
            $customer->address,
            $customer->city,
            $customer->postal_code,
            $customer->state,
            $customer->country,
        ))));

        if (!empty($challan->billing_address_id)) {
            $billing_addr = $CI->site->getCustomerAddressById($challan->billing_address_id);
            $billing_address_text = !empty($billing_addr['label']) ? $billing_addr['label'] : '';
        } else {
            $billing_address_text = $company_fallback;
        }

        if (!empty($challan->shipping_address_id)) {
            $shipping_addr = $CI->site->getCustomerAddressById($challan->shipping_address_id);
            $shipping_address_text = !empty($shipping_addr['label']) ? $shipping_addr['label'] : '';
        } else {
            // Requirement: when shipping_address_id is empty, do not show shipping section.
            $shipping_address_text = '';
        }

        return array(
            'billing_address_text' => $billing_address_text,
            'shipping_address_text' => $shipping_address_text,
            'show_receipt_addresses' => true,
        );
    }
}
