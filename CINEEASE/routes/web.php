<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookingController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authenticated and verified user routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Task routes
    Route::prefix('task')->name('task.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/create', [TaskController::class, 'create'])->name('create');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/{task}/update', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}/destroy', [TaskController::class, 'destroy'])->name('destroy');
    });

    // Movie routes
    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('/', [MovieController::class, 'index'])->name('index');
        Route::get('/book/{id}', [BookingController::class, 'showBookingPage'])->name('book');
        Route::post('/reserve-seat', [BookingController::class, 'reserveSeat'])->name('reserve.seat');
        Route::get('/proceed', [BookingController::class, 'proceed'])->name('proceed');
        Route::post('/confirm-booking', [BookingController::class, 'confirmBooking'])->name('confirm.booking');
        Route::get('/print-ticket', [BookingController::class, 'printTicket'])->name('print.ticket');
    });

    // Profile routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('users', [AdminController::class, 'manageUsers'])->name('manage-users');
    Route::delete('users/{id}', [AdminController::class, 'destroy'])->name('users.destroy');

    // Admin movie management routes
    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('create', [MovieController::class, 'create'])->name('create');
        Route::post('/', [MovieController::class, 'store'])->name('store');
        Route::get('{movie}/edit', [MovieController::class, 'edit'])->name('edit');
        Route::put('{movie}', [MovieController::class, 'update'])->name('update');
    });
});

require __DIR__.'/auth.php';
