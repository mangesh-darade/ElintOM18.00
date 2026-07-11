<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css" media="screen">

    /* ===== Mobile-specific layout for purchases ===== */
    .purchases-mobile .box {
        border-radius: 0;
        box-shadow: none;
    }

    .purchases-mobile .box-header {
        padding: 10px 12px;
    }

    .purchases-mobile #POData {
        font-size: 13px;
    }

    /* Hide checkbox column */
    .purchases-mobile #POData th:nth-child(1),
    .purchases-mobile #POData td:nth-child(1) {
        display: none !important;
    }

    @media (max-width: 768px) {
        .purchases-mobile #POData th,
        .purchases-mobile #POData td {
            white-space: nowrap;
        }

        .purchases-mobile #POData th:nth-child(4),
        .purchases-mobile #POData td:nth-child(4) {
            max-width: 150px;
            white-space: normal;
        }

        /* Amount column center */
        .purchases-mobile #POData th:nth-child(6),
        .purchases-mobile #POData td:nth-child(6) {
            text-align: center;
        }
    }

    /* Merged Amount column styling */
    .amount-cell, .amount-footer {
        display: flex;
        flex-direction: column;
        line-height: 1.4;
        font-size: 12px;
    }
    .amount-row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
    }
    .amount-label {
        font-weight: 600;
        color: #555;
    }
    .amount-value {
        text-align: right;
        font-family: monospace;
    }
    .footer-amount-row {
        border-bottom: 1px dashed #ccc;
        padding: 2px 0;
    }
    .footer-amount-row:last-child {
        border-bottom: none;
    }

    /* Merged Status column styling */
    .status-cell {
        display: flex;
        flex-direction: column;
        gap: 5px;
        font-size: 12px;
    }
    .status-sub-label {
        font-weight: 600;
        color: #777;
        font-size: 10px;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 2px;
    }
    .status-item {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
        border-bottom: 1px dashed #eee;
        padding-bottom: 3px;
    }
    .status-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    /* ===== DataTables top bar: Show entries + Search stacked ===== */
    .purchases-mobile .dataTables_wrapper > .row:first-child {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    .purchases-mobile .dataTables_wrapper > .row:first-child > div {
        width: 100% !important;
        float: none !important;
        padding: 0 19px;
    }
    .purchases-mobile .dataTables_length,
    .purchases-mobile .dataTables_filter {
        float: none;
        display: block;
        width: 100%;
        margin: 5px 0;
    }
    .purchases-mobile .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
        margin: 0;
        font-weight: normal;
    }
    .purchases-mobile .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        width: 100%;
    }
    .purchases-mobile .dataTables_filter input[type="search"] {
        flex: 1;
        padding: 4px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .purchases-mobile .box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
    }
    .purchases-mobile .box-header h2 {
        margin: 0;
        flex: 1;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 15px;
    }
    .purchases-mobile .box-header .box-icon {
        flex-shrink: 0;
    }
    .box .box-header h2 i {
        margin: -10px 20px -10px -12px;
    }
    .modal-lg {
        width: 95%!important;
    }
    .col-xs-8.pull-right{
        width:100%!important;
    }
    .col-xs-6 {
    width: 46%;
    max-width: 50%;
    overflow: scroll;
    }
</style>

