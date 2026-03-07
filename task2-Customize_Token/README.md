# Custom Token Authentication System

**Task-2:** Karaaj Company

This task implements a **custom stateless authentication token system** in Laravel, similar to JWT, **without using any external JWT packages**.

---

## Table of Contents

* [Overview](#overview)
* [Features](#features)
* [Installation](#installation)
* [Usage](#usage)
* [Authentication Flow](#authentication-flow)
* [Protected Routes](#protected-routes)
* [Events & Logging](#events--logging)
* [Security Notes](#security-notes)

---

## Overview

This system allows users to log in and receive a **signed stateless token**. Tokens are validated on every request to protected routes.

---

## Features

* Stateless token authentication
* Tokens signed using HMAC-SHA256
* Contains encoded user data and expiration time
* Custom middleware to protect routes
* Event-driven logging of token creation

---

## Installation

1. Clone the repository:

```bash
git clone https://github.com/MohammadBasharKhalaf/Customize_Token.git
cd Customize_Token
```

2. Install dependencies:

```bash
composer install
```

3. Configure `.env` file:

```env
APP_KEY=base64:YourAppKeyHere
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=karaaj_db
DB_USERNAME=root
DB_PASSWORD=
CUSTOM_TOKEN_TTL=3600
```

4. Run migrations and seed:

```bash
php artisan migrate:refresh --seed
```

---

## Usage

### Login

* Endpoint: `POST /api/login`
* Request Body (JSON):

```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

* Response:

```json
{
    "access_token": "<token_here>",
    "token_type": "Bearer",
    "expires_in": 3600
}
```

---

### Protected Routes

Example of a protected route:

```php
Route::middleware('customAuth')->get('/profile', function (Request $request) {
    return response()->json($request->user);
});
```

> Include the token in the header:

```http
Authorization: Bearer <token_here>
```

---

## Authentication Flow

1. User logs in → Token is created
2. Middleware reads the `Authorization` header
3. Token is validated (signature + expiration + version)
4. If valid → User attached to request → Route executes
5. If invalid/expired → HTTP 401 returned


---

## Events & Logging

* Event: `TokenCreated` → triggers whenever a token is generated
* Listener: `LogTokenCreation` → writes a log entry with `user_id` and `timestamp`

Log entries are stored in:

```
storage/logs/laravel.log
```

Example log:

```
[2026-02-21 00:55:33] local.INFO: Token created {"user_id":1,"created_at":"2026-02-21 00:55:33"}
```

---

## Security Notes

* Passwords are hashed using Laravel's `Hash` facade
* Tokens are signed using a secret key stored in `.env`
* Stateless system: no token storage in DB
* Recommended: store tokens securely on client-side (localStorage or cookie)

---

**Author:** Mohammad Khalaf -- Karaaj Company
**Date:** February 2026
