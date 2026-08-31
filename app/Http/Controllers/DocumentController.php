<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Data\DocumentTemplates;

class DocumentController extends Controller
{
    public function index()
    {
       $documents = Document::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('pages.documents', [
            'title' => 'Dokumen Saya & Arsip',
            'documents' => $documents,
        ]);
    }

    public function create()
    {
        return view('pages.document-create', [
            'title'    => 'Buat Dokumen Baru',
        ]);
    }

    public function edit(Document $document)
    {
        abort_unless($document->user_id === Auth::id(), 403);

        $signatureData = $document->signature_data ?? [];

        if (!empty($signatureData['signatureId'])) {
            $signature = \App\Models\Signature::where('id', $signatureData['signatureId'])
                ->where('user_id', Auth::id())
                ->first();

            if ($signature) {
                $signatureData['signatureUrl'] = Storage::url($signature->image_path);
                $document->signature_data = $signatureData;
            }
        }

        return view('pages.editor', [
            'title' => 'Edit: ' . $document->title,
            'document' => $document,
            'signatures' => $this->userSignatures(),
        ]);
    }
    
    private function userSignatures()
    {
        return \App\Models\Signature::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'url' => \Illuminate\Support\Facades\Storage::url($s->image_path),
            ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'header_data' => ['required', 'array'],
            'header_data.nomorSurat' => ['required', 'string', 'max:255'],
            'header_data.content' => ['required', 'string'],
            'footer_data' => ['nullable', 'array'],
            'footer_data.content' => ['nullable', 'string'],
            'body_html' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'template' => ['nullable', 'string'],
        ]);

        $templateKey = $request->input('template');
        $template = $templateKey ? $this->templateData($templateKey) : null;

        $title = $data['title'];
        $nomorSurat = $data['header_data']['nomorSurat'];
        $headerContent = $data['header_data']['content'];
        $footerContent = $data['footer_data']['content'] ?? '';
        $bodyHtml = $data['body_html'] ?? '';
        
        if ($template) {
            $useCenter = \App\Data\ContractTemplates::find($templateKey) !== null;
            $bodyHtml = $template['body_html']
                ?? $this->buildTemplateBodyHtml($template['body_content'] ?? [], $useCenter);
        }

        // Pastikan judul "PASAL n" selalu berurutan sesuai urutan pasalnya,
        // apa pun isi body (dari template, hasil import, atau manual).
        $bodyHtml = $this->normalizePasalNumbering([$bodyHtml])[0];

        // Halaman pertama dokumen: sampul (cover) untuk semua template kecuali
        // surat-kuasa & surat-pernyataan; tanpa template -> langsung isi saja.
        // Untuk cover: ikon masuk section header, identitas/pihak + paraf +
        // stample/materai masuk section footer (server yang menentukan,
        // nilai dari form diabaikan).
        $coverPages = 0;

        if ($templateKey && in_array($templateKey, $this->coverTemplateKeys(), true)) {
            $headerContent = $this->buildCoverHeaderHtml();
            $footerContent = $this->buildCoverFooterHtml();
            $pages = [$this->buildCoverPageHtml($title), $bodyHtml];
            $coverPages = 1; // halaman pertama = sampul: dikunci dari paginasi balik
        } else {
            $pages = [$bodyHtml];
        }

        $document = Document::create([
            'user_id' => Auth::id(),
            'title' => $title,
            'type' => $data['type'] ?? 'surat',
            'header_data' => [
                'nomorSurat' => $nomorSurat,
                'content' => $headerContent,
            ],
            'body_content' => [
                'pages' => $pages,
                'coverPages' => $coverPages,
            ],
            'footer_data' => [
                'content' => $footerContent,
            ],
            'signature_data' => [
                'selectedMaterai' => 'none',
                'signatureX' => 65,
                'signatureY' => 78,
                'signatureUrl' => null,
            ],
            'status' => 'draft',
        ]);

        return redirect()
            ->route('documents.edit', $document)
            ->with('success', 'Dokumen berhasil dibuat.');
    }

    /**
     * Data template dokumen (dipakai oleh store & createFromTemplate).
     */
    private function templateData(string $key): ?array
    {
        return \App\Data\ContractTemplates::find($key);
    }

    /**
     * Daftar template yang memakai halaman sampul (cover).
     * Dipakai bersama oleh store() dan createFromTemplate().
     */
    private function coverTemplateKeys(): array
    {
        return [
            'perjanjian-kerja-sama',
            'kontrak-kerja',
            // Template kontrak (kunci dipakai create page / ContractTemplates).
            'kontrak-kemitraan',
            'kontrak-colocation',
            'kontrak-managed-service',
            'kontrak-soho',
            'kontrak-payung',
            // Legasi (dipertahankan untuk kompatibilitas).
            'kemitraan',
            'colocation',
            'managed-service',
            'soho',
        ];
    }

    /**
     * Isi SECTION HEADER untuk dokumen ber-cover:
     * area foto/ikon pihak pertama di atas halaman sampul.
     *
     * Catatan: disusun dari paragraf sederhana agar selamat dinormalisasi
     * ulang oleh Quill di editor (format tabel tidak ada di whitelist).
     */
    private function buildCoverHeaderHtml(): string
    {
        return '<p style="text-align:center;">'
            .'[ Foto / Ikon Pihak Pertama ]'
            .'</p>';
    }

    /**
     * Isi SECTION FOOTER untuk dokumen ber-cover:
     * identitas pihak pertama di kiri, lalu paraf para pihak +
     * area stample/materai di sisi kanan.
     */
    private function buildCoverFooterHtml(): string
    {
        return implode("\n", [
            '<p><strong>Pihak Pertama</strong></p>',
            '<p>Alamat: [Ketik alamat pihak pertama di sini]</p>',
            '<p>No. Telp | Email | Web: '
                .'[Ketik telp di sini] | [Ketik email di sini] | [Ketik website di sini]</p>',
            '<p style="text-align:right;"><strong>Paraf PIHAK PERTAMA:</strong> ______________</p>',
            '<p style="text-align:right;"><strong>Paraf PIHAK KEDUA:</strong> ______________</p>',
            '<p style="text-align:right;">[ Tempel Stample/Materai di sini ]</p>',
        ]);
    }

    /**
     * Halaman sampul (cover): bagian tubuh saja — judul, para pihak,
     * dan nomor dokumen. Semua data memakai placeholder agar diisi
     * sendiri oleh pengguna.
     *
     * Ikon pihak pertama berada di section header (buildCoverHeaderHtml),
     * sedangkan identitas + paraf + stample/materai berada di section
     * footer (buildCoverFooterHtml).
     */
    private function buildCoverPageHtml(string $title): string
    {
        $judul = trim($title) !== '' ? e($title) : '[Ketik judul dokumen di sini]';

        return <<<HTML
        <h1 style="text-align:center; font-size:22pt; font-weight:bold; letter-spacing:1px; margin:120px 0 48px;">{$judul}</h1>

        <p style="text-align:center; font-size:13pt; font-weight:bold; margin:10px 0;">[Ketik nama pihak pertama di sini]</p>
        <p style="text-align:center; font-size:11pt; margin:10px 0;">Dengan</p>
        <p style="text-align:center; font-size:13pt; font-weight:bold; margin:10px 0;">[Ketik nama pihak kedua di sini]</p>

        <p style="text-align:center; font-size:11pt; margin:72px 0 0;"><strong>Nomor:</strong> [Ketik nomor dokumen di sini]</p>
        HTML;
    }

        /**
     * Pastikan judul "PASAL n" berurutan KESELURUHAN dokumen (1,2,3...),
     * melintasi batas halaman. Dipanggil dengan seluruh array halaman
     * body sehingga counter tidak ter-reset per halaman.
     *
     * Nomor pasal pada template disimpan sebagai TEKS keras (mis. "PASAL 5").
     * Jika sebuah pasal pernah dipindah/diurut-ulang (di editor, hasil import,
     * dsb.) nomor teks itu tidak ikut menyesuaikan, sehingga tampak tidak
     * berurutan. Helper ini menomori ulang hanya untuk baris yang benar-benar
     * merupakan JUDUL pasal berdiri sendiri (berawalan PASAL + angka), lalu
     * membiarkan paragraf lain (yang sekadar menyebut "pasal 4 ayat 1", dst.)
     * tetap apa adanya.
     *
     * Terima array halaman -> kembalikan array halaman yang sudah
onta     * di-renumber secara berurutan.
     */
    private function normalizePasalNumbering(array $pages): array
    {
        $counter = 0;

        // Match elemen yang isinya HANYA judul pasal (boleh dibungkus tag
        // inline spt <strong>/<em>), contoh:
        //   <p><strong>PASAL 1</strong></p>
        //   <h1 class="unnumbered" id="pasal-1">PASAL 2 — JUDUL</h1>
        // Baris/cetak HTML boleh banyak elemen dalam satu baris (bentukan
        // editor), makanya diproses per elemen, bukan per baris.
        // Inline ref spt "<p>... lihat pasal 4 ayat 1 ...</p>" atau
        // "Pasal 17 KUHPerdata mengatur..." TIDAK match, karena teks di dalam
        // elemen tidak diawali kata "pasal" (atau tanpa pemisah judul).
        $pattern = '/(<(?:p|h[1-6])\b[^>]*>)\s*('
            . '(?:<(?!\/(?:p|h[1-6])\b)[^>]+>\s*)*'
            . 'pasal\s+\d+'
            . '(?:\s*[\x{2013}\x{2014}.;,:-][^<]*)?'
            . '\s*(?:<(?!\/(?:p|h[1-6])\b)[^>]+>\s*)*'
            . ')<\/(?:p|h[1-6])>/iu';

        foreach ($pages as $key => $html) {
            $pages[$key] = preg_replace_callback(
                $pattern,
                function ($m) use (&$counter) {
                    $open  = $m[1];
                    $inner = $m[2];
                    $close = substr($m[0], strlen($open) + strlen($inner));

                    $counter++;

                    return $open . preg_replace('/pasal\s+\d+/iu', 'PASAL ' . $counter, $inner, 1) . $close;
                },
                (string) $html
            );
        }

        return $pages;
    }

    /**
     * Bangun HTML body dari data template menjadi dokumen perjanjian yang utuh.
     *
     * Mendukung struktur:
     *  - 'preamble'  : kalimat pembuka (fallback ke legacy 'tujuanSurat')
     *  - 'menimbang' : konsideran Menimbang (satu poin per baris)
     *  - 'mengingat' : konsideran Mengingat (satu poin per baris)
     *  - 'paraPihak' : array deskripsi para pihak
     *  - 'isi'       : array pasal berisi ['judul' => .., 'text' => ..]
     *                  (fallback ke legacy 'isiPasal1'..'isiPasalN')
     *  - 'tutup'     : kalimat penutup
     */
    private function buildTemplateBodyHtml(array $body, bool $centerPasalHeadings = false): string
    {
        $parts = [];

        // 1) Pembuka
        $preamble = $body['preamble'] ?? $body['tujuanSurat'] ?? null;
        if (!empty($preamble)) {
            $parts[] = $this->contractPara($preamble);
        }

        // 2) Para pihak (identitas pihak biasanya langsung setelah pembuka)
        if (!empty($body['paraPihak']) && is_array($body['paraPihak'])) {
            foreach ($body['paraPihak'] as $pihak) {
                $parts[] = $this->contractPara($pihak);
            }
        }

        // 2b) Blok definisi istilah (mis. template colocation) — dirender
        //     sebelum pasal-pasal dan TIDAK ikut penomoran PASAL.
        if (!empty($body['definisi'])) {
            $parts[] = '<p><strong>DEFINISI DAN INTERPRETASI</strong></p>';
            $parts[] = $this->contractPara($body['definisi']);
        }

        // 2c) Tabel spesifikasi (HTML mentah, mis. template colocation).
        if (!empty($body['spesifikasi'])) {
            $parts[] = '<p><strong>SPESIFIKASI</strong></p>';
            $spec = $body['spesifikasi'];
            $parts[] = is_array($spec) ? implode("\n", $spec) : $spec;
        }

        // 3) Konsideran Menimbang
        if (!empty($body['menimbang'])) {
            $parts[] = '<p><strong>MENIMBANG:</strong></p>';
            $parts[] = $this->contractRecitals($body['menimbang'], 'lower-alpha');
        }

        // 4) Konsideran Mengingat
        if (!empty($body['mengingat'])) {
            $parts[] = '<p><strong>MENGINGAT:</strong></p>';
            $parts[] = $this->contractRecitals($body['mengingat'], 'decimal');
        }

        // 5) Pasal-pasal
        $pasals = $body['isi'] ?? [];
        if (empty($pasals)) {
            // Fallback ke kunci legacy isiPasal1..N
            $pasals = [];
            $i = 1;
            while (!empty($body['isiPasal'.$i])) {
                $pasals[] = ['judul' => '', 'text' => $body['isiPasal'.$i]];
                $i++;
            }
        }

        $number = 1;
        foreach ($pasals as $pasal) {
            $judul = strtoupper(trim($pasal['judul'] ?? ''));
            if ($centerPasalHeadings) {
                $parts[] = '<p style="text-align:center;"><strong>PASAL '.$number.'</strong></p>';
                if ($judul !== '') {
                    $parts[] = '<p style="text-align:center;"><strong>'.$judul.'</strong></p>';
                }
            } else {
                $heading = 'PASAL '.$number.($judul !== '' ? ' — '.$judul : '');
                $parts[] = '<p><strong>'.$heading.'</strong></p>';
            }
            if (!empty($pasal['blocks']) && is_array($pasal['blocks'])) {
                $parts[] = $this->renderBlocks($pasal['blocks']);
            } else {
                $parts[] = $this->contractPara($pasal['text'] ?? '');
            }
            $number++;
        }

        // 6) Penutup
        if (!empty($body['tutupBlocks']) && is_array($body['tutupBlocks'])) {
            $parts[] = $this->renderBlocks($body['tutupBlocks']);
        } elseif (!empty($body['tutup'])) {
            $parts[] = $this->contractPara($body['tutup']);
        }

        // 7) Lampiran (lampiran dokumen setelah pasal-pasal)
        if (!empty($body['lampiran'])) {
            $lampiran = $body['lampiran'];
            if (is_string($lampiran)) {
                $lampiran = [['judul' => 'LAMPIRAN', 'text' => $lampiran]];
            }
            foreach ($lampiran as $item) {
                $judul = strtoupper(trim($item['judul'] ?? ''));
                if ($judul !== '') {
                    $parts[] = '<p><strong>'.$judul.'</strong></p>';
                }

                // Blok terstruktur (paragraf + tabel asli dari .docx)
                if (!empty($item['blocks']) && is_array($item['blocks'])) {
                    $parts[] = $this->renderBlocks($item['blocks']);
                }

                // Blok teks biasa (diparagraph-kan otomatis).
                if (empty($item['blocks']) && !empty($item['text'])) {
                    $parts[] = $this->contractPara($item['text']);
                }

                // Blok HTML mentah (mis. tabel pada lampiran template
                // kontrak-kemitraan) — dirender apa adanya tanpa escape.
                if (!empty($item['html'])) {
                    $htmlBlocks = is_array($item['html']) ? $item['html'] : [$item['html']];
                    foreach ($htmlBlocks as $htmlBlock) {
                        $parts[] = $htmlBlock;
                    }
                }

                // Lampiran kosong (judul saja) — kompatibel dengan perilaku lama.
                if (empty($item['text']) && empty($item['html'])) {
                    $parts[] = $this->contractPara('');
                }
            }
        }

        return implode("\n", $parts);
    }

    /**
     * Ubah teks yang mungkin berisi beberapa paragraf (dipisah baris kosong)
     * menjadi satu atau lebih tag <p> yang sudah di-escape dan dijaga barisnya.
     */
    private function contractPara(string $text): string
    {
        $blocks = preg_split('/(\r?\n){2,}/', $text);
        $out = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }
            $out[] = '<p>'.nl2br(e($block)).'</p>';
        }

        return count($out) ? implode("\n", $out) : '<p></p>';
    }

    /**
     * Render blok terstruktur body template kontrak: urutan paragraf & tabel
     * asli dari dokumen .docx, dipertahankan pada posisi aslinya.
     */
    private function renderBlocks(array $blocks): string
    {
        $out = [];

        foreach ($blocks as $block) {
            if (isset($block['p'])) {
                $out[] = $this->contractPara((string) $block['p']);
            } elseif (isset($block['table']) && is_array($block['table'])) {
                $out[] = $this->contractTableHtml($block['table']);
            }
        }

        return implode("\n", $out);
    }

    /**
     * Render satu tabel kontrak menjadi HTML. Tabel data memakai border
     * hitam 1px (seperti dokumen asli), tabel tanda tangan tanpa border.
     * Setiap sel mempertahankan baris-baris paragrafnya (<br>).
     */
    private function contractTableHtml(array $table): string
    {
        $bordered = $table['bordered'] ?? true;
        $head     = (bool) ($table['head'] ?? false);
        $rows     = $table['rows'] ?? [];

        $borderStyle  = $bordered ? 'border:1px solid #000;' : 'border:none;';
        $cellBaseStyle = $borderStyle.' padding:4px 6px; vertical-align:top;';

        $html = '<table style="border-collapse:collapse; width:100%; margin:0.5rem 0;">';

        foreach ($rows as $ri => $row) {
            $html .= '<tr>';

            foreach ($row as $cell) {
                $lines   = $cell['c'] ?? [];
                $span    = max(1, (int) ($cell['s'] ?? 1));
                $spanAttr = $span > 1 ? ' colspan="'.$span.'"' : '';

                $content = implode('<br>', array_map(static fn ($l) => e((string) $l), $lines));

                $isHeadCell = $head && $ri === 0;
                $tag   = $isHeadCell ? 'th' : 'td';
                $style = $cellBaseStyle.($isHeadCell ? ' font-weight:bold; text-align:center;' : '');

                $html .= '<'.$tag.$spanAttr.' style="'.$style.'">'.$content.'</'.$tag.'>';
            }

            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * Ubah daftar poin (satu poin per baris) menjadi daftar <ol>.
     * $listStyle: 'decimal' atau 'lower-alpha'.
     */
    private function contractRecitals(string $text, string $listStyle): string
    {
        $lines = preg_split('/\r?\n/', $text);
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));

        // Hanya satu poin → cukup satu paragraf.
        if (count($lines) <= 1) {
            return '<p>'.e($lines[0] ?? '').'</p>';
        }

        $items = '';
        foreach ($lines as $line) {
            $items .= '<li>'.e($line).'</li>';
        }

        return '<ol style="list-style-type:'.$listStyle.'; padding-left:2rem; margin:0 0 0.75rem;">'.$items.'</ol>';
    }

    public function update(Request $request, Document $document)
    {
        abort_unless($document->user_id === Auth::id(), 403);

        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'header_data'    => ['required', 'array'],
            'body_content'   => ['nullable', 'array'],
            'footer_data'    => ['required', 'array'],
            'signature_data' => ['nullable', 'array'],
            'status'         => ['nullable', 'in:draft,pending,signed,archieved'],
        ]);

        // Pastikan judul "PASAL n" berurutan KESELURUHAN dokumen (1,2,3...)
        // melintasi batas halaman, bukan per halaman.
        if (!empty($data['body_content']['pages']) && is_array($data['body_content']['pages'])) {
            $data['body_content']['pages'] = $this->normalizePasalNumbering($data['body_content']['pages']);
        }

        // Pertahankan penanda halaman sampul (cover): payload simpanan editor
        // hanya membawa 'pages', tanpa flag ini sampul kehilangan kunci
        // paginasi balik setiap kali dokumen disimpan.
        if (isset($data['body_content']) && is_array($data['body_content'])) {
            $data['body_content']['coverPages'] = (int) ($document->body_content['coverPages'] ?? 0);
        }

        $document->update($data);

        return response()->json(['message' => 'Perubahan tersimpan.']);
    }

    public function updateStatus(Request $request, Document $document)
    {
        abort_unless($document->user_id === Auth::id(), 403);

        $data = $request->validate([
            'status' => [
                'required',
                'in:draft,pending,signed,archived'
            ],
        ]);

        $document->update([
            'status' => $data['status'],
        ]);

        return response()->json([
            'message' => 'Status dokumen berhasil diperbarui.',
            'status' => $document->status,
        ]);
    }

    public function destroy(Document $document)
    {
        abort_unless($document->user_id === Auth::id(), 403);
        $document->delete();
        return response()->json(['message' => 'Dokumen dipindah ke trash.']);
    }

    public function deleteAll()
    {
        $query = Document::where('user_id', Auth::id());
        $count = (clone $query)->count();

        if ($count === 0) {
            return response()->json([
                'message' => 'Tidak ada dokumen untuk dihapus.',
            ], 422);
        }

        $query->delete();

        return response()->json([
            'message' => "{$count} dokumen berhasil dipindahkan ke trash.",
            'deleted' => $count,
        ]);
    }

    public function exportPdf(Document $document)
    {
        abort_unless($document->user_id === Auth::id(), 403);

        $signaturePath = $this->resolvePublicPath($document->signature_data['signatureUrl'] ?? null);
        // Renumber di saat ekspor juga, supaya dokumen lama (tersimpan sebelum
        // ada normalisasi) tetap dicetak dengan PASAL 1, 2, 3, ... yang urut.
        $pages = $this->normalizePasalNumbering(
            $document->body_content['pages'] ?? [$document->body_content['content'] ?? '']
        );

        $headerHtml = $this->resolveImagePathsForPdf($document->header_data['content'] ?? '');
        $footerHtml = $this->resolveImagePathsForPdf($document->footer_data['content'] ?? '');

        $pdf = Pdf::loadView('pdf.document', [
            'document'      => $document,
            'pages'         => $pages,
            'headerHtml'    => $headerHtml,
            'footerHtml'    => $footerHtml,
            'signaturePath' => $signaturePath,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('dokumen-'.$document->id.'-'.now()->format('Ymd').'.pdf');
    }

    public function createFromTemplate(string $template)
    {
        $data = $this->templateData($template);

        if (!$data) {
            abort(404, 'Template tidak ditemukan.');
        }

        if (preg_match('/^([A-Z]+)\//', $data['header_data']['nomorSurat'], $m)) {
            $prefix = $m[1];
            $data['header_data']['nomorSurat'] = $this->nextDocumentNumber($prefix);
        }

        // Template ber-cover: kirim isi section header (ikon) & footer
        // (pihak/paraf/stample) supaya form create mengisinya otomatis
        // sama seperti yang akan disimpan oleh store().
        if (in_array($template, $this->coverTemplateKeys(), true)) {
            $data['header_content'] = $this->buildCoverHeaderHtml();
            $data['footer_content'] = $this->buildCoverFooterHtml();
        }

        // Template kontrak (ContractTemplates) hanya membawa body_content;
        // bangun body_html untuk pratinjau di halaman create.
        if (empty($data['body_html'])) {
            $useCenter = \App\Data\ContractTemplates::find($template) !== null;
            $data['body_html'] = $this->buildTemplateBodyHtml($data['body_content'] ?? [], $useCenter);
        }

        // Pastikan judul "PASAL n" berurutan pada pratinjau template juga.
        if (!empty($data['body_html'])) {
            $data['body_html'] = $this->normalizePasalNumbering([$data['body_html']])[0];
        }

        return response()->json($data);
    }

    public function uploadLogo(Request $request)
    {
        $data = $request->validate([
            'image' => ['required', 'string'],
        ]);

        [$meta, $base64] = explode(',', $data['image'], 2);
        $imageData = base64_decode($base64);

        $filename = 'logos/'.Auth::id(). '_' .Str::random(10). '.png';
        Storage::disk('public')->put($filename, $imageData);

        return response()->json(['url' => asset('storage/' .$filename)]);
    }

    public function uploadImage(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'max:5120'],
        ]);

        $path = $data['file']->store('images', 'public');

        return response()->json(['url' => Storage::url($path)]);
    }

    private function resolvePublicPath(?string $url): ?string 
    {
        if (!$url) return null;
        $relative = ltrim(parse_url($url, PHP_URL_PATH), '/');
        $fullPath = public_path($relative);
        return file_exists($fullPath) ? $fullPath : null;
    }

    private function resolveImagePathsForPdf(string $html): string
    {
        return preg_replace_callback('/<img[^>]+src="([^"]+)"/i', function ($matches) {
            $path = $this->resolvePublicPath($matches[1]);
            return $path ? str_replace($matches[1], $path, $matches[0]) : $matches[0];
        }, $html);
    }

    public function saveAsNew(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type'  => ['nullable', 'string'],
            'header_data' => ['required', 'array'],
            'body_content' => ['nullable', 'array'],
            'footer_data' => ['nullable', 'array'],
            'signature' => ['nullable', 'array'],
        ]);

        $newDocument = Document::create([
            'user_id'   => Auth::id(),
            'title'     => $data['title'],
            'type'      => $data['type'] ?? 'surat',
            'header_data'   => $data['header_data'],
            'body_content'  => $data['body_content'] ?? [],
            'footer_data'   => $data['footer_data'] ?? [],
            'signature_data' => $data['signature_data'] ?? null,
            'status'    => 'draft',
        ]);

        return response()->json(['id' => $newDocument->id]);
    }

    public function chooseStart()
    {
        return view('pages.editor-start', [
            'title' => 'Mulai dokumen baru', 
        ]);
    }

    private function sanitizeDocxHeaderFooterStyles(string $sourcePath): string
    {
        $sanitizedPath = tempnam(sys_get_temp_dir(), 'docxclean').'.docx';
        copy($sourcePath, $sanitizedPath);

        $zip = new \ZipArchive();

        if ($zip->open($sanitizedPath) !== true) {
            return $sourcePath; // gagal buka sebagai zip, coba pakai file asli aja
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if (!preg_match('#^word/(header|footer)\d*\.xml$#', $name)) {
                continue;
            }

            $xml = $zip->getFromName($name);

            if ($xml === false) {
                continue;
            }

            $xml = preg_replace(
                '/<w:pStyle w:val="(Title|Heading[1-6])"\s*\/>/i',
                '<w:pStyle w:val="Normal"/>',
                $xml
            );

            $zip->deleteName($name);
            $zip->addFromString($name, $xml);
        }

        $zip->close();

        return $sanitizedPath;
    }

    public function importDocument(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        if ($extension === 'pdf') {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($file->getRealPath());
            $text = $pdf->getText();
            $text = str_replace(["\r\n", "\r"], "\n", $text);
            $blocks = preg_split('/\n\s*\n/', trim($text));
            if (count($blocks) <= 1) {
                $blocks = preg_split('/\n/', trim($text));
            }

            $paragraphs = array_filter(array_map('trim', $blocks));
            $bodyHtml = collect($paragraphs)
                ->map(fn($p) => '<p>'.nl2br(e($p)).'</p>')
                ->implode('');
        } else {
            $sanitizedPath = $this->sanitizeDocxHeaderFooterStyles($file->getRealPath());

            try {
                $phpWord = \PhpOffice\PhpWord\IOFactory::load($sanitizedPath);
            } finally {
                if ($sanitizedPath !== $file->getRealPath()) {
                    @unlink($sanitizedPath);
                }
            }

            $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');

            $tempPath = tempnam(sys_get_temp_dir(), 'docximport').'.html';
            $htmlWriter->save($tempPath);
            $fullHtml = file_get_contents($tempPath);
            @unlink($tempPath);

            $bodyHtml = preg_match('/<body[^>]*>(.*)<\/body>/is', $fullHtml, $m) ? $m[1] : $fullHtml;
        }

        $document = Document::create([
            'user_id' => Auth::id(),
            'title' => $originalName,
            'type' => 'surat',
            'header_data' => ['content' => ''],
            'body_content' => ['pages' => [$bodyHtml]],
            'footer_data' => ['content' => ''],
            'signature_data' => [
                'selectedMaterai' => 'none',
                'signatureX' => 65,
                'signatureY' => 78,
                'signatureUrl' => null,
            ],
            'status' => 'draft',
        ]);

        return redirect()->route('documents.edit', $document)->with('success', 'Dokumen berhasil diimpor. Cek formatnya sebelum dipakai!');
    }

    private function nextDocumentNumber(string $prefix): string
    {
        $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $month = $romanMonths[now()->month - 1];
        $year = now()->year;

        $maxNumber = Document::where('user_id', Auth::id())
            ->get()
            ->map(fn($doc) => $doc->header_data['nomorSurat'] ?? '')
            ->filter(fn($nomor) => preg_match(
                '/^'.preg_quote($prefix, '/'). '\/(\d+)\/'.$month.'\/'.$year.'$/',
                $nomor
            ))
            ->map(function ($nomor) {
                preg_match('/\/(\d+)\//', $nomor, $m);
                return (int) $m[1];
            })
            ->max();

            $next = ($maxNumber ?? 0) + 1;
            return $prefix.'/'.str_pad($next, 3, '0', STR_PAD_LEFT).'/'.$month.'/'.$year;
    }
}