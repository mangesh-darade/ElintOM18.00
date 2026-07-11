<?php
/**
 * Phase 4 — Quotes deep-link tests
 * Run: php phase4_quotes_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_quotes_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_quotes_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/quotes", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/quotes/getQuotes", $cookieFile, phase4_dtPost($token));
$quoteId = phase4_firstDtId($r['body']);
phase4_check('getQuotes AJAX', $r['code'] === 200 && $quoteId !== null, 'quote_id=' . ($quoteId ?? 'none'));

if ($quoteId) {
    foreach ([
        'view' => "/quotes/view/$quoteId",
        'edit' => "/quotes/edit/$quoteId",
        'modal_view' => "/quotes/modal_view/$quoteId",
        'email' => "/quotes/email/$quoteId",
        'update_status' => "/quotes/update_status/$quoteId",
    ] as $label => $path) {
        phase4_linkGet($base, "Quote $label", $path, $cookieFile);
    }

    phase4_linkGet($base, 'Quote pdf', "/quotes/pdf/$quoteId", $cookieFile, [200, 302]);

    $details = phase4_httpGetFollow("$base/quotes/get_quote_details/$quoteId", $cookieFile);
    $detailsOk = $details['code'] === 200 && !phase4_hasPhpIssue($details['body']);
    $cust = json_decode($details['body'], true);
    phase4_check('get_quote_details JSON', $detailsOk && is_array($cust), 'quote_id=' . $quoteId);
} else {
    phase4_check('Quote deep links', false, 'no quote id');
}

phase4_linkGet($base, 'quotes add', '/quotes/add', $cookieFile);

echo "\n=== Quotes deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
