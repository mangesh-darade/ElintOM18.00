<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Service Site Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #222; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 8px; }
        .subtitle { color: #666; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: top; }
        th { width: 24%; background: #f8f8f8; text-align: left; }
        .section-title { font-size: 13px; font-weight: bold; margin: 14px 0 6px; }
        .muted { color: #666; }
    </style>
</head>
<body>
<?php
$report = isset($report) && is_array($report) ? $report : array();
$customerName = '';
if (isset($customer->name)) {
    $customerName = (string) $customer->name;
} elseif (isset($report['customer_name'])) {
    $customerName = (string) $report['customer_name'];
}
$value = function ($key, $default = '-') use ($report) {
    return isset($report[$key]) && trim((string) $report[$key]) !== '' ? (string) $report[$key] : $default;
};
?>

<div class="title">Service Site Report</div>
<div class="subtitle">
    Report No: <strong><?= htmlspecialchars($value('service_report_no', 'N/A'), ENT_QUOTES, 'UTF-8'); ?></strong>
</div>

<div class="section-title">Customer & Service Details</div>
<table>
    <tr>
        <th>Customer</th>
        <td><?= htmlspecialchars($customerName !== '' ? $customerName : '-', ENT_QUOTES, 'UTF-8'); ?></td>
        <th>Date</th>
        <td><?= htmlspecialchars($value('service_date'), ENT_QUOTES, 'UTF-8'); ?></td>
    </tr>
    <tr>
        <th>Service Type</th>
        <td><?= htmlspecialchars($value('service_type'), ENT_QUOTES, 'UTF-8'); ?></td>
        <th>Job Site Name</th>
        <td><?= htmlspecialchars($value('job_site_name'), ENT_QUOTES, 'UTF-8'); ?></td>
    </tr>
    <tr>
        <th>Job Site Address</th>
        <td colspan="3"><?= nl2br(htmlspecialchars($value('job_site_address'), ENT_QUOTES, 'UTF-8')); ?></td>
    </tr>
</table>

<div class="section-title">Equipment Details</div>
<table>
    <tr>
        <th>Equipment Tag No.</th>
        <td><?= htmlspecialchars($value('eqpt_tag_no'), ENT_QUOTES, 'UTF-8'); ?></td>
        <th>Model No.</th>
        <td><?= htmlspecialchars($value('model_no'), ENT_QUOTES, 'UTF-8'); ?></td>
    </tr>
    <tr>
        <th>Serial No.</th>
        <td><?= htmlspecialchars($value('serial_no'), ENT_QUOTES, 'UTF-8'); ?></td>
        <th>Engineer Name</th>
        <td><?= htmlspecialchars($value('engineer_name'), ENT_QUOTES, 'UTF-8'); ?></td>
    </tr>
    <tr>
        <th>Activity</th>
        <td colspan="3"><?= nl2br(htmlspecialchars($value('activity'), ENT_QUOTES, 'UTF-8')); ?></td>
    </tr>
    <tr>
        <th>Action List</th>
        <td colspan="3"><?= nl2br(htmlspecialchars($value('action_list'), ENT_QUOTES, 'UTF-8')); ?></td>
    </tr>
</table>

<div class="section-title">Work Order & Spares</div>
<table>
    <tr>
        <th>Work Order Status</th>
        <td><?= htmlspecialchars($value('work_order_status'), ENT_QUOTES, 'UTF-8'); ?></td>
        <th>Job Completed</th>
        <td><?= htmlspecialchars($value('job_completed'), ENT_QUOTES, 'UTF-8'); ?></td>
    </tr>
    <tr>
        <th>Quotation Required</th>
        <td><?= htmlspecialchars($value('quotation_required'), ENT_QUOTES, 'UTF-8'); ?></td>
        <th>Used Spare Parts</th>
        <td><?= nl2br(htmlspecialchars($value('used_spare_parts'), ENT_QUOTES, 'UTF-8')); ?></td>
    </tr>
    <tr>
        <th>Required Spare Parts</th>
        <td><?= nl2br(htmlspecialchars($value('required_spare_parts'), ENT_QUOTES, 'UTF-8')); ?></td>
        <th>Quotation Description</th>
        <td><?= nl2br(htmlspecialchars($value('quotation_description'), ENT_QUOTES, 'UTF-8')); ?></td>
    </tr>
</table>

<?php
// PM Log — works for both desktop (pm_log_grid key) and mobile (pm_log_grid_json key)
$_pmRaw = '';
if (isset($report['pm_log_grid']) && !empty($report['pm_log_grid'])) {
    $_pmRaw = $report['pm_log_grid'];
} elseif (isset($report['pm_log_grid_json']) && !empty($report['pm_log_grid_json'])) {
    $_pmRaw = $report['pm_log_grid_json'];
}

$_pmRows = null;
if (is_string($_pmRaw) && trim($_pmRaw) !== '') {
    $_pmRows = json_decode($_pmRaw, true);
}

if (is_array($_pmRows) && !empty($_pmRows)):
?>
<div class="section-title">PM Log</div>
<table>
    <thead>
        <tr style="background:#f8f8f8;">
            <th style="width:14%;">Section</th>
            <th style="width:38%;">Parameter</th>
            <th>CKT 01</th>
            <th>CKT 02</th>
            <th>CKT 03</th>
            <th>CKT 04</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($_pmRows as $_pmRow): ?>
        <tr>
            <td style="font-size:10px;"><?php echo htmlspecialchars((string)(isset($_pmRow['section']) ? $_pmRow['section'] : ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td style="font-size:10px;"><?php echo htmlspecialchars((string)(isset($_pmRow['parameter']) ? $_pmRow['parameter'] : ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td style="font-size:10px;"><?php echo htmlspecialchars((string)(isset($_pmRow['ckt_01']) ? $_pmRow['ckt_01'] : ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td style="font-size:10px;"><?php echo htmlspecialchars((string)(isset($_pmRow['ckt_02']) ? $_pmRow['ckt_02'] : ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td style="font-size:10px;"><?php echo htmlspecialchars((string)(isset($_pmRow['ckt_03']) ? $_pmRow['ckt_03'] : ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <td style="font-size:10px;"><?php echo htmlspecialchars((string)(isset($_pmRow['ckt_04']) ? $_pmRow['ckt_04'] : ''), ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="section-title">Remarks & Signatures</div>
<table>
    <tr>
        <th>Engineer Remarks</th>
        <td><?= nl2br(htmlspecialchars($value('engineer_remarks'), ENT_QUOTES, 'UTF-8')); ?></td>
        <th>Customer Remark</th>
        <td><?= nl2br(htmlspecialchars($value('customer_remark'), ENT_QUOTES, 'UTF-8')); ?></td>
    </tr>
    <tr>
        <th>Customer Signature Name</th>
        <td><?= htmlspecialchars($value('customer_signature_name'), ENT_QUOTES, 'UTF-8'); ?></td>
        <th>Generated By</th>
        <td><?= htmlspecialchars(isset($this->Settings->site_name) ? (string) $this->Settings->site_name : 'System', ENT_QUOTES, 'UTF-8'); ?></td>
    </tr>
    <tr>
        <th>Engineer Signature</th>
        <td style="text-align: center;">
            <?php 
                $engSig = $value('engineer_signature');
                if ($engSig !== '-' && !empty($engSig)) {
                    // Engineer signature is currently Base64
                    echo '<img src="' . $engSig . '" style="max-height: 60px; max-width: 150px;">';
                } else {
                    echo '<span class="muted">No Signature</span>';
                }
            ?>
        </td>
        <th>Customer Signature</th>
        <td style="text-align: center;">
            <?php 
                $custSig = $value('customer_signature');
                if ($custSig !== '-' && !empty($custSig)) {
                    // Check if it's a file name or Base64
                    $src = (strpos($custSig, 'data:image') === 0) ? $custSig : base_url('assets/uploads/signatures/' . $custSig);
                    echo '<img src="' . $src . '" style="max-height: 60px; max-width: 150px;">';
                } else {
                    echo '<span class="muted">No Signature</span>';
                }
            ?>
        </td>
    </tr>
</table>

<p class="muted" style="text-align:center;font-size:10px;">This is a system generated service report PDF.</p>

<?php if (isset($this->Settings->watermark) && $this->Settings->watermark == 1): ?>
    <div style="text-align: right; margin-top: 30px; margin-right: 15px;">
        <span style="font-size: 12px; color: #555; opacity: 0.6;">
            Powered by : 
        </span>
        <img src="<?= base_url('assets/images/ElintOm_Logo.png'); ?>" alt="ElintOm Watermark" style="max-height: 35px; opacity: 0.3;">
    </div>
<?php endif; ?>

</body>
</html>
