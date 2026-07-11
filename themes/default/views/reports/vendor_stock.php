<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('warehouse')) { $v .= "&warehouse=" . $this->input->post('warehouse'); }
if ($this->input->post('supplier'))  { $v .= "&supplier=" . $this->input->post('supplier'); }
if ($this->input->post('product'))   { $v .= "&product=" . $this->input->post('product'); }
?>
<style>
    #vendorsStockData td:nth-child(5),
    #vendorsStockData th:nth-child(5),
    #vendorsStockData td:nth-child(6),
    #vendorsStockData th:nth-child(6),
    #vendorsStockData td:nth-child(7),
    #vendorsStockData th:nth-child(7) {
        text-align: center !important;
    }
    #vendorsStockData tbody tr.selected-row,
    #vendorsStockData tbody tr.selected-row td {
        background-color: #d9edf7 !important; /* light blue */
        box-shadow: 0 0 5px rgba(91, 192, 222, 0.5);
    }
</style>
<script>
    $(document).ready(function () {
        // Using formatQuantity from core.js
        var aoColumns = [
            null,   // Supplier
            null,   // Raw Material
            null,   // Unit
            null,   // Location
            null,   // Stock
            null,   // Pending Required
            null    // To be supplied
        ];

    oTable = $('#vendorsStockData').dataTable({

    "aaSorting": [[0, "asc"]],
    "aLengthMenu": [[10,25,50,100,-1],[10,25,50,100,"<?= lang('all') ?>"]],
    "iDisplayLength": <?= isset($Settings->rows_per_page) ? $Settings->rows_per_page : 25 ?>,
    'bProcessing': false, 
    'bServerSide': true,

    "footerCallback": function (row, data, start, end, display) {
        var api = this.api();
        if (api) {
            // Modern DataTables (1.10+)
            function sumCol(idx){
                var total = api
                    .column(idx, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        var x = (typeof a === 'string') ? a.replace(/[^0-9.-]+/g, "") : a;
                        var y = (typeof b === 'string') ? b.replace(/[^0-9.-]+/g, "") : b;
                        var nx = parseFloat(x) || 0;
                        var ny = parseFloat(y) || 0;
                        return nx + ny;
                    }, 0);
                $(api.column(idx).footer()).html(formatQuantity2(total));
            }
            sumCol(4);
            sumCol(5);
            sumCol(6);
        } else {
            // Legacy DataTables (1.9.x) - use nRow and aaData
            try {
                function sumLegacy(idx){
                    var sum = 0;
                    for (var i = 0; i < display.length; i++) {
                        var val = display[i][idx];
                        if (typeof val === 'string') { val = val.replace(/[^0-9.-]+/g, ''); }
                        sum += (parseFloat(val) || 0);
                    }
                    $('th:eq(' + idx + ')', row).html(formatQuantity(sum));
                }
                sumLegacy(4);
                sumLegacy(5);
                sumLegacy(6);
            } catch (e) {
                console.error('Footer callback error:', e);
            }
        }
    },
    // Legacy callback for older DataTables (1.9.x)
    "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
        try {
            function sumLegacy(idx){
                var sum = 0;
                for (var i = 0; i < aiDisplay.length; i++) {
                    var row = aaData[aiDisplay[i]];
                    var val = row && row[idx] ? row[idx] : 0;
                    if (typeof val === 'string') { val = val.replace(/[^0-9.-]+/g, ''); }
                    sum += (parseFloat(val) || 0);
                }
                $('th:eq(' + idx + ')', nRow).html(formatQuantity(sum));
            }
            sumLegacy(4);
            sumLegacy(5);
            sumLegacy(6);
        } catch (e) {
            console.error('Legacy footer callback error:', e);
        }
    },
    'sAjaxSource': '<?= site_url('Variant_bill_of_materials/getVendorStockReport/?v=1'.$v); ?>',
    'fnServerData': function (sSource, aoData, fnCallback) {
        aoData.push({
            "name": "<?= $this->security->get_csrf_token_name() ?>",
            "value": "<?= $this->security->get_csrf_hash() ?>",
        });
        $.ajax({ 'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
    },

    "aoColumns": aoColumns,
    "columnDefs": [
        { "className": "text-center", "targets": [4,5,6] }
    ],

    }).fnSetFilteringDelay().dtFilter([
        {column_number: 0, filter_default_label: "[<?= lang('Vendor'); ?>]", filter_type: "text"},
        {column_number: 1, filter_default_label: "[<?= lang('Raw_Material'); ?>]", filter_type: "text"},
        {column_number: 2, filter_default_label: "[<?= lang('Location'); ?>]", filter_type: "text"},
        {column_number: 3, filter_default_label: "[<?= lang('unit'); ?>]", filter_type: "text"},
    ], "footer");;

    // Auto-select first row after DataTable loads
    oTable.on('draw.dt', function(){
        setTimeout(function(){
            var $firstRow = $('#vendorsStockData tbody tr:first');
            if($firstRow.length && !$firstRow.hasClass('selected-row') && $firstRow.find('a.rm-details').length){
                $firstRow.trigger('click');
            }
        }, 100);
    });

    });
