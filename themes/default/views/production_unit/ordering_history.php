<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <!-- <link href="<?= $assets ?>production_unit/css/style.css" rel="stylesheet" /> -->
    <link href="<?= $assets ?>production_unit/css/procurment_orders.css" rel="stylesheet" />
    <script type="text/javascript" src="<?= $assets ?>production_unit/js/ordering_history.js"></script>
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
    .tracking-cell .tracking-number {
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.2s;
        position: absolute;
        background: #fff;
        color: #222;
        padding: 5px 14px;
        border-radius: 4px;
        top: 10%;
        left: 0;
        white-space: nowrap;
        z-index: 999;
    }

    .tracking-cell:hover .tracking-number {
        visibility: visible;
        opacity: 1;
    }

   .tracking-cell {
    position: relative;
    }
    .right_section{
        display: none !important;
    }

    .popup {
        position: absolute;
        top: -30px; /* Adjust as needed */
        left: 0;
        background: #fff;
        border: 1px solid #ccc;
        padding: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        display: none;
        z-index: 1000;
    }

    .popup-content {
        display: block;
        margin-bottom: 5px;
    }

    .copy-button {
        background: #007bff;
        color: white;
        border: none;
        padding: 3px 6px;
        cursor: pointer;
    }

    .copy-button:hover {
        background: #0056b3;
    }


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
    display: inline-block; /* Ensures the span takes up space */
    vertical-align: middle; /* Aligns vertically with adjacent elements */
    margin-right: 10px; /* Adjust spacing between elements */
}
.subCatName {
    display: inline-block; /* Ensures the span takes up space */
    vertical-align: middle; /* Aligns vertically with adjacent elements */
    margin-right: 10px; /* Adjust spacing between elements */
}
.fa-hand-o-left:before {
    content: "\f0a5";
    font-size: 3rem;
}
.font-weight-right {
        display: flex;            /* Use flexbox for positioning */
        justify-content: center;  /* Center content horizontally */
        align-items: center;      /* Center content vertically */
    }

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    /* padding: 8px 12px; */
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f2f2f2;
}

.tracking-cell {
    position: relative;
}

.popup {
    display: inline-block;
    position: absolute;
    left: 0;
    top: 0rem;
    background-color: #fff;
    border: 1px solid #ddd;
    padding: 5px;
    display: none;
}

.tracking-cell:hover .popup {
    display: block;
}

.copy-button {
    margin-top: 5px;
    background-color: #007bff;
    color: white;
    padding: 2px 8px;
    border: none;
    cursor: pointer;
}
    </style>
</head>

