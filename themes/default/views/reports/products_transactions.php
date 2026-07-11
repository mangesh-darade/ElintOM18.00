<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
    //$v .=($user_warehouse=='0' ||$user_warehouse==NULL)?'': "&warehouse=" . str_replace(",", "_",$user_warehouse);
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
if($this->input->post('purchase_date_filter'))
{
    $v .= "&purchase_date_filter=" . $this->input->post('purchase_date_filter');
}
 
?>
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
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
    th.sorting{
        width:80px
    }
</style>
<script type="text/javascript">
    $(document).ready(function () {
        if ($("#start_date").val())
        {
            $("#form").slideUp();
        $('#form').hide();

        }
        else{
            $("#form").slideDown();
        }
    $('.toggle_down').click(function() {
        $("#form").slideDown();
        return false;
    });
    $('.toggle_up').click(function() {
        $("#form").slideUp();
        return false;
    });
    });
 
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
<script>
$(document).ready(function () {

    $('form').on('submit', function (e) {

        var purchaseFilter = $('#purchase_date_filter').val(); // 0 or 1
        var startDate = $('#start_date').val();
        var endDate   = $('#end_date').val();

        // If "Date Wise Purchase" selected
        if (purchaseFilter == '1') {

            if (!startDate || !endDate) {
                e.preventDefault(); // stop form submit

                bootbox.alert({
                    message: 'Please select date',
                    backdrop: true
                });

                return false;
            }
        }
    });

});
</script>

