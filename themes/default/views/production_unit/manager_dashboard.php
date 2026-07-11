<!-- <link href="<?= $assets ?>production_unit/css/style.css" rel="stylesheet" /> -->
<link href="<?= $assets ?>production_unit/css/manager_dashboard.css" rel="stylesheet" />
<link href="<?= $assets ?>styles/style.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

<script type="text/javascript" src="<?= $assets ?>production_unit/js/manager_dashboard.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>


<!-- jQuery library -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- jQuery UI library -->
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<style>
    .btnset {
        margin-left: 30px !important;
    }
</style>
<style>
    .wastage-container {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .wastage-input {
        width: 80px;
        text-align: center;
        padding: 4px 6px;
        font-size: 13px;
        border-radius: 4px;
        margin-left: 30px;
    }
.alt-badge {
    display: inline-block;
    background-color: #FF8846;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
    margin-bottom: 4px;
}
/* Grey only Avl Qty, Batch No, Expiry Date, Wastage */
.alt-disabled-row .available-qty-td,
.alt-disabled-row .available-qty-td *,
.alt-disabled-row .batch-qty-td,
.alt-disabled-row .batch-qty-td *,
.alt-disabled-row .expiry_date,
.alt-disabled-row .expiry_date *,
.alt-disabled-row .wastage_td,
.alt-disabled-row .wastage_td * {
    color: #999 !important;
    opacity: 0.9;
    pointer-events: none;
}

/* Req Qty badge inside disabled alternative */
.alt-disabled-row .req_qty_td .req-badge,
.alt-disabled-row .req_qty_td .req-badge span {
    background-color: #f1f1f1 !important;
    color: #999 !important;
}

/* Avl Qty red badge -> grey when alternative disabled */
.alt-disabled-row .qty-badge {
    background-color: #e0e0e0 !important;
    color: #999 !important;
}
/* Disabled checkbox - visual cue */
.large-checkbox:disabled {
    background-color: #f0f0f0 !important;
    border-color: #b5b5b5 !important;
    cursor: not-allowed;
    opacity: 0.6;
}

/* Disabled + checked */
.large-checkbox:disabled:checked {
    background-color: #d6d6d6 !important;
}

/* Disabled tick mark */
.large-checkbox:disabled:checked::after {
    border-color: #888 !important;
}

</style>

<style>
    .checkbox-placeholder {
        display: inline-block;
        width: 25px;   
        height: 40px;
    }
    .td-center-flex {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px; /* spacing between checkbox and text */
    }

    #builds_Quantity_Container {
        display: flex;
        align-items: center;
    }

    #builds_Quantity {
        text-align: center;
        height: 20px;

    }

    .kot-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin-top: 20px;
    }

    .kot-table th,
    .kot-table td {
        border: 1px solid #333;
        padding: 8px;
        text-align: center;
    }

    .kot-table th {
        background-color: #f2f2f2;
    }

    @media print {
        .btnset {
            display: none;
        }
    }
    
  .modal-header {
    min-height: 16.43px;
    padding: 15px;
    border-bottom: none!important;
}
.modal-title {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 18px;
}


