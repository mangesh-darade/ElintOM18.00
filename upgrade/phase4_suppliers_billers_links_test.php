<?php
/**
 * Phase 4 — Suppliers & Billers deep-link tests
 * CLI:  php phase4_suppliers_billers_links_test.php <identity> <password>
 * Web:  ?identity=Admin&password=Admin%40554
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

phase4_webOutputStart();

$base = 'http://localhost/ElintOM18.00';
list($identity, $password) = phase4_credentials();
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_suppliers_billers_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    phase4_usage('phase4_suppliers_billers_links_test.php');
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/suppliers", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/suppliers/getSuppliers", $cookieFile, phase4_dtPost($token));
$supplierId = phase4_firstDtId($r['body']);
phase4_check('getSuppliers AJAX', $r['code'] === 200 && $supplierId !== null, 'supplier_id=' . ($supplierId ?? 'none'));

if ($supplierId) {
    foreach ([
        'view' => "/suppliers/view/$supplierId",
        'edit' => "/suppliers/edit/$supplierId",
        'users' => "/suppliers/users/$supplierId",
        'add_user' => "/suppliers/add_user/$supplierId",
        'getSupplier' => "/suppliers/getSupplier/$supplierId",
        'getSupplierName' => "/suppliers/getSupplierName/$supplierId",
    ] as $label => $path) {
        phase4_linkGet($base, "Supplier $label", $path, $cookieFile);
    }
} else {
    phase4_check('Supplier deep links', false, 'no supplier id');
}

$r = phase4_httpGetFollow("$base/billers", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/billers/getBillers", $cookieFile, phase4_dtPost($token));
$billerId = phase4_firstDtId($r['body']);
phase4_check('getBillers AJAX', $r['code'] === 200 && $billerId !== null, 'biller_id=' . ($billerId ?? 'none'));

if ($billerId) {
    phase4_linkGet($base, 'Biller edit', "/billers/edit/$billerId", $cookieFile);
    phase4_linkGet($base, 'Biller getBiller', "/billers/getBiller/$billerId", $cookieFile);
} else {
    phase4_check('Biller deep links', false, 'no biller id');
}

phase4_linkGet($base, 'suppliers add', '/suppliers/add', $cookieFile);
phase4_linkGet($base, 'suppliers import', '/suppliers/import_csv', $cookieFile);
phase4_linkGet($base, 'billers add', '/billers/add', $cookieFile);

echo "\n=== Suppliers & Billers deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
