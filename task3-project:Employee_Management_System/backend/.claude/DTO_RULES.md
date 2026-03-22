# EMS DTO and Resource Rules

## DTO vs Resource

- DTO: internal application contract between layers (primarily Service boundary).
- Resource: external API representation contract for clients.

## When to Use DTO

- Use DTOs for business-use-case output/input shaping.
- Use DTOs when data must be decoupled from Eloquent models.
- Use DTOs to keep service logic explicit and type-safe.

## When to Use Resource

- Use Resources for every API response payload.
- Resources format public fields and enforce response consistency.

## Mandatory Boundary Rules

- Never return models directly from controllers.
- Never expose raw model attributes without Resource filtering.
- Controller output must always be Resource-based in standardized API envelope.
- Services should return DTOs or DTO-ready arrays, not raw response objects.

## Sensitive Data Policy

Never expose:

- password
- remember_token
- auth internals
- hidden/internal-only fields not required by API consumers
