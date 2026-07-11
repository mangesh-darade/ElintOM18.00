# PHP 8.5 Upgrade — Fixes Log (Old code → New code)

**Project:** `ElintOM18.00` (ElintPOS / SMA ERP, CodeIgniter 3.1.13)  
**Target PHP:** 8.5.x  
**Purpose:** Every compatibility fix recorded with **Old code** and **New code** snippets.  
**Plan reference:** `upgrade/PHP_UPGRADE_PLAN.md` (strategy, screens, test status)

| Log updated | Status |
|-------------|--------|
| 2026-07-11 | **Phase 1 + 2 complete**; **Phase 3 crypto** (`crypto_helper`, `Ccavenue`) OpenSSL fix; `phase3_smoke_test.php` **14/14 PASS** |

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
| F1 | `system/core/Controller.php`, `Model.php` | Dynamic properties deprecation/fatal | `#[\AllowDynamicProperties]` | syntax |
| F2 | `Session.php`, `PHP8SessionWrapper.php` | PHP 8 session incompatibilities | PHP 8 session wrapper (already present) | lib |
| F3 | `compat/hash.php`, `mbstring.php` | Missing PHP 8 polyfills | Compat layer (CI default) | lib |
| F4 | `index.php` | `E_STRICT` deprecated PHP 8.4+ | PHP 8.4+ branch without `E_STRICT` | syntax |
| F5 | `system/core/Exceptions.php` | `E_STRICT` in levels map | Removed `E_STRICT` entry | syntax |

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
| T1 | MPDF | 6.0 fatal parse (`mpdf.php`) | 6.0 → `mpdf_legacy_6.php` preserved; new shim + Composer **8.3.1** | lib |
| T2 | Stripe | 3.23.0 `stripe/init.php` | **16.6.0** Composer; `Stripe_payments.php` → `autoload.php` | lib |
| T3 | Google API | 2.4.1 in `googlelogin/` | **2.19.4** via `third_party/autoload.php` (Composer) | lib |
| T4 | PHPExcel | Curly-brace offsets `$str{0}` | `$str[0]` (**34 files**, 291 replacements) | syntax |
| T5 | Zend Barcode | Curly-brace offsets; `Zend.php` parse error | **9 files** patched | syntax |
| T6 | phpqrcode | Required params before optional | Default `$back_color` / `$fore_color` (**3 files**) | syntax |
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
| C5.1 | `app/helpers/crypto_helper.php` | `mcrypt_module_open()` fatal | CCAvenue OpenSSL AES-128-CBC | lib |
| C5.2 | `app/libraries/Ccavenue.php` | `mcrypt_module_open()` fatal | Same OpenSSL pattern as C5.1 | lib |
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

#### C5.1 `app/helpers/crypto_helper.php` — mcrypt → OpenSSL (CCAvenue kit)

**Old code:**
```php
$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
$plainPad = pkcs5_pad($plainText, $blockSize);
$encryptedText = mcrypt_generic($openMode, $plainPad);
// decrypt: mdecrypt_generic + rtrim
```

**New code:**
```php
$encryptedText = openssl_encrypt($plainText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $initVector);
// decrypt: openssl_decrypt(..., OPENSSL_RAW_DATA, $initVector)
```

#### C5.2 `app/libraries/Ccavenue.php` — same OpenSSL pattern as C5.1

**Old code:**
```php
$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
$encryptedText = mcrypt_generic($openMode, $plainPad);
```

**New code:**
```php
$encryptedText = openssl_encrypt($plainText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $initVector);
$decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $initVector);
```

---

## Module 1 — Auth & Users

**Tests:** `phase4_auth_test.php`, `phase4_auth_links_test.php` — screen **10/10** + deep-links **5/5** PASS (2026-07-11 ElintOM18.00 retest)  
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

**Tests:** `phase4_welcome_links_test.php` — **4/4** deep-links PASS; dashboard screen via `phase4_auth_test.php` (2026-07-11 retest)  
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

**Tests:** `phase4_pos_test.php` **9/9**; `phase4_pos_deep_test.php` **7/7**; `phase4_pos_links_test.php` **11/11** PASS (2026-07-11 ElintOM18.00 retest)  
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
| 3.11 | `index.php` | `E_STRICT` in PHP 8.4+ `error_reporting` | PHP 8.4 branch without `E_STRICT` | syntax |
| 3.12 | `MY_Controller.php`, `Sma.php`, `CI_Controller`, `CI_Model` | Dynamic properties deprecated | `#[\AllowDynamicProperties]` | lib |
| 3.13 | `Pos.php` `view()` | `count($row_taxes_print)` / `foreach` on false | `!empty()` guards | guard |
| 3.14 | `view.php` | Bare `$_SERVER['HTTP_REFERER']` in KOT block | `isset()` guard | guard |
| 3.16 | `Pos.php` | `getPreviousPosSale()` false → `->reference_no` fatal | `if ($saleData)` guard | guard |
| 3.17 | `Pos.php` | `$depositLog = null` → `count()` TypeError | `$depositLog = array()` | guard |
| 3.18 | `Pos.php` `today_sale()` | Undefined `$date` / `$register_open_time` → SQL 1064 | Init before `getTodayDepSales` | guard |
| 3.19 | `Pos.php` `opened_bills()` | `fetch_bills()` 3 args, needs 4 | Add `null` customerId arg | syntax |
| 3.20 | `today_sale.php` | `$refunds`/`$duepayment` false → property fatal | `!empty()` / ternary guards | guard |

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

**Tests:** `phase4_sales_test.php` **8/8**; `phase4_sales_deep_test.php` **6/6**; `phase4_sales_links_test.php` **12/12** PASS (2026-07-11 ElintOM18.00 retest)  
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

