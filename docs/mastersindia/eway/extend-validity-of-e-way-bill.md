> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/extend-validity-of-e-way-bill.md).

# Extend Validity of E-Way Bill

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/ewayBillValidityExtend/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/extend_eway_bill_api_v_1_ewayBillValidityExtend__post).

#### Sample Request

```json
{
    "userGstin": "XXXXXXXXXXXXXXX",
    "eway_bill_number": 331009219156,
    "vehicle_number": "KA12TR1234",
    "place_of_consignor": "Dehradun",
    "state_of_consignor": "UTTARAKHAND",
    "remaining_distance": 20,
    "transporter_document_number": "123",
    "transporter_document_date": "25/06/2023",
    "mode_of_transport": "5",
    "extend_validity_reason": "Natural Calamity",
    "extend_remarks": "Flood",
    "consignment_status": "T",
    "from_pincode": 248001,
    "transit_type": "W",
    "address_line1": "HUBLI",
    "address_line2": "HUBLI",
    "address_line3": "HUBLI"
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/extend_eway_bill_api_v_1_ewayBillValidityExtend__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "ewayBillNo": "331009219156",
      "updatedDate": "20/09/2023 07:07:00 PM",
      "validUpto": "21/09/2023 11:59:00 PM",
      "error": false,
      "url": "https://sandb-api.mastersindia.co/api/v1/detailPrintPdf/anVsX3NlcF8yMDIzLTI0-650af67d1b6b83a4653b2870/"
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
    "message": "301: Invalid eway bill number",
    "status": "No Content",
    "code": 204,
    "nic_code": "301"
  }
}
```
