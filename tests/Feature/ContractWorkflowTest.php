<?php

use App\Models\Barang;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleSeeder;

/**
 * Alur kontrak: Dokumen Saya (form + tabel pelanggan) → Studio Editor →
 * Buat Dokumen Baru (detail kontrak + barang/service) → Editor.
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
});

/** Payload form "Buat Dokumen Baru" (Detail Kontrak + Barang + Service). */
function contractPayload(Customer $customer, array $overrides = []): array
{
    return array_merge([
        'customer_id' => $customer->id,
        'title' => 'Perjanjian Kerjasama Uji',
        'header_data' => [
            'nomorSurat' => $customer->contract_number,
            'content' => '<p>Kop surat</p>',
        ],
        'footer_data' => ['content' => '<p>Footer</p>'],
        'template' => 'kontrak-kemitraan',
        'active_date' => '2026-09-01',
        'active_months' => 3,
        'items_barang' => [
            ['name' => 'Router Mikrotik', 'quantity' => 2, 'price' => 1500000],
            ['name' => '', 'quantity' => 1, 'price' => 0], // baris kosong diabaikan
        ],
        'items_service' => [
            ['name' => 'Internet Dedicated 100 Mbps', 'price' => 2500000],
        ],
    ], $overrides);
}

test('halaman dokumen saya menampilkan form dan tabel pelanggan', function () {
    $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee('Form Pelanggan')
        ->assertSee('Tabel Pelanggan')
        ->assertSee('Nomer Pelanggan')
        ->assertSee('Tanggal Selesai')
        ->assertSee('PT Uji Coba');
});

test('pelanggan baru tersimpan dengan nomor kontrak otomatis dan status draft', function () {
    $this->actingAs($this->user)
        ->post(route('customers.store'), [
            'customer_number' => '081298765432',
            'name' => 'CV Contoh Mandiri',
            // contract_number sengaja dikosongkan → server memakai auto-generate
        ])
        ->assertRedirect(route('documents'));

    $customer = Customer::where('name', 'CV Contoh Mandiri')->firstOrFail();

    expect($customer->status)->toBe('draft')
        // beforeEach sudah memakai KTR/001/... → nomor berikutnya adalah 002
        // (bulan dalam angka Romawi mengikuti bulan berjalan).
        ->and($customer->contract_number)->toMatch('/^KTR\/002\/[IVX]+\/\d{4}$/')
        ->and($customer->contract_name)->toBeNull()
        ->and($customer->active_date)->toBeNull()
        ->and($customer->active_months)->toBeNull()
        ->and($customer->finish_date)->toBeNull();
});

test('nomor kontrak manual dipakai apa adanya', function () {
    $this->actingAs($this->user)
        ->post(route('customers.store'), [
            'customer_number' => '081200000001',
            'name' => 'PT Nomor Manual',
            'contract_number' => 'MANUAL/077/X/2026',
        ])
        ->assertRedirect(route('documents'));

    expect(Customer::where('name', 'PT Nomor Manual')->firstOrFail()->contract_number)
        ->toBe('MANUAL/077/X/2026');
});

test('studio editor menampilkan dua pilihan dan upload masih dinonaktifkan', function () {
    $this->actingAs($this->user)
        ->get(route('studio.customer', $this->customer))
        ->assertOk()
        ->assertSee('Susun Kontrak untuk Pelanggan Ini')
        ->assertSee('Segera Hadir')
        ->assertSee('PT Uji Coba');

    $this->actingAs($this->user)
        ->get(route('editor.start'))
        ->assertOk()
        ->assertSee('Buat Dokumen Baru')
        ->assertSee('Belum tersedia');
});

test('halaman buat dokumen baru menampilkan detail kontrak, barang, service, dan template', function () {
    $this->actingAs($this->user)
        ->get(route('documents.create', ['customer' => $this->customer->id]))
        ->assertOk()
        ->assertSee('Detail Kontrak')
        ->assertSee('Data Barang')
        ->assertSee('Data Service')
        ->assertSee('Pilih Template Dokumen')
        ->assertSee('Masa Aktif (bulan)')
        ->assertSee($this->customer->contract_number);
});

