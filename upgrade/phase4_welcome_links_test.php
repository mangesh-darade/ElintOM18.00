<?php
/**
 * Phase 4 — Welcome deep-link tests (logged-in)
 * Run: php phase4_welcome_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_welcome_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_welcome_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

phase4_linkGet($base, 'Dashboard', '/welcome', $cookieFile);
phase4_linkGet($base, 'Promotions', '/welcome/promotions', $cookieFile);
phase4_linkGet($base, 'Language english', '/welcome/language/english', $cookieFile, [200, 302]);

echo "\n=== Welcome deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
