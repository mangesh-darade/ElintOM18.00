> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/einvoicing/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/einvoicing/get-irn-details-by-doc-details.md).

# Get IRN Details By Doc Details

### Request Method

GET

### Request Path

```
{{API_URL}}/api/v1/get-einvoice-bydoc/
```

### Request Headers

<table><thead><tr><th>Parameter</th><th width="184">Value</th><th>Description</th></tr></thead><tbody><tr><td>Authorization/api_key</td><td>api_key/auth token</td><td>Value and header type is mentioned in the Authentication Page</td></tr></tbody></table>

### Request Params

| Parameter        | Value           | Description                     |
| ---------------- | --------------- | ------------------------------- |
| user\_gstin      | XXXXXXXXXXXXXXX | GSTIN of the authenticated user |
| document\_type   | INV             | Document type of invoice        |
| document\_number | TATA/99027      | Document number of invoice      |
| document\_date   | 26/10/2023      | Document date of invoice        |

### Response Body

#### 200 (OK)

#### Sample Response

```json
{
  "results": {
    "message": {
      "AckNo": 142310015934410,
      "AckDt": "2023-09-14 13:35:00",
      "Irn": "dd609070d4f3f66f1585fc13abd52e8f47d4102c8389bb9f7411afe52a9e778a",
      "SignedInvoice": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjE1MTNCODIxRUU0NkM3NDlBNjNCODZFMzE4QkY3MTEwOTkyODdEMUYiLCJ4NXQiOiJGUk80SWU1R3gwbW1PNGJqR0w5eEVKa29mUjgiLCJ0eXAiOiJKV1QifQ.eyJkYXRhIjoie1wiQWNrTm9cIjoxNDIzMTAwMTU5MzQ0MTAsXCJBY2tEdFwiOlwiMjAyMy0wOS0xNCAxMzozNToyOVwiLFwiSXJuXCI6XCJkZDYwOTA3MGQ0ZjNmNjZmMTU4NWZjMTNhYmQ1MmU4ZjQ3ZDQxMDJjODM4OWJiOWY3NDExYWZlNTJhOWU3NzhhXCIsXCJWZXJzaW9uXCI6XCIxLjFcIixcIlRyYW5EdGxzXCI6e1wiVGF4U2NoXCI6XCJHU1RcIixcIlN1cFR5cFwiOlwiQjJCXCIsXCJSZWdSZXZcIjpcIllcIixcIklnc3RPbkludHJhXCI6XCJOXCJ9LFwiRG9jRHRsc1wiOntcIlR5cFwiOlwiSU5WXCIsXCJOb1wiOlwiVEFUQS85OTAyN1wiLFwiRHRcIjpcIjE0LzA5LzIwMjNcIn0sXCJTZWxsZXJEdGxzXCI6e1wiR3N0aW5cIjpcIjA5QUFBUEc3ODg1UjAwMlwiLFwiTGdsTm1cIjpcIk1hc3RlcnNJbmRpYSBVUFwiLFwiVHJkTm1cIjpcIjEyM1wiLFwiQWRkcjFcIjpcIjQ1XCIsXCJBZGRyMlwiOlwibDQ1XCIsXCJMb2NcIjpcIjEyNzlcIixcIlBpblwiOjIwMTMwMSxcIlN0Y2RcIjpcIjA5XCIsXCJQaFwiOlwiOTg3NjU0MzIzMVwifSxcIkJ1eWVyRHRsc1wiOntcIkdzdGluXCI6XCIwNUFBQVBHNzg4NVIwMDJcIixcIkxnbE5tXCI6XCJld2V3ZXdlaHlyaXUzMnk4cnVcIixcIlRyZE5tXCI6XCIxMjJcIixcIlBvc1wiOlwiMTNcIixcIkFkZHIxXCI6XCI5XCIsXCJBZGRyMlwiOlwiMTc4NDNcIixcIkxvY1wiOlwiMTIzMjF1XCIsXCJQaW5cIjoyNjMwMDEsXCJQaFwiOlwiMTIzMjMzXCIsXCJTdGNkXCI6XCIwNVwifSxcIkl0ZW1MaXN0XCI6W3tcIkl0ZW1Ob1wiOjAsXCJTbE5vXCI6XCI1MDFcIixcIklzU2VydmNcIjpcIk5cIixcIlByZERlc2NcIjpcIldoZWF0IGRlc2NcIixcIkhzbkNkXCI6XCIxMDAxXCIsXCJCYXJjZGVcIjpcIjEyMTJcIixcIlF0eVwiOjEuMCxcIkZyZWVRdHlcIjowLjAsXCJVbml0XCI6XCJLR1NcIixcIlVuaXRQcmljZVwiOjQuMCxcIlRvdEFtdFwiOjQuMCxcIkRpc2NvdW50XCI6MC4wLFwiUHJlVGF4VmFsXCI6MC4wLFwiQXNzQW10XCI6NC4wLFwiR3N0UnRcIjo1LjAsXCJJZ3N0QW10XCI6MC4yLFwiQ2dzdEFtdFwiOjAuMCxcIlNnc3RBbXRcIjowLjAsXCJDZXNSdFwiOjAuMCxcIkNlc0FtdFwiOjAuMCxcIkNlc05vbkFkdmxBbXRcIjowLjAsXCJTdGF0ZUNlc1J0XCI6MC4wLFwiU3RhdGVDZXNBbXRcIjowLjAsXCJTdGF0ZUNlc05vbkFkdmxBbXRcIjowLjAsXCJPdGhDaHJnXCI6MC4wLFwiVG90SXRlbVZhbFwiOjQuMixcIkJjaER0bHNcIjp7XCJObVwiOlwiYWFhXCIsXCJFeHBEdFwiOlwiMzEvMTAvMjAyMFwiLFwiV3JEdFwiOlwiMzEvMTAvMjAyMFwifSxcIkF0dHJpYkR0bHNcIjpbe1wiTm1cIjpcImFhYVwiLFwiVmFsXCI6XCIxNDc4NTJcIn1dfV0sXCJWYWxEdGxzXCI6e1wiQXNzVmFsXCI6NC4wLFwiQ2dzdFZhbFwiOjAuMCxcIlNnc3RWYWxcIjowLjAsXCJJZ3N0VmFsXCI6MC4yLFwiQ2VzVmFsXCI6MC4wLFwiU3RDZXNWYWxcIjowLjAsXCJEaXNjb3VudFwiOjAuMCxcIk90aENocmdcIjowLjAsXCJSbmRPZmZBbXRcIjowLjAsXCJUb3RJbnZWYWxcIjo0LjIsXCJUb3RJbnZWYWxGY1wiOjAuMH0sXCJQYXlEdGxzXCI6e1wiTm1cIjpcIlBheWVlIE5hbWVcIixcIkFjY0RldFwiOlwiQWNjb3VudCBEZXRhaWxzXCIsXCJNb2RlXCI6XCJDQVNIXCIsXCJGaW5JbnNCclwiOlwiS0tLMDAwMTgwXCIsXCJQYXlUZXJtXCI6XCJUZXJtcyBvZiBQYXltZW50XCIsXCJQYXlJbnN0clwiOlwiUGF5bWVudCBJbnN0cnVjdGlvblwiLFwiQ3JUcm5cIjpcIkNyZWRpdCBUcmFuc2ZlclwiLFwiRGlyRHJcIjpcIkRpcmVjdCBEZWJpdFwiLFwiQ3JEYXlcIjoyLFwiUGFpZEFtdFwiOjEwMC4yLFwiUGF5bXREdWVcIjoxLjl9LFwiUmVmRHRsc1wiOntcIkludlJtXCI6XCJJbnZvaWNlIFJlbWFya3NcIixcIkRvY1BlcmREdGxzXCI6e1wiSW52U3REdFwiOlwiMDcvMDMvMjAyMFwiLFwiSW52RW5kRHRcIjpcIjA3LzAzLzIwMjBcIn0sXCJQcmVjRG9jRHRsc1wiOlt7XCJJbnZOb1wiOlwiQ0ZSVC8wMDA2XCIsXCJJbnZEdFwiOlwiMDcvMDMvMjAyMFwiLFwiT3RoUmVmTm9cIjpcIjIzMzRcIn1dLFwiQ29udHJEdGxzXCI6W3tcIlJlY0FkdlJlZnJcIjpcImFhYVwiLFwiUmVjQWR2RHRcIjpcIjA3LzAzLzIwMjBcIixcIlRlbmRSZWZyXCI6XCIyMzM0XCIsXCJDb250clJlZnJcIjpcIjIzMzRcIixcIkV4dFJlZnJcIjpcIjIzMzRcIixcIlByb2pSZWZyXCI6XCIyMzM0XCIsXCJQT1JlZnJcIjpcIjIzMzQzMzQ1NDU0NVwiLFwiUE9SZWZEdFwiOlwiMDcvMDIvMjAyMFwifV19LFwiQWRkbERvY0R0bHNcIjpbe1wiVXJsXCI6XCJhc2Fmc2RcIixcIkRvY3NcIjpcImluZGlhXCIsXCJJbmZvXCI6XCJpbmRpYVwifV0sXCJFeHBEdGxzXCI6e1wiUG9ydFwiOlwiMTJcIixcIlJlZkNsbVwiOlwiTlwiLFwiRm9yQ3VyXCI6XCJpbnJcIixcIkNudENvZGVcIjpcIklOXCIsXCJFeHBEdXR5XCI6OTAuOX0sXCJFd2JEdGxzXCI6e1wiVHJhbnNJZFwiOlwiMDVBQUFCQjA2MzlHMVo4XCIsXCJUcmFuc05hbWVcIjpcIkpheSBUcmFuc1wiLFwiVHJhbnNNb2RlXCI6XCIxXCIsXCJEaXN0YW5jZVwiOjI5NixcIlRyYW5zRG9jTm9cIjpcIjEyMzAxXCIsXCJUcmFuc0RvY0R0XCI6XCIxNC8wNy8yMDIzXCIsXCJWZWhOb1wiOlwiUFFSMTIzNFwiLFwiVmVoVHlwZVwiOlwiUlwifX0iLCJpc3MiOiJOSUMgU2FuZGJveCJ9.S5nfWvmDhYCSE8QlrtVH8ivUTE6xNNzcXhO3vTRzVbJqIM6vLNAbYHb5hz_eRlBM32sOQHET4kZzNU0M6KeasYGM8YWIwqhvaZbVkqRRIEJT0oLT8lGY3s0MyFjuVrS_f43IhmXqpTBNzVLqqq6nAqqBrq-sHlVJoklDMss1fEdGiJMPkXdqsLZ7XlF3DunOBS9M4ojZodYC5B6JHknMvF645m-11P2SYMFYVe5INQuraBY2cY6wPdtlB0wztjXIZwMtvpAPZgxYUbbU4y3T9oKBkeSnujOwAd7qIMyRn-x0B8WAf9f0LBprCqT50mc3IJoqFWFiyajEPDeE33kBoA",
      "SignedQRCode": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjE1MTNCODIxRUU0NkM3NDlBNjNCODZFMzE4QkY3MTEwOTkyODdEMUYiLCJ4NXQiOiJGUk80SWU1R3gwbW1PNGJqR0w5eEVKa29mUjgiLCJ0eXAiOiJKV1QifQ.eyJkYXRhIjoie1wiU2VsbGVyR3N0aW5cIjpcIjA5QUFBUEc3ODg1UjAwMlwiLFwiQnV5ZXJHc3RpblwiOlwiMDVBQUFQRzc4ODVSMDAyXCIsXCJEb2NOb1wiOlwiVEFUQS85OTAyN1wiLFwiRG9jVHlwXCI6XCJJTlZcIixcIkRvY0R0XCI6XCIxNC8wOS8yMDIzXCIsXCJUb3RJbnZWYWxcIjo0LjIsXCJJdGVtQ250XCI6MSxcIk1haW5Ic25Db2RlXCI6XCIxMDAxXCIsXCJJcm5cIjpcImRkNjA5MDcwZDRmM2Y2NmYxNTg1ZmMxM2FiZDUyZThmNDdkNDEwMmM4Mzg5YmI5Zjc0MTFhZmU1MmE5ZTc3OGFcIixcIklybkR0XCI6XCIyMDIzLTA5LTE0IDEzOjM1OjI5XCJ9IiwiaXNzIjoiTklDIFNhbmRib3gifQ.jClkVwfefxkUa5f4dY9aREmbosIsOWCH-70ZYWiu51RT7msowBZkeijxnGjnfLg2LFy0u-mAK0aKUvYeQntKFA3VxqSXsJjHUNMyei4PgJAJZILhzKwZyTJObeOXqDyjty9Et2Ao2idiu73LjyojbNmOBYfxfTNkGch8S8zemBmESwN70XGkB3i26_fm-0ijmwvbJktOos1yknqC6lQiYwnoU7IXo2kSpV7o9c01qRdwCxKxVe7T8YeWrhPVYyT_wImLk3S03Jw-sBtP4om6g7StZOCNYsXi_24XBD6xWI6UH0Z6zkwWdNYfw4Eq2VXtCT_3BUgVty4QMkzCNkpBXA",
      "Status": "ACT",
      "EwbNo": 401008880911,
      "EwbDt": "2023-09-14 13:36:00",
      "EwbValidTill": "2023-09-16 23:59:00",
      "Remarks": null
    },
    "errorMessage": "",
    "InfoDtls": "",
    "status": "Success",
    "code": 200
  }
}
```

#### 204 (Invalid Parameters)

```json
{
  "results": {
    "message": "",
    "errorMessage": "2154: IRN details are not found",
    "InfoDtls": "",
    "status": "Failed",
    "code": 204
  }
}
```
