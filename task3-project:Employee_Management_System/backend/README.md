<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

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

- Users: None completed yet.
- Employees: None completed yet.
- Departments: None completed yet.
- Salaries: None completed yet.
- Leaves: None completed yet.
- Attendance: None completed yet.
- Reporting and Analytics: None completed yet.

### Project Status

- Current status: NOT STARTED (from AGENTS.md)
- Current roadmap position: Pre-Phase 1
- Next phase: Phase 1 - Auth + Roles

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
- Phase 4: Salary + Leaves + Attendance
- Phase 5: Reports + Analytics
- Phase 6: Optimization + Caching

### README Update Rule

For each completed feature:

- Append the feature under the appropriate module in the Features section.
- Update the Project Status section to reflect current progress/phase.
- Do not rewrite the entire README; keep updates incremental and readable.
