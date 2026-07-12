> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/generate-e-way-bill.md).

# Generate E-Way Bill

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/ewayBillsGenerate/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/generate_eway_bill_api_v_1_ewayBillsGenerate__post).

#### Sample Request

```json
{
    "userGstin": "XXXXXXXXXXXXXXX",
    "supply_type": "outward",
    "sub_supply_type": "Supply",
    "sub_supply_description": "",
    "document_type": "Tax Invoice",
    "document_number": "IMP/23/032",
    "document_date": "22/06/2023",
    "gstin_of_consignor": "XXXXXXXXXXXXXXX",
    "legal_name_of_consignor": "Welton",
    "address1_of_consignor": "The Taj Mahal Palace,Apollo Bandar",
    "address2_of_consignor": "Colaba,Mumbai",
    "place_of_consignor": "Mumbai",
    "pincode_of_consignor": 400001,
    "state_of_consignor": "UTTARAKHAND",
    "actual_from_state_name": "MAHARASHTRA",
    "gstin_of_consignee": "XXXXXXXXXXXXXXX",
    "legal_name_of_consignee": "Sthuthya",
    "address1_of_consignee": "Shree Nilaya",
    "address2_of_consignee": "Dasarahosahalli",
    "place_of_consignee": "Dehradun",
    "pincode_of_consignee": 248001,
    "state_of_supply": "UTTARAKHAND",
    "actual_to_state_name": "UTTARAKHAND",
    "transaction_type": 3,
    "other_value": 0,
    "total_invoice_value": 560,
    "taxable_amount": 500,
    "cgst_amount": 30,
    "sgst_amount": 30,
    "igst_amount": 0,
    "cess_amount": 0,
    "cess_nonadvol_value": 0,
    "transporter_id": "XXXXXXXXXXXXXXX",
    "transporter_name": "",
    "transporter_document_number": "",
    "transporter_document_date": "",
    "transportation_mode": "Road",
    "transportation_distance": "80",
    "vehicle_number": "WXX2121",
    "vehicle_type": "Regular",
    "generate_status": 1,
    "data_source": "erp",
    "user_ref": "",
    "location_code": "",
    "eway_bill_status": "ABC",
    "auto_print": "N",
    "email": "",
    "delete_record": "N",
    "itemList": [
        {
            "product_name": "Wheat",
            "product_description": "Wheat",
            "hsn_code": "1001",
            "quantity": 1,
            "unit_of_product": "BOX",
            "cgst_rate": 6,
            "sgst_rate": 6,
            "igst_rate": 0,
            "cess_rate": 0,
            "cessNonAdvol": 0,
            "taxable_amount": 100
        },
        {
            "product_name": "Wheat",
            "product_description": "Wheat",
            "hsn_code": "1001",
            "quantity": 1,
            "unit_of_product": "BOX",
            "cgst_rate": 6,
            "sgst_rate": 6,
            "igst_rate": 0,
            "cess_rate": 0,
            "cessNonAdvol": 0,
            "taxable_amount": 100
        }
    ]
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/generate_eway_bill_api_v_1_ewayBillsGenerate__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "ewayBillNo": 301009218802,
      "ewayBillDate": "15/09/2023 02:36:00 PM",
      "validUpto": "24/09/2023 11:59:00 PM",
      "alert": "",
      "error": false,
      "url": "sandb-api.mastersindia.co/api/v1/detailPrintPdf/anVsX3NlcF8yMDIzLTI0-65041f35112f30081f9e0b3d/"
    },
    "status": "Success",
    "code": 200,
    "requestId": "ERP1694768948_IMP/23/032"
  }
}
```

#### 204 (Invalid Parameters)

```json
{
  "results": {
    "message": "437: Invalid Supplier ship from State Code for the given pincode.",
    "status": "No Content",
    "code": 204,
    "nic_code": "437"
  }
}
```
