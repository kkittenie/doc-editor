<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

test('marketer user diarahkan ke halaman dokumen setelah login', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('marketer');

    expect($user->homeRouteName())->toBe('documents');

    $this->actingAs($user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee('Dokumen tidak ditemukan'); // empty state
});

test('marketer tidak bisa membuka dashboard admin', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('marketer');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('sidebar marketer tidak menampilkan menu khusus admin', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('marketer');

    $this->actingAs($user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee('Dokumen')
        ->assertDontSee('Studio Editor')
        ->assertDontSee('Dashboard');
});

test('marketer yang login dengan intended halaman admin diarahkan ke dokumen, bukan 403', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('marketer');

    // Simulasikan: sebelum login user mencoba membuka "/" (route admin),
    // URL itu tersimpan sebagai intended, lalu barulah login.
    $this->withSession(['url.intended' => route('editor.start')])
        ->post(route('signin'), [
            'login'    => $user->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('documents'));

    // Tidak boleh ke "/" yang memicu 403.
    $this->assertNotEquals(route('editor.start'), session()->get('url.intended'));
});

test('marketer yang membuka "/" diarahkan ke daftar dokumen, bukan 403', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('marketer');

    // bypass middleware role lewat actingAs, lalu pastikan controller redirect.
    $this->actingAs($user)
        ->get(route('editor.start'))
        ->assertRedirect(route('documents'));
});

test('marketer tidak bisa membuka halaman admin lain (settings)', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('marketer');

    $this->actingAs($user)
        ->get(route('settings'))
        ->assertForbidden();
});