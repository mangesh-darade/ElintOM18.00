<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1252"> 
        <base href="<?= site_url() ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $page_title ?> - <?= $Settings->site_name ?></title>
        <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
        <link href="<?= $assets ?>styles/theme.css" rel="stylesheet"/>
        <link href="<?= $assets ?>styles/style.css" rel="stylesheet"/>
        <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
    <!--<script type="text/javascript"  src="<?= $assets ?>js/use.fontawesome.2d4bde2e03.js"></script>-->     
        <!--[if lt IE 9]>
        <script src="<?= $assets ?>js/jquery.js"></script>
        <![endif]-->
        <noscript>
        <style type="text/css">
            #loading {
                display: none;
            }
        </style>
        </noscript>
        <?php if ($Settings->user_rtl) { ?>
            <link href="<?= $assets ?>styles/helpers/bootstrap-rtl.min.css" rel="stylesheet"/>
            <link href="<?= $assets ?>styles/style-rtl.css" rel="stylesheet"/>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('.pull-right, .pull-left').addClass('flip');
                }
                );
            </script>
        <?php } ?>
        <script type="text/javascript">
            $(window).load(function () {
                $("#loading").fadeOut();
            });

            function display_WifiPrinterSetting() {
                $('#printers_wifi').css('display', 'block');
            }

            setTimeout(function () {
                $('#errs').fadeOut('slow');
            }, 3000);

            $(document).ready(function() {
                // Robust Sidebar Toggle Logic for both Standard and Custom pages
                function syncLayout(isMinified, restoreActiveMenu) {
                    var $ = window.jQuery;
                    if (!$) return;
                    
                    var $sidebarLeft = $('#sidebar-left');
                    var isStandardSidebarPresent = $sidebarLeft.length && $sidebarLeft.is(':visible');
                    
                    // Toggle body classes
                    if (isMinified) {
                        $('body').addClass('sidebar-minified');
                        $('#content').addClass('sidebar-minified');
                        if (isStandardSidebarPresent) $sidebarLeft.addClass('minified');
                    } else {
                        $('body').removeClass('sidebar-minified');
                        $('#content').removeClass('sidebar-minified');
                        if (isStandardSidebarPresent) $sidebarLeft.removeClass('minified');
                        if (restoreActiveMenu === true) {
                            setTimeout(restoreActiveSidebar, 80);
                        }
                    }

                    // Handle Custom Production Unit Panels
                    // Detect custom left panel (usually col-md-2, col-md-3 or width-25)
                    var $customLeft = $('.width-25, .wd-set.col-md-2, .wd-set.col-md-3, #dashbord_items .col-md-3, #production_unit .col-md-3, #production_unit .col-md-2, .wd-set.bg-setting');
                    var $customRight = $customLeft.siblings('.width-75, .col-md-8, .col-md-9, .sixty-eight-percent, .middle_body, .sty-bg-set, .hundred-percent, .max-height');

                    if ($customLeft.length) {
                        // CRITICAL: Only hide custom panels if the standard sidebar is NOT present
                        // If both are present, we want to keep the inner one visible (Dual Sidebar mode)
                        if (!isStandardSidebarPresent) {
                            if (isMinified) {
                                $customLeft.hide();
                                $customRight.css({'width': '100%', 'max-width': '100%', 'flex': '0 0 100%'})
                                            .removeClass('col-md-8 col-md-9 col-md-10')
                                            .addClass('col-md-12');
                            } else {
                                $customLeft.show();
                                $customRight.css({'width': '', 'max-width': '', 'flex': ''}).removeClass('col-md-12');
                                if ($customLeft.hasClass('col-md-2')) $customRight.addClass('col-md-10');
                                else if ($customLeft.hasClass('col-md-3')) $customRight.addClass('col-md-9');
                                else if ($customRight.hasClass('sty-bg-set')) $customRight.addClass('col-md-8');
                            }
                        } else {
                            // Dual sidebar mode: Ensure custom left panel is VISIBLE
                            $customLeft.show();
                            $customRight.removeClass('col-md-12');
                        }
                    }
                    
                    // Fix Toggle Button Position
                    var $btn = $('#main-menu-act');
                    if (isMinified) {
                        $btn.css('left', '0');
                    } else {
                        var leftPosition = 250;
                        if (isStandardSidebarPresent) {
                            var $sidebar = $('.sidebar-con');
                            var offsetLeft = $sidebar.offset() ? $sidebar.offset().left : 0;
                            var measuredWidth = $sidebar.outerWidth() || $('#sidebar-left').outerWidth() || 250;
                            if (measuredWidth && measuredWidth > 100) {
                                leftPosition = offsetLeft + measuredWidth;
                            }
                        } else if ($customLeft.length && $customLeft.is(':visible')) {
                            var offsetLeftCustom = $customLeft.offset() ? $customLeft.offset().left : 0;
                            var measuredCustomWidth = $customLeft.outerWidth();
                            if (measuredCustomWidth && measuredCustomWidth > 100) {
                                leftPosition = offsetLeftCustom + measuredCustomWidth;
                            }
                        }
                        $btn.css('left', leftPosition + 'px');
                    }
                }
                
                // Sidebar Menu Click Listener (tracks submenu expand/collapse animations in real time)
                document.addEventListener('click', function(e) {
                    var menuLink = e.target.closest('.main-menu a');
                    if (!menuLink) return;
                    
                    // Track sidebar width changes smoothly over the next 600ms during the toggle slide animation
                    for (var delay = 50; delay <= 600; delay += 50) {
                        setTimeout(function () { runInitialSync(false); }, delay);
                    }
                });

                // Global Click Listener
                document.addEventListener('click', function(e) {
                    var btn = e.target.closest('#main-menu-act');
                    if (!btn) return;
                    
                    e.preventDefault();
                    var $ = window.jQuery;
                    var oldState = $('body').hasClass('sidebar-minified');
                    
                    setTimeout(function() {
                        var newState = $('body').hasClass('sidebar-minified');
                        if (newState === oldState) {
                            newState = !oldState;
                        }
                        syncLayout(newState, !newState);
                        
                        if ($.cookie) {
                            $.cookie('sma_sidebar', newState ? 'minified' : 'full', { path: '/' });
                            $.cookie('production-sidebar-state', newState ? 'minified' : 'full', { path: '/' });
                        }

                        // Fix DataTables layout after sidebar toggle
                        function fixDataTables() {
                            if (!$ || !$.fn || !$.fn.dataTable) return;

                            $('table.dataTable').each(function() {
                                if (!$.fn.dataTable.isDataTable || !$.fn.dataTable.isDataTable(this)) return;
                                var $table = $(this);
                                var $wrapper = $table.closest('.dataTables_wrapper');

                                // Clear ALL stale inline widths at every level DataTables sets them
                                $wrapper.css('width', '');
                                $table.css('width', '100%');
                                // Clear colgroup column widths (DataTables sets these per-column)
                                $table.find('colgroup col').css('width', '');
                                // Clear header cell inline widths
                                $table.find('thead th').css('width', '');

                                // Also clear the scrollHead/scrollBody wrappers if present
                                $wrapper.find('.dataTables_scrollHead, .dataTables_scrollBody, .dataTables_scrollHeadInner').css('width', '');

                                // Call DataTables column recalculation
                                try { $table.DataTable().columns.adjust(); } catch(e) {}
                                try { $table.dataTable().fnAdjustColumnSizing(false); } catch(e) {}
                            });

                            // Trigger resize for any other layout-dependent components
                            window.dispatchEvent(new Event('resize'));
                        }

                        // Run immediately and again after any CSS transition finishes
                        setTimeout(fixDataTables, 50);
                        setTimeout(fixDataTables, 400);
                    }, 50);
                });

                // Initial Sync on Load (unconditional & repeated to ensure perfect visual sync)
                function runInitialSync(restoreActiveMenu) {
                    var isMinified = false;
                    if (window.jQuery && window.jQuery.cookie) {
                        var saved = window.jQuery.cookie('production-sidebar-state') || window.jQuery.cookie('sma_sidebar');
                        isMinified = (saved === 'minified');
                    }
                    syncLayout(isMinified, restoreActiveMenu === true);
                }
                runInitialSync();
                // Register window events to trigger positioning dynamically
                window.addEventListener('load', runInitialSync);
                window.addEventListener('resize', runInitialSync);
                
                // Track frequently over the first 2 seconds to seamlessly follow slideToggle submenu animations
                for (var delay = 50; delay <= 2000; delay += 100) {
                    setTimeout(runInitialSync, delay);
                }
                // Restore active module highlight and open submenus after sidebar expand
                function restoreActiveSidebar() {
                    var $sidebar = $('#sidebar-left');
                    if (!$sidebar.length) return;

                    // Find the active item at ANY depth inside the sidebar nav
                    var $activeItem = $sidebar.find('.sidebar-nav ul li.active').first();
                    if (!$activeItem.length) return;

                    // Walk UP through every ancestor <li> and show its direct <ul> submenu
                    $activeItem.parents('li').each(function() {
                        var $li = $(this);
                        var $childUl = $li.find('> ul');
                        if ($childUl.length) {
                            $childUl.show();
                            $li.find('> a')
                               .addClass('open')
                               .find('.chevron')
                               .removeClass('closed')
                               .addClass('opened');
                        }
                    });

                    // Also open the active item's own direct child submenu if it has one
                    var $directChild = $activeItem.find('> ul');
                    if ($directChild.length) {
                        $directChild.show();
                        $activeItem.find('> a')
                            .addClass('open')
                            .find('.chevron')
                            .removeClass('closed')
                            .addClass('opened');
                    }
                }

                // Use MutationObserver to reliably detect when sidebar expands
                // because core.js intercepts clicks and stops propagation
                if (window.MutationObserver) {
                    var lastIsMinified = $('#sidebar-left').hasClass('minified');
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.attributeName === 'class') {
                                var $sidebar = $('#sidebar-left');
                                if ($sidebar.length) {
                                    var currentIsMinified = $sidebar.hasClass('minified');
                                    if (lastIsMinified && !currentIsMinified) {
                                        // Sidebar transitioned from minified to expanded, restore active menu
                                        restoreActiveSidebar();
                                    }
                                    lastIsMinified = currentIsMinified;
                                }
                            }
                        });
                    });
                    
                    var sidebarNode = document.getElementById('sidebar-left');
                    if (sidebarNode) {
                        observer.observe(sidebarNode, { attributes: true });
                    }
                }
            });
        </script>
        <style>
            /* Hide sidebar slide toggle on mobile - overrides display:flex which bypasses Bootstrap visible-md/visible-lg */
            @media (max-width: 768px) {
                a#main-menu-act {
                    display: none !important;
                }
            }
            @media screen and (max-width: 1024px) {
                a#main-menu-act {
                    display: none !important;
                }
            }
            @media screen and (max-width: 360px) {
                a#main-menu-act {
                    display: none !important;
                }
            }
            @media screen and (max-width: 320px) {
                a#main-menu-act {
                    display: none !important;
                }
            }
            .alert_notify {            
                position: absolute;
                top: 50px;
                right: 10px;
                width: 350px;
                z-index: 55555;
                -webkit-box-shadow: 0px 5px 10px 0px rgba(102,102,102,1);
                -moz-box-shadow: 0px 5px 10px 0px rgba(102,102,102,1);
                box-shadow: 0px 5px 10px 0px rgba(102,102,102,1);
                display: block;
            }      

           .urbanpiper-stock_notify{
                position: absolute;
                top: 50px;
               
                width: 350px;
                z-index: 55555;
                -webkit-box-shadow: 0px 5px 10px 0px rgba(102,102,102,1);
                -moz-box-shadow: 0px 5px 10px 0px rgba(102,102,102,1);
                box-shadow: 0px 5px 10px 0px rgba(102,102,102,1);
                display: block;
            }  
            .input-group .form-control{
                z-index: 1;
            }
            .siteleng {
        display: inline-block;
        white-space: normal;
        word-break: break-word;
        width: 17% !important;
           }

           .navbar-nav>li>a:hover {
        background-color: #009DFF !important;
           }

            #setnav-section:hover img {
        border-color: #fff !important;
        color: #009DFF !important;
        filter: brightness(0) invert(1);
           }

         #setnav-section img {
        transition: filter 0.3s ease;
           }
           /* ============================================================
               SIDEBAR MINIFIED: Keep active module icon highlighted
               so the user can always see which module is selected,
               even when the sidebar is collapsed to icon-only mode.
               ============================================================ */
            #sidebar-left.minified .sidebar-nav > ul > li.active > a,
            #sidebar-left.minified .sidebar-nav > ul > li.active > a:hover {
                background-color: #428BCA !important;
                color: #fff !important;
            }
        </style>
        <link rel="stylesheet" href="<?= $assets ?>styles/bootstrap-tagsinput.css" />
        <link rel="stylesheet" href="<?= $assets ?>styles/bootstrap-tagsinput-app.css" />

        <?php $logopath = base_url("assets/icons/") ?>        
        <link rel="icon" type="image/png" sizes="16x16" href="<?= $logopath ?>favicon-16x16.png">
        <link rel="manifest" href="<?= $logopath ?>manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="<?= $logopath ?>ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <style>
            a#main-menu-act {
                display: flex;
                width: 20px !important;
                height: 70px !important;
                background: linear-gradient(to bottom, #333, #000) !important;
                color: #fff !important;
                text-align: center !important;
                line-height: 65px !important;
                border-radius: 0 12px 12px 0 !important;
                position: fixed !important;
                top: 50% !important;
                left: 250px; /* Sidebar width */
                transform: translateY(-50%) !important;
                z-index: 1030 !important; /* Below Bootstrap modal backdrop(1040) & dialog(1050) */
                text-decoration: none !important;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                box-shadow: 4px 0 10px rgba(0, 0, 0, 0.3) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                border-left: none !important;
                align-items: center !important;
                justify-content: center !important;
            }
            body.modal-open a#main-menu-act {
                display: none !important;
            }
            body.sidebar-minified a#main-menu-act {
                left: 0 !important;
            }
            a#main-menu-act:hover {
                background: linear-gradient(to bottom, #009DFF, #007ACC);
                width: 34px;
                box-shadow: 6px 0 20px rgba(0, 157, 255, 0.4);
                color: #fff !important;
            }
            a#main-menu-act i {
                font-size: 16px;
                color: #FFF !important;
                position: static !important;
                width: auto !important;
                height: auto !important;
                background: transparent !important;
                margin: 0 !important;
                line-height: inherit !important;
                transition: transform 0.3s ease;
            }
            /* Robust icon direction handling - pointing right when minified */
            body.sidebar-minified a#main-menu-act i:before {
                content: "\f101" !important; /* fa-angle-double-right */
            }
            body:not(.sidebar-minified) a#main-menu-act i:before {
                content: "\f100" !important; /* fa-angle-double-left */
            }
            
            /* Smooth transitions ONLY for custom Production Unit containers */
            /* NOTE: DO NOT add transition:all to Bootstrap .col-md-* classes - breaks DataTables! */
            .width-25, .width-75, .wd-set, .sty-bg-set, .hundred-percent {
                transition: width 0.3s ease, max-width 0.3s ease, flex 0.3s ease;
            }
            /* ============================================================
               SIDEBAR-MINIFIED: Force DataTables to fill the full width
               This is a CSS-first fix - runs synchronously when class toggles,
               no JS timing issues. Targets only when sidebar is actually closed.
               ============================================================ */
            body.sidebar-minified .dataTables_wrapper,
            body.sidebar-minified .dataTables_scrollBody,
            body.sidebar-minified .dataTables_scrollHead,
            body.sidebar-minified .dataTables_scrollHeadInner,
            body.sidebar-minified .table-responsive {
                width: 100% !important;
                min-width: 0 !important;
                box-sizing: border-box !important;
            }
            body.sidebar-minified table.dataTable {
                width: 100% !important;
                table-layout: auto !important;
            }
            body.sidebar-minified table.dataTable colgroup col {
                width: auto !important;
            }
            /* Fix #content box model so 40px border-left doesn't overflow */
            body.sidebar-minified #content {
                box-sizing: border-box !important;
            }
            /* Allow table-responsive to resize with the layout */
            .table-responsive {
                overflow-x: auto;
                width: 100%;
            }
            .dataTables_wrapper {
                width: 100% !important;
                min-width: 0 !important;
            }
            a#main-menu-act:hover i {
                transform: scale(1.2) !important;
            }
            #content {
                position: relative;
            }
        </style>
    </head>
    <body>
        <noscript>
        <div class="global-site-notice noscript">
            <div class="notice-inner">
                <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in your browser to utilize the functionality of this website.</p>
            </div>
        </div>
        </noscript>
        <div id="eshop-order-alert"></div>
        <div id="urbanpiper-order-alert"></div>
        <div id="urbanpiper-stock-alert"></div>
        <div id="purcahse-order-alert"></div>
        <div id="suplier-order-alert"></div>
        <div id="loading"></div>
        <div id="app_wrapper">
            <header id="header" class="navbar">
                <div class="container">
                    <a class="navbar-brand" href="<?= site_url() ?>"><span class="logo"><?= $Settings->site_name ?></span>&nbsp;<sub>
                            <?php
                            $pos_res = json_decode($Settings->pos_version, TRUE);
                            $pos_ver = $pos_res['version'];
                            ?>
                            <?= "Version " . $pos_ver ?></sub></a>
                    <div class="btn-group visible-xs pull-right btn-visible-sm">
                        <button class="navbar-toggle btn" type="button" data-toggle="collapse" data-target="#sidebar_menu">
                            <span class="fa fa-bars"></span>
                        </button>
                        <a href="<?= site_url('users/profile/' . $this->session->userdata('user_id')); ?>" class="btn">
                            <span class="fa fa-user"></span>
                        </a>
                        <a href="<?= site_url('logout'); ?>" class="btn">
                            <span class="fa fa-sign-out"></span>
                        </a>
                    </div>
                    <?php  

                    ?>
                    <div class="header-nav">
                    <ul class="nav navbar-nav pull-right">
                        <li class="dropdown">
                            <a class="btn account no-effect dropdown-toggle" id="setnav-section" data-toggle="dropdown"
                                href="#">
                                <div class="user" style="display: flex; align-items: center; gap: 8px;">
                                    <span style="color: white;"><?= lang('welcome') ?>
                                        <?= $this->session->userdata('username'); ?></span>
                                    <img alt="avatar" src="<?= $this->session->userdata('avatar') 
                             ? site_url() . 'assets/'.$Customer_assets.'/uploads/avatars/thumbs/' . $this->session->userdata('avatar') 
                             : base_url('assets/images/Account.svg'); ?>" class="mini_avatar img-rounded"
                                        style="width: 30px; height: 30px; border-radius: 50%;">
                                </div>
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="<?= site_url('users/profile/' . $this->session->userdata('user_id')); ?>">
                                        <i class="fa fa-user"></i> <?= lang('profile'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="<?= site_url('users/profile/' . $this->session->userdata('user_id') . '/#cpassword'); ?>"><i
                                            class="fa fa-key"></i> <?= lang('change_password'); ?>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <?php  if ($Owner && $Admin ) { ?>
                                <li>
                                    <a href="#" onclick="restBill()">
                                        <i class="fa fa-sign-out"></i> <?= lang('Reset & logout'); ?>
                                    </a>
                                </li>
                                <?php } ?>
                                <li>
                                    <a href="<?= site_url('logout'); ?>">
                                        <i class="fa fa-sign-out"></i> <?= lang('logout'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                        <ul class="nav navbar-nav pull-right">
                            <!-- <li class="dropdown hidden-xs"><a class="btn blightOrange tip" title="<?= lang('dashboard') ?>"
                                                              data-placement="bottom" href="<?= site_url('welcome') ?>"><i
                                        class="fa fa-dashboard"></i></a></li> -->
                                <?php if (POS) { ?>
                        <li class="dropdown hidden-xs">
                            <a class="btn" id="setnav-section" title="<?= lang('pos') ?>" data-placement="bottom"
                                href="<?= site_url('pos') ?>"
                                style="display: inline-flex; align-items: center; border: 1px solid #fff; padding: 6px 12px; color: #fff; background-color: transparent; font-weight: 500; border-radius: 4px; font-size: 14px;margin-top:8px;">
                                <img src="<?= $assets ?>pos/images/PosNew.svg" alt="POS"
                                    style="height: 18px; margin-right: 8px;">
                                <span style="line-height: 1; color:white;"><?= lang('pos') ?></span>
                            </a>
                        </li>
                        <?php } ?>
                        <li class="dropdown hidden-sm">
                            <a class=" " title="<?= lang('styles') ?>" data-placement="bottom" data-toggle="dropdown"
                                href="#" style="display: flex; align-items: center; padding: 6px 10px;">
                                <img src="<?= $assets ?>pos/images/StylesWhiteNew.svg" alt="Style"
                                    style="height: 35px; margin-right: 6px; margin-top: -3px;">
                            </a>
                             <ul class="dropdown-menu pull-right">
                                    <li class="bwhite noPadding">
                                        <a href="#" id="fixed" class="">
                                            <i class="fa fa-angle-double-left"></i>
                                            <span id="fixedText">Fixed</span>
                                        </a>
                                        <a href="#" id="cssLight" class="grey">
                                            <i class="fa fa-stop"></i> Grey
                                        </a>
                                        <a href="#" id="cssBlue" class="blue">
                                            <i class="fa fa-stop"></i> Blue
                                        </a>
                                        <a href="#" id="cssBlack" class="black">
                                            <i class="fa fa-stop"></i> Black
                                        </a>
                                    </li>
                                </ul>
                        </li>
                        <li class="dropdown hidden-xs">
                            <a class="" title="<?= lang('calculator') ?>" data-placement="bottom" href="#"
                                data-toggle="dropdown" style="display: flex; align-items: center;">
                                <img src="<?= $assets ?>pos/images/CalculatorWhiteNew.svg" alt="Calculator"
                                    style="height:35px; margin-right: 6px; margin-top: -12px;">
                                <i class=""></i>
                            </a>
                            <ul class="dropdown-menu pull-right calc">
                                <li class="dropdown-content">
                                    <span id="inlineCalc"></span>
                                </li>
                            </ul>
                        </li>
                        <?php if ($info) { ?>
                        <li class="dropdown hidden-sm">
                            <a class="btn  tip" title="<?= lang('notifications') ?>" data-placement="bottom" href="#"
                                data-toggle="dropdown">
                                <i class="fa fa-bell"></i>
                                <span class="number blightOrange black"><?= sizeof($info) ?></span>
                            </a>
                            <ul class="dropdown-menu pull-right content-scroll">
                                <li class="dropdown-header"><i class="fa fa-info-circle"></i>
                                    <?= lang('notifications'); ?></li>
                                <li class="dropdown-content">
                                    <div class="scroll-div">
                                        <div class="top-menu-scroll">
                                            <ol class="oe">
                                                <?php
                                                        foreach ($info as $n) {
                                                            echo '<li>' . $n->comment . '</li>';
                                                        }
                                                        ?>
                                            </ol>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <?php } ?>
                        <?php if ($events) { ?>
                        <li class="dropdown hidden-xs">
                            <a class="btn borange tip" title="<?= lang('calendar') ?>" data-placement="bottom" href="#"
                                data-toggle="dropdown">
                                <i class="fa fa-calendar"></i>
                                <span class="number blightOrange black"><?= sizeof($events) ?></span>
                            </a>
                            <ul class="dropdown-menu pull-right content-scroll">
                                <li class="dropdown-header">
                                    <i class="fa fa-calendar"></i> <?= lang('upcoming_events'); ?>
                                </li>
                                <li class="dropdown-content">
                                    <div class="top-menu-scroll">
                                        <ol class="oe">
                                            <?php
                                                    foreach ($events as $event) {
                                                        echo '<li>' . date($dateFormats['php_ldate'], strtotime($event->start)) . ' <strong>' . $event->title . '</strong><br>' . $event->description . '</li>';
                                                    }
                                                    ?>
                                        </ol>
                                    </div>
                                </li>
                                <li class="dropdown-footer">
                                    <a href="<?= site_url('calendar') ?>" class="btn-block link">
                                        <i class="fa fa-calendar"></i> <?= lang('calendar') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php } else { ?>
                        <li class="dropdown hidden-xs">
                            <a class="" title="<?= lang('calendar') ?>" data-placement="bottom"
                                href="<?= site_url('calendar') ?>">
                                <img src="<?= $assets ?>pos/images/CalendarWhiteNew.svg" alt="Calendar"
                                    style="height: 35px; margin-right: 6px; margin-top: -12px;">
                            </a>
                        </li>

                        <?php } ?>
                        <?php if ($Owner) { ?>
                        <li class="dropdown hidden-sm">
                            <a class="" title="<?= lang('settings') ?>" data-placement="bottom"
                                href="<?= site_url('system_settings') ?>">
                                <!-- <i class="fa fa-cogs"></i> -->
                                <img src="<?= $assets ?>pos/images/SettingsWhiteNew.svg" alt="Settings_White"
                                    style="height: 29px; margin-right: 8px;margin-top: -8px;">
                            </a>
                        </li>
                        <?php } ?>

                        <ul class="dropdown-menu pull-right">
                            <li class="bwhite noPadding">
                                <a href="#" id="fixed" class="">
                                    <i class="fa fa-angle-double-left"></i>
                                    <span id="fixedText">Fixed</span>
                                </a>
                                <a href="#" id="cssLight" class="grey">
                                    <i class="fa fa-stop"></i> Grey
                                </a>
                                <a href="#" id="cssBlue" class="blue">
                                    <i class="fa fa-stop"></i> Blue
                                </a>
                                <a href="#" id="cssBlack" class="black">
                                    <i class="fa fa-stop"></i> Black
                                </a>
                            </li>
                        </ul>
                        </li>
                        <li class="dropdown hidden-xs">
                            <!-- <a class="btn bblue tip" title="<?= lang('language') ?>" data-placement="bottom"
                                data-toggle="dropdown" href="#">
                                <img src="<?= base_url('assets/images/' . $Settings->user_language . '.png'); ?>"
                                    alt="">
                            </a> -->
                            <a class="" title="<?= lang('language') ?>" data-placement="bottom" data-toggle="dropdown"
                                href="#">
                                <img src="<?= base_url('assets/images/' . $Settings->country_name . '.png'); ?>" alt=""
                                    style="height: 24px; vertical-align: middle;margin-top:-7px;">
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <?php
                                    $scanned_lang_dir = array_map(function($path) {
                                        return basename($path);
                                    }, glob(APPPATH . 'language/*', GLOB_ONLYDIR));


                                    foreach ($scanned_lang_dir as $entry) {
                                        ?>
                                <li>
                                    <a href="<?= site_url('welcome/language/' . $entry); ?>">
                                        <img src="<?= base_url(); ?>assets/images/<?= $entry; ?>.png"
                                            class="language-img">
                                        &nbsp;&nbsp;<?= ucwords($entry); ?>
                                    </a>
                                </li>
                                <?php } ?>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?= site_url('welcome/toggle_rtl') ?>">
                                        <i class="fa fa-align-<?= $Settings->user_rtl ? 'right' : 'left'; ?>"></i>
                                        <?= lang('toggle_alignment') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php if ($Owner && $Settings->update) { ?>
                        <li class="dropdown hidden-sm">
                            <a class="btn bdarkGreen tip" title="<?= lang('update_available') ?>"
                                data-placement="bottom" data-container="body"
                                href="<?= site_url('system_settings/updates') ?>">
                                <i class="fa fa-download"></i>
                            </a>
                        </li>
                        <?php } ?>

                        <?php if ($Owner) { ?>
                        <li class="dropdown">
                            <a class="" id="today_profit" title="<?= lang('today_profit') ?>"
                                data-placement="bottom" data-html="true" href="<?= site_url('reports/profit') ?>"
                                data-toggle="modal" data-target="#myModal">
                                <!-- <i class="fa fa-line-chart"></i> -->
                                <img src="<?= $assets ?>pos/images/TodaysProfitWhiteNew.svg" alt="Today'sProfit_White"
                                    style="height: 30px; margin-right: 8px;margin-top: -11px;">
                            </a>
                        </li>
                        <?php } ?>
                        <?php if ($Owner || $Admin) { ?>
                        <?php if (POS) { ?>
                        <li class="dropdown hidden-xs">
                            <a class="" title="<?= lang('list_open_registers') ?>" data-placement="bottom"
                                href="<?= site_url('pos/registers') ?>">
                                <!-- <i class="fa fa-book"></i> -->
                                <img src="<?= $assets ?>pos/images/ListOpenRegWhiteNew.svg" alt="ListOpenReg_White"
                                    style="height: 30px; margin-right: 8px;margin-top: -8px;">
                            </a>
                        </li>
                        <?php } ?>
                        <li class="dropdown hidden-xs">
                            <a class="" title="<?= lang('clear_ls') ?>" data-placement="bottom" id="clearLS" href="#">
                                <!-- <i class="fa fa-trash"></i> -->
                                <img src="<?= $assets ?>pos/images/ClearDataWhiteNew.svg" alt="ClearData_White"
                                    style="height: 30px; margin-right: 8px;margin-top: -8px;">
                            </a>
                        </li>
                        <?php } ?>
                        <?php if ($eshop_due_payment && isset($eshop_due_payment->cnt) && $eshop_due_payment->cnt > 0): ?>
                        <a class="" title="Payment due orders (ESHOP)" data-placement="bottom"
                            href="<?= site_url('sales/eshop_sales?status=due') ?>">
                            <i class="" aria-hidden="true"></i><?php echo $eshop_due_payment->cnt ?>
                            <img src="<?= $assets ?>pos/images/NotificationsNew.svg" alt="Notifications_White"
                                style="height: 35px;  margin-right: 6px;  margin-top: 4px;">
                        </a>
                        <?php endif; ?>
                        <li class="dropdown hidden-xs">
                            <a class="  " title="New orders (ESHOP)" data-placement="bottom"
                                href="<?= site_url('orders/eshop_order') ?>">
                                <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                <img src="<?= $assets ?>pos/images/NewEshopOrdersWhiteNew.svg"
                                    alt="Urbanpiper New Eshop Orders"
                                    style="height: 35px;  margin-right: 6px;  margin-top: -12px;">
                                <span id="eshop_new_orders">0</span>
                            </a>
                        </li>
                        <li class="dropdown hidden-xs">
                            <a class="  " title="New orders (Urbanpiper)" data-placement="bottom"
                                href="<?= site_url('urban_piper') ?>" style="display: flex; align-items: center;">
                                <img src="<?= $assets ?>pos/images/UrbanpiperOrdersWhiteNew.svg" alt="Urbanpiper Orders"
                                    style="height: 35px;  margin-right: 6px;  margin-top: -12px;">
                                <span id="urbanpipersorder">0</span>
                            </a>

                        </li>
                        <?php if (($Owner || $Admin || $GP['reports-quantity_alerts'] || $GP['reports-expiry_alerts']) && ($qty_alert_num > 0 || $exp_alert_num > 0)) { ?>
                        <li class="dropdown hidden-sm">
                            <a class="" title="<?= lang('alerts') ?>" data-placement="bottom" data-toggle="dropdown"
                                href="#">
                                <img src="<?= $assets ?>pos/images/AlertNew.svg" alt="Alert"
                                    style="height: 34px; vertical-align: middle;margin-top:-12px;">
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="<?= site_url('reports/quantity_alerts') ?>" class="">
                                        <span class="label label-danger pull-right"
                                            style="margin-top:3px;"><?= $qty_alert_num; ?></span>
                                        <span style="padding-right: 35px;"><?= lang('quantity_alerts') ?></span>
                                    </a>
                                </li>


                                <?php if ($Settings->product_expiry) { ?>
                                <li>
                                    <a href="<?= site_url('reports/expiry_alerts') ?>" class="">
                                        <span class="label label-danger pull-right"
                                            style="margin-top:3px;"><?= $exp_alert_num; ?></span>
                                        <span style="padding-right: 35px;"><?= lang('expiry_alerts') ?></span>
                                    </a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </header>

            <?php
            echo '<div class="container" id="container">
                <div class="row" id="main-con">
                    <table class="lt">
                        <tr>';
            ?>
            <td class="sidebar-con">
                <div id="sidebar-left">
                    <div class="sidebar-nav nav-collapse collapse navbar-collapse" id="sidebar_menu">
                        <ul class="nav main-menu">
                            <li class="mm_welcome">
                                <a href="<?= site_url() ?>">
                                    <i class="fa fa-dashboard"></i>
                                    <span class="text"> <?= lang('dashboard'); ?></span>
                                </a>
                            </li>
                            <?php
                            if ($Owner || $Admin) {
                                include_once 'admin_access_menu.php';
                            } else {  
                                include_once 'user_access_menu.php';
                            } 
                            ?>
                        </ul>
                    </div>
                </div>
            </td>
            <td class="content-con">
                <div id="content">
                    <a href="#" id="main-menu-act" class="full visible-md visible-lg" onclick="return false;">
                        <i class="fa fa-angle-double-left"></i>
                    </a>
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <ul class="breadcrumb">
                                <?php
                                foreach ($bc as $b) {
                                    if ($b['link'] === '#') {
                                        echo '<li class="active">' . $b['page'] . '</li>';
                                    } else {
                                        echo '<li><a href="' . $b['link'] . '">' . $b['page'] . '</a></li>';
                                    }
                                }
                                ?>
                                <li class="right_log hidden-xs">
                                    <?= lang('your_ip') . ' ' . $ip_address . " <span class='hidden-sm'>( " . lang('last_login_at') . ": " . date($dateFormats['php_ldate'], $this->session->userdata('old_last_login')) . " " . ($this->session->userdata('last_ip') != $ip_address ? lang('ip:') . ' ' . $this->session->userdata('last_ip') : '') . " )</span>" ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <?php if ($message) { ?>
                                <div class="alert alert-success" id="errs">
                                    <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                    <?= $message; ?>
                                </div>
                            <?php } ?>
                            <?php if ($error) { ?>
                                <div class="alert alert-danger" >
                                    <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                    <?= $error; ?>
                                </div>
                            <?php } ?>
                            <?php if ($warning) { ?>
                                <div class="alert alert-warning">
                                    <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                                    <?= $warning; ?>
                                </div>
                            <?php } ?>
                            <?php
                            if ($info) {
                                foreach ($info as $n) {
                                    if (!$this->session->userdata('hidden' . $n->id)) {
                                        ?>
                                        <div class="alert alert-info" style="display:block;">
                                            <a href="#" id="<?= $n->id ?>" class="close hideComment external"
                                               data-dismiss="alert">&times;</a>
                                               <?= $n->comment; ?>
                                        </div>
                                        <?php
                                    }
                                }
                            }
                            ?>
                            <div class="alerts-con" id="err"></div>

