<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$warehouseIds = is_numeric($warehouse_id) ? '/'.$warehouse_id : '';
// Preserve filter values from GET (preferred) or POST so selections survive page refresh
$q = function($k){
    if (isset($_GET[$k]) && $_GET[$k] !== '') return $_GET[$k];
    if (isset($_POST[$k]) && $_POST[$k] !== '') return $_POST[$k];
    return null;
};
$preserve_start = $q('start_date');
$preserve_end = $q('end_date');
$preserve_reference = $q('reference_no');
$preserve_user = $q('user');
$preserve_customer = $q('customer');
$preserve_biller = $q('biller');
$preserve_warehouse = $q('warehouse');
$preserve_product = $q('product');
$preserve_serial = $q('serial');
$preserve_TypeOfModeSale = $q('TypeOfModeSale');
?>
<script>
    $(document).ready(function () {
        // start hidden: close the filter form by default
        $("#form").hide();
        
        ////////////////////////////////////////////////////////////
         var oTable = $('#POSData').dataTable({
            "aaSorting": [[0, "asc"], [1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('pos/getSales'.$warehouseIds) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                aoData.push({ "name": "product", "value": $('#report_product_id').val() });
                aoData.push({ "name": "reference_no", "value": $('#reference_no').val() });
                aoData.push({ "name": "user", "value": $('#filter_user').val() });
                aoData.push({ "name": "customer", "value": $('#filter_customer').val() });
                aoData.push({ "name": "biller", "value": $('#filter_biller').val() });
                aoData.push({ "name": "warehouse", "value": $('#filter_warehouse').val() });
                aoData.push({ "name": "serial", "value": $('#filter_serial').val() });
                aoData.push({ "name": "TypeOfModeSale", "value": $('#TypeOfModeSale').val() });
                aoData.push({ "name": "start_date", "value": $('#start_date').val() });
                aoData.push({ "name": "end_date", "value": $('#end_date').val() });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "receipt_link";
                return nRow;
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, {"mRender": fld}, null, null, null, null, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat},{"mRender": row_status}, {"mRender": row_status}, {"mRender": row_status}, {"bSortable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                   gtotal += parseFloat(aaData[aiDisplay[i]][6]);
                    paid += parseFloat(aaData[aiDisplay[i]][7]);
                    balance += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[7].innerHTML = currencyFormat(parseFloat(paid));
                nCells[8].innerHTML = currencyFormat(balance);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('Invoice_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text"},
            {column_number: 9, filter_default_label: "[<?=lang('sale_status');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('Delivery');?>]", filter_type: "text", data: []},
        ], "footer"); 

        // Filter button actions — submit as GET so page reloads and filters persist on load
        $(document).on('click', '#btn-filter', function (e) {
            e.preventDefault();
            // If hidden start/end are empty but visual daterange has a value, parse it.
            var dr = $('#daterange_new').val();
            if (( !$('#start_date').val() || !$('#end_date').val() ) && dr) {
                var parts = dr.split(' - ');
                if (parts.length === 2) {
                    $('#start_date').val(parts[0].trim());
                    $('#end_date').val(parts[1].trim());
                }
            }
            var params = {
                product: $('#report_product_id').val(),
                reference_no: $('#reference_no').val(),
                user: $('#filter_user').val(),
                customer: $('#filter_customer').val(),
                biller: $('#filter_biller').val(),
                warehouse: $('#filter_warehouse').val(),
                serial: $('#filter_serial').val(),
                TypeOfModeSale: $('#TypeOfModeSale').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            };
            var parts = [];
            for (var k in params) {
                if (params.hasOwnProperty(k) && params[k] !== null && params[k] !== '' ) {
                    parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k]));
                }
            }
            var qs = parts.join('&');
            var base = window.location.pathname;
            if (qs.length) {
                window.location.href = base + '?' + qs;
            } else {
                window.location.href = base;
            }
        });

        // Reset button clears filters and reloads page without params
        $(document).on('click', '#btn-reset', function (e) {
            e.preventDefault();
            window.location.href = window.location.pathname;
        });

        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });

        $(document).on('click', '.email_receipt', function (e) {

             e.preventDefault();

            var sid = $(this).attr('data-id');
            var ea = $(this).attr('data-email-address');
            var email = prompt("<?= lang("email_address"); ?>", ea);
            if (email != null) {
                $.ajax({
                    type: "post",
                    url: "<?= site_url('pos/email_receipt') ?>/" + sid,
                    data: { <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>", email: email, id: sid },
                    dataType: "json",
                        success: function (data) {
                        bootbox.alert(data.msg);
                       return true;
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_request_failed'); ?>');
                        return false;
                    }
                });
            }
        });
        
        
        setTimeout(function(){
            $('.link_delete_completed').hide();
            $('.link_add_payment_paid').hide();            
        }, 1000); 

        

        <?php if ($preserve_customer) { ?>
        $('#filter_customer').val("<?= $preserve_customer ?>").select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "customers/suggestions/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data.results[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        $('#filter_customer').val("<?= $preserve_customer ?>").trigger('change');
        <?php } else { ?>
        $('#filter_customer').select2({
            minimumInputLength: 1,
            data: [],
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        <?php } ?>

    });

</script>
<style>
    .modal-lg{width:90%}
</style> 

