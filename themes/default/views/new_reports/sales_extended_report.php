<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$user_warehouse = $this->session->userdata('warehouse_id');
$v = $v1 = "";
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
} else {
    $v .= ($user_warehouse == '0' || $user_warehouse == NULL) ? '' : "&warehouse=" . str_replace(",", "_", $user_warehouse);
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('serial')) {
    $v .= "&serial=" . $this->input->post('serial');
}

if ($this->input->post('gstn_opt')) {
    $v .= "&gstn_opt=" . $this->input->post('gstn_opt');
}
if ($this->input->post('gstn_no')) {
    $v .= "&gstn_no=" . $this->input->post('gstn_no');
}
if ($this->input->post('hsn_code')) {
    $v .= "&hsn_code=" . $this->input->post('hsn_code');
}
if ($this->input->post('max_export_sales')) {
    $v .= "&max_export_sales=" . $this->input->post('max_export_sales');
}

if ($this->input->post('start_date')) {
    $v1 = $v;
    $st = $this->sma->fld($this->input->post('start_date')) . ":00";
    $v1 .= "&start_date=" . strtotime($st);
    $v .= "&start_date=" . $this->input->post('start_date');
    if (empty($this->input->post('end_date'))) {
        $v .= "&end_date=" . date("d/m/Y") . ' 23:55';
        $_POST['end_date'] = date("d/m/Y") . ' 23:55';
    }
}/* else {
  $start_date = date('d/m/Y', strtotime("-7 days")).' 00:00';
  $v .= "&start_date=" . $start_date;

  } */


if ($this->input->post('end_date')) {
    $et = $this->sma->fld($this->input->post('end_date')) . ":00";
    $v1 .= "&end_date=" . strtotime($et);

    $v .= "&end_date=" . $this->input->post('end_date');
}/* else{

  $end_date = date('d/m/Y H:i');
  $v .= "&end_date=" . $end_date;
  } */
?>
<style>
    #clear_customer {
        position: absolute;
        right: 40px;
        top: 35px;
    }
</style>
<script>
    $(document).ready(function () {

        $("#clear_customer").click(function () {

            $("#customer").select2("val", "");
        });

    });
</script>
<script type="text/javascript">
    $(document).ready(function () {

        $('#form').hide();
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
                    quietMillis: 17,
                    data: function (term, page) {
                        return {
                            term: term,
                            limit: 10
                        };
                    },
                    results: function (data, page) {
                        if (data.results != null) {
                            alert('<?php echo $this->input->post('customer') ?>');
                            return {results: data.results};
                        } else {
                            return {results: [{id: '', text: 'No Match Found'}]};
                        }
                    }
                }
            });

            $('#customer').val('<?php echo $this->input->post('customer') ?>').trigger('change')
<?php } ?>
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });


        load_report(1);

    });



    function load_report(pageNo, xls = 0) {

        pageNo = parseInt(pageNo) ? pageNo : 1;
        var previous_rows = $('#previous_rows').val();
        var per_page_records_top = parseInt($('#per_page_records_top').val()) ? $('#per_page_records_top').val() : 0;
        var per_page_records_bottom = parseInt($('#per_page_records_bottom').val()) ? $('#per_page_records_bottom').val() : 0;

        var per_page_records = (per_page_records_top && per_page_records_top != previous_rows) ? per_page_records_top : (per_page_records_bottom && per_page_records_bottom != previous_rows) ? per_page_records_bottom : previous_rows;
        $('#previous_rows').val(per_page_records);

        var requestURL = $('#ajax_report_request_url').val();

        var search_key_top = $('#search_key_top').val() ? $('#search_key_top').val() : '';
        var search_key_bottom = $('#search_key_bottom').val() ? $('#search_key_bottom').val() : '';
        var search_key = search_key_top ? search_key_top : (search_key_bottom ? search_key_bottom : '');

        var postData = 'action=sales_extended_report_result';
        postData += '&xls=' + xls;
        postData += '&page_no=' + pageNo;
        postData += '&per_page_records=' + per_page_records;
        postData += '&search_key=' + search_key;
        postData += '&reference_no=' + $('#reference_no').val();
        postData += '&user=' + $('#user').val();
        postData += '&customer=' + $('#customer').val();
        postData += '&biller=' + $('#biller').val();
        postData += '&warehouse=' + $('#warehouse').val();
        postData += '&start_date=' + $('#start_date').val();
        postData += '&end_date=' + $('#end_date').val();
        postData += '&gstn_no=' + $('#gstn_no').val();
        //   postData += '&gstn_opt='+$('#gstn_opt').val();
        postData += '&hsn_code=' + $('#hsn_code').val();
        //   postData += '&max_export_sales='+$('#max_export_sales').val();

        $.ajax({
            type: "POST",
            url: requestURL,
            data: postData,
            beforeSend: function () {
                $("#header_cart_content").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i> Loading Cart Items</div>");
            },
            success: function (responseData) {

                $('#report_div').html(responseData);

            }
        });
    }



