<?php
/**
 * Phase 4 — Attendance deep-link tests (Owner)
 * Run: php phase4_attendance_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_attendance_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/attendance';

if (!$identity || !$password) {
    echo "Usage: php phase4_attendance_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$attendanceId = null;
$userId = null;

$r = phase4_httpGetFollow("$base$prefix", $cookieFile);
if (preg_match('/name="val\[\]"\s+value="(\d+)"/', $r['body'], $m)) {
    $attendanceId = (int) $m[1];
}

try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        if (!$attendanceId) {
            $q = $m->query('SELECT id FROM sma_attendance ORDER BY id DESC LIMIT 1');
            if ($q && $q->num_rows) {
                $attendanceId = (int) $q->fetch_assoc()['id'];
            }
        }
        $q = $m->query('SELECT id FROM sma_users WHERE company_id IS NULL ORDER BY id ASC LIMIT 1');
        if ($q && $q->num_rows) {
            $userId = (int) $q->fetch_assoc()['id'];
        }
    }
} catch (Throwable $e) {
    // optional DB discovery
}

$links = [];
if ($attendanceId) {
    $links['details'] = "$prefix/details/$attendanceId";
    $links['details_modal'] = "$prefix/details/$attendanceId?modal=1";
    $links['edit'] = "$prefix/edit/$attendanceId";
    $links['edit_modal'] = "$prefix/edit/$attendanceId?modal=1";
}
if ($userId) {
    $links['edit_user'] = "$prefix/edit_user/$userId";
}

$today = date('Y-m-d');
$links['report_filtered'] = "$prefix/report?start_date=$today&end_date=$today";

foreach ($links as $label => $path) {
    phase4_linkGet($base, "ATT $label", $path, $cookieFile, [200], $identity, $password);
}

if (!$attendanceId) {
    phase4_check('ATT attendance id discovery', true, 'skipped — no attendance rows');
}
if (!$userId) {
    phase4_check('ATT user id discovery', true, 'skipped — no staff user');
}

echo "\n=== Attendance deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