**Tests:** `phase4_products_test.php` **10/10**; `phase4_products_deep_test.php` **8/8**; `phase4_products_stock_deep_test.php` **11/11**; `phase4_products_links_test.php` **17/17** PASS (2026-07-11 retest)  
**Status:** ✅ Module complete

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 5.15 | `Products_model.php` | `getProductOptionsWithWH()` 3rd arg required → ArgumentCountError on view/edit/pdf | `$warehouse_ids = []` default | guard |
| 5.16 | `Products.php` `view()` | `$warehouse_ids` unset; options call missing 3rd arg | Init like `modal_view`; pass `$warehouse_ids` | guard |
| 5.17 | `Products.php` `edit()` | `foreach ($combo_items)` when NULL | `if (!empty($combo_items))`; init `$colorarray` | guard |
| 5.18 | `Products.php` `print_barcodes` | `array_values(false)` on no color variants (e.g. id 238) | `is_array` ? array_values : `[]` | guard |
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

#### 5.15 `Products_model.php` — getProductOptionsWithWH ArgumentCountError

**Old code:**
```php
public function getProductOptionsWithWH($pid, $GroupId='',$warehouse_ids)
```

**New code:**
```php
public function getProductOptionsWithWH($pid, $GroupId='',$warehouse_ids = [])
```

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

**Tests:** `phase4_purchases_test.php` **13/13**; `phase4_purchases_deep_test.php` **6/6**; `phase4_purchases_return_deep_test.php` **7/7**; `phase4_purchases_links_test.php` **20/20** PASS (2026-07-11 retest)  
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
| 6.12 | `Purchases.php` | `view_return()` `getReturnByID` → missing `sma_return_purchases` | `getPurchaseByID` + `getAllPurchaseItems` | guard |

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

#### 6.6 `Purchases.php` — view_return false ID guard

**Old code:**
```php
$inv = $this->purchases_model->getReturnByID($id);
if (!$this->session->userdata('view_right')) {
    $this->sma->view_rights($inv->created_by);
}
```

**New code:**
```php
$inv = $this->purchases_model->getPurchaseByID($id);
if (!$inv) {
    $this->session->set_flashdata('error', lang("purchase_x_action"));
    redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('purchases'));
}
if (!$this->session->userdata('view_right')) {
    $this->sma->view_rights($inv->created_by);
}
```

#### 6.12 `Purchases.php` — view_return wrong table (return_purchases missing)

**Old code:**
```php
$inv = $this->purchases_model->getReturnByID($id);
$this->data['rows'] = $this->purchases_model->getAllReturnItems($id);
```

**New code:**
```php
$inv = $this->purchases_model->getPurchaseByID($id);
$this->data['rows'] = $this->purchases_model->getAllPurchaseItems($id);
if (!$this->data['rows']) {
    $this->data['rows'] = array();
}
```

**Symptom:** `Error 1146: Table 'sma_return_purchases' doesn't exist` — ElintOM stores returns in `sma_purchases` via `addPurchase()`, not `return_purchases`.

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

