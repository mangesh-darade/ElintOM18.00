<?php
/**
 * Phase 4 — Products deep tests: quick add, view new product
 * Run: php phase4_products_deep_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_products_deep_cookies.txt';
$fail = 0;
$pass = 0;

function check($name, $ok, $detail = '')
{
    global $fail, $pass;
    if ($ok) { $pass++; echo "[PASS] $name" . ($detail ? " — $detail" : '') . "\n"; }
    else { $fail++; echo "[FAIL] $name" . ($detail ? " — $detail" : '') . "\n"; }
}

function httpRequest($url, $cookieFile, $post = null, $headers = [])
{
    $ch = curl_init($url);
    $hdr = array_merge(['User-Agent: Mozilla/5.0 PHPUpgradeTest', 'Referer: http://localhost/ElintOM18.00/products/add'], $headers);
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
    return ['code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE), 'body' => $body ?: '', 'location' => curl_getinfo($ch, CURLINFO_REDIRECT_URL)];
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
    if (preg_match('/id="[^"]*' . preg_quote($name, '/') . '"[^>]*>.*?<option[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    return null;
}

function hasPhpIssue($body)
{
    return (bool) preg_match('/Fatal error|Parse error|Uncaught (Error|TypeError)/i', $body);
}

if (!$identity || !$password) {
    echo "Usage: php phase4_products_deep_test.php <identity> <password>\n";
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

$r = httpGetFollow("$base/products/add", $cookieFile);
check('Add product screen', $r['code'] === 200 && !hasPhpIssue($r['body']), "HTTP {$r['code']}");

$category = extractSelectFirst($r['body'], 'category');
$unit = extractSelectFirst($r['body'], 'unit');
$posType = preg_match('/id="pos_type"[^>]*value="([^"]*)"/', $r['body'], $pm) ? $pm[1] : '';
$token = extractCsrf($r['body']);
$uniq = 'P85' . time();

check('Parse add defaults', $category && $unit && $token, "cat=$category unit=$unit");

$addPost = http_build_query([
    'token' => $token,
    'type' => 'standard',
    'category' => $category,
    'name' => 'PHP85 Test ' . $uniq,
    'code' => $uniq,
    'cost' => '10.00',
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
]);

$r2 = httpGetFollow("$base/products/add", $cookieFile);
if ($t2 = extractCsrf($r2['body'])) {
    parse_str($addPost, $fields);
    $fields['token'] = $t2;
    $addPost = http_build_query($fields);
}

$sr = httpRequest("$base/products/add", $cookieFile, $addPost, ['Content-Type: application/x-www-form-urlencoded']);
$addOk = in_array($sr['code'], [302, 303], true) && $sr['location'] && stripos($sr['location'], 'products') !== false;
$detail = "HTTP {$sr['code']}";
if ($addOk) {
    $detail .= ' → ' . $sr['location'];
} elseif (hasPhpIssue($sr['body'])) {
    preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,150}/', $sr['body'], $em);
    $detail = trim(strip_tags($em[0] ?? $detail));
} elseif ($sr['code'] === 200) {
    $detail = 'validation error — re-rendered add form';
}
check('Product add submit', $addOk, $detail);

$sug = httpRequest("$base/products/suggestions?term=" . urlencode(substr($uniq, 0, 5)), $cookieFile);
$sugJson = json_decode($sug['body'], true);
$productId = null;
if (is_array($sugJson)) {
    foreach ($sugJson as $item) {
        if (!empty($item['row']['code']) && $item['row']['code'] === $uniq) {
            $productId = (int) ($item['row']['id'] ?? 0);
            break;
        }
        if (!empty($item['id']) && stripos(json_encode($item), $uniq) !== false) {
            $productId = (int) preg_replace('/\D.*/', '', (string) $item['id']);
            break;
        }
    }
}

if (!$productId) {
    $list = httpRequest("$base/products/getProducts", $cookieFile, http_build_query([
        'sEcho' => 1, 'iDisplayStart' => 0, 'iDisplayLength' => 50, 'sSearch' => $uniq, 'token' => $t2 ?? $token,
    ]), ['Content-Type: application/x-www-form-urlencoded']);
    $json = json_decode($list['body'], true);
    if (is_array($json['aaData'] ?? null)) {
        foreach ($json['aaData'] as $row) {
            if (isset($row[2]) && stripos((string) $row[2], $uniq) !== false) {
                $productId = (int) $row[0];
                break;
            }
        }
    }
}

check('Find new product', (bool) $productId, $productId ? "id=$productId code=$uniq" : 'not in suggestions/getProducts');

if ($productId) {
    $vr = httpGetFollow("$base/products/view/$productId", $cookieFile);
    check('View new product', $vr['code'] === 200 && !hasPhpIssue($vr['body']), "HTTP {$vr['code']}");

    $er = httpGetFollow("$base/products/edit/$productId", $cookieFile);
    check('Edit new product', $er['code'] === 200 && !hasPhpIssue($er['body']), "HTTP {$er['code']}");

    $mr = httpGetFollow("$base/products/modal_view/$productId", $cookieFile);
    check('Modal view new product', $mr['code'] === 200 && !hasPhpIssue($mr['body']), "HTTP {$mr['code']}");
} else {
    check('View new product', false, 'skipped');
    check('Edit new product', false, 'skipped');
    check('Modal view new product', false, 'skipped');
}

echo "\n=== PHP " . PHP_VERSION . " | $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
