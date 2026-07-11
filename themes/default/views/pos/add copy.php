<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$is_pharma = isset($Settings->pos_type) && $Settings->pos_type == 'pharma' ? true : false;

$disblePage = $enablePage = "hidden";

if (isset($featuerd_products) && $featuerd_products == 1) {
    $pos_settings->default_category = NULL;
    $enablePage = "";
    // $disblePage = "";
} else {
    $enablePage = "";
}

$permisions = array();
if (is_array($GP)) {
    foreach ($GP as $key => $val) {
        $permisions[str_replace("-", "_", $key)] = $val;
    }
}
if ($kot_tokan) {
    $tokan = $kot_tokan;
} else {

    if (empty($order_tokan)) {
        $tokan = '1';
    } else {
        $tokan = $order_tokan->tokan + 1;
    }
}
?>
<script>
function validateCheckout() {
    var salesPerson = document.getElementById('sales_person').value;
    if (salesPerson === '0') {
        alert('Please Select Sales Person.');
    }
}
</script>
<script>
var tokan_no = '<?= $tokan ?>';
var token_no = '<?= $kot_token_no ?>';

function getSellerDetails(value) {
    $('#pos_sale_person').val(value);
}
</script>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title><?= lang('pos_module') . " | " . $Settings->site_name; ?></title>
    <script type="text/javascript">
    if (parent.frames.length !== 0) {
        top.location = '<?= site_url('pos') ?>';
    }
    var permisions = JSON.parse('<?php echo json_encode($permisions); ?>');
    </script>
    <base href="<?= base_url() ?>" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="pragma" content="no-cache" />
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png" />
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?= $assets ?>styles/theme.css" type="text/css" />
    <link rel="stylesheet" href="<?= $assets ?>styles/style.css" type="text/css" />
    <link rel="stylesheet" href="<?= $assets ?>pos/css/posajax.css" type="text/css" />

    <?php if ($Settings->theme != 'default') { ?>
    <link rel="stylesheet" href="<?= $assets ?>pos/css/<?= $Settings->theme ?>.css" type="text/css" />
    <style>
    #shifttable {
        margin-top: 5px;
        border-radius: 5px !important;
    }
    </style>
    <?php } else { ?>
    <link rel="stylesheet" href="<?= $assets ?>pos/css/default-inline.css" type="text/css" />
    <style>
    #shifttable {
        margin-top: 0px;
    }
    </style>
    <?php } ?>

    <link rel="stylesheet" href="<?= $assets ?>pos/css/print.css" type="text/css" media="print" />
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
    <script src="<?= $assets ?>pos/js/jquery.validate.min.js"></script>
    <!-- Owl Stylesheets -->
    <link rel="stylesheet" href="<?= $assets ?>owl-slider/owlcarousel/assets/owl.carousel.min.css">

    <!--[if lt IE 9]>
        <script src="<?= $assets ?>js/jquery.js"></script>
        <![endif]-->
    <?php if ($Settings->user_rtl) { ?>
    <link href="<?= $assets ?>styles/helpers/bootstrap-rtl.min.css" rel="stylesheet" />
    <link href="<?= $assets ?>styles/style-rtl.css" rel="stylesheet" />
    <script type="text/javascript">
    $(document).ready(function() {
        $('.pull-right, .pull-left').addClass('flip');
    });
    </script>
    <?php }
        ?>
    <style>
    #paymentModal #s2id_paid_by_1,
    #paymentModal #s2id_paid_by_1 a {
        pointer-events: none !important;
        cursor: none !important;
    }

    .notification_counter {
        color: #ff0000;
        font-weight: bold;
        border: 1px solid;
        padding: 2px 4px;
        margin: 5px;
        border-radius: 12%
    }

    #posbiller {
        display: block !important;
    }

    .btn-prni {
        background: <?php echo ( !empty($pos_settings->pos_theme->css_class_product->background_color)) ? $pos_settings->pos_theme->css_class_product->background_color: '#fff';
        ?>
    }

    /*            .second{height: 82px !important}
                        .second .owl-stage-outer{overflow-y: visible;height: 81px !important;}*/
    #brands-list {
        overflow-y: scroll;
    }

    .del_btn_group {
        display: none !important;
    }


    .urbanpiper-stock_notify {
        position: absolute;
        top: 50px;

        width: 350px;
        z-index: 55555;
        -webkit-box-shadow: 0px 5px 10px 0px rgba(102, 102, 102, 1);
        -moz-box-shadow: 0px 5px 10px 0px rgba(102, 102, 102, 1);
        box-shadow: 0px 5px 10px 0px rgba(102, 102, 102, 1);
        display: block;
    }

    .alert_notify {
        position: absolute;
        top: 50px;
        right: 10px;
        width: 350px;
        z-index: 55555;
        -webkit-box-shadow: 0px 5px 10px 0px rgba(102, 102, 102, 1);
        -moz-box-shadow: 0px 5px 10px 0px rgba(102, 102, 102, 1);
        box-shadow: 0px 5px 10px 0px rgba(102, 102, 102, 1);
        display: block;
    }


    @media print {
        .noprint {
            display: none;
        }
    }
    </style>


    <script type="text/javascript">
    <?php if (isset($featuerd_products) && ($featuerd_products == 1)): ?>
    $('#previous').css('display', 'none');
    $('#next').css('display', 'none');
    <?php else: ?>
    $('#previous1').css('display', 'none');
    $('#next1').css('display', 'none');
    <?php endif; ?>
    </script>

    <?php $logopath = base_url("assets/icons/") ?>
    <link rel="apple-touch-icon" sizes="57x57" href="<?= $logopath ?>apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?= $logopath ?>apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= $logopath ?>apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= $logopath ?>apple-icon-76x76.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= $logopath ?>android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $logopath ?>favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= $logopath ?>favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $logopath ?>favicon-16x16.png">
    <!--  <link rel="manifest" href="<?= $logopath ?>manifest.json"> -->
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?= $logopath ?>ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>