</style>
<style>
    /* RIGHT divider only on merged cells (rowspan>1), avoid double with Sr. No. column */
    td.merge-border { position: relative; }
    td.merge-border:not(:first-child)::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        right: 0;
        width: 1px;
        background: #e0e0e0;
    }
    /* Persistent vertical divider for Sr. No. column */
    #rmConsumptionModal table.table thead th:first-child,
    #rmConsumptionModal table.table tbody td:first-child {
        border-right: 1px solid #e0e0e0;
    }
    /* Align wastage header text with the input boxes */
    #rmConsumptionModal table.table thead th.wastage-th {
        padding-left: 35px;
        display: flex;
        align-items: center; /* vertical center */
        justify-content: flex-start;
    }
    .available-qty {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .available-qty span {
        font-size: 18px;
        color: #337ab7;
    }
    /* Remove forced left alignment on Available Qty to revert to default (center via table row class) */
    /* If any left-align rule exists elsewhere, override here */
    #rmConsumptionModal table.table tbody td:nth-child(4) { text-align: center; }
</style>
<script>
    function printDiv(divId) {
        var printContents = document.getElementById(divId).innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload(); // reload to restore JS bindings
    }
</script>

<script>
    function showDiv() {
        document.getElementById('welcomeDiv').style.display = "block";
    }

    function hideDiv() {
        document.getElementById('welcomeDiv').style.display = "none";
    }
</script>
<script>
    function showDiv(li) {
        // Remove 'active' class from all li elements
        document.querySelectorAll('.mainmenu li').forEach(item => {
            item.classList.remove('active');
        });
        // Add 'active' class to the clicked li element
        li.classList.add('active');
        // Check if the clicked li is the third one
        if (li === document.querySelectorAll('.mainmenu li')[2]) {
            // Show the welcomeDiv
            document.getElementById('welcomeDiv').style.display = "block";
        } else {
            // Hide the welcomeDiv if the clicked li is not the third one
            document.getElementById('welcomeDiv').style.display = "none";
        }
    }

    function hideDiv() {
        document.getElementById('welcomeDiv').style.display = "none";
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allotInputs = document.querySelectorAll('.allot-input');

        allotInputs.forEach(input => {
            input.addEventListener('input', function() {
                const row = input.closest('tr');
                const requestedQuantity = parseInt(row.querySelector('.requested-quantity')
                    .innerText, 10);
                const allotValue = parseInt(input.value, 10);

                if (!isNaN(allotValue)) {
                    if (allotValue >= requestedQuantity) {
                        row.style.backgroundColor = '#d4edda'; // Green background color
                    } else if (allotValue >= 40) {
                        row.style.backgroundColor = '#FFEFCF'; // Yellow background color
                    } else {
                        row.style.backgroundColor = '#FFD9D9'; // Red background color
                    }
                } else {
                    row.style.backgroundColor = ''; // Reset to default
                }
            });
        });
    });
</script>

