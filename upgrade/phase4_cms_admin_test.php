<?php
/**
 * Phase 4 — CMS Admin Panel screen smoke test (Owner)
 * Run: php phase4_cms_admin_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_cms_admin_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_cms_admin_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $m->query("UPDATE sma_settings SET active_cms_admin_panel=1, active_webshop=1 WHERE setting_id=1");
    }
} catch (Throwable $e) {
}

$screens = [
    'CMS Admin entry' => '/cms_admin',
    'CMS dashboard' => '/cms_admin/dashboard',
    'CMS pages' => '/cms_admin/pages',
    'CMS catalog' => '/cms_admin/catalog',
    'CMS media' => '/cms_admin/media',
    'CMS layout builder' => '/cms_admin/layout_builder',
];

foreach ($screens as $label => $path) {
    $r = phase4_httpGetFollow("$base$path", $cookieFile);
    $ok = $r['code'] === 200
        && !phase4_hasPhpIssue($r['body'])
        && (stripos($r['body'], 'cms-admin') !== false || stripos($r['body'], 'CMS Admin') !== false || stripos($r['body'], 'cms_admin') !== false);
    phase4_check($label, $ok, 'HTTP ' . $r['code']);
}

echo "\n=== CMS Admin screen | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
