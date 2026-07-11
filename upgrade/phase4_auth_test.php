<?php
/**
 * Phase 4 — logged-in Auth + Welcome screen tests
 * Run: php phase4_auth_test.php [identity] [password]
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? 'Admin';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_phase4_cookies.txt';

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
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $location = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    return ['code' => $code, 'body' => $body ?: '', 'location' => $location];
}

function httpGetFollow($url, $cookieFile, $max = 5)
{
    $last = ['code' => 0, 'body' => '', 'location' => ''];
    for ($i = 0; $i < $max; $i++) {
        $last = httpRequest($url, $cookieFile);
        if (!in_array($last['code'], [301, 302, 303, 307, 308], true) || empty($last['location'])) {
            return $last;
        }
        $url = $last['location'];
    }
    return $last;
}

function hasPhpIssue($body)
{
    return (bool) preg_match('/Fatal error|Parse error|Uncaught (Error|TypeError)|<(b|strong)>(Deprecated|Warning|Notice):/i', $body);
}

function extractCsrf($body)
{
    if (preg_match('/name="token"\s+value="([^"]+)"/', $body, $m)) {
        return $m[1];
    }
    return null;
}

@unlink($cookieFile);

// 1) GET login page
$r = httpRequest("$base/login", $cookieFile);
check('GET login page', $r['code'] === 200 && strlen($r['body']) > 1000, "HTTP {$r['code']}");
$token = extractCsrf($r['body']);
check('CSRF token on login', !empty($token));

if (empty($password)) {
    echo "\nUsage: php phase4_auth_test.php <identity> <password>\n";
    exit(1);
}

// 2) POST login
$post = http_build_query([
    'identity' => $identity,
    'password' => $password,
    'login_device' => 'web',
    'token' => $token,
]);
$r = httpRequest("$base/auth/login", $cookieFile, $post, ['Content-Type: application/x-www-form-urlencoded']);
$loginOk = in_array($r['code'], [200, 302, 303], true) && !hasPhpIssue($r['body']);
check('POST login', $loginOk, "HTTP {$r['code']}" . ($r['location'] ? " → {$r['location']}" : ''));

// Follow redirect after login
if (in_array($r['code'], [302, 303], true) && $r['location']) {
    $r = httpRequest($r['location'], $cookieFile);
}

$screens = [
    'Dashboard' => '/welcome',
    'POS' => '/pos',
    'Auth users' => '/auth/users',
    'Create user' => '/auth/create_user',
    'Profile' => '/auth/profile/1',
    'Forgot password (guest)' => '/forgot_password',
];

// change_password is POST-only; profile hosts the form

foreach ($screens as $label => $path) {
    $r = httpGetFollow("$base$path", $cookieFile);
    $ok = in_array($r['code'], [200, 302], true) && !hasPhpIssue($r['body']);
    $issue = '';
    if (hasPhpIssue($r['body'])) {
        if (preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,200}/', $r['body'], $m)) {
            $issue = trim(strip_tags($m[0]));
        }
    }
    check("$label ($path)", $ok, "HTTP {$r['code']}" . ($issue ? " | $issue" : ''));
}

// Guest forgot password (new session)
@unlink($cookieFile);
$r = httpGetFollow("$base/forgot_password", $cookieFile);
check('Forgot password guest', $r['code'] === 200 && !hasPhpIssue($r['body']), "HTTP {$r['code']}");

echo "\n=== PHP " . PHP_VERSION . " | $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
