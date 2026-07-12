> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-hsn-details.md).

# GET HSN Details

### Request Method

GET

### Request Path

```
{{API_URL}}/api/v1/getEwayBillData/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Params

| Parameter | Value                  | Description |
| --------- | ---------------------- | ----------- |
| action    | GetHsnDetailsByHsnCode | Action      |
| userGstin | XXXXXXXXXXXXXXX        | Gstin       |
| hsncode   | 1001                   | HSN Code    |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": {
      "hsn_code": "1001",
      "hsn_description": "WHEAT AND MESLIN - Durum wheat :"
    },
    "status": "Success",
    "code": 200
  }
}
```

#### 204 (Invalid Parameters)

```json
{
  "results": {
    "message": "216: Invalid HSN Code",
    "status": "No Content",
    "code": 204,
    "nic_code": "216"
  }
}
```
