<?php
/**
 * Phase 4 — POS return / exchange deep-link tests (PHP 8.5)
 * Run: php phase4_pos_return_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

phase4_webOutputStart();

$base = PHASE4_BASE_URL;
list($identity, $password) = phase4_credentials();
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_pos_return_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    phase4_usage('phase4_pos_return_links_test.php');
    exit(1);
}

function ret_extractHidden($html, $id)
{
    if (preg_match('/id="' . preg_quote($id, '/') . '"[^>]*value="([^"]*)"/', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name="' . preg_quote($id, '/') . '"[^>]*value="([^"]*)"/', $html, $m)) {
        return $m[1];
    }
    return null;
}

function ret_extractWarehouse($html)
{
    if (preg_match('/id="poswarehouse"[^>]*>.*?<option[^>]*selected[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/id="poswarehouse"[^>]*>.*?<option[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    return ret_extractHidden($html, 'poswarehouse');
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/pos", $cookieFile);
$onRegister = stripos($r['body'], 'cash_in_hand') !== false && stripos($r['body'], 'pos-sale-form') === false;
if ($onRegister) {
    $token = phase4_extractCsrf($r['body']);
    phase4_httpRequest("$base/pos/open_register", $cookieFile, http_build_query([
        'token' => $token,
        'cash_in_hand' => '0',
    ]));
    $r = phase4_httpGetFollow("$base/pos", $cookieFile);
}
phase4_check('POS screen load', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
$posBody = $r['body'];

$hasReturnModal = (stripos($posBody, 'id="exampleModal"') !== false)
    || (stripos($posBody, "form_open('pos/returnsale')") !== false)
    || (stripos($posBody, 'pos/returnsale') !== false);
phase4_check('POS return modal markup', $hasReturnModal, $hasReturnModal ? 'returnsale form present' : 'return UI not in POS view');

$hasReturnBtn = (stripos($posBody, 'id="send-datas"') !== false)
    || (stripos($posBody, 'Return Sale') !== false);
phase4_check('POS Return Sale button', $hasReturnBtn, $hasReturnBtn ? 'button present' : 'display_return may be off');

$hasExchangeBtn = stripos($posBody, 'id="exchangeAllProductsButton"') !== false;
phase4_check('POS Exchange button', $hasExchangeBtn, $hasExchangeBtn ? 'button present' : 'display_exchange may be off');

$rRetGet = phase4_httpGetFollow("$base/pos/returnsale", $cookieFile);
$retGetOk = in_array($rRetGet['code'], [200, 302, 303], true) && !phase4_hasPhpIssue($rRetGet['body']);
phase4_check('GET pos/returnsale (POST-only guard)', $retGetOk, 'HTTP ' . $rRetGet['code'] . ' — should redirect, not fatal');

$r = phase4_httpGetFollow("$base/pos/sales", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/pos/getSales", $cookieFile, phase4_dtPost($token));
$saleId = phase4_firstDtId($r['body']);
phase4_check('pos/getSales AJAX', $r['code'] === 200 && $saleId !== null, 'sale_id=' . ($saleId ?? 'none'));

if ($saleId) {
    phase4_linkGet($base, 'POS sales list return screen', "/sales/return_sale/$saleId", $cookieFile);
    phase4_linkGet($base, 'POS view sale', "/pos/view/$saleId", $cookieFile);
    phase4_linkGet($base, 'Sales modal from POS', "/sales/modal_view/$saleId", $cookieFile);

    $vr = phase4_httpGetFollow("$base/pos/view/$saleId", $cookieFile);
    if (preg_match('/sales\/modal_view\/(\d+)/', $vr['body'], $m)) {
        phase4_linkGet($base, 'Linked return invoice modal', '/sales/modal_view/' . $m[1], $cookieFile);
    } else {
        phase4_check('Linked return invoice modal', true, 'no return_id on this sale (skipped)');
    }
} else {
    phase4_check('POS return deep links', false, 'no sale id from pos/getSales');
}

$warehouse = ret_extractWarehouse($posBody);
$customer = ret_extractHidden($posBody, 'poscustomer') ?: ret_extractHidden($posBody, 'customer');

if ($warehouse && $customer) {
    $sug = phase4_httpGetFollow("$base/sales/suggestions?term=test&warehouse_id=$warehouse&customer_id=$customer&pos=1", $cookieFile);
    phase4_check('Return invoice lookup AJAX', $sug['code'] === 200 && !phase4_hasPhpIssue($sug['body']), 'HTTP ' . $sug['code']);

    if ($saleId) {
        $ref = phase4_httpGetFollow("$base/sales/suggestions?term=$saleId&warehouse_id=$warehouse&customer_id=$customer&pos=1", $cookieFile);
        phase4_check('Return by sale id suggestions', $ref['code'] === 200 && !phase4_hasPhpIssue($ref['body']), 'HTTP ' . $ref['code']);
    }
} else {
    phase4_check('Return invoice lookup AJAX', false, 'warehouse/customer not parsed from POS');
}

phase4_linkGet($base, 'POS sales list screen', '/pos/sales', $cookieFile);

echo "\n=== POS return/exchange deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
