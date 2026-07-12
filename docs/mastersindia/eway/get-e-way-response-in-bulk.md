> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-e-way-response-in-bulk.md).

# Get E-Way Response in Bulk

### Request Method

GET

### Request Path

```
{{API_URL}}/api/v1/bulkEwayApiResponse/
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
        "ewayBillNo": 371009218874,
        "ewayBillDate": "15/09/2023 09:10:00 PM",
        "validUpto": "16/09/2023 11:59:00 PM",
        "alert": "",
        "error": false,
        "url": "sandb-api.mastersindia.co/api/v1/detailPrintPdf/anVsX3NlcF8yMDIzLTI0-65047b8f4b0fa9b8fe92d121/"
      },
      "status": "Success",
      "code": 200,
      "requestId": "ERP1694792591_jjjkhh-1055",
      "docNo": "jjjkhh-1055"
    },
    {
      "message": {
        "ewayBillNo": 341009218875,
        "ewayBillDate": "15/09/2023 09:10:00 PM",
        "validUpto": "16/09/2023 11:59:00 PM",
        "alert": "",
        "error": false,
        "url": "sandb-api.mastersindia.co/api/v1/detailPrintPdf/anVsX3NlcF8yMDIzLTI0-65047b8f4b0fa9b8fe92d123/"
      },
      "status": "Success",
      "code": 200,
      "requestId": "ERP1694792591_abbbbdc-1066",
      "docNo": "abbbbdc-1066"
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
    "request_id": "edoc_1694792590_8200e8dcd6d",
    "message": "No records found."
  }
}
```
