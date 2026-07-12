> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-ewb-by-transporter-4.md).

# Get EWB By Transporter 4

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

| Parameter      | Value                                      | Description   |
| -------------- | ------------------------------------------ | ------------- |
| action         | GetEwayBillReportByTransporterassignedDate | Action        |
| gstin          | XXXXXXXXXXXXXXX                            | Gstin         |
| assigned\_date | 25/06/2023                                 | Assigned Date |
| state\_code    | 5                                          | State Code    |

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
        "pincode_of_delivery": 248001,
        "state_name_of_delivery": "UTTARAKHAND",
        "place_of_delivery": "Dehradun",
        "eway_bill_valid_date": "24/09/2023 11:59:00 PM",
        "extended_times": 0,
        "reject_status": "Y"
      },
      {
        "eway_bill_number": 341009218804,
        "eway_bill_date": "15/09/2023 02:38:00 PM",
        "eway_bill_status": "Active",
        "gstin_of_generator": "XXXXXXXXXXXXXXX",
        "document_number": "eway/pctt11/3",
        "document_date": "10/05/2018",
        "pincode_of_delivery": 121004,
        "state_name_of_delivery": "HARYANA",
        "place_of_delivery": "Beml Nagar",
        "eway_bill_valid_date": "",
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
