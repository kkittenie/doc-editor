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
            $title = $template['title'] ?? $title;
            $nomorSurat = $template['header_data']['nomorSurat'] ?? $nomorSurat;
            $headerContent = $template['header_data']['content'] ?? $headerContent;
            $footerContent = $template['footer_data']['content'] ?? $footerContent;
            $bodyHtml = $this->buildTemplateBodyHtml($template['body_content'] ?? []);
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
                'pages' => [$bodyHtml],
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
        $templates = [
            'perjanjian-kerja-sama' => [
                'title' => 'Perjanjian Kerja Sama (PKS)',
                'header_data' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => 'Gedung Menara Palma Lt. 18, Jl. H.R. Rasuna Said Blok X-2, Jakarta Selatan 12950',
                    'kopKontrak' => 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id | Web: www.ncm-media.co.id',
                    'nomorSurat' => 'PKS/001/VIII/2026',
                    'perihalSurat' => 'Perjanjian Kerja Sama',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'tujuanSurat' => 'Pada hari ini telah dibuat dan disepakati Perjanjian Kerja Sama antara Para Pihak.',
                    'menimbang' => "Bahwa Para Pihak sepakat untuk melakukan kerja sama sesuai dengan ketentuan yang berlaku.",
                    'mengingat' => "Bahwa kerja sama ini perlu dituangkan dalam suatu perjanjian tertulis.",
                    'isiPasal1' => "Pasal 1\nRUANG LINGKUP KERJA SAMA\n\nPara Pihak sepakat untuk melaksanakan kerja sama sesuai ruang lingkup yang telah disepakati.",
                    'isiPasal2' => "Pasal 2\nHAK DAN KEWAJIBAN\n\nMasing-masing Pihak mempunyai hak dan kewajiban sesuai dengan ketentuan dalam perjanjian ini.",
                ],
            ],
            'kontrak-kerja' => [
                'title' => 'Kontrak Kerja',
                'header_data' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => 'Gedung Menara Palma Lt. 18, Jakarta Selatan',
                    'kopKontrak' => 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id',
                    'nomorSurat' => 'PK/001/VIII/2026',
                    'perihalSurat' => 'Perjanjian Kerja',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'tujuanSurat' => 'Perjanjian kerja antara Perusahaan dan Pekerja.',
                    'menimbang' => "Bahwa Perusahaan membutuhkan tenaga kerja dan Pekerja bersedia melaksanakan pekerjaan sesuai ketentuan.",
                    'mengingat' => "Ketentuan peraturan perundang-undangan ketenagakerjaan yang berlaku.",
                    'isiPasal1' => "Pasal 1\nJABATAN DAN PEKERJAAN\n\nPekerja ditempatkan pada jabatan sesuai dengan kebutuhan Perusahaan.",
                    'isiPasal2' => "Pasal 2\nHAK DAN KEWAJIBAN\n\nPara Pihak wajib melaksanakan hak dan kewajibannya sebagaimana diatur dalam perjanjian ini.",
                ],
            ],
            'surat-kuasa' => [
                'title' => 'Surat Kuasa',
                'header_data' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => 'Gedung Menara Palma Lt. 18, Jakarta Selatan',
                    'kopKontrak' => 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id',
                    'nomorSurat' => 'SK/001/VIII/2026',
                    'perihalSurat' => 'Surat Kuasa',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'tujuanSurat' => 'SURAT KUASA',
                    'menimbang' => "Yang bertanda tangan di bawah ini memberikan kuasa kepada pihak yang disebutkan dalam surat ini.",
                    'mengingat' => "Untuk melaksanakan tindakan sebagaimana ruang lingkup kuasa yang diberikan.",
                    'isiPasal1' => "RUANG LINGKUP KUASA\n\nPenerima Kuasa diberikan kewenangan untuk mewakili Pemberi Kuasa dalam urusan yang telah ditentukan.",
                    'isiPasal2' => "MASA BERLAKU\n\nSurat kuasa ini berlaku sejak tanggal ditandatangani sampai dengan dinyatakan berakhir.",
                ],
            ],
            'surat-pernyataan' => [
                'title' => 'Surat Pernyataan',
                'header_data' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => 'Gedung Menara Palma Lt. 18, Jakarta Selatan',
                    'kopKontrak' => 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id',
                    'nomorSurat' => 'SP/001/VIII/2026',
                    'perihalSurat' => 'Surat Pernyataan',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'tujuanSurat' => 'SURAT PERNYATAAN',
                    'menimbang' => "Dengan ini saya menyatakan dengan sebenar-benarnya bahwa seluruh informasi yang diberikan adalah benar.",
                    'mengingat' => "Pernyataan ini dibuat dengan penuh tanggung jawab.",
                    'isiPasal1' => "ISI PERNYATAAN\n\nDengan ini saya menyatakan bahwa seluruh data dan keterangan yang diberikan kepada Perusahaan adalah benar.",
                    'isiPasal2' => "KETENTUAN\n\nApabila di kemudian hari terdapat ketidaksesuaian, saya bersedia bertanggung jawab sesuai ketentuan yang berlaku.",
                ],
            ],
        ];

        return $templates[$key] ?? null;
    }

    /**
     * Bangun HTML body dari data template.
     */
    private function buildTemplateBodyHtml(array $body): string
    {
        $parts = [];

        if (!empty($body['tujuanSurat'])) {
            $parts[] = '<p>'.nl2br(e($body['tujuanSurat'])).'</p>';
        }

        if (!empty($body['menimbang'])) {
            $parts[] = '<p><strong>Menimbang:</strong></p>';
            $parts[] = '<p>'.nl2br(e($body['menimbang'])).'</p>';
        }

        if (!empty($body['mengingat'])) {
            $parts[] = '<p><strong>Mengingat:</strong></p>';
            $parts[] = '<p>'.nl2br(e($body['mengingat'])).'</p>';
        }

        if (!empty($body['isiPasal1'])) {
            $parts[] = '<p>'.nl2br(e($body['isiPasal1'])).'</p>';
        }

        if (!empty($body['isiPasal2'])) {
            $parts[] = '<p>'.nl2br(e($body['isiPasal2'])).'</p>';
        }

        return implode("\n", $parts);
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

    public function exportPdf(Document $document)
    {
        abort_unless($document->user_id === Auth::id(), 403);

        $signaturePath = $this->resolvePublicPath($document->signature_data['signatureUrl'] ?? null);
        $pages = $document->body_content['pages'] ?? [$document->body_content['content'] ?? ''];

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
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->getRealPath());
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