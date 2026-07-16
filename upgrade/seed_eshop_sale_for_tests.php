<?php
/**
 * Seed one completed eshop_sale=1 sale (+ item + eshop_order) for deep-link tests.
 * Run: php seed_eshop_sale_for_tests.php
 * Skips if an eshop sale already exists.
 */
$m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
if ($m->connect_error) {
    fwrite(STDERR, 'connect: ' . $m->connect_error . PHP_EOL);
    exit(1);
}
$m->set_charset('utf8');

$existing = $m->query('SELECT id FROM sma_sales WHERE eshop_sale=1 ORDER BY id DESC LIMIT 1');
if ($existing && $existing->num_rows) {
    $id = (int) $existing->fetch_assoc()['id'];
    echo "SKIP seed — existing eshop sale id=$id\n";
    exit(0);
}

$customer = $m->query("SELECT id,name FROM sma_companies WHERE group_name='customer' ORDER BY id ASC LIMIT 1")->fetch_assoc();
$biller = $m->query("SELECT id,name FROM sma_companies WHERE group_name='biller' LIMIT 1")->fetch_assoc();
$product = $m->query('SELECT id,code,name,price,cost,tax_rate,unit,sale_unit FROM sma_products WHERE id=2 LIMIT 1')->fetch_assoc();
if (!$product) {
    $product = $m->query('SELECT id,code,name,price,cost,tax_rate,unit,sale_unit FROM sma_products LIMIT 1')->fetch_assoc();
}
$warehouse = $m->query('SELECT id,name FROM sma_warehouses LIMIT 1')->fetch_assoc();
$userId = 1;

$price = (float) $product['price']; // 15.00 tea
$qty = 2.0;
$netUnit = round($price / 1.05, 4);
$unitTax = round($price - $netUnit, 4);
$itemTax = round($unitTax * $qty, 4);
$subtotal = round($price * $qty, 4); // inclusive
$totalNet = round($netUnit * $qty, 4);
$now = date('Y-m-d H:i:s');
$ref = 'ESHOP/SEED/' . date('YmdHis');
$orderNo = 'EO' . date('YmdHis');
$invoiceNo = 'E' . date('His');

$saleSql = sprintf(
    "INSERT INTO sma_sales (
        date, reference_no, invoice_no, customer_id, customer, biller_id, biller,
        warehouse_id, note, total, product_discount, total_discount, order_discount,
        product_tax, order_tax, total_tax, shipping, grand_total, sale_status, payment_status,
        payment_term, created_by, total_items, pos, paid, surcharge, return_sale_total,
        rounding, eshop_sale, offline_sale, delivery_status, eshop_order_alert_status,
        order_no, cgst, sgst, igst, order_type, source
    ) VALUES (
        '%s', '%s', '%s', %d, '%s', %d, '%s',
        %d, 'Eshop sales PHP upgrade seed', %F, 0, 0, 0,
        %F, 0, %F, 0, %F, 'completed', 'due',
        0, %d, 1, 0, 0, 0, 0,
        0, 1, 0, 'pending', 1,
        '%s', %F, %F, 0, 'Delivery', 'eshop'
    )",
    $m->real_escape_string($now),
    $m->real_escape_string($ref),
    $m->real_escape_string($invoiceNo),
    (int) $customer['id'],
    $m->real_escape_string($customer['name']),
    (int) $biller['id'],
    $m->real_escape_string($biller['name']),
    (int) $warehouse['id'],
    $totalNet,
    $itemTax,
    $itemTax,
    $subtotal,
    $userId,
    $m->real_escape_string($orderNo),
    $itemTax / 2,
    $itemTax / 2
);

if (!$m->query($saleSql)) {
    fwrite(STDERR, 'sale insert: ' . $m->error . PHP_EOL);
    exit(1);
}
$saleId = (int) $m->insert_id;

$unitId = (int) ($product['sale_unit'] ?: $product['unit'] ?: 1);
$taxRateId = (int) ($product['tax_rate'] ?: 15);
$itemSql = sprintf(
    "INSERT INTO sma_sale_items (
        sale_id, product_id, product_code, product_name, product_type, option_id,
        net_unit_price, unit_discount, unit_tax, invoice_unit_price, invoice_net_unit_price,
        unit_price, quantity, packing_size, net_price, invoice_total_net_unit_price,
        warehouse_id, item_tax, tax_method, tax_rate_id, tax, discount, item_discount,
        subtotal, serial_no, real_unit_price, product_unit_id, product_unit_code, unit_quantity,
        mrp, hsn_code, note, delivery_status, pending_quantity, delivered_quantity,
        gst_rate, cgst, sgst, igst, item_weight
    ) VALUES (
        %d, %d, '%s', '%s', 'standard', 0,
        %F, 0, %F, %F, %F,
        %F, %F, 0, %F, %F,
        %d, %F, '0', %d, '5.0000%%', '0%%', 0,
        %F, '', %F, %d, 'Pcs', %F,
        %F, '', '', 'pending', %F, 0,
        2.5, %F, %F, 0, 0
    )",
    $saleId,
    (int) $product['id'],
    $m->real_escape_string($product['code']),
    $m->real_escape_string($product['name']),
    $netUnit,
    $unitTax,
    $netUnit,
    $price,
    $price,
    $qty,
    $price,
    $subtotal,
    (int) $warehouse['id'],
    $itemTax,
    $taxRateId,
    $subtotal,
    $price,
    $unitId,
    $qty,
    $price,
    $qty,
    $itemTax / 2,
    $itemTax / 2
);

if (!$m->query($itemSql)) {
    fwrite(STDERR, 'item insert: ' . $m->error . PHP_EOL);
    exit(1);
}

$eoSql = sprintf(
    "INSERT INTO sma_eshop_order (
        sale_id, is_cod, shipping_method_name, date, customer_id,
        billing_name, billing_addr, billing_email, billing_phone,
        shipping_name, shipping_addr, shipping_email, shipping_phone
    ) VALUES (
        %d, '1', 'Standard Delivery', '%s', %d,
        '%s', 'Seed Billing Addr, Pune', 'seed@example.com', '9999999999',
        '%s', 'Seed Shipping Addr, Pune', 'seed@example.com', '9999999999'
    )",
    $saleId,
    $m->real_escape_string($now),
    (int) $customer['id'],
    $m->real_escape_string($customer['name']),
    $m->real_escape_string($customer['name'])
);

if (!$m->query($eoSql)) {
    fwrite(STDERR, 'eshop_order insert: ' . $m->error . PHP_EOL);
    exit(1);
}

echo "SEEDED eshop sale_id=$saleId ref=$ref grand_total=$subtotal\n";
exit(0);
