<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$user_warehouse = $this->session->userdata('warehouse_id');

$v = "";

if($this->input->post('customer'))
{
    $v .= "&customer=" . $this->input->post('customer');
}

if($this->input->post('start_date'))
{
    $v .= "&start_date=" . $this->input->post('start_date');
}
if($this->input->post('end_date'))
{
    $v .= "&end_date=" . $this->input->post('end_date');
}

 
?>
<style>
    #CusData td:nth-child(1) {
    white-space: nowrap;
}
</style>
<script>
    $(document).ready(function () {
        oTable = $('#CusData').dataTable({
            // "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,

            "bProcessing": true,
            "bServerSide": true,

            "sAjaxSource": "<?= site_url('reports/getCustomerLedgerV1/?v=1'.$v) ?>",

            "fnServerData": function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    dataType: 'json',
                    type: 'POST',
                    url: sSource,
                    data: aoData,
                    success: fnCallback
                });
            },

            "aoColumns": [
                { 
                    "mRender": fld, 
                    "bSearchable": true, 
                    "sType": "date", 
                    "bSortable": true,
                    "sSortDataType": "dom-text"
                },  // Date - sortable with datetime
                { 
                    "bSortable": true,
                    "bSearchable": true
                },  // Voucher No
                { 
                    "bSortable": true,
                    "bSearchable": true
                },  // Particulars
                { 
                    // "mRender": currencyFormat, 
                    "bSortable": true,
                    "sType": "numeric"
                },  // Opening Balance
                { 
                    // "mRender": currencyFormat, 
                    "bSortable": true,
                    "sType": "numeric"
                },  // Debit
                { 
                    // "mRender": currencyFormat, 
                    "bSortable": true,
                    "sType": "numeric"
                },  // Credit
                { 
                    // "mRender": currencyFormat, 
                    "bSortable": true,
                    "sType": "numeric"
                }   // Balance
            ],

            "aoColumnDefs": [

                /* ALIGNMENT */
                { "sClass": "text-left", "aTargets": [0] },           // Date
                { "sClass": "text-left",   "aTargets": [1, 2] },        // Voucher, Particulars
                { "sClass": "text-right",  "aTargets": [3, 4, 5, 6] },  // Amount columns

                /* WIDTH */
                { "sWidth": "120px", "aTargets": [0] },   // Date
                { "sWidth": "200px", "aTargets": [1] },   // Voucher No
                { "sWidth": "280px", "aTargets": [2] },   // Particulars
                { "sWidth": "130px", "aTargets": [3] },   // Opening Balance
                { "sWidth": "120px", "aTargets": [4] },   // Debit
                { "sWidth": "120px", "aTargets": [5] },   // Credit
                { "sWidth": "130px", "aTargets": [6] }    // Balance
            ],


            "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                nRow.className = " ";
                return nRow;
            },

            /* FOOTER TOTAL ALIGNMENT INCLUDED */
            // "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {

            //     var debitTotal  = 0;
            //     var creditTotal = 0;

            //     for (var i = 0; i < aiDisplay.length; i++) {

            //         var debitStr  = aaData[aiDisplay[i]][4] || '0';
            //         var creditStr = aaData[aiDisplay[i]][5] || '0';

            //         // ✅ Remove currency text ONLY, keep decimals
            //         var debit  = parseFloat(
            //             debitStr.replace(/Rs\.?/gi, '').replace(/,/g, '').trim()
            //         ) || 0;

            //         var credit = parseFloat(
            //             creditStr.replace(/Rs\.?/gi, '').replace(/,/g, '').trim()
            //         ) || 0;

            //         debitTotal  += debit;
            //         creditTotal += credit;
            //     }

            //     var nCells = nRow.getElementsByTagName('th');

            //     nCells[4].innerHTML =
            //         '<div class="text-right"><strong>Rs. ' + debitTotal.toFixed(2) + '</strong></div>';

            //     nCells[5].innerHTML =
            //         '<div class="text-right"><strong>Rs. ' + creditTotal.toFixed(2) + '</strong></div>';
            // }

            // "fnfooterCallback": function (nRow, aaData) {
            //     var nCells = nRow.getElementsByTagName('th');
            //     nCells[3].innerHTML = '<strong class="text-right">' + currencyFormat(parseFloat($(nCells[3]).text()).toFixed(2)) + '</strong>';
            //     nCells[4].innerHTML = '<strong class="text-right">' + currencyFormat(parseFloat($(nCells[4]).text()).toFixed(2)) + '</strong>';
            //     nCells[5].innerHTML = '<strong class="text-right">' + currencyFormat(parseFloat($(nCells[5]).text()).toFixed(2)) + '</strong>';
            //     nCells[6].innerHTML = '<strong class="text-right">' + currencyFormat(parseFloat($(nCells[6]).text()).toFixed(2)) + '</strong>';
            // }

        }).fnSetFilteringDelay().dtFilter([
            { column_number: 0, filter_default_label: "[<?= lang('date'); ?>]", filter_type: "text", data: []},
            { column_number: 1, filter_default_label: "[<?= lang('Voucher No.'); ?>]", filter_type: "text", data: [] },
            { column_number: 2, filter_default_label: "[<?= lang('Particulars'); ?>]", filter_type: "text" , data: []},
            { column_number: 3, filter_default_label: "[<?= lang('Opening Balance'); ?>]", filter_type: "text", data: [] },
            { column_number: 4, filter_default_label: "[<?= lang('Debit'); ?>]", filter_type: "text" , data: []},
            { column_number: 5, filter_default_label: "[<?= lang('Credit'); ?>]", filter_type: "text", data: [] },
            { column_number: 6, filter_default_label: "[<?= lang('Balance'); ?>]", filter_type: "text" , data: []}
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
    });
 
   
