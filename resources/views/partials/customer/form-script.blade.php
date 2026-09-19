<script>
    function customerForm() {
        return {
            activeDate: @js(old('active_date', now()->toDateString())),
            activeMonths: @js((string) old('active_months', 12)),
            barangRows: @js(old('barang', [])),
            serviceRows: @js(old('services', [])),

            // Tanggal Selesai otomatis: Aktif + Masa (bulan), clamp akhir
            // bulan agar sama dengan hitungan server (addMonthsNoOverflow).
            get finishDate() {
                if (!this.activeDate || !this.activeMonths) return '';

                const parts = String(this.activeDate).split('-').map(Number);
                const year = parts[0];
                const month = parts[1];
                const day = parts[2];

                if (!year || !month || !day) return '';

                const months = parseInt(this.activeMonths, 10);
                if (!months || months < 1) return '';

                const target = (month - 1) + months;
                const targetYear = year + Math.floor(target / 12);
                const targetMonth = ((target % 12) + 12) % 12;
                const lastDay = new Date(Date.UTC(targetYear, targetMonth + 1, 0)).getUTCDate();
                const safeDay = Math.min(day, lastDay);

                return targetYear + '-'
                    + String(targetMonth + 1).padStart(2, '0') + '-'
                    + String(safeDay).padStart(2, '0');
            },

            get finishDateLabel() {
                if (!this.finishDate) return '';

                const parts = this.finishDate.split('-');
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

                return parseInt(parts[2], 10) + ' ' + monthNames[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
            },

            addBarang() {
                if (this.barangRows.length >= 20) return;
                this.barangRows.push({ name: '', quantity: 1, price: null, price_type: 'one_time', ownership: 'dibeli' });
            },

            removeBarang(index) {
                this.barangRows.splice(index, 1);
            },

            addService() {
                if (this.serviceRows.length >= 20) return;
                this.serviceRows.push({ name: '', price: null, price_type: 'one_time' });
            },

            removeService(index) {
                this.serviceRows.splice(index, 1);
            },
        };
    }
</script>