<style>
    .text-bold {
        font-weight: bold !important;
    }
    .search-label{
     font-weight:300;
    }
    .text-right{
        text-align:right;
    }
    .text-left{
        text-align:left;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('Products_Transactions_Report'); ?> <?php
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

                    <?php echo form_open("reports/products_transactions"); ?>
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
                                $wh[""] = lang('select') . ' ' . lang('Warehouse');
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

<!--                        <div class="col-md-4">
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
                        </div>-->
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
                                <label class="control-label" for="With_Or_Without_Gst"><?= lang("Prices Display"); ?></label>
                                <?php
                                $w_gst["with_gst"] = 'Prices Include Tax/GST';
                                $w_gst["without_gst"] = 'Prices Without Tax/GST';;
                                echo form_dropdown('with_or_without_gst', $w_gst, (isset($_POST['with_or_without_gst']) ? $_POST['with_or_without_gst'] : ""), 'class="form-control" id="with_or_without_gst" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="purchase_date_filter"><?= lang("Purchase Date Filter"); ?></label>
                                <?php
                                $pdate[0] = 'All Time Purchase';
                                $pdate[1] = 'Date Wise Purchase';
                                echo form_dropdown('purchase_date_filter', $pdate, (isset($_POST['purchase_date_filter']) ? $_POST['purchase_date_filter'] : ""), 'class="form-control" id="purchase_date_filter" ');
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
                <!-- <div class="d-flx">
                    <span>Show
                        <select id="rowsPerPage" onchange="changeRowsPerPage()">
                            <option value="all">All</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="500">500</option>
                            <option value="1000">1000</option>
                        </select>
                        entries
                    </span>
                    <div>
                        <label for="searchBox" class="search-label">Search:</label>
                        <input type="text" class="brd-setting" id="searchBox" oninput="handleSearch()">
                    </div>
                </div> -->

            <div class="table-responsive" id="table_body">
                        <?=lang('loading_data_from_server')?> 
            </div>
                <div class="d-flx">
                    <div id="entryCount" class="entry-count"></div>
                    <div id="pagination" class="pagination"></div>
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
            window.location.href = "<?=site_url('reports/getProductsTransactionsReport?v=1&export=pdf' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?= site_url('reports/getProductsTransactionsReport?v=1&export=xls' . $v) ?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getProductsTransactionsReport?v=1&export=img' . $v)?>";
                /*html2canvas($('.box'), {
                    onrendered: function (canvas) {
                        var img = canvas.toDataURL()
                        window.open(img);
                    }
                });*/
                return false;
            });
        
            loadReport(1);
        
    });
    
    function loadReport(page){
    
        $.ajax({
            type: "POST",
            url: "<?=site_url('reports/load_ajax_reports')?>",
            data:'action=ProductsTransactionsReport&page='+page+'<?=$v?>',
            beforeSend: function(){
                $("#table_body").html('<tr><td colspan="6"><div class="overlay"><i class="fa fa-refresh fa-spin"></i></div></td></tr>');
            },
            success: function(data){			 
                $("#table_body").html(data);
                var table = $('#PrData2').DataTable({
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "drawCallback": function () {
                    formatTableBody();
                },
                "footerCallback": function (row, data, start, end, display) {

                    function parseCurrency(value) {
                        if (value == null) {
                            return 0; // Handle null or undefined values
                        }
                        value = String(value);
                        var cleanedValue = value.replace(/<[^>]+>/g, '').replace(/[^\d.-]/g, '');
                        return parseFloat(cleanedValue) || 0;
                    }
                    var api = this.api();
                    var totalPurchaseQty = api.column(3, { page: 'current' }).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(String(b).replace(/,/g, ''));
                    }, 0);
                    var totalSoldQty = api.column(8, { page: 'current' }).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(String(b).replace(/,/g, ''));
                    }, 0);
                    var totalPurchased = api.column(4, { page: 'current' }).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(String(b).replace(/,/g, ''));
                    }, 0);
                    var totalTransferRequest = api.column(5, { page: 'current' }).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(String(b).replace(/,/g, ''));
                    }, 0);
                    var totalSold = api.column(6, { page: 'current' }).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(String(b).replace(/,/g, ''));
                    }, 0);
                    var totalProfit = api.column(7, { page: 'current' }).data().reduce(function (a, b) {
                        var cleanValue = b.replace(/<[^>]*>/g, '').trim();

                        // Extract numeric part and convert to float
                        var numericValue = parseFloat(cleanValue.replace(/[^\d.-]/g, '') || 0);
                        return parseFloat(a) + numericValue;
                    }, 0);
                    var totalStockQty = api.column(10, { page: 'current' }).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(String(b).replace(/,/g, ''));
                    }, 0);
                    var totalStockAmount = api.column(9, { page: 'current' }).data().reduce(function (a, b) {
                        // Remove HTML tags and extra spaces
                        var cleanValue = b.replace(/<[^>]*>/g, '').trim();
                        console.log("Clean Value:", cleanValue);

                        // Remove any leading non-numeric characters (e.g., currency symbol, spaces, etc.)
                        var numericString = cleanValue.replace(/^[^\d-]*([\d.-]+)/, '$1');

                        // Convert to float
                        var numericValue = parseFloat(numericString) || 0;

                        console.log("Numeric Value:", numericValue);

                        return parseFloat(a) + numericValue;
                    }, 0);

                    var totalCost = api.column(7, { page: 'current' }).data().reduce(function (a, b) {
                        var cleanValue = b.replace(/<[^>]*>/g, '').trim();
                        console.log("Column 7 raw value:", b, "cleaned:", cleanValue);
                        var numericValue = parseFloat(cleanValue.replace(/Rs\.?\s?/, '').replace(/,/g, '')) || 0;
                        console.log("Column 7 numeric:", numericValue);
                        return parseFloat(a) + numericValue;
                    }, 0);
                    var totalSoldPrice = api.column(9, { page: 'current' }).data().reduce(function (a, b) {
                        var cleanValue = b.replace(/<[^>]*>/g, '').trim();
                        console.log("Column 9 raw value:", b, "cleaned:", cleanValue);
                        var numericValue = parseFloat(cleanValue.replace(/Rs\.?\s?/, '').replace(/,/g, '')) || 0;
                        console.log("Column 9 numeric:", numericValue);
                        return parseFloat(a) + numericValue;
                    }, 0);
                    var totalStockCost = api.column(11, { page: 'current' }).data().reduce(function (a, b) {
                        var cleanValue = b.replace(/<[^>]*>/g, '').trim();
                        console.log("Column 11 raw value:", b, "cleaned:", cleanValue);
                        var numericValue = parseFloat(cleanValue.replace(/Rs\.?\s?/, '').replace(/,/g, '')) || 0;
                        console.log("Column 11 numeric:", numericValue);
                        return parseFloat(a) + numericValue;
                    }, 0);
                    var profitLoss = api.column(12, { page: 'current' }).data().reduce(function (a, b) {
                        var cleanValue = b.replace(/<[^>]*>/g, '').trim();
                        console.log("Column 12 raw value:", b, "cleaned:", cleanValue);
                        var numericValue = parseFloat(cleanValue.replace(/Rs\.?\s?/, '').replace(/,/g, '')) || 0;
                        console.log("Column 12 numeric:", numericValue);
                        return parseFloat(a) + numericValue;
                    }, 0);

                    console.log("Final totals - Cost:", totalCost, "Sold:", totalSoldPrice, "Stock:", totalStockCost, "Profit:", profitLoss);

                    $(api.column(3).footer()).html(formatQuantity(totalPurchaseQty.toFixed(2)));
                    $(api.column(8).footer()).html(formatQuantity(totalSoldQty.toFixed(2)));
                    $(api.column(4).footer()).html(formatQuantity(totalPurchased.toFixed(2)));
                    $(api.column(5).footer()).html(formatQuantity(totalTransferRequest.toFixed(2)));
                    $(api.column(6).footer()).html(formatQuantity(totalSold.toFixed(2)));
                    $(api.column(7).footer()).html(formatMoney(totalCost));
                    $(api.column(10).footer()).html(formatQuantity(totalStockQty.toFixed(2))); 
                    $(api.column(9).footer()).html(formatMoney(totalSoldPrice));
                    $(api.column(11).footer()).html(formatMoney(totalStockCost));
                    $(api.column(12).footer()).html(formatMoney(profitLoss));
                }
            });			 
            }
    });
    function formatTableBody() {
        $('.reports-table tbody td.money').each(function () {
            let val = $(this).data('value');
            $(this).html(formatMoney(val));
        });

        $('.reports-table tbody td.qty').each(function () {
            let val = $(this).data('value');
            $(this).html(formatQuantity(val));
        });
    }

    }
