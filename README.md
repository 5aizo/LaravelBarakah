# 🛡️ INFO 4345 Web Application Security Project
**Project Name:** BiteBarakah2  
**Course:** INFO 4345 Web Application Security 
**Semester:** Semester 2, 2024/2025  
**Institution:** International Islamic University Malaysia
**Group Name:** 
**Members:**
- Raja Muhamad Umar (2119191) [Rubrics 1–4]
- Muhammad Afzal Bin Mohd Nor (2123023) [Rubrics 5–8]
- Muhammad Afiff Firdaus Bin Abdullah (2120573) [Rubrics 9–13]

---

## 🔍 Overview
This project is an enhancement of our previous Laravel-based web application `bitebarakah2`, which has been evaluated and improved based on 13 web application security principles aligned with the course rubric and OWASP best practices.

Based on the given rubrics, each group member focused on specific security criteria, and changes were implemented across authentication, validation, session handling, authorization, browser security, database access, and file protection.

---

## ✅ Vulnerability Scan Report (ZAP)
- Tool: OWASP ZAP vX.X.X
- Target: http://127.0.0.1:8000
- Result: [Summary of ZAP results]
- Before Fixes
    🟠 Medium Risk (2 alerts)
    1. Content Security Policy (CSP) Header Not Set
        - Description: Missing CSP headers means browsers have no restrictions on where content (e.g., JS, fonts, CSS) can be loaded from. This increases risk of XSS and code injection.
        - Pages Affected: /login, /register, /home, /forgot-password, /sitemap.xml
        - Fix suggestion: Add a Content-Security-Policy header in your Laravel middleware.

    2. Missing Anti-clickjacking Header (X-Frame-Options)
        - Description: Your site doesn’t block iframe embedding, making it vulnerable to clickjacking.
        - Pages Affected: /login, /register, /home, /forgot-password
        - Fix suggestion: Add X-Frame-Options: DENY or use CSP frame-ancestors 'none';.

    🟡 Low Risk (5 alerts)
    1. Big Redirect Detected
        - Description: Large redirect responses may unintentionally leak sensitive information in the response body.
        - Pages Affected: /, /login, /register, /home, /forgot-password
    2. Cookie Missing HttpOnly Flag
        - Description: Cookies without HttpOnly are accessible to JavaScript, increasing risk of session hijacking.
        - Fix suggestion: Ensure cookies are set with HttpOnly.
    3. Cross-Domain JS File Inclusion
        - Description: External scripts (like Google Fonts or JSCDN) were loaded without CSP restrictions.
        - Fix suggestion: Use CSP to restrict allowed JS origins.
    4. Server Leaks Info via X-Powered-By Header
        - Description: This header exposes technology stack info (e.g., PHP), which helps attackers.
        - Fix suggestion: Remove this header in Laravel or your server config.
    5. Missing X-Content-Type-Options Header
        - Description: Without this, browsers may attempt MIME-type sniffing, which can lead to XSS.
        - Fix suggestion: Set X-Content-Type-Options: nosniff.

    ℹ️ Informational (3 alerts)
    1. Authentication Request Identified
        - ZAP detected login forms on the site.
    2. Session Management Response Identified
        - Session tokens were found in responses (normal for login systems).
    3. User-Agent Fuzzing
        - No exploit found, just ZAP testing different user-agents.

📄 [View Full ZAP Report](reports/2025-06-30-ZAP-Report-bitebarakah2.html)

---

## 🔒 Rubric-based Security Enhancements

### 🧾 RUBRICS 1–4 (Handled by Umar)
### 🧾 Rubric 1: Input Validation

**Before:**  
- OrderController accepted raw input without any validation.
- Login and registration inputs lacked strict type/format enforcement.

**Fix Implemented:**
- Applied strict validation rules using Laravel's `$request->validate()` function.
- Enforced email formatting (`rfc`, `dns`), input length limits, and regex-based password complexity.

**Code:**

`LoginRequest.php`:
```php
public function rules(): array
{
    return [
        'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
        'password' => ['required', 'string', 'min:8'],
    ];
}
```
`RegisteredUserController.php`:
```php
$request->validate([
    'name' => 'required|string|max:100',
    'email' => 'required|email:rfc,dns|max:255|unique:users,email',
    'password' => [
        'required',
        'string',
        'min:8',
        'confirmed',
        'regex:/[A-Z]/',      // uppercase
        'regex:/[a-z]/',      // lowercase
        'regex:/[0-9]/',      // digit
        'regex:/[@$!%*#?&]/', // special character
    ],
]);
```
`OrderController.php`:
```php
$request->validate([
    'customer_name' => 'required|string|max:100',
    'dessert_type' => 'required|string|in:Cake,Pie,Tart,Pudding',
    'dessert_item' => 'required|string|max:100',
    'quantity' => 'required|integer|min:1|max:50',
]);

```
---
### 🧾 Rubric 2: Error Handling & Information Disclosure

**Before:**  
- APP_DEBUG=true risked exposing internal error messages and stack traces.
- No custom error views existed for 404 or 500 responses.

