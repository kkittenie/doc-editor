<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

/*
 */

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
});

test('halaman dokumen saya memuat dropdown urutkan di samping bar pencarian', function () {
    $response = $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk();

    $response->assertSee('Urutkan', false)
        ->assertSee('id="customers-sort-menu"', false)
        ->assertSee('id="customers-search"', false)
        ->assertSee('data-sort="id"', false)
        ->assertSee('data-sort="name"', false)
        ->assertSee('data-sort="contract_number"', false);

    foreach ([
        'Pelanggan ID',
        'Nomer Pelanggan',
        'Nama Pelanggan',
        'Nomor Kontrak',
        'Nama Kontrak',
        'Tanggal Aktif',
        'Masa Aktif',
        'Tanggal Selesai',
        'Status',
    ] as $label) {
        $response->assertSee($label, false);
    }
});

test('header tabel tidak lagi menangani sort; pengurutan lewat API', function () {
    $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee('handler: false', false)
        ->assertSee('indicators: false', false)
        ->assertSee('table.order([sortColumns[field], sortState.dir]).draw()', false);
});

test('atribut Alpine dropdown urutkan utuh (tidak terpotong)', function () {
    $html = $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('x-data="sortMenuDropdown()"')
        ->toContain('@click.outside="close()"')
        ->toContain('x-ref="content"')
        ->toContain('top: ${top}px; left: ${left}px;')
        ->toContain('class="fixed z-50 max-h-[70vh] w-56');

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();

    $scope = null;
    foreach ((new DOMXPath($dom))->query('//*[@x-data]') as $node) {
        if ($node->getAttribute('x-data') === 'sortMenuDropdown()') {
            $scope = $node;
            break;
        }
    }

    expect($scope)->not->toBeNull();
});
