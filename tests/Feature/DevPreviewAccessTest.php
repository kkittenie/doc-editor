<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    // Buat role 'admin' di DB test (konsisten dgn test Feature lain).
    $this->seed(RoleSeeder::class);
});

test('dev preview: admin bisa buka index', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('documents.devPreview.index'))
        ->assertOk()
        ->assertSee('Uji Coba Template Dokumen');
});

test('dev preview: non-admin ditolak 403', function () {
    $user = User::factory()->create();
    // tidak diberi role admin

    $this->actingAs($user)
        ->get(route('documents.devPreview.index'))
        ->assertForbidden();
});

test('dev preview: admin bisa buka editor dari template (membuat dokumen draft)', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Template pertama yang tersedia di ContractTemplates
    $key = array_key_first(\App\Data\ContractTemplates::all());

    $this->actingAs($admin)
        ->get(route('documents.devPreview', $key))
        ->assertRedirect(); // store() redirect ke editor

    expect(\App\Models\Document::where('title', 'like', 'UJI — %')->count())->toBe(1);
});

