<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;

// Protected Routes (Studio Workspace)
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
    Route::get('/', function () {
        return view('pages.editor', ['title' => 'Studio Composer Dokumen']);
    })->name('editor');

    // Dokumen Saya / Library Hub
    Route::get('/documents', function () {
        return view('pages.documents', ['title' => 'Dokumen Saya & Arsip']);
    })->name('documents');

    // Galeri Template Dokumen
    Route::get('/templates', function () {
        return view('pages.templates', ['title' => 'Galeri Template Dokumen']);
    })->name('templates');

    // Studio Tanda Tangan & e-Sign BSRE
    Route::get('/signatures', function () {
        return view('pages.signatures', ['title' => 'Studio Tanda Tangan & e-Sign']);
    })->name('signatures');

    // Settings Page
    Route::get('/settings', function () {
        return view('pages.settings', ['title' => 'Pengaturan Workspace']);
    })->name('settings');
});

// Backwards compatibility alias redirects for old template routes
Route::get('/basic-tables', fn() => redirect()->route('documents'));
Route::get('/form-elements', fn() => redirect()->route('templates'));
Route::get('/profile', fn() => redirect()->route('signatures'));
Route::get('/blank', fn() => redirect()->route('settings'));

// Authentication Routes
Route::get('/signin', [AuthController::class, 'showSignin'])->name('signin');
Route::get('/login', [AuthController::class, 'showSignin'])->name('login');
Route::post('/do-login', [AuthController::class, 'login'])->name('do-login');
Route::post('/signin', [AuthController::class, 'login']);

Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::get('/register', [AuthController::class, 'showSignup'])->name('register');
Route::post('/do-register', [AuthController::class, 'register'])->name('do-register');
Route::post('/signup', [AuthController::class, 'register']);

// Logout Route
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [AuthController::class, 'logout']);

// Auxiliary pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Kalender Agendakan']);
})->name('calendar');

Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Halaman Tidak Ditemukan']);
})->name('error-404');
