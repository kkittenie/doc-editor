<?php

use App\Data\ContractTemplates;
use App\Http\Controllers\DocumentController;
use App\Models\Customer;

/**
 */

function contractBuildBodyHtml(array $template, bool $center, bool $attachmentStartsNewPage = false): string
{
    $ctrl   = new DocumentController();
    $method = new ReflectionMethod(DocumentController::class, 'buildTemplateBodyHtml');

    return $method->invoke($ctrl, $template['body_content'] ?? [], $center, $attachmentStartsNewPage);
}

function contractNormalizePasal(string $html): string
{
    $ctrl   = new DocumentController();
    $method = new ReflectionMethod(DocumentController::class, 'normalizePasalNumbering');

    return $method->invoke($ctrl, [$html])[0];
}

function contractRepairSplitLists(array $pages): array
{
    $ctrl   = new DocumentController();
    $method = new ReflectionMethod(DocumentController::class, 'repairSplitOrderedLists');

    return $method->invoke($ctrl, $pages);
}

$keys = [
    'kontrak-kemitraan',
    'kontrak-colocation',
    'kontrak-payung',
    'kontrak-soho',
    'kontrak-managed-service',
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
            $hasContent = ($pasal['text'] ?? '') !== '' || !empty($pasal['blocks']);
            expect($hasContent)->toBeTrue();
        }
    }
});

