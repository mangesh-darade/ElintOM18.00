<?php
/**
 * Phase 4 — Transfers deep-link tests
 * Run: php phase4_transfers_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_transfers_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_transfers_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/transfers", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/transfers/getTransfers", $cookieFile, phase4_dtPost($token));
$transferId = phase4_firstDtId($r['body']);
phase4_check('getTransfers AJAX', $r['code'] === 200 && $transferId !== null, 'transfer_id=' . ($transferId ?? 'none'));

$fromWh = null;
$toWh = null;

if ($transferId) {
    foreach ([
        'view' => "/transfers/view/$transferId",
        'edit' => "/transfers/edit/$transferId",
        'email' => "/transfers/email/$transferId",
        'update_status' => "/transfers/update_status/$transferId",
    ] as $label => $path) {
        phase4_linkGet($base, "Transfer $label", $path, $cookieFile);
    }
    phase4_linkGet($base, 'Transfer pdf', "/transfers/pdf/$transferId", $cookieFile, [200, 302]);

    $edit = phase4_httpGetFollow("$base/transfers/edit/$transferId", $cookieFile);
    if (preg_match('/id="from_warehouse"[^>]*>.*?value="(\d+)"/s', $edit['body'], $m)) {
        $fromWh = (int) $m[1];
    }
    if (preg_match('/id="to_warehouse"[^>]*>.*?value="(\d+)"/s', $edit['body'], $m)) {
        $toWh = (int) $m[1];
    }
    if (!$fromWh && preg_match('/name="from_warehouse"[^>]*value="(\d+)"/', $edit['body'], $m)) {
        $fromWh = (int) $m[1];
    }
    if (!$toWh && preg_match('/name="to_warehouse"[^>]*value="(\d+)"/', $edit['body'], $m)) {
        $toWh = (int) $m[1];
    }
} else {
    phase4_check('Transfer deep links', false, 'no transfer id');
}

$r = phase4_httpRequest("$base/transfers/getTransfersRequests", $cookieFile, phase4_dtPost($token));
$requestId = phase4_firstDtId($r['body']);
phase4_check('getTransfersRequests AJAX', $r['code'] === 200 && $requestId !== null, 'request_id=' . ($requestId ?? 'none'));

if ($requestId) {
    foreach ([
        'view_request' => "/transfers/view_request/$requestId",
        'edit_request' => "/transfers/edit_request/$requestId",
    ] as $label => $path) {
        phase4_linkGet($base, "Request $label", $path, $cookieFile);
    }
} else {
    phase4_check('Request deep links', true, 'no request id (skip)');
}

if ($fromWh && $toWh) {
    $gq = phase4_httpGetFollow("$base/transfers/getQuantity?vartient=1&from_warehouse=$fromWh&to_warehouse=$toWh", $cookieFile);
    phase4_check('getQuantity AJAX', $gq['code'] === 200 && !phase4_hasPhpIssue($gq['body']), "from=$fromWh to=$toWh");

    $gtp = phase4_httpGetFollow("$base/transfers/getTransferProduct/$fromWh/$toWh", $cookieFile);
    phase4_check('getTransferProduct AJAX', $gtp['code'] === 200 && !phase4_hasPhpIssue($gtp['body']), "from=$fromWh to=$toWh");
} else {
    phase4_check('getQuantity AJAX', true, 'skipped — no warehouse ids from edit');
    phase4_check('getTransferProduct AJAX', true, 'skipped — no warehouse ids from edit');
}

echo "\n=== Transfers deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