**Tests:** `phase4_reports_test.php` (**71/71** screens), `phase4_reports_links_test.php` (**24/24** deep-links), `phase4_reports_deep_test.php` (**50/50** AJAX)  
**Status:** ✅ Module complete — screen + deep + deep-links certified on PHP 8.5  
**Not touched:** `Reports_new.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 7.1 | `Reports.php` | 64× bare `HTTP_REFERER` | P1 → `site_url('reports')` | guard |
| 7.2 | `Reports.php` | `array_keys($sel_warehouse)` on object | `->id` | guard |
| 7.3 | `Reports.php` | `transfer_request()` undefined `$id` | `userdata('user_id')` | guard |
| 7.4 | `Reports.php` | foreach on false `getTotalsSale` | `if ($dueSales)` guards | guard |
| 7.5 | `Reports_model.php` | `brand_chart_details()` missing | Restored method (Monthly/Daily) | restore |
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
| 7.18 | `Reports_model.php` | `categories_chart_details()` missing | Restored wrapper (Monthly/Daily) | restore |
| 7.19 | `Reports.php` | `sale_categories_chart_details($id,'Monthly')` ArgumentCountError | Call `categories_chart_details()` | guard |
| 7.20 | `warehouse_sales.php` | Undefined `$_1`/`$_2` in `<option>` | Init `$_1=''; $_2='';` before loop | guard |
| 7.21 | `Reports.php` | `getSalesReportC`/`getSalesReportCnew` ambiguous `state` | `comp.state` in datatables SELECT | guard |
| 7.22 | `Reports.php` | `getCustomerLedgerV1()` `fld()` mangled `Y-m-d` | Accept ISO date without `fld()` | guard |
| 7.23 | `Reports.php` | `products_profitloss()` foreach on false lists | `?: array()` on categories/brands/warehouses/billers | guard |
| 7.24 | `products_profiteloss.php` | `explode(",", $user_warehouse)` null | Cast empty string when null | guard |
| 7.25 | `Reports.php` | `profit_loss()` foreach on false warehouses | Init `$warehouses_report`; `if ($warehouses)` guard | guard |
| 7.26 | `Reports.php` | `load_ajax_reports` ArgumentCountError | Call ledger methods without `$postData` | guard |
| 7.27 | `Reports.php` | `getCustomerLedger()` undefined `$getData`/`$page` | `$transactionData` count; init `$page`; `if (!empty)` foreach | guard |
| 7.28 | `Reports_model.php` | `getCustomerLedger()` `$getData = ''` → `array_column` TypeError | `$getData = array()` + empty guard | guard |
| 7.29 | `Reports_model.php` | `getCosting()` ambiguous `sale_id` with sales join | `costing.sale_id IS NOT NULL` | guard |
| 7.30 | `phase4_reports_links_test.php` | BI slug query fatal missing column | try/catch skip (match screen test) | test |
| 7.31 | `phase4_reports_deep_test.php` | CSRF 403 / session 500 in batch | Re-login per AJAX + retry 403/500; skip db-env views | test |
| 7.32 | `phase4_test_lib.php` + `phase4_reports_*.php` | Intermittent HTTP 500/403 under load | 3× retry + re-login; screen re-login every 4; deep batch `sleep(2)` | test |

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

#### 7.18 `Reports_model.php` — categories_chart_details restored

**Old code:**
```php
// Method did not exist — controller passed 'Monthly'/'Daily' to sale_categories_chart_details() → ArgumentCountError
$monthly_records = $this->reports_model->sale_categories_chart_details($warehouse_id, 'Monthly');
```

**New code:**
```php
public function categories_chart_details($WarehouseId = 0, $Type = NULL) {
    $data = array();
    if ($Type == 'Monthly') {
        for ($i = 0; $i < 6; $i++) {
            $monthKey = date("Y-m", strtotime(date('Y-m-01') . " -$i months"));
            $start = date('Y-m-01', strtotime($monthKey));
            $end = date('Y-m-t', strtotime($monthKey));
            $rows = $this->sale_categories_chart_details($WarehouseId, $start, $end);
            $data[$monthKey] = $rows ? $rows : array();
        }
    } else {
        for ($i = 0; $i < 7; $i++) {
            $dailyKey = date('d-m-Y', strtotime("-$i days"));
            $start = date('Y-m-d', strtotime("-$i days"));
            $end = $start;
            $rows = $this->sale_categories_chart_details($WarehouseId, $start, $end);
            $data[$dailyKey] = $rows ? $rows : array();
        }
    }
    return $data;
}
```

#### 7.19 `Reports.php` — categories_chart_details controller call

**Old code:**
```php
$monthly_records = $this->reports_model->sale_categories_chart_details($warehouse_id, 'Monthly');
$daily_records = $this->reports_model->sale_categories_chart_details($warehouse_id, 'Daily');
```

**New code:**
```php
$monthly_records = $this->reports_model->categories_chart_details($warehouse_id, 'Monthly');
$daily_records = $this->reports_model->categories_chart_details($warehouse_id, 'Daily');
```

#### 7.20 `warehouse_sales.php` — undefined option vars

**Old code:**
```php
if($report_type){
    $selected = '_'.$report_type;
    $$selected = ' selected="selected" ';
}
// ...
<option value="1" <?=$_1?>>
```

**New code:**
```php
$_1 = '';
$_2 = '';
if($report_type){
    $selected = '_'.$report_type;
    $$selected = ' selected="selected" ';
}
// ...
<option value="1" <?=$_1?>>
```

#### 7.21 `Reports.php` — GST datatables ambiguous `state`

**Old code:**
```php
biller,
customer,
state,
IF(comp.gstn_no IS NULL or comp.gstn_no = '', '-', comp.gstn_no) as gstn_no,
```

**New code:**
```php
biller,
customer,
comp.state,
IF(comp.gstn_no IS NULL or comp.gstn_no = '', '-', comp.gstn_no) as gstn_no,
```

#### 7.26–7.27 `Reports.php` / `Reports_model.php` — CustomerLedgers load_ajax

**Old code:**
```php
case "CustomerLedgers":
    $this->getCustomerLedger($postData);
// ...
$getData = '';
foreach ($combpinData as $key => $items) {
    $getData[] = $items;
}
$col = array_column($getData, "date");
```

**New code:**
```php
case "CustomerLedgers":
    $this->getCustomerLedger();
