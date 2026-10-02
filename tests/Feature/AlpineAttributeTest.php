<?php

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Js;


function alpineDataValues(string $html): array
{
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();

    $values = [];

    foreach ((new DOMXPath($dom))->query('//*[@x-data]') as $node) {
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

    expect($component)->not->toBeNull();
    expect($component)
        ->toContain('defaultHeaderHtml()')
        ->toContain('buildFooterHtml(fd)')
        ->toContain('loadTemplate(key)')
        ->toContain('submitForm(event)')
        ->toEndWith('}');

    expect(html_entity_decode($component, ENT_QUOTES))
        ->toContain('style="text-align:left;')
        ->toContain('src="/images/fibertrust.png"');

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
        expect(trim($value))->toEndWith('}')
            ->and($value)->toContain('defaultDate:')
            ->not->toContain('defaultDate: "');
    }

    expect($html)->toContain("defaultDate: '2026-09-26'")
        ->and($html)->toContain('defaultDate: null');
});

test('Js::from meng-escape karakter yang bisa menutup atribut x-data', function () {
    $hostile = ['quote"double', "apos'single", "back\\slash", '<tag>'];

    $encoded = Js::from($hostile)->toHtml();

    expect($encoded)->not->toContain('"');

    expect($encoded)
        ->toContain('JSON.parse(')
        ->not->toContain('<')
        ->not->toContain('&')
        ->toContain('\u0022')   
        ->toContain('\u0027');  
    expect(Js::from('2026-09-26')->toHtml())->toBe("'2026-09-26'")
        ->and(Js::from(null)->toHtml())->toBe('null')
        ->and(Js::from([])->toHtml())->toBe('[]');
});
