<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$savings = 0;
//modifie name of product
foreach ($rows as $key => $val)    {
    $Color = '';
    if (!empty($val->shade_id)) {
        $options_color = $this->pos_model->getProductOptionsByShapeId($val->shade_id, $val->product_id, '2');
        $Color = $options_color[0]->name;
    }
    if(!empty($val->option_id)) {
        $item_note = (empty($val->note)) ? '' : ": " . $val->note;
        $rows[$key]->product_name = $val->product_name . ' (' . $val->variant . $item_note . ')';
        $rows[$key]->product_code = $val->product_code . '_' . $val->option_id;
        $rows[$key]->product_color = $Color;
    }//end if
    if($val->mrp >= $val->unit_price) {
        $savings += ($val->mrp - $val->unit_price ) * $val->quantity;
    }
}//end foreach

$resOutput = $this->sma->posBillTable($default_printer, $inv, $return_sale, $rows, $return_rows);
$resOutput = str_replace('uploads/thumbs/', 'uploads/', $resOutput);
$resOutput = str_replace('width:60px;height:60px;', 'width:75px;height:75px;', $resOutput);
$font_size = isset($default_printer->font_size) ? $default_printer->font_size : '11';

$hide_customer_phone = $this->sma->shouldHideCustomerPhone($customer);
$sms_code = md5('Reciept' . $inv->reference_no . $inv->id);
$sms_url  = base_url('reciept/send_sms_challan') . '?code=' . md5('Reciept' . $inv->reference_no . $inv->id);
if (!$hide_customer_phone && !empty($customer->phone)) {
    $sms_url .= '&phone=' . rawurlencode($customer->phone);
}
$DisSMSLink = isset($DisSMSLink) ? $DisSMSLink : '';
$print_redirect_url = isset($print_redirect_url) ? $print_redirect_url : site_url('sales/challans');

function product_name($name) {
    return character_limiter($name, (isset($pos_settings->char_per_line) ? ($pos_settings->char_per_line - 8) : 35));
}

$b_email = "";
if (isset($biller->email) && $biller->email != "") {
    $b_email = lang("email") . ": " . $biller->email . "<br>";
}

if ($this->sma->isA4NewFormatPrinter($default_printer)) {
    $receipt_context = 'challan';
    if (!isset($pos_settings) && isset($pos_settingss)) {
        $pos_settings = $pos_settingss;
    }
    include dirname(__FILE__) . '/view_a4_new_format.php';
    return;
}

