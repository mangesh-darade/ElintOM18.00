> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-e-way-bill-in-bulk.md).

# Get E-Way Bill in Bulk

### Request Method

GET

### Request Path

```
{{API_URL}}/api/v1/getBulkEwayBillsResponse/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Params

| Parameter   | Value                          | Description                              |
| ----------- | ------------------------------ | ---------------------------------------- |
| request\_id | edoc\_1694792305\_dc85e3f62632 | Request id given in generate    bulk IRN |
|             |                                |                                          |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": [
    {
      "message": {
        "ewayBillNo": 331009218872,
        "ewayBillDate": "15/09/2023 09:05:00 PM",
        "validUpto": "16/09/2023 11:59:00 PM",
        "alert": "",
        "error": false,
        "url": "sandb-api.mastersindia.co/api/v1/detailPrintPdf/anVsX3NlcF8yMDIzLTI0-65047a724b0fa9b8fe92d11d/"
      },
      "status": "Success",
      "code": 200,
      "requestId": "ERP1694792306_lkkkkk-1021",
      "docNo": "lkkkkk-1021"
    },
    {
      "message": {
        "ewayBillNo": 301009218873,
        "ewayBillDate": "15/09/2023 09:05:00 PM",
        "validUpto": "16/09/2023 11:59:00 PM",
        "alert": "",
        "error": false,
        "url": "sandb-api.mastersindia.co/api/v1/detailPrintPdf/anVsX3NlcF8yMDIzLTI0-65047a734b0fa9b8fe92d11f/"
      },
      "status": "Success",
      "code": 200,
      "requestId": "ERP1694792307_lkkkkk-1033",
      "docNo": "lkkkkk-1033"
    }
  ]
}
```

#### 204 (Invalid Parameters)

```json
{
  "results": {
    "status": "No Content",
    "code": 204,
    "request_status": "",
    "requestId": "edoc_1694792305_dc85e3f62631",
    "message": "No records found."
  }
}
```
