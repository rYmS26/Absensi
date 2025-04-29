<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\LeaveRequestController as AdminLeaveRequestController;
use App\Http\Controllers\Admin\OfficeLocationController as AdminOfficeLocationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Attendance routes
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/verify-location', [AttendanceController::class, 'verifyLocation'])->name('attendance.verify-location');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::post('/attendance/alternative-check-in', [AttendanceController::class, 'alternativeCheckIn'])->name('attendance.alternative-check-in');
    Route::post('/attendance/alternative-check-out', [AttendanceController::class, 'alternativeCheckOut'])->name('attendance.alternative-check-out');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');

    // Leave request routes
    Route::get('/leave', [LeaveRequestController::class, 'index'])->name('leave.index');
    Route::get('/leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
    Route::post('/leave', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::get('/leave/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave.show');

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // User management
        Route::resource('users', AdminUserController::class);

        // Attendance management
        Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/create', [AdminAttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/attendance', [AdminAttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/{attendance}/edit', [AdminAttendanceController::class, 'edit'])->name('attendance.edit');
        Route::put('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendance.update');
        Route::get('/attendance/report', [AdminAttendanceController::class, 'report'])->name('attendance.report');

        // Leave request management
        Route::get('/leave', [AdminLeaveRequestController::class, 'index'])->name('leave.index');
        Route::get('/leave/{leaveRequest}', [AdminLeaveRequestController::class, 'show'])->name('leave.show');
        Route::put('/leave/{leaveRequest}/status', [AdminLeaveRequestController::class, 'updateStatus'])->name('leave.update-status');

        // Office location management
        Route::resource('locations', AdminOfficeLocationController::class);
    });
});

require __DIR__.'/auth.php';
