<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Auth::routes(['verify' => true]);

// Email verification notice
Route::get('/email/verify', function () {
    return view('auth.verify-email'); // Laravel provides a default template
})->middleware('auth')->name('verification.notice');

// Verify email using signed URL
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // Mark email as verified
    return redirect('/dashboard'); // Redirect to dashboard after verification
})->middleware(['auth', 'signed'])->name('verification.verify');

// Resend verification email
Route::post('/email/resend', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect('/dashboard');
    }

    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard')->middleware('admin');
    Route::get('/customer/dashboard', [CustomerController::class, 'index'])->name('customer.dashboard')->middleware('customer');
});
Route::middleware(['auth'])->get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
Route::middleware(['auth'])->get('/customer/dashboard', [CustomerController::class, 'index'])->name('customer.dashboard');

require __DIR__.'/auth.php';
