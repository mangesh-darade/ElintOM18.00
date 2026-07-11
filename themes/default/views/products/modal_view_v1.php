<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .bcimg {
        height: 50px;
    }

    .color-black {
        color: black;
    }

    .text-center {
        text-align: center;
    }

    .img-fld {
        /* width: 100%; */
        /* margin-right: 1rem;
        height: 50px; */
        aspect-ratio: 1 / 0.7;
        margin: 0% !important;
        border: none !important;
    }

    .product-details {
        /* display: flex; */
        flex-wrap: wrap;
        gap: 20px;
        padding: auto;
        background-color: #f9f9f9;
        border-radius: 10px;
        /* box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1); */
        margin-top: 2rem;
        background-color: white;
    }
    @media screen and (max-width: 1024px) {
        .product-details {
        display: flex;
        flex-direction: column;
    }
    .modal-content{
        width: 95%;
        margin-left: 9px;
    }
    .d-flx{
        display: flex;
        flex-wrap: wrap;
    }
    }

    .product-details-inner {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .product-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        /* width: calc(25% - 10px); */
        background-color: #fff;
        /* padding: 5px 1.2rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin: 0.5rem;
        min-height: 2rem; */
        color: black;
        background-color: white;
    }


    .product-label {
        font-weight: bold;
        color: #333;
        flex-basis: 80%;
        padding: 0rem;
        font-size: 1.3rem;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex-basis: 25%;
        text-align: start;
    }

    .product-content {
        color: #666;
        flex-basis: 100%;
        text-align: start;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 150px;
    }

    .product-content1 {
        color: #666;
        flex-basis: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: flex;
        flex-wrap: wrap;
        margin: 0rem;
    }

    .product-content span.label-primary {
        background-color: #428bca;
        color: #fff;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .qrimg {
        width: 50px !important;
    }

    .barcode-qrcode-item.content {
        width: 10% !important;
    }

    @media (max-width: 768px) {
        .product-row {
            width: 100%;
        }
    }

    .display-flx {
        display: flex;
        align-items: center;
        justify-content : space-between;
    }

    .display-flx1 {
        display: flex;
        align-items: center;
        justify-content: center;
    }


    .custom-wid {
        /* width: 1015px; */
        width : 80vw;
        height: 80vh; 
    }

    .p0 {
        padding: 0rem;
    }

    .product-row1 {
        /* display: flex;
        justify-content: space-between;
        align-items: center;
        width: calc(80% - 20px);
        background-color: #fff;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin: 0.5rem; */
    }

    .m1 {
        margin: 0.5rem;
    }

    .qrimg {
        width: 50px !important;
    }

    .barcode-qrcode-item.content {
        width: 10% !important;
    }

    .btn-group-justified .btn-group a {
        background: #ffffff !important;
        color: black;
        padding: 0%;
    }

    .btn-group-justified .btn-group a span {
        background: #009DFF;
        color: white;
        width: 100%;
        padding: 3%;
        border-radius: 5px;
    }

    .quantity-cell {
        text-align: left !important;
        background-color: #fff;
        padding: 8px !important;
    }

    .quantity-cell > div {
        white-space: nowrap;
    }

    .qty-highlight {
        float: right;
        margin-left: 10px;
        font-weight: bold;
    }

    .quantity-table thead td {
        background: #009DFF !important;
    }

    .quantity-table thead th {
        background: #009DFF !important;
        color: white;
    }

    .variant-pricing-table td,
    .variant-pricing-table th {
        border: 1px solid #AAA7A7 !important;

    }

    .variant-pricing-table tbody td {
        background-color: #fff !important;
        border: 1px solid #AAA7A7 !important;
    }

    .variant-pricing-table th {
        text-align: center !important;
        background: #009DFF !important;
        color: white;
    }

    .variant-pricing-table td {
        text-align: center !important;
        background-color: #fff;
    }

    .variant-pricing-table thead td {
        background: #009DFF !important;
    }

    .variant-pricing-table thead th {
        background: #009DFF !important;
        color: white;
    }

    .qty-div {
        font-size: 2rem;
        font-weight: bolder;
        /* padding: 2%; */
        border: 2px solid black;
        border-radius: 10px;
        text-align: center;
    }

    .new-img-div {
        width: 100%;
        border-radius: 5px
    }

    /* #myModalLabel {
        text-align: center;
        background-color: #009DFF;
        color: white;
        font-weight: bold;
        border: 1px solid #009DFF;
        border-radius: 10px;
    } */

    .quantity-table {
        border: 1px solid #AAA7A7 !important;
        border-radius: 5px;
    }

    .quantity-table th {
        border: 1px solid #AAA7A7 !important;
    }

    .quantity-table tbody td {
        background-color: white !important;
        border: 1px solid #AAA7A7;

    }

    .product-content2 {
        text-align: start;
    }

    .quantity-table td {
        border: 1px solid #AAA7A7 !important;
    }

    .quantity-table th {
        border: 1px solid #AAA7A7 !important;
    }

    .quantity-table tbody tr:first-child {
        border-radius: 5px !important;
    }

    /* .qty-highlight {
        font-weight: bold;
        color: #000;
    } */

    .btn-group {
        padding: 0% 1% 0% 1%;
    }

    .two-columns thead th {
        background-color: #009DFF !important;
    }

    .two-columns thead tr th:first-child {
        border: 1px solid #AAA7A7 !important;
        border-radius: 5px 0px 0px 0px !important;
    }

    .two-columns thead tr th:last-child {
        border: 1px solid #AAA7A7 !important;
        border-radius: 0px 5px 0px 0px !important;
    }

    .two-columns tbody td {
        border: 1px solid #AAA7A7 !important;
    }

    .two-columns tbody tr:last-child td:first-child {
        border: 1px solid #AAA7A7 !important;
        border-radius: 0px 0px 0px 5px !important;
        width: 20%!important;
    }

    .two-columns tbody tr:last-child td:last-child {
        border: 1px solid #AAA7A7 !important;
        border-radius: 0px 0px 5px 0px !important;
    }

    .two-columns tbody td {
        background-color: #fff !important;
        text-align: center;
        padding: 5px!important
    }

    .two-columns tbody th {
        text-align: center;
        width: 30%;
    }

    /* .pr-image {
        height: 215px;
        width: 148px;
    } */

    .modal-body {
        max-height: none !important;
        /* padding: 10px 30px 0px 0px !important; */
    }

    /* Keep content spacing symmetric inside popup body */
    .modal-body .disp {
        display: block !important;
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .modal-body > .row {
        margin-left: 0;
    }

    .modal-body .row > [class*="col-"] {
        padding-left: 8px !important;
    }

    .cfl{
        font-weight:normal ;
        text-align: left !important;
    }

    .cfl label{
        font-weight:normal ;
        text-align: left !important;
    }

    @media print {

        .b-new-img-div {
            width: 75%;
            display: flex;
        }

        .new-img-div {
            /* aspect-ratio: 1; */
            width: 100%;
        }

        .qty-div {
            width: 100%;
        }

        .b-b-cf-table {
            width: 70% !important;
        }

        .b-cf-table {
            width: 100% !important;
        }

        .cf-table-div {
            width: 100% !important;

        }

        .dfTable {
            /* display: none !important; */
        }

        .dfTable td,
        .dfTable th {
            border: 1px solid black !important;
        }

        .wh-heading {
            margin-top: 2%;
            margin-bottom: 0%;
        }

        .cf-heading {
            margin-top: 5% !important;
            margin-bottom: 0% !important;
        }

        .cf-table-div {
            break-after: page;
        }

        .cf-table-div .cf-table td,
        .cf-table-div .cf-table th {
            border: 1px solid grey !important;
        }

        .wh-div .wh-table {
            font-size: 12px !important;
            margin: 5px 0 !important;
            width: auto !important;
        }
        
        .wh-div .wh-table td,
        .wh-div .wh-table th {
            border: 1px solid grey !important;
            padding: 4px 8px !important;
            line-height: 1.2;
        }
        
        .wh-div .wh-table th {
            white-space: nowrap;
        }
        
        .wh-div .quantity-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .wh-div {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 10px;
        }

        .wh-div .wh-table td,
        .wh-div .wh-table th {
            border: 1px solid grey !important;
        }

        .wh-table thead th {
            text-align: center !important;
        }

        .product-details {
            width: 70% !important;

        }

        .vp-heading {
            margin-top: 5% !important
        }

        .vp-div .vp-table td,
        .vp-div .vp-table th {
            border: 1px solid grey !important;
        }

        #qpv-heading {
            margin-top: 5% !important;
        }

        .vpd-div .variant-pricing-table td,
        .vpd-div .variant-pricing-table th {
            border: 1px solid grey !important;
        }

        /* Hide DataTables sort arrows only in this popup's tables */
        .hide-dt-sort-icons thead .sorting,
        .hide-dt-sort-icons thead .sorting_asc,
        .hide-dt-sort-icons thead .sorting_desc {
            background-image: none !important;
        }

        .hide-dt-sort-icons thead .DataTables_sort_icon {
            display: none !important;
        }

        .hide-dt-sort-icons thead th.sorting:before,
        .hide-dt-sort-icons thead th.sorting:after,
        .hide-dt-sort-icons thead th.sorting_asc:before,
        .hide-dt-sort-icons thead th.sorting_asc:after,
        .hide-dt-sort-icons thead th.sorting_desc:before,
        .hide-dt-sort-icons thead th.sorting_desc:after,
        .hide-dt-sort-icons thead th.sorting_disabled:before,
        .hide-dt-sort-icons thead th.sorting_disabled:after {
            content: none !important;
            display: none !important;
        }

        #multiimages {
            display: flex;
            flex-wrap: wrap;
        }
    }

    .add-fav-product{
        -webkit-text-stroke: 1px black;
        color: transparent; 
    }

    .remove-fav-product{
        color : #bd1919;
    }

    /* Desktop + mobile: remove DataTables sort indicators in product detail popup tables */
    .hide-dt-sort-icons.dataTable thead th,
    .hide-dt-sort-icons thead th {
        background-image: none !important;
    }
    .hide-dt-sort-icons thead .sorting,
    .hide-dt-sort-icons thead .sorting_asc,
    .hide-dt-sort-icons thead .sorting_desc,
    .hide-dt-sort-icons thead .sorting_disabled {
        background-image: none !important;
    }
    .hide-dt-sort-icons thead .DataTables_sort_icon {
        display: none !important;
    }
    .hide-dt-sort-icons thead th.sorting:before,
    .hide-dt-sort-icons thead th.sorting:after,
    .hide-dt-sort-icons thead th.sorting_asc:before,
    .hide-dt-sort-icons thead th.sorting_asc:after,
    .hide-dt-sort-icons thead th.sorting_desc:before,
    .hide-dt-sort-icons thead th.sorting_desc:after,
    .hide-dt-sort-icons thead th.sorting_disabled:before,
    .hide-dt-sort-icons thead th.sorting_disabled:after {
        content: none !important;
        display: none !important;
    }

    #warehouse-quantity-table tbody td:nth-child(2),
    #vendor-quantity-table tbody td:nth-child(2) {
        text-align: right !important;
    }
     #vendor-quantity-table tbody td:nth-child(1) {
        text-align: left !important;
    }
    div.dataTables_paginate {
        float: right !important;
    }
    
    .new-img-div .fa-heart{
        cursor: pointer;
        font-size: 24px;
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .buttons{
        width : 93%;
        display : flex;
        align-items :center;
        justify-content : space-between;
    }

    #name-code{
        width : 40%;
    }

    #header-buttons{
        column-gap: 30px;
        width: 60%;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    #header-buttons-print{
        display : flex;
        align-items : center;
        /* gap : 5px; */
    }

    .edit-button{
        color : white;
        background-color : #009DFF;
        height : 100%;
        width : 100% ; 
        padding : 26% 40%;
        border-radius : 5px;

    }

    #name-code p{
        font-weight : bold;
        font-size : 16px;
        margin-bottom : 0px;
    }

    .buttons .prt-btn{
        background-color : white;
        border: 1px solid black;
        padding : 6% 23%;
        margin-bottom : 0px;
        border-radius : 5px !important;
    }

    .buttons .btn i{
        margin : 0%;
    }

    .kebab-menu-div .fa-ellipsis-v{
        border : 0px;
    }

    .kebab-menu-div .dropdown-menu{
        left: -200% !important;
    }

    .hollow-kebab i{
        display:block;
        line-height:6px !important;
        font-size:6px !important;
    }  

    .hollow-kebab {
        border : none;
        margin : 0% 0% 0% 0% !important;
    }

    .dropdown-menu li a{
        text-align : left !important;
    }
    
    #header-buttons-print .fa-chevron-down{
        margin : 0% 0% 0% 8%;
    }

    .edit-button-div a {
        text-decoration: none;
    }

    #pd-modal .modal-content{
        height : 90vh;
		overflow-y : scroll;
    }

    .popover .arrow{
        display: none;
    }

    .popover{
        top: 0px !important;
    }
