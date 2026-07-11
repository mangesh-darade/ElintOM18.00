<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- for right alighn ofgrand total,paid and balance column -->
<style>
    #POData td:nth-child(7),
    #POData td:nth-child(8),
    #POData td:nth-child(9),
    #POData th:nth-child(7),
    #POData th:nth-child(8),
    #POData th:nth-child(9) {
        text-align: right !important;
    }
    #POData th:last-child,
    #POData td:last-child {
        min-width: 140px;
        white-space: nowrap;
        text-align: center !important;
    }
    #POData .purchase-list-actions {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
        gap: 8px;
        white-space: nowrap;
    }
    #POData .purchase-list-actions a {
        display: inline-block;
        line-height: 1;
        float: none;
    }
</style>
<script>
    $(document).ready(function () {
        // Always show only incomplete purchases on page load
        // $('#all_purchase').prop('checked', false);
        var oTable = $('#POData').dataTable({
            "aaSorting": [[2, "desc"]],
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
                // push supplier filters from toggle form
                var sel = $('#psupplier').val();
                var supplier_all = (sel === 'all') ? 1 : 0;
                var supplier = (sel && sel !== 'all') ? sel : '';

                aoData.push({ name: 'supplier', value: supplier });
                aoData.push({ name: 'supplier_all', value: supplier_all });

                // All purchase toggle (show all vs exclude 'received')
                // All purchase toggle
                aoData.push({ name: 'all_purchase', value: $('#all_purchase').is(':checked') ? 1 : 0 });
                <?php } ?>
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"bSortable": false}, {"mRender": fld}, null, null, {"mRender": row_status}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": pay_status}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {               
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "purchase_link";
                var poStatus = (aData[5] || '').toString().toLowerCase();
                if (poStatus === 'received' || poStatus === 'returned') {
                    $(nRow).find('td:eq(1)').html('<div class="text-center">N/A</div>');
                }
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][6]);
                    paid += parseFloat(aaData[aiDisplay[i]][7]);
                    balance += parseFloat(aaData[aiDisplay[i]][8]);                     
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(total);
                nCells[7].innerHTML = currencyFormat(paid);
                nCells[8].innerHTML = currencyFormat(balance);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 2, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('purchase_status');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");
        // Toggle filter form
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });

        // Supplier select2 suggestions with 'All Suppliers' option inside dropdown,
        // but default shows 'Select Supplier' (no selection)
        <?php if ($Settings->display_job_work) { ?>
        $('#psupplier').select2({
            minimumInputLength: 0,
            allowClear: true,
            placeholder: "<?= lang('select') . ' ' . lang('supplier'); ?>",
            initSelection: function (element, callback) {
                var val = $(element).val();
                if (val) {
                    $.ajax({
                        type: "get", async: false,
                        url: "<?= site_url('suppliers/getSupplier') ?>/" + val,
                        dataType: "json",
                        success: function (data) { callback(data[0]); }
                    });
                }
                // if empty, do nothing (keeps placeholder 'Select Supplier')
            },
            ajax: {
                url: site.base_url + "suppliers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) { return { term: term, limit: 10 }; },
                results: function (data, page) {
                    var results = data.results || [];
                    // Prepend All Suppliers as the first option in the dropdown
                    results.unshift({ id: 'all', text: "<?= lang('All Suppliers'); ?>" });
                    return { results: results };
                }
            }
        });
        <?php } ?>

    

        <?php if ($this->session->userdata('remove_pols')) {?>
        if (localStorage.getItem('poitems')) {
            localStorage.removeItem('poitems');
        }
        if (localStorage.getItem('podiscount')) {
            localStorage.removeItem('podiscount');
        }
        if (localStorage.getItem('potax2')) {
            localStorage.removeItem('potax2');
        }
        if (localStorage.getItem('poshipping')) {
            localStorage.removeItem('poshipping');
        }
        if (localStorage.getItem('poref')) {
            localStorage.removeItem('poref');
        }
        if (localStorage.getItem('powarehouse')) {
            localStorage.removeItem('powarehouse');
        }
        if (localStorage.getItem('ponote')) {
            localStorage.removeItem('ponote');
        }
        if (localStorage.getItem('posupplier')) {
            localStorage.removeItem('posupplier');
        }
        if (localStorage.getItem('pocurrency')) {
            localStorage.removeItem('pocurrency');
        }
        if (localStorage.getItem('poextras')) {
            localStorage.removeItem('poextras');
        }
        if (localStorage.getItem('podate')) {
            localStorage.removeItem('podate');
        }
        if (localStorage.getItem('postatus')) {
            localStorage.removeItem('postatus');
        }
        if (localStorage.getItem('popayment_term')) {
            localStorage.removeItem('popayment_term');
        }
        <?php $this->sma->unset_data('remove_pols');}
        ?>
         
        setTimeout(function(){
            $('.row_status_deleted').hide(); 
            
            $('.link_view_payment_pending').hide();   
            $('.link_return_ordered').hide(); 
            
            $('.link_delete_received').hide();   
            $('.link_edit_received').hide();
            
            $('.link_add_payment_paid').hide();
            
            $('.link_return_returned').hide();   
            $('.link_duplicate_returned').hide();   
            $('.link_edit_returned').hide();   
            $('.link_add_payment_returned').hide();   
            $('.link_add_payment_pending').show(); 
            
            $('.link_delete_partial').hide(); 

            $('.link_view_payment_due').hide();   
            $('.link_add_payment_due').show();   
        
        }, 1000); 
        <?php if ($Settings->display_job_work) { ?>
         // Filter submit button
        $('#filter_submit').on('click', function (e) {
            e.preventDefault();
            oTable.fnDraw();
        });    
        <?php } ?>
        
        $('#POData').on('click', 'td:has(.inward-po-btn)', function (e) {
            e.stopPropagation();
        });

    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
	    echo form_open('purchases/purchase_actions', 'id="action-form"');
	}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-star"></i><?=lang('purchases') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('all_warehouses'))  . ')';?>
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
                            <a href="#" id="export_invoice_to_excel" data-action="export_invoice_to_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('Export Purchase  Items to Excel')?>
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
                            <a href="#" id="combine" data-action="combine">
                                <i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?>
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
                <?php if (!empty($warehouses)) {
                    ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang("warehouses")?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?=site_url('purchases')?>"><i class="fa fa-building-o"></i> <?=lang('all_warehouses')?></a></li>
                            <li class="divider"></li>
                            <?php
                                $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                            	foreach ($warehouses as $warehouse) {
                                    if($Owner || $Admin  ){
                            	        echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('purchases/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            	    }elseif (in_array($warehouse->id,$permisions_werehouse)) {
                                        echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('purchases/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                    }
                                }    
                                ?>
                        </ul>
                    </li>
                <?php }
                ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?=lang('list_results');?></p>
                <?php if ($Settings->display_job_work) { ?>
                <div id="form" class="well well-sm">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label><?= lang('suppliers'); ?></label>
                                <input type="hidden" id="psupplier" name="psupplier" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group" style="margin-top: 25px;">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="all_purchase" value="1"> <?= lang('All purchases'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <button id="filter_submit" class="btn btn-primary"><?= $this->lang->line("submit") ?></button>
                            <a href="reports/restbutton" class="btn btn-success">Reset</a>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("Inward_PO"); ?></th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("ref_no"); ?></th>
                            <th><?= lang("supplier"); ?></th>
                            <th><?= lang("purchase_status"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="min-width:140px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?=lang('loading_data_from_server');?></td>
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
                            <th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="min-width:140px; text-align: center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php }
?>

<script>

    $(document).on("click", "a.send_quote_sms", function (e) {

        e.preventDefault();
        var click_ele = this;
        $.ajax({
            url: "purchases/get_purchaseDetails/"+$(click_ele).attr('purchase_id'),

        }).done(function (supplier) {
//        console.log(customer);
        var phone = prompt("Phone", supplier.phone);


      if (phone != null) {
            //alert($( this ).attr('quote_id') );
            $.ajax({
                url: $(click_ele).attr('href')+'/'+phone,

                beforeSend: function (xhr) {
                    //xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
                }
            }).done(function (data) {
                var data = JSON.parse(data);
                bootbox.alert(data.msg);
            });
        }else{
            return false;
        }
        });





    });
</script>