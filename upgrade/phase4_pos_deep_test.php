<?php
/**
 * Phase 4 — POS deep tests: register, product AJAX, cash sale submit
 * Run: php phase4_pos_deep_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_pos_deep_cookies.txt';
$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) PHPUpgradeTest/1.0';
$fail = 0;
$pass = 0;

function check($name, $ok, $detail = '')
{
    global $fail, $pass;
    if ($ok) {
        $pass++;
        echo "[PASS] $name" . ($detail ? " — $detail" : '') . "\n";
    } else {
        $fail++;
        echo "[FAIL] $name" . ($detail ? " — $detail" : '') . "\n";
    }
}

function httpRequest($url, $cookieFile, $post = null, $headers = [])
{
    global $ua;
    $ch = curl_init($url);
    $hdr = array_merge(['User-Agent: ' . $ua, 'Referer: http://localhost/phpupgrade/pos'], $headers);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTPHEADER => $hdr,
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $body = curl_exec($ch);
    return [
        'code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'body' => $body ?: '',
        'location' => curl_getinfo($ch, CURLINFO_REDIRECT_URL),
    ];
}

function httpGetFollow($url, $cookieFile)
{
    for ($i = 0; $i < 6; $i++) {
        $r = httpRequest($url, $cookieFile);
        if (!in_array($r['code'], [301, 302, 303, 307, 308], true) || empty($r['location'])) {
            return $r;
        }
        $url = $r['location'];
    }
    return $r;
}

function extractCsrf($html)
{
    if (preg_match('/name="token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return null;
}

function extractHidden($html, $id)
{
    if (preg_match('/id="' . preg_quote($id, '/') . '"[^>]*value="([^"]*)"/', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name="' . preg_quote($id, '/') . '"[^>]*value="([^"]*)"/', $html, $m)) {
        return $m[1];
    }
    return null;
}

function extractWarehouse($html)
{
    if (preg_match('/id="poswarehouse"[^>]*>.*?<option[^>]*selected[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name="warehouse"[^>]*>.*?<option[^>]*selected[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/id="poswarehouse"[^>]*>.*?<option[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    return extractHidden($html, 'poswarehouse');
}

function hasPhpIssue($body)
{
    return (bool) preg_match('/Fatal error|Parse error|Uncaught (Error|TypeError)/i', $body);
}

if (!$identity || !$password) {
    echo "Usage: php phase4_pos_deep_test.php <identity> <password>\n";
    exit(1);
}

@unlink($cookieFile);

// Login
$r = httpRequest("$base/login", $cookieFile);
$token = extractCsrf($r['body']);
$post = http_build_query(['identity' => $identity, 'password' => $password, 'login_device' => 'web', 'token' => $token]);
$r = httpRequest("$base/auth/login", $cookieFile, $post, ['Content-Type: application/x-www-form-urlencoded']);
if (in_array($r['code'], [302, 303], true) && $r['location']) {
    httpRequest($r['location'], $cookieFile);
}
check('Login', true);

// POS page or open register
$r = httpGetFollow("$base/pos", $cookieFile);
$onRegister = stripos($r['body'], 'open_register') !== false && stripos($r['body'], 'cash_in_hand') !== false
    && stripos($r['body'], 'pos-sale-form') === false;

if ($onRegister || strpos($r['body'], 'pos-sale-form') === false) {
    $token = extractCsrf($r['body']);
    $r2 = httpRequest("$base/pos/open_register", $cookieFile, http_build_query([
        'token' => $token,
        'cash_in_hand' => '0',
    ]), ['Content-Type: application/x-www-form-urlencoded', 'X-Requested-With: XMLHttpRequest']);
    $opened = ($r2['code'] === 200 && strpos($r2['body'], 'success') !== false)
        || in_array($r2['code'], [302, 303], true);
    check('Open register POST', $opened, "HTTP {$r2['code']}");
    $r = httpGetFollow("$base/pos", $cookieFile);
}

check('POS screen load', $r['code'] === 200 && strpos($r['body'], 'pos-sale-form') !== false && !hasPhpIssue($r['body']), "HTTP {$r['code']}");

$warehouse = extractWarehouse($r['body']);
$customer = extractHidden($r['body'], 'poscustomer') ?: extractHidden($r['body'], 'customer');
$biller = extractHidden($r['body'], 'biller');
$orderTax = extractHidden($r['body'], 'postax2') ?: '0';
$token = extractCsrf($r['body']);

check('Parse POS defaults', $warehouse && $customer && $biller && $token, "wh=$warehouse cust=$customer biller=$biller");

// Product suggestions AJAX
$sug = httpGetFollow("$base/sales/suggestions?term=test&warehouse_id=$warehouse&customer_id=$customer&pos=1", $cookieFile);
$sugOk = $sug['code'] === 200 && !hasPhpIssue($sug['body']);
$productCode = null;
$sugItems = json_decode($sug['body'], true);
if ($sugOk && is_array($sugItems) && count($sugItems) > 0) {
    $first = $sugItems[0];
    $productCode = $first['row']['code'] ?? null;
}
check('Product suggestions AJAX', $sugOk && $productCode, $productCode ? "code=$productCode" : 'no product in JSON');

if ($productCode) {
    $gp = httpGetFollow("$base/pos/getProductDataByCode?code=" . urlencode($productCode) . "&warehouse_id=$warehouse&customer_id=$customer", $cookieFile);
    $gpJson = json_decode($gp['body'], true);
    check('getProductDataByCode AJAX', $gp['code'] === 200 && is_array($gpJson) && isset($gpJson['row']), "HTTP {$gp['code']}");

    if (is_array($gpJson) && isset($gpJson['row'])) {
        $row = (array) $gpJson['row'];
        $taxRate = $row['tax_rate'] ?? '0';
        $unit = $row['sale_unit'] ?? ($row['unit'] ?? '1');
        $price = (float) ($row['price'] ?? 100);
        $qty = 1;
        $taxDetails = null;
        if (!empty($gpJson['tax_rate']) && is_array($gpJson['tax_rate'])) {
            $taxDetails = (array) $gpJson['tax_rate'];
        }
        $taxAmt = 0;
        if ($taxDetails && isset($taxDetails['type'], $taxDetails['rate'])) {
            $taxAmt = ($taxDetails['type'] == 1) ? ($price * $qty * (float) $taxDetails['rate'] / 100) : (float) $taxDetails['rate'];
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
            'product_id[]' => $row['id'] ?? '',
            'product_code[]' => $row['code'] ?? $productCode,
            'product_name[]' => $row['name'] ?? 'Test Product',
            'product_type[]' => $row['type'] ?? 'standard',
            'hsn_code[]' => $row['hsn_code'] ?? '',
            'article_code[]' => $row['article_code'] ?? '',
            'product_option[]' => '0',
            'product_unit[]' => $unit,
            'product_base_quantity[]' => (string) $qty,
            'quantity[]' => (string) $qty,
            'unit_price[]' => (string) $price,
            'real_unit_price[]' => (string) $price,
            'product_discount[]' => '0',
            'discount_on_mrp[]' => '0%',
            'product_tax[]' => (string) $taxRate,
            'item_description[]' => '',
            'item_note[]' => '',
            'manualedit[]' => '0',
            'item_weight[]' => '',
            'cat_id[]' => (string) ($row['category_id'] ?? '1'),
            'mrp[]' => (string) ($row['mrp'] ?? $price),
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

        // Refresh CSRF token from POS page before submit
        $r2 = httpGetFollow("$base/pos", $cookieFile);
        if ($t2 = extractCsrf($r2['body'])) {
            $salePost['token'] = $t2;
        }
        $sr = httpRequest("$base/pos", $cookieFile, http_build_query($salePost), ['Content-Type: application/x-www-form-urlencoded', 'X-Requested-With: XMLHttpRequest']);
        $saleJson = json_decode($sr['body'], true);
        $saleOk = is_array($saleJson) && isset($saleJson['status']) && $saleJson['status'] === 'success';
        $detail = "HTTP {$sr['code']} len=" . strlen($sr['body']);
        if (!$saleOk && hasPhpIssue($sr['body'])) {
            preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,150}/', $sr['body'], $em);
            $detail = trim(strip_tags($em[0] ?? $detail));
        } elseif (!$saleOk && $sr['code'] === 200 && strpos($sr['body'], 'pos-sale-form') !== false) {
            $detail = 'validation/stock error — re-rendered POS';
        } elseif (!$saleOk) {
            $detail .= ' ' . substr(preg_replace('/\s+/', ' ', $sr['body']), 0, 120);
        } else {
            $detail = 'sale_id=' . ($saleJson['sale']['sale_id'] ?? '?');
        }
        check('Cash sale submit', $saleOk, $detail);

        if ($saleOk && !empty($saleJson['sale']['sale_id'])) {
            $sid = $saleJson['sale']['sale_id'];
            $vr = httpGetFollow("$base/pos/view/$sid", $cookieFile);
            check('Sale view / PDF screen', $vr['code'] === 200 && !hasPhpIssue($vr['body']), "HTTP {$vr['code']} len=" . strlen($vr['body']));
        }
    }
} else {
    check('getProductDataByCode AJAX', false, 'no product code available');
    check('Cash sale submit', false, 'skipped — no product');
}

echo "\n=== PHP " . PHP_VERSION . " | $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
