# PHP Upgrade — Dev & Test Files

Non-application files for the PHP 8.5 upgrade (tests, plan, fixes log, audits).

**App entry point stays at project root:** `../index.php`

## Run tests

```bash
cd c:\wamp64\www\phpupgrade\upgrade
c:\wamp64\bin\php\php8.5.0\php.exe phase4_auth_links_test.php Admin "Admin@554"
c:\wamp64\bin\php\php8.5.0\php.exe phase4_auto_links_test.php Admin "Admin@554"
```

## Contents

| Type | Files |
|------|--------|
| Plan & log | `PHP_UPGRADE_PLAN.md`, `PHP_UPGRADE_FIXES_LOG.md` |
| Tests | `phase3_smoke_test.php`, `baseline_test.php`, `phase4_*.php` |
| Other | `SECURITY_AUDIT.md`, `MODULE_STATUS.txt` |
