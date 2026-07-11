<?php
/**
 * Phase 4 — Purchases deep-link tests (logged-in)
 * Run: php phase4_purchases_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_purchases_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

if (!$identity || !$password) {
    echo "Usage: php phase4_purchases_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/purchases", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/purchases/getPurchases", $cookieFile, phase4_dtPost($token));
$purchaseId = phase4_firstDtId($r['body']);
phase4_check('getPurchases AJAX', $r['code'] === 200 && $purchaseId !== null, 'purchase_id=' . ($purchaseId ?? 'none'));

if ($purchaseId) {
    $links = [
        'view' => "/purchases/view/$purchaseId",
        'edit' => "/purchases/edit/$purchaseId",
        'modal_view' => "/purchases/modal_view/$purchaseId",
        'modal_print' => "/purchases/modal_view/$purchaseId?print=1",
        'pdf' => "/purchases/pdf/$purchaseId",
        'email' => "/purchases/email/$purchaseId",
        'payments' => "/purchases/payments/$purchaseId",
        'add_payment' => "/purchases/add_payment/$purchaseId",
        'duplicate' => "/purchases/add?purchase_id=$purchaseId",
        'print_barcodes' => "/products/print_barcodes/?purchase=$purchaseId",
        'update_status' => "/purchases/update_status/$purchaseId",
        'inward_po' => "/purchases/inward_po/$purchaseId",
    ];
    foreach ($links as $label => $path) {
        $codes = in_array($label, ['pdf'], true) ? [200, 302] : [200];
        phase4_linkGet($base, "Purchase $label", $path, $cookieFile, $codes);
    }

    $r = phase4_httpGetFollow("$base/purchases/payments/$purchaseId", $cookieFile);
    $paymentId = null;
    if (preg_match('/purchases\/payment_note\/(\d+)/', $r['body'], $m) ||
        preg_match('/purchases\/edit_payment\/(\d+)/', $r['body'], $m)) {
        $paymentId = (int) $m[1];
    }
    if ($paymentId) {
        phase4_linkGet($base, 'payment_note', "/purchases/payment_note/$paymentId", $cookieFile);
        phase4_linkGet($base, 'edit_payment', "/purchases/edit_payment/$paymentId", $cookieFile);
    } else {
        phase4_check('Payment deep links', true, 'skipped — no payment id on PO');
    }
} else {
    phase4_check('Purchase deep links', false, 'no purchase id');
}

$r = phase4_httpGetFollow("$base/purchases/expenses", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/purchases/getExpenses", $cookieFile, phase4_dtPost($token));
$expenseId = phase4_firstDtId($r['body']);
if ($expenseId) {
    phase4_linkGet($base, 'expense_note', "/purchases/expense_note/$expenseId", $cookieFile);
    phase4_linkGet($base, 'edit_expense', "/purchases/edit_expense/$expenseId", $cookieFile);
} else {
    phase4_check('Expense deep links', true, 'skipped — no expense rows');
}

phase4_linkGet($base, 'add_expense', '/purchases/add_expense', $cookieFile);

$_SERVER['HTTP_HOST'] = 'localhost';
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}
require PHPUPGRADE_ROOT . '/app/config/database.php';
$pdo = new PDO("mysql:host={$db['default']['hostname']};dbname={$db['default']['database']}", $db['default']['username'], $db['default']['password']);
$returnableId = (int) $pdo->query("SELECT id FROM sma_purchases WHERE status IN ('received','partial') AND (return_id IS NULL OR return_id = 0) ORDER BY id DESC LIMIT 1")->fetchColumn();
if ($returnableId) {
    phase4_linkGet($base, 'return_purchase', "/purchases/return_purchase/$returnableId", $cookieFile);
} else {
    phase4_check('return_purchase', true, 'skipped — no returnable PO');
}
$returnRowId = (int) $pdo->query("SELECT id FROM sma_purchases WHERE status = 'returned' ORDER BY id DESC LIMIT 1")->fetchColumn();
if ($returnRowId) {
    phase4_linkGet($base, 'view_return', "/purchases/view_return/$returnRowId", $cookieFile);
} else {
    phase4_check('view_return', true, 'skipped — no returned PO in DB');
}

echo "\n=== Purchases deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
