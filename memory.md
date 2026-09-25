# ReviewFlow — Project Memory & Context

**Last Updated:** 2026-09-25

This file captures project context, decisions, and institutional knowledge that isn't obvious from the code alone.

---

## 1. Project Identity

- **Product:** ReviewFlow — Local Growth OS
- **Company:** Heltog Technologies (Help Together Group)
- **Production URL:** https://review.heltog.com
- **Admin Email:** admin@demo.com / admin123 (SUPER_ADMIN test account)
- **Google OAuth Client ID:** 969050138931-uclne6fp24kgcpjsmfnleeo3ee83vdgp.apps.googleusercontent.com
- **Market:** India — INR currency, GST tax system (18% default), Asia/Kolkata timezone
- **Dev Environment:** Windows 11, XAMPP (PHP 8.2, MySQL 8), `php artisan serve` on port 8000

---

## 2. Users in Production

| Name | Email | Role | Scoped Client |
|------|-------|------|--------------|
| HELP TOGETHER GROUP | htgdigitalmarketing@gmail.com | Client | HELP TOGETHER GROUP's Business |
| HELP TOGETHER GROUP | helptogethergroup2@gmail.com | Client | HELP TOGETHER GROUP's Business |
| Atul | heltogtechnologies@gmail.com | Client | Heltog |
| Suman | alluringdecorllp@gmail.com | Client | ALLURING DECOR LLP |
| Super Admin | admin@demo.com | Super Admin | All agency |

---

## 3. Key Decisions & Why

### Why "teal" variable is actually blue (#4c6fff)
The CSS variable `--teal` was originally named when the accent color was teal. It was later changed to blue `#4c6fff` but the variable name was kept to avoid breaking all references across 50+ views. Treat `--teal` as "primary accent blue."

### Why no Vite/npm/build tools
Deliberate choice. The project uses inline CSS/JS in Blade templates. This avoids build complexity, makes deployment trivial (just PHP), and keeps the stack simple. Don't add build tools unless explicitly requested.

### Why routes are split into auth-only and auth+plan groups
Free vs paid boundary. Users should be able to login, connect Google, view reviews/data/insights freely. Only AI-powered features (generate, audit, rank check, etc.) require a paid plan. This was implemented in Phase 7 to improve the onboarding experience — users can explore before committing to a plan.

### Why EnsureActivePlan returns 402 JSON for AJAX
So the frontend can show a contextual upgrade modal (via `window.handlePlanRequired()`) instead of hard-redirecting to the plans page. Better UX — the user stays on the page they were using and gets a gentle prompt to upgrade.

### Why the admin panel has no "Scope to client" field
Removed because it was confusing. CLIENT_OWNER users are auto-scoped via `client_id`. When creating a CLIENT_OWNER, a Client record is auto-created. Admin should assign plans, not manually scope users to clients.

### Why billing was removed from admin sidebar
The admin billing page showed the admin's own subscription data and a manual credit top-up. This was redundant since plans are managed on the Plans page, and credit top-up can be done through the admin panel's user management. The route still exists for backward compatibility but is no longer linked in the UI.

### Why GBP photos use base64 storage
Photos are stored as base64 in `longText` columns (`gbp_photos.image`, `ai_media.image_data`, `gbp_posts.image`). This avoids needing cloud storage (S3) setup for the MVP. The `rawPhoto()` and `rawPostImage()` public endpoints serve these as real image bytes so Google can fetch them via `sourceUrl`.

### Why MockGbpProvider exists
For development without Google API credentials. Returns 6 hardcoded demo reviews. `GbpService` auto-selects the provider based on whether a `GOOGLE_GBP` integration exists for the client.

### Why subscriptions use agency_id (not user_id)
Plans are per-agency, not per-user. All users within an agency share the same subscription, credit balance, and plan features. This supports the agency model where one admin manages multiple team members.

---

## 4. Credit Costs

| Action | Credits | Key |
|--------|---------|-----|
| AI Review Reply | 1 | `ai_review_reply` |
| AI Social Caption | 1 | `ai_social_caption` |
| AI Post Caption | 1 | `ai_post_caption` |
| AI Photo Caption | 1 | `ai_photo_caption` |
| AI Chat Message | 1 | `ai_chat` |
| AI Rank Check | 1 | `ai_rank_check` |
| AI Keyword Generation | 2 | `ai_keyword_gen` |
| AI Audit | 2 | `ai_audit` |
| AI Competitor Analysis | 2 | `ai_competitor` |
| One-Click Optimize | 3 | `ai_optimize` |
| AI Image Generation | 5 | `ai_media_generate` |

### Plan Credit Allocations
- Starter: 100 credits/month
- Growth: 500 credits/month
- Agency: 2000 credits/month

---

## 5. API Integration Details

### Google GBP API v4
- Uses 4 different Google API hosts (Account Management, Business Information, Reviews v4, Media v4)
- Token refresh is automatic — `GoogleGbpProvider` detects 401/token errors and refreshes
- `invalid_grant` errors clear tokens and surface a "reconnect" message to the user
- OAuth callback is outside the `auth` middleware group because session cookies can drop across Google's redirect

### Google Gemini AI
- Text model: `gemini-2.0-flash` (configurable via `GEMINI_MODEL`)
- Image model: `imagen-3.0-generate-002`
- AiService has local fallbacks for all operations (keyword matching for sentiment, template responses for replies, etc.) when no API key is set
- Response format: requests JSON when possible, parses with `json_decode`, falls back to raw text

