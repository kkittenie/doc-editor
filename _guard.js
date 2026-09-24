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
