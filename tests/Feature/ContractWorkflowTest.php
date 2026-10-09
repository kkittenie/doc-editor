<?php

use App\Models\Barang;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Service;
use App\Models\User;
use App\Data\ContractStyle;
use App\Data\ContractTemplates;
use App\Http\Controllers\DocumentController;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\RoleSeeder;


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
        ->and($customer->contract_number)->toMatch('/^KTR\/002\/[IVX]+\/\d{4}$/')
        ->and($customer->contract_name)->toBeNull()
        ->and($customer->active_date->toDateString())->toBe('2026-09-01')
        ->and($customer->active_months)->toBe(3)
        ->and($customer->finish_date->toDateString())->toBe('2026-12-01');

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

test('template kontrak memakai sampul terpisah sebagai halaman 1', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer, [
            'template' => 'kontrak-colocation',
        ]))
        ->assertRedirect();

    $document = Document::firstOrFail();
    $pages    = $document->body_content['pages'];

    expect($document->body_content['contractTemplate'])->toBeTrue()
        ->and($document->body_content['coverPages'])->toBe(1)
        ->and($pages)->toHaveCount(2);

    expect($pages[0])->toContain('PERJANJIAN BERLANGGANAN')
        ->and($pages[0])->toContain('JASA COLOCATION')
        ->and($pages[0])->toContain('DENGAN')
        ->and($pages[0])->toContain($this->customer->name)
        ->and($pages[0])->not->toContain('[PIHAK KEDUA]')
        ->and($pages[0])->not->toContain('Pada hari')
        ->and($pages[0])->not->toContain('[Ketik nama pihak pertama di sini]')
        ->and($pages[0])->not->toContain('[Ketik nama pihak kedua di sini]');
    expect($pages[1])->toContain('Pada hari')
        ->and($pages[1])->not->toContain('DENGAN')
        ->and($pages[1])->toContain(now()->locale('id')->translatedFormat('d F Y'));

    expect($document->header_data['content'])->toContain('fibertrust')
        ->and($document->footer_data['content'])->toContain('Paraf PIHAK PERTAMA')
        ->and($document->footer_data['content'])->toContain('info@fibertrust.id')
        ->and($document->header_data['content'])->not->toContain('[ Foto / Ikon Pihak Pertama ]');
});

test('setiap template kontrak mengisi tanggal berbahasa Indonesia', function () {
    $ctrl = new DocumentController();
    $m    = new ReflectionMethod(DocumentController::class, 'applyTemplateReplacements');

    $hariIndo  = now()->locale('id')->translatedFormat('l');
    $tanggalIndo = now()->locale('id')->translatedFormat('d F Y');
    $hariEn = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

    foreach (ContractTemplates::all() as $key => $tpl) {
        $out = $m->invoke($ctrl, (string) $tpl['body_content']['preamble'], null, 'X/1/2026');

        expect($out)->toContain($hariIndo);
        expect($out)->toContain($tanggalIndo);

        foreach ($hariEn as $en) {
            expect($out)->not->toContain($en);
        }
    }
});

test('sampul tiap template kontrak muat utuh di satu halaman A4', function () {
    $keys = [
        'kontrak-kemitraan',
        'kontrak-colocation',
        'kontrak-payung',
        'kontrak-soho',
        'kontrak-managed-service',
    ];

    foreach ($keys as $key) {
        $tpl  = ContractTemplates::find($key);
        $ctrl = new DocumentController();
        $m    = new ReflectionMethod(DocumentController::class, 'buildContractCoverHtml');

        $coverHtml = $m->invoke($ctrl, (string) $tpl['body_content']['cover']);
        $pdf = Pdf::loadHTML(
            '<style>@page{margin:' . ContractStyle::PAGE_MARGIN_TOP . ' '
                . ContractStyle::PAGE_MARGIN_SIDE . ' '
                . ContractStyle::PAGE_MARGIN_BOTTOM . ' '
                . ContractStyle::PAGE_MARGIN_SIDE . ';}'
                . 'body{line-height:' . ContractStyle::LINE_HEIGHT . ';font-family:DejaVu Sans,sans-serif;}</style>'
                . $coverHtml
        )->setPaper('a4', 'portrait')->output();

        $pageCount = preg_match_all('/\/Type\s*\/Page[^s]/', $pdf);

        expect($pageCount)->toBe(1, "sampul {$key} meluber ke halaman berikutnya");
    }
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
        ...$content['pages'],
    ];
    $content['coverPages'] = 1;
    $document->update(['body_content' => $content]);

    $this->actingAs($this->user)
        ->get(route('documents.edit', $document))
        ->assertOk();

    $document->refresh();
    $pages = $document->body_content['pages'];

    expect($document->body_content['coverPages'])->toBe(1)
        ->and($pages)->toHaveCount(2)
        ->and($pages[0])->not->toContain('[Ketik nama pihak pertama di sini]')
        ->and($pages[0])->toContain('JASA COLOCATION')
        ->and($document->body_content['templateKey'])->toBe('kontrak-colocation')
        ->and($document->header_data['content'])->toContain('fibertrust');
});

