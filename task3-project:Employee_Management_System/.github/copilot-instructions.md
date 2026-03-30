# EMS Copilot Instructions

## Source of Truth

- AGENTS.md is the single source of truth for EMS.
- Copilot must follow AGENTS.md in every response and code generation task.
- If a request conflicts with AGENTS.md, ask before proceeding.

## Project Context

- Project: Employee Management System (EMS)
- Type: RESTful API (Laravel)
- Architecture: Modular Monolith (Clean Architecture)
- Database: MySQL (Eloquent ORM)
- Auth: JWT (tymon/jwt-auth)

## Architecture Rules (Critical)

- Mandatory flow: Controller -> Service -> Repository -> Model
- Controller responsibility: request/response only
- Service responsibility: business logic only
- Repository responsibility: data access/query logic only
- Model responsibility: Eloquent persistence and relations
- Resource responsibility: API formatting

### Strict Prohibitions

- No business logic in controllers
- No DB queries in controllers
- No raw Eloquent models in API responses
- No architecture layer skipping

## API Response Standard (Mandatory)

Success:
{
  "success": true,
  "message": "string",
  "data": {},
  "meta": {}
}

Error:
{
  "success": false,
  "message": "string",
  "errors": {},
  "code": "ERROR_CODE"
}

### API Enforcement

- Always return the standardized envelope.
- Always format output with Resources.
- Never expose internal exceptions or sensitive internals.

## Auth Rules

- JWT only
- Stateless authentication
- Token blacklist on logout
- Rate limit login

Roles:
- admin
- manager
- employee

## Development Workflow (Definition of Done)

For every feature, follow exactly:

1. FormRequest
2. Service
3. Repository
4. Resource
5. Controller
6. Routes
7. Postman
8. Tests

Feature is incomplete if any step is skipped.

README maintenance is mandatory for every completed feature:

- Update README after each completed feature.
- README updates must be append-only (never rewrite the full README).
- Update both:
  - Features section
  - Project Status section
- A feature is NOT complete without README update.

## Performance Rules (Very Important)

- DB filtering only (never collection filtering)
- Use pagination for list endpoints
- Avoid N+1 queries
- Use eager loading where relations are required
- Index frequently used filter/sort/join columns

## Logging Rules

- Log login event with exact message:
  "We are logged in to the EMS OSP"
- Log user creation events
- Log leave approval events
- Log salary update events

## Behavior Rules

Copilot must:

- Follow AGENTS.md always
- Never generate quick hacks
- Always generate production-level code
- Always include validation
- Always respect architecture
- Never skip layers
- Always optimize queries
- Always update Postman for endpoint changes
- Always update README after every completed feature

## Code Generation Rules

Whenever generating backend feature code:

- Use FormRequest for validation
- Use Service layer for business logic
- Use Repository for DB access
- Use Resource for response formatting
- Use clean, explicit naming
- Avoid duplication
- Keep implementation maintainable and testable
