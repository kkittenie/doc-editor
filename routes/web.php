<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/templates', fn() => view('pages.templates', ['title' => 'Galeri Template Dokumen']))->name('templates');
    Route::get('/templates/{template}', [DocumentController::class, 'createFromTemplate'])->name('templates.use');
    Route::get('/', [DocumentController::class, 'chooseStart'])->name('editor.start');
    Route::get('/documents/new', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents/import', [DocumentController::class, 'importDocument'])->name('documents.import');
    Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::patch('/documents/{document}/status', [DocumentController::class, 'updateStatus'])->name('documents.Status');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents');
    Route::get('/documents/{document}/export', [DocumentController::class, 'exportPdf']);
    Route::post('documents/logo', [DocumentController::class, 'uploadLogo'])->name('documents.logo');
    Route::post('documents/image', [DocumentController::class, 'uploadImage'])->name('documents.image');
    Route::get('/documents/template/{template}', [DocumentController::class, 'createFromTemplate'])->name('documents.template');
    Route::post('/documents/save-as', [DocumentController::class, 'saveAsNew'])->name('documents.saveAs');
    Route::get('/signatures', [SignatureController::class, 'index'])->name('signatures');
    Route::post('/signatures', [SignatureController::class, 'store'])->name('signatures.store');
    Route::delete('/signatures/{signature}', [SignatureController::class, 'destroy'])->name('signatures.destroy');
    Route::get('/settings', fn() => view('pages.settings', ['title' => 'Pengaturan Workspace']))->name('settings');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', fn() => view('pages.profile.password', ['title' => 'Ubah Kata Sandi']))->name('profile.password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
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