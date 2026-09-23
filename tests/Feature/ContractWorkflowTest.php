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

/** Pelanggan uji: periode + barang/service diisi dari Form Pelanggan. */
function seedContractCustomer(Customer $customer): Customer
{
    $customer->update([
        'active_date' => '2026-09-01',
        'active_months' => 3,
        'finish_date' => '2026-12-01',
    ]);

    $customer->barang()->create([
        'name' => 'Router Mikrotik',
        'quantity' => 2,
        'price' => 1500000,
        'price_type' => 'one_time',
        'ownership' => 'disewa',
    ]);

    $customer->services()->create([
        'name' => 'Internet Dedicated 100 Mbps',
        'price' => 2500000,
        'price_type' => 'monthly',
    ]);

    return $customer->refresh();
}

/** Payload form "Buat Dokumen Baru" (kontrak dibaca dari pelanggan). */
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
    ], $overrides);
}

/** Payload Form Pelanggan (periode + barang/service, tanggal selesai auto). */
function customerPayload(array $overrides = []): array
{
    return array_merge([
        'customer_number' => '081298765432',
        'name' => 'CV Contoh Mandiri',
        'active_date' => '2026-09-01',
        'active_months' => 3,
        'barang' => [
            ['name' => 'Router Mikrotik', 'quantity' => 2, 'price' => 1500000, 'price_type' => 'one_time', 'ownership' => 'disewa'],
            ['name' => '', 'quantity' => 1, 'price' => 0], // baris kosong diabaikan
        ],
        'services' => [
            ['name' => 'Internet Dedicated 100 Mbps', 'price' => 2500000, 'price_type' => 'monthly'],
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
        ->post(route('customers.store'), customerPayload())
        ->assertRedirect(route('documents'));

    $customer = Customer::where('name', 'CV Contoh Mandiri')->firstOrFail();

    expect($customer->status)->toBe('draft')
        // beforeEach sudah memakai KTR/001/... → nomor berikutnya adalah 002
        // (bulan dalam angka Romawi mengikuti bulan berjalan).
        ->and($customer->contract_number)->toMatch('/^KTR\/002\/[IVX]+\/\d{4}$/')
        ->and($customer->contract_name)->toBeNull()
        // Periode & tanggal selesai terisi dari Form Pelanggan (auto).
        ->and($customer->active_date->toDateString())->toBe('2026-09-01')
        ->and($customer->active_months)->toBe(3)
        ->and($customer->finish_date->toDateString())->toBe('2026-12-01');

    // Baris kosong dari repeater tidak ikut tersimpan.
    expect($customer->barang()->count())->toBe(1)
        ->and($customer->services()->count())->toBe(1);

    expect($customer->barang()->firstOrFail()->ownership)->toBe('disewa')
        ->and($customer->barang()->firstOrFail()->price_type)->toBe('one_time')
        ->and($customer->services()->firstOrFail()->price_type)->toBe('monthly');
});

test('nomor kontrak manual dipakai apa adanya', function () {
    $this->actingAs($this->user)
        ->post(route('customers.store'), customerPayload([
            'customer_number' => '081200000001',
            'name' => 'PT Nomor Manual',
            'contract_number' => 'MANUAL/077/X/2026',
        ]))
        ->assertRedirect(route('documents'));

    expect(Customer::where('name', 'PT Nomor Manual')->firstOrFail()->contract_number)
        ->toBe('MANUAL/077/X/2026');
});

test('tombol lanjut langsung masuk ke halaman pilih template', function () {
    $this->actingAs($this->user)
        ->get(route('studio.customer', $this->customer))
        ->assertRedirect(route('documents.create', $this->customer));

    $this->actingAs($this->user)
        ->get(route('editor.start'))
        ->assertRedirect(route('documents'));
});

test('lanjut kedua membuka editor dokumen yang sudah ada, barang tidak mengganda', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer));

    $document = Document::firstOrFail();

    // Klik Lanjut lagi: redirect ke editor dokumen aktif, bukan buat baru.
    $this->actingAs($this->user)
        ->get(route('documents.create', $this->customer))
        ->assertRedirect(route('documents.edit', $document));

    expect($this->customer->refresh()->barang()->count())->toBe(1)
        ->and($this->customer->refresh()->services()->count())->toBe(1)
        ->and($this->customer->barangCopies()->count())->toBe(1)
        ->and($this->customer->serviceCopies()->count())->toBe(1)
        ->and(Document::count())->toBe(1);
});

test('halaman buat dokumen baru menampilkan tabel kontrak read-only dan template', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->get(route('documents.create', ['customer' => $this->customer->id]))
        ->assertOk()
        ->assertSee('Data Barang')
        ->assertSee('Data Service')
        ->assertSee('Total One Time')
        ->assertSee('Total Bulanan')
        ->assertSee('Pilih Template Dokumen')
        ->assertSee('Router Mikrotik')
        ->assertSee($this->customer->contract_number);
});

