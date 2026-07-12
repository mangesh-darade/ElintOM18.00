> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/einvoicing/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/einvoicing/introduction.md).

# Introduction

**Background**

The GST Council has approved the implementation of ‘e-Invoicing’ or ‘electronic invoicing’ in a phased manner for reporting of Business to Business (B2B) invoices to GST System, starting from 1st January 2020 on a voluntary basis. Being the first Invoice Registration Portal (IRP), National Informatics Centre, has made the e-Invoice registration services available through API mode, in addition to other modes.&#x20;

The tax payers and GSPs can integrate their business systems and processes with the e-Invoice system through these APIs for seamless registration of the invoices, generated/prepared on their systems.

**Purpose of this portal**

This portal enables/provides:

* Developers or System integrators of the taxpayers to understand the interfacing processes of e-Invoicing system with their business systems.
* Registration of the users to access the APIs
  * **Credentials:** The portal explains how to get the credentials to access the APIs on **sandbox and production environments.** The credentials include **client-id, client-secret, username and password.**
* API Documentation
  * This portal provides all the information required by the application developers related to integrating their systems with the e-invoice system through APIs.
  * This includes the calling methods / URLs, JSON Schema of the request payloads, sample request payloads and sample responses, validations being applied, etc., for each of the APIs.
  * Sample code extracts, for reference, are provided for understanding the logic and concepts.
  * Other references like the master data used in the system etc. are provided.
* Understanding / Testing the API methods through the sandbox portal. Through this portal, the developers can simulate the use of APIs, end-to-end.
  * Can understand how the request payloads can be generated.
  * Can see how the encryption and decryption of the requests and responses work.
  * Can substitute the encrypted payload generated using his/her system and check for the correctness of encryption
  * By changing the parameter values in the request payloads, developers can test the successful (valid) and unsuccessful (invalid) responses for different combinations of values.
    * What will be the response if any of the credentials supplied are wrong?
    * What will be the error response when any of the mandatory parameters are missing in the payload?
    * What types of errors are displayed when the API fails?
    * What happens if the API is called without a valid token?
