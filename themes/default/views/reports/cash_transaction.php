<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
thead th:nth-child(5) .yadcf-filter-wrapper input{
    text-align: right;
}
tbody td:nth-child(5) {
    text-align: right;
}
thead th:nth-child(6) .yadcf-filter-wrapper input{
    text-align: right;
}
#CTData td:nth-child(6) {
    text-align: right;
}
</style>

<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$user_warehouse = $this->session->userdata('warehouse_id');
$v = "";
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}else{
    $v .=($user_warehouse=='0' ||$user_warehouse==NULL)?'':"&warehouse=" . str_replace(",", "_",$user_warehouse);
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
// if ($this->input->post('transaction_type')) {
//     $v .= "&transaction_type=" . $this->input->post('transaction_type');
// }
if ($this->input->post('transaction_type')) {
    $types = $this->input->post('transaction_type'); // array
    if (is_array($types)) {
        $v .= "&transaction_type=" . implode(",", $types);
    }
}

if ($this->input->post('amount_filter')) {
    $v .= "&amount_filter=" . $this->input->post('amount_filter');
}
if ($this->input->post('amount_value')) {
    $v .= "&amount_value=" . $this->input->post('amount_value');
}
if ($this->input->post('amount_value_to')) {
    $v .= "&amount_value_to=" . $this->input->post('amount_value_to');
}
if ($this->input->post('currency')) {
    $types = $this->input->post('currency'); // array
    if (is_array($types)) {
        $v .= "&currency=" . implode(",", $types);
    }
}

?>
<script>
$(document).ready(function () {
    $('#transaction_type').select2({
        placeholder: "<?= lang('select').' '.lang('transaction_type'); ?>",
        width: '100%',
        allowClear: true
    });
});
</script>

<script type="text/javascript">
$(document).ready(function () {
    var columns = [
    {"mRender": fld},            // Date
    null,                        // User
    null,                        // Location
    null,                        // Transaction Type
    {"mRender": currencyFormat}, // Amount
    { "bSearchable": false },                        // Denominations (already formatted)
    null                         // Note
];

    var oTable = $('#CTData').dataTable({
        "aaSorting": [[0, "desc"]],
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "<?= site_url('reports/getCashTransactionReport/?v=1' . $v) ?>",
        "fnServerData": function (sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?= $this->security->get_csrf_token_name() ?>",
                "value": "<?= $this->security->get_csrf_hash() ?>"
            });
            $.ajax({
                dataType: "json",
                type: "POST",
                url: sSource,
                data: aoData,
                success: fnCallback
            });
        },
        "fnRowCallback": function (nRow, aData) {
            nRow.id = aData[aData.length - 1];
            return nRow;
        },
        "aoColumns": columns,
        "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
            var total = 0;
            for (var i = 0; i < aaData.length; i++) {
                total += parseFloat(aaData[aiDisplay[i]][4]);
            }

            var nCells = nRow.getElementsByTagName("th");
            nCells[4].innerHTML = currencyFormat(total);
        }
    }).fnSetFilteringDelay().dtFilter([
        {column_number: 0, filter_default_label: "[<?=lang('date_time');?> (yyyy-mm-dd HH:MM:SS)]", filter_type: "text"},
        {column_number: 1, filter_default_label: "[<?=lang('user');?>]", filter_type: "text"},
        {column_number: 2, filter_default_label: "[<?=lang('location');?>]", filter_type: "text"},
        {column_number: 3, filter_default_label: "[<?=lang('transaction_type');?>]", filter_type: "text"},
        {column_number: 5, filter_default_label: "[<?=lang('Denominations');?>]", filter_type: "text"},
        {column_number: 6, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []}
    ], "footer");
});
</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        
        // Amount filter handling
        $('#amount_filter').change(function() {
            var filterType = $(this).val();
            if (filterType == 'between') {
                $('#amount_value_to_group').show();
            } else {
                $('#amount_value_to_group').hide();
            }
        });
        
        // Trigger change on load
        $('#amount_filter').trigger('change');
    });
