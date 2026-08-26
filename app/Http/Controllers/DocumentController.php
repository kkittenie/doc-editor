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
            $bodyHtml = $template['body_html']
                ?? $this->buildTemplateBodyHtml($template['body_content'] ?? []);
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
                    'preamble' => "Pada hari ini, Senin tanggal dua puluh enam bulan Agustus tahun dua ribu dua puluh enam (26-08-2026), yang bertanda tangan di bawah ini:",

                    'paraPihak' => [
                        "1. PT NUSANTARA CITRA MEDIA TBBK, suatu perseroan terbatas yang berkedudukan dan berkantor pusat di Jakarta Selatan, beralamat di Gedung Menara Palma Lantai 18, Jalan H.R. Rasuna Said Blok X-2, Jakarta Selatan 12950, dalam hal ini diwakili secara sah oleh Bapak Arya Wibisono, S.H., M.H., selaku Direktur Utama, yang bertindak untuk dan atas nama Perseroan, berdasarkan Akta Pendirian Nomor 12 tanggal 8 Februari 2015 yang telah diubah dengan Akta Nomor 27 tanggal 5 Juli 2020 dan disahkan oleh Menteri Hukum dan Hak Asasi Manusia Republik Indonesia, selanjutnya disebut sebagai “PIHAK PERTAMA”;",
                        "2. CV KARYA DIGITAL NUSANTARA, suatu persekutuan komanditer yang berkedudukan di Bandung, beralamat di Jalan Dago Asri Nomor 45, Bandung 40135, dalam hal ini diwakili secara sah oleh Ibu Ratna Kumala Sari, selaku Direktur, yang bertindak berdasarkan Akta Pendirian Nomor 33 tanggal 21 Maret 2018, selanjutnya disebut sebagai “PIHAK KEDUA”;",
                        "PIHAK PERTAMA dan PIHAK KEDUA selanjutnya secara bersama-sama disebut sebagai “PARA PIHAK”, dan masing-masing disebut sebagai “PIHAK”.",
                        "PARA PIHAK terlebih dahulu menerangkan sebagai berikut:",
                    ],

                    'menimbang' => "a. bahwa PIHAK PERTAMA bergerak di bidang penyediaan jasa media, komunikasi, dan pengelolaan konten digital, serta membutuhkan mitra kerja profesional untuk mengembangkan dan mengelola layanannya;\nb. bahwa PIHAK KEDUA bergerak di bidang teknologi informasi dan pengembangan perangkat lunak, serta memiliki kemampuan, kapasitas, dan sumber daya yang dibutuhkan oleh PIHAK PERTAMA;\nc. bahwa PARA PIHAK telah melakukan musyawarah dan sepakat untuk mengadakan kerja sama yang dituangkan dalam bentuk perjanjian secara tertulis.",

                    'mengingat' => "1. Kitab Undang-Undang Hukum Perdata (KUHP);\n2. Undang-Undang Nomor 19 Tahun 2016 tentang perubahan atas Undang-Undang Nomor 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik;\n3. Peraturan perundang-undangan lainnya yang berlaku serta Anggaran Dasar dan ketentuan internal masing-masing PIHAK.",

                    'isi' => [
                        ['judul' => 'DEFINISI', 'text' => "Dalam Perjanjian ini, kecuali ditentukan lain, istilah-istilah berikut memiliki arti sebagai berikut:\n\n1. “Perjanjian” adalah Perjanjian Kerja Sama ini beserta seluruh lampiran, addendum, dan/atau amandemen yang merupakan satu kesatuan yang tidak terpisahkan.\n2. “Jasa” adalah layanan teknologi informasi dan pengelolaan konten digital yang disediakan oleh PIHAK KEDUA kepada PIHAK PERTAMA.\n3. “Materi” adalah seluruh dokumen, konten, data, dan informasi yang dihasilkan atau diolah dalam rangka pelaksanaan Perjanjian ini.\n4. “Hari Kerja” adalah hari Senin sampai dengan Jumat, tidak termasuk hari Sabtu, Minggu, dan hari libur nasional yang ditetapkan oleh Pemerintah."],
                        ['judul' => 'MAKSUD DAN TUJUAN', 'text' => "Perjanjian ini dibuat dengan maksud untuk mengikat PARA PIHAK dalam suatu hubungan kerja sama yang saling menguntungkan, dengan tujuan:\n\na. menyelaraskan kepentingan PARA PIHAK dalam penyediaan dan pemanfaatan Jasa;\nb. menjamin terlaksananya hak dan kewajiban masing-masing PIHAK sesuai dengan ketentuan yang disepakati; dan\nc. memberikan dasar hukum yang jelas bagi PARA PIHAK dalam pelaksanaan kerja sama ini."],
                        ['judul' => 'RUANG LINGKUP KERJA SAMA', 'text' => "PIHAK KEDUA bersedia menyediakan Jasa kepada PIHAK PERTAMA sesuai dengan karakteristik dan spesifikasi yang dicantumkan dalam Lampiran I Perjanjian ini. PIHAK PERTAMA dengan ini bersedia untuk menerima dan membayar Jasa tersebut sesuai dengan spesifikasi serta biaya yang dicantumkan dalam Lampiran II Perjanjian ini."],
                        ['judul' => 'JANGKA WAKTU', 'text' => "Perjanjian ini berlaku sejak tanggal penandatanganannya untuk jangka waktu selama 2 (dua) tahun dan dapat diperpanjang berdasarkan kesepakatan PARA PIHAK dengan menerbitkan Addendum. Dalam hal salah satu PIHAK bermaksud mengakhiri Perjanjian ini, wajib memberitahukan secara tertulis kepada PIHAK lainnya sekurang-kurangnya 30 (tiga puluh) hari sebelum tanggal berakhirnya."],
                        ['judul' => 'HAK DAN KEWAJIBAN PIHAK PERTAMA', 'text' => "Hak dan kewajiban PIHAK PERTAMA adalah sebagai berikut:\n\na. menerima Jasa dan hasil kerja sesuai dengan ketentuan dalam Perjanjian ini;\nb. menyediakan data, dokumen, dan informasi kepada PIHAK KEDUA yang diperlukan untuk kelancaran pelaksanaan pekerjaan;\nc. membayar biaya jasa sesuai dengan nominal serta jadwal yang disepakati; dan\nd. melakukan monitoring terhadap hasil pekerjaan yang dilakukan oleh PIHAK KEDUA."],
                        ['judul' => 'HAK DAN KEWAJIBAN PIHAK KEDUA', 'text' => "Hak dan kewajiban PIHAK KEDUA adalah sebagai berikut:\n\na. melaksanakan dan menyediakan Jasa sesuai dengan spesifikasi dan jadwal yang disepakati;\nb. menjamin bahwa Jasa yang diberikan bebas dari gangguan dan sesuai dengan standar yang ditentukan;\nc. menjaga kerahasiaan seluruh data dan informasi milik PIHAK PERTAMA yang diperoleh selama berlangsungnya pelaksanaan pekerjaan; dan\nd. berhak menerima pembayaran atas pekerjaan yang telah disepakati sesuai dengan ketentuan yang berlaku."],
                        ['judul' => 'KAIDAH PELAKSANAAN', 'text' => "PARA PIHAK sepakat untuk melaksanakan kegiatan sebagaimana dimaksud dalam Pasal 3 dengan penuh itikad baik, ketekunan, dan semangat kerja sama, serta berpedoman pada rencana kerja dan jadwal yang telah disepakati bersama. Segala perubahan terhadap ruang lingkup dan jadwal pelaksanaan dapat dilakukan atas kesepakatan tertulis PARA PIHAK."],
                        ['judul' => 'KERAHASIAAN', 'text' => "PARA PIHAK sepakat, baik selama jangka waktu maupun setelah berakhirnya Perjanjian, untuk menjaga kerahasiaan seluruh informasi tertulis maupun lisan yang berhubungan dengan bisnis, kegiatan usaha, dan pekerjaan PIHAK lainnya yang diperoleh selama pelaksanaan Perjanjian ini, kecuali informasi yang telah menjadi domain publik bukan disebabkan oleh pelanggaran salah satu PIHAK."],
                        ['judul' => 'JAMINAN DAN TANGGUNG JAWAB', 'text' => "Masing-masing PIHAK bertanggung jawab atas seluruh kerugian yang timbul sebagai akibat kelalaian, pelanggaran, atau itikad tidak baik dalam melaksanakan ketentuan Perjanjian ini. PIHAK yang terbukti melanggar wajib mengganti seluruh kerugian yang dialami oleh PIHAK lainnya sesuai dengan ketentuan peraturan perundang-undangan yang berlaku."],
                        ['judul' => 'FORCE MAJEURE', 'text' => "Yang dimaksud dengan force majeure adalah keadaan di luar kekuasaan salah satu PIHAK yang mengakibatkan kewajibannya tidak dapat dilaksanakan, antara lain bencana alam, perang, huru-hara, kebakaran, pemogokan, dan larangan dari Pemerintah. PIHAK yang mengalami force majeure wajib memberitahukan kepada PIHAK lainnya dalam waktu 7 (tujuh) Hari Kerja disertai dengan surat keterangan dari instansi yang berwenang."],
                        ['judul' => 'PENYELESAIAN PERSELISIHAN', 'text' => "Setiap perselisihan yang timbul akibat atau dalam pelaksanaan Perjanjian ini akan diselesaikan secara musyawarah dan kekeluargaan untuk mufakat. Apabila tidak tercapai kesepakatan dalam jangka waktu 30 (tiga puluh) hari, PARA PIHAK sepakat untuk memilih domisili hukum yang tetap di Pengadilan Negeri Jakarta Selatan."],
                        ['judul' => 'PEMBAYARAN', 'text' => "Nilai, harga, dan tata cara pembayaran atas Jasa yang diberikan oleh PIHAK KEDUA kepada PIHAK PERTAMA diatur secara terperinci sebagaimana tercantum dalam Lampiran II Perjanjian ini. Seluruh pembayaran dilakukan dalam mata uang Rupiah melalui transfer dana ke rekening bank atas nama PIHAK KEDUA. Setiap keterlambatan pembayaran yang dilakukan oleh PIHAK PERTAMA dapat dikenakan biaya keterlambatan sebagaimana diatur dalam Lampiran II, dan pembayaran yang telah diterima tidak dapat ditarik kembali kecuali terdapat kesepakatan tertulis PARA PIHAK."],
                        ['judul' => 'LAIN-LAIN', 'text' => "Hal-hal yang belum diatur atau belum cukup diatur dalam Perjanjian ini akan ditetapkan oleh PARA PIHAK kemudian dengan musyawarah untuk mufakat dan dituangkan dalam bentuk Addendum yang merupakan satu kesatuan dan tidak terpisahkan dari Perjanjian ini. Setiap ketentuan dalam Perjanjian ini yang untuk sebagian atau seluruhnya tidak sah atau batal demi hukum tidak akan mempengaruhi ketentuan lainnya yang tetap sah. Tidak ada PIHAK yang dapat mengalihkan hak dan/atau kewajibannya dalam Perjanjian ini kepada pihak lain tanpa persetujuan tertulis terlebih dahulu dari PIHAK lainnya."],
                        ['judul' => 'PENUTUP', 'text' => "Demikian Perjanjian Kerja Sama ini dibuat dan ditandatangani oleh PARA PIHAK dalam rangkap dua (2) yang masing-masing berkekuatan hukum yang sama, untuk dilaksanakan dengan penuh itikad baik. Apabila terdapat hal-hal yang belum diatur dalam Perjanjian ini, PARA PIHAK sepakat untuk menetapkannya kemudian dan dituangkan dalam Addendum yang merupakan satu kesatuan yang tidak terpisahkan dari Perjanjian ini."],
                    ],
