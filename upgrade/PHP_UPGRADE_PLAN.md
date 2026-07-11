# PHP Upgrade Plan — ElintOM18.00

| Item | Value |
|------|-------|
| Framework | CodeIgniter 3.1.13 |
| Application | ElintPOS / SMA ERP |
| **Fixes log** | **`PHP_UPGRADE_FIXES_LOG.md`** (Old → New, modules 1–9+) |
| Total Controllers | ~75 |
| Total Screens | ~900+ |
| Total Files | ~21,900 |
| Timezone | Asia/Kolkata |
| DB (localhost) | Per `app/config/database.php` in ElintOM18.00 (prefix: `sma_`) |

---

## 1. Upgrade Strategy (4 Phases)

| Phase | Scope | काय करायचे | Priority | Risk | Status |
|-------|-------|------------|----------|------|--------|
| Phase 1 | `system/` | CI 3.1.13 + PHP 8 session wrappers | Critical | Low | **Done** (ElintOM18.00 2026-07-11) |
| Phase 2 | `app/third_party/` | MPDF, PHPExcel, Stripe, Google SDK upgrade | Critical | High | **Done** (ElintOM18.00 2026-07-11) |
| Phase 3 | `app/` | Controllers, Models, `libraries/Sma.php` | High | Medium | In Progress |
| Phase 4 | All modules | Module-wise screen testing | Medium | — | **In Progress** |

---

## 2. Phase 1 — Framework Core (`system/`)

| # | Submodule | Files | PHP 8 Action | Status |
|---|-----------|-------|--------------|--------|
| 1 | Core Bootstrap | `CodeIgniter.php`, `Common.php`, `Router.php` | CI 3.1.13 | **Done** |
| 2 | Session | `Session.php`, `PHP8SessionWrapper.php` | PHP 8 wrapper | **Done** |
| 3 | Database | `mysqli_driver.php`, `DB_query_builder.php` | Test all queries | Test |
| 4 | Security/Input | `Security.php`, `Input.php` | Null coalescing fixes | Test |
| 5 | Compat Layer | `compat/hash.php`, `mbstring.php` | PHP 8 polyfills | **Done** |
| 6 | Dynamic props | `Controller.php`, `Model.php` | `#[\AllowDynamicProperties]` | **Done** (2026-07-11) |
| 7 | Error reporting | `index.php`, `Exceptions.php` | PHP 8.4+ `E_STRICT` removed | **Done** (2026-07-11) |

---

## 3. Phase 2 — Third Party Libraries (`app/third_party/`)

### 3.0 Upgrade Summary (PHP 8.5 — Completed)

#### Upgraded via Composer (`app/third_party/vendor/`)

| Library | Old | New |
|---------|-----|-----|
| MPDF | 6.0 (fatal parse errors) | **8.3.1** via legacy shim (`MPDF/mpdf.php`) |
| Stripe | 3.23.0 (manual `init.php`) | **16.6.0** |
| Google API | 2.4.1 (~13K files in `googlelogin/`) | **2.19.4** via Composer (`third_party/autoload.php`) |

#### Patched for PHP 8.5 syntax (kept same API)

| Library | Fix |
|---------|-----|
| PHPExcel | 36 files — curly-brace offsets + deprecated casts |
| Zend Barcode | 8 files — curly-brace offsets + `Zend.php` parse error |
| phpqrcode | Default params for `$back_color` / `$fore_color` |

#### Unchanged (already PHP 8.5 compatible)

| Library | Notes |
|---------|-------|
| Facebook SDK 5.0 | Loads cleanly on PHP 8.5 |
| PayPal Pro | Custom NVP lib in `app/libraries/` — not the unused REST SDK in `third_party/paypal/` |

### 3.1 Version Matrix (Old → Latest Stable)

| # | Library | Use | Old Version | Latest Stable | Upgrade Method | Status |
|---|---------|-----|-------------|---------------|----------------|--------|
| 1 | MPDF | PDF bills, reports, challans | **6.0** | **8.3.1** | Composer `mpdf/mpdf` + legacy shim (`MPDF/mpdf.php`) | Done |
| 2 | PHPExcel | Excel import/export | **~1.8.x** (2014) | **~1.8.x** (patched) | PHP 8.5 syntax patch (curly braces, casts) — PhpSpreadsheet migration deferred | Done |
| 3 | Stripe | Payments | **3.23.0** | **16.6.0** | Composer `stripe/stripe-php` + `Stripe_payments.php` adapter | Done |
| 4 | PayPal Pro | POS direct card (NVP) | Custom lib | Custom lib | No change — app uses `Paypal_pro`, not REST SDK | N/A |
| 4b | PayPal REST SDK | Vendored only | **1.7.4** | — | Unused in app — cleanup candidate | Skip |
| 5 | Facebook SDK | Social login (Webshop) | **5.0.0** | **5.0.0** (patched) | Kept in place — loads on PHP 8.5 | Done |
| 6 | Google Login | OAuth (Webshop) | **2.4.1** (+ services 0.131) | **2.19.4** | Composer `google/apiclient` via `third_party/autoload.php` | Done |
| 7 | phpqrcode | QR codes on invoices | Unversioned (2010–2012) | Unversioned (patched) | PHP 8.5 param defaults fix | Done |
| 8 | Zend Barcode | Product/invoice barcodes | ZF1 subset (~2012) | ZF1 subset (patched) | PHP 8.5 syntax patch + `Zend.php` fix | Done |

### 3.2 Composer Packages (`app/third_party/composer.json`)

| Package | Installed Version | PHP Requirement |
|---------|-------------------|-----------------|
| `mpdf/mpdf` | 8.3.1 | ^8.1 |
| `stripe/stripe-php` | 16.6.0 | ^8.1 |
| `google/apiclient` | 2.19.4 | ^8.1 |

### 3.3 Module Impact & Priority

| # | Library | Modules Affected | Priority |
|---|---------|------------------|----------|
| 1 | MPDF | Sales, POS, Reports, Purchases | P1 |
| 2 | PHPExcel | Products, Customers, Reports | P1 |
| 3 | Stripe | POS, Webshop, Payments | P1 |
| 4 | PayPal Pro | POS, Webshop | P2 |
| 5 | Facebook SDK | Webshop (`Shop.php`) | P2 |
| 6 | Google Login | Webshop (`Shop.php`) | P2 |
| 7 | phpqrcode | Products, POS | P2 |
| 8 | Zend Barcode | Products, POS, Sales | P3 |

### 3.4 Cleanup Candidates (post-upgrade)

| Path | Old Size | Reason |
|------|----------|--------|
| `app/third_party/googlelogin/` | ~13,349 files | Replaced by `vendor/google/apiclient` |
| `app/third_party/paypal/` | ~616 files | REST SDK unused — app uses `Paypal_pro` |
| `app/third_party/MPDF/mpdf_legacy_6.php` | 1 file | Backup of mPDF 6 entry point |

### 3.5 Phase 3 Progress (App Code — PHP 8.5)

#### Batch 1 — Auth + core (Done)

| Area | Files | Fix |
|------|-------|-----|
| Dynamic properties | `system/core/Controller.php`, `Model.php`, `MY_Controller.php`, `Auth_model.php` | `#[\AllowDynamicProperties]` |
| Auth | `Auth.php`, `Ion_auth.php` | HTTP_REFERER guards, `$new_password` email fix |
| Param order | 15 models/controllers | 30 signatures — trailing `= null` defaults |
| Fatal syntax | `Paytm.php`, `Apicrypter.php` | Curly-brace offsets, cast fix |

#### Batch 2 — Crypto + POS (Done)

| Area | Files | Fix |
|------|-------|-----|
| mcrypt → OpenSSL | `Encrypt.php`, `crypto_helper.php`, `Ccavenue.php`, `Paytm.php`, `Apicrypter.php` | AES-128/256-CBC via OpenSSL |
| **crypto_helper + Ccavenue** | `crypto_helper.php`, `Ccavenue.php` | **Done** 2026-07-11 — CCAvenue OpenSSL kit pattern; `phase3_smoke_test.php` **14/14 PASS** |
| Sma library | `Sma.php` | `#[\AllowDynamicProperties]`, `&` → `&&` |
| POS | `Pos.php`, `Pos_elite.php` | `end(explode())` → temp variable |

#### Batch 3 — In Progress (Phase 4 screen testing)

**Status reset 2026-07-11:** Modules 1–11 marked **not done** — PHP fixes remain; full retest required before marking complete.

| Area | Status | Notes |
|------|--------|-------|
| **Module 1 — Auth** | **✅ Module complete** | Screen 10/10 + deep-links 5/5 (2026-07-11 ElintOM18.00 retest) |
| **Module 2 — Welcome** | **✅ Module complete** | Deep-links 4/4 + dashboard screen via auth test (2026-07-11 retest) |
| **Module 3 — POS** | **✅ Module complete** | Screen 9/9 + deep 7/7 + deep-links 11/11 (2026-07-11 retest) |
| **Module 4 — Sales** | **✅ Module complete** | Screen 8/8 + deep 6/6 + deep-links 12/12 (2026-07-11 retest) |
| **Module 5 — Products** | **✅ Module complete** | Screen 10/10 + deep 8/8 + stock 11/11 + deep-links 17/17 (2026-07-11 retest) |
| **Module 6 — Purchases** | **✅ Module complete** | Screen 13/13 + deep 6/6 + return 7/7 + deep-links 20/20 (2026-07-11 retest) |
| **Module 7 — Reports** | **⏳ Not done** | Retest: screen + deep + deep-links |
| **Module 8 — Customers** | **⏳ Not done** | Retest: screen + deep-links |
| **Module 9 — Suppliers & Billers** | **⏳ Not done** | Retest: screen + deep-links |
| **Module 10 — Quotes** | **⏳ Not done** | Retest: screen + deep-links |
| **Module 11 — Transfers** | **⏳ Not done** | Retest: screen + deep-links |
| Remaining controllers (12–24) | Pending | Full module scan during Phase 4 |

