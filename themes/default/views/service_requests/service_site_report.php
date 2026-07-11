<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= $assets ?>service_requests/css/service_site_report.css?v=<?= time(); ?>">
<style>
    /* Zebra Striping for Select2 (v4 & v3) and Selectpicker Dropdowns */
    .select2-results__options > .select2-results__option:nth-child(odd),
    .select2-results > li:nth-child(odd),
    .dropdown-menu.inner > li:nth-child(odd) > a {
        background-color: #e8f4fd !important;
        color: #111827 !important;
    }
    .select2-results__options > .select2-results__option:nth-child(even),
    .select2-results > li:nth-child(even),
    .dropdown-menu.inner > li:nth-child(even) > a {
        background-color: #ffffff !important;
        color: #111827 !important;
    }
    .select2-results__option--highlighted[aria-selected],
    .select2-results__option:hover,
    .select2-results > li:hover,
    .dropdown-menu.inner > li > a:hover {
        background-color: #2563eb !important;
        color: #ffffff !important;
    }

    /* Zebra Striping for Chosen Plugin (spare parts multiselect) */
    .chosen-container .chosen-results li:nth-child(odd) {
        background: #e8f4fd !important;
        background-image: none !important;
        color: #111827 !important;
    }
    .chosen-container .chosen-results li:nth-child(even) {
        background: #ffffff !important;
        background-image: none !important;
        color: #111827 !important;
    }
    .chosen-container .chosen-results li.highlighted {
        background-color: #2563eb !important;
        background-image: none !important;
        color: #ffffff !important;
    }
    .chosen-container .chosen-results li.result-selected {
        background: #dbeafe !important;
        background-image: none !important;
        color: #1e40af !important;
        font-weight: 700;
    }

    /* Forced Navigation Styles */
    .category-nav-container { 
        display: flex !important; 
        flex-wrap: wrap !important;
        gap: 15px !important; 
        margin-bottom: 10px !important; 
        padding: 15px 0 !important;
        border-bottom: none !important; /* Removed redundant line */
    }
    .cat-pill { 
        padding: 10px 28px !important; 
        border-radius: 50px !important; 
        background: #f1f5f9 !important; 
        color: #475569 !important; 
        font-weight: 700 !important; 
        font-size: 15px !important; 
        text-decoration: none !important; 
        transition: all 0.3s !important;
        border: 1px solid #e2e8f0 !important;
        cursor: pointer !important;
    }
    .cat-pill.active { 
        background: #3b82f6 !important; 
        color: #fff !important; 
        border-color: #3b82f6 !important;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4) !important;
    }
    .sub-nav-tabs { 
        display: flex !important; 
        gap: 40px !important; 
        margin-bottom: 25px !important; 
        border-bottom: 1px solid #e2e8f0 !important; /* Thinner gray line */
        padding: 0 10px !important;
    }
    .sub-nav-tabs .nav-item { 
        padding: 12px 0 !important; 
        color: #94a3b8 !important; 
        font-weight: 700 !important; 
        font-size: 16px !important; 
        cursor: pointer !important; 
        position: relative !important;
    }
    .sub-nav-tabs .nav-item.active { 
        color: #3b82f6 !important; 
    }
    .sub-nav-tabs .nav-item.active::after { 
        content: '' !important; 
        position: absolute !important; 
        bottom: -2px !important; 
        left: 0 !important; 
        right: 0 !important; 
        height: 3px !important; 
        background: #3b82f6 !important; 
        border-radius: 3px 3px 0 0 !important;
    }
    .category-group { display: none; }
    .category-group.active { display: block !important; }
    #group_service_report .tab-pane { display: none; }
    #group_service_report .tab-pane.active { display: block !important; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa fa-file-text-o"></i>
            Service Site Report
        </h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php echo form_open("service_requests/save_service_site_report", array('class' => 'form-horizontal', 'role' => 'form')); ?>

                    <div class="category-nav-container">
                        <a href="javascript:void(0)" class="cat-pill active" data-group="service_report">Service Report</a>
                        <a href="javascript:void(0)" class="cat-pill" data-group="pm_log">PM Log</a>
                        <a href="javascript:void(0)" class="cat-pill" data-group="report_trigger">Report</a>
                        <a href="javascript:void(0)" class="cat-pill" data-group="signatures">Signatures</a>
                    </div>

                    <div class="tab-content" style="padding: 0;">
                        <!-- SERVICE REPORT GROUP -->
                        <div id="group_service_report" class="category-group active">
                            <!-- Sub Tabs (Image 2 style) -->
                            <div class="sub-nav-tabs">
                                <div class="nav-item active" data-tab="customer_tab">Customer Details</div>
                                <div class="nav-item" data-tab="equipment_tab">Equipment</div>
                                <div class="nav-item" data-tab="work_details_tab">Work Details</div>
                            </div>

                            <div class="tab-content-inner">
                                <!-- CUSTOMER TAB -->
                                <div class="tab-pane active" id="customer_tab">
                                    <h4 class="section-header">Customer &amp; Service Details</h4>
                            <table class="table table-bordered table-condensed service-report-table">
                                <tbody>
                                <tr>
                                    <th style="width: 15%;">Customer <span class="text-danger">*</span></th>
                                    <td style="width: 35%;">
                                        <select name="customer_id" id="customer_id" class="form-control" required>
                                            <option value=""><?= lang('select'); ?> customer</option>
                                            <?php if (!empty($customers)) { ?>
                                                <?php foreach ($customers as $c) { ?>
                                                    <?php if (!isset($c->name) || trim((string) $c->name) === '') { continue; } ?>
                                                    <option value="<?= $c->id; ?>">
                                                        <?php
                                                            $displayName = trim((string) $c->name);
                                                            if (!empty($c->company) && $c->company != '-' && $c->company != $c->name) {
                                                                $displayName .= ' (' . trim($c->company) . ')';
                                                            }
                                                            if (!empty($c->phone)) {
                                                                $displayName .= ' - ' . trim($c->phone);
                                                            }
                                                            echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
                                                        ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <th style="width: 20%; text-align:right;">Service Report No. <span class="text-danger"></span></th>
                                    <td style="width: 30%;">
                                        <input type="text" name="service_report_no" id="service_report_no" class="form-control" placeholder="CustId/Loc/I"  required readonly style="background: #f8fafc; cursor: not-allowed;">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td>
                                        <input type="text"
                                               name="service_date"
                                               class="form-control datepicker"
                                               value="<?= set_value('service_date', date('d/m/Y')); ?>"
                                               placeholder="dd/mm/yyyy">
                                    </td>
                                    <th style="text-align:right;">Type of Service Performed</th>
                                    <td>
                                        <select name="service_type" id="service_type" class="form-control">
                                            <option value=""><?= lang('select'); ?> service type</option>
                                            <?php if (!empty($service_types)) { ?>
                                                <?php foreach ($service_types as $st) { ?>
                                                    <option value="<?= $st->name; ?>">
                                                        <?= htmlspecialchars($st->name, ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Job Site Name</th>
                                    <td>
                                        <input type="hidden" name="job_site_name" id="job_site_name" value="">
                                        <select name="customer_location_id" id="customer_location_id" class="form-control">
                                            <option value=""><?= lang('select'); ?> job site name</option>
                                        </select>
                                    </td>
                                    <th style="text-align:right;">Job Site Address</th>
                                    <td>
                                        <input type="text" name="job_site_address" id="job_site_address" class="form-control" placeholder="Job site address" readonly style="background: #f1f5f9; cursor: not-allowed;">
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- EQUIPMENT TAB -->
                        <div role="tabpanel" class="tab-pane <?= ($tab == 'equipment') ? 'active' : '' ?>" id="equipment_tab">
                            <h4 class="section-header">Equipment Details</h4>
                            <table class="table table-bordered table-condensed service-report-table">
                                <tbody>
                                <tr>
                                    <th style="width: 15%;">Equipment Tag No.</th>
                                    <td colspan="3">
                                        <div class="input-group">
                                            <select name="eqpt_tag_no" id="eqpt_tag_no" class="form-control" style="flex: 1 1 auto;">
                                                <option value=""><?= lang('select'); ?> equipment</option>
                                            </select>
                                            <div class="input-group-btn" style="flex: 0 0 auto;">
                                                <button type="button" class="btn btn-primary" id="btn_open_global_equipment" style="border-radius: 4px 4px 4px 4px !important; margin-left: 7px;">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="model_no" id="model_no">
                                        <input type="hidden" name="serial_no" id="serial_no">
                                    </td>
                                </tr>
                                <tr id="multi_equipment_row" style="display:none;">
                                    <td colspan="4">
                                        <div id="multi_equipment_wrapper" style="margin-top:10px; border:1px solid #e2e8f0; border-radius:8px; padding:20px; background:#f8fafc;">
                                            <h5 style="margin-top:0; margin-bottom:15px; font-weight:700; color:#334155;"><i class="fa fa-info-circle text-info"></i> Multiple records found for this Tag. Please select one:</h5>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped table-hover" style="margin-bottom:0; background:#fff; table-layout: fixed; width: 100%;">
                                                    <thead>
                                                        <tr style="background:#f1f5f9;">
                                                            <th style="width:100px; text-align:center; font-size:11px; color:#475569; text-transform:uppercase;">Select</th>
                                                            <th style="font-size:11px; color:#475569; text-transform:uppercase;">Model No.</th>
                                                            <th style="font-size:11px; color:#475569; text-transform:uppercase;">Serial No.</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="multi_equipment_grid"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                

                                <tr>
                                    <th>Activity</th>
                                    <td colspan="3">
                                        <input type="text" name="activity" id="activity" class="form-control" placeholder="Activity (e.g. Liquid level sensor)">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Action List</th>
                                    <td colspan="3">
                                        <textarea name="action_list" id="action_list" class="form-control" rows="3" placeholder="Actions performed / observations"></textarea>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <input type="hidden" name="equipment_id" id="equipment_id">
                        </div>

                        <!-- WORK DETAILS TAB -->
                        <div role="tabpanel" class="tab-pane <?= ($tab == 'work') ? 'active' : '' ?>" id="work_details_tab">
                            <h4 class="section-header">Work Order &amp; Spares</h4>
                            <table class="table table-bordered table-condensed service-report-table">
                                <tbody>
                                <tr>
                                    <th style="width: 15%;">Work Order Status</th>
                                    <td style="width: 35%;">
                                        <select name="work_order_status" class="form-control">
                                            <option value="">Select</option>
                                            <option value="Open">Open</option>
                                            <option value="In Progress">In Progress</option>
                                            <option value="On Hold">On Hold</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Closed">Closed</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </td>
                                    <th style="width: 15%;">Job Completed</th>
                                    <td style="width: 35%;">
                                        <select name="job_completed" class="form-control">
                                            <option value="">Select</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Quotation Required</th>
                                    <td>
                                        <select name="quotation_required" class="form-control">
                                            <option value="">Select</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </td>
                                    <th>Used Spare Parts</th>
                                    <td>
                                        <select name="used_spare_parts[]" id="used_spare_parts" class="form-control select" multiple="multiple" data-placeholder="Select Used Spare Parts">
                                            <?php 
                                            $used_parts = $this->input->post('used_spare_parts');
                                            if (!is_array($used_parts)) { $used_parts = explode(', ', (string)$used_parts); }
                                            if (!empty($spare_parts)) {
                                                foreach ($spare_parts as $part) {
                                                    $selected = in_array($part->name, $used_parts) ? 'selected' : '';
                                                    echo '<option value="' . htmlspecialchars($part->name) . '" ' . $selected . '>' . htmlspecialchars($part->name) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Required Spare Parts</th>
                                    <td>
                                        <select name="required_spare_parts[]" id="required_spare_parts" class="form-control select" multiple="multiple" data-placeholder="Select Required Spare Parts">
                                            <?php 
                                            $req_parts = $this->input->post('required_spare_parts');
                                            if (!is_array($req_parts)) { $req_parts = explode(', ', (string)$req_parts); }
                                            if (!empty($spare_parts)) {
                                                foreach ($spare_parts as $part) {
                                                    $selected = in_array($part->name, $req_parts) ? 'selected' : '';
                                                    echo '<option value="' . htmlspecialchars($part->name) . '" ' . $selected . '>' . htmlspecialchars($part->name) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <th>Quotation Description</th>
                                    <td>
                                        <textarea name="quotation_description" class="form-control" rows="2" placeholder="e.g. Compressor overhauling"></textarea>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                                </div>
                            </div>
                        </div>

                        <div class="category-group" id="pm_log_tab">
                    <!-- TRANE CHILLER LOG SHEET -->
                    <h4 class="section-header">Trane Chiller – Preventive Maintenance Log Sheet</h4>
                    <p class="help-block" style="margin-bottom: 10px;">
                        Fill PM values directly in this grid (Section, Parameter, CKT 01-04).
                    </p>
                    <div class="alert alert-info" id="pm_log_customer_hint" style="margin-bottom: 10px;">
                        Please select a customer in Service Site Report tab to view PM log grid.
                    </div>
                    <p id="pm_log_customer_title" class="help-block" style="display:none; margin-bottom: 10px;">
                        PM Log for Customer: <strong id="pm_log_customer_name"></strong>
                    </p>
                    <div class="table-responsive" id="pm_log_grid_wrapper" style="display:none;">
                        <table class="table table-bordered table-condensed pm-log-table" id="pm_log_table">
                            <thead>
                                <tr>
                                    <th style="width: 22%;">Section</th>
                                    <th style="width: 30%;">Parameter</th>
                                    <th style="width: 12%;">CKT 01</th>
                                    <th style="width: 12%;">CKT 02</th>
                                    <th style="width: 12%;">CKT 03</th>
                                    <th style="width: 12%;">CKT 04</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($pm_parameters) && is_array($pm_parameters)) { ?>
                                    <?php foreach ($pm_parameters as $sectionName => $parameterList) { ?>
                                        <tr class="pm-section-row">
                                            <th colspan="6"><?= htmlspecialchars($sectionName, ENT_QUOTES, 'UTF-8'); ?></th>
                                        </tr>
                                        <?php if (!empty($parameterList) && is_array($parameterList)) { ?>
                                            <?php foreach ($parameterList as $parameterName) { ?>
                                                <tr class="pm-data-row"
                                                    data-section="<?= htmlspecialchars($sectionName, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-parameter="<?= htmlspecialchars($parameterName, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <td><?= htmlspecialchars($sectionName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="pm-param-label"><?= htmlspecialchars($parameterName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><input type="text" class="form-control input-sm pm-cell" data-ckt="ckt_01" value=""></td>
                                                    <td><input type="text" class="form-control input-sm pm-cell" data-ckt="ckt_02" value=""></td>
                                                    <td><input type="text" class="form-control input-sm pm-cell" data-ckt="ckt_03" value=""></td>
                                                    <td><input type="text" class="form-control input-sm pm-cell" data-ckt="ckt_04" value=""></td>
                                                </tr>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No PM parameters available.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="pm_log_grid_json" id="pm_log_grid_json" value="">
                        </div>

                        <div class="category-group" id="report_trigger_tab">
                    <input type="hidden" name="otp_context_ref" id="otp_context_ref" value="<?= htmlspecialchars((string) $service_site_otp_context_ref, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="report_sent_confirmed" id="report_sent_confirmed" value="<?= isset($draft['report_sent_confirmed']) ? $draft['report_sent_confirmed'] : '' ?>">
                    <input type="hidden" name="otp_challenge_id" id="otp_challenge_id" value="<?= isset($draft['otp_challenge_id']) ? $draft['otp_challenge_id'] : '' ?>">
                    <input type="hidden" name="otp_verification_token" id="otp_verification_token" value="<?= isset($draft['otp_verification_token']) ? $draft['otp_verification_token'] : '' ?>">
                    <input type="hidden" name="otp_verified_channel" id="otp_verified_channel" value="<?= isset($draft['otp_verified_channel']) ? $draft['otp_verified_channel'] : '' ?>">

                    <div class="quick-actions-container" style="max-width: 800px; margin: 0;">
                        <h3 style="font-weight: 700; margin-bottom: 20px;">Quick Actions</h3>
                        
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
                            <div id="report_send_feedback" style="margin-top:10px; font-weight:600; font-size:13px;"></div>
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
                                <div class="btn-qa btn-outline-qa" id="btn_send_otp_sms">
                                    <span><i class="fa fa-mobile" style="font-size:22px; margin-right:5px;"></i> SMS</span>
                                    <i class="fa fa-chevron-right"></i>
                                </div>
                                <div class="btn-qa btn-outline-qa" id="btn_send_otp_email">
                                    <span><i class="fa fa-envelope-o"></i> Email</span>
                                    <i class="fa fa-chevron-right"></i>
                                </div>
                            </div>
                            <div id="otp_feedback" style="margin-top:10px; font-weight:600; font-size:13px;"></div>
                        </div>

                        <!-- Verify & Sign Section -->
                        <div class="qa-card" id="verify_sign_section" style="display:none;">
                            <div class="qa-title">Verify & Sign</div>
                            <div class="qa-desc">Enter the 6-digit OTP shared by the client to verify and sign the report.</div>
                            
                            <div class="otp-digit-group">
                                <input type="text" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                <input type="text" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                <input type="text" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                <input type="text" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                <input type="text" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                <input type="text" class="otp-digit-input" maxlength="1" pattern="\d*" inputmode="numeric">
                                <input type="hidden" id="otp_code" value="">
                            </div>

                            <button type="button" id="btn_verify_otp_big" class="btn-verify-big">
                                Verify <i class="fa fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                        </div>

                        <div class="category-group" id="customer_details_tab">
                    <!-- ENGINEER SIGNATURE -->
                    <h4 class="section-header">Engineer Signature &amp; Remarks</h4>

                    <div class="row signature-section">
                        <div class="col-sm-6 col-xs-12">
                            <div class="panel panel-default signature-panel">
                                <div class="panel-heading">
                                    <strong>Engineer</strong>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="engineer_name">Engineer Name</label>
                                        <input type="text" name="engineer_name" id="engineer_name" class="form-control" placeholder="Engineer name">
                                    </div>
                                    <div class="form-group">
                                        <label>Engineer Digital Signature</label>
                                        <div class="signature-wrapper" style="position:relative;">
                                            <div class="sig-hint">Sign here</div>
                                            <canvas id="engineer_signature_pad" style="width:100%; height:100%;"></canvas>
                                            <input type="hidden" name="engineer_signature_data" id="engineer_signature_data">
                                        </div>
                                        <button type="button" class="btn btn-xs btn-default" onclick="clearSig('engineer_signature_pad')">
                                            Clear Signature
                                        </button>
                                    </div>
                                    <div class="form-group">
                                        <label for="engineer_remarks">Engineer Remarks</label>
                                        <textarea name="engineer_remarks" id="engineer_remarks" class="form-control" rows="2" placeholder="Engineer remarks"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xs-12">
                            <div class="panel panel-default signature-panel">
                                <div class="panel-heading">
                                    <strong>Customer</strong>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="customer_name_signature">Customer Name</label>
                                        <input type="text" name="customer_signature_name" id="customer_name_signature" class="form-control" placeholder="Customer name">
                                    </div>
                                    <div class="form-group">
                                        <label>Customer Digital Signature</label>
                                        <div class="signature-wrapper" style="position:relative;">
                                            <div class="sig-hint">Sign here</div>
                                            <canvas id="customer_signature_pad" style="width:100%; height:100%;"></canvas>
                                            <input type="hidden" name="customer_signature_data" id="customer_signature_data">
                                        </div>
                                        <button type="button" class="btn btn-xs btn-default" onclick="clearSig('customer_signature_pad')">
                                            Clear Signature
                                        </button>
                                    </div>
                                    <div class="form-group">
                                        <label for="customer_remark">Customer Remarks</label>
                                        <textarea name="customer_remark" id="customer_remark" class="form-control" rows="2" placeholder="Customer remark"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <div class="col-sm-12 text-right">
                            <button type="reset" class="btn btn-warning">Reset</button>
                            <button type="submit" class="btn btn-success">Save</button>
                            <button type="button" class="btn btn-primary" onclick="window.print();">Print</button>
                        </div>
                    </div>
                    </div>

                </div>
                <?php echo form_close(); ?>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var customerPmGridUrl = "<?= site_url('service_requests/get_customer_pm_log_grid'); ?>";
    var customerLocationsUrl = "<?= site_url('service_requests/get_customer_locations'); ?>";
    var equipmentTagsUrl = "<?= site_url('service_requests/get_equipment_tags_for_service_site_report'); ?>";
    var equipmentDetailsUrl = "<?= site_url('service_requests/get_equipment_details_for_service_site_report'); ?>";
    var nextReportNumberUrl = "<?= site_url('service_requests/get_next_service_report_number_json'); ?>";
    var savePmLogSectionUrl = "<?= site_url('service_requests/save_pm_log_section'); ?>";
    var sendServiceReportToCustomerUrl = "<?= site_url('service_requests/send_service_site_report_to_customer'); ?>";
    var sendServiceReportOtpUrl = "<?= site_url('service_requests/send_service_site_report_otp'); ?>";
    var verifyServiceReportOtpUrl = "<?= site_url('service_requests/verify_service_site_report_otp'); ?>";
    var csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
    var csrfHash = "<?= $this->security->get_csrf_hash(); ?>";

    $(function () {
        var customerAddresses = <?= !empty($customers) ? json_encode(array_reduce($customers, function($m, $c){
            $p = array_filter([$c->address_name, $c->line1, $c->line2, $c->city, $c->state, $c->postal_code, $c->country]);
            $m[$c->id] = ['name' => $c->name, 'address' => implode(', ', $p), 'phone' => $c->phone, 'email' => $c->email];
            return $m;
        }, [])) : '{}' ?>;

        var customerLocationsMap = {};
        var previousCustomerId = $('#customer_id').val() || '';

        function buildAddressText(item) {
            return $.trim([item.line1, item.line2, item.city, item.state, item.postal_code, item.country, item.floor_details].filter(v => $.trim(v)).join(', '));
        }

        function updateServiceReportNo() {
            var custId = $('#customer_id').val() || "";
            var locId = $('#customer_location_id').val() || "0";
            if (!custId || custId === "0") {
                $('#service_report_no').val("");
                return;
            }

            $('#service_report_no').val("Generating...");
            $.post(nextReportNumberUrl, {company_id: custId, location_id: locId, [csrfName]: csrfHash}, function(res) {
                if (res.csrfHash) csrfHash = res.csrfHash;
                if (res.status === 'success' && res.next_number) {
                    $('#service_report_no').val(res.next_number);
                } else {
                    $('#service_report_no').val(custId + "/LOC/??");
                }
            }, 'json');
        }

        function prefillOtpDestination() {
            var cid = $('#customer_id').val();
            var channel = $('#otp_channel').val();
            if (!cid || !customerAddresses[cid]) {
                $('#otp_destination').val('');
                return;
            }
            if (channel === 'email') {
                $('#otp_destination').val($.trim(customerAddresses[cid].email || ''));
            } else {
                $('#otp_destination').val($.trim(customerAddresses[cid].phone || ''));
            }
        }

        function renderCustomerLocations(locations) {
            customerLocationsMap = {};
            var $l = $('#customer_location_id').html('<option value="">Select job site</option>');
            $.each(locations, function(_, item) {
                customerLocationsMap[item.id] = item;
                $l.append($('<option/>', {value: item.id, text: $.trim(item.location_name || item.warehouse_name || item.address_name || ('Loc '+item.id))}).attr('data-location-id', item.location_id).attr('data-code', item.code));
            });
            if (locations.length) $l.val(locations[0].id).trigger('change');
        }

        function fetchCustomerLocations(cid) {
            if (!cid) return renderCustomerLocations([]);
            $.post(customerLocationsUrl, {company_id: cid, [csrfName]: csrfHash}, function(res) {
                if (res.csrfHash) csrfHash = res.csrfHash;
                if (res.status === 'success' && res.locations) renderCustomerLocations(res.locations);
            }, 'json');
        }

        function resetEquipmentSection() { $('#eqpt_tag_no').html('<option value="">Select equipment</option>'); $('#model_no, #serial_no, #activity, #action_list').val(''); }

        function fetchEquipmentTagsBySelection(selectedTag) {
            var cid = $('#customer_id').val(), lid = $('#customer_location_id').val();
            resetEquipmentSection();
            if (!cid || !lid) return;
            $.post(equipmentTagsUrl, {company_id: cid, location_id: lid, [csrfName]: csrfHash}, function(res) {
                if (res.csrfHash) csrfHash = res.csrfHash;
                if (res.status === 'success' && res.tags) {
                    var $el = $('#eqpt_tag_no');
                    $.each(res.tags, function(_, t) { 
                        $el.append($('<option/>', {value: t.eqpt_no, text: t.eqpt_no})); 
                    });
                    if (selectedTag) {
                        setTimeout(function() {
                            $el.val(selectedTag).trigger('change');
                        }, 100);
                    }
                }
            }, 'json');
        }

        $('#customer_id').on('change', function () {
            var id = $(this).val();
            $('#job_site_name, #job_site_address').val('');
            resetEquipmentSection();
            if (id !== previousCustomerId) { clearPmGridValues(); previousCustomerId = id; }
            togglePmGridByCustomer();
            loadCustomerPmGrid(id);
            fetchCustomerLocations(id);
            prefillOtpDestination();
            resetReportSendState();
            updateServiceReportNo();
        });

        $('#customer_location_id').on('change', function () {
            var item = customerLocationsMap[$(this).val()];
            if (!item) return resetEquipmentSection();
            $('#job_site_name').val($.trim(item.location_name || item.warehouse_name || item.address_name || ''));
            $('#job_site_address').val(buildAddressText(item));
            fetchEquipmentTagsBySelection();
            updateServiceReportNo();
        });

        function resetReportSendState() { 
            $('#report_sent_confirmed').val(''); 
            $('#otp_challenge_id, #otp_verification_token, #otp_verified_channel, #otp_code').val(''); 
            $('.otp-digit-input').val('');
            $('#btn_verify_otp_big').removeClass('enabled').html('Verify <i class="fa fa-chevron-right"></i>').css('background', '');
            $('#otp_trigger_section, #verify_sign_section').hide();
            $('#report_send_feedback, #otp_feedback').text('');
        }

        function serializePmLogGrid() {
            var d = [];
            $('#pm_log_table tbody tr.pm-data-row').each(function() {
                var $r = $(this);
                d.push({section: $r.data('section'), parameter: $r.data('parameter'), ckt_01: $r.find('[data-ckt="ckt_01"]').val(), ckt_02: $r.find('[data-ckt="ckt_02"]').val(), ckt_03: $r.find('[data-ckt="ckt_03"]').val(), ckt_04: $r.find('[data-ckt="ckt_04"]').val()});
            });
            $('#pm_log_grid_json').val(JSON.stringify(d));
        }

        function clearPmGridValues() { $('#pm_log_table tbody tr.pm-data-row .pm-cell').val(''); $('#pm_log_grid_json').val(''); }

        function loadCustomerPmGrid(cid) {
            if (!cid) return;
            $.post(customerPmGridUrl, {customer_id: cid, [csrfName]: csrfHash}, function(res) {
                if (res.csrfHash) csrfHash = res.csrfHash;
                clearPmGridValues();
                if (res.status === 'success' && res.grid_rows) {
                    $('#pm_log_table tbody tr.pm-data-row').each(function() {
                        var $r = $(this), s = $r.data('section').toString().toLowerCase(), p = $r.data('parameter').toString().toLowerCase();
                        var match = res.grid_rows.find(i => i.section.toString().toLowerCase() === s && i.parameter.toString().toLowerCase() === p);
                        if (match) { $r.find('[data-ckt="ckt_01"]').val(match.ckt_01); $r.find('[data-ckt="ckt_02"]').val(match.ckt_02); $r.find('[data-ckt="ckt_03"]').val(match.ckt_03); $r.find('[data-ckt="ckt_04"]').val(match.ckt_04); }
                    });
                }
            }, 'json');
        }

        function togglePmGridByCustomer() {
            var cid = $('#customer_id').val();
            if (cid) {
                $('#pm_log_customer_name').text(customerAddresses[cid] ? customerAddresses[cid].name : $('#customer_id option:selected').text());
                $('#pm_log_customer_hint').hide(); $('#pm_log_customer_title, #pm_log_grid_wrapper').show();
            } else { $('#pm_log_customer_title, #pm_log_grid_wrapper').hide(); $('#pm_log_customer_hint').show(); }
        }

        function initSignaturePad(canvasId, hiddenId) {
            var canvas = document.getElementById(canvasId); if (!canvas) return;
            
            // Only set dimensions if visible and not already set correctly
            if (canvas.offsetWidth > 0 && (canvas.width !== canvas.offsetWidth || canvas.height !== canvas.offsetHeight)) {
                canvas.width = canvas.offsetWidth; 
                canvas.height = canvas.offsetHeight;
            }

            var ctx = canvas.getContext("2d"); ctx.strokeStyle = "#3b82f6"; ctx.lineWidth = 3; ctx.lineJoin = "round"; ctx.lineCap = "round";
            var existing = $("#" + hiddenId).val();
            if (existing) {
                var img = new Image(); img.onload = function() { ctx.drawImage(img, 0, 0); };
                img.src = (existing.indexOf('data:image') === 0) ? existing : "<?= base_url('assets/uploads/') ?>" + existing;
                $(canvas).siblings(".sig-hint").hide();
            }
            var drawing = false;
            function getPos(e) { 
                var r = canvas.getBoundingClientRect(); 
                var ev = e.originalEvent || e; 
                var touch = ev.touches && ev.touches[0] ? ev.touches[0] : (ev.changedTouches && ev.changedTouches[0] ? ev.changedTouches[0] : null);
                var cx = touch ? touch.clientX : ev.clientX;
                var cy = touch ? touch.clientY : ev.clientY;
                return { x: cx - r.left, y: cy - r.top }; 
            }
            function start(e) { drawing = true; ctx.beginPath(); var p = getPos(e); ctx.moveTo(p.x, p.y); $(canvas).siblings(".sig-hint").hide(); if (e.type === "touchstart") e.preventDefault(); }
            function move(e) { if (!drawing) return; var p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); if (e.type === "touchmove") e.preventDefault(); }
            function end() {
                if (!drawing) return; drawing = false;
                canvas.toBlob(function(b) {
                    if (!b) return; var fd = new FormData(); fd.append('signature_file', b, hiddenId + '.jpg'); fd.append(csrfName, csrfHash);
                    $.ajax({
                        url: "<?= site_url('service_requests/upload_signature'); ?>", type: 'POST', data: fd, contentType: false, processData: false,
                        success: function(res) { if (res.csrfHash) csrfHash = res.csrfHash; if (res.status === 'success' && res.filename) $('#' + hiddenId).val(res.filename).trigger('change'); }
                    });
                }, 'image/jpeg', 0.9);
            }
            canvas.addEventListener("mousedown", start); canvas.addEventListener("touchstart", start, {passive: false});
            window.addEventListener("mousemove", move); canvas.addEventListener("touchmove", move, {passive: false});
            window.addEventListener("mouseup", end); canvas.addEventListener("touchend", end);
        }

        // Initialize pads
        initSignaturePad("engineer_signature_pad", "engineer_signature_data");
        initSignaturePad("customer_signature_pad", "customer_signature_data");

        // Re-initialize pads when tab is shown to fix 0x0 size issue
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if ($(e.target).attr('href') === '#customer_details_tab') {
                initSignaturePad("engineer_signature_pad", "engineer_signature_data");
                initSignaturePad("customer_signature_pad", "customer_signature_data");
            }
        });

        window.clearSig = function(id) {
            var c = document.getElementById(id); if (!c) return;
            c.getContext('2d').clearRect(0, 0, c.width, c.height);
            $('#' + (id === 'engineer_signature_pad' ? 'engineer_signature_data' : 'customer_signature_data')).val('').trigger('change');
            $(c).siblings(".sig-hint").show();
        };

        // --- Global Equipment Modal ---
        var $m = $('#global_equipment_modal'), unmapped = [];
        $("#btn_open_global_equipment").click(function(){ if(!$('#customer_id').val()) return alert('Select customer'); $m.fadeIn(200); fetchUnmapped($('#customer_id').val()); });
        function fetchUnmapped(cid) {
            $("#unmapped_list").html('<div class="text-center" style="padding:20px;"><i class="fa fa-spinner fa-spin"></i> Fetching...</div>');
            $.getJSON("<?= site_url('service_requests/get_unmapped_equipment_products'); ?>", {customer_id: cid}, function(res){ 
                unmapped = res.data || []; 
                unmapped.sort(function(a, b) {
                    var nA = (a.name || "").toLowerCase();
                    var nB = (b.name || "").toLowerCase();
                    if (nA < nB) return -1;
                    if (nA > nB) return 1;
                    return 0;
                });
                renderUnmapped(""); 
            });
        }
        function renderUnmapped(f) {
            var h = '', q = f.toLowerCase();
            var filtered = unmapped.filter(p => (p.name||"").toLowerCase().includes(q) || (p.code||"").toLowerCase().includes(q));
            if (!filtered.length) h = '<div class="text-center" style="padding:20px;">No products found.</div>';
            else filtered.forEach(p => {
                h += `<div class="unmapped-item-row" style="padding:15px; border-bottom:1px solid #f1f5f9; background:#fff;">
                        <div style="font-weight:700; color:#1e293b; margin-bottom:10px;">${p.name}</div>
                        <div style="display:flex; gap:10px;">
                            <input type="text" class="form-control inline-model" placeholder="Model" style="height:34px;">
                            <input type="text" class="form-control inline-serial" placeholder="Serial" style="height:34px;">
                            <button type="button" class="btn btn-sm btn-info btn-map-inline" data-id="${p.id}" data-name="${p.name}">Select</button>
                        </div>
                    </div>`;
            });
            $("#unmapped_list").html(h);
        }
        $('#equipment_search_input').on('input', function() { renderUnmapped($(this).val()); });
        $(document).on('click', '.btn-map-inline', function() {
            var $r = $(this).closest('.unmapped-item-row'), data = { product_id: $(this).data('id'), product_name: $(this).data('name'), model_no: $r.find('.inline-model').val(), serial_no: $r.find('.inline-serial').val(), customer_id: $('#customer_id').val(), location_id: $('#customer_location_id').val(), [csrfName]: csrfHash };
            if(!data.model_no || !data.serial_no) return alert('Model_no and Serial_no Required.');
            $.post("<?= site_url('service_requests/add_new_equipment'); ?>", data, function(res) { 
                if (res.csrfHash) csrfHash = res.csrfHash; 
                if (res.status === 'success') { 
                    $m.fadeOut(200); 
                    var eq = res.equipment || {tag_no: res.tag_no || data.serial_no, model_no: data.model_no, serial_no: data.serial_no};
                    var $dropdown = $('#eqpt_tag_no');
                    if ($dropdown.find('option[value="' + eq.tag_no + '"]').length === 0) {
                        $dropdown.append($('<option/>', { value: eq.tag_no, text: eq.tag_no }));
                    }
                    $dropdown.val(eq.tag_no);
                    $('#model_no').val(eq.model_no || "");
                    $('#serial_no').val(eq.serial_no || "");
                    $('#equipment_id').val(eq.id || "");
                    
                    $('#multi_equipment_row').show();
                    var h = `<tr>
                               <td style="text-align:center;"><input type="radio" name="select_multi_eq" class="select-multi-eq" data-id="${eq.id || ''}" data-model="${eq.model_no || ''}" data-serial="${eq.serial_no || ''}" checked></td>
                               <td style="text-align:center;">${eq.model_no || '-'}</td>
                               <td style="text-align:center;">${eq.serial_no || '-'}</td>
                             </tr>`;
                    $('#multi_equipment_grid').html(h);
                    $('#multi_equipment_wrapper').find('h5').html('<i class="fa fa-info-circle text-info"></i> Equipment Details:');

                    window.isManuallySettingEquipment = true;
                    $dropdown.trigger('change');
                } else alert(res.message); 
            }, 'json');
        });
        $('#btn_save_new_equipment').click(function() {
            var data = { customer_id: $('#customer_id').val(), location_id: $('#customer_location_id').val(), product_name: $('#new_eq_product_name').val(), brand: $('#new_eq_brand_input').val(), model_no: $('#new_eq_model_no').val(), serial_no: $('#new_eq_serial_no').val(), [csrfName]: csrfHash };
            $.post("<?= site_url('service_requests/add_new_equipment'); ?>", data, function(res) { 
                if (res.csrfHash) csrfHash = res.csrfHash; 
                if (res.status === 'success') { 
                    $m.fadeOut(200); 
                    var eq = res.equipment || {tag_no: res.tag_no || data.serial_no, model_no: data.model_no, serial_no: data.serial_no, id: res.id || ''};
                    var $dropdown = $('#eqpt_tag_no');
                    if ($dropdown.find('option[value="' + eq.tag_no + '"]').length === 0) {
                        $dropdown.append($('<option/>', { value: eq.tag_no, text: eq.tag_no }));
                    }
                    $dropdown.val(eq.tag_no);
                    $('#model_no').val(eq.model_no || "");
                    $('#serial_no').val(eq.serial_no || "");
                    $('#equipment_id').val(eq.id || "");
                    
                    $('#multi_equipment_row').show();
                    var h = `<tr>
                               <td style="text-align:center;"><input type="radio" name="select_multi_eq" class="select-multi-eq" data-id="${eq.id || ''}" data-model="${eq.model_no || ''}" data-serial="${eq.serial_no || ''}" checked></td>
                               <td style="text-align:center;">${eq.model_no || '-'}</td>
                               <td style="text-align:center;">${eq.serial_no || '-'}</td>
                             </tr>`;
                    $('#multi_equipment_grid').html(h);
                    $('#multi_equipment_wrapper').find('h5').html('<i class="fa fa-info-circle text-info"></i> Equipment Details:');

                    window.isManuallySettingEquipment = true;
                    $dropdown.trigger('change');
                } else alert(res.message); 
            }, 'json');
        });

        $('#new_eq_brand_input').on('focus input', function() {
            $.getJSON("<?= site_url('service_requests/get_brands'); ?>", {term: $(this).val()}, function(res) {
                if(res.status === 'success' && res.data) {
                    var h = ''; res.data.forEach(b => { h += `<div class="brand-item" data-name="${b.name}" style="padding:10px; cursor:pointer;">${b.name}</div>`; });
                    $('#brand_search_results').html(h).show();
                }
            });
        });
        $(document).on('click', '.brand-item', function() { $('#new_eq_brand_input').val($(this).data('name')); $('#brand_search_results').hide(); });

        $('#eqpt_tag_no').on('change', function() {
            if (window.isManuallySettingEquipment) {
                window.isManuallySettingEquipment = false;
                return;
            }
            var t = $(this).val(), c = $('#customer_id').val(), l = $('#customer_location_id').val();
            if (!t) {
                $('#model_no, #serial_no, #equipment_id').val('');
                $('#multi_equipment_row').hide();
                $('#multi_equipment_grid').empty();
                return;
            }
            if (!c) return;
            $.post(equipmentDetailsUrl, {equipment_tag: t, company_id: c, location_id: l, [csrfName]: csrfHash}, function(res) {
                if (res.csrfHash) csrfHash = res.csrfHash;
                $('#multi_equipment_row').hide();
                $('#multi_equipment_grid').empty();
                
                if (res.status === 'success' && res.details) {
                    var details = Array.isArray(res.details) ? res.details : [res.details];
                    if (details.length >= 1) {
                        $('#multi_equipment_row').show();
                        var h = '';
                        details.forEach(function(item) {
                            var isChecked = details.length === 1 ? 'checked' : '';
                            h += `<tr>
                                    <td style="text-align:center;"><input type="radio" name="select_multi_eq" class="select-multi-eq" data-id="${item.id}" data-model="${item.model_no || ''}" data-serial="${item.serial_no || ''}" ${isChecked}></td>
                                    <td style="text-align:center;">${item.model_no || '-'}</td>
                                    <td style="text-align:center;">${item.serial_no || '-'}</td>
                                  </tr>`;
                        });
                        $('#multi_equipment_grid').html(h);
                        
                        var titleElem = $('#multi_equipment_wrapper').find('h5');
                        if (details.length === 1) {
                            var item = details[0];
                            $('#equipment_id').val(item.id || '');
                            $('#model_no').val(item.model_no || '');
                            $('#serial_no').val(item.serial_no || '');
                            titleElem.html('<i class="fa fa-info-circle text-info"></i> Equipment Details:');
                        } else {
                            $('#equipment_id').val('');
                            $('#model_no').val('');
                            $('#serial_no').val('');
                            titleElem.html('<i class="fa fa-info-circle text-info"></i> Multiple records found for this Tag. Please select one:');
                        }
                    }
                }
            }, 'json');
        });

        $(document).on('change', '.select-multi-eq', function() {
            var $r = $(this);
            $('#equipment_id').val($r.data('id'));
            $('#model_no').val($r.data('model'));
            $('#serial_no').val($r.data('serial'));
        });

        $('#btn_send_report_customer').on('click', function () {
            if (!$('#customer_id').val()) return alert('Select customer');
            serializePmLogGrid();
            $('#report_send_feedback').text('Sending...').css('color', '#3b82f6');
            var p = $('form[role="form"]').serializeArray().reduce((o, i) => { o[i.name] = i.value; return o; }, {});
            p[csrfName] = csrfHash;
            $.post(sendServiceReportToCustomerUrl, p, function(res) {
                if (res.csrfHash) csrfHash = res.csrfHash;
                if (res.status === 'success') { 
                    $('#report_sent_confirmed').val('1'); 
                    $('#report_send_feedback').text('Report sent to customer successfully.').css('color', '#10b981');
                    $('#otp_trigger_section').slideDown();
                } else {
                    $('#report_send_feedback').text(res.message).css('color', '#ef4444');
                }
            }, 'json');
        });

        // OTP Channel buttons
        function triggerOtp(channel) {
            if (!$('#report_sent_confirmed').val()) { alert('Please send report to customer first'); return; }
            var cid = $('#customer_id').val();
            if (!cid || !customerAddresses[cid]) return alert('Select customer first');
            var destination = (channel === 'email') ? customerAddresses[cid].email : customerAddresses[cid].phone;
            if (!destination) return alert('No ' + channel + ' address found for customer.');
            
            $('#otp_verified_channel').val(channel);
            var d = { channel: channel, destination: destination, customer_id: cid, context_ref: $('#otp_context_ref').val(), [csrfName]: csrfHash };
            
            $('#otp_feedback').text('Sending OTP...').css('color', '#3b82f6');
            
            $.post(sendServiceReportOtpUrl, d, function(res) { 
                if (res.csrfHash) csrfHash = res.csrfHash; 
                if (res.status === 'success') { 
                    $('#otp_challenge_id').val(res.challenge_id || ""); 
                    if (res.context_ref) $('#otp_context_ref').val(res.context_ref);
                    $('#otp_feedback').text('OTP Sent successfully to ' + destination).css('color', '#10b981');
                    $('#verify_sign_section').slideDown();
                } else {
                    $('#otp_feedback').text(res.message).css('color', '#ef4444');
                }
            }, 'json');
        }

        $('#btn_send_otp_email').click(function() { triggerOtp('email'); });
        $('#btn_send_otp_sms').click(function() { triggerOtp('sms'); });

        // Auto move OTP digits - Mobile style
        $(document).on("input", ".otp-digit-input", function (e) {
            var $inputs = $(".otp-digit-input"), idx = $inputs.index(this), val = $(this).val();
            if (val.length > 1) $(this).val(val.slice(0, 1)); // Handle paste or multi-char
            if (val.length === 1 && idx < $inputs.length - 1) $inputs.eq(idx + 1).focus();
            
            var code = ""; $inputs.each(function() { code += $(this).val(); });
            $("#otp_code").val(code);
            $("#btn_verify_otp_big").toggleClass("enabled", code.length === 6);
        });

        $(document).on("keydown", ".otp-digit-input", function (e) {
            if (e.key === "Backspace" && !$(this).val()) {
                var $inputs = $(".otp-digit-input"), idx = $inputs.index(this);
                if (idx > 0) $inputs.eq(idx - 1).focus();
            }
        });

        $('#btn_verify_otp_big').on('click', function () {
            $('#otp_feedback').text('Verifying...').css('color', '#3b82f6');
            var d = { challenge_id: $('#otp_challenge_id').val(), otp_code: $('#otp_code').val(), context_ref: $('#otp_context_ref').val(), channel: $('#otp_verified_channel').val(), [csrfName]: csrfHash };
            var $btn = $(this);
            var originalText = $btn.html();
            $btn.html('Verifying...').removeClass('enabled');
            
            $.post(verifyServiceReportOtpUrl, d, function(res) { 
                if (res.csrfHash) csrfHash = res.csrfHash; 
                if (res.status === 'success') { 
                    $('#otp_verification_token').val(res.verification_token); 
                    $btn.html('Verified <i class="fa fa-check"></i>').css('background', '#10b981');
                    $('#otp_feedback').text('OTP Verified!').css('color', '#10b981');
                    alert("OTP Verified. Proceed to Signatures tab.");
                    $('a[href="#customer_details_tab"]').tab('show'); // Move to signatures
                } else { 
                    $('#otp_feedback').text(res.message || "Invalid OTP").css('color', '#ef4444');
                    $btn.html(originalText).addClass('enabled');
                } 
            }, 'json');
        });

        $('form[role="form"]').on('submit', function() {
            serializePmLogGrid();
            if (!$('#report_sent_confirmed').val()) { alert('Send report first'); return false; }
            if (!$('#otp_verification_token').val()) { alert('Verify OTP first'); return false; }
        });

        function checkSubmitBtn() {
            var engSig = $.trim($("#engineer_signature_data").val());
            var custSig = $.trim($("#customer_signature_data").val());
            var engSigned = engSig.length > 5; 
            var custSigned = custSig.length > 5; 
            $('button[type="submit"]').prop('disabled', !(engSigned && custSigned));
        }

        $(document).on("change", "#engineer_signature_data, #customer_signature_data", function () {
            checkSubmitBtn();
        });

        // Logic for Category Pills and Sub Tabs
        $('.cat-pill').on('click', function() {
            var group = $(this).data('group');
            $('.cat-pill').removeClass('active');
            $(this).addClass('active');
            
            $('.category-group').removeClass('active');
            if (group === 'service_report') {
                $('#group_service_report').addClass('active');
            } else if (group === 'pm_log') {
                $('#pm_log_tab').addClass('active');
            } else if (group === 'report_trigger') {
                $('#report_trigger_tab').addClass('active');
            } else if (group === 'signatures') {
                $('#customer_details_tab').addClass('active');
            }
        });

        $('.sub-nav-tabs .nav-item').on('click', function() {
            var tabId = $(this).data('tab');
            $('.sub-nav-tabs .nav-item').removeClass('active');
            $(this).addClass('active');
            
            $('#group_service_report .tab-pane').removeClass('active');
            $('#' + tabId).addClass('active');
        });

        // Initial state
        togglePmGridByCustomer();
        if (previousCustomerId) { loadCustomerPmGrid(previousCustomerId); fetchCustomerLocations(previousCustomerId); }
        updateServiceReportNo();
        
        // Show OTP sections if already in progress
        if ($('#report_sent_confirmed').val() == '1') { $('#otp_trigger_section').show(); }
        if ($('#otp_challenge_id').val()) { $('#verify_sign_section').show(); }
        
        // Load initial tab from URL if needed
        var urlTab = "<?= $tab ?>";
        if (urlTab === 'pm_log') { $('[data-group="pm_log"]').click(); }
        else if (urlTab === 'report_trigger') { $('[data-group="report_trigger"]').click(); }
        else if (urlTab === 'signatures') { $('[data-group="signatures"]').click(); }
        else if (urlTab === 'equipment') { $('[data-tab="equipment_tab"]').click(); }
        else if (urlTab === 'work') { $('[data-tab="work_details_tab"]').click(); }

        checkSubmitBtn();
    });
</script>



<div id="global_equipment_modal">
    <div class="modal-content-parity">
        <div id="equipment_selection_view">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;"><h3 style="margin:0; font-weight:700;">Select Equipment</h3><button type="button" onclick="$('#global_equipment_modal').fadeOut(200);" class="close" style="font-size:28px;">&times;</button></div>
            <input type="text" id="equipment_search_input" class="form-control" placeholder="Search products..." style="border-radius:8px;">
            <div id="unmapped_list"></div>
            <div style="margin-top:25px; text-align:center;"><button type="button" onclick="$('#equipment_selection_view').hide(); $('#add_equipment_form_view').show();" class="btn btn-block btn-info" style="padding:12px; font-weight:600; border-radius:8px;">+ Add New Custom Equipment</button></div>
        </div>
        <div id="add_equipment_form_view" style="display:none;">
            <h3 style="margin-top:0; font-weight:700; margin-bottom:20px;">Register New Equipment</h3>
            <div class="form-group"><label>Product Name *</label><input type="text" id="new_eq_product_name" class="form-control"></div>
            <div class="form-group" style="position:relative;"><label>Brand</label><input type="text" id="new_eq_brand_input" class="form-control"><div id="brand_search_results" class="brand-results-container" style="display:none;"></div></div>
            <div class="row">
                <div class="col-sm-6"><div class="form-group"><label>Model No *</label><input type="text" id="new_eq_model_no" class="form-control"></div></div>
                <div class="col-sm-6"><div class="form-group"><label>Serial No *</label><input type="text" id="new_eq_serial_no" class="form-control"></div></div>
            </div>
            <div style="margin-top:30px; display:flex; gap:15px;"><button type="button" onclick="$('#add_equipment_form_view').hide(); $('#equipment_selection_view').show();" class="btn btn-default" style="flex:1;">Cancel</button><button type="button" id="btn_save_new_equipment" class="btn btn-success" style="flex:1;">Save & Map</button></div>
        </div>
    </div>
</div>
