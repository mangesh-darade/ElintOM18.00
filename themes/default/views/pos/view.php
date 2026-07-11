<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$savings = 0;
//modifie name of product

foreach ($rows as $key => $val) {
    $Color = '';
    if (!empty($val->shade_id)) {
        $options_color = $this->pos_model->getProductOptionsByShapeId($val->shade_id, $val->product_id, 2);
        $Color = $options_color[0]->name;
    }
    if (!empty($val->option_id)) {
        $item_note = (empty($val->note)) ? '' : ": " . $val->note;


        $variant = ($default_printer->show_product_size_color) ? '  (' . $val->variant . ' - ' . $Color . $item_note . ')' : '';

        $rows[$key]->product_name = $val->product_name . $variant;

        // $rows[$key]->product_name = $val->product_name . ' (' . $val->variant.' - '.$Color . $item_note . ')';
        $rows[$key]->product_code = $val->product_code . '_' . $val->option_id . '_' . $val->shade_id;

        $rows[$key]->product_size = $val->variant;
        $rows[$key]->product_color = $Color;
    }//end if
    if ($val->mrp >= $val->unit_price) {
        $savings += ($val->mrp - $val->unit_price ) * $val->quantity;
    }
}//end foreach
// return rows
if(is_array($return_rows)) {
    foreach ($return_rows as $key => $val) {
    $Color = '';
    if (!empty($val->shade_id)) {
        $options_color = $this->pos_model->getProductOptionsByShapeId($val->shade_id, $val->product_id, 2);
        $Color = $options_color[0]->name;
    }
    if (!empty($val->option_id)) {
        $item_note = (empty($val->note)) ? '' : ": " . $val->note;


        $variant = ($default_printer->show_product_size_color) ? '  (' . $val->variant . ' - ' . $Color . $item_note . ')' : '';

        $return_rows[$key]->product_name = $val->product_name . $variant;

        // $rows[$key]->product_name = $val->product_name . ' (' . $val->variant.' - '.$Color . $item_note . ')';
        $return_rows[$key]->product_code = $val->product_code . '_' . $val->option_id . '_' . $val->shade_id;

        $return_rows[$key]->product_size = $val->variant;
        $return_rows[$key]->product_color = $Color;
    }//end if
    if ($val->mrp >= $val->unit_price) {
        $savings += ($val->mrp - $val->unit_price ) * $val->quantity;
    }
}
}

$return_sale = isset($return_sale) && !empty($return_sale) ? $return_sale : [];

//print_r($rows);
//$resOutput = $this->sma->posBillTable($default_printer, $inv, $return_sale, $rows, $return_rows);
$resOutput = $this->sma->posBillTableCSI($default_printer, $inv, $return_sale, $rows, $return_rows, $salestax);

$_invoice_show_time = !isset($default_printer->invoice_display_time) || !empty($default_printer->invoice_display_time);
$_invoice_date_display = $this->sma->invoiceDisplayDate($inv->date, $_invoice_show_time);

$font_size = isset($default_printer->font_size) ? $default_printer->font_size : '11';

$hide_customer_phone = $this->sma->shouldHideCustomerPhone($customer);
$sms_code = md5('Reciept' . $inv->reference_no . $inv->id);
$sms_url = base_url('reciept/send_sms') . '?code=' . md5('Reciept' . $inv->reference_no . $inv->id);
if (!$hide_customer_phone && !empty($customer->phone)) {
    $sms_url .= '&phone=' . rawurlencode($customer->phone);
}

function product_name($name) {
    return character_limiter($name, (isset($pos_settings->char_per_line) ? ($pos_settings->char_per_line - 8) : 35));
}

$b_email = "";
if (isset($biller->email) && $biller->email != "") {
    $b_email = lang("email") . ": " . $biller->email . "<br>";
}

