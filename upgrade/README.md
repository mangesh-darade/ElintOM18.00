# PHP Upgrade — Dev & Test Files

Non-application files for the PHP 8.5 upgrade (tests, plan, fixes log, audits).

**App entry point:** `../index.php` — **Target codebase:** `ElintOM18.00/`

## Upgrade rule (mandatory)

- **Syntax only** — PHP 8.5 compatibility (casts, guards, `isset`, deprecations)
- **No code delete** — ElintOM18.00 मधला कोणताही logic/line काढू नका
- **No variable rename** — names जसे आहेत तसेच ठेवा
- **Patch in place** — line-by-line fix; **इतर folder/repo वरूn entire file copy करू नका**

Tests run against: `http://localhost/ElintOM18.00` (see `PHASE4_BASE_URL` in `phase4_test_lib.php`).

## Run tests

```bash
cd c:\wamp64\www\ElintOM18.00\upgrade
c:\wamp64\bin\php\php8.5.0\php.exe phase4_auth_links_test.php Admin "Admin@554"
```

## Contents

| Type | Files |
|------|--------|
| Plan & log | `PHP_UPGRADE_PLAN.md`, `PHP_UPGRADE_FIXES_LOG.md` |
| Tests | `phase3_smoke_test.php`, `baseline_test.php`, `phase4_*.php` |
| Other | `SECURITY_AUDIT.md`, `MODULE_STATUS.txt` |
