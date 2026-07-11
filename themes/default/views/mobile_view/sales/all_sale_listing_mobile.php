<?php defined('BASEPATH') OR exit('No direct script access allowed');

$v = "";
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
} else {
    $v .= ($user_warehouse == '0' || $user_warehouse == NULL) ? '' : "&warehouse=" . str_replace(",", "_", $user_warehouse);
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('serial')) {
    $v .= "&serial=" . $this->input->post('serial');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
if ($this->input->post('TypeOfModeSale')) {
    $v .= "&TypeOfModeSale=" . $this->input->post('TypeOfModeSale');
}
if ($this->input->post('sale_status')) {
    if (is_array($this->input->post('sale_status'))) {
        foreach ($this->input->post('sale_status') as $status) {
            $v .= "&sale_status[]=" . $status;
        }
    } else {
        $v .= "&sale_status=" . $this->input->post('sale_status');
    }
}
?>

<style type="text/css" media="screen">
    /* Hide specific action buttons for mobile */
    .delete_Offline, .add_payment_Sale, .add_payment_Offline, .edit_Offline, 
    .add_delivery_Sale, .add_delivery_Offline, .view_payments_Offline, 
    .download_POS, .email_Eshop, .return_Sale, .return_Offline, 
    .duplicate_Eshop, .SaleDetailModel {
        display: none;
    }
    .SaleDetailModel_POS {
        display: block;
    }

    /* ===== Mobile-specific layout for sales ===== */
    .sales-mobile .box {
        border-radius: 0;
        box-shadow: none;
    }

    .sales-mobile .box-header {
        padding: 10px 12px;
    }

    .sales-mobile #SLData {
        font-size: 13px;
    }

    /* Hide checkbox column */
    .sales-mobile #SLData th:nth-child(1),
    .sales-mobile #SLData td:nth-child(1) {
        display: none !important;
    }

    /* Hide fa-chain (attachment) column */
    .sales-mobile #SLData th:nth-child(12),
    .sales-mobile #SLData td:nth-child(12) {
        display: none !important;
    }

    /* Hide non-essential columns on small screens:
       Keep:
         1: checkbox (selection)
         2: date
         3: reference_no
         5: customer
         6: sale_status
         7: grand_total
         8: paid
         9: balance
         10: payment_status
         15: actions
       Hide:
         4: Invoice_no
         7: biller (moved to keep 7 as grand_total)
         11: attachment
         13: hidden
         14: Type
    */
    @media (max-width: 768px) {
        .sales-mobile #SLData th:nth-child(4),
        .sales-mobile #SLData td:nth-child(4),
        .sales-mobile #SLData th:nth-child(6),
        .sales-mobile #SLData td:nth-child(6),
        .sales-mobile #SLData th:nth-child(11),
        .sales-mobile #SLData td:nth-child(11),
        .sales-mobile #SLData th:nth-child(13),
        .sales-mobile #SLData td:nth-child(13),
        .sales-mobile #SLData th:nth-child(14),
        .sales-mobile #SLData td:nth-child(14) {
            display: none;
        }

        .sales-mobile #SLData th,
        .sales-mobile #SLData td {
            white-space: nowrap;
        }

        .sales-mobile #SLData th:nth-child(5),
        .sales-mobile #SLData td:nth-child(5) {
            max-width: 150px;
            white-space: normal;
        }

        /* Adjust column indices after hiding/merging */
        .sales-mobile #SLData th:nth-child(7),
        .sales-mobile #SLData td:nth-child(7),
        .sales-mobile #SLData th:nth-child(8),
        .sales-mobile #SLData td:nth-child(8) {
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
    .sales-mobile .dataTables_wrapper > .row:first-child {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    .sales-mobile .dataTables_wrapper > .row:first-child > div {
        width: 100% !important;
        float: none !important;
        padding: 0 19px;
    }
    .sales-mobile .dataTables_length,
    .sales-mobile .dataTables_filter {
        float: none;
        display: block;
        width: 100%;
        margin: 5px 0;
    }
    .sales-mobile .sales-dt-topbar {
        display: block;
        padding: 6px 8px;
        background: #fff;
        border-bottom: 1px solid #ddd;
    }
    .sales-mobile .sales-dt-topbar .dataTables_length,
    .sales-mobile .sales-dt-topbar .dataTables_filter {
        width: 100%;
    }
    .sales-mobile .sales-dt-topbar .dataTables_filter input {
        width: 100%;
        box-sizing: border-box;
    }
    /* Put length select & label inline */
    .sales-mobile .dataTables_length label {
        align-items: center;
        gap: 19px;
        white-space: nowrap;
        margin: 0;
        font-weight: normal;
        display: flex;
    }
    /* Search label + input inline */
    .sales-mobile .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        width: 100%;
    }
    .sales-mobile .dataTables_filter input[type="search"] {
        flex: 1;
        padding: 4px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    /* Hide the default DT top/bottom info line */
    .sales-mobile .dataTables_info { padding-top: 4px; font-size: 12px; }

    /* ===== box-header: title + icon inline ===== */
    .sales-mobile .box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
    }
    .sales-mobile .box-header h2 {
        margin: 0;
        flex: 1;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 15px;
    }
    .sales-mobile .box-header .box-icon {
        flex-shrink: 0;
    }
    .box .box-header h2 i {
        margin: -10px 20px -10px -12px;
    }

    /* ===== Mobile form adjustments ===== */
    .sales-mobile .form-group {
        margin-bottom: 10px;
    }
    .sales-mobile .form-group label {
        font-weight: normal;
        margin-bottom: 3px;
    }
    .sales-mobile .btn {
        padding: 6px 12px;
        font-size: 13px;
    }
    .col-xs-8.pull-right{
        width:100%!important;
    }
    button.btn.btn-xs.btn-default.no-print.pull-right{
        margin-top: -2px;
    }
