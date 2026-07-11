<?php
/**
 * Phase 4 — Auth deep-link tests (logged-in)
 * Run: php phase4_auth_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_auth_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_auth_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/auth/users", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/auth/getUsers", $cookieFile, phase4_dtPost($token));
$userId = phase4_firstDtId($r['body']);
phase4_check('getUsers AJAX', $r['code'] === 200 && $userId !== null, 'user_id=' . ($userId ?? 'none'));

if ($userId) {
    phase4_linkGet($base, 'Profile', "/auth/profile/$userId", $cookieFile);
} else {
    phase4_check('Profile deep link', false, 'no user id from getUsers');
}

// Guest-only routes (new session)
@unlink($cookieFile);
phase4_linkGet($base, 'Forgot password', '/forgot_password', $cookieFile);
phase4_linkGet($base, 'Forgot password mobile', '/auth/forgot_password_mobile', $cookieFile);

echo "\n=== Auth deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
