<?php

use App\Models\Document;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function adminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function marketerUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('marketer');

    return $user;
}

test('halaman kelola user hanya bisa diakses admin', function () {
    $admin = adminUser();
    $marketer = marketerUser();

    $this->actingAs($admin)->get(route('users.index'))->assertOk()->assertSee('Buat Akun Baru');

    $this->actingAs($marketer)->get(route('users.index'))->assertForbidden();
});

test('admin bisa membuat akun marketer baru', function () {
    $admin = adminUser();

    $this->actingAs($admin)->post(route('users.store'), [
        'name'     => 'Marketer Baru',
        'username' => 'mk_baru',
        'email'    => 'marketer@example.com',
        'password' => 'secret123',
        'role'     => 'marketer',
    ])->assertRedirect();

    $created = User::where('email', 'marketer@example.com')->firstOrFail();

    expect($created->name)->toBe('Marketer Baru')
        ->and($created->username)->toBe('mk_baru')
        ->and($created->hasRole('marketer'))->toBeTrue();
});

test('admin bisa membuat akun tanpa username (username opsional)', function () {
    $admin = adminUser();

    $this->actingAs($admin)->post(route('users.store'), [
        'name'     => 'Tanpa Username',
        'email'    => 'tusern@example.com',
        'password' => 'secret123',
        'role'     => 'admin',
    ])->assertRedirect();

    $created = User::where('email', 'tusern@example.com')->firstOrFail();

    expect($created->username)->toBeNull()
        ->and($created->hasRole('admin'))->toBeTrue();
});

test('nama, email, dan password wajib saat membuat akun', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->from(route('users.index'))
        ->post(route('users.store'), [
            'username' => 'abc',
            'role'     => 'marketer',
        ])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

test('role yang tidak valid ditolak saat membuat akun', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name'     => 'Role Salah',
            'email'    => 'rolesalah@example.com',
            'password' => 'secret123',
            'role'     => 'superadmin',
        ])
        ->assertSessionHasErrors('role');
});

test('admin tidak bisa menghapus akun sendiri', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $admin))
        ->assertRedirect();

    expect(User::find($admin->id))->not->toBeNull();
});

test('admin bisa mengubah role user dari marketer menjadi admin', function () {
    $admin = adminUser();
    $marketer = marketerUser();

    $this->actingAs($admin)->put(route('users.update', $marketer), [
        'name'  => $marketer->name,
        'email' => $marketer->email,
        'role'  => 'admin',
    ])->assertRedirect();

    expect($marketer->fresh()->hasRole('admin'))->toBeTrue();
});

test('dokumen wajib memilih marketer saat dibuat', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('documents.store'), [
            'title' => 'Tanpa Marketer',
            'header_data' => [
                'nomorSurat' => 'X/001',
                'content' => '<p>header</p>',
            ],
            'footer_data' => ['content' => '<p>footer</p>'],
        ])
        ->assertSessionHasErrors('marketer_id');
});

test('dokumen yang dibuat teregistrasi ke marketer terpilih', function () {
    $admin = adminUser();
    $marketerA = marketerUser();
    $marketerB = marketerUser();

    $this->actingAs($admin)->post(route('documents.store'), [
        'title' => 'Untuk A',
        'marketer_id' => $marketerA->id,
        'header_data' => [
            'nomorSurat' => 'A/002',
            'content' => '<p>header</p>',
        ],
        'body_html' => '<p>isi</p>',
        'footer_data' => ['content' => '<p>footer</p>'],
    ])->assertRedirect();

    $doc = Document::where('title', 'Untuk A')->firstOrFail();

    expect($doc->marketer_id)->toBe($marketerA->id);

    // Kirim ke review, lalu pastikan hanya marketer A yang melihatnya.
    $this->actingAs($admin)
        ->patch(route('documents.Status', $doc), ['status' => 'review_marketing'])
        ->assertOk();

    $resA = $this->actingAs($marketerA)->get(route('documents'))->assertOk();
    expect($resA->viewData('documents')->pluck('id'))->toContain($doc->id);

    $resB = $this->actingAs($marketerB)->get(route('documents'))->assertOk();
    expect($resB->viewData('documents')->pluck('id'))->not->toContain($doc->id);
});