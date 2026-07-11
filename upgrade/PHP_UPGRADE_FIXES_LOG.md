# PHP 8.5 Upgrade — Fixes Log (Old code → New code)

**Project:** `phpupgrade` (ElintPOS / SMA ERP, CodeIgniter 3.1.13)  
**Target PHP:** 8.5.x  
**Purpose:** Every compatibility fix recorded with **Old code** and **New code** snippets.  
**Plan reference:** `upgrade/PHP_UPGRADE_PLAN.md` (strategy, screens, test status)

| Log updated | Modules complete in log |
|-------------|-------------------------|
| 2026-07-07 | 1–10 (+ Framework / Third-party batches) |

---

## How to read

| Field | Meaning |
|-------|---------|
| **Summary table** | Quick index: file, symptom, fix type |
| **Old code** | Snippet before fix (or broken pattern) |
| **New code** | Minimal change applied — **behavior preserved** |
| **Type** | `guard` / `syntax` / `restore` / `lib` / `test` / `db-env` |
| **P1–P6** | Reused pattern — see [Common patterns](#common-patterns) |

**Rule:** Agents must append rows **and** Old/New code blocks after each fix session — see `.cursor/rules/php-upgrade-fixes-log.mdc`.

---

## Common patterns

Use these IDs in summary tables when the same fix repeats many times in one file.

### P1 — Bare `HTTP_REFERER` redirect

Used in: Auth, Welcome, POS, Sales (79×), Products (41×), Purchases (60×), Reports (64×).

**Old code:**
```php
redirect($_SERVER["HTTP_REFERER"]);
```

**New code:**
```php
redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('module'));
// module = 'auth/login' | 'sales' | 'products' | 'purchases' | 'reports' | etc.
```

---

### P2 — `foreach` on query result that may be `false`

**Old code:**
```php
foreach ($rows as $row) {
```

**New code:**
```php
if (!empty($rows)) {
    foreach ($rows as $row) {
        // ...
    }
}
```

---

### P3 — `getInvoiceByID` / `getPurchaseByID` false → property access fatal

**Old code:**
```php
$inv = $this->sales_model->getInvoiceByID($id);
$this->data['inv'] = $inv;
// later: $inv->reference_no → fatal if false
```

**New code:**
```php
$inv = $this->sales_model->getInvoiceByID($id);
if (!$inv) {
    $this->session->set_flashdata('error', lang('sale_not_found'));
    redirect('sales');
}
```

---

### P4 — Dynamic properties (PHP 8.2+)

**Old code:**
```php
class MY_Controller extends CI_Controller {
```

**New code:**
```php
#[\AllowDynamicProperties]
class MY_Controller extends CI_Controller {
```

---

### P5 — `end(explode(...))` illegal in PHP 8

**Old code:**
```php
$right_section = end(explode("/", $saleData->reference_no));
```

**New code:**
```php
$parts = explode("/", $saleData->reference_no);
$right_section = end($parts);
```

---

### P6 — Unquoted `$_SERVER` keys

**Old code:**
```php
$_SERVER[HTTP_HOST]
```

**New code:**
```php
$_SERVER['HTTP_HOST']
```

---

## Phase 1 — Framework (`system/`)

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| F1 | `system/core/Controller.php`, `Model.php` | Dynamic properties deprecation/fatal | P4 | syntax |
| F2 | `Session.php`, `PHP8SessionWrapper.php` | PHP 8 session incompatibilities | PHP 8 session wrapper | lib |
| F3 | `compat/hash.php`, `mbstring.php` | Missing PHP 8 polyfills | Compat layer added | lib |

#### F1 `system/core/Controller.php`

**Old code:**
```php
class CI_Controller {
```

**New code:**
```php
#[\AllowDynamicProperties]
class CI_Controller {
```

---

## Phase 2 — Third party (`app/third_party/`)

| # | Library | Old | New | Type |
|---|---------|-----|-----|------|
| T1 | MPDF | 6.0 fatal parse | 8.3.1 via Composer + legacy shim `MPDF/mpdf.php` | lib |
| T2 | Stripe | 3.23.0 manual `init.php` | 16.6.0 Composer + adapter | lib |
| T3 | Google API | 2.4.1 in `googlelogin/` | 2.19.4 via `third_party/autoload.php` | lib |
| T4 | PHPExcel | Curly-brace offsets `$str{0}` | `$str[0]` (36 files) | syntax |
| T5 | Zend Barcode | Curly-brace offsets; `Zend.php` parse error | 8 files patched | syntax |
| T6 | phpqrcode | Required params before optional | Default `$back_color` / `$fore_color` | syntax |
| T7 | Facebook SDK 5.0 | — | Unchanged (loads on 8.5) | — |

#### T4 PHPExcel — curly-brace string offset

**Old code:**
```php
$char = $value{0};
```

**New code:**
```php
$char = $value[0];
```

---

## Phase 3 — App core (pre–module scan)

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| C1 | `MY_Controller.php`, `Auth_model.php` | Dynamic properties | P4 | syntax |
| C2 | `Paytm.php`, `Apicrypter.php` | Fatal parse (curly braces / cast) | PHP 8.5 syntax fix | syntax |
| C3 | `Ion_auth.php` | Undefined `$password` in email path | Null guard / init | guard |
| C4 | 16 files (models/controllers) | Optional param before required | Trailing `= null` on optional params | syntax |
| C5 | `Encrypt.php`, `crypto_helper.php`, etc. | mcrypt removed in PHP 8 | OpenSSL AES-128/256-CBC | lib |
| C6 | `Sma.php` | Dynamic properties; `&` typo | P4; `&` → `&&` | syntax |
| C7 | `Pos.php`, `Pos_elite.php` | `end(explode())` illegal | P5 | syntax |

#### C3 `Ion_auth.php` — undefined `$password`

**Old code:**
```php
// $password not set on some code paths
$password = $this->hash_password($password, ...);
```

**New code:**
```php
$password = isset($password) ? $password : '';
// or early return / skip when password empty
```

#### C4 Optional-before-required signature

**Old code:**
```php
public function foo($optional = null, $required) {
```

**New code:**
```php
public function foo($required, $optional = null) {
// OR: public function foo($optional = null, $required = null) {
```

---

## Module 1 — Auth & Users

**Tests:** `phase4_auth_test.php`, `phase4_auth_links_test.php` — **5/5** deep-links PASS  
**Status:** ✅ Module complete

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 1.1 | `Auth_model.php` | `single_login` broken raw SQL | Query builder `like` on `app_sessions` | guard |
| 1.2 | `Ion_auth.php` | Null access in `in_group()`, `getUserGroupID()` | Null guards | guard |
| 1.3 | `Auth.php` | Bare `HTTP_REFERER` redirect | P1 → `site_url('auth/login')` | guard |
| 1.4 | `Auth.php` | Null on login/profile/edit_user | Null guards before `->property` | guard |
| 1.5 | `Auth.php` | `restandlogout` unsafe redirect | P1 | guard |
| 1.6 | `Bcrypt.php` | `openssl_random_pseudo_bytes` may return false | False check before use | guard |
| 1.7 | Auth views | `$error`/`$message` undefined | `!empty()` guards | guard |
| 1.8 | `register.php` | Short open tag `<?` | Full `<?php` | syntax |
| 1.9 | `Welcome.php` | 3× bare `HTTP_REFERER` | P1 | guard |

#### 1.1 `Auth_model.php` — single_login session check

**Old code:**
```php
// Raw SQL string with broken concatenation / wrong table access
$this->db->query("SELECT ... FROM app_sessions WHERE ...");
```

**New code:**
```php
if (!isset($this->Settings->single_login)) {
    $this->Settings->single_login = 0;
}
if ($this->Settings->single_login) {
    $userID_Length = strlen($user->id);
    $now = time() - 10;
    $this->db->where('last_activity >=', $now);
    $this->db->like('user_data', 's:7:"user_id";s:' . $userID_Length . ':"' . $user->id . '";', 'both');
    $sq = $this->db->get('app_sessions');
    // ...
}
```

#### 1.3 `Auth.php` — HTTP_REFERER

**Old code:**
```php
redirect($_SERVER["HTTP_REFERER"]);
```

**New code:**
```php
redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : site_url('auth/login'));
```

#### 1.8 `register.php` — short open tag

**Old code:**
```php
<?
```

**New code:**
```php
<?php
```

---

## Module 2 — Dashboard & Welcome

**Tests:** `phase4_welcome_links_test.php` — **4/4** deep-links PASS  
**Status:** ✅ Module complete

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 2.1 | `Welcome.php` | 3× bare `HTTP_REFERER` | P1 | guard |
| 2.2 | `Welcome.php` | `settings` missing before `page_construct()` | Set `settings` before construct | guard |
| 2.3 | `Welcome.php` | `scandir()` on bad path | Guard before `scandir` | guard |
| 2.4 | `Calendar.php` | Bare `HTTP_REFERER`; `getEvents()` false | P1; false → `[]` | guard |
| 2.5 | `Calendar_model.php` | `$data` unset in `getEvents()` | `$data = array()` init | guard |
| 2.6 | `Db_model.php` | `$data` unset in chart methods | `$data = array()` in each method | guard |
| 2.7 | `dashboard.php` | `$stock` null; unguarded `$GP[...]` | Null guard; `!empty($GP[...])` | guard |
| 2.8 | `admin_access_menu.php` | `$GP` null | Null guards + menu var defaults | guard |
| 2.9 | `user_access_menu.php` | Undefined `$segment1/2`, `$active_*` | Defaults for menu vars | guard |

#### 2.5 `Calendar_model.php` — unset `$data`

**Old code:**
```php
public function getEvents() {
    foreach ($query->result() as $row) {
        $data[] = ...;
    }
    return $data;
}
```

**New code:**
```php
public function getEvents() {
    $data = array();
    foreach ($query->result() as $row) {
        $data[] = ...;
    }
    return $data;
}
```

#### 2.7 `dashboard.php` — permission array

**Old code:**
```php
<?php if ($GP['products-index']) { ?>
```

**New code:**
```php
<?php if (!empty($GP['products-index'])) { ?>
```

---

## Module 3 — POS

**Tests:** `phase4_pos_test.php`, `phase4_pos_deep_test.php`, `phase4_pos_links_test.php` — **11/11** deep-links PASS  
**Status:** ✅ Module complete

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 3.1 | `Pos.php`, `Pos_elite.php`, `Pos2.php`, `Pos_sun.php` | Bare `HTTP_REFERER` | P1 → `site_url('pos')` | guard |
| 3.2 | `Pos.php`, etc. | Unquoted `$_SERVER` keys | P6 | syntax |
| 3.3 | `Pos.php`, `Pos_elite.php` | `getPreviousPosSale()` null → `->id` | Null guards | guard |
| 3.4 | `Pos.php`, `Pos_elite.php` | `customerRefNo` explode out of bounds | Bounds check on explode | guard |
| 3.5 | `add.php`, `add_ep.php` | Unchecked `$_GET['checkout']` | `isset($_GET['checkout'])` | guard |
| 3.6 | `open_register.php`, `close_register.php` | Null closer/due amounts | Null guards in view | guard |
| 3.7 | `Pos.php` | `depositLog` unset | Init array + `!empty()` | guard |
| 3.8 | `Pos.php` | `SellerName` / `source` undefined | isset guards | guard |
| 3.9 | `today_sale.php` | `str_replace` on null refunds | Null guards | guard |
| 3.10 | `Site.php` | `$where_clause['status']` undefined | `isset()` guard | guard |

#### 3.1 `Pos.php` — HTTP_REFERER (representative; many occurrences)

**Old code:**
```php
redirect($_SERVER["HTTP_REFERER"]);
```

**New code:**
```php
redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('pos'));
```

#### 3.4 `Pos.php` — explode bounds

**Old code:**
```php
$refParts = explode("/", $customerRefNo);
$segment = $refParts[2]; // undefined index if short string
```

**New code:**
```php
$refParts = explode("/", $customerRefNo);
$segment = isset($refParts[2]) ? $refParts[2] : '';
```

#### 3.5 `Pos.php` — end(explode)

**Old code:**
```php
$right_section = end(explode("/", $saleData->reference_no));
```

**New code:**
```php
$parts = explode("/", $saleData->reference_no);
$right_section = end($parts);
```

---

## Module 4 — Sales

**Tests:** `phase4_sales_test.php`, `phase4_sales_deep_test.php`, `phase4_sales_links_test.php` — **12/12** deep-links; deep **6/6** PASS  
**Status:** ✅ Module complete

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 4.1 | `Sales.php` | 79× bare `HTTP_REFERER` | P1 → `site_url('sales')` | guard |
| 4.2 | `Sales.php` | `$this->pos_settings` null in constructor | Null guard | guard |
| 4.3 | `Sales.php` | `getInvoiceByID()` false in view/modal/pdf | P3 | guard |
| 4.4 | `Sales.php` | `foreach ($return_rows)` when false | P2 | guard |
| 4.5 | `Sales.php` | `default_printer` null → property access | Null guard | guard |
| 4.6 | `Sales.php` | `getSales` user warehouse null | Guard before filter | guard |
| 4.7 | `Sales.php` | `suggestions()` false customer | Null skip | guard |
| 4.8 | `Sales.php` | `add()`/`edit()` customer false → `->id` | Null guard | guard |
| 4.9 | `Sales.php` | `pos_settings` null for order type | Null guard | guard |
| 4.10 | `Sales.php` | `add()` `customer_pu` null | Guard before use | guard |
| 4.11 | `sales/index.php` | `$_SESSION['Send_Excel']` undefined | `isset()` on session keys | guard |

#### 4.1 `Sales.php` — HTTP_REFERER (79×)

**Old code:**
```php
redirect($_SERVER["HTTP_REFERER"]);
```

**New code:**
```php
redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('sales'));
```

#### 4.3 `Sales.php` — invoice not found

**Old code:**
```php
$inv = $this->sales_model->getInvoiceByID($id);
// direct use of $inv->...
```

**New code:**
```php
$inv = $this->sales_model->getInvoiceByID($id);
if (!$inv) {
    $this->session->set_flashdata('error', lang('sale_not_found'));
    redirect('sales');
}
```

#### 4.4 `Sales.php` — return rows

**Old code:**
```php
foreach ($return_rows as $row) {
```

**New code:**
```php
if (!empty($return_rows)) {
    foreach ($return_rows as $row) {
```

---

## Module 5 — Products & Inventory

**Tests:** `phase4_products_test.php`, `phase4_products_deep_test.php`, `phase4_products_stock_deep_test.php`, `phase4_products_links_test.php` — **17/17** deep-links PASS  
**Status:** ✅ Module complete

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 5.1 | `Products.php` | 41× bare `HTTP_REFERER` | P1 → `site_url('products')` | guard |
| 5.2 | `Products.php` | duplicate product `->type` on false | Null guard | guard |
| 5.3 | `Products.php` | `foreach` on empty `combo_items` | P2 | guard |
| 5.4 | `Products.php` | `$warehouse_ids` unset | Init before use | guard |
| 5.5 | `Products.php` | `modal_view` options false → foreach | P2 | guard |
| 5.6 | `Products.php` | `$size`/`$color` unset | Init variables | guard |
| 5.7 | `Products.php` | `season_id` missing → NOT NULL DB error | Default `0` when not posted | guard |
| 5.8 | `products/view.php` | `foreach ($colors)` when false | P2 | guard |
| 5.9 | `Products.php` | `note` vs `note[]` → array to `strip_tags` | `is_array` guard | guard |
| 5.10 | `Products.php` | `add_adjustment()` unset `$products` | Init; `isset` on POST keys | guard |
| 5.11 | `Products.php` | `qa_suggestions()` false product | Skip null; init array | guard |
| 5.12 | `Products_model.php` | `serial_no` undefined in `addAdjustment()` | `isset($_POST['serial_no'])` | guard |
| 5.13 | `Products.php` | `array_values(false)` TypeError | `is_array` ? array_values : `[]` | guard |
| 5.14 | `add_adjustment.php` | Bare `HTTP_REFERER` in view | P1 | guard |

#### 5.7 `Products.php` — season_id NOT NULL

**Old code:**
```php
'season_id' => $this->input->post('season'),
```

**New code:**
```php
'season_id' => $this->input->post('season') !== null && $this->input->post('season') !== '' ? $this->input->post('season') : 0,
```

#### 5.13 `Products.php` — print_barcodes

**Old code:**
```php
$codes = array_values($this->input->post('codes'));
```

**New code:**
```php
$post_codes = $this->input->post('codes');
$codes = is_array($post_codes) ? array_values($post_codes) : array();
```

---

## Module 6 — Purchases

**Tests:** `phase4_purchases_test.php` (13/13), `phase4_purchases_links_test.php` (20/20), `phase4_purchases_deep_test.php` (6/6), `phase4_purchases_return_deep_test.php` (7/7)  
**Status:** ✅ Module complete

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 6.1 | `Purchases.php` | 60× bare `HTTP_REFERER` | P1 → `site_url('purchases')` | guard |
| 6.2 | `Purchases.php` | `getPurchaseByID()` false | P3 pattern | guard |
| 6.3 | `Purchases.php` | `foreach ($rows)` on false | P2 | guard |
| 6.4 | `Purchases.php` | `default_printer` null | Null guard | guard |
| 6.5 | `Purchases.php` | `combine_pdf` used `sales_model` | `purchases_model` | guard |
| 6.6 | `Purchases.php` | `view_return()` no guard on false ID | Redirect + empty rows | guard |
| 6.7 | `Purchases.php` | foreach empty `inv_items` | Guards before `krsort` | guard |
| 6.8 | `Purchases.php` | `sizeof($_POST['product'])` without isset | `isset` ? sizeof : 0 | guard |
| 6.9 | `Purchases.php` | Payment methods false objects | Null guards | guard |
| 6.10 | `purchases/view_return.php` | **File missing** → 500 | Restored minimal view | restore |
| 6.11 | `purchases/view.php`, etc. | `foreach ($rows)` on false | P2 | guard |

#### 6.5 `Purchases.php` — wrong model for return rows

**Old code:**
```php
$return_rows = $this->sales_model->getPurchaseReturnItems($return_id);
```

**New code:**
```php
$return_rows = $this->purchases_model->getPurchaseReturnItems($return_id);
```

#### 6.8 `Purchases.php` — sizeof without isset

**Old code:**
```php
for ($i = 0; $i < sizeof($_POST['product']); $i++) {
```

**New code:**
```php
$product_count = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
for ($i = 0; $i < $product_count; $i++) {
```

#### 6.10 `themes/default/views/purchases/view_return.php` — restored view

**Old code:**
```php
// File did not exist — Purchases::view_return() called load->view → 500
```

**New code:**
```php
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <!-- minimal return purchase modal — same data keys controller passes -->
    <?php if (!empty($rows)) { foreach ($rows as $row) { ... } } ?>
</div>
```

---

## Module 7 — Reports

**Tests:** `phase4_reports_test.php` (~69/71 screens), `phase4_reports_links_test.php` (~22), `phase4_reports_deep_test.php` (**39/50** AJAX)  
**Status:** ✅ Upgraded (full screen sweep; deep AJAX mostly green)  
**Not touched:** `Reports_new.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 7.1 | `Reports.php` | 64× bare `HTTP_REFERER` | P1 → `site_url('reports')` | guard |
| 7.2 | `Reports.php` | `array_keys($sel_warehouse)` on object | `->id` | guard |
| 7.3 | `Reports.php` | `transfer_request()` undefined `$id` | `userdata('user_id')` | guard |
| 7.4 | `Reports.php` | foreach on false `getTotalsSale` | `if ($dueSales)` guards | guard |
| 7.5 | `Reports_model.php` | `brand_chart_details()` missing | Restored method | restore |
| 7.6 | `Reports_model.php` | `payment_option()` SELECT missing columns | `list_fields()` dynamic select | guard |
| 7.7 | `Reports_model.php` | `get_currency($id)` required | `$id = null` optional | guard |
| 7.8 | `Reports.php` | GST SQL `state` ambiguous | `comp.state` | guard |
| 7.9 | `Reports.php` | `getExpiryAlerts` wrong settings ref | `$this->Settings` | guard |
| 7.10 | `Reports.php` | `load_ajax_reports` extra args | Call methods without `$postData` | guard |
| 7.11 | `Reports.php` | `getProductsLedgers()` unset arrays | Init at start | guard |
| 7.12 | `Reports.php` | `getCustomerLedger()` undefined `$getData` | `$transactionData` from model | guard |
| 7.13 | `Reports_model.php` | `$getData = ''` → `array_column` TypeError | `$getData = array()` | guard |
| 7.14 | `Reports.php` | `fld()` mangled ISO date | Accept `Y-m-d` without `fld()` | guard |
| 7.15 | `Reports.php` | `products_orderReport()` missing `end_date` | Default `date('Y-m-d')` | guard |
| 7.16 | `phase4_test_lib.php` | CSRF only from form input | Parse DataTables JS token | test |
| 7.17 | `phase4_reports_*.php` | Wrong URLs; ~24 screens only | ~70 screens; correct URLs | test |

#### 7.1 `Reports.php` — HTTP_REFERER (64×)

**Old code:**
```php
redirect($_SERVER["HTTP_REFERER"]);
```

**New code:**
```php
redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('reports'));
```

#### 7.2 `Reports.php` — sel_warehouse TypeError

**Old code:**
```php
$key = array_keys($this->data['sel_warehouse']);
```

**New code:**
```php
$key = $this->data['sel_warehouse'] ? $this->data['sel_warehouse']->id : 0;
```

#### 7.3 `Reports.php` — transfer_request undefined `$id`

**Old code:**
```php
$user = $this->ion_auth->user($id)->row();
```

**New code:**
```php
$user = $this->ion_auth->user($this->session->userdata('user_id'))->row();
```

#### 7.5 `Reports_model.php` — brand_chart_details restored

**Old code:**
```php
// Method did not exist — Reports controller called brand_chart_details() → 500
```

**New code:**
```php
public function brand_chart_details($WarehouseId = 0, $Type = NULL) {
    $data = array();
    if ($Type == 'Monthly') {
        for ($i = 0; $i < 6; $i++) {
            $monthKey = date("Y-m", strtotime(date('Y-m-01') . " -$i months"));
            $start = date('Y-m-01', strtotime($monthKey));
            $end = date('Y-m-t', strtotime($monthKey));
            $rows = $this->sale_brand_chart_details($WarehouseId, $start, $end);
            $data[$monthKey] = $rows ? $rows : array();
        }
    } else {
        // daily branch → sale_brand_chart_details per day
    }
    return $data;
}
```

#### 7.6 `Reports_model.php` — payment_option dynamic columns

**Old code:**
```php
$this->db->select('authorize, instamojo, ccavenue, credit_card, ...');
$row = $this->db->get('pos_settings')->row_array();
// MySQL 1054: Unknown column when column missing in sma_pos_settings
```

**New code:**
```php
$existing = $this->db->list_fields('pos_settings');
$parts = array();
foreach ($wanted as $col => $alias) {
    if (in_array($col, $existing, true)) {
        $parts[] = ($col === $alias) ? $col : "{$col} as {$alias}";
    }
}
if ($parts) {
    $row = $this->db->select(implode(',', $parts))->get('pos_settings')->row_array();
}
```

#### 7.7 `Reports_model.php` — get_currency optional id

**Old code:**
```php
public function get_currency($id) {
    $this->db->where_in('id', $id);
```

**New code:**
```php
public function get_currency($id = null) {
    if ($id !== null) {
        $this->db->where_in('id', $id);
    }
```

#### 7.8 `Reports.php` — GST ambiguous `state`

**Old code:**
```php
$this->datatables->select("
    ...
    state,
    ...
");
```

**New code:**
```php
$this->datatables->select("
    ...
    comp.state,
    ...
");
```

#### 7.10 `Reports.php` — load_ajax_reports ArgumentCountError

**Old code:**
```php
case "ProductsLedgers":
    $this->getProductsLedgers($postData);
    break;
case "CustomerLedger":
    $this->getCustomerLedger($postData);
    break;
```

**New code:**
```php
case "ProductsLedgers":
    $this->getProductsLedgers();
    break;
case "CustomerLedger":
    $this->getCustomerLedger();
    break;
```

#### 7.12–7.14 `Reports.php` — getCustomerLedger date + data

**Old code:**
```php
$startDate = trim($this->sma->fld($start_date));
$transactionData = $this->reports_model->getCustomerLedger($customer_id, $startDate, $getData);
```

**New code:**
```php
if (preg_match('/^\d{4}-\d{2}-\d{2}/', $start_date)) {
    $startDate = substr($start_date, 0, 10);
    $enddate = ($end_date && preg_match('/^\d{4}-\d{2}-\d{2}/', $end_date)) ? substr($end_date, 0, 10) : date('Y-m-d');
} else {
    $startDate = trim($this->sma->fld($start_date));
    $enddate = trim($end_date ? $this->sma->fld($end_date) : date('Y-m-d'));
}
$transactionData = $this->reports_model->getCustomerLedger($customer_id, $startDate, $enddate);
```

#### 7.13 `Reports_model.php` — getCustomerLedger array init

**Old code:**
```php
$getData = '';
foreach ($combpinData as $key => $items) {
    $getData[] = $items;
}
$col = array_column($getData, "date"); // TypeError if $getData was string
```

**New code:**
```php
$getData = array();
foreach ($combpinData as $key => $items) {
    $getData[] = $items;
}
if (!empty($getData)) {
    $col = array_column($getData, "date");
    array_multisort($col, SORT_ASC, $getData);
}
```

#### 7.16 `phase4_test_lib.php` — CSRF from DataTables JS

**Old code:**
```php
function phase4_extractCsrf($html) {
    if (preg_match('/name="token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return null;
}
```

**New code:**
```php
function phase4_extractCsrf($html) {
    if (preg_match('/name="token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name="([^"]+)"\s+value="([^"]+)"[^>]*class="[^"]*token/i', $html, $m)) {
        return $m[2];
    }
    if (preg_match('/"name":\s*"token",\s*"value":\s*"([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return null;
}
```

### Module 7 — Known open (not PHP code)

| Item | Old | New / action | Type |
|------|-----|--------------|------|
| `get_products_combo_items_report` | MySQL view definer `'admin'@'localhost'` missing | Fix DB user or recreate view on WAMP | db-env |
| `customerDepositLedger` | Same definer / view family | DB admin task | db-env |
| Long `phase4_reports_*` batch | Session timeout → false 500 tail | Re-login every 10–12 screens in test script | test |

---

## Module 8 — Customers (CRM)

**Tests:** `phase4_customers_test.php` (**10/10**), `phase4_customers_links_test.php` (**19/19**) PASS  
**Status:** ✅ Module complete  
**Model:** `companies_model.php` (no `Customers_model.php`)

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 8.1 | `Customers.php` | 19× bare `HTTP_REFERER` redirect | P1 → `site_url('customers')` | guard |
| 8.2 | `Customers.php` | `getCompanyByID()` false → view/edit/deposit fatal | Early guard / error in modal | guard |
| 8.3 | `Customers.php` | `edit()` `row()->cf1` on missing row | `$original_row ? $original_row->cf1 : ''` | guard |
| 8.4 | `Customers.php` | `$cg->name` / `$pg->name` when group false | Null-safe `($cg && isset($cg->name))` | guard |
| 8.5 | `Customers.php` | `reset($warehouseArr)` → false `->primary_biller_id` | `is_array` + fallback default biller | guard |
| 8.6 | `Customers.php` | `getCustomer*` / `get_award_points` false row | `send_json([])` early return | guard |
| 8.7 | `Customers.php` | `getGiftBalance` null `getOPCLDeposit` | Ternary `0` for balances | guard |
| 8.8 | `Customers.php` | `supplier_key_accept()` undefined `$status` | Read from `input->post/get` | guard |
| 8.9 | `Customers.php` | `getAllCustomerGroups()` false → view foreach | `?: array()` on list data | guard |
| 8.10 | `Customers.php` | `index()` missing `$biller` for deposit receipt | Pass `default_biller` company | guard |
| 8.11 | `companies_model.php` | `getGiftCard()` `$get->giftbalance` when no row | `$get ? $get->giftbalance : null` | guard |
| 8.12 | `companies_model.php` | `order_by('balance', DESC)` undefined constant → 500 | `'desc'` string | syntax |
| 8.13 | `customers/index.php` | `$GP['bulk_actions']` / session receipt unguarded | `!empty()` guards | guard |
| 8.14 | `customers/view.php` | `$customer` false → property access | `!empty($customer)` wrapper | guard |
| 8.15 | `phase4_customers_*.php` | — | New screen + deep-link test scripts | test |

#### 8.1 `Customers.php` — HTTP_REFERER (19×)

**Old code:**
```php
redirect($_SERVER["HTTP_REFERER"]);
```

**New code:**
```php
redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('customers'));
```

#### 8.3 `Customers.php` — edit() cf1 row

**Old code:**
```php
$original_value = $this->db->select('cf1')->where('id', $id)->get('sma_companies')->row()->cf1;
```

**New code:**
```php
$original_row = $this->db->select('cf1')->where('id', $id)->get('sma_companies')->row();
$original_value = $original_row ? $original_row->cf1 : '';
```

#### 8.11–8.12 `companies_model.php` — getGiftCard + DESC constant

**Old code:**
```php
->order_by('balance', DESC)
// ...
return $get->giftbalance;
```

**New code:**
```php
->order_by('balance', 'desc')
// ...
return $get ? $get->giftbalance : null;
```

#### 8.10 `Customers.php` — index biller for deposit print

**Old code:**
```php
// index() did not set $biller — view used $biller->company → fatal after deposit
```

**New code:**
```php
$this->data['biller'] = $this->site->getCompanyByID($this->Settings->default_biller);
```

---

## Module 9 — Suppliers & Billers

**Tests:** `phase4_suppliers_billers_test.php` (**10/10**), `phase4_suppliers_billers_links_test.php` (**14/14**) PASS  
**Status:** ✅ Module complete  
**Controllers:** `Suppliers.php`, `Billers.php` — shared `companies_model.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 9.1 | `Suppliers.php` | 9× bare `HTTP_REFERER` | P1 → `site_url('suppliers')` | guard |
| 9.2 | `Billers.php` | 6× bare `HTTP_REFERER` | P1 → `site_url('billers')` | guard |
| 9.3 | `Suppliers.php` | `getCompanyByID` false in view/edit/users | Early guard / error modal | guard |
| 9.4 | `Suppliers.php` | `reset($warehouseArr)` → `->primary_biller_id` | `is_array` + default biller fallback | guard |
| 9.5 | `Suppliers.php` | `getSupplier` / `getSupplierName` false row | `send_json([])` | guard |
| 9.6 | `Suppliers.php` | `strpos($state, '~')` on null | Cast `(string)` + empty check | guard |
| 9.7 | `Suppliers.php` | `$_FILES['userfile']['size']` unset | `!empty($_FILES['userfile']['size'])` | guard |
| 9.8 | `Suppliers.php` | `add_warehouse` modal missing `$warehouse`, `$location_type` | Init defaults in controller | guard |
| 9.9 | `Suppliers.php` | Export loop `getCompanyByID` false | `if (!$customer) continue` | guard |
| 9.10 | `Billers.php` | `edit()` false `company_details` | Redirect if not found | guard |
| 9.11 | `Billers.php` | `getBiller` false row | `send_json([])` | guard |
| 9.12 | `Companies_model.php` | `getSupplierSuggestions` / `getBillerSuggestions` no return | `return array()` | guard |
| 9.13 | `suppliers/index.php`, `view.php` | `$GP[...]` unguarded | `!empty($GP[...])` | guard |
| 9.14 | `phase4_suppliers_billers_*.php` | — | New screen + deep-link scripts | test |

#### 9.1 `Suppliers.php` — HTTP_REFERER (9×)

**Old code:**
```php
redirect($_SERVER["HTTP_REFERER"]);
```

**New code:**
```php
redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('suppliers'));
```

#### 9.4 `Suppliers.php` — warehouse biller chain

**Old code:**
```php
$warehouse = reset($warehouseArr);
$biller_details = $this->site->getCompanyByID($warehouse->primary_biller_id);
```

**New code:**
```php
$warehouse = is_array($warehouseArr) ? reset($warehouseArr) : false;
$biller_details = ($warehouse && !empty($warehouse->primary_biller_id))
    ? $this->site->getCompanyByID($warehouse->primary_biller_id)
    : $this->site->getCompanyByID($this->Settings->default_biller);
```

#### 9.12 `Companies_model.php` — empty suggestions

**Old code:**
```php
if ($q->num_rows() > 0) {
  // ...
  return $data;
}
// implicit null return
```

**New code:**
```php
if ($q->num_rows() > 0) {
  // ...
  return $data;
}
return array();
```

---

## Module 10 — Quotes

**Tests:** `phase4_quotes_test.php` (**6/6**), `phase4_quotes_links_test.php` (**10/10**) PASS  
**Status:** ✅ Module complete  
**Controllers:** `Quotes.php` — `Quotes_model.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 10.1 | `Quotes.php` | 10× bare `HTTP_REFERER` | P1 → `site_url('quotes')` | guard |
| 10.2 | `Quotes.php` | `getQuoteByID()` false in view/pdf/email/edit | Early flash + redirect | guard |
| 10.3 | `Quotes.php` | `getAllQuoteItems()` false | `?: array()` (6×) | guard |
| 10.4 | `Quotes.php` | `$colors->name` on false option | Ternary on `$colors` | guard |
| 10.5 | `Quotes.php` | `default_printer` null → property | `!empty()` guard (4×) | guard |
| 10.6 | `Quotes.php` | `$inv->return_id` unset | `isset(...) ? ... : null` in tax calls | guard |
| 10.7 | `Quotes.php` | `combine_pdf` missing quote | `if (!$inv) continue` | guard |
| 10.8 | `Quotes.php` | Export loop false quote | `if (!$qu) continue` | guard |
| 10.9 | `Quotes.php` | `suggestions()` false customer/warehouse/group | `send_json` no_match + return | guard |
| 10.10 | `Quotes.php` | `$options[0]`, price_group_id, combo foreach | `reset()`, `!empty()`, `!empty($combo_items)` | guard |
| 10.11 | `Quotes.php` | `$_FILES['document']['size']` unset | `!empty($_FILES['document']['size'])` | guard |
| 10.12 | `Quotes.php` | `edit` else: `krsort` on false, `$unitData->name` | `?: array()`, `!empty` krsort, ternary | guard |
| 10.13 | `Quotes.php` | `index()` wrong warehouse id for non-owner | `getWarehouseByID($this->data['warehouse_id'])` | guard |
| 10.14 | `Quotes.php` | `(Float)` cast | `(float)` (4×) | syntax |
| 10.15 | `Quotes_model.php` | `getProductOptionscolor` undefined `$all` | Remove `$all` from overselling check | guard |
| 10.16 | `quotes/index.php` | `$GP['bulk_actions']`, warehouse title | `!empty($GP...)`, `is_object($warehouse)` | guard |
| 10.17 | `quotes/email.php` | bare `HTTP_REFERER` hidden field | `isset(...) ? ... : ''` | guard |
| 10.18 | `phase4_quotes_*.php` | — | New screen + deep-link scripts | test |

#### 10.2 `Quotes.php` — getQuoteByID guard

**Old code:**
```php
$inv = $this->quotes_model->getQuoteByID($quote_id);
if (!$this->session->userdata('view_right')) {
    $this->sma->view_rights($inv->created_by);
}
```

**New code:**
```php
$inv = $this->quotes_model->getQuoteByID($quote_id);
if (!$inv) {
    $this->session->set_flashdata('error', lang('no_quote_selected'));
    redirect('quotes');
}
if (!$this->session->userdata('view_right')) {
    $this->sma->view_rights($inv->created_by);
}
```

#### 10.15 `Quotes_model.php` — getProductOptionscolor

**Old code:**
```php
if (!$this->Settings->overselling && !$all) {
```

**New code:**
```php
if (!$this->Settings->overselling) {
```

---

## Module 11 — Transfers

**Tests:** `phase4_transfers_test.php` (**10/10**), `phase4_transfers_links_test.php` (**12/12**) PASS  
**Status:** ✅ Module complete (PRIMARY `Transfers.php` only; `Transfersnew.php` deferred)  
**Controllers:** `Transfers.php` — `Transfers_model.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 11.1 | `Transfers.php` | 34× bare `HTTP_REFERER` | P1 → `site_url('transfers')` | guard |
| 11.2 | `Transfers.php` | `getTransferByID()` false before `->property` | Early flash + redirect (view/pdf/email/edit/delete/update_status/view_report) | guard |
| 11.3 | `Transfers.php` | `getTransferRequestByID()` false | Early flash + redirect (edit/view/cancel/delete request) | guard |
| 11.4 | `Transfers.php` | `combine_pdf` / export loops missing transfer | `if (!$transfer) continue` | guard |
| 11.5 | `Transfers.php` | `getTransferCompletedItems()` null | `?: array()` in edit | guard |
| 11.6 | `Transfers.php` | `getAllTransferRequestItems()` false + `krsort` | `?: array()` before krsort | guard |
| 11.7 | `Transfers.php` | `$colors->name` on false option | `if ($colors)` guard (view/pdf/combine) | guard |
| 11.8 | `Transfers.php` | `$pr` unset before item loops | `$pr = array()` (edit, edit_request, suggestions) | guard |
| 11.9 | `Transfers.php` | `getstockwarehousedata` null `$sql` | Return 0 when row missing | guard |
| 11.10 | `Transfers.php` | `suggestions()` `$opt`/`$opt_color` false | Guard before `->id`/`->name`; safe `reset($options)` | guard |
| 11.11 | `Transfers.php` | `calcel_request` ajax success echoes `$error` | Echo `$msg` | guard |
| 11.12 | `Transfers_model.php` | Implicit null from request/report/completed items | `return array();` (3 methods) | guard |
| 11.13 | `transfers/index.php` | `$GP['bulk_actions']` unset | `!empty($GP['bulk_actions'])` | guard |
| 11.14 | `phase4_transfers_*.php` | — | New screen + deep-link scripts | test |

#### 11.1 `Transfers.php` — HTTP_REFERER

**Old code:**
```php
redirect($_SERVER["HTTP_REFERER"]);
```

**New code:**
```php
redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('transfers'));
```

#### 11.2 `Transfers.php` — getTransferByID guard

**Old code:**
```php
$transfer = $this->transfers_model->getTransferByID($transfer_id);
if (!$this->session->userdata('view_right')) {
    $this->sma->view_rights($transfer->created_by, true);
}
```

**New code:**
```php
$transfer = $this->transfers_model->getTransferByID($transfer_id);
if (!$transfer) {
    $this->session->set_flashdata('error', lang('no_transfer_selected'));
    redirect('transfers');
}
if (!$this->session->userdata('view_right')) {
    $this->sma->view_rights($transfer->created_by, true);
}
```

#### 11.11 `Transfers.php` — calcel_request ajax echo

**Old code:**
```php
if($this->input->is_ajax_request()) {
    echo $error; die();
}
```

**New code:**
```php
if($this->input->is_ajax_request()) {
    echo $msg; die();
}
```

#### 11.12 `Transfers_model.php` — implicit null returns

**Old code:**
```php
        if ($q->num_rows() > 0) {
            ...
            return $data;
        }
    }
```

**New code:**
```php
        if ($q->num_rows() > 0) {
            ...
            return $data;
        }
        return array();
    }
```
(applied in `getTransferCompletedItems`, `getAllTransferRequestItems`, `getAllTransferReportItems`)

---

## Test scripts index (Modules 1–11)

| Module | Scripts |
|--------|---------|
| 1 Auth | `phase4_auth_test.php`, `phase4_auth_links_test.php` |
| 2 Welcome | `phase4_welcome_links_test.php` |
| 3 POS | `phase4_pos_test.php`, `phase4_pos_deep_test.php`, `phase4_pos_links_test.php` |
| 4 Sales | `phase4_sales_test.php`, `phase4_sales_deep_test.php`, `phase4_sales_links_test.php` |
| 5 Products | `phase4_products_test.php`, `phase4_products_deep_test.php`, `phase4_products_stock_deep_test.php`, `phase4_products_links_test.php` |
| 6 Purchases | `phase4_purchases_test.php`, `phase4_purchases_deep_test.php`, `phase4_purchases_links_test.php`, `phase4_purchases_return_deep_test.php` |
| 7 Reports | `phase4_reports_test.php`, `phase4_reports_deep_test.php`, `phase4_reports_links_test.php` |
| 8 Customers | `phase4_customers_test.php`, `phase4_customers_links_test.php` |
| 9 Suppliers & Billers | `phase4_suppliers_billers_test.php`, `phase4_suppliers_billers_links_test.php` |
| 10 Quotes | `phase4_quotes_test.php`, `phase4_quotes_links_test.php` |
| 11 Transfers | `phase4_transfers_test.php`, `phase4_transfers_links_test.php` |
| Shared | `phase4_test_lib.php` |

**Run:** `cd upgrade && php phase4_<module>_*.php Admin "Admin@554"`

---

## Changelog

| Date | Module | Summary |
|------|--------|---------|
| 2026-07-07 | 1–7 | Initial fixes log — descriptions only |
| 2026-07-07 | 1–7 | **Old code / New code** snippets added; common patterns P1–P6 |
| 2026-07-07 | 8 | Module 8 Customers — guards + companies_model getGiftCard; tests 10/10 + 19/19 |
| 2026-07-07 | 9 | Module 9 Suppliers & Billers — HTTP_REFERER, getCompanyByID guards; tests 10/10 + 14/14 |
| 2026-07-07 | 10 | Module 10 Quotes — HTTP_REFERER, getQuoteByID guards, suggestions; tests 6/6 + 10/10 |
| 2026-07-07 | 11 | Module 11 Transfers — HTTP_REFERER, getByID guards, model empty arrays; tests 10/10 + 12/12 |

---

*Next module fixes → append summary row + **Old code** / **New code** blocks under **Module N** and add a **Changelog** line.*
