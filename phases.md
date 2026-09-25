# ReviewFlow — Development Phases

**Last Updated:** 2026-09-25

---

## Phase 1: Foundation ✅ (Completed)

**Goal:** Core platform with multi-tenancy, auth, and basic data models.

### Deliverables
- [x] Laravel 12 project setup with MySQL (XAMPP)
- [x] Agency → User → Client multi-tenancy model
- [x] Two-role system: SUPER_ADMIN and CLIENT_OWNER
- [x] Email/password authentication (login + register)
- [x] Google OAuth login via Socialite
- [x] Email verification with signed URLs
- [x] Profile page (avatar, name, email, phone)
- [x] Landing page with pricing section
- [x] Plus Jakarta Sans typography, #4c6fff blue palette
- [x] Responsive sidebar layout with dark mode toggle
- [x] Policy pages (terms, privacy, refund, shipping, pricing)

### Key Files Created
- `app/Models/Agency.php`, `User.php`, `Client.php`
- `app/Http/Controllers/Auth/AuthController.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/welcome.blade.php`
- `resources/views/auth/login.blade.php`, `register.blade.php`

---

## Phase 2: Google Business Profile Integration ✅ (Completed)

**Goal:** Connect clients to Google, sync reviews, manage posts & photos.

### Deliverables
- [x] Google OAuth flow for GBP access (per-client)
- [x] GBP location import and management
- [x] Review syncing from Google API
- [x] Review display with star ratings
- [x] Reply to reviews (direct to Google)
- [x] GBP Posts CRUD (create, edit, publish, delete)
- [x] GBP Photos upload and publish
- [x] Insights page with live Google data
- [x] GbpService with provider pattern (GoogleGbpProvider + MockGbpProvider)

### Key Files Created
- `app/Services/GbpService.php`, `GoogleGbpProvider.php`, `MockGbpProvider.php`
- `app/Http/Controllers/GoogleOAuthController.php`
- `app/Http/Controllers/ReviewController.php`
- `app/Http/Controllers/GbpContentController.php`
- `app/Http/Controllers/InsightsController.php`
- `app/Models/GbpLocation.php`, `Review.php`, `GbpPost.php`, `GbpPhoto.php`

---

## Phase 3: AI Features ✅ (Completed)

**Goal:** Gemini AI integration for content generation, audits, and competitive intelligence.

### Deliverables
- [x] AiService wrapping Google Gemini API
- [x] AI review reply generation
- [x] AI post copy generation
- [x] AI photo caption generation
- [x] AI social media caption generation
- [x] Google Audit (full GBP audit with AI)
- [x] Competitor Analysis (AI-powered comparison)
- [x] Keyword Suggestion (AI-generated local SEO keywords)
- [x] Local Rank Checker (Google Places API + AI fallback)
- [x] One-Click Optimize (AI optimization suggestions)
- [x] AI Mode (marketing chat interface)
- [x] AI Media (image generation via Gemini Imagen)
- [x] CreditService for tracking AI usage

### Key Files Created
- `app/Services/AiService.php`
- `app/Services/CreditService.php`
- `app/Services/PlacesService.php`
- `app/Http/Controllers/AiModeController.php`, `AuditController.php`
- `app/Http/Controllers/CompetitorController.php`, `KeywordController.php`
- `app/Http/Controllers/RankCheckerController.php`, `OptimizationController.php`
- `app/Http/Controllers/AiMediaController.php`
- `app/Models/AiMedia.php`, `CreditLedger.php`

---

## Phase 4: Social & Communication ✅ (Completed)

**Goal:** Multi-platform social posting, leads CRM, WhatsApp integration.

### Deliverables
- [x] Social Studio — compose and publish to Facebook, Instagram, LinkedIn, X
- [x] Live post preview with platform-specific styling
- [x] Image upload (drag & drop) and URL support
- [x] Post scheduling
- [x] Meta OAuth for Facebook/Instagram
- [x] MetaService for Graph API posting
- [x] Leads CRM with Kanban pipeline
- [x] WhatsApp messaging via Cloud API
- [x] Ad reports view

### Key Files Created
- `app/Http/Controllers/SocialController.php`
- `app/Http/Controllers/MetaOAuthController.php`
- `app/Http/Controllers/LeadController.php`
- `app/Http/Controllers/WhatsappController.php`
- `app/Http/Controllers/AdController.php`
- `app/Services/MetaService.php`
- `app/Models/SocialPost.php`, `Lead.php`, `WhatsappMessage.php`, `AdReport.php`