<section id="dashbord_items">
    <div class="container-fluid">
        <div class="row brd-set">
            <div class="col-md-3 p-0 brd-right1 height-set1">

                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-sitemap icon-th" aria-hidden="true"></i><span
                            class="break"></span>Items</h2>
                </div>
                <div class="search-container">
                    <!-- <form action="search.php" method="GET"> -->
                    <input type="text" placeholder="Search Items" name="query" class="search-box">
                    <button type="submit" class="search-button"><i class="fa fa-search"></i></button>
                    <!-- </form> -->
                </div>



                <!-- <div class="menu">

                    <ul class="mainmenu">
                        <li onclick="showDiv(this)" class="active">Veg Puff</li>
                        <li onclick="showDiv(this)">Bhakarwadi</li>
                        <li onclick="showDiv(this)"> Kachori </li>

                        <li onclick="showDiv(this)">Surali Wadi</li>
                        <li onclick="showDiv(this)">Pani Puri</li>
                        <li onclick="showDiv(this)">Shev Puri</li>
                        <li onclick="showDiv(this)">Lachcha Tokri</li>
                        <li onclick="showDiv(this)">Raaj Kachori Puri</li>
                        <li onclick="showDiv(this)">Farsan</li>
                    </ul>
                </div> -->
                <div class="menu">
                    <ul class="mainmenu"></ul>
                </div>
            </div>


            <div class="col-md-9 p-0 max-height">
                <div class="row">
                    <div class="text-start d-flex drd-all">
                        <h3 class="mrgn-set">Currently viewing orders for:</h3>
                        <div class="dropdown" id="drop">
                            <select class="form-control dropdown-toggle production-unit-select" id="productionUnitName"
                                name="productionUnitName" required="required">
                                <?php if (!empty($productionUnitName)): ?>
                                    <?php foreach ($productionUnitName as $Name => $productionUnit): ?>
                                        <option value="<?php echo $productionUnit; ?>"
                                            <?php echo $Name === 0 ? 'selected' : ''; ?>>
                                            <?php echo $productionUnit; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value=""><?php echo lang('No Production Unit'); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="btnset">
                            <button class="button-set-1" onclick="order_kot()" style="height: 3.5rem; width: 5.5rem;">KOT</button>
                        </div>

                        <div class="ml-auto mr-2">
                            <div id="iconWithTime1">
                                <i class="fa fa-clock"></i>
                                <span id="currentTime1"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex">
                                <div class="box col-md-4 p-0">
                                    <div class="d-flx1">
                                        <h2 class="set-brd">Stock</h2>
                                        <div class="cursor" data-toggle="modal" data-target="#reset">
                                            <img src="<?php echo base_url('themes/default/assets/images/ResetStock.svg'); ?>"
                                                alt="Your SVG Image">
                                        </div>

                                    </div>

                                    <div class="text-center">
                                        <!-- <p class="fnt-set" id ="">57</p> -->
                                        <p class="fnt-set" id="stockQuantity"></p>
                                    </div>
                                    <!-- <div class="d-flex">
                                        
                                                <div class="col-md-6 mrgn-bottom text-center padding-leftset"><button
                                                class="button-set">Ingredients</button></div>
                                    </div> -->
                                </div>

                                <div class="box col-md-4 p-0">
                                    <h2 class="set-brd">Order Quantity</h2>
                                    <div class="text-center">
                                        <p class="fnt-set" id="orderQuantity"></p>
                                        <!-- <p class="fnt-set">530</p> -->
                                    </div>
                                </div>

                                <div class="box col-md-4 p-0">
                                    <h2 class="set-brd">Build</h2>
                                    <div class="text-center">
                                        <!-- <p class="fnt-set">473</p> -->
                                        <p class="fnt-set" id="buildQuantity"></p>
                                    </div>
                                    <div class="col-md-6 text-center  mrgn-bottom">
                                         <!-- <button onclick="showtable()" class="button-set">Batch</button> -->
                                        <button id="showTable" class="button-set">Batch</button>
                                    </div>
                                    <div class="col-md-6 mrgn-bottom text-center padding-leftset">
                                        <button id="ingredients" class="button-set" data-toggle="modal" data-target="#ingredientsModal" style="margin-left: 8px;">BOM</button>
                                    </div>                                
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row set-mtmb" id="mytable" style="display: none;">
                        <div class="col-md-12">
                            <div class="row disp-setting">

                                <div class="text-center col-md-12 mrgn-tt">
                                    <button class="w-100" data-toggle="modal" data-target="#batchModal"
                                        id="addBatchModal">
                                        <div class="d-flx12">
                                            <div class="mr-31">Add Batch</div>
                                            <div class="icon-setting">
                                                <i class="fa-fw fa fa-plus" style="color: #fff;"></i>
                                            </div>
                                        </div>
                                    </button>
                                </div>

                                <div class="col-md-11">
                                    <div class="">
                                        <h2>Today’s 5 Most Recent Batches:</h2>
                                    </div>

                                    <table class="table fnt-s">
                                        <thead>
                                            <tr class="text-center">
                                                <th scope="col">Batch Id</th>
                                                <th scope="col">Product Id</th>
                                                <th scope="col">Qty</th>
                                                <th scope="col">Unit</th>
                                                <th scope="col">Mfg. Date</th>
                                                <th scope="col">Exp. Date</th>

                                            </tr>
                                        </thead>
                                        <tbody class="" id="latestbatchestablebody">
                                        </tbody>
                                    </table>


                                    <div class="d-flex justify mrgn-bt">
                                        <!-- <button class="button-set1" onclick="hidetable()">Close</button> -->
                                        <button class="button-set1" id="hideTable">Close</button>
                                        <button class="button-set1" data-toggle="modal"
                                            data-target=".bd-example-modal-lg">View
                                            All Batches</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <table class="table table-bordered fnt-s">
                            <thead class="bg-white">
                                <tr class="text-center">
                                    <th scope="col">Order #</th>
                                    <th scope="col">Age</th>
                                    <th scope="col">Requested Quantity</th>
                                    <th scope="col">Allot</th>
                                </tr>
                            </thead>
                            <tbody id="tablebody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Today’s Batches:</h5>
                <button type="button" class="set-to-close close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table fnt-s">
                    <thead>
                        <tr class="text-center">
                            <th scope="col">Batch Id</th>
                            <th scope="col">Product Id</th>
                            <th scope="col">Qty.</th>
                            <th scope="col">Unit</th>
                            <th scope="col">Mfg. Date</th>
                            <th scope="col">Exp. Date</th>
                        </tr>
                    </thead>
                    <tbody class="" id="batchtablebody">
                    </tbody>

                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom-bdsetting" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="note" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content brd">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Note from Outlet: </h5>
                <button type="button" class="close set-to-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal-body-content">
                <!-- Modal content will be dynamically inserted here -->
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="batchModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div class="batch-modal-header-main">
                    <h4 class="modal-title" id="batchModalProductName" style="font-weight:bold; margin:0;"></h4>
                    <div class="batch-modal-variant-wrap" id="batchVariantRow">
                        <h3><b>Variant:</b></h3>
                        <select id="batchProductVariant" class="form-control batch-variant-select" aria-label="Product variant">
                        </select>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-md-7 pd-lf">
                        <div class="row display-set mrgn-tp">
                            <div class="col-md-6">
                                <h2>Build Quantity</h2>
                                <small id="minBatchQtyText" style="color:#777;"></small>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="quantity[]" id="allotbatchqty" class="allot-set"
                                    placeholder="0" min="1">
                            </div>

                        </div>

                        <div class="row display-set">
                            <div class="col-md-6">
                                <!-- <h3><b id="batchId">Batch Id</b></h3> -->
                                <h3><b>Batch Id: <span id="batchId"></span></b></h3>

                            </div>
                            <div class="col-md-6">
                                <!-- <h3><b id="manufacturingDate">Mfr. Date</b></h3> -->
                                <h3><b>Mfg. Date: <br><span id="manufacturingDate"></span></b></h3>

                            </div>
                        </div>

                        <div class="row display-set">
                            <div class="col-md-6">
                                <!-- <h3><b id="productsId">Product Id:</b></h3> -->
                                <h3><b> Product Id : <br><span id="productsId"></span></b></h3>

                            </div>
                            <div class="col-md-6">
                                <h3><b>Expiry Date: <br><span id="expiryDate"></span></b></h3>

                            </div>
                        </div>

                        <div class="row display-set">
                            <div class="col-md-6">
                                <!-- <h3><b id="unit">Unit</b></h3> -->
                                <h3><b>Unit: <br><span id="unit"></span></b></h3>
                            </div>
                            <div class="col-md-6">
                                <h3><b>Price: <br>Rs :<span id="price"></span></b></h3>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-5 text-center">
                        <button id="addBatch" class="set-box">

                            <h2>Add Batch</h2>

                        </button>
                        <button class="set-box1">

                            <h2>Print Label</h2>

                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom-bdsetting" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Multi-variant Add Batch modal (variant products only) -->
