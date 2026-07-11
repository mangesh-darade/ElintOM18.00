<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
$(document).ready(function () {
    var oTable = $('#TransHistoryData').dataTable({
        "aaSorting": [[1, "desc"]],
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "<?= site_url('reports/getTransactionHistory') ?>",
        "fnServerData": function (sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?= $this->security->get_csrf_token_name() ?>",
                "value": "<?= $this->security->get_csrf_hash() ?>"
            });
            $.ajax({
                dataType: 'json',
                type: 'POST',
                url: sSource,
                data: aoData,
                success: fnCallback
            });
        },
        "aoColumns": [
            {"bSortable": false, "bVisible": false}, // ID
            {"bSortable": false}, // action_id
            {"mRender": fld}, // Date
            null, // Action
            null, // Product
            null, // Variant
            null, // Warehouse
            null, // Supplier
            null, // Biller
            null, // Customer
            {"mRender": formatQuantity}, // Qty Before
            {"mRender": formatQuantity}, // Qty
            {"mRender": formatQuantity}, // Balance Qty
            {"mRender": currencyFormat}, // Price
            {"mRender": currencyFormat}, // Net Price
            {"mRender": currencyFormat}, // Cost
            {"mRender": currencyFormat}, // Net Cost
            {"mRender": currencyFormat}, // MRP
            {"mRender": currencyFormat}, // Net MRP
            null, // Tax Name
            {"mRender": currencyFormat}, // Total Tax Amt
            {"mRender": currencyFormat}, // Total Paid
            null  // Created By
        ]
    });
});
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-history"></i><?= lang('Transaction_History'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="TransHistoryData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped dfTable reports-table">
                        <thead>
                        <tr class="active">
                            <th><?= lang("id"); ?></th>
                            <th><?= lang("action id"); ?></th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("action"); ?></th>
                            <th><?= lang("product"); ?></th>
                            <th><?= lang("variant"); ?></th>
                            <th><?= lang("warehouse"); ?></th>
                            <th><?= lang("supplier"); ?></th>
                            <th><?= lang("biller"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("qty_before"); ?></th>
                            <th><?= lang("quantity"); ?></th>
                            <th><?= lang("balance_qty"); ?></th>
                            <th><?= lang("price"); ?></th>
                            <th><?= lang("net_price"); ?></th>
                            <th><?= lang("cost"); ?></th>
                            <th><?= lang("net_cost"); ?></th>
                            <th><?= lang("mrp"); ?></th>
                            <th><?= lang("net_mrp"); ?></th>
                            <th><?= lang("tax_name"); ?></th>
                            <th><?= lang("total_tax_amt"); ?></th>
                            <th><?= lang("total_paid"); ?></th>
                            <th><?= lang("created_by"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="22" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
