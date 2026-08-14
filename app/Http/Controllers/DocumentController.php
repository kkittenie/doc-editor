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
        ]);

        $document = Document::create([
        'user_id' => Auth::id(),
        'title' => $data['title'],
        'type' => $data['type'] ?? 'surat',
        'header_data' => [
            'nomorSurat' => $data['header_data']['nomorSurat'],
            'content' => $data['header_data']['content'],
        ],
        'body_content' => [
            'pages' => [$data['body_html'] ?? ''],
        ],
        'footer_data' => [
            'content' => $data['footer_data']['content'] ?? '',
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

        if (!isset($templates[$template])) {
            abort(404, 'Template tidak ditemukan.');
        }

        return response()->json($templates[$template]);
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

        return response()->json(['url' => Storage::url($filename)]);
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
}