# EMS Auth Rules

## Authentication Stack

- JWT only (tymon/jwt-auth).
- Bearer token authentication.
- Stateless auth flow.

## Mandatory Security Rules

- Login must be rate-limited.
- Logout must invalidate/blacklist the current token.
- Token refresh flow must be implemented.
- Never expose sensitive auth failure internals.

## Role Model

- Allowed roles:
	- admin
	- manager
	- employee

## Role Authorization Baseline

- admin: full platform access, including users and governance flows.
- manager: department/employee operational management based on assigned scope.
- employee: self-service access and allowed personal operations only.

## Authorization Rules

- Enforce role checks in policies/guards/middleware.
- Deny by default if role permission is undefined.
- Return standardized error envelope for unauthorized actions.