</script>


<div class="box">
    <div class="box-header" style="max-width:1030px;">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('Sales_Extended Report'); ?> <?php
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
                    <a id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>                 
            </ul>
        </div>
    </div>
    <p class="introtext"><?= lang('customize_report'); ?></p>

    <div class="box-content">
        <div class="row" style="max-width:1030px;">
            <div class="col-lg-12 table-responsive">
                <div id="form">

                <?php //echo form_open("reports_new/sales_extended_report");  ?>
                    <input type="hidden" id="ajax_report_request_url" value="<?= base_url('reports_new/ajax_report_request_url') ?>" />
                    <input type="hidden" id="ajax_report_export_request_url" value="<?= base_url('reports_new/xls_export_sales_extended_report') ?>" />
                    <input type="hidden" id="previous_rows" value="20" />
                    <div class="row">
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
                                $us[""] = lang('select') . ' ' . lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4" style="position: relative;">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                                <a href="javascript:void(0);" id="clear_customer"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select') . ' ' . lang('biller');
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
                                    if ($Owner || $Admin) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    } else if (in_array($warehouse->id, $permisions_werehouse)) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group choose-date hidden-xs">
                                <div class="controls">
                                <?= lang("date_range_sales", "date_range_sales"); ?>
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
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("gstn", "gstn"); ?>
                                <?php echo form_input('gstn_no', (isset($_POST['gstn_no']) ? $_POST['gstn_no'] : ""), 'class="form-control" id="gstn_no"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("hsn_code", "hsn_code"); ?>
                                <?php echo form_input('hsn_code', (isset($_POST['hsn_code']) ? $_POST['hsn_code'] : ""), 'class="form-control" id="hsn_code"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary" onclick="load_report()" '); ?>
                            <a href="<?= base_url('reports_new/sales_extended_report'); ?>" class="btn btn-success">Reset
                                Filter</a></div>
                        <div></div>
                    </div>
                <?php // echo form_close();  ?>

                </div>
                <div class="clearfix"></div>

                <div id="report_div" class="table-responsive">
                    <h1>Please Wait Report Is Loading ....</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {

        $('#xls').click(function (event) {

            var search_key_top = $('#search_key_top').val() ? $('#search_key_top').val() : '';
            var search_key_bottom = $('#search_key_bottom').val() ? $('#search_key_bottom').val() : '';
            var search_key = search_key_top ? search_key_top : (search_key_bottom ? search_key_bottom : '');

            var postData = '?action=export_report';
            postData += '&search_key=' + search_key;
            postData += '&reference_no=' + $('#reference_no').val();
            postData += '&user=' + $('#user').val();
            postData += '&customer=' + $('#customer').val();
            postData += '&biller=' + $('#biller').val();
            postData += '&warehouse=' + $('#warehouse').val();
            postData += '&start_date=' + $('#start_date').val();
            postData += '&end_date=' + $('#end_date').val();
            postData += '&gstn_no=' + $('#gstn_no').val();
            postData += '&hsn_code=' + $('#hsn_code').val();

            event.preventDefault();
            window.location.href = "<?= site_url('reports_new/xls_export_sales_extended_report/') ?>" + postData;
            return false;
        });

    });
</script>