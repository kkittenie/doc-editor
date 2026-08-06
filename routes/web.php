<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => view('pages.editor', ['title' => 'Studio Composer Dokumen']))->name('editor');
    Route::get('/documents', fn() => view('pages.documents', ['title' => 'Dokumen Saya & Arsip']))->name('documents');
    Route::get('/templates', fn() => view('pages.templates', ['title' => 'Galeri Template Dokumen']))->name('templates');
    Route::get('/signatures', fn() => view('pages.signatures', ['title' => 'Studio Tanda Tangan & e-Sign']))->name('signatures');
    Route::get('/settings', fn() => view('pages.settings', ['title' => 'Pengaturan Workspace']))->name('settings');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/signin', [AuthController::class, 'showSignin'])->name('signin');
    Route::post('/signin', [AuthController::class, 'signin']);
    Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
    Route::post('/signup', [AuthController::class, 'signup']);
});

Route::get('/login', fn() => redirect()->route('signin'));
Route::get('/register', fn() => redirect()->route('signup'));
Route::get('/logout', fn() => redirect()->route('signin')); // safety fallback