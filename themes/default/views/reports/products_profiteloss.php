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
if($this->input->post('style_code'))
{
    $v .= "&style_code=" . $this->input->post('style_code');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
?>
<script>
$(document).ready(function () {
    const oldAlert = window.alert;
        window.alert = function(msg) {
            if (msg && msg.indexOf('html5_data') !== -1) {
                console.warn('dtFilter skipped popup:', msg);
                return;
            }
            oldAlert(msg);
    };
    // Utility for display (already in your code)
    function spb(x) {
        v = x.split('__');
        return '(' + formatQuantity2(v[0]) + ') <strong>' + formatMoney(v[1]) + '</strong>';
    }

    // Detect batch visibility from PHP setting
    var show_batch = <?= ($Settings->product_batch_setting == 1 || $Settings->product_batch_setting == 2) ? 'true' : 'false' ?>;

    // === Define Columns Dynamically ===
    var columns = [
        {"mRender": fsd},  // 0 Date
        null,              // 1 Barcode
        null,              // 2 Product Name
        null,              // 3 Category
        null,              // 4 Subcategory
        null,              // 5 Brand
        null,              // 6 Style Code
        null               // 7 Variant
    ];

    if (show_batch) {
        columns.push(null); // 8 Batch
    }

    // Calculate starting index for numeric columns
    var start = show_batch ? 9 : 8;

    // Insert in exact order server sends
    columns[start]     = {"mRender": formatQuantity};     // Quantity
    columns[start + 1] = {"mRender": currencyFormat};     // Cost
    columns[start + 2] = {"mRender": currencyFormat};     // Selling Price
    columns[start + 3] = {"mRender": currencyFormat};     // Discount
    columns[start + 4] = {"mRender": currencyFormat};     // GST Amount
    columns[start + 5] = null;                            // GST %
    columns[start + 6] = {"mRender": currencyFormat};     // Gross Profit
    columns[start + 7] = {"mRender": currencyFormat};     // Net Profit

    // === Initialize DataTable ===
    var oTable = $('#PrRData').dataTable({
        "aaSorting": [[0, "desc"]],
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "<?= site_url('reports/getProductsReport_Profitloss/?v=1' . $v) ?>",
        "fnServerData": function (sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?= $this->security->get_csrf_token_name() ?>",
                "value": "<?= $this->security->get_csrf_hash() ?>"
            });
            $.ajax({
                dataType: "json",
                type: "POST",
                url: sSource,
                data: aoData,
                success: fnCallback
            });
        },
        "aoColumns": columns,

        // === Footer Totals Calculation ===
        "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
            var purcost = 0, sprice = 0, gamt = 0, grpft = 0, ntpft = 0, sdis = 0, totalQty = 0;

            // Column index shift if batch column is hidden
            var colOffset = show_batch ? 0 : -1;

            for (var i = 0; i < aaData.length; i++) {
                if (aaData[aiDisplay[i]][9 + colOffset] != null && aaData[aiDisplay[i]][9 + colOffset] !== '') {
                    totalQty += parseFloat(aaData[aiDisplay[i]][9 + colOffset]);
                }
                if (aaData[aiDisplay[i]][10 + colOffset] != null && aaData[aiDisplay[i]][10 + colOffset] !== '') {
                    purcost += parseFloat(aaData[aiDisplay[i]][10 + colOffset]);
                }
                if (aaData[aiDisplay[i]][11 + colOffset] != null && aaData[aiDisplay[i]][11 + colOffset] !== '') {
                    sprice += parseFloat(aaData[aiDisplay[i]][11 + colOffset]);
                }
                if (aaData[aiDisplay[i]][12 + colOffset] != null && aaData[aiDisplay[i]][12 + colOffset] !== '') {
                    sdis += parseFloat(aaData[aiDisplay[i]][12 + colOffset]);
                }
                if (aaData[aiDisplay[i]][13 + colOffset] != null && aaData[aiDisplay[i]][13 + colOffset] !== '') {
                    gamt += parseFloat(aaData[aiDisplay[i]][13 + colOffset]);
                }
                if (aaData[aiDisplay[i]][15 + colOffset] != null && aaData[aiDisplay[i]][15 + colOffset] !== '') {
                    grpft += parseFloat(aaData[aiDisplay[i]][15 + colOffset]);
                }
                if (aaData[aiDisplay[i]][16 + colOffset] != null && aaData[aiDisplay[i]][16 + colOffset] !== '') {
                    ntpft += parseFloat(aaData[aiDisplay[i]][16 + colOffset]);
                }
            }

            var nCells = nRow.getElementsByTagName('th');
            nCells[9 + colOffset].innerHTML = '<div class="text-center">' + formatQuantity(totalQty) + '</div>';
            nCells[10 + colOffset].innerHTML = '<div class="text-right">' + formatMoney(purcost) + '</div>';
            nCells[11 + colOffset].innerHTML = '<div class="text-right">' + formatMoney(sprice) + '</div>';
            nCells[12 + colOffset].innerHTML = '<div class="text-right">' + formatMoney(sdis) + '</div>';
            nCells[13 + colOffset].innerHTML = '<div class="text-right">' + formatMoney(gamt) + '</div>';
            nCells[15 + colOffset].innerHTML = '<div class="text-right">' + formatMoney(grpft) + '</div>';
            nCells[16 + colOffset].innerHTML = '<div class="text-right">' + formatMoney(ntpft) + '</div>';
        }
    }).fnSetFilteringDelay().dtFilter([
        {column_number: 0, filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text"},
        {column_number: 1, filter_default_label: "[<?= lang('product_code'); ?>]", filter_type: "text"},
        {column_number: 2, filter_default_label: "[<?= lang('product_name'); ?>]", filter_type: "text"}
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
   #PrRData td:nth-child(11), 
   #PrRData td:nth-child(12), 
   #PrRData td:nth-child(13), 
   #PrRData td:nth-child(14), 
   #PrRData td:nth-child(15), 
   #PrRData td:nth-child(16), 
   #PrRData td:nth-child(17) {
    text-align: right;
    width: 12%;
}
#PrRData td:nth-child(3), #PrRData td:nth-child(4), #PrRData td:nth-child(5), #PrRData td:nth-child(6), #PrRData td:nth-child(7), #PrRData td:nth-child(8), #PrRData td:nth-child(9) {
    text-align: left!important;
    width: 12%;
}
#PrRData td:nth-child(10) {
    text-align: center!important;
    width: 12%;
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('Products_Profit_And_Loss_Report'); ?> <?php
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

                    <?php echo form_open("reports/products_profitloss","id='searchproduct'"); ?>
                    <div class="row">
                        <!--<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product"
                                       value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>"
                                       id="report_product_id"/>
                            </div>
                        </div>-->
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

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("Style Code", "Style Code") ?>
                                <?php
                                
                                echo form_input('style_code',(isset($_POST['style_code']) ? $_POST['style_code'] : ''), 'class="form-control " id="style_code" placeholder="' .  lang("Style_Code") . '" style="width:100%"')
                                ?>
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
                            <a href="<?= base_url('reports/products_profitloss'); ?>" type="reset" id="report_reset" data-value="<?= base_url('reports/products_profitloss'); ?>"
                                   name="submit_report" value="Reset" class="btn btn-warning input-xs"> Reset</a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="PrRData"
                           class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                           style="margin-bottom:5px;">
                        <thead>
                        <tr class="active">
                            <th><?= lang("Date"); ?></th>
                            <th style="padding: 17px 0px 0px 0px;"><?= lang("product_code"); ?></th>
                            <th style="padding: 17px 0px 0px 0px;"><?= lang("product_name"); ?></th>
                            <th><?= lang("Category"); ?></th>
                            <th style="padding: 17px 0px 0px 0px;"><?= lang("Sub_Category"); ?></th>
                            <th><?= lang("brand"); ?></th>
                            <th style="padding: 17px 0px 0px 0px;"><?= lang("Style_Code"); ?></th>
                            <th><?= lang("Variant"); ?></th>
                            <?php if ($Settings->product_batch_setting == 1 || $Settings->product_batch_setting == 2): ?>
                                <th><?= lang("batch_no"); ?></th>
                            <?php endif; ?>
                            <th><?= lang("Quantity"); ?></th>
							<th><?= lang("Cost"); ?></th>
                            <th style="padding: 4px 0px 0px 0px;"><?= lang("Selling_Price"); ?></th>
                            <th><?= lang("Discount"); ?></th>
						    <th style="padding: 5px 0px 0px 0px;"><?= lang("GST_AMT"); ?></th>
                            <th><?= lang("GST %"); ?></th>
                            <th><?= lang("Gross_Profit"); ?></th>
                            <th><?= lang("Net_Profit"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="17" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                            <?php if ($Settings->product_batch_setting == 1 || $Settings->product_batch_setting == 2): ?>
                                <th></th>
                            <?php endif; ?>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?= lang("Discount"); ?></th>
                            <th></th>
                            <th></th>
							<th></th>
							<th></th>
                          
                            
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
            window.location.href = "<?=site_url('reports/getProductsReport_Profitloss/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getProductsReport_Profitloss/0/xls/?v=1' . $v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
			window.location.href = "<?=site_url('reports/getProductsReport_Profitloss/0/0/img/?v=1' . $v)?>";
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
  