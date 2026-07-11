<?php
/**
 * Phase 4 — Purchases deep tests: suggestions, PO submit, view
 * Run: php phase4_purchases_deep_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/phpupgrade';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_purchases_deep_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;

function extractSelectFirst($html, $name)
{
    if (preg_match('/name="' . preg_quote($name, '/') . '"[^>]*>.*?<option[^>]*selected[^>]*value="([^"]+)"/s', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name="' . preg_quote($name, '/') . '"[^>]*>.*?<option[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/id="po' . preg_quote($name, '/') . '"[^>]*>.*?<option[^>]*value="(\d+)"/s', $html, $m)) {
        return $m[1];
    }
    return null;
}

if (!$identity || !$password) {
    echo "Usage: php phase4_purchases_deep_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$r = phase4_httpGetFollow("$base/purchases", $cookieFile);
$token = phase4_extractCsrf($r['body']);
$r = phase4_httpRequest("$base/purchases/getPurchases", $cookieFile, phase4_dtPost($token));
$templateId = phase4_firstDtId($r['body']);
phase4_check('Resolve template PO', $templateId !== null, 'purchase_id=' . ($templateId ?? 'none'));

$r = phase4_httpGetFollow("$base/purchases/add?purchase_id=$templateId", $cookieFile);
$warehouse = extractSelectFirst($r['body'], 'warehouse');
$status = extractSelectFirst($r['body'], 'status') ?: 'pending';
$supplierId = null;
if (preg_match("/localStorage\.setItem\('posupplier',\s*'(\d+)'\)/", $r['body'], $m)) {
    $supplierId = $m[1];
}
if (!$supplierId && preg_match('/id="posupplier"[^>]*value="(\d+)"/', $r['body'], $m)) {
    $supplierId = $m[1];
}
$token = phase4_extractCsrf($r['body']);
phase4_check('Parse add defaults', $warehouse && $supplierId && $token, "wh=$warehouse supplier=$supplierId status=$status");

$sugUrl = "$base/purchases/suggestions?term=test&supplier_id=$supplierId&quantity=1";
$sug = phase4_httpRequest($sugUrl, $cookieFile);
$sugItems = json_decode($sug['body'], true);
$sugOk = $sug['code'] === 200 && is_array($sugItems) && count($sugItems) > 0 && !phase4_hasPhpIssue($sug['body']);
$row = null;
$taxRate = null;
if ($sugOk && !empty($sugItems[0]['row'])) {
    $row = is_array($sugItems[0]['row']) ? $sugItems[0]['row'] : (array) $sugItems[0]['row'];
    if (!empty($sugItems[0]['tax_rate'])) {
        $taxRate = is_array($sugItems[0]['tax_rate']) ? $sugItems[0]['tax_rate'] : (array) $sugItems[0]['tax_rate'];
    }
}
$productCode = $row['code'] ?? null;
phase4_check('Product suggestions AJAX', $sugOk && $productCode, $productCode ? "code=$productCode" : substr($sug['body'], 0, 80));

if ($row && $warehouse && $supplierId) {
    $cost = (float) ($row['cost'] ?? $row['real_unit_cost'] ?? 10);
    $qty = 1;
    $taxId = (string) ($row['tax_rate'] ?? '0');
    $taxMethod = (string) ($row['item_tax_method'] ?? $row['tax_method'] ?? '0');
    $unit = (string) ($row['unit'] ?? $row['purchase_unit'] ?? '1');

    $poPost = [
        'token' => $token,
        'warehouse' => $warehouse,
        'supplier' => $supplierId,
        'status' => $status,
        'reference_no' => '',
        'note' => 'PHP upgrade deep test',
        'shipping' => '0',
        'payment_term' => '',
        'product_id[]' => (string) ($row['id'] ?? ''),
        'product[]' => (string) ($row['code'] ?? $productCode),
        'product_name[]' => (string) ($row['name'] ?? 'Test Product'),
        'quantity[]' => (string) $qty,
        'unit_cost[]' => (string) $cost,
        'net_cost[]' => (string) $cost,
        'real_unit_cost[]' => (string) ($row['real_unit_cost'] ?? $cost),
        'product_tax[]' => $taxId,
        'tax_method[]' => $taxMethod,
        'product_unit[]' => $unit,
        'product_base_quantity[]' => (string) $qty,
        'quantity_balance[]' => '0',
        'product_option[]' => (string) ($row['option'] ?? '0'),
        'product_option_color[]' => '',
        'product_discount[]' => '0',
        'part_no[]' => '',
        'hsn_code[]' => (string) ($row['hsn_code'] ?? ''),
        'batch_number[]' => '',
        'expiry[]' => '',
    ];

    $r2 = phase4_httpGetFollow("$base/purchases/add", $cookieFile);
    if ($t2 = phase4_extractCsrf($r2['body'])) {
        $poPost['token'] = $t2;
    }

    $sr = phase4_httpRequest("$base/purchases/add", $cookieFile, http_build_query($poPost), [
        'Content-Type: application/x-www-form-urlencoded',
        'Referer: http://localhost/phpupgrade/purchases/add',
    ]);
    $poOk = in_array($sr['code'], [302, 303], true) && $sr['location']
        && stripos($sr['location'], 'purchases') !== false;
    $detail = "HTTP {$sr['code']}";
    if ($poOk) {
        $detail .= ' → ' . $sr['location'];
    } elseif (phase4_hasPhpIssue($sr['body'])) {
        preg_match('/(Fatal error|Uncaught Error|Uncaught TypeError).{0,150}/', $sr['body'], $em);
        $detail = trim(strip_tags($em[0] ?? $detail));
    } elseif ($sr['code'] === 200 && strpos($sr['body'], 'powarehouse') !== false) {
        $detail = 'validation error — re-rendered add form';
    }
    phase4_check('Purchase submit', $poOk, $detail);

    if ($poOk) {
        $list = phase4_httpRequest("$base/purchases/getPurchases", $cookieFile, phase4_dtPost($poPost['token']));
        $newId = phase4_firstDtId($list['body']);
        if ($newId) {
            $vr = phase4_httpGetFollow("$base/purchases/view/$newId", $cookieFile);
            phase4_check('Purchase view after submit', $vr['code'] === 200 && !phase4_hasPhpIssue($vr['body']), "HTTP {$vr['code']} purchase_id=$newId");
        } else {
            phase4_check('Purchase view after submit', false, 'could not resolve new purchase_id');
        }
    }
} else {
    phase4_check('Purchase submit', false, 'skipped — missing product/supplier');
    phase4_check('Purchase view after submit', false, 'skipped');
}

echo "\n=== Purchases deep | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