<div class="modal fade bd-example-modal-kg" id="variantMultiBatchModal" tabindex="-1" role="dialog" aria-labelledby="variantMultiBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="vmb-static-head">
                <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h4 class="modal-title" id="variantMultiBatchProductName" style="font-weight:bold; margin:0; font-size:18px;"></h4>
                    <button type="button" class="set-to-close close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="vmb-meta-section">
                <div class="vmb-meta-panel">
                    <div class="row display-set">
                        <div class="col-md-4 col-sm-6"><h3><b>Batch Id:</b> <span id="vmb_batchId"></span></h3></div>
                        <div class="col-md-4 col-sm-6"><h3><b>Product Id:</b> <span id="vmb_productsId"></span></h3></div>
                        <div class="col-md-4 col-sm-6"><h3><b>Unit:</b> <span id="vmb_unit"></span></h3></div>
                    </div>
                    <div class="row display-set" style="margin-bottom:0;">
                        <div class="col-md-4 col-sm-6"><h3><b>Mfg. Date:</b> <span id="vmb_manufacturingDate"></span></h3></div>
                        <div class="col-md-4 col-sm-6"><h3><b>Expiry Date:</b> <span id="vmb_expiryDate"></span></h3></div>
                        <div class="col-md-4 col-sm-6"><h3><b>Price:</b> Rs :<span id="vmb_price"></span></h3></div>
                    </div>
                    <h3 class="vmb-min-batch-qty" id="vmb_minBatchQtyText"></h3>
                </div>
            </div>
            <div class="vmb-buildset-hint">Enter batch quantity per variant (0 = skip that variant)</div>
            <div class="vmb-grid-section">
                <table class="table table-bordered vmb-variant-grid fnt-s">
                    <thead>
                        <tr>
                            <th class="vmb-col-variant">Variant Name</th>
                            <th style="width:140px;">Batch Qty</th>
                        </tr>
                    </thead>
                    <tbody id="vmb_variantGridBody"></tbody>
                </table>
            </div>
            <div class="modal-footer vmb-footer">
                <button type="button" id="variantMultiAddBatch" class="btn btn-primary vmb-btn-add">Add Batch</button>
                <button type="button" class="btn vmb-btn-print vmb-print-label-btn">Print Label</button>
                <button type="button" class="btn btn-secondary custom-bdsetting" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reset" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="display-seeting">
                    <span class="mrg-right"><img src="<?php echo base_url('assets/images/Warning.svg'); ?>"
                            alt="Your SVG Image"></span>
                    <h5 class="modal-title" id="exampleModalLongTitle">Reset Stock</h5>
                </div>

                <button type="button" class="set-close close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h2>Are you sure you want to Reset Stock for Item: <span id="productName"></span>?</h2>
                <div class="align-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmResetStock">Confirm</button>
                    <!-- <button type="button" class="btn btn-primary" id="confirmResetStock" onclick="$('#reset').modal('hide');">Confirm</button> -->

                </div>
            </div>
            <!-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> -->
        </div>
    </div>