test('setiap template memuat tabel sesuai dokumen asli pada posisi yang benar', function () use ($keys) {
    $expected = [
        'kontrak-kemitraan'      => 5, 
        'kontrak-colocation'     => 3,
        'kontrak-managed-service'=> 5, 
        'kontrak-soho'           => 3, 
        'kontrak-payung'         => 7, 
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

    $tpl = ContractTemplates::find('kontrak-kemitraan');
    $komunikasi = null;
    foreach ($tpl['body_content']['isi'] as $pasal) {
        if (str_contains($pasal['judul'], 'KOMUNIKASI')) { $komunikasi = $pasal; }
    }
    expect($komunikasi)->not->toBeNull();
    $lists = array_values(array_filter($komunikasi['blocks'], fn ($b) => isset($b['ol'])));
    expect(count($lists))->toBe(1);
    expect(json_encode($lists[0]['ol'], JSON_UNESCAPED_UNICODE))->toContain('admin@fibertrust.id');

    $tpl = ContractTemplates::find('kontrak-colocation');
    $lampTables = array_values(array_filter(
        $tpl['body_content']['lampiran'][0]['blocks'],
        fn ($b) => isset($b['table'])
    ));
    $tbl = $lampTables[0]['table'] ?? null;
    expect($tbl)->not->toBeNull();
    expect($tbl['bordered'])->toBeTrue();
    expect(json_encode($tbl, JSON_UNESCAPED_UNICODE))->toContain('Colocation Full Rack 42 U');

    expect($tbl['rows'][1][4]['v'] ?? null)->toBe('r');
    foreach ([2, 3, 4, 5] as $vr) {
        expect($tbl['rows'][$vr][4]['v'] ?? null)->toBe('c');
    }

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
    $html = contractBuildBodyHtml($tpl, true, true);
    expect(strpos($html, 'PASAL 7'))->toBeLessThan(strpos($html, 'PASAL 8'));
    expect(strpos($html, 'PASAL 15'))->toBeLessThan(strpos($html, 'LAMPIRAN BERLANGGANAN JASA I'));
    expect($html)->not->toContain('<div style="page-break-before: always;"></div>');
    expect($html)->toMatch('/<p style="page-break-before:always;[^"]*"><strong>LAMPIRAN BERLANGGANAN JASA I<\/strong><\/p>/');
    $tables = array_values(array_filter($tpl['body_content']['lampiran'][0]['blocks'], fn ($b) => isset($b['table'])));
    expect(count($tables))->toBe(5); // identitas, detail & komersial, SLA ×2, ttd lampiran

    $identity = $tables[0]['table'];
    expect($identity['bordered'])->toBeTrue();
    expect($identity['head'])->toBeFalse();
    expect($identity['rows'])->toHaveCount(6);
    foreach ($identity['rows'] as $row) {
        expect($row)->toHaveCount(2);
    }
    expect(array_map(fn ($row) => implode(' ', $row[0]['c']), $identity['rows']))->toBe([
        'Nama dan Alamat Pelanggan',
        'NPWP',
        'Nomor Telepon Pelanggan / Penanggungjawab',
        'Nomor Handphone Pelanggan / Penanggungjawab',
        'Nama Penanggungjawab Administrasi/Keuangan',
        'Nama Penanggungjawab Teknisi',
    ]);

    $detail = $tables[1]['table'];
    expect($detail['bordered'])->toBeTrue();
    expect($detail['head'])->toBeTrue();
    expect($detail['cols'])->toHaveCount(7);
    expect(array_sum($detail['cols']))->toBe(100);
    expect($detail['rows'])->toHaveCount(3);
    foreach ($detail['rows'] as $row) {
        expect($row)->toHaveCount(7);
    }
    expect(array_map(fn ($cell) => implode(' ', $cell['c']), $detail['rows'][0]))->toBe([
        'No',
        'Lokasi Layanan',
        'Rute Metro',
        'Bandwidth',
        'Biaya Bulanan',
        'Biaya Instalasi',
        'Masa Berlaku',
    ]);

    $blocks = $tpl['body_content']['lampiran'][0]['blocks'];
    $detailBlock = array_search($tables[1], $blocks, true);
    expect($blocks[$detailBlock + 1]['p'] ?? null)->toBe('* Biaya tercantum belum termasuk PPn');
});

test('numeral & klaim untuk setiap template dipertahankan', function () use ($keys) {
    // Kemitraan
    $tpl = ContractTemplates::find('kontrak-kemitraan');
    expect($tpl['title'])->toBe('Perjanjian Kerjasama Jual Kembali Jasa Layanan Akses Internet');
    expect($tpl['body_content']['cover'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->toStartWith('Pada hari');
    expect($tpl['body_content']['preamble'])->not->toContain('NOMOR: 196/FBT/PKS/III/2026');
    expect($tpl['body_content']['isi'][0]['judul'])->toBe('DEFINISI');
    expect(implode("\n", array_column($tpl['body_content']['isi'], 'text')))
        ->toContain('Perjanjian adalah Perjanjian Kerja Sama Jual Kembali Jasa Layanan Akses Internet');
    expect($tpl['body_content']['tutup'] ?? '')->toContain('[PIHAK KEDUA]');

    // Colocation
    $tpl = ContractTemplates::find('kontrak-colocation');
    expect($tpl['body_content']['cover'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->toStartWith('Pada hari');
    expect($tpl['body_content']['preamble'])->not->toContain('239/FBT/J.C/VII/2026');
    expect($tpl['body_content']['isi'][3]['judul'])
        ->toBe('JANGKA WAKTU PERJANJIAN, BERITA ACARA AKTIVASI, SUSPENSI LAYANAN DAN PEMBERHENTIAN');
    expect($tpl['body_content']['lampiran'][0]['judul'])->toBe('DESKRIPSI LAYANAN');
    expect($tpl['body_content']['tutup'] ?? '')->toContain('PT Bina Informatika Solusi');

    // Managed Service
    $tpl = ContractTemplates::find('kontrak-managed-service');
    expect($tpl['body_content']['cover'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->toStartWith('Pada hari');
    expect($tpl['body_content']['preamble'])->not->toContain('335/FBC/J.MS/IV/2026');
    expect(count($tpl['body_content']['isi']))->toBe(14);
    expect($tpl['body_content']['lampiran'][1]['judul'])->toBe('LAMPIRAN B');

    // SOHO
    $tpl = ContractTemplates::find('kontrak-soho');
    expect($tpl['body_content']['cover'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->toStartWith('Pada hari');
    expect($tpl['body_content']['preamble'])->not->toContain('152/FBT/J.S/IX/2025');
    expect(implode("\n", array_column($tpl['body_content']['isi'], 'text')))->toContain('(isolir) dalam');
    expect(count($tpl['body_content']['isi']))->toBe(14);

    // Payung
    $tpl = ContractTemplates::find('kontrak-payung');
    // Baris judul sampul = placeholder (diisi form "Nama Kontrak"), sub-judul
    // tetap hardcode tepat setelahnya.
    expect($tpl['body_content']['cover'])
        ->toMatch('/\[Nama Kontrak\]\s+BERLANGGANAN JASA METRO FIBER OPTIK/');
    expect($tpl['body_content']['cover'])->toContain('Nomor: [Nomor Perjanjian]');
    expect($tpl['body_content']['preamble'])->toStartWith('Pada hari');
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

    // Judul kontrak kini hanya di sampul (cover) & kop, tidak diulang di body.
    expect($html)->toContain('Kontrak Payung Nomor: [Nomor Perjanjian]');
    expect($html)->not->toContain('238/FBT/J.M/VI/2026');
});

test('render HTML memunculkan tabel asli dengan border & posisi yang benar', function () use ($keys) {
    $counts = [
        'kontrak-kemitraan'       => 5, // PASAL 17 komunikasi kini teks <ol>, bukan tabel
        'kontrak-colocation'      => 3,
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

    // kemitraan: TEKS pemberitahuan (<ol>, bukan tabel) muncul SETELAH heading pasal KOMUNIKASI/PEMBERITAHUAN
    $tpl  = ContractTemplates::find('kontrak-kemitraan');
    $html = contractBuildBodyHtml($tpl, true);
    $pos  = strpos($html, 'KOMUNIKASI/PEMBERITAHUAN');
    $tblPos = strpos($html, 'admin@fibertrust.id');
    expect($pos)->toBeInt();
    expect($tblPos)->toBeInt();
    expect($tblPos)->toBeGreaterThan($pos);
    expect(strip_tags($html))->toContain('Jika kepada PIHAK PERTAMA');
    expect(strip_tags($html))->toContain('Jika kepada PIHAK KEDUA');

    expect($html)->toContain('<strong>PIHAK PERTAMA</strong><br>');

    $tpl  = ContractTemplates::find('kontrak-colocation');
    $html = contractBuildBodyHtml($tpl, true);
    $pos  = strpos($html, 'Jangka Waktu Berlangganan');
    expect($pos)->toBeInt();
    expect(strpos($html, 'rowspan="5"', (int) $pos))->toBeInt();
    expect(substr_count($html, '<td></td>'))->toBe(0);
});

test('tabel formula satu baris ditandai atomik agar tidak berkedip di batas halaman', function () {
    foreach (['kontrak-managed-service', 'kontrak-payung'] as $key) {
        $tpl  = ContractTemplates::find($key);
        $html = contractBuildBodyHtml($tpl, true);

        expect($html)->toContain('Service Level Guarantee');
        expect($html)->toContain('Monthly Cost.');
        expect($html)->toContain('data-flow-atomic="1"');
    }
});

test('pasal komunikasi kemitraan berupa teks bernomor, bukan tabel', function () {
    // PASAL 17 harus tampil sebagai teks <ol> 1. / a. / b. / 2. seperti PDF
    // sumber — bukan tabel bergaris.
    $tpl = ContractTemplates::find('kontrak-kemitraan');
    $komunikasi = null;
    foreach ($tpl['body_content']['isi'] as $pasal) {
        if (str_contains($pasal['judul'], 'KOMUNIKASI')) { $komunikasi = $pasal; }
    }
    expect($komunikasi)->not->toBeNull();
    $tables = array_values(array_filter($komunikasi['blocks'], fn ($b) => isset($b['table'])));
    expect(count($tables))->toBe(0);
    $lists = array_values(array_filter($komunikasi['blocks'], fn ($b) => isset($b['ol'])));
    expect(count($lists))->toBe(1);
    expect($lists[0]['ol']['type'] ?? '')->toBe('1');
    expect($lists[0]['ol']['items'][0]['children']['type'] ?? '')->toBe('a');

    $html = contractBuildBodyHtml($tpl, true);
    $plain = strip_tags($html);
    $headPos = strpos($plain, 'KOMUNIKASI/PEMBERITAHUAN');
    $p1Pos = strpos($plain, 'Segala pemberitahuan yang diisyaratkan');
    $paPos = strpos($plain, 'Jika kepada PIHAK PERTAMA');
    $pbPos = strpos($plain, 'Jika kepada PIHAK KEDUA');
    $p2Pos = strpos($plain, 'Jika salah satu pihak mengganti atau mengubah alamatnya');
    foreach ([$headPos, $p1Pos, $paPos, $pbPos, $p2Pos] as $pp) { expect($pp)->toBeInt(); }
    expect($p1Pos)->toBeGreaterThan($headPos);
    expect($paPos)->toBeGreaterThan($p1Pos);
    expect($pbPos)->toBeGreaterThan($paPos);
    expect($p2Pos)->toBeGreaterThan($pbPos);
    // Detail alamat multi-baris memakai <br>, bukan sel tabel. Nama pihak
    // dibungkus <strong> oleh styleContractPartyNames, jadi cek tanpa tag.
    expect($html)->toContain('Nama : PT Bina Informatika Solusi<br');
    expect($html)->toContain('adidarmawanspd@gmail.com');
    expect(strip_tags($html))->toContain('Jika kepada PIHAK PERTAMA');
    expect(strip_tags($html))->toContain('Jika kepada PIHAK KEDUA');
});
test('render numbering selektif: blok yang memang list me-render menjadi <ol>', function () use ($keys) {
    // kemitraan: definisi + hak/kewajiban secara selektif jadi daftar bernomor
    $tpl  = ContractTemplates::find('kontrak-kemitraan');
    $html = contractBuildBodyHtml($tpl, false);
    expect($html)->toContain('list-style-type:decimal');
    expect($html)->toContain('list-style-type:lower-alpha');
    expect(strpos($html, '<ol'))->toBeInt();
    // rincian hak PIHAK PERTAMA adalah daftar (nama pihak di-bold oleh renderer)
    expect($html)->toContain('<li value=');
    expect($html)->toContain('menerima pembayaran dari ');

    // colocation: ayat pasal bernomor + sub-list A + lanjutan start
    $tpl  = ContractTemplates::find('kontrak-colocation');
    $html = contractBuildBodyHtml($tpl, false);
    expect($html)->toContain('list-style-type:upper-alpha');
    expect($html)->toContain('start="3"');  // PASAL 9 lanjutan
    expect($html)->toContain('start="8"');  // PASAL 3 lanjutan
    expect(strpos($html, '<ol'))->toBeInt();

    // lampiran: Syarat (decimal) + Fasilitas (A..D nested decimal)
    expect($html)->toContain('<li value="1" style="list-style-type:decimal;">Harga diatas belum termasuk PPN');
});

test('semua blok ol type 1 di setiap template memakai preserveNumbering', function () use ($keys) {
    foreach ($keys as $key) {
        $tpl  = ContractTemplates::find($key);
        $body = $tpl['body_content'];

        // Kumpulkan semua blok 'ol' di seluruh struktur body_content.
        $ols  = [];
        $walk = function ($node) use (&$walk, &$ols) {
            if (!is_array($node)) {
                return;
            }
            if (isset($node['ol']) && is_array($node['ol'])) {
                $ols[] = $node['ol'];
            }
            foreach ($node as $child) {
                if (is_array($child)) {
                    $walk($child);
                }
            }
        };
        $walk($body);

        foreach ($ols as $ol) {
            $type = $ol['type'] ?? null;
            if ($type === '1') {
                // Daftar ayat bernomor wajib stabil penomorannya.
                expect($ol['preserveNumbering'] ?? false)->toBeTrue();
            } elseif ($type === 'A') {
                // Penanda lampiran tetap <li> polos (lihat ContractWorkflowTest).
                expect($ol['preserveNumbering'] ?? false)->toBeFalse();
            }
        }
    }
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

        if (in_array($key, ['kontrak-soho', 'kontrak-managed-service'], true)) {
            expect($html)->toContain('font-size:' . $cs::SIZE_TITLE . '; font-weight:bold;');
        } else {
            expect($html)->not->toContain('font-size:' . $cs::SIZE_TITLE . '; font-weight:bold;');
        }


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

test('managed service merender pasal bernomor sebagai <ol> bernomor, bukan paragraf', function () {
    // Regresi bug "point-point hilang" di template Managed Service: bagian
    // Definisi, Fasilitas, Hak & Kewajiban, dst yang di dokumen sumber berupa
    // <ol> harus tetap dirender sebagai daftar bernomor — bukan blok <p> biasa.
    $cs   = \App\Data\ContractStyle::class;
    $tpl  = ContractTemplates::find('kontrak-managed-service');
    $html = contractBuildBodyHtml($tpl, true);

    // Minimal 14 <ol> (Definisi, Fasilitas, Jangka Waktu×2, Pembayaran,
    // Hak & Kewajiban, Perpindahan, Alasan×2, Tanpa Alasan×2, Ketentuan,
    // Force Majeure, Penyelesaian Sengketa, Lain-Lain).
    expect(substr_count($html, '<ol'))->toBeGreaterThanOrEqual(14);

    // Penanda numbering tunggal (decimal & lower-alpha untuk sub-list).
    expect($html)->toContain('<ol class="' . $cs::LIST_CLASS . ' ql-liststyle-decimal"');
    expect($html)->toContain('ql-liststyle-alpha');       // sub-list Pasal 9 & 10

    // Lanjutan penomoran lintas tabel/paragraf memakai atribut start.
    expect($html)->toContain('start="3"');                // Pasal 4 lanjutan
    expect($html)->toContain('start="5"');                // Pasal 9 lanjutan
    expect($html)->toContain('start="2"');                // Pasal 10 lanjutan

    // Tabel denda Pasal 4 & lampiran tetap utuh (5 tabel).
    expect(substr_count($html, '<table'))->toBe(5);
});

test('template kontrak bebas nomor kontrak hardcode', function () use ($keys) {
    $stale = [
        '196/FBT/PKS/III/2026',
        '239/FBT/J.C/VII/2026',
        '238/FBT/J.M/VI/2026',
    ];
    $bodyNumbers = [
        'kontrak-managed-service' => '335/FBC/J.MS/IV/2026',
        'kontrak-soho' => '152/FBT/J.S/IX/2025',
    ];

    foreach ($keys as $key) {
        $tpl  = ContractTemplates::find($key);
        $html = contractNormalizePasal(contractBuildBodyHtml($tpl, true));

        foreach ($stale as $nomor) {
            expect($html)->not->toContain($nomor);
        }

        if (isset($bodyNumbers[$key])) {
            expect($tpl['body_content']['bodyTitle'])->toContain('Nomor: ' . $bodyNumbers[$key]);
            expect($html)->toContain($bodyNumbers[$key]);
        }

        // Tiap template tetap memuat placeholder untuk diisi form, di cover.
        expect($tpl['body_content']['cover'])->toContain('[Nomor Perjanjian]');
    }
});

test('kontrak-soho punya kunci cover terpisah dari preamble (halaman 1 & 2 PDF sumber)', function () {
    $tpl   = ContractTemplates::find('kontrak-soho');
    $cover = (string) ($tpl['body_content']['cover'] ?? '');

    expect($cover)->not->toBeEmpty();
    // Baris judul sampul memakai placeholder, diisi dari form "Detail Kontrak".
    expect($cover)->toContain('[Nama Kontrak]');
    expect($cover)->toContain('JASA SOHO');
    expect($cover)->toContain('DENGAN');
    expect($cover)->toContain('[PIHAK KEDUA]');
    expect($cover)->toContain('Nomor: [Nomor Perjanjian]');
    // Cover bebas nomor kontrak hardcode sumber.
    expect($cover)->not->toContain('152/FBT/J.S/IX/2025');

    // Halaman 2 menampilkan judul body, lalu pembuka naratif dan daftar pihak.
    expect($tpl['body_content']['preamble'])->toStartWith('Pada hari');
    expect($tpl['body_content']['preamble'])->toContain('Pada hari ini, [Hari]');
    expect($tpl['body_content']['bodyTitle'])->toBe([
        'PERJANJIAN BERLANGGANAN JASA SOHO',
        'Nomor: 152/FBT/J.S/IX/2025',
    ]);
    expect($tpl['body_content']['openingParties']['type'])->toBe('I');
    expect($tpl['body_content']['openingParties']['items'])->toHaveCount(2);
});

test('bug kolom kedua template memakai format body dan tabel yang diminta', function () {
    $kemitraan = contractBuildBodyHtml(ContractTemplates::find('kontrak-kemitraan'), true);
    expect(preg_match('/<tr><td[^>]*>Tanggal Awal Berlangganan<\/td><td[^>]*>\x{00A0}<\/td><\/tr>/u', $kemitraan))->toBe(1);
    expect(preg_match('/<tr><td[^>]*>Tanggal Akhir Berlangganan<\/td><td[^>]*>\x{00A0}<\/td><\/tr>/u', $kemitraan))->toBe(1);

    $colocation = contractBuildBodyHtml(ContractTemplates::find('kontrak-colocation'), true);
    foreach ([
        'SPESIFIKASI',
        'Tanggal Aktivasi',
        'Pelanggan',
        'Nomor Pokok Wajib Pajak Pelanggan',
        'Alamat Kantor Pusat Pelanggan dan kontak yang dinominasikan',
        'Jangka Waktu',
        'Jasa & Produk Layanan',
        'Gedung',
    ] as $label) {
        expect($colocation)->toContain('<p><strong>' . e($label) . '</strong></p>');
    }
    foreach ([
        'Jalan Ciledug Raya No. 99, Cipulir, Kebayoran Lama, Jakarta Selatan',
        'Attention: Ahmad Ifan',
        'selama 1 (satu) Tahun, dimulai sejak ditandatangani Berita Acara Aktivasi',
        'Sebagaimana tercantum dalam lampiran PERJANJIAN ini',
    ] as $plainText) {
        expect($colocation)->toContain('<p>' . $plainText . '</p>');
        expect($colocation)->not->toContain('<strong>' . $plainText . '</strong>');
    }
    expect(preg_match(
        '/<p><strong>Gedung<\/strong><\/p>\s*<p>Gedung Wisma Bumiputera, Lantai 7 Suite #701B,<\/p>\s*<p>Jl\. Asia Afrika No\.141-149, Kb\. Pisang, Kec\. Sumur Bandung, Kota\s+Bandung, Jawa Barat 40112<\/p>/u',
        $colocation
    ))->toBe(1);

    $payung = ContractTemplates::find('kontrak-payung');
    $payment = array_values(array_filter(
        $payung['body_content']['isi'],
        fn ($pasal) => ($pasal['judul'] ?? '') === 'Pembayaran'
    ))[0];
    expect($payment['blocks'][0]['ol']['items'])->toHaveCount(10);
    expect(contractBuildBodyHtml($payung, true))->toContain('class="ct-list ql-liststyle-decimal"');

    foreach ([
        'kontrak-soho' => ['PERJANJIAN BERLANGGANAN JASA SOHO', 'Nomor: 152/FBT/J.S/IX/2025'],
        'kontrak-managed-service' => ['PERJANJIAN BERLANGGANAN JASA MANAGED SERVICE', 'Nomor: 335/FBC/J.MS/IV/2026'],
    ] as $key => $titleLines) {
        $template = ContractTemplates::find($key);
        $html = contractBuildBodyHtml($template, true);
        expect($template['body_content']['bodyTitle'])->toBe($titleLines);
        foreach ($titleLines as $titleLine) {
            expect($html)->toContain('<strong>' . $titleLine . '</strong>');
            expect(substr_count($html, '<strong>' . $titleLine . '</strong>'))->toBe(1);
        }
        expect($template['body_content']['openingParties']['type'])->toBe('I');
        expect($template['body_content']['openingParties']['items'])->toHaveCount(2);
        expect($html)->toContain('list-style-type:upper-roman');
    }
});

test('bug kolom ketiga menjaga rekening dalam tabel dan poin pembayaran yang diminta', function () {
    $colocation = ContractTemplates::find('kontrak-colocation');
    $colocationPayment = array_values(array_filter(
        $colocation['body_content']['isi'],
        fn ($pasal) => ($pasal['judul'] ?? '') === 'BIAYA, SYARAT PEMBAYARAN, PENYETORAN DAN PAJAK'
    ))[0];
    $paymentBlocks = $colocationPayment['blocks'];
    $accountTable = array_values(array_filter(
        $paymentBlocks,
        fn ($block) => !empty($block['table']['keepTogether'])
    ))[0]['table'] ?? null;
    expect($accountTable)->not->toBeNull();
    expect($accountTable['cols'])->toBe([100]);
    expect(count($accountTable['rows']))->toBe(8);
    expect($accountTable['keepRowsTogether'])->toBeTrue();
    expect($accountTable['rows'][0][0]['c'][0])->toContain('Pelanggan menerima tagihan/invoice');
    expect($paymentBlocks[array_key_last($paymentBlocks)]['table']['keepTogether'])->toBeTrue();
    $colocationHtml = contractBuildBodyHtml($colocation, true);
    expect($colocationHtml)->toContain('page-break-inside:avoid;');
    expect($colocationHtml)->toContain('contract-table-keep-rows');
    expect(substr_count($colocationHtml, 'Pelanggan menerima tagihan/invoice paling lambat sebelum tanggal 1 bulan berjalan'))->toBe(1);
    expect(substr_count($colocationHtml, 'No. Rekening: 1340001209104'))->toBe(1);
    expect(substr_count($colocationHtml, 'No. Rekening: 010701003038305'))->toBe(1);

    $payung = ContractTemplates::find('kontrak-payung');
    $payungPayment = array_values(array_filter(
        $payung['body_content']['isi'],
        fn ($pasal) => ($pasal['judul'] ?? '') === 'Pembayaran'
    ))[0];
    $payungItems = $payungPayment['blocks'][0]['ol']['items'];
    expect($payungItems)->toHaveCount(10);
    expect($payungItems[1]['pageBreakBefore'])->toBeTrue();
    expect($payungItems[3])->not->toHaveKey('pageBreakBefore');
    expect($payungItems[9]['children']['items'])->toHaveCount(2);
    $payungHtml = contractBuildBodyHtml($payung, true);
    expect($payungHtml)->toContain('page-break-before:always;');
    expect($payungHtml)->toContain('<li value="1" style="list-style-type:decimal;">');
    expect($payungHtml)->toContain('<li value="2" style="list-style-type:decimal; page-break-before:always;">');
    expect($payungHtml)->toContain('<li value="10" style="list-style-type:decimal;">');
    expect($payungHtml)->toContain('1340001209104');
    expect($payungHtml)->toContain('010701003038305');
    expect($payungHtml)->toContain('ql-liststyle-alpha');
    expect($payungHtml)->toContain('class="ct-list ql-liststyle-upperalpha"');
    expect($payungHtml)->toContain('start="2" class="ct-list ql-liststyle-upperalpha contract-keep-with-next" type="A"');
    expect($payungHtml)->toContain('start="3" class="ct-list ql-liststyle-upperalpha contract-keep-with-next" type="A"');
    expect($payungHtml)->toContain('start="4" class="ct-list ql-liststyle-upperalpha contract-keep-with-next" type="A"');
    expect($payungHtml)->toContain('<thead><tr><th');
    expect($payungHtml)->toContain('</thead><tbody>');
    expect($payungHtml)->toContain('padding:2pt 2pt;');

    $identityTable = $payung['body_content']['lampiran'][0]['blocks'][1]['table'];
    expect($identityTable['cols'])->toBe([50, 50]);
    expect($identityTable['columnGroup'])->toBeTrue();
    expect($identityTable['rows'])->toHaveCount(6);
    foreach ($identityTable['rows'] as $row) {
        expect($row)->toHaveCount(2);
    }
    foreach ($identityTable['rows'] as $row) {
        expect($row[1]['c'][0])->toBe("\u{00A0}");
    }
    expect($payungHtml)->toContain('width:50%');
    expect($payungHtml)->toContain('<colgroup><col width="50.00%"><col width="50.00%"></colgroup>');
    expect(
        substr_count($payungHtml, "\u{00A0}") + substr_count($payungHtml, '&nbsp;')
    )->toBeGreaterThanOrEqual(6);
    $serviceTable = $payung['body_content']['lampiran'][0]['blocks'][3]['table'];
    expect($serviceTable['cols'])->toBe([5, 17, 24, 11, 15, 14, 14]);
    expect($serviceTable['columnGroup'])->toBeTrue();
    expect($serviceTable['rows'])->toHaveCount(3);
    foreach ($serviceTable['rows'] as $row) {
        expect($row)->toHaveCount(7);
    }
    expect($serviceTable['rows'][2])->toHaveCount(7);
    expect(array_sum($serviceTable['cols']))->toBe(100);
    expect($payungHtml)->toContain(
        '<colgroup><col width="5.00%"><col width="17.00%"><col width="24.00%"><col width="11.00%"><col width="15.00%"><col width="14.00%"><col width="14.00%"></colgroup>'
    );
    expect($payungHtml)->toContain('border:1px solid #000;');
    expect($payungHtml)->toContain('font-weight:bold; text-align:center;');
    expect($payungHtml)->toContain('table-layout:fixed; width:100%;');
    expect($serviceTable['keepRowsTogether'])->toBeTrue();
    $detailHeadingOffset = strpos($payungHtml, 'DETAIL &amp; KOMERSIAL LAYANAN');
    expect($detailHeadingOffset)->not->toBeFalse();
    $detailHtml = substr($payungHtml, $detailHeadingOffset);
    preg_match('/<table class="contract-table-bordered contract-table-keep-rows contract-payung-attachment-grid[^"]*"[^>]*>.*?<\/table>/s', $detailHtml, $detailTableHtml);
    expect($detailHtml)->toContain('contract-keep-with-next');
    expect($detailHtml)->toContain('page-break-after:avoid;');
    expect($detailTableHtml[0] ?? '')->toContain('word-break:normal; overflow-wrap:normal;');
    expect($detailTableHtml[0] ?? '')->not->toContain('overflow-wrap:anywhere;');
    expect($detailTableHtml[0] ?? '')->not->toContain('word-break:break-word;');
    expect($detailTableHtml[0] ?? '')->toContain('<thead><tr>');
    expect($detailTableHtml[0] ?? '')->toContain('Rute Metro');
    expect($detailTableHtml[0] ?? '')->toContain('Masa Berlaku');
    expect($detailHtml)->toContain('<p class="contract-keep-with-next">SLA Availability = 99,5%</p>');
    expect($detailHtml)->toContain('class="ct-list ql-liststyle-upperalpha contract-keep-with-next"');
    expect($detailHtml)->toContain('start="4" class="ct-list ql-liststyle-upperalpha contract-keep-with-next" type="A"');
    expect($payungHtml)->toContain('contract-payung-attachment-grid');
    expect(substr_count($payungHtml, 'class="contract-table-bordered contract-payung-attachment-grid"'))->toBe(1);
    preg_match_all('/<table class="contract-table-bordered contract-table-keep-rows contract-payung-attachment-grid[^"]*"/', $payungHtml, $serviceTableTags);
    expect($serviceTableTags[0])->toHaveCount(1);
    expect(substr_count($payungHtml, 'SLA Availability = 99,5%'))->toBe(1);
    expect($payungHtml)->not->toContain('SLA Avaibility');

    $attachmentMarkers = [
        strpos($payungHtml, '<li>IDENTITAS PELANGGAN</li>'),
        strpos($payungHtml, '<li>DETAIL &amp; KOMERSIAL LAYANAN</li>'),
        strpos($payungHtml, '<li>SLA</li>'),
        strpos($payungHtml, '<li>KETENTUAN</li>'),
    ];
    expect($attachmentMarkers)->not->toContain(false);
    $sortedAttachmentMarkers = $attachmentMarkers;
    sort($sortedAttachmentMarkers);
    expect($attachmentMarkers)->toBe($sortedAttachmentMarkers);

    $pasalTujuh = array_values(array_filter(
        $payung['body_content']['isi'],
        fn ($pasal) => ($pasal['judul'] ?? '') === 'Hak dan Kewajiban Pihak Kedua'
    ))[0] ?? [];
    expect($pasalTujuh)->not->toBeEmpty();
    $pasalTujuhSource = json_encode($pasalTujuh, JSON_UNESCAPED_UNICODE);
    expect($pasalTujuhSource)->not->toContain('SLA Availability = 99,5%');
    expect($pasalTujuhSource)->not->toContain('A. KETENTUAN');
    expect($pasalTujuhSource)->not->toContain('D. KETENTUAN');
    foreach (array_slice($serviceTable['rows'][2], 1) as $cell) {
        expect($cell['c'][0])->toBe("\u{00A0}");
    }

    $soho = ContractTemplates::find('kontrak-soho');
    $sohoPayment = array_values(array_filter(
        $soho['body_content']['isi'],
        fn ($pasal) => ($pasal['judul'] ?? '') === 'Pembayaran'
    ))[0];
    $sohoItems = $sohoPayment['blocks'][0]['ol']['items'];
    expect($sohoItems)->toHaveCount(7);
    expect($sohoItems[6]['children']['items'])->toHaveCount(2);
    expect(contractBuildBodyHtml($soho, true))->toContain('1340001209104');
    expect(contractBuildBodyHtml($soho, true))->toContain('010701003038305');
    expect(contractBuildBodyHtml($soho, true))->toContain('<li value="1" style="list-style-type:decimal;">');

    $managed = ContractTemplates::find('kontrak-managed-service');
    $definitions = $managed['body_content']['isi'][0]['blocks'][0]['ol'];
    expect($definitions['items'])->toHaveCount(4);
    $managedHtml = contractBuildBodyHtml($managed, true);
    expect($managedHtml)->toContain('<ol class="ct-list ql-liststyle-decimal"');
    expect($managedHtml)->toContain('<li value="1" style="list-style-type:decimal;">“Perjanjian” adalah Perjanjian ini');
    expect($managedHtml)->toContain(
        '<p class="contract-keep-together">Apabila Layanan tidak sesuai dengan yang disepakati dalam Service Order Form, maka akan berlaku rumusan Restitusi sebagai berikut:</p>'
    );
    $managedPayment = array_values(array_filter(
        $managed['body_content']['isi'],
        fn ($pasal) => ($pasal['judul'] ?? '') === 'Pembayaran'
    ))[0];
    $managedItems = $managedPayment['blocks'][0]['ol']['items'];
    expect($managedItems)->toHaveCount(7);
    expect($managedItems[6]['children']['items'])->toHaveCount(4);
    foreach (['130-00-2010068-4', '1317-01-000039-30-8', '008-3982-397', '012-1989-247-001'] as $accountNumber) {
        expect($managedHtml)->toContain($accountNumber);
    }
});

test('setiap template kontrak punya kunci cover terpisah dari preamble', function () use ($keys) {
    foreach ($keys as $key) {
        $tpl   = ContractTemplates::find($key);
        $cover = (string) ($tpl['body_content']['cover'] ?? '');

        expect($cover)->not->toBeEmpty("template {$key} harus punya cover");

        // Blok display minimum: judul, PT BINA, DENGAN, pihak kedua, Nomor.
        expect($cover)->toContain('PT BINA INFORMATIKA SOLUSI');
        expect($cover)->toContain('DENGAN');
        expect($cover)->toContain('[PIHAK KEDUA]');
        expect($cover)->toContain('Nomor: [Nomor Perjanjian]');
        // Cover bebas nomor kontrak hardcode sumber.
        expect($cover)->not->toContain('152/FBT/J.S/IX/2025');

        // Preamble = halaman 2: langsung naratif pembuka verbatim, TANPA
        // mengulang blok display (judul / DENGAN / Nomor) milik sampul.
        $preamble = (string) $tpl['body_content']['preamble'];
        expect($preamble)->toStartWith('Pada hari');
        expect($preamble)->not->toContain('DENGAN');
        // Nomor kontrak TIDAK diulang sebagai baris display. Teks 'Nomor:'
        // di dalam paragraf naratif tetap boleh ada (mis. 'Nomor: 1888').
        expect($preamble)->not->toMatch('/^Nomor:/m');
    }
});

test('contractPreambleHtml masih merender blok judul untuk input berblok display', function () {
    $cs   = \App\Data\ContractStyle::class;
    $ctrl = new DocumentController();
    $m    = new ReflectionMethod(DocumentController::class, 'contractPreambleHtml');

    $html = $m->invoke($ctrl, "JUDUL DOKUMEN\n\nNomor: ABC/123\n\nPada hari ini, kontrak dibuat.");

    expect($html)->toContain(
        'text-align:' . $cs::ALIGN_TITLE
        . '; font-size:' . $cs::SIZE_TITLE
        . '; font-weight:bold;'
        . ' margin-top:' . $cs::TITLE_SPACE_BEFORE
        . '; margin-bottom:' . $cs::TITLE_SPACE_AFTER . ';'
    );
    expect($html)->toContain('JUDUL DOKUMEN')->toContain('Nomor: ABC/123');
});

test('buildContractCoverHtml merender baris sampul memakai token ContractStyle', function () {
    $tpl  = ContractTemplates::find('kontrak-soho');
    $ctrl = new DocumentController();
    $m    = new ReflectionMethod(DocumentController::class, 'buildContractCoverHtml');

    $html = $m->invoke($ctrl, (string) $tpl['body_content']['cover']);
    $cs   = \App\Data\ContractStyle::class;

    expect(substr_count($html, '<p '))->toBe(6);
    expect($html)->toContain('<strong>[Nama Kontrak]</strong>');
    expect($html)->toContain($cs::coverStyle('[Nama Kontrak]', true, false));
    expect($html)->toContain($cs::coverStyle('Nomor: [Nomor Perjanjian]', false, true));

    expect($m->invoke($ctrl, "  \n "))->toBe('');
});

test('judul sampul mengikuti nama kontrak dari form detail kontrak', function () use ($keys) {
    $ctrl = new DocumentController();

    $replace = new ReflectionMethod(DocumentController::class, 'applyTemplateReplacements');
    $render  = new ReflectionMethod(DocumentController::class, 'buildContractCoverHtml');

    $title  = 'Perjanjian Kerjasama Layanan Internet Fibre Optik';
    $nomor  = 'KTR/007/IX/2026';
    $customer = new Customer(['name' => 'PT Contoh Mitra', 'customer_number' => '081234567890']);

    foreach ($keys as $key) {
        $cover = (string) (ContractTemplates::find($key)['body_content']['cover'] ?? '');

        expect($cover)->toContain('[Nama Kontrak]');

        $filled = $replace->invoke($ctrl, $cover, $customer, $nomor, $title);
        expect($filled)->toContain($title);
        expect($filled)->not->toContain('[Nama Kontrak]');

        expect($filled)->toContain('PT BINA INFORMATIKA SOLUSI');
        expect($filled)->toContain('DENGAN');
        expect($filled)->toContain('PT Contoh Mitra');
        expect($filled)->toContain('Nomor: ' . $nomor);
        expect($filled)->not->toContain('[PIHAK KEDUA]');

        $html = $render->invoke($ctrl, $filled);
        expect($html)->toContain('<strong>' . $title . '</strong>');
    }
});

test('judul kontrak kosong tidak merusak isi sampul', function () use ($keys) {
    $ctrl = new DocumentController();

    $replace = new ReflectionMethod(DocumentController::class, 'applyTemplateReplacements');

    foreach ($keys as $key) {
        $cover = (string) (ContractTemplates::find($key)['body_content']['cover'] ?? '');

        $filled = $replace->invoke($ctrl, $cover, null, 'KTR/008/IX/2026', '');

        expect($filled)->toContain('[Nama Kontrak]');
        expect($filled)->toContain('PT BINA INFORMATIKA SOLUSI');
        expect($filled)->toContain('DENGAN');
        expect($filled)->toContain('Nomor: KTR/008/IX/2026');
    }
});

test('ol terbelah antar halaman disambung: lanjutan Pasal 5 tidak reset ke 1', function () {
    $ctrl = new DocumentController();

    // Simulasi hasil auto-pagination editor: <ol> 10 ayat Pasal 5 terbelah
    // jadi 1 ayat di halaman N dan 9 ayat di halaman N+1 tanpa start.
    $page1 = '<ol class="ct-list ql-liststyle-decimal" style="list-style-type:decimal; padding-left:18pt; margin:0;">'
        . '<li value="1">PIHAK KEDUA wajib melakukan pembayaran atas seluruh layanan;</li></ol>';
    $page2 = '<ol class="ct-list ql-liststyle-decimal" style="list-style-type:decimal; padding-left:18pt; margin:0;">'
        . '<li>Ayat 2</li><li>Ayat 3</li><li>Ayat 4</li><li>Ayat 5</li><li>Ayat 6</li>'
        . '<li>Ayat 7</li><li>Ayat 8</li><li>Ayat 9</li><li>Ayat 10</li></ol>';

    $fixed = contractRepairSplitLists([$page1, $page2]);

    expect($fixed[0])->toBe($page1);
    expect($fixed[1])->toContain('start="2"');
    expect(substr_count($fixed[1], '<li'))->toBe(9);

    // Idempoten: start yang sudah benar tidak diubah.
    $again = contractRepairSplitLists($fixed);
    expect($again)->toBe($fixed);

    // Daftar beda tipe antar halaman tidak dirangkai.
    $other = contractRepairSplitLists([
        '<ol class="ct-list ql-liststyle-decimal"><li>satu</li><li>dua</li></ol>',
        '<ol class="ct-list ql-liststyle-alpha"><li>a</li><li>b</li></ol>',
    ]);
    expect($other[1])->not->toContain('start=');

    // Satu halaman saja tidak diubah.
    $single = ['<ol><li>satu</li></ol>'];
    expect(contractRepairSplitLists($single))->toBe($single);
});

test('regresi: footer tidak ber-border dan sel tabel kosong di lampiran tidak hilang', function () {
    $ctrl = new DocumentController();
    $buildDefaultFooter = new ReflectionMethod(DocumentController::class, 'buildDefaultFooterHtml');
    $footerHtml = $buildDefaultFooter->invoke($ctrl);

    expect($footerHtml)->toContain('contract-table-unstyled');
    expect($footerHtml)->toContain('border:none !important;');

    // Sel tabel kosong tidak terhapus oleh stripEmptyHtmlFragments
    $strip = new ReflectionMethod(DocumentController::class, 'stripEmptyHtmlFragments');
    $sampleTable = '<table><tr><td>Label</td><td>&nbsp;</td></tr></table>';
    expect($strip->invoke($ctrl, $sampleTable))->toBe($sampleTable);

    // Kemitraan: baris tanggal awal & akhir berlangganan punya 2 kolom utuh
    $kemitraan = contractBuildBodyHtml(ContractTemplates::find('kontrak-kemitraan'), true);
    preg_match_all('/<tr><td[^>]*>Tanggal Awal Berlangganan<\/td><td[^>]*>(?:&nbsp;|\x{00A0})<\/td><\/tr>/u', $kemitraan, $m1);
    expect(count($m1[0]))->toBe(1);
    preg_match_all('/<tr><td[^>]*>Tanggal Akhir Berlangganan<\/td><td[^>]*>(?:&nbsp;|\x{00A0})<\/td><\/tr>/u', $kemitraan, $m2);
    expect(count($m2[0]))->toBe(1);

    // Payung: Detail & Komersial Layanan baris 2 punya 7 kolom utuh
    $payung = contractBuildBodyHtml(ContractTemplates::find('kontrak-payung'), true);
    expect($payung)->toContain('DETAIL &amp; KOMERSIAL LAYANAN');
    preg_match('/<tr><td[^>]*>2<\/td>(?:<td[^>]*>.*?<\/td>){6}<\/tr>/u', $payung, $m3);
    expect($m3)->not->toBeEmpty();
});

test('managed service: pasal 14 lengkap dan lampiran A muncul sebelum lampiran B dengan page break', function () {
    $tpl = ContractTemplates::find('kontrak-managed-service');
    $p14 = null;
    foreach ($tpl['body_content']['isi'] as $pasal) {
        if (($pasal['judul'] ?? '') === 'Lain-Lain') {
            $p14 = $pasal;
            break;
        }
    }
    expect($p14)->not->toBeNull();
    $items = $p14['blocks'][0]['ol']['items'];
    expect($items)->toHaveCount(5);
    expect($items[1]['pageBreakBefore'])->toBeTrue();

    // Pastikan kedua lampiran ditandai pageBreakBefore
    expect($tpl['body_content']['lampiran'][0]['judul'])->toBe('LAMPIRAN A');
    expect($tpl['body_content']['lampiran'][0]['pageBreakBefore'])->toBeTrue();
    expect($tpl['body_content']['lampiran'][1]['judul'])->toBe('LAMPIRAN B');
    expect($tpl['body_content']['lampiran'][1]['pageBreakBefore'])->toBeTrue();

    $html = contractBuildBodyHtml($tpl, true);

    // Verifikasi Pasal 14 ter-render lengkap dengan nilai 1..5
    expect($html)->toContain('<li value="1" style="list-style-type:decimal;">Setiap perubahan, penambahan');
    expect($html)->toContain('<li value="2" style="list-style-type:decimal; page-break-before:always;">Seluruh Lampiran dalam Perjanjian');
    expect($html)->toContain('<li value="3" style="list-style-type:decimal;"><strong>PARA PIHAK</strong> sepakat dan setuju');
    expect($html)->toContain('<li value="4" style="list-style-type:decimal;"><strong>Para pihak</strong> menjamin bahwa');
    expect($html)->toContain('<li value="5" style="list-style-type:decimal;">Perjanjian ini dibuat dan ditandatangani');

    // Urutan Lampiran: Lampiran A harus sebelum Lampiran B
    $posLampA = strpos($html, 'LAMPIRAN A');
    $posLampB = strpos($html, 'LAMPIRAN B');
    expect($posLampA)->not->toBeFalse();
    expect($posLampB)->not->toBeFalse();
    expect($posLampA)->toBeLessThan($posLampB);

    // Kedua heading lampiran harus memiliki page-break-before:always;
    expect($html)->toMatch('/<p style="page-break-before:always;[^"]*">\s*<strong>LAMPIRAN A<\/strong>\s*<\/p>/');
    expect($html)->toMatch('/<p style="page-break-before:always;[^"]*">\s*<strong>LAMPIRAN B<\/strong>\s*<\/p>/');
});

