# Employee Management System (EMS) API


## Overview
Employee Management System (EMS) is a backend API built to support day-to-day HR operations in organizations of different sizes.

It centralizes employee records, department structures, payroll history, leave workflows, attendance tracking, and operational reporting in a single system.

The project exists to provide a stable foundation for HR products that need clear business rules, predictable API contracts, and maintainable code over time.

Typical use cases include internal admin dashboards, manager workspaces, and employee self-service portals.

## Architecture
The codebase follows a strict Clean Architecture flow:

Controller -> Service -> Repository -> Model -> Resource

Layer responsibilities:
- Controller: Handles HTTP concerns only (requests, responses, authorization boundaries).
- Service: Implements business rules and use-case workflows.
- Repository: Encapsulates database access and query composition.
- Model: Defines persistence structure, casts, and relationships.
- Resource: Shapes API output into consistent response payloads.

This structure keeps business logic out of controllers, prevents data-access leakage, and makes the system easier to test and extend.

## Features

### Authentication
- JWT-based authentication with register, login, logout, refresh, and current-user endpoints.
- Stateless token flow suitable for SPA and mobile clients.

### Users
- Admin-oriented user management with role assignment and account status handling.
- Paginated listing with database-level filtering.

### Employees
- Employee profile lifecycle management linked to departments and system users.
- Automatic employee code generation and assignment validations.

### Departments
- Department CRUD with manager linkage and employee counts.
- Query-friendly listing and detail retrieval.

### Salaries
- Salary record management with amount, bonuses, deductions, and computed net salary.
- Historical salary retrieval by employee and date range.

### Leaves
- Leave application and approval lifecycle (pending, approved, rejected, cancelled).
- Overlap prevention for active leave windows.

### Attendance
- Daily check-in and check-out workflows with status tracking.
- Employee-level attendance history and filtered reporting.

### Reports
- Operational summaries for workforce, departments, attendance, salary distribution, and leaves.
- Dashboard-oriented aggregates built through database queries.

## Key Strengths
- Separation of concerns: each layer has a single, clear responsibility.
- Scalability: modular structure supports feature growth without controller bloat.
- Optimized queries: filtering, grouping, and pagination are handled at database level.
- Reliable business logic: domain rules are centralized in service classes.
- Structured responses: endpoints return a unified and predictable API envelope.

## API Design
All endpoints follow a consistent response contract.

Success response:

```json
{
  "success": true,
  "message": "string",
  "data": {},
  "meta": {}
}
```

Error response:

```json
{
  "success": false,
  "message": "string",
  "errors": {},
  "code": "ERROR_CODE"
}
```

This consistency simplifies frontend integration, improves client-side error handling, and keeps API behavior predictable across modules.

## Setup & Installation

```bash
git clone <repository-url>
cd task3-project:Employee_Management_System/backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Notes
- This project is a backend API and does not include a production frontend.
- The API is ready to be integrated with web, mobile, or admin panel clients.
