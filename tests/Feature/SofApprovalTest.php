<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;

/**
 * Tombol "Selesai" di Studio Editor (letaknya sebelah Save): approval boleh
 * dari tahap mana pun sebelum final, berkas PDF S.O.F wajib ikut terbentuk,
 * status pelanggan ikut disetujui, dan dokumen lalu muncul di Menu S.O.F.
 */
beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('admin');

    $this->customer = Customer::create([
        'user_id' => $this->user->id,
        'customer_number' => '081234567890',
        'name' => 'PT Uji Coba',
        'contract_number' => 'KTR/001/IX/2026',
        'status' => 'draft',
    ]);

    $this->document = Document::create([
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'title' => 'Perjanjian Uji',
        'header_data' => [
            'nomorSurat' => $this->customer->contract_number,
            'content' => '<p>Kop surat</p>',
        ],
        'body_content' => ['pages' => ['<p>Isi kontrak uji.</p>']],
        'footer_data' => ['content' => '<p>Footer</p>'],
        'status' => 'draft',
    ]);

    Storage::fake('public');
});

test('selesai dari status draft menyetujui dokumen, pelanggan, dan membuat berkas sof', function () {
    $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document))
        ->assertOk()
        ->assertJsonPath('status', 'disetujui');

    $document = $this->document->refresh();

    expect($document->status)->toBe('disetujui')
        ->and($this->customer->refresh()->status)->toBe('disetujui')
        ->and($document->hasSofPdf())->toBeTrue()
        ->and(Storage::disk('public')->exists($document->pdf_path))->toBeTrue()
        ->and($document->pdf_generated_at)->not->toBeNull();
});

test('selesai juga bisa dilakukan dari status on progress, on review, dan revisi', function () {
    foreach (['on_progress', 'on_review', 'revisi'] as $status) {
        $this->document->update(['status' => $status, 'pdf_path' => null, 'pdf_generated_at' => null]);
        $this->customer->update(['status' => $status]);

        $this->actingAs($this->user)
            ->post(route('documents.approve', $this->document))
            ->assertOk()
            ->assertJsonPath('status', 'disetujui');

        expect($this->document->refresh()->status)->toBe('disetujui')
            ->and($this->customer->refresh()->status)->toBe('disetujui')
            ->and($this->document->hasSofPdf())->toBeTrue();
    }
});

test('dokumen yang sudah disetujui tidak dapat disetujui ulang', function () {
    $this->document->update(['status' => 'disetujui']);

    $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document))
        ->assertStatus(422);
});

test('simpan draf tidak menaikkan status dokumen maupun pelanggan', function () {
    $this->actingAs($this->user)
        ->put(route('documents.update', $this->document), [
            'title' => $this->document->title,
            'header_data' => [
                'nomorSurat' => $this->customer->contract_number,
                'content' => '<p>Kop surat</p>',
            ],
            'body_content' => ['pages' => ['<p>Isi kontrak</p>']],
            'footer_data' => ['content' => '<p>Footer</p>'],
            'intent' => 'draft',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'draft');

    expect($this->document->refresh()->status)->toBe('draft')
        ->and($this->customer->refresh()->status)->toBe('draft');
});

test('revisi mengeluarkan dokumen dari sof dan mengembalikan status ke on progress', function () {
    // Setujui dulu supaya berkas S.O.F terbentuk.
    $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document))
        ->assertOk();

    $document = $this->document->refresh();
    $pdfPath = $document->pdf_path;

    expect(Storage::disk('public')->exists($pdfPath))->toBeTrue();

    $this->actingAs($this->user)
        ->post(route('documents.revise', $document))
        ->assertOk()
        ->assertJsonPath('status', 'on_progress');

    $document = $document->refresh();

    expect($document->status)->toBe('on_progress')
        ->and($this->customer->refresh()->status)->toBe('on_progress')
        ->and($document->hasSofPdf())->toBeFalse()
        ->and($document->pdf_path)->toBeNull()
        ->and($document->pdf_generated_at)->toBeNull()
        ->and(Storage::disk('public')->exists($pdfPath))->toBeFalse();
});

test('revisi hanya berlaku untuk dokumen yang sudah disetujui', function () {
    $this->actingAs($this->user)
        ->post(route('documents.revise', $this->document))
        ->assertStatus(422);
});

