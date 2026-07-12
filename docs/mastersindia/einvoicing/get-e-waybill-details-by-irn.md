> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/einvoicing/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/einvoicing/get-e-waybill-details-by-irn.md).

# Get e-Waybill Details by IRN

### Request Method

GET

### Request Path

```
{{API_URL}}/api/v1/get-ewb-byirn/
```

### Request Headers

<table><thead><tr><th>Parameter</th><th width="184">Value</th><th>Description</th></tr></thead><tbody><tr><td>Authorization/api_key</td><td>api_key/auth token</td><td>Value and header type is mentioned in the Authentication Page</td></tr></tbody></table>

### Request Params

| Parameter   | Value                                                            | Description                                                                  |
| ----------- | ---------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| user\_gstin | XXXXXXXXXXXXXXX                                                  | GSTIN of the authenticated user                                              |
| irn         | dd609070d4f3f66f1585fc13abd52e8f47d4102c8389bb9f7411afe52a9e778a | SHA256 hash of Gstin, DocDtls.No, DocDtls.Typ, financial year of DoctDtls.Dt |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": {
      "EwbNo": 401008880911,
      "Status": "ACT",
      "GenGstin": "XXXXXXXXXXXXXXX",
      "EwbDt": "2023-09-14 13:36:00",
      "EwbValidTill": "2023-09-16 23:59:00",
      "Alert": ""
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
    "errorMessage": "4005: Eway Bill details are not found",
    "InfoDtls": "",
    "status": "Failed",
    "code": 204
  }
}
```
