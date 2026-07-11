<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= isset($page_title) ? $page_title : 'Restaurant Orders' ?> - Restaurant</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= $assets ?>restaurant/assets/css/theme.css" />
    <link rel="stylesheet" href="<?= $assets ?>restaurant/assets/css/responsive.css" />

    <style>
        body {
            background: #ffffff;
            color: #2f3d4a;
            font-family: 'Segoe UI', Roboto, sans-serif;
        }

        #body-div {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            max-width: 1200px;
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative; /* Context for absolute positioning of modal if needed */
        }

        .nav-tabs-container {
            background: #ffffff;
            border: 2px solid #e9176b;
            border-radius: 10px;
            padding: 0;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .nav-tabs-custom {
            display: flex;
        }

        .nav-tab-custom {
            flex: 1;
            background: #ffffff;
            color: #000;
            font-weight: 600;
            text-align: center;
            padding: 16px 0;
            border-right: 1.5px solid #e9176b;
            border-radius: 0;
            transition: all 0.25s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .nav-tab-custom:last-child {
            border-right: none;
        }

        .nav-tab-custom.active {
            background: #e9176b;
            color: #ffffff;
        }

        .nav-tab-custom:hover {
            background: #e9176b;
            color: #ffffff;
        }

        .controls-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 32%;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .control-label {
            font-weight: 700;
            font-size: 1.2rem;
            color: #000;
        }

        .table-select {
            border: 2px solid #e9176b;
            border-radius: 8px;
            padding: 5px 30px 5px 15px;
            font-size: 1.4rem;
            font-weight: 600;
            min-width: 120px;
            color: #000;
            background-color: #fff;
            outline: none;
            /* text-align: center; */
        }
        
        .guest-counter {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .guest-btn {
            background: #e9176b;
            color: white;
            border: none;
            border-radius: 5px;
            width: 38px;
            height: 38px;
            font-size: 1.5rem;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .guest-btn:hover {
            background-color: #d00f5c;
        }

        .guest-count-display {
            font-size: 1.8rem;
            font-weight: 600;
            min-width: 40px;
            text-align: center;
            cursor: pointer; /* Clickable to open calculator */
        }
        
        /* Order Items / Guest List Area */
        .order-items-header {
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 20px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 15px;
            width: 100%;
        }

        .guest-list-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 0 10px;
        }

        .guest-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 15px;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
        }

        .guest-info {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 6px 18px;
            min-width: 110px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .guest-info.active {
            background-color: #f7cbdc !important;
            border-color: #e9176b !important;
            color: #e9176b !important;
        }

        .guest-info.served-item {
            background-color: #a7e1a7 !important;
            border-color: #7ecf81 !important;
            color: #333 !important;
        }

        .guest-info {
            background-color: #ffffff;
            border: 1px solid #ccc;
            color: #333;
        }

        .guest-icon {
            font-size: 1.3rem;
        }

        .guest-name {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .guest-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-add-item {
            color: #e9176b;
            background: #fff;
            border: 1.5px solid #e9176b;
            border-radius: 6px;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.2s;
        }
        .btn-add-item:hover {
            background-color: #fef1f6;
        }

        .btn-expand {
            color: #000;
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            transform: rotate(0deg);
            transition: transform 0.2s;
        }
        .btn-expand.expanded {
            transform: rotate(180deg);
        }

        /* Bottom Action Bar */
        .bottom-actions {
            margin-top: auto;
            padding: 30px 0;
            display: flex;
            justify-content: center;
            gap: 5%;
            height: 105px !important;
        }

        .btn-action {
            padding: 14px 40px;
            font-size: 1.3rem;
            font-weight: 700;
            border-radius: 12px;
            min-width: 320px;
            text-transform: capitalize;
        }

        .btn-kot-color {
            background-color: #e9176b !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-kot-color:hover {
            background-color: #d00f5c !important;
        }

        .btn-finelize-order {
            background-color: #ffffff !important;
            color: #a0a0a0 !important;
            border: 1.5px solid #d0d0d0 !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .btn-finelize-order:hover {
            background-color: #f8f8f8 !important;
            border-color: #c0c0c0 !important;
        }
            color: white;
        }

        .btn-finalize {
            background-color: white;
            color: #9e9e9e;
            border: 1px solid #9e9e9e;
        }
        .btn-finalize:hover {
            background-color: #f0f0f0;
            color: #9e9e9e;
        }

        /* Calculator Modal Overlay */
        .calc-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.1); /* Light overlay */
            z-index: 1050;
            display: none;
            align-items: flex-end; /* Bottom sheet style or center */
            justify-content: center;
        }

        .calc-container {
            background: #eee;
            width: 100%;
            max-width: 500px; /* Or full width/specific width */
            /* position: absolute; */
            /* bottom: 0; */
            margin-bottom: 0; /* Attach to bottom if responsive */
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 20px;
            /* transform: translateY(100%); transition? */
        }
        
        .calc-display-container {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .calc-display {
            flex: 1;
            background: #fff;
            border: 1px solid #ddd; /* Pink border in image? */
            border: 2px solid #ddd; 
            border-radius: 6px;
            height: 50px;
            display: flex;
            align-items: center;
            padding: 0 15px;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .calc-backspace {
            width: 60px;
            background: #fff;
            border: 2px solid #e9176b;
            border-radius: 6px;
            color: #e9176b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .numpad-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr) 1fr; /* 3 cols for numbers, 1 col for actions? No, image is 3x4 + side action */
            /* Actually image shows:
               [Display] [Backspace]
               [1][2][3] [Clear]
               [4][5][6] [Done (tall)]
               [7][8][9]
                 [0]
            */
            gap: 10px;
        }
        
        .numpad-main {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .numpad-side {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-numpad {
            background: #fff;
            border: none;
            border-radius: 6px;
            height: 50px;
            font-size: 1.5rem;
            font-weight: 600;
            color: #000;
            cursor: pointer;
        }
        .btn-numpad:active {
            background-color: #f0f0f0;
        }

        .btn-clear {
            background: #fff;
            border: 1px solid #e9176b; /* Image shows text Clear */
            color: #000; 
            font-weight: 600;
            height: 50px;
            border-radius: 6px;
        }

        .btn-done {
            background: #e9176b;
            color: white;
            border: none;
            border-radius: 6px;
            flex: 1; /* Take remaining height */
            /* height: 100%; */
            font-weight: 600;
        }
        
        /* Adjust grid to match image exactly */
        .calculator-layout {
            display: flex;
            gap: 10px;
        }
        .calc-left {
            flex: 3;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .calc-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .row-nums {
            display: flex;
            gap: 10px;
        }
        .row-nums .btn-numpad {
            flex: 1;
        }
        .btn-kot-color {
            background-color: #e91e63 !important;
            color: #ffffff !important;
            border: none;
        }
        .btn-kot-color:hover {
            background-color: #d81b60 !important;
        }

        .btn-finalize {
            background-color: #ffffff;
            color: #e91e63;
            border: 1px solid #e91e63;
        }
        .btn-finalize:hover {
            background-color: #fce4ec;
            color: #d81b60;
            border-color: #d81b60;
        }
        .btn-finelize-order{
            background-color: #ffffff !important;
            color: #e91e63 !important;
            border:1px solid #e91e63 !important;
        }

        .order-item-card.served-item {
            background-color: #7ecf81 !important;
            border-color: #badbcc !important;
        }

        .btn-served {
            background-color: #28a745 !important;
            color: #ffffff !important;
            border: none !important;
        }

        .total-amount-box {
            background-color: #f8c6d8;
            color: #333;
            padding: 15px 25px;
            border-radius: 12px;
            margin: 20px auto;
            max-width: 800px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 1.4rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .total-amount-box .total-label {
            color: #555;
        }

        .total-amount-box .total-value {
            color: #000;
        }

        /* Read-Only Summary Mode */
        .readonly-mode .btn-add-item,
        .readonly-mode .guest-btn,
        .readonly-mode .btn-qty,
        .readonly-mode .btn-edit-item,
        .readonly-mode #table-select,
        .readonly-mode #guest-count,
        .readonly-mode .order-item-card {
            pointer-events: none !important;
            opacity: 0.7 !important;
            cursor: default !important;
        }

        .readonly-mode .guest-info {
            cursor: default !important;
        }

        /* Keep expand buttons clickable in read-only mode */
        .readonly-mode .btn-expand {
            pointer-events: auto !important;
            opacity: 1;
            cursor: pointer !important;
        }
        .btn-finalize.disabled {
            background-color: #ccc !important;
            border-color: #ccc !important;
            color: #666 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            opacity: 0.7;
        }

        /* Grouped Quantity Control Styles */
        .qty-control {
            border: 1.5px solid #e9176b;
            border-radius: 8px;
            display: flex;
            align-items: center;
            padding: 2px;
            background: #fff;
            height: 36px;
        }
        .qty-control .btn-qty {
            background: #e9176b;
            color: #fff;
            border: none;
            border-radius: 4px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: background 0.2s;
        }
        .qty-control .btn-qty:hover {
            background: #d00f5c;
        }
        .qty-control .btn-qty i {
            font-size: 1.1rem;
            font-weight: 900;
        }
        .qty-control .qty-val {
            padding: 0 10px;
            font-weight: 700;
            min-width: 30px;
            text-align: center;
            color: #000;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 m-0" id="body-div">
        <?php include('partials/header.php'); ?>

        <div class="container-xl mt-4 main-content <?= (isset($view_mode) && $view_mode == 'summary') ? 'readonly-mode' : '' ?>">
            <!-- Nav Tabs -->
            <div class="nav-tabs-container">
                <div class="nav-tabs-custom">
                    <a href="<?= base_url('Restaurant_Order_Taking') ?>" class="nav-tab-custom" data-tab="sections">Sections</a>
                    <?php $tab_sec_id = isset($section_id) ? $section_id : (isset($table) && isset($table->section_id) ? $table->section_id : (isset($sections[0]) ? $sections[0]->id : '')); ?>
                    <?php $tab_sub_id = isset($subsection_id) ? $subsection_id : (isset($table) && isset($table->subsection_id) ? $table->subsection_id : ''); ?>
                    <?php $tables_link = $tab_sub_id ? base_url('Restaurant_Order_Taking/tables/' . $tab_sec_id . '/' . $tab_sub_id) : base_url('Restaurant_Order_Taking/tables/' . $tab_sec_id); ?>
                    <a href="<?= $tables_link ?>" class="nav-tab-custom" data-tab="tables">Tables</a>
                    <a href="<?= base_url('Restaurant_Order_Taking/orders') ?>" class="nav-tab-custom active" data-tab="orders">Orders</a>
                </div>
            </div>

            <!-- Controls (Table & Guests) -->
            <div class="controls-row">
                <!-- Table Selector -->
                <div class="control-group">
                    <span class="control-label">Table</span>
                    <select class="table-select" id="table-select">
                        <?php if(isset($tables) && !empty($tables)): ?>
                            <?php foreach($tables as $t): ?>
                                <option value="<?= $t->id ?>" <?= (isset($table_id) && $table_id == $t->id) ? 'selected' : '' ?>><?= $t->name ?></option>
                            <?php endforeach; ?>
                        <?php elseif(isset($table)): ?>
                             <option value="<?= $table->id ?>" selected><?= $table->name ?></option>
                        <?php else: ?>
                            <option value="">Select</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Guest Counter -->
                <div class="control-group">
                    <span class="control-label">Guests</span>
                    <div class="guest-counter">
                        <button class="guest-btn" id="btn-minus" type="button"><i class="bi bi-dash"></i></button>
                        <span class="guest-count-display" id="guest-count"><?= isset($order) ? $order->guest_count : '0' ?></span>
                        <button class="guest-btn" id="btn-plus" type="button"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
            </div>

            <!-- Order Items Header -->
            <div class="order-items-header">
                Order Items (<span id="total-items-count">0</span> Items)
            </div>

            <div class="guest-list-container" id="guest-list-container">
            </div>

            <?php if (isset($totals) && isset($view_mode) && $view_mode == 'summary'): ?>
                <div class="total-amount-box">
                    <span class="total-label">Total Amount:</span>
                    <span class="total-value"><?= $this->sma->formatMoney($totals['grand_total']) ?></span>
                </div>
            <?php endif; ?>

            <!-- Bottom Actions --> 
            <?php if (!isset($view_mode) || $view_mode != 'summary'): ?>
            <div class="bottom-actions">
                <button class="btn btn-action btn-kot btn-kot-color" type="button" style="width: 25rem !important;">KOT</button>
                <button class="btn btn-action btn-finalize btn-finelize-order"  onclick="finalizeOrder(true)" type="button" style="width: 25rem !important;">Finalize Order</button>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Calculator Overlay -->
    <div class="calc-overlay" id="calc-overlay">
        <div class="calc-container">
            <div class="calc-display-container">
                <div class="calc-display" id="calc-display-val">2</div>
                <div class="calc-backspace" id="btn-calc-backspace"><i class="bi bi-backspace"></i></div> <!-- Icon for backspace with x -->
            </div>
            
            <div class="calculator-layout">
                <div class="calc-left">
                    <div class="row-nums">
                        <button class="btn-numpad" data-num="1">1</button>
                        <button class="btn-numpad" data-num="2">2</button>
                        <button class="btn-numpad" data-num="3">3</button>
                    </div>
                    <div class="row-nums">
                        <button class="btn-numpad" data-num="4">4</button>
                        <button class="btn-numpad" data-num="5">5</button>
                        <button class="btn-numpad" data-num="6">6</button>
                    </div>
                    <div class="row-nums">
                        <button class="btn-numpad" data-num="7">7</button>
                        <button class="btn-numpad" data-num="8">8</button>
                        <button class="btn-numpad" data-num="9">9</button>
                    </div>
                    <div class="row-nums">
                        <button class="btn-numpad" style="visibility:hidden"> </button> <!-- Placeholder to center 0 -->
                        <button class="btn-numpad" data-num="0">0</button>
                        <button class="btn-numpad" style="visibility:hidden"> </button>
                    </div>
                </div>
                <div class="calc-right">
                    <button class="btn-clear" id="btn-calc-clear">Clear</button>
                    <button class="btn-done" id="btn-calc-done">Done</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title w-100 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span id="edit-modal-product-name" class="fw-semibold"></span>
                        </div>
                        <span class="text-muted small"><span id="edit-modal-guest-label">All Guests</span></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"> 
                    <div id="edit-modal-content">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <!-- <div class="modal-footer">
                    <div class="w-100 d-flex align-items-center justify-content-between gap-2">
                        <div class="btn-group footer-qty" role="group" aria-label="Quantity Stepper">
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustEditQty(-1)">-</button>
                            <button type="button" class="btn btn-outline-secondary qty-display" id="edit-qty-display">1</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="adjustEditQty(1)">+</button>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="update-item-btn" class="btn btn-brand btn-lg px-4 flex-fill"
                                onclick="updateItem()">
                                <i class="bi bi-pencil"></i> Update Item
                            </button>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            <?php $ci =& get_instance(); ?>
            let currentOrderId = '<?= isset($order) ? $order->id : "" ?>';
            let orderStatus = '<?= isset($order) ? $order->status : "" ?>';
            const currentTableId = '<?= isset($table_id) ? $table_id : "" ?>';
            const serverGuests = <?= json_encode(isset($guests) ? $guests : []) ?>;
            const serverOrderItems = <?= json_encode(isset($order_items) ? $order_items : []) ?>;
            console.log('Server Guests:', serverGuests);
            console.log('Server Order Items:', serverOrderItems);

            // Persist guest count across refresh (per table)
            const guestStorageKey = currentTableId ? ('restaurant_guest_count_' + currentTableId) : '';
            const initialGuestCount = parseInt($('#guest-count').text()) || 0;
            // Only use saved count if there is an active order (to avoid stale data on available tables)
            const savedGuestCount = (guestStorageKey && currentOrderId) ? parseInt(localStorage.getItem(guestStorageKey) || '') : NaN;
            let currentGuestCount = Number.isFinite(savedGuestCount) ? savedGuestCount : initialGuestCount;

            function saveGuestCount(count) {
                if (!guestStorageKey) return;
                localStorage.setItem(guestStorageKey, String(count));
            }

            // Update table guest information display
            function updateTableGuestInfo() {
                // Update current guests display
                $('#table-current-guests').text(currentGuestCount);
                
                // Update storage display
                if (guestStorageKey) {
                    const storageValue = localStorage.getItem(guestStorageKey);
                    $('#table-storage-guests').text(storageValue || '0');
                } else {
                    $('#table-storage-guests').text('No Key');
                }
            }

            // Sync UI with persisted count (or default 0)
            $('#guest-count').text(currentGuestCount);
            $('#calc-display-val').text(currentGuestCount);
            saveGuestCount(currentGuestCount);
            
            // Update table guest info display
            updateTableGuestInfo();

            // Handle Table Selection Change
            $('#table-select').on('change', function() {
                const tableId = $(this).val();
                if (tableId) {
                    window.location.href = '<?= base_url("Restaurant_Order_Taking/orders/") ?>' + tableId;
                }
            });

            // Storage key for active guest selection
            const activeGuestStorageKey = currentTableId ? ('restaurant_active_guest_' + currentTableId) : '';
            // Retrieve active guest or default to null (don't show directly)
            let rawStoredGuest = activeGuestStorageKey ? localStorage.getItem(activeGuestStorageKey) : null;
            
            // Check FOR guest_id in URL - specifically for when coming back from finalize screen
            const _urlParams = new URLSearchParams(window.location.search);
            const _urlGuestId = _urlParams.get('guest_id');
            
            let activeGuestId;
            if (_urlGuestId && _urlGuestId !== 'null') {
                activeGuestId = _urlGuestId.toString();
                // Normalize index to DB ID if possible
                if (activeGuestId.length < 5 && serverGuests && serverGuests[parseInt(activeGuestId)-1]) {
                    activeGuestId = serverGuests[parseInt(activeGuestId)-1].id.toString();
                }
                
                // Sync to storage
                if (activeGuestStorageKey) {
                    localStorage.setItem(activeGuestStorageKey, activeGuestId);
                }
            } else {
                 // Handle case where 'null' string might be stored or value is actually null
                activeGuestId = (rawStoredGuest === 'null' || rawStoredGuest === null) ? null : rawStoredGuest;
            }
            
            console.log('Initial (normalized) activeGuestId:', activeGuestId);

            // Storage key for expanded guest sections
            const expandedGuestsStorageKey = currentTableId ? ('restaurant_expanded_guests_' + currentTableId) : '';
            let expandedGuestIds = [];
            
            if (expandedGuestsStorageKey) {
                const stored = localStorage.getItem(expandedGuestsStorageKey);
                if (stored) {
                    try {
                        expandedGuestIds = JSON.parse(stored);
                    } catch(e) { expandedGuestIds = []; }
                } else {
                    // Default to individual guests expanded, but 'All' section collapsed
                    for (let i = 1; i <= 20; i++) expandedGuestIds.push(i.toString()); // Max 20 guests pre-expansion
                }
            }

            // Retrieval and initialization logic already moved below guest_id parsing
            
            function saveExpandedState() {
                if (expandedGuestsStorageKey) {
                    localStorage.setItem(expandedGuestsStorageKey, JSON.stringify(expandedGuestIds));
                }
            }

            // Move this AFTER guest_id parsing to include URL guest in initial expanded list
            if (_urlGuestId && _urlGuestId !== 'null') {
                const gidStr = _urlGuestId.toString();
                if (!expandedGuestIds.includes(gidStr)) {
                    expandedGuestIds.push(gidStr);
                }
                
                // Also find and expand the corresponding DB ID if the URL was an index
                if (gidStr.length < 5) { // Likely an index
                    const idx = parseInt(gidStr);
                    if (serverGuests && serverGuests[idx-1]) {
                        const dbId = serverGuests[idx-1].id.toString();
                        if (!expandedGuestIds.includes(dbId)) expandedGuestIds.push(dbId);
                    }
                }
                saveExpandedState();
            }

            function isExpanded(id, index) {
                if (expandedGuestIds.includes(id.toString())) return true;
                if (index && expandedGuestIds.includes(index.toString())) return true;
                return false;
            }

            function toggleExpanded(id) {
                id = id.toString();
                const index = expandedGuestIds.indexOf(id);
                if (index === -1) expandedGuestIds.push(id);
                else expandedGuestIds.splice(index, 1);
                saveExpandedState();
            }

            // --- Guest List Rendering ---
            function guestHasItems(guestId, guestIndex) {
                return serverOrderItems.some(item => {
                    // 1. Match by sequential number (calculated by DB)
                    if (item.guest_display_number && parseInt(item.guest_display_number) === guestIndex) {
                        return true;
                    }
                    // 2. Exact match by DB ID
                    if (item.res_orders_guests_id == guestId) {
                        return true;
                    }
                    // 3. Match by index (for items added before guest record sync)
                    if (item.res_orders_guests_id == guestIndex) {
                        return true;
                    }
                    return false;
                });
            }

            function renderGuestList(count) {
                const container = $('#guest-list-container');
                container.empty();

                const isReadyStatus = (orderStatus === 'Ready');
                
                // 1. "All" Row
                const allRow = `
                    <div class="guest-section">
                        <div class="guest-row">
                            <div class="guest-info ${activeGuestId === 'all' ? 'active' : ''}" onclick="setActiveGuest('all')">
                                <i class="bi bi-people guest-icon"></i>
                                <span class="guest-name">All</span>
                            </div>
                            <div class="guest-actions">
                                <button class="btn-add-item" onclick="addItemForGuest('all')"><i class="bi bi-plus"></i></button>
                                <button class="btn-expand" onclick="toggleSectionExpanded('all')"><i class="bi ${isExpanded('all') ? 'bi-chevron-up' : 'bi-chevron-down'}"></i></button>
                            </div>
                        </div>
                        <div class="guest-items-container" id="guest-items-all" style="${isExpanded('all') ? '' : 'display:none;'}">
                            ${renderGuestItems('all', 0)}
                        </div>
                    </div>
                `;
                container.append(allRow);

                // 2. Individual Guest Rows
                for (let i = 1; i <= count; i++) {
                    const guestId = i.toString();
                    
                    // Best effort to find real guest ID if available
                    let realGuestId = guestId;
                    if (serverGuests && serverGuests.length >= i) {
                        realGuestId = serverGuests[i-1].id;
                    }

                    const guestHasItemsFlag = guestHasItems(realGuestId, i);
                    const rowExpanded = isExpanded(realGuestId, i);
                    const isReadyStatus = (orderStatus === 'Ready');
                    const row = `
                        <div class="guest-section">
                            <div class="guest-row">
                                <div class="guest-info ${activeGuestId === realGuestId ? 'active' : ''} ${isReadyStatus && guestHasItemsFlag ? 'served-item' : ''}" onclick="setActiveGuest('${realGuestId}')">
                                    <i class="bi bi-person guest-icon"></i>
                                    <span class="guest-name">${i}</span>
                                </div>
                                <div class="guest-actions">
                                    <button class="btn-add-item" onclick="addItemForGuest('${realGuestId}')"><i class="bi bi-plus"></i></button>
                                    <button class="btn-expand" onclick="toggleSectionExpanded('${realGuestId}')"><i class="bi ${rowExpanded ? 'bi-chevron-up' : 'bi-chevron-down'}"></i></button>
                                </div>
                            </div>
                            <div class="guest-items-container" id="guest-items-${realGuestId}" style="${rowExpanded ? '' : 'display:none;'}">
                                ${renderGuestItems(realGuestId, i)}
                            </div>
                        </div>
                    `;
                    container.append(row);
                }
                
                updateTotalCount();
            }

            function renderGuestItems(guestId, guestIndex) {
                const items = serverOrderItems.filter(item => {
                    // Use a flag for items explicitly assigned to 'all' or with no guest
                    const isAllItem = !item.res_orders_guests_id || item.res_orders_guests_id === 'all' || item.res_orders_guests_id == 0;

                    if (guestId === 'all') {
                        return true; // "All" section shows the entire order
                    }
                    
                    if (isAllItem) {
                        return true; // items for "All" show up in every guest's list
                    }
                    
                    // 1. Try matching by guest_display_number (the sequential index from DB)
                    if (item.guest_display_number && parseInt(item.guest_display_number) === guestIndex) {
                        return true;
                    }
                    // 2. Try matching by DB ID (string or number comparison)
                    if (item.res_orders_guests_id && (item.res_orders_guests_id == guestId || item.res_orders_guests_id.toString() === guestId.toString())) {
                         return true;
                    }
                    // 3. Match by sequential index directly (fallback for items added with numerical index)
                    var itemGid = parseInt(item.res_orders_guests_id);
                    if (!isNaN(itemGid) && itemGid === guestIndex) {
                         return true;
                    }
                    
                    return false;
                });

                if (items.length === 0) return '';

                let html = '<div class="guest-items-list" style="width: 85%; padding-left: 15%;">';
                items.forEach(item => {
                    const metaIcons = [];
                    // 1. Allergies (Big Red Warning)
                    let allergyIcon = '';
                    if (item.sma_res_common_allergies_list || item.custom_allergies_text) {
                        allergyIcon = `<i class="bi bi-exclamation-triangle-fill text-danger fs-4 mb-1 d-block"></i>`;
                    }

                    // 2. Veg/Non-Veg
                    const isVeg = (item.meal_type_name || '').toLowerCase() === 'veg' || item.meal_type_id == 1;
                    const vegIconFile = isVeg ? 'veg_icon.svg' : 'non-veg_icon.svg';
                    metaIcons.push(`
                        <div class="veg-icon-box ${isVeg?'veg':'non-veg'}" style="width:20px;height:20px;border:1px solid ${isVeg?'#28a745':'#dc3545'};padding:1px;display:inline-flex;align-items:center;justify-content:center;margin-right:8px; border-radius: 4px; background: #fff;">
                            <img src="${site.base_url}themes/default/assets/restaurant/images/${vegIconFile}" width="14" height="14">
                        </div>
                    `);

                    // 3. Garlic Customization
                    if (item.garlic_flag == 1) {
                         metaIcons.push(`
                            <div class="d-inline-flex align-items-center me-2" style="background:#fff; border-radius:20px; padding:2px 6px; border:1px solid #ddd; height: 26px;">
                                <img src="${site.base_url}themes/default/assets/restaurant/images/garlic_icon.svg" width="16" height="16">
                                <img src="${site.base_url}themes/default/assets/restaurant/images/toggle-on.svg" width="22" class="ms-1">
                            </div>
                         `);
                    }

                    // 4. Onion Customization
                    if (item.onion_flag == 1) {
                         metaIcons.push(`
                            <div class="d-inline-flex align-items-center me-2" style="background:#fff; border-radius:20px; padding:2px 6px; border:1px solid #ddd; height: 26px;">
                                <img src="${site.base_url}themes/default/assets/restaurant/images/onion_icon.svg" width="16" height="16">
                                <img src="${site.base_url}themes/default/assets/restaurant/images/toggle-on.svg" width="22" class="ms-1">
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
                                <img src="${site.base_url}themes/default/assets/restaurant/images/${spicyIcon}" height="16">
                             </div>
                        `);
                    }
                    
                    const isReadyStatus = (orderStatus === 'Ready');
                    html += `
                        <div class="order-item-card ${isReadyStatus ? 'served-item' : ''}" style="${isReadyStatus ? 'border: none; box-shadow: 0 11px 16px rgba(0, 0, 0, 0.2);' : 'border: 1px solid #eee; box-shadow: 0 2px 6px rgba(0,0,0,0.05); background: #fff;'} border-radius: 8px; padding: 12px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="editItem(${item.id})">
                            <div class="item-info">
                                ${allergyIcon}
                                <div class="item-meta-icons d-flex align-items-center mb-1">
                                    ${metaIcons.join('')}
                                </div>
                                <div class="item-name" style="font-weight: 600; font-size: 14px; color: #333;">${item.product_name}</div>
                            </div>
                            <div class="item-actions" onclick="event.stopPropagation();">
                                <div class="qty-control">
                                    <button class="btn-qty" onclick="updateItemQty(${item.id}, -1, '${item.product_name}')"><i class="bi bi-dash"></i></button>
                                    <span class="qty-val">${parseFloat(item.quantity)}</span>
                                    <button class="btn-qty" onclick="updateItemQty(${item.id}, 1, '${item.product_name}')"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                return html;
            }

            function updateTotalCount() {
                let total = 0;
                serverOrderItems.forEach(i => total += parseFloat(i.quantity));
                $('#total-items-count').text(total);
            }

            window.setActiveGuest = function(id) {
                activeGuestId = id;
                if (activeGuestStorageKey) {
                    localStorage.setItem(activeGuestStorageKey, id);
                }
                renderGuestList(currentGuestCount);
            };

            window.updateItemQty = function(itemId, delta, productName) {
                if ($('.main-content').hasClass('readonly-mode')) return;
                var item = serverOrderItems.find(function(i){ return i.id == itemId; });
                if (!item) return;

                var currentQty = parseFloat(item.quantity);
                var newQty = currentQty + delta;
                
                if (newQty <= 0) {
                   if(confirm("Remove " + (productName || 'item') + "?")) {
                        $.ajax({
                            url: '<?= base_url("Restaurant_Order_Taking/delete_item") ?>',
                            type: 'POST',
                            dataType: 'json',
                            data: { 
                                item_id: itemId,
                                <?= $ci->security->get_csrf_token_name() ?>: '<?= $ci->security->get_csrf_hash() ?>'
                            },
                            success: function(resp) {
                                if(resp.status === 'success') location.reload();
                                else alert(resp.message || 'Error');
                            }
                        });
                   }
                   return;
                }

                // Construct payload preserving existing customizations
                var payload = {
                    item_id: itemId,
                    quantity: newQty,
                    <?= $ci->security->get_csrf_token_name() ?>: '<?= $ci->security->get_csrf_hash() ?>',
                    spice_level: item.spice_level,
                    meat_wellness: item.meat_wellness_id || item.sma_res_meat_wellness_id,
                    onion_flag: item.onion_flag,
                    garlic_flag: item.garlic_flag,
                    special_instructions: item.special_instructions,
                    allergies: item.sma_res_common_allergies_list,
                    add_ons: item.on_add_on_id,
                    toppings: item.on_toppings_id,
                    custom_allergies: item.custom_allergies_text
                };

                const itemGuestId = item.res_orders_guests_id || 'all';

                $.ajax({
                    url: '<?= base_url("Restaurant_Order_Taking/update_item") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: payload,
                    success: function(resp) {
                        if(resp.status === 'success') {
                            console.log('Quantity updated for item on guest:', itemGuestId);
                            // Explicitly ensure the item's guest stays active after reload
                            if (activeGuestStorageKey) {
                                localStorage.setItem(activeGuestStorageKey, itemGuestId);
                            }
                            // Also ensure it is expanded if it wasn't
                            if (expandedGuestsStorageKey) {
                                let currentExpanded = JSON.parse(localStorage.getItem(expandedGuestsStorageKey) || '[]');
                                if (!currentExpanded.includes(itemGuestId.toString())) {
                                    currentExpanded.push(itemGuestId.toString());
                                    localStorage.setItem(expandedGuestsStorageKey, JSON.stringify(currentExpanded));
                                }
                            }
                            location.reload();
                        }
                        else alert(resp.message || 'Error updating quantity');
                    }
                });
            };

            window.addItemForGuest = function(guestId) {
                if ($('.main-content').hasClass('readonly-mode')) return;
                // Just set the active state; the click event bubbles up to the .btn-add-item handler
                setActiveGuest(guestId);
            };

            // Edit Item Function
            window.editItem = function(itemId) {
                if ($('.main-content').hasClass('readonly-mode')) return;
                const item = serverOrderItems.find(i => i.id == itemId);
                if (!item) return;

                // Set modal title
                $('#edit-modal-product-name').text(item.product_name);
                
                // Set guest label
                let guestLabel = 'All Guests';
                if (item.res_orders_guests_id && item.res_orders_guests_id !== '0' && item.res_orders_guests_id !== 'all') {
                    const guestIndex = parseInt(item.guest_display_number) || parseInt(item.res_orders_guests_id);
                    if (guestIndex > 0) {
                        guestLabel = `Guest ${guestIndex}`;
                    }
                }
                $('#edit-modal-guest-label').text(guestLabel);

                // Load customization content via AJAX
                $.ajax({
                    url: '<?= base_url("Restaurant_Order_Taking/get_customizations") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        product_id: item.sma_product_id,
                        <?= $ci->security->get_csrf_token_name() ?>: '<?= $ci->security->get_csrf_hash() ?>'
                    },
                    success: function(resp) {
                        if (resp.status === 'success') {
                            // Load the customization modal content
                            $('#edit-modal-content').html(resp.html);
                            
                            // Initialize the customization scripts
                            if (window.setCustBasePrice) {
                                window.setCustBasePrice(item.item_price || item.price || 0, '<?= $Settings->currency_symbol ?>');
                            }
                            
                            // Change button to Update Item
                            $('#cust-action-btn').text('Update Item').attr('onclick', 'updateItem()');
                            
                            // Pre-fill existing values
                            setTimeout(() => {
                                prefillEditValues(item);
                            }, 100);
                            
                            // Show modal
                            const modal = new bootstrap.Modal(document.getElementById('editItemModal'));
                            modal.show();
                        } else {
                            alert(resp.message || 'Error loading customizations');
                        }
                    },
                    error: function() {
                        alert('Error loading customizations');
                    }
                });
            };

            function prefillEditValues(item) {
                // Set quantity
                $('#edit-qty-display').text(parseFloat(item.quantity));
                if (window.updateCustQty) {
                    $('#cust-qty').val(parseFloat(item.quantity));
                }

                // Set spice level
                if (item.spice_level) {
                    $(`.spice-btn[data-spice="${item.spice_level}"]`).addClass('active');
                    $('#cust-spice').val(item.spice_level);
                }

                // Set meat wellness
                if (item.meat_wellness_id || item.sma_res_meat_wellness_id) {
                    const meatId = item.meat_wellness_id || item.sma_res_meat_wellness_id;
                    $('#cust-meat-wellness').val(meatId);
                }

                // Set onion/garlic flags
                if (item.onion_flag == '1') {
                    $('#cust-onion').prop('checked', true);
                } else {
                    $('#cust-onion').prop('checked', false);
                }
                if (item.garlic_flag == '1') {
                    $('#cust-garlic').prop('checked', true);
                } else {
                    $('#cust-garlic').prop('checked', false);
                }

                // Set toppings
                if (item.on_toppings_id) {
                    const toppingIds = item.on_toppings_id.split(',');
                    toppingIds.forEach(id => {
                        $(`#toppings-container .chip[data-id="${id.trim()}"]`).addClass('active');
                    });
                }

                // Set add-ons
                if (item.on_add_on_id) {
                    const addonIds = item.on_add_on_id.split(',');
                    addonIds.forEach(id => {
                        $(`#addons-container .chip[data-id="${id.trim()}"]`).addClass('active');
                    });
                }

                // Set allergies
                if (item.sma_res_common_allergies_list) {
                    const allergyIds = item.sma_res_common_allergies_list.split(',');
                    allergyIds.forEach(id => {
                        $(`#allergies-container .allergy-chip[data-id="${id.trim()}"]`).addClass('active');
                    });
                }

                // Set custom allergies
                if (item.custom_allergies_text) {
                    const customAllergies = item.custom_allergies_text.split('|');
                    customAllergies.forEach(allergy => {
                        if (allergy.trim()) {
                            $('#allergies-container').append(
                                `<span class="chip allergy-chip active custom-allergy" data-val="${allergy.trim()}" onclick="$(this).remove(); syncCustomAllergies();">
                                    ${allergy.trim()}
                                </span>`
                            );
                        }
                    });
                }

                // Sync all values
                if (window.syncOptions) window.syncOptions();
                if (window.syncAllergies) window.syncAllergies();
                if (window.syncCustomAllergies) window.syncCustomAllergies();
                if (window.calculateTotal) window.calculateTotal();

                // Store current item ID for update
                window.currentEditingItemId = item.id;
            }

            // Adjust edit quantity
            window.adjustEditQty = function(delta) {
                const $display = $('#edit-qty-display');
                const $custQty = $('#cust-qty');
                let currentQty = parseInt($display.text()) || 1;
                currentQty += delta;
                if (currentQty < 1) currentQty = 1;
                
                $display.text(currentQty);
                $custQty.val(currentQty);
                
                if (window.calculateTotal) {
                    window.calculateTotal();
                }
            };

            // Update item function
            window.updateItem = function() {
                if (!window.currentEditingItemId) return;

                // Collect all customization data
                const payload = {
                    item_id: window.currentEditingItemId,
                    quantity: parseInt($('#cust-qty').val()) || 1,
                    <?= $ci->security->get_csrf_token_name() ?>: '<?= $ci->security->get_csrf_hash() ?>',
                    spice_level: $('#cust-spice').val(),
                    meat_wellness: $('#cust-meat-wellness').val(),
                    onion_flag: $('#cust-onion').is(':checked') ? '1' : '0',
                    garlic_flag: $('#cust-garlic').is(':checked') ? '1' : '0',
                    toppings: $('#cust-toppings').val(),
                    add_ons: $('#cust-addons').val(),
                    allergies: $('#cust-allergies').val(),
                    custom_allergies: $('#cust-custom-allergies').val()
                };

                $.ajax({
                    url: '<?= base_url("Restaurant_Order_Taking/update_item") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: payload,
                    success: function(resp) {
                        if (resp.status === 'success') {
                            // Close modal and reload page to show changes
                            bootstrap.Modal.getInstance(document.getElementById('editItemModal')).hide();
                            location.reload();
                        } else {
                            alert(resp.message || 'Error updating item');
                        }
                    },
                    error: function() {
                        alert('Error updating item');
                    }
                });
            };

            window.toggleSectionExpanded = function(id) {
                toggleExpanded(id);
                const container = id === 'all' ? $('#guest-items-all') : $(`#guest-items-${id}`);
                const icon = container.closest('.guest-section').find('.btn-expand i');
                
                icon.toggleClass('bi-chevron-down bi-chevron-up');
                container.slideToggle();
            };

            // Initial Render
            renderGuestList(currentGuestCount);

            // --- Plus / Minus Handlers (Synced with Server) ---
            $('#btn-plus').click(function() {
                if (!currentOrderId || currentOrderId === '') {
                    // If no order yet, just update local UI (order will be created with this count)
                    currentGuestCount++;
                    $('#guest-count').text(currentGuestCount);
                    saveGuestCount(currentGuestCount);
                    updateTableGuestInfo();
                    renderGuestList(currentGuestCount);
                    return;
                }

                $.ajax({
                    url: '<?= base_url("Restaurant_Order_Taking/increase_guest") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        order_id: currentOrderId,
                        <?= $ci->security->get_csrf_token_name() ?>: '<?= $ci->security->get_csrf_hash() ?>'
                    },
                    success: function(resp) {
                        if (resp.status === 'success') {
                            location.reload(); // Reload to get new serverGuests and IDs
                        } else {
                            alert(resp.message || 'Error adding guest');
                        }
                    }
                });
            });

            $('#btn-minus').click(function() {
                if (!currentOrderId || currentOrderId === '') {
                    if(currentGuestCount > 0) {
                        currentGuestCount--;
                        $('#guest-count').text(currentGuestCount);
                        saveGuestCount(currentGuestCount);
                        updateTableGuestInfo();
                        renderGuestList(currentGuestCount);
                    }
                    return;
                }

                $.ajax({
                    url: '<?= base_url("Restaurant_Order_Taking/decrease_guest") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        order_id: currentOrderId,
                        <?= $ci->security->get_csrf_token_name() ?>: '<?= $ci->security->get_csrf_hash() ?>'
                    },
                    success: function(resp) {
                        if (resp.status === 'success') {
                            location.reload(); // Reload to sync
                        } else {
                            alert(resp.message || 'Error removing guest');
                        }
                    }
                });
            });

            // --- Calculator Logic ---
            const $overlay = $('#calc-overlay');
            const $display = $('#calc-display-val');
            let tempVal = '0';

                // Open Calculator
            $('#guest-count').click(function() {
                tempVal = currentGuestCount.toString();
                $display.text(tempVal);
                $overlay.css('display', 'flex'); // Flex to center/bottom
            });

            // Close (Click outside? optional)
            $overlay.on('click', function(e) {
                if(e.target === this) {
                    $overlay.hide();
                }
            });

            // Numpad clicks
            $('.btn-numpad').click(function() {
                const num = $(this).data('num');
                if (num === undefined) return; 

                if (tempVal === '0') {
                    tempVal = num.toString();
                } else {
                    tempVal += num.toString();
                }
                $display.text(tempVal);
            });

            // Clear
            $('#btn-calc-clear').click(function() {
                tempVal = '0';
                $display.text(tempVal);
            });
            
            // Backspace
            $('#btn-calc-backspace').click(function() {
                 if(tempVal.length > 1) {
                     tempVal = tempVal.slice(0, -1);
                 } else {
                     tempVal = '0';
                 }
                 $display.text(tempVal);
            });

            // Done
            $('#btn-calc-done').click(function() {
                const targetCount = parseInt(tempVal) || 0;
                
                if (!currentOrderId || currentOrderId === '') {
                    currentGuestCount = targetCount;
                    $('#guest-count').text(currentGuestCount);
                    saveGuestCount(currentGuestCount);
                    renderGuestList(currentGuestCount);
                    $overlay.hide();
                    return;
                }

                // If existing order, we need to sync multiple guests
                // To keep it simple, we'll reload after processing
                // Note: The controller handles one decrease at a time for safety
                // We'll just alert that for active orders, manual adjustment is preferred
                // OR we can implement a batch update. For now, since individual buttons exist:
                alert('For active orders, please use the +/- buttons for individual guest management.');
                $overlay.hide();
            });

            // --- Action Buttons ---

            // Add Item Button Logic
            $(document).on('click', '.btn-add-item', function(e) {
                // e.preventDefault(); // Optional, but usually good if button
                
                // Default to Guest 1 if no guest is explicitly selected and we have guests
                let targetGuestId = (typeof activeGuestId !== 'undefined' && activeGuestId !== null && activeGuestId !== 'null') ? activeGuestId : (currentGuestCount > 0 ? '1' : 'all');

                if (currentOrderId && currentOrderId !== '') {
                    window.location.href = '<?= base_url("Restaurant_Order_Taking/order_screen/") ?>' + currentOrderId + '?guest_id=' + targetGuestId;
                } else {
                    if (currentGuestCount <= 0) { alert('Please add at least 1 guest.'); return; }
                    
                    $.ajax({
                        url: '<?= base_url("Restaurant_Order_Taking/create_order") ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            table_id: currentTableId,
                            guest_count: currentGuestCount,
                            <?= $ci->security->get_csrf_token_name() ?>: '<?= $ci->security->get_csrf_hash() ?>'
                        },
                        success: function(resp) {
                            if (resp && resp.status === 'success' && resp.order_id) {
                                currentOrderId = resp.order_id;
                                window.location.href = '<?= base_url("Restaurant_Order_Taking/order_screen/") ?>' + currentOrderId + '?guest_id=' + targetGuestId;
                            } else {
                                alert(resp.message || 'Failed to create order');
                            }
                        },
                        error: function() {
                            alert('Error creating order');
                        }
                    });
                }
            });

            // Expose finalizeOrder to global scope for the onclick handler
           window.finalizeOrder = function() {
                if (orderStatus === 'Active' || !orderStatus) {
                    alert('Please send KOT to the kitchen before finalizing the order.');
                    return;
                }
            if (currentOrderId && currentOrderId !== '') {

                // Redirect to finalize screen page
                window.location.href = '<?= base_url("Restaurant_Order_Taking/finalize_screen/") ?>' + currentOrderId;

            } else {
                alert('No active order to finalize.');
            }
        };

            $('.btn-kot').click(function() {
                if (!currentOrderId || currentOrderId === '') {
                    alert('No active order to process.');
                    return;
                }

                if (orderStatus === 'Ready') {
                    // Mark as Served
                    $.ajax({
                        url: '<?= base_url("Restaurant_Order_Taking/mark_served") ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            order_id: currentOrderId,
                            <?= $ci->security->get_csrf_token_name() ?>: '<?= $ci->security->get_csrf_hash() ?>'
                        },
                        success: function(resp) {
                            if(resp.status === 'success') {
                                // Redirect to table section on success
                                window.location.href = '<?= base_url("Restaurant_Order_Taking/table/") ?>' + currentTableId + '/' + currentOrderId;
                            } else {
                                alert(resp.message || 'Error updating status to Served');
                            }
                        },
                        error: function() {
                            alert('Failed to process Served update');
                        }
                    });
                } else {
                    // Standard KOT logic
                    $.ajax({
                        url: '<?= base_url("Restaurant_Order_Taking/update_kot_status") ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            order_id: currentOrderId,
                            <?= $ci->security->get_csrf_token_name() ?>: '<?= $ci->security->get_csrf_hash() ?>'
                        },
                        success: function(resp) {
                            if(resp.status === 'success') {
                                // Redirect to table section on success
                                window.location.href = '<?= base_url("Restaurant_Order_Taking/table/") ?>' + currentTableId + '/' + currentOrderId;
                            } else {
                                alert(resp.message || 'Error updating table status');
                            }
                        },
                        error: function() {
                            alert('Failed to process KOT update');
                        }
                    });
                }
            });

            function updateKOTButtonUI() {
                const btn = $('.btn-kot');
                const finalizeBtn = $('.btn-finalize');
                if (orderStatus === 'Ready') {
                    btn.text('Served').removeClass('btn-kot-color').addClass('btn-served');
                    finalizeBtn.removeClass('disabled');
                } else {
                    btn.text('KOT').addClass('btn-kot-color').removeClass('btn-served');
                    if (orderStatus === 'Served' || orderStatus === 'Partial Served') {
                        finalizeBtn.removeClass('disabled');
                    } else {
                        // Keep it disabled if it's a completely new/unconfirmed order
                        // or customize based on your business logic. 
                        // For now, if we have items, we might want it enabled if they are all served.
                    }
                }
            }
            updateKOTButtonUI();
            
        });
    </script>
</body>
</html>