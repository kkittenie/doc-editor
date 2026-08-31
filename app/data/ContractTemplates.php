<?php

namespace App\Data;

class ContractTemplates
{
    /**
     * Template kontrak (dibangkitkan dari dokumen .docx resmi,
     * teks & tabel body_content asli tanpa perubahan).
     */
    public static function all(): array
    {
        return [
            'kontrak-kemitraan' => [
                'title' => 'Perjanjian Kerjasama Jual Kembali Jasa Layanan Akses Internet',
                'header_data' => array (
  'kopInstansi' => '[Nama Perusahaan PIHAK PERTAMA]',
  'kopAlamat' => '[Alamat perusahaan PIHAK PERTAMA]',
  'kopKontrak' => 'PERJANJIAN KERJASAMA',
  'nomorSurat' => '[Nomor Perjanjian]',
  'perihalSurat' => 'Jual Kembali Jasa Layanan Akses Internet',
  'sifatSurat' => 'Penting',
),
                'body_content' => array (
  'preamble' => 'PERJANJIAN KERJASAMA

ANTARA

PT BINA INFORMATIKA SOLUSI

DENGAN

ADI DARMAWAN

TENTANG

JUAL KEMBALI JASA LAYANAN AKSES INTERNET

NOMOR: 196/FBT/PKS/III/2026

Perjanjian Kerjasama tentang Jual Kembali Jasa Layanan Akses Internet (selanjutnya disebut “Perjanjian”), dibuat pada hari Kamis, tanggal 5 Maret 2026, bertempat di Cirebon, oleh dan antara:

PT Bina Informatika Solusi, suatu perseroan terbatas, yang didirikan berdasarkan Hukum Negara Republik Indonesia, berkedudukan di Jl. Prakarsa Muda No. 258, Kel. Pekiringan, Kec. Kesambi, Kota Cirebon, Jawa Barat 45131. Berdasarkan Akta Berita Acara RUPS Tahunan Perseroan Terbatas “PT Bina Informatika Solusi”, Nomor 5, tanggal 10 Juli 2026, dibuat dihadapan Irni Yuniati, S.H., M.Kn., Notaris di Kota Cimahi. Dalam hal ini diwakili oleh Ageng Bagja Priyadi, S.T., M.Kom., selaku Direktur, sah bertindak untuk dan atas nama PT Bina Informatika Solusi, selanjutnya disebut sebagai “PIHAK PERTAMA”.

d e n g a n

Adi Darmawan, suatu (Perorangan), beralamat di Perumahan Zada Regency 1 Blok E No.5, Rt/Rw 03/09, Desa Bulakan, Kec. Sukoharjo, Kab. Sukoharjo. Dalam hal ini bertindak atas nama sendiri, selanjutnya dalam Perjanjian ini disebut sebagai “PIHAK KEDUA”.

PIHAK PERTAMA dan PIHAK KEDUA secara sendiri-sendiri disebut “PIHAK” dan secara bersama-sama disebut sebagai “PARA PIHAK”.

PARA PIHAK dengan ini menerangkan terlebih dahulu hal-hal sebagai berikut:

Bahwa PIHAK PERTAMA merupakan penyelenggara jasa yang memiliki izin penyelenggaraan Layanan Akses Internet dari Kementerian Komunikasi dan Informatika Republik Indonesia Nomor: 1888 tertanggal 18 Oktober 2017 Tentang Perubahan Nomor: 987 Tahun 2014 Izin Penyelenggaraan Jasa Akses Internet (Internet Service Provider) PT Bina Informatika Solusi.

Bahwa PIHAK KEDUA merupakan suatu (perseorangan).

Bahwa PIHAK PERTAMA sepakat untuk menjual jasa Layanan Akses Internet kepada PIHAK KEDUA, dan untuk selanjutnya PIHAK KEDUA sepakat untuk menjual kembali Jasa Layanan Akses Internet kepada Pelanggan/end user.

Berdasarkan hal-hal tersebut di atas, maka PARA PIHAK sepakat untuk membuat Perjanjian Kerjasama Jual Kembali Jasa Layanan Akses Internet dengan syarat-syarat dan ketentuan-ketentuan sebagai berikut:',
  'isi' => 
  array (
    0 => 
    array (
      'judul' => 'DEFINISI',
      'text' => 'Dalam Perjanjian ini yang dimaksud dengan:

Perjanjian adalah Perjanjian Kerja Sama Jual Kembali Jasa Layanan Akses Internet beserta seluruh lampiran, perubahan, addendum, dan dokumen lain yang merupakan satu kesatuan yang tidak terpisahkan dengan Perjanjian ini.

Layanan adalah jasa akses internet yang diselenggarakan oleh PIHAK PERTAMA berdasarkan izin penyelenggaraan yang berlaku, termasuk seluruh fasilitas, jaringan, sistem, aplikasi, perangkat, dan layanan pendukung yang berkaitan.

PIHAK KEDUA (Reseller) adalah pihak yang memperoleh hak dari PIHAK PERTAMA untuk memasarkan, menjual kembali, dan mengelola Layanan kepada Pelanggan sesuai dengan ketentuan Perjanjian ini.

Pelanggan adalah orang perseorangan, badan usaha, badan hukum, instansi pemerintah, atau pihak lainnya yang memperoleh Layanan melalui PIHAK KEDUA.

Biaya adalah seluruh kewajiban pembayaran yang menjadi tanggung jawab PIHAK KEDUA kepada PIHAK PERTAMA berdasarkan Perjanjian ini, termasuk namun tidak terbatas pada biaya instalasi, biaya layanan, biaya administrasi, denda, penalti, dan biaya lainnya yang disepakati.

Perangkat adalah seluruh perangkat keras, perangkat lunak, jaringan, sistem pendukung, maupun perlengkapan lainnya yang digunakan dalam penyelenggaraan Layanan, baik yang dimiliki maupun yang dipinjamkan oleh PIHAK PERTAMA.

Data Pelanggan adalah seluruh informasi mengenai identitas, administrasi, teknis, maupun data lain yang berkaitan dengan Pelanggan yang diperoleh dalam pelaksanaan Perjanjian ini.

Data Pemakaian adalah seluruh data mengenai penggunaan Layanan oleh Pelanggan, termasuk namun tidak terbatas pada data trafik, log akses, penggunaan bandwidth, statistik penggunaan, dan data teknis lainnya.

Hari Kerja adalah hari Senin sampai dengan Jumat, selain hari libur nasional dan hari yang ditetapkan Pemerintah sebagai hari libur.

Keadaan Memaksa (Force Majeure) adalah setiap keadaan di luar kemampuan dan kendali PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, sebagaimana diatur lebih lanjut dalam Pasal mengenai Force Majeure.

Dokumen Layanan adalah seluruh formulir, Service Order Form (SOF), formulir perubahan layanan, berita acara aktivasi, berita acara serah terima, maupun dokumen operasional lainnya yang diterbitkan oleh PIHAK PERTAMA sebagai bagian dari pelaksanaan Perjanjian ini.

Penalti adalah kewajiban pembayaran yang dikenakan kepada PIHAK KEDUA akibat pelanggaran terhadap ketentuan Perjanjian, termasuk namun tidak terbatas pada terminasi dini, pembatalan layanan, downgrade, relokasi, maupun pelanggaran lainnya sebagaimana diatur dalam Perjanjian ini.

Aplikasi Reseller adalah sistem aplikasi yang disediakan oleh PIHAK PERTAMA untuk keperluan registrasi pelanggan, pelaporan penjualan, monitoring layanan, administrasi, maupun fungsi operasional lainnya.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Dalam Perjanjian ini yang dimaksud dengan:',
        ),
        1 => 
        array (
          'p' => 'Perjanjian adalah Perjanjian Kerja Sama Jual Kembali Jasa Layanan Akses Internet beserta seluruh lampiran, perubahan, addendum, dan dokumen lain yang merupakan satu kesatuan yang tidak terpisahkan dengan Perjanjian ini.',
        ),
        2 => 
        array (
          'p' => 'Layanan adalah jasa akses internet yang diselenggarakan oleh PIHAK PERTAMA berdasarkan izin penyelenggaraan yang berlaku, termasuk seluruh fasilitas, jaringan, sistem, aplikasi, perangkat, dan layanan pendukung yang berkaitan.',
        ),
        3 => 
        array (
          'p' => 'PIHAK KEDUA (Reseller) adalah pihak yang memperoleh hak dari PIHAK PERTAMA untuk memasarkan, menjual kembali, dan mengelola Layanan kepada Pelanggan sesuai dengan ketentuan Perjanjian ini.',
        ),
        4 => 
        array (
          'p' => 'Pelanggan adalah orang perseorangan, badan usaha, badan hukum, instansi pemerintah, atau pihak lainnya yang memperoleh Layanan melalui PIHAK KEDUA.',
        ),
        5 => 
        array (
          'p' => 'Biaya adalah seluruh kewajiban pembayaran yang menjadi tanggung jawab PIHAK KEDUA kepada PIHAK PERTAMA berdasarkan Perjanjian ini, termasuk namun tidak terbatas pada biaya instalasi, biaya layanan, biaya administrasi, denda, penalti, dan biaya lainnya yang disepakati.',
        ),
        6 => 
        array (
          'p' => 'Perangkat adalah seluruh perangkat keras, perangkat lunak, jaringan, sistem pendukung, maupun perlengkapan lainnya yang digunakan dalam penyelenggaraan Layanan, baik yang dimiliki maupun yang dipinjamkan oleh PIHAK PERTAMA.',
        ),
        7 => 
        array (
          'p' => 'Data Pelanggan adalah seluruh informasi mengenai identitas, administrasi, teknis, maupun data lain yang berkaitan dengan Pelanggan yang diperoleh dalam pelaksanaan Perjanjian ini.',
        ),
        8 => 
        array (
          'p' => 'Data Pemakaian adalah seluruh data mengenai penggunaan Layanan oleh Pelanggan, termasuk namun tidak terbatas pada data trafik, log akses, penggunaan bandwidth, statistik penggunaan, dan data teknis lainnya.',
        ),
        9 => 
        array (
          'p' => 'Hari Kerja adalah hari Senin sampai dengan Jumat, selain hari libur nasional dan hari yang ditetapkan Pemerintah sebagai hari libur.',
        ),
        10 => 
        array (
          'p' => 'Keadaan Memaksa (Force Majeure) adalah setiap keadaan di luar kemampuan dan kendali PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, sebagaimana diatur lebih lanjut dalam Pasal mengenai Force Majeure.',
        ),
        11 => 
        array (
          'p' => 'Dokumen Layanan adalah seluruh formulir, Service Order Form (SOF), formulir perubahan layanan, berita acara aktivasi, berita acara serah terima, maupun dokumen operasional lainnya yang diterbitkan oleh PIHAK PERTAMA sebagai bagian dari pelaksanaan Perjanjian ini.',
        ),
        12 => 
        array (
          'p' => 'Penalti adalah kewajiban pembayaran yang dikenakan kepada PIHAK KEDUA akibat pelanggaran terhadap ketentuan Perjanjian, termasuk namun tidak terbatas pada terminasi dini, pembatalan layanan, downgrade, relokasi, maupun pelanggaran lainnya sebagaimana diatur dalam Perjanjian ini.',
        ),
        13 => 
        array (
          'p' => 'Aplikasi Reseller adalah sistem aplikasi yang disediakan oleh PIHAK PERTAMA untuk keperluan registrasi pelanggan, pelaporan penjualan, monitoring layanan, administrasi, maupun fungsi operasional lainnya.',
        ),
      ),
    ),
    1 => 
    array (
      'judul' => 'RUANG LINGKUP',
      'text' => 'Ruang lingkup dalam Perjanjian ini adalah kerjasama Jual Kembali Jasa Layanan Akses Internet milik PIHAK PERTAMA oleh PIHAK KEDUA;

Lokasi/wilayah serta Konfigurasi Teknis Jasa Layanan Akses Internet yang akan dijual kembali oleh PIHAK KEDUA kepada Pelanggan sebagaimana tercantum pada Lampiran I Perjanjian ini;

Paket Jasa Layanan Akses Internet yang akan dijual kembali oleh PIHAK KEDUA kepada Pelanggan sebagaimana dimaksud dalam ayat (1) dan (2) tercantum pada Lampiran II Perjanjian ini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Ruang lingkup dalam Perjanjian ini adalah kerjasama Jual Kembali Jasa Layanan Akses Internet milik PIHAK PERTAMA oleh PIHAK KEDUA;',
        ),
        1 => 
        array (
          'p' => 'Lokasi/wilayah serta Konfigurasi Teknis Jasa Layanan Akses Internet yang akan dijual kembali oleh PIHAK KEDUA kepada Pelanggan sebagaimana tercantum pada Lampiran I Perjanjian ini;',
        ),
        2 => 
        array (
          'p' => 'Paket Jasa Layanan Akses Internet yang akan dijual kembali oleh PIHAK KEDUA kepada Pelanggan sebagaimana dimaksud dalam ayat (1) dan (2) tercantum pada Lampiran II Perjanjian ini.',
        ),
      ),
    ),
    2 => 
    array (
      'judul' => 'JANGKA WAKTU',
      'text' => 'Perjanjian ini berlaku sejak tanggal ditandatangani oleh PARA PIHAK dan tetap berlaku untuk jangka waktu 1 (satu) tahun, sebagaimana tercantum dalam Lampiran I, kecuali diakhiri lebih dahulu sesuai ketentuan dalam Perjanjian ini.

Setelah jangka waktu sebagaimana dimaksud pada ayat (1) berakhir, Perjanjian ini secara otomatis diperpanjang untuk jangka waktu 1 (satu) tahun berikutnya dengan syarat:

tidak terdapat wanprestasi yang belum diselesaikan oleh salah satu pihak; dan

tidak ada pemberitahuan tertulis mengenai pengakhiran Perjanjian dari Pihak Kedua paling lambat 30 (tiga puluh) Hari Kalender sebelum tanggal berakhirnya Perjanjian.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Perjanjian ini berlaku sejak tanggal ditandatangani oleh PARA PIHAK dan tetap berlaku untuk jangka waktu 1 (satu) tahun, sebagaimana tercantum dalam Lampiran I, kecuali diakhiri lebih dahulu sesuai ketentuan dalam Perjanjian ini.',
        ),
        1 => 
        array (
          'p' => 'Setelah jangka waktu sebagaimana dimaksud pada ayat (1) berakhir, Perjanjian ini secara otomatis diperpanjang untuk jangka waktu 1 (satu) tahun berikutnya dengan syarat:',
        ),
        2 => 
        array (
          'p' => 'tidak terdapat wanprestasi yang belum diselesaikan oleh salah satu pihak; dan',
        ),
        3 => 
        array (
          'p' => 'tidak ada pemberitahuan tertulis mengenai pengakhiran Perjanjian dari Pihak Kedua paling lambat 30 (tiga puluh) Hari Kalender sebelum tanggal berakhirnya Perjanjian.',
        ),
      ),
    ),
    3 => 
    array (
      'judul' => 'HAK DAN KEWAJIBAN PARA PIHAK',
      'text' => 'Hak PIHAK PERTAMA

PIHAK PERTAMA berhak:

menerima pembayaran dari PIHAK KEDUA sesuai dengan ketentuan Perjanjian;

melakukan verifikasi, audit, dan pengawasan terhadap pelaksanaan kerja sama, termasuk data pelanggan, data penjualan, penggunaan jaringan, dan kepatuhan PIHAK KEDUA terhadap Perjanjian;

melakukan isolir, pembatasan, penghentian sementara, atau pemutusan layanan sesuai ketentuan Perjanjian apabila PIHAK KEDUA melakukan wanprestasi atau pelanggaran;

mengubah, mengembangkan, menambah, atau mengurangi jenis layanan, teknologi, sistem, aplikasi, maupun kebijakan operasional sepanjang tidak menghilangkan hak-hak PIHAK KEDUA yang telah timbul sebelum perubahan tersebut berlaku;

menolak permohonan aktivasi, relokasi, perubahan layanan, atau penambahan pelanggan yang tidak memenuhi ketentuan teknis, administratif, atau ketentuan hukum yang berlaku;

menggunakan data operasional yang diperoleh dari pelaksanaan Perjanjian untuk kepentingan operasional, pemenuhan kewajiban hukum, audit, peningkatan kualitas layanan, dan pengembangan usaha dengan tetap memperhatikan ketentuan peraturan perundang-undangan.

Kewajiban PIHAK PERTAMA

PIHAK PERTAMA berkewajiban:

menyediakan Layanan sesuai spesifikasi layanan yang disepakati;

memberikan dukungan teknis, aktivasi layanan, pemeliharaan jaringan, dan penanganan gangguan sesuai ketentuan SLA;

menyediakan sistem administrasi dan pelaporan yang diperlukan untuk pelaksanaan kerja sama;

menjaga kerahasiaan data PIHAK KEDUA dan Pelanggan sesuai ketentuan Perjanjian dan peraturan perundang-undangan;

memberikan informasi kepada PIHAK KEDUA mengenai perubahan kebijakan yang berdampak langsung terhadap pelaksanaan kerja sama.

Hak PIHAK KEDUA

PIHAK KEDUA berhak:

memperoleh Layanan sesuai spesifikasi layanan yang disepakati;

memperoleh dukungan teknis, pelatihan, dan pendampingan operasional sesuai kebutuhan pelaksanaan kerja sama;

memasarkan dan menjual kembali Layanan kepada Pelanggan sesuai ketentuan Perjanjian;

memperoleh akses terhadap sistem administrasi, pelaporan, dan layanan pendukung yang disediakan oleh PIHAK PERTAMA.

Kewajiban PIHAK KEDUA

PIHAK KEDUA berkewajiban:

memasarkan dan menjual Layanan sesuai ketentuan Perjanjian, standar operasional, dan kebijakan PIHAK PERTAMA;

membayar seluruh kewajiban finansial kepada PIHAK PERTAMA tepat waktu;

menjaga kualitas pelayanan kepada Pelanggan dan menjadi pihak yang bertanggung jawab atas hubungan komersial dengan Pelanggan;

melaporkan seluruh data pelanggan, penjualan, perubahan layanan, dan informasi lain pada sistem PIHAK PERTAMA yang dipersyaratkan secara lengkap, benar, dan tepat waktu;

menjaga keamanan jaringan, perangkat, akun, kata sandi, dan akses yang diberikan oleh PIHAK PERTAMA;

tidak menyalahgunakan jaringan, menyembunyikan data pelanggan, melakukan manipulasi data, menggunakan jaringan untuk kegiatan yang melanggar hukum, atau melakukan tindakan lain yang dapat merugikan PIHAK PERTAMA;

mematuhi seluruh ketentuan teknis, operasional, keamanan informasi, serta peraturan perundang-undangan yang berlaku.

Ketentuan Umum

Masing-masing PIHAK wajib melaksanakan hak dan kewajibannya dengan itikad baik, profesional, dan sesuai dengan prinsip kehati-hatian.

Hak dan kewajiban yang diatur dalam Pasal ini tidak mengurangi hak dan kewajiban lain yang diatur dalam Perjanjian ini maupun peraturan perundang-undangan yang berlaku',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Hak PIHAK PERTAMA',
        ),
        1 => 
        array (
          'p' => 'PIHAK PERTAMA berhak:',
        ),
        2 => 
        array (
          'p' => 'menerima pembayaran dari PIHAK KEDUA sesuai dengan ketentuan Perjanjian;',
        ),
        3 => 
        array (
          'p' => 'melakukan verifikasi, audit, dan pengawasan terhadap pelaksanaan kerja sama, termasuk data pelanggan, data penjualan, penggunaan jaringan, dan kepatuhan PIHAK KEDUA terhadap Perjanjian;',
        ),
        4 => 
        array (
          'p' => 'melakukan isolir, pembatasan, penghentian sementara, atau pemutusan layanan sesuai ketentuan Perjanjian apabila PIHAK KEDUA melakukan wanprestasi atau pelanggaran;',
        ),
        5 => 
        array (
          'p' => 'mengubah, mengembangkan, menambah, atau mengurangi jenis layanan, teknologi, sistem, aplikasi, maupun kebijakan operasional sepanjang tidak menghilangkan hak-hak PIHAK KEDUA yang telah timbul sebelum perubahan tersebut berlaku;',
        ),
        6 => 
        array (
          'p' => 'menolak permohonan aktivasi, relokasi, perubahan layanan, atau penambahan pelanggan yang tidak memenuhi ketentuan teknis, administratif, atau ketentuan hukum yang berlaku;',
        ),
        7 => 
        array (
          'p' => 'menggunakan data operasional yang diperoleh dari pelaksanaan Perjanjian untuk kepentingan operasional, pemenuhan kewajiban hukum, audit, peningkatan kualitas layanan, dan pengembangan usaha dengan tetap memperhatikan ketentuan peraturan perundang-undangan.',
        ),
        8 => 
        array (
          'p' => 'Kewajiban PIHAK PERTAMA',
        ),
        9 => 
        array (
          'p' => 'PIHAK PERTAMA berkewajiban:',
        ),
        10 => 
        array (
          'p' => 'menyediakan Layanan sesuai spesifikasi layanan yang disepakati;',
        ),
        11 => 
        array (
          'p' => 'memberikan dukungan teknis, aktivasi layanan, pemeliharaan jaringan, dan penanganan gangguan sesuai ketentuan SLA;',
        ),
        12 => 
        array (
          'p' => 'menyediakan sistem administrasi dan pelaporan yang diperlukan untuk pelaksanaan kerja sama;',
        ),
        13 => 
        array (
          'p' => 'menjaga kerahasiaan data PIHAK KEDUA dan Pelanggan sesuai ketentuan Perjanjian dan peraturan perundang-undangan;',
        ),
        14 => 
        array (
          'p' => 'memberikan informasi kepada PIHAK KEDUA mengenai perubahan kebijakan yang berdampak langsung terhadap pelaksanaan kerja sama.',
        ),
        15 => 
        array (
          'p' => 'Hak PIHAK KEDUA',
        ),
        16 => 
        array (
          'p' => 'PIHAK KEDUA berhak:',
        ),
        17 => 
        array (
          'p' => 'memperoleh Layanan sesuai spesifikasi layanan yang disepakati;',
        ),
        18 => 
        array (
          'p' => 'memperoleh dukungan teknis, pelatihan, dan pendampingan operasional sesuai kebutuhan pelaksanaan kerja sama;',
        ),
        19 => 
        array (
          'p' => 'memasarkan dan menjual kembali Layanan kepada Pelanggan sesuai ketentuan Perjanjian;',
        ),
        20 => 
        array (
          'p' => 'memperoleh akses terhadap sistem administrasi, pelaporan, dan layanan pendukung yang disediakan oleh PIHAK PERTAMA.',
        ),
        21 => 
        array (
          'p' => 'Kewajiban PIHAK KEDUA',
        ),
        22 => 
        array (
          'p' => 'PIHAK KEDUA berkewajiban:',
        ),
        23 => 
        array (
          'p' => 'memasarkan dan menjual Layanan sesuai ketentuan Perjanjian, standar operasional, dan kebijakan PIHAK PERTAMA;',
        ),
        24 => 
        array (
          'p' => 'membayar seluruh kewajiban finansial kepada PIHAK PERTAMA tepat waktu;',
        ),
        25 => 
        array (
          'p' => 'menjaga kualitas pelayanan kepada Pelanggan dan menjadi pihak yang bertanggung jawab atas hubungan komersial dengan Pelanggan;',
        ),
        26 => 
        array (
          'p' => 'melaporkan seluruh data pelanggan, penjualan, perubahan layanan, dan informasi lain pada sistem PIHAK PERTAMA yang dipersyaratkan secara lengkap, benar, dan tepat waktu;',
        ),
        27 => 
        array (
          'p' => 'menjaga keamanan jaringan, perangkat, akun, kata sandi, dan akses yang diberikan oleh PIHAK PERTAMA;',
        ),
        28 => 
        array (
          'p' => 'tidak menyalahgunakan jaringan, menyembunyikan data pelanggan, melakukan manipulasi data, menggunakan jaringan untuk kegiatan yang melanggar hukum, atau melakukan tindakan lain yang dapat merugikan PIHAK PERTAMA;',
        ),
        29 => 
        array (
          'p' => 'mematuhi seluruh ketentuan teknis, operasional, keamanan informasi, serta peraturan perundang-undangan yang berlaku.',
        ),
        30 => 
        array (
          'p' => 'Ketentuan Umum',
        ),
        31 => 
        array (
          'p' => 'Masing-masing PIHAK wajib melaksanakan hak dan kewajibannya dengan itikad baik, profesional, dan sesuai dengan prinsip kehati-hatian.',
        ),
        32 => 
        array (
          'p' => 'Hak dan kewajiban yang diatur dalam Pasal ini tidak mengurangi hak dan kewajiban lain yang diatur dalam Perjanjian ini maupun peraturan perundang-undangan yang berlaku',
        ),
      ),
    ),
    4 => 
    array (
      'judul' => 'BIAYA DAN CARA PEMBAYARAN',
      'text' => 'Biaya yang wajib dibayarkan oleh PIHAK KEDUA kepada PIHAK PERTAMA meliputi biaya Layanan Akses Internet internet dan biaya administrasi bulanan;

Biaya Layanan dan Instalasi jasa Layanan Akses Internet sebagaimana diuraikan dalam Lampiran I;

Biaya administrasi bulanan sebesar 5% (lima persen) sesuai dengan pendapatan kotor PIHAK KEDUA;

Biaya sebagaimana dimaksud dalam ayat (3) sudah termasuk Biaya Hak Penyelenggaraan (BHP), Universal Service Obligation (USO), Pajak Pertambahan Nilai Dalam Negeri (PPnDn) dan Pajak Penghasilan (PPh) sesuai ketentuan perundang-undangan yang berlaku;

Biaya jasa Layanan Akses Internet dan biaya administrasi sebagaimana tersebut dalam ayat (2) dan ayat (3) wajib dibayarkan oleh PIHAK KEDUA kepada PIHAK PERTAMA setiap bulan. Tagihan wajib dibayarkan maksimal 7 Hari Kerja sejak tanggal tagihan (invoice) dikirimkan;

Seluruh pembayaran dilakukan melalui transfer ke Rekening

Bank Mandiri

No. Rekening: 1340001209104

Atas Nama	: PT Bina Informatika Solusi

Bank Rakyat Indonesia

No. Rekening: 010701003038305

Atas Nama   : PT Bina Informatika Solusi',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Biaya yang wajib dibayarkan oleh PIHAK KEDUA kepada PIHAK PERTAMA meliputi biaya Layanan Akses Internet internet dan biaya administrasi bulanan;',
        ),
        1 => 
        array (
          'p' => 'Biaya Layanan dan Instalasi jasa Layanan Akses Internet sebagaimana diuraikan dalam Lampiran I;',
        ),
        2 => 
        array (
          'p' => 'Biaya administrasi bulanan sebesar 5% (lima persen) sesuai dengan pendapatan kotor PIHAK KEDUA;',
        ),
        3 => 
        array (
          'p' => 'Biaya sebagaimana dimaksud dalam ayat (3) sudah termasuk Biaya Hak Penyelenggaraan (BHP), Universal Service Obligation (USO), Pajak Pertambahan Nilai Dalam Negeri (PPnDn) dan Pajak Penghasilan (PPh) sesuai ketentuan perundang-undangan yang berlaku;',
        ),
        4 => 
        array (
          'p' => 'Biaya jasa Layanan Akses Internet dan biaya administrasi sebagaimana tersebut dalam ayat (2) dan ayat (3) wajib dibayarkan oleh PIHAK KEDUA kepada PIHAK PERTAMA setiap bulan. Tagihan wajib dibayarkan maksimal 7 Hari Kerja sejak tanggal tagihan (invoice) dikirimkan;',
        ),
        5 => 
        array (
          'p' => 'Seluruh pembayaran dilakukan melalui transfer ke Rekening',
        ),
        6 => 
        array (
          'p' => 'Bank Mandiri',
        ),
        7 => 
        array (
          'p' => 'No. Rekening: 1340001209104',
        ),
        8 => 
        array (
          'p' => 'Atas Nama	: PT Bina Informatika Solusi',
        ),
        9 => 
        array (
          'p' => 'Bank Rakyat Indonesia',
        ),
        10 => 
        array (
          'p' => 'No. Rekening: 010701003038305',
        ),
        11 => 
        array (
          'p' => 'Atas Nama   : PT Bina Informatika Solusi',
        ),
      ),
    ),
    5 => 
    array (
      'judul' => 'TANGGUNGJAWAB PENAGIHAN',
      'text' => 'Penagihan biaya Layanan Akses Internet dari Pelanggan dilakukan oleh PIHAK KEDUA;

PIHAK KEDUA bertanggungjawab atas upaya-upaya penagihan kepada Pelanggan;

Penagihan kepada Pelanggan menjadi dasar bagi PARA PIHAK untuk melakukan perhitungan untuk biaya administrasi.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Penagihan biaya Layanan Akses Internet dari Pelanggan dilakukan oleh PIHAK KEDUA;',
        ),
        1 => 
        array (
          'p' => 'PIHAK KEDUA bertanggungjawab atas upaya-upaya penagihan kepada Pelanggan;',
        ),
        2 => 
        array (
          'p' => 'Penagihan kepada Pelanggan menjadi dasar bagi PARA PIHAK untuk melakukan perhitungan untuk biaya administrasi.',
        ),
      ),
    ),
    6 => 
    array (
      'judul' => 'PENGADUAN PELANGGAN',
      'text' => 'PIHAK KEDUA wajib untuk menyediakan form pengaduan Pelanggan;

Mekanisme dan Standar Operasional Prosedur (S.O.P) untuk pengaduan Pelanggan tercantum dalam Lampiran III.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA wajib untuk menyediakan form pengaduan Pelanggan;',
        ),
        1 => 
        array (
          'p' => 'Mekanisme dan Standar Operasional Prosedur (S.O.P) untuk pengaduan Pelanggan tercantum dalam Lampiran III.',
        ),
      ),
    ),
    7 => 
    array (
      'judul' => 'ISOLIR',
      'text' => 'Apabila PIHAK KEDUA melalaikan kewajiban sebagaimana   dimaksud dalam Pasal 5 ayat (5), maka PIHAK PERTAMA akan memberikan Surat Peringatan kepada PIHAK KEDUA sebanyak 3 (tiga) kali dengan jangka waktu masing-masing Surat Peringatan selama 7 (tujuh) hari kerja. Apabila sampai Surat Peringatan ke-2, PIHAK KEDUA belum melakukan pembayaran, maka PIHAK PERTAMA berhak melakukan Isolir terhadap Layanan Akses Internet;

PIHAK PERTAMA akan membuka Isolir dalam waktu selambat-lambatnya 3 (tiga) Hari Kerja setelah PIHAK KEDUA membayar seluruh kewajibannya;

Apabila dalam jangka waktu 7 (tujuh) Hari Kerja sejak terjadinya Isolir sebagaimana yang dimaksud dalam ayat (1) Pasal ini, PIHAK KEDUA belum memenuhi kewajibannya, maka PIHAK PERTAMA akan melakukan Pemutusan Layanan Permanen;

Dalam hal terjadi Pemutusan Layanan Permanen sebagaimana dimaksud dalam ayat (3), maka PIHAK KEDUA tetap wajib membayar segala kewajibannya yang belum terlaksana kepada PIHAK PERTAMA selambat-lambatnya 7 (tujuh) Hari Kerja sejak Pemutusan Layanan Permanen dilakukan oleh PIHAK PERTAMA.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Apabila PIHAK KEDUA melalaikan kewajiban sebagaimana   dimaksud dalam Pasal 5 ayat (5), maka PIHAK PERTAMA akan memberikan Surat Peringatan kepada PIHAK KEDUA sebanyak 3 (tiga) kali dengan jangka waktu masing-masing Surat Peringatan selama 7 (tujuh) hari kerja. Apabila sampai Surat Peringatan ke-2, PIHAK KEDUA belum melakukan pembayaran, maka PIHAK PERTAMA berhak melakukan Isolir terhadap Layanan Akses Internet;',
        ),
        1 => 
        array (
          'p' => 'PIHAK PERTAMA akan membuka Isolir dalam waktu selambat-lambatnya 3 (tiga) Hari Kerja setelah PIHAK KEDUA membayar seluruh kewajibannya;',
        ),
        2 => 
        array (
          'p' => 'Apabila dalam jangka waktu 7 (tujuh) Hari Kerja sejak terjadinya Isolir sebagaimana yang dimaksud dalam ayat (1) Pasal ini, PIHAK KEDUA belum memenuhi kewajibannya, maka PIHAK PERTAMA akan melakukan Pemutusan Layanan Permanen;',
        ),
        3 => 
        array (
          'p' => 'Dalam hal terjadi Pemutusan Layanan Permanen sebagaimana dimaksud dalam ayat (3), maka PIHAK KEDUA tetap wajib membayar segala kewajibannya yang belum terlaksana kepada PIHAK PERTAMA selambat-lambatnya 7 (tujuh) Hari Kerja sejak Pemutusan Layanan Permanen dilakukan oleh PIHAK PERTAMA.',
        ),
      ),
    ),
    8 => 
    array (
      'judul' => 'DATA, KERAHASIAAN, DAN PERLINDUNGAN DATA',
      'text' => 'PARA PIHAK wajib menjaga kerahasiaan seluruh data, informasi, dokumen, sistem, maupun informasi lain yang diperoleh sehubungan dengan pelaksanaan Perjanjian ini dan tidak mengungkapkannya kepada pihak lain tanpa persetujuan tertulis dari pihak yang berhak, kecuali diwajibkan berdasarkan ketentuan peraturan perundang-undangan atau perintah instansi yang berwenang.

Seluruh data operasional jaringan, data penggunaan layanan, konfigurasi sistem, dokumentasi teknis, serta data lain yang dihasilkan atau tersimpan dalam sistem milik PIHAK PERTAMA merupakan milik PIHAK PERTAMA.

PIHAK PERTAMA berhak menggunakan, mengolah, menyimpan, dan mengakses data sebagaimana dimaksud pada ayat (3) untuk kepentingan operasional, pemeliharaan jaringan, peningkatan kualitas layanan, audit, keamanan sistem, pemenuhan kewajiban hukum, serta tujuan lain yang berkaitan dengan penyelenggaraan Layanan, dengan tetap memperhatikan ketentuan peraturan perundang-undangan.

PIHAK KEDUA tidak diperkenankan mengakses, menyalin, mengubah, memindahtangankan, memperjualbelikan, atau menggunakan data maupun informasi milik PIHAK PERTAMA di luar pelaksanaan Perjanjian tanpa persetujuan tertulis dari PIHAK PERTAMA.

Kewajiban menjaga kerahasiaan sebagaimana dimaksud dalam Pasal ini tetap berlaku selama Perjanjian berlangsung dan selama 5 (lima) tahun setelah Perjanjian berakhir atau jangka waktu lain yang diwajibkan berdasarkan ketentuan peraturan perundang-undangan.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PARA PIHAK wajib menjaga kerahasiaan seluruh data, informasi, dokumen, sistem, maupun informasi lain yang diperoleh sehubungan dengan pelaksanaan Perjanjian ini dan tidak mengungkapkannya kepada pihak lain tanpa persetujuan tertulis dari pihak yang berhak, kecuali diwajibkan berdasarkan ketentuan peraturan perundang-undangan atau perintah instansi yang berwenang.',
        ),
        1 => 
        array (
          'p' => 'Seluruh data operasional jaringan, data penggunaan layanan, konfigurasi sistem, dokumentasi teknis, serta data lain yang dihasilkan atau tersimpan dalam sistem milik PIHAK PERTAMA merupakan milik PIHAK PERTAMA.',
        ),
        2 => 
        array (
          'p' => 'PIHAK PERTAMA berhak menggunakan, mengolah, menyimpan, dan mengakses data sebagaimana dimaksud pada ayat (3) untuk kepentingan operasional, pemeliharaan jaringan, peningkatan kualitas layanan, audit, keamanan sistem, pemenuhan kewajiban hukum, serta tujuan lain yang berkaitan dengan penyelenggaraan Layanan, dengan tetap memperhatikan ketentuan peraturan perundang-undangan.',
        ),
        3 => 
        array (
          'p' => 'PIHAK KEDUA tidak diperkenankan mengakses, menyalin, mengubah, memindahtangankan, memperjualbelikan, atau menggunakan data maupun informasi milik PIHAK PERTAMA di luar pelaksanaan Perjanjian tanpa persetujuan tertulis dari PIHAK PERTAMA.',
        ),
        4 => 
        array (
          'p' => 'Kewajiban menjaga kerahasiaan sebagaimana dimaksud dalam Pasal ini tetap berlaku selama Perjanjian berlangsung dan selama 5 (lima) tahun setelah Perjanjian berakhir atau jangka waktu lain yang diwajibkan berdasarkan ketentuan peraturan perundang-undangan.',
        ),
      ),
    ),
    9 => 
    array (
      'judul' => 'PERANGKAT DAN INFRASTRUKTUR',
      'text' => 'Seluruh perangkat, jaringan, infrastruktur, aplikasi, sistem, dan fasilitas pendukung yang disediakan oleh PIHAK PERTAMA untuk penyelenggaraan Layanan tetap menjadi milik PIHAK PERTAMA, kecuali disepakati lain secara tertulis.

PIHAK KEDUA wajib menggunakan, menjaga, dan memelihara seluruh perangkat yang dikuasainya sesuai dengan petunjuk penggunaan serta tidak mengalihkan, memindahtangankan, menyewakan, menjaminkan, memodifikasi, atau menggunakannya untuk kepentingan selain pelaksanaan Perjanjian tanpa persetujuan tertulis dari PIHAK PERTAMA.

PIHAK KEDUA bertanggung jawab atas kehilangan, kerusakan, atau penyalahgunaan perangkat yang berada dalam penguasaannya, kecuali apabila disebabkan oleh cacat bawaan perangkat atau kesalahan PIHAK PERTAMA.

PIHAK PERTAMA berhak melakukan pemeriksaan, pemeliharaan, penggantian, peningkatan, relokasi, atau penarikan perangkat apabila diperlukan untuk kepentingan operasional, keamanan jaringan, pemeliharaan, atau berakhirnya Perjanjian.

Dalam hal Perjanjian berakhir karena sebab apa pun, PIHAK KEDUA wajib mengembalikan seluruh perangkat milik PIHAK PERTAMA dalam kondisi baik sesuai pemakaian yang wajar paling lambat 14 (empat belas) Hari Kalender sejak tanggal berakhirnya Perjanjian, kecuali disepakati lain secara tertulis.

Apabila PIHAK KEDUA tidak mengembalikan perangkat sebagaimana dimaksud pada ayat (5), PIHAK PERTAMA berhak melakukan penagihan atas nilai penggantian perangkat dan/atau menempuh upaya hukum sesuai dengan ketentuan peraturan perundang-undangan dan Perjanjian ini.

Ketentuan mengenai spesifikasi perangkat, instalasi, pemeliharaan, penggantian, dan pengembalian perangkat diatur lebih lanjut dalam Lampiran yang merupakan bagian tidak terpisahkan dari Perjanjian ini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Seluruh perangkat, jaringan, infrastruktur, aplikasi, sistem, dan fasilitas pendukung yang disediakan oleh PIHAK PERTAMA untuk penyelenggaraan Layanan tetap menjadi milik PIHAK PERTAMA, kecuali disepakati lain secara tertulis.',
        ),
        1 => 
        array (
          'p' => 'PIHAK KEDUA wajib menggunakan, menjaga, dan memelihara seluruh perangkat yang dikuasainya sesuai dengan petunjuk penggunaan serta tidak mengalihkan, memindahtangankan, menyewakan, menjaminkan, memodifikasi, atau menggunakannya untuk kepentingan selain pelaksanaan Perjanjian tanpa persetujuan tertulis dari PIHAK PERTAMA.',
        ),
        2 => 
        array (
          'p' => 'PIHAK KEDUA bertanggung jawab atas kehilangan, kerusakan, atau penyalahgunaan perangkat yang berada dalam penguasaannya, kecuali apabila disebabkan oleh cacat bawaan perangkat atau kesalahan PIHAK PERTAMA.',
        ),
        3 => 
        array (
          'p' => 'PIHAK PERTAMA berhak melakukan pemeriksaan, pemeliharaan, penggantian, peningkatan, relokasi, atau penarikan perangkat apabila diperlukan untuk kepentingan operasional, keamanan jaringan, pemeliharaan, atau berakhirnya Perjanjian.',
        ),
        4 => 
        array (
          'p' => 'Dalam hal Perjanjian berakhir karena sebab apa pun, PIHAK KEDUA wajib mengembalikan seluruh perangkat milik PIHAK PERTAMA dalam kondisi baik sesuai pemakaian yang wajar paling lambat 14 (empat belas) Hari Kalender sejak tanggal berakhirnya Perjanjian, kecuali disepakati lain secara tertulis.',
        ),
        5 => 
        array (
          'p' => 'Apabila PIHAK KEDUA tidak mengembalikan perangkat sebagaimana dimaksud pada ayat (5), PIHAK PERTAMA berhak melakukan penagihan atas nilai penggantian perangkat dan/atau menempuh upaya hukum sesuai dengan ketentuan peraturan perundang-undangan dan Perjanjian ini.',
        ),
        6 => 
        array (
          'p' => 'Ketentuan mengenai spesifikasi perangkat, instalasi, pemeliharaan, penggantian, dan pengembalian perangkat diatur lebih lanjut dalam Lampiran yang merupakan bagian tidak terpisahkan dari Perjanjian ini.',
        ),
      ),
    ),
    10 => 
    array (
      'judul' => 'MEREK DAN HAK KEKAYAAN INTELEKTUAL',
      'text' => 'Seluruh hak atas merek, logo, nama dagang, hak cipta, perangkat lunak, desain, dokumentasi, sistem, aplikasi, jaringan, serta Hak Kekayaan Intelektual lainnya yang digunakan dalam penyelenggaraan Layanan merupakan milik PIHAK PERTAMA atau pihak lain yang memberikan hak penggunaannya kepada PIHAK PERTAMA.

PIHAK PERTAMA memberikan kepada PIHAK KEDUA hak yang terbatas, tidak eksklusif, tidak dapat dialihkan, dan tidak dapat disublisensikan untuk menggunakan merek, logo, dan materi promosi milik PIHAK PERTAMA semata-mata dalam rangka pelaksanaan Perjanjian ini.

PIHAK KEDUA wajib menggunakan merek, logo, dan identitas perusahaan milik PIHAK PERTAMA sesuai dengan pedoman, standar, dan ketentuan yang ditetapkan oleh PIHAK PERTAMA serta tidak melakukan perubahan, penghapusan, atau penggunaan yang dapat merugikan nama baik PIHAK PERTAMA.

PIHAK KEDUA dilarang mendaftarkan, menggunakan, meniru, atau mengklaim kepemilikan atas merek, logo, nama dagang, nama domain, desain, atau Hak Kekayaan Intelektual lainnya yang mempunyai persamaan atau kemiripan dengan milik PIHAK PERTAMA tanpa persetujuan tertulis dari PIHAK PERTAMA.

Berakhirnya Perjanjian ini mengakibatkan seluruh hak penggunaan merek, logo, dan Hak Kekayaan Intelektual yang diberikan kepada PIHAK KEDUA berakhir secara otomatis. PIHAK KEDUA wajib segera menghentikan seluruh penggunaan serta menghapus atau mengembalikan seluruh materi yang memuat identitas PIHAK PERTAMA, kecuali diwajibkan lain oleh ketentuan peraturan perundang-undangan.

Pelanggaran terhadap ketentuan dalam Pasal ini memberikan hak kepada PIHAK PERTAMA untuk mencabut hak penggunaan merek, menghentikan kerja sama, menuntut ganti rugi, dan/atau menempuh upaya hukum sesuai dengan ketentuan Perjanjian dan peraturan perundang-undangan yang berlaku.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Seluruh hak atas merek, logo, nama dagang, hak cipta, perangkat lunak, desain, dokumentasi, sistem, aplikasi, jaringan, serta Hak Kekayaan Intelektual lainnya yang digunakan dalam penyelenggaraan Layanan merupakan milik PIHAK PERTAMA atau pihak lain yang memberikan hak penggunaannya kepada PIHAK PERTAMA.',
        ),
        1 => 
        array (
          'p' => 'PIHAK PERTAMA memberikan kepada PIHAK KEDUA hak yang terbatas, tidak eksklusif, tidak dapat dialihkan, dan tidak dapat disublisensikan untuk menggunakan merek, logo, dan materi promosi milik PIHAK PERTAMA semata-mata dalam rangka pelaksanaan Perjanjian ini.',
        ),
        2 => 
        array (
          'p' => 'PIHAK KEDUA wajib menggunakan merek, logo, dan identitas perusahaan milik PIHAK PERTAMA sesuai dengan pedoman, standar, dan ketentuan yang ditetapkan oleh PIHAK PERTAMA serta tidak melakukan perubahan, penghapusan, atau penggunaan yang dapat merugikan nama baik PIHAK PERTAMA.',
        ),
        3 => 
        array (
          'p' => 'PIHAK KEDUA dilarang mendaftarkan, menggunakan, meniru, atau mengklaim kepemilikan atas merek, logo, nama dagang, nama domain, desain, atau Hak Kekayaan Intelektual lainnya yang mempunyai persamaan atau kemiripan dengan milik PIHAK PERTAMA tanpa persetujuan tertulis dari PIHAK PERTAMA.',
        ),
        4 => 
        array (
          'p' => 'Berakhirnya Perjanjian ini mengakibatkan seluruh hak penggunaan merek, logo, dan Hak Kekayaan Intelektual yang diberikan kepada PIHAK KEDUA berakhir secara otomatis. PIHAK KEDUA wajib segera menghentikan seluruh penggunaan serta menghapus atau mengembalikan seluruh materi yang memuat identitas PIHAK PERTAMA, kecuali diwajibkan lain oleh ketentuan peraturan perundang-undangan.',
        ),
        5 => 
        array (
          'p' => 'Pelanggaran terhadap ketentuan dalam Pasal ini memberikan hak kepada PIHAK PERTAMA untuk mencabut hak penggunaan merek, menghentikan kerja sama, menuntut ganti rugi, dan/atau menempuh upaya hukum sesuai dengan ketentuan Perjanjian dan peraturan perundang-undangan yang berlaku.',
        ),
      ),
    ),
    11 => 
    array (
      'judul' => 'WANPRESTASI DAN SANKSI',
      'text' => 'PIHAK KEDUA dinyatakan melakukan wanprestasi apabila:

tidak memenuhi kewajibannya berdasarkan Perjanjian;

terlambat melakukan pembayaran sesuai dengan ketentuan Perjanjian;

memberikan data atau informasi yang tidak benar;

melanggar ketentuan mengenai penggunaan Layanan, perangkat, data, atau Hak Kekayaan Intelektual;

menggunakan Layanan untuk kegiatan yang melanggar hukum; atau

melakukan tindakan lain yang mengakibatkan kerugian bagi PIHAK PERTAMA.

Dalam hal PIHAK KEDUA melakukan wanprestasi, PIHAK PERTAMA berhak memberikan sanksi secara bertahap sesuai dengan tingkat pelanggaran berupa:
a. teguran tertulis;
b. penangguhan aktivasi atau layanan tertentu;
c. pembatasan atau isolir Layanan;
d. pengenaan denda atau penalti;
e. pemutusan sebagian atau seluruh Layanan; dan/atau
f. pengakhiran Perjanjian.

PIHAK PERTAMA berhak menjatuhkan salah satu atau beberapa sanksi sebagaimana dimaksud pada ayat (2) tanpa harus menerapkannya secara berurutan, apabila menurut penilaian PIHAK PERTAMA pelanggaran yang dilakukan bersifat material atau berpotensi menimbulkan kerugian bagi PIHAK PERTAMA, Pelanggan, atau pihak lain.

Dalam hal PIHAK KEDUA mengakhiri Perjanjian sebelum berakhirnya jangka waktu yang disepakati atau menyebabkan Perjanjian diakhiri karena wanprestasi PIHAK KEDUA, maka PIHAK KEDUA wajib membayar penalti sesuai dengan ketentuan yang tercantum dalam Lampiran atau addendum yang merupakan bagian tidak terpisahkan dari Perjanjian ini.

Pengenaan sanksi, denda, atau penalti tidak menghapus kewajiban PIHAK KEDUA untuk:
a. melunasi seluruh kewajiban pembayaran;
b. mengembalikan perangkat milik PIHAK PERTAMA;
c. mengganti kerugian yang timbul akibat pelanggaran; dan
d. memenuhi kewajiban lain berdasarkan Perjanjian.

PIHAK PERTAMA berhak menagih seluruh kerugian yang timbul akibat wanprestasi PIHAK KEDUA, termasuk biaya penagihan, biaya pemulihan jaringan, biaya hukum, dan kerugian lain yang dapat dibuktikan sesuai dengan ketentuan peraturan perundang-undangan.

Apabila PIHAK KEDUA tidak memperbaiki wanprestasi dalam jangka waktu yang ditetapkan oleh PIHAK PERTAMA atau melakukan pelanggaran yang bersifat material, PIHAK PERTAMA berhak mengakhiri Perjanjian secara sepihak melalui pemberitahuan tertulis tanpa mengurangi hak PIHAK PERTAMA untuk menuntut pemenuhan kewajiban, ganti rugi, maupun upaya hukum lainnya sesuai dengan ketentuan Perjanjian dan peraturan perundang-undangan yang berlaku.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA dinyatakan melakukan wanprestasi apabila:',
        ),
        1 => 
        array (
          'p' => 'tidak memenuhi kewajibannya berdasarkan Perjanjian;',
        ),
        2 => 
        array (
          'p' => 'terlambat melakukan pembayaran sesuai dengan ketentuan Perjanjian;',
        ),
        3 => 
        array (
          'p' => 'memberikan data atau informasi yang tidak benar;',
        ),
        4 => 
        array (
          'p' => 'melanggar ketentuan mengenai penggunaan Layanan, perangkat, data, atau Hak Kekayaan Intelektual;',
        ),
        5 => 
        array (
          'p' => 'menggunakan Layanan untuk kegiatan yang melanggar hukum; atau',
        ),
        6 => 
        array (
          'p' => 'melakukan tindakan lain yang mengakibatkan kerugian bagi PIHAK PERTAMA.',
        ),
        7 => 
        array (
          'p' => 'Dalam hal PIHAK KEDUA melakukan wanprestasi, PIHAK PERTAMA berhak memberikan sanksi secara bertahap sesuai dengan tingkat pelanggaran berupa:
a. teguran tertulis;
b. penangguhan aktivasi atau layanan tertentu;
c. pembatasan atau isolir Layanan;
d. pengenaan denda atau penalti;
e. pemutusan sebagian atau seluruh Layanan; dan/atau
f. pengakhiran Perjanjian.',
        ),
        8 => 
        array (
          'p' => 'PIHAK PERTAMA berhak menjatuhkan salah satu atau beberapa sanksi sebagaimana dimaksud pada ayat (2) tanpa harus menerapkannya secara berurutan, apabila menurut penilaian PIHAK PERTAMA pelanggaran yang dilakukan bersifat material atau berpotensi menimbulkan kerugian bagi PIHAK PERTAMA, Pelanggan, atau pihak lain.',
        ),
        9 => 
        array (
          'p' => 'Dalam hal PIHAK KEDUA mengakhiri Perjanjian sebelum berakhirnya jangka waktu yang disepakati atau menyebabkan Perjanjian diakhiri karena wanprestasi PIHAK KEDUA, maka PIHAK KEDUA wajib membayar penalti sesuai dengan ketentuan yang tercantum dalam Lampiran atau addendum yang merupakan bagian tidak terpisahkan dari Perjanjian ini.',
        ),
        10 => 
        array (
          'p' => 'Pengenaan sanksi, denda, atau penalti tidak menghapus kewajiban PIHAK KEDUA untuk:
a. melunasi seluruh kewajiban pembayaran;
b. mengembalikan perangkat milik PIHAK PERTAMA;
c. mengganti kerugian yang timbul akibat pelanggaran; dan
d. memenuhi kewajiban lain berdasarkan Perjanjian.',
        ),
        11 => 
        array (
          'p' => 'PIHAK PERTAMA berhak menagih seluruh kerugian yang timbul akibat wanprestasi PIHAK KEDUA, termasuk biaya penagihan, biaya pemulihan jaringan, biaya hukum, dan kerugian lain yang dapat dibuktikan sesuai dengan ketentuan peraturan perundang-undangan.',
        ),
        12 => 
        array (
          'p' => 'Apabila PIHAK KEDUA tidak memperbaiki wanprestasi dalam jangka waktu yang ditetapkan oleh PIHAK PERTAMA atau melakukan pelanggaran yang bersifat material, PIHAK PERTAMA berhak mengakhiri Perjanjian secara sepihak melalui pemberitahuan tertulis tanpa mengurangi hak PIHAK PERTAMA untuk menuntut pemenuhan kewajiban, ganti rugi, maupun upaya hukum lainnya sesuai dengan ketentuan Perjanjian dan peraturan perundang-undangan yang berlaku.',
        ),
      ),
    ),
    12 => 
    array (
      'judul' => 'PENGAKHIRAN PERJANJIAN',
      'text' => 'Perjanjian ini berakhir apabila:

jangka waktu Perjanjian berakhir dan tidak diperpanjang;

PARA PIHAK sepakat secara tertulis untuk mengakhiri Perjanjian;

diakhiri oleh salah satu pihak sesuai dengan ketentuan Perjanjian ini; atau

terjadi keadaan lain yang berdasarkan ketentuan peraturan perundang-undangan mengakibatkan Perjanjian tidak dapat dilaksanakan.

PIHAK PERTAMA berhak mengakhiri Perjanjian secara sepihak dengan pemberitahuan tertulis apabila PIHAK KEDUA:

melakukan wanprestasi yang tidak diperbaiki dalam jangka waktu yang ditentukan;

melakukan pelanggaran yang bersifat material;

dinyatakan pailit, dibubarkan, atau menghentikan kegiatan usahanya;

menggunakan Layanan untuk kegiatan yang melanggar hukum atau merugikan PIHAK PERTAMA; atau

tidak lagi memenuhi persyaratan sebagai mitra berdasarkan ketentuan yang ditetapkan oleh PIHAK PERTAMA.

Pengakhiran Perjanjian tidak menghapus hak dan kewajiban PARA PIHAK yang telah timbul sebelum tanggal efektif pengakhiran, termasuk kewajiban pembayaran, denda, penalti, ganti rugi, pengembalian perangkat, serta kewajiban lainnya berdasarkan Perjanjian.

Sejak tanggal efektif pengakhiran Perjanjian:

PIHAK KEDUA wajib menghentikan penggunaan merek, logo, sistem, aplikasi, dan fasilitas milik PIHAK PERTAMA;

PIHAK KEDUA wajib mengembalikan seluruh perangkat, dokumen, data, dan aset milik PIHAK PERTAMA yang berada dalam penguasaannya; dan

PIHAK PERTAMA berhak menghentikan akses PIHAK KEDUA terhadap sistem dan Layanan.

PARA PIHAK sepakat dan setuju untuk mengesampikan berlakunya Pasal 1266 KUHPerdata, sehingga Pemutusan Perjanjian ini dapat dilakukan oleh PARA PIHAK tanpa terlebih dahulu menunggu Putusan Pengadilan.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Perjanjian ini berakhir apabila:',
        ),
        1 => 
        array (
          'p' => 'jangka waktu Perjanjian berakhir dan tidak diperpanjang;',
        ),
        2 => 
        array (
          'p' => 'PARA PIHAK sepakat secara tertulis untuk mengakhiri Perjanjian;',
        ),
        3 => 
        array (
          'p' => 'diakhiri oleh salah satu pihak sesuai dengan ketentuan Perjanjian ini; atau',
        ),
        4 => 
        array (
          'p' => 'terjadi keadaan lain yang berdasarkan ketentuan peraturan perundang-undangan mengakibatkan Perjanjian tidak dapat dilaksanakan.',
        ),
        5 => 
        array (
          'p' => 'PIHAK PERTAMA berhak mengakhiri Perjanjian secara sepihak dengan pemberitahuan tertulis apabila PIHAK KEDUA:',
        ),
        6 => 
        array (
          'p' => 'melakukan wanprestasi yang tidak diperbaiki dalam jangka waktu yang ditentukan;',
        ),
        7 => 
        array (
          'p' => 'melakukan pelanggaran yang bersifat material;',
        ),
        8 => 
        array (
          'p' => 'dinyatakan pailit, dibubarkan, atau menghentikan kegiatan usahanya;',
        ),
        9 => 
        array (
          'p' => 'menggunakan Layanan untuk kegiatan yang melanggar hukum atau merugikan PIHAK PERTAMA; atau',
        ),
        10 => 
        array (
          'p' => 'tidak lagi memenuhi persyaratan sebagai mitra berdasarkan ketentuan yang ditetapkan oleh PIHAK PERTAMA.',
        ),
        11 => 
        array (
          'p' => 'Pengakhiran Perjanjian tidak menghapus hak dan kewajiban PARA PIHAK yang telah timbul sebelum tanggal efektif pengakhiran, termasuk kewajiban pembayaran, denda, penalti, ganti rugi, pengembalian perangkat, serta kewajiban lainnya berdasarkan Perjanjian.',
        ),
        12 => 
        array (
          'p' => 'Sejak tanggal efektif pengakhiran Perjanjian:',
        ),
        13 => 
        array (
          'p' => 'PIHAK KEDUA wajib menghentikan penggunaan merek, logo, sistem, aplikasi, dan fasilitas milik PIHAK PERTAMA;',
        ),
        14 => 
        array (
          'p' => 'PIHAK KEDUA wajib mengembalikan seluruh perangkat, dokumen, data, dan aset milik PIHAK PERTAMA yang berada dalam penguasaannya; dan',
        ),
        15 => 
        array (
          'p' => 'PIHAK PERTAMA berhak menghentikan akses PIHAK KEDUA terhadap sistem dan Layanan.',
        ),
        16 => 
        array (
          'p' => 'PARA PIHAK sepakat dan setuju untuk mengesampikan berlakunya Pasal 1266 KUHPerdata, sehingga Pemutusan Perjanjian ini dapat dilakukan oleh PARA PIHAK tanpa terlebih dahulu menunggu Putusan Pengadilan.',
        ),
      ),
    ),
    13 => 
    array (
      'judul' => 'PERTANGGUNGJAWABAN TERHADAP PIHAK KETIGA',
      'text' => 'Dalam hal terjadi Pemutusan Layanan Permanen sebagaimana dimaksud dalam Pasal 13 ayat (2), PIHAK PERTAMA tidak bertanggung jawab atas hubungan hukum, hak, kewajiban, kerugian, maupun tuntutan antara PIHAK KEDUA dan Pelanggan.

Untuk menjaga keberlangsungan Layanan Akses Internet kepada Pelanggan, PIHAK PERTAMA berhak menawarkan layanan secara langsung kepada Pelanggan atau memfasilitasi pengalihan layanan kepada Penyelenggara Jasa Layanan Akses Internet lainnya, sepanjang memungkinkan secara teknis dan sesuai dengan ketentuan yang berlaku.

PIHAK KEDUA menjamin bahwa Pelanggan telah memperoleh informasi mengenai kemungkinan pengalihan layanan sebagaimana dimaksud pada ayat (2), serta membebaskan dan melepaskan PIHAK PERTAMA dari setiap tuntutan, gugatan, atau klaim yang timbul sehubungan dengan berakhirnya Perjanjian ini atau berakhirnya hubungan hukum antara PIHAK KEDUA dan Pelanggan.

Pelaksanaan ketentuan dalam Pasal ini tidak menghapus kewajiban PARA PIHAK yang masih harus diselesaikan berdasarkan Perjanjian ini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Dalam hal terjadi Pemutusan Layanan Permanen sebagaimana dimaksud dalam Pasal 13 ayat (2), PIHAK PERTAMA tidak bertanggung jawab atas hubungan hukum, hak, kewajiban, kerugian, maupun tuntutan antara PIHAK KEDUA dan Pelanggan.',
        ),
        1 => 
        array (
          'p' => 'Untuk menjaga keberlangsungan Layanan Akses Internet kepada Pelanggan, PIHAK PERTAMA berhak menawarkan layanan secara langsung kepada Pelanggan atau memfasilitasi pengalihan layanan kepada Penyelenggara Jasa Layanan Akses Internet lainnya, sepanjang memungkinkan secara teknis dan sesuai dengan ketentuan yang berlaku.',
        ),
        2 => 
        array (
          'p' => 'PIHAK KEDUA menjamin bahwa Pelanggan telah memperoleh informasi mengenai kemungkinan pengalihan layanan sebagaimana dimaksud pada ayat (2), serta membebaskan dan melepaskan PIHAK PERTAMA dari setiap tuntutan, gugatan, atau klaim yang timbul sehubungan dengan berakhirnya Perjanjian ini atau berakhirnya hubungan hukum antara PIHAK KEDUA dan Pelanggan.',
        ),
        3 => 
        array (
          'p' => 'Pelaksanaan ketentuan dalam Pasal ini tidak menghapus kewajiban PARA PIHAK yang masih harus diselesaikan berdasarkan Perjanjian ini.',
        ),
      ),
    ),
    14 => 
    array (
      'judul' => 'EVALUASI PELAKSANAAN PEKERJAAN',
      'text' => 'PARA PIHAK sepakat melakukan evaluasi atas pelaksanaan Perjanjian ini setiap 6 (enam) bulan sejak Perjanjian mulai berlaku.

Evaluasi dilakukan melalui Forum Konsultasi yang dihadiri oleh wakil PARA PIHAK yang berwenang.

evaluasi meliputi antara lain:

pelaksanaan operasional layanan;

pemasaran dan penjualan;

pengaduan Pelanggan;

kendala teknis; dan

hal lain yang disepakati PARA PIHAK.

Hasil evaluasi dituangkan dalam berita acara atau dokumen tertulis yang menjadi dasar bagi PARA PIHAK untuk melakukan perbaikan pelaksanaan Perjanjian atau perubahan Perjanjian berdasarkan kesepakatan PARA PIHAK.

Dalam hal hasil evaluasi menunjukkan adanya kondisi yang dapat menjadi dasar pengakhiran Perjanjian, pelaksanaannya tetap mengacu pada ketentuan mengenai pemutusan Perjanjian sebagaimana diatur dalam Perjanjian ini',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PARA PIHAK sepakat melakukan evaluasi atas pelaksanaan Perjanjian ini setiap 6 (enam) bulan sejak Perjanjian mulai berlaku.',
        ),
        1 => 
        array (
          'p' => 'Evaluasi dilakukan melalui Forum Konsultasi yang dihadiri oleh wakil PARA PIHAK yang berwenang.',
        ),
        2 => 
        array (
          'p' => 'evaluasi meliputi antara lain:',
        ),
        3 => 
        array (
          'p' => 'pelaksanaan operasional layanan;',
        ),
        4 => 
        array (
          'p' => 'pemasaran dan penjualan;',
        ),
        5 => 
        array (
          'p' => 'pengaduan Pelanggan;',
        ),
        6 => 
        array (
          'p' => 'kendala teknis; dan',
        ),
        7 => 
        array (
          'p' => 'hal lain yang disepakati PARA PIHAK.',
        ),
        8 => 
        array (
          'p' => 'Hasil evaluasi dituangkan dalam berita acara atau dokumen tertulis yang menjadi dasar bagi PARA PIHAK untuk melakukan perbaikan pelaksanaan Perjanjian atau perubahan Perjanjian berdasarkan kesepakatan PARA PIHAK.',
        ),
        9 => 
        array (
          'p' => 'Dalam hal hasil evaluasi menunjukkan adanya kondisi yang dapat menjadi dasar pengakhiran Perjanjian, pelaksanaannya tetap mengacu pada ketentuan mengenai pemutusan Perjanjian sebagaimana diatur dalam Perjanjian ini',
        ),
      ),
    ),
    15 => 
    array (
      'judul' => 'KEADAAN MEMAKSA (FORCE MAJEURE)',
      'text' => 'Keadaan Memaksa (Force Majeure) adalah setiap peristiwa di luar kendali dan kemampuan wajar PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, termasuk namun tidak terbatas pada bencana alam, kebakaran, perang, kerusuhan, wabah penyakit, pemogokan, gangguan jaringan berskala besar, kegagalan sistem di luar kendali PARA PIHAK, kebijakan Pemerintah, atau peristiwa lain yang sejenis.

PIHAK yang mengalami Keadaan Memaksa wajib memberitahukan secara tertulis kepada pihak lainnya paling lambat 14 (empat belas) Hari Kalender sejak diketahui atau seharusnya diketahui terjadinya Keadaan Memaksa, disertai penjelasan mengenai dampak terhadap pelaksanaan Perjanjian.

Selama Keadaan Memaksa berlangsung, kewajiban PARA PIHAK yang terdampak ditangguhkan sepanjang tidak dapat dilaksanakan akibat Keadaan Memaksa. Penangguhan tersebut tidak menghapus kewajiban yang telah timbul sebelum terjadinya Keadaan Memaksa.

PARA PIHAK wajib melakukan upaya yang wajar untuk mengurangi dampak Keadaan Memaksa dan melanjutkan pelaksanaan Perjanjian segera setelah keadaan tersebut berakhir.

Apabila Keadaan Memaksa berlangsung lebih dari 90 (sembilan puluh) Hari Kalender secara berturut-turut dan PARA PIHAK tidak mencapai kesepakatan mengenai kelanjutan Perjanjian, masing-masing pihak berhak mengakhiri Perjanjian dengan pemberitahuan tertulis tanpa dikenakan penalti, dengan tetap menyelesaikan seluruh hak dan kewajiban yang telah timbul sebelum tanggal efektif pengakhiran.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Keadaan Memaksa (Force Majeure) adalah setiap peristiwa di luar kendali dan kemampuan wajar PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, termasuk namun tidak terbatas pada bencana alam, kebakaran, perang, kerusuhan, wabah penyakit, pemogokan, gangguan jaringan berskala besar, kegagalan sistem di luar kendali PARA PIHAK, kebijakan Pemerintah, atau peristiwa lain yang sejenis.',
        ),
        1 => 
        array (
          'p' => 'PIHAK yang mengalami Keadaan Memaksa wajib memberitahukan secara tertulis kepada pihak lainnya paling lambat 14 (empat belas) Hari Kalender sejak diketahui atau seharusnya diketahui terjadinya Keadaan Memaksa, disertai penjelasan mengenai dampak terhadap pelaksanaan Perjanjian.',
        ),
        2 => 
        array (
          'p' => 'Selama Keadaan Memaksa berlangsung, kewajiban PARA PIHAK yang terdampak ditangguhkan sepanjang tidak dapat dilaksanakan akibat Keadaan Memaksa. Penangguhan tersebut tidak menghapus kewajiban yang telah timbul sebelum terjadinya Keadaan Memaksa.',
        ),
        3 => 
        array (
          'p' => 'PARA PIHAK wajib melakukan upaya yang wajar untuk mengurangi dampak Keadaan Memaksa dan melanjutkan pelaksanaan Perjanjian segera setelah keadaan tersebut berakhir.',
        ),
        4 => 
        array (
          'p' => 'Apabila Keadaan Memaksa berlangsung lebih dari 90 (sembilan puluh) Hari Kalender secara berturut-turut dan PARA PIHAK tidak mencapai kesepakatan mengenai kelanjutan Perjanjian, masing-masing pihak berhak mengakhiri Perjanjian dengan pemberitahuan tertulis tanpa dikenakan penalti, dengan tetap menyelesaikan seluruh hak dan kewajiban yang telah timbul sebelum tanggal efektif pengakhiran.',
        ),
      ),
    ),
    16 => 
    array (
      'judul' => 'KOMUNIKASI/PEMBERITAHUAN',
      'text' => '',
      'blocks' => 
      array (
        0 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '1.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Segala pemberitahuan yang diisyaratkan atau diperkenankan menurut perjanjian kerjasama ini harus dibuat secara tertulis dan dapat dikirimkan melalui email, surat tercatat atau dikirimkan secara langsung melalui kurir kepada alamat-alamat dibawah ini:',
                  ),
                  's' => 5,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'a.',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Jika kepada PIHAK PERTAMA:',
                  ),
                  's' => 4,
                ),
              ),
              2 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => ':',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => 'PT Bina Informatika Solusi',
                  ),
                  's' => 1,
                ),
              ),
              3 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Alamat',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => ':',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => 'Jl. Prakarsa Muda No. 258 Pekiringan Kec. Kesambi Kota Cirebon',
                  ),
                  's' => 1,
                ),
              ),
              4 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Telepon',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => ':',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => '0231-247618',
                  ),
                  's' => 1,
                ),
              ),
              5 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'U.p',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => ':',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => 'Ageng Bagja Priyadi, S.T., M.Kom',
                  ),
                  's' => 1,
                ),
              ),
              6 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Email',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => ':',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => 'admin@fibertrust.id',
                  ),
                  's' => 1,
                ),
              ),
              7 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'b.',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Jika kepada PIHAK KEDUA:',
                  ),
                  's' => 4,
                ),
              ),
              8 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => ':',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => 'Adi Darmawan',
                  ),
                  's' => 1,
                ),
              ),
              9 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Alamat',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => ':',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => 'Perumahan Zada Regency 1 Blok E No.5, Rt/Rw 03/09, Desa Bulakan, Kec. Sukoharjo, Kab. Sukoharjo',
                  ),
                  's' => 1,
                ),
              ),
              10 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Telepon',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => ':',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => '0822-2562-2897',
                  ),
                  's' => 1,
                ),
              ),
              11 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'U.p',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => ':',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => 'ADI DARMAWAN',
                  ),
                  's' => 1,
                ),
              ),
              12 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Email',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => ':',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => 'adidarmawanspd@gmail.com',
                  ),
                  's' => 1,
                ),
              ),
              13 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '2.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Jika salah satu pihak mengganti atau mengubah alamatnya atau hal-hal terkait lainnya sehubungan dengan alamat ini, maka pihak tersebut harus memberitahukan penggantian dan perubahan tersebut kepada pihak lainnya.',
                  ),
                  's' => 5,
                ),
              ),
            ),
            'bordered' => false,
            'head' => false,
          ),
        ),
      ),
    ),
    17 => 
    array (
      'judul' => 'PENYELESAIAN SENGKETA DAN KETENTUAN PENUTUP',
      'text' => 'Setiap perselisihan yang timbul sehubungan dengan pelaksanaan atau penafsiran Perjanjian ini akan diselesaikan terlebih dahulu secara musyawarah untuk mufakat dalam waktu paling lama 30 (tiga puluh) Hari Kalender sejak salah satu pihak menyampaikan pemberitahuan tertulis mengenai adanya perselisihan.

Apabila musyawarah sebagaimana dimaksud pada ayat (1) tidak mencapai kesepakatan, PARA PIHAK sepakat untuk menyelesaikan perselisihan melalui Pengadilan Negeri Cirebon di wilayah hukum kedudukan PIHAK PERTAMA, tanpa mengurangi hak PIHAK PERTAMA untuk mengajukan gugatan atau tuntutan lain sesuai dengan ketentuan peraturan perundang-undangan.

Selama proses penyelesaian perselisihan berlangsung, PARA PIHAK tetap wajib melaksanakan bagian Perjanjian yang tidak menjadi objek perselisihan.

Setiap perubahan, penambahan, atau pengurangan terhadap Perjanjian ini hanya sah apabila dibuat addendum/amandemen secara tertulis dan ditandatangani oleh PARA PIHAK atau wakilnya yang sah, serta menjadi bagian yang tidak terpisahkan dari Perjanjian ini.

Apabila terdapat ketentuan dalam Perjanjian ini yang dinyatakan tidak sah, tidak berlaku, atau tidak dapat dilaksanakan berdasarkan putusan pengadilan atau ketentuan peraturan perundang-undangan, ketentuan lainnya tetap berlaku dan mengikat PARA PIHAK.

PARA PIHAK menyatakan bahwa:

telah membaca, memahami, dan menyetujui seluruh isi Perjanjian ini;

memiliki kewenangan yang sah untuk menandatangani dan melaksanakan Perjanjian ini;

Perjanjian ini dibuat tanpa adanya paksaan, kekhilafan, atau penipuan dari pihak mana pun.

Perjanjian ini dibuat dalam 2 (dua) rangkap asli yang masing-masing mempunyai kekuatan hukum yang sama, dan mulai berlaku sejak tanggal ditandatangani oleh PARA PIHAK.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Setiap perselisihan yang timbul sehubungan dengan pelaksanaan atau penafsiran Perjanjian ini akan diselesaikan terlebih dahulu secara musyawarah untuk mufakat dalam waktu paling lama 30 (tiga puluh) Hari Kalender sejak salah satu pihak menyampaikan pemberitahuan tertulis mengenai adanya perselisihan.',
        ),
        1 => 
        array (
          'p' => 'Apabila musyawarah sebagaimana dimaksud pada ayat (1) tidak mencapai kesepakatan, PARA PIHAK sepakat untuk menyelesaikan perselisihan melalui Pengadilan Negeri Cirebon di wilayah hukum kedudukan PIHAK PERTAMA, tanpa mengurangi hak PIHAK PERTAMA untuk mengajukan gugatan atau tuntutan lain sesuai dengan ketentuan peraturan perundang-undangan.',
        ),
        2 => 
        array (
          'p' => 'Selama proses penyelesaian perselisihan berlangsung, PARA PIHAK tetap wajib melaksanakan bagian Perjanjian yang tidak menjadi objek perselisihan.',
        ),
        3 => 
        array (
          'p' => 'Setiap perubahan, penambahan, atau pengurangan terhadap Perjanjian ini hanya sah apabila dibuat addendum/amandemen secara tertulis dan ditandatangani oleh PARA PIHAK atau wakilnya yang sah, serta menjadi bagian yang tidak terpisahkan dari Perjanjian ini.',
        ),
        4 => 
        array (
          'p' => 'Apabila terdapat ketentuan dalam Perjanjian ini yang dinyatakan tidak sah, tidak berlaku, atau tidak dapat dilaksanakan berdasarkan putusan pengadilan atau ketentuan peraturan perundang-undangan, ketentuan lainnya tetap berlaku dan mengikat PARA PIHAK.',
        ),
        5 => 
        array (
          'p' => 'PARA PIHAK menyatakan bahwa:',
        ),
        6 => 
        array (
          'p' => 'telah membaca, memahami, dan menyetujui seluruh isi Perjanjian ini;',
        ),
        7 => 
        array (
          'p' => 'memiliki kewenangan yang sah untuk menandatangani dan melaksanakan Perjanjian ini;',
        ),
        8 => 
        array (
          'p' => 'Perjanjian ini dibuat tanpa adanya paksaan, kekhilafan, atau penipuan dari pihak mana pun.',
        ),
        9 => 
        array (
          'p' => 'Perjanjian ini dibuat dalam 2 (dua) rangkap asli yang masing-masing mempunyai kekuatan hukum yang sama, dan mulai berlaku sejak tanggal ditandatangani oleh PARA PIHAK.',
        ),
      ),
    ),
  ),
  'tutup' => 'PIHAK PERTAMA

PT Bina Informatika Solusi

Ageng Bagja Priyadi, S.T., M.Kom

Direktur

PIHAK KEDUA

Adi Darmawan',
  'tutupBlocks' => 
  array (
    0 => 
    array (
      'table' => 
      array (
        'rows' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'c' => 
              array (
                0 => 'PIHAK PERTAMA',
                1 => 'PT Bina Informatika Solusi',
                2 => 'Ageng Bagja Priyadi, S.T., M.Kom',
                3 => 'Direktur',
              ),
              's' => 1,
            ),
            1 => 
            array (
              'c' => 
              array (
                0 => 'PIHAK KEDUA',
                1 => 'Adi Darmawan',
              ),
              's' => 1,
            ),
          ),
        ),
        'bordered' => false,
        'head' => true,
      ),
    ),
  ),
  'lampiran' => 
  array (
    0 => 
    array (
      'judul' => 'LAMPIRAN I PERJANJIAN',
      'text' => 'DESKRIPSI LAYANAN DAN KONFIGURASI

NOMOR: 196/FBT/PKS/III/2026',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'DESKRIPSI LAYANAN DAN KONFIGURASI',
        ),
        1 => 
        array (
          'p' => 'NOMOR: 196/FBT/PKS/III/2026',
        ),
        2 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama Pelanggan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Adi Darmawan',
                  ),
                  's' => 1,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Tanggal Awal Berlangganan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
              2 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Tanggal Akhir Berlangganan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
              3 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'NPWP',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '3314.1726.0693.0004',
                  ),
                  's' => 1,
                ),
              ),
              4 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Lokasi / Wilayah Layanan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Perumahan Zada Regency 1 Blok E No.5, Rt/Rw 03/09, Desa Bulakan, Kec. Sukoharjo, Kab. Sukoharjo',
                  ),
                  's' => 1,
                ),
              ),
              5 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Layanan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Dedicated Internet 1 (satu) Gbps',
                  ),
                  's' => 1,
                ),
              ),
              6 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Instalasi',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp4.500.000,- (One time charge)',
                    1 => 'Sudah termasuk PPN',
                  ),
                  's' => 1,
                ),
              ),
              7 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Layanan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp25.700.000,- (Perbulan)',
                    1 => 'Sudah termasuk PPN',
                  ),
                  's' => 1,
                ),
              ),
              8 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Registrasi',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp5.000.000,- (One time charge)',
                    1 => 'Sudah termasuk PPN',
                  ),
                  's' => 1,
                ),
              ),
              9 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Konfigurasi',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'PIHAK PERTAMA melakukan konfigurasi sampai dengan Backbone.',
                    1 => 'Pihak Kedua melakukan konfigurasi ke Pelanggan.',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
      ),
    ),
    1 => 
    array (
      'judul' => 'LAMPIRAN II PERJANJIAN',
      'text' => 'PAKET LAYANAN

NOMOR: 196/FBT/PKS/III/2026

Paket layanan meliputi persyaratan sebagai berikut:

Nama Brand FIBERTRUST

Harga setiap paket minimal Rp 100.000/2Mbps

Biaya instalasi disesuaikan dengan kebutuhan pelanggan

Paket Layanan yang dibuat harus diinformasikan ke Pihak Pertama untuk persetujuan dan jika ada perubahaan maka maksimal menginformasikan perubahan tersebut 7 hari kalender',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PAKET LAYANAN',
        ),
        1 => 
        array (
          'p' => 'NOMOR: 196/FBT/PKS/III/2026',
        ),
        2 => 
        array (
          'p' => 'Paket layanan meliputi persyaratan sebagai berikut:',
        ),
        3 => 
        array (
          'p' => 'Nama Brand FIBERTRUST',
        ),
        4 => 
        array (
          'p' => 'Harga setiap paket minimal Rp 100.000/2Mbps',
        ),
        5 => 
        array (
          'p' => 'Biaya instalasi disesuaikan dengan kebutuhan pelanggan',
        ),
        6 => 
        array (
          'p' => 'Paket Layanan yang dibuat harus diinformasikan ke Pihak Pertama untuk persetujuan dan jika ada perubahaan maka maksimal menginformasikan perubahan tersebut 7 hari kalender',
        ),
      ),
    ),
    2 => 
    array (
      'judul' => 'LAMPIRAN III PERJANJIAN',
      'text' => 'PENGADUAN PELANGGAN

NOMOR: 196/FBT/PKS/III/2026

Penanganan gangguan selama operasional, dilayanani sbb:

FIBERTRUST mengoperasikan call center melalui chat, telepon, dan surat elektronik (e-mail) selama 24 jam per hari, 7 hari untuk setiap minggu.

Untuk koordinasi, perijinan dan pencatatan, seluruh pemberitahuan yang membutuhkan tindakan-tindakan oleh FIBERTRUST, akan dilakukan dalam bentuk pemberitahuan tertulis dalam bentuk surat yang ditandatangani dan dikirimkan ke alamat Fibertust atau melalui call center.

Fibertrust memberikan tanggapan, diteksi dan perbaikan dengan ketentuan sebagai berikut:

2. Matriks Eskalasi',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PENGADUAN PELANGGAN',
        ),
        1 => 
        array (
          'p' => 'NOMOR: 196/FBT/PKS/III/2026',
        ),
        2 => 
        array (
          'p' => 'Penanganan gangguan selama operasional, dilayanani sbb:',
        ),
        3 => 
        array (
          'p' => 'FIBERTRUST mengoperasikan call center melalui chat, telepon, dan surat elektronik (e-mail) selama 24 jam per hari, 7 hari untuk setiap minggu.',
        ),
        4 => 
        array (
          'p' => 'Untuk koordinasi, perijinan dan pencatatan, seluruh pemberitahuan yang membutuhkan tindakan-tindakan oleh FIBERTRUST, akan dilakukan dalam bentuk pemberitahuan tertulis dalam bentuk surat yang ditandatangani dan dikirimkan ke alamat Fibertust atau melalui call center.',
        ),
        5 => 
        array (
          'p' => 'Fibertrust memberikan tanggapan, diteksi dan perbaikan dengan ketentuan sebagai berikut:',
        ),
        6 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Kegiatan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Tolak Ukur Layanan',
                  ),
                  's' => 1,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Penerimaan pengaduan gangguan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '15 Menit',
                  ),
                  's' => 1,
                ),
              ),
              2 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Konfirmasi penyebab Gangguan (RFO)',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Disampaikan dalam jangka waktu 24 jam setelah perbaikan dinyatakan selesai',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
        7 => 
        array (
          'p' => '2. Matriks Eskalasi',
        ),
        8 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'No',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Fault time',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Escalation Level',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Point of Contact',
                  ),
                  's' => 1,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '1',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '< 120 Menit',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Level 1',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Helpdesk',
                  ),
                  's' => 1,
                ),
              ),
              2 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Phone: 0231-247618',
                  ),
                  's' => 1,
                ),
              ),
              3 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Email: cs@fibertrust.id',
                  ),
                  's' => 1,
                ),
              ),
              4 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '2',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '121 Menit – 240 Menit',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Level 2',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'NOC',
                  ),
                  's' => 1,
                ),
              ),
              5 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Phone: 0896-6049-5927',
                  ),
                  's' => 1,
                ),
              ),
              6 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Email: oman@fibertrust.id',
                  ),
                  's' => 1,
                ),
              ),
              7 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '3',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '241 Menit – 480 Menit',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Level 3',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Direktur',
                  ),
                  's' => 1,
                ),
              ),
              8 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Phone: 0852-2203-0317',
                  ),
                  's' => 1,
                ),
              ),
              9 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Email: ageng@fibertrust.id',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
      ),
    ),
    3 => 
    array (
      'judul' => 'LAMPIRAN IV PERJANJIAN',
      'text' => 'PERANGKAT

NOMOR: 196/FBT/PKS/III/2026',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PERANGKAT',
        ),
        1 => 
        array (
          'p' => 'NOMOR: 196/FBT/PKS/III/2026',
        ),
        2 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'No.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Deskripsi',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Jumlah',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Keterangan',
                  ),
                  's' => 1,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '1.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'SFP 1.25GTX1310nm 40KM DDMSC',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => '1 Pcs',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Dipinjamkan',
                  ),
                  's' => 1,
                ),
              ),
              2 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '2.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'SFP 1.25G TX1550nm 40KM DDM SC',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => '1 Pcs',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Dipinjamkan',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => true,
          ),
        ),
      ),
    ),
  ),
),
            ],
            'kontrak-colocation' => [
                'title' => 'Perjanjian Berlangganan Jasa Colocation',
                'header_data' => array (
  'kopInstansi' => '[Nama Perusahaan PIHAK PERTAMA]',
  'kopAlamat' => '[Alamat perusahaan PIHAK PERTAMA]',
  'kopKontrak' => 'PERJANJIAN BERLANGGANAN',
  'nomorSurat' => '[Nomor Perjanjian]',
  'perihalSurat' => 'Jasa Colocation',
  'sifatSurat' => 'Penting',
),
                'body_content' => array (
  'preamble' => 'PERJANJIAN BERLANGGANAN

JASA COLOCATION

PT BINA INFORMATIKA SOLUSI

DENGAN

PT SOLUSINDO BINTANG PRATAMA

Nomor: 239/FBT/J.C/VII/2026

PERJANJIAN BERLANGGANAN

JASA COLOCATION

Nomor: 239/FBT/J.C/VII/2026

Perjanjian Berlangganan Jasa Colocation ini disetujui di Cirebon, pada hari Sabtu, tanggal 25 Juli 2026, oleh dan antara:

PT Bina Informatika Solusi, suatu perseroan terbatas, yang didirikan berdasarkan Hukum Negara Republik Indonesia, berkedudukan di Jalan Prakarsa Muda Nomor 258, Kel. Pekiringan, Kec. Kesambi, Kota Cirebon, Jawa Barat 45131. Berdasarkan Akta Berita Acara RUPS Tahunan Perseroan Terbatas “PT Bina Informatika Solusi”, Nomor 5, tanggal 10 Juli 2026, dibuat dihadapan Irni Yuniati, S.H., M.Kn., Notaris di Kota Cimahi. Dalam hal ini diwakili oleh Ageng Bagja Priyadi, S.T., M.Kom, selaku Direktur, bertindak untuk dan atas nama PT Bina Informatika Solusi, selanjutnya disebut sebagai PIHAK PERTAMA.

d e n g a n

PT Solusindo Bintang Pratama, suatu perseroan terbatas, yang didirikan berdasarkan Hukum Negara Republik Indonesia, berkedudukan di Jalan Ciledug Raya No. 99, Cipulir, Kebayoran Lama, Jakarta Selatan. Berdasarkan Akta Pendirian Perseroan Terbatas "PT. Solusindo Bintang Pratama", Nomor 1, tanggal 4 April 2005, dibuat dihadapan Herry Ridwanto, SH., Notaris di Kota Batam. Dalam hal ini diwakili oleh Budiarto, selaku Direktur, bertindak untuk dan atas nama PT Solusindo Bintang Pratama, untuk selanjutnya disebut sebagai PIHAK KEDUA atau PELANGGAN.

Definisi dan Interpretasi:

"Biaya Layanan” adalah biaya yang timbul atas layanan Colocation yang dikenakan kepada Pelanggan dan wajib dibayarkan setiap bulan oleh Pelanggan kepada PIHAK PERTAMA.

"Gedung” berarti Lokasi Data Centre PIHAK PERTAMA seperti yang dijelaskan dalam Bagian Spesifikasi dari Uraian di mana Ruang Colocation Pelanggan berada atau akan dibangun.

"Hari Kerja” berarti jumlah hari dalam seminggu tidak termasuk Minggu atau hari libur nasional.

"Ruang Colocation” berarti ruang Colocation yang dilisensikan atau disewakan atau digunakan oleh Pelanggan  di dalam Gedung.

"Rack Colocation” adalah tempat (rack) beserta listrik  dan dukungan teknik untuk penempatan komputer, server dan perangkat pendukung lainnya yang disewakan.

“Area Umum” berarti semua bagian dari Gedung yang dari waktu ke waktu disediakan untuk digunakan bersama oleh lebih dari satu penghuni Gedung dan para pengunjung  mereka termasuk (namun tidak terbatas) jalan akses untuk pengendara dan pejalan kaki, tangga, area sirkulasi, lift, eskalator, loading bays, pintu darurat, kebakaran, fasilitas toilet, area penyimpanan, tempat pengumpulan barang yang ditolak, tempat pembuangan dan area parkir.

"Alamat Pelanggan” berarti alamat yang tertera dalam Bagian 4 dari Uraian atau alamat lainnya yang diberitahukan Oleh Pelanggan kepada PIHAK PERTAMA.

“Peralatan Pelanggan” berarti peralatan yang dipasang oleh Pelanggan di dalam Ruang Colocation/Area Teknis Pelanggan.

“Personil Pelanggan” berarti karyawan, petugas, undangan, agen, penghuni yang terdaftar atau kontraktor independen Pelanggan.

"Biaya Listrik” adalah biaya dan pengeluaran yang ditagih oleh PIHAK PERTAMA atau berkaitan dengan penyediaan  pasokan listrik dan pendinginan ke Colocation Pelanggan.

"Layanan Tambahan” adalah layanan selain layanan utama, seperti penambahan kapasitas listrik, cross connect,  smarthand, dan layanan lainnya yang diminta oleh Pelanggan yang terdapat pada lampiran 2.

“Peralatan” berarti lemari, rak dan peralatan telekomunikasi dan peralatan lainnya yang dari waktu ke waktu dipasang di dalam Ruang Colocation oleh Pelanggan.

“Tanggal Berakhir” berarti tanggal yang telah ditetapkan di dalam Berita Acara Aktivasi dimana PIHAK PERTAMA akan menghentikan penyediaan Jasa.

"Grup Perusahaan” berarti perusahaan, yang merupakan perusahaan yang terkait dengan Pelanggan.

“Alamat Faktur” berarti alamat yang tertera dalam Bagian 5 dari Uraian.

“Fasilitas Listrik” adalah besar daya maksimum yang diberikan dari PIHAK PERTAMA kepada Pelanggan sebagaimana yang ditentukan pada PERJANJIAN ini dengan pilihan daya listrik sebesar 6 A, 10 A, 16 A, 20 A, 25 A, 32 A. Jika ada permintaan penambahan listrik dari Pelanggan, maka akan diukur kelebihan listrik tersebut oleh Pelanggan dan   PIHAK PERTAMA untuk mengetahui besar listrik tersebut dan kelebihan tersebut akan dibebankan kepada Pelanggan.

"Meet Me Room” berarti lokasi terminasi utama untuk konektivitas yang masuk kedalam Gedung.

"Uraian” berarti uraian/keterangan khusus sebagaimana diatur dalam PERJANJIAN ini.

"Para Pihak” berarti PIHAK PERTAMA dan Pelanggan dan "Pihak" berarti salah satu diantara mereka.

"Jasa-Jasa” berarti Produk-produk Colocation yang akan diberikan oleh PIHAK PERTAMA berdasarkan PERJANJIAN ini, dan \'Jasa\' harus ditafsirkan dengan sesuai.

"Gangguan Jasa” berarti tidak tersedianya pasokan tenaga listrik dan/atau penyediaan pendinginan mekanik (yang konsekuensinya bisnis Pelanggan di Ruang Colocation Pelanggan mengalami gangguan atau kerusakan).

"Media Jasa” berarti kawat, kabel, saluran dan media penghubung lainnya; pipa-pipa ini tidak boleh digunakan oleh Pelanggan di Data Center atau Network Operation Center (NOC) mereka.

“Jangka Waktu” berarti durasi penyediaan Jasa Produk sebagaimana tercantum dalam PERJANJIAN ini.

"Total Daya Listrik Peralatan IT” berarti total daya listrik yang digunakan oleh Pelanggan dan Pelanggan Lain untuk peralatan teknologi informasi, telekomunikasi, dan lainnya di dalam Ruang Colocation.

"Ruang Lingkup” adalah kerjasama dalam hal layanan yang diberikan oleh PIHAK PERTAMA kepada Pelanggan berupa Colocation, sebagaimana tercantum dalam PERJANJIAN ini.

"Penalti” adalah denda yang dikenakan kepada Pelanggan sebagai akibat dari adanya pelanggaran terhadap  ketentuan-ketentuan dan syarat-syarat PERJANJIAN ini atau permintaan perpindahan atau perubahan tertentu atas jasa layanan yang telah disetujui kedua belah pihak dalam PERJANJIAN ini. Penalti yang dikenakan kepada Pelanggan disesuaikan dengan jenis pelanggaran yang dilakukan Pelanggan atau jenis perubahan jasa layanan yang diminta oleh Pelanggan.

Apabila terdapat istilah yang belum diatur dalam Perjanjian ini, maka akan ditafsirkan sesuai ketentuan peraturan perundang-undangan yang berlaku.

SPESIFIKASI

Tanggal Aktivasi

25 Juli 2026

Pelanggan

PT SOLUSINDO BINTANG PRATAMA

Nomor Pokok Wajib Pajak Pelanggan

0024.5981.9521.5000

Alamat Kantor Pusat Pelanggan dan kontak yang dinominasikan

Jalan Ciledug Raya No. 99, Cipulir, Kebayoran Lama, Jakarta Selatan

Attention: Ahmad Ifan

Alamat Pengiriman Invoice

Jalan Ciledug Raya No. 99, Cipulir, Kebayoran Lama, Jakarta Selatan

Attention: Ahmad Ifan

Jangka Waktu

selama 1 (satu) Tahun, dimulai sejak ditandatangani Berita Acara Aktivasi

Jasa & Produk Layanan

Sebagaimana tercantum dalam lampiran PERJANJIAN ini

Gedung

Gedung Wisma Bumiputera, Lantai 7 Suite #701B,

Jl. Asia Afrika No.141-149, Kb. Pisang, Kec. Sumur Bandung, Kota  Bandung, Jawa Barat 40112',
  'isi' => 
  array (
    0 => 
    array (
      'judul' => 'URAIAN',
      'text' => 'BAA adalah Berita Acara Aktivasi yang merupakan berita acara pengaktifan layanan Colocation dengan ketentuan mengikat jangka waktu minimum 1 tahun dan menjadi satu kesatuan di dalam PERJANJIAN ini;

Uraian merupakan satu kesatuan dari Perjanjian ini, tetapi judul-judul untuk klausal digunakan hanya untuk memudahkan saja dan tidak akan mempengaruhi interpretasi atau konstruksi dari PERJANJIAN ini;

Kata-kata yang mengandung arti bentuk tunggal juga mencakup    arti bentuk jamaknya dan sebaliknya. Kata-kata yang mengandung arti satu gender mencakup semua gender dan rujukan terhadap orang meliputi satu individu, perusahaan, korporasi, firma, partnership atau badan-badan lainnya;

Kecuali dinyatakan lain, Pelanggan harus membayar semua bea materai, jika ada pada PERJANJIAN ini;

Kata-kata dan frasa "lain" atau "lainnya", "termasuk" dan "khusus" atau "khususnya" tidak akan membatasi keumuman dari kata-kata sebelumnya atau ditafsirkan sebagai terbatas pada kelas yang sama seperti kata-kata sebelumnya  dimana konstruksi yang lebih luas dimungkinkan;

Setiap kewajiban untuk melakukan atau tidak melakukan sesuatu harus mencakup kewajiban untuk mengusahakan bahwa hal itu dilakukan atau tidak dilakukan;

Rujukan terhadap Klausa-klausa dan Lampiran-lampiran adalah rujukan terhadap klausa dan lampiran pada PERJANJIAN ini;

Semua rujukan terhadap Ketentuan perundang-undangan mencakup sebagaimana undang-undang dari waktu ke waktu dapat diubah atau diberlakukan kembali sejauh perubahan          atau pemberlakuan kembali itu berlaku atau dapat diberlakukan terhadap setiap transaksi yang diadakan di bawah atau berkaitan dengan PERJANJIAN ini;

Rujukan terhadap PIHAK PERTAMA mencakup penerusnya dan perwakilan yang diizinkan dan semua orang yang berhak terhadap kepemilikan Gedung. Rujukan terhadap Pelanggan termasuk para penggantinya dan perwakilan yang diizinkan;

Rujukan terhadap hak atau kewajiban dari dua atau lebih orang memberikan hak itu atau membebankan kewajiban itu, pada masing-masing individu dan keduanya (atau semuanya) secara bersama-sama.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'BAA adalah Berita Acara Aktivasi yang merupakan berita acara pengaktifan layanan Colocation dengan ketentuan mengikat jangka waktu minimum 1 tahun dan menjadi satu kesatuan di dalam PERJANJIAN ini;',
        ),
        1 => 
        array (
          'p' => 'Uraian merupakan satu kesatuan dari Perjanjian ini, tetapi judul-judul untuk klausal digunakan hanya untuk memudahkan saja dan tidak akan mempengaruhi interpretasi atau konstruksi dari PERJANJIAN ini;',
        ),
        2 => 
        array (
          'p' => 'Kata-kata yang mengandung arti bentuk tunggal juga mencakup    arti bentuk jamaknya dan sebaliknya. Kata-kata yang mengandung arti satu gender mencakup semua gender dan rujukan terhadap orang meliputi satu individu, perusahaan, korporasi, firma, partnership atau badan-badan lainnya;',
        ),
        3 => 
        array (
          'p' => 'Kecuali dinyatakan lain, Pelanggan harus membayar semua bea materai, jika ada pada PERJANJIAN ini;',
        ),
        4 => 
        array (
          'p' => 'Kata-kata dan frasa "lain" atau "lainnya", "termasuk" dan "khusus" atau "khususnya" tidak akan membatasi keumuman dari kata-kata sebelumnya atau ditafsirkan sebagai terbatas pada kelas yang sama seperti kata-kata sebelumnya  dimana konstruksi yang lebih luas dimungkinkan;',
        ),
        5 => 
        array (
          'p' => 'Setiap kewajiban untuk melakukan atau tidak melakukan sesuatu harus mencakup kewajiban untuk mengusahakan bahwa hal itu dilakukan atau tidak dilakukan;',
        ),
        6 => 
        array (
          'p' => 'Rujukan terhadap Klausa-klausa dan Lampiran-lampiran adalah rujukan terhadap klausa dan lampiran pada PERJANJIAN ini;',
        ),
        7 => 
        array (
          'p' => 'Semua rujukan terhadap Ketentuan perundang-undangan mencakup sebagaimana undang-undang dari waktu ke waktu dapat diubah atau diberlakukan kembali sejauh perubahan          atau pemberlakuan kembali itu berlaku atau dapat diberlakukan terhadap setiap transaksi yang diadakan di bawah atau berkaitan dengan PERJANJIAN ini;',
        ),
        8 => 
        array (
          'p' => 'Rujukan terhadap PIHAK PERTAMA mencakup penerusnya dan perwakilan yang diizinkan dan semua orang yang berhak terhadap kepemilikan Gedung. Rujukan terhadap Pelanggan termasuk para penggantinya dan perwakilan yang diizinkan;',
        ),
        9 => 
        array (
          'p' => 'Rujukan terhadap hak atau kewajiban dari dua atau lebih orang memberikan hak itu atau membebankan kewajiban itu, pada masing-masing individu dan keduanya (atau semuanya) secara bersama-sama.',
        ),
      ),
    ),
    1 => 
    array (
      'judul' => 'LAYANAN/PRODUK',
      'text' => 'Pelanggan berkeinginan menggunakan jasa dari PIHAK PERTAMA dan PIHAK PERTAMA berkeinginan memberikan Jasa kepada Pelanggan dengan tunduk pada syarat dan ketentuan yang tercantum dalam PERJANJIAN ini;

Pelanggan meminta Jasa dengan menandatangani Berita Acara Aktivasi PIHAK PERTAMA yang hanya akan berlaku bila disetujui oleh PIHAK PERTAMA, dan akan diatur oleh syarat dan ketentuan pada PERJANJIAN ini;

Setiap Pengaktifan 1 atau lebih layanan colocation akan diterbitkan Berita Acara Aktivasi mengikuti ketentuan jangka waktu PERJANJIAN yang terdapat pada pasal 4 dalam perjanjian.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Pelanggan berkeinginan menggunakan jasa dari PIHAK PERTAMA dan PIHAK PERTAMA berkeinginan memberikan Jasa kepada Pelanggan dengan tunduk pada syarat dan ketentuan yang tercantum dalam PERJANJIAN ini;',
        ),
        1 => 
        array (
          'p' => 'Pelanggan meminta Jasa dengan menandatangani Berita Acara Aktivasi PIHAK PERTAMA yang hanya akan berlaku bila disetujui oleh PIHAK PERTAMA, dan akan diatur oleh syarat dan ketentuan pada PERJANJIAN ini;',
        ),
        2 => 
        array (
          'p' => 'Setiap Pengaktifan 1 atau lebih layanan colocation akan diterbitkan Berita Acara Aktivasi mengikuti ketentuan jangka waktu PERJANJIAN yang terdapat pada pasal 4 dalam perjanjian.',
        ),
      ),
    ),
    2 => 
    array (
      'judul' => 'BIAYA, SYARAT PEMBAYARAN, PENYETORAN DAN PAJAK',
      'text' => 'Pelanggan wajib membayar Biaya Bulanan dan atau Biaya Transaksi lainnya yang tercantum dalam PERJANJIAN ini kepada PIHAK      PERTAMA;

Masing-masing Pihak sepakat bahwa atas biaya sebagaimana terlampir dalam PERJANJIAN ini berlaku untuk 1 (satu) tahun masa berlangganan sebagaimana dimaksud dalam poin Jangka Waktu Aktivasi Layanan dalam Pasal 4 ayat (1) Spesifikasi jo. Pasal 1 ayat (1) PERJANJIAN ini;

Biaya sebagaimana telah diatur pada Pasal 3 ayat (1). Pelanggan wajib membayar biaya bulanan setelah PIHAK PERTAMA mengirimkan tagihan di minggu I pada setiap bulan dan tanggal jatuh tempo selambat-lambatnya 30 (tiga puluh) hari kalender dari tanggal invoice diterbitkan. Biaya-biaya tersebut wajib dibayarkan oleh Pelanggan kepada PIHAK PERTAMA setelah tagihan diterima secara lengkap, benar, dan disepakati oleh PIHAK PERTAMA dan Pelanggan, dan Pelanggan tidak ada lagi perubahan atau penggantian atas tagihan, maka Pelanggan wajib membayar jumlah-jumlah di dalam tagihan selambat-lambatnya 30 (tiga puluh) hari kalender dari tanggal invoice diterbitkan;

Bilamana sampai berakhirnya tenggang waktu sebagaimana  dimaksud pada Pasal 3 ayat (3), Pelanggan tidak atau belum melakukan pembayaran Biaya Layanan sebagaimana disebutkan dalam Pasal ini kepada PIHAK PERTAMA, maka PIHAK PERTAMA akan memberikan surat peringatan yang selanjutnya menerbitkan surat pemberitahuan tertulis atau melalui email untuk melakukan pembatasan akses masuk atau keluar baik barang maupun personal, dan apabila tidak ada upaya penyelesaian maka PIHAK PERTAMA berhak melakukan pemutusan sementara dengan pemberitahuan tertulis melalui surat. Dalam hal ini PIHAK KEDUA tetap berkewajiban untuk membayar biaya sewa berlangganan sampai dengan diterimanya  pembayaran kepada PIHAK PERTAMA;

Jika PIHAK PERTAMA melakukan pemutusan sementara berdasarkan paragraf ini, PIHAK PERTAMA akan mengaktifkan kembali Jasa yang dihentikan dalam 1x24 jam setelah Pelanggan melakukan pembayaran Biaya Layanan. PIHAK PERTAMA membebankan denda keterlambatan pembayaran sebesar 1 (satu) permil setiap hari keterlambatan kepada Pelanggan;

Pelanggan wajib memberitahukan kepada PIHAK PERTAMA jika pembayaran sebagaimana dimaksud pada Pasal 3 ayat (1) telah dilakukan pembayaran dengan melampirkan salinan bukti setor;

Pelanggan harus membayar semua pajak dan biaya pihak ketiga

(apabila menggunakan pihak ketiga) yang terkait dengan kepemilikan dan pengoperasian Peralatan Pelanggan dan kegiatan yang berkaitan dengan Pelanggan didalam Gedung ini. Tanpa membatasi ketentuan yang disebutkan sebelumnya, Pelanggan akan bertanggung jawab untuk membayar seluruh kewajiban Pajaknya sebagaimana diwajibkan oleh peraturan perundang-undangan yang berlaku yang diberlakukan secara terpisah, yang dikenakan atau dibebankan Pelanggan, dan mempersiapkan mengisi setiap  pernyataan yang diperlukan kepada, pemerintah, kuasa pemerintah atau petugas pajak sebelum tanggal untuk pembayaran dan pernyataan tersebut jatuh tempo. Dalam  keadaan apapun tidak ada Peralatan Pelanggan yang dapat ditafsirkan untuk menjadi fixture (bagian dari gedung);

Pelanggan menyatakan dan menjamin bahwa seluruh barang yang diletakan di dalam Data Center sepenuhnya merupakan milik Pelanggan dan bebas dari sengketa dengan pihak manapun dan Pelanggan bertanggung jawab atas segala akibat yang timbul apabila terjadi permasalahan dikemudian hari atas barang- barang yang diletakkan di lokasi karena tidak ditaatinya peraturan perundang-undangan yang berlaku.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Pelanggan wajib membayar Biaya Bulanan dan atau Biaya Transaksi lainnya yang tercantum dalam PERJANJIAN ini kepada PIHAK      PERTAMA;',
        ),
        1 => 
        array (
          'p' => 'Masing-masing Pihak sepakat bahwa atas biaya sebagaimana terlampir dalam PERJANJIAN ini berlaku untuk 1 (satu) tahun masa berlangganan sebagaimana dimaksud dalam poin Jangka Waktu Aktivasi Layanan dalam Pasal 4 ayat (1) Spesifikasi jo. Pasal 1 ayat (1) PERJANJIAN ini;',
        ),
        2 => 
        array (
          'p' => 'Biaya sebagaimana telah diatur pada Pasal 3 ayat (1). Pelanggan wajib membayar biaya bulanan setelah PIHAK PERTAMA mengirimkan tagihan di minggu I pada setiap bulan dan tanggal jatuh tempo selambat-lambatnya 30 (tiga puluh) hari kalender dari tanggal invoice diterbitkan. Biaya-biaya tersebut wajib dibayarkan oleh Pelanggan kepada PIHAK PERTAMA setelah tagihan diterima secara lengkap, benar, dan disepakati oleh PIHAK PERTAMA dan Pelanggan, dan Pelanggan tidak ada lagi perubahan atau penggantian atas tagihan, maka Pelanggan wajib membayar jumlah-jumlah di dalam tagihan selambat-lambatnya 30 (tiga puluh) hari kalender dari tanggal invoice diterbitkan;',
        ),
        3 => 
        array (
          'p' => 'Bilamana sampai berakhirnya tenggang waktu sebagaimana  dimaksud pada Pasal 3 ayat (3), Pelanggan tidak atau belum melakukan pembayaran Biaya Layanan sebagaimana disebutkan dalam Pasal ini kepada PIHAK PERTAMA, maka PIHAK PERTAMA akan memberikan surat peringatan yang selanjutnya menerbitkan surat pemberitahuan tertulis atau melalui email untuk melakukan pembatasan akses masuk atau keluar baik barang maupun personal, dan apabila tidak ada upaya penyelesaian maka PIHAK PERTAMA berhak melakukan pemutusan sementara dengan pemberitahuan tertulis melalui surat. Dalam hal ini PIHAK KEDUA tetap berkewajiban untuk membayar biaya sewa berlangganan sampai dengan diterimanya  pembayaran kepada PIHAK PERTAMA;',
        ),
        4 => 
        array (
          'p' => 'Jika PIHAK PERTAMA melakukan pemutusan sementara berdasarkan paragraf ini, PIHAK PERTAMA akan mengaktifkan kembali Jasa yang dihentikan dalam 1x24 jam setelah Pelanggan melakukan pembayaran Biaya Layanan. PIHAK PERTAMA membebankan denda keterlambatan pembayaran sebesar 1 (satu) permil setiap hari keterlambatan kepada Pelanggan;',
        ),
        5 => 
        array (
          'p' => 'Pelanggan wajib memberitahukan kepada PIHAK PERTAMA jika pembayaran sebagaimana dimaksud pada Pasal 3 ayat (1) telah dilakukan pembayaran dengan melampirkan salinan bukti setor;',
        ),
        6 => 
        array (
          'p' => 'Pelanggan harus membayar semua pajak dan biaya pihak ketiga',
        ),
        7 => 
        array (
          'p' => '(apabila menggunakan pihak ketiga) yang terkait dengan kepemilikan dan pengoperasian Peralatan Pelanggan dan kegiatan yang berkaitan dengan Pelanggan didalam Gedung ini. Tanpa membatasi ketentuan yang disebutkan sebelumnya, Pelanggan akan bertanggung jawab untuk membayar seluruh kewajiban Pajaknya sebagaimana diwajibkan oleh peraturan perundang-undangan yang berlaku yang diberlakukan secara terpisah, yang dikenakan atau dibebankan Pelanggan, dan mempersiapkan mengisi setiap  pernyataan yang diperlukan kepada, pemerintah, kuasa pemerintah atau petugas pajak sebelum tanggal untuk pembayaran dan pernyataan tersebut jatuh tempo. Dalam  keadaan apapun tidak ada Peralatan Pelanggan yang dapat ditafsirkan untuk menjadi fixture (bagian dari gedung);',
        ),
        8 => 
        array (
          'p' => 'Pelanggan menyatakan dan menjamin bahwa seluruh barang yang diletakan di dalam Data Center sepenuhnya merupakan milik Pelanggan dan bebas dari sengketa dengan pihak manapun dan Pelanggan bertanggung jawab atas segala akibat yang timbul apabila terjadi permasalahan dikemudian hari atas barang- barang yang diletakkan di lokasi karena tidak ditaatinya peraturan perundang-undangan yang berlaku.',
        ),
      ),
    ),
    3 => 
    array (
      'judul' => 'JANGKA WAKTU PERJANJIAN, BERITA ACARA AKTIVASI, SUSPENSI LAYANAN DAN PEMBERHENTIAN',
      'text' => 'Jangka waktu PERJANJIAN ini selama 1 (satu) tahun dan berlaku terhitung sejak tanggal diterbitkannya Berita Acara Aktivasi                          oleh PIHAK PERTAMA. Apabila selambatnya 30 (tiga puluh) hari sebelum jangka waktu berakhirnya PERJANJIAN ini tidak ada pemberitahuan secara tertulis dari Pelanggan, maka jangka waktu PERJANJIAN ini akan diperpanjang secara otomatis minimal  untuk 1 (satu) tahun berikutnya;

Setiap Berita Acara Aktivasi akan mulai berlaku penuh selama 1 (satu) tahun dan efektif pada Tanggal Permulaan Berita Acara Aktivasi dan diperpanjang secara otomatis untuk jangka   waktu 12 (dua belas) bulan berikutnya kecuali salah satu Pihak bermaksud untuk mengakhiri jasa atau PERJANJIAN ini dengan menyampaikan pemberitahuan secara tertulis sebelum 30 (tiga puluh) hari jangka waktu PERJANJIAN berakhir;

Pelanggan wajib menandatangani Berita Acara Aktivasi (BAA) dalam waktu selambat-lambatnya 7 hari dari tanggal yang tertera pada BAA;

Penolakan untuk menandatangani Berita Acara Aktivasi (BAA) oleh Pelanggan, maka PIHAK PERTAMA berhak mendapatkan ganti rugi dari Pelanggan berupa pembayaran biaya berlangganan dikalikan sisa jangka waktu berlangganan yang belum dijalani dan wajib dibayarkan paling lambat 14 (empat belas) hari kerja;

Apabila Pelanggan bermaksud melakukan pengakhiran PERJANJIAN, maka Pelanggan harus memberitahukan secara tertulis kepada PIHAK PERTAMA minimal 30 (tiga puluh) hari sebelum tanggal berakhirnya jangka waktu PERJANJIAN;

Surat pemberitahuan pemutusan PERJANJIAN dari Pelanggan kepada PIHAK PERTAMA wajib diberikan dan pemberitahuan secara prosedural administrasi bukanlah sebagai alat pembenar dari Pelanggan untuk lepas dari segala tanggung jawab dan kewajiban yang telah diatur dalam PERJANJIAN ini;

PIHAK PERTAMA berhak untuk mengakhiri PERJANJIAN ini dengan memberikan pemberitahuan tertulis kepada Pelanggan (atau, atas kebijakan) PIHAK PERTAMA sendiri menangguhkan jasa, termasuk diantaranya menghentikan pasokan listrik jika:

Pelanggan melakukan manipulasi bisnis yang berakibat pailit atau terlikuidasi atau berhenti beroperasi;

Pelanggan dapat dibuktikan melakukan pelanggaran akan PERJANJIAN ini yang menurut penilaian PIHAK PERTAMA memiliki potensi  untuk mengganggu jalannya operasional atau pemeliharaan Gedung PIHAK PERTAMA atau mengganggu Pelanggan lainnya di dalam Gedung, dengan pemberitahuan tertulis yang dilengkapi dengan bukti-bukti otentik atas penilaian  tersebut dan Pelanggan gagal untuk memperbaiki pelanggaran tersebut dalam dua puluh empat (24) jam pemberitahuan yang sama. Maka PIHAK PERTAMA menghentikan Jasa layanan tersebut (termasuk menghentikan pasokan listrik) sampai dengan Pelanggan membayar biaya pemulihan atas kerusakan yang terjadi.

Pelanggan memberikan jaminan dengan seluruh peralatannya  kepada PIHAK PERTAMA sekarang atau nanti setelah ditempatkan di dalam Gedung, untuk menjamin pembayaran dalam jumlah berapapun dan pemenuhan semua kewajiban yang terdapat dalam PERJANJIAN ini. Sehubungan dengan hal ini, jika dibutuhkan secara hukum, PIHAK PERTAMA akan berhak untuk mengajukan satu atau lebih laporan keuangan untuk jaminan ini dan Pelanggan akan menandatangani semua dokumen yang diperlukan, dan akan mengambil tindakan lain yang diminta PIHAK PERTAMA secara wajar. Ketentuan-ketentuan sebelumnya dari Pasal ini tidak akan mengurangi hak PIHAK PERTAMA untuk melaksanakan hak gadai atas setiap Peralatan Pelanggan untuk pembayaran yang belum dilunasi oleh Pelanggan kepada PIHAK PERTAMA sampai dengan selambat-lambatnya 45 (empat puluh hari) hari sejak tanggal invoice diterbitkan. PIHAK PERTAMA berhak menjual atau menyingkirkan Peralatan Pelanggan tersebut, tanpa pemberitahuan sebelumnya kepada Pelanggan, dengan cara penjualan publik atau privat tanpa proses hukum apapun sebelumnya, pada waktu dan harga yang oleh kebijakan PIHAK PERTAMA dianggap wajar, tanpa mengurangi hak-hak dan perbaikan lain yang dapat dimiliki PIHAK PERTAMA. PIHAK PERTAMA berhak untuk menggunakan hasil penjualan, jika ada, setelah dikurangi biaya yang terkait dengan penjualan atau penyingkiran (termasuk tapi tidak terbatas pada biaya administrasi yang ditentukan oleh PIHAK PERTAMA sendiri), untuk pembayaran semua tagihan yang jatuh tempo dan harus dibayar oleh Pelanggan kepada PIHAK PERTAMA dan jika hasil penjualan tersebut tidak cukup, maka semua jumlah yang belum dilunasi harus dibayarkan kepada PIHAK PERTAMA oleh Pelanggan dalam waktu 30 (tiga puluh) hari setelah adanya permintaan dari PIHAK PERTAMA. Pelanggan akan membebaskan PIHAK PERTAMA terhadap kewajiban apapun yang timbul akibat hal diatas terhadap pihak ketiga;

Apabila PELANGGAN tidak melaksanakan kewajibannya sebagaimana dimaksud dalam PERJANJIAN ini untuk melunasi pembayaran sampai dengan selambat-lambatnya 30 (tiga puluh) hari sejak tanggal invoice diterbitkan, maka Pelanggan bersedia dan sepakat PIHAK PERTAMA telah memiliki hak sepenuhnya/kewenangan dalam melakukan penjualan secara public ataupun privat tanpa proses hukum apapun atau menyingkirkan peralatan Pelanggan dari lokasi dan PIHAK PERTAMA dibebaskan dari tuntutan oleh Pelanggan.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Jangka waktu PERJANJIAN ini selama 1 (satu) tahun dan berlaku terhitung sejak tanggal diterbitkannya Berita Acara Aktivasi                          oleh PIHAK PERTAMA. Apabila selambatnya 30 (tiga puluh) hari sebelum jangka waktu berakhirnya PERJANJIAN ini tidak ada pemberitahuan secara tertulis dari Pelanggan, maka jangka waktu PERJANJIAN ini akan diperpanjang secara otomatis minimal  untuk 1 (satu) tahun berikutnya;',
        ),
        1 => 
        array (
          'p' => 'Setiap Berita Acara Aktivasi akan mulai berlaku penuh selama 1 (satu) tahun dan efektif pada Tanggal Permulaan Berita Acara Aktivasi dan diperpanjang secara otomatis untuk jangka   waktu 12 (dua belas) bulan berikutnya kecuali salah satu Pihak bermaksud untuk mengakhiri jasa atau PERJANJIAN ini dengan menyampaikan pemberitahuan secara tertulis sebelum 30 (tiga puluh) hari jangka waktu PERJANJIAN berakhir;',
        ),
        2 => 
        array (
          'p' => 'Pelanggan wajib menandatangani Berita Acara Aktivasi (BAA) dalam waktu selambat-lambatnya 7 hari dari tanggal yang tertera pada BAA;',
        ),
        3 => 
        array (
          'p' => 'Penolakan untuk menandatangani Berita Acara Aktivasi (BAA) oleh Pelanggan, maka PIHAK PERTAMA berhak mendapatkan ganti rugi dari Pelanggan berupa pembayaran biaya berlangganan dikalikan sisa jangka waktu berlangganan yang belum dijalani dan wajib dibayarkan paling lambat 14 (empat belas) hari kerja;',
        ),
        4 => 
        array (
          'p' => 'Apabila Pelanggan bermaksud melakukan pengakhiran PERJANJIAN, maka Pelanggan harus memberitahukan secara tertulis kepada PIHAK PERTAMA minimal 30 (tiga puluh) hari sebelum tanggal berakhirnya jangka waktu PERJANJIAN;',
        ),
        5 => 
        array (
          'p' => 'Surat pemberitahuan pemutusan PERJANJIAN dari Pelanggan kepada PIHAK PERTAMA wajib diberikan dan pemberitahuan secara prosedural administrasi bukanlah sebagai alat pembenar dari Pelanggan untuk lepas dari segala tanggung jawab dan kewajiban yang telah diatur dalam PERJANJIAN ini;',
        ),
        6 => 
        array (
          'p' => 'PIHAK PERTAMA berhak untuk mengakhiri PERJANJIAN ini dengan memberikan pemberitahuan tertulis kepada Pelanggan (atau, atas kebijakan) PIHAK PERTAMA sendiri menangguhkan jasa, termasuk diantaranya menghentikan pasokan listrik jika:',
        ),
        7 => 
        array (
          'p' => 'Pelanggan melakukan manipulasi bisnis yang berakibat pailit atau terlikuidasi atau berhenti beroperasi;',
        ),
        8 => 
        array (
          'p' => 'Pelanggan dapat dibuktikan melakukan pelanggaran akan PERJANJIAN ini yang menurut penilaian PIHAK PERTAMA memiliki potensi  untuk mengganggu jalannya operasional atau pemeliharaan Gedung PIHAK PERTAMA atau mengganggu Pelanggan lainnya di dalam Gedung, dengan pemberitahuan tertulis yang dilengkapi dengan bukti-bukti otentik atas penilaian  tersebut dan Pelanggan gagal untuk memperbaiki pelanggaran tersebut dalam dua puluh empat (24) jam pemberitahuan yang sama. Maka PIHAK PERTAMA menghentikan Jasa layanan tersebut (termasuk menghentikan pasokan listrik) sampai dengan Pelanggan membayar biaya pemulihan atas kerusakan yang terjadi.',
        ),
        9 => 
        array (
          'p' => 'Pelanggan memberikan jaminan dengan seluruh peralatannya  kepada PIHAK PERTAMA sekarang atau nanti setelah ditempatkan di dalam Gedung, untuk menjamin pembayaran dalam jumlah berapapun dan pemenuhan semua kewajiban yang terdapat dalam PERJANJIAN ini. Sehubungan dengan hal ini, jika dibutuhkan secara hukum, PIHAK PERTAMA akan berhak untuk mengajukan satu atau lebih laporan keuangan untuk jaminan ini dan Pelanggan akan menandatangani semua dokumen yang diperlukan, dan akan mengambil tindakan lain yang diminta PIHAK PERTAMA secara wajar. Ketentuan-ketentuan sebelumnya dari Pasal ini tidak akan mengurangi hak PIHAK PERTAMA untuk melaksanakan hak gadai atas setiap Peralatan Pelanggan untuk pembayaran yang belum dilunasi oleh Pelanggan kepada PIHAK PERTAMA sampai dengan selambat-lambatnya 45 (empat puluh hari) hari sejak tanggal invoice diterbitkan. PIHAK PERTAMA berhak menjual atau menyingkirkan Peralatan Pelanggan tersebut, tanpa pemberitahuan sebelumnya kepada Pelanggan, dengan cara penjualan publik atau privat tanpa proses hukum apapun sebelumnya, pada waktu dan harga yang oleh kebijakan PIHAK PERTAMA dianggap wajar, tanpa mengurangi hak-hak dan perbaikan lain yang dapat dimiliki PIHAK PERTAMA. PIHAK PERTAMA berhak untuk menggunakan hasil penjualan, jika ada, setelah dikurangi biaya yang terkait dengan penjualan atau penyingkiran (termasuk tapi tidak terbatas pada biaya administrasi yang ditentukan oleh PIHAK PERTAMA sendiri), untuk pembayaran semua tagihan yang jatuh tempo dan harus dibayar oleh Pelanggan kepada PIHAK PERTAMA dan jika hasil penjualan tersebut tidak cukup, maka semua jumlah yang belum dilunasi harus dibayarkan kepada PIHAK PERTAMA oleh Pelanggan dalam waktu 30 (tiga puluh) hari setelah adanya permintaan dari PIHAK PERTAMA. Pelanggan akan membebaskan PIHAK PERTAMA terhadap kewajiban apapun yang timbul akibat hal diatas terhadap pihak ketiga;',
        ),
        10 => 
        array (
          'p' => 'Apabila PELANGGAN tidak melaksanakan kewajibannya sebagaimana dimaksud dalam PERJANJIAN ini untuk melunasi pembayaran sampai dengan selambat-lambatnya 30 (tiga puluh) hari sejak tanggal invoice diterbitkan, maka Pelanggan bersedia dan sepakat PIHAK PERTAMA telah memiliki hak sepenuhnya/kewenangan dalam melakukan penjualan secara public ataupun privat tanpa proses hukum apapun atau menyingkirkan peralatan Pelanggan dari lokasi dan PIHAK PERTAMA dibebaskan dari tuntutan oleh Pelanggan.',
        ),
      ),
    ),
    4 => 
    array (
      'judul' => 'BATASAN TANGGUNG JAWAB',
      'text' => 'Tidak ada Pihak (Pihak Yang Bersalah) yang harus bertanggung jawab kepada Pihak lainnya (Pihak Yang Tidak Bersalah) atas hilangnya keuntungan, nama baik atau nilai ekonomi lainnya, atau kerusakan secara tidak konsekuensial atau kehilangan langsung, (termasuk kehilangan atau kerusakan yang diderita oleh Pihak Yang Tidak Bersalah) sebagai akibat  dari suatu tindakan yang dilakukan oleh pihak ketiga;

Para Pihak bersepakat bahwa tanggung jawab masing-masing Pihak sehubungan dengan klaim atas tanggung jawab yang timbul berdasarkan Perjanjian ini, tidak akan melebihi total biaya yang timbul berdasarkan berdasarkan Perjanjian ini;

PIHAK PERTAMA berhak untuk melakukan deinstalasi terhadap layanan yang diberikan kepada Pelanggan yang tidak menjalankan sebagaimana ketentuan yang tercantum pada Pasal 3 ayat (3) PERJANJIAN ini dan PIHAK PERTAMA tidak bertanggung jawab terhadap seluruh kerusakan dan atau kehilangan perangkat milik pelanggan beserta aksesoris perangkat tersebut, jika dalam jangka waktu 60 (enam puluh hari) setelah deinstalasi dilakukan Pelanggan tidak menyelesaikan segala kewajibannya kepada PIHAK PERTAMA.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Tidak ada Pihak (Pihak Yang Bersalah) yang harus bertanggung jawab kepada Pihak lainnya (Pihak Yang Tidak Bersalah) atas hilangnya keuntungan, nama baik atau nilai ekonomi lainnya, atau kerusakan secara tidak konsekuensial atau kehilangan langsung, (termasuk kehilangan atau kerusakan yang diderita oleh Pihak Yang Tidak Bersalah) sebagai akibat  dari suatu tindakan yang dilakukan oleh pihak ketiga;',
        ),
        1 => 
        array (
          'p' => 'Para Pihak bersepakat bahwa tanggung jawab masing-masing Pihak sehubungan dengan klaim atas tanggung jawab yang timbul berdasarkan Perjanjian ini, tidak akan melebihi total biaya yang timbul berdasarkan berdasarkan Perjanjian ini;',
        ),
        2 => 
        array (
          'p' => 'PIHAK PERTAMA berhak untuk melakukan deinstalasi terhadap layanan yang diberikan kepada Pelanggan yang tidak menjalankan sebagaimana ketentuan yang tercantum pada Pasal 3 ayat (3) PERJANJIAN ini dan PIHAK PERTAMA tidak bertanggung jawab terhadap seluruh kerusakan dan atau kehilangan perangkat milik pelanggan beserta aksesoris perangkat tersebut, jika dalam jangka waktu 60 (enam puluh hari) setelah deinstalasi dilakukan Pelanggan tidak menyelesaikan segala kewajibannya kepada PIHAK PERTAMA.',
        ),
      ),
    ),
    5 => 
    array (
      'judul' => 'KERAHASIAAN',
      'text' => 'Para Pihak dalam PERJANJIAN setuju untuk menjaga kerahasiaan, baik selama Jangka Waktu dan setelah pengakhiran atau berakhirnya perjanjian ini, semua informasi (tertulis atau lisan) yang berhubungan dengan bisnis dan pekerjaan Pihak lainnya yang diperoleh atau diketahui atas hasil diskusi yang mengarah kepada atau menuju ke dalam atau untuk pelaksanaan PERJANJIAN ini;

Masing-masing dari Para Pihak setuju kepada Pihak lainnya untuk mengambil semua langkah yang diperlukan dari waktu ke waktu untuk memastikan kepatuhan karyawan, agen, sub-kontraktor atau orang lain di bawah kendalinya terhadap Klausul Pasal 6 ini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Para Pihak dalam PERJANJIAN setuju untuk menjaga kerahasiaan, baik selama Jangka Waktu dan setelah pengakhiran atau berakhirnya perjanjian ini, semua informasi (tertulis atau lisan) yang berhubungan dengan bisnis dan pekerjaan Pihak lainnya yang diperoleh atau diketahui atas hasil diskusi yang mengarah kepada atau menuju ke dalam atau untuk pelaksanaan PERJANJIAN ini;',
        ),
        1 => 
        array (
          'p' => 'Masing-masing dari Para Pihak setuju kepada Pihak lainnya untuk mengambil semua langkah yang diperlukan dari waktu ke waktu untuk memastikan kepatuhan karyawan, agen, sub-kontraktor atau orang lain di bawah kendalinya terhadap Klausul Pasal 6 ini.',
        ),
      ),
    ),
    6 => 
    array (
      'judul' => 'ASURANSI',
      'text' => 'Pihak Pertama akan menjamin perangkat Pelanggan yang ditempatkan pada Gedung sesuai dengan berita acara serah terima perangkat, namun apabila terdapat kehilangan perangkat Pelanggan yang tidak menjadi pertanggungan dalam berita acara serah terima perangkat, maka PIHAK PERTAMA  tidak menanggung penggantian perangkat Pelanggan tersebut yang berada pada Gedung;

Pelanggan akan, atas pilihannya sendiri, memiliki program asuransi properti atau asuransi pihak sendiri yang mencakup kerugian atau kerusakan peralatan dan properti pribadi lain yang berada di dalam Gedung.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Pihak Pertama akan menjamin perangkat Pelanggan yang ditempatkan pada Gedung sesuai dengan berita acara serah terima perangkat, namun apabila terdapat kehilangan perangkat Pelanggan yang tidak menjadi pertanggungan dalam berita acara serah terima perangkat, maka PIHAK PERTAMA  tidak menanggung penggantian perangkat Pelanggan tersebut yang berada pada Gedung;',
        ),
        1 => 
        array (
          'p' => 'Pelanggan akan, atas pilihannya sendiri, memiliki program asuransi properti atau asuransi pihak sendiri yang mencakup kerugian atau kerusakan peralatan dan properti pribadi lain yang berada di dalam Gedung.',
        ),
      ),
    ),
    7 => 
    array (
      'judul' => 'FORCE MAJEURE',
      'text' => 'Keadaan Memaksa (Force Majeure) adalah setiap peristiwa di luar kendali dan kemampuan wajar PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, termasuk namun tidak terbatas pada bencana alam, kebakaran, perang, kerusuhan, wabah penyakit, pemogokan, gangguan jaringan berskala besar, kegagalan sistem di luar kendali PARA PIHAK, kebijakan Pemerintah, atau peristiwa lain yang sejenis.

PIHAK yang mengalami Keadaan Memaksa wajib memberitahukan secara tertulis kepada pihak lainnya paling lambat 14 (empat belas) Hari Kalender sejak diketahui atau seharusnya diketahui terjadinya Keadaan Memaksa, disertai penjelasan mengenai dampak terhadap pelaksanaan Perjanjian.

Selama Keadaan Memaksa berlangsung, kewajiban PARA PIHAK yang terdampak ditangguhkan sepanjang tidak dapat dilaksanakan akibat Keadaan Memaksa. Penangguhan tersebut tidak menghapus kewajiban yang telah timbul sebelum terjadinya Keadaan Memaksa.

PARA PIHAK wajib melakukan upaya yang wajar untuk mengurangi dampak Keadaan Memaksa dan melanjutkan pelaksanaan Perjanjian segera setelah keadaan tersebut berakhir.

Apabila Keadaan Memaksa berlangsung lebih dari 90 (sembilan puluh) Hari Kalender secara berturut-turut dan PARA PIHAK tidak mencapai kesepakatan mengenai kelanjutan Perjanjian, masing-masing pihak berhak mengakhiri Perjanjian dengan pemberitahuan tertulis tanpa dikenakan penalti, dengan tetap menyelesaikan seluruh hak dan kewajiban yang telah timbul sebelum tanggal efektif pengakhiran.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Keadaan Memaksa (Force Majeure) adalah setiap peristiwa di luar kendali dan kemampuan wajar PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, termasuk namun tidak terbatas pada bencana alam, kebakaran, perang, kerusuhan, wabah penyakit, pemogokan, gangguan jaringan berskala besar, kegagalan sistem di luar kendali PARA PIHAK, kebijakan Pemerintah, atau peristiwa lain yang sejenis.',
        ),
        1 => 
        array (
          'p' => 'PIHAK yang mengalami Keadaan Memaksa wajib memberitahukan secara tertulis kepada pihak lainnya paling lambat 14 (empat belas) Hari Kalender sejak diketahui atau seharusnya diketahui terjadinya Keadaan Memaksa, disertai penjelasan mengenai dampak terhadap pelaksanaan Perjanjian.',
        ),
        2 => 
        array (
          'p' => 'Selama Keadaan Memaksa berlangsung, kewajiban PARA PIHAK yang terdampak ditangguhkan sepanjang tidak dapat dilaksanakan akibat Keadaan Memaksa. Penangguhan tersebut tidak menghapus kewajiban yang telah timbul sebelum terjadinya Keadaan Memaksa.',
        ),
        3 => 
        array (
          'p' => 'PARA PIHAK wajib melakukan upaya yang wajar untuk mengurangi dampak Keadaan Memaksa dan melanjutkan pelaksanaan Perjanjian segera setelah keadaan tersebut berakhir.',
        ),
        4 => 
        array (
          'p' => 'Apabila Keadaan Memaksa berlangsung lebih dari 90 (sembilan puluh) Hari Kalender secara berturut-turut dan PARA PIHAK tidak mencapai kesepakatan mengenai kelanjutan Perjanjian, masing-masing pihak berhak mengakhiri Perjanjian dengan pemberitahuan tertulis tanpa dikenakan penalti, dengan tetap menyelesaikan seluruh hak dan kewajiban yang telah timbul sebelum tanggal efektif pengakhiran.',
        ),
      ),
    ),
    8 => 
    array (
      'judul' => 'PENGALIHAN',
      'text' => 'Pelanggan tidak berhak untuk melimpahkan, mentransfer, melisensikan, menyewakan, membebankan sebagian biaya pada, atau berbagi kepemilikan atau membuang atau menawarkan atau memberikan kepada pihak ketiga hak atas Ruang Colocation Pelanggan secara keseluruhan atau sebagian atau melimpahkan, mentransfer atau sebaliknya melepaskan atau membatasi hak Pelanggan dibawah PERJANJIAN ini kecuali sebagaimana diizinkan oleh Klausul Pasal 9 ayat (2);

Dengan persetujuan tertulis PIHAK PERTAMA, Pelanggan dapat:

membagi atau berbagi kepemilikan atau penempatan keseluruhan atau sebagian dari Peralatan dan/atau Colocation Pelanggan dengan Grup Perusahaan dengan ketentuan bahwa penempatan oleh Grup Perusahaan akan berhenti ketika Grup Perusahaan bukan bagian dari grup;

memberikan hak untuk menggunakan atau sebagian dari semua Colocation Pelanggan dan/atau Peralatan kepada    pihak ketiga, dengan ketentuan bahwa pihak ketiga tersebut akan menggunakan Colocation atau Peralatan yang diberikan hak penggunaan atasnya sesuai dengan ketentuan didalam PERJANJIAN ini (kecuali untuk pembayaran Biaya Jasa dan kewajiban lainnya yang tidak bisa diterapkan pada area Colocation dari Ruang Pelanggan yang telah diserahkan hak penggunaannya kepada Pihak ketiga).

Namun Pelanggan akan tetap bertanggung jawab kepada PIHAK PERTAMA untuk pengawasan dan pelaksanaan kewajibannya di bawah PERJANJIAN ini dan Pelanggan harus mengambil semua langkah yang memungkinkan dan sah dan siap untuk memperbaiki pelanggaran atas kewajiban yang dilakukan oleh Pelanggan.

Jika Pelanggan ingin memberikan kepemilikan dari semua Ruang Colocation Pelanggan kepada pihak ketiga yang mempunyai kedudukan keuangan yang sama atau lebih tinggi dari Pelanggan dan pihak ketiga setuju untuk mengadakan perjanjian Colocation baru dengan syarat dan ketentuan yang sebagian besar sama seperti PERJANJIAN ini, maka dapat (atas keputusannya PIHAK PERTAMA semata) menyetujui untuk mengakhiri PERJANJIAN ini dan melakukan perjanjian baru dengan  pihak ketiga.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Pelanggan tidak berhak untuk melimpahkan, mentransfer, melisensikan, menyewakan, membebankan sebagian biaya pada, atau berbagi kepemilikan atau membuang atau menawarkan atau memberikan kepada pihak ketiga hak atas Ruang Colocation Pelanggan secara keseluruhan atau sebagian atau melimpahkan, mentransfer atau sebaliknya melepaskan atau membatasi hak Pelanggan dibawah PERJANJIAN ini kecuali sebagaimana diizinkan oleh Klausul Pasal 9 ayat (2);',
        ),
        1 => 
        array (
          'p' => 'Dengan persetujuan tertulis PIHAK PERTAMA, Pelanggan dapat:',
        ),
        2 => 
        array (
          'p' => 'membagi atau berbagi kepemilikan atau penempatan keseluruhan atau sebagian dari Peralatan dan/atau Colocation Pelanggan dengan Grup Perusahaan dengan ketentuan bahwa penempatan oleh Grup Perusahaan akan berhenti ketika Grup Perusahaan bukan bagian dari grup;',
        ),
        3 => 
        array (
          'p' => 'memberikan hak untuk menggunakan atau sebagian dari semua Colocation Pelanggan dan/atau Peralatan kepada    pihak ketiga, dengan ketentuan bahwa pihak ketiga tersebut akan menggunakan Colocation atau Peralatan yang diberikan hak penggunaan atasnya sesuai dengan ketentuan didalam PERJANJIAN ini (kecuali untuk pembayaran Biaya Jasa dan kewajiban lainnya yang tidak bisa diterapkan pada area Colocation dari Ruang Pelanggan yang telah diserahkan hak penggunaannya kepada Pihak ketiga).',
        ),
        4 => 
        array (
          'p' => 'Namun Pelanggan akan tetap bertanggung jawab kepada PIHAK PERTAMA untuk pengawasan dan pelaksanaan kewajibannya di bawah PERJANJIAN ini dan Pelanggan harus mengambil semua langkah yang memungkinkan dan sah dan siap untuk memperbaiki pelanggaran atas kewajiban yang dilakukan oleh Pelanggan.',
        ),
        5 => 
        array (
          'p' => 'Jika Pelanggan ingin memberikan kepemilikan dari semua Ruang Colocation Pelanggan kepada pihak ketiga yang mempunyai kedudukan keuangan yang sama atau lebih tinggi dari Pelanggan dan pihak ketiga setuju untuk mengadakan perjanjian Colocation baru dengan syarat dan ketentuan yang sebagian besar sama seperti PERJANJIAN ini, maka dapat (atas keputusannya PIHAK PERTAMA semata) menyetujui untuk mengakhiri PERJANJIAN ini dan melakukan perjanjian baru dengan  pihak ketiga.',
        ),
      ),
    ),
    9 => 
    array (
      'judul' => 'UMUM',
      'text' => 'Jika pada suatu waktu ada bagian dari PERJANJIAN ini (satu atau lebih klausa dari PERJANJIAN ini atau bagian dari satu atau lebih dari klausa di dalam ini) dianggap tidak bisa diterapkan atas alasan apapun di bawah hukum yang berlaku, maka ketentuan itu akan dianggap dihilangkan dari PERJANJIAN ini dan keabsahan ketentuan lainnya dalam PERJANJIAN ini tidak akan dengan cara apapun terpengaruh atau terganggu sebagai akibat dari penghilangan  ini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Jika pada suatu waktu ada bagian dari PERJANJIAN ini (satu atau lebih klausa dari PERJANJIAN ini atau bagian dari satu atau lebih dari klausa di dalam ini) dianggap tidak bisa diterapkan atas alasan apapun di bawah hukum yang berlaku, maka ketentuan itu akan dianggap dihilangkan dari PERJANJIAN ini dan keabsahan ketentuan lainnya dalam PERJANJIAN ini tidak akan dengan cara apapun terpengaruh atau terganggu sebagai akibat dari penghilangan  ini.',
        ),
      ),
    ),
    10 => 
    array (
      'judul' => 'HUKUM YANG TEPAT DAN YURISDIKSI',
      'text' => 'PERJANJIAN ini dan setiap sengketa, yang tidak dapat diselesaikan yang timbul dari atau dalam hubungannya dengan PERJANJIAN ini akan diatur oleh dan ditafsirkan sesuai dengan hukum yang berlaku  di Indonesia;

Para Pihak Sepakat:

Apabila terjadi perselisihan antara Para Pihak sehubungan dengan Perjanjian ini, maka Para Pihak sepakat untuk menyelesaikannya secara musyawarah untuk mufakat dalam jangka waktu 30 (tiga puluh) hari;

Apabila penyelesaian secara musyawarah tidak tercapai, maka masing-masing pihak sepakat untuk menempuh jalur hukum melalui Kepaniteraan Pengadilan Negeri Kota Cirebon;

PERJANJIAN ini tetap berlaku dan Para Pihak tetap melaksanakan hak dan kewajiban masing-masing, sampai perselisihan dimaksud sebagaimana Pasal ini mendapatkan penyelesaian baik sebagai hasil musyawarah maupun berdasar atas keputusan hukum yang mempunyai kekuatan hukum yang bersifat tetap.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PERJANJIAN ini dan setiap sengketa, yang tidak dapat diselesaikan yang timbul dari atau dalam hubungannya dengan PERJANJIAN ini akan diatur oleh dan ditafsirkan sesuai dengan hukum yang berlaku  di Indonesia;',
        ),
        1 => 
        array (
          'p' => 'Para Pihak Sepakat:',
        ),
        2 => 
        array (
          'p' => 'Apabila terjadi perselisihan antara Para Pihak sehubungan dengan Perjanjian ini, maka Para Pihak sepakat untuk menyelesaikannya secara musyawarah untuk mufakat dalam jangka waktu 30 (tiga puluh) hari;',
        ),
        3 => 
        array (
          'p' => 'Apabila penyelesaian secara musyawarah tidak tercapai, maka masing-masing pihak sepakat untuk menempuh jalur hukum melalui Kepaniteraan Pengadilan Negeri Kota Cirebon;',
        ),
        4 => 
        array (
          'p' => 'PERJANJIAN ini tetap berlaku dan Para Pihak tetap melaksanakan hak dan kewajiban masing-masing, sampai perselisihan dimaksud sebagaimana Pasal ini mendapatkan penyelesaian baik sebagai hasil musyawarah maupun berdasar atas keputusan hukum yang mempunyai kekuatan hukum yang bersifat tetap.',
        ),
      ),
    ),
    11 => 
    array (
      'judul' => 'BIAYA DAN BEBAN',
      'text' => 'Setiap Pihak akan menanggung sendiri biaya hukum dan biaya lainnya. Serta pengeluaran yang berkaitan dengan negosiasi dan pelaksanaan PERJANJIAN ini berhubungan dengan persiapan layanan;

PIHAK PERTAMA berhak melakukan perubahan biaya layanan kepada Pelanggan atas jasa layanan yang telah diberikan dan sedang berjalan dengan melakukan pemberitahuan terlebih  dahulu kepada Pelanggan jika terjadi perubahaan kebijakan pemerintah atau pihak pengelola Gedung terkait dengan kebijakan biaya listrik, biaya dasar sewa gedung ataupun kebijakan lainnya.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Setiap Pihak akan menanggung sendiri biaya hukum dan biaya lainnya. Serta pengeluaran yang berkaitan dengan negosiasi dan pelaksanaan PERJANJIAN ini berhubungan dengan persiapan layanan;',
        ),
        1 => 
        array (
          'p' => 'PIHAK PERTAMA berhak melakukan perubahan biaya layanan kepada Pelanggan atas jasa layanan yang telah diberikan dan sedang berjalan dengan melakukan pemberitahuan terlebih  dahulu kepada Pelanggan jika terjadi perubahaan kebijakan pemerintah atau pihak pengelola Gedung terkait dengan kebijakan biaya listrik, biaya dasar sewa gedung ataupun kebijakan lainnya.',
        ),
      ),
    ),
    12 => 
    array (
      'judul' => 'AMANDEMEN',
      'text' => 'PERJANJIAN ini tidak akan diubah, diganti, ditambah atau diamandemen kecuali dengan instrumen tertulis atau kesepakatan yang ditandatangani oleh Para Pihak.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PERJANJIAN ini tidak akan diubah, diganti, ditambah atau diamandemen kecuali dengan instrumen tertulis atau kesepakatan yang ditandatangani oleh Para Pihak.',
        ),
      ),
    ),
    13 => 
    array (
      'judul' => 'PERBAIKAN',
      'text' => 'Tidak ada perbaikan yang diberikan oleh ketentuan dalam PERJANJIAN  ini dimaksudkan untuk menjadi eksklusif atas perbaikan lain  yang tersedia berdasarkan hukum, dalam ekuitas, oleh undang-undang atau lainnya, serta setiap dan semua perbaikan lain akan bersifat kumulatif dan akan menjadi tambahan untuk   setiap perbaikan lain di dalam ini atau di saat ini atau dimasa yang akan datang berdasarkan hukum, dalam ekuitas, oleh undang-undang atau lainnya. Pemilihan atas satu atau lebih perbaikan tersebut oleh salah satu dari Para Pihak dalam perjanjian ini tidak bisa menjadi pengesampingan oleh Pihak tersebut atas hak untuk mendapatkan setiap perbaikan lain yang tersedia.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Tidak ada perbaikan yang diberikan oleh ketentuan dalam PERJANJIAN  ini dimaksudkan untuk menjadi eksklusif atas perbaikan lain  yang tersedia berdasarkan hukum, dalam ekuitas, oleh undang-undang atau lainnya, serta setiap dan semua perbaikan lain akan bersifat kumulatif dan akan menjadi tambahan untuk   setiap perbaikan lain di dalam ini atau di saat ini atau dimasa yang akan datang berdasarkan hukum, dalam ekuitas, oleh undang-undang atau lainnya. Pemilihan atas satu atau lebih perbaikan tersebut oleh salah satu dari Para Pihak dalam perjanjian ini tidak bisa menjadi pengesampingan oleh Pihak tersebut atas hak untuk mendapatkan setiap perbaikan lain yang tersedia.',
        ),
      ),
    ),
    14 => 
    array (
      'judul' => 'PENALTI',
      'text' => 'Ketentuan mengenai jenis pelanggaran, perubahan yang diminta

Pelanggan serta penalti yang dikenakan adalah sebagai berikut:

Terminasi Dini. Apabila ada permintaan tertulis dari Pelanggan untuk berhenti berlangganan sebelum berakhirnya Jangka Waktu PERJANJIAN ini termasuk terminasi dini untuk jangka waktu perpanjangan PERJANJIAN atau apabila terjadi pelanggaran oleh Pelanggan terhadap ketentuan dan syarat-syarat PERJANJIAN ini yang mengakibatkan diakhirinya PERJANJIAN ini oleh PIHAK PERTAMA sebelum berakhirnya Pelanggan dikenakan Penalti sebagai berikut:

Dalam hal terminasi dini dilakukan dalam jangka waktu (periode) pertama Pelanggan, Pelanggan wajib membayar penuh sisa masa berlangganan, membayar berlangganan (100% x Biaya Bulanan x Bulan yang belum terpenuhi).

Dalam hal terminasi dini yang dilakukan dalam jangka waktu perpanjangan berlangganan, Pelanggan wajib waktu perpanjangan membayar sisa masa berlangganan sebesar 50% dari Biaya Layanan bulanan untuk sisa jangka waktu perpanjangan berlangganan (50% x Biaya Bulanan x Bulan yang belum terpenuhi).

Penurunan Kapasitas Listrik. Apabila ada permintaan tertulis dari Pelanggan untuk menurunkan kapasitas listrik, Pelanggan akan dikenakan penalti dengan ketentuan sebagai berikut:

Untuk lama pemakaian kurang dari jangka waktu berlangganan yang ditetapkan dalam Perjanjian Berlangganan Jasa Colocation ini, Pelanggan wajib membayar þenalti sebesar 100% x (biaya per bulan kapasitas lama - biaya per bulan kapasitas baru) x sisa  jangka waktu berlangganan.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Ketentuan mengenai jenis pelanggaran, perubahan yang diminta',
        ),
        1 => 
        array (
          'p' => 'Pelanggan serta penalti yang dikenakan adalah sebagai berikut:',
        ),
        2 => 
        array (
          'p' => 'Terminasi Dini. Apabila ada permintaan tertulis dari Pelanggan untuk berhenti berlangganan sebelum berakhirnya Jangka Waktu PERJANJIAN ini termasuk terminasi dini untuk jangka waktu perpanjangan PERJANJIAN atau apabila terjadi pelanggaran oleh Pelanggan terhadap ketentuan dan syarat-syarat PERJANJIAN ini yang mengakibatkan diakhirinya PERJANJIAN ini oleh PIHAK PERTAMA sebelum berakhirnya Pelanggan dikenakan Penalti sebagai berikut:',
        ),
        3 => 
        array (
          'p' => 'Dalam hal terminasi dini dilakukan dalam jangka waktu (periode) pertama Pelanggan, Pelanggan wajib membayar penuh sisa masa berlangganan, membayar berlangganan (100% x Biaya Bulanan x Bulan yang belum terpenuhi).',
        ),
        4 => 
        array (
          'p' => 'Dalam hal terminasi dini yang dilakukan dalam jangka waktu perpanjangan berlangganan, Pelanggan wajib waktu perpanjangan membayar sisa masa berlangganan sebesar 50% dari Biaya Layanan bulanan untuk sisa jangka waktu perpanjangan berlangganan (50% x Biaya Bulanan x Bulan yang belum terpenuhi).',
        ),
        5 => 
        array (
          'p' => 'Penurunan Kapasitas Listrik. Apabila ada permintaan tertulis dari Pelanggan untuk menurunkan kapasitas listrik, Pelanggan akan dikenakan penalti dengan ketentuan sebagai berikut:',
        ),
        6 => 
        array (
          'p' => 'Untuk lama pemakaian kurang dari jangka waktu berlangganan yang ditetapkan dalam Perjanjian Berlangganan Jasa Colocation ini, Pelanggan wajib membayar þenalti sebesar 100% x (biaya per bulan kapasitas lama - biaya per bulan kapasitas baru) x sisa  jangka waktu berlangganan.',
        ),
      ),
    ),
    15 => 
    array (
      'judul' => 'BANTUAN AUDIT',
      'text' => 'PIHAK PERTAMA akan memberikan kepada Pelanggan (atas biaya Pelanggan), dan setiap auditor hukum atau peraturannya, dan agen resminya, akses ke lokasi yang relevan, personil dengan penyediaan Jasa sebagaimana yang mungkin diperlukan oleh Pelanggan untuk tujuan:

melakukan audit hukum termasuk untuk tujuan mempersiapkan laporan tahunan dan interim Pelanggan dan laporan lainnya yang diperlukan dengan audit eksternal dan internal atau oleh otoritas PIHAK PERTAMA dan catatan yang berkaitan pengawas yang berwenang;

melakukan audit IT security Pelanggan dan Kelompok Pelanggan;

memberikan informasi kepada otoritas pengawas yang berwenang; dan/atau

melakukan audit penyediaan Jasa dan biaya yang dibayarkan dibawah PERJANJIAN ini.

PIHAK PERTAMA akan menyediakan Pelanggan dengan semua kerjasama dan bantuan sewajarnya yang berkaitan dengan audit  dan, atas permintaan Pelanggan akan bekerjasama dengan  regulator dari Pelanggan atau Grup Pelanggan dengan cara  yang terbuka dan wajar. Pelanggan harus pemberitahuan terlebih dahulu sekurang-kurangnya empat belas (14) hari dari setiap permintaan audit. PIHAK PERTAMA berhak untuk menolak audit pada tanggal tertentu dan untuk menyarankan tanggal alternatif;

Pelanggan tidak diperbolehkan dalam proses audit ini menggunakan orang yang merupakan pesaing langsung dari PIHAK PERTAMA atau, kecuali ada perjanjian untuk menjaga kerahasiaan yang sudah disetujui, setiap orang yang terlibat  dengan pesaing dari PIHAK PERTAMA;

Pelanggan setuju untuk membayar PIHAK PERTAMA atas semua biaya yang dikeluarkan oleh PIHAK PERTAMA, dengan persetujuan Pelanggan terlebih dahulu atas biaya-biaya yang akan dikeluarkan tersebut, dalam kaitannya dengan audit yang dilakukan oleh Pelanggan atau wakil-wakilnya.

SEBAGAI SAKSI DARI APA YANG DIKEMUKAKAN DI ATAS, Para Pihak

telah menandatangani PERJANJIAN ini pada Tanggal Efektif sebagaimana tertulis di atas.

Pihak yang bertanda tangan di bawah dengan ini menjamin dan menyatakan bahwa ia memiliki otoritas penuh untuk menandatangani PERJANJIAN ini dan atau untuk Pihak yang diwakilkan atas namanya. PERJANJIAN ini dapat ditandatangani dalam sejumlah salinan, setiap salinan akan dianggap sebagai dokumen asli dan keseluruhan merupakan salinan yang sama bunyinya.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK PERTAMA akan memberikan kepada Pelanggan (atas biaya Pelanggan), dan setiap auditor hukum atau peraturannya, dan agen resminya, akses ke lokasi yang relevan, personil dengan penyediaan Jasa sebagaimana yang mungkin diperlukan oleh Pelanggan untuk tujuan:',
        ),
        1 => 
        array (
          'p' => 'melakukan audit hukum termasuk untuk tujuan mempersiapkan laporan tahunan dan interim Pelanggan dan laporan lainnya yang diperlukan dengan audit eksternal dan internal atau oleh otoritas PIHAK PERTAMA dan catatan yang berkaitan pengawas yang berwenang;',
        ),
        2 => 
        array (
          'p' => 'melakukan audit IT security Pelanggan dan Kelompok Pelanggan;',
        ),
        3 => 
        array (
          'p' => 'memberikan informasi kepada otoritas pengawas yang berwenang; dan/atau',
        ),
        4 => 
        array (
          'p' => 'melakukan audit penyediaan Jasa dan biaya yang dibayarkan dibawah PERJANJIAN ini.',
        ),
        5 => 
        array (
          'p' => 'PIHAK PERTAMA akan menyediakan Pelanggan dengan semua kerjasama dan bantuan sewajarnya yang berkaitan dengan audit  dan, atas permintaan Pelanggan akan bekerjasama dengan  regulator dari Pelanggan atau Grup Pelanggan dengan cara  yang terbuka dan wajar. Pelanggan harus pemberitahuan terlebih dahulu sekurang-kurangnya empat belas (14) hari dari setiap permintaan audit. PIHAK PERTAMA berhak untuk menolak audit pada tanggal tertentu dan untuk menyarankan tanggal alternatif;',
        ),
        6 => 
        array (
          'p' => 'Pelanggan tidak diperbolehkan dalam proses audit ini menggunakan orang yang merupakan pesaing langsung dari PIHAK PERTAMA atau, kecuali ada perjanjian untuk menjaga kerahasiaan yang sudah disetujui, setiap orang yang terlibat  dengan pesaing dari PIHAK PERTAMA;',
        ),
        7 => 
        array (
          'p' => 'Pelanggan setuju untuk membayar PIHAK PERTAMA atas semua biaya yang dikeluarkan oleh PIHAK PERTAMA, dengan persetujuan Pelanggan terlebih dahulu atas biaya-biaya yang akan dikeluarkan tersebut, dalam kaitannya dengan audit yang dilakukan oleh Pelanggan atau wakil-wakilnya.',
        ),
        8 => 
        array (
          'p' => 'SEBAGAI SAKSI DARI APA YANG DIKEMUKAKAN DI ATAS, Para Pihak',
        ),
        9 => 
        array (
          'p' => 'telah menandatangani PERJANJIAN ini pada Tanggal Efektif sebagaimana tertulis di atas.',
        ),
        10 => 
        array (
          'p' => 'Pihak yang bertanda tangan di bawah dengan ini menjamin dan menyatakan bahwa ia memiliki otoritas penuh untuk menandatangani PERJANJIAN ini dan atau untuk Pihak yang diwakilkan atas namanya. PERJANJIAN ini dapat ditandatangani dalam sejumlah salinan, setiap salinan akan dianggap sebagai dokumen asli dan keseluruhan merupakan salinan yang sama bunyinya.',
        ),
      ),
    ),
  ),
  'tutup' => 'Pihak Pertama

PT Bina Informatika Solusi

Pihak Kedua

PT Solusindo Bintang Pratama

Ageng Bagja Priyadi, S.T., M.Kom

Direktur

Budiarto

Direktur',
  'tutupBlocks' => 
  array (
    0 => 
    array (
      'table' => 
      array (
        'rows' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'c' => 
              array (
                0 => 'Pihak Pertama',
                1 => 'PT Bina Informatika Solusi',
              ),
              's' => 1,
            ),
            1 => 
            array (
              'c' => 
              array (
                0 => 'Pihak Kedua',
                1 => 'PT Solusindo Bintang Pratama',
              ),
              's' => 1,
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'c' => 
              array (
                0 => 'Ageng Bagja Priyadi, S.T., M.Kom',
                1 => 'Direktur',
              ),
              's' => 1,
            ),
            1 => 
            array (
              'c' => 
              array (
                0 => 'Budiarto',
                1 => 'Direktur',
              ),
              's' => 1,
            ),
          ),
        ),
        'bordered' => false,
        'head' => true,
      ),
    ),
  ),
  'lampiran' => 
  array (
    0 => 
    array (
      'judul' => 'DESKRIPSI LAYANAN',
      'text' => 'Syarat dan Ketentuan

Harga diatas belum termasuk PPN

Minimal Kontrak Berlanggan 1 (satu) tahun

Jangka Waktu Pembayaran: Perbulan (Maksimal pada 7 hari kalender sejak invoice diterima secara lengkap dan benar)

Fasilitas Data Center

Konsep

Desain Data Center Tier-II

Data Center dengan realibilitas yang tinggi

Lingkungan

Ruangan dilengkapi Air Condition (AC)

Suhu ruangan 20o C (dua puluh derajat selsius)

Ketinggian raised floor 500 mm (lima ratus milimiter)

Daya tampung/kekuatan lantai 1.000 Kg (seribu kilogram) permeter persegi

Realibilitas

Daya UPS Terproteksi Penuh N+1 oleh Eaton

Ketersediaan daya 2,5 MW (dua koma lima mega wat)

Pasokan daya melalui jalur tunggal ke rak server

Opsi pasokan tersedia daya AC (Alternating Current) dan DC (Direct Current)

Pengaduan Layanan dan Sistem Keamanan

Operator Network Operation Center (NOC) memantau status operasional peralatan dan stabilitasnya selama 24 jam 7 hari.

Cakupan CCTV kamera video digital terintegrasi di seluruh data center.

Sistem pelacakan pengunjung, prosedur akses keamanan tersedia untuk memastikan bahwa hanya staf atau pengunjung yang ditunjuk yang mendapatkan akses resmi.

Sistem akses keamanan dengan RFID.

Menyediakan bantuan teknis smarthand: Layanan smarthand fisik sesuai permintaan, KVM jarak jauh sesuai permintaan, dll.

Lokasi Data Center

Gedung Wisma Bumiputera, Lantai 7 Suite #701B,

Jl. Asia Afrika No.141-149, Kb. Pisang, Kec. Sumur Bandung, Kota  Bandung, Jawa Barat 40112

Rekening RupiahBank MandiriNo. Rekening: 1340001209104	Atas Nama	: PT Bina Informatika Solusi		Bank Rakyat Indonesia (BRI)	No. Rekening: 010701003038305 	Atas Nama   : PT Bina Informatika SolusiRekening RupiahBank MandiriNo. Rekening: 1340001209104	Atas Nama	: PT Bina Informatika Solusi		Bank Rakyat Indonesia (BRI)	No. Rekening: 010701003038305 	Atas Nama   : PT Bina Informatika SolusiPelanggan menerima tagihan/invoice paling lambat sebelum tanggal 1 bulan berjalan dan tanggal jatuh tempo  setiap tanggal 7 bulan berjalan melalui sistem transfer ke nomor rekening:',
      'blocks' => 
      array (
        0 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'No.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Layanan',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Layanan',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Instalasi',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => 'Jangka Waktu Berlangganan',
                  ),
                  's' => 1,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '1.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Colocation Full Rack 42 U',
                    1 => '(Free Xconnect Inner Dacen SBP >< Fiberstar di Lantai 7)',
                    2 => '(Free Xconnect Inner Dacen SBP >< TIS di Lantai 8)',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp6.000.000 (Perbulan)',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp1.000.000',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => '25 Juli 2026 – 24 Juli 2027',
                  ),
                  's' => 1,
                ),
              ),
              2 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '2.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Add Electricity 6 Ampere',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp2.550.000 (Perbulan)',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp2.500.000',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
              3 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '3.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Xconnect 1 Pair SBP >< Asianet di Lt.9',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp3.000.000 (Pertahun)',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp3.000.000',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
              4 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '4.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Xconnect 1 Pair SBP >< CIFO di Lt. 8',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp3.000.000 (Pertahun)',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp3.000.000',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
              5 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '5.',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Xconnect 1 Pair SBP >< TIS di Lt. 10',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp3.000.000 (Pertahun)',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp3.000.000',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
        1 => 
        array (
          'p' => 'Syarat dan Ketentuan',
        ),
        2 => 
        array (
          'p' => 'Harga diatas belum termasuk PPN',
        ),
        3 => 
        array (
          'p' => 'Minimal Kontrak Berlanggan 1 (satu) tahun',
        ),
        4 => 
        array (
          'p' => 'Jangka Waktu Pembayaran: Perbulan (Maksimal pada 7 hari kalender sejak invoice diterima secara lengkap dan benar)',
        ),
        5 => 
        array (
          'p' => 'Fasilitas Data Center',
        ),
        6 => 
        array (
          'p' => 'Konsep',
        ),
        7 => 
        array (
          'p' => 'Desain Data Center Tier-II',
        ),
        8 => 
        array (
          'p' => 'Data Center dengan realibilitas yang tinggi',
        ),
        9 => 
        array (
          'p' => 'Lingkungan',
        ),
        10 => 
        array (
          'p' => 'Ruangan dilengkapi Air Condition (AC)',
        ),
        11 => 
        array (
          'p' => 'Suhu ruangan 20o C (dua puluh derajat selsius)',
        ),
        12 => 
        array (
          'p' => 'Ketinggian raised floor 500 mm (lima ratus milimiter)',
        ),
        13 => 
        array (
          'p' => 'Daya tampung/kekuatan lantai 1.000 Kg (seribu kilogram) permeter persegi',
        ),
        14 => 
        array (
          'p' => 'Realibilitas',
        ),
        15 => 
        array (
          'p' => 'Daya UPS Terproteksi Penuh N+1 oleh Eaton',
        ),
        16 => 
        array (
          'p' => 'Ketersediaan daya 2,5 MW (dua koma lima mega wat)',
        ),
        17 => 
        array (
          'p' => 'Pasokan daya melalui jalur tunggal ke rak server',
        ),
        18 => 
        array (
          'p' => 'Opsi pasokan tersedia daya AC (Alternating Current) dan DC (Direct Current)',
        ),
        19 => 
        array (
          'p' => 'Pengaduan Layanan dan Sistem Keamanan',
        ),
        20 => 
        array (
          'p' => 'Operator Network Operation Center (NOC) memantau status operasional peralatan dan stabilitasnya selama 24 jam 7 hari.',
        ),
        21 => 
        array (
          'p' => 'Cakupan CCTV kamera video digital terintegrasi di seluruh data center.',
        ),
        22 => 
        array (
          'p' => 'Sistem pelacakan pengunjung, prosedur akses keamanan tersedia untuk memastikan bahwa hanya staf atau pengunjung yang ditunjuk yang mendapatkan akses resmi.',
        ),
        23 => 
        array (
          'p' => 'Sistem akses keamanan dengan RFID.',
        ),
        24 => 
        array (
          'p' => 'Menyediakan bantuan teknis smarthand: Layanan smarthand fisik sesuai permintaan, KVM jarak jauh sesuai permintaan, dll.',
        ),
        25 => 
        array (
          'p' => 'Lokasi Data Center',
        ),
        26 => 
        array (
          'p' => 'Gedung Wisma Bumiputera, Lantai 7 Suite #701B,',
        ),
        27 => 
        array (
          'p' => 'Jl. Asia Afrika No.141-149, Kb. Pisang, Kec. Sumur Bandung, Kota  Bandung, Jawa Barat 40112',
        ),
        28 => 
        array (
          'p' => 'Rekening RupiahBank MandiriNo. Rekening: 1340001209104	Atas Nama	: PT Bina Informatika Solusi		Bank Rakyat Indonesia (BRI)	No. Rekening: 010701003038305 	Atas Nama   : PT Bina Informatika SolusiRekening RupiahBank MandiriNo. Rekening: 1340001209104	Atas Nama	: PT Bina Informatika Solusi		Bank Rakyat Indonesia (BRI)	No. Rekening: 010701003038305 	Atas Nama   : PT Bina Informatika SolusiPelanggan menerima tagihan/invoice paling lambat sebelum tanggal 1 bulan berjalan dan tanggal jatuh tempo  setiap tanggal 7 bulan berjalan melalui sistem transfer ke nomor rekening:',
        ),
      ),
    ),
  ),
),
            ],
            'kontrak-managed-service' => [
                'title' => 'Perjanjian Berlangganan Jasa Dedicated, Metro, dan Managed Service',
                'header_data' => array (
  'kopInstansi' => '[Nama Perusahaan PIHAK PERTAMA]',
  'kopAlamat' => '[Alamat perusahaan PIHAK PERTAMA]',
  'kopKontrak' => 'PERJANJIAN BERLANGGANAN',
  'nomorSurat' => '[Nomor Perjanjian]',
  'perihalSurat' => 'Jasa Dedicated, Metro, dan Managed Service',
  'sifatSurat' => 'Penting',
),
                'body_content' => array (
  'preamble' => 'PERJANJIAN BERLANGGANAN

JASA MANAGED SERVICE

PT BINA INFORMATIKA SOLUSINDO

DENGAN

PT DINAR WAHANA GEMILANG

Nomor: 335/FBC/J.MS/IV/2026

PERJANJIAN BERLANGGANAN

JASA MANAGED SERVICE

Nomor: 335/FBC/J.MS/IV/2026

Pada hari ini, Rabu, tanggal 1 April 2026, bertempat di Bandung, telah dibuat dan ditandatangani Perjanjian, oleh dan antara:

PT Bina Informatika Solusindo, berkedudukan di Gedung Wisma Bumiputera Lantai 7 Suite #701B Jl. Asia-Afrika No.141-149 Kebon Pisang, Sumur, Kota Bandung. Berdasarkan Akta Berita Acara RUPS Tahunan Perseroan Terbatas “PT Bina Informatika Solusi”, Nomor 5, tanggal 10 Juli 2026, dibuat dihadapan Irni Yuniati, S.H., M.Kn., Notaris di Kota Cimahi. Dalam hal ini diwakili oleh Ageng Bagja Priyadi, S.T.,M.Kom., selaku Direktur, bertindak untuk dan atas nama PT Bina Informatika Solusindo, selanjutnya disebut “PIHAK PERTAMA”

PT Dinar Wahana Gemilang, berkedudukan di Jl. Cetarip Barat (Cetarip raya) No. 15/200 Rt 05 Rw 10, Kopo, Kota Bandung. Berdasarkan Akta Perusahaan No: 16, Tanggal 12 Desember 2022, dibuat dihadapan Arief Karisma, S.H., M.Kn.,  notaris di Kabupaten Bandung. Dalam hal ini diwakili oleh Wildan Arief Santika Budi, selaku Direktur Utama, bertindak untuk dan atas nama PT Dinar Wahana Gemilang, selanjutnya disebut “PIHAK KEDUA”

PIHAK PERTAMA dan PIHAK KEDUA secara bersama-sama selanjutnya disebut “PARA PIHAK”

PARA PIHAK dengan ini menerangkan telah sepakat untuk mengikatkan diri pada syarat-syarat dan ketentuan-ketentuan sebagai berikut:',
  'isi' => 
  array (
    0 => 
    array (
      'judul' => 'Definisi',
      'text' => '“Perjanjian” adalah Perjanjian ini berikut lampiran dan semua perubahan yang terkait dan merupakan bagian dari Perjanjian ini.

“Jasa” adalah layanan yang harus dipenuhi oleh PIHAK PERTAMA sebagaimana diuraikan pada Lampiran A.

“Biaya Jasa” adalah biaya yang harus dibayar oleh PIHAK KEDUA seperti diuraikan pada Lampiran A.

“SLA” – Service Level Agreement adalah kriteria hasil kerja Jasa yang telah ditetapkan terlebih dahulu sebagaimana diuraikan pada Lampiran B.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => '“Perjanjian” adalah Perjanjian ini berikut lampiran dan semua perubahan yang terkait dan merupakan bagian dari Perjanjian ini.',
        ),
        1 => 
        array (
          'p' => '“Jasa” adalah layanan yang harus dipenuhi oleh PIHAK PERTAMA sebagaimana diuraikan pada Lampiran A.',
        ),
        2 => 
        array (
          'p' => '“Biaya Jasa” adalah biaya yang harus dibayar oleh PIHAK KEDUA seperti diuraikan pada Lampiran A.',
        ),
        3 => 
        array (
          'p' => '“SLA” – Service Level Agreement adalah kriteria hasil kerja Jasa yang telah ditetapkan terlebih dahulu sebagaimana diuraikan pada Lampiran B.',
        ),
      ),
    ),
    1 => 
    array (
      'judul' => 'Fasilitas PIHAK PERTAMA',
      'text' => 'PIHAK PERTAMA sepakat untuk menyediakan Jasa dan Fasilitas terkait (selanjutnya disebut “Jasa”) sebagaimana tercantum dalam Service Order Form yang dikeluarkan oleh PIHAK KEDUA yang menjadi bagian tak terpisahkan dari Perjanjian ini;

Layanan Jasa yang disediakan PIHAK PERTAMA berdasarkan Perjanjian ini dapat digunakan oleh PIHAK KEDUA selama 24 jam/hari (7 hari/minggu);

Penyediaan Fasilitas dan Jasa PIHAK PERTAMA akan dilakukan sesuai dengan konfigurasi teknis yang telah disepakati;

Terminal dan perangkat antarmuka milik PIHAK KEDUA yang akan dihubungkan dengan perangkat/saluran PIHAK PERTAMA harus mendapat persetujuan terlebih dahulu dari PIHAK PERTAMA;

Penyambungan pelayanan PIHAK PERTAMA akan dilaksanakan setelah PIHAK KEDUA mengeluarkan Service Order Form dan diterima oleh pihak PIHAK PERTAMA.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK PERTAMA sepakat untuk menyediakan Jasa dan Fasilitas terkait (selanjutnya disebut “Jasa”) sebagaimana tercantum dalam Service Order Form yang dikeluarkan oleh PIHAK KEDUA yang menjadi bagian tak terpisahkan dari Perjanjian ini;',
        ),
        1 => 
        array (
          'p' => 'Layanan Jasa yang disediakan PIHAK PERTAMA berdasarkan Perjanjian ini dapat digunakan oleh PIHAK KEDUA selama 24 jam/hari (7 hari/minggu);',
        ),
        2 => 
        array (
          'p' => 'Penyediaan Fasilitas dan Jasa PIHAK PERTAMA akan dilakukan sesuai dengan konfigurasi teknis yang telah disepakati;',
        ),
        3 => 
        array (
          'p' => 'Terminal dan perangkat antarmuka milik PIHAK KEDUA yang akan dihubungkan dengan perangkat/saluran PIHAK PERTAMA harus mendapat persetujuan terlebih dahulu dari PIHAK PERTAMA;',
        ),
        4 => 
        array (
          'p' => 'Penyambungan pelayanan PIHAK PERTAMA akan dilaksanakan setelah PIHAK KEDUA mengeluarkan Service Order Form dan diterima oleh pihak PIHAK PERTAMA.',
        ),
      ),
    ),
    2 => 
    array (
      'judul' => 'Aktivasi Layanan',
      'text' => 'Aktivasi Layanan akan dimulai setelah Fasilitas PIHAK PERTAMA siap dioperasikan dan dinyatakan dengan Berita Acara Aktivasi yang ditandatangani oleh PARA PIHAK.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Aktivasi Layanan akan dimulai setelah Fasilitas PIHAK PERTAMA siap dioperasikan dan dinyatakan dengan Berita Acara Aktivasi yang ditandatangani oleh PARA PIHAK.',
        ),
      ),
    ),
    3 => 
    array (
      'judul' => 'Jangka Waktu Berlangganan',
      'text' => 'Jangka Waktu Efektif Layanan sebagaimana dimaksud dalam Syarat dan Ketentuan Berlangganan ini adalah tanggal sebagaimana dimaksud dalam lampiran A Perjanjian Berlangganan Jasa ini.

Apabila PIHAK KEDUA  mengakhiri Layanan sebelum Jangka Waktu berakhir sebagaimana dimaksud dalam lampiran A dan atau Service Order Form, maka PIHAK KEDUA akan dikenakan denda sebagaimana berikut:

Apabila 30 (Tiga puluh) hari sebelum jangka waktu dalam pasal  4 ayat 1 ini berakhir PIHAK KEDUA tidak melakukan pemberitahuan pengakhiran Layanan, maka Syarat dan Ketentuan Berlangganan akan otomatis berlanjut selama 1 (Satu) tahun (“Jangka Waktu Perpanjangan”). Pemberitahuan pengakhiran Berlangganan dihitung 30 (tiga puluh) hari sejak diterimanya pemberitahuan pengakhiran Layanan;',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Jangka Waktu Efektif Layanan sebagaimana dimaksud dalam Syarat dan Ketentuan Berlangganan ini adalah tanggal sebagaimana dimaksud dalam lampiran A Perjanjian Berlangganan Jasa ini.',
        ),
        1 => 
        array (
          'p' => 'Apabila PIHAK KEDUA  mengakhiri Layanan sebelum Jangka Waktu berakhir sebagaimana dimaksud dalam lampiran A dan atau Service Order Form, maka PIHAK KEDUA akan dikenakan denda sebagaimana berikut:',
        ),
        2 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '100% x Biaya Bulanan x Bulan yang belum terpenuhi',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
        3 => 
        array (
          'p' => 'Apabila 30 (Tiga puluh) hari sebelum jangka waktu dalam pasal  4 ayat 1 ini berakhir PIHAK KEDUA tidak melakukan pemberitahuan pengakhiran Layanan, maka Syarat dan Ketentuan Berlangganan akan otomatis berlanjut selama 1 (Satu) tahun (“Jangka Waktu Perpanjangan”). Pemberitahuan pengakhiran Berlangganan dihitung 30 (tiga puluh) hari sejak diterimanya pemberitahuan pengakhiran Layanan;',
        ),
      ),
    ),
    4 => 
    array (
      'judul' => 'Pembayaran',
      'text' => 'PIHAK KEDUA wajib melakukan pembayaran atas Layanan sebagaimana dimaksud dalam Service Order Form;

Pembayaran dilakukan oleh PIHAK KEDUA selambat–lambatnya pada tanggal sesuai dengan invoice yang diterbitkan atau selambat-lambatnya 7 (tujuh) hari dari tanggal invoice diterbitkan;

Atas setiap keterlambatan pembayaran dari tanggal sebagaimana dimaksud dalam Pasal 5.2, maka PIHAK KEDUA dikenakan denda keterlambatan pembayaran sebesar 1 (satu) permil setiap hari keterlambatan;

Apabila PIHAK KEDUA terlambat melakukan pembayaran 30 (tiga puluh) hari sejak invoice diterima oleh PIHAK KEDUA, maka PIHAK PERTAMA akan melakukan pemutusan sementara (isolir) layanan tanpa pemberitahuan terlebih dahulu kepada PIHAK KEDUA;

Apabila PIHAK KEDUA melunasi biaya-biaya dalam pasal ini, maka PIHAK PERTAMA akan membuka pemutusan sementara (isolir) dalam waktu selambat-lambatnya 1 (satu) hari kerja;

Semua Biaya bank yang timbul dalam pembayaran tagihan merupakan tanggung jawab PIHAK KEDUA;

Seluruh pembayaran dianggap telah dilakukan PIHAK KEDUA setelah pembayaran diterima di rekening PIHAK PERTAMA, dengan detail sebagai berikut:

Bank Mandiri

Nomor Rekening	: 130-00-2010068-4

Nama Rekening	: PT Bina Informatika Solusindo

Bank Rakyat Indonesia (BRI)

Nomor Rekening	: 1317-01-000039-30-8

Nama Rekening	: PT Bina Informatika Solusindo

Bank Central Asia (BCA)

Nomor Rekening	: 008-3982-397

Nama Rekening	: PT Bina Informatika Solusindo

Bank Pembangunan Daerah Jawa Barat dan Banten (BJB)

Nomor Rekening	: 012-1989-247-001

Nama Rekening	: PT Bina Informatika Solusindo',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA wajib melakukan pembayaran atas Layanan sebagaimana dimaksud dalam Service Order Form;',
        ),
        1 => 
        array (
          'p' => 'Pembayaran dilakukan oleh PIHAK KEDUA selambat–lambatnya pada tanggal sesuai dengan invoice yang diterbitkan atau selambat-lambatnya 7 (tujuh) hari dari tanggal invoice diterbitkan;',
        ),
        2 => 
        array (
          'p' => 'Atas setiap keterlambatan pembayaran dari tanggal sebagaimana dimaksud dalam Pasal 5.2, maka PIHAK KEDUA dikenakan denda keterlambatan pembayaran sebesar 1 (satu) permil setiap hari keterlambatan;',
        ),
        3 => 
        array (
          'p' => 'Apabila PIHAK KEDUA terlambat melakukan pembayaran 30 (tiga puluh) hari sejak invoice diterima oleh PIHAK KEDUA, maka PIHAK PERTAMA akan melakukan pemutusan sementara (isolir) layanan tanpa pemberitahuan terlebih dahulu kepada PIHAK KEDUA;',
        ),
        4 => 
        array (
          'p' => 'Apabila PIHAK KEDUA melunasi biaya-biaya dalam pasal ini, maka PIHAK PERTAMA akan membuka pemutusan sementara (isolir) dalam waktu selambat-lambatnya 1 (satu) hari kerja;',
        ),
        5 => 
        array (
          'p' => 'Semua Biaya bank yang timbul dalam pembayaran tagihan merupakan tanggung jawab PIHAK KEDUA;',
        ),
        6 => 
        array (
          'p' => 'Seluruh pembayaran dianggap telah dilakukan PIHAK KEDUA setelah pembayaran diterima di rekening PIHAK PERTAMA, dengan detail sebagai berikut:',
        ),
        7 => 
        array (
          'p' => 'Bank Mandiri',
        ),
        8 => 
        array (
          'p' => 'Nomor Rekening	: 130-00-2010068-4',
        ),
        9 => 
        array (
          'p' => 'Nama Rekening	: PT Bina Informatika Solusindo',
        ),
        10 => 
        array (
          'p' => 'Bank Rakyat Indonesia (BRI)',
        ),
        11 => 
        array (
          'p' => 'Nomor Rekening	: 1317-01-000039-30-8',
        ),
        12 => 
        array (
          'p' => 'Nama Rekening	: PT Bina Informatika Solusindo',
        ),
        13 => 
        array (
          'p' => 'Bank Central Asia (BCA)',
        ),
        14 => 
        array (
          'p' => 'Nomor Rekening	: 008-3982-397',
        ),
        15 => 
        array (
          'p' => 'Nama Rekening	: PT Bina Informatika Solusindo',
        ),
        16 => 
        array (
          'p' => 'Bank Pembangunan Daerah Jawa Barat dan Banten (BJB)',
        ),
        17 => 
        array (
          'p' => 'Nomor Rekening	: 012-1989-247-001',
        ),
        18 => 
        array (
          'p' => 'Nama Rekening	: PT Bina Informatika Solusindo',
        ),
      ),
    ),
    5 => 
    array (
      'judul' => 'Hak dan Kewajiban',
      'text' => 'PIHAK KEDUA wajib menyediakan perangkat yang dibutuhkan, sehingga fasilitas dan pelayanan PIHAK PERTAMA dapat diaktivasikan sesuai jadwal yang telah disepakati bersama;

PIHAK KEDUA tidak diperkenankan memberi kesempatan kepada pihak ketiga untuk memanfaatkan fasilitas dan pelayanan PIHAK PERTAMA tanpa izin tertulis dari PIHAK PERTAMA;

PIHAK KEDUA tidak diperkenankan mengadakan perubahan terhadap spesifikasi teknis, konfigurasi, dan fasilitas layanan PIHAK PERTAMA, termasuk menghubungkannya ke dalam jaringan PIHAK PERTAMA dengan cara apapun, kecuali atas izin tertulis dari PIHAK PERTAMA;

PIHAK KEDUA tidak diperkenankan untuk menghubungkan jaringan dan/atau fasilitas PIHAK PERTAMA dengan jaringan telekomunikasi umum (PSTN) termasuk namun tidak terbatas kepada jaringan telepon, teleks, atau komunikasi data;

PIHAK KEDUA akan memberikan izin wilayah kepada PIHAK PERTAMA  untuk memasuki fasilitas dan/atau lokasi milik PIHAK KEDUA sehubungan dengan keperluan pemeliharaan dan perbaikan;

PIHAK PERTAMA bertanggung jawab terhadap pemeliharaan dan perbaikan atas kerusakan atau gangguan pada saluran dan fasilitas milik PIHAK PERTAMA. Apabila kerusakan atau gangguan tersebut disebabkan oleh kesalahan, kesengajaan, atau kelalaian PIHAK KEDUA, maka PIHAK PERTAMA berhak memungut biaya perbaikan;

PIHAK KEDUA berhak memperoleh restitusi atas kerusakan atau gangguan yang terbukti bukan disebabkan oleh PIHAK KEDUA. Kompensasi akan diberikan sesuai ketentuan yang berlaku (Jaminan Pelayanan-SLA) dan tidak berlaku untuk kerusakan atau gangguan yang disebabkan oleh perangkat milik PIHAK KEDUA atau Force Majeure;

PIHAK PERTAMA tidak bertanggung jawab atas kebenaran, kerahasiaan dan atau kualitas informasi yang disalurkan melalui layanan PIHAK PERTAMA;

PIHAK PERTAMA tidak bertanggung jawab atas kerugian tidak langsung, kerugian konsekuensial, kehilangan keuntungan, kehilangan data, kehilangan peluang usaha, atau tuntutan pihak ketiga yang timbul akibat penggunaan atau ketidakmampuan penggunaan layanan oleh PIHAK KEDUA, kecuali apabila kerugian tersebut secara langsung disebabkan oleh kesalahan atau kelalaian berat PIHAK PERTAMA.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA wajib menyediakan perangkat yang dibutuhkan, sehingga fasilitas dan pelayanan PIHAK PERTAMA dapat diaktivasikan sesuai jadwal yang telah disepakati bersama;',
        ),
        1 => 
        array (
          'p' => 'PIHAK KEDUA tidak diperkenankan memberi kesempatan kepada pihak ketiga untuk memanfaatkan fasilitas dan pelayanan PIHAK PERTAMA tanpa izin tertulis dari PIHAK PERTAMA;',
        ),
        2 => 
        array (
          'p' => 'PIHAK KEDUA tidak diperkenankan mengadakan perubahan terhadap spesifikasi teknis, konfigurasi, dan fasilitas layanan PIHAK PERTAMA, termasuk menghubungkannya ke dalam jaringan PIHAK PERTAMA dengan cara apapun, kecuali atas izin tertulis dari PIHAK PERTAMA;',
        ),
        3 => 
        array (
          'p' => 'PIHAK KEDUA tidak diperkenankan untuk menghubungkan jaringan dan/atau fasilitas PIHAK PERTAMA dengan jaringan telekomunikasi umum (PSTN) termasuk namun tidak terbatas kepada jaringan telepon, teleks, atau komunikasi data;',
        ),
        4 => 
        array (
          'p' => 'PIHAK KEDUA akan memberikan izin wilayah kepada PIHAK PERTAMA  untuk memasuki fasilitas dan/atau lokasi milik PIHAK KEDUA sehubungan dengan keperluan pemeliharaan dan perbaikan;',
        ),
        5 => 
        array (
          'p' => 'PIHAK PERTAMA bertanggung jawab terhadap pemeliharaan dan perbaikan atas kerusakan atau gangguan pada saluran dan fasilitas milik PIHAK PERTAMA. Apabila kerusakan atau gangguan tersebut disebabkan oleh kesalahan, kesengajaan, atau kelalaian PIHAK KEDUA, maka PIHAK PERTAMA berhak memungut biaya perbaikan;',
        ),
        6 => 
        array (
          'p' => 'PIHAK KEDUA berhak memperoleh restitusi atas kerusakan atau gangguan yang terbukti bukan disebabkan oleh PIHAK KEDUA. Kompensasi akan diberikan sesuai ketentuan yang berlaku (Jaminan Pelayanan-SLA) dan tidak berlaku untuk kerusakan atau gangguan yang disebabkan oleh perangkat milik PIHAK KEDUA atau Force Majeure;',
        ),
        7 => 
        array (
          'p' => 'PIHAK PERTAMA tidak bertanggung jawab atas kebenaran, kerahasiaan dan atau kualitas informasi yang disalurkan melalui layanan PIHAK PERTAMA;',
        ),
        8 => 
        array (
          'p' => 'PIHAK PERTAMA tidak bertanggung jawab atas kerugian tidak langsung, kerugian konsekuensial, kehilangan keuntungan, kehilangan data, kehilangan peluang usaha, atau tuntutan pihak ketiga yang timbul akibat penggunaan atau ketidakmampuan penggunaan layanan oleh PIHAK KEDUA, kecuali apabila kerugian tersebut secara langsung disebabkan oleh kesalahan atau kelalaian berat PIHAK PERTAMA.',
        ),
      ),
    ),
    6 => 
    array (
      'judul' => 'Pembatalan',
      'text' => 'Jika PIHAK KEDUA membatalkan layanan yang telah disepakati dalam Service Order Form sebelum dan atau sesudah aktivasi sebagaimana dimaksud dalam Service Order Form atau Berita Acara, maka PIHAK KEDUA diwajibkan melakukan pelunasan atas Biaya Instalasi',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Jika PIHAK KEDUA membatalkan layanan yang telah disepakati dalam Service Order Form sebelum dan atau sesudah aktivasi sebagaimana dimaksud dalam Service Order Form atau Berita Acara, maka PIHAK KEDUA diwajibkan melakukan pelunasan atas Biaya Instalasi',
        ),
      ),
    ),
    7 => 
    array (
      'judul' => 'Perpindahan dan Pengalihan',
      'text' => 'PIHAK KEDUA dapat meminta perpindahan lokasi fasilitas PIHAK PERTAMA serta penambahan kapasitas sepanjang teknis memungkinkan. Segala biaya yang timbul akibat perpindahan lokasi serta penambahan kapasitas tersebut akan dibebankan kepada PIHAK KEDUA; dan

Pemindahan fasilitas PIHAK PERTAMA yang telah terpasang ke lokasi lainnya akan diperlakukan sebagai sambungan baru. Biaya berlangganan akan disesuaikan dengan penambahan kapasitas terpasang.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA dapat meminta perpindahan lokasi fasilitas PIHAK PERTAMA serta penambahan kapasitas sepanjang teknis memungkinkan. Segala biaya yang timbul akibat perpindahan lokasi serta penambahan kapasitas tersebut akan dibebankan kepada PIHAK KEDUA; dan',
        ),
        1 => 
        array (
          'p' => 'Pemindahan fasilitas PIHAK PERTAMA yang telah terpasang ke lokasi lainnya akan diperlakukan sebagai sambungan baru. Biaya berlangganan akan disesuaikan dengan penambahan kapasitas terpasang.',
        ),
      ),
    ),
    8 => 
    array (
      'judul' => 'Pembatalan Perjanjian dengan Alasan',
      'text' => 'PIHAK PERTAMA tidak dapat dianggap melakukan wanprestasi dan dibebaskan dari segala tanggung jawab atas kegagalan penyediaan Jasa yang disebabkan oleh tindakan, kelalaian, kesalahan, gangguan sistem, kegagalan perangkat keras (hardware), perangkat lunak (software), jaringan internal, sumber daya manusia, maupun sebab lainnya yang berasal dari PIHAK KEDUA atau pihak yang berada di bawah kendali PIHAK KEDUA;

Apabila terjadi pelanggaran kewajiban dari PIHAK KEDUA yang mengakibatkan pembatalan perjanjian ini, maka PIHAK KEDUA wajib membayar penggunaan internet sampai dengan bulan terakhir pembatalan kontrak;

Apabila salah satu pihak mengalami kegagalan keuangan atau berhenti beroperasi maka hal ini dapat menjadi penyebab pembatalan, dimana salah satu pihak atau lainnya dapat membatalkan perjanjian ini dengan melakukan pemberitahuan secara tertulis. Namun pihak yang mengalami keadaan insolven, kegagalan keuangan atau berhenti beroperasi tersebut tetap harus melaksanakan segala kewajiban hingga tanggal pemutusan kontrak berdasarkan peraturan perundang-undangan yang berlaku, termasuk akan tetapi tidak terbatas pada, Undang-undang Nomor 37 Tahun 2004 tentang Kepailitan dan Penundaan Kewajiban Pembayaran Utang;

Apabila PIHAK PERTAMA gagal untuk menyediakan Jasa atau gagal mencapai SLA sebagaimana diuraikan pada Lampiran B, maka akan berlaku ketentuan denda sebagai berikut:

Atas setiap akumulasi selama periode 30 hari kegagalan penyediaan Jasa, PIHAK PERTAMA akan memberikan potongan biaya Jasa secara prorata atas kegagalan penyediaan Jasa sesuai dengan skema restitusi yang dijelaskan dalam Lampiran B; dan

Dalam hal pihak PIHAK PERTAMA gagal memenuhi minimum SLA sebesar 99.5% dalam jangka waktu sebulan berdasarkan pada laporan dan pembuktian, maka PIHAK KEDUA wajib memberikan teguran sebanyak 3 (tiga) kali secara berturut-turut dan jika tidak ada penyelesaian dari PIHAK PERTAMA, maka PIHAK KEDUA berhak memutuskan kontrak dan wajib membayar seluruh kewajiban sampai tanggal pemutusan kontrak.

Apabila setelah 3 (tiga) surat teguran berturut-turut PIHAK PERTAMA tetap gagal memenuhi SLA sebagaimana diatur dalam Lampiran B, PIHAK KEDUA berhak mengakhiri Perjanjian tanpa dikenakan penalti terminasi dini. Namun PIHAK KEDUA tetap wajib melunasi seluruh tagihan yang telah jatuh tempo sampai dengan tanggal efektif pengakhiran layanan.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK PERTAMA tidak dapat dianggap melakukan wanprestasi dan dibebaskan dari segala tanggung jawab atas kegagalan penyediaan Jasa yang disebabkan oleh tindakan, kelalaian, kesalahan, gangguan sistem, kegagalan perangkat keras (hardware), perangkat lunak (software), jaringan internal, sumber daya manusia, maupun sebab lainnya yang berasal dari PIHAK KEDUA atau pihak yang berada di bawah kendali PIHAK KEDUA;',
        ),
        1 => 
        array (
          'p' => 'Apabila terjadi pelanggaran kewajiban dari PIHAK KEDUA yang mengakibatkan pembatalan perjanjian ini, maka PIHAK KEDUA wajib membayar penggunaan internet sampai dengan bulan terakhir pembatalan kontrak;',
        ),
        2 => 
        array (
          'p' => 'Apabila salah satu pihak mengalami kegagalan keuangan atau berhenti beroperasi maka hal ini dapat menjadi penyebab pembatalan, dimana salah satu pihak atau lainnya dapat membatalkan perjanjian ini dengan melakukan pemberitahuan secara tertulis. Namun pihak yang mengalami keadaan insolven, kegagalan keuangan atau berhenti beroperasi tersebut tetap harus melaksanakan segala kewajiban hingga tanggal pemutusan kontrak berdasarkan peraturan perundang-undangan yang berlaku, termasuk akan tetapi tidak terbatas pada, Undang-undang Nomor 37 Tahun 2004 tentang Kepailitan dan Penundaan Kewajiban Pembayaran Utang;',
        ),
        3 => 
        array (
          'p' => 'Apabila PIHAK PERTAMA gagal untuk menyediakan Jasa atau gagal mencapai SLA sebagaimana diuraikan pada Lampiran B, maka akan berlaku ketentuan denda sebagai berikut:',
        ),
        4 => 
        array (
          'p' => 'Atas setiap akumulasi selama periode 30 hari kegagalan penyediaan Jasa, PIHAK PERTAMA akan memberikan potongan biaya Jasa secara prorata atas kegagalan penyediaan Jasa sesuai dengan skema restitusi yang dijelaskan dalam Lampiran B; dan',
        ),
        5 => 
        array (
          'p' => 'Dalam hal pihak PIHAK PERTAMA gagal memenuhi minimum SLA sebesar 99.5% dalam jangka waktu sebulan berdasarkan pada laporan dan pembuktian, maka PIHAK KEDUA wajib memberikan teguran sebanyak 3 (tiga) kali secara berturut-turut dan jika tidak ada penyelesaian dari PIHAK PERTAMA, maka PIHAK KEDUA berhak memutuskan kontrak dan wajib membayar seluruh kewajiban sampai tanggal pemutusan kontrak.',
        ),
        6 => 
        array (
          'p' => 'Apabila setelah 3 (tiga) surat teguran berturut-turut PIHAK PERTAMA tetap gagal memenuhi SLA sebagaimana diatur dalam Lampiran B, PIHAK KEDUA berhak mengakhiri Perjanjian tanpa dikenakan penalti terminasi dini. Namun PIHAK KEDUA tetap wajib melunasi seluruh tagihan yang telah jatuh tempo sampai dengan tanggal efektif pengakhiran layanan.',
        ),
      ),
    ),
    9 => 
    array (
      'judul' => 'Pembatalan Perjanjian Tanpa Alasan',
      'text' => 'Para Pihak dapat membatalkan Perjanjian tanpa alasan dengan pemberitahuan tertulis dimuka dengan ketentuan sebagai berikut:

Jika PIHAK PERTAMA membatalkan Perjanjian, maka PIHAK PERTAMA akan memberitahukan 30 hari dimuka; dan

Jika PIHAK KEDUA membatalkan Perjanjian, maka PIHAK KEDUA akan memberitahukan 30 hari dimuka.

Jika PIHAK KEDUA membatalkan Perjanjian tanpa alasan, PIHAK KEDUA wajib membayar PIHAK PERTAMA atas seluruh biaya bulan berjalan di bulan terjadinya pembatalan hingga tanggal jatuh tempo perjanjian;

Dalam hal PIHAK PERTAMA mengakhiri Perjanjian tanpa alasan yang sah, PIHAK PERTAMA wajib memberikan pemberitahuan tertulis sekurang-kurangnya 30 (tiga puluh) hari kalender sebelumnya dan tetap memberikan layanan sampai dengan tanggal efektif pengakhiran. Kewajiban PIHAK PERTAMA terbatas pada pengembalian biaya layanan yang telah dibayar di muka untuk periode yang belum digunakan.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Para Pihak dapat membatalkan Perjanjian tanpa alasan dengan pemberitahuan tertulis dimuka dengan ketentuan sebagai berikut:',
        ),
        1 => 
        array (
          'p' => 'Jika PIHAK PERTAMA membatalkan Perjanjian, maka PIHAK PERTAMA akan memberitahukan 30 hari dimuka; dan',
        ),
        2 => 
        array (
          'p' => 'Jika PIHAK KEDUA membatalkan Perjanjian, maka PIHAK KEDUA akan memberitahukan 30 hari dimuka.',
        ),
        3 => 
        array (
          'p' => 'Jika PIHAK KEDUA membatalkan Perjanjian tanpa alasan, PIHAK KEDUA wajib membayar PIHAK PERTAMA atas seluruh biaya bulan berjalan di bulan terjadinya pembatalan hingga tanggal jatuh tempo perjanjian;',
        ),
        4 => 
        array (
          'p' => 'Dalam hal PIHAK PERTAMA mengakhiri Perjanjian tanpa alasan yang sah, PIHAK PERTAMA wajib memberikan pemberitahuan tertulis sekurang-kurangnya 30 (tiga puluh) hari kalender sebelumnya dan tetap memberikan layanan sampai dengan tanggal efektif pengakhiran. Kewajiban PIHAK PERTAMA terbatas pada pengembalian biaya layanan yang telah dibayar di muka untuk periode yang belum digunakan.',
        ),
      ),
    ),
    10 => 
    array (
      'judul' => 'Ketentuan Perubahan',
      'text' => 'Selama masa berlakunya Perjanjian, salah satu pihak dapat mengajukan usulan perubahan Perjanjian dengan mengajukan usulan secara tertulis kepada pihak lainnya;

Dalam jangka waktu 30 hari setelah menerima pemberitahuan tertulis mengenai usulan perubahan dari PIHAK KEDUA, PIHAK PERTAMA akan memberitahu PIHAK KEDUA apakah perubahan dapat dilaksanakan atau tidak. Apabila perubahan tersebut dapat dilaksanakan, maka PIHAK PERTAMA berhak mengajukan perubahan atas biaya Jasa dan ketentuan lainnya dari Perjanjian ini;

PIHAK KEDUA dapat mengajukan usulan upgrade dan downgrade layanan selama masa berlakunya perjanjian melalui pemberitahuan tertulis 30 hari sebelumnya;

Permohonan downgrade layanan yang diajukan sebelum berakhirnya Masa Berlangganan Minimum sebagaimana ditentukan dalam Service Order Form atau Lampiran Perjanjian ini akan dianggap sebagai pengakhiran sebagian layanan dan dikenakan penalti sebesar (100% x biaya layanan perbulan x Bulan yang belum terpenuhi);

Selama masa berlakunya Perjanjian ini Pihak Kedua tidak bisa mengajukan perubahan biaya layanan yang berjalan sampai dengan masa kontrak Perjanjian ini berakhir. Terkecuali adanya permohonan upgrade layanan;',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Selama masa berlakunya Perjanjian, salah satu pihak dapat mengajukan usulan perubahan Perjanjian dengan mengajukan usulan secara tertulis kepada pihak lainnya;',
        ),
        1 => 
        array (
          'p' => 'Dalam jangka waktu 30 hari setelah menerima pemberitahuan tertulis mengenai usulan perubahan dari PIHAK KEDUA, PIHAK PERTAMA akan memberitahu PIHAK KEDUA apakah perubahan dapat dilaksanakan atau tidak. Apabila perubahan tersebut dapat dilaksanakan, maka PIHAK PERTAMA berhak mengajukan perubahan atas biaya Jasa dan ketentuan lainnya dari Perjanjian ini;',
        ),
        2 => 
        array (
          'p' => 'PIHAK KEDUA dapat mengajukan usulan upgrade dan downgrade layanan selama masa berlakunya perjanjian melalui pemberitahuan tertulis 30 hari sebelumnya;',
        ),
        3 => 
        array (
          'p' => 'Permohonan downgrade layanan yang diajukan sebelum berakhirnya Masa Berlangganan Minimum sebagaimana ditentukan dalam Service Order Form atau Lampiran Perjanjian ini akan dianggap sebagai pengakhiran sebagian layanan dan dikenakan penalti sebesar (100% x biaya layanan perbulan x Bulan yang belum terpenuhi);',
        ),
        4 => 
        array (
          'p' => 'Selama masa berlakunya Perjanjian ini Pihak Kedua tidak bisa mengajukan perubahan biaya layanan yang berjalan sampai dengan masa kontrak Perjanjian ini berakhir. Terkecuali adanya permohonan upgrade layanan;',
        ),
      ),
    ),
    11 => 
    array (
      'judul' => 'Force Majeure',
      'text' => 'Keadaan Memaksa (Force Majeure) adalah setiap peristiwa di luar kendali dan kemampuan wajar PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, termasuk namun tidak terbatas pada bencana alam, kebakaran, perang, kerusuhan, wabah penyakit, pemogokan, gangguan jaringan berskala besar, kegagalan sistem di luar kendali PARA PIHAK, kebijakan Pemerintah, atau peristiwa lain yang sejenis.

PIHAK yang mengalami Keadaan Memaksa wajib memberitahukan secara tertulis kepada pihak lainnya paling lambat 14 (empat belas) Hari Kalender sejak diketahui atau seharusnya diketahui terjadinya Keadaan Memaksa, disertai penjelasan mengenai dampak terhadap pelaksanaan Perjanjian.

Selama Keadaan Memaksa berlangsung, kewajiban PARA PIHAK yang terdampak ditangguhkan sepanjang tidak dapat dilaksanakan akibat Keadaan Memaksa. Penangguhan tersebut tidak menghapus kewajiban yang telah timbul sebelum terjadinya Keadaan Memaksa.

PARA PIHAK wajib melakukan upaya yang wajar untuk mengurangi dampak Keadaan Memaksa dan melanjutkan pelaksanaan Perjanjian segera setelah keadaan tersebut berakhir.

Apabila Keadaan Memaksa berlangsung lebih dari 90 (sembilan puluh) Hari Kalender secara berturut-turut dan PARA PIHAK tidak mencapai kesepakatan mengenai kelanjutan Perjanjian, masing-masing pihak berhak mengakhiri Perjanjian dengan pemberitahuan tertulis tanpa dikenakan penalti, dengan tetap menyelesaikan seluruh hak dan kewajiban yang telah timbul sebelum tanggal efektif pengakhiran.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Keadaan Memaksa (Force Majeure) adalah setiap peristiwa di luar kendali dan kemampuan wajar PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, termasuk namun tidak terbatas pada bencana alam, kebakaran, perang, kerusuhan, wabah penyakit, pemogokan, gangguan jaringan berskala besar, kegagalan sistem di luar kendali PARA PIHAK, kebijakan Pemerintah, atau peristiwa lain yang sejenis.',
        ),
        1 => 
        array (
          'p' => 'PIHAK yang mengalami Keadaan Memaksa wajib memberitahukan secara tertulis kepada pihak lainnya paling lambat 14 (empat belas) Hari Kalender sejak diketahui atau seharusnya diketahui terjadinya Keadaan Memaksa, disertai penjelasan mengenai dampak terhadap pelaksanaan Perjanjian.',
        ),
        2 => 
        array (
          'p' => 'Selama Keadaan Memaksa berlangsung, kewajiban PARA PIHAK yang terdampak ditangguhkan sepanjang tidak dapat dilaksanakan akibat Keadaan Memaksa. Penangguhan tersebut tidak menghapus kewajiban yang telah timbul sebelum terjadinya Keadaan Memaksa.',
        ),
        3 => 
        array (
          'p' => 'PARA PIHAK wajib melakukan upaya yang wajar untuk mengurangi dampak Keadaan Memaksa dan melanjutkan pelaksanaan Perjanjian segera setelah keadaan tersebut berakhir.',
        ),
        4 => 
        array (
          'p' => 'Apabila Keadaan Memaksa berlangsung lebih dari 90 (sembilan puluh) Hari Kalender secara berturut-turut dan PARA PIHAK tidak mencapai kesepakatan mengenai kelanjutan Perjanjian, masing-masing pihak berhak mengakhiri Perjanjian dengan pemberitahuan tertulis tanpa dikenakan penalti, dengan tetap menyelesaikan seluruh hak dan kewajiban yang telah timbul sebelum tanggal efektif pengakhiran.',
        ),
      ),
    ),
    12 => 
    array (
      'judul' => 'Penyelesaian Sengketa',
      'text' => 'PARA PIHAK sepakat bahwa Perjanjian ini dibuat dan dilaksanakan berdasarkan prinsip itikad baik, saling menguntungkan, serta tunduk pada ketentuan peraturan perundang-undangan yang berlaku di Republik Indonesia;

Apabila timbul perselisihan, perbedaan penafsiran, atau sengketa yang berkaitan dengan pelaksanaan, pelanggaran, pengakhiran, maupun keabsahan Perjanjian ini, PARA PIHAK sepakat untuk terlebih dahulu menyelesaikannya secara musyawarah untuk mufakat dalam jangka waktu paling lama 30 (tiga puluh) hari kalender sejak salah satu pihak menyampaikan pemberitahuan tertulis mengenai adanya sengketa;

Dalam hal musyawarah sebagaimana dimaksud pada ayat (2) tidak mencapai kesepakatan dalam jangka waktu tersebut, PARA PIHAK sepakat untuk menyelesaikan sengketa melalui Pengadilan Negeri Bandung, tanpa mengurangi hak PIHAK PERTAMA untuk melakukan upaya penagihan, pemutusan layanan, atau tindakan hukum lainnya sesuai ketentuan Perjanjian ini;

Selama proses penyelesaian sengketa berlangsung, PARA PIHAK tetap berkewajiban melaksanakan bagian-bagian Perjanjian yang tidak dipersengketakan;

PARA PIHAK sepakat bahwa pengajuan keberatan, klaim, atau sengketa oleh PIHAK KEDUA tidak menghapus, menangguhkan, atau mengurangi kewajiban PIHAK KEDUA untuk melakukan pembayaran atas tagihan yang telah jatuh tempo berdasarkan Perjanjian ini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PARA PIHAK sepakat bahwa Perjanjian ini dibuat dan dilaksanakan berdasarkan prinsip itikad baik, saling menguntungkan, serta tunduk pada ketentuan peraturan perundang-undangan yang berlaku di Republik Indonesia;',
        ),
        1 => 
        array (
          'p' => 'Apabila timbul perselisihan, perbedaan penafsiran, atau sengketa yang berkaitan dengan pelaksanaan, pelanggaran, pengakhiran, maupun keabsahan Perjanjian ini, PARA PIHAK sepakat untuk terlebih dahulu menyelesaikannya secara musyawarah untuk mufakat dalam jangka waktu paling lama 30 (tiga puluh) hari kalender sejak salah satu pihak menyampaikan pemberitahuan tertulis mengenai adanya sengketa;',
        ),
        2 => 
        array (
          'p' => 'Dalam hal musyawarah sebagaimana dimaksud pada ayat (2) tidak mencapai kesepakatan dalam jangka waktu tersebut, PARA PIHAK sepakat untuk menyelesaikan sengketa melalui Pengadilan Negeri Bandung, tanpa mengurangi hak PIHAK PERTAMA untuk melakukan upaya penagihan, pemutusan layanan, atau tindakan hukum lainnya sesuai ketentuan Perjanjian ini;',
        ),
        3 => 
        array (
          'p' => 'Selama proses penyelesaian sengketa berlangsung, PARA PIHAK tetap berkewajiban melaksanakan bagian-bagian Perjanjian yang tidak dipersengketakan;',
        ),
        4 => 
        array (
          'p' => 'PARA PIHAK sepakat bahwa pengajuan keberatan, klaim, atau sengketa oleh PIHAK KEDUA tidak menghapus, menangguhkan, atau mengurangi kewajiban PIHAK KEDUA untuk melakukan pembayaran atas tagihan yang telah jatuh tempo berdasarkan Perjanjian ini.',
        ),
      ),
    ),
    13 => 
    array (
      'judul' => 'Lain-Lain',
      'text' => 'Setiap perubahan, penambahan, pengurangan, atau penyesuaian terhadap ketentuan dalam Perjanjian ini hanya sah dan mengikat apabila dibuat secara tertulis serta disepakati dan ditandatangani oleh PARA PIHAK dalam bentuk Addendum dan/atau Amandemen yang menjadi bagian yang tidak terpisahkan dari Perjanjian ini;

Seluruh Lampiran dalam Perjanjian ini merupakan satu kesatuan yang tidak terpisahkan dan mempunyai kekuatan hukum yang sama dengan Perjanjian ini;

PARA PIHAK sepakat dan setuju untuk mengesampikan berlakunya Pasal 1266 KUHPerdata, sehingga Pemutusan Perjanjian ini dapat dilakukan oleh PARA PIHAK tanpa terlebih dahulu menunggu Putusan Pengadilan

Para pihak menjamin bahwa penandatangan Perjanjian ini dan/atau Lampiran-Lampirannya adalah pihak yang sah dan berwenang secara hukum untuk mengikatkan diri dan/atau mewakili perusahaan masing-masing, baik berdasarkan anggaran dasar, keputusan organ perusahaan yang berwenang, maupun surat kuasa yang sah;

Perjanjian ini dibuat dan ditandatangani dalam rangkap 2 (dua) asli, masing-masing bermeterai cukup dan mempunyai kekuatan hukum yang sama. Perjanjian ini dibuat dengan itikad baik untuk dilaksanakan oleh PARA PIHAK. Dalam hal ditandatangani secara elektronik, PARA PIHAK sepakat bahwa dokumen elektronik memiliki kekuatan hukum yang sah sesuai peraturan perundang-undangan yang berlaku.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Setiap perubahan, penambahan, pengurangan, atau penyesuaian terhadap ketentuan dalam Perjanjian ini hanya sah dan mengikat apabila dibuat secara tertulis serta disepakati dan ditandatangani oleh PARA PIHAK dalam bentuk Addendum dan/atau Amandemen yang menjadi bagian yang tidak terpisahkan dari Perjanjian ini;',
        ),
        1 => 
        array (
          'p' => 'Seluruh Lampiran dalam Perjanjian ini merupakan satu kesatuan yang tidak terpisahkan dan mempunyai kekuatan hukum yang sama dengan Perjanjian ini;',
        ),
        2 => 
        array (
          'p' => 'PARA PIHAK sepakat dan setuju untuk mengesampikan berlakunya Pasal 1266 KUHPerdata, sehingga Pemutusan Perjanjian ini dapat dilakukan oleh PARA PIHAK tanpa terlebih dahulu menunggu Putusan Pengadilan',
        ),
        3 => 
        array (
          'p' => 'Para pihak menjamin bahwa penandatangan Perjanjian ini dan/atau Lampiran-Lampirannya adalah pihak yang sah dan berwenang secara hukum untuk mengikatkan diri dan/atau mewakili perusahaan masing-masing, baik berdasarkan anggaran dasar, keputusan organ perusahaan yang berwenang, maupun surat kuasa yang sah;',
        ),
        4 => 
        array (
          'p' => 'Perjanjian ini dibuat dan ditandatangani dalam rangkap 2 (dua) asli, masing-masing bermeterai cukup dan mempunyai kekuatan hukum yang sama. Perjanjian ini dibuat dengan itikad baik untuk dilaksanakan oleh PARA PIHAK. Dalam hal ditandatangani secara elektronik, PARA PIHAK sepakat bahwa dokumen elektronik memiliki kekuatan hukum yang sah sesuai peraturan perundang-undangan yang berlaku.',
        ),
      ),
    ),
  ),
  'tutup' => 'PIHAK PERTAMA

PT Bina Informatika Solusindo

PIHAK KEDUA

PT Dinar Wahana Gemilang

Ageng Bagja Priyadi, S.T.,M.Kom

Direktur

Wildan Arief Santika Budi

Direktur Utama',
  'tutupBlocks' => 
  array (
    0 => 
    array (
      'table' => 
      array (
        'rows' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'c' => 
              array (
                0 => 'PIHAK PERTAMA',
                1 => 'PT Bina Informatika Solusindo',
              ),
              's' => 1,
            ),
            1 => 
            array (
              'c' => 
              array (
                0 => 'PIHAK KEDUA',
                1 => 'PT Dinar Wahana Gemilang',
              ),
              's' => 1,
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'c' => 
              array (
                0 => 'Ageng Bagja Priyadi, S.T.,M.Kom',
                1 => 'Direktur',
              ),
              's' => 1,
            ),
            1 => 
            array (
              'c' => 
              array (
                0 => 'Wildan Arief Santika Budi',
                1 => 'Direktur Utama',
              ),
              's' => 1,
            ),
          ),
        ),
        'bordered' => false,
        'head' => true,
      ),
    ),
  ),
  'lampiran' => 
  array (
    0 => 
    array (
      'judul' => 'LAMPIRAN A',
      'text' => '',
      'blocks' => 
      array (
        0 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nomor Perjanjian Berlangganan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '335/FBC/J.MS/IV/2026',
                  ),
                  's' => 1,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Tanggal Awal Berlangganan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '1 April 2026',
                  ),
                  's' => 1,
                ),
              ),
              2 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Tanggal Akhir Berlangganan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '31 Maret 2027',
                  ),
                  's' => 1,
                ),
              ),
              3 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama dan Alamat Pelanggan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'PT Dinar Wahana Gemilang',
                    1 => 'Jl. Cetarip Barat (Cetarip raya) No. 15/200 Rt 05 Rw 10, Kopo, Kota Bandung',
                  ),
                  's' => 1,
                ),
              ),
              4 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'NPWP',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '95.536.317.1-422.000',
                  ),
                  's' => 1,
                ),
              ),
              5 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nomor Telepon Pelanggan /',
                    1 => 'Penanggungjawab',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '0889-9999-0707',
                  ),
                  's' => 1,
                ),
              ),
              6 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nomor Handphone Pelanggan /',
                    1 => 'Penanggungjawab',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '0811-2236-799',
                  ),
                  's' => 1,
                ),
              ),
              7 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama Penanggungjawab Administrasi/Keuangan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Wenni Kartina Pelita',
                  ),
                  's' => 1,
                ),
              ),
              8 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama Penanggungjawab Teknisi',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Firman Syahruman',
                  ),
                  's' => 1,
                ),
              ),
              9 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Jenis Layanan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Meta Content 3 (tiga) Gbps',
                  ),
                  's' => 1,
                ),
              ),
              10 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Layanan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp9,000,000,- (Perbulan)',
                    1 => 'Belum termasuk PPN',
                  ),
                  's' => 1,
                ),
              ),
              11 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Instalasi',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '-',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
      ),
    ),
    1 => 
    array (
      'judul' => 'LAMPIRAN B',
      'text' => 'SLA = 99.5%

Catatan :

PIHAK PERTAMA tidak bertanggung jawab atas ketersediaan dari infrastruktur LAN (Local Area Network) sebagai dari Perjanjian ini. Target Availability terkait dengan sambungan internet dari sisi pemancar PIHAK PERTAMA hingga sisi PIHAK KEDUA dan koneksi fisik dari Ethernet port perangkat PIHAK PERTAMA hingga ke PIHAK KEDUA tapi tidak termasuk Ethernet port disisi PC maupun server PIHAK KEDUA.

Downtime yang diperhitungkan tidak termasuk perawatan rutin.

FORMULA PERHITUNGAN

PIHAK PERTAMA memberikan jaminan Layanan yang tercantum dalam Service Order Form dengan rumusan sebagai berikut:

Apabila Layanan tidak sesuai dengan yang disepakati dalam Service Order Form, maka akan berlaku rumusan Restitusi sebagai berikut:',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'SLA = 99.5%',
        ),
        1 => 
        array (
          'p' => 'Catatan :',
        ),
        2 => 
        array (
          'p' => 'PIHAK PERTAMA tidak bertanggung jawab atas ketersediaan dari infrastruktur LAN (Local Area Network) sebagai dari Perjanjian ini. Target Availability terkait dengan sambungan internet dari sisi pemancar PIHAK PERTAMA hingga sisi PIHAK KEDUA dan koneksi fisik dari Ethernet port perangkat PIHAK PERTAMA hingga ke PIHAK KEDUA tapi tidak termasuk Ethernet port disisi PC maupun server PIHAK KEDUA.',
        ),
        3 => 
        array (
          'p' => 'Downtime yang diperhitungkan tidak termasuk perawatan rutin.',
        ),
        4 => 
        array (
          'p' => 'FORMULA PERHITUNGAN',
        ),
        5 => 
        array (
          'p' => 'PIHAK PERTAMA memberikan jaminan Layanan yang tercantum dalam Service Order Form dengan rumusan sebagai berikut:',
        ),
        6 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Service Level Guarantee(%): (usage minutes per month – down time) x 100',
                    1 => 'Total minutes per month',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
        7 => 
        array (
          'p' => 'Apabila Layanan tidak sesuai dengan yang disepakati dalam Service Order Form, maka akan berlaku rumusan Restitusi sebagai berikut:',
        ),
        8 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '[Agreed Service Level – Actual Service Level] x Monthly Cost.',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
      ),
    ),
  ),
),
            ],
            'kontrak-soho' => [
                'title' => 'Perjanjian Berlangganan Jasa SOHO',
                'header_data' => array (
  'kopInstansi' => '[Nama Perusahaan PIHAK PERTAMA]',
  'kopAlamat' => '[Alamat perusahaan PIHAK PERTAMA]',
  'kopKontrak' => 'PERJANJIAN BERLANGGANAN',
  'nomorSurat' => '[Nomor Perjanjian]',
  'perihalSurat' => 'Jasa SOHO',
  'sifatSurat' => 'Penting',
),
                'body_content' => array (
  'preamble' => 'PERJANJIAN BERLANGGANAN

JASA SOHO

PT BINA INFORMATIKA SOLUSI

DENGAN

PT TRIPUTRA TEXTILE INDUSTRY

Nomor: 152/FBT/J.S/IX/2025

PERJANJIAN BERLANGGANAN

JASA SOHO

Nomor: 152/FBT/J.S/IX/2025

Pada hari ini, Senin, tanggal 1 September 2025, bertempat di Cirebon, telah dibuat dan ditandatangani Perjanjian, oleh dan antara:

PT Bina Informatika Solusi, berkedudukan di Jalan Prakarsa Muda Nomor 258, Kel. Pekiringan, Kec. Kesambi, Kota Cirebon, Jawa Barat 45131. Berdasarkan Akta Berita Acara RUPS Tahunan Perseroan Terbatas “PT Bina Informatika Solusi”, Nomor 5, tanggal 10 Juli 2026, dibuat dihadapan Irni Yuniati, S.H., M.Kn., Notaris di Kota Cimahi. Dalam hal ini diwakili oleh Ageng Bagja Priyadi, S.T.,       M. Kom., selaku Direktur, sah bertindak untuk dan atas nama PT Bina Informatika Solusi, selanjutnya disebut sebagai “PIHAK PERTAMA”

PT Triputra Textile Industry, berkedudukan di Jalan Raya Laswi No. 8, Kec. Majalaya, Kab. Bandung. Dalam hal ini diwakili oleh Delly Yulia, dalam kedudukanya sebagai Manager Area Bandung, oleh karenanya bertindak atas nama PT Triputra Textile Industry, selanjutnya disebut “PIHAK KEDUA”

PIHAK KEDUA dan PIHAK PERTAMA secara bersama-sama selanjutnya disebut “PARA PIHAK”

PARA PIHAK dengan ini menerangkan telah sepakat untuk mengikatkan diri pada syarat-syarat dan ketentuan-ketentuan sebagai berikut:',
  'isi' => 
  array (
    0 => 
    array (
      'judul' => 'Definisi',
      'text' => '“Perjanjian” adalah Perjanjian ini berikut lampiran dan semua perubahan yang terkait dan merupakan bagian dari Perjanjian ini.

“Jasa” adalah layanan yang harus dipenuhi oleh PIHAK PERTAMA sebagaimana diuraikan pada Lampiran A.

“Biaya Jasa” adalah biaya yang harus dibayar oleh PIHAK KEDUA seperti diuraikan pada Lampiran A.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => '“Perjanjian” adalah Perjanjian ini berikut lampiran dan semua perubahan yang terkait dan merupakan bagian dari Perjanjian ini.',
        ),
        1 => 
        array (
          'p' => '“Jasa” adalah layanan yang harus dipenuhi oleh PIHAK PERTAMA sebagaimana diuraikan pada Lampiran A.',
        ),
        2 => 
        array (
          'p' => '“Biaya Jasa” adalah biaya yang harus dibayar oleh PIHAK KEDUA seperti diuraikan pada Lampiran A.',
        ),
      ),
    ),
    1 => 
    array (
      'judul' => 'Fasilitas PIHAK PERTAMA',
      'text' => 'PIHAK PERTAMA sepakat untuk menyediakan Jasa dan Fasilitas terkait (selanjutnya disebut “Jasa”) sebagaimana tercantum dalam Service Order Form yang dikeluarkan oleh PIHAK KEDUA yang menjadi bagian tak terpisahkan dari Perjanjian ini;

Layanan Jasa yang disediakan PIHAK PERTAMA berdasarkan Perjanjian ini dapat digunakan oleh PIHAK KEDUA selama 24 jam/hari (7 hari/minggu);

Penyediaan Fasilitas dan Jasa PIHAK PERTAMA akan dilakukan sesuai dengan konfigurasi teknis yang telah disepakati;

Terminal dan perangkat antarmuka milik PIHAK KEDUA yang akan dihubungkan dengan perangkat/saluran PIHAK PERTAMA harus mendapat persetujuan terlebih dahulu dari PIHAK PERTAMA;

Penyambungan pelayanan PIHAK PERTAMA akan dilaksanakan setelah PIHAK KEDUA mengeluarkan Service Order Form dan diterima oleh pihak PIHAK PERTAMA.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK PERTAMA sepakat untuk menyediakan Jasa dan Fasilitas terkait (selanjutnya disebut “Jasa”) sebagaimana tercantum dalam Service Order Form yang dikeluarkan oleh PIHAK KEDUA yang menjadi bagian tak terpisahkan dari Perjanjian ini;',
        ),
        1 => 
        array (
          'p' => 'Layanan Jasa yang disediakan PIHAK PERTAMA berdasarkan Perjanjian ini dapat digunakan oleh PIHAK KEDUA selama 24 jam/hari (7 hari/minggu);',
        ),
        2 => 
        array (
          'p' => 'Penyediaan Fasilitas dan Jasa PIHAK PERTAMA akan dilakukan sesuai dengan konfigurasi teknis yang telah disepakati;',
        ),
        3 => 
        array (
          'p' => 'Terminal dan perangkat antarmuka milik PIHAK KEDUA yang akan dihubungkan dengan perangkat/saluran PIHAK PERTAMA harus mendapat persetujuan terlebih dahulu dari PIHAK PERTAMA;',
        ),
        4 => 
        array (
          'p' => 'Penyambungan pelayanan PIHAK PERTAMA akan dilaksanakan setelah PIHAK KEDUA mengeluarkan Service Order Form dan diterima oleh pihak PIHAK PERTAMA.',
        ),
      ),
    ),
    2 => 
    array (
      'judul' => 'Aktivasi Layanan',
      'text' => 'Aktivasi Layanan akan dimulai setelah Fasilitas PIHAK PERTAMA siap dioperasikan dan dinyatakan dengan Berita Acara Aktivasi yang ditandatangani oleh PARA PIHAK.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Aktivasi Layanan akan dimulai setelah Fasilitas PIHAK PERTAMA siap dioperasikan dan dinyatakan dengan Berita Acara Aktivasi yang ditandatangani oleh PARA PIHAK.',
        ),
      ),
    ),
    3 => 
    array (
      'judul' => 'Jangka Waktu Berlangganan',
      'text' => 'Jangka Waktu Efektif Layanan sebagaimana dimaksud dalam Syarat dan Ketentuan Berlangganan ini adalah tanggal sebagaimana dimaksud dalam lampiran A Perjanjian Berlangganan Jasa ini dan atau Service Order Form.

Apabila PIHAK KEDUA  mengakhiri Layanan sebelum Jangka Waktu berakhir sebagaimana dimaksud dalam lampiran A dan atau Service Order Form, maka PIHAK KEDUA akan dikenakan denda sebagaimana berikut:

Apabila 30 (Tiga puluh) hari sebelum jangka waktu dalam pasal  4 ayat 1 ini berakhir PIHAK KEDUA tidak melakukan pemberitahuan pengakhiran Layanan, maka Syarat dan Ketentuan Berlangganan akan otomatis berlanjut selama 1 (Satu) tahun (“Jangka Waktu Perpanjangan”). Untuk menghindari keragu-raguan maka pemberitahuan pengakhiran Syarat dan Ketentuan Berlangganan dihitung 30 (tiga puluh) hari sejak diterimanya pemberitahuan pengakhiran Layanan.

PIHAK PERTAMA akan melakukan penghentian sementara Layanan apabila PIHAK KEDUA terlambat melakukan pembayaran 30 (tiga puluh) hari sejak invoice oleh PIHAK KEDUA.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Jangka Waktu Efektif Layanan sebagaimana dimaksud dalam Syarat dan Ketentuan Berlangganan ini adalah tanggal sebagaimana dimaksud dalam lampiran A Perjanjian Berlangganan Jasa ini dan atau Service Order Form.',
        ),
        1 => 
        array (
          'p' => 'Apabila PIHAK KEDUA  mengakhiri Layanan sebelum Jangka Waktu berakhir sebagaimana dimaksud dalam lampiran A dan atau Service Order Form, maka PIHAK KEDUA akan dikenakan denda sebagaimana berikut:',
        ),
        2 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '50% x Biaya Bulanan x Bulan yang belum terpenuhi',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
        3 => 
        array (
          'p' => 'Apabila 30 (Tiga puluh) hari sebelum jangka waktu dalam pasal  4 ayat 1 ini berakhir PIHAK KEDUA tidak melakukan pemberitahuan pengakhiran Layanan, maka Syarat dan Ketentuan Berlangganan akan otomatis berlanjut selama 1 (Satu) tahun (“Jangka Waktu Perpanjangan”). Untuk menghindari keragu-raguan maka pemberitahuan pengakhiran Syarat dan Ketentuan Berlangganan dihitung 30 (tiga puluh) hari sejak diterimanya pemberitahuan pengakhiran Layanan.',
        ),
        4 => 
        array (
          'p' => 'PIHAK PERTAMA akan melakukan penghentian sementara Layanan apabila PIHAK KEDUA terlambat melakukan pembayaran 30 (tiga puluh) hari sejak invoice oleh PIHAK KEDUA.',
        ),
      ),
    ),
    4 => 
    array (
      'judul' => 'Pembayaran',
      'text' => 'PIHAK KEDUA wajib melakukan pembayaran atas Layanan sebagaimana dimaksud dalam Service Order Form;

Pembayaran dilakukan oleh PIHAK KEDUA selambat–lambatnya pada tanggal sesuai dengan invoice yang diterbitkan atau selambat-lambatnya 7 (tujuh) hari dari tanggal invoice diterbitkan;

Atas setiap keterlambatan pembayaran dari tanggal sebagaimana dimaksud dalam Pasal 5.2, maka PIHAK KEDUA dikenakan denda keterlambatan pembayaran sebesar 1 (satu) permil setiap hari keterlambatan;

Apabila dalam 1 (satu) bulan PIHAK KEDUA belum memenuhi kewajibannya tersebut, maka PIHAK PERTAMA akan melakukan pemutusan sementara (isolir) layanan tanpa pemberitahuan terlebih dahulu kepada PIHAK KEDUA;

Apabila PIHAK KEDUA melunasi biaya-biaya dalam pasal ini, maka PIHAK PERTAMA akan membuka pemutusan sementara (isolir) dalam waktu selambat-lambatnya 1 (satu) hari kerja;

Semua Biaya bank yang timbul dalam pembayaran tagihan merupakan tanggung jawab PIHAK KEDUA;

Seluruh pembayaran dianggap telah dilakukan PIHAK KEDUA setelah pembayaran diterima di rekening PIHAK PERTAMA, dengan detail sebagai berikut:

Bank Mandiri

No. Rekening: 1340001209104

Atas Nama   : PT Bina Informatika Solusi

Bank Rakyat Indonesia (BRI)

No. Rekening: 010701003038305

Atas Nama   : PT Bina Informatika Solusi',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA wajib melakukan pembayaran atas Layanan sebagaimana dimaksud dalam Service Order Form;',
        ),
        1 => 
        array (
          'p' => 'Pembayaran dilakukan oleh PIHAK KEDUA selambat–lambatnya pada tanggal sesuai dengan invoice yang diterbitkan atau selambat-lambatnya 7 (tujuh) hari dari tanggal invoice diterbitkan;',
        ),
        2 => 
        array (
          'p' => 'Atas setiap keterlambatan pembayaran dari tanggal sebagaimana dimaksud dalam Pasal 5.2, maka PIHAK KEDUA dikenakan denda keterlambatan pembayaran sebesar 1 (satu) permil setiap hari keterlambatan;',
        ),
        3 => 
        array (
          'p' => 'Apabila dalam 1 (satu) bulan PIHAK KEDUA belum memenuhi kewajibannya tersebut, maka PIHAK PERTAMA akan melakukan pemutusan sementara (isolir) layanan tanpa pemberitahuan terlebih dahulu kepada PIHAK KEDUA;',
        ),
        4 => 
        array (
          'p' => 'Apabila PIHAK KEDUA melunasi biaya-biaya dalam pasal ini, maka PIHAK PERTAMA akan membuka pemutusan sementara (isolir) dalam waktu selambat-lambatnya 1 (satu) hari kerja;',
        ),
        5 => 
        array (
          'p' => 'Semua Biaya bank yang timbul dalam pembayaran tagihan merupakan tanggung jawab PIHAK KEDUA;',
        ),
        6 => 
        array (
          'p' => 'Seluruh pembayaran dianggap telah dilakukan PIHAK KEDUA setelah pembayaran diterima di rekening PIHAK PERTAMA, dengan detail sebagai berikut:',
        ),
        7 => 
        array (
          'p' => 'Bank Mandiri',
        ),
        8 => 
        array (
          'p' => 'No. Rekening: 1340001209104',
        ),
        9 => 
        array (
          'p' => 'Atas Nama   : PT Bina Informatika Solusi',
        ),
        10 => 
        array (
          'p' => 'Bank Rakyat Indonesia (BRI)',
        ),
        11 => 
        array (
          'p' => 'No. Rekening: 010701003038305',
        ),
        12 => 
        array (
          'p' => 'Atas Nama   : PT Bina Informatika Solusi',
        ),
      ),
    ),
    5 => 
    array (
      'judul' => 'Hak dan Kewajiban',
      'text' => 'PIHAK KEDUA wajib menyediakan perangkat yang dibutuhkan, sehingga fasilitas dan pelayanan PIHAK PERTAMA dapat diaktivasikan sesuai jadwal yang telah disepakati bersama;

PIHAK KEDUA tidak diperkenankan memberi kesempatan kepada pihak ketiga untuk memanfaatkan fasilitas dan pelayanan PIHAK PERTAMA tanpa izin tertulis dari PIHAK PERTAMA;

PIHAK KEDUA tidak diperkenankan mengadakan perubahan terhadap spesifikasi teknis, konfigurasi, dan fasilitas layanan PIHAK PERTAMA, termasuk menghubungkannya ke dalam jaringan PIHAK PERTAMA dengan cara apapun, kecuali atas izin tertulis dari PIHAK PERTAMA;

PIHAK KEDUA tidak diperkenankan untuk menghubungkan jaringan dan/atau fasilitas PIHAK PERTAMA dengan jaringan telekomunikasi umum (PSTN) termasuk namun tidak terbatas kepada jaringan telepon, teleks, atau komunikasi data;

PIHAK KEDUA akan memberikan izin wilayah kepada PIHAK PERTAMA  untuk memasuki fasilitas dan/atau lokasi milik PIHAK KEDUA sehubungan dengan keperluan pemeliharaan dan perbaikan;

PIHAK PERTAMA bertanggung jawab terhadap pemeliharaan dan perbaikan atas kerusakan atau gangguan pada saluran dan fasilitas milik PIHAK PERTAMA. Apabila kerusakan atau gangguan tersebut disebabkan oleh kesalahan, kesengajaan, atau kelalaian PIHAK KEDUA, maka PIHAK PERTAMA berhak memungut biaya perbaikan;

PIHAK PERTAMA tidak bertanggung jawab atas kebenaran, kerahasiaan dan atau kualitas informasi yang disalurkan melalui layanan PIHAK PERTAMA;

PIHAK PERTAMA tidak bertanggung jawab atas kerugian–kerugian PIHAK KEDUA atau pihak ketiga yang timbul berkaitan dengan penggunaan jasa PIHAK PERTAMA.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA wajib menyediakan perangkat yang dibutuhkan, sehingga fasilitas dan pelayanan PIHAK PERTAMA dapat diaktivasikan sesuai jadwal yang telah disepakati bersama;',
        ),
        1 => 
        array (
          'p' => 'PIHAK KEDUA tidak diperkenankan memberi kesempatan kepada pihak ketiga untuk memanfaatkan fasilitas dan pelayanan PIHAK PERTAMA tanpa izin tertulis dari PIHAK PERTAMA;',
        ),
        2 => 
        array (
          'p' => 'PIHAK KEDUA tidak diperkenankan mengadakan perubahan terhadap spesifikasi teknis, konfigurasi, dan fasilitas layanan PIHAK PERTAMA, termasuk menghubungkannya ke dalam jaringan PIHAK PERTAMA dengan cara apapun, kecuali atas izin tertulis dari PIHAK PERTAMA;',
        ),
        3 => 
        array (
          'p' => 'PIHAK KEDUA tidak diperkenankan untuk menghubungkan jaringan dan/atau fasilitas PIHAK PERTAMA dengan jaringan telekomunikasi umum (PSTN) termasuk namun tidak terbatas kepada jaringan telepon, teleks, atau komunikasi data;',
        ),
        4 => 
        array (
          'p' => 'PIHAK KEDUA akan memberikan izin wilayah kepada PIHAK PERTAMA  untuk memasuki fasilitas dan/atau lokasi milik PIHAK KEDUA sehubungan dengan keperluan pemeliharaan dan perbaikan;',
        ),
        5 => 
        array (
          'p' => 'PIHAK PERTAMA bertanggung jawab terhadap pemeliharaan dan perbaikan atas kerusakan atau gangguan pada saluran dan fasilitas milik PIHAK PERTAMA. Apabila kerusakan atau gangguan tersebut disebabkan oleh kesalahan, kesengajaan, atau kelalaian PIHAK KEDUA, maka PIHAK PERTAMA berhak memungut biaya perbaikan;',
        ),
        6 => 
        array (
          'p' => 'PIHAK PERTAMA tidak bertanggung jawab atas kebenaran, kerahasiaan dan atau kualitas informasi yang disalurkan melalui layanan PIHAK PERTAMA;',
        ),
        7 => 
        array (
          'p' => 'PIHAK PERTAMA tidak bertanggung jawab atas kerugian–kerugian PIHAK KEDUA atau pihak ketiga yang timbul berkaitan dengan penggunaan jasa PIHAK PERTAMA.',
        ),
      ),
    ),
    6 => 
    array (
      'judul' => 'Pembatalan',
      'text' => 'Jika PIHAK KEDUA mengakhiri layanan sebelum Jangka Waktu berakhir dan atau membatalkan layanan yang telah disepakati dalam Service order form sebelum aktivasi sebagaimana dimaksud dalam perjanjian atau berita acara atau Service Order Form, maka PIHAK KEDUA diwajibkan melakukan pelunasan atas biaya instalasi dan oleh karenanya dikenakan denda sebesar 50% x Biaya Bulanan x Bulan yang belum terpenuhi.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Jika PIHAK KEDUA mengakhiri layanan sebelum Jangka Waktu berakhir dan atau membatalkan layanan yang telah disepakati dalam Service order form sebelum aktivasi sebagaimana dimaksud dalam perjanjian atau berita acara atau Service Order Form, maka PIHAK KEDUA diwajibkan melakukan pelunasan atas biaya instalasi dan oleh karenanya dikenakan denda sebesar 50% x Biaya Bulanan x Bulan yang belum terpenuhi.',
        ),
      ),
    ),
    7 => 
    array (
      'judul' => 'Perpindahan dan Pengalihan',
      'text' => 'PIHAK KEDUA dapat meminta perpindahan lokasi fasilitas PIHAK PERTAMA serta penambahan kapasitas sepanjang teknis memungkinkan. Segala biaya yang timbul akibat perpindahan lokasi serta penambahan kapasitas tersebut akan dibebankan kepada PIHAK KEDUA; dan

Pemindahan fasilitas PIHAK PERTAMA yang telah terpasang ke lokasi lainnya akan diperlakukan sebagai sambungan baru. Biaya berlangganan akan disesuaikan dengan penambahan kapasitas terpasang.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA dapat meminta perpindahan lokasi fasilitas PIHAK PERTAMA serta penambahan kapasitas sepanjang teknis memungkinkan. Segala biaya yang timbul akibat perpindahan lokasi serta penambahan kapasitas tersebut akan dibebankan kepada PIHAK KEDUA; dan',
        ),
        1 => 
        array (
          'p' => 'Pemindahan fasilitas PIHAK PERTAMA yang telah terpasang ke lokasi lainnya akan diperlakukan sebagai sambungan baru. Biaya berlangganan akan disesuaikan dengan penambahan kapasitas terpasang.',
        ),
      ),
    ),
    8 => 
    array (
      'judul' => 'Pembatalan Perjanjian dengan Alasan',
      'text' => 'PIHAK PERTAMA dibebaskan dari penyebab pembatalan jika kegagalan disebabkan oleh sumber yang berasal dari PIHAK KEDUA seperti data, kegagalan Hardware, atau kegagalan internal PIHAK KEDUA;

Apabila terjadi pelanggaran kewajiban dari PIHAK KEDUA yang mengakibatkan pembatalan perjanjian ini, maka PIHAK KEDUA wajib membayar penggunaan internet sampai dengan bulan terakhir pembatalan kontrak;

Apabila salah satu pihak mengalami kegagalan keuangan atau berhenti beroperasi maka hal ini dapat menjadi penyebab pembatalan, dimana salah satu pihak atau lainnya dapat membatalkan perjanjian ini dengan melakukan pemberitahuan secara tertulis. Namun pihak yang mengalami keadaan insolven, kegagalan keuangan atau berhenti beroperasi tersebut tetap harus melaksanakan segala kewajiban hingga tanggal pemutusan kontrak berdasarkan peraturan perundang-undangan yang berlaku, termasuk akan tetapi tidak terbatas pada, Undang-undang Nomor 37 Tahun 2004 tentang Kepailitan dan Penundaan Kewajiban Pembayaran Utang;

PIHAK KEDUA akan mengeluarkan Surat Teguran terhadap pelayanan PIHAK PERTAMA yang tidak memuaskan mencakup kegagalan fatal penyediaan jasa yang telah merugikan PIHAK KEDUA. Surat Teguran  disampaikan maksimum tiga kali. Dan Jika setelah Teguran Ketiga  tidak ada improvement dari layanan PIHAK PERTAMA maka PIHAK KEDUA berhak melakukan pemutusan sepihak dan dibebaskan dari kewajiban pembayaran apapun sebagaimana tersebut dalam ayat 2 dan 3 pasal ini dan pasal 10 ayat 3 perjanjian ini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK PERTAMA dibebaskan dari penyebab pembatalan jika kegagalan disebabkan oleh sumber yang berasal dari PIHAK KEDUA seperti data, kegagalan Hardware, atau kegagalan internal PIHAK KEDUA;',
        ),
        1 => 
        array (
          'p' => 'Apabila terjadi pelanggaran kewajiban dari PIHAK KEDUA yang mengakibatkan pembatalan perjanjian ini, maka PIHAK KEDUA wajib membayar penggunaan internet sampai dengan bulan terakhir pembatalan kontrak;',
        ),
        2 => 
        array (
          'p' => 'Apabila salah satu pihak mengalami kegagalan keuangan atau berhenti beroperasi maka hal ini dapat menjadi penyebab pembatalan, dimana salah satu pihak atau lainnya dapat membatalkan perjanjian ini dengan melakukan pemberitahuan secara tertulis. Namun pihak yang mengalami keadaan insolven, kegagalan keuangan atau berhenti beroperasi tersebut tetap harus melaksanakan segala kewajiban hingga tanggal pemutusan kontrak berdasarkan peraturan perundang-undangan yang berlaku, termasuk akan tetapi tidak terbatas pada, Undang-undang Nomor 37 Tahun 2004 tentang Kepailitan dan Penundaan Kewajiban Pembayaran Utang;',
        ),
        3 => 
        array (
          'p' => 'PIHAK KEDUA akan mengeluarkan Surat Teguran terhadap pelayanan PIHAK PERTAMA yang tidak memuaskan mencakup kegagalan fatal penyediaan jasa yang telah merugikan PIHAK KEDUA. Surat Teguran  disampaikan maksimum tiga kali. Dan Jika setelah Teguran Ketiga  tidak ada improvement dari layanan PIHAK PERTAMA maka PIHAK KEDUA berhak melakukan pemutusan sepihak dan dibebaskan dari kewajiban pembayaran apapun sebagaimana tersebut dalam ayat 2 dan 3 pasal ini dan pasal 10 ayat 3 perjanjian ini.',
        ),
      ),
    ),
    9 => 
    array (
      'judul' => 'Pembatalan Perjanjian Tanpa Alasan',
      'text' => 'Kedua belah pihak dapat membatalkan Perjanjian tanpa alasan dengan pemberitahuan tertulis dimuka dengan ketentuan sebagai berikut:

Jika PIHAK PERTAMA membatalkan Perjanjian, maka PIHAK PERTAMA akan memberitahukan 30 hari dimuka; dan

Jika PIHAK KEDUA membatalkan Perjanjian, maka PIHAK KEDUA akan memberitahukan 30 hari dimuka.

Jika PIHAK KEDUA membatalkan Perjanjian tanpa alasan, PIHAK KEDUA wajib membayar PIHAK PERTAMA atas seluruh biaya bulan berjalan di bulan terjadinya pembatalan hingga tanggal jatuh tempo perjanjian;

Jika PIHAK PERTAMA membatalkan Perjanjian tanpa alasan maka PIHAK PERTAMA akan menyediakan Jasa secara gratis selama masa transisi sampai dengan PIHAK KEDUA menunjuk internet provider yang baru dan PIHAK PERTAMA mengembalikan biaya awal yang telah dibayarkan oleh PIHAK KEDUA di pemasangan awal.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Kedua belah pihak dapat membatalkan Perjanjian tanpa alasan dengan pemberitahuan tertulis dimuka dengan ketentuan sebagai berikut:',
        ),
        1 => 
        array (
          'p' => 'Jika PIHAK PERTAMA membatalkan Perjanjian, maka PIHAK PERTAMA akan memberitahukan 30 hari dimuka; dan',
        ),
        2 => 
        array (
          'p' => 'Jika PIHAK KEDUA membatalkan Perjanjian, maka PIHAK KEDUA akan memberitahukan 30 hari dimuka.',
        ),
        3 => 
        array (
          'p' => 'Jika PIHAK KEDUA membatalkan Perjanjian tanpa alasan, PIHAK KEDUA wajib membayar PIHAK PERTAMA atas seluruh biaya bulan berjalan di bulan terjadinya pembatalan hingga tanggal jatuh tempo perjanjian;',
        ),
        4 => 
        array (
          'p' => 'Jika PIHAK PERTAMA membatalkan Perjanjian tanpa alasan maka PIHAK PERTAMA akan menyediakan Jasa secara gratis selama masa transisi sampai dengan PIHAK KEDUA menunjuk internet provider yang baru dan PIHAK PERTAMA mengembalikan biaya awal yang telah dibayarkan oleh PIHAK KEDUA di pemasangan awal.',
        ),
      ),
    ),
    10 => 
    array (
      'judul' => 'Ketentuan Perubahan',
      'text' => 'Selama masa berlakunya Perjanjian, salah satu pihak dapat mengajukan usulan perubahan Perjanjian dengan mengajukan usulan secara tertulis kepada pihak lainnya;

Dalam jangka waktu 30 hari setelah menerima pemberitahuan tertulis mengenai usulan perubahan dari PIHAK KEDUA, PIHAK PERTAMA akan memberitahu PIHAK KEDUA apakah perubahan dapat dilaksanakan atau tidak. Apabila perubahan tersebut dapat dilaksanakan, maka PIHAK PERTAMA berhak mengajukan perubahan atas biaya Jasa dan ketentuan lainnya dari Perjanjian ini;

PIHAK KEDUA dapat mengajukan usulan upgrade dan downgrade layanan selama masa berlakunya perjanjian melalui pemberitahuan tertulis 30 hari sebelumnya;

Penurunan kapasitas / bandwidth / downgrade apabila ada kapasitas bandwidth / downgrade layanan sebelum berakhirnya masa berlangganan minimal 1 tahun akan dikenakan penalti dengan ketentuan sebesar 50% X (biaya perbulan kecepatan lama-biaya perbulan kecepatan baru) X sisa masa berlangganan;

Selama masa berlakunya Perjanjian ini Pihak Kedua tidak bisa mengajukan perubahan biaya layanan yang berjalan sampai dengan masa kontrak Perjanjian ini berakhir. Terkecuali adanya permohonan upgrade layanan;

Apabila ada hal-hal  yang belum diatur dalam Kontrak ini. Maka hal-hal tersebut akan diatur dan ditetapkan kemudian secara tertulis tertuang dalam Amandemen dan atau Addendum dengan tetap memperhatikan ketentuan-ketentuan dan peraturan intern PIHAK PERTAMA dan hukum yang berlaku di Indonesia.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Selama masa berlakunya Perjanjian, salah satu pihak dapat mengajukan usulan perubahan Perjanjian dengan mengajukan usulan secara tertulis kepada pihak lainnya;',
        ),
        1 => 
        array (
          'p' => 'Dalam jangka waktu 30 hari setelah menerima pemberitahuan tertulis mengenai usulan perubahan dari PIHAK KEDUA, PIHAK PERTAMA akan memberitahu PIHAK KEDUA apakah perubahan dapat dilaksanakan atau tidak. Apabila perubahan tersebut dapat dilaksanakan, maka PIHAK PERTAMA berhak mengajukan perubahan atas biaya Jasa dan ketentuan lainnya dari Perjanjian ini;',
        ),
        2 => 
        array (
          'p' => 'PIHAK KEDUA dapat mengajukan usulan upgrade dan downgrade layanan selama masa berlakunya perjanjian melalui pemberitahuan tertulis 30 hari sebelumnya;',
        ),
        3 => 
        array (
          'p' => 'Penurunan kapasitas / bandwidth / downgrade apabila ada kapasitas bandwidth / downgrade layanan sebelum berakhirnya masa berlangganan minimal 1 tahun akan dikenakan penalti dengan ketentuan sebesar 50% X (biaya perbulan kecepatan lama-biaya perbulan kecepatan baru) X sisa masa berlangganan;',
        ),
        4 => 
        array (
          'p' => 'Selama masa berlakunya Perjanjian ini Pihak Kedua tidak bisa mengajukan perubahan biaya layanan yang berjalan sampai dengan masa kontrak Perjanjian ini berakhir. Terkecuali adanya permohonan upgrade layanan;',
        ),
        5 => 
        array (
          'p' => 'Apabila ada hal-hal  yang belum diatur dalam Kontrak ini. Maka hal-hal tersebut akan diatur dan ditetapkan kemudian secara tertulis tertuang dalam Amandemen dan atau Addendum dengan tetap memperhatikan ketentuan-ketentuan dan peraturan intern PIHAK PERTAMA dan hukum yang berlaku di Indonesia.',
        ),
      ),
    ),
    11 => 
    array (
      'judul' => 'Force Majeure',
      'text' => 'Yang dimaksud dengan Force Majeure dalam kontrak ini adalah keadaan-keadaan diluar kekuasaan salah satu pihak atau PARA PIHAK yang mengakibatkan pihak dimaksud tidak dapat melaksanakan kontrak ini, yaitu:

Gempa bumi besar, angin ribut (topan), kebakaran besar, banjir  besar, tanah longsor, petir, wabah penyakit; dan

Pemogokan umum, huru-hara, pemberontakan, perang, dan keadaan-keadaan lain yang oleh pejabat berwenang dinyatakan sebagai Force Majeure.

Dalam hal terjadi Force Majeure dimaksud pada ayat 1 pasal ini, maka pihak yang mengalami Force Majeure berkewajiban memberitahukan secara tertulis kepada pihak lainnya dalam waktu 14 (empat belas) hari kalender sejak saat mulainya, begitu juga saat berakhirnya dan diterangkan secara resmi oleh pejabat pemerintah yang berwenang;

Kelalaian atau keterlambatan dalam memenuhi kewajiban pemberitahuan dimaksud ayat 2 pasal ini, mengakibatkan tidak diakuinya peristiwa dimaksud ayat 1 pasal ini sebagai Force Majeure;

Semua kerugian yang timbul atau diderita salah satu pihak karena  terjadi Force Majeure bukan merupakan tanggung jawab pihak lain;

Force Majeure dimaksud ayat 1 pasal ini tidak dapat dijadikan alasan oleh salah satu pihak untuk menunda kewajiban pembayaran kepada pihak lainnya yang telah jatuh tempo sebelum terjadinya Force Majeure.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Yang dimaksud dengan Force Majeure dalam kontrak ini adalah keadaan-keadaan diluar kekuasaan salah satu pihak atau PARA PIHAK yang mengakibatkan pihak dimaksud tidak dapat melaksanakan kontrak ini, yaitu:',
        ),
        1 => 
        array (
          'p' => 'Gempa bumi besar, angin ribut (topan), kebakaran besar, banjir  besar, tanah longsor, petir, wabah penyakit; dan',
        ),
        2 => 
        array (
          'p' => 'Pemogokan umum, huru-hara, pemberontakan, perang, dan keadaan-keadaan lain yang oleh pejabat berwenang dinyatakan sebagai Force Majeure.',
        ),
        3 => 
        array (
          'p' => 'Dalam hal terjadi Force Majeure dimaksud pada ayat 1 pasal ini, maka pihak yang mengalami Force Majeure berkewajiban memberitahukan secara tertulis kepada pihak lainnya dalam waktu 14 (empat belas) hari kalender sejak saat mulainya, begitu juga saat berakhirnya dan diterangkan secara resmi oleh pejabat pemerintah yang berwenang;',
        ),
        4 => 
        array (
          'p' => 'Kelalaian atau keterlambatan dalam memenuhi kewajiban pemberitahuan dimaksud ayat 2 pasal ini, mengakibatkan tidak diakuinya peristiwa dimaksud ayat 1 pasal ini sebagai Force Majeure;',
        ),
        5 => 
        array (
          'p' => 'Semua kerugian yang timbul atau diderita salah satu pihak karena  terjadi Force Majeure bukan merupakan tanggung jawab pihak lain;',
        ),
        6 => 
        array (
          'p' => 'Force Majeure dimaksud ayat 1 pasal ini tidak dapat dijadikan alasan oleh salah satu pihak untuk menunda kewajiban pembayaran kepada pihak lainnya yang telah jatuh tempo sebelum terjadinya Force Majeure.',
        ),
      ),
    ),
    12 => 
    array (
      'judul' => 'Penyelesaian Sengketa',
      'text' => 'PERJANJIAN ini dibuat dengan itikad baik dan untuk dilaksanakan dan dijadikan landasan perjanjian kerjasama yang akan dibuat dalam rangka menindaklanjuti PERJANJIAN ini;

Perubahan dan/atau penambahan syarat-syarat dan ketentuan-ketentuan dari PERJANJIAN ini hanya dapat dilakukan atas dasar persetujuan PARA PIHAK yang akan dituangkan dalam Addendum/Amandemen dari PERJANJIAN ini;

Pelaksanaan atas PERJANJIAN ini PARA PIHAK sepakat untuk senantiasa menempuh cara musyawarah dan dengan itikad baik untuk mencapai mufakat;

Apabila terjadi perselisihan yang tidak dapat diselesaikan secara musyawarah, maka PARA PIHAK sepakat untuk menyelesaikannya melalui Kantor Kepaniteraan Pengadilan Negeri Kota Cirebon.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PERJANJIAN ini dibuat dengan itikad baik dan untuk dilaksanakan dan dijadikan landasan perjanjian kerjasama yang akan dibuat dalam rangka menindaklanjuti PERJANJIAN ini;',
        ),
        1 => 
        array (
          'p' => 'Perubahan dan/atau penambahan syarat-syarat dan ketentuan-ketentuan dari PERJANJIAN ini hanya dapat dilakukan atas dasar persetujuan PARA PIHAK yang akan dituangkan dalam Addendum/Amandemen dari PERJANJIAN ini;',
        ),
        2 => 
        array (
          'p' => 'Pelaksanaan atas PERJANJIAN ini PARA PIHAK sepakat untuk senantiasa menempuh cara musyawarah dan dengan itikad baik untuk mencapai mufakat;',
        ),
        3 => 
        array (
          'p' => 'Apabila terjadi perselisihan yang tidak dapat diselesaikan secara musyawarah, maka PARA PIHAK sepakat untuk menyelesaikannya melalui Kantor Kepaniteraan Pengadilan Negeri Kota Cirebon.',
        ),
      ),
    ),
    13 => 
    array (
      'judul' => 'Lain-Lain',
      'text' => 'Perjanjian Berlangganan Jasa ini dapat ditambah, dimodifikasi, dan disesuaikan atas persetujuan kedua belah pihak;

Lampiran-lampiran dalam Perjanjian Berlangganan Jasa ini merupakan bagian yang tidak dapat dipisahkan dan mempunyai kekuatan hukum yang sama;

Bila terjadi perbedaan pengertian antara teks bahasa Inggris dan teks bahasa Indonesia dalam Perjanjian Berlangganan Jasa ini, maka teks bahasa Indonesia yang berlaku;

Demikian Perjanjian ini dibuat dan ditandatangani, dibuat rangkap 2 (dua) dan memiliki kekuatan hukum yang sama, dibuat dengan itikad baik untuk dilaksanakan oleh kedua belah Pihak.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Perjanjian Berlangganan Jasa ini dapat ditambah, dimodifikasi, dan disesuaikan atas persetujuan kedua belah pihak;',
        ),
        1 => 
        array (
          'p' => 'Lampiran-lampiran dalam Perjanjian Berlangganan Jasa ini merupakan bagian yang tidak dapat dipisahkan dan mempunyai kekuatan hukum yang sama;',
        ),
        2 => 
        array (
          'p' => 'Bila terjadi perbedaan pengertian antara teks bahasa Inggris dan teks bahasa Indonesia dalam Perjanjian Berlangganan Jasa ini, maka teks bahasa Indonesia yang berlaku;',
        ),
        3 => 
        array (
          'p' => 'Demikian Perjanjian ini dibuat dan ditandatangani, dibuat rangkap 2 (dua) dan memiliki kekuatan hukum yang sama, dibuat dengan itikad baik untuk dilaksanakan oleh kedua belah Pihak.',
        ),
      ),
    ),
  ),
  'tutup' => 'PIHAK PERTAMA

PT Bina Informatika Solusi

PIHAK KEDUA

PT Triputra Textile Industry

Ageng Bagja Priyadi, S.T.,M.Kom

Direktur

Delly Yulia

Manager Area Bandung',
  'tutupBlocks' => 
  array (
    0 => 
    array (
      'table' => 
      array (
        'rows' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'c' => 
              array (
                0 => 'PIHAK PERTAMA',
                1 => 'PT Bina Informatika Solusi',
              ),
              's' => 1,
            ),
            1 => 
            array (
              'c' => 
              array (
                0 => 'PIHAK KEDUA',
                1 => 'PT Triputra Textile Industry',
              ),
              's' => 1,
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'c' => 
              array (
                0 => 'Ageng Bagja Priyadi, S.T.,M.Kom',
                1 => 'Direktur',
              ),
              's' => 1,
            ),
            1 => 
            array (
              'c' => 
              array (
                0 => 'Delly Yulia',
                1 => 'Manager Area Bandung',
              ),
              's' => 1,
            ),
          ),
        ),
        'bordered' => false,
        'head' => true,
      ),
    ),
  ),
  'lampiran' => 
  array (
    0 => 
    array (
      'judul' => 'LAMPIRAN A',
      'text' => '',
      'blocks' => 
      array (
        0 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nomor Perjanjian Berlangganan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '152/FBT/J.S/IX/2025',
                  ),
                  's' => 1,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Tanggal Awal Berlangganan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '1 September 2025',
                  ),
                  's' => 1,
                ),
              ),
              2 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Tanggal Akhir Berlangganan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '31 Agustus 2026',
                  ),
                  's' => 1,
                ),
              ),
              3 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama dan Alamat Pelanggan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'PT TRIPUTRA TEXTILE INDUSTRY',
                    1 => 'Jalan Raya Laswi No. 8, Kec. Majalaya, Kab. Bandung',
                  ),
                  's' => 1,
                ),
              ),
              4 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'NPWP',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '02.735.813.4-444.000',
                  ),
                  's' => 1,
                ),
              ),
              5 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nomor Telepon Pelanggan /',
                    1 => 'Penanggungjawab',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '022-5955078',
                  ),
                  's' => 1,
                ),
              ),
              6 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nomor Handphone Pelanggan /',
                    1 => 'Penanggungjawab',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => '0831-2089-9561',
                  ),
                  's' => 1,
                ),
              ),
              7 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama Penanggungjawab Administrasi/Keuangan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Delly Yulia',
                  ),
                  's' => 1,
                ),
              ),
              8 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama Penanggungjawab Teknisi',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Sugiharto',
                  ),
                  's' => 1,
                ),
              ),
              9 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Jenis Layanan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'SOHO TIF 200Mbps',
                    1 => '(Rp5.000.000)',
                    2 => 'SOHO TIF 50Mbps',
                    3 => '(Rp1.800.000)',
                  ),
                  's' => 1,
                ),
              ),
              10 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Layanan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp6.800.000,- (Perbulan)',
                    1 => 'Belum termasuk PPN',
                  ),
                  's' => 1,
                ),
              ),
              11 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Instalasi',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp2000000,- (one time charge)',
                    1 => 'Belum termasuk PPN',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
      ),
    ),
  ),
),
            ],
            'kontrak-payung' => [
                'title' => 'Perjanjian Kerja Sama (Kontrak Payung) Berlangganan Jasa Metro Fiber Optik',
                'header_data' => array (
  'kopInstansi' => '[Nama Perusahaan PIHAK PERTAMA]',
  'kopAlamat' => '[Alamat perusahaan PIHAK PERTAMA]',
  'kopKontrak' => 'PERJANJIAN KERJA SAMA (KONTRAK PAYUNG)',
  'nomorSurat' => 'PK/001/VIII/2026',
  'perihalSurat' => 'Berlangganan Jasa Metro Fiber Optik',
  'sifatSurat' => 'Penting',
),
                'body_content' => array (
  'preamble' => 'PERJANJIAN KERJA SAMA (KONTRAK PAYUNG)
BERLANGGANAN JASA METRO FIBER OPTIK

PT BINA INFORMATIKA SOLUSI

DENGAN

PT DINAR WAHANA GEMILANG

Nomor: 238/FBT/J.M/VI/2026

PERJANJIAN KERJA SAMA (KONTRAK PAYUNG)
BERLANGGANAN JASA METRO FIBER OPTIK

Nomor: 238/FBT/J.M/VI/2026

Pada hari ini, Selasa, tanggal 23 Juni 2026, bertempat di Cirebon, telah dibuat dan ditandatangani Perjanjian Kerja Sama Berlangganan Jasa Metro Fiber Optik (“Perjanjian”), oleh dan antara:

PT Bina Informatika Solusi, berkedudukan di Jalan Prakarsa Muda Nomor 258, Kel. Pekiringan, Kec. Kesambi, Kota Cirebon, Jawa Barat 45131. Berdasarkan Akta Berita Acara RUPS Tahunan Perseroan Terbatas “PT Bina Informatika Solusi”, Nomor 5, tanggal 10 Juli 2026, dibuat dihadapan Irni Yuniati, S.H., M.Kn., Notaris di Kota Cimahi. Dalam hal ini diwakili oleh Ageng Bagja Priyadi, S.T., M. Kom., selaku Direktur, sah bertindak untuk dan atas nama PT Bina Informatika Solusi, selanjutnya disebut “PIHAK PERTAMA”

PT Dinar Wahana Gemilang, berkedudukan di Jl. Cetarip Barat (Cetarip Raya) No. 15/200 Rt/Rw 05/10, Kopo, Kota Bandung. Berdasarkan Risalah Rapat PT. Dinar Wahana Gemilang Nomor 16 Tanggal 12 Desember 2022, dibuat dihadapan Arief Karisma, S.H., M.Kn Notaris di Kabupaten Bandung. Dalam hal ini diwakili oleh Wildan Arief Santika Budi, selaku Direktur, sah bertindak untuk dan atas nama PT Dinar Wahana Gemilang, selanjutnya disebut “PIHAK KEDUA”

PIHAK PERTAMA dan PIHAK KEDUA secara bersama-sama selanjutnya disebut “PARA PIHAK”

PARA PIHAK dengan ini menerangkan telah sepakat untuk mengikatkan diri pada syarat-syarat dan ketentuan-ketentuan sebagai berikut:',
  'isi' => 
  array (
    0 => 
    array (
      'judul' => 'Definisi',
      'text' => '“Perjanjian” adalah Kontrak Payung ini berikut seluruh Lampiran Berlangganan Jasa, Service Order Form, Service Upgrade/Downgrade Form, Berita Acara, Addendum dan/atau Amandemen yang merupakan satu kesatuan yang tidak terpisahkan.

“Jasa” adalah layanan yang disediakan oleh PIHAK PERTAMA kepada PIHAK KEDUA sebagaimana tercantum dalam setiap Lampiran Berlangganan Jasa.

“Metro Fiber Optik” adalah layanan konektivitas jaringan privat berbasis fiber optik berkecepatan tinggi menggunakan teknologi Metro Ethernet yang menghubungkan satu atau lebih lokasi PIHAK KEDUA secara aman dan stabil dengan kapasitas besar (Gbps), Quality of Service (QoS) terjamin, fleksibilitas Point-to-Point dan/atau Multipoint, serta keandalan tinggi untuk kebutuhan bisnis dan aplikasi real-time.

“Biaya Jasa” adalah biaya yang wajib dibayarkan oleh PIHAK KEDUA kepada PIHAK PERTAMA atas penggunaan Jasa sebagaimana tercantum dalam masing-masing Lampiran Berlangganan Jasa.

“Service Order Form” adalah formulir permohonan berlangganan Jasa yang diajukan oleh PIHAK KEDUA kepada PIHAK PERTAMA.

“Service Upgrade/Downgrade Form” adalah formulir permohonan perubahan kapasitas layanan yang diajukan oleh PIHAK KEDUA kepada PIHAK PERTAMA.

“Lampiran Berlangganan Jasa” adalah dokumen yang memuat rincian teknis dan komersial setiap lokasi layanan termasuk bandwidth, rute koneksi, biaya layanan, biaya instalasi, serta masa berlaku.

“Berita Acara” adalah dokumen serah terima aktivasi, pemasangan, upgrade, downgrade, atau penyelesaian pekerjaan yang ditandatangani PARA PIHAK.

“Isolir” adalah penghentian sementara layanan oleh PIHAK PERTAMA akibat tidak dipenuhinya kewajiban pembayaran atau kewajiban lainnya oleh PIHAK KEDUA.

“Hari Kerja” adalah hari selain hari Minggu dan hari libur nasional yang ditetapkan Pemerintah Republik Indonesia.

“Pemberitahuan Tertulis” adalah komunikasi resmi PARA PIHAK melalui surat atau media elektronik.

“SLA (Service Level Agreement)” adalah standar tingkat layanan minimum yang dijamin oleh PIHAK PERTAMA sebagaimana tercantum dalam Lampiran SLA.

Apabila terdapat istilah yang belum diatur dalam Perjanjian ini, maka akan ditafsirkan sesuai ketentuan peraturan perundang-undangan yang berlaku.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => '“Perjanjian” adalah Kontrak Payung ini berikut seluruh Lampiran Berlangganan Jasa, Service Order Form, Service Upgrade/Downgrade Form, Berita Acara, Addendum dan/atau Amandemen yang merupakan satu kesatuan yang tidak terpisahkan.',
        ),
        1 => 
        array (
          'p' => '“Jasa” adalah layanan yang disediakan oleh PIHAK PERTAMA kepada PIHAK KEDUA sebagaimana tercantum dalam setiap Lampiran Berlangganan Jasa.',
        ),
        2 => 
        array (
          'p' => '“Metro Fiber Optik” adalah layanan konektivitas jaringan privat berbasis fiber optik berkecepatan tinggi menggunakan teknologi Metro Ethernet yang menghubungkan satu atau lebih lokasi PIHAK KEDUA secara aman dan stabil dengan kapasitas besar (Gbps), Quality of Service (QoS) terjamin, fleksibilitas Point-to-Point dan/atau Multipoint, serta keandalan tinggi untuk kebutuhan bisnis dan aplikasi real-time.',
        ),
        3 => 
        array (
          'p' => '“Biaya Jasa” adalah biaya yang wajib dibayarkan oleh PIHAK KEDUA kepada PIHAK PERTAMA atas penggunaan Jasa sebagaimana tercantum dalam masing-masing Lampiran Berlangganan Jasa.',
        ),
        4 => 
        array (
          'p' => '“Service Order Form” adalah formulir permohonan berlangganan Jasa yang diajukan oleh PIHAK KEDUA kepada PIHAK PERTAMA.',
        ),
        5 => 
        array (
          'p' => '“Service Upgrade/Downgrade Form” adalah formulir permohonan perubahan kapasitas layanan yang diajukan oleh PIHAK KEDUA kepada PIHAK PERTAMA.',
        ),
        6 => 
        array (
          'p' => '“Lampiran Berlangganan Jasa” adalah dokumen yang memuat rincian teknis dan komersial setiap lokasi layanan termasuk bandwidth, rute koneksi, biaya layanan, biaya instalasi, serta masa berlaku.',
        ),
        7 => 
        array (
          'p' => '“Berita Acara” adalah dokumen serah terima aktivasi, pemasangan, upgrade, downgrade, atau penyelesaian pekerjaan yang ditandatangani PARA PIHAK.',
        ),
        8 => 
        array (
          'p' => '“Isolir” adalah penghentian sementara layanan oleh PIHAK PERTAMA akibat tidak dipenuhinya kewajiban pembayaran atau kewajiban lainnya oleh PIHAK KEDUA.',
        ),
        9 => 
        array (
          'p' => '“Hari Kerja” adalah hari selain hari Minggu dan hari libur nasional yang ditetapkan Pemerintah Republik Indonesia.',
        ),
        10 => 
        array (
          'p' => '“Pemberitahuan Tertulis” adalah komunikasi resmi PARA PIHAK melalui surat atau media elektronik.',
        ),
        11 => 
        array (
          'p' => '“SLA (Service Level Agreement)” adalah standar tingkat layanan minimum yang dijamin oleh PIHAK PERTAMA sebagaimana tercantum dalam Lampiran SLA.',
        ),
        12 => 
        array (
          'p' => 'Apabila terdapat istilah yang belum diatur dalam Perjanjian ini, maka akan ditafsirkan sesuai ketentuan peraturan perundang-undangan yang berlaku.',
        ),
      ),
    ),
    1 => 
    array (
      'judul' => 'Ruang Lingkup Layanan',
      'text' => 'PARA PIHAK sepakat bahwa layanan Metro Fiber Optik dapat diberikan pada satu atau lebih lokasi PIHAK KEDUA;

Setiap lokasi layanan dituangkan dalam Lampiran Berlangganan Jasa tersendiri yang memiliki kekuatan hukum yang sama dengan Perjanjian ini;

Layanan Jasa yang disediakan PIHAK PERTAMA berdasarkan Perjanjian ini dapat digunakan oleh PIHAK KEDUA selama 24 jam/hari (7 hari/minggu);

Penyediaan Fasilitas dan Jasa PIHAK PERTAMA akan dilakukan sesuai dengan konfigurasi teknis yang telah disepakati;

Terminal, perangkat antarmuka, maupun perangkat lainnya milik PIHAK KEDUA yang akan dihubungkan dengan jaringan PIHAK PERTAMA wajib memenuhi spesifikasi teknis yang ditetapkan oleh PIHAK PERTAMA dan memperoleh persetujuan terlebih dahulu. PIHAK PERTAMA berhak menolak perangkat yang berpotensi mengganggu keamanan, stabilitas, atau kinerja jaringan;

Penambahan, pengurangan, perubahan, maupun pengakhiran layanan pada suatu lokasi tidak memerlukan perubahan terhadap Kontrak Payung ini dan cukup dilakukan melalui penerbitan, perubahan, atau pengakhiran Lampiran Berlangganan Jasa yang disepakati PARA PIHAK. Pengakhiran satu Lampiran tidak mengakhiri keberlakuan Perjanjian ini maupun Lampiran lainnya yang masih berlaku.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PARA PIHAK sepakat bahwa layanan Metro Fiber Optik dapat diberikan pada satu atau lebih lokasi PIHAK KEDUA;',
        ),
        1 => 
        array (
          'p' => 'Setiap lokasi layanan dituangkan dalam Lampiran Berlangganan Jasa tersendiri yang memiliki kekuatan hukum yang sama dengan Perjanjian ini;',
        ),
        2 => 
        array (
          'p' => 'Layanan Jasa yang disediakan PIHAK PERTAMA berdasarkan Perjanjian ini dapat digunakan oleh PIHAK KEDUA selama 24 jam/hari (7 hari/minggu);',
        ),
        3 => 
        array (
          'p' => 'Penyediaan Fasilitas dan Jasa PIHAK PERTAMA akan dilakukan sesuai dengan konfigurasi teknis yang telah disepakati;',
        ),
        4 => 
        array (
          'p' => 'Terminal, perangkat antarmuka, maupun perangkat lainnya milik PIHAK KEDUA yang akan dihubungkan dengan jaringan PIHAK PERTAMA wajib memenuhi spesifikasi teknis yang ditetapkan oleh PIHAK PERTAMA dan memperoleh persetujuan terlebih dahulu. PIHAK PERTAMA berhak menolak perangkat yang berpotensi mengganggu keamanan, stabilitas, atau kinerja jaringan;',
        ),
        5 => 
        array (
          'p' => 'Penambahan, pengurangan, perubahan, maupun pengakhiran layanan pada suatu lokasi tidak memerlukan perubahan terhadap Kontrak Payung ini dan cukup dilakukan melalui penerbitan, perubahan, atau pengakhiran Lampiran Berlangganan Jasa yang disepakati PARA PIHAK. Pengakhiran satu Lampiran tidak mengakhiri keberlakuan Perjanjian ini maupun Lampiran lainnya yang masih berlaku.',
        ),
      ),
    ),
    2 => 
    array (
      'judul' => 'Aktivasi Layanan',
      'text' => 'Aktivasi setiap Layanan akan dimulai setelah Fasilitas PIHAK PERTAMA siap dioperasikan dan dinyatakan dengan Berita Acara yang telah ditandatangani oleh PARA PIHAK.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Aktivasi setiap Layanan akan dimulai setelah Fasilitas PIHAK PERTAMA siap dioperasikan dan dinyatakan dengan Berita Acara yang telah ditandatangani oleh PARA PIHAK.',
        ),
      ),
    ),
    3 => 
    array (
      'judul' => 'Jangka Waktu Berlangganan',
      'text' => 'Jangka waktu berlangganan setiap layanan Metro Fiber Optik adalah minimal 1 (satu) tahun terhitung sejak tanggal aktivasi layanan yang dibuktikan dengan Berita Acara yang ditandatangani PARA PIHAK untuk masing-masing Lampiran Berlangganan Jasa;

Apabila PIHAK KEDUA  mengakhiri Layanan sebelum Jangka Waktu berakhir sebagaimana dimaksud dalam ayat 1 (satu) Pasal ini, maka PIHAK KEDUA akan dikenakan denda sebagaimana berikut:

Apabila 30 (Tiga puluh) hari sebelum jangka waktu dalam pasal  4 ayat 1 ini berakhir PIHAK KEDUA tidak melakukan pemberitahuan tertulis pengakhiran Layanan, maka Syarat dan Ketentuan Berlangganan akan otomatis berlanjut selama 1 (Satu) tahun (“Jangka Waktu Perpanjangan”);

Perpanjangan atau pengakhiran pada satu Lampiran Berlangganan Jasa tidak mempengaruhi keberlakuan Lampiran lainnya yang masih aktif.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Jangka waktu berlangganan setiap layanan Metro Fiber Optik adalah minimal 1 (satu) tahun terhitung sejak tanggal aktivasi layanan yang dibuktikan dengan Berita Acara yang ditandatangani PARA PIHAK untuk masing-masing Lampiran Berlangganan Jasa;',
        ),
        1 => 
        array (
          'p' => 'Apabila PIHAK KEDUA  mengakhiri Layanan sebelum Jangka Waktu berakhir sebagaimana dimaksud dalam ayat 1 (satu) Pasal ini, maka PIHAK KEDUA akan dikenakan denda sebagaimana berikut:',
        ),
        2 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '100% x Biaya Bulanan x Bulan yang belum terpenuhi',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
        3 => 
        array (
          'p' => 'Apabila 30 (Tiga puluh) hari sebelum jangka waktu dalam pasal  4 ayat 1 ini berakhir PIHAK KEDUA tidak melakukan pemberitahuan tertulis pengakhiran Layanan, maka Syarat dan Ketentuan Berlangganan akan otomatis berlanjut selama 1 (Satu) tahun (“Jangka Waktu Perpanjangan”);',
        ),
        4 => 
        array (
          'p' => 'Perpanjangan atau pengakhiran pada satu Lampiran Berlangganan Jasa tidak mempengaruhi keberlakuan Lampiran lainnya yang masih aktif.',
        ),
      ),
    ),
    4 => 
    array (
      'judul' => 'Pembayaran',
      'text' => 'PIHAK KEDUA wajib melakukan pembayaran atas seluruh layanan Metro Fiber Optik yang digunakan sesuai dengan masing-masing Lampiran Berlangganan Jasa;

Pembayaran dilakukan oleh PIHAK KEDUA selambat–lambatnya pada tanggal sesuai dengan invoice yang diterbitkan atau selambat-lambatnya 7 (tujuh) hari dari tanggal invoice diterbitkan. Tidak diterimanya invoice oleh PIHAK KEDUA tidak menghapus kewajiban pembayaran sepanjang tagihan tersebut telah diterbitkan oleh PIHAK PERTAMA;

Atas setiap keterlambatan pembayaran dari tanggal sebagaimana dimaksud pada ayat (2), maka PIHAK KEDUA dikenakan denda keterlambatan pembayaran sebesar 1 (satu) permil setiap hari keterlambatan dari total tagihan yang belum dibayarkan;

Apabila dalam 1 (satu) bulan PIHAK KEDUA belum memenuhi kewajibannya tersebut, maka PIHAK PERTAMA akan melakukan pemutusan sementara (isolir) terhadap satu atau seluruh layanan tanpa pemberitahuan terlebih dahulu kepada PIHAK KEDUA;

Apabila PIHAK KEDUA melunasi biaya-biaya dalam pasal ini, maka PIHAK PERTAMA akan membuka pemutusan sementara (isolir) dalam waktu selambat-lambatnya 1 (satu) hari kerja;

Seluruh biaya administrasi perbankan yang timbul dalam proses pembayaran menjadi tanggung jawab PIHAK KEDUA;

Pajak-pajak yang timbul atas pelaksanaan Perjanjian ini menjadi tanggung jawab masing-masing Pihak berdasarkan peraturan perundang-undangan yang berlaku di Indonesia;

PIHAK PERTAMA memiliki hak mengubah Biaya Instalasi atau Biaya Bulanan dengan pemberitahuan terlebih dahulu secara tertulis 30 (tiga puluh) Hari Kalender sebelum biaya baru tersebut diberlakukan;

Total tagihan bulanan PIHAK KEDUA merupakan akumulasi dari seluruh Lampiran Berlangganan Jasa yang masih aktif;

Seluruh pembayaran dianggap telah dilakukan PIHAK KEDUA setelah pembayaran diterima di rekening PIHAK PERTAMA, dengan detail sebagai berikut:

Bank Mandiri

No. Rekening: 1340001209104

Atas Nama   : PT Bina Informatika Solusi

Bank Rakyat Indonesia (BRI)

No. Rekening: 010701003038305

Atas Nama   : PT Bina Informatika Solusi',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA wajib melakukan pembayaran atas seluruh layanan Metro Fiber Optik yang digunakan sesuai dengan masing-masing Lampiran Berlangganan Jasa;',
        ),
        1 => 
        array (
          'p' => 'Pembayaran dilakukan oleh PIHAK KEDUA selambat–lambatnya pada tanggal sesuai dengan invoice yang diterbitkan atau selambat-lambatnya 7 (tujuh) hari dari tanggal invoice diterbitkan. Tidak diterimanya invoice oleh PIHAK KEDUA tidak menghapus kewajiban pembayaran sepanjang tagihan tersebut telah diterbitkan oleh PIHAK PERTAMA;',
        ),
        2 => 
        array (
          'p' => 'Atas setiap keterlambatan pembayaran dari tanggal sebagaimana dimaksud pada ayat (2), maka PIHAK KEDUA dikenakan denda keterlambatan pembayaran sebesar 1 (satu) permil setiap hari keterlambatan dari total tagihan yang belum dibayarkan;',
        ),
        3 => 
        array (
          'p' => 'Apabila dalam 1 (satu) bulan PIHAK KEDUA belum memenuhi kewajibannya tersebut, maka PIHAK PERTAMA akan melakukan pemutusan sementara (isolir) terhadap satu atau seluruh layanan tanpa pemberitahuan terlebih dahulu kepada PIHAK KEDUA;',
        ),
        4 => 
        array (
          'p' => 'Apabila PIHAK KEDUA melunasi biaya-biaya dalam pasal ini, maka PIHAK PERTAMA akan membuka pemutusan sementara (isolir) dalam waktu selambat-lambatnya 1 (satu) hari kerja;',
        ),
        5 => 
        array (
          'p' => 'Seluruh biaya administrasi perbankan yang timbul dalam proses pembayaran menjadi tanggung jawab PIHAK KEDUA;',
        ),
        6 => 
        array (
          'p' => 'Pajak-pajak yang timbul atas pelaksanaan Perjanjian ini menjadi tanggung jawab masing-masing Pihak berdasarkan peraturan perundang-undangan yang berlaku di Indonesia;',
        ),
        7 => 
        array (
          'p' => 'PIHAK PERTAMA memiliki hak mengubah Biaya Instalasi atau Biaya Bulanan dengan pemberitahuan terlebih dahulu secara tertulis 30 (tiga puluh) Hari Kalender sebelum biaya baru tersebut diberlakukan;',
        ),
        8 => 
        array (
          'p' => 'Total tagihan bulanan PIHAK KEDUA merupakan akumulasi dari seluruh Lampiran Berlangganan Jasa yang masih aktif;',
        ),
        9 => 
        array (
          'p' => 'Seluruh pembayaran dianggap telah dilakukan PIHAK KEDUA setelah pembayaran diterima di rekening PIHAK PERTAMA, dengan detail sebagai berikut:',
        ),
        10 => 
        array (
          'p' => 'Bank Mandiri',
        ),
        11 => 
        array (
          'p' => 'No. Rekening: 1340001209104',
        ),
        12 => 
        array (
          'p' => 'Atas Nama   : PT Bina Informatika Solusi',
        ),
        13 => 
        array (
          'p' => 'Bank Rakyat Indonesia (BRI)',
        ),
        14 => 
        array (
          'p' => 'No. Rekening: 010701003038305',
        ),
        15 => 
        array (
          'p' => 'Atas Nama   : PT Bina Informatika Solusi',
        ),
      ),
    ),
    5 => 
    array (
      'judul' => 'Hak dan Kewajiban Pihak Pertama',
      'text' => 'PIHAK PERTAMA berkewajiban melakukan pemeliharaan dan/atau perbaikan atas kerusakan atau gangguan pada saluran, perangkat, dan/atau fasilitas milik PIHAK PERTAMA yang digunakan untuk penyelenggaraan Jasa;

Dalam hal kerusakan atau gangguan sebagaimana dimaksud pada ayat (1) Pasal ini terbukti disebabkan oleh kesalahan, kesengajaan, dan/atau kelalaian PIHAK KEDUA dan/atau pihak yang berada di bawah tanggung jawab PIHAK KEDUA, maka PIHAK PERTAMA berhak membebankan biaya perbaikan dan/atau penggantian kepada PIHAK KEDUA sesuai perhitungan wajar PIHAK PERTAMA;

PIHAK PERTAMA berhak memperoleh informasi yang diperlukan mengenai tujuan penggunaan Jasa oleh PIHAK KEDUA untuk memastikan penggunaan Jasa tidak bertentangan dengan ketentuan peraturan perundang-undangan yang berlaku di Republik Indonesia;

PIHAK PERTAMA berhak menerima pembayaran atas Biaya Jasa dari PIHAK KEDUA sesuai dengan ketentuan dalam Perjanjian ini dan/atau Lampiran Berlangganan Jasa;

PIHAK PERTAMA tidak bertanggung jawab atas isi, keakuratan, legalitas, integritas, kerahasiaan, kehilangan, kerusakan, maupun penyalahgunaan data, informasi, aplikasi, atau sistem milik PIHAK KEDUA yang ditransmisikan melalui layanan sepanjang berada di luar kendali wajar PIHAK PERTAMA;

PIHAK PERTAMA tidak bertanggung jawab atas kerugian tidak langsung, kerugian konsekuensial, kehilangan keuntungan, kehilangan pendapatan, kehilangan peluang usaha, kehilangan data, atau kerugian lainnya yang timbul akibat penggunaan atau tidak dapat digunakannya layanan, kecuali apabila kerugian tersebut secara langsung disebabkan oleh kesalahan atau kelalaian berat PIHAK PERTAMA yang telah dibuktikan berdasarkan putusan pengadilan yang berkekuatan hukum tetap;

Hak dan kewajiban PIHAK PERTAMA sebagaimana diatur dalam Pasal ini berlaku terhadap seluruh layanan, lokasi, kapasitas, dan Lampiran Berlangganan Jasa yang diterbitkan berdasarkan Perjanjian ini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK PERTAMA berkewajiban melakukan pemeliharaan dan/atau perbaikan atas kerusakan atau gangguan pada saluran, perangkat, dan/atau fasilitas milik PIHAK PERTAMA yang digunakan untuk penyelenggaraan Jasa;',
        ),
        1 => 
        array (
          'p' => 'Dalam hal kerusakan atau gangguan sebagaimana dimaksud pada ayat (1) Pasal ini terbukti disebabkan oleh kesalahan, kesengajaan, dan/atau kelalaian PIHAK KEDUA dan/atau pihak yang berada di bawah tanggung jawab PIHAK KEDUA, maka PIHAK PERTAMA berhak membebankan biaya perbaikan dan/atau penggantian kepada PIHAK KEDUA sesuai perhitungan wajar PIHAK PERTAMA;',
        ),
        2 => 
        array (
          'p' => 'PIHAK PERTAMA berhak memperoleh informasi yang diperlukan mengenai tujuan penggunaan Jasa oleh PIHAK KEDUA untuk memastikan penggunaan Jasa tidak bertentangan dengan ketentuan peraturan perundang-undangan yang berlaku di Republik Indonesia;',
        ),
        3 => 
        array (
          'p' => 'PIHAK PERTAMA berhak menerima pembayaran atas Biaya Jasa dari PIHAK KEDUA sesuai dengan ketentuan dalam Perjanjian ini dan/atau Lampiran Berlangganan Jasa;',
        ),
        4 => 
        array (
          'p' => 'PIHAK PERTAMA tidak bertanggung jawab atas isi, keakuratan, legalitas, integritas, kerahasiaan, kehilangan, kerusakan, maupun penyalahgunaan data, informasi, aplikasi, atau sistem milik PIHAK KEDUA yang ditransmisikan melalui layanan sepanjang berada di luar kendali wajar PIHAK PERTAMA;',
        ),
        5 => 
        array (
          'p' => 'PIHAK PERTAMA tidak bertanggung jawab atas kerugian tidak langsung, kerugian konsekuensial, kehilangan keuntungan, kehilangan pendapatan, kehilangan peluang usaha, kehilangan data, atau kerugian lainnya yang timbul akibat penggunaan atau tidak dapat digunakannya layanan, kecuali apabila kerugian tersebut secara langsung disebabkan oleh kesalahan atau kelalaian berat PIHAK PERTAMA yang telah dibuktikan berdasarkan putusan pengadilan yang berkekuatan hukum tetap;',
        ),
        6 => 
        array (
          'p' => 'Hak dan kewajiban PIHAK PERTAMA sebagaimana diatur dalam Pasal ini berlaku terhadap seluruh layanan, lokasi, kapasitas, dan Lampiran Berlangganan Jasa yang diterbitkan berdasarkan Perjanjian ini.',
        ),
      ),
    ),
    6 => 
    array (
      'judul' => 'Hak dan Kewajiban Pihak Kedua',
      'text' => 'PIHAK KEDUA berhak memperoleh dan menggunakan Jasa Metro Fiber Optik sesuai dengan Lampiran Berlangganan Jasa dan ketentuan peraturan perundang-undangan yang berlaku di Republik Indonesia;

PIHAK KEDUA berkewajiban menyediakan perangkat, infrastruktur internal, izin, serta akses lokasi yang memenuhi standar teknis yang dipersyaratkan PIHAK PERTAMA agar instalasi dan aktivasi dapat dilakukan sesuai jadwal. Segala keterlambatan akibat tidak dipenuhinya kewajiban ini menjadi tanggung jawab PIHAK KEDUA;

PIHAK KEDUA dilarang memberikan kesempatan kepada pihak ketiga untuk memanfaatkan Jasa PIHAK PERTAMA tanpa persetujuan tertulis terlebih dahulu dari PIHAK PERTAMA;

PIHAK KEDUA dilarang melakukan perubahan terhadap spesifikasi teknis, konfigurasi, maupun fasilitas Jasa PIHAK PERTAMA, termasuk menghubungkan ke jaringan lain, kecuali dengan persetujuan tertulis dari PIHAK PERTAMA;

PIHAK KEDUA dilarang menghubungkan jaringan dan/atau fasilitas PIHAK PERTAMA dengan jaringan telekomunikasi umum (PSTN) maupun jaringan lainnya tanpa izin tertulis dari PIHAK PERTAMA;

PIHAK KEDUA wajib memberikan izin akses kepada PIHAK PERTAMA untuk memasuki lokasi PIHAK KEDUA sepanjang diperlukan untuk keperluan instalasi, pemeliharaan, perbaikan, dan pemeriksaan layanan;

PIHAK KEDUA wajib melakukan pembayaran seluruh Biaya Jasa, termasuk denda keterlambatan dan biaya lain yang timbul sesuai Perjanjian ini dan Lampiran Berlangganan Jasa;

PIHAK KEDUA berhak memperoleh kompensasi atas gangguan layanan yang terbukti secara langsung disebabkan oleh PIHAK PERTAMA dan tidak termasuk kondisi force majeure, gangguan pihak ketiga, atau kelalaian PIHAK KEDUA, sesuai ketentuan SLA;

Hak dan kewajiban PIHAK KEDUA sebagaimana dimaksud dalam Pasal ini berlaku untuk seluruh layanan, kapasitas, lokasi, dan Lampiran Berlangganan Jasa yang masih aktif maupun yang akan diterbitkan di kemudian hari.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA berhak memperoleh dan menggunakan Jasa Metro Fiber Optik sesuai dengan Lampiran Berlangganan Jasa dan ketentuan peraturan perundang-undangan yang berlaku di Republik Indonesia;',
        ),
        1 => 
        array (
          'p' => 'PIHAK KEDUA berkewajiban menyediakan perangkat, infrastruktur internal, izin, serta akses lokasi yang memenuhi standar teknis yang dipersyaratkan PIHAK PERTAMA agar instalasi dan aktivasi dapat dilakukan sesuai jadwal. Segala keterlambatan akibat tidak dipenuhinya kewajiban ini menjadi tanggung jawab PIHAK KEDUA;',
        ),
        2 => 
        array (
          'p' => 'PIHAK KEDUA dilarang memberikan kesempatan kepada pihak ketiga untuk memanfaatkan Jasa PIHAK PERTAMA tanpa persetujuan tertulis terlebih dahulu dari PIHAK PERTAMA;',
        ),
        3 => 
        array (
          'p' => 'PIHAK KEDUA dilarang melakukan perubahan terhadap spesifikasi teknis, konfigurasi, maupun fasilitas Jasa PIHAK PERTAMA, termasuk menghubungkan ke jaringan lain, kecuali dengan persetujuan tertulis dari PIHAK PERTAMA;',
        ),
        4 => 
        array (
          'p' => 'PIHAK KEDUA dilarang menghubungkan jaringan dan/atau fasilitas PIHAK PERTAMA dengan jaringan telekomunikasi umum (PSTN) maupun jaringan lainnya tanpa izin tertulis dari PIHAK PERTAMA;',
        ),
        5 => 
        array (
          'p' => 'PIHAK KEDUA wajib memberikan izin akses kepada PIHAK PERTAMA untuk memasuki lokasi PIHAK KEDUA sepanjang diperlukan untuk keperluan instalasi, pemeliharaan, perbaikan, dan pemeriksaan layanan;',
        ),
        6 => 
        array (
          'p' => 'PIHAK KEDUA wajib melakukan pembayaran seluruh Biaya Jasa, termasuk denda keterlambatan dan biaya lain yang timbul sesuai Perjanjian ini dan Lampiran Berlangganan Jasa;',
        ),
        7 => 
        array (
          'p' => 'PIHAK KEDUA berhak memperoleh kompensasi atas gangguan layanan yang terbukti secara langsung disebabkan oleh PIHAK PERTAMA dan tidak termasuk kondisi force majeure, gangguan pihak ketiga, atau kelalaian PIHAK KEDUA, sesuai ketentuan SLA;',
        ),
        8 => 
        array (
          'p' => 'Hak dan kewajiban PIHAK KEDUA sebagaimana dimaksud dalam Pasal ini berlaku untuk seluruh layanan, kapasitas, lokasi, dan Lampiran Berlangganan Jasa yang masih aktif maupun yang akan diterbitkan di kemudian hari.',
        ),
      ),
    ),
    7 => 
    array (
      'judul' => 'Pembatalan',
      'text' => 'Dalam hal PIHAK KEDUA membatalkan salah satu atau seluruh layanan yang telah disepakati dalam Service Order Form dan/atau Lampiran Berlangganan Jasa sebelum dan/atau sesudah dilakukan aktivasi sebagaimana dibuktikan dengan Berita Acara, maka PIHAK KEDUA tetap wajib melakukan pelunasan atas Biaya Instalasi untuk layanan yang dibatalkan tersebut.

Pembatalan terhadap satu layanan pada satu Lampiran Berlangganan Jasa tidak mempengaruhi keberlakuan layanan pada Lampiran Berlangganan Jasa lainnya yang masih aktif.

Ketentuan pembatalan ini tidak mengurangi hak PIHAK PERTAMA untuk menagih kewajiban PIHAK KEDUA lainnya yang telah timbul berdasarkan Perjanjian ini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Dalam hal PIHAK KEDUA membatalkan salah satu atau seluruh layanan yang telah disepakati dalam Service Order Form dan/atau Lampiran Berlangganan Jasa sebelum dan/atau sesudah dilakukan aktivasi sebagaimana dibuktikan dengan Berita Acara, maka PIHAK KEDUA tetap wajib melakukan pelunasan atas Biaya Instalasi untuk layanan yang dibatalkan tersebut.',
        ),
        1 => 
        array (
          'p' => 'Pembatalan terhadap satu layanan pada satu Lampiran Berlangganan Jasa tidak mempengaruhi keberlakuan layanan pada Lampiran Berlangganan Jasa lainnya yang masih aktif.',
        ),
        2 => 
        array (
          'p' => 'Ketentuan pembatalan ini tidak mengurangi hak PIHAK PERTAMA untuk menagih kewajiban PIHAK KEDUA lainnya yang telah timbul berdasarkan Perjanjian ini.',
        ),
      ),
    ),
    8 => 
    array (
      'judul' => 'Perpindahan dan Pengalihan',
      'text' => 'PIHAK KEDUA dapat mengajukan permohonan perpindahan lokasi layanan. Permohonan tersebut akan dievaluasi oleh PIHAK PERTAMA berdasarkan hasil survei dan ketersediaan jaringan, kapasitas, serta pertimbangan teknis lainnya. PIHAK PERTAMA berhak menyetujui atau menolak permohonan tersebut apabila secara teknis tidak memungkinkan untuk dilaksanakan;

Seluruh biaya yang timbul akibat perpindahan lokasi layanan, termasuk namun tidak terbatas pada biaya survei, instalasi, material, aktivasi, konfigurasi, penarikan kabel, pembangunan infrastruktur tambahan, dan biaya lainnya yang diperlukan, menjadi tanggung jawab PIHAK KEDUA sesuai penawaran atau ketentuan yang berlaku pada PIHAK PERTAMA;

Pemindahan layanan atau fasilitas yang telah terpasang ke lokasi lain akan diperlakukan sebagai pemasangan atau sambungan baru, termasuk penerapan biaya instalasi dan ketentuan masa berlangganan baru, kecuali PARA PIHAK menyepakati lain secara tertulis.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK KEDUA dapat mengajukan permohonan perpindahan lokasi layanan. Permohonan tersebut akan dievaluasi oleh PIHAK PERTAMA berdasarkan hasil survei dan ketersediaan jaringan, kapasitas, serta pertimbangan teknis lainnya. PIHAK PERTAMA berhak menyetujui atau menolak permohonan tersebut apabila secara teknis tidak memungkinkan untuk dilaksanakan;',
        ),
        1 => 
        array (
          'p' => 'Seluruh biaya yang timbul akibat perpindahan lokasi layanan, termasuk namun tidak terbatas pada biaya survei, instalasi, material, aktivasi, konfigurasi, penarikan kabel, pembangunan infrastruktur tambahan, dan biaya lainnya yang diperlukan, menjadi tanggung jawab PIHAK KEDUA sesuai penawaran atau ketentuan yang berlaku pada PIHAK PERTAMA;',
        ),
        2 => 
        array (
          'p' => 'Pemindahan layanan atau fasilitas yang telah terpasang ke lokasi lain akan diperlakukan sebagai pemasangan atau sambungan baru, termasuk penerapan biaya instalasi dan ketentuan masa berlangganan baru, kecuali PARA PIHAK menyepakati lain secara tertulis.',
        ),
      ),
    ),
    9 => 
    array (
      'judul' => 'Pemutusan Perjanjian',
      'text' => 'PIHAK PERTAMA tidak bertanggung jawab atas gangguan, penurunan kualitas layanan, maupun pemutusan layanan yang disebabkan oleh kerusakan, kegagalan sistem, perangkat, jaringan internal, sumber daya listrik, konfigurasi, atau fasilitas lain yang berada di bawah penguasaan dan tanggung jawab PIHAK KEDUA maupun pihak ketiga yang ditunjuk oleh PIHAK KEDUA;

Apabila pemutusan layanan terjadi akibat pelanggaran PERJANJIAN oleh PIHAK KEDUA, maka PIHAK KEDUA tetap berkewajiban melunasi seluruh tagihan yang telah jatuh tempo, biaya berlangganan untuk sisa masa kontrak, denda, penalti terminasi dini, serta kewajiban finansial lainnya yang timbul berdasarkan PERJANJIAN ini;

Dalam hal salah satu pihak dinyatakan pailit berdasarkan putusan pengadilan yang berkekuatan hukum tetap, dibubarkan, dicabut izin usahanya oleh instansi yang berwenang, atau secara permanen menghentikan kegiatan usahanya, maka pihak lainnya berhak mengakhiri PERJANJIAN ini dengan pemberitahuan tertulis;

Apabila PIHAK PERTAMA terbukti tidak memenuhi SLA minimum sebagaimana diatur dalam Lampiran SLA karena kesalahan atau kelalaian PIHAK PERTAMA dan kondisi tersebut terjadi secara berulang selama 3 (tiga) bulan berturut-turut, maka PIHAK KEDUA berhak memperoleh kompensasi sesuai Lampiran SLA. Apabila setelah diberikan 3 (tiga) teguran tertulis secara berturut-turut PIHAK PERTAMA tetap tidak melakukan perbaikan dalam jangka waktu yang wajar, PIHAK KEDUA berhak mengakhiri PERJANJIAN tanpa dikenakan penalti terminasi dini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'PIHAK PERTAMA tidak bertanggung jawab atas gangguan, penurunan kualitas layanan, maupun pemutusan layanan yang disebabkan oleh kerusakan, kegagalan sistem, perangkat, jaringan internal, sumber daya listrik, konfigurasi, atau fasilitas lain yang berada di bawah penguasaan dan tanggung jawab PIHAK KEDUA maupun pihak ketiga yang ditunjuk oleh PIHAK KEDUA;',
        ),
        1 => 
        array (
          'p' => 'Apabila pemutusan layanan terjadi akibat pelanggaran PERJANJIAN oleh PIHAK KEDUA, maka PIHAK KEDUA tetap berkewajiban melunasi seluruh tagihan yang telah jatuh tempo, biaya berlangganan untuk sisa masa kontrak, denda, penalti terminasi dini, serta kewajiban finansial lainnya yang timbul berdasarkan PERJANJIAN ini;',
        ),
        2 => 
        array (
          'p' => 'Dalam hal salah satu pihak dinyatakan pailit berdasarkan putusan pengadilan yang berkekuatan hukum tetap, dibubarkan, dicabut izin usahanya oleh instansi yang berwenang, atau secara permanen menghentikan kegiatan usahanya, maka pihak lainnya berhak mengakhiri PERJANJIAN ini dengan pemberitahuan tertulis;',
        ),
        3 => 
        array (
          'p' => 'Apabila PIHAK PERTAMA terbukti tidak memenuhi SLA minimum sebagaimana diatur dalam Lampiran SLA karena kesalahan atau kelalaian PIHAK PERTAMA dan kondisi tersebut terjadi secara berulang selama 3 (tiga) bulan berturut-turut, maka PIHAK KEDUA berhak memperoleh kompensasi sesuai Lampiran SLA. Apabila setelah diberikan 3 (tiga) teguran tertulis secara berturut-turut PIHAK PERTAMA tetap tidak melakukan perbaikan dalam jangka waktu yang wajar, PIHAK KEDUA berhak mengakhiri PERJANJIAN tanpa dikenakan penalti terminasi dini.',
        ),
      ),
    ),
    10 => 
    array (
      'judul' => 'Ketentuan Perubahan',
      'text' => 'Selama masa berlakunya Perjanjian, salah satu pihak dapat mengajukan usulan perubahan Perjanjian dengan mengajukan usulan secara tertulis kepada pihak lainnya;

Dalam jangka waktu 30 hari setelah menerima pemberitahuan tertulis mengenai usulan perubahan dari PIHAK KEDUA, PIHAK PERTAMA akan memberitahu PIHAK KEDUA apakah perubahan dapat dilaksanakan atau tidak. Apabila perubahan tersebut dapat dilaksanakan, maka PIHAK PERTAMA berhak mengajukan perubahan atas biaya Jasa dan ketentuan lainnya dari Perjanjian ini;

PIHAK KEDUA dapat mengajukan usulan upgrade dan downgrade layanan selama masa berlakunya perjanjian melalui pemberitahuan tertulis 30 hari sebelumnya;

Penurunan kapasitas / bandwidth / downgrade apabila ada kapasitas bandwidth / downgrade layanan sebelum berakhirnya masa berlangganan minimal 1 tahun akan dikenakan penalti dengan ketentuan sebagai berikut (100% X Biaya Bulanan X Bulan yang belum terpenuhi);

Selama masa berlakunya Perjanjian ini Pihak Kedua tidak bisa mengajukan perubahan biaya layanan yang berjalan sampai dengan masa kontrak Perjanjian ini berakhir. Terkecuali adanya permohonan upgrade layanan;

Apabila ada hal-hal  yang belum diatur dalam Kontrak ini. Maka hal-hal tersebut akan diatur dan ditetapkan kemudian secara tertulis tertuang dalam Amandemen dan atau Addendum dengan tetap memperhatikan ketentuan-ketentuan dan peraturan intern PIHAK PERTAMA dan hukum yang berlaku di Indonesia.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Selama masa berlakunya Perjanjian, salah satu pihak dapat mengajukan usulan perubahan Perjanjian dengan mengajukan usulan secara tertulis kepada pihak lainnya;',
        ),
        1 => 
        array (
          'p' => 'Dalam jangka waktu 30 hari setelah menerima pemberitahuan tertulis mengenai usulan perubahan dari PIHAK KEDUA, PIHAK PERTAMA akan memberitahu PIHAK KEDUA apakah perubahan dapat dilaksanakan atau tidak. Apabila perubahan tersebut dapat dilaksanakan, maka PIHAK PERTAMA berhak mengajukan perubahan atas biaya Jasa dan ketentuan lainnya dari Perjanjian ini;',
        ),
        2 => 
        array (
          'p' => 'PIHAK KEDUA dapat mengajukan usulan upgrade dan downgrade layanan selama masa berlakunya perjanjian melalui pemberitahuan tertulis 30 hari sebelumnya;',
        ),
        3 => 
        array (
          'p' => 'Penurunan kapasitas / bandwidth / downgrade apabila ada kapasitas bandwidth / downgrade layanan sebelum berakhirnya masa berlangganan minimal 1 tahun akan dikenakan penalti dengan ketentuan sebagai berikut (100% X Biaya Bulanan X Bulan yang belum terpenuhi);',
        ),
        4 => 
        array (
          'p' => 'Selama masa berlakunya Perjanjian ini Pihak Kedua tidak bisa mengajukan perubahan biaya layanan yang berjalan sampai dengan masa kontrak Perjanjian ini berakhir. Terkecuali adanya permohonan upgrade layanan;',
        ),
        5 => 
        array (
          'p' => 'Apabila ada hal-hal  yang belum diatur dalam Kontrak ini. Maka hal-hal tersebut akan diatur dan ditetapkan kemudian secara tertulis tertuang dalam Amandemen dan atau Addendum dengan tetap memperhatikan ketentuan-ketentuan dan peraturan intern PIHAK PERTAMA dan hukum yang berlaku di Indonesia.',
        ),
      ),
    ),
    11 => 
    array (
      'judul' => 'Kerahasiaan',
      'text' => 'Para Pihak sepakat bahwa seluruh isi Perjanjian ini harus diperlakukan secara rahasia. Oleh karena itu, Para Pihak sepakat untuk merahasiakan semua data, dokumen, catatan atau informasi yang diterima oleh salah satu Pihak dari Pihak lainnya sehubungan dengan pelaksanaan Perjanjian ini dan tidak akan diberitahukan kepada pihak ketiga tanpa terlebih dahulu mendapatkan persetujuan tertulis dari Pihak lainnya.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Para Pihak sepakat bahwa seluruh isi Perjanjian ini harus diperlakukan secara rahasia. Oleh karena itu, Para Pihak sepakat untuk merahasiakan semua data, dokumen, catatan atau informasi yang diterima oleh salah satu Pihak dari Pihak lainnya sehubungan dengan pelaksanaan Perjanjian ini dan tidak akan diberitahukan kepada pihak ketiga tanpa terlebih dahulu mendapatkan persetujuan tertulis dari Pihak lainnya.',
        ),
      ),
    ),
    12 => 
    array (
      'judul' => 'Force Majeure',
      'text' => 'Keadaan Memaksa (Force Majeure) adalah setiap peristiwa di luar kendali dan kemampuan wajar PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, termasuk namun tidak terbatas pada bencana alam, kebakaran, perang, kerusuhan, wabah penyakit, pemogokan, gangguan jaringan berskala besar, kegagalan sistem di luar kendali PARA PIHAK, kebijakan Pemerintah, atau peristiwa lain yang sejenis.

PIHAK yang mengalami Keadaan Memaksa wajib memberitahukan secara tertulis kepada pihak lainnya paling lambat 14 (empat belas) Hari Kalender sejak diketahui atau seharusnya diketahui terjadinya Keadaan Memaksa, disertai penjelasan mengenai dampak terhadap pelaksanaan Perjanjian.

Selama Keadaan Memaksa berlangsung, kewajiban PARA PIHAK yang terdampak ditangguhkan sepanjang tidak dapat dilaksanakan akibat Keadaan Memaksa. Penangguhan tersebut tidak menghapus kewajiban yang telah timbul sebelum terjadinya Keadaan Memaksa.

PARA PIHAK wajib melakukan upaya yang wajar untuk mengurangi dampak Keadaan Memaksa dan melanjutkan pelaksanaan Perjanjian segera setelah keadaan tersebut berakhir.

Apabila Keadaan Memaksa berlangsung lebih dari 90 (sembilan puluh) Hari Kalender secara berturut-turut dan PARA PIHAK tidak mencapai kesepakatan mengenai kelanjutan Perjanjian, masing-masing pihak berhak mengakhiri Perjanjian dengan pemberitahuan tertulis tanpa dikenakan penalti, dengan tetap menyelesaikan seluruh hak dan kewajiban yang telah timbul sebelum tanggal efektif pengakhiran.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Keadaan Memaksa (Force Majeure) adalah setiap peristiwa di luar kendali dan kemampuan wajar PARA PIHAK yang secara langsung mengakibatkan sebagian atau seluruh kewajiban dalam Perjanjian ini tidak dapat dilaksanakan, termasuk namun tidak terbatas pada bencana alam, kebakaran, perang, kerusuhan, wabah penyakit, pemogokan, gangguan jaringan berskala besar, kegagalan sistem di luar kendali PARA PIHAK, kebijakan Pemerintah, atau peristiwa lain yang sejenis.',
        ),
        1 => 
        array (
          'p' => 'PIHAK yang mengalami Keadaan Memaksa wajib memberitahukan secara tertulis kepada pihak lainnya paling lambat 14 (empat belas) Hari Kalender sejak diketahui atau seharusnya diketahui terjadinya Keadaan Memaksa, disertai penjelasan mengenai dampak terhadap pelaksanaan Perjanjian.',
        ),
        2 => 
        array (
          'p' => 'Selama Keadaan Memaksa berlangsung, kewajiban PARA PIHAK yang terdampak ditangguhkan sepanjang tidak dapat dilaksanakan akibat Keadaan Memaksa. Penangguhan tersebut tidak menghapus kewajiban yang telah timbul sebelum terjadinya Keadaan Memaksa.',
        ),
        3 => 
        array (
          'p' => 'PARA PIHAK wajib melakukan upaya yang wajar untuk mengurangi dampak Keadaan Memaksa dan melanjutkan pelaksanaan Perjanjian segera setelah keadaan tersebut berakhir.',
        ),
        4 => 
        array (
          'p' => 'Apabila Keadaan Memaksa berlangsung lebih dari 90 (sembilan puluh) Hari Kalender secara berturut-turut dan PARA PIHAK tidak mencapai kesepakatan mengenai kelanjutan Perjanjian, masing-masing pihak berhak mengakhiri Perjanjian dengan pemberitahuan tertulis tanpa dikenakan penalti, dengan tetap menyelesaikan seluruh hak dan kewajiban yang telah timbul sebelum tanggal efektif pengakhiran.',
        ),
      ),
    ),
    13 => 
    array (
      'judul' => 'Penyelesaian Sengketa',
      'text' => 'Apabila timbul perselisihan, sengketa, atau perbedaan penafsiran yang berkaitan dengan pelaksanaan maupun pelanggaran atas PERJANJIAN ini, PARA PIHAK sepakat untuk terlebih dahulu menyelesaikannya secara musyawarah untuk mufakat;

Pihak yang merasa dirugikan wajib menyampaikan pemberitahuan tertulis kepada pihak lainnya mengenai adanya sengketa dimaksud. Sejak diterimanya pemberitahuan tersebut, PARA PIHAK wajib melakukan musyawarah untuk mencapai penyelesaian dalam jangka waktu paling lama 30 (tiga puluh) hari kalender;

Selama proses penyelesaian sengketa berlangsung, PARA PIHAK tetap berkewajiban melaksanakan ketentuan-ketentuan dalam PERJANJIAN ini sepanjang pelaksanaannya tidak berkaitan langsung dengan pokok sengketa yang sedang diperselisihkan;

Apabila dalam jangka waktu sebagaimana dimaksud pada ayat (2) PARA PIHAK tidak berhasil mencapai kesepakatan, maka PARA PIHAK sepakat untuk menyelesaikan sengketa tersebut melalui jalur hukum pada Pengadilan Negeri Cirebon;

PARA PIHAK dengan ini memilih domisili hukum yang tetap dan tidak berubah di wilayah hukum Pengadilan Negeri Cirebon untuk segala akibat hukum yang timbul dari pelaksanaan maupun penafsiran PERJANJIAN ini.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Apabila timbul perselisihan, sengketa, atau perbedaan penafsiran yang berkaitan dengan pelaksanaan maupun pelanggaran atas PERJANJIAN ini, PARA PIHAK sepakat untuk terlebih dahulu menyelesaikannya secara musyawarah untuk mufakat;',
        ),
        1 => 
        array (
          'p' => 'Pihak yang merasa dirugikan wajib menyampaikan pemberitahuan tertulis kepada pihak lainnya mengenai adanya sengketa dimaksud. Sejak diterimanya pemberitahuan tersebut, PARA PIHAK wajib melakukan musyawarah untuk mencapai penyelesaian dalam jangka waktu paling lama 30 (tiga puluh) hari kalender;',
        ),
        2 => 
        array (
          'p' => 'Selama proses penyelesaian sengketa berlangsung, PARA PIHAK tetap berkewajiban melaksanakan ketentuan-ketentuan dalam PERJANJIAN ini sepanjang pelaksanaannya tidak berkaitan langsung dengan pokok sengketa yang sedang diperselisihkan;',
        ),
        3 => 
        array (
          'p' => 'Apabila dalam jangka waktu sebagaimana dimaksud pada ayat (2) PARA PIHAK tidak berhasil mencapai kesepakatan, maka PARA PIHAK sepakat untuk menyelesaikan sengketa tersebut melalui jalur hukum pada Pengadilan Negeri Cirebon;',
        ),
        4 => 
        array (
          'p' => 'PARA PIHAK dengan ini memilih domisili hukum yang tetap dan tidak berubah di wilayah hukum Pengadilan Negeri Cirebon untuk segala akibat hukum yang timbul dari pelaksanaan maupun penafsiran PERJANJIAN ini.',
        ),
      ),
    ),
    14 => 
    array (
      'judul' => 'Lain-Lain',
      'text' => 'Perjanjian ini hanya dapat ditambah, diubah, dimodifikasi, atau disesuaikan berdasarkan kesepakatan PARA PIHAK yang dibuat secara tertulis dan ditandatangani oleh PARA PIHAK;

Seluruh Lampiran dalam Perjanjian ini merupakan satu kesatuan yang tidak terpisahkan dan mempunyai kekuatan hukum yang sama dengan Perjanjian ini. Setiap Lampiran baru yang diterbitkan kemudian hari tunduk pada ketentuan Perjanjian ini sepanjang tidak diatur lain secara tegas;

Para pihak menjamin bahwa penandatangan Perjanjian ini dan/atau Lampiran-Lampirannya adalah pihak yang sah dan berwenang secara hukum untuk mengikatkan diri dan/atau mewakili perusahaan masing-masing, baik berdasarkan anggaran dasar, keputusan organ perusahaan yang berwenang, maupun surat kuasa yang sah;

Perjanjian ini dibuat dan ditandatangani dalam rangkap 2 (dua) asli, masing-masing bermeterai cukup dan mempunyai kekuatan hukum yang sama. Perjanjian ini dibuat dengan itikad baik untuk dilaksanakan oleh PARA PIHAK. Dalam hal ditandatangani secara elektronik, PARA PIHAK sepakat bahwa dokumen elektronik memiliki kekuatan hukum yang sah sesuai peraturan perundang-undangan yang berlaku.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'Perjanjian ini hanya dapat ditambah, diubah, dimodifikasi, atau disesuaikan berdasarkan kesepakatan PARA PIHAK yang dibuat secara tertulis dan ditandatangani oleh PARA PIHAK;',
        ),
        1 => 
        array (
          'p' => 'Seluruh Lampiran dalam Perjanjian ini merupakan satu kesatuan yang tidak terpisahkan dan mempunyai kekuatan hukum yang sama dengan Perjanjian ini. Setiap Lampiran baru yang diterbitkan kemudian hari tunduk pada ketentuan Perjanjian ini sepanjang tidak diatur lain secara tegas;',
        ),
        2 => 
        array (
          'p' => 'Para pihak menjamin bahwa penandatangan Perjanjian ini dan/atau Lampiran-Lampirannya adalah pihak yang sah dan berwenang secara hukum untuk mengikatkan diri dan/atau mewakili perusahaan masing-masing, baik berdasarkan anggaran dasar, keputusan organ perusahaan yang berwenang, maupun surat kuasa yang sah;',
        ),
        3 => 
        array (
          'p' => 'Perjanjian ini dibuat dan ditandatangani dalam rangkap 2 (dua) asli, masing-masing bermeterai cukup dan mempunyai kekuatan hukum yang sama. Perjanjian ini dibuat dengan itikad baik untuk dilaksanakan oleh PARA PIHAK. Dalam hal ditandatangani secara elektronik, PARA PIHAK sepakat bahwa dokumen elektronik memiliki kekuatan hukum yang sah sesuai peraturan perundang-undangan yang berlaku.',
        ),
      ),
    ),
  ),
  'tutup' => 'PIHAK PERTAMA

PT Bina Informatika Solusi

PIHAK KEDUA

PT Dinar Wahana Gemilang

Ageng Bagja Priyadi, S.T.,M.Kom

Direktur

Wildan Arief Santika Budi

Direktur',
  'tutupBlocks' => 
  array (
    0 => 
    array (
      'table' => 
      array (
        'rows' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'c' => 
              array (
                0 => 'PIHAK PERTAMA',
                1 => 'PT Bina Informatika Solusi',
              ),
              's' => 1,
            ),
            1 => 
            array (
              'c' => 
              array (
                0 => 'PIHAK KEDUA',
                1 => 'PT Dinar Wahana Gemilang',
              ),
              's' => 1,
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'c' => 
              array (
                0 => 'Ageng Bagja Priyadi, S.T.,M.Kom',
                1 => 'Direktur',
              ),
              's' => 1,
            ),
            1 => 
            array (
              'c' => 
              array (
                0 => 'Wildan Arief Santika Budi',
                1 => 'Direktur',
              ),
              's' => 1,
            ),
          ),
        ),
        'bordered' => false,
        'head' => true,
      ),
    ),
  ),
  'lampiran' => 
  array (
    0 => 
    array (
      'judul' => 'LAMPIRAN BERLANGGANAN JASA I',
      'text' => 'IDENTITAS PELANGGAN

DETAIL & KOMERSIAL LAYANAN

* Biaya tercantum belum termasuk PPn

SERVICE LEVEL AGREEMENT (SLA)

SLA Avaibility = 99,5%

PIHAK PERTAMA memberikan jaminan Layanan yang tercantum dalam Service Order Form dengan rumusan sebagai berikut:

Apabila Layanan tidak sesuai dengan yang disepakati dalam Service Order Form, maka akan berlaku rumusan Restitusi sebagai berikut:

D. 	KETENTUAN

Lampiran ini merupakan bagian yang tidak terpisahkan dan tunduk pada seluruh ketentuan Kontrak Payung Nomor:238/FBT/J.M/VI/2026.',
      'blocks' => 
      array (
        0 => 
        array (
          'p' => 'IDENTITAS PELANGGAN',
        ),
        1 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama dan Alamat Pelanggan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'NPWP',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
              2 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nomor Telepon Pelanggan /',
                    1 => 'Penanggungjawab',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
              3 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nomor Handphone Pelanggan /',
                    1 => 'Penanggungjawab',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
              4 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama Penanggungjawab Administrasi/Keuangan',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
              5 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Nama Penanggungjawab Teknisi',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
        2 => 
        array (
          'p' => 'DETAIL & KOMERSIAL LAYANAN',
        ),
        3 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'No',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Lokasi Layanan',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rute Metro',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => 'Bandwidth',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Bulanan',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => 'Biaya Instalasi',
                  ),
                  's' => 1,
                ),
                6 => 
                array (
                  'c' => 
                  array (
                    0 => 'Masa Berlaku',
                  ),
                  's' => 1,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '1',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Bandung',
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                    0 => 'APJII Cyber Jakarta <> APJII Jabar 8 BBU Bandung',
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                    0 => '1 (satu) Gbps',
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp5.000.000',
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                    0 => 'Rp2.000.000',
                  ),
                  's' => 1,
                ),
                6 => 
                array (
                  'c' => 
                  array (
                    0 => '1 Desember 2025 – 30 November 2026',
                  ),
                  's' => 1,
                ),
              ),
              2 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '2',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                2 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                3 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                4 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                5 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
                6 => 
                array (
                  'c' => 
                  array (
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => true,
          ),
        ),
        4 => 
        array (
          'p' => '* Biaya tercantum belum termasuk PPn',
        ),
        5 => 
        array (
          'p' => 'SERVICE LEVEL AGREEMENT (SLA)',
        ),
        6 => 
        array (
          'p' => 'SLA Avaibility = 99,5%',
        ),
        7 => 
        array (
          'p' => 'PIHAK PERTAMA memberikan jaminan Layanan yang tercantum dalam Service Order Form dengan rumusan sebagai berikut:',
        ),
        8 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Service Level Guarantee(%): (usage minutes per month – down time) x 100',
                    1 => 'Total minutes per month',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
        9 => 
        array (
          'p' => 'Apabila Layanan tidak sesuai dengan yang disepakati dalam Service Order Form, maka akan berlaku rumusan Restitusi sebagai berikut:',
        ),
        10 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => '[Agreed Service Level – Actual Service Level] x Monthly Cost.',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => true,
            'head' => false,
          ),
        ),
        11 => 
        array (
          'p' => 'D. 	KETENTUAN',
        ),
        12 => 
        array (
          'p' => 'Lampiran ini merupakan bagian yang tidak terpisahkan dan tunduk pada seluruh ketentuan Kontrak Payung Nomor:238/FBT/J.M/VI/2026.',
        ),
        13 => 
        array (
          'table' => 
          array (
            'rows' => 
            array (
              0 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'PIHAK PERTAMA',
                    1 => 'PT Bina Informatika Solusi',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'PIHAK KEDUA',
                    1 => 'PT Dinar Wahana Gemilang',
                  ),
                  's' => 1,
                ),
              ),
              1 => 
              array (
                0 => 
                array (
                  'c' => 
                  array (
                    0 => 'Ageng Bagja Priyadi, S.T.,M.Kom',
                    1 => 'Direktur',
                  ),
                  's' => 1,
                ),
                1 => 
                array (
                  'c' => 
                  array (
                    0 => 'Wildan Arief Santika Budi',
                    1 => 'Direktur',
                  ),
                  's' => 1,
                ),
              ),
            ),
            'bordered' => false,
            'head' => true,
          ),
        ),
      ),
    ),
  ),
),
            ],
        ];
    }

    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