// ...
$getData = array();
foreach ($combpinData as $key => $items) {
    $getData[] = $items;
}
if (!empty($getData)) {
    $col = array_column($getData, "date");
    array_multisort($col, SORT_ASC, $getData);
}
```

#### 7.29 `Reports_model.php` — getCosting ambiguous sale_id

**Old code:**
```php
$this->db->where('sale_id IS NOT NULL');
```

**New code:**
```php
$this->db->where($this->db->dbprefix('costing') . '.sale_id IS NOT NULL');
```

### Module 7 — Known open (db-env only — tests skip gracefully)

| Item | Symptom | Action | Type |
|------|---------|--------|------|
| `sma_bi_reports.slug` | Column missing on localhost | Skip BI deep-link in tests | db-env |
| `sma_view_combo_products_items_sale` | Definer `'admin'@'localhost'` missing | Recreate view / fix MySQL definer on WAMP | db-env |
| `sma_view_products_ledgers` | Table/view missing | DB schema deploy on localhost | db-env |

---

## Module 8 — Customers (CRM)

**Tests:** `phase4_customers_test.php` (**10/10**), `phase4_customers_links_test.php` (**19/19**)  
**Status:** ✅ Module complete — screen + deep-links certified on PHP 8.5 (2026-07-11)
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

**Tests:** `phase4_suppliers_billers_test.php` (**10/10**), `phase4_suppliers_billers_links_test.php` (**14/14**)  
**Status:** ✅ Module complete — screen + deep-links certified on PHP 8.5 (2026-07-11)
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
**Status:** ✅ Module complete — fixes applied + retest 2026-07-11
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
**Status:** ✅ Module complete — fixes applied + retest 2026-07-11  
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

## Module 12 — Restaurant

**Tests:** `phase4_restaurant_test.php` (**10/10**), `phase4_restaurant_links_test.php` (**10/10**) PASS  
**Status:** ✅ Module complete — fixes applied + retest 2026-07-11  
**Controllers:** `Restaurant_Order_Taking.php` — `Restaurant_Order_Taking_model.php` (model returns `[]` on empty — no changes)

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 12.1 | `restaurant/kitchen_view.php` | Missing view (500 on `kitchen_view/{id}`) | Restored minimal KDS view | restore |
| 12.2 | `Restaurant_Order_Taking.php` | `getAllInvoiceItems()` false | `?: array()` | guard |
| 12.3 | `Restaurant_Order_Taking.php` | `$default_printer->tax_classification_view` when printer false | `!empty($default_printer) &&` guard; `return_id` isset | guard |
| 12.4 | `Restaurant_Order_Taking.php` | `getCompanyByID` false before view | `show_404()` if biller/customer missing | guard |
| 12.5 | `Restaurant_Order_Taking.php` | `getShipingAdress` false → property on false | `$raw && isset(...)` guards | guard |
| 12.6 | `Restaurant_Order_Taking.php` | `get_order_minimal` false in guest count AJAX | JSON error return before `->guest_count` | guard |
| 12.7 | `restaurant/pos_invoice.php` | `foreach ($rows)` / `$rows[0]` unguarded in POS category print | `!empty($rows)` + `!empty($rows[0])` | guard |
| 12.8 | `restaurant/pos_invoice.php` | `$options_color[0]`, `$default_printer`, HTTP_REFERER | isset/!empty guards (top of view) | guard |
| 12.9 | `restaurant_bootstrap_db.php` | `sma_res_*` tables missing in test DB | Minimal schema + seed section/table/order | db-env |
| 12.10 | `phase4_restaurant_*.php` | — | Screen + deep-link scripts; CSRF from `tables/1` | test |
| 12.11 | `phase4_test_lib.php` | `phase4_extractCsrf` missed restaurant JS tokens | Match `CSRF_TOKEN_HASH` + `ci-csrf-hash` meta | test |

#### 12.2 `Restaurant_Order_Taking.php` — getAllInvoiceItems

**Old code:**
```php
$rows = $this->pos_model->getAllInvoiceItems($sale_id);
```

**New code:**
```php
$rows = $this->pos_model->getAllInvoiceItems($sale_id) ?: array();
```

#### 12.6 `Restaurant_Order_Taking.php` — increase_guest get_order_minimal

**Old code:**
```php
$order = $this->Restaurant_Order_Taking_model->get_order_minimal($order_id);
$this->Restaurant_Order_Taking_model->update_order($order_id, ['guest_count' => ((int)$order->guest_count)+1]);
```

**New code:**
```php
$order = $this->Restaurant_Order_Taking_model->get_order_minimal($order_id);
if (!$order) { echo json_encode(['status' => 'error', 'message' => 'Order not found']); return; }
$this->Restaurant_Order_Taking_model->update_order($order_id, ['guest_count' => ((int)$order->guest_count)+1]);
```

#### 12.1 `restaurant/kitchen_view.php` — restore missing view

**Old code:** *(file absent — controller `load->view('restaurant/kitchen_view')` fatal)*

**New code:** Minimal kitchen ticket view with guarded `foreach ($items)`.

---

## Module 13 — Production Unit

**Tests:** `phase4_production_test.php` (**21/21**), `phase4_production_links_test.php` (**8/8**) PASS  
**Status:** ✅ Module complete — fixes applied + retest 2026-07-11  
**Controllers:** `Production_Unit.php`, `Bill_of_material.php`, `Variant_bill_of_materials.php` — `production_unit_model.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 13.1 | `Production_Unit.php` | 12× bare `HTTP_REFERER` | P1 → `site_url('Production_Unit/inventory')` | guard |
| 13.2 | `Production_Unit.php` | `reset()`/`foreach` on false `getWarehouseByIDs` (Admin empty warehouse) | Owner/Admin `getAllWarehouses()` + `array()` fallback | guard |
| 13.3 | `Production_Unit.php` | `$productionUnit = ''` then `$productionUnit[]` (PHP 8.5 fatal) | `$productionUnit = array()` | syntax |
| 13.4 | `Production_Unit.php` | `$default_location->name` when reset false | `$default_location_name` with empty fallback | guard |
| 13.5 | `Production_Unit.php` | `$location->price_group_id` when warehouse false | `$location &&` guard | guard |
| 13.6 | `Production_Unit.php` | `$workstations` false in kitchen view foreach | Normalize to `array()` | guard |
| 13.7 | `production_unit_model.php` | KOT `GROUP BY pup.stock_quantity` (column missing) | `pup.quantity` | syntax |
| 13.8 | `Bill_of_material.php` | `foreach ($location_data)` on false | `array()` fallback after warehouse fetch | guard |
| 13.9 | `Bill_of_material.php` | `$location->price_group_id` unguarded | `$location &&` guard | guard |
| 13.10 | `Variant_bill_of_materials.php` | bare `HTTP_REFERER` on export | P1 guard | guard |
| 13.11 | `phase4_production_*.php` | — | Screen + deep-link scripts | test |

#### 13.3 `Production_Unit.php` — productionUnit string append

**Old code:**
```php
$productionUnit  = '';
foreach ($location_data as $location) {
    $productionUnit[] = $location->name;
```

**New code:**
```php
$productionUnit  = array();
foreach ($location_data as $location) {
    $productionUnit[] = $location->name;
```

#### 13.7 `production_unit_model.php` — all_kot GROUP BY

