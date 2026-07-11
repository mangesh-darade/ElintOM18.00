<?php
/**
 * Phase 4 — Restaurant screen tests
 * Run: php phase4_restaurant_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_restaurant_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$ajaxHdr = ['X-Requested-With: XMLHttpRequest'];

if (!$identity || !$password) {
    echo "Usage: php phase4_restaurant_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$prefix = '/Restaurant_Order_Taking';
foreach ([
    'Restaurant home' => $prefix,
    'Tables' => $prefix . '/tables/1',
    'Orders list' => $prefix . '/orders',
    'Orders by table' => $prefix . '/orders/1',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

$r = phase4_httpGetFollow("$base$prefix/tables/1", $cookieFile);
$token = phase4_extractCsrf($r['body']);

$r = phase4_httpGetFollow("$base$prefix/table_statuses", $cookieFile);
$tsOk = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
$tsJson = json_decode($r['body'], true);
phase4_check('table_statuses AJAX', $tsOk && is_array($tsJson), 'HTTP ' . $r['code']);

$r = phase4_httpRequest("$base$prefix/load_tables", $cookieFile, http_build_query(['section_id' => 1, 'token' => $token ?? '']), $ajaxHdr);
$ltOk = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
$ltJson = json_decode($r['body'], true);
phase4_check('load_tables AJAX', $ltOk && is_array($ltJson), 'HTTP ' . $r['code']);

$r = phase4_httpRequest("$base$prefix/load_subsections", $cookieFile, http_build_query(['section_id' => 1, 'token' => $token ?? '']), $ajaxHdr);
$lsOk = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
phase4_check('load_subsections AJAX', $lsOk, 'HTTP ' . $r['code']);

$orderId = null;
$m = @new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
if ($m && !$m->connect_error) {
    $q = $m->query("SELECT id FROM sma_res_orders ORDER BY id DESC LIMIT 1");
    if ($q && $q->num_rows) {
        $orderId = (int) $q->fetch_assoc()['id'];
    }
}

if ($orderId) {
    phase4_linkGet($base, 'Order screen', "$prefix/order_screen/$orderId", $cookieFile, [200], $identity, $password);
    $r = phase4_httpRequest("$base$prefix/get_menu_items", $cookieFile, http_build_query(['search' => 'a', 'token' => $token ?? '']), $ajaxHdr);
    $menuOk = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
    phase4_check('get_menu_items AJAX', $menuOk, 'order_id=' . $orderId);
} else {
    phase4_check('Order screen', true, 'skipped — no order id (run restaurant_bootstrap_db.php)');
    phase4_check('get_menu_items AJAX', true, 'skipped — no order id');
}

echo "\n=== Restaurant screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
