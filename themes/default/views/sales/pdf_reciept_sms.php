<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$savings = 0;
//modifie name of product
foreach ($rows as $key => $val) {
    $Color = '';
    if (!empty($val->shade_id)) {
        $options_color = $this->pos_model->getProductOptionsByShapeId($val->shade_id, $val->product_id, COLOR);
        $Color = $options_color[0]->name;
    }
    if (!empty($val->option_id)) {
        $item_note = (empty($val->note)) ? '' : ": " . $val->note;
        $rows[$key]->product_name = $val->product_name . ' (' . $val->variant . '-' . $Color . $item_note . ')';
        $rows[$key]->product_code = $val->product_code . '_' . $val->option_id;
        $rows[$key]->product_color = $Color;
        $rows[$key]->product_size = $val->variant;
    }//end if
    if ($val->mrp >= $val->unit_price) {
        $savings += ($val->mrp - $val->unit_price ) * $val->quantity;
    }
}//end foreach
//print_r($rows);
$resOutput = $this->sma->posBillTable($default_printer, $inv, $return_sale, $rows, $return_rows, '', 1);
$font_size = isset($default_printer->font_size) ? $default_printer->font_size : '11';

$sms_code = md5('Reciept' . $inv->reference_no . $inv->id);
$sms_url = base_url('reciept/send_sms') . '?code=' . md5('Reciept' . $inv->reference_no . $inv->id) . '&phone=' . $customer->phone;

/* function product_name($name) {
  return character_limiter($name, (isset($pos_settings->char_per_line) ? ($pos_settings->char_per_line - 8) : 35));
  } */

