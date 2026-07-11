<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = '';

if ($this->input->post('startDate_term1')) {
    $startDateterm1 = explode('/', substr($this->input->post('startDate_term1') , 0, 10));
    $startDate_term1 = $startDateterm1[2] . "-" . $startDateterm1[1] . "-" . $startDateterm1[0] . "  00:00";
    $v .= "&startDate_term1=" . $startDate_term1;
}
if ($this->input->post('endDate_term1')) {
    $endDateterm1 = explode('/', substr($this->input->post('endDate_term1') , 0, 10));
    $endDate_term1 = $endDateterm1[2] . "-" . $endDateterm1[1] . "-" . $endDateterm1[0]. "  23:59";
    $v .= "&endDate_term1=" . $endDate_term1;
}
if ($this->input->post('startDate_term2')) {
    $startDateterm2 = explode('/', substr($this->input->post('startDate_term2') , 0, 10));
    $startDate_term2 = $startDateterm2[2] . "-" . $startDateterm2[1] . "-" . $startDateterm2[0] . "  00:00";
    $v .= "&startDate_term2=" . $startDate_term2;
}
if ($this->input->post('endDate_term2')) {
    $endDateterm2 = explode('/', substr($this->input->post('endDate_term2') , 0, 10));
    $endDate_term2 = $endDateterm2[2] . "-" . $endDateterm2[1] . "-" . $endDateterm2[0]. "  23:59";
    $v .= "&endDate_term2=" . $endDate_term2;
}
if ($this->input->post('warehouse')) {
    $warehouseData = $this->input->post('warehouse'); 
    foreach ($warehouseData as $key => $value) {
        $v .= "&warehouse[]=" . urlencode($value); 
    }
}
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
if($this->input->post('view_sale_count_warehouse_wise'))
{
    $v .= "&view_sale_count_warehouse_wise=1";
}
?>

<style>
.table th,
.table td {
    text-align: center;
    width: 15%;
}

.custom-set {
    display: inline-flex !important;
    position: absolute;
    width: 25%;
    right: 1.3em;
    top: 4rem;
    z-index: 1111;
}

.d-flx {
    display: flex;
}

.font-weight-set {
    font-weight: 200;
}
.text-left {
    text-align: left !important;
}

.text-center {
    text-align: center !important;
}

