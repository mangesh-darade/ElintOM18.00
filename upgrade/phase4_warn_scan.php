<?php
$cf = sys_get_temp_dir() . '/phpupgrade_scan_cookies.txt';
@unlink($cf);
function req($url, $cf, $post = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 0, CURLOPT_COOKIEJAR => $cf, CURLOPT_COOKIEFILE => $cf, CURLOPT_TIMEOUT => 60]);
    if ($post) { curl_setopt($ch, CURLOPT_POST, 1); curl_setopt($ch, CURLOPT_POSTFIELDS, $post); }
    $b = curl_exec($ch);
    return [(int)curl_getinfo($ch, CURLINFO_HTTP_CODE), curl_getinfo($ch, CURLINFO_REDIRECT_URL), $b];
}
$base = 'http://localhost/phpupgrade';
$r = req("$base/login", $cf);
preg_match('/name="token"\s+value="([^"]+)"/', $r[2], $m);
$post = http_build_query(['identity' => 'Admin', 'password' => 'Admin@554', 'login_device' => 'web', 'token' => $m[1]]);
$r = req("$base/auth/login", $cf, $post);
if ($r[0] >= 300 && $r[1]) req($r[1], $cf);
foreach (['/welcome', '/pos', '/auth/users', '/auth/profile/1'] as $p) {
    $r = req("$base$p", $cf);
    $body = $r[2];
    $issues = [];
    if (preg_match_all('/<(b|strong)>(Deprecated|Warning|Notice|Fatal error)[^<]*/i', $body, $mm)) {
        $issues = array_slice(array_unique($mm[0]), 0, 3);
    }
    echo "$p HTTP {$r[0]} issues=" . count($issues) . "\n";
    foreach ($issues as $i) echo "  " . strip_tags($i) . "\n";
}
