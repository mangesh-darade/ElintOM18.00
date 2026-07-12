> For the complete documentation index, see [llms.txt](https://docs.mastersindia.co/eway/llms.txt). Markdown versions of documentation pages are available by appending `.md` to page URLs; this page is available as [Markdown](https://docs.mastersindia.co/eway/authentication/access-tokens.md).

# Access Tokens

Masters India supports access-token-based authentication. After a user login request, our API generates an access\_token and a refresh token to a user after validating his credentials.&#x20;

### Access Token

The Access token key can be used in subsequent requests but expires after 24 hours which must be requested again by re-initiating a login or call the token-refresh API. The Access token once generated is valid for 24 hours and the refresh token is valid for many days. This token need to be passed in the subsequent requests to avoid permission issues.

The key needs to be passed as a request parameter. To generate the Authorization token by clicking on the Send button. Once the Token Is generated. We need to the token in **Authorization** header with the prefix **JWT** so the token will be passed as **JWT \<token>**

## Get Access token API

### Request Method

POST

### Request Path

```
{{API_URL}}/api/v1/token-auth/
```

### Request Body

| Parameter | Value        | Description                |
| --------- | ------------ | -------------------------- |
| username  | sample\_user | username                   |
| password  | 1234Pass     | password of the given user |

### Response Body

#### 200 (OK)

| Parameter | Value | Description                   |
| --------- | ----- | ----------------------------- |
| token     | ''    | access token (24 hr validity) |

#### 206 (Invalid Credentials)

| Parameter | Value                                      | Description |
| --------- | ------------------------------------------ | ----------- |
| error     | "Unable to login with provided credential" |             |

#### 400 (Invalid Params)

| Parameter | Value              | Description |
| --------- | ------------------ | ----------- |
| username  | \['missing field'] |             |
