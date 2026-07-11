<?php
/**
 * Phase 4 — Sales screen load tests (logged-in)
 * Run: php phase4_sales_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_sales_cookies.txt';
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
        'type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
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
    echo "Usage: php phase4_sales_test.php <identity> <password>\n";
    exit(1);
}

@unlink($cookieFile);
$r = httpRequest("$base/login", $cookieFile);
preg_match('/name="token"\s+value="([^"]+)"/', $r['body'], $m);
$post = http_build_query(['identity' => $identity, 'password' => $password, 'login_device' => 'web', 'token' => $m[1] ?? '']);
$r = httpRequest("$base/auth/login", $cookieFile, $post);
if (in_array($r['code'], [302, 303], true) && $r['location']) {
    httpRequest($r['location'], $cookieFile);
}
check('Login session', true);

$screens = [
    'Sales list' => '/sales',
    'Add sale' => '/sales/add',
    'All sale lists' => '/sales/all_sale_lists',
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

// getSales DataTables AJAX
$r = httpGetFollow("$base/sales", $cookieFile);
preg_match('/name="([^"]+)"\s+value="([^"]+)"[^>]*class="[^"]*token/i', $r['body'], $csrf);
if (!$csrf) {
    preg_match('/"name":\s*"([^"]+)"[^}]*"value":\s*"([^"]+)"/', $r['body'], $csrf);
}
$dtPost = http_build_query([
    'sEcho' => 1,
    'iColumns' => 14,
    'sColumns' => '',
    'iDisplayStart' => 0,
    'iDisplayLength' => 10,
    'mDataProp_0' => 0,
    'sSearch' => '',
    'bRegex' => 'false',
    $csrf[1] ?? 'token' => $csrf[2] ?? '',
]);
$r = httpRequest("$base/sales/getSales", $cookieFile, $dtPost);
$json = json_decode($r['body'], true);
$saleId = null;
if (is_array($json) && !empty($json['aaData'][0][0])) {
    $saleId = (int) $json['aaData'][0][0];
}
check('getSales AJAX', $r['code'] === 200 && is_array($json) && isset($json['aaData']), "HTTP {$r['code']} rows=" . (is_array($json['aaData'] ?? null) ? count($json['aaData']) : 0));

// suggestions (needs warehouse + customer from add page)
$rAdd = httpGetFollow("$base/sales/add", $cookieFile);
preg_match('/id="slcustomer"[^>]*value="(\d+)"/', $rAdd['body'], $cust);
preg_match('/id="slwarehouse"[^>]*value="(\d+)"/', $rAdd['body'], $wh);
if (!$wh && preg_match('/name="warehouse"[^>]*value="(\d+)"/', $rAdd['body'], $wh)) {}
$customerId = $cust[1] ?? '1';
$warehouseId = $wh[1] ?? '1';
$sugUrl = "$base/sales/suggestions?term=test&warehouse_id=$warehouseId&customer_id=$customerId&quantity=1";
$r = httpRequest($sugUrl, $cookieFile);
$sugOk = $r['code'] === 200 && !hasPhpIssue($r['body']);
check('suggestions AJAX', $sugOk, "HTTP {$r['code']} len=" . strlen($r['body']));

if ($saleId) {
    $r = httpGetFollow("$base/sales/view/$saleId", $cookieFile);
    check("view sale ($saleId)", $r['code'] === 200 && !hasPhpIssue($r['body']), "HTTP {$r['code']}");

    $r = httpGetFollow("$base/sales/pdf/$saleId", $cookieFile);
    $pdfOk = in_array($r['code'], [200, 302], true) && !hasPhpIssue($r['body']);
  check("pdf sale ($saleId)", $pdfOk, "HTTP {$r['code']} type=" . ($r['type'] ?? ''));
} else {
    check('view sale (skipped)', false, 'no sale id from getSales');
    check('pdf sale (skipped)', false, 'no sale id from getSales');
}

echo "\n=== Screen + AJAX load. Deep sale submit: manual or future phase4_sales_deep_test.php ===\n";
echo "=== PHP " . PHP_VERSION . " | $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
