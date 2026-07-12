> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-other-party-ewb.md).

# Get Other Party EWB

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

| Parameter       | Value                    | Description    |
| --------------- | ------------------------ | -------------- |
| action          | GetEwayBillsofOtherParty | Action         |
| gstin           | XXXXXXXXXXXXXXX          | Gstin          |
| generated\_date | 15/09/2023               | Generated Date |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": [
      {
        "eway_bill_number": 301009218790,
        "eway_bill_date": "15/09/2023 01:55:00 PM",
        "generate_mode": "API",
        "userGstin": "XXXXXXXXXXXXXXX",
        "document_number": "eway/pctt11/2",
        "document_date": "10/05/2018",
        "gstin_of_consignor": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignor": "welton",
        "gstin_of_consignee": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignee": "sthuthya",
        "total_invoice_value": 16702.01,
        "hsn_code": 84314930,
        "hsn_description": "PIN/WB/H LINK 851",
        "eway_bill_status": "Active",
        "reject_status": "N"
      },
      {
        "eway_bill_number": 301009218857,
        "eway_bill_date": "15/09/2023 05:48:00 PM",
        "generate_mode": "API",
        "userGstin": "XXXXXXXXXXXXXXX",
        "document_number": "4239000005",
        "document_date": "15/09/2023",
        "gstin_of_consignor": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignor": "Kasuya GPP Auto Products",
        "gstin_of_consignee": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignee": "SHIVA TECHNIQUES SURFACES PVT. LTD.",
        "total_invoice_value": 0,
        "hsn_code": 84099990,
        "hsn_description": "LOMB. 2 CYL 107604508600  A/F OD GRIND F",
        "eway_bill_status": "Active",
        "reject_status": "N"
      },
      {
        "eway_bill_number": 311009218805,
        "eway_bill_date": "15/09/2023 02:39:00 PM",
        "generate_mode": "API",
        "userGstin": "XXXXXXXXXXXXXXX",
        "document_number": "eway/pctt11/4",
        "document_date": "10/05/2018",
        "gstin_of_consignor": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignor": "welton",
        "gstin_of_consignee": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignee": "sthuthya",
        "total_invoice_value": 16702.01,
        "hsn_code": 84314930,
        "hsn_description": "PIN/WB/H LINK 851",
        "eway_bill_status": "Active",
        "reject_status": "N"
      },
      {
        "eway_bill_number": 321009218770,
        "eway_bill_date": "15/09/2023 12:38:00 PM",
        "generate_mode": "API",
        "userGstin": "XXXXXXXXXXXXXXX",
        "document_number": "MIPL/169801032",
        "document_date": "02/11/2022",
        "gstin_of_consignor": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignor": "welton",
        "gstin_of_consignee": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignee": "sthuthya",
        "total_invoice_value": 118,
        "hsn_code": 1001,
        "hsn_description": "Wheat",
        "eway_bill_status": "Active",
        "reject_status": "N"
      },
      {
        "eway_bill_number": 331009218773,
        "eway_bill_date": "15/09/2023 12:46:00 PM",
        "generate_mode": "API",
        "userGstin": "XXXXXXXXXXXXXXX",
        "document_number": "MIPL/169801034",
        "document_date": "02/11/2022",
        "gstin_of_consignor": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignor": "welton",
        "gstin_of_consignee": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignee": "sthuthya",
        "total_invoice_value": 118,
        "hsn_code": 1001,
        "hsn_description": "Wheat",
        "eway_bill_status": "Active",
        "reject_status": "N"
      },
      {
        "eway_bill_number": 331009218814,
        "eway_bill_date": "15/09/2023 03:08:00 PM",
        "generate_mode": "API",
        "userGstin": "XXXXXXXXXXXXXXX",
        "document_number": "eway/pctt11/7",
        "document_date": "10/05/2018",
        "gstin_of_consignor": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignor": "welton",
        "gstin_of_consignee": "XXXXXXXXXXXXXXX",
        "legal_name_of_consignee": "sthuthya",
        "total_invoice_value": 16702.01,
        "hsn_code": 84314930,
        "hsn_description": "PIN/WB/H LINK 851",
        "eway_bill_status": "Active",
        "reject_status": "N"
      }
    ],
    "status": "Success",
    "code": 200
  }
}
```

#### 204 (Invalid Parameters)

```json
{
  "results": {
    "message": "366: You will not get the ewaybills generated today, howerver you cann access the ewaybills of yester days",
    "status": "No Content",
    "code": 204,
    "nic_code": "366"
  }
}
```