</script>


<script>
let currentPage = 1;
let totalPages = 1;
let totalRows = 0;

// Function to change the number of rows displayed
// function changeRowsPerPage() {
//     const perPage = document.getElementById("rowsPerPage").value;
//     const rows = document.querySelectorAll(".reports-table tbody tr");
//     totalRows = rows.length; // Update total rows

//     // Hide all rows initially
//     rows.forEach(row => row.style.display = "none");

//     // Determine how many rows to show
//     let numRowsToShow = perPage === "all" ? totalRows : parseInt(perPage);
//     totalPages = Math.ceil(totalRows / numRowsToShow);

//     // Show the specified number of rows for the current page
//     const start = (currentPage - 1) * numRowsToShow;
//     for (let i = start; i < start + numRowsToShow && i < totalRows; i++) {
//         rows[i].style.display = "";
//     }

//     // Update entry count display
//     updateEntryCount(start, numRowsToShow, perPage);
    
//     // Update pagination only if not showing all rows
//     updatePagination(perPage !== "all");
// }

// // Function to update entry count display
// function updateEntryCount(start, numRowsToShow, perPage) {
//     const end = Math.min(start + numRowsToShow, totalRows);
//     const entryCount = document.getElementById("entryCount");

//     if (totalRows > 0) {
//         if (perPage === "all") {
//             entryCount.innerText = `Showing 1 to ${totalRows} of ${totalRows} entries`;
//         } else {
//             entryCount.innerText = `Showing ${start + 1} to ${end} of ${totalRows} entries`;
//         }
//         entryCount.style.display = "block"; // Show entry count
//     } else {
//         entryCount.style.display = "none"; // Hide entry count when no rows
//     }
// }

// // Function to handle search input
// function handleSearch() {
//     const searchTerm = document.getElementById("searchBox").value.toLowerCase();
//     const rows = document.querySelectorAll(".reports-table tbody tr");
//     let visibleRows = 0;

//     // Remove existing message row if present
//     const existingMessageRow = document.querySelector(".reports-table tbody tr.message-row");
//     if (existingMessageRow) {
//         existingMessageRow.remove();
//     }

//     rows.forEach(row => {
//         const rowText = row.innerText.toLowerCase();
//         if (rowText.includes(searchTerm)) {
//             row.style.display = ""; // Show the row if it matches the search term
//             visibleRows++;
//         } else {
//             row.style.display = "none"; // Hide the row if it doesn't match
//         }
//     });

//     // Update totalRows based on search
//     totalRows = visibleRows;

//     // If no rows match the search term and the search box is not empty
//     if (searchTerm !== "" && visibleRows === 0) {
//         const messageRow = document.createElement("tr");
//         messageRow.classList.add("message-row"); // Add class for easy identification
//         const messageCell = document.createElement("td");
//         messageCell.colSpan = 12; // Adjust to the number of columns in your table
//         messageCell.innerText = "Result not found";
//         messageCell.style.textAlign = "center"; // Center the message
//         messageRow.appendChild(messageCell);
//         document.querySelector(".reports-table tbody").appendChild(messageRow);
//     }

//     // If the search box is empty
//     if (searchTerm === "") {
//         currentPage = 1; // Reset to the first page
//         changeRowsPerPage(); // Show rows based on the current dropdown selection
//     } else {
//         // Update pagination based on visible rows
//         const perPage = parseInt(document.getElementById("rowsPerPage").value);
//         totalPages = Math.ceil(visibleRows / perPage);
//         updatePagination(perPage !== "all");
//     }
// }

// // Function to update pagination controls
// function updatePagination(show) {
//     const pagination = document.getElementById("pagination");
//     pagination.innerHTML = ""; // Clear existing pagination

