> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/get-e-way-bill-details.md).

# Get E Way Bill Details

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

| Parameter          | Value           | Description       |
| ------------------ | --------------- | ----------------- |
| action             | GetEwayBill     | Action            |
| gstin              | XXXXXXXXXXXXXXX | Gstin             |
| eway\_bill\_number | 321009218808    | E-Way Bill Number |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": {
      "eway_bill_number": 321009218808,
      "eway_bill_date": "15/09/2023 02:44:00 PM",
      "generate_mode": "API",
      "userGstin": "XXXXXXXXXXXXXXX",
      "supply_type": "OUTWARD",
      "sub_supply_type": "Supply",
      "document_type": "Tax Invoice",
      "document_number": "IMP/23/035",
      "document_date": "22/06/2023",
      "gstin_of_consignor": "XXXXXXXXXXXXXXX",
      "legal_name_of_consignor": "Welton",
      "address1_of_consignor": "The Taj Mahal Palace,Apollo Bandar",
      "address2_of_consignor": "Colaba,Mumbai",
      "place_of_consignor": "Mumbai",
      "pincode_of_consignor": 400001,
      "state_of_consignor": "UTTARAKHAND",
      "gstin_of_consignee": "XXXXXXXXXXXXXXX",
      "legal_name_of_consignee": "Sthuthya",
      "address1_of_consignee": "Shree Nilaya",
      "address2_of_consignee": "Dasarahosahalli",
      "place_of_consignee": "Dehradun",
      "pincode_of_consignee": 248001,
      "state_of_supply": "UTTARAKHAND",
      "taxable_amount": 500,
      "total_invoice_value": 560,
      "cgst_amount": 30,
      "sgst_amount": 30,
      "igst_amount": 0,
      "cess_amount": 0,
      "transporter_id": "XXXXXXXXXXXXXXX",
      "transporter_name": "M/S UTTARAYAN CO-OPERATIVE FOR RENEWABLE ENERGY",
      "eway_bill_status": "Active",
      "transportation_distance": 1769,
      "number_of_valid_days": 9,
      "eway_bill_valid_date": "24/09/2023 11:59:00 PM",
      "extended_times": 0,
      "reject_status": "N",
      "vehicle_type": "regular",
      "actual_from_state_name": "MAHARASHTRA",
      "actual_to_state_name": "UTTARAKHAND",
      "transaction_type": "Bill/Dispatch From",
      "other_value": 0,
      "cess_nonadvol_value": 0,
      "itemList": [
        {
          "item_number": 1,
          "product_id": 0,
          "product_name": "Wheat",
          "product_description": "Wheat",
          "hsn_code": 1001,
          "quantity": 5,
          "unit_of_product": "BOX",
          "cgst_rate": 6,
          "sgst_rate": 6,
          "igst_rate": 0,
          "cess_rate": 0,
          "cessNonAdvol": 0,
          "taxable_amount": 500
        }
      ],
      "VehiclListDetails": [
        {
          "update_mode": "API",
          "vehicle_number": "UQC1234",
          "place_of_consignor": "Dehradun",
          "state_of_consignor": "UTTARAKHAND",
          "tripshtNo": 0,
          "userGstin": "XXXXXXXXXXXXXXX",
          "vehicle_number_update_date": "15/09/2023 05:25:00 PM",
          "transportation_mode": "Road",
          "transporter_document_number": "NEWTDN0011234",
          "transporter_document_date": null,
          "group_number": "1"
        },
        {
          "update_mode": "API",
          "vehicle_number": "HHJ9089",
          "place_of_consignor": "Dehradun",
          "state_of_consignor": "UTTARAKHAND",
          "tripshtNo": 0,
          "userGstin": "XXXXXXXXXXXXXXX",
          "vehicle_number_update_date": "15/09/2023 03:50:00 PM",
          "transportation_mode": "Road",
          "transporter_document_number": "908899",
          "transporter_document_date": "25/06/2023",
          "group_number": "1"
        },
        {
          "update_mode": "API",
          "vehicle_number": "JJK8977",
          "place_of_consignor": "Noida",
          "state_of_consignor": "UTTAR PRADESH",
          "tripshtNo": 0,
          "userGstin": "XXXXXXXXXXXXXXX",
          "vehicle_number_update_date": "15/09/2023 02:46:00 PM",
          "transportation_mode": "Road",
          "transporter_document_number": "14456",
          "transporter_document_date": "25/06/2023",
          "group_number": "0"
        },
        {
          "update_mode": "API",
          "vehicle_number": "JJK8977",
          "place_of_consignor": "Noida",
          "state_of_consignor": "UTTAR PRADESH",
          "tripshtNo": 0,
          "userGstin": "XXXXXXXXXXXXXXX",
          "vehicle_number_update_date": "15/09/2023 02:45:00 PM",
          "transportation_mode": "Road",
          "transporter_document_number": "14456",
          "transporter_document_date": "25/06/2023",
          "group_number": "0"
        },
        {
          "update_mode": "API",
          "vehicle_number": "WXX2121",
          "place_of_consignor": "Mumbai",
          "state_of_consignor": "MAHARASHTRA",
          "tripshtNo": 0,
          "userGstin": "XXXXXXXXXXXXXXX",
          "vehicle_number_update_date": "15/09/2023 02:44:00 PM",
          "transportation_mode": "Road",
          "transporter_document_number": "",
          "transporter_document_date": null,
          "group_number": "0"
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
    "message": "357: Could not retrieve eway bill details, pl. contact helpdesk",
    "status": "No Content",
    "code": 204,
    "nic_code": "357"
  }
}
```
