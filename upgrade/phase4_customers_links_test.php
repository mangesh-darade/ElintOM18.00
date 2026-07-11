<?php
/**
 * Phase 4 — Customers deep-link tests (logged-in, P1 routes)
 * Run: php phase4_customers_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_customers_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_customers_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/customers", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/customers/getCustomers", $cookieFile, phase4_dtPost($token));
$customerId = phase4_firstDtId($r['body']);
phase4_check('getCustomers AJAX', $r['code'] === 200 && $customerId !== null, 'customer_id=' . ($customerId ?? 'none'));

if ($customerId) {
    $links = [
        'view' => "/customers/view/$customerId",
        'edit' => "/customers/edit/$customerId",
        'deposits' => "/customers/deposits/$customerId",
        'depositsHistory' => "/customers/depositsHistory/$customerId",
        'add_deposit' => "/customers/add_deposit/$customerId",
        'addresses' => "/customers/addresses/$customerId",
        'add_address' => "/customers/add_address/$customerId",
        'users' => "/customers/users/$customerId",
        'add_user' => "/customers/add_user/$customerId",
        'getCustomer JSON' => "/customers/getCustomer/$customerId",
        'get_award_points' => "/customers/get_award_points/$customerId",
    ];
    foreach ($links as $label => $path) {
        phase4_linkGet($base, "Customer $label", $path, $cookieFile);
    }

    $r = phase4_httpRequest("$base/customers/get_deposits/$customerId", $cookieFile, phase4_dtPost($token));
    $depositId = phase4_firstDtId($r['body']);
    if ($depositId) {
        phase4_linkGet($base, 'deposit_note', "/customers/deposit_note/$depositId", $cookieFile);
        phase4_linkGet($base, 'edit_deposit', "/customers/edit_deposit/$depositId", $cookieFile);
    } else {
        phase4_check('Deposit deep links', true, 'no deposit rows (skip)');
    }

    $r = phase4_httpGetFollow("$base/customers/addresses/$customerId", $cookieFile);
    if (preg_match('/customers\/edit_address\/(\d+)/', $r['body'], $m)) {
        phase4_linkGet($base, 'edit_address', '/customers/edit_address/' . $m[1], $cookieFile);
    }
} else {
    phase4_check('Customer deep links', false, 'no customer id');
}

phase4_linkGet($base, 'add', '/customers/add', $cookieFile);
phase4_linkGet($base, 'add_quick', '/customers/add_quick', $cookieFile);
phase4_linkGet($base, 'import_csv', '/customers/import_csv', $cookieFile);
phase4_linkGet($base, 'getBulkDeposit', '/customers/getBulkDeposit', $cookieFile);

echo "\n=== Customers deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
