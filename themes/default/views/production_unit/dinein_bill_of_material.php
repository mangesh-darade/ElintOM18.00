<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link href="<?= $assets ?>production_unit/css/style.css" rel="stylesheet" /> -->
    <link href="<?= $assets ?>production_unit/css/procurment_orders.css?v=<?= time(); ?>" rel="stylesheet" />
    <script type="text/javascript" src="<?= $assets ?>production_unit/js/dinein_bill_of_material.js?v=<?= time(); ?>"></script>
    <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>

    <!-- <link href="<?= $assets ?>styles/style.css" rel="stylesheet" /> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <!-- Include Redactor.js script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/redactor/3.5.4/redactor.js"></script>
    <!-- Include Redactor.css for styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/redactor/3.5.4/redactor.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        .disabled-product {
            opacity: 0.5;
            cursor: not-allowed;
        }

        #itemTable thead th:nth-child(1),
        #itemTable thead th:nth-child(2) {
            text-align: center;
        }
        .right_section:not(.is-visible) {
        display: none !important; /* Force hide unless active */
    }

        #itemTable thead th:nth-child(3),
        #itemTable thead th:nth-child(4),
        #itemTable thead th:nth-child(5),
        #itemTable thead th:nth-child(6) {
            text-align: center;
        }

        .switch {
            position: relative;
            display: inline-block;
    width: 44px;
    height: 24px;
        }
        .middle_body{
             width: 82%;
        }
        .bom-id {
            margin-left: 33px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .version-bom {
            margin-left: 18px;
            text-align: center;

        }

        .status-toggle {
            margin-left: 18px;
        }

        .created-date {
            margin-left: 18px;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked+.slider {
    background-color: #42b36a; /* brighter green for active */
        }

        input:checked+.slider:before {
    transform: translateX(20px);
        }
    </style>

    <style>
        .red-dot {
            display: inline-block;
            /* Display as inline block to make it a circle */
            width: 10px;
            /* Diameter of the circle */
            height: 10px;
            /* Diameter of the circle */
            background-color: red;
            /* Color of the dot */
            border-radius: 50%;
            /* Makes the element round */
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }

        .print_button {
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
            /* or center if you want */
            gap: 0.5rem;
            /* same as gap-2 */
        }

        .fa-plus-circle {
            cursor: pointer;
        }

        #addedText1 {
            display: none;
            color: #00C314;
        }

        h3.m-0_10.text-left.orderNo {
            margin-left: -0.5rem;
        }

        #outletName {
            margin-left: 19px;
            /* margin-bottom: 17px; */
        }

        .catName {
            display: inline-block;
            /* Ensures the span takes up space */
            vertical-align: middle;
            /* Aligns vertically with adjacent elements */
            margin-right: 10px;
            /* Adjust spacing between elements */
        }

        .modal-footer {
            flex-wrap: wrap;
        }

        .modal-footer small {
            white-space: nowrap;
        }

        .subCatName {
            display: inline-block;
            /* Ensures the span takes up space */
            vertical-align: middle;
            /* Aligns vertically with adjacent elements */
            margin-right: 10px;
            /* Adjust spacing between elements */
        }

        .fa-hand-o-left:before {
            content: "\f0a5";
            font-size: 3rem;
        }

        .font-weight-right {
            display: flex;
            /* Use flexbox for positioning */
            justify-content: center;
            /* Center content horizontally */
            align-items: center;
            /* Center content vertically */
        }

        .btn {
            width: 130px !important;
        }

        .center-btn {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        .ItemsDetails.active {
            background: linear-gradient(to right, #fff 96%, rgb(255, 0, 165) 4%);
            color: #000;
            /* optional for better readability */
        }
        #minQtyContainer {
    display: none;
}
        select#sale_unit_uom {
            height: 3.1rem;
        }

        .Detailshover {
       background: linear-gradient(to right, #fff 91%, #FEC34E)!important;
        }
        span.hover-fill.Details-fill {
        position: absolute;
        background:#FEC34E!important;
        transition: transform 0.7s ease-in-out;
        }
        ul, ol {
        margin-top: 0;
        margin-bottom: 10px;
        margin-left: 15px;
        }
       span#raw_material_title {
        margin-left: 9px;
        }
        #minQtyContainer {
    transition: all 0.3s ease;
        }
       .Displayset{ 
      display: block !important;
       }
       .d-flex.align-items-center.gap-2{
        display: flex;
        flex-wrap: wrap;
       }

    </style>

