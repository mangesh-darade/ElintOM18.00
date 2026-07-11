<style>
    
    thead th:nth-child(9) .yadcf-filter-wrapper input,
    thead th:nth-child(10) .yadcf-filter-wrapper input,
    thead th:nth-child(11) .yadcf-filter-wrapper input
    {
    text-align: right;
    
}

tbody td:nth-child(8), 
tbody td:nth-child(9), 
tbody td:nth-child(10),
tbody td:nth-child(11) 
{
    text-align:right;
}
thead th:nth-child(7) .yadcf-filter-wrapper input
    {
    text-align: center;
    
}
tbody td:nth-child(7) 
{
    text-align:center;
}
</style>

<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$user_warehouse = $this->session->userdata('warehouse_id');

$permisions_werehouse = explode(",", $this->session->userdata('warehouse_id'));

$v = "";
/* if($this->input->post('name')){
  $v .= "&product=".$this->input->post('product');
  } */
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
}else{
    $v .=($user_warehouse=='0' ||$user_warehouse==NULL)?'':"&warehouse=" . str_replace(",", "_",$user_warehouse);
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('serial')) {
    $v .= "&serial=" . $this->input->post('serial');
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

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();

        //  If no date → DO NOT initialize datatable
        if (start_date == '' || end_date == '') {
            var start = moment().subtract(29, 'days').format('DD/MM/YYYY');
            var end = moment().format('DD/MM/YYYY');

            $('#start_date').val(start);
            $('#end_date').val(end);
            $('#daterange_new').val(start + ' - ' + end);

            start_date = start;
            end_date = end;
        }
        //  Initialize only if date exists
        var oTable = $('#SlRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getChallansReport/?v=1'. $v) ?>',

            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });

                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },

            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[14];
                nRow.className = "challan_link2";
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {

                var gtotal = 0, paid = 0, balance = 0;

                for (var i = 0; i < aiDisplay.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][8]) || 0;
                    paid += parseFloat(aaData[aiDisplay[i]][9]) || 0;
                    balance += parseFloat(aaData[aiDisplay[i]][10]) || 0;
                }

                var nCells = nRow.getElementsByTagName('th');

                nCells[8].innerHTML = currencyFormat(gtotal);
                nCells[9].innerHTML = currencyFormat(paid);
                nCells[10].innerHTML = currencyFormat(balance);
            },
            "aoColumns": [
                {"mRender": fld}, 
                null, 
                null,
                null,
                null, 
                null, 
                null,
                {"mRender": pqFormat},
                {"mRender": currencyFormat},
                {"mRender": currencyFormat},
                {"mRender": currencyFormat},
                {"mRender": row_status}, // challan_status
                {"mRender": row_status},
                null, // paid_by
                {"bVisible": false}, // id
                {"bVisible": false} // tax_details
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('Challan No');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('Invoice No');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('Warehouses');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
            {column_number: 12, filter_type: "select", data: ["paid", "pending", "due"], html5_data: 'text'},
            {column_number: 13, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: [], html5_data: 'text'},
        ], "footer");

    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        var start_date = $('#start_date').val();
    var end_date = $('#end_date').val();

    // 👉 If no date → show form
    if (start_date == '' || end_date == '') {
        $('#form').show();   // open form
    } else {
        $('#form').hide();   // keep hidden if already filtered
    }
        <?php if ($this->input->post('customer')) { ?>
        $('#customer').val(<?= $this->input->post('customer') ?>).select2({
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

        $('#customer').val(<?= $this->input->post('customer') ?>);
        <?php } ?>
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
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('challans_report'); ?> <?php
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
                <!--<li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>-->
            </ul>
        </div>
    </div>
<p class="introtext"><?= lang('customize_report'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                

                <div id="form">

                    <?php echo form_open("reports/challans"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>

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
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $permisions_werehouse = explode(",", $user_warehouse);
                                $wh[""] = lang('select') . ' ' . lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    if($Owner || $Admin ){
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }else if(in_array($warehouse->id,$permisions_werehouse)){
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }    
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        <?php if($Settings->product_serial) { ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('serial_no', 'serial'); ?>
                                    <?= form_input('serial', (isset($_POST['serial']) ? $_POST['serial'] : ""), 'class="form-control tip" id="serial"'); ?>
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
                                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] . '-' . $_POST['end_date'] : ""; ?>"
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
                            
                            <!--<input type="button" id="report_reset" data-value="<?=base_url('reports/challans');?>" name="submit_report" value="Reset" class="btn btn-warning input-xs">-->
                            <a href="<?= site_url('reports/challans') ?>" class="btn btn-success">Reset</a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="SlRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("Challan No"); ?></th>
                            <th><?= lang("Invoice No"); ?></th>
                            <th><?= lang("biller"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("Warehouses"); ?></th>
                            <th><?= lang("product_qty"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("status"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                            <th><?= lang("paid_by"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="14" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                            <th></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th> <!-- challan_status -->
                            <th></th> <!-- payment_status -->
                            <th></th> <!-- paid_by -->
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
$(document).ready(function () {
    // Load data via AJAX like products report
    // $.ajax({
    //     type: "get",
    //     url: 'reports/getChallansReport',
    //     data: "<?= 'v=1' . $v ?>",
    //     beforeSend: function () {
    //         $("#SlRData").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i>Loading data from server</div>");
    //     },
    //     success: function (data) {
    //         $("#SlRData").html(data);
            
    //         // Initialize DataTable after data is loaded
    //         $('#SlRData').DataTable({
    //             "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
    //             "aaSorting": [[0, "desc"]],
    //             "aoColumns": [
    //                 {"mRender": fld}, 
    //                 null, 
    //                 null, 
    //                 null, 
    //                 null, 
    //                 null, 
    //                 { 
    //                     "mRender": pqFormat1
    //                 }, 
    //                 {"mRender": currencyFormat, "bSearchable": false}, 
    //                 {"mRender": currencyFormat, "bSearchable": false}, 
    //                 {"mRender": currencyFormat, "bSearchable": false}, 
    //                 {"mRender": row_status}
    //             ],
    //             "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
    //                 var gtotal = 0, paid = 0, balance = 0;
    //                 for (var i = 0; i < aaData.length; i++) {
    //                     gtotal += parseFloat(aaData[aiDisplay[i]][7]);
    //                     paid += parseFloat(aaData[aiDisplay[i]][8]);
    //                     balance += parseFloat(aaData[aiDisplay[i]][9]);
    //                 }
    //                 var nCells = nRow.getElementsByTagName('th');
    //                 nCells[7].innerHTML = currencyFormat(parseFloat(gtotal));
    //                 nCells[8].innerHTML = currencyFormat(parseFloat(paid));
    //                 nCells[9].innerHTML = currencyFormat(parseFloat(balance));
    //             }
    //         }).fnSetFilteringDelay().dtFilter([
    //             {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
    //             {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
    //             {column_number: 2, filter_default_label: "[<?=lang('Challan No');?>]", filter_type: "text", data: []},
    //             {column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
    //             {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
    //             {column_number: 10, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
    //         ], "footer");
    //     },
    //     error: function () {
    //         bootbox.alert('<?= lang('ajax_error') ?>');
    //     }
    // });

    $('#pdf').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getChallansReport/pdf/?v=1'.$v)?>";
        return false;
    });

    $('#xls').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getChallansReport/0/xls/?v=1'.$v)?>";
        return false;
    });

    $('#image').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getChallansReport/0/0/img/?v=1'.$v)?>";
        return false;
    });
});

function resetFunction(){
   $('form#search-form input[type=hidden].search-value').val('');
}
</script>