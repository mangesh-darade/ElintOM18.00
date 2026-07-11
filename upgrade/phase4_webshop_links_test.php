<?php
/**
 * Phase 4 — Webshop deep-link tests
 * Run: php phase4_webshop_links_test.php [identity] [password]
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_webshop_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/webshop';

@unlink($cookieFile);

foreach ([
    'About us' => $prefix . '/about_us',
    'Terms' => $prefix . '/terms_and_conditions',
    'Privacy' => $prefix . '/privacy_policy',
    'Contact us' => $prefix . '/contact_us',
    'Checkout' => $prefix . '/checkout',
    'Wishlist' => $prefix . '/wishlist',
] as $label => $path) {
    phase4_linkGet($base, "Webshop $label", $path, $cookieFile, [200, 302]);
}

$pageHash = null;
$orderId = null;
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SHOW TABLES LIKE 'sma_webshop_custom_pages'");
    if ($q && $q->num_rows) {
        $q2 = $m->query("SELECT page_key, id FROM sma_webshop_custom_pages ORDER BY id ASC LIMIT 1");
        if ($q2 && $q2->num_rows) {
            $row = $q2->fetch_assoc();
            $pageHash = md5((string) $row['id']);
            phase4_linkGet($base, 'Webshop custom page', $prefix . '/page/' . $row['page_key'] . '/' . $pageHash, $cookieFile, [200, 302]);
        }
    }
    $q = $m->query("SELECT id FROM sma_sales WHERE sale_status='completed' ORDER BY id DESC LIMIT 1");
    if ($q && $q->num_rows) {
        $orderId = (int) $q->fetch_assoc()['id'];
    }
    }
} catch (Throwable $e) {
    // DB optional for ID discovery
}

if (!$pageHash) {
    phase4_check('Webshop custom page', true, 'skipped — no custom page');
}

if ($orderId) {
    phase4_linkGet($base, 'Webshop track order', "$prefix/track_order/$orderId", $cookieFile, [200, 302]);
} else {
    phase4_check('Webshop track order', true, 'skipped — no order id');
}

$r = phase4_httpGetFollow("$base$prefix/getpincodecharges?pincode=400001", $cookieFile);
phase4_check('getpincodecharges', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

echo "\n=== Webshop deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
