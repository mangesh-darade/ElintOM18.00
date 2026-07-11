<?php
/**
 * Phase 4 — Webshop Settings screen tests (Owner)
 * Run: php phase4_webshop_settings_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_webshop_settings_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/webshop_settings';

if (!$identity || !$password) {
    echo "Usage: php phase4_webshop_settings_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Layout index' => $prefix,
    'Homepage sections' => $prefix . '/sections',
    'Sliders' => $prefix . '/sliders',
    'Shipping methods' => $prefix . '/shipping_methods',
    'Manage products' => $prefix . '/manage_products',
    'Custom pages' => $prefix . '/custom_pages',
    'E-shop settings' => $prefix . '/settings',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

echo "\n=== Webshop Settings screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
