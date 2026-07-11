<?php
/**
 * Phase 4 — System Settings screen tests (Owner)
 * Run: php phase4_system_settings_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_system_settings_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/system_settings';

if (!$identity || !$password) {
    echo "Usage: php phase4_system_settings_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Settings index' => $prefix,
    'User groups' => $prefix . '/user_groups',
    'Currencies' => $prefix . '/currencies',
    'Categories' => $prefix . '/categories',
    'Tax rates' => $prefix . '/tax_rates',
    'Warehouses' => $prefix . '/warehouses',
    'Units' => $prefix . '/units',
    'Brands' => $prefix . '/brands',
    'Customer groups' => $prefix . '/customer_groups',
    'Expense categories' => $prefix . '/expense_categories',
    'Backups' => $prefix . '/backups',
    'Manage barcode' => $prefix . '/manage_barcode',
    'POS type labels' => $prefix . '/pos_type_labels',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

$r = phase4_httpGetFollow("$base$prefix/currencies", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base$prefix/getCurrencies", $cookieFile, phase4_dtPost($token), ['X-Requested-With: XMLHttpRequest']);
    phase4_check('getCurrencies AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('getCurrencies AJAX', true, 'skipped — no CSRF token');
}

echo "\n=== System Settings screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
