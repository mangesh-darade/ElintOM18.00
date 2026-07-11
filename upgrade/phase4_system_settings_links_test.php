<?php
/**
 * Phase 4 — System Settings deep-link tests (Owner)
 * Run: php phase4_system_settings_links_test.php <identity> <password>
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/phase4_test_lib.php';

$base = 'http://localhost/ElintOM18.00';
$identity = $argv[1] ?? '';
$password = $argv[2] ?? '';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpupgrade_system_settings_links_cookies.txt';
$phase4_fail = 0;
$phase4_pass = 0;
$prefix = '/system_settings';

if (!$identity || !$password) {
    echo "Usage: php phase4_system_settings_links_test.php <identity> <password>\n";
    exit(1);
}

phase4_check('Login', phase4_login($base, $identity, $password, $cookieFile));

$ids = [
    'group' => null, 'currency' => null, 'category' => null, 'tax' => null,
    'customer_group' => null, 'warehouse' => null, 'variant' => null,
    'unit' => null, 'brand' => null, 'price_group' => null,
];

$r = phase4_httpGetFollow("$base$prefix/user_groups", $cookieFile);
$token = phase4_extractCsrf($r['body']);

$ajaxMap = [
    'group' => 'getUserGroups',
    'currency' => 'getCurrencies',
    'category' => 'getCategories',
    'tax' => 'getTaxRates',
    'customer_group' => 'getCustomerGroups',
    'warehouse' => 'getWarehouses',
    'variant' => 'getVariants',
    'unit' => 'getUnits',
    'brand' => 'getBrands',
    'price_group' => 'getPriceGroups',
];

foreach ($ajaxMap as $key => $method) {
    if (!$token) {
        break;
    }
    $r = phase4_httpRequest("$base$prefix/$method", $cookieFile, phase4_dtPost($token), ['X-Requested-With: XMLHttpRequest']);
    $id = phase4_firstDtId($r['body']);
    if ($id) {
        $ids[$key] = $id;
    }
}

$links = [];
if ($ids['group']) {
    $links['edit_group'] = "$prefix/edit_group/{$ids['group']}";
    $links['permissions'] = "$prefix/permissions/{$ids['group']}";
    $links['group_settings_screens'] = "$prefix/group_settings_screens/{$ids['group']}";
}
if ($ids['currency']) {
    $links['edit_currency'] = "$prefix/edit_currency/{$ids['currency']}";
}
if ($ids['category']) {
    $links['edit_category'] = "$prefix/edit_category/{$ids['category']}";
}
if ($ids['tax']) {
    $links['edit_tax_rate'] = "$prefix/edit_tax_rate/{$ids['tax']}";
}
if ($ids['customer_group']) {
    $links['edit_customer_group'] = "$prefix/edit_customer_group/{$ids['customer_group']}";
}
if ($ids['warehouse']) {
    $links['edit_warehouse'] = "$prefix/edit_warehouse/{$ids['warehouse']}";
}
if ($ids['variant']) {
    $links['edit_variant'] = "$prefix/edit_variant/{$ids['variant']}";
}
if ($ids['unit']) {
    $links['edit_unit'] = "$prefix/edit_unit/{$ids['unit']}";
}
if ($ids['brand']) {
    $links['edit_brand'] = "$prefix/edit_brand/{$ids['brand']}";
}
if ($ids['price_group']) {
    $links['edit_price_group'] = "$prefix/edit_price_group/{$ids['price_group']}";
    $links['group_product_prices'] = "$prefix/group_product_prices/{$ids['price_group']}";
}

$links['email_templates'] = "$prefix/email_templates/credentials";
$links['change_logo'] = "$prefix/change_logo";
$links['updates'] = "$prefix/updates";
$links['tax_rates_attr'] = "$prefix/tax_rates_attr";
$links['variants'] = "$prefix/variants";
$links['price_groups'] = "$prefix/price_groups";
$links['printers'] = "$prefix/printers";
$links['restaurant_tables'] = "$prefix/restaurant_tables";

foreach ($links as $label => $path) {
    phase4_linkGet($base, "SS $label", $path, $cookieFile, [200], $identity, $password);
}

echo "\n=== System Settings deep-links | PHP " . PHP_VERSION . " | $phase4_pass passed, $phase4_fail failed ===\n";
exit($phase4_fail > 0 ? 1 : 0);