if ($this->sma->isA4NewFormatPrinter($default_printer)) {
    $receipt_context = 'sale';
    if (!isset($pos_settings) && isset($pos_settingss)) {
        $pos_settings = $pos_settingss;
    }
    include dirname(__FILE__) . '/../sales/view_a4_new_format.php';
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
            <base href="<?= base_url() ?>"/>
              <!---------------------------- WhatsApp integration --------------------------------------->
            <script type="text/javascript"> const site = { base_url: "<?= base_url(); ?>", site_url: "<?= site_url(); ?>" };</script>
              <!---------------------------- WhatsApp integration --------------------------------------->

            <meta http-equiv="cache-control" content="max-age=0"/>
            <meta http-equiv="cache-control" content="no-cache"/>
            <meta http-equiv="expires" content="0"/>
            <meta http-equiv="pragma" content="no-cache"/>
            <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
            <link rel="stylesheet" href="<?= $assets ?>styles/theme.css" type="text/css"/>
            <style type="text/css" media="all">
                body { color: #000; }
                #wrapper { max-width: <?php echo $default_printer->width ?>px; margin: 0 auto; padding-top: 20px; }
                @media (min-width: 1024px) and (max-width: 1366px) {
                #wrapper {
                    max-width: <?php echo $default_printer->width ?>px;
                    margin: 0 auto;
                    padding-top: 30px;
                }
                }

                /* Medium Laptops */
                @media (min-width: 1367px) and (max-width: 1600px) {
                #wrapper {
                    max-width: <?php echo $default_printer->width ?>px;
                    margin: 0 auto;
                    padding-top: 30px;
                }
                }

                /* Large Laptops */
                @media (min-width: 1601px) and (max-width: 1920px) {
                #wrapper {
                    max-width: <?php echo $default_printer->width ?>px;
                    margin: 0 auto;
                    padding-top: 30px!important; 
                }
                }
                .btn { border-radius: 0; margin-bottom: 5px; }
                h3 { margin: 5px 0; }
                /*#receipt-data table { font-size: <?php echo $font_size; ?>pt !important;}*/
                #receipt-data table { font-size: 9pt !important; margin-bottom: 5px;}
                .table>thead>tr>th, .table>thead,.table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td { padding: 1px 1px;border: 1px solid #000; }
                .well {
                    padding: 5px; margin-bottom: 3px;
                }
                @media print {
                    .table-bordered tr td, .table-bordered tr th{border: 1px solid #000 !important;}
  
                    .no-print { display: none; }
                    #wrapper { max-width: <?php echo $default_printer->width ?>px; width: 100%; min-width: 250px; margin: 0 auto; }
                    .no-border { border: none !important; }
                    .border-bottom { border-bottom: 1px solid #ddd !important; }
                    #receipt-data{ font-size: <?php echo $font_size; ?>pt;}
                    #receipt-data table{ width:100%;padding:0 0%; border:none !important; margin-bottom: 0px!important; font-size: <?php echo $font_size; ?>pt !important;}
                    table tfoot{display:table-row-group;}
                    .table>thead>tr>th,.table>thead, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td { padding: 1px 2px;border: 1px solid #000; }
                    .well {
                        padding: 5px; margin-bottom: 3px;
                    }
                }
                <?php if (isset($default_printer->show_invoice_lines) && $default_printer->show_invoice_lines == 0) { ?>.table>thead>tr>th,
                    .table>thead,
                    .table>tbody>tr>th,
                    .table>tfoot>tr>th,
                    .table>thead>tr>td,
                    .table>tbody>tr>td,
                    .table>tfoot>tr>td {
                        border: none !important;
                    }
                <?php } ?>
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
                <div id="receipt-data" style="font-size: <?php echo $font_size; ?>pt !important; margin-top: -15px;">


                    <div class="text-center">
                          
                        <?php 
                            switch($default_printer->logo_position){
                                case 'center': 
                                      if (isset($default_printer->show_invoice_logo) && $default_printer->show_invoice_logo == 1 && !empty($biller->logo)): 
                                       echo  '<img src="'. base_url('assets/mdata/'.$Customer_assets.'/uploads/logos/' . $biller->logo).'" alt="'.($biller->company != '-' ? $biller->company : $biller->name).'" style="padding-top:10px")/>';
                                      endif; 
                                     echo  '<h3 style="text-transform:uppercase; margin-bottom: 0px;">'.( $biller->company != '-' ? $biller->company : $biller->name).'</h3>';
                                
                                      echo '<div style="clear: both;"></div>';
                                      echo "<p style='margin: 0 0 5px; '>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country . '. ' .
                                        lang("tel") . ":&nbsp;" . $biller->phone . ",&nbsp;" . $b_email;
                                        if ($inv->GstSale && $biller->gstn_no):
                                            // echo lang("gstn_no") . ":&nbsp;" . $biller->gstn_no;
                                            echo lang($Settings->tax_label) . ":&nbsp;" . $biller->gstn_no;
                                        endif;
                                        if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                                            echo ", " . $pos_settings->cf_title1 . ":&nbsp;" . $pos_settings->cf_value1;
                                        }
                                        if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                                            echo ", " . $pos_settings->cf_title2 . ":&nbsp;" . $pos_settings->cf_value2;
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
                                break;
                                 
                                case 'left':
                                        echo '<table ><tr>';
                                            echo '<td>';
                                           if (isset($default_printer->show_invoice_logo) && $default_printer->show_invoice_logo == 1 && !empty($biller->logo)): 
                                                echo  '<img src="'. base_url('assets/mdata/'.$Customer_assets.'/uploads/logos/' . $biller->logo).'" alt="'.($biller->company != '-' ? $biller->company : $biller->name).'" >   &nbsp; ';
                                            endif; 
                                          
                                            echo '</td>';
                                            echo '<td>';
                                               echo '<h3 style="text-transform:uppercase; margin-bottom: 0px;  ">';
                                                echo  ( $biller->company != '-' ? $biller->company : $biller->name).'</h3>';
                                                    echo "<p style='margin: 0 0 5px; '>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country . '. ' .
                                                lang("tel") . ":&nbsp;" . $biller->phone . ",&nbsp;" . $b_email;
                                                if ($inv->GstSale && $biller->gstn_no):
                                                    // echo lang("gstn_no") . ":&nbsp;" . $biller->gstn_no;
                                                    echo lang($Settings->tax_label) . ":&nbsp;" . $biller->gstn_no;
                                                endif;
                                                if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                                                    echo ", " . $pos_settings->cf_title1 . ":&nbsp;" . $pos_settings->cf_value1;
                                                }
                                                if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                                                    echo ", " . $pos_settings->cf_title2 . ":&nbsp;" . $pos_settings->cf_value2;
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
                                                
                                             echo '</td>';
                                        echo '</tr></table>';
//                                      
                                    break;
                                
                                case 'right':
                                        echo '<table ><tr>';
                                             echo '<td>';
                                               echo '<h3 style="text-transform:uppercase; margin-bottom: 0px;  ">';
                                                echo  ( $biller->company != '-' ? $biller->company : $biller->name).'</h3>';
                                                    echo "<p style='margin: 0 0 5px; '>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country . '. ' .
                                                lang("tel") . ":&nbsp;" . $biller->phone . ",&nbsp;" . $b_email;
                                                if ($inv->GstSale && $biller->gstn_no):
                                                    // echo lang("gstn_no") . ":&nbsp;" . $biller->gstn_no;
                                                    echo lang($Settings->tax_label) . ":&nbsp;" . $biller->gstn_no;
                                                endif;
                                                if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                                                    echo ", " . $pos_settings->cf_title1 . ":&nbsp;" . $pos_settings->cf_value1;
                                                }
                                                if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                                                    echo ", " . $pos_settings->cf_title2 . ":&nbsp;" . $pos_settings->cf_value2;
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
                                                
                                             echo '</td>';
                                             echo '<td style="text-align: right;" >';
                                                if (isset($default_printer->show_invoice_logo) && $default_printer->show_invoice_logo == 1 && !empty($biller->logo)): 
                                                     echo  '<img src="'. base_url('assets/mdata/'.$Customer_assets.'/uploads/logos/' . $biller->logo).'" alt="'.($biller->company != '-' ? $biller->company : $biller->name).'" >   &nbsp; ';
                                                 endif;                                           
                                            echo '</td>';
                                        echo '</tr></table>';
                                    break;
                                    
                            }
                        ?>
                       
                  
                    </div>  
                    <?php
                    if ($Settings->invoice_view == 1 || $Settings->invoice_view == 2) {
                        ?>
                        <div class="col-sm-12 text-center fontmark">
                            <h6 style="font-weight:bold; margin: 0px;"><?= lang('tax_invoice'); ?></h6>
                        </div>
                        <?php
                    }

                    if (!empty($inv->return_sale_ref)) {
                        echo '<p class="fontmark"><b>' . lang("return_ref") . ':</b>&nbsp;' . $inv->return_sale_ref;
                        if ($inv->return_id) {
                            echo ' <a data-target="#myModal2" data-toggle="modal" href="' . site_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a>';
                        }
                        echo '</p>';
                    }//end if
                    ?>
                    <div class="row fontmark">
                        <div class="col-sm-12">
                            <div class="pull-right"><b><?php echo lang("date") ?></b>  :  <?php echo $_invoice_date_display; ?></div>
                            <div class="pull-left"><b><?php echo lang("Invoice No.") ?></b>:&nbsp <?php echo $inv->invoice_no; ?>

                                <?php
                                if ($pos_settingss->display_token == 1) {
                                    if (isset($default_printer->show_kot_tokan) && $default_printer->show_kot_tokan == 1) {
                                        if (!empty($inv->category_tokens)) {
                                            $category_tokens = json_decode($inv->category_tokens, true);
                                            if (!empty($category_tokens)) {
                                                $tokens = array_values($category_tokens);
                                                echo '<br/><b>' . lang("Token No.") . "</b>:&nbsp;" . implode(', ', $tokens);
                                            }
                                        } elseif (!empty($inv->kot_tokan)) {
                                            echo '<br/><b>' . lang("Token No.") . "</b>:&nbsp;" . $inv->kot_tokan;
                                        }
                                    }
                                }
                            ?>
                            </div>
                           <?php 
                            if($default_printer->sales_person && $inv->seller){ ?>
                              <br/>
                                <div class="pull-right"><strong><?php echo lang("Sales Person") ?></strong> : <?php echo $inv->seller; ?></div>
                            <?php } ?>  

                        </div>
                    </div> 

                 
                    <div class="row">
                        <div class="col-sm-12">
                            <?php if ($default_printer->sale_refe_no) { ?>
                                <div class="pull-left"><strong><?php echo lang("PO/Reference_no") ?></strong> : <?php echo $inv->reference_no; ?></div>
                            <?php } if ($default_printer->table_no) { ?>
                                <div class="pull-right"><?php echo ($inv->staff_note && $show_kot) ? $this->sma->decode_html($inv->staff_note) : ''; ?></div>
                            <?php } ?>
                                
                        </div>
                    </div> 
                    <div class="row">
                        <div class="col-sm-12">
                            <?php if ($default_printer->show_order_type && !empty($inv->order_type)) {
                                $_otl_pt = isset($Settings->pos_type) ? strtolower(trim((string) $Settings->pos_type)) : '';
                                $_otl_lbl = in_array($_otl_pt, array('restaurant', 'cafe', 'bakery'), true) ? lang('Order Type') : lang('Sale Source');
                                ?>
                                <div class="pull-left"><strong><?php echo $_otl_lbl; ?></strong> : <?php echo $inv->order_type; ?></div>
                            <?php } ?>
                        </div>
                    </div> 
                    <div class="row">
                        <?php if (isset($shipping_details) && $inv->eshop_sale) { ?>
                            <table style="width: 100%">
                                <tr>
                                    <td style="width: 50%; vertical-align: top;    padding-left: 14px;">
                                        <?php
                                        echo '<b>' . lang("Billing details ") . "</b><br/><b>Name</b> : " . $shipping_details->billing_name . '<br/>';
                                        if (!empty($shipping_details->billing_phone)) {
                                            echo " <b>Tel</b> :&nbsp;" . $shipping_details->billing_phone, ', ';
                                        }
                                        if (!empty($shipping_details->billing_email)) {
                                            echo "<b>Email</b> :&nbsp;" . $shipping_details->billing_email . "<br>";
                                        }
                                        if (!empty($shipping_details->billing_addr)) {
                                            echo "<b>Address</b> :&nbsp;" . $shipping_details->billing_addr;
                                        }
                                        ?>

                                    </td>
                                    <td style="width: 50%;  vertical-align: top;  padding-left: 14px;">
                                        <strong><?php echo $this->lang->line("Shipping Details"); ?></strong>
                                        <br/><span><b>Name</b> :<?= $shipping_details->shipping_name; ?></span>
                                        <address>
                                            <b>Tel</b> : <?= $shipping_details->shipping_phone ?>, 
                                            <b>Email</b> : <?= $shipping_details->shipping_email ?><br
                                                <b>Address</b> :<?= $shipping_details->shipping_addr ?><br/>


                                            <?php
                                            if ($shipping_details) {
                                                echo ' <b>Delivery Type</b> : ' . $shipping_details->shipping_method_name . '<br/>';
                                            }if ($shipping_details->deliver_later) {
                                                echo ' <b>Date</b> : ' . date('d-m-Y', strtotime($shipping_details->deliver_later)) . '<br/>';
                                            }if ($shipping_details->time_slotes) {
                                                echo '<b>Time</b> : ' . $shipping_details->time_slotes;
                                            }
                                            ?>    
                                        </address>
                                    </td>
                                </tr>
                            </table>
                        <?php } elseif (!empty($show_receipt_addresses)) { ?>
                            <div class="col-sm-12">
                                <?php
                                echo '<div><b>' . lang("customer") . '</b>:&nbsp;' . $inv->customer . '</div>';
                                if (!empty($default_printer->show_order_cf)) {
                                    foreach ($this->site->getCustomerCustomFieldsForDisplay($customer, $custome_fields) as $cf_row) {
                                        echo '<strong>' . htmlspecialchars($cf_row['label'], ENT_QUOTES, 'UTF-8') . ':</strong>&nbsp;'
                                            . htmlspecialchars($cf_row['value'], ENT_QUOTES, 'UTF-8') . '<br>';
                                    }
                                }
                                if (isset($default_printer->show_tin) && $default_printer->show_tin == 1) {
                                    if (isset($default_printer->show_customer_info) && $default_printer->show_customer_info == 1 && !empty($customer->pan_card)) {
                                        echo "<strong>" . lang("Pan Card") . ":</strong>&nbsp;" . $customer->pan_card . "<br>";
                                    }
                                    if ($inv->GstSale && !empty($customer->gstn_no)) {
                                        echo "<strong>" . lang($Settings->tax_label) . ":</strong>&nbsp;" . $customer->gstn_no . "<br>";
                                    } elseif (!empty($customer->vat_no)) {
                                        echo "<strong>" . lang($Settings->tax_label) . ":</strong>&nbsp;" . $customer->vat_no . "<br>";
                                    }
                                }
                                if (!$hide_customer_phone && !empty($customer->phone)) {
                                    echo " <b>Tel</b>:&nbsp;" . $customer->phone . "<br>";
                                }
                                $this->load->view($this->theme . 'sales/partials/receipt_addresses', array(
                                    'billing_address_text' => isset($billing_address_text) ? $billing_address_text : '',
                                    'shipping_address_text' => isset($shipping_address_text) ? $shipping_address_text : '',
                                ));
                                ?>
                            </div>
                        <?php } else { ?>
                            <div class="col-sm-12">
                                <?php 
                                    if ($default_printer->show_bill_no) {  
                                        if ($Settings->pos_type == 'restaurant' && !empty($inv->bill_no)) {
                                            echo "<b>Bill No</b>:&nbsp;" . $inv->bill_no;
                                        }
                                    }
                                ?>
                                
                                <?php
                                // echo "<b>" . lang("customer") . "</b>: " . $inv->customer . '<br/>';
                                echo '<div class=" "><b>' . lang("customer") . '</b>:&nbsp;' . $inv->customer . '</div>';
                                if (!$hide_customer_phone && !empty($customer->phone)) {
                                    echo " <b>Tel</b>:&nbsp;" . $customer->phone. " " . "<br>";
                                }
                                if (!empty($default_printer->show_order_cf)) {
                                    foreach ($this->site->getCustomerCustomFieldsForDisplay($customer, $custome_fields) as $cf_row) {
                                        echo '<strong>' . htmlspecialchars($cf_row['label'], ENT_QUOTES, 'UTF-8') . ':</strong>&nbsp;'
                                            . htmlspecialchars($cf_row['value'], ENT_QUOTES, 'UTF-8') . '<br>';
                                    }
                                }
                                if (isset($default_printer->show_customer_info) && $default_printer->show_customer_info == 1) {
                                    if (!empty($customer->email)) {
                                        echo " <b>Email</b>:&nbsp;" . $customer->email . "<br>";
                                    }
                                    if (!empty($customer->address)) {
                                        echo "<b>Address</b>:&nbsp;" . $customer->address. " ";
                                        if (!empty($customer->city) || !empty($customer->postal_code) || !empty($customer->state)) {
                                            echo "<br/>";
                                        }
                                    }
                                    if (!empty($customer->city)) {
                                        echo $customer->city. " ";
                                    }
                                    if (!empty($customer->postal_code)) {
                                        echo " - " . $customer->postal_code. " " ; 
                                    }
                                    // if (!empty($customer->city)) {
                                    //     echo ", &nbsp;" . $customer->city. " ";
                                    // }

                                    if (!empty($customer->state)) {
                                        echo ((!empty($customer->city) || !empty($customer->postal_code)) ? ",  " : "") . $customer->state. "<br>";
                                    }

                                    if (isset($default_printer->show_tin) && $default_printer->show_tin == 1) {
                                        if (isset($default_printer->show_customer_info) && $default_printer->show_customer_info == 1 && !empty($customer->pan_card)) {
                                            echo "<strong>" . lang("Pan Card") . ":</strong>&nbsp;" . $customer->pan_card . "<br>"; 
                                        }

                                        if ($inv->GstSale && !empty($customer->gstn_no)) {
                                            // echo ", " . lang("gstn_no") . ":&nbsp;" . $customer->gstn_no;
                                            echo "<strong>" . lang($Settings->tax_label) . ":</strong>&nbsp;" . $customer->gstn_no;
                                        } elseif (!empty($customer->vat_no)) {
                                            // echo ", " . lang('vat_no') . ":&nbsp;" . $customer->vat_no;
                                            echo "<strong>" . lang($Settings->tax_label) . ":</strong>&nbsp;" . $customer->vat_no;
                                        }
                                    }
                                }//end if.                        
                                ?>
                            </div>
                        <?php } ?>
                    </div>



                    <?php if (isset($default_printer->transporter) && $default_printer->transporter && $inv->order_no == '') { ?>
                        <strong>Transporter</strong><br/>

                        <table style="font-size: 13px !important;" cellpadding="10" cellspacing="5">

                            <tr>
                                <td>Transporter/Mode</td>
                                <td> &nbsp; : &nbsp; </td>
                                <td><?= ($inv->transporter_mode) ? $inv->transporter_mode : '' ?></td>
                            </tr>

                            <tr>
                                <td>L.R.No.</td>
                                <td> &nbsp; : &nbsp; </td>
                                <td><?= ($inv->LR_No) ? $inv->LR_No : '' ?></td>
                            </tr>

                            <tr>
                                <td>Total Parcels</td>
                                <td> &nbsp; : &nbsp; </td>
                                <td><?= ($inv->total_parcels) ? $inv->total_parcels : '' ?> </td>
                            </tr>

                            <tr>
                                <td>Place of Supply</td>
                                <td> &nbsp; : &nbsp; </td>
                                <td><?= ($inv->place_of_supply) ? $inv->place_of_supply : '' ?></td>
                            </tr>

                            <tr>
                                <td>Way Bill No</td>
                                <td> &nbsp; : &nbsp; </td>
                                <td><?= ($inv->bill_no) ? $inv->bill_no : '' ?></td>

                            </tr>  
                        </table>
                    <?php } ?>

                    <div style="clear:both;"></div> 
                    <?php
                    $r = 1;
                    $category = 0;
                    $tax_summary = array();
                    foreach ($rows as $row) {
                        if (isset($tax_summary[$row->tax_code])) {
                            $tax_summary[$row->tax_code]['items'] += $row->quantity;
                            $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                            $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price);
                        } else {
                            $tax_summary[$row->tax_code]['items'] = $row->quantity;
                            $tax_summary[$row->tax_code]['hsn_code'] = $row->hsn_code;
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
                    $discount_2 = 0;
                    if (isset($Settings->active_double_discounts) && $Settings->active_double_discounts) {
                        if (!empty($discountSummeryData)) {
                            foreach ($discountSummeryData as $key => $value) {
                                $discount_2 = $value[1]['rate'];
                            }
                            if ($discount_2 != 0) {
                                echo '<h4 class="tax_summary_head" style="font-size: 12px; text-align:center; font-weight:bold; margin:5px 0px;">Discount Summary</h4>';
                                echo $this->sma->discountSummery($discountSummeryData);
                            }//end if
                        }
                    }
                    $invoice_grand = $pos_settings->rounding ? $this->sma->roundMoney($inv->grand_total + $inv->rounding) : $inv->grand_total;
                    $is_multi_payment = !empty($payments) && count($payments) > 1;
                    if ($payments) {

                        echo '<div style="margin-top:-4px;" ><table class="table" style="margin-bottom:0px;" ><tbody>';

                        foreach ($payments as $payment) {

                            $payment_tendered = ($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid);
                            $is_coinage_return = (!empty($payment->note) && strpos($payment->note, '__coinage_return__') !== false)
                                || (isset($payment->cc_holder) && $payment->cc_holder === '__coinage_return__');
                            if ($is_coinage_return) {
                                $pay_surplus_label = lang("change");
                            } elseif ($is_multi_payment || $payment_tendered > $invoice_grand) {
                                $pay_surplus_label = lang("balance");
                            } else {
                                $pay_surplus_label = lang("balance");
                            }

                            echo '<tr>';

                            if ($payment->paid_by == 'cash' || $payment->paid_by == 'deposit' || $payment->paid_by == 'Due Payment') {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                if ($payment->pos_balance > 0) {
                                    echo '<td align="right">' . ($pay_surplus_label . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                } elseif ($payments[count($payments) - 1]->paid_by == 'cash') {
                                    echo '<td align="right">' . ($pay_surplus_label . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                }
                                 if($payment->paid_by == 'deposit' && $customer->deposit_amount > 0){
                                 
                                        echo '<td>';
                                           echo  'Opening Balance : '.$this->sma->formatMoney($payment->cc_holder + $payment->amount );
                                        
                                            echo  ' <br/> Use : '. $this->sma->formatMoney($payment->amount);
                                        echo '<br/> Closing Balance : '. $this->sma->formatMoney($payment->cc_holder);
                                       
                                        echo '</td>';
                                    
                                }
                                
                            } elseif($payment->paid_by == 'award_point'){
                               echo '<td>' . lang("paid_by") . ':&nbsp;' . lang('Award_Point') . '</td>';
                                echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                if ($payment->pos_balance > 0) {
                                    echo '<td align="right">' . ($pay_surplus_label . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                } elseif ($payments[count($payments) - 1]->paid_by == 'cash') {
                                    echo '<td align="right">' . ($pay_surplus_label . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                }
                            }elseif (( $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td align="center">' . lang("no") . ':&nbsp;' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                                echo '<td align="right">' . lang("name") . ':&nbsp;' . $payment->cc_holder . '</td>';
                            } elseif (( $payment->paid_by == 'CC' || $payment->paid_by == 'DC')) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td align="center">Transaction No : ' . $payment->transaction_id . '</td>';
                                echo '<td align="right">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                if ($payment->pos_balance > 0) {
                                    echo '<td align="right">' . ($pay_surplus_label . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                }
                            } elseif ($payment->paid_by == 'instomojo' && $payment->transaction_id) {
                                echo '<td>' . lang("paid_by") . ': Instomojo</td>';
                                echo '<td>Payment ID: ' . $payment->transaction_id . '</td>';
                                echo '<td>' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'ccavenue' && $payment->transaction_id) {
                                echo '<td>' . lang("paid_by") . ': CCavenue</td>';
                                echo '<td align="center">Payment ID: ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'paytm' && $payment->transaction_id) {
                                echo '<td>' . lang("paid_by") . ': Paytm</td>';
                                echo '<td align="center">Payment ID: ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'paynear' && $payment->transaction_id) {
                                echo '<td>' . lang("paid_by") . ': Paynear</td>';
                                echo '<td align="center">Payment ID: ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'payumoney' && $payment->transaction_id) {
                                echo '<td>' . lang("paid_by") . ': Payumoney</td>';
                                echo '<td>Payment ID: ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td align="center">' . lang("cheque_no") . ':&nbsp;' . $payment->cheque_no . '</td>';
                                echo '<td align="right">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            } elseif ($payment->paid_by == 'gift_card' && $payment->paid_by) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang('Gift_Card') . '</td>';
                                echo '<td align="center">' . lang("no") . ':&nbsp;' . $payment->cc_no . '</td>';
                                echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'credit_note' && $payment->pos_paid) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang('Credit_Note') . '</td>';
                                echo '<td>' . lang("no") . ':&nbsp;' . $payment->cc_no . '</td>';
                                echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td>' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                            } elseif ($payment->paid_by && $payment->amount) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . ((strpos($payment->paid_by, 'other_') === 0) ? ucwords(str_replace('_', ' ', substr($payment->paid_by, 6))) : (lang($payment->paid_by) ? lang($payment->paid_by) : $payment->paid_by)) . '</td>';
                                echo '<td align="center">Transaction No : ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                if ($payment->pos_balance > 0) {
                                    echo '<td align="right">' . ($pay_surplus_label . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                                }
                                echo $payment->note ? '<td align="right">' . lang("payment_mode") . ':&nbsp;' . $payment->note . '</td>' : '';
                            }

                            echo '</tr>';
                        }
                        echo '</tbody></table></div>';
                    }
                    echo '<div style:width:100%;>';
                    if (isset($default_printer->show_saving_amount) && $default_printer->show_saving_amount) {
                        echo '<h6 style="text-align:center;margin:0; text-transform: uppercase;"><b>Total Savings: ' . $this->sma->formatMoney($savings, 2) . '</b></h6>';
                    }

                    if (isset($return_payments) && !empty($return_payments)) {
                        echo '<div><strong>' . lang('return_payments') . '</strong><table class="table table-striped table-condensed" style="margin-bottom:0px;"><tbody>';
                        foreach ($return_payments as $payment) {
                            $payment->amount = (0 - $payment->amount);
                            $payment_tendered = ($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid);
                            $is_coinage_return = (!empty($payment->note) && strpos($payment->note, '__coinage_return__') !== false)
                                || (isset($payment->cc_holder) && $payment->cc_holder === '__coinage_return__');
                            if ($is_coinage_return) {
                                $pay_surplus_label = lang("change");
                            } elseif ($payment_tendered > $invoice_grand) {
                                $pay_surplus_label = lang("balance");
                            } else {
                                $pay_surplus_label = lang("balance");
                            }
                            echo '<tr>';
                            // if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            if ($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td>' . ($pay_surplus_label . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';

                                if($payment->paid_by == 'deposit' && $customer->deposit_amount > 0){
                                    
                                    echo '<td>';
                                           echo  'Opening Balance : '.$this->sma->formatMoney($payment->cc_holder + $payment->amount );
                                        
                                            echo  ' <br/> Use : '. $this->sma->formatMoney($payment->amount);
                                        echo '<br/> Closing Balance : '. $this->sma->formatMoney($payment->cc_holder);
                                       
                                        echo '</td>';
                                }
                        
                            }elseif($payment->paid_by == 'award_point'){
                                 echo '<td>' . lang("paid_by") . ':&nbsp;' . lang('Award Point') . '</td>';
                                echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td>' . ($pay_surplus_label . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
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
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang('Gift_Card') . '</td>';
                                echo '<td>' . lang("no") . ':&nbsp;' . $payment->cc_no . '</td>';
                                echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td>' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'credit_note' && $payment->pos_paid) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang('Credit_Note') . '</td>';
                                echo '<td>' . lang("no") . ':&nbsp;' . $payment->cc_no . '</td>';
                                echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td>' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
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

                    if ($Settings->invoice_view == 1) {
                        // $resTaxTbl = $this->sma->taxInvvoiceTabel($tax_summary, $taxItems, $inv, $return_sale, $Settings, 1);
                        // $resTaxTbl = $this->sma->taxInvvoiceTabel($tax_summary,$taxItems,$inv,$return_sale,$Settings);
                        // $resTaxTbl = $this->sma->taxInvoiceTableCSI($tax_summary, $inv, $return_sale, $Settings, 1);
                        // echo $resTaxTbl;
                        $taxReport = $this->sma->getTaxSalesReport($inv->id, $Settings, false);
                        $surchargeReport = $this->sma->getSurchargeTaxSalesReport($inv->id, $Settings);
                        if ($taxReport === '' && $surchargeReport !== '') {
                            echo '<h4 class="tax_summary_head" style="font-size: 12px; text-align:center; font-weight:bold; margin:5px 0px;">Tax Summary</h4>';
                        }
                        echo $taxReport;
                        echo $surchargeReport;
                        echo $this->sma->getTaxSalesReportAttrTotals($inv->id, $Settings);
                    }
                    ?>
                    <?php if ($default_printer->show_award_point) { ?>
                        <?=
                        $customer->award_points != 0 && $Settings->each_spent > 0 ? '<p class="text-center" style="margin-bottom:0px;">(' . lang('this_sale') . ':&nbsp;' . floor(($inv->grand_total / $Settings->each_spent) * $Settings->ca_point)
                                . ') ' .
                                lang('total') . ' ' . lang('award_points') . ':&nbsp;' . $customer->award_points . '</p>' : '';
                        ?>
                        <?php
                    }//close if 
                    if ($default_printer->show_offer_description) {
                        ?>
                        <?= '<p class="text-center">' . $inv->offer_description ? 'Offer Applied : ' . $inv->offer_description : $inv->offer_category . '</p>' ?>
                    <?php }//close if 
                    if ($inv->coupon_code) {
                        ?>
                        <p>
                            <?php echo $inv->coupon_code ? 'Coupon Applied : ' . $inv->coupon_code . '(' . $inv->product_discount . ')' : ''; ?>
                        <p>
                            <?php
                        }
                        ?>
                        <?= $inv->note ? '<p class="text-center">' . $this->sma->decode_html($inv->note) . '</p>' : ''; ?>
                        <?= $inv->staff_note ? '<p class="no-print"><strong>' . lang('staff_note') . ':</strong> ' . $this->sma->decode_html($inv->staff_note) . '</p>' : ''; ?>
                </div>
                     
                       <div class="">
                            <div class=" well-sm" style="<?= (($default_printer->footer_align =='center')?'':'float:'.$default_printer->footer_align.';    width: 69%;') ?>text-align:<?= (isset($default_printer->footer_align) && $default_printer->footer_align != '') ? $default_printer->footer_align : 'center' ?>;">
                                <?= $this->sma->decode_html($biller->invoice_footer); ?>
                            </div>
                           <?php
                            if($default_printer->footer_align !='center'){
                                if($default_printer->signature){ ?>
                                   <div class=" well-sm pull-right text-center" style="width: 30%;margin-top: 3em;">
                                   <div style="height:60px; border:1px solid;">

                                   </div>
                                    <!-- For <strong><?= $Settings->site_name?> </strong> -->
                                    For <strong><?= (isset($inv->biller) && !empty($inv->biller)) ? $inv->biller : ((isset($biller->company) && $biller->company != '-') ? $biller->company : (isset($biller->name) ? $biller->name : $Settings->site_name)) ?></strong>

                               </div>
                              <?php 
                                 }
                             } ?>
                    </div>
                </div>
                <?php
                if ($default_printer->show_barcode_qrcode) {
                    ?>
                    <div class="order_barcodes">
                        <?php // $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>

                        <?php $qr_code =  md5('Reciept' . $inv->reference_no . $inv->id) ?>
                        <?php // $this->sma->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2); ?>

                          <?= $this->sma->qrcode('link', urlencode(site_url('reciept/pdf/' . $qr_code)), 2); ?>
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
                    <?php if ($message) { ?>
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
                        <span class="pull-right col-xs-12">
                            <a href="javascript:window.print()" id="web_print" class="btn btn-block btn-primary"
                               onClick="window.print();
                                               return false;"><?= lang("web_print"); ?></a>
                        </span>
                    <?php }
                    ?>
                    <span class="pull-left col-xs-12"><a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a></span>
                    <?php if (!empty($sms_url)) : ?>
                        <span class="pull-left col-xs-12">

                            <?php $DisSMSLink = (int) $sms_limit < 1 ? '<br><a href="http://simplypos.in/login.php" style="color:#000" target="_blank">Please Login on merchant panel & Rechagre Now</a>' : ''; ?>

                            <?php if ($sms_limit > 0): ?>
                                <a class="btn btn-block btn-info" href="javascript:void(0);" id="sms_bill">SMS</a>
                            <?php else: ?>
                                <a class="btn btn-block btn-info" href="javascript:void(0);" id="sms_bill">SMS</a>
                                <!-- <a class="btn btn-block btn-info" style="cursor:no-drop" href="javascript:void(0);" id="sms_bill_not"> SMS </a> -->
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                     <!---------------------------- WhatsApp integration --------------------------------------->

                     <span class="pull-left col-xs-12">
                                <a class="btn btn-block btn-info" href="javascript:void(0);" id="send_whatsapp_app">Whatsapp</a>
                                <!-- <a class="btn btn-block btn-info" style="cursor:no-drop" href="javascript:void(0);" id="sms_bill_not"> SMS </a> -->
                        </span>
              <!---------------------------- WhatsApp integration --------------------------------------->
                    <span class="col-xs-12">
                        <?php
                        $lasturl = explode("/", $_SERVER['HTTP_REFERER']);

                        $last_segment = sizeof($lasturl) - 1;

                        if ($lasturl[$last_segment - 1] . '/' . $lasturl[$last_segment] == 'pos/sales') {
                            ?>
                            <a class="btn btn-block btn-warning"  href="<?= site_url('pos/sales'); ?>">
                                Back To POS Sales
                            </a>
    <?php } elseif ($lasturl[$last_segment] == 'sales') { ?>
                            <a class="btn btn-block btn-warning"  href="<?= site_url('sales'); ?>">
                                Back To Sales
                            </a>
    <?php } elseif ($lasturl[$last_segment] == 'all_sale_lists') { ?>
                            <a class="btn btn-block btn-warning"  href="<?= site_url('sales/all_sale_lists'); ?>">
                                Back To All Sales
                            </a>
                        <?php } else { ?>
                            <a class="btn btn-block btn-warning"  href="<?= site_url(isset($_SESSION['Sales']) ? 'Sales' : 'pos'); ?>"><?= (isset($_SESSION['Sales']) ? "Back To Sales" : lang("back_to_pos")); ?></a>
    <?php } ?>

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
            <!---------------------------- WhatsApp integration --------------------------------------->

             <script type="text/javascript" src="<?= $assets ?>js/whatsapp.js"></script>
             <script>
                const customerPhone = "<?= $hide_customer_phone ? '' : htmlspecialchars($customer->phone, ENT_QUOTES, 'UTF-8') ?>";
                const customerPhoneSystemGenerated = <?= $hide_customer_phone ? 'true' : 'false' ?>;
                const invoice_auto_sms = "<?= $pos_settings->invoice_auto_sms ?>";
                const whatsapp_service = "<?php echo $this->Settings->whatsapp_service; ?>";
                const countryCode = "<?= isset($country_code) ? $country_code : '' ?>";
                const phoneDigits = <?= isset($phone_digits) ? (int)$phone_digits : 10 ?>;
                const receiptCode = "<?= $sms_code ?>";
            </script>
              <!---------------------------- WhatsApp integration --------------------------------------->
              
    <?php if (!empty($sms_url)) : ?>
                <script>
                                   $('#sms_bill').click(function () {
                                       var phone = prompt("<?= lang("phone"); ?>", "<?= $hide_customer_phone ? '' : htmlspecialchars($customer->phone, ENT_QUOTES, 'UTF-8'); ?>");
                                       var code = '<?php echo $sms_code ?>';
                                       if (phone != null) {
                                           $.ajax({
                                               type: "GET",
                                               url: "<?= site_url('reciept/send_sms') ?>",
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
                                   function printReceipt() {
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
        if ($Settings->invoice_view == 1 || $Settings->invoice_view == 2) {
            echo lang('tax_invoice');
        }
        ?>\r\n";
                                       receipt += "<?php
        if ($Settings->invoice_view == 1 || $Settings->invoice_view == 2) {
            echo drawLine($pos_settings->char_per_line);
        }
        ?>\r\n";

                                       receipt += "\x1B\x61\x30";
                                       receipt += "<?= printLine(lang("reference_no") . ": " . $inv->reference_no, null, null, $pos_settings->char_per_line) ?>" + "\n";
                                       receipt += "<?= printLine(lang("sales_person") . ": " . $biller->name, null, null, $pos_settings->char_per_line); ?>" + "\n";
                                       receipt += "<?= printLine(lang("customer") . ": " . $inv->customer, null, null, $pos_settings->char_per_line); ?>" + "\n";
                                       receipt += "<?= printLine(lang("date") . ": " . $_invoice_date_display, null, null, $pos_settings->char_per_line) ?>" + "\n\n";
                                       receipt += "<?php
        $r = 1;
        foreach ($rows as $row):
            ?>";
                                           receipt += "<?= "#" . $r . " "; ?>";
                                           receipt += "<?= printLine(product_name(addslashes($row->product_name)) . ($row->variant ? ' (' . $row->variant . ')' : '') . ":" . $row->tax_code, '*', null, $pos_settings->char_per_line); ?>" + "\n";
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
    <?php } ?>
    <script>
  <?php
				
    /* ------ For checking Print/notPrint Button updated by SW 21/01/2017 --------------- */
    $_print = $_SESSION['print_type'];
    if (!$pos_settings->java_applet && isset($_SESSION['print_type']) && $_SESSION['print_type'] == 'print' || $auto_print_receipt == 1):
        unset($_SESSION['print_type']);
        ?>
                $(window).load(function () {
                        window.print();
                    });    
    <?php endif ?>
            </script>

            <?php if ($_print != 'notprint_notredirect' && !empty($_print)): ?>
                <?php

                 if ($Settings->pos_type == 'restaurant') {
                    //  $redirect = $_SESSION['Sales'] ? 'Sales' : 'pos/kot';
                     $redirect = $_SESSION['Sales'] ? 'Sales' : 'pos';

                 }else{
                     if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')) {
                       ?>
                       <script>
                       jQuery(function () {
                           window.__androidPrinting = true;
                           setTimeout(function () {
                               var $printButton = jQuery("[id*='web_print']");
                               if ($printButton.length) {
                                   $printButton.trigger('click');
                               } else if (window.print) {
                                   window.print();
                               }
                           }, 200);
                       });
                       </script>
                       <?php
                           $redirect = $_SESSION['Sales'] ? 'Sales' : 'pos';

                   }else{
                        $redirect = $_SESSION['Sales'] ? 'Sales' : 'pos';
                    }
                 }
                unset($_SESSION['Sales']);
                ?>		
                <script>
                    jQuery(function () {
                        window.__initializePosRedirect = window.__initializePosRedirect || function (redirectUrl) {
                            if (window.__posRedirectHandlerInitialized) {
                                return;
                            }
                            window.__posRedirectHandlerInitialized = true;

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
                                window.__androidPrinting = false;
                                jQuery("[id*='back_to_pos']").trigger('click');
                                window.location.href = redirectUrl;
                                return false;
                            };

                            if (window.__androidPrinting) {
                                var afterPrintHandler = function () {
                                    window.removeEventListener('afterprint', afterPrintHandler);
                                    executeRedirect();
                                };
                                window.addEventListener('afterprint', afterPrintHandler);

                                jQuery(window).one('focus', function () {
                                    if (window.__androidPrinting) {
                                        setTimeout(executeRedirect, 100);
                                    }
                                });

                                redirectTimeoutId = setTimeout(executeRedirect, 15000);
                            } else {
                                setTimeout(executeRedirect, 500);
                            }
                        };

                        window.__initializePosRedirect('<?= $redirect ?>');
                    });
                </script>
    <?php endif; ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('#email').click(function (event) {
                        event.preventDefault();
                        var email = prompt("<?= lang("email_address"); ?>", "<?= $customer->email; ?>");

                        if (email != null) {
                            $.ajax({
                                type: "post",
                                url: "<?= site_url('pos/email_receipt') ?>",
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
    /* ------ For checking Print/notPrint Button updated by SW 21/01/2017 --------------- */
    $_print = $_SESSION['print_type'];
    if (!$pos_settings->java_applet && isset($_SESSION['print_type']) && $_SESSION['print_type'] == 'print' || $auto_print_receipt == 1):
        unset($_SESSION['print_type']);
        ?>
                    $(window).load(function () {
                        window.print();
                    });
    <?php endif ?>
            </script>

            <?php if ($_print != 'notprint_notredirect' && !empty($_print)): ?>
                <?php

                 if ($Settings->pos_type == 'restaurant') {
                     $redirect = $_SESSION['Sales'] ? 'Sales' : 'pos';
                 }else{
                    if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')) {
                     exit;
                    }else{
                        $redirect = $_SESSION['Sales'] ? 'Sales' : 'pos';
                    }
                }
                unset($_SESSION['Sales']);
                ?>		
                <script>
                    jQuery("document").ready(function () {
                        setTimeout(function () {
                            jQuery("[id*='back_to_pos']").trigger('click');
                            //jQuery("[id*='email']").trigger('click');
                              window.location.href = '<?= $redirect ?>';
                             return false;
                        }, 10);
                    });
                </script>
    <?php endif; ?>
        </body>
    </html>
<?php }
?>

   
<?php 
if ($_SERVER['HTTP_REFERER'] == base_url('pos')) {

    // Decode and trim category tokens
    $category_tokens = json_decode($inv->category_tokens, true) ?: [];
    $category_tokens = array_combine(
        array_map('trim', array_keys($category_tokens)),
        array_values($category_tokens)
    );

    // Get allowed categories from POS settings
    $category_Array = array_map('trim', explode(",", $pos_settings->categorys));

    $category = [];

    // Group items by category based on settings
    foreach ($rows as $rowItems) { 
        if ($pos_settings->print_all_category == '1' || in_array($rowItems->category_id, $category_Array)) {
            if (isset($category[$rowItems->category_id])) {
                $category[$rowItems->category_id][] = $rowItems;
            } else {
                $category[$rowItems->category_id] = [$rowItems];
            }
        }
    }

    if (!empty($category)) {
        foreach ($category as $key => $items) { 

            // Get token number for this category
            $token_key = (string)trim($key);
            $token_no = isset($category_tokens[$token_key]) ? $category_tokens[$token_key] : 'N/A';
?>
<!-- <div class="page-break" id="order_print_bill_<?= $key ?>" style="display:none">
    <style>
        @media print {
            .page-break { display: block; page-break-before: always; }
        }
        .no-border { border: 0; } 
        #orderTable_<?= $key ?> tbody tr td, 
        #orderTable_<?= $key ?> tbody tr th {
            border: 1px solid; 
            padding: 5px 2px;
        }
        .bold { font-weight: bold; }
    </style>
    <div class="text-center" style="text-align: center;">
        <strong style="text-transform:uppercase;">
            <?= $biller->company != '-' ? $biller->company : $biller->name; ?>
        </strong><br/>
        <span>
            Token No: <?= $token_no ?> | <?= date('d/m/y h:i A', strtotime($inv->date)); ?>
        </span><br/>
    </div> 

    <table id="orderTable_<?= $key ?>" 
           style="width: 100%; border-collapse: collapse; text-align: left;">
        <tbody>
            <tr>
                <th colspan="2" style="text-align: center;">
                    <?= $items[0]->category_name ?>
                </th> 
            </tr>
            <tr>
                <th>Items</th>
                <th>Qty</th>
            </tr>
            <?php foreach ($items as $rowItem) { ?>            
                <tr>
                    <td>
                        <?= $rowItem->product_name . ($rowItem->variant ? ' (' . $rowItem->variant . ')' : '') ?>
                        (<?= $rowItem->product_code ?>)
                    </td>
                    <td>
                        <?= $this->sma->formatQuantity($rowItem->quantity) ?>
                        <?= ucfirst($rowItem->product_unit_code) ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>        
</div> -->
<div class="page-break" id="order_print_bill_<?= $key ?>" style="display:none">
    <style>

        @media print {
        .page-break { display: block; page-break-before: always; }
        }
        .no-border { border: 0; } 
        #orderTable_<?=$key ?>>tbody>tr>td,#orderTable_<?=$key ?>>tbody>tr>th{ border: 1px solid;padding:5px 2px}
        .bold { font-weight: bold; }
        .token-number { font-size: 48px;  margin: 10px 0; }
        .order-type-icon { width: 60px; height: 60px; margin: 10px auto; }
        .order-type-text { font-size: 18px; font-weight: bold; margin-top: 5px; }
        .restaurant-info { margin: 15px 0; line-height: 1.6; }
        .order-details { margin: 15px 0; text-align: left; }
        .item-table { width: 100%; border-top: 2px solid #000; border-bottom: 2px solid #000; margin-top: 10px; border-spacing: 0; }
        .item-table th { text-align: left; padding: 8px 5px; font-weight: bold; }
        .item-table td { padding: 8px 5px; }
    </style>

    <?php if ($Settings->pos_type == 'restaurant') { ?>
      
        <!-- QSR Token Format -->
        <div class="text-center" style="text-align: center; padding: 20px 10px;">
            <?php 
            $display_token = !empty($inv->kot_tokan) ? $inv->kot_tokan : substr($inv->reference_no, -4);
            ?>
            <div class="token-number"><?= $display_token ?></div>

            <div style="display: table; width: 100%; margin-top: 15px;">
                <div style="display: table-cell; text-align: left; width: 60%; vertical-align: top;">
                    <div class="order-details">
                        <strong>INV#</strong> <?= $inv->invoice_no ?><br/>
                        <?= date('d/m/Y H:i:s', strtotime($inv->date)); ?><br/>
                        <strong><?= $rows[0]->category_name ?></strong>
                    </div>
                </div>
                <div style="display: table-cell; text-align: center; width: 40%; vertical-align: top;">

                    <?php 
                    $order_type = !empty($inv->order_type) ? strtolower($inv->order_type) : 'dine in';
                    if (strpos($order_type, 'take') !== false || strpos($order_type, 'away') !== false || strpos($order_type, 'parcel') !== false) {
                        echo '<div class="order-type-icon">
                            <img src="' . $assets . 'pos/images/Token_Takeaway_Icon.svg" alt="Take Away" width="60" height="60">
                        </div>';
                    } else {
                        echo '<div class="order-type-icon">
                            <img src="' . $assets . 'pos/images/Token_Takeaway_Icon.svg" alt="Take Away" width="60" height="60">
                        </div>';
                    }
                    ?>
                </div>
            </div>

            <table class="item-table">
                <thead>
                    <tr>
                        <th style="width: 15%; padding-right: 15px;">Qty</th>
                        <th style="width: 85%; padding-left: 15px;">Item</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($rows as $rowItems){ ?>            
                    <tr>
                        <td style="text-align: center; padding-right: 15px;"><?= $this->sma->formatQuantity($rowItems->quantity) ?></td>
                        <td style="padding-left: 15px;"><?= $rowItems->product_name .($rowItems->variant? ' ('.$rowItems->variant.') ':'' ) ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    <?php } 
    /* else { ?>
        <!-- Restaurant KOT Format (Original) -->
        <div class="text-center" style="text-align: center;">
            <strong style="text-transform:uppercase; margin-bottom: 0px;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></strong><br/>
            <span>Token No: <?= $inv->kot_tokan?>, <?php echo date('d/m/y h:i A', strtotime( $inv->date)); ?></span><br/>
        </div> 

        <table  id="orderTable_<?= $key ?>" style="width: 100%;border-collapse: collapse;text-align: left;" > 
            <tbody>
                <tr>
                    <th colspan="2" style="text-align: center;"><?= $rows[0]->category_name ?></th> 
                </tr>
                <tr>
                    <th>Items</th>
                    <th>Qty</th>
                </tr>
                <?php foreach($rows as $rowItems){ ?>            
                    <tr>
                        <td><?= $rowItems->product_name .($rowItems->variant? ' ('.$rowItems->variant.') ':'' ) ?> (<?= $rowItems->product_code ?>)</td>
                        <td><?= $this->sma->formatQuantity($rowItems->quantity) ?> <?= ucfirst($rowItems->product_unit_code) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } */ ?>        
</div>


<script>
    setTimeout(function() {
        openWin('<?= $key ?>');
    }, 10);
</script>

<?php 
        } 
    }
?>    

<script>
    function openWin(div) {
        var winPrint = window.open('', '', 
            'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
        winPrint.document.write(document.getElementById('order_print_bill_' + div).innerHTML);
        winPrint.document.close();
        winPrint.focus();
        winPrint.print();
        setTimeout(function() {
            winPrint.close();
        }, 100);
    }
    localStorage.removeItem("ordertype");
</script>

<?php } ?>



  
     
    