<script>
    $(document).ready(function () {
        var oTable = $('#POData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('purchases/getPurchases' . ($warehouse_id ? '/' .  str_replace(",","_",$warehouse_id) : ''))?>',
            'fnServerData': function (sSource, aoData, fnCallback) {                 
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                <?php if ($Settings->display_job_work) { ?>
                var sel = $('#psupplier').val();
                var supplier_all = (sel === 'all') ? 1 : 0;
                var supplier = (sel && sel !== 'all') ? sel : '';
                aoData.push({ name: 'supplier', value: supplier });
                aoData.push({ name: 'supplier_all', value: supplier_all });
                aoData.push({ name: 'all_purchase', value: $('#all_purchase').is(':checked') ? 1 : 0 });
                <?php } ?>
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox},    // 0: checkbox (hidden via CSS)
                {"mRender": fld},                              // 1: date
                null,                                          // 2: ref_no
                null,                                          // 3: supplier
                {                                              // 4: Status (Pur. Sts + Pay. Sts merged)
                    "mRender": function(data, type, row) {
                        return '<div class="status-cell">' +
                               '<div class="status-item"><span class="status-sub-label">Purchase</span>' + row_status(row[4], type, row) + '</div>' +
                               '<div class="status-item"><span class="status-sub-label">Payment</span>' + pay_status(row[8], type, row) + '</div>' +
                               '</div>';
                    }
                },
                {                                              // 5: Amount (merged GT+PD+BAL)
                    "mRender": function(data, type, row) {
                        return '<div class="amount-cell">' +
                               '<div class="amount-row"><span class="amount-label">GT –</span> <span class="amount-value">' + currencyFormat(row[5]) + '</span></div>' +
                               '<div class="amount-row"><span class="amount-label">PD –</span> <span class="amount-value">' + currencyFormat(row[6]) + '</span></div>' +
                               '<div class="amount-row"><span class="amount-label">BAL –</span> <span class="amount-value">' + currencyFormat(row[7]) + '</span></div>' +
                               '</div>';
                    }
                },
                {"bVisible": false},                           // 6: paid (hidden, used in footer)
                {"bVisible": false},                           // 7: balance (hidden, used in footer)
                {"bVisible": false},                           // 8: payment_status (merged into Status col)
                {"bVisible": false},                           // 9: attachment/fa-chain (hidden)
                {"bVisible": false}                            // 10: actions (hidden)
            ],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {               
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "purchase_link";
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total   += parseFloat(aaData[aiDisplay[i]][5]);
                    paid    += parseFloat(aaData[aiDisplay[i]][6]);
                    balance += parseFloat(aaData[aiDisplay[i]][7]);                     
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = '<div class="amount-footer">' +
                                      '<div class="amount-row footer-amount-row"><span class="amount-label">GT –</span> <span class="amount-value">' + currencyFormat(parseFloat(total)) + '</span></div>' +
                                      '<div class="amount-row footer-amount-row"><span class="amount-label">PD –</span> <span class="amount-value">' + currencyFormat(parseFloat(paid)) + '</span></div>' +
                                      '<div class="amount-row footer-amount-row"><span class="amount-label">BAL –</span> <span class="amount-value">' + currencyFormat(parseFloat(balance)) + '</span></div>' +
                                      '</div>';
            },
            "fnInitComplete": function() {
                $('.purchases-mobile .dataTables_wrapper > .row:first-child').append('<div class="legend-well" style="padding: 0 19px; margin: 5px 0;"><div class="well well-sm" style="margin-bottom: 0; padding: 5px 10px; font-size:14px;"><strong>Abbreviation:</strong> GT: <?= lang('grand_total'); ?> | PD: <?= lang('paid'); ?> | BAL: <?= lang('balance'); ?></div></div>');
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
        ], "footer");

        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });

        <?php if ($Settings->display_job_work) { ?>
        $('#psupplier').select2({
            minimumInputLength: 0,
            allowClear: true,
            placeholder: "<?= lang('select') . ' ' . lang('supplier'); ?>",
            ajax: {
                url: site.base_url + "suppliers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) { return { term: term, limit: 10 }; },
                results: function (data, page) {
                    var results = data.results || [];
                    results.unshift({ id: 'all', text: "<?= lang('All Suppliers'); ?>" });
                    return { results: results };
                }
            }
        });

        $('#filter_submit').on('click', function (e) {
            e.preventDefault();
            oTable.fnDraw();
        });    
        <?php } ?>
    });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('purchases/purchase_actions', 'id="action-form"');
}
?>
<div class="box purchases-mobile">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star"></i><?=lang('purchases') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('all_warehouses'))  . ')';?> 
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <?php if ($Settings->display_job_work) { ?>
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
                <?php } ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?=site_url('purchases/add')?>">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_purchase')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?=lang('export_to_pdf')?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?=lang("delete_purchases")?></b>"
                                data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?=lang('delete_purchases')?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang("warehouses")?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?=site_url('Purchases_Mobile')?>"><i class="fa fa-building-o"></i> <?=lang('all_warehouses')?></a></li>
                            <li class="divider"></li>
                            <?php
                            $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                            foreach ($warehouses as $warehouse) {
                                if($Owner || $Admin  ){
                                    echo '<li><a href="' . site_url('Purchases_Mobile/index/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                }elseif (in_array($warehouse->id,$permisions_werehouse)) {
                                    echo '<li><a href="' . site_url('Purchases_Mobile/index/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                }
                            }    
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php if ($Settings->display_job_work) { ?>
                <div id="form" class="well well-sm">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label><?= lang('suppliers'); ?></label>
                                <input type="hidden" id="psupplier" name="psupplier" class="form-control" style="width:100%"/>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="all_purchase" value="1"> <?= lang('All purchases'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button id="filter_submit" class="btn btn-primary"><?= $this->lang->line("submit") ?></button>
                    </div>
                </div>
                <?php } ?>

                <div class="table-responsive">
                    <table id="POData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th><?= lang("date"); ?></th>
                                <th><?= lang("ref_no"); ?></th>
                                <th><?= lang("supplier"); ?></th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="11" class="dataTables_empty"><?=lang('loading_data_from_server');?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>
