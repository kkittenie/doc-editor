<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SofController;


Route::middleware('auth')->group(function () {
    Route::get('/documents/new/{customer?}', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents/import', [DocumentController::class, 'importDocument'])->name('documents.import');
    Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::patch('/documents/{document}/status', [DocumentController::class, 'updateStatus'])->name('documents.Status');
    // Approval dokumen (status pra-final → disetujui) + generate & simpan PDF S.O.F.
    Route::post('/documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
    // Revisi dokumen yang sudah disetujui: keluarkan dari Menu S.O.F
    // (berkas PDF dihapus) dan kembalikan status dokumen + kontrak ke On Progress.
    Route::post('/documents/{document}/revise', [DocumentController::class, 'revise'])->name('documents.revise');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::delete('/documents', [DocumentController::class, 'deleteAll'])->name('documents.deleteAll');
    Route::get('/documents/{document}/export', [DocumentController::class, 'exportPdf'])->name('documents.export');

    Route::get('/documents', [CustomerController::class, 'index'])->name('documents');

    // Menu S.O.F — repositori berkas PDF dari kontrak yang sudah disetujui.
    Route::get('/sof', [SofController::class, 'index'])->name('sof.index');
    Route::get('/sof/{customer}/download', [SofController::class, 'download'])->name('sof.download');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::post('documents/logo', [DocumentController::class, 'uploadLogo'])->name('documents.logo');
    Route::post('documents/image', [DocumentController::class, 'uploadImage'])->name('documents.image');
    Route::get('/documents/template/{template}', [DocumentController::class, 'createFromTemplate'])->name('documents.template');
    Route::post('/documents/save-as', [DocumentController::class, 'saveAsNew'])->name('documents.saveAs');

    Route::get('/', [DocumentController::class, 'chooseStart'])->name('editor.start');

    Route::get('/studio/{customer}', [DocumentController::class, 'chooseStart'])->name('studio.customer');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', fn() => view('pages.profile.password', ['title' => 'Ubah Kata Sandi']))->name('profile.password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/settings', fn() => view('pages.settings', ['title' => 'Pengaturan Workspace']))->name('settings');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/signin', [AuthController::class, 'showSignin'])->name('signin');
    Route::post('/signin', [AuthController::class, 'login']);
    Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
    Route::post('/signup', [AuthController::class, 'register']);
});

Route::get('/login', fn() => redirect()->route('signin'));
Route::get('/register', fn() => redirect()->route('signup'));