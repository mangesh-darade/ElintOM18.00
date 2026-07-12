> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/change-multi-vehicles.md).

# Change Multi Vehicles

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/changeVehiclesDetailsforMultiVehicle/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/change_multi_vehicles_api_v_1_changeVehiclesDetailsforMultiVehicle__post).

#### Sample Request

```json
{
    "userGstin": "XXXXXXXXXXXXXXX",
    "eway_bill_number": 321009218808,
    "group_number": 1,
    "old_vehicle_number": "HHJ9089",
    "new_vehicle_number": "UQC1234",
    "old_transporter_document_number": "908899",
    "new_transporter_document_number": "NEWTDN0011234",
    "place_of_consignor": "Dehradun",
    "state_of_consignor": "UTTARAKHAND",
    "reason_code_for_vehicle_updation": "due to break down",
    "reason_for_vehicle_updation": "",
    "data_source": "erp"
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/change_multi_vehicles_api_v_1_changeVehiclesDetailsforMultiVehicle__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "ewbNo": "321009218808",
      "groupNo": "1",
      "vehUpdDate": "15/09/2023 05:25:00 PM",
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
    "message": "406: Group number cannot be empty or zero.",
    "status": "No Content",
    "code": 204,
    "nic_code": "406"
  }
}
```
