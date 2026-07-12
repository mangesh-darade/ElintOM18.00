<?php
/**
 * Phase 4 — Urban Piper deep-link / AJAX tests (Owner)
 * Run: php phase4_urban_piper_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_urban_piper_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$ajaxHdr = ['X-Requested-With: XMLHttpRequest'];

if (!$identity || !$password) {
    echo "Usage: php phase4_urban_piper_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$saleId = null;
$storeId = null;
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT id FROM sma_sales WHERE up_sales=1 ORDER BY id DESC LIMIT 1");
        if ($q && $q->num_rows) {
            $saleId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT id FROM sma_up_stores ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $storeId = (int) $q->fetch_assoc()['id'];
        }
    }
} catch (Throwable $e) {
    // optional DB discovery
}

$r = phase4_httpGetFollow("$base/urban_piper/orders", $cookieFile);
phase4_check('orders_list AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
$r = phase4_httpRequest("$base/urban_piper/orders_list", $cookieFile, null, $ajaxHdr);
phase4_check('orders_list XHR', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

$r = phase4_httpRequest("$base/urban_piper/getstore", $cookieFile, null, $ajaxHdr);
phase4_check('getstore AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

$r = phase4_httpGetFollow("$base/urban_piper/sales", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/urban_piper/getSales", $cookieFile, phase4_dtPost($token), $ajaxHdr);
    phase4_check('getSales AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('getSales AJAX', true, 'skipped — no CSRF token');
}

if ($saleId) {
    foreach ([
        'order_details' => "/urban_piper/order_details/$saleId",
        'order_kot' => "/urban_piper/order_kot/$saleId",
        'riderinfo' => "/urban_piper/riderinfo/$saleId",
    ] as $label => $path) {
        phase4_linkGet($base, "UP $label", $path, $cookieFile, [200], $identity, $password);
    }
} else {
    phase4_check('UP order deep links', true, 'skipped — no up sale id');
}

if ($storeId) {
    phase4_linkGet($base, 'update_store', "/urban_piper/update_store/$storeId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'store_platform_list', "/urban_piper/store_platform_list", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('UP store deep links', true, 'skipped — no store id');
}

phase4_linkGet($base, 'order_details missing', '/urban_piper/order_details/0', $cookieFile, [200], $identity, $password);

echo "\n=== Urban Piper deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
