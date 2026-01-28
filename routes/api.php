<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProgressReportImageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\CropProductionController;
use App\Http\Controllers\LivestockStatisticController;
use App\Http\Controllers\NewsUpdateController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ContactInquiryController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\ProjectImageController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\DepartmentReportController;
use App\Http\Controllers\ProjectApprovalController;
use App\Http\Controllers\ProjectDisbursementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes - no authentication required
Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Password Reset Routes
Route::post('password/forgot', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1')->name('password.email');
Route::post('password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.reset');

// Public API endpoints
Route::get('projects', [ProjectController::class, 'index']); // Public can view projects
// Note: Specific project routes are defined below in the auth middleware to avoid route conflicts
Route::get('news-updates', [NewsUpdateController::class, 'index']); // Public news
Route::get('news-updates/{newsUpdate}', [NewsUpdateController::class, 'show']);
Route::get('documents', [DocumentController::class, 'index']); // Public documents
Route::get('documents/featured', [DocumentController::class, 'featured']); // Featured documents for homepage
Route::get('documents/{document}', [DocumentController::class, 'show']);
Route::post('documents/{document}/download', [DocumentController::class, 'download']); // Download with tracking
Route::get('crop-productions', [CropProductionController::class, 'index']); // Public agricultural data
Route::get('livestock-statistics', [LivestockStatisticController::class, 'index']);

// Public contact and newsletter (with rate limiting to prevent spam)
Route::post('contact-inquiries', [ContactInquiryController::class, 'store'])->middleware('throttle:10,1'); // 10 submissions per minute
Route::post('newsletter-subscriptions', [NewsletterSubscriptionController::class, 'store'])->middleware('throttle:5,1'); // 5 subscriptions per minute

Route::get('test', function () {
    return ['status' => 'success', 'message' => 'API is working'];
});

// Dashboard Analytics (Public endpoints for dashboard data)
Route::prefix('dashboard')->group(function () {
    Route::get('overview', [DashboardController::class, 'overview']);
    Route::get('budget-allocation', [DashboardController::class, 'budgetAllocation']);
    Route::get('project-status-distribution', [DashboardController::class, 'projectStatusDistribution']);
    Route::get('national-performance', [DashboardController::class, 'nationalPerformance']);
    Route::get('recent-updates', [DashboardController::class, 'recentUpdates']);
    Route::get('monthly-progress', [DashboardController::class, 'monthlyProgress']);
});

// Location Management (Public endpoints for filtering)
Route::prefix('locations')->group(function () {
    Route::get('/regions', [LocationController::class, 'regions']);
    Route::get('/regions/{region}', [LocationController::class, 'showRegion']);
    Route::get('/provinces', [LocationController::class, 'provinces']);
    Route::get('/provinces/{province}', [LocationController::class, 'showProvince']);
    Route::get('/municipalities', [LocationController::class, 'municipalities']);
    Route::get('/municipalities/{municipality}', [LocationController::class, 'showMunicipality']);
    Route::get('/hierarchy', [LocationController::class, 'hierarchy']);
    Route::get('/search', [LocationController::class, 'search']);
    Route::get('/statistics', [LocationController::class, 'statistics']);
});

// Protected routes - authentication required
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('password/change', [AuthController::class, 'changePassword']);

    // Departments (Internal only)
    Route::apiResource('departments', DepartmentController::class);

    // Projects (Full CRUD for authenticated users)
    Route::post('projects', [ProjectController::class, 'store']);
    Route::put('projects/{project}', [ProjectController::class, 'update']);
    Route::delete('projects/{project}', [ProjectController::class, 'destroy']);

    // Project Approval Workflow
    Route::prefix('projects')->group(function () {
        Route::get('/pending-approval', [ProjectApprovalController::class, 'pendingApproval']);
        Route::get('/approval-statistics', [ProjectApprovalController::class, 'statistics']);
        Route::get('/by-approval-status', [ProjectApprovalController::class, 'byApprovalStatus']);
        Route::post('/{project}/submit-for-approval', [ProjectApprovalController::class, 'submitForApproval']);
        Route::post('/{project}/approve', [ProjectApprovalController::class, 'approve']);
        Route::post('/{project}/reject', [ProjectApprovalController::class, 'reject']);
        Route::post('/{project}/request-changes', [ProjectApprovalController::class, 'requestChanges']);
        Route::get('/{project}/approval-history', [ProjectApprovalController::class, 'approvalHistory']);
    });

    // Progress Reports
    Route::prefix('progress-reports')->group(function () {
        Route::get('/with-issues', [ProgressReportController::class, 'withIssues']);
        Route::get('/statistics', [ProgressReportController::class, 'statistics']);
    });
    Route::apiResource('progress-reports', ProgressReportController::class);

    // Project Progress Timeline (enhanced progress tracking)
    Route::get('projects/{project}/progress-timeline', [ProgressReportController::class, 'progressTimeline']);

    // Agricultural Data (Internal management)
    Route::post('crop-productions', [CropProductionController::class, 'store']);
    Route::put('crop-productions/{cropProduction}', [CropProductionController::class, 'update']);
    Route::delete('crop-productions/{cropProduction}', [CropProductionController::class, 'destroy']);

    Route::post('livestock-statistics', [LivestockStatisticController::class, 'store']);
    Route::put('livestock-statistics/{livestockStatistic}', [LivestockStatisticController::class, 'update']);
    Route::delete('livestock-statistics/{livestockStatistic}', [LivestockStatisticController::class, 'destroy']);

    // News Updates (Full CRUD)
    Route::post('news-updates', [NewsUpdateController::class, 'store']);
    Route::put('news-updates/{newsUpdate}', [NewsUpdateController::class, 'update']);
    Route::delete('news-updates/{newsUpdate}', [NewsUpdateController::class, 'destroy']);

    // Documents (Full CRUD)
    Route::post('documents', [DocumentController::class, 'store']);
    Route::put('documents/{document}', [DocumentController::class, 'update']);
    Route::delete('documents/{document}', [DocumentController::class, 'destroy']);

    // Contact Inquiries (Internal management)
    Route::get('contact-inquiries', [ContactInquiryController::class, 'index']);
    Route::get('contact-inquiries/{contactInquiry}', [ContactInquiryController::class, 'show']);
    Route::put('contact-inquiries/{contactInquiry}', [ContactInquiryController::class, 'update']); // Update status
    Route::delete('contact-inquiries/{contactInquiry}', [ContactInquiryController::class, 'destroy']);

    // Newsletter Subscriptions (Internal management)
    Route::get('newsletter-subscriptions', [NewsletterSubscriptionController::class, 'index']);
    Route::get('newsletter-subscriptions/{newsletterSubscription}', [NewsletterSubscriptionController::class, 'show']);
    Route::put('newsletter-subscriptions/{newsletterSubscription}', [NewsletterSubscriptionController::class, 'update']);
    Route::delete('newsletter-subscriptions/{newsletterSubscription}', [NewsletterSubscriptionController::class, 'destroy']);

    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'index']);
        Route::post('/', [UserManagementController::class, 'store']);
        Route::get('/statistics', [UserManagementController::class, 'statistics']);
        Route::get('/{user}', [UserManagementController::class, 'show']);
        Route::put('/{user}', [UserManagementController::class, 'update']);
        Route::delete('/{user}', [UserManagementController::class, 'destroy']);
        Route::patch('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus']);
    });

    // Department Reports & KPI Tracking
    Route::prefix('departments')->group(function () {
        Route::get('/reports', [DepartmentReportController::class, 'index']);
        Route::get('/budget-utilization', [DepartmentReportController::class, 'budgetUtilization']);
        Route::get('/{department}/monthly-progress', [DepartmentReportController::class, 'monthlyProgress']);
        Route::get('/{department}/kpi-summary', [DepartmentReportController::class, 'kpiSummary']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/clear-all', [NotificationController::class, 'clearAll']);
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Project Disbursements & Financial Tracking
    Route::get('disbursement-categories', [ProjectDisbursementController::class, 'categories']);
    Route::prefix('projects/{project}')->group(function () {
        Route::get('/disbursements', [ProjectDisbursementController::class, 'index']);
        Route::post('/disbursements', [ProjectDisbursementController::class, 'store']);
        Route::get('/disbursements/{disbursement}', [ProjectDisbursementController::class, 'show']);
        Route::put('/disbursements/{disbursement}', [ProjectDisbursementController::class, 'update']);
        Route::delete('/disbursements/{disbursement}', [ProjectDisbursementController::class, 'destroy']);
        Route::post('/disbursements/{disbursement}/approve', [ProjectDisbursementController::class, 'approve']);
        Route::post('/disbursements/{disbursement}/cancel', [ProjectDisbursementController::class, 'cancel']);
        Route::get('/financial-summary', [ProjectDisbursementController::class, 'financialSummary']);
        Route::get('/disbursements-by-category', [ProjectDisbursementController::class, 'byCategory']);
        Route::get('/monthly-spending', [ProjectDisbursementController::class, 'monthlySpending']);
    });

    // Project Images
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('projects/{project}/images', [ProjectImageController::class, 'index']);
        Route::post('projects/{project}/images', [ProjectImageController::class, 'upload']);
        Route::put('projects/{project}/images/{image}', [ProjectImageController::class, 'update']);
        Route::delete('projects/{project}/images/{image}', [ProjectImageController::class, 'destroy']);

        // Progress Report Images
        Route::get('progress-reports/{report}/images', [ProgressReportImageController::class, 'index']);
        Route::post('progress-reports/{report}/images', [ProgressReportImageController::class, 'upload']);
        Route::put('progress-reports/{report}/images/{image}', [ProgressReportImageController::class, 'update']);
        Route::delete('progress-reports/{report}/images/{image}', [ProgressReportImageController::class, 'destroy']);
    });

});

// Public project show route (must be last to avoid conflicts with specific routes above)
Route::get('projects/{project}', [ProjectController::class, 'show']);