</div>
<!-- Ingredients Modal -->
<div class="modal fade bd-example-modal-kg" id="ingredientsModal" tabindex="-1" role="dialog" aria-labelledby="ingredientsModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 75vw; width: 75vw;">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h5 class="modal-title" id="ingredientsModalLabel" style="margin: 0;">Bill Of Materials</h5>
                </div>

                <button type="button" class="set-to-close close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="buildset">
            <div for="builds_Quantity" style="display: inline-flex; justify-content: center; align-items: center; border: 2px solid black; border-radius: 10px; padding: 5px 10px; font-weight: bold; font-size: 18px; color: #222; text-align: center; margin-left: 17px;">
            <span style="margin-right: 3px;">Build Qty:-</span>
            <input type="text" id="builds_Quantity" style="width: 80px; border: none; outline: none; font-weight: bold; font-size: 18px; color: #222; text-align: center;"></div>
            <label for="Min_batch_quantity" style="margin: 0; margin-left: 50px;">Min. Batch Quantity:</label>
            <span id="Min_batch_quantity"></span>
            <label for="selling_unit" style="margin: 0; margin-left: 55px;">Selling Unit:</label>
            <span id="selling_unit"></span>
        </div>
            <!-- Modal Body -->
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <table class="table fnt-s" style="margin-top: 10px; border-collapse: collapse; border-spacing: 0;">
                    <thead style="position: sticky; top: 0; z-index: 9999 !important; background: white !important; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                        <tr class="text-center" style="background: white !important;">
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">#</th>
                            <th class="text-left" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Ingredient</th>
                            <th class="text-left" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Required Qty.</th>
                            <th class="text-left" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Available Qty.</th>
                            <th class="text-left" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Batch No.</th>
                            <th class="text-left" style="text-align:center; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Expiry Date</th>
                            <!-- <th>Options</th> -->
                        </tr>
                    </thead>
                    <tbody id="ingredientsTableBody" style="border: 1px solid #ddd;">
                    </tbody>
                </table>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; padding: 12px 20px;">
                <button type="button" class="btn btn-secondary custom-bdsetting" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn btn-xs btn-default no-print pull-right" onclick="printModalContent();">Print</button>
            </div>
        </div>
    </div>
</div>
<!-- Variant Ingredients Modal (same layout as Ingredients; variant data fetch) -->
<div class="modal fade bd-example-modal-kg" id="variantIngredientsModal" tabindex="-1" role="dialog" aria-labelledby="variantIngredientsModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 75vw; width: 75vw;">
        <div class="modal-content">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h5 class="modal-title" id="variantIngredientsModalLabel" style="margin: 0;">Variant Ingredients</h5>
                </div>
                <button type="button" class="set-to-close close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="buildset">
                <div for="variant_builds_Quantity" style="display: inline-flex; justify-content: center; align-items: center; border: 2px solid black; border-radius: 10px; padding: 5px 10px; font-weight: bold; font-size: 18px; color: #222; text-align: center; margin-left: 17px;">
                    <span style="margin-right: 3px;">Build Qty:-</span>
                    <input type="text" id="variant_builds_Quantity" style="width: 80px; border: none; outline: none; font-weight: bold; font-size: 18px; color: #222; text-align: center;">
                </div>
                <label for="variant_Min_batch_quantity" style="margin: 0; margin-left: 50px;">Min. Batch Quantity:</label>
                <span id="variant_Min_batch_quantity"></span>
                <label for="variant_selling_unit" style="margin: 0; margin-left: 55px;">Selling Unit:</label>
                <span id="variant_selling_unit"></span>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <table class="table fnt-s" style="margin-top: 10px; border-collapse: collapse; border-spacing: 0;">
                    <thead style="position: sticky; top: 0; z-index: 9999 !important; background: white !important; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                        <tr class="text-center" style="background: white !important;">
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">#</th>
                            <th class="text-left" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Ingredient</th>
                            <th class="text-left" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Required Qty.</th>
                            <th class="text-left" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Available Qty.</th>
                            <th class="text-left" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Batch No.</th>
                            <th class="text-left" style="text-align:center; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody id="variantIngredientsTableBody" style="border: 1px solid #ddd;"></tbody>
                </table>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; padding: 12px 20px;">
                <button type="button" class="btn btn-secondary custom-bdsetting" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn btn-xs btn-default no-print pull-right" onclick="printVariantIngredientsModalContent();">Print</button>
            </div>
        </div>
    </div>