</script>
<style>
    .text-bold {
        font-weight: bold !important;
    }
    #CusData td:nth-child(2),
    #CusData td:nth-child(3) {
        white-space: normal;
    }

</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-folder-open"></i><?= lang('Customer_Ledgers'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
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
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
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
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('customize_report'); ?></p>
                <div id="form">
                    <?php echo form_open("reports/customer_ledger_v1"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("Customer", "Customer"); ?>
                                <select class="form-control" name="customer">
                                   <option value="0"> Select Customer </option>
                                <?php
                                
                                $customername = '';
                                    $cust[''] = lang('select') . ' ' . lang('Customer');
                                    foreach($customers as $customer)
                                    {
                                        if(isset($_POST['customer'])){
                                            if($customer->id == $_POST['customer']){
                                               $customername = $customer->name;
                                            }
                                        }
                                        
                                        $cust[$customer->id] = $customer->name.(($customer->company != '-' && $customer->company != '') ?' ('.$customer->company.')' :'');
                                        echo '<option value="'.$customer->id.'" '
                                            .($customer->id == $_POST['customer'] ? 'selected' : '').'>'
                                            .$customer->name
                                            .(!empty($customer->phone) ? ' ('.$customer->phone.')' : '')
                                            .(!empty($customer->cf1) ? ' ('.$customer->cf1.')' : '')
                                            .(!empty($customer->cf2) ? ' ('.$customer->cf2.')' : '')
                                            .'</option>';
                                        
                                            } 
                                    
                                                                    //                                    echo form_dropdown('customer', $cust, (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control " id="customer" placeholder="' . lang("select") . " " . lang("customer") . '" style="width:100%"')
                                ?>  
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group choose-date hidden-xs">
                                <div class="controls">
                                    <?= lang("date_range", "date_range"); ?>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text"
                                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] . '-' . $_POST['end_date'] : ""; ?>"
                                               id="daterange_new" class="form-control">
                                        <span class="input-group-addon" style="display:none;"><i class="fa fa-chevron-down"></i></span>
                                        <input type="hidden" name="start_date" id="start_date"
                                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ""; ?>">
                                        <input type="hidden" name="end_date" id="end_date"
                                               value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ""; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
		            </div>
                    <div class="form-group">
                        <div class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>                             
                            <a href="reports/restbutton" class="btn btn-success"  onClick="resetFunction();">Reset</a> 

                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>
                 <div class="biller_details text-center">
                    <h3 ><?= $biller->company ?></h3>
                    <p><?= ($biller->address?$biller->address .'<br/>':'') ?>
                       <?= $biller->city ?>, <?= $biller->state ?> - <?= $biller->postal_code ?> <br/>
                       <?php if($biller->phone){ ?>
                         <strong> Phone : </strong> <?= $biller->phone ?><br/>
                       <?php } 
                        if($biller->email){ ?>
                         <strong> Email : </strong> <?= $biller->email ?><br/>
                       <?php } 
                        if($biller->gstn_no){ ?>
                         <strong> GSTIN : </strong> <?= $biller->gstn_no ?><br/>
                       <?php } ?>  
                         
                         <strong>Customer Name : <?= $customername ?></strong><br/>
                        <?php if (!empty($selected_customer)) { ?>
                            <?php if (!empty($selected_customer->cf1)) { ?>
                                <strong><?= $cf_labels['cf1'] ?> :</strong> <?= $selected_customer->cf1 ?><br/>
                            <?php } ?>

                            <?php if (!empty($selected_customer->cf2)) { ?>
                                <strong><?= $cf_labels['cf2'] ?> :</strong> <?= $selected_customer->cf2 ?><br/>
                            <?php } ?>

                        <?php } ?>
 
                         <br/>
                         <?php
                            if($_POST['start_date']){
                                echo '<strong> Date : '.$_POST['start_date'] . ' - ' . $_POST['end_date'].'  </strong>';
                            }
                         
                         ?>
                    </p>
                   
                 
                </div>                           
                <div class="table-responsive">
                    <table id="CusData"
                           class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                           style="margin-bottom:5px;">
                        <thead>
                        <tr class="active">
                            <th><?= lang("Date"); ?></th>
                            <th><?= lang("Voucher_no"); ?></th>
                            <th><?= lang("Particulars"); ?></th>
                            <th><?= lang("Opening_Balance"); ?></th>
                            <th><?= lang("Debit"); ?></th>
                            <th><?= lang("Credit"); ?></th>
                            <th><?= lang("Balance"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- <tr>
                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr> -->
                        <tr>
                            <td colspan="7" class="les_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <!-- <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?= lang("Debit"); ?></th>
                            <th><?= lang("Credit"); ?></th>
                            <th></th>
                        </tr>
                        </tfoot> -->
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
            window.location.href = "<?=site_url('reports/getCustomerLedgerv1?v=1&export=pdf' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCustomerLedgerv1?v=1&export=xls' . $v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
			window.location.href = "<?=site_url('reports/getCustomerLedgerv1?v=1&export=img' . $v)?>";
            /*html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });*/
            return false;
        });
        
        // loadReport(1);
        
    });
    
    function resetFunction(){
       $('form#search-form input[type=hidden].search-value').val('');
     // location.reload(true);
    }

</script>
