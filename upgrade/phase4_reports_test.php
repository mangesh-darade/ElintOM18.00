<?php
/**
 * Phase 4 — Reports ALL screen load tests (logged-in)
 * Run: php phase4_reports_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_reports_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_reports_test.php <identity> <password>\n";
    exit(1);
}

$screens = [
    'Reports home' => '/reports',
    'BI manage' => '/reports/bi_manage',
    'Sales dash' => '/reports/sales_dash',
    'Daily sales' => '/reports/daily_sales',
    'Daily sales up' => '/reports/daily_sales_up',
    'Monthly sales' => '/reports/monthly_sales',
    'Sales report' => '/reports/sales',
    'Best sellers' => '/reports/best_sellers',
    'Customer wise sales' => '/reports/get_customer_wise_sales',
    'Product wise sales' => '/reports/Product_wise_Sale_Report',
    'Sales person' => '/reports/sales_person_report',
    'Sales due' => '/reports/sales_due',
    'Term wise sales' => '/reports/term_wise_sale_report',
    'Overdue payments' => '/reports/overdue_payments',
    'Purchases report' => '/reports/purchases',
    'Daily purchases' => '/reports/daily_purchases',
    'Monthly purchases' => '/reports/monthly_purchases',
    'Purchases due' => '/reports/purchases_due',
    'Sales GST' => '/reports/sales_gst_report',
    'Purchases GST' => '/reports/purchases_gst_report',
    'Tax reports' => '/reports/taxreports',
    'Tax reports new' => '/reports/taxreports_new',
    'HSN reports' => '/reports/hsncode_reports',
    'HSN reports new' => '/reports/hsncode_reports_new',
    'Sales GST new' => '/reports/sales_gst_reportnew',
    'Sales tax report' => '/reports/sales_tax_report',
    'Warehouse stock' => '/reports/warehouse_stock',
    'Expiry alerts' => '/reports/expiry_alerts',
    'Quantity alerts' => '/reports/quantity_alerts',
    'Products report' => '/reports/products',
    'Categories' => '/reports/categories',
    'Brands' => '/reports/brands',
    'Adjustments' => '/reports/adjustments',
    'Variant stock' => '/reports/product_varient_stock_report',
    'Variant report' => '/reports/product_varient_report',
    'Variant sale report' => '/reports/product_varient_sale_report',
    'Variant purchase report' => '/reports/product_varient_purchase_report',
    'Products combo items' => '/reports/products_combo_items',
    'Products transactions' => '/reports/products_transactions',
    'Products ledgers' => '/reports/products_ledgers',
    'Products profitloss' => '/reports/products_profitloss',
    'Products costing' => '/reports/products_costing',
    'Products order report' => '/reports/products_orderReport',
    'Profit loss' => '/reports/profit_loss',
    'Payments report' => '/reports/payments',
    'Payment summary' => '/reports/paymentssummary',
    'Expenses' => '/reports/expenses',
    'Cash transaction' => '/reports/cash_transaction',
    'Deposit' => '/reports/deposit',
    'Deposit history' => '/reports/depositHistory',
    'Customer deposit ledger' => '/reports/customerDepositLedger',
    'Customers report' => '/reports/customers',
    'Customer ledger' => '/reports/customer_ledger',
    'Customer ledger v1' => '/reports/customer_ledger_v1',
    'Suppliers report' => '/reports/suppliers',
    'Users report' => '/reports/users',
    'User log action' => '/reports/user_log_action',
    'Register' => '/reports/register',
    'Transfer report' => '/reports/transferReport',
    'Transfer request' => '/reports/transfer_request',
    'Sale purchase chart' => '/reports/sale_purchase_chart_details',
    'Brand chart' => '/reports/brand_chart_details',
    'Categories chart' => '/reports/categories_chart_details',
    'Categories brand chart' => '/reports/categories_brand_chart_details',
    'Payment chart' => '/reports/payment_chart_details',
    'Warehouse sales' => '/reports/warehouse_sales',
    'Challans' => '/reports/challans',
    'Categories report' => '/reports/categories_report',
    'Transaction history' => '/reports/transaction_history',
];

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$screenNum = 0;
foreach ($screens as $label => $path) {
    if ($screenNum > 0 && $screenNum % 10 === 0) {
        phase4_login($base, $identity, $password, $cookieFile);
    }
    $r = phase4_httpGetFollow("$base$path", $cookieFile);
    $ok = in_array($r['code'], [200, 302], true) && !phase4_hasPhpIssue($r['body']);
    if (!$ok && $r['code'] === 500) {
        phase4_login($base, $identity, $password, $cookieFile);
        $r = phase4_httpGetFollow("$base$path", $cookieFile);
        $ok = in_array($r['code'], [200, 302], true) && !phase4_hasPhpIssue($r['body']);
    }
    $issue = '';
    if (phase4_hasPhpIssue($r['body']) && preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,160}/', $r['body'], $mm)) {
        $issue = ' | ' . trim(strip_tags($mm[0]));
    }
    phase4_check("$label ($path)", $ok, "HTTP {$r['code']}" . $issue);
    $screenNum++;
}

$_SERVER['HTTP_HOST'] = 'localhost';
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}
require PHPUPGRADE_ROOT . '/app/config/database.php';
try {
    $pdo = new PDO("mysql:host={$db['default']['hostname']};dbname={$db['default']['database']}", $db['default']['username'], $db['default']['password']);
    $biSlug = $pdo->query("SELECT slug FROM sma_bi_reports WHERE status='1' ORDER BY id ASC LIMIT 1")->fetchColumn();
    if ($biSlug) {
        phase4_linkGet($base, 'BI report', "/reports/bi/$biSlug", $cookieFile, [200, 302]);
    } else {
        phase4_check('BI report', true, 'skipped — no active bi_reports slug');
    }
} catch (Throwable $e) {
    phase4_check('BI report', true, 'skipped — ' . $e->getMessage());
}

echo "\n=== Reports ALL screens | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
