<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/appointments/suggest-slots', [AppointmentController::class, 'suggestSlots']);

// Protected API routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'apiIndex']);

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'apiIndex']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::post('/appointments/{appointment}/approve', [AppointmentController::class, 'approve']);
    Route::post('/appointments/{appointment}/reject', [AppointmentController::class, 'reject']);

    // Meetings
    Route::get('/meetings', [MeetingController::class, 'index']);
    Route::post('/meetings', [MeetingController::class, 'store']);
    Route::get('/meetings/today', [MeetingController::class, 'today']);
    Route::get('/meetings/upcoming', [MeetingController::class, 'upcoming']);
    Route::get('/meetings/{meeting}', [MeetingController::class, 'show']);
    Route::put('/meetings/{meeting}', [MeetingController::class, 'update']);
    Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy']);
    Route::post('/meetings/{meeting}/complete', [MeetingController::class, 'complete']);
    Route::post('/meetings/{meeting}/cancel', [MeetingController::class, 'cancel']);

    // Calendar
    Route::get('/calendar/events', [CalendarController::class, 'events']);
    Route::get('/calendar/{date}', [CalendarController::class, 'date']);
    Route::post('/calendar/sync-google', [CalendarController::class, 'syncGoogle']);
    Route::post('/calendar/sync-microsoft', [CalendarController::class, 'syncMicrosoft']);

    // Todos
    Route::get('/todos', [TodoController::class, 'index']);
    Route::post('/todos', [TodoController::class, 'store']);
    Route::put('/todos/{todo}', [TodoController::class, 'update']);
    Route::post('/todos/{todo}/toggle', [TodoController::class, 'toggle']);
    Route::delete('/todos/{todo}', [TodoController::class, 'destroy']);

    // Journal
    Route::get('/journal', [JournalController::class, 'index']);
    Route::post('/journal', [JournalController::class, 'store']);
    Route::get('/journal/today', [JournalController::class, 'today']);
    Route::put('/journal/{journal}', [JournalController::class, 'update']);
    Route::post('/journal/{journal}/auto-save', [JournalController::class, 'autoSave']);
});
