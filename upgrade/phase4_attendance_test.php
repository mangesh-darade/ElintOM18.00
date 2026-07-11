<?php
/**
 * Phase 4 — Attendance screen tests (Owner)
 * Run: php phase4_attendance_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_attendance_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/attendance';

if (!$identity || !$password) {
    echo "Usage: php phase4_attendance_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Attendance list' => $prefix,
    'Attendance report' => $prefix . '/report',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

$kioskCookie = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_attendance_kiosk_cookies.txt';
@unlink($kioskCookie);
phase4_linkGet($base, 'Attendance capture kiosk', $prefix . '/captured', $kioskCookie, [200]);

echo "\n=== Attendance screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
