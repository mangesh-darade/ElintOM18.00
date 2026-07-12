> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-ewb-by-consignor.md).

# Get EWB By Consignor

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

| Parameter        | Value                           | Description     |
| ---------------- | ------------------------------- | --------------- |
| action           | GetEwayBillGeneratedByConsigner | Action          |
| gstin            | XXXXXXXXXXXXXXX                 | Gstin           |
| document\_number | IMP/23/035                      | Document Number |
| document\_type   | Tax Invoice                     | Document Type   |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": {
      "ewayBillNo": 321009218808,
      "ewayBillDate": "15/09/2023 02:44:00 PM",
      "validUpto": "24/09/2023 23:59:59 ",
      "alert": ""
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
    "message": "418: No record found.",
    "status": "No Content",
    "code": 204,
    "nic_code": "418"
  }
}
```