test('tanggal selesai dihitung otomatis dari tanggal aktif dan masa aktif', function () {
    $response = $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer));

    $document = Document::firstOrFail();

    $response->assertRedirect(route('documents.edit', $document));

    $this->customer->refresh();

    expect($document->customer_id)->toBe($this->customer->id)
        ->and($this->customer->contract_name)->toBe('Perjanjian Kerjasama Uji')
        ->and($this->customer->active_date->toDateString())->toBe('2026-09-01')
        ->and($this->customer->active_months)->toBe(3)
        ->and($this->customer->finish_date->toDateString())->toBe('2026-12-01')
        // Status tetap Draft sampai dokumen disimpan di editor.
        ->and($this->customer->status)->toBe('draft');
});

test('hari akhir bulan di-clamp saat menghitung tanggal selesai', function () {
    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer, [
            'active_date' => '2026-01-31',
            'active_months' => 1,
        ]))
        ->assertRedirect();

    expect($this->customer->refresh()->finish_date->toDateString())->toBe('2026-02-28');
});

test('data barang dan service tersimpan serta terhubung ke dokumen', function () {
    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer));

    $document = Document::firstOrFail();

    // Baris kosong dari form tidak ikut tersimpan.
    expect(Barang::count())->toBe(1)
        ->and(Service::count())->toBe(1);

    $barang = Barang::firstOrFail();

    expect($barang->name)->toBe('Router Mikrotik')
        ->and($barang->quantity)->toBe(2)
        ->and((float) $barang->price)->toBe(1500000.0)
        ->and($barang->customer_id)->toBe($this->customer->id)
        ->and($barang->document_id)->toBe($document->id);

    $service = Service::firstOrFail();

    expect($service->name)->toBe('Internet Dedicated 100 Mbps')
        ->and($service->customer_id)->toBe($this->customer->id)
        ->and($service->document_id)->toBe($document->id);
});

test('save di editor mengubah status dokumen dan pelanggan menjadi on progress', function () {
    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer));

    $document = Document::firstOrFail();

    $this->actingAs($this->user)
        ->put(route('documents.update', $document), [
            'title' => $document->title,
            'header_data' => [
                'nomorSurat' => $this->customer->contract_number,
                'content' => '<p>Kop surat</p>',
            ],
            'body_content' => ['pages' => ['<p>Isi kontrak</p>']],
            'footer_data' => ['content' => '<p>Footer</p>'],
        ])
        ->assertOk();

    expect($document->refresh()->status)->toBe('on_progress')
        ->and($this->customer->refresh()->status)->toBe('on_progress');
});

test('status on progress tidak diturunkan saat dokumen disimpan ulang', function () {
    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer));

    $document = Document::firstOrFail();
    $document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->put(route('documents.update', $document), [
            'title' => $document->title,
            'header_data' => [
                'nomorSurat' => $this->customer->contract_number,
                'content' => '<p>Kop surat</p>',
            ],
            'body_content' => ['pages' => ['<p>Isi kontrak revisi</p>']],
            'footer_data' => ['content' => '<p>Footer</p>'],
        ])
        ->assertOk();

    expect($document->refresh()->status)->toBe('on_review')
        ->and($this->customer->refresh()->status)->toBe('on_review');
});

test('kirim untuk review menyinkronkan status pelanggan dan transisi tidak sah ditolak', function () {
    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer));

    $document = Document::firstOrFail();

    // draft → disetujui tidak diizinkan.
    $this->actingAs($this->user)
        ->patch(route('documents.Status', $document), ['status' => 'disetujui'])
        ->assertForbidden();

    // draft → on_review diizinkan.
    $this->actingAs($this->user)
        ->patch(route('documents.Status', $document), ['status' => 'on_review'])
        ->assertOk();

    expect($document->refresh()->status)->toBe('on_review')
        ->and($this->customer->refresh()->status)->toBe('on_review');
});

test('menghapus pelanggan ikut menghapus barang dan service', function () {
    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer));

    $this->actingAs($this->user)
        ->delete(route('customers.destroy', $this->customer))
        ->assertOk();

    expect(Customer::count())->toBe(0)
        ->and(Barang::count())->toBe(0)
        ->and(Service::count())->toBe(0);

    // Dokumen tetap ada, hanya tautannya yang dilepas.
    expect(Document::firstOrFail()->customer_id)->toBeNull();
});