<body>

    <audio id="myAudio">
        <source src="alertsound.ogg" type="audio/ogg">
        <source src="<?= $assets ?>sounds/alertsound.mp3" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>

    <noscript>
        <div class="global-site-notice noscript">
            <div class="notice-inner">
                <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled
                    in
                    your browser to utilize the functionality of this website.</p>
            </div>
        </div>
    </noscript>

    <div id="wrapper">
        <header id="header" class="navbar">
            <div class="container">
                <?php
                    $pos_res = json_decode($Settings->pos_version, true);
                    $pos_ver = $pos_res['version'];
                    ?>
                <!-- <a class="navbar-brand" href="<?= site_url() ?>"><span class="logo"><span class="pos-logo-lg"><?= $Settings->site_name ?></span><span class="pos-logo-sm"><?= $Settings->site_name; //lang('pos')               ?></span></span>--->
                <!--                        <sub style="text-transform: capitalize;"><?= $Settings->pos_type . " (ver. $pos_ver)" ?></sub></a>-->

                <div class="header-nav">
                    <ul class="nav navbar-nav pull-right">
                        <li class="dropdown">
                            <a class="btn no-effect account dropdown-toggle" data-toggle="dropdown" href="#">
                                <img alt=""
                                    src="<?= $this->session->userdata('avatar') ? site_url() . 'assets/mdata/'.$Customer_assets.'/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : $assets . 'images/male.png'; ?>"
                                    class="mini_avatar img-rounded">

                                <div class="user">
                                    <span><?= lang('welcome') ?>!
                                        <?= $this->session->userdata('username'); ?></span><br>
                                    <sub class="subtext"><?= $Settings->site_name . " (ver. $pos_ver)" ?></sub>
                                </div>
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="<?= site_url('auth/profile/' . $this->session->userdata('user_id')); ?>">
                                        <i class="fa fa-user"></i> <?= lang('profile'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="<?= site_url('auth/profile/' . $this->session->userdata('user_id') . '/#cpassword'); ?>">
                                        <i class="fa fa-key"></i> <?= lang('change_password'); ?>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <?php if ($Owner || $Admin) { ?>
                                <li>
                                    <a href="javascript:void(0)" onclick="restBill()">
                                        <i class="fa fa-sign-out"></i> <?= lang('Rest & logout'); ?>
                                    </a>
                                </li>
                                <?php } ?>
                                <li>
                                    <a href="<?= site_url('auth/logout/0'); ?>">
                                        <i class="fa fa-sign-out"></i> <?= lang('logout'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>

                    <ul class="nav navbar-nav pull-right">
                        <!--<li class="dropdown">
                                                            <a class="btn bdarkGreen pos-tip" id="pos_details" title="<?= lang('recent_pos_list') ?>" data-placement="bottom" data-html="true" href="<?= site_url('pos/recent_pos_list'); ?>" data-toggle="modal" data-target="#myModal">
                                                                <i class="fa fa-list"></i>
                                                            </a>
                                                        </li>
                                                        <li class="dropdown">
                                                            <a class="btn blightOrange pos-tip" id="opened_bills" title="<?= lang('suspended_sales') ?>" data-placement="bottom" data-html="true" href="<?= site_url('pos/opened_bills') ?>" data-toggle="ajax">
                                                                <img src="<?= $assets ?>images/icon-spe.png" alt="suspended_sales" ><span  class="notification_counter"><?php echo isset($opend_bill_count_custom) ? $opend_bill_count_custom : '' ?></span>
                                                            </a>
                                                        </li>--->
                        <li class="dropdown hidden-xs">
                            <a class="btn bblue pos-tip" style="border-radius: 20px !important;"
                                title="<?= lang('products below alert quantity') ?>" data-placement="bottom" id=""
                                href="<?= site_url('products/index/0/alert_qty') ?>">
                                <i class="fa fa-bell"></i><sup><?php echo $alertProd_Count['count']; ?></sup>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn blightOrange pos-tip" id="opened_bills" title="<?= lang('suspended_sales') ?>"
                                data-placement="bottom" data-html="true" href="<?= site_url('pos/opened_bills') ?>"
                                data-toggle="ajax">
                                <img src="<?= $assets ?>images/icon-spe.png" alt="suspended_sales"><span
                                    class="notification_counter"><?php echo isset($opend_bill_count_custom) ? $opend_bill_count_custom : '' ?></span>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" id="pos_details" title="<?= lang('recent_pos_list') ?>"
                                data-placement="bottom" data-html="true" href="<?= site_url('pos/recent_pos_list'); ?>"
                                data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-list"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn bblue pos-tip" title="<?= lang('dashboard') ?>" data-placement="bottom"
                                href="<?= site_url('welcome') ?>">
                                <i class="fa fa-dashboard"></i>
                            </a>
                        </li>
                        <?php
                            if ($Owner) {
                                if ($Settings->theme == 'default') {
                                    ?>
                        <li class="dropdown hidden-sm">
                            <a class="btn blightOrange pos-tip" id="pos_setting" title="<?= lang('settings') ?>"
                                data-placement="bottom" href="<?= site_url('pos/settings') ?>">
                                <i class="fa fa-cogs"></i>
                            </a>
                        </li>
                        <?php } else { ?>
                        <li class="dropdown hidden-sm">
                            <a href="<?= site_url('pos/short_setting'); ?>" id=""
                                class=" btn blightOrange pos-tip external" data-placement="bottom"
                                title="<?= lang('settings') ?>" data-toggle="modal" data-target="#myModal"
                                tabindex="-1">
                                <i class="fa fa-cogs"></i>
                            </a>
                        </li>
                        <?php
                                }
                            }
                            ?>
                        <!--li class="dropdown hidden-xs">
                                <a class="btn bdarkGreen pos-tip" title="<?= lang('calculator') ?>" data-placement="bottom" href="#" data-toggle="dropdown">
                                    <i class="fa fa-calculator"></i>
                                </a>
                                <ul class="dropdown-menu pull-right calc">
                                    <li class="dropdown-content">
                                        <span id="inlineCalc"></span>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown hidden-sm">
                                <a class="btn borange pos-tip" id="pos_shortcuts" title="<?= lang('shortcuts') ?>" data-placement="bottom" href="#" data-toggle="modal" data-target="#sckModal">
                                    <i class="fa fa-key"></i>
                                </a>
                            </li>
                            <li class="dropdown">
                                <a class="btn bblue pos-tip" id ="pos_view" title="<?= lang('view_bill_screen') ?>" data-placement="bottom" href="<?= site_url('pos/view_bill') ?>" target="_blank">
                                    <i class="fa fa-laptop"></i>
                                </a>
                            </li-->
                        <?php if ($Owner || $Admin || $GP['sales-add_gift_card']) { ?>
                        <li class="dropdown">
                            <!--a class="btn bblue pos-tip" id="sellGiftCard"  title="<?= lang('Sell Gift Card') ?>" data-placement="bottom" href="<?= site_url('pos/view_bill') ?>" target="_blank">
                    <i class="fa fa-credit-card"></i>
                </a-->
                            <button class="btn bblue pos-tip" type="button" id="sellGiftCard"
                                title="<?= lang('sell_gift_card') ?>">
                                <i class="fa fa-credit-card"></i>
                            </button>
                        </li>
                        <?php } ?>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" id="register_details"
                                title="<?= lang('register_details') ?>" data-placement="bottom" data-html="true"
                                href="<?= site_url('pos/register_details') ?>" data-toggle="modal"
                                data-target="#myModal">
                                <i class="fa fa-file-text"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn borange pos-tip" id="close_register" title="<?= lang('close_register') ?>"
                                data-placement="bottom" data-html="true" href="<?= site_url('pos/close_register') ?>"
                                data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-times-circle"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn bblue pos-tip" id="add_expense" title="<?= lang('add_expense') ?>"
                                data-placement="bottom" data-html="true" href="<?= site_url('purchases/add_expense') ?>"
                                data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-money"></i>
                            </a>
                        </li>
                        <?php if ($Owner) { ?>
                        <li class="dropdown">
                            <a class="btn blightOrange pos-tip" id="today_profit" title="<?= lang('today_profit') ?>"
                                data-placement="bottom" data-html="true" href="<?= site_url('reports/profit') ?>"
                                data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-line-chart"></i>
                            </a>
                        </li>
                        <?php }
                            ?>

                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" id="today_sale" title="<?= lang('today_sale') ?>"
                                data-placement="bottom" data-html="true" href="<?= site_url('pos/today_sale') ?>"
                                data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-tags"></i>
                            </a>
                        </li>
                        <?php if ($Owner || $Admin) { ?>
                        <li class="dropdown hidden-xs book">
                            <a class="btn borange pos-tip" title="<?= lang('list_open_registers') ?>"
                                data-placement="bottom" href="<?= site_url('pos/registers') ?>">
                                <i class="fa fa-book"></i>
                            </a>
                        </li>
                        <li class="dropdown hidden-xs">
                            <a class="btn bblue pos-tip" title="<?= lang('clear_ls') ?>" data-placement="bottom"
                                id="clearLS" href="#">
                                <i class="fa fa-trash"></i>
                            </a>
                        </li>
                        <?php
                            }

                            if ($Settings->pos_type == 'restaurant') {
                                ?>

                        <li class="dropdown cutlery">
                            <a class="btn bdarkGreen pos-tip" title="Kitchen Printer" data-placement="bottom"
                                rel="external" onclick="window.open(this.href, '_new');
                                            return false;" href="<?= site_url('screens/display/1') ?>">
                                <i class="fa fa-cutlery" aria-hidden="true"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a class="btn borange pos-tip" title="Bar Printer" data-placement="bottom" target="_blank"
                                href="<?= site_url('screens/display/2') ?>">
                                <i class="fa fa-glass" aria-hidden="true"></i>
                            </a>
                        </li>
                        <?php } ?>

                        <li class="dropdown">
                            <a class="btn no-effect themeicon dropdown-toggle" data-placement="bottom"
                                title="<?= lang('Theme Setting') ?>" data-toggle="dropdown" href="#"><i
                                    class="fa fa-magic"></i></a>
                            <ul class="dropdown-menu pull-right padding0">
                                <?php foreach ($post_theme as $pt) { ?>
                                <li onclick="change_theme('<?= $pt->theme_name; ?>')"><a class="pointer">
                                        <?= ucfirst($pt->theme_label); ?> </a></li>
                                <?php } ?>
                            </ul>
                        </li>

                        <li class="dropdown">
                            <a class="btn no-effect themeicon1 dropdown-toggle" data-placement="bottom"
                                title="<?= lang('New Offers') ?>" data-toggle="dropdown" href="#"><i
                                    class="fa fa-list-alt"></i></a>

                            <ul class="dropdown-menu pull-right padding0">
                                <li><a style=" background: #000;color:#fff;"> <?= $active_offers_category ?></a></li>
                                <?php foreach ($active_offers as $offers) { ?>
                                <li onclick="change_offerdetails('<?= $offers->id; ?>')"> <a class="pointer"
                                        id="offer_detail" data-toggle="modal" data-target="#offer_modal">
                                        <?= ucfirst($offers->offer_name) ?></a></li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>

                    <?php if ($pos_settings->display_time) { ?>
                    <ul class="nav navbar-nav pull-right display_time">
                        <li class="dropdown">
                            <a class="no-effect bblack timer"><span id="display_time"></span></a>
                        </li>
                    </ul>
                    <?php } ?>


                    <?php if ($Settings->pos_type == 'restaurant') { ?>
                    <ul class="nav navbar-nav "
                        style="padding: 7px; background: coral; font-size: larger; font-weight: bold;">
                        <li> Table : <strong id="active_table">--</strong></li>
                    </ul>
                    <?php } ?>
                </div>
            </div>
        </header>
        <nav class="menu-opener noprint">
            <div class="menu-opener-inner"><i class="fa fa-angle-left"></i></div>
        </nav>
        <div class="rotate  btn-cat-con">
            <!--button type="button" id="open-category" class="btn btn-primary open-category fa fa-tag">
                        <p><?= lang('categories'); ?></p>
                </button>
                <button type="button" id="open-subcategory" class="btn btn-warning open-subcategory fa fa-tags">
                        <p><?= lang('subcategories'); ?></p>
                </button-->
            <button type="button" id="open-brands" class="btn btn-info open-brands fa fa-thumb-tack">
                <p><?= lang('brands'); ?></p>
            </button>
            <button type="button" onclick="return actQRCam()" class="btn btn-info qr-code open-brands">
                <p>QR Code</p>
            </button>
            <button type="button" id="customer_button" aria-hidden="true"
                class="btn btn-warning customer_button fa fa-user">
                <p>Customer</p>
            </button>
            <button type="button" id="addManually" class="btn btn-warning quick-product fa fa-truck">
                <p>Quick Sale</p>
            </button>

            <?php if ($Settings->pos_type == 'restaurant' && $pos_settings->combo_add_pos) { ?>
            <button type="button" href="<?= base_url('products/comboproduct') ?>" data-toggle="modal"
                data-target="#myModal" class="btn btn-warning quick-product fa fa-archive">
                <p>Add Combo</p>
            </button>
            <?php } ?>

            <?php if ($Owner || $Admin || $GP['sales-add_gift_card']) { ?>
            <button class="sellGiftCard btn btn-primary fa fa-credit-card" type="button" id="sellGiftCard"
                title="<?= lang('sell_gift_card') ?>">
                <p><?= lang('sell_gift_card') ?></p>
            </button>
            <?php } ?>
            <button type="button" onClick="window.open('http://localhost/offline/')" id=""
                class="btn btn-info Offline fa fa-refresh">
                <p>Offline</p>
            </button>

            <script>
            function calculateTotals(pi) {
                var total_paying = 0;
                var ia = $(".amount");
                $.each(ia, function(i) {
                    var this_amount = formatCNum($(this).val() ? $(this).val() : 0);
                    total_paying += parseFloat(this_amount);
                });
                $('#total_paying').text(formatMoney(total_paying));
                <?php if ($pos_settings->rounding) { ?>
                $('#balance').text(formatMoney(total_paying - round_total));
                $('#balance_' + pi).val(formatDecimal(total_paying - round_total));
                total_paid = total_paying;
                grand_total = round_total;
                <?php } else { ?>
                $('#balance').text(formatNumber(total_paying - gtotal));
                $('#balance_' + pi).val(formatDecimal(total_paying - gtotal));
                total_paid = total_paying;
                grand_total = gtotal;
                <?php } ?>
            }
            $(document).ready(function() {
                $("#customer_button").click(function() {
                    $("#s2id_poscustomer, #sales_personId").toggle();
                });
                /*  $("#customer_button").click(function () {
                 $(".first_menu").toggle();
                 });*/
                $("#customer_button").click(function() {
                    $(".second_menu").toggle();
                });
                $("#customer_button").click(function() {
                    $(".third_menu").toggle();
                });
                $("#customer_button").click(function() {
                    $("#patient_name").toggle();
                });
                $("#customer_button").click(function() {
                    $("#doctor_name").toggle();
                });
            });
            $(".menu-opener").click(function() {
                $(".menu-opener, .menu-opener-inner, .btn-cat-con").toggleClass("active");
            });
            </script>
        </div>
        <div id="content">
            <div class="c1">
                <div class="pos">
                    <div class="alert alert-success" style="display:none;" id='offermsg'></div>
                    <div class="alert alert-success" style="display:none;" id='successmsg'></div>
                    <div id="urbanpiper-order-alert"></div>
                    <?php if ($Settings->theme != 'default') { ?>
                    <div class="alert alert-danger" id="errormsg"><button type="button" class="close fa-2x"
                            id="msgclose">&times;</button><span id="error_msg"></span></div>
                    <?php } ?>
                    <?php
                        if ($error) {
                            echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close fa-2x\" data-dismiss=\"alert\">&times;</button>" . $error . "</div>";
                            /* To set error log on cloud */
//                            $errorUrl = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//                            $logger = array($error, $errorUrl);
//                            $this->sma->pos_error_log($logger);
                            /* End To set error log on cloud */
                        }
                        ?>
                    <?php
                        if ($message) {
                            echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close fa-2x\" data-dismiss=\"alert\">&times;</button>" . $message . "</div>";
                        }
                        ?>
                    <div id="pos">
                        <?php
                            $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'pos-sale-form');
                            echo form_open("pos", $attrib);
                            ?>
                        <div id="leftdiv">
                            <div id="printhead">
                                <h4 class="textucase"><?php echo $Settings->site_name; ?></h4>
                                <?php
                                    echo "<h5 class=\"textucase\">" . $this->lang->line('order_list') . "</h5>";
                                    echo $this->lang->line("date") . " " . $this->sma->hrld(date('Y-m-d H:i:s'));
                                    ?>
                            </div>
                            <!--removed-->
                            <input type="hidden" name="events_data" id="events_data">
                            <input type="hidden" name="profile_details" id="profile_details">
                            <input type="hidden" value="" name="customer1" id="custname" />
                            <input type="hidden" value="" name="cust_search" id="custsearch" />
                            <div id="print">
                                <div id="left-middle">
                                    <div id="product-list" style="padding-bottom: 3em !important;">
                                        <input type="hidden" value="<?= $GP['cart-unit_view']; ?>"
                                            name="per_cartunitview" id="per_cartunitview" />
                                        <input type="hidden" value="<?= $GP['cart-price_edit']; ?>"
                                            name="per_cartpriceedit" id="per_cartpriceedit" />
                                        <input type="hidden" value="<?= $Owner; ?>" name="per_owner"
                                            id="permission_owner" />
                                        <input type="hidden" value="<?= $Admin; ?>" name="per_admin"
                                            id="permission_admin" />
                                        <input type="hidden" value="<?= $Settings->add_tax_in_cart_unit_price; ?>"
                                            name="add_tax_in_cart_unit_price" id="add_tax_in_cart_unit_price" />
                                        <input type="hidden" value="<?= $Settings->add_discount_in_cart_unit_price; ?>"
                                            name="add_discount_in_cart_unit_price"
                                            id="add_discount_in_cart_unit_price" />
                                        <input type="hidden" value="<?= $pos_settings->change_qty_as_per_user_price; ?>"
                                            name="change_qty_as_per_user_price" id="change_qty_as_per_user_price" />

                                        <input type="hidden" name="Current_Date" id="Current_Date"
                                            value="<?php echo date('Y-m-d'); ?>">
                                        <table
                                            class="table items table-striped table-bordered table-condensed table-hover"
                                            id="posTable" style="margin-bottom: 50;">
                                            <thead>
                                                <tr>
                                                    <th width="35%"><?= lang("product"); ?></th>
                                                    <th width="15%"><?= lang("price"); ?></th>
                                                    <th width="25%"><?= lang("qty"); ?></th>
                                                    <?php if ($Owner || $Admin || $GP['cart-unit_view']) { ?>
                                                    <th width="10%"><?= lang("unit"); ?></th>
                                                    <?php } ?>
                                                    <th width="15%"><?= lang("Sub total"); ?></th>
                                                    <th class="width5">
                                                        <i class="fa fa-trash-o"></i>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="clear"></div>
                                    <div id="left-bottom">
                                        <table id="totalTable">
                                            <tr>
                                                <td class="tdpaddingborder"><?= lang('items'); ?></td>
                                                <td class="text-right tdpaddingborder font-weight-bold">
                                                    <span id="titems">0</span>
                                                </td>
                                                <td class="tdpaddingborder"><?= lang('total'); ?></td>
                                                <td class="text-right tdpaddingborder font-weight-bold">
                                                    <span id="total">0.00</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="tdpadding"><?= lang('order_tax'); ?>
                                                    <a href="#" id="pptax2">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                </td>
                                                <td class="text-right tdpadding font-weight-bold">
                                                    <span id="ttax2">0.00</span>
                                                </td>
                                                <td class="tdpadding"><?= lang('discount'); ?>
                                                    <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                                                    <a href="#" id="ppdiscount">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-right tdpadding font-weight-bold">
                                                    <span id="tds">0.00</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 5px 10px; border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;"
                                                    colspan="2">
                                                    <?= lang('total_payable'); ?>
                                                </td>
                                                <td class="text-right"
                                                    style="padding:5px 10px 5px 10px; font-size: 14px;border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;"
                                                    colspan="2">
                                                    <span id="gtotal">0.00</span>
                                                </td>
                                            </tr>
                                        </table>

                                        <div class="clearfix"></div>
                                        <div id="botbuttons" class="col-xs-12 text-center">
                                            <input type="hidden" name="biller" id="biller"
                                                value="<?= ($Owner || $Admin || !$this->session->userdata('biller_id')) ? $pos_settings->default_biller : $this->session->userdata('biller_id') ?>" />
                                            <div class="row" style="margin-bottom:6px;">
                                                <div class="col-xs-8" style="padding:0 10px 0 0;">
                                                    <div class="btn-group-vertical btn-block">
                                                        <input type="text" name="coupon_code" id="coupon_code"
                                                            class="form-control" placeholder="Enter Coupon Code"
                                                            autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-xs-4 padding0">
                                                    <div class="btn-group-vertical btn-block">
                                                        <input type="button" name="apply_coupon" id="apply_coupon"
                                                            value="Apply Coupon" class="btn btn-info btn-block"
                                                            onclick="return applyCoupon();">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row" style="margin-bottom:6px;">
                                                <div class="col-xs-12" style="padding:0 10px 0 0; display:none;"
                                                    id="discription_input_block">

                                                    <div class="btn-group-vertical btn-block">
                                                        <input type="text" name="pos_note" id="note"
                                                            class="form-control" placeholder="Description"
                                                            autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 padding0" style="padding:0 10px 0 0;"
                                                    id="discription_btn_block">
                                                    <div class="btn-group-vertical btn-block">
                                                        <input type="button" id="DescriptionBtn" value="Description"
                                                            class="btn btn-info btn-block">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-xs-4 padding0">
                                                    <div class="btn-group-vertical btn-block">
                                                        <?php if ($Settings->pos_type == 'restaurant') { ?>
                                                        <button type="button" id="suspend"
                                                            class="btn btn-warning btn-block btn-flat"
                                                            onclick="window.location.href = '<?= base_url('pos/kot') ?>'">
                                                            KOT </button>
                                                        <?php } else { ?>
                                                        <button type="button" class="btn btn-warning btn-block btn-flat"
                                                            id="suspend"> Suspend </button>
                                                        <?php } ?>
                                                        <button type="button" id="suspend_sale1"
                                                            class="btn btn-warning btn-block btn-flat  "
                                                            style="display:none;">Save</button>

                                                        <button type="button" class="btn btn-danger btn-block btn-flat"
                                                            id="reset" style="width: 50%;float: left;">
                                                            <?= lang('cancel'); ?>
                                                        </button>
                                                        <button type="button" class="btn btn-warning btn-block btn-flat"
                                                            id="shifttable" style="width: 50%;float: left; ">
                                                            <?= lang('Shift'); ?>
                                                        </button>
                                                    </div>

                                                </div>
                                                <div class="col-xs-4 padding0">
                                                    <div class="btn-group-vertical btn-block">
                                                        <?php if ($Owner || $Admin || $GP['pos-show-order-btn']) { ?>
                                                        <button type="button" class="btn btn-info btn-block"
                                                            id="print_order">
                                                            <?= lang('order'); ?>
                                                        </button>
                                                        <?php } else { ?>
                                                        <button type="button"
                                                            class="btn btn-default btn-block">&nbsp;</button>
                                                        <?php } ?>
                                                        <?php if ($Owner || $Admin || $GP['cart-show_bill_btn']) { ?>
                                                        <button type="button" class="btn btn-primary btn-block"
                                                            id="print_bill">
                                                            <?= lang('bill'); ?>
                                                        </button>
                                                        <?php } else { ?>
                                                        <button type="button"
                                                            class="btn btn-default btn-block">&nbsp;</button>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <?php if ($pos_settingss->display_seller == 0) { ?>
                                                <div class="col-xs-4 padding0">
                                                    <?php if ($Owner || $Admin || $GP['checkout']) { ?>
                                                    <button type="button" class="btn btn-success btn-block"
                                                        id="payment">
                                                        <i
                                                            class="fa fa-money marginright5"></i><?php /* lang('payment'); */ ?>Checkout
                                                    </button>
                                                    <?php } ?>
                                                </div>
                                                <?php } ?>
                                                <?php if ($pos_settingss->display_seller == 1) { ?>
                                                <div class="col-xs-4 padding0">
                                                    <?php if ($Owner || $Admin || $GP['checkout']) { ?>
                                                    <button type="button" class="btn btn-success btn-block"
                                                        id="payment">
                                                        <i class="fa fa-money marginright5"></i>Checkout
                                                    </button>
                                                    <?php } ?>
                                                </div>
                                                <?php } ?>

                                                <?php if ($pos_settingss->display_seller == 2) { ?>
                                                <div class="col-xs-4 padding0">
                                                    <?php if ($Owner || $Admin || $GP['checkout']) { ?>
                                                    <button type="button" class="btn btn-success btn-block" id="payment"
                                                        onclick="validateCheckout()">
                                                        <i class="fa fa-money marginright5"></i>Checkout
                                                    </button>
                                                    <?php } ?>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="clear height5"></div>
                                        <div id="num">
                                            <div id="icon"></div>
                                        </div>
                                        <span id="hidesuspend"></span>
                                        <input type="hidden" name="pos_sale_person" value="0" id="pos_sale_person">
                                        <!--<input type="hidden" name="pos_note" value="" id="pos_note"> -->
                                        <input type="hidden" name="staff_note" value="" id="staff_note">
                                        <input type="hidden" name="offer_category" value="" id="offer_category">
                                        <input type="hidden" name="offer_on_category" value="" id="offer_on_category">
                                        <input type="hidden" name="offer_description" value="" id="offer_description">

                                        <input type="hidden" name="suspendsale"
                                            value="<?= $this->uri->segment(4) == 'suspendsale' ? 1 : 0; ?>"
                                            id="suspendsale">

                                        <div id="payment-con">
                                            <?php for ($i = 1; $i <= 5; $i++) { ?>
                                            <input type="hidden" name="amount[]" id="amount_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="ap[]" id="ap_val_<?= $i ?>">
                                            <input type="hidden" name="balance_amount[]" id="balance_amount_<?= $i ?>"
                                                value="" />
                                            <input type="hidden" name="paid_by[]" id="paid_by_val_<?= $i ?>"
                                                value="cash" />
                                            <input type="hidden" name="cc_no[]" id="cc_no_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="paying_gift_card_no[]"
                                                id="paying_gift_card_no_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="cc_holder[]" id="cc_holder_val_<?= $i ?>"
                                                value="" />
                                            <input type="hidden" name="cheque_no[]" id="cheque_no_val_<?= $i ?>"
                                                value="" />
                                            <input type="hidden" name="other_tran[]" id="other_tran_no_val<?= $i ?>"
                                                value="" />
                                            <input type="hidden" name="other_tran_mode[]"
                                                id="other_tran_mode_val<?= $i ?>" value="" />
                                            <input type="hidden" name="cc_month[]" id="cc_month_val_<?= $i ?>"
                                                value="" />
                                            <input type="hidden" name="cc_year[]" id="cc_year_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="cc_type[]" id="cc_type_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="cc_cvv2[]" id="cc_cvv2_val_<?= $i ?>" value="" />
                                            <input type="hidden" name="payment_note[]" id="payment_note_val_<?= $i ?>"
                                                value="" />
                                            <input type="hidden" name="cc_transac_no[]" id="cc_transac_no_val<?= $i ?>"
                                                value="" />
                                            <?php }
                                                ?>
                                        </div>
                                        <input name="order_tax" type="hidden"
                                            value="<?= $suspend_sale ? $suspend_sale->order_tax_id : $Settings->default_tax_rate2; ?>"
                                            id="postax2">
                                        <input name="discount" type="hidden"
                                            value="<?= $suspend_sale ? $suspend_sale->order_discount_id : ''; ?>"
                                            id="posdiscount">

                                        <input type="hidden" name="rpaidby" id="rpaidby" value="cash"
                                            class="nodisplay" />
                                        <input type="hidden" name="total_items" id="total_items" class="nodisplay"
                                            value="0" />
                                        <input type="submit" id="submit_sale" value="Submit Sale" class="nodisplay" />

                                        <input type="hidden" name="paynear_mobile_app" id="paynear_mobile_app"
                                            value="" />
                                        <input type="hidden" name="paynear_mobile_app_type" id="paynear_mobile_app_type"
                                            value="" />
                                        <?php /* ------ For checking Print/notPrint Button updated by SW 21/01/2017 --------------- */ ?>
                                        <input type="hidden" name="submit_type" id="submit_type" value="">
                                        <?php if ($is_pharma): ?>
                                        <input type="hidden" name="patient_name" id="patient_name1" value="">
                                        <input type="hidden" name="doctor_name" id="doctor_name1" value="">
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <?php echo form_close(); ?>
                        <div id="cp">

                            <div id="cpinner">
                                <!--search-->
                                <div id="left-top">
                                    <div
                                        style="position: absolute; <?= $Settings->user_rtl ? 'right:-9999px;' : 'left:-9999px;'; ?>">
                                        <?php echo form_input('test', '', 'id="test" class="kb-pad"'); ?></div>
                                    <div class="form-group">
                                        <?php if ($Settings->theme != 'default') { ?>
                                        <div class="row">
                                            <div class="<?php
                                                    if ($pos_settingss->display_seller == 1)
                                                        echo 'col-sm-9';
                                                    else
                                                        echo 'col-sm-9';
                                                    ?> ">
                                                <div class="row">
                                                    <div class="col-sm-5 ">
                                                        <div class="input-group">
                                                            <input type="text" pattern="[0-9]+" min="10" maxlength="10"
                                                                placeholder="Search Customer Mobile No."
                                                                id="search_customer"
                                                                class=" form-control kb-pad txt-box ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted" />
                                                            <div id="sales_icon"
                                                                class="input-group-addon first_menu no-print">
                                                                <span disabled="true" style="padding:8px;"
                                                                    onclick="searchCustomer()" id="searchicon"
                                                                    class="search-btn">
                                                                    <i class="fa fa-search" id="addIcon"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <span class="text-danger" id="error_message"></span>
                                                    </div>
                                                    <div class="col-sm-7">
                                                        <div class="input-group">
                                                            <input type="hidden" name="customer_group"
                                                                id="customer_group"
                                                                value="<?= $customer->customer_group_id; ?>">
                                                            <input type="hidden" class="nodisplay" name="customer"
                                                                id="poscustomer"
                                                                value="<?= ($_SESSION['quick_customerid'] ) ? $_SESSION['quick_customerid'] : $get_cmp->id; ?>" />
                                                            <input type="text" placeholder="Customer Name"
                                                                name="customer_name"
                                                                class="form-control kb-text txt-box ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted"
                                                                id="customer_name"
                                                                value="<?= ($_SESSION['quick_customername'] ) ? $_SESSION['quick_customername'] . '(' . $_SESSION['quick_customerphone'] . ')' : $get_cmp->name; ?>" />
                                                            <div class="input-group-addon padding28" id="customebtn"
                                                                title="WALK-IN CUSTOMER">
                                                                <a href="#" id="view-customer"
                                                                    class="external text-primary" data-toggle="modal">
                                                                    <i class="fa fa-eye fa-lg iconcolor"
                                                                        aria-hidden="true"></i>
                                                                </a>
                                                                <a style="display:none;outline: none; float: inherit;"
                                                                    id="AddNewCustomer"><i
                                                                        class="fa fa-floppy-o fa-lg text-info"
                                                                        aria-hidden="true"> </i></a>
                                                            </div>
                                                            <?php
                                                                    if ($pos_settings->add_deposit_btn_show) {
                                                                        if ($Owner || $Admin || $GP['customers-deposits']) {
                                                                            ?>
                                                            <div class="input-group-addon padding28"
                                                                id="customer_diposit" title="CUSTOMER DEPOSIT">
                                                                <a class="external text-primary"
                                                                    id="customer_deposit_link" title="CUSTOMER DEPOSIT"
                                                                    href="#" data-toggle="modal" data-target="#myModal"
                                                                    data-original-title="Add Deposit">
                                                                    <i class="fa fa-money fa-lg iconcolor"
                                                                        aria-hidden="true"></i>
                                                                </a>
                                                            </div>
                                                            <?php }
                                                                    }
                                                                    ?>
                                                            <div class="input-group-addon padding28 roundbtn"
                                                                title="ADD CUSTOMER">
                                                                <a href="customers/add_quick" id="add-customer"
                                                                    class="external" data-toggle="modal"
                                                                    data-target="#myModal" tabindex="-1">
                                                                    <i class="fa fa-plus-circle fa-lg iconcolor"
                                                                        aria-hidden="true"></i>
                                                                </a>
                                                                <a id="scustomer"
                                                                    style="display:none; float: inherit; outline: none;"><i
                                                                        class="fa fa-check  fa-lg text-success"
                                                                        aria-hidden="true"></i></a>
                                                                <img id="loaderimg"
                                                                    style="display:none; float: inherit; outline: none;"
                                                                    src="<?= base_url() ?>/themes/blueorange/assets/img/loading.gif" />

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3"
                                                style='width:25%!important; <?php if ($pos_settingss->display_seller == 0) { ?>display:none; <?php } ?> '>
                                                <div class="row ">
                                                    <div class="col-sm-12 ">
                                                        <div class="select-group">

                                                            <select name="sales_person" id="sales_person"
                                                                class="form-control select_border"
                                                                onchange="return getSellerDetails(this.value);">
                                                                <option value="0">Select Sales Person</option>
                                                                <?php foreach ($salesperson_details as $key_salesperson) { ?>
                                                                <option
                                                                    value="<?php echo $key_salesperson['id'] . '-' . $key_salesperson['name']; ?>">
                                                                    <?php echo $key_salesperson['name']; ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } else { ?>
                                        <div class="row">
                                            <div class="<?php
                                                    if ($pos_settingss->display_seller == 1)
                                                        echo 'col-sm-9';
                                                    else
                                                        echo 'col-sm-12';
                                                    ?> ">
                                                <div class="input-group mob">
                                                    <?php
                                                            echo form_input('customer', (($_SESSION['quick_customername'] ) ? $_SESSION['quick_customername'] . '(' . $_SESSION['quick_customerphone'] . ')' : $_GET['customer']), 'id="poscustomer"   data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" autofocus required="required" name="name_s2id_poscustomer" class="form-control pos-input-tip" style="width:100%;"');
                                                            ?>
                                                    <div id="sales_icon" class="input-group-addon first_menu no-print">
                                                        <a href="#" id="toogle-customer-read-attr" class="external">
                                                            <i class="fa fa-pencil iconcolor" id="addIcon"></i>
                                                        </a>
                                                    </div>
                                                    <div id="sales_icon" class="input-group-addon second_menu no-print">
                                                        <a href="#" id="view-customer" class="external"
                                                            data-toggle="modal" data-target="#myModal">
                                                            <i class="fa fa-eye iconcolor" id="addIcon"></i>
                                                        </a>
                                                    </div>
                                                    <?php
                                                            if ($pos_settings->add_deposit_btn_show) {
                                                                if ($Owner || $Admin || $GP['customers-deposits']) {
                                                                    ?>
                                                    <div class="input-group-addon padding28" id="customer_diposit"
                                                        title="CUSTOMER DEPOSIT">
                                                        <a class="external text-primary"
                                                            id="customer_deposit_link_default" title="CUSTOMER DEPOSIT"
                                                            href="#" data-toggle="modal" data-target="#myModal"
                                                            data-original-title="Add Deposit">
                                                            <i class="fa fa-money fa-lg iconcolor"
                                                                aria-hidden="true"></i>
                                                        </a>
                                                    </div>
                                                    <?php }
    }
    ?>
                                                    <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
                                                    <div id="sales_icon" class="input-group-addon third_menu no-print">
                                                        <a href="<?= site_url('customers/add_quick'); ?>"
                                                            id="add-customer" class="external" data-toggle="modal"
                                                            data-target="#myModal">
                                                            <i class="fa fa-plus-circle iconcolor" id="addIcon"></i>
                                                        </a>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-3" id="sales_personId"
                                                <?php if ($pos_settingss->display_seller == 0) { ?>style="display:none;"
                                                <?php } ?>>
                                                <div class="row ">
                                                    <div class="col-sm-12 ">
                                                        <div class="select-group">

                                                            <select name="sales_person" id="sales_person"
                                                                class="form-control "
                                                                onchange="return getSellerDetails(this.value);">
                                                                <option value="0">Select Sales Person</option>
                                                                <?php foreach ($salesperson_details as $key_salesperson) { ?>
                                                                <option
                                                                    value="<?php echo $key_salesperson['id'] . '-' . $key_salesperson['name']; ?>">
                                                                    <?php echo $key_salesperson['name']; ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php } ?>


                                        <div class="clear"></div>
                                    </div>
                                    <div class="no-print">
                                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {
                                                    ?>
                                        <div class="form-group">
                                            <?php
                                                    $wh[''] = '';
                                                    foreach ($warehouses as $warehouse) {
                                                        $wh[$warehouse->id] = $warehouse->name;
                                                    }
                                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="poswarehouse" class="form-control pos-input-tip" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                                    ?>
                                        </div>
                                        <?php
                                            } else {

                                                $warehouse_input = array(
                                                    'type' => 'hidden',
                                                    'name' => 'warehouse',
                                                    'id' => 'poswarehouse',
                                                    'value' => $this->session->userdata('warehouse_id'),
                                                );

                                                echo form_input($warehouse_input);
                                            }
                                            ?>
                                        <div class="form-group" id="ui">
                                            <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                            <div class="input-group">
                                                <?php } ?>
                                                <div class="input-group-addon qr_main padding28">
                                                    <i class="fa fa-qrcode addIcon_qr" title="QR code"
                                                        onClick="return actQRCam()" id="addIcon"
                                                        style="font-size: 1.5em; cursor: pointer;"></i>
                                                </div>

                                                <?php if ($pos_settings->display_qr_code_scanner) { ?>
                                                <div class="">
                                                    <div class="row">
                                                        <div class="col-sm-5">
                                                            <?php echo form_input('add_item', '', 'class="form-control pos-tip kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted" id="add_item" data-placement="top" data-trigger="focus" style="border-radius: 40px !important;"  placeholder="' . $this->lang->line("search_product_by_name_code") . '" title="' . $this->lang->line("au_pr_name_tip") . '"'); ?>
                                                        </div>
                                                        <?php if ($Settings->barcode_scan_camera) { ?>
                                                        <div class="col-sm-2">
                                                            <button type="button" class="btn btn-primary"
                                                                data-toggle="modal" id="scancamerabtn"
                                                                data-target="#scan_barcode_camera"> <i
                                                                    class="fa fa-camera"></i> Scan </button>
                                                        </div>
                                                        <?php } ?>
                                                        <div class="col-sm-5">
                                                            <?php echo form_input('add_item_qr', '', 'class="form-control pos-tip kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted" id="add_item_qr" data-placement="top" data-trigger="focus"  placeholder="' . $this->lang->line("QR Code Scan") . '" title="' . $this->lang->line("au_pr_name_tip") . '"'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php } else { ?>
                                                <?php echo form_input('add_item', '', 'class="form-control pos-tip kb-text ui-keyboard-input ui-widget-content ui-corner-all ui-keyboard-autoaccepted" id="add_item" data-placement="top" data-trigger="focus"  placeholder="' . $this->lang->line("search_product_by_name_code") . '" title="' . $this->lang->line("au_pr_name_tip") . '"'); ?>
                                                <?php if ($Settings->barcode_scan_camera) { ?>
                                                <div class="input-group-addon padding28 borderblue"
                                                    title="ADD PRODUCT MANUALLY">

                                                    <button type="button" class="btn btn-xs btn-primary"
                                                        data-toggle="modal" id="scancamerabtn"
                                                        data-target="#scan_barcode_camera"> <i class="fa fa-camera"></i>
                                                        Scan </button>
                                                </div>
                                                <?php }
} ?>
                                                <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                                <div class="input-group-addon padding28 borderblue"
                                                    title="ADD PRODUCT MANUALLY">
                                                    <a href="<?= site_url() ?>products/add" id="">
                                                        <i class="fa fa-plus-circle" id="addIcon"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon padding28 borderblue" title="Rfid">
                                                    <button onClick="Rfid()" id="" class="rfidbtn"
                                                        style="border: none;background: transparent;color: #428bca;outline: none;">
                                                        <i class="fa fa-cart-arrow-down" id="addIcon"></i>
                                                    </button>
                                                </div>

                                                <?php if ($Owner || $Admin || $GP['sales-add_gift_card']) { ?>
                                                <div class="input-group-addon padding28 roundbtn">
                                                    <a href="#" id="sellGiftCard" class="tip"
                                                        title="<?= lang('sell_gift_card') ?>">
                                                        <i class="fa fa-2x fa-credit-card addIcon" id="addIcon"></i>
                                                    </a>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            <div class="clear"></div>
                                        </div>
                                        <?php if ($is_pharma) { ?>
                                        <div class="row" id="pharma_detail">
                                            <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                            <div class=" col-sm-6"><input type="text" value="" name="patient_name"
                                                    id="patient_name" placeholder="Patient name"
                                                    class="form-control required"></div>
                                            <div class=" col-sm-6"><input type="text" value="" name="doctor_name"
                                                    id="doctor_name" placeholder="Doctor name"
                                                    class="form-control required"></div>

                                            <?php } ?>
                                            <div class="clear"></div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <!--end search-->
                                <div class="quick-menu">
                                    <?php if ($Settings->theme != 'default') { ?>
                                    <div id="proContainer">

                                        <div class="cat-outer">
                                            <!--                                                <div class="">
                                                                                                        <div class="owl-carousel owl-theme custome">
                                                    <?php
//for ($i = 1; $i <= 40; $i++) {
                                                    foreach ($categories as $category) {
                                                        if (file_exists('assets/mdata/'.$Customer_assets.'/uploads/thumbs/' . $category->image)) {
                                                            if ($category->image != "") {
                                                                $imgsrc = 'assets/mdata/'.$Customer_assets.'/uploads/thumbs/' . $category->image;
                                                            } else {
                                                                $imgsrc = 'assets/mdata/'.$Customer_assets.'/uploads/thumbs/no_image.png';
                                                            }
                                                        } else {
                                                            $imgsrc = 'assets/mdata/'.$Customer_assets.'/uploads/thumbs/no_image.png';
                                                        }
                                                        echo "<div class='item'><button id=\"category-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni category\" >"
//                                                                    . "<img src=\"" .$imgsrc. "\" style='width:33px;height:33px;' class='img-rounded img-thumbnail' />"
                                                        . "<span>" . character_limiter($category->name, 15) . "</span></button></div>";
                                                    }
                                                    ?>
                                                                                                            <div class='item'></div> 
                                                                                                        </div>
                                                    </div>-->

                                            <div class="hscroll dragscroll">
                                                <?php
                                                        echo "<div class='inline-item'><button  type=\"button\"  class=\"btn-prni1 favouritebtn\" >"
                                                        . "<span>Favourite</span></button></div>";

                                                        foreach ($categories as $category) {
                                                            if (file_exists('assets/mdata/'.$Customer_assets.'/uploads/thumbs/' . $category->image)) {
                                                                if ($category->image != "") {
                                                                    $imgsrc = 'assets/mdata/'.$Customer_assets.'/uploads/thumbs/' . $category->image;
                                                                } else {
                                                                    $imgsrc = 'assets/mdata/'.$Customer_assets.'/uploads/thumbs/no_image.png';
                                                                }
                                                            } else {
                                                                $imgsrc = 'assets/mdata/'.$Customer_assets.'/uploads/thumbs/no_image.png';
                                                            }
                                                            echo "<div class='inline-item'><button id=\"category-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni1 category\" >"
//                                                                    . "<img src=\"" .$imgsrc. "\" style='width:33px;height:33px;' class='img-rounded img-thumbnail' />"
                                                            . "<span>" . $category->name . "</span></button></div>";
                                                        }
                                                        ?>
                                                <div class="inline-item">
                                                </div>
                                            </div>

                                            <div class="hscroll dragscroll nodisplay" id="subcat">
                                                <!--                                                    <div class="catsl-title">
                                                                                                                Sub Categories
                                                                                                            </div>-->
                                                <div class="subcategorydiv">
                                                </div>

                                                <!--                                                    <div class="owl-carousel second owl-theme owl-drag">
                                                        
                                                                                                            </div>-->

                                            </div>
                                        </div>
                                        <div id="ajaxproducts">
                                            <div id="item-list">
                                                <?php echo $products; ?>
                                            </div>
                                            <div class="btn-group btn-group-justified pos-grid-nav">
                                                <div class="btn-group prev">
                                                    <button class="btn btn-primary pos-tip z-index2 <?= $disblePage ?>"
                                                        title="<?= lang('previous') ?>" type="button" id="previous1"
                                                        disabled="disabled">
                                                        <i class="fa fa-chevron-left"></i>
                                                    </button>
                                                    <button class="btn btn-primary pos-tip <?= $enablePage ?>"
                                                        title="<?= lang('previous') ?>" type="button" id="previous">
                                                        <i class="fa fa-chevron-left"></i>
                                                    </button>
                                                </div>
                                                <?php //if ($Owner || $Admin || $GP['sales-add_gift_card']) {      ?>
                                                <!-- <div class="btn-group">
                                                             <button style="z-index:10003;" class="btn btn-primary pos-tip" type="button" id="sellGiftCard" title="<?= lang('sell_gift_card') ?>">
                                                                 <i class="fa fa-credit-card" id="addIcon"></i> <?= lang('sell_gift_card') ?>
                                                             </button>
                                                         </div-->
                                                <?php //}       ?>
                                                <div class="btn-group next">
                                                    <button class="btn btn-primary pos-tip z-index4 <?= $disblePage ?>"
                                                        title="<?= lang('next') ?>" type="button" id="next1"
                                                        disabled="disabled">
                                                        <i class="fa fa-chevron-right"></i>
                                                    </button>
                                                    <button class="btn btn-primary pos-tip z-index4 <?= $enablePage ?>"
                                                        title="<?= lang('next') ?>" type="button" id="next">
                                                        <i class="fa fa-chevron-right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <?php } else { ?>

                                <div id="proContainer">
                                    <div id="ajaxproducts">
                                        <div id="item-list">
                                            <?php echo $products; ?>
                                        </div>
                                        <div class="btn-group btn-group-justified pos-grid-nav">
                                            <div class="btn-group prev">
                                                <button class="z-index2 btn btn-primary pos-tip <?= $disblePage ?>"
                                                    title="<?= lang('previous') ?>" type="button" id="previous1"
                                                    disabled="disabled">
                                                    <i class="fa fa-chevron-left"></i>
                                                </button>
                                                <button class="z-index2 btn btn-primary pos-tip <?= $enablePage ?>"
                                                    title="<?= lang('previous') ?>" type="button" id="previous">
                                                    <i class="fa fa-chevron-left"></i>
                                                </button>
                                            </div>
                                            <?php //if ($Owner || $Admin || $GP['sales-add_gift_card']) {      ?>
                                            <!--div class="btn-group">
                                                        <button style="z-index:10003;" class="btn btn-primary pos-tip" type="button" id="sellGiftCard" title="<?= lang('sell_gift_card') ?>">
                                                            <i class="fa fa-credit-card" id="addIcon"></i> <?= lang('sell_gift_card') ?>
                                                        </button>
                                                    </div-->
                                            <?php //}        ?>
                                            <div class="btn-group next">
                                                <button class="z-index4 btn btn-primary pos-tip <?= $disblePage ?>"
                                                    title="<?= lang('next') ?>" type="button" id="next1"
                                                    disabled="disabled">
                                                    <i class="fa fa-chevron-right"></i>
                                                </button>
                                                <button class="z-index4 btn btn-primary pos-tip <?= $enablePage ?>"
                                                    title="<?= lang('next') ?>" type="button" id="next">
                                                    <i class="fa fa-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="cat-outer">
                                        <div class="cat-div">
                                            <div class="catsl-title">
                                                Categories
                                            </div>
                                            <div class="owl-carousel owl-theme">
                                                <?php
                                                        echo "<div class='item'><button  type=\"button\"  class=\"btn-prni favouritebtn\" >"
                                                        . "<img src=\"assets/mdata/$Customer_assets/uploads/thumbs/no_image.png\" style='width:33px;height:33px;' class='img-rounded img-thumbnail' />"
                                                        . "<span> Favourite </span></button></div>";
//for ($i = 1; $i <= 40; $i++) {
                                                        foreach ($categories as $category) {
                                                            if (file_exists('assets/mdata/'.$Customer_assets.'/uploads/thumbs/' . $category->image)) {
                                                                if ($category->image != "") {
                                                                    $imgsrc = 'assets/mdata/'.$Customer_assets.'/uploads/thumbs/' . $category->image;
                                                                } else {
                                                                    $imgsrc = 'assets/mdata/'.$Customer_assets.'/uploads/thumbs/no_image.png';
                                                                }
                                                            } else {
                                                                $imgsrc = 'assets/mdata/'.$Customer_assets.'/uploads/thumbs/no_image.png';
                                                            }
                                                            echo "<div class='item'><button id=\"category-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni category\" >"
                                                            . "<img src=\"" . $imgsrc . "\" style='width:33px;height:33px;' class='img-rounded img-thumbnail' />"
                                                            . "<span>" . $category->name . "</span></button></div>";
                                                        }
//}
                                                        ?>
                                                <div class='item'></div>
                                            </div>
                                        </div>
                                        <div class="cat-div">
                                            <div class="catsl-title">
                                                Sub Categories
                                            </div>
                                            <div class="owl-carousel second owl-theme">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>

    <div id="brands-slider">
        <div id="brands-list" class="">
            <?php
                foreach ($brands as $brand) {
                    $img = $brand->image;
                    if ($brand->image) {
                        if (!file_exists('assets/mdata/'.$Customer_assets.'/uploads/thumbs/' . $img)) {
                            $img = 'no_image.png';
                        }
                    } else {
                        $img = 'no_image.png';
                    }

                    echo "<button id=\"brand-" . $brand->id . "\" type=\"button\" value='" . $brand->id . "' class=\"btn-prni brand\" ><img src=\"assets/mdata/$Customer_assets/uploads/thumbs/" . ($img) . "\" style='width:" . $Settings->twidth . "px;height:" . $Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $brand->name . "</span></button>";
                }
                ?>
        </div>
    </div>
    <!--div id="category-slider">
            <!--<button type="button" class="close open-category"><i class="fa fa-2x">&times;</i></button>>
            <div id="category-list">
        <?php
        //for ($i = 1; $i <= 40; $i++) {
        foreach ($categories as $category) {
            echo "<button id=\"category-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni category\" ><img src=\"assets/mdata/$Customer_assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" style='width:" . $Settings->twidth . "px;height:" . $Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
        }
        //}
        ?>
            </div>
        </div>
        <div id="subcategory-slider">
            <!--<button type="button" class="close open-category"><i class="fa fa-2x">&times;</i></button>>
            <div id="subcategory-list">
        <?php
        if (!empty($subcategories)) {
            foreach ($subcategories as $category) {
                echo "<button id=\"subcategory-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"assets/mdata/$Customer_assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" style='width:" . $Settings->twidth . "px;height:" . $Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
            }
        }
        ?>
            </div>
        </div-->
    <?php
        $Settings->theme = !empty($Settings->theme) ? $Settings->theme : 'default';

        if ($Settings->theme == 'default') {
            include_once('checkout_model.php');
        } else {
            include_once('checkout_model_' . $Settings->theme . '.php');
        }
        ?>

    <div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                                class="fa fa-2x">&times;</i></span><span
                            class="sr-only"><?= lang('close'); ?></span></button>
                    <h4 class="modal-title" id="prModalLabel"></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">
                    <form class="form-horizontal" role="form">
                        <p id="returnmessage"></p>
                        <?php if ($Settings->tax1) {
                                        ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                        $tr[""] = "";
                                        foreach ($tax_rates as $tax) {
                                            $tr[$tax->id] = $tax->name;
                                        }
                                        echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                        ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-text" id="pserial">
                            </div>
                        </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="pquantity">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                            <div class="col-sm-8">
                                <div id="punits-div"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                            <div class="col-sm-8">
                                <div id="poptions-div"></div>
                            </div>
                        </div>
                        <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount"
                                class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="pdiscount">
                            </div>
                        </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="pprice"
                                    <?= ($Owner || $Admin || $GP['cart-price_edit']) ? '' : 'readonly="readonly" onchange="return false"'; ?>>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pdescription" class="col-sm-4 control-label"><?= lang('Description') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="pdescription">
                            </div>
                        </div>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th class="width25"><?= lang('net_unit_price'); ?></th>
                                <th class="width25"><span id="net_price"></span></th>
                                <th class="width25"><?= lang('product_tax'); ?></th>
                                <th class="width25"><span id="pro_tax"></span></th>
                            </tr>
                        </table>
                        <input type="hidden" id="punit_price" value="" />
                        <input type="hidden" id="old_tax" value="" />
                        <input type="hidden" id="old_qty" value="" />
                        <input type="hidden" id="old_price" value="" />
                        <input type="hidden" id="row_id" value="" />
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade in" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></button>
                    <h4 class="modal-title" id="myModalLabel"><?= lang('sell_gift_card'); ?></h4>
                </div>
                <div class="modal-body">
                    <p><?= lang('enter_info'); ?></p>
                    <div class="alert alert-danger gcerror-con" style="display: none;">
                        <button data-dismiss="alert" class="close" type="button">x</button>
                        <span id="gcerror"></span>
                    </div>
                    <div class="form-group">
                        <?= lang("card_no", "gccard_no"); ?> *
                        <div class="input-group">
                            <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                <a href="#" id="genNo"><i class="fa fa-cogs"></i></a>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname" />

                    <div class="form-group">
                        <?= lang("value", "gcvalue"); ?> *
                        <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("price", "gcprice"); ?> *
                        <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("customer", "gccustomer"); ?>
                        <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("expiry_date", "gcexpiry"); ?>
                        <?php echo form_input('gcexpiry', $this->sma->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="gcexpiry"'); ?>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" id="addGiftCard"
                        class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade in" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                                class="fa fa-2x">&times;</i></span><span
                            class="sr-only"><?= lang('close'); ?></span></button>
                    <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">

                    <form class="form-horizontal" role="form" id="quickSaleForm" name="quickSaleForm">
                        <div class="form-group hide-me">
                            <label for="mcode" class="col-sm-4 control-label "><?= lang('product_code') ?> *</label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-text" name="mcode" id="mcode" required>
                            </div>

                            <span class="qSerror"></span>
                        </div>
                        <div id="pname" class="form-group">
                            <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-text" name="mname" id="mname"
                                    onblur="jQuery('#mcode').val(this.value)" required>
                            </div>
                            <span id="mnameerr"></span>

                        </div>
                        <?php if ($Settings->tax1) {
    ?>
                        <div class="form-group hide-me">
                            <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>

                            <div class="col-sm-8">
                                <?php
                                        $tr[""] = "";
                                        foreach ($tax_rates as $tax) {
                                            $tr[$tax->id] = $tax->name;
                                        }
                                        echo form_dropdown('mtax', $tr, "1", 'id="mtax" name="mtax" class="form-control pos-input-tip" style="width:100%;"');
                                        ?>
                            </div>
                        </div>
                        <?php }
?>
                        <div class="form-group">
                            <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>

                            <div class="col-sm-8">
                                <input type="number" class="form-control kb-pad" name="mquantity" id="mquantity"
                                    required>
                            </div>
                        </div>
                        <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group hide-me">
                            <label for="mdiscount"
                                class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" name="mdiscount" id="mdiscount" required>
                            </div>
                        </div>
                        <?php }
?>
                        <div class="form-group">
                            <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="mprice" name="mprice"
                                    onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;"
                                    required>
                                <span id="error" style="color:#a94442; font-size:11px;display: none">Please Enter
                                    numbers only</span>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                                <th style="width:25%;"><span id="mnet_price"></span></th>
                                <th style="width:25%;"><?= lang('product_tax'); ?></th>
                                <th style="width:25%;"><span id="mpro_tax"></span></th>
                            </tr>
                        </table>
                    </form>
                    <div class="row">
                        <div class="pull-left col-xs-12">
                            <div class="row">
                                <div class="col-xs-3">
                                    <button id="mitems" class="btn btn-primary til-btn misc">Miscellaneous item</button>
                                </div>
                                <div class="col-xs-3">
                                    <button id="scharges" class="btn btn-primary til-btn serv">service charges</button>
                                </div>
                                <div class="col-xs-3">
                                    <button id="tcharges" class="btn btn-primary til-btn trans">transportation
                                        charges</button>
                                </div>
                                <div class="col-xs-3">
                                    <button id="other" class="btn btn-primary til-btn othr">other</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
                </div>
            </div>
        </div>
    </div>
    <script>
    const division_array = [];
    const unique_array = [];
    var kotprint_flag = true;
    const delete_items = [];
    $(document).ready(function() {
        $('#successmsg').remove();
        $('.error').remove();
        $("#quickSaleForm").validate({
            rules: {
                mcode: "required",
                mname: "required",
                mtax: "required",
                mquantity: "required",
                mdiscount: "required",
                mprice: "required",
            },
            messages: {
                mcode: "Please Enter Product's code",
                mname: "Please Enter Product Name",
                mtax: "Please Enter Tax on Product's",
                mquantity: "Please Enter Product's quantity",
                mdiscount: "Please Enter Discount",
                mprice: "Please Enter Product's Price"

            }
        })
        $('#addItemManually').click(function() {
            if ($("#quickSaleForm").valid()) {
                console.log('true');
                $('#successmsg').html('successfully submited');
                $('#successmsg').show();
                setTimeout("$('#successmsg').hide(); ", 3000);
            } else {
                console.log('false');
                return false;
            }
        });
        $.fn.clearValidation = function() {
            var v = $(this).validate();
            $('[name]', this).each(function() {
                v.successList.push(this);
                v.showErrors();
            });
            v.resetForm();
            v.reset();
        };
        //used:
        $("#quickSaleForm").clearValidation();
    });
    </script>
    <div class="modal fade in" id="sckModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                            <i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span>
                    </button>
                    <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;"
                        onClick="window.print();">
                        <i class="fa fa-print"></i> <?= lang('print'); ?>
                    </button>
                    <h4 class="modal-title" id="mModalLabel"><?= lang('shortcut_keys') ?></h4>
                </div>
                <div class="modal-body" id="pr_popover_content">
                    <table class="table table-bordered table-striped table-condensed table-hover"
                        style="margin-bottom: 0px;">
                        <thead>
                            <tr>
                                <th><?= lang('shortcut_keys') ?></th>
                                <th><?= lang('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= $pos_settings->focus_add_item ?></td>
                                <td><?= lang('focus_add_item') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->add_manual_product ?></td>
                                <td><?= lang('add_manual_product') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->customer_selection ?></td>
                                <td><?= lang('customer_selection') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->add_customer ?></td>
                                <td><?= lang('add_customer') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->toggle_category_slider ?></td>
                                <td><?= lang('toggle_category_slider') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->toggle_subcategory_slider ?></td>
                                <td><?= lang('toggle_subcategory_slider') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->cancel_sale ?></td>
                                <td><?= lang('cancel_sale') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->suspend_sale ?></td>
                                <td><?= lang('suspend_sale') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->print_items_list ?></td>
                                <td><?= lang('print_items_list') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->finalize_sale ?></td>
                                <td><?= lang('finalize_sale') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->today_sale ?></td>
                                <td><?= lang('today_sale') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->open_hold_bills ?></td>
                                <td><?= lang('open_hold_bills') ?></td>
                            </tr>
                            <tr>
                                <td><?= $pos_settings->close_register ?></td>
                                <td><?= lang('close_register') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade in" id="dsModal" tabindex="-1" role="dialog" aria-labelledby="dsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <i class="fa fa-2x">&times;</i>
                    </button>
                    <h4 class="modal-title" id="dsModalLabel"><?= lang('edit_order_discount'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <?= lang("order_discount", "order_discount_input"); ?>
                        <?php echo form_input('order_discount_input', '', 'class="form-control kb-pad" id="order_discount_input"'); ?>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" id="updateOrderDiscount"
                        class="btn btn-primary"><?= lang('update') ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade in" id="txModal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></button>
                    <h4 class="modal-title" id="txModalLabel"><?= lang('edit_order_tax'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <?= lang("order_tax", "order_tax_input"); ?>
                        <?php
                            $tr[""] = "";
                            foreach ($tax_rates as $tax) {
                                $tr[$tax->id] = $tax->name;
                            }
                            echo form_dropdown('order_tax_input', $tr, "", 'id="order_tax_input" class="form-control pos-input-tip" style="width:100%;"');
                            ?>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" id="updateOrderTax" class="btn btn-primary"><?= lang('update') ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade in" id="susModal" tabindex="-1" role="dialog" aria-labelledby="susModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></button>
                    <h4 class="modal-title" id="susModalLabel"><?= lang('suspend_sale'); ?></h4>
                </div>
                <div class="modal-body">
                    <p><?= lang('type_reference_note'); ?></p>
                    <div class="form-group">
                        <? //=lang("restaurant_tables","restaurant_tables");?>
                        <? //=lang("table_number", "table_number");?>
                        <?php
//echo form_input('table_id', $table_id, 'class="form-control kb-text restaurant_tables" ');
                            $options[""] = "";
                            foreach ($tabless as $key => $table) {
                                $options[$table->id] = $table->name;
                            }
                            echo form_dropdown('table_id', $options, '', ' id="kot_restaurant_tables" class="form-control restaurant_tables" style="width:100%; display:none;"');
//echo form_dropdown('table_id', $table_id, $tables);
                            ?>
                    </div>
                    <div class="form-group">
                        <?= lang("reference_note", "reference_note"); ?>
                        <?php echo form_input('reference_note', $reference_note, 'readonly="true" class="form-control kb-text" id="reference_note"'); ?>
                    </div>
                    <?php
if (isset($Settings->pos_type) && $Settings->pos_type == 'restaurant') {
    ?>

                    <div class="row">
                        <div class="col-xs-6">
                            <div class="chk-btn">
                                <input class="carry_out" type="radio" name="carry_out" value="carry_out"
                                    onchange="valueChanged()" />
                                <label>Carry Out</label>
                            </div>
                            <div class="information-field" style="display:none">
                                <div class="row">
                                    <div class="col-md-12">
                                        <input name="carry_out_customer_info" type="text" class="form-control"
                                            placeholder="Customer name / Mobile no">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="chk-btn">
                                <input class="table-num" type="radio" name="carry_out" value="carry_out"
                                    checked="checked" onchange="valueChanged()" />
                                <label>Enter table number</label>
                            </div>
                        </div>
                    </div>
                    <div class="radio-btn-section">
                        <ul class="nav nav-tabs">
                            <?php foreach ($tables_groups as $key => $grp_table) { ?>
                            <li class="<?= ($key == 0 ? 'active' : '') ?>"><a data-toggle="tab"
                                    href="#<?= ($grp_table['table_group'] ? str_replace(" ", "", $grp_table['table_group']) : 'default' ) ?>"><?= ($grp_table['table_group'] ? $grp_table['table_group'] : 'Default' ) ?></a>
                            </li>
                            <?php } ?>

                        </ul>

                        <div class="tab-content">
                            <?php foreach ($tables_groups as $key => $items_tableG) { ?>
                            <div id="<?= ($items_tableG['table_group'] ? str_replace(" ", "", $items_tableG['table_group']) : 'default' ) ?>"
                                class="tab-pane fade <?= ($key == 0 ? 'in active' : '') ?>">
                                <strong
                                    style="display: block;clear: both; color: #FF0000; padding:10px"><?= ($items_tableG['table_group'] ? $items_tableG['table_group'] : 'Default' ) ?></strong>
                                <div class="row">
                                    <?php
                                                foreach ($tables as $rtkey => $rtval) {
                                                    $getSubtable = getSubTables($rtval->id);

                                                    if ($user->table_assign) {

                                                        $tableselected = explode(",", $user->table_assign);
                                                        if (in_array($rtval->id, $tableselected)) {
                                                            $billPrint = '';
                                                            if ($items_tableG['table_group'] == $rtval->table_group) {
                                                                $checked = (($table_id == $rtval->id) && ($rtval->name == $rtval->suspended_note)) ? "checked" : "";
                                                                $style_lable = '';
                                                                $call_id = '';
                                                                if (($table_id != $rtval->id) && ($rtval->name == $rtval->suspended_note)) {

                                                                    //var_dump($rtval->status);
                                                                    $style_lable = ($rtval->status == 'Booked') ? "background:red" : "";
                                                                    $call_id = $rtval->suspended_id;
                                                                    //$style_desable = ($rtval->status=='Booked')? "disabled" :"";
                                                                }
                                                                if ($rtval->bill_printed) {
                                                                    $billPrint = 'style="background-color:#ff8400eb;border:1px solid #ff8400eb;"';
                                                                }
                                                                echo '<div class="col-sm-3 table-block" id="table_block-' . $rtval->id . '">';
                                                                if ($Owner || $Admin || $GP['pos_clear_table']) {
                                                                    if ($call_id) {
                                                                        echo '<button delete-id="' . $call_id . '" type="button" class="btn btn-danger pull-right delete_suspend"> <i class="fa fa-trash"></i> </button>';
                                                                    }
                                                                }

                                                                echo '<div id="select_table-' . $rtval->id . '" class="rd-btn resturent_table_group" ' . (($call_id) ? 'onclick=callTable("' . $call_id . '")' : '') . ' >';
                                                                echo '<input ' . $checked . ' type="radio"  table_id="' . $rtval->id . '"  name="gender" id="' . str_replace(" ", "_", $rtval->name) . '" value="' . $rtval->name . '" ' . $style_desable . '>';
                                                                echo '<lable style="' . $style_lable . '">' . $rtval->name . '</lable>';

                                                                echo '</div>';
                                                                echo '<div style="display:block; padding: 8px;">';
                                                                echo '<button type="button" onclick="update_seats(\'' . $rtval->id . '\')" id="table-' . $rtval->id . '" class=" btn btn-xs btn-warning">Guests : ' . $rtval->seats . '</button> ';

                                                                if ($Owner || $Admin || $GP['bill_print']) {
                                                                    echo '<button type="button" onclick="bill_print(\'' . $rtval->id . '\')"  id="billprint-' . $rtval->id . '" class=" btn btn-xs btn-info" ' . $billPrint . ($call_id ? '' : 'disabled="true"') . '>Bill Print</button> ';
                                                                }

                                                                if ($getSubtable) {
                                                                    echo '<div class="btn-group">';
                                                                    echo '<button class="btn btn-primary  btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                                                    echo 'Sub Table';
                                                                    echo '</button>';
                                                                    echo '<div class="dropdown-menu" style="padding: 10px 20px; color:#000">';
                                                                    echo '<ul class="sub_table">';
                                                                    foreach ($getSubtable as $sub_items) {
                                                                        echo '<li onclick="add_subtable(' . $sub_items->id . ')" class="dropdown-item" >' . $sub_items->name . '</li>';
                                                                    }
                                                                    echo '</ul>';
                                                                    echo ' </div>';
                                                                    echo '</div>';
                                                                }

                                                                echo '</div>';
                                                                echo '</div>';

                                                                if ($getSubtable) {
                                                                    foreach ($getSubtable as $sub_items) {
                                                                        $billPrintSub = '';
                                                                        $style_lablesub = '';
                                                                        $call_idsub = '';
                                                                        if (($table_id != $sub_items->id) && ($sub_items->name == $sub_items->suspended_note)) {
                                                                            $style_lablesub = ($sub_items->status == 'Booked') ? "background:red" : "";
                                                                            $call_idsub = $sub_items->suspended_id;
                                                                        }
                                                                        if ($sub_items->bill_printed) {
                                                                            $billPrintSub = 'style="background-color:#ff8400eb;border:1px solid #ff8400eb;"';
                                                                        }
                                                                        echo '<div class="col-sm-3 table-block" ' . ($call_idsub || ($table_id == $sub_items->id) ? 'style="display:block;' : 'style="display:none;') . ' " id="table_block-' . $sub_items->id . '">';

                                                                        if ($Owner || $Admin || $GP['pos_clear_table']) {
                                                                            if ($call_idsub) {
                                                                                echo '<button delete-id="' . $call_idsub . '" type="button" class="btn btn-danger pull-right delete_suspend"> <i class="fa fa-trash"></i> </button>';
                                                                            }
                                                                        }

                                                                        echo '<div id="select_table-' . $sub_items->id . '" class="rd-btn resturent_table_group"' . ($call_idsub ? 'onclick=callTable("' . $call_idsub . '")' : '') . '  >';
                                                                        echo '<input ' . $checked . ' type="radio"  table_id="' . $sub_items->id . '"  name="gender" id="' . $sub_items->name . '" value="' . $sub_items->name . '" ' . $style_desable . '>';
                                                                        echo '<lable style="' . $style_lablesub . '">' . $sub_items->name . '</lable>';
                                                                        echo '</div>';
                                                                        echo '<div style="display:block; padding: 8px;">';
                                                                        echo '<button type="button" onclick="delete_subtable(' . $sub_items->id . ')" class=" btn btn-xs btn-danger">Delete</button> ';
                                                                        echo '<button type="button" onclick="update_seats(\'' . $sub_items->id . '\')" id="table-' . $sub_items->id . '" class=" btn btn-xs btn-warning">Guests : ' . $sub_items->seats . '</button> ';
                                                                        if ($Owner || $Admin || $GP['bill_print']) {
                                                                            echo '<button type="button" onclick="bill_print(\'' . $sub_items->id . '\')"  id="billprint-' . $sub_items->id . '" class=" btn btn-xs btn-info" ' . $billPrintSub . ($call_idsub ? '' : 'disabled="true"') . '>Bill Print</button> ';
                                                                        }
                                                                        echo '</div>';
                                                                        echo '</div>';
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        $billPrint = '';
                                                        if ($items_tableG['table_group'] == $rtval->table_group) {
                                                            $checked = (($table_id == $rtval->id) && ($rtval->name == $rtval->suspended_note)) ? "checked" : "";
                                                            $style_lable = '';
                                                            $call_id = '';
                                                            if (($table_id != $rtval->id) && ($rtval->name == $rtval->suspended_note)) {

                                                                //var_dump($rtval->status);
                                                                $style_lable = ($rtval->status == 'Booked') ? "background:red" : "";
                                                                $call_id = $rtval->suspended_id;
                                                                //$style_desable = ($rtval->status=='Booked')? "disabled" :"";
                                                            }
                                                            if ($rtval->bill_printed) {
                                                                $billPrint = 'style="background-color:#ff8400eb;border:1px solid #ff8400eb;"';
                                                            }
                                                            echo '<div class="col-sm-3 table-block" id="table_block-' . $rtval->id . '">';
                                                            if ($Owner || $Admin || $GP['pos_clear_table']) {
                                                                if ($call_id) {
                                                                    echo '<button delete-id="' . $call_id . '" type="button" class="btn btn-danger pull-right delete_suspend"> <i class="fa fa-trash"></i> </button>';
                                                                }
                                                            }

                                                            echo '<div  id="select_table-' . $rtval->id . '" class="rd-btn resturent_table_group" ' . (($call_id) ? 'onclick=callTable("' . $call_id . '")' : '') . ' >';
                                                            echo '<input ' . $checked . ' type="radio"  table_id="' . $rtval->id . '"  name="gender" id="' . str_replace(" ", "_", $rtval->name) . '" value="' . $rtval->name . '" ' . $style_desable . '>';
                                                            echo '<lable style="' . $style_lable . '">' . $rtval->name . '</lable>';

                                                            echo '</div>';
                                                            echo '<div style="display:block; padding: 8px;">';
                                                            echo '<button type="button" onclick="update_seats(\'' . $rtval->id . '\')" id="table-' . $rtval->id . '" class=" btn btn-xs btn-warning">Guests : ' . $rtval->seats . '</button> ';

                                                            if ($Owner || $Admin || $GP['bill_print']) {
                                                                echo '<button type="button" onclick="bill_print(\'' . $rtval->id . '\')"  id="billprint-' . $rtval->id . '" class=" btn btn-xs btn-info" ' . $billPrint . ($call_id ? '' : 'disabled="true"') . '>Bill Print</button> ';
                                                            }

                                                            if ($getSubtable) {
                                                                echo '<div class="btn-group">';
                                                                echo '<button class="btn btn-primary  btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                                                echo 'Sub Table';
                                                                echo '</button>';
                                                                echo '<div class="dropdown-menu" style="padding: 10px 20px; color:#000">';
                                                                echo '<ul class="sub_table">';
                                                                //                                                                        echo '<li onclick="add_subtable(\'A\','.$rtval->id.')" class="dropdown-item" >A</</li>';
                                                                foreach ($getSubtable as $sub_items) {
                                                                    echo '<li onclick="add_subtable(' . $sub_items->id . ')" class="dropdown-item" >' . $sub_items->name . '</li>';
                                                                }
                                                                echo '</ul>';
                                                                echo ' </div>';
                                                                echo '</div>';
                                                            }

                                                            echo '</div>';
                                                            echo '</div>';

                                                            if ($getSubtable) {
                                                                foreach ($getSubtable as $sub_items) {
                                                                    $billPrintSub = '';
                                                                    $style_lablesub = '';
                                                                    $call_idsub = '';
                                                                    if (($table_id != $sub_items->id) && ($sub_items->name == $sub_items->suspended_note)) {
                                                                        $style_lablesub = ($sub_items->status == 'Booked') ? "background:red" : "";
                                                                        $call_idsub = $sub_items->suspended_id;
                                                                    }
                                                                    if ($sub_items->bill_printed) {
                                                                        $billPrintSub = 'style="background-color:#ff8400eb;border:1px solid #ff8400eb;"';
                                                                    }
                                                                    echo '<div class="col-sm-3 table-block" ' . ($call_idsub || ($table_id == $sub_items->id) ? 'style="display:block;' : 'style="display:none;') . ' " id="table_block-' . $sub_items->id . '">';
                                                                    if ($Owner || $Admin || $GP['pos_clear_table']) {
                                                                        if ($call_idsub) {
                                                                            echo '<button delete-id="' . $call_idsub . '" type="button" class="btn btn-danger pull-right delete_suspend"> <i class="fa fa-trash"></i> </button>';
                                                                        }
                                                                    }
                                                                    echo '<div id="select_table-' . $sub_items->id . '" class="rd-btn resturent_table_group"' . ($call_idsub ? 'onclick=callTable("' . $call_idsub . '")' : '') . '  >';
                                                                    echo '<input ' . $checked . ' type="radio"  table_id="' . $sub_items->id . '"  name="gender" id="' . $sub_items->name . '" value="' . $sub_items->name . '" ' . $style_desable . '>';
                                                                    echo '<lable style="' . $style_lablesub . '">' . $sub_items->name . '</lable>';
                                                                    echo '</div>';
                                                                    echo '<div style="display:block; padding: 8px;">';
                                                                    echo '<button type="button" onclick="delete_subtable(' . $sub_items->id . ')" class=" btn btn-xs btn-danger">Delete</button> ';
                                                                    echo '<button type="button" onclick="update_seats(\'' . $sub_items->id . '\')" id="table-' . $sub_items->id . '" class=" btn btn-xs btn-warning">Guests : ' . $sub_items->seats . '</button> ';
                                                                    if ($Owner || $Admin || $GP['bill_print']) {
                                                                        echo '<button type="button" onclick="bill_print(\'' . $sub_items->id . '\')"  id="billprint-' . $sub_items->id . '" class=" btn btn-xs btn-info" ' . $billPrintSub . ($call_idsub ? '' : 'disabled="true"') . '>Bill Print</button> ';
                                                                    }
                                                                    echo '</div>';
                                                                    echo '</div>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                ?>
                                </div>
                            </div>
                            <?php } ?>

                        </div>
                        <script type="text/javascript">
                        function valueChanged() {
                            if ($('.carry_out').is(":checked")) {
                                $("#reference_note").val('');
                                $(".information-field").show();
                            } else {
                                $("#reference_note").val('');
                                $(".information-field").hide();
                            }
                            if ($('.table-num').is(":checked")) {
                                $("#reference_note").val('');
                                $(".radio-btn-section").show();
                            } else {
                                $("#reference_note").val('');
                                $(".radio-btn-section").hide();
                            }


                        }
                        </script>

                        <!--div class="row">
                                    <div class="col-xs-6">

                                        <div class="chk-btn">
                                            <input class="carry_out" type="radio" name="carry_out" value="carry_out" onchange="valueChanged()"/>
                                            <label>Carry Out</label>
                                        </div>
                                        <div class="information-field" style="display:none">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input name="carry_out_customer_info" type="text" class="form-control" placeholder="Customer name / Mobile no">

                                                </div>
                                            </div>
                                        </div>
                                        <div class="keypad" >
                                            <div class="key-btns">
                                                <button value="A">A</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="B">B</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="C">C</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="D">D</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="E">E</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="F">F</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="G">G</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="H">H</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="I">I</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="J">J</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="K">K</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="L">L</button>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="col-xs-6">
                                        <div class="chk-btn">
                                            <input class="table-num" type="radio" name="carry_out" value="carry_out" checked="checked"  onchange="valueChanged()"/>
                                            <label>Enter table number</label>
                                        </div>
                                        <div class="keypad" >
                                            <div class="key-btns">
                                                <button value="1">1</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="2">2</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="3">3</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="4">4</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="5">5</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="6">6</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="7">7</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="8">8</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="9">9</button>
                                            </div>
                                            <div class="key-btns">
                                                <button value="0">0</button>
                                            </div>
                                            <div class="bl-btn">
                                                <button value="clear" onClick="$('#reference_note').val('')">CLEAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div-->
                        <div style="clear:both"></div>
                    </div>
                    <?php }
?>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <?php
if (isset($Settings->pos_type) && $Settings->pos_type == 'restaurant') {
    ?>
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <button type="button" style="float:left"
                                class="btn btn-block btn-lg btn-primary cmdnotprint print_kot" id="print">Print</button>
                        </div>
                        <?php } ?>
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <button type="button" id="suspend_sale"
                                class="btn btn-block btn-lg btn-primary cmdnotprint">Save</button>
                        </div>
                        <?php
if (isset($Settings->pos_type) && $Settings->pos_type == 'restaurant') {
    ?>
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <button onclick="call_checkout()" type="button"
                                class="btn btn-block btn-lg btn-primary cmdnotprint">Checkout</button>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal  modalvarient" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" onclick="modalClose('modalvarient')" data-dismiss="modal"
                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Modal title</h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" onclick="modalClose('modalvarient')" class="btn btn-default"
                        data-toggle="modal">Close</button>
                    <!--button type="button" class="btn btn-primary" onclick="addProductToVarientProduct('modalvarient')">Save changes</button -->
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


    <div class="kot_tbl" style="display: none;"></div>
    <div id="order_tbl">
        <style>
        .btn_back {
            display: inline-block;
            padding: 6px 12px;
            margin: 15px;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-image: none;
            border: 1px solid #357ebd;
            border-radius: 4px;
            color: #fff;
            background-color: #428bca
        }
        </style>
        <span id="order_span"></span>
        <table id="order-table" class="prT table table-striped" style="margin-bottom:0;" width="100%"></table>
        <!--div style="text-align:center"  id="bk_pos" ><a  href="<?= site_url() ?>/pos"  class="btn btn-primary btn_back"  >BACK TO POS</a></div-->
    </div>
    <div id="bill_tbl">
        <style>
        .btn_back {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-image: none;
            border: 1px solid #357ebd;
            border-radius: 4px;
            color: #fff;
            background-color: #428bca
        }
        </style>
        <span id="bill_span"></span>
        <table id="bill-table" width="100%" class="prT table table-striped" style="margin-bottom:0;"></table>
        <table id="bill-total-table" class="prT table" style="margin-bottom:0;" width="100%"></table>
        <!--div style="text-align:center"  id="bk_pos" ><a   href="<?= site_url() ?>/pos"  class="btn btn-primary btn_back"  >BACK TO POS</a></div-->
    </div>
    <div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true"></div>
    <div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2"
        aria-hidden="true"></div>
    <div id="modal-loading" style="display: none;">
        <div class="blackbg"></div>
        <div class="loader"></div>
    </div>
    <div id="recent_pos_sale_modal-loading" style="display: none;">
        <div class="blackbg" style="z-index: 1051;"></div>
        <div class="loader" style="z-index: 1052;"></div>
    </div>
    <!--        offer-details modal-->


    <div class="modal fade in" id="offer_modal" tabindex="-1" role="dialog" aria-labelledby="dsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <i class="fa fa-2x">&times;</i>
                    </button>
                    <h2> <?= $active_offers_category ?></h2>
                </div>

                <div class="modal-body">
                    <form action="" method="post" id="offerUpdates">
                        <table cellpadding="0" cellspacing="0" border="0" width="60%"
                            class="table table-bordered table-hover">

                            <tbody>
                                <input type="hidden" name="offer_id" id="offer_id">
                                <tr>
                                    <td>Offer Name</td>
                                    <td> <input type="text" class="form-control" name="offer_name" id="offer_name"></td>
                                </tr>
                                <tr>
                                    <td>Offer Amount Including Tax</td>
                                    <td><input type="text" class="form-control" name="offer_amount_including_tax"
                                            id="offer_amount_including_tax"></td>
                                </tr>
                                <tr>
                                    <td>Offer Discount Rate</td>
                                    <td><input type="text" name="offer_discount_rate" class="form-control"
                                            id="offer_discount_rate"></td>
                                </tr>
                                <tr>
                                    <td>Offer End Date</td>
                                    <td><input type="text" id="offer_end_date" name="offer_end_date"
                                            class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Offer End Time</td>
                                    <td><input type="text" name="offer_end_time" class="form-control"
                                            id="offer_end_time"></td>
                                </tr>

                                <tr>
                                    <td>Offer Free Products</td>
                                    <td><input type="text" name="offer_free_products" class="form-control"
                                            id="offer_free_products"></td>
                                </tr>
                                <tr>
                                    <td>Offer Free Products Quantity</td>
                                    <td><input type="text" name="offer_free_products_quantity" class="form-control"
                                            id="offer_free_products_quantity"></td>
                                </tr>
                                <tr>
                                    <td>offer Items Condition</td>
                                    <td><input type="text" name="offer_items_condition" value="" class="form-control"
                                            id="offer_items_condition"></td>

                                </tr>
                                <tr>
                                    <td> Offer on Brands</td>
                                    <td><input type="text" name="offer_on_brands" value="" class="form-control"
                                            id="offer_on_brands"></td>
                                </tr>
                                <tr>
                                    <td>Offer on Category Quantity</td>
                                    <td><input type="text" name="offer_on_category_quantity" class="form-control"
                                            value="" id="offer_on_category_quantity"></td>
                                </tr>
                                <tr>
                                    <td>Offer on Days</td>
                                    <td> <input type="text" name="offer_on_days" class="form-control" value=""
                                            id="offer_on_days"></td>
                                </tr>
                                <tr>
                                    <td>Offer on Invoice Amount</td>
                                    <td><input type="text" name="offer_on_invoice_amount" class="form-control" value=""
                                            id="offer_on_invoice_amount"></td>
                                </tr>
                                </tr>
                                <tr>
                                    <td>Offer on Products</td>
                                    <td><input type="text" name="offer_on_products" class="form-control" value=""
                                            id="offer_on_products"></td>
                                </tr>
                                <tr>
                                    <td>Offer on Products Amount</td>
                                    <td><input type="text" name="offer_on_products_amount" class="form-control" value=""
                                            id="offer_on_products_amount"></td>
                                </tr>
                                <tr>
                                    <td>Offer on Products Quantity</td>
                                    <td><input type="text" class="form-control" name="offer_on_products_quantity"
                                            value="" id="offer_on_products_quantity"></td>
                                </tr>
                                <tr>
                                    <td>Offer on Warehouses</td>
                                    <td><input type="text" name="offer_on_warehouses" class="form-control" value=""
                                            id="offer_on_warehouses"></td>
                                </tr>
                                <tr>
                                    <td>Offer Start Date</td>
                                    <td><input type="text" name="offer_start_date" class="form-control" value=""
                                            id="offer_start_date"></td>
                                </tr>
                                <tr>
                                    <td> offer Start Time</td>
                                    <td><input type="text" name="offer_start_time" class="form-control" value=""
                                            id="offer_start_time"></td>
                                </tr>

                                <tr>
                                    <td colspan="2"><input type="submit" class="btn btn-info text-center"
                                            value="UPDATE OFFER" id="update_offer"></td>
                                </tr>

                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->envato_username, $Settings->purchase_code); ?>

    <script type="text/javascript">
    var site =
        <?= json_encode(array('base_url' => base_url(), 'settings' => $Settings, 'dateFormats' => $dateFormats, 'offers' => $active_offers)) ?>,
        pos_settings = <?= json_encode($pos_settings); ?>;
    var lang = {
        unexpected_value: '<?= lang('unexpected_value'); ?>',
        select_above: '<?= lang('select_above'); ?>',
        r_u_sure: '<?= lang('r_u_sure'); ?>',
        bill: '<?= lang('bill'); ?>',
        order: '<?= lang('order'); ?>'
    };
    </script>

    <script type="text/javascript">
    // Create Base64 Object
    var Base64 = {
        _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
        encode: function(e) {
            var t = "";
            var n, r, i, s, o, u, a;
            var f = 0;
            e = Base64._utf8_encode(e);
            while (f < e.length) {
                n = e.charCodeAt(f++);
                r = e.charCodeAt(f++);
                i = e.charCodeAt(f++);
                s = n >> 2;
                o = (n & 3) << 4 | r >> 4;
                u = (r & 15) << 2 | i >> 6;
                a = i & 63;
                if (isNaN(r)) {
                    u = a = 64
                } else if (isNaN(i)) {
                    a = 64
                }
                t = t + this._keyStr.charAt(s) + this._keyStr.charAt(o) + this._keyStr.charAt(u) + this._keyStr
                    .charAt(a)
            }
            return t
        },
        decode: function(e) {
            var t = "";
            var n, r, i;
            var s, o, u, a;
            var f = 0;
            e = e.replace(/[^A-Za-z0-9+/=]/g, "");
            while (f < e.length) {
                s = this._keyStr.indexOf(e.charAt(f++));
                o = this._keyStr.indexOf(e.charAt(f++));
                u = this._keyStr.indexOf(e.charAt(f++));
                a = this._keyStr.indexOf(e.charAt(f++));
                n = s << 2 | o >> 4;
                r = (o & 15) << 4 | u >> 2;
                i = (u & 3) << 6 | a;
                t = t + String.fromCharCode(n);
                if (u != 64) {
                    t = t + String.fromCharCode(r)
                }
                if (a != 64) {
                    t = t + String.fromCharCode(i)
                }
            }
            t = Base64._utf8_decode(t);
            return t
        },
        _utf8_encode: function(e) {
            e = e.replace(/rn/g, "n");
            var t = "";
            for (var n = 0; n < e.length; n++) {
                var r = e.charCodeAt(n);
                if (r < 128) {
                    t += String.fromCharCode(r)
                } else if (r > 127 && r < 2048) {
                    t += String.fromCharCode(r >> 6 | 192);
                    t += String.fromCharCode(r & 63 | 128)
                } else {
                    t += String.fromCharCode(r >> 12 | 224);
                    t += String.fromCharCode(r >> 6 & 63 | 128);
                    t += String.fromCharCode(r & 63 | 128)
                }
            }
            return t
        },
        _utf8_decode: function(e) {
            var t = "";
            var n = 0;
            var r = c1 = c2 = 0;
            while (n < e.length) {
                r = e.charCodeAt(n);
                if (r < 128) {
                    t += String.fromCharCode(r);
                    n++
                } else if (r > 191 && r < 224) {
                    c2 = e.charCodeAt(n + 1);
                    t += String.fromCharCode((r & 31) << 6 | c2 & 63);
                    n += 2
                } else {
                    c2 = e.charCodeAt(n + 1);
                    c3 = e.charCodeAt(n + 2);
                    t += String.fromCharCode((r & 15) << 12 | (c2 & 63) << 6 | c3 & 63);
                    n += 3
                }
            }
            return t
        }
    }
    offer_free_items = {};
    var offers_status = '<?= $pos_settings->offers_status ?>';
    var base_url = '<?= site_url(); ?>';
    var product_variant = 0,
        shipping = 0,
        p_page = 0,
        per_page = 0,
        tcp = "<?= $tcp ?>",
        pro_limit = <?= $pos_settings->pro_limit; ?>,
        brand_id = 0,
        obrand_id = 0,
        cat_id = "<?= $pos_settings->default_category ?>",
        ocat_id = "<?= $pos_settings->default_category ?>",
        sub_cat_id = 0,
        osub_cat_id,
        count = 1,
        an = 1,
        DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0,
        invoice_tax = 0,
        product_discount = 0,
        order_discount = 0,
        total_discount = 0,
        total = 0,
        total_paid = 0,
        grand_total = 0,
        KB = <?= $pos_settings->keyboard ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    var protect_delete = <?php
if (!$Owner && !$Admin) {
    echo $pos_settings->pin_code ? '1' : '0';
} else {
    echo '0';
}
?>
    //var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
    //var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    var lang_total = '<?= lang('total'); ?>',
        lang_items = '<?= lang('items'); ?>',
        lang_discount = '<?= lang('discount'); ?>',
        lang_tax2 = '<?= lang('order_tax'); ?>',
        lang_total_payable = '<?= lang('total_payable'); ?>';
    var java_applet = <?= $pos_settings->java_applet ?>,
        order_data = '',
        bill_data = '';

    function widthFunctions(e) {
        var wh = $(window).height(),
            lth = $('#left-top').height(),
            lbh = $('#left-bottom').height();
        $('#cpinner').css("height", wh - 60);
        $('#cpinner').css("min-height", 410);
        $('#left-middle').css("height", wh - 75);
        $('#left-middle').css("min-height", 410);
        $('#product-list').css("height", wh - 245);
        $('#product-list').css("min-height", 240);
    }
    $(window).bind("resize", widthFunctions);
    $(document).ready(function() {
        <?php if ($Settings->pos_type == 'restaurant') { ?>
        if (localStorage.getItem('table_name')) {
            $('#reference_note').val(localStorage.getItem('table_name'));
            $('#active_table').html(localStorage.getItem('table_name'));
        }

        <?php
    if ($table_id) {
        foreach ($tabless as $table_items) {
            if ($table_id == $table_items->id) {
                ?>

        $('#active_table').html('<?= $suspend_sale->suspend_note ?>');
        $('#reference_note').html('<?= $suspend_sale->suspend_note ?>');
        localStorage.setItem('table_id', '<?= $table_items->id ?>');
        localStorage.setItem('table_name', '<?= $table_items->name ?>');
        $('#kot_restaurant_tables option[value=<?= $table_items->id ?>]').attr('selected', 'selected');
        <?php
                break;
            }
        }
        ?>

        $('#suspend').hide();
        $('#suspend_sale1').show();
        // $('#active_table').html($('#reference_note').val());
        <?php
    }
}
?>

        $('#view-customer').click(function() {
            $('#myModal').modal({
                remote: site.base_url + 'customers/view/' + $("input[name=customer]").val()
            });
            $('#myModal').modal('show');
        });
        //alert(jQuery('#s2id_autogen8_search'));
        //jQuery('.select2-with-searchbox#s2id_autogen8_search').removeAttr("disabled");

        $('textarea').keydown(function(e) {
            if (e.which == 13) {
                var s = $(this).val();
                $(this).val(s + '\n').focus();
                e.preventDefault();
                return false;
            }
        });
        <?php if ($sid) { ?>

        localStorage.setItem('positems', JSON.stringify(<?= $items; ?>));
        localStorage.setItem('olditems', JSON.stringify(<?= $items; ?>));
        <?php } ?>
        <?php if ($this->session->userdata('remove_posls')) { ?>
        if (localStorage.getItem('positems')) {
            localStorage.removeItem('positems');
        }
        if (localStorage.getItem('active_offers')) {
            localStorage.removeItem('active_offers');
        }
        if (localStorage.getItem('applyOffers')) {
            localStorage.removeItem('applyOffers');
        }
        if (localStorage.getItem('posdiscount')) {
            localStorage.removeItem('posdiscount');
        }
        if (localStorage.getItem('postax2')) {
            localStorage.removeItem('postax2');
        }
        if (localStorage.getItem('posshipping')) {
            localStorage.removeItem('posshipping');
        }
        if (localStorage.getItem('poswarehouse')) {
            localStorage.removeItem('poswarehouse');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }
        if (localStorage.getItem('poscustomer')) {
            localStorage.removeItem('poscustomer');
        }
        if (localStorage.getItem('posbiller')) {
            localStorage.removeItem('posbiller');
        }
        if (localStorage.getItem('poscurrency')) {
            localStorage.removeItem('poscurrency');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }
        if (localStorage.getItem('staffnote')) {
            localStorage.removeItem('staffnote');
        }

        if (localStorage.getItem('table_id')) {
            localStorage.removeItem('table_id');
        }

        if (localStorage.getItem('olditems')) {
            localStorage.removeItem('olditems');
        }

        if (localStorage.getItem('table_name')) {
            $('#reference_note').val('');
            $('#active_table').html('--');
            localStorage.removeItem('table_name');
            $('#suspend').show();
            $('#suspend_sale1').hide();
        }
        <?php
    $this->sma->unset_data('remove_posls');
}
?>
        widthFunctions();
        <?php if ($suspend_sale) { ?>
        localStorage.setItem('postax2', '<?= $suspend_sale->order_tax_id; ?>');
        localStorage.setItem('posdiscount', '<?= $suspend_sale->order_discount_id; ?>');
        localStorage.setItem('poswarehouse', '<?= $suspend_sale->warehouse_id; ?>');
        localStorage.setItem('poscustomer', '<?= $suspend_sale->customer_id; ?>');
        localStorage.setItem('posbiller', '<?= $suspend_sale->biller_id; ?>');
        localStorage.setItem('staffnote', '<?= $suspend_sale->suspend_note; ?>');
        <?php }
?>
        <?php if ($this->input->get('customer')) { ?>
        if (!localStorage.getItem('positems')) {
            localStorage.setItem('poscustomer', <?= $this->input->get('customer'); ?>);
        } else if (!localStorage.getItem('poscustomer')) {
            localStorage.setItem('poscustomer', <?= $customer->id; ?>);
        } else {
            localStorage.setItem('poscustomer', <?= $this->input->get('customer'); ?>);
        }
        <?php } else {
    if ($_SESSION['quick_customerid']) {
        ?>
        localStorage.setItem('poscustomer', <?= ( $_SESSION['quick_customerid'] ) ?>);
        <?php } else { ?>
        if (!localStorage.getItem('poscustomer')) {
            localStorage.setItem('poscustomer', <?= $customer->id; ?>);
        }
        <?php }
    ?>
        /* if (!localStorage.getItem('poscustomer')) {
         localStorage.setItem('poscustomer', <?= (($_SESSION['quick_customerid'] ) ? $_SESSION['quick_customerid'] : $customer->id); ?>);
         }*/
        <?php }
?>
        if (!localStorage.getItem('postax2')) {
            localStorage.setItem('postax2', <?= $Settings->default_tax_rate2; ?>);
        }
        $('.select').select2({
            minimumResultsForSearch: 7
        });
        // var customers = [{
        //     id: <?= $customer->id; ?>,
        //     text: '<?= $customer->company == '-' ? $customer->name : $customer->company; ?>'
        // }];
        $('#poscustomer').val(localStorage.getItem('poscustomer')).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function(element, callback) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('customers/getCustomer') ?>/" + $(element).val(),
                    dataType: "json",
                    success: function(data) {
                        $("#customer_deposit_link_default").attr("href",
                            "<?= base_url('customers/add_deposit/') ?>" + data[0].id
                            );
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function(data, page) {
                    if (data.results != null) {
                        $("#customer_deposit_link_default").attr("href",
                            "<?= base_url('customers/add_deposit/') ?>" + data.results[0].id);
                        return {
                            results: data.results
                        };
                    } else {
                        return {
                            results: [{
                                id: '',
                                text: 'No Match Found'
                            }]
                        };
                    }
                }
            }
        });
        // Hide Keybord on mobile and Android device
        /* if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
         
         $('input').attr("onfocus","blur()"); // it is commenting because of input field is disabled, in mobile view and android in customer search.
         
         KB = true;
         
         }*/
        if (KB) {
            // display_keyboards();

            var result = false,
                sct = '';
            $('#poscustomer').on('select2-opening', function() {
                sct = '';
                $('.select2-input').addClass('kb-text');
                //   display_keyboards();
                $('.select2-input').bind('change.keyboard', function(e, keyboard, el) {
                    if (el && el.value != '' && el.value.length > 0 && sct != el.value) {
                        sct = el.value;
                    }
                    if (!el && sct.length > 0) {
                        $('.select2-input').addClass('select2-active');
                        $.ajax({
                            type: "get",
                            async: false,
                            url: "<?= site_url('customers/suggestions') ?>/" + sct,
                            dataType: "json",
                            success: function(res) {
                                if (res.results != null) {
                                    $('#poscustomer').select2({
                                        data: res
                                    }).select2('open');
                                    $('.select2-input').removeClass(
                                        'select2-active');
                                } else {
                                    bootbox.alert('no_match_found');
                                    $('#poscustomer').select2('close');
                                    $('#test').click();
                                }
                            }
                        });
                    }
                });
            });
            $('#poscustomer').on('select2-close', function() {
                $('.select2-input').removeClass('kb-text');
                $('#test').click();
                $('select, .select').select2('destroy');
                $('select, .select').select2({
                    minimumResultsForSearch: 7
                });
            });
            $(document).bind('click', '#test', function() {
                var kb = $('#test').keyboard().getkeyboard();
                kb.close();
                //kb.destroy();
                $('#add-item').focus();
            });
        }

        $(document).on('change', '#posbiller', function() {
            $('#biller').val($(this).val());
        });
        <?php for ($i = 1; $i <= 5; $i++) { ?>
        $('#paymentModal').on('change', '#amount_<?= $i ?>', function(e) {
            $('#amount_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('blur', '#amount_<?= $i ?>', function(e) {
            $('#amount_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('select2-close', '#paid_by_<?= $i ?>', function(e) {
            $('#paid_by_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_no_<?= $i ?>', function(e) {
            $('#cc_no_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_holder_<?= $i ?>', function(e) {
            $('#cc_holder_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#gift_card_no_<?= $i ?>', function(e) {
            $('#paying_gift_card_no_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_month_<?= $i ?>', function(e) {
            $('#cc_month_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_year_<?= $i ?>', function(e) {
            $('#cc_year_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_type_<?= $i ?>', function(e) {
            $('#cc_type_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_cvv2_<?= $i ?>', function(e) {
            $('#cc_cvv2_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#cheque_no_<?= $i ?>', function(e) {
            $('#cheque_no_val_<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#other_tran_no_<?= $i ?>', function(e) {
            $('#other_tran_no_val<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#other_tran_mode_<?= $i ?>', function(e) {
            $('#other_tran_mode_val<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#cc_transac_no_<?= $i ?>', function(e) {

            $('#cc_transac_no_val<?= $i ?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#payment_note_<?= $i ?>', function(e) {
            $('#payment_note_val_<?= $i ?>').val($(this).val());
        });
        <?php }
?>

        $('#payment').click(function() {
            var salesPerson = document.getElementById('sales_person').value;
            var displaySeller = <?php echo $pos_settingss->display_seller; ?>;
            if (salesPerson == '0' && displaySeller == '2') {
                return false;
            }

            amount_1 = 0;
            $('.custom_payment_icon').prop('checked', false);
            $('#checkbox1').prop('checked', true);
            //$('#paid_by_1').select2('cash');
            $('#paid_by_1').val('cash');
            $('#paid_by_1').trigger('change');
            checkoutOffers();
            <?php if ($sid) { ?>
            suspend = $('<span></span>');
            suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" />');
            suspend.appendTo("#hidesuspend");
            <?php } ?>

            // var twt = formatDecimal((total + invoice_tax) - total_discount);
            var twt = formatDecimal(gtotal);

            if (count == 1) {
                bootbox.alert('<?= lang('x_total'); ?>');
                return false;
            }
            gtotal = formatDecimal(twt);
            <?php if ($pos_settings->rounding) { ?>
            round_total = roundNumber(gtotal, <?= $pos_settings->rounding ?>);
            <?php if ($pos_settingss->pos_amount == 1) { ?>
            $('#amount_1').val(round_total);
            $('#submit-sale').focus();
            amount_1 = round_total;
            <?php } else { ?>
            $('#amount_1').val(0);
            <?php } ?>
            var rounding = formatDecimal(0 - (gtotal - round_total));
            $('#twt').text(formatMoney(round_total) + ' (' + formatMoney(rounding) + ')');
            $('#quick-payable').html('<i class="fa fa-inr" aria-hidden="true"></i> ' + round_total);
            <?php } else { ?>
            $('#twt').text(formatMoney(gtotal));
            $('#quick-payable').html('<i class="fa fa-inr" aria-hidden="true"></i> ' + gtotal);
            $payment_det = $('.card').val();
            /* if ($payment_det == "cash") {
             $('#amount_1').val(0);
             } else {
             $('#amount_1').val(gtotal);
             }*/


            <?php if ($pos_settingss->pos_amount == 1) { ?>
            $('#amount_1').val(gtotal);
            amount_1 = gtotal;
            // $('#submit-sale').focus();
            <?php } else { ?>
            $('#amount_1').val(0);
            <?php } ?>
            //$('#amount_1').val(0);
            <?php }//end else     ?>

            $('#item_count').text(count - 1);
            $('#paymentModal').appendTo("body").modal('show');
            //$('#amount_1').focus();
            // $('#payModalLabel').focus();
            depositGiftAmt();
            dueamount();
            //                    setTimeout(function(){                       
            //                        $('#amount_1').val(amount_1);
            //                        $('#amount_1').focus();
            //                    }, 1000);
            //alert(jQuery('button#quick-payable').html());
            //$('#clear-cash-notes').trigger('click');
            //$('#quick-payable').trigger('click');

            // $('#amount_1').focus();
            // calculateTotals('amount_1');
            //$('#submit-sale').focus();
        });
        /**
         * Get Diposit Amount And Gif card Amount
         */
        function depositGiftAmt() {
            var cusotmer_id = $('#poscustomer').val();
            $.ajax({
                type: 'ajax',
                dataType: 'json',
                method: 'get',
                url: '<?= base_url('customers/getdeposit/') ?>' + cusotmer_id,
                async: true,
                success: function(result) {
                    console.log('success');
                    if (result.deposit) {
                        if (result.deposit > 0) {

                            $('input[value="deposit"]').trigger('click');
                        }
                        $('#showdeposit').html('Deposit Amount: Rs. ' + result.deposit);
                    }
                    if (result.award_points) {
                        $('#showawardpoint').html('Award Point: ' + result.award_points);
                    }
                    if (result.giftcardqty && result.giftcardAmt) {
                        $('#showgiftcard').html('Gift Card: ' + result.giftcardqty + ', ' +
                            ' Gift Card Amount:  ' + result.giftcardAmt);
                    }

                    if (result.deposit || result.award_points || (result.giftcardqty && result
                            .giftcardAmt)) {
                        $('#showamtbalance').show();
                    }

                },
                error: function() {
                    console.log('erorr');
                    $('#showdeposit').html('');
                    $('#showawardpoint').html('');
                    $('#showgiftcard').html('');
                    if (result.deposit || result.award_points || (result.giftcardqty && result
                            .giftcardAmt)) {
                        $('#showamtbalance').hide();
                    }
                }
            })
        }
        //ShowTotalDeposit

        function dueamount() {
            var cusotmer_id = $('#poscustomer').val();
            $.ajax({
                type: 'ajax',
                dataType: 'json',
                method: 'get',
                url: '<?= base_url('Reports/getDueAmount/') ?>' + cusotmer_id,
                async: true,
                success: function(result) {
                    console.log('success');
                    if (result.total_amount && result.paid) {
                        var due = parseFloat(result.total_amount) + parseFloat(result.rounding);
                        var due1 = parseFloat(due) - parseFloat(result.paid);
                        $('#showdue').html('Due Amount: Rs. ' + due1 + '.00');
                    }


                    if (result.total_amount || result.paid) {
                        $('#showduebalance').show();
                    }

                },
                error: function() {
                    console.log('erorr');
                    $('#showdue').html('');

                    if (result.total_amount || result.paid) {
                        $('#showduebalance').hide();
                    }
                }
            })
        }
        /**
         * End 
         */

        $('#paymentModal').on('show.bs.modal', function(e) {
            $('#submit-sale').attr('disabled', false);
        });
        $('#paymentModal').on('shown.bs.modal', function(e) {
            //$('#amount_1').prop('readonly','readonly');
            $('input#s2id_autogen4_search').prop('readonly', 'readonly');
            setTimeout(function() {
                $('#amount_1').focus();
                $('#amount_1').focusout();
                calculateTotals('amount_1');
            }, 50);
            //$('#payModalLabel').focus();
        });
        var pi = 'amount_1',
            pa = 2;
        $(document).on('click', '.quick-cash', function() {
            var $quick_cash = $(this);
            var amt = $quick_cash.contents().filter(function() {
                return this.nodeType == 3;
            }).text();
            var th = ',';
            var $pi = $('#' + pi);
            amt = formatDecimal(amt.split(th).join("")) * 1 + $pi.val() * 1;
            $pi.val(formatDecimal(amt)).focus();
            var note_count = $quick_cash.find('span');
            if (note_count.length == 0) {
                $quick_cash.append('<span class="badge">1</span>');
            } else {
                note_count.text(parseInt(note_count.text()) + 1);
            }
        });
        $(document).on('click', '#clear-cash-notes', function() {
            $('.quick-cash').find('.badge').remove();
            $('#' + pi).val('0').focus();
            //$('#balance').text('0').focus();
        });
        $(document).on('keyup', '.gift_card_no', function() {
            $('.final-submit-btn').prop('disabled', true);
        });
        $(document).on('change', '.gift_card_no', function() {
            $('.final-submit-btn').prop('disabled', true);
            var cn = $(this).val() ? $(this).val() : '';
            var payid = $(this).attr('id');
            var SplitPayId = payid.split('_');
            var MainPaynumber = SplitPayId[3];
            id = payid.substr(payid.length - 1);
            if (cn != '' && payid == 'gift_card_no_' + MainPaynumber) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function(data) {
                        if (data === false) {
                            $('#gift_card_no_' + id).parent('.form-group').addClass(
                                'has-error');
                            bootbox.alert('<?= lang('incorrect_gift_card') ?>');
                        } else if (data.customer_id !== null && data.customer_id !== $(
                                '#poscustomer').val()) {
                            $('#gift_card_no_' + id).parent('.form-group').addClass(
                                'has-error');
                            bootbox.alert('<?= lang('gift_card_not_for_customer') ?>');
                            //location.reload();
                            // return false;
                        } else {
                            $('.final-submit-btn').prop('disabled', false);
                            $('#gc_details_' + id).html('<small>Card No: ' + data.card_no +
                                '<br>Value: ' + data.value + ' - Balance: ' + data
                                .balance + '</small>');
                            $('#gift_card_no_' + id).parent('.form-group').removeClass(
                                'has-error');
                            //calculateTotals();
                            $('#amount_' + id).val(gtotal >= data.balance ? data.balance :
                                gtotal).focus();
                        }
                    }
                });
            }

        });
        $(document).on('click', '.addButton', function() {
            if (pa <= 5) {
                $('#paid_by_1, #pcc_type_1').select2('destroy');
                var phtml = $('#payments').html(),
                    update_html = phtml.replace(/_1/g, '_' + pa);
                pi = 'amount_' + pa;
                $('#multi-payment').append(
                    '<button type="button" class="close close-payment" style="margin: -10px 0px 0 0;"><i class="fa fa-2x">&times;</i></button>' +
                    update_html);
                var amount_length = $('.amount').length;
                for (var k = 1; k <= amount_length; k++) {
                    var paid_by_inr = 'award_point';
                    $("#paid_by_" + pa + " option[value=" + paid_by_inr + "]").remove();
                }
                $('#paid_by_1, #pcc_type_1, #paid_by_' + pa + ', #pcc_type_' + pa).select2({
                    minimumResultsForSearch: 7
                });
                read_card();
                pa++;
            } else {
                bootbox.alert('<?= lang('max_reached') ?>');
                return false;
            }
            // display_keyboards();
            $('#paymentModal').css('overflow-y', 'scroll');
        });
        $(document).on('click', '.close-payment', function() {
            $(this).next().remove();
            $(this).remove();
            pa--;
            $('#amount_' + (pa - 1)).focus();
        });
        $(document).on('focus', '.amount', function() {
            pi = $(this).attr('id');
            calculateTotals(pi);
        }).on('blur', '.amount', function() {
            calculateTotals(pi);
        });
        /* balance Amt is not greater then that */
        $(".amount").focusout(function() {
            var amt = $(this).val();
            //console.log(amt);
            var select_payed_type = $("input[name='colorRadio']:checked").val();
            if (select_payed_type == 'gift_card') {
                $('.final-submit-btn').prop('disabled', false);
                $('#errorgift_1').html('');
                setCustomerGiftcard(amt, 'gamount');
            }

            if (select_payed_type == 'deposit') {
                $('.final-submit-btn').prop('disabled', false);
                $('#errordeposit_1').html('');
                setCustomerDeposit(amt, 'damount');
            }
            //console.log(select_payed_type);
        });
        /**/


        $("#add_item").autocomplete({
            source: function(request, response) {
                if (!$('#poscustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?= lang('select_above'); ?>');
                    //response('');
                    $('#add_item').focus();
                    $('#add_item').removeClass('ui-autocomplete-loading');
                    return false;
                }

                <?php if ($Settings->pos_type == 'restaurant' && $pos_settings->restaurant_table) { ?>
                if (!localStorage.getItem('table_id')) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?= lang('Please Select Table'); ?>');
                    //response('');
                    $('#add_item').focus();
                    $('#add_item').removeClass('ui-autocomplete-loading');
                    return false;
                }
                <?php } ?>

                $.ajax({
                    type: 'get',
                    url: '<?= site_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#poswarehouse").val(),
                        customer_id: $("#poscustomer").val(),
                        <?php if ($Settings->pos_type == 'restaurant') { ?>
                        table_id: localStorage.getItem('table_id'),
                        <?php } ?>
                    },
                    beforeSend: function() {
                        return true;
                    },
                    success: function(data) {
                        var exp = request.term.split("_"); // Using bacrcode Scanning
                        if (exp[1]) { //// Using bacrcode Scanning
                            if (data[0].id !== 0) {
                                add_invoice_item(data[0]);
                                response('');
                                document.getElementById('add_item').value = '';
                            } else {
                                bootbox.alert('<?= lang('no_match_found') ?>',
                                function() {
                                    $('#add_item').focus();
                                });
                                $('#add_item').removeClass('ui-autocomplete-loading');
                                $('#add_item').val('');
                            }
                        } else {
                            response(data);
                        }
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                        $('#add_item').removeClass('ui-autocomplete-loading');
                    });
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $('#add_item').removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                        $('#add_item').removeClass('ui-autocomplete-loading');
                    });
                    $(this).val('');
                }
            },
            select: function(event, ui) {

                event.preventDefault();
                // alert(ui.item.options);
                if (ui.item.batchs) {
                    product_batch_model_call(ui.item);
                    $(this).val('');
                    <?php if ($sid) { ?>
                    kotprint_flag = true;
                    <?php } ?>
                    $('#add_item').removeClass('ui-autocomplete-loading');
                    return true;
                }

                <?php if ($Settings->attributes == 1) { ?>
                if (ui.item.options) {
                    product_option_model_call(ui.item);
                    $(this).val('');
                    <?php if ($sid) { ?>
                    kotprint_flag = true;
                    <?php } ?>
                    $('#add_item').removeClass('ui-autocomplete-loading');
                    return true;
                }
                <?php } ?>
                if (ui.item.id !== 0) {
                    var row = add_invoice_item(ui.item);
                    if (row)
                        $(this).val('');
                    <?php if ($sid) { ?>
                    kotprint_flag = true;
                    <?php } ?>
                    $('#add_item').removeClass('ui-autocomplete-loading');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                    $('#add_item').removeClass('ui-autocomplete-loading');
                }
            }
        });
        //$('#add_item_qr').blur(function (e) {
        $('#add_item_qr').on("click blur", function(event) {
            $("#add_item_qr").autocomplete({
                source: function(request, response) {
                    if (!$('#poscustomer').val()) {
                        $('#add_item_qr').val('').removeClass('ui-autocomplete-loading');
                        bootbox.alert('<?= lang('select_above'); ?>');
                        //response('');
                        $('#add_item_qr').focus();
                        return false;
                    }

                    <?php if ($Settings->pos_type == 'restaurant' && $pos_settings->restaurant_table) { ?>
                    if (!localStorage.getItem('table_id')) {
                        $('#add_item_qr').val('').removeClass('ui-autocomplete-loading');
                        bootbox.alert('<?= lang('Please Select Table'); ?>');
                        //response('');
                        $('#add_item_qr').focus();
                        $('#add_item_qr').removeClass('ui-autocomplete-loading');
                        return false;
                    }
                    <?php } ?>

                    //                       tream = request.term.split(" "); 
                    //                       tream.forEach((element) =>{ //.trim()
                    $.ajax({
                        type: 'get',
                        url: '<?= site_url('sales/suggestions_qr'); ?>',
                        dataType: "json",
                        data: {
                            term: request.term.trim(),
                            warehouse_id: $("#poswarehouse").val(),
                            customer_id: $("#poscustomer").val()
                        },
                        beforeSend: function() {
                            return true;
                        },
                        success: function(data) {

                            var tream = request.term.split(
                                "<?= $Settings->barcode_separator_weight ?>"
                                ); //
                            tream.forEach((element, index) => {
                                var exp = request.term.split(
                                    "<?= $pos_settings->rounding ?>"
                                    ); // Using bacrcode Scanning
                                //                                    if (exp[1]) { 
                                //                                       
                                if (data[index].id !== 0) {
                                    console.log('--- Data  --');
                                    console.log(data[index]);
                                    add_invoice_item(data[index]);
                                    response('');
                                    document.getElementById(
                                        'add_item_qr').value = '';
                                    $('#add_item_qr').removeClass(
                                        'ui-autocomplete-loading');
                                } else {
                                    bootbox.alert(
                                        '<?= lang('no_match_found') ?>',
                                        function() {
                                            $('#add_item_qr')
                                            .focus();
                                        });
                                    $('#add_item_qr').removeClass(
                                        'ui-autocomplete-loading');
                                    $('#add_item_qr').val('');
                                }
                                //                                       
                                //                                    }else{
                                //                                        response(data);
                                //                                    }
                            });
                            //                                var exp = request.term.split("_"); // Using bacrcode Scanning
                            //                                if (exp[1]) { //Using bacrcode Scanning
                            //                                    
                            //                                    if (data[0].id !== 0) {
                            //                                        
                            //                                        add_invoice_item(data[0]);
                            //                                        response('');
                            //                                        document.getElementById('add_item_qr').value = '';
                            //                                    } else {
                            //
                            //                                        bootbox.alert('<?= lang('no_match_found') ?>', function () {
                            //                                            $('#add_item_qr').focus();
                            //                                        });
                            //                                        $('#add_item_qr').removeClass('ui-autocomplete-loading');
                            //                                        $('#add_item_qr').val('');
                            //
                            //                                    }
                            //                                } else {
                            //                                    response(data);
                            //                                }
                        }
                    });
                    //                        });
                },
                minLength: 1,
                autoFocus: false,
                delay: 250,
                response: function(event, ui) {
                    if (ui.content) {
                        if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                            bootbox.alert('<?= lang('no_match_found') ?>', function() {
                                $('#add_item_qr').focus();
                            });
                            $(this).val('');
                        } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                            ui.item = ui.content[0];
                            $(this).data('ui-autocomplete')._trigger('select',
                                'autocompleteselect', ui);
                            $(this).autocomplete('close');
                        } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                            bootbox.alert('<?= lang('no_match_found') ?>', function() {
                                $('#add_item_qr').focus();
                            });
                            $(this).val('');
                        }
                    }
                },
                select: function(event, ui) {

                    event.preventDefault();
                    if (ui.item.options) {
                        product_option_model_call(ui.item);
                        $(this).val('');
                        <?php if ($sid) { ?>
                        kotprint_flag = true;
                        <?php } ?>
                        return true;
                    }

                    if (ui.item.id !== 0) {

                        var row = add_invoice_item(ui.item);
                        if (row)
                            $(this).val('');
                        <?php if ($sid) { ?>
                        kotprint_flag = true;
                        <?php } ?>
                    } else {
                        bootbox.alert('<?= lang('no_match_found') ?>');
                    }
                }
            });
        });
        <?php
if ($pos_settings->tooltips) {
    echo '$(".pos-tip").tooltip();';
}
?>
        // $('#posTable').stickyTableHeaders({fixedOffset: $('#product-list')});
        $('#posTable').stickyTableHeaders({
            scrollableArea: $('#product-list')
        });
        $('#product-list, #category-list, #subcategory-list, #brands-list, #amnt').perfectScrollbar({
            suppressScrollX: true
        });
        $('select, .select').not('#multiselect1, #multiselect1_to').select2({
            minimumResultsForSearch: 7
        });
        $(document).on('click', '.product', function(e) {

            <?php if ($Settings->pos_type == 'restaurant' && $pos_settings->restaurant_table) { ?>
            if (!localStorage.getItem('table_id')) {
                bootbox.alert('<?= lang('Please Select Table'); ?>');
                return false;
            }
            <?php } ?>

            $('#modal-loading').hide();
            code = $(this).val();
            wh = $('#poswarehouse').val();
            cu = $('#poscustomer').val();
            $.ajax({
                type: "get",
                url: "<?= site_url('pos/getProductDataByCode') ?>",
                data: {
                    code: code,
                    warehouse_id: wh,
                    customer_id: cu,
                    <?php if ($Settings->pos_type == 'restaurant') { ?>
                    table_id: localStorage.getItem('table_id'),
                    <?php } ?>
                },
                dataType: "json",
                success: function(data) {
                    <?php
if ($Settings->attributes == 1 && $pos_settings->product_variant_popup == 1) {
    ?>
                    if (data !== null && data.options) {
                        product_option_model_call(data);
                        $(this).val('');
                        return true;
                    }
                    <?php
}
?>
                    e.preventDefault();
                    if (data !== null) {
                        add_invoice_item(data);
                        $('#modal-loading').hide();
                    } else {
                        bootbox.alert('<?= lang('no_match_found') ?>');
                        $('#modal-loading').hide();
                    }
                },
                fail: function(e) {

                }
            });
        });
        $(document).on('click', '.favouritebtn', function() {
            $('#previous1').addClass('hidden');
            $('#next1').addClass('hidden');
            $('#previous').removeClass('hidden');
            $('#next').removeClass('hidden');
            // if (cat_id != $(this).val()) {
            $('#open-category').click();
            $('#modal-loading').show();
            cat_id = 0;
            $.ajax({
                type: "get",
                url: "<?= site_url('pos/featuerdProducts'); ?>",
                data: {
                    per_page: 'n'
                },
                dataType: "json",
                success: function(data) {

                    $('#item-list').empty();
                    var newPrs = $('<div></div>');
                    newPrs.html(data.products);
                    newPrs.appendTo("#item-list");
                    $('.owl-carousel.second').find('.owl-stage').empty();
                    $('.owl-carousel.second').find('.owl-stage').append(data
                    .subcategories2);
                    $('#subcat').find('.subcategorydiv').empty();
                    nav_pointer();
                    tcp = data.tcp;
                }
            }).done(function() {
                p_page = 'n';
                $('#modal-loading').hide();
                nav_pointer();
            });
        });
        $(document).on('click', '.category', function() {
            $('#previous1').addClass('hidden');
            $('#next1').addClass('hidden');
            $('#previous').removeClass('hidden');
            $('#next').removeClass('hidden');
            // if (cat_id != $(this).val()) {
            $('#open-category').click();
            $('#modal-loading').show();
            cat_id = $(this).val();
            //alert(cat_id);
            $.ajax({
                type: "get",
                url: "<?= site_url('pos/ajaxcategorydata'); ?>",
                data: {
                    category_id: cat_id
                },
                dataType: "json",
                success: function(data) {
                    $('#item-list').empty();
                    var newPrs = $('<div></div>');
                    newPrs.html(data.products);
                    newPrs.appendTo("#item-list");
                    /*$('#subcategory-list').empty();
                     var newScs = $('<div></div>');
                     newScs.html(data.subcategories);
                     newScs.appendTo("#subcategory-list");*/
                    console.log(data.subcategories2);
                    // (data.subcategories=="")? $('#subcat').hide():$('#subcat').show();
                    <?php if ($Settings->theme != 'default') { ?>
                    /* if(data.subcategories==""){ 
                     $('#subcat').hide();
                     document.getElementById('ajaxproducts').style.height="80%";
                     document.getElementById('item-list').style.height="95%";
                     }else{
                     $('#subcat').show();
                     document.getElementById('ajaxproducts').style.height="74%";
                     document.getElementById('item-list').style.height="95%";
                     }*/
                    if (data.subcategories == "") {
                        $('#subcat').hide();
                        document.getElementById('ajaxproducts').style.height = "68%";
                        document.getElementById('item-list').style.height = "90%";
                    } else {
                        $('#subcat').show();
                        document.getElementById('ajaxproducts').style.height = "60%";
                        document.getElementById('item-list').style.height = "97%";
                    }
                    <?php } ?>

                    $('.owl-carousel.second').find('.owl-stage').empty();
                    $('.owl-carousel.second').find('.owl-stage').append(data
                    .subcategories2);
                    $('#subcat').find('.subcategorydiv').empty();
                    $('#subcat').find('.subcategorydiv').append(data.subcategories2);
                    tcp = data.tcp;
                    nav_pointer();
                }
            }).done(function() {
                p_page = 'n';
                $('#category-' + cat_id).addClass('active');
                $('#category-' + ocat_id).removeClass('active');
                ocat_id = cat_id;
                $('#modal-loading').hide();
                nav_pointer();
            });
            //}
        });
        $('#category-' + cat_id).addClass('active');
        $(document).on('click', '.brand', function() {

            $('#previous1').addClass('hidden');
            $('#next1').addClass('hidden');
            $('#previous').removeClass('hidden');
            $('#next').removeClass('hidden');
            if (brand_id != $(this).val()) {
                $('#open-brands').click();
                $('#modal-loading').show();
                brand_id = $(this).val();
                $.ajax({
                    type: "get",
                    url: "<?= site_url('pos/ajaxbranddata'); ?>",
                    data: {
                        brand_id: brand_id
                    },
                    dataType: "json",
                    success: function(data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data.products);
                        newPrs.appendTo("#item-list");
                        tcp = data.tcp;
                        nav_pointer();
                    }
                }).done(function() {
                    p_page = 'n';
                    $('#brand-' + brand_id).addClass('active');
                    $('#brand-' + obrand_id).removeClass('active');
                    obrand_id = brand_id;
                    $('#category-' + cat_id).removeClass('active');
                    $('#subcategory-' + sub_cat_id).removeClass('active');
                    cat_id = 0;
                    sub_cat_id = 0;
                    $('#modal-loading').hide();
                    nav_pointer();
                });
            }
        });
        $(document).on('click', '.subcategory', function() {
            $('#previous1').addClass('hidden');
            $('#next1').addClass('hidden');
            $('#previous').removeClass('hidden');
            $('#next').removeClass('hidden');
            if (sub_cat_id != $(this).val()) {
                $('#open-subcategory').click();
                $('#modal-loading').show();
                sub_cat_id = $(this).val();
                $.ajax({
                    type: "get",
                    url: "<?= site_url('pos/ajaxproducts'); ?>",
                    data: {
                        category_id: cat_id,
                        subcategory_id: sub_cat_id,
                        per_page: p_page
                    },
                    dataType: "json",
                    success: function(data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data.products);
                        newPrs.appendTo("#item-list");
                        tcp = data.tcp;
                    }
                }).done(function() {
                    p_page = 'n';
                    $('#subcategory-' + sub_cat_id).addClass('active');
                    $('#subcategory-' + osub_cat_id).removeClass('active');
                    $('#modal-loading').hide();
                });
            }
        });
        $(document).ready(function() {
            $('#next').click(function() {
                if (p_page == 'n') {
                    p_page = 0
                }
                p_page = p_page + pro_limit;
                console.log(tcp + '>=' + pro_limit + '|' + p_page + ' < ' + tcp);
                if (tcp >= pro_limit && p_page < tcp) {
                    $('#modal-loading').show();
                    var senddata = '';
                    var sendurl = '';
                    if ($.isNumeric(cat_id) && cat_id != 0) {
                        senddata = {
                            category_id: cat_id,
                            subcategory_id: sub_cat_id,
                            per_page: p_page
                        };
                        sendurl = "<?= site_url('pos/ajaxproducts') ?>";
                    } else {
                        senddata = {
                            per_page: p_page
                        };
                        sendurl =
                            "<?= ((isset($featuerd_products) && $featuerd_products == 1) ? site_url('pos/featuerdProducts') : site_url('pos/ajaxproducts')); ?>";
                    }

                    $.ajax({
                        type: "get",
                        url: sendurl,
                        data: senddata,
                        dataType: "json",
                        success: function(data) {
                            $('#item-list').empty();
                            var newPrs = $('<div></div>');
                            newPrs.html(data.products);
                            newPrs.appendTo("#item-list");
                            nav_pointer();
                            tcp = data.tcp;
                        }
                    }).done(function() {
                        $('#modal-loading').hide();
                    });
                } else {
                    p_page = p_page - pro_limit;
                }
            });
            $('#previous').click(function() {
                if (p_page == 'n') {
                    p_page = 0;
                }
                if (p_page != 0) {
                    $('#modal-loading').show();
                    p_page = p_page - pro_limit;
                    if (p_page == 0) {
                        p_page = 'n'
                    }

                    var senddata = '';
                    var sendurl = '';
                    if ($.isNumeric(cat_id) && cat_id != 0) {
                        senddata = {
                            category_id: cat_id,
                            subcategory_id: sub_cat_id,
                            per_page: p_page
                        };
                        sendurl = "<?= site_url('pos/ajaxproducts') ?>";
                    } else {
                        senddata = {
                            per_page: p_page
                        };
                        sendurl =
                            "<?= ((isset($featuerd_products) && $featuerd_products == 1) ? site_url('pos/featuerdProducts') : site_url('pos/ajaxproducts')); ?>";
                    }



                    $.ajax({
                        type: "get",
                        url: sendurl,
                        data: senddata,
                        dataType: "json",
                        success: function(data) {
                            $('#item-list').empty();
                            var newPrs = $('<div></div>');
                            newPrs.html(data.products);
                            newPrs.appendTo("#item-list");
                            nav_pointer();
                            tcp = data.tcp;
                        }

                    }).done(function() {
                        $('#modal-loading').hide();
                    });
                }
            });
        });
        $(document).on('change', '.paid_by', function() {
            $('.final-submit-btn').prop('disabled', false);
            var p_val = $(this).val(),
                id = $(this).attr('id'),
                pa_no = id.substr(id.length - 1);
            $('#rpaidby').val(p_val);
            if (p_val == 'cash') {
                $('.pcheque_' + pa_no).hide();
                $('.pcc_' + pa_no).hide();
                $('.pother_' + pa_no).hide();
                $('.pcash_' + pa_no).show();
                $('#payment_note_' + pa_no).focus();
            } else if (p_val == 'CC' || p_val == 'DC' || p_val == 'stripe' || p_val == 'ppp' || p_val ==
                'authorize') {
                $('.pcheque_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pother_' + pa_no).hide();
                $('.pcc_' + pa_no).show();
                $('#swipe_' + pa_no).focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pother_' + pa_no).hide();
                $('.pcheque_' + pa_no).show();
                $('#cheque_no_' + pa_no).focus();
            } else if (p_val == 'other' || p_val == 'NEFT') {
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pcheque_' + pa_no).hide();
                document.getElementById('note').style.display = "block";
                $('.pother_' + pa_no).show();
                $('#other_tran_no_' + pa_no).focus();
            } else if (p_val == 'PAYTM' || p_val == 'Googlepay' || p_val == 'complimentry' || p_val ==
                'UPI_QRCODE' || p_val == 'swiggy' || p_val == 'zomato' || p_val == 'ubereats' ||
                p_val == 'magicpin') {
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pcheque_' + pa_no).hide();
                $('.pother_' + pa_no).show();
                document.getElementById('note').style.display = "none";
                $('#other_tran_no_' + pa_no).focus();
            } else {
                $('.pcheque_' + pa_no).hide();
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pother_' + pa_no).hide();
            }
            if (p_val == 'gift_card') {
                $('.final-submit-btn').prop('disabled', true);
                $('.gc_' + pa_no).show();
                $('.ngc_' + pa_no).hide();
                $('#gift_card_no_' + pa_no).focus();
            } else {
                $('.ngc_' + pa_no).show();
                $('.gc_' + pa_no).hide();
                $('#gc_details_' + pa_no).html('');
                $('#errorgift_' + pa_no).html('');
            }

            if (p_val == 'deposit') {
                $('.final-submit-btn').prop('disabled', true);
                $('.db_' + pa_no).show();
            } else {
                $('.db_' + pa_no).hide();
                $('#depositdetails_' + pa_no).html('');
                $('#errordeposit_' + pa_no).html('');
            }
            if (p_val == 'award_point') {
                $('.final-submit-btn').prop('disabled', true);
                $('.ap_' + pa_no).show();
            } else {
                $('.ap_' + pa_no).hide();
                $('#apdetails_' + pa_no).html('');
                $('#errorap_' + pa_no).html('');
            }
        });
        $(document).on('click', '#submit-sale', function(e) {

            var payid1 = $('#paid_by_1').val();
            var _self = $(this);
            <?php if ($is_pharma): ?>
            var patient_name = $('#patient_name').val();
            if (patient_name == '') {
                patient_name = '-';
                // bootbox.alert('Please enter Patient name.');
                // return false;
            }
            var doctor_name = $('#doctor_name').val();
            if (doctor_name == '') {
                doctor_name = '-';
                // bootbox.alert('Please enter Doctor name.');
                //  return false;
            }
            $('#patient_name1').val(patient_name);
            $('#doctor_name1').val(doctor_name);
            <?php endif; ?>
            //--------------------- validation  For Cheque--------------//
            if (payid1 == 'Cheque') {
                var cheque = $('#cheque_no_1').val();
                if (cheque.trim() == '') {
                    bootbox.alert('Please enter cheque number.');
                    return false;
                }
                if (cheque.length != 6) {
                    bootbox.alert('Please enter valid cheque number.');
                    return false;
                }
            }
            //------------------ validation  For Cheque End--------------//

            //--------------------- validation  For Cheque--------------//
            if (payid1 == 'other') {
                var other_tran_no_1 = $('#other_tran_no_1').val();
                if (other_tran_no_1.trim() == '') {
                    bootbox.alert('Please enter Transaction No.');
                    return false;
                }
            }

            //------------------ validation  For Cheque End--------------//

            /*if (total_paid == 0 || total_paid < grand_total) {
             bootbox.confirm("<?= lang('paid_l_t_payable'); ?>", function (res) {
             if (res == true) {
             $('#pos_note').val(localStorage.getItem('posnote'));
             $('#staff_note').val(localStorage.getItem('staffnote'));
             //$('#submit-sale').text('<?= lang('loading'); ?>').attr('disabled', true);
             
             if (_self.prop('name') == 'cmd') {
             $('.cmdnotprint').text('<?= lang('loading'); ?>').attr('disabled', true);
             } else if (_self.prop('name') == 'cmdprint1') {
             $('.cmdprint1').text('<?= lang('loading'); ?>').attr('disabled', true);
             } else if (_self.prop('name') == 'cmdprint') {
             $('.cmdprint').text('<?= lang('loading'); ?>').attr('disabled', true);
             }
             
             //$('#pos-sale-form').submit();
             //return false;
             //alert($(this).prop('name'));
             if (_self.prop('name') == 'cmd') {
             var form = $('#pos-sale-form');
             
             $.ajax({
             type: form.attr('method'),
             url: form.attr('action'),
             data: form.serialize()
             }).done(function (data) {
             location.reload();
             // Optionally alert the user of success here...
             }).fail(function (data) {
             alert('failed');
             });
             } else {
             $('#pos-sale-form').submit();
             }
             
             }
             });
             return false;
             } else {*/
            var paid_by = $('#paid_by_val_1').val();
            if (paid_by == 'CC' || paid_by == 'DC' || paid_by == 'ppp' || paid_by == 'stripe' ||
                paid_by == 'authorize') {
                var cc_transac_no = $('#cc_transac_no_1').val().trim();
                var cc_payment_other = $('#cc_payment_other').val().trim();
                if (cc_transac_no == '') {
                    bootbox.alert('Please Enter Card Transaction No.');
                    $('#cc_transac_no').parent().addClass('has-error');
                    $('#cc_transac_no').focus();
                    return false;
                } else {
                    $('#cc_transac_no_1').val(cc_transac_no);
                    $('#payment_note_val_1').val(cc_payment_other);
                }

                /*   var pcc_no_1= $('#pcc_no_1').val().trim();
                 if(pcc_no_1==''){
                 bootbox.alert('Please Enter Card No.');
                 $('#pcc_no_1').parent().addClass('has-error');
                 $('#pcc_no_1').focus();
                 return false;
                 }
                 var pcc_holder_1 = $('#pcc_holder_1').val().trim();
                 if(pcc_holder_1==''){
                 bootbox.alert('Please Enter Card Holder Name.');
                 $('#pcc_holder_1').parent().addClass('has-error');
                 $('#pcc_holder_1').focus();
                 return false;
                 }
                 var pcc_month_1 = $('#pcc_month_1').val().trim();
                 if(pcc_month_1==''){
                 bootbox.alert('Please Enter Card Exp. Mont');
                 $('#pcc_month_1').parent().addClass('has-error');
                 $('#pcc_month_1').focus();
                 return false;
                 }
                 var cc_year = $('#pcc_year_1').val().trim();
                 if(cc_year==''){
                 bootbox.alert('Please Enter Card Exp. Year ');
                 $('#pcc_year_1').parent().addClass('has-error');
                 $('#pcc_year_1').focus();
                 return false;
                 }else{
                 if(!validYear($('#pcc_year_1').val())){
                 bootbox.alert('Please Enter Valid Card Exp. Year ');
                 $('#pcc_year_1').parent().addClass('has-error');
                 $('#pcc_year_1').focus();
                 return false;
                 }
                 }*/
                /*
                 var pcc_cvv2  = $('#pcc_cvv2_1').val().trim(); 
                 if(pcc_cvv2==''){
                 bootbox.alert('Please Enter CVV No.');
                 $('#pcc_cvv2_1').parent().addClass('has-error');
                 $('#pcc_cvv2_1').focus();
                 return false;
                 }else{
                 if(!validCVV($('#pcc_cvv2_1').val())){
                 bootbox.alert('Please Enter Valid CVV ');
                 $('#pcc_cvv2_1').parent().addClass('has-error');
                 $('#pcc_cvv2_1').focus();
                 return false;
                 }
                 }*/
            }

            if (paid_by == 'Cheque') {
                var cheque_no_1 = $('#cheque_no_1').val().trim();
                if (cheque_no_1 == '') {
                    bootbox.alert('Please Enter Cheque No.');
                    $('#cheque_no_1').parent().addClass('has-error');
                    $('#cheque_no_1').focus();
                    return false;
                }
            }

            if (paid_by == 'gift_card') {
                var gift_card_no_1 = $('#gift_card_no_1').val().trim();
                if (gift_card_no_1 == '') {
                    bootbox.alert('Please Enter Gift Card');
                    $('#gift_card_no_1').parent().addClass('has-error');
                    $('#gift_card_no_1').focus();
                    return false;
                }
            }

            $('#pos_note').val(localStorage.getItem('posnote'));
            $('#staff_note').val(localStorage.getItem('staffnote'));
            $(this).text('<?= lang('loading'); ?>').attr('disabled', true);
            //$('#pos-sale-form').submit();

            //                        alert($(this).prop('name'))
            //                        return false;
            if ($(this).prop('name') == 'cmd') {
                var form = $('#pos-sale-form');
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize()
                }).done(function(data) {
                    location.reload();
                    // Optionally alert the user of success here...
                }).fail(function(data) {
                    alert('failed');
                });
                return false;
            } else {

                $('#pos-sale-form').submit();
            }

            //}
        });
        $('#suspend').click(function() {
            if (count <= 1) {
                <?php if ($Settings->pos_type == 'restaurant') { ?>
                // $('#susModal').modal();
                setTimeout(function() {
                    $("#reference_note").focus();
                }, 500);
                <?php } else { ?>
                bootbox.alert('<?= lang('x_suspend'); ?>');
                return false;
                <?php } ?>
            } else {
                checkoutOffers();
                <?php if ($Settings->pos_type == 'restaurant') { ?>
                // $('#susModal').modal();
                setTimeout(function() {
                    $("#reference_note").focus();
                }, 500);
                <?php } else { ?>
                $('#susModal').modal();
                <?php } ?>
                setTimeout(function() {
                    $("#reference_note").focus();
                }, 500);
            }
        });
        /*$('#suspend_sale').click(function () {
             ref = $('#reference_note').val();
             
             if (!ref || ref == '') {
             bootbox.alert('<?= lang('type_reference_note'); ?>');
             return false;
             } else {
             suspend = $('<span></span>');
<?php if ($sid) { ?>
                 suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" /><input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
<?php } else { ?>
                 suspend.html('<input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
<?php }
?>
             suspend.appendTo("#hidesuspend");
             $('#total_items').val(count - 1);
             $('#pos-sale-form').submit();
             
             }
             });*/
        $('#suspend_sale, #suspend_sale1').click(function() {
            <?php if ($sid) { ?>
            if (kotprint_flag == true) {
                printKOT();
                deletekotItems();
            }
            <?php } elseif ($pos_settingss->order_receipt == 1) { ?>
            printKOT();
            <?php } ?>
            update_token();
            // return false;
            var errors = '';
            var ref = $('#reference_note').val();
            errors = (!ref || ref == '') ? "<br><?= lang('type_reference_note'); ?>" : '';
            var table_id = (site['settings']['pos_type'] == 'restaurant') ? $(
                '.restaurant_tables :selected').val() : $('#reference_note')
        .val(); // $('.restaurant_tables :selected').val();
            //console.log(restaurant_tables);
            //alert(restaurant_tables);
            if (site['settings']['pos_type'] == 'restaurant') {
                errors += (!table_id || table_id == '') ?
                    "<br><?= lang('restaurant_tables_error'); ?>" : '';
                errors = $.trim(errors);
                if (errors != '') {
                    bootbox.alert(errors);
                    return false;
                } else {
                    suspend = $('<span></span>');
                    <?php if ($sid) { ?>
                    suspend.html(
                        '<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" /><input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' +
                        ref + '" /><input type="hidden" name="table_id" value="' + table_id +
                        '" />    ');
                    <?php } else { ?>
                    suspend.html(
                        '<input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' +
                        ref + '" />  <input type="hidden" name="table_id" value="' + table_id +
                        '" />  ');
                    <?php }
?>
                    suspend.appendTo("#hidesuspend");
                    $('#total_items').val(count - 1);
                    $('#pos-sale-form').submit();
                }
            } else {
                suspend = $('<span></span>');
                <?php if ($sid) { ?>
                suspend.html(
                    '<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" /><input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' +
                    ref + '" /><input type="hidden" name="table_id" value="' + table_id + '" />    '
                    );
                <?php } else { ?>
                suspend.html(
                    '<input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' +
                    ref + '" />  <input type="hidden" name="table_id" value="' + table_id + '" />  '
                    );
                <?php }
?>
                suspend.appendTo("#hidesuspend");
                $('#total_items').val(count - 1);
                $('#pos-sale-form').submit();
            }
        });
    });
    var printing_pos = {
        print: function(data, action) {
            /*var mywindow = window.open('', 'sma_pos_print', 'height=500,width=300');
             mywindow.document.write('<html><head><title>Print</title><style>@media screen,print {    .btn { display:none }  }</style>');
             mywindow.document.write('<link rel="stylesheet" href="<?= $assets ?>styles/helpers/bootstrap.min.css" type="text/css" />');
             mywindow.document.write('</head><body >');
             mywindow.document.write(data);*/
            //mywindow.document.write('<script>'+'  setTimeout(function(){ window.print(); window.close();this.checkChild(); /**/ }, 100); </'+'script>');
            /*  mywindow.document.write('</body></html>');*/

            //setTimeout(function() {
            //  mywindow.print();
            /*   mywindow.close();*/
            if (action == 'kot') {
                this.checkChild();
            }
            //},100);

        },
        checkChild: function() {
            $("#suspend_sale").trigger('click');
            $('.kot_tbl').empty();
        }
    };
    $(".print_kot").click(function() {
        $('.kot_tbl').html($('#order_tbl').html());
        var kot_tbl_head_title = $('#reference_note').val();
        $('.kot_tbl').find('h4').html(kot_tbl_head_title);
        str = $('.kot_tbl').html();
        str = str.split("<td>#");
        str = str.join("<td>");
        $('.kot_tbl').html(str);
        //Popup($('.kot_tbl').html());
        setPrintRequestData($('.kot_tbl').html());
        printing_pos.print($('.kot_tbl').html(), 'kot')
    });
    <?php if ($pos_settings->java_applet) { ?>
    $(document).ready(function() {
        $('#print_order').click(function() {

            console.log(order_data)
            setPrintRequestData(order_data);
            printBill(order_data);
        });
        $('#print_bill').click(function() {
            setPrintRequestData(bill_data);
            printBill(bill_data);
        });
    });
    <?php } else { ?>
    $(document).ready(function() {
        $('#print_order').click(function() {
            setPrintRequestData($('#order_tbl').html());
            Popup($('#order_tbl').html());
        });
        $('#print_bill').click(function() {
            setPrintRequestData($('#bill_tbl').html());
            console.log($('#bill_tbl').html());
            Popup($('#bill_tbl').html());
        });
    });
    <?php }
?>
    $(function() {
        $(".alert").effect("shake");
        setTimeout(function() {
            $(".alert").hide('blind', {}, 500)
        }, 15000);
        <?php if ($pos_settings->display_time) { ?>
        var now = new moment();
        $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
        setInterval(function() {
            var now = new moment();
            $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
        }, 1000);
        <?php }
?>
    });
    <?php if (!$pos_settings->java_applet) { ?>

    function Popup(data) {
        var mywindow = window.open('', 'sma_pos_print', 'height=500,width=300');
        mywindow.document.write(
            '<html><head><title>Print</title><style>@media screen,print {    .btn { display:none }  }</style>');
        mywindow.document.write(
            '<link rel="stylesheet" href="<?= $assets ?>styles/helpers/bootstrap.min.css" type="text/css" />');
        mywindow.document.write('</head><body >');
        mywindow.document.write(data);
        mywindow.document.write('<script>' + '  setTimeout(function(){ window.print();window.close();/**/ }, 100); </' +
            'script>');
        mywindow.document.write('</body></html>');
        //    mywindow.print();
        //    mywindow.close(); 
        return false;
    }
    <?php }
        ?>
    </script>
    <?php
        $s2_lang_file = read_file('./assets/config_dumps/s2_lang.js');
        foreach (lang('select2_lang') as $s2_key => $s2_line) {
            $s2_data[$s2_key] = str_replace(array('{', '}'), array('"+', '+"'), $s2_line);
        }
        $s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
        ?>
    <script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/perfect-scrollbar.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery.calculator.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/bootstrapValidator.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>pos/js/plugins.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>pos/js/parse-track-data.js"></script>
    <script type="text/javascript" src="<?= $assets ?>pos/js/pos.ajax.js"></script>
    <script type="text/javascript" src="<?= $assets ?>pos/js/split.order.js"></script>
    <script type="text/javascript" src="<?= $assets ?>pos/js/split.order.payment.js"></script>

    <?php if ($pos_settings->java_applet) {
    ?>
    <script type="text/javascript" src="<?= $assets ?>pos/qz/js/deployJava.js"></script>
    <script type="text/javascript" src="<?= $assets ?>pos/qz/qz-functions.js"></script>
    <script type="text/javascript">
    deployQZ('themes/<?= $Settings->theme ?>/assets/pos/qz/qz-print.jar', '<?= $assets ?>pos/qz/qz-print_jnlp.jnlp');

    function printBill(bill) {
        usePrinter("<?= $pos_settings->receipt_printer; ?>");
        printData(bill);
    }
    <?php
    $printers = json_encode(explode('|', $pos_settings->pos_printers));
    echo $printers . ';';
    ?>

    function printOrder(order) {
        for (index = 0; index < printers.length; index++) {
            usePrinter(printers[index]);
            printData(order);
        }
    }
    </script>

    <?php }
?>

    <script type="text/javascript">
    function paynear_mobile_app() {
        $('#paynear_mobile_app').val(1);
        $('#paynear_btn_holder').css("display", 'none');
        $('#paynear_btn_app_holder').css("display", 'block');
        //alert('IN MOBILE APP');
    }

    $('.sortable_table tbody').sortable({
        containerSelector: 'tr'
    });

    function cardDetails(cart_no, card_name, card_month, card_year, card_cvv, txt) {
        txt = GetCardType(cart_no);
        //alert(txt);
        jQuery('#cardNo').html(cart_no);
        //1234-XXXX-XXXX-1234
        jQuery('#pcc_no_1').val(cart_no);
        jQuery('#pcc_no_1').hide();
        jQuery('#pcc_holder_1').val(card_name);
        jQuery('#pcc_holder_1').hide();
        jQuery('#pcc_month_1').val(card_month);
        jQuery('#pcc_month_1').hide();
        jQuery('#pcc_year_1').val(card_year);
        jQuery('#pcc_year_1').hide();
        jQuery('#swipe_1').hide();
        var str = jQuery('#cardNo').html();
        str1 = str.split("");
        var card_split = str1[0] + '' + str1[1] + '' + str1[2] + '' + str1[3] + '-XXXX-XXXX-' + str1[12] + '' + str1[
            13] + '' + str1[14] + '' + str1[15];
        jQuery('#cardNo').html(card_split);
        var ctype = jQuery('#cardty').html(txt);
        jQuery('#pcc_type_1 option[value=ctype]').attr('selected', 'selected');
        jQuery('#s2id_pcc_type_1').val(txt);
        jQuery('#s2id_pcc_type_1').hide();
        jQuery("#pcc_cvv2_1").css("margin-top", "-65px");
    }

    function GetCardType(number) {
        var re = new RegExp("^4");
        if (number.match(re) != null) {
            return "Visa";
        }
        re = new RegExp("^(34|37)");
        if (number.match(re) != null) {
            return "American Express";
        }
        re = new RegExp("^5[1-5]");
        if (number.match(re) != null) {
            return "MasterCard";
        }
        re = new RegExp("^6011");
        if (number.match(re) != null) {
            return "Discover";
        }
        return "unknown";
    }

    function getQRCode(fullURL) {
        param = fullURL.split('/');
        addItemTest(param[param.length - 1]);
    }
    //getQRCode('http://dev.greatwebsoft.co.in/pos1/products/view/16');

    function actQRCam() {
        window.MyHandler.activateQRCam(true);
        return false;
    }

    $(document).ready(function() {
        $('#custname').val($("input[name=customer]").val());
        $('#custname').prop('name', 'customer');
        $('#custsearch').val($('#poswarehouse').val());
        $('#custsearch').prop('name', 'warehouse');
        //var v = $('#poscustomer').val();
        $('#poscustomer').change(function() {
            $('#custname').val(jQuery('#poscustomer').val());
        });
        $('#poswarehouse').change(function() {
            $('#custsearch').val(jQuery('#poswarehouse').val());
        });
    });
    /*------------------ Payment Icon Button  updated on 21012017 BY SW------------------*/
    $(document).ready(function() {

        $('.custom_payment_icon').click(function() {
            $('.final-submit-btn').prop('disabled', false);
            var custom_payment_value = $(this).val();
            $('select#paid_by_1.paid_by').val(custom_payment_value).change();
            $('#paid_by_val_1').val(custom_payment_value);
            if (custom_payment_value != 'cash' && $('#amount_1').val() == '0') {
                $('#quick-payable').click();
            }
            if (custom_payment_value == 'CC' || custom_payment_value == 'DC') {

            }
            if (custom_payment_value == 'payswiff') {

                //setCCPaymentAndroid();

                $('#amount_val_1').val(gtotal);
                setTimeout(function() {
                    jQuery('button#submit-sale.btn.btn-block.btn-lg.btn-primary.cmdprint1')
                        .trigger('click');
                }, 100);
            }

            if (custom_payment_value == 'paynear') {

                if ($('#paynear_mobile_app').val() == '1') {
                    paynear_opt = $(this).attr('data-value');
                    $('#paynear_mobile_app_type').val(paynear_opt);
                }

                $('#amount_val_1').val(gtotal);
                setTimeout(function() {
                    jQuery('button#submit-sale.btn.btn-block.btn-lg.btn-primary.cmdprint1')
                        .trigger('click');
                }, 100);
            } else if (custom_payment_value == 'instamojo' || custom_payment_value == 'ccavenue' ||
                custom_payment_value == 'payumoney') {
                $('#amount_val_1').val(gtotal);
                setTimeout(function() {
                    jQuery('button#submit-sale.btn.btn-block.btn-lg.btn-primary.cmdprint1')
                        .trigger('click');
                }, 100);
            } else if (custom_payment_value == 'gift_card') {
                $('.final-submit-btn').prop('disabled', false);
                var bal = $('#amount_1').val();
                setCustomerGiftcard(bal, 'gift_card');
            } else if (custom_payment_value == 'deposit') {
                $('.final-submit-btn').prop('disabled', false);
                var bal = $('#amount_1').val();
                setCustomerDeposit(bal, 'deposit');
            } else if (custom_payment_value == 'award_point') {
                $('.final-submit-btn').prop('disabled', true);
                var bal = $('#amount_1').val();
                setCustomerAwardPoint(bal, 'award_point');
            }

            $('#amount_1').focus();
        });
        $('#add_item').blur();
    });
    $(document).on('change', '#OTP', function() {
        $('#SuccessOTP').text('');
        $('#ErrOTP').text('');
        var OTP = $(this).val();
        var OrgOTP = $('#OrgOTP').val();
        console.log(OTP + '==' + OrgOTP);
        if (OTP != OrgOTP) {
            $('#ErrOTP').text('Entered OTP is Wrong');
        } else {
            $('#paymentModal').removeClass('payment_box');
            $('#myModalAwardPoint').modal('hide');
            $('.final-submit-btn').prop('disabled', false);
        }
    });

    function setCustomerAwardPoint(ba, sel) {
        var today = '<?= date('Y-m-d') ?>';
        var cu = $('#poscustomer').val();
        $.ajax({
            type: "get",
            url: "<?= site_url('pos/searchAwardPointByCustomer') ?>",
            data: {
                customer_id: cu,
                bill_amt: ba
            },
            dataType: "json",
            success: function(res) {
                console.log(res.msg);
                if (res.phone) {
                    console.log('Mobile Number:', res.phone);
                    // Example usage
                    $('#MobileNumber').val(res.phone);
                }
                $('.ap_1').show();
                if (res.msg == 'AwardPointNotFound') {
                    $('.final-submit-btn').prop('disabled', true);
                    $('#errorap_1').html(
                        '<small class="red">Award Point not found for this customer, please select other payment mode</small>'
                        );
                } else if (res.msg == 'AwardPointNotAdded') {
                    $('.final-submit-btn').prop('disabled', true);
                    $('#errorap_1').html(
                        '<small class="red">Award Point not added, please select other payment mode</small>'
                        );
                } else if (res.msg == 'AwardPointNotValid') {
                    $('.final-submit-btn').prop('disabled', true);
                    $('#errorap_1').html(
                        '<small class="red">you can only redeem award points worth 50% of invoice value</small>'
                        );
                } else {
                    $('.final-submit-btn').prop('disabled', true);
                    $('#amount_1').val(res.invoice_amount);
                    $('#ap_val_1').val(res.redeem_point);
                    $('#ap_1').val(res.redeem_point);
                    calculateTotals('amount_1');
                    sendSMS();
                    $('#paymentModal').addClass('payment_box');
                    $('#myModalAwardPoint').modal('show');
                }
            }
        });
    }

    function verification_box() {
        $('#paymentModal').removeClass('payment_box');
    }

    function sendSMS() {
        $('#SuccessOTP').text('');
        $('#ErrOTP').text('');
        $('#OTP').val('');
        var MobileNo = $('#MobileNumber').val();
        if (MobileNo.length == '') {
            alert('Mobile No is required');
            return false;
        }
        $('#SuccessOTP').text(
            'OTP has been sent on your Mobile number for verification. If not getting OTP then click on send OTP');
        $.ajax({
            type: 'ajax',
            datatype: 'json',
            method: "GET",
            url: "<?php echo base_url(); ?>pos/send_otp",
            data: {
                MobileNo: MobileNo
            },
            success: function(response) {
                //console.log(response);
                var res = $.parseJSON(response);
                callSendOTP(res.url);
                $('#OrgOTP').val(res.OTP);
                //console.log(res.sms.msg);
                //$('#paymentModal').removeClass('payment_box');
                //$('#OrgOTP').val(response);

            },
        });
    }

    function callSendOTP(passurl) {
        //        alert(passurl);
        $.ajax({
            type: 'ajax',
            datatype: 'json',
            method: 'get',
            url: passurl,
            async: false,
            success: function(result) {
                console.log('success');
            },
            error: function() {
                console.log('error');
            }
        });
    }

    function setCustomerGiftcard(bal, sel) {
        var today = '<?= date('Y-m-d') ?>';
        var cus = $('#poscustomer').val();
        $.ajax({
            type: "get",
            url: "<?= site_url('pos/searchGiftcardByCustomer') ?>",
            data: {
                customer_id: cus,
                bill_amt: bal
            },
            dataType: "json",
            success: function(data) {

                if (data.card_no !== null && data.balance > 0) {
                    if (today > data.expiry) {
                        bootbox.alert('<?= lang('Gift card number is incorrect or expired.') ?>');
                    } else {
                        $('#gift_card_no_1').val(data.card_no);
                        $('#gc_details_1').html('<small>Card No: ' + data.card_no + '<br>Value: ' + data
                            .value + ' - Balance: ' + data.balance + '</small>');
                        $('#gift_card_no_1').parent('.form-group').removeClass('has-error');
                        //calculateTotals();
                        //$('#amount_1').val(ba >= data.balance ? data.balance : ba).focus();
                        //$('#amount_1').val(ba).focus();
                        $('#paying_gift_card_no_val_1').val(data.card_no);
                        if (bal > parseFloat(data.balance)) {
                            $('#errorgift_1').html(
                                '<small class="red">Amount Greater than Gift Card</small>');
                            $('.final-submit-btn').prop('disabled', true);
                            if (sel == 'gift_card') {
                                bootbox.alert(
                                    'Invoice amount is greater that available gift card balance please select other payment mode'
                                    );
                            }

                        }
                    }
                } else {
                    $('#gift_card_no_1').val('');
                    $('#paying_gift_card_no_val_1').val('');
                    $('#amount_1').val('');
                    $('#gc_details_1').html(
                        '<small class="red">Giftcard not found for this customer</small>');
                    $('#gift_card_no_1').parent('.form-group').removeClass('has-error');
                    //bootbox.alert('<?= lang('gift_card_not_for_customer') ?>');
                }
            }
        });
    }

    function setCustomerDeposit(ba, sel) {
        var today = '<?= date('Y-m-d') ?>';
        var cu = $('#poscustomer').val();
        $.ajax({
            type: "get",
            url: "<?= site_url('pos/searchDepositByCustomer') ?>",
            data: {
                customer_id: cu,
                bill_amt: ba
            },
            dataType: "json",
            success: function(data) {

                if (data.balanceamt > 0) {
                    //$('#amount_1').val(ba).focus();
                    $('#depositdetails_1').html('<small>Value: ' + data.value + ' - Balance: ' + data
                        .balanceamt + '</small>');
                    if (ba > parseFloat(data.balanceamt)) {
                        $('#errordeposit_1').html('<small class="red">Amount Greater than Deposit</small>');
                        $('.final-submit-btn').prop('disabled', true);
                        if (sel == 'deposit') {
                            bootbox.alert(
                                'Invoice amount is greater that available Deposit balance please select other payment mode'
                                );
                        }

                    }
                } else {
                    $('#depositdetails_1').html(
                        '<small class="red">Deposit not found for this customer</small>');
                }
            }
        });
    }
    /*------------------ setting button  type in hidden  updated on 21012f017 BY SW------------------*/
    jQuery('.cmdprint').on('click', function() {
        jQuery('#submit_type').val('print');
    });
    jQuery('.cmdprint1').on('click', function() {
        jQuery('#submit_type').val('notprint_notredirect');
    });
    jQuery('.cmdnotprint').on('click', function() {
        // alert("bhavana");
        jQuery('#submit_type').val('notprint');
    });
    /*___________  End ___________________ */
    jQuery('#add_item').on('focus', function() {
        // alert('test');
        $('#custname').val(jQuery('#poscustomer').val());
        $('#custname').prop('name', 'customer');
        // $('#custsearch').val($('#poswarehouse :selected').val());
        $('#custsearch').prop('name', 'warehouse');
    });

    function setCustomerName(valu) {
        $('#custname').val(valu);
        $('#custname').prop('name', 'customer');
    }

    function addItemTest(itemId) {
        $('#modal-loading').show();
        var code;
        $.ajax({
            type: "get", //base_url("index.php/admin/do_search")
            url: "<?= site_url('pos/getProductByID') ?>",
            data: {
                id: itemId
            },
            dataType: "json",
            success: function(data) {
                code = data.code;
                code = code,
                    wh = $('#poswarehouse').val(),
                    cu = $('#poscustomer').val();
                $.ajax({
                    type: "get",
                    url: "<?= site_url('pos/getProductDataByCode') ?>",
                    data: {
                        code: code,
                        warehouse_id: wh,
                        customer_id: cu
                    },
                    dataType: "json",
                    success: function(data) {
                        if (data !== null) {
                            add_invoice_item(data);
                            $('#modal-loading').hide();
                        } else {
                            bootbox.alert('<?= lang('no_match_found') ?>');
                            $('#modal-loading').hide();
                        }
                    }
                });
            }
        });
    }

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        } else {
            return true;
        }
    }

    function validCVV(cvv) {
        var re = /^[0-9]{3,4}$/;
        return re.test(cvv);
    }

    function validYear(year) {
        var re = /^(19|20)\d{2}$/;
        return re.test(year);
    }
    </script>
    <script type="text/javascript" charset="UTF-8">
    <?= $s2_file_date ?>
    </script>
    <div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>
    <?php
        if (isset($_REQUEST['test'])) {
            $errorUrl = "http://" . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI];
            $logger = array('Testing error view', $errorUrl);
            $this->sma->pos_error_log($logger);
        }
        ?>
    <script>
    $(document).ready(function() {
        <?php if ($Settings->theme != 'default') { ?>
        document.getElementById('errormsg').style.display = 'none';
        <?php } ?>
        $('[data-toggle="tooltip"]').tooltip();
        //$("#s2id_autogen8_search").prop('disabled', 'true');

        //quick sales start//
        var pname_mi = 'Miscellaneous Item';
        var pname_sc = 'Service Charges';
        var pname_tc = 'Transportation Charges';
        var qty = 1;
        // $("#pname").hide();
        $('#mitems').click(function() {
            $("#pname").show();
            $('#mname').val(pname_mi);
            $('#mquantity').val(qty);
            $('#mname').focus();
            $('#mquantity').focus();
            $('#mprice').focus();
        });
        $('#scharges').click(function() {
            $("#pname").show();
            $('#mname').val(pname_sc);
            $('#mquantity').val(qty);
            $('#mname').focus();
            $('#mquantity').focus();
            $('#mprice').focus();
        });
        $('#tcharges').click(function() {
            $("#pname").show();
            $('#mname').val(pname_tc);
            $('#mquantity').val(qty);
            $('#mname').focus();
            $('#mquantity').focus();
            $('#mprice').focus();
        });
        $('#other').click(function() {

            $("#pname").show();
            $('#mname').val("");
            $('#mquantity').val(qty);
            $('#mname').focus();
            $('#mquantity').focus();
            $('#mprice').focus();
        });
        //quick sales end//


    })

    function Rfid() {
        $.get('https://elintpos.in/Posadmin_15.01/api/rfid/?get=<?php echo site_url(); ?>', function(data) {
            data3 = data.split(':');
            $.each(data3, function(index, value) {
                data4 = value.split('A');
                addItemByProductCode(data4[1]);
            });
        });
    }

    function addItemByProductCode(code) {

        code = code,
            wh = $('#poswarehouse').val(),
            cu = $('#poscustomer').val();
        $.ajax({
            type: "get",
            url: "<?= site_url('pos/getProductDataByCode') ?>",
            data: {
                code: code,
                warehouse_id: wh,
                customer_id: cu
            },
            dataType: "json",
            success: function(data) {
                if (data !== null) {
                    add_invoice_item(data);
                    $('#modal-loading').hide();
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                    $('#modal-loading').hide();
                }
            }
        });
    }

    var specialKeys = new Array();

    function IsNumeric(e) {
        var keyCode = e.which ? e.which : e.keyCode
        var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
        document.getElementById("error").style.display = ret ? "none" : "inline";
        return ret;
    }

    $(document).ready(function() {
        $("input[type='radio']").on('change', function() {
            var selectedValue = $("input[name='gender']:checked").val();
            if (selectedValue) {
                $('#reference_note').val($("input[name='gender']:checked").val());
            }
        });
        $("input[name='carry_out_customer_info']").on('change keyup', function() {

            var reference_note = "Carry Out : " + $(this).val();
            $("#reference_note").val(reference_note);
        });
        $(".key-btns").on('click', function() {
            //alert($(this).find('button').text())
            var reference_note = $("#reference_note").val();
            var keypad_val = $(this).find('button').text();
            if (reference_note == '') {
                reference_note = 'Table : ' + keypad_val;
            } else {
                reference_note = reference_note.replace("Table", '');
                reference_note = 'Table' + reference_note + keypad_val;
            }
            $("#reference_note").val(reference_note);
        });
    });

    function call_checkout() {

        localStorage.setItem('staffnote', $("#reference_note").val());
        $("#payment").trigger('click');
    }

    function enDis(idName) {
        var txt = jQuery('#' + idName).attr('readonly');
        if (txt == 'readonly') {
            jQuery('#' + idName).attr('readonly', false);
        } else {
            jQuery('#' + idName).attr('readonly', 'readonly');
        }
    }

    function addProductToVarientProduct(option_id, option_name) {
        //alert('aa');
        //console.log(option_name);

        var note = '';
        if (option_name.toLowerCase() == 'note') {

            note = prompt("Please enter your note");
            if (note == null) {
                return false;
            }
        }

        var itemId = $(".modalvarient").find('.product_item_id').attr("value")
        //var option_id = $(".modalvarient").find('.option_id').val();
        var term = $(".modalvarient").find('.product_term').val() +
            "<?php echo $this->Settings->barcode_separator; ?>" + option_id;
        wh = $('#poswarehouse').val(),
            cu = $('#poscustomer').val();
        $.ajax({
            type: "get",
            url: "<?= site_url('sales/suggestions') ?>",
            data: {
                term: term,
                option_id: option_id,
                warehouse_id: wh,
                customer_id: cu,
                option_note: note
            },
            dataType: "json",
            success: function(data) {

                /*var selected_option = $.map(data[0].options, function(element,index) {
                 //console.log(element);
                 if(element.id == data[0].row.option && element.name.toLowerCase() == 'note'){
                 //alert(element.toSource())
                 var note = prompt("Please enter your note");
                 data[0].options[index].name = data[0].options[index].name+": " +note;
                 return element;
                 }
                 //return {name: element.name.substring(data[0].option), value: element.value};
                 });*/


                if (data !== null) {
                    add_invoice_item(data[0]);
                    $('.modalvarient').hide();
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                    $('.modalvarient').hide();
                }
            }
        });
    }

    function product_option_model_call(product) {
        //console.log(product);
        var subdomain = new URL(window.location.href).hostname.split('.')[0];
        var product_options = '';
        product_options = "" +
            "<div class='row'>" +
            "<div class='col-sm-12'>";
        $.each(product.options, function(index, element) {

            if (element.name.toLowerCase() == 'note') {
                product_options +=
                    '</div><div style="clear:both"></div></div><div class="note-btn"><button onclick="addProductToVarientProduct(\'' +
                    element.id + '\',\'' + element.name +
                    '\')"><i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>Note</button></div>';
            } else {
                product_options += '<button onclick="addProductToVarientProduct(\'' + element.id + '\',\'' +
                    element.name + '\')" type="button"  title="' + element.name +
                    '" class="btn-prni btn-info pos-tip" tabindex="-1"><img src="assets/mdata/' + subdomain +
                    '/uploads/thumbs/no_image.png" alt="' + element.name +
                    '" style="width:33px;height:33px;" class="img-rounded"><span>' + element.name +
                    '</span></button>';
            }
        });
        product_options += "<input type='hidden' class='product_item_id' name='product_item_id' value='" + product.row
            .id + "' >";
        product_options += "<input type='hidden' class='product_term' name='product_term' value='" + product.row.code +
            "' >";
        //$('#modal-loading').show();
        $('.modalvarient').find('.modal-title').html(product.row.name);
        $('.modalvarient').find('.modal-body').empty();
        $('.modalvarient').find('.modal-body').append(product_options);
        $('.modalvarient').show();
        return true;
    }


    /**
     * Batch No
     * @param {type} product
     * @returns {Boolean}
     */
    function product_batch_model_call(product) {

        var product_options = '';
        product_options = "" +
            "<div class='row'>" +
            "<div class='col-sm-12'>";
        $.each(product.batchs, function(index, element) {

            if (element.batch_no.toLowerCase() == 'note') {
                product_options +=
                    '</div><div style="clear:both"></div></div><div class="note-btn"><button onclick="addProductToVarientProduct(\'' +
                    element.id + '\',\'' + element.batch_no +
                    '\')"><i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>Note</button></div>';
            } else {
                product_options += '<button onclick="addProductBatchno(\'' + element.id + '\',\'' + element
                    .batch_no + '\')" type="button"  title="' + element.batch_no +
                    '" class="btn-prni btn-info pos-tip" tabindex="-1"><span>' + element.batch_no +
                    '</span></button>';
            }
        });
        product_options += "<input type='hidden' class='product_item_id' name='product_item_id' value='" + product.row
            .id + "' >";
        product_options += "<input type='hidden' class='product_term' name='product_term' value='" + product.row.code +
            "' >";
        //$('#modal-loading').show();
        $('.modalvarient').find('.modal-title').html(product.row.name);
        $('.modalvarient').find('.modal-body').empty();
        $('.modalvarient').find('.modal-body').append(product_options);
        $('.modalvarient').show();
        return true;
    }

    function addProductBatchno(batch_id, batch_no) {
        var itemId = $(".modalvarient").find('.product_item_id').attr("value")
        var term = $(".modalvarient").find('.product_term').val();
        wh = $('#poswarehouse').val();
        cu = $('#poscustomer').val();
        $.ajax({
            type: "get",
            url: "<?= site_url('sales/suggestions') ?>",
            data: {
                term: term,
                batch_no: batch_id,
                warehouse_id: wh,
                customer_id: cu
            },
            dataType: "json",
            success: function(data) {
                console.log(data);
                if (data !== null) {
                    add_invoice_item(data[0]);
                    $('.modalvarient').hide();
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                    $('.modalvarient').hide();
                }
            }
        });
    }





    function modalClose(modalClass) {
        $('.' + modalClass).hide();
    }
    </script>
    <script>
    function change_offerdetails(offer) {
        //alert(offer);

        $.ajax({
            type: "ajax",
            dataType: 'json',
            method: 'get',
            url: "pos/change_offerdetails/" + offer,
            success: function(result) {
                if (result) {
                    $('#offermsg').hide();
                    document.getElementById('offer_id').value = result.id;
                    document.getElementById('offer_name').value = result.offer_name;
                    //alert( document.getElementById('offer_name').value);
                    document.getElementById('offer_amount_including_tax').value = result
                        .offer_amount_including_tax;
                    document.getElementById('offer_discount_rate').value = result.offer_discount_rate;
                    document.getElementById('offer_end_date').value = result.offer_end_date;
                    document.getElementById('offer_end_time').value = result.offer_end_time;
                    document.getElementById('offer_free_products').value = result.offer_free_products;
                    document.getElementById('offer_free_products_quantity').value = result
                        .offer_free_products_quantity;
                    document.getElementById('offer_items_condition').value = result.offer_items_condition;
                    document.getElementById('offer_on_brands').value = result.offer_on_brands;
                    document.getElementById('offer_on_category_quantity').value = result
                        .offer_on_category_quantity;
                    document.getElementById('offer_on_days').value = result.offer_on_days;
                    document.getElementById('offer_on_invoice_amount').value = result
                        .offer_on_invoice_amount;
                    document.getElementById('offer_on_products').value = result.offer_on_products;
                    document.getElementById('offer_on_products_amount').value = result
                        .offer_on_products_amount;
                    document.getElementById('offer_on_products_quantity').value = result
                        .offer_on_products_quantity;
                    document.getElementById('offer_on_warehouses').value = result.offer_on_warehouses;
                    document.getElementById('offer_start_date').value = result.offer_start_date;
                    document.getElementById('offer_start_time').value = result.offer_start_time;
                    console.log(result);
                }
                console.log(result);
            },
            error: function() {
                console.log('error');
            }

        });
    }
    </script>
    <!--        offer details updates-->
    <script>
    $(document).ready(function() {
        $('#offermsg').hide();
        $("#update_offer").click(function(event) {
            event.preventDefault();
            //var offer_name = $("input#offer_name").val();
            // var offer_amount_including_tax = $("input#offer_amount_including_tax").val();
            var offerupdate = $("#offerUpdates").serialize();
            console.log(offerupdate);
            jQuery.ajax({
                type: "ajax",
                url: "pos/updates_offerdetails?" + offerupdate,
                dataType: 'json',
                method: 'get',
                // data: {offer_name: offer_name, offer_amount_including_tax: offer_amount_including_tax},
                success: function(result) {
                    if (result) {
                        $('#offermsg').html(result["msg"]);
                        $('#offermsg').show(1000);
                        setTimeout("$('#offermsg').hide(); ", 3000);
                        setTimeout(function() {
                            $('#offer_modal').modal('hide');
                        }, 200);
                        console.log(result);
                    }
                }
            });
        });
    });
    </script>
    <!--input type='button' onClick="Rfid()" value="rfid 7635 record" style="display:none"  / -->

    <script>
    //Responce From Online Pos Mobile WebApp
    function getCCPaymentAndroidresponce(data) {

        alert(data);
    }

    /*
     function setCCPaymentAndroid() { 
     
     //exicute only for webApp.
     if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
     //alert(print_data);
     if (localStorage.getItem('positems')) {
     var data = '{"table_number":"","customerName":"' + $.trim($("#s2id_poscustomer").text()) + '","total":"' + $.trim($("#total").text()) + '","tax":"' + $.trim($("#ttax2").text()) + '","discount":"' + localStorage.getItem('posdiscount') + '","items": [' + localStorage.getItem('positems') + ']  }';
     
     //alert(data);
     //var pos_item_string = data;
     $.ajax({
     type: "POST",
     url: '<?= base_url('pos/set_saleorder'); ?>',
     data:'data='+data,
     beforeSend: function(){ },
     success: function(pos_item_string){			 
     window.MyHandler.InitiateCCPaymentAndroid(pos_item_string);			 
     }
     }); 
     
     } else {
     //var pos_item_string = JSON.stringify(localStorage.getItem('positems'));
     //alert('---data not found---');
     window.MyHandler.InitiateCCPaymentAndroid('{status:"false"}');
     }
     }
     
     }
     */

    function setPrintRequestData(print_data) {
        //alert('--Store to handler---');
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            //alert(print_data);
            if (localStorage.getItem('positems')) {
                var data = '{"table_number":"","customerName":"' + $.trim($("#s2id_poscustomer").text()) +
                    '","total":"' + $.trim($("#total").text()) + '","tax":"' + $.trim($("#ttax2").text()) +
                    '","discount":"' + localStorage.getItem('posdiscount') + '","items": [' + localStorage.getItem(
                        'positems') + ']  }';
                //alert(data);
                var pos_item_string = data;
                //alert(pos_item_string);
                window.MyHandler.setPrintRequestPos(pos_item_string);
            } else {
                //var pos_item_string = JSON.stringify(localStorage.getItem('positems'));
                //alert('---data not found---');
                window.MyHandler.setPrintRequestPos('{status:"false"}');
            }
        }
    }
    </script>

    <div class="modal splitOrder" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" onclick="modalClose('splitOrder')" data-dismiss="modal"
                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Split Order</h4>
                </div>
                <div class="modal-body">
                    <div class="row debug"></div>
                    <div class="row">
                        <div class="col-sm-5">
                            <lable>Order 1</lable>
                            <select name="from[]" id="multiselect1" class="form-control" size="8" multiple="multiple">
                                <!--option value="1" data-position="1">Item 1</option>
                                    <option value="2" data-position="2">Item 5</option>
                                    <option value="2" data-position="3">Item 2</option>
                                    <option value="2" data-position="4">Item 4</option>
                                    <option value="3" data-position="5">Item 3</option -->
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" id="multiselect1_rightAll" class="btn btn-block"><i
                                    class="glyphicon glyphicon-forward"></i></button>
                            <button type="button" id="multiselect1_rightSelected" class="btn btn-block"><i
                                    class="glyphicon glyphicon-chevron-right"></i></button>
                            <button type="button" id="multiselect1_leftSelected" class="btn btn-block"><i
                                    class="glyphicon glyphicon-chevron-left"></i></button>
                            <button type="button" id="multiselect1_leftAll" class="btn btn-block"><i
                                    class="glyphicon glyphicon-backward"></i></button>
                        </div>
                        <div class="col-sm-5">
                            <lable>Order 2</lable>
                            <select name="to[]" id="multiselect1_to" class="form-control" size="8"
                                multiple="multiple"></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!--button type="button" onclick="modalClose('splitOrder')" class="btn btn-default" data-toggle="modal">Close</button-->
                    <button type="button" class="btn btn-primary" onclick="save_split_order('Save')">Save</button>
                    <button type="button" class="btn btn-primary" onclick="save_split_order('Save & New')">Save &
                        New</button>
                    <button type="button" class="btn btn-primary" onclick="save_split_order('Save & Print')">Save &
                        Print</button>
                    <button type="button" class="btn btn-primary"
                        onclick="save_split_order('Checkout')">Checkout</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- Modal -->
    <div id="myModalAwardPoint" class="modal fade" role="dialog" style="z-index:9999">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" onclick="return verification_box();" class="close"
                        data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Mobile Verification</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label class="col-md-4 col-sm-4 col-xs-4" for="MobileNumber">Mobile Number</label>
                        <div class="col-md-8 col-sm-8 col-xs-8">
                            <div class="form-group">
                                <input name="MobileNumber" readonly type="text" id="MobileNumber" class="form-control"
                                    placeholder="Mobile Number" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-md-4 col-sm-4 col-xs-4" for="OTP">OTP</label>
                        <div class="col-md-8 col-sm-8 col-xs-8">
                            <div class="form-group">
                                <input name="OTP" type="text" id="OTP" class="form-control" placeholder="OTP" />
                                <a href="javascript:void(0);" onclick="return sendSMS();"
                                    style="font-size:12px; position: absolute; top: 10px; right: 22px;">Send OTP</a>
                                <span style="color:red;" class="clear_error" id="ErrOTP"></span>
                                <span style="color:green;" class="clear_error" id="SuccessOTP"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="return verification_box();" class="btn btn-default"
                        data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
    <div class="modal" id="recentPOsDetailModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel"
        aria-hidden="true"></div>
    <div class="modal fade" id="alert_qty_modal" tabindex="-1" role="dialog" aria-labelledby="dsModalLabel"
        aria-hidden="true" style="top:30px; ">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-hidden="true" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-center">Products Alert Quantity</h4>
                </div>
                <div class="modal-body" style="height:400px; overflow-y:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" style="text-align: left;">No.</th>
                                <th scope="col" style="text-align: left;">Name</th>
                                <th scope="col" style="text-align: left;">Variants(Name, Qty)</th>
                                <th scope="col" style="text-align: left;">Code</th>
                                <th scope="col" style="text-align: left;">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
$i = 1;
foreach ($alertProd_Count['result'] as $alertprod) {
    ?>
                            <tr>
                                <td align="left"><?= $i++; ?></td>
                                <td align="left"><?= $alertprod['name']; ?></td>
                                <td align="left"><?php
                                            if ((!empty($alertprod['option']))) {
                                                foreach ($alertprod['option'] as $alertProdOption) {
                                                    echo $alertProdOption['name'] . ' , ';
                                                    echo floor($alertProdOption['quantity']) . '<br>';
                                                }
                                            }
                                            ?></td>
                                <td align="left"><?= $alertprod['code']; ?></td>
                                <td align="left"><?= $alertprod['quantity']; ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
    <input type="hidden" class="form-control" placeholder="OTP" name="OrgOTP" id="OrgOTP">
    <!--input type='button' onClick="split_order()" value="Split Order" >
        
        <!--script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.js"></script-->
    <script type="text/javascript" src="bower_components/multiselect/dist/js/multiselect.js"></script>
    <!-- javascript -->
    <!--<script src="<?= $assets ?>owl-slider/vendors/jquery.min.js"></script> -->
    <script src="<?= $assets ?>owl-slider/owlcarousel/owl.carousel.js"></script>
    <script type="text/javascript" src="<?= $assets ?>pos/js/dragscroll.js"></script>
    <script>
    <?php $login_controller1 = explode('/', $login_controller); ?>
    var login_controller = '<?= $login_controller1[3]; ?>';
    $(window).on('load', function() {
        if (login_controller == 'login' && login_controller != '') {
            <?php if (!($_SESSION['alert_modal'])) { ?>
            $('#alert_qty_modal').modal('show');
            <?php
    $_SESSION['alert_modal'] = 1;
}
?>
        }
    });
    $(document).ready(function() {
        $('#search_customer').focus();
        var owl = $(".owl-carousel");
        $('.owl-carousel').owlCarousel({
            loop: true,
            margin: 2,
            responsiveClass: true,
            nav: true,
            responsive: {
                0: {
                    items: 10,
                    nav: true,
                    width: 47
                },
                600: {
                    items: 10,
                    nav: false,
                    width: 47
                },
                1000: {
                    items: 10,
                    nav: true,
                    loop: false,
                    margin: 2,
                    width: 47
                }
            }
        })

        $('.customNextBtn').click(function() {
            owl.trigger('next.owl.carousel', [300]);
        })
        $('.customPrevBtn').click(function() {
            owl.trigger('prev.owl.carousel', [300]);
        })

        // var owl1 = $(".owl-carousel.second");
        $('.owl-carousel.second').owlCarousel({
            loop: true,
            margin: 2,
            responsiveClass: true,
            responsive: {
                0: {
                    items: 10,
                    nav: true,
                    width: 47
                },
                600: {
                    items: 10,
                    nav: false,
                    width: 47
                },
                1000: {
                    items: 10,
                    nav: true,
                    loop: false,
                    margin: 2,
                    width: 47
                }
            }
        })
    })
    </script>



    <script>
    /* $('#add_item').change(function(){
                     var sproduct = $('#add_item').val();
                     
                     $.ajax({
                     type: 'get',
                     url: '<?= site_url('sales/suggestions'); ?>',
                     dataType: "json",
                     data: {
                     term: sproduct,
                     warehouse_id: $("#poswarehouse").val(),
                     customer_id: $("#poscustomer").val()
                     },
                     beforeSend: function () {
                     return true;
                     },
                     success: function (data) {
                     var getUrl = location.pathname.split('/');
                     var imgPath = location.origin+'/'+getUrl[1]+'/assets/uploads/thumbs/';
                     var i=0, pass_html = '</div>';
                     for(i=0;i<data.length;i++){
                     var count = data[i].item_id;
                     if(count < 10){
                     count = "0" + (count / 100) * 100;
                     }
                     
                     var cate = data[i].category;
                     if(cate < 10){
                     cate = "0" + (cate / 100) * 100;
                     }
                     pass_html += "<button id=\"product-"+cate+count+"\" type=\"button\" value='"+data[i].row.code+"' title=\""+data[i].label+ "\" class=\"btn-prni btn-info product pos-tip\" data-container=\"body\"><img src=\""+imgPath+data[i].image +"  \" alt=\"" +data[i].label+ "\" style='width:60px;height:60px;' class='img-rounded' /><span>"  +data[i].label.substr(0,20)+ "</span></button>";
                     
                     
                     }
                     pass_html +='</div>';
                     $('#item-list').html(pass_html);
                     }
                     });
                     });
                     */
    </script>

    <?php if ($Settings->theme != 'default') { ?>
    <script>
    var defuatl_customer =
        '<?= ($_SESSION['quick_customerid'] ) ? $_SESSION['quick_customerid'] : $pos_settings->default_customer ?>';
    getCustomer('id', defuatl_customer);
    $(document).ready(function() {
        // document.getElementById('ajaxproducts').style.height="80%";
        // document.getElementById('item-list').style.height="100%";
        document.getElementById('ajaxproducts').style.height = "68%";
        document.getElementById('item-list').style.height = "90%";
    });
    // Customer Search
    $('#search_customer').autocomplete({
        source: function(request, response) {

            if (request.term.length > 1) {
                document.getElementById('customer_name').value = '';
                document.getElementById('poscustomer').value = '';
                document.getElementById('custname').value = '';
                localStorage.setItem('poscustomer', '');
                $.ajax({
                    type: 'ajax',
                    dataType: 'json',
                    data: {
                        term: request.term,
                        'return': 'phone'
                    },
                    url: '<?= site_url('pos/getCustomerAuto'); ?>',
                    method: 'get',
                    async: false,
                    beforeSend: function() {
                        // $('#searchicon').html('<img id="loaderimg"  src="<?= base_url() ?>/themes/default/assets/img/loading.gif"/>');
                        //  $('#scustomer').hide();
                        return true;
                    },
                    success: function(data) {
                        $('.ui-autocomplete-loading').removeClass("ui-autocomplete-loading");
                        response(data);
                        //$('#scustomer').hide();
                        // setTimeout(function(){ $('#searchicon').html('<span class="glyphicon glyphicon-search"></span>');  }, 1000);
                    }

                });
            }
        },
        response: function(event, ui) {

            if (ui.content == null) {
                showerrorMsg('Number not registered');
                $('#AddNewCustomer').show();
                $('#add-customer').show();
                $('#view-customer').hide();
                $('#scustomer').hide();
            }
        },
        select: function(event, ui) {
            $('#AddNewCustomer').hide();
            $('#view-customer').show();
            $('#scustomer').hide();
            getCustomer('phone', ui.item['value']);
        }

    });
    // End Customer Search
    // Get Customer name
    $("#customer_name").click(function() {
        $('#customer_name').val('');
    }).blur(function() {
        if ($('#customer_name').val() == '') {
            getCustomer('id', localStorage.getItem('poscustomer'));
        }
    });
    $('#customer_name').autocomplete({

        source: function(request, response) {
            $.ajax({
                type: 'ajax',
                dataType: 'json',
                data: {
                    term: request.term,
                    'return': 'name'
                },
                url: '<?= site_url('pos/getCustomerName'); ?>',
                method: 'get',
                async: false,
                beforeSend: function() {
                    $('#loaderimg').show();
                    $('#add-customer').hide();
                    $('#scustomer').hide();
                },
                success: function(data) {
                    response(data);
                    setTimeout(function() {
                        $('#loaderimg').hide();
                        $('#add-customer').show();
                        $('#scustomer').hide();
                    }, 1000);
                }

            });
        },
        response: function(event, ui) {

            if (ui.content == null) {
                showerrorMsg('Name not registered');
                $('#AddNewCustomer').show();
                $('#view-customer').hide();
                $('#scustomer').hide();
            }

        },
        select: function(event, ui) {
            var get_data = ui.item['value'];
            var getarray = get_data.split(" ");
            var phone_number = getarray[getarray.length - 1];
            var sillyString = phone_number.slice(1, -1);
            $('#AddNewCustomer').hide();
            $('#view-customer').show();
            $('#scustomer').hide();
            $('#MobileNumber').val(sillyString);
            getCustomer('phone', sillyString);
        }
    });
    // End Get Customer Name

    // get customer
    function getCustomer(fieldkey, mobile_no) {
        var pass_data = fieldkey + '=' + mobile_no;
        $.ajax({
            type: 'get',
            dataType: 'json',
            data: pass_data,
            url: site.base_url + 'pos/get_dependancy',
            async: false,
            success: function(data) {
                if (data != null) {

                    $('#MobileNumber').val(data.phone);
                    $('#customer_group').val(data.customer_group_id);
                    //if(data.name =='Walk-in Customer name'){
                    //  document.getElementById('customer_name').value= data.name; 
                    //}else{
                    document.getElementById('customer_name').value = data.name + '(' + data.phone + ')';
                    //}
                    document.getElementById('poscustomer').value = data.id;
                    document.getElementById('custname').value = data.id;
                    localStorage.setItem('poscustomer', data.id);
                    //                             console.log(localStorage);

                    $("#customer_deposit_link").attr("href", "<?= base_url('customers/add_deposit/') ?>" +
                        data.id);
                } else {

                    bootbox.alert('Number not registered, to <a href="customers/add/quick?mobile_no=' +
                        mobile_no +
                        '" id="add-customer"  class="external" data-toggle="ajax"  tabindex="-1"> add new customer click here</a>'
                        );
                }
            }
        });
    }

    //         It should be number not registered , to add new customer click here
    function searchCustomer() {
        var mobile_no = $('#search_customer').val();
        if (mobile_no == '') {
            bootbox.alert('Please Enter Mobile Number');
            $('#search_customer').focus();
        } else {
            getCustomer('phone', mobile_no);
        }

    }



    <?php if ($sid) { ?>
    var supcusto_id = '<?= $customer->id; ?>';
    getCustomer('id', supcusto_id);
    <?php } ?>


    $('#AddNewCustomer').click(function() {
        var mobile = validation('mobile', $('#search_customer').val(), 'search_customer');
        if (mobile == true) {
            var name = validation('name', $('#customer_name').val(), 'customer_name');
            if (name == true && mobile == true) {
                var getmobile = $('#search_customer').val();
                var getname = $('#customer_name').val();
                $.ajax({
                    type: 'ajax',
                    dataType: 'json',
                    method: 'get',
                    url: '<?= site_url('pos/addcustomer') ?>',
                    data: {
                        'mobile_no': getmobile,
                        'name': getname
                    },
                    async: false,
                    success: function(result) {
                        if (result.msg == 'Success') {
                            document.getElementById('poscustomer').value = result.id;
                            document.getElementById('custname').value = result.id;
                            localStorage.setItem('poscustomer', result.id);
                            $('#scustomer').show();
                            $('#view-customer').show();
                            $('#AddNewCustomer').hide();
                            $('#add-customer').hide();
                            $('#error_message').html('');
                        } else {
                            console.log(result);
                        }
                        //                           
                    },
                    error: function() {
                        console.log('error');
                    }
                });
            }
        }
    });

    function validation() {
        var getkey = arguments[0]; // Get Key Typr
        var getvalue = arguments[1]; // get Value Type
        var getID = arguments[2];
        switch (getkey) {
            case 'mobile':
                var filter =
                    /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
                if (!getvalue == ' ') {
                    if (filter.test(getvalue)) {
                        if (getvalue.length == 10) {
                            return true;
                        } else {
                            showerrorMsg('Please put 10  digit mobile number');
                            boxFocus(getID)
                            return false;
                        }
                    } else {
                        showerrorMsg('Not a valid number');
                        boxFocus(getID)
                        return false;
                    }
                } else {
                    showerrorMsg('Please enter mobile number');
                    boxFocus(getID)
                    return false;
                }
                break;
            case 'name':
                var nameRegex = /^[a-zA-Z \-]+$/;
                if (!getvalue == ' ') {
                    if (nameRegex.test(getvalue)) {
                        if (getvalue.length >= 1) {
                            return true;
                        } else {
                            showerrorMsg('Please put Min 4 character');
                            boxFocus(getID)
                            return false;
                        }

                    } else {
                        showerrorMsg('Not a valid name');
                        boxFocus(getID)
                        return false;
                    }
                } else {
                    showerrorMsg('Please enter customer name');
                    boxFocus(getID)
                    return false;
                }
                break;
            default:

                break;
        }
    }

    function boxFocus() {
        document.getElementById(arguments[0]).focus();
    }

    function showerrorMsg(msg) {
        document.getElementById('errormsg').style.display = 'block';
        $('#error_msg').html(msg);
        setTimeout(function() {
            $('#errormsg').hide();
            $('#error_msg').html('');
        }, 3000)

    }
    $('#msgclose').click(function() {
        document.getElementById('errormsg').style.display = 'none';
        $('#error_msg').html('');
    });
    // End Get Customer
    </script>
    <?php } ?>
    <!--Theme Changes-->
    <script>
    function change_theme(theme) {
        $.ajax({
            type: "ajax",
            dataType: 'json',
            method: 'get',
            url: "pos/change_theme/" + theme,
            success: function(result) {
                if (result == "TRUE") {
                    location.reload();
                }
                console.log(result);
            },
            error: function() {
                console.log('error');
            }
        });
    }

    $(".resturent_table_group").on('click', function() {

        //alert($(this).find('input').attr("table_id"));
        $("[name='table_id']").val($(this).find('input').attr("table_id"));
        var table_id = $(this).find('input').attr("table_id");
        localStorage.setItem('table_id', table_id);
        $('#kot_restaurant_tables option[value=' + table_id + ']').attr('selected', 'selected');
        var tablevalue = $(this).find('input').val();
        $('#reference_note').val(tablevalue);
        localStorage.setItem('table_name', tablevalue);
        $('#active_table').html(tablevalue);
        $('#suspend_sale1').show();
        $('#suspend').hide();
    });
    jQuery(document).ready(function() {
        /* $.get("/pos/opened_bills", function(data, status){
         var t = data.split('class="btn btn-info sus_sale" id="');
         $.each(t, function(i, item) {
         if(i != 0){
         var d = t[i].split('"><p>');
         //alert(d[0]);
         var e = d[1].split('</p><strong>');
         //alert(e[0].replace(' ','_'));
         
         $('#'+e[0].replace(' ','_')).click(function(){
         window.location.href='/pos/index/'+d[0];
         })
         
         //newObj[e[0]] = d[0];
         //var tt = {e[0]:d[0]};
         //alert(tt);
         //newObj.push(tt);
         }
         
         });
         });*/
    });
    </script>
    <!--End Theme changes-->
    <?php if ($report_send == 'send_reports') { ?>
    <script>
    $.ajax({
        type: 'ajax',
        dataType: 'json',
        method: 'get',
        url: '<?= base_url('reports_new/sendreport') ?>',
        async: false,
        success: function(result) {
            console.log(result);
        }
    })
    </script>
    <?php } ?>


    <!--Add Quick Customer Form Direct Mobile No and Name Show-->
    <script>
    $('#add-customer').click(function() {
        //localStorage.removeItem("quickCusName");
        localStorage.removeItem("quickCusMobile");
        var mobileno = $('#search_customer').val();
        //  var name = $('#customer_name').val();
        if ($.isNumeric(mobileno) && mobileno.length == 10) {
            localStorage.setItem('quickCusMobile', mobileno);
        }

        /*if(name!=''){          
         localStorage.setItem('quickCusName', name);
         }*/

    })
    </script>

    <script>
    var alertOn = false;
    <?php if ($Settings->pos_type == 'restaurant') { ?>

    function changeText1() {
        $.ajax({
            type: "get",
            async: false,
            url: '<?= base_url("urban_piper/new_orders") ?>',
            dataType: "json",
            success: function(data) {

                if (data.num) {
                    //                        $('#urbanpipersorder').html(data.num);

                    if (data.new_order > 0) {
                        playSound(1);
                        $('#urbanpiper-order-alert').html(
                            '<div class="alert alert_notify alert-success"><button type="button" class="close fa-2x" onclick="upnotify_close()" >&times;</button> <a href="<?= base_url('urban_piper') ?>" onclick="upnotify_close()" target="_bank" >' +
                            data.new_order + ' new orders received from Urbanpiper.</a></div>');
                        if (alertOn == false) {
                            $('.alert_notify').show();
                            alertOn = true;
                        }
                    }

                    if (alertOn == true) {
                        setTimeout(function() {
                            $('.alert_notify').hide();
                            alertOn = false;
                        }, 19000);
                    }
                }
            }
        });
    }

    function upnotify_close() {
        $.ajax({
            type: "get",
            async: false,
            url: '<?= base_url("urban_piper/new_orders_alert/2") ?>',
            dataType: "json",
            success: function(data) {
                $('.alert_notify').hide();
            }
        });
    }

    setInterval(changeText1, 5000); //30000
    changeText1();

    function upstocknotify_close() {
        $('.urbanpiper-stock_notify').hide();
    }

    <?php }//end if   ?>

    function playSound(Play) {
        var x = document.getElementById("myAudio");
        if (Play == 1) {
            x.play();
            setTimeout(function() {
                x.pause();
            }, 4000);
        }
    }
    </script>
    <script>
    <?php
if ($Settings->send_sales_excel) {
    if ($_SESSION['Send_Excel'] == 1) {
        ?>
    $.ajax({
        type: 'ajax',
        method: 'get',
        url: '<?= base_url() . "sales/export_excel" . "/" . $_SESSION['sale_id']; ?>',
        //async:false,
        success: function(res) {
            console.log(res);
            console.log('success');
        },
        error: function() {
            console.log('errror');
        }
    });
    <?php
    }
}
?>
    </script>

    <!--input type='button' onClick="split_order()" value="Split Order" -->
    <input type="hidden" id="is_suspend_id" name="is_suspend_id" value="<?php echo (($sid > 0) ? $sid : 0); ?>">
    <input type="hidden" id="is_reference_note" name="is_reference_note" value="<?php echo $reference_note; ?>">
    <style>
    .payment_box {
        opacity: 0.5 !important;
    }
    </style>



    <?php
if ($_SESSION['Print_Deposite_Receipt']['status'] == '1') {
    $deposit_data = $_SESSION['Print_Deposite_Receipt']['last_deposit'];
    $customerData = $_SESSION['Print_Deposite_Receipt']['customer_Details'];
    $openingBalance = $_SESSION['Print_Deposite_Receipt']['openingBalance'];
    unset($_SESSION['Print_Deposite_Receipt']);
    ?>
    <div class="page-break" id="deposit_print_bill" style="display:none">
        <style>
        .page-break {
            width: 480px
        }

        @media print {
            .page-break {
                display: block;
                page-break-before: always;
            }

            .page-break {
                width: 480px
            }
        }

        /*#orderTable_<?php // $key  ?>, th, td { border-collapse:collapse; border-bottom: 1px solid #CCC; }*/
        .no-border {
            border: 0;
        }

        #depositTable>tbody>tr>td,
        #depositTable>tbody>tr>th {
            border: 1px solid;
            padding: 5px 2px
        }

        .bold {
            font-weight: bold;
        }
        </style>
        <div class="text-center" style="text-align: center;">
            <strong
                style="text-transform:uppercase; margin-bottom: 0px;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></strong><br />
            <span> Date : <?= $deposit_data['date'] ?></span><br />
            <span> Name: <?= $customerData->name?></span><br />
            <span> Tel No.: <?= $customerData->phone ?></span><br />
            <span> Email: <?= $customerData->email ?></span><br />
            <span> Card No: <?= $customerData->cf1 ?></span><br />
            <span> Room No: <?= $customerData->cf2 ?></span><br />
        </div>

        <table id="depositTable" style="width: 100%;border-collapse: collapse;text-align: left;">
            <tbody>
                <tr>
                    <td>Opening Balance</td>
                    <td><?= $this->sma->formatMoney(($customerData->deposit_amount?$customerData->deposit_amount:"0")) ?>
                    </td>

                </tr>
                <tr>
                    <td> Recharge Amount </td>
                    <td> <?= $this->sma->formatMoney($deposit_data['amount'] - $deposit_data['super_cash']) ?> </td>
                </tr>
                <tr>
                    <td> Super Cash Recived</td>
                    <td> <?= $this->sma->formatMoney($deposit_data['super_cash']) ?> </td>
                </tr>
                <tr>
                    <td> Total Deposit Amount</td>
                    <td> <?= $this->sma->formatMoney($deposit_data['amount']) ?> </td>
                </tr>
                <tr>
                    <td> Closing Balance </td>
                    <td> <?= $this->sma->formatMoney(($customerData->deposit_amount?$customerData->deposit_amount + $deposit_data['amount']: $customerData->deposit_amount + $deposit_data['amount']) ) ?>
                    </td>
                </tr>
                <tr>
                    <td> Paid By </td>
                    <td> <?= $deposit_data['paid_by'] ?> </td>
                </tr>

            </tbody>
        </table>
        <div style="padding:2px;">
            <table>
                <tr>
                    <td>Remark :</td>
                    <td> <?= $deposit_data['note'] ?></td>
                </tr>
            </table>

        </div>
    </div>
    <script>
    openWin('deposit_print_bill')

    // Second time open receipt    

    // setTimeout(function() {
    // openWin('deposit_print_bill');
    // }, 100);
    function openWin(div) {
        var winPrint = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
        winPrint.document.write($('#' + div).html());
        winPrint.document.close();
        winPrint.focus();
        winPrint.print();
        setTimeout(function() {
            winPrint.close();
        }, 100)
    }
    </script>

    <?php } ?>

    <script>
    $('#DescriptionBtn').click(function() {
        $('#discription_input_block').show();
        $('#discription_btn_block').hide();
    });
    $('#repeate_sales_discount').click(function() {
        if ($(this).prop("checked") == true) {
            applyRepeateDiscount(true);
        } else {
            applyRepeateDiscount(false);
        }

    });

    function callTable(passid) {
        window.location.href = '<?= base_url('pos/index') ?>/' + passid;
    }
    </script>

    <!--  Barcode Scan using system camera -->
    <!-- Modal -->
    <div class="modal fade" id="scan_barcode_camera" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Scan Barcode</h5>

                </div>
                <div class="modal-body" style="height: 72%;">
                    <main class="wrapper" style="padding-top:2em">

                        <section class="container" id="demo-content">




                            <div>
                                <video id="video" width="100%" height="90%" style="border: 1px solid gray"></video>
                            </div>

                            <div id="sourceSelectPanel" style="display:none">
                                <label for="sourceSelect" style="display:none">Change video source:</label>
                                <select id="sourceSelect" style="max-width:400px; display:none">
                                </select>
                            </div>

                            <!--              <label>Result:</label>
                                              <pre><code id="result"></code></pre>-->
                        </section>

                    </main>

                    <!--<div id="barcodeScanner">
                            <span id='loading-status' style='font-size:x-large'>Loading Library...</span>
                        </div>
    
                        <div class="cameralist" style="display:none">
                            <label for="videoSource">Video source: </label>
                            <select id="videoSource"></select>
                        </div>
    
                            <div id="videoview">
                                <div class="dce-video-container" id="videoContainer"></div>
                                <canvas id="overlay"></canvas>
                            </div>  -->
                </div>
                <div class="modal-footer">
                    <button type="button" id="closecamera" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <!--<button type="button" class="btn btn-primary">Save changes</button>-->
                </div>
            </div>
        </div>
    </div>


    <script src="<?= $assets ?>js/barcodezxing/index.js"></script>
    <script src="<?= $assets ?>js/barcodezxing/script.js"></script>

    <script>
    window.addEventListener('load', function() {
        let selectedDeviceId;
        var hints = new Map();
        hints.set(ZXing.DecodeHintType.ASSUME_GS1, true)
        hints.set(ZXing.DecodeHintType.TRY_HARDER, true)
        const codeReader = new ZXing.BrowserMultiFormatReader(hints)
        console.log('ZXing code reader initialized')
        codeReader.getVideoInputDevices()
            .then((videoInputDevices) => {
                const sourceSelect = document.getElementById('sourceSelect')
                console.log(videoInputDevices);
                selectedDeviceId = videoInputDevices[0].deviceId
                if (videoInputDevices.length >= 1) {
                    videoInputDevices.forEach((element) => {
                        const sourceOption = document.createElement('option')
                        sourceOption.text = element.label
                        sourceOption.value = element.deviceId
                        sourceSelect.appendChild(sourceOption)
                    })

                    sourceSelect.onchange = () => {
                        selectedDeviceId = sourceSelect.value;
                    };
                    const sourceSelectPanel = document.getElementById('sourceSelectPanel')
                    sourceSelectPanel.style.display = 'block'
                }

                //document.getElementById('startButton').addEventListener('click', () => {
                document.getElementById('scancamerabtn').addEventListener('click', () => {
                    codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
                        if (result) {
                            console.log(result.getText())

                            $('#add_item').val(result.getText());
                            $('#add_item').autocomplete('search', $('#add_item').val());
                            $('#closecamera').trigger('click');
                            $('#scan_barcode_camera').modal('hide');
                            setTimeout(function() {

                                $('#scancamerabtn').trigger('click');
                            }, 1000);
                            //                            document.getElementById('result').textContent = result.text
                        }
                        if (err && !(err instanceof ZXing.NotFoundException)) {
                            console.error(err)
                            document.getElementById('result').textContent = err
                        }
                    })
                    console.log(`Started continous decode from camera with id ${selectedDeviceId}`)
                })


                //                    document.getElementById('resetButton').addEventListener('click', () => {
                document.getElementById('closecamera').addEventListener('click', () => {
                    codeReader.reset()
                    document.getElementById('result').textContent = '';
                    console.log('Reset.')
                })



            })
            .catch((err) => {
                console.error(err)
            })
    })
    </script>

    <!--<script src="https://cdn.jsdelivr.net/npm/dynamsoft-javascript-barcode@9.0.0/dist/dbr.js"></script> -->

    <!-- <script type="text/javascript" src="<?= $assets ?>js/barcode/overlay.js"></script> -->
    <script>
    /* // Make sure to set the key before you call any other APIs under Dynamsoft.DBR
                     // You can register for a free 30-day trial here: https://www.dynamsoft.com/customer/license/trialLicense?product=dbr&deploymenttype=browser.
                     Dynamsoft.DBR.BarcodeReader.license = "DLS2eyJoYW5kc2hha2VDb2RlIjoiMjAwMDAxLTE2NDk4Mjk3OTI2MzUiLCJvcmdhbml6YXRpb25JRCI6IjIwMDAwMSIsInNlc3Npb25QYXNzd29yZCI6IndTcGR6Vm05WDJrcEQ5YUoifQ==";
                     var videoSelect = document.querySelector('#videoSource');
                     var cameraInfo = {};
                     var scanner = null;
                     initOverlay(document.getElementById('overlay'));
                     async function openCamera() {
                     clearOverlay();
                     let deviceId = videoSelect.value;
                     if (scanner) {
                     await scanner.setCurrentCamera(cameraInfo[deviceId]);
                     }
                     }
                      
                     async function closeCamera() {
                     clearOverlay();
                     //            let deviceId = videoSelect.value;
                     //            if (scanner) {
                     //                await scanner.stop();
                     //            }
                     }
                     videoSelect.onchange = openCamera;
                      
                     $('#scancamerabtn').click(function(){
                     Dynamsoft.DBR.BarcodeScanner.loadWasm();
                     initBarcodeScanner();
                     });
                      
                      
                     $('#closecamera').click(function(){
                     closeCamera();
                     });
                      
                      
                      
                     //        window.onload = async function () {
                     //            try {
                     //                await Dynamsoft.DBR.BarcodeScanner.loadWasm();
                     ////                await initBarcodeScanner();
                     //            } catch (ex) {
                     //                alert(ex.message);
                     //                throw ex;
                     //            }
                     //        };
                      
                     function updateResolution() {
                     if (scanner) {
                     let resolution = scanner.getResolution();
                     updateOverlay(resolution[0], resolution[1]);
                     }
                     }
                      
                     function listCameras(deviceInfos) {
                     for (var i = 0; i < deviceInfos.length; ++i) {
                     var deviceInfo = deviceInfos[i];
                     var option = document.createElement('option');
                     option.value = deviceInfo.deviceId;
                     option.text = deviceInfo.label;
                     cameraInfo[deviceInfo.deviceId] = deviceInfo;
                     videoSelect.appendChild(option);
                     }
                     }
                      
                     async function initBarcodeScanner() {
                     scanner = await Dynamsoft.DBR.BarcodeScanner.createInstance();
                     await scanner.updateRuntimeSettings("speed");
                     await scanner.setUIElement(document.getElementById('videoContainer'));
                      
                     let cameras = await scanner.getAllCameras();
                     listCameras(cameras);
                     await openCamera();
                     scanner.onFrameRead = results => {
                     clearOverlay();
                      
                     let txts = [];
                     try {
                     let localization;
                     if (results.length > 0) {
                     for (var i = 0; i < results.length; ++i) {
                     txts.push(results[i].barcodeText);
                     localization = results[i].localizationResult;
                     //                            drawOverlay(localization, results[i].barcodeText);
                     }
                     getBarcodeValue(txts.join(', '));
                     //                        alert(txts.join(', '));
                     //                         document.getElementById('result').innerHTML = txts.join(', ');
                     }
                     else {
                     //                        document.getElementById('result').innerHTML = "No barcode found";
                     }
                      
                     } catch (e) {
                     alert(e);
                     }
                     };
                     scanner.onUnduplicatedRead = (txt, result) => { };
                     document.getElementById('loading-status').hidden = true;
                     scanner.onPlayed = function() {
                     updateResolution();
                     }
                     await scanner.show();
                      
                     }
                      
                      
                     function getBarcodeValue(pass){
                     console.log("Barcode : "+ pass);
                     $('#add_item').val(pass);
                     $('#add_item').autocomplete('search', $('#add_item').val());
                     $('#closecamera').trigger('click');
                     $('#scan_barcode_camera').modal('hide');
                     setTimeout(function(){
                      
                     $('#scancamerabtn').trigger('click');
                     }, 1000);
                     }
                      
                     $('#closecamera').click(function(){
                     $('#scan_barcode_camera').modal('hide');
                     })*/


    if (localStorage.getItem('table_id')) {
        $('#suspend_sale1').show();
        $('#suspend').hide();
    } else {
        $('#suspend_sale1').hide();
        $('#suspend').show();
    }

    function update_seats(tableId) {
        bootbox.prompt({
            title: "Enter Table Guests",
            inputType: 'number',
            callback: function(result) {
                if (result) {
                    $.ajax({
                        type: 'ajax',
                        dataType: 'json',
                        method: 'get',
                        data: {
                            'table_id': tableId,
                            'seats': result
                        },
                        url: '<?= base_url("pos/table_seats") ?>',
                        async: false,
                        success: function(response) {
                            if (response.status) {
                                $('#table-' + tableId).html("Guests : " + response.seats);
                            }
                            console.log(response);
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
                console.log(result);
            }
        });
    }
    </script>

    <!-- End Barcode Scan Using Camera -->
    <script>
    function add_subtable(table_id) {
        $('#table_block-' + table_id).show();
    }

    function delete_subtable(table_id) {
        $('#table_block-' + table_id).hide();
    }



    shortcut.add("Ctrl+B", function() {
        document.getElementById("print_bill").click();
    }, {
        'type': 'keydown',
        'propagate': true,
        'target': document
    });
    shortcut.add("F2", function() {
        document.getElementById("suspend_sale").click();
    }, {
        'type': 'keydown',
        'propagate': true,
        'target': document
    });
    shortcut.add("Ctrl+C", function() {
        call_checkout();
    }, {
        'type': 'keydown',
        'propagate': true,
        'target': document
    });
    /**
     * Print KOT Using Division wise
     */
    function printKOT() {

        var unique_array = division_array.filter((item, i, ar) => ar.indexOf(item) === i);
        $.each(unique_array, function(index, value) {
            var printstatus = false;
            let kotprint = '<div style="text-align:center;"><strong> Table No.' + localStorage.getItem(
                'table_name') + ' </strong><br/>';
            kotprint += '<span>KOT No. : ' + token_no + '</span><br/>';
            kotprint += '<span>Waiter : ' + ($('#sales_person').val() == '0' ? ' ' : $('#sales_person').find(
                'option:selected').text()) + '</span><br/>';
            kotprint += '<span>No.of ' + $('#table-' + localStorage.getItem('table_id')).text() +
            '</span><br/>';
            kotprint += '<span> ' + hrld() + ' </span></div>';
            kotprint +=
                '<table style="border-collapse: collapse;" width="100%" border="1"><tr><td> Item Code</td><td>Qty</td>'
            <?php if ($sid) { ?>

            $.each(sortedItems, function() {
                var item = this;
                if (value == item.divisionid) {
                    let getdata = checkkotPrint(item.row.code, item.row.qty, item.id);
                    console.log(getdata.status);
                    if (getdata.status == 'false') {
                        printstatus = true;
                        kotprint += '<tr>';
                        kotprint += '<td>' + item.row.name + '(' + item.row.code + ')' + '</td>';
                        kotprint += '<td>' + getdata.qty + '</td>';
                        kotprint += '</tr>';
                    }
                }

            });
            <?php } else { ?>

            printstatus = true;
            $.each(sortedItems, function() {
                var item = this;
                if (value == item.divisionid) {
                    kotprint += '<tr>';
                    kotprint += '<td>' + item.row.name + '(' + item.row.code + ')' + '</td>';
                    kotprint += '<td>' + item.row.qty + '</td>';
                    kotprint += '</tr>';
                }

            });
            <?php } ?>
            kotprint += '</table>';
            kotprint += '<span>' + $('#note').val() + '</span>';
            if (printstatus) {
                openWin(kotprint);
                token_no++;
            }
        });
    }

    /**
     * Print
     **/

    /* function printKOT(){
          
             var unique_array = division_array.filter((item, i, ar) => ar.indexOf(item) === i);
          
          
          
             $.each(unique_array , function(index, value){
          
             let kotprint = '<div style="text-align:center;"><strong> Table No.'+localStorage.getItem('table_name') +' </strong><br/>';
             kotprint+= '<span>KOT No. : '+token_no+'</span><br/>';
             kotprint+= '<span>Waiter : '+($('#sales_person').val()== '0'? ' ' :$('#sales_person').find('option:selected').text())+'</span><br/>';
             kotprint+='<span>No.of '+$('#table-'+localStorage.getItem('table_id')).text()+'</span><br/>';
             kotprint+='<span> '+hrld()+' </span></div>';
             kotprint+='<table style="border-collapse: collapse;" width="100%" border="1"><tr><td> Item Code</td><td>Qty</td>'
             $.each(sortedItems, function () {
             var item = this;
             if(value==item.divisionid){
             kotprint+='<tr>';
             kotprint+='<td>'+ item.row.name +'('+item.row.code+')' +'</td>';
             kotprint+='<td>'+ item.row.qty +'</td>';
             kotprint+='</tr>';
             }
             }); 
             kotprint+='</table>'; 
             kotprint+='<span>'+$('#note').val()+'</span>';
             openWin(kotprint);
             token_no++;
             });
          
             } */

    function openWin(div) {
        var winPrint = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
        //            winPrint.document.write('<link rel="stylesheet" href="<?= $assets ?>styles/theme.css" type="text/css"/>'); 
        winPrint.document.write(div);
        winPrint.document.close();
        winPrint.focus();
        winPrint.print();
        setTimeout(function() {
            winPrint.close();
        }, 100)
    }

    function bill_print(table_id) {
        $('#billprint-' + table_id).css("background-color", "#ff8400eb");
        $('#billprint-' + table_id).css("border", "1px solid #ff8400eb");
        if (table_id == localStorage.getItem('table_id')) {
            $('#print_bill').trigger('click');
        } else {
            $.ajax({
                type: 'ajax',
                dataType: 'html',
                method: 'get',
                url: '<?= base_url("pos/billPrint") ?>/' + table_id,
                async: false,
                success: function(result) {
                    openWin(result);
                },
                error: function(erorr) {
                    console.log('erorr');
                }
            });
        }

        $.ajax({
            type: 'ajax',
            dataType: 'json',
            method: 'get',
            url: "<?= base_url('pos/tableBillPrint') ?>/" + table_id,
            data: {
                'billPrint': '1'
            },
            async: false,
            success: function(result) {
                console.log(result);
            },
            error: function(error) {
                console.log('erorr');
            }
        });
    }

    function checkkotPrint(code, qty2, rowid) {

        var OldItems = JSON.parse(localStorage.getItem('olditems'));
        var oldItems_array = [];
        $.each(OldItems, function() {
            oldItems_array.push({
                "product_code": this.row.code,
                "qty": this.row.qty,
                'rowID': this.id
            });
        });
        const result = oldItems_array.find(({
            product_code
        }) => product_code === code);
        if (result) {
            if (result.qty == qty2) {

                const idcheck = oldItems_array.find(({
                    rowID
                }) => rowID === rowid);
                if (idcheck) {
                    return response = {
                        'status': 'true',
                        'qty': qty2
                    };
                } else {
                    let nqty = qty2;
                    return response = {
                        'status': 'false',
                        'qty': nqty
                    };
                }
            } else {
                let nqty = qty2 - result.qty;
                return response = {
                    'status': 'false',
                    'qty': nqty
                };
            }
        } else {
            let nqty = qty2;
            return response = {
                'status': 'false',
                'qty': nqty
            };
        }
    }

    // Delete KOT Print
    function deletekotItems() {
        var tem_devision = [];
        $.each(delete_items, function() {
            console.log('---');
            var ditems = this;
            tem_devision.push(ditems.divisionid);
        });
        var unique_array = tem_devision.filter((item, i, ar) => ar.indexOf(item) === i);
        $.each(unique_array, function(index, value) {
            var printstatus = false;
            let kotprint = '<div style="text-align:center;"><strong> Table No.' + localStorage.getItem(
                'table_name') + ' </strong><br/>';
            kotprint += '<span>KOT No. : ' + token_no + '</span><br/>';
            kotprint += '<span>Waiter : ' + ($('#sales_person').val() == '0' ? ' ' : $('#sales_person').find(
                'option:selected').text()) + '</span><br/>';
            kotprint += '<span>No.of ' + $('#table-' + localStorage.getItem('table_id')).text() +
            '</span><br/>';
            kotprint += '<span> ' + hrld() + ' </span></div>';
            kotprint +=
                '<table style="border-collapse: collapse;" width="100%" border="1"><tr><td> Item Code</td><td>Qty</td>'

            $.each(delete_items, function() {
                var item = this;
                printstatus = true;
                if (value == item.divisionid) {
                    kotprint += '<tr>';
                    kotprint += '<td>' + item.row.name + '(' + item.row.code + ')' + '</td>';
                    kotprint += '<td>-' + item.row.qty + '</td>';
                    kotprint += '</tr>';
                }

            });
            kotprint += '</table>';
            kotprint += '<span>' + $('#note').val() + '</span>';
            if (printstatus) {
                openWin(kotprint);
                token_no++;
            }
        });
    }

    <?php if (isset($_GET['select_table'])) { ?>
    $('#table_block-<?= $_GET['select_table'] ?>').show();
    $('#select_table-<?= $_GET['select_table'] ?>').trigger('click');
    $("#select_table-<?= $_GET['select_table'] ?>>lable").css("background",
        "linear-gradient(to bottom, rgb(99,212,246) 0%,rgb(29,121,149) 100%)");
    <?php } ?>

    $('#shifttable').click(function() {
        $('#susModal').modal();
        setTimeout(function() {
            $("#reference_note").focus();
        }, 500);
    });

    function update_token() {
        $.ajax({
            type: 'ajax',
            dataType: 'json',
            method: 'get',
            url: '<?= base_url("pos/updateToken") ?>?TokenNo=' + token_no,
            async: false,
            success: function(result) {
                console.log(result);
            },
            error: function() {
                console.log('erorr');
            }
        });
    }



    <?php if ($_GET['checkout'] == '1') { ?>
    setTimeout(function() {
        $('#payment').trigger('click');
    }, 1000);
    <?php } ?>


    function restBill() {
        bootbox.confirm("Are you sure?", function(res) {
            if (res == true) {
                window.location.href = '<?= base_url('restandlogout') ?>';
            }
        });
        return false;
    }
    </script>
</body>

</html>