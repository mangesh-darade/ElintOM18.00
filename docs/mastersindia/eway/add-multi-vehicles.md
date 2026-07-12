> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/add-multi-vehicles.md).

# Add Multi Vehicles

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/addvehiclesDetailsforMultiVehicle/
```

### Request Headers

| Parameter              | Value               | Description                                                   |
| ---------------------- | ------------------- | ------------------------------------------------------------- |
| Authorization/api\_key | api\_key/auth token | Value and header type is mentioned in the Authentication Page |

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/add_multi_vehicles_api_v_1_addvehiclesDetailsforMultiVehicle__post).

#### Sample Request

```json
{
    "userGstin": "XXXXXXXXXXXXXXX",
    "eway_bill_number": 321009218808,
    "group_number": "1",
    "vehicle_number": "HHJ9089",
    "transporter_document_number": "908899",
    "transporter_document_date": "25/06/2023",
    "quantity": 5.25,
    "data_source": "erp"
}
```

### Response Body

#### 200 (OK)

The response description for success can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Eway/operation/add_multi_vehicles_api_v_1_addvehiclesDetailsforMultiVehicle__post).

#### Sample Response

```json
{
  "results": {
    "message": {
      "ewbNo": "321009218808",
      "groupNo": "1",
      "vehAddedDate": "15/09/2023 03:50:00 PM",
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
    "message": "224: Invalid Transaction Date",
    "status": "No Content",
    "code": 204,
    "nic_code": "224"
  }
}
```
