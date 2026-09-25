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
            // isi pasal boleh berupa paragraf ('text') atau blok tabel ('blocks')
            $hasContent = ($pasal['text'] ?? '') !== '' || !empty($pasal['blocks']);
            expect($hasContent)->toBeTrue();
        }
    }
});

test('setiap template memuat tabel sesuai dokumen asli pada posisi yang benar', function () use ($keys) {
    // jumlah tabel per template: [isi, tutup, lampiran]
    $expected = [
        'kontrak-kemitraan'      => 6, // 1 pasal komunikasi + 1 ttd + 4 lampiran
        'kontrak-colocation'     => 2, // 1 ttd + 1 deskripsi layanan
        'kontrak-managed-service'=> 5, // 1 pasal denda + 1 ttd + 3 lampiran
        'kontrak-soho'           => 3, // 1 pasal denda + 1 ttd + 1 lampiran
        'kontrak-payung'         => 7, // 1 pasal denda + 1 ttd + 5 lampiran
    ];

    foreach ($keys as $key) {
        $tpl = ContractTemplates::find($key);
        $body = $tpl['body_content'];
        $count = 0;

        foreach ($body['isi'] as $pasal) {
            foreach ($pasal['blocks'] ?? [] as $b) {
                if (isset($b['table'])) { $count++; }
            }
        }
        foreach ($body['tutupBlocks'] ?? [] as $b) {
            if (isset($b['table'])) { $count++; }
        }
        foreach ($body['lampiran'] ?? [] as $l) {
            foreach ($l['blocks'] ?? [] as $b) {
                if (isset($b['table'])) { $count++; }
            }
        }

        expect($count)->toBe($expected[$key]);
    }

    // Kemitraan: tabel pemberitahuan di dalam pasal KOMUNIKASI/PEMBERITAHUAN
    $tpl = ContractTemplates::find('kontrak-kemitraan');
    $komunikasi = null;
    foreach ($tpl['body_content']['isi'] as $pasal) {
        if (str_contains($pasal['judul'], 'KOMUNIKASI')) { $komunikasi = $pasal; }
    }
    expect($komunikasi)->not->toBeNull();
    $tables = array_values(array_filter($komunikasi['blocks'], fn ($b) => isset($b['table'])));
    expect(count($tables))->toBe(1);
    expect(json_encode($tables[0]['table'], JSON_UNESCAPED_UNICODE))->toContain('admin@fibertrust.id');

    // Colocation: tabel DESKRIPSI LAYANAN ber-border di lampiran
    $tpl = ContractTemplates::find('kontrak-colocation');
    $lampTables = array_values(array_filter(
        $tpl['body_content']['lampiran'][0]['blocks'],
        fn ($b) => isset($b['table'])
    ));
    $tbl = $lampTables[0]['table'] ?? null;
    expect($tbl)->not->toBeNull();
    expect($tbl['bordered'])->toBeTrue();
    expect(json_encode($tbl, JSON_UNESCAPED_UNICODE))->toContain('Colocation Full Rack 42 U');

    // vertical merge: sel "Jangka Waktu Berlangganan" restart di baris data
    // pertama dan berlanjut (CONT) di 4 baris berikutnya
    expect($tbl['rows'][1][4]['v'] ?? null)->toBe('r');
    foreach ([2, 3, 4, 5] as $vr) {
        expect($tbl['rows'][$vr][4]['v'] ?? null)->toBe('c');
    }

    // Tabel tanda tangan tanpa border (seperti dokumen asli)
    $sig = $tpl['body_content']['tutupBlocks'][0]['table'];
    expect($sig['bordered'])->toBeFalse();
    expect(json_encode($sig, JSON_UNESCAPED_UNICODE))->toContain('PT Bina Informatika Solusi');

    // Managed Service: tabel denda di pasal 4 & LAMPIRAN A identitas ber-border
    $tpl = ContractTemplates::find('kontrak-managed-service');
    expect(json_encode($tpl['body_content']['isi'][3]['blocks'], JSON_UNESCAPED_UNICODE))
        ->toContain('100% x Biaya Bulanan x Bulan yang belum terpenuhi');
    $lampATables = array_values(array_filter(
        $tpl['body_content']['lampiran'][0]['blocks'],
        fn ($b) => isset($b['table'])
    ));
    $lampA = $lampATables[0]['table'] ?? null;
    expect($lampA)->not->toBeNull();
    expect($lampA['bordered'])->toBeTrue();
    expect(json_encode($lampA, JSON_UNESCAPED_UNICODE))->toContain('Meta Content 3 (tiga) Gbps');

    // SOHO: LAMPIRAN A identitas memuat jenis layanan multi-baris
    $tpl = ContractTemplates::find('kontrak-soho');
    expect(json_encode($tpl['body_content']['lampiran'][0]['blocks'], JSON_UNESCAPED_UNICODE))
        ->toContain('SOHO TIF 200Mbps');

    // Payung: tabel DETAIL & KOMERSIAL LAYANAN dengan baris header bold
    $tpl = ContractTemplates::find('kontrak-payung');
    $json = json_encode($tpl['body_content']['lampiran'][0]['blocks'], JSON_UNESCAPED_UNICODE);
    expect($json)->toContain('DETAIL & KOMERSIAL LAYANAN');
    expect($json)->toContain('APJII Cyber Jakarta <> APJII Jabar 8 BBU Bandung');
    $tables = array_values(array_filter($tpl['body_content']['lampiran'][0]['blocks'], fn ($b) => isset($b['table'])));
    expect(count($tables))->toBe(5); // identitas, detail & komersial, SLA ×2, ttd lampiran
    expect($tables[1]['table']['head'])->toBeTrue();
});

