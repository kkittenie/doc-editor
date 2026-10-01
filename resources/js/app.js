import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import './editor';
// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { Indonesian } from 'flatpickr/dist/l10n/id';
// FullCalendar
import { Calendar } from '@fullcalendar/core';
//sweetalert2
import Swal from 'sweetalert2';


window.Swal = Swal;
window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

// Kalender berbahasa Indonesia (nama bulan & hari).
//
// Berkas l10n/id.js mendaftarkan lokalenya ke `flatpickr.l10ns.id` (baris 54),
// jadi kita cukup meneruskannya ke localize(). Flatpickr TIDAK punya API
// `locales` maupun `addLocale` — memanggilnya akan melempar TypeError di awal
// modul, sehingga Alpine.start() tidak pernah jalan dan preloader berputar
// tanpa henti. Karena itu dibungkus typeof + try/catch.
try {
    if (typeof flatpickr.localize === 'function' && flatpickr.l10ns && flatpickr.l10ns.id) {
        flatpickr.localize(flatpickr.l10ns.id);
    }
} catch (e) {
    console.warn('Locale Indonesia gagal dimuat:', e);
}

Alpine.start();

// Catatan: import dinamis untuk map/chart/calendar (js/components/*) sudah
// dihapus — semua halaman yang memakainya (dashboard e-commerce, halaman
// chart, halaman kalender) adalah halaman demo TailwindAdmin yang tidak pernah
// diroute, jadi tidak ada lagi elemen #mapOne/#chartOne/#calendar di DOM.
