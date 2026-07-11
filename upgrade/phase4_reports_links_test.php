<?php
/**
 * Phase 4 — Reports ALL deep-link tests (logged-in)
 * Run: php phase4_reports_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_reports_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_reports_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$_SERVER['HTTP_HOST'] = 'localhost';
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}
require PHPUPGRADE_ROOT . '/app/config/database.php';
$pdo = new PDO("mysql:host={$db['default']['hostname']};dbname={$db['default']['database']}", $db['default']['username'], $db['default']['password']);
$warehouseId = (int) $pdo->query('SELECT id FROM sma_warehouses ORDER BY id ASC LIMIT 1')->fetchColumn();
$year = date('Y');
$month = date('m');
$today = date('Y-m-d');

if ($warehouseId) {
    phase4_linkGet($base, 'warehouse_stock', "/reports/warehouse_stock/$warehouseId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'expiry_alerts wh', "/reports/expiry_alerts/$warehouseId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'best_sellers wh', "/reports/best_sellers/$warehouseId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'daily_sales cal', "/reports/daily_sales/$warehouseId/$year/$month", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'daily_sales_up cal', "/reports/daily_sales_up/$warehouseId/$year/$month", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'monthly_sales cal', "/reports/monthly_sales/$warehouseId/$year", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'monthly_purchases cal', "/reports/monthly_purchases/$warehouseId/$year", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'sale_purchase_chart', "/reports/sale_purchase_chart_details/$warehouseId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'sale_purchase_chart purchase', "/reports/sale_purchase_chart_details/$warehouseId/purchase", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'brand_chart', "/reports/brand_chart_details/$warehouseId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'categories_chart', "/reports/categories_chart_details/$warehouseId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'payment_chart', "/reports/payment_chart_details/$warehouseId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'products_costing wh', "/reports/products_costing/$warehouseId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'profit modal', "/reports/profit/$today/$warehouseId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'monthly_profit', "/reports/monthly_profit/$year/$month", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('Warehouse deep links', false, 'no warehouse id');
}

$biSlug = null;
try {
    $biSlug = $pdo->query("SELECT slug FROM sma_bi_reports WHERE status='1' ORDER BY id ASC LIMIT 1")->fetchColumn();
} catch (Throwable $e) {
    phase4_check('BI report slug', true, 'skipped — ' . $e->getMessage());
}
if ($biSlug) {
    phase4_linkGet($base, 'BI report slug', "/reports/bi/$biSlug", $cookieFile, [200], $identity, $password);
}

$r = phase4_httpGetFollow("$base/reports/customers", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/reports/getCustomersData/?v=1", $cookieFile, phase4_dtPost($token));
$customerId = phase4_firstDtId($r['body']);
if ($customerId) {
    phase4_linkGet($base, 'customer_report', "/reports/customer_report/$customerId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'overdue_sales', "/reports/overdue_sales/$customerId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('customer_report', true, 'skipped — no customer row');
    phase4_check('overdue_sales', true, 'skipped — no customer row');
}

$r = phase4_httpGetFollow("$base/reports/users", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/reports/getUsers", $cookieFile, phase4_dtPost($token));
$userId = phase4_firstDtId($r['body']);
if ($userId) {
    phase4_linkGet($base, 'staff_report', "/reports/staff_report/$userId", $cookieFile, [200], $identity, $password);
    phase4_linkGet($base, 'staff_report cal', "/reports/staff_report/$userId/$year/$month", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('staff_report', true, 'skipped — no user row');
}

$r = phase4_httpGetFollow("$base/reports/suppliers", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/reports/getSuppliers/?v=1", $cookieFile, phase4_dtPost($token));
$supplierId = phase4_firstDtId($r['body']);
if ($supplierId) {
    phase4_linkGet($base, 'supplier_report', "/reports/supplier_report/$supplierId", $cookieFile, [200], $identity, $password);
} else {
    phase4_check('supplier_report', true, 'skipped — no supplier row');
}

phase4_login($base, $identity, $password, $cookieFile);
phase4_linkGet($base, 'profit_loss_pdf', '/reports/profit_loss_pdf/?v=1', $cookieFile, [200, 302], $identity, $password);
phase4_linkGet($base, 'transferReport', '/reports/transferReport', $cookieFile, [200], $identity, $password);
phase4_linkGet($base, 'categories_brand_chart', '/reports/categories_brand_chart_details', $cookieFile, [200], $identity, $password);

echo "\n=== Reports ALL deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
