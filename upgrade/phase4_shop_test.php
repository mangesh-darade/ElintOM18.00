<?php
/**
 * Phase 4 — Shop screen tests (guest storefront)
 * Run: php phase4_shop_test.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_shop_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/shop';

@unlink($cookieFile);

foreach ([
    'Shop index' => $prefix . '/index',
    'Shop home' => $prefix . '/home',
    'Shop cart' => $prefix . '/cart',
    'Shop login' => $prefix . '/login',
    'Shop signup' => $prefix . '/signup',
    'Shop about_us' => $prefix . '/about_us',
    'Shop contact' => $prefix . '/contact',
    'Shop faq' => $prefix . '/faq',
    'Shop privacy_policy' => $prefix . '/privacy_policy',
    'Shop terms_conditions' => $prefix . '/terms_conditions',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200, 302]);
}

echo "\n=== Shop screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
