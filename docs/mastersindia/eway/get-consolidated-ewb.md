> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-consolidated-ewb.md).

# Get Consolidated EWB

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

| Parameter                        | Value           | Description                    |
| -------------------------------- | --------------- | ------------------------------ |
| action                           | GetTripSheet    | Action                         |
| gstin                            | XXXXXXXXXXXXXXX | Gstin                          |
| consolidated\_eway\_bill\_number | 3210007391      | Consolidated e-way bill number |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": {
      "trip_sheet_number": 3210007391,
      "from_place": "rajpura",
      "state_of_consignor": "PUNJAB",
      "vehicle_number": "JJK8977",
      "transportation_mode": "Rail",
      "transporter_document_number": "14456",
      "transporter_document_date": "15/09/2023",
      "userGstin": "XXXXXXXXXXXXXXX",
      "entered_date": "15/09/2023 03:32:00 PM",
      "tripSheetEwbBills": [
        {
          "eway_bill_number": 381009218819,
          "eway_bill_date": "15/09/2023 03:18:00 PM",
          "userGstin": "XXXXXXXXXXXXXXX",
          "document_number": "eway/pctt11/8",
          "document_date": "10/05/2018",
          "gstin_of_consignor": "XXXXXXXXXXXXXXX",
          "legal_name_of_consignor": "welton",
          "gstin_of_consignee": "XXXXXXXXXXXXXXX",
          "legal_name_of_consignee": "sthuthya",
          "total_invoice_value": 16702,
          "eway_bill_valid_date": "16/09/2023 11:59:00 PM"
        },
        {
          "eway_bill_number": 371009218816,
          "eway_bill_date": "15/09/2023 03:10:00 PM",
          "userGstin": "XXXXXXXXXXXXXXX",
          "document_number": "T1ffr56t7931",
          "document_date": "04/05/2023",
          "gstin_of_consignor": "XXXXXXXXXXXXXXX",
          "legal_name_of_consignor": "LPG",
          "gstin_of_consignee": "XXXXXXXXXXXXXXX",
          "legal_name_of_consignee": "Is Pvt. Ltd.",
          "total_invoice_value": 119,
          "eway_bill_valid_date": "23/09/2023 11:59:00 PM"
        }
      ]
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
