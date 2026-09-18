<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AiModeController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CompetitorController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\OptimizationController;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\GbpContentController;
use App\Http\Controllers\MetaOAuthController;
use App\Http\Controllers\RankCheckerController;
use App\Http\Controllers\AiMediaController;
use App\Http\Controllers\Invoicing\CustomerController;
use App\Http\Controllers\Invoicing\ServiceCategoryController;
use App\Http\Controllers\Invoicing\ServiceController;
use App\Http\Controllers\Invoicing\ExpenseController;
use App\Http\Controllers\Invoicing\InvoiceController;
use App\Http\Controllers\Invoicing\BillingSettingsController;
use App\Http\Controllers\Invoicing\TallyExportController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillingController;

// ---- Guest ----
Route::get('/', fn () => view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.login.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Google OAuth callback — kept OUTSIDE the auth group on purpose: the session
// cookie can be dropped across the Google redirect, so we don't require login
// here. The client/agency is recovered from the cached state instead.
Route::get('/google/callback', [GoogleOAuthController::class, 'callback'])->name('google.callback');

// Meta OAuth callback — kept OUTSIDE the auth group for the same reason as
// the Google one above (session cookie can drop across the redirect).
Route::get('/meta/callback', [MetaOAuthController::class, 'callback'])->name('meta.callback');

// Public raw-image endpoint — Google's servers fetch a photo's bytes from
// here when publishing it (sourceUrl must be a real fetchable URL, not a
// data: URI), so this has to stay outside the auth group.
Route::get('/media/gbp-photo/{photo}', [GbpContentController::class, 'rawPhoto'])->name('media.gbp-photo');
Route::get('/media/gbp-post-image/{post}', [GbpContentController::class, 'rawPostImage'])->name('media.gbp-post-image');

// Email verification (signed URL — no auth required for the verify link)
Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

// ---- Authenticated (all users) ----
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/sync', [DashboardController::class, 'syncAll'])->name('dashboard.sync');

    // Plans & upgrade (account owner / admin) — user-facing self-serve upgrade
    Route::get('/plans', [BillingController::class, 'plans'])->name('plans');
    Route::post('/plans/upgrade', [BillingController::class, 'upgrade'])->name('plans.upgrade');
    // Razorpay checkout for self-serve upgrade
    Route::post('/plans/checkout', [BillingController::class, 'checkout'])->name('plans.checkout');
    Route::post('/plans/verify', [BillingController::class, 'verify'])->name('plans.verify');

    // Client team management (Client Owner adds Staff / Marketing Managers)
    Route::get('/team', [\App\Http\Controllers\TeamController::class, 'index'])->name('team');
    Route::post('/team', [\App\Http\Controllers\TeamController::class, 'store'])->name('team.store');
    Route::post('/team/{user}', [\App\Http\Controllers\TeamController::class, 'update'])->name('team.update');
    Route::delete('/team/{user}', [\App\Http\Controllers\TeamController::class, 'destroy'])->name('team.destroy');

    // Client billing (subscription payment history + PDF invoices)
    Route::get('/billing', [\App\Http\Controllers\ClientBillingController::class, 'index'])->name('client-billing');
    Route::get('/billing/invoice/{payment}', [\App\Http\Controllers\ClientBillingController::class, 'invoice'])->name('client-billing.invoice');

    // Buy credits (client tops up when their credits run low)
    Route::get('/buy-credits', [BillingController::class, 'buyCredits'])->name('buy-credits');
    Route::post('/buy-credits/instant', [BillingController::class, 'creditBuyInstant'])->name('buy-credits.instant');
    Route::post('/buy-credits/checkout', [BillingController::class, 'creditCheckout'])->name('buy-credits.checkout');
    Route::post('/buy-credits/verify', [BillingController::class, 'creditVerify'])->name('buy-credits.verify');

    // Email verification
    Route::post('/email/send-verification', [App\Http\Controllers\EmailVerificationController::class, 'send'])->name('verification.send');
    Route::post('/email/change', [App\Http\Controllers\EmailVerificationController::class, 'changeEmail'])->name('email.change');

    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::post('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::post('/clients/{client}/locations', [ClientController::class, 'storeLocation'])->name('clients.locations.store');
    Route::delete('/clients/{client}/locations/{location}', [ClientController::class, 'destroyLocation'])->name('clients.locations.destroy');

    // Google connect (OAuth)
    Route::get('/clients/{client}/google/connect', [GoogleOAuthController::class, 'connect'])->name('google.connect');
    Route::delete('/clients/{client}/google/disconnect', [GoogleOAuthController::class, 'disconnect'])->name('google.disconnect');
    Route::post('/clients/{client}/google/import-locations', [ClientController::class, 'importLocations'])->name('clients.import-locations');

    // Reviews (GBP) — card grid of locations, then a scoped view per location.
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
    Route::post('/reviews/settings', [ReviewController::class, 'updateSettings'])->name('reviews.settings');
    Route::get('/reviews/{location}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::post('/reviews/{location}/sync', [ReviewController::class, 'sync'])->name('reviews.sync');
    Route::post('/reviews/{review}/generate', [ReviewController::class, 'generateReply'])->name('reviews.generate');
    Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');

    // GBP Posts & Photos
    Route::get('/gbp-content', [GbpContentController::class, 'index'])->name('gbp-content');
    Route::post('/gbp-content/posts', [GbpContentController::class, 'storePost'])->name('gbp-content.posts.store');
    Route::post('/gbp-content/posts/generate', [GbpContentController::class, 'generatePostCopy'])->name('gbp-content.posts.generate');
    Route::post('/gbp-content/posts/{post}/publish', [GbpContentController::class, 'publishExistingPost'])->name('gbp-content.posts.publish');
    Route::post('/gbp-content/posts/{post}', [GbpContentController::class, 'updatePost'])->name('gbp-content.posts.update');
    Route::delete('/gbp-content/posts/{post}', [GbpContentController::class, 'destroyPost'])->name('gbp-content.posts.destroy');
    Route::post('/gbp-content/photos', [GbpContentController::class, 'storePhoto'])->name('gbp-content.photos.store');
    Route::post('/gbp-content/photos/caption', [GbpContentController::class, 'generateCaption'])->name('gbp-content.photos.caption');
    Route::post('/gbp-content/photos/{photo}/publish', [GbpContentController::class, 'publishExistingPhoto'])->name('gbp-content.photos.publish');
    Route::delete('/gbp-content/photos/{photo}', [GbpContentController::class, 'destroyPhoto'])->name('gbp-content.photos.destroy');

    // Leads
    Route::get('/leads', [LeadController::class, 'index'])->name('leads');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::post('/leads/{lead}/move', [LeadController::class, 'move'])->name('leads.move');

    // Meta connect (OAuth) — mirrors Google connect, per client.
    Route::get('/clients/{client}/meta/connect', [MetaOAuthController::class, 'connect'])->name('meta.connect');
    Route::delete('/clients/{client}/meta/disconnect', [MetaOAuthController::class, 'disconnect'])->name('meta.disconnect');

    // Social
    Route::get('/social', [SocialController::class, 'index'])->name('social');
    Route::post('/social', [SocialController::class, 'store'])->name('social.store');
    Route::post('/social/caption', [SocialController::class, 'caption'])->name('social.caption');
    Route::post('/social/{post}/retry', [SocialController::class, 'retry'])->name('social.retry');

    // WhatsApp
    Route::get('/whatsapp', [WhatsappController::class, 'index'])->name('whatsapp');
    Route::post('/whatsapp/send', [WhatsappController::class, 'send'])->name('whatsapp.send');

    // Ads
    Route::get('/ads', [AdController::class, 'index'])->name('ads');
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');

    // Keyword Suggestion (AI)
    Route::get('/keywords', [KeywordController::class, 'index'])->name('keywords');
    Route::post('/keywords/generate', [KeywordController::class, 'generate'])->name('keywords.generate');

    // Local Rank Checker
    Route::get('/rank-checker', [RankCheckerController::class, 'index'])->name('rank-checker');
    Route::post('/rank-checker/check', [RankCheckerController::class, 'check'])->name('rank-checker.check');

    // AI Generated Media
    Route::get('/ai-media', [AiMediaController::class, 'index'])->name('ai-media');
    Route::post('/ai-media/generate', [AiMediaController::class, 'generate'])->name('ai-media.generate');
    Route::delete('/ai-media/{media}', [AiMediaController::class, 'destroy'])->name('ai-media.destroy');
    Route::post('/ai-media/{media}/use-as-photo', [AiMediaController::class, 'useAsPhoto'])->name('ai-media.use-as-photo');

    // Invoicing / accounting module
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/mark-sent', [InvoiceController::class, 'markSent'])->name('invoices.mark-sent');
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::post('/customers/{client}', [CustomerController::class, 'update'])->name('customers.update');

    Route::get('/services', [ServiceController::class, 'index'])->name('services');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::post('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    Route::get('/service-categories', [ServiceCategoryController::class, 'index'])->name('service-categories');
    Route::post('/service-categories', [ServiceCategoryController::class, 'store'])->name('service-categories.store');
    Route::delete('/service-categories/{category}', [ServiceCategoryController::class, 'destroy'])->name('service-categories.destroy');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::get('/credits', [BillingController::class, 'credits'])->name('credits');

    Route::get('/billing-settings', [BillingSettingsController::class, 'edit'])->name('billing-settings');
    Route::post('/billing-settings', [BillingSettingsController::class, 'update'])->name('billing-settings.update');

    Route::get('/tally-export', [TallyExportController::class, 'index'])->name('tally-export');
    Route::get('/tally-export/download', [TallyExportController::class, 'export'])->name('tally-export.download');

    // One-Click Optimization
    Route::get('/optimize', [OptimizationController::class, 'index'])->name('optimize');
    Route::post('/optimize/run', [OptimizationController::class, 'run'])->name('optimize.run');

    // AI Mode (chat assistant)
    Route::get('/ai', [AiModeController::class, 'index'])->name('ai');
    Route::post('/ai/send', [AiModeController::class, 'send'])->name('ai.send');

    // Google Audit
    Route::get('/audit', [AuditController::class, 'index'])->name('audit');
    Route::post('/audit/run', [AuditController::class, 'run'])->name('audit.run');

    // Competitor Analysis
    Route::get('/competitors', [CompetitorController::class, 'index'])->name('competitors');
    Route::post('/competitors/run', [CompetitorController::class, 'run'])->name('competitors.run');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// ---- Admin only ----
Route::middleware(['auth', 'role:SUPER_ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'overview'])->name('overview');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/verify', [BillingController::class, 'verify'])->name('billing.verify');
    Route::post('/billing/topup', [BillingController::class, 'topup'])->name('billing.topup');

    // Plans management (create/edit plans, per-plan access, GST)
    Route::get('/plans', [\App\Http\Controllers\PlanController::class, 'index'])->name('plans');
    Route::post('/plans', [\App\Http\Controllers\PlanController::class, 'store'])->name('plans.store');
    Route::post('/plans/{plan}', [\App\Http\Controllers\PlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [\App\Http\Controllers\PlanController::class, 'destroy'])->name('plans.destroy');

    // Credit packages (create/edit, pause/activate)
    Route::get('/credit-packages', [\App\Http\Controllers\CreditPackageController::class, 'index'])->name('credit-packages');
    Route::post('/credit-packages', [\App\Http\Controllers\CreditPackageController::class, 'store'])->name('credit-packages.store');
    Route::post('/credit-packages/{package}', [\App\Http\Controllers\CreditPackageController::class, 'update'])->name('credit-packages.update');
    Route::post('/credit-packages/{package}/toggle', [\App\Http\Controllers\CreditPackageController::class, 'toggle'])->name('credit-packages.toggle');
    Route::delete('/credit-packages/{package}', [\App\Http\Controllers\CreditPackageController::class, 'destroy'])->name('credit-packages.destroy');
});
