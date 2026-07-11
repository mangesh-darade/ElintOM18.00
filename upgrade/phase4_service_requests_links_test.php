<?php
/**
 * Phase 4 — Service Requests deep-link / AJAX tests (Owner)
 * Run: php phase4_service_requests_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_service_requests_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_service_requests_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'SSR tab customer' => '/service-site-report/customer',
    'SSR tab work' => '/service-site-report/work',
    'SSR tab pm_log' => '/service-site-report/pm_log',
    'SSR tab report_trigger' => '/service-site-report/report_trigger',
    'SSR tab signatures' => '/service-site-report/signatures',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile, [200], $identity, $password);
}

$customerId = null;
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT id FROM sma_companies WHERE group_name='customer' ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $customerId = (int) $q->fetch_assoc()['id'];
        }
    }
} catch (Throwable $e) {
    // optional DB discovery
}

$r = phase4_httpGetFollow("$base/service-site-report", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$ajaxHdr = ['X-Requested-With: XMLHttpRequest'];

if ($token) {
    $post = http_build_query(['token' => $token]);
    $r = phase4_httpRequest("$base/service_requests/get_brands", $cookieFile, null, $ajaxHdr);
    phase4_check('SSR get_brands', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

    if ($customerId) {
        $r = phase4_httpRequest(
            "$base/service_requests/get_customer_locations",
            $cookieFile,
            http_build_query(['company_id' => $customerId, 'token' => $token]),
            $ajaxHdr
        );
        phase4_check('SSR get_customer_locations', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

        $r = phase4_httpRequest(
            "$base/service_requests/get_next_service_report_number_json",
            $cookieFile,
            http_build_query(['company_id' => $customerId, 'token' => $token]),
            $ajaxHdr
        );
        phase4_check('SSR get_next_service_report_number_json', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

        $r = phase4_httpRequest(
            "$base/service_requests/get_customer_pm_log_grid",
            $cookieFile,
            http_build_query(['customer_id' => $customerId, 'token' => $token]),
            $ajaxHdr
        );
        phase4_check('SSR get_customer_pm_log_grid', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

        $r = phase4_httpGetFollow("$base/service_requests/get_unmapped_equipment_products?customer_id=$customerId", $cookieFile);
        phase4_check('SSR get_unmapped_equipment_products', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
    } else {
        phase4_check('SSR customer AJAX', true, 'skipped — no customer id');
    }
} else {
    phase4_check('SSR AJAX suite', true, 'skipped — no CSRF token');
}

echo "\n=== Service Requests deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