</style>

<script>
    $(document).ready(function () {
        $('#form').hide();
               
        var oTable = $('#SLData').dataTable({
            "aaSorting": [[0, "asc"], [1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('sales/all_sale_lists_filter/?v=1'. $v)?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.setAttribute('data-return-id', aData[11]);
                nRow.className = "invoice_link re"+aData[11];
                return nRow;
            },
            "aoColumns": [
                {"bSortable": false,"mRender": checkbox},    // 0: checkbox (hidden)
                {"mRender": fld},                            // 1: date
                null,                                        // 2: reference_no
                null,                                        // 3: Invoice_no
                null,                                        // 4: biller
                null,                                        // 5: customer
                {                                            // 6: Status (Sale Sts + Pay Sts merged)
                    "mRender": function(data, type, row) {
                        return '<div class="status-cell">' +
                               '<div class="status-item"><span class="status-sub-label">Sale </span>' + row_status(row[6], type, row) + '</div>' +
                               '<div class="status-item"><span class="status-sub-label">Payment </span>' + pay_status(row[10], type, row) + '</div>' +
                               '</div>';
                    }
                },
                {                                            // 7: Amount (GT+PD+BAL merged)
                    "mRender": function(data, type, row) {
                        return '<div class="amount-cell">' +
                               '<div class="amount-row"><span class="amount-label">GT –</span> <span class="amount-value">' + currencyFormat(row[7]) + '</span></div>' +
                               '<div class="amount-row"><span class="amount-label">PD –</span> <span class="amount-value">' + currencyFormat(row[8]) + '</span></div>' +
                               '<div class="amount-row"><span class="amount-label">BAL –</span> <span class="amount-value">' + currencyFormat(row[9]) + '</span></div>' +
                               '</div>';
                    }
                },
                {"bVisible": false},                         // 8: paid (hidden, used in footer)
                {"bVisible": false},                         // 9: balance (hidden, used in footer)
                {"bVisible": false},                         // 10: payment_status (merged into Status)
                {"bVisible": false},                         // 11: attachment (hidden)
                {"bVisible": false},                         // 12: hidden
                {"bSortable": false},                        // 13: Type
                {"bVisible": false}                          // 14: actions (hidden)
            ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][7]);
                    paid += parseFloat(aaData[aiDisplay[i]][8]);
                    balance += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = '<div class="amount-footer">' +
                                      '<div class="amount-row footer-amount-row"><span class="amount-label">GT –</span> <span class="amount-value">' + currencyFormat(parseFloat(gtotal)) + '</span></div>' +
                                      '<div class="amount-row footer-amount-row"><span class="amount-label">PD –</span> <span class="amount-value">' + currencyFormat(parseFloat(paid)) + '</span></div>' +
                                      '<div class="amount-row footer-amount-row"><span class="amount-label">BAL–</span> <span class="amount-value">' + currencyFormat(parseFloat(balance)) + '</span></div>' +
                                      '</div>';
            },
            "fnInitComplete": function() {
                $('.sales-mobile .dataTables_wrapper > .row:first-child').append('<div class="legend-well" style="padding: 0 19px; margin: 5px 0;"><div class="well well-sm" style="margin-bottom: 0; padding: 5px 10px; font-size:14px;"><strong>Abbreviation:</strong> GT: <?= lang('grand_total'); ?> | PD: <?= lang('paid'); ?> | BAL: <?= lang('balance'); ?></div></div>');
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
        ], "footer");

        // Clear localStorage
        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            localStorage.removeItem('remove_slls');
        }

        <?php if ($this->session->userdata('remove_slls')) {?>
        if (localStorage.getItem('slitems')) {
            localStorage.removeItem('slitems');
        }
        if (localStorage.getItem('sldiscount')) {
            localStorage.removeItem('sldiscount');
        }
        if (localStorage.getItem('sltax2')) {
            localStorage.removeItem('sltax2');
        }
        if (localStorage.getItem('slref')) {
            localStorage.removeItem('slref');
        }
        if (localStorage.getItem('slshipping')) {
            localStorage.removeItem('slshipping');
        }
        if (localStorage.getItem('slwarehouse')) {
            localStorage.removeItem('slwarehouse');
        }
        if (localStorage.getItem('slnote')) {
            localStorage.removeItem('slnote');
        }
        if (localStorage.getItem('slinnote')) {
            localStorage.removeItem('slinnote');
        }
        if (localStorage.getItem('slcustomer')) {
            localStorage.removeItem('slcustomer');
        }
        if (localStorage.getItem('slbiller')) {
            localStorage.removeItem('slbiller');
        }
        if (localStorage.getItem('slcurrency')) {
            localStorage.removeItem('slcurrency');
        }
        if (localStorage.getItem('sldate')) {
            localStorage.removeItem('sldate');
        }
        if (localStorage.getItem('slsale_status')) {
            localStorage.removeItem('slsale_status');
        }
        if (localStorage.getItem('slpayment_status')) {
            localStorage.removeItem('slpayment_status');
        }
        <?php $this->sma->unset_data('remove_slls');} ?>

        $(document).on('click', '.sledit', function (e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });

        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });

        $('#sale_status').select2({
            allowClear: true,
            width: '100%'
        });
    });

    function resetSaleList() {
        window.location = "<?=base_url('sales/all_sale_lists')?>";
    }
