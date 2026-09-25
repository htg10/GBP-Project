# ReviewFlow — Product Requirements Document

**Product Name:** ReviewFlow
**Tagline:** Dominate Local Search
**Production URL:** https://review.heltog.com
**Version:** 1.0
**Last Updated:** 2026-09-25

---

## 1. Vision

ReviewFlow is an all-in-one **Local Growth OS** for multi-location businesses and agencies. It centralizes Google Business Profile management, review monitoring, AI-powered content creation, social publishing, lead tracking, invoicing, and competitive intelligence into a single dashboard — replacing 5-10 disconnected tools.

---

## 2. Target Users

| Role | Description |
|------|-------------|
| **SUPER_ADMIN** | Platform owner / agency admin. Manages all users, clients, plans, billing, credits. Sees all data across agencies. |
| **CLIENT_OWNER** | Business owner. Scoped to one client via `user.client_id`. Sees only their own business data (reviews, posts, photos, leads, etc.). |

Single-client CLIENT_OWNER users get auto-scoped — no client picker shown. Multi-client admins see a client selector on relevant pages.

---

## 3. Core Modules

### 3.1 Dashboard Overview
- **Business Health Cockpit** — SVG circular gauge (0-100) with animated arc, 6 sub-scores (Reviews, Posts, Photos, Social, Leads, Connections) with progress bars
- **"Do Next" Action Queue** — up to 6 prioritized actions with impact badges (Critical/High/Medium/Low) and direct route links
- **Module Summary Cards** — 6 cards (Reviews, Google Posts, Social Media, Leads, Insights, Ad Reports) with key metrics
- **KPI Row** — Total reviews, average rating, reply rate, posts count
- **Charts** — Rating distribution bar chart, review volume line chart (30 days)
- **Locations Grid** — All GBP locations with rating, review count, link to review detail
- **Recent Reviews** — Latest 5 reviews with star display
- **Quick Actions** — Shortcut buttons to key features

### 3.2 Reviews & Replies
- View all reviews per location (synced from Google)
- Star rating display (1-5 stars)
- Reply to reviews (direct to Google via API)
- **AI Generate Reply** — Gemini-powered contextual reply generation (requires plan + credits)
- Auto-reply toggle per subscription
- Draft reply support
- Sync reviews from Google on demand
- Filter/search reviews

### 3.3 Posts & Photos (GBP Content)
- **Posts tab** — Create, edit, publish GBP posts (UPDATE type) with body text and optional image
- **Photos tab** — Upload, publish photos to GBP with AI-generated captions
- **AI Media tab** — Generate images via Gemini, then use them as GBP photos
- AI post copy generation (requires plan + credits)
- AI caption generation (requires plan + credits)
- Manual CRUD is free; AI features are behind plan

### 3.4 Social Studio
- Compose posts for Facebook, Instagram, LinkedIn, X
- Live preview with platform-specific styling
- Image upload (drag & drop, file picker) or URL
- Schedule or publish immediately
- AI caption generation (requires plan + credits)
- Post history with status badges (Published/Scheduled/Failed)
- Retry failed posts

### 3.5 Leads CRM
- Kanban-style pipeline: New → Contacted → Qualified → Won / Lost
- Add leads manually with name, email, phone, source, notes
- Drag/move leads between stages
- Per-client lead tracking

### 3.6 Google Audit
- AI-powered comprehensive audit of a client's Google Business Profile
- Generates structured analysis with scores and recommendations
- Requires plan + credits

### 3.7 Competitor Analysis
- Compare a client against named competitors
- AI generates strengths, gaps, and action plan
- Rating/review comparison
- Requires plan + credits

### 3.8 Local Rank Checker
- Check business position in local search results for a keyword
- Supports real Google Places API (if key set) or AI estimation fallback
- Configurable search radius (2/5/10/20 km)
- Requires plan + credits

### 3.9 Keyword Suggestion
- AI-generated local SEO keywords by business, city, industry
- Grouped keyword results with copy-all button
- Quick-fill from existing clients
- Requires plan + credits

### 3.10 One-Click Optimize
- AI optimization suggestions for a client's GBP listing
- Generates actionable recommendations
- Requires plan + credits

### 3.11 AI Mode (Marketing Chat)
- Full-page AI chat interface for marketing queries
- Powered by Gemini
- Requires plan (entire page behind plan middleware)

