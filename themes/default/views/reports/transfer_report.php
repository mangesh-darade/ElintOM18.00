<?php defined('BASEPATH') OR exit('No direct script access allowed');

$v = "";
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
if ($this->input->post('refno')) {
    $v .= "&refno=" . $this->input->post('refno');
}
if ($this->input->post('created_by')) {
    $v .= "&created_by=" . $this->input->post('created_by');
}
if ($this->input->post('status')) {
    $v .= "&status=" . $this->input->post('status');
}
if ($this->input->post('brand')) {
    $v .= "&brand=" . $this->input->post('brand');
}
// else{
//     $v .=($user_warehouse=='0' ||$user_warehouse==NULL)?'':"&warehouse=" . str_replace(",", "_",$user_warehouse);
// }
if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
}
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
// if ($this->input->post('TypeOfModeSale')) {
//     $v .= "&TypeOfModeSale=" . $this->input->post('TypeOfModeSale');
// }
?>
<script>
    $(document).ready(function () {
        var toWarehouse;
        $('#toWarehouse').on("select2-focus", function (e) {
            toWarehouse = $(this).val();
        }).on("select2-close", function (e) {
            if ($(this).val() != '' && $(this).val() == $('#fromWarehouse').val()) {
                $(this).select2('val', toWarehouse);
                            

                bootbox.alert('<?= lang('please_select_different_warehouse') ?>');
            }
        });
        var from_warehouse;
        $('#fromWarehouse').on("select2-focus", function (e) {
            from_warehouse = $(this).val();
        }).on("select2-close", function (e) {
            if ($(this).val() != '' && $(this).val() == $('#toWarehouse').val()) {
                $(this).select2('val', fromWarehouse);
                bootbox.alert('<?= lang('please_select_different_warehouse') ?>');
            }
        });

		$('#form').hide();
               
        var oTable = $('#SLData').dataTable({
            "aaSorting": [[0, "asc"], [1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('transfers/load_transfer_report_data/?v=1'. $v)?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                //console.log(aoData);
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
            //     var oSettings = oTable.fnSettings();
            //     //$("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            //     nRow.id = aData[0];
            //     nRow.setAttribute('data-return-id', aData[11]);
            //     nRow.className = "invoice_link re"+aData[11];
            //     //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
            //     return nRow;
            // },
            var oSettings = oTable.fnSettings();
                //$("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                nRow.id = aData[0];
                nRow.setAttribute('data-return-id', aData[11]);
                nRow.className = "report_link re"+aData[11];
        //         if(aData[6]=='order_ready'){
		// 	$("td:eq(6)", nRow).find('.row_status').text('Order Ready');
		// }
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                return nRow;
            },
            "aoColumns": [
            {"bSortable": false,"mRender": checkbox},
            null, 
            null,
             {"bVisible": true}, 
             {"bVisible": true},
             {"bVisible": true},
             {"mRender": currencyFormat},
             {"bVisible": true},
             {"bVisible": true},
             {"bVisible": true},
             {"bVisible": true},
             {"bVisible": true},
            //    {"mRender": row_status},
            //    {"mRender": currencyFormat},
            //     {"mRender": currencyFormat},
            //      {"mRender": currencyFormat},
            //       {"mRender": pay_status}, 
            //       {"bVisible": false,"mRender": attachment},
            //        {"bVisible": false}, 
            //         {"bVisible": false},
            //          {"bVisible": true}
        ],

            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0;

                for (var i = 0; i < aiDisplay.length; i++) {
                    var amt = aaData[aiDisplay[i]][6]; // Amount column
                    amt = amt ? amt.toString().replace(/,/g, '') : 0;
                    total += parseFloat(amt);
                }

                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(total); // Amount footer
            }

        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('Time');?> (hh-mm-ss)]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('From Warehouse');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('To Warehouse');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('Amount');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('Status ');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('Code ');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('Products ');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('Variant');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('Users');?>]", filter_type: "text", data: []},
        ], "footer");

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
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
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
        if (localStorage.getItem('paid_by')) {
            localStorage.removeItem('paid_by');
        }
        if (localStorage.getItem('amount_1')) {
            localStorage.removeItem('amount_1');
        }
        if (localStorage.getItem('paid_by_1')) {
            localStorage.removeItem('paid_by_1');
        }
        if (localStorage.getItem('pcc_holder_1')) {
            localStorage.removeItem('pcc_holder_1');
        }
        if (localStorage.getItem('pcc_type_1')) {
            localStorage.removeItem('pcc_type_1');
        }
        if (localStorage.getItem('pcc_month_1')) {
            localStorage.removeItem('pcc_month_1');
        }
        if (localStorage.getItem('pcc_year_1')) {
            localStorage.removeItem('pcc_year_1');
        }
        if (localStorage.getItem('pcc_no_1')) {
            localStorage.removeItem('pcc_no_1');
        }
        if (localStorage.getItem('cheque_no_1')) {
            localStorage.removeItem('cheque_no_1');
        }
        if (localStorage.getItem('slpayment_term')) {
            localStorage.removeItem('slpayment_term');
        }
        <?php $this->sma->unset_data('remove_slls');}
        ?>

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

    });

