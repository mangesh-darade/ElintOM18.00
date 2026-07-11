<?php
/**
 * Phase 4 — Sales deep-link tests (logged-in, P1 routes)
 * Run: php phase4_sales_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_sales_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_sales_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/sales", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/sales/getSales", $cookieFile, phase4_dtPost($token));
$saleId = phase4_firstDtId($r['body']);
phase4_check('getSales AJAX', $r['code'] === 200 && $saleId !== null, 'sale_id=' . ($saleId ?? 'none'));

if ($saleId) {
    $links = [
        'view' => "/sales/view/$saleId",
        'modal_view' => "/sales/modal_view/$saleId",
        'edit' => "/sales/edit/$saleId",
        'pdf' => "/sales/pdf/$saleId",
        'payments' => "/sales/payments/$saleId",
        'add_payment' => "/sales/add_payment/$saleId",
        'return_sale' => "/sales/return_sale/$saleId",
        'email' => "/sales/email/$saleId",
        'add_delivery' => "/sales/add_delivery/$saleId",
        'duplicate add' => "/sales/add?sale_id=$saleId",
    ];
    foreach ($links as $label => $path) {
        $codes = ($label === 'pdf') ? [200, 302] : [200];
        phase4_linkGet($base, "Sale $label", $path, $cookieFile, $codes);
    }
} else {
    phase4_check('Sale deep links', false, 'no sale id from getSales');
}

echo "\n=== Sales deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
