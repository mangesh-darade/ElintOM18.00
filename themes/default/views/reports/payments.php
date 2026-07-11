<style>
    thead th:nth-child(6) .yadcf-filter-wrapper input
    {
    text-align:left !important;
    
    }

    tbody td:nth-child(6) 
    {
        text-align:left !important;
    }
</style>

<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$user_warehouse = $this->session->userdata('warehouse_id');
$v = "";
$selected_pm = "";
$default_pm = "";
if ($payment_methods) {
    foreach ($payment_methods as $pm) {
        if (isset($pm->is_default) && $pm->is_default == 1) {
            $default_pm = $pm->type;
            break;
        }
    }
}
if ($this->input->post('submit_report') || $this->input->post('payment_method') !== NULL) {
    $selected_pm = $this->input->post('payment_method');
} elseif ($this->input->get('payment_method') !== NULL) {
    $selected_pm = $this->input->get('payment_method');
} else {
    $selected_pm = $default_pm;
}
/* if($this->input->post('name')){
  $v .= "&name=".$this->input->post('name');
  } */
if ($this->input->post('payment_ref')) {
    $v .= "&payment_ref=" . $this->input->post('payment_ref');
}
if ($this->input->post('sale_ref')) {
    $v .= "&sale_ref=" . $this->input->post('sale_ref');
}
if ($this->input->post('purchase_ref')) {
    $v .= "&purchase_ref=" . $this->input->post('purchase_ref');
}
if ($this->input->post('supplier')) {
    $v .= "&supplier=" . $this->input->post('supplier');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('cheque')) {
    $v .= "&cheque=" . $this->input->post('cheque');
}
if ($selected_pm) {
    $v .= "&payment_method=" . $selected_pm;
}
if ($this->input->post('tid')) {
    $v .= "&tid=" . $this->input->post('tid');
}
if ($this->input->post('card')) {
    $v .= "&card=" . $this->input->post('card');
}
if ($this->input->post('start_date')) {
    $startDate = explode('/', substr($this->input->post('start_date'), 0, 10));
    $start_date = $startDate[2] . "-" . $startDate[1] . "-" . $startDate[0] . "  00:00";
    $v .= "&start_date=" . $start_date;
}
if ($this->input->post('end_date')) {
    $endDate = explode('/', substr($this->input->post('end_date'), 0, 10));
    $end_date = $endDate[2] . "-" . $endDate[1] . "-" . $endDate[0] . "  23:59";
    $v .= "&end_date=" . $end_date;
}
?>
<style>
    tbody td:nth-child(8){
        text-align:right;
    }