</head>

<body>
    <section id="procurement_order">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 p-0">
                    <div class="wd-set p-0 bg-setting col-md-2 height-setting">
                        <div class="search-container">
                            <input type="text" placeholder="Search..." name="query" class="search-box" id="left_search">
                            <button type="" class="search-button"><i class="fa fa-search"></i></button>
                        </div>
                        <div class="row mrgn-tp padding-set">
                            <ul class="menu-list recent" style="display: none;">
                                <li class="btn set-bdr-none">
                                    <span class="font-weight">
                                        <img id="" class="mr-1 hand-o-left" style="margin-right: 7px; "
                                            src="<?= base_url() ?>/themes/default/assets/production_unit/images/Back Arrow.svg" />
                                    </span>
                                    <span class="catName"
                                        style="background-color: #009DFF; padding: 3px 1.5rem; color: white; border-radius:0.3rem; width:100%; white-space:normal; word-wrap:wrap;"></span>
                                    <span class="font-weight-right">
                                        <img id="" class="mr-1" style="margin-right: 7px; "
                                            src="<?= base_url() ?>/themes/default/assets/production_unit/images/Right Arrow.svg" />
                                    </span>
                                    <span class="subCatName" style="padding: 5px;"></span>
                                </li>
                            </ul>
                        </div>
                        <div class="row padding-set">
                            <div class="wd-setting" style="">
                                <ul class=" menu-list recentClick">
                                </ul>
                                <ul class=" menu-list categoriesList">
                                </ul>
                                <ul class="menu-list subcategoriesList">
                                </ul>
                                <ul class="menu-list productList">
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="middle_body sty-bg-set col-md-8">

                        <!-- <div class="hide-divs">
                            <div class="d-flex align-items-center set-bg-8">
                                <h3 class="m-0_10 font-weight mt-set">
                                    Date :
                                </h3>
                                <form>
                                    <div class="input-group w-set">
                                        <input type="text" id="requestDeliveryDate" name="requestDeliveryDate"
                                            class="form-control" placeholder="Select a date">
                                        <span class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </span>
                                    </div>
                                </form>
                                <div class="">
                                    <button class="reset-btn" id="reset">Reset</button>
                                </div> 
                                 <div class="w-set1 ml-auto">
                                    <select class="form-control" id="outletName" name="outletName" required="required">
                                        <?php foreach ($outletName as $location): ?>
                                            <option value="<?php echo $location->name; ?>"><?php echo $location->name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div> 
                            </div>
                        </div> -->
                        <div class="bom_material_section_item" style="display: none;">
                            <div class="d-flex align-items-center set-bg-8 Displayset">
                                <div class="container-fluid">
                                    <div class="row align-items-center mb-2">
                                        <!-- Back button -->
                                        <div class="col-lg-2 col-md-3 col-sm-4 mb-2">
                                            <button id="backid" class="clickme w-100">Back</button>
                                        </div>

                                        <!-- Row 1: header info -->
                                        <div class="col-lg-3 col-md-5 col-sm-8 mb-2">
                                            <strong>Product Name:</strong><br>
                                            <span id="product_name"></span>
                                            <input type="hidden" name="product_id" value="" id="product_id">
                                        </div>

                                        <div class="col-lg-2 col-md-4 col-sm-4 mb-2">
                                            <strong>Version:</strong><br>
                                            <span id="version_bom"></span>
                                        </div>

                                        <div class="col-lg-2 col-md-4 col-sm-4 mb-2">
                                            <strong>Status:</strong><br>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="status_toggle">
                                                <span class="slider round"></span>
                                            </label>
                                        </div>

                                        <div class="col-lg-3 col-md-5 col-sm-8 mb-2">
                                            <strong>Date:</strong><br>
                                            <span id="created_date"></span>
                                        </div>
                                    </div>

                                    <div class="row align-items-end">
                                        <!-- Row 2: batch and units -->
                                        <div class="col-lg-2 col-md-3 col-sm-4 mb-2" style="margin-left: 0px; padding: 10px 1px 10px 1px; margin-top: -8px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <strong class="mb-0">Is Batch Only:</strong>
                                                <label class="switch mb-0" style="margin-left:4px;">
                                                    <input type="checkbox" id="is_batch_only_toggle">
                                                    <span class="slider round"></span>
                                                </label>
                                                <input type="hidden" id="is_batch_only" name="is_batch_only" value="0">
                                            </div>
                                        </div>

                                        <div id="minQtyContainer" class="col-lg-2 col-md-4 col-sm-4 mb-2">
                                            <strong>Min. Qty:</strong><br>
                                            <input type="number" name="min_batch_qty" value="0" id="min_batch_qty"
                                                class="form-control form-control-sm text-end minqtyset min_batch_qty" placeholder="1" min="0" required>
                                        </div>

                                        <div class="col-lg-2 col-md-4 col-sm-4 mb-2 uom-field">
                                            <strong>UOM:</strong><br>
                                            <select class="form-control form-control-sm units w-100" id="uom_select">
                                                <option value="">Select Unit</option>
                                                <?php foreach ($units as $unit): ?>
                                                    <option value="<?php echo $unit->id; ?>"><?php echo $unit->name; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-lg-3 col-md-6 col-sm-8 mb-2 sale-uom-field">
                                            <strong>Sale Unit & UOM:</strong><br>
                                            <div class="d-flex gap-2">
                                                <input type="text" id="sale_unit" class="form-control form-control-sm text-end Saleunitset sale_unit flex-fill"
                                                    placeholder="0" />
                                                <select id="sale_unit_uom" class="form-select form-select-sm" style="max-width:120px;">
                                                    <option value="">Unit</option>
                                                    <?php foreach ($units as $unit): ?>
                                                        <option value="<?php echo $unit->name; ?>"><?php echo $unit->name; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-5 col-sm-8 mb-2">
                                            <strong>Workstation:</strong><br>
                                            <select class="form-control form-control-sm w-100" id="workstation_select">
                                                <option value="">Select Workstation</option>
                                                <?php if (!empty($workstations)): ?>
                                                    <?php foreach ($workstations as $ws): ?>
                                                        <option value="<?php echo $ws->id; ?>"><?php echo $ws->name; ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bom_material_section" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center set-bg-8">
                                <!-- Left: Product Info -->
                                <div class="col-md-6 p-2">
                                    <strong>Product Name:</strong>
                                    <span id="SelectedProductName"></span>
                                    <input type="hidden" name="product_id" value="" id="product_id">
                                </div>
                                <!-- Right: Add Button -->
                                <div class="col-md-6 text-right">
                                    <button id="addBomItemBtn" class="btn btn-primary">Add</button>
                                </div>
                            </div>
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">

                                    <!-- Modal Header -->
                                    <div class="modal-header flex-column align-items-stretch position-relative" style="padding-top: 2.5rem;">
                                    
                                       <!-- Title Centered -->
                                        <div class="w-100 text-center mb-2 position-relative">
                                            <h5 class="modal-title PrintSet">BILL OF MATERIALS</h5>

                                            <!-- Close Button at Top-Right of Modal -->
                                        <button type="button"
                                                class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                                data-bs-dismiss="modal"
                                                aria-label="Close"
                                                style="position: absolute; top: 0; right: 0; margin: 0.5rem 2rem; background: white; border: none;">
                                                <i class="fa fa-times"></i>
                                            </button>

                                            <!-- Print Button to the Left of Close Button -->
                                        <button type="button"
                                            class="btn btn-xs btn-default no-print"
                                            onclick="printModalContent();"
                                            style="position: absolute; top: 0; right: 50px; margin: 0.6rem 1rem; height:4vh;">
                                            <i class="fa fa-print"></i> <?= lang('print'); ?>
                                        </button>

                                        </div>
                                        <!-- Second Row: Product Info Left, Created Date Right -->
                                        <div class="w-100 d-flex justify-content-between align-items-center px-3 flex-wrap">
                                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                                <div class="align-items-center" style="margin-right: 20px;">
                                                    <span id="selected_product_name"></span>
                                                    &nbsp;<span id="material_status"></span>
                                                </div>

                                                <div id="minQtyContainer" class="col-md-6 col-sm-12 mb-3 d-flex align-items-center" style="margin-top: 0px;">
                                                    <strong style="margin-right: 5px;">Min. Batch Qty.:</strong>
                                                    <span id="min_batch_qty_new" class="text-end_set bg-light" style="margin-right: 5px;"></span>
                                                </div>

                                            <div id="saleContainer" class="col-md-6 col-sm-12 mb-3 d-flex align-items-center" style="margin-left: 20px;">
                                                <strong style="margin-right: 5px;">Sale Unit:</strong>
                                                <!-- Input for Sale Unit Quantity -->
                                                <!-- <input type="text" id="sale_unit_view"  style="width: 100px; margin-right: 10px; border: none;" readonly /> -->
                                                 <span id="sale_unit_view" style="width: 100px; display: inline-block;"></span>
                                                 <!-- <span id="uom_name" class="bg-light" style="display: inline-block; margin-left: -78px;"></span> -->
                                            </div>
                                            </div>
                                            <div class="text-end Setdate" style="margin-top: -20px; margin-left: auto;">
                                             Created: <span id="created_at"></span>
                                            </div>
                                        </div>   

                                        <!-- Third Row: Print Button Right -->
                                        <!-- <div class="w-100 justify-content-end gap-2 mt-2" style="display: flex; justify-content: end;">
                                            <button type="button" class="btn btn-xs btn-default no-print" onclick="printModalContent();" style="margin-top: 12px;">
                                                <i class="fa fa-print"></i> <?= lang('print'); ?>
                                            </button>
                                        </div> -->
                                    </div>

                                    <div class="modal-body">
                                        <table id="itemTable" class="table table-bordered display" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Ref. No.</th>
                                                    <th>Required Qty.</th>
                                                    <th>UOM</th>
                                                    <th>Wastage (%)</th>
                                                    <!-- <th>Is Alternative</th> -->
                                                    <th>Alternative</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic rows here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Modal Footer -->
                                    <div class="modal-footer justify-content-center position-relative">
                                        <!-- Centered Text -->
                                        <div class="text-center">
                                            <small><strong>© 2025 ElintOM</strong></small>
                                        </div>

                                        <!-- Right-side Printed Date using absolute position -->
                                        <div class="position-absolute end-0 me-3" style="margin-top: -20px;">
                                            <small>Printed on: <span id="print_date"></span></small>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                        <!-- Script to insert current date and time -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const printDateElem = document.getElementById('print_date');
                                if (printDateElem) {
                                    printDateElem.textContent = new Date().toLocaleString();
                                }
                            });
                        </script>


                        <div class="tab-content overflowset">
                            <div class="tab-pane fade in active text-center" role="tabpanel">
                                <h3 class="m-0_10 text-left" id="order_status"></h3>
                                <div class="col-md-12 p-0">
                                    <table class="table table-bordered rounded-table text-center" id="dynamicTable">
                                        <thead>
                                        </thead>
                                        <tbody class="text-center">
                                        </tbody>
                                        <tfoot>
                                        </tfoot>
                                    </table>
                                    <div class="col-md-12 mb-3" id="editorRow" style="display: none;">
                                        <div>
                                            <!-- <textarea id="editor"></textarea> -->
                                            <?php echo form_textarea('note', '', 'class="form-control col-md-12" id="editor" style="margin-top: 10px; height: 100px;"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <button class="btn add_note col-md-3" id="add_note" style="display: none; margin:0.8rem;  background: #FFDACE; color: #000; box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);
                                            outline: none;" value="place_order" type="submit"> <i
                                                    class="fa fa-file-text-o set-fnt"> Add Note</i>
                                            </button>
                                            <button class="btn saveRawData col-md-3" id="saveRawData" style="display: none; margin:0.8rem; background: #039be5; color: #fff; box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);
                                           outline: none;" value="saveRawData" type="submit">Save
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wd-set bg-setting right_section col-md-2" style="display: none;">
                        <div class="search-container">
                            <input type="text" placeholder="Search..." name="query" class="search-box"
                                id="raw_material_search">
                            <button type="" class="search-button"><i class="fa fa-search"></i></button>
                        </div>
                        <div class="m-0_10 text-left orderNo">
                            <b><span id="raw_material_title">Ingredients</span></b>
                            <div class="row padding-set">
                                <div class="wd-setting" style="">
                                    <ul class="menu-list rawProductList">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
<script>
    var units = <?php echo json_encode($units); ?>;
    var categories = <?php echo json_encode($categories); ?>;
    var site = {
        settings: {
            qty_decimals: <?php echo $this->Settings->qty_decimals; ?>
        }
    };
    console.log(categories)
</script>
<script>
    var csrf_token_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?php echo $this->security->get_csrf_hash(); ?>';
</script>
<!-- DataTables JS & CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    function printModalContent() {
        const modalContent = document.querySelector('#itemModal .modal-content');
        const printWindow = window.open('', '', 'height=600,width=800');

        printWindow.document.write(`
        <html>
        <head>
            <title>Print Modal</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
            <style>
                body { padding: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }

                @media print {
                    .no-print {
                        display: none !important;
                    }
                }
                    .position-absolute.end-0.me-3 {
                        display: flex;
                        justify-content: end;
                        margin-top: 4px;
                    }
                        .text-end {
                        display: flex;
                        justify-content: end;
                    }
                        h5.modal-title.PrintSet {
                        display: flex;
                        justify-content: center;
                    }
                        .text-center {
                        display: flex;
                        justify-content: center;
                        margin-bottom: 4px;
                    }
                        div#saleContainer {
                        display: flex;
                        justify-content: center;
                        margin-top: -18px;
                        margin-left: 12rem !important;
                    }
                        div#minQtyContainer {
                        margin-left: 11rem;
                    }
                        button.btn.btn-xs.btn-default.no-print {
                        display: none;
                    }
                        button.btn-close {
                        display: none;
                    }
                    
                    .modal-body{
                    margin-top: 14px;
                    }
                    .text-center {
                    display: flex;
                    justify-content: center;
                    margin-top: 15px;
                    }
                    span#selected_product_name {
                    display: flex;
                    }

            </style>
        </head>
        <body>
            ${modalContent.innerHTML}
        </body>
        </html>
    `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
    
// Show modal
$(document).on('click', '.view-icon', function () {
    $('#itemModal').modal('show');
});

// Close modal properly using Bootstrap API
document.querySelector('#itemModal .fa-times').closest('button').addEventListener('click', function () {
    $('#itemModal').modal('hide');
});
// $(document).ready(function () {
//     $('#reset').on('click', function () {
//         localStorage.removeItem('productRawDetailsList');
//         $('.bom_material_section_item').hide();
//         $('#bom_material_main').show(); 
//     });
// });
document.getElementById('sale_unit').addEventListener('input', function () {
    this.value = this.value.replace(/-/g, '');
    if (parseFloat(this.value) < 0) this.value = 0;
});

 const minBatchQtyInput = document.getElementById('min_batch_qty');

    // Prevent typing minus sign
    minBatchQtyInput.addEventListener('keydown', function (e) {
        if (e.key === '-' || e.key === 'Subtract') {
            e.preventDefault();
        }
    });

    // Also prevent paste of negative values
    minBatchQtyInput.addEventListener('input', function () {
        if (this.value < 0) {
            this.value = 0;
        }
});
</script>
<script>
// Run this as early as possible, before any other scripts
if (performance.navigation.type === 1 || 
    performance.getEntriesByType('navigation')[0].type === 'reload') {
    localStorage.clear();
}
</script>


</html>
