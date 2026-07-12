> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/einvoicing/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/einvoicing/authentication.md).

# Authentication

{% content-ref url="/pages/ydGfRwZrOOZ3LttD63es" %}
[Access Tokens](/einvoicing/authentication/access-tokens.md)
{% endcontent-ref %}

{% content-ref url="/pages/CAb29kKnZZ2azxQZMItY" %}
[API KEY](/einvoicing/authentication/api-key.md)
{% endcontent-ref %}

## Authentication Header

### Access Token&#x20;

If you have chosen the access token approach then for authentication to work you will need to pass the token in request **headers** in the following manner

<table><thead><tr><th>Paramater</th><th width="232.33333333333331">Value</th><th>Description</th></tr></thead><tbody><tr><td>Authorization</td><td>JWT <strong>&#x3C;access_token></strong></td><td><strong>JWT</strong> remains constant following by a space and then the access token received from the associated API</td></tr></tbody></table>

### API Key

If you have chosen the API key approach then for authentication to work you will need to pass the token in request **headers** in the following manner

| Paramater | Value       | Description                                                |
| --------- | ----------- | ---------------------------------------------------------- |
| api\_key  | \<api\_key> | Pass the API key generated from the UI as is in the header |