</style>
<div class="modal-dialog modal-lg custom-wid pd-modal" id="pd-modal">
    <div class="modal-content">
        <div class="modal-header display-flx">
            <?php if (!$Supplier || !$Customer) { ?>
                <div class="buttons">                    

                    <div id="name-code">
                        <p><?= $product->name ?></p>
                        <p><?= $product->code ?></p>
                    </div>

                    <div id="header-buttons">
                        <div class="edit-button-div">
                            <a href="<?= site_url('products/edit/' . $product->id) ?>" class="eda"
                                title="<?= lang('edit_product') ?>">
                                <span class="edit-button"><?= lang('edit') ?></span>
                            </a>
                        </div>

                        <div id="header-buttons-print" class="">
                            <!-- <label for="print-options">Print</label>
                            <select name="print-options" id="print-options">
                                <option value="barcode-label">Barcode Label</option>
                                <option value="pdf">PDF</option>
                            </select> -->

                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle prt-btn" type="button" data-toggle="dropdown">
                                    Print 
                                    <i class="fa fa-angle-down"></i>
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="<?= site_url('products/print_barcodes/' . $product->id) ?>" id="print-barcode">Barcode Label</a></li>
                                    <li>
                                        <a href="<?= site_url('products/pdf/' . $product->id) ?>" id="print-pdf">PDF</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="dropdown kebab-menu-div">
                            <!-- <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"> -->
                                <!-- Print  -->
                                <!-- <i class="btn btn-default dropdown-toggle fa fa-ellipsis-v" type="button" data-toggle="dropdown" ></i> -->
                            <div class="hollow-kebab btn btn-default dropdown-toggle" type="button"     data-toggle="dropdown">
                                <i class="fa fa-circle"></i>
                                <i class="fa fa-circle"></i>
                                <i class="fa fa-circle"></i>
                            </div>
                            <!-- </button> -->
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="<?= site_url('products/add/' . $product->id) ?>" id="duplicate-product">Duplicate Product</a>
                                </li>
                                <?php if ($GP ? ($GP['products-add_batch'] == '1' ) :                       ($this->Settings->product_batch_setting > 0)) { ?>
                                    <li>
                                        <a href="<?= site_url('products/add_batch?p=' . $product->id)?> " type="button" id="manage-batches" data-toggle="modal" data-target="#myModal">Manage Batches</a>
                                    </li>                                
                                <?php } ?>                                
                                <li>
                                    <!-- <a href="<?= site_url('products/delete/' . $product->id) ?>" id="delete-product">Delete Product</a> -->
                                    <a 
                                        href="#"
                                        class="tip bpo btn" title="<b><?= lang('delete_product') ?></b>"
                                        data-content="<div style='width:150px; class='delete-confirmation-popover'>
                                                        <p><?= lang('r_u_sure') ?></p>
                                                        <a class='btn btn-danger' href='<?= site_url('products/delete/' . $product->id) ?>'><?= lang('i_m_sure') ?></a>
                                                        <button class='btn bpo-close'><?= lang('no') ?></button>
                                                      </div>"
                                        data-html="true"
                                        data-placement="bottom">
                                        <span><?= lang('delete') ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <script type="text/javascript">
                    $(document).ready(function() {
                        $('.tip').tooltip();
                    });
                </script>
            <?php } ?>

            <button type="button" class="close pd-close-btn" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="disp">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-3 b-new-img-div">

                                <div>
                                    <div class="new-img-div">
                                        <?php  $isFavProd = $product->is_featured == 1 ? 'remove-fav-product' : 'add-fav-product';
                                        ?>
                                        <i class="fa fa-heart <?= $isFavProd ?>" data-productId="<?= $product->id ?>" aria-hidden="true"></i>
                                        <img id="pr-image" src="<?= base_url('assets/mdata/' . $this->Customer_assets . '/uploads/' . $product->image) ?>"
                                            alt="<?= $product->name ?>" class="img-responsive img-thumbnail img-fld" />
                                        <!-- <h4 class="modal-title" id="myModalLabel"><?= $product->name . '(' . $product->code . ')' ?></h4> -->
                                    </div>
                                    <div class="qty-div" style="margin-top : 1%">Qty : <?= $product->quantity == 0 ? 0 : $this->sma->formatQuantity($product->quantity) ?></div>
                                </div>

                                <div id="multiimages" class="padding10">
                                    <?php if (!empty($images)) {
                                        // echo '<a class="img-thumbnail change_img" href="' . base_url() . 'assets/uploads/' . $product->image . '" style="margin-right:5px;">
                                        //         <img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" alt="' . $product->image . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" />
                                        //       </a>';
                                        foreach ($images as $ph) {
                                            echo '<div class="gallery-image">
                                                    <a class="img-thumbnail change_img" href="' . base_url() . 'assets/uploads/' . $ph->photo . '" style="margin-right:5px;">
                                                        <img class="img-responsive" src="' . base_url('assets/mdata/' . $this->Customer_assets . '/uploads/' . $ph->photo) . '" alt="' . $ph->photo . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" />
                                                    </a>';
                                            if ($Owner || $Admin || $GP['products-edit']) {
                                                echo '<a href="#" class="delimg" data-item-id="' . $ph->id . '"><i class="fa fa-times"></i></a>';
                                            }
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                    <!-- <h4 class="modal-title" id="myModalLabel"><?= $product->name . '(' . $product->code . ')' ?></h4> -->

                                    <!-- <div class="qty-div">Qty : <?= $this->sma->formatQuantity($product->quantity) ?></div> -->
                                    <!-- <div class="clearfix"></div> -->
                                </div>
                            </div>
                            <div class="col-sm-9 text-center barcodealign">
                                <!-- <div class="col-md-4 text-center">
                                    <div class="display-flx1">
                                        <?= $this->sma->save_barcode($product->code, $product->barcode_symbology, 66, false); ?>
                                        <h3 class="color-black"></h3>
                                    </div>
                                </div>    barcode -->

                                <!-- <div class="col-md-4">
                                <div class="barcode-qrcode-item content">
                                    <?= $this->sma->qrcode('link', urlencode(site_url('products/view/' . $product->id)), 2); ?>
                                </div>
                                <?= $this->sma->save_barcode($product->code, $product->barcode_symbology, 66, false); ?>
                                <h3></h3>
                                </div> if you uncomment this comment the $this->sma->save_barcod line -->


                                <div class="col-sm-12">
                                    <div class="table-responsive">
                                        <div class="product-details ">
                                            <div class="product-details-inner">
                                                <div class="product-row">
                                                    <div class="product-label col-md-6"><?= lang("type"); ?> : </div>
                                                    <div class="product-content col-md-6"><?= lang($product->type); ?></div>
                                                </div>
                                                <!-- <div class="product-row">
                                                    <div class="product-label col-md-6"><?= lang("code"); ?> : </div>
                                                    <div class="product-content col-md-6"><?= $product->code; ?></div>
                                                </div> -->
                                                <div class="product-row">
                                                    <div class="product-label col-md-6"><?= lang("brand"); ?> : </div>
                                                    <div class="product-content col-md-6"><?= $brand ? $brand->name : ''; ?></div>
                                                </div>
                                                <div class="product-row">
                                                    <div class="product-label col-md-6"><?= lang("category"); ?> : </div>
                                                    <div class="product-content col-md-6"><?= $category->name; ?></div>
                                                </div>
                                                <div class="product-row">
                                                    <div class="product-label col-md-6"><?= lang("HSN Code"); ?> : </div>
                                                    <div class="product-content col-md-6"><?= $product->hsn_code; ?></div>
                                                </div>

                                                <?php if ($product->subcategory_id) { ?>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6"><?= lang("subcategory"); ?> : </div>
                                                        <div class="product-content col-md-6"><?= $subcategory->name; ?></div>
                                                    </div>
                                                <?php } ?>
                                                <?php if ($Settings->other_category_for_product): ?>
                                                    <?php
                                                    // Safety fallback
                                                    $other_category = trim($other_category);
                                                    if (empty($other_category)) {
                                                        $other_category = 'No categories';
                                                    }

                                                    $max_length = 40; // max visible length
                                                    $display_text = $other_category;
                                                    if (strlen($other_category) > $max_length) {
                                                        $display_text = substr($other_category, 0, $max_length) . '...';
                                                    }
                                                    ?>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6"><?= lang("Other Category"); ?> : </div>
                                                        <div class="product-content col-md-6">
                                                            <span title="<?= htmlspecialchars($other_category, ENT_QUOTES, 'UTF-8') ?>"
                                                                style="cursor:Pointer;">
                                                                <?= htmlspecialchars($display_text, ENT_QUOTES, 'UTF-8') ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                <?php endif; ?>
                                                <div class="product-row">
                                                    <div class="product-label col-md-6"><?= lang("unit"); ?> : </div>
                                                    <div class="product-content col-md-6">
                                                        <?= $unit->name . ' (' . $unit->code . ')'; ?></div>
                                                </div>
                                                <?php if ($Owner || $Admin) { ?>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6"><?= lang("cost"); ?> : </div>
                                                        <div class="product-content col-md-6">
                                                            <?= $this->sma->formatMoney($product->cost); ?></div>
                                                    </div>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6"><?= lang("price"); ?> : </div>
                                                        <div class="product-content col-md-6">
                                                            <?= $this->sma->formatMoney($product->price); ?></div>
                                                    </div>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6"><?= lang("mrp"); ?> : </div>
                                                        <div class="product-content col-md-6">
                                                            <?= $this->sma->formatMoney($product->mrp); ?></div>
                                                    </div>
                                                    <?php if ($product->promotion) { ?>
                                                        <div class="product-row">
                                                            <div class="product-label col-md-6"><?= lang("promotion"); ?> : </div>
                                                            <div class="product-content col-md-6">
                                                                <?= $this->sma->formatMoney($product->promo_price); ?>
                                                                (<?= $this->sma->hrsd($product->start_date); ?> -
                                                                <?= $this->sma->hrsd($product->end_date); ?>)
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                    <?php } else {
                                                    if ($this->session->userdata('show_cost')) { ?>
                                                        <div class="product-row">
                                                            <div class="product-label col-md-6"><?= lang("cost"); ?> : </div>
                                                            <div class="product-content col-md-6">
                                                                <?= $this->sma->formatMoney($product->cost); ?></div>
                                                        </div>
                                                    <?php }
                                                    if ($this->session->userdata('show_price')) { ?>
                                                        <div class="product-row">
                                                            <div class="product-label col-md-6"><?= lang("price"); ?> : </div>
                                                            <div class="product-content col-md-6">
                                                                <?= $this->sma->formatMoney($product->price); ?></div>
                                                        </div>
                                                        <?php if ($product->promotion) { ?>
                                                            <div class="product-row">
                                                                <div class="product-label col-md-6"><?= lang("promotion"); ?> : </div>
                                                                <div class="product-content col-md-6">
                                                                    <?= $this->sma->formatMoney($product->promo_price); ?>
                                                                    (<?= $this->sma->hrsd($product->start_date); ?> -
                                                                    <?= $this->sma->hrsd($product->end_date); ?>)
                                                                </div>
                                                            </div>
                                                        <?php }
                                                    }
                                                    if ($this->session->userdata('show_mrp')) { ?>
                                                        <div class="product-row">
                                                            <div class="product-label col-md-6"><?= lang("mrp"); ?> : </div>
                                                            <div class="product-content col-md-6">
                                                                <?= $this->sma->formatMoney($product->mrp); ?></div>
                                                        </div>
                                                    <?php }
                                                } ?>

                                                <?php if ($product->tax_rate) { ?>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6"><?= lang("tax_rate"); ?> : </div>
                                                        <div class="product-content col-md-6"><?= $tax_rate->name; ?></div>
                                                    </div>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6"><?= lang("tax_method"); ?> : </div>
                                                        <div class="product-content col-md-6">
                                                            <?= $product->tax_method == 0 ? lang('inclusive') : lang('exclusive'); ?>
                                                        </div>
                                                    </div>
                                                <?php } ?>

                                                <?php if ($product->alert_quantity != 0) { ?>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6"><?= lang("alert_quantity"); ?> : </div>
                                                        <div class="product-content col-md-6">
                                                            <?= $this->sma->formatQuantity($product->alert_quantity); ?></div>
                                                    </div>
                                                <?php } ?>

                                                <div class="product-row ">
                                                    <div class="product-label col-md-6"><?= lang("Article No"); ?> :
                                                    </div>
                                                    <div class="product-content col-md-6"><?= $product->article_code; ?></div>
                                                </div>
                                                <div class="product-row ">
                                                    <div class="product-label col-md-6"><?= lang("Quantity"); ?> :
                                                    </div>
                                                    <div class="product-content col-md-6"><?= $product->quantity == 0 ? 0 : $this->sma->formatQuantity($product->quantity); ?></div>
                                                </div>
                                                <?php if ($Settings->packing_size_column == 1) { ?>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6">
                                                            <?= lang("Packing Size"); ?> :
                                                        </div>
                                                        <div class="product-content col-md-6">
                                                            <?= $product->packing_size; ?>
                                                        </div>
                                                    </div>
                                                <?php } ?> 

                                                <div class="col-md-12 p0">
                                                    <?php if ($variants) { ?>
                                                        <div class="product-row1">
                                                            <div class="product-label col-md-6"><?= lang("product_variants"); ?> : </div>
                                                            <div class="product-content1 col-md-6 p0">
                                                                <?php foreach ($variants as $variant) {
                                                                    //$options_color = $this->sma->getProductOptionsByGroupId($product->id, COLOR);
                                                                    echo '<span class="label label-primary m1">' . $variant->name . '</span> ';
                                                                } ?>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <?php
                                                $colorArr = [];
                                                foreach ($colors as $color) {
                                                    $color->name ? $colorArr[] = $color->name : '';
                                                }
                                                if (!empty($colorArr)) { ?>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6"><?= lang("Product_Color"); ?> : </div>
                                                        <div class="product-content2 col-md-6">
                                                            <?php foreach ($colors as $color) {
                                                                echo '<span class="label label-primary m1">' . $color->name . '</span> ';
                                                            } ?>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-xs-12">
                            <div class="table-responsive">
                                <div class="product-details">
                                    <div class="d-flx">
                                        <div class="product-row">
                                            <div class="product-label col-md-6"><?= lang("type"); ?></div>
                                            <div class="product-content col-md-6"><?= lang($product->type); ?></div>
                                        </div>
                                        <div class="product-row">
                                            <div class="product-label col-md-6"><?= lang("code"); ?></div>
                                            <div class="product-content col-md-6"><?= $product->code; ?></div>
                                        </div>
                                        <div class="product-row">
                                            <div class="product-label col-md-6"><?= lang("brand"); ?></div>
                                            <div class="product-content col-md-6"><?= $brand ? $brand->name : ''; ?></div>
                                        </div>
                                        <div class="product-row">
                                            <div class="product-label col-md-6"><?= lang("category"); ?></div>
                                            <div class="product-content col-md-6"><?= $category->name; ?></div>
                                        </div>
                                        <?php if ($product->subcategory_id) { ?>
                                            <div class="product-row">
                                                <div class="product-label col-md-6"><?= lang("subcategory"); ?></div>
                                                <div class="product-content col-md-6"><?= $subcategory->name; ?></div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($Settings->other_category_for_product): ?>
                                            <?php
                                            // Safety fallback
                                            $other_category = trim($other_category);
                                            if (empty($other_category)) {
                                                $other_category = 'No categories';
                                            }

                                            $max_length = 40; // max visible length
                                            $display_text = $other_category;
                                            if (strlen($other_category) > $max_length) {
                                                $display_text = substr($other_category, 0, $max_length) . '...';
                                            }
                                            ?>
                                            <div class="product-row">
                                                <div class="product-label col-md-6"><?= lang("Other Category"); ?></div>
                                                <div class="product-content col-md-6">
                                                    <span title="<?= htmlspecialchars($other_category, ENT_QUOTES, 'UTF-8') ?>"
                                                        style="cursor:Pointer;">
                                                        <?= htmlspecialchars($display_text, ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </div>
                                            </div>

                                        <?php endif; ?>
                                        <div class="product-row">
                                            <div class="product-label col-md-6"><?= lang("unit"); ?></div>
                                            <div class="product-content col-md-6">
                                                <?= $unit->name . ' (' . $unit->code . ')'; ?></div>
                                        </div>
                                        <?php if ($Owner || $Admin) { ?>
                                            <div class="product-row">
                                                <div class="product-label col-md-6"><?= lang("cost"); ?></div>
                                                <div class="product-content col-md-6">
                                                    <?= $this->sma->formatMoney($product->cost); ?></div>
                                            </div>
                                            <div class="product-row">
                                                <div class="product-label col-md-6"><?= lang("price"); ?></div>
                                                <div class="product-content col-md-6">
                                                    <?= $this->sma->formatMoney($product->price); ?></div>
                                            </div>
                                            <?php if ($product->promotion) { ?>
                                                <div class="product-row">
                                                    <div class="product-label col-md-6"><?= lang("promotion"); ?></div>
                                                    <div class="product-content col-md-6">
                                                        <?= $this->sma->formatMoney($product->promo_price); ?>
                                                        (<?= $this->sma->hrsd($product->start_date); ?> -
                                                        <?= $this->sma->hrsd($product->end_date); ?>)
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <?php } else {
                                            if ($this->session->userdata('show_cost')) { ?>
                                                <div class="product-row">
                                                    <div class="product-label col-md-6"><?= lang("cost"); ?></div>
                                                    <div class="product-content col-md-6">
                                                        <?= $this->sma->formatMoney($product->cost); ?></div>
                                                </div>
                                            <?php }
                                            if ($this->session->userdata('show_price')) { ?>
                                                <div class="product-row">
                                                    <div class="product-label col-md-6"><?= lang("price"); ?></div>
                                                    <div class="product-content col-md-6">
                                                        <?= $this->sma->formatMoney($product->price); ?></div>
                                                </div>
                                                <?php if ($product->promotion) { ?>
                                                    <div class="product-row">
                                                        <div class="product-label col-md-6"><?= lang("promotion"); ?></div>
                                                        <div class="product-content col-md-6">
                                                            <?= $this->sma->formatMoney($product->promo_price); ?>
                                                            (<?= $this->sma->hrsd($product->start_date); ?> -
                                                            <?= $this->sma->hrsd($product->end_date); ?>)
                                                        </div>
                                                    </div>
                                        <?php }
                                            }
                                        } ?>
                                        <div class="product-row">
                                            <div class="product-label col-md-6"><?= lang("mrp"); ?></div>
                                            <div class="product-content col-md-6">
                                                <?= $this->sma->formatMoney($product->mrp); ?></div>
                                        </div>

                                        <?php if ($product->tax_rate) { ?>
                                            <div class="product-row">
                                                <div class="product-label col-md-6"><?= lang("tax_rate"); ?></div>
                                                <div class="product-content col-md-6"><?= $tax_rate->name; ?></div>
                                            </div>
                                            <div class="product-row">
                                                <div class="product-label col-md-6"><?= lang("tax_method"); ?></div>
                                                <div class="product-content col-md-6">
                                                    <?= $product->tax_method == 0 ? lang('inclusive') : lang('exclusive'); ?>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($product->alert_quantity != 0) { ?>
                                            <div class="product-row">
                                                <div class="product-label col-md-6"><?= lang("alert_quantity"); ?></div>
                                                <div class="product-content col-md-6">
                                                    <?= $this->sma->formatQuantity($product->alert_quantity); ?></div>
                                            </div>
                                        <?php } ?>

                                        <div class="product-row col-md-6">
                                            <div class="product-label col-md-6"><?= lang("Article No"); ?>
                                            </div>
                                            <div class="product-content col-md-6"><?= $product->article_code; ?></div>
                                        </div>
                                        <div class="product-row col-md-6">
                                            <div class="product-label col-md-6"><?= lang("Quantity"); ?>
                                            </div>
                                            <div class="product-content col-md-6"><?= $product->quantity; ?></div>
                                        </div>

                                        <div class="col-md-12 p0">


                                            <?php if ($variants) { ?>
                                                <div class="product-row1 col-md-12">
                                                    <div class="product-label col-md-6"><?= lang("product_variants"); ?></div>
                                                    <div class="product-content1 col-md-6 p0">
                                                        <?php foreach ($variants as $variant) {
                                                            //$options_color = $this->sma->getProductOptionsByGroupId($product->id, COLOR);
                                                            echo '<span class="label label-primary m1">' . $variant->name . '</span> ';
                                                        } ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <?php if ($colors) { ?>
                                            <div class="product-row col-md-12">
                                                <div class="product-label col-md-6"><?= lang("Product_Color"); ?></div>
                                                <div class="product-content2 col-md-6">
                                                    <?php foreach ($colors as $color) {
                                                        echo '<span class="label label-primary m1">' . $color->name . '</span> ';
                                                    } ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </div>

                    <div class="clearfix"></div>
                    <div class="col-xs-12">
                        <div class="row b-b-cf-table">
                            <div class="col-xs-12 b-cf-table">
                                <?php if ($product->cf1 || $product->cf2 || $product->cf3 || $product->cf4 || $product->cf5 || $product->cf6) { 
                                            $custom_fields_labels = $this->data['custome_fields'];
                                    ?>
                                    <h3 class="bold cf-heading"><?= lang('custom_fields') ?></h3>
                                    <div class="table-responsive cf-table-div">
                                        <table class="table table-bordered table-striped table-condensed dfTable two-columns cf-table">
                                            <thead>
                                                <tr>
                                                    <th><?= lang('Name') ?></th>
                                                    <th><?= lang('Value') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // $product_row = $this->products_model->getProduct_typeByID($product->cf2);

                                                if ($product->cf1) {
                                                    echo '<tr>
                                                            <td class="cfl">' . ($custom_fields_labels->cf1 ? $custom_fields_labels->cf1 : lang('pcf1', 'pcf1')). '</td>
                                                            <td class="cfl">' . $product->cf1 . '</td>
                                                        </tr>';
                                                }
                                                if ($product->cf2) {
                                                    echo '<tr>
                                                            <td class="cfl" >' . ($custom_fields_labels->cf2 ? $custom_fields_labels->cf2 : lang('pcf2', 'pcf2')). '</td>
                                                            <td class="cfl">' . $product->cf2 . '</td>
                                                        </tr>';
                                                }
                                                if ($product->cf3) {
                                                    echo '<tr>
                                                            <td class="cfl" >' . ($custom_fields_labels->cf3 ? $custom_fields_labels->cf3 : lang('pcf3', 'pcf3')). '</td>
                                                            <td class="cfl">' . $product->cf3 . '</td>
                                                        </tr>';
                                                }
                                                if ($product->cf4) {
                                                    echo '<tr>
                                                            <td class="cfl" >' . ($custom_fields_labels->cf4 ? $custom_fields_labels->cf4 : lang('pcf4', 'pcf4')). '</td>
                                                            <td class="cfl">' . $product->cf4 . '</td>
                                                        </tr>';
                                                }
                                                if ($product->cf5) {
                                                    echo '<tr>
                                                            <td class="cfl" >' . ($custom_fields_labels->cf5 ? $custom_fields_labels->cf5 : lang('pcf5', 'pcf5')). '</td>
                                                            <td class="cfl">' . $product->cf5 . '</td>
                                                        </tr>';
                                                }
                                                if ($product->cf6) {
                                                    echo '<tr>
                                                            <td class="cfl" >' . ($custom_fields_labels->cf6 ? $custom_fields_labels->cf6 : lang('pcf6', 'pcf6')). '</td>
                                                            <td class="cfl">' . $product->cf6 . '</td>
                                                        </tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>

                               <?php
                    // Show only active warehouses/vendors in popup tables.
                    $warehouses = array_values(array_filter((array)$warehouses, function ($w) {
                        return !isset($w->is_deleted) || (int)$w->is_deleted === 0;
                    }));
                    $suppliers = array_values(array_filter((array)$suppliers, function ($s) {
                        return !isset($s->is_deleted) || (int)$s->is_deleted === 0;
                    }));
                    $activeWarehouseNames = [];
                    foreach ($warehouses as $activeWh) {
                        if (isset($activeWh->name)) {
                            $activeWarehouseNames[(string)$activeWh->name] = true;
                        }
                    }

                    // NON-VENDOR warehouses
                    $hasNonVendorStock = false;
                    if (!empty($warehouses)) {
                        foreach ($warehouses as $w) {
                            if (isset($w->quantity) && (float)$w->quantity > 0) {
                                $hasNonVendorStock = true;
                                break;
                            }
                        }
                    }

                    // VENDOR warehouses
                    $hasVendorStock = false;
                    if (!empty($suppliers)) {
                        foreach ($suppliers as $s) {
                            if (isset($s->quantity) && (float)$s->quantity > 0) {
                                $hasVendorStock = true;
                                break;
                            }
                        }
                    }
                    ?>
                                <!-- Warehouse Quantity Table -->
                          <div class="row">
                            <?php if ((!$Supplier || !$Customer) && !empty($warehouses) && $hasNonVendorStock && ($product->type == 'standard' || $product->type == 'raw' || $product->type == 'Intermediate')) { ?>
                                <div class="col-sm-6">
                                    <h3 class="bold wh-heading"><?= lang('warehouse_quantity') ?></h3>
                                    <div class="table-responsive wh-div">
                                        <table id="warehouse-quantity-table" class="table table-bordered table-striped table-condensed dfTable two-columns wh-table hide-dt-sort-icons">
                                            <thead>
                                                <tr>
                                                    <th><?= lang('warehouse_name') ?></th>
                                                    <th><?= ($Settings->product_batch_setting > 0) ? lang('quantity') : (lang('quantity') . ' (' . lang('rack') . ')'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($Settings->product_batch_setting > 0 && !empty($purchase)) {
                                                    $whIndex = [];
                                                    foreach ($warehouses as $w) { $whIndex[$w->id] = $w; }
                                                    $group = [];
                                                    foreach ($purchase as $pi) {
                                                        $wid = isset($pi->warehouse_id) ? (int)$pi->warehouse_id : 0;
                                                        if (!isset($whIndex[$wid])) { continue; }
                                                        $batch_raw = trim((string)$pi->batch_number);
                                                        $batch = ($batch_raw === '' || $batch_raw === '0') ? '---' : $batch_raw;
                                                        $qty    = (float)$pi->quantity_balance;
                                                        $expiry = isset($pi->expiry) ? trim((string)$pi->expiry) : '';

                                                        if (!isset($group[$wid])) {
                                                            $group[$wid] = [];
                                                        }
                                                        if (!isset($group[$wid][$batch])) {
                                                            $group[$wid][$batch] = ['qty' => 0, 'expiry' => ''];
                                                        }

                                                        $group[$wid][$batch]['qty'] += $qty;

                                                        // store a readable expiry once, if valid
                                                        if ($expiry && $expiry !== '0000-00-00' && $group[$wid][$batch]['expiry'] === '') {
                                                            $group[$wid][$batch]['expiry'] = $this->sma->hrsd($expiry);
                                                        }
                                                    }

                                                    $groupTotals = [];
                                                    foreach ($group as $wid => $batches) {
                                                        $total = 0;
                                                        foreach ($batches as $info) {
                                                            $total += isset($info['qty']) ? (float)$info['qty'] : 0;
                                                        }
                                                        $groupTotals[$wid] = $total;
                                                    }
                                                    uksort($group, function ($a, $b) use ($groupTotals) {
                                                        $aTotal = isset($groupTotals[$a]) ? (float)$groupTotals[$a] : 0;
                                                        $bTotal = isset($groupTotals[$b]) ? (float)$groupTotals[$b] : 0;
                                                        if ($aTotal === $bTotal) {
                                                            return 0;
                                                        }
                                                        return ($aTotal < $bTotal) ? 1 : -1;
                                                    });

                                                    foreach ($group as $wid => $batches) {
                                                        $wh = $whIndex[$wid];
                                                        $wh_total = isset($groupTotals[$wid]) ? (float)$groupTotals[$wid] : 0;
                                                        echo '<tr>';
                                                        echo '<td style="text-align:left;">' . htmlspecialchars($wh->name) . ' (' . htmlspecialchars($wh->code) . ')</td>';
                                                        echo '<td class="product-content2" style="text-align:left;" data-order="' . (float)$wh_total . '">';
                                                        $i = 1;
                                                        foreach ($batches as $batch => $info) {
                                                            $qty    = isset($info['qty']) ? (float)$info['qty'] : 0;
                                                            $expiry = isset($info['expiry']) ? $info['expiry'] : '';
                                                            
                                                            $display_batch = htmlspecialchars($batch);
                                                            $qty_html = $qty == 0 ? 0 : $this->sma->formatQuantity($qty);
                                                            
                                                            echo '<div style="display: flex; align-items: center; margin: 0 0 2px 0; padding: 0; font-size: 13px; line-height: 1.4; width: 100%;">';
                                                            echo '<span style="flex-shrink: 0; width: 20px; text-align: right; padding-right: 2px; font-size: 15px; width:5%; font-weight:bold; color:#2c3e50;">' . (int)$i . '.</span>';
                                                            echo '<span style="flex: 1; min-width: 30%; white-space: nowrap; text-overflow: ellipsis; padding-right: 10px; font-size: 13px; font-weight: bold; text-align: left;" title="' . $display_batch . '">' . $display_batch . '</span>';
                                                            if ($expiry) {
                                                                echo '<span style="color: #2c3e50; font-size: 13px; min-width: 100px; white-space: nowrap; width: 42%; text-align: center;">Expiry:' . $expiry . '</span>';
                                                            } else {
                                                                echo '<span style="min-width: 100px; width: 42%;"></span>';
                                                            }
                                                            echo '<span style="flex-shrink: 0; font-weight: bold; width: 20%; text-align: right; padding: 0 10px; font-size: 15px; color: #2c3e50;">' . $qty_html . '</span>';
                                                            echo '</div>';
                                                            
                                                            $i++;
                                                        }
                                                    echo '<div style="
                                                            margin-top: 4px;
                                                            padding-top: 4px;                                                          
                                                            font-size: 13px;
                                                            font-weight: bold;
                                                            display: flex;
                                                            align-items: center;
                                                            width: 100%;
                                                        ">
                                                            <span style="flex-shrink: 0; width: 20px; text-align: right; padding-right: 2px; font-size: 15px; width:5%;"></span>
                                                            <span style="flex: 1; min-width: 30%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 10px; font-size: 15px; font-weight: bold; text-align: left;">' . lang('total') . '</span>
                                                            <span style="min-width: 100px; width: 30%; text-align: center;"></span>
                                                            <span style="flex-shrink: 0; font-weight: bold; width: 20%; text-align: right; padding: 0 10px; font-size: 15px; color: #2c3e50;">' . htmlspecialchars($wh_total == 0 ? 0 : $this->sma->formatQuantity($wh_total)) . '</span>
                                                        </div>';

                                                        echo '</td>';
                                                        echo '</tr>';
                                                    }
                                                    // removed bottom totals row; per-cell totals are shown above
                                                } else {
                                                    usort($warehouses, function ($a, $b) {
                                                        $aQty = isset($a->quantity) ? (float)$a->quantity : 0;
                                                        $bQty = isset($b->quantity) ? (float)$b->quantity : 0;
                                                        if ($aQty === $bQty) {
                                                            return 0;
                                                        }
                                                        return ($aQty < $bQty) ? 1 : -1;
                                                    });
                                                    foreach ($warehouses as $warehouse) {
                                                        echo '<tr><td style="text-align:left;">' . $warehouse->name . ' (' . $warehouse->code . ')</td><td data-order="' . (float)$warehouse->quantity . '"><strong>' . ($warehouse->quantity == 0 ? 0 : $this->sma->formatQuantity($warehouse->quantity)) . '</strong>' . ($warehouse->rack ? ' (' . $warehouse->rack . ')' : '') . '</td></tr>';
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                                 </div>
                                <!-- Supplier Quantity Table -->
                    <?php if ((!$Supplier || !$Customer) && !empty($suppliers) && $hasVendorStock && ($product->type == 'standard' || $product->type == 'raw' || $product->type == 'Intermediate')) { ?>
                        <div class="col-sm-6">
                            <h3 class="bold"><?= lang('supplier_quantity') ?></h3>
                            <div class="table-responsive">
                                <table id="vendor-quantity-table" class="table table-bordered table-striped table-condensed dfTable two-columns hide-dt-sort-icons">
                                    <thead>
                                        <tr>
                                            <th><?= lang('warehouse_name') ?></th>
                                            <th><?= ($Settings->product_batch_setting > 0) ? lang('quantity') : (lang('quantity') . ' (' . lang('rack') . ')'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($Settings->product_batch_setting > 0 && !empty($purchase)) {
                                            // Index suppliers by id for name/code
                                            $supIndex = [];
                                            foreach ($suppliers as $s) { if (isset($s->id)) { $supIndex[(int)$s->id] = $s; } }

                                            // Group quantities per supplier warehouse and batch
                                            $sGroup = [];
                                            foreach ($purchase as $pi) {
                                                $wid = isset($pi->warehouse_id) ? (int)$pi->warehouse_id : 0;
                                                if (!isset($supIndex[$wid])) { continue; }
                                                $batch = trim((string)(isset($pi->batch_number) ? $pi->batch_number : ''));
                                                $qty   = (float)(isset($pi->quantity_balance) ? $pi->quantity_balance : 0);
                                                if (!isset($sGroup[$wid])) { $sGroup[$wid] = []; }
                                                if (!isset($sGroup[$wid][$batch])) { $sGroup[$wid][$batch] = 0; }
                                                $sGroup[$wid][$batch] += $qty;
                                            }

                                            $sTotals = [];
                                            foreach ($sGroup as $wid => $batches) {
                                                $sTotals[$wid] = array_sum($batches);
                                            }
                                            uksort($sGroup, function ($a, $b) use ($sTotals) {
                                                $aTotal = isset($sTotals[$a]) ? (float)$sTotals[$a] : 0;
                                                $bTotal = isset($sTotals[$b]) ? (float)$sTotals[$b] : 0;
                                                if ($aTotal === $bTotal) {
                                                    return 0;
                                                }
                                                return ($aTotal < $bTotal) ? 1 : -1;
                                            });

        	                                if (!empty($sGroup)) {
                                                foreach ($sGroup as $wid => $batches) {
                                                    $sw = $supIndex[$wid];
                                                    $sw_total = isset($sTotals[$wid]) ? (float)$sTotals[$wid] : 0;
                                                    echo '<tr>';
                                                    echo '<td>' . htmlspecialchars($sw->name, ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($sw->code, ENT_QUOTES, 'UTF-8') . ')</td>';
                                                    echo '<td class="product-content2" data-order="' . (float)$sw_total . '">';
                                                    $i = 1;
                                                    foreach ($batches as $batch => $qty) {
                                                        $batch_text = ($batch === '' ? '-' : $batch);
                                                        echo '<div>' . $i++ . ') ' . htmlspecialchars($batch_text, ENT_QUOTES, 'UTF-8') . ' '
                                                           . '<span class="qty-highlight">(' . $this->sma->formatQuantity($qty) . ')</span>'
                                                           . '</div>';
                                                    }
                                                    echo '<div><strong>' . lang('total') . ' = ' . $this->sma->formatQuantity($sw_total) . '</strong></div>';
                                                    echo '</td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                // Fallback: show original simple quantities when no batch rows match suppliers
                                                usort($suppliers, function ($a, $b) {
                                                    $aQty = isset($a->quantity) ? (float)$a->quantity : 0;
                                                    $bQty = isset($b->quantity) ? (float)$b->quantity : 0;
                                                    if ($aQty === $bQty) {
                                                        return 0;
                                                    }
                                                    return ($aQty < $bQty) ? 1 : -1;
                                                });
                                                foreach ($suppliers as $supplier) {
                                                    echo '<tr>'
                                                        . '<td>' . $supplier->name . ' (' . $supplier->code . ')</td>'
                                                        . '<td data-order="' . (float)$supplier->quantity . '"><strong>' . $this->sma->formatQuantity($supplier->quantity) . '</strong>'
                                                        . ($supplier->rack ? ' (' . $supplier->rack . ')' : '') . '</td>'
                                                        . '</tr>';
                                                }
                                            }
                                        } else {
                                            usort($suppliers, function ($a, $b) {
                                                $aQty = isset($a->quantity) ? (float)$a->quantity : 0;
                                                $bQty = isset($b->quantity) ? (float)$b->quantity : 0;
                                                if ($aQty === $bQty) {
                                                    return 0;
                                                }
                                                return ($aQty < $bQty) ? 1 : -1;
                                            });
                                            foreach ($suppliers as $supplier) {
                                                echo '<tr>'
                                                    . '<td>' . $supplier->name . ' (' . $supplier->code . ')</td>'
                                                    . '<td data-order="' . (float)$supplier->quantity . '"><strong>' . $this->sma->formatQuantity($supplier->quantity) . '</strong>'
                                                    . ($supplier->rack ? ' (' . $supplier->rack . ')' : '') . '</td>'
                                                    . '</tr>';
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                            
                            <div class="col-xs-12">
                                <?php if ($product->type == 'combo') { ?>
                                    <h3 class="bold"><?= lang('Combo_items') ?></h3>
                                    <div class="table-responsive">
                                        <table class="table  table-striped table-condensed dfTable two-columns">
                                            <thead>
                                                <tr>
                                                    <th><?= lang('product_name') ?></th>
                                                    <th><?= lang('quantity') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($combo_items as $combo_item) {
                                                    echo '<tr><td>' . $combo_item->name . ' (' . $combo_item->code . ') </td><td>' . $this->sma->formatQuantity($combo_item->qty) . '</td></tr>';
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                                <?php if ($product->type == 'Bundle') { ?>
                                    <h3 class="bold"><?= lang('Combo_items') ?></h3>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-condensed dfTable two-columns">
                                            <thead>
                                                <tr>
                                                    <th><?= lang('product_name') ?></th>
                                                    <th><?= lang('quantity') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($combo_items as $combo_item) {
                                                    echo '<tr><td>' . $combo_item->name . ' (' . $combo_item->code . ') </td><td>' . $this->sma->formatQuantity($combo_item->qty) . '</td></tr>';
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>

                                <?php if (!empty($options)) { ?>
                                    <?php
                                        // Get currency symbol using formatMoney()
                                        $money_example  = $this->sma->formatMoney(0);

                                        // Remove numbers, dots, commas, spaces & NBSP → keep symbol only
                                        $currency_symbol = trim(preg_replace('/[0-9\.,\s\x{00A0}]/u', '', $money_example));
                                        ?>

                                    <h3 class="bold vp-heading"><?= lang('Variant Pricing'); ?></h3>
                                    <div class="table-responsive vpd-div">
                                        <table class="table table-bordered table-striped table-condensed dfTable variant-pricing-table">
                                            <thead>
                                                <tr>
                                                    <th><?= lang('Variant') ?></th>
                                                    <?php
                                                    $variant_compare_name = '';
                                                    foreach ($options as $option) {
                                                        if ($variant_compare_name != $option->name) {
                                                            $variant_compare_name = $option->name;
                                                            echo '<td>' . $option->name . '</td>';
                                                        }
                                                    }
                                                    ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th><?= lang('Price') ?> (<?= $currency_symbol ?>)</th>
                                                    <?php
                                                    $variant_compare_name = '';
                                                    foreach ($options as $option) {
                                                        if ($variant_compare_name != $option->name) {
                                                            $variant_compare_name = $option->name;
                                                            echo '<td style="text-align:right !important">' . $this->sma->formatMoney($option->price ?: 0) . '</td>';
                                                        }
                                                    }
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <th><?= lang('MRP') ?> (<?= $currency_symbol ?>)</th>
                                                    <?php
                                                    $variant_compare_name = '';
                                                    foreach ($options as $option) {
                                                        if ($variant_compare_name != $option->name) {
                                                            $variant_compare_name = $option->name;
                                                            echo '<td style="text-align:right !important">' . $this->sma->formatMoney($option->mrp ?: 0) . '</td>';
                                                        }
                                                    }
                                                    ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <h3 class="bold qpv-heading" id="qpv-heading"><?= lang('Quantity_Of_Product_Variants_At_Each_Warehouse'); ?></h3>
                                    <div class="table-responsive vp-div">
                                        <table id="variant-warehouse-quantity-table" class="table table-bordered table-striped table-condensed dfTable quantity-table vp-table hide-dt-sort-icons">
                                            <thead>
                                                <tr>
                                                    <th style="text-align:left;"><?= lang('warehouses') ?></th>
                                                    <?php
                                                    $variant_compare_name = '';
                                                    $variant_headers = []; // To store variant names (columns)
                                                    foreach ($options as $option) {
                                                        if ($variant_compare_name != $option->name) {
                                                            $variant_compare_name = $option->name;
                                                            echo '<th>' . htmlspecialchars($option->name) . '</th>';
                                                            $variant_headers[] = $option->name;  // Collect variant names (for columns)
                                                        }
                                                    }
                                                    ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($Settings->product_batch_setting > 0 && !empty($purchase)) {
                                                    $whById = [];
                                                    foreach ($warehouses as $w) { $whById[$w->id] = ['name' => $w->name, 'code' => $w->code]; }
                                                    $variantById = [];
                                                    if (!empty($variants)) {
                                                        foreach ($variants as $v) { $variantById[(int)$v->id] = $v->name; }
                                                    }
                                                    $grid = [];
                                                    $totalsByVariant = [];
                                                    foreach ($purchase as $pi) {
                                                        $wid = isset($pi->warehouse_id) ? (int)$pi->warehouse_id : 0;
                                                        if (!isset($whById[$wid])) { continue; }
                                                        $opt = isset($pi->option_id) ? (int)$pi->option_id : 0;
                                                        $vname = isset($variantById[$opt]) ? $variantById[$opt] : '';
                                                        if ($vname === '' || !in_array($vname, $variant_headers, true)) { continue; }
                                                        $batch = trim((string)$pi->batch_number);
                                                        $qty   = (float)$pi->quantity_balance;
                                                        if (!isset($grid[$wid])) { $grid[$wid] = []; }
                                                        if (!isset($grid[$wid][$vname])) { $grid[$wid][$vname] = []; }
                                                        if (!isset($grid[$wid][$vname][$batch])) { $grid[$wid][$vname][$batch] = 0; }
                                                        $grid[$wid][$vname][$batch] += $qty;
                                                        if (!isset($totalsByVariant[$vname])) { $totalsByVariant[$vname] = 0; }
                                                        $totalsByVariant[$vname] += $qty;
                                                    }
                                                    foreach ($grid as $wid => $perVariant) {
                                                        $wh = $whById[$wid];
                                                        echo '<tr>';
                                                        echo '<td class="quantity-cell">' . htmlspecialchars($wh['name']) . '</td>';
                                                        foreach ($variant_headers as $vh) {
                                                            echo '<td class="quantity-cell">';
                                                            if (isset($perVariant[$vh])) {
                                                                $cell_total = 0;
                                                                $i = 1;
                                                                foreach ($perVariant[$vh] as $b => $q) {
                                                                    $batch_text = (string)($b ?: '-');
                                                                    $batch_raw = trim((string)$b);
                                                                $display_batch = ($batch_raw === '' || $batch_raw === '0') ? '---' : $batch_raw;
                                                                   $qty_html = '<span class="qty-highlight">' . htmlspecialchars($this->sma->formatQuantity($q)) . '</span>';
                                                                echo '<div style="display:flex; align-items:center; margin-bottom:2px; font-weight:bold;">
                                                                        <span style="width:26px; text-align:right;">' . $i . '.</span>
                                                                        <span style="flex:1;
                                                                                padding-left:6px;
                                                                                text-align:left;
                                                                                white-space:nowrap;
                                                                                overflow:hidden;
                                                                                text-overflow:ellipsis;
                                                                            ">' . htmlspecialchars($display_batch) . '</span>
                                                                        <span style="width:70px; text-align:right;">' . $qty_html . '</span>
                                                                    </div>';
                                                                    $i++;
                                                                    $cell_total += (float)$q;
                                                                }
                                                        echo '<div style="
                                                                margin-top: 4px;
                                                                padding-left: 14px;   /* aligns with batch numbers */
                                                                font-weight: bold;
                                                                text-align: right;
                                                                font-weight:bold; 
                                                                display: flex; 
                                                            ">
                                                            <span style="flex: 1; text-align:left;">' . lang('total') . '</span>
                                                            <span style="width:70px; text-align:right;">'. htmlspecialchars($this->sma->formatQuantity($cell_total)) .'</span>
                                                            </div>';
                                                            } else {
                                                                echo '&nbsp;';
                                                            }
                                                            echo '</td>';
                                                        }
                                                        echo '</tr>';
                                                    }
                                                    // removed bottom totals row; per-cell totals are shown above
                                                } else {
                                                    $whRows = [];
                                                    foreach ($options as $option) {
                                                        if (isset($option->is_deleted) && (int)$option->is_deleted !== 0) {
                                                            continue;
                                                        }
                                                        $wh_name = $option->wh_name;
                                                        if ($wh_name === '' || !isset($activeWarehouseNames[(string)$wh_name])) {
                                                            continue;
                                                        }
                                                        $variant_name = $option->name;
                                                        $wh_qty = $option->wh_qty;
                                                        if (!isset($whRows[$wh_name])) { $whRows[$wh_name] = []; }
                                                        $whRows[$wh_name][$variant_name] = $wh_qty;
                                                    }
                                                    foreach ($whRows as $wh_name => $variants_row) {
                                                        echo '<tr>';
                                                        echo '<td class="quantity-cell">' . htmlspecialchars($wh_name) . '</td>';
                                                        foreach ($variant_headers as $variant) {
                                                            $qty = isset($variants_row[$variant]) ? $variants_row[$variant] : 0;
                                                            echo '<td style="text-align:center !important" class="quantity-cell">' . htmlspecialchars($this->sma->formatQuantity($qty)) . '</td>';
                                                        }
                                                        echo '</tr>';
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>

                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <!-- <h2>Product Variant Barcode</h2> -->
                        <?php
                        if (!empty($variants_barcode)) {
                            foreach ($variants_barcode as $key => $val) {
                                $VariantBarcode = $val->Variants_stock;
                            }
                            if ($VariantBarcode != '') {
                                $ExploadeBarcode = explode(',', $VariantBarcode);
                                foreach ($ExploadeBarcode as $kbarcode => $valbarcode) {
                                    $str = trim(preg_replace('/\s*\([^)]*\)/', '', $valbarcode));
                                    echo $str . '<br>';
                                }
                            }
                        }
                        ?>
                    </div>
                    <div class="col-xs-12">
                        <?= $product->details ? '<div class="panel panel-success"><div class="panel-heading">' . lang('product_details_for_invoice') . '</div><div class="panel-body">' . $product->details . '</div></div>' : ''; ?>
                        <?= $product->product_details ? '<div class="panel panel-primary"><div class="panel-heading">' . lang('product_details') . '</div><div class="panel-body">' . $product->product_details . '</div></div>' : ''; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.change_img').click(function(event) {
                event.preventDefault();
                var img_src = $(this).find('img').attr('src');
                $('#pr-image').attr('src', img_src);
                return false;
            });

            const isFav = $('.new-img-div .fa-heart').hasClass('remove-fav-product');
            const containingModal = document.getElementById('pd-modal').closest("#myModal");            
            function isFavourite() {
                if(!containingModal.classList.contains('in')){
                    if(isFav != $('.new-img-div .fa-heart').hasClass('remove-fav-product')) {
                        window.location.reload();
                    }                   
                }
            }
            containingModal.addEventListener('click', isFavourite);

            $('#manage-batches').on('click', function (e) {
                e.preventDefault();
                let fetchUrl = $(this).attr('href');

                $('#modal_batch_add').remove();

                $.get(fetchUrl, function (html) {
                    if(!html) {
                        alert('Something went wrong, please try again later.');
                        return;                        
                    }
                    var $modal = $(html).filter('#modal_batch_add');

                    $('#pd-modal').append(html);
                    $('#modal_batch_add').css({
                        'z-index': 10,
                        'position': 'fixed',
                        'top' : '0px',
                        'left' : '-10%',
                    });

                    $('#ajaxCall').hide();
                    $('#modal_batch_add').modal({ backdrop: 'static', keyboard: false });
                });
                
                // console.log('funnnnnnnnnnnnnnnnncccccccccccc');
                $(document).on('shown.bs.modal', '#modal_batch_add', disableButtons);
                $(document).off('shown.bs.modal', '#modal_batch_add', disableButtons);                 

                function enableButtons(event) {
                    setTimeout(() => {
                        if(!document.getElementById('modal_batch_add')) {
                            // console.log('phurrrrrrrrrrrrr');
                            $('#modal_batch_add').remove();
                            $('.modal-backdrop').remove();
                            $('#header-buttons, #header-buttons-print').css('pointer-events', 'auto');
                            $('.hollow-kebab, .edit-button-div, .pd-close-btn').css('pointer-events', 'auto');
                            document.getElementById('modal_batch_add').removeEventListener('click', enableButtons);
                            document.getElementById('modal_batch_add').removeEventListener('click', disableButtons);
                        }                         
                    }, 1000);                                       
                }

                function disableButtons(event) {
                    // console.log('euuuuuuuu');
                    $('#header-buttons, #header-buttons-print').css('pointer-events', 'none');
                    $('.hollow-kebab, .edit-button-div, .pd-close-btn').css('pointer-events', 'none');
                    document.getElementById('modal_batch_add').addEventListener('click', enableButtons);
                }
            });

            $(document).on('hide.bs.modal', '#myModal', function () {
                $('#modal_batch_add').remove();
                $('.modal-backdrop').remove();
            });

            function initProductDetailTable(selector, orderConfig, enableOrdering) {
                if (!$.fn.dataTable || !$(selector).length) {
                    return;
                }
                
                try {
                    if ($.fn.dataTable.isDataTable && $.fn.dataTable.isDataTable(selector)) {
                        $(selector).dataTable().fnDestroy();
                    } else if ($(selector).hasClass('dataTable')) {
                        $(selector).dataTable().fnDestroy();
                    }
                } catch (e) {}

                $(selector).dataTable({
                    bPaginate: true,
                    bLengthChange: false,
                    bFilter: false,
                    bInfo: false,
                    bSort: enableOrdering,
                    iDisplayLength: 5,
                    sDom: 't<"dt-pagination"p>'
                });
            }

            initProductDetailTable('#warehouse-quantity-table', [], false);
            initProductDetailTable('#vendor-quantity-table', [], false);
            initProductDetailTable('#variant-warehouse-quantity-table', [], false);

            // $('body').on('click', '.bpo', function (e) {
            //     e.preventDefault();
            //     $(this).popover({html: true, trigger: 'manual'}).popover('toggle');
            //     return false;
            // });
            // $('body').on('click', '.bpo-close', function (e) {
            //     $('.bpo').popover('hide');
            //     return false;
            // });

    $(document).ready(function() {
        // Initialize tooltips
        $('.tip').tooltip();
        
        // Initialize popovers for delete confirmation
        $('body').on('click', '.bpo', function(e) {
            e.preventDefault();
            var popover = $('.bpo').not(this);
            // popover.popover('hide');
            $(this).popover({
                html: true,
                trigger: 'manual',
                container: 'body',
                placement: 'bottom',
                content: function() {
                    return $(this).data('content');
                }
            }).popover('show');
        });

        // Close popover when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.bpo, .popover').length) {
                $('.bpo').popover('hide');
            }
        });

        // Handle close button in popover
        $(document).on('click', '.bpo-close', function() {
            $('.delete-confirmation-popover').hide();
            $(this).closest('.popover').hide();
        });
    });
        });
    </script>