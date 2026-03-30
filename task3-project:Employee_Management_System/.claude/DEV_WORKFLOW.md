# EMS Development Workflow (Definition of Done)

For every feature, follow this exact sequence:

1. FormRequest
2. Service
3. Repository
4. Resource
5. Controller
6. Routes
7. Postman
8. Tests

## Definition of Done

- Feature is incomplete if any workflow step is skipped.
- Architecture rules must remain intact end-to-end.
- API response standard must be preserved.
- Validation, authorization, and edge-case tests are required.
- Postman update is mandatory for every new/changed endpoint.
- README update is mandatory after every completed feature.
- README updates are append-only; never rewrite entire README.
- README update must include:
	- Features section update
	- Project Status update
- A feature is NOT complete without README update.

## Required Testing Scope

- success
- validation
- authorization
- edge cases
