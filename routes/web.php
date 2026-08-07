<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DocumentController::class, 'create'])->name('editor');
    Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents', fn() => view('pages.documents', ['title' => 'Dokumen Saya & Arsip']))->name('documents');
    Route::get('/templates', fn() => view('pages.templates', ['title' => 'Galeri Template Dokumen']))->name('templates');
    Route::get('/signatures', fn() => view('pages.signatures', ['title' => 'Studio Tanda Tangan & e-Sign']))->name('signatures');
    Route::get('/settings', fn() => view('pages.settings', ['title' => 'Pengaturan Workspace']))->name('settings');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/signin', [AuthController::class, 'showSignin'])->name('signin');
    Route::post('/signin', [AuthController::class, 'login']);
    Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
    Route::post('/signup', [AuthController::class, 'register']);
});

Route::get('/login', fn() => redirect()->route('signin'));
Route::get('/register', fn() => redirect()->route('signup'));
Route::get('/logout', fn() => redirect()->route('signin'));