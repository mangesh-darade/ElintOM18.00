<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                        <p class="bold">
                            <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                            <?= lang("return_purchase_no"); ?>: <?= $inv->id; ?><br>
                            <?= lang("return_ref"); ?>: <?= $inv->return_purchase_ref; ?><br>
                            <?= lang("purchase_reference"); ?>: <?= $inv->reference_no; ?>
                            <?php if (!empty($purchase)) { ?>
                                <a data-target="#myModal2" data-toggle="modal" href="<?= site_url('purchases/modal_view/' . $purchase->id) ?>"><i class="fa fa-external-link no-print"></i></a>
                            <?php } ?><br>
                            <?= lang("status"); ?>: <?= lang($inv->status); ?><br>
                            <?= lang("payment_status"); ?>: <?= lang($inv->payment_status); ?>
                        </p>
                    </div>
                    <div class="col-xs-7 text-right order_barcodes">
                        <?= $barcode; ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <strong><?= lang("from"); ?></strong>
                    <h2 style="margin-top:10px;"><?= $Settings->site_name; ?></h2>
                    <?php if (is_array($warehouse)) { foreach ($warehouse as $ware) { ?>
                        <?= ($ware->name != '') ? '<b> Warehouse : </b> ' . $ware->name : '' ?>
                    <?php } } elseif (!empty($warehouse)) { ?>
                        <?= ($warehouse->name != '') ? '<b> Warehouse : </b> ' . $warehouse->name : '' ?>
                    <?php } ?>
                </div>
                <div class="col-xs-6">
                    <strong><?= lang("Supplier Details"); ?></strong>
                    <h2 style="margin-top:10px;"><?= $supplier->company ? $supplier->company : $supplier->name; ?></h2>
                    <?= $supplier->company ? "" : "Attn: " . $supplier->name ?>
                    <address>
                        <?= ($supplier->address != '') ? '<b> Address : </b> ' . $supplier->address . ', <br/>' : '' ?>
                        <?= ($supplier->city != '') ? $supplier->city . ' - ' : '' ?> <?= ($supplier->postal_code != '') ? $supplier->postal_code . ', ' : '' ?>
                        <?= ($supplier->state != '') ? $supplier->state . ', ' : '' ?> <?= ($supplier->country != '') ? $supplier->country . '. ' : '' ?>
                        <?= ($supplier->phone != '') ? '</br><b> ' . lang("tel") . ' : </b> ' . $supplier->phone : '' ?>
                        <?= ($supplier->email != '') ? '</br><b> ' . lang("email") . ' : </b> ' . $supplier->email : '' ?>
                    </address>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">
                    <thead>
                        <tr>
                            <th><?= lang("no"); ?></th>
                            <th><?= lang("description"); ?></th>
                            <th><?= lang("batch_number"); ?></th>
                            <?php if ($Owner || $Admin || ($GP['products-cost'] == '1')) { ?>
                                <th><?= lang("unit_cost"); ?></th>
                            <?php } ?>
                            <th><?= lang("return_quantity"); ?></th>
                            <?php if ($Owner || $Admin || ($GP['products-cost'] == '1')) { ?>
                                <th><?= lang("subtotal"); ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $r = 1;
                        if (!empty($rows)) {
                            foreach ($rows as $row) {
                                $qty = abs($row->unit_quantity);
                                ?>
                                <tr>
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    </td>
                                    <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $row->batch_number; ?></td>
                                    <?php if ($Owner || $Admin || ($GP['products-cost'] == '1')) { ?>
                                        <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->real_unit_cost); ?></td>
                                    <?php } ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($qty); ?></td>
                                    <?php if ($Owner || $Admin || ($GP['products-cost'] == '1')) { ?>
                                        <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                                    <?php } ?>
                                </tr>
                                <?php
                                $r++;
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="100%" style="text-align:right; font-weight:bold;">
                                <?= lang("return_amount"); ?>: <?= $this->sma->formatMoney($inv->grand_total); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if ($inv->note || $inv->note != "") { ?>
                <div class="well well-sm">
                    <p class="bold"><?= lang("return_note"); ?>:</p>
                    <div><?= $this->sma->decode_html($inv->note); ?></div>
                </div>
            <?php } ?>

            <?php if (!empty($payments)) { ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="well well-sm">
                            <p class="bold"><?= lang("payments"); ?>:</p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= lang("date"); ?></th>
                                            <th><?= lang("reference_no"); ?></th>
                                            <th><?= lang("amount"); ?></th>
                                            <th><?= lang("paid_by"); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment) { ?>
                                            <tr>
                                                <td><?= $this->sma->hrld($payment->date); ?></td>
                                                <td><?= $payment->reference_no; ?></td>
                                                <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                                <td><?= lang($payment->paid_by); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="well well-sm">
                <p>
                    <?= lang("created_by"); ?>: <?= $user->first_name . ' ' . $user->last_name; ?> <br>
                    <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?>
                </p>
            </div>
        </div>
    </div>
</div>