---

## Phase 5: Billing & Monetization ✅ (Completed)

**Goal:** Plan-based subscriptions, credit packs, Razorpay payments, invoicing.

### Deliverables
- [x] Plan model with GST-inclusive pricing, module permissions, feature lists
- [x] Self-serve plan upgrade page
- [x] Razorpay payment integration (order creation, verification)
- [x] Credit packages (admin-created, purchasable)
- [x] Credit ledger for audit trail
- [x] Invoicing module (invoices, customers, services, expenses)
- [x] Billing settings (GSTIN, bank details, signature)
- [x] Tally XML export
- [x] Payment history with invoice download

### Key Files Created
- `app/Models/Plan.php`, `Subscription.php`, `CreditPackage.php`, `Payment.php`
- `app/Services/RazorpayService.php`
- `app/Http/Controllers/BillingController.php`, `ClientBillingController.php`
- `app/Http/Controllers/PlanController.php`, `CreditPackageController.php`
- `app/Http/Controllers/Invoicing/*.php` (7 controllers)
- `app/Models/Invoice.php`, `InvoiceItem.php`, `Service.php`, etc.

---

## Phase 6: Admin Panel ✅ (Completed)

**Goal:** Super Admin management interface for users, plans, credits.

### Deliverables
- [x] Admin layout (`layouts/admin.blade.php`)
- [x] Admin overview — platform-wide KPIs, all users table, subscription summary
- [x] User management — card-based list, create/edit/delete, plan assignment
- [x] Plan management — card-based CRUD with GST, credits, module permissions
- [x] Credit package management — card-based CRUD with pause/activate
- [x] Role-based middleware (`EnsureRole`)
- [x] Admin can assign plans to users
- [x] No "scope to client" in user form (auto-handled)

### Key Files Created
- `app/Http/Controllers/AdminController.php`
- `app/Http/Middleware/EnsureRole.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/admin/overview.blade.php`, `users.blade.php`, `plans.blade.php`, `credit-packages.blade.php`

---

## Phase 7: Business Health & Smart UX ✅ (Completed)

**Goal:** Beacon-style Business Health Score, "Do Next" queue, smart billing flow.

### Deliverables
- [x] HealthScoreService — compute business health from real data
- [x] SVG circular gauge with animated arc on Overview
- [x] 6 sub-scores with progress bars (Reviews, Posts, Photos, Social, Leads, Connections)
- [x] "Do Next" prioritized action queue with impact badges
- [x] Module summary cards on Overview
- [x] Free vs Paid route separation (auth-only vs auth+plan)
- [x] EnsureActivePlan middleware returns 402 JSON for AJAX
- [x] Global `handlePlanRequired()` JS function for contextual upgrade prompts
- [x] Sidebar billing section hidden for users without plan
- [x] Billing sidebar link removed from admin panel

### Key Files Created/Modified
- `app/Services/HealthScoreService.php` (new)
- `app/Http/Middleware/EnsureActivePlan.php` (modified — AJAX 402 support)
- `routes/web.php` (restructured — split auth-only and auth+plan groups)
- `resources/views/dashboard/overview.blade.php` (Business Health Cockpit added)
- `resources/views/layouts/app.blade.php` (global plan modal, conditional billing sidebar)
- 10 blade views updated with `handlePlanRequired` check

---

## Phase 8: Future Roadmap 🔮

### Planned Features
- [ ] Automated review syncing (cron/scheduled task)
- [ ] Auto-reply to reviews based on rating rules
- [ ] Bulk actions (multi-select reviews, bulk reply)
- [ ] Client onboarding wizard
- [ ] Multi-language support
- [ ] Advanced analytics dashboard
- [ ] Custom report generation
- [ ] Webhook integrations
- [ ] Mobile app (or PWA)
- [ ] White-label support for agencies
- [ ] API for third-party integrations
- [ ] Automated CI/CD pipeline
- [ ] Automated test suite

### Technical Debt
- [ ] Add automated tests (PHPUnit)
- [ ] Implement job queues for long-running tasks
- [ ] Add database indexing optimization
- [ ] Implement proper error logging/monitoring
- [ ] Add rate limiting on AI endpoints
- [ ] Image optimization pipeline