<div class="box">
    <div class="box-header">
        <?php
        // Prefer warehouse selected in the form (GET/POST) so header reflects user's selection
        $warehouse_display = lang('all_warehouses');
        if (!empty($preserve_warehouse)) {
            // $warehouses is a list of objects — find matching name
            foreach ($warehouses as $w) {
                if (isset($w->id) && $w->id == $preserve_warehouse) {
                    $warehouse_display = $w->name;
                    break;
                }
            }
        } elseif (!empty($warehouse_id) && is_numeric($warehouse_id) && isset($warehouse[$warehouse_id])) {
            $warehouse_display = $warehouse[$warehouse_id]->name;
        }
        $date_display = ($preserve_start ? ' - ' . $preserve_start . ' - ' . $preserve_end : '');
        ?>
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('pos_sales') . ' (' . $warehouse_display . ')' . $date_display;?>
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
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= site_url('pos') ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_sale') ?></a></li>
                        <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        <li><a href="#" id="pdf" data-action="export_pdf"><i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?></a></li>
                          <li>
                        <a href="#" id="export_invoice_to_excel" data-action="export_invoice_to_excel">
                            <i class="fa fa-file-excel-o"></i> <?=lang('Export Sales Items to Excel')?>
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
                        <li><a href="#" class="bpo" title="<b><?= $this->lang->line("delete_sales") ?></b>" data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_sales') ?></a></li>
                    </ul>
                </li>
                <?php // header warehouse selector intentionally hidden; warehouse filter lives inside the form ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div id="form">
                    <?php echo form_open("pos/sales"); ?>
                    <div class="row">
                        <div class="col-sm-4" style="display:none;">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_GET['sproduct']) ? $_GET['sproduct'] : (isset($_POST['sproduct']) ? $_POST['sproduct'] : "")), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= $preserve_product ? $preserve_product : '' ?>" id="report_product_id"/>
                            </div>
                        </div>
                        <div class="col-sm-4" style="display:none;">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', ($preserve_reference ? $preserve_reference : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>
                        <div class="col-sm-4" style="display:none;">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                if (!empty($users)) {
                                    foreach ($users as $user) {
                                        $us[$user->id] = $user->first_name . " " . $user->last_name;
                                    }
                                }
                                echo form_dropdown('user', $us, ($preserve_user ? $preserve_user : ''), 'class="form-control" id="filter_user"');
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', ($preserve_customer ? $preserve_customer : (isset($_POST['customer']) ? $_POST['customer'] : "")), 'class="form-control" id="filter_customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4" style="display:none;">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                if (!empty($billers)) {
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                }
                                echo form_dropdown('biller', $bl, ($preserve_biller ? $preserve_biller : ''), 'class="form-control" id="filter_biller"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('all_warehouses');
                                if (!empty($warehouses)) {
                                    foreach ($warehouses as $warehouse) {
                                       $wh[$warehouse->id] = $warehouse->name;
                                    }
                                }
                                // Default to 'all warehouses' (empty value) unless user preserved a warehouse filter
                                $selected_wh = $preserve_warehouse ? $preserve_warehouse : '';
                                echo form_dropdown('warehouse', $wh, $selected_wh, 'class="form-control" id="filter_warehouse"');
                               ?>
                            </div>
                        </div>
                        <?php if($Settings->product_serial) { ?>
                            <div class="col-sm-4 " style="display:none;">
                                <div class="form-group">
                                    <?= lang('serial_no', 'serial'); ?>
                                    <?= form_input('serial', ($preserve_serial ? $preserve_serial : ''), 'class="form-control tip" id="filter_serial"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                         <div class="col-sm-4">                        
                            <div class="form-group choose-date hidden-xs">
                                <div class="controls">
                                    <?= lang("date_range", "date_range"); ?>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" autocomplete="off" value="<?php echo ($preserve_start ? $preserve_start.' - '.$preserve_end : ''); ?>" id="daterange_new" class="form-control">
                                        <input type="hidden" name="start_date"  id="start_date" value="<?= $preserve_start ? $preserve_start : '' ?>">
                                        <input type="hidden" name="end_date"  id="end_date" value="<?= $preserve_end ? $preserve_end : '' ?>" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4" style="display:none;">
                                <div class="form-group" >
                                    <?= lang('Type_of_sales', 'Type_of_sales'); ?>
                                   <?php $TypeOfModeSale =  $this->config->item('TypeOfModeSale');
                                    $ms[""] = lang('select') . ' ' . lang('type_of_sale');
                                        if (!empty($TypeOfModeSale)) {
                                            foreach ($TypeOfModeSale as $KeyModeOfSale => $ValueModeOfSale) {
                                                   $ms[$KeyModeOfSale] = $ValueModeOfSale;
                                            }
                                        }
                                        echo form_dropdown('TypeOfModeSale', $ms, ($preserve_TypeOfModeSale ? $preserve_TypeOfModeSale : ''), 'class="form-control" id="TypeOfModeSale"');
                                   ?>
                                </div>
                            </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> 
                            <button id="btn-filter" class="btn btn-primary"><?= $this->lang->line("submit") ?></button>
                            <button type="button" id="btn-reset" class="btn btn-warning input-xs"><?= $this->lang->line("reset") ?></button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>

                <?php if ($Owner || $GP['bulk_actions']) {
                    echo form_open('sales/sale_actions', 'id="action-form"');
                } ?>
                <div class="table-responsive">
                    <table id="POSData" class="table table-bordered table-hover table-striped">
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
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("sale_status"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th><?= lang("Delivery"); ?></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="13" class="dataTables_empty"><?= lang("loading_data"); ?></td>
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
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th class="defaul-color"></th>
                            <th class="defaul-color"></th>
                            <th class="defaul-color"></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
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
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
