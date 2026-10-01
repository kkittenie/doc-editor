<?php

use App\Models\Sof;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;

/**
 * Kolom "Berkas" pada tabel S.O.F:
 * - S.O.F yang punya berkas → tombol "Lihat File" (membuka PDF di tab baru).
 * - S.O.F tanpa berkas → tanda "—".
 */
beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('admin');

    Storage::fake('public');
});

function makeSof(array $attributes = []): Sof
{
    return Sof::create(array_merge([
        'user_id'       => test()->user->id,
        'order_number'  => 'SOF/001/IX/2026',
        'customer_number' => '081234567890',
        'customer_name' => 'PT Uji Berkas',
        'status'        => 'draft',
    ], $attributes));
}

test('kolom berkas menampilkan tombol lihat file bila berkas diunggah', function () {
    Storage::disk('public')->put('sofs/1/SOF-001.pdf', '%PDF-1.4 uji');

    makeSof([
        'file_path' => 'sofs/1/SOF-001.pdf',
        'file_name' => 'SOF-001.pdf',
    ]);

    $this->actingAs($this->user)
        ->getJson(route('sof.data'))
        ->assertOk()
        ->assertJsonPath('data.0.has_file', fn ($html) => str_contains($html, 'Lihat File')
            && str_contains($html, route('sof.view', Sof::first())));
});

test('kolom berkas hanya tanda strip bila berkas tidak diunggah', function () {
    makeSof(['order_number' => 'SOF/002/IX/2026']);

    $this->actingAs($this->user)
        ->getJson(route('sof.data'))
        ->assertOk()
        ->assertJsonPath('data.0.has_file', '—')
        ->assertJsonMissing(['Lihat File']);
});

test('tombol lihat file menampilkan pdf di browser dan bukan mengunduhnya', function () {
    Storage::disk('public')->put('sofs/1/SOF-001.pdf', '%PDF-1.4 uji');

    $sof = makeSof([
        'file_path' => 'sofs/1/SOF-001.pdf',
        'file_name' => 'SOF-001.pdf',
    ]);

    $this->actingAs($this->user)
        ->get(route('sof.view', $sof))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'inline; filename=SOF-001.pdf');
});

test('badge status memakai gaya yang sama dengan tabel dokumen', function () {
    makeSof(['order_number' => 'SOF/005/IX/2026']);
    makeSof(['order_number' => 'SOF/006/IX/2026', 'status' => 'selesai']);

    $rows = collect($this->actingAs($this->user)->getJson(route('sof.data'))->assertOk()->json('data'));

    // Urutan baris mengikuti sorting tabel (terbaru dulu), jadi cari per palet badge.
    $draft = $rows->first(fn ($row) => str_contains($row['status'], 'bg-amber-50'));
    $selesai = $rows->first(fn ($row) => str_contains($row['status'], 'bg-green-100'));

    expect($draft)->not->toBeNull()
        ->and($selesai)->not->toBeNull();

    // Draft mengikuti palet amber tabel dokumen, bukan slate solid.
    expect($draft['status'])->toContain('bg-amber-50')
        ->and($draft['status'])->toContain('border-amber-200')
        ->and($draft['status'])->toContain('dark:bg-amber-500/15')
        ->and($draft['status'])->not->toContain('bg-slate-100');

    // Selesai memakai palet green + border, sama seperti status "disetujui".
    expect($selesai['status'])->toContain('bg-green-100')
        ->and($selesai['status'])->toContain('border-green-200')
        ->and($selesai['status'])->toContain('dark:bg-green-500/15')
        ->and($selesai['status'])->toContain('>Selesai</span>');

    // Struktur span mengikuti partial dokumen (border + text-[11px]).
    expect($draft['status'])->toContain('rounded-full border px-2.5 py-1 text-[11px]');
});

test('form s.o.f punya tombol kalender untuk tanggal aktif', function () {
    $html = $this->actingAs($this->user)->get(route('sof.create'))->assertOk()->getContent();

    // Field tanggal + tombol kalender.
    expect($html)->toContain('id="active_date"')
        ->and($html)->toContain('name="active_date"')
        ->and($html)->toContain('id="active_date_picker_btn"')
        ->and($html)->toContain('aria-label="Pilih tanggal"')
        // Flatpickr diinisialisasi dengan format Y-m-d (format POST tidak berubah).
        ->and($html)->toContain("dateFormat: 'Y-m-d'")
        ->and($html)->toContain('window.flatpickr')
        // Native date input diganti text + tombol kalender.
        ->and($html)->not->toContain('type="date" id="active_date"');
});