if ($modal) {
    echo '<div class="modal-dialog  modal-lg  no-modal-header"><div class="modal-content"><div class="modal-body"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>';
} else {
    ?>
    <!doctype html>
    <html>
        <head>
            <meta charset="utf-8">
            <title><?= $page_title . " " . lang("no") . " " . $inv->id; ?></title>
            <base href="<?= base_url()?>"/>
            <script type="text/javascript">var site = { base_url: "<?= base_url(); ?>", site_url: "<?= site_url(); ?>" };</script>
            <meta http-equiv="cache-control" content="max-age=0"/>
            <meta http-equiv="cache-control" content="no-cache"/>
            <meta http-equiv="expires" content="0"/>
            <meta http-equiv="pragma" content="no-cache"/>
            <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
            <link rel="stylesheet" href="<?= $assets ?>styles/theme.css" type="text/css"/>
            <style type="text/css" media="all">
                body { color: #000; }
                #wrapper { max-width: <?php echo $default_printer->width ?>px; margin: 0 auto; padding-top: 20px; }
                .btn { border-radius: 0; margin-bottom: 5px; }
                h3 { margin: 5px 0; }
                /*#receipt-data table { font-size: <?php echo $font_size; ?>pt !important;}*/
                #receipt-data table { font-size: 9pt !important; margin-bottom: 5px;}
                .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td { padding: 1px 1px; }
                .well {
                    padding: 5px; margin-bottom: 3px;
                }
                @media print {
                    .no-print { display: none; }
                    #wrapper { max-width: <?php echo $default_printer->width ?>px; width: 100%; min-width: 250px; margin: 0 auto; }
                    .no-border { border: none !important; }
                    .border-bottom { border-bottom: 1px solid #ddd !important; }
                    #receipt-data{ font-size: <?php echo $font_size; ?>pt;}
                    #receipt-data table{ width:100%;padding:0 0%; border:none !important; margin-bottom: 0px!important; font-size: <?php echo $font_size; ?>pt !important;}
                    table tfoot{display:table-row-group;}
                    .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td { padding: 1px 2px; }
                .well {
                    padding: 5px; margin-bottom: 3px;
                }
                }
            </style>
        </head>

        <body>
            <?php
        }
        $inv->customer = $customer->name;
        ?>
        <div id="wrapper">
            <div id="receiptData">
                <div class="no-print">
                    <?php if ($message) { ?>
                        <div class="alert alert-success">
                            <?= is_array($message) ? print_r($message, true) : $message; ?>
                        </div>
                    <?php }
                    ?>
                </div>
                <div id="receipt-data" style="font-size: <?php echo $font_size; ?>pt !important">

                    <div class="text-center">
                        <?php if (isset($default_printer->show_invoice_logo) && $default_printer->show_invoice_logo == 1 && !empty($biller->logo)): ?>
                            <img src="<?= base_url('assets/mdata/'.$Customer_assets.'/uploads/logos/' . $biller->logo); ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                        <?php endif; ?>
                        <h3 style="text-transform:uppercase; margin-bottom: 0px;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h3>
                        <?php
                        echo "<p style='margin: 0 0 5px;'>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country .'. '.
                         lang("tel") . ":&nbsp;" . $biller->phone . ",&nbsp;" . $b_email;
                        if ($inv->GstSale):
                            echo lang("gstn_no") . ":&nbsp;" . $biller->gstn_no ;
                        endif;
                        if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                            echo ", " .$pos_settings->cf_title1 . ":&nbsp;" . $pos_settings->cf_value1 ;
                        }
                        if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                            echo  ", " . $pos_settings->cf_title2 . ":&nbsp;" . $pos_settings->cf_value2 ;
                        }
                        if (isset($default_printer->show_order_cf) && $default_printer->show_order_cf == 1) {
                            if (!empty($biller->cf1) && $biller->cf1 != "-") { echo "<br>" . lang("bcf1") . ":&nbsp;" . $biller->cf1; }
                            if (!empty($biller->cf2) && $biller->cf2 != "-") { echo "<br>" . lang("bcf2") . ":&nbsp;" . $biller->cf2; }
                            if (!empty($biller->cf3) && $biller->cf3 != "-") { echo "<br>" . lang("bcf3") . ":&nbsp;" . $biller->cf3; }
                            if (!empty($biller->cf4) && $biller->cf4 != "-") { echo "<br>" . lang("bcf4") . ":&nbsp;" . $biller->cf4; }
                            if (!empty($biller->cf5) && $biller->cf5 != "-") { echo "<br>" . lang("bcf5") . ":&nbsp;" . $biller->cf5; }
                            if (!empty($biller->cf6) && $biller->cf6 != "-") { echo "<br>" . lang("bcf6") . ":&nbsp;" . $biller->cf6; }
                        }
                        echo '</p>';
                        ?>
                    </div>  
                        <?php
                        if ($Settings->invoice_view == 1) {
                            ?>
                            <div class="col-sm-12 text-center">
                                <h5 style="font-weight:bold; margin: 0px;"><?php // $this->Settings->order_receipt_label ? $this->Settings->order_receipt_label : 'Delivery Challan Order' ?> <?= lang('delivery_challan_order'); ?> </h5>
                            </div>
                    <hr/>
                            <?php
                        }
                        
                        if (!empty($inv->return_sale_ref)) {
                            echo '<p>' . lang("return_ref") . ':&nbsp;' . $inv->return_sale_ref;
                            if ($inv->return_id) {
                                echo ' <a data-target="#myModal2" data-toggle="modal" href="' . site_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a>';
                            } 
                            echo '</p>';                            
                        }//end if
                        ?>
                         <?php 
                            if($default_printer->sales_person && $inv->seller){ ?>
                              <br/>
                                <div class="pull-right"><strong><?php echo lang("Sales Person") ?></strong> : <?php echo $inv->seller; ?></div>
                            <?php } ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="pull-right"><?php echo '<b>'.lang("date") . "</b>: " . $this->sma->hrld($inv->date) ;?></div>
                            <div class="pull-left"><b><?php // $this->Settings->order_receipt_label ? $this->Settings->order_receipt_label : 'Challan ' ?><?= lang("challan_no") ? lang("challan_no") : 'Challan' ?></b>: <?= $inv->challan_no; ?>
                                <?php if($pos_settingss->display_token==1){ if (isset($default_printer->show_kot_tokan) && $default_printer->show_kot_tokan == 1){ echo  ($inv->kot_tokan)?'<br/><b>'.lang("Token No.") . "</b>:&nbsp" . $inv->kot_tokan:'';} } ?> 
                            </div>
                        </div>
                    </div> 
                   
                    <div class="row">
                        <div class="col-sm-12">
                        <?php
                            if ($default_printer->show_order_type) {  
                                if (!empty($inv->order_type)) {
                                    $_otl_pt = isset($Settings->pos_type) ? strtolower(trim((string) $Settings->pos_type)) : '';
                                    $_otl_lbl = in_array($_otl_pt, array('restaurant', 'cafe', 'bakery'), true) ? lang('Order Type') : lang('Sale Source');
                                    echo '<div class="pull-left"><b>' . $_otl_lbl . '</b>:&nbsp;' . $inv->order_type . '</div>';
                                }
                            }
                        ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12">    
                    <?php
                        if (!empty($show_receipt_addresses)) {
                            echo '<b>' . lang("customer") . "</b>: " . $inv->customer;
                            if (!$hide_customer_phone && !empty($customer->phone)) {
                                echo ", <b>Ph</b>:&nbsp;" . $customer->phone . "<br>";
                            }
                            $this->load->view($this->theme . 'sales/partials/receipt_addresses', array(
                                'billing_address_text' => isset($billing_address_text) ? $billing_address_text : '',
                                'shipping_address_text' => isset($shipping_address_text) ? $shipping_address_text : '',
                            ));
                        } else {
                            echo '<b>'.lang("customer") . "</b>: " . $inv->customer ;
                            if (!$hide_customer_phone && !empty($customer->phone)) {
                                echo ", <b>Ph</b>:&nbsp;" . $customer->phone;
                            }
                            if (isset($default_printer->show_customer_info) && $default_printer->show_customer_info == 1){
                                if(!empty($customer->email)) {
                                    echo ", <b>Email</b>:&nbsp;" . $customer->email . "<br>";
                                }
                                if(!empty($customer->address)) {
                                    echo '<b>'.lang("address") . "</b>:&nbsp;" . $customer->address ;
                                }
                                if(!empty($customer->city)) {
                                    echo ", &nbsp;" . $customer->city ;
                                }
                                if(!empty($customer->postal_code)) {
                                    echo " - " . $customer->postal_code ;
                                }
                                
                                if(!empty($customer->state)) {
                                    echo ",  " . $customer->state ;
                                }
                                
                                if ($customer->cf1) {
                                    echo "<br/><b>Pan&nbsp;No:&nbsp;</b>" . $customer->cf1;
                                }
                                if (isset($default_printer->show_tin) && $default_printer->show_tin == 1) {
                                    if (isset($default_printer->show_customer_info) && $default_printer->show_customer_info == 1 && !empty($customer->pan_card)) {
                                        echo ", <b>". lang("Pan Card") . "</b>:&nbsp;" . $customer->pan_card;
                                    }
                                
                                    if ($inv->GstSale && !empty($customer->gstn_no)){
                                        echo ", <b>". lang("gstn_no") . "</b>:&nbsp;" . $customer->gstn_no;                                    
                                    }
                                    elseif (!empty($customer->vat_no)){
                                        echo ", <b>".lang('vat_no') . "</b>:&nbsp;" . $customer->vat_no ;
                                    }
                                    if ($customer->cf2) {
                                        echo ", <b>State&nbsp;Code</b>:&nbsp;" . $customer->cf2 ;
                                    }
                                }
                            }//end if.
                        }
                        ?>
                        </div>
                     </div>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="pull-left"><b><?php echo lang("reference_no") . "</b>: " . $inv->reference_no;?></div>
                            <div class="pull-right"><?php echo ($inv->staff_note && $show_kot) ? $this->sma->decode_html($inv->staff_note) : ''; ?></div>
                        </div>
                    </div> 
                    <div style="clear:both;"></div> 
                        <?php
                        $r = 1;
                        $category = 0;
                        $tax_summary = array();
                        foreach ($rows as $row) {
                             if($row->tax_code == ''){
                               $row->tax_code = '0GST';
                            } 
                            if (isset($tax_summary[$row->tax_code])) {
                                $tax_summary[$row->tax_code]['items'] += $row->quantity;
                                $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                                $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price);
                            } else {
                                $tax_summary[$row->tax_code]['items'] = $row->quantity;
                                $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                                $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price);
                                $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                                $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                                $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                                $tax_summary[$row->tax_code]['tax_rate_id'] = $row->tax_rate_id;
                            }
                        }
                        if ($return_rows) {
                            foreach ($return_rows as $row) {
                                if($row->tax_code == ''){
                                  $row->tax_code = '0GST';
                                } 
                                if (isset($tax_summary[$row->tax_code])) {
                                    $tax_summary[$row->tax_code]['items'] += $row->quantity;
                                    $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                                    $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
                                } else {
                                    $tax_summary[$row->tax_code]['items'] = $row->quantity;
                                    $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                                    $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                                    $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                                    $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                                    $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                                    $tax_summary[$row->tax_code]['tax_rate_id'] = $row->tax_rate_id;
                                }
                                $r++;
                            }
                        }
                        ?>
                        <?php echo $resOutput; ?>
                        <?php
                        
                        if ($payments) {

                            echo '<div style="margin-top:-4px;" ><table class="table" style="margin-bottom:0px;" ><tbody>';

                            foreach ($payments as $payment) {
                                
                                //$rounding = giftcardBalance( $payment->sale_id );  
								$getrounding = $this->orders_model->orderRounding($payment->order_id);   
                                $rounding = $getrounding->rounding;
                                echo '<tr>';
                                    
                                if ($payment->paid_by == 'cash' || $payment->paid_by == 'deposit' || $payment->paid_by == 'Due Payment') {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    if($payment->paid_by == 'deposit'){
                                        $deposit = depositeBalance($customer->id);
                                        // echo '<td align="right">' . lang("Deposit Balance") . ':&nbsp;' . ($deposit > 0 ? $deposit : 0) . '</td>';
                                        echo '<td align="right">' . lang("Deposit Balance") . ':&nbsp;' . ($payment->cc_holder > 0 ? $payment->cc_holder : 0) . '</td>';
                                    }
                                    if ($payments[count($payments) - 1]->paid_by == 'cash') {
                                       // echo '<td align="right">' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                       
                                       echo '<td align="right">' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;'). $this->sma->formatMoney($payment->amount  - ($inv->grand_total + $rounding))  . '</td>';
                                    }
                                } elseif (( $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    echo '<td align="center">' . lang("no") . ':&nbsp;' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                                    echo '<td align="right">' . lang("name") . ':&nbsp;' . $payment->cc_holder . '</td>';
                                } elseif (( $payment->paid_by == 'CC' || $payment->paid_by == 'DC')) {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td align="center">Transaction No : ' . $payment->transaction_id . '</td>';
                                    echo '<td align="right">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                } elseif ($payment->paid_by == 'instomojo' && $payment->transaction_id) {
                                    echo '<td>' . lang("paid_by") . ': Instomojo</td>';
                                    echo '<td>Payment ID: ' . $payment->transaction_id . '</td>';
                                    echo '<td>' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                   // echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                                   echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid  - ($inv->grand_total + $rounding)) : 0) . '</td>';

                                } elseif ($payment->paid_by == 'ccavenue' && $payment->transaction_id) {
                                    echo '<td>' . lang("paid_by") . ': CCavenue</td>';
                                    echo '<td align="center">Payment ID: ' . $payment->transaction_id . '</td>';
                                    echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    //echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                                    
                                   echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid  - ($inv->grand_total + $rounding)) : 0) . '</td>';

                                } elseif ($payment->paid_by == 'paytm' && $payment->transaction_id) {
                                    echo '<td>' . lang("paid_by") . ': Paytm</td>';
                                    echo '<td align="center">Payment ID: ' . $payment->transaction_id . '</td>';
                                    echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                   // echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                                   echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid  - ($inv->grand_total + $rounding)) : 0) . '</td>';

                                } elseif ($payment->paid_by == 'paynear' && $payment->transaction_id) {
                                    echo '<td>' . lang("paid_by") . ': Paynear</td>';
                                    echo '<td align="center">Payment ID: ' . $payment->transaction_id . '</td>';
                                    echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                   // echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                                   
                                   echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid  - ($inv->grand_total + $rounding)) : 0) . '</td>';

                                } elseif ($payment->paid_by == 'payumoney' && $payment->transaction_id) {
                                    echo '<td>' . lang("paid_by") . ': Payumoney</td>';
                                    echo '<td>Payment ID: ' . $payment->transaction_id . '</td>';
                                    echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                   // echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                                   echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid  - ($inv->grand_total + $rounding)) : 0) . '</td>';

                                   
                                } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td align="center">' . lang("cheque_no") . ':&nbsp;' . $payment->cheque_no . '</td>';
                                    echo '<td align="right">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td align="center">' . lang("no") . ':&nbsp;' . $payment->cc_no . '</td>';
                                    echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';

                                    

                                     if($payment->paid_by == 'gift_card'){
                                        
                                        $giftcard = giftcardBalance($payment->cc_no);
                                    } else { 
                                    echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                                    }

                                } elseif ($payment->paid_by == 'Tamara' && $payment->transaction_id) {
                                    echo '<td>' . lang("paid_by") . ': Tamara</td>';
                                    echo '<td align="center">Transaction ID: ' . $payment->transaction_id . '</td>';
                                    echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    echo '<td align="right">' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                } elseif ($payment->paid_by == 'Tabby' && $payment->transaction_id) {
                                    echo '<td>' . lang("paid_by") . ': Tabby</td>';
                                    echo '<td align="center">Transaction ID: ' . $payment->transaction_id . '</td>';
                                    echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    echo '<td align="right">' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                } elseif ($payment->paid_by == 'Stripe_PM' && $payment->transaction_id) {
                                    echo '<td>' . lang("paid_by") . ': Stripe</td>';
                                    echo '<td align="center">Transaction ID: ' . $payment->transaction_id . '</td>';
                                    echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid  - ($inv->grand_total + $rounding)) : 0) . '</td>';
                                } elseif ($payment->paid_by && $payment->amount) {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . ((strpos($payment->paid_by, 'other_') === 0) ? ucwords(str_replace('_', ' ', substr($payment->paid_by, 6))) : (lang($payment->paid_by) ? lang($payment->paid_by) : $payment->paid_by)) . '</td>';
                                    echo '<td align="center">Transaction No : ' . $payment->transaction_id . '</td>';
                                    echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    echo $payment->note ? '<td align="right">' . lang("payment_mode") . ':&nbsp;' . $payment->note . '</td>' : '';
                                }
                                
                                echo '</tr>';
                            }
                            echo '</tbody></table></div>';
                             if ($default_printer->show_saving_amount) {
                            echo '<h6 style="text-align:center;margin:0; text-transform: uppercase;"><b>Total Savings: ' . $this->sma->formatMoney($savings,2).'</b></h6>';
                            } 
                        }
                        
                        if ($return_payments) {
                            echo '<div><strong>' . lang('return_payments') . '</strong><table class="table table-striped table-condensed" style="margin-bottom:0px;"><tbody>';
                            foreach ($return_payments as $payment) {
                                $payment->amount = (0 - $payment->amount);
                                echo '<tr>';
                                // if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                                 if ($payment->paid_by == 'cash') {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    echo '<td>' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                    
                                }elseif ($payment->paid_by == 'deposit') {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
//                                     echo '<td>' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                    echo '<td>' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->cc_holder) . '</td>';
                                } elseif (( $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    echo '<td>' . lang("no") . ':&nbsp;' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                                    echo '<td>' . lang("name") . ':&nbsp;' . $payment->cc_holder . '</td>';
                                } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'DC' ) && $payment->transaction_id) {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td>Transaction No: ' . $payment->transaction_id . '</td>';
                                    echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    echo '<td>' . lang("no") . ':&nbsp;' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                                    echo '<td>' . lang("name") . ':&nbsp;' . $payment->cc_holder . '</td>';
                                } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    echo '<td>' . lang("cheque_no") . ':&nbsp;' . $payment->cheque_no . '</td>';
                                } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                    echo '<td>' . lang("no") . ':&nbsp;' . $payment->cc_no . '</td>';
                                    echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    //echo '<td>' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                                    echo '<td>' . lang("balance") . ':&nbsp;' . ($payment->cc_holder > 0 ? $this->sma->formatMoney($payment->cc_holder) : 0) . '</td>';
                                } elseif ($payment->paid_by && $payment->amount) {
                                    echo '<td>' . lang("paid_by") . ':&nbsp;' . ((strpos($payment->paid_by, 'other_') === 0) ? ucwords(str_replace('_', ' ', substr($payment->paid_by, 6))) : (lang($payment->paid_by) ? lang($payment->paid_by) : $payment->paid_by)) . '</td>';
                                    echo '<td>Transaction No: ' . $payment->transaction_id . '</td>';
                                    echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                    echo $payment->note ? '</tr><td colspan="2">' . lang("payment_mode") . ':&nbsp;' . $payment->note . '</td>' : '';
                                }
                                echo '</tr>';
                            }
                            echo '</tbody></table></div>';
                        }

                        if ($Settings->invoice_view == 1 && $Settings->view_tax_classification_on_challan) {
                            $taxReport = $this->sma->getTaxChallanReport($inv->id, $Settings, false);
                            $surchargeReport = $this->sma->getSurchargeTaxChallanReport($inv->id, $Settings);
                            if ($taxReport === '' && $surchargeReport !== '') {
                                echo '<h4 class="tax_summary_head" style="font-size: 12px; text-align:center; font-weight:bold; margin:5px 0px;">Tax Summary</h4>';
                            }
                            echo $taxReport;
                            echo $surchargeReport;
                            echo $this->sma->getTaxChallanReportAttrTotals($inv->id, $Settings);
                        }
                        ?>
                         
                        <?php
                        if ($default_printer->show_offer_description) {
                           echo !empty($inv->offer_description)  ? '<p class="text-center">Offer Applied : '.$inv->offer_description . '</p>' : '';
                         }//close if 
                        ?>
                        <?= $inv->note ? '<p class="text-center">' . $this->sma->decode_html($inv->note) . '</p>' : ''; ?>
                        <?= $inv->staff_note ? '<p class="no-print"><strong>' . lang('staff_note') . ':</strong> ' . $this->sma->decode_html($inv->staff_note) . '</p>' : ''; ?>
                        <div class="">
                            <div class=" well-sm" style="<?= (($default_printer->footer_align =='center')?'':'float:'.$default_printer->footer_align.';    width: 69%;') ?>text-align:<?= (isset($default_printer->footer_align) && $default_printer->footer_align != '') ? $default_printer->footer_align : 'center' ?>;">
                                <?= $this->sma->decode_html($biller->invoice_footer); ?>
                            </div>
                            <?php
                            if($default_printer->footer_align !='center'){
                                if($default_printer->signature){ ?>
                                    <div class=" well-sm pull-right text-center" style="width: 30%;margin-top: 3em;">
                                        <div style="height:60px; border:1px solid;"></div>
                                        For <strong><?= (isset($inv->biller) && !empty($inv->biller)) ? $inv->biller : ((isset($biller->company) && $biller->company != '-') ? $biller->company : (isset($biller->name) ? $biller->name : $Settings->site_name)) ?></strong>
                                    </div>
                                <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    if ($default_printer->show_barcode_qrcode) {
                    ?>
                        <div class="order_barcodes">
                            <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                            <?= $this->sma->qrcode('link', urlencode(site_url('sales/challan_view/' . $inv->id)), 2); ?>
                        </div>
                    <?php }//close if 
                    ?>
                    <div style="clear:both;"></div>
                </div>
                <?php
                if ($modal) {
                    echo '</div></div></div></div>';
                } else {
                    ?>
                    <div id="buttons" style="padding-top:10px; text-transform:uppercase;" class="no-print">
                        <hr>
                        <?php if($message) { ?>
                            <div class="alert alert-success">
                                <?= is_array($message) ? print_r($message, true) : $message; ?>
                            </div>
                        <?php }
                        ?>

                        <?php if ($pos_settings->java_applet) { ?>
                            <span class="col-xs-12"><a class="btn btn-block btn-primary" onClick="printReceipt()"><?= lang("print"); ?></a></span>
                            <span class="col-xs-12"><a class="btn btn-block btn-info" type="button" onClick="openCashDrawer()">Open Cash Drawer</a></span>
                            <div style="clear:both;"></div>
                        <?php } else { ?>
                            <span class="pull-left col-xs-12">
                                <a href="javascript:window.print()" id="web_print" class="btn btn-block btn-primary"
                                   onClick="window.print();return false;"><?= lang("web_print"); ?></a>
                            </span>
                        <?php }
                        ?>
                        <span class="pull-left col-xs-12"><a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a></span>
                        <?php if (!empty($sms_url)) : ?>
                            <span class="pull-left col-xs-12">

                                <?php $DisSMSLink = (int) $sms_limit < 1 ? '<br><a href="http://elintom.in/login.php" style="color:#000" target="_blank">Please Login on merchant panel & Rechagre Now</a>' : ''; ?>

                                <?php if ($sms_limit > 0): ?>
                                    <a class="btn btn-block btn-info" href="javascript:void(0);" id="sms_bill">SMS</a>
                                <?php else: ?>
                                    <a class="btn btn-block btn-info" style="cursor:no-drop" href="javascript:void(0);" id="sms_bill_not"> SMS </a>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                        <span class="pull-left col-xs-12">
                            <a class="btn btn-block btn-info" href="javascript:void(0);" id="send_whatsapp_app">Whatsapp</a>
                        </span>
                        <span class="pull-left col-xs-12">
                            <a class="btn btn-block btn-warning"  href="<?= site_url('sales/challans/'); ?>"><?= lang("Back_to_list"); ?></a>
                        </span>
                        <?php if (!$pos_settings->java_applet) { ?>
                            <div style="clear:both;"></div>
                            <div class="col-xs-12" style="background:#F5F5F5; padding:10px;">
                                <p style="font-weight:bold;"> Please don't forget to disable the header and footer in browser print settings.</p>
                                <p style="text-transform: capitalize;"><strong>FF:</strong> File &gt; Print Setup &gt; Margin &amp;
                                    Header/Footer Make all --blank--</p>
                                <p style="text-transform: capitalize;"><strong>chrome:</strong> Menu &gt; Print &gt; Disable Header/Footer
                                    in Option &amp; Set Margins to None</p>
                            </div>
                        <?php }
                        ?>
                        <div style="clear:both;"></div>
                        <div class="col-xs-12" style="background:#F5F5F5; padding:10px;">
                            <div class="sms_note blue">(Note : Available SMS limit <?php print((int) $sms_limit) ?> <?php echo $DisSMSLink; ?>)</div>
                        </div> 
                        <div style="clear:both;"></div>
                    </div>
                </div>
                <canvas id="hidden_screenshot" style="display:none;">
                </canvas>
                <div class="canvas_con" style="display:none;"></div>
                <script type="text/javascript" src="<?= $assets ?>pos/js/jquery-1.7.2.min.js"></script>
                <script type="text/javascript">
                    if (typeof site === 'undefined') {
                        var site = { base_url: "<?= base_url(); ?>", site_url: "<?= site_url(); ?>" };
                    }
                </script>
                <script type="text/javascript" src="<?= $assets ?>js/whatsapp.js?v=challan1"></script>
                <script type="text/javascript">
                    const customerPhone = "<?= isset($customer->phone) ? htmlspecialchars($customer->phone, ENT_QUOTES, 'UTF-8') : '' ?>";
                    const customerPhoneSystemGenerated = <?= $hide_customer_phone ? 'true' : 'false' ?>;
                    const invoice_auto_sms = "<?= isset($pos_settings->invoice_auto_sms) ? $pos_settings->invoice_auto_sms : '' ?>";
                    const whatsapp_service = "<?= isset($Settings->whatsapp_service) ? $Settings->whatsapp_service : '' ?>";
                    const countryCode = "<?= isset($country_code) ? htmlspecialchars($country_code, ENT_QUOTES, 'UTF-8') : '' ?>";
                    const phoneDigits = <?= isset($phone_digits) ? (int) $phone_digits : 10 ?>;
                    const receiptCode = "<?= $sms_code ?>";
                    window.receiptType = "challan";
                </script>
                <?php if (!empty($sms_url)) : ?>
                <script>
                    $('#sms_bill').click(function () {
                        var phone = prompt("<?= lang("phone"); ?>", "<?= $hide_customer_phone ? '' : htmlspecialchars($customer->phone, ENT_QUOTES, 'UTF-8'); ?>");
                        var code = '<?php echo $sms_code ?>';
                        if (phone != null) {
                            $.ajax({
                                type: "GET",
                                url: "<?= site_url('reciept/send_sms_challan') ?>",
                                data: {phone: phone, code: code},
                                dataType: "json",
                                success: function (data) {
                                    alert(data['msg']);
                                },
                                error: function () {
                                    alert('<?= lang('ajax_request_failed'); ?>');
                                    return false;
                                }
                            });
                        }
                        return false;
                    });
                </script>
                <?php endif; ?>
                
             
                <?php
                if ($pos_settings->java_applet) {

                    function drawLine($char_per_line) {
                        $size = $char_per_line;
                        $new = '';
                        for ($i = 1; $i < $size; $i++) {
                            $new .= '-';
                        }
                        $new .= ' ';
                        return $new;
                    }

                    function printLine($str, $sep = ":", $space = null, $char_per_line) {
                        $size = $space ? $space : $char_per_line;
                        $lenght = strlen($str);
                        list($first, $second) = explode(":", $str, 2);
                        $new = $first . ($sep == ":" ? $sep : '');
                        for ($i = 1; $i < ($size - $lenght); $i++) {
                            $new .= ' ';
                        }
                        $new .= ($sep != ":" ? $sep : '') . $second;
                        return $new;
                    }

                    function printText($text, $char_per_line) {
                        $size = $char_per_line;
                        $new = wordwrap($text, $size, "\\n");
                        return $new;
                    }

                    function taxLine($name, $code, $qty, $amt, $tax, $char_per_line) {
                        return printLine(printLine(printLine(printLine($name . ':' . $code, '', 18, $char_per_line) . ':' . $qty, '', 25, $char_per_line) . ':' . $amt, '', 35, $char_per_line) . ':' . $tax, ' ', $char_per_line);
                    }
                    ?>
                    
                    <script type="text/javascript" src="<?= $assets ?>pos/qz/js/deployJava.js"></script>
                    <script type="text/javascript" src="<?= $assets ?>pos/qz/qz-functions.js"></script>
                    <script type="text/javascript">
                                       deployQZ('themes/<?= $Settings->theme ?>/assets/pos/qz/qz-print.jar', '<?= $assets ?>pos/qz/qz-print_jnlp.jnlp');
                                       usePrinter("<?= $pos_settings->receipt_printer; ?>");
        <?php /* $image = $this->sma->save_barcode($inv->reference_no); */ ?>
               function printReceipt()              {
                                           //var barcode = 'data:image/png;base64,<?php /* echo $image; */ ?>';
                                           receipt = "";
                                           receipt += chr(27) + chr(69) + "\r" + chr(27) + "\x61" + "\x31\r";
                                           receipt += "<?= $biller->company != '-' ? $biller->company : $biller->name; ?>" + "\n";
                                           receipt += " \x1B\x45\x0A\r ";
                                           receipt += "<?= $biller->address . " " . $biller->city . " " . $biller->country; ?>" + "\n";
                                           receipt += "<?= $biller->phone; ?>" + "\n";

                                            <?php if ($biller->email != "") { ?>
                                                receipt += "<?= $biller->email; ?>" + "\n";
                                            <?php } ?>

                                            receipt += "<?php
                                            if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                                                echo printLine($pos_settings->cf_title1 . ": " . $pos_settings->cf_value1, null, null, $pos_settings->char_per_line);
                                            }
                                            ?>" + "\n";
                                            receipt += "<?php
                                            if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                                                echo printLine($pos_settings->cf_title2 . ": " . $pos_settings->cf_value2, null, null, $pos_settings->char_per_line);
                                            }
                                            ?>" + "\n";
                                            receipt += "<?= drawLine($pos_settings->char_per_line); ?>\r\n";
                                            receipt += "<?php
                                            if ($Settings->invoice_view == 1) {
                                                echo lang('tax_invoice');
                                            }
                                            ?>\r\n";
                                            receipt += "<?php
                                            if ($Settings->invoice_view == 1) {
                                                echo drawLine($pos_settings->char_per_line);
                                            }
                                            ?>\r\n";
                                                    
                                           receipt += "\x1B\x61\x30";
                                           receipt += "<?= printLine(lang("reference_no") . ": " . $inv->reference_no, null, null, $pos_settings->char_per_line) ?>" + "\n";
                                           receipt += "<?= printLine(lang("sales_person") . ": " . $biller->name, null, null, $pos_settings->char_per_line); ?>" + "\n";
                                           receipt += "<?= printLine(lang("customer") . ": " . $inv->customer, null, null, $pos_settings->char_per_line); ?>" + "\n";
                                           receipt += "<?= printLine(lang("date") . ": " . date($dateFormats['php_ldate'], strtotime($inv->date)), null, null, $pos_settings->char_per_line) ?>" + "\n\n";
                                           receipt += "<?php
        $r = 1;
        foreach ($rows as $row):
            ?>";
                                               receipt += "<?= "#" . $r . " "; ?>";
                                               receipt += "<?= printLine(product_name(addslashes($row->product_name)) . ($row->variant ? ' (' . $row->variant . ')' : '') . ":" . $row->tax_code, '*', null, $pos_settings->char_per_line); ?>" + "\n" ;
                                               receipt += "<?= printLine($this->sma->formatQuantity($row->quantity) . "x" . $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)) . ":  " . $this->sma->formatMoney($row->subtotal), ' ', null, $pos_settings->char_per_line) . ""; ?>" + "\n";
                                               receipt += "<?php
            $r++;
        endforeach;
        ?>";
        <?php if ($return_rows) { ?>
                    receipt += "\n" + "<?= lang('returned_items'); ?>" + "\n";
            <?php foreach ($return_rows as $row): ?>
                        receipt += "<?= "#" . $r . " "; ?>";
                        receipt += "<?= printLine(product_name(addslashes($row->product_name)) . ($row->variant ? ' (' . $row->variant . ')' : '') . ":" . $row->tax_code, '*', null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine($this->sma->formatQuantity($row->quantity) . "x" . $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)) . ":  " . $this->sma->formatMoney($row->subtotal), ' ', null, $pos_settings->char_per_line) . ""; ?>" + "\n";
                <?php
                $r++;
            endforeach;
        }
        ?>
            receipt += "\x1B\x61\x31";
            receipt += "<?= drawLine($pos_settings->char_per_line); ?>\r\n";
            receipt += "\x1B\x61\x30";
            receipt += "<?= printLine(lang("total") . ": " . $this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)), null, null, $pos_settings->char_per_line); ?>" + "\n";
        <?php if ($inv->order_tax != 0) { ?>
                receipt += "<?= printLine(lang("tax") . ": " . $this->sma->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax), null, null, $pos_settings->char_per_line); ?>" + "\n";
        <?php }
        ?>
        <?php if ($inv->total_discount != 0) { ?>
                receipt += "<?= printLine(lang("discount") . ": (" . $this->sma->formatMoney($return_sale ? ($inv->product_discount + $return_sale->product_discount) : $inv->product_discount) . ") " . $this->sma->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount), null, null, $pos_settings->char_per_line); ?>" + "\n";
        <?php }
        ?>
        <?php if ($pos_settings->rounding) { ?>
                receipt += "<?= printLine(lang("rounding") . ": " . $inv->rounding, null, null, $pos_settings->char_per_line); ?>" + "\n";
                receipt += "<?= printLine(lang("grand_total") . ": " . $this->sma->formatMoney($return_sale ? ($this->sma->roundMoney($inv->grand_total + $inv->rounding) + $return_sale->grand_total) : $this->sma->roundMoney($inv->grand_total + $inv->rounding)), null, null, $pos_settings->char_per_line); ?>" + "\n";
        <?php } else { ?>
                receipt += "<?= printLine(lang("grand_total") . ": " . $this->sma->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total), null, null, $pos_settings->char_per_line); ?>" + "\n";
        <?php }
        ?>
        <?php if ($inv->paid < $inv->grand_total) { ?>
                receipt += "<?= printLine(lang("paid_amount") . ": " . $this->sma->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid), null, null, $pos_settings->char_per_line); ?>" + "\n";
                receipt += "<?= printLine(lang("due_amount") . ": " . $this->sma->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding) + $return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)), null, null, $pos_settings->char_per_line); ?>" + "\n\n";
        <?php }
        ?>
        <?php if ($payments) { ?>
                receipt += "\n" + "<?= printText(lang("payments"), $pos_settings->char_per_line); ?>" + "\n";
            <?php
            foreach ($payments as $payment) {
                if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                    ?>
                        receipt += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("change") . ": " . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0), null, null, $pos_settings->char_per_line); ?>" + "\n";
                <?php } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) { ?>
                        receipt += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("card_no") . ": xxxx xxxx xxxx " . substr($payment->cc_no, -4), null, null, $pos_settings->char_per_line); ?>" + "\n";
                <?php } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'DC' ) && $payment->transaction_id) { ?>
                        receipt += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine("Transaction No:" . $payment->transaction_id); ?>" + "\n";
                        receipt += "<?= printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("card_no") . ": xxxx xxxx xxxx " . substr($payment->cc_no, -4), null, null, $pos_settings->char_per_line); ?>" + "\n";

                <?php } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                    ?>
                        receipt += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("cheque_no") . ": " . $payment->cheque_no, null, null, $pos_settings->char_per_line); ?>" + "\n";
                <?php } elseif ($payment->paid_by && $payment->amount) { ?>
                        receipt += "<?= printLine(lang("paid_by") . ": " . ((strpos($payment->paid_by, 'other_') === 0) ? ucwords(str_replace('_', ' ', substr($payment->paid_by, 6))) : (lang($payment->paid_by) ? lang($payment->paid_by) : $payment->paid_by)), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine("Transaction No:" . $payment->transaction_id); ?>" + "\n";
                        receipt += "<?= printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->amount), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printText(lang("payment_mode") . ": " . $payment->note, $pos_settings->char_per_line); ?>" + "\n";
                <?php
                }
            }
        }
        if ($return_payments) {
        ?>
            receipt += "\n" + "<?= printText(lang("return_payments"), $pos_settings->char_per_line); ?>" + "\n";
            <?php
            foreach ($return_payments as $payment) {
                if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && ($payment->pos_paid || $return_sale)) {
                    ?>
                        receipt += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->amount), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("change") . ": " . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0), null, null, $pos_settings->char_per_line); ?>" + "\n";
                <?php } elseif (( $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) { ?>
                        receipt += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("card_no") . ": xxxx xxxx xxxx " . substr($payment->cc_no, -4), null, null, $pos_settings->char_per_line); ?>" + "\n";

                <?php } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'DC' ) && $payment->transaction_id) { ?>
                        receipt += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine("Transaction No:" . $payment->transaction_id); ?>" + "\n";
                        receipt += "<?= printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("card_no") . ": xxxx xxxx xxxx " . substr($payment->cc_no, -4), null, null, $pos_settings->char_per_line); ?>" + "\n";

                <?php } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                    ?>
                        receipt += "<?= printLine(lang("paid_by") . ": " . lang($payment->paid_by), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->pos_paid), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine(lang("cheque_no") . ": " . $payment->cheque_no, null, null, $pos_settings->char_per_line); ?>" + "\n";
                <?php } elseif ($payment->paid_by && $payment->amount) { ?>
                        receipt += "<?= printLine(lang("paid_by") . ": " . ((strpos($payment->paid_by, 'other_') === 0) ? ucwords(str_replace('_', ' ', substr($payment->paid_by, 6))) : (lang($payment->paid_by) ? lang($payment->paid_by) : $payment->paid_by)), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printLine("Transaction No:" . $payment->transaction_id); ?>" + "\n";
                        receipt += "<?= printLine(lang("amount") . ": " . $this->sma->formatMoney($payment->amount), null, null, $pos_settings->char_per_line); ?>" + "\n";
                        receipt += "<?= printText(lang("payment_mode") . ": " . $payment->note, $pos_settings->char_per_line); ?>" + "\n";
                <?php
                }
            }
        }
        if ($Settings->invoice_view == 1) {
            if (!empty($tax_summary)) {
                ?>
                    receipt += "\n" + "<?= lang('tax_summary'); ?>" + "\n";
                    receipt += "<?= taxLine(lang('name'), lang('code'), lang('qty'), lang('tax_excl'), lang('tax_amt'), $pos_settings->char_per_line); ?>" + "\n";
                    receipt += "<?php foreach ($tax_summary as $summary): ?>";
                    receipt += "<?= taxLine($summary['name'], $summary['code'], $this->sma->formatQuantity($summary['items']), $this->sma->formatMoney($summary['amt']), $this->sma->formatMoney($summary['tax']), $pos_settings->char_per_line); ?>" + "\n";
                    receipt += "<?php endforeach; ?>";
                    receipt += "<?= printLine(lang("total_tax_amount") . ":" . $this->sma->formatMoney($inv->product_tax), null, null, $pos_settings->char_per_line); ?>" + "\n";
                <?php
            }
        }
        ?>
                    receipt += "\x1B\x61\x31";
                    receipt += "\n" + "<?= $biller->invoice_footer ? printText(str_replace(array('\n', '\r'), ' ', $this->sma->decode_html($biller->invoice_footer)), $pos_settings->char_per_line) : '' ?>" + "\n";
                    receipt += "\x1B\x61\x30";
        <?php if (isset($pos_settings->cash_drawer_cose)) { ?>
                print(receipt, '', '<?= $pos_settings->cash_drawer_cose; ?>');
        <?php } else { ?>
                print(receipt, '', '');
        <?php }
        ?>

        }
    </script>
