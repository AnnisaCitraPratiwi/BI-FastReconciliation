<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\Auth\ForgotPasswordController; 
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReconciliationHistoryController;


// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/registration-success', [AuthController::class, 'showRegistrationSuccess'])->name('registration.success');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->middleware('guest')
    ->name('forgot.password');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');

// Protected routes
Route::middleware(['auth'])->group(function () {
    
    // Master only routes
    Route::middleware(['role:master'])->group(function () {
        Route::get('/master/dashboard', [DashboardController::class, 'masterDashboard'])->name('master.dashboard');
        
        // Master user management routes
        Route::prefix('master')->group(function () {
            Route::get('/manage-users', [MasterController::class, 'manageUsers'])->name('master.manage_users');
            Route::post('/users', [MasterController::class, 'storeUser'])->name('master.store_user');
            Route::put('/users/{user}', [MasterController::class, 'updateUser'])->name('master.update_user');
            Route::delete('/users/{user}', [MasterController::class, 'destroyUser'])->name('master.destroy_user');
            Route::post('/users/{user}/approve', [MasterController::class, 'approveUser'])->name('master.approve_user');
        });
        
        // Legacy users resource route for backward compatibility
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::get('/master/recon-history', [ReconciliationHistoryController::class, 'index'])->name('master.recon-history');
        Route::delete('/master/recon-history/{id}',[ReconciliationHistoryController::class, 'destroy'])->name('master.destroy-history');
    }); 


    // Administrator only routes
    Route::middleware(['role:administrator|master'])->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::post('/profile/update-photo', [ProfileController::class, 'updatePhoto'])->name('profile.update-photo');
        
        // Reconciliation routes
        Route::prefix('reconciliation')->group(function () {
            Route::get('/', [ReconciliationController::class, 'index'])->name('reconciliation');
            
            // Upload routes terpisah
            Route::post('/upload-cip', [ReconciliationController::class, 'uploadCip'])->name('upload.cip');
            Route::post('/upload-ams', [ReconciliationController::class, 'uploadAms'])->name('upload.ams');
            Route::post('/upload-bs', [ReconciliationController::class, 'uploadBs'])->name('upload.bs');
            
            // Data management routes
            Route::get('/view-data', [ReconciliationController::class, 'viewData'])->name('reconciliation.view.data'); 
            Route::post('/process', [ReconciliationController::class, 'processReconciliation'])->name('reconciliation.process');
            Route::post('/process-detailed', [ReconciliationController::class, 'processDetailedReconciliation'])->name('reconciliation.process.detailed'); 
            Route::post('/reconciliation/process-job', [ReconciliationController::class, 'processJob'])->name('reconciliation.process.job');
            Route::post('/reconciliation/process-direct', [ReconciliationController::class, 'processDirect'])->name('reconciliation.process.direct');
             
            Route::get('/history', [ReconciliationController::class, 'history'])->name('reconciliation.history');
            Route::get('/history/{id}', [ReconciliationController::class, 'viewReconciliationHistory'])->name('reconciliation.history.view');
            Route::get('/history/download/{id}/{type}', [ReconciliationController::class, 'downloadHistoryFile'])->name('reconciliation.history.download');
            Route::get('/history/{id}/view', [ReconciliationController::class, 'viewHistoryDetail'])->name('reconciliation.history.view');
 
            // Clear data routes
            Route::delete('/clear-all', [ReconciliationController::class, 'clearAllData'])->name('reconciliation.clear.all');
            Route::delete('/clear-date', [ReconciliationController::class, 'clearDataByDate'])->name('reconciliation.clear.date');
            
            // Export routes 
            Route::get('/export-all-anomalies', [ReconciliationController::class, 'exportAllAnomalies'])->name('reconciliation.export.all'); 
            Route::get('/export-all-anomalies-pdf', [ReconciliationController::class, 'exportAllAnomaliesPdf'])->name('reconciliation.export.pdf');

        });
        
        // Upload history routes
        Route::get('/upload-history', [ReconciliationController::class, 'getUploadHistory'])->name('upload.history');
        Route::get('/records-by-date', [ReconciliationController::class, 'getRecordsByDate'])->name('records.by.date');
 
    }); 
});