test('tanggal selesai dihitung otomatis dari data pelanggan', function () {
    seedContractCustomer($this->customer);

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
        // Lanjut ke editor langsung menaikkan status ke On Progress.
        ->and($this->customer->status)->toBe('on_progress')
        ->and($document->status)->toBe('on_progress');
});

test('hari akhir bulan di-clamp saat menghitung tanggal selesai', function () {
    seedContractCustomer($this->customer);
    $this->customer->update(['active_date' => '2026-01-31', 'active_months' => 1]);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer))
        ->assertRedirect();

    expect($this->customer->refresh()->finish_date->toDateString())->toBe('2026-02-28');
});

test('data barang dan service disalin dari pelanggan ke dokumen', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer));

    $document = Document::firstOrFail();

    // Data master tetap di pelanggan + salinan yang terhubung ke dokumen.
    expect(Barang::count())->toBe(2)
        ->and(Service::count())->toBe(2);

    $barang = Barang::where('document_id', $document->id)->firstOrFail();

    expect($barang->name)->toBe('Router Mikrotik')
        ->and($barang->quantity)->toBe(2)
        ->and((float) $barang->price)->toBe(1500000.0)
        ->and($barang->price_type)->toBe('one_time')
        ->and($barang->ownership)->toBe('disewa')
        ->and($barang->customer_id)->toBe($this->customer->id)
        ->and($barang->document_id)->toBe($document->id);

    $service = Service::where('document_id', $document->id)->firstOrFail();

    expect($service->name)->toBe('Internet Dedicated 100 Mbps')
        ->and($service->price_type)->toBe('monthly')
        ->and($service->customer_id)->toBe($this->customer->id)
        ->and($service->document_id)->toBe($document->id);
});

test('save di editor mengubah status dokumen dan pelanggan menjadi on progress', function () {
    seedContractCustomer($this->customer);

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

test('template kontrak memakai halaman pertama sumber tanpa sampul placeholder', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer, [
            'template' => 'kontrak-colocation',
        ]))
        ->assertRedirect();

    $document = Document::firstOrFail();

    expect($document->body_content['contractTemplate'])->toBeTrue()
        ->and($document->body_content['coverPages'])->toBe(0)
        ->and($document->body_content['pages'])->toHaveCount(1)
        ->and($document->body_content['pages'][0])->toContain('PERJANJIAN BERLANGGANAN')
        ->and($document->body_content['pages'][0])->toContain('JASA COLOCATION')
        ->and($document->header_data['content'])->toContain('Paraf PIHAK PERTAMA')
        ->and($document->header_data['content'])->toContain('info@fibertrust.id')
        ->and($document->header_data['content'])->not->toContain('[ Foto / Ikon Pihak Pertama ]');
});

test('dokumen template lama dengan sampul placeholder dirapikan saat dibuka', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer, [
            'template' => 'kontrak-colocation',
        ]));

    $document = Document::firstOrFail();
    $content = $document->body_content;
    $content['pages'] = [
        '<p>[Ketik nama pihak pertama di sini]</p><p>[Ketik nama pihak kedua di sini]</p>',
        $content['pages'][0],
    ];
    $content['coverPages'] = 1;
    $document->update(['body_content' => $content]);

    $this->actingAs($this->user)
        ->get(route('documents.edit', $document))
        ->assertOk();

    $document->refresh();
    expect($document->body_content['coverPages'])->toBe(0)
        ->and($document->body_content['pages'])->toHaveCount(1)
        ->and($document->body_content['templateKey'])->toBe('kontrak-colocation')
        ->and($document->header_data['content'])->toContain('Paraf PIHAK KEDUA');
});

test('status on progress tidak diturunkan saat dokumen disimpan ulang', function () {
    seedContractCustomer($this->customer);

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
    seedContractCustomer($this->customer);

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

test('dokumen bisa dibuat tanpa template dengan editor kosong dan status on progress', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer, ['template' => null]))
        ->assertRedirect();

    $document = Document::firstOrFail();

    expect($document->status)->toBe('on_progress')
        ->and($this->customer->refresh()->status)->toBe('on_progress')
        ->and($document->body_content['pages'])->toHaveCount(1);
});

test('halaman dokumen saya memuat tanggal update untuk penanda progress', function () {
    $this->customer->update(['status' => 'on_progress']);

    $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee('statusUpdated', false)
        ->assertSee('Update: ', false);
});

test('menghapus pelanggan ikut menghapus barang dan service', function () {
    seedContractCustomer($this->customer);

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
