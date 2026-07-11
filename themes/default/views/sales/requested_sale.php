<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$warehouseIds = is_numeric($warehouse_id) ? '/'.$warehouse_id : '';
?>
<script>
<?php if($_SESSION['Send_Excel']==1){ ?>
$.ajax({
    type: 'ajax',
    method: 'get',
    url: '<?= base_url()."sales/export_excel"."/".$_SESSION['sale_id']; ?>',
    //async:false,
    success: function(res) {
        //console.log(res);
        //console.log('success');
    },
    error: function() {
        //console.log('errror');
    }
});
<?php } ?>

function export_excel(SaleId) {
    $.ajax({
        type: 'ajax',
        method: 'get',
        url: '<?= base_url()."sales/export_excel"."/"; ?>' + SaleId,
        //async:false,
        success: function(res) {
            //console.log(res);
            //console.log('success');
        },
        error: function() {
            //console.log('errror');
        }
    });
}
$(document).on('click', '#save_discount', function(e) {
    e.preventDefault(); // Prevent default button action
    
    var discount = $('#discount').val();
    var selectedSales = [];

    $('.row-checkbox:checked').each(function() {
        selectedSales.push($(this).data('sale-id'));
    });

    if (selectedSales.length === 0) {
        alert('Please select at least one sale to update.');
        return;
    }

    $.ajax({
        url: '<?= site_url('Requested_sale/updateGrandTotal') ?>', // Update with your URL
        type: 'POST',
        data: {
            discount: discount,
            selected_sales: selectedSales.join(',')
        },
        success: function(response) {
            if (response.success) {
                alert('Grand total updated successfully!');
                // Refresh the page to show updated data
                location.reload();
            } else {
                alert('Failed to update grand total: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while updating the grand total.');
        }
    });
});

$(document).ready(function() {
    var save = 0;
    $(document).on('click', '#Reset', function(e) {
    e.preventDefault(); // Prevent default button action
    $('#start_date').val(''); // Clear start date
    $('#end_date').val(''); // Clear end date
    $('.dis').hide(); // Optionally hide the discount section
    location.reload();
});

    var oTable;
    $(document).on('click', '.dtable', function(e) {

        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var discount = $('#discount').val();
        
        $(document).on('click', '#Submit', function(e) {
                save = 0;
    });
    //     $(document).on('click', '#save_discount', function(e) {
    //             save = 1;
    // });
        if (!startDate || !endDate) {
            e.preventDefault(); // Prevent form submission
            alert('Please select both start and end dates.'); // Alert the user
            $('dis').hide();
            return; // Exit the function
        }
        $('.dis').show();
        var selectedSales = [];
        $('.row-checkbox:checked').each(function() {
            selectedSales.push($(this).data('id'));
            
        });

        oTable = $('#SLData').dataTable({
            "bDestroy": true,
            "aaSorting": [
                [0, "asc"],
                [1, "desc"]
            ],
            "aLengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?=lang('all')?>"]
            ],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?=site_url('Requested_sale/getSales' . $warehouseIds )?>',
            'fnServerData': function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                aoData.push({
                    "name": "start_date",
                    "value": startDate
                });
                aoData.push({
                    "name": "end_date",
                    "value": endDate
                });
                aoData.push({
                    "name" : "discount",
                    "value" : discount
                });
                aoData.push({
                    "name" : "save",
                    "value" : save
                });
                aoData.push({
                    "name": "selected_sales",
                    "value": selectedSales.join(',')
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                console.log(aData[12]);
                var oSettings = oTable.fnSettings();
                //$("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                nRow.id = aData[0];
                nRow.setAttribute('data-return-id', aData[11]);
                nRow.className = "invoice_link re" + aData[11];
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                // alert(nRow);
                return nRow;
            },
            "aoColumns": [{
                "bSortable": false,"mRender": checkbox
                // "mRender": function (data, type, row) {

        //         return '<input type="checkbox" class="row-checkbox" data-id="' + row[0] + '">'; // Assuming row[0] holds the ID
        // $('#amount').val(row[0]);
           
            // }
            }, {
                "mRender": fld
            }, null, null, null,
             {
                "mRender": row_status
            },

            {
                "mRender": currencyFormat
            }, 
            {
                "mRender": currencyFormat
            }, 
                            
        ],
            "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0,
                    ntotal = 0,
                    paid = 0,
                    balance = 0;
                    for (var i = 0; i < aaData.length; i++) {
                        var index = aiDisplay[i];
                        if (index < aaData.length) {
                            var gvalue = parseFloat(aaData[index][6]);
                            var nvalue = parseFloat(aaData[index][7]);
                            if (!isNaN(gvalue)) gtotal += gvalue;
                            if (!isNaN(nvalue)) ntotal += nvalue;
                        } else {
                            console.warn('Invalid aiDisplay index:', index);
                        }
                    }
                // var nCells = nRow.getElementsByTagName('th');
                // nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
                // nCells[8].innerHTML = currencyFormat(parseFloat(ntotal));
                var nCells = nRow.getElementsByTagName('th');
                if (nCells.length >= 6) {
                   nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
                }
                if (nCells.length >= 7) {
                    nCells[7].innerHTML = currencyFormat(parseFloat(ntotal)); // Show total in 7th column
                }
               
            }
        }).fnSetFilteringDelay().dtFilter([{
                column_number: 1,
                filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 2,
                filter_default_label: "[<?=lang('reference_no');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 3,
                filter_default_label: "[<?=lang('biller');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 4,
                filter_default_label: "[<?=lang('customer');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 6,
                filter_default_label: "[<?=lang('current_grand_total');?>]",
                filter_type: "text",
                data: []
            },
            {
                column_number: 7,
                filter_default_label: "[<?=lang('New_Grand_Total');?>]",
                filter_type: "text",
                data: []
            },
            
        ], "footer");
    });
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

    $(document).on('click', '.sledit', function(e) {
        if (localStorage.getItem('slitems')) {
            e.preventDefault();
            var href = $(this).attr('href');
            bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function(result) {
                if (result) {
                    window.location.href = href;
                }
            });
        }
    });
    $(document).on('click', '.email_receipt_purchase_excel', function(e) {
        e.preventDefault();
        var SaleId = $(this).attr('data-id');
        var ea = $(this).attr('data-email-address');
        var email = prompt("<?= lang("email_address"); ?>", ea);
        if (email != null) {
            $.ajax({
                type: 'ajax',
                method: 'get',
                url: '<?= base_url()."sales/export_excel"."/"; ?>' + SaleId,
                data: {
                    email: email
                },
                //async:false,
                success: function(res) {
                    //alert(res);
                    console.log(res);
                    console.log('success');
                    bootbox.alert(res.msg);
                    return true;
                },
                error: function(res) {
                    alert(res);
                    console.log('errror');
                    bootbox.alert('<?= lang('ajax_request_failed'); ?>');
                    return false;
                }
            });
        }
    });
});
</script>
<script type="text/javascript">
$(document).ready(function() {
    $('.dis').hide();
    $('#form').hide();
    $('.toggle_down').click(function() {
        $("#form").slideDown();
        return false;
    });
    $('.toggle_up').click(function() {
        $("#form").slideUp();
        return false;
    });
});
</script>
<?php

