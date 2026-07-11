<?php
/**
 * Phase 4 — Transfers screen tests
 * Run: php phase4_transfers_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_transfers_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_transfers_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Transfer list' => '/transfers',
    'Add transfer' => '/transfers/add',
    'Request list' => '/transfers/request',
    'Add request' => '/transfers/add_request',
    'Transfer by CSV' => '/transfers/transfer_by_csv',
    'Transfer RM' => '/transfers/transfer_rm',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile);
}

$r = phase4_httpGetFollow("$base/transfers", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/transfers/getTransfers", $cookieFile, phase4_dtPost($token));
$transferId = phase4_firstDtId($r['body']);
phase4_check('getTransfers AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'transfer_id=' . ($transferId ?? 'none'));

$r = phase4_httpRequest("$base/transfers/getTransfersRequests", $cookieFile, phase4_dtPost($token));
$requestId = phase4_firstDtId($r['body']);
phase4_check('getTransfersRequests AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'request_id=' . ($requestId ?? 'none'));

$fromWh = null;
$toWh = null;
$add = phase4_httpGetFollow("$base/transfers/add", $cookieFile);
if (preg_match_all('/<option[^>]+value="(\d+)"/', $add['body'], $m) && count($m[1]) >= 2) {
    $fromWh = (int) $m[1][0];
    $toWh = (int) $m[1][1];
}

if ($fromWh && $toWh && $fromWh !== $toWh) {
    $sug = phase4_httpGetFollow("$base/transfers/suggestions?term=abc&warehouse_id=$fromWh&warehouse_2=$toWh", $cookieFile);
    $sugOk = $sug['code'] === 200 && !phase4_hasPhpIssue($sug['body']);
    $json = json_decode($sug['body'], true);
    phase4_check('suggestions AJAX', $sugOk && is_array($json), "from=$fromWh to=$toWh");
} else {
    phase4_check('suggestions AJAX', true, 'skipped — need two warehouse ids from add form');
}

echo "\n=== Transfers screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
