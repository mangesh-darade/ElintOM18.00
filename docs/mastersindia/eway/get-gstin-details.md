> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-gstin-details.md).

# Get GSTIN Details

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

| Parameter | Value           | Description    |
| --------- | --------------- | -------------- |
| action    | GetGSTINDetails | Action         |
| userGstin | XXXXXXXXXXXXXXX | Gstin          |
| gstin     | XXXXXXXXXXXXXXX | Taxpayer gstin |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": {
      "gstin_of_taxpayer": "XXXXXXXXXXXXXXX",
      "trade_name": "M/S UTTARAYAN CO-OPERATIVE FOR RENEWABLE ENERGY",
      "legal_name_of_business": "UTTARAYAN CO-OPERATIVE FOR RENEWABLE ENERGY",
      "address1": "UCREPeelikothi , Near the nainital bank ltd,Kaladhungi Road",
      "address2": "263139Peelikothi",
      "state_name": "UTTARAKHAND",
      "pincode": "263139",
      "taxpayer_type": "REG",
      "status": "ACT",
      "block_status": ""
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
    "message": "325: Could not retrieve data",
    "status": "No Content",
    "code": 204,
    "nic_code": "325"
  }
}
```
