> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-ewb-by-date.md).

# Get EWB By Date

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

| Parameter       | Value              | Description    |
| --------------- | ------------------ | -------------- |
| action          | GetEwayBillsByDate | Action         |
| gstin           | XXXXXXXXXXXXXXX    | Gstin          |
| generated\_date | 15/09/2023         | Generated Date |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": [
      {
        "eway_bill_number": 321009218840,
        "eway_bill_date": "15/09/2023 04:21:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "DC-505/23-24",
        "document_date": "15/09/2023",
        "pincode_of_delivery": 201301,
        "state_name_of_delivery": "UTTAR PRADESH",
        "place_of_delivery": "NOIDA",
        "eway_bill_valid_date": "16/09/2023 11:59:00 PM",
        "extended_times": 0,
        "reject_status": "N"
      },
      {
        "eway_bill_number": 321009218837,
        "eway_bill_date": "15/09/2023 04:13:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "DC/GD-4/23-24",
        "document_date": "15/09/2023",
        "pincode_of_delivery": 201301,
        "state_name_of_delivery": "UTTAR PRADESH",
        "place_of_delivery": "NOIDA",
        "eway_bill_valid_date": "16/09/2023 11:59:00 PM",
        "extended_times": 0,
        "reject_status": "N"
      },
      {
        "eway_bill_number": 381009218835,
        "eway_bill_date": "15/09/2023 04:09:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "FG-OT-9/23-24",
        "document_date": "15/09/2023",
        "pincode_of_delivery": 201301,
        "state_name_of_delivery": "UTTAR PRADESH",
        "place_of_delivery": "NOIDA",
        "eway_bill_valid_date": "16/09/2023 11:59:00 PM",
        "extended_times": 0,
        "reject_status": "N"
      },
      {
        "eway_bill_number": 311009218834,
        "eway_bill_date": "15/09/2023 04:07:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "abc153",
        "document_date": "11/09/2023",
        "pincode_of_delivery": 201304,
        "state_name_of_delivery": "UTTAR PRADESH",
        "place_of_delivery": "Faridabad",
        "eway_bill_valid_date": "16/09/2023 11:59:00 PM",
        "extended_times": 0,
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
    "message": "418: No record found.",
    "status": "No Content",
    "code": 204,
    "nic_code": "418"
  }
}
```
