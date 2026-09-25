# ReviewFlow — Technical Architecture

**Last Updated:** 2026-09-25

---

## 1. System Overview

```
┌─────────────────────────────────────────────────┐
│                   Browser                        │
│  (Server-rendered Blade + inline CSS/JS)        │
└──────────┬──────────────────────────────┬────────┘
           │ HTTP                          │ OAuth
┌──────────▼──────────┐     ┌─────────────▼──────────┐
│    Laravel 12       │     │   External APIs          │
│    PHP 8.2          │     │                          │
│  ┌──────────────┐   │     │  - Google GBP API v4     │
│  │ Controllers  │   │     │  - Google Places API     │
│  │ Middleware   │   │     │  - Google Gemini AI      │
│  │ Services     │   │     │  - Razorpay Payments     │
│  │ Models       │   │     │  - Meta Graph API        │
│  └──────┬───────┘   │     │  - WhatsApp Cloud API    │
│         │           │     │  - Google OAuth 2.0      │
└─────────┼───────────┘     └──────────────────────────┘
          │
┌─────────▼───────────┐
│   MySQL 8 (XAMPP)   │
│   reviewflow DB     │
└─────────────────────┘
```

---

## 2. Database Schema

### Core Tables

```
agencies
├── id, name, created_at, updated_at

users
├── id, agency_id (FK→agencies), client_id (FK→clients, nullable)
├── name, email, password, role (SUPER_ADMIN|CLIENT_OWNER)
├── phone, avatar, email_verified_at
├── remember_token, created_at, updated_at

clients
├── id, agency_id (FK→agencies)
├── name, email, phone, industry, website
├── google_account_id, google_access_token, google_refresh_token
├── meta_user_id, meta_access_token, meta_page_id, meta_page_token
├── meta_ig_user_id
├── created_at, updated_at

integrations
├── id, agency_id, client_id, provider (google|meta)
├── access_token, refresh_token, account_id, scopes, raw
├── created_at, updated_at
```

### GBP Tables

```
gbp_locations
├── id, agency_id, client_id (FK→clients)
├── google_name (accounts/{id}/locations/{id}), title, address
├── phone, website, latitude, longitude, place_id
├── average_rating, total_reviews
├── created_at, updated_at

reviews
├── id, agency_id, gbp_location_id (FK→gbp_locations)
├── google_review_id (unique), reviewer_name, reviewer_photo
├── star_rating (1-5), comment, reply, replied_at
├── draft_reply
├── review_time, created_at, updated_at

gbp_posts
├── id, agency_id, client_id, gbp_location_id
├── google_post_id, type (UPDATE), body, image_url
├── status (DRAFT|PUBLISHED|FAILED), error
├── published_at, created_at, updated_at

(gbp_photos — via GbpPhoto model, stores uploaded photos)
```

### Social & Messaging

```
social_posts
├── id, agency_id, client_id
├── platform (FACEBOOK|INSTAGRAM|LINKEDIN|X)
├── body, media_urls (JSON array), scheduled_at
├── status (DRAFT|SCHEDULED|PUBLISHED|FAILED), error
├── external_id, created_at, updated_at

whatsapp_messages
├── id, agency_id, client_id
├── to_phone, body, status, external_id
├── created_at, updated_at

leads
├── id, agency_id, client_id
├── name, email, phone, source, notes
├── stage (NEW|CONTACTED|QUALIFIED|WON|LOST)
├── created_at, updated_at

ad_reports
├── id, agency_id, client_id
├── campaign_name, impressions, clicks, cost, conversions
├── date, created_at, updated_at
```

### Billing & Subscriptions

```
subscriptions
├── id, agency_id
├── plan (string — plan name), status (ACTIVE|TRIALING|EXPIRED|CANCELLED)
├── credit_balance, monthly_credits, credits_reset_at
├── provider (razorpay), external_id, renews_at
├── auto_reply (boolean), wa_notify (boolean)
├── created_at, updated_at

plans
├── id, name, code (unique), price (int, GST-inclusive)
├── gst_rate (int, e.g. 18), credits (int, monthly)
├── features (JSON array of strings)
├── permissions (JSON array of module keys — empty = all)
├── is_active (boolean), sort (int)
├── created_at, updated_at

credit_packages
├── id, name, credits, price (GST-inclusive), gst_rate
├── is_active (boolean), sort (int)
├── created_at, updated_at

credit_ledger
├── id, agency_id, amount (signed int), balance_after
├── description, created_at

payments
├── id, agency_id, plan (string), amount (int, paise)
├── razorpay_order_id, razorpay_payment_id, razorpay_signature
├── status (PAID|FAILED), created_at, updated_at
```

### Invoicing

```
invoices
├── id, agency_id, client_id, invoice_number
├── issue_date, due_date, subtotal, tax, total, notes
├── status (DRAFT|SENT|PAID|CANCELLED)
├── created_at, updated_at

invoice_items
├── id, invoice_id, description, qty, rate, amount

services
├── id, agency_id, category_id, name, rate, unit

service_categories
├── id, agency_id, name

expenses
├── id, agency_id, description, amount, category, date

billing_settings
├── id, agency_id, business_name, gstin, pan
├── address, bank_name, account_number, ifsc, upi_id
├── signature_url, invoice_prefix, next_number
```

### AI Media

```
ai_media
├── id, agency_id, client_id
├── prompt, image_url, mime_type
├── created_at, updated_at
```

