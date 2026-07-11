<?php
/**
 * Phase 4 — Restaurant deep-link tests
 * Run: php phase4_restaurant_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_restaurant_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$ajaxHdr = ['X-Requested-With: XMLHttpRequest'];
$prefix = '/Restaurant_Order_Taking';

if (!$identity || !$password) {
    echo "Usage: php phase4_restaurant_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base$prefix/tables/1", $cookieFile);
$token = phase4_extractCsrf($r['body']);

$orderId = null;
$saleId = null;
$m = @new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
if ($m && !$m->connect_error) {
    $q = $m->query("SELECT id FROM sma_res_orders ORDER BY id DESC LIMIT 1");
    if ($q && $q->num_rows) {
        $orderId = (int) $q->fetch_assoc()['id'];
    }
    $q = $m->query("SELECT id FROM sma_sales ORDER BY id DESC LIMIT 1");
    if ($q && $q->num_rows) {
        $saleId = (int) $q->fetch_assoc()['id'];
    }
}

if ($orderId) {
    foreach ([
        'order_screen' => "$prefix/order_screen/$orderId",
        'orders summary' => "$prefix/orders/1?view=summary",
        'finalize_screen' => "$prefix/finalize_screen/$orderId",
        'kot' => "$prefix/kot/$orderId",
        'order_bill' => "$prefix/order_bill/$orderId",
        'kitchen_view' => "$prefix/kitchen_view/$orderId",
    ] as $label => $path) {
        phase4_linkGet($base, "Restaurant $label", $path, $cookieFile, [200], $identity, $password);
    }

    $r = phase4_httpGetFollow("$base$prefix/get_order_status?table_id=1", $cookieFile);
    $gosJson = json_decode($r['body'], true);
    phase4_check('get_order_status AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']) && is_array($gosJson), 'order_id=' . $orderId);
} else {
    phase4_check('Restaurant deep links', false, 'no order id — run restaurant_bootstrap_db.php');
}

if ($saleId) {
    phase4_linkGet($base, 'Restaurant invoice', "$prefix/invoice/$saleId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('Restaurant invoice', true, 'skipped — no sale id');
}

phase4_linkGet($base, 'Restaurant table route', "$prefix/table/1", $cookieFile, [200, 302], $identity, $password);

echo "\n=== Restaurant deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