**Fix Implemented:**
- Disabled debug mode in production via .env
- Used Handler.php to show custom views for errors
- Created user-friendly 500.blade.php and 404.blade.php

**Code:**

`.env`:
```php
APP_DEBUG=false
```
`Handler.php`:
```php
public function render($request, Throwable $exception)
{
    if (app()->environment('production')) {
        $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
        return response()->view("errors.$status", [], $status);
    }

    return parent::render($request, $exception);
}

```
`resources/views/errors/500.blade.php`:
```php
<h1>Something went wrong</h1>
<p>We're sorry, but an unexpected error occurred.</p>
```
---
### 🧾 Rubric 3: Password Storage

**Before:**  
- Passwords were hashed using Laravel default (bcrypt), which is secure but not the strongest available.

**Fix Implemented:**
- Passwords securely hashed via Hash::make()
- Hashing algorithm upgraded to argon2id (memory-hard, GPU-resistant)

**Code:**

`RegisteredUserController.php`:
```php
'password' => Hash::make($request->password),
```
`config/hashing.php`:
```php
'default' => 'argon2id',
```
---
### 🧾 Rubric 4: Password Policies

**Before:**  
- No password complexity rules
- No lockout mechanism on failed logins

**Fix Implemented:**
- Applied strict complexity rules via regex in validation
- Enabled Laravel's rate limiter to block after 5 failed login attempts (1 min delay)

**Code:**

`Password validation (RegisteredUserController.php)`:
```php
    'password' => [
        'required',
        'string',
        'min:8',
        'confirmed',
        'regex:/[A-Z]/',      // uppercase
        'regex:/[a-z]/',      // lowercase
        'regex:/[0-9]/',      // digit
        'regex:/[@$!%*#?&]/', // special character
    ],
```
`Login rate limiting (LoginRequest.php)`:
```php
if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
    return;
}
// Lockout occurs after 5 attempts
```
---
### 🧾 RUBRICS 5-8 (Handled by Afzal)
### ✅ Rubric 5: Authentication – Session Management

**Explanation:**

Laravel ensures strong session management by:
- Regenerating session IDs after login (prevents session fixation)
- Auto-expiring sessions after a defined timeout
- Securing cookies with `HttpOnly`, `Secure`, and `SameSite` flags

**Code:**

```php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php
$request->session()->regenerate();

// .env
SESSION_LIFETIME=60

// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',
```
---

### ✅ Rubric 6: Authentication – Multi-Factor Authentication

**Explanation:**

To enhance security, 2FA (Time-based One-Time Password) was added for admin users using the pragmarx/google2fa package.
Admins can enable 2FA from a dedicated page, generating a secret key and recovery codes. These can be used with apps like Google Authenticator.

**Code:**
```php
// Enable 2FA: routes/web.php
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

Route::post('/admin/2fa/enable', function () {
    $user = auth()->user();
    $user->forceFill([
        'two_factor_secret' => encrypt((new Google2FA)->generateSecretKey()),
        'two_factor_recovery_codes' => encrypt(json_encode(
            collect(range(1, 8))->map(fn () => Str::random(10))->all()
        )),
    ])->save();

    return back()->with('status', '2FA enabled. Store your recovery codes.');
})->middleware(['auth', 'is_admin'])->name('admin.2fa.enable');

<!-- View: resources/views/admin/2fa.blade.php -->
@if (auth()->user()->two_factor_secret)
    <p>✅ 2FA is enabled</p>
    <form method="POST" action="{{ route('admin.2fa.disable') }}">
        @csrf
        <button type="submit">Disable 2FA</button>
    </form>
@else
    <form method="POST" action="{{ route('admin.2fa.enable') }}">
        @csrf
        <button type="submit">Enable 2FA</button>
    </form>
@endif
```
---

### ✅ Rubric 7: Authorization – Default Permissions

**Explanation:**

Basic access control is enforced:

-Guests are redirected to the registration page.

-Only authenticated users can access /home.

-Non-admin users trying to access admin routes are shown a 403 Forbidden page.

**Code:**
```php
// web.php
Route::get('/', function () {
    return Auth::check() ? redirect('/home') : redirect()->route('register');
});

Route::get('/home', function () {
    return view('home');
})->middleware('auth')->name('home');

// Inside controller
if (!auth()->check() || !auth()->user()->is_admin) {
    abort(403); // Forbidden access
}
```
---

### ✅ Rubric 8: Authorization – Role-Based Access Control (RBAC)

**Explanation:**

Access to admin routes is protected based on roles:

-An is_admin column is added to the users table

-Middleware is_admin is used to protect sensitive admin routes

-Only users with is_admin = 1 can access these routes

**Code:**
```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_admin')->default(false);
});

// Middleware: app/Http/Middleware/IsAdmin.php
public function handle(Request $request, Closure $next)
{
    if (!auth()->user()->is_admin) {
        abort(403);
    }

    return $next($request);
}

// Route group with middleware
Route::middleware(['auth', 'is_admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/orders', [AdminController::class, 'viewOrders'])->name('admin.orders');
});
```
---

**How to Test**

-✅ Log in as a normal user → access to /home ✅ but access to /admin ❌ (403) (Rubric 7: Authorization – Default Permissions)