test('numeral & klaim untuk setiap template dipertahankan', function () use ($keys) {
    // Kemitraan
    $tpl = ContractTemplates::find('kontrak-kemitraan');
    expect($tpl['title'])->toBe('Perjanjian Kerjasama Jual Kembali Jasa Layanan Akses Internet');
    expect($tpl['body_content']['preamble'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->not->toContain('NOMOR: 196/FBT/PKS/III/2026');
    expect($tpl['body_content']['isi'][0]['judul'])->toBe('DEFINISI');
    expect(implode("\n", array_column($tpl['body_content']['isi'], 'text')))
        ->toContain('Perjanjian adalah Perjanjian Kerja Sama Jual Kembali Jasa Layanan Akses Internet');
    expect($tpl['body_content']['tutup'] ?? '')->toContain('[PIHAK KEDUA]');

    // Colocation
    $tpl = ContractTemplates::find('kontrak-colocation');
    expect($tpl['body_content']['preamble'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->not->toContain('239/FBT/J.C/VII/2026');
    expect($tpl['body_content']['isi'][3]['judul'])
        ->toBe('JANGKA WAKTU PERJANJIAN, BERITA ACARA AKTIVASI, SUSPENSI LAYANAN DAN PEMBERHENTIAN');
    expect($tpl['body_content']['lampiran'][0]['judul'])->toBe('DESKRIPSI LAYANAN');
    expect($tpl['body_content']['tutup'] ?? '')->toContain('PT Bina Informatika Solusi');

    // Managed Service
    $tpl = ContractTemplates::find('kontrak-managed-service');
    expect($tpl['body_content']['preamble'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->not->toContain('335/FBC/J.MS/IV/2026');
    expect(count($tpl['body_content']['isi']))->toBe(14);
    expect($tpl['body_content']['lampiran'][1]['judul'])->toBe('LAMPIRAN B');

    // SOHO
    $tpl = ContractTemplates::find('kontrak-soho');
    expect($tpl['body_content']['preamble'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->not->toContain('152/FBT/J.S/IX/2025');
    expect(implode("\n", array_column($tpl['body_content']['isi'], 'text')))->toContain('(isolir) dalam');
    expect(count($tpl['body_content']['isi']))->toBe(14);

    // Payung
    $tpl = ContractTemplates::find('kontrak-payung');
    expect($tpl['body_content']['preamble'])
        ->toContain("(KONTRAK PAYUNG)\nBERLANGGANAN JASA METRO FIBER OPTIK");
    expect($tpl['body_content']['preamble'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->not->toContain('238/FBT/J.M/VI/2026');
    expect(count($tpl['body_content']['isi']))->toBe(15);
    expect($tpl['body_content']['lampiran'][0]['judul'])->toBe('LAMPIRAN BERLANGGANAN JASA I');
});

test('normalisasi pasal hanya mengubah judul pasal, bukan referensi di isi paragraf', function () {
    $html = '<p>Dalam pasal 4 ayat (1), PIHAK PERTAMA akan ...</p>'
        . '<p><strong>PASAL 1 — PENGERTIAN</strong></p>'
        . '<p><strong>PASAL 2 — HAK DAN KEWAJIBAN</strong></p>';

    $normalized = contractNormalizePasal($html);

    expect($normalized)->toContain('Dalam pasal 4 ayat (1)');
    expect($normalized)->toContain('<strong>PASAL 1');
    expect($normalized)->toContain('<strong>PASAL 2');
    expect($normalized)->not->toContain('Dalam <strong>PASAL');
});

test('normalisasi pasal tahan nbsp, judul tanpa tanda hubung, dan heading menempel', function () {
    $html = '<p><strong>PASAL&nbsp;5</strong></p>'
        . '<p><strong>PASAL 6 BIAYA DAN BEBAN</strong></p>'
        . '<p>PASAL 7Judul yang menempel langsung pada nomor</p>'
        . '<p>Pasal 1266 KUHPerdata dikecualikan sehingga Pasal ini tetap berlaku.</p>';

    $normalized = contractNormalizePasal($html);

    // Nomor heading diurutkan ulang 1..3 (5,6,7 → 1,2,3).
    expect($normalized)->toContain('<strong>PASAL 1');
    expect($normalized)->toContain('<strong>PASAL 2 BIAYA DAN BEBAN');
    expect($normalized)->toContain('PASAL 3Judul yang menempel');
    // Rujukan isi paragraf TIDAK ikut dinomori (1266 tetap 1266).
    expect($normalized)->toContain('Pasal 1266 KUHPerdata');
    expect($normalized)->not->toContain('PASAL 4');
});

test('normalisasi memperbaiki lompatan heading: PASAL 1 lalu PASAL 15 jadi 1,2', function () {
    $html = '<p><strong>PASAL 1</strong></p><p>isi satu</p>'
        . '<p><strong>PASAL 15</strong></p><p>isi dua</p>';

    $normalized = contractNormalizePasal($html);

    expect($normalized)->toContain('<strong>PASAL 1');
    expect($normalized)->toContain('<strong>PASAL 2');
    expect($normalized)->not->toContain('PASAL 15');
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

    expect($html)->toContain('NOMOR: [Nomor Perjanjian]');
    expect($html)->not->toContain('196/FBT/PKS/III/2026');
    expect($html)->toContain('Jual Kembali Jasa Layanan Akses Internet');

    $tpl  = ContractTemplates::find('kontrak-payung');
    $html = contractBuildBodyHtml($tpl, true);

    expect($html)->toContain('PERJANJIAN KERJA SAMA (KONTRAK PAYUNG)');
    expect($html)->toContain('Kontrak Payung Nomor: [Nomor Perjanjian]');
    expect($html)->not->toContain('238/FBT/J.M/VI/2026');
});

test('render HTML memunculkan tabel asli dengan border & posisi yang benar', function () use ($keys) {
    $counts = [
        'kontrak-kemitraan'       => 6,
        'kontrak-colocation'      => 2,
        'kontrak-managed-service' => 5,
        'kontrak-soho'            => 3,
        'kontrak-payung'          => 7,
    ];

    foreach ($keys as $key) {
        $tpl  = ContractTemplates::find($key);
        $html = contractBuildBodyHtml($tpl, true);

        // jumlah <table> sama dengan jumlah tabel dokumen asli
        expect(substr_count($html, '<table'))->toBe($counts[$key]);

        // tabel data ber-border, tabel tanda tangan tanpa border
        if (in_array($key, ['kontrak-colocation', 'kontrak-managed-service', 'kontrak-soho', 'kontrak-payung'], true)) {
            expect($html)->toContain('border:1px solid #000;');
            expect($html)->toContain('border:none;');
        }
    }

    // kemitraan: tabel pemberitahuan muncul SETELAH heading pasal KOMUNIKASI/PEMBERITAHUAN
    $tpl  = ContractTemplates::find('kontrak-kemitraan');
    $html = contractBuildBodyHtml($tpl, true);
    $pos  = strpos($html, 'KOMUNIKASI/PEMBERITAHUAN');
    $tblPos = strpos($html, 'admin@fibertrust.id');
    expect($pos)->toBeInt();
    expect($tblPos)->toBeInt();
    expect($tblPos)->toBeGreaterThan($pos);

    // sel tanda tangan mempertahankan baris paragraf (<br>) — penanda pihak
    // ditebalkan renderer (styleContractPartyNames) sehingga ber <strong>.
    expect($html)->toContain('<strong>PIHAK PERTAMA</strong><br>');

    // vertical merge dirender sebagai rowspan (bukan kolom kosong tambahan):
    // tabel deskripsi layanan colocation = 5 kolom, sel jangka waktu rowspan=5
    $tpl  = ContractTemplates::find('kontrak-colocation');
    $html = contractBuildBodyHtml($tpl, true);
    $pos  = strpos($html, 'Jangka Waktu Berlangganan');
    expect($pos)->toBeInt();
    expect(strpos($html, 'rowspan="5"', (int) $pos))->toBeInt();
    // tidak ada lagi sel kosong ekstra pada baris data (kolom hantu hilang)
    expect(substr_count($html, '<td></td>'))->toBe(0);
});

test('tabel formula satu baris ditandai atomik agar tidak berkedip di batas halaman', function () {
    // Regresi bug "teks blink/kelap-kelip" di akhir Managed Service:
    // tabel formula satu baris harus membawa penanda blok atomik paginasi.
    foreach (['kontrak-managed-service', 'kontrak-payung'] as $key) {
        $tpl  = ContractTemplates::find($key);
        $html = contractBuildBodyHtml($tpl, true);

        expect($html)->toContain('Service Level Guarantee');
        expect($html)->toContain('Monthly Cost.');
        expect($html)->toContain('data-flow-atomic="1"');
    }
});
test('render numbering selektif: blok yang memang list me-render menjadi <ol>', function () use ($keys) {
    // kemitraan: definisi + hak/kewajiban secara selektif jadi daftar bernomor
    $tpl  = ContractTemplates::find('kontrak-kemitraan');
    $html = contractBuildBodyHtml($tpl, false);
    expect($html)->toContain('list-style-type:decimal');
    expect($html)->toContain('list-style-type:lower-alpha');
    expect(strpos($html, '<ol'))->toBeInt();
    // rincian hak PIHAK PERTAMA adalah daftar (nama pihak di-bold oleh renderer)
    expect($html)->toContain('<li>menerima pembayaran dari ');

    // colocation: ayat pasal bernomor + sub-list A + lanjutan start
    $tpl  = ContractTemplates::find('kontrak-colocation');
    $html = contractBuildBodyHtml($tpl, false);
    expect($html)->toContain('list-style-type:upper-alpha');
    expect($html)->toContain('start="3"');  // PASAL 9 lanjutan
    expect($html)->toContain('start="8"');  // PASAL 3 lanjutan
    expect(strpos($html, '<ol'))->toBeInt();

    // lampiran: Syarat (decimal) + Fasilitas (A..D nested decimal)
    expect($html)->toContain('<li>Harga diatas belum termasuk PPN');
});

test('house style kanonik: heading, judul, body, list & tabel dari token ContractStyle', function () use ($keys) {
    $cs = \App\Data\ContractStyle::class;

    foreach ($keys as $key) {
        $tpl  = ContractTemplates::find($key);
        $html = contractNormalizePasal(contractBuildBodyHtml($tpl, true));

        // Heading PASAL: center + 11pt bold + jarak 16pt atas / 4pt bawah.
        expect($html)->toContain(
            'text-align:' . $cs::ALIGN_HEADING
            . '; font-size:' . $cs::SIZE_HEADING
            . '; font-weight:bold;'
            . ' margin-top:' . $cs::HEADING_SPACE_BEFORE
            . '; margin-bottom:' . $cs::HEADING_SPACE_AFTER . ';'
        );

        // Blok judul preamble: 16pt bold center, space-bawah 12pt.
        expect($html)->toContain(
            'text-align:' . $cs::ALIGN_TITLE
            . '; font-size:' . $cs::SIZE_TITLE
            . '; font-weight:bold;'
            . ' margin-top:' . $cs::TITLE_SPACE_BEFORE
            . '; margin-bottom:' . $cs::TITLE_SPACE_AFTER . ';'
        );

        // Sel tabel: ukuran & border dari token tabel.
        expect($html)->toContain('font-size:' . $cs::SIZE_TABLE . ';');
        expect($html)->toContain('border:' . $cs::TABLE_BORDER . ';');

        // Body justify & ukuran TIDAK ditulis inline — disediakan CSS
        // partials/contract-style.blade.php supaya alignment pilihan user
        // (ql-align-*) tidak tertimpa.
        expect($html)->not->toContain('<p style="text-align:' . $cs::ALIGN_BODY);
    }

    // Semua <ol> kontrak memakai penanda LIST_CLASS + kelas ql-liststyle
    // bertoken tunggal (tanpa tanda hubung) agar tipe numbering selamat
    // dari round-trip Quill (ClassAttributor.keys() memotong segmen '-').
    $html = contractBuildBodyHtml(ContractTemplates::find('kontrak-colocation'), false);
    expect($html)->toContain('<ol class="' . $cs::LIST_CLASS . ' ql-liststyle-decimal"');
    expect($html)->toContain('ql-liststyle-upperalpha');
    expect($html)->toContain('list-style-type:' . $cs::listCssType('A'));
    expect($html)->toContain('padding-left:' . $cs::listIndent(1));

    $html = contractBuildBodyHtml(ContractTemplates::find('kontrak-kemitraan'), false);
    expect($html)->toContain('ql-liststyle-alpha');
    expect($html)->toContain('class="' . $cs::LIST_CLASS . ' ');
});

test('template kontrak bebas nomor kontrak hardcode', function () use ($keys) {
    // Nomor dokumen hanya boleh datang dari form (header_data.nomorSurat)
    // lewat placeholder [Nomor Perjanjian] yang diganti
    // DocumentController::applyTemplateReplacements() saat pembuatan dokumen.
    $stale = [
        '196/FBT/PKS/III/2026',
        '239/FBT/J.C/VII/2026',
        '335/FBC/J.MS/IV/2026',
        '152/FBT/J.S/IX/2025',
        '238/FBT/J.M/VI/2026',
    ];

    foreach ($keys as $key) {
        $tpl  = ContractTemplates::find($key);
        $html = contractNormalizePasal(contractBuildBodyHtml($tpl, true));

        foreach ($stale as $nomor) {
            expect($html)->not->toContain($nomor);
        }

        // Tiap template tetap memuat placeholder untuk diisi form.
        expect($tpl['body_content']['preamble'])->toContain('[Nomor Perjanjian]');
    }
});

test('kontrak-soho punya kunci cover terpisah dari preamble (halaman 1 & 2 PDF sumber)', function () {
    $tpl   = ContractTemplates::find('kontrak-soho');
    $cover = (string) ($tpl['body_content']['cover'] ?? '');

    expect($cover)->not->toBeEmpty();
    expect($cover)->toContain('PERJANJIAN BERLANGGANAN');
    expect($cover)->toContain('JASA SOHO');
    expect($cover)->toContain('DENGAN');
    expect($cover)->toContain('[PIHAK KEDUA]');
    expect($cover)->toContain('Nomor: [Nomor Perjanjian]');
    // Cover bebas nomor kontrak hardcode sumber.
    expect($cover)->not->toContain('152/FBT/J.S/IX/2025');

    // Halaman 2 tetap dibuka judul + nomor + naratif pembuka verbatim.
    expect($tpl['body_content']['preamble'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->toContain('Pada hari ini, [Hari]');
    expect($tpl['body_content']['preamble'])->toContain('I. PT Bina Informatika Solusi');
    expect($tpl['body_content']['preamble'])->toContain('II. [PIHAK KEDUA]');
});

test('buildContractCoverHtml merender baris sampul memakai token ContractStyle', function () {
    $tpl  = ContractTemplates::find('kontrak-soho');
    $ctrl = new DocumentController();
    $m    = new ReflectionMethod(DocumentController::class, 'buildContractCoverHtml');

    $html = $m->invoke($ctrl, (string) $tpl['body_content']['cover']);
    $cs   = \App\Data\ContractStyle::class;

    // 6 baris cover: judul, sub-judul, nama pihak pertama, DENGAN, pihak
    // kedua, nomor — masing-masing satu paragraf center.
    expect(substr_count($html, '<p '))->toBe(6);
    expect($html)->toContain('<strong>PERJANJIAN BERLANGGANAN</strong>');
    expect($html)->toContain($cs::coverStyle('PERJANJIAN BERLANGGANAN', true, false));
    expect($html)->toContain($cs::coverStyle('Nomor: [Nomor Perjanjian]', false, true));

    // Baris kosong tidak menghasilkan paragraf kosong.
    expect($m->invoke($ctrl, "  \n "))->toBe('');
});