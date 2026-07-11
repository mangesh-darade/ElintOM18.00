<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script src="<?= base_url('Themes/default/assets/js/core.js'); ?>"></script>
<?php

$user_warehouse = $this->session->userdata('warehouse_id');
$v = $v1 = "";

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

if ($this->input->post('gstn_no')) {
    $v .= "&gstn_no=" . $this->input->post('gstn_no');
}
if ($this->input->post('start_date')) {
    $v1 = $v;
    $st = $this->sma->fld($this->input->post('start_date')) . ":00";
    $v1 .= "&start_date=" . strtotime($st);
    $v .= "&start_date=" . $this->input->post('start_date');
    if (empty($this->input->post('end_date'))) {
        $v .= "&end_date=" . date("d/m/Y") . ' 23:55';
        $_POST['end_date'] = date("d/m/Y") . ' 23:55';
    }
}


if ($this->input->post('end_date')) {
    $et = $this->sma->fld($this->input->post('end_date')) . ":00";
    $v1 .= "&end_date=" . strtotime($et);

    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>
$(document).ready(function () {
    $("#form").hide();
    function formatInteger(x) {
    // if (!x)
    return parseInt(x)+'%';
}

    $("#clear_customer").click(function () {
        $("#customer").select2("val", "");
    });

    var oTable = $('#VatReportData').dataTable({
        "aaSorting": [[0, "desc"]],
        "aLengthMenu": [[10, 25, 50, 100, 500, 1000, -1], [10, 25, 50, 100, 500, 1000, "All"]],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        'bProcessing': true, 'bServerSide': true,
        'sAjaxSource': '<?= site_url('reports_new/getSalesVatReportUAE/?v=1') ?>',
       'fnServerData': function (sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?= $this->security->get_csrf_token_name() ?>",
                "value": "<?= $this->security->get_csrf_hash() ?>"
            });

            // collect filter form data and send it with AJAX
            aoData.push({ name: 'reference_no', value: $('#reference_no').val() });
            aoData.push({ name: 'user', value: $('#user').val() });
            aoData.push({ name: 'customer', value: $('#customer').val() });
            aoData.push({ name: 'biller', value: $('#biller').val() });
            aoData.push({ name: 'warehouse', value: $('#warehouse').val() });
            aoData.push({ name: 'gstn_no', value: $('input[name="gstn_no"]').val() });
            aoData.push({ name: 'start_date', value: $('#start_date').val() });
            aoData.push({ name: 'end_date', value: $('#end_date').val() });

            $.ajax({
                'dataType': 'json',
                'type': 'POST',
                'url': sSource,
                'data': aoData,
                'success': fnCallback
            });
          },
        'fnRowCallback': function (nRow, aData, iDisplayIndex) {
            nRow.id = aData[18];
            return nRow;
        },
        "aoColumns": [
            null, null, null, null, null, null, null,
            {
                "mRender": function (data, type, row) {
                    if (!data) return '';

                    return data.split(', ').map(function (item) {
                        // Match the last (...) in the string (e.g., (pq 45.000), (39.000))
                        const lastBracketMatch = item.match(/\(([^()]*)\)(?!.*\([^()]*\))/);
                        if (lastBracketMatch) {
                            const fullMatch = lastBracketMatch[0];  // entire (..)
                            const inner = lastBracketMatch[1];      // content inside (...)

                            // Extract the last number inside that string (for example: 'pq 45.000' or just '45.000')
                            const matchAmount = inner.match(/([\d.]+)$/);
                            if (matchAmount) {
                                const raw = parseFloat(matchAmount[1]);
                                const formatted = currencyFormat(raw).replace(/[\n\r\s]+/g, '');
                                item = item.replace(fullMatch, `(${formatted})`);
                            }
                        }

                        // Format the trailing -1.00 or 1.00 quantity
                        item = item.replace(/-(\d+\.\d+)$|(\d+\.\d+)$/, function (match, negQty, posQty) {
                            const value = parseFloat(negQty || posQty);
                            const formatted = formatQuantity(value).replace(/[\n\r\s]+/g, '');
                            return (negQty ? '-' : '') + formatted;
                        });

                        return item;
                    }).join(', ');
                },
                "bSearchable": false
            },
            {"mRender": currencyFormat,"bSearchable": false},
            {"mRender": formatInteger, "sClass": "text-center"},
            {"mRender": currencyFormat,"bSearchable": false},
            {"mRender": currencyFormat,"bSearchable": false},
            {"mRender": currencyFormat,"bSearchable": false},
            {"mRender": currencyFormat,"bSearchable": false},
            {"mRender": currencyFormat,"bSearchable": false},
            {"mRender": currencyFormat,"bSearchable": false},
            null,
            null
        ]

    }).fnSetFilteringDelay().dtFilter([
        {
            column_number: 0,
            filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]",
            filter_type: "text",
            data: []
        },
    ], "footer");

    $('.toggle_down').click(function () {
        $("#form").slideDown();
        return false;
    });
    $('.toggle_up').click(function () {
        $("#form").slideUp();
        return false;
    });
});
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang('Sales Transaction Report') ?></h2>

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
                <!-- <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li> -->
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <!-- <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li> -->
            </ul>
        </div>
    </div>

    <p class="introtext"><?= lang('customize_report') ?></p>

    <div class="box-content">
    <div id="form">
