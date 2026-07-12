> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/reject-e-way-bill.md).

# Reject E-Way Bill

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/ewayBillReject/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/reject_eway_bill_api_v_1_ewayBillReject__post).

#### Sample Request

```json
{
    "userGstin":"XXXXXXXXXXXXXXX",
    "eway_bill_number": 371009218816,
    "data_source":""
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/reject_eway_bill_api_v_1_ewayBillReject__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "ewayBillNo": "371009218816",
      "ewbRejectedDate": "15/09/2023 03:24:00 PM",
      "error": false
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
    "message": "344: Invalid eway bill number",
    "status": "No Content",
    "code": 204,
    "nic_code": "344"
  }
}
```
