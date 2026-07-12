> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/update-transporter.md).

# Update Transporter

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/transporterIdUpdate/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/update_transporter_id_api_v_1_transporterIdUpdate__post).

#### Sample Request

```json
{
    "userGstin": "XXXXXXXXXXXXXXX",
    "eway_bill_number": 321009218808,
    "transporter_id": "XXXXXXXXXXXXXXX",
    "transporter_name": "MS Uttarayan"
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/update_transporter_id_api_v_1_transporterIdUpdate__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "ewayBillNo": "321009218808",
      "transporterId": "XXXXXXXXXXXXXXX",
      "transUpdateDate": "15/09/2023 03:05:00 PM",
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
    "message": "222: Invalid Transporter Id",
    "status": "No Content",
    "code": 204,
    "nic_code": "222"
  }
}
```