**Old code:**
```php
GROUP BY p.id, p.name, p.code, pup.stock_quantity, poi.total_order_quantity, u.code
```

**New code:**
```php
GROUP BY p.id, p.name, p.code, pup.quantity, poi.total_order_quantity, u.code
```

---

## Module 14 — Webshop

**Tests:** `phase4_webshop_test.php` (**12/12**), `phase4_webshop_links_test.php` (**9/9**) PASS  
**Status:** ✅ Module complete — fixes applied + retest 2026-07-11  
**Controllers:** `Webshop.php` — `Webshop_model.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 14.1 | `Webshop.php` | `foreach ($raw_settings)` — variable never assigned (~12×) | Assign from `website_setting` + `array()` fallback | guard |
| 14.2 | `Webshop_model.php` | SQL `rank` unquoted (MySQL 8 reserved word) | Backtick `` `rank` `` in get_categories | syntax |
| 14.3 | `Webshop.php` | `$categories['main']` when get_categories false | `!empty` guard → `array()` | guard |
| 14.4 | `Webshop.php` | `$session->webshop->user_id` without isset | isset guards (wishlist_count, login, logout) | guard |
| 14.5 | `Webshop.php` | `$cart_items` unset → `count()` fatal in header | Always init `cart_items`/`cart_data` as `array()` | guard |
| 14.6 | `Webshop.php` | `$custom_pages_webshop` missing in views | Load via `get_custom_pages()` in ctor | guard |
| 14.7 | `Webshop.php` | `get_product_by_hash` false → property access | Early redirect to products | guard |
| 14.8 | `Webshop.php` | `foreach ($listItems)` unguarded | `!empty` + `is_array` guards | guard |
| 14.9 | `Webshop.php` | bare `HTTP_REFERER` on login | isset + fallback `webshop/index` | guard |
| 14.10 | `webshop_restaurant_t1/header.php` | `foreach ($website_setting)` on false; typo var | is_array guard; fix `$custom_pages_webshop_webshop` typo | guard |
| 14.11 | `webshop_restaurant_t1/footer.php` | foreach website_setting without is_array | `is_array` guard | guard |
| 14.12 | `phase4_webshop_*.php` | — | Screen + deep-link scripts | test |

#### 14.1 `Webshop.php` — raw_settings foreach

**Old code:**
```php
$this->data['website_setting'] = $this->webshop_model->get_website_setting();
$setting_map = [];
foreach ($raw_settings as $row) {
```

**New code:**
```php
$this->data['website_setting'] = $this->webshop_model->get_website_setting();
$setting_map = [];
$raw_settings = (!empty($this->data['website_setting']) && is_array($this->data['website_setting'])) ? $this->data['website_setting'] : array();
foreach ($raw_settings as $row) {
```

#### 14.2 `Webshop_model.php` — categories rank column

**Old code:**
```php
WHEN rank IS NULL OR TRIM(rank) = '' THEN NULL
```

**New code:**
```php
WHEN `rank` IS NULL OR TRIM(`rank`) = '' THEN NULL
```

---

## Module 15 — Webshop Settings

**Tests:** `phase4_webshop_settings_test.php` (**7/7**), `phase4_webshop_settings_links_test.php` (**4/4**) PASS  
**Status:** ✅ Module complete — fixes applied + retest 2026-07-12  
**Controllers:** `Webshop_settings.php` — `Webshop_settings_model.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 15.1 | `Webshop_settings.php` | `getWebshopSettings()` false → `->home_page` fatal | Fallback object `home_page => theme_1` | guard |
| 15.2 | `Webshop_settings.php` | `foreach ($sections)` when `getActiveSections()` false | Init arrays + `!empty`/`is_array` guard | guard |
| 15.3 | `Webshop_settings.php` | `foreach ($sections)` on null POST `section_id` | `!empty` + `is_array` guard; `!empty($data)` before update | guard |
| 15.4 | `Webshop_settings.php` | `custom_pages()` bc uses undefined `$pageData['page_title']` | Breadcrumb without undefined var | guard |
| 15.5 | `Webshop_settings.php` | `edit_custom_pages` wrong `$pageData[$page_key]` key | Use `$page` slug key + empty array fallback | guard |
| 15.6 | `Webshop_settings.php` | `section_data` missing key in elements switch | `isset()` fallback to `''` | guard |
| 15.7 | `Webshop_settings_model.php` | Undefined `$parent_id` in `get_categories()` | Init `$parent_id = null` before `(bool)` check | guard |
| 15.8 | `elements_*.php` views | `unserialize()` on empty/non-string section data | `is_string` guard + `$sectionData` default | guard |
| 15.9 | `sliders.php` | `scandir()` false → foreach TypeError | `is_array($files)` fallback `array()` | guard |
| 15.10 | `settings.php` | `$wh` unset when no warehouses | Init `$wh = array('' => '')` before foreach | guard |
| 15.11 | `elements_sections/` | Missing `elements_section_full_width_banner_2.php` | Restored from banner_1 partial (controller include) | restore |
| 15.12 | `phase4_webshop_settings_*.php` | — | Screen + deep-link scripts | test |

#### 15.2 `Webshop_settings.php` — getActiveSections foreach

**Old code:**
```php
$sections = $this->webshop_settings_model->getActiveSections($this->webshop_settings->home_page);

foreach ($sections as $key => $section) {
```

**New code:**
```php
$this->data['active_sections'] = array();
$this->data['sections'] = array();

$sections = $this->webshop_settings_model->getActiveSections($this->webshop_settings->home_page);

if (!empty($sections) && is_array($sections)) {
foreach ($sections as $key => $section) {
```

#### 15.5 `Webshop_settings.php` — edit_custom_pages page_data key

**Old code:**
```php
$bc = array(..., ucwords('Edit ' . $pageData[$page_key]['page_title']));
$this->data['page_data'] = $pageData[$page];
```

**New code:**
```php
$pageTitle = (is_array($pageData) && $page && isset($pageData[$page]['page_title'])) ? $pageData[$page]['page_title'] : lang('Edit Custom Pages');
$bc = array(..., ucwords('Edit ' . $pageTitle));
$this->data['page_data'] = (is_array($pageData) && $page && isset($pageData[$page])) ? $pageData[$page] : array();
```

---

## Module 16 — Eshop (Mobile API)

**Tests:** `phase4_eshop_test.php` (**14/14**), `phase4_eshop_links_test.php` (**10/10**) PASS  
**Status:** ✅ Module complete — fixes applied + retest 2026-07-12  
**Controllers:** `Eshop_admin.php`, `Eshop_api.php`, `Eshop.php` — `Eshop_api_model.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 16.1 | `Eshop_api.php` | `foreach` on false from API model lists | `!empty` + `is_array` guards on all list endpoints | guard |
| 16.2 | `Eshop_api_model.php` | `getCategoryProducts(..., $type)` missing 4th arg fatal | Default `$type = null`; related call passes `null` | guard |
| 16.3 | `Eshop_api.php` | `product_details` related products 500 (ArgumentCountError) | Pass 4th arg `null` to `getCategoryProducts` | guard |
| 16.4 | `Eshop_api.php` | `round()` on empty qty strings TypeError | `(float)` cast before `round()` | guard |
| 16.5 | `Eshop_api.php` | `checkout_details` foreach on missing `slitems` | Validate decoded JSON + `slitems` array | guard |
| 16.6 | `Eshop_api.php` | `storeInfo()` false → property access in checkout | Fallback `shopinfo` array in constructor | guard |
| 16.7 | `Eshop_api.php` | `json_encode` invalid UTF-8 | `JSON_INVALID_UTF8_SUBSTITUTE` in `json_op` | syntax |
| 16.8 | `Eshop.php` | `count($cart)` on null POST sales | `empty` + `is_countable` guard | guard |
| 16.9 | `Eshop.php` | `getInvoiceByID` / `getSaleByReff` false property access | False guards before `->` access | guard |
| 16.10 | `Eshop.php` | `getDeliveryByID($id)` undefined `$id` | Use `$validOrder`; guard payment/billing `[0]` | guard |
| 16.11 | `Eshop_admin.php` | `foreach ($_POST['price'])` unguarded; HTTP_REFERER | POST `isset` guards; referer fallback paths | guard |
| 16.12 | `eshop/pages.php`, `settings.php` | bareword `id=>` array key (undefined constant) | Quoted `'id' =>` | syntax |
| 16.13 | `phase4_eshop_*.php` | — | Screen + deep-link scripts | test |

#### 16.2 `Eshop_api_model.php` — getCategoryProducts 4th parameter

**Old code:**
```php
public function getCategoryProducts($category_id, $pageno = 1, $itemsPerPage = 18, $type) {
```

**New code:**
```php
public function getCategoryProducts($category_id, $pageno = 1, $itemsPerPage = 18, $type = null) {
```

#### 16.3 `Eshop_api.php` — product_details related products call

**Old code:**
```php
$Releted_products = $this->eshop_api_model->getCategoryProducts($products[0]->category_id,1, 10);
```

**New code:**
```php
$Releted_products = $this->eshop_api_model->getCategoryProducts($products[0]->category_id, 1, 10, null);
```

---

## Module 17 — Shop

**Tests:** `phase4_shop_test.php` (**10/10**), `phase4_shop_links_test.php` (**9/9**) PASS  
**Status:** ✅ Module complete — fixes applied + retest 2026-07-12  
**Controllers:** `Shop.php` — `Shop_model.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 17.1 | `Shop.php` | `storeInfo()` false; `$this->shopinfo` before assign | `is_array` fallback; use `$shopinfo` for warehouse | guard |
| 17.2 | `Shop.php` | `foreach ($outlets)` when `getEshopOutlets()` false | `is_array` guards on outlets + shipping `[0]` | guard |
| 17.3 | `Shop.php` | `count($_SESSION['cart'])` without isset | `isset` + `is_array` before count (6×) | guard |
| 17.4 | `Shop.php` | `product_info()` false hash → property access | Redirect `shop/home` when product missing | guard |
| 17.5 | `Shop.php` | `home()` `$category[0]['id']` when categories false | `isset` fallback default category | guard |
| 17.6 | `Shop.php` | `productNavigations()` `$details[0]` unguarded | `!empty` + `isset($details[0])` guard | guard |
| 17.7 | `Shop.php` | `getTaxMethods`/`getTaxAttribs` foreach on false | Init `$data` + `!empty`/`is_array` guards | guard |
| 17.8 | `Shop.php` | `orderDetails()` `getDeliveryByID($id)` undefined `$id` | Use `$validOrder`; guard payment/billing `[0]` | guard |
| 17.9 | `Shop.php` | unguarded `HTTP_REFERER` redirects (6×) | `isset` + fallback `shop/home` | guard |
| 17.10 | `Shop.php` | `$_POST['action']` without isset on T2 home | `isset($_POST['action'])` guard | guard |
| 17.11 | `Shop_model.php` | `count($_SESSION['cart'])` on logout | `isset` + `is_array` guard | guard |
| 17.12 | `phase4_shop_*.php` | — | Screen + deep-link scripts | test |

#### 17.3 `Shop.php` — session cart count

**Old code:**
```php
if (count($_SESSION['cart']) > 0) {
```

**New code:**
```php
if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
```

---

## Module 18 — System Settings

**Tests:** `phase4_system_settings_test.php` (**15/15**), `phase4_system_settings_links_test.php` (**18/18**) PASS  
**Status:** ✅ Module complete — fixes applied + retest 2026-07-12  
**Controllers:** `System_settings.php` — `settings/manage_barcode.php` view

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 18.1 | `System_settings.php` | unguarded `HTTP_REFERER` redirects (~86×) | `isset` + fallback `system_settings` (P3) | guard |
| 18.2 | `System_settings.php` | `manage_barcode()` foreach on missing POST arrays | Init `$barcodedata`; `!empty` + `is_array` guards | guard |
| 18.3 | `System_settings.php` | `import_categories` `count($arrResult)` on non-array | `is_array` guard before count; skip non-array rows | guard |
| 18.4 | `System_settings.php` | `import_expense_categories`/`import_brands` foreach on bad CSV rows | `is_array` continue before `count`/`array_combine` | guard |
| 18.5 | `settings/manage_barcode.php` | `$manageB2`/`$manageBside` undefined → `in_array` TypeError | Init arrays; `!empty($managebarcode)` foreach guards | guard |
| 18.6 | `phase4_system_settings_*.php` | — | Screen + deep-link scripts | test |

#### 18.1 `System_settings.php` — HTTP_REFERER (representative)

**Old code:**
```php
redirect($_SERVER["HTTP_REFERER"]);
```

**New code:**
```php
redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'system_settings');
```

#### 18.5 `settings/manage_barcode.php` — undefined manage arrays

**Old code:**
```php
foreach($barcode_field2 as $key2 => $barcodefield2){
    if(!in_array($key2,$manageB2)){
```

**New code:**
```php
$manageB = array();
$manageB2 = array();
$manageBside = array();
// ...
foreach($barcode_field2 as $key2 => $barcodefield2){
    if(!in_array($key2,$manageB2)){
```

---

## Module 19 — Attendance

**Tests:** `phase4_attendance_test.php` (**4/4**), `phase4_attendance_links_test.php` (**7/7**) PASS  
**Status:** ✅ Module complete — fixes applied + retest 2026-07-12  
**Controllers:** `Attendance.php` — `attendance/edit_user.php` (restore)

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 19.1 | `Attendance.php` | `report()` / `list_actions()` foreach on empty rows | `!empty($rows)` guard before foreach | guard |
| 19.2 | `attendance/edit_user.php` | Missing view — `edit_user()` 500 fatal | Restored enrollment edit form matching `$user`/`$groups` | restore |
| 19.3 | `phase4_attendance_*.php` | — | Screen + deep-link scripts | test |

#### 19.2 `attendance/edit_user.php` — restore missing view

**Old code:**
```php
// file absent — controller called page_construct('attendance/edit_user', ...)
```

**New code:**
```php
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <?php echo form_open('attendance/update_user/' . (int) $user->id, ...); ?>
    <!-- first_name, last_name, group_id, password, face_image fields -->
```

---

## Module 20 — Leads (CRM)

**Tests:** `phase4_leads_test.php` (**4/4**), `phase4_leads_links_test.php` (**8/8**) PASS  
**Status:** ✅ Module complete — fixes applied + retest 2026-07-12  
**Controllers:** `Leads.php` — `Leads_model.php` — `themes/default/views/leads/*`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 20.1 | `Leads.php` | `add()` missing `$leads` object for add view | Init empty `(object)` defaults for form fields | guard |
| 20.2 | `Leads.php` | `edit()` unguarded `HTTP_REFERER` redirect | `isset` + fallback `Leads/index` (P3) | guard |
| 20.3 | `Leads.php` | `edit()` / modals load views when `getLeadsByID` false | Early guard/redirect or modal error | guard |
| 20.4 | `Leads.php` | `add_deals()` called `getDealsNameByID($lead_id)` | `$deals = FALSE` for new-deal form | guard |
| 20.5 | `Leads_model.php` | `getLeadTypes()` returns null when empty | `return array()` | guard |
| 20.6 | `leads/add.php`, `edit.php` | `foreach ($leads_type)` on null | `!empty($leads_type)` guard | guard |
| 20.7 | `leads/list_deals.php` | `$deals->name` / `$deals->Leadid` when no deals | Use `$leadDetails->full_name` + `$lead_id` | guard |
| 20.8 | `leads/add_deals.php` | `$deals->CategoryId` when `$deals` false | `!empty($deals) && isset(...)` guard | guard |
| 20.9 | `phase4_leads_*.php` | — | Screen + deep-link scripts | test |

#### 20.1 `Leads.php` — empty leads object on add

**Old code:**
```php
$this->data['leads_type'] =  $this->Leads_model->getLeadTypes();
```

**New code:**
```php
$this->data['leads'] = (object) array('full_name' => '', 'mobile' => '', ...);
$this->data['leads_type'] =  $this->Leads_model->getLeadTypes();
```

---

## Module 21 — Service Requests

**Tests:** `phase4_service_requests_test.php` (**5/5**), `phase4_service_requests_links_test.php` (**11/11**) PASS  
**Status:** ✅ Module complete — fixes applied + retest 2026-07-12  
**Controllers:** `Service_requests.php` — `service_site_report_mobileview.php`

| # | File | Old | New | Type |
|---|------|-----|-----|------|
| 21.1 | `service_site_report_mobileview.php` | `foreach ($customers)` / `foreach ($service_types)` unguarded | `!empty()` guards before foreach | guard |
| 21.2 | `Service_requests.php` | `$this->Settings->barcode_img` undefined property | `isset` guard before read | guard |
| 21.3 | `Service_requests.php` | `$this->Settings->site_name` in email body unguarded | `isset(...) ? ... : ''` | guard |
| 21.4 | `phase4_service_requests_*.php` | — | Screen + tab/AJAX scripts | test |

#### 21.1 `service_site_report_mobileview.php` — foreach guards

**Old code:**
```php
<?php foreach($customers as $c):
```

**New code:**
```php
<?php if (!empty($customers)) { foreach($customers as $c):
```

---

## Test scripts index (Modules 1–21)

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
| 14 Webshop | `phase4_webshop_test.php`, `phase4_webshop_links_test.php` |
| 15 Webshop Settings | `phase4_webshop_settings_test.php`, `phase4_webshop_settings_links_test.php` |
| 16 Eshop | `phase4_eshop_test.php`, `phase4_eshop_links_test.php` |
| 17 Shop | `phase4_shop_test.php`, `phase4_shop_links_test.php` |
| 18 System Settings | `phase4_system_settings_test.php`, `phase4_system_settings_links_test.php` |
| 19 Attendance | `phase4_attendance_test.php`, `phase4_attendance_links_test.php` |
| 20 Leads | `phase4_leads_test.php`, `phase4_leads_links_test.php` |
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
| 2026-07-11 | P1+P2 | **ElintOM18.00** Phase 1 (AllowDynamicProperties, E_STRICT) + Phase 2 (Composer MPDF/Stripe/Google, PHPExcel/Zend/phpqrcode patches, mPDF shim) |
| 2026-07-11 | P3 | `crypto_helper.php`, `Ccavenue.php` — mcrypt → OpenSSL AES-128-CBC; `phase3_smoke_test.php` 14/14 PASS |
| 2026-07-11 | 6 | Module 6 Purchases — `view_return` use `getPurchaseByID` + restore view; screen 13/13 + deep 6/6 + return 7/7 + links 20/20 PASS |
| 2026-07-11 | 5 | Module 5 Products — view/edit/pdf/print_barcodes PHP 8.5 guards; screen 10/10 + deep 8/8 + stock 11/11 + links 17/17 PASS |
| 2026-07-11 | 4 | Module 4 Sales — screen 8/8 + deep 6/6 + deep-links 12/12 PASS on ElintOM18.00 |
| 2026-07-11 | 3 | Module 3 POS — `Pos.php`/`today_sale.php` PHP 8.5 guards; screen 9/9 + deep 7/7 + deep-links 11/11 PASS |
| 2026-07-11 | 1–11 | **Status reset** — all modules marked not done; fixes retained; retest required |
| 2026-07-11 | 6 | Module 6 Purchases — `view_return` uses `getPurchaseByID`/`getAllPurchaseItems` (not missing `return_purchases` table); restored `view_return.php`; return deep 7/7 + links 20/20 PASS |
| 2026-07-11 | 7 | Module 7 Reports **certified complete** — `comp.state`, ledger dates, `load_ajax_reports`, `getCosting` sale_id; screen **71/71**, deep **50/50**, links **24/24** |
| 2026-07-11 | 8 | Module 8 Customers **certified complete** — `getGiftCard` DESC→desc, `getGiftBalance` guards, HTTP_REFERER; screen **10/10**, links **19/19** |
| 2026-07-11 | 9 | Module 9 Suppliers & Billers **certified complete** — HTTP_REFERER, getCompanyByID guards, suggestions `return array()`; screen **10/10**, links **14/14** |
| 2026-07-11 | 10 | Module 10 Quotes **certified complete** — HTTP_REFERER, getQuoteByID guards, suggestions; screen **6/6**, links **10/10** |
| 2026-07-11 | 11 | Module 11 Transfers **certified complete** — HTTP_REFERER, getTransferByID/Request guards, suggestions; screen **10/10**, links **12/12** |
| 2026-07-11 | 12 | Module 12 Restaurant **certified complete** — kitchen_view restore, invoice/guest guards, test DB bootstrap; screen **10/10**, links **10/10** |
| 2026-07-11 | 13 | Module 13 Production Unit **certified complete** — productionUnit array, warehouse/KOT guards, HTTP_REFERER; screen **21/21**, links **8/8** |
| 2026-07-11 | 14 | Module 14 Webshop **certified complete** — raw_settings, rank SQL, cart_items/custom_pages guards; screen **12/12**, links **9/9** |
| 2026-07-12 | 15 | Module 15 Webshop Settings **certified complete** — getWebshopSettings/sections/custom_pages/elements guards; screen **7/7**, links **4/4** |
| 2026-07-12 | 16 | Module 16 Eshop **certified complete** — API foreach guards, getCategoryProducts arg, product_details/legacy API; screen **14/14**, links **10/10** |
| 2026-07-12 | 17 | Module 17 Shop **certified complete** — constructor outlets/cart/tax/orderDetails guards; screen **10/10**, links **9/9** |
| 2026-07-12 | 18 | Module 18 System Settings **certified complete** — HTTP_REFERER, manage_barcode/import guards, manage_barcode view; screen **15/15**, links **18/18** |
| 2026-07-12 | 19 | Module 19 Attendance **certified complete** — report/list_actions foreach guards, restore `edit_user` view; screen **4/4**, links **7/7** |
| 2026-07-12 | 20 | Module 20 Leads **certified complete** — add empty `$leads`, getByID/modal guards, getLeadTypes array; screen **4/4**, links **8/8** |

---

*Next module fixes → append summary row + **Old code** / **New code** blocks under **Module N** and add a **Changelog** line.*
