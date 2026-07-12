<?php
/**
 * Phase 4 — Other Modules screen tests (Owner)
 * Run: php phase4_other_modules_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_other_modules_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$ajaxHdr = ['X-Requested-With: XMLHttpRequest'];

if (!$identity || !$password) {
    echo "Usage: php phase4_other_modules_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Employees' => '/employees',
    'GSTR' => '/gstr',
    'Orders' => '/orders',
    'Orders eshop' => '/orders/eshop_order',
    'Orders items' => '/orders/order_items',
    'Orders items stocks' => '/orders/order_items_stocks',
    'File manager' => '/file_manager',
    'SMS dashboard' => '/smsdashboard',
    'Send SMS/email' => '/sendsmsemail',
    'Send SMS customer notify' => '/sendsmsemail/coustmer_notification',
    'Notifications' => '/notifications',
    'Sync' => '/sync',
    'Offline sales' => '/offline/sales',
    'Help statelist' => '/help/statelist',
    'Check stock' => '/checkStock',
    'Calendar' => '/calendar',
    'Vendor rates' => '/vendor_rates',
    'RM calculator' => '/rm_calculator',
    'Recipies' => '/recipies',
    'Products mobile' => '/Products_Mobile',
    'Purchases mobile' => '/Purchases_Mobile',
    'Sales mobile lists' => '/sales_mobile/all_sale_lists',
    'Production mobile procurement' => '/Production_Unit_Mobile/procurementOrders',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

$r = phase4_httpGetFollow("$base/employees", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/employees/get_employees", $cookieFile, phase4_dtPost($token), $ajaxHdr);
    phase4_check('Employees get_employees AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('Employees get_employees AJAX', true, 'skipped — no CSRF token');
}

$r = phase4_httpGetFollow("$base/orders", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/orders/getOrders", $cookieFile, phase4_dtPost($token), $ajaxHdr);
    phase4_check('Orders getOrders AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('Orders getOrders AJAX', true, 'skipped — no CSRF token');
}

$r = phase4_httpGetFollow("$base/notifications", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/notifications/getNotifications", $cookieFile, phase4_dtPost($token), $ajaxHdr);
    phase4_check('Notifications getNotifications AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('Notifications getNotifications AJAX', true, 'skipped — no CSRF token');
}

$r = phase4_httpGetFollow("$base/file_manager", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/file_manager/getFiles", $cookieFile, phase4_dtPost($token), $ajaxHdr);
    phase4_check('File manager getFiles AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('File manager getFiles AJAX', true, 'skipped — no CSRF token');
}

$r = phase4_httpGetFollow("$base/calendar/get_events?start=2026-07-01&end=2026-07-31", $cookieFile);
phase4_check('Calendar get_events', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

$r = phase4_httpGetFollow("$base/offline/sales", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/offline/getSales", $cookieFile, phase4_dtPost($token), $ajaxHdr);
    phase4_check('Offline getSales AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('Offline getSales AJAX', true, 'skipped — no CSRF token');
}

echo "\n=== Other Modules screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
