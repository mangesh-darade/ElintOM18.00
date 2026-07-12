<?php
/**
 * Phase 4 — Other Modules deep-link tests (Owner)
 * Run: php phase4_other_modules_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_other_modules_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$ajaxHdr = ['X-Requested-With: XMLHttpRequest'];

if (!$identity || !$password) {
    echo "Usage: php phase4_other_modules_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$employeeId = null;
$saleId = null;
$orderId = null;
$notificationId = null;
$categoryId = null;
try {
    $m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
    if ($m && !$m->connect_error) {
        $q = $m->query("SELECT id FROM sma_employee ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $employeeId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT id FROM sma_sales ORDER BY id DESC LIMIT 1");
        if ($q && $q->num_rows) {
            $saleId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT id FROM sma_orders ORDER BY id DESC LIMIT 1");
        if ($q && $q->num_rows) {
            $orderId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT id FROM sma_notifications ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $notificationId = (int) $q->fetch_assoc()['id'];
        }
        $q = $m->query("SELECT id FROM sma_categories WHERE is_active=1 ORDER BY id ASC LIMIT 1");
        if ($q && $q->num_rows) {
            $categoryId = (int) $q->fetch_assoc()['id'];
        }
    }
} catch (Throwable $e) {
    // optional DB discovery
}

phase4_linkGet($base, 'Employees add', '/employees/add', $cookieFile, [200], $identity, $password);
if ($employeeId) {
    phase4_linkGet($base, 'Employees edit', "/employees/edit/$employeeId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('Employees edit', true, 'skipped — no employee id');
}

phase4_linkGet($base, 'Orders add', '/orders/add', $cookieFile, [200], $identity, $password);
if ($saleId) {
    foreach ([
        'view' => "/orders/view/$saleId",
        'modal_view' => "/orders/modal_view/$saleId",
        'pdf' => "/orders/pdf/$saleId",
    ] as $label => $path) {
        $codes = ($label === 'pdf') ? [200, 302] : [200];
        phase4_linkGet($base, "Orders sale $label", $path, $cookieFile, $codes, $identity, $password);
    }
} else {
    phase4_check('Orders sale deep links', true, 'skipped — no sale id');
}

if ($orderId) {
    foreach ([
        'view_order' => "/orders/view_order/$orderId",
        'modal_view_order' => "/orders/modal_view_order/$orderId",
        'modal_view_eshop_order' => "/orders/modal_view_eshop_order/$orderId",
    ] as $label => $path) {
        phase4_linkGet($base, "Orders $label", $path, $cookieFile, [200], $identity, $password);
    }
} else {
    phase4_check('Orders order deep links', true, 'skipped — no order id');
}

phase4_linkGet($base, 'Orders missing sale view', '/orders/modal_view/0', $cookieFile, [200], $identity, $password);
phase4_linkGet($base, 'Orders missing order view', '/orders/modal_view_order/0', $cookieFile, [200], $identity, $password);

phase4_linkGet($base, 'Notifications add', '/notifications/add', $cookieFile, [200], $identity, $password);
if ($notificationId) {
    phase4_linkGet($base, 'Notifications edit', "/notifications/edit/$notificationId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('Notifications edit', true, 'skipped — no notification id');
}

if ($saleId) {
    phase4_linkGet($base, 'Offline view sale', "/offline/view/$saleId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('Offline view', true, 'skipped — no sale id');
}

phase4_linkGet($base, 'Sales mobile filter page', '/sales_mobile/all_sale_lists', $cookieFile, [200], $identity, $password);
$r = phase4_httpGetFollow("$base/sales_mobile/all_sale_lists", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/sales_mobile/all_sale_lists_filter", $cookieFile, phase4_dtPost($token), $ajaxHdr);
    phase4_check('Sales mobile filter AJAX', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('Sales mobile filter AJAX', true, 'skipped — no CSRF token');
}
phase4_linkGet($base, 'Production mobile inventory', '/Production_Unit_Mobile/inventory', $cookieFile, [200], $identity, $password);

if ($categoryId) {
    $r = phase4_httpGetFollow("$base/recipies/GetProductsbyCategoriesID?categoriesId=$categoryId", $cookieFile);
    phase4_check('Recipies GetProductsbyCategoriesID', $r['code'] === 200 && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('Recipies GetProductsbyCategoriesID', true, 'skipped — no category id');
}

$r = phase4_httpGetFollow("$base/sync", $cookieFile);
$token = phase4_extractCsrf($r['body']);
if ($token) {
    $r = phase4_httpRequest("$base/sync/import_billers", $cookieFile, http_build_query(['token' => $token]), $ajaxHdr);
    phase4_check('Sync import_billers', in_array($r['code'], [200, 302], true) && !phase4_hasPhpIssue($r['body']), 'HTTP ' . $r['code']);
} else {
    phase4_check('Sync import_billers', true, 'skipped — no CSRF token');
}

if ($saleId) {
    phase4_linkGet($base, 'Payswiff app invoice', "/payswiff/payswiff_app_invoice/$saleId", $cookieFile, [200, 302], $identity, $password);
} else {
    phase4_check('Payswiff app invoice', true, 'skipped — no sale id');
}

echo "\n=== Other Modules deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