<body>
    <section id="procurement_order">
        <div class="container-fluid">
            <div class="row">
                <!-- <div class="wd-set p-0 bg-setting"  > -->
                <div class="wd-set p-0 bg-setting" style="display: none;" >

                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-barcode icon-th"></i><span class="break"></span>Orders
                        </h2>
                    </div>

                    <div class="mrgn-tp">
                        <ul class="nav nav-tabs nav-justified m-4 nav-set" role="tablist">
                            <li class="nav-item p-0 mt-1 active" id="activeStatus">
                                <a class="nav-link bg-set status" data-toggle="tab" href="" value="current_order"
                                    role="tab">Current Order</a>
                            </li>
                            <li class="nav-item p-0 mt-1">
                                <a class="nav-link bg-set status" id ="Open" data-toggle="tab" href="" value="Open" role="tab">Open
                                    Orders</a>
                            </li>
                            <li class="nav-item p-0 mt-1" id ="partially">
                                <a class="nav-link bg-set status" data-toggle="tab" href="" value="partially"
                                    role="tab">Partial Orders</a>
                            </li>
                            <li class="nav-item p-0 mt-1" id="previous_order">
                                <a class="nav-link bg-set status" data-toggle="tab" href="" value="previous_order"
                                    role="tab">Previous Orders</a>
                            </li>
                            <li class="nav-item p-0 mt-1" id="Received">
                                <a class="nav-link bg-set status" data-toggle="tab" href="" value="Received"
                                    role="tab">Receive Orders</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="hundred-percent sty-bg-set">

                    <div class="hide-div">
                        <div class="d-flex align-items-center set-bg-8">
                            <h3 class="m-0_10 font-weight mt-set">
                                Request a Delivery Date :
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
                                        <option value="<?php echo $location->name; ?>"><?php echo $location->name; ?></option>
                                    <?php endforeach; ?>
                                </select>

                               </div>
                        </div>
                    </div>


                    <div class="show-div clickMe" style="display: none;">
                        <div class="d-flex align-items-center set-bg-8">
                            <div class="d-flex align-items-center">
                            <i class="fa fa-hand-o-left" id="clickMe"></i>
                                    <h3 class="m-0_10 text-left" id="order_status"></h3>
                                    <h3 class="m-0_10 text-left orderNo" style="display: none;"></h3>
                            </div>

                            <div class="w-set1 ml-auto">
                                <h4>
                                </h4>
                            </div>
                        </div>
                    </div>


                    <!-- <div class="d-flex align-items-center show set-bg-8">
                        <h3 class="m-0_10 font-weight mt-set">
                           Open Orders
                        </h3>
                        <div class="w-set1 ml-auto">
                            <h4>
                                <i class="fa fa-people"> </i> <span id="userName"><?php echo $user_name; ?></span>
                            </h4>
                        </div>
                    </div> -->


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
                                <button class="btn repeat_order" id="repeat_order" style="border: 2px solid #039be5;"
                                    data-id="" value="current_order" type="submit">Repeat Order</button>
                                <div class="col-md-12 mb-3" id="editorRow" style="display: none;">
                                    <div>
                                        <!-- <textarea id="editor"></textarea> -->
                                        <?php echo form_textarea('note', '', 'class="form-control" id="editor" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>
                                <button class="btn place_order" id="place_order"
                                    style="display: none; border: 2px solid #039be5; margin:0.8rem;" value="place_order"
                                    type="submit">Place Order
                                </button>
                                <button class="btn update_order TabClick" id="update_order"
                                    style="display: none; border: 2px solid #039be5; margin:0.8rem;" data-id="" value="update_order"
                                    type="submit">Update Order</button>
                                <button class="btn add_note" id="add_note"
                                    style="display: none; border: 2px solid #039be5; margin:0.8rem;" value="place_order"
                                    type="submit">Add Note
                                </button>
                                <button class="btn update_order TabClick" id="received_order"
                                    style="display: none; border: 2px solid #039be5; margin:0.8rem;" data-id="" value="received_order"
                                    type="submit">Order Received
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wd-set bg-setting right_section" style="display: none;">
                    <div class="search-container">
                            <input type="text" placeholder="Search..." name="query" class="search-box">
                            <button type="" class="search-button"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="row mrgn-tp padding-set">
                        <ul class="menu-list recent" style="display: none;">
                            <li class="btn set-bdr-none">
                            <span class="font-weight">
                                <img id="" class="mr-1 hand-o-left" style="margin-right: 7px; " src="<?= base_url() ?>/themes/default/assets/production_unit/images/Back Arrow.svg" />
                            </span>
                            <span class="catName" style="background-color: #009DFF; padding: 5px 2rem; color: white; border-radius:0.3rem; width:100%;"></span>
                            <span class="font-weight-right">
                                <img id="" class="mr-1" style="margin-right: 7px; " src="<?= base_url() ?>/themes/default/assets/production_unit/images/Right Arrow.svg" />
                            </span>
                            <span class="subCatName"  style="padding: 5px;"></span>

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
            </div>
        </div>
    </section>
</body>
<script>
var categories = <?php echo json_encode($categories); ?>;
console.log(categories)
</script>

</html>