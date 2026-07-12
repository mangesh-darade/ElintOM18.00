# Masters India — E-Invoice & E-Way Bill

**All plans in one file:** [`EINVOICE_EWB_MASTER_PLAN.md`](EINVOICE_EWB_MASTER_PLAN.md)

Screens · flows · functions · logic · DB tables/columns · API · phases.

---

## API docs (local copy)

**Source:** https://docs.mastersindia.co/  
**Downloaded:** 2026-07-12

| Folder | Content |
|--------|---------|
| `einvoicing/` | 16 pages — Generate IRN, Cancel, EWB by IRN, GSTIN lookup |
| `eway/` | 39 pages — Generate/Cancel EWB, Part-B, bulk, transporter |

**Key files:** `einvoicing/generate-irn.md` · `eway/generate-e-way-bill.md`

### Quick API reference

| Operation | Method | Path |
|-----------|--------|------|
| Generate IRN | POST | `/api/v1/einvoice/` |
| Cancel IRN | POST | `/api/v1/cancel-einvoice/` |
| EWB by IRN | POST | `/api/v1/gen-ewb-by-irn/` |
| Generate EWB (standalone) | POST | `/api/v1/ewayBillsGenerate/` |

| Env | API base |
|-----|----------|
| Sandbox | https://sandb-api.mastersindia.co |
| Production | https://router.mastersindia.co |

Auth: header `api_key: <key>` or `Authorization: JWT <token>`

---

## Re-download API docs (PowerShell)

```powershell
$base = "c:\wamp64\www\ElintOM18.00\docs\mastersindia"
foreach ($dir in @('einvoicing','eway')) {
  $index = Join-Path (Join-Path $base $dir) 'llms.txt'
  curl.exe -sL -o $index "https://docs.mastersindia.co/$dir/llms.txt"
  Select-String -Path $index -Pattern '\((https://[^)]+)\)' -AllMatches |
    ForEach-Object { $_.Matches } |
    ForEach-Object { $_.Groups[1].Value } |
    Sort-Object -Unique |
    ForEach-Object {
      $name = Split-Path $_ -Leaf
      $out = Join-Path (Join-Path $base $dir) $name
      curl.exe -sL -o $out $_
    }
}
```