-✅ Log in as an admin user (is_admin = 1 in DB) → access to /admin ✅ (Rubric 8: Authorization – Role-Based Access Control (RBAC))

-✅ Go to /admin/2fa → enable 2FA and scan with Google Authenticator (Rubric 6: Authentication – Multi-Factor Authentication)

-✅ Session auto-expires after timeout (SESSION_LIFETIME), and regenerates on login (Rubric 5: Authentication – Session Management)

---
### 🧾 RUBRICS 9-13 (Handled by Afiff)
### ✅ Rubric 9: Browser Security Principles (Cross-Site Scripting (XSS) Prevention)
- All user-supplied data displayed in the browser is contextually encoded. Content Security Policy (CSP) is implemented.

**XSS Prevention:**
I used Blade’s default escaping syntax {{ $variable }} instead of {!! !!} to ensure all user-supplied data is encoded before displaying it in the browser.

✅ Implemented in views like profile.blade.php, dashboard.blade.php, and order.blade.php.
**Code:**
```php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}

                    <!-- ✅ Safely show user's name -->
                    <p>Welcome, {{ Auth::user()->name }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```
---
### ✅ Rubric 10. Browser Security Principles (Cross-Site Request Forgery (CSRF) Prevention)
- Anti-CSRF tokens (synchronizer tokens) are used for all state-changing requests. SameSite cookies are properly configured.

**CSP (Content Security Policy):**
I added the ContentSecurityPolicy middleware to enforce a strict CSP policy. This helps to mitigate XSS by only allowing scripts/styles from trusted sources.

✅ Implemented in app/Http/Middleware/ContentSecurityPolicy.php and registered in Kernel.php.
**Code:**
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; object-src 'none';");

        return $response;
    }
}
```
CSRF Protection:
All forms include @csrf to protect against CSRF attacks by generating synchronizer tokens. Laravel handles token validation automatically.
✅ Applied in all form views like order.blade.php.

**Code inserted**
```php
\App\Http\Middleware\ContentSecurityPolicy::class,
```
---
### ✅ Rubric 11. Database Security Principles (SQL Injection Prevention)
- All database queries use parameterized statements, prepared statements, or ORM frameworks that prevent SQL injection.

**SQL Injection Prevention:**
Laravel uses Eloquent ORM to interact with the database. This means all queries are automatically parameterized and protected from SQL injection.
✅ Used in User model and controllers that access user data.

use Illuminate\Foundation\Auth\User as Authenticatable;
And then:

class User extends Authenticatable
➡️ Authenticatable itself extends Eloquent's base model, which means your User model is an Eloquent model.

---
### ✅ Rubric 12. Database Security Principles (Database Access Control)
- Database users have the principle of least privilege applied; application connects with minimal required permissions.

**Database Access Control:**
The application connects to the MySQL database using the .env file. I ensured the database user in .env only has minimal privileges (e.g., SELECT, INSERT, UPDATE).
✅ Used default .env config and did not grant full/root access.

---
### ✅ Rubric 13. File Security Principles (File Access Control)
- File and directory permissions follow the principle of least privilege, restricting access to only necessary users/processes. User input is never directly concatenated into file paths.

**File/Directory Access Control:**
I ensured storage, .env, and backend files are not accessible publicly by:

Only exposing the public/ folder via the web server (Apache).

DocumentRoot "C:/xampp/htdocs/your-laravel-project/public"
<Directory "C:/xampp/htdocs/your-laravel-project/public">

Setting proper permissions on sensitive files.
✅ Followed Laravel’s default .gitignore which excludes sensitive files from being pushed.

**Avoided Dangerous File Paths:**
I did not concatenate user input into file paths directly. All file uploads (if any) use Laravel’s built-in methods to handle file names securely.
✅ Ensured no dynamic path injection from user input.

---

### ✅ After — Summary Conclusion

After identifying and addressing security issues through OWASP ZAP scanning and rubric-guided improvements, our Laravel application bitebarakah2 has been significantly hardened against common web vulnerabilities.

All 13 rubric categories were addressed by the group, with specific fixes including:

✅ Strict input validation across all user forms

✅ Secure password storage with argon2id hashing

✅ Enforcement of password policies and login rate-limiting

✅ Session regeneration, secure cookies, and HttpOnly flags

✅ Two-Factor Authentication (2FA) for admin accounts

✅ Full Role-Based Access Control (RBAC) and least-privilege permissions

✅ Content Security Policy (CSP) headers added to prevent XSS and clickjacking

✅ CSRF protection with Laravel's built-in token system

✅ SQL injection prevention via Laravel’s Eloquent ORM

✅ Server header hardening and file access protection

As a result:

All high-risk and most medium-risk vulnerabilities reported by ZAP have been mitigated.

Our application now aligns with best practices in web application security, ensuring safer authentication, authorization, and user interaction.

This enhancement process has improved not only the system’s security but also the team's understanding of real-world secure coding practices.

📄 [View Full ZAP Report After](reports/2025-06-30-ZAP-Report-bitebarakah2 After.html)

"# Bitebarakah3" 
"# WebAppSec1" 
