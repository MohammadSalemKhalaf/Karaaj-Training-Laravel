# EMS Project Context

## Identity

- Project: Employee Management System (EMS)
- Type: RESTful API (Laravel)
- Architecture: Modular Monolith (Clean Architecture)
- Database: MySQL (Eloquent ORM)
- Auth: JWT (tymon/jwt-auth)

## Current Status

- Current Phase: Database Design
- Completed: None
- Next Step: Design database schema (employees, users, departments, salaries, leaves, attendance)

## Domain Scope

- Users and Roles
- Employees
- Departments
- Salaries
- Leaves
- Attendance
- Reporting and Analytics

## Execution Roadmap

- Phase 1: Auth + Roles
- Phase 2: Users
- Phase 3: Employees + Departments
- Phase 4: Salary + Leaves + Attendance
- Phase 5: Reports + Analytics
- Phase 6: Optimization + Caching

## Core Enforcement Snapshot

- Use Controller -> Service -> Repository -> Model.
- Use Resources for API formatting.
- Keep API response envelope standardized.
- Enforce validation and authorization on all feature paths.