---

## 3. Model Relationships

```
Agency
├── hasMany → User, Client, Review, GbpLocation, SocialPost, Lead, etc.

User
├── belongsTo → Agency
├── belongsTo → Client (nullable — only CLIENT_OWNER)

Client
├── belongsTo → Agency
├── hasMany → GbpLocation, Review (via location), GbpPost, SocialPost, Lead

GbpLocation
├── belongsTo → Agency, Client
├── hasMany → Review

Review
├── belongsTo → GbpLocation

Subscription
├── belongsTo → Agency
```

---

## 4. Service Layer

### AiService
- Wraps Google Gemini API (`generativelanguage.googleapis.com`)
- Methods: `generateText(prompt)`, `generateImage(prompt)`
- Model: configurable via `GEMINI_MODEL` env (default: `gemini-2.0-flash`)
- Image generation uses `imagen-3.0-generate-002`
- Returns structured JSON when possible (review replies, audits, keywords)

### CreditService
- `spend(agencyId, amount, description)` — deducts credits, logs to ledger
- `balance(agencyId)` — returns current credit balance
- Checks balance before allowing AI operations

### GbpService
- Abstraction over Google Business Profile API v4
- Delegates to `GoogleGbpProvider` (real) or `MockGbpProvider` (testing)
- Methods: `listLocations()`, `listReviews()`, `replyToReview()`, `createPost()`, `uploadPhoto()`, `getInsights()`
- Handles OAuth token refresh automatically

### HealthScoreService
- `compute(agencyId, clientId)` → `['overall' => int, 'subScores' => array, 'doNext' => array]`
- 6 sub-scores: reviews (rating + reply rate), posts, photos, social, leads, connections
- `buildDoNext()` generates up to 6 prioritized actions based on gaps

### MetaService
- Facebook/Instagram posting via Meta Graph API
- Methods: `publishToPage()`, `publishToInstagram()`
- OAuth token management for page tokens

### PlacesService
- Google Places API for local rank checking
- `nearbySearch(keyword, lat, lng, radius)` → list of places
- Determines business rank among competitors

### RazorpayService
- `createOrder(amount, currency, receipt)` → Razorpay order object
- `verifyPayment(orderId, paymentId, signature)` → boolean
- Used for plan purchases and credit pack purchases

---

## 5. Middleware Pipeline

```
Request → VerifyCsrfToken → auth → [plan] → [role:X] → Controller
```

### EnsureActivePlan (`plan`)
- Checks `Subscription` for user's agency
- AJAX requests → 402 JSON `{ error, upgrade_url, needs_plan: true }`
- Page requests → redirect to `/plans` with flash message

### EnsureRole (`role:SUPER_ADMIN`)
- Checks `$user->role` against allowed roles
- Returns 403 if unauthorized

---

## 6. Authentication Flow

### Email/Password
1. User registers → creates Agency + User (SUPER_ADMIN) → auto-login → redirect to `/dashboard`
2. User logs in → validates credentials → redirect to `/dashboard`

### Google OAuth Login
1. User clicks "Sign in with Google" → redirect to Google consent
2. Callback at `/auth/google/callback` → Socialite handles token exchange
3. Find-or-create User by Google email → auto-login → redirect to `/dashboard`

### Google GBP Connect (per-client)
1. Admin clicks "Connect Google" on client card → redirect to Google with GBP scopes
2. Callback at `/google/callback` → store access/refresh tokens on Client model
3. Import GBP locations and start syncing reviews/insights

### Meta Connect (per-client)
1. Admin clicks "Connect Meta" → redirect to Facebook login dialog
2. Callback at `/meta/callback` → exchange code for long-lived token
3. Store page token and IG user ID on Client model

---

## 7. Frontend Architecture

### No Build Step
- All CSS is inline in `<style>` blocks pushed to `@stack('head')`
- All JS is inline in `<script>` blocks pushed to `@stack('scripts')`
- Only external dependencies: Google Fonts (Plus Jakarta Sans), Chart.js (CDN)

### Layout System
- `layouts/app.blade.php` — main layout with sidebar, topbar, content area
- `layouts/admin.blade.php` — admin layout with admin sidebar
- `@yield('content')` for page content
- `@push('head')` for page-specific CSS
- `@push('scripts')` for page-specific JS

### Global JS Functions
- `window.handlePlanRequired(response)` — shows upgrade modal when 402 received
- Available on all pages via `layouts/app.blade.php`

### Responsive Pattern
- Sidebar collapses to hamburger on mobile (<960px for admin, <900px for app)
- Tables become horizontally scrollable
- Grid layouts collapse from multi-column to single column
- Sticky elements become static on mobile

---

## 8. Environment Configuration

```env
# App
APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_TIMEZONE (Asia/Kolkata), APP_URL

# Database
DB_CONNECTION (mysql), DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Sessions & Cache
SESSION_DRIVER (database), CACHE_STORE (database), QUEUE_CONNECTION (database)

# AI
GEMINI_API_KEY, GEMINI_MODEL (gemini-2.0-flash)

# Payments
RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET

# Google OAuth / GBP
GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI

# Google Places
GOOGLE_PLACES_API_KEY

# Meta
META_APP_ID, META_APP_SECRET, META_REDIRECT_URI

# WhatsApp
WHATSAPP_PHONE_NUMBER_ID, WHATSAPP_ACCESS_TOKEN
```
