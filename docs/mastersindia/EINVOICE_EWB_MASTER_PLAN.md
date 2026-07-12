# E-Invoice + E-Way Bill — Complete Master Plan

**Single document** — screens, flows, functions, logic, DB tables/columns, API, phases.

| | |
|---|---|
| **Project** | ElintOM18.00 (CodeIgniter 3.1.13 / PHP 8.5) |
| **GSP** | Masters India |
| **API docs (local)** | `docs/mastersindia/einvoicing/` · `docs/mastersindia/eway/` |
| **Live docs** | https://docs.mastersindia.co/einvoicing · https://docs.mastersindia.co/eway |
| **Date** | 2026-07-12 |

---

## Table of contents

1. [Executive summary](#1-executive-summary)
2. [Current state](#2-current-state)
3. [Goals & scope](#3-goals--scope)
4. [API reference](#4-api-reference)
5. [Architecture & new files](#5-architecture--new-files)
6. [Database changes (tables & columns)](#6-database-changes-tables--columns)
7. [Configuration](#7-configuration)
8. [Business rules & eligibility](#8-business-rules--eligibility)
9. [Screen map](#9-screen-map)
10. [User flows (A / B / C)](#10-user-flows-a--b--c)
11. [Screen UI design](#11-screen-ui-design)
12. [Function reference (all new code)](#12-function-reference-all-new-code)
13. [Screen → Function → Logic → DB (detailed)](#13-screen--function--logic--db-detailed)
14. [Payload mapping (ElintOM → NIC JSON)](#14-payload-mapping-elintom--nic-json)
15. [E-Way Bill strategy](#15-e-way-bill-strategy)
16. [Error handling & security](#16-error-handling--security)
17. [Phased rollout](#17-phased-rollout)
18. [Testing plan](#18-testing-plan)
19. [Open questions](#19-open-questions)
20. [Related existing files](#20-related-existing-files)

---

## 1. Executive summary

Integrate Masters India GSP into ElintOM to:

1. **Generate IRN** for eligible B2B GST sales
2. Store **IRN, Ack No, Ack Date, Signed QR Code**
3. Print IRN + QR on invoice (A4 + PDF)
4. **Cancel IRN** on return (within NIC window)
5. **Generate E-Way Bill** (by IRN or standalone on challan)
6. Admin settings, audit log, retry on failure

Most GST data already exists (`cgst/sgst/igst`, HSN, `gstn_no`). Work = API layer + DB columns + UI — **not** rebuilding tax logic.

**Three main paths:**

```
Path A: Sales → Invoice → IRN → (optional) EWB     ← most common (B2B)
Path B: Challan → EWB standalone → Convert → IRN
Path C: Return Sale → Cancel IRN on original invoice
```

---

## 2. Current state

| Area | Status | Location |
|------|--------|----------|
| GST on sales | ✅ Ready | `sma_sales`, `sma_sale_items`, `sma_sales_items_tax` |
| HSN per line | ✅ Ready | `sale_items.hsn_code`, `products.hsn_code` |
| GSTIN parties | ✅ Ready | `companies.gstn_no`, `state_code`, `addresses` |
| IGST vs CGST+SGST | ✅ Ready | `Sales.php` / `Pos.php` + `Sma::taxAtrrClassification()` |
| GST invoice print | ✅ Ready | `themes/default/views/sales/view_a4_new_format.php` |
| Transport on sale | ✅ Partial | `sales.transporter_mode`, `LR_No`, `place_of_supply` |
| IRN / EWB | ❌ Missing | No columns, no API |
| GSP integration | ❌ Missing | No Masters India library |

**Naming:** codebase uses `gstn_no` (not `gstin`).

---

## 3. Goals & scope

### In scope (Phases 1–4)

- Generate IRN for B2B GST sales (Sales; POS optional)
- Persist IRN + audit log; print QR on A4/PDF
- Cancel IRN on sale return
- E-Way Bill by IRN + standalone on challan
- System Settings: enable, sandbox/prod, API key
- Manual retry + error on sale view

### Out of scope (initial)

- Bulk IRN/EWB, GSTR-1 filing, multi-GSP
- Changing tax calculation logic
- POS auto-IRN for B2C (default OFF)

---

## 4. API reference

### Environments

| Env | Portal | API base |
|-----|--------|----------|
| Sandbox | https://sandb-edoc.mastersindia.co | https://sandb-api.mastersindia.co |
| Production | https://edoc.mastersindia.co | https://router.mastersindia.co |

### Authentication

| Method | Header |
|--------|--------|
| API Key (recommended) | `api_key: <key>` |
| JWT | `Authorization: JWT <token>` from `POST /api/v1/token-auth/` |

### Endpoints

| Operation | Method | Path | When |
|-----------|--------|------|------|
| **Generate IRN** | POST | `/api/v1/einvoice/` | After B2B tax invoice save |
| Cancel IRN | POST | `/api/v1/cancel-einvoice/` | Return / cancel |
| **EWB by IRN** | POST | `/api/v1/gen-ewb-by-irn/` | After IRN + transport data |
| **EWB standalone** | POST | `/api/v1/ewayBillsGenerate/` | Challan (no IRN) |
| Cancel EWB | POST | `/api/v1/ewayBillCancel/` | Cancel movement |
| Get IRN by doc | GET | `/api/v1/get-einvoice-bydoc/` | Duplicate recovery |
| GSTIN lookup | GET | `/api/v1/get-gstin-details/` | Sync biller/customer |
| Update vehicle | POST | `/api/v1/updateVehicleNumber/` | EWB Part-B |

**Local docs:** `einvoicing/generate-irn.md`, `eway/generate-e-way-bill.md`

---

## 5. Architecture & new files

```
Sale Saved (Sales_model / Pos_model)
        ↓
Einvoice_service.php     ← eligibility, build payload, orchestrate
        ↓
Mastersindia.php         ← curl + JSON + auth
        ↓
Masters India API
        ↓
Einvoice_model.php       ← save IRN/EWB + audit log
        ↓
Invoice print view       ← IRN + QR on A4/PDF
```

### New files to create

| File | Class / role |
|------|----------------|
| `app/config/mastersindia.php` | API URL, timeout, flags |
| `app/libraries/Mastersindia.php` | HTTP client |
| `app/libraries/Einvoice_service.php` | Business logic + payload mapper |
| `app/models/Einvoice_model.php` | DB read/write + audit |
| `app/controllers/Einvoice.php` | Admin actions (generate, cancel, EWB) |
| `themes/default/views/einvoice/_status_panel.php` | IRN/EWB UI partial |
| `themes/default/views/einvoice/settings.php` | Settings form |
| `upgrade/sql/einvoice_alter_sales.sql` | DB migration |

**Patterns to follow:** `app/config/payment_gateways.php`, `app/models/Urban_piper_model.php`

### New controller routes (`Einvoice.php`)

| Route | Method | Purpose |
|-------|--------|---------|
| `einvoice/settings` | GET/POST | Admin config |
| `einvoice/generate_irn/{sale_id}` | POST | Generate IRN |
| `einvoice/cancel_irn/{sale_id}` | POST | Cancel IRN |
| `einvoice/generate_ewb/{sale_id}` | POST | EWB by IRN |
| `einvoice/generate_ewb_challan/{challan_id}` | POST | Standalone EWB |
| `einvoice/retry/{sale_id}` | POST | Retry failed IRN |
| `einvoice/save_transport/{sale_id}` | POST | Save vehicle/transporter |
| `einvoice/sync_gstin` | POST | GSTIN lookup AJAX |
| `einvoice/log/{sale_id}` | GET | Audit log (admin) |

---

## 6. Database changes (tables & columns)

### 6.1 `sma_sales` — NEW columns

| Column | Type | Default | Purpose |
|--------|------|---------|---------|
| `irn` | VARCHAR(64) | NULL | Invoice Reference Number |
| `ack_no` | VARCHAR(20) | NULL | NIC acknowledgement |
| `ack_dt` | DATETIME | NULL | Ack datetime |
| `signed_qr_code` | TEXT | NULL | QR JWT for print |
| `signed_invoice` | TEXT | NULL | Signed payload archive |
| `einvoice_status` | ENUM('none','pending','success','failed','cancelled') | 'none' | IRN lifecycle |
| `einvoice_request_id` | VARCHAR(100) | NULL | Masters India requestId |
| `einvoice_error` | TEXT | NULL | Last NIC error |
| `einvoice_pdf_url` | VARCHAR(255) | NULL | Hosted PDF URL |
| `ewb_no` | VARCHAR(20) | NULL | E-Way Bill number |
| `ewb_dt` | DATETIME | NULL | EWB date |
| `ewb_valid_till` | DATETIME | NULL | EWB validity |
| `ewb_pdf_url` | VARCHAR(255) | NULL | EWB PDF URL |
| `ewb_status` | ENUM('none','pending','success','failed','cancelled') | 'none' | EWB lifecycle |
| `ewb_transporter_id` | VARCHAR(20) | NULL | Transporter GSTIN |
| `ewb_vehicle_no` | VARCHAR(20) | NULL | Vehicle (Part-B) |
| `ewb_transport_mode` | VARCHAR(5) | NULL | 1=Road, 2=Rail, 3=Air, 4=Ship |
| `ewb_distance_km` | INT | NULL | Distance km |

### 6.2 `sma_sales` — EXISTING columns (reuse, no change)

| Column | Use |
|--------|-----|
| `cgst`, `sgst`, `igst` | IRN `value_details` |
| `invoice_no`, `date`, `grand_total`, `rounding` | Document identity & totals |
| `biller_id`, `customer_id` | Party FK → `companies` |
| `billing_address_id`, `shipping_address_id` | Addresses |
| `transporter_mode`, `LR_No`, `place_of_supply` | EWB transport |
| `sale_status`, `pos` | Eligibility gates |

### 6.3 `sma_einvoice_log` — NEW table

| Column | Type | Purpose |
|--------|------|---------|
| `id` | INT PK AUTO_INCREMENT | Log ID |
| `doc_type` | ENUM('sale','challan') | Document type |
| `doc_id` | INT | `sales.id` or `delivery_challan.id` |
| `action` | VARCHAR(50) | generate_irn, cancel_irn, gen_ewb, etc. |
| `request_json` | LONGTEXT | Full request |
| `response_json` | LONGTEXT | Full response |
| `status` | VARCHAR(20) | Success / Failed |
| `error_message` | TEXT | NIC error |
| `request_id` | VARCHAR(100) | MI requestId |
| `created_at` | DATETIME | Timestamp |
| `created_by` | INT | User ID |

### 6.4 `sma_delivery_challan` — NEW columns (Phase 3)

| Column | Type | Purpose |
|--------|------|---------|
| `ewb_no` | VARCHAR(20) | Standalone EWB |
| `ewb_dt` | DATETIME | EWB date |
| `ewb_valid_till` | DATETIME | Validity |
| `ewb_status` | ENUM(...) | Status |
| `ewb_pdf_url` | VARCHAR(255) | PDF |
| `ewb_vehicle_no` | VARCHAR(20) | Vehicle |

**Existing reuse:** `transporter_mode`, `LR_No`, `way_bill_no`, `place_of_supply`, `cgst`, `sgst`, `igst`

### 6.5 `sma_settings` — NEW columns (Phase 4)

| Column | Type | Default | Purpose |
|--------|------|---------|---------|
| `einvoice_enabled` | TINYINT(1) | 0 | Master ON/OFF |
| `einvoice_auto_irn` | TINYINT(1) | 1 | Auto on sale save |
| `einvoice_pos_enabled` | TINYINT(1) | 0 | POS B2B IRN |
| `einvoice_environment` | VARCHAR(20) | 'sandbox' | sandbox / production |
| `einvoice_api_key` | VARCHAR(255) | NULL | API key |
| `ewb_enabled` | TINYINT(1) | 1 | EWB ON |
| `ewb_default_transporter_id` | VARCHAR(20) | NULL | Default transporter |

### 6.6 Existing tables — READ ONLY (no schema change)

| Table | Key columns |
|-------|-------------|
| `sma_companies` | `gstn_no`, `state_code`, `gst_state_code`, `address`, `city`, `postal_code`, `phone`, `email` |
| `sma_sale_items` | `hsn_code`, `gst_rate`, `cgst`, `sgst`, `igst`, `unit_quantity`, `net_unit_price`, `subtotal` |
| `sma_sales_items_tax` | `attr_code`, `attr_per`, `tax_amount`, `item_id`, `sale_id` |
| `sma_addresses` | `state_code`, address lines |
| `sma_deliveries` | `sale_id`, `address`, `pincode` (optional EWB cols Phase 3) |

### 6.7 SQL migration script

**File:** `upgrade/sql/einvoice_alter_sales.sql`

```sql
ALTER TABLE sma_sales
  ADD COLUMN irn VARCHAR(64) DEFAULT NULL,
  ADD COLUMN ack_no VARCHAR(20) DEFAULT NULL,
  ADD COLUMN ack_dt DATETIME DEFAULT NULL,
  ADD COLUMN signed_qr_code TEXT DEFAULT NULL,
  ADD COLUMN signed_invoice TEXT DEFAULT NULL,
  ADD COLUMN einvoice_status ENUM('none','pending','success','failed','cancelled') DEFAULT 'none',
  ADD COLUMN einvoice_request_id VARCHAR(100) DEFAULT NULL,
  ADD COLUMN einvoice_error TEXT DEFAULT NULL,
  ADD COLUMN einvoice_pdf_url VARCHAR(255) DEFAULT NULL,
  ADD COLUMN ewb_no VARCHAR(20) DEFAULT NULL,
  ADD COLUMN ewb_dt DATETIME DEFAULT NULL,
  ADD COLUMN ewb_valid_till DATETIME DEFAULT NULL,
  ADD COLUMN ewb_pdf_url VARCHAR(255) DEFAULT NULL,
  ADD COLUMN ewb_status ENUM('none','pending','success','failed','cancelled') DEFAULT 'none',
  ADD COLUMN ewb_transporter_id VARCHAR(20) DEFAULT NULL,
  ADD COLUMN ewb_vehicle_no VARCHAR(20) DEFAULT NULL,
  ADD COLUMN ewb_transport_mode VARCHAR(5) DEFAULT NULL,
  ADD COLUMN ewb_distance_km INT DEFAULT NULL;

CREATE TABLE sma_einvoice_log (
  id INT NOT NULL AUTO_INCREMENT,
  doc_type ENUM('sale','challan') NOT NULL DEFAULT 'sale',
  doc_id INT NOT NULL,
  action VARCHAR(50) NOT NULL,
  request_json LONGTEXT,
  response_json LONGTEXT,
  status VARCHAR(20) DEFAULT NULL,
  error_message TEXT,
  request_id VARCHAR(100) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_by INT DEFAULT NULL,
  PRIMARY KEY (id),
  KEY doc_type_id (doc_type, doc_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

---

## 7. Configuration

### `app/config/mastersindia.php`

```php
$config['mi_environment']     = 'sandbox';
$config['mi_api_url']         = 'https://sandb-api.mastersindia.co';
$config['mi_api_key']         = '';
$config['mi_data_source']     = 'erp';
$config['mi_enabled']         = FALSE;
$config['mi_auto_irn']        = TRUE;
$config['mi_pos_enabled']     = FALSE;
$config['mi_request_timeout'] = 15;
```

---

## 8. Business rules & eligibility

### IRN — ALL must be true

| # | Rule | Check |
|---|------|-------|
| 1 | E-Invoice enabled | `sma_settings.einvoice_enabled` or config |
| 2 | Sale completed | `sales.sale_status = 'completed'` |
| 3 | GST sale | `Site::isGstSale($sale_id)` → rows in `sales_items_tax` |
| 4 | Biller GSTIN | `companies.gstn_no` (biller) — 15 chars |
| 5 | Customer GSTIN | `companies.gstn_no` (customer) — B2B |
| 6 | No IRN yet | `sales.irn IS NULL` |
| 7 | Not challan-only | Normal invoice (`INV`) |
| 8 | POS | Only if `einvoice_pos_enabled = 1` |

### EWB — ANY of

- IRN exists + transport fields filled → `gen-ewb-by-irn`
- OR challan with transport → standalone `ewayBillsGenerate`
- Vehicle OR transporter ID required

### Auto vs manual

| Event | Auto (setting ON) | Manual |
|-------|-------------------|--------|
| Sale save → IRN | `maybe_auto_irn()` | Button on Sale View |
| EWB | **Always manual** | Sale View / Add Delivery |
| Return → Cancel IRN | Auto attempt | Same |
| POS | OFF default | Manual if B2B enabled |

---

## 9. Screen map

### Setup (once)

| ID | Screen | URL | Controller::method | View file |
|----|--------|-----|-------------------|-----------|
| S1 | System Settings | `/system_settings` | `System_settings::index()` | `settings/index.php` |
| S2 | Biller Add/Edit | `/billers/edit/{id}` | `Billers::edit()` | `billers/edit.php` |
| S3 | Customer Add/Edit | `/customers/edit/{id}` | `Customers::edit()` | `customers/edit.php` |
| S4 | Tax Rates | `/system_settings/tax_rates` | `System_settings::tax_rates()` | `settings/tax_rates.php` |
| S5 | Printers | `/system_settings/printers` | `System_settings::printers()` | `settings/printers.php` |

### Daily operations

| ID | Screen | URL | Controller::method | View | E-Inv | EWB |
|----|--------|-----|-------------------|------|-------|-----|
| D1 | Sales List | `/sales` | `Sales::index()` / `getSales()` | `sales/index.php` | Col | Col |
| D2 | Add Sale | `/sales/add` | `Sales::add()` | `sales/add.php` | Auto/manual | — |
| D3 | **Sale View** ★ | `/sales/view/{id}` | `Sales::view()` | `sales/view.php` | **Hub** | Button |
| D4 | Modal View | `/sales/modal_view/{id}` | `Sales::modal_view()` | `sales/modal_view.php` | Compact | Compact |
| D5 | PDF Invoice | `/sales/pdf/{id}` | `Sales::pdf()` | `sales/pdf_reciept.php` | Print QR | Print EWB |
| D6 | A4 GST Format | (included) | — | `sales/view_a4_new_format.php` | Print QR | Print EWB |
| D7 | Return Sale | `/sales/return_sale/{id}` | `Sales::return_sale()` | `sales/return_sale.php` | Cancel | Cancel |
| D8 | Add Delivery | `/sales/add_delivery/{id}` | `Sales::add_delivery()` | `sales/add_delivery.php` | — | Transport |
| D9 | Deliveries | `/sales/deliveries` | `Sales::deliveries()` | `sales/deliveries.php` | Link | Status |

### Challan

| ID | Screen | URL | Controller | View | E-Inv | EWB |
|----|--------|-----|------------|------|-------|-----|
| C1 | Challan List | `/sales/challans` | `Sales::challans()` | `sales/challans.php` | — | Col |
| C2 | Add Challan | `/sales/add?sale_action=chalan` | `Sales::add()` | `sales/add.php` | — | Capture |
| C3 | Challan View | `/sales/challan_view/{id}` | `Sales::challan_view()` | `sales/view_challan.php` | — | Standalone |
| C4 | Convert to Sale | `/sales/add_sale_from_chalan` | `Sales::add_sale_from_chalan()` | → sale | IRN new | Link |

### POS (Phase 5, default OFF)

| ID | Screen | URL | Controller | View |
|----|--------|-----|------------|------|
| P1 | POS Billing | `/pos` | `Pos::index()` | `pos/add.php` |
| P2 | POS Receipt | `/pos/view/{id}` | `Pos::view()` | `pos/view.php` |

### Reports (Phase 4, read-only)

| ID | Screen | URL |
|----|--------|-----|
| R1 | Sales GST Report | `/reports/sales_gst_report` |
| R2 | Sales Report | `/reports/sales` |
| R3 | GST Reports New | `/reports_new/gst_reports_new` |

---

## 10. User flows (A / B / C)

### Flow A — B2B Sale → IRN → EWB (main)

| Step | User action | Screen | System function | DB |
|------|-------------|--------|-----------------|-----|
| 1 | Enable E-Invoice + API key | S1 | `System_settings::index()` POST | UPDATE `sma_settings` |
| 2 | Enter biller GSTIN | S2 | `Billers::edit()` | UPDATE `sma_companies.gstn_no` |
| 3 | Enter customer GSTIN | S3 | `Customers::edit()` | UPDATE `sma_companies.gstn_no` |
| 4 | Create sale, save | D2 | `Sales::add()` → `Sales_model::addSale()` | INSERT `sma_sales`, `sma_sale_items`, `sma_sales_items_tax` |
| 5 | Generate IRN (auto or button) | D3 | `Einvoice_service::generate_irn()` | UPDATE `sma_sales.irn`, `ack_*`, `signed_qr_code` |
| 6 | Print invoice | D5 | `Sales::pdf()` | READ `sma_sales` |
| 7 | Enter vehicle/LR | D8 | `Sales::add_delivery()` + `save_transport()` | UPDATE `sma_sales.ewb_*`, INSERT `sma_deliveries` |
| 8 | Generate EWB | D3 | `Einvoice_service::generate_ewb_by_irn()` | UPDATE `sma_sales.ewb_no` |
| 9 | Print with EWB | D5/D6 | view print | READ `sma_sales` |

### Flow B — Challan → EWB before invoice

| Step | Screen | Function | DB |
|------|--------|----------|-----|
| 1 | C2 Add Challan | `Sales_model::addSale()` (chalan mode) | INSERT `sma_delivery_challan` |
| 2 | C3 Generate EWB | `Einvoice::generate_ewb_challan()` | UPDATE `sma_delivery_challan.ewb_*` |
| 3 | C4 Convert to Sale | `Sales::add_sale_from_chalan()` | INSERT `sma_sales` |
| 4 | D3 Generate IRN | `Einvoice_service::generate_irn()` | UPDATE `sma_sales.irn` |

### Flow C — Return → Cancel IRN

| Step | Screen | Function | DB |
|------|--------|----------|-----|
| 1 | D7 Return Sale | `Sales::return_sale()` | INSERT return `sma_sales` |
| 2 | Auto cancel | `Einvoice_service::cancel_irn()` → `Mastersindia::cancel_irn()` | UPDATE original `sma_sales.einvoice_status=cancelled` |

---

## 11. Screen UI design

### S1 — System Settings (new section in `settings/index.php`)

```
┌─────────────────────────────────────────────────────────────┐
│  GST / E-Invoice (Masters India)                            │
│  [✓] Enable E-Invoicing    [✓] Auto-generate IRN on save    │
│  [ ] Enable on POS (B2B)   Environment: Sandbox / Production│
│  API Key: [••••••••]  [Test Connection]                     │
│  [✓] Enable E-Way Bill    Default Transporter: [________]   │
│  [Save Settings]                                            │
└─────────────────────────────────────────────────────────────┘
```

### D3 — Sale View ★ (`sales/view.php` + `_status_panel.php`)

**Before IRN:** Status ⚪ Not Generated → `[ Generate IRN ]`

**Success:** 🟢 IRN + Ack + QR → `[ Cancel IRN ]` `[ Generate E-Way Bill ]`

**Failed:** 🔴 Error message → `[ Retry Generate IRN ]`

**EWB success:** 🟢 EWB No + Valid Till → `[ Print EWB PDF ]`

### D5/D6 — Print block (`view_a4_new_format.php`, `pdf_reciept.php`)

```
─── E-Invoice Details ───
IRN: ...    Ack No: ...    Ack Date: ...
        [ QR CODE 2x2 cm ]
─── E-Way Bill ───
EWB No: ...    Valid Upto: ...
```

### D8 — Add Delivery (`add_delivery.php`)

Add section: Transporter ID, Vehicle No, Mode, Distance, LR No  
Buttons: `[Save Delivery]` `[Save & Generate E-Way Bill]`

---

## 12. Function reference (all new code)

### 12.1 `Mastersindia` — `app/libraries/Mastersindia.php`

| Function | Logic | API |
|----------|-------|-----|
| `__construct()` | Load config + Settings | — |
| `get_api_url()` | Sandbox or production URL | — |
| `get_headers()` | `api_key` or JWT header | — |
| `request($method, $path, $body, $query)` | curl → decode JSON | All |
| `generate_irn($payload)` | POST | `/einvoice/` |
| `cancel_irn($payload)` | POST | `/cancel-einvoice/` |
| `generate_ewb_by_irn($payload)` | POST | `/gen-ewb-by-irn/` |
| `generate_ewb_standalone($payload)` | POST | `/ewayBillsGenerate/` |
| `cancel_ewb($payload)` | POST | `/ewayBillCancel/` |
| `get_irn_by_doc(...)` | GET | `/get-einvoice-bydoc/` |
| `get_gstin_details($gstin)` | GET | `/get-gstin-details/` |
| `parse_response($raw)` | Check `results.status`; return message or error | — |

### 12.2 `Einvoice_service` — `app/libraries/Einvoice_service.php`

| Function | Logic |
|----------|-------|
| `is_enabled()` | Settings flag |
| `is_eligible_for_irn($sale_id)` | All 8 rules (§8) |
| `is_eligible_for_ewb_by_irn($sale_id)` | IRN exists + transport |
| `build_irn_payload($sale_id)` | Load sale/parties/items → NIC JSON (§14) |
| `build_ewb_by_irn_payload($sale_id, $transport)` | IRN + transport fields |
| `build_ewb_standalone_payload($challan_id, $transport)` | Challan → EWB JSON |
| **`generate_irn($sale_id)`** | See step logic below |
| `cancel_irn($sale_id, $reason, $remarks)` | POST cancel → update status |
| `generate_ewb_by_irn($sale_id, $transport)` | POST EWB → save ewb_* |
| `generate_ewb_challan($challan_id, $transport)` | Standalone EWB on challan |
| `maybe_auto_irn($sale_id)` | If auto + eligible → generate; **never rollback sale** |
| `sync_gstin($gstin)` | Lookup → return name/address |
| `format_doc_date($datetime)` | → `DD/MM/YYYY` |
| `map_unit_uqc($unit_code)` | ElintOM unit → NIC UQC |

**`generate_irn($sale_id)` step logic:**

```
1. is_eligible_for_irn() → false → return error
2. Einvoice_model::save_irn_pending()     → einvoice_status=pending
3. build_irn_payload()
4. Mastersindia::generate_irn($payload)
5. parse_response()
6. IF failed → save_irn_failed() + insert_log() → return error
7. IF success → save_irn_response() + insert_log() → return IRN+QR
8. IF duplicate → get_irn_by_doc() → recover and save
```

### 12.3 `Einvoice_model` — `app/models/Einvoice_model.php`

| Function | Tables touched |
|----------|----------------|
| `save_irn_response($sale_id, $response, $request_json)` | UPDATE `sma_sales` (irn, ack_*, signed_qr_code, status=success) |
| `save_irn_failed($sale_id, $error, ...)` | UPDATE `sma_sales` + INSERT `sma_einvoice_log` |
| `save_irn_pending($sale_id)` | UPDATE `sma_sales.einvoice_status=pending` |
| `save_irn_cancelled($sale_id, $response)` | UPDATE `sma_sales.einvoice_status=cancelled` |
| `save_ewb_response($sale_id, $response)` | UPDATE `sma_sales.ewb_*` |
| `save_ewb_challan_response($challan_id, $response)` | UPDATE `sma_delivery_challan.ewb_*` |
| `save_transport($sale_id, $transport)` | UPDATE `sma_sales.ewb_vehicle_no`, etc. |
| `insert_log(...)` | INSERT `sma_einvoice_log` |
| `get_sale_einvoice($sale_id)` | SELECT from `sma_sales` |

### 12.4 `Einvoice` controller — `app/controllers/Einvoice.php`

| Function | Calls | DB |
|----------|-------|-----|
| `settings()` | Save/read config | `sma_settings` |
| `generate_irn($sale_id)` | `Einvoice_service::generate_irn()` | `sma_sales`, log |
| `retry_irn($sale_id)` | Same as generate | same |
| `cancel_irn($sale_id)` | `Einvoice_service::cancel_irn()` | `sma_sales` |
| `generate_ewb($sale_id)` | `generate_ewb_by_irn()` | `sma_sales` |
| `generate_ewb_challan($challan_id)` | `generate_ewb_challan()` | `sma_delivery_challan` |
| `save_transport($sale_id)` | `Einvoice_model::save_transport()` | `sma_sales` |
| `sync_gstin()` | AJAX → `sync_gstin()` | log |

### 12.5 Existing functions — hooks (modify minimally)

| Function | File | Hook logic | Phase |
|----------|------|------------|-------|
| `Sales_model::addSale()` | ~L800 | Before `return $sale_id`: `maybe_auto_irn($sale_id)` if completed sale | 4 |
| `Pos_model::addSale()` | ~L819 | Same if POS enabled | 5 |
| `Sales_model::updateSale()` | ~L806 | If IRN exists → warn / cancel per NIC rules | 4 |
| `Sales::view($id)` | ~L682 | Load `$this->data['einvoice']`, include `_status_panel.php` | 1 |
| `Sales::getSales()` | ~L64 | Add `einvoice_status`, `ewb_no` to SELECT | 2 |
| `Sales::return_sale($id)` | ~L3051 | After return: `cancel_irn()` on original | 4 |
| `Sales::add_delivery($id)` | ~L4638 | Save transport + optional EWB trigger | 3 |
| `Sales::challan_view($Id)` | ~L8221 | Show EWB panel | 3 |
| `Sales::pdf($id)` | ~L774 | IRN block in print views | 2 |

### 12.6 Existing functions — read only (no change)

| Function | File | Use |
|----------|------|-----|
| `getInvoiceByID($id)` | Sales_model.php | Sale header |
| `getAllInvoiceItems($id)` | Sales_model.php | Line items |
| `getAllTaxItems($id)` | Sales_model.php | CGST/SGST/IGST |
| `getCompanyByID($id)` | Site.php | Biller/customer |
| `isGstSale($id)` | Site.php | GST check |
| `getstatecode($company_id)` | Sma.php | State code |
| `taxAtrrClassification()` | Sma.php | Tax rows on save |
| `qrcode()` | Sma.php | QR on print |

---

## 13. Screen → Function → Logic → DB (detailed)

| Screen | URL | User action | Controller::method | Service/Model function | Logic summary | Table.column |
|--------|-----|-------------|-------------------|------------------------|---------------|--------------|
| **S1 Settings** | `/system_settings` | Save API key | `System_settings::index()` | — | Save flags to settings | `sma_settings.einvoice_*` |
| **S2 Biller** | `/billers/edit/{id}` | Sync GSTIN | AJAX → `Einvoice::sync_gstin()` | `sync_gstin()` | GET GST portal details | READ `sma_companies` |
| **D2 Add Sale** | `/sales/add` | Save invoice | `Sales::add()` → `addSale()` | (Phase 4) `maybe_auto_irn()` | Insert sale + items + tax | INSERT `sma_sales`, `sma_sale_items` |
| **D3 Sale View** | `/sales/view/{id}` | Generate IRN | `Einvoice::generate_irn()` | `generate_irn()` | Build JSON → API → save | UPDATE `sma_sales.irn`, `ack_no`, `signed_qr_code` |
| **D3 Sale View** | `/sales/view/{id}` | Generate EWB | `Einvoice::generate_ewb()` | `generate_ewb_by_irn()` | IRN + transport → API | UPDATE `sma_sales.ewb_no` |
| **D3 Sale View** | `/sales/view/{id}` | Cancel IRN | `Einvoice::cancel_irn()` | `cancel_irn()` | POST cancel | UPDATE `sma_sales.einvoice_status` |
| **D5 PDF** | `/sales/pdf/{id}` | Print | `Sales::pdf()` | — | Read IRN/QR from sale | READ `sma_sales.irn` |
| **D1 List** | `/sales` | View status | `Sales::getSales()` | — | SELECT einvoice_status | READ `sma_sales` |
| **D8 Delivery** | `/sales/add_delivery/{id}` | Save transport | `Sales::add_delivery()` | `save_transport()` | Vehicle, transporter | UPDATE `sma_sales.ewb_*` |
| **D7 Return** | `/sales/return_sale/{id}` | Return qty | `Sales::return_sale()` | `cancel_irn()` on original | Cancel IRN | UPDATE original `sma_sales` |
| **C3 Challan** | `/sales/challan_view/{id}` | Generate EWB | `Einvoice::generate_ewb_challan()` | `generate_ewb_challan()` | Standalone API | UPDATE `sma_delivery_challan.ewb_no` |
| **C4 Convert** | `/sales/add_sale_from_chalan` | Convert | `add_sale_from_chalan()` | — | New sale record | INSERT `sma_sales` |

### Complete function call chain

```
[Setup]
System_settings::index() → UPDATE sma_settings

[Add Sale]
Sales::add() → Sales_model::addSale()
  → INSERT sma_sales, sma_sale_items, sma_sales_items_tax
  → UPDATE sales.invoice_no
  → (Phase 4) Einvoice_service::maybe_auto_irn()
       → Mastersindia::generate_irn()
       → Einvoice_model::save_irn_response()
       → UPDATE sma_sales.irn, ack_*, signed_qr_code
       → INSERT sma_einvoice_log

[Manual IRN]
Sales::view() → User clicks Generate IRN
  → Einvoice::generate_irn($id)
       → (same chain as above)

[EWB]
Einvoice::save_transport() → UPDATE sma_sales.ewb_vehicle_no, ...
Einvoice::generate_ewb() → generate_ewb_by_irn()
  → Mastersindia::generate_ewb_by_irn()
  → Einvoice_model::save_ewb_response()
  → UPDATE sma_sales.ewb_no, ewb_valid_till

[Print]
Sales::pdf() → view_a4_new_format.php reads sma_sales.irn, signed_qr_code

[Return]
Sales::return_sale() → Einvoice_service::cancel_irn()
  → Mastersindia::cancel_irn()
  → UPDATE sma_sales.einvoice_status = cancelled
```

---

## 14. Payload mapping (ElintOM → NIC JSON)

**Endpoint:** `POST /api/v1/einvoice/` · **Doc:** `einvoicing/generate-irn.md`

### Data load chain

```
getInvoiceByID($sale_id)              → sma_sales
getCompanyByID($biller_id)            → sma_companies (seller)
getCompanyByID($customer_id)          → sma_companies (buyer)
getAllInvoiceItems($sale_id)          → sma_sale_items
getAllTaxItems($sale_id)              → sma_sales_items_tax
isGstSale($sale_id)                   → sma_sales_items_tax
Sma::getstatecode($company_id)        → sma_companies.state_code
```

### Field mapping

| NIC JSON | Source |
|----------|--------|
| `user_gstin` | biller `companies.gstn_no` |
| `document_details.document_number` | `sales.invoice_no` |
| `document_details.document_date` | `format_doc_date(sales.date)` → DD/MM/YYYY |
| `document_details.document_type` | `INV` |
| `seller_details.*` | biller company fields |
| `buyer_details.gstin` | customer `gstn_no` |
| `buyer_details.place_of_supply` | customer `state_code` or `sales.place_of_supply` |
| `value_details.total_cgst_value` | `sales.cgst` |
| `value_details.total_sgst_value` | `sales.sgst` |
| `value_details.total_igst_value` | `sales.igst` |
| `value_details.total_invoice_value` | `sales.grand_total + sales.rounding` |
| `item_list[].hsn_code` | `sale_items.hsn_code` |
| `item_list[].quantity` | `sale_items.unit_quantity` |
| `item_list[].gst_rate` | `sale_items.gst_rate` |
| `item_list[].cgst/sgst/igst_amount` | from `sales_items_tax` per item |
| `transaction_details.supply_type` | `B2B` |

### API response → DB

| API field | `sma_sales` column |
|-----------|-------------------|
| `Irn` | `irn` |
| `AckNo` | `ack_no` |
| `AckDt` | `ack_dt` |
| `SignedQRCode` | `signed_qr_code` |
| `SignedInvoice` | `signed_invoice` |
| `EinvoicePdf` | `einvoice_pdf_url` |
| `requestId` | `einvoice_request_id` |
| `EwbNo` | `ewb_no` |
| `errorMessage` | `einvoice_error` |

**Rule:** Use existing Sma totals — do NOT recalculate tax for API.

---

## 15. E-Way Bill strategy

| Option | API | ElintOM use |
|--------|-----|-------------|
| A. Inline with IRN | `ewaybill_details` in `/einvoice/` | Optional — if transport known at billing |
| **B. By IRN** ★ | `/gen-ewb-by-irn/` | Phase 3 — button on D3 after IRN |
| C. Standalone | `/ewayBillsGenerate/` | Phase 3 — C3 challan view |

**Transport source priority:**
1. POST from D8 Add Delivery form
2. `sma_sales.ewb_vehicle_no`, `ewb_transporter_id`, `LR_No`, `transporter_mode`
3. `sma_delivery_challan` fields if converted from challan

---

## 16. Error handling & security

### Error handling

| Scenario | Function | User sees | DB |
|----------|----------|-----------|-----|
| NIC validation error | `generate_irn()` | 🔴 Failed + message | `einvoice_status=failed`, `einvoice_error` |
| Timeout | `generate_irn()` | 🟡 Pending + Retry | `einvoice_status=pending` |
| Duplicate doc | `generate_irn()` | Auto recover via `get_irn_by_doc()` | `einvoice_status=success` |
| Cancel failed | `cancel_irn()` | Flash error | log only |
| IRN fail on auto save | `maybe_auto_irn()` | Sale saved; status failed | **no rollback** |

**Response envelope:** check `results.status !== Success` even on HTTP 200.

### Security

- API key in settings/config — never in git or JS
- `Einvoice` controller — admin permission only
- Full request/response in `sma_einvoice_log` — admin only
- CSRF on all POST forms

---

## 17. Phased rollout

| Phase | Weeks | Screens | Functions to build | DB |
|-------|-------|---------|-------------------|-----|
| **1** | 1–2 | D3, S1 | `Mastersindia`, `Einvoice_service`, `Einvoice_model`, `Einvoice`, `Sales::view()` | `sma_sales` cols + `sma_einvoice_log` |
| **2** | 3 | D1, D4, D5, D6 | `getSales()`, print views | — |
| **3** | 4 | D8, D3 EWB, C3 | `generate_ewb_*`, `save_transport`, challan EWB | `sma_delivery_challan` ewb cols |
| **4** | 5 | S1, D2, D7, R1–R3 | `addSale()` hook, `return_sale()` cancel, settings UI | `sma_settings` cols |
| **5** | future | P1, P2 | `Pos_model::addSale()` hook, bulk APIs | — |

### Phase 1 exit criteria

One sandbox B2B sale → manual Generate IRN → IRN + QR saved on D3.

### Phase 1 first coding steps

1. Run `upgrade/sql/einvoice_alter_sales.sql`
2. Create `mastersindia.php` + `Mastersindia.php`
3. Create `Einvoice_service.php` + `Einvoice_model.php`
4. Create `Einvoice::generate_irn()` + button on D3
5. **Do NOT** hook `addSale()` until sandbox validated

---

## 18. Testing plan

| # | Test | Expected |
|---|------|----------|
| 1 | Sandbox B2B → Generate IRN | IRN saved in `sma_sales.irn` |
| 2 | Missing customer GSTIN | Error; sale still saved |
| 3 | Invalid pincode | NIC error in `einvoice_error`; Retry works |
| 4 | Duplicate invoice no | Recover via `get-einvoice-bydoc` |
| 5 | Return sale | Original `einvoice_status=cancelled` |
| 6 | EWB by IRN | `ewb_no` saved; shows on print |
| 7 | A4 print | IRN + QR scannable |
| 8 | POS disabled | No IRN attempt |
| 9 | PHP 8.5 | No fatal in new library code |

**Test script (Phase 4):** `upgrade/phase4_einvoice_test.php`

---

## 19. Open questions

1. Sandbox API host — `sandb-api.mastersindia.co` vs `sandb-api.edoc.mastersindia.co`?
2. Auto IRN on all B2B saves vs button-only initially?
3. POS B2B — IRN needed at billing time?
4. Credit notes — cancel IRN only or generate CRN document?
5. Challan → IRN on challan or only on final invoice?
6. Multi-biller — one API key per tenant or per GSTIN?
7. Masters India sandbox credentials ready?

---

## 20. Related existing files

| Area | Path |
|------|------|
| Sales controller | `app/controllers/Sales.php` |
| POS controller | `app/controllers/Pos.php` |
| Sales model | `app/models/Sales_model.php` — `addSale()` ~L453, `getInvoiceByID()` ~L286 |
| POS model | `app/models/Pos_model.php` — `addSale()` ~L514 |
| Tax library | `app/libraries/Sma.php` — `taxAtrrClassification()`, `qrcode()` |
| GST check | `app/models/Site.php` — `isGstSale()` ~L2511 |
| A4 invoice | `themes/default/views/sales/view_a4_new_format.php` |
| PDF | `themes/default/views/sales/pdf_reciept.php` |
| Sale view | `themes/default/views/sales/view.php` |
| Payment config pattern | `app/config/payment_gateways.php` |
| curl API pattern | `app/models/Urban_piper_model.php` |
| DB schema backup | `files/localhost/backups/db-backup-*.txt` |

---

## Changelog

| Date | Change |
|------|--------|
| 2026-07-12 | Masters India API docs downloaded to `docs/mastersindia/` |
| 2026-07-12 | Merged IMPLEMENTATION + SCREEN_FLOW + TECHNICAL into this master plan |
