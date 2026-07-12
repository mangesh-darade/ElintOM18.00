> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/introduction.md).

# Introduction

**Background**

The introduction of Goods and Services Tax (GST) across India with effect from 1st of July 2017 is a very significant step in the field of indirect tax reforms in India. For quick and easy movement of goods across India without any hindrance, all the check posts across the country are abolished.&#x20;

The GST system provides a provision of an e-Way Bill, a document to be carried by the person in charge of conveyance, generated electronically from the common portal. To implement the e-Way Bill system, an ICT-based solution is required. Hence, as approved by the Goods and Services Tax (GST) Council, a web-based has been designed and developed by the National Informatics Centre and it is being rolled out for the use of taxpayers and transporters.  Also, the other modes of e-way bill generation rolled out are SMS-based, Android-based, and API-based solutions.

**Purpose and Intended Audience**

This document aims to explain the operational and technical procedure on how to use the API interface to generate e-Way Bills by Taxpayers or Transporters.

\
This document is intended for technical experts or software solutions providers of taxpayers or transporters, who are already using a computerized system for generating invoices and also generating a large number of invoices.  The best method of EWB generation for such large taxpayers, who generate a large number of e-way Bills, is to build an API interface with the E-way bill system. This is site-to-site integration of the systems for e-way Bill generation.&#x20;

In this method, the taxpayer system will directly or through GSP request an e-way bill to the E-way Bill system while generating an invoice and getting the e-way Bill number. This can be printed on the Invoice document and movement of the goods can be started. This avoids duplicate data entry and eliminates data entry mistakes. To use this facility, the taxpayers have to request the online for this service.

**Scope**

This document covers APIs published by the E-way bill system. It includes an API description with detailed payloads to be exchanged. The details of various APIs for Authentication, E-way bill generation, Consolidated E-way bill generation, Vehicle No. updation, Cancellation of e-way bill, and Rejection of e-way bill are explained in detail along with sample source code in C# .Net for better understanding for the taxpayers and transporters.

\
**This document also includes**

* Interface Business Flow Process
* API format and brief details on key payload parameters.
* The attribute level description of each API request and response payload.
* JSON schema and sample JSON payload for respective APIs.
* API data structure Specification
* Various Master codes and error codes are listed in the Annexures
* JSON Schema
