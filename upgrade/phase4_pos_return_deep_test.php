<?php
/**
 * Phase 4 — POS return sale deep submit (PHP 8.5)
 * Creates a POS sale, then POST pos/returnsale with one line item.
 * Run: php phase4_pos_return_deep_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

phase4_webOutputStart();

$base = PHASE4_BASE_URL;
list($identity, $password) = phase4_credentials();
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_pos_return_deep_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    phase4_usage('phase4_pos_return_deep_test.php');
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

function ret_httpRequest($url, $cookieFile, $post = null, $headers = [])
{
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_USERAGENT => 'Mozilla/5.0 PHPUpgradeTest/pos-return',
        CURLOPT_HTTPHEADER => array_merge(['Referer: ' . PHASE4_BASE_URL . '/pos'], $headers),
    ];
    if ($post !== null) {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = $post;
        $opts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    return [
        'code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'body' => $body ?: '',
        'location' => curl_getinfo($ch, CURLINFO_REDIRECT_URL),
    ];
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

phase4_check('POS screen load', $r['code'] === 200 && stripos($r['body'], 'pos-sale-form') !== false && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

$warehouse = ret_extractWarehouse($r['body']);
$customer = ret_extractHidden($r['body'], 'poscustomer') ?: ret_extractHidden($r['body'], 'customer');
$biller = ret_extractHidden($r['body'], 'biller');
$orderTax = ret_extractHidden($r['body'], 'postax2') ?: '0';
$token = phase4_extractCsrf($r['body']);

phase4_check('Parse POS defaults', $warehouse && $customer && $biller && $token, "wh=$warehouse cust=$customer biller=$biller");

$sug = phase4_httpGetFollow("$base/sales/suggestions?term=test&warehouse_id=$warehouse&customer_id=$customer&pos=1", $cookieFile);
$sugItems = json_decode($sug['body'], true);
$productCode = (is_array($sugItems) && !empty($sugItems[0]['row']['code'])) ? $sugItems[0]['row']['code'] : null;
phase4_check('Product suggestions AJAX', $sug['code'] === 200 && $productCode, $productCode ? "code=$productCode" : 'no product');

$saleId = null;
if ($productCode) {
    $gp = phase4_httpGetFollow("$base/pos/getProductDataByCode?code=" . urlencode($productCode) . "&warehouse_id=$warehouse&customer_id=$customer", $cookieFile);
    $gpJson = json_decode($gp['body'], true);
    phase4_check('getProductDataByCode AJAX', $gp['code'] === 200 && is_array($gpJson) && isset($gpJson['row']), 'HTTP ' . $gp['code']);

    if (is_array($gpJson) && isset($gpJson['row'])) {
        $row = (array) $gpJson['row'];
        $taxRate = (string) ($row['tax_rate'] ?? '0');
        $unit = (string) ($row['sale_unit'] ?? ($row['unit'] ?? '1'));
        $price = (float) ($row['price'] ?? 100);
        $mrp = (float) ($row['mrp'] ?? $price);
        $qty = 1;
        $taxAmt = 0;
        if (!empty($gpJson['tax_rate']) && is_array($gpJson['tax_rate'])) {
            $taxDetails = (array) $gpJson['tax_rate'];
            if (isset($taxDetails['type'], $taxDetails['rate'])) {
                $taxAmt = ($taxDetails['type'] == 1) ? ($price * $qty * (float) $taxDetails['rate'] / 100) : (float) $taxDetails['rate'];
            }
        }
        $grand = round($price * $qty + $taxAmt, 2);

        $salePost = [
            'token' => $token,
            'warehouse' => $warehouse,
            'biller' => $biller,
            'customer' => $customer,
            'pos_sale_person' => '0',
            'total_items' => '1',
            'order_tax' => $orderTax,
            'discount' => '',
            'shipping' => '0',
            'payment_method' => 'cash',
            'amount-paid' => (string) $grand,
            'submit_type' => 'notprint',
            'product_id[]' => (string) ($row['id'] ?? ''),
            'product_code[]' => (string) ($row['code'] ?? $productCode),
            'product_name[]' => (string) ($row['name'] ?? 'Test Product'),
            'product_type[]' => (string) ($row['type'] ?? 'standard'),
            'hsn_code[]' => (string) ($row['hsn_code'] ?? ''),
            'article_code[]' => (string) ($row['article_code'] ?? ''),
            'product_option[]' => '0',
            'product_unit[]' => $unit,
            'product_base_quantity[]' => (string) $qty,
            'quantity[]' => (string) $qty,
            'unit_price[]' => (string) $price,
            'real_unit_price[]' => (string) $price,
            'product_discount[]' => '0',
            'discount_on_mrp[]' => '0%',
            'product_tax[]' => $taxRate,
            'item_description[]' => '',
            'item_note[]' => '',
            'manualedit[]' => '0',
            'item_weight[]' => '',
            'cat_id[]' => (string) ($row['category_id'] ?? '1'),
            'mrp[]' => (string) $mrp,
            'customer_group_discount[]' => '0',
            'itemsalesperson[]' => '0',
            'product_option_color[]' => '',
            'batch_number[]' => '',
            'amount[]' => (string) $grand,
            'paid_by[]' => 'cash',
            'balance_amount[]' => '0',
            'cheque_no[]' => '',
            'cc_no[]' => '',
            'cc_holder[]' => '',
            'cc_month[]' => '',
            'cc_year[]' => '',
            'cc_type[]' => '',
            'cc_cvv2[]' => '',
            'payment_note[]' => '',
            'cc_transac_no[]' => '',
            'other_tran[]' => '',
            'other_tran_mode[]' => '',
            'paying_gift_card_no[]' => '',
            'ap[]' => '',
            'source' => 'Walk-in',
        ];

        $r2 = phase4_httpGetFollow("$base/pos", $cookieFile);
        if ($t2 = phase4_extractCsrf($r2['body'])) {
            $salePost['token'] = $t2;
        }
        $sr = ret_httpRequest("$base/pos", $cookieFile, http_build_query($salePost));
        $saleJson = json_decode($sr['body'], true);
        $saleOk = is_array($saleJson) && isset($saleJson['status']) && $saleJson['status'] === 'success';
        $saleId = $saleOk ? ($saleJson['sale']['sale_id'] ?? null) : null;
        phase4_check('Setup POS sale for return', $saleOk, $saleOk ? "sale_id=$saleId" : 'HTTP ' . $sr['code']);

        if ($saleOk && $saleId) {
            $refNo = 'RET-TEST-' . date('YmdHis');
            $returnPost = [
                'token' => phase4_extractCsrf($r2['body']) ?: $salePost['token'],
                'customer' => $customer,
                'warehouse' => $warehouse,
                'biller' => $biller,
                'pos_sale_person' => '0',
                'payment_reference_no' => $refNo,
                'grandtotal' => (string) $grand,
                'return_amounts' => (string) $grand,
                'amount-paid' => (string) $grand,
                'order_tax' => $orderTax,
                'shipping' => '0',
                'product_id[]' => (string) ($row['id'] ?? ''),
                'return_quantity[]' => (string) $qty,
                'return_product_base_quantity[]' => (string) $qty,
                'return_subtotal[]' => (string) $grand,
                'return_netprice[]' => (string) $price,
                'return_product_discount[]' => '0',
                'return_product_tax[]' => $taxRate,
                'return_item_price[]' => (string) $price,
                'article_code[]' => (string) ($row['article_code'] ?? ''),
                'product_name[]' => (string) ($row['name'] ?? 'Test Product'),
                'totalpayable[]' => (string) $grand,
                'mrp' => (string) $mrp,
                'mrpdiscount[]' => '0%',
                'unitprices[]' => (string) $price,
                'option_ids[]' => '0',
                'cat_id[]' => (string) ($row['category_id'] ?? '1'),
                'product_option_color[]' => '',
                'manualedit[]' => '0',
                'customer_group_discount[]' => '0',
            ];

            $rr = ret_httpRequest("$base/pos/returnsale", $cookieFile, http_build_query($returnPost));
            $retOk = in_array($rr['code'], [200, 302, 303], true) && !phase4_hasPhpIssue($rr['body']);
            $detail = 'HTTP ' . $rr['code'];
            if (!$retOk && phase4_hasPhpIssue($rr['body'])) {
                preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,160}/', $rr['body'], $em);
                $detail = trim(strip_tags($em[0] ?? $detail));
            } elseif ($retOk && !empty($rr['location'])) {
                $detail .= ' location=' . $rr['location'];
            }
            phase4_check('POST pos/returnsale submit', $retOk, $detail);

            if ($retOk) {
                $vr = phase4_httpGetFollow("$base/pos/view/$saleId", $cookieFile);
                phase4_check('Original sale view after return', $vr['code'] === 200 && !phase4_hasPhpIssue($vr['body']), 'HTTP ' . $vr['code']);
            }
        }
    }
} else {
    phase4_check('Setup POS sale for return', false, 'no product code');
    phase4_check('POST pos/returnsale submit', false, 'skipped');
}

echo "\n=== POS return deep | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
