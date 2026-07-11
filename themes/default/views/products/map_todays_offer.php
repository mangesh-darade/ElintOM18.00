

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link href="<?= $assets ?>production_unit/css/style.css" rel="stylesheet" /> -->
    <link href="<?= $assets ?>production_unit/css/procurment_orders.css" rel="stylesheet" />
    <script>
    var csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
    var csrfHash = "<?= $this->security->get_csrf_hash(); ?>";
</script>
    <script type="text/javascript" src="<?= $assets ?>js/todays_special.js"></script>
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
        display: inline-block;
        /* Ensures the span takes up space */
        vertical-align: middle;
        /* Aligns vertically with adjacent elements */
        margin-right: 10px;
        /* Adjust spacing between elements */
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

    </style>
</head>

<body>
    <section id="procurement_order">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 p-0">
                    <div class="wd-set p-0 bg-setting col-md-2 height-setting">
                        <div class="box-header">
                            <h2 class="blue"><i class="fa-fw fa fa-barcode icon-th"></i><span
                                    class="break"></span>Menu
                            </h2>
                        </div>

                        <div class="mrgn-tp">
                            <ul class="nav nav-tabs nav-justified m-4 nav-set" role="tablist">
                                <li class="nav-item p-0 mt-1 active" id="activeStatus">
                                    <a class="nav-link bg-set status" data-toggle="tab" href="" value="add_items"
                                        role="tab">Add item</a>
                                </li>
                                  <li class="nav-item p-0 mt-1" id="previous_order">
                                    <a class="nav-link bg-set status" data-toggle="tab" href="" value="previous_order"
                                        role="tab">Previous item</a>
                                </li>
                                <li class="nav-item p-0 mt-1" id="date_wise_item">
                                    <a class="nav-link bg-set status" data-toggle="tab" href="" value="date_wise_item"
                                        role="tab">Date Wise Item</a>
                                </li>
                              
                            </ul>
                        </div>
                    </div>
                    <div class="sixty-eight-percent sty-bg-set col-md-8">

                        
                        <div class="hide-div">
                            <div class="d-flex align-items-center set-bg-8">
                                <h3 class="m-0_10 font-weight mt-set">
                                    Offers Date :
                                </h3>
                                <form>
                                    <div class="input-group w-set" style="min-width: 145px;">
                                        <input type="text" id="requestDeliveryDate" name="requestDeliveryDate"
                                            class="form-control" placeholder="Select a date">
                                        <span class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </span>
                                    </div>
                                </form>
									<div class="input-group w-set" style="width: 380px; margin-left: -15px;">
										<input type="text" id="title" name="title" class="form-control" placeholder="Enter Todays Special Title">
									</div>
									<div class="" style="width: 280px; margin-left: 25px;">
										<button class="reset-btn" id="reset">Reset</button>
									</div>

                            </div>
                        </div>
                        
                        <div class="tab-content overflowset">
                            <div class="tab-pane fade in active text-center" role="tabpanel">
                                <!-- <h3 class="m-0_10 text-left" id="order_status"></h3> -->

                                <div class="col-md-12 p-0">

                                    <table class="table table-bordered rounded-table text-center" id="dynamicTable">
                                        <thead>
                                        </thead>
                                        <tbody class="text-center">
                                        </tbody>
                                        <tfoot>
                                        </tfoot>
                                    </table>

                                    <div class="center-btn">
                                        <button class="btn repeat_order" id="repeat_order"
                                            style="box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1); background: #039be5; color: #fff;"
                                            data-id="" value="current_order" type="submit">Repeat Order</button>
                                    </div>

                                    <div class="col-md-12 mb-3" id="editorRow" style="display: none;">
                                        <div>
                                            <!-- <textarea id="editor"></textarea> -->
                                            <?php echo form_textarea('note', '', 'class="form-control col-md-12" id="editor" style="margin-top: 10px; height: 100px;"'); ?>

                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <button class="btn place_order col-md-3" id="place_order" style="display: none; margin:0.8rem; background: #039be5; color: #fff; box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);
                                           outline: none;" value="place_order" type="submit">Save
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wd-set bg-setting right_section col-md-2" style="display: none;">
                        <div class="search-container">
                            <input type="text" placeholder="Search..." name="query" class="search-box">
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