$b_email = "";
if (isset($biller->email) && $biller->email != "") {
    $b_email = lang("email") . ": " . $biller->email . "<br>";
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
                #receipt-data table { font-size: <?php echo $font_size; ?>pt !important;}
                #receipt-data table { font-size: 9pt !important; margin-bottom: 5px;}
                .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td { padding: 1px 1px; }
                 .attr_table ul{float: left !important;}
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

        <body id="container-fluid">
            <?php
        }
        $inv->customer = $customer->name;
        ?>
        <div id="wrapper" syle="    height: 842px;">
            <div id="receiptData" style="padding:1em;">
                <div class="no-print">
                    <?php if ($message) { ?>
                        <div class="alert alert-success">
                            <?= is_array($message) ? print_r($message, true) : $message; ?>
                        </div>
                    <?php }
                    ?>
                </div> 
                <div id="receipt-data" style="font-size: <?php echo $font_size; ?>pt !important"> <!-- -->

                    <div class="text-center">
                        <?php if (isset($default_printer->show_invoice_logo) && $default_printer->show_invoice_logo == 1 && !empty($biller->logo)): ?>
                            <img src="<?= base_url('assets/mdata/'.$Customer_assets.'/uploads/logos/' . $biller->logo); ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                        <?php endif; ?>
                        <h3 style="text-transform:uppercase; margin-bottom: 0px;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h3>
                        <?php
                        echo "<p style='margin: 0 0 5px; '>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country . '. ' .
                        lang("tel") . ":&nbsp;" . $biller->phone . ",&nbsp;" . $b_email;
                        if ($inv->GstSale):
                            echo lang("gstn_no") . ":&nbsp;" . $biller->gstn_no;
                        endif;
                        if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                            echo ", " . $pos_settings->cf_title1 . ":&nbsp;" . $pos_settings->cf_value1;
                        }
                        if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                            echo ", " . $pos_settings->cf_title2 . ":&nbsp;" . $pos_settings->cf_value2;
                        }
                        echo '</p>';
                        ?>
                    </div>  
                    <?php
                    if ($Settings->invoice_view == 1) {
                        ?>
                        <div class="col-sm-12 text-center">
                            <h6 style="font-weight:bold; margin: 0px;"><?= lang('tax_invoice'); ?></h6>
                        </div>
                        <?php
                    }

                    if (!empty($inv->return_sale_ref)) {
                        echo '<p>' . lang("return_ref") . ': ' . $inv->return_sale_ref;
                        if ($inv->return_id) {
                            echo ' <a data-target="#myModal2" data-toggle="modal" href="' . site_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a>';
                        }
                        echo '</p>';
                    }//end if
                    ?>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="pull-right" style="font-weight: bold;"><?php echo lang("date") . ": " . $this->sma->hrld($inv->date); ?></div>
                            <div class="pull-left" style="font-weight: bold;"><?php echo lang("Invoice No.") . ": " . $inv->invoice_no; ?>
                                <?php
                                if ($pos_settingss->display_token == 1) {
                                    if (isset($default_printer->show_kot_tokan) && $default_printer->show_kot_tokan == 1) {
                                        echo ($inv->kot_tokan) ? '<br/>' . lang("Tokan No.") . ":&nbsp" . $inv->kot_tokan : '';
                                    }
                                }
                                ?> 

                            </div>
                        </div>
                    </div> 
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="" style="font-weight: bold;"><?php echo lang("reference_no") . ": " . $inv->reference_no; ?></div>
                            <div class="pull-right" style="font-weight: bold;"><?php echo ($inv->staff_note && $show_kot) ? $this->sma->decode_html($inv->staff_note) : ''; ?></div>
                        </div>
                    </div> 
                    <div class="row">

                        <div class="col-sm-12">   
                            <?php if (isset($shipping_details) && $inv->eshop_sale) { ?>
                                <table style="width: 100%">
                                    <tr>
                                        <td style="width: 50%; vertical-align: top;   ">
                                            <strong style="font-weight: bold">Billing details</strong><br/>
                                            <?= $shipping_details->billing_name ?> <br/>
                                            <?php
                                            if (!empty($shipping_details->billing_phone)) {
                                                echo " Tel:&nbsp;" . $shipping_details->billing_phone, ', ';
                                            }
                                            if (!empty($shipping_details->billing_email)) {
                                                echo " Email:&nbsp;" . $shipping_details->billing_email . "<br>";
                                            } if (!empty($shipping_details->billing_addr)) {
                                                echo lang("address") . ":&nbsp;" . $shipping_details->billing_addr;
                                            }
                                            ?>
                                        </td>
                                        <td style="width: 50%; vertical-align: top;" >
                                            <strong style="font-weight: bold">Shipping Details</strong><br/>
                                            <span><?= $shipping_details->shipping_name; ?></span>
                                            <address>
                                                <b>Tel</b> : <?= $shipping_details->shipping_phone ?>,
                                                <b>Email</b> : <?= $shipping_details->shipping_email ?><br/>
                                                <b>Address</b> :<?= $shipping_details->shipping_addr ?><br/>

                                                <?php
                                                if ($shipping_details) {
                                                    echo $shipping_details->shipping_method_name . '<br/>';
                                                }if ($shipping_details->deliver_later) {
                                                    echo ' <b>Date</b> : ' . date('d-m-Y', strtotime($shipping_details->deliver_later)) . '<br/>';
                                                }if ($shipping_details->time_slotes) {
                                                    echo '<b>Time</b> : ' . $shipping_details->time_slotes;
                                                }
                                                ?>  
                                            </address>
                                        </td>
                                </table>    
                            <?php } else { ?>
                                <?php
                                echo '<span style="font-weight:bold">' . lang("customer") . ": " . $inv->customer . '</span>';
                                if (!empty($customer->phone)) {
                                    echo '<span style="font-weight:bold">, Ph:&nbsp;' . $customer->phone . '</span>';
                                }

                                if (isset($default_printer->show_customer_info) && $default_printer->show_customer_info == 1) {
                                    if (!empty($customer->email)) {
                                        echo ", Email:&nbsp;" . $customer->email . "<br>";
                                    }
                                    if (!empty($customer->address)) {
                                        echo lang("address") . ":&nbsp;" . $customer->address;
                                    }
                                    if (!empty($customer->city)) {
                                        echo ", &nbsp;" . $customer->city;
                                    }
                                    if (!empty($customer->postal_code)) {
                                        echo " - " . $customer->postal_code;
                                    }

                                    if (!empty($customer->state)) {
                                        echo ",  " . $customer->state;
                                    }

                                    if ($customer->cf1) {
                                        echo "<br/>Pan&nbsp;No:&nbsp;" . $customer->cf1;
                                    }
                                    if (isset($default_printer->show_tin) && $default_printer->show_tin == 1) {
                                        if (!empty($customer->pan_card)) {
                                            echo ", " . lang("Pan Card") . ":&nbsp;" . $customer->pan_card;
                                        }

                                        if ($inv->GstSale && !empty($customer->gstn_no)) {
                                            echo ", " . lang("gstn_no") . ":&nbsp;" . $customer->gstn_no;
                                        } elseif (!empty($customer->vat_no)) {
                                            echo ", " . lang('vat_no') . ":&nbsp;" . $customer->vat_no;
                                        }
                                        if ($customer->gst_state_code) {
                                            echo ", State&nbsp;Code:&nbsp;" . $customer->gst_state_code;
                                        }
                                    }
                                }//end if.                        
                            }
                            ?>
                        </div>
                    </div>

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
                    if ($payments) {

                        echo '<div style="margin-top:-4px;" ><table class="table" style="margin-bottom:0px;" ><tbody>';

                        foreach ($payments as $payment) {

                            echo '<tr>';

                            if ($payment->paid_by == 'cash' || $payment->paid_by == 'deposit' || $payment->paid_by == 'Due Payment') {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                if ($payments[count($payments) - 1]->paid_by == 'cash') {
                                    echo '<td align="right">' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
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
                            } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang('Credit_Note') . '</td>';
                                echo '<td align="center">' . lang("no") . ':&nbsp;' . $payment->cc_no . '</td>';
                                echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                            } elseif (($payment->paid_by == 'other' || $payment->paid_by == 'NEFT' || $payment->paid_by == 'PAYTM' || $payment->paid_by == 'Googlepay' || $payment->paid_by == 'complimentry' || $payment->paid_by == 'swiggy' || $payment->paid_by == 'zomato' || $payment->paid_by == 'ubereats') && $payment->amount) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td align="center">Transaction No : ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo $payment->note ? '<td align="right">' . lang("payment_mode") . ':&nbsp;' . $payment->note . '</td>' : '';
                            }

                            echo '</tr>';
                        }
                        echo '</tbody></table></div>';
                        if ($default_printer->show_saving_amount) {
                            echo '<h6 style="text-align:center;margin:0; text-transform: uppercase;"><b>Total Savings: ' . $this->sma->formatMoney($savings, 2) . '</b></h6>';
                        }
                    }

                    if ($return_payments) {
                        echo '<div><strong>' . lang('return_payments') . '</strong><table class="table table-striped table-condensed" style="margin-bottom:0px;"><tbody>';
                        foreach ($return_payments as $payment) {
                            $payment->amount = (0 - $payment->amount);
                            echo '<tr>';
                            // if (($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') && $payment->pos_paid) {
                            if ($payment->paid_by == 'cash' || $payment->paid_by == 'deposit') {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td>' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
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
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang('Credit_Note') . '</td>';
                                echo '<td>' . lang("no") . ':&nbsp;' . $payment->cc_no . '</td>';
                                echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td>' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                            } elseif (($payment->paid_by == 'other' || $payment->paid_by == 'NEFT' || $payment->paid_by == 'PAYTM' || $payment->paid_by == 'Googlepay' || $payment->paid_by == 'complimentry' || $payment->paid_by == 'swiggy' || $payment->paid_by == 'zomato' || $payment->paid_by == 'ubereats') && $payment->amount) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td>Transaction No: ' . $payment->transaction_id . '</td>';
                                echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo $payment->note ? '</tr><td colspan="2">' . lang("payment_mode") . ':&nbsp;' . $payment->note . '</td>' : '';
                            }
                            echo '</tr>';
                        }
                        echo '</tbody></table></div>';
                    }

                    if ($Settings->invoice_view == 1) {
                        //$resTaxTbl = $this->sma->taxInvvoiceTabel($tax_summary, $taxItems, $inv, $return_sale, $Settings, 1);
                        $resTaxTbl = $this->sma->taxInvoiceTableCSI($tax_summary, $inv, $return_sale, $Settings, 1);

                        echo $resTaxTbl;
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
                    ?>
                    <?= $inv->note ? '<p class="text-center">' . $this->sma->decode_html($inv->note) . '</p>' : ''; ?>
                    <?= $inv->staff_note ? '<p class="no-print"><strong>' . lang('staff_note') . ':</strong> ' . $this->sma->decode_html($inv->staff_note) . '</p>' : ''; ?>
                    <div class="well well-sm" style="text-align:<?= $default_printer->footer_align ?>;">
                        <?= $this->sma->decode_html($biller->invoice_footer); ?>
                    </div>
                </div>
                <?php
                if ($default_printer->show_barcode_qrcode) {
                    ?>
                    <div class="order_barcodes">
                        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                        <?= $this->sma->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2); ?>
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

                </div>
            </div>


            <script type="text/javascript" src="<?= $assets ?>pos/js/jquery-1.7.2.min.js"></script>
           
            <script src="<?= $assets ?>js/html2canvas.min.js"></script>
            <script src="<?= $assets ?>js/jspdf.debug.js"></script>
            
            


        </body>
    </html>
<?php }
?>


<script>

    $(document).ready(function () {
       CreatePDFfromHTML() ;
    });


   function CreatePDFfromHTML() {
             let pdf = $('#receiptData').css({'background':'#FFF','padding':'10px','padding-bottom':'2em'});
             let doc = new jsPDF('1', 'pt', [$('#receiptData').width(), $('#receiptData').height()]);
             doc.addHTML(pdf, function () {
            doc.save("Invoice_<?= $inv->invoice_no ?>.pdf");
        });
    }
    
</script>