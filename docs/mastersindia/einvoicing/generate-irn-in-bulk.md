> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/einvoicing/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/einvoicing/generate-irn-in-bulk.md).

# Generate IRN in Bulk

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/einvoiceGenerateInBulk/
```

### Request Headers

<table><thead><tr><th>Parameter</th><th width="184">Value</th><th>Description</th></tr></thead><tbody><tr><td>Authorization/api_key</td><td>api_key/auth token</td><td>Value and header type is mentioned in the Authentication Page</td></tr></tbody></table>

### Request Body

The request body description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Einvoice/operation/generate_irn_in_bulk_api_v_1_einvoiceGenerateInBulk__post).

#### Sample Request

```json
{
	"einvoice_list": [{
			"user_gstin": "XXXXXXXXXXXXXXX",
			"data_source": "erp",
			"transaction_details": {
				"supply_type": "B2B",
				"charge_type": "Y",
				"igst_on_intra": "N",
				"ecommerce_gstin": ""
			},
			"document_details": {
				"document_type": "INV",
				"document_number": "TATA/A/0011",
				"document_date": "23/06/2023"
			},
			"seller_details": {
				"gstin": "XXXXXXXXXXXXXXX",
				"legal_name": "MastersIndia UP",
				"trade_name": "123",
				"address1": "45",
				"address2": "l45",
				"location": "1279",
				"pincode": 201301,
				"state_code": "09",
				"phone_number": 9876543231,
				"email": ""
			},
			"buyer_details": {
				"gstin": "XXXXXXXXXXXXXXX",
				"legal_name": "ewewewehyriu32y8ru",
				"trade_name": "122",
				"address1": "9",
				"address2": "17843",
				"location": "12321u",
				"pincode": 263001,
				"place_of_supply": "13",
				"state_code": "05",
				"phone_number": "123233",
				"email": ""
			},
			"dispatch_details": {
				"company_name": "dqfefkewl",
				"address1": "Vila",
				"address2": "Vila",
				"location": "Noida",
				"pincode": 201301,
				"state_code": "09"
			},
			"ship_details": {
				"gstin": "XXXXXXXXXXXXXXX",
				"legal_name": "123",
				"trade_name": "232",
				"address1": "1",
				"address2": "",
				"location": "221",
				"pincode": 263001,
				"state_code": "5"
			},
			"export_details": {
				"ship_bill_number": "",
				"ship_bill_date": "",
				"country_code": "IN",
				"foreign_currency": "inr",
				"refund_claim": "N",
				"port_code": "12",
				"export_duty": "90.9"
			},
			"payment_details": {
				"bank_account_number": "Account Details",
				"paid_balance_amount": "100.2",
				"credit_days": 2,
				"credit_transfer": "Credit Transfer",
				"direct_debit": "Direct Debit",
				"branch_or_ifsc": "KKK000180",
				"payment_mode": "CASH",
				"payee_name": "Payee Name",
				"outstanding_amount": "1.9",
				"payment_instruction": "Payment Instruction",
				"payment_term": "Terms of Payment"
			},
			"reference_details": {
				"invoice_remarks": "Invoice Remarks",
				"document_period_details": {
					"invoice_period_start_date": "07/03/2020",
					"invoice_period_end_date": "07/03/2020"
				},
				"preceding_document_details": [{
					"reference_of_original_invoice": "CFRT/0006",
					"preceding_invoice_date": "07/03/2020",
					"other_reference": "2334"
				}],
				"contract_details": [{
					"receipt_advice_number": "aaa",
					"receipt_advice_date": "07/03/2020",
					"batch_reference_number": "2334",
					"contract_reference_number": "2334",
					"other_reference": "2334",
					"project_reference_number": "2334",
					"vendor_po_reference_number": "233433454545",
					"vendor_po_reference_date": "07/02/2020"
				}]
			},
			"additional_document_details": [{
				"supporting_document_url": "asafsd",
				"supporting_document": "india",
				"additional_information": "india"
			}],
			"ewaybill_details": {
				"transporter_id": "XXXXXXXXXXXXXXX",
				"transporter_name": "Jay Trans",
				"transportation_mode": "1",
				"transportation_distance": 4000,
				"transporter_document_number": "1230",
				"transporter_document_date": "23/06/2023",
				"vehicle_number": "PQR1234",
				"vehicle_type": "R"
			},
			"value_details": {
				"total_assessable_value": 4,
				"total_cgst_value": "",
				"total_sgst_value": 0,
				"total_igst_value": 0.2,
				"total_cess_value": 0,
				"total_cess_value_of_state": 0,
				"total_discount": 0,
				"total_other_charge": 0,
				"total_invoice_value": 4.2,
				"round_off_amount": 0,
				"total_invoice_value_additional_currency": 0
			},
			"item_list": [{
				"item_serial_number": "501",
				"product_description": "Wheat desc",
				"is_service": "N",
				"hsn_code": "1001",
				"bar_code": "1212",
				"quantity": 1,
				"free_quantity": 0,
				"unit": "KGS",
				"unit_price": 4,
				"total_amount": 4,
				"pre_tax_value": 0,
				"discount": 0,
				"other_charge": 0,
				"assessable_value": 4,
				"gst_rate": 5,
				"igst_amount": 0.2,
				"cgst_amount": 0,
				"sgst_amount": 0,
				"cess_rate": 0,
				"cess_amount": 0,
				"cess_nonadvol_amount": 0,
				"state_cess_rate": 0,
				"state_cess_amount": 0,
				"state_cess_nonadvol_amount": 0,
				"total_item_value": 4.2,
				"country_origin": "",
				"order_line_reference": "",
				"product_serial_number": "",
				"batch_details": {
					"name": "aaa",
					"expiry_date": "31/10/2020",
					"warranty_date": "31/10/2020"
				},
				"attribute_details": [{
					"item_attribute_details": "aaa",
					"item_attribute_value": "147852"
				}]
			}]
		},

		{
			"user_gstin": "XXXXXXXXXXXXXXX",
			"data_source": "erp",
			"transaction_details": {
				"supply_type": "B2B",
				"charge_type": "Y",
				"igst_on_intra": "N",
				"ecommerce_gstin": ""
			},
			"document_details": {
				"document_type": "INV",
				"document_number": "TATA/A/0012",
				"document_date": "23/06/2023"
			},
			"seller_details": {
				"gstin": "XXXXXXXXXXXXXXX",
				"legal_name": "MastersIndia UP",
				"trade_name": "123",
				"address1": "45",
				"address2": "l45",
				"location": "1279",
				"pincode": 201301,
				"state_code": "09",
				"phone_number": 9876543231,
				"email": ""
			},
			"buyer_details": {
				"gstin": "XXXXXXXXXXXXXXX",
				"legal_name": "ewewewehyriu32y8ru",
				"trade_name": "122",
				"address1": "9",
				"address2": "17843",
				"location": "12321u",
				"pincode": 263001,
				"place_of_supply": "13",
				"state_code": "05",
				"phone_number": "123233",
				"email": ""
			},
			"dispatch_details": {
				"company_name": "dqfefkewl",
				"address1": "Vila",
				"address2": "Vila",
				"location": "Noida",
				"pincode": 201301,
				"state_code": "09"
			},
			"ship_details": {
				"gstin": "XXXXXXXXXXXXXXX",
				"legal_name": "123",
				"trade_name": "232",
				"address1": "1",
				"address2": "",
				"location": "221",
				"pincode": 263001,
				"state_code": "5"
			},
			"export_details": {
				"ship_bill_number": "",
				"ship_bill_date": "",
				"country_code": "IN",
				"foreign_currency": "inr",
				"refund_claim": "N",
				"port_code": "12",
				"export_duty": "90.9"
			},
			"payment_details": {
				"bank_account_number": "Account Details",
				"paid_balance_amount": "100.2",
				"credit_days": 2,
				"credit_transfer": "Credit Transfer",
				"direct_debit": "Direct Debit",
				"branch_or_ifsc": "KKK000180",
				"payment_mode": "CASH",
				"payee_name": "Payee Name",
				"outstanding_amount": "1.9",
				"payment_instruction": "Payment Instruction",
				"payment_term": "Terms of Payment"
			},
			"reference_details": {
				"invoice_remarks": "Invoice Remarks",
				"document_period_details": {
					"invoice_period_start_date": "07/03/2020",
					"invoice_period_end_date": "07/03/2020"
				},
				"preceding_document_details": [{
					"reference_of_original_invoice": "CFRT/0006",
					"preceding_invoice_date": "07/03/2020",
					"other_reference": "2334"
				}],
				"contract_details": [{
					"receipt_advice_number": "aaa",
					"receipt_advice_date": "07/03/2020",
					"batch_reference_number": "2334",
					"contract_reference_number": "2334",
					"other_reference": "2334",
					"project_reference_number": "2334",
					"vendor_po_reference_number": "233433454545",
					"vendor_po_reference_date": "07/02/2020"
				}]
			},
			"additional_document_details": [{
				"supporting_document_url": "asafsd",
				"supporting_document": "india",
				"additional_information": "india"
			}],
			"ewaybill_details": {
				"transporter_id": "XXXXXXXXXXXXXXX",
				"transporter_name": "Jay Trans",
				"transportation_mode": "1",
				"transportation_distance": 4000,
				"transporter_document_number": "1230",
				"transporter_document_date": "23/06/2023",
				"vehicle_number": "PQR1234",
				"vehicle_type": "R"
			},
			"value_details": {
				"total_assessable_value": 4,
				"total_cgst_value": "",
				"total_sgst_value": 0,
				"total_igst_value": 0.2,
				"total_cess_value": 0,
				"total_cess_value_of_state": 0,
				"total_discount": 0,
				"total_other_charge": 0,
				"total_invoice_value": 4.2,
				"round_off_amount": 0,
				"total_invoice_value_additional_currency": 0
			},
			"item_list": [{
				"item_serial_number": "501",
				"product_description": "Wheat desc",
				"is_service": "N",
				"hsn_code": "1001",
				"bar_code": "1212",
				"quantity": 1,
				"free_quantity": 0,
				"unit": "KGS",
				"unit_price": 4,
				"total_amount": 4,
				"pre_tax_value": 0,
				"discount": 0,
				"other_charge": 0,
				"assessable_value": 4,
				"gst_rate": 5,
				"igst_amount": 0.2,
				"cgst_amount": 0,
				"sgst_amount": 0,
				"cess_rate": 0,
				"cess_amount": 0,
				"cess_nonadvol_amount": 0,
				"state_cess_rate": 0,
				"state_cess_amount": 0,
				"state_cess_nonadvol_amount": 0,
				"total_item_value": 4.2,
				"country_origin": "",
				"order_line_reference": "",
				"product_serial_number": "",
				"batch_details": {
					"name": "aaa",
					"expiry_date": "31/10/2020",
					"warranty_date": "31/10/2020"
				},
				"attribute_details": [{
					"item_attribute_details": "aaa",
					"item_attribute_value": "147852"
				}]
			}]
		}


	]
}
```

### Response Body

#### 200 (OK)

The response description for the API can be found [here](https://prepro-router.mastersindia.co/api/v1/redoc/#tag/Einvoice/operation/generate_irn_in_bulk_api_v_1_einvoiceGenerateInBulk__post).

#### Sample Response

```json
{
  "results": {
    "status": "Success",
    "message": "Request has been accepted.Please get response by the request id",
    "code": 200,
    "request_status": "pending",
    "requestId": "edoc_1694681573_7a4d44de71b7"
  }
}
```

#### 204 (Invalid Parameters)

```json
{
  "error": "invalid_request",
  "error_description": "Invalid Json Structure."
}
```
