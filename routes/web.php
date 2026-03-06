<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SupervisorController;

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
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/log-entries', [StudentController::class, 'logEntries'])->name('log-entries');
    Route::post('/log-entries', [StudentController::class, 'storeLogEntry'])->name('log-entries.store');
    Route::get('/log-entries/{logEntry}', [StudentController::class, 'showLogEntry'])->name('log-entries.show');
    Route::get('/log-entries/{logEntry}/edit', [StudentController::class, 'editLogEntry'])->name('log-entries.edit');
    Route::put('/log-entries/{logEntry}', [StudentController::class, 'updateLogEntry'])->name('log-entries.update');
    Route::delete('/log-attachments/{attachment}', [StudentController::class, 'deleteAttachment'])->name('log-attachments.destroy');
    Route::post('/ai-generate-summary', [StudentController::class, 'generateAiSummary'])->name('ai-generate-summary');
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
    Route::post('/profile', [StudentController::class, 'updateProfile'])->name('profile.update');
    Route::get('/progress', [StudentController::class, 'progress'])->name('progress');
    Route::get('/progress/week/{week}', [StudentController::class, 'progressWeek'])->name('progress.week');
    Route::get('/notifications', [StudentController::class, 'notifications'])->name('notifications');
    Route::post('/reminders', [StudentController::class, 'storeReminder'])->name('reminders.store');
    Route::post('/notifications/{notification}/read', [StudentController::class, 'markNotificationRead'])->name('notifications.read');
});

// Supervisor Routes (Protected)
Route::middleware(['auth'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');
    Route::get('/review-logbook', [SupervisorController::class, 'reviewLogbook'])->name('review-logbook');
    Route::post('/approve/{id}', [SupervisorController::class, 'approveLog'])->name('approve');
    Route::post('/reject/{id}', [SupervisorController::class, 'rejectLog'])->name('reject');
    Route::get('/analytics', [SupervisorController::class, 'analytics'])->name('analytics');
    Route::get('/milestones', [SupervisorController::class, 'milestones'])->name('milestones');
    Route::post('/milestones', [SupervisorController::class, 'storeMilestone'])->name('milestones.store');
});

// Admin Routes (Protected) - placeholder for future
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return 'Admin Dashboard - Coming Soon';
    })->name('dashboard');
});
