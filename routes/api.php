<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\CropProductionController;
use App\Http\Controllers\LivestockStatisticController;
use App\Http\Controllers\NewsUpdateController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ContactInquiryController;
use App\Http\Controllers\NewsletterSubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes - no authentication required
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Public API endpoints
Route::get('projects', [ProjectController::class, 'index']); // Public can view projects
Route::get('projects/{project}', [ProjectController::class, 'show']);
Route::get('news-updates', [NewsUpdateController::class, 'index']); // Public news
Route::get('news-updates/{newsUpdate}', [NewsUpdateController::class, 'show']);
Route::get('documents', [DocumentController::class, 'index']); // Public documents
Route::get('documents/{document}', [DocumentController::class, 'show']);
Route::get('crop-productions', [CropProductionController::class, 'index']); // Public agricultural data
Route::get('livestock-statistics', [LivestockStatisticController::class, 'index']);

// Public contact and newsletter
Route::post('contact-inquiries', [ContactInquiryController::class, 'store']); // Anyone can submit inquiry
Route::post('newsletter-subscriptions', [NewsletterSubscriptionController::class, 'store']); // Anyone can subscribe

Route::get('test', function () {
    return ['status' => 'success', 'message' => 'API is working'];
});

// Protected routes - authentication required
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [AuthController::class, 'logout']);

    // Departments (Internal only)
    Route::apiResource('departments', DepartmentController::class);

    // Projects (Full CRUD for authenticated users)
    Route::post('projects', [ProjectController::class, 'store']);
    Route::put('projects/{project}', [ProjectController::class, 'update']);
    Route::delete('projects/{project}', [ProjectController::class, 'destroy']);

    // Progress Reports
    Route::apiResource('progress-reports', ProgressReportController::class);

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
});