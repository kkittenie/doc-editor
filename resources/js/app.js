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

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    // Chart imports
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then(module => module.initChartSix());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then(module => module.initChartEight());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then(module => module.initChartThirteen());
    }

    // Calendar init
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }
});
