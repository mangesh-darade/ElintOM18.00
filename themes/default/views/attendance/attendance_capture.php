<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? html_escape($page_title) : 'Capture Attendance'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= $assets; ?>styles/theme.css" rel="stylesheet">
    <link href="<?= $assets; ?>styles/style.css" rel="stylesheet">
    <style>
        body { background: #e8f0fb; font-family: "Inter", "Helvetica Neue", Helvetica, Arial, sans-serif; margin: 0; color: #0f172a; }
        .attendance-shell { min-height: 100vh; display: block; padding: 0; box-sizing: border-box; }
        .attendance-content { width: 100%; max-width: none; }
        .attendance-panel { border: 0; border-radius: 0; background: #fff; box-shadow: none; min-height: 100vh; overflow: hidden; }
        .attendance-header { background: #e7ebf1e3; color: #fff; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .attendance-logo { width: auto; height: 34px; object-fit: contain; max-width: 45%; }
        .attendance-logo-slot { width: 45%; height: 34px; }
        .attendance-body { padding: 14px; padding-bottom: 88px; }
        .attendance-select-wrap { position: relative; }
        .attendance-select-wrap::after { content: ""; position: absolute; right: 12px; top: 50%; margin-top: -3px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid #64748b; pointer-events: none; }
        .attendance-top-controls .form-control { height: 42px; border-radius: 10px !important; border: 1px solid #d1dae6; box-shadow: none; font-size: 14px; padding: 8px 32px 8px 12px; appearance: none; background-image: none; }
        #attendanceType { display: none; }
        .attendance-toggle-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 12px 0 8px; }
        .attendance-toggle-btn { height: 38px; border-radius: 8px; border: 1px solid #b8c9da; background: transparent; color: #334155; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.4px; }
        .attendance-toggle-btn.active { background: rgba(15, 109, 122, 0.14); color: #1a5e66; border-color: #1a5e66; }
        /* .attendance-toggle-btn:disabled { opacity: 0.45; cursor: not-allowed; } */
        .attendance-last-checkin { font-size: 13px; color: #475569; margin: 4px 0 12px; }
        .attendance-notes-wrap { margin-bottom: 12px; }
        .attendance-notes-label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .attendance-notes { width: 100%; min-height: 78px; border-radius: 10px; border: 1px solid #d1dae6; padding: 8px 10px; font-size: 13px; resize: vertical; box-sizing: border-box; }
        .attendance-camera-frame { width: 100%; aspect-ratio: 16 / 9; border: 1px solid #dce5f1; border-radius: 12px; background: #0b1220; overflow: hidden; }
        .attendance-camera { width: 100%; height: 100%; display: block; background: #000; object-fit: cover; }
        #liveFaceStatus { font-size: 13px; margin-top: 10px; min-height: 18px; text-align: center; color: #6b7280 !important; font-weight: 500; }
        .attendance-powered { margin-top: 10px; text-align: center; color: rgba(15, 23, 42, 0.62); font-size: 12px; }
        .attendance-powered-logo { height: 14px; width: auto; opacity: 0.9; object-fit: contain; }
        .attendance-sticky-bar { position: fixed; left: 0; right: 0; bottom: 0; background: #fff; border-top: 1px solid #dbe5f1; box-shadow: 0 -8px 20px rgba(15, 23, 42, 0.08); z-index: 10040; padding: 8px 10px; }
        .attendance-sticky-inner { max-width: none; margin: 0; }
        #captureAttendanceBtn { background: #1a5e66; border: 1px solid #1a5e66; color: #fff; min-height: 44px; border-radius: 10px !important; font-weight: 700; }
        .attendance-sticky-inner #captureAttendanceBtn { width: 100%; display: block; }
        .attendance-toast-container { position: fixed; top: 12px; right: 12px; left: auto; z-index: 100050; max-width: min(360px, calc(100vw - 24px)); pointer-events: none; }
        .attendance-toast { pointer-events: auto; margin-bottom: 8px; padding: 12px 16px; border-radius: 6px; box-shadow: 0 4px 18px rgba(0, 0, 0, 0.18); font-size: 14px; line-height: 1.4; opacity: 0; transform: translateX(12px); transition: opacity 0.25s ease, transform 0.25s ease; word-break: break-word; }
        .attendance-toast-show { opacity: 1; transform: translateX(0); }
        .attendance-toast-success { background: #dff0d8; border: 1px solid #d6e9c6; color: #3c763d; }
        .attendance-toast-error { background: #f2dede; border: 1px solid #ebccd1; color: #a94442; }
        .attendance-toast-info { background: #d9edf7; border: 1px solid #bce8f1; color: #31708f; }
        @media (max-width: 767px) {
            .attendance-shell { padding: 0; }
            .attendance-logo { height: 28px; }
            .attendance-body { padding: 10px; padding-bottom: 84px; }
            .attendance-toast-container { top: 8px; right: 8px; left: 8px; max-width: none; }
            .attendance-toast { transform: translateY(-8px); }
            .attendance-toast-show { transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="attendance-shell">
    <div class="attendance-content">
        <div class="attendance-panel">
            <div class="attendance-header">
                <img class="attendance-logo" src="<?= base_url('assets/images/ElintOm_Logo.png'); ?>" alt="ElintOm">
                <?php if (!empty($Settings->logo2)) : ?>
                    <img class="attendance-logo" src="<?= base_url('assets/mdata/' . $Customer_assets . '/uploads/logos/' . $Settings->logo2); ?>" alt="<?= html_escape($Settings->site_name); ?>" onerror="this.style.display='none';this.parentNode.querySelector('.attendance-logo-fallback').style.display='inline-block';">
                    <span class="attendance-logo-fallback" style="display:none; font-size:12px; font-weight:600; color:#111827; text-align:right; max-width:45%; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"><?= html_escape($Settings->site_name); ?></span>
                <?php else : ?>
                    <span class="attendance-logo-slot" style="font-size:12px; font-weight:600; color:#111827; text-align:right; display:inline-flex; align-items:center; justify-content:flex-end; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"><?= html_escape($Settings->site_name); ?></span>
                <?php endif; ?>
            </div>
            <div class="attendance-body">
                <div class="attendance-top-controls">
                    <div class="attendance-select-wrap" id="locationPickerWrap" style="display: none;">
                        <select id="locationPicker" class="form-control">
                            <option value="">Select nearby location</option>
                        </select>
                    </div>
                    <div id="locationPickerHint" style="margin-top:6px; font-size:12px; color:#b45309; display:none;">
                        Multiple nearby locations found. Please select one location.
                    </div>
                    <?php
                    $defType = isset($default_attendance_type) ? strtolower((string) $default_attendance_type) : 'auto';
                    $selectAuto = in_array($defType, array('auto', 'check_in'), true);
                    $selectOut = ($defType === 'check_out');
                    ?>
                    <select id="attendanceType" class="form-control">
                        <option value="auto" <?= $selectAuto ? 'selected' : ''; ?>>In</option>
                        <option value="check_out" <?= $selectOut ? 'selected' : ''; ?>>Out</option>
                    </select>
                    <div class="attendance-toggle-row">
                        <button type="button" class="attendance-toggle-btn" id="attendanceInBtn">In</button>
                        <button type="button" class="attendance-toggle-btn" id="attendanceOutBtn">Out</button>
                    </div>
                    <div class="attendance-last-checkin">Last activity: <span id="lastActivityText">--</span></div>
                </div>
                <input type="hidden" id="derivedLocationDisplay" value="None">
                <div class="attendance-notes-wrap">
                    <label class="attendance-notes-label" for="attendanceNotes">Notes</label>
                    <textarea id="attendanceNotes" class="attendance-notes" placeholder="Enter notes..."></textarea>
                </div>
                <div class="attendance-camera-frame">
                    <video id="attendanceVideo" class="attendance-camera" width="480" height="360" autoplay muted playsinline></video>
                </div>
                <canvas id="attendanceCanvas" width="480" height="360" style="display:none;"></canvas>
                <div id="liveFaceStatus">No face detected</div>
                <div class="attendance-powered">
                    Powered by
                    <a href="https://elintom.io" target="_blank" rel="noopener noreferrer" aria-label="ElintOm">
                        <img class="attendance-powered-logo" src="<?= base_url('assets/logs/elintomfevicon.png'); ?>" alt="ElintOm" onerror="this.onerror=null;this.src='<?= base_url('assets/images/ElintOm_Logo.png'); ?>';">
                    </a>
                </div>
                <input type="hidden" id="latitude">
                <input type="hidden" id="longitude">
                <input type="hidden" id="landmark">
            </div>
        </div>
    </div>
</div>
<div class="attendance-sticky-bar">
    <div class="attendance-sticky-inner">
        <button type="button" class="btn" id="captureAttendanceBtn">Capture</button>
    </div>
</div>

<script src="<?= $assets; ?>js/jquery-2.0.3.min.js"></script>
<script>
window.attendanceConfig = {
    postUrl: "<?= site_url('attendance/store'); ?>",
    deriveUrl: "<?= site_url('attendance/derive_location_preview'); ?>",
    csrfName: "<?= $this->security->get_csrf_token_name(); ?>",
    csrfHash: "<?= $this->security->get_csrf_hash(); ?>",
    kioskMode: <?= !empty($attendance_kiosk_mode) ? 'true' : 'false' ?>
};
</script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="<?= $assets; ?>js/attendance_face.js"></script>
</body>
</html>

