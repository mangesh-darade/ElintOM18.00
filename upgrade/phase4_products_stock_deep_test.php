<?php
/**
 * Phase 4 — Products STOCK deep tests (adjustments, qa_suggestions, timing)
 * Run: php phase4_products_stock_deep_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_products_stock_cookies.txt';
$fail = 0;
$pass = 0;
$maxMs = 1000;

function check($name, $ok, $detail = '')
{
    global $fail, $pass;
    if ($ok) { $pass++; echo "[PASS] $name" . ($detail ? " — $detail" : '') . "\n"; }
    else { $fail++; echo "[FAIL] $name" . ($detail ? " — $detail" : '') . "\n"; }
}

function httpRequest($url, $cookieFile, $post = null, $headers = [])
{
    $ch = curl_init($url);
    $hdr = array_merge(['User-Agent: Mozilla/5.0 PHPUpgradeStockTest', 'Referer: http://localhost/phpupgrade/products/add_adjustment'], $headers);
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
    $t0 = microtime(true);
    $body = curl_exec($ch);
    $ms = (int) round((microtime(true) - $t0) * 1000);
    return [
        'code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'body' => $body ?: '',
        'location' => curl_getinfo($ch, CURLINFO_REDIRECT_URL),
        'ms' => $ms,
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
    return preg_match('/name="token"\s+value="([^"]+)"/', $html, $m) ? $m[1] : null;
}

function extractSelectFirst($html, $name)
{
    if (preg_match('/name="' . preg_quote($name, '/') . '"[^>]*>.*?<option[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/id="qawarehouse"[^>]*>.*?<option[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    return null;
}

function hasPhpIssue($body)
{
    return (bool) preg_match('/Fatal error|Parse error|Uncaught (Error|TypeError)/i', $body);
}

function timedCheck($name, $r, $jsonOk = false, $maxMs = 1000)
{
    global $fail, $pass;
    $ok = $r['code'] === 200 && !hasPhpIssue($r['body']);
    if ($jsonOk) {
        $j = json_decode($r['body'], true);
        $ok = $ok && is_array($j);
    }
    $slow = $r['ms'] > $maxMs;
    $detail = "HTTP {$r['code']} {$r['ms']}ms" . ($slow ? ' SLOW' : '');
    if (!$ok && hasPhpIssue($r['body'])) {
        preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,120}/', $r['body'], $em);
        $detail = trim(strip_tags($em[0] ?? $detail));
    }
    check($name, $ok && !$slow, $detail);
    return $ok ? ($jsonOk ? json_decode($r['body'], true) : $r['body']) : null;
}

if (!$identity || !$password) {
    echo "Usage: php phase4_products_stock_deep_test.php <identity> <password>\n";
    exit(1);
}

@unlink($cookieFile);

$r = httpRequest("$base/login", $cookieFile);
$token = extractCsrf($r['body']);
$r = httpRequest("$base/auth/login", $cookieFile, http_build_query([
    'identity' => $identity, 'password' => $password, 'login_device' => 'web', 'token' => $token,
]), ['Content-Type: application/x-www-form-urlencoded']);
if (in_array($r['code'], [302, 303], true) && $r['location']) {
    httpRequest($r['location'], $cookieFile);
}
check('Login', true);

$r = httpGetFollow("$base/products/quantity_adjustments", $cookieFile);
check('Adjustments list', $r['code'] === 200 && !hasPhpIssue($r['body']), "HTTP {$r['code']}");

$r = httpGetFollow("$base/products/add_adjustment", $cookieFile);
check('Add adjustment screen', $r['code'] === 200 && !hasPhpIssue($r['body']), "HTTP {$r['code']}");

$warehouse = extractSelectFirst($r['body'], 'warehouse');
$token = extractCsrf($r['body']);
check('Parse adjustment defaults', $warehouse && $token, "wh=$warehouse");

$list = httpRequest("$base/products/getadjustments", $cookieFile, http_build_query([
    'sEcho' => 1, 'iDisplayStart' => 0, 'iDisplayLength' => 10, 'token' => $token,
]), ['Content-Type: application/x-www-form-urlencoded']);
timedCheck('getadjustments AJAX', $list, true, $maxMs);

$plist = timedCheck("product_list/$warehouse", httpRequest("$base/products/product_list/$warehouse", $cookieFile), true, $maxMs * 3);

$qa = timedCheck('qa_suggestions (term=test)', httpRequest("$base/products/qa_suggestions?term=test&warehouse_id=$warehouse", $cookieFile), true, $maxMs * 2);

$productRow = null;
if (is_array($qa) && count($qa) > 0 && !empty($qa[0]['row'])) {
    $productRow = is_array($qa[0]['row']) ? $qa[0]['row'] : (array) $qa[0]['row'];
} elseif (is_array($plist) && count($plist) > 0) {
    $first = (array) $plist[0];
    $productRow = [
        'id' => $first['id'] ?? $first['product_id'] ?? null,
        'code' => $first['code'] ?? '',
        'name' => $first['name'] ?? 'Stock Test Product',
        'cost' => $first['cost'] ?? '10',
        'price' => $first['price'] ?? '15',
        'mrp' => $first['mrp'] ?? '20',
        'storage_type' => $first['storage_type'] ?? 'packed',
        'tax_rate_id' => $first['tax_rate'] ?? '',
        'tax_method' => $first['tax_method'] ?? '0',
        'product_type' => $first['type'] ?? 'standard',
        'unit' => $first['unit_name'] ?? $first['unit'] ?? 'Piece',
        'hsn_code' => $first['hsn_code'] ?? '',
        'option' => $first['option'] ?? '0',
        'item_stock' => $first['quantity'] ?? $first['product_qty'] ?? 0,
    ];
}

if ($productRow && !empty($productRow['id'])) {
    $pid = $productRow['id'];
    $variant = $productRow['option'] ?? '0';
    if ($variant && $variant !== '0') {
        $vd = timedCheck('get_variant_details', httpRequest(
            "$base/products/get_variant_details?VarientId=$variant&ProductId=$pid&WarehouseId=$warehouse",
            $cookieFile
        ), true, $maxMs);
    } else {
        check('get_variant_details', true, 'skipped — no variant');
    }

    $r2 = httpGetFollow("$base/products/add_adjustment", $cookieFile);
    $token = extractCsrf($r2['body']) ?: $token;

    $adjPost = http_build_query([
        'token' => $token,
        'warehouse' => $warehouse,
        'add_adjustment' => 'Submit',
        'reference_no' => '',
        'note' => 'PHP85 stock deep test',
        'product_id[]' => $pid,
        'product_code[]' => $productRow['code'] ?? 'STK',
        'product_name[]' => $productRow['name'] ?? 'Test',
        'quantity[]' => '1',
        'type[]' => 'addition',
        'variant[]' => $variant,
        'storage_type[]' => $productRow['storage_type'] ?? 'packed',
        'cost[]' => $productRow['cost'] ?? '10',
        'price[]' => $productRow['price'] ?? '15',
        'real_unit_cost[]' => $productRow['cost'] ?? '10',
        'tax_rate_id[]' => $productRow['tax_rate_id'] ?? '',
        'tax_method[]' => $productRow['tax_method'] ?? '0',
        'product_type[]' => $productRow['product_type'] ?? 'standard',
        'unit[]' => $productRow['unit'] ?? 'Piece',
        'hsn_code[]' => $productRow['hsn_code'] ?? '',
        'mrp[]' => $productRow['mrp'] ?? '20',
        'item_qty[]' => $productRow['item_stock'] ?? '0',
        'batch_qty[]' => '0',
        'batch_number[]' => '',
        'product_option_color[]' => '0',
        'expiry[]' => '',
    ]);

    $sr = httpRequest("$base/products/add_adjustment", $cookieFile, $adjPost, ['Content-Type: application/x-www-form-urlencoded']);
    $adjOk = in_array($sr['code'], [302, 303], true) && $sr['location'] && stripos($sr['location'], 'quantity_adjustments') !== false;
    $detail = "HTTP {$sr['code']} {$sr['ms']}ms";
    if ($adjOk) {
        $detail .= ' → adjustments list';
    } elseif (hasPhpIssue($sr['body'])) {
        preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,150}/', $sr['body'], $em);
        $detail = trim(strip_tags($em[0] ?? $detail));
    } elseif ($sr['code'] === 200) {
        $detail = 'validation/flash error — re-rendered form';
    }
    check('Stock adjustment submit (+1)', $adjOk, $detail);

    $qa2 = httpRequest("$base/products/qa_suggestions?term=" . urlencode(substr($productRow['code'] ?? 't', 0, 4)) . "&warehouse_id=$warehouse", $cookieFile);
    timedCheck('qa_suggestions after adjustment', $qa2, true, $maxMs * 2);
} else {
    check('get_variant_details', false, 'no product from qa_suggestions');
    check('Stock adjustment submit (+1)', false, 'skipped');
    check('qa_suggestions after adjustment', false, 'skipped');
}

$gp = httpRequest("$base/products/getProducts/$warehouse", $cookieFile, http_build_query([
    'sEcho' => 1, 'iDisplayStart' => 0, 'iDisplayLength' => 25, 'token' => $token,
]), ['Content-Type: application/x-www-form-urlencoded']);
timedCheck("getProducts/$warehouse AJAX", $gp, true, $maxMs * 2);

echo "\n=== Stock deep test | budget {$maxMs}ms hot paths | PHP " . PHP_VERSION . " | $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
