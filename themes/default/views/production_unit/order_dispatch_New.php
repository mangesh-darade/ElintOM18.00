<meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">
<!-- <link href="<?= $assets ?>production_unit/css/style1.css" rel="stylesheet" /> -->
<link href="<?= $assets ?>production_unit/css/order_dispatch_new.css" rel="stylesheet" />

<link href="<?= $assets ?>styles/style.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

<script type="text/javascript" src="<?= $assets ?>production_unit/js/production_new.js"></script>
<!-- <script type="text/javascript" src="<?= $assets ?>production_unit/js/production.js"></script> -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<style>
    
  .circle-set { border-radius: 0 !important; }
    </style>
<section id="production_unit">
    <div class="container-fluid">
        <div class="row brd-set disp-flx">

            <div class="width-25 p-0">
                <!-- <div class="d-flx align-items-center margin-tp">
                    <h3 class="">
                        <i class="fa-fw fa fa-heart"></i><span class="ml-3">Procurement Order</span>
                    </h3>
                </div> -->
                <div class="brd-btm">
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-barcode icon-th"></i><span
                                class="break"></span>Working Orders</h2>
                    </div>

                    <div class="dropdown" id="drop">
                        <button class="btn btn-default dropdown-toggle" type="button" id="menu1"
                            data-toggle="dropdown">All
                            Working Orders
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                            <li role="presentation" id="filterOutlets">
                                <a role="menuitem" tabindex="-1">Filter by
                                    Outlets</a>
                            </li>
                            <li role="presentation" class="some-other-action">
                                <a role="menuitem" tabindex="-1" id="filterByTimestamp">Sort by Timestamp</a>
                            </li>
                        </ul>
                    </div>

                    <ul class="checkbox-list" id="timestampCheckboxList" style="display:none;">
                        <li><input type="checkbox" id="Oldest"><label for="Newest" class="check-set"> Oldest
                                First</label></li>
                        <li><input type="checkbox" id="Newest"><label for="Newest" class="check-set"> Newest
                                First</label></li>
                    </ul>

                    <!-- <form class="example" id="searchForm" style="display: none;"
                    action="<?php echo base_url('production_unit/order_dispatch'); ?>" method="GET">
                    <input type="text" placeholder="Search.." id="searchInput" name="search" class="search"
                        value="<?php echo $this->input->get('search'); ?>">
                    <button type="submit" class="brd-gray"><i class="fa fa-search"></i></button>
                </form> -->

                    <div id="searchForm" style="display: none; ">
                        <?= lang('', 'searchInput') ?>
                        <?php
                    $tr[""] = "";
                    foreach ($locationNames as $locationName) {
                        $tr[$locationName->id] = $locationName->code. '-' . $locationName->name;
                    }
                    echo form_dropdown('locationName',  $tr, '', 'class="form-control" id="searchInput" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("Location Name") . '"');
                    ?>
                    </div>

                    <!-- Suggestions dropdown -->
                    <div id="suggestionsDropdown" style="display: none;"></div>
                    <div class="mt-3 mb-3"></div>

                    <div class="menu brd-btm">
                        <ul class="mainmenu"></ul>
                    </div>
                </div>
            </div>
            <div class="width-75 brd-left height-set">
                <div class="row align-items-center brd-btm d-flx settings">

                    <div class="text-start ml-3">
                        <h3 class="ml-3">Ordering Outlet:<br><b><span id="locationName"></span></b></h3>
                    </div>
                    <div class="text-start ml-3">
                        <h3 class="ml-3">Items for:<br><b><span id="productionUnit"></span></b></h3>
                    </div>

                    <div class="ml-3 ml-auto text-center">
                        <label class="switch">
                            <!-- <input type="checkbox" onclick="toggleDiv()" id="toggleSwitch"> -->
                            <input type="checkbox" id="toggleSwitch" class="toggle-switch">

                            <span class="slider"></span>
                        </label>
                        <h5 class="m-0">View Full Order</h5>
                    </div>

                    <div class="ml-auto">
                        <!-- <h2 class="">Request</h2> -->
                        <div class="d-flx">
                            <div class="box border p-3 mt-2">
                                <div id="iconWithTime1">
                                    <i class="fa fa-clock"></i>
                                    <span id="currentTimes1"></span>
                                </div>
                                <div class="format-setting">
                                    <?= $Settings->production_order_age == 1? "DD  : HH  : MM  : SS" : "HH : MM : SS" ; ?>
                                </div>
                                <div class="border-bot"></div>
                                <p class="mb-0 text-center">Order Age</p>
                            </div>

                            <div class="box border p-3 mt-2 d-flx align-items-center" id="timeToDeliveryContainer">
                                <div id="timeInputContainer" style="display:none;">
                                    <div class="d-flex">
                                        <input type="time" id="deliveryTimeInput">
                                        <button class="btn-set1" onclick="saveDeliveryTime()">Save</button>
                                        <button class="btn-set1" onclick="cancelSetDeliveryTime()">Cancel</button>
                                    </div>
                                </div>
                                <div id="timeDisplayContainer">
                                    <div id="iconWithTime2">
                                        <i class="fa fa-clock"></i>
                                        <span id="currentTime2">00:00:00</span>
                                    </div>
                                    <div class="">
                                        <span class="format-setting">HH : MM : SS</span>
                                    </div>
                                    <div class="border-bot"></div>
                                    <p class="mb-0 text-center">Time to Delivery</p>
                                </div>
                                <button class="btn-set1" id="setButton" onclick="toggleDeliveryTimeInput()">Set</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="d-flex justify-content-end">
                    <div class="btn">
                        <button class="sty-set text-end locked" id="locked">
                            <span class="button-text"> Locked</span>
                        </button>
                    </div>
                </div> -->


                <div class="col-md-12">
                    <div class="row set-alert" id="welcomeDiv100" style="display: none;">
                        <div class="fa fa-file-text-o set-fnt"> Notes
                            <span class="col-md-12" id="noteContent"></span>
                        </div>
                        <div class="close-btn" onclick="hideDiv()">
                            <i class="fa fa-times"></i>
                        </div>
                    </div>
                </div>

                <div class="border-setbox">
                    <div class="col-md-12 brd-btm">
                        <div class="row d-flx" id="welcomeDiv">
                            <div class="text-start ml-3">
                                <h3 class="ml-3">Order Status:<br><b><span id="order_status"></span></b></h3>
                            </div>
                            <div class="ml-auto">
                                <button class="sty-set text-end" id="locked">
                                    <span class="button-text">Lock Order</span>
                                </button>
                            </div>
                        </div>


                        <ul class="nav nav-tabs nav-justified m-4" role="tablist">
                            <li class="nav-item-all active p-0">
                                <a class="nav-link  bg-set set-brd-top-cornor" id="status" values="" data-toggle="tab"
                                    href="#tabs-1" role="tab">All
                                    Items</a>
                            </li>
                            <li class="nav-item p-0">
                                <a class="nav-link bg-set" id="status" values="Completed" data-toggle="tab"
                                    href="Completed" role="tab">Completed
                                    Items</a>
                            </li>
                            <li class="nav-item p-0">
                                <a class="nav-link bg-set" id="status" values="Partially Completed" data-toggle="tab"
                                    href="Partially Completed" role="tab">Partially
                                    Completed Items</a>
                            </li>
                            <li class="nav-item p-0">
                                <a class="nav-link bg-set set-brd-right-cornor" id="status" values="Pending"
                                    data-toggle="tab" href="Pending" role="tab">Pending
                                    Items</a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3 overflowset">
                            <div class="tab-pane fade in active" id="tabs-1" role="tabpanel">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="bg-black header-cell" cellspacing="1">Order</th>
                                            <th scope="col header-cell"></th>
                                            <th scope="col" class="bg-black header-cell" cellspacing="1">Stock</th>
                                            <th scope="col" class="bg-black header-cell top-radius" cellspacing="1">
                                                Allot <span class="ml-2">
                                                    <input type="checkbox" class="large-checkbox" name="quantity[]"
                                                        id="selectAll" cellspacing="1"></span></th>
                                            <!-- <th scope="col" class="bg-black header-cell" cellspacing="1">build</th> -->
                                        </tr>
                                    </thead>
                                    <tbody id="tablebody">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row justify-content-center p-3">
                            <div class="d-flex">
                                <div class="m-4">
                                    <button id="completeOrder" data-toggle="modal" data-target="#complete_order"
                                        class="sty-set">Complete Unit Order</button>
                                </div>
                                <!-- <div class="m-4">
                            <button class="sty-set">Partially Fulfil</button>
                        </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
<script>
var production_order_age = <?php echo json_encode($Settings->production_order_age);  ?>;
var site = { settings: { qty_decimals: <?= (int) $Settings->qty_decimals; ?>, decimals: <?= (int) $Settings->decimals; ?> } };
</script>
<script>
function generateKOT(orderId) {
    $('#orderdetailtitle').html('KOT Details');
    var isToggleOn = $('.toggle-switch').is(':checked') ? 1 : 0;
    $.ajax({
        type: "GET",
        url: '<?= site_url("Production_Unit/kot_by_order/") ?>' + orderId + '?isToggleOn=' + isToggleOn, // Ensure orderId is appended
        beforeSend: function () {
            $("#model_body").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
        },
        success: function (data) {
            var printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>KOT</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 14px; }');
            printWindow.document.write('table { border-collapse: collapse; width: 100%; }');
            printWindow.document.write('th, td { border: 1px solid #333; padding: 5px; text-align: center; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(data);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            // Optionally auto-close after printing
            printWindow.close();
        },
        error: function () {
            $("#model_body").html("<div class='alert alert-danger'>Ajax Error</div>");
        }
    });
}
</script>
</script>
<!-- <script>
    var kotIconUrl = "<?= $assets ?>production_unit/images/KOTIcon.svg";
    // console.log("ameya:", baseUrl);
</script> -->
<script>
    var kotIconBlue = "<?= $assets ?>production_unit/images/KOTIcon.svg";
    var kotIconBlack = "<?= $assets ?>production_unit/images/KOTIconBlack.svg";
</script>