'lampiran' => [
                        ['judul' => 'LAMPIRAN I — SPESIFIKASI DAN RUANG LINGKUP JASA', 'text' => "Dalam Lampiran ini diuraikan secara terperinci spesifikasi, ruang lingkup, dan mutu Jasa yang wajib disediakan serta dilaksanakan oleh PIHAK KEDUA untuk dan atas nama PIHAK PERTAMA selama berlangsungnya Perjanjian, antara lain:\n\n1. Pengembangan dan pemeliharaan platform teknologi informasi milik PIHAK PERTAMA;\n2. Pengelolaan dan moderasi konten digital pada seluruh saluran media;\n3. Layanan analisis data dan pelaporan secara berkala bagi kepentingan PIHAK PERTAMA;\n4. Dukungan teknis atas segala gangguan operasional dengan waktu tanggap maksimal 1 x 24 (satu kali dua puluh empat) jam sejak laporan diterima.\n\nSeluruh hasil pekerjaan wajib memenuhi standar kualitas yang ditetapkan oleh PIHAK PERTAMA serta seluruh peraturan perundang-undangan yang berlaku. Perubahan terhadap spesifikasi pekerjaan hanya dimungkinkan atas persetujuan tertulis PARA PIHAK."],
                        ['judul' => 'LAMPIRAN II — NILAI PEKERJAAN DAN TATA CARA PEMBAYARAN', 'text' => "Sebagai imbalan atas pelaksanaan seluruh Jasa sebagaimana dimaksud dalam Lampiran I, PIHAK PERTAMA bersedia membayar kepada PIHAK KEDUA dengan ketentuan sebagai berikut:\n\n1. Nilai pekerjaan disepakati sebesar Rp. 120.000.000,- (seratus dua puluh juta rupiah) untuk jangka waktu 1 (satu) tahun;\n2. Pembayaran dilakukan dalam 4 (empat) termin, masing-masing sebesar Rp. 30.000.000,- (tiga puluh juta rupiah) per tiga bulan;\n3. Seluruh pembayaran dilakukan melalui transfer dana ke rekening bank atas nama PIHAK KEDUA selambat-lambatnya pada akhir bulan berjalan;\n4. Apabila terjadi keterlambatan pembayaran, PIHAK PERTAMA dikenakan denda sebesar 0,2% (nol koma dua persen) per hari keterlambatan;\n5. Seluruh pajak yang timbul sehubungan dengan perjanjian ini akan ditanggung dan menjadi beban masing-masing PIHAK sesuai dengan ketentuan peraturan perundang-undangan yang berlaku."],
                    ],
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
                    'preamble' => "Pada hari ini, Senin tanggal dua puluh empat bulan Agustus tahun dua ribu dua puluh enam (24-08-2026), yang bertanda tangan di bawah ini:",

                    'paraPihak' => [
                        "1. PT NUSANTARA CITRA MEDIA TBBK, suatu perseroan terbatas yang berkedudukan di Jakarta Selatan, beralamat di Gedung Menara Palma Lantai 18, Jalan H.R. Rasuna Said Blok X-2, Jakarta Selatan 12950, dalam hal ini diwakili secara sah oleh Ibu Dewi Lestari, S.E., selaku Direktur Sumber Daya Manusia, yang bertindak untuk dan atas nama Perusahaan, selanjutnya disebut sebagai “PERUSAHAAN”;",
                        "2. Sdr. DIMAS ADI PRAYOGA, lahir di Surabaya pada tanggal 17 Maret 1998, NIK 3578171703980001, bertempat tinggal di Jalan Menur Permai Nomor 21, Surabaya, selaku calon Pekerja yang selanjutnya disebut sebagai “PEKERJA”;",
                        "PERUSAHAAN dan PEKERJA secara bersama-sama disebut sebagai “PARA PIHAK”.",
                    ],

                    'menimbang' => "a. bahwa PERUSAHAAN membutuhkan tenaga kerja untuk melaksanakan pekerjaan di bidang pemasaran digital;\nb. bahwa PEKERJA bersedia dan sanggup untuk melaksanakan pekerjaan tersebut sesuai dengan kualifikasi yang dipersyaratkan; dan\nc. bahwa PARA PIHAK sepakat untuk mengadakan Perjanjian Kerja dalam bentuk tertulis berdasarkan ketentuan peraturan perundang-undangan yang berlaku.",

                    'mengingat' => "1. Undang-Undang Nomor 13 Tahun 2003 tentang Ketenagakerjaan;\n2. Peraturan Pemerintah Nomor 35 Tahun 2021 tentang Perjanjian Kerja Waktu Tertentu, Alih Daya, dan Keselamatan dan Kesehatan Kerja;\n3. Ketentuan dan tata tertib yang berlaku di lingkungan PERUSAHAAN.",

                    'isi' => [
                        ['judul' => 'JABATAN DAN RUANG LINGKUP PEKERJAAN', 'text' => "PEKERJA dengan ini ditempatkan pada jabatan sebagai Staff Pemasaran Digital di lingkungan Divisi Pemasaran PERUSAHAAN. PEKERJA wajib melaksanakan seluruh pekerjaan, tugas, dan tanggung jawab yang diberikan oleh PERUSAHAAN sesuai dengan jabatan dan keahliannya, serta bersedia ditempatkan di lokasi kerja yang ditentukan oleh PERUSAHAAN."],
                        ['judul' => 'STATUS DAN JANGKA WAKTU', 'text' => "Perjanjian Kerja ini dibuat dalam bentuk Perjanjian Kerja Waktu Tertentu (PKWT) untuk jangka waktu selama 1 (satu) tahun terhitung mulai tanggal 1 September 2026 sampai dengan tanggal 31 Agustus 2027. Perjanjian ini dapat diperpanjang sesuai dengan ketentuan peraturan perundang-undangan yang berlaku dan berdasarkan kesepakatan PARA PIHAK."],
                        ['judul' => 'WAKTU KERJA', 'text' => "Waktu kerja PEKERJA ditetapkan sebanyak 8 (delapan) jam dalam 1 (satu) hari dan 40 (empat puluh) jam dalam 1 (satu) minggu selama 5 (lima) hari kerja. PEKERJA wajib hadir dan melaksanakan pekerjaan tepat waktu sesuai dengan jam kerja yang ditetapkan oleh PERUSAHAAN."],
                        ['judul' => 'GAJI DAN TUNJANGAN', 'text' => "Sebagai imbalan atas pelaksanaan pekerjaan, PERUSAHAAN akan membayar kepada PEKERJA berupa:\n\na. gaji pokok sebesar Rp. 5.000.000,- (lima juta rupiah) setiap bulan;\nb. tunjangan makan sebesar Rp. 500.000,- (lima ratus ribu rupiah) setiap bulan;\nc. Tunjangan Hari Raya (THR) sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.\n\nPembayaran gaji dilakukan selambat-lambatnya pada tanggal 2 (dua) setiap bulannya.\n"],
                        ['judul' => 'JAMINAN SOSIAL', 'text' => "Selama berlakunya Perjanjian ini, PERUSAHAAN akan mendaftarkan PEKERJA dalam program Jaminan Sosial Ketenagakerjaan dan Jaminan Kesehatan sesuai dengan ketentuan BPJS Ketenagakerjaan dan BPJS Kesehatan yang biaya iurannya dibebankan sesuai dengan ketentuan peraturan perundang-undangan yang berlaku."],
                        ['judul' => 'HAK DAN KEWAJIBAN PEKERJA', 'text' => "Hak dan kewajiban PEKERJA adalah sebagai berikut:\n\na. menerima gaji dan tunjangan serta memperoleh lingkungan kerja yang sehat dan aman;\nb. melaksanakan pekerjaan dengan penuh tanggung jawab, teliti, dan menjunjung tinggi etika kerja;\nc. mematuhi seluruh peraturan dan tata tertib yang berlaku di lingkungan PERUSAHAAN; dan\nd. menjaga rahasia PERUSAHAAN selama bekerja maupun setelah perhubungan kerja berakhir.\n"],
                        ['judul' => 'HAK DAN KEWAJIBAN PERUSAHAAN', 'text' => "Hak dan kewajiban PERUSAHAAN adalah sebagai berikut:\n\na. memberikan pekerjaan, gaji, tunjangan, dan jaminan sosial kepada PEKERJA sesuai dengan ketentuan yang berlaku;\nb. memberikan pembinaan, pelatihan, dan pengembangan kompetensi kepada PEKERJA;\nc. menyediakan sarana dan prasarana kerja yang diperlukan bagi kelancaran pelaksanaan pekerjaan; dan\nd. menilai dan mengevaluasi kinerja PEKERJA secara periodik dan objektif."],
                        ['judul' => 'DISIPLIN DAN TATA TERTIB', 'text' => "PEKERJA wajib mematuhi seluruh peraturan perusahaan dan tata tertib yang berlaku. Pelanggaran terhadap tata tertib dapat dikenakan sanksi sesuai dengan tingkat pelanggaran, berupa teguran lisan, teguran tertulis, sampai dengan pemutusan hubungan kerja berdasarkan ketentuan yang berlaku."],
                        ['judul' => 'PEMUTUSAN HUBUNGAN KERJA', 'text' => "Perjanjian Kerja ini berakhir apabila: (a) jangka waktu yang ditentukan telah berakhir; (b) atas kesepakatan PARA PIHAK; atau (c) sesuai dengan ketentuan peraturan perundang-undangan tentang ketenagakerjaan yang berlaku. Dalam hal terjadi pemutusan hubungan kerja, PERUSAHAAN akan memberikan hak-hak PEKERJA sesuai dengan peraturan perundang-undangan."],
                        ['judul' => 'KERAHASIAAN', 'text' => "PEKERJA wajib menjaga dan menyimpan kerahasiaan seluruh dokumen, data, dan keterangan yang berkaitan dengan kegiatan usaha PERUSAHAAN yang diketahuinya baik selama maupun setelah berakhirnya Perjanjian ini, kecuali yang telah menjadi informasi publik atau diwajibkan oleh peraturan perundang-undangan untuk diungkapkan."],
                        ['judul' => 'PENYELESAIAN PERSELISIHAN', 'text' => "Segala perselisihan yang timbul dalam pelaksanaan Perjanjian ini wajib diselesaikan secara musyawarah untuk mencapai mufakat terlebih dahulu. Apabila tidak tercapai kesepakatan, maka akan diselesaikan melalui mekanisme penyelesaian perselisihan hubungan industri berdasarkan ketentuan peraturan perundang-undangan yang berlaku."],
                        ['judul' => 'KESEHATAN DAN KESELAMATAN KERJA', 'text' => "PERUSAHAAN wajib melaksanakan seluruh ketentuan kesehatan dan keselamatan kerja (K3) serta menyediakan alat pelindung diri dan sarana kerja yang aman bagi PEKERJA. PEKERJA wajib mematuhi seluruh prosedur K3 yang diberlakukan oleh PERUSAHAAN serta melaporkan setiap kondisi atau potensi kecelakaan kerja kepada pejabat yang berwenang. Apabila PEKERJA mengalami kecelakaan kerja, seluruh biaya yang timbul menjadi tanggung jawab PERUSAHAAN sesuai dengan ketentuan peraturan perundang-undangan yang berlaku."],
                        ['judul' => 'LAIN-LAIN', 'text' => "Segala sesuatu yang belum diatur atau belum cukup diatur dalam Perjanjian ini akan diatur lebih lanjut berdasarkan kesepakatan PARA PIHAK yang dituangkan dalam Perjanjian Tambahan (Addendum) yang merupakan satu kesatuan dan tidak terpisahkan dari Perjanjian ini. Segala perubahan atas Perjanjian ini hanya sah apabila dilakukan secara tertulis dan ditandatangani oleh PARA PIHAK. Tidak ada suatu hal dalam Perjanjian ini yang dapat ditafsirkan sebagai penciptaan hubungan kerja sama atau kemitraan antara PARA PIHAK yang melampaui hubungan kerja berdasarkan Perjanjian ini."],
                        ['judul' => 'PENUTUP', 'text' => "Demikian Perjanjian Kerja ini dibuat dan ditandatangani oleh PARA PIHAK dalam rangkap dua (2), masing-masing berkekuatan hukum yang sama. Apabila terdapat hal-hal yang belum diatur dalam Perjanjian ini, PARA PIHAK sepakat untuk mengaturnya kemudian dan dituangkan dalam Perjanjian Tambahan (Addendum) yang merupakan satu kesatuan dan tidak terpisahkan dari Perjanjian ini."],
                    ],
