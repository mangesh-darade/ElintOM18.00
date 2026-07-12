> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/initiate-multi-vehicle.md).

# Initiate Multi Vehicle

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/multiVehicleMovement/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/initiate_multi_vehicle_api_v_1_multiVehicleMovement__post).

#### Sample Request

```json
{
    "userGstin": "XXXXXXXXXXXXXXX",
    "eway_bill_number": 321009218808,
    "reason_code_for_vehicle_updation": "due to break down",
    "reason_for_vehicle_updation": "",
    "place_of_consignor": "Dehradun",
    "state_of_consignor": "UTTARAKHAND",
    "place_of_consignee": "Beml Nagar",
    "state_of_consignee": "KARNATAKA",
    "mode_of_transport": 1,
    "total_quantity": 60,
    "unit_code": "NOS",
    "data_source": "erp"
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/initiate_multi_vehicle_api_v_1_multiVehicleMovement__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "ewbNo": "321009218808",
      "groupNo": "1",
      "createdDate": "15/09/2023 03:48:00 PM",
      "error": false
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
    "message": "220: Invalid Trans mode",
    "status": "No Content",
    "code": 204,
    "nic_code": "220"
  }
}
```
