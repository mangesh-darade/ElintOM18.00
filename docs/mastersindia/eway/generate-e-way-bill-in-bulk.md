> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/generate-e-way-bill-in-bulk.md).

# Generate E-Way Bill In Bulk

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/ewayBillsGenerateInBulk/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/generate_ewaybill_in_bulk_api_v_1_ewayBillsGenerateInBulk__post).

#### Sample Request

```json
{
    "ewayBillList": [
        {
            "userGstin": "XXXXXXXXXXXXXXX",
            "supply_type": "outward",
            "sub_supply_type": "Others",
            "sub_supply_description": "sales from other country",
            "document_type": "tax invoice",
            "document_number": "lkkkkk-1021",
            "document_date": "10/05/2018",
            "gstin_of_consignor": "XXXXXXXXXXXXXXX",
            "legal_name_of_consignor": "welton",
            "address1_of_consignor": "2ND CROSS NO 59 19 A",
            "address2_of_consignor": "GROUND FLOOR OSBORNE ROAD",
            "place_of_consignor": "Dehradun",
            "pincode_of_consignor": 248001,
            "state_of_consignor": "UTTARAKHAND",
            "actual_from_state_name": "UTTARAKHAND",
            "gstin_of_consignee": "XXXXXXXXXXXXXXX",
            "legal_name_of_consignee": "sthuthya",
            "address1_of_consignee": "Shree Nilaya",
            "address2_of_consignee": "Dasarahosahalli",
            "place_of_consignee": "Beml Nagar",
            "pincode_of_consignee": 248001,
            "state_of_supply": "UTTARAKHAND",
            "actual_to_state_name": "UTTARAKHAND",
            "total_invoice_value": 6002581.23,
            "taxable_amount": 5609889,
            "cgst_amount": 84148.335,
            "sgst_amount": 84148.335,
            "igst_amount": 0,
            "cess_amount": 224395.56,
            "transporter_id": "XXXXXXXXXXXXXXX",
            "transporter_name": "",
            "transporter_document_number": "",
            "transporter_document_date": "",
            "transportation_mode": "road",
            "transportation_distance": "100",
            "vehicle_number": "PVC1234",
            "vehicle_type": "Regular",
            "generate_status": 1,
            "data_source": "erp",
            "user_ref": "1232435466sdsf234",
            "location_code": "XYZ",
            "eway_bill_status": "ABC",
            "auto_print": "Y",
            "itemList": [
                {
                    "product_name": "Wheat",
                    "product_description": "Wheat",
                    "hsn_code": 1001,
                    "quantity": 4,
                    "unit_of_product": "BOX",
                    "cgst_rate": 1.5,
                    "sgst_rate": 1.5,
                    "igst_rate": 0,
                    "cess_rate": 0,
                    "cessAdvol": 0,
                    "taxable_amount": 5609889
                },
                {
                    "product_name": "Wheat",
                    "product_description": "Wheat",
                    "hsn_code": 1001,
                    "quantity": 4,
                    "unit_of_product": "BOX",
                    "cgst_rate": 1.5,
                    "sgst_rate": 1.5,
                    "igst_rate": 0,
                    "cess_rate": 0,
                    "cessAdvol": 0,
                    "taxable_amount": 5609889
                }
            ]
        },
        {
            "userGstin": "XXXXXXXXXXXXXXX",
            "supply_type": "outward",
            "sub_supply_type": "Others",
            "sub_supply_description": "sales from other country",
            "document_type": "tax invoice",
            "document_number": "lkkkkk-1033",
            "document_date": "10/05/2018",
            "gstin_of_consignor": "XXXXXXXXXXXXXXX",
            "legal_name_of_consignor": "welton",
            "address1_of_consignor": "2ND CROSS NO 59 19 A",
            "address2_of_consignor": "GROUND FLOOR OSBORNE ROAD",
            "place_of_consignor": "Dehradun",
            "pincode_of_consignor": 248001,
            "state_of_consignor": "UTTARAKHAND",
            "actual_from_state_name": "UTTARAKHAND",
            "gstin_of_consignee": "XXXXXXXXXXXXXXX",
            "legal_name_of_consignee": "sthuthya",
            "address1_of_consignee": "Shree Nilaya",
            "address2_of_consignee": "Dasarahosahalli",
            "place_of_consignee": "Beml Nagar",
            "pincode_of_consignee": 248001,
            "state_of_supply": "UTTARAKHAND",
            "actual_to_state_name": "UTTARAKHAND",
            "total_invoice_value": 6002581.23,
            "taxable_amount": 5609889,
            "cgst_amount": 84148.335,
            "sgst_amount": 84148.335,
            "igst_amount": 0,
            "cess_amount": 224395.56,
            "transporter_id": "",
            "transporter_name": "",
            "transporter_document_number": "",
            "transporter_document_date": "",
            "transportation_mode": "road",
            "transportation_distance": "100",
            "vehicle_number": "PVC1234",
            "vehicle_type": "Regular",
            "generate_status": 1,
            "data_source": "erp",
            "user_ref": "1232435466sdsf234",
            "location_code": "XYZ",
            "eway_bill_status": "ABC",
            "auto_print": "Y",
            "itemList": [
                {
                    "product_name": "Wheat",
                    "product_description": "Wheat",
                    "hsn_code": 1001,
                    "quantity": 4,
                    "unit_of_product": "BOX",
                    "cgst_rate": 1.5,
                    "sgst_rate": 1.5,
                    "igst_rate": 0,
                    "cess_rate": 0,
                    "cessAdvol": 0,
                    "taxable_amount": 5609889
                },
                {
                    "product_name": "Wheat",
                    "product_description": "Wheat",
                    "hsn_code": 1001,
                    "quantity": 4,
                    "unit_of_product": "BOX",
                    "cgst_rate": 1.5,
                    "sgst_rate": 1.5,
                    "igst_rate": 0,
                    "cess_rate": 0,
                    "cessAdvol": 0,
                    "taxable_amount": 5609889
                }
            ]
        }
    ]
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/generate_ewaybill_in_bulk_api_v_1_ewayBillsGenerateInBulk__post).

#### Sample Response

```json
{
  "results": {
    "status": "Success",
    "message": "Request has been accepted.Please get response by the request id",
    "code": 200,
    "request_status": "pending",
    "requestId": "edoc_1694792305_dc85e3f62632"
  }
}
```

#### 204 (Invalid Parameters)

```json
{
  "error": "invalid_request",
  "error_description": "Invalid Json Structure."
}
```
