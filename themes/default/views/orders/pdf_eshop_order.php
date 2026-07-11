<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$savings = 0;
//modifie name of product
foreach ($rows as $key => $val) {
    if (!empty($val->option_id)) {
        $item_note = (empty($val->note)) ? '' : ": " . $val->note;
        $rows[$key]->product_name = $val->product_name . ' (' . $val->variant . $item_note . ')';
        $rows[$key]->product_code = $val->product_code . '_' . $val->option_id;
    }//end if
    if ($val->mrp >= $val->unit_price) {
        $savings += ($val->mrp - $val->unit_price ) * $val->quantity;
    }
}//end foreach

$resOutput = $this->sma->posBillTable($default_printer, $inv, $return_sale, $rows, $return_rows);
$font_size = isset($default_printer->font_size) ? $default_printer->font_size : '11';

$sms_code = md5('Reciept' . $inv->reference_no . $inv->id);
$sms_url = base_url('reciept/send_sms') . '?code=' . md5('Reciept' . $inv->reference_no . $inv->id) . '&phone=' . $customer->phone;

function product_name($name) {
    return character_limiter($name, (isset($pos_settings->char_per_line) ? ($pos_settings->char_per_line - 8) : 35));
}

$b_email = "";
if (isset($biller->email) && $biller->email != "") {
    $b_email = lang("email") . ": " . $biller->email . "<br>";
}
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
            /*#receipt-data table { font-size: <?php echo $font_size; ?>pt !important;}*/
            #receipt-data table { font-size: 9pt !important; margin-bottom: 5px;}
            .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td { padding: 1px 1px; border: 1px solid #000 !important; }
            .well {
                padding: 5px; margin-bottom: 3px;
            }
            @media print {
                .no-print { display: none; }
                #wrapper { max-width: <?php echo $default_printer->width ?>px; width: 100%; min-width: 250px; margin: 0 auto; }
                .no-border { border: none !important; }
                .border-bottom { border-bottom: 1px solid #000 !important; }
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
                        <hr/>
                        <div class="col-sm-12 text-center">
                            <h5 style="font-weight:bold; margin: 0px;">Eshop Order</h5>
                        </div>

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
                    <div class="row">

                        <div class="col-sm-6">
                            <div ><?php echo '<b>' . lang("reference_no") . ": </b>" . $inv->reference_no; ?></div>

                            <div><b>Order No.</b> : <?= $inv->invoice_no; ?>
                                <?php
                                if ($pos_settingss->display_token == 1) {
                                    if (isset($default_printer->show_kot_tokan) && $default_printer->show_kot_tokan == 1) {
                                        echo ($inv->kot_tokan) ? '<br/>' . lang("Token No.") . ":&nbsp" . $inv->kot_tokan : '';
                                    }
                                }
                                ?> 
                            </div>

                        </div>
                        <div class="col-sm-6">
                            <?php echo '<b>' . lang("date") . "</b>: " . $this->sma->hrld($inv->date); ?>
                        </div>
                    </div> 

                    <div class="row">
                        <table style="width:100%; font-size: 13px;">
                            <tr>
                                <td class="text-left" style="width:50%;font-size: 13px; padding-left: 15px; vertical-align: top; ">
                                    <?php
                                    echo '<b>' . lang("Billing details ") . "</b><br/> <b>Name</b> : " . ($billing_address->company_name ? $billing_address->company_name : $customer->name ) . '<br/>';
                                    if (!empty($billing_address->phone)) {
                                        echo " <b>Tel:&nbsp;</b>" . $billing_address->phone, ', ';
                                    }
                                    if (!empty($billing_address->email_id)) {
                                        echo " <b>Email:&nbsp;</b>" . $billing_address->email_id . "<br>";
                                    }
                                    if (!empty($billing_address->address_name)) {
                                        echo '<b>' . lang("address") . ":&nbsp;</b>" . $billing_address->address_name;
                                        echo ($billing_address->line1 ? ',<br/> ' . $billing_address->line1 : '') . ($billing_address->line2 ? ',<br/> ' . $billing_address->line2 : '');
                                        echo ($billing_address->city ? '<br/> ' . ucfirst($billing_address->city) . ' ' : '');
                                        echo ($billing_address->state ? ucfirst($billing_address->state) . ' ' : '');
                                        echo ($billing_address->country ? ucfirst($billing_address->country) : '');
                                        echo ($billing_address->postal_code ? ' - ' . $billing_address->postal_code : '');
                                    }
                                    ?>
                                </td>
                                <td class="text-left" style="width:50%;font-size: 13px; padding-left: 15px; vertical-align: top; ">
                                    <strong><?php echo $this->lang->line("Shipping Details"); ?></strong>
                                    <br/><span><b>Name</b> : <?= ($shiping_address->company_name ? $shiping_address->company_name : $shiping_address->name ) ?></span>
                                    <address>
                                        <b>Tel</b> : <?= $shiping_address->phone ?><br/>
                                        <b>Email</b> : <?= $shiping_address->email_id ?><br/>
                                        <?php
                                        if (!empty($billing_address->address_name)) {
                                            echo '<b>' . lang("address") . ":&nbsp;</b>" . $shiping_address->address_name;
                                            echo ($shiping_address->line1 ? ',<br/> ' . $shiping_address->line1 : '') . ($shiping_address->line2 ? ',<br/> ' . $shiping_address->line2 : '');
                                            echo ($shiping_address->city ? '<br/> ' . ucfirst($shiping_address->city) . ' ' : '');
                                            echo ($shiping_address->state ? ucfirst($shiping_address->state) . ' ' : '');
                                            echo ($shiping_address->country ? ucfirst($shiping_address->country) : '');
                                            echo ($shiping_address->postal_code ? ' - ' . $shiping_address->postal_code : '');
                                        }
                                        if ($billerDetails) {
                                            echo ' <b>Shipping Type</b> : ' . $billerDetails[0]['shipping_method_name'];
                                        }if ($inv->deliver_later) {
                                            echo '<br/><b>Shipping Date</b> : ' . date('d-m-Y', strtotime($inv->deliver_later));
                                        }if ($inv->time_slotes) {
                                            echo ', <b>Time</b> : ' . $inv->time_slotes;
                                        }
                                        if ($inv->shipping_outlet != $inv->warehouse_id) {
                                            //                                            echo '<br/><b>Shipping Outlet</b> : ' . $inv->shipping_outlet_name;
                                        }
                                        ?>    
                                    </address>
                                </td>
                            </tr>
                        </table>    

                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="pull-left"><?php echo '<b>' . lang("reference_no") . "</b>: " . $inv->reference_no; ?></div>
                            <div class="pull-right"><?php echo ($inv->staff_note && $show_kot) ? $this->sma->decode_html($inv->staff_note) : ''; ?></div>
                        </div>
                    </div> 
                    <div style="clear:both;"></div> 
                    <?php
                    $r = 1;
                    $category = 0;
                    $tax_summary = array();
                    foreach ($rows as $row) {
                        if (isset($tax_summary[$row->tax_code])) {
                            $tax_summary[$row->tax_code]['items'] += $row->unit_quantity;
                            $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                            $tax_summary[$row->tax_code]['amt'] += ($row->unit_quantity * $row->net_unit_price);
                        } else {
                            $tax_summary[$row->tax_code]['items'] = $row->unit_quantity;
                            $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                            $tax_summary[$row->tax_code]['amt'] = ($row->unit_quantity * $row->net_unit_price);
                            $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                            $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                            $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                            $tax_summary[$row->tax_code]['tax_rate_id'] = $row->tax_rate_id;
                        }
                    }
                    if ($return_rows) {
                        foreach ($return_rows as $row) {
                            if (isset($tax_summary[$row->tax_code])) {
                                $tax_summary[$row->tax_code]['items'] += $row->unit_quantity;
                                $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                                $tax_summary[$row->tax_code]['amt'] += ($row->unit_quantity * $row->net_unit_price) - $row->item_discount;
                            } else {
                                $tax_summary[$row->tax_code]['items'] = $row->unit_quantity;
                                $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                                $tax_summary[$row->tax_code]['amt'] = ($row->unit_quantity * $row->net_unit_price) - $row->item_discount;
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

                            // $rounding = giftcardBalance( $payment->sale_id );  
                            $rounding = $this->orders_model->ordersRounding($payment->order_id);
                            echo '<tr>';

                            if ($payment->paid_by == 'cash' || $payment->paid_by == 'deposit' || $payment->paid_by == 'Due Payment') {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                if ($payment->paid_by == 'deposit') {
                                    $deposit = depositeBalance($customer->id);
                                    // echo '<td align="right">' . lang("Deposit Balance") . ':&nbsp;' . ($deposit > 0 ? $deposit : 0) . '</td>';
                                    echo '<td align="right">' . lang("Deposit Balance") . ':&nbsp;' . ($payment->cc_holder > 0 ? $payment->cc_holder : 0) . '</td>';
                                }
                                if ($payments[count($payments) - 1]->paid_by == 'cash') {
                                    // echo '<td align="right">' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';

                                    echo '<td align="right">' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->amount - ($inv->grand_total + $rounding)) . '</td>';
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
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid - ($inv->grand_total + $rounding)) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'ccavenue' && $payment->transaction_id) {
                                echo '<td>' . lang("paid_by") . ': CCavenue</td>';
                                echo '<td align="center">Payment ID: ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                //echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';

                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid - ($inv->grand_total + $rounding)) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'paytm' && $payment->transaction_id) {
                                echo '<td>' . lang("paid_by") . ': Paytm</td>';
                                echo '<td align="center">Payment ID: ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
//                                   echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid  - ($inv->grand_total + $rounding)) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'UPI_QRCODE' && $payment->transaction_id) {
                                echo '<td>' . lang("paid_by") . ': UPI</td>';
                                echo '<td align="center">Transaction ID: ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
//                                 
                            } elseif ($payment->paid_by == 'paynear' && $payment->transaction_id) {
                                echo '<td>' . lang("paid_by") . ': Paynear</td>';
                                echo '<td align="center">Payment ID: ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                // echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';

                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid - ($inv->grand_total + $rounding)) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'payumoney' && $payment->transaction_id) {
                                echo '<td>' . lang("paid_by") . ': Payumoney</td>';
                                echo '<td>Payment ID: ' . $payment->transaction_id . '</td>';
                                echo '<td align="center">' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                // echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                                echo '<td align="right">' . lang("balance") . ':&nbsp;' . ( ($payment->pos_balance >= 0) ? $this->sma->formatMoney($payment->pos_paid - ($inv->grand_total + $rounding)) : 0) . '</td>';
                            } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td align="center">' . lang("cheque_no") . ':&nbsp;' . $payment->cheque_no . '</td>';
                                echo '<td align="right">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                            } elseif ($payment->paid_by == 'gift_card' && $payment->pos_paid) {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td align="center">' . lang("no") . ':&nbsp;' . $payment->cc_no . '</td>';
                                echo '<td align="center">Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';



                                if ($payment->paid_by == 'gift_card') {

                                    $giftcard = giftcardBalance($payment->cc_no);

                                    //echo '<td align="right">' . lang("GiftCard Balance") . ':&nbsp;' . ($giftcard > 0 ? $this->sma->formatMoney($giftcard) : 0) . '</td>';
                                    echo '<td align="right">' . lang("GiftCard Balance") . ':&nbsp;' . ($payment->cc_holder > 0 ? $this->sma->formatMoney($payment->cc_holder) : 0) . '</td>';
                                } else {
                                    echo '<td align="right">' . lang("balance") . ':&nbsp;' . ($payment->pos_balance > 0 ? $this->sma->formatMoney($payment->pos_balance) : 0) . '</td>';
                                }
                            } elseif (($payment->paid_by == 'other' || $payment->paid_by == 'NEFT' || $payment->paid_by == 'PAYTM' || $payment->paid_by == 'Googlepay' || $payment->paid_by == 'complimentry' || $payment->paid_by == 'swiggy' || $payment->paid_by == 'zomato' || $payment->paid_by == 'ubereats' || $payment->paid_by == 'magicpin') && $payment->amount) {
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
                            if ($payment->paid_by == 'cash') {
                                echo '<td>' . lang("paid_by") . ':&nbsp;' . lang($payment->paid_by) . '</td>';
                                echo '<td>Paid ' . lang("amount") . ':&nbsp;' . $this->sma->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . ($payment->return_id ? ' (' . lang('returned') . ')' : '') . '</td>';
                                echo '<td>' . (($payment->pos_balance >= 0) ? lang("balance") . ':&nbsp;' : lang("change") . ':&nbsp;') . $this->sma->formatMoney($payment->pos_balance) . '</td>';
                            } elseif ($payment->paid_by == 'deposit') {
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
                            } elseif (($payment->paid_by == 'other' || $payment->paid_by == 'NEFT' || $payment->paid_by == 'PAYTM' || $payment->paid_by == 'Googlepay' || $payment->paid_by == 'complimentry' || $payment->paid_by == 'swiggy' || $payment->paid_by == 'zomato' || $payment->paid_by == 'ubereats' || $payment->paid_by == 'magicpin') && $payment->amount) {
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
                        echo $resTaxTbl = $this->sma->taxOrderTabel($tax_summary, $taxItems, $inv, $return_sale, $Settings, 1);
                    }
                    ?>

                    <?php
                    if ($default_printer->show_offer_description) {
                        echo!empty($inv->offer_description) ? '<p class="text-center">Offer Applied : ' . $inv->offer_description . '</p>' : '';
                    }//close if 
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
                        <?= $this->sma->qrcode('link', urlencode(site_url('sales/challan_view/' . $inv->id)), 2); ?>
                    </div>
                <?php }//close if 
                ?>
                <div style="clear:both;"></div>
            </div>

        </div>


    </body>
</html>
