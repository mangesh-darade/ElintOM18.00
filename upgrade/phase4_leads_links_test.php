<?php
/**
 * Phase 4 — Leads deep-link tests (Owner)
 * Run: php phase4_leads_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_leads_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/leads';

if (!$identity || !$password) {
    echo "Usage: php phase4_leads_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$leadId = null;
$dealId = null;

$r = phase4_httpGetFollow("$base/Leads/index", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/Leads/get_leads", $cookieFile, phase4_dtPost($token), ['X-Requested-With: XMLHttpRequest']);
    $leadId = phase4_firstDtId($r['body']);
}

try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        if (!$leadId) {
            $q = $m->query("SELECT id FROM sma_leads WHERE is_delete='0' ORDER BY id DESC LIMIT 1");
            if ($q && $q->num_rows) {
                $leadId = (int) $q->fetch_assoc()['id'];
            }
        }
        $q = $m->query("SELECT id FROM deals WHERE is_delete='0' ORDER BY id DESC LIMIT 1");
        if ($q && $q->num_rows) {
            $dealId = (int) $q->fetch_assoc()['id'];
        }
    }
} catch (Throwable $e) {
    // optional DB discovery
}

$links = [];
if ($leadId) {
    $links['edit'] = "$prefix/edit/$leadId";
    $links['lead_history'] = "$prefix/lead_history/$leadId";
    $links['listDeals'] = "$prefix/listDeals/$leadId";
    $links['add_deals'] = "$prefix/add_deals/$leadId";
}
if ($dealId) {
    $links['edit_deals'] = "$prefix/edit_deals/$dealId";
}

foreach ($links as $label => $path) {
    phase4_linkGet($base, "LEAD $label", $path, $cookieFile, [200], $identity, $password);
}

if ($leadId && $token) {
    $r = phase4_httpRequest("$base/Leads/getHistory/$leadId", $cookieFile, phase4_dtPost($token), ['X-Requested-With: XMLHttpRequest']);
    phase4_check('LEAD getHistory AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
    $r = phase4_httpRequest("$base/Leads/getDeals/$leadId", $cookieFile, phase4_dtPost($token), ['X-Requested-With: XMLHttpRequest']);
    phase4_check('LEAD getDeals AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
}

if (!$leadId) {
    phase4_check('LEAD id discovery', true, 'skipped — no lead rows');
}
if (!$dealId) {
    phase4_check('LEAD deal id discovery', true, 'skipped — no deal rows');
}

echo "\n=== Leads deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
