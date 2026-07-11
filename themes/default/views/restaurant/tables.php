<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title><?= isset($page_title) ? $page_title : 'Restaurant Tables' ?> - Restaurant</title>



    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">



    <link rel="stylesheet" href="<?= $assets ?>restaurant/assets/css/theme.css" />

    <link rel="stylesheet" href="<?= $assets ?>restaurant/assets/css/responsive.css" />



    <style>

        body {

            background: #f5f5f5;

            color: #2f3d4a;

            font-family: 'Segoe UI', Roboto, sans-serif;

        }



        #body-div {

            min-height: 100vh;

        }



        .main-content {

            max-width: 1200px;

        }



        .nav-tabs-container {

            background: #ffffff;

            border: 2px solid #e9176b;

            border-radius: 10px;

            padding: 0;

            overflow: hidden;

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



        .subsection-badge {

            display: inline-block;

            color: #2f3d4a;

            padding: 6px 14px;

            border-radius: 999px;

            font-size: 26px;

            font-weight: 700;

            margin-left: -12px;

            transition: all 0.2s ease;

        }

    

        .section-dropdown-container {

            background: transparent;

            padding: 0;

            margin: 20px 0;

            border-radius: 0;

            display: flex;

            align-items: center;

            gap: 16px;

            flex-direction: row;

            justify-content: center;

        }



        .section-dropdown-label {

            margin: 0;

            font-weight: 600;

            color: #000;

            white-space: nowrap;

            font-size: 25px;

        }



        .section-dropdown {

            width: 623px;

            padding: 0px 17px;

            border: 2px solid #e9176b;

            border-radius: 8px;

            background: #ffffff;

            outline: none;

            height: 42px;

            font-size: 25px;

            font-weight: 500;

        }



        .section-dropdown:focus {

            border-color: #e9176b;

            box-shadow: none;

        }



        .tables-title {

            text-align: center;

            font-weight: 700;

            font-size: 26px;

            margin: -24px 0 18px;

            color: #2f3d4a;

        }



        .table-grid {

            margin-top: 8px;

        }



        .table-card {

            border-radius: 12px;

            height: 110px;

            position: relative;

            display: flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            transition: all 0.2s ease;

            background: #ffffff;

            border: 2px solid #f0f0f0;

            overflow: hidden;

        }



        .table-card .table-number {

            font-size: 64px;

            font-weight: 800;

            line-height: 1;

            opacity: 0.35;

        }



        .table-card .table-status-pill {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 16px;
            font-weight: 700;
            padding: 4px 18px;
            border-radius: 999px;
            color: #111;
            background: rgba(255,255,255,0.4);
            text-transform: capitalize;
            white-space: nowrap;
            z-index: 5;
        }



        .table-card .table-time {
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            font-weight: 700;
            font-size: 16px;
            color: rgba(255,255,255,0.95);
            display: none;
        }



        .table-card .table-meta {

            position: absolute;

            bottom: 10px;

            right: 10px;

            color: rgba(255,255,255,0.9);

            font-weight: 700;

            display: none;

        }



        .table-card .table-guests {
            position: absolute;
            bottom: 8px;
            right: 12px;
            color: #fff;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 26px;
            line-height: 1;
        }

        .table-card .table-guests .bi-person-fill {
            font-size: 16px;
            margin-bottom: 2px;
        }



        .table-card .table-icon {

            position: absolute;

            top: 50%;

            left: 50%;

            transform: translate(-50%, -50%);

            font-size: 24px;

            color: #e9176b;

            display: none;

        }



        .table-card.has-guests .table-guests {

            display: flex;

        }



        .table-card.has-time .table-time {

            display: block;

        }



        .table-card.has-icon .table-icon {

            display: block;

        }

        .table-receipt-icon {
            position: absolute;
            bottom: 10px;
            left: 10px;
            font-size: 18px;
            color: rgba(255,255,255,1);
            z-index: 10;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 4px 8px;
            transition: scale 0.2s ease;
        }
        .table-receipt-icon:hover {
            scale: 1.1;
            background: rgba(255,255,255,0.4);
        }
        .table-card.has-guests .table-number {

            font-size: 48px;

            opacity: 0.3;

        }



        .table-card.has-time .table-number {

            font-size: 75px;

            opacity: 0.3;

        }



        .table-card.has-icon .table-number {

            font-size: 5rem;

            opacity: 0.3;

        }



        .table-card.status-available {

            background: #ffffff;

        }



        .table-card.status-available .table-number {

            color: #e9176b;

        }



        .table-card.status-available .table-status-pill {

            color: #333;

        }



        .table-card.status-free {

            background: #ff6b6b;

        }



        .table-card.status-free .table-number {

            color: rgba(255,255,255,0.95);

        }



        .table-card.status-free .table-status-pill {
            background:rgba(255, 255, 255, 0.4) !important;
            color: #000000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }



        .table-card.status-reserved {

            background: #ffd56a;

        }



        .table-card.status-reserved .table-number {

            color: #e9176b;

        }



        .table-card.status-reserved .table-status-pill {

            color: #7a4b00;

        }



        .table-card.status-occupied {

            background: #aeecff;

        }



        .table-card.status-occupied .table-number {

            /* color: rgba(255,255,255,0.95); */
            color: #e9176b;
        }



        .table-card.status-occupied .table-status-pill {

            color: #004a70;

        }



        .table-card.status-order-placed {

            background: #ff7eb6;

        }



        .table-card.status-order-placed .table-number {

            color: rgba(255,255,255,0.95);

        }



        .table-card.status-order-placed .table-status-pill {

            color: #7a0030;

        }



        .table-card.status-ready {

            background: #62d26f;

        }



        .table-card.status-ready .table-number {
            color: rgba(0,0,0,0.15);
            font-size: 90px;
        }



        .table-card.status-ready .table-status-pill {

            color: #0b5b15;

        }

        /* Ready Icon Container - Cloche icon without white circle */
        .ready-icon-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            width:40%;
            height: 40%;
        }

        .ready-icon-container img {
            width: 100%;
            height: auto;
            max-height: 80px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        /* Plus Icon Container - Just plus icon without circle */
        .plus-icon-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .plus-icon-container i {
            color: white;
            font-size: 32px;
            font-weight: bold;
        }

        .section-dropdown-label {

                font-size: 18px;

            }



            .table-card .table-number {

                font-size: 75px;

            }

            .table-card .table-status-pill {
                font-size: 14px;
                top:50%;
                bottom: auto;
                transform: translate(-50%, -50%);
            }

        .table-card.status-served {
            background: #ff7eb6;
        }

        .table-card.status-served .table-number {
            color: rgba(255,255,255,0.95);
        }

        .table-card.status-served .table-status-pill {
            color: #7a0030;
        }

    </style>

</head>

<body>

    <div class="container-fluid p-0 m-0" id="body-div">

        <?php include('partials/header.php'); ?>



        <div class="container-xl mt-4 main-content">

            <div class="nav-tabs-container">

                <div class="nav-tabs-custom">

                    <div class="nav-tab-custom" onclick="window.location.href='<?= base_url('Restaurant_Order_Taking') ?>'" data-tab="sections">Sections</div>

                    <div class="nav-tab-custom active" data-tab="tables">Tables</div>

                    <div class="nav-tab-custom" onclick="window.location.href='<?= base_url('Restaurant_Order_Taking/orders') ?>'" data-tab="orders">Orders</div>

                </div>

            </div>



            <div class="section-dropdown-container">

                <!-- <div class="section-dropdown-label">Select Section:</div> -->

                <select class="section-dropdown" id="section-dropdown" style="display: none;">

                    <?php if (!empty($sections)): ?>

                        <?php foreach ($sections as $sec): ?>

                            <option value="<?= $sec->id ?>" <?= (isset($section_id) && (int)$section_id === (int)$sec->id) ? 'selected' : '' ?>>

                                <?= htmlspecialchars($sec->name) ?>

                            </option>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <option value="">No sections available</option>

                    <?php endif; ?>

                </select>

            </div>



            <div class="section-dropdown-container" id="subsection-dropdown-container" style="display:none;">

                <div class="section-dropdown-label">Select Sub-Section:</div>

                <select class="section-dropdown" id="subsection-dropdown"></select>

            </div>



            <!-- Subsection cards (populated dynamically) -->

            <div class="row mb-4" id="subsections-section" style="display:none;">

                <div class="col-12">

                    <div class="row" id="subsections-container"></div>

                </div>

            </div>



            <div class="tables-title" id="tables-title">

                <?= isset($section) && $section ? htmlspecialchars($section->name) : '' ?>

                <span id="subsection-badges"></span>

            </div>



            <div class="row table-grid" id="tables-container"></div>



            <div class="loading-spinner" id="tables-loading" style="display:none;">

                <div class="spinner-border text-primary" role="status">

                    <span class="visually-hidden">Loading...</span>

                </div>

            </div>

    </div>
    <!-- Free Table Confirmation Modal -->
    <div class="modal fade" id="freeTableModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-body text-center p-5">
                    <h4 class="mb-5" style="color: #333; line-height: 1.4; ">Would you like to free <span id="freeTableNumber" class="text-brand" style="color: black;"></span>?</h4>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn px-5 py-2" data-bs-dismiss="modal" style="border-radius: 12px; border: 2px solid #e91e63; color: #e91e63; background: white; font-weight: 700; min-width: 140px;">Cancel</button>
                        <button type="button" id="confirmFreeTableBtn" class="btn px-5 py-2" style="border-radius: 12px; background-color: #e91e63; color: white; border: none; font-weight: 700; min-width: 140px;">Yes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>



    <script>

        <?php $ci =& get_instance(); $csrf = $ci->security; ?>

        window.site = { base_url: '<?= base_url() ?>' };
        const CSRF_TOKEN_NAME = '<?= $csrf->get_csrf_token_name(); ?>';
        let CSRF_TOKEN_HASH = '<?= $csrf->get_csrf_hash(); ?>';



        $.ajaxSetup({

            beforeSend: function(xhr, settings) {

                if ((settings.type || '').toUpperCase() === 'POST') {

                    if (typeof settings.data === 'string') {

                        settings.data += (settings.data ? '&' : '') + encodeURIComponent(CSRF_TOKEN_NAME) + '=' + encodeURIComponent(CSRF_TOKEN_HASH);

                    } else if (settings.data && typeof settings.data === 'object') {

                        settings.data[CSRF_TOKEN_NAME] = CSRF_TOKEN_HASH;

                    } else {

                        settings.data = encodeURIComponent(CSRF_TOKEN_NAME) + '=' + encodeURIComponent(CSRF_TOKEN_HASH);

                    }

                }

            }

        });



        function normalizeStatus(label) {

            if (!label) return 'available';

            const s = ('' + label).trim().toLowerCase();

            if (['free','checkout','settled','closed','billed','paid','finalized','finalised','confirmorder','confirmed','finalised_bill','bill finalized','bill_finalized'].indexOf(s) !== -1) return 'free';

            if (['available','vacant','empty','open'].indexOf(s) !== -1) return 'available';

            if (['occupied','dining','busy','in use','in_use','active','running','engaged'].indexOf(s) !== -1) return 'occupied';

            if (['reserved','booking','booked','blocked','hold','on hold','on_hold'].indexOf(s) !== -1) return 'reserved';

            if (['order placed','placed','order_placed','queued','in queue','kitchen','preparing','cooking','in_kitchen','processing'].indexOf(s) !== -1) return 'order-placed';

            if (['order ready','ready','order_ready','prepared','prepped','packed','food ready','meal ready','ready to serve'].indexOf(s) !== -1) return 'ready';
            if (['served', 'order served', 'delivered'].indexOf(s) !== -1) return 'served';
            return s.replace(/\s+/g, '-');

        }



        function renderTables(tables) {

            let html = '';

            if (!tables || !tables.length) {

                html = '<div class="col-12"><div class="alert alert-info text-center">No tables found for this section.</div></div>';

                $('#tables-container').html(html);

                return;

            }



            tables.forEach(function(t) {

                const label = t.status_name || 'Available';

                const key = normalizeStatus(label);

                

                // Determine additional display information

                let guestCount = '';

                let timeDisplay = '';

                let tableIcon = '';

                let extraClasses = '';

                

                if (t.has_order && t.guest_count > 0) {
                    guestCount = `<i class="bi bi-person-fill"></i><div>${t.guest_count}</div>`;
                    extraClasses += ' has-guests';
                }

                
                if (t.order_time) {

                    const orderDate = new Date(t.order_time);

                    timeDisplay = orderDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });

                    extraClasses += ' has-time';

                }

                
                // Determine icon based on status

                if (key === 'occupied' && t.has_order) {
                    // Show plus icon for occupied tables with orders
                    tableIcon = '<div class="plus-icon-container"><i class="bi bi-plus-lg"></i></div>';
                    extraClasses += ' has-icon';
                } else if (key === 'free') {

                    tableIcon = '<i class="bi bi-receipt table-receipt-icon" title="View Order"></i>';

                    extraClasses += ' has-receipt';

                } else if (key === 'reserved') {

                    tableIcon = '<i class="bi bi-clock-fill table-icon"></i>';

                    extraClasses += ' has-icon';

                } else if (key === 'ready') {
                    tableIcon = `<div class="ready-icon-container"><img src="${window.site.base_url}themes/default/assets/restaurant/images/served.svg"></div>`;
                    extraClasses += ' has-icon';
                } else if (key === 'order-placed' || key === 'served') {
                    tableIcon = '<div class="plus-icon-container"><i class="bi bi-plus-lg"></i></div>';
                    extraClasses += ' has-icon';
                }

                

                html += `

                    <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 col-6 mb-3">

                        <div class="table-card status-${key}${extraClasses}" data-table-id="${t.id}">

                            <div class="table-guests">${guestCount}</div>

                            <div class="table-time">${timeDisplay}</div>

                            <div class="table-number">${t.name}</div>
                            ${['available', 'free'].includes(key) ? `<div class="table-status-pill">${label}</div>` : ''}
                            ${tableIcon}

                            <div class="table-meta">${t.items_count > 0 ? t.items_count : ''}</div>

                        </div>

                    </div>

                `;

            });



            $('#tables-container').html(html);

        }



        function loadTablesAjax(sectionId, subsectionId) {

            $('#tables-loading').show();

            $.ajax({

                url: '<?= base_url('Restaurant_Order_Taking/load_tables') ?>',

                type: 'POST',

                dataType: 'json',

                data: { section_id: sectionId, subsection_id: subsectionId || '' },

                success: function(resp) {

                    $('#tables-loading').hide();

                    if (resp && resp.status === 'success') {

                        renderTables(resp.tables || []);

                    } else {

                        renderTables([]);

                    }

                },

                error: function() {

                    $('#tables-loading').hide();

                    renderTables([]);

                }

            });

        }



        function loadSubsectionsAjax(sectionId, preselectSubId) {

            $('#subsections-section').hide();

            $('#subsections-container').empty();



            $.ajax({

                url: '<?= base_url('Restaurant_Order_Taking/load_subsections') ?>',

                type: 'POST',

                dataType: 'json',

                data: { section_id: sectionId },

                success: function(resp) {

                    const subs = (resp && resp.status === 'success' && resp.subsections) ? resp.subsections : [];



                    if (!subs.length) {

                        $('#subsections-section').hide();

                        $('#subsection-badges').empty();

                        loadTablesAjax(sectionId, '');

                        return;

                    }



                    // Show only the selected subsection badge

                    const effectiveSubId = preselectSubId ? preselectSubId : (subs[0] ? subs[0].id : '');

                    const selectedSub = subs.find(s => ('' + s.id) === ('' + effectiveSubId));



                    if (selectedSub) {

                        $('#subsection-badges').html(`

                            - <span class="subsection-badge active" data-subsection-id="${selectedSub.id}">

                                ${selectedSub.name || ''}

                            </span>

                        `);

                    } else {

                        $('#subsection-badges').empty();

                    }



                    loadTablesAjax(sectionId, effectiveSubId);

                },

                error: function() {

                    $('#subsections-section').hide();

                    $('#subsection-badges').empty();

                    loadTablesAjax(sectionId, '');

                }

            });

        }



        $('#section-dropdown').on('change', function() {

            const sectionId = $(this).val();

            if (sectionId) {

                loadSubsectionsAjax(sectionId, '');

            }

        });



        $(document).ready(function() {

            const currentSection = $('#section-dropdown').val();

            if (currentSection) {

                const currentSubsection = '<?= isset($subsection_id) ? (string)$subsection_id : '' ?>';

                loadSubsectionsAjax(currentSection, currentSubsection || null);

            }

        });



        $(document).on('click', '.table-status-pill', function(e) {
            const card = $(this).closest('.table-card');
            if (card.hasClass('status-free') || card.hasClass('status-reserved')) {
                e.stopPropagation();
                const tableId = card.data('table-id');
                const tableName = card.find('.table-number').text();

                // Formatting name to match user's screenshot "Table #2"
                $('#freeTableNumber').text('Table #' + tableName);
                $('#confirmFreeTableBtn').data('table-id', tableId);
                const freeModal = new bootstrap.Modal(document.getElementById('freeTableModal'));
                freeModal.show();
            }
        });

        $(document).on('click', '.table-card', function() {
            const tableId = $(this).data('table-id');
            if (tableId) {
                window.location.href = '<?= base_url("Restaurant_Order_Taking/orders/") ?>' + tableId;
            }
        });

        $(document).on('click', '.table-receipt-icon', function(e) {
            e.stopPropagation();
            const tableId = $(this).closest('.table-card').data('table-id');
            if (tableId) {
                window.location.href = '<?= base_url("Restaurant_Order_Taking/orders/") ?>' + tableId + '?view=summary';
            }
        });

        $('#confirmFreeTableBtn').on('click', function() {
            const tableId = $(this).data('table-id');
            const btn = $(this);
            btn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: '<?= base_url("Restaurant_Order_Taking/free_table") ?>',
                type: 'POST',
                dataType: 'json',
                data: { 
                    table_id: tableId,
                    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                },
                success: function(resp) {
                    btn.prop('disabled', false).text('Yes');
                    if (resp && resp.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('freeTableModal')).hide();
                        // Reload tables to show Available status
                        const currentSection = $('#section-dropdown').val();
                        const activeSub = $('#subsection-badges .active');
                        const subId = activeSub.length ? activeSub.data('subsection-id') : null;
                        loadTablesAjax(currentSection, subId);
                    } else {
                        alert(resp.message || 'Error occurred');
                    }
                },
                error: function(xhr, status, error) {
                    btn.prop('disabled', false).text('Yes');
                    let msg = 'Failed to process request: ' + error;
                    if (xhr.responseText) {
                        // Extract first 100 characters of response to avoid huge alerts
                        msg += '\nResponse: ' + xhr.responseText.substring(0, 300);
                    }
                    alert(msg);
                }
            });
        });

        // Auto-change table color to green (Ready) after 15 seconds
        const autoReadyOrderId = '<?= isset($auto_ready_order) ? $auto_ready_order : "" ?>';
        if (autoReadyOrderId) {
            setTimeout(function() {
                $.ajax({
                    url: '<?= base_url("Restaurant_Order_Taking/mark_ready") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: { order_id: autoReadyOrderId },
                    success: function(resp) {
                        if (resp && resp.status === 'success') {
                            const currentSection = $('#section-dropdown').val();
                            if (currentSection) {
                                const activeSub = $('#subsection-badges .active');
                                const subId = activeSub.length ? activeSub.data('subsection-id') : null;
                                loadTablesAjax(currentSection, subId);
                            }
                        }
                    }
                });
            }, 15000); // 15 seconds delay
        }

    </script>

</body>

</html>