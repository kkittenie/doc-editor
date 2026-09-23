// ---- Guard: blot tabel (core & table-better) adalah Container TANPA
// formats(); saat HTML berisi struktur tabel tak didukung (tabel di dalam
// sel / campuran blot core+table-better) optimize() pihak ketiga memanggil
// child.formats() -> "i.formats is not a function" dan SELURUH region gagal
// dipasang (error berulang tiap paginasi/mirror). Wrapper ini membuat
// optimize tahan-gagal agar editor tetap hidup.
const __guardBlotOptimize = (blotName) => {
    const BlotClass = Quill.import(blotName);
    if (!BlotClass || !BlotClass.prototype || typeof BlotClass.prototype.optimize !== 'function') return;
    if (BlotClass.prototype.__docQuillGuard) return;
    const __originalOptimize = BlotClass.prototype.optimize;
    BlotClass.prototype.optimize = function (context) {
        try {
            return __originalOptimize.call(this, context);
        } catch (err) {
            return undefined; // struktur blot campuran - jangan matikan editor
        }
    };
    BlotClass.prototype.__docQuillGuard = true;
};
['formats/table-cell', 'formats/table-th', 'formats/table-row', 'formats/table',
 'formats/table-container', 'formats/table-body', 'formats/list-container']
    .forEach(__guardBlotOptimize);
