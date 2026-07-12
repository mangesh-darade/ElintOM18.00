> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-ewb-by-transporter-1.md).

# Get EWB By Transporter 1

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

| Parameter       | Value                             | Description    |
| --------------- | --------------------------------- | -------------- |
| action          | GetEwayBillsForTransporterByState | Action         |
| gstin           | XXXXXXXXXXXXXXX                   | Gstin          |
| generated\_date | 25/06/2023                        | Generated Date |
| state\_code     | 5                                 | State Code     |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": [
      {
        "eway_bill_number": 321009218770,
        "eway_bill_date": "15/09/2023 12:38:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "MIPL/169801032",
        "document_date": "02/11/2022",
        "pincode_of_delivery": 247663,
        "state_name_of_delivery": "UTTARAKHAND",
        "place_of_delivery": "Beml Nagar",
        "eway_bill_valid_date": "16/09/2023 11:59:00 PM",
        "extended_times": 0,
        "reject_status": "N"
      },
      {
        "eway_bill_number": 361009218772,
        "eway_bill_date": "15/09/2023 12:45:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "MIPL/169801033",
        "document_date": "02/11/2022",
        "pincode_of_delivery": 247663,
        "state_name_of_delivery": "UTTARAKHAND",
        "place_of_delivery": "Beml Nagar",
        "eway_bill_valid_date": "16/09/2023 11:59:00 PM",
        "extended_times": 0,
        "reject_status": "N"
      },
      {
        "eway_bill_number": 331009218773,
        "eway_bill_date": "15/09/2023 12:46:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "MIPL/169801034",
        "document_date": "02/11/2022",
        "pincode_of_delivery": 247663,
        "state_name_of_delivery": "UTTARAKHAND",
        "place_of_delivery": "Beml Nagar",
        "eway_bill_valid_date": "16/09/2023 11:59:00 PM",
        "extended_times": 0,
        "reject_status": "N"
      },
      {
        "eway_bill_number": 371009218775,
        "eway_bill_date": "15/09/2023 01:02:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "767676",
        "document_date": "09/01/2022",
        "pincode_of_delivery": 201301,
        "state_name_of_delivery": "UTTAR PRADESH",
        "place_of_delivery": "VARANASI",
        "eway_bill_valid_date": "16/09/2023 11:59:00 PM",
        "extended_times": 0,
        "reject_status": "N"
      },
      {
        "eway_bill_number": 351009218782,
        "eway_bill_date": "15/09/2023 01:26:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "FG-OT-6/23-24",
        "document_date": "15/09/2023",
        "pincode_of_delivery": 201301,
        "state_name_of_delivery": "UTTAR PRADESH",
        "place_of_delivery": "NOIDA",
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
    "message": "336: Could not retrieve transporter data by gstin",
    "status": "No Content",
    "code": 204,
    "nic_code": "336"
  }
}
```
