<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;


beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
});

test('form pelanggan punya tombol kalender untuk tanggal aktif', function () {
    $html = $this->actingAs($this->user)->get(route('documents'))->assertOk()->getContent();

    expect($html)->toContain('id="customer-active-date"')
        ->and($html)->toContain('id="customer-active-date-picker"')
        ->and($html)->toContain('aria-label="Pilih tanggal"')
        ->and($html)->toContain("dateFormat: 'Y-m-d'")
        ->and($html)->toContain('window.flatpickr')
        ->and($html)->toContain('customer-datepicker custom-datepicker relative')
        ->and($html)->not->toContain('type="date" id="customer-active-date"');
});

test('nilai tanggal aktif tetap dikirim saat submit', function () {
    $this->actingAs($this->user)
        ->get(route('documents'))
        ->assertOk()
        ->assertSee('<input type="hidden" name="active_date" :value="activeDate">', false);
});

test('flatpickr memakai opsi static agar kalender menempel di wrapper', function () {
    $html = $this->actingAs($this->user)->get(route('documents'))->assertOk()->getContent();
    expect($html)->toContain('static: true')
        ->and($html)->toContain('allowInput: true')
        ->and($html)->toContain('monthSelectorType: \'static\'');
});