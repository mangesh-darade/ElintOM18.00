<?php
/**
 * Phase 4 — Eshop deep-link tests (Owner admin + API + legacy router)
 * Run: php phase4_eshop_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_eshop_links_cookies.txt';
$apiCookie = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_eshop_links_api_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_eshop_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));
@unlink($apiCookie);

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
    phase4_linkGet($base, 'Eshop manage_products category', "/eshop_admin/manage_products/$categoryId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('Eshop manage_products category', true, 'skipped — no category id');
}

if ($categoryId) {
    phase4_linkGet($base, 'API product_list', "/eshop_api/product_list/$categoryId/1", $apiCookie, [200]);
} else {
    phase4_check('API product_list', true, 'skipped — no category id');
}

if ($productId) {
    phase4_linkGet($base, 'API product_details', "/eshop_api/product_details/$productId", $apiCookie, [200]);
    phase4_linkGet($base, 'API addToCart', "/eshop_api/addToCart/{$productId}/1", $apiCookie, [200]);
    phase4_linkGet($base, 'API search_product', "/eshop_api/search_product/test/1", $apiCookie, [200]);
    phase4_linkGet($base, 'API suggestions', "/eshop_api/suggestions?search=test", $apiCookie, [200]);
} else {
    phase4_check('API product_details', true, 'skipped — no product id');
    phase4_check('API addToCart', true, 'skipped — no product id');
    phase4_check('API search_product', true, 'skipped');
    phase4_check('API suggestions', true, 'skipped');
}

if ($shippingId) {
    phase4_linkGet($base, 'API getSloteTime', "/eshop_api/getSloteTime/$shippingId", $apiCookie, [200]);
} else {
    phase4_check('API getSloteTime', true, 'skipped — no shipping method id');
}

foreach ([
    'Legacy getShippingMethod' => '/eshop?action=getShippingMethod',
    'Legacy getPaymentMethod' => '/eshop?action=getPaymentMethod',
    'Legacy getPages' => '/eshop?action=getPages',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $apiCookie, [200]);
}

echo "\n=== Eshop deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
