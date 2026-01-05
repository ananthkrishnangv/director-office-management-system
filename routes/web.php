<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\JournalController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public appointment request form
Route::get('/appointment-request', [AppointmentController::class, 'create'])->name('appointment.request');
Route::post('/appointment-request', [AppointmentController::class, 'store']);

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{appointment}/approve', [AppointmentController::class, 'approve'])->name('appointments.approve');
    Route::post('/appointments/{appointment}/reject', [AppointmentController::class, 'reject'])->name('appointments.reject');

    // Meetings
    Route::resource('meetings', MeetingController::class);
    Route::post('/meetings/{meeting}/complete', [MeetingController::class, 'complete'])->name('meetings.complete');
    Route::post('/meetings/{meeting}/cancel', [MeetingController::class, 'cancel'])->name('meetings.cancel');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/kiosk', [CalendarController::class, 'kiosk'])->name('calendar.kiosk');

    // Todos
    Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::put('/todos/{todo}', [TodoController::class, 'update'])->name('todos.update');
    Route::post('/todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todos.toggle');
    Route::delete('/todos/{todo}', [TodoController::class, 'destroy'])->name('todos.destroy');

    // Journal
    Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
    Route::post('/journal', [JournalController::class, 'store'])->name('journal.store');
    Route::put('/journal/{journal}', [JournalController::class, 'update'])->name('journal.update');

    // Profile
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
});
