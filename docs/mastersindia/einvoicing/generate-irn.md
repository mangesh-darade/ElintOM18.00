> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/einvoicing/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/einvoicing/generate-irn.md).

# Generate IRN

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/einvoice/
```

### Request Headers

<table><thead><tr><th width="213">Parameter</th><th width="227">Value</th><th>Description</th></tr></thead><tbody><tr><td>Authorization/api_key</td><td>api_key/auth token</td><td>Value and header type is mentioned in the Authentication Page</td></tr></tbody></table>

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Einvoice/operation/generate_irn_api_v_1_einvoice__post).

#### Sample Request

```json
{
    "user_gstin": "XXXXXXXXXXXXXXX",
    "data_source": "erp",
    "transaction_details": {
        "supply_type": "B2B",
        "charge_type": "Y",
        "igst_on_intra": "N",
        "ecommerce_gstin": ""
    },
    "document_details": {
        "document_type": "INV",
        "document_number": "TATA/99025",
        "document_date": "14/09/2023"
    },
    "seller_details": {
        "gstin": "XXXXXXXXXXXXXXX",
        "legal_name": "MastersIndia UP",
        "trade_name": "123",
        "address1": "45",
        "address2": "l45",
        "location": "1279",
        "pincode": 201301,
        "state_code": "09",
        "phone_number": 9876543231,
        "email": ""
    },
    "buyer_details": {
        "gstin": "XXXXXXXXXXXXXXX",
        "legal_name": "ewewewehyriu32y8ru",
        "trade_name": "122",
        "address1": "9",
        "address2": "17843",
        "location": "12321u",
        "pincode": 263001,
        "place_of_supply": "13",
        "state_code": "05",
        "phone_number": "123233",
        "email": ""
    },
    "dispatch_details": {
        "company_name": "dqfefkewl",
        "address1": "Vila",
        "address2": "Vila",
        "location": "Noida",
        "pincode": 201301,
        "state_code": "09"
    },
    "ship_details": {
        "gstin": "XXXXXXXXXXXXXXX",
        "legal_name": "123",
        "trade_name": "232",
        "address1": "1",
        "address2": "",
        "location": "221",
        "pincode": 263001,
        "state_code": "5"
    },
    "export_details": {
        "ship_bill_number": "",
        "ship_bill_date": "",
        "country_code": "IN",
        "foreign_currency": "inr",
        "refund_claim": "N",
        "port_code": "12",
        "export_duty": "90.9"
    },
    "payment_details": {
        "bank_account_number": "Account Details",
        "paid_balance_amount": "100.2",
        "credit_days": 2,
        "credit_transfer": "Credit Transfer",
        "direct_debit": "Direct Debit",
        "branch_or_ifsc": "KKK000180",
        "payment_mode": "CASH",
        "payee_name": "Payee Name",
        "outstanding_amount": "1.9",
        "payment_instruction": "Payment Instruction",
        "payment_term": "Terms of Payment"
    },
    "reference_details": {
        "invoice_remarks": "Invoice Remarks",
        "document_period_details": {
            "invoice_period_start_date": "07/03/2020",
            "invoice_period_end_date": "07/03/2020"
        },
        "preceding_document_details": [
            {
                "reference_of_original_invoice": "CFRT/0006",
                "preceding_invoice_date": "07/03/2020",
                "other_reference": "2334"
            }
        ],
        "contract_details": [
            {
                "receipt_advice_number": "aaa",
                "receipt_advice_date": "07/03/2020",
                "batch_reference_number": "2334",
                "contract_reference_number": "2334",
                "other_reference": "2334",
                "project_reference_number": "2334",
                "vendor_po_reference_number": "233433454545",
                "vendor_po_reference_date": "07/02/2020"
            }
        ]
    },
    "additional_document_details": [
        {
            "supporting_document_url": "asafsd",
            "supporting_document": "india",
            "additional_information": "india"
        }
    ],
    "ewaybill_details": {
        "transporter_id": "XXXXXXXXXXXXXXX",
        "transporter_name": "Jay Trans",
        "transportation_mode": "1",
        "transportation_distance": 296,
        "transporter_document_number": "12301",
        "transporter_document_date": "14/09/2023",
        "vehicle_number": "PQR1234",
        "vehicle_type": "R"
    },
    "value_details": {
        "total_assessable_value": 4,
        "total_cgst_value": "",
        "total_sgst_value": 0,
        "total_igst_value": 0.2,
        "total_cess_value": 0,
        "total_cess_value_of_state": 0,
        "total_discount": 0,
        "total_other_charge": 0,
        "total_invoice_value": 4.2,
        "round_off_amount": 0,
        "total_invoice_value_additional_currency": 0
    },
    "item_list": [
        {
            "item_serial_number": "501",
            "product_description": "Wheat desc",
            "is_service": "N",
            "hsn_code": "1001",
            "bar_code": "1212",
            "quantity": 1,
            "free_quantity": 0,
            "unit": "KGS",
            "unit_price": 4,
            "total_amount": 4,
            "pre_tax_value": 0,
            "discount": 0,
            "other_charge": 0,
            "assessable_value": 4,
            "gst_rate": 5,
            "igst_amount": 0.2,
            "cgst_amount": 0,
            "sgst_amount": 0,
            "cess_rate": 0,
            "cess_amount": 0,
            "cess_nonadvol_amount": 0,
            "state_cess_rate": 0,
            "state_cess_amount": 0,
            "state_cess_nonadvol_amount": 0,
            "total_item_value": 4.2,
            "country_origin": "",
            "order_line_reference": "",
            "product_serial_number": "",
            "batch_details": {
                "name": "aaa",
                "expiry_date": "31/10/2020",
                "warranty_date": "31/10/2020"
            },
            "attribute_details": [
                {
                    "item_attribute_details": "aaa",
                    "item_attribute_value": "147852"
                }
            ]
        }
    ]
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Einvoice/operation/generate_irn_api_v_1_einvoice__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "AckNo": 142310015934386,
      "AckDt": "2023-09-14 13:20:56",
      "Irn": "d812d43cf9a8951e25973390fcd2b2fbaa4786e779a277768422e7302d50fcde",
      "SignedInvoice": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjE1MTNCODIxRUU0NkM3NDlBNjNCODZFMzE4QkY3MTEwOTkyODdEMUYiLCJ4NXQiOiJGUk80SWU1R3gwbW1PNGJqR0w5eEVKa29mUjgiLCJ0eXAiOiJKV1QifQ.eyJkYXRhIjoie1wiQWNrTm9cIjoxNDIzMTAwMTU5MzQzODYsXCJBY2tEdFwiOlwiMjAyMy0wOS0xNCAxMzoyMDo1NlwiLFwiSXJuXCI6XCJkODEyZDQzY2Y5YTg5NTFlMjU5NzMzOTBmY2QyYjJmYmFhNDc4NmU3NzlhMjc3NzY4NDIyZTczMDJkNTBmY2RlXCIsXCJWZXJzaW9uXCI6XCIxLjFcIixcIlRyYW5EdGxzXCI6e1wiVGF4U2NoXCI6XCJHU1RcIixcIlN1cFR5cFwiOlwiQjJCXCIsXCJSZWdSZXZcIjpcIllcIixcIklnc3RPbkludHJhXCI6XCJOXCJ9LFwiRG9jRHRsc1wiOntcIlR5cFwiOlwiSU5WXCIsXCJOb1wiOlwiVEFUQS85OTAyNFwiLFwiRHRcIjpcIjE0LzA5LzIwMjNcIn0sXCJTZWxsZXJEdGxzXCI6e1wiR3N0aW5cIjpcIjA5QUFBUEc3ODg1UjAwMlwiLFwiTGdsTm1cIjpcIk1hc3RlcnNJbmRpYSBVUFwiLFwiVHJkTm1cIjpcIjEyM1wiLFwiQWRkcjFcIjpcIjQ1XCIsXCJBZGRyMlwiOlwibDQ1XCIsXCJMb2NcIjpcIjEyNzlcIixcIlBpblwiOjIwMTMwMSxcIlN0Y2RcIjpcIjA5XCIsXCJQaFwiOlwiOTg3NjU0MzIzMVwifSxcIkJ1eWVyRHRsc1wiOntcIkdzdGluXCI6XCIwNUFBQVBHNzg4NVIwMDJcIixcIkxnbE5tXCI6XCJld2V3ZXdlaHlyaXUzMnk4cnVcIixcIlRyZE5tXCI6XCIxMjJcIixcIlBvc1wiOlwiMTNcIixcIkFkZHIxXCI6XCI5XCIsXCJBZGRyMlwiOlwiMTc4NDNcIixcIkxvY1wiOlwiMTIzMjF1XCIsXCJQaW5cIjoyNjMwMDEsXCJQaFwiOlwiMTIzMjMzXCIsXCJTdGNkXCI6XCIwNVwifSxcIkRpc3BEdGxzXCI6e1wiTm1cIjpcImRxZmVma2V3bFwiLFwiQWRkcjFcIjpcIlZpbGFcIixcIkFkZHIyXCI6XCJWaWxhXCIsXCJMb2NcIjpcIk5vaWRhXCIsXCJQaW5cIjoyMDEzMDEsXCJTdGNkXCI6XCIwOVwifSxcIlNoaXBEdGxzXCI6e1wiR3N0aW5cIjpcIjA1QUFBUEc3ODg1UjAwMlwiLFwiTGdsTm1cIjpcIjEyM1wiLFwiVHJkTm1cIjpcIjIzMlwiLFwiQWRkcjFcIjpcIjFcIixcIkxvY1wiOlwiMjIxXCIsXCJQaW5cIjoyNjMwMDEsXCJTdGNkXCI6XCIwNVwifSxcIkl0ZW1MaXN0XCI6W3tcIkl0ZW1Ob1wiOjAsXCJTbE5vXCI6XCI1MDFcIixcIklzU2VydmNcIjpcIk5cIixcIlByZERlc2NcIjpcIldoZWF0IGRlc2NcIixcIkhzbkNkXCI6XCIxMDAxXCIsXCJCYXJjZGVcIjpcIjEyMTJcIixcIlF0eVwiOjEuMCxcIkZyZWVRdHlcIjowLjAsXCJVbml0XCI6XCJLR1NcIixcIlVuaXRQcmljZVwiOjQuMCxcIlRvdEFtdFwiOjQuMCxcIkRpc2NvdW50XCI6MC4wLFwiUHJlVGF4VmFsXCI6MC4wLFwiQXNzQW10XCI6NC4wLFwiR3N0UnRcIjo1LjAsXCJJZ3N0QW10XCI6MC4yLFwiQ2dzdEFtdFwiOjAuMCxcIlNnc3RBbXRcIjowLjAsXCJDZXNSdFwiOjAuMCxcIkNlc0FtdFwiOjAuMCxcIkNlc05vbkFkdmxBbXRcIjowLjAsXCJTdGF0ZUNlc1J0XCI6MC4wLFwiU3RhdGVDZXNBbXRcIjowLjAsXCJTdGF0ZUNlc05vbkFkdmxBbXRcIjowLjAsXCJPdGhDaHJnXCI6MC4wLFwiVG90SXRlbVZhbFwiOjQuMixcIkJjaER0bHNcIjp7XCJObVwiOlwiYWFhXCIsXCJFeHBEdFwiOlwiMzEvMTAvMjAyMFwiLFwiV3JEdFwiOlwiMzEvMTAvMjAyMFwifSxcIkF0dHJpYkR0bHNcIjpbe1wiTm1cIjpcImFhYVwiLFwiVmFsXCI6XCIxNDc4NTJcIn1dfV0sXCJWYWxEdGxzXCI6e1wiQXNzVmFsXCI6NC4wLFwiQ2dzdFZhbFwiOjAuMCxcIlNnc3RWYWxcIjowLjAsXCJJZ3N0VmFsXCI6MC4yLFwiQ2VzVmFsXCI6MC4wLFwiU3RDZXNWYWxcIjowLjAsXCJEaXNjb3VudFwiOjAuMCxcIk90aENocmdcIjowLjAsXCJSbmRPZmZBbXRcIjowLjAsXCJUb3RJbnZWYWxcIjo0LjIsXCJUb3RJbnZWYWxGY1wiOjAuMH0sXCJQYXlEdGxzXCI6e1wiTm1cIjpcIlBheWVlIE5hbWVcIixcIkFjY0RldFwiOlwiQWNjb3VudCBEZXRhaWxzXCIsXCJNb2RlXCI6XCJDQVNIXCIsXCJGaW5JbnNCclwiOlwiS0tLMDAwMTgwXCIsXCJQYXlUZXJtXCI6XCJUZXJtcyBvZiBQYXltZW50XCIsXCJQYXlJbnN0clwiOlwiUGF5bWVudCBJbnN0cnVjdGlvblwiLFwiQ3JUcm5cIjpcIkNyZWRpdCBUcmFuc2ZlclwiLFwiRGlyRHJcIjpcIkRpcmVjdCBEZWJpdFwiLFwiQ3JEYXlcIjoyLFwiUGFpZEFtdFwiOjEwMC4yLFwiUGF5bXREdWVcIjoxLjl9LFwiUmVmRHRsc1wiOntcIkludlJtXCI6XCJJbnZvaWNlIFJlbWFya3NcIixcIkRvY1BlcmREdGxzXCI6e1wiSW52U3REdFwiOlwiMDcvMDMvMjAyMFwiLFwiSW52RW5kRHRcIjpcIjA3LzAzLzIwMjBcIn0sXCJQcmVjRG9jRHRsc1wiOlt7XCJJbnZOb1wiOlwiQ0ZSVC8wMDA2XCIsXCJJbnZEdFwiOlwiMDcvMDMvMjAyMFwiLFwiT3RoUmVmTm9cIjpcIjIzMzRcIn1dLFwiQ29udHJEdGxzXCI6W3tcIlJlY0FkdlJlZnJcIjpcImFhYVwiLFwiUmVjQWR2RHRcIjpcIjA3LzAzLzIwMjBcIixcIlRlbmRSZWZyXCI6XCIyMzM0XCIsXCJDb250clJlZnJcIjpcIjIzMzRcIixcIkV4dFJlZnJcIjpcIjIzMzRcIixcIlByb2pSZWZyXCI6XCIyMzM0XCIsXCJQT1JlZnJcIjpcIjIzMzQzMzQ1NDU0NVwiLFwiUE9SZWZEdFwiOlwiMDcvMDIvMjAyMFwifV19LFwiQWRkbERvY0R0bHNcIjpbe1wiVXJsXCI6XCJhc2Fmc2RcIixcIkRvY3NcIjpcImluZGlhXCIsXCJJbmZvXCI6XCJpbmRpYVwifV0sXCJFeHBEdGxzXCI6e1wiUG9ydFwiOlwiMTJcIixcIlJlZkNsbVwiOlwiTlwiLFwiRm9yQ3VyXCI6XCJpbnJcIixcIkNudENvZGVcIjpcIklOXCIsXCJFeHBEdXR5XCI6OTAuOX0sXCJFd2JEdGxzXCI6e1wiVHJhbnNJZFwiOlwiMDVBQUFCQjA2MzlHMVo4XCIsXCJUcmFuc05hbWVcIjpcIkpheSBUcmFuc1wiLFwiVHJhbnNNb2RlXCI6XCIxXCIsXCJEaXN0YW5jZVwiOjI5NixcIlRyYW5zRG9jTm9cIjpcIjEyMzAxXCIsXCJUcmFuc0RvY0R0XCI6XCIxNC8wOS8yMDIzXCIsXCJWZWhOb1wiOlwiUFFSMTIzNFwiLFwiVmVoVHlwZVwiOlwiUlwifX0iLCJpc3MiOiJOSUMgU2FuZGJveCJ9.Vdwpz08_AR_rfakbj1qTOsD8IMMGqn-wrHOf7ir_glfiLPFhPtq6yrLzb3aORajyWDSc4wBwp3LyRN7cpSs9TX9zuu_cjhMbRAl6e1yvYyIa_-cRoz7xJ7QbomWz0CnX5BgN0-xnl4B-EuBcc2nAzdMhG8UKPRho5G09byQzYTaRWyhPQSPBFMkJTnDrMJRqgjxPqsxNarNesO64Gt9e7G2P83XOrqOzROpsX2mdbEtkLFdLmwgzS1xN7quOWLtusGQmj0A1AeDMv_B9QVLT7O5www1oBci2kEI8go0c9zY4qCnHlRJmW1_izpNWzPXM6hwGdfsQRmPyygIgu8QqFQ",
      "SignedQRCode": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjE1MTNCODIxRUU0NkM3NDlBNjNCODZFMzE4QkY3MTEwOTkyODdEMUYiLCJ4NXQiOiJGUk80SWU1R3gwbW1PNGJqR0w5eEVKa29mUjgiLCJ0eXAiOiJKV1QifQ.eyJkYXRhIjoie1wiU2VsbGVyR3N0aW5cIjpcIjA5QUFBUEc3ODg1UjAwMlwiLFwiQnV5ZXJHc3RpblwiOlwiMDVBQUFQRzc4ODVSMDAyXCIsXCJEb2NOb1wiOlwiVEFUQS85OTAyNFwiLFwiRG9jVHlwXCI6XCJJTlZcIixcIkRvY0R0XCI6XCIxNC8wOS8yMDIzXCIsXCJUb3RJbnZWYWxcIjo0LjIsXCJJdGVtQ250XCI6MSxcIk1haW5Ic25Db2RlXCI6XCIxMDAxXCIsXCJJcm5cIjpcImQ4MTJkNDNjZjlhODk1MWUyNTk3MzM5MGZjZDJiMmZiYWE0Nzg2ZTc3OWEyNzc3Njg0MjJlNzMwMmQ1MGZjZGVcIixcIklybkR0XCI6XCIyMDIzLTA5LTE0IDEzOjIwOjU2XCJ9IiwiaXNzIjoiTklDIFNhbmRib3gifQ.OM6_Yk5R71ZwzmHmFTFfO3eF-Cn1x1sReZwHV6-2pyHYSD6mhlcPapMUjrHRCK0YUIXl9U3WnLVx0ZKp7AJ_1Pc7cBFVS_fCd2DldqCOXTZc1aEQx6dQRurAb1It10NF_YzczviRj9uVxVitTPoiPtRd_KiElYR5xhGVtyQdcjX5xImXflJqHLU-ycsG9hmxRXiMkMCZ-UlgAnF3ptr156pCaDRPrAnBJEZNt8c8J3wNeSkI9yahAURIsnTHjh3i-3Uqype4522jLv90Pqcvl8OfVrEpmey306wTnTrjADKMlrY5x2dW8KY5uM5eTjoCZddQYu9S5pB5iC8RqqMBwA",
      "EwbNo": 471008880909,
      "EwbDt": "2023-09-14 13:21:00",
      "EwbValidTill": "2023-09-16 23:59:00",
      "QRCodeUrl": "https://sandb-api.mastersindia.co/api/v1/einvoice/qrcode/anVsX3NlcF8yMDIzLTI0-6502bc57b5f34bd57e404e6f/",
      "EinvoicePdf": "https://sandb-api.mastersindia.co/api/v1/einvoice/pdf/anVsX3NlcF8yMDIzLTI0-6502bc57b5f34bd57e404e6f/",
      "EwaybillPdf": "https://sandb-api.mastersindia.co/api/v1/detailPrintPdf/anVsX3NlcF8yMDIzLTI0-6502bc57b5f34bd57e404e71/",
      "Status": "ACT",
      "Remarks": "",
      "alert": "",
      "error": false
    },
    "errorMessage": "",
    "InfoDtls": "",
    "status": "Success",
    "code": 200,
    "requestId": "XXXXXXXXXXXXXXX_TATA/99024_1694678102"
  }
}
```

#### 204 (Invalid Parameters)

```json
{
  "results": {
    "message": "",
    "errorMessage": "3038: Seller details Details:Pincode-101301 does not exists",
    "InfoDtls": "",
    "status": "Failed",
    "code": 204,
    "requestId": "XXXXXXXXXXXXXXX_TATA/99025_1694678229"
  }
}
```
