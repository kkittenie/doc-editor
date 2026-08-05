<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Helper to check authentication state (defaults to true for initial visit, set to false on logout)
function checkAuth() {
    if (session()->has('logged_in')) {
        return session('logged_in');
    }
    // Default to logged in on first visit so user can explore, but once logged out, block access
    session(['logged_in' => true]);
    return true;
}

// Protected Routes (Studio Workspace)
Route::get('/', function () {
    if (!checkAuth()) {
        return redirect()->route('signin')->with('error', 'Anda harus masuk terlebih dahulu.');
    }
    return view('pages.dashboard.ecommerce', ['title' => 'Studio Composer Dokumen']);
})->name('dashboard');

Route::get('/basic-tables', function () {
    if (!checkAuth()) {
        return redirect()->route('signin')->with('error', 'Anda harus masuk terlebih dahulu.');
    }
    return view('pages.tables.basic-tables', ['title' => 'Dokumen Saya & Arsip']);
})->name('basic-tables');

Route::get('/form-elements', function () {
    if (!checkAuth()) {
        return redirect()->route('signin')->with('error', 'Anda harus masuk terlebih dahulu.');
    }
    return view('pages.form.form-elements', ['title' => 'Galeri Template Dokumen']);
})->name('form-elements');

Route::get('/profile', function () {
    if (!checkAuth()) {
        return redirect()->route('signin')->with('error', 'Anda harus masuk terlebih dahulu.');
    }
    return view('pages.profile', ['title' => 'Studio Tanda Tangan & e-Sign']);
})->name('profile');

Route::get('/blank', function () {
    if (!checkAuth()) {
        return redirect()->route('signin')->with('error', 'Anda harus masuk terlebih dahulu.');
    }
    return view('pages.blank', ['title' => 'Pengaturan Workspace']);
})->name('blank');

// Login Handler Action
Route::get('/do-login', function (Request $request) {
    session(['logged_in' => true]);
    return redirect()->route('dashboard');
})->name('do-login');

Route::post('/do-login', function (Request $request) {
    session(['logged_in' => true]);
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/signin', function () {
    // If already logged in, redirect to studio
    if (session('logged_in') === true) {
        return redirect()->route('dashboard');
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
