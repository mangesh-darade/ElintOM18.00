<?php
/**
 * Phase 4 — Purchases screen load tests (logged-in)
 * Run: php phase4_purchases_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_purchases_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_purchases_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$screens = [
    'Purchase list' => '/purchases',
    'Add purchase' => '/purchases/add',
    'CSV import' => '/purchases/purchase_by_csv',
    'Expenses' => '/purchases/expenses',
    'PO notification' => '/purchases/purchase_notification',
    'Add expense' => '/purchases/add_expense',
];

foreach ($screens as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile);
}

$r = phase4_httpGetFollow("$base/purchases", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/purchases/getPurchases", $cookieFile, phase4_dtPost($token));
$purchaseId = phase4_firstDtId($r['body']);
phase4_check('getPurchases AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'purchase_id=' . ($purchaseId ?? 'none'));

$r = phase4_httpGetFollow("$base/purchases/add", $cookieFile);
$supplierId = null;
if (preg_match('/name="supplier"[^>]*>.*?<option[^>]*value="(\d+)"/s', $r['body'], $m)) {
    $supplierId = $m[1];
}
if ($supplierId) {
    $sug = phase4_httpGetFollow("$base/purchases/suggestions?term=test&supplier_id=$supplierId&quantity=1", $cookieFile);
    $sugOk = $sug['code'] === 200 && !phase4_hasPhpIssue($sug['body']);
    phase4_check('suggestions AJAX', $sugOk, 'HTTP ' . $sug['code'] . ' len=' . strlen($sug['body']));
} else {
    phase4_check('suggestions AJAX', false, 'no supplier id on add form');
}

$r = phase4_httpGetFollow("$base/purchases/expenses", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/purchases/getExpenses", $cookieFile, phase4_dtPost($token));
phase4_check('getExpenses AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'HTTP ' . $r['code']);

if ($purchaseId) {
    foreach (['view' => "/purchases/view/$purchaseId", 'edit' => "/purchases/edit/$purchaseId", 'modal' => "/purchases/modal_view/$purchaseId"] as $label => $path) {
        phase4_linkGet($base, "Purchase $label", $path, $cookieFile);
    }
}

echo "\n=== Purchases screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