</script>

<script type="text/javascript">
$(document).ready(function () {
    $('#form').hide();  // hide on load

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
    <h2 class="blue"><i class="fa fa-barcode"></i> <?= lang('Vendors_Stock_Report'); ?></h2>

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
            <li><a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
            <li><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
        </ul>
    </div>
</div>


    <p class="introtext"><?= lang('list_results'); ?></p>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form">
                <?php echo form_open("Variant_bill_of_materials/vendor_stock"); ?>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang("Location", "warehouse"); ?>
                            <?php
                            $wh[""] = lang('select').' '.lang('Location');
                            foreach ($warehouses as $warehouse) { $wh[$warehouse->id] = $warehouse->name; }
                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse"');
                            ?>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang("Vendor", "Vendor"); ?>
                            <?php
                            $sup[""] = lang('select').' '.lang('Vendor');
                            foreach ($suppliers as $s) { $sup[$s->id] = $s->name; }
                            echo form_dropdown('supplier', $sup, (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="supplier"');
                            ?>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang("Raw_Material", "product"); ?>
                            <?php
                            $pr[""] = lang('select').' '.lang('product');
                            foreach ($raw_products as $p) { $pr[$p->id] = $p->name; }
                            echo form_dropdown('product', $pr, (isset($_POST['product']) ? $_POST['product'] : ""), 'class="form-control" id="product"');
                            ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                    <a href="<?= site_url('reports/restbutton')?>" class="btn btn-success">Reset</a>
                </div>
                <?php echo form_close(); ?>
                </div>

                <div class="table-responsive">
                    <p class="" style="color: #fa8507; margin-bottom:10px; font-weight:500;">
                        Note: Negative stock denotes surplus inventory available with the vendor.
                    </p>
                    <table id="vendorsStockData" class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                        <tr>
                            <th><?= lang("Vendor"); ?></th>
                            <th><?= lang("Raw_Material"); ?></th>
                            <th><?= lang("Location"); ?></th>
                            <th><?= lang("unit"); ?></th>
                            <th><?= lang("Stock"); ?></th>
                            <th><?= lang("Req._For_Pending_Orders"); ?></th>
                            <th><?= lang("To_Be_Supplied"); ?></th>
                        </tr>
                    </thead>

                        <tbody><tr><td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td></tr></tbody>
                        <tfoot class="dtFilter">
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th id="qty_total"></th>
                                <th id="pending_total"></th>
                                <th id="tosupply_total"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div id="rm-detail" class="mt-15" style="display:none;">
                    <h4 id="rm-detail-title" class="blue"></h4>
                    <div class="table-responsive">
                        <table id="rm-detail-table" class="table table-bordered table-condensed table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>PO#</th>
                                    <th><?= lang("unit"); ?></th>
                                    <th>Total Req Qty</th>
                                    <th>Total Supplied</th>
                                    <th>Consumed Till Date</th>
                                    <th>Locked Stock</th>
                                    <th>Need To Be Supplied</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th></th>
                                    <th id="d_req" class="text-center"></th>
                                    <th id="d_sup" class="text-center"></th>
                                    <th id="d_cons" class="text-center"></th>
                                    <th id="d_lock" class="text-center"></th>
                                    <th id="d_need" class="text-center"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {
    var detailSupplier = '', detailProduct = '', detailSupplierName = '', detailProductName = '';
    var rmDetailTable = $('#rm-detail-table').dataTable({
        "aaData": [],
        "aaSorting": [[0, "asc"]],
        "aLengthMenu": [[10,25,50,100,-1],[10,25,50,100,"All"]],
        "iDisplayLength": 10,
        "bProcessing": false,
        "bServerSide": false,
        "aoColumns": [
            { "sTitle": "PO#" },
            { "sTitle": "<?= lang('unit'); ?>" },
            { "sTitle": "Total Req Qty", "sClass": "text-right" },
            { "sTitle": "Total Supplied", "sClass": "text-right" },
            { "sTitle": "Consumed Till Date", "sClass": "text-right" },
            { "sTitle": "Locked Stock", "sClass": "text-right" },
            { "sTitle": "Need To Be Supplied", "sClass": "text-right" }
        ]
    });

    function qtyFmt(v){
        var n = parseFloat(v);
        if (isNaN(n)) n = 0;
        return n.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function loadRMDetails(supplierId, productId, supplierName, productName){
        console.log('loadRMDetails called with:', {supplierId, productId, supplierName, productName});
        
        detailSupplier = supplierId; detailProduct = productId;
        detailSupplierName = supplierName; detailProductName = productName;
        $('#rm-detail-title').text('Vendor: ' + supplierName + ' | RM: ' + productName);
        $('#rm-detail').show();
        $.getJSON('<?= site_url('Variant_bill_of_materials/getVendorRMStockDetails'); ?>', {supplier_id: detailSupplier || 0, product_id: detailProduct || 0}, function(json){
            if (json && json.status === 'success') {
                var rows = json.rows || [];
                var tableData = [];
                for (var i = 0; i < rows.length; i++) {
                    var r = rows[i];
                    tableData.push([
                        (r.po_reference_no || ''),
                        (r.unit_name || ''),
                        formatQuantity(r.total_req_qty),
                        formatQuantity(r.total_supplied),
                        formatQuantity(r.total_consumed_till_date),
                        formatQuantity(r.locked_stock),
                        formatQuantity(r.need_to_supply)
                    ]);
                }
                rmDetailTable.fnClearTable();
                if (tableData.length) { rmDetailTable.fnAddData(tableData); }
                rmDetailTable.fnDraw();
                $('#d_req').text(qtyFmt(json.totals.total_req_qty));
                $('#d_sup').text(qtyFmt(json.totals.total_supplied));
                $('#d_cons').text(qtyFmt(json.totals.total_consumed_till_date));
                $('#d_lock').text(qtyFmt(json.totals.locked_stock));
                $('#d_need').text(qtyFmt(json.totals.need_to_supply));
            } else {
                rmDetailTable.fnClearTable();
                $('#d_req, #d_sup, #d_cons, #d_lock, #d_need').text(qtyFmt(0));
            }
        }).fail(function(){
            rmDetailTable.fnClearTable();
            $('#d_req, #d_sup, #d_cons, #d_lock, #d_need').text(qtyFmt(0));
        });
    }

    function markSelectedRow($tr){
        $('#vendorsStockData tbody tr').removeClass('selected-row');
        if($tr && $tr.length){ $tr.addClass('selected-row'); }
    }

    // click handler for RM link
    $(document).on('click', 'a.rm-details', function(e){
        e.preventDefault();
        markSelectedRow($(this).closest('tr'));
        loadRMDetails($(this).data('supplier'), $(this).data('product'), $(this).data('supplier-name'), $(this).data('product-name'));
    });

    // click anywhere on the row to trigger RM link
    $(document).on('click', '#vendorsStockData tbody tr', function(e){
        // Don't trigger if clicking on pagination, controls, or the RM link itself
        if ($(e.target).closest('.pagination, .dataTables_paginate, .dataTables_length, .dataTables_filter, a.rm-details').length) {
            return;
        }
        
        var $tr = $(this);
        var $rmLink = $tr.find('a.rm-details');
        
        if ($rmLink.length) {
            markSelectedRow($tr);
            $rmLink.trigger('click');
        }
    });

    $('#pdf').click(function (event) {
        event.preventDefault();
        var url = "<?=site_url('Variant_bill_of_materials/getVendorStockReport/pdf/?v=1'.$v)?>";
        if(detailSupplier && detailProduct){ url += "&detail_supplier="+detailSupplier+"&detail_product="+detailProduct; }
        window.location.href = url;
        return false;
    });
    $('#xls').click(function (event) {
        event.preventDefault();
        var url = "<?=site_url('Variant_bill_of_materials/getVendorStockReport/0/xls/?v=1'.$v)?>";
        if(detailSupplier && detailProduct){ url += "&detail_supplier="+detailSupplier+"&detail_product="+detailProduct; }
        window.location.href = url;
        return false;
    });

    // initialise with empty data already handled by dataTable setup
});
</script>
