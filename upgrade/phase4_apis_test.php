<?php
/**
 * Phase 4 — APIs (JSON) smoke tests
 * Run: php phase4_apis_test.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_apis_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$jsonHdr = ['Content-Type: application/x-www-form-urlencoded'];

@unlink($cookieFile);

$apiKey = null;
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT api_privatekey FROM sma_settings LIMIT 1");
        if ($q && $q->num_rows) {
            $apiKey = $q->fetch_assoc()['api_privatekey'] ?? null;
        }
    }
} catch (Throwable $e) {
    // optional DB discovery
}

function phase4_apiPost($base, $label, $path, $post, $cookieFile, $jsonHdr)
{
    global $phase4_fail, $phase4_pass;
    $r = phase4_httpRequest("$base$path", $cookieFile, $post, $jsonHdr);
    $ok = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
    phase4_check($label, $ok, 'HTTP ' . $r['code']);
    return $r;
}

function phase4_apiGetKey($base, $label, $path, $apiKey, $cookieFile)
{
    global $phase4_fail, $phase4_pass;
    $hdr = ['X-API-KEY: ' . $apiKey];
    $r = phase4_httpRequest("$base$path", $cookieFile, null, $hdr);
    $ok = in_array($r['code'], [200, 401, 403], true) && !phase4_hasPhpIssue($r['body']);
    phase4_check($label, $ok, 'HTTP ' . $r['code']);
    return $r;
}

if ($apiKey) {
    phase4_apiPost($base, 'Api3 eshop getsettings', '/api3/eshop', http_build_query([
        'privatekey' => $apiKey,
        'action' => 'getsettings',
    ]), $cookieFile, $jsonHdr);

    phase4_apiPost($base, 'Api3 offline getsettings', '/api3/offline', http_build_query([
        'privatekey' => $apiKey,
        'action' => 'getsettings',
    ]), $cookieFile, $jsonHdr);

    phase4_apiPost($base, 'Api3 offline getallcategories', '/api3/offline', http_build_query([
        'privatekey' => $apiKey,
        'action' => 'getallcategories',
    ]), $cookieFile, $jsonHdr);

    phase4_apiPost($base, 'Api4 getposdata', '/api4/getposdata', http_build_query([
        'privatekey' => $apiKey,
    ]), $cookieFile, $jsonHdr);

    phase4_apiPost($base, 'Api4 transfer', '/api4/transfer', http_build_query([
        'privatekey' => $apiKey,
    ]), $cookieFile, $jsonHdr);

    phase4_apiGetKey($base, 'Restapi5 store_settings', '/restapi5/store_settings', $apiKey, $cookieFile);
    phase4_apiGetKey($base, 'Restapi5 categories', '/restapi5/categories', $apiKey, $cookieFile);
    phase4_apiGetKey($base, 'Restapi5 payment_methods', '/restapi5/payment_methods', $apiKey, $cookieFile);
} else {
    phase4_check('API key discovery', true, 'skipped — no api_privatekey in DB');
}

$r = phase4_httpRequest(
    "$base/webhook/cheerio_add_customer",
    $cookieFile,
    json_encode(['name' => 'PHPUpgrade Test', 'mobile' => '919999999999']),
    ['Content-Type: application/json']
);
phase4_check('Webhook cheerio_add_customer', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

$r = phase4_httpRequest(
    "$base/web_service/testing",
    $cookieFile,
    '{}',
    ['Content-Type: application/json']
);
phase4_check('Web_service testing', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

$r = phase4_httpGetFollow("$base/whatsapp/send_whatsapp_message?code=test&phone=9999999999", $cookieFile);
phase4_check('Whatsapp send_whatsapp_message', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

echo "\n=== APIs smoke | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
