> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/regenerate-consolidate-e-way-bill.md).

# Regenerate Consolidate E-Way Bill

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/consolidatedEwayBillsRegenerate/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/regenerate_consolidate_eway_bill_api_v_1_consolidatedEwayBillsRegenerate__post).

#### Sample Request

```json
{
    "userGstin": "XXXXXXXXXXXXXXX",
    "consolidated_eway_bill_number": 3210007391,
    "place_of_consignor": "Noida",
    "state_of_consignor": "Uttar Pradesh",
    "vehicle_number": "JJK8977",
    "mode_of_transport": 2,
    "transporter_document_number": 14456,
    "transporter_document_date": "15/09/2023",
    "reason_of_cancel": "Data Entry Mistake",
    "reason_for_updation": "Wrong Entry"
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/regenerate_consolidate_eway_bill_api_v_1_consolidatedEwayBillsRegenerate__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "tripSheetNo": 3910007392,
      "tripSheetEwayBills": [
        {
          "ewbNo": 381009218819
        },
        {
          "ewbNo": 371009218816
        }
      ],
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
    "message": "224: Invalid Transaction Date",
    "status": "No Content",
    "code": 204,
    "nic_code": "224"
  }
}
```
