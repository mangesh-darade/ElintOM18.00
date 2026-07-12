> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/einvoicing/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/einvoicing/generate-e-way-bill-by-irn.md).

# Generate E-Way Bill by IRN

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/gen-ewb-by-irn/
```

### Request Headers

<table><thead><tr><th>Parameter</th><th width="184">Value</th><th>Description</th></tr></thead><tbody><tr><td>Authorization/api_key</td><td>api_key/auth token</td><td>Value and header type is mentioned in the Authentication Page</td></tr></tbody></table>

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Einvoice/operation/generate_eway_bill_by_irn_api_v_1_gen_ewb_by_irn__post).

#### Sample Request

```json
{
    "user_gstin": "XXXXXXXXXXXXXXX",
    "irn": "dd609070d4f3f66f1585fc13abd52e8f47d4102c8389bb9f7411afe52a9e778a",
    "transporter_id": "XXXXXXXXXXXXXXX",
    "transportation_mode": "1",
    "transporter_document_number": "12345",
    "transporter_document_date": "14/09/2023",
    "vehicle_number": "KA01AB1234",
    "distance": 296,
    "vehicle_type": "R",
    "transporter_name": "Jay Trans",
    "data_source": "erp",
    "dispatch_details": {
        "company_name": "dqfefkewl",
        "address1": "Vila",
        "address2": "Vila",
        "location": "Noida",
        "pincode": 201301,
        "state_code": "09"
    },
    "ship_details": {
        "address1": "PILA 1",
        "address2": "PILA 1",
        "location": "Nainital",
        "pincode": 248001,
        "state_code": "UTTARAKHAND"
    }
}
```

### Response Body

#### 200 (OK)

The response description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Einvoice/operation/generate_eway_bill_by_irn_api_v_1_gen_ewb_by_irn__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "EwbNo": 401008880911,
      "EwbDt": "2023-09-14 13:36:00",
      "EwbValidTill": "2023-09-16 23:59:00",
      "Remarks": "",
      "QRCodeUrl": "https://sandb-api.mastersindia.co/api/v1/einvoice/qrcode/anVsX3NlcF8yMDIzLTI0-6502bfc0b5f34bd57e404e75/",
      "EinvoicePdf": "https://sandb-api.mastersindia.co/api/v1/einvoice/pdf/anVsX3NlcF8yMDIzLTI0-6502bfc0b5f34bd57e404e75/",
      "EwaybillPdf": "https://sandb-api.mastersindia.co/api/v1/detailPrintPdf/anVsX3NlcF8yMDIzLTI0-6502bfe6b5f34bd57e404e77/"
    },
    "errorMessage": "",
    "InfoDtls": "",
    "status": "Success",
    "code": 200
  }
}
```

#### 204 (Invalid Parameters)

```json
{
  "results": {
    "message": "",
    "errorMessage": "2302: Status of the IRN is not active",
    "InfoDtls": "",
    "status": "Failed",
    "code": 204
  }
}
```
