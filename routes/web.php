<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProjectWorkController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\GoogleDriveController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\AdminInvoiceController;
use App\Http\Controllers\Admin\MasterServiceController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectPhotoController as AdminProjectPhotoController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\SurfaceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Supervisor\PaymentController as SupervisorPaymentController;
use App\Http\Controllers\Supervisor\ProjectController as SupervisorProjectController;
use App\Http\Controllers\Supervisor\ProjectPhotoController;
use App\Http\Controllers\Supervisor\ProjectRoomController;
use App\Http\Controllers\Supervisor\QuoteItemController;
use App\Http\Controllers\Supervisor\ServiceController;
use App\Http\Controllers\Supervisor\SupervisorProjectWorkController;
use App\Http\Controllers\Supervisor\ZoneDashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// ============================================
// SUPERVISOR AUTHENTICATION ROUTES
// ============================================

// Supervisor Login Page
Route::get('/supervisor/login', [\App\Http\Controllers\Supervisor\SupervisorAuthController::class, 'create'])
    ->name('supervisor.login')
    ->middleware('guest');

// Supervisor Login Handle (Public - wrapped in web for session/CSRF)
Route::middleware('web')->group(function () {
    Route::post('/supervisor/login', [\App\Http\Controllers\Supervisor\SupervisorAuthController::class, 'store'])
        ->name('supervisor.login.store');
});

