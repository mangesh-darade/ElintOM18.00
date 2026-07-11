<?php
/**
 * Phase 3 smoke tests — run: php phase3_smoke_test.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
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

define('PHPUPGRADE_ROOT', dirname(__DIR__));
chdir(PHPUPGRADE_ROOT);

// --- Baseline (separate process) ---
$baselinePhp = 'c:\\wamp64\\bin\\php\\php8.5.0\\php.exe';
$baseline = shell_exec('"' . $baselinePhp . '" "' . __DIR__ . '/baseline_test.php" 2>&1');
check('Baseline bootstrap', strpos($baseline, 'Errors captured: 0') !== false);

// MPDF
try {
    require PHPUPGRADE_ROOT . '/app/third_party/MPDF/mpdf.php';
    $pdf = new mPDF('utf-8', 'A4-P');
    $pdf->WriteHTML('<h1>Test</h1>');
    $bytes = $pdf->Output('', 'S');
    check('MPDF generate', strlen($bytes) > 1000, strlen($bytes) . ' bytes');
} catch (Throwable $t) {
    check('MPDF generate', false, $t->getMessage());
}

// Stripe
try {
    require PHPUPGRADE_ROOT . '/app/third_party/autoload.php';
    check('Stripe SDK', class_exists('Stripe\Stripe'), \Stripe\Stripe::VERSION);
} catch (Throwable $t) {
    check('Stripe SDK', false, $t->getMessage());
}

// PHPExcel
try {
    require PHPUPGRADE_ROOT . '/app/third_party/PHPExcel/PHPExcel.php';
    $xl = new PHPExcel();
    check('PHPExcel load', $xl instanceof PHPExcel);
} catch (Throwable $t) {
    check('PHPExcel load', false, $t->getMessage());
}

// phpqrcode
try {
    require PHPUPGRADE_ROOT . '/app/third_party/phpqrcode/qrlib.php';
    check('phpqrcode load', class_exists('QRcode'));
} catch (Throwable $t) {
    check('phpqrcode load', false, $t->getMessage());
}

// Encrypt roundtrip
try {
    if (!defined('BASEPATH')) {
        define('BASEPATH', PHPUPGRADE_ROOT . '/');
    }
    if (!function_exists('get_instance')) {
        function get_instance() { return null; }
    }
    if (!function_exists('log_message')) {
        function log_message($a, $b) {}
    }
    if (!function_exists('show_error')) {
        function show_error($m) { throw new Exception($m); }
    }
    if (!function_exists('config_item')) {
        function config_item($k) { return 'testkey123456789012345678901234'; }
    }
    require PHPUPGRADE_ROOT . '/app/libraries/Encrypt.php';
    $enc = new CI_Encrypt();
    $enc->set_key('testkey123456789012345678901234');
    $plain = 'smtp_test_password';
    $encoded = $enc->encode($plain);
    $decoded = $enc->decode($encoded);
    check('Encrypt roundtrip', $decoded === $plain);
} catch (Throwable $t) {
    check('Encrypt roundtrip', false, $t->getMessage());
}

// crypto_helper
try {
    require PHPUPGRADE_ROOT . '/app/helpers/crypto_helper.php';
    $key = 'working_key_test';
    $text = 'merchant_id=1&amount=100';
    $cipher = encrypt($text, $key);
    $plain = decrypt($cipher, $key);
    check('crypto_helper roundtrip', $plain === $text);
} catch (Throwable $t) {
    check('crypto_helper roundtrip', false, $t->getMessage());
}

// Facebook SDK
try {
    require PHPUPGRADE_ROOT . '/app/third_party/facebook-php-sdk/autoload.php';
    check('Facebook SDK load', class_exists('Facebook\Facebook'));
} catch (Throwable $t) {
    check('Facebook SDK load', false, $t->getMessage());
}

// HTTP endpoints
$endpoints = [
    'home' => 'http://localhost/ElintOM18.00/',
    'login' => 'http://localhost/ElintOM18.00/login',
    'auth_login' => 'http://localhost/ElintOM18.00/auth/login',
    'forgot_password' => 'http://localhost/ElintOM18.00/forgot_password',
    'welcome' => 'http://localhost/ElintOM18.00/welcome',
    'pos' => 'http://localhost/ElintOM18.00/pos',
];
foreach ($endpoints as $name => $url) {
    $ctx = stream_context_create(['http' => ['timeout' => 15, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    $fatal = $body && preg_match('/Fatal error|Parse error|Uncaught (Error|TypeError)/', $body);
    $ok = $body !== false && !$fatal && strlen($body) > 50;
    check("HTTP $name", $ok, $body ? strlen($body) . ' bytes' : 'no response');
}

echo "\n=== PHP " . PHP_VERSION . " | $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