#### Phase 4 — Module Testing Log (last run 2026-07-07; status reset 2026-07-11 — pending retest)

| Module | Screen | Load | Form/AJAX | Notes |
|--------|--------|------|-----------|-------|
| 1 Auth | Login | ✅ | ✅ | POST login → POS redirect, session OK |
| 1 Auth | Register | ✅ | — | Guest redirect OK |
| 1 Auth | Forgot Password | ✅ | — | Guest + logged-in OK |
| 1 Auth | Change Password | ✅ | ⏳ | Form on profile `#cpassword` — save not tested |
| 1 Auth | User List | ✅ | ⏳ | `/auth/users` 200 — DataTables AJAX pending |
| 1 Auth | Profile | ✅ | ⏳ | `/auth/profile/1` 200 — save not tested |
| 1 Auth | Create User | ✅ | ⏳ | Page loads — submit not tested |
| 2 Welcome | Dashboard | ✅ | — | `/welcome` 200, 0 PHP warnings |
| 2 Welcome | Menu includes | ✅ | — | Via header on dashboard/POS |
| 3 POS | Main POS | ✅ | ✅ | `/pos` 200 |
| 3 POS | Sales list | ✅ | ⏳ | `/pos/sales` 200 |
| 3 POS | Open/Close register | ✅ | ⏳ | Both load — save not tested |
| 3 POS | Today sales | ✅ | — | Fixed PHP 8 null/`str_replace` — now 200 |
| 3 POS | POS elite | ✅ | ⏳ | `/pos_elite` 200 |
| 3 POS | Product AJAX | ✅ | — | `sales/suggestions` + `getProductDataByCode` |
| 3 POS | Cash sale submit | ✅ | ✅ | `phase4_pos_deep_test` — sale_id OK |
| 3 POS | View bill | ✅ | — | `/pos/view/{id}` 200 |

| 4 Sales | Sales list | ✅ | ✅ | `/sales` 200, `getSales` AJAX |
| 4 Sales | Add sale | ✅ | ✅ | Submit → redirect `/sales`; view OK |
| 4 Sales | All sale lists | ✅ | ⏳ | `/sales/all_sale_lists` 200 |
| 4 Sales | Product AJAX | ✅ | — | `sales/suggestions` 200 |
| 4 Sales | View sale | ✅ | — | `/sales/view/{id}` 200 |
| 4 Sales | PDF | ✅ | — | `/sales/pdf/{id}` application/pdf |

| 5 Products | Product list | ✅ | ✅ | `/products` 200, `getProducts` AJAX |
| 5 Products | Add product | ✅ | ✅ | Submit → `/products`; view/edit/modal OK |
| 5 Products | View/Edit/Modal | ✅ | — | Product id from getProducts |
| 5 Products | Print barcodes | ✅ | — | `/products/print_barcodes` 200 |
| 5 Products | Adjustments list | ✅ | — | `/products/quantity_adjustments` 200 |
| 5 Products | suggestions AJAX | ✅ | — | `/products/suggestions?term=test` |
| 5 Products | qa_suggestions (stock) | ✅ | — | ~700–980ms, warehouse 15 |
| 5 Products | Stock adjustment +1 | ✅ | ✅ | POST `add_adjustment` → list redirect |
| 5 Products | product_list AJAX | ✅ | — | ~700ms (JS blocks UI with async:false) |

**POS test scripts:** `phase4_pos_test.php` (screens), `phase4_pos_deep_test.php` (sale flow), `phase4_pos_links_test.php` (deep links 11/11)

**Sales test scripts:** `phase4_sales_test.php` (screens), `phase4_sales_deep_test.php` (sale submit), `phase4_sales_links_test.php` (deep links 12/12)

**Products test scripts:** `phase4_products_test.php` (screens), `phase4_products_deep_test.php` (add submit), `phase4_products_stock_deep_test.php` (stock/timing), `phase4_products_links_test.php` (deep links 17/17)

**Purchases test scripts:** `phase4_purchases_test.php` (screens 13/13), `phase4_purchases_deep_test.php` (add submit 6/6), `phase4_purchases_links_test.php` (deep links 20/20), `phase4_purchases_return_deep_test.php` (return 7/7)

**Auth/Welcome links:** `phase4_auth_links_test.php` (5/5), `phase4_welcome_links_test.php` (4/4)

**Shared lib:** `phase4_test_lib.php` (login, CSRF, DataTables ID helpers)

**Test script:** `phase4_auth_test.php` — `php phase4_auth_test.php <user> <pass>`

**Run all deep-link tests (modules 1–5):**
```bash
cd upgrade
php phase4_auth_links_test.php Admin "Admin@554"
php phase4_welcome_links_test.php Admin "Admin@554"
php phase4_pos_links_test.php Admin "Admin@554"
php phase4_sales_links_test.php Admin "Admin@554"
php phase4_products_links_test.php Admin "Admin@554"
php phase4_customers_test.php Admin "Admin@554"
php phase4_customers_links_test.php Admin "Admin@554"
php phase4_suppliers_billers_test.php Admin "Admin@554"
php phase4_suppliers_billers_links_test.php Admin "Admin@554"
```

