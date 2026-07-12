> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/return-status-blocked-and-unblocked.md).

# Return Status (Blocked and Unblocked)

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/returnStatus/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/return_status_api_v_1_returnStatus__post).

#### Sample Request

```json
{
    "user_gstin": "XXXXXXXXXXXXXXX",
    "GSTIN_Status":"N",
    "gstin_list": [
        {
            "gstin": "XXXXXXXXXXXXXXX"
        },
        {
            "gstin": "XXXXXXXXXXXXXXX"
        }
    ]
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/return_status_api_v_1_returnStatus__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "XXXXXXXXXXXXXXX": "Unblocked",
      "XXXXXXXXXXXXXXX": "Unblocked"
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
    "message": {
      "XXXXXXXXXXXXXXX": "Incorrect user id/User does not exists",
      "XXXXXXXXXXXXXXX": "Incorrect user id/User does not exists"
    },
    "status": "Fail",
    "code": 204
  }
}
```
