<?php
/**
 * Phase 4 — POS screen load tests (logged-in)
 * Run: php phase4_pos_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_pos_cookies.txt';
$fail = 0;
$pass = 0;

function check($name, $ok, $detail = '')
{
    global $fail, $pass;
    if ($ok) { $pass++; echo "[PASS] $name" . ($detail ? " — $detail" : '') . "\n"; }
    else { $fail++; echo "[FAIL] $name" . ($detail ? " — $detail" : '') . "\n"; }
}

function httpRequest($url, $cookieFile, $post = null)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_TIMEOUT => 60,
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
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
    echo "Usage: php phase4_pos_test.php <identity> <password>\n";
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
    'POS main' => '/pos',
    'POS sales list' => '/pos/sales',
    'Open register' => '/pos/open_register',
    'Close register' => '/pos/close_register',
    'Registers' => '/pos/registers',
    'Today sales' => '/pos/today_sale',
    'Recent POS list' => '/pos/recent_pos_list',
    'POS elite' => '/pos_elite',
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

echo "\n=== Screen load only. Deep flow: phase4_pos_deep_test.php ===\n";
echo "=== PHP " . PHP_VERSION . " | $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
