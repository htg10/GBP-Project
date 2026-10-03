<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\GbpContentController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\InsightsController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\WhatsappController;
use App\Http\Controllers\Api\AdController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\CompetitorController;
use App\Http\Controllers\Api\KeywordController;
use App\Http\Controllers\Api\RankCheckerController;
use App\Http\Controllers\Api\OptimizationController;
use App\Http\Controllers\Api\AiModeController;
use App\Http\Controllers\Api\AiMediaController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\InvoicingController;

Route::get('/ping', fn() => ['ok' => true]);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/auth/google', [AuthController::class, 'googleLogin'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {

    // ---- Auth & Profile ----
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::get('/subscription', [AuthController::class, 'subscription']);

    // ---- Dashboard ----
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/dashboard/sync', [DashboardController::class, 'syncAll']);

    // ---- Clients ----
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::put('/clients/{client}', [ClientController::class, 'update']);
    Route::delete('/clients/{client}', [ClientController::class, 'destroy']);
    Route::post('/clients/{client}/locations', [ClientController::class, 'storeLocation']);
    Route::delete('/clients/{client}/locations/{location}', [ClientController::class, 'destroyLocation']);
    Route::post('/clients/{client}/import-locations', [ClientController::class, 'importLocations']);

    // ---- Reviews ----
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{location}', [ReviewController::class, 'show']);
    Route::post('/reviews/{location}/sync', [ReviewController::class, 'sync']);
    Route::post('/reviews/settings', [ReviewController::class, 'updateSettings']);
    Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply']);

    // ---- GBP Content ----
    Route::get('/gbp-content', [GbpContentController::class, 'index']);
    Route::post('/gbp-content/posts', [GbpContentController::class, 'storePost']);
    Route::put('/gbp-content/posts/{post}', [GbpContentController::class, 'updatePost']);
    Route::delete('/gbp-content/posts/{post}', [GbpContentController::class, 'destroyPost']);
    Route::post('/gbp-content/posts/{post}/publish', [GbpContentController::class, 'publishPost']);
    Route::post('/gbp-content/photos', [GbpContentController::class, 'storePhoto']);
    Route::delete('/gbp-content/photos/{photo}', [GbpContentController::class, 'destroyPhoto']);
    Route::post('/gbp-content/photos/{photo}/publish', [GbpContentController::class, 'publishPhoto']);

    // ---- Leads ----
    Route::get('/leads', [LeadController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::put('/leads/{lead}/move', [LeadController::class, 'move']);
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy']);

    // ---- Social ----
    Route::get('/social', [SocialController::class, 'index']);
    Route::post('/social', [SocialController::class, 'store']);
    Route::post('/social/{post}/retry', [SocialController::class, 'retry']);

    // ---- WhatsApp ----
    Route::get('/whatsapp', [WhatsappController::class, 'index']);
    Route::post('/whatsapp', [WhatsappController::class, 'send']);

    // ---- Ads ----
    Route::get('/ads', [AdController::class, 'index']);
    Route::post('/ads', [AdController::class, 'store']);

    // ---- Insights ----
    Route::get('/insights', [InsightsController::class, 'index']);
    Route::get('/insights/download', [InsightsController::class, 'download']);

    // ---- Team ----
    Route::get('/team', [TeamController::class, 'index']);
    Route::post('/team', [TeamController::class, 'store']);
    Route::put('/team/{user}', [TeamController::class, 'update']);
    Route::delete('/team/{user}', [TeamController::class, 'destroy']);

    // ---- Billing ----
    Route::get('/billing/plans', [BillingController::class, 'plans']);
    Route::get('/billing/credits', [BillingController::class, 'credits']);
    Route::post('/billing/upgrade', [BillingController::class, 'upgrade']);
    Route::post('/billing/buy-credits', [BillingController::class, 'buyCredits']);
    Route::post('/billing/checkout', [BillingController::class, 'checkout']);
    Route::post('/billing/verify', [BillingController::class, 'verify']);
    Route::post('/billing/credit-checkout', [BillingController::class, 'creditCheckout']);
    Route::post('/billing/credit-verify', [BillingController::class, 'creditVerify']);

    // ---- Invoicing ----
    Route::get('/invoicing/invoices', [InvoicingController::class, 'invoices']);
    Route::post('/invoicing/invoices', [InvoicingController::class, 'createInvoice']);
    Route::get('/invoicing/invoices/{invoice}', [InvoicingController::class, 'showInvoice']);
    Route::post('/invoicing/invoices/{invoice}/send', [InvoicingController::class, 'markSent']);
    Route::post('/invoicing/invoices/{invoice}/paid', [InvoicingController::class, 'markPaid']);
    Route::delete('/invoicing/invoices/{invoice}', [InvoicingController::class, 'destroyInvoice']);
    Route::get('/invoicing/customers', [InvoicingController::class, 'customers']);
    Route::post('/invoicing/customers', [InvoicingController::class, 'storeCustomer']);
    Route::put('/invoicing/customers/{client}', [InvoicingController::class, 'updateCustomer']);
    Route::get('/invoicing/services', [InvoicingController::class, 'services']);
    Route::post('/invoicing/services', [InvoicingController::class, 'storeService']);
    Route::put('/invoicing/services/{service}', [InvoicingController::class, 'updateService']);
    Route::delete('/invoicing/services/{service}', [InvoicingController::class, 'destroyService']);
    Route::get('/invoicing/categories', [InvoicingController::class, 'categories']);
    Route::post('/invoicing/categories', [InvoicingController::class, 'storeCategory']);
    Route::delete('/invoicing/categories/{category}', [InvoicingController::class, 'destroyCategory']);
    Route::get('/invoicing/expenses', [InvoicingController::class, 'expenses']);
    Route::post('/invoicing/expenses', [InvoicingController::class, 'storeExpense']);
    Route::delete('/invoicing/expenses/{expense}', [InvoicingController::class, 'destroyExpense']);
    Route::get('/invoicing/settings', [InvoicingController::class, 'billingSettings']);
    Route::put('/invoicing/settings', [InvoicingController::class, 'updateBillingSettings']);

    // ---- AI Features (require active plan) ----
    Route::middleware('plan')->group(function () {
        Route::post('/reviews/{review}/generate', [ReviewController::class, 'generateReply']);
        Route::post('/gbp-content/generate-post', [GbpContentController::class, 'generatePostCopy']);
        Route::post('/gbp-content/generate-caption', [GbpContentController::class, 'generateCaption']);
        Route::post('/social/caption', [SocialController::class, 'caption']);
        Route::post('/audit', [AuditController::class, 'run']);
        Route::post('/competitors', [CompetitorController::class, 'run']);
        Route::post('/keywords', [KeywordController::class, 'generate']);
        Route::get('/rank-checker/locations', [RankCheckerController::class, 'locations']);
        Route::post('/rank-checker', [RankCheckerController::class, 'check']);
        Route::get('/optimization', [OptimizationController::class, 'index']);
        Route::post('/optimization/run', [OptimizationController::class, 'run']);
        Route::post('/ai-mode', [AiModeController::class, 'send']);
        Route::post('/ai-media/generate', [AiMediaController::class, 'generate']);
        Route::delete('/ai-media/{media}', [AiMediaController::class, 'destroy']);
        Route::post('/ai-media/{media}/use-as-photo', [AiMediaController::class, 'useAsPhoto']);
    });

    // ---- Admin (SUPER_ADMIN only) ----
    Route::middleware('role:SUPER_ADMIN')->prefix('admin')->group(function () {
        Route::get('/overview', [AdminController::class, 'overview']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser']);
        Route::get('/plans', [AdminController::class, 'plans']);
        Route::post('/plans', [AdminController::class, 'storePlan']);
        Route::put('/plans/{plan}', [AdminController::class, 'updatePlan']);
        Route::delete('/plans/{plan}', [AdminController::class, 'destroyPlan']);
        Route::get('/credit-packages', [AdminController::class, 'creditPackages']);
        Route::post('/credit-packages', [AdminController::class, 'storeCreditPackage']);
        Route::put('/credit-packages/{package}', [AdminController::class, 'updateCreditPackage']);
        Route::post('/credit-packages/{package}/toggle', [AdminController::class, 'toggleCreditPackage']);
        Route::delete('/credit-packages/{package}', [AdminController::class, 'destroyCreditPackage']);
        Route::post('/topup', [AdminController::class, 'topup']);
    });
});
