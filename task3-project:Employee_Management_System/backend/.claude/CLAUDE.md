# EMS Global Agent Instructions

## Absolute Source of Truth

- Follow AGENTS.md as the highest in-project authority.
- Do not override AGENTS.md rules.
- If a requested change conflicts with AGENTS.md, ask before proceeding.

## Engineering Standards

- Build production-level code only.
- No shortcuts that bypass architecture or validation.
- Enforce clean architecture and strict layering.
- Keep controllers thin and service-driven.

## Mandatory Architecture and API Rules

- Flow must be Controller -> Service -> Repository -> Model.
- Always use Resources for API output.
- Never return raw models.
- Always return standardized success/error response envelopes.

## Validation, Auth, and Authorization

- Always validate inputs using FormRequest.
- JWT auth is mandatory and stateless.
- Enforce roles: admin, manager, employee.
- Apply authorization checks for protected operations.

## Logging and Performance

- Log required business/security events per AGENTS.md.
- Apply performance-first development:
	- DB filtering only
	- pagination
	- eager loading
	- index awareness

## Feature Definition of Done

1. FormRequest
2. Service
3. Repository
4. Resource
5. Controller
6. Routes
7. Postman
8. Tests
