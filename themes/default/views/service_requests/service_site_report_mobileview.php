<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Customers & Service</title>
    <link rel="stylesheet" href="<?= $assets ?>service_requests/css/service_site_report_mobile.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Force Toggle Switch Styles */
        .switch-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fdfdfd;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 5px;
        }
        .switch-label {
            font-weight: 600;
            font-size: 14px;
            color: #444;
            cursor: pointer;
            flex: 1;
        }
        .switch-input {
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
            position: absolute !important;
            pointer-events: none;
        }
        .switch-slider {
            position: relative;
            display: block;
            width: 46px;
            height: 24px;
            background-color: #cbd5e1;
            border-radius: 24px;
            cursor: pointer;
            transition: background-color 0.3s;
            flex-shrink: 0;
        }
        .switch-slider:after {
            content: "";
            position: absolute;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background-color: white;
            top: 3px;
            left: 3px;
            transition: transform 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .switch-input:checked + .switch-slider {
            background-color: #3b82f6;
        }
        .switch-input:checked + .switch-slider:after {
            transform: translateX(22px);
        }

        /* Hide iCheck elements inside switch container */
        .switch-container [class^="icheckbox_"], 
        .switch-container .iCheck-helper {
            display: none !important;
        }

        /* Premium Modal Styling */
        .modal-mobile-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
            padding: 15px;
        }
        .modal-mobile-content {
            background: #fff;
            width: 100%;
            max-width: 440px;
            min-height: 400px; /* Ensure it opens enough even if empty initially */
            max-height: 90vh;
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalSlideUp 0.3s ease-out;
        }
        @keyframes modalSlideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-mobile-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-mobile-header h3 {
            margin: 0;
            font-size: 19px;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.02em;
        }
        .modal-mobile-close {
            font-size: 28px;
            color: #9ca3af;
            cursor: pointer;
            line-height: 1;
        }
        .modal-mobile-body {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
            -webkit-overflow-scrolling: touch;
        }
        .btn-mobile-primary {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-weight: 600;
            width: 100%;
            transition: background 0.2s;
        }
        .btn-mobile-primary:active {
            background: #1d4ed8;
        }
        .btn-mobile-secondary {
            background: #f9fafb;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            padding: 14px;
            border-radius: 14px;
            font-weight: 600;
        }
        .form-control-mobile {
            background: #f9fafb !important;
            border: 1.5px solid #e5e7eb !important;
            padding: 14px 16px !important;
            border-radius: 14px !important;
            width: 100%;
            box-sizing: border-box;
            color: #111827 !important;
        }
        .form-control-mobile:focus {
            border-color: #3b82f6 !important;
            background: #fff !important;
            outline: none;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #374151;
        }
        .modal-footer-btns {
            margin-top: 24px;
            display: flex;
            gap: 12px;
        }
        .modal-footer-btns button {
            flex: 1;
        }
        .brand-results-overlay {
            display: none;
            position: absolute;
            z-index: 100;
            left: 0; right: 0; top: 100%;
            border: 1px solid #e5e7eb;
            background: #fff;
            max-height: 200px;
            overflow-y: auto;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
    <script src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
</head>
<body>

<div class="mobile-container">
    <div class="header-section">
        <h1>Service Site Report</h1>
    </div>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert-dismissible-mobile" style="background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #f87171;">
            <?php echo $this->session->flashdata('error'); ?>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('message')): ?>
        <div class="alert-dismissible-mobile" style="background: #d1fae5; color: #059669; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #34d399;">
            <?php echo $this->session->flashdata('message'); ?>
        </div>
    <?php endif; ?>


    <!-- Tab Navigation Level 1 -->
    <div class="scrollable-tabs-wrapper">
        <div class="tabs-scroll-container">
            <ul class="nav-tabs-mobile">
                <?php $isServiceReportActive = in_array($tab, ['customer', 'equipment', 'work']); ?>
                <li class="<?= $isServiceReportActive ? 'active' : '' ?>">
                    <!-- Clicking Service Report jumps to customer sub-tab by default -->
                    <a href="<?= site_url('service_requests/service_site_report/customer') ?>">Service Report</a>
                </li>
                <li class="<?= ($tab == 'pm_log') ? 'active' : '' ?>">
                    <a href="<?= site_url('service_requests/service_site_report/pm_log') ?>">PM Log</a>
                </li>
                <li class="<?= ($tab == 'report_trigger') ? 'active' : '' ?>">
                    <a href="<?= site_url('service_requests/service_site_report/report_trigger') ?>">Report</a>
                </li>
                <li class="<?= ($tab == 'signatures') ? 'active' : '' ?>">
                    <a href="<?= site_url('service_requests/service_site_report/signatures') ?>">Signatures</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Navigation Level 2 (Sub-tabs) -->
    <?php if ($isServiceReportActive): ?>
    <div class="scrollable-tabs-wrapper sub-tabs">
        <div class="tabs-scroll-container">
            <ul class="nav-tabs-mobile">
                <li class="<?= ($tab == 'customer') ? 'active' : '' ?>">
                    <a href="<?= site_url('service_requests/service_site_report/customer') ?>">Customer Details</a>
                </li>
                <li class="<?= ($tab == 'equipment') ? 'active' : '' ?>">
                    <a href="<?= site_url('service_requests/service_site_report/equipment') ?>">Equipment</a>
                </li>
                <li class="<?= ($tab == 'work') ? 'active' : '' ?>">
                    <a href="<?= site_url('service_requests/service_site_report/work') ?>">Work</a>
                </li>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="tab-content-mobile">
        <?php echo form_open("service_requests/save_mobile_tab_data", array('id' => 'ssr_mobile_form')); ?>
        <input type="hidden" name="active_tab" value="<?= $tab ?>">
        <input type="hidden" name="is_mobile" value="1">

        <input type="hidden" name="otp_context_ref" id="otp_context_ref" value="<?= htmlspecialchars((string) (isset($draft['otp_context_ref']) ? $draft['otp_context_ref'] : $service_site_otp_context_ref), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="report_sent_confirmed" id="report_sent_confirmed" value="<?= !empty($draft['report_sent_confirmed']) ? '1' : '' ?>">
        <input type="hidden" name="otp_challenge_id" id="otp_challenge_id" value="<?= htmlspecialchars(isset($draft['otp_challenge_id']) ? $draft['otp_challenge_id'] : '') ?>">
        <input type="hidden" name="otp_verification_token" id="otp_verification_token" value="<?= htmlspecialchars(isset($draft['otp_verification_token']) ? $draft['otp_verification_token'] : '') ?>">
        <input type="hidden" name="otp_verified_channel" id="otp_verified_channel" value="<?= htmlspecialchars(isset($draft['otp_verified_channel']) ? $draft['otp_verified_channel'] : '') ?>">

        <?php
        // Build COMPLETE pm_log_grid_json from ALL parameters in DB ($pm_parameters),
        // merged with any values already saved in the session draft.
        // This mirrors exactly how the desktop works: ALL rows are always sent.
        $draftPmRows = array();
        if (!empty($draft['pm_log_grid_json'])) {
            $decoded = json_decode($draft['pm_log_grid_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $r) {
                    if (isset($r['section']) && isset($r['parameter'])) {
                        $draftPmRows[$r['section'] . '|||' . $r['parameter']] = $r;
                    }
                }
            }
        }
        $fullPmGrid = array();
        if (!empty($pm_parameters) && is_array($pm_parameters)) {
            foreach ($pm_parameters as $pmSection => $pmParams) {
                foreach ($pmParams as $pmParam) {
                    $key = $pmSection . '|||' . $pmParam;
                    $existing = isset($draftPmRows[$key]) ? $draftPmRows[$key] : array();
                    $fullPmGrid[] = array(
                        'section'   => $pmSection,
                        'parameter' => $pmParam,
                        'ckt_01'    => isset($existing['ckt_01']) ? $existing['ckt_01'] : '',
                        'ckt_02'    => isset($existing['ckt_02']) ? $existing['ckt_02'] : '',
                        'ckt_03'    => isset($existing['ckt_03']) ? $existing['ckt_03'] : '',
                        'ckt_04'    => isset($existing['ckt_04']) ? $existing['ckt_04'] : '',
                    );
                }
            }
        }
        $completePmJson = !empty($fullPmGrid) ? json_encode($fullPmGrid) : (isset($draft['pm_log_grid_json']) ? $draft['pm_log_grid_json'] : '');
        ?>
        <input type="hidden" name="pm_log_grid_json" id="pm_log_grid_json" value="<?= htmlspecialchars($completePmJson, ENT_QUOTES, 'UTF-8') ?>">

        <?php if ($tab != 'customer'): ?>
            <input type="hidden" name="customer_id" id="customer_id" value="<?= isset($draft['customer_id']) ? $draft['customer_id'] : '' ?>">
            <input type="hidden" name="customer_location_id" id="customer_location_id" value="<?= isset($draft['customer_location_id']) ? $draft['customer_location_id'] : '' ?>">
            <input type="hidden" name="actual_location_id" id="actual_location_id" value="<?= isset($actualLocationId) ? $actualLocationId : '' ?>">
            <input type="hidden" name="job_site_address" id="job_site_address" value="<?= htmlspecialchars(isset($draft['job_site_address']) ? $draft['job_site_address'] : '') ?>">
        <?php endif; ?>

        <?php if ($tab == 'customer'): ?>
            <div class="form-card">
                <!-- <div class="section-title">Customer & Job Info</div> -->
                
                <div class="form-group">
                    <label>Customer *</label>
                    <select name="customer_id" id="customer_id" class="form-control-mobile" required>
                        <option value="">Select customer</option>
                        <?php foreach($customers as $c): 
                            $cname = trim((string)(isset($c->name) ? $c->name : ''));
                            if ($cname === '') continue;
                            
                            $displayName = $cname;
                            if (!empty($c->company) && $c->company != '-' && $c->company != $c->name) {
                                $displayName .= ' (' . trim($c->company) . ')';
                            }
                            if (!empty($c->phone)) {
                                $displayName .= ' - ' . trim($c->phone);
                            }
                        ?>
                            <option value="<?= $c->id ?>" <?= (isset($draft['customer_id']) && $draft['customer_id'] == $c->id) ? 'selected' : '' ?>><?= htmlspecialchars($displayName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Service Report No.</label>
                    <input type="text" name="service_report_no" id="service_report_no" class="form-control-mobile" value="<?= isset($draft['service_report_no']) ? $draft['service_report_no'] : '' ?>" placeholder="CustId/Loc/I"  required readonly style="background: #f1f5f9; cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="service_date" class="form-control-mobile" value="<?= isset($draft['service_date']) ? $draft['service_date'] : date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label>Type of Service</label>
                    <select name="service_type" class="form-control-mobile">
                        <option value="">Select service type</option>
                        <?php foreach($service_types as $st): ?>
                            <option value="<?= $st->name ?>" <?= (isset($draft['service_type']) && $draft['service_type'] == $st->name) ? 'selected' : '' ?>><?= $st->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Job Site Name</label>
                    <input type="hidden" name="job_site_name" id="job_site_name" value="<?= isset($draft['job_site_name']) ? $draft['job_site_name'] : '' ?>">
                    <!-- Matches desktop: select#customer_location_id.form-control -->
                    <select name="customer_location_id" id="customer_location_id" class="form-control-mobile">
                        <option value="">Select job site name</option>
                        <?php 
                        if (isset($customer_locations) && !empty($customer_locations)) {
                            foreach ($customer_locations as $loc) {
                                $locId = isset($loc['id']) ? $loc['id'] : '';
                                $locName = !empty($loc['location_name']) ? $loc['location_name'] : (!empty($loc['warehouse_name']) ? $loc['warehouse_name'] : (isset($loc['address_name']) ? $loc['address_name'] : ''));
                                $locCode = isset($loc['code']) ? $loc['code'] : 'LOC';
                                $selected = (isset($draft['customer_location_id']) && $draft['customer_location_id'] == $locId) ? 'selected' : '';
                                echo '<option value="'.$locId.'" '.$selected.' data-code="'.htmlspecialchars($locCode).'">'.htmlspecialchars($locName).'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Job Site Address</label>
                    <!-- Matches desktop: input type=text (NOT textarea) so Redactor never touches it -->
                    <input type="text" name="job_site_address" id="job_site_address" class="form-control-mobile" placeholder="Job site address" value="<?= htmlspecialchars(isset($draft['job_site_address']) ? $draft['job_site_address'] : '') ?>" readonly>
                </div>
            </div>

        <?php elseif ($tab == 'equipment'): ?>
            <div class="form-card">
                <!-- <div class="section-title">Equipment Details</div> -->
                <div class="form-group">
                    <label>Equipment Tag No.</label>
                    <div class="equipment-select-row">
                        <select name="eqpt_tag_no" id="eqpt_tag_no" class="form-control-mobile">
                            <option value="">Select equipment</option>
                            <?php 
                            $found_draft_tag = false;
                            $draft_tag = isset($draft['eqpt_tag_no']) ? $draft['eqpt_tag_no'] : '';

                            if (isset($equipment_tags) && !empty($equipment_tags)) {
                                foreach ($equipment_tags as $t) {
                                    $tagNo = isset($t['eqpt_no']) ? $t['eqpt_no'] : (isset($t['tag_no']) ? $t['tag_no'] : '');
                                    if ($tagNo == $draft_tag) { $found_draft_tag = true; }
                                    $selected = ($draft_tag == $tagNo) ? 'selected' : '';
                                    echo '<option value="'.$tagNo.'" '.$selected.'>'.htmlspecialchars($tagNo).'</option>';
                                }
                            }
                            
                            // If the draft tag wasn't in the filtered list (e.g. selected via global search), add it now
                            if (!empty($draft_tag) && !$found_draft_tag) {
                                echo '<option value="'.htmlspecialchars($draft_tag).'" selected>'.htmlspecialchars($draft_tag).'</option>';
                            }
                            ?>
                        </select>
                        <button type="button" class="btn-search-plus" id="btn_open_global_equipment">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div id="multi_equipment_wrapper" style="display:none; margin-top:10px; margin-bottom:15px; border:1px solid #e2e8f0; border-radius:12px; padding:16px; background:#f8fafc; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                    <div style="font-size:12px; font-weight:800; color:#475569; margin-bottom:12px; text-transform:uppercase; letter-spacing:0.05em;"><i class="fa fa-info-circle text-info"></i> Multiple records found:</div>
                    <div id="multi_equipment_grid" style="display:flex; flex-direction:column; gap:10px;"></div>
                </div>
                <input type="hidden" name="model_no" id="model_no" value="<?= isset($draft['model_no']) ? $draft['model_no'] : '' ?>">
                <input type="hidden" name="serial_no" id="serial_no" value="<?= isset($draft['serial_no']) ? $draft['serial_no'] : '' ?>">
                <div class="form-group">
                    <label>Activity</label>
                    <input type="text" name="activity" id="activity" class="form-control-mobile" value="<?= isset($draft['activity']) ? $draft['activity'] : '' ?>" placeholder="e.g. Liquid level sensor">
                </div>
                <div class="form-group">
                    <label>Action List</label>
                    <textarea name="action_list" id="action_list" class="form-control-mobile skip" rows="4"><?= isset($draft['action_list']) ? $draft['action_list'] : '' ?></textarea>
                </div>
                
                <input type="hidden" name="equipment_id" id="equipment_id" value="<?= htmlspecialchars(isset($draft['equipment_id']) ? $draft['equipment_id'] : '') ?>">

            </div>

        <?php elseif ($tab == 'work'): ?>
            <div class="form-card">
                <!-- <div class="section-title">Work Order & Spares</div> -->
                <div class="form-group">
                    <label>Work Order Status</label>
                    <select name="work_order_status" class="form-control-mobile">
                        <option value="">Select</option>
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="On Hold">On Hold</option>
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                        <option value="Closed">Closed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="switch-container">
                        <label class="switch-label" for="job_completed_toggle">Job Completed</label>
                        <input type="hidden" name="job_completed" value="No">
                        <input type="checkbox" id="job_completed_toggle" name="job_completed" value="Yes" class="switch-input" <?= (isset($draft['job_completed']) && $draft['job_completed'] == 'Yes') ? 'checked' : '' ?>>
                        <label class="switch-slider" for="job_completed_toggle"></label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch-container">
                        <label class="switch-label" for="quotation_required_toggle">Quotation Required</label>
                        <input type="hidden" name="quotation_required" value="No">
                        <input type="checkbox" id="quotation_required_toggle" name="quotation_required" value="Yes" class="switch-input" <?= (isset($draft['quotation_required']) && $draft['quotation_required'] == 'Yes') ? 'checked' : '' ?>>
                        <label class="switch-slider" for="quotation_required_toggle"></label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Quotation Description</label>
                    <textarea name="quotation_description" class="form-control-mobile skip" rows="2" placeholder="e.g. Compressor overhauling"><?= isset($draft['quotation_description']) ? $draft['quotation_description'] : '' ?></textarea>
                </div>
                <div class="form-group">
                    <label>Used Spare Parts</label>
                    <select name="used_spare_parts[]" id="used_spare_parts" class="form-control-mobile skip select" multiple="multiple" data-placeholder="Select Spare Parts">>
                        <?php 
                        $used_parts_draft = isset($draft['used_spare_parts']) ? $draft['used_spare_parts'] : '';
                        if (!is_array($used_parts_draft)) { $used_parts_draft = explode(', ', (string)$used_parts_draft); }
                        if (!empty($spare_parts)) {
                            foreach ($spare_parts as $part) {
                                $selected = in_array($part->name, $used_parts_draft) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($part->name) . '" ' . $selected . '>' . htmlspecialchars($part->name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Required Spare Parts</label>
                    <select name="required_spare_parts[]" id="required_spare_parts" class="form-control-mobile skip select" multiple="multiple" data-placeholder="Select Spare Parts">>
                        <?php 
                        $req_parts_draft = isset($draft['required_spare_parts']) ? $draft['required_spare_parts'] : '';
                        if (!is_array($req_parts_draft)) { $req_parts_draft = explode(', ', (string)$req_parts_draft); }
                        if (!empty($spare_parts)) {
                            foreach ($spare_parts as $part) {
                                $selected = in_array($part->name, $req_parts_draft) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($part->name) . '" ' . $selected . '>' . htmlspecialchars($part->name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

        <?php elseif ($tab == 'pm_log'): ?>
            <div class="form-card">
                <!-- <div class="section-title">PM Log (Equipment)</div> -->
                
                <?php if (empty($draft['customer_id'])): ?>
                    <div class="alert alert-info-mobile" style="background: #e3f2fd; color: #0d47a1; padding: 15px; border-radius: 8px; font-size: 14px; border: 1px solid #bbdefb; margin-bottom: 20px;">
                        <i class="fa fa-info-circle"></i> Please select a customer in <b>Customer</b> tab to view PM log grid.
                    </div>
                <?php else: ?>
                    <div style="margin-bottom: 10px; font-weight: 600; color: #555;">
                        <?php 
                        $customerNameToShow = 'Selected Customer';
                        if (!empty($draft['customer_id']) && !empty($customers)) {
                            foreach ($customers as $c) {
                                if ($c->id == $draft['customer_id']) {
                                    $customerNameToShow = htmlspecialchars(trim(isset($c->name) ? $c->name : (isset($c->company) ? $c->company : '')));
                                    break;
                                }
                            }
                        }
                        ?>
                        PM Log for Customer: <?= $customerNameToShow ?>
                    </div>
                    <div class="pm-global-filters">
                        <div class="filter-label">View Circuit:</div>
                        <div class="ckt-filter-row">
                            <div class="ckt-pill active" onclick="filterCktGlobal(1, this)">C1</div>
                            <div class="ckt-pill" onclick="filterCktGlobal(2, this)">C2</div>
                            <div class="ckt-pill" onclick="filterCktGlobal(3, this)">C3</div>
                            <div class="ckt-pill" onclick="filterCktGlobal(4, this)">C4</div>
                        </div>
                    </div>

                    <div class="pm-grid-mobile filter-ckt1">
                        <?php if (!empty($pm_parameters)): ?>
                            <?php foreach ($pm_parameters as $section => $params): ?>
                                <div class="pm-category-mobile" id="cat_<?= md5($section) ?>">
                                    <div class="pm-category-header-mobile" onclick="togglePMCategory('cat_<?= md5($section) ?>')">
                                        <span class="pm-category-title"><?= $section ?></span>
                                        <i class="fa fa-chevron-right pm-category-icon"></i>
                                    </div>
                                    <div class="pm-category-body-mobile" style="display: none;">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Parameter</th>
                                                    <th class="ckt-th ckt1">C1</th>
                                                    <th class="ckt-th ckt2">C2</th>
                                                    <th class="ckt-th ckt3">C3</th>
                                                    <th class="ckt-th ckt4">C4</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($params as $p): ?>
                                                    <?php
                                                    $val1 = '';
                                                    $val2 = '';
                                                    $val3 = '';
                                                    $val4 = '';
                                                    
                                                    $draftJson = isset($draft['pm_log_grid_json']) ? $draft['pm_log_grid_json'] : '';
                                                    $draftRows = !empty($draftJson) ? json_decode($draftJson, true) : array();
                                                    
                                                    // Check draft rows first
                                                    if (!empty($draftRows) && is_array($draftRows)) {
                                                        foreach ($draftRows as $row) {
                                                            if (isset($row['section']) && $row['section'] == $section && isset($row['parameter']) && $row['parameter'] == $p) {
                                                                $val1 = isset($row['ckt_01']) ? $row['ckt_01'] : '';
                                                                $val2 = isset($row['ckt_02']) ? $row['ckt_02'] : '';
                                                                $val3 = isset($row['ckt_03']) ? $row['ckt_03'] : '';
                                                                $val4 = isset($row['ckt_04']) ? $row['ckt_04'] : '';
                                                                break;
                                                            }
                                                        }
                                                    } elseif (!empty($pm_log_rows)) {
                                                        foreach ($pm_log_rows as $row) {
                                                            if (isset($row['section']) && $row['section'] == $section && isset($row['parameter']) && $row['parameter'] == $p) {
                                                                $val1 = isset($row['ckt_01']) ? $row['ckt_01'] : '';
                                                                $val2 = isset($row['ckt_02']) ? $row['ckt_02'] : '';
                                                                $val3 = isset($row['ckt_03']) ? $row['ckt_03'] : '';
                                                                $val4 = isset($row['ckt_04']) ? $row['ckt_04'] : '';
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <tr class="pm-data-row-mobile" data-section="<?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8'); ?>" data-parameter="<?= htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <td style="font-size: 11px;"><?= $p ?></td>
                                                        <td class="ckt-td ckt1"><input type="text" class="pm-cell-mobile" data-ckt="ckt_01" value="<?= htmlspecialchars($val1) ?>">
                                                        </td>
                                                        <td class="ckt-td ckt2"><input type="text" class="pm-cell-mobile" data-ckt="ckt_02" value="<?= htmlspecialchars($val2) ?>">
                                                        </td>
                                                        <td class="ckt-td ckt3"><input type="text" class="pm-cell-mobile" data-ckt="ckt_03" value="<?= htmlspecialchars($val3) ?>">
                                                        </td>
                                                        <td class="ckt-td ckt4"><input type="text" class="pm-cell-mobile" data-ckt="ckt_04" value="<?= htmlspecialchars($val4) ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                <?php endif; ?>
            </div>

        <?php elseif ($tab == 'report_trigger'): ?>
            <div class="quick-actions-container">
                <h2 style="font-size: 18px; font-weight: 700; margin: 0 0 5px 5px;">Quick Actions</h2>
                

                <!-- Section A: Generate & Share Report -->
                <div class="qa-card">
                    <div class="qa-title">Share Report</div>
                    <div class="qa-desc">Generate a PDF report and share it with the on-site client.</div>
                    <div class="qa-btn-row">
                        <div class="btn-qa btn-whatsapp" onclick="alert('WhatsApp Share: Feature coming soon')">
                            <span><i class="fa fa-whatsapp"></i> WhatsApp</span>
                            <i class="fa fa-chevron-right"></i>
                        </div>
                        <div class="btn-qa btn-outline-qa" id="btn_send_report_customer">
                            <span><i class="fa fa-envelope-o"></i> Email</span>
                            <i class="fa fa-chevron-right"></i>
                        </div>
                    </div>
                    <div id="report_send_feedback" class="status-msg-mobile" style="margin-top:10px;"></div>
                </div>

                <!-- Section B: Generate & Send OTP -->
                <div class="qa-card" id="otp_trigger_section">
                    <div class="qa-title">Send OTP</div>
                    <div class="qa-desc">Send a 6-digit OTP to the on-site client for verification and signing.</div>
                    <div class="qa-btn-row">
                        <div class="btn-qa btn-whatsapp" onclick="alert('WhatsApp OTP: Feature coming soon')">
                            <span><i class="fa fa-whatsapp"></i> WhatsApp</span>
                            <i class="fa fa-chevron-right"></i>
                        </div>
                        <div class="btn-qa btn-outline-qa" onclick="alert('SMS OTP: Feature coming soon')">
                            <span><i class="fa fa-mobile"></i> SMS</span>
                        </div>
                        <div class="btn-qa btn-outline-qa" id="btn_send_otp_email">
                            <span><i class="fa fa-envelope-o"></i> Email</span>
                        </div>
                    </div>
                    
                    <div id="resend_otp_wrapper" style="display:none; margin-top:12px;">
                        <div class="resend-otp-btn" id="btn_resend_otp">Resend OTP</div>
                    </div>
                    
                    <div id="otp_feedback" class="status-msg-mobile" style="margin-top:10px;"></div>
                </div>

                <!-- Verify & Sign Section -->
                <div class="qa-card" id="verify_sign_section" style="display:none;">
                    <div class="qa-title">Verify & Sign</div>
                    <div class="qa-desc">Enter the 6-digit OTP shared by the client to verify and sign the report.</div>
                    
                    <div class="otp-digit-group">
                        <input type="number" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="number" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="number" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="number" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="number" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="number" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                        <input type="hidden" id="otp_code" value="">
                    </div>

                    <div id="btn_verify_otp_big" class="btn-verify-big">
                        Verify <i class="fa fa-chevron-right"></i>
                    </div>
                </div>

            </div>

        <?php elseif ($tab == 'signatures'): ?>
            <div class="form-card">
                <div class="section-title">Signatures</div>
                
                <!-- Signature Toggle Pills -->
                <div class="sig-tab-pill-container">
                    <div class="sig-pill active" onclick="switchSigTab('engineer')">Engineer</div>
                    <div class="sig-pill" onclick="switchSigTab('customer')">Customer</div>
                </div>

                <!-- Engineer Section -->
                <div id="sig_engineer_section" class="sig-pane active">
                    <!-- <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="engineer_name" class="form-control-mobile" placeholder="Enter name" value="<?= isset($draft['engineer_name']) ? $draft['engineer_name'] : '' ?>">
                    </div> -->
                    <div class="form-group">
                        <label>Signature *</label>
                        <div class="sig-pad-box">
                            <div class="sig-hint">Tap to sign</div>
                            <canvas id="engineer_signature_pad" class="sig-canvas"></canvas>
                            <div class="sig-pad-footer">
                                <span class="sig-clear-btn" onclick="clearSig('engineer_signature_pad')">Clear</span>
                                <span class="sig-saved-label"><i class="fa fa-check"></i> Use saved signature</span>
                            </div>
                        </div>
                        <input type="hidden" name="engineer_signature_data" id="engineer_signature_data" value="<?= isset($draft['engineer_signature_data']) ? $draft['engineer_signature_data'] : '' ?>">
                    </div>
                    <div class="remarks-section-mobile">
                        <div class="form-group">
                            <label>Engineer Remarks</label>
                            <textarea name="engineer_remarks" class="form-control-mobile skip" rows="2" placeholder="Engineer remarks..."><?= isset($draft['engineer_remarks']) ? $draft['engineer_remarks'] : '' ?></textarea>
                        </div>
                        <div id="sig_timestamp_msg_eng" class="sig-timestamp" style="display:none;"><i class="fa fa-clock-o"></i> Signed just now</div>
                    </div>
                </div>

                <!-- Customer Section -->
                <div id="sig_customer_section" class="sig-pane">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="customer_signature_name" class="form-control-mobile" placeholder="Enter name" value="<?= isset($draft['customer_signature_name']) ? $draft['customer_signature_name'] : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Signature *</label>
                        <div class="sig-pad-box">
                            <div class="sig-hint">Tap to sign</div>
                            <canvas id="customer_signature_pad" class="sig-canvas"></canvas>
                            <div class="sig-pad-footer">
                                <span class="sig-clear-btn" onclick="clearSig('customer_signature_pad')">Clear</span>
                                <span class="sig-saved-label"><i class="fa fa-check"></i> Use saved signature</span>
                            </div>
                        </div>
                        <input type="hidden" name="customer_signature_data" id="customer_signature_data" value="<?= isset($draft['customer_signature_data']) ? $draft['customer_signature_data'] : '' ?>">
                    </div>
                    <div class="remarks-section-mobile">
                        <div class="form-group">
                            <label>Customer Remarks</label>
                            <textarea name="customer_remark" class="form-control-mobile skip" rows="2" placeholder="Customer remark..."><?= isset($draft['customer_remark']) ? $draft['customer_remark'] : '' ?></textarea>
                        </div>
                        <div id="sig_timestamp_msg" class="sig-timestamp" style="display:none;"><i class="fa fa-clock-o"></i> Signed just now</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="nav-footer-mobile">
            <div class="btn-group-mobile">
                <?php if ($tab != 'customer'): ?>
                    <button type="button" onclick="history.back()" class="btn-nav-flat">
                        <i class="fa fa-arrow-left"></i> Back
                    </button>
                <?php endif; ?>

                <?php if ($tab != 'signatures'): ?>
                    <button type="submit" name="action" value="next" class="btn-nav-next">
                        Next <i class="fa fa-arrow-right"></i>
                    </button>
                <?php else: ?>
                    <button type="submit" name="action" value="submit" class="btn-nav-next">
                        Submit
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<div id="global_equipment_modal" class="modal-mobile-overlay" style="display:none;">
    <div class="modal-mobile-content">
        <div class="modal-mobile-header">
            <h3 id="equipment_modal_title">Equipment Selection</h3>
            <span id="btn_close_global_equipment" class="modal-mobile-close">&times;</span>
        </div>
        <div class="modal-mobile-body">
            <div id="equipment_selection_view">
                <div class="equipment-search-box" style="margin-bottom: 10px; position: relative;">
                    <i class="fa fa-search" 
                    style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #888;">
                    </i>
                    <input 
                        type="text" 
                        id="equipment_search_input" 
                        placeholder="Search unmapped equipment..." 
                        autocomplete="off" 
                        style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #ddd; border-radius: 8px;"
                    >
            </div>
                <div id="global_equipment_list_container" class="equipment-results-list" style="max-height: 40vh; overflow-y: auto;">
                    <!-- Search results will be injected here -->
                </div>
                <div style="margin-top: 15px; text-align: center;">
                    <button type="button" id="btn_show_add_equipment_form" class="btn-mobile-primary" style="width: 100%; padding: 12px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; font-weight: 600;">+ Add New Equipment</button>
                </div>
            </div>

            <!-- Add New Equipment Form (Hidden by default) -->
            <div id="add_equipment_form_view" style="display: none; opacity: 1 !important;">
                <input type="hidden" id="selected_existing_product_id">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="new_eq_product_name" class="form-control-mobile" placeholder="e.g. Chiller Unit">
                </div>
                <div class="form-group" style="position: relative;">
                    <label>Brand</label>
                    <input type="text" id="new_eq_brand_input" class="form-control-mobile" placeholder="Search or type brand...">
                    <input type="hidden" id="new_eq_brand_id">
                    <div id="brand_search_results" class="brand-results-overlay"></div>
                </div>
                <div class="form-group">
                    <label>Model No</label>
                    <input type="text" id="new_eq_model_no" class="form-control-mobile" placeholder="Enter model number">
                </div>
                <div class="form-group">
                    <label>Serial No</label>
                    <input type="text" id="new_eq_serial_no" class="form-control-mobile" placeholder="Enter serial number">
                </div>
                <div class="modal-footer-btns">
                    <button type="button" id="btn_cancel_add_equipment" class="btn-mobile-secondary">Cancel</button>
                    <button type="button" id="btn_save_new_equipment" class="btn-mobile-primary">Save Equipment</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var ServiceSiteReportConfig = {
        nextReportNumberUrl: "<?= site_url('service_requests/get_next_service_report_number_json'); ?>",
        saveMobileTabDataUrl: "<?= site_url('service_requests/save_mobile_tab_data_json'); ?>",
        customerPmGridUrl: "<?= site_url('service_requests/get_customer_pm_log_grid'); ?>",
        customerLocationsUrl: "<?= site_url('service_requests/get_customer_locations'); ?>",
        equipmentTagsUrl: "<?= site_url('service_requests/get_equipment_tags_for_service_site_report'); ?>",
        equipmentDetailsUrl: "<?= site_url('service_requests/get_equipment_details_for_service_site_report'); ?>",
        sendServiceReportToCustomerUrl: "<?= site_url('service_requests/send_service_site_report_to_customer'); ?>",
        sendServiceReportOtpUrl: "<?= site_url('service_requests/send_service_site_report_otp'); ?>",
        verifyServiceReportOtpUrl: "<?= site_url('service_requests/verify_service_site_report_otp'); ?>",
        uploadSignatureUrl: "<?= site_url('service_requests/upload_signature'); ?>",
        assetsUploadUrl: "<?= base_url('assets/uploads/'); ?>",
        getUnmappedProductsUrl: "<?= site_url('service_requests/get_unmapped_equipment_products'); ?>",
        addNewEquipmentUrl: "<?= site_url('service_requests/add_new_equipment'); ?>",
        getBrandsUrl: "<?= site_url('service_requests/get_brands'); ?>",
        csrfName: "<?= $this->security->get_csrf_token_name(); ?>",
        csrfHash: "<?= $this->security->get_csrf_hash(); ?>",
        customerAddresses: <?php
            $map = array();
            if (!empty($customers)) {
                foreach ($customers as $c) {
                    $map[$c->id] = array(
                        'name'  => $c->name,
                        'phone' => isset($c->phone) ? $c->phone : '',
                        'email' => isset($c->email) ? $c->email : '',
                    );
                }
            }
            echo json_encode($map);
        ?>,
        allEquipments: <?php 
            $allEq = array();
            if (!empty($equipments)) {
                foreach ($equipments as $e) {
                    $allEq[] = array(
                        'tag_no'    => (isset($e->eqpt_no) ? $e->eqpt_no : (isset($e->asset_no) ? $e->asset_no : '')),
                        'model_no'  => isset($e->model_no) ? $e->model_no : '',
                        'serial_no' => isset($e->serial_no) ? $e->serial_no : ''
                    );
                }
            }
            echo json_encode($allEq);
        ?>
    };
</script>
<script src="<?= $assets ?>service_requests/js/service_site_report_mobile.js?v=20260429a"></script>

    <script>
        $(function() {
            function handleToggle() {
                if (typeof $.fn.iCheck !== 'undefined') {
                   $('.switch-input').iCheck('destroy');
                }
            }
            handleToggle();
            // Re-run after any dynamic content load if necessary
            $(document).ajaxComplete(handleToggle);

            // Robust click handling for toggles
            $(document).on('click', '.switch-slider, .switch-label', function(e) {
                var forId = $(this).attr('for');
                if (forId) {
                    var $input = $('#' + forId);
                    if ($input.length) {
                        var isChecked = !$input.prop('checked');
                        $input.prop('checked', isChecked).trigger('change');
                        e.preventDefault();
                    }
                }
            });

            // Sync hidden input to prevent duplicates and ensure persistence
            $(document).on('change', '.switch-input', function() {
                var isChecked = $(this).prop('checked');
                // Find the hidden input with the same name in the same container
                $(this).closest('.switch-container').find('input[type="hidden"][name="' + this.name + '"]').prop('disabled', isChecked);
            });

            // Initial sync for existing state
            $('.switch-input').trigger('change');
        });
    </script>
</body>
</html>