test('form s.o.f tidak memanggil api flatpickr yang tidak ada', function () {
    $html = $this->actingAs($this->user)->get(route('sof.create'))->assertOk()->getContent();

    // instance.setPosition() tidak ada di flatpickr; memanggilnya melempar
    // TypeError yang memutus positionCalendar() sehingga kalender tak muncul.
    expect($html)->not->toContain('setPosition')
        ->and($html)->not->toContain('onOpen');

    // Wrapper penanda untuk override CSS bertema terang.
    expect($html)->toContain('sof-datepicker');

    // Tanpa `static`, flatpickr menaruh kalender di <body> sehingga selector
    // `.sof-datepicker .flatpickr-*` tidak pernah cocok.
    expect($html)->toContain('static: true')
        ->and($html)->toContain('custom-datepicker');
});

test('form s.o.f edit menyimpan tanggal aktif berformat Y-m-d', function () {
    makeSof(['active_date' => '2026-09-01']);

    $html = $this->actingAs($this->user)->get(route('sof.edit', Sof::first()))->assertOk()->getContent();
    expect($html)->toContain('value="2026-09-01"');

    // Nilai dari kalender harus tetap lolos validasi 'date' di server.
    $this->actingAs($this->user)
        ->post(route('sof.store'), [
            'customer_number' => '081234567890',
            'customer_name'   => 'PT Uji Tanggal',
            'active_date'     => '2026-09-15',
            'active_months'   => 6,
        ])
        ->assertRedirect(route('sof.index'));

    $sof = Sof::where('customer_name', 'PT Uji Tanggal')->firstOrFail();

    expect($sof->active_date)->not->toBeNull()
        ->and($sof->active_date->format('Y-m-d'))->toBe('2026-09-15')
        // Tanggal selesai = tanggal aktif + masa aktif (masih dihitung server).
        ->and($sof->finish_date->format('Y-m-d'))->toBe('2027-03-15');
});

test('status berlabel selesai bukan disetujui', function () {
    $sof = makeSof(['status' => 'selesai']);

    expect($sof->statusLabel())->toBe('Selesai')
        ->and(Sof::STATUSES)->toBe(['draft', 'selesai']);

    $rows = collect($this->actingAs($this->user)->getJson(route('sof.data'))->assertOk()->json('data'));
    expect($rows->first()['status'])->not->toContain('Disetujui');
});

test('kartu ringkasan hanya total dan selesai', function () {
    makeSof(['order_number' => 'SOF/008/IX/2026']);
    makeSof(['order_number' => 'SOF/009/IX/2026', 'status' => 'selesai']);

    // Key counts yang dipakai kartu di view.
    $counts = $this->actingAs($this->user)->getJson(route('sof.data'))->assertOk()->json('counts');

    expect($counts['total'])->toBe(2)
        ->and($counts['selesai'])->toBe(1);

    $html = $this->actingAs($this->user)->get(route('sof.index'))->assertOk()->getContent();

    // Hanya dua kartu yang tersisa: Total S.O.F & Selesai.
    expect(substr_count($html, 'data-count="'))->toBe(2)
        ->and($html)->toContain('data-count="total"')
        ->and($html)->toContain('data-count="selesai"')
        ->and($html)->toContain('>Selesai</p>')
        ->and($html)->not->toContain('data-count="with_file"')
        ->and($html)->not->toContain('data-count="approved"')
        ->and($html)->not->toContain('data-count="pending"');
});

test('halaman detail memakai badge status yang sama', function () {
    makeSof(['order_number' => 'SOF/007/IX/2026']);
    $sof = Sof::first();

    $this->actingAs($this->user)
        ->get(route('sof.show', $sof))
        ->assertOk()
        ->assertSee('bg-amber-50', false)
        ->assertSee('border-amber-200', false)
        ->assertDontSee('bg-slate-100 text-slate-700', false);
});

test('halaman lihat file menolak s.o.f tanpa berkas dan milik user lain', function () {
    $tanpaBerkas = makeSof(['order_number' => 'SOF/003/IX/2026']);

    $this->actingAs($this->user)
        ->get(route('sof.view', $tanpaBerkas))
        ->assertNotFound();

    $userLain = User::factory()->create();
    $userLain->assignRole('admin');

    Storage::disk('public')->put('sofs/2/SOF-002.pdf', '%PDF-1.4 uji');
    $milikLain = makeSof([
        'user_id'    => $userLain->id,
        'order_number' => 'SOF/004/IX/2026',
        'file_path'  => 'sofs/2/SOF-002.pdf',
        'file_name'  => 'SOF-002.pdf',
    ]);

    $this->actingAs($this->user)
        ->get(route('sof.view', $milikLain))
        ->assertForbidden();
});