</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<script>
    // === Validate mandatory Term 1 and Term 2 dates ===
    $(document).ready(function() {
    $('#searchproduct').on('submit', function(e) {
        var start1 = $('#start_date').val().trim();
        var end1 = $('#end_date').val().trim();
        var start2 = $('#start_date1').val().trim();
        var end2 = $('#end_date1').val().trim();

        // Allow submit if either Term 1 OR Term 2 has both dates filled
        var term1Valid = start1 && end1;
        var term2Valid = start2 && end2;

        if (!term1Valid && !term2Valid) {
            e.preventDefault();
            bootbox.alert("Please select Term 1 or Term 2 date range before submitting.");
            return false;
        }
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
// $(document).ready(function() {

//     var oTable = $('#PrData').dataTable({
//         "aaSorting": [],
//         "aLengthMenu": [
//             [10, 25, 50, 100, -1],
//             [10, 25, 50, 100, "<?= lang('all') ?>"]
//         ],
//         "iDisplayLength": <?= $Settings->rows_per_page ?>,
//         'bProcessing': true,
//         'bServerSide': true,
//         'sAjaxSource': '<?= site_url('reports/termWiseSaleReports/?v=1' . $v) ?>',
//         'fnServerData': function(sSource, aoData, fnCallback) {
//             // console.log('URL:', '<?= site_url('reports/termWiseSaleReports  /?v=1' . $v) ?>');
//             aoData.push({
//                 "name": "<?= $this->security->get_csrf_token_name() ?>",
//                 "value": "<?= $this->security->get_csrf_hash() ?>",
//             });
//             $.ajax({
//                 'dataType': 'json',
//                 'type': 'POST',
//                 'url': sSource,
//                 'data': aoData,
//                 'success': fnCallback,
//             });
//         },
//         "searching": true,
//         "aoColumns": [
//             { "bSortable": false },
//             { "bSortable": false },
//             { "bSortable": false },
//             { "bSortable": false },
//             { "bSortable": false },
//             { "bSortable": false, "mRender": parseFloat },
//             { "bSortable": false, "mRender": parseFloat },
//             { "bSortable": false, "mRender": parseFloat },
//             { "bSortable": false, "mRender": parseFloat },
//             { "bSortable": false, "mRender": parseFloat }
//         ],
//         'fnRowCallback': function(nRow, aData, iDisplayIndex) {
//             nRow.id = aData[0];
//             var nCells = nRow.getElementsByTagName('td');
//             nCells['0'].innerHTML = aData[0];
//         },
//         "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
//             var Stock = 0,
//                 t1Sale = 0,
//                 t2Sale = 0,
//                 excessInv = 0,
//                 shortageInv = 0;

//             for (var i = 0; i < aaData.length; i++) {
//                 Stock += parseFloat(aaData[aiDisplay[i]][5]);
//                 t1Sale += parseFloat(aaData[aiDisplay[i]][6]);
//                 t2Sale += parseFloat(aaData[aiDisplay[i]][7]);
//                 excessInv += parseFloat(aaData[aiDisplay[i]][8]);
//                 shortageInv += parseFloat(aaData[aiDisplay[i]][9]);
//             }

//             var nCells = nRow.getElementsByTagName('th');
//             nCells[5].innerHTML = (parseFloat(Stock));
//             nCells[6].innerHTML = (parseFloat(t1Sale));
//             nCells[7].innerHTML = (parseFloat(t2Sale));
//             nCells[8].innerHTML = (parseFloat(excessInv));
//             nCells[9].innerHTML = (parseFloat(shortageInv));
//         }
//     }).fnSetFilteringDelay().dtFilter([{
//             column_number: 0,
//             filter_default_label: "<?= lang('product_code'); ?>",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 1,
//             filter_default_label: "[<?= lang('category_name'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 2,
//             filter_default_label: "[<?= lang('product_name'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 3,
//             filter_default_label: "[<?= lang('variant_name'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 4,
//             filter_default_label: "[<?= lang('Warehouse'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 5,
//             filter_default_label: "[<?= lang('current_stock'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 6,
//             filter_default_label: "[<?= lang('term_1_sale'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 7,
//             filter_default_label: "[<?= lang('term_2_sale'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 8,
//             filter_default_label: "[<?= lang('excess_inventory'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//         {
//             column_number: 9,
//             filter_default_label: "[<?= lang('shortage_inventory'); ?>]",
//             filter_type: "text",
//             data: []
//         },
//     ], "footer");
// });
$(document).ready(function () {
    $.ajax({
        type: "get",
        url: "reports/termWiseSaleReports",
        data: "<?= 'v=1' . $v ?>",
        beforeSend: function () {
            $("#PrData").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i> Loading data from server...</div>");
        },
        success: function (data) {
            // Inject the new table HTML
            $("#PrData").html(data);

            // Wait for DOM update
            setTimeout(function () {
                const $table = $('#PrData1');

                if (!$table.length) {
                    console.error('Table not found after AJAX load!');
                    return;
                }

                // Destroy any previous DataTable
                if ($.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().clear().destroy();
                }

                // --- Determine if batch and warehouse columns exist ---
                let hasBatchColumn = false;
                let hasWarehouseColumn = false;
                $table.find('thead th').each(function () {
                    const text = $(this).text().trim().toLowerCase();
                    if (text.includes('batch')) {
                        hasBatchColumn = true;
                    }
                    if (text.includes('warehouse')) {
                        hasWarehouseColumn = true;
                    }
                });

                // --- Compute column index offsets ---
                let stockCol = 4; // Code, Cat, Name, Variant (4 cols)
                if (hasWarehouseColumn) stockCol++;
                if (hasBatchColumn) stockCol++;
                
                const t1Col = stockCol + 1;
                const t2Col = stockCol + 2;

                let alignLeftCols = [];
                for(let i = 0; i < stockCol; i++) {
                    alignLeftCols.push(i);
                }

                // Initialize DataTable
                $table.DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    columnDefs: [
                        { targets: alignLeftCols, className: "text-left" },
                        { targets: [stockCol, t1Col, t2Col], className: "text-center" }
                    ],
                    footerCallback: function (row, data, start, end, display) {
                        const api = this.api();

                        function sumColumn(index) {
                            return api.column(index, { page: 'current' }).data().reduce(function (a, b) {
                                const clean = b.replace(/<[^>]*>/g, '').trim();
                                const val = parseFloat(clean.replace(/[^\d.-]/g, '')) || 0;
                                return (a || 0) + val;
                            }, 0);
                        }

                        [stockCol, t1Col, t2Col].forEach(function (i) {
                            $(api.column(i).footer()).html('<div class="text-center">' + formatQuantity(sumColumn(i).toFixed(2)) + '</div>');
                        });
                    }
                });
            }, 200);
        },
        error: function () {
            alert("Error while loading data");
        }
    });

});

</script>
<script type="text/javascript">
$(document).ready(function() {
    if ($("#start_date").val() || $("#start_date1").val())
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
</script>
<style>
#form {
    display: none;
}
</style>
<!-- <div class="form-group">
    <label for="searchInput">Search:</label>
    <input type="text" id="searchInput" class="form-control" placeholder="Search for products...">
</div> -->
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('Term Wise Sales Report'); ?> <?php
            
            if($this->input->post('startDate_term1'))
            {
                echo "From " . $this->input->post('startDate_term1') . " to " . $this->input->post('endDate_term1');
            }
            if($this->input->post('startDate_term2'))
            {
                echo "And From " . $this->input->post('startDate_term2') . " to " . $this->input->post('endDate_term2');
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

                <?php echo form_open("reports/term_wise_sale_report", "id='searchproduct'"); ?>

            <div class="row">
                <!-- Term 1 Date -->
                <div class="col-sm-4">
                    <div class="form-group choose-date hidden-xs">
                        <div class="controls">
                            <?= lang("Start - End Date Term 1", "Start - End Date Term 1"); ?>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text"
                                    value="<?php echo isset($_POST['startDate_term1']) ? $_POST['startDate_term1'].' - '.$_POST['endDate_term1'] : '-'; ?>"
                                    id="daterange_new" class="form-control" autocomplete="off">
                                <input type="hidden" name="startDate_term1" id="start_date"
                                    value="<?php echo isset($_POST['startDate_term1']) ? $_POST['startDate_term1'] : ''; ?>">
                                <input type="hidden" name="endDate_term1" id="end_date"
                                    value="<?php echo isset($_POST['endDate_term1']) ? $_POST['endDate_term1'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Term 2 Date -->
                <div class="col-sm-4">
                    <div class="form-group choose-date hidden-xs">
                        <div class="controls">
                            <?= lang("Start - End Date Term 2", "Start - End Date Term 2"); ?>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text"
                                    value="<?php echo isset($_POST['startDate_term2']) ? $_POST['startDate_term2'].' - '.$_POST['endDate_term2'] : '-'; ?>"
                                    id="daterange_new1" class="form-control" autocomplete="off">
                                <input type="hidden" name="startDate_term2" id="start_date1"
                                    value="<?php echo isset($_POST['startDate_term2']) ? $_POST['startDate_term2'] : ''; ?>">
                                <input type="hidden" name="endDate_term2" id="end_date1"
                                    value="<?php echo isset($_POST['endDate_term2']) ? $_POST['endDate_term2'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warehouse -->
                <div class="col-sm-4">
                    <div class="form-group">
                        <?= lang("warehouse", "warehouse"); ?>
                        <?php
                        $wh = ['all' => 'Select All'];
                        foreach ($warehouses as $warehouse) {
                            $wh[$warehouse->id] = $warehouse->name;
                        }

                        echo form_dropdown(
                            'warehouse[]',
                            $wh,
                            (isset($_POST['warehouse']) ? $_POST['warehouse'] : ['all']),
                            'id="warehouse" class="form-control" multiple="multiple" style="width:100%;"'
                        );
                        ?>
                    </div>
                </div>
            </div>

            <!-- Row 2: Product / Category / Subcategory -->
            <div class="row">
                <!-- Product -->
                <div class="col-sm-4">
                    <div class="form-group">
                        <?= lang("product", "suggest_product"); ?>
                        <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                        <input type="hidden" name="product"
                                value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>"
                                id="report_product_id"/>
                    </div>
                </div>

                <!-- Category -->
                <div class="col-sm-4">
                    <div class="form-group">
                        <?= lang("category", "category"); ?>
                        <?php
                        $cat[''] = lang('select') . ' ' . lang('category');
                        foreach ($categories as $category) {
                            $cat[$category->id] = $category->name;
                        }
                        echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" style="width:100%;"');
                        ?>
                    </div>
                </div>

                <!-- Subcategory -->
                <div class="col-sm-4">
                    <div class="form-group">
                        <?= lang("subcategory", "subcategory"); ?>
                        <div class="controls" id="subcat_data">
                            <?php
                            echo form_input('subcategory', (isset($_POST['subcategory']) ? $_POST['subcategory'] : ''), 'class="form-control" id="subcategory" placeholder="'.lang('select_category_to_load').'"');
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Brand -->
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <?= lang("brand", "brand"); ?>
                        <?php
                        $bt[''] = lang('select') . ' ' . lang('brand');
                        foreach ($brands as $brand) {
                            $bt[$brand->id] = $brand->name;
                        }
                        echo form_dropdown('brand', $bt, (isset($_POST['brand']) ? $_POST['brand'] : ''), 'class="form-control select" id="brand" style="width:100%;"');
                        ?>
                    </div>
                </div>
                
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="control-label" for="view_sale_count_warehouse_wise">View sale count warehouse wise</label>
                        <div class="controls">
                            <input type="checkbox" name="view_sale_count_warehouse_wise" value="1" id="view_sale_count_warehouse_wise" <?php echo isset($_POST['view_sale_count_warehouse_wise']) ? 'checked' : ''; ?>>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="form-group">
                <div class="controls">
                    <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                    <a href="<?= site_url('reports/term_wise_sale_report') ?>" type="reset" id="report_reset" class="btn btn-warning">Reset</a>
                </div>
            </div>

            <?php echo form_close(); ?>


                </div>

                <div class="clearfix"></div>

                
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive" id="PrData">
                        <?=lang('loading_data_from_server')?> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// document.getElementById('searchInput').addEventListener('keyup', function() {
//     var input = this.value.toLowerCase();
//     var rows = document.querySelectorAll('#PrData tbody tr');
//     rows.forEach(function(row) {
//         var cells = row.getElementsByTagName('td');
//         var found = Array.from(cells).some(function(cell) {
//             return cell.textContent.toLowerCase().includes(input);
//         });
//         row.style.display = found ? '' : 'none';
//     });
// });
</script>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#daterange_new1').daterangepicker({
            timePicker: false,
            format: (site.dateFormats.js_sdate).toUpperCase(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Last 7 Days': [moment().subtract('days', 6), moment()],
                'Last 30 Days': [moment().subtract('days', 29), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1)
                    .endOf('month')
                ]
            }
        },
        function(start, end) {
            $('#start_date1').val(start.format('DD/MM/YYYY ')); //HH:mm
            $('#end_date1').val(end.format('DD/MM/YYYY ')); //HH:mm
        });
        $('#daterange_new').daterangepicker({
            timePicker: false,
            format: (site.dateFormats.js_sdate).toUpperCase(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Last 7 Days': [moment().subtract('days', 6), moment()],
                'Last 30 Days': [moment().subtract('days', 29), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1)
                    .endOf('month')
                ]
            }
        },
        function(start, end) {
            $('#start_date').val(start.format('DD/MM/YYYY ')); //HH:mm
            $('#end_date').val(end.format('DD/MM/YYYY ')); //HH:mm
        });
        
    $('#pdf').click(function(event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/termWiseSaleReports/pdf/?v=1' . $v)?>";
        return false;
    });
    $('#xls').click(function(event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/termWiseSaleReports/0/xls/?v=1' . $v)?>";
        return false;
    });
    $('#image').click(function(event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/termWiseSaleReports/0/0/img/?v=1' . $v)?>";
        /*html2canvas($('.box'), {
            onrendered: function (canvas) {
                var img = canvas.toDataURL()
                window.open(img);
            }
        });*/
        return false;
    });
    $('#warehouse').select2({
        placeholder: "Select Warehouse",
        allowClear: true,
        closeOnSelect: false
    });

    $('#warehouse').on('select2:select', function (e) {
        if (e.params.data.id === 'all') {
            $(this).val(['all']).trigger('change');
        } else {
            let selected = $(this).val();
            if (selected && selected.includes('all')) {
                selected = selected.filter(v => v !== 'all');
                $(this).val(selected).trigger('change');
            }
        }
    });

    // Handle "Select All" if it's the only one left after unselect
    $('#warehouse').on('select2:unselect', function (e) {
    });
});
</script>