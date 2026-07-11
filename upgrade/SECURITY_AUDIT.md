# Security Audit — `phpupgrade` (Initial Scan)

**Date:** 2026-07-07 | **PHP:** 8.5 | **Scanner:** Agent grep (app code, excl. vendor noise)

Rules: `.cursor/rules/php-security-*.mdc` — run before every dev task; PM uses checklist in `php-security-gate.mdc`.

---

## Summary

| Severity | Count | Status |
|----------|-------|--------|
| P0 | 2 | Report — fix requires PM approval (not during blind upgrade) |
| P1 | 5 | Report — track per module |
| P2 | 4 | Config / dev settings |
| P3 | — | Legacy patterns (unserialize DB blobs) |

---

## P0 — Critical (PM + dev fix plan)

| # | Issue | File | Notes |
|---|-------|------|-------|
| 1 | **Shell command injection risk** | `app/controllers/Screens.php:113` | `exec("mysql ... $username $password $path")` — user-controlled path/creds |
| 2 | **`extract($_POST)`** | `app/controllers/Webshop.php:2351` | Variable overwrite / injection risk |

---

## P1 — High (fix before production)

| # | Issue | File | Notes |
|---|-------|------|-------|
| 1 | **Hardcoded Google OAuth secret** | `app/controllers/Shop.php` | Client ID + secret in controller (lines ~2947–2949, 4126–4128) |
| 2 | **Hardcoded test passwords** | `app/controllers/Shop.php` | `test@123`, md5 passwords in OAuth flow |
| 3 | **SSL verify disabled** | `app/libraries/Sma.php:6215` | `CURLOPT_SSL_VERIFYPEER => false` |
| 4 | **SSL verify disabled** | `app/helpers/cheerio_whatsapp_helper.php` | Lines 382, 411 |
| 5 | **SSL verify disabled** | `app/libraries/Paynearepay.php:114` | Payment gateway curl |

---

## P2 — Medium (production config)

| # | Issue | File | Notes |
|---|-------|------|-------|
| 1 | **`db_debug` TRUE** | `app/config/database.php` | SQL errors visible — set FALSE for prod |
| 2 | **Large CSRF exclude list** | `app/config/config.php` | Many API/webhook URIs — review each |
| 3 | **Weak password hash** | Webshop `md5()` passwords | `Shop.php` — legacy; migrate with PM approval |
| 4 | **Hardcoded encryption_key** | `app/config/config.php` | In repo — rotate per environment |

---

## P3 — Track (legacy, DB-stored data)

| Pattern | Files | Notes |
|---------|-------|-------|
| `unserialize()` on DB/config | `Sma.php`, `Pos.php`, `Shop.php`, models | Not user input directly — object injection if DB compromised |
| `Apicrypter` fixed key | `app/libraries/Apicrypter.php` | Legacy — do not rotate without migration plan |

---

## PM Review Checklist (from rules)

| # | Check | Pass |
|---|-------|------|
| 1 | No new secrets committed | ☐ |
| 2 | SQL uses Query Builder | ☐ |
| 3 | XSS output escaped | ☐ |
| 4 | CSRF on new forms | ☐ |
| 5 | Auth on admin actions | ☐ |
| 6 | Upload validation intact | ☐ |
| 7 | Payment callbacks verified | ☐ |
| 8 | No new eval/unserialize on user input | ☐ |
| 9 | TLS verify on new curl code | ☐ |
| 10 | Prod db_debug off, errors hidden | ☐ |

---

## Next Actions (recommended order)

1. **PM approves** which P0/P1 items to fix now vs post-upgrade
2. Move OAuth secrets to `google_config.php` / env (Shop.php)
3. Replace `extract($_POST)` in Webshop.php with explicit assignments
4. Audit `Screens.php` exec — remove or harden with escapeshellarg
5. Enable `CURLOPT_SSL_VERIFYPEER` where certs allow
6. Production: `db_debug` FALSE, `ENVIRONMENT` production

*Re-run scan after fixes: grep patterns in `php-security-code.mdc`*