'lampiran' => [
                        ['judul' => 'LAMPIRAN A — RINCIAN GAJI, TUNJANGAN, DAN JAMINAN', 'text' => "Rincian imbalan kerja yang diterima oleh PEKERJA selama berlakunya Perjanjian Kerja ini adalah sebagai berikut:\n\n1. Gaji pokok sebesar Rp. 5.000.000,- (lima juta rupiah) setiap bulan;\n2. Tunjangan makan sebesar Rp. 500.000,- (lima ratus ribu rupiah) setiap bulan;\n3. Tunjangan transportasi sebesar Rp. 300.000,- (tiga ratus ribu rupiah) setiap bulan;\n4. Tunjangan Hari Raya (THR) sesuai dengan ketentuan peraturan perundang-undangan yang berlaku;\n5. Pendaftaran keikutsertaan dalam program Jaminan Sosial Ketenagakerjaan dan Jaminan Kesehatan sesuai dengan ketentuan BPJS.\n\nSeluruh komponen imbalan tersebut di atas akan ditinjau secara berkala dan dapat disesuaikan berdasarkan kinerja PEKERJA dan kesepakatan PARA PIHAK."],
                        ['judul' => 'LAMPIRAN B — PROSEDUR PENILAIAN KINERJA', 'text' => "Penilaian kinerja PEKERJA dilakukan secara periodik setiap 6 (enam) bulan melalui mekanisme sebagai berikut:\n\n1. Penyusunan target dan sasaran kinerja oleh atasan langsung;\n2. Evaluasi pencapaian target kinerja serta penilaian sikap, disiplin, dan kerja sama;\n3. Wawancara penilaian antara PEKERJA dengan atasan langsung; serta\n4. Pengolahan hasil penilaian menjadi dasar penetapan penghargaan dan/atau pengembangan karier PEKERJA.\n\nHasil penilaian kinerja dapat dijadikan dasar bagi PERUSAHAAN untuk memberikan pencatatan atas kinerja yang dicapai oleh PEKERJA."],
                    ],
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
                    'preamble' => "Yang bertanda tangan di bawah ini:",

                    'paraPihak' => [
                        "1. Bapak BUDI SETIAWAN, lahir di Jakarta pada tanggal 12 Mei 1978, NIK 3171021205780009, pekerjaan Wiraswasta, bertempat tinggal di Jalan Kemang Timur Nomor 8, Jakarta Selatan, dalam hal ini bertindak untuk dan atas nama Pribadi, selanjutnya disebut sebagai “PEMBERI KUASA”;",
                        "2. Ibu SUCIATI, S.H., lahir di Semarang pada tanggal 3 Juli 1985, NIK 3174037307850002, pekerjaan Advokat, bertempat tinggal di Jalan Senopati Dalam III Nomor 21, Jakarta Selatan, dalam hal ini bertindak untuk dan atas nama Pribadi, selanjutnya disebut sebagai “PENERIMA KUASA”;",
                    ],

                    'menimbang' => "a. bahwa PEMBERI KUASA memberikan kuasa yang sah kepada PENERIMA KUASA untuk melaksanakan seluruh tindakan sebagaimana tercantum dalam Surat Kuasa ini;\nb. bahwa PENERIMA KUASA bersedia dan sanggup untuk menerima serta melaksanakan kuasa yang diberikan tersebut; dan\nc. bahwa pemberian kuasa ini dibuat berdasarkan persetujuan sukarela kedua belah pihak tanpa adanya paksaan dari pihak manapun.",

                    'mengingat' => "1. Kitab Undang-Undang Hukum Perdata (KUHPerdata);\n2. Undang-Undang Nomor 18 Tahun 2003 tentang Advokat; dan\n3. Peraturan perundang-undangan lainnya yang berlaku.",

                    'isi' => [
                        ['judul' => 'RUANG LINGKUP KUASA', 'text' => "PENERIMA KUASA dengan ini diberikan kewenangan penuh untuk mewakili PEMBERI KUASA dalam melakukan segala tindakan hukum yang berkaitan dengan pengurusan dokumen pertanahan atas tanah dan bangunan yang terletak di Jalan Kemang Timur Nomor 8, Jakarta Selatan, atas nama PEMBERI KUASA."],
                        ['judul' => 'TINDAKAN YANG DIKUASAKAN', 'text' => "Berdasarkan kuasa yang diberikan, PENERIMA KUASA untuk dan atas nama PEMBERI KUASA berwenang untuk:\n\na. menghadiri dan mewakili PEMBERI KUASA dalam setiap pengurusan administrasi dan perizinan;\nb. menandatangani seluruh dokumen yang diperlukan dalam rangka penyelesaian urusan yang dikuasakan;\nc. menerima dan menyerahkan dokumen, uang, serta bukti-bukti kepemilikan yang berkaitan dengan penyelesaian urusan; dan\nd. melakukan tindakan umum lainnya yang diperlukan agar kuasa dapat dilaksanakan sesuai dengan maksud dan tujuannya.\n"],
                        ['judul' => 'KEWAJIBAN PENERIMA KUASA', 'text' => "PENERIMA KUASA wajib melaksanakan seluruh kuasa yang diberikan dengan itikad baik, teliti, penuh tanggung jawab, serta sesuai dengan petunjuk dan kepentingan PEMBERI KUASA. PENERIMA KUASA wajib memberikan laporan pelaksanaan kuasa secara berkala kepada PEMBERI KUASA."],
                        ['judul' => 'MASA BERLAKU', 'text' => "Surat Kuasa ini berlaku sejak tanggal ditandatangani dan berakhir pada saat seluruh kuasa telah selesai dilaksanakan, dicabut kembali oleh PEMBERI KUASA, atau melalui surat pernyataan berakhirnya kuasa dari PEMBERI KUASA."],
                        ['judul' => 'PELAKSANAAN KUASA', 'text' => "PENERIMA KUASA wajib melaksanakan kuasa yang diberikan ini dengan penuh itikad baik, cermat, dan teliti sesuai dengan maksud serta tujuan pemberian kuasa sebagaimana tercantum dalam surat ini. Dalam melaksanakan kuasa, PENERIMA KUASA dapat berkonsultasi dan/atau meminta petunjuk kepada PEMBERI KUASA bilamana terdapat hal-hal yang memerlukan pertimbangan atau keputusan lebih lanjut."],
                        ['judul' => 'BATASAN KEWENANGAN', 'text' => "PENERIMA KUASA tidak berwenang melakukan tindakan hukum di luar maksud dan ruang lingkup kuasa yang diberikan, antara lain tidak berwenang untuk mengalihkan kepemilikan, membebani dengan hak tanggungan, atau melakukan perjanjian lain atas harta benda milik PEMBERI KUASA, kecuali apabila terdapat kuasa khusus yang diberikan secara tertulis oleh PEMBERI KUASA."],
                        ['judul' => 'PELAPORAN', 'text' => "PENERIMA KUASA wajib menyampaikan laporan pelaksanaan kuasa secara tertulis kepada PEMBERI KUASA setiap kali diminta atau sekurang-kurangnya satu kali dalam setiap tiga bulan, meliputi perkembangan serta hasil pelaksanaan seluruh urusan yang dikuasakan. Laporan tersebut menjadi dasar evaluasi PEMBERI KUASA terhadap kelanjutan pelaksanaan kuasa."],
                        ['judul' => 'KERAHASIAAN', 'text' => "PENERIMA KUASA wajib menjaga kerahasiaan seluruh dokumen, data, dan keterangan yang diperoleh selama melaksanakan kuasa ini, baik selama proses pelaksanaan maupun setelah berakhirnya kuasa, kecuali apabila keterangan tersebut wajib diungkapkan berdasarkan peraturan perundang-undangan yang berlaku atau perintah aparat penegak hukum yang sah."],
                        ['judul' => 'LARANGAN DELEGASI', 'text' => "PENERIMA KUASA dilarang untuk menyerahkan atau mendelegasikan seluruh atau sebagian pelaksanaan kuasa ini kepada pihak lain, baik seluruhnya maupun sebagian, tanpa persetujuan tertulis terlebih dahulu dari PEMBERI KUASA. Setiap pendelegasian yang dilakukan tanpa persetujuan tertulis merupakan tindakan di luar kuasa dan menjadi tanggung jawab pribadi PENERIMA KUASA."],
                        ['judul' => 'TANGGUNG JAWAB PENERIMA KUASA', 'text' => "PENERIMA KUASA bertanggung jawab secara pribadi atas seluruh pelaksanaan kuasa yang diberikan, serta wajib mengganti segala kerugian yang timbul sebagai akibat dari kelalaian, kesalahan, atau penyalahgunaan wewenang dalam melaksanakan kuasa ini. PENERIMA KUASA tidak berhak menuntut ganti kerugian kepada PEMBERI KUASA sepanjang bertindak sesuai dengan batasan kuasa yang diberikan."],
                        ['judul' => 'PENCABUTAN KUASA', 'text' => "PEMBERI KUASA dapat sewaktu-waktu mencabut atau mengakhiri kuasa ini dengan menyampaikan pemberitahuan secara tertulis kepada PENERIMA KUASA. Pencabutan kuasa berlaku sejak pemberitahuan diterima oleh PENERIMA KUASA, dan seluruh kuasa yang telah diberikan dinyatakan berakhir tanpa mengurangi tanggung jawab PENERIMA KUASA atas pelaksanaan urusan yang telah dilakukan."],
                        ['judul' => 'PENYELESAIAN PERSELISIHAN', 'text' => "Segala perselisihan yang timbul sehubungan dengan pelaksanaan Surat Kuasa ini akan diselesaikan secara musyawarah untuk mencapai mufakat antara PEMBERI KUASA dan PENERIMA KUASA. Dalam hal tidak tercapai kesepakatan, PARA PIHAK sepakat untuk menyelesaikannya melalui pengadilan yang berwenang sesuai dengan ketentuan peraturan perundang-undangan yang berlaku."],
                        ['judul' => 'KETENTUAN PENUTUP', 'text' => "Surat Kuasa ini merupakan kuasa khusus yang dibuat dalam rangka penyelesaian urusan tertentu sebagaimana disebutkan dalam ruang lingkup kuasa, dan oleh karenanya tidak dapat dipergunakan untuk kepentingan di luar maksud serta tujuan pemberian kuasa. Segala tindakan yang dilakukan oleh PENERIMA KUASA di luar batasan kuasa bukan merupakan tanggung jawab PEMBERI KUASA kecuali apabila dikecualikan oleh ketentuan yang berlaku."],
                        ['judul' => 'PENUTUP', 'text' => "Demikian Surat Kuasa ini dibuat dengan sebenar-benarnya untuk dipergunakan sebagaimana mestinya. Apabila di kemudian hari terdapat kekeliruan dalam Surat Kuasa ini, akan dilakukan perbaikan sebagaimana semestinya."],
                    ],