<?php echo form_open("reports_new/sales_vat_report_uae"); ?>
<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <label><?= lang("reference_no"); ?></label>
            <?= form_input('reference_no', set_value('reference_no'), 'class="form-control" id="reference_no"') ?>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            <label><?= lang("created_by"); ?></label>
            <?php
                $us[""] = lang('select') . ' ' . lang('user');
                foreach ($users as $user) {
                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                }
                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                ?>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            <label><?= lang("customer"); ?></label>
            <?= form_input('customer', set_value('customer'), 'class="form-control" id="customer"') ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <label><?= lang("biller"); ?></label>
            <?php
                $bl[""] = lang('select') . ' ' . lang('biller');
                foreach ($billers as $biller) {
                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                }
                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                ?>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            <label><?= lang("warehouse"); ?></label>
            <?php
                $permisions_werehouse = explode(",", $user_warehouse);
                $wh[""] = lang('select') . ' ' . lang('warehouse');
                foreach ($warehouses as $warehouse) {
                    if ($Owner || $Admin) {
                        $wh[$warehouse->id] = $warehouse->name;
                    } else if (in_array($warehouse->id, $permisions_werehouse)) {
                        $wh[$warehouse->id] = $warehouse->name;
                    }
                }
                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                ?>
        </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group choose-date hidden-xs">
            <div class="controls">
                <?= lang("Start-End Date", "date_range_sales"); ?>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="text" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] . '-' . $_POST['end_date'] : ""; ?>" id="daterange_new" class="form-control">
                    <span class="input-group-addon" style="display:none;"><i class="fa fa-chevron-down"></i></span>
                    <input type="hidden" name="start_date" id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ""; ?>">
                    <input type="hidden" name="end_date" id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ""; ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <?= lang("TRN No", "gstn_no"); ?>
            <?= form_input('gstn_no', set_value('gstn_no'), 'class="form-control"') ?>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="controls">
        <?= form_submit('submit_report', lang("submit"), 'class="btn btn-primary"') ?>
        <a href="<?= base_url('reports_new/sales_vat_report_uae'); ?>" class="btn btn-success"><?= lang('reset') ?></a>
    </div>
</div>
<?php echo form_close(); ?>
</div>


        <div class="clearfix"></div>

        <div class="table-responsive">
            <table id="VatReportData" class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>
                        <th><?= lang("date") ?></th>
                        <th><?= lang("Invoice_no") ?></th>
                        <th><?= lang("reference_no") ?></th>
                        <th><?= lang("biller") ?></th>
                        <th><?= lang("customer") ?></th>
                        <th><?= lang("state") ?></th>
                        <th><?= lang("TRN Number") ?></th>
                        <th><?= lang("Products_Qty") ?></th>
                        <th><?= lang("Item_Tax_Amount") ?></th>
                        <th><?= lang("Tax_Value (%)") ?></th>
                        <th><?= lang("Grand_Total") ?></th>
                        <th><?= lang("Discount") ?></th>
                        <th><?= lang("Taxable_Amount") ?></th>
                        <th><?= lang("Total_Invoice_Tax_Amount") ?></th>
                        <th><?= lang("Paid") ?></th>
                        <th><?= lang("Balance") ?></th>
                        <th><?= lang("Payment_Method") ?></th>
                        <th><?= lang("Payment_Status") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="19" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                    </tr>
                </tbody>
                <tfoot class="dtFilter">
                    <tr class="active">
                        <th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                        <th></th><th></th><th></th><th></th>
                        <th></th><th></th><th></th><th></th>
                        <th></th><th></th><th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
#VatReportData td:nth-child(12),
#VatReportData td:nth-child(13),
#VatReportData td:nth-child(14),
#VatReportData td:nth-child(15),
#VatReportData td:nth-child(16),
#VatReportData td:nth-child(17) {
    text-align: right;
}
</style>
<script type="text/javascript">
    $(document).ready(function () {
        // $('#xls').click(function (event) {
        //     event.preventDefault();
        //     window.location.href = "<?=site_url('reports_new/getSalesVatReportUAE/0/xls/?v=1' . $v)?>";
        //     return false;
        // });
        $('#xls').click(function (event) {
    event.preventDefault();

    var params = [];
    params.push('xls=1');
    params.push('reference_no=' + encodeURIComponent($('#reference_no').val()));
    params.push('user=' + encodeURIComponent($('#user').val()));
    params.push('customer=' + encodeURIComponent($('#customer').val()));
    params.push('biller=' + encodeURIComponent($('#biller').val()));
    params.push('warehouse=' + encodeURIComponent($('#warehouse').val()));
    params.push('gstn_no=' + encodeURIComponent($('input[name="gstn_no"]').val()));
    params.push('start_date=' + encodeURIComponent($('#start_date').val()));
    params.push('end_date=' + encodeURIComponent($('#end_date').val()));

    var url = "<?= site_url('reports_new/getSalesVatReportUAE') ?>?" + params.join('&');
    window.location.href = url;
});


    });
</script>
<style>
    #VatReportData td:nth-child(17) {
        text-align: left !important;
    }
    #VatReportData td:nth-child(9),
    #VatReportData td:nth-child(11){
        text-align: right !important;
    }
</style>