?>
<?php echo form_open('Requested_sale/save_data', 'id="action-form"');
?>
<style>
    .d-flx{
        display:flex;
        align-items:end;
    }

    table#SLData{
        width:100%!important;
    }
    .custom-margin{
        margin:0.3rem 1rem;
    }
    #SLData th:nth-child(1) {
    width: 5% !important;
}
.fa-calendar{
    top: 4rem;
    cursor: pointer;
    position: absolute;
    right: 3rem;
}
.custom-search{
    padding-left: 1rem;
    line-height: 26px;
    width: 40px;
    height: 38px;
    border-radius: 50% !important;
}
.custom-srch{
    font-size:21px;
}
#Apply_discount{
    background: transparent;
    color: #333;
    box-shadow: 1px 4px 6px 1px #bababa;
    border-radius: 0.4rem !important;
    margin: 0rem 1.5rem 0rem 0rem;
}
#myButton{
    box-shadow: 1px 4px 6px 1px #bababa;
    border-radius: 0.4rem !important;
    margin: 0rem 1.5rem 0rem 0rem;
}

#SLData_wrapper .row{
    display: flex;
    align-items: end;
    margin: 1rem;
}

#SLData td:nth-child(8) {
    color:#428bca;
}
.table tfoot th .text-center, .table tfoot th:last-child .text-right{
    color:#428bca!important;
}
    </style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-heart"></i><?=lang('sales') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('Bulk Discount')) . ')';?>
        </h2>
