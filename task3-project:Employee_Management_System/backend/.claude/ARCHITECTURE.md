# EMS Architecture Rules

## Architecture Style

- Modular Monolith (Clean Architecture).
- No microservice split.

## Mandatory Layer Flow

Controller -> Service -> Repository -> Model

## Layer Responsibilities

- Controller: request validation handoff, authorization trigger, response output only.
- Service: business rules and use-case orchestration.
- Repository: database access and query composition.
- Model: Eloquent persistence model and relations.
- Resource: API formatting and output shape.

## Non-Negotiable Rules

- No business logic in controllers.
- No DB queries in controllers.
- No raw Eloquent models in API responses.
- Always return Resources inside standardized API envelope.
- Always keep Service layer between Controller and Repository.

## Data Integrity Rules

- Prefer UUID primary keys.
- Keep strict foreign key constraints.
- Relations must not be broken.
- No schema changes without approval.

## Query and Performance Rules

- Filter and paginate at database level.
- Avoid N+1 queries with eager loading.
- Index frequently used columns for filtering and sorting.