function resetSaleList(){
	window.location="<?=base_url('reports/transferReport');?>";
}
</script>
<style>
    .delete_Offline, .add_payment_Sale, .add_payment_Offline, .edit_Offline, .add_delivery_Sale, .add_delivery_Offline, .view_payments_Offline, .download_POS, .email_Eshop, .return_Sale, .return_Offline, .duplicate_Eshop, .SaleDetailModel{
        display:none;
    }
    .SaleDetailModel_POS{
        display:block;
    }

    .fa-file-excel-o:before {
    content: "\f1c3";
    margin: -6px;

    .dropdown-menu>li>a {
    display: block;
    padding: 3px 20px;
    clear: both;
    font-weight: 400;
    line-height: 1.42857143;
    color: #333;
    white-space: nowrap;
    margin: -2px;
}
}
    
    </style>

<?php 
    $warehouse_id = $_POST['warehouse'];
    foreach ($warehouses as $warehouse) {
        if($warehouse->id == $_POST['warehouse']){
          $warehousename = $warehouse->name;
        }
    }
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-heart"></i><?=lang('Transfer_Report')?>
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
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                      
                    </ul>
                </li>
            </ul>
        </div>
    </div>
<p class="introtext"><?=lang('list_results');?></p>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form" >
                    <?php echo form_open("reports/transferReport"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                            </div>
                        </div>
                        <?php
                        $GgroupView=0;
                        if($this->session->userdata('group_id')==1) $GgroupView=1; elseif($this->session->userdata('group_id')==2) $GgroupView=1;
                        ?>
                        <div class="col-sm-4" <?php if($GgroupView==0){ ?>style="display:none;"<?php } ?>>
                            <div class="form-group" >
                                <label class="control-label" for="refno"><?= lang("Reference No"); ?></label>
                                <?php
                                $rf[""] = lang('select').' '.lang('refno');
                                foreach ($refNo as $ref) {
                                    $rf[$ref->id] = $ref->transfer_no;
                                }
                                echo form_dropdown('refno', $rf, (isset($_POST['refno']) ? $_POST['refno'] : ""), 'class="form-control"  id="refno" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("refno") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4" >
                            <div class="form-group" >
                                <label class="control-label" for="created_by"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('created_by');
                                foreach ($Users as $user) {
                                    $us[$user->id] = $user->username;
                                }
                                echo form_dropdown('created_by', $us, (isset($_POST['created_by']) ? $_POST['created_by'] : ''), 'class="form-control"');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("status", "tostatus"); ?>
                                <?php
                               
                                if($Owner || $Admin || $this->session->userdata('view_right')=='1'  ){
                                    $post = array('completed' => lang('completed'),/* 'request' => lang('Request'),*/ 'sent' => lang('sent'));

                                }else{
                                    if($GP['transfer_status_completed']){
                                        $post['completed'] = lang('completed');
                                    }
                                    /*if($GP['transfer_status_request']){
                                        $post['request'] = lang('Request');
                                    }*/
                                    if($GP['transfer_status_sent']){
                                        $post['sent'] = lang('sent');
                                    }
                                }
                                echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="tostatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("status") . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("brand", "brand") ?>
                                <?php
                                $bt[''] = lang('select') . ' ' . lang('brand');
                                foreach($brands as $brand)
                                {
                                    $bt[$brand->id] = $brand->name;
                                }
                                echo form_dropdown('brand', $bt, (isset($_POST['brand']) ? $_POST['brand'] : ''), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = lang('select') . ' ' . lang('category');
                                foreach($categories as $category)
                                {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

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
                               
                               $new[""] = lang('select') . ' ' . lang('To warehouse');
                              
                                foreach ($toWarehouses as $warehouse) {
                                   $new[$warehouse->to_warehouse_id] = $warehouse->to_warehouse_name;
                                }
                                 
                                // echo form_dropdown('toWarehouse', $new, (isset($_POST['toWarehouse']) ? $_POST['toWarehouse'] : ""),'class="form-control"');
                                echo form_dropdown('toWarehouse', $new, (isset($_POST['toWarehouse']) ? $_POST['toWarehouse'] : ""), 'class="form-control" id="toWarehouse" data-placeholder="' . $this->lang->line("toWarehouse") . " " . $this->lang->line("toWarehouse") . '"');
                               ?>
                            </div>
                        </div>


                        <?php if($Settings->product_serial) { ?>
                            <div class="col-sm-4 " style="display:none;">
                                <div class="form-group">
                                    <?= lang('serial_no', 'serial'); ?>
                                    <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                         <div class="col-sm-4">                        
                            <div class="form-group choose-date hidden-xs">
		                <div class="controls">
		                    <?= lang("date_range", "date_range"); ?>
		                    <div class="input-group">
		                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                      
		                        <input type="text"
                                               autocomplete="off"
		                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'].'-'.$_POST['end_date'] : "";?>"
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
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                            
                            <input type="button" id="report_reset" onclick="return resetSaleList();" data-value="<?=base_url('sales/all_sale_lists');?>" name="submit_report" value="Reset" class="btn btn-warning input-xs">        
                        </div>
                    </div>
					
					</form>
                    <?php //echo form_close(); ?>

                </div>
				<?php if ($Owner || $GP['bulk_actions']) {
						echo form_open('transfers/transfer_actions1', 'id="action-form"');
					}
				?>
                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("Time"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("From Warehouse"); ?></th>
                            <th><?= lang("To Warehouse"); ?></th>
                            <th><?= lang("Amount"); ?></th>
                            <th><?= lang("Status"); ?></th>
                            <th><?= lang("Code"); ?></th>
                            <th><?= lang("Products "); ?></th>
                            <th><?= lang("Variant"); ?></th>
                            <th><?= lang("User "); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <!-- <th>Date</th> -->
                            <!-- <th><?= lang("date"); ?></th>
                            <th><?= lang("time"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("From Warehouse"); ?></th>
                            <th><?= lang("To Warehouse"); ?></th>
                            <th><?= lang("Amount"); ?></th>
                            <th><?= lang("Status"); ?></th>
                            <th><?= lang("Products "); ?></th>
                            <th><?= lang("User "); ?></th> -->
                        </tr>
                        </tfoot>
                    </table>
                </div>
				<?php if ($Owner || $GP['bulk_actions']) {?>
					<div style="display: none;">
						<input type="hidden" name="form_action" value="" id="form_action"/>
						<?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
					</div>
					<?=form_close()?>
				<?php }
				?>
            </div>
        </div>
    </div>
</div>
<style>
#SLData td:nth-child(7){
    text-align: right;
}
</style>
