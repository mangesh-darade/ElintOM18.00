<!-- <link href="<?= $assets ?>production_unit/css/style.css" rel="stylesheet" /> -->
<link href="<?= $assets ?>production_unit/css/production_dashboard.css" rel="stylesheet" />
<link href="<?= $assets ?>styles/style.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

<script type="text/javascript" src="<?= $assets ?>production_unit/js/production_dashboard.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>


<!-- jQuery library -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- jQuery UI library -->
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>


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
                    <input type="text" placeholder="Search Warehouses" name="query" class="search-box">
                    <button type="submit" class="search-button"><i class="fa fa-search"></i></button>
                    <!-- </form> -->
                </div>

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
                    <!-- <div class="ml-auto mr-2">
                            <div id="iconWithTime1">
                                <i class="fa fa-clock"></i>
                                <span id="currentTime1"></span>
                            </div>
                        </div> -->
                    </div>


                    <div id="cards-section">

                        <!-- <div>
                            <div class="card-div">
                                <div class="table-content">
                                    <div class="${color}-card col">
                                        <p class="p-0 mb-1 item white-text">${arr[i].name}</p>
                                        <div class="table-div">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="text-center">Stock</th>
                                                        <th scope="col" class="text-center">Order Quantity</th>
                                                        <th scope="col" class="text-center">Build</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>${stock_quantity}</td>
                                                        <td>${order_quantity}</td>
                                                        <td>${build_quantity}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
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
                                        <button class="button-set1" id="hideTable">Close</button>
                                        <button class="button-set1" data-toggle="modal"
                                            data-target=".bd-example-modal-lg">View
                                            All Batches</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="col-md-12">
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
                    </div> -->


                </div>
            </div>

        </div>

    </div>
</section>


<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="batches" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
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
                            <th scope="col">Qty</th>
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
            <div class="modal-header">
                <!-- <h5 class="modal-title" id="exampleModalLabel1">Modal title</h5> -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-md-7 pd-lf">
                        <div class="row display-set mrgn-tp">
                            <div class="col-md-6">
                                <h2>Batch Quantity</h2>
                            </div>
                            <div class="col-md-6">
                                <input type="numeric" name="quantity[]" id="allotbatchqty" class="allot-set"
                                    placeholder="0" min="1" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
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
                                <h3><b>Unit: <br><span id="unit"></span></b></h3>
                            </div>
                            <div class="col-md-6">
                                <h3><b>Price: <br>Rs :<span id="price"></span></b></h3>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-5 text-center">
                        <button id="addBatch" class="set-box addBatch">

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

<div class="modal fade" id="reset" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="display-seeting">
                    <span class="mrg-right"><img src="<?php echo base_url('assets/images/warning.svg'); ?>"
                            alt="Your SVG Image"></span>
                    <h5 class="modal-title" id="exampleModalLongTitle">Reset Stock</h5>
                </div>

                <button type="button" class="set-close close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h2>Are you sure you want to Reset Stock for Item: <span id="productName"></span>?
                </h2>
                <div class="align-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmResetStock">Confirm</button>
                    <!-- <button type="button" class="btn btn-primary" id="confirmResetStock" onclick="$('#reset').modal('hide');">Confirm</button> -->

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="recentBatchModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalShowBatches"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <!-- style="display: none;" -->
                <div class="row set-mtmb" id="mytable">
                    <div class="col-md-12">
                        <div class="row disp-setting">

                            <div class="text-center col-md-12 mrgn-tt">
                                <button class="w-100" data-toggle="modal" data-target="#batchModal" id="addBatchModal">
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
                                    <tbody class="" id="latestbatchestablebodyProduction">
                                    </tbody>
                                </table>

                                <div class="modal-footer">
                                    <div class="d-flex justify mrgn-bt">
                                        <button class="button-set1" id="hideTable" data-dismiss="modal">Close</button>
                                        <button class="button-set1" data-toggle="modal"
                                            data-target=".bd-example-modal-lg">View
                                            All Batches</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<script>
   function order_kot() {
    $('#orderdetailtitle').html('KOT Details');
    $.ajax({
            type: "GET",
            data: {
                productionUnits: productionUnitId // This line sends your variable
            },
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
// var products = <?php echo json_encode($products); ?>;
// var productionUnitId = <?php echo json_encode($productionUnitId); ?>;
// console.log("productsproducts",products);
// var productionUnits = <?php echo json_encode($productionUnitName); ?>; // for currently view orders
// var productionOrderAgeFormat = <?php echo json_encode($Settings->production_order_age); ?>;


    var productionUnitId = <?php echo json_encode($productionUnitId); ?>;
    var products = <?php echo json_encode($products); ?>;
    var warehousesWithProductsDetails = <?php echo json_encode($warehousesWithProductsDetails); ?>;
    var productionUnits = <?php echo json_encode($productionUnitName); ?>; // for currently view orders
    var productionOrderAgeFormat = <?php echo json_encode($Settings->production_order_age); ?>;
</script>