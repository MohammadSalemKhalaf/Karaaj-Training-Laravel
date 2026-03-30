# 🧠 EMS AI Memory Layer (Persistent Rules)

> This file is the single source of truth for all AI agents and developers working on EMS (Employee Management System).
> MUST NOT be deleted or rewritten. Only append updates.

---

# 📌 1. Project Identity

- Name: Employee Management System (EMS)
- Type: RESTful API (Laravel)
- Architecture: Modular Monolith (Clean Architecture)
- Database: MySQL (Eloquent ORM)
- Auth: JWT (tymon/jwt-auth)

---

# 🌍 2. Domain Overview

EMS is a business system for managing:

- Users & Roles
- Employees
- Departments
- Salaries
- Leaves
- Attendance
- Reporting & Analytics

---

# 🧱 3. Database Policy

- UUID for primary keys (preferred)
- Strict foreign key constraints
- Relations MUST NOT be broken
- No schema changes without approval

---

# 🏗 4. Architecture Rules (MANDATORY)

Controller → Service → Repository → Model

| Layer | Responsibility |
|------|--------------|
| Controller | Request/Response only |
| Service | Business Logic |
| Repository | DB access |
| Model | Eloquent models |
| Resource | API formatting |

---

# 🚫 Rules

- NO business logic in controllers
- NO DB queries in controllers
- NO raw models returned
- ALWAYS use Resources
- ALWAYS use Services

---

# 📡 5. API Response Standard

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

---

# 🔐 6. Authentication & Roles

Roles:
- admin
- manager
- employee

JWT Authentication:
- Stateless
- Token blacklist on logout
- Rate limit login

---

# 🧩 7. Core Systems

### Users
- CRUD (admin only)

### Employees
- Linked to departments
- Profile management

### Departments
- Has manager
- Has employees

### Salaries
- base_salary
- bonuses
- deductions

### Leaves
- apply / approve / reject

### Attendance
- check-in / check-out

---

# 🔄 8. Feature Workflow (MANDATORY)

For every feature:

1. FormRequest
2. Service
3. Repository
4. Resource
5. Controller
6. Routes
7. Postman
8. Tests

---

# 🧪 9. Testing Policy

- Pest required
- Cover:
  - success
  - validation
  - authorization
  - edge cases

---

# 📬 10. Postman Rules

- NEVER delete requests
- ALWAYS add new
- Include:
  - request
  - headers
  - body
  - response

---

# 🚀 11. Execution Roadmap

Phase 1: Auth + Roles
Phase 2: Users
Phase 3: Employees + Departments
Phase 4: Salary + Leaves + Attendance
Phase 5: Reports + Analytics
Phase 6: Optimization + Caching

---

# 🚫 12. Guardrails

NEVER:
- break architecture
- expose sensitive data
- skip validation
- skip service layer

ALWAYS:
- use DTO/Resources
- validate requests
- handle errors properly

---

# 🧠 13. Logging Rules

- Log login event:
  "We are logged in to the EMS OSP"

- Log:
  - user creation
  - leave approval
  - salary update

---

# 🚀 14. Performance Rules

- DB filtering ONLY (no collection filtering)
- Use pagination
- Avoid N+1
- Use eager loading
- Index frequently used columns

---

# 🧠 15. Agent Behavior Rules

Agent MUST:

- follow clean architecture
- never skip layers
- always validate inputs
- always optimize queries
- always update Postman
- always write production code

---

# 📋 16. Current Status

NOT STARTED

---

# 🔄 17. Memory Policy

- NEVER delete content
- ONLY append
- version updates required

---

# 📝 18. README Maintenance Rule (MANDATORY)

- After completing ANY feature, the agent MUST update `README.md`.
- The update MUST append the completed feature under the correct module in the Features section.
- The update MUST refresh Project Status to reflect current phase/progress.
- README updates are part of Definition of Done and are NOT optional.

---

# 🔖 Version Update Log

- 2026-03-22: Added mandatory rule to update `README.md` after every completed feature.

---