'lampiran' => [
                        ['judul' => 'LAMPIRAN — DAFTAR DOKUMEN DAN URUSAN YANG DIKUASAKAN', 'text' => "Dalam rangka pelaksanaan Surat Kuasa ini, PEMBERI KUASA menyerahkan serta memberikan kewenangan kepada PENERIMA KUASA untuk mengurus hal-hal sebagai berikut:\n\n1. Pengurusan dan pendaftaran balik nama hak atas tanah dan bangunan yang terletak di Jalan Kemang Timur Nomor 8, Jakarta Selatan;\n2. Penandatanganan seluruh dokumen yang diperlukan, baik surat permohonan, pernyataan, maupun bukti-bukti administrasi lainnya;\n3. Pembayaran seluruh biaya, pajak, dan bea yang timbul sehubungan dengan pengurusan dimaksud;\n4. Penerimaan dan penyerahan dokumen kepemilikan serta seluruh kelengkapan lainnya kepada instansi yang berwenang.\n\nSeluruh dokumen yang diserahkan kepada PENERIMA KUASA dicatat dan harus dikembalikan kepada PEMBERI KUASA setelah seluruh urusan selesai dilaksanakan."],
                    ],
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
                    'preamble' => "Yang bertanda tangan di bawah ini:",

                    'paraPihak' => [
                        "Nama : RIZKI PRATAMA\nTempat, Tanggal Lahir : Surabaya, 9 November 1995\nNIK : 3578120911950006\nAlamat : Jalan Dukuh Kupang Barat Nomor 12, Surabaya\nPekerjaan : Karyawan Swasta\n\nSelanjutnya disebut sebagai “SAYA”.",
                        "Untuk keperluan administrasi perusahaan, dengan ini menyatakan sebagai berikut:",
                    ],

                    'menimbang' => "a. bahwa SAYA menyatakan kebenaran atas seluruh keterangan dan data yang diberikan sesuai dengan yang diketahui sepenuhnya serta dapat dipertanggungjawabkan; dan\nb. bahwa pernyataan ini dibuat dengan sebenar-benarnya dan tanpa ada paksaan dari pihak manapun, serta digunakan untuk keperluan yang sah.",

                    'mengingat' => "1. Kitab Undang-Undang Hukum Perdata (KUHPerdata); dan\n2. Ketentuan peraturan perundang-undangan yang berlaku.",

                    'isi' => [
                        ['judul' => 'PERNYATAAN', 'text' => "Dengan ini menyatakan bahwa seluruh data, dokumen, dan keterangan yang diberikan kepada PT NUSANTARA CITRA MEDIA TBBK adalah benar, sah, dan dapat dipertanggungjawabkan secara hukum. Seluruh informasi tersebut tidak pernah diubah, dipalsukan, atau direkayasa dengan maksud tertentu."],
                        ['judul' => 'ITIKAD BAIK', 'text' => "Dengan ini menyatakan bersedia untuk melaksanakan seluruh kewajiban dan ketentuan yang berlaku dengan penuh itikad baik, serta tidak akan melakukan perbuatan yang dapat merugikan pihak perusahaan maupun pihak lainnya."],
                        ['judul' => 'KETENTUAN HUKUM', 'text' => "Apabila di kemudian hari terdapat ketidaksesuaian antara pernyataan dengan keadaan yang sebenarnya, maka dengan ini bersedia bertanggung jawab dan menanggung segala akibat hukum yang timbul sesuai dengan ketentuan peraturan perundang-undangan yang berlaku."],
                        ['judul' => 'SANKSI', 'text' => "Menyadari bahwa pernyataan ini dipergunakan untuk berbagai keperluan yang sah, maka apabila terdapat penyalahgunaan atau pemalsuan dalam pernyataan ini, bersedia menerima sanksi yang ditetapkan berdasarkan peraturan perundang-undangan."],
                        ['judul' => 'PERNYATAAN KEBENARAN DOKUMEN', 'text' => "Saya menyatakan bahwa seluruh dokumen, surat, dan bukti yang saya lampirkan serta serahkan kepada PT NUSANTARA CITRA MEDIA TBBK adalah asli, sah, dan benar secara hukum, serta tidak pernah dipalsukan. Apabila diketahui dokumen tersebut tidak asli atau tidak benar, saya bersedia menanggung akibat hukum sebagaimana ketentuan peraturan perundang-undangan yang berlaku."],
                        ['judul' => 'KEPEMILIKAN DAN KEABSAHAN DOKUMEN', 'text' => "Saya menyatakan bahwa seluruh dokumen yang diserahkan merupakan milik saya atau yang berhak, serta tidak sedang dijadikan jaminan, objek sengketa, atau tidak sedang berada dalam penguasaan pihak lain tanpa dasar hukum yang sah. Saya menjamin keabsahan seluruh dokumen tersebut untuk digunakan sebagaimana mestinya."],
                        ['judul' => 'KESADARAN DAN TANPA PAKSAAN', 'text' => "Saya menyatakan bahwa seluruh pernyataan dan keterangan dalam Surat Pernyataan ini saya buat dengan itikad baik, secara sadar, dan tanpa adanya unsur paksaan, tekanan, atau penyesatan dari pihak manapun, serta semata-mata untuk kepentingan yang sah dalam rangka melengkapi administrasi perusahaan."],
                        ['judul' => 'PELAKSANAAN KEWAJIBAN', 'text' => "Saya menyatakan sanggup dan bersedia untuk melaksanakan serta mematuhi seluruh kewajiban yang timbul berdasarkan Surat Pernyataan ini dan ketentuan peraturan perundang-undangan, serta bersedia memberikan seluruh informasi tambahan yang diperlukan bilamana diminta oleh perusahaan atau instansi yang berwenang."],
                        ['judul' => 'PENGGUNAAN DATA', 'text' => "Saya menyatakan bahwa seluruh data dan keterangan pribadi yang saya sampaikan dapat dipergunakan oleh PT NUSANTARA CITRA MEDIA TBBK untuk keperluan administrasi, pengelolaan data kepegawaian, serta kepentingan lainnya yang sah sesuai dengan peraturan perundang-undangan tentang perlindungan data pribadi."],
                        ['judul' => 'JAMINAN', 'text' => "Saya menjamin bahwa seluruh data, dokumen, dan keterangan yang saya berikan adalah benar dan sah, serta saya bersedia mempertanggungjawabkan seluruhnya di hadapan hukum apabila di kemudian hari terbukti terdapat kekeliruan atau ketidaksesuaian antara pernyataan dengan keadaan yang sebenarnya."],
                        ['judul' => 'TANGGUNG JAWAB', 'text' => "Saya bertanggung jawab penuh secara pribadi atas kebenaran seluruh isi Surat Pernyataan ini, termasuk seluruh lampiran yang menyertainya. Apabila di kemudian hari timbul kerugian bagi pihak manapun sebagai akibat dari ketidakbenaran pernyataan saya, maka saya bersedia untuk mengganti seluruh kerugian tersebut."],
                        ['judul' => 'PENYELESAIAN PERSELISIHAN', 'text' => "Segala perselisihan yang timbul sehubungan dengan pelaksanaan Surat Pernyataan ini akan diselesaikan secara musyawarah untuk mencapai mufakat. Dalam hal tidak tercapai kesepakatan, perselisihan akan diselesaikan melalui sarana hukum yang tersedia sesuai dengan ketentuan peraturan perundang-undangan yang berlaku."],
                        ['judul' => 'LAIN-LAIN', 'text' => "Hal-hal yang belum diatur atau belum cukup diatur dalam Surat Pernyataan ini akan disesuaikan dan dilengkapi berdasarkan ketentuan peraturan perundang-undangan yang berlaku serta keputusan perusahaan yang sah. Surat Pernyataan ini merupakan bagian yang tidak terpisahkan dari seluruh dokumen yang saya serahkan kepada perusahaan."],
                        ['judul' => 'PENUTUP', 'text' => "Demikian Surat Pernyataan ini saya buat dengan sebenarnya dan tanpa adanya paksaan dari pihak manapun, untuk dipergunakan sebagaimana mestinya, serta dengan penuh kesadaran bahwa setiap pernyataan yang tidak benar dapat menimbulkan akibat hukum bagi saya."],
                    ],

                    'tutup' => "Demikian Surat Pernyataan ini dibuat dan ditandatangani di tempat dan pada tanggal sebagaimana tersebut di atas.",