### 3.12 AI Generated Media
- Generate images using Gemini (Imagen) from text prompts
- Gallery view of generated images
- Use generated images as GBP photos
- Delete unwanted generations
- Generate requires plan + credits; view/delete/use is free

### 3.13 Insights
- Live Google Business Profile performance metrics
- Search views, map views, calls, direction requests, website clicks
- Period comparison charts
- CSV/PDF download
- Free (no plan required)

### 3.14 Ads Reports
- View ad campaign performance data
- Metrics: impressions, clicks, CTR, spend
- Live data from Google Ads integration

### 3.15 WhatsApp
- Send WhatsApp messages via Cloud API
- Message history per client

### 3.16 Invoicing & Accounting
- Full invoicing module: create, send, track invoices
- Customer management (linked to clients)
- Service catalog with categories
- Expense tracking
- Billing settings (GST number, bank details, signature)
- Tally XML export for accounting integration

### 3.17 Team Management
- Add/edit/remove team members under your agency
- Role assignment (SUPER_ADMIN / CLIENT_OWNER)

### 3.18 Client Management
- Add/edit/remove clients
- Connect clients to Google Business Profile via OAuth
- Connect clients to Meta (Facebook/Instagram) via OAuth
- Import GBP locations automatically
- Add manual locations
- View client detail page with all locations

---

## 4. Billing & Credits

### Plan System
- Plans are created by SUPER_ADMIN in admin panel
- Each plan has: name, code, price (GST-inclusive), GST rate, credits/month, features list, module permissions, sort order, active/hidden status
- GST calculated as: base = price / (1 + gst_rate/100), gst = price - base
- Plans visible on `/plans` page for self-serve upgrade

### Credit System
- AI actions consume credits (managed by CreditService)
- Credits tracked in `credit_ledger` table
- Monthly credit allocation from plan
- Credit packs available for purchase (admin-created packages)
- Manual top-up by admin

### Payment
- Razorpay integration (test mode)
- INR currency
- Payment verification flow: create order → Razorpay checkout → server-side verify

### Free vs Paid Flow
- Users can login, connect Google, view reviews/data, manage clients — all free
- AI features (generate reply, audit, keywords, rank check, etc.) require active plan
- When user clicks AI action without plan: AJAX returns 402 JSON → frontend shows "Unlock AI Features" modal with plan link
- Sidebar hides billing items for users without plan

---

## 5. Admin Panel

Accessible at `/admin` — requires SUPER_ADMIN role.

| Page | Purpose |
|------|---------|
| Overview | Platform-wide KPIs (agencies, users, clients, reviews), all users table, subscription summary |
| Users | Card-based user list. Create/edit/delete users. Assign plans. No scope-to-client field. |
| Plans | Card-based plan management. Create/edit/delete plans with GST, credits, module permissions. |
| Credit Packs | Card-based credit package management. Create/edit/pause/activate/delete. |

---

## 6. Authentication

- Email/password login and registration
- Google OAuth login (via Socialite)
- Email verification (signed URL)
- Profile page with avatar, name, email, phone editing
- Forgot password (placeholder)

---

## 7. Integrations

| Service | Purpose | Config Keys |
|---------|---------|-------------|
| Google Business Profile API v4 | Reviews, posts, photos, insights, locations | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` |
| Google Places API | Local rank checking | `GOOGLE_PLACES_API_KEY` |
| Google Gemini AI | Content generation, image generation, AI replies | `GEMINI_API_KEY`, `GEMINI_MODEL` |
| Razorpay | Payment processing | `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET` |
| Meta Graph API | Facebook/Instagram social posting | `META_APP_ID`, `META_APP_SECRET` |
| WhatsApp Cloud API | WhatsApp messaging | `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_ACCESS_TOKEN` |
| Google OAuth | User login via Google | Same as GBP client |

---

## 8. Non-Functional Requirements

- **Performance:** Server-rendered Blade pages, no SPA framework overhead. Inline CSS/JS per page.
- **Security:** CSRF protection on all forms, signed URLs for email verification, OAuth state parameter validation, role-based middleware.
- **Responsiveness:** Mobile-first breakpoints at 1000px, 820px, 700px, 520px. Collapsible sidebar on mobile.
- **Timezone:** Asia/Kolkata (IST) default.
- **Database:** MySQL via XAMPP.
- **Hosting:** Can deploy on any PHP 8.2+ server with MySQL.