</script>

<?php 
    $warehouse_id = $_POST['warehouse'];
    foreach ($warehouses as $warehouse) {
        if($warehouse->id == $_POST['warehouse']) {
            $warehousename = $warehouse->name;
        }
    }
?>

<div class="box sales-mobile">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-heart"></i><?=lang('All_Sales') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehousename : lang('all_warehouses')) . ')';?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
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
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?=site_url('sales/add')?>">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_sale')?>
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
                        <li>
                            <a href="#" id="export_invoice_to_excel" data-action="export_invoice_to_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('Export Sales Items to Excel')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="export_to_json" data-action="export_to_json">
                                <i class="fa fa-file-excel-o"></i> <?=lang('Export to Json')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="combine" data-action="combine">
                                <i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="combine_invoice" data-action="combine_invoice">
                                <i class="fa fa-file-pdf-o"></i> <?=lang('Combine_Invoice_To_Pdf')?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo"
                               title="<b><?=lang("delete_sales")?></b>"
                               data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                               data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?=lang('delete_sales')?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <!-- <p class="introtext"><?=lang('list_results');?></p> -->

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form">
                    <?php echo form_open("sales/all_sale_lists"); ?>
                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
                        
                        <?php
                        $GgroupView = 0;
                        if($this->session->userdata('group_id') == 1) $GgroupView = 1; 
                        elseif($this->session->userdata('group_id') == 2) $GgroupView = 1;
                        ?>
                        <div class="col-sm-6 col-md-4" <?php if($GgroupView == 0){ ?>style="display:none;"<?php } ?>>
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : $user_id), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select') . ' ' . lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("warehouse") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group choose-date">
                                <div class="controls">
                                    <?= lang("date_range", "date_range"); ?>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text"
                                               autocomplete="off"
                                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'].'-'.$_POST['end_date'] : "";?>"
                                               id="daterange_new" class="form-control">
                                        <input type="hidden" name="start_date" id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : "";?>">
                                        <input type="hidden" name="end_date" id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : "";?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <?= lang('Type_of_sales', 'Type_of_sales'); ?>
                                <?php 
                                $TypeOfModeSale = $this->config->item('TypeOfModeSale');
                                $ms[""] = lang('select') . ' ' . lang('type_of_sale');
                                foreach ($TypeOfModeSale as $KeyModeOfSale => $ValueModeOfSale) {
                                    $ms[$KeyModeOfSale] = $ValueModeOfSale;
                                }
                                echo form_dropdown('TypeOfModeSale', $ms, (isset($_POST['TypeOfModeSale']) ? $_POST['TypeOfModeSale'] : ""), 'class="form-control" id="TypeOfModeSale" data-placeholder="' . $this->lang->line("TypeOfModeSale") . " " . $this->lang->line("TypeOfModeSale") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="sale_status"><?= lang("Sale_status"); ?></label>
                                <?php
                                $ss["completed"] = lang('completed');
                                $ss["pending"] = lang('pending');
                                $ss["returned"] = lang('returned');
                                $ss["exchange"] = lang('Exchanged');
                                echo form_dropdown('sale_status[]', $ss, (isset($_POST['sale_status']) ? $_POST['sale_status'] : ""), 'class="form-control" id="sale_status" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("sale_status") . '" multiple="multiple"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> 
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                            <input type="button" id="report_reset" onclick="return resetSaleList();" data-value="<?=base_url('sales/all_sale_lists');?>" name="submit_report" value="Reset" class="btn btn-warning input-xs">        
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>

                <?php if ($Owner || $GP['bulk_actions']) {
                    echo form_open('sales/sale_actions', 'id="action-form"');
                } ?>
                
                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th><?= lang("date"); ?></th>
                                <th><?= lang("reference_no"); ?></th>
                                <th><?= lang("Invoice_no"); ?></th>
                                <th><?= lang("biller"); ?></th>
                                <th><?= lang("customer"); ?></th>
                                <th>Status</th>
                                <th><?= lang("Amount"); ?></th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                <th></th>
                                <th>Type</th>
                                <th style="display:none;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="15" class="dataTables_empty"><?= lang("loading_data"); ?></td>
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
                                <th></th>
                                <th></th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                                <th style="display:none;"></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="display:none;"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <?php if ($Owner || $GP['bulk_actions']) { ?>
                    <div style="display: none;">
                        <input type="hidden" name="form_action" value="" id="form_action"/>
                        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
                    </div>
                    <?=form_close()?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
