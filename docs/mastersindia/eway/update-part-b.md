> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/update-part-b.md).

# Update Part-B

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/updateVehicleNumber/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/update_part_b_api_v_1_updateVehicleNumber__post).

#### Sample Request

```json
{
"userGstin":"XXXXXXXXXXXXXXX",
"eway_bill_number": 321009218808,
"vehicle_number": "JJK8977",
"vehicle_type":"r",
"place_of_consignor": "Noida",
"state_of_consignor": "Uttar Pradesh",
"reason_code_for_vehicle_updation": "due to break down",
"reason_for_vehicle_updation": "",
"transporter_document_number": "14456",
"transporter_document_date": "25/06/2023",
"mode_of_transport": 1,
"data_source":""
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/update_part_b_api_v_1_updateVehicleNumber__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "vehUpdDate": "15/09/2023 02:45:00 PM",
      "validUpto": "24/09/2023 11:59:00 PM",
      "error": false,
      "url": "https://sandb-api.mastersindia.co/api/v1/detailPrintPdf/anVsX3NlcF8yMDIzLTI0-65042126bc6d8306741fdcf8/"
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
    "message": "362: Transporter document date cannot be earlier than the invoice date",
    "status": "No Content",
    "code": 204,
    "nic_code": "362"
  }
}
```
