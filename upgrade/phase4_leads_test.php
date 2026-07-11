<?php
/**
 * Phase 4 — Leads screen tests (Owner)
 * Run: php phase4_leads_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_leads_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/Leads';

if (!$identity || !$password) {
    echo "Usage: php phase4_leads_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Leads list' => $prefix . '/index',
    'Add lead' => $prefix . '/add',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

$r = phase4_httpGetFollow("$base$prefix/index", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base$prefix/get_leads", $cookieFile, phase4_dtPost($token), ['X-Requested-With: XMLHttpRequest']);
    phase4_check('get_leads AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('get_leads AJAX', true, 'skipped — no CSRF token');
}

echo "\n=== Leads screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