<?php }  ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#email').click(function (event) {
            event.preventDefault();
            var email = prompt("<?= lang("email_address"); ?>", "<?= $customer->email; ?>");

            if (email != null) {
                $.ajax({
                    type: "post",
                    url: "<?= site_url('sales/email_challan_receipt') ?>",
                    data: {<?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>", email: email, id: <?= $inv->id; ?>},
                    dataType: "json",
                    success: function (data) {
                        alert("Your receipt has been successfully sent by mail.");
                    },
                    error: function () {
                        alert('<?= lang('ajax_request_failed'); ?>');
                        return false;
                    }
                });
            } else {
               // alert("Please input your email.");
               // return false;
            }

        });
    });
    <?php
    /* ------ For checking Print/notPrint Button updated by SW 21/01/2017 (aligned with pos/view) --------------- */
    $_print = isset($_SESSION['print_type']) ? $_SESSION['print_type'] : '';
    $should_autoprint = !$pos_settings->java_applet && (
        (isset($_SESSION['print_type']) && $_SESSION['print_type'] === 'print')
        || !empty($auto_print_receipt)
    );
    if ($should_autoprint):
        unset($_SESSION['print_type']);
        ?>
        $(window).load(function () {
            window.print();
        });
    <?php endif ?>
</script>

        <?php if ($_print !== 'notprint_notredirect' && $_print !== ''): ?>
                <?php unset($_SESSION['Sales']); ?>
                <script>
                    jQuery(function () {
                        window.__initializeChallanRedirect = window.__initializeChallanRedirect || function (redirectUrl) {
                            if (window.__challanRedirectHandlerInitialized) {
                                return;
                            }
                            window.__challanRedirectHandlerInitialized = true;

                            var redirectDone = false;
                            var redirectTimeoutId = null;

                            var executeRedirect = function () {
                                if (redirectDone) {
                                    return;
                                }
                                redirectDone = true;
                                if (redirectTimeoutId) {
                                    clearTimeout(redirectTimeoutId);
                                    redirectTimeoutId = null;
                                }
                                window.__androidChallanPrinting = false;
                                jQuery("[id*='back_to_pos']").trigger('click');
                                window.location.href = redirectUrl;
                                return false;
                            };

                            if (window.__androidChallanPrinting) {
                                var afterPrintHandler = function () {
                                    window.removeEventListener('afterprint', afterPrintHandler);
                                    executeRedirect();
                                };
                                window.addEventListener('afterprint', afterPrintHandler);

                                jQuery(window).one('focus', function () {
                                    if (window.__androidChallanPrinting) {
                                        setTimeout(executeRedirect, 100);
                                    }
                                });

                                redirectTimeoutId = setTimeout(executeRedirect, 15000);
                            } else {
                                setTimeout(executeRedirect, 500);
                            }
                        };

                        <?php if ($Settings->pos_type == 'restaurant'): ?>
                        window.__initializeChallanRedirect('<?= $print_redirect_url ?>');
                        <?php else: ?>
                        <?php if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')): ?>
                        window.__androidChallanPrinting = true;
                        setTimeout(function () {
                            var $printButton = jQuery("[id*='web_print']");
                            if ($printButton.length) {
                                $printButton.trigger('click');
                            } else if (window.print) {
                                window.print();
                            }
                        }, 200);
                        window.__initializeChallanRedirect('<?= $print_redirect_url ?>');
                        <?php else: ?>
                        window.__initializeChallanRedirect('<?= $print_redirect_url ?>');
                        <?php endif; ?>
                        <?php endif; ?>
                    });
                </script>
        <?php endif; ?>
        </body>
    </html>
<?php }
?>