<!-- 
         <div class="">
                            <span class="btn btn-warning input-xs reset" id="Reset"><?php echo $this->lang->line("Reset") ?> </span>
                            </div> -->
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
            <li class="dropdown" style="display: none;">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <!-- <li>
                            <a href="<?=site_url('sales/add')?>">
                                <i class="fa fa-plus-circle"></i> <?=lang('add_sale')?>
                            </a>
                        </li> -->
                        <li>
                            <a href="#" id="export_invoice_to_excel" data-action="export_invoice_to_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                        <!-- <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>--->

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
                        <!-- <li>
                            <a href="#" class="bpo" title="<b><?=lang("delete_sales")?></b>"
                                data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?=lang('delete_sales')?>
                            </a>
                        </li> -->
                    </ul>
                </li>
                <?php if (!empty($warehouses)) {
                    ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip"
                            data-placement="left" title="<?=lang("Warehouse")?>"></i></a>

                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?=site_url('Requested_sale')?>"><i class="fa fa-building-o"></i>
                                <?=lang('Bulk Discount')?></a></li>
                        <li class="divider"></li>
                        <?php
                                $permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));
                            	foreach ($warehouses as $warehouse) {
                            	     if($Owner || $Admin  ){
                            	        	echo '<li><a href="' . site_url('Requested_sale/index/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';		
                            	       }elseif (in_array($warehouse->id,$permisions_werehouse)) {
                            	         echo '<li><a href="' . site_url('Requested_sale/index/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';		
                            	      }  
                            	 }
                                ?>
                    </ul>
                </li>
                <?php }
                ?>

             <li class="dropdown">
                <div class="box-icon custom-margin">
                     <div class="">
                            <span class="btn btn-warning input-xs reset" id="Reset"><?php echo $this->lang->line("Reset") ?> </span>
                     </div>
                </div>
                </li>

                
            </ul>
        </div>
    </div>
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?=lang('list_results');?></p>
                <div id="form">
                    <div class="d-flx">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang("Start_date", "start_date"); ?>
                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            <i class="fa fa-calendar datetime"></i>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang("End_date", "end_date"); ?>
                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                            <i class="fa fa-calendar datetime"></i>
                        </div>
                    </div>
                    <div class="col-sm-4">
                    <div class="form-group">
                        <div class="controls">
                            <span class="btn btn-primary dtable custom-search" id="Submit">
                                <!-- <?php echo $this->lang->line("Search") ?>  -->
                                <i class="fa fa-search custom-srch"></i>
                            </span>
                            <!-- <span class="btn btn-warning input-xs reset" id="Reset"><?php echo $this->lang->line("Reset") ?> </span> -->
                            <!-- btn btn-warning input-xs -->
                        </div>
                    </div>
                                </div>
                                </div>
                    <div class="dis col-sm-4">   
                    <div class="form-group dis" style="display: none;"> 
                        <?= lang("Discount(%)", "discount(%)"); ?>
                        <input type="number" id="discount" class="form-control" name="discount" value="<?= isset($_POST['discount']) ? $_POST['discount'] : ''; ?>" min="0" step="any">
                    </div>

                    
                    <!-- <input type="text" id = "amount" class="form-control" name="values" value="<?= isset($_POST['discount']) ? $_POST['discount'] : ''; ?>"> -->

                    <div class="controls dis">
                        <span class="btn btn-primary dtable" id="Apply_discount"><?php echo $this->lang->line("Apply") ?> </span>
                        <!-- <span class="btn btn-primary savediscount" id="savediscounts"><?php echo $this->lang->line("Save") ?> </span> -->
                        <button class="btn btn-primary" id="myButton"><?php echo $this->lang->line("Save") ?></button>

                    </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <div class="table-responsive">
        <table id="SLData" class="table table-bordered table-hover table-striped">
            <thead>
                <tr>
                <th style="min-width:30px; width: 30px; text-align: center;">
                    <input class="checkbox checkft" type="checkbox" name="check"/>
                </th>
                    <th><?= lang("date"); ?></th>
                    <th><?= lang("reference_no"); ?></th>
                    <th><?= lang("biller"); ?></th>
                    <th><?= lang("customer"); ?></th>
                    <th><?= lang("sale_status"); ?></th>
                    <th><?= lang("Current_Grand_Total"); ?></th>
                    <th><?= lang("New_Grand_Total"); ?></th>
                    <!-- <th style="min-width:30px; width: 30px; text-align: center;"> -->
                    </th>
                   

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
                        <input class="checkbox checkft" type="checkbox" name="check" />
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <!-- <th></th> -->

                   
                    <!-- <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                    </th> -->
                    <!-- <th></th>

                    <th style="width:80px; text-align:center;"></th>
                    <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th> -->
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
    <input type="hidden" name="form_action" value="" id="form_action" />
    <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
</div>
<?=form_close()?>
<?php }
?>

<?php 
    /**
    * Return sales after show Gift Card
    */
    
        if($this->session->flashdata('giftcard_id')){ ?>

<a href="<?= site_url('sales/view_creadit_note_card/'.$this->session->flashdata('giftcard_id')) ?>" class="tip"
    style="display:none" title="" id="link" data-toggle="modal" data-target="#myModal"
    data-original-title="View Gift Card"><i class="fa fa-gift" aria-hidden="true"></i></a>
<script>
$(document).ready(function() {
    $('#link').trigger('click');
});
$(document).ready(function() {
     

});
</script>
<?php  }
  
    /**
     * End return sales after show Gift Card
     */
?>
<script>
    // Get the input element
    const discountInput = document.getElementById('discount');
    discountInput.addEventListener('input', function() {
        // If the input value is greater than 100, apply the limit and alert
        if (parseFloat(discountInput.value) > 100) {
            alert("Applied value up to 100");
            discountInput.value = 100;
        } else if (discountInput.value === "") {
            // Optionally handle case where the field is cleared
            discountInput.value = ""; 
        }
    });


    
</script>