'lampiran' => [
                        ['judul' => 'LAMPIRAN — DAFTAR DATA DAN DOKUMEN YANG DISERAHKAN', 'text' => "Data dan dokumen yang menjadi dasar serta dilampirkan dalam Surat Pernyataan ini adalah sebagai berikut:\n\n1. Fotokopi Kartu Tanda Penduduk (KTP) yang masih berlaku;\n2. Kartu Nomor Pokok Wajib Pajak (NPWP);\n3. Salinan ijazah dan dokumen pendukung kualifikasi pendidikan;\n4. Surat keterangan domisili dari pejabat yang berwenang;\n5. Dokumen pendukung lainnya yang dipersyaratkan oleh perusahaan.\n\nSeluruh data dan dokumen tersebut di atas merupakan bagian yang tidak terpisahkan dari Surat Pernyataan ini dan kebenarannya menjadi tanggung jawab saya sebagai pembuat pernyataan."],
                    ],
            ],
            ],
            'kemitraan' => [
                'title' => 'Perjanjian Kerjasama Jual Kembali Jasa Layanan Akses Internet',
                'header_data' => ['nomorSurat' => 'PKS/001/III/2026'],
            ],
            'colocation' => [
                'title' => 'Perjanjian Berlangganan Jasa Colocation',
                'header_data' => ['nomorSurat' => 'COLO/001/VII/2026'],
            ],
            'managed-service' => [
                'title' => 'Perjanjian Berlangganan Jasa Managed Service',
                'header_data' => ['nomorSurat' => 'MS/001/IV/2026'],
            ],
            'soho' => [
                'title' => 'Perjanjian Berlangganan Jasa SOHO',
                'header_data' => ['nomorSurat' => 'SOHO/001/IX/2025'],
            ],
            'kontrak-payung' => [
                'title' => 'Perjanjian Kerja Sama (Kontrak Payung) Berlangganan Jasa Metro Fiber Optik',
                'header_data' => ['nomorSurat' => 'METRO/001/VI/2026'],
            ],
        ];

        if (!isset($templates[$key])) {
            return null;
        }

        $template = $templates[$key];

        // body_html template yang beneran hasil convert docx cuma dimuat pas
        // key-nya cocok -- biar gak baca 5 file tiap kali fungsi ini dipanggil
        // buat key lain.
        $htmlFiles = [
            'kemitraan'       => 'kemitraan.html',
            'colocation'      => 'colocation.html',
            'managed-service' => 'managed-service.html',
            'soho'            => 'soho.html',
            'kontrak-payung'  => 'kontrak-payung.html',
        ];

        if (isset($htmlFiles[$key])) {
            $path = resource_path('document-templates/'.$htmlFiles[$key]);
            $template['body_html'] = file_exists($path) ? file_get_contents($path) : '';
        }

        return $template;
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
    private function buildTemplateBodyHtml(array $body): string
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
            $heading = 'PASAL '.$number.($judul !== '' ? ' — '.$judul : '');
            $parts[] = '<p><strong>'.$heading.'</strong></p>';
            $parts[] = $this->contractPara($pasal['text'] ?? '');
            $number++;
        }

        // 6) Penutup
        if (!empty($body['tutup'])) {
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
                if ($judul === '') {
                    $judul = 'LAMPIRAN';
                }
                $parts[] = '<p><strong>'.$judul.'</strong></p>';
                $parts[] = $this->contractPara($item['text'] ?? '');
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