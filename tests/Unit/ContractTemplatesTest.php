<?php

use App\Data\ContractTemplates;
use App\Http\Controllers\DocumentController;

/**
 * Uji konsistensi template kontrak hasil konversi dari dokumen .docx resmi.
 * Body template wajib memuat teks asli dokumen (verbatim) dan penomoran
 * PASAL yang berurutan (1..N) baik pada data maupun HTML hasil render.
 */

function contractBuildBodyHtml(array $template, bool $center): string
{
    $ctrl   = new DocumentController();
    $method = new ReflectionMethod(DocumentController::class, 'buildTemplateBodyHtml');

    return $method->invoke($ctrl, $template['body_content'] ?? [], $center);
}

function contractNormalizePasal(string $html): string
{
    $ctrl   = new DocumentController();
    $method = new ReflectionMethod(DocumentController::class, 'normalizePasalNumbering');

    return $method->invoke($ctrl, [$html])[0];
}

$keys = [
    'kontrak-kemitraan',
    'kontrak-colocation',
    'kontrak-managed-service',
    'kontrak-soho',
    'kontrak-payung',
];

test('semua template kontrak tersedia', function () use ($keys) {
    expect(array_keys(ContractTemplates::all()))->toBe($keys);

    foreach ($keys as $key) {
        expect(ContractTemplates::find($key))->not->toBeNull();
    }
});

test('setiap template punya minimal 14 pasal, judul & isi tidak kosong', function () use ($keys) {
    foreach ($keys as $key) {
        $tpl = ContractTemplates::find($key);
        $isi = $tpl['body_content']['isi'] ?? [];

        expect(count($isi))->toBeGreaterThanOrEqual(14);

        foreach ($isi as $pasal) {
            expect($pasal['judul'] ?? '')->not->toBeEmpty();
            expect($pasal['text'] ?? '')->not->toBeEmpty();
        }
    }
});

test('numeral & klaim untuk setiap template dipertahankan', function () use ($keys) {
    // Kemitraan
    $tpl = ContractTemplates::find('kontrak-kemitraan');
    expect($tpl['title'])->toBe('Perjanjian Kerjasama Jual Kembali Jasa Layanan Akses Internet');
    expect($tpl['body_content']['preamble'])->toContain('NOMOR: 196/FBT/PKS/III/2026');
    expect($tpl['body_content']['isi'][0]['judul'])->toBe('DEFINISI');
    expect(implode("\n", array_column($tpl['body_content']['isi'], 'text')))
        ->toContain('Perjanjian adalah Perjanjian Kerja Sama Jual Kembali Jasa Layanan Akses Internet');
    expect($tpl['body_content']['tutup'] ?? '')->toContain('Adi Darmawan');

    // Colocation
    $tpl = ContractTemplates::find('kontrak-colocation');
    expect($tpl['body_content']['preamble'])->toContain('239/FBT/J.C/VII/2026');
    expect($tpl['body_content']['isi'][3]['judul'])
        ->toBe('JANGKA WAKTU PERJANJIAN, BERITA ACARA AKTIVASI, SUSPENSI LAYANAN DAN PEMBERHENTIAN');
    expect($tpl['body_content']['lampiran'][0]['judul'])->toBe('DESKRIPSI LAYANAN');
    expect($tpl['body_content']['tutup'] ?? '')->toContain('PT Solusindo Bintang Pratama');

    // Managed Service
    $tpl = ContractTemplates::find('kontrak-managed-service');
    expect($tpl['body_content']['preamble'])->toContain('335/FBC/J.MS/IV/2026');
    expect(count($tpl['body_content']['isi']))->toBe(14);
    expect($tpl['body_content']['lampiran'][1]['judul'])->toBe('LAMPIRAN B');

    // SOHO
    $tpl = ContractTemplates::find('kontrak-soho');
    expect($tpl['body_content']['preamble'])->toContain('152/FBT/J.S/IX/2025');
    expect(implode("\n", array_column($tpl['body_content']['isi'], 'text')))->toContain('(isolir) dalam');
    expect(count($tpl['body_content']['isi']))->toBe(14);

    // Payung
    $tpl = ContractTemplates::find('kontrak-payung');
    expect($tpl['body_content']['preamble'])
        ->toContain("(KONTRAK PAYUNG)\nBERLANGGANAN JASA METRO FIBER OPTIK");
    expect($tpl['body_content']['preamble'])->toContain('238/FBT/J.M/VI/2026');
    expect(count($tpl['body_content']['isi']))->toBe(15);
    expect($tpl['body_content']['lampiran'][0]['judul'])->toBe('LAMPIRAN BERLANGGANAN JASA I');
});

test('render buildTemplateBodyHtml menormalisasi PASAL menjadi 1..N berurutan', function () use ($keys) {
    foreach ($keys as $key) {
        $tpl  = ContractTemplates::find($key);
        $html = contractNormalizePasal(contractBuildBodyHtml($tpl, true));

        preg_match_all('/<p[^>]*><strong>PASAL (\d+)<\/strong><\/p>/i', $html, $m);
        $actual = array_map('intval', $m[1] ?? []);

        expect($actual)->toBe(range(1, count($tpl['body_content']['isi'])));
    }
});

test('isi render HTML memuat teks asli verbatim', function () {
    $tpl  = ContractTemplates::find('kontrak-kemitraan');
    $html = contractBuildBodyHtml($tpl, true);

    expect($html)->toContain('NOMOR: 196/FBT/PKS/III/2026');
    expect($html)->toContain('Jual Kembali Jasa Layanan Akses Internet');

    $tpl  = ContractTemplates::find('kontrak-payung');
    $html = contractBuildBodyHtml($tpl, true);

    expect($html)->toContain('PERJANJIAN KERJA SAMA (KONTRAK PAYUNG)');
    expect($html)->toContain('238/FBT/J.M/VI/2026');
});