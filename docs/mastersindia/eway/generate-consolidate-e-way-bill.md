> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/generate-consolidate-e-way-bill.md).

# Generate Consolidate E-Way Bill

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/consolidatedEwayBillsGenerate/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/generate_consolidate_eway_bill_api_v_1_consolidatedEwayBillsGenerate__post).

#### Sample Request

```json
{
    "userGstin": "XXXXXXXXXXXXXXX",
    "place_of_consignor": "RAJPURA",
    "state_of_consignor": "PUNJAB",
    "vehicle_number": "JJK8977",
    "mode_of_transport": 2,
    "transporter_document_number": 14456,
    "transporter_document_date": "25/06/2023",
    "data_source": "erp",
    "list_of_eway_bills": [
        {
            "eway_bill_number": "371003223324"
        },
        {
            "eway_bill_number": "341003223325"
        }
    ]
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/generate_consolidate_eway_bill_api_v_1_consolidatedEwayBillsGenerate__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "cEwbNo": "3210007391",
      "cEwbDate": "15/09/2023 03:32:00 PM",
      "error": false,
      "url": "sandb-api.mastersindia.co/api/v1/consolidatePrintPdf/anVsX3NlcF8yMDIzLTI0-65042c58112f30081f9e0b43"
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
