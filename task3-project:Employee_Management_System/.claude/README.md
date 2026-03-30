# EMS AI Memory Layer

This directory is the persistent project memory layer for AI agents and developers working on EMS.

## Source of Truth

- AGENTS.md is the single source of truth.
- All files in this folder must stay aligned with AGENTS.md.

## Files

- PROJECT_CONTEXT.md: project identity, domain, and current phase
- ARCHITECTURE.md: clean architecture contracts and layering rules
- API_RULES.md: response envelope and API consistency rules
- AUTH_RULES.md: JWT and role authorization rules
- DTO_RULES.md: DTO vs Resource boundaries
- POSTMAN_RULES.md: API collection maintenance requirements
- DEV_WORKFLOW.md: mandatory feature sequence and Definition of Done
- AI_BEHAVIOR.md: hard guardrails for AI-assisted development
- CLAUDE.md: global execution instructions for AI agents

## Purpose

Keep implementation behavior consistent, production-grade, and architecture-safe without repeated prompting.