</div>
<!-- RM Consumption Modal -->
<div class="modal fade bd-example-modal-kg" id="rmConsumptionModal" tabindex="-1" role="dialog" aria-labelledby="rmConsumptionModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 90vw; width: 90vw;">
        <div class="modal-content">
            
            <div class="static-head">
                <!-- Modal Header -->
                <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px 5px 20px;">
                    <h5 class="modal-title" id="rmConsumptionModalLabel" style="margin: 0;">RM Consumption</h5>
                    <button type="button" class="set-to-close close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Batch Qty Display -->
                <div class="buildset" style="padding-left:10px;">
                    <div style="display:flex; gap:30px; align-items:center; flex-wrap:wrap;">

                        <!-- Batch Qty (existing – keep as is) -->
                        <div style="font-weight:bold; font-size:16px;">
                            Build Qty:
                            <span id="rmBatchQuantity"
                                style="min-width:60px; display:inline-block; text-align:center;">
                            </span>
                            <span id="rmBatchUnit" style="margin-left: 5px;"></span>
                        </div>

                        <!-- Yield Qty -->
                        <div class="yield-sales-container" style="display:flex; gap:20px; align-items:center; font-weight:bold; font-size:16px;">
                            <div style="font-size:16px; font-weight:bold;">Yield Qty:</div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <input type="text"
                                    id="yieldQty"
                                    class="batch-like-input"
                                    inputmode="decimal"
                                    step="any"
                                    autocomplete="off"
                                    onkeydown="event.stopPropagation();"
                                    onfocus="this.select();"
                                    style="width:100px; padding:5px; border:1px solid #050505ff; border-radius:4px;">
                                <span id="yieldUnit" class="unit-text"></span>
                            </div>
                        </div>

                        <!-- Sales Qty -->
                        <div class="sales-container" style="display:flex; gap:20px; align-items:center; font-weight:bold; font-size:16px;">
                            <div style="font-size:16px; font-weight:bold;;">Sales Qty:</div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <input type="text"
                                    id="salesQty"
                                    class="batch-like-input"
                                    inputmode="decimal"
                                    step="any"
                                    autocomplete="off"
                                    onkeydown="event.stopPropagation();"
                                    onfocus="this.select();"
                                    style="width:100px; padding:5px; border:1px solid #050505ff; border-radius:4px;">
                                <span id="salesUnit" class="unit-text"></span>
                            </div>
                        </div>

                        <!-- Wastage Field -->
                        <div class="wastage-container" style="display:flex; gap:20px; align-items:center; font-weight:bold; font-size:16px;">
                            <div style="font-size:16px; font-weight:bold;">Wastage:</div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <input type="text"
                                    id="wastageW3"
                                    class="batch-like-input"
                                    readonly
                                    inputmode="decimal"
                                    step="any"
                                    autocomplete="off"
                                    onkeydown="event.stopPropagation();"
                                    onfocus="this.select();"
                                    style="width:100px; padding:5px; border:1px solid #050505ff; border-radius:4px; background-color:#f5f5f5; color:#666;">
                                <span id="wastageW3Unit" class="unit-text"></span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>



            <!-- Modal Body -->
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <table class="table fnt-s" style="margin-top: 10px; border-collapse: separate; border-spacing: 0 5px;">
                    <thead style="position: sticky; top: 0; z-index: 9999 !important; background: white !important; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                        <tr class="text-center" style="background: white !important;">
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">#</th>
                            <th class="text-left" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Ingredient</th>
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Req Qty.</th>
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Avl Qty.</th>
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Batch No.</th>
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Expiry Date</th>
                            <th class="text-left wastage-th" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Wastage</th>
                        </tr>
                    </thead>
                    <tbody id="rmConsumptionTableBody">
                        <!-- Rows populated dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; padding: 12px 20px;">
                <button type="button" class="btn btn-secondary custom-bdsetting" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="rmConsumptionSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Variant RM Consumption Modal -->
