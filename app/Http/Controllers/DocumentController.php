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
                ?? $this->buildTemplateBodyHtml($template['body_content'] ?? [], in_array($templateKey, ['kontrak-kemitraan', 'kontrak-colocation']));
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
        $templates = [
            'perjanjian-kerja-sama' => [
                'title' => 'Perjanjian Kerja Sama (PKS)',
                'header_data' => [
                    'kopInstansi' => '[Ketik nama perusahaan PIHAK PERTAMA]',
                    'kopAlamat' => '[Ketik alamat instansi di sini]',
                    'kopKontrak' => 'Telp: [Ketik nomor telepon di sini] | Email: [Ketik email di sini] | Web: [Ketik alamat website di sini]',
                    'nomorSurat' => 'PKS/001/VIII/2026',
                    'perihalSurat' => 'Perjanjian Kerja Sama',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'preamble' => "Pada hari ini, Senin tanggal dua puluh enam bulan Agustus tahun dua ribu dua puluh enam (26-08-2026), yang bertanda tangan di bawah ini:",

                    'paraPihak' => [
                        "1. [Ketik nama perusahaan PIHAK PERTAMA], suatu perseroan terbatas yang berkedudukan dan berkantor pusat di Jakarta Selatan, beralamat di [Ketik alamat perusahaan di sini], dalam hal ini diwakili secara sah oleh Bapak [Ketik nama di sini], selaku Direktur Utama, yang bertindak untuk dan atas nama Perseroan, berdasarkan Akta Pendirian Nomor [Nomor Akta] tanggal [Tanggal Akta] yang telah diubah dengan Akta Nomor [Nomor Akta] tanggal [Tanggal Akta] dan disahkan oleh Menteri Hukum dan Hak Asasi Manusia Republik Indonesia, selanjutnya disebut sebagai “PIHAK PERTAMA”;",
                        "2. [Ketik nama perusahaan PIHAK KEDUA], suatu persekutuan komanditer yang berkedudukan di Bandung, beralamat di [Ketik alamat perusahaan di sini], dalam hal ini diwakili secara sah oleh Ibu [Ketik nama di sini], selaku Direktur, yang bertindak berdasarkan Akta Pendirian Nomor [Nomor Akta] tanggal [Tanggal Akta], selanjutnya disebut sebagai “PIHAK KEDUA”;",
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
                    'kopInstansi' => '[Ketik nama perusahaan PIHAK PERTAMA]',
                    'kopAlamat' => '[Ketik alamat instansi di sini]',
                    'kopKontrak' => 'Telp: [Ketik nomor telepon di sini] | Email: [Ketik email di sini]',
                    'nomorSurat' => 'PK/001/VIII/2026',
                    'perihalSurat' => 'Perjanjian Kerja',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'preamble' => "Pada hari ini, Senin tanggal dua puluh empat bulan Agustus tahun dua ribu dua puluh enam (24-08-2026), yang bertanda tangan di bawah ini:",

                    'paraPihak' => [
                        "1. [Ketik nama perusahaan PIHAK PERTAMA], suatu perseroan terbatas yang berkedudukan di Jakarta Selatan, beralamat di [Ketik alamat perusahaan di sini], dalam hal ini diwakili secara sah oleh Ibu [Ketik nama di sini], selaku Direktur Sumber Daya Manusia, yang bertindak untuk dan atas nama Perusahaan, selanjutnya disebut sebagai “PERUSAHAAN”;",
                        "2. Sdr. [Ketik nama di sini], lahir di [Kota] pada tanggal [Tanggal Lahir], NIK [Ketik NIK di sini], bertempat tinggal di [Ketik alamat di sini], selaku calon Pekerja yang selanjutnya disebut sebagai “PEKERJA”;",
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
                    'kopInstansi' => '[Ketik nama perusahaan PIHAK PERTAMA]',
                    'kopAlamat' => '[Ketik alamat instansi di sini]',
                    'kopKontrak' => 'Telp: [Ketik nomor telepon di sini] | Email: [Ketik email di sini]',
                    'nomorSurat' => 'SK/001/VIII/2026',
                    'perihalSurat' => 'Surat Kuasa',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'preamble' => "Yang bertanda tangan di bawah ini:",

                    'paraPihak' => [
                        "1. Bapak [Ketik nama di sini], lahir di [Kota] pada tanggal [Tanggal Lahir], NIK [Ketik NIK di sini], pekerjaan Wiraswasta, bertempat tinggal di [Ketik alamat di sini], dalam hal ini bertindak untuk dan atas nama Pribadi, selanjutnya disebut sebagai “PEMBERI KUASA”;",
                        "2. Ibu [Ketik nama di sini], lahir di [Kota] pada tanggal [Tanggal Lahir], NIK [Ketik NIK di sini], pekerjaan Advokat, bertempat tinggal di [Ketik alamat di sini], dalam hal ini bertindak untuk dan atas nama Pribadi, selanjutnya disebut sebagai “PENERIMA KUASA”;",
                    ],

                    'menimbang' => "a. bahwa PEMBERI KUASA memberikan kuasa yang sah kepada PENERIMA KUASA untuk melaksanakan seluruh tindakan sebagaimana tercantum dalam Surat Kuasa ini;\nb. bahwa PENERIMA KUASA bersedia dan sanggup untuk menerima serta melaksanakan kuasa yang diberikan tersebut; dan\nc. bahwa pemberian kuasa ini dibuat berdasarkan persetujuan sukarela kedua belah pihak tanpa adanya paksaan dari pihak manapun.",

                    'mengingat' => "1. Kitab Undang-Undang Hukum Perdata (KUHPerdata);\n2. Undang-Undang Nomor 18 Tahun 2003 tentang Advokat; dan\n3. Peraturan perundang-undangan lainnya yang berlaku.",

                    'isi' => [
                        ['judul' => 'RUANG LINGKUP KUASA', 'text' => "PENERIMA KUASA dengan ini diberikan kewenangan penuh untuk mewakili PEMBERI KUASA dalam melakukan segala tindakan hukum yang berkaitan dengan pengurusan dokumen pertanahan atas tanah dan bangunan yang terletak di [Ketik alamat di sini], atas nama PEMBERI KUASA."],
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
                        ['judul' => 'LAMPIRAN — DAFTAR DOKUMEN DAN URUSAN YANG DIKUASAKAN', 'text' => "Dalam rangka pelaksanaan Surat Kuasa ini, PEMBERI KUASA menyerahkan serta memberikan kewenangan kepada PENERIMA KUASA untuk mengurus hal-hal sebagai berikut:\n\n1. Pengurusan dan pendaftaran balik nama hak atas tanah dan bangunan yang terletak di [Ketik alamat di sini];\n2. Penandatanganan seluruh dokumen yang diperlukan, baik surat permohonan, pernyataan, maupun bukti-bukti administrasi lainnya;\n3. Pembayaran seluruh biaya, pajak, dan bea yang timbul sehubungan dengan pengurusan dimaksud;\n4. Penerimaan dan penyerahan dokumen kepemilikan serta seluruh kelengkapan lainnya kepada instansi yang berwenang.\n\nSeluruh dokumen yang diserahkan kepada PENERIMA KUASA dicatat dan harus dikembalikan kepada PEMBERI KUASA setelah seluruh urusan selesai dilaksanakan."],
                    ],
            ],
            ],
            'surat-pernyataan' => [
                'title' => 'Surat Pernyataan',
                'header_data' => [
                    'kopInstansi' => '[Ketik nama perusahaan PIHAK PERTAMA]',
                    'kopAlamat' => '[Ketik alamat instansi di sini]',
                    'kopKontrak' => 'Telp: [Ketik nomor telepon di sini] | Email: [Ketik email di sini]',
                    'nomorSurat' => 'SP/001/VIII/2026',
                    'perihalSurat' => 'Surat Pernyataan',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'preamble' => "Yang bertanda tangan di bawah ini:",

                    'paraPihak' => [
                        "Nama : [Ketik nama di sini]\nTempat, Tanggal Lahir : [Ketik kota & tanggal lahir di sini]\nNIK : [Ketik NIK di sini]\nAlamat : [Ketik alamat di sini]\nPekerjaan : [Ketik pekerjaan di sini]\n\nSelanjutnya disebut sebagai “SAYA”.",
                        "Untuk keperluan administrasi perusahaan, dengan ini menyatakan sebagai berikut:",
                    ],

                    'menimbang' => "a. bahwa SAYA menyatakan kebenaran atas seluruh keterangan dan data yang diberikan sesuai dengan yang diketahui sepenuhnya serta dapat dipertanggungjawabkan; dan\nb. bahwa pernyataan ini dibuat dengan sebenar-benarnya dan tanpa ada paksaan dari pihak manapun, serta digunakan untuk keperluan yang sah.",

                    'mengingat' => "1. Kitab Undang-Undang Hukum Perdata (KUHPerdata); dan\n2. Ketentuan peraturan perundang-undangan yang berlaku.",

                    'isi' => [
                        ['judul' => 'PERNYATAAN', 'text' => "Dengan ini menyatakan bahwa seluruh data, dokumen, dan keterangan yang diberikan kepada [Ketik nama perusahaan PIHAK PERTAMA] adalah benar, sah, dan dapat dipertanggungjawabkan secara hukum. Seluruh informasi tersebut tidak pernah diubah, dipalsukan, atau direkayasa dengan maksud tertentu."],
                        ['judul' => 'ITIKAD BAIK', 'text' => "Dengan ini menyatakan bersedia untuk melaksanakan seluruh kewajiban dan ketentuan yang berlaku dengan penuh itikad baik, serta tidak akan melakukan perbuatan yang dapat merugikan pihak perusahaan maupun pihak lainnya."],
                        ['judul' => 'KETENTUAN HUKUM', 'text' => "Apabila di kemudian hari terdapat ketidaksesuaian antara pernyataan dengan keadaan yang sebenarnya, maka dengan ini bersedia bertanggung jawab dan menanggung segala akibat hukum yang timbul sesuai dengan ketentuan peraturan perundang-undangan yang berlaku."],
                        ['judul' => 'SANKSI', 'text' => "Menyadari bahwa pernyataan ini dipergunakan untuk berbagai keperluan yang sah, maka apabila terdapat penyalahgunaan atau pemalsuan dalam pernyataan ini, bersedia menerima sanksi yang ditetapkan berdasarkan peraturan perundang-undangan."],
                        ['judul' => 'PERNYATAAN KEBENARAN DOKUMEN', 'text' => "Saya menyatakan bahwa seluruh dokumen, surat, dan bukti yang saya lampirkan serta serahkan kepada [Ketik nama perusahaan PIHAK PERTAMA] adalah asli, sah, dan benar secara hukum, serta tidak pernah dipalsukan. Apabila diketahui dokumen tersebut tidak asli atau tidak benar, saya bersedia menanggung akibat hukum sebagaimana ketentuan peraturan perundang-undangan yang berlaku."],
                        ['judul' => 'KEPEMILIKAN DAN KEABSAHAN DOKUMEN', 'text' => "Saya menyatakan bahwa seluruh dokumen yang diserahkan merupakan milik saya atau yang berhak, serta tidak sedang dijadikan jaminan, objek sengketa, atau tidak sedang berada dalam penguasaan pihak lain tanpa dasar hukum yang sah. Saya menjamin keabsahan seluruh dokumen tersebut untuk digunakan sebagaimana mestinya."],
                        ['judul' => 'KESADARAN DAN TANPA PAKSAAN', 'text' => "Saya menyatakan bahwa seluruh pernyataan dan keterangan dalam Surat Pernyataan ini saya buat dengan itikad baik, secara sadar, dan tanpa adanya unsur paksaan, tekanan, atau penyesatan dari pihak manapun, serta semata-mata untuk kepentingan yang sah dalam rangka melengkapi administrasi perusahaan."],
                        ['judul' => 'PELAKSANAAN KEWAJIBAN', 'text' => "Saya menyatakan sanggup dan bersedia untuk melaksanakan serta mematuhi seluruh kewajiban yang timbul berdasarkan Surat Pernyataan ini dan ketentuan peraturan perundang-undangan, serta bersedia memberikan seluruh informasi tambahan yang diperlukan bilamana diminta oleh perusahaan atau instansi yang berwenang."],
                        ['judul' => 'PENGGUNAAN DATA', 'text' => "Saya menyatakan bahwa seluruh data dan keterangan pribadi yang saya sampaikan dapat dipergunakan oleh [Ketik nama perusahaan PIHAK PERTAMA] untuk keperluan administrasi, pengelolaan data kepegawaian, serta kepentingan lainnya yang sah sesuai dengan peraturan perundang-undangan tentang perlindungan data pribadi."],
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

            'kontrak-colocation' => [
                'title' => 'Perjanjian Layanan Colocation',
                'header_data' => [
                    'kopInstansi' => '[Nama Perusahaan PIHAK PERTAMA]',
                    'kopAlamat' => '[Alamat perusahaan PIHAK PERTAMA]',
                    'kopKontrak' => 'Telp: [Nomor Telepon] | Email: [Email Perusahaan]',
                    'nomorSurat' => '[Nomor Perjanjian]',
                    'perihalSurat' => 'Perjanjian Layanan Colocation',
                    'sifatSurat' => 'Penting',
                ],

                'body_content' => [

                    'preamble' => "Perjanjian Layanan Colocation (selanjutnya disebut “Perjanjian”), dibuat pada [Tanggal Perjanjian], bertempat di [Kota/Kabupaten], oleh dan antara:",

                    'paraPihak' => [
                        "1. [Nama Perusahaan PIHAK PERTAMA], suatu [Bentuk Badan Usaha], yang didirikan berdasarkan Hukum Negara Republik Indonesia, berkedudukan di [Alamat dan Kode Pos]. Berdasarkan Akta Berita Acara RUPS Tahunan Perseroan Terbatas “[Nama Perusahaan PIHAK PERTAMA]”, Nomor [Nomor Akta], [Tanggal Akta], dibuat dihadapan [Nama Notaris], Notaris di [Kota/Kabupaten]. Dalam hal ini diwakili oleh [Nama Pejabat PIHAK PERTAMA], selaku [Jabatan], sah bertindak untuk dan atas nama [Nama Perusahaan PIHAK PERTAMA], selanjutnya disebut sebagai “PIHAK PERTAMA”.",

                        "d e n g a n",

                        "2. [Nama Perusahaan PIHAK KEDUA], suatu [Bentuk Badan Usaha], yang didirikan berdasarkan Hukum Negara Republik Indonesia, berkedudukan di [Alamat dan Kode Pos]. Berdasarkan [Akta Pendirian/Perubahan] Nomor [Nomor Akta], [Tanggal Akta], dibuat dihadapan [Nama Notaris], Notaris di [Kota/Kabupaten]. Dalam hal ini diwakili oleh [Nama Pejabat PIHAK KEDUA], selaku [Jabatan], sah bertindak untuk dan atas nama [Nama Perusahaan PIHAK KEDUA], selanjutnya disebut sebagai “PIHAK KEDUA”.",

                        "PIHAK PERTAMA dan PIHAK KEDUA secara sendiri-sendiri disebut “PIHAK” dan secara bersama-sama disebut “PARA PIHAK”.",

                        "PARA PIHAK terlebih dahulu menerangkan hal-hal sebagai berikut:",

                        "1. Bahwa PIHAK PERTAMA merupakan penyedia fasilitas colocation dan/atau pengelola Data Center yang memiliki fasilitas, infrastruktur, sistem kelistrikan, sistem pendingin, keamanan, jaringan, serta sarana pendukung lainnya untuk penempatan perangkat elektronik dan/atau perangkat jaringan milik pelanggan.",

                        "2. Bahwa PIHAK KEDUA membutuhkan fasilitas penempatan perangkat pada Data Center PIHAK PERTAMA untuk mendukung kebutuhan operasional sistem, jaringan, aplikasi, server, penyimpanan data, dan/atau kebutuhan teknologi informasi lainnya.",

                        "3. Bahwa PIHAK PERTAMA sepakat menyediakan fasilitas Colocation kepada PIHAK KEDUA dan PIHAK KEDUA sepakat menggunakan fasilitas tersebut sesuai dengan syarat dan ketentuan dalam Perjanjian ini.",

                        "4. Bahwa berdasarkan hal-hal tersebut di atas, PARA PIHAK sepakat untuk mengikatkan diri dalam Perjanjian Layanan Colocation dengan syarat-syarat dan ketentuan-ketentuan sebagai berikut:",
                    ],

                    'isi' => [

                        [
                            'judul' => 'DEFINISI',
                            'text' => "Dalam Perjanjian ini yang dimaksud dengan:

            1. Perjanjian adalah Perjanjian Layanan Colocation beserta seluruh lampiran, perubahan, addendum, Service Order Form (SOF), berita acara, dan dokumen lainnya yang merupakan satu kesatuan dan bagian yang tidak terpisahkan dari Perjanjian ini.

            2. Colocation adalah layanan penyediaan tempat dan fasilitas Data Center oleh PIHAK PERTAMA untuk penempatan, pengoperasian, dan pemeliharaan perangkat milik atau yang dikuasai oleh PIHAK KEDUA sesuai dengan kapasitas dan spesifikasi layanan yang disepakati.

            3. Data Center adalah fasilitas yang dikelola oleh PIHAK PERTAMA yang digunakan untuk menempatkan perangkat teknologi informasi dan komunikasi serta dilengkapi dengan infrastruktur pendukung seperti rack, listrik, pendingin, jaringan, keamanan, monitoring, dan fasilitas pendukung lainnya.

            4. Rack adalah tempat atau ruang khusus yang disediakan oleh PIHAK PERTAMA untuk penempatan perangkat milik PIHAK KEDUA sesuai dengan ukuran dan kapasitas yang disepakati.

            5. Perangkat adalah server, router, switch, firewall, storage, perangkat telekomunikasi, perangkat jaringan, kabel, dan perangkat elektronik lainnya yang ditempatkan oleh PIHAK KEDUA pada fasilitas Colocation.

            6. Ruang Colocation adalah area pada Data Center yang diperuntukkan bagi penempatan Perangkat PIHAK KEDUA.

            7. Layanan adalah seluruh fasilitas dan layanan Colocation yang diberikan oleh PIHAK PERTAMA kepada PIHAK KEDUA berdasarkan Perjanjian ini, termasuk namun tidak terbatas pada ruang Rack, sumber daya listrik, pendingin, konektivitas, keamanan, monitoring, dan layanan pendukung lainnya.

            8. Bandwidth adalah kapasitas koneksi jaringan yang disediakan oleh PIHAK PERTAMA kepada PIHAK KEDUA sesuai dengan paket dan spesifikasi yang disepakati.

            9. Cross Connect adalah koneksi fisik atau jaringan yang menghubungkan Perangkat PIHAK KEDUA dengan perangkat, jaringan, Rack, operator, atau penyedia layanan lainnya yang berada di fasilitas Data Center.

            10. SLA (Service Level Agreement) adalah tingkat kualitas dan ketersediaan Layanan yang menjadi standar pelayanan PIHAK PERTAMA sebagaimana ditentukan dalam Perjanjian dan/atau Lampiran.

            11. Maintenance adalah kegiatan pemeliharaan, perbaikan, penggantian, peningkatan, atau pekerjaan teknis lainnya terhadap fasilitas dan infrastruktur Data Center.

            12. Akses adalah hak PIHAK KEDUA dan/atau personel yang ditunjuk untuk memasuki area Data Center sesuai prosedur keamanan dan ketentuan yang berlaku.

            13. Hari Kerja adalah hari Senin sampai dengan Jumat, selain hari libur nasional dan hari yang ditetapkan Pemerintah sebagai hari libur.

            14. Keadaan Memaksa (Force Majeure) adalah keadaan di luar kemampuan dan kendali PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan.

            15. Biaya adalah seluruh kewajiban pembayaran PIHAK KEDUA kepada PIHAK PERTAMA berdasarkan Perjanjian ini, termasuk biaya Colocation, listrik, bandwidth, cross connect, instalasi, maintenance, administrasi, denda, penalti, dan biaya lainnya yang disepakati.

            16. Informasi Rahasia adalah seluruh data, informasi, dokumen, konfigurasi, sistem, spesifikasi teknis, keamanan, dan informasi bisnis yang diperoleh salah satu PIHAK dalam pelaksanaan Perjanjian ini.",
                        ],

                        [
                            'judul' => 'RUANG LINGKUP LAYANAN',
                            'text' => "1. Ruang lingkup Perjanjian ini meliputi penyediaan fasilitas Colocation oleh PIHAK PERTAMA kepada PIHAK KEDUA untuk penempatan dan pengoperasian Perangkat PIHAK KEDUA pada Data Center.

            2. Layanan Colocation sebagaimana dimaksud pada ayat (1) dapat meliputi:

            a. penyediaan Rack atau ruang penempatan perangkat;

            b. penyediaan sumber daya listrik;

            c. penyediaan sistem pendingin;

            d. penyediaan konektivitas jaringan;

            e. penyediaan bandwidth sesuai paket layanan;

            f. penyediaan fasilitas keamanan Data Center;

            g. monitoring infrastruktur;

            h. akses terbatas ke area Colocation;

            i. layanan Cross Connect;

            j. layanan remote hands apabila disepakati; dan

            k. fasilitas pendukung lainnya sebagaimana tercantum dalam Lampiran.

            3. Spesifikasi, jumlah Rack, kapasitas listrik, bandwidth, lokasi Data Center, jenis konektivitas, dan fasilitas lainnya tercantum dalam Lampiran I yang merupakan bagian tidak terpisahkan dari Perjanjian ini.

            4. Penambahan, pengurangan, atau perubahan Layanan hanya dapat dilakukan berdasarkan permohonan PIHAK KEDUA dan persetujuan PIHAK PERTAMA sesuai ketentuan yang berlaku.",
                        ],

                        [
                            'judul' => 'JANGKA WAKTU',
                            'text' => "1. Perjanjian ini berlaku sejak tanggal [Tanggal Mulai] sampai dengan [Tanggal Berakhir] untuk jangka waktu [Jangka Waktu].

            2. Perjanjian dapat diperpanjang berdasarkan kesepakatan tertulis PARA PIHAK.

            3. PIHAK KEDUA wajib menyampaikan permohonan perpanjangan paling lambat [Jumlah Hari] Hari Kalender sebelum tanggal berakhirnya Perjanjian.

            4. Dalam hal tidak terdapat pemberitahuan pengakhiran dari salah satu PIHAK, Perjanjian dapat diperpanjang secara otomatis untuk jangka waktu [Jangka Waktu Perpanjangan] berdasarkan kesepakatan dan ketentuan yang berlaku.

            5. Berakhirnya jangka waktu Perjanjian tidak menghapus kewajiban pembayaran, pengembalian perangkat, penyelesaian kerusakan, dan kewajiban lain yang telah timbul sebelum tanggal berakhirnya Perjanjian.",
                        ],

                        [
                            'judul' => 'HAK DAN KEWAJIBAN PARA PIHAK',
                            'text' => "1. Hak PIHAK PERTAMA

            PIHAK PERTAMA berhak:

            a. menerima pembayaran dari PIHAK KEDUA sesuai ketentuan Perjanjian;

            b. melakukan pemeriksaan terhadap Perangkat dan instalasi yang ditempatkan di Data Center untuk kepentingan keamanan, keselamatan, dan operasional;

            c. menolak pemasangan Perangkat yang tidak memenuhi standar teknis, keamanan, kapasitas listrik, ukuran Rack, atau ketentuan Data Center;

            d. melakukan pembatasan atau penghentian sementara Layanan apabila terdapat kondisi yang dapat mengganggu keamanan, keselamatan, atau operasional Data Center;

            e. melakukan Maintenance terhadap fasilitas Data Center sesuai prosedur yang berlaku;

            f. mengatur prosedur Akses ke Data Center;

            g. melakukan relokasi Rack atau Perangkat apabila diperlukan untuk kepentingan operasional, keamanan, pengembangan fasilitas, atau kondisi darurat dengan pemberitahuan kepada PIHAK KEDUA sejauh memungkinkan; dan

            h. menerima pembayaran atas layanan tambahan yang diminta oleh PIHAK KEDUA.

            2. Kewajiban PIHAK PERTAMA

            PIHAK PERTAMA berkewajiban:

            a. menyediakan fasilitas Colocation sesuai spesifikasi yang disepakati;

            b. menjaga operasional fasilitas Data Center sesuai SLA;

            c. menyediakan sumber daya listrik dan pendingin sesuai kapasitas layanan;

            d. menjaga keamanan fisik fasilitas Data Center sesuai standar operasional yang berlaku;

            e. menyediakan dukungan teknis sesuai jenis layanan yang disepakati;

            f. melakukan monitoring terhadap fasilitas yang menjadi tanggung jawab PIHAK PERTAMA;

            g. memberitahukan kepada PIHAK KEDUA mengenai Maintenance terjadwal yang berpotensi memengaruhi Layanan; dan

            h. menjaga kerahasiaan Informasi Rahasia milik PIHAK KEDUA.

            3. Hak PIHAK KEDUA

            PIHAK KEDUA berhak:

            a. menggunakan fasilitas Colocation sesuai kapasitas dan spesifikasi yang disepakati;

            b. menempatkan Perangkat pada Rack yang telah disediakan;

            c. memperoleh sumber daya listrik, pendingin, dan konektivitas sesuai Layanan;

            d. memperoleh Akses ke area Data Center sesuai prosedur;

            e. memperoleh dukungan teknis sesuai jenis layanan;

            f. memperoleh informasi mengenai gangguan dan Maintenance yang berdampak terhadap Layanan; dan

            g. meminta penambahan atau perubahan Layanan sesuai prosedur.

            4. Kewajiban PIHAK KEDUA

            PIHAK KEDUA berkewajiban:

            a. membayar seluruh tagihan tepat waktu;

            b. memastikan seluruh Perangkat yang ditempatkan di Data Center dalam kondisi baik dan memenuhi standar teknis;

            c. memastikan Perangkat tidak menyebabkan gangguan terhadap perangkat, jaringan, sistem listrik, pendingin, atau fasilitas Data Center;

            d. mematuhi seluruh prosedur keamanan dan Akses Data Center;

            e. menjaga keamanan akun, kredensial, kartu Akses, kunci, dan sarana Akses lainnya;

            f. tidak membawa atau menempatkan bahan, perangkat, atau benda yang berbahaya dan dapat mengganggu keamanan Data Center;

            g. tidak melakukan perubahan terhadap instalasi listrik, jaringan, Rack, sistem pendingin, atau infrastruktur Data Center tanpa persetujuan tertulis dari PIHAK PERTAMA;

            h. bertanggung jawab terhadap keamanan dan konfigurasi Perangkat miliknya;

            i. memastikan seluruh perangkat lunak dan sistem yang digunakan memiliki lisensi atau hak penggunaan yang sah;

            j. menunjuk personel yang berwenang untuk melakukan pekerjaan di Data Center;

            k. menjaga kebersihan dan kerapian area Rack;

            l. mematuhi ketentuan peraturan perundang-undangan yang berlaku; dan

            m. bertanggung jawab atas kerugian yang timbul akibat kelalaian atau tindakan PIHAK KEDUA maupun personelnya.",
                        ],

                        [
                            'judul' => 'PERANGKAT DAN INSTALASI',
                            'text' => "1. Seluruh Perangkat yang ditempatkan pada fasilitas Colocation merupakan milik atau berada dalam penguasaan PIHAK KEDUA dan bukan merupakan milik PIHAK PERTAMA.

            2. PIHAK KEDUA bertanggung jawab atas pengadaan, pengiriman, pemasangan, konfigurasi, pemeliharaan, dan pengoperasian Perangkat miliknya, kecuali layanan tersebut secara khusus menjadi tanggung jawab PIHAK PERTAMA berdasarkan Perjanjian.

            3. Setiap pemasangan Perangkat wajib memperoleh persetujuan dan mengikuti prosedur teknis PIHAK PERTAMA.

            4. PIHAK KEDUA dilarang melakukan instalasi yang dapat menyebabkan beban listrik, panas, interferensi, gangguan jaringan, atau risiko keselamatan terhadap fasilitas Data Center.

            5. PIHAK PERTAMA berhak meminta PIHAK KEDUA melakukan pemindahan, penggantian, atau penghentian Perangkat apabila Perangkat tersebut terbukti mengganggu operasional atau keamanan Data Center.

            6. PIHAK KEDUA bertanggung jawab atas kerusakan yang disebabkan oleh Perangkat atau instalasi miliknya.

            7. PIHAK PERTAMA tidak bertanggung jawab atas kerusakan atau kehilangan data yang tersimpan pada Perangkat PIHAK KEDUA, kecuali dapat dibuktikan terjadi akibat kelalaian PIHAK PERTAMA.

            8. Ketentuan mengenai spesifikasi Perangkat, jumlah Rack, konsumsi listrik, ukuran Perangkat, dan konfigurasi teknis diatur dalam Lampiran.",
                        ],

                        [
                            'judul' => 'AKSES DAN KEAMANAN DATA CENTER',
                            'text' => "1. Akses ke Data Center hanya diberikan kepada personel PIHAK KEDUA yang telah didaftarkan dan mendapatkan persetujuan PIHAK PERTAMA.

            2. Setiap personel PIHAK KEDUA wajib mematuhi seluruh prosedur keamanan, registrasi, identifikasi, pendampingan, dan ketentuan Akses yang berlaku.

            3. PIHAK KEDUA wajib memberikan daftar personel yang memiliki kewenangan untuk melakukan pekerjaan di Data Center.

            4. PIHAK PERTAMA berhak menolak atau membatasi Akses apabila personel PIHAK KEDUA tidak memenuhi prosedur keamanan.

            5. PIHAK KEDUA dilarang memberikan kartu Akses, kunci, password, atau sarana Akses lainnya kepada pihak yang tidak berwenang.

            6. Setiap kehilangan kartu Akses, kunci, atau perangkat keamanan lainnya wajib segera dilaporkan kepada PIHAK PERTAMA.

            7. PIHAK KEDUA bertanggung jawab atas tindakan personelnya selama berada di lingkungan Data Center.

            8. PIHAK PERTAMA berhak menerapkan sistem pengawasan dan pencatatan Akses untuk kepentingan keamanan fasilitas.",
                        ],

                        [
                            'judul' => 'KONEKTIVITAS DAN BANDWIDTH',
                            'text' => "1. PIHAK PERTAMA menyediakan konektivitas dan/atau Bandwidth sesuai spesifikasi Layanan yang tercantum dalam Lampiran.

            2. Penambahan Bandwidth, Cross Connect, IP Address, atau konektivitas lainnya dapat dikenakan biaya tambahan.

            3. PIHAK KEDUA bertanggung jawab atas konfigurasi Perangkat jaringan miliknya kecuali layanan konfigurasi tersebut secara khusus disepakati menjadi tanggung jawab PIHAK PERTAMA.

            4. PIHAK KEDUA dilarang menggunakan konektivitas untuk kegiatan yang melanggar hukum atau yang dapat mengganggu jaringan PIHAK PERTAMA maupun pelanggan lainnya.

            5. PIHAK PERTAMA berhak melakukan pembatasan sementara terhadap konektivitas apabila ditemukan aktivitas yang mengancam keamanan jaringan, melanggar hukum, atau berpotensi mengganggu jaringan.

            6. Setiap perubahan konfigurasi jaringan yang dilakukan PIHAK KEDUA wajib mengikuti prosedur teknis PIHAK PERTAMA apabila perubahan tersebut dapat berdampak terhadap jaringan Data Center.",
                        ],

                        [
                            'judul' => 'BIAYA DAN CARA PEMBAYARAN',
                            'text' => "1. Biaya Layanan Colocation meliputi biaya Rack, listrik, bandwidth, konektivitas, instalasi, dan layanan lainnya sebagaimana tercantum dalam Lampiran.

            2. Biaya dapat terdiri dari biaya berulang bulanan (recurring) dan biaya satu kali (one time charge).

            3. Layanan tambahan seperti Cross Connect, Remote Hands, penambahan daya listrik, penambahan Bandwidth, relokasi Perangkat, dan layanan lainnya dapat dikenakan biaya tambahan.

            4. PIHAK PERTAMA akan menerbitkan invoice kepada PIHAK KEDUA sesuai periode penagihan yang disepakati.

            5. Invoice wajib dibayarkan oleh PIHAK KEDUA paling lambat [Jumlah Hari] Hari Kerja sejak tanggal invoice diterbitkan.

            6. Seluruh pembayaran dilakukan melalui transfer ke rekening yang ditetapkan oleh PIHAK PERTAMA.

            7. Keterlambatan pembayaran dapat dikenakan denda atau biaya keterlambatan sebesar [Persentase] atau sesuai ketentuan yang tercantum dalam Lampiran.

            8. Pajak yang timbul atas pelaksanaan Perjanjian ini dilaksanakan sesuai ketentuan peraturan perundang-undangan yang berlaku.",
                        ],

                        [
                            'judul' => 'SERVICE LEVEL AGREEMENT (SLA)',
                            'text' => "1. PIHAK PERTAMA memberikan tingkat layanan sesuai SLA yang tercantum dalam Lampiran Perjanjian.

            2. SLA dapat meliputi antara lain:

            a. ketersediaan fasilitas Data Center;

            b. ketersediaan sumber daya listrik;

            c. ketersediaan sistem pendingin;

            d. ketersediaan konektivitas;

            e. waktu respons terhadap gangguan; dan

            f. waktu penanganan gangguan.

            3. SLA tidak berlaku terhadap gangguan yang disebabkan oleh:

            a. Perangkat PIHAK KEDUA;

            b. konfigurasi PIHAK KEDUA;

            c. tindakan atau kelalaian PIHAK KEDUA;

            d. Maintenance terjadwal;

            e. Force Majeure;

            f. gangguan dari pihak ketiga di luar kendali PIHAK PERTAMA; atau

            g. kondisi lainnya sebagaimana ditentukan dalam SLA.

            4. PIHAK PERTAMA wajib melakukan upaya yang wajar untuk menangani gangguan yang menjadi tanggung jawabnya sesuai SLA.",
                        ],

                        [
                            'judul' => 'MAINTENANCE DAN GANGGUAN',
                            'text' => "1. PIHAK PERTAMA berhak melakukan Maintenance terjadwal maupun tidak terjadwal untuk menjaga keamanan dan keandalan Data Center.

            2. Untuk Maintenance terjadwal yang berpotensi memengaruhi Layanan, PIHAK PERTAMA akan memberikan pemberitahuan kepada PIHAK KEDUA sesuai prosedur yang berlaku.

            3. Dalam keadaan darurat, PIHAK PERTAMA dapat melakukan tindakan Maintenance tanpa pemberitahuan terlebih dahulu apabila diperlukan untuk mencegah kerusakan yang lebih besar atau menjaga keselamatan dan keamanan Data Center.

            4. PIHAK KEDUA wajib memberikan akses teknis yang diperlukan apabila gangguan berasal dari Perangkat atau konfigurasi milik PIHAK KEDUA.

            5. PIHAK PERTAMA tidak bertanggung jawab atas gangguan yang disebabkan oleh Perangkat, konfigurasi, aplikasi, atau tindakan PIHAK KEDUA.

            6. Setiap gangguan akan ditangani sesuai prosedur penanganan gangguan dan SLA yang berlaku.",
                        ],

                        [
                            'judul' => 'DATA DAN KERAHASIAAN',
                            'text' => "1. PARA PIHAK wajib menjaga kerahasiaan seluruh informasi yang diperoleh sehubungan dengan pelaksanaan Perjanjian ini.

            2. PIHAK PERTAMA tidak diperkenankan mengakses isi data yang tersimpan pada Perangkat PIHAK KEDUA kecuali diperlukan untuk pelaksanaan Layanan, penanganan gangguan, keamanan, pemenuhan kewajiban hukum, atau berdasarkan persetujuan PIHAK KEDUA.

            3. PIHAK KEDUA bertanggung jawab atas keamanan data, aplikasi, sistem operasi, database, dan informasi yang tersimpan pada Perangkat miliknya.

            4. PIHAK KEDUA wajib melakukan pengamanan yang wajar terhadap sistem dan Perangkat untuk mencegah akses yang tidak sah.

            5. PARA PIHAK wajib menjaga kerahasiaan Informasi Rahasia selama Perjanjian berlangsung dan selama 5 (lima) tahun setelah Perjanjian berakhir atau selama jangka waktu lain yang diwajibkan oleh peraturan perundang-undangan.

            6. Kewajiban kerahasiaan tidak berlaku terhadap informasi yang wajib diberikan berdasarkan perintah pengadilan atau ketentuan peraturan perundang-undangan.",
                        ],

                        [
                            'judul' => 'WANPRESTASI DAN SANKSI',
                            'text' => "1. Salah satu PIHAK dinyatakan melakukan wanprestasi apabila tidak memenuhi kewajiban material berdasarkan Perjanjian.

            2. PIHAK KEDUA dianggap melakukan wanprestasi apabila:

            a. tidak membayar tagihan sesuai jatuh tempo;

            b. menggunakan fasilitas untuk kegiatan yang melanggar hukum;

            c. memberikan akses kepada pihak yang tidak berwenang;

            d. melakukan perubahan terhadap fasilitas Data Center tanpa izin;

            e. menempatkan Perangkat yang membahayakan keamanan atau operasional Data Center;

            f. melakukan tindakan yang menyebabkan gangguan terhadap pelanggan lain; atau

            g. melanggar ketentuan material lainnya dalam Perjanjian.

            3. Dalam hal terjadi wanprestasi, PIHAK PERTAMA berhak memberikan:

            a. teguran tertulis;

            b. pembatasan layanan;

            c. penghentian sementara layanan;

            d. pengenaan denda atau penalti;

            e. penarikan atau pemindahan Perangkat sesuai prosedur; dan/atau

            f. pengakhiran Perjanjian.

            4. Dalam hal wanprestasi berkaitan dengan pembayaran, PIHAK PERTAMA dapat melakukan pembatasan atau penghentian Layanan sesuai mekanisme yang ditentukan dalam Perjanjian.

            5. Pengenaan sanksi tidak menghapus kewajiban PIHAK KEDUA untuk melunasi seluruh kewajiban pembayaran dan mengganti kerugian yang timbul akibat pelanggaran.

            6. Dalam hal pelanggaran menimbulkan risiko terhadap keamanan atau keselamatan Data Center, PIHAK PERTAMA berhak mengambil tindakan segera yang diperlukan untuk mencegah kerugian yang lebih besar.",
                        ],

                        [
                            'judul' => 'PENGAKHIRAN PERJANJIAN',
                            'text' => "1. Perjanjian berakhir apabila:

            a. jangka waktu Perjanjian berakhir dan tidak diperpanjang;

            b. PARA PIHAK sepakat secara tertulis untuk mengakhiri Perjanjian;

            c. salah satu PIHAK melakukan wanprestasi material yang tidak diperbaiki dalam jangka waktu yang ditentukan;

            d. salah satu PIHAK dinyatakan pailit atau dibubarkan;

            e. Layanan tidak dapat lagi disediakan berdasarkan ketentuan hukum atau kebijakan Pemerintah; atau

            f. terjadi keadaan lain yang menyebabkan Perjanjian tidak dapat dilaksanakan.

            2. Dalam hal Perjanjian berakhir, PIHAK KEDUA wajib:

            a. menghentikan penggunaan fasilitas Colocation;

            b. melakukan pembongkaran dan pengambilan seluruh Perangkat;

            c. menyelesaikan seluruh kewajiban pembayaran;

            d. mengembalikan kartu Akses, kunci, atau fasilitas milik PIHAK PERTAMA;

            e. mengembalikan area Rack dalam kondisi baik; dan

            f. memenuhi kewajiban lain yang masih terutang.

            3. Pengambilan Perangkat wajib dilakukan paling lambat [Jumlah Hari] Hari Kalender sejak tanggal efektif pengakhiran.

            4. Apabila PIHAK KEDUA tidak mengambil Perangkat dalam jangka waktu tersebut, PIHAK PERTAMA berhak melakukan tindakan penyimpanan, pemindahan, atau tindakan lain sesuai ketentuan Perjanjian dan peraturan perundang-undangan.

            5. PARA PIHAK sepakat untuk mengesampingkan berlakunya Pasal 1266 KUHPerdata sepanjang diperbolehkan berdasarkan ketentuan peraturan perundang-undangan.",
                        ],

                        [
                            'judul' => 'TANGGUNG JAWAB DAN BATASAN TANGGUNG JAWAB',
                            'text' => "1. Masing-masing PIHAK bertanggung jawab atas kerugian yang timbul akibat kesalahan atau kelalaiannya dalam melaksanakan Perjanjian.

            2. PIHAK KEDUA bertanggung jawab atas Perangkat, data, aplikasi, konfigurasi, dan sistem yang dimilikinya.

            3. PIHAK PERTAMA bertanggung jawab atas fasilitas dan infrastruktur yang secara tegas menjadi tanggung jawab PIHAK PERTAMA berdasarkan Perjanjian.

            4. PIHAK PERTAMA tidak bertanggung jawab atas kehilangan atau kerusakan data, aplikasi, atau sistem PIHAK KEDUA yang disebabkan oleh Perangkat, konfigurasi, kesalahan pengguna, serangan keamanan yang tidak dapat dicegah secara wajar, atau sebab lain yang berada di luar kendali PIHAK PERTAMA.

            5. Ketentuan mengenai batas maksimum tanggung jawab, apabila ada, diatur lebih lanjut dalam Lampiran atau addendum.

            6. Masing-masing PIHAK wajib melakukan upaya yang wajar untuk mengurangi kerugian yang timbul akibat suatu gangguan atau kejadian.",
                        ],

                        [
                            'judul' => 'KEADAAN MEMAKSA (FORCE MAJEURE)',
                            'text' => "1. Keadaan Memaksa adalah peristiwa di luar kemampuan dan kendali wajar PARA PIHAK yang secara langsung menyebabkan sebagian atau seluruh kewajiban tidak dapat dilaksanakan.

            2. Keadaan Memaksa meliputi namun tidak terbatas pada:

            a. bencana alam;

            b. kebakaran;

            c. banjir;

            d. gempa bumi;

            e. perang;

            f. kerusuhan;

            g. wabah penyakit;

            h. gangguan jaringan berskala besar;

            i. gangguan listrik berskala luas;

            j. kebijakan Pemerintah;

            k. gangguan fasilitas umum; atau

            l. kejadian lain yang sejenis.

            3. PIHAK yang mengalami Force Majeure wajib memberitahukan secara tertulis kepada PIHAK lainnya paling lambat 14 (empat belas) Hari Kalender sejak terjadinya atau diketahuinya keadaan tersebut.

            4. Selama Force Majeure berlangsung, kewajiban yang secara langsung terdampak dapat ditangguhkan sepanjang tidak dapat dilaksanakan akibat keadaan tersebut.

            5. PARA PIHAK wajib melakukan upaya yang wajar untuk mengurangi dampak Force Majeure.

            6. Apabila Force Majeure berlangsung lebih dari 90 (sembilan puluh) Hari Kalender dan tidak terdapat kesepakatan mengenai kelanjutan Perjanjian, masing-masing PIHAK berhak mengakhiri Perjanjian dengan pemberitahuan tertulis.",
                        ],

                        [
                            'judul' => 'KOMUNIKASI / PEMBERITAHUAN',
                            'text' => "1. Setiap pemberitahuan berdasarkan Perjanjian ini harus dibuat secara tertulis dan dapat disampaikan melalui email, surat tercatat, kurir, atau sarana komunikasi resmi lainnya yang disepakati.

            2. Jika kepada PIHAK PERTAMA:

            Nama : [Nama PIHAK PERTAMA]
            Alamat : [Alamat PIHAK PERTAMA]
            Telepon : [Nomor Telepon PIHAK PERTAMA]
            U.p : [Nama Penanggung Jawab]
            Email : [Email PIHAK PERTAMA]

            3. Jika kepada PIHAK KEDUA:

            Nama : [Nama PIHAK KEDUA]
            Alamat : [Alamat PIHAK KEDUA]
            Telepon : [Nomor Telepon PIHAK KEDUA]
            U.p : [Nama Penanggung Jawab]
            Email : [Email PIHAK KEDUA]

            4. Setiap perubahan alamat, nomor telepon, email, atau penanggung jawab wajib diberitahukan secara tertulis kepada PIHAK lainnya.",
                        ],

                        [
                            'judul' => 'PENYELESAIAN SENGKETA DAN KETENTUAN PENUTUP',
                            'text' => "1. Setiap perselisihan yang timbul sehubungan dengan pelaksanaan atau penafsiran Perjanjian ini akan diselesaikan terlebih dahulu secara musyawarah untuk mufakat.

            2. Musyawarah sebagaimana dimaksud pada ayat (1) dilakukan dalam waktu paling lama 30 (tiga puluh) Hari Kalender sejak salah satu PIHAK menyampaikan pemberitahuan tertulis mengenai adanya perselisihan.

            3. Apabila musyawarah tidak mencapai kesepakatan, PARA PIHAK sepakat untuk menyelesaikan perselisihan melalui Pengadilan Negeri [Kota/Kabupaten] di wilayah hukum kedudukan PIHAK PERTAMA.

            4. Selama proses penyelesaian sengketa berlangsung, PARA PIHAK tetap wajib melaksanakan bagian Perjanjian yang tidak menjadi objek sengketa.

            5. Setiap perubahan, penambahan, atau pengurangan terhadap Perjanjian hanya sah apabila dibuat secara tertulis dalam bentuk addendum atau amandemen dan ditandatangani oleh PARA PIHAK.

            6. Apabila terdapat ketentuan dalam Perjanjian yang dinyatakan tidak sah atau tidak dapat dilaksanakan, ketentuan lainnya tetap berlaku dan mengikat PARA PIHAK.

            7. PARA PIHAK menyatakan bahwa:

            a. telah membaca dan memahami seluruh isi Perjanjian;

            b. memiliki kewenangan yang sah untuk menandatangani Perjanjian;

            c. Perjanjian dibuat tanpa adanya paksaan, kekhilafan, atau penipuan dari pihak mana pun.

            8. Perjanjian ini dibuat dalam 2 (dua) rangkap asli yang masing-masing mempunyai kekuatan hukum yang sama dan mulai berlaku sejak tanggal ditandatangani oleh PARA PIHAK.",
                        ],
                    ],

                    'tutup' => "PIHAK PERTAMA
            [Nama Perusahaan PIHAK PERTAMA]


            [Nama Penandatangan PIHAK PERTAMA]
            [Jabatan]


            PIHAK KEDUA
            [Nama Perusahaan PIHAK KEDUA]


            [Nama Penandatangan PIHAK KEDUA]
            [Jabatan]",

                    'lampiran' => [
                        [
                            'judul' => 'LAMPIRAN I PERJANJIAN — SPESIFIKASI LAYANAN COLOCATION',
                            'text' => "NOMOR: [Nomor Perjanjian]",
                            'html' => '<table style="width:100%; border-collapse:collapse;">'
                                . '<tbody>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Nama Pelanggan</td><td style="border:1px solid #000; padding:4px 6px;">[Nama PIHAK KEDUA]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Lokasi Data Center</td><td style="border:1px solid #000; padding:4px 6px;">[Lokasi Data Center]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Nomor Rack</td><td style="border:1px solid #000; padding:4px 6px;">[Nomor Rack]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Ukuran Rack</td><td style="border:1px solid #000; padding:4px 6px;">[Ukuran Rack / U]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Kapasitas Daya</td><td style="border:1px solid #000; padding:4px 6px;">[Kapasitas Daya]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Sumber Listrik</td><td style="border:1px solid #000; padding:4px 6px;">[A/B Feed]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Bandwidth</td><td style="border:1px solid #000; padding:4px 6px;">[Bandwidth]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">IP Address</td><td style="border:1px solid #000; padding:4px 6px;">[Jumlah / Blok IP]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Cross Connect</td><td style="border:1px solid #000; padding:4px 6px;">[Ada / Tidak]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Remote Hands</td><td style="border:1px solid #000; padding:4px 6px;">[Ada / Tidak]</td></tr>'
                                . '</tbody>'
                                . '</table>'
                        ],

                        [
                            'judul' => 'LAMPIRAN II PERJANJIAN — DAFTAR PERANGKAT',
                            'text' => "NOMOR: [Nomor Perjanjian]",
                            'html' => '<table style="width:100%; border-collapse:collapse;">'
                                . '<tbody>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">No.</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Nama Perangkat</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Merk / Tipe</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Serial Number</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Jumlah</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Keterangan</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">1</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Nama Perangkat]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Merk / Tipe]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Serial Number]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Jumlah]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">2</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Nama Perangkat]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Merk / Tipe]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Serial Number]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Jumlah]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td>'
                                . '</tr>'
                                . '</tbody>'
                                . '</table>'
                        ],

                        [
                            'judul' => 'LAMPIRAN III PERJANJIAN — BIAYA LAYANAN',
                            'text' => "NOMOR: [Nomor Perjanjian]",
                            'html' => '<table style="width:100%; border-collapse:collapse;">'
                                . '<tbody>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">No.</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Jenis Layanan</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Satuan</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Biaya</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Keterangan</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">1</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Colocation Rack</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Unit]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Biaya]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">2</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Bandwidth</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Mbps]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Biaya]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">3</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Cross Connect</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Port]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Biaya]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">4</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Remote Hands</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Jam]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Biaya]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td>'
                                . '</tr>'
                                . '</tbody>'
                                . '</table>'
                        ],

                        [
                            'judul' => 'LAMPIRAN IV PERJANJIAN — SERVICE LEVEL AGREEMENT (SLA)',
                            'text' => "NOMOR: [Nomor Perjanjian]\n\nStandar layanan yang diberikan oleh PIHAK PERTAMA kepada PIHAK KEDUA adalah sebagai berikut:",
                            'html' => [
                                '<table style="width:100%; border-collapse:collapse;">'
                                . '<tbody>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">No.</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Parameter</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Target SLA</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Keterangan</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">1</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Availability Data Center</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Persentase]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">2</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Availability Power</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Persentase]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">3</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Network Availability</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Persentase]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">4</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Response Time Gangguan</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Waktu]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td>'
                                . '</tr>'
                                . '</tbody>'
                                . '</table>',

                                '<p style="margin-top:12px;"><strong>Ketentuan Maintenance:</strong></p>'
                                . '<p>Maintenance terjadwal akan diinformasikan kepada PIHAK KEDUA sesuai dengan prosedur dan jangka waktu pemberitahuan yang berlaku.</p>',

                                '<p style="margin-top:12px;"><strong>Matriks Eskalasi:</strong></p>'
                                . '<table style="width:100%; border-collapse:collapse;">'
                                . '<tbody>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">No</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Fault Time</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Escalation Level</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Point of Contact</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">1</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Fault Time]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Level 1</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Helpdesk<br>Phone: [Nomor]<br>Email: [Email]</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">2</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Fault Time]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Level 2</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">NOC<br>Phone: [Nomor]<br>Email: [Email]</td>'
                                . '</tr>'
                                . '<tr>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">3</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">[Fault Time]</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Level 3</td>'
                                . '<td style="border:1px solid #000; padding:4px 6px;">Manager / Direktur<br>Phone: [Nomor]<br>Email: [Email]</td>'
                                . '</tr>'
                                . '</tbody>'
                                . '</table>',
                            ],
                        ],
                    ],
                ],
            ],

            'kontrak-payung' => [
                'title' => 'Perjanjian Kerja Sama Payung',
                'header_data' => [
                    'kopInstansi' => '[Ketik nama perusahaan PIHAK PERTAMA]',
                    'kopAlamat' => '[Ketik alamat perusahaan di sini]',
                    'kopKontrak' => 'Telp: [Nomor telepon] | Email: [Email perusahaan]',
                    'nomorSurat' => 'PK/001/VIII/2026',
                    'perihalSurat' => 'Perjanjian Kerja Sama Payung',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'preamble' => "Pada hari ini, [Hari], tanggal [Tanggal] bulan [Bulan] tahun [Tahun], Para Pihak sepakat untuk membuat dan menandatangani Perjanjian Kerja Sama Payung sebagai dasar pelaksanaan kerja sama:",

                    'paraPihak' => [
                        "1. Nama Perusahaan : [Nama perusahaan PIHAK PERTAMA]\nAlamat : [Alamat lengkap]\nDiwakili oleh : [Nama pejabat]\nJabatan : [Jabatan]\n\nSelanjutnya disebut sebagai “PIHAK PERTAMA”.",

                        "2. Nama Perusahaan : [Nama perusahaan PIHAK KEDUA]\nAlamat : [Alamat lengkap]\nDiwakili oleh : [Nama pejabat]\nJabatan : [Jabatan]\n\nSelanjutnya disebut sebagai “PIHAK KEDUA”.",
                    ],

                    'menimbang' => "a. bahwa Para Pihak memiliki kemampuan dan sumber daya yang dapat dikembangkan melalui kerja sama;\nb. bahwa Para Pihak bermaksud membangun hubungan kerja sama yang dapat digunakan sebagai dasar pelaksanaan berbagai pekerjaan, pengadaan, layanan, atau kegiatan lainnya;\nc. bahwa untuk memberikan kerangka umum bagi pelaksanaan kerja sama tersebut, Para Pihak sepakat membuat Perjanjian Kerja Sama Payung.",

                    'mengingat' => "1. Kitab Undang-Undang Hukum Perdata (KUHPerdata);\n2. Ketentuan peraturan perundang-undangan yang berlaku;\n3. Anggaran Dasar dan ketentuan internal masing-masing pihak;\n4. Kesepakatan Para Pihak.",

                    'isi' => [
                        [
                            'judul' => 'MAKSUD DAN TUJUAN',
                            'text' => "Perjanjian ini dimaksudkan sebagai dasar umum hubungan kerja sama antara Para Pihak dalam melaksanakan pekerjaan, layanan, pengadaan barang atau jasa, pengembangan kegiatan, maupun bentuk kerja sama lainnya yang disepakati."
                        ],
                        [
                            'judul' => 'RUANG LINGKUP KERJA SAMA',
                            'text' => "Ruang lingkup kerja sama meliputi penyediaan barang dan/atau jasa, layanan teknologi informasi, konsultasi, pengembangan sistem, pengadaan perangkat, pemeliharaan, dukungan teknis, serta kegiatan lainnya sesuai kebutuhan dan kesepakatan Para Pihak."
                        ],
                        [
                            'judul' => 'PELAKSANAAN PEKERJAAN',
                            'text' => "Setiap pekerjaan atau kegiatan yang dilaksanakan berdasarkan Perjanjian ini dapat dituangkan lebih lanjut dalam dokumen pelaksanaan seperti Work Order, Surat Pesanan, Statement of Work, Purchase Order, atau dokumen lain yang disepakati."
                        ],
                        [
                            'judul' => 'DOKUMEN TURUNAN',
                            'text' => "Dokumen pelaksanaan sebagaimana dimaksud dalam Perjanjian ini merupakan bagian yang tidak terpisahkan dari Perjanjian sepanjang tidak bertentangan dengan ketentuan Perjanjian Kerja Sama Payung."
                        ],
                        [
                            'judul' => 'HAK DAN KEWAJIBAN PARA PIHAK',
                            'text' => "Para Pihak wajib melaksanakan kewajiban masing-masing sesuai dengan ruang lingkup pekerjaan yang disepakati serta berhak memperoleh hak dan manfaat sesuai dengan ketentuan dalam Perjanjian dan dokumen pelaksanaannya."
                        ],
                        [
                            'judul' => 'BIAYA DAN PEMBAYARAN',
                            'text' => "Nilai pekerjaan, harga barang dan/atau jasa, mekanisme pembayaran, pajak, serta biaya lainnya akan ditentukan dalam dokumen pelaksanaan masing-masing pekerjaan."
                        ],
                        [
                            'judul' => 'KERAHASIAAN',
                            'text' => "Para Pihak wajib menjaga kerahasiaan seluruh informasi, data, dokumen, spesifikasi, harga, dan informasi lainnya yang diperoleh dalam pelaksanaan kerja sama, kecuali diwajibkan berdasarkan hukum."
                        ],
                        [
                            'judul' => 'JANGKA WAKTU',
                            'text' => "Perjanjian ini berlaku selama [jangka waktu] sejak tanggal ditandatangani dan dapat diperpanjang berdasarkan kesepakatan tertulis Para Pihak."
                        ],
                        [
                            'judul' => 'PENGAKHIRAN',
                            'text' => "Perjanjian dapat diakhiri berdasarkan kesepakatan Para Pihak atau berdasarkan alasan lain yang diperbolehkan berdasarkan Perjanjian dan ketentuan peraturan perundang-undangan."
                        ],
                        [
                            'judul' => 'KEADAAN KAHAR',
                            'text' => "Para Pihak tidak bertanggung jawab atas keterlambatan atau kegagalan pelaksanaan kewajiban yang disebabkan oleh keadaan di luar kemampuan dan kendali wajar Para Pihak, sepanjang dapat dibuktikan dan diberitahukan kepada pihak lainnya."
                        ],
                        [
                            'judul' => 'PENYELESAIAN PERSELISIHAN',
                            'text' => "Setiap perselisihan akan diselesaikan terlebih dahulu melalui musyawarah. Apabila tidak tercapai penyelesaian, Para Pihak dapat menempuh mekanisme penyelesaian sesuai ketentuan hukum yang berlaku."
                        ],
                        [
                            'judul' => 'PERUBAHAN PERJANJIAN',
                            'text' => "Setiap perubahan atau penambahan terhadap Perjanjian ini hanya sah apabila dibuat secara tertulis dan disetujui serta ditandatangani oleh Para Pihak."
                        ],
                        [
                            'judul' => 'PENUTUP',
                            'text' => "Perjanjian ini menjadi dasar umum hubungan kerja sama dan tidak dengan sendirinya mewajibkan salah satu pihak untuk memberikan atau menerima pekerjaan tertentu sebelum adanya dokumen pelaksanaan yang disepakati."
                        ],
                    ],

                    'tutup' => "Demikian Perjanjian Kerja Sama Payung ini dibuat dan ditandatangani dengan itikad baik untuk dipergunakan sebagaimana mestinya.",

                    'lampiran' => [
                        [
                            'judul' => 'LAMPIRAN — RUANG LINGKUP KERJA SAMA',
                            'text' => "1. Bidang Kerja Sama : [Bidang]\n2. Jenis Barang/Jasa : [Jenis barang/jasa]\n3. Wilayah Kerja : [Wilayah]\n4. Mekanisme Pelaksanaan : [Mekanisme]\n5. Ketentuan Khusus : [Ketentuan tambahan]"
                        ],
                    ],
                ],
            ],

            'kontrak-kemitraan' => [
                'title' => 'Perjanjian Kemitraan',
                'header_data' => [
                    'kopInstansi' => '[Nama Perusahaan PIHAK PERTAMA]',
                    'kopAlamat' => '[Alamat perusahaan PIHAK PERTAMA]',
                    'kopKontrak' => 'Telp: [Nomor Telepon] | Email: [Email Perusahaan]',
                    'nomorSurat' => '[Nomor Perjanjian]',
                    'perihalSurat' => 'Perjanjian Kerjasama Jual Kembali Jasa Layanan Akses Internet',
                    'sifatSurat' => 'Penting',
                ],

                'body_content' => [

                    'preamble' => "Perjanjian Kerjasama tentang Jual Kembali Jasa Layanan Akses Internet (selanjutnya disebut “Perjanjian”), dibuat pada [Tanggal Perjanjian], bertempat di [Kota/Kabupaten], oleh dan antara:",

                    'paraPihak' => [
                        "1. [Nama Perusahaan PIHAK PERTAMA], suatu [Bentuk Badan Usaha], yang didirikan berdasarkan Hukum Negara Republik Indonesia, berkedudukan di [Alamat dan Kode Pos]. Berdasarkan Akta Berita Acara RUPS Tahunan Perseroan Terbatas “[Nama Perusahaan PIHAK PERTAMA]”, Nomor [Nomor Akta], [Tanggal Akta], dibuat dihadapan [Nama Notaris], Notaris di [Kota/Kabupaten]. Dalam hal ini diwakili oleh [Nama Pejabat PIHAK PERTAMA], selaku [Jabatan], sah bertindak untuk dan atas nama [Nama Perusahaan PIHAK PERTAMA], selanjutnya disebut sebagai “PIHAK PERTAMA”.",

                        "d e n g a n",

                        "2. [Nama PIHAK KEDUA], suatu [Perorangan/Badan Usaha/Badan Hukum], beralamat di [Alamat Lengkap dengan Kecamatan dan Kota/Kabupaten]. Dalam hal ini bertindak atas nama sendiri, selanjutnya dalam Perjanjian ini disebut sebagai “PIHAK KEDUA”.",

                        "PIHAK PERTAMA dan PIHAK KEDUA secara sendiri-sendiri disebut “PIHAK” dan secara bersama-sama disebut sebagai “PARA PIHAK”.",

                        "PARA PIHAK dengan ini menerangkan terlebih dahulu hal-hal sebagai berikut:",

                        "1. Bahwa PIHAK PERTAMA merupakan penyelenggara jasa yang memiliki izin penyelenggaraan Layanan Akses Internet dari Kementerian Komunikasi dan Informatika Republik Indonesia Nomor: [Nomor Izin] tertanggal [Tanggal Izin] Tentang Perubahan Nomor: [Nomor Perubahan] Tahun [Tahun Izin] Izin Penyelenggaraan Jasa Akses Internet (Internet Service Provider) [Nama Perusahaan PIHAK PERTAMA].",

                        "2. Bahwa PIHAK KEDUA merupakan suatu [Perseorangan/Badan Usaha/Badan Hukum].",

                        "3. Bahwa PIHAK PERTAMA sepakat untuk menjual jasa Layanan Akses Internet kepada PIHAK KEDUA, dan untuk selanjutnya PIHAK KEDUA sepakat untuk menjual kembali Jasa Layanan Akses Internet kepada Pelanggan/end user.",

                        "Berdasarkan hal-hal tersebut di atas, maka PARA PIHAK sepakat untuk membuat Perjanjian Kerjasama Jual Kembali Jasa Layanan Akses Internet dengan syarat-syarat dan ketentuan-ketentuan sebagai berikut:",
                    ],

                    'isi' => [

                        [
                            'judul' => 'DEFINISI',
                            'text' => "Dalam Perjanjian ini yang dimaksud dengan:\n\n1. Perjanjian adalah Perjanjian Kerja Sama Jual Kembali Jasa Layanan Akses Internet beserta seluruh lampiran, perubahan, addendum, dan dokumen lain yang merupakan satu kesatuan yang tidak terpisahkan dengan Perjanjian ini.\n\n2. Layanan adalah jasa akses internet yang diselenggarakan oleh PIHAK PERTAMA berdasarkan izin penyelenggaraan yang berlaku, termasuk seluruh fasilitas, jaringan, sistem, aplikasi, perangkat, dan layanan pendukung yang berkaitan.\n\n3. PIHAK KEDUA (Reseller) adalah pihak yang memperoleh hak dari PIHAK PERTAMA untuk memasarkan, menjual kembali, dan mengelola Layanan kepada Pelanggan sesuai dengan ketentuan Perjanjian ini.\n\n4. Pelanggan adalah orang perseorangan, badan usaha, badan hukum, instansi pemerintah, atau pihak lainnya yang memperoleh Layanan melalui PIHAK KEDUA.\n\n5. Biaya adalah seluruh kewajiban pembayaran yang menjadi tanggung jawab PIHAK KEDUA kepada PIHAK PERTAMA berdasarkan Perjanjian ini, termasuk namun tidak terbatas pada biaya instalasi, biaya layanan, biaya administrasi, denda, penalti, dan biaya lainnya yang disepakati.\n\n6. Perangkat adalah seluruh perangkat keras, perangkat lunak, jaringan, sistem pendukung, maupun perlengkapan lainnya yang digunakan dalam penyelenggaraan Layanan, baik yang dimiliki maupun yang dipinjamkan oleh PIHAK PERTAMA.\n\n7. Data Pelanggan adalah seluruh informasi mengenai identitas, administrasi, teknis, maupun data lain yang berkaitan dengan Pelanggan yang diperoleh dalam pelaksanaan Perjanjian ini.\n\n8. Data Pemakaian adalah seluruh data mengenai penggunaan Layanan oleh Pelanggan, termasuk namun tidak terbatas pada data trafik, log akses, penggunaan bandwidth, statistik penggunaan, dan data teknis lainnya.\n\n9. Hari Kerja adalah hari Senin sampai dengan Jumat, selain hari libur nasional dan hari yang ditetapkan Pemerintah sebagai hari libur.\n\n10. Keadaan Memaksa (Force Majeure) adalah setiap keadaan di luar kemampuan dan kendali PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, sebagaimana diatur lebih lanjut dalam Pasal mengenai Force Majeure.\n\n11. Dokumen Layanan adalah seluruh formulir, Service Order Form (SOF), formulir perubahan layanan, berita acara aktivasi, berita acara serah terima, maupun dokumen operasional lainnya yang diterbitkan oleh PIHAK PERTAMA sebagai bagian dari pelaksanaan Perjanjian ini.\n\n12. Penalti adalah kewajiban pembayaran yang dikenakan kepada PIHAK KEDUA akibat pelanggaran terhadap ketentuan Perjanjian, termasuk namun tidak terbatas pada terminasi dini, pembatalan layanan, downgrade, relokasi, maupun pelanggaran lainnya sebagaimana diatur dalam Perjanjian ini.\n\n13. Aplikasi Reseller adalah sistem aplikasi yang disediakan oleh PIHAK PERTAMA untuk keperluan registrasi pelanggan, pelaporan penjualan, monitoring layanan, administrasi, maupun fungsi operasional lainnya."
                        ],

                        [
                            'judul' => 'RUANG LINGKUP',
                            'text' => "1. Ruang lingkup dalam Perjanjian ini adalah kerjasama Jual Kembali Jasa Layanan Akses Internet milik PIHAK PERTAMA oleh PIHAK KEDUA;\n\n2. Lokasi/wilayah serta Konfigurasi Teknis Jasa Layanan Akses Internet yang akan dijual kembali oleh PIHAK KEDUA kepada Pelanggan sebagaimana tercantum pada Lampiran I Perjanjian ini;\n\n3. Paket Jasa Layanan Akses Internet yang akan dijual kembali oleh PIHAK KEDUA kepada Pelanggan sebagaimana dimaksud dalam ayat (1) dan (2) tercantum pada Lampiran II Perjanjian ini."
                        ],

                        [
                            'judul' => 'JANGKA WAKTU',
                            'text' => "1. Perjanjian ini berlaku sejak tanggal ditandatangani oleh PARA PIHAK dan tetap berlaku untuk jangka waktu [Jangka Waktu], sebagaimana tercantum dalam Lampiran I, kecuali diakhiri lebih dahulu sesuai ketentuan dalam Perjanjian ini.\n\n2. Setelah jangka waktu sebagaimana dimaksud pada ayat (1) berakhir, Perjanjian ini secara otomatis diperpanjang untuk jangka waktu [Jangka Waktu Perpanjangan] berikutnya dengan syarat:\n\na. tidak terdapat wanprestasi yang belum diselesaikan oleh salah satu pihak; dan\n\nb. tidak ada pemberitahuan tertulis mengenai pengakhiran Perjanjian dari Pihak Kedua paling lambat [Jumlah Hari] Hari Kalender sebelum tanggal berakhirnya Perjanjian."
                        ],

                        [
                            'judul' => 'HAK DAN KEWAJIBAN PARA PIHAK',
                            'text' => "1. Hak PIHAK PERTAMA\n\nPIHAK PERTAMA berhak:\n\na. menerima pembayaran dari PIHAK KEDUA sesuai dengan ketentuan Perjanjian;\n\nb. melakukan verifikasi, audit, dan pengawasan terhadap pelaksanaan kerja sama, termasuk data pelanggan, data penjualan, penggunaan jaringan, dan kepatuhan PIHAK KEDUA terhadap Perjanjian;\n\nc. melakukan isolir, pembatasan, penghentian sementara, atau pemutusan layanan sesuai ketentuan Perjanjian apabila PIHAK KEDUA melakukan wanprestasi atau pelanggaran;\n\nd. mengubah, mengembangkan, menambah, atau mengurangi jenis layanan, teknologi, sistem, aplikasi, maupun kebijakan operasional sepanjang tidak menghilangkan hak-hak PIHAK KEDUA yang telah timbul sebelum perubahan tersebut berlaku;\n\ne. menolak permohonan aktivasi, relokasi, perubahan layanan, atau penambahan pelanggan yang tidak memenuhi ketentuan teknis, administratif, atau ketentuan hukum yang berlaku;\n\nf. menggunakan data operasional yang diperoleh dari pelaksanaan Perjanjian untuk kepentingan operasional, pemenuhan kewajiban hukum, audit, peningkatan kualitas layanan, dan pengembangan usaha dengan tetap memperhatikan ketentuan peraturan perundang-undangan.\n\n2. Kewajiban PIHAK PERTAMA\n\nPIHAK PERTAMA berkewajiban:\n\na. menyediakan Layanan sesuai spesifikasi layanan yang disepakati;\n\nb. memberikan dukungan teknis, aktivasi layanan, pemeliharaan jaringan, dan penanganan gangguan sesuai ketentuan SLA;\n\nc. menyediakan sistem administrasi dan pelaporan yang diperlukan untuk pelaksanaan kerja sama;\n\nd. menjaga kerahasiaan data PIHAK KEDUA dan Pelanggan sesuai ketentuan Perjanjian dan peraturan perundang-undangan;\n\ne. memberikan informasi kepada PIHAK KEDUA mengenai perubahan kebijakan yang berdampak langsung terhadap pelaksanaan kerja sama.\n\n3. Hak PIHAK KEDUA\n\nPIHAK KEDUA berhak:\n\na. memperoleh Layanan sesuai spesifikasi layanan yang disepakati;\n\nb. memperoleh dukungan teknis, pelatihan, dan pendampingan operasional sesuai kebutuhan pelaksanaan kerja sama;\n\nc. memasarkan dan menjual kembali Layanan kepada Pelanggan sesuai ketentuan Perjanjian;\n\nd. memperoleh akses terhadap sistem administrasi, pelaporan, dan layanan pendukung yang disediakan oleh PIHAK PERTAMA.\n\n4. Kewajiban PIHAK KEDUA\n\nPIHAK KEDUA berkewajiban:\n\na. memasarkan dan menjual Layanan sesuai ketentuan Perjanjian, standar operasional, dan kebijakan PIHAK PERTAMA;\n\nb. membayar seluruh kewajiban finansial kepada PIHAK PERTAMA tepat waktu;\n\nc. menjaga kualitas pelayanan kepada Pelanggan dan menjadi pihak yang bertanggung jawab atas hubungan komersial dengan Pelanggan;\n\nd. melaporkan seluruh data pelanggan, penjualan, perubahan layanan, dan informasi lain pada sistem PIHAK PERTAMA yang dipersyaratkan secara lengkap, benar, dan tepat waktu;\n\ne. menjaga keamanan jaringan, perangkat, akun, kata sandi, dan akses yang diberikan oleh PIHAK PERTAMA;\n\nf. tidak menyalahgunakan jaringan, menyembunyikan data pelanggan, melakukan manipulasi data, menggunakan jaringan untuk kegiatan yang melanggar hukum, atau melakukan tindakan lain yang dapat merugikan PIHAK PERTAMA;\n\ng. mematuhi seluruh ketentuan teknis, operasional, keamanan informasi, serta peraturan perundang-undangan yang berlaku.\n\n5. Ketentuan Umum\n\na. Masing-masing PIHAK wajib melaksanakan hak dan kewajibannya dengan itikad baik, profesional, dan sesuai dengan prinsip kehati-hatian.\n\nb. Hak dan kewajiban yang diatur dalam Pasal ini tidak mengurangi hak dan kewajiban lain yang diatur dalam Perjanjian ini maupun peraturan perundang-undangan yang berlaku."
                        ],

                        [
                            'judul' => 'BIAYA DAN CARA PEMBAYARAN',
                            'text' => "1. Biaya yang wajib dibayarkan oleh PIHAK KEDUA kepada PIHAK PERTAMA meliputi biaya Layanan Akses Internet dan biaya administrasi bulanan;\n\n2. Biaya Layanan dan Instalasi jasa Layanan Akses Internet sebagaimana diuraikan dalam Lampiran I;\n\n3. Biaya administrasi bulanan sebesar [Persentase] sesuai dengan pendapatan kotor PIHAK KEDUA;\n\n4. Biaya sebagaimana dimaksud dalam ayat (3) sudah termasuk Biaya Hak Penyelenggaraan (BHP), Universal Service Obligation (USO), Pajak Pertambahan Nilai Dalam Negeri (PPnDn) dan Pajak Penghasilan (PPh) sesuai ketentuan perundang-undangan yang berlaku;\n\n5. Biaya jasa Layanan Akses Internet dan biaya administrasi sebagaimana tersebut dalam ayat (2) dan ayat (3) wajib dibayarkan oleh PIHAK KEDUA kepada PIHAK PERTAMA setiap bulan. Tagihan wajib dibayarkan maksimal [Jumlah Hari] Hari Kerja sejak tanggal tagihan (invoice) dikirimkan;\n\n6. Seluruh pembayaran dilakukan melalui transfer ke rekening yang ditetapkan oleh PIHAK PERTAMA."
                        ],

                        [
                            'judul' => 'TANGGUNGJAWAB PENAGIHAN',
                            'text' => "1. Penagihan biaya Layanan Akses Internet dari Pelanggan dilakukan oleh PIHAK KEDUA;\n\n2. PIHAK KEDUA bertanggungjawab atas upaya-upaya penagihan kepada Pelanggan;\n\n3. Penagihan kepada Pelanggan menjadi dasar bagi PARA PIHAK untuk melakukan perhitungan untuk biaya administrasi."
                        ],

                        [
                            'judul' => 'PENGADUAN PELANGGAN',
                            'text' => "1. PIHAK KEDUA wajib untuk menyediakan form pengaduan Pelanggan;\n\n2. Mekanisme dan Standar Operasional Prosedur (S.O.P) untuk pengaduan Pelanggan tercantum dalam Lampiran III."
                        ],

                        [
                            'judul' => 'ISOLIR',
                            'text' => "1. Apabila PIHAK KEDUA melalaikan kewajiban sebagaimana dimaksud dalam Pasal 5 ayat (5), maka PIHAK PERTAMA akan memberikan Surat Peringatan kepada PIHAK KEDUA sebanyak 3 (tiga) kali dengan jangka waktu masing-masing Surat Peringatan selama 7 (tujuh) hari kerja. Apabila sampai Surat Peringatan ke-2, PIHAK KEDUA belum melakukan pembayaran, maka PIHAK PERTAMA berhak melakukan Isolir terhadap Layanan Akses Internet;\n\n2. PIHAK PERTAMA akan membuka Isolir dalam waktu selambat-lambatnya 3 (tiga) Hari Kerja setelah PIHAK KEDUA membayar seluruh kewajibannya;\n\n3. Apabila dalam jangka waktu 7 (tujuh) Hari Kerja sejak terjadinya Isolir sebagaimana yang dimaksud dalam ayat (1) Pasal ini, PIHAK KEDUA belum memenuhi kewajibannya, maka PIHAK PERTAMA akan melakukan Pemutusan Layanan Permanen;\n\n4. Dalam hal terjadi Pemutusan Layanan Permanen sebagaimana dimaksud dalam ayat (3), maka PIHAK KEDUA tetap wajib membayar segala kewajibannya yang belum terlaksana kepada PIHAK PERTAMA selambat-lambatnya 7 (tujuh) Hari Kerja sejak Pemutusan Layanan Permanen dilakukan oleh PIHAK PERTAMA."
                        ],

                        [
                            'judul' => 'DATA, KERAHASIAAN, DAN PERLINDUNGAN DATA',
                            'text' => "1. PARA PIHAK wajib menjaga kerahasiaan seluruh data, informasi, dokumen, sistem, maupun informasi lain yang diperoleh sehubungan dengan pelaksanaan Perjanjian ini dan tidak mengungkapkannya kepada pihak lain tanpa persetujuan tertulis dari pihak yang berhak, kecuali diwajibkan berdasarkan ketentuan peraturan perundang-undangan atau perintah instansi yang berwenang.\n\n2. Seluruh data operasional jaringan, data penggunaan layanan, konfigurasi sistem, dokumentasi teknis, serta data lain yang dihasilkan atau tersimpan dalam sistem milik PIHAK PERTAMA merupakan milik PIHAK PERTAMA.\n\n3. PIHAK PERTAMA berhak menggunakan, mengolah, menyimpan, dan mengakses data sebagaimana dimaksud pada ayat (2) untuk kepentingan operasional, pemeliharaan jaringan, peningkatan kualitas layanan, audit, keamanan sistem, pemenuhan kewajiban hukum, serta tujuan lain yang berkaitan dengan penyelenggaraan Layanan, dengan tetap memperhatikan ketentuan peraturan perundang-undangan.\n\n4. PIHAK KEDUA tidak diperkenankan mengakses, menyalin, mengubah, memindahtangankan, memperjualbelikan, atau menggunakan data maupun informasi milik PIHAK PERTAMA di luar pelaksanaan Perjanjian tanpa persetujuan tertulis dari PIHAK PERTAMA.\n\n5. Kewajiban menjaga kerahasiaan sebagaimana dimaksud dalam Pasal ini tetap berlaku selama Perjanjian berlangsung dan selama 5 (lima) tahun setelah Perjanjian berakhir atau jangka waktu lain yang diwajibkan berdasarkan ketentuan peraturan perundang-undangan."
                        ],

                        [
                            'judul' => 'PERANGKAT DAN INFRASTRUKTUR',
                            'text' => "1. Seluruh perangkat, jaringan, infrastruktur, aplikasi, sistem, dan fasilitas pendukung yang disediakan oleh PIHAK PERTAMA untuk penyelenggaraan Layanan tetap menjadi milik PIHAK PERTAMA, kecuali disepakati lain secara tertulis.\n\n2. PIHAK KEDUA wajib menggunakan, menjaga, dan memelihara seluruh perangkat yang dikuasainya sesuai dengan petunjuk penggunaan serta tidak mengalihkan, memindahtangankan, menyewakan, menjaminkan, memodifikasi, atau menggunakannya untuk kepentingan selain pelaksanaan Perjanjian tanpa persetujuan tertulis dari PIHAK PERTAMA.\n\n3. PIHAK KEDUA bertanggung jawab atas kehilangan, kerusakan, atau penyalahgunaan perangkat yang berada dalam penguasaannya, kecuali apabila disebabkan oleh cacat bawaan perangkat atau kesalahan PIHAK PERTAMA.\n\n4. PIHAK PERTAMA berhak melakukan pemeriksaan, pemeliharaan, penggantian, peningkatan, relokasi, atau penarikan perangkat apabila diperlukan untuk kepentingan operasional, keamanan jaringan, pemeliharaan, atau berakhirnya Perjanjian.\n\n5. Dalam hal Perjanjian berakhir karena sebab apa pun, PIHAK KEDUA wajib mengembalikan seluruh perangkat milik PIHAK PERTAMA dalam kondisi baik sesuai pemakaian yang wajar paling lambat 14 (empat belas) Hari Kalender sejak tanggal berakhirnya Perjanjian, kecuali disepakati lain secara tertulis.\n\n6. Apabila PIHAK KEDUA tidak mengembalikan perangkat sebagaimana dimaksud pada ayat (5), PIHAK PERTAMA berhak melakukan penagihan atas nilai penggantian perangkat dan/atau menempuh upaya hukum sesuai dengan ketentuan peraturan perundang-undangan dan Perjanjian ini.\n\n7. Ketentuan mengenai spesifikasi perangkat, instalasi, pemeliharaan, penggantian, dan pengembalian perangkat diatur lebih lanjut dalam Lampiran yang merupakan bagian tidak terpisahkan dari Perjanjian ini."
                        ],

                        [
                            'judul' => 'MEREK DAN HAK KEKAYAAN INTELEKTUAL',
                            'text' => "1. Seluruh hak atas merek, logo, nama dagang, hak cipta, perangkat lunak, desain, dokumentasi, sistem, aplikasi, jaringan, serta Hak Kekayaan Intelektual lainnya yang digunakan dalam penyelenggaraan Layanan merupakan milik PIHAK PERTAMA atau pihak lain yang memberikan hak penggunaannya kepada PIHAK PERTAMA.\n\n2. PIHAK PERTAMA memberikan kepada PIHAK KEDUA hak yang terbatas, tidak eksklusif, tidak dapat dialihkan, dan tidak dapat disublisensikan untuk menggunakan merek, logo, dan materi promosi milik PIHAK PERTAMA semata-mata dalam rangka pelaksanaan Perjanjian ini.\n\n3. PIHAK KEDUA wajib menggunakan merek, logo, dan identitas perusahaan milik PIHAK PERTAMA sesuai dengan pedoman, standar, dan ketentuan yang ditetapkan oleh PIHAK PERTAMA serta tidak melakukan perubahan, penghapusan, atau penggunaan yang dapat merugikan nama baik PIHAK PERTAMA.\n\n4. PIHAK KEDUA dilarang mendaftarkan, menggunakan, meniru, atau mengklaim kepemilikan atas merek, logo, nama dagang, nama domain, desain, atau Hak Kekayaan Intelektual lainnya yang mempunyai persamaan atau kemiripan dengan milik PIHAK PERTAMA tanpa persetujuan tertulis dari PIHAK PERTAMA.\n\n5. Berakhirnya Perjanjian ini mengakibatkan seluruh hak penggunaan merek, logo, dan Hak Kekayaan Intelektual yang diberikan kepada PIHAK KEDUA berakhir secara otomatis. PIHAK KEDUA wajib segera menghentikan seluruh penggunaan serta menghapus atau mengembalikan seluruh materi yang memuat identitas PIHAK PERTAMA, kecuali diwajibkan lain oleh ketentuan peraturan perundang-undangan.\n\n6. Pelanggaran terhadap ketentuan dalam Pasal ini memberikan hak kepada PIHAK PERTAMA untuk mencabut hak penggunaan merek, menghentikan kerja sama, menuntut ganti rugi, dan/atau menempuh upaya hukum sesuai dengan ketentuan Perjanjian dan peraturan perundang-undangan yang berlaku."
                        ],

                        [
                            'judul' => 'WANPRESTASI DAN SANKSI',
                            'text' => "1. PIHAK KEDUA dinyatakan melakukan wanprestasi apabila:\n\na. tidak memenuhi kewajibannya berdasarkan Perjanjian;\n\nb. terlambat melakukan pembayaran sesuai dengan ketentuan Perjanjian;\n\nc. memberikan data atau informasi yang tidak benar;\n\nd. melanggar ketentuan mengenai penggunaan Layanan, perangkat, data, atau Hak Kekayaan Intelektual;\n\ne. menggunakan Layanan untuk kegiatan yang melanggar hukum; atau\n\nf. melakukan tindakan lain yang mengakibatkan kerugian bagi PIHAK PERTAMA.\n\n2. Dalam hal PIHAK KEDUA melakukan wanprestasi, PIHAK PERTAMA berhak memberikan sanksi secara bertahap sesuai dengan tingkat pelanggaran berupa:\n\na. teguran tertulis;\n\nb. penangguhan aktivasi atau layanan tertentu;\n\nc. pembatasan atau isolir Layanan;\n\nd. pengenaan denda atau penalti;\n\ne. pemutusan sebagian atau seluruh Layanan; dan/atau\n\nf. pengakhiran Perjanjian.\n\n3. PIHAK PERTAMA berhak menjatuhkan salah satu atau beberapa sanksi sebagaimana dimaksud pada ayat (2) tanpa harus menerapkannya secara berurutan, apabila menurut penilaian PIHAK PERTAMA pelanggaran yang dilakukan bersifat material atau berpotensi menimbulkan kerugian bagi PIHAK PERTAMA, Pelanggan, atau pihak lain.\n\n4. Dalam hal PIHAK KEDUA mengakhiri Perjanjian sebelum berakhirnya jangka waktu yang disepakati atau menyebabkan Perjanjian diakhiri karena wanprestasi PIHAK KEDUA, maka PIHAK KEDUA wajib membayar penalti sesuai dengan ketentuan yang tercantum dalam Lampiran atau addendum yang merupakan bagian tidak terpisahkan dari Perjanjian ini.\n\n5. Pengenaan sanksi, denda, atau penalti tidak menghapus kewajiban PIHAK KEDUA untuk:\n\na. melunasi seluruh kewajiban pembayaran;\n\nb. mengembalikan perangkat milik PIHAK PERTAMA;\n\nc. mengganti kerugian yang timbul akibat pelanggaran; dan\n\nd. memenuhi kewajiban lain berdasarkan Perjanjian.\n\n6. PIHAK PERTAMA berhak menagih seluruh kerugian yang timbul akibat wanprestasi PIHAK KEDUA, termasuk biaya penagihan, biaya pemulihan jaringan, biaya hukum, dan kerugian lain yang dapat dibuktikan sesuai dengan ketentuan peraturan perundang-undangan.\n\n7. Apabila PIHAK KEDUA tidak memperbaiki wanprestasi dalam jangka waktu yang ditetapkan oleh PIHAK PERTAMA atau melakukan pelanggaran yang bersifat material, PIHAK PERTAMA berhak mengakhiri Perjanjian secara sepihak melalui pemberitahuan tertulis tanpa mengurangi hak PIHAK PERTAMA untuk menuntut pemenuhan kewajiban, ganti rugi, maupun upaya hukum lainnya sesuai dengan ketentuan Perjanjian dan peraturan perundang-undangan yang berlaku."
                        ],

                        [
                            'judul' => 'PENGAKHIRAN PERJANJIAN',
                            'text' => "1. Perjanjian ini berakhir apabila:\n\na. jangka waktu Perjanjian berakhir dan tidak diperpanjang;\n\nb. PARA PIHAK sepakat secara tertulis untuk mengakhiri Perjanjian;\n\nc. diakhiri oleh salah satu pihak sesuai dengan ketentuan Perjanjian ini; atau\n\nd. terjadi keadaan lain yang berdasarkan ketentuan peraturan perundang-undangan mengakibatkan Perjanjian tidak dapat dilaksanakan.\n\n2. PIHAK PERTAMA berhak mengakhiri Perjanjian secara sepihak dengan pemberitahuan tertulis apabila PIHAK KEDUA:\n\na. melakukan wanprestasi yang tidak diperbaiki dalam jangka waktu yang ditentukan;\n\nb. melakukan pelanggaran yang bersifat material;\n\nc. dinyatakan pailit, dibubarkan, atau menghentikan kegiatan usahanya;\n\nd. menggunakan Layanan untuk kegiatan yang melanggar hukum atau merugikan PIHAK PERTAMA; atau\n\ne. tidak lagi memenuhi persyaratan sebagai mitra berdasarkan ketentuan yang ditetapkan oleh PIHAK PERTAMA.\n\n3. Pengakhiran Perjanjian tidak menghapus hak dan kewajiban PARA PIHAK yang telah timbul sebelum tanggal efektif pengakhiran, termasuk kewajiban pembayaran, denda, penalti, ganti rugi, pengembalian perangkat, serta kewajiban lainnya berdasarkan Perjanjian.\n\n4. Sejak tanggal efektif pengakhiran Perjanjian:\n\na. PIHAK KEDUA wajib menghentikan penggunaan merek, logo, sistem, aplikasi, dan fasilitas milik PIHAK PERTAMA;\n\nb. PIHAK KEDUA wajib mengembalikan seluruh perangkat, dokumen, data, dan aset milik PIHAK PERTAMA yang berada dalam penguasaannya; dan\n\nc. PIHAK PERTAMA berhak menghentikan akses PIHAK KEDUA terhadap sistem dan Layanan.\n\n5. PARA PIHAK sepakat dan setuju untuk mengesampingkan berlakunya Pasal 1266 KUHPerdata, sehingga Pemutusan Perjanjian ini dapat dilakukan oleh PARA PIHAK tanpa terlebih dahulu menunggu Putusan Pengadilan."
                        ],

                        [
                            'judul' => 'PERTANGGUNGJAWABAN TERHADAP PIHAK KETIGA',
                            'text' => "1. Dalam hal terjadi Pemutusan Layanan Permanen sebagaimana dimaksud dalam Pasal 13 ayat (2), PIHAK PERTAMA tidak bertanggung jawab atas hubungan hukum, hak, kewajiban, kerugian, maupun tuntutan antara PIHAK KEDUA dan Pelanggan.\n\n2. Untuk menjaga keberlangsungan Layanan Akses Internet kepada Pelanggan, PIHAK PERTAMA berhak menawarkan layanan secara langsung kepada Pelanggan atau memfasilitasi pengalihan layanan kepada Penyelenggara Jasa Layanan Akses Internet lainnya, sepanjang memungkinkan secara teknis dan sesuai dengan ketentuan yang berlaku.\n\n3. PIHAK KEDUA menjamin bahwa Pelanggan telah memperoleh informasi mengenai kemungkinan pengalihan layanan sebagaimana dimaksud pada ayat (2), serta membebaskan dan melepaskan PIHAK PERTAMA dari setiap tuntutan, gugatan, atau klaim yang timbul sehubungan dengan berakhirnya Perjanjian ini atau berakhirnya hubungan hukum antara PIHAK KEDUA dan Pelanggan.\n\n4. Pelaksanaan ketentuan dalam Pasal ini tidak menghapus kewajiban PARA PIHAK yang masih harus diselesaikan berdasarkan Perjanjian ini."
                        ],

                        [
                            'judul' => 'EVALUASI PELAKSANAAN PEKERJAAN',
                            'text' => "1. PARA PIHAK sepakat melakukan evaluasi atas pelaksanaan Perjanjian ini setiap 6 (enam) bulan sejak Perjanjian mulai berlaku.\n\n2. Evaluasi dilakukan melalui Forum Konsultasi yang dihadiri oleh wakil PARA PIHAK yang berwenang.\n\na. evaluasi meliputi antara lain:\n\nb. pelaksanaan operasional layanan;\n\nc. pemasaran dan penjualan;\n\nd. pengaduan Pelanggan;\n\ne. kendala teknis; dan\n\nf. hal lain yang disepakati PARA PIHAK.\n\n3. Hasil evaluasi dituangkan dalam berita acara atau dokumen tertulis yang menjadi dasar bagi PARA PIHAK untuk melakukan perbaikan pelaksanaan Perjanjian atau perubahan Perjanjian berdasarkan kesepakatan PARA PIHAK.\n\n4. Dalam hal hasil evaluasi menunjukkan adanya kondisi yang dapat menjadi dasar pengakhiran Perjanjian, pelaksanaannya tetap mengacu pada ketentuan mengenai pemutusan Perjanjian sebagaimana diatur dalam Perjanjian ini."
                        ],

                        [
                            'judul' => 'KEADAAN MEMAKSA (FORCE MAJEURE)',
                            'text' => "1. Keadaan Memaksa (Force Majeure) adalah setiap peristiwa di luar kendali dan kemampuan wajar PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, termasuk namun tidak terbatas pada bencana alam, kebakaran, perang, kerusuhan, wabah penyakit, pemogokan, gangguan jaringan berskala besar, kegagalan sistem di luar kendali PARA PIHAK, kebijakan Pemerintah, atau peristiwa lain yang sejenis.\n\n2. PIHAK yang mengalami Keadaan Memaksa wajib memberitahukan secara tertulis kepada pihak lainnya paling lambat 14 (empat belas) Hari Kalender sejak diketahui atau seharusnya diketahui terjadinya Keadaan Memaksa, disertai penjelasan mengenai dampak terhadap pelaksanaan Perjanjian.\n\n3. Selama Keadaan Memaksa berlangsung, kewajiban PARA PIHAK yang terdampak ditangguhkan sepanjang tidak dapat dilaksanakan akibat Keadaan Memaksa. Penangguhan tersebut tidak menghapus kewajiban yang telah timbul sebelum terjadinya Keadaan Memaksa.\n\n4. PARA PIHAK wajib melakukan upaya yang wajar untuk mengurangi dampak Keadaan Memaksa dan melanjutkan pelaksanaan Perjanjian segera setelah keadaan tersebut berakhir.\n\n5. Apabila Keadaan Memaksa berlangsung lebih dari 90 (sembilan puluh) Hari Kalender secara berturut-turut dan PARA PIHAK tidak mencapai kesepakatan mengenai kelanjutan Perjanjian, masing-masing pihak berhak mengakhiri Perjanjian dengan pemberitahuan tertulis tanpa dikenakan penalti, dengan tetap menyelesaikan seluruh hak dan kewajiban yang telah timbul sebelum tanggal efektif pengakhiran."
                        ],

                        [
                            'judul' => 'KOMUNIKASI/PEMBERITAHUAN',
                            'text' => "1. Segala pemberitahuan yang diisyaratkan atau diperkenankan menurut perjanjian kerjasama ini harus dibuat secara tertulis dan dapat dikirimkan melalui email, surat tercatat atau dikirimkan secara langsung melalui kurir kepada alamat-alamat di bawah ini:\n\na. Jika kepada PIHAK PERTAMA:\nNama : [Nama PIHAK PERTAMA]\nAlamat : [Alamat PIHAK PERTAMA]\nTelepon : [Nomor Telepon PIHAK PERTAMA]\nU.p : [Nama Penanggung Jawab]\nEmail : [Email PIHAK PERTAMA]\n\nb. Jika kepada PIHAK KEDUA:\nNama : [Nama PIHAK KEDUA]\nAlamat : [Alamat PIHAK KEDUA]\nTelepon : [Nomor Telepon PIHAK KEDUA]\nU.p : [Nama Penanggung Jawab]\nEmail : [Email PIHAK KEDUA]\n\n2. Jika salah satu pihak mengganti atau mengubah alamatnya atau hal-hal terkait lainnya sehubungan dengan alamat ini, maka pihak tersebut harus memberitahukan penggantian dan perubahan tersebut kepada pihak lainnya."
                        ],

                        [
                            'judul' => 'PENYELESAIAN SENGKETA DAN KETENTUAN PENUTUP',
                            'text' => "1. Setiap perselisihan yang timbul sehubungan dengan pelaksanaan atau penafsiran Perjanjian ini akan diselesaikan terlebih dahulu secara musyawarah untuk mufakat dalam waktu paling lama 30 (tiga puluh) Hari Kalender sejak salah satu pihak menyampaikan pemberitahuan tertulis mengenai adanya perselisihan.\n\n2. Apabila musyawarah sebagaimana dimaksud pada ayat (1) tidak mencapai kesepakatan, PARA PIHAK sepakat untuk menyelesaikan perselisihan melalui Pengadilan Negeri [Kota/Kabupaten] di wilayah hukum kedudukan PIHAK PERTAMA, tanpa mengurangi hak PIHAK PERTAMA untuk mengajukan gugatan atau tuntutan lain sesuai dengan ketentuan peraturan perundang-undangan.\n\n3. Selama proses penyelesaian perselisihan berlangsung, PARA PIHAK tetap wajib melaksanakan bagian Perjanjian yang tidak menjadi objek perselisihan.\n\n4. Setiap perubahan, penambahan, atau pengurangan terhadap Perjanjian ini hanya sah apabila dibuat addendum/amandemen secara tertulis dan ditandatangani oleh PARA PIHAK atau wakilnya yang sah, serta menjadi bagian yang tidak terpisahkan dari Perjanjian ini.\n\n5. Apabila terdapat ketentuan dalam Perjanjian ini yang dinyatakan tidak sah, tidak berlaku, atau tidak dapat dilaksanakan berdasarkan putusan pengadilan atau ketentuan peraturan perundang-undangan, ketentuan lainnya tetap berlaku dan mengikat PARA PIHAK.\n\n6. PARA PIHAK menyatakan bahwa:\n\na. telah membaca, memahami, dan menyetujui seluruh isi Perjanjian ini;\n\nb. memiliki kewenangan yang sah untuk menandatangani dan melaksanakan Perjanjian ini;\n\nc. Perjanjian ini dibuat tanpa adanya paksaan, kekhilafan, atau penipuan dari pihak mana pun.\n\n7. Perjanjian ini dibuat dalam 2 (dua) rangkap asli yang masing-masing mempunyai kekuatan hukum yang sama, dan mulai berlaku sejak tanggal ditandatangani oleh PARA PIHAK."
                        ],
                    ],

                    'tutup' => "PIHAK PERTAMA\n[Nama Perusahaan PIHAK PERTAMA]\n\n\n\n[Nama Penandatangan PIHAK PERTAMA]\n[Jabatan]\n\n\nPIHAK KEDUA\n[Nama PIHAK KEDUA]\n\n\n\n[Nama Penandatangan PIHAK KEDUA]\n[Jabatan]",

                    'lampiran' => [
                        [
                            'judul' => 'LAMPIRAN I PERJANJIAN — DESKRIPSI LAYANAN DAN KONFIGURASI',
                            'text' => "NOMOR: [Nomor Perjanjian]",
                            'html' => '<table style="width:100%; border-collapse:collapse;">'
                                . '<tbody>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Nama Pelanggan</td><td style="border:1px solid #000; padding:4px 6px;">[Nama PIHAK KEDUA]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Tanggal Awal Berlangganan</td><td style="border:1px solid #000; padding:4px 6px;">[Tanggal Awal]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Tanggal Akhir Berlangganan</td><td style="border:1px solid #000; padding:4px 6px;">[Tanggal Akhir]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">NPWP</td><td style="border:1px solid #000; padding:4px 6px;">[NPWP]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Lokasi / Wilayah Layanan</td><td style="border:1px solid #000; padding:4px 6px;">[Lokasi / Wilayah Layanan]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Layanan</td><td style="border:1px solid #000; padding:4px 6px;">[Jenis Layanan]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Biaya Instalasi</td><td style="border:1px solid #000; padding:4px 6px;">[Biaya Instalasi]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Biaya Layanan</td><td style="border:1px solid #000; padding:4px 6px;">[Biaya Layanan]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Biaya Registrasi</td><td style="border:1px solid #000; padding:4px 6px;">[Biaya Registrasi]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Konfigurasi</td><td style="border:1px solid #000; padding:4px 6px;">PIHAK PERTAMA melakukan konfigurasi sampai dengan Backbone.<br>Pihak Kedua melakukan konfigurasi ke Pelanggan.</td></tr>'
                                . '</tbody>'
                                . '</table>'
                        ],

                        [
                            'judul' => 'LAMPIRAN II PERJANJIAN — PAKET LAYANAN',
                            'text' => "NOMOR: [Nomor Perjanjian]\n\nPaket layanan meliputi persyaratan sebagai berikut:\n\n1. Nama Brand : [Nama Brand]\n2. Harga setiap paket minimal : [Harga / Paket]\n3. Biaya instalasi disesuaikan dengan kebutuhan pelanggan.\n4. Paket Layanan yang dibuat harus diinformasikan ke Pihak Pertama untuk persetujuan dan jika ada perubahan maka maksimal menginformasikan perubahan tersebut [Jumlah Hari] hari kalender."
                        ],

                        [
                            'judul' => 'LAMPIRAN III PERJANJIAN — PENGADUAN PELANGGAN',
                            'text' => "NOMOR: [Nomor Perjanjian]\n\nPenanganan gangguan selama operasional dilayani sebagai berikut:\n\n[Nama Perusahaan PIHAK PERTAMA] mengoperasikan call center melalui chat, telepon, dan surat elektronik (e-mail) selama 24 jam per hari, 7 hari untuk setiap minggu.\n\nUntuk koordinasi, perijinan dan pencatatan, seluruh pemberitahuan yang membutuhkan tindakan-tindakan oleh [Nama Perusahaan PIHAK PERTAMA], akan dilakukan dalam bentuk pemberitahuan tertulis dalam bentuk surat yang ditandatangani dan dikirimkan ke alamat [Nama Perusahaan PIHAK PERTAMA] atau melalui call center.\n\n[Nama Perusahaan PIHAK PERTAMA] memberikan tanggapan, deteksi dan perbaikan dengan ketentuan sebagai berikut:",
                            'html' => [
                                '<table style="width:100%; border-collapse:collapse;">'
                                . '<tbody>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Kegiatan</td><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Tolak Ukur Layanan</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px;">Penerimaan pengaduan gangguan</td><td style="border:1px solid #000; padding:4px 6px;">[Waktu]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px;">Konfirmasi penyebab Gangguan (RFO)</td><td style="border:1px solid #000; padding:4px 6px;">[Ketentuan Waktu]</td></tr>'
                                . '</tbody>'
                                . '</table>',

                                '<p>Matriks Eskalasi:</p>'
                                . '<table style="width:100%; border-collapse:collapse;">'
                                . '<tbody>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">No</td><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Fault time</td><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Escalation Level</td><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Point of Contact</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px;">1</td><td style="border:1px solid #000; padding:4px 6px;">[Fault Time]</td><td style="border:1px solid #000; padding:4px 6px;">Level 1</td><td style="border:1px solid #000; padding:4px 6px;">Helpdesk<br>Phone: [Nomor]<br>Email: [Email]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px;">2</td><td style="border:1px solid #000; padding:4px 6px;">[Fault Time]</td><td style="border:1px solid #000; padding:4px 6px;">Level 2</td><td style="border:1px solid #000; padding:4px 6px;">NOC<br>Phone: [Nomor]<br>Email: [Email]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px;">3</td><td style="border:1px solid #000; padding:4px 6px;">[Fault Time]</td><td style="border:1px solid #000; padding:4px 6px;">Level 3</td><td style="border:1px solid #000; padding:4px 6px;">Direktur<br>Phone: [Nomor]<br>Email: [Email]</td></tr>'
                                . '</tbody>'
                                . '</table>',
                            ],
                        ],

                        [
                            'judul' => 'LAMPIRAN IV PERJANJIAN — PERANGKAT',
                            'text' => "NOMOR: [Nomor Perjanjian]",
                            'html' => '<table style="width:100%; border-collapse:collapse;">'
                                . '<tbody>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">No.</td><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Deskripsi</td><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Jumlah</td><td style="border:1px solid #000; padding:4px 6px; font-weight:bold;">Keterangan</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px;">1</td><td style="border:1px solid #000; padding:4px 6px;">[Deskripsi Perangkat]</td><td style="border:1px solid #000; padding:4px 6px;">[Jumlah]</td><td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td></tr>'
                                . '<tr><td style="border:1px solid #000; padding:4px 6px;">2</td><td style="border:1px solid #000; padding:4px 6px;">[Deskripsi Perangkat]</td><td style="border:1px solid #000; padding:4px 6px;">[Jumlah]</td><td style="border:1px solid #000; padding:4px 6px;">[Keterangan]</td></tr>'
                                . '</tbody>'
                                . '</table>'
                        ],
                    ],
                ],
            ],


            'kontrak-managed-service' => [
                'title' => 'Perjanjian Managed Service',
                'header_data' => [
                    'kopInstansi' => '[Ketik nama perusahaan PIHAK PERTAMA]',
                    'kopAlamat' => '[Ketik alamat perusahaan di sini]',
                    'kopKontrak' => 'Telp: [Nomor telepon] | Email: [Email perusahaan]',
                    'nomorSurat' => 'MS/001/VIII/2026',
                    'perihalSurat' => 'Perjanjian Managed Service',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'preamble' => "Pada hari ini, [Hari], tanggal [Tanggal] bulan [Bulan] tahun [Tahun], telah dibuat dan ditandatangani Perjanjian Managed Service oleh dan antara:",

                    'paraPihak' => [
                        "1. Nama Perusahaan : [Nama perusahaan PIHAK PERTAMA]\nAlamat : [Alamat lengkap]\nDiwakili oleh : [Nama pejabat]\nJabatan : [Jabatan]\n\nSelanjutnya disebut sebagai “PIHAK PERTAMA”.",

                        "2. Nama Perusahaan : [Nama perusahaan PIHAK KEDUA]\nAlamat : [Alamat lengkap]\nDiwakili oleh : [Nama pejabat]\nJabatan : [Jabatan]\n\nSelanjutnya disebut sebagai “PIHAK KEDUA”.",
                    ],

                    'menimbang' => "a. bahwa PIHAK PERTAMA membutuhkan layanan pengelolaan, pemantauan, pemeliharaan, dan dukungan terhadap sistem dan/atau infrastruktur teknologi informasi;\nb. bahwa PIHAK KEDUA memiliki kompetensi dan sumber daya untuk menyediakan layanan managed service;\nc. bahwa Para Pihak sepakat mengatur pelaksanaan layanan tersebut dalam Perjanjian Managed Service.",

                    'mengingat' => "1. Kitab Undang-Undang Hukum Perdata (KUHPerdata);\n2. Ketentuan peraturan perundang-undangan yang berlaku;\n3. Standar dan prosedur layanan teknologi informasi yang disepakati;\n4. Kesepakatan Para Pihak.",

                    'isi' => [
                        [
                            'judul' => 'RUANG LINGKUP LAYANAN',
                            'text' => "PIHAK KEDUA menyediakan layanan managed service yang meliputi monitoring, maintenance, troubleshooting, support, pengelolaan sistem, pengelolaan jaringan, pengelolaan server, keamanan sistem, backup, serta layanan teknologi informasi lainnya sesuai ruang lingkup yang disepakati."
                        ],
                        [
                            'judul' => 'SERVICE LEVEL AGREEMENT',
                            'text' => "PIHAK KEDUA wajib memberikan layanan sesuai tingkat layanan atau Service Level Agreement (SLA) yang mencakup waktu respons, waktu penanganan, tingkat ketersediaan layanan, prioritas gangguan, dan parameter layanan lainnya."
                        ],
                        [
                            'judul' => 'MONITORING DAN PEMELIHARAAN',
                            'text' => "PIHAK KEDUA melakukan pemantauan dan pemeliharaan terhadap sistem dan/atau infrastruktur yang menjadi objek layanan secara berkala untuk menjaga kinerja, keamanan, dan ketersediaan layanan."
                        ],
                        [
                            'judul' => 'PENANGANAN GANGGUAN',
                            'text' => "Setiap gangguan yang dilaporkan PIHAK PERTAMA akan ditangani oleh PIHAK KEDUA berdasarkan tingkat prioritas dan SLA yang telah disepakati."
                        ],
                        [
                            'judul' => 'PERUBAHAN DAN PEKERJAAN TAMBAHAN',
                            'text' => "Permintaan pekerjaan yang berada di luar ruang lingkup layanan dapat diperlakukan sebagai pekerjaan tambahan dan dilaksanakan berdasarkan persetujuan Para Pihak serta ketentuan biaya yang disepakati."
                        ],
                        [
                            'judul' => 'AKSES SISTEM',
                            'text' => "PIHAK PERTAMA memberikan akses yang diperlukan kepada PIHAK KEDUA sepanjang diperlukan untuk pelaksanaan layanan. PIHAK KEDUA wajib menggunakan akses tersebut hanya untuk kepentingan pelaksanaan Perjanjian."
                        ],
                        [
                            'judul' => 'KEAMANAN INFORMASI',
                            'text' => "PIHAK KEDUA wajib menjaga keamanan informasi, kredensial akses, konfigurasi sistem, data, dan informasi milik PIHAK PERTAMA serta menerapkan tindakan pengamanan yang wajar sesuai kebutuhan layanan."
                        ],
                        [
                            'judul' => 'BACKUP DAN PEMULIHAN DATA',
                            'text' => "Apabila termasuk dalam ruang lingkup layanan, PIHAK KEDUA melakukan backup dan pemulihan data sesuai kebijakan, jadwal, kapasitas, serta mekanisme yang telah disepakati Para Pihak."
                        ],
                        [
                            'judul' => 'LAPORAN LAYANAN',
                            'text' => "PIHAK KEDUA memberikan laporan layanan secara berkala yang dapat mencakup kondisi sistem, gangguan, tindakan pemeliharaan, hasil monitoring, penggunaan sumber daya, dan rekomendasi perbaikan."
                        ],
                        [
                            'judul' => 'BIAYA DAN PEMBAYARAN',
                            'text' => "PIHAK PERTAMA wajib membayar biaya managed service sesuai nilai dan periode pembayaran yang telah disepakati. Pekerjaan tambahan akan dikenakan biaya berdasarkan persetujuan Para Pihak."
                        ],
                        [
                            'judul' => 'JANGKA WAKTU',
                            'text' => "Perjanjian berlaku selama [jangka waktu] sejak tanggal [tanggal mulai] sampai dengan [tanggal berakhir] dan dapat diperpanjang berdasarkan kesepakatan tertulis."
                        ],
                        [
                            'judul' => 'KERAHASIAAN',
                            'text' => "Para Pihak wajib menjaga kerahasiaan seluruh informasi dan data yang diperoleh selama pelaksanaan layanan dan tidak menggunakannya di luar kepentingan Perjanjian tanpa persetujuan pihak lainnya."
                        ],
                        [
                            'judul' => 'PENGAKHIRAN',
                            'text' => "Perjanjian dapat diakhiri berdasarkan kesepakatan Para Pihak atau apabila salah satu pihak melakukan pelanggaran material terhadap ketentuan Perjanjian."
                        ],
                        [
                            'judul' => 'PENYELESAIAN PERSELISIHAN',
                            'text' => "Perselisihan akan diselesaikan terlebih dahulu melalui musyawarah. Apabila tidak tercapai kesepakatan, penyelesaian dilakukan sesuai mekanisme hukum yang berlaku."
                        ],
                        [
                            'judul' => 'PENUTUP',
                            'text' => "Perjanjian ini menjadi dasar pelaksanaan layanan managed service antara Para Pihak dan dilaksanakan dengan itikad baik."
                        ],
                    ],

                    'tutup' => "Demikian Perjanjian Managed Service ini dibuat dan ditandatangani oleh Para Pihak untuk dipergunakan sebagaimana mestinya.",

                    'lampiran' => [
                        [
                            'judul' => 'LAMPIRAN — SERVICE LEVEL AGREEMENT',
                            'text' => "1. Sistem/Perangkat : [Daftar sistem/perangkat]\n2. Jam Layanan : [Jam layanan]\n3. Response Time : [Waktu respons]\n4. Resolution Time : [Waktu penyelesaian]\n5. Availability : [Persentase]\n6. Prioritas Gangguan : [P1/P2/P3/P4]\n7. Jadwal Maintenance : [Jadwal]\n8. Biaya Layanan : [Nilai biaya]"
                        ],
                    ],
                ],
            ],

            'kontrak-soho' => [
                'title' => 'Perjanjian Layanan SOHO',
                'header_data' => [
                    'kopInstansi' => '[Ketik nama perusahaan PIHAK PERTAMA]',
                    'kopAlamat' => '[Ketik alamat perusahaan di sini]',
                    'kopKontrak' => 'Telp: [Nomor telepon] | Email: [Email perusahaan]',
                    'nomorSurat' => 'SOHO/001/VIII/2026',
                    'perihalSurat' => 'Perjanjian Layanan SOHO',
                    'sifatSurat' => 'Penting',
                ],
                'body_content' => [
                    'preamble' => "Pada hari ini, [Hari], tanggal [Tanggal] bulan [Bulan] tahun [Tahun], telah dibuat dan ditandatangani Perjanjian Layanan SOHO oleh dan antara:",

                    'paraPihak' => [
                        "1. Nama Perusahaan : [Nama perusahaan PIHAK PERTAMA]\nAlamat : [Alamat lengkap]\nDiwakili oleh : [Nama pejabat]\nJabatan : [Jabatan]\n\nSelanjutnya disebut sebagai “PIHAK PERTAMA”.",

                        "2. Nama Pelanggan/Perusahaan : [Nama pelanggan/perusahaan PIHAK KEDUA]\nAlamat : [Alamat lengkap]\nNomor Identitas/NIB : [Nomor identitas/NIB]\nDiwakili oleh : [Nama]\nJabatan : [Jabatan]\n\nSelanjutnya disebut sebagai “PIHAK KEDUA”.",
                    ],

                    'menimbang' => "a. bahwa PIHAK PERTAMA menyediakan layanan teknologi informasi dan/atau konektivitas untuk kebutuhan Small Office/Home Office (SOHO);\nb. bahwa PIHAK KEDUA membutuhkan layanan tersebut untuk mendukung kegiatan pekerjaan, usaha, dan/atau kebutuhan operasional;\nc. bahwa Para Pihak sepakat mengatur ketentuan layanan SOHO dalam Perjanjian ini.",

                    'mengingat' => "1. Kitab Undang-Undang Hukum Perdata (KUHPerdata);\n2. Ketentuan peraturan perundang-undangan yang berlaku;\n3. Ketentuan layanan dan kebijakan PIHAK PERTAMA;\n4. Kesepakatan Para Pihak.",

                    'isi' => [
                        [
                            'judul' => 'RUANG LINGKUP LAYANAN',
                            'text' => "PIHAK PERTAMA menyediakan layanan SOHO kepada PIHAK KEDUA yang dapat meliputi koneksi internet, perangkat jaringan, alamat IP, instalasi, konfigurasi, pemeliharaan, dukungan teknis, dan layanan tambahan sesuai paket yang dipilih."
                        ],
                        [
                            'judul' => 'INSTALASI DAN AKTIVASI',
                            'text' => "PIHAK PERTAMA melakukan instalasi dan aktivasi layanan pada lokasi yang telah disepakati. PIHAK KEDUA wajib menyediakan akses lokasi dan kondisi teknis yang diperlukan untuk pelaksanaan instalasi."
                        ],
                        [
                            'judul' => 'PERANGKAT LAYANAN',
                            'text' => "Perangkat yang disediakan dalam layanan dapat berupa modem, router, access point, perangkat jaringan, atau perangkat pendukung lainnya sesuai paket layanan. Kepemilikan perangkat mengikuti ketentuan yang disepakati Para Pihak."
                        ],
                        [
                            'judul' => 'PENGGUNAAN LAYANAN',
                            'text' => "PIHAK KEDUA wajib menggunakan layanan secara wajar dan sesuai dengan ketentuan hukum serta tidak diperkenankan menggunakan layanan untuk kegiatan yang dapat mengganggu jaringan, keamanan sistem, atau pelanggan lainnya."
                        ],
                        [
                            'judul' => 'PEMELIHARAAN DAN GANGGUAN',
                            'text' => "PIHAK PERTAMA menyediakan dukungan teknis dan melakukan penanganan gangguan sesuai standar layanan yang berlaku. Gangguan yang timbul akibat kerusakan atau perubahan pada instalasi yang dilakukan PIHAK KEDUA dapat menjadi tanggung jawab PIHAK KEDUA."
                        ],
                        [
                            'judul' => 'BIAYA LAYANAN',
                            'text' => "PIHAK KEDUA wajib membayar biaya berlangganan sesuai paket layanan yang dipilih. Biaya instalasi, perangkat tambahan, perubahan layanan, atau pekerjaan di luar paket dapat dikenakan sesuai ketentuan yang berlaku."
                        ],
                        [
                            'judul' => 'PERUBAHAN PAKET',
                            'text' => "PIHAK KEDUA dapat mengajukan perubahan paket layanan sesuai ketersediaan layanan. Perubahan tersebut dapat menyebabkan perubahan biaya dan ketentuan layanan."
                        ],
                        [
                            'judul' => 'KEAMANAN AKUN',
                            'text' => "PIHAK KEDUA bertanggung jawab menjaga keamanan akun, kata sandi, perangkat, serta kredensial yang digunakan untuk mengakses layanan."
                        ],
                        [
                            'judul' => 'JANGKA WAKTU',
                            'text' => "Perjanjian berlaku selama [jangka waktu] sejak layanan diaktifkan dan dapat diperpanjang sesuai ketentuan yang berlaku."
                        ],
                        [
                            'judul' => 'PENGHENTIAN LAYANAN',
                            'text' => "Layanan dapat dihentikan berdasarkan permintaan PIHAK KEDUA, berakhirnya masa layanan, tidak dipenuhinya kewajiban pembayaran, atau alasan lain sesuai dengan ketentuan Perjanjian."
                        ],
                        [
                            'judul' => 'KERAHASIAAN',
                            'text' => "Para Pihak wajib menjaga kerahasiaan informasi yang diperoleh selama pelaksanaan layanan dan tidak menggunakannya untuk kepentingan di luar Perjanjian tanpa persetujuan pihak lainnya."
                        ],
                        [
                            'judul' => 'PENYELESAIAN PERSELISIHAN',
                            'text' => "Setiap perselisihan akan diselesaikan terlebih dahulu melalui musyawarah untuk mencapai mufakat. Apabila tidak tercapai kesepakatan, penyelesaian dilakukan berdasarkan ketentuan hukum yang berlaku."
                        ],
                        [
                            'judul' => 'LAIN-LAIN',
                            'text' => "Ketentuan teknis, paket layanan, harga, spesifikasi perangkat, dan ketentuan tambahan dapat dituangkan dalam dokumen atau lampiran yang menjadi bagian tidak terpisahkan dari Perjanjian."
                        ],
                        [
                            'judul' => 'PENUTUP',
                            'text' => "Perjanjian ini dibuat sebagai dasar penyediaan dan penggunaan layanan SOHO antara Para Pihak."
                        ],
                    ],

                    'tutup' => "Demikian Perjanjian Layanan SOHO ini dibuat dan ditandatangani oleh Para Pihak dengan itikad baik dan tanpa adanya paksaan dari pihak manapun.",

                    'lampiran' => [
                        [
                            'judul' => 'LAMPIRAN — DETAIL PAKET SOHO',
                            'text' => "1. Nama Paket : [Nama paket]\n2. Kecepatan Layanan : [Kecepatan]\n3. Biaya Bulanan : [Biaya]\n4. Biaya Instalasi : [Biaya]\n5. Perangkat : [Daftar perangkat]\n6. Alamat Instalasi : [Alamat]\n7. Alamat IP : [Ketentuan IP]\n8. Masa Berlangganan : [Periode]\n9. SLA : [Ketentuan SLA]"
                        ],
                    ],
                ],
            ],
        ];

        return $templates[$key] ?? null;

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
            'kemitraan',
            'colocation',
            'managed-service',
            'soho',
            'kontrak-payung',
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
                if ($judul !== '') {
                    $parts[] = '<p><strong>'.$judul.'</strong></p>';
                }

                // Blok teks biasa (diparagraph-kan otomatis).
                if (!empty($item['text'])) {
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