# ReviewFlow — Development Rules & Conventions

**Last Updated:** 2026-09-25

---

## 1. Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2 |
| Frontend | Blade templates (server-rendered), inline CSS/JS per view |
| Database | MySQL 8 (via XAMPP locally) |
| Auth | Laravel Sanctum + Socialite (Google OAuth) |
| AI | Google Gemini 2.0 Flash (`gemini-2.0-flash`) |
| Payments | Razorpay (INR, test mode) |
| Font | Plus Jakarta Sans (Google Fonts) |
| Dev Server | `php artisan serve` on port 8000 |

---

## 2. Code Conventions

### PHP / Laravel
- **No comments** unless the WHY is non-obvious. No docblocks, no multi-line comment blocks.
- **Controllers** return views directly — no resource/API controllers pattern.
- **Models** use `$fillable` (not `$guarded`), typed `casts()` method, explicit relationship methods.
- **No Repository pattern** — controllers call models/services directly.
- **Services** are injected via method injection (e.g., `index(HealthScoreService $svc)`).
- **Middleware** registered as aliases in `bootstrap/app.php`: `plan` → `EnsureActivePlan`, `role` → `EnsureRole`.
- **Routes** in `routes/web.php` only. No API routes. No route model binding customization.
- **Validation** done inline in controllers via `$request->validate()`.
- **No Form Requests** — validation stays in controller methods.
- **No Events/Listeners** — direct method calls.
- **No Queues in production** — synchronous processing (jobs table exists but not used).

### Blade / Frontend
- **No build tools** — no Vite, no Webpack, no npm. All CSS/JS is inline in Blade `@push('head')` and `@push('scripts')`.
- **No CSS framework** — custom CSS with CSS variables on `:root`.
- **No JS framework** — vanilla JavaScript only. No jQuery, no Alpine.js, no Livewire.
- **Chart.js** loaded from CDN for charts on overview page.
- **Component patterns:** `.card`, `.btn`, `.btn-ghost`, `.badge`, `.alert`, `.modal-bg`/`.modal`, `.icon-btn`, `.page-head`, tables.
- **Responsive:** Mobile-first, breakpoints at `max-width: 1000px`, `820px`, `700px`, `520px`.
- **Dark mode:** Supported via `[data-theme="dark"]` selector on `<html>`. CSS variables redefine colors.

### Naming
- **Routes:** kebab-case (`gbp-content`, `rank-checker`, `ai-media`)
- **Route names:** dot-separated (`reviews.show`, `admin.users.store`)
- **Views:** `dashboard/{page}.blade.php` for user pages, `admin/{page}.blade.php` for admin
- **Models:** PascalCase singular (`GbpPost`, `AiMedia`, `CreditPackage`)
- **Controllers:** PascalCase + `Controller` suffix
- **Middleware:** PascalCase + descriptive (`EnsureActivePlan`, `EnsureRole`)
- **Services:** PascalCase + `Service` suffix (`CreditService`, `HealthScoreService`)
- **Database tables:** snake_case plural (`gbp_posts`, `ai_media`, `credit_packages`)
- **Database columns:** snake_case (`agency_id`, `client_id`, `google_access_token`)

---

## 3. Architecture Rules

### Multi-Tenancy (Agency Scoping)
- Every data model belongs to an `agency_id`.
- `SUPER_ADMIN` sees all data for their agency.
- `CLIENT_OWNER` is scoped to `user.client_id` — they only see their own client's data.
- Controllers determine scoping: `$agencyId = auth()->user()->agency_id`, then optionally `$clientId = auth()->user()->client_id`.
- Single-client users (CLIENT_OWNER) auto-pick their client — no client selector shown.

### Route Middleware Groups
```
guest               → login, register, Google login
auth                → plans, profile, billing, email verification
auth                → core features (dashboard, reviews, clients, etc.) — FREE
auth + plan         → AI/premium features (generate, audit, rank-check, etc.) — REQUIRES PLAN
auth + role:SUPER_ADMIN → admin panel (/admin/*)
```