<div class="modal fade bd-example-modal-kg" id="variantRmConsumptionModal" tabindex="-1" role="dialog"
    aria-labelledby="variantRmConsumptionModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 90vw; width: 90vw;">
        <div class="modal-content">
            <div class="static-head">
                <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px 5px 20px;">
                    <h5 class="modal-title" id="variantRmConsumptionModalLabel" style="margin: 0;">Variant RM Consumption</h5>
                    <button type="button" class="set-to-close close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="buildset" style="padding-left:10px;">
                    <div id="variantRmSingleQtyRow" style="display:flex; gap:30px; align-items:center; flex-wrap:wrap;">
                        <div class="variant-build-qty-wrap" style="font-weight:bold; font-size:16px;">
                            <span>Build Qty:</span>
                            <span id="variantRmBatchQuantity"></span>
                            <span id="variantRmBatchUnit" class="unit-text"></span>
                        </div>
                        <div class="yield-sales-container" style="display:flex; gap:20px; align-items:center; font-weight:bold; font-size:16px;">
                            <div style="font-size:16px; font-weight:bold;">Yield Qty:</div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <input type="number" id="variantYieldQty" class="batch-like-input"
                                    inputmode="decimal" autocomplete="off" onkeydown="event.stopPropagation();"
                                    onfocus="this.select();"
                                    style="width:100px; padding:5px; border:1px solid #050505ff; border-radius:4px;">
                                <span id="variantYieldUnit" class="unit-text"></span>
                            </div>
                        </div>
                        <div class="sales-container" style="display:flex; gap:20px; align-items:center; font-weight:bold; font-size:16px;">
                            <div style="font-size:16px; font-weight:bold;">Sales Qty:</div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <input type="number" id="variantSalesQty" class="batch-like-input"
                                    inputmode="decimal" autocomplete="off" onkeydown="event.stopPropagation();"
                                    onfocus="this.select();"
                                    style="width:100px; padding:5px; border:1px solid #050505ff; border-radius:4px;">
                                <span id="variantSalesUnit" class="unit-text"></span>
                            </div>
                        </div>
                        <div class="wastage-container" style="display:flex; gap:20px; align-items:center; font-weight:bold; font-size:16px;">
                            <div style="font-size:16px; font-weight:bold;">Wastage:</div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <input type="number" id="variantWastageW3" class="batch-like-input" readonly
                                    inputmode="decimal" autocomplete="off" onkeydown="event.stopPropagation();"
                                    onfocus="this.select();"
                                    style="width:100px; padding:5px; border:1px solid #050505ff; border-radius:4px; background-color:#f5f5f5; color:#666;">
                                <span id="variantWastageW3Unit" class="unit-text"></span>
                            </div>
                        </div>
                    </div>
                    <div id="variantRmMultiQtyWrap" style="display:none;">
                        <div class="vrm-summary-panel">
                            <p class="vrm-summary-panel-title">Variant quantity (Yield / Sales / Wastage)</p>
                            <table id="variantRmMultiQtyGrid" class="table table-bordered table-condensed" style="margin:0; background:#fff;">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-left">Variant</th>
                                        <th>Build Qty</th>
                                        <th>Yield Qty</th>
                                        <th>Sales Qty</th>
                                        <th>Wastage</th>
                                    </tr>
                                </thead>
                                <tbody id="variantRmMultiQtyGridBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="vrm-grid-separator" id="variantRmGridSeparator" style="display:none;">
            <p class="vrm-ingredients-heading" id="variantRmIngredientsHeading" style="display:none;">Ingredients &amp; batch selection</p>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <table class="table fnt-s" style="margin-top: 10px; border-collapse: separate; border-spacing: 0 5px;">
                    <thead style="position: sticky; top: 0; z-index: 9999 !important; background: white !important; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                        <tr class="text-center" style="background: white !important;">
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">#</th>
                            <th class="text-left" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Ingredient</th>
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Req Qty.</th>
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Avl Qty.</th>
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Batch No.</th>
                            <th style="background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Expiry Date</th>
                            <th class="text-left wastage-th" style="text-align:left; background: white !important; padding: 12px 8px; min-height: 48px; color: #333;">Wastage</th>
                        </tr>
                    </thead>
                    <tbody id="variantRmConsumptionTableBody"></tbody>
                </table>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; padding: 12px 20px;">
                <button type="button" class="btn btn-secondary custom-bdsetting" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="variantRmConsumptionSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
    function order_kot() {
        $('#orderdetailtitle').html('KOT Details');
        $.ajax({
            type: "GET",
            url: '<?= site_url("Production_Unit/kot") ?>',
            beforeSend: function() {
                $("#model_body").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
            },
            success: function(data) {
                var printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write('<html><head><title>KOT</title>');
                printWindow.document.write('<style>table{border-collapse: collapse; width: 100%;} th, td{border:1px solid #333; padding:5px; text-align:center;}</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write(data);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close(); // optionally close after print
            },
            error: function() {
                $("#model_body").html("<div class='alert alert-danger'>Ajax Error</div>");
            }
        });
    }
</script>
<script>
    var products = <?php echo json_encode($products); ?>;
    var productionUnitId = <?php echo json_encode($productionUnitId); ?>;
    console.log(products);
    var productionUnits = <?php echo json_encode($productionUnitName); ?>; // for currently view orders
    var productionOrderAgeFormat = <?php echo json_encode($Settings->production_order_age); ?>;

    // Expose quantity/number decimal settings to JS (for quantityDecimal formatter)
    var site = {
        settings: {
            qty_decimals: <?= (int) $Settings->qty_decimals; ?>,
            decimals: <?= (int) $Settings->decimals; ?>
        }
    };
</script>
<script>
    function printModalContent() {
        // Sync input values
        var qtyInput = document.getElementById('builds_Quantity');
        qtyInput.setAttribute('value', qtyInput.value);

        // ✅ Ensure all checkboxes reflect their checked state in HTML
        document.querySelectorAll('#ingredientsModal input[type="checkbox"]').forEach(cb => {
            if (cb.checked) {
                cb.setAttribute('checked', 'checked');
            } else {
                cb.removeAttribute('checked');
            }
        });

        // Now get updated HTML
        var modalContent = document.querySelector('#ingredientsModal .modal-content').innerHTML;

        // Open print window
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Ingredients</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">');
        printWindow.document.write('<style>body{ padding:20px; } .no-print, .modal-footer, .set-to-close { display: none !important; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="container">');
        printWindow.document.write(modalContent);
        printWindow.document.write('</div></body></html>');

        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    function printVariantIngredientsModalContent() {
        var qtyInput = document.getElementById('variant_builds_Quantity');
        if (qtyInput) {
            qtyInput.setAttribute('value', qtyInput.value);
        }
        var modalContent = document.querySelector('#variantIngredientsModal .modal-content').innerHTML;
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Variant Ingredients</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">');
        printWindow.document.write('<style>body{ padding:20px; } .no-print, .modal-footer, .set-to-close { display: none !important; }</style>');
        printWindow.document.write('</head><body><div class="container">');
        printWindow.document.write(modalContent);
        printWindow.document.write('</div></body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function () {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    const buildsqtyInput = document.getElementById('builds_Quantity');

    // Prevent typing minus sign
    buildsqtyInput.addEventListener('keydown', function (e) {
        if (e.key === '-' || e.key === 'Subtract') {
            e.preventDefault();
        }
    });

    // Also prevent paste of negative values
    buildsqtyInput.addEventListener('input', function () {
        if (this.value < 0) {
            this.value = 0;
        }
    });

    var variantBuildsQtyInput = document.getElementById('variant_builds_Quantity');
    if (variantBuildsQtyInput) {
        variantBuildsQtyInput.addEventListener('keydown', function (e) {
            if (e.key === '-' || e.key === 'Subtract') {
                e.preventDefault();
            }
        });
        variantBuildsQtyInput.addEventListener('input', function () {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    }
</script>