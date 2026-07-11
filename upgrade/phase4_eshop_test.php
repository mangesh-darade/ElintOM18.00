<?php
/**
 * Phase 4 — Eshop screen tests (Owner admin + public API)
 * Run: php phase4_eshop_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_eshop_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_eshop_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Eshop admin pages' => '/eshop_admin/pages',
    'Eshop admin settings' => '/eshop_admin/settings',
    'Eshop admin shipping' => '/eshop_admin/shipping_methods',
    'Eshop admin products' => '/eshop_admin/manage_products',
    'Eshop admin pincodes' => '/eshop_admin/pincodes',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

$apiCookie = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_eshop_api_cookies.txt';
@unlink($apiCookie);
foreach ([
    'API featured_product' => '/eshop_api/featured_product',
    'API get_categories' => '/eshop_api/get_categories',
    'API get_brands_list' => '/eshop_api/get_brands_list',
    'API popular_categories' => '/eshop_api/popular_categories',
    'API populerproduct' => '/eshop_api/populerproduct',
    'API storeInfo' => '/eshop_api/storeInfo',
    'API shipping_methods' => '/eshop_api/shipping_methods',
    'API getTaxMethods' => '/eshop_api/getTaxMethods',
    'API getTaxAttribs' => '/eshop_api/getTaxAttribs',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $apiCookie, [200]);
}

echo "\n=== Eshop screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
