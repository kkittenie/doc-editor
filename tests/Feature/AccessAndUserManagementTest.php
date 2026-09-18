<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\RoleSeeder;

if (! function_exists('accessAdmin')) {
    function accessAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }
}

if (! function_exists('accessRolelessUser')) {
    function accessRolelessUser(): User
    {
        return User::factory()->create();
    }
}

/**
 * Guard akses: fitur non-admin harus tetap terlindungi walau role marketer
 * sudah tidak ada. Studio Editor ("/") me-redirect non-admin ke halaman
 * dokumen mereka, sedangkan Dashboard/Settings tetap 403 (middleware role).
 */
test('user tanpa role diarahkan ke dokumen saat membuka studio editor', function () {
    $this->seed(RoleSeeder::class);

    $user = accessRolelessUser();

    $this->actingAs($user)
        ->get(route('editor.start'))
        ->assertRedirect(route('documents'));
});

test('user tanpa role tidak bisa membuka dashboard admin', function () {
    $this->seed(RoleSeeder::class);

    $user = accessRolelessUser();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('user tanpa role tidak bisa membuka settings', function () {
    $this->seed(RoleSeeder::class);

    $user = accessRolelessUser();

    $this->actingAs($user)
        ->get(route('settings'))
        ->assertForbidden();
});

/** Pendaftaran (signup) langsung mendapat role admin — hanya ada satu role. */
test('pendaftaran akun baru mendapat role admin', function () {
    $this->seed(RoleSeeder::class);

    $this->post(route('signup'), [
        'fname'    => 'Budi',
        'lname'    => 'Santoso',
        'email'    => 'budi@example.com',
        'password' => 'rahasia123',
    ])->assertRedirect();

    expect(User::where('email', 'budi@example.com')->firstOrFail()->hasRole('admin'))->toBeTrue();
});

test('admin bisa membuat akun admin lain dari kelola user', function () {
    $this->seed(RoleSeeder::class);
    $admin = accessAdmin();

    $this->actingAs($admin)->post(route('users.store'), [
        'name'     => 'Admin Baru',
        'username' => 'admin_baru',
        'email'    => 'admin2@example.com',
        'password' => 'secret123',
        'role'     => 'admin',
    ])->assertRedirect();

    $created = User::where('email', 'admin2@example.com')->firstOrFail();

    expect($created->name)->toBe('Admin Baru')
        ->and($created->username)->toBe('admin_baru')
        ->and($created->hasRole('admin'))->toBeTrue();
});

test('role yang tidak valid ditolak saat membuat akun', function () {
    $this->seed(RoleSeeder::class);
    $admin = accessAdmin();

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
    $this->seed(RoleSeeder::class);
    $admin = accessAdmin();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $admin))
        ->assertRedirect();

    expect(User::find($admin->id))->not->toBeNull();
});

/** Dokumen hanya bisa diubah/dihapus oleh admin pemiliknya. */
test('dokumen milik admin lain tidak bisa diubah atau dihapus', function () {
    $this->seed(RoleSeeder::class);
    $owner = accessAdmin();
    $other = accessAdmin();

    $doc = Document::create([
        'user_id'      => $owner->id,
        'title'        => 'Dokumen Owner',
        'type'         => 'surat',
        'header_data'  => ['nomorSurat' => 'T/001', 'content' => '<p>h</p>'],
        'body_content' => ['pages' => ['<p>isi</p>']],
        'footer_data'  => ['content' => '<p>f</p>'],
        'status'       => 'draft',
    ]);

    $this->actingAs($other)
        ->put(route('documents.update', $doc), [
            'title'       => 'Diubah Paksa',
            'header_data' => ['nomorSurat' => 'T/001', 'content' => '<p>h</p>'],
            'footer_data' => ['content' => '<p>f</p>'],
        ])
        ->assertForbidden();

    expect($doc->fresh()->title)->toBe('Dokumen Owner');

    $this->actingAs($other)
        ->delete(route('documents.destroy', $doc))
        ->assertForbidden();

    expect(Document::find($doc->id))->not->toBeNull();
});

/** Pelanggan hanya bisa dihapus oleh pemiliknya. */
test('pelanggan milik admin lain tidak bisa dihapus', function () {
    $this->seed(RoleSeeder::class);
    $owner = accessAdmin();
    $other = accessAdmin();

    $customer = Customer::create([
        'user_id'         => $owner->id,
        'customer_number' => '081211111111',
        'name'            => 'PT Milik Owner',
        'contract_number' => 'KTR/777/'.now()->format('n').'/'.now()->year,
        'status'          => 'draft',
    ]);

    $this->actingAs($other)
        ->delete(route('customers.destroy', $customer))
        ->assertForbidden();

    expect(Customer::find($customer->id))->not->toBeNull();
});
