> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/authentication.md).

# Authentication

{% content-ref url="/pages/QTBvBTZKdlcFPIxYx7zm" %}
[Access Tokens](/eway/authentication/access-tokens.md)
{% endcontent-ref %}

{% content-ref url="/pages/Rp40nFRaenlHc1eT9D6e" %}
[API KEY](/eway/authentication/api-key.md)
{% endcontent-ref %}

## Authentication Header

### Access Token&#x20;

If you have chosen the access token approach then for authentication to work you will need to pass the token in request **headers** in the following manner

| Paramater     | Value                    | Description                                                                                              |
| ------------- | ------------------------ | -------------------------------------------------------------------------------------------------------- |
| Authorization | JWT **\<access\_token>** | **JWT** remains constant following by a space and then the access token received from the associated API |

### API Key

If you have chosen the API key approach then for authentication to work you will need to pass the token in request **headers** in the following manner

| Paramater | Value       | Description                                                |
| --------- | ----------- | ---------------------------------------------------------- |
| api\_key  | \<api\_key> | Pass the API key generated from the UI as is in the header |
