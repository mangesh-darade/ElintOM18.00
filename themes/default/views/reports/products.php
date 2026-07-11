<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
#SRData th:first-child, #PrRData th:nth-child(2) {
    width: 9%!important;
}
#PrRData th {
    width: 8%!important;
}
#PrRData td:nth-child(3), #PrRData td:nth-child(4), #PrRData td:nth-child(5), #PrRData td:nth-child(6), #PrRData td:nth-child(7) {
    text-align: right;
    width: 9%!important;
}
</style>
<?php
$user_warehouse = $this->session->userdata('warehouse_id');

$v = "";

if($this->input->post('product'))
{
    $v .= "&product=" . $this->input->post('product');
}
if($this->input->post('category'))
{
    $v .= "&category=" . $this->input->post('category');
}
if($this->input->post('brand'))
{
    $v .= "&brand=" . $this->input->post('brand');
}
if($this->input->post('subcategory'))
{
    $v .= "&subcategory=" . $this->input->post('subcategory');
}
if($this->input->post('warehouse'))
{
    $v .= "&warehouse=" . $this->input->post('warehouse');
}else{
    $v .=($user_warehouse=='0' ||$user_warehouse==NULL)?'': "&warehouse=" . str_replace(",", "_",$user_warehouse);
}
if($this->input->post('start_date'))
{
    $v .= "&start_date=" . $this->input->post('start_date');
}
if($this->input->post('end_date'))
{
    $v .= "&end_date=" . $this->input->post('end_date');
}
if($this->input->post('cf1'))
{
    $v .= "&cf1=" . $this->input->post('cf1');
}
if($this->input->post('cf2'))
{
    $v .= "&cf2=" . $this->input->post('cf2');
}
if($this->input->post('cf3'))
{
    $v .= "&cf3=" . $this->input->post('cf3');
}
if($this->input->post('cf4'))
{
    $v .= "&cf4=" . $this->input->post('cf4');
}
if($this->input->post('cf5'))
{
    $v .= "&cf5=" . $this->input->post('cf5');
}
if($this->input->post('cf6'))
{
    $v .= "&cf6=" . $this->input->post('cf6');
}
if($this->input->post('with_or_without_gst'))
{
    $v .= "&with_or_without_gst=" . $this->input->post('with_or_without_gst');
}

?>
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
<script>
    var showBatchColumn = <?= (($this->data['Settings']->product_batch_setting == 1 || $this->data['Settings']->product_batch_setting == 2) ? 'true' : 'false') ?>;
    var showCategoryColumn = <?= ($this->data['Settings']->other_category_for_product == 1 ? 'true' : 'false') ?>;
</script>

<script>
$(document).ready(function () {
    // Custom sorting function for data-order attributes
    $.fn.dataTable.ext.order['data-order'] = function (settings, col) {
        return this.api().column(col, {order:'index'}).nodes().map(function (td, i) {
            var orderValue = $(td).attr('data-order');
            return orderValue !== undefined ? parseFloat(orderValue) : 0;
        });
    };

    $.ajax({
        type: "get",
        url: 'reports/getProductsReport',
        data: "<?= 'v=1' . $v ?>",
        beforeSend: function () {
            $("#PrRData").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i>Loading data from server</div>");
        },
        success: function (data) {
            $("#PrRData").html(data);

            // Build column definitions dynamically based on settings
            let columns = [
                null,   // Code
                { className: "text-left" }, // Product Name
                { className: "text-left" }, // Variant Name
                { className: "text-left" }, // Color
            ];

            if (showBatchColumn) {
                columns.push({ className: "text-left" }); // Batch No
            }

            columns.push(
                { className: "text-left" }, // Category Name
                { className: "text-left" }, // Brand Name
                { 
                    "bSearchable": false, 
                    className: "text-center",
                    "orderDataType": "data-order"
                }, // Purchased Qty
                { 
                    "bSearchable": false, 
                    className: "text-right",
                    "orderDataType": "data-order"
                }, // Purchased
                { 
                    "bSearchable": false, 
                    className: "text-center",
                    "orderDataType": "data-order"
                }, // Sold Qty
                { 
                    "bSearchable": false, 
                    className: "text-right",
                    "orderDataType": "data-order"
                }, // Sold
                { 
                    "bSearchable": false, 
                    className: "text-right",
                    "orderDataType": "data-order"
                }, // Profit
                { 
                    "bSearchable": false, 
                    className: "text-center",
                    "orderDataType": "data-order"
                }, // Stock Qty
                { 
                    "bSearchable": false, 
                    className: "text-right",
                    "orderDataType": "data-order"
                }  // Stock Amt
            );

            // Initialize DataTable
            var table = $('#PrData1').DataTable({
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "aaSorting": [[6, "desc"]],
                "aoColumns": columns,
                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api();

                    // Helper to sum a column
                    // var sumColumn = function (colIndex) {
                    //     return api.column(colIndex, { page: 'current' }).data().reduce(function (a, b) {
                    //         // return parseFloat(a) + parseFloat(String(b).replace(/,/g, ''));
                    //         return parseFloat(a) + parseFloat(String(b).replace(/[^0-9.-]+/g, ''));
                    //     }, 0);
                    // };
                    var sumColumn = function (colIndex) {
                        var total = 0;

                        api.column(colIndex, { page: 'current' }).nodes().each(function (cell) {
                            var val = $(cell).attr('data-order');

                            if (val !== undefined) {
                                total += parseFloat(val);
                            }
                        });

                        return total;
                    };

                    // Adjust column indices dynamically depending on batch column visibility
                    let colOffset = showBatchColumn ? 1 : 0;
                    let baseIndex = 6 + (showBatchColumn ? 1 : 0); // Increased by 1 to account for brand column

                    $(api.column(baseIndex).footer()).html(formatQuantity(sumColumn(baseIndex))); // Purchased Qty
                    $(api.column(baseIndex + 1).footer()).html(currencyFormat(sumColumn(baseIndex + 1))); // Purchased
                    $(api.column(baseIndex + 2).footer()).html(formatQuantity(sumColumn(baseIndex + 2))); // Sold Qty
                    $(api.column(baseIndex + 3).footer()).html(currencyFormat(sumColumn(baseIndex + 3))); // Sold
                    $(api.column(baseIndex + 4).footer()).html(currencyFormat(sumColumn(baseIndex + 4))); // Profit
                    $(api.column(baseIndex + 5).footer()).html(formatQuantity(sumColumn(baseIndex + 5))); // Stock Qty
                    $(api.column(baseIndex + 6).footer()).html(currencyFormat(sumColumn(baseIndex + 6))); // Stock Amt
                }
            });
        },
        error: function () {
            alert("An error occurred while loading data.");
        }
    });
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
<script type="text/javascript">
    $(document).ready(function () {
        // $('#category').select2({allowClear: true, placeholder: "<?= lang('select'); ?>", minimumResultsForSearch: 7}).select2('destroy');
        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            allowClear: true,
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
        $('#category').change(function () {
            var v = $(this).val();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                allowClear: true,
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        } else {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                                allowClear: true,
                                placeholder: "<?= lang('no_subcategory') ?>",
                                data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                    }
                });
            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    allowClear: true,
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
        });
        <?php if (isset($_POST['category']) && ! empty($_POST['category'])) { ?>
        $.ajax({
            type: "get", async: false,
            url: "<?= site_url('products/getSubCategories') ?>/" + <?= $_POST['category'] ?>,
            dataType: "json",
            success: function (scdata) {
                if (scdata != null) {
                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                        allowClear: true,
                        placeholder: "<?= lang('no_subcategory') ?>",
                        data: scdata
                    });
                }
            }
        });
        <?php } ?>
    });
