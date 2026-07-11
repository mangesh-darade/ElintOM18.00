
<?php defined('BASEPATH') OR exit('No direct script access allowed');
 $user_warehouse = $this->session->userdata('warehouse_id');
$v= ($user_warehouse=='0' ||$user_warehouse==NULL)?'':"?warehouse=" . str_replace(",", "_",$user_warehouse);

$v = "";

if ($this->input->post('fromWarehouse')) {
    $v .= "&fromWarehouse=" . $this->input->post('fromWarehouse');
}
if ($this->input->post('toWarehouse')) {
    $v .= "&toWarehouse=" . $this->input->post('toWarehouse');
}
if (!$this->input->post('toWarehouse')) {
//    unset($_POST['toWarehouse']);
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>
    $(document).ready(function () {
		$('#form').hide();   
        var oTable = $('#TOData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('transfers/getTransfers/?v=1'. $v)?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"mRender": fld}, null, null, null, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var ntdCells = nRow.getElementsByTagName('td');
				//console.log(aData[8]);
				if(aData[8]=='partial_completed')
						ntdCells[8].innerHTML = '<span style="background-color:#5cb85c; color:#fff; font-size:11px; padding:2px; font-weight:bold;" >Partial</span>';
					if(aData[8]=='sent_balance')
						ntdCells[8].innerHTML = '<span style="background-color:#5cb85c; color:#fff; font-size:11px; padding:2px; font-weight:bold;" >Sent</span>';
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "transfer_link";
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var row_total = 0, tax = 0, gtotal = 0;
                for (var i = 0; i < aaData.length; i++) {
                    row_total += parseFloat(aaData[aiDisplay[i]][5]);
                    tax += parseFloat(aaData[aiDisplay[i]][6]);
                    gtotal += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(row_total);
                nCells[6].innerHTML = currencyFormat(tax);
                nCells[7].innerHTML = currencyFormat(gtotal);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {
                column_number: 3,
                filter_default_label: "[<?=lang("warehouse").' ('.lang('from').')';?>]",
                filter_type: "text", data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?=lang("warehouse").' ('.lang('to').')';?>]",
                filter_type: "text", data: []
            },
            {column_number: 8, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
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


function resetSaleList(){
	window.location="<?=base_url('transfers/index');?>";
}
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('transfers'); ?></h2>
       
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('transfers/add') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_transfer') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="combine" data-action="combine">
                                <i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?>
                            </a>
                        </li>
<!--                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_transfers") ?></b>"
                             data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                             data-html="true" data-placement="left">
                             <i class="fa fa-trash-o"></i> <?= lang('delete_transfers') ?>
                         </a>
                     </li>-->
                 </ul>
             </li>
            </ul>
        </div>
    </div>
     <p class="introtext"><?= lang('list_results'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
            <div id="form" >
                    <?php echo form_open("transfers/index"); ?>
                    <div class="row">                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="fromWarehouse"><?= lang("From warehouse"); ?></label>
                                <?php
                               
                                $wh[""] = lang('select') . ' ' . lang('From warehouse');
                              
                                foreach ($fromWarehouses as $warehouse) {
                                   $wh[$warehouse->from_warehouse_id] = $warehouse->from_warehouse_name;
                                }
                                 
                                echo form_dropdown('fromWarehouse', $wh, (isset($_POST['fromWarehouse']) ? $_POST['fromWarehouse'] : ""), 'class="form-control" id="fromWarehouse" data-placeholder="' . $this->lang->line("fromWarehouse") . " " . $this->lang->line("fromWarehouse") . '"');
                               ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="toWarehouse"><?= lang("To warehouse"); ?></label>
                                <?php
                               
                               $new["0"] = lang('select') . ' ' . lang('To warehouse');
                              
                                foreach ($toWarehouses as $warehouse) {
                                   $new[$warehouse->to_warehouse_id] = $warehouse->to_warehouse_name;
                                }
                                 
                                echo form_dropdown('toWarehouse', $new, (isset($_POST['toWarehouse']) ? $_POST['toWarehouse'] : ""),'class="form-control"');
                               ?>
                            </div>
                        </div>

                        <div class="col-sm-4">                        
                            <div class="form-group choose-date hidden-xs">
		                        <div class="controls">
		                           <?= lang("Start - End Date", "Start - End Date"); ?>
		                           <div class="input-group">
		                               <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
		                               <input type="text" autocomplete="off" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'].'-'.$_POST['end_date'] : "";?>"
		                               id="daterange_new" class="form-control">
		                                <!--<span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>-->
		                              <input type="hidden" name="start_date"  id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : "";?>">
		                              <input type="hidden" name="end_date"  id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : "";?>" >
                                    </div>
		                       </div>
		                    </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> 
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary" '); ?>
                            
                            <input type="button" id="report_reset" onclick="return resetSaleList();" data-value="<?=base_url('sales/all_sale_lists');?>" name="submit_report" value="Reset" class="btn btn-warning input-xs">        
                        </div>
                    </div>
				</form>

             </div>
                <?php if ($Owner || $GP['bulk_actions']) {
                        echo form_open('transfers/transfer_actions', 'id="action-form"');
                  } ?>
                <div class="table-responsive">
                    <table id="TOData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("ref_no"); ?></th>
                            <th><?= lang("warehouse") . ' (' . lang('from') . ')'; ?></th>
                            <th><?= lang("warehouse") . ' (' . lang('to') . ')'; ?></th>
                            <th><?= lang("Taxable Amount"); ?></th>
                            <th><?= lang("Total GST Tax"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:100px; text-align: center;"><?= lang("actions"); ?></th>
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