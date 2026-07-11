<?php
/**
 * Phase 4 — Products deep-link tests (logged-in, P1 routes)
 * Run: php phase4_products_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_products_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_products_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/products", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/products/getProducts", $cookieFile, phase4_dtPost($token));
$productId = phase4_firstDtId($r['body']);
phase4_check('getProducts AJAX', $r['code'] === 200 && $productId !== null, 'product_id=' . ($productId ?? 'none'));

if ($productId) {
    $productLinks = [
        'view' => "/products/view/$productId",
        'edit' => "/products/edit/$productId",
        'modal_view' => "/products/modal_view/$productId",
        'print_barcodes' => "/products/print_barcodes/$productId",
        'pdf' => "/products/pdf/$productId",
        'duplicate add' => "/products/add/$productId",
    ];
    foreach ($productLinks as $label => $path) {
        $codes = ($label === 'pdf') ? [200, 302] : [200];
        phase4_linkGet($base, "Product $label", $path, $cookieFile, $codes);
    }
} else {
    phase4_check('Product deep links', false, 'no product id');
}

// Known regression target (Milk product 238)
phase4_linkGet($base, 'print_barcodes/238', '/products/print_barcodes/238', $cookieFile);

$r = phase4_httpGetFollow("$base/products/quantity_adjustments", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/products/getadjustments", $cookieFile, phase4_dtPost($token));
$adjId = phase4_firstDtId($r['body']);
phase4_check('getadjustments AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'adj_id=' . ($adjId ?? 'none'));

if ($adjId) {
    phase4_linkGet($base, 'view_adjustment', "/products/view_adjustment/$adjId", $cookieFile);
    phase4_linkGet($base, 'edit_adjustment', "/products/edit_adjustment/$adjId", $cookieFile);
}

$r = phase4_httpGetFollow("$base/products/stock_counts", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/products/getCounts/file", $cookieFile, phase4_dtPost($token));
$countId = phase4_firstDtId($r['body']);
phase4_check('getCounts/file AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'count_id=' . ($countId ?? 'none'));

if ($countId) {
    phase4_linkGet($base, 'view_count', "/products/view_count/$countId", $cookieFile);
}

phase4_linkGet($base, 'quantity_adjustments', '/products/quantity_adjustments', $cookieFile);
phase4_linkGet($base, 'add_adjustment', '/products/add_adjustment', $cookieFile);
phase4_linkGet($base, 'print_barcodes list', '/products/print_barcodes', $cookieFile);

echo "\n=== Products deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