test('dokumen lama tanpa sampul dirapikan tanpa memunculkan halaman tambahan', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer, [
            'template' => 'kontrak-colocation',
        ]));

    $document = Document::firstOrFail();
    $content = $document->body_content;
    $content['pages'] = [
        '<p>[Ketik nama pihak pertama di sini]</p><p>[Ketik nama pihak kedua di sini]</p>',
        '<p>PERJANJIAN BERLANGGANAN</p><p>JASA COLOCATION</p><p>Nomor: X/1</p>'
            . $content['pages'][1],
    ];
    $content['coverPages'] = 1;
    $document->update(['body_content' => $content]);

    $this->actingAs($this->user)
        ->get(route('documents.edit', $document))
        ->assertOk();

    $document->refresh();
    expect($document->body_content['coverPages'])->toBe(0)
        ->and($document->body_content['pages'])->toHaveCount(1)
        ->and($document->body_content['pages'][0])->toContain('Pada hari')
        ->and($document->body_content['templateKey'])->toBe('kontrak-colocation');
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

    $this->actingAs($this->user)
        ->patch(route('documents.Status', $document), ['status' => 'disetujui'])
        ->assertForbidden();

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

    expect(Document::firstOrFail()->customer_id)->toBeNull();
});

test('membuka editor menormalkan lompatan nomor pasal (PASAL 1 lalu 15 jadi 1,2)', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer))
        ->assertRedirect();

    $document = Document::where('title', 'Perjanjian Kerjasama Uji')->firstOrFail();

    $content = $document->body_content;
    $content['pages'][0] = str_replace(
        '<strong>PASAL 2</strong>',
        '<strong>PASAL 15</strong>',
        (string) $content['pages'][0]
    );
    $document->update(['body_content' => $content]);
    expect(implode("\n", $document->body_content['pages']))->toContain('PASAL 15');

    $this->actingAs($this->user)
        ->get(route('documents.edit', $document))
        ->assertOk();

    $document->refresh();
    preg_match_all(
        '/<p[^>]*>\s*<strong>PASAL (\d+)<\/strong>\s*<\/p>/u',
        implode("\n", $document->body_content['pages']),
        $m
    );

    expect(array_map('intval', $m[1]))->toBe(range(1, 18));
});

test('editor menampilkan dropdown info kontrak beserta data barang dan service', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer));

    $document = Document::firstOrFail();

    $this->actingAs($this->user)
        ->get(route('documents.edit', $document))
        ->assertOk()
        ->assertSee('Info Kontrak')
        ->assertSee('showContractInfo', false)
        ->assertSee("contractTab: 'barang'", false)
        ->assertSee('KTR/001/IX/2026')
        ->assertSee('PT Uji Coba')
        ->assertSee('081234567890')
        ->assertSee('01 Sep 2026')
        ->assertSee('3 bulan')
        ->assertSee('01 Dec 2026')
        ->assertSee('Kontrak Kemitraan')
        ->assertSee('Router Mikrotik')
        ->assertSee('Internet Dedicated 100 Mbps')
        ->assertSee('BRG-00001')
        ->assertSee('SRV-00001')
        ->assertSee('Rp 3.000.000')
        ->assertSee('Rp 2.500.000')
        ->assertSee('Lihat di Dokumen Saya');
});

test('panel info kontrak tetap tampil saat dokumen terkunci di tahap review', function () {
    seedContractCustomer($this->customer);

    $this->actingAs($this->user)
        ->post(route('documents.store'), contractPayload($this->customer));

    $document = Document::firstOrFail();
    $document->update(['status' => 'on_review']);
    $this->customer->update(['status' => 'on_review']);

    $this->actingAs($this->user)
        ->get(route('documents.edit', $document))
        ->assertOk()
        ->assertSee('Info Kontrak')
        ->assertSee('KTR/001/IX/2026')
        ->assertSee('Router Mikrotik')
        ->assertSee('On Review');
});

test('panel info kontrak dokumen tanpa pelanggan tampil ringkas tanpa rincian barang', function () {
    $document = Document::create([
        'user_id' => $this->user->id,
        'customer_id' => null,
        'title' => 'Surat Mandiri Uji',
        'type' => 'surat',
        'header_data' => [
            'nomorSurat' => 'SM/001/IX/2026',
            'content' => '<p>Kop surat</p>',
        ],
        'body_content' => ['pages' => ['<p>Isi surat</p>']],
        'footer_data' => ['content' => '<p></p>'],
        'status' => 'draft',
    ]);

    $this->actingAs($this->user)
        ->get(route('documents.edit', $document))
        ->assertOk()
        ->assertSee('Info Kontrak')
        ->assertSee('SM/001/IX/2026')
        ->assertSee('Jenis Dokumen')
        ->assertSee('Jumlah Halaman')
        ->assertSee('tidak terhubung ke data pelanggan')
        ->assertDontSee('Belum ada barang untuk kontrak ini.');
});
