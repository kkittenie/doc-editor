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

try {
    if (typeof flatpickr.localize === 'function' && flatpickr.l10ns && flatpickr.l10ns.id) {
        flatpickr.localize(flatpickr.l10ns.id);
    }
} catch (e) {
    console.warn('Locale Indonesia gagal dimuat:', e);
}

Alpine.start();