**Module 1 fixes (this session):** — details in [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 1

**Module 2 fixes (Welcome scan):** — details in [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 2

**Module 3 fixes (POS scan):** — details in [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 3

**Module 4 fixes (Sales scan):** — [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 4

**Module 4 deep test (last run — not certified):** `phase4_sales_deep_test.php` — 6/6 PASS (suggestions → pending sale submit → view)

**Module 5 fixes (Products scan):** — [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 5

**Module 5 stock deep test (last run — not certified):** `phase4_products_stock_deep_test.php` — 11/11 PASS (qa_suggestions ~700–980ms, adjustment +1 OK)

**Module 6 fixes (Purchases scan):** — [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 6

**Module 6 tests (last run — not certified):** screen 13/13, deep-links 20/20, add deep 6/6, return deep 7/7 PASS

**Module 7 fixes (Reports scan):** — [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 7

**Module 7 tests (last run — not certified):** screens ~69/71; deep-links ~22; deep AJAX 39/50 (see fixes log for open items)

**Module 8 fixes (Customers scan):** — [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 8

**Module 8 tests (last run — not certified):** screen 10/10, deep-links 19/19 PASS

**Module 9 fixes (Suppliers & Billers):** — [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 9

**Module 9 tests (last run — not certified):** screen 10/10, deep-links 14/14 PASS

**Module 10 fixes (Quotes):** — [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 10

**Module 10 tests (last run — not certified):** screen 6/6, deep-links 10/10 PASS

**Module 11 fixes (Transfers):** — [`PHP_UPGRADE_FIXES_LOG.md`](PHP_UPGRADE_FIXES_LOG.md) §Module 11

**Module 11 tests (last run — not certified):** screen 10/10, deep-links 12/12 PASS (`Transfersnew.php` deferred)

**Stock/timing note:** Adjustment screen uses `async:false` on `product_list` + `qa_suggestions` in JS (`adjustments.js`) — causes UI “stuck” feel on large warehouses; server paths OK under 1s locally.

---

## 4. Module Master Index

| # | Module | Controller(s) | Screens | Priority | PHP Risk | Cleanup Files |
|---|--------|---------------|---------|----------|----------|---------------|
| 1 | Auth & Users | `Auth.php` | 17 | P1 | Ion_auth, Bcrypt, session | — |
| 2 | Dashboard & Welcome | `Welcome.php` | 5 | P1 | Charts, widgets | — |
| 3 | POS | `Pos.php`, `Pos_elite.php`, `Pos2.php`, `Pos_sun.php` | 53+ | P1 | Payments, Sma.php, Razorpay | `Pos_backup(08_05_2025).php` |
| 4 | Sales | `Sales.php` | 58 | P1 | MPDF, 11K lines controller | `Sales_backup(08_05_2025).php` |
| 5 | Products & Inventory | `Products.php` | 43 | P1 | PHPExcel, barcode, upload | — |
| 6 | Purchases | `Purchases.php` | 25 | P2 | MPDF, CSV import | — |
| 7 | Reports | `Reports.php`, `Reports_new.php` | 109 | P2 | Heavy queries, MPDF, 25K lines | `Reports_new.php` |
| 8 | Customers (CRM) | `Customers.php` | 22 | P2 | CSV import | — |
| 9 | Suppliers & Billers | `Suppliers.php`, `Billers.php` | 13 | P3 | — | — |
| 10 | Quotes | `Quotes.php` | 9 | P3 | MPDF | — |
| 11 | Transfers | `Transfers.php`, `Transfersnew.php` | 26 | P3 | PDF export | `Transfersnew.php` |
| 12 | Restaurant | `Restaurant_Order_Taking.php` | 11 | P2 | KOT, real-time AJAX | — |
| 13 | Production Unit | `Production_Unit.php`, `Bill_of_material.php` | 21 | P3 | KDS, procurement | `Production_Unit_Demo.php`, `Production_Unit_New.php` |
| 14 | Webshop | `Webshop.php` | 170 | P2 | Multi-theme, payments, OAuth | — |
| 15 | Webshop Settings | `Webshop_settings.php` | 36 | P3 | CMS, page builder | — |
| 16 | Eshop (Mobile API) | `Eshop.php`, `Eshop_admin.php`, `Eshop_api.php` | 10 | P2 | JSON API, mobile app | — |
| 17 | Shop | `Shop.php` | 88 | P3 | T1/T2 templates | — |
| 18 | System Settings | `System_settings.php` | 85 | P1 | Import, permissions | — |
| 19 | Attendance | `Attendance.php` | 6 | P3 | Kiosk, geolocation | — |
| 20 | Leads (CRM) | `Leads.php` | 7 | P3 | — | — |
| 21 | Service Requests | `Service_requests.php` | 3 | P3 | MPDF PDF | — |
| 22 | Urban Piper / Omnichannel | `Urban_piper.php`, `Omnichannel.php` | 30 | P3 | Webhooks, API callbacks | — |
| 23 | APIs (JSON) | `Api3`, `Api4`, `ApiOwner`, `Restapi5`, `Webhook`, `Whatsapp` | 0 UI | P2 | REST, webhooks | — |
| 24 | Other Modules | Various (see Section 28) | 50+ | P2-P4 | Mixed | — |

---

## 5. MODULE 1 — Auth & Users

**Module status:** ✅ Module complete (screen 10/10 + deep-links 5/5 — 2026-07-11 retest)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Login | Login | Auth | `auth/login.php` | P1 | ⏳ Not done — retest required |
| 2 | Login | Register | Auth | `auth/register.php` | P1 | ⏳ Not done — retest required |
| 3 | Password | Forgot Password | Auth | `auth/reset_password.php` | P1 | ⏳ Not done — retest required |
| 4 | Password | Mobile Forgot OTP | Auth | `auth/verify_forgot_password_otp.php` | P1 | ⏳ Not done — retest required |
| 5 | Password | Mobile Reset | Auth | `auth/reset_password_mobile.php` | P1 | ⏳ Not done — retest required |
| 6 | Password | Change Password | Auth | `auth/change_password.php` | P1 | ⏳ Not done — retest required |
| 7 | Profile | Profile | Auth | `auth/profile.php` | P1 | ⏳ Not done — retest required |
| 8 | Users | Create User | Auth | `auth/create_user.php` | P1 | ⏳ Not done — retest required |
| 9 | Users | Deactivate User | Auth | `auth/deactivate_user.php` | P1 | ⏳ Not done — retest required |
| 10 | Users | User List | Auth | `auth/index.php` | P1 | ⏳ Not done — retest required |
| 11 | Users | Logout | Auth | redirect | P1 | ⏳ Not done — retest required |

---

## 6. MODULE 2 — Dashboard & Welcome

**Module status:** ✅ Module complete (deep-links 4/4 + dashboard screen — 2026-07-11 retest)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Dashboard | Dashboard | Welcome | `dashboard.php` | P1 | ⏳ Not done — retest required |
| 2 | Dashboard | Best Sellers | Welcome | `best_sellers.php` | P1 | ⏳ Not done — retest required |
| 3 | Dashboard | Calendar | Welcome | `calendar.php` | P1 | ⏳ Not done — retest required |
| 4 | Menu | Admin Menu | Welcome | `admin_access_menu.php` | P1 | ⏳ Not done — retest required |
| 5 | Menu | User Menu | Welcome | `user_access_menu.php` | P1 | ⏳ Not done — retest required |

---

## 7. MODULE 3 — POS (Point of Sale)

**Module status:** ✅ Module complete (screen 9/9 + deep 7/7 + deep-links 11/11 — 2026-07-11 retest)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | POS Main | New Sale | Pos | `pos/add.php` | P1 | Product search, cart, billing |
| 2 | POS Main | EP Sale | Pos | `pos/add_ep.php` | P1 | Elite POS variant |
| 3 | POS Main | Restaurant POS | Pos | `pos/add_ep_restaurant.php` | P1 | Table + KOT |
| 4 | POS Main | Elite POS | Pos_elite | `pos/add_epelite.php` | P1 | Elite theme |
| 5 | Register | Open Register | Pos | `pos/open_register.php` | P1 | Cash drawer open |
| 6 | Register | Open Register (New) | Pos | `pos/open_register_new.php` | P1 | Cash drawer open |
| 7 | Register | Close Register | Pos | `pos/close_register.php` | P1 | Day end closing |
| 8 | Register | Register List | Pos | `pos/registers.php` | P1 | All registers |
| 9 | Register | Register Details | Pos | `pos/register_details.php` | P1 | Register summary |
| 10 | Register | Update Register | Pos | `pos/update_register.php` | P1 | Cash adjustment |
| 11 | Sales View | View Sale | Pos | `pos/view.php` | P1 | Sale details |
| 12 | Sales View | View Sale (Pharma) | Pos | `pos/view-pharma.php` | P1 | Pharma sale view |
| 13 | Sales View | View Bill | Pos | `pos/view_bill.php` | P1 | Bill print |
| 14 | Sales View | Today Sales | Pos | `pos/today_sale.php` | P1 | Daily summary |
| 15 | Sales View | Recent List | Pos | `pos/recent_pos_list.php` | P1 | Recent transactions |
| 16 | Sales View | Sales List | Pos | `pos/sales.php` | P1 | All POS sales |
| 17 | Payments | Checkout | Pos | `pos/checkout.php` | P1 | Payment screen |
| 18 | Payments | Add Payment | Pos | `pos/add_payment.php` | P1 | Partial payment |
| 19 | Payments | Razorpay | Pos | `pos/razorpay.php` | P1 | Razorpay gateway |
| 20 | Payments | Paytm | Pos | `pos/paytm.php` | P1 | Paytm gateway |
| 21 | Payments | CCAvenue | Pos | `pos/ccavenue.php` | P1 | CCAvenue gateway |
| 22 | Payments | PayUMoney | Pos | `pos/payumoney.php` | P1 | PayUMoney gateway |
| 23 | KOT/Restaurant | KOT Print | Pos | `pos/kot.php` | P1 | Kitchen order ticket |
| 24 | KOT/Restaurant | Print Sale | Pos | `pos/print_sale.php` | P1 | Receipt print |
| 25 | KOT/Restaurant | Suspended Bills | Pos | `pos/sus_print.php` | P1 | Hold bills |
| 26 | KOT/Restaurant | Opened Bills | Pos | `pos/opened.php` | P1 | Pending bills |
| 27 | Settings | POS Settings | Pos | `pos/settings.php` | P1 | POS config |
| 28 | Settings | Short Settings | Pos | `pos/short_setting.php` | P1 | Quick settings |
| 29 | Settings | Updates | Pos | `pos/updates.php` | P1 | POS updates |
| 30 | CRM | CRM | Pos | `pos/CRM.php` | P1 | Customer edit from POS |
| 31 | CRM | Customer Address | Pos | `pos/customer_addresses.php` | P1 | Address modal |
| 32 | CRM | Edit Customer | Pos | `pos/edit_customer_details.php` | P1 | Customer details |
| 33 | Mobile | Android View | Pos_elite | `pos/android_view.php` | P2 | Mobile POS |

---

## 8. MODULE 4 — Sales

**Module status:** ✅ Module complete (screen 8/8 + deep 6/6 + deep-links 12/12 — 2026-07-11 retest)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Sales List | Index | Sales | `sales/index.php` | P1 | Sales listing |
| 2 | Sales List | All Sales | Sales | `sales/all_sale_listing.php` | P1 | Filtered list |
| 3 | Sales List | Modal View | Sales | `sales/modal_view.php` | P1 | Quick view popup |
| 4 | Sales List | View Sale | Sales | `sales/view.php` | P1 | Full sale view |
| 5 | Sales List | Add Sale | Sales | `sales/add.php` | P1 | New sale form |
| 6 | Sales List | Edit Sale | Sales | `sales/edit.php` | P1 | Edit sale |
| 7 | Sales List | Return Sale | Sales | `sales/return_sale.php` | P1 | Sale return |
| 8 | Sales List | CSV Import | Sales | `sales/sale_by_csv.php` | P1 | Bulk import |
| 9 | PDF/Email | PDF Receipt | Sales | `sales/pdf_reciept.php` | P1 | PDF (MPDF) |
| 10 | PDF/Email | PDF | Sales | `sales/pdf.php` | P1 | Sale PDF |
| 11 | PDF/Email | Email | Sales | `sales/email.php` | P1 | Email sale |
| 12 | Deliveries | Deliveries List | Sales | `sales/deliveries.php` | P1 | Delivery list |
| 13 | Deliveries | Add Delivery | Sales | `sales/add_delivery.php` | P1 | New delivery |
| 14 | Deliveries | Edit Delivery | Sales | `sales/edit_delivery.php` | P1 | Edit delivery |
| 15 | Deliveries | View Delivery | Sales | `sales/view_delivery.php` | P1 | Delivery details |
| 16 | Deliveries | PDF Delivery | Sales | `sales/pdf_delivery.php` | P1 | Delivery note PDF |
| 17 | Payments | Payments | Sales | `sales/payments.php` | P1 | Payment list |
| 18 | Payments | Add Payment | Sales | `sales/add_payment.php` | P1 | Record payment |
| 19 | Payments | Edit Payment | Sales | `sales/edit_payment.php` | P1 | Edit payment |
| 20 | Payments | Payment Note | Sales | `sales/payment_note.php` | P1 | Payment receipt |
| 21 | Gift Cards | Gift Cards | Sales | `sales/gift_cards.php` | P2 | Gift card list |
| 22 | Gift Cards | Add Gift Card | Sales | `sales/add_gift_card.php` | P2 | Create card |
| 23 | Gift Cards | Edit Gift Card | Sales | `sales/edit_gift_card.php` | P2 | Edit card |
| 24 | Gift Cards | View Gift Card | Sales | `sales/view_gift_card.php` | P2 | Card details |
| 25 | Gift Cards | Topup | Sales | `sales/topup_gift_card.php` | P2 | Recharge card |
| 26 | Gift Cards | History | Sales | `sales/giftcard_history.php` | P2 | Card history |
| 27 | Credit Notes | Credit Notes | Sales | `sales/credit_note.php` | P2 | Credit note list |
| 28 | Credit Notes | Add Credit Note | Sales | `sales/add_credit_note.php` | P2 | New credit note |
| 29 | Credit Notes | Edit Credit Note | Sales | `sales/edit_credit_note.php` | P2 | Edit note |
| 30 | Credit Notes | View Credit Note | Sales | `sales/view_credit_note.php` | P2 | Note details |
| 31 | Challans | Challans List | Sales | `sales/challans.php` | P2 | Challan listing |
| 32 | Challans | View Challan | Sales | `sales/view_challan.php` | P2 | Challan view |
| 33 | Challans | Edit Challan | Sales | `sales/edit_challan.php` | P2 | Edit challan |
| 34 | Challans | Return Challan | Sales | `sales/return_challan.php` | P2 | Challan return |
| 35 | Challans | PDF Challan | Sales | `sales/pdf_challan.php` | P2 | Challan PDF |
| 36 | Eshop/Offline | Eshop Sales | Sales | `sales/eshop.php` | P2 | Online orders |
| 37 | Eshop/Offline | Offline Sales | Sales | `sales/offline.php` | P2 | Offline sync sales |
| 38 | Eshop/Offline | Update Status | Sales | `sales/update_status.php` | P2 | Status change |

---

## 9. MODULE 5 — Products & Inventory

**Module status:** ✅ Module complete (screen 10/10 + deep 8/8 + stock 11/11 + deep-links 17/17 — 2026-07-11 retest)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Products | Product List | Products | `products/index.php` | P1 | DataTables list |
| 2 | Products | Add Product | Products | `products/add.php` | P1 | New product |
| 3 | Products | Edit Product | Products | `products/edit.php` | P1 | Edit product |
| 4 | Products | View Product | Products | `products/view.php` | P1 | Product details |
| 5 | Products | Quick Add | Products | `products/add_quick.php` | P1 | POS quick add |
| 6 | Products | Modal View | Products | `products/modal_view_v1.php` | P1 | Popup view |
| 7 | Products | Import CSV | Products | `products/import_csv.php` | P1 | Bulk import (PHPExcel) |
| 8 | Products | Update Price | Products | `products/update_price.php` | P1 | Price update |
| 9 | Products | Manage Price | Products | `products/manage_price.php` | P1 | Group pricing |
| 10 | Products | POS Combo | Products | `products/poscombo.php` | P1 | Combo products |
| 11 | Products | Combo Product | Products | `products/add_combo_product.php` | P1 | Create combo |
| 12 | Products | Raw Materials | Products | `products/rawMaterials.php` | P1 | RM list |
| 13 | Products | Print Barcodes | Products | `products/print_barcodes.php` | P1 | Barcode print |
| 14 | Products | Set Rack | Products | `products/set_rack.php` | P1 | Rack location |
| 15 | Products | Favourites | Products | `products/list_favourite.php` | P2 | Fav products |
| 16 | Products | PDF | Products | `products/pdf.php` | P2 | Product PDF |
| 17 | Adjustments | Adjustments | Products | `products/quantity_adjustments.php` | P1 | Stock adjustments |
| 18 | Adjustments | Add Adjustment | Products | `products/add_adjustment.php` | P1 | New adjustment |
| 19 | Adjustments | Edit Adjustment | Products | `products/edit_adjustment.php` | P1 | Edit adjustment |
| 20 | Adjustments | View Adjustment | Products | `products/view_adjustment.php` | P1 | Adjustment view |
| 21 | Adjustments | CSV Adjustment | Products | `products/add_adjustment_by_csv.php` | P1 | Bulk adjustment |
| 22 | Stock Count | Stock Counts | Products | `products/stock_counts.php` | P1 | Count list |
| 23 | Stock Count | Count Stock | Products | `products/count_stock.php` | P1 | Start count |
| 24 | Stock Count | View Count | Products | `products/view_count.php` | P1 | Count details |
| 25 | Stock Count | Finalize Count | Products | `products/finalize_count.php` | P1 | Finalize |
| 26 | Batch | Batch List | Products | `batch/batch_list.php` | P2 | Batch tracking |
| 27 | Batch | Add Batch | Products | `batch/add.php` | P2 | New batch |
| 28 | Batch | Edit Batch | Products | `batch/edit.php` | P2 | Edit batch |

---

## 10. MODULE 6 — Purchases

**Module status:** ✅ Module complete (screen 13/13 + deep 6/6 + return 7/7 + deep-links 20/20 — 2026-07-11 retest)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Purchases | Purchase List | Purchases | `purchases/index.php` | P2 | Listing |
| 2 | Purchases | Add Purchase | Purchases | `purchases/add.php` | P2 | New PO |
| 3 | Purchases | Edit Purchase | Purchases | `purchases/edit.php` | P2 | Edit PO |
| 4 | Purchases | View Purchase | Purchases | `purchases/view.php` | P2 | PO details |
| 5 | Purchases | Modal View | Purchases | `purchases/modal_view.php` | P2 | Quick view |
| 6 | Purchases | Return Purchase | Purchases | `purchases/return_purchase.php` | P2 | Return |
| 7 | Purchases | View Return | Purchases | `purchases/view_return.php` | P2 | Return details |
| 8 | Purchases | CSV Import | Purchases | `purchases/purchase_by_csv.php` | P2 | Bulk import |
| 9 | Purchases | PDF | Purchases | `purchases/pdf.php` | P2 | Purchase PDF |
| 10 | Purchases | Email | Purchases | `purchases/email.php` | P2 | Email PO |
| 11 | Payments | Payments | Purchases | `purchases/payments.php` | P2 | Payment list |
| 12 | Payments | Add Payment | Purchases | `purchases/add_payment.php` | P2 | Record payment |
| 13 | Payments | Edit Payment | Purchases | `purchases/edit_payment.php` | P2 | Edit payment |
| 14 | Payments | Payment Note | Purchases | `purchases/payment_note.php` | P2 | Receipt |
| 15 | Expenses | Expenses | Purchases | `purchases/expenses.php` | P2 | Expense list |
| 16 | Expenses | Add Expense | Purchases | `purchases/add_expense.php` | P2 | New expense |
| 17 | Expenses | Edit Expense | Purchases | `purchases/edit_expense.php` | P2 | Edit expense |
| 18 | Expenses | Expense Note | Purchases | `purchases/expense_note.php` | P2 | Expense receipt |
| 19 | Notifications | PO Notification | Purchases | `purchases/purchase_notification.php` | P2 | Alerts |
| 20 | Notifications | Notification Items | Purchases | `purchases/notification_items.php` | P2 | Item alerts |
| 21 | Notifications | Inward PO | Purchases | `purchases/inward_po.php` | P2 | Goods receipt |
| 22 | Job Works | Variant PO | Purchases | `job_works/generate_variant_po.php` | P3 | Variant PO |

---

## 11. MODULE 7 — Reports

**Module status:** ⏳ Not done — retest required (2026-07-11)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Dashboard | Reports Home | Reports | `reports/index.php` | P2 | Report menu |
| 2 | Dashboard | BI Report | Reports | `reports/bi_report.php` | P2 | Business intelligence |
| 3 | Dashboard | BI Manage | Reports | `reports/bi_manage.php` | P2 | BI config |
| 4 | Dashboard | Sales Dash | Reports | `reports/sales_dash.php` | P2 | Sales dashboard |
| 5 | Sales Reports | Daily Sales | Reports | `reports/daily.php` | P2 | Day-wise |
| 6 | Sales Reports | Monthly Sales | Reports | `reports/monthly.php` | P2 | Month-wise |
| 7 | Sales Reports | Sales Report | Reports | `reports/sales.php` | P2 | Full sales |
| 8 | Sales Reports | Best Sellers | Reports | `reports/best_sellers.php` | P2 | Top products |
| 9 | Sales Reports | Customer Wise | Reports | `reports/customer_wise_sales.php` | P2 | Per customer |
| 10 | Sales Reports | Product Wise | Reports | `reports/Product_wise_Sale_Report.php` | P2 | Per product |
| 11 | Sales Reports | Sales Person | Reports | `reports/sales_person_report.php` | P2 | Per salesperson |
| 12 | Sales Reports | Overdue Sales | Reports | `reports/overdue_sale.php` | P2 | Due sales |
| 13 | Sales Reports | Sales Due | Reports | `reports/sales_due.php` | P2 | Outstanding |
| 14 | Sales Reports | Term Wise | Reports | `reports/term_wise_sale_report.php` | P2 | Payment terms |
| 15 | Purchase Reports | Purchases | Reports | `reports/purchases.php` | P2 | Purchase report |
| 16 | Purchase Reports | Daily Purchases | Reports | `reports/daily_purchases.php` | P2 | Day-wise |
| 17 | Purchase Reports | Monthly Purchases | Reports | `reports/monthly_purchases.php` | P2 | Month-wise |
| 18 | Purchase Reports | Purchases Due | Reports | `reports/purchases_due.php` | P2 | Outstanding |
| 19 | GST/Tax | Sales GST | Reports | `reports/sales_custome_report.php` | P2 | GST sales |
| 20 | GST/Tax | Purchases GST | Reports | `reports/purchases_gst.php` | P2 | GST purchases |
| 21 | GST/Tax | Tax Reports | Reports | `reports/taxreports.php` | P2 | Tax summary |
| 22 | GST/Tax | Tax Reports New | Reports | `reports/taxreports_new.php` | P2 | Updated tax |
| 23 | GST/Tax | HSN Code | Reports | `reports/hsncode_reports.php` | P2 | HSN report |
| 24 | GST/Tax | HSN New | Reports | `reports/hsncode_reports_new.php` | P2 | Updated HSN |
| 25 | Inventory | Warehouse Stock | Reports | `reports/warehouse_stock.php` | P2 | Stock levels |
| 26 | Inventory | Quantity Alerts | Reports | `reports/quantity_alerts.php` | P2 | Low stock |
| 27 | Inventory | Expiry Alerts | Reports | `reports/expiry_alerts.php` | P2 | Expiring items |
| 28 | Inventory | Products Report | Reports | `reports/products.php` | P2 | Product report |
| 29 | Inventory | Categories | Reports | `reports/categories.php` | P2 | Category report |
| 30 | Inventory | Brands | Reports | `reports/brands.php` | P2 | Brand report |
| 31 | Inventory | Adjustments | Reports | `reports/adjustments.php` | P2 | Stock adjustments |
| 32 | Inventory | Variant Stock | Reports | `reports/product_varient_stock_report.php` | P2 | Variant stock |
| 33 | Financial | Profit/Loss | Reports | `reports/profit_loss.php` | P2 | P&L statement |
| 34 | Financial | Monthly Profit | Reports | `reports/monthly_profit.php` | P2 | Monthly P&L |
| 35 | Financial | Profit | Reports | `reports/profit.php` | P2 | Profit report |
| 36 | Financial | Payments | Reports | `reports/payments.php` | P2 | Payment report |
| 37 | Financial | Payment Summary | Reports | `reports/payments_summary.php` | P2 | Summary |
| 38 | Financial | Expenses | Reports | `reports/expenses.php` | P2 | Expense report |
| 39 | Financial | Cash Transaction | Reports | `reports/cash_transaction.php` | P2 | Cash flow |
| 40 | Financial | Deposit | Reports | `reports/deposit.php` | P2 | Deposits |
| 41 | Customer/Supplier | Customers | Reports | `reports/customers.php` | P2 | Customer report |
| 42 | Customer/Supplier | Customer Report | Reports | `reports/customer_report.php` | P2 | Detailed |
| 43 | Customer/Supplier | Customer Ledger | Reports | `reports/customer_ledgers.php` | P2 | Ledger |
| 44 | Customer/Supplier | Customer Ledger V1 | Reports | `reports/customer_ledger_v1.php` | P2 | Updated ledger |
| 45 | Customer/Supplier | Suppliers | Reports | `reports/suppliers.php` | P2 | Supplier report |
| 46 | Customer/Supplier | Supplier Report | Reports | `reports/supplier_report.php` | P2 | Detailed |
| 47 | Staff/Users | Users | Reports | `reports/users.php` | P2 | User report |
| 48 | Staff/Users | Staff Report | Reports | `reports/staff_report.php` | P2 | Staff activity |
| 49 | Staff/Users | User Log Action | Reports | `reports/user_log_action.php` | P2 | Audit log |
| 50 | Staff/Users | Register | Reports | `reports/register.php` | P2 | Cash register |
| 51 | Transfers | Transfer Report | Reports | `reports/transfer_report.php` | P2 | Stock transfers |
| 52 | Transfers | Transfer Request | Reports | `reports/transfer_request.php` | P2 | Pending requests |
| 53 | Charts | Chart | Reports | `reports/chart.php` | P2 | Visual charts |
| 54 | Charts | Sale Purchase Chart | Reports | `reports/sale_purchase_chart_details.php` | P2 | S/P chart |
| 55 | Charts | Brand Chart | Reports | `reports/brand_chart_details.php` | P2 | Brand chart |
| 56 | Charts | Payment Chart | Reports | `reports/payment_chart_details.php` | P2 | Payment chart |

---

## 12. MODULE 8 — Customers (CRM)

**Module status:** ⏳ Not done — retest required (2026-07-11)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Customers | Customer List | Customers | `customers/index.php` | P2 | Listing |
| 2 | Customers | Add Customer | Customers | `customers/add.php` | P2 | New customer |
| 3 | Customers | Edit Customer | Customers | `customers/edit.php` | P2 | Edit via POS CRM |
| 4 | Customers | Quick Add | Customers | `customers/add_quick.php` | P2 | Quick create |
| 5 | Customers | View Customer | Customers | `customers/view.php` | P2 | Profile view |
| 6 | Customers | Import CSV | Customers | `customers/import.php` | P2 | Bulk import |
| 7 | Users | Users | Customers | `customers/users.php` | P2 | Customer users |
| 8 | Users | Add User | Customers | `customers/add_user.php` | P2 | Sub-user |
| 9 | Addresses | Addresses | Customers | `customers/addresses.php` | P2 | Address list |
| 10 | Addresses | Add Address | Customers | `customers/add_address.php` | P2 | New address |
| 11 | Addresses | Edit Address | Customers | `customers/edit_address.php` | P2 | Edit address |
| 12 | Addresses | Address Modal | Customers | `customers/add_address_modal.php` | P2 | POS modal |
| 13 | Deposits | Deposits | Customers | `customers/deposits.php` | P2 | Deposit list |
| 14 | Deposits | Add Deposit | Customers | `customers/add_deposit.php` | P2 | New deposit |
| 15 | Deposits | Edit Deposit | Customers | `customers/edit_deposit.php` | P2 | Edit deposit |
| 16 | Deposits | Deposit Note | Customers | `customers/deposit_note.php` | P2 | Receipt |
| 17 | Deposits | Deposit History | Customers | `customers/deposits_history.php` | P2 | History |
| 18 | Deposits | Bulk Deposit | Customers | `customers/bulk_deposit.php` | P2 | Bulk upload |

---

## 13. MODULE 9 — Suppliers & Billers

**Module status:** ⏳ Not done — retest required (2026-07-11)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Suppliers | Supplier List | Suppliers | `suppliers/index.php` | P3 | Listing |
| 2 | Suppliers | Add Supplier | Suppliers | `suppliers/add.php` | P3 | New supplier |
| 3 | Suppliers | Edit Supplier | Suppliers | `suppliers/edit.php` | P3 | Edit |
| 4 | Suppliers | View Supplier | Suppliers | `suppliers/view.php` | P3 | Details |
| 5 | Billers | Biller List | Billers | `billers/index.php` | P3 | Listing |
| 6 | Billers | Add Biller | Billers | `billers/add.php` | P3 | New biller |
| 7 | Billers | Edit Biller | Billers | `billers/edit.php` | P3 | Edit |

---

## 14. MODULE 10 — Quotes

**Module status:** ⏳ Not done — retest required (2026-07-11)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Quotes | Quote List | Quotes | `quotes/index.php` | P3 | Listing |
| 2 | Quotes | Add Quote | Quotes | `quotes/add.php` | P3 | New quote |
| 3 | Quotes | Edit Quote | Quotes | `quotes/edit.php` | P3 | Edit quote |
| 4 | Quotes | View Quote | Quotes | `quotes/view.php` | P3 | Details |
| 5 | Quotes | Modal View | Quotes | `quotes/modal_view.php` | P3 | Popup |
| 6 | Quotes | PDF | Quotes | `quotes/pdf.php` | P3 | Quote PDF |
| 7 | Quotes | Email | Quotes | `quotes/email.php` | P3 | Email quote |
| 8 | Quotes | Update Status | Quotes | `quotes/update_status.php` | P3 | Status change |

---

## 15. MODULE 11 — Transfers

**Module status:** ⏳ Not done — retest required (2026-07-11)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Transfers | Transfer List | Transfers | `transfers/index.php` | P3 | Listing |
| 2 | Transfers | Add Transfer | Transfers | `transfers/add.php` | P3 | New transfer |
| 3 | Transfers | Edit Transfer | Transfers | `transfers/edit.php` | P3 | Edit |
| 4 | Transfers | View Transfer | Transfers | `transfers/view.php` | P3 | Details |
| 5 | Transfers | CSV Import | Transfers | `transfers/transfer_by_csv.php` | P3 | Bulk |
| 6 | Transfers | PDF | Transfers | `transfers/pdf.php` | P3 | Transfer PDF |
| 7 | Requests | Request List | Transfers | `transfers/request.php` | P3 | Pending requests |
| 8 | Requests | Add Request | Transfers | `transfers/add_request.php` | P3 | New request |
| 9 | Requests | Edit Request | Transfers | `transfers/edit_request.php` | P3 | Edit request |
| 10 | Requests | View Request | Transfers | `transfers/view_request.php` | P3 | Request details |
| 11 | Requests | RM Transfer | Transfers | `transfers/transfer_rm.php` | P3 | Raw material |
| 12 | Transfers New | All Screens | Transfersnew | `transfers_new/*` | P3 | Review parallel module |

---

## 16. MODULE 12 — Restaurant

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Restaurant | Restaurant Home | Restaurant_Order_Taking | `restaurant/index.php` | P2 | Dashboard |
| 2 | Restaurant | Tables | Restaurant_Order_Taking | `restaurant/tables.php` | P2 | Table management |
| 3 | Restaurant | Orders | Restaurant_Order_Taking | `restaurant/orders.php` | P2 | Order list |
| 4 | Restaurant | Order Screen | Restaurant_Order_Taking | `restaurant/order_screen.php` | P2 | Take order |
| 5 | Restaurant | Kitchen View | Restaurant_Order_Taking | `restaurant/kitchen_view.php` | P2 | KDS display |
| 6 | Restaurant | KOT | Restaurant_Order_Taking | `restaurant/kot.php` | P2 | Kitchen ticket |
| 7 | Restaurant | Order Bill | Restaurant_Order_Taking | `restaurant/order_bill.php` | P2 | Bill generation |
| 8 | Restaurant | Finalize | Restaurant_Order_Taking | `restaurant/finalize_screen.php` | P2 | Payment screen |
| 9 | Restaurant | POS Invoice | Restaurant_Order_Taking | `restaurant/pos_invoice.php` | P2 | Invoice print |
| 10 | Restaurant | Customizations | Restaurant_Order_Taking | `restaurant/modal_product_customizations.php` | P2 | Item modifiers |

---

## 17. MODULE 13 — Production Unit

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Production | Order Dispatch | Production_Unit | `production_unit/order_dispatch.php` | P3 | Dispatch orders |
| 2 | Production | Procurement | Production_Unit | `production_unit/procurement_order.php` | P3 | Raw material PO |
| 3 | Production | Manager Dashboard | Production_Unit | `production_unit/manager_dashboard.php` | P3 | Manager view |
| 4 | Production | Production Dashboard | Production_Unit | `production_unit/production_dashboard.php` | P3 | Production view |
| 5 | Production | Kitchen Dashboard | Production_Unit | `production_unit/kitchen_user_dashboard.php` | P3 | Kitchen user |
| 6 | Production | Receive Delivery | Production_Unit | `production_unit/receive_delivery.php` | P3 | Goods receipt |
| 7 | Production | Ordering History | Production_Unit | `production_unit/ordering_history.php` | P3 | Order history |
| 8 | Production | Add Product | Production_Unit | `production_unit/add_product.php` | P3 | Production item |
| 9 | Production | Inventory | Production_Unit | `production_unit/inventory.php` | P3 | Stock levels |
| 10 | Production | KOT | Production_Unit | `production_unit/kot.php` | P3 | Kitchen ticket |
| 11 | Production | KDS | Production_Unit | `production_unit/kds.php` | P3 | Kitchen display |
| 12 | Production | KDS Display | Production_Unit | `production_unit/kds_D.php` | P3 | Display screen |
| 13 | BOM | Variant BOM | Bill_of_material | `job_works/variant_bill_of_materials.php` | P3 | Bill of materials |

---

## 18. MODULE 14 — Webshop (E-commerce Frontend)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Home | Homepage | Webshop | `webshop/index.php` | P2 | Main store |
| 2 | Home | NW Theme | Webshop | `webshop/nw_theme/index.php` | P2 | Theme 1 |
| 3 | Home | Gulf Pharmacy | Webshop | `webshop/gulfpharmacy_theme/index.php` | P2 | Theme 2 |
| 4 | Home | Restaurant T1 | Webshop | `webshop/webshop_restaurant_t1/index.php` | P2 | Restaurant theme |
| 5 | Home | Service Off | Webshop | `webshop/service_off.php` | P2 | Maintenance page |
| 6 | Products | Products | Webshop | `webshop/products.php` | P2 | Product listing |
| 7 | Products | Product Details | Webshop | theme `product_details.php` | P2 | Single product |
| 8 | Products | Category Products | Webshop | `category_products.php` | P2 | Category page |
| 9 | Products | Search | Webshop | `webshop/search_products.php` | P2 | Search results |
| 10 | Products | Compare | Webshop | `webshop/compare.php` | P2 | Product compare |
| 11 | Products | Wishlist | Webshop | `wishlist.php` | P2 | Wishlist page |
| 12 | Cart & Checkout | Cart | Webshop | `cart.php` | P2 | Shopping cart |
| 13 | Cart & Checkout | Checkout | Webshop | `checkout.php` | P2 | Checkout form |
| 14 | Cart & Checkout | Payments | Webshop | `webshop/payments.php` | P2 | Payment page |
| 15 | Cart & Checkout | Order Success | Webshop | `webshop/order_success.php` | P2 | Confirmation |
| 16 | Cart & Checkout | Razorpay | Webshop | `webshop/razorpay.php` | P2 | Payment gateway |
| 17 | Cart & Checkout | Paytm | Webshop | `webshop/paytm.php` | P2 | Payment gateway |
| 18 | Cart & Checkout | Payment Success | Webshop | `payment_success.php` | P2 | Success page |
| 19 | Cart & Checkout | Payment Declined | Webshop | `payment_declined.php` | P2 | Failed page |
| 20 | Account | Login | Webshop | `login_registration.php` | P2 | Login/Register |
| 21 | Account | Your Account | Webshop | `your_account.php` | P2 | Account dashboard |
| 22 | Account | Your Profile | Webshop | `webshop/your_profile.php` | P2 | Profile edit |
| 23 | Account | Your Address | Webshop | `webshop/your_address.php` | P2 | Address book |
| 24 | Account | Your Orders | Webshop | `webshop/your_orders.php` | P2 | Order history |
| 25 | Account | Order Details | Webshop | `webshop/order_details.php` | P2 | Single order |
| 26 | Account | Change Password | Webshop | `webshop/change_password.php` | P2 | Password change |
| 27 | Account | Forgot Password | Webshop | theme `forgot_password.php` | P2 | Reset password |
| 28 | Account | Views History | Webshop | `webshop/my_views_history.php` | P2 | Browsing history |
| 29 | CMS Pages | About Us | Webshop | theme `about_us.php` | P3 | Static page |
| 30 | CMS Pages | Terms | Webshop | `terms_and_conditions.php` | P3 | Terms page |
| 31 | CMS Pages | Privacy | Webshop | `privacy_policy.php` | P3 | Privacy page |
| 32 | CMS Pages | Contact Us | Webshop | `contact_us.php` | P3 | Contact form |
| 33 | CMS Pages | Track Order | Webshop | `tracking_order.php` | P3 | Order tracking |
| 34 | CMS Pages | Custom Page | Webshop | `webshop/page.php` | P3 | CMS page |

---

## 19. MODULE 15 — Webshop Settings (Admin)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | CMS | Pages | Webshop_settings | `webshop_settings/pages.php` | P3 | CMS pages |
| 2 | CMS | Page Edit | Webshop_settings | `webshop_settings/page_edit.php` | P3 | Edit page |
| 3 | Products | Manage Products | Webshop_settings | `webshop_settings/manage_products.php` | P3 | Online products |
| 4 | CMS | Elements/Sections | Webshop_settings | `webshop_settings/elements_sections/` | P3 | Page builder |

---

## 20. MODULE 16 — Eshop (Mobile App API)

| # | Submodule Group | Submodule | Controller | Screen/API Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-----------------|----------|-------------------|
| 1 | Admin UI | Eshop Admin Pages | Eshop_admin | `eshop/pages.php` | P2 | CMS |
| 2 | Admin UI | Eshop Settings | Eshop_admin | `eshop/settings.php` | P2 | Config |
| 3 | Admin UI | Shipping Methods | Eshop_admin | `eshop/shipping_methods.php` | P2 | Shipping |
| 4 | Admin UI | Manage Products | Eshop_admin | `eshop/eshop_products.php` | P2 | Products |
| 5 | API | Featured Products | Eshop_api | `featured_product` | P2 | API test |
| 6 | API | Categories | Eshop_api | `get_categories` | P2 | API test |
| 7 | API | Product List | Eshop_api | `product_list` | P2 | API test |
| 8 | API | Product Details | Eshop_api | `product_details` | P2 | API test |
| 9 | API | Cart | Eshop_api | `addToCart` | P2 | API test |
| 10 | API | Checkout | Eshop_api | `checkout_details` | P2 | API test |
| 11 | API | Store Info | Eshop_api | `storeInfo` | P2 | API test |

---

## 21. MODULE 17 — Shop (Alternate Storefront)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Templates | Shop T1 | Shop | `shop/T1/*` | P3 | Template 1 |
| 2 | Templates | Shop T2 | Shop | `shop/T2/*` | P3 | Template 2 |
| 3 | Components | Shared Components | Shop | `shop/components/*` | P3 | Shared components |

---

## 22. MODULE 18 — System Settings

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | General | Settings Home | System_settings | `settings/index.php` | P1 | Main settings |
| 2 | General | Change Logo | System_settings | `settings/change_logo.php` | P1 | Logo upload |
| 3 | General | Updates | System_settings | `settings/updates.php` | P1 | System updates |
| 4 | General | Backups | System_settings | `settings/backups.php` | P1 | DB backup |
| 5 | General | Email Templates | System_settings | `settings/email_templates.php` | P1 | Email config |
| 6 | Users | Permissions | System_settings | `settings/permissions.php` | P1 | Role permissions |
| 7 | Users | User Groups | System_settings | `settings/user_groups.php` | P1 | Group list |
| 8 | Users | Create Group | System_settings | `settings/create_group.php` | P1 | New group |
| 9 | Users | Edit Group | System_settings | `settings/edit_group.php` | P1 | Edit group |
| 10 | Master Data | Categories | System_settings | `settings/categories.php` | P1 | Product categories |
| 11 | Master Data | Add/Edit Category | System_settings | `settings/add_category.php` | P1 | Category CRUD |
| 12 | Master Data | Brands | System_settings | `settings/brands.php` | P1 | Brand list |
| 13 | Master Data | Tax Rates | System_settings | `settings/tax_rates.php` | P1 | Tax config |
| 14 | Master Data | Add Tax Rate | System_settings | `settings/add_tax_rate.php` | P1 | New tax |
| 15 | Master Data | Tax Attributes | System_settings | `settings/tax_rates_attr.php` | P1 | Tax attrs |
| 16 | Master Data | Units | System_settings | `settings/units.php` | P1 | Measurement units |
| 17 | Master Data | Variants | System_settings | `settings/variants.php` | P1 | Product variants |
| 18 | Master Data | Warehouses | System_settings | `settings/warehouses.php` | P1 | Warehouse list |
| 19 | Master Data | Currencies | System_settings | `settings/currencies.php` | P1 | Currency config |
| 20 | Master Data | Customer Groups | System_settings | `settings/customer_groups.php` | P1 | Customer groups |
| 21 | Master Data | Price Groups | System_settings | `settings/price_groups.php` | P1 | Pricing groups |
| 22 | Master Data | Expense Categories | System_settings | `settings/expense_categories.php` | P1 | Expense types |
| 23 | POS/Restaurant | Printers | System_settings | `settings/printers.php` | P1 | Printer config |
| 24 | POS/Restaurant | Restaurant Tables | System_settings | `settings/restaurant_tables.php` | P1 | Table setup |
| 25 | POS/Restaurant | Table Price Groups | System_settings | `settings/table_price_groups.php` | P1 | Table pricing |
| 26 | POS/Restaurant | POS Type Labels | System_settings | `settings/pos_type_labels.php` | P1 | POS labels |
| 27 | POS/Restaurant | Manage Barcode | System_settings | `settings/manage_barcode.php` | P1 | Barcode config |
| 28 | Offers | Offer Discount | System_settings | `settings/offer_discount.php` | P2 | Offers |
| 29 | Offers | Offer List | System_settings | `settings/offer_list.php` | P2 | Active offers |
| 30 | Offers | Offer Category | System_settings | `settings/offer_category.php` | P2 | Category offers |
| 31 | Offers | Discount Coupons | System_settings | `settings/discount_coupons.php` | P2 | Coupons |
| 32 | Integrations | PayPal | System_settings | `settings/paypal.php` | P2 | PayPal config |
| 33 | Integrations | Skrill | System_settings | `settings/skrill.php` | P2 | Skrill config |
| 34 | Integrations | SMS Config | System_settings | `settings/sms_configs.php` | P2 | SMS gateway |
| 35 | Integrations | Custom Fields | System_settings | `settings/custom_fields.php` | P2 | Custom fields |
| 36 | Import | Import Categories | System_settings | `settings/import_categories.php` | P2 | Bulk import |
| 37 | Import | Import Brands | System_settings | `settings/import_brands.php` | P2 | Bulk import |
| 38 | Import | Import Subcategories | System_settings | `settings/import_subcategories.php` | P2 | Bulk import |

---

## 23. MODULE 19 — Attendance

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Attendance | Attendance List | Attendance | `attendance/attendance_list.php` | P3 | Daily list |
| 2 | Attendance | Attendance Report | Attendance | `attendance/attendance_report.php` | P3 | Reports |
| 3 | Attendance | Capture (Kiosk) | Attendance | `attendance/attendance_capture.php` | P3 | Public kiosk |
| 4 | Attendance | Details | Attendance | `attendance/attendance_details.php` | P3 | User details |
| 5 | Attendance | Edit Attendance | Attendance | `attendance/attendance_edit.php` | P3 | Edit record |
| 6 | Attendance | Edit User | Attendance | `attendance/edit_user.php` | P3 | User config |

---

## 24. MODULE 20 — Leads (CRM)

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Leads | Leads List | Leads | `leads/index.php` | P3 | Lead listing |
| 2 | Leads | Add Lead | Leads | `leads/add.php` | P3 | New lead |
| 3 | Leads | Edit Lead | Leads | `leads/edit.php` | P3 | Edit lead |
| 4 | Leads | Lead History | Leads | `leads/lead_history.php` | P3 | Activity log |
| 5 | Deals | Deals List | Leads | `leads/list_deals.php` | P3 | Deals pipeline |
| 6 | Deals | Add Deal | Leads | `leads/add_deals.php` | P3 | New deal |
| 7 | Deals | Edit Deal | Leads | `leads/edit_deals.php` | P3 | Edit deal |

---

## 25. MODULE 21 — Service Requests

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Reports | Site Report | Service_requests | `service_requests/service_site_report.php` | P3 | Desktop report |
| 2 | Reports | Mobile Report | Service_requests | `service_requests/service_site_report_mobileview.php` | P3 | Mobile view |
| 3 | Reports | PDF Report | Service_requests | `service_requests/service_site_report_pdf.php` | P3 | PDF export |

---

## 26. MODULE 22 — Urban Piper / Omnichannel

| # | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Orders | Orders (Inactive) | Urban_piper | `urbanpiper/up_orders_inactive.php` | P3 | Inactive orders |
| 2 | Orders | Orders | Urban_piper | `urbanpiper/up_orders.php` | P3 | Active orders |
| 3 | Orders | Combined Orders | Omnichannel | `orders_list_combined` | P3 | All channels |
| 4 | Sales | Sales | Urban_piper | `urbanpiper/sales.php` | P3 | UP sales |
| 5 | Stores | Store List | Urban_piper | `urbanpiper/store_list.php` | P3 | Stores |
| 6 | Stores | Add Store | Urban_piper | `urbanpiper/add_store.php` | P3 | New store |
| 7 | Stores | Update Store | Urban_piper | `urbanpiper/store_update.php` | P3 | Edit store |
| 8 | Settings | Settings | Urban_piper | `urbanpiper/settings.php` | P3 | UP config |
| 9 | Products | Products | Urban_piper | `urbanpiper/product.php` | P3 | Product sync |
| 10 | Products | Platform Products | Urban_piper | `urbanpiper/platform_product_list.php` | P3 | Platform items |
| 11 | Products | Categories | Urban_piper | `urbanpiper/category.php` | P3 | Category sync |
| 12 | Orders | Order KOT | Urban_piper | `urbanpiper/order_kot.php` | P3 | Kitchen ticket |
| 13 | Orders | Rider Info | Urban_piper | `urbanpiper/orderrider_modal.php` | P3 | Delivery rider |

---

## 27. MODULE 23 — APIs (JSON Only — No UI)

| # | Submodule Group | Submodule | Controller | Endpoint / Action | Priority | Test After Upgrade |
|---|-----------------|-----------|------------|-------------------|----------|-------------------|
| 1 | API v3 | Index Router | Api3 | `index` | P2 | Postman/curl |
| 2 | API v3 | Eshop Sync | Api3 | `eshop`, `getCustomers` | P2 | Postman/curl |
| 3 | API v3 | Offline POS | Api3 | `offline`, `synchOfflineposSales` | P2 | Postman/curl |
| 4 | API v3 | Super Admin | Api3 | `super_admin` | P2 | Postman/curl |
| 5 | API v4 | POS Data | Api4 | `getposdata`, `transactions` | P2 | Postman/curl |
| 6 | API v4 | Purchases | Api4 | `getpurchases`, `getPurchaseItems` | P2 | Postman/curl |
| 7 | API v4 | Transfers | Api4 | `transfer` | P2 | Postman/curl |
| 8 | API v4 | Notifications | Api4 | `salesNotification` | P2 | Postman/curl |
| 9 | API Owner | Owner App | ApiOwner | All endpoints | P2 | Postman/curl |
| 10 | REST API | REST v5 | Restapi5 | All endpoints | P2 | Postman/curl |
| 11 | Webhooks | Payment Webhooks | Webhook | Callback URLs | P2 | Callback test |
| 12 | WhatsApp | Send Message | Whatsapp | `send_whatsapp_message` | P2 | API test |
| 13 | WhatsApp | Send OTP | Whatsapp | `send_otp_by_whatsapp` | P2 | API test |
| 14 | Web Service | Generic API | Web_service | `action` | P2 | API test |

---

## 28. MODULE 24 — Other Modules

| # | Module | Submodule Group | Submodule | Controller | Screen Path | Priority | Test After Upgrade |
|---|--------|-----------------|-----------|------------|-------------|----------|-------------------|
| 1 | Employees | HR | Employee List | Employees | `employees/index.php` | P3 | Listing |
| 2 | Employees | HR | Add Employee | Employees | `employees/add.php` | P3 | New employee |
| 3 | Employees | HR | Edit Employee | Employees | `employees/edit.php` | P3 | Edit employee |
| 4 | GSTR | GST | GSTR Report | Gstr | `gstr/index.php` | P2 | GST return |
| 5 | Orders | Orders | Order List | Orders | `orders/index.php` | P2 | Order listing |
| 6 | Orders | Orders | View Order | Orders | `orders/view.php` | P2 | Order details |
| 7 | Payments | Payments | Payment Gateway | Payments | AJAX only | P2 | Payment callback |
| 8 | File Manager | Files | File Manager | File_manager | `file_manager/index.php` | P3 | File browse/upload |
| 9 | SMS Dashboard | SMS | SMS Dashboard | Smsdashboard | `smsdashboard/index.php` | P3 | SMS campaigns |
| 10 | Send SMS/Email | SMS | Send SMS/Email | Sendsmsemail | `sendsmsemail/index.php` | P3 | Bulk SMS/email |
| 11 | Notifications | Alerts | Notifications | Notifications | `notifications/index.php` | P3 | System alerts |
| 12 | Sync | Data | Data Sync | Sync | `sync/index.php` | P2 | Offline sync |
| 13 | Offline | Data | Offline Mode | Offline | `offline/index.php` | P2 | Offline sales |
| 14 | Cron | System | Cron Jobs | Cron, Cron_job | CLI only | P2 | Scheduled tasks |
| 15 | Help | Support | Help | Help | `help/index.php` | P4 | Help docs |
| 16 | Screens | Display | Digital Screens | Screens | `screens/index.php` | P3 | Display screens |
| 17 | Check Stock | Inventory | Stock Check | CheckStock | `checkStock/index.php` | P3 | Stock verification |
| 18 | Calendar | Dashboard | Calendar | Calendar | `calendar.php` | P3 | Event calendar |
| 19 | Vendor Rates | Purchasing | Vendor Rates | Vendor_rates | AJAX only | P3 | Rate management |
| 20 | RM Calculator | Job Works | RM Calculator | RM_Calculator | `job_works/*` | P3 | Raw material calc |
| 21 | Recipies/BOM | Job Works | Recipies | Recipies | `job_works/*` | P3 | Recipe management |
| 22 | Mobile View | Mobile | Products Mobile | mobile_view | `mobile_view/products/*` | P2 | Mobile products |
| 23 | Mobile View | Mobile | Sales Mobile | mobile_view | `mobile_view/sales/*` | P2 | Mobile sales |
| 24 | Mobile View | Mobile | Purchases Mobile | mobile_view | `mobile_view/purchases/*` | P2 | Mobile purchases |
| 25 | Paynear | Payments | Paynear Gateway | Paynear | Payment callback | P2 | Payment test |
| 26 | Payswiff | Payments | Payswiff Gateway | Payswiff | Payment callback | P2 | Payment test |

---

## 29. Screen Count Summary

| # | View Folder | Screens | Module | Priority |
|---|-------------|---------|--------|----------|
| 1 | webshop | 170 | Webshop | P2 |
| 2 | reports | 95 | Reports | P2 |
| 3 | shop | 88 | Shop | P3 |
| 4 | settings | 85 | System Settings | P1 |
| 5 | sales | 58 | Sales | P1 |
| 6 | pos | 53 | POS | P1 |
| 7 | products | 40 | Products | P1 |
| 8 | webshop_settings | 36 | Webshop Settings | P3 |
| 9 | urbanpiper | 30 | Urban Piper | P3 |
| 10 | purchases | 25 | Purchases | P2 |
| 11 | smsdashboard | 22 | SMS Dashboard | P3 |
| 12 | customers | 22 | Customers | P2 |
| 13 | production_unit | 21 | Production Unit | P3 |
| 14 | orders | 21 | Orders | P2 |
| 15 | auth | 17 | Auth | P1 |
| 16 | transfers | 16 | Transfers | P3 |
| 17 | new_reports | 14 | Reports New | P2 |
| 18 | restaurant | 11 | Restaurant | P2 |
| 19 | eshop | 10 | Eshop | P2 |
| 20 | suppliers | 10 | Suppliers | P3 |
| 21 | transfers_new | 10 | Transfers New | P3 |
| 22 | quotes | 9 | Quotes | P3 |
| 23 | leads | 7 | Leads | P3 |
| 24 | attendance | 5 | Attendance | P3 |
| 25 | service_requests | 3 | Service Requests | P3 |

---

## 30. Testing Checklist (Per Module)

| # | Test Item | Description | Pass |
|---|-----------|-------------|------|
| 1 | Screen Load | Screen load होते का (no PHP fatal error) | ☐ |
| 2 | Form Submit | Form submit / save काम करते का | ☐ |
| 3 | AJAX/DataTables | DataTables / AJAX lists load होतात का | ☐ |
| 4 | PDF Generation | PDF generate होतो का (MPDF) | ☐ |
| 5 | Excel Import/Export | Excel import/export (PhpSpreadsheet) | ☐ |
| 6 | Payment Gateway | Payment gateway callback काम करते का | ☐ |
| 7 | File Upload | File upload (images, CSV) काम करते का | ☐ |
| 8 | Session | Session / login persist होते का | ☐ |
| 9 | Multi-tenant | Subdomain DB switch योग्य रीतीने होते का | ☐ |

---

## 31. Recommended Upgrade Order (6 Weeks)

| Week | Phase | Modules | Reason |
|------|-------|---------|--------|
| Week 1 | Phase 1+2 | Framework + Third Party Libraries | Foundation |
| Week 2 | Phase 3+4 | Auth, Dashboard, Settings, POS | Core daily use |
| Week 3 | Phase 4 | Sales, Products, Purchases, Reports | Business critical |
| Week 4 | Phase 4 | Webshop, Eshop API, Restaurant, Production | Customer-facing |
| Week 5 | Phase 4 | Transfers, Quotes, CRM, Attendance, UrbanPiper | Secondary |
| Week 6 | Cleanup | APIs, Cron, Backup file removal | Final + cleanup |

---

## 32. Cleanup Files (Remove During Upgrade)

### 32.1 Controllers (Duplicate Modules)

| # | File Path | Type | Action |
|---|-----------|------|--------|
| 1 | `app/controllers/Sales_backup(08_05_2025).php` | Full backup | Delete |
| 2 | `app/controllers/Pos_backup(08_05_2025).php` | Full backup | Delete |
| 3 | `app/controllers/Production_Unit_Demo.php` | Demo module | Delete |
| 4 | `app/controllers/Production_Unit_New.php` | Alternate module | Review & delete |
| 5 | `app/controllers/Transfersnew.php` | Parallel module | Review & delete |
| 6 | `app/controllers/Reports_new.php` | Parallel module | Review & delete |

### 32.2 Views (Dated / Copy / Backup)

| # | File Path | Type | Action |
|---|-----------|------|--------|
| 1 | `themes/default/views/sales/add(27May2026).php` | Dated copy | Delete |
| 2 | `themes/default/views/sales/edit(27May2026).php` | Dated copy | Delete |
| 3 | `themes/default/views/sales/edit_challan(27May2026).php` | Dated copy | Delete |
| 4 | `themes/default/views/customers/add(26-5-2026).php` | Dated copy | Delete |
| 5 | `themes/default/views/customers/add_quick(05-05-2025).php` | Dated copy | Delete |
| 6 | `themes/default/views/pos/edit_customer_details(26-5-2026).php` | Dated copy | Delete |
| 7 | `themes/default/views/reports/quantity_alerts(20-1-2026).php` | Dated copy | Delete |
| 8 | `themes/default/views/user_access_menu(22-01-2026).php` | Dated copy | Delete |
| 9 | `themes/default/views/admin_access_menu(22-01-2026).php` | Dated copy | Delete |
| 10 | `themes/default/views/settings/permission(22-1-2026).php` | Dated copy | Delete |
| 11 | `themes/default/views/pos/add copy.php` | Copy | Delete |
| 12 | `themes/default/views/pos/add_ep copy.php` | Copy | Delete |
| 13 | `themes/default/views/header copy.php` | Copy | Delete |
| 14 | `themes/default/views/products/modal_view_v1 copy.php` | Copy | Delete |

### 32.3 Views (`_old` / `_bup` Patterns)

| # | Pattern | Example Files | Action |
|---|---------|---------------|--------|
| 1 | `*_old.php` | `sales/pdf_old.php`, `pos/view_old.php` | Delete |
| 2 | `*_bup*.php` | `customers/add_bup.php`, `settings/index_bup.php` | Delete |
| 3 | Date-stamped | `products/index_20_1_26.php` | Delete |

### 32.4 Assets (JS Backups)

| # | File Path | Action |
|---|-----------|--------|
| 1 | `themes/default/assets/pos/js/pos.ajax_ep_backup(08_05_2025).js` | Delete |
| 2 | `themes/default/assets/pos/js/pos_ajax_ep_backup08_05_2025.js` | Delete |
| 3 | `themes/default/assets/pos/js/pos.ajax_ep_backup(17_12_2024).js` | Delete |
| 4 | `themes/default/assets/js/sales_backup(14-12_2024).js` | Delete |
| 5 | `themes/default/assets/pos/js/coinage_checkout(Backup).js` | Delete |

---

## 33. Project Structure

| # | Path | Type | Description | Files |
|---|------|------|-------------|-------|
| 1 | `index.php` | File | Entry point (timezone: Asia/Kolkata) | 1 |
| 2 | `.htaccess` | File | URL rewriting | 1 |
| 3 | `app/` | Directory | Application code | ~16,011 |
| 4 | `app/config/` | Directory | database.php, routes.php, config.php | 21 |
| 5 | `app/controllers/` | Directory | 75 controllers | 75 |
| 6 | `app/models/` | Directory | 55 models | 55 |
| 7 | `app/libraries/` | Directory | Sma.php, Ion_auth, payment libs | 27 |
| 8 | `app/helpers/` | Directory | Utility helpers | 11 |
| 9 | `app/third_party/` | Directory | MPDF, PHPExcel, Stripe, Google SDK | ~14,000 |
| 10 | `app/core/` | Directory | MY_Controller.php, MY_Lang.php | 2 |
| 11 | `system/` | Directory | CodeIgniter 3.1.13 core | 753 |
| 12 | `themes/default/views/` | Directory | 40+ module view folders | ~800 |
| 13 | `themes/default/assets/` | Directory | CSS, JS, images | ~2,000 |
| 14 | `assets/mdata/{subdomain}/` | Directory | Runtime uploads per tenant | ~2,153 |
| 15 | `files/` | Directory | DB backups, updates | 6 |
| 16 | `logs/` | Directory | Application logs | — |

---

## 34. Database Config (Multi-tenant)

| # | Setting | Value |
|---|---------|-------|
| 1 | Config File | `app/config/database.php` |
| 2 | Routing | Subdomain-based |
| 3 | Subdomain (local) | `localhost` |
| 4 | Database Name | `sitadmin_phpupgarde` |
| 5 | Table Prefix | `sma_` |
| 6 | Driver | `mysqli` |
| 7 | Hostname | `localhost` |
| 8 | Username | `root` |
| 9 | Charset | `utf8` |
| 10 | Collation | `utf8_general_ci` |

---

*Generated for PHP upgrade project — CodeIgniter 3.1.13 / ElintPOS ERP*
