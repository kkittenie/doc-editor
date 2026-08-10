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
            ->paginate(12);

        return view('pages.documents', [
            'title' => 'Dokumen Saya & Arsip',
            'documents' => $documents,
        ]);
    }

    public function create()
    {
        return view('pages.editor', [
            'title'    => 'Studio Composer Dokumen',
            'document' => null,
            'signatures' => $this->userSignatures(),
        ]);
    }

    public function edit(Document $document)
    {
        abort_unless($document->user_id === Auth::id(), 403);

        return view('pages.editor', [
            'title'    => 'Edit: '.$document->title,
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
            'title'          => ['required', 'string', 'max:255'],
            'type'           => ['nullable', 'string'],
            'header_data'    => ['required', 'array'],
            'body_content'   => ['nullable', 'array'],
            'footer_data'    => ['required', 'array'],
            'signature_data' => ['nullable', 'array'],
            'status'         => ['nullable', 'in:draft,final,archived'],
        ]);

        $document = Document::create([
            'user_id'        => Auth::id(),
            'title'          => $data['title'],
            'type'           => $data['type'] ?? 'umum',
            'header_data'    => $data['header_data'],
            'body_content'   => $data['body_content'] ?? [],
            'footer_data'    => $data['footer_data'],
            'signature_data' => $data['signature_data'] ?? null,
            'status'         => $data['status'] ?? 'draft',
        ]);

        return response()->json([
            'message' => 'Dokumen berhasil disimpan.',
            'id'      => $document->id,
        ]);
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
            'status'         => ['nullable', 'in:draft,final,archived'],
        ]);

        $document->update($data);

        return response()->json(['message' => 'Perubahan tersimpan.']);
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
    $logoPath = $this->resolvePublicPath($document->header_data['logoUrl'] ?? null);

    $pdf = Pdf::loadView('pdf.document', [
        'document'      => $document,
        'signaturePath' => $signaturePath,
        'logoPath'      => $logoPath,
    ])->setPaper('a4', 'portrait');

    return $pdf->download('dokumen-'.$document->id.'-'.now()->format('Ymd').'.pdf');
    }

    public function createFromTemplate(string $template)
    {
        $templates = [

            'perjanjian-kerja-sama' => [
                'title' => 'Perjanjian Kerja Sama (PKS)',
                'type' => 'pks',

                'header_data' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => 'Gedung Menara Palma Lt. 18, Jl. H.R. Rasuna Said Blok X-2, Jakarta Selatan 12950',
                    'kopKontrak' => 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id | Web: www.ncm-media.co.id',
                    'nomorSurat' => 'PKS/001/VIII/2026',
                    'perihalSurat' => 'Perjanjian Kerja Sama',
                    'tanggalSurat' => '10 Agustus 2026',
                    'sifatSurat' => 'Penting',
                ],

                'body_content' => [
                    'tujuanSurat' => 'Pada hari ini telah dibuat dan disepakati Perjanjian Kerja Sama antara Para Pihak.',
                    'menimbang' => 'Bahwa Para Pihak sepakat untuk melakukan kerja sama sesuai dengan ketentuan yang berlaku.',
                    'mengingat' => 'Bahwa kerja sama ini perlu dituangkan dalam suatu perjanjian tertulis.',
                    'isiPasal1' => 'Pasal 1\\nRUANG LINGKUP KERJA SAMA\\n\\nPara Pihak sepakat untuk melaksanakan kerja sama sesuai ruang lingkup yang telah disepakati.',
                    'isiPasal2' => 'Pasal 2\\nHAK DAN KEWAJIBAN\\n\\nMasing-masing Pihak mempunyai hak dan kewajiban sesuai dengan ketentuan dalam perjanjian ini.',
                ],

                'footer_data' => [
                    'kotaTtd' => 'Jakarta',
                    'jabatanPenandatangan' => 'Direktur Utama',
                    'namaPenandatangan' => 'Drs. H. Aris Budiman, M.B.A.',
                    'nipPenandatangan' => 'NIP: 19780412 200312 1 002',
                    'tembusan' => '1. Arsip Legal\\n2. Para Pihak',
                ],

                'signature_data' => [
                    'selectedMaterai' => 'materai10k',
                    'signatureX' => 65,
                    'signatureY' => 78,
                    'signatureUrl' => null,
                ],
            ],

            'kontrak-kerja' => [
                'title' => 'Kontrak Kerja',
                'type' => 'kontrak',

                'header_data' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => 'Gedung Menara Palma Lt. 18, Jakarta Selatan',
                    'kopKontrak' => 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id',
                    'nomorSurat' => 'PK/001/VIII/2026',
                    'perihalSurat' => 'Perjanjian Kerja',
                    'tanggalSurat' => '10 Agustus 2026',
                    'sifatSurat' => 'Penting',
                ],

                'body_content' => [
                    'tujuanSurat' => 'Perjanjian kerja antara Perusahaan dan Pekerja.',
                    'menimbang' => 'Bahwa Perusahaan membutuhkan tenaga kerja dan Pekerja bersedia melaksanakan pekerjaan sesuai ketentuan.',
                    'mengingat' => 'Ketentuan peraturan perundang-undangan ketenagakerjaan yang berlaku.',
                    'isiPasal1' => 'Pasal 1\\nJABATAN DAN PEKERJAAN\\n\\nPekerja ditempatkan pada jabatan sesuai dengan kebutuhan Perusahaan.',
                    'isiPasal2' => 'Pasal 2\\nHAK DAN KEWAJIBAN\\n\\nPara Pihak wajib melaksanakan hak dan kewajibannya sebagaimana diatur dalam perjanjian ini.',
                ],

                'footer_data' => [
                    'kotaTtd' => 'Jakarta',
                    'jabatanPenandatangan' => 'Direktur Utama',
                    'namaPenandatangan' => 'Drs. H. Aris Budiman, M.B.A.',
                    'nipPenandatangan' => 'NIP: 19780412 200312 1 002',
                    'tembusan' => '1. Arsip Legal',
                ],

                'signature_data' => [
                    'selectedMaterai' => 'materai10k',
                    'signatureX' => 65,
                    'signatureY' => 78,
                    'signatureUrl' => null,
                ],
            ],

            'surat-kuasa' => [
                'title' => 'Surat Kuasa',
                'type' => 'surat',

                'header_data' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => 'Gedung Menara Palma Lt. 18, Jakarta Selatan',
                    'kopKontrak' => 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id',
                    'nomorSurat' => 'SK/001/VIII/2026',
                    'perihalSurat' => 'Surat Kuasa',
                    'tanggalSurat' => '10 Agustus 2026',
                    'sifatSurat' => 'Penting',
                ],

                'body_content' => [
                    'tujuanSurat' => 'SURAT KUASA',
                    'menimbang' => 'Yang bertanda tangan di bawah ini memberikan kuasa kepada pihak yang disebutkan dalam surat ini.',
                    'mengingat' => 'Untuk melaksanakan tindakan sebagaimana ruang lingkup kuasa yang diberikan.',
                    'isiPasal1' => 'RUANG LINGKUP KUASA\\n\\nPenerima Kuasa diberikan kewenangan untuk mewakili Pemberi Kuasa dalam urusan yang telah ditentukan.',
                    'isiPasal2' => 'MASA BERLAKU\\n\\nSurat kuasa ini berlaku sejak tanggal ditandatangani sampai dengan dinyatakan berakhir.',
                ],

                'footer_data' => [
                    'kotaTtd' => 'Jakarta',
                    'jabatanPenandatangan' => 'Pemberi Kuasa',
                    'namaPenandatangan' => 'Drs. H. Aris Budiman, M.B.A.',
                    'nipPenandatangan' => 'NIP: 19780412 200312 1 002',
                    'tembusan' => '1. Arsip',
                ],

                'signature_data' => [
                    'selectedMaterai' => 'materai10k',
                    'signatureX' => 65,
                    'signatureY' => 78,
                    'signatureUrl' => null,
                ],
            ],

            'surat-pernyataan' => [
                'title' => 'Surat Pernyataan',
                'type' => 'surat',

                'header_data' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => 'Gedung Menara Palma Lt. 18, Jakarta Selatan',
                    'kopKontrak' => 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id',
                    'nomorSurat' => 'SP/001/VIII/2026',
                    'perihalSurat' => 'Surat Pernyataan',
                    'tanggalSurat' => '10 Agustus 2026',
                    'sifatSurat' => 'Penting',
                ],

                'body_content' => [
                    'tujuanSurat' => 'SURAT PERNYATAAN',
                    'menimbang' => 'Dengan ini saya menyatakan dengan sebenar-benarnya bahwa seluruh informasi yang diberikan adalah benar.',
                    'mengingat' => 'Pernyataan ini dibuat dengan penuh tanggung jawab.',
                    'isiPasal1' => 'ISI PERNYATAAN\\n\\nDengan ini saya menyatakan bahwa seluruh data dan keterangan yang diberikan kepada Perusahaan adalah benar.',
                    'isiPasal2' => 'KETENTUAN\\n\\nApabila di kemudian hari terdapat ketidaksesuaian, saya bersedia bertanggung jawab sesuai ketentuan yang berlaku.',
                ],

                'footer_data' => [
                    'kotaTtd' => 'Jakarta',
                    'jabatanPenandatangan' => 'Yang Membuat Pernyataan',
                    'namaPenandatangan' => 'Drs. H. Aris Budiman, M.B.A.',
                    'nipPenandatangan' => 'NIP: 19780412 200312 1 002',
                    'tembusan' => '1. Arsip Legal',
                ],

                'signature_data' => [
                    'selectedMaterai' => 'materai10k',
                    'signatureX' => 65,
                    'signatureY' => 78,
                    'signatureUrl' => null,
                ],
            ],
        ];

        if (!isset($templates[$template])) {
            abort(404, 'Template tidak ditemukan.');
        }

        return view('pages.editor', [
            'title' => 'Buat Dokumen: '.$templates[$template]['title'],
            'document' => null,
            'templateData' => $templates[$template],
            'signatures' => $this->userSignatures(),
        ]);
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
}