</script>
<style>
   #CTData td:nth-child(5) {
    text-align: right;
   }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('cash_transaction_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i
                            class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i
                            class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="<?=site_url('reports/getCashTransactionReport/0/0/img/?v=1'.$v)?>" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
<p class="introtext"><?= lang('customize_report'); ?></p>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                
                <div id="form">

                    <?php echo form_open("reports/cash_transaction"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $permisions_werehouse = explode(",", $user_warehouse);
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    if($Owner || $Admin  ){
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }else if(in_array($warehouse->id,$permisions_werehouse)){
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }    
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="transaction_type"><?= lang("transaction_type"); ?></label>
                                <?php
                                $tt = [];
                                if ($transaction_types) {
                                    foreach ($transaction_types as $type) {
                                        $tt[$type->type] = ucfirst($type->type);
                                    }
                                } else {
                                    // Fallback to hardcoded types if no data found
                                    $tt["cash_in"] = 'Cash In';
                                    $tt["cash_out"] = 'Cash Out';
                                    $tt["transfer"] = 'Transfer';
                                    $tt["expense"] = 'Expense';
                                    $tt["other"] = 'Other';
                                }
                                echo form_dropdown('transaction_type[]', $tt, (isset($_POST['transaction_type']) ? $_POST['transaction_type'] : []),'class="form-control" id="transaction_type" multiple data-placeholder="' .$this->lang->line("select") . " " . $this->lang->line("transaction_type") . '"');
                                ?>
                            </div>
                        </div>
                         <div class="col-sm-4">                        
                            <div class="form-group choose-date hidden-xs">
		                <div class="controls">
		                    <?= lang("date_range", "date_range"); ?>
		                    <div class="input-group">
		                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
		                        <input type="text"
		                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'].'-'.$_POST['end_date'] : "";?>"
		                               id="daterange_new" class="form-control">
		                        <span class="input-group-addon" style="display:none;"><i class="fa fa-chevron-down"></i></span>
		                         <input type="hidden" name="start_date"  id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : "";?>">
		                         <input type="hidden" name="end_date"  id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : "";?>" >
                                    </div>
		                </div>
		            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="currency"><?= lang("currency"); ?></label>
                                <?php
                                $tt = [];
                                if ($currency) {
                                    foreach ($currency as $cur) {
                                        $key = $cur->currency_value . '_' . $cur->type; // optional, avoids duplicate keys
                                        $tt[$key] = $cur->currency_value . ' (' . $cur->type . ')';
                                    }
                                }
                                echo form_dropdown('currency[]', $tt, (isset($_POST['currency']) ? $_POST['currency'] : []),'class="form-control" id="currency" multiple data-placeholder="' .$this->lang->line("select") . " " . $this->lang->line("currency") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="amount_filter"><?= lang("amount_filter"); ?></label>
                                <?php
                                $af[""] = lang('select').' '.lang('filter');
                                $af["greater"] = lang('greater_than');
                                $af["less"] = lang('less_than');
                                $af["between"] = lang('between');
                                echo form_dropdown('amount_filter', $af, (isset($_POST['amount_filter']) ? $_POST['amount_filter'] : ""), 'class="form-control" id="amount_filter" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("filter") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="amount_value"><?= lang("amount_value"); ?></label>
                                <?php echo form_input('amount_value', (isset($_POST['amount_value']) ? $_POST['amount_value'] : ""), 'class="form-control" id="amount_value" placeholder="' . lang('amount_value') . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4" id="amount_value_to_group" style="display:none;">
                            <div class="form-group">
                                <label class="control-label" for="amount_value_to"><?= lang("amount_value_to"); ?></label>
                                <?php echo form_input('amount_value_to', (isset($_POST['amount_value_to']) ? $_POST['amount_value_to'] : ""), 'class="form-control" id="amount_value_to" placeholder="' . lang('amount_value_to') . '"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                            <a href="reports/cash_transaction" class="btn btn-success">Reset</a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="CTData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("user"); ?></th>
                            <th><?= lang("location"); ?></th>
                            <th><?= lang("transaction_type"); ?></th>
                            <th><?= lang("amount"); ?></th>
                            <th><?= lang("Denominations"); ?></th>
                            <th><?= lang("note"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th><?= lang("total_amount"); ?></th>
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

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCashTransactionReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCashTransactionReport/0/xls/?v=1'.$v)?>";
            return false;
        });
    });
</script>
