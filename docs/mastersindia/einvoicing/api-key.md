# Page Not Found

The URL `api-key` does not exist. This page may have been moved, renamed, or deleted.

## Suggested Pages

You may be looking for one of the following:
- [API KEY](https://docs.mastersindia.co/einvoicing/authentication/api-key.md)
- [Cancel IRN](https://docs.mastersindia.co/einvoicing/cancel-irn.md)
- [Get IRN Details By IRN](https://docs.mastersindia.co/einvoicing/get-irn-details-by-irn.md)
- [Get e-Waybill Details by IRN](https://docs.mastersindia.co/einvoicing/get-e-waybill-details-by-irn.md)
- [Get Gstin Details](https://docs.mastersindia.co/einvoicing/get-gstin-details.md)

## How to find the correct page

If the exact page cannot be found, you can still retrieve the information using the documentation query interface.

### Option 1 — Ask a question (recommended)

Perform an HTTP GET request on the documentation index with the `ask` parameter, and the optional `goal` parameter:

```
GET https://docs.mastersindia.co/einvoicing/authentication/api-key.md?ask=<question>&goal=<end_goal>
```

`ask` is the immediate question: it should be specific, self-contained, and written in natural language.
`goal` is optional and describes the broader end goal you are ultimately trying to accomplish on behalf of the user. GitBook uses it to tailor the answer towards what is most useful for that goal.

The response will contain a direct answer to the question and relevant excerpts and sources from the documentation.

### Option 2 — Browse the documentation index

Full index: https://docs.mastersindia.co/einvoicing/sitemap.md

Use this to discover valid page paths or navigate the documentation structure.

### Option 3 — Retrieve the full documentation corpus

Full export: https://docs.mastersindia.co/einvoicing/llms-full.txt

Use this to access all content at once and perform your own parsing or retrieval. It will be more expensive.

## Tips for requesting documentation

Prefer `.md` URLs for structured content, append `.md` to URLs (e.g., `/einvoicing/authentication/api-key.md`).

You may also use `Accept: text/markdown` header for content negotiation.
