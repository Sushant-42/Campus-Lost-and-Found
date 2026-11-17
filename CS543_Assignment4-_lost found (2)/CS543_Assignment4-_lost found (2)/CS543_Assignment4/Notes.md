# CampusFind — Assignment 4 Notes

## A4. REST Resource Design

The Claims and Verification Service exposes claims as resources. The API uses HTTP methods to operate on these resources instead of placing action verbs in resource URLs.

| Method | URL | Purpose | Success code | Failure codes |
|---|---|---|---|---|
| GET | `/claims` | Retrieve claims, optionally filtered by status | 200 | 400, 500 |
| POST | `/claims` | Submit a new claim | 201 | 400, 409, 422, 503, 500 |
| GET | `/claims/{claimId}` | Retrieve a specific claim | 200 | 404, 500 |
| POST | `/claims/{claimId}/cancellation` | Cancel a pending claim | 200 | 404, 409, 500 |

## A5. Mapping the SOAP Operation to REST

The SOAP `verifyClaim()` operation represents a claim-verification action, whereas REST models claims as resources. In CampusFind, a client submits a claim using `POST /claims`, and the service checks the referenced item through the Item Service before creating the claim. The resulting claim can be retrieved through `GET /claims/{claimId}`, while cancellation is represented by the `/claims/{claimId}/cancellation` sub-resource. This resource-oriented approach makes the claim's lifecycle accessible through standard HTTP methods and resource URLs.

## D3. Dependency Failure and Fallback Strategy

The Claims Service calls the Item Service to confirm that the referenced item exists before creating a claim. If the Item Service remains unavailable after the configured timeout and retry attempts, the Claims Service returns `503 Service Unavailable` using the standard Problem Details response format. It does not create the claim because doing so without item verification could produce an invalid or inconsistent record.

## Assignment 3 and Assignment 4 Comparison

**1. Number of lines in WSDL and OpenAPI**

**WSDL vs OpenAPI line-count comparison**

The Assignment 3 WSDL contains **100 lines**, while the Assignment 4 OpenAPI YAML contains **250 lines**. The OpenAPI document is 150 lines longer because it describes multiple REST endpoints, HTTP methods, request and response schemas, parameters, status codes, and reusable error responses. The WSDL focuses on the SOAP service contract, its messages, operation, binding, and endpoint.

WSDL is an XML-based contract and generally contains more structural and protocol-specific declarations. OpenAPI uses a more concise YAML or JSON structure to describe HTTP resources, methods, parameters, request bodies, and responses.

**2. Two things WSDL declares that OpenAPI does not require in the same way**

- SOAP-specific bindings, including SOAP version, transport, and SOAP action information.
- XML message and operation definitions tied to SOAP envelopes and XML Schema types.

OpenAPI describes HTTP APIs directly and does not require SOAP envelopes or SOAP binding declarations.

**3. SOAP Fault mapping to REST Problem Details**

Assignment 3's fault identifier to use here: `CLAIM_VERIFICATION_UNAVAILABLE`.

- Original SOAP fault: **[Insert the exact `soap:Fault` XML from Assignment 3 here.]**
- REST status code: `503 Service Unavailable`
- REST response:

```json
{
  "type": "about:blank",
  "title": "Service Unavailable",
  "status": 503,
  "detail": "Claim verification service is currently unavailable."
}
```

Returning `200 OK` with an error in the response body would be misleading because HTTP clients would interpret the request as successful. The appropriate HTTP status code communicates the failure clearly and allows clients to handle it correctly.
