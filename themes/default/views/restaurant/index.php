<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $page_title ?> - Restaurant Order Taking</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $assets ?>restaurant/assets/css/theme.css" />
    <link rel="stylesheet" href="<?= $assets ?>restaurant/assets/css/responsive.css" />
    
    <style>
        :root {
            --primary-color: #e9176b;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        } 

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



        .section-card {
            background: #ffffff;
            border: 2px solid #e9176b;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: none;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 600;
            color: #e9176b;
        }

        .section-card:hover,
        .section-card.active {
            background: #e9176b;
            color: #ffffff;
            transform: none;
            box-shadow: none;
        }

        .section-card h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .section-card p {
            display: none;
        }

        .subsection-card {
            background: #ffffff;
            border: 2px solid #e9176b;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: none;
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            color: #e9176b;
            width: 100%;
            max-width: 316px;
            margin: 4px auto 0;
        }

        .subsection-card:hover,
        .subsection-card.active {
            background: #e9176b;
            color: #ffffff;
            transform: none;
            box-shadow: none;
        }

        .table-card {
            background: #ffffff;
            border: 2px solid #e9176b!important;
            border-radius: 12px;
            padding: 30px;
            cursor: pointer;
            transition: all 0.22s ease;
            box-shadow: none;
            position: relative;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            color: #e9176b;
            width: 316px;
            margin-top: 4px;
        }

        .table-card::before {
            display: none;
        }

        .table-card:hover {
            background: #e9176b;
            color: #ffffff;
            transform: none;
            box-shadow: none;
        }

        .table-available::before {
            display: none;
        }

        .table-occupied::before {
            display: none;
        }

        .table-reserved::before {
            display: none;
        }

        .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .table-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: #273349;
        }

        .status-icon-top {
            font-size: 24px;
            opacity: 0.7;
        }

        .table-info {
            margin: 10px 0;
            color: #5a6a85;
        }

        .table-capacity,
        .table-location {
            font-size: 0.95rem;
            margin-bottom: 6px;
        }

        .table-status-badge .badge {
            font-size: 0.9rem;
            padding: 6px 14px;
            border-radius: 999px;
            font-weight: 600;
        }

        .reserved-details .countdown,
        .countdown {
            color: #c0392b;
            font-weight: 600;
            font-size: 0.9rem;
            background: rgba(192, 57, 43, 0.08);
            padding: 4px 10px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .table-actions .btn {
            font-size: 0.95rem;
            padding: 9px 16px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
        }

        .table-actions .btn i {
            margin-right: 6px;
        }
        
        /* === Tabs container outer shell (Image-1 look) === */
        .nav-tabs-container {
            background: #ffffff;
            border: 2px solid #e9176b;
            border-radius: 10px;
            padding: 0;
            overflow: hidden;
        }

        /* ===== Tabs wrapper ===== */
        .nav-tabs-custom {
            display: flex;
        }

        /* ===== Each tab ===== */
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
        }

        /* Remove last divider */
        .nav-tab-custom:last-child {
            border-right: none;
        }

        /* ===== Active tab ===== */
        .nav-tab-custom.active {
            background: #e9176b;
            color: #ffffff;
        }

        /* ===== Hover effect ===== */
        .nav-tab-custom:hover {
            background: #e9176b;
            color: #ffffff;
        }


        /* Section Dropdown */
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

        /* === Inline label === */
        .section-dropdown-label {
            margin: 0;
            font-weight: 600;
            color: #000;
            white-space: nowrap;
            font-size: 25px;
        }

        /* === Dropdown styling (Image-2 look) === */
        .section-dropdown {
            width: 623px;
            padding: 0px 17px;
            border: 2px solid #e9176b;
            border-radius: 8px;
            font-size: 1rem;
            background: #ffffff;
            outline: none;
            height: 42px;
            font-size: 25px;
            font-weight: 500;
        }

        /* Optional: focus state */
        .section-dropdown:focus {
            border-color: #e9176b;
            box-shadow: none;
        }


        /* Hide original section cards */
        #sections-container {
            display: none;
        }

        .table-card-header,
        .table-info,
        .table-status-badge,
        .reserved-details,
        .table-actions {
            display: none;
        }

        .table-name {
            font-size: 5rem;
            font-weight: 500;
            margin: 0;
            color: inherit;
            display: block !important;
        }

        .back-button,
        .btn-secondary {
            border-radius: 999px;
            font-weight: 600;
            padding: 10px 22px;
        }
        
        .tables-header-bar {
            background: #ffffff;
            border: 1px solid #e5e9f2;
            border-radius: 14px;
            padding: 12px 18px;
            box-shadow: 0 6px 16px rgba(15, 20, 35, 0.06);
        }

        .tables-header-bar .back-button {
            border-radius: 999px;
            padding: 8px 14px;
        }

        #legend-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        #legend-container .legend-chip {
            background: #fff;
            border: 1px solid #e5e9f2;
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 6px 16px rgba(15, 20, 35, 0.1);
        }

        #legend-container .legend-chip i {
            font-size: 1rem;
        }

        @media (max-width: 1200px) {
            .table-card {
                min-height: 180px;
            }
        }

        @media (max-width: 768px) {
            .section-card {
                padding: 22px;
                margin-bottom: 16px;
            }

            .table-card {
                padding: 20px 16px;
                min-height: 150px;
            }

            .table-name {
                font-size: 1.35rem;
            }

            .table-actions .btn {
                font-size: 0.9rem;
                padding: 8px 14px;
            }
        }

        @media (max-width: 576px) {
            .table-card {
                padding: 18px 14px;
                min-height: 140px;
            }

            .table-actions .btn {
                font-size: 0.88rem;
            }

            .navbar-brand {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include('partials/header.php'); ?>
    <div class="container-fluid p-0 m-0" id="body-div">

        <!-- Main Content -->
        <div class="container-xl mt-4 main-content">
            <!-- Navigation Tabs -->
            <div class="nav-tabs-container">
                <div class="nav-tabs-custom">
                    <div class="nav-tab-custom active" data-tab="sections">
                        Sections
                    </div>
                    <div class="nav-tab-custom" data-tab="tables">
                        Tables
                    </div>
                    <div class="nav-tab-custom" data-tab="orders">
                        Orders
                    </div>
                </div>
            </div>

            <!-- Section Selection Dropdown -->
            <div class="section-dropdown-container">
                <div class="section-dropdown-label">Select Section:</div>
                <select class="section-dropdown" id="section-dropdown" onchange="loadTablesFromDropdown()">
                    <?php if (!empty($sections)): ?>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= $section->id ?>"><?= htmlspecialchars($section->name) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No sections available</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Subsection cards (populated dynamically) -->
            <div class="row mb-4" id="subsections-section" style="display:none;">
                <div class="col-12">
                    <div class="row" id="subsections-container"></div>
                </div>
            </div>

            <!-- Section Selection (Hidden) -->
            <div class="row mb-4">
                <div class="col-12">
                    
                    <div class="row" id="sections-container">
                        <?php if (!empty($sections)): ?>
                            <?php foreach ($sections as $section): ?>
                                <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12">
                                    <div class="section-card" data-section-id="<?= $section->id ?>" onclick="loadSubsections(<?= $section->id ?>, null)">
                                        <h4 class="mb-2">
                                            <i class="bi bi-door-open"></i> <?= htmlspecialchars($section->name) ?>
                                        </h4>
                                        <p class="mb-0">Click to view tables</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle"></i> No sections available. Please contact administrator.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tables Display -->
            <div class="row mb-4" id="tables-section" style="display: none;">
                <div class="col-12">
                    <!-- <div class="tables-header-bar d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-outline-secondary back-button" onclick="backToSections()">
                                <i class="bi bi-arrow-left"></i>
                            </button>
                            <h3 id="section-title" class="mb-0">
                                <i class="bi bi-table"></i> Tables
                            </h3>
                        </div>
                    </div> -->
                    
                    
                    <div class="loading-spinner" id="tables-loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading tables...</p>
                    </div>
                    
                    <div class="row" id="tables-container">
                        <!-- Tables will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Reservation Details Modal -->
    <div class="modal fade" id="viewReservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Reservation Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="reservation-details">
                        <div class="mb-3">
                            <h5 id="reservationTableName" class="text-center mb-4"></h5>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-clock fs-5 me-2 text-primary"></i>
                                <div>
                                    <div class="fw-bold">Reserved Until</div>
                                    <div id="reservationTime" class="text-muted"></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person fs-5 me-2 text-primary"></i>
                                <div>
                                    <div class="fw-bold">Reserved By</div>
                                    <div id="reservationBy" class="text-muted"></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-2">
                                <i class="bi bi-chat-left-text fs-5 me-2 mt-1 text-primary"></i>
                                <div>
                                    <div class="fw-bold">Notes</div>
                                    <div id="reservationNoteDisplay" class="text-muted"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="unreserveBtn">
                        <i class="bi bi-x-circle"></i> Unreserve Table
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reservation Modal -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Reserve Table</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reservationForm">
                        <input type="hidden" name="table_id" id="reserveTableId">
                        <input type="hidden" name="table_name" id="reserveTableName">
                        
                        <div class="mb-3">
                            <label for="reservedBy" class="form-label">Reserved By</label>
                            <input type="text" class="form-control" id="reservedBy" name="reserved_by" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reservedUntil" class="form-label">Reserved Until</label>
                            <input type="datetime-local" class="form-control" id="reservedUntil" name="reserved_until" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reservationNoteInput" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="reservationNoteInput" name="reserved_note" rows="3"></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Confirm Reservation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Guest Count Modal -->
    <div class="modal fade" id="guestModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-people"></i> Enter Guest Count
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Table: <span id="selected-table-name" class="fw-bold"></span></p>
                    <p>Capacity: <span id="selected-table-capacity" class="fw-bold"></span> guests</p>
                    
                    <div class="row justify-content-center mt-4">
                        <div class="col-8">
                            <label for="guest-count" class="form-label">Number of Guests:</label>
                            <input type="number" class="form-control text-center" id="guest-count" min="1" max="20" value="1">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="button" class="btn btn-primary btn-lg" onclick="createOrder()">
                            <i class="bi bi-plus-circle"></i> Start Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reserve Table Modal -->
    <div class="modal fade" id="reserveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-bookmark-plus"></i> Reserve Table <span id="reserve-table-name" class="fw-bold"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="reserve-table-id" value="">
                    <div class="row g-3">
                        <div class="col-6">
                            <label for="reserve-date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="reserve-date">
                        </div>
                        <div class="col-6">
                            <label for="reserve-time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="reserve-time" step="60">
                        </div>
                        <div class="col-12">
                            <label for="reserve-name" class="form-label">Name / Contact</label>
                            <input type="text" class="form-control" id="reserve-name" placeholder="e.g., Mr. Sharma">
                        </div>
                        <div class="col-12">
                            <label for="reserve-note" class="form-label">Note</label>
                            <textarea id="reserve-note" class="form-control" rows="2" placeholder="Optional note"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitReservation()"><i class="bi bi-check2-circle"></i> Reserve</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        // CSRF setup for all AJAX POST requests
        <?php $ci =& get_instance(); $csrf = $ci->security; ?>
        const CSRF_TOKEN_NAME = '<?= $csrf->get_csrf_token_name(); ?>';
        let CSRF_TOKEN_HASH = '<?= $csrf->get_csrf_hash(); ?>';
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if ((settings.type || '').toUpperCase() === 'POST') {
                    if (typeof settings.data === 'string') {
                        settings.data += (settings.data ? '&' : '') +
                            encodeURIComponent(CSRF_TOKEN_NAME) + '=' + encodeURIComponent(CSRF_TOKEN_HASH);
                    } else if (settings.data && typeof settings.data === 'object') {
                        settings.data[CSRF_TOKEN_NAME] = CSRF_TOKEN_HASH;
                    } else {
                        settings.data = encodeURIComponent(CSRF_TOKEN_NAME) + '=' + encodeURIComponent(CSRF_TOKEN_HASH);
                    }
                }
            }
        });
        let selectedTableId = null;
        let selectedSectionId = null;
        let selectedSubsectionId = '';
        let TABLE_STATUSES = [];
        const COLOR_TEXT_CACHE = {};

        function getReadableTextColor(color) {
            if (!color) { return '#000000'; }
            const key = String(color).toLowerCase();
            if (COLOR_TEXT_CACHE[key]) { return COLOR_TEXT_CACHE[key]; }
            const temp = document.createElement('span');
            temp.style.color = color;
            temp.style.display = 'none';
            document.body.appendChild(temp);
            const computed = window.getComputedStyle(temp).color;
            document.body.removeChild(temp);
            const rgb = computed.match(/\d+/g);
            let textColor = '#000000';
            if (rgb && rgb.length >= 3) {
                const r = parseInt(rgb[0], 10) || 0;
                const g = parseInt(rgb[1], 10) || 0;
                const b = parseInt(rgb[2], 10) || 0;
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                textColor = luminance > 0.6 ? '#000000' : '#FFFFFF';
            }
            COLOR_TEXT_CACHE[key] = textColor;
            return textColor;
        }

        function normalizeStatus(label) {
            if (!label) return 'Available';
            const s = ('' + label).trim().toLowerCase();
            if ([
                'order ready','ready','order_ready','prepared','prepped','packed','food ready','meal ready','ready to serve'
            ].indexOf(s) !== -1) return 'Ready';
            if ([
                'order placed','placed','order_placed','queued','in queue','kitchen','preparing','cooking','in_kitchen','processing'
            ].indexOf(s) !== -1) return 'Order Placed';
            if ([
                'occupied','dining','busy','in use','in_use','active','running','engaged'
            ].indexOf(s) !== -1) return 'Occupied';
            if ([
                'reserved','booking','booked','blocked','hold','on hold','on_hold'
            ].indexOf(s) !== -1) return 'Reserved';
            if ([
                'served','delivered','completed','done'
            ].indexOf(s) !== -1) return 'Served';
            if ([
                'free','checkout','settled','closed','billed','paid','finalized','finalised','confirmorder','confirmed','finalised_bill','bill finalized','bill_finalized'
            ].indexOf(s) !== -1) return 'Free';
            if ([
                'available','vacant','empty','open'
            ].indexOf(s) !== -1) return 'Available';
            return label; // fallback original
        }

        function getStatusMeta(statusLabelOrId) {
            if (!TABLE_STATUSES || !TABLE_STATUSES.length) { return null; }
            let targetId = null;
            let targetLabel = null;
            if (typeof statusLabelOrId === 'number') {
                targetId = parseInt(statusLabelOrId, 10);
            } else if (statusLabelOrId !== undefined && statusLabelOrId !== null) {
                targetLabel = normalizeStatus(statusLabelOrId);
            }
            for (let i = 0; i < TABLE_STATUSES.length; i++) {
                const st = TABLE_STATUSES[i] || {};
                if (targetId !== null && parseInt(st.id, 10) === targetId) {
                    return st;
                }
                const raw = (st.value || st.name || st.label || '').toString();
                if (!raw) { continue; }
                const norm = normalizeStatus(raw);
                if (targetLabel && norm === targetLabel) {
                    return st;
                }
            }
            return null;
        }

        function statusVisuals(statusLabelOrId) {
            // Accept label (preferred) or numeric fallback
            let norm = typeof statusLabelOrId === 'number' ? null : normalizeStatus(statusLabelOrId);
            // Fallback by common IDs if label missing
            if (!norm && typeof statusLabelOrId === 'number') {
                if (statusLabelOrId == 1) norm = 'Available';
                else if (statusLabelOrId == 2) norm = 'Occupied';
                else if (statusLabelOrId == 3) norm = 'Reserved';
            }
            norm = norm || 'Available';
            const map = {
                'Available': { cardClass: 'table-available', icon: 'check-circle', badgeClass: 'bg-success', defaultColor: '#ffffff' },
                'Occupied': { cardClass: 'table-occupied', icon: 'x-circle', badgeClass: 'bg-danger', defaultColor: '#d1e7ff' },
                'Reserved': { cardClass: 'table-reserved', icon: 'clock', badgeClass: 'bg-warning text-dark', defaultColor: '#fff3cd' },
                'Order Placed': { cardClass: 'table-reserved', icon: 'plus-lg', badgeClass: 'bg-info text-dark', defaultColor: '#f8d7da' },
                'Ready': { cardClass: 'table-available', icon: 'egg-fried', badgeClass: 'bg-success', defaultColor: '#d1e7dd' },
                'Free': { cardClass: 'table-available', icon: 'receipt', badgeClass: 'bg-primary', defaultColor: '#f5c6cb' },
                'Served': { cardClass: 'table-available', icon: 'emoji-smile', badgeClass: 'bg-secondary', defaultColor: '#f8d7da' },
            };
            const vis = map[norm] ? { ...map[norm] } : { cardClass: 'table-available', icon: 'question-circle', badgeClass: 'bg-secondary', defaultColor: '#ffffff' };
            const meta = getStatusMeta(typeof statusLabelOrId === 'number' ? statusLabelOrId : norm);
            const colorValue = meta && meta.color ? meta.color : vis.defaultColor;
            if (colorValue) {
                vis.cardColor = colorValue;
                vis.badgeColor = colorValue;
                vis.badgeTextColor = getReadableTextColor(colorValue);
            }
            return vis;
        }

        function buildLegend() {
            if (!TABLE_STATUSES || !TABLE_STATUSES.length) return;
            const html = TABLE_STATUSES.map(function(st) {
                const label = normalizeStatus(st.value || st.name || st.label || '');
                const vis = statusVisuals(label);
                const bg = vis.badgeColor || vis.cardColor || '';
                const textColor = vis.badgeTextColor || '#000000';
                const styleAttr = bg ? ` style="background:${bg}; color:${textColor}; border-color:${bg};"` : '';
                return `<span class="legend-chip"${styleAttr}><i class="bi bi-${vis.icon}"></i> ${label}</span>`;
            }).join('\n');
            $('#legend-container').html(html);
        }

        function loadStatuses(callback) {
            $.ajax({
                url: '<?= base_url("Restaurant_Order_Taking/table_statuses") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(resp) {
                    if (resp && resp.status === 'success' && resp.data) {
                        TABLE_STATUSES = resp.data;
                        buildLegend();
                    }
                    if (typeof callback === 'function') callback();
                },
                error: function() { if (typeof callback === 'function') callback(); }
            });
        }
        
        function loadTables(sectionId, subsectionId) {
            selectedSectionId = sectionId;
            selectedSubsectionId = subsectionId || '';
            window.location.href = '<?= base_url("Restaurant_Order_Taking/tables/") ?>' + sectionId + (selectedSubsectionId ? '/' + selectedSubsectionId : '');
        }
        
        // Function to show reservation details in a modal
        function showReservationDetails(reservation) {
            if (!reservation) return;
            
            // Format the reservation time
            let reservedTime = '';
            let timeRemaining = '';
            
            if (reservation.reserved_until) {
                const date = new Date(reservation.reserved_until);
                reservedTime = date.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                // Calculate time remaining
                const now = new Date();
                const diff = date - now;
                if (diff > 0) {
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    timeRemaining = `${hours}h ${minutes}m`;
                } else {
                    timeRemaining = 'Expired';
                }
            }
            
            // Update modal content with better formatting
            $('#reservationTableName').html(`
                <i class="bi bi-table"></i> Table: ${reservation.name || ''}
                ${timeRemaining ? `<span class="badge bg-primary ms-2">${timeRemaining}</span>` : ''}
            `);
            
            $('#reservationTime').html(`
                <i class="bi bi-calendar2-check"></i> ${reservedTime || '<em>Not specified</em>'}
            `);
            
            $('#reservationBy').html(`
                <i class="bi bi-person-fill"></i> ${reservation.reserved_by || 'Not specified'}
            `);
            
            $('#reservationNoteDisplay').html(`
                <div class="bg-light p-3 rounded">
                    ${reservation.reserved_note ? reservation.reserved_note : '<em class="text-muted">No notes provided</em>'}
                </div>
            `);
            
            // Set up unreserve button
            const unreserveBtn = $('#unreserveBtn');
            unreserveBtn.off('click').on('click', function() {
                unreserveTable(reservation.id, true);
                const modal = bootstrap.Modal.getInstance(document.getElementById('viewReservationModal'));
                modal.hide();
            });
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('viewReservationModal'));
            modal.show();
        }
        
        function selectTable(tableId, tableName, capacity, statusRef) {
            // If this is a reserved table, show reservation details
            const table = Array.from($('.table-card')).find(el => $(el).data('table-id') == tableId);
            if (table) {
                const isReserved = $(table).hasClass('table-reserved') || $(table).hasClass('table-reserved');
                if (isReserved) {
                    const tableData = $(table).data();
                    showReservationDetails({
                        id: tableId,
                        name: tableName,
                        reserved_until: tableData.reservedUntil,
                        reserved_by: tableData.reservedBy,
                        reserved_note: tableData.reservedNote
                    });
                    return;
                }
            }
            selectedTableId = tableId;
            const statusLabel = typeof statusRef === 'string' ? normalizeStatus(statusRef) : null;
            const isOccupied = statusLabel ? (statusLabel === 'Occupied') : (parseInt(statusRef) === 2);
            const isReserved = statusLabel ? (statusLabel === 'Reserved') : (parseInt(statusRef) === 3);

            if (isOccupied) { // Table is occupied
                // Check if there's an active order and redirect to order screen
                $.ajax({
                    url: '<?= base_url("Restaurant_Order_Taking/get_order_status") ?>',
                    type: 'POST',
                    data: { table_id: tableId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.order) {
                            window.location.href = '<?= base_url("Restaurant_Order_Taking/order_screen") ?>/' + response.order.id;
                        } else {
                            alert('Table appears occupied but no active order found. Please contact administrator.');
                        }
                    }
                });
                return;
            }
            
            if (isReserved) { // Table is reserved
                alert('This table is reserved. Please contact administrator.');
                return;
            }
            
            // Table is available - show guest count modal
            $('#selected-table-name').text(tableName);
            $('#selected-table-capacity').text(capacity);
            $('#guest-count').attr('max', capacity).val(1);
            
            const guestModal = new bootstrap.Modal(document.getElementById('guestModal'));
            guestModal.show();
        }

        // Function to open reservation modal
        function openReserveModal(tableId, tableName) {
            // Set current date and time as minimum for the datetime-local input
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            // Set minimum datetime to now
            const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            
            // Set default reservation end time to 1 hour from now
            const defaultEndTime = new Date(now.getTime() + 60 * 60 * 1000);
            const defaultEndHours = String(defaultEndTime.getHours()).padStart(2, '0');
            const defaultEndMinutes = String(defaultEndTime.getMinutes()).padStart(2, '0');
            const defaultDateTime = `${year}-${month}-${day}T${defaultEndHours}:${defaultEndMinutes}`;
            
            // Set form values
            $('#reserveTableId').val(tableId);
            $('#reserveTableName').text(tableName);
            $('#reservedBy').val('');
            $('#reservedUntil').attr('min', minDateTime).val(defaultDateTime);
            $('#reservationNoteInput').val('');
            
            // Clear previous validation
            $('#reservationForm').removeClass('was-validated');
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('reservationModal'));
            modal.show();
        }

        // Function to submit reservation form
        function submitReservation() {
            const form = document.getElementById('reservationForm');
            
            // Check form validity
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return false;
            }
            
            const tableId = $('#reserveTableId').val();
            const reservedBy = $('#reservedBy').val();
            const reservedUntil = $('#reservedUntil').val();
            const reservationNote = $('#reservationNoteInput').val();
            
            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
            
            // Format the date for the server
            const dateTime = new Date(reservedUntil);
            const formattedDateTime = dateTime.toISOString().slice(0, 19).replace('T', ' ');
            
            // Send AJAX request
            $.ajax({
                url: '<?= base_url("Restaurant_Order_Taking/reserve_table") ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    table_id: tableId,
                    reserved_by: reservedBy,
                    reserved_until: formattedDateTime,
                    reserved_note: reservationNote,
                    [CSRF_TOKEN_NAME]: CSRF_TOKEN_HASH
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Show success message
                        showAlert('success', 'Table reserved successfully!');
                        
                        // Close the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('reservationModal'));
                        modal.hide();
                        
                        // Refresh the table list
                        loadTables(selectedSectionId);
                    } else {
                        // Show error message
                        showAlert('danger', response.message || 'Failed to reserve table.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error reserving table:', error);
                    showAlert('danger', 'An error occurred while reserving the table. Please try again.');
                },
                complete: function() {
                    // Reset button state
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
            
            return false;
        }

        // Function to unreserve a table
        function unreserveTable(tableId, skipConfirm = false) {
            if (!skipConfirm && !confirm('Are you sure you want to unreserve this table?')) {
                return false;
            }
            
            // Show loading state
            const tableCard = $(`.table-card[data-table-id="${tableId}"]`);
            tableCard.addClass('table-loading');
            
            $.ajax({
                url: '<?= base_url("Restaurant_Order_Taking/unreserve_table") ?>',
                type: 'POST',
                data: {
                    table_id: tableId,
                    [CSRF_TOKEN_NAME]: CSRF_TOKEN_HASH
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert('success', 'Table reservation removed successfully');
                        if (selectedSectionId) {
                            loadTables(selectedSectionId);
                        }
                    } else {
                        showAlert('danger', response.message || 'Failed to remove reservation');
                    }
                },
                error: function(xhr) {
                    console.error('Unreserve error:', xhr);
                    showAlert('danger', 'An error occurred while trying to unreserve the table');
                },
                complete: function() {
                    tableCard.removeClass('table-loading');
                }
            });
        }
        
        // Function to load tables from dropdown selection
        function loadTablesFromDropdown() {
            const sectionId = $('#section-dropdown').val();
            if (sectionId) {
                loadSubsections(sectionId, null);
            }
        }

        function loadSubsections(sectionId, preselectSubsectionId) {
            selectedSectionId = sectionId;
            $('#subsections-section').hide();
            $('#subsections-container').empty();
            selectedSubsectionId = '';

            // Update UI - mark section as active
            $('.section-card').removeClass('active');
            $(`[data-section-id="${sectionId}"]`).addClass('active');

            // Update dropdown selection
            $('#section-dropdown').val(sectionId);

            $.ajax({
                url: '<?= base_url("Restaurant_Order_Taking/load_subsections") ?>',
                type: 'POST',
                dataType: 'json',
                data: { section_id: sectionId },
                success: function(resp) {
                    const subs = (resp && resp.status === 'success' && resp.subsections) ? resp.subsections : [];

                    if (!subs.length) {
                        // No subsections - go to tables for section
                        selectedSubsectionId = '';
                        loadTables(sectionId, '');
                        return;
                    }

                    let html = '';
                    subs.forEach(function(ss) {
                        const isActive = preselectSubsectionId && ('' + preselectSubsectionId) === ('' + ss.id);
                        html += `
                            <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                                <div class="subsection-card${isActive ? ' active' : ''}" data-subsection-id="${ss.id}" onclick="loadTables(${sectionId}, '${ss.id}')">
                                    ${ss.name || ''}
                                </div>
                            </div>
                        `;
                    });

                    $('#subsections-container').html(html);
                    $('#subsections-section').show();
                },
                error: function() {
                    selectedSubsectionId = '';
                    loadTables(sectionId, '');
                }
            });
        }

        // Tab switching functionality
        $(document).ready(function() {
            $('.nav-tab-custom').on('click', function() {
                $('.nav-tab-custom').removeClass('active');
                $(this).addClass('active');
                
                const tab = $(this).data('tab');
                if (tab === 'tables') {
                    const sectionId = $('#section-dropdown').val();
                    if (sectionId) {
                        const subId = selectedSubsectionId || '';
                        window.location.href = '<?= base_url("Restaurant_Order_Taking/tables/") ?>' + sectionId + (subId ? '/' + subId : '');
                        return;
                    }
                }
            });

            // Auto-load first section's tables
            const firstSection = $('#section-dropdown option:first').val();
            if (firstSection) {
                loadSubsections(firstSection, null);
            }
        });

        function refreshTables() {
            if (selectedSectionId) {
                loadTables(selectedSectionId);
            }
        }

        // Note: Removed auto-refresh interval and initial preload of statuses to avoid redundant behavior.
    </script>
</body>
</html>