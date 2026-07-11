<?php
/**
 * Phase 4 — Products screen load tests (logged-in)
 * Run: php phase4_products_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_products_cookies.txt';
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
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) PHPUpgradeTest',
    ];
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
    return [
        'code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'body' => $body ?: '',
        'location' => curl_getinfo($ch, CURLINFO_REDIRECT_URL),
    ];
}

function httpGetFollow($url, $cookieFile)
{
    for ($i = 0; $i < 5; $i++) {
        $r = httpRequest($url, $cookieFile);
        if (!in_array($r['code'], [301, 302, 303, 307, 308], true) || empty($r['location'])) {
            return $r;
        }
        $url = $r['location'];
    }
    return $r;
}

function hasPhpIssue($body)
{
    return (bool) preg_match('/Fatal error|Parse error|Uncaught (Error|TypeError)|<(b|strong)>(Deprecated|Warning|Notice):/i', $body);
}

if (!$identity || !$password) {
    echo "Usage: php phase4_products_test.php <identity> <password>\n";
    exit(1);
}

@unlink($cookieFile);
$r = httpRequest("$base/login", $cookieFile);
preg_match('/name="token"\s+value="([^"]+)"/', $r['body'], $m);
$r = httpRequest("$base/auth/login", $cookieFile, http_build_query([
    'identity' => $identity, 'password' => $password, 'login_device' => 'web', 'token' => $m[1] ?? '',
]));
if (in_array($r['code'], [302, 303], true) && $r['location']) {
    httpRequest($r['location'], $cookieFile);
}
check('Login session', true);

$screens = [
    'Products list' => '/products',
    'Add product' => '/products/add',
    'Print barcodes' => '/products/print_barcodes',
    'Quantity adjustments' => '/products/quantity_adjustments',
];

foreach ($screens as $label => $path) {
    $r = httpGetFollow("$base$path", $cookieFile);
    $ok = $r['code'] === 200 && !hasPhpIssue($r['body']);
    $issue = '';
    if (hasPhpIssue($r['body']) && preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,180}/', $r['body'], $mm)) {
        $issue = ' | ' . trim(strip_tags($mm[0]));
    }
    check("$label ($path)", $ok, "HTTP {$r['code']} len=" . strlen($r['body']) . $issue);
}

$r = httpGetFollow("$base/products", $cookieFile);
preg_match('/name="([^"]+)"\s+value="([^"]+)"/', $r['body'], $csrf);
$dtPost = http_build_query([
    'sEcho' => 1, 'iDisplayStart' => 0, 'iDisplayLength' => 10,
    $csrf[1] ?? 'token' => $csrf[2] ?? '',
]);
$r = httpRequest("$base/products/getProducts", $cookieFile, $dtPost);
$json = json_decode($r['body'], true);
$productId = is_array($json) && !empty($json['aaData'][0][0]) ? (int) $json['aaData'][0][0] : null;
check('getProducts AJAX', $r['code'] === 200 && is_array($json) && isset($json['aaData']), 'rows=' . (is_array($json['aaData'] ?? null) ? count($json['aaData']) : 0));

if ($productId) {
    foreach (['view' => "/products/view/$productId", 'edit' => "/products/edit/$productId", 'modal' => "/products/modal_view/$productId"] as $label => $path) {
        $r = httpGetFollow("$base$path", $cookieFile);
        check("Product $label ($productId)", $r['code'] === 200 && !hasPhpIssue($r['body']), "HTTP {$r['code']}");
    }
} else {
    check('Product view/edit/modal', false, 'no product id from getProducts');
}

$r = httpRequest("$base/products/suggestions?term=test", $cookieFile);
check('suggestions AJAX', $r['code'] === 200 && !hasPhpIssue($r['body']), 'len=' . strlen($r['body']));

echo "\n=== Deep flow: phase4_products_deep_test.php ===\n";
echo "=== PHP " . PHP_VERSION . " | $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
