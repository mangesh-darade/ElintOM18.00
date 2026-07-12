> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/einvoicing/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/einvoicing/sync-gstin-details-from-cp.md).

# Sync GSTIN Details from CP

### Request Method

GET

### Request Path

```
{{API_URL}}/api/v1/sync-gstin/
```

### Request Headers

<table><thead><tr><th>Parameter</th><th width="184">Value</th><th>Description</th></tr></thead><tbody><tr><td>Authorization/api_key</td><td>api_key/auth token</td><td>Value and header type is mentioned in the Authentication Page</td></tr></tbody></table>

### Request Params

| Parameter   | Value           | Description                     |
| ----------- | --------------- | ------------------------------- |
| user\_gstin | XXXXXXXXXXXXXXX | GSTIN of the authenticated user |
| gstin       | XXXXXXXXXXXXXXX | Taxpayer gstin                  |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": {
      "Gstin": "XXXXXXXXXXXXXXX",
      "TradeName": null,
      "LegalName": "Masters India Private Limited",
      "AddrBnm": null,
      "AddrBno": null,
      "AddrFlno": null,
      "AddrSt": null,
      "AddrLoc": null,
      "StateCode": 5,
      "AddrPncd": 560009,
      "TxpType": "REG",
      "Status": "ACT",
      "BlkStatus": "U",
      "DtReg": null,
      "DtDReg": null
    },
    "errorMessage": "",
    "InfoDtls": "",
    "status": "Success",
    "code": 200
  }
}
```

#### 204 (Invalid Parameters)

```json
{
  "results": {
    "message": "",
    "errorMessage": "5001: Application Error in Auth, Please Contact the help desk",
    "InfoDtls": "",
    "status": "Failed",
    "code": 204
  }
}
```
