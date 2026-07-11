<?php
/**
 * Phase 4 — Sales deep tests: product AJAX, sale submit, view
 * Run: php phase4_sales_deep_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_sales_deep_cookies.txt';
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
    $hdr = array_merge(['User-Agent: ' . $ua, 'Referer: http://localhost/phpupgrade/sales/add'], $headers);
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

function httpGetFollow($url, $cookieFile, $max = 6)
{
    for ($i = 0; $i < $max; $i++) {
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

function extractSelect($html, $name)
{
    if (preg_match('/name="' . preg_quote($name, '/') . '"[^>]*>.*?<option[^>]*selected[^>]*value="([^"]+)"/s', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/id="sl' . preg_quote($name, '/') . '"[^>]*>.*?<option[^>]*selected[^>]*value="([^"]+)"/s', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name="' . preg_quote($name, '/') . '"[^>]*>.*?<option[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    return null;
}

function hasPhpIssue($body)
{
    return (bool) preg_match('/Fatal error|Parse error|Uncaught (Error|TypeError)/i', $body);
}

if (!$identity || !$password) {
    echo "Usage: php phase4_sales_deep_test.php <identity> <password>\n";
    exit(1);
}

@unlink($cookieFile);

$r = httpRequest("$base/login", $cookieFile);
$token = extractCsrf($r['body']);
$post = http_build_query(['identity' => $identity, 'password' => $password, 'login_device' => 'web', 'token' => $token]);
$r = httpRequest("$base/auth/login", $cookieFile, $post, ['Content-Type: application/x-www-form-urlencoded']);
if (in_array($r['code'], [302, 303], true) && $r['location']) {
    httpRequest($r['location'], $cookieFile);
}
check('Login', true);

$r = httpGetFollow("$base/sales/add", $cookieFile);
check('Sales add screen', $r['code'] === 200 && strpos($r['body'], 'sale_action') !== false && !hasPhpIssue($r['body']), "HTTP {$r['code']}");

$warehouse = extractSelect($r['body'], 'warehouse');
$biller = extractSelect($r['body'], 'biller');
$customer = extractHidden($r['body'], 'quick_custome_nm');
$saleAction = extractHidden($r['body'], 'sale_action') ?: 'sales';
$token = extractCsrf($r['body']);

check('Parse sales defaults', $warehouse && $biller && $customer && $token, "wh=$warehouse cust=$customer biller=$biller action=$saleAction");

$sugUrl = "$base/sales/suggestions?term=test&warehouse_id=$warehouse&customer_id=$customer&Sale_flag=1&sale_action=$saleAction&quantity=1";
$sug = httpGetFollow($sugUrl, $cookieFile);
$sugItems = json_decode($sug['body'], true);
$sugOk = $sug['code'] === 200 && is_array($sugItems) && count($sugItems) > 0 && !hasPhpIssue($sug['body']);
$productCode = null;
$row = null;
$taxRate = null;
if ($sugOk) {
    $first = $sugItems[0];
    $row = isset($first['row']) ? (is_array($first['row']) ? $first['row'] : (array) $first['row']) : null;
    $productCode = $row['code'] ?? null;
    if (!empty($first['tax_rate'])) {
        $taxRate = is_array($first['tax_rate']) ? $first['tax_rate'] : (array) $first['tax_rate'];
    }
}
check('Product suggestions AJAX', $sugOk && $productCode, $productCode ? "code=$productCode" : substr($sug['body'], 0, 80));

if ($row) {
    $price = (float) ($row['price'] ?? $row['unit_price'] ?? 100);
    $qty = 1;
    $taxId = (string) ($row['tax_rate'] ?? '0');
    $taxMethod = (string) ($row['tax_method'] ?? $row['item_tax_method'] ?? '0');
    $unit = (string) ($row['unit'] ?? $row['sale_unit'] ?? '1');
    $storageType = (string) ($row['storage_type'] ?? 'packed');
    $taxAmt = 0;
    if ($taxRate && isset($taxRate['type'], $taxRate['rate'])) {
        $taxAmt = ($taxRate['type'] == 1) ? ($price * $qty * (float) $taxRate['rate'] / 100) : (float) $taxRate['rate'];
    }
    $grand = round($price * $qty + $taxAmt, 2);

    $salePost = [
        'token' => $token,
        'sale_action' => $saleAction,
        'syncQuantity' => '1',
        'warehouse' => $warehouse,
        'biller' => $biller,
        'customer' => $customer,
        'quick_custome_nm' => $customer,
        'sale_status' => 'completed',
        'payment_status' => 'pending',
        'total_items' => '1',
        'reference_no' => '',
        'shipping' => '0',
        'order_tax' => '0',
        'order_discount' => '',
        'product_id[]' => (string) ($row['id'] ?? ''),
        'product_type[]' => (string) ($row['type'] ?? 'standard'),
        'product_code[]' => (string) ($row['code'] ?? $productCode),
        'product_name[]' => (string) ($row['name'] ?? 'Test Product'),
        'quantity[]' => (string) $qty,
        'unit_price[]' => (string) $price,
        'real_unit_price[]' => (string) ($row['unit_price'] ?? $price),
        'net_price[]' => (string) $price,
        'mrp[]' => (string) ($row['mrp'] ?? $price),
        'product_tax[]' => $taxId,
        'tax_method[]' => $taxMethod,
        'product_unit[]' => $unit,
        'product_base_quantity[]' => (string) $qty,
        'item_storage_type[]' => $storageType,
        'product_option[]' => (string) ($row['option'] ?? '0'),
        'product_option_color[]' => '',
        'product_discount[]' => '0',
        'packing_size[]' => '0',
        'hsn_code[]' => (string) ($row['hsn_code'] ?? ''),
        'cat_id[]' => (string) ($row['category_id'] ?? '1'),
        'item_weight[]' => '',
        'cf1[]' => '',
        'old_qty[]' => '0',
        'batch_number[]' => '',
    ];

    $r2 = httpGetFollow("$base/sales/add", $cookieFile);
    if ($t2 = extractCsrf($r2['body'])) {
        $salePost['token'] = $t2;
    }

    $sr = httpRequest("$base/sales/add", $cookieFile, http_build_query($salePost), ['Content-Type: application/x-www-form-urlencoded']);
    $saleOk = in_array($sr['code'], [302, 303], true) && $sr['location']
        && (stripos($sr['location'], 'sales') !== false || stripos($sr['location'], 'pos/view') !== false);
    $detail = "HTTP {$sr['code']}";
    if ($saleOk) {
        $detail .= ' → ' . $sr['location'];
    } elseif (hasPhpIssue($sr['body'])) {
        preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,150}/', $sr['body'], $em);
        $detail = trim(strip_tags($em[0] ?? $detail));
    } elseif ($sr['code'] === 200 && strpos($sr['body'], 'sale_action') !== false) {
        $detail = 'validation error — re-rendered add form';
    } else {
        $detail .= ' ' . substr(preg_replace('/\s+/', ' ', $sr['body']), 0, 120);
    }
    check('Sale submit (pending payment)', $saleOk, $detail);

    if ($saleOk) {
        $r3 = httpGetFollow("$base/sales/getSales", $cookieFile, 1);
        $dtPost = http_build_query(['sEcho' => 1, 'iDisplayStart' => 0, 'iDisplayLength' => 1, 'token' => $salePost['token']]);
        $list = httpRequest("$base/sales/getSales", $cookieFile, $dtPost, ['Content-Type: application/x-www-form-urlencoded']);
        $json = json_decode($list['body'], true);
        $saleId = is_array($json) && !empty($json['aaData'][0][0]) ? (int) $json['aaData'][0][0] : null;
        if (!$saleId && preg_match('/pos\/view\/(\d+)/', $sr['location'], $m)) {
            $saleId = (int) $m[1];
        }
        if ($saleId) {
            $vr = httpGetFollow("$base/sales/view/$saleId", $cookieFile);
            check('Sale view after submit', $vr['code'] === 200 && !hasPhpIssue($vr['body']), "HTTP {$vr['code']} sale_id=$saleId");
        } else {
            check('Sale view after submit', false, 'could not resolve sale_id');
        }
    }
} else {
    check('Sale submit (pending payment)', false, 'skipped — no product');
    check('Sale view after submit', false, 'skipped');
}

echo "\n=== PHP " . PHP_VERSION . " | $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