//     if (!show) {
//         pagination.style.display = "none"; // Hide pagination if showing all records
//         return;
//     } else {
//         pagination.style.display = "block"; // Show pagination
//     }

//     // Previous button
//     const prevButton = document.createElement("button");
//     prevButton.innerText = "Previous";
//     prevButton.disabled = currentPage === 1; // Disable if on the first page
//     prevButton.className = "custom-css";
//     prevButton.onclick = () => {
//         if (currentPage > 1) {
//             currentPage--;
//             changeRowsPerPage();
//         }
//     };
//     pagination.appendChild(prevButton);

//     // Calculate page numbers to show
//     const startPage = Math.max(1, currentPage - 2); // Start page number
//     const endPage = Math.min(totalPages, startPage + 4); // End page number

//     // Page number buttons
//     for (let i = startPage; i <= endPage; i++) {
//         const pageButton = document.createElement("button");
//         pageButton.innerText = i;
//         pageButton.disabled = i === currentPage; // Disable current page button
//         pageButton.onclick = () => {
//             currentPage = i;
//             changeRowsPerPage();
//         };
//         pagination.appendChild(pageButton);
//     }

//     // Next button
//     const nextButton = document.createElement("button");
//     nextButton.innerText = "Next";
//     nextButton.disabled = currentPage === totalPages; // Disable if on the last page
//     nextButton.onclick = () => {
//         if (currentPage < totalPages) {
//             currentPage++;
//             changeRowsPerPage();
//         }
//     };
//     pagination.appendChild(nextButton);
// }

// // Function to initialize the table on page load
// function initializeTable() {
//     const rows = document.querySelectorAll(".reports-table tbody tr");
//     totalRows = rows.length; // Get the total rows

//     // Hide the entry count initially
//     document.getElementById("entryCount").style.display = "none";

//     changeRowsPerPage(); // Show rows based on dropdown selection
// }

// Call the initialization function on page load
// window.onload = initializeTable;
</script>

<style>
.pagination {
    margin-top: 10px;
}

.pagination button {
    /* margin: 0 5px; */
    padding: 5px 10px;
    cursor: pointer;
    border: none;
    color:#0088cc;
}


button[disabled], html input[disabled].active {
    background: #0088cc;
    color: #fff;
}
.d-flx {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.brd-setting {
    border: 1px solid #ddd;
    padding:0.7rem;
    background-color: #FFFFFF;
    background-image: none;
    border: 1px solid #CCCCCC;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;
    color: #555555;
    font-size: 14px;
    padding: 6px 12px;
    transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;
    vertical-align: middle;
}

.entry-count {
    margin-top: 10px;
}

#entryCount {
    display: none; /* Hide entry count initially */
}
button.custom-css {
    background: #f0f0f0;
    color:#0088cc;
}

span#arrow-0 {
    cursor: pointer;
}

.header-content {
    display: flex;            
    align-items: center;      
    justify-content:center;
}

.sort-arrow {
    display: flex;            
    flex-direction: column;   
    margin-left: 5px;         
    cursor: pointer;          
}

.sort-arrow i {
    font-size: 0.88em;
    line-height: 0.2;
}


</style>

<!-- <script>
    let currentSortColumn = -1; // No column sorted initially
    let currentSortDirection = 'asc'; // Default sort direction

    function toggleSort(columnIndex) {
        const table = document.querySelector('.reports-table tbody');
        const rows = Array.from(table.querySelectorAll('tr')); // Get all rows
        const isAscending = columnIndex === currentSortColumn && currentSortDirection === 'asc';

        // Toggle sort direction
        currentSortDirection = isAscending ? 'desc' : 'asc';
        currentSortColumn = columnIndex;

        // Sort rows based on the current column
        rows.sort((a, b) => {
            const aText = a.children[columnIndex].innerText;
            const bText = b.children[columnIndex].innerText;

            const aValue = isNaN(aText) ? aText : parseFloat(aText);
            const bValue = isNaN(bText) ? bText : parseFloat(bText);

            return isAscending 
                ? (aValue > bValue ? 1 : -1) 
                : (aValue < bValue ? 1 : -1);
        });

        // Remove existing rows
        table.innerHTML = '';

        // Append sorted rows back to the table
        rows.forEach(row => table.appendChild(row));

        // Update sort arrow display
        updateSortArrows();
    }

    function updateSortArrows() {
        // Remove existing classes from arrows
        document.querySelectorAll('.sort-arrow').forEach(arrow => {
            arrow.classList.remove('asc', 'desc');
        });

        // Set class for the currently sorted column
        if (currentSortColumn >= 0) {
            const activeArrow = document.getElementById(`arrow-${currentSortColumn}`);
            activeArrow.classList.add(currentSortDirection);
        }
    }
</script> -->
