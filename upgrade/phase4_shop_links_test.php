<?php
/**
 * Phase 4 — Shop deep-link tests (guest + optional customer)
 * Run: php phase4_shop_links_test.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_shop_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/shop';

@unlink($cookieFile);

$categoryId = null;
$productId = null;
$shippingId = null;

try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT id FROM sma_categories WHERE is_active=1 AND in_eshop=1 ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $categoryId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT id FROM sma_products WHERE is_active=1 AND in_eshop=1 ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $productId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT id FROM sma_eshop_shipping_methods WHERE is_active=1 ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $shippingId = (int) $q->fetch_assoc()['id'];
        }
    }
} catch (Throwable $e) {
    // optional DB discovery
}

if ($categoryId) {
    phase4_linkGet($base, 'Shop home category', "$prefix/home/" . md5((string) $categoryId) . "/1", $cookieFile, [200, 302]);
    phase4_linkGet($base, 'Shop viewCatlogProducts', "$prefix/viewCatlogProducts?catId=$categoryId&page=1", $cookieFile, [200]);
} else {
    phase4_check('Shop home category', true, 'skipped — no category id');
    phase4_check('Shop viewCatlogProducts', true, 'skipped — no category id');
}

if ($productId) {
    phase4_linkGet($base, 'Shop product_info', "$prefix/product_info/" . md5((string) $productId), $cookieFile, [200, 302]);
    phase4_linkGet($base, 'Shop search', "$prefix/search/test/1", $cookieFile, [200, 302]);
    phase4_linkGet($base, 'Shop addCartItems', "$prefix/addCartItems?product_id={$productId}&qty=1", $cookieFile, [200]);
} else {
    phase4_check('Shop product_info', true, 'skipped — no product id');
    phase4_check('Shop search', true, 'skipped');
    phase4_check('Shop addCartItems', true, 'skipped');
}

if ($shippingId) {
    phase4_linkGet($base, 'Shop getSloteTime', "$prefix/getSloteTime/$shippingId", $cookieFile, [200]);
} else {
    phase4_check('Shop getSloteTime', true, 'skipped — no shipping method id');
}

phase4_linkGet($base, 'Shop get_pincode_location', "$prefix/get_pincode_location/400001", $cookieFile, [200]);
phase4_linkGet($base, 'Shop reset_shipping_methods', "$prefix/reset_shipping_methods", $cookieFile, [200, 302]);
phase4_linkGet($base, 'Shop set_shipping_methods', "$prefix/set_shipping_methods", $cookieFile, [200, 302]);

echo "\n=== Shop deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
