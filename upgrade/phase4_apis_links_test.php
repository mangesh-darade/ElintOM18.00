<?php
/**
 * Phase 4 — APIs deep-link / endpoint sweep
 * Run: php phase4_apis_links_test.php [identity] [password]
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_apis_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$jsonHdr = ['Content-Type: application/x-www-form-urlencoded'];

@unlink($cookieFile);

$apiKey = null;
$saleId = null;
$productCode = null;
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT api_privatekey FROM sma_settings LIMIT 1");
        if ($q && $q->num_rows) {
            $apiKey = $q->fetch_assoc()['api_privatekey'] ?? null;
        }
        $q = $m->query("SELECT id FROM sma_sales ORDER BY id DESC LIMIT 1");
        if ($q && $q->num_rows) {
            $saleId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT code FROM sma_products WHERE is_active=1 ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $productCode = $q->fetch_assoc()['code'];
        }
    }
} catch (Throwable $e) {
    // optional DB discovery
}

if ($identity && $password) {
    phase4_check('Login (ApiOwner CSRF)', phase4_login($base, $identity, $password, $cookieFile));
    $r = phase4_httpGetFollow("$base/welcome", $cookieFile);
    $csrf = phase4_extractCsrf($r['body']);
} else {
    phase4_check('Login (ApiOwner CSRF)', true, 'skipped — no credentials');
    $csrf = null;
}

if ($apiKey) {
    $r = phase4_httpRequest("$base/api3/super_admin", $cookieFile, http_build_query([
        'privatekey' => $apiKey,
        'action' => 'SaleList',
        'draw' => 1,
        'start' => 0,
        'length' => 10,
    ]), $jsonHdr);
    phase4_check('Api3 super_admin SaleList', in_array($r['code'], [200, 403], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

    if ($saleId) {
        $r = phase4_httpRequest("$base/api3/super_admin", $cookieFile, http_build_query([
            'privatekey' => $apiKey,
            'action' => 'SaleDetails',
            'id' => $saleId,
        ]), $jsonHdr);
        phase4_check('Api3 super_admin SaleDetails', in_array($r['code'], [200, 403], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

        $r = phase4_httpRequest("$base/api3/super_admin", $cookieFile, http_build_query([
            'privatekey' => $apiKey,
            'action' => 'SaleDetails',
            'id' => 0,
        ]), $jsonHdr);
        phase4_check('Api3 SaleDetails missing id', in_array($r['code'], [200, 403], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
    } else {
        phase4_check('Api3 SaleDetails deep', true, 'skipped — no sale id');
    }

    $r = phase4_httpRequest("$base/api4/transactions", $cookieFile, http_build_query([
        'privatekey' => $apiKey,
        'warehouseCode' => '1',
        'startdate' => date('Y-m-d', strtotime('-7 days')),
        'enddate' => date('Y-m-d'),
    ]), $jsonHdr);
    phase4_check('Api4 transactions', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

    if ($saleId) {
        $r = phase4_httpRequest("$base/api4/getpurchases", $cookieFile, http_build_query([
            'privatekey' => $apiKey,
            'salesId' => $saleId,
        ]), $jsonHdr);
        phase4_check('Api4 getpurchases', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
    } else {
        phase4_check('Api4 getpurchases', true, 'skipped — no sale id');
    }

    $ownerPost = [
        'privatekey' => $apiKey,
        'type' => 'sales',
        'from_date' => date('Y-m-d', strtotime('-7 days')),
        'to_date' => date('Y-m-d'),
    ];
    if ($csrf) {
        $ownerPost['token'] = $csrf;
    }
    $r = phase4_httpRequest("$base/apiowner/index", $cookieFile, http_build_query($ownerPost), $jsonHdr);
    phase4_check('ApiOwner index sales', in_array($r['code'], [200, 403], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

    $ownerPost['type'] = 'warehouse_list';
    $r = phase4_httpRequest("$base/apiowner/index", $cookieFile, http_build_query($ownerPost), $jsonHdr);
    phase4_check('ApiOwner index warehouse_list', in_array($r['code'], [200, 403], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

    if ($productCode) {
        $ownerPost['type'] = 'livestock';
        $ownerPost['product_code'] = $productCode;
        $r = phase4_httpRequest("$base/apiowner/index", $cookieFile, http_build_query($ownerPost), $jsonHdr);
        phase4_check('ApiOwner index livestock', in_array($r['code'], [200, 403], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
    } else {
        phase4_check('ApiOwner livestock', true, 'skipped — no product code');
    }

    $apiHdr = ['X-API-KEY: ' . $apiKey];
    foreach ([
        'Restapi5 products' => '/restapi5/products',
        'Restapi5 catlogs' => '/restapi5/catlogs',
        'Restapi5 mobile_homescreen/1' => '/restapi5/mobile_homescreen/1',
    ] as $label => $path) {
        $r = phase4_httpRequest("$base$path", $cookieFile, null, $apiHdr);
        phase4_check($label, in_array($r['code'], [200, 401, 403, 404], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
    }

    $r = phase4_httpRequest("$base/restapi5/login", $cookieFile, json_encode([
        'username' => 'invalid',
        'password' => 'invalid',
    ]), array_merge($apiHdr, ['Content-Type: application/json']));
    phase4_check('Restapi5 login POST', in_array($r['code'], [200, 401, 403], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('API deep-link suite', true, 'skipped — no api_privatekey');
}

$r = phase4_httpGetFollow("$base/whatsapp/pdf_eshop_order/0", $cookieFile);
phase4_check('Whatsapp pdf_eshop_order missing', in_array($r['code'], [200, 404], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

echo "\n=== APIs deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
