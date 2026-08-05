<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Helper to check authentication state
function checkAuth() {
    if (session()->has('logged_in')) {
        return session('logged_in');
    }
    session(['logged_in' => true]);
    return true;
}

// Protected Routes (Studio Workspace)
Route::get('/', function () {
    if (!checkAuth()) {
        return redirect()->route('signin')->with('error', 'Anda harus masuk terlebih dahulu.');
    }
    return view('pages.editor', ['title' => 'Studio Composer Dokumen']);
})->name('editor');

// Dokumen Saya / Library Hub
Route::get('/documents', function () {
    if (!checkAuth()) {
        return redirect()->route('signin')->with('error', 'Anda harus masuk terlebih dahulu.');
    }
    return view('pages.documents', ['title' => 'Dokumen Saya & Arsip']);
})->name('documents');

// Galeri Template Dokumen
Route::get('/templates', function () {
    if (!checkAuth()) {
        return redirect()->route('signin')->with('error', 'Anda harus masuk terlebih dahulu.');
    }
    return view('pages.templates', ['title' => 'Galeri Template Dokumen']);
})->name('templates');

// Studio Tanda Tangan & e-Sign BSRE
Route::get('/signatures', function () {
    if (!checkAuth()) {
        return redirect()->route('signin')->with('error', 'Anda harus masuk terlebih dahulu.');
    }
    return view('pages.signatures', ['title' => 'Studio Tanda Tangan & e-Sign']);
})->name('signatures');

// Settings Page
Route::get('/settings', function () {
    if (!checkAuth()) {
        return redirect()->route('signin')->with('error', 'Anda harus masuk terlebih dahulu.');
    }
    return view('pages.settings', ['title' => 'Pengaturan Workspace']);
})->name('settings');

// Backwards compatibility alias redirects for old template routes
Route::get('/basic-tables', fn() => redirect()->route('documents'));
Route::get('/form-elements', fn() => redirect()->route('templates'));
Route::get('/profile', fn() => redirect()->route('signatures'));
Route::get('/blank', fn() => redirect()->route('settings'));

// Login Handler Action
Route::get('/do-login', function (Request $request) {
    session(['logged_in' => true]);
    return redirect()->route('editor');
})->name('do-login');

Route::post('/do-login', function (Request $request) {
    session(['logged_in' => true]);
    return redirect()->route('editor');
});

// Authentication Routes
Route::get('/signin', function () {
    if (session('logged_in') === true) {
        return redirect()->route('editor');
    }
    return view('pages.auth.signin', ['title' => 'Masuk ke Studio Papercraft']);
})->name('signin');

Route::get('/login', function () {
    return redirect()->route('signin');
});

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Pendaftaran Penandatangan Resmi']);
})->name('signup');

Route::get('/register', function () {
    return redirect()->route('signup');
});

// Dynamic Logout Route
Route::get('/logout', function () {
    session(['logged_in' => false]);
    return redirect('/signin?logged_out=1');
})->name('logout');

Route::post('/logout', function () {
    session(['logged_in' => false]);
    return redirect('/signin?logged_out=1');
});

// Auxiliary pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Kalender Agendakan']);
})->name('calendar');

Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Halaman Tidak Ditemukan']);
})->name('error-404');
