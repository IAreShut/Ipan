<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
// Student Controllers
use App\Http\Controllers\Student\LogEntryController;
use App\Http\Controllers\Student\NotificationController as StudentNotificationController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Supervisor\AnalyticsController;
// Supervisor Controllers
use App\Http\Controllers\Supervisor\AssignStudentController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Supervisor\ProfileController as SupervisorProfileController;
use App\Http\Controllers\Supervisor\ReviewController;
use App\Http\Controllers\Supervisor\TaskController;
use Illuminate\Support\Facades\Route;

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
Route::post('/check-assignment', [AuthController::class, 'checkAssignment'])->name('check-assignment');

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
    Route::post('/tasks/{task}/complete', [StudentNotificationController::class, 'completeTask'])->name('tasks.complete');
    Route::post('/notifications/{notification}/read', [StudentNotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/notifications/unread', [StudentNotificationController::class, 'unread'])->name('notifications.unread');
});

// Supervisor Routes (Protected)
Route::middleware(['auth'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/assigned-students', [AssignStudentController::class, 'assignedStudents'])->name('assigned-students');
    Route::get('/students/{student}', [SupervisorDashboardController::class, 'showStudent'])->name('students.show');
    Route::get('/review-logbook', [ReviewController::class, 'index'])->name('review-logbook');
    Route::post('/approve/{id}', [ReviewController::class, 'approve'])->name('approve');
    Route::post('/reject/{id}', [ReviewController::class, 'reject'])->name('reject');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::post('/analytics/ai-summary', [AnalyticsController::class, 'generateAiSummary'])->name('analytics.ai-summary');
    Route::post('/analytics/ai-at-risk', [AnalyticsController::class, 'identifyAtRisk'])->name('analytics.ai-at-risk');
    Route::post('/analytics/ai-chat', [AnalyticsController::class, 'askData'])->name('analytics.ai-chat');
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/profile', [SupervisorProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [SupervisorProfileController::class, 'update'])->name('profile.update');
});

// Admin Routes (Protected) - placeholder for future
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return 'Admin Dashboard - Coming Soon';
    })->name('dashboard');
});
