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
else{
    $v .= "&start_date=" . date('Y-m-01');
}
if($this->input->post('end_date'))
{
    $v .= "&end_date=" . $this->input->post('end_date');
}
else{
    $v .= "&end_date=" . date('Y-m-t');
}

 
?>
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

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
</style>
<div class="box">
    <div class="box-header">
            <h2 class="blue">
            <i class="fa-fw fa fa-barcode"></i>
            <?= lang('Deposit Ledgers'); ?>
            <?php
                $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : date('Y-m-01');
                $end_date   = $this->input->post('end_date')   ? $this->input->post('end_date')   : date('Y-m-t');

                echo " From " . $start_date . " to " . $end_date;
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
<!--                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>-->
                 <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li> 
<!--                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>-->
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form">
                    <?php echo form_open("reports/customerDepositLedger"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                
                                <?= lang("Customers (Used Deposit Wallet Only)", "Customer"); ?>
                                <select class="form-control" name="customer">
                                      <option value="0"> Select Customer </option>
                                <?php
                               
                                $customername = '';
                                    $cust[''] = lang('select') . ' ' . lang('Customer');
                                    if(!empty($customers)) {
                                        foreach($customers as $customer)
                                        {
                                            if(isset($_POST['customer'])){
                                                if($customer->id == $_POST['customer']){
                                                   $customername = $customer->name;
                                                }
                                            }

                                            $cust[$customer->id] = $customer->name.(($customer->company != '-' && $customer->company != '') ?' ('.$customer->company.')' :'');
                                            echo '<option value="'.$customer->id.'"'.($customer->id == $_POST['customer']?'selected' : '').' >'.$customer->name.(($customer->company != '-' && $customer->company != '') ?' ('.$customer->company.')' :'').'</option>';

                                        } 
                                    }
//                                    echo form_dropdown('customer', $cust, (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control " id="customer" placeholder="' . lang("select") . " " . lang("customer") . '" style="width:100%"')
                                ?>  
                                </select>
                            </div>
                        </div>
                        
                        
                        
                        
                        <div class="col-sm-4">
                            <div class="form-group choose-date hidden-xs">
                                <div class="controls">
                                    <?=  lang("date_range", "date_range"); ?>
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
                                <a href="reports/restbutton" class="btn btn-success">Reset</a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>
                
                <div class="table-responsive" id="table_body">
                    <table class="table table-striped table-bordered table-condensed table-hover dfTable reports-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Phone</th>
                                <th>Card No.</th>
                                <th>Room No.</th>
                                <th>Total Deposit Amount</th>
                                <th>Balance Amount</th>
                                <th>Spent Amount (In Record) </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            if(!empty($customers)) {
                                        foreach($customers as $customer)
                                        {                                             
                                            ?>
                                               <tr>
                                                   <td class="text-left"><?=$customer->id?></td>
                                                   <td><?= ucwords($customer->name)?></td>
                                                   <td><?=$customer->phone?></td>
                                                   <td><?=$customer->card_no?></td>
                                                   <td><?=$customer->room_no?></td>
                                                   <td class="text-right">Rs. <?= number_format($customer->total_deposit, 2)?></td>
                                                   <td class="text-right">Rs. <?= number_format($customer->balance_amount,2)?></td>
                                                   <td class="text-right">Rs. <?= number_format($customer->spent_amount,2)?></td>
                                               </tr>

                                          <?php
                                          
                                                $deposits_total += $customer->total_deposit;
                                                $balance_total += $customer->balance_amount;
                                                $spent_total += $customer->spent_amount;
                                            } 
                                    }
                        ?>            
                            
                        </tbody>
                        <tfoot>
                            <tr>
                                
                                <th colspan="5">Total</th>
                                <th class="text-right">Rs. <?= number_format($deposits_total,2)?></th>
                                <th class="text-right">Rs. <?= number_format($balance_total,2)?></th>
                                <th class="text-right">Rs. <?= number_format($spent_total,2)?></th>
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
//        $('#pdf').click(function (event) {
//            event.preventDefault();
//            window.location.href = "<?=site_url('reports/getCustomerDepositLedger?v=1&export=pdf' . $v)?>";
//            return false;
//        });
        $('#xls').click(function (event) {
            event.preventDefault();
           <?php if($v) { ?>
                window.location.href = "<?=site_url('reports/getCustomerDepositLedger?v=1&export=xls' . $v)?>";
           <?php } else { ?>
                window.location.href = "<?=site_url('reports/customerDepositLedger?v=1&export=xls' . $v)?>";
           <?php } ?>
            return false;
        });
//        $('#image').click(function (event) {
//            event.preventDefault();
//			window.location.href = "<?=site_url('reports/getCustomerDepositLedger?v=1&export=img' . $v)?>";
//            /*html2canvas($('.box'), {
//                onrendered: function (canvas) {
//                    var img = canvas.toDataURL()
//                    window.open(img);
//                }
//            });*/
//            return false;
//        });
     
    <?php if($v) { ?>
       loadReport(1);
    <?php } ?>
        
    });
    
    // function loadReport(page){    
    //     $.ajax({
    //         type: "POST",
    //         url: "<?= site_url('reports/load_ajax_reports')?>",
    //         data:'action=CustomerDepositLadger&page='+page+'<?= $v?>',
    //         beforeSend: function(){
    //             $("#table_body").html('<tr><td colspan="6"><div class="overlay"><i class="fa fa-refresh fa-spin"></i></div></td></tr>');
    //         },
    //         success: function(data){
    //            // console.log(data);
    //             $("#table_body").html(data);			 
    //         }
	// });    
    // }
    function loadReport(page){    
    $.ajax({
        type: "POST",
        url: "<?= site_url('reports/load_ajax_reports')?>",
        data: 'action=CustomerDepositLadger&page='+page+'<?= $v?>',
        beforeSend: function(){
            $("#table_body").html('<tr><td colspan="6"><div class="overlay"><i class="fa fa-refresh fa-spin"></i></div></td></tr>');
        },
        success: function(data){
            // inject table HTML
            $("#table_body").html(data);

            // destroy if already initialized
            if ($.fn.DataTable.isDataTable('.reports-table')) {
                $('.reports-table').DataTable().destroy();
            }

            // initialize DataTable
            $('.reports-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "ordering": true,
                "searching": true,
                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api();

                    // Utility function to clean and parse currency values
                    var parseCurrency = function (value) {
                        if (value == null) return 0;
                        if (typeof value === 'number') return value;

                        var str = String(value)
                            .replace(/,/g, '')     // remove thousand separators
                            .replace(/[₹\s]/g, ''); // remove rupee symbol and spaces

                        // Handle negatives with parentheses
                        var negative = /\(.*\)/.test(str);
                        if (negative) str = str.replace(/[()]/g, '');

                        // Extract first numeric portion (skip "Rs.")
                        var m = str.match(/-?\d+(?:\.\d+)?/);
                        var num = m ? parseFloat(m[0]) : 0;

                        return negative ? -num : num;
                    };

                    // Columns: Opening Amount (5), Recharge (6), Spent (7), Closing (8), Balance (9)
                    [5, 6, 7, 8, 9].forEach(function (colIndex) {
                        var total = api.column(colIndex, { page: 'current' }).data()
                            .toArray()
                            .reduce(function (sum, val) { return sum + parseCurrency(val); }, 0);

                        // Update the footer cell
                        $(api.column(colIndex).footer()).html(currencyFormat(total));
                    });
                }
            });
        }
    });    
}


</script>
