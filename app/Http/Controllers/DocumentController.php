<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Service;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Data\DocumentTemplates;
use App\Data\ContractStyle;
use App\Data\ContractTemplates;

class DocumentController extends Controller
{
   

    public function index()
    {
        $documents = Document::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('pages.documents', [
            'title' => 'Dokumen Saya & Arsip',
            'documents' => $documents,
        ]);
    }

        public function create(?Customer $customer = null)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        if ($customer) {
            abort_unless($customer->user_id === Auth::id(), 403);

            $activeDocument = $customer->documents()
                ->whereIn('status', ['draft', 'on_progress', 'revisi'])
                ->orderByDesc('id')
                ->first();

            if ($activeDocument) {
                return redirect()->route('documents.edit', $activeDocument);
            }

            $customer->load(['barang', 'services']);
        }

        $totals = $customer ? $this->contractTotals($customer) : null;

        return view('pages.document-create', [
            'title' => 'Buat Dokumen Baru',
            'customers' => Customer::where('user_id', Auth::id())
                ->orderBy('name')
                ->get(),
            'selectedCustomer' => $customer,
            'contractTotals' => $totals,
        ]);
    }

    private function contractTotals(Customer $customer): array
    {
        return $this->totalsFrom($customer->barang ?? [], $customer->services ?? []);
    }

    private function totalsFrom(iterable $barang, iterable $services): array
    {
        $oneTime = 0;
        $monthly = 0;

        foreach ($barang as $item) {
            $line = (float) ($item->price ?? 0) * (int) ($item->quantity ?? 1);

            if (($item->price_type ?? 'one_time') === 'monthly') {
                $monthly += $line;
            } else {
                $oneTime += $line;
            }
        }

        foreach ($services as $item) {
            $line = (float) ($item->price ?? 0);

            if (($item->price_type ?? 'one_time') === 'monthly') {
                $monthly += $line;
            } else {
                $oneTime += $line;
            }
        }

        return ['one_time' => $oneTime, 'monthly' => $monthly];
    }

    public function edit(Document $document)
    {
        $user = Auth::user();
        $canEdit = $user->hasRole('admin')
            && $document->user_id === $user->id
            && in_array($document->status, ['draft', 'on_progress', 'revisi'], true);

        $canView = $user->hasRole('admin')
            && $document->user_id === $user->id;

        abort_unless($canView, 403);

        $this->repairLegacyContractTemplateLayout($document);

        $this->normalizeDocumentPasalOnOpen($document);

        $document->load(['customer', 'barang', 'services']);

        $customer = $document->customer;
        $contractBarang = $document->barang;
        $contractServices = $document->services;

        $contractDataFromMaster = false;

        if ($customer && $contractBarang->isEmpty() && $customer->barang->isNotEmpty()) {
            $contractBarang = $customer->barang;
            $contractDataFromMaster = true;
        }

        if ($customer && $contractServices->isEmpty() && $customer->services->isNotEmpty()) {
            $contractServices = $customer->services;
            $contractDataFromMaster = true;
        }

        return view('pages.editor', [
            'title' => ($canEdit ? 'Edit: ' : 'Lihat: ') . $document->title,
            'document' => $document,
            'readOnly' => ! $canEdit,
            'footerHtml' => $this->resolveFooterHtml($document),
            'contractCustomer' => $customer,
            'contractBarang' => $contractBarang,
            'contractServices' => $contractServices,
            'contractTotals' => $this->totalsFrom($contractBarang, $contractServices),
            'contractDataFromMaster' => $contractDataFromMaster,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

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
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $customer = isset($data['customer_id'])
            ? Customer::where('user_id', Auth::id())->find($data['customer_id'])
            : null;

        $templateKey = $request->input('template');
        $template = $templateKey ? $this->templateData($templateKey) : null;

        $title = $data['title'];
        $nomorSurat = $data['header_data']['nomorSurat'];
        $headerContent = $data['header_data']['content'];
        $footerContent = $data['footer_data']['content'] ?? '';
        $bodyHtml = $data['body_html'] ?? '';
        
        if ($template) {
            $useCenter = \App\Data\ContractTemplates::find($templateKey) !== null;
            $attachmentStartsNewPage = $useCenter;
            $bodyHtml = $template['body_html']
                ?? $this->buildTemplateBodyHtml(
                    $template['body_content'] ?? [],
                    $useCenter,
                    $attachmentStartsNewPage
                );
        }

        $bodyHtml = $this->normalizePasalNumbering([$bodyHtml])[0];
        $bodyHtml = $this->applyTemplateReplacements($bodyHtml, $customer, $nomorSurat, $title);

        $isContractTemplate = \App\Data\ContractTemplates::find((string) $templateKey) !== null;
        $coverPages = 0;

        if ($isContractTemplate) {
            $headerContent = $this->buildContractLetterheadHtml((string) $templateKey);
            $footerContent = $this->buildDefaultFooterHtml();

            $coverText = (string) ($template['body_content']['cover'] ?? '');
            $coverHtml = $this->buildContractCoverHtml(
                $coverText === ''
                    ? ''
                    : $this->applyTemplateReplacements($coverText, $customer, $nomorSurat, $title)
            );

            if ($coverHtml !== '') {
                $pages = [$coverHtml, $bodyHtml];
                $coverPages = 1;
            } else {
                $pages = [$bodyHtml];
            }
        } elseif ($templateKey && in_array($templateKey, $this->coverTemplateKeys(), true)) {
            $headerContent = $this->buildCoverHeaderHtml();
            $footerContent = $this->buildCoverFooterHtml();
            $pages = [$this->buildCoverPageHtml($title, $nomorSurat), $bodyHtml];
            $coverPages = 1; 
        } else {
            $pages = [$bodyHtml];
        }

        $pages = $this->repairSplitOrderedLists($pages);

        $document = Document::create([
            'user_id' => Auth::id(),
            'customer_id' => $customer?->id,
            'title' => $title,
            'type' => $data['type'] ?? 'surat',
            'header_data' => [
                'nomorSurat' => $nomorSurat,
                'content' => $headerContent,
            ],
            'body_content' => [
                'pages' => $pages,
                'coverPages' => $coverPages,
                'contractTemplate' => $isContractTemplate,
                'templateKey' => $isContractTemplate ? $templateKey : null,
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
            'status' => $customer ? 'on_progress' : 'draft',
        ]);

        $this->persistContractData($document, $customer, $data);

        return redirect()
            ->route('documents.edit', $document)
            ->with('success', 'Dokumen berhasil dibuat.');
    }

    private function persistContractData(Document $document, ?Customer $customer, array $data): void
    {
        if (! $customer) {
            return;
        }

        $activeDate = $customer->active_date?->toDateString();
        $activeMonths = $customer->active_months ? (int) $customer->active_months : null;

        $customer->update([
            'contract_name' => $document->title,
            'active_date'   => $activeDate,
            'active_months' => $activeMonths,
            'finish_date'   => $this->calculateFinishDate($activeDate, $activeMonths),
            'status'        => 'on_progress',
        ]);

        if (! $customer->barangCopies()->where('document_id', $document->id)->exists()) {
            foreach ($customer->barang as $item) {
                Barang::create([
                    'customer_id' => $customer->id,
                    'document_id' => $document->id,
                    'name'        => $item->name,
                    'quantity'    => $item->quantity,
                    'price'       => $item->price,
                    'price_type'  => $item->price_type ?? 'one_time',
                    'ownership'   => $item->ownership ?? 'dibeli',
                ]);
            }

            foreach ($customer->services as $item) {
                Service::create([
                    'customer_id' => $customer->id,
                    'document_id' => $document->id,
                    'name'        => $item->name,
                    'price'       => $item->price,
                    'price_type'  => $item->price_type ?? 'one_time',
                ]);
            }
        }
    }

    private function calculateFinishDate(?string $activeDate, ?int $activeMonths): ?string
    {
        if (! $activeDate || ! $activeMonths || $activeMonths < 1) {
            return null;
        }

        return \Illuminate\Support\Carbon::parse($activeDate)
            ->addMonthsNoOverflow($activeMonths)
            ->toDateString();
    }

    private function templateData(string $key): ?array
    {
        return \App\Data\ContractTemplates::all()[$key] ?? null;
    }

    private function coverTemplateKeys(): array
    {
        return [
            'perjanjian-kerja-sama',
            'kontrak-kerja',
            'kemitraan',
            'colocation',
            'managed-service',
            'soho',
        ];
    }

    private function buildDefaultHeaderHtml(): string
    {
        return '<p style="text-align:left; margin:0; padding:0;">'
            .'<img src="/images/fibertrust.png" alt="Fibertrust" '
            .'style="height:72px; width:auto; max-width:340px; display:inline-block; vertical-align:middle; margin:0; padding:0;">'
            .'</p>';
    }

    private function buildContractLetterheadHtml(string $templateKey): string
    {
        return $this->buildDefaultHeaderHtml();
    }

    private function buildContractCoverHtml(string $cover): string
    {
        $lines = preg_split('/\r?\n/', trim($cover));
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));

        if ($lines === []) {
            return '';
        }

        $last = count($lines) - 1;
        $out = [];

        $space = ContractStyle::coverSpacing($lines);

        foreach ($lines as $i => $line) {
            $style = ContractStyle::coverStyle($line, $i === 0, $i === $last, $space);
            $inner = $this->styleContractPartyNames(e($line));

            if ($i === 0) {
                $inner = '<strong>' . $inner . '</strong>';
            }

            $out[] = '<p style="' . $style . '">' . $inner . '</p>';
        }

        return implode("\n", $out);
    }

    private function repairLegacyContractTemplateLayout(Document $document): void
    {
        $content = $document->body_content ?? [];
        $pages = $content['pages'] ?? [];

        if ((int) ($content['coverPages'] ?? 0) !== 1 || count($pages) < 2) {
            return;
        }

        $legacyCover = (string) ($pages[0] ?? '');
        if (!str_contains($legacyCover, '[Ketik nama pihak pertama di sini]')
            || !str_contains($legacyCover, '[Ketik nama pihak kedua di sini]')) {
            return;
        }

        $sourcePage = (string) ($pages[1] ?? '');
        $templateKey = match (true) {
            str_contains($sourcePage, 'JASA COLOCATION') => 'kontrak-colocation',
            str_contains($sourcePage, 'JASA MANAGED SERVICE') => 'kontrak-managed-service',
            str_contains($sourcePage, 'JASA SOHO') => 'kontrak-soho',
            str_contains($sourcePage, 'KONTRAK PAYUNG') => 'kontrak-payung',
            str_contains($sourcePage, 'JUAL KEMBALI JASA LAYANAN AKSES INTERNET') => 'kontrak-kemitraan',
            default => null,
        };

        if ($templateKey === null) {
            return;
        }

        $content['pages'] = array_values(array_slice($pages, 1));
        $content['contractTemplate'] = true;
        $content['templateKey'] = $templateKey;

        $template  = ContractTemplates::find($templateKey);
        $hasCover  = trim((string) ($template['body_content']['cover'] ?? '')) !== '';
        $content['coverPages'] = ($hasCover && count($content['pages']) > 1) ? 1 : 0;

        $headerData = $document->header_data ?? [];
        $headerData['content'] = $this->buildContractLetterheadHtml($templateKey);

        $document->update([
            'body_content' => $content,
            'header_data' => $headerData,
            'footer_data' => ['content' => ''],
        ]);
        $document->refresh();
    }

    private function buildCoverHeaderHtml(): string
    {
        return $this->buildDefaultHeaderHtml();
    }

    private function buildCoverFooterHtml(): string
    {
        return $this->buildDefaultFooterHtml();
    }

    private function buildDefaultFooterHtml(): string
    {
        return implode("\n", [
            '<table class="fibertrust-footer contract-table-unstyled" border="0" style="width:100%; table-layout:fixed; border-collapse:collapse; margin:24px 0 0; page-break-inside:avoid; border:none !important;">',
            '<tbody>',
            '<tr style="border:none !important;">',
            '<td style="width:58%; vertical-align:top; text-align:left; padding:0; font-size:8.5pt; border:none !important;">',
            '<p style="margin:0; font-size:8.5pt;"><strong>PT Bina Informatika Solusi</strong></p>',
            '<p style="margin:0; font-size:8.5pt;">Jl. Prakarsa Muda No.258 Kelurahan Pekiringan, Kec. Kesambi</p>',
            '<p style="margin:0; font-size:8.5pt;">Kota Cirebon, Jawa Barat 45131</p>',
            '<p style="margin:0; font-size:8.5pt;">Tlp. 0231-247618 | email : info@fibertrust.id | www.fibertrust.id</p>',
            '</td>',
            '<td style="width:42%; vertical-align:top; text-align:left; padding:0; font-size:8.5pt; border:none !important;">',
            '<table class="fibertrust-footer-inner contract-table-unstyled" border="0" style="width:100%; table-layout:fixed; border-collapse:collapse; margin:0; padding:0; page-break-inside:avoid; border:none !important;">',
            '<tbody>',
            '<tr style="border:none !important;">',
            '<td style="width:50%; vertical-align:top; text-align:left; padding:0 6px 0 0; font-size:8pt; border:none !important;">',
            '<p style="margin:0; font-size:8pt;">Paraf PIHAK<br>PERTAMA: ______</p>',
            '</td>',
            '<td style="width:50%; vertical-align:top; text-align:left; padding:0 0 0 6px; font-size:8pt; border:none !important;">',
            '<p style="margin:0; font-size:8pt;">Paraf PIHAK<br>KEDUA: ______</p>',
            '</td>',
            '</tr>',
            '</tbody>',
            '</table>',
            '</td>',
            '</tr>',
            '</tbody>',
            '</table>',
        ]);
    }

    
    private function normalizeLegacyParafFooterHtml(string $footerHtml): string
    {
        if (!str_contains($footerHtml, 'Paraf PIHAK PERTAMA')
            || !str_contains($footerHtml, 'Paraf PIHAK KEDUA')
            || !preg_match('/display\s*:\s*inline-block/i', $footerHtml)) {
            return $footerHtml;
        }

        $replacement = '<table style="width:100%; table-layout:fixed; border-collapse:collapse; margin:0; padding:0; page-break-inside:avoid;">'
            . '<tbody><tr>'
            . '<td style="width:50%; vertical-align:top; text-align:left; padding:0 6px 0 0; font-size:8pt; border:none !important;">'
            . '<p style="margin:0; font-size:8pt;">Paraf PIHAK<br>PERTAMA: ______</p>'
            . '</td>'
            . '<td style="width:50%; vertical-align:top; text-align:left; padding:0 0 0 6px; font-size:8pt; border:none !important;">'
            . '<p style="margin:0; font-size:8pt;">Paraf PIHAK<br>KEDUA: ______</p>'
            . '</td>'
            . '</tr></tbody></table>';

        $normalized = preg_replace(
            '/<p[^>]*>\s*'
            . '<span[^>]*>\s*Paraf PIHAK PERTAMA:[^<]*<\/span>\s*'
            . '<span[^>]*>\s*Paraf PIHAK KEDUA:[^<]*<\/span>\s*'
            . '<\/p>/is',
            $replacement,
            $footerHtml
        );

        return is_string($normalized) && $normalized !== '' ? $normalized : $footerHtml;
    }

    private function resolveFooterHtml(Document $document): string
    {
        $footerHtml = (string) ($document->footer_data['content'] ?? '');

        if ((bool) ($document->body_content['contractTemplate'] ?? false)) {
            if (trim(strip_tags($footerHtml)) === '' && !preg_match('/<table/i', $footerHtml)) {
                return $this->buildDefaultFooterHtml();
            }

            return $this->normalizeLegacyParafFooterHtml($footerHtml);
        }

        $isCover = (int) ($document->body_content['coverPages'] ?? 0) > 0;
        if ($isCover && !preg_match('/<table/i', $footerHtml)) {
            $footerHtml = $this->buildCoverFooterHtml();
        }

        return $footerHtml;
    }

    private function buildCoverPageHtml(string $title, ?string $nomorSurat = null): string
    {
        $judul = trim($title) !== '' ? e($title) : '[Ketik judul dokumen di sini]';

        $nomor = trim((string) $nomorSurat) !== '' ? e($nomorSurat) : '[Ketik nomor dokumen di sini]';

        return <<<HTML
        <h1 style="text-align:center; font-size:22pt; font-weight:bold; letter-spacing:1px; margin:16px 0 40px;">{$judul}</h1>

        <p style="text-align:center; font-size:13pt; font-weight:bold; margin:0 0 90px;">[Ketik nama pihak pertama di sini]</p>
        <p style="text-align:center; font-size:11pt; margin:0 0 70px;">Dengan</p>
        <p style="text-align:center; font-size:13pt; font-weight:bold; margin:0 0 70px;">[Ketik nama pihak kedua di sini]</p>

        <p style="text-align:center; font-size:11pt; margin:8px 0 0;"><strong>Nomor:</strong> {$nomor}</p>
        HTML;
    }

    private function applyTemplateReplacements(
        string $bodyHtml,
        ?Customer $customer,
        string $nomorSurat,
        string $title = ''
    ): string {
        $now = now()->locale('id');

        $placeholders = [
            '[Nama Kontrak]'    => trim($title),
            '[Nomor Perjanjian]' => trim($nomorSurat),
            '[Hari]'             => $now->translatedFormat('l'),
            '[Tanggal]'          => $now->translatedFormat('d F Y'),
            '[Tempat]'           => 'Cirebon',
        ];

        if ($customer) {
            $placeholders['[PIHAK KEDUA]'] = trim((string) ($customer->name ?? ''));
            $placeholders['[Nomer Pelanggan]'] = trim((string) ($customer->customer_number ?? ''));
        }

        $replacements = array_filter($placeholders, fn ($v) => $v !== '');

        return str_ireplace(array_keys($replacements), array_values($replacements), $bodyHtml);
    }

    private function normalizePasalNumbering(array $pages): array
    {
        $counter = 0;

        $num = 'pasal(?:\s|\x{00A0}|&nbsp;|&#160;)*\d+';

        $pattern = '/(<(?:p|h[1-6])\b[^>]*>\s*(?:<(?!\/(?:p|h[1-6])\b)[^>]+>\s*)*)'
            . '(' . $num . ')'
            . '([^<]*)'
            . '((?:<(?!\/(?:p|h[1-6])\b)[^>]+>\s*)*)'
            . '(<\/(?:p|h[1-6])>)/iu';

        // ── Proteksi tabel: ekstrak semua <table> sebelum regex ──
        $tableStore = [];
        foreach ($pages as $key => $html) {
            $pages[$key] = $this->extractTables($html, $tableStore);
        }

        foreach ($pages as $key => $html) {
            $pages[$key] = preg_replace_callback(
                $pattern,
                function ($m) use (&$counter) {
                    $suffixRaw = $m[3];
                    $suffixVis = html_entity_decode(
                        preg_replace('/<(?!\/(?:p|h[1-6])\b)[^>]+>/', '', $suffixRaw),
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    );
                    $suffixVis = str_replace(["\xC2\xA0", '&nbsp;'], ' ', $suffixVis);

                    $empty = trim($suffixVis) === '';
                    $sepLed = preg_match('/^\sy*[\x{2013}\x{2014}.;,:-]/u', $suffixVis) === 1;
                    $lead = ltrim($suffixVis);
                    $upperTitle = preg_match('/^[A-Z]/u', $lead) === 1
                        && preg_match('/^[A-Z][A-Z0-9]*\b/u', $lead) === 1;
                    $glued = preg_match('/\d$/u', $m[2]) === 1
                        && preg_match('/^[A-Za-z]/', $suffixRaw) === 1;

                    if (!($empty || $sepLed || $upperTitle || $glued)) {
                        return $m[0]; 
                    }

                    $counter++;

                    $suffix = $suffixRaw;
                    if ($sepLed) {
                        $suffix = preg_replace('/^\s*[:\-.;,\s]*/u', '', $suffix, 1);
                    }

                    return $m[1] . 'PASAL ' . $counter . $suffix . $m[4] . $m[5];
                },
                (string) $html
            );
        }

        // ── Kembalikan tabel yang telah diekstrak ke tempatnya. ──
        foreach ($pages as $key => $html) {
            $pages[$key] = $this->restoreTables($html, $tableStore);
        }

        return $pages;
    }

    /**
     * Ekstrak semua elemen <table> dari HTML dan ganti dengan placeholder unik.
     * Tabel disimpan di $tableStore (referensi) untuk dikembalikan nanti.
     */
    private function extractTables(string $html, array &$tableStore): string
    {
        $placeholder = 0;
        $html = preg_replace_callback(
            '/<table\b[^>]*>.*?<\/table>/is',
            function ($m) use (&$tableStore, &$placeholder) {
                $key = '{{TABLE_PLACEHOLDER_' . $placeholder++ . '}}';
                $tableStore[$key] = $m[0];
                return $key;
            },
            $html
        );
        return $html;
    }

    /**
     * Kembalikan elemen <table> dari placeholder ke dalam HTML.
     */
    private function restoreTables(string $html, array $tableStore): string
    {
        foreach ($tableStore as $key => $table) {
            $html = str_replace($key, $table, $html);
        }
        return $html;
    }

    private function listSplitChainState(\DOMElement $ol): array
    {
        $start = 1;
        $raw = trim((string) $ol->getAttribute('start'));
        if ($raw !== '' && ctype_digit($raw) && (int) $raw > 0) {
            $start = (int) $raw;
        } else {
            foreach ($ol->childNodes as $c) {
                if ($c instanceof \DOMElement && strtolower($c->tagName) === 'li') {
                    $v = trim((string) $c->getAttribute('value'));
                    if ($v !== '' && ctype_digit($v) && (int) $v > 0) {
                        $start = (int) $v;
                    }
                    break;
                }
            }
        }
        $count = 0;
        foreach ($ol->childNodes as $c) {
            if ($c instanceof \DOMElement && strtolower($c->tagName) === 'li') {
                $count++;
            }
        }

        return ['start' => $start, 'count' => $count, 'key' => $this->listSplitChainKey($ol)];
    }

    private function listSplitChainKey(\DOMElement $ol): string
    {
        $cls = (string) $ol->getAttribute('class');
        foreach ($ol->childNodes as $c) {
            if ($c instanceof \DOMElement && strtolower($c->tagName) === 'li') {
                $cls .= ' ' . (string) $c->getAttribute('class');
                break;
            }
        }
        if (preg_match('/ql-liststyle-([a-z]+)/', $cls, $m) === 1) {
            return 'q:' . strtolower($m[1]);
        }
        $t = strtolower(trim((string) $ol->getAttribute('type')));
        if ($t !== '') {
            return 't:' . $t;
        }
        $style = (string) $ol->getAttribute('style');
        if (preg_match('/list-style-type\s*:\s*([^;]+)/i', $style, $m) === 1) {
            return 's:' . strtolower(trim($m[1]));
        }

        return 'unknown';
    }

    private function listSplitChainMatches(string $a, string $b): bool
    {
        return $a === $b || $a === 'unknown' || $b === 'unknown';
    }

    private function listSplitMeaningfulKids(\DOMElement $wrap): array
    {
        $out = [];
        foreach ($wrap->childNodes as $c) {
            if ($c instanceof \DOMText) {
                $t = preg_replace('/\x{200B}/u', '', (string) $c->nodeValue);
                if (trim((string) $t) !== '') {
                    $out[] = $c;
                }
                continue;
            }
            if (!($c instanceof \DOMElement)) {
                continue;
            }
            $tag = strtolower($c->tagName);
            if ($tag === 'script' || $tag === 'style') {
                continue;
            }
            $text = trim((string) preg_replace('/\x{200B}/u', '', (string) $c->textContent));
            if ($text !== '') {
                $out[] = $c;
                continue;
            }
            foreach (['img', 'table', 'ol', 'ul', 'li', 'iframe', 'video'] as $t) {
                if ($c->getElementsByTagName($t)->length > 0) {
                    $out[] = $c;
                    break;
                }
            }
        }

        return $out;
    }

    private function listSplitApplyStart(\DOMElement $ol, int $want): void
    {
        $ol->setAttribute('start', (string) $want);
        $hasValue = false;
        foreach ($ol->childNodes as $c) {
            if ($c instanceof \DOMElement
                && strtolower($c->tagName) === 'li'
                && trim((string) $c->getAttribute('value')) !== '') {
                $hasValue = true;
                break;
            }
        }
        if ($hasValue) {
            $idx = 0;
            foreach ($ol->childNodes as $c) {
                if ($c instanceof \DOMElement && strtolower($c->tagName) === 'li') {
                    $c->setAttribute('value', (string) ($want + $idx));
                    $idx++;
                }
            }
        }
    }

    private function repairSplitOrderedLists(array $pages): array
    {
        if (count($pages) < 2) {
            return $pages;
        }
        $doms = [];
        foreach ($pages as $i => $html) {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            libxml_use_internal_errors(true);
            $dom->loadHTML(
                '<?xml encoding="utf-8" ?><div data-doc-page="' . $i . '">' . (string) $html . '</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );
            libxml_clear_errors();
            $doms[$i] = $dom;
        }
        $prev = null;
        $dirty = [];
        foreach ($doms as $i => $dom) {
            $wrap = $dom->getElementsByTagName('div')->item(0);
            if (!($wrap instanceof \DOMElement)) {
                $prev = null;
                continue;
            }
            $kids = $this->listSplitMeaningfulKids($wrap);
            if (empty($kids)) {
                $prev = null;
                continue;
            }
            $first = $kids[0];
            $last = $kids[count($kids) - 1];
            $firstIsOl = ($first instanceof \DOMElement) && strtolower($first->tagName) === 'ol';
            if ($firstIsOl && $prev !== null && $this->listSplitChainMatches($prev['key'], $this->listSplitChainKey($first))) {
                $want = $prev['start'] + $prev['count'];
                $cur = $this->listSplitChainState($first);
                $curRaw = trim((string) $first->getAttribute('start'));
                if ($cur['start'] !== $want || $curRaw === '') {
                    $this->listSplitApplyStart($first, $want);
                    $dirty[$i] = true;
                }
            }
            if (($last instanceof \DOMElement) && strtolower($last->tagName) === 'ol') {
                $prev = $this->listSplitChainState($last);
            } else {
                $prev = null;
            }
        }
        if (empty($dirty)) {
            return $pages;
        }
        foreach (array_keys($dirty) as $i) {
            $dom = $doms[$i];
            $wrap = $dom->getElementsByTagName('div')->item(0);
            $html = '';
            foreach ($wrap->childNodes as $c) {
                $html .= $dom->saveHTML($c);
            }
            $pages[$i] = $html;
        }

        return $pages;
    }

    private function normalizeDocumentPasalOnOpen(Document $document): void
    {
        $content = $document->body_content ?? [];

        if (empty($content['pages']) || ! is_array($content['pages'])) {
            return;
        }

        $normalized = $this->normalizePasalNumbering($content['pages']);
        $normalized = $this->repairSplitOrderedLists($normalized);

        if ($normalized === $content['pages']) {
            return;
        }

        $content['pages'] = $normalized;
        $document->update(['body_content' => $content]);
        $document->refresh();
    }

    private function buildTemplateBodyHtml(
        array $body,
        bool $centerPasalHeadings = false,
        bool $attachmentStartsNewPage = false
    ): string
    {
        $parts = [];

        foreach ($body['bodyTitle'] ?? [] as $titleLine) {
            $parts[] = $this->contractTitleHtml((string) $titleLine);
        }

        $preamble = $body['preamble'] ?? $body['tujuanSurat'] ?? null;
        if (!empty($preamble)) {
            $parts[] = $this->contractPreambleHtml(
                (string) $preamble,
                $body['boldPreambleLabels'] ?? []
            );
        }

        if (!empty($body['openingParties']) && is_array($body['openingParties'])) {
            $list = $this->contractListHtml($body['openingParties']);
            if ($list !== '') {
                $parts[] = $list;
            }
        }

        if (!empty($body['afterOpeningParties'])) {
            $parts[] = $this->contractPara((string) $body['afterOpeningParties']);
        }

        if (!empty($body['paraPihak']) && is_array($body['paraPihak'])) {
            foreach ($body['paraPihak'] as $pihak) {
                $parts[] = $this->contractPara($pihak);
            }
        }

        if (!empty($body['definisi'])) {
            $parts[] = $this->contractHeadingHtml('DEFINISI DAN INTERPRETASI');
            $parts[] = $this->contractPara($body['definisi']);
        }

        if (!empty($body['spesifikasi'])) {
            $parts[] = $this->contractHeadingHtml('SPESIFIKASI');
            $spec = $body['spesifikasi'];
            $parts[] = is_array($spec) ? implode("\n", $spec) : $spec;
        }

        if (!empty($body['menimbang'])) {
            $parts[] = $this->contractHeadingHtml('MENIMBANG:');
            $parts[] = $this->contractRecitals($body['menimbang'], 'a');
        }

        if (!empty($body['mengingat'])) {
            $parts[] = $this->contractHeadingHtml('MENGINGAT:');
            $parts[] = $this->contractRecitals($body['mengingat'], '1');
        }

        $pasals = $body['isi'] ?? [];
        if (empty($pasals)) {
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
            $parts[] = $this->contractHeadingHtml('PASAL '.$number, true);
            if ($judul !== '') {
                $parts[] = '<p style="'.ContractStyle::displayStyle().'"><strong>'
                    .e($judul).'</strong></p>';
            }
            if (!empty($pasal['blocks']) && is_array($pasal['blocks'])) {
                $parts[] = $this->renderBlocks($pasal['blocks']);
            } else {
                $parts[] = $this->contractPara($pasal['text'] ?? '');
            }
            $number++;
        }

        if (!empty($body['tutupBlocks']) && is_array($body['tutupBlocks'])) {
            $parts[] = $this->renderBlocks($body['tutupBlocks']);
        } elseif (!empty($body['tutup'])) {
            $parts[] = $this->contractPara($body['tutup']);
        }

        if (!empty($body['lampiran'])) {
            $lampiran = $body['lampiran'];
            if (is_string($lampiran)) {
                $lampiran = [['judul' => 'LAMPIRAN', 'text' => $lampiran]];
            }
            foreach ($lampiran as $item) {
                $judul = strtoupper(trim($item['judul'] ?? ''));
                $itemPageBreak = (bool) ($item['pageBreakBefore'] ?? $attachmentStartsNewPage);
                if ($judul !== '') {
                    $parts[] = $this->contractHeadingHtml($judul, true, $itemPageBreak);
                }

                $hasBlocks = !empty($item['blocks']) && is_array($item['blocks']);
                $hasText   = !empty($item['text']);
                $hasHtml   = !empty($item['html']);
                $hasP      = !empty($item['p']);
                $hasOl     = !empty($item['ol']) && is_array($item['ol']);

                if ($hasBlocks) {
                    $parts[] = $this->renderBlocks($item['blocks']);
                }

                if (!$hasBlocks && $hasText) {
                    $parts[] = $this->contractPara($item['text']);
                }

                if ($hasHtml) {
                    $htmlBlocks = is_array($item['html']) ? $item['html'] : [$item['html']];
                    foreach ($htmlBlocks as $htmlBlock) {
                        $parts[] = $htmlBlock;
                    }
                }

                if (!$hasBlocks && !$hasText && !$hasHtml && $hasP) {
                    $parts[] = $this->contractPara((string) $item['p']);
                }

                if (!$hasBlocks && !$hasText && !$hasHtml && !$hasP && $hasOl) {
                    $list = $this->contractListHtml($item['ol']);
                    if ($list !== '') {
                        $parts[] = $list;
                    }
                }

                if (!$hasText && !$hasHtml && !$hasP && !$hasOl) {
                    $parts[] = $this->contractPara('');
                }
            }
        }

        $html = implode("\n", $parts);
        $html = $this->sanitizePayungTableLayout($html);
        return $this->stripEmptyHtmlFragments($html) ?: '<p></p>';
    }

    private function stripEmptyHtmlFragments(string $html): string
    {
        $patterns = [
            '/<p\b[^>]*>\s*(?:&nbsp;|\s|<br\s*\/?>)*\s*<\/p>/i',
            '/<li\b[^>]*>\s*(?:&nbsp;|\s|<br\s*\/?>)*\s*<\/li>/i',
        ];

        do {
            $before = $html;
            foreach ($patterns as $pattern) {
                $html = preg_replace($pattern, '', $html) ?? $html;
            }
        } while ($html !== $before);

        return trim($html);
    }

    private function sanitizePayungTableLayout(string $html): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        // Paksa libxml membaca input sebagai UTF-8. Tanpa deklarasi ini libxml
        // mengasumsikan ISO-8859-1 sehingga NBSP (C2 A0) terbelah jadi dua char
        // dan saveHTML merusaknya jadi "&Acirc;&nbsp;" (juga merusak “ ”, €, dll).
        @$doc->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        $xpath = new \DOMXPath($doc);
        $tables = $xpath->query('//table');

        if ($tables->length < 2) {
            return $html;
        }

        $identity = null;
        $commercial = null;

        foreach ($tables as $idx => $table) {
            $text = trim($table->textContent);
            if (str_contains($text, 'Nama dan Alamat Pelanggan') && str_contains($text, 'Penanggungjawab Teknisi')) {
                $identity = $idx;
            } elseif (str_contains($text, 'Masa Berlaku')) {
                $commercial = $idx;
            }
        }


        // Guard against a fully merged table: a single table carrying both the
        // identity rows and the commercial header/body. The template source
        // always renders two separate tables, so this only triggers if the
        // stored body was corrupted. Detection only: return the stored body as-is
        // (no re-sanitization) to avoid infinite recursion on unchanged HTML.
        if ($identity === null && $commercial === null) {
            foreach ($tables as $idx => $table) {
                $text = trim($table->textContent);
                $isIdentity = str_contains($text, 'Nama dan Alamat Pelanggan') && str_contains($text, 'Penanggungjawab Teknisi');
                $isCommercial = str_contains($text, 'Masa Berlaku');
                if ($isIdentity && $isCommercial && $table->getElementsByTagName('tr')->length > 2) {
                    trigger_error(
                        '[DocumentController] payung table collision in stored document: identity and commercial rows merged into a single <table>; kept stored body unchanged so the renderer can persist.',
                        E_USER_WARNING
                    );
                    return $html;
                }
            }
        }

        if ($identity === null || $commercial === null) {
            return $html;
        }

        $identityTable = $tables->item($identity);
        $commercialTable = $tables->item($commercial);

        if ($identityTable->getElementsByTagName('tr')->length !== 6) {
            return $html;
        }

        $commercialRows = $commercialTable->getElementsByTagName('tr');
        if ($commercialRows->length < 2) {
            return $html;
        }

        $headerRow = $commercialRows->item(0);
        $header = [];
        foreach ($headerRow->getElementsByTagName('th') as $th) {
            $header[] = trim($th->textContent);
        }
        $expectedHeader = ['No', 'Lokasi Layanan', 'Rute Metro', 'Bandwidth', 'Biaya Bulanan', 'Biaya Instalasi', 'Masa Berlaku'];
        if (count($header) !== count($expectedHeader)) {
            return $html;
        }
        $headerOk = true;
        foreach ($expectedHeader as $i => $label) {
            if (!str_contains($header[$i] ?? '', $label)) {
                $headerOk = false;
                break;
            }
        }
        if (!$headerOk) {
            return $html;
        }

        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($identityTable->cloneNode(true));
        $commercialTable->parentNode->replaceChild($fragment, $identityTable);

        $doc->normalize();

        $out = $doc->saveHTML() ?? $html;
        // Buang PI encoding yang ikut ter-serialize, lalu kembalikan NBSP mentah
        // (saveHTML mengeluarkannya sebagai entitas &nbsp;).
        $out = str_replace('<?xml encoding="UTF-8">', '', $out);
        return str_replace('&nbsp;', "\u{00A0}", $out);
    }

    private function styleContractPartyNames(string $text): string
    {
        if (stripos($text, 'pihak') === false) {
            return $text;
        }

        $parts = preg_split('/(<[^>]+>)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $text;
        }

        $out = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if ($part[0] === '<') {           
                $out .= $part;
                continue;
            }
            $out .= preg_replace(
                '/\b(PARA PIHAK|PIHAK PERTAMA|PIHAK KEDUA)\b/iu',
                '<strong>$1</strong>',
                $part
            ) ?? $part;
        }

        return $out;
    }

    private function contractHeadingHtml(
        string $inner,
        bool $center = true,
        bool $pageBreakBefore = false
    ): string
    {
        $style = ($pageBreakBefore ? 'page-break-before:always;' : '')
            .ContractStyle::headingStyle($center);

        return '<p style="'.$style.'">'
            .'<strong>'.e($inner).'</strong></p>';
    }

    private function contractTitleHtml(string $text): string
    {
        return '<p style="'.ContractStyle::titleStyle().'">'
            .'<strong>'.e($text).'</strong></p>';
    }

    private function contractDisplayHtml(string $text): string
    {
        return '<p style="'.ContractStyle::displayStyle().'">'
            .$this->styleContractPartyNames(e($text)).'</p>';
    }

    private function isContractDisplayLine(string $line): bool
    {
        $line = trim($line);
        if ($line === '' || mb_strlen($line) > 120) {
            return false;
        }

        if (preg_match('/^nomor\b/iu', $line) === 1) {
            return true;
        }

        return preg_match('/\p{Ll}/u', $line) !== 1;
    }

    private function contractPreambleHtml(string $preamble, array $boldLabels = []): string
    {
        $blocks = preg_split('/(\r?\n){2,}/', trim($preamble));
        $out    = [];
        $titleShown = false;
        $inHeader   = true;

        foreach ($blocks as $block) {
            $block = trim((string) $block);
            if ($block === '') {
                continue;
            }

            $lines = preg_split('/\r?\n/', $block);
            $lines = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));

            if ($inHeader && $lines !== [] && $this->isContractDisplayLine($lines[0])) {
                foreach ($lines as $line) {
                    if (!$titleShown) {
                        $out[] = $this->contractTitleHtml($line);
                        $titleShown = true;
                        continue;
                    }
                    $out[] = $this->contractDisplayHtml($line);
                }
                continue;
            }

            $inHeader = false;
            $paragraph = implode("\n", $lines);
            // Kemitraan hal.2: pemisah "d e n g a n" selalu center + bold + sedikit diperbesar.
            if (preg_match('/^d\s*e\s*n\s*g\s*a\s*n$/iu', trim($paragraph)) === 1) {
                $out[] = '<p style="text-align:center; font-size:13pt; font-weight:bold; margin-top:0; margin-bottom:4pt;">'
                    .'<strong>'.e($paragraph).'</strong></p>';
                continue;
            }
            if (in_array($paragraph, $boldLabels, true)) {
                $out[] = '<p><strong>'.e($paragraph).'</strong></p>';
                continue;
            }
            $rendered  = $this->contractPara($paragraph);
            if ($rendered !== '') {
                $out[] = $rendered;
            }
        }

        return implode("\n", $out);
    }

    private function contractPara(
        string $text,
        bool $keepTogether = false,
        bool $keepWithNext = false
    ): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $blocks = preg_split('/(\r?\n){2,}/', $text);
        $out = [];

        foreach ($blocks as $block) {
            $block = trim((string) $block);
            if ($block === '') {
                continue;
            }
            $classes = [];
            if ($keepTogether) {
                $classes[] = 'contract-keep-together';
            }
            if ($keepWithNext) {
                $classes[] = 'contract-keep-with-next';
            }
            $class = $classes !== [] ? ' class="'.implode(' ', $classes).'"' : '';
            $out[] = '<p'.$class.'>'.nl2br($this->styleContractPartyNames(e($block))).'</p>';
        }

        return count($out) ? implode("\n", $out) : '';
    }

    private function contractListHtml(
        array $list,
        int $level = 1,
        bool $keepWithNext = false
    ): string
    {
        $type  = (string) ($list['type'] ?? '1');
        $start = (int) ($list['start'] ?? 1);
        $items = $list['items'] ?? [];

        $quillClass = 'ql-liststyle-'.ContractStyle::listQuillValue($type);
        $startAttr  = $start > 1 ? ' start="'.$start.'"' : '';
        $preserveNumbering = (bool) ($list['preserveNumbering'] ?? false);
        $itemNumber = $start;

        $classes = [ContractStyle::LIST_CLASS, $quillClass];
        if ($keepWithNext) {
            $classes[] = 'contract-keep-with-next';
        }

        $html = '<ol'.$startAttr
            .' class="'.implode(' ', $classes).'"'
            .' type="'.$type.'"'
            .' style="'.ContractStyle::listStyle($type, $level).'">';
        $hasItems = false;

        foreach ($items as $item) {
            if (is_string($item)) {
                $item = trim($item);
                if ($item === '') {
                    continue;
                }
                $valueAttr = $preserveNumbering ? ' value="'.$itemNumber.'"' : '';
                $liStyle   = $preserveNumbering ? ' style="list-style-type:'.ContractStyle::listCssType($type).';"' : '';
                $html .= '<li'.$valueAttr.$liStyle.'>'.nl2br($this->styleContractPartyNames(e($item))).'</li>';
                $itemNumber++;
                $hasItems = true;
                continue;
            }

            if (is_array($item)) {
                $text = trim((string) ($item['text'] ?? ''));
                if ($text === '' && empty($item['children'])) {
                    continue;
                }
                $valueAttr = $preserveNumbering ? ' value="'.$itemNumber.'"' : '';
                $liStyles  = [];
                if ($preserveNumbering) {
                    $liStyles[] = 'list-style-type:'.ContractStyle::listCssType($type).';';
                }
                if (!empty($item['pageBreakBefore'])) {
                    $liStyles[] = 'page-break-before:always;';
                }
                $liStyle = !empty($liStyles) ? ' style="'.implode(' ', $liStyles).'"' : '';
                $html .= '<li'.$valueAttr.$liStyle.'>'.nl2br($this->styleContractPartyNames(e($text)));
                if (!empty($item['children']) && is_array($item['children'])) {
                    $nested = $this->contractListHtml($item['children'], $level + 1);
                    if (trim(strip_tags($nested)) !== '') {
                        $html .= $nested;
                    }
                }
                $html .= '</li>';
                $itemNumber++;
                $hasItems = true;
            }
        }

        $html .= '</ol>';

        return $hasItems ? $html : '';
    }

    private function renderBlocks(array $blocks): string
    {
        $out = [];

        foreach ($blocks as $block) {
            if (isset($block['p'])) {
                $para = $this->contractPara(
                    (string) $block['p'],
                    (bool) ($block['keepTogether'] ?? false),
                    (bool) ($block['keepWithNext'] ?? false)
                );
                if ($para !== '') {
                    $out[] = $para;
                }
            } elseif (isset($block['ol']) && is_array($block['ol'])) {
                $list = $this->contractListHtml(
                    $block['ol'],
                    1,
                    (bool) ($block['keepWithNext'] ?? false)
                );
                if ($list !== '') {
                    $out[] = $list;
                }
            } elseif (isset($block['table']) && is_array($block['table'])) {
                $table = $this->contractTableHtml(
                    $block['table'],
                    (bool) ($block['keepWithNext'] ?? false)
                );
                if (trim(strip_tags($table)) !== '') {
                    $out[] = $table;
                }
            }
        }

        return implode("\n", $out);
    }

    private function contractTableHtml(array $table, bool $keepWithNext = false): string
    {
        $bordered = $table['bordered'] ?? true;
        $head     = (bool) ($table['head'] ?? false);
        $rows     = $table['rows'] ?? [];

        $pending = [];
        $byRow   = [];
        $totalRows = count($rows);
        $colCount = 0;
        foreach ($rows as $row) {
            $w = 0;
            foreach ($row as $cell) {
                $w += max(1, (int) ($cell['s'] ?? 1));
            }
            $colCount = max($colCount, $w);
        }
        $colCount = max(1, $colCount);
        $colWidth = 100 / $colCount;

        $colWidths = null;
        if (isset($table['cols']) && is_array($table['cols']) && count($table['cols']) === $colCount) {
            $weights = [];
            foreach ($table['cols'] as $w) {
                $w = max(0, (float) $w);
                $weights[] = $w;
            }
            $weightSum = array_sum($weights);
            if ($weightSum > 0) {
                foreach ($weights as $i => $w) {
                    $colWidths[$i] = 100 * $w / $weightSum;
                }
            }
        }

        foreach ($rows as $ri => $row) {
            $w = 0;
            foreach ($row as $cell) {
                $w += max(1, (int) ($cell['s'] ?? 1));
            }
            if ($w > 0 && $w < $colCount) {
                $last = count($rows[$ri]) - 1;
                $rows[$ri][$last]['s'] = max(1, (int) ($rows[$ri][$last]['s'] ?? 1)) + ($colCount - $w);
            }
        }

        $tableClass = $bordered ? 'contract-table-bordered' : 'contract-table-unstyled';
        if (!empty($table['keepRowsTogether'])) {
            $tableClass .= ' contract-table-keep-rows';
        }
        if (!empty($table['payungAttachmentGrid'])) {
            $tableClass .= ' contract-payung-attachment-grid';
        }
        if ($keepWithNext) {
            $tableClass .= ' contract-keep-with-next';
        }

        foreach ($rows as $r => $row) {
            $col    = 0;
            $rowOut = [];

            foreach ($row as $cell) {
                $span = max(1, (int) ($cell['s'] ?? 1));
                $v    = $cell['v'] ?? null;

                if ($v === 'c') {
                    $col += $span;
                    continue;
                }

                while (!empty($pending[$col])) { $col++; }

                $rowspan = 1;
                if ($v === 'r') {
                    for ($rr = $r + 1; $rr < $totalRows; $rr++) {
                        $continued = false;
                        $c2 = 0;
                        foreach ($rows[$rr] as $cell2) {
                            $s2 = max(1, (int) ($cell2['s'] ?? 1));
                            if (($cell2['v'] ?? null) === 'c'
                                && $c2 < $col + $span && $c2 + $s2 > $col) {
                                $continued = true;
                                break;
                            }
                            $c2 += $s2;
                        }
                        if (!$continued) { break; }
                        $rowspan++;
                    }
                }

                $rowOut[] = [
                    'cell'    => $cell,
                    'span'    => $span,
                    'col'     => $col,
                    'rowspan' => ($rowspan > 1) ? $rowspan : null,
                ];

                if ($v === 'r' && $rowspan > 1) {
                    for ($i = 0; $i < $span; $i++) {
                        $pending[$col + $i] = $rowspan - 1;
                    }
                }

                $col += $span;
            }

            foreach ($pending as $c => $n) {
                if ($n <= 1) { unset($pending[$c]); } else { $pending[$c] = $n - 1; }
            }

            $byRow[$r] = $rowOut;
        }

        $tableStyle = ContractStyle::tableStyle($bordered);
        if (!empty($table['keepTogether'])) {
            $tableStyle .= ' page-break-inside:avoid;';
        }
        if ($keepWithNext) {
            $tableStyle .= ' page-break-after:avoid;';
        }

        $html = '<table class="'.$tableClass.'" style="'.$tableStyle.'"';

        if (count($byRow) === 1 || !empty($table['atomic'])) {
            $html .= ' data-flow-atomic="1"';
        }

        $html .= '>';

        if (!empty($table['columnGroup']) && $colWidths !== null) {
            $html .= '<colgroup>';
            foreach ($colWidths as $width) {
                $html .= '<col width="'.number_format($width, 2, '.', '').'%">';
            }
            $html .= '</colgroup>';
        }

        foreach ($rows as $r => $row) {
            if ($r === 0) {
                $html .= $head ? '<thead>' : '<tbody>';
            }
            $html .= '<tr>';

            foreach ($byRow[$r] ?? [] as $pc) {
                $cell  = $pc['cell'];
                $lines = $cell['c'] ?? [];
                $span  = $pc['span'];

                $spanAttr  = $span > 1 ? ' colspan="'.$span.'"' : '';
                $spanAttr .= !empty($pc['rowspan']) ? ' rowspan="'.$pc['rowspan'].'"' : '';

                $content = implode('<br>', array_map(fn ($l) => $this->styleContractPartyNames(e((string) $l)), $lines));
                if ($content === '') {
                    $content = '&nbsp;';
                }

                $isHeadCell = $head && $r === 0;
                $tag   = $isHeadCell ? 'th' : 'td';

                $widthPercent = $span * $colWidth;
                if ($colWidths !== null) {
                    $startCol = (int) ($pc['col'] ?? 0);
                    $w = 0.0;
                    for ($ci = $startCol; $ci < $startCol + $span; $ci++) {
                        $w += $colWidths[$ci] ?? 0;
                    }
                    $widthPercent = $w;
                }

                $style = ContractStyle::cellStyle(
                    $isHeadCell,
                    $widthPercent,
                    $bordered,
                    isset($table['cellPadding']) ? (string) $table['cellPadding'] : null,
                    (bool) ($table['normalWrap'] ?? false)
                );

                $html .= '<'.$tag.$spanAttr.' style="'.$style.'">'.$content.'</'.$tag.'>';
            }

            $html .= '</tr>';
            if ($head && $r === 0) {
                $html .= '</thead><tbody>';
            }
        }

        if ($rows !== []) {
            $html .= '</tbody>';
        }

        $html .= '</table>';

        return $html;
    }

    private function contractRecitals(string $text, string $type): string
    {
        $lines = preg_split('/\r?\n/', $text);
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));

        if (count($lines) <= 1) {
            return '<p>'.$this->styleContractPartyNames(e($lines[0] ?? '')).'</p>';
        }

        $items = '';
        foreach ($lines as $line) {
            $items .= '<li>'.$this->styleContractPartyNames(e($line)).'</li>';
        }

        return '<ol type="'.$type.'" class="'.ContractStyle::LIST_CLASS.' ql-liststyle-'
            .ContractStyle::listQuillValue($type).'" style="'
            .ContractStyle::listStyle($type).'">'.$items.'</ol>';
    }

    public function update(Request $request, Document $document)
    {
        abort_unless(
            Auth::user()->hasRole('admin') && $document->user_id === Auth::id(),
            403
        );

        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'header_data'    => ['required', 'array'],
            'body_content'   => ['nullable', 'array'],
            'footer_data'    => ['required', 'array'],
            'signature_data' => ['nullable', 'array'],
            'status'         => ['nullable', 'in:draft,on_progress,on_review,revisi,disetujui,archived'],
            'intent'         => ['nullable', 'in:draft,submit'],
        ]);

        $saveAsDraft = ($data['intent'] ?? 'submit') === 'draft';

        if (!empty($data['body_content']['pages']) && is_array($data['body_content']['pages'])) {
            $data['body_content']['pages'] = $this->normalizePasalNumbering($data['body_content']['pages']);
            $data['body_content']['pages'] = $this->repairSplitOrderedLists($data['body_content']['pages']);
        }

        if (isset($data['body_content']) && is_array($data['body_content'])) {
            $data['body_content']['coverPages'] = (int) ($document->body_content['coverPages'] ?? 0);
            $data['body_content']['contractTemplate'] = (bool) ($document->body_content['contractTemplate'] ?? false);
            $data['body_content']['templateKey'] = $document->body_content['templateKey'] ?? null;
        }

        if ((int) ($document->body_content['coverPages'] ?? 0) > 0
            && !preg_match('/<table/i', (string) ($data['footer_data']['content'] ?? ''))) {
            $data['footer_data']['content'] = $this->buildCoverFooterHtml();
        }

        unset($data['status'], $data['intent']);

        $document->update($data);

        if (! $saveAsDraft && $document->status === 'draft') {
            $document->update(['status' => 'on_progress']);
        }

        $customer = $document->customer;
        if (! $saveAsDraft && $customer && in_array($customer->status, ['draft', 'on_progress'], true)) {
            $customer->update(['status' => 'on_progress']);
        }

        return response()->json([
            'message' => $saveAsDraft ? 'Draf tersimpan.' : 'Perubahan tersimpan.',
            'status'  => $document->status,
        ]);
    }

        public function updateStatus(Request $request, Document $document)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $data = $request->validate([
            'status' => ['required', 'in:draft,on_progress,on_review,revisi,disetujui,archived'],
        ]);

        $from = $document->status;
        $to = $data['status'];

        $allowed = false;

        if ($isAdmin && $document->user_id === $user->id) {
            $allowed = in_array(
                [$from, $to],
                [
                    ['draft', 'on_review'],
                    ['on_progress', 'on_review'],
                    ['on_review', 'revisi'],
                    ['revisi', 'on_review'],
                    ['revisi', 'on_progress'],
                    ['draft', 'archived'],
                    ['on_progress', 'archived'],
                    ['disetujui', 'archived'],
                ],
                true
            );
        }

        abort_unless($allowed, 403);

        $document->update(['status' => $to]);

        if ($document->customer && in_array($to, Customer::STATUSES, true)) {
            $document->customer->update(['status' => $to]);
        }

        return response()->json([
            'message' => 'Status dokumen berhasil diperbarui.',
            'status' => $document->status,
        ]);
    }

    public function destroy(Document $document)
    {
        abort_unless(
            Auth::user()->hasRole('admin') && $document->user_id === Auth::id(),
            403
        );
        $document->delete();
        return response()->json(['message' => 'Dokumen dipindah ke trash.']);
    }

    public function deleteAll()
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

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
        $user = Auth::user();
        $canAccess = $user->hasRole('admin') && $document->user_id === $user->id;

        abort_unless($canAccess, 403);

        if ($document->hasFinalFile()
            && Storage::disk('public')->exists($document->final_file_path)) {
            return Storage::disk('public')->download(
                $document->final_file_path,
                $document->finalFileName()
            );
        }

        return $this->makeDocumentPdf($document)
            ->download('dokumen-'.$document->id.'-'.now()->format('Ymd').'.pdf');
    }

    public function approve(Request $request, Document $document)
    {
        $user = Auth::user();

        abort_unless($user->hasRole('admin') && $document->user_id === $user->id, 403);

        if (in_array($document->status, ['disetujui', 'archived'], true)) {
            return response()->json([
                'message' => 'Dokumen sudah disetujui/diarsipkan dan tidak dapat disetujui ulang.',
            ], 422);
        }

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'file.required' => 'Pilih berkas kontrak terlebih dahulu.',
            'file.mimes'    => 'Berkas kontrak harus berformat PDF.',
            'file.max'      => 'Ukuran berkas kontrak maksimal 10 MB.',
        ]);

        $file = $data['file'];
        $customer = $document->customer;

        $path = $file->store('documents/' . $document->id, 'public');

        DB::transaction(function () use ($document, $customer, $path, $file) {
            if ($document->hasFinalFile()) {
                Storage::disk('public')->delete($document->final_file_path);
            }

            $document->update([
                'status'                 => 'disetujui',
                'final_file_path'        => $path,
                'final_file_name'        => $file->getClientOriginalName(),
                'final_file_uploaded_at' => now(),
            ]);

            if ($customer && in_array('disetujui', Customer::STATUSES, true)) {
                $customer->update(['status' => 'disetujui']);
            }
        });

        return response()->json([
            'message'     => 'Dokumen disetujui & berkas kontrak berhasil di-upload.',
            'status'      => $document->status,
            'fileName'    => $document->final_file_name,
            'downloadUrl' => route('documents.export', $document),
        ]);
    }

    /**
     * @return string 
     */
    public function regenerateSofPdf(Document $document): string
    {
        $user = Auth::user();

        abort_unless(
            $user && $user->hasRole('admin') && $document->user_id === $user->id,
            403
        );

        $customer = $document->customer;

        $output = $this->makeDocumentPdf($document, [
            'sofSummaryHtml' => $this->buildSofSummaryHtml($document, $customer),
        ])->output();

        $path = $this->buildSofPdfPath($document, $customer);

        Storage::disk('public')->put($path, $output);

        return $path;
    }

    private function makeDocumentPdf(Document $document, array $extraData = [])
    {
        $this->repairLegacyContractTemplateLayout($document);
        $signaturePath = $this->resolvePublicPath($document->signature_data['signatureUrl'] ?? null);
        $pages = $this->repairSplitOrderedLists($this->normalizePasalNumbering(
            $document->body_content['pages'] ?? [$document->body_content['content'] ?? '']
        ));

        $headerHtml = $this->resolveImagePathsForPdf($document->header_data['content'] ?? '');
        $isContract = (bool) ($document->body_content['contractTemplate'] ?? false);

        if ($isContract) {
            $headerHtml = str_replace(
                'border:none; padding:0; text-align:left; font-size:',
                'border:none; padding:0 0 0 32mm; text-align:left; font-size:',
                $headerHtml
            );
        }

        $footerHtml = $this->resolveImagePathsForPdf($this->resolveFooterHtml($document));
        $footerHasTable = (bool) preg_match('/<table/i', $footerHtml);
        $isFibertrustFooter = str_contains($footerHtml, 'fibertrust-footer');
        if ($isFibertrustFooter) {
            $footerHtml = str_replace('margin:24px 0 0;', 'margin:0;', $footerHtml);
        }

        $pdf = Pdf::loadView('pdf.document', array_merge([
            'document'      => $document,
            'pages'         => $pages,
            'headerHtml'    => $headerHtml,
            'footerHtml'    => $footerHtml,
            'footerHasTable' => $footerHasTable,
            'isFibertrustFooter' => $isFibertrustFooter ?? false,
            'signaturePath' => $signaturePath,
            'coverPages'    => (int) ($document->body_content['coverPages'] ?? 0),
        ], $extraData))->setPaper('a4', 'portrait');

        if (($isFibertrustFooter ?? false) || $isContract) {
            $useFooterNum = (bool) ($isFibertrustFooter ?? false);
            $pageNumY = $useFooterNum ? ContractStyle::FOOTER_NUM_Y : ContractStyle::PAGE_NUM_Y;
            $pdf->setCallbacks([
                [
                    'event' => 'end_document',
                    'f' => function (int $pageNumber, int $pageCount, $canvas, $fontMetrics) use ($pageNumY): void {
                        $font = $fontMetrics->getFont('times')
                            ?? $fontMetrics->getFont(null);

                        // Pojok kanan bawah kertas, di bawah blok footer:
                        // X rata kanan terhadap kolom teks (lebar kertas -
                        // margin kanan - lebar teks) agar "Page | 1" s.d.
                        // "Page | 100" tetap mepet kanan & tidak goyang.
                        $text = ContractStyle::PAGE_NUM_PREFIX . $pageNumber;
                        $pageNumX = ContractStyle::FOOTER_NUM_X;
                        try {
                            $textWidth = $fontMetrics->getTextWidth(
                                $text,
                                $font,
                                ContractStyle::PAGE_NUM_SIZE
                            );
                            $pageNumX = ContractStyle::PAGE_WIDTH_PT
                                - ContractStyle::PAGE_MARGIN_RIGHT_PT
                                - $textWidth;
                        } catch (\Throwable $e) {
                        }

                        $canvas->text(
                            $pageNumX,
                            $pageNumY,
                            $text,
                            $font,
                            ContractStyle::PAGE_NUM_SIZE,
                            [0, 0, 0]
                        );
                    },
                ],
            ]);
        }

        return $pdf;
    }

    private function buildSofSummaryHtml(Document $document, ?Customer $customer): string
    {
        if (! $customer) {
            return '';
        }

        return view('pdf.sof', [
            'document' => $document,
            'customer' => $customer,
            'barang'   => $customer->barang()->orderBy('id')->get(),
            'services' => $customer->services()->orderBy('id')->get(),
        ])->render();
    }

    private function buildSofPdfPath(Document $document, ?Customer $customer): string
    {
        $label = trim((string) ($customer->contract_number ?? ''));

        if ($label === '') {
            $label = trim((string) $document->title);
        }

        return sprintf(
            'sof/%s/SOF-%s-%d.pdf',
            $customer ? 'pelanggan-'.$customer->id : 'tanpa-pelanggan',
            Str::slug($label) ?: 'dokumen',
            $document->id
        );
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

        if (in_array($template, $this->coverTemplateKeys(), true)) {
            $data['header_content'] = $this->buildCoverHeaderHtml();
            $data['footer_content'] = $this->buildCoverFooterHtml();
        }

        if (empty($data['body_html'])) {
            $useCenter = \App\Data\ContractTemplates::find($template) !== null;
            $attachmentStartsNewPage = $useCenter;
            $data['body_html'] = $this->buildTemplateBodyHtml(
                $data['body_content'] ?? [],
                $useCenter,
                $attachmentStartsNewPage
            );
        }

        if (!empty($data['body_html'])) {
            $data['body_html'] = $this->normalizePasalNumbering([$data['body_html']])[0];
            $data['body_html'] = $this->applyTemplateReplacements(
                $data['body_html'],
                null,
                (string) ($data['header_data']['nomorSurat'] ?? '')
            );
        }

        return response()->json($data);
    }

    public function devPreviewIndex()
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        $labels = [
            'kontrak-kemitraan'       => ['name' => 'Kontrak Kemitraan',       'code' => 'TPL-KM-05'],
            'kontrak-colocation'      => ['name' => 'Kontrak Colocation',      'code' => 'TPL-CO-06'],
            'kontrak-managed-service' => ['name' => 'Kontrak Managed Service', 'code' => 'TPL-MS-07'],
            'kontrak-soho'            => ['name' => 'Jasa SOHO',               'code' => 'TPL-SH-08'],
            'kontrak-payung'          => ['name' => 'Kontrak Payung Metro',    'code' => 'TPL-KP-09'],
        ];

        $cards = [];
        foreach (\App\Data\ContractTemplates::all() as $key => $tpl) {
            $cards[] = [
                'key'   => $key,
                'name'  => $labels[$key]['name'] ?? $key,
                'code'  => $labels[$key]['code'] ?? '',
                'title' => (string) ($tpl['title'] ?? $key),
            ];
        }

        return view('pages.dev-preview', [
            'title' => 'Uji Coba Template (DEV)',
            'cards' => $cards,
        ]);
    }

    public function devPreview(string $template)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        $data = $this->templateData($template);

        if (!$data) {
            abort(404, 'Template tidak ditemukan.');
        }

        $nomorSurat = (string) ($data['header_data']['nomorSurat'] ?? '');
        if (preg_match('/^([A-Z]+)\//', $nomorSurat, $m)) {
            $nomorSurat = $this->nextDocumentNumber($m[1]);
        }

        $request = Request::create('/documents', 'POST', [
            'title'       => 'UJI — ' . (string) ($data['title'] ?? $template),
            'header_data' => [
                'nomorSurat' => $nomorSurat,
                'content'    => $this->buildDefaultHeaderHtml(),
            ],
            'footer_data' => ['content' => ''],
            'body_html'   => '',
            'type'        => 'surat',
            'template'    => $template,
        ]);

        return $this->store($request);
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
        // Membuat salinan dokumen buat admin
        abort_unless(Auth::user()->hasRole('admin'), 403);

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

    public function chooseStart(?Customer $customer = null)
    {
        if ($customer) {
            abort_unless($customer->user_id === Auth::id(), 403);

            return redirect()->route('documents.create', $customer);
        }

        return redirect()->route('documents');
    }

    private function sanitizeDocxHeaderFooterStyles(string $sourcePath): string
    {
        $sanitizedPath = tempnam(sys_get_temp_dir(), 'docxclean').'.docx';
        copy($sourcePath, $sanitizedPath);

        $zip = new \ZipArchive();

        if ($zip->open($sanitizedPath) !== true) {
            return $sourcePath; 
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

            if (preg_match('#^word/footer\d*\.xml$#', $name)) {
                $xml = preg_replace('/<w:tblBorders\b[^>]*>.*?<\/w:tblBorders>/is', '<w:tblBorders><w:top w:val="none"/><w:left w:val="none"/><w:bottom w:val="none"/><w:right w:val="none"/><w:insideH w:val="none"/><w:insideV w:val="none"/></w:tblBorders>', $xml);
                $xml = preg_replace('/<w:tcBorders\b[^>]*>.*?<\/w:tcBorders>/is', '<w:tcBorders><w:top w:val="none"/><w:left w:val="none"/><w:bottom w:val="none"/><w:right w:val="none"/></w:tcBorders>', $xml);
            }

            $zip->deleteName($name);
            $zip->addFromString($name, $xml);
        }

        $zip->close();

        return $sanitizedPath;
    }

    public function importDocument(Request $request)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

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
