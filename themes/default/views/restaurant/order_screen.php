<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Order Taking - Table <?= $order->table_name ?></title>

    <!-- jQuery 3.7.1 (full build - required for jQuery UI) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Initialize site object for date formats -->
    <script>
        var site = site || {};
        site.settings = site.settings || {};
        site.settings.rtl = 0; // Set RTL if needed
        site.base_url = '<?= base_url() ?>';
        // Provide customer assets subfolder for image paths (fallback to 'localhost')
        <?php
          $customer_assets_js = 'localhost';
          if (isset($Customer_assets) && $Customer_assets) {
            $customer_assets_js = $Customer_assets;
          } elseif (isset($this->Settings) && isset($this->Settings->customer_assets) && $this->Settings->customer_assets) {
            $customer_assets_js = $this->Settings->customer_assets;
          }
        ?>
        site.customer_assets = '<?= addslashes($customer_assets_js) ?>';
        site.dateFormats = {
            js_ldate: 'YYYY-MM-DD HH:mm:ss',
            js_sdate: 'YYYY-MM-DD',
            js_ldate_time: 'HH:mm',
            js_sdate_time: 'HH:mm'
        };
    </script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $assets ?>restaurant/assets/css/theme.css" />
    <!-- Order screen CSS -->
    <link rel="stylesheet" href="<?= $assets ?>restaurant/css/order_screen.css" />

    <?php /* CSRF for AJAX (read by order_screen.js) */ ?>
    <meta name="ci-csrf-name" content="<?= $this->security->get_csrf_token_name(); ?>">
    <meta name="ci-csrf-hash" content="<?= $this->security->get_csrf_hash(); ?>">
</head>