</style>
<script>
    $(document).ready(function () {
        var pb = <?= json_encode($pb); ?>;
        function paid_by(x) {
            return (x != null) ? (pb[x] ? pb[x] : x) : x;
        }

        function ref(x) {
            return (x != null) ? x : ' ';
        }

        var oTable = $('#PayRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getPaymentsReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null, {"mRender": ref}, {"mRender": ref}, {"mRender": ref}, {"mRender": paid_by}, null, {"mRender": currencyFormat}, {"mRender": row_status}, null, {"bVisible": false}, {"bVisible": false}, {"bVisible": false}],

            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[12];
                nRow.className = "payment_link";
                if (aData[8] == 'sent') {
                    nRow.className = "payment_link2 warning";
                } else if (aData[8] == 'returned') {
                    nRow.className = "payment_link danger";
                }
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = currencyFormat(parseFloat(total));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?= lang('Customer name'); ?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?= lang('payment_ref'); ?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?= lang('reference_no'); ?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?= lang('paid_by'); ?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?= lang('type'); ?>]", filter_type: "text", data: []},
        ], "footer");

    });

    $('body').on('click', '.payment_link td', function () {
        $('#myModal').modal({remote: site.base_url + 'sales/payment_note/' + $(this).parent('.payment_link').attr('id')});
        $('#myModal').modal('show');
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
<?php if ($this->input->post('biller')) { ?>
            $('#rbiller').select2({allowClear: true});
<?php } ?>
<?php if ($this->input->post('supplier')) { ?>
            $('#rsupplier').val(<?= $this->input->post('supplier') ?>).select2({
                minimumInputLength: 1,
                allowClear: true,
                initSelection: function (element, callback) {
                    $.ajax({
                        type: "get", async: false,
                        url: "<?= site_url('suppliers/getSupplier') ?>/" + $(element).val(),
                        dataType: "json",
                        success: function (data) {
                            callback(data[0]);
                        }
                    });
                },
                ajax: {
                    url: site.base_url + "suppliers/suggestions",
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
            $('#rsupplier').val(<?= $this->input->post('supplier') ?>);
<?php } ?>
<?php if ($this->input->post('customer')) { ?>
            /* $('#rcustomer').val(<?= $this->input->post('customer') ?>).select2({
             minimumInputLength: 1,
             allowClear: true,
             initSelection: function (element, callback) {
             $.ajax({
             type: "get", async: false,
             url: "<?= site_url('customers/getCustomer') ?>/" + $(element).val(),
             dataType: "json",
             success: function (data) {
             callback(data[0]);
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
             }); */
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
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('payments_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
        </h2>
        <div class="col-sm-4">
            <div class="col-sm-6" >
                <h4 class="control-label" for="sales">Download limit</h4></div>
<?php 
$startcount = 0;
$count = $paymentcount;
$addcount = 1000;
$endcount = 1000;
$seccount = 0; ?>
            <div class="col-sm-6">
                <select class="form-control" name="limitpdf" id="limitpdf">
                    <option value="0">Select</option>
                    <?php
                    for ($startcount = 0; $count >= $startcount; $startcount = $startcount + $endcount) {
                        $seccount = $startcount + $endcount;
                        ?>
                        <option value="<?php echo $startcount . '-' . $endcount; ?>"> <?php echo $startcount . '-' . $seccount; ?></option>
<?php } ?>
                </select>
            </div>
        </div>
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
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <p class="introtext"><?= lang('customize_report'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">


                <div id="form">

                                <?php echo form_open("reports/payments"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
<?= lang("payment_ref", "payment_ref"); ?>
<?php echo form_input('payment_ref', (isset($_POST['payment_ref']) ? $_POST['payment_ref'] : ""), 'class="form-control tip" id="payment_ref"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
<?= lang("sale_ref", "sale_ref"); ?>
<?php echo form_input('sale_ref', (isset($_POST['sale_ref']) ? $_POST['sale_ref'] : ""), 'class="form-control tip" id="sale_ref"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
<?= lang("purchase_ref", "purchase_ref"); ?>
<?php echo form_input('purchase_ref', (isset($_POST['purchase_ref']) ? $_POST['purchase_ref'] : ""), 'class="form-control tip" id="purchase_ref"'); ?>

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
                            <div class="form-group">
                                <label class="control-label" for="rcustomer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="rcustomer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                                <?php
//                                $cust[""] = lang('select') . ' ' . lang('Customer');
//                                foreach ($customer as $customer_val) {
//                                        $cust[$customer_val->id] = (($customer_val->company && ($customer_val->company !='-'))?$customer_val->company.' ('.$customer_val->name.')':$customer_val->name);
//                                      
//                                }
//                                echo form_dropdown('customer', $cust, (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="rcustomer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"');
                                ?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="rbiller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[''] = '';
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="rbiller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
<?= lang("supplier", "rsupplier"); ?>
<?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="rsupplier" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("supplier") . '"'); ?> </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
<?= lang("transaction_id", "tid"); ?>
<?php echo form_input('tid', (isset($_POST['tid']) ? $_POST['tid'] : ""), 'class="form-control" id="tid"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
<?= lang("card_no", "card"); ?>
<?php echo form_input('card', (isset($_POST['card']) ? $_POST['card'] : ""), 'class="form-control" id="card"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
<?= lang("cheque_no", "cheque"); ?>
<?php echo form_input('cheque', (isset($_POST['cheque']) ? $_POST['cheque'] : ""), 'class="form-control" id="cheque"'); ?>
                            </div>
                        </div>
                                <!-- sun -->
                        <div class="col-sm-4" id="payment_method">
                            <div class="form-group">
                                <label class="control-label" for="payment_method"><?= lang("Payment Method"); ?></label>
                                <?php
                                $p_modes[""] = lang("select") . " " . lang("Payment Method");
                                if ($payment_methods) {
                                    foreach ($payment_methods as $pm) {
                                        $p_modes[$pm->type] = $pm->display_name;
                                    }
                                }
                                echo form_dropdown('payment_method', $p_modes, $selected_pm, 'class="form-control load_report" id="payment_method1" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("Payment Mode") . '"');
                                ?>
                            </div>
                        </div>
                        <!--  -->
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
                        <div class="col-sm-4">                        
                            <div class="form-group choose-date hidden-xs">
                                <div class="controls">
<?= lang("date_range", "date_range"); ?>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text"
                                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] . '-' . $_POST['end_date'] : ""; ?>"
                                               id="daterange_new" class="form-control" autocomplete="off">
                                        <!--<span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>-->
                                        <input type="hidden" name="start_date"  id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ""; ?>">
                                        <input type="hidden" name="end_date"  id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ""; ?>" >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
<?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>

<!--<input type="button" id="report_reset" data-value="<?= base_url('reports/payments'); ?>" name="submit_report" value="Reset" class="btn btn-warning input-xs"> -->
                            <a href="<?= base_url('reports/payments'); ?>" class="btn btn-warning input-xs">Reset</a>
                        </div>
                    </div>
<?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>


                <div class="table-responsive">
                    <table id="PayRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">

                        <thead>
                            <tr>
                                <th><?= lang("date"); ?></th>
                                <th><?= lang("Customer name"); ?></th>
                                <th><?= lang("payment_ref"); ?></th>
                                <th><?= lang("reference_no"); ?></th>
                                <th><?= lang("Invoice_No"); ?></th>
                                <th style="text-align: left;"><?= lang("paid_by"); ?></th>
                                <th><?= lang("transaction_id"); ?></th>
                                <th><?= lang("amount"); ?></th>
                                <th><?= lang("type"); ?></th>
                                <th><?= lang("note"); ?></th>
                                <th style="display: none;"><?= lang("purchase_ref"); ?></th>
                                <th style="display: none;"><?= lang("Order_ref"); ?></th>
                                <th style="display: none;"><?= lang("id"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
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
            var limitcnt = $("#limitpdf option:selected").val();
            if (limitcnt == '0') {
                alert('Please Select Pdf/Excel limit');
            } else {
<?php $v .= "&strtlimit=" ?>
                window.location.href = "<?= site_url('reports/getPaymentsReport/pdf/?v=1' . $v) ?>" + limitcnt;
                $("#limitpdf").val(0).change();
                return false;
            }
            //window.location.href = "<?= site_url('reports/getPaymentsReport/pdf/?v=1' . $v) ?>";
            //return false;
        });

        $('#xls').click(function (event) {
            event.preventDefault();
            var limitcnt = $("#limitpdf option:selected").val();
            if (limitcnt == '0') {
                alert('Please Select Pdf/Excel limit');
            } else {
<?php $v .= "&strtlimit=" ?>
                window.location.href = "<?= site_url('reports/getPaymentsReport/0/xls/?v=1' . $v) ?>" + limitcnt;
                $("#limitpdf").val(0).change();
                return false;

            }
            // window.location.href = "<?= site_url('reports/getPaymentsReport/0/xls/?v=1' . $v) ?>";
            //return false;
        });

        $('#image').click(function (event) {
            event.preventDefault();
            window.location.href = "<?= site_url('reports/getPaymentsReport/0/0/img/?v=1' . $v) ?>";
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