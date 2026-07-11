<?php
/**
 * Phase 4 — Customers screen load tests (logged-in)
 * Run: php phase4_customers_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_customers_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_customers_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$screens = [
    'Customer list' => '/customers',
    'Add customer (modal route)' => '/customers/add',
    'Quick add' => '/customers/add_quick',
    'CSV import' => '/customers/import_csv',
    'Bulk deposit' => '/customers/getBulkDeposit',
];

foreach ($screens as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile);
}

$r = phase4_httpGetFollow("$base/customers", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/customers/getCustomers", $cookieFile, phase4_dtPost($token));
$customerId = phase4_firstDtId($r['body']);
phase4_check('getCustomers AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'customer_id=' . ($customerId ?? 'none'));

if ($customerId) {
    $r = phase4_httpGetFollow("$base/customers/getGiftBalance?id=$customerId", $cookieFile);
    $giftOk = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
    phase4_check('getGiftBalance AJAX', $giftOk);

    $r = phase4_httpGetFollow("$base/customers/get_deposits/$customerId", $cookieFile);
    $token2 = phase4_extractCsrf($r['body']) ?? $token;
    $r = phase4_httpRequest("$base/customers/get_deposits/$customerId", $cookieFile, phase4_dtPost($token2));
    phase4_check('get_deposits AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)));

    $sug = phase4_httpGetFollow("$base/customers/suggestions?term=a&limit=5", $cookieFile);
    phase4_check('suggestions AJAX', $sug['code'] === 200 && !phase4_hasPhpIssue($sug['body']));
}

echo "\n=== Customers screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
