# EMS API Rules

## Mandatory Response Standard

Success response:

{
  "success": true,
  "message": "string",
  "data": {},
  "meta": {}
}

Error response:

{
  "success": false,
  "message": "string",
  "errors": {},
  "code": "ERROR_CODE"
}

## Response Enforcement

- All endpoints must follow the same envelope.
- Data must contain Resource-transformed payload only.
- Meta is used for pagination and response context.
- Errors must be validation/domain-safe and non-sensitive.
- Never leak stack traces or raw internal exception details.

## Resource and DTO Boundary

- Never expose raw Eloquent models in responses.
- Use Resources for output formatting always.
- Use DTOs in service boundaries when use-case data shaping is needed.

## Error Handling Rules

- Validation failures must return standardized error envelope.
- Authorization failures must return standardized error envelope.
- Domain rule violations must return standardized error envelope.
