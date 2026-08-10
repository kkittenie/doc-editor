<?php

namespace App\Data;

class DocumentTemplates
{
    public static function all(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | 1. PERJANJIAN KERJA SAMA
            |--------------------------------------------------------------------------
            */

            [
                'id' => 'perjanjian-kerja-sama',

                'name' => 'Perjanjian Kerja Sama (PKS)',

                'category' => 'pks',

                'description' => 'Format Perjanjian Kerja Sama antara dua pihak dengan struktur pasal dan ketentuan yang dapat disesuaikan.',

                'header' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => '',
                    'kopKontrak' => 'PERJANJIAN KERJA SAMA',
                    'nomorSurat' => '',
                    'perihalSurat' => 'Perjanjian Kerja Sama',
                    'tanggalSurat' => '',
                    'sifatSurat' => 'Penting',
                ],

                'body' => [
                    'tujuanSurat' => '',
                    'menimbang' => '',
                    'mengingat' => '',

                    'isiPasal1' =>
                        "PASAL 1\nKETENTUAN UMUM\n\n" .
                        "Para Pihak sepakat untuk melaksanakan kerja sama sesuai dengan ketentuan yang telah disepakati bersama.",

                    'isiPasal2' =>
                        "PASAL 2\nHAK DAN KEWAJIBAN PARA PIHAK\n\n" .
                        "Masing-masing pihak memiliki hak dan kewajiban sebagaimana diatur dalam perjanjian ini.",
                ],

                'footer' => [
                    'kotaTtd' => '',
                    'jabatanPenandatangan' => '',
                    'namaPenandatangan' => '',
                    'nipPenandatangan' => '',
                    'tembusan' => '',
                ],
            ],


            /*
            |--------------------------------------------------------------------------
            | 2. KONTRAK KERJA
            |--------------------------------------------------------------------------
            */

            [
                'id' => 'kontrak-kerja',

                'name' => 'Kontrak Kerja',

                'category' => 'kontrak',

                'description' => 'Template perjanjian kerja antara perusahaan dan pekerja dengan struktur hak, kewajiban, dan ketentuan kerja.',

                'header' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => '',
                    'kopKontrak' => 'PERJANJIAN KERJA',
                    'nomorSurat' => '',
                    'perihalSurat' => 'Perjanjian Kerja',
                    'tanggalSurat' => '',
                    'sifatSurat' => 'Penting',
                ],

                'body' => [
                    'tujuanSurat' => '',

                    'menimbang' =>
                        "Bahwa perusahaan membutuhkan tenaga kerja untuk melaksanakan pekerjaan sesuai dengan jabatan dan tanggung jawab yang telah ditentukan.",

                    'mengingat' =>
                        "Bahwa Para Pihak sepakat untuk mengikatkan diri dalam hubungan kerja berdasarkan ketentuan yang berlaku.",

                    'isiPasal1' =>
                        "PASAL 1\nJABATAN DAN RUANG LINGKUP PEKERJAAN\n\n" .
                        "Pekerja akan melaksanakan pekerjaan sesuai dengan jabatan, tugas, dan tanggung jawab yang telah disepakati oleh Para Pihak.",

                    'isiPasal2' =>
                        "PASAL 2\nHAK DAN KEWAJIBAN\n\n" .
                        "Perusahaan dan Pekerja memiliki hak dan kewajiban yang wajib dilaksanakan sesuai dengan ketentuan dalam perjanjian kerja ini.",
                ],

                'footer' => [
                    'kotaTtd' => '',
                    'jabatanPenandatangan' => '',
                    'namaPenandatangan' => '',
                    'nipPenandatangan' => '',
                    'tembusan' => '',
                ],
            ],


            /*
            |--------------------------------------------------------------------------
            | 3. SURAT KUASA
            |--------------------------------------------------------------------------
            */

            [
                'id' => 'surat-kuasa',

                'name' => 'Surat Kuasa',

                'category' => 'surat',

                'description' => 'Template surat kuasa resmi untuk memberikan kewenangan kepada pihak lain dalam suatu urusan tertentu.',

                'header' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => '',
                    'kopKontrak' => 'SURAT KUASA',
                    'nomorSurat' => '',
                    'perihalSurat' => 'Surat Kuasa',
                    'tanggalSurat' => '',
                    'sifatSurat' => 'Biasa',
                ],

                'body' => [
                    'tujuanSurat' => '',

                    'menimbang' =>
                        "Yang bertanda tangan di bawah ini menerangkan bahwa pemberi kuasa memberikan kewenangan kepada penerima kuasa untuk mewakili kepentingan sebagaimana tercantum dalam surat kuasa ini.",

                    'mengingat' => '',

                    'isiPasal1' =>
                        "PEMBERI KUASA\n\n" .
                        "Nama: \n" .
                        "Jabatan: \n" .
                        "Alamat: \n\n" .
                        "Selanjutnya disebut sebagai PEMBERI KUASA.",

                    'isiPasal2' =>
                        "PENERIMA KUASA\n\n" .
                        "Nama: \n" .
                        "Jabatan: \n" .
                        "Alamat: \n\n" .
                        "Selanjutnya disebut sebagai PENERIMA KUASA.",
                ],

                'footer' => [
                    'kotaTtd' => '',
                    'jabatanPenandatangan' => '',
                    'namaPenandatangan' => '',
                    'nipPenandatangan' => '',
                    'tembusan' => '',
                ],
            ],


            /*
            |--------------------------------------------------------------------------
            | 4. SURAT PERNYATAAN
            |--------------------------------------------------------------------------
            */

            [
                'id' => 'surat-pernyataan',

                'name' => 'Surat Pernyataan',

                'category' => 'surat',

                'description' => 'Template surat pernyataan resmi untuk kebutuhan administrasi, perusahaan, maupun keperluan legal lainnya.',

                'header' => [
                    'kopInstansi' => 'PT NUSANTARA CITRA MEDIA TBBK',
                    'kopAlamat' => '',
                    'kopKontrak' => 'SURAT PERNYATAAN',
                    'nomorSurat' => '',
                    'perihalSurat' => 'Surat Pernyataan',
                    'tanggalSurat' => '',
                    'sifatSurat' => 'Biasa',
                ],

                'body' => [
                    'tujuanSurat' => '',

                    'menimbang' =>
                        "Yang bertanda tangan di bawah ini menyatakan bahwa:",

                    'mengingat' => '',

                    'isiPasal1' =>
                        "IDENTITAS\n\n" .
                        "Nama: \n" .
                        "Jabatan: \n" .
                        "Alamat: \n\n" .
                        "Dengan ini menyatakan bahwa:",

                    'isiPasal2' =>
                        "PERNYATAAN\n\n" .
                        "1. Bahwa seluruh informasi yang diberikan adalah benar dan dapat dipertanggungjawabkan.\n\n" .
                        "2. Pernyataan ini dibuat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana mestinya.",
                ],

                'footer' => [
                    'kotaTtd' => '',
                    'jabatanPenandatangan' => '',
                    'namaPenandatangan' => '',
                    'nipPenandatangan' => '',
                    'tembusan' => '',
                ],
            ],

        ];
    }


    public static function find(string $id): ?array
    {
        foreach (self::all() as $template) {

            if ($template['id'] === $id) {
                return $template;
            }

        }

        return null;
    }
}