</script>
<style>

    div.dataTables_length select {
        height: 30px;
        width: 60px;
    }
    input[type=search] {
        height: 30px;
        width: 200px;
        padding: 5px;
        margin-left: 10px;  /* Adds space between the label and the input */
        margin-top: 5px; 
    }
   /* #PrRData td:nth-child(3), 
   #PrRData td:nth-child(4), 
   #PrRData td:nth-child(5), 
   #PrRData td:nth-child(6), 
   #PrRData td:nth-child(7), 
   #PrRData td:nth-child(8),
   #PrRData td:nth-child(9) {
    text-align: right;
    
} */
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('products_report'); ?> <?php
            if($this->input->post('start_date'))
            {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>

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
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo form_open("reports/products"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product"
                                       value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>"
                                       id="report_product_id"/>
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

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("subcategory", "subcategory") ?>
                                <div class="controls" id="subcat_data"> <?php
                                    echo form_input('subcategory', (isset($_POST['subcategory']) ? $_POST['subcategory'] : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                                    ?>
                                </div>
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
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $permisions_werehouse = explode(",", $user_warehouse);
                                $wh[""] = lang('select') . ' ' . lang('warehouse');
                                foreach($warehouses as $warehouse)
                                {
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

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf1', 'cf1') ?>
                                <?= form_input('cf1', (isset($_POST['cf1']) ? $_POST['cf1'] : ''), 'class="form-control tip" id="cf1"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf2', 'cf2') ?>
                                <?= form_input('cf2', (isset($_POST['cf2']) ? $_POST['cf2'] : ''), 'class="form-control tip" id="cf2"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf3', 'cf3') ?>
                                <?= form_input('cf3', (isset($_POST['cf3']) ? $_POST['cf3'] : ''), 'class="form-control tip" id="cf3"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf4', 'cf4') ?>
                                <?= form_input('cf4', (isset($_POST['cf4']) ? $_POST['cf4'] : ''), 'class="form-control tip" id="cf4"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf5', 'cf5') ?>
                                <?= form_input('cf5', (isset($_POST['cf5']) ? $_POST['cf5'] : ''), 'class="form-control tip" id="cf5"') ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf6', 'cf6') ?>
                                <?= form_input('cf6', (isset($_POST['cf6']) ? $_POST['cf6'] : ''), 'class="form-control tip" id="cf6"') ?>
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

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="With_Or_Without_Gst"><?= lang("With_Or_Without_Gst"); ?></label>
                                <?php
                                $w_gst["with_gst"] = 'With Gst';
                                $w_gst["without_gst"] = 'Without Gst';;
                                /*foreach($warehouses as $warehouse)
                                {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }*/

                                echo form_dropdown('with_or_without_gst', $w_gst, (isset($_POST['with_or_without_gst']) ? $_POST['with_or_without_gst'] : ""), 'class="form-control" id="with_or_without_gst" ');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                            <!--<input type="button" id="report_reset" data-value="<?= base_url('reports/products'); ?>"
                                   name="submit_report" value="Reset" class="btn btn-warning input-xs">-->
                                <a href="reports/restbutton" class="btn btn-success">Reset</a>

                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive" id="PrRData">
                        <?=lang('loading_data_from_server')?> 
                        </div>
                    </div>
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
            window.location.href = "<?=site_url('reports/getProductsReport/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getProductsReport/0/xls/?v=1' . $v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
			window.location.href = "<?=site_url('reports/getProductsReport/0/0/img/?v=1' . $v)?>";
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