test('revisi oleh user selain pemilik dokumen ditolak', function () {
    $this->document->update(['status' => 'disetujui']);

    $other = User::factory()->create();
    $other->assignRole('admin');

    $this->actingAs($other)
        ->post(route('documents.revise', $this->document))
        ->assertForbidden();
});

test('dokumen yang direvisi tidak lagi tampil di menu sof', function () {
    $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document))
        ->assertOk();

    $this->actingAs($this->user)
        ->post(route('documents.revise', $this->document))
        ->assertOk();

    $this->actingAs($this->user)
        ->get(route('sof.index'))
        ->assertOk()
        ->assertDontSee('PT Uji Coba');
});

test('unduh pdf bisa dilakukan saat dokumen masih on review', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $response = $this->actingAs($this->user)
        ->get(route('documents.export', $this->document))
        ->assertOk();

    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('unduh pdf ditolak untuk user yang bukan pemilik dokumen', function () {
    $other = User::factory()->create();
    $other->assignRole('admin');

    $this->actingAs($other)
        ->get(route('documents.export', $this->document))
        ->assertForbidden();
});

test('setujui dari tabel pelanggan menyetujui dokumen on review dan menerbitkan sof', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document))
        ->assertOk()
        ->assertJsonPath('status', 'disetujui');

    $document = $this->document->refresh();

    expect($document->status)->toBe('disetujui')
        ->and($this->customer->refresh()->status)->toBe('disetujui')
        ->and($document->hasSofPdf())->toBeTrue()
        ->and(Storage::disk('public')->exists($document->pdf_path))->toBeTrue();
});

test('minta revisi dari tabel pelanggan mengubah on review menjadi revisi', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->patch(route('documents.Status', $this->document), ['status' => 'revisi'])
        ->assertOk()
        ->assertJsonPath('status', 'revisi');

    expect($this->document->refresh()->status)->toBe('revisi')
        ->and($this->customer->refresh()->status)->toBe('revisi');
});

test('halaman dokumen saya menampilkan tiga aksi tahap on review untuk admin', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee('canApprove', false)
        ->assertSee('canRequestRevision', false)
        ->assertSee('canExportPdf', false)
        ->assertSee('Unduh PDF')
        ->assertSee('Setujui')
        ->assertSee('Lihat')
        ->assertSee('"canContinue":false', false)
        ->assertSee('"canView":true', false);
});

test('editor menampilkan tiga aksi tahap on review meski dokumen terkunci', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->get(route('documents.edit', $this->document))
        ->assertOk()
        ->assertSee('Minta Revisi')
        ->assertSee('Setujui')
        ->assertSee('Unduh PDF')
        ->assertSee(route('documents.export', $this->document), false);
});

test('selesai di editor mengirim dokumen untuk review, bukan menyetujui', function () {
    $this->actingAs($this->user)
        ->put(route('documents.update', $this->document), [
            'title' => $this->document->title,
            'header_data' => [
                'nomorSurat' => $this->customer->contract_number,
                'content' => '<p>Kop surat</p>',
            ],
            'body_content' => ['pages' => ['<p>Isi kontrak</p>']],
            'footer_data' => ['content' => '<p>Footer</p>'],
            'intent' => 'draft',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'draft');

    $this->actingAs($this->user)
        ->patch(route('documents.Status', $this->document), ['status' => 'on_review'])
        ->assertOk()
        ->assertJsonPath('status', 'on_review');

    $document = $this->document->refresh();

    expect($document->status)->toBe('on_review')
        ->and($this->customer->refresh()->status)->toBe('on_review')
        ->and($document->hasSofPdf())->toBeFalse()
        ->and($document->pdf_generated_at)->toBeNull();
});

test('editor menampilkan tombol setujui saat on review', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->get(route('documents.edit', $this->document))
        ->assertOk()
        ->assertSee('Setujui')
        ->assertSee('Minta Revisi')
        ->assertSee('Unduh PDF');
});

test('dokumen on review bisa dibuka pemiliknya dalam mode lihat', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->get(route('documents.edit', $this->document))
        ->assertOk()
        ->assertSee($this->document->title);
});
