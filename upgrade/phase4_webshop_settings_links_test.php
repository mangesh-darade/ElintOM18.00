<?php
/**
 * Phase 4 — Webshop Settings deep-link tests (Owner)
 * Run: php phase4_webshop_settings_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_webshop_settings_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/webshop_settings';

if (!$identity || !$password) {
    echo "Usage: php phase4_webshop_settings_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$categoryId = null;
$pageKey = null;
$pageHash = null;
$sectionName = null;
$homePage = 'theme_1';

try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT id FROM sma_categories WHERE is_active=1 ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $categoryId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT page_key, id FROM sma_webshop_static_pages ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $row = $q->fetch_assoc();
            $pageKey = $row['page_key'];
            $pageHash = md5((string) $row['id']);
        }
        $q = $m->query("SELECT home_page FROM sma_webshop_settings LIMIT 1");
        if ($q && $q->num_rows) {
            $homePage = $q->fetch_assoc()['home_page'] ?: 'theme_1';
        }
        $col = preg_replace('/[^a-z0-9_]/i', '', $homePage);
        $q = $m->query("SELECT section_name FROM sma_webshop_homepage_sections WHERE is_active=1 AND `$col`=1 AND display_status=1 ORDER BY display_order ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $sectionName = $q->fetch_assoc()['section_name'];
        }
    }
} catch (Throwable $e) {
    // optional DB discovery
}

if ($categoryId) {
    phase4_linkGet($base, 'WS manage_products category', "$prefix/manage_products/$categoryId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('WS manage_products category', true, 'skipped — no category id');
}

if ($pageKey && $pageHash) {
    phase4_linkGet($base, 'WS edit custom page', "$prefix/edit_custom_pages/$pageKey/$pageHash", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('WS edit custom page', true, 'skipped — no static page');
}

if ($sectionName) {
    phase4_linkGet($base, 'WS elements section', "$prefix/elements/$sectionName", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('WS elements section', true, 'skipped — no active homepage section');
}

$r = phase4_httpGetFollow("$base$prefix", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base$prefix/webshop_ajax_request", $cookieFile, http_build_query([
        'action' => 'manage_eshop_category',
        'token' => $token,
        'category_id' => $categoryId ?: 1,
        'in_eshop' => 1,
    ]), ['X-Requested-With: XMLHttpRequest']);
    phase4_check('webshop_ajax_request', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('webshop_ajax_request', true, 'skipped — no CSRF token');
}

echo "\n=== Webshop Settings deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
