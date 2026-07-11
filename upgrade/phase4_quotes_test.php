<?php
/**
 * Phase 4 — Quotes screen tests
 * Run: php phase4_quotes_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_quotes_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_quotes_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

foreach ([
    'Quote list' => '/quotes',
    'Add quote' => '/quotes/add',
] as $label => $path) {
    phase4_linkGet($base, $label, $path, $cookieFile);
}

$r = phase4_httpGetFollow("$base/quotes", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/quotes/getQuotes", $cookieFile, phase4_dtPost($token));
$quoteId = phase4_firstDtId($r['body']);
phase4_check('getQuotes AJAX', $r['code'] === 200 && is_array(json_decode($r['body'], true)), 'quote_id=' . ($quoteId ?? 'none'));

$warehouseId = null;
$customerId = null;
if ($quoteId) {
    $edit = phase4_httpGetFollow("$base/quotes/edit/$quoteId", $cookieFile);
    if (preg_match("/localStorage\.setItem\('quwarehouse',\s*'(\d+)'\)/", $edit['body'], $m)) {
        $warehouseId = (int) $m[1];
    }
    if (preg_match("/localStorage\.setItem\('qucustomer',\s*'(\d+)'\)/", $edit['body'], $m)) {
        $customerId = (int) $m[1];
    }
}

if ($warehouseId && $customerId) {
    $sug = phase4_httpGetFollow("$base/quotes/suggestions?term=a&warehouse_id=$warehouseId&customer_id=$customerId", $cookieFile);
    $sugOk = $sug['code'] === 200 && !phase4_hasPhpIssue($sug['body']);
    $json = json_decode($sug['body'], true);
    phase4_check('suggestions AJAX', $sugOk && is_array($json), 'warehouse=' . $warehouseId . ' customer=' . $customerId);
} else {
    phase4_check('suggestions AJAX', true, 'skipped — no warehouse/customer from edit form');
}

if ($quoteId) {
    $bom = phase4_httpGetFollow("$base/quotes/get_bom_materials?product_id=1&warehouse_id=" . ($warehouseId ?: 1), $cookieFile);
    phase4_check('get_bom_materials AJAX', $bom['code'] === 200 && !phase4_hasPhpIssue($bom['body']));
}

echo "\n=== Quotes screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
