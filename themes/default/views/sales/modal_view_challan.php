<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
$itemTaxes = isset($inv->rows_tax)?$inv->rows_tax:array();

?>
<style>
    table td p{    width: 250px;
     overflow-wrap: break-word;}
</style> 
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/mdata/'.$Customer_assets.'/uploads/logos/' . $biller->logo; ?>"
                         alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
             
                </div>
            <?php } ?>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                    <p class="bold">
                        <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                        <?= lang("ref"); ?>: <?= $inv->reference_no; ?><br>
                        <?php if (!empty($inv->return_sale_ref)) {
                            echo lang("return_ref").': '.$inv->return_sale_ref;
                            if ($inv->return_id) {
                                echo ' <a data-target="#myModal2" data-toggle="modal" href="'.site_url('sales/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                            } else {
                                echo '<br>';
                            }
                        } ?>
                        <?= lang("challan_no"); ?>: <?= $inv->challan_no; ?><br>
                        <?= lang("sale_status"); ?>: <?= lang($inv->sale_status); ?><br>
                        <?= lang("payment_status"); ?>: <?= lang($inv->payment_status); ?><br>
                    </p>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
     
            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <strong><?php  echo $this->lang->line("from"); ?>,</strong>
                    <h2 style="margin-top:10px;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                    <?= $biller->company ? "" : "Attn: " . $biller->name ?>

                    <address>
                        <?= ($biller->address!='')?'<b> Address : </b> '.$biller->address.',<br/>':'' ?>
                        <?= ($biller->city!='')?$biller->city.' - ':'' ?> <?= ($biller->postal_code!='')?$biller->postal_code.', ':'' ?> 
                        <?= ($biller->state!='')?$biller->state.', ':'' ?><?= ($biller->country!='')?$biller->country.'.<br/>':'' ?>
                        <?= ($biller->phone!='')?'<b>'.lang("tel").' : </b>'.$biller->phone.'<br/> ':'' ?>
                        <?= ($biller->email!='')?'<b>'.lang("email").' : </b>'.$biller->email:'' ?>
                   
                        <?php if ($biller->gstn_no != "-" && $biller->gstn_no != "" && count($itemTaxes) > 0) {
                                echo "<br> <b>" . lang("gstn_no") . " : </b> " . $biller->gstn_no ;
                               }elseif ($biller->vat_no != "-" && $biller->vat_no != "" && count($itemTaxes) ==0) {
                                    echo "<br> <b>" . lang("vat_no") . " : </b>" . $biller->vat_no;
                            }
                            if ($biller->cf1 != "-" && $biller->cf1 != "") {
                                echo "<br> <b>"  .$this->Settings->prd_cmfield1 . " : </b> ". $biller->cf1;
                            }
                            if ($biller->cf2 != "-" && $biller->cf2 != "") {
                                echo "<br> <b>" .$this->Settings->prd_cmfield2 .  " : </b> ". $biller->cf2;
                            }
                        ?>
                    </address>
                </div>
                <div class="col-xs-6">
                    <strong><?php echo $this->lang->line("Customer Details"); ?></strong>
                    <h2 style="margin-top:10px;"><?= $customer->company ? $customer->company : $customer->name; ?></h2>
                    <?= $customer->company ? "" : "Attn: " . $customer->name ?>
                    <address>
                        <?= ($customer->address!='')?'<b> Address : </b>'.$customer->address.',<br/>':'' ?>
                        <?= ($customer->city!='')?$customer->city.' - ':''?><?= ($customer->postal_code!='')?$customer->postal_code.', ':''?>
                        <?= ($customer->state!='')?$customer->state.', ':''?><?= ($customer->country!='')?$customer->country.'.<br/> ':''?>
                        <?= ($customer->phone!='')?'<b>'.lang("tel").' : </b> '.$customer->phone.'</br>':'' ?> 
                        <?= ($customer->email!='')?'<b>'.lang("email").' : </b>'.$customer->email:'' ?>
                        <?php
                            if ($customer->gstn_no != "-" && $customer->gstn_no != "" && count($itemTaxes) > 0) {
                                echo "<br><b>" . lang("gstn_no") . " : </b> " . $customer->gstn_no ;
                            }
                            elseif ($customer->vat_no != "-" && $customer->vat_no != "" && count($itemTaxes) ==0) {
                                echo "<br><b> " . lang("vat_no") . " : </b>" . $customer->vat_no;
                            }
                        ?>
                    </address>
                </div>
            </div>
            <?php if (!empty($show_receipt_addresses)) { ?>
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-xs-12">
                    <?php $this->load->view($this->theme . 'sales/partials/receipt_addresses', array(
                        'billing_address_text' => isset($billing_address_text) ? $billing_address_text : '',
                        'shipping_address_text' => isset($shipping_address_text) ? $shipping_address_text : '',
                    )); ?>
                </div>
            </div>
            <?php } ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">
                    <thead>
                    <tr>
                        <th><?= lang("no"); ?></th>
                        <th><?= lang("Product Name"); ?> (<?= lang("code"); ?>) </th>
                        <?php
                            if ($Settings->product_serial) {
                                echo '<th style="text-align:center; vertical-align:middle;">' . lang("serial_no") . '</th>';
                            }
                        ?>
                        <th><?= lang("unit_price"); ?></th>
                        <th><?= lang("quantity"); ?></th>
                        <?php
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<th>' . lang("discount") . '</th>';
                        }
                        ?>
                        <th><?= lang("Net Price"); ?></th>
                        <?php
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<th>' . lang("tax") . '</th>';
                        }
                        ?>
                        <th><?= lang("subtotal"); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php $r = 1;
                    $totalqty = 0;
                    foreach ($rows as $row):
                    ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?php if($Settings->sales_image == '1') { ?>
                                    <img src="assets/mdata/<?= $Customer_assets ?>/uploads/thumbs/<?=$row->image?>" style="width:30px; height:30px;" alt="<?=$row->product_code?>" />
                                <?php } ?>
                                <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''). ($row->shade_name ? ' (' . $row->shade_name . ')' : ''); ?>
                                <?= $row->details ? '<br>' . $row->details : ''; ?>                                
                                
                                <?php 
                                if (!empty($itemTaxes[$row->id]) && $default_printer->tax_classification_view) {
                                    echo '<div style="margin-top:5px; color:#666; font-size:0.9em;">';
                                    foreach ($itemTaxes[$row->id] as $tax) {
                                        echo $tax->attr_code . ' ' . $tax->attr_per . '%: ' . $this->sma->formatMoney($tax->amt) . '<br>';
                                    }
                                    echo '</div>';
                                }
                                ?>
                            </td>
                            <?php
                                if ($Settings->product_serial) {
                                    echo '<td>' . $row->serial_no . '</td>';
                                }
                            ?>
                            <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->unit_quantity)); ?></td>
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                            <?php
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_quantity * $row->net_unit_price); ?></td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                            }                            
                            ?>
                            <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                        </tr>
                        <?php echo $this->sma->taxAttrTBL_csi_dynamic($row, 'challan'); ?>
                    <?php
                        $r++;
                        $totalqty += $row->unit_quantity;
                    endforeach;
                    
                    if ($return_rows) {
                        echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
                        foreach ($return_rows as $row):
                    ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?php if($Settings->sales_image == '1') { ?>
                                    <img src="assets/mdata/<?= $Customer_assets ?>/uploads/thumbs/<?=$row->image?>" style="width:30px; height:30px;" alt="<?=$row->product_code?>" />
                                <?php } ?>
                               <?= $row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''). ($row->shade_name ? ' (' . $row->shade_name . ')' : ''); ?>
                                <?= $row->details ? '<br>' . $row->details : ''; ?>                                
                                
                                <?php 
                                if (!empty($itemTaxes[$row->id]) && $default_printer->tax_classification_view) {
                                    echo '<div style="margin-top:5px; color:#000000; font-size:0.9em;">';
                                    foreach ($itemTaxes[$row->id] as $tax) {
                                        echo $tax->attr_code . ' ' . $tax->attr_per . '%: ' . $this->sma->formatMoney($tax->amt) . '<br>';
                                    }
                                    echo '</div>';
                                }
                                ?>
                            </td>
                            <?php
                                if ($Settings->product_serial) {
                                    echo '<td>' . $row->serial_no . '</td>';
                                }
                            ?>
                            <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->unit_quantity)); ?></td>
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code; ?></td>
                            <?php
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_quantity * $row->net_unit_price); ?></td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                        </tr>
                        <?php echo $this->sma->taxAttrTBL_csi_dynamic($row, 'challan'); ?>
                    <?php
                            $r++;
                        endforeach;
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <?php
                    $col = 5;
                    if ($Settings->product_serial) { $col++; }
                    if ($Settings->product_discount && $inv->product_discount != 0) { $col++; }
                    if ($Settings->tax1 && $inv->product_tax > 0) { $col++; }
                    
                    $tcol = $col;
                    ?>
                    <tr>
                        <td colspan="<?= $tcol - 1; ?>" style="text-align:right; padding-right:10px; font-weight:bold;"><?= lang("total_amount"); ?> (<?= $default_currency->code; ?>)</td>
                        <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total+$inv->rounding+$return_sale->rounding) : ($inv->grand_total+$inv->rounding)); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $tcol - 1; ?>" style="text-align:right; padding-right:10px; font-weight:bold;"><?= lang("paid"); ?> (<?= $default_currency->code; ?>)</td>
                        <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $tcol - 1; ?>" style="text-align:right; padding-right:10px; font-weight:bold;"><?= lang("balance"); ?> (<?= $default_currency->code; ?>)</td>
                        <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney(($return_sale ? ($inv->grand_total+$return_sale->grand_total+$inv->rounding+$return_sale->rounding) : $inv->grand_total+$inv->rounding) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid)); ?></td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?php if ($inv->note || $inv->note != "") { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang("note"); ?>:</p>
                            <div><?= $this->sma->decode_html($inv->note); ?></div>
                        </div>
                    <?php } ?>
                </div>

                <div class="col-xs-7 pull-right">
                    <?php
                    if ($Settings->invoice_view == 1 && $Settings->view_tax_classification_on_challan) {
                        echo $this->sma->getTaxChallanReport($inv->id, $Settings);
                    } ?>
                    <div class="well well-sm">
                        <p>
                            <?= lang("created_by"); ?>: <?= $created_by->first_name . ' ' . $created_by->last_name; ?> <br>
                            <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <?php if ($payments) { ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th><?= lang("date"); ?></th>
                                        <th><?= lang("payment_reference"); ?></th>
                                        <th><?= lang("paid_by"); ?></th>
                                        <th><?= lang("amount"); ?></th>
                                        <th><?= lang("created_by"); ?></th>
                                        <th><?= lang("type"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment) { ?>
                                        <tr>
                                            <td><?= $this->sma->hrld($payment->date); ?></td>
                                            <td><?= $payment->reference_no; ?></td>
                                            <td><?= lang($payment->paid_by); ?></td>
                                            <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                            <td><?= $payment->first_name . ' ' . $payment->last_name; ?></td>
                                            <td><?= lang($payment->type); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <!-- <div class="buttons">
                <div class="btn-group btn-group-justified">
                    <div class="btn-group">
                        <a href="<?= site_url('sales/order_as_pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
    });
</script>
