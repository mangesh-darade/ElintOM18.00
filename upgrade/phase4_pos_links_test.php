<?php
/**
 * Phase 4 — POS deep-link tests (logged-in)
 * Run: php phase4_pos_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_pos_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_pos_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/pos/sales", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/pos/getSales", $cookieFile, phase4_dtPost($token));
$saleId = phase4_firstDtId($r['body']);
phase4_check('pos/getSales AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'sale_id=' . ($saleId ?? 'none'));

if ($saleId) {
    phase4_linkGet($base, 'POS view', "/pos/view/$saleId", $cookieFile);
    phase4_linkGet($base, 'Sales modal from POS', "/sales/modal_view/$saleId", $cookieFile);
    phase4_linkGet($base, 'Sales view from POS', "/sales/view/$saleId", $cookieFile);
    phase4_linkGet($base, 'Sales payments', "/sales/payments/$saleId", $cookieFile);
    phase4_linkGet($base, 'Add payment', "/sales/add_payment/$saleId", $cookieFile);
} else {
    phase4_check('POS sale deep links', false, 'no sale id from pos/getSales');
}

$r = phase4_httpGetFollow("$base/pos/opened_bills", $cookieFile);
$suspendId = null;
if (preg_match('/id="(\d+)"[^>]*class="[^"]*sus_sale/i', $r['body'], $m) ||
    preg_match('/class="[^"]*sus_sale[^"]*"[^>]*id="(\d+)"/i', $r['body'], $m) ||
    preg_match('/pos\/index\/(\d+)/', $r['body'], $m)) {
    $suspendId = (int) $m[1];
}
phase4_check('Opened bills screen', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code'] . ' suspend=' . ($suspendId ?? 'none'));

if ($suspendId) {
    phase4_linkGet($base, 'Resume suspend', "/pos/index/$suspendId", $cookieFile, [200, 302]);
}

phase4_linkGet($base, 'Register details', '/pos/register_details', $cookieFile);
phase4_linkGet($base, 'Update register', '/pos/update_register', $cookieFile);

echo "\n=== POS deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
