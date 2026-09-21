<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Tombol "Setujui" (tabel pelanggan & Studio Editor): user meng-upload berkas
 * kontrak (PDF) lewat popup, berkas itu disimpan sebagai dokumen final kontrak
 * (kolom final_file_path) dan dipakai tombol "Unduh PDF".
 *
 * Berkas upload bukan berkas S.O.F: Menu S.O.F tetap eksklusif untuk dokumen
 * yang berkasnya dibuat sistem (pdf_path).
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

test('setujui dengan berkas upload menyetujui dokumen, pelanggan, dan menyimpan berkas final', function () {
    $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document), [
            'file' => UploadedFile::fake()->create('kontrak-final.pdf', 20, 'application/pdf'),
        ])
        ->assertOk()
        ->assertJsonPath('status', 'disetujui')
        ->assertJsonPath('fileName', 'kontrak-final.pdf');

    $document = $this->document->refresh();

    expect($document->status)->toBe('disetujui')
        ->and($this->customer->refresh()->status)->toBe('disetujui')
        ->and($document->hasFinalFile())->toBeTrue()
        ->and(Storage::disk('public')->exists($document->final_file_path))->toBeTrue()
        ->and($document->final_file_uploaded_at)->not->toBeNull()
        // Approval upload tidak menyentuh berkas S.O.F sama sekali.
        ->and($document->hasSofPdf())->toBeFalse()
        ->and($document->pdf_path)->toBeNull();
});

test('setujui tanpa berkas ditolak dan status dokumen tidak berubah', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->postJson(route('documents.approve', $this->document))
        ->assertStatus(422);

    expect($this->document->refresh()->status)->toBe('on_review')
        ->and($this->customer->refresh()->status)->toBe('on_review');
});

test('setujui menolak berkas yang bukan pdf', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->postJson(route('documents.approve', $this->document), [
            'file' => UploadedFile::fake()->create('kontrak.docx', 10, 'application/msword'),
        ])
        ->assertStatus(422);

    expect($this->document->refresh()->status)->toBe('on_review')
        ->and($this->customer->refresh()->status)->toBe('on_review');
});

test('dokumen yang sudah disetujui tidak dapat disetujui ulang', function () {
    $this->document->update([
        'status' => 'disetujui',
        'final_file_path' => 'documents/1/kontrak-final.pdf',
        'final_file_name' => 'kontrak-final.pdf',
    ]);

    $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document), [
            'file' => UploadedFile::fake()->create('kontrak-baru.pdf', 20, 'application/pdf'),
        ])
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

test('unduh pdf dokumen disetujui mengembalikan berkas hasil upload', function () {
    $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document), [
            'file' => UploadedFile::fake()->create('kontrak-final.pdf', 20, 'application/pdf'),
        ])
        ->assertOk();

    $response = $this->actingAs($this->user)
        ->get(route('documents.export', $this->document))
        ->assertOk();

    expect($response->headers->get('content-type'))->toContain('application/pdf')
        ->and($response->headers->get('content-disposition'))->toContain('kontrak-final.pdf');
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

test('kontrak yang disetujui lewat upload tidak tampil di menu sof', function () {
    $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document), [
            'file' => UploadedFile::fake()->create('kontrak-final.pdf', 20, 'application/pdf'),
        ])
        ->assertOk();

    $this->actingAs($this->user)
        ->get(route('sof.index'))
        ->assertOk()
        ->assertDontSee('PT Uji Coba');

    // Berkas finalnya bukan berkas S.O.F → unduhan S.O.F-nya 404.
    $this->actingAs($this->user)
        ->get(route('sof.download', $this->customer))
        ->assertNotFound();
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

test('tabel pelanggan menyembunyikan lanjut dan revisi saat dokumen disetujui', function () {
    $this->document->update(['status' => 'disetujui']);
    $this->customer->update(['status' => 'disetujui']);

    $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee('"canContinue":false', false)
        ->assertSee('Lihat')
        ->assertDontSee('reviseCustomer(', false)
        ->assertSee('Unduh PDF');
});

test('tabel pelanggan tetap menampilkan lanjut saat dokumen draft', function () {
    $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee('"canContinue":true', false);
});

test('tanggal update mengikuti perubahan dokumen walau status tidak berubah', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    // Save saat status On Review: dokumen berubah, pelanggan tidak tersentuh.
    $this->actingAs($this->user)
        ->put(route('documents.update', $this->document), [
            'title' => $this->document->title,
            'header_data' => [
                'nomorSurat' => $this->customer->contract_number,
                'content' => '<p>Kop surat</p>',
            ],
            'body_content' => ['pages' => ['<p>Isi kontrak diperbarui</p>']],
            'footer_data' => ['content' => '<p>Footer</p>'],
        ])
        ->assertOk();

    $document = $this->document->refresh();

    expect($document->status)->toBe('on_review');

    $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee($document->updated_at->format('d M Y H:i'), false);
});

test('tanggal update fallback ke pelanggan saat belum ada dokumen', function () {
    // Lepas tautan dokumen supaya pelanggan tidak punya dokumen.
    $this->document->update(['customer_id' => null]);

    $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee($this->customer->refresh()->updated_at->format('d M Y H:i'), false);
});

test('aksi setujui memakai popup upload berkas di tabel dan editor', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        // Popup upload: user memilih berkas kontrak lewat input file PDF.
        ->assertSee("input: 'file'", false)
        ->assertSee("accept: 'application/pdf'", false);

    $this->actingAs($this->user)
        ->get(route('documents.edit', $this->document))
        ->assertOk()
        ->assertSee("input: 'file'", false)
        ->assertSee("accept: 'application/pdf'", false);
});

test('setujui dari tabel pelanggan memakai berkas upload', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document), [
            'file' => UploadedFile::fake()->create('kontrak-final.pdf', 20, 'application/pdf'),
        ])
        ->assertOk()
        ->assertJsonPath('status', 'disetujui');

    $document = $this->document->refresh();

    expect($document->status)->toBe('disetujui')
        ->and($this->customer->refresh()->status)->toBe('disetujui')
        ->and($document->hasFinalFile())->toBeTrue()
        ->and(Storage::disk('public')->exists($document->final_file_path))->toBeTrue();
});

test('setujui dari tabel sekaligus menyimpan berkas yang bisa diunduh', function () {
    $this->document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $response = $this->actingAs($this->user)
        ->post(route('documents.approve', $this->document), [
            'file' => UploadedFile::fake()->create('kontrak-final.pdf', 20, 'application/pdf'),
        ])
        ->assertOk()
        ->assertJsonPath('status', 'disetujui');

    $downloadUrl = $response->json('downloadUrl');

    expect($downloadUrl)->not->toBeNull();

    // Berkas upload langsung bisa diunduh lewat tombol "Unduh PDF".
    $this->actingAs($this->user)
        ->get($downloadUrl)
        ->assertOk();
});
