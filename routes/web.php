<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Student Controllers
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\LogEntryController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\NotificationController as StudentNotificationController;

// Supervisor Controllers
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Supervisor\ReviewController;
use App\Http\Controllers\Supervisor\MilestoneController;
use App\Http\Controllers\Supervisor\AnalyticsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Student Routes (Protected)
Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/log-entries', [LogEntryController::class, 'index'])->name('log-entries');
    Route::post('/log-entries', [LogEntryController::class, 'store'])->name('log-entries.store');
    Route::get('/log-entries/{logEntry}', [LogEntryController::class, 'show'])->name('log-entries.show');
    Route::get('/log-entries/{logEntry}/edit', [LogEntryController::class, 'edit'])->name('log-entries.edit');
    Route::put('/log-entries/{logEntry}', [LogEntryController::class, 'update'])->name('log-entries.update');
    Route::delete('/log-attachments/{attachment}', [LogEntryController::class, 'deleteAttachment'])->name('log-attachments.destroy');
    Route::post('/ai-generate-summary', [LogEntryController::class, 'generateAiSummary'])->name('ai-generate-summary');
    Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
    Route::get('/progress', [ProgressController::class, 'index'])->name('progress');
    Route::get('/progress/week/{week}', [ProgressController::class, 'week'])->name('progress.week');
    Route::get('/notifications', [StudentNotificationController::class, 'index'])->name('notifications');
    Route::post('/reminders', [StudentNotificationController::class, 'storeReminder'])->name('reminders.store');
    Route::post('/notifications/{notification}/read', [StudentNotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/notifications/unread', [StudentNotificationController::class, 'unread'])->name('notifications.unread');
});

// Supervisor Routes (Protected)
Route::middleware(['auth'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/review-logbook', [ReviewController::class, 'index'])->name('review-logbook');
    Route::post('/approve/{id}', [ReviewController::class, 'approve'])->name('approve');
    Route::post('/reject/{id}', [ReviewController::class, 'reject'])->name('reject');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/milestones', [MilestoneController::class, 'index'])->name('milestones');
    Route::post('/milestones', [MilestoneController::class, 'store'])->name('milestones.store');
});

// Admin Routes (Protected) - placeholder for future
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return 'Admin Dashboard - Coming Soon';
    })->name('dashboard');
});
