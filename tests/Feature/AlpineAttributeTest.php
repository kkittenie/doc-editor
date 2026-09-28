<?php

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Js;

/**
 * Regresi untuk error Alpine di halaman "Buat Dokumen Baru".
 *
 * Atribut x-data diapit tanda kutip ganda. Bila string JS di dalamnya memuat
 * " mentah (mis. markup <p style="...">), parser HTML memotong atribut tepat
 * di sana. Alpine menerima x-data terpotong -> "Invalid or unexpected token",
 * dan karena scope-nya tidak pernah terbentuk, SETAPUS properti di dalamnya
 * (headerHtml, footerHtml, selectedTemplate, ...) meledak menjadi
 * "X is not defined" untuk seluruh kartu template.
 *
 * PHP tidak punya parser HTML, jadi test ini sengaja membaca atribut lewat
 * DOMDocument — itulah yang mereproduksi perilaku browser.
 */

/** Ekstrak nilai semua atribut x-data dari HTML yang sudah dirender. */
function alpineDataValues(string $html): array
{
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();

    $values = [];

    foreach ((new DOMXPath($dom))->query('//*[@x-data]') as $node) {
        // x-data kosong sah (mis. penanda "sudah di-init" di layout) dan bukan
        // sasaran bug ini, jadi dilewati.
        if (trim($node->getAttribute('x-data')) === '') {
            continue;
        }

        $values[] = $node->getAttribute('x-data');
    }

    return $values;
}

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('admin');

    $this->customer = Customer::create([
        'user_id' => $this->user->id,
        'customer_number' => '081234567890',
        'name' => 'PT Uji Regresi',
        'contract_number' => 'KTR/001/IX/2026',
        'status' => 'draft',
        'active_date' => '2026-09-01',
        'active_months' => 3,
    ]);
});

test('x-data halaman buat dokumen tidak terpotong tanda kutip', function () {
    $response = $this->actingAs($this->user)
        ->get(route('documents.create', ['customer' => $this->customer->id]))
        ->assertOk();

    $values = alpineDataValues($response->getContent());

    expect($values)->not->toBeEmpty();

    $component = collect($values)->first(fn (string $v) => str_contains($v, 'selectedTemplate'));

    // Kalau atribut terpotong, nilai pertama yang memuat selectedTemplate akan
    // berhenti di tengah (legacy: 'headerHtml: \'<p style=').
    expect($component)->not->toBeNull();

    // Seluruh definisi & method harus utuh di dalam satu nilai atribut.
    expect($component)
        ->toContain('defaultHeaderHtml()')
        ->toContain('buildFooterHtml(fd)')
        ->toContain('loadTemplate(key)')
        ->toContain('submitForm(event)')
        // Penutup objek JS wajib ada — penanda atribut tidak terpotong.
        ->toEndWith('}');

    // Kutip yang sah ditulis &quot; di sumber, lalu didekode browser menjadi "
    // sebelum Alpine mengevaluasinya — jadi markup kop tetap utuh saat
    // dirender. Wajib ada tanda kutip utuh di dalam nilai atribut.
    expect(html_entity_decode($component, ENT_QUOTES))
        ->toContain('style="text-align:left;')
        ->toContain('src="/images/fibertrust.png"');

    // Sumber HTML tidak boleh memuat markup mentah yang memotong atribut
    // (pola bug asli: '<p style="' di dalam x-data).
    expect($response->getContent())->not->toContain('\'<p style="');
});

test('tidak ada atribut x-data terpotong di halaman buat dokumen', function () {
    $response = $this->actingAs($this->user)
        ->get(route('documents.create', ['customer' => $this->customer->id]))
        ->assertOk();

    foreach (alpineDataValues($response->getContent()) as $value) {
        expect(trim($value))->toEndWith('}');
    }
});

test('kartu template menampilkan label utuh tanpa atribut bocor', function () {
    $this->actingAs($this->user)
        ->get(route('documents.create', ['customer' => $this->customer->id]))
        ->assertOk()
        ->assertSee('Gunakan Template Ini', false)
        ->assertSee('Template Terpilih', false);
});

test('date-picker merender defaultDate untuk string dan null', function () {
    $html = Blade::render('<x-form.date-picker defaultDate="2026-09-26" />')
        . Blade::render('<x-form.date-picker />');

    foreach (alpineDataValues($html) as $value) {
        // Atribut utuh dan tidak ada " mentah yang memotong x-data.
        expect(trim($value))->toEndWith('}')
            ->and($value)->toContain('defaultDate:')
            ->not->toContain('defaultDate: "');
    }

    // Js::from() menulis string sebagai petik tunggal (JSON_HEX_APOS), bukan
    // " yang akan memotong atribut x-data.
    expect($html)->toContain("defaultDate: '2026-09-26'")
        ->and($html)->toContain('defaultDate: null');
});

test('Js::from meng-escape karakter yang bisa menutup atribut x-data', function () {
    // Nilai bermuatan " dan ' — persis kelas nilai yang dulu merusak atribut
    // x-data. Blade::render tidak bisa mengirim array ke atribut komponen, jadi
    // helper yang dipakai komponen diuji langsung.
    $hostile = ['quote"double', "apos'single", "back\\slash", '<tag>'];

    $encoded = Js::from($hostile)->toHtml();

    // Syarat mutlak: tidak boleh ada " mentah, karena itulah satu-satunya
    // karakter yang bisa menutup atribut x-data="..." lebih awal.
    expect($encoded)->not->toContain('"');

    // Tag/&/kutip ganda ikut di-escape supaya tidak ditafsirkan sebagai HTML.
    // (Petik tunggal hanya muncul sebagai pembungkus JSON.parse, bukan di data.)
    expect($encoded)
        ->toContain('JSON.parse(')
        ->not->toContain('<')
        ->not->toContain('&')
        ->toContain('\u0022')   // " → \u0022
        ->toContain('\u0027');  // ' → \u0027

    // String biasa diserialisasi sebagai petik tunggal, bukan " yang berbahaya.
    expect(Js::from('2026-09-26')->toHtml())->toBe("'2026-09-26'")
        ->and(Js::from(null)->toHtml())->toBe('null')
        ->and(Js::from([])->toHtml())->toBe('[]');
});
