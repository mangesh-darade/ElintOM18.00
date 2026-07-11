<meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">
<!-- <link href="<?= $assets ?>production_unit/css/style1.css" rel="stylesheet" /> -->
<link href="<?= $assets ?>production_unit/css/order_dispatch.css" rel="stylesheet" />

<link href="<?= $assets ?>styles/style.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

<script type="text/javascript" src="<?= $assets ?>production_unit/js/order_dispatch.js"></script>
<!-- <script type="text/javascript" src="<?= $assets ?>production_unit/js/production.js"></script> -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

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
                        <h2 class="blue"><i class="fa-fw fa fa-barcode icon-th"></i><span class="break"></span>
                        Ready To Dispatch</h2>
                    </div>

                    <div class="dropdown" id="drop">
                        <button class="btn btn-default dropdown-toggle" type="button" id="menu1" data-toggle="dropdown">
                            Orders Ready To Dispatch
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
                    action="<?php echo base_url('production_unit/Ready_To_Dispatch'); ?>" method="GET">
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

                    <!-- <div class="ml-3 ml-auto text-center">
                        <label class="switch">
                            <input type="checkbox" onclick="toggleDiv()" id="toggleSwitch">
                            <input type="checkbox" id="toggleSwitch" class="toggle-switch">

                            <span class="slider"></span>
                        </label>
                        <h5 class="m-0">View Full Order</h5>
                    </div> -->

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
                                <p class="mb-0 text-center font-set">Order Age</p>
                            </div>

                            <div class="box border p-3 mt-2 d-flx align-items-center" id="timeToDeliveryContainer">
                                <div id="timeInputContainer" style="display:none;">
                                    <input type="time" id="deliveryTimeInput">
                                    <button class="btn-set1" onclick="saveDeliveryTime()">Save</button>
                                    <button class="btn-set1" onclick="cancelSetDeliveryTime()">Cancel</button>
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
                                    <p class="mb-0 text-center font-set">Time to Delivery</p>
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
                <?= form_open('Production_Unit/insert_data', ['enctype' => 'multipart/form-data']); ?>
                <div class="col-md-12 brd-btm">
                    <div class="row">
                        <div class="col-md-12 margin-tp">
                            <!-- Hidden input for procurement_order_ref_no -->
                            <input type="hidden" name="procurement_order_ref_no"
                                value="<?= $procurement_order_ref_no ?>">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="Courier"><?= 'Courier'; ?> *</label>
                                    <div class="controls">
                                        <select id="Courier" class="form-control" name="Courier">
                                            <option value="0" <?= ($Settings->Courier == '0' ? 'selected' : '') ?>>DTDC
                                            </option>
                                            <option value="1" <?= ($Settings->Courier == '1' ? 'selected' : '') ?>>FedEx
                                            </option>
                                            <option value="2" <?= ($Settings->Courier == '2' ? 'selected' : '') ?>>DHL
                                            </option>
                                            <option value="3" <?= ($Settings->Courier == '3' ? 'selected' : '') ?>>
                                                Aramex</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <input type="Hidden" name="refNumber" id="refNumber" value="">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="Tracking_Number"><?= 'Tracking Number'; ?></label>
                                    <div class="controls">
                                        <?= form_input('Tracking_Number', $Settings->Tracking_Number, 'class="form-control tip" id="Tracking_Number" maxlength="30"'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="attachment"><?= 'Attachment'; ?></label>
                                    <div class="controls">
                                        <?= form_upload('attachment', '', 'class="form-control" type="file" id="attachment"'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="Picked_Up_by"><?= 'Picked Up by'; ?></label>
                                    <div class="controls">
                                        <?= form_input('Picked_Up_by', $Settings->Picked_Up_by, 'class="form-control tip" id="Picked_Up_by" maxlength="30"'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="Notes"><?= 'Notes'; ?></label>
                                    <div class="controls">
                                        <?= form_textarea([
                            'name' => 'Notes',
                            'id' => 'Notes',
                            'class' => 'form-control tip',
                            'maxlength' => '500',
                            'rows' => '5',
                            'value' => $Settings->Notes
                        ]); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-start">
                                    <h3 class="mb0"><b>Delivery Address</b></h3>
                                    <h4><b>To :</b></h4>
                                        <span id="userAddress"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex">
                                <div class="m-4 col-md-3">
                                    <button type="button" id="printLabel" class="sty-set">Print Label</button>
                                </div>
                                <div class="m-4 col-md-3">
                                    <button type="submit" id="dispatchOrder" name="dispatchOrder"
                                        class="sty-set">Dispatch Order</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>

</section>
<script>
var production_order_age = <?php echo json_encode($Settings->production_order_age);  ?>;
</script>