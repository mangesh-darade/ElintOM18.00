<?php
/**
 * One-off: login devdinein.elintpos.in and add pen product with dummy data.
 * Run: php add_pen_devdinein.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/phase4_test_lib.php';

$base = 'https://devdinein.elintpos.in';
$identity = 'Admin';
$password = 'Admin@554';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'devdinein_pen_cookies.txt';
$caFile = PHPUPGRADE_ROOT . '/app/libraries/cacert.pem';

function devdineinHttpRequest($url, $cookieFile, $post = null, $headers = [], $caFile = '')
{
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) PHPUpgradeTest',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];
    if ($caFile && is_file($caFile)) {
        $opts[CURLOPT_CAINFO] = $caFile;
    }
    // WAMP Windows often lacks system CA for elintpos.in — allow remote smoke add.
    if (stripos($url, 'elintpos.in') !== false) {
        $opts[CURLOPT_SSL_VERIFYPEER] = false;
        $opts[CURLOPT_SSL_VERIFYHOST] = 0;
    }
    if ($post !== null) {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = $post;
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }
    if ($headers) {
        $opts[CURLOPT_HTTPHEADER] = $headers;
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $err = curl_error($ch);
    return [
        'code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'body' => $body ?: '',
        'location' => curl_getinfo($ch, CURLINFO_REDIRECT_URL),
        'error' => $err,
    ];
}

function devdineinHttpGetFollow($url, $cookieFile, $caFile, $max = 6)
{
    for ($i = 0; $i < $max; $i++) {
        $r = devdineinHttpRequest($url, $cookieFile, null, [], $caFile);
        if (!in_array($r['code'], [301, 302, 303, 307, 308], true) || empty($r['location'])) {
            return $r;
        }
        $url = $r['location'];
    }
    return $r;
}

function devdineinLogin($base, $identity, $password, $cookieFile, $caFile)
{
    @unlink($cookieFile);
    $r = devdineinHttpRequest("$base/login", $cookieFile, null, [], $caFile);
    if ($r['code'] === 0) {
        echo "Login page curl error: {$r['error']}\n";
        return false;
    }
    $token = phase4_extractCsrf($r['body']);
    $r = devdineinHttpRequest("$base/auth/login", $cookieFile, http_build_query([
        'identity' => $identity,
        'password' => $password,
        'login_device' => 'web',
        'token' => $token ?? '',
    ]), [], $caFile);
    if (in_array($r['code'], [302, 303], true) && $r['location']) {
        devdineinHttpRequest($r['location'], $cookieFile, null, [], $caFile);
    }
    return $r['code'] > 0 && !phase4_hasPhpIssue($r['body']);
}

function extractSelectFirst($html, $name)
{
    if (preg_match('/name="' . preg_quote($name, '/') . '"[^>]*>.*?<option[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/id="[^"]*' . preg_quote($name, '/') . '"[^>]*>.*?<option[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    return null;
}

@unlink($cookieFile);

echo "=== Login ($base) ===\n";
$ok = devdineinLogin($base, $identity, $password, $cookieFile, $caFile);
echo $ok ? "Login OK\n" : "Login FAIL\n";
if (!$ok) {
    exit(1);
}

$r = devdineinHttpGetFollow("$base/products/add", $cookieFile, $caFile);
echo "Add form HTTP: {$r['code']}\n";
if (!empty($r['error'])) {
    echo "Add form curl error: {$r['error']}\n";
}
if (phase4_hasPhpIssue($r['body'])) {
    echo "PHP issue on add form\n";
    exit(1);
}

$category = extractSelectFirst($r['body'], 'category');
$unit = extractSelectFirst($r['body'], 'unit');
$brand = extractSelectFirst($r['body'], 'brand');
$token = phase4_extractCsrf($r['body']);
$posType = '';
if (preg_match('/id="pos_type"[^>]*value="([^"]*)"/', $r['body'], $pm)) {
    $posType = $pm[1];
}
$uniq = 'PEN' . date('ymdHis');

echo "category=$category unit=$unit brand=$brand code=$uniq\n";

if (!$category || !$unit || !$token) {
    echo "Missing required form fields\n";
    exit(1);
}

$fields = [
    'token' => $token,
    'type' => 'standard',
    'category' => $category,
    'name' => 'Blue Ballpoint Pen',
    'code' => $uniq,
    'article_code' => 'PEN-ART-001',
    'hsn_code' => '96081019',
    'cost' => '8.00',
    'price' => '15.00',
    'mrp' => '20.00',
    'unit' => $unit,
    'default_sale_unit' => $unit,
    'default_purchase_unit' => $unit,
    'barcode_symbology' => 'code128',
    'tax_method' => '0',
    'tax_rate' => '',
    'storage_type' => 'packed',
    'pos_type' => $posType,
    'track_quantity' => '1',
    'season' => '0',
    'alert_quantity' => '10',
    'weight' => '0.010',
    'details' => 'Smooth writing blue ink ballpoint pen for daily use.',
    'product_details' => 'Pack of 1 pen. Body color: blue. Tip size: 0.7mm.',
];
if ($brand) {
    $fields['brand'] = $brand;
}

$r2 = devdineinHttpGetFollow("$base/products/add", $cookieFile, $caFile);
if ($t2 = phase4_extractCsrf($r2['body'])) {
    $fields['token'] = $t2;
}

$sr = devdineinHttpRequest(
    "$base/products/add",
    $cookieFile,
    http_build_query($fields),
    ['Referer: ' . $base . '/products/add'],
    $caFile
);
echo "Submit HTTP: {$sr['code']}\n";
if (!empty($sr['location'])) {
    echo "Redirect: {$sr['location']}\n";
}

$addOk = in_array($sr['code'], [302, 303], true) && stripos($sr['location'] ?? '', 'products') !== false;
if (!$addOk && phase4_hasPhpIssue($sr['body'])) {
    preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,200}/', $sr['body'], $em);
    echo 'Error: ' . ($em[0] ?? 'unknown') . "\n";
} elseif (!$addOk) {
    echo "Submit failed — validation or re-rendered form\n";
}

$productId = null;
$sug = devdineinHttpRequest("$base/products/suggestions?term=" . urlencode($uniq), $cookieFile, null, [], $caFile);
$sugJson = json_decode($sug['body'], true);
if (is_array($sugJson)) {
    foreach ($sugJson as $item) {
        if (!empty($item['row']['code']) && $item['row']['code'] === $uniq) {
            $productId = (int) $item['row']['id'];
            break;
        }
    }
}
if (!$productId) {
    $list = devdineinHttpRequest(
        "$base/products/getProducts",
        $cookieFile,
        phase4_dtPost($fields['token'], ['sSearch' => $uniq, 'iDisplayLength' => 50]),
        [],
        $caFile
    );
    $json = json_decode($list['body'], true);
    if (!empty($json['aaData'])) {
        foreach ($json['aaData'] as $row) {
            if (isset($row[2]) && stripos((string) $row[2], $uniq) !== false) {
                $productId = (int) $row[0];
                break;
            }
        }
    }
}

echo "\n=== RESULT ===\n";
echo "Product Name : Blue Ballpoint Pen\n";
echo "Product Code : $uniq\n";
echo "Cost/Price/MRP: 8.00 / 15.00 / 20.00\n";
echo "HSN Code     : 96081019 (pens & markers)\n";
echo "Details      : Blue ink ballpoint pen, 0.7mm tip\n";
echo "Status       : " . ($addOk ? 'ADDED SUCCESSFULLY' : 'FAILED') . "\n";
echo "Product ID   : " . ($productId ?: 'not found') . "\n";
echo "View URL     : $base/products/view/" . ($productId ?: '{id}') . "\n";

exit($addOk ? 0 : 1);
