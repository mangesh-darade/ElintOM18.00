> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-ewaybills-rejected-by-others.md).

# Get EwayBills Rejected By Others

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

| Parameter       | Value                        | Description    |
| --------------- | ---------------------------- | -------------- |
| action          | GetEwayBillsRejectedByOthers | Action         |
| gstin           | XXXXXXXXXXXXXXX              | Gstin          |
| generated\_date | 15/09/2023                   | Generated Date |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": [
      {
        "eway_bill_number": 321009218808,
        "eway_bill_date": "15/09/2023 02:44:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "IMP/23/035",
        "document_date": "22/06/2023",
        "eway_bill_valid_date": "24/09/2023 11:59:00 PM",
        "reject_status": "Y",
        "reject_date": "15/09/2023 08:05:00 PM",
        "pincode_of_delivery": "",
        "state_name_of_delivery": "",
        "extended_times": "",
        "place_of_delivery": ""
      },
      {
        "eway_bill_number": 321009218809,
        "eway_bill_date": "15/09/2023 02:44:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "IMP/23/036",
        "document_date": "22/06/2023",
        "eway_bill_valid_date": "24/09/2023 11:59:00 PM",
        "reject_status": "Y",
        "reject_date": "15/09/2023 08:05:00 PM",
        "pincode_of_delivery": "",
        "state_name_of_delivery": "",
        "extended_times": "",
        "place_of_delivery": ""
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
    "message": "418: No record found.",
    "status": "No Content",
    "code": 204,
    "nic_code": "418"
  }
}
```
