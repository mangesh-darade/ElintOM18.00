<?php
/**
 * Phase 4 — Webshop screen tests (public storefront)
 * Run: php phase4_webshop_test.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_webshop_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$ajaxHdr = ['X-Requested-With: XMLHttpRequest'];
$prefix = '/webshop';

@unlink($cookieFile);

$productHash = null;
$categoryId = null;
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT id FROM sma_products WHERE in_eshop=1 AND is_active=1 ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $productHash = md5((string) $q->fetch_assoc()['id']);
        }
        $q = $m->query("SELECT id FROM sma_categories WHERE is_active=1 AND in_eshop=1 ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $categoryId = (int) $q->fetch_assoc()['id'];
        }
    }
} catch (Throwable $e) {
    // DB optional for ID discovery
}

foreach ([
    'Home' => $prefix,
    'Products' => $prefix . '/products',
    'Cart' => $prefix . '/cart',
    'Compare' => $prefix . '/compare',
    'Login' => $prefix . '/login',
    'Register' => $prefix . '/register',
    'Forgot password' => $prefix . '/forgot_password',
    'My views history' => $prefix . '/my_views_history',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200, 302]);
}

if ($productHash) {
    phase4_linkGet($base, 'Product details', "$prefix/product_details/$productHash", $cookieFile, [200, 302]);
} else {
    phase4_check('Product details', true, 'skipped — no eshop product');
}

if ($categoryId) {
    phase4_linkGet($base, 'Category products', "$prefix/category_products/$categoryId", $cookieFile, [200, 302]);
} else {
    phase4_check('Category products', true, 'skipped — no eshop category');
}

phase4_linkGet($base, 'Search products', $prefix . '/search_products?search=test', $cookieFile, [200, 302]);

$r = phase4_httpRequest("$base$prefix/webshop_request", $cookieFile, http_build_query(['action' => 'load_header_cart']), $ajaxHdr);
phase4_check('load_header_cart AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

echo "\n=== Webshop screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
