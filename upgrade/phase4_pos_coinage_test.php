<?php
/**
 * POS coinage finalize — AJAX smoke (get_denominations + saveDenominations guards)
 * Run: php phase4_pos_coinage_test.php Admin "Admin@554"
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
list($identity, $password) = phase4_credentials();
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_pos_coinage_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_pos_coinage_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

function pos_coinage_req($url, $cookieFile, $post = null)
{
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_TIMEOUT => 60,
    ];
    if ($post !== null) {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = is_array($post) ? http_build_query($post) : $post;
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return [$code, $body];
}

list($code, $body) = pos_coinage_req($base . '/pos/get_denominations', $cookieFile);
$data = json_decode($body, true);
$ok = ($code === 200 && is_array($data));
phase4_check('get_denominations JSON array', $ok);
if ($ok && count($data) > 0) {
    phase4_check('denomination has currency_value', isset($data[0]['currency_value']));
}

list($code2, $body2) = pos_coinage_req($base . '/pos/saveDenominations', $cookieFile, [
    'CollectedDenomination' => 'null',
    'ReturnsDwnominations' => 'null',
    'coinage_return_mode' => '0',
]);
phase4_check('saveDenominations null JSON no 500', ($code2 >= 200 && $code2 < 500));

$col = json_encode(['Bills' => ['10.00' => 1], 'Coins' => ['1.00' => 2]]);
list($code3, $body3) = pos_coinage_req($base . '/pos/saveDenominations', $cookieFile, [
    'CollectedDenomination' => $col,
    'ReturnsDwnominations' => json_encode([]),
    'coinage_return_mode' => '0',
]);
phase4_check('saveDenominations valid JSON no 500', ($code3 >= 200 && $code3 < 500));

echo "\n=== POS coinage AJAX | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
