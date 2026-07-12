> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/einvoicing/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/einvoicing/cancel-irn.md).

# Cancel IRN

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/cancel-einvoice/
```

### Request Headers

<table><thead><tr><th width="259">Parameter</th><th width="232">Value</th><th>Description</th></tr></thead><tbody><tr><td>Authorization/api_key</td><td>api_key/auth token</td><td>Value and header type is mentioned in the Authentication Page</td></tr></tbody></table>

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Einvoice/operation/cancel_irn_api_v_1_cancel_einvoice__post).

#### Sample Request

```json
{
    "user_gstin": "XXXXXXXXXXXXXXX",
    "irn": "ac58d64d47b8816e444deb35c27477dea18d630a34e3c2f59a2aa0e86c70629a",
    "cancel_reason": "1",
    "cancel_remarks": "Wrong entry",
    "ewaybill_cancel": ""
}
```

### Response Body

#### 200 (OK)

The response description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Einvoice/operation/cancel_irn_api_v_1_cancel_einvoice__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "Irn": "ac58d64d47b8816e444deb35c27477dea18d630a34e3c2f59a2aa0e86c70629e",
      "CancelDate": "2023-09-14 13:29:00"
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
    "errorMessage": "2143: Invoice does not belongs to the user GSTIN",
    "InfoDtls": "",
    "status": "Failed",
    "code": 204
  }
}
```
