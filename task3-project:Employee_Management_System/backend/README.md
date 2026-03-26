## Employee Management System (EMS)

### Project Overview

Employee Management System (EMS) is a Laravel RESTful API built to centralize workforce operations across users, employees, departments, salaries, leaves, attendance, and reporting.

The system solves common operational issues such as fragmented HR data, inconsistent approval flows, and poor visibility into employee and department-level activity.

### Tech Stack

- Laravel
- MySQL
- JWT Authentication (`tymon/jwt-auth`)

### Architecture

EMS follows a Modular Monolith with Clean Architecture principles.

Mandatory request flow:

`Controller -> Service -> Repository -> Model`

Architecture enforcement rules:

- Controllers handle request/response only.
- Business logic belongs in Services.
- Database access belongs in Repositories.
- API output must use Resources.
- Raw Eloquent models must not be returned from API endpoints.

### Features

Completed Features (grouped by module):

- Users: Database schema and Eloquent model implemented with UUID, role relation, and indexed string status for scalable filtering. JWT authentication is implemented with register, login, logout, refresh, and me endpoints using Clean Architecture, with enforced JSON-only API behavior and hardened unauthenticated/validation error handling. Register now creates user accounts only and requires a separate login call to issue JWT tokens. Production-grade User Management module is completed with admin-only CRUD, FormRequest validation, repository/service layering, role/status/email constraints, self-delete prevention, and filtered pagination.
- Employees: Database schema and Eloquent model implemented with department, salary, leave, and attendance relations; enums replaced by indexed string fields and hire_date indexing added. Production-grade Employees module is completed with admin/manager access control, full CRUD, FormRequest validation, repository/service layering, user-assignment constraints, auto-generated incremental employee codes, and filtered pagination.
- Departments: Database schema and Eloquent model implemented with manager relation and indexed string status. Production-grade Departments module is implemented with admin/manager access control, full CRUD, manager-role validation, duplicate-code protection, paginated search/status filtering, and department show responses including employee lists.
- Salaries: Database schema and Eloquent model implemented with composite index `(employee_id, effective_date)` for historical payroll queries. Production-grade Salary module is implemented with admin/manager access control, full CRUD, server-side net salary calculation (`amount + bonuses - deductions`), non-negative payroll validation, employee salary history endpoint, and filtered pagination by employee/date range.
- Leaves: leave_requests schema and LeaveRequest model implemented with approver relation, indexed string status, and date-range indexing. Production-grade Leave Management module is implemented with clean status flow (`pending`, `approved`, `rejected`, `cancelled`), employee-only apply/update/cancel, manager/admin-only approve/reject, overlap prevention for active periods, and filtered pagination.
- Attendance: attendance_records schema and AttendanceRecord model implemented with timestamp check-in/check-out and unique `(employee_id, attendance_date)` constraint. Production-grade Attendance module is implemented with employee check-in/check-out endpoints, one-record-per-day duplicate prevention, auto date/time and initial status assignment, check-out precondition validation, and manager/admin attendance visibility with employee history and pagination.
- Reporting and Analytics: Pending.

### Project Status

- Current status: Attendance module implemented at production level with secure check-in/check-out flow and attendance visibility controls.
- Current roadmap position: Phase 4 completed (Salary + Leaves + Attendance done).
- Next phase: Start Phase 5 for Reports + Analytics.

### Installation Guide

1. Clone the repository.

```bash
git clone <repository-url>
cd task3-project:Employee_Management_System/backend
```

2. Install PHP dependencies.

```bash
composer install
```

3. Set up environment variables.

```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database credentials in `.env`, then run migrations.

```bash
php artisan migrate
```

5. Start the development server.

```bash
php artisan serve
```

### API Structure

Standard success response format:

```json
{
	"success": true,
	"message": "",
	"data": {},
	"meta": {}
}
```

Standard error response format:

```json
{
	"success": false,
	"message": "",
	"errors": {},
	"code": "ERROR_CODE"
}
```

### Future Work

Roadmap-aligned future implementation plan:

- Phase 1: Auth + Roles
- Phase 2: Users
- Phase 3: Employees + Departments
- Phase 4: Salary + Leaves + Attendance (Completed)
- Phase 5: Reports + Analytics
- Phase 6: Optimization + Caching

### README Update Rule

For each completed feature:

- Append the feature under the appropriate module in the Features section.
- Update the Project Status section to reflect current progress/phase.
- Do not rewrite the entire README; keep updates incremental and readable.
