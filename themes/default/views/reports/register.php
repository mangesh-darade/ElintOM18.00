<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";
/* if($this->input->post('name')){
  $v .= "&product=".$this->input->post('product');
} */

if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}

?>
<style type="text/css">
    .topborder div { border-top: 1px solid #CCC; }
</style>
<style>
<?php if($pos_settings->display_coinage): ?>
/* Set width for Withdrawal column (8th column) */
#registerTable th:nth-child(8),
#registerTable td:nth-child(8) {
    width: 140px !important;   /* Change 140px to whatever you want */
    white-space: nowrap;
    text-align: right;
}
#registerTable th:nth-child(9),
#registerTable td:nth-child(9) {
    width: 140px !important;   /* Change 140px to whatever you want */
    white-space: nowrap;
    text-align: right;
}
<?php endif; ?>
</style>

<script>
    $(document).ready(function () {
        function total_cash(x) {
            if(x !== null) {
                var y = x.split(' (');
                var z = y[1].split(')');
                return currencyFormat(y[0])+'<span class="text-success">'+currencyFormat(z[0])+'</span><span class="text-danger topborder">'+currencyFormat(y[0]-z[0])+'</span>';
            }
            return '';
        }
        function total_sub(x) {
            if(x !== null) {
                var y = x.split(' (');
                var z = y[0].split(')');
                return y[0]+'<br><span class="text-success">'+z[0]+'</span><span class="text-danger topborder"><div>'+(y[0]-z[0])+'</div></span>';
            }
            return '';
        }
        function numericVal(x){
            var n = parseFloat(String(x).replace(/[^0-9.-]/g,''));
            return isNaN(n) ? 0 : n;
        }
        var oTable = $('#registerTable').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getRrgisterlogs/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"mRender": fld},                  // 0 open_time
                {"mRender": fld},                  // 1 close_time
                null,                              // 2 user
                {"mRender": currencyFormat},       // 3 cash_in_hand
                {"mRender": total_sub},            // 4 cc_slips
                {"mRender": total_sub},            // 5 cheques
                {"mRender": total_cash},           // 6 total_cash
                <?php if($pos_settings->display_coinage): ?>
                // ✅ ALWAYS define these two
                {
                    "mRender": currencyFormat,
                    "visible": <?= $pos_settings->display_coinage ? 'true' : 'false' ?>
                }, // 7 withdrawal
                {
                    "mRender": currencyFormat,
                    "visible": <?= $pos_settings->display_coinage ? 'true' : 'false' ?>
                }, // 8 bank_deposit
                            <?php endif; ?>

                {"bSortable": false}               // 9 note
            ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {

                var nCells = nRow.getElementsByTagName('th');

                // ✅ Cash in hand
                var cash = 0;
                for (var i = iStart; i < iEnd; i++) {
                    var a = aaData[aiDisplay[i]];
                    cash += numericVal(a[3]);
                }
                nCells[3].innerHTML = currencyFormat(cash);

                <?php if($pos_settings->display_coinage): ?>
                // ✅ Withdrawal & Bank Deposit
                var wd = 0, bd = 0;
                for (var i = iStart; i < iEnd; i++) {
                    var a = aaData[aiDisplay[i]];
                    wd += numericVal(a[7]);
                    bd += numericVal(a[8]);
                }
                nCells[7].innerHTML = currencyFormat(wd);
                nCells[8].innerHTML = currencyFormat(bd);
                <?php endif; ?>
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[ yyyy-mm-dd HH:mm:ss ]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[ yyyy-mm-dd HH:mm:ss ]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('user');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('cash_in_hand');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('cc_slips');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('Cheques');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('total_cash');?>]", filter_type: "text", data: []},
            <?php if($pos_settings->display_coinage): ?>
            {column_number: 7, filter_default_label: "[<?=lang('Withdrawal');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('Bank_Deposit');?>]", filter_type: "text", data: []},
            <?php endif; ?>
            {column_number: <?php echo ($pos_settings->display_coinage) ? 9 : 7; ?>, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
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

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-large"></i><?= lang('register_report'); ?><?php
            if ($this->input->post('start_date')) {
                echo " From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
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
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
<p class="introtext"><?= lang('customize_report'); ?></p>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                
                <div id="form">

                    <?php echo form_open("reports/register"); ?>
                    <div class="row">

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("user"); ?></label>
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
                         
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> 
                            <!--<input type="button" id="report_reset" data-value="<?=base_url('reports/register');?>" name="submit_report" value="Reset" class="btn btn-warning input-xs">-->
                            <a href="reports/restbutton" class="btn btn-success">Reset</a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="registerTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                        <tr>
                            <th><?= lang('open_time'); ?></th>
                            <th><?= lang('close_time'); ?></th>
                            <th><?= lang('user'); ?></th>
                            <th><?= lang('cash_in_hand'); ?></th>
                            <th><?= lang('cc_slips'); ?></th>
                            <th><?= lang('Cheques'); ?></th>
                            <th><?= lang('total_cash'); ?></th>
                            <?php if($pos_settings->display_coinage): ?>
                            <th><?= lang('Withdrawal'); ?></th>
                            <th><?= lang('Bank_Deposit'); ?></th>
                            <?php endif; ?>
                            <th><?= lang('note'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="<?php echo ($pos_settings->display_coinage) ? 10 : 8; ?>" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                            <?php if($pos_settings->display_coinage): ?>
                            <th></th>
                            <th></th>
                            <?php endif; ?>
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
            window.location.href = "<?=site_url('reports/getRrgisterlogs/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getRrgisterlogs/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
			window.location.href = "<?=site_url('reports/getRrgisterlogs/0/0/xls/?v=1'.$v)?>";
            /*html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });*/
            return false;
        });
    });
</script>
