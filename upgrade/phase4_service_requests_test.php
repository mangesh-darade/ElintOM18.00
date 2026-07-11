<?php
/**
 * Phase 4 — Service Requests screen tests (Owner)
 * Run: php phase4_service_requests_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_service_requests_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_service_requests_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Service site report (friendly)' => '/service-site-report',
    'Service site report (controller)' => '/service_requests/service_site_report',
    'Service site report equipment tab' => '/service-site-report/equipment',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

$r = phase4_httpGetFollow("$base/service_requests/service_site_report_mobile/work", $cookieFile);
phase4_check('Mobile redirect compat', in_array($r['code'], [200, 302], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

echo "\n=== Service Requests screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