// ============================================
// ADMIN ROUTES - Uses auth:admin guard
// ============================================
Route::prefix('admin')->middleware(['auth:web', 'verified'])->group(function () {
    // Dashboard - Financial Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Admin Profile Routes
    Route::get('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'updatePassword'])->name('admin.profile.password.update');

    // Surface CRUD
    Route::resource('surfaces', SurfaceController::class)->names('admin.surfaces');

    // Product CRUD
    Route::resource('products', ProductController::class)->names('admin.products');

    // Service CRUD
    Route::resource('services', MasterServiceController::class)->names('admin.services');

    // User CRUD
    Route::resource('users', UserController::class)->names('admin.users');

    // User Custom Actions
    Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('admin.users.activate');
    Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('admin.users.deactivate');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');

    // Quotes
    Route::get('/quotes', [QuoteController::class, 'index'])->name('admin.quotes.index');

    // Inquiries
    Route::get('/inquiries', [InquiryController::class, 'index'])->name('admin.inquiries.index');
    Route::post('/inquiries/{id}/book-visit', [InquiryController::class, 'bookVisit'])->name('admin.inquiries.bookVisit');

    // Projects (Home Visits)
    Route::resource('projects', ProjectController::class)->names('admin.projects');

    // View customer quote (same view as customer sees)
    Route::get('/projects/{project}/quote', [ProjectController::class, 'viewQuote'])->name('admin.projects.quote');

    // Send WhatsApp message for home visit
    Route::post('/projects/{project}/send-whatsapp', [ProjectController::class, 'sendWhatsAppMessage'])->name('admin.projects.send-whatsapp');

    // Payment Routes - Admin can manually confirm/change payments
    Route::post('/projects/{project}/confirm-cash', [AdminPaymentController::class, 'confirmCashPayment'])->name('admin.projects.confirm-cash');
    Route::post('/projects/{project}/collect-mid', [AdminPaymentController::class, 'collectMidPayment'])->name('admin.projects.collect-mid');
    Route::post('/projects/{project}/collect-final', [AdminPaymentController::class, 'collectFinalPayment'])->name('admin.projects.collect-final');
    Route::post('/projects/{project}/mark-booking-paid', [AdminPaymentController::class, 'markBookingPaid'])->name('admin.projects.mark-booking-paid');
    Route::post('/projects/{project}/mark-mid-paid', [AdminPaymentController::class, 'markMidPaid'])->name('admin.projects.mark-mid-paid');
    Route::post('/projects/{project}/mark-final-paid', [AdminPaymentController::class, 'markFinalPaid'])->name('admin.projects.mark-final-paid');

    // Work Status Routes - Admin has full control
    Route::post('/projects/{project}/work-status', [AdminProjectWorkController::class, 'updateStatus'])->name('admin.projects.work-status');

    // Payment Gateway Settings
    Route::get('/payment-gateways', [PaymentGatewayController::class, 'index'])->name('admin.payment-gateways.index');
    Route::post('/payment-gateways', [PaymentGatewayController::class, 'update'])->name('admin.payment-gateways.update');

    // Google Drive OAuth Routes
    Route::get('/google-drive/auth', [GoogleDriveController::class, 'redirectToGoogle'])->name('admin.google-drive.auth');
    Route::get('/google-drive/callback', [GoogleDriveController::class, 'handleGoogleCallback'])->name('admin.google-drive.callback');

    // Project Photos
    Route::get('/projects/{project}/photos', [AdminProjectPhotoController::class, 'index'])->name('admin.project-photos.index');
    Route::delete('/projects/{project}/photos/{photo}', [AdminProjectPhotoController::class, 'destroy'])->name('admin.project-photos.destroy');

    // Coupon Management
    Route::resource('coupons', CouponController::class)->names('admin.coupons');
    Route::post('/coupons/{coupon}/toggle', [CouponController::class, 'toggleStatus'])->name('admin.coupons.toggle');

    // Invoice Routes
    Route::get('/projects/{project}/invoice', [InvoiceController::class, 'view'])->name('admin.invoice.view');
    Route::get('/projects/{project}/invoice/download', [InvoiceController::class, 'generateInvoice'])->name('admin.invoice.download');
    Route::post('/projects/{project}/invoice/regenerate', [InvoiceController::class, 'regenerateInvoice'])->name('admin.invoice.regenerate');
    Route::get('/projects/{project}/invoice/new', [AdminInvoiceController::class, 'view'])->name('admin.invoice.new');

    // Warranty Routes
    Route::get('/projects/{project}/warranty', [\App\Http\Controllers\Admin\AdminWarrantyController::class, 'view'])->name('admin.warranty.view');
    Route::get('/projects/{project}/warranty/download', [\App\Http\Controllers\Admin\AdminWarrantyController::class, 'view'])->name('admin.warranty.download');

    // Settings Route
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('admin.settings.update');
});

// ============================================
// SUPERVISOR ROUTES - Uses auth:web with role-based access
// ============================================
Route::middleware(['auth:web', 'verified'])->prefix('supervisor')->name('supervisor.')->group(function () {
    // Dashboard
    Route::get('/dashboard', fn() => Inertia::render('Supervisor/Dashboard'))->name('dashboard');

    // Projects
    Route::resource('projects', SupervisorProjectController::class);

    // Screen A: Create Zone (Room)
    Route::get('/projects/{project}/zones/create', [ProjectRoomController::class, 'create'])->name('zones.create');
    Route::post('/projects/{project}/zones', [ProjectRoomController::class, 'store'])->name('zones.store');

    // Screen B: Zone Dashboard (Hub)
    Route::get('/zones/{projectRoom}', [ZoneDashboardController::class, 'index'])->name('zones.show');
    Route::put('/zones/{projectRoom}', [ProjectRoomController::class, 'update'])->name('zones.update');

    // Screen C: Paint Items
    Route::get('/zones/{projectRoom}/paint', [QuoteItemController::class, 'create'])->name('zones.paint.create');
    Route::post('/zones/{projectRoom}/paint', [QuoteItemController::class, 'store'])->name('zones.paint.store');
    Route::get('/zones/{projectRoom}/paint/{item}/edit', [QuoteItemController::class, 'edit'])->name('zones.paint.edit');
    Route::put('/zones/{projectRoom}/paint/{item}', [QuoteItemController::class, 'update'])->name('zones.paint.update');
    Route::get('/products/{product}/systems', [QuoteItemController::class, 'getSystems'])->name('products.systems');

    // Screen D: Service/Repair
    Route::get('/zones/{projectRoom}/service', [ServiceController::class, 'create'])->name('zones.service.create');
    Route::post('/zones/{projectRoom}/service', [ServiceController::class, 'store'])->name('zones.service.store');
    Route::get('/zones/{projectRoom}/service/{quoteService}/edit', [ServiceController::class, 'edit'])->name('zones.service.edit');
    Route::put('/zones/{projectRoom}/service/{quoteService}', [ServiceController::class, 'update'])->name('zones.service.update');

    // Service store with FormData (for Zones/Show.vue modal)
    Route::post('/zones/{projectRoom}/services', [ServiceController::class, 'storeFormData'])->name('zones.services.store');

    // Duplicate Zone
    Route::post('/zones/{projectRoom}/duplicate', [ProjectRoomController::class, 'duplicate'])->name('zones.duplicate');

    // Screen E: Summary
    Route::get('/projects/{project}/summary', [\App\Http\Controllers\Supervisor\SummaryController::class, 'show'])->name('summary');
    Route::post('/projects/{project}/finalize', [\App\Http\Controllers\Supervisor\SummaryController::class, 'finalize'])->name('finalize');
    Route::get('/projects/{project}/pdf', [\App\Http\Controllers\Supervisor\SummaryController::class, 'generatePdf'])->name('pdf');
    Route::post('/projects/{project}/send-whatsapp', [\App\Http\Controllers\Supervisor\SummaryController::class, 'sendWhatsAppMessage'])->name('summary.send-whatsapp');

    // Coupon Routes
    Route::post('/projects/{project}/apply-coupon', [\App\Http\Controllers\Supervisor\CouponApplyController::class, 'apply'])->name('projects.apply-coupon');
    Route::post('/projects/{project}/remove-coupon', [\App\Http\Controllers\Supervisor\CouponApplyController::class, 'remove'])->name('projects.remove-coupon');

    // Payment Routes - Supervisor can confirm cash payments
    Route::post('/projects/{project}/confirm-cash-booking', [SupervisorPaymentController::class, 'confirmCashBooking'])->name('projects.confirm-cash-booking');
    Route::post('/projects/{project}/confirm-cash', [SupervisorPaymentController::class, 'confirmCashPayment'])->name('projects.confirm-cash');
    Route::post('/projects/{project}/collect-mid', [SupervisorPaymentController::class, 'collectMidPayment'])->name('projects.collect-mid');
    Route::post('/projects/{project}/collect-final', [SupervisorPaymentController::class, 'collectFinalPayment'])->name('projects.collect-final');

    // Work Status Routes - Supervisor has limited control
    Route::post('/projects/{project}/work-status', [SupervisorProjectWorkController::class, 'updateStatus'])->name('projects.work-status');

    // Project Photos
    Route::get('/projects/{project}/photos', [ProjectPhotoController::class, 'index'])->name('photos.index');
    Route::post('/projects/{project}/photos', [ProjectPhotoController::class, 'store'])->name('photos.store');
    Route::delete('/projects/{project}/photos/{photo}', [ProjectPhotoController::class, 'destroy'])->name('photos.destroy');

    // Warranty Routes (Read-only for Supervisor)
    Route::get('/projects/{project}/warranty', [\App\Http\Controllers\Admin\AdminWarrantyController::class, 'view'])->name('warranty.view');

    // Logout
    Route::post('/logout', [\App\Http\Controllers\Supervisor\SupervisorAuthController::class, 'destroy'])->name('logout');
});

// Dashboard redirect - role-based redirect
Route::get('/dashboard', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    if ($user) {
        if ($user->role === 'ADMIN') {
            return redirect()->intended('/admin/projects');
        }
        if ($user->role === 'SUPERVISOR') {
            return redirect()->intended('/supervisor/projects');
        }
    }
    // If no user or invalid role, redirect to login
    return redirect()->route('login');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================
// CUSTOMER AUTHENTICATION ROUTES (WhatsApp OTP)
// ============================================

// Customer Login Page
Route::get('/customer/login', [\App\Http\Controllers\Customer\CustomerAuthController::class, 'showLogin'])
    ->name('customer.login');

// Customer OTP Auth Routes (Public) - wrapped in web middleware for session/CSRF support
Route::middleware('web')->group(function () {
    Route::prefix('customer/auth')->name('customer.auth.')->group(function () {
        Route::post('/send-otp', [\App\Http\Controllers\Customer\CustomerAuthController::class, 'sendOtp'])->name('send-otp');
        Route::post('/verify-otp', [\App\Http\Controllers\Customer\CustomerAuthController::class, 'verifyOtp'])->name('verify-otp');
    });
});

// Customer Protected Routes (all require login)
Route::middleware(['auth:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'index'])->name('dashboard');

    // Debug route
    Route::get('/debug-dashboard', [\App\Http\Controllers\Customer\DebugDashboardController::class, 'index'])->name('debug-dashboard');

    // Quote View (Authenticated - no token needed, uses session)
    Route::get('/quote/{project}', [\App\Http\Controllers\Customer\CustomerQuoteController::class, 'show'])->name('quote.show');

    // Progress Photos (Authenticated - no token needed)
    Route::get('/project/{project}/photos', [\App\Http\Controllers\Customer\ProjectPhotoController::class, 'show'])->name('project.photos');

    // Payment Endpoints (Authenticated only - NoBroker style)
    Route::get('/project/{project}/milestone/{milestone}', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'getMilestoneDetails'])->name('project.milestone.details');
    Route::post('/project/{project}/booking/online', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'onlineBooking'])->name('project.booking.online');
    Route::post('/project/{project}/booking/cash', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'cashBooking'])->name('project.booking.cash');
    Route::post('/project/{project}/mid-payment', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'payMidPayment'])->name('project.pay-mid');
    Route::post('/project/{project}/final-payment', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'payFinalPayment'])->name('project.pay-final');

    // Dedicated Payment Page (Checkout Style)
    Route::get('/payment/{project}/{milestone}', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'showPaymentPage'])->name('payment.page');

    // Billing Details
    Route::post('/project/{project}/billing-details', [\App\Http\Controllers\Customer\CustomerPaymentController::class, 'saveBillingDetails'])->name('project.billing-details');

    // Invoice (View-only - after full payment)
    Route::get('/project/{project}/invoice', [\App\Http\Controllers\Customer\CustomerInvoiceController::class, 'view'])->name('project.invoice');
    Route::get('/project/{project}/invoice/download', [\App\Http\Controllers\Customer\CustomerInvoiceController::class, 'download'])->name('project.invoice.download');

    // Warranty (View-only - after full payment and work completion)
    Route::get('/project/{project}/warranty', [\App\Http\Controllers\Customer\CustomerWarrantyController::class, 'view'])->name('customer.project.warranty');

    // Payment History
    Route::get('/payment-history', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'paymentHistory'])->name('payment.history');

    // Work Progress
    Route::get('/work-progress', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'workProgress'])->name('work.progress');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Customer\CustomerProfileController::class, 'index'])->name('profile');
    Route::patch('/profile', [\App\Http\Controllers\Customer\CustomerProfileController::class, 'update'])->name('profile.update');
});