<body class="order-screen-body" data-order-id="<?= (int) $order->id ?>">
    <?php
        $order_items_count = !empty($order_items) ? count($order_items) : 0;
        $order_guest_count = isset($order->guest_count) ? (int) $order->guest_count : 0;
        $guest_list_count = !empty($guests) ? count($guests) : 0;
        $display_guest_count = $guest_list_count > 0 ? $guest_list_count : $order_guest_count;
    ?>
    <div class="order-screen-wrapper">
        <?php include('partials/header.php'); ?>

        <div class="container-xxl order-screen-main py-4" style="padding-top: 0 !important; max-width: 100%;">
            <style>
                :root { --primary-color: #e91e63; }
                body { background-color: #f8f9fa; }
                .new-ui-header { background: #fff; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
                .new-top-header { display: flex; align-items: center; margin-bottom: 20px; }
                .new-top-header h4 { font-weight: bold; margin: 0; display: flex; align-items: center; gap: 10px; }
                .new-search-box { position: relative; margin-bottom: 15px; }
                .new-search-box input { border: 1px solid #e91e63; border-radius: 8px; padding-left: 45px; height: 50px; }
                .new-search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); font-size: 1.2rem; }
                
                .new-filters { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 20px; }
                .filter-pill { border: 1px solid #ddd; padding: 6px 15px; border-radius: 6px; background: #fff; font-weight: 600; display: flex; align-items: center; gap: 6px; color: #333; cursor: pointer; }
                .filter-pill.veg { border-color: #28a745; color: #333; }
                .filter-pill.non-veg { border-color: #dc3545; color: #333; }
                .filter-pill i { font-size: 14px; }
                .clear-all-btn { color: #e91e63; font-weight: bold; margin-left: auto; cursor: pointer; text-decoration: none; }
                
                .new-categories { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px; scrollbar-width: none; margin-bottom: 20px; }
                .new-categories::-webkit-scrollbar { display: none; }
                .cat-pill { padding: 8px 20px; border: 1px solid #333; border-radius: 8px; background: #fff; color: #333; white-space: nowrap; cursor: pointer; transition: all 0.2s; }
                .cat-pill.active { background: #333; color: #fff; }
                
                .section-title { font-weight: bold; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
                
                .menu-item-new { background: #fff; border: none; border-radius: 12px; padding: 15px; margin-bottom: 15px; display: flex; align-items: center; box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
                .menu-thumb-new { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; margin-right: 15px; }
                .item-details { flex: 1; }
                .veg-icon-box { width: 16px; height: 16px; border: 1px solid; display: flex; align-items: center; justify-content: center; padding: 1px; font-size: 8px; margin-bottom: 5px; }
                .veg-icon-box.veg { border-color: #28a745; } .veg-icon-box.veg .dot { color: #28a745; }
                .veg-icon-box.non-veg { border-color: #dc3545; } .veg-icon-box.non-veg .dot { color: #dc3545; }
                .item-title { font-weight: bold; font-size: 16px; margin-bottom: 4px; color: #000; }
                .item-price { font-weight: bold; font-size: 15px; color: #000; }
                .add-btn-new { border: 1.5px solid #e91e63; color: #e91e63; background: #fff; padding: 8px 30px; border-radius: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; transition: 0.2s; font-size: 14px; }
                .add-btn-new:hover { background: #e91e63; color: #fff; }
                .menu-item-new { width: 100%; } /* Ensure items take full available width */

                /* Centering Logic */
                .order-screen-main { display: flex; flex-direction: column; align-items: center; }
                .new-ui-container { width: 100%; max-width: 800px; padding: 0 15px; }
                
                /* Hide sidebar logic */
                .order-summary-column { display: none !important; }
                .col-lg-7, .col-xl-8 { width: 100% !important; flex: 0 0 100%; max-width: 100%; }

                /* Active filter styles */
                .filter-pill.active { background-color: #f0f8ff; border-color: #007bff; }
                .filter-pill.veg.active { background-color: #e8f5e9; border-color: #28a745; }
                .filter-pill.non-veg.active { background-color: #fce8e8; border-color: #dc3545; }

                /* Order Items List Styling */
                .order-section-header { margin-bottom: 0px; margin-top: 15px; }
                .guest-order-block { margin-bottom: 20px; }
                .guest-order-header {
                     display: flex; align-items: center; justify-content: space-between;
                     padding: 8px 0; border-bottom: 1px solid #eee; margin-bottom: 10px;
                }
                .guest-badge {
                    background: #fce4ec; color: #e91e63;
                    border: 1px solid #e91e63; border-radius: 8px;
                    padding: 5px 12px; font-weight: bold; display: flex; align-items: center; gap: 5px;
                }
                .guest-actions { display: flex; gap: 10px; }
                .guest-action-btn {
                    width: 30px; height: 30px; border-radius: 6px; border: 1px solid #e91e63;
                    color: #e91e63; background: #fff; display: flex; align-items: center; justify-content: center;
                }
                .ordered-item-card {
                    background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 10px 15px;
                    margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;
                    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
                }
                .ordered-item-info { display: flex; flex-direction: column; }
                .ordered-item-name { font-weight: 600; font-size: 15px; color: #333; margin-bottom: 4px; }
                .ordered-item-meta { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
                .meta-icon { font-size: 14px; color: #666; }
                .qty-control-sm {
                    display: flex; align-items: center; gap: 5px;
                    border: 1px solid #e91e63; border-radius: 6px; padding: 2px;
                }
                .qty-btn-sm {
                    width: 24px; height: 24px; background: #e91e63; color: white; border: none; border-radius: 4px;
                    display: flex; align-items: center; justify-content: center; font-size: 16px; line-height: 1;
                }
                .qty-val-sm { font-weight: bold; min-width: 20px; text-align: center; font-size: 14px; }
            </style>


            
            <!-- New UI Structure -->
            <div class="new-ui-container">
                <div class="new-top-header">
                <h4 onclick="backToTables()" style="cursor: pointer; color: #000;">
                    <img
                        src="<?= base_url('themes/default/assets/restaurant/images/back_icon.svg'); ?>"
                        alt="Back Icon">
                        Menu
                </h4>
            </div>
                
                
                <div class="new-search-box">
                    <i class="bi bi-search" style="color: #000;"></i>
                    <input type="text" class="form-control" placeholder="Search item" id="new_menu_search_display" onkeyup="$('#menu-search').val(this.value).trigger('input')" style="border-color: #e91e63; box-shadow: none;">
                </div>
                
                <div class="new-filters">
                    <div class="filter-pill veg" data-filter="meal-type" data-value="1">
                        <div class="veg-icon-box veg">
                            <img
                                src="<?= base_url('themes/default/assets/restaurant/images/veg_icon.svg'); ?>"
                                alt="Veg"
                                class="dot">
                        </div>
                        Veg
                    </div>

                    <div class="filter-pill non-veg" data-filter="meal-type" data-value="2">
                        <div class="veg-icon-box non-veg">
                            <img
                                src="<?= base_url('themes/default/assets/restaurant/images/non-veg_icon.svg'); ?>"
                                alt="Non-Veg"
                                class="dot">
                        </div>
                        Non-veg
                    </div>
                    <div class="filter-pill" onclick="console.log('Spice filter')"><img 
                    src="<?= base_url('themes/default/assets/restaurant/images/low_spicy_icon.svg'); ?>" 
                    alt="Low Spicy"
                    class="spice-level-icon" ></div>
                        <div class="filter-pill" onclick="console.log('Spice filter')"><img 
                    src="<?= base_url('themes/default/assets/restaurant/images/medium_spicy_icon.svg'); ?>" 
                    alt="Medium Spicy"
                    class="spice-level-icon" ></div>
                        <div class="filter-pill" onclick="console.log('Spice filter')"><img 
                    src="<?= base_url('themes/default/assets/restaurant/images/high_spicy_icon.svg'); ?>" 
                    alt="High Spicy"
                    class="spice-level-icon" ></div>
                        <a class="clear-all-btn" href="javascript:location.reload()">Clear All</a>
                    </div>
                
                <!-- Guest Selector Counter -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <!-- <div class="guest-counter" style="display: flex; align-items: center; gap: 10px;">
                        <span class="fw-bold">Guest:</span>
                        <button class="guest-btn" onclick="cycleGuest(-1)" style="background: #e9176b; color: white; border: none; border-radius: 5px; width: 32px; height: 32px; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">-</button>
                        <span id="active-guest-display" class="guest-count-display fw-bold" style="font-size: 1.2rem; min-width: 30px; text-align: center;">1</span>
                        <button class="guest-btn" onclick="cycleGuest(1)" style="background: #e9176b; color: white; border: none; border-radius: 5px; width: 32px; height: 32px; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">+</button>
                    </div> -->
                    <!-- Hidden inputs for logic -->
                    <input type="hidden" id="selected-guest-num" value="1">
                    <div class="guest-tab active d-none" data-guest-number="1" data-guest-id="1"></div> <!-- Logic Hook -->
                </div>
                
                <!-- New Order Items List for showing the order item in menuw screen  -->
                <!-- <div class="mb-4">
                     <h5 class="fw-bold mb-3">Order Items (<span id="total-order-count">0</span> Items)</h5>
                     Buttons for All Guests actions could go here 
                     <button class="btn btn-sm btn-outline-secondary mb-3" onclick="selectGuest('all')">
                        <i class="bi bi-people"></i> All
                     </button>
                     
                     <div id="new-order-items-list">
                         Dynamically Populated
                     </div>
                </div> -->

                    <div class="menu-categories-section mb-4">
                        <h5 class="card-title fw-bold">Menu</h5>
                    </div>
                <div class="new-categories" id="new-categories-scroll"> 
                     <div class="cat-pill active" onclick="selectCategory('all'); $('.cat-pill').removeClass('active'); $(this).addClass('active');">All Items</div>
                     <?php if (!empty($menu_categories)): ?>
                        <?php foreach ($menu_categories as $category): ?>
                            <div class="cat-pill" onclick="selectCategory(<?= (int)$category->id ?>); $('.cat-pill').removeClass('active'); $(this).addClass('active');">
                                <?= htmlspecialchars($category->name) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Section Header dynamically shown by JS or static -->
                 <div class="section-title" id="menu-section-header" onclick="toggleMenuSection()" style="cursor: pointer;">
                    <span id="current-category-title" style="font-size: 1.1rem;">All Items</span>
                    <i class="bi bi-chevron-up"></i>
                </div> 

                <!-- Moved Items Container Here for Perfect Alignment -->
                <div id="loading-items" class="text-center" style="display: none; margin-top: 20px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <!-- FORCE full width and block display -->
                <div id="menu-items-container" style="width: 100%; display: block;">
                    <!-- Items rendered here by JS -->
                </div>
            </div>



            <!-- Existing Blocks (Completely Commented Out or Hidden) -->
            <div style="display:none;">

            <!-- Order Header -->
            </div> <!-- End Hidden Header -->

                <div class="row g-3 align-items-center">
                    <!-- <div class="col-12 col-lg-7">
                        <h3 class="mb-2">
                            <i class="bi bi-table"></i> <?= htmlspecialchars($order->table_name) ?>
                        </h3>
                        <p class="mb-0">
                            <i class="bi bi-people"></i> <?= $display_guest_count ?> Guest<?= $display_guest_count === 1 ? '' : 's' ?> |
                            Order #<?= $order->id ?> |
                            Status:
                            <span class="badge bg-light text-dark"><?= $order->status ?></span>
                        </p>
                        <div class="mt-3 d-lg-none">
                            <button class="btn back-btn w-100" onclick="backToTables()">
                                <i class="bi bi-arrow-left"></i> Back to Tables
                            </button>
                        </div>
                    </div> -->
                    <!-- <div class="col-12 col-lg-5 d-lg-flex align-items-center justify-content-lg-end order-header-actions-wrapper">
                        <div class="d-flex flex-wrap justify-content-lg-end gap-2 order-header-actions">
                            <button class="btn back-btn d-none d-lg-inline-flex" onclick="backToTables()">
                                <i class="bi bi-arrow-left"></i> Back
                            </button>
                            <button class="cart-button cart-toggle">
                                <i class="bi bi-cart"></i>
                                <span class="cart-button-label">Cart</span>
                                <span class="cart-count-bubble"><span id="cart-count"><?= $order_items_count ?></span></span>
                            </button>
                            <?php if (!empty($order) && isset($order->status) && strtolower($order->status) === 'ready'): ?>
                                <button type="button" class="btn btn-success" data-action="mark-served">
                                    <i class="bi bi-check2-circle"></i> Mark Served
                                </button>
                            <?php endif; ?>
                        </div>
                    </div> -->
                </div>
            </div>

            </div> <!-- End Old Menu Section -->

            <!-- New Items Container Location (REMOVED - Moved Up) -->
            <!-- <div class="row g-4 order-content"> ... </div> -->

                <!-- Menu Section -->
                <!-- <div class="col-12 col-xl-8 col-lg-7 order-main-column"> -->
                <!-- Guest Selection -->
                <!-- <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-person-check"></i> Select Guest
                        </h5>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="guest-tab active" data-guest-id="all" onclick="selectGuest('all')">
                                <i class="bi bi-people"></i> All Guests
                            </div>
                            <?php if (!empty($guests)): ?>
                                <?php foreach ($guests as $index => $guest): ?>
                                    <div class="guest-tab" data-guest-id="<?= $guest->id ?>"
                                        data-guest-number="<?= $index + 1 ?>" onclick="selectGuest(<?= (int)$guest->id ?>)">
                                        <i class="bi bi-person"></i> Guest <?= $index + 1 ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="increaseGuest()">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="decreaseGuest()">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                        </div>
                    </div>
                </div> -->

                <!-- Search & Filters -->
                <!-- <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="menu-search" oninput="loadMenuItems()"
                                        placeholder="Search menu items or codes...">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="scroll-x">
                                    <?php if (!empty($meal_types)): ?>
                                        <?php foreach ($meal_types as $meal_type): ?>
                                            <span class="filter-badge" data-filter="meal-type"
                                                data-value="<?= $meal_type->id ?>">
                                                <?= htmlspecialchars($meal_type->name) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- Menu Categories -->
                <!-- <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-grid"></i> Menu Categories
                        </h5>
                        <div class="d-flex flex-wrap scroll-x" id="menu-categories">
                            <div class="menu-category active" data-category-id="all" onclick="selectCategory('all')">
                                All Items
                            </div>
                            <?php if (!empty($menu_categories)): ?>
                                <?php foreach ($menu_categories as $category): ?>
                                    <div class="menu-category" data-category-id="<?= $category->id ?>" onclick="selectCategory(<?= (int)$category->id ?>)">
                                        <?= htmlspecialchars($category->name) ?>
                                        <?php if (isset($category->product_count)): ?>
                                            <span class="badge bg-light text-dark ms-1"><?= (int) $category->product_count ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div> -->

                <!-- Menu Items -->
                <!-- <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-list"></i> Menu Items
                        </h5>
                        <div id="loading-items" class="text-center" style="display: none;">
                            <div class="row w-100 g-3">
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="menu-item skeleton">
                                        <div class="menu-item-thumb"></div>
                                        <div class="menu-item-body">
                                            <div class="skeleton-line"></div>
                                            <div class="skeleton-line short"></div>
                                            <div class="skeleton-pill"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="menu-item skeleton">
                                        <div class="menu-item-thumb"></div>
                                        <div class="menu-item-body">
                                            <div class="skeleton-line"></div>
                                            <div class="skeleton-line short"></div>
                                            <div class="skeleton-pill"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="menu-item skeleton">
                                        <div class="menu-item-thumb"></div>
                                        <div class="menu-item-body">
                                            <div class="skeleton-line"></div>
                                            <div class="skeleton-line short"></div>
                                            <div class="skeleton-pill"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <div id="menu-items-container">
                            <!-- Inline add controls appear on each item card; modal removed for faster flow -->
                        </div>
                    </div>
                <!-- </div> -->
            </div>

            <!-- Order Summary -->
            <!-- Order Summary Commented Out -->
            <!-- 
            <div class="col-12 col-xl-4 col-lg-5 order-summary-column">
               ...
            </div> 
            -->

                <!-- <div class="card order-summary-card sticky-lg-top">
                    <div class="card-header">
                        <h5 class="mb-0 d-flex align-items-center gap-2">
                            <span><i class="bi bi-cart"></i> Order Summary</span>
                            <span class="summary-count" id="order-summary-count"><?= $order_items_count ?></span>
                        </h5>
                    </div>
                    <div class="card-body" style="max-height: 60vh; overflow-y: auto;">
                        <?php
                        // Build guest id -> display number map (1..N) for this order
                        $guest_numbers_map = [];
                        if (!empty($guests)) {
                            foreach ($guests as $idx => $g) {
                                $guest_numbers_map[$g->id] = $idx + 1;
                            }
                        }
                        ?>
                        <div id="order-items-container">
                            <?php if (!empty($order_items)): ?>
                                <?php foreach ($order_items as $item): ?>
                                    <?php
                                        $hasGuest = isset($item->res_orders_guests_id) && !empty($item->res_orders_guests_id);
                                        $guestIdAttr = $hasGuest ? (int) $item->res_orders_guests_id : 'all';
                                        $guestDisplayNumber = null;
                                        if ($hasGuest) {
                                            if (isset($item->guest_display_number) && $item->guest_display_number) {
                                                $guestDisplayNumber = (int) $item->guest_display_number;
                                            } elseif (isset($guest_numbers_map[$item->res_orders_guests_id])) {
                                                $guestDisplayNumber = $guest_numbers_map[$item->res_orders_guests_id];
                                            } elseif (isset($item->guest_number) && $item->guest_number) {
                                                $guestDisplayNumber = (int) $item->guest_number;
                                            }
                                        }
                                        $guestLabel = $guestDisplayNumber ? 'Guest ' . $guestDisplayNumber : 'All Guests';
                                    ?>
                                    <div class="order-item" data-item-id="<?= $item->id ?>"
                                        data-guest-id="<?= $guestIdAttr ?>"
                                        data-guest-number="<?= htmlspecialchars($guestDisplayNumber ? $guestDisplayNumber : 'all', ENT_QUOTES) ?>"
                                        data-product-id="<?= isset($item->sma_product_id) ? (int) $item->sma_product_id : 0 ?>"
                                        data-product-name="<?= htmlspecialchars($item->product_name, ENT_QUOTES) ?>"
                                        data-base-price="<?= ($item->quantity ? number_format((float) $item->amount / (float) $item->quantity, 2, '.', '') : number_format((float) $item->amount, 2, '.', '')) ?>"
                                        data-allergy-ids="<?= htmlspecialchars(isset($item->sma_res_common_allergies_list) ? $item->sma_res_common_allergies_list : '', ENT_QUOTES) ?>"
                                        data-custom-allergies="<?= htmlspecialchars(isset($item->custom_allergies_text) ? $item->custom_allergies_text : '', ENT_QUOTES) ?>"
                                        data-onion-flag="<?= isset($item->onion_flag) ? (int) $item->onion_flag : 1 ?>"
                                        data-garlic-flag="<?= isset($item->garlic_flag) ? (int) $item->garlic_flag : 1 ?>"
                                        data-addon-ids="<?= htmlspecialchars(isset($item->on_add_on_id) ? $item->on_add_on_id : '', ENT_QUOTES) ?>"
                                        data-topping-ids="<?= htmlspecialchars(isset($item->on_toppings_id) ? $item->on_toppings_id : '', ENT_QUOTES) ?>"
                                        data-spice-level="<?= htmlspecialchars(isset($item->spice_level) ? $item->spice_level : '', ENT_QUOTES) ?>"
                                        data-meat-wellness-id="<?= isset($item->sma_res_meat_wellness_id) ? (int) $item->sma_res_meat_wellness_id : 0 ?>"
                                        data-quantity="<?= isset($item->quantity) ? (float) $item->quantity : 1 ?>"
                                        data-instructions="<?= htmlspecialchars(isset($item->special_instructions) ? $item->special_instructions : (isset($item->instructions) ? $item->instructions : ''), ENT_QUOTES) ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="text-end">
                                                <strong><?= $this->sma->formatMoney($item->amount) ?></strong>
                                            </div>
                                        </div>
                                        <?php
                                        // Build display lines for allergies/custom notes beneath the item
                                        $allergy_bits = [];
                                        if (!empty($item->allergy_names) && is_array($item->allergy_names)) {
                                            $allergy_bits[] = 'Allergies: ' . implode(', ', $item->allergy_names);
                                        }
                                        // No Onion / No Garlic flags (0 means NO as per UI mapping)
                                        if (isset($item->onion_flag) && (string) $item->onion_flag === '0') {
                                            $allergy_bits[] = 'No Onion';
                                        }
                                        if (isset($item->garlic_flag) && (string) $item->garlic_flag === '0') {
                                            $allergy_bits[] = 'No Garlic';
                                        }
                                        // Custom allergies if persisted in a dedicated column
                                        if (!empty($item->custom_allergies_text)) {
                                            $allergy_bits[] = 'Custom: ' . $item->custom_allergies_text;
                                        }
                                        // Fallback: if custom allergies were appended to special_instructions earlier, they will still be visible there
                                        if (!empty($allergy_bits)): ?>
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                                    <?= htmlspecialchars(implode(' | ', $allergy_bits)) ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <!-- <div id="order-summary-empty" class="text-center text-muted <?= !empty($order_items) ? 'd-none' : '' ?>">
                            <i class="bi bi-cart-x fs-1"></i>
                            <p>No items added yet</p>
                        </div> -->
                    </div>
                    <!-- <div class="card-footer">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-finalize-bill" onclick="finalizeOrder(true)">
                                <i class="bi bi-check-circle"></i> Finalize 
                            </button>
                            <a class="btn btn-outline-primary"
                                href="<?= site_url('Restaurant_Order_Taking/order_bill/' . $order->id); ?>">
                                <i class="bi bi-receipt"></i> Order Bill
                            </a>
                            <a class="btn btn-outline-dark" target="_blank"
                                href="<?= base_url('Restaurant_Order_Taking/kot/') . $order->id ?>?size=58">
                                <i class="bi bi-printer"></i> Print KOT
                            </a>
                            <button class="btn btn-outline-danger" data-action="close-table">
                                <i class="bi bi-door-closed"></i> Close Table
                            </button> 
                            <button class="btn btn-success" onclick="completeAndFree(true)">
                                <i class="bi bi-cash"></i> Complete & Free Table
                            </button>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>

        <!-- Mobile Bottom Bar -->
        <!-- <div class="mobile-bottom-bar d-flex justify-content-between align-items-center">
            <button class="btn btn-cart" onclick="toggleCart()">
                <i class="bi bi-cart"></i>
                <span>Cart</span>
                <span class="cart-count-bubble"><span id="cart-count-mobile"><?= $order_items_count ?></span></span>
            </button>
            <a class="btn btn-outline-primary"
                href="<?= site_url('Restaurant_Order_Taking/order_bill/' . $order->id); ?>"><i
                    class="bi bi-receipt"></i></a>
            <a class="btn btn-outline-secondary"
                href="<?= base_url('Restaurant_Order_Taking/kot/') . $order->id ?>?size=58"><i
                    class="bi bi-printer"></i></a>
            <button class="btn btn-primary btn-finalize-bill" onclick="finalizeOrder(true)"><i class="bi bi-check-circle"></i> Finalize</button>
        </div> -->
    </div>

    <!-- Offcanvas Cart Drawer (for tablets/mobiles) -->
    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasCartLabel"><i class="bi bi-cart"></i> Order Summary</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body cart-offcanvas-body">
            <div id="offcanvas-cart-container"></div>
        </div>
        <div class="offcanvas-footer cart-offcanvas-footer">
            <div class="d-grid gap-2 cart-offcanvas-actions">
                <button class="btn btn-primary btn-finalize-bill" onclick="finalizeOrder(true)">
                    <i class="bi bi-check-circle"></i> Finalize & Bill
                </button>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title w-100 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span id="modal-product-name" class="fw-semibold"></span>
                            <i class="bi bi-info-circle text-primary" style="cursor: pointer; font-size: 18px;"
                                onclick="showProductDetails()" title="View product details"></i>
                        </div>
                        <span class="text-muted small">Guest: <span id="modal-guest-label">All Guests</span></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"> 
                                   
                    <form id="add-item-form">
                        <input type="hidden" id="selected-product-id">
                        <!-- Keep price element for calc but hide visually -->
                        <p id="selected-product-price" class="d-none" aria-hidden="true"></p>
                        <!-- Hidden quantity (controlled by footer stepper) -->
                        <input type="hidden" id="item-quantity" value="1">

                        <!-- Toppings section (first) -->
                        <div class="section-card">
                            <div class="section-header">Toppings</div>
                            <div id="toppings-container" class="option-list"></div>
                            <input type="hidden" id="item-toppings" value="">
                        </div>

                        <!-- Add Ons section (second) -->
                        <div class="section-card">
                            <div class="section-header">Add Ons</div>
                            <div id="add-ons-container" class="option-list"></div>
                            <input type="hidden" id="item-addons" value="">
                        </div>

                        <!-- Additional cooking instructions card -->
                        <div class="section-card">
                            <div class="section-header">Additional cooking instructions</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Spice Level</label>
                                    <div class="spice-level">
                                        <span class="spice-btn active" data-spice="mild" onclick="selectSpice(this)">😋
                                            Mild</span>
                                        <span class="spice-btn" data-spice="medium" onclick="selectSpice(this)">🌶️
                                            Medium</span>
                                        <span class="spice-btn" data-spice="hot" onclick="selectSpice(this)">🔥
                                            Hot</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Meat Preparation</label>
                                    <select class="form-select" id="item-meat-wellness">
                                        <option value="">Not Applicable</option>
                                        <?php if (!empty($meat_wellness)): ?>
                                            <?php foreach ($meat_wellness as $wellness): ?>
                                                <option value="<?= $wellness->id ?>"><?= htmlspecialchars($wellness->type) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label d-block">Allergies</label>
                                    <div id="allergies-list">
                                        <!-- rendered dynamically -->
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <input type="text" id="custom-allergy-input"
                                            class="form-control form-control-sm"
                                            placeholder="Add allergy and press Enter" style="max-width: 260px;">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="addCustomAllergy()">+</button>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="form-check allergy-option">
                                                <input class="form-check-input allergy-checkbox" type="checkbox"
                                                    id="no-onion" value="no_onion">
                                                <label class="form-check-label" for="no-onion">No Onion</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check allergy-option">
                                                <input class="form-check-input allergy-checkbox" type="checkbox"
                                                    id="no-garlic" value="no_garlic">
                                                <label class="form-check-label" for="no-garlic">No Garlic</label>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="item-allergies" value="">
                                    <input type="hidden" id="item-custom-allergies" value="">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Special Instructions</label>
                                    <textarea class="form-control" id="item-instructions" rows="2"
                                        placeholder="E.g. extra crispy, pack separately..."></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="w-100 d-flex align-items-center justify-content-between gap-2">
                        <div class="btn-group footer-qty" role="group" aria-label="Quantity Stepper">
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustQty(-1)">-</button>
                            <input id="qty-display" type="text" class="form-control text-center" value="1"
                                style="width:70px" readonly>
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustQty(1)">+</button>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="add-to-order-btn" class="btn btn-brand btn-lg px-4 flex-fill"
                                onclick="addItemToOrder()">
                                <i class="bi bi-plus"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Modal -->
    <div class="modal fade" id="productDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle text-primary"></i> Product Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="row justify-content-center align-items-center g-3">
                        <div class="col-lg-5 col-md-6 text-center">
                            <img id="product-detail-image" src="" alt="Product Image"
                                class="img-fluid rounded shadow-lg"
                                style="width: 100%; max-width: 320px; height: 220px; object-fit: cover; border: 3px solid #e9ecef;">
                        </div>
                        <div class="col-lg-7 col-md-6">
                            <h5 id="product-detail-name" class="mb-2"></h5>
                            <div class="row g-2 small">
                                <div class="col-6">
                                    <div class="text-muted">Product Code</div>
                                    <div id="product-detail-code" class="fw-semibold"></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted">Category</div>
                                    <div id="product-detail-category" class="fw-semibold"></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted">Price</div>
                                    <div id="product-detail-price" class="fw-bold text-primary"></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted">Meal Type</div>
                                    <div id="product-detail-meal-type" class="fw-semibold"></div>
                                </div>
                                <div class="col-12 mt-1">
                                    <div class="text-muted">Description</div>
                                    <div id="product-detail-description"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Note: core.js is intentionally not included on this page to avoid plugin dependency errors -->
    <!-- Inline order screen logic implemented below -->
    

</body>
<script>
    /* Restaurant Order Screen - extracted scripts */
(function(window, $){
  'use strict';

  // Ensure namespace
  window.site = window.site || {};
  window.site.vars = window.site.vars || {};
  window.site.settings = window.site.settings || {};

  // Utils
  function debounce(fn, wait){
    var t; return function(){
      var ctx = this, args = arguments; clearTimeout(t);
      t = setTimeout(function(){ fn.apply(ctx, args); }, wait);
    };
  }

  // Read base_url from existing global or infer from document
  function getBaseUrl(){
    if (window.site && window.site.base_url) return window.site.base_url.replace(/\/?$/, '/');
    var base = $('base').attr('href') || '/';
    return base.replace(/\/?$/, '/');
  }

  // CSRF helpers (expects meta tags to be present in the HTML)
  function getCookie(name){
    var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : '';
  }

  function getCsrf(){
    var name = $('meta[name="ci-csrf-name"]').attr('content') || 'token';
    var hash = $('meta[name="ci-csrf-hash"]').attr('content') || '';
    if (!hash) {
      // Fallback to cookie if meta missing/empty
      var fromCookie = getCookie('token_cookie');
      if (fromCookie) hash = fromCookie;
    }
    return { name: name, hash: hash };
  }

  // Build a safe image URL from a filename or absolute URL, with default no_image fallback
  function resolveImageUrl(img){
    var ca = (window.site && window.site.customer_assets) ? window.site.customer_assets : 'localhost';
    var defaultNoImg = getBaseUrl() + 'assets/mdata/' + ca + '/uploads/thumbs/no_image.png';
    if (!img) return defaultNoImg;
    var s = img.toString().trim();
    // Absolute URL
    if (/^(?:https?:)?\/\//i.test(s)) return s;
    // Normalize leading slash
    if (s.charAt(0) === '/') { s = s.replace(/^\/+/, ''); }
    // Already an assets path
    if (/^assets\//i.test(s)) { return getBaseUrl() + s; }
    // Assume customer mdata thumbs path for product images
    return getBaseUrl() + 'assets/mdata/' + ca + '/uploads/thumbs/' + s;
  }

  function setupAjaxCsrf(){
    var csrf = getCsrf();
    if (!csrf.name || !csrf.hash) return; // graceful if not provided
    $.ajaxSetup({
      beforeSend: function(xhr, settings){
        if ((settings.type || '').toUpperCase() === 'POST'){
          if (typeof settings.data === 'string') {
            settings.data += (settings.data ? '&' : '') +
              encodeURIComponent(csrf.name) + '=' + encodeURIComponent(csrf.hash);
          } else if (settings.data && typeof settings.data === 'object') {
            settings.data[csrf.name] = csrf.hash;
          } else {
            settings.data = encodeURIComponent(csrf.name) + '=' + encodeURIComponent(csrf.hash);
          }
        }
      }
    });
  }

  // Set the X-Requested-With header for all AJAX requests
  $.ajaxSetup({
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  });

  // Lightweight AJAX helpers consistent with CI + CSRF setup
  function ajaxPost(url, data){
    try {
      var csrf = (typeof getCsrf === 'function') ? getCsrf() : { name: 'token', hash: '' };
      var payload = (data && typeof data === 'object') ? $.extend({}, data) : (data || {});
      if (csrf && csrf.name && csrf.hash) { payload[csrf.name] = csrf.hash; }
      return $.ajax({
        url: getBaseUrl() + url,
        type: 'POST',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: payload
      });
    } catch(err){
      return $.Deferred().reject(err).promise();
    }
  }

  function ajaxGet(url, params){
    return $.ajax({
      url: getBaseUrl() + url,
      type: 'GET',
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: params || {}
    });
  }

  function handleAjaxFail(jqXHR, defaultMsg){
    if (jqXHR && jqXHR.responseText && jqXHR.responseText.trim().startsWith('<!DOCTYPE')){
      showAlert('error', 'Server returned an error page. Please check console.');
      console.error('HTML error response:', jqXHR.responseText);
    } else {
      showAlert('error', defaultMsg || 'Network/Server error');
      console.error('AJAX fail:', jqXHR);
    }
  }

  // Optionally update CSRF meta from JSON responses that include fresh tokens
  $(document).ajaxSuccess(function(event, xhr, settings){
    try {
      var ct = (xhr.getResponseHeader('Content-Type') || '').toLowerCase();
      if (ct.indexOf('application/json') === -1) return;
      var data = JSON.parse(xhr.responseText);
      var nameMeta = $('meta[name="ci-csrf-name"]');
      var hashMeta = $('meta[name="ci-csrf-hash"]');
      if (data && nameMeta.length && hashMeta.length) {
        var n = nameMeta.attr('content') || 'token';
        if (data[n]) { hashMeta.attr('content', data[n]); }
      }
    } catch(e) { /* ignore */ }
  });

  // Global AJAX error handler to catch non-JSON responses
  $(document).ajaxError(function(event, jqXHR, settings, thrownError) {
    if (jqXHR.responseText && jqXHR.responseText.trim().startsWith('<!DOCTYPE')) {
      // This is likely an HTML error page
      showAlert('error', 'Server returned an error page. Please check the console for details.');
      console.error('AJAX Error: Server returned HTML instead of JSON. URL: ' + settings.url + '\nResponse: ' + jqXHR.responseText.substring(0, 500));
    }
  });

  // Bootstrap from DOM attributes
  function bootstrapFromDom(){
    var $root = $(document.body);
    var oid = $root.data('order-id');
    if (oid && !window.site.vars.orderId) { window.site.vars.orderId = oid; }
  }

  // UI helpers
  function showAlert(type, message){
    // Prefer Bootstrap toast/alert if present
    if (window.toastr && toastr[type]) { toastr[type](message); return; }
    console[(type === 'error' ? 'error' : 'log')](message);
  }
  function showPopup(message, type){ showAlert(type === 'error' ? 'error' : 'info', message); }

  // State — initialise from URL ?guest_id= param if present
  var _urlParams = new URLSearchParams(window.location.search);
  var _urlGuestId = _urlParams.get('guest_id');
  var selectedGuestId = (_urlGuestId && _urlGuestId !== 'null' && _urlGuestId !== 'all') ? _urlGuestId : '1';
  var selectedCategoryId = 'all';
  var selectedSpiceLevel = 'medium';
  var activeFilters = { 'meal-type': [], 'allergy': [], 'spice-level': [] };

  // iCheck init (if available)
  function initICheck(){
    if (typeof $.fn.iCheck === 'function') {
      $('input[type="checkbox"].icheck, input[type="radio"].icheck').not('.icheckbox_minimal, .iradio_minimal').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%'
      });
    }
  }

  // Event handlers
  function initEventHandlers(){
    // Inline handlers are used for guest tabs, categories, and search input

    // Filter badges & pills
    $(document).off('click.order.filterbadge', '.filter-badge, .filter-pill').on('click.order.filterbadge', '.filter-badge, .filter-pill', function(){
      var filterType = $(this).data('filter');
      var filterValue = $(this).data('value');
      $(this).toggleClass('active');
      if ($(this).hasClass('active')) {
        if (activeFilters[filterType].indexOf(filterValue) === -1) activeFilters[filterType].push(filterValue);
      } else {
        activeFilters[filterType] = activeFilters[filterType].filter(function(v){ return v !== filterValue; });
      }
      loadMenuItems();
    });

    // Inline oninput handles search updates

    // Custom allergy Enter to add
    $(document).off('keydown.order.allergy', '#custom-allergy-input').on('keydown.order.allergy', '#custom-allergy-input', function(e){
      if (e.key === 'Enter') { e.preventDefault(); addCustomAllergy(); }
    });

    // Offcanvas toggle button alias
    $(document).off('click.order.carttoggle', '.cart-toggle').on('click.order.carttoggle', '.cart-toggle', function(e){ e.preventDefault(); toggleCart(); });

    // Generic data-action handlers (no inline onclicks in HTML)
    $(document).off('click.order.actions', '[data-action]').on('click.order.actions', '[data-action]', function(e){
      var action = $(this).data('action');
      if (action === 'increase-guest') { e.preventDefault(); return increaseGuest(); }
      if (action === 'decrease-guest') { e.preventDefault(); return decreaseGuest(); }
      if (action === 'toggle-cart') { e.preventDefault(); return toggleCart(); }
      if (action === 'finalize-order') { e.preventDefault(); return (typeof completeAndFree === 'function' ? completeAndFree(true) : (window.finalizeOrder && window.finalizeOrder(true))); }
      if (action === 'close-table') { e.preventDefault(); return (typeof closeTable === 'function' ? closeTable() : undefined); }
      if (action === 'complete-free') { e.preventDefault(); return (typeof completeAndFree === 'function' ? completeAndFree() : undefined); }
    });

    // Re-init after AJAX loads
    $(document).ajaxComplete(function(){ initICheck(); });
  }

  // Public functions expected by HTML
  function toggleCart(){
    var el = document.getElementById('offcanvasCart');
    if (!el) return;
    var off = new bootstrap.Offcanvas(el);
    off.show();
    var $container = $('#offcanvas-cart-container');
    if ($container.length) $container.html($('#order-items-container').html());
  }

  function backToTables(){ 
    var tid = '<?= isset($order->res_tables_id) ? $order->res_tables_id : "" ?>';
    var guestId = new URLSearchParams(window.location.search).get('guest_id') || 'all';
    window.location.href = getBaseUrl() + 'Restaurant_Order_Taking/orders' + (tid ? '/' + tid : '') + '?guest_id=' + guestId;
  }

  function increaseGuest(){
    $.ajax({
      url: getBaseUrl() + 'Restaurant_Order_Taking/increase_guest',
      type: 'POST', data: { order_id: window.site.vars.orderId }, dataType: 'json'
    }).done(function(resp){ if (resp && resp.status === 'success') location.reload(); else showAlert('error', (resp && resp.message) || 'Failed to add guest'); })
      .fail(function(){ showAlert('error', 'Error adding guest'); });
  }

  function decreaseGuest(){
    if (!confirm('Are you sure you want to remove a guest? This cannot be undone.')) return;
    $.ajax({
      url: getBaseUrl() + 'Restaurant_Order_Taking/decrease_guest',
      type: 'POST', data: { order_id: window.site.vars.orderId }, dataType: 'json'
    }).done(function(resp){ if (resp && resp.status === 'success') location.reload(); else showAlert('error', (resp && resp.message) || 'Cannot remove guest with items'); })
      .fail(function(){ showAlert('error', 'Error removing guest'); });
  }

  function updateQuantity(itemId, change){
    var d = (window.site.settings && (window.site.settings.qty_decimals || window.site.settings.qty_decimals === 0)) ? parseInt(window.site.settings.qty_decimals) : 0;
    var step = d > 0 ? 1 / Math.pow(10, d) : 1;
    // Try to get quantity from data model first (more reliable if DOM hidden)
    var currentQty = 1;
    if (window.serverOrderItems) {
        var found = window.serverOrderItems.find(function(i){ return String(i.id) === String(itemId); });
        if (found) { currentQty = parseFloat(found.quantity); }
    } else {
        // Fallback to DOM
        currentQty = parseFloat($(".order-item[data-item-id="+itemId+"] .quantity-control span").text());
    }
    if (isNaN(currentQty)) currentQty = 1;

    var newQty = currentQty + (change * step);
    if (newQty <= 0) { removeItem(itemId); return; }
    newQty = parseFloat(newQty.toFixed(d));
    if (typeof ajaxPost === 'function') {
      ajaxPost('Restaurant_Order_Taking/update_item', { item_id: itemId, quantity: newQty })
        .done(function(resp){ if (resp && resp.status === 'success') location.reload(); else showPopup((resp && resp.message) || 'Failed to update quantity', 'error'); })
        .fail(function(){ showPopup('Network error while updating quantity', 'error'); });
    }
  }

  function removeItem(itemId){
    if (!confirm('Are you sure you want to remove this item?')) return;
    if (typeof ajaxPost === 'function') {
      ajaxPost('Restaurant_Order_Taking/delete_item', { item_id: itemId })
        .done(function(resp){ if (resp && resp.status === 'success') { location.reload(); } else { showPopup((resp && resp.message) || 'Failed to remove item', 'error'); } })
        .fail(function(){ showPopup('Network error while removing item', 'error'); });
    }
  }

  function selectGuest(guestId){
    selectedGuestId = guestId;
    $('.guest-tab').removeClass('active');
    $('.guest-tab[data-guest-id="'+guestId+'"]').addClass('active');
    var $tab = $('.guest-tab[data-guest-id="'+guestId+'"]').first();
    var label = (guestId === 'all') ? 'All Guests' : ('Guest ' + ($tab.data('guest-number')));
    $('#apply-to-label, #footer-guest-label, #modal-guest-label').text(label);
    loadMenuItems();
    filterOrderSummaryItems();
  }

  function selectCategory(categoryId){
    selectedCategoryId = categoryId;
    $('.menu-category').removeClass('active');
    $('.menu-category[data-category-id="'+categoryId+'"]').addClass('active');
    loadMenuItems();
  }

  function toggleFilter(el){ $(el).toggleClass('active'); loadMenuItems(); }

  // Open modal with product data on + click
  function openQuickAdd(productId){
    try { productId = parseInt(productId, 10); } catch(e) {}
    if (!productId || isNaN(productId)) { showAlert('error', 'Invalid product'); return; }

    // Reset modal state
    $('#selected-product-id').val(productId);
    
    // Pull basic info from the card
    // Pull basic info from the card
    var $card = $('.menu-item[data-product-id="'+productId+'"]');
    if (!$card.length) { $card = $('.menu-item-new[data-product-id="'+productId+'"]'); }

    // Remember the card for product details modal
    window.site.vars.lastProductCard = $card;
    
    // Scrape data (support both new and old UI)
    var name = $card.find('.item-title').text().trim() || $card.find('h6').first().text().trim();
    var priceText = $card.find('.item-price').text().trim() || $card.find('.fw-bold').first().text().trim();
    var imgSrc = $card.find('img').attr('src') || '';

    // Set the visible product name in modal header
    $('#modal-product-name').text(name || '');
    
    // Set guest label in modal header
    var guestLabel = 'All Guests';
    if (typeof selectedGuestId !== 'undefined' && selectedGuestId !== 'all') {
         var $tab = $('.guest-tab[data-guest-id="' + selectedGuestId + '"]');
         if ($tab.length && $tab.data('guest-number')) {
             guestLabel = 'Guest ' + $tab.data('guest-number');
         } else {
             // Fallback
             guestLabel = 'Guest ' + selectedGuestId;
         }
    }
    $('#modal-guest-label').text(guestLabel);

    // Show modal container first
    var el = document.getElementById('addItemModal');
    if (!el) { showAlert('error', 'Add Item modal not found'); return; }
    
    // Show loading state in modal body
    $('#addItemModal .modal-body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading customizations...</p></div>');
    // Hide default footer since the view provides its own or we re-enable it by ensuring the view includes it or we hide the static one
    $('#addItemModal .modal-footer').hide(); 

    var modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.show();

    // Fetch HTML view from server
    $.ajax({
      url: getBaseUrl() + 'Restaurant_Order_Taking/get_product_customizations',
      type: 'POST', dataType: 'json', data: { product_id: productId }
    }).done(function(resp){
      if (resp && resp.status === 'success' && resp.html) {
          // Inject HTML
          $('#addItemModal .modal-body').html(resp.html);
          
          // Parse price from extracted text and init modal logic
          var cleanPrice = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
          if(typeof window.setCustBasePrice === 'function') {
              window.setCustBasePrice(cleanPrice);
          }
      } else {
          $('#addItemModal .modal-body').html('<div class="alert alert-danger">Failed to load customizations.</div>');
      }
    }).fail(function(){
        $('#addItemModal .modal-body').html('<div class="alert alert-danger">Network error.</div>');
    });
  }

  // Helper: Find visible cart items for a product using data source
  function getVisibleCartItemInfo(productId) {
    var qty = 0;
    var ids = [];
    var items = window.serverOrderItems || [];
    var currentGuestId = (typeof selectedGuestId === 'undefined' || selectedGuestId === null) ? 'all' : selectedGuestId;
    var targetPid = String(productId);

    for (var i = 0; i < items.length; i++) {
        var item = items[i];
        
        // Robust ID check: support product_id, sma_product_id, or even just id if ambiguous
        var pId = item.product_id || item.sma_product_id || item.id || 0;
        if (String(pId) !== targetPid) continue;

        // Guest match logic
        var itemGuestId = item.res_orders_guests_id || item.guest_id || 'all';
        if (itemGuestId === '0') itemGuestId = 'all'; 

        var curGuestStr = String(currentGuestId);
        var itemGuestStr = String(itemGuestId);

        var matches = (curGuestStr === 'all') || (itemGuestStr === 'all') || (itemGuestStr === curGuestStr);
        
        if (matches) {
            qty += parseFloat(item.quantity || 0);
            ids.push(item.id);
        }
    }
    return { qty: qty, ids: ids };
  }

  // Decrease quantity from menu
  function decreaseFromMenu(productId){
    var info = getVisibleCartItemInfo(productId);
    if (info.ids.length === 0) return;
    if (info.ids.length === 1) {
        updateQuantity(info.ids[0], -1);
    } else {
        showAlert('info', 'Multiple variations in cart. Please adjust in cart.');
        toggleCart();
    }
  }

  // Increase quantity from menu
  function increaseFromMenu(productId){
    var info = getVisibleCartItemInfo(productId);
    if (info.ids.length === 1) {
        updateQuantity(info.ids[0], 1);
    } else {
        openQuickAdd(productId);
    }
  }

  // Toggle spice selection
  function selectSpice(el){
    try {
      var $el = $(el);
      $el.closest('.spice-level').find('.spice-btn').removeClass('active');
      $el.addClass('active');
    } catch(e) {}
  }
  // Expose for inline onclick handlers
  window.selectSpice = selectSpice;

  // Cycle Guest Counter
  function cycleGuest(delta){
    var $input = $('#selected-guest-num');
    var current = parseInt($input.val() || '1');
    var max = 50; // Reasonable limit
    var next = current + delta;
    if (next < 1) next = 1;
    if (next > max) next = max;
    
    $input.val(next);
    $('#active-guest-display').text(next);
    selectedGuestId = next; 
    var $tab = $('.guest-tab.active');
    if ($tab.length) {
        $tab.data('guest-number', next);
        $tab.data('guest-id', next); 
        $tab.attr('data-guest-number', next);
        $tab.attr('data-guest-id', next);
        $tab.html('<i class="bi bi-person"></i> Guest ' + next);
    }
  }
  window.cycleGuest = cycleGuest;

  // Render chips for toppings/add-ons
  function renderOptionChips(containerSelector, items, kind){
    var $c = $(containerSelector);
    if (!$c.length) return;
    var html = [];
    $.each(items, function(_, it){
      var id = it.id || it["id"]; // tolerate different shapes
      var nameRaw = it.name || it.title || it.label || it.text || '';
      var name = (nameRaw == null ? '' : String(nameRaw));
      var price = (typeof it.price !== 'undefined') ? it.price : (typeof it.amount !== 'undefined' ? it.amount : null);
      var priceTxt = price !== null ? ' <small class="text-muted">(' + (window.sma && sma.formatMoney ? sma.formatMoney(price) : parseFloat(price).toFixed(2)) + ')</small>' : '';
      html.push('<span class="chip" tabindex="0" role="checkbox" aria-checked="false" data-id="'+ id +'" data-kind="'+ kind +'">'+ name + priceTxt +'</span>');
    });
    $c.html(html.join(''));
    // Click/keyboard toggle
    $c.off('click.option', '.chip').on('click.option', '.chip', function(){
      $(this).toggleClass('active');
      syncOptionHiddenInputs();
    });
    $c.off('keydown.option', '.chip').on('keydown.option', '.chip', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $(this).trigger('click'); } });
  }

  // Render preset allergy chips (same UI as toppings chips)
  function renderAllergyChips(containerSelector, items){
    var $c = $(containerSelector);
    if (!$c.length) return;
    var html = [];
    $.each(items, function(_, it){
      var id = it.id || it["id"]; 
      var nameRaw = it.name || it.title || it.label || it.text || '';
      var name = (nameRaw == null ? '' : String(nameRaw));
      html.push('<span class="chip allergy-chip" tabindex="0" role="checkbox" aria-checked="false" data-id="'+ id+'">'+ name +'</span>');
    });
    $c.html(html.join(''));
    $c.off('click.allergy', '.allergy-chip').on('click.allergy', '.allergy-chip', function(e){
      e.preventDefault();
      var $chip = $(this);
      var nowActive = !$chip.hasClass('active');
      $chip.toggleClass('active', nowActive).attr('aria-checked', nowActive ? 'true' : 'false');
      syncPresetAllergies();
    });
    $c.off('keydown.allergy', '.allergy-chip').on('keydown.allergy', '.allergy-chip', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $(this).trigger('click'); } });
  }

  // Sync hidden inputs for options
  function syncOptionHiddenInputs(){
    var toppingIds = [];
    $('#toppings-container .chip.active').each(function(){ toppingIds.push($(this).data('id')); });
    $('#item-toppings').val(toppingIds.join(','));
    var addOnIds = [];
    $('#add-ons-container .chip.active').each(function(){ addOnIds.push($(this).data('id')); });
    $('#item-addons').val(addOnIds.join(','));
  }

  // Sync preset allergies into hidden input
  function syncPresetAllergies(){
    var ids = [];
    $('#allergies-list .allergy-chip.active').each(function(){
      var id = $(this).data('id');
      if (id != null && id !== '' && !$(this).data('custom')) { ids.push(id); }
    });
    $('#item-allergies').val(ids.join(','));
  }

  // Add a custom allergy: create chip, update hidden, and persist to master (optional)
  function addCustomAllergy(){
    var name = ($.trim($('#custom-allergy-input').val() || ''));
    if (!name) return;
    var $cont = $('#custom-allergies-container');
    var slug = name.toLowerCase().replace(/\s+/g, '-');
    // Avoid duplicates by text
    var exists = false; $cont.find('.allergy-chip').each(function(){ if ($.trim($(this).text().toLowerCase()) === name.toLowerCase()) { exists = true; } });
    if (exists) { $('#custom-allergy-input').val(''); showAlert('info', 'Allergy already added'); return; }
    var $chip = $('<span class="chip allergy-chip active" data-name="'+ $('<div>').text(name).html() +'">'+ name +'</span>');
    $cont.append($chip);
    // Update hidden
    syncCustomAllergies();
    $('#custom-allergy-input').val('');
    // Also reflect in preset allergy list visually (not counted in preset ids)
    try {
      var $alist = $('#allergies-list');
      if ($alist.length){
        var html = '<span class="chip allergy-chip active" role="checkbox" aria-checked="true" data-custom="1">'
          + $('<div>').text(name).html()
          + '</span>';
        $alist.append(html);
      }
    } catch(e) {}
    // Try to persist to master asynchronously (non-blocking) with CSRF
    if (typeof ajaxPost === 'function') {
      ajaxPost('Restaurant_Order_Taking/add_allergy', { name: name })
        .done(function(resp){ if (resp && resp.message) { /* optionally show */ } })
        .fail(function(){ /* ignore */ });
    }
    showAlert('success', 'Allergy added');
  }

  // Open Product Details modal and populate content
  function showProductDetails(){
    var $card = window.site.vars.lastProductCard || $();
    var pid = parseInt($('#selected-product-id').val() || ($card.data('product-id') || 0), 10) || 0;
    var name = $card.find('h6').first().text().trim() || $('#modal-product-name').text().trim();
    var priceText = $card.find('.fw-bold').first().text().trim();
    var imgSrc = $card.find('img').attr('src') || '';

    // Set immediate known fields
    $('#product-detail-name').text(name || '');
    $('#product-detail-price').text(priceText || '');
    $('#product-detail-image').attr('src', imgSrc || resolveImageUrl(''));
    $('#product-detail-code').text('');
    $('#product-detail-category').text('');
    $('#product-detail-meal-type').text('');
    $('#product-detail-description').text('');

    if (pid) {
      $.ajax({
        url: getBaseUrl() + 'Restaurant_Order_Taking/get_product_details',
        type: 'POST',
        dataType: 'json',
        data: { product_id: pid }
      }).done(function(resp){
        if (resp && resp.status === 'success' && resp.data){
          var d = resp.data;

          console.log(d); // ✅ debug once
          // ✔ Correct key usage
          if (d.product_name) $('#product-detail-name').text(d.product_name);
          if (d.price) $('#product-detail-price').text('₹ ' + d.price);
          if (d.image) $('#product-detail-image').attr('src', resolveImageUrl(d.image));

          if (d.product_id) $('#product-detail-code').text(d.product_id);
          if (d.category_name) $('#product-detail-category').text(d.category_name);
          if (d.meal_type_name) $('#product-detail-meal-type').text(d.meal_type_name);
          if (d.description) $('#product-detail-description').text(d.description);
        }

      }).always(function(){
        var mdl = bootstrap.Modal.getOrCreateInstance(
          document.getElementById('productDetailsModal')
        );
        mdl.show();
      });

    } else {
      var mdl = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('productDetailsModal')
      );
      mdl.show();
    }
  }
  function syncCustomAllergies(){
    var names = [];
    $('#custom-allergies-container .allergy-chip').each(function(){ names.push($.trim($(this).text())); });
    $('#item-custom-allergies').val(names.join('|'));
  }

  // Quantity stepper for modal
  function adjustQty(delta){
    var d = (window.site.settings && (window.site.settings.qty_decimals || window.site.settings.qty_decimals === 0)) ? parseInt(window.site.settings.qty_decimals) : 0;
    var step = d > 0 ? 1 / Math.pow(10, d) : 1;
    var $hidden = $('#item-quantity');
    var $display = $('#qty-display');
    var current = parseFloat($hidden.val() || '1');
    if (isNaN(current)) current = 1;
    var next = current + (delta * step);
    if (next < step) next = step; // min 1 or min step
    next = parseFloat(next.toFixed(d));
    $hidden.val(String(next));
    if ($display.is('input')) $display.val(String(next)); else $display.text(String(next));
  }

  // Confirm from modal and add to order
  function addItemToOrder(){
    // Support both old and new ID schemes if needed, or just switch to new
    var productId = parseInt($('#cust-product-id').val() || $('#selected-product-id').val(), 10) || 0;
    if (!productId) { showAlert('error', 'No product selected'); return; }
    var qty = parseFloat($('#cust-qty').val() || $('#item-quantity').val() || '1');
    if (!qty || isNaN(qty)) qty = 1;

    var orderId = (window.site && window.site.vars && window.site.vars.orderId) ? window.site.vars.orderId : '';
    if (!orderId) { showAlert('error', 'Missing order id'); return; }

    // Determine guest id
    var guestId = (typeof selectedGuestId !== 'undefined' && selectedGuestId !== null && selectedGuestId !== '') ? selectedGuestId : 'all';

    // Find the button (could be in the loaded view)
    var $btn = $('.btn-add-item');
    if(!$btn.length) $btn = $('#add-to-order-btn');
    
    var originalText = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Adding...');

    // Collect data from new inputs
    // Note: The new view updates hidden inputs on click, so we just read them.
    
    var payload = {
      order_id: orderId,
      guest_id: guestId,
      product_id: productId,
      quantity: qty,
      spice_level: ($('#cust-spice').val() || ''),
      meat_wellness: ($('#cust-meat-wellness').val() || ''),
      allergies: ($('#cust-allergies').val() || ''),
      custom_allergies: ($('#cust-custom-allergies').val() || ''),
      add_ons: ($('#cust-addons').val() || ''),
      toppings: ($('#cust-toppings').val() || ''),
      onion_flag: ($('#cust-onion').is(':checked') ? 1 : ($('#onion_flag').is(':checked')?1:0)), 
      garlic_flag: ($('#cust-garlic').is(':checked') ? 1 : ($('#garlic_flag').is(':checked')?1:0)),
      special_instructions: ($('#cust-instructions').val() || $('#item-instructions').val() || '')
    };
    
    // Also support fallback to old IDs if new ones are empty (e.g. if we revert)
    // But for now assuming new view is active.

    // Decide create vs update
    var isEdit = !!window.editingItemId;
    var urlPath = isEdit ? 'Restaurant_Order_Taking/update_item' : 'Restaurant_Order_Taking/add_item';
    if (isEdit) { payload.item_id = window.editingItemId; }

    $.ajax({
      url: getBaseUrl() + urlPath,
      type: 'POST', dataType: 'json', data: payload
    }).done(function(resp){
      if (resp && (resp.status === 'success' || resp.success)){
        showAlert('success', isEdit ? 'Item updated' : 'Item added to order');
        try { $('#addItemModal').modal('hide'); } catch(e) {}
        try { var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addItemModal')); modal.hide(); } catch(e) {}
        location.reload();
      } else {
        showAlert('error', (resp && (resp.message || resp.error)) || 'Failed');
      }
    }).fail(function(xhr){ handleAjaxFail(xhr, 'Failed to submit'); })
      .always(function(){ $btn.prop('disabled', false).html(originalText); });
  }

  // Open modal prefilled to edit an existing order item
  function openEditItemModal(itemId){
    try { itemId = parseInt(itemId, 10); } catch(e) {}
    var $row = $('.order-item[data-item-id="'+itemId+'"]');
    if (!$row.length) { showAlert('error', 'Item not found'); return; }

    // Set editing mode
    window.editingItemId = itemId;
    $('#add-to-order-btn').html('<i class="bi bi-check"></i> Update Item');

    // Basic fields
    var productId = parseInt($row.data('product-id') || 0, 10) || 0;
    var name = ($row.data('product-name') || '').toString();
    var qty = parseFloat($row.data('quantity') || $row.find('.quantity-control span').text() || '1');
    if (isNaN(qty) || qty <= 0) qty = 1;
    $('#selected-product-id').val(productId);
    $('#modal-product-name').text(name);
    $('#item-quantity').val(qty);
    $('#qty-display').val(qty);

    // Flags and selections from data- attributes
    var toppingIds = ($row.data('topping-ids') || '').toString();
    var addOnIds = ($row.data('addon-ids') || '').toString();
    var spice = ($row.data('spice-level') || '').toString();
    var meatWell = parseInt($row.data('meat-wellness-id') || 0, 10) || 0;
    var onion = parseInt($row.data('onion-flag') || 1, 10) === 0 ? 1 : 0; // 0 means No Onion flag set
    var garlic = parseInt($row.data('garlic-flag') || 1, 10) === 0 ? 1 : 0; // 0 means No Garlic flag set
    var instr = ($row.data('instructions') || '').toString();

    // Reset modal and load customizations for this product, then apply preselects
    var el = document.getElementById('addItemModal');
    var modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.show();

    $.ajax({
      url: getBaseUrl() + 'Restaurant_Order_Taking/get_product_customizations',
      type: 'POST', dataType: 'json', data: { product_id: productId }
    }).done(function(resp){
      if (!resp || resp.status !== 'success') { return; }
      // Render sections
      try { renderOptionChips('#toppings-container', resp.toppings || [], 'topping'); } catch(e) {}
      try { renderOptionChips('#add-ons-container', resp.add_ons || [], 'add_on'); } catch(e) {}
      try { renderAllergyChips('#allergies-list', resp.allergies || []); } catch(e) {}

      // Apply pre-selections
      var tSet = (toppingIds ? toppingIds.split(',') : []);
      $('#toppings-container .chip').each(function(){ var id = String($(this).data('id')); if (tSet.indexOf(id) !== -1) $(this).addClass('active'); });
      var aSet = (addOnIds ? addOnIds.split(',') : []);
      $('#add-ons-container .chip').each(function(){ var id = String($(this).data('id')); if (aSet.indexOf(id) !== -1) $(this).addClass('active'); });
      syncOptionHiddenInputs();

      if (spice) { $('#addItemModal .spice-btn').removeClass('active'); $('#addItemModal .spice-btn[data-spice="'+spice+'"]').addClass('active'); }
      $('#item-meat-wellness').val(meatWell);
      $('#no-onion').prop('checked', !!onion);
      $('#no-garlic').prop('checked', !!garlic);
      $('#item-instructions').val(instr);

      // Allergies preset preselect from row data-allergy-ids
      var presetAllIds = ($row.data('allergy-ids') || '').toString();
      var pSet = (presetAllIds ? presetAllIds.split(',') : []);
      $('#allergies-list .allergy-chip').each(function(){ var id = String($(this).data('id')); if (pSet.indexOf(id) !== -1) $(this).addClass('active').attr('aria-checked','true'); });
      syncPresetAllergies();
      syncCustomAllergies();
    });
  }

  function completeAndFree(confirmFirst){
    if (confirmFirst === undefined) confirmFirst = true;
    var confirmMessage = 'Are you sure you want to complete and free this table? This will finalize the order and make the table available.';
    if (confirmFirst && !confirm(confirmMessage)) return;
    // Pick the visible finalize button we just clicked (or first visible)
    var $btn = $('.btn-finalize-bill:visible').first();
    if (!$btn.length) { $btn = $('.btn-finalize-bill').first(); }
    if (window.__finalize_in_progress) { return; }
    window.__finalize_in_progress = true;
    var originalText = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Finalizing...');
    $.ajax({
      url: getBaseUrl() + 'Restaurant_Order_Taking/finalize_order',
      type: 'POST',
      data: { order_id: (window.site.vars && window.site.vars.orderId) || $(document.body).data('order-id') },
      dataType: 'json',
      timeout: 30000
    }).done(function(response){
        if (response && (response.success || response.status === 'success')){
        if (response.invoice_url) {
          window.location.href = response.invoice_url;
          return;
        }
        // Fallback if no invoice url provided
        showAlert('success', 'Order finalized.');
        try { backToTables(); } catch(e) {}
      } else {
        showAlert('error', (response && (response.message || response.error)) ? (response.message || response.error) : 'Failed to finalize order');
      }
    }).fail(function(xhr, status, error){
      showAlert('error', error || 'Request failed');
      console.error('finalize_order Error:', { status: xhr.status, statusText: xhr.statusText, response: xhr.responseText, error: error });
    }).always(function(){
      window.__finalize_in_progress = false;
      $btn.prop('disabled', false).html(originalText);
    });
  }

  // Backward-compat wrapper: existing buttons call finalizeOrder()
  function finalizeOrder(confirmFirst){
    return completeAndFree(confirmFirst);
  }

  // Load and render menu items filtered by current state
  function loadMenuItems(){
    var $container = $('#menu-items-container');
    var $loading = $('#loading-items');
    if (!$container.length) return;

    // Build filters
    var category_id = (selectedCategoryId && selectedCategoryId !== 'all') ? selectedCategoryId : '';
    var search = $('#menu-search').val() || '';
    var mealTypeCsv = (activeFilters['meal-type'] && activeFilters['meal-type'].length) ? activeFilters['meal-type'].join(',') : '';

    // UI: show loading
    $loading.show();
    $container.empty();

    $.ajax({
      url: getBaseUrl() + 'Restaurant_Order_Taking/get_menu_items',
      type: 'POST',
      dataType: 'json',
      data: {
        category_id: category_id,
        meal_type: mealTypeCsv,
        search: search
      }
    }).done(function(resp){
      $loading.hide();
      if (!resp || (resp.status !== 'success')){
        showAlert('error', (resp && resp.message) || 'Failed to load menu items');
        return;
      }
      var items = resp.items || [];
      if (!items.length){
        $container.html('<div class="col-12"><div class="alert alert-info mb-0">No items found for the selected filters.</div></div>');
        return;
      }

      // Render grid of items
      var html = [];
      $.each(items, function(i, it){
        var id = it.id || it.product_id || it.sma_product_id || 0;
        var name = (it.name || '').toString();
        var code = (it.code || '').toString();
        var price = (typeof it.price !== 'undefined') ? it.price : (it.pd_price || 0);
        var mealType = (it.meal_type_name || '').toString();
        var img = (it.image || '').toString();
        var imgUrl = resolveImageUrl(img);
        var noImg = resolveImageUrl('');
      var safeName = name.replace(/"/g, '&quot;');
      var imgTag = '<div class="menu-item-thumb"><img src="'+ imgUrl +'" alt="'+ safeName +'" class="menu-thumb" onerror="this.onerror=null;this.src=\''+ noImg +'\';"></div>';
      var priceText = (window.sma && sma.formatMoney ? sma.formatMoney(price) : parseFloat(price).toFixed(2));
      var metaHtmlParts = [];
      if (code) { metaHtmlParts.push('<span class="menu-code">'+ code +'</span>'); }
      if (mealType) { metaHtmlParts.push('<span class="menu-pill">'+ mealType +'</span>'); }
      var metaHtml = metaHtmlParts.length ? '<div class="menu-item-meta">'+ metaHtmlParts.join('') +'</div>' : '';

      
      // Determine stepper state
      var cartInfo = getVisibleCartItemInfo(id);
      var btnHtml = '';
      if (cartInfo.qty > 0) {
           // Compact stepper matching the reference design (auto width, not full width)
           btnHtml = '<div class="d-flex align-items-center justify-content-between gap-2" style="min-width:110px; border:2px solid #e9176b; border-radius:8px; padding:4px 8px; background:#fff;">' +
                      '<button type="button" class="btn btn-sm p-0 d-flex align-items-center justify-content-center" style="width:24px; height:24px; background:#e9176b; color:#fff; border-radius:4px;" onclick="decreaseFromMenu('+ id +')"><i class="bi bi-dash"></i></button>' +
                      '<span class="fw-bold text-dark" style="font-size:1.1rem;">'+ (cartInfo.qty % 1 === 0 ? cartInfo.qty : cartInfo.qty.toFixed(1)) +'</span>' +
                      '<button type="button" class="btn btn-sm p-0 d-flex align-items-center justify-content-center" style="width:24px; height:24px; background:#e9176b; color:#fff; border-radius:4px;" onclick="increaseFromMenu('+ id +')"><i class="bi bi-plus"></i></button>' +
                     '</div>';
      } else {
           btnHtml = '<button type="button" class="add-btn-new" onclick="openQuickAdd('+ id +')">ADD</button>';
      }

      html.push(
        '<div class="menu-item-new" data-product-id="'+ id +'">' +
          '<img src="'+ imgUrl +'" class="menu-thumb-new" onerror="this.onerror=null;this.src=\''+ noImg +'\';">' +
          '<div class="item-details">' +
             '<div style="display:flex; align-items:center; gap:5px; margin-bottom:5px;">' +
                 '<div class="veg-icon-box '+ (mealType.toLowerCase() === 'veg' ? 'veg' : 'non-veg') +'">'+
                    '<div class="dot"><i class="bi '+ (mealType.toLowerCase() === 'veg' ? 'bi-circle-fill' : 'bi-caret-up-fill') +'"></i></div>'+
                 '</div>'+
                 (it.spice_level ? '<i class="bi bi-fire text-danger" style="font-size:12px;"></i>' : '') +
             '</div>' +
             '<div class="item-title">'+ name +'</div>'+
             '<div class="item-price">'+ priceText +'</div>'+
          '</div>'+
          btnHtml +
        '</div>'
      );
      });
      $container.html(html.join(''));
    }).fail(function(){
      $loading.hide();
      showAlert('error', 'Network error while loading menu items');
    });
  }
  
  // Expose to window as required by inline attributes
  window.toggleCart = window.toggleCart || toggleCart;
  window.backToTables = window.backToTables || backToTables;
  window.increaseGuest = window.increaseGuest || increaseGuest;
  window.decreaseGuest = window.decreaseGuest || decreaseGuest;
  window.updateQuantity = window.updateQuantity || updateQuantity;
  window.removeItem = window.removeItem || removeItem;
  window.selectGuest = window.selectGuest || selectGuest;
  window.selectCategory = window.selectCategory || selectCategory;
  window.toggleFilter = window.toggleFilter || toggleFilter;
  window.completeAndFree = window.completeAndFree || completeAndFree;
  window.openQuickAdd = window.openQuickAdd || openQuickAdd;
  window.adjustQty = window.adjustQty || adjustQty;
  window.addItemToOrder = window.addItemToOrder || addItemToOrder;
  window.openEditItemModal = window.openEditItemModal || openEditItemModal;
  // Ensure modal-related handlers are globally accessible for inline onclick
  window.showProductDetails = window.showProductDetails || showProductDetails;
  window.addCustomAllergy = window.addCustomAllergy || addCustomAllergy;
  window.syncPresetAllergies = window.syncPresetAllergies || syncPresetAllergies;
  window.decreaseFromMenu = window.decreaseFromMenu || decreaseFromMenu;
  window.increaseFromMenu = window.increaseFromMenu || increaseFromMenu;
  // Back-compat alias: some HTML still calls finalizeOrder()
  window.finalizeOrder = window.finalizeOrder || function(confirmFirst){ return completeAndFree(confirmFirst); };

  // Initialize
  function filterOrderSummaryItems(){
    var guestId = (typeof selectedGuestId === 'undefined' || selectedGuestId === null) ? 'all' : selectedGuestId;
    var $items = $('#order-items-container .order-item');
    var visibleCount = 0;
    if (!$items.length){
      $('#order-summary-count').text('0');
      $('#order-summary-empty').toggleClass('d-none', false);
      return;
    }
    $items.each(function(){
      var $item = $(this);
      var itemGuestId = $item.data('guest-id');
      var matches = (guestId === 'all') || (itemGuestId === 'all') || (String(itemGuestId) === String(guestId));
      $item.toggleClass('d-none', !matches);
      if (matches) { visibleCount++; }
    });
    $('#order-summary-count').text(visibleCount);
    $('#order-summary-empty').toggleClass('d-none', visibleCount > 0);
  }

  window.filterOrderSummaryItems = window.filterOrderSummaryItems || filterOrderSummaryItems;
  window.serverOrderItems = <?php echo isset($order_items) ? json_encode($order_items) : '[]'; ?>;

  window.toggleMenuSection = function() {
    $('#menu-items-container').slideToggle();
    var icon = $('#menu-section-header i');
    if (icon.hasClass('bi-chevron-up')) {
      icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
    } else {
      icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
    }
  };

  function renderOrderItems() {
      var items = window.serverOrderItems || [];
      var $container = $('#new-order-items-list');
      var $countDisplay = $('#total-order-count');
      
      $countDisplay.text(items.length);
      $container.empty();

      if (items.length === 0) {
          $container.html('<div class="text-muted text-center py-3">No items in order</div>');
          return;
      }

      // Group by Guest
      var guestGroups = {};
      var guestIds = [];

      items.forEach(function(item) {
          var gId = item.res_orders_guests_id || item.guest_id || 'all';
          if (gId === '0') gId = 'all'; // Normalize
          
          if (!guestGroups[gId]) {
              guestGroups[gId] = [];
              guestIds.push(gId);
          }
          guestGroups[gId].push(item);
      });

      // Sort guest IDs (All always first, then numerical)
      guestIds.sort(function(a, b) {
          if (a === 'all') return -1;
          if (b === 'all') return 1;
          return parseInt(a) - parseInt(b);
      });

      // Render Each Group
      guestIds.forEach(function(gId) {
          var groupItems = guestGroups[gId];
          var guestLabel = (gId === 'all') ? 'All Guests' : 'Guest ' + gId; 
          
          // Derive sequential guest number from guest_display_number if available
          if (gId !== 'all' && groupItems.length > 0) {
              var dispNum = groupItems[0].guest_display_number || groupItems[0].guest_number;
              if (dispNum) {
                  guestLabel = 'Guest ' + dispNum;
              }
          }

          var groupHtml = '<div class="guest-order-block">';
          
          // Header
          groupHtml += '<div class="guest-order-header">';
          groupHtml +=   '<div class="guest-badge"><i class="bi bi-person"></i> ' + guestLabel + '</div>';
          groupHtml +=   '<div class="guest-actions">';
          groupHtml +=     '<div class="guest-action-btn" onclick="openGuestActions(\''+gId+'\')"><i class="bi bi-plus"></i></div>';
          groupHtml +=     '<div class="" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#collapse-guest-'+gId+'"><i class="bi bi-chevron-up"></i></div>';
          groupHtml +=   '</div>';
          groupHtml += '</div>';

          // Items Container
          groupHtml += '<div class="collapse show" id="collapse-guest-'+gId+'">';
          
          groupItems.forEach(function(item) {
              var id = item.id;
              var name = item.product_name;
              var qty = parseFloat(item.quantity);
              var price = parseFloat(item.amount); // total amount
              var unitPrice = qty > 0 ? (price / qty) : price;
              // Icons Logic
              const metaIcons = [];
              // 1. Allergies
              let allergyIconHtml = '';
              if (item.sma_res_common_allergies_list || item.custom_allergies_text) {
                  allergyIconHtml = `<i class="bi bi-exclamation-triangle-fill text-danger fs-4 mb-1 d-block"></i>`;
              }
              
              // 2. Veg/Non-veg
              const isVegItem = (item.meal_type_name || '').toLowerCase() === 'veg' || item.meal_type_id == 1;
              const vegIconFile = isVegItem ? 'veg_icon.svg' : 'non-veg_icon.svg';
              metaIcons.push(`
                  <div class="veg-icon-box ${isVegItem?'veg':'non-veg'}" style="width:20px;height:20px;border:1px solid ${isVegItem?'#28a745':'#dc3545'};padding:1px;display:inline-flex;align-items:center;justify-content:center;margin-right:8px; border-radius: 4px; background: #fff;">
                      <img src="${window.site.base_url}themes/default/assets/restaurant/images/${vegIconFile}" width="14" height="14">
                  </div>
              `);

              // 3. Garlic
              if (item.garlic_flag == 1) {
                   metaIcons.push(`
                      <div class="d-inline-flex align-items-center me-2" style="background:#fff; border-radius:20px; padding:2px 6px; border:1px solid #ddd; height: 26px;">
                          <img src="${window.site.base_url}themes/default/assets/restaurant/images/garlic_icon.svg" width="16" height="16">
                          <img src="${window.site.base_url}themes/default/assets/restaurant/images/toggle-on.svg" width="22" class="ms-1">
                      </div>
                   `);
              }

              // 4. Onion
              if (item.onion_flag == 1) {
                   metaIcons.push(`
                      <div class="d-inline-flex align-items-center me-2" style="background:#fff; border-radius:20px; padding:2px 6px; border:1px solid #ddd; height: 26px;">
                          <img src="${window.site.base_url}themes/default/assets/restaurant/images/onion_icon.svg" width="16" height="16">
                          <img src="${window.site.base_url}themes/default/assets/restaurant/images/toggle-on.svg" width="22" class="ms-1">
                      </div>
                   `);
              }

              // 5. Spice Level
              if (item.spice_level) {
                  let spicyIcon = 'low_spicy_icon.svg';
                  const sl = item.spice_level.toLowerCase();
                  if (sl.includes('medium')) spicyIcon = 'medium_spicy_icon.svg';
                  if (sl.includes('high') || sl.includes('spicy') || sl.includes('hot')) spicyIcon = 'high_spicy_icon.svg';
                  metaIcons.push(`
                       <div class="d-inline-flex align-items-center me-2" style="background:#fff; border-radius:20px; padding:2px 8px; border:1px solid #ddd; height: 26px;">
                          <img src="${window.site.base_url}themes/default/assets/restaurant/images/${spicyIcon}" height="16">
                       </div>
                  `);
              }


              // Prepare data attributes for edit modal
              var dataAttrs = ' data-item-id="' + id + '"' +
                              ' data-product-id="' + (item.product_id || item.sma_product_id) + '"' +
                              ' data-product-name="' + (name || '').replace(/"/g, '&quot;') + '"' +
                              ' data-quantity="' + qty + '"' +
                              ' data-topping-ids="' + (item.on_toppings_id || '') + '"' +
                              ' data-addon-ids="' + (item.on_add_on_id || '') + '"' +
                              ' data-spice-level="' + (item.spice_level || '') + '"' +
                              ' data-meat-wellness-id="' + (item.meat_wellness_id || '') + '"' +
                              ' data-onion-flag="' + (item.onion_flag) + '"' +
                              ' data-garlic-flag="' + (item.garlic_flag) + '"' +
                              ' data-instructions="' + (item.special_instructions || '').replace(/"/g, '&quot;') + '"' +
                              ' data-allergy-ids="' + (item.sma_res_common_allergies_list || '') + '"';

              groupHtml += '<div class="ordered-item-card order-item" ' + dataAttrs + '>';
              
              // LEFT: Icon + Name + Meta
              groupHtml +=   '<div class="ordered-item-info" onclick="openEditItemModal(\'' + id + '\')" style="cursor:pointer; flex: 1;">';
              groupHtml +=      allergyIconHtml;
              groupHtml +=      '<div class="item-meta-icons d-flex align-items-center mb-1">';
              groupHtml +=          metaIcons.join('');
              groupHtml +=      '</div>';
              groupHtml +=      '<div class="ordered-item-name" style="font-weight: 600; font-size: 14px; color: #333;">' + name + '</div>';
              groupHtml +=   '</div>';

              // RIGHT: Qty Control
              groupHtml +=   '<div class="d-flex align-items-center gap-3">';
              groupHtml +=      '<div class="qty-control-sm">';
              groupHtml +=          '<button class="qty-btn-sm" onclick="event.stopPropagation(); updateQuantity('+id+', -1)">-</button>';
              groupHtml +=          '<div class="qty-val-sm">' + qty + '</div>';
              groupHtml +=          '<button class="qty-btn-sm" onclick="event.stopPropagation(); updateQuantity('+id+', 1)">+</button>';
              groupHtml +=      '</div>';
              
              // Edit/Delete (Optional, maybe just edit)
              // groupHtml +=      '<i class="bi bi-pencil-square text-primary" style="cursor:pointer;" onclick="openEditItemModal('+id+')"></i>';
              
              groupHtml +=   '</div>'; // End Right

              groupHtml += '</div>'; // End Card
          });

          groupHtml += '</div>'; // End Collapse
          groupHtml += '</div>'; // End Block
          
          $container.append(groupHtml);
      });
  }

  // Helper for guest action button in the new list
  window.openGuestActions = function(guestId) {
      if(typeof selectGuest === 'function') {
          selectGuest(guestId);
      }
      // Scroll to menu or focus search
      $('html, body').animate({
        scrollTop: $(".menu-categories-section").offset().top - 100
      }, 500);
  };

  $(function(){
    bootstrapFromDom();
    setupAjaxCsrf();
    initICheck();
    initEventHandlers();
    
    // Check for guest_id in URL to deep link
    const urlParams = new URLSearchParams(window.location.search);
    const initialGuestId = urlParams.get('guest_id');
    
    // Initial load
    loadMenuItems();
    if ($('#cart-count-mobile').length) {
      $('#cart-count-mobile').text($('#cart-count').text());
    }
    
    // If guest ID provided, select it (this will trigger filterOrderSummaryItems)
    if (initialGuestId && initialGuestId !== 'all') {
         // Use a small timeout to ensure DOM is ready or just call it if synchronous
         if (typeof selectGuest === 'function') {
             selectGuest(initialGuestId);
         }
    } else {
         filterOrderSummaryItems();
    }
    
    renderOrderItems(); // Call our new function
    // Fade out any loading overlay if present
    $(window).on('load', function(){ $('#loading').fadeOut('slow'); });
  });

})(window, window.jQuery);

</script>
</html>