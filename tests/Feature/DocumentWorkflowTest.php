<?php

use App\Models\Document;
use App\Models\User;
use Database\Seeders\RoleSeeder;

if (! function_exists('workflowCreateDocument')) {
    function workflowCreateDocument(User $user, string $status = 'draft', array $revisionNotes = []): Document
    {
        return Document::create([
            'user_id' => $user->id,
            'title' => 'Dokumen Uji Workflow',
            'type' => 'surat',
            'header_data' => ['nomorSurat' => 'TEST/001', 'content' => '<p>header</p>'],
            'body_content' => ['pages' => ['<p>isi dokumen</p>']],
            'footer_data' => ['content' => '<p>footer</p>'],
            'status' => $status,
            'revision_notes' => $revisionNotes ?: null,
        ]);
    }
}

if (! function_exists('workflowUser')) {
    function workflowUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

test('admin melihat dokumen draft dan revisi saja', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    workflowCreateDocument($admin, 'draft');
    workflowCreateDocument($admin, 'revisi');
    workflowCreateDocument($admin, 'review_marketing');
    workflowCreateDocument($admin, 'disetujui');

    $response = $this->actingAs($admin)->get(route('documents'))->assertOk();

    expect($response->viewData('documents')->pluck('status')->all())
        ->toEqualCanonicalizing(['draft', 'revisi']);
});

test('marketer melihat dokumen review marketing dan disetujui saja', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $marketer = workflowUser('marketer');

    workflowCreateDocument($admin, 'draft');
    workflowCreateDocument($admin, 'revisi');
    workflowCreateDocument($admin, 'review_marketing');
    workflowCreateDocument($admin, 'disetujui');

    $response = $this->actingAs($marketer)->get(route('documents'))->assertOk();

    expect($response->viewData('documents')->pluck('status')->all())
        ->toEqualCanonicalizing(['review_marketing', 'disetujui']);
});

test('marketer tidak bisa mengubah isi dokumen', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $marketer = workflowUser('marketer');
    $doc = workflowCreateDocument($admin, 'review_marketing');

    $this->actingAs($marketer)
        ->put(route('documents.update', $doc), [
            'title' => 'Judul Baru',
            'header_data' => ['nomorSurat' => 'X/1', 'content' => '<p>x</p>'],
            'footer_data' => ['content' => '<p>y</p>'],
        ])
        ->assertForbidden();

    expect($doc->fresh()->title)->toBe('Dokumen Uji Workflow');
});

test('marketer tidak bisa menghapus dokumen', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $marketer = workflowUser('marketer');
    $doc = workflowCreateDocument($admin, 'review_marketing');

    $this->actingAs($marketer)
        ->delete(route('documents.destroy', $doc))
        ->assertForbidden();

    expect($doc->fresh()->trashed())->toBeFalse();
});

test('marketer tidak bisa membuat dokumen baru', function () {
    $this->seed(RoleSeeder::class);

    $marketer = workflowUser('marketer');

    $this->actingAs($marketer)
        ->get(route('documents.create'))
        ->assertForbidden();
});

test('marketer bisa menyetujui dokumen review marketing', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $marketer = workflowUser('marketer');
    $doc = workflowCreateDocument($admin, 'review_marketing');

    $this->actingAs($marketer)
        ->patch(route('documents.Status', $doc), ['status' => 'disetujui'])
        ->assertOk()
        ->assertJsonPath('status', 'disetujui');

    expect($doc->fresh()->status)->toBe('disetujui');
});

test('marketer bisa meminta revisi dokumen review marketing', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $marketer = workflowUser('marketer');
    $doc = workflowCreateDocument($admin, 'review_marketing');

    $this->actingAs($marketer)
        ->patch(route('documents.Status', $doc), [
            'status' => 'revisi',
            'reason' => 'Perlu penyesuaian jangka waktu.',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'revisi');

    expect($doc->fresh()->status)->toBe('revisi');
});

test('marketer tidak bisa mentransisi status di luar alur review', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $marketer = workflowUser('marketer');
    $doc = workflowCreateDocument($admin, 'draft');

    // draft → disetujui bukan kewenangan marketer.
    $this->actingAs($marketer)
        ->patch(route('documents.Status', $doc), ['status' => 'disetujui'])
        ->assertForbidden();

    expect($doc->fresh()->status)->toBe('draft');
});

test('admin bisa mengirim dokumen draft untuk direview marketing', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $doc = workflowCreateDocument($admin, 'draft');

    $this->actingAs($admin)
        ->patch(route('documents.Status', $doc), ['status' => 'review_marketing'])
        ->assertOk()
        ->assertJsonPath('status', 'review_marketing');

    expect($doc->fresh()->status)->toBe('review_marketing');
});

test('admin tidak bisa menyetujui dokumen sendiri', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $doc = workflowCreateDocument($admin, 'review_marketing');

    // review_marketing → disetujui hanya lewat marketer.
    $this->actingAs($admin)
        ->patch(route('documents.Status', $doc), ['status' => 'disetujui'])
        ->assertForbidden();

    expect($doc->fresh()->status)->toBe('review_marketing');
});

test('marketer membuka editor dalam mode baca (read-only)', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $marketer = workflowUser('marketer');
    $doc = workflowCreateDocument($admin, 'review_marketing');

    $this->actingAs($marketer)
        ->get(route('documents.edit', $doc))
        ->assertOk()
        ->assertSee('Mode Lihat')
        ->assertSee('Setujui');
});

test('admin membuka editor draft dalam mode edit penuh', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $doc = workflowCreateDocument($admin, 'draft');

    $this->actingAs($admin)
        ->get(route('documents.edit', $doc))
        ->assertOk()
        ->assertSee('Save')
        ->assertDontSee('Mode Lihat');
});

test('marketer menolak dengan alasan revisi tersimpan', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $marketer = workflowUser('marketer');
    $doc = workflowCreateDocument($admin, 'review_marketing');

    $this->actingAs($marketer)
        ->patch(route('documents.Status', $doc), [
            'status' => 'revisi',
            'reason' => 'Harga di pasal 3 perlu dicek ulang.',
        ])
        ->assertOk();

    $fresh = $doc->fresh();

    expect($fresh->status)->toBe('revisi')
        ->and($fresh->revision_notes)->toHaveCount(1)
        ->and($fresh->revision_notes[0]['reason'])->toBe('Harga di pasal 3 perlu dicek ulang.')
        ->and($fresh->revision_notes[0]['by'])->toBe($marketer->name)
        ->and($fresh->revision_notes[0]['at'])->not->toBeEmpty();
});

test('alasan revisi wajib diisi ketika marketer menolak', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $marketer = workflowUser('marketer');
    $doc = workflowCreateDocument($admin, 'review_marketing');

    // Tanpa alasan → ditolak (422), status tidak berubah.
    $this->actingAs($marketer)
        ->patch(route('documents.Status', $doc), ['status' => 'revisi'])
        ->assertStatus(422);

    // Alasan kosong/whitespace juga ditolak.
    $this->actingAs($marketer)
        ->patch(route('documents.Status', $doc), ['status' => 'revisi', 'reason' => '   '])
        ->assertStatus(422);

    $fresh = $doc->fresh();

    expect($fresh->status)->toBe('review_marketing')
        ->and($fresh->revision_notes)->toBeNull();
});

test('alasan revisi tercatat setiap kali dokumen ditolak ulang', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $marketer = workflowUser('marketer');
    $doc = workflowCreateDocument($admin, 'review_marketing');

    // Tolak pertama.
    $this->actingAs($marketer)
        ->patch(route('documents.Status', $doc), [
            'status' => 'revisi', 'reason' => 'Catatan pertama.',
        ])->assertOk();

    // Admin kirim ulang, marketer tolak lagi.
    $this->actingAs($admin)
        ->patch(route('documents.Status', $doc), ['status' => 'review_marketing'])
        ->assertOk();

    $this->actingAs($marketer)
        ->patch(route('documents.Status', $doc), [
            'status' => 'revisi', 'reason' => 'Catatan kedua.',
        ])->assertOk();

    $notes = $doc->fresh()->revision_notes;

    expect($notes)->toHaveCount(2)
        ->and($notes[0]['reason'])->toBe('Catatan pertama.')
        ->and($notes[1]['reason'])->toBe('Catatan kedua.');
});

test('admin melihat tombol lihat revisi untuk dokumen berstatus revisi', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    workflowCreateDocument($admin, 'revisi', revisionNotes: [
        ['reason' => 'Jumlah hari kolaborasi tidak sesuai', 'by' => 'Marketer', 'at' => '01 Jan 2026 10:00'],
    ]);

    $this->actingAs($admin)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee('Lihat Revisi')
        ->assertSee('Jumlah hari kolaborasi tidak sesuai');
});

test('editor admin pada dokumen revisi menampilkan alasan revisi', function () {
    $this->seed(RoleSeeder::class);

    $admin = workflowUser('admin');
    $doc = workflowCreateDocument($admin, 'revisi', revisionNotes: [
        ['reason' => 'Nama pihak kedua perlu diganti', 'by' => 'Marketer', 'at' => '01 Jan 2026 10:00'],
    ]);

    $this->actingAs($admin)
        ->get(route('documents.edit', $doc))
        ->assertOk()
        ->assertSee('Alasan Revisi')
        ->assertSee('Nama pihak kedua perlu diganti');
});