### Free vs Paid Boundary
- **Free (auth only):** View pages, sync data, manual CRUD, manage clients/team, connect Google/Meta
- **Paid (auth + plan):** AI generate, AI reply, audit run, keyword generate, rank check, competitor analysis, AI media generate, AI mode, buy credits
- The `EnsureActivePlan` middleware returns:
  - AJAX: `{ error, upgrade_url, needs_plan: true }` with HTTP 402
  - Page: redirect to `/plans` with flash message

### Credit System
- AI actions consume credits via `CreditService::spend(agencyId, amount, description)`.
- Credit balance stored in `subscriptions.credit_balance`.
- Ledger entries in `credit_ledger` table for audit trail.
- If insufficient credits: return error (not a 402, just a validation error).

---

## 4. File Organization

```
app/
├── Http/
│   ├── Controllers/        # All controllers (flat, except Invoicing/)
│   │   ├── Auth/           # AuthController
│   │   └── Invoicing/      # Invoice, Customer, Service, Expense, BillingSettings, TallyExport
│   └── Middleware/          # EnsureActivePlan, EnsureRole
├── Models/                 # All Eloquent models
├── Services/               # Business logic services
│   ├── AiService.php       # Gemini API wrapper
│   ├── CreditService.php   # Credit spending/balance
│   ├── GbpService.php      # GBP API abstraction (delegates to provider)
│   ├── GoogleGbpProvider.php  # Real Google API calls
│   ├── MockGbpProvider.php # Mock for testing without Google
│   ├── HealthScoreService.php # Business health score computation
│   ├── MetaService.php     # Meta Graph API calls
│   ├── PlacesService.php   # Google Places API
│   └── RazorpayService.php # Razorpay payment processing
resources/views/
├── layouts/
│   ├── app.blade.php       # Main user-facing layout (sidebar, topbar, CSS)
│   └── admin.blade.php     # Admin panel layout
├── dashboard/              # All user-facing pages
├── admin/                  # Admin panel pages
├── auth/                   # Login, register
├── policies/               # Legal pages (terms, privacy, refund, etc.)
└── welcome.blade.php       # Landing page
routes/
└── web.php                 # All routes (single file)
```

---

## 5. Security Rules

- CSRF token on every form (`@csrf`).
- `@method('DELETE')` for delete operations.
- Role check via middleware, not in-controller checks.
- OAuth state parameter to prevent CSRF on OAuth flows.
- Signed URLs for email verification.
- No raw SQL — always Eloquent or query builder with parameter binding.
- Passwords hashed automatically via `User` model's `hashed` cast.
- Admin routes behind `role:SUPER_ADMIN` middleware.
- No `client_id` or `agency_id` trust from user input — always from `auth()->user()`.

---

## 6. Git & Deployment

- **Branch:** `main` (single branch workflow)
- **Production:** https://review.heltog.com
- **Commits:** Concise messages, `Co-Authored-By` when AI-assisted
- **No CI/CD pipeline** currently — manual deployment
- **Storage:** `storage/framework/views/` for compiled Blade (gitignored patterns apply)
- **Logs:** `storage/logs/laravel.log`

---

## 7. Testing

- **No automated tests** currently.
- **Manual testing** via browser: login as admin@demo.com / admin123 (SUPER_ADMIN).
- **PHP syntax check:** `php -l <file>` for quick validation.
- **Route verification:** `php artisan route:list` to confirm routes register.
- **View compilation:** `php artisan view:cache` to verify Blade syntax.

---

## 8. Don'ts

- Don't add npm/Vite/Webpack — all frontend is inline.
- Don't create API routes — everything is web routes.
- Don't add abstractions (repositories, interfaces) unless strictly needed.
- Don't mock the database in tests.
- Don't add unused features or "plan for the future."
- Don't remove existing code without explicit instruction — update/enhance instead.
- Don't change the font (Plus Jakarta Sans) or primary color (#4c6fff) without instruction.
- Don't add external CSS/JS libraries except from CDN when genuinely needed.