// Customer Logout Route (POST only, accessible without auth for CSRF handling)
Route::post('/customer/logout', [\App\Http\Controllers\Customer\CustomerAuthController::class, 'logout'])
    ->name('customer.logout')
    ->middleware('web');

// ============================================
// CCAAVENUE PAYMENT GATEWAY ROUTES
// ============================================

// CCavenue payment initiation (for any milestone)
Route::post('/payment/ccavenue/initiate/{project}/{milestone}', [\App\Http\Controllers\Payment\CCavenueController::class, 'initiate'])
    ->name('payment.ccavenue.initiate');

// CCavenue callback URL (called by CCAvenue after payment)
Route::get('/payment/ccavenue/callback', [\App\Http\Controllers\Payment\CCavenueController::class, 'callback'])
    ->name('payment.ccavenue.callback');

// CCavenue cancel URL (called when user cancels payment)
Route::get('/payment/ccavenue/cancel', [\App\Http\Controllers\Payment\CCavenueController::class, 'cancel'])
    ->name('payment.ccavenue.cancel');

// Payment success page
Route::get('/payment/success/{project}', [\App\Http\Controllers\Payment\CCavenueController::class, 'success'])
    ->name('payment.success');

// Payment failed page
Route::get('/payment/failed', [\App\Http\Controllers\Payment\CCavenueController::class, 'failed'])
    ->name('payment.failed');

// Get CCAvenue config (for frontend)
Route::get('/payment/ccavenue/config', [\App\Http\Controllers\Payment\CCavenueController::class, 'getConfig'])
    ->name('payment.ccavenue.config');

require __DIR__.'/auth.php';
