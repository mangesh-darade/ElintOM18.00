<?php
/**
 * Phase 4 — Omnichannel deep-link / AJAX tests (Owner)
 * Run: php phase4_omnichannel_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_omnichannel_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$ajaxHdr = ['X-Requested-With: XMLHttpRequest'];

if (!$identity || !$password) {
    echo "Usage: php phase4_omnichannel_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$upSaleId = null;
$webshopSaleId = null;
$holidayId = null;
$scheduleId = null;
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT id FROM sma_sales WHERE up_sales=1 ORDER BY id DESC LIMIT 1");
        if ($q && $q->num_rows) {
            $upSaleId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT id FROM sma_sales WHERE eshop_sale=1 ORDER BY id DESC LIMIT 1");
        if ($q && $q->num_rows) {
            $webshopSaleId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT id FROM sma_holiday ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $holidayId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT id FROM sma_schedule ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $scheduleId = (int) $q->fetch_assoc()['id'];
        }
    }
} catch (Throwable $e) {
    // optional DB discovery
}

$r = phase4_httpRequest("$base/Omnichannel/orders_list_combined", $cookieFile, null, $ajaxHdr);
phase4_check('orders_list_combined AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

$r = phase4_httpRequest("$base/Omnichannel/get_order_status_counts_ajax", $cookieFile, null, $ajaxHdr);
phase4_check('get_order_status_counts_ajax', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);

$r = phase4_httpGetFollow("$base/Omnichannel/view_holidays", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/Omnichannel/get_holidays", $cookieFile, phase4_dtPost($token), $ajaxHdr);
    phase4_check('get_holidays AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('get_holidays AJAX', true, 'skipped — no CSRF token');
}

$r = phase4_httpGetFollow("$base/Omnichannel/view_schdules", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/Omnichannel/get_schdules", $cookieFile, phase4_dtPost($token), $ajaxHdr);
    phase4_check('get_schdules AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('get_schdules AJAX', true, 'skipped — no CSRF token');
}

if ($upSaleId) {
    foreach ([
        'order_details' => "/Omnichannel/order_details/$upSaleId",
        'order_kot' => "/Omnichannel/order_kot/$upSaleId",
    ] as $label => $path) {
        phase4_linkGet($base, "OC UP $label", $path, $cookieFile, [200], $identity, $password);
    }
} else {
    phase4_check('OC UP order deep links', true, 'skipped — no up sale id');
}

if ($webshopSaleId) {
    foreach ([
        'order_detailsforwebshop' => "/Omnichannel/order_detailsforwebshop/$webshopSaleId",
        'order_kot_forwebshop' => "/Omnichannel/order_kot_forwebshop/$webshopSaleId",
    ] as $label => $path) {
        phase4_linkGet($base, "OC webshop $label", $path, $cookieFile, [200], $identity, $password);
    }
} else {
    phase4_check('OC webshop deep links', true, 'skipped — no webshop sale id');
}

if ($holidayId) {
    phase4_linkGet($base, 'edit_holiday', "/Omnichannel/edit_holiday/$holidayId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('edit_holiday', true, 'skipped — no holiday id');
}

if ($scheduleId) {
    phase4_linkGet($base, 'edit_schedule', "/Omnichannel/edit_schedule/$scheduleId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('edit_schedule', true, 'skipped — no schedule id');
}

phase4_linkGet($base, 'orders_inactive', '/Omnichannel/orders_inactive', $cookieFile, [200], $identity, $password);
phase4_linkGet($base, 'order_details missing', '/Omnichannel/order_details/0', $cookieFile, [200], $identity, $password);

echo "\n=== Omnichannel deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
