<?php
/**
 * Phase 4 — Suppliers & Billers screen tests
 * Run: php phase4_suppliers_billers_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_suppliers_billers_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_suppliers_billers_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Supplier list' => '/suppliers',
    'Add supplier' => '/suppliers/add',
    'CSV import' => '/suppliers/import_csv',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile);
}

$r = phase4_httpGetFollow("$base/suppliers", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/suppliers/getSuppliers", $cookieFile, phase4_dtPost($token));
$supplierId = phase4_firstDtId($r['body']);
phase4_check('getSuppliers AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'supplier_id=' . ($supplierId ?? 'none'));

if ($supplierId) {
    $sug = phase4_httpGetFollow("$base/suppliers/suggestions?term=a&limit=5", $cookieFile);
    phase4_check('supplier suggestions', $sug['code'] === 200 && !phase4_hasPhpIssue($sug['body']));
}

phase4_linkGet($base, 'Biller list', '/billers', $cookieFile);
phase4_linkGet($base, 'Add biller', '/billers/add', $cookieFile);

$r = phase4_httpGetFollow("$base/billers", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/billers/getBillers", $cookieFile, phase4_dtPost($token));
$billerId = phase4_firstDtId($r['body']);
phase4_check('getBillers AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'biller_id=' . ($billerId ?? 'none'));

if ($billerId) {
    $sug = phase4_httpGetFollow("$base/billers/suggestions?term=a&limit=5", $cookieFile);
    phase4_check('biller suggestions', $sug['code'] === 200 && !phase4_hasPhpIssue($sug['body']));
}

echo "\n=== Suppliers & Billers screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