### Meta Graph API v21.0
- Instagram uses two-step flow: create container → publish container
- Facebook supports both text-only and photo posts
- Page tokens are obtained during OAuth and stored on the Client model
- Long-lived tokens are exchanged during the callback

### Razorpay
- Test mode (keys from dashboard.razorpay.com)
- Order creation returns an order ID that's passed to the Razorpay Checkout.js popup
- Payment verification uses HMAC-SHA256 signature: `hash_hmac('sha256', orderId.'|'.paymentId, secret)`
- Amounts are in paise (1 rupee = 100 paise)

---

## 6. Database Notes

### Enum Columns
These tables use MySQL ENUM types in migrations:
- `users.role`: SUPER_ADMIN, CLIENT_OWNER, MARKETING_MANAGER, STAFF
- `integrations.provider`: GOOGLE_GBP, GOOGLE_ADS, META_GRAPH, META_MARKETING, WHATSAPP
- `reviews.sentiment`: POSITIVE, NEUTRAL, NEGATIVE
- `gbp_posts.type`: OFFER, EVENT, UPDATE
- `gbp_posts.status` / `gbp_photos.status`: DRAFT, SCHEDULED, PUBLISHED, FAILED
- `social_posts.platform`: FACEBOOK, INSTAGRAM, LINKEDIN, X
- `social_posts.status`: DRAFT, SCHEDULED, PUBLISHED, FAILED
- `leads.source`: FACEBOOK, INSTAGRAM, WEBSITE, WHATSAPP, GOOGLE_FORM
- `leads.stage`: NEW, CONTACTED, FOLLOW_UP, APPOINTMENT, CONVERTED, LOST
- `subscriptions.status`: ACTIVE, PAST_DUE, CANCELLED, TRIALING
- `payments.status`: CREATED, PAID, FAILED
- `invoices.status`: DRAFT, SENT, PAID, OVERDUE, CANCELLED
- `expenses.method`: CASH, BANK, UPI, CARD, OTHER

### Agency Scoping Pattern
Every query in controllers follows this pattern:
```php
$agencyId = auth()->user()->agency_id;
$clientId = auth()->user()->client_id; // null for SUPER_ADMIN
// If CLIENT_OWNER → filter by client_id
// If SUPER_ADMIN → show all for agency_id
```

### Single-Client Auto-Pickup
When a CLIENT_OWNER has exactly one client (via `client_id`), the UI skips client selectors and auto-uses their client. This is checked in each controller, not centrally.

---

## 7. File Storage

- **No cloud storage** — everything is stored in the database
- Photos, avatars, AI media: stored as base64 `longText`
- Images served via public endpoints (`/media/gbp-photo/{photo}`, `/media/gbp-post-image/{post}`)
- Social post images: uploaded via form or provided as URL, stored in `media_urls` JSON array
- Invoice logos/signatures: base64 in `billing_settings.logo`

---

## 8. Known Limitations / Technical Debt

1. **No automated tests** — all testing is manual via browser
2. **No job queues** — everything is synchronous (QUEUE_CONNECTION=database but no workers running)
3. **No automated review sync** — reviews are synced manually via "Sync" button
4. **WhatsApp integration is stubbed** — messages are saved locally but not sent to API
5. **No rate limiting** on AI endpoints — relies on credit balance to limit usage
6. **No image optimization** — base64 images can be large
7. **No CI/CD** — manual deployment
8. **Admin layout has no dark mode** — only the app layout supports it
9. **No pagination** on some list pages (users, clients)
10. **No real-time updates** — everything is request/response based

---

## 9. Session Context (Recent Changes)

### Session: 2026-09-24/25

**Changes made in this session:**

1. **Business Health Cockpit** — Added SVG gauge, 6 sub-scores, "Do Next" queue, module summary cards to overview via `HealthScoreService`

2. **Plan/Billing Flow Restructured** — Split routes into auth-only (free features) and auth+plan (AI features). `EnsureActivePlan` returns 402 JSON for AJAX. Global `handlePlanRequired()` modal. Sidebar billing hidden for users without plan.

3. **10 Blade Views Updated** — optimize, audit, review-location, keywords, rank-checker, competitors, social, gbp-content (3 locations) with `handlePlanRequired` check

4. **Admin Panel Redesigned** — Users, Plans, Credit Packs pages converted from tables to card-based grid layouts

5. **Admin Users Fixed** — Removed "Scope to client" field, added plan assignment dropdown, fixed 403 error by removing `agency_id` check from `updateUser`/`destroyUser`

6. **Billing Removed from Admin Sidebar** — Link removed, Quick Manage updated

7. **Ads Reports Redesigned** — Live data integration, card-based metrics

8. **AI Media Merged with Posts & Photos** — AI Media tab integrated into GBP Content page

---

## 10. Update Protocol

When making significant changes to the codebase, update the relevant documentation files:

| File | Update When |
|------|------------|
| `PRD.md` | New features, modules, user flows, or billing changes |
| `rules.md` | New conventions, patterns, or tech stack changes |
| `architecture.md` | New models, services, middleware, database schema, or integration changes |
| `design.md` | Color palette, typography, component, layout, or responsive changes |
| `phases.md` | Features completed or new phases planned |
| `memory.md` | Key decisions, context, production data, or session changes |
