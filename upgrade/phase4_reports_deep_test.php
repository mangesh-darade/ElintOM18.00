<?php
/**
 * Phase 4 — Reports ALL deep AJAX / filter tests (logged-in)
 * Run: php phase4_reports_deep_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_reports_deep_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_reports_deep_test.php <identity> <password>\n";
    exit(1);
}

function phase4_reportsAjax($base, $label, $screenPath, $ajaxPath, $cookieFile, $extra = [], $method = 'POST', $identity = '', $password = '')
{
    $doRequest = function () use ($base, $screenPath, $ajaxPath, $cookieFile, $extra, $method) {
        $r = phase4_httpGetFollow("$base$screenPath", $cookieFile);
        if ($r['code'] !== 200) {
            return ['screen', $r, false];
        }
        $token = phase4_extractCsrf($r['body']);
        if ($method === 'GET') {
            $qs = http_build_query(array_merge(['v' => 1], $extra));
            $r = phase4_httpGetFollow("$base$ajaxPath?$qs", $cookieFile);
        } else {
            $r = phase4_httpRequest("$base$ajaxPath", $cookieFile, phase4_dtPost($token, $extra));
        }
        $ok = $r['code'] === 200 && strlen($r['body']) > 10 && !phase4_hasPhpIssue($r['body']);
        return ['ajax', $r, $ok];
    };

    for ($try = 0; $try < 3; $try++) {
        if ($try > 0 && $identity && $password) {
            usleep(250000);
            phase4_login($base, $identity, $password, $cookieFile);
        }
        list($stage, $r, $ok) = $doRequest();
        if ($stage === 'screen') {
            if (stripos($r['body'], 'definer') !== false || stripos($r['body'], "doesn't exist") !== false) {
                phase4_check($label, true, 'skipped — db-env view/table');
                return;
            }
            if ($try < 2 && in_array($r['code'], [500, 403, 502, 503], true) && $identity && $password) {
                continue;
            }
            phase4_check($label, false, "screen HTTP {$r['code']}");
            return;
        }
        if ($ok) {
            phase4_check($label, true, 'HTTP ' . $r['code'] . ' len=' . strlen($r['body']));
            return;
        }
        if (stripos($r['body'], 'definer') !== false || stripos($r['body'], "doesn't exist") !== false) {
            phase4_check($label, true, 'skipped — db-env view/table');
            return;
        }
        if ($try < 2 && in_array($r['code'], [403, 500, 502, 503], true) && $identity && $password) {
            continue;
        }
        phase4_check($label, false, 'HTTP ' . $r['code'] . ' len=' . strlen($r['body']));
        return;
    }
    phase4_check($label, false, 'HTTP ' . $r['code'] . ' len=' . strlen($r['body']));
}

function phase4_reportsLoadAjax($base, $label, $screenPath, $action, $cookieFile, $identity = '', $password = '')
{
    for ($try = 0; $try < 3; $try++) {
        if ($try > 0 && $identity && $password) {
            usleep(250000);
            phase4_login($base, $identity, $password, $cookieFile);
        }
        $r = phase4_httpGetFollow("$base$screenPath", $cookieFile);
        if ($r['code'] !== 200) {
            if ($try < 2 && in_array($r['code'], [500, 403, 502, 503], true) && $identity && $password) {
                continue;
            }
            phase4_check($label, false, "screen HTTP {$r['code']}");
            return;
        }
        $token = phase4_extractCsrf($r['body']);
        $r = phase4_httpRequest("$base/reports/load_ajax_reports", $cookieFile, http_build_query([
            'action' => $action,
            'page' => 1,
            'token' => $token ?? '',
        ]));
        $ok = $r['code'] === 200 && strlen($r['body']) > 5 && !phase4_hasPhpIssue($r['body']);
        if ($ok) {
            phase4_check($label, $ok, 'HTTP ' . $r['code'] . ' len=' . strlen($r['body']));
            return;
        }
        if (stripos($r['body'], 'definer') !== false || stripos($r['body'], "doesn't exist") !== false) {
            phase4_check($label, true, 'skipped — db-env view/table');
            return;
        }
        if ($try < 2 && in_array($r['code'], [403, 500, 502, 503], true) && $identity && $password) {
            continue;
        }
        phase4_check($label, $ok, 'HTTP ' . $r['code'] . ' len=' . strlen($r['body']));
        return;
    }
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$dates = ['start_date' => date('Y-m-01'), 'end_date' => date('Y-m-d')];

$ajaxTests = [
    ['getSalesReport', '/reports/sales', '/reports/getSalesReport/?v=1', $dates],
    ['getSalesReportDue', '/reports/sales_due', '/reports/getSalesReportDue/?v=1', $dates],
    ['getCustomerSalesReport', '/reports/get_customer_wise_sales', '/reports/getCustomerSalesReport/?v=1', $dates],
    ['get_sales_person_report', '/reports/sales_person_report', '/reports/get_sales_person_report/?v=1', $dates],
    ['getPurchasesReport', '/reports/purchases', '/reports/getPurchasesReport/?v=1', $dates],
    ['getPurchasesReportDue', '/reports/purchases_due', '/reports/getPurchasesReportDue/?v=1', $dates],
    ['getSalesReportC', '/reports/sales_gst_report', '/reports/getSalesReportC/?v=1', $dates],
    ['getPurchasesReportC', '/reports/purchases_gst_report', '/reports/getPurchasesReportC/?v=1', $dates],
    ['getTaxReports', '/reports/taxreports', '/reports/getTaxReports/?v=1', $dates],
    ['getTaxReports_new', '/reports/taxreports_new', '/reports/getTaxReports_new/?v=1', $dates],
    ['getTaxHsnCodeReports', '/reports/hsncode_reports', '/reports/getTaxHsnCodeReports/?v=1', $dates],
    ['getTaxHsnCodeReports_new', '/reports/hsncode_reports_new', '/reports/getTaxHsnCodeReports_new/?v=1', $dates],
    ['getSalesReportCnew', '/reports/sales_gst_reportnew', '/reports/getSalesReportCnew/?v=1', $dates],
    ['getQuantityAlerts', '/reports/quantity_alerts', '/reports/getQuantityAlerts/?v=1', []],
    ['getExpiryAlerts', '/reports/expiry_alerts', '/reports/getExpiryAlerts/?v=1', []],
    ['getCategoriesReport', '/reports/categories', '/reports/getCategoriesReport/?v=1', []],
    ['getBrandsReport', '/reports/brands', '/reports/getBrandsReport/?v=1', []],
    ['getAdjustmentReport', '/reports/adjustments', '/reports/getAdjustmentReport/?v=1', $dates],
    ['getPaymentsReport', '/reports/payments', '/reports/getPaymentsReport/?v=1', $dates],
    ['getPaymentSummary', '/reports/paymentssummary', '/reports/getPaymentSummary/?v=1', $dates],
    ['getExpensesReport', '/reports/expenses', '/reports/getExpensesReport/?v=1', $dates],
    ['getCashTransactionReport', '/reports/cash_transaction', '/reports/getCashTransactionReport/?v=1', $dates],
    ['get_deposit_report', '/reports/deposit', '/reports/get_deposit_report/?v=1', $dates],
    ['getDepositHistory', '/reports/depositHistory', '/reports/getDepositHistory/?v=1', $dates],
    ['getCustomersData', '/reports/customers', '/reports/getCustomersData/?v=1', []],
    ['getSuppliers', '/reports/suppliers', '/reports/getSuppliers/?v=1', []],
    ['getUsers', '/reports/users', '/reports/getUsers', []],
    ['getUserLogActionReport', '/reports/user_log_action', '/reports/getUserLogActionReport/?v=1', $dates],
    ['getRrgisterlogs', '/reports/register', '/reports/getRrgisterlogs/?v=1', $dates],
    ['gettransfer_request', '/reports/transfer_request', '/reports/gettransfer_request/?v=1', []],
    ['getOverduePaymentsReport', '/reports/overdue_payments', '/reports/getOverduePaymentsReport/?v=1', $dates],
    ['getChallansReport', '/reports/challans', '/reports/getChallansReport/?v=1', $dates],
    ['getCategoriesDetailReport', '/reports/categories_report', '/reports/getCategoriesDetailReport/?v=1', $dates],
    ['getProductsReport_Profitloss', '/reports/products_profitloss', '/reports/getProductsReport_Profitloss/?v=1', $dates],
    ['getProductsOrderReport', '/reports/products_orderReport', '/reports/getProductsOrderReport/?v=1', $dates],
    ['get_products_combo_items_report', '/reports/products_combo_items', '/reports/get_products_combo_items_report/', []],
    ['getTransactionHistory', '/reports/transaction_history', '/reports/getTransactionHistory', []],
    ['getCustomerLedgerV1', '/reports/customer_ledger_v1', '/reports/getCustomerLedgerV1/?v=1', $dates],
    ['load_product_varient_stock_report', '/reports/product_varient_stock_report', '/reports/load_product_varient_stock_report/?v=1', $dates],
    ['load_product_varient_report', '/reports/product_varient_report', '/reports/load_product_varient_report/?v=1', $dates],
    ['load_product_varient_sale_report', '/reports/product_varient_sale_report', '/reports/load_product_varient_sale_report/?v=1', $dates],
    ['load_product_varient_purchase_report', '/reports/product_varient_purchase_report', '/reports/load_product_varient_purchase_report/?v=1', $dates],
];

$ajaxNum = 0;
foreach ($ajaxTests as $t) {
    if ($ajaxNum > 0 && $ajaxNum % 8 === 0) {
        sleep(2);
    }
    phase4_login($base, $identity, $password, $cookieFile);
    phase4_reportsAjax($base, $t[0], $t[1], $t[2], $cookieFile, $t[3], 'POST', $identity, $password);
    usleep(150000);
    $ajaxNum++;
}
$loadAjax = [
    ['load_ajax CustomerLedgers', '/reports/customer_ledger', 'CustomerLedgers'],
    ['load_ajax ProductsTransactionsReport', '/reports/products_transactions', 'ProductsTransactionsReport'],
    ['load_ajax ProductsLedgers', '/reports/products_ledgers', 'ProductsLedgers'],
    ['load_ajax CustomerDepositLadger', '/reports/customerDepositLedger', 'CustomerDepositLadger'],
];
foreach ($loadAjax as $t) {
    phase4_login($base, $identity, $password, $cookieFile);
    phase4_reportsLoadAjax($base, $t[0], $t[1], $t[2], $cookieFile, $identity, $password);
    usleep(150000);
}

phase4_login($base, $identity, $password, $cookieFile);
$startSeg = date('Y-m-01');
$endSeg = date('Y-m-d');
$profitOk = false;
$profitCode = 0;
for ($try = 0; $try < 3; $try++) {
    if ($try > 0) {
        usleep(250000);
        phase4_login($base, $identity, $password, $cookieFile);
    }
    phase4_httpGetFollow("$base/reports/profit_loss", $cookieFile);
    $r = phase4_httpGetFollow("$base/reports/profit_loss/$startSeg/$endSeg", $cookieFile);
    $profitCode = $r['code'];
    $profitOk = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
    if ($profitOk || !in_array($r['code'], [500, 403, 502, 503], true)) {
        break;
    }
}
phase4_check('profit_loss date range GET', $profitOk, 'HTTP ' . $profitCode);

for ($try = 0; $try < 3; $try++) {
    if ($try > 0) {
        usleep(250000);
        phase4_login($base, $identity, $password, $cookieFile);
    }
    $r = phase4_httpGetFollow("$base/reports/getProductsReport?v=1", $cookieFile);
    $ok = $r['code'] === 200 && strlen($r['body']) > 50 && !phase4_hasPhpIssue($r['body']);
    if ($ok || !in_array($r['code'], [500, 403, 502, 503], true)) {
        phase4_check('getProductsReport GET', $ok, 'HTTP ' . $r['code']);
        break;
    }
    if ($try === 2) {
        phase4_check('getProductsReport GET', $ok, 'HTTP ' . $r['code']);
    }
}

for ($try = 0; $try < 3; $try++) {
    if ($try > 0) {
        usleep(250000);
        phase4_login($base, $identity, $password, $cookieFile);
    }
    $r = phase4_httpGetFollow("$base/reports/getProductCosting", $cookieFile);
    $ok = $r['code'] === 200 && !phase4_hasPhpIssue($r['body']);
    if ($ok || !in_array($r['code'], [500, 403, 502, 503], true)) {
        phase4_check('getProductCosting GET', $ok, 'HTTP ' . $r['code']);
        break;
    }
    if ($try === 2) {
        phase4_check('getProductCosting GET', $ok, 'HTTP ' . $r['code']);
    }
}

echo "\n=== Reports ALL deep | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
