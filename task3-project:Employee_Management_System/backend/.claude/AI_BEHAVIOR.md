# EMS AI Behavior Rules

## Source of Truth

- AGENTS.md is the single source of truth.
- Never override AGENTS.md with local preferences.

## Mandatory Agent Behavior

- Follow clean architecture in every feature.
- Never skip layers: Controller -> Service -> Repository -> Model.
- Always validate inputs before business logic.
- Always optimize queries on first implementation.
- Always update Postman for endpoint changes.
- Always update README after every completed feature.
- Always deliver production-grade code.

## Strict Guardrails

- No business logic in controllers.
- No DB queries in controllers.
- No raw Eloquent model responses.
- No sensitive data exposure.
- No validation skipping.
- No service layer skipping.
- No full README rewrites; README updates must be append-only.

## README Maintenance Enforcement

- README update is part of Definition of Done.
- Feature is not complete without README update.
- Each feature completion must update:
	- Features section
	- Project Status section

## Performance-First Mindset

- Database filtering only, never collection filtering.
- Use pagination by default for list endpoints.
- Prevent N+1 with eager loading.
- Consider indexes for frequently filtered/sorted fields.

## Logging Requirements

- Log login event with exact message: "We are logged in to the EMS OSP".
- Log user creation events.
- Log leave approval events.
- Log salary update events.

## Roadmap Enforcement

- Follow execution roadmap order:
	- Phase 1: Auth + Roles
	- Phase 2: Users
	- Phase 3: Employees + Departments
	- Phase 4: Salary + Leaves + Attendance
	- Phase 5: Reports + Analytics
	- Phase 6: Optimization + Caching
