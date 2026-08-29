
// QUILL EDITOR — pengganti TinyMCE
// Satu instance Quill per region kertas
// (.doc-sheet-header / body / footer),
// dengan SATU toolbar bersama di atas.


import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// ---- Font family: pakai inline style (bukan class), seperti TinyMCE ----
const FontAttributor = Quill.import('attributors/style/font');
FontAttributor.whitelist = [
    'Arial', 'Georgia', 'Times New Roman', 'Courier New', 'Verdana',
];
Quill.register(FontAttributor, true);

// ---- Ukuran font: nilai px nyata ----
const SizeAttributor = Quill.import('attributors/style/size');
SizeAttributor.whitelist = null;
Quill.register(SizeAttributor, true);


const Parchment = Quill.import('parchment');
const LineHeightStyle = new Parchment.StyleAttributor('lineheight', 'line-height', {
    scope: Parchment.Scope.BLOCK,
    whitelist: ['1', '1.15', '1.5', '2', '2.5'],
});
Quill.register(LineHeightStyle, true);

// ---- Penanda "list ini pakai huruf (a, b, c)", terpisah dari nesting/indent ----
const ListStyleAttributor = new Parchment.ClassAttributor('liststyle', 'ql-liststyle', {
    scope: Parchment.Scope.BLOCK,
    whitelist: ['alpha'],
});
Quill.register(ListStyleAttributor, true);

const BaseImage = Quill.import('formats/image');

class StyledImage extends BaseImage {
    static formats(domNode) {
        const formats = super.formats(domNode);
        const style = domNode.getAttribute('style');
        if (style) formats.style = style;
        return formats;
    }

    format(name, value) {
        if (name === 'style') {
            if (value) this.domNode.setAttribute('style', value);
            else this.domNode.removeAttribute('style');
        } else {
            super.format(name, value);
        }
    }
}

Quill.register(StyledImage, true);

// ---- Garis pemisah kop surat (<hr>) ----
const BlockEmbed = Quill.import('blots/block/embed');

class HrBlot extends BlockEmbed {
    static blotName = 'hr';
    static tagName = 'HR';
}

Quill.register(HrBlot);

// Daftar format yang diizinkan (dipakai semua instance)
const ALLOWED_FORMATS = [
    'header', 'bold', 'italic', 'underline', 'strike',
    'script', 'list', 'align', 'indent',
    'blockquote', 'link', 'image', 'hr',
    'font', 'size', 'color', 'background', 'lineheight', 'liststyle',
    'table',
];

// ---- Upload gambar ke server ----
const uploadImageFile = (file) =>
    new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file, file.name);

        window.axios
            .post('/documents/image', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
            .then((response) => resolve(response.data.url))
            .catch((error) => {
                console.error(error);
                reject(new Error('Gagal mengunggah gambar.'));
            });
    });


// REGISTRY EDITOR (shim agar seluruh sistem
// gambar lama tetap bekerja tanpa TinyMCE)


// Semua root editor yang memasang image tools
let registeredImageToolEditors = [];

// Callback "dokumen berubah" — diisi dari blade (Alpine)
window.__docEditorDirty = null;

const notifyDirty = () => {
    if (typeof window.__docEditorDirty === 'function') {
        window.__docEditorDirty();
    }
};

// Shim dengan API minimal yang dipakai sistem gambar:
// save / fire / nodeChanged / getBody / dom.remove / dom.create
const makeEditorShim = (rootEl) => ({
    rootEl,
    getBody: () => rootEl,
    save() {},
    fire(name) {
        if (name === 'change') notifyDirty();
    },
    nodeChanged() {
        notifyDirty();
    },
    dom: {
        remove: (el) => {
            try { el?.remove?.(); } catch (err) { /* noop */ }
        },
        create: (tag) => document.createElement(tag),
    },
});

const findEditorContaining = (node) => {
    if (!node) return null;
    for (let i = 0; i < registeredImageToolEditors.length; i++) {
        const ed = registeredImageToolEditors[i];
        try {
            if (ed.getBody().contains(node)) return ed;
        } catch (err) {
            // noop
        }
    }
    return null;
};


// IMAGE LAYOUT TOOLS
// (bubble ⚓ + titik resize + posisi depan/belakang teks)


let activeImage = null;
let activeEditor = null;
let bubbleEl = null;
let removeBtnEl = null;
let dragSurfaceEl = null;
let panelEl = null;
let handleEls = [];
let isResizingImage = false;
let resizeCorner = null;
let resizeStartW = 0, resizeStartH = 0;
let resizeStartCursorX = 0, resizeStartCursorY = 0;
let resizeStartLeft = 0, resizeStartTop = 0;
let resizeIsFloating = false;
let resizeAnchorX = 0, resizeAnchorY = 0;      // posisi layar sudut OPOSISI (anchor)
let resizeBaseVecX = 0, resizeBaseVecY = 0;    // vektor handle -> anchor saat mulai
let resizeStartMarginLeft = 0, resizeStartMarginTop = 0;
let watchTimer = null;

// State drag "angkat & jatuhkan" untuk gambar biasa (non-floating)
let flowDragArmed = false;
let isDraggingFlowImage = false;
let flowStartX = 0, flowStartY = 0;
let flowDragOffsetX = 0, flowDragOffsetY = 0;
let flowImgOriginalCssText = '';
let flowImgOriginalParent = null;
let flowImgOriginalNext = null;
let flowDragSourceImg = null;

// State drag bebas untuk gambar floating (behind/front text)
let floatingImg = null;
let floatingRegion = null;
let floatingDragArmed = false;
let isDraggingFloating = false;
let floatStartX = 0, floatStartY = 0;
let floatBaseLeft = 0, floatBaseTop = 0;

const FLOAT_REGION_SELECTOR = '.doc-sheet-body, .doc-sheet-header, .doc-sheet-footer';

const isFloatingImage = (img) => getComputedStyle(img).position === 'absolute';

const getImageLayout = (img) => {
    if (!img) return 'inline';
    if (isFloatingImage(img)) {
        const z = parseInt(getComputedStyle(img).zIndex, 10);
        return z < 0 ? 'behind' : 'front';
    }
    const f = getComputedStyle(img).cssFloat;
    if (f === 'left' || f === 'right') return 'square';
    if (getComputedStyle(img).display === 'block') return 'topbottom';
    return 'inline';
};

// Batas posisi gambar mengikuti KERTAS penuh (.doc-sheet), bukan region-nya —
// jadi gambar bisa ditaruh sampai ke sudut, tepi, area kop, dan footer.
// koordinat masukan/keluaran tetap relatif terhadap region gambar itu sendiri.
const clampPosToSheet = (regionEl, left, top, w, h) => {
    const sheet = regionEl?.closest?.('.doc-sheet');
    if (!sheet) return [Math.max(0, left), Math.max(0, top)];

    const sRect = sheet.getBoundingClientRect();
    const rRect = regionEl.getBoundingClientRect();
    const offX = rRect.left - sRect.left;
    const offY = rRect.top - sRect.top;

    const minX = -offX;
    const minY = -offY;
    const maxX = sRect.width - w - offX;
    const maxY = sRect.height - h - offY;

    const cx = Math.min(Math.max(left, minX), Math.max(minX, maxX));
    const cy = Math.min(Math.max(top, minY), Math.max(minY, maxY));
    return [cx, cy];
};

const removeImageTools = () => {
    clearInterval(watchTimer);
    cancelAnimationFrame(watchTimer || 0);
    watchTimer = null;
    bubbleEl?.remove();
    removeBtnEl?.remove();
    dragSurfaceEl?.remove();
    panelEl?.remove();
    handleEls.forEach((h) => h.remove());
    bubbleEl = null;
    removeBtnEl = null;
    dragSurfaceEl = null;
    panelEl = null;
    handleEls = [];
    activeImage = null;
    activeEditor = null;
};

const positionImageTools = () => {
    if (!activeImage || !activeImage.isConnected) {
        removeImageTools();
        return;
    }

    const rect = activeImage.getBoundingClientRect();

    if (bubbleEl) {
        bubbleEl.style.left = (rect.right - 12) + 'px';
        bubbleEl.style.top = (rect.top - 12) + 'px';
    }

    if (removeBtnEl) {
        removeBtnEl.style.left = (rect.right - 12 - 32) + 'px';
        removeBtnEl.style.top = (rect.top - 12) + 'px';
    }

    // Permukaan drag menutupi area gambar selama mode edit aktif
    if (dragSurfaceEl) {
        dragSurfaceEl.style.left = rect.left + 'px';
        dragSurfaceEl.style.top = rect.top + 'px';
        dragSurfaceEl.style.width = rect.width + 'px';
        dragSurfaceEl.style.height = rect.height + 'px';
    }

    const corners = [
        ['nw', rect.left, rect.top],
        ['ne', rect.right, rect.top],
        ['sw', rect.left, rect.bottom],
        ['se', rect.right, rect.bottom],
    ];

    handleEls.forEach((h, i) => {
        const [, x, y] = corners[i];
        h.style.left = x + 'px';
        h.style.top = y + 'px';
    });
};

// Pastikan region masih punya minimal satu blok untuk menaruh kursor
const ensureRegionHasBlock = (editor, region) => {
    const hasBlock = Array.from(region.children).some((el) =>
        ['P', 'DIV', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'UL', 'OL', 'BLOCKQUOTE']
            .includes(el.nodeName) &&
        !(el.classList && el.classList.contains('doc-signature'))
    );
    if (!hasBlock) {
        const p = editor.dom.create('p');
        p.innerHTML = '<br>';
        region.insertBefore(p, region.firstChild);
    }
};

// Bersihkan paragraf yang jadi kosong setelah gambarnya dipindah jadi floating
const cleanupAfterFloatMove = (editor, oldParent, region) => {
    try {
        if (
            oldParent &&
            oldParent.nodeType === 1 &&
            oldParent !== region &&
            region.contains(oldParent) &&
            !oldParent.matches(FLOAT_REGION_SELECTOR) &&
            !oldParent.querySelector('img') &&
            !(oldParent.textContent || '').trim() &&
            !oldParent.querySelector('table, hr, ul, ol')
        ) {
            editor.dom.remove(oldParent);
        }
    } catch (err) {
        // noop
    }
};

// Kembalikan gambar floating (anak langsung region, di luar editor Quill)
// ke dalam aliran konten editor — dipakai saat berganti ke layout aliran
// teks (inline / persegi / atas-bawah) agar ikut ter-render & tersimpan.
const returnImageToFlow = (editor, img) => {
    const region = img.closest(FLOAT_REGION_SELECTOR);
    const qlEditor = region?.querySelector('.ql-editor');
    if (!qlEditor || qlEditor.contains(img)) return;

    // Selipkan di awal blok pertama; bila editor kosong, buat paragraf baru
    let firstBlock = qlEditor.querySelector('p, h1, h2, h3, h4, h5, h6, li');
    if (!firstBlock) {
        firstBlock = editor.dom.create('p');
        firstBlock.innerHTML = '<br>';
        qlEditor.insertBefore(firstBlock, qlEditor.firstChild);
    }
    firstBlock.insertBefore(img, firstBlock.firstChild);
};

const applyImageLayout = (img, layout) => {
    const editor = activeEditor;
    if (!editor) return;

    // Layout & sisi float SEBELUM direset (dipakai toggle sisi opsi 'square')
    const prevLayout = getImageLayout(img);
    const prevFloat =
        prevLayout === 'square' ? getComputedStyle(img).cssFloat : 'none';

    const oldParent = img.parentElement;

    // Reset semua style layout dulu
    img.style.float = '';
    img.style.display = '';
    img.style.margin = '';
    img.style.position = '';
    img.style.left = '';
    img.style.top = '';
    img.style.zIndex = '';
    img.style.cursor = '';
    img.style.verticalAlign = '';
    img.style.clear = '';
    img.classList.remove('doc-image-behind', 'doc-image-front');

    const region = img.closest(FLOAT_REGION_SELECTOR);

    if ((layout === 'behind' || layout === 'front') && region) {
        // Catat posisi visual gambar SAAT INI relatif terhadap region,
        // supaya gambar tidak "lompat" saat berubah jadi floating.
        const imgRect = img.getBoundingClientRect();
        const regionRect = region.getBoundingClientRect();
        // Posisi bebas di seluruh area kertas — bukan hanya di dalam region
        const [freeLeft, freeTop] = clampPosToSheet(
            region,
            imgRect.left - regionRect.left,
            imgRect.top - regionRect.top,
            imgRect.width,
            imgRect.height
        );
        const left = Math.round(freeLeft);
        const top = Math.round(freeTop);

        // Keluarkan gambar dari paragrafnya -> jadi anak langsung region.
        region.appendChild(img);
        cleanupAfterFloatMove(editor, oldParent, region);
        ensureRegionHasBlock(editor, region);

        img.style.position = 'absolute';
        img.style.left = left + 'px';
        img.style.top = top + 'px';
        img.style.margin = '0';
        img.style.cursor = 'grab';

        if (layout === 'behind') {
            img.style.zIndex = '-1';
            img.classList.add('doc-image-behind');
        } else {
            img.style.zIndex = '20';
            img.classList.add('doc-image-front');
        }
    } else if (layout === 'inline' || layout === 'square' || layout === 'topbottom') {
        // Layout aliran teks: gambar WAJIB kembali ke dalam editor Quill.
        // (bila sebelumnya floating, ia anak langsung region — di luar editor)
        returnImageToFlow(editor, img);

        if (layout === 'inline') {
            // Sejajar dengan teks: mengalir seperti huruf dalam satu baris
            img.style.display = 'inline';
            img.style.verticalAlign = 'middle';
        } else if (layout === 'square') {
            // Persegi — teks mengalir di sampingnya: CSS float kiri/kanan.
            // Klik ulang opsi ini saat sudah persegi untuk pindah sisi.
            const side =
                prevLayout === 'square'
                    ? prevFloat === 'left'
                        ? 'right'
                        : 'left'
                    : 'left';
            img.style.float = side;
            img.style.margin =
                side === 'left' ? '4px 14px 8px 0' : '4px 0 8px 14px';
        } else {
            // Atas dan bawah: blok penuh — teks hanya di atas & bawah gambar
            img.style.display = 'block';
            img.style.clear = 'both';
        }
    }

    editor.save();
    editor.fire('change');
    editor.nodeChanged();

    requestAnimationFrame(positionImageTools);
};

const buildPanel = () => {
    panelEl?.remove();

    const options = [
        { key: 'inline', label: 'Sejajar dengan Teks' },
        { key: 'square', label: 'Persegi — Teks di Samping' },
        { key: 'topbottom', label: 'Atas dan Bawah' },
        { key: 'behind', label: 'Di Belakang Teks' },
        { key: 'front', label: 'Di Depan Teks' },
    ];

    const currentLayout = getImageLayout(activeImage);

    panelEl = document.createElement('div');
    panelEl.style.cssText =
        'position:fixed;z-index:999999;width:230px;background:#fff;border:1px solid #d6d3cc;' +
        'border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:8px;font-family:Arial,sans-serif;';

    panelEl.innerHTML =
        '<div style="font-size:12px;font-weight:700;color:#1B2A4A;margin-bottom:6px;padding:0 4px;">Atur Posisi Gambar</div>' +
        options.map((o) => {
            const active = o.key === currentLayout;
            return '<button type="button" data-layout="' + o.key + '"' + (active ? ' data-active="1"' : '') +
                ' style="display:block;width:100%;text-align:left;padding:7px 8px;border-radius:6px;' +
                (active
                    ? 'background:#1B2A4A;color:#fff;font-weight:600;'
                    : 'background:transparent;color:#1B2A4A;') +
                'border:none;cursor:pointer;font-size:12.5px;">' +
                (active ? '✓ ' : '') + o.label + '</button>';
        }).join('');

    document.body.appendChild(panelEl);

    const bubbleRect = bubbleEl.getBoundingClientRect();
    panelEl.style.left = Math.max(8, bubbleRect.left - 210) + 'px';
    panelEl.style.top = (bubbleRect.bottom + 6) + 'px';

    panelEl.querySelectorAll('button[data-layout]').forEach((btn) => {
        btn.addEventListener('mouseenter', () => {
            if (!btn.dataset.active) btn.style.background = '#f5f2eb';
        });
        btn.addEventListener('mouseleave', () => {
            if (!btn.dataset.active) btn.style.background = 'transparent';
        });
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            applyImageLayout(activeImage, btn.dataset.layout);
            panelEl?.remove();
            panelEl = null;
        });
    });
};

const startImageResize = (e, corner) => {
    e.preventDefault();
    e.stopPropagation();
    if (!activeImage || e.button !== 0) return;

    isResizingImage = true;
    resizeCorner = corner;
    resizeStartW = activeImage.offsetWidth;
    resizeStartH = activeImage.offsetHeight;
    resizeIsFloating = isFloatingImage(activeImage);
    resizeStartLeft = parseFloat(activeImage.style.left) || 0;
    resizeStartTop = parseFloat(activeImage.style.top) || 0;
    resizeStartCursorX = e.clientX;
    resizeStartCursorY = e.clientY;

    // Anchor = sudut OPOSISI dari handle yang ditarik -> titik ini yang tetap diam,
    // gambar "tumbuh menuju" handle sesuai posisi kursor.
    const rect = activeImage.getBoundingClientRect();
    const cornerPts = {
        nw: [rect.left, rect.top],
        ne: [rect.right, rect.top],
        sw: [rect.left, rect.bottom],
        se: [rect.right, rect.bottom],
    };
    const opposite = { nw: 'se', ne: 'sw', sw: 'ne', se: 'nw' }[corner];
    resizeAnchorX = cornerPts[opposite][0];
    resizeAnchorY = cornerPts[opposite][1];
    resizeBaseVecX = cornerPts[corner][0] - resizeAnchorX;
    resizeBaseVecY = cornerPts[corner][1] - resizeAnchorY;

    // Untuk gambar non-floating: kompensasi margin agar anchor tidak bergeser.
    const cs = getComputedStyle(activeImage);
    resizeStartMarginLeft = parseFloat(cs.marginLeft) || 0;
    resizeStartMarginTop = parseFloat(cs.marginTop) || 0;

    document.body.style.userSelect = 'none';
};

const showImageTools = (editor, img) => {
    if (activeImage === img) return;
    removeImageTools();

    activeImage = img;
    activeEditor = editor;

    bubbleEl = document.createElement('div');
    bubbleEl.innerHTML = '⚓';
    bubbleEl.title = 'Atur Posisi Gambar';
    bubbleEl.style.cssText =
        'position:fixed;z-index:999999;width:26px;height:26px;border-radius:6px;background:#1B2A4A;' +
        'color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;' +
        'box-shadow:0 2px 6px rgba(0,0,0,.25);font-size:13px;';
    document.body.appendChild(bubbleEl);

    bubbleEl.addEventListener('click', (e) => {
        e.stopPropagation();
        panelEl ? (panelEl.remove(), panelEl = null) : buildPanel();
    });

    // Tombol hapus cepat di samping anchor
    removeBtnEl = document.createElement('div');
    removeBtnEl.innerHTML = '✕';
    removeBtnEl.title = 'Hapus gambar';
    removeBtnEl.style.cssText =
        'position:fixed;z-index:999999;width:26px;height:26px;border-radius:6px;background:#dc2626;' +
        'color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;' +
        'box-shadow:0 2px 6px rgba(0,0,0,.25);font-size:12px;';
    document.body.appendChild(removeBtnEl);

    removeBtnEl.addEventListener('click', (e) => {
        e.stopPropagation();
        if (!activeImage || !activeEditor) return;
        const ed = activeEditor;
        const image = activeImage;
        removeImageTools();
        ed.dom.remove(image);
        ed.save();
        ed.fire('change');
        ed.nodeChanged();
    });

    handleEls = ['nw', 'ne', 'sw', 'se'].map((corner) => {
        const h = document.createElement('div');
        h.style.cssText =
            'position:fixed;z-index:999999;width:10px;height:10px;background:#fff;border:2px solid #1B2A4A;' +
            `border-radius:50%;cursor:${corner === 'nw' || corner === 'se' ? 'nwse-resize' : 'nesw-resize'};`;
        document.body.appendChild(h);
        h.addEventListener('mousedown', (e) => startImageResize(e, corner));
        return h;
    });

    // Permukaan drag untuk gambar floating
    if (isFloatingImage(img)) {
        dragSurfaceEl = document.createElement('div');
        dragSurfaceEl.title = 'Geser gambar';
        dragSurfaceEl.style.cssText =
            'position:fixed;z-index:999998;cursor:move;background:transparent;';
        document.body.appendChild(dragSurfaceEl);

        dragSurfaceEl.addEventListener('mousedown', (e) => {
            if (!activeImage || !isFloatingImage(activeImage)) return;
            const region = activeImage.closest(FLOAT_REGION_SELECTOR) || activeImage.closest('.doc-sheet');
            if (!region) return;

            e.preventDefault();
            floatingImg = activeImage;
            floatingRegion = region;
            floatingDragArmed = true;
            isDraggingFloating = false;
            floatStartX = e.clientX;
            floatStartY = e.clientY;
            floatBaseLeft = parseFloat(activeImage.style.left) || 0;
            floatBaseTop = parseFloat(activeImage.style.top) || 0;
        });
    }

    positionImageTools();

    const loop = () => {
        if (!activeImage) return;
        if (!activeImage.isConnected) {
            removeImageTools();
            return;
        }
        positionImageTools();
        watchTimer = requestAnimationFrame(loop);
    };
    watchTimer = requestAnimationFrame(loop);
};


// DRAG ANGKAT & JATUHKAN UNTUK GAMBAR BIASA
// Tekan gambar + geser -> gambar "terangkat" mengikuti kursor.
// Lepas di atas kertas -> berhenti PERSIS di titik pelepasan
//                         (otomatis jadi gambar floating).
// Lepas di luar kertas -> kembali ke tempat & gaya semula.


const mulaiFlowDrag = () => {
    const img = flowDragSourceImg;
    if (!img) return;

    isDraggingFlowImage = true;
    try { window.getSelection()?.removeAllRanges(); } catch (err) { /* noop */ }

    const rect = img.getBoundingClientRect();
    flowDragOffsetX = flowStartX - rect.left;
    flowDragOffsetY = flowStartY - rect.top;

    // Kunci ukuran supaya tidak berubah saat pindah induk
    img.style.width = rect.width + 'px';
    img.style.height = rect.height + 'px';

    // Angkat dari aliran teks
    img.style.position = 'fixed';
    img.style.margin = '0';
    img.style.zIndex = '999997';
    img.style.pointerEvents = 'none';
    img.style.cursor = 'grabbing';
    img.style.maxWidth = 'none';

    document.body.appendChild(img);
    document.body.style.userSelect = 'none';
};

const kembalikanKePosisiSemula = (img) => {
    img.setAttribute('style', flowImgOriginalCssText);

    if (flowImgOriginalParent && flowImgOriginalParent.isConnected) {
        flowImgOriginalParent.insertBefore(img, flowImgOriginalNext);
        return;
    }

    // Induk lama sudah hilang -> taruh di awal region pertama
    const firstRegion = document.querySelector('#document-editor .doc-sheet-body');
    if (firstRegion) firstRegion.insertBefore(img, firstRegion.firstChild);
};

const selesaiFlowDrag = (e) => {
    const img = flowDragSourceImg;
    flowDragSourceImg = null;
    if (!img) return;

    // Titik jatuh yang diinginkan user = posisi ghost saat dilepas
    const ghostLeft = e.clientX - flowDragOffsetX;
    const ghostTop = e.clientY - flowDragOffsetY;
    const ghostW = img.offsetWidth;
    const ghostH = img.offsetHeight;

    // Cari region kertas di bawah kursor
    let region = null;
    try {
        const stack = document.elementsFromPoint(e.clientX, e.clientY) || [];
        for (const el of stack) {
            if (!el.closest) continue;
            region = el.closest('.doc-sheet-body, .doc-sheet-header, .doc-sheet-footer');
            if (region) break;
            const sheet = el.closest('.doc-sheet');
            if (sheet) { region = sheet.querySelector('.doc-sheet-body'); break; }
        }
    } catch (err) {
        region = null;
    }

    // Lepas gaya "angkat" (ukuran sengaja dipertahankan)
    img.style.position = '';
    img.style.margin = '';
    img.style.zIndex = '';
    img.style.pointerEvents = '';
    img.style.cursor = '';
    img.style.maxWidth = '';

    if (region && region.isConnected) {
        // Berhenti PERSIS di titik pelepasan: jadikan gambar floating
        const rRect = region.getBoundingClientRect();
        // Bebas menempel di mana saja dalam kertas (tepi, sudut, kop, footer)
        const [left, top] = clampPosToSheet(
            region,
            ghostLeft - rRect.left,
            ghostTop - rRect.top,
            ghostW,
            ghostH
        );

        img.classList.remove('doc-image-behind');
        img.classList.add('doc-image-front');

        img.style.float = '';
        img.style.display = 'block';
        img.style.position = 'absolute';
        img.style.left = left + 'px';
        img.style.top = top + 'px';
        img.style.margin = '0';
        img.style.zIndex = '20';
        img.style.cursor = 'grab';

        region.appendChild(img);
    } else {
        // Dilepas di luar kertas -> kembali ke tempat & gaya semula
        img.style.left = '';
        img.style.top = '';
        kembalikanKePosisiSemula(img);
    }

    notifyDirty();
    positionImageTools();
};


// TITIK SISIP DI MANA SAJA (ala Microsoft Word):
// klik di mana pun pada kertas -> caret pindah ke
// baris terdekat yang bisa diketik.


// Kelompokkan karakter tiap text-node menjadi baris-baris visual,
// lalu cari baris dengan jarak terdekat ke titik klik.
const nearestLine = (rootEl, x, y) => {
    const walker = document.createTreeWalker(rootEl, NodeFilter.SHOW_TEXT);
    let best = null;
    let node;

    const consider = (L) => {
        const midY = (L.top + L.bottom) / 2;
        const score =
            Math.abs(y - midY) * 1000 +
            Math.abs(x - (L.left + L.right) / 2) * 0.001;
        if (!best || score < best.score) best = Object.assign({ score }, L);
    };

    while ((node = walker.nextNode())) {
        const txt = node.nodeValue;
        if (!txt || !txt.trim()) continue;

        let cur = null;
        for (let i = 0; i < txt.length; i++) {
            const r = document.createRange();
            r.setStart(node, i);
            r.setEnd(node, i + 1);
            const rc = r.getBoundingClientRect();
            if (rc.width === 0 && rc.height === 0) continue;

            if (!cur || Math.abs(rc.top - cur.top) > 2) {
                if (cur) consider(cur);
                cur = {
                    top: rc.top,
                    bottom: rc.bottom,
                    start: i,
                    end: i + 1,
                    node,
                    left: rc.left,
                    right: rc.right,
                };
            } else {
                cur.end = i + 1;
                cur.left = Math.min(cur.left, rc.left);
                cur.right = Math.max(cur.right, rc.right);
                cur.top = Math.min(cur.top, rc.top);
                cur.bottom = Math.max(cur.bottom, rc.bottom);
            }
        }
        if (cur) consider(cur);
    }
    return best;
};

// Konversi posisi DOM (text node + offset) di dalam editor Quill
// menjadi indeks karakter milik Quill. Bukan text node -> null.
const domPosToQuillIndex = (q, node, offset) => {
    try {
        if (!node || node.nodeType !== Node.TEXT_NODE || !q.root.contains(node)) return null;
        const leaf = Quill.find(node);
        if (!leaf) return null;
        const len = String(leaf.value?.() ?? '').length;
        return q.getIndex(leaf) + Math.max(0, Math.min(offset, len));
    } catch (_) {
        return null;
    }
};

// Range dari titik layar (cross-browser).
const caretRangeAtPoint = (x, y) => {
    try {
        if (document.caretRangeFromPoint) return document.caretRangeFromPoint(x, y);
        if (document.caretPositionFromPoint) {
            const p = document.caretPositionFromPoint(x, y);
            if (!p) return null;
            const rg = document.createRange();
            rg.setStart(p.offsetNode, p.offset);
            rg.collapse(true);
            return rg;
        }
    } catch (_) { /* noop */ }
    return null;
};

// Semua "slot" blok dalam satu editor: paragraf/judul/list/kutipan,
// plus embed hr/img. Dipakai untuk memetakan titik klik -> indeks Quill
// TERMASUK di area kosong (paragraf kosong, ruang kosong kertas).
const SLOT_SELECTOR = 'p, h1, h2, h3, h4, h5, h6, li, blockquote, pre, hr, img';

// Indeks caret di dalam SATU slot blok berdasarkan titik klik.
const indexInSlot = (q, el, x, y) => {
    const r = el.getBoundingClientRect();
    const blot = Quill.find(el);
    const base = blot ? q.getIndex(blot) : 0;

    // Embed tunggal (garis kop / gambar): sebelum atau sesudah embed
    if (el.nodeName === 'HR' || el.nodeName === 'IMG') {
        return y > r.top + r.height / 2
            ? Math.min(base + 1, Math.max(0, q.getLength() - 1))
            : base;
    }

    // Paragraf/baris KOSONG -> caret persis di baris kosong itu.
    // (versi lama melompatinya karena tak punya text node)
    if (!(el.textContent || '').trim()) return base;

    // Presisi: baris karakter terdekat dalam slot ini
    // (teks panjang yang wrap beberapa baris tetap akurat).
    const line = nearestLine(el, x, y);
    if (line) {
        const idx = domPosToQuillIndex(
            q, line.node,
            x < line.left + 1 ? line.start : line.end
        );
        if (idx != null) return idx;
    }

    // Cadangan: awal/akhir blok
    const len = typeof blot?.length === 'function' ? blot.length() : 1;
    return x < r.left + r.width / 2 ? base : base + Math.max(0, len - 1);
};

const quillIndexFromPoint = (q, editorEl, x, y) => {
    const slots = Array.from(editorEl.querySelectorAll(SLOT_SELECTOR))
        .filter((el) => !el.querySelector('p, h1, h2, h3, h4, h5, h6, li, blockquote, pre'))
        .filter((el) => {
            const r = el.getBoundingClientRect();
            return r.width > 0 || r.height > 0;
        });

    if (!slots.length) return Math.max(0, q.getLength() - 1);

    const first = slots[0].getBoundingClientRect();
    const last = slots[slots.length - 1].getBoundingClientRect();

    // Klik di atas semua konten -> awal dokumen
    if (y < first.top) return 0;
    // Klik di bawah semua konten (area kosong kertas) -> akhir dokumen
    if (y > last.bottom) return Math.max(0, q.getLength() - 1);

    // Slot dengan pita vertikal terdekat terhadap titik klik
    let best = null;
    let bestDy = Infinity;
    for (const el of slots) {
        const r = el.getBoundingClientRect();
        const dy = y < r.top ? r.top - y : (y > r.bottom ? y - r.bottom : 0);
        if (dy < bestDy) {
            bestDy = dy;
            best = el;
        }
    }
    return best ? indexInSlot(q, best, x, y) : Math.max(0, q.getLength() - 1);
};


const placeCaretAtPoint = (x, y) => {
    let region = null;
    let sheet = null;
    try {
        const stack = document.elementsFromPoint(x, y) || [];
        for (const el of stack) {
            if (!el.closest) continue;
            sheet = sheet || el.closest('.doc-sheet');
            region = region ||
                el.closest('.doc-sheet-body, .doc-sheet-header, .doc-sheet-footer');
            if (region && sheet) break;
        }
    } catch (_) { /* noop */ }


    const pickEnabled = (reg) => {
        if (!reg) return null;
        const q = quillsByRegion.get(reg);
        return q && q.isEnabled() ? q : null;
    };

    let q = pickEnabled(region);

    if (!q && sheet) {

        let bestReg = null;
        let bestDy = Infinity;
        sheet.querySelectorAll('.doc-sheet-body[data-region="body"]').forEach((reg) => {
            const cq = pickEnabled(reg);
            if (!cq) return;
            const rr = reg.getBoundingClientRect();
            const dy = y < rr.top ? rr.top - y : (y > rr.bottom ? y - rr.bottom : 0);
            if (dy < bestDy) {
                bestDy = dy;
                bestReg = reg;
            }
        });
        q = bestReg ? quillsByRegion.get(bestReg) : null;
    }

    if (!q) return false;

    const index = quillIndexAtPoint(q, x, y);
    if (index == null) return false;

    // Pasang caret lewat Quill (bukan DOM mentah) agar semuanya konsisten
    q.focus();
    q.setSelection(index, 0);
    return true;
};

// Indeks Quill pada titik layar untuk SATU instance Quill — TANPA
// memindahkan caret. Dipakai placeCaretAtPoint dan seleksi double-click.
const quillIndexAtPoint = (q, x, y) => {
    let index = null;
    const native = caretRangeAtPoint(x, y);
    if (
        native &&
        native.startContainer &&
        native.startContainer.nodeType === Node.TEXT_NODE &&
        q.root.contains(native.startContainer)
    ) {
        index = domPosToQuillIndex(q, native.startContainer, native.startOffset);
    }

    // Fallback geometris: area kosong, paragraf kosong, bawah kertas, hr
    if (index == null) {
        index = quillIndexFromPoint(q, q.root, x, y);
    }
    if (index == null) return null;
    return Math.max(0, Math.min(index, Math.max(0, q.getLength() - 1)));
};

// LISTENER GLOBAL (dipasang sekali)

if (!window.__imageToolsBound) {
    window.__imageToolsBound = true;

    document.addEventListener('mousedown', (e) => {
        if (e.target?.nodeName !== 'IMG') return;
        const img = e.target;
        if (!isFloatingImage(img)) return;

        if (e.button !== 0) return;

        const region = img.closest(FLOAT_REGION_SELECTOR) || img.closest('.doc-sheet');
        if (!region) return;

        // preventDefault: cegah seleksi teks & drag bawaan browser.
        // Klik singkat tetap menghasilkan event click -> perilaku normal.
        e.preventDefault();
        floatingImg = img;
        floatingRegion = region;
        floatingDragArmed = true;
        isDraggingFloating = false;
        floatStartX = e.clientX;
        floatStartY = e.clientY;
        floatBaseLeft = parseFloat(img.style.left) || 0;
        floatBaseTop = parseFloat(img.style.top) || 0;
    });

    // Siapkan drag angkat & jatuhkan saat menekan gambar biasa
    document.addEventListener('mousedown', (e) => {
        if (e.target?.nodeName !== 'IMG') return;
        if (e.button !== 0) return;
        const img = e.target;
        if (isFloatingImage(img)) return;          // floating ditangani handler di atas
        if (!img.closest('.doc-sheet')) return;    // hanya gambar di dalam dokumen

        flowDragSourceImg = img;
        flowDragArmed = true;
        isDraggingFlowImage = false;
        flowStartX = e.clientX;
        flowStartY = e.clientY;
        flowImgOriginalCssText = img.getAttribute('style') || '';
        flowImgOriginalParent = img.parentElement;
        flowImgOriginalNext = img.nextSibling;
    });

    // Blokir drag bawaan browser pada gambar dokumen —
    // ghost drag native membunuh event mousemove/mouseup milik drag kustom.
    document.addEventListener('dragstart', (e) => {
        const img = e.target;
        if (img?.nodeName !== 'IMG') return;
        if (img.closest('.doc-signature')) return;
        if (!img.closest('.doc-sheet')) return;
        e.preventDefault();
    });

    // TITIK SISIP DI MANA SAJA: SEMUA klik kiri di kertas dihitung lewat
    document.addEventListener(
        'mousedown',
        (e) => {
            if (e.button !== 0) return;

            const sheet = e.target?.closest?.('.doc-sheet');
            if (!sheet) return; // hanya di dalam kertas

            // Interaksi khusus yang tidak boleh diganggu
            if (e.target.closest?.('.doc-signature')) return;
            if (e.target.closest?.('img')) return;
            if (e.target.closest?.('button, a, input, select, textarea')) return;

            // Zona terkunci (di luar sesi): biarkan tanpa caret —
            // double-click untuk membuka sesi ditangani handler lain
            const zone = e.target.closest?.('.doc-sheet-header, .doc-sheet-footer');
            if (zone) {
                const zq = quillsByRegion.get(zone);
                if (!zq || !zq.isEnabled()) return;
            }


            if (e.detail >= 2) return;

            // Hitung & pasang caret tepat di titik klik (fallback: posisi terdekat)
            if (placeCaretAtPoint(e.clientX, e.clientY)) {
                e.preventDefault(); // kita yang memasang caret
            }
        },
        true // capture: jalan paling awal, tidak bisa diganggu handler lain
    );

    // DOUBLE-CLICK = blok kata; TRIPLE-CLICK = blok baris/paragraf.
    // Dikerjakan MANUAL lewat API Quill agar deterministik — tidak lagi
    // bergantung pada seleksi native browser yang bisa terganggu oleh
    // preventDefault pada klik pertama maupun fitur clickAndType.
    // Header/footer dibiarkan lewat supaya double-click tetap membuka
    // sesi edit zona (ditangani editor.blade.php).
    const __wordChar = (ch) => /[\w\u00C0-\u024F\u1E00-\u1EFF]/.test(ch || '');

    document.addEventListener(
        'dblclick',
        (e) => {
            if (e.button !== 0) return;
            if (document.querySelector('.zone-editing')) return; // sesi zona aktif

            const region = e.target?.closest?.('.doc-sheet-body');
            if (!region) return; // header/footer: biarkan handler sesi zona
            const q = quillsByRegion.get(region);
            if (!q || !q.isEnabled()) return;

            // Hanya titik di ATAS teks. Titik di area kosong kertas ->
            // biarkan fitur clickAndType (double-click area kosong) bekerja.
            const native = caretRangeAtPoint(e.clientX, e.clientY);
            const overText = !!(
                native &&
                native.startContainer &&
                native.startContainer.nodeType === Node.TEXT_NODE &&
                q.root.contains(native.startContainer)
            );
            if (!overText) return;

            // Blokir handler lain (termasuk clickAndType di blade) yang
            // bisa menghapus seleksi yang akan kita buat.
            e.preventDefault();
            e.stopPropagation();

            const index = quillIndexAtPoint(q, e.clientX, e.clientY);
            if (index == null) return;
            const text = q.getText();

            // TRIPLE-CLICK: blok satu baris/paragraf (tanpa newline penutup)
            if (e.detail >= 3) {
                const start = text.lastIndexOf('\n', index - 1) + 1;
                let end = text.indexOf('\n', index);
                if (end < 0) end = text.length;
                q.setSelection(start, Math.max(0, end - start), 'user');
                refreshToolbarStates();
                return;
            }

            // DOUBLE-CLICK: blok satu kata.
            // Jika titik klik jatuh tepat di batas kata (offset setelah
            // karakter terakhir), geser mundur satu agar kata terpilih.
            let idx = index;
            if (!__wordChar(text[idx]) && idx > 0 && __wordChar(text[idx - 1])) {
                idx--;
            }

            let s = idx;
            let t = idx;
            if (__wordChar(text[idx])) {
                // kata: huruf/angka
                while (s > 0 && __wordChar(text[s - 1])) s--;
                while (t < text.length && __wordChar(text[t])) t++;
            } else if (/\s/.test(text[idx] || '')) {
                // whitespace: blok run whitespace (meniru perilaku Word)
                while (s > 0 && /\s/.test(text[s - 1]) && !__wordChar(text[s - 1])) s--;
                while (t < text.length && /\s/.test(text[t]) && !__wordChar(text[t])) t++;
            } else {
                // tanda baca: blok run karakter yang sama
                const ch = text[idx] || '';
                while (s > 0 && text[s - 1] === ch) s--;
                while (t < text.length && text[t] === ch) t++;
            }
            if (t > s) q.setSelection(s, t - s, 'user');
            refreshToolbarStates();
        },
        true
    );

    document.addEventListener('mousemove', (e) => {
        // ---- DRAG ANGKAT GAMBAR FLOW: trigger saat digeser + ghost ikut kursor ----
        if (flowDragArmed && !isDraggingFlowImage && flowDragSourceImg) {
            if (e.buttons === 0) {
                // Tombol sudah dilepas tanpa mouseup -> batalkan arm-nya saja
                flowDragArmed = false;
                flowDragSourceImg = null;
            } else if (Math.hypot(e.clientX - flowStartX, e.clientY - flowStartY) >= 4) {
                mulaiFlowDrag();
            }
        }

        if (isDraggingFlowImage && flowDragSourceImg) {
            e.preventDefault();
            // Ghost mengikuti kursor dengan offset genggaman yang sama
            flowDragSourceImg.style.left = (e.clientX - flowDragOffsetX) + 'px';
            flowDragSourceImg.style.top = (e.clientY - flowDragOffsetY) + 'px';
        }

        // ---- RESIZE: tumbuh menuju handle, sudut oposisi tetap sebagai anchor ----
        if (isResizingImage && activeImage) {
            // Proyeksikan pergerakan kursor ke vektor handle->anchor (skala seragam)
            const vx = e.clientX - resizeAnchorX;
            const vy = e.clientY - resizeAnchorY;
            const denom = resizeBaseVecX * resizeBaseVecX + resizeBaseVecY * resizeBaseVecY;
            let scale = denom > 0 ? (vx * resizeBaseVecX + vy * resizeBaseVecY) / denom : 1;

            const minScale = Math.max(24 / resizeStartW, 24 / resizeStartH, 0.02);
            scale = Math.max(scale, minScale);

            const newW = Math.max(24, Math.round(resizeStartW * scale));
            const newH = Math.max(24, Math.round(resizeStartH * scale));
            const dW = newW - resizeStartW;
            const dH = newH - resizeStartH;

            // Sisi yang bergerak tergantung handle yang ditarik
            const shiftLeft = resizeCorner === 'nw' || resizeCorner === 'sw';
            const shiftTop = resizeCorner === 'nw' || resizeCorner === 'ne';

            activeImage.style.width = newW + 'px';
            activeImage.style.height = newH + 'px';

            if (resizeIsFloating) {
                // Anchor diam: geser left/top hanya bila sisi kiri/atas yang bergerak
                if (shiftLeft) activeImage.style.left = (resizeStartLeft - dW) + 'px';
                if (shiftTop) activeImage.style.top = (resizeStartTop - dH) + 'px';
            } else {
                // Gambar di aliran teks: kompensasi margin agar anchor tak bergeser visual
                if (shiftLeft) activeImage.style.marginLeft = (resizeStartMarginLeft - dW) + 'px';
                if (shiftTop) activeImage.style.marginTop = (resizeStartMarginTop - dH) + 'px';
            }

            positionImageTools();
        }

        // ---- DRAG FLOATING ----
        if (!floatingImg) return;

        if (floatingDragArmed && !isDraggingFloating) {
            if (Math.hypot(e.clientX - floatStartX, e.clientY - floatStartY) < 4) return;
            isDraggingFloating = true;
            try { window.getSelection()?.removeAllRanges(); } catch (err) { /* noop */ }
            floatingImg.style.cursor = 'grabbing';
            document.body.style.userSelect = 'none';
        }

        if (!isDraggingFloating) return;
        e.preventDefault();

        // Bebas digeser ke seluruh area kertas (tanpa batas kotak region)
        const [nextLeft, nextTop] = clampPosToSheet(
            floatingRegion,
            floatBaseLeft + (e.clientX - floatStartX),
            floatBaseTop + (e.clientY - floatStartY),
            floatingImg.offsetWidth,
            floatingImg.offsetHeight
        );

        floatingImg.style.left = nextLeft + 'px';
        floatingImg.style.top = nextTop + 'px';
        positionImageTools();
    });

    document.addEventListener('mouseup', (e) => {
        // Selesaikan drag floating
        if (floatingImg) {
            const wasDragged = isDraggingFloating;
            floatingImg.style.cursor = 'grab';
            floatingImg = null;
            floatingRegion = null;
            floatingDragArmed = false;
            isDraggingFloating = false;

            if (wasDragged) {
                document.body.style.userSelect = '';
                notifyDirty();
            }
        }

        // Selesaikan drag angkat & jatuhkan gambar biasa
        if (flowDragArmed || isDraggingFlowImage) {
            const wasDragging = isDraggingFlowImage;
            flowDragArmed = false;
            isDraggingFlowImage = false;

            if (wasDragging) {
                document.body.style.userSelect = '';
                selesaiFlowDrag(e);
            } else {
                flowDragSourceImg = null; // hanya klik biasa, batal saja
            }
        }

        if (isResizingImage) {
            notifyDirty();
            document.body.style.userSelect = '';
        }
        isResizingImage = false;
    });

    document.addEventListener('scroll', () => positionImageTools(), true);
    window.addEventListener('resize', () => positionImageTools());

    // Klik di luar gambar & alatnya -> tutup mode edit gambar
    document.addEventListener('click', (e) => {
        if (e.target === bubbleEl || bubbleEl?.contains(e.target)) return;
        if (e.target === removeBtnEl || removeBtnEl?.contains(e.target)) return;
        if (e.target === dragSurfaceEl || dragSurfaceEl?.contains(e.target)) return;
        if (e.target === panelEl || panelEl?.contains(e.target)) return;
        if (handleEls.includes(e.target)) return;
        if (e.target.nodeName === 'IMG') return;
        removeImageTools();
    });

    // Klik dua kali pada gambar yang TERTUTUP teks -> pilih gambarnya
    document.addEventListener('dblclick', (e) => {
        const stack = document.elementsFromPoint(e.clientX, e.clientY);
        const img = stack.find((el) => el.nodeName === 'IMG');
        if (!img) return;

        // Gambar yang terlihat ditangani klik tunggal per-editor
        if (stack[0] === img) return;

        if (img.closest('.doc-signature')) return;

        const editor = findEditorContaining(img);
        if (!editor) return;

        e.preventDefault();
        try { window.getSelection()?.removeAllRanges(); } catch (err) { /* noop */ }

        showImageTools(editor, img);
    });
}

// TOOLBAR QUILL (satu toolbar bersama untuk semua region)

let activeQuill = null;
const quillsByRegion = new Map();

let hiddenImageInput = null;

const getActiveQuill = () => {
    if (activeQuill && activeQuill.isEnabled()) return activeQuill;
    for (const q of quillsByRegion.values()) {
        if (q.isEnabled()) return q;
    }
    return null;
};

const TOOLBAR_TOGGLES = ['bold', 'italic', 'underline', 'strike'];

const refreshToolbarStates = () => {
    const q = activeQuill;
    const sel = q ? q.getSelection() : null;

    const fmt = (q && sel) ? q.getFormat(sel.index, sel.length) : {};

    document.querySelectorAll('#body-toolbar-container [data-cmd]').forEach((btn) => {
        const cmd = btn.dataset.cmd;
        if (TOOLBAR_TOGGLES.includes(cmd)) {
            btn.classList.toggle('active', !!fmt[cmd]);
        } else if (cmd === 'superscript') {
            btn.classList.toggle('active', fmt.script === 'super');
        } else if (cmd === 'subscript') {
            btn.classList.toggle('active', fmt.script === 'sub');
        } else if (cmd === 'bullist') {
            btn.classList.toggle('active', fmt.list === 'bullet');
        } else if (cmd === 'numlist') {
            btn.classList.toggle('active', fmt.list === 'ordered' && !fmt.liststyle);
        } else if (cmd === 'alphalist') {
            btn.classList.toggle('active', fmt.list === 'ordered' && fmt.liststyle === 'alpha');
        } else if (cmd === 'table') {
            btn.classList.toggle('active', !!fmt.table);
        }
    });

    document.querySelectorAll('#body-toolbar-container [data-align]').forEach((btn) => {
        btn.classList.toggle('active', (fmt.align || 'left') === btn.dataset.align);
    });

    const blockSel = document.getElementById('tb-block');
    if (blockSel) blockSel.value = fmt.header ? String(fmt.header) : '';
    const fontSel = document.getElementById('tb-font');
    if (fontSel) fontSel.value = fmt.font || 'Arial';
    const sizeSel = document.getElementById('tb-size');
    if (sizeSel) sizeSel.value = fmt.size || '14px';
    const lineHeightSel = document.getElementById('tb-lineheight');
    if (lineHeightSel) lineHeightSel.value = fmt.lineheight || '1.5';
};


// TOOL TABEL (Quill 2 punya modul table bawaan)


const getTableModule = () => {
    const q = getActiveQuill();
    if (!q) return null;
    return q.getModule('table') || null;
};

// Benar-benar di dalam sel tabel? (berguna untuk enable/disable aksi)
const isInsideTable = () => {
    const q = getActiveQuill();
    if (!q) return false;
    const sel = q.getSelection();
    if (!sel) return false;
    return !!q.getFormat(sel.index, sel.length || 1).table;
};

const runTableAction = (action) => {
    const q = getActiveQuill();
    if (!q) return;
    const table = q.getModule('table');
    if (!table) return;

    switch (action) {
        case 'insert-row': table.insertRowBelow(); break;
        case 'insert-column': table.insertColumnRight(); break;
        case 'delete-row': table.deleteRow(); break;
        case 'delete-column': table.deleteColumn(); break;
        case 'delete-table': table.deleteTable(); break;
        default: return;
    }

    notifyDirty();
    refreshToolbarStates();
};

const insertTableAtSelection = (rows, cols) => {
    const q = getActiveQuill();
    if (!q) return;
    const table = q.getModule('table');
    if (!table) return;

    // Bila sudah di dalam tabel, tabel baru tidak boleh ditumpuk
    const sel = q.getSelection(true);
    if (sel && q.getFormat(sel.index, sel.length || 1).table) return;

    table.insertTable(Math.max(1, rows), Math.max(1, cols));
    notifyDirty();
    refreshToolbarStates();
};

const ensureHiddenImageInput = () => {
    if (hiddenImageInput) return hiddenImageInput;

    hiddenImageInput = document.createElement('input');
    hiddenImageInput.type = 'file';
    hiddenImageInput.accept = 'image/png,image/jpeg,image/gif,image/webp';
    hiddenImageInput.style.display = 'none';
    document.body.appendChild(hiddenImageInput);

    hiddenImageInput.addEventListener('change', async () => {
        const file = hiddenImageInput.files?.[0];
        hiddenImageInput.value = '';
        if (!file) return;

        try {
            const url = await uploadImageFile(file);
            const q = getActiveQuill();
            if (!q) return;
            const range = q.getSelection(true);
            q.insertEmbed(range.index, 'image', url, 'user');
            q.setSelection(range.index + 1);
            notifyDirty();
        } catch (err) {
            window.Swal?.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal mengunggah gambar.',
                confirmButtonColor: '#1B2A4A',
            });
        }
    });

    return hiddenImageInput;
};

const applyCmd = (cmd) => {
    const q = getActiveQuill();
    if (!q) return;

    const sel = q.getSelection(true);

    switch (cmd) {
        case 'undo': {
            const bridge = window.__docUndoBridge;
            if (bridge && typeof bridge.undo === 'function' && bridge.undo()) {
                break;
            }
            q.history.undo();
            break;
        }
        case 'redo': {
            const bridge = window.__docUndoBridge;
            if (bridge && typeof bridge.redo === 'function' && bridge.redo()) {
                break;
            }
            q.history.redo();
            break;
        }
        case 'bold':
        case 'italic':
        case 'underline':
        case 'strike':
            q.format(cmd, !q.getFormat(sel)[cmd]);
            break;
        case 'superscript':
        case 'subscript': {
            const scriptKey = cmd === 'superscript' ? 'super' : 'sub';
            q.format('script', q.getFormat(sel).script === scriptKey ? false : scriptKey);
            break;
        }
        case 'bullist': {
            const cur = q.getFormat(sel).list === 'bullet';
            q.format('list', cur ? false : 'bullet');
            q.format('liststyle', false);
            break;
        }
        case 'numlist': {
            const fmtNow = q.getFormat(sel);
            const isPlainOrdered = fmtNow.list === 'ordered' && !fmtNow.liststyle;
            q.format('list', isPlainOrdered ? false : 'ordered');
            q.format('liststyle', false);
            break;
        }
        case 'alphalist': {
            const fmtNow = q.getFormat(sel);
            const isAlpha = fmtNow.list === 'ordered' && fmtNow.liststyle === 'alpha';
            if (isAlpha) {
                q.format('list', false);
                q.format('liststyle', false);
            } else {
                q.format('list', 'ordered');
                q.format('liststyle', 'alpha');
            }
            break;
        }
        case 'outdent':
        case 'indent': {
            // Whitelist indent Quill = angka 1..8; string '+1'/'-1' tidak valid.
            const curIndent = q.getFormat(sel).indent;
            const level = typeof curIndent === 'number' ? curIndent : 0;
            const nextLevel =
                cmd === 'indent'
                    ? Math.min(8, level + 1)
                    : Math.max(0, level - 1);
            q.format('indent', nextLevel > 0 ? nextLevel : false);
            break;
        }
        case 'link': {
            const prev = q.getFormat(sel).link || '';
            const url = window.prompt('URL link:', prev || 'https://');
            if (url === null) return;
            if (!url) q.formatText(sel.index, sel.length, 'link', false);
            else q.formatText(sel.index, sel.length, 'link', url);
            break;
        }
        case 'image':
            ensureHiddenImageInput().click();
            return;
        case 'hr': {
            const range = q.getSelection(true);
            q.insertEmbed(range.index, 'hr', true, 'user');
            q.setSelection(range.index + 1);
            break;
        }
        case 'removeformat':
            q.removeFormat(sel.index, sel.length);
            break;
        default:
            return;
    }

    notifyDirty();
    refreshToolbarStates();
};

const bindToolbar = () => {
    if (window.__quillToolbarBound) return;
    window.__quillToolbarBound = true;

    const container = document.getElementById('body-toolbar-container');
    if (!container) return;

    container.querySelectorAll('[data-cmd]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            applyCmd(btn.dataset.cmd);
        });
    });

    container.querySelectorAll('[data-align]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const q = getActiveQuill();
            if (!q) return;

            // Tombol mencuri fokus dari editor -> pulihkan seleksi terakhir
            if (!q.getSelection(true)) return;

            // Rata kiri = default = HAPUS atribut align.
            const val = btn.dataset.align;
            q.format('align', val === 'left' ? false : val);

            notifyDirty();
            refreshToolbarStates();
        });
    });

    const blockSel = document.getElementById('tb-block');
    blockSel?.addEventListener('change', () => {
        const q = getActiveQuill();
        if (!q) return;
        if (!q.getSelection(true)) return;
        q.format('header', blockSel.value ? parseInt(blockSel.value, 10) : false);
        notifyDirty();
        refreshToolbarStates();
    });

    const fontSel = document.getElementById('tb-font');
    fontSel?.addEventListener('change', () => {
        const q = getActiveQuill();
        if (!q) return;
        if (!q.getSelection(true)) return;
        q.format('font', fontSel.value || false);
        notifyDirty();
        refreshToolbarStates();
    });

    const sizeSel = document.getElementById('tb-size');

    const applyFontSize = (px) => {
        const q = getActiveQuill();
        if (!q) return;
        if (!q.getSelection(true)) return;
        const clamped = Math.max(6, Math.min(120, px));
        const pxValue = clamped + 'px';

        q.format('size', pxValue);

        const hasOption = Array.from(sizeSel.options).some((o) => o.value === pxValue);
        sizeSel.value = hasOption ? pxValue : '';

        notifyDirty();
        refreshToolbarStates();
    };

    sizeSel?.addEventListener('change', () => {
        const q = getActiveQuill();
        if (!q) return;
        if (!q.getSelection(true)) return;
        q.format('size', sizeSel.value || false);
        notifyDirty();
        refreshToolbarStates();
    });

    document.querySelectorAll('.toolbar-size-arrow').forEach((btn) => {
        btn.addEventListener('click', () => {
            const step = parseInt(btn.dataset.sizeStep, 10);
            const q = getActiveQuill();
            if (!q) return;
            const sel = q.getSelection(true);
            if (!sel) return;
            const currentPx = parseInt(q.getFormat(sel).size, 10) || 14;
            applyFontSize(currentPx + step);
        });
    });

        const lineHeightSel = document.getElementById('tb-lineheight');
        lineHeightSel?.addEventListener('change', () => {
            const q = getActiveQuill();
            if (!q) return;
            if (!q.getSelection(true)) return;

            q.format('lineheight', lineHeightSel.value || false);

            notifyDirty();
            refreshToolbarStates();
        });

    const colorInput = document.getElementById('tb-color');
    colorInput?.addEventListener('input', () => {
        const q = getActiveQuill();
        if (!q) return;
        if (!q.getSelection(true)) return;
        q.format('color', colorInput.value);
        notifyDirty();
        refreshToolbarStates();
    });

    const bgColorInput = document.getElementById('tb-bgcolor');
    bgColorInput?.addEventListener('input', () => {
        const q = getActiveQuill();
        if (!q) return;
        if (!q.getSelection(true)) return;
        q.format('background', bgColorInput.value);
        notifyDirty();
        refreshToolbarStates();
    });

    // Klik kanan pada swatch warna = hapus warna
    [colorInput, bgColorInput].forEach((input) => {
        input?.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            const q = getActiveQuill();
            if (!q) return;
            if (!q.getSelection(true)) return;
            q.format(input === colorInput ? 'color' : 'background', false);
            notifyDirty();
            refreshToolbarStates();
        });
    });

    // TOOL TABEL: dropdown grid picker + aksi baris/kolom
    const tableDd = document.getElementById('tb-table-dd');
    if (tableDd) {
        const menu = tableDd.querySelector('.toolbar-dropdown-menu');
        const grid = tableDd.querySelector('.table-grid-picker');
        const label = tableDd.querySelector('.table-grid-label');

        // Bangun grid picker 10 x 8 (baris x kolom) sekali saja
        if (grid && grid.children.length === 0) {
            const GRID_COLS = 10;
            const GRID_ROWS = 8;
            for (let r = 1; r <= GRID_ROWS; r += 1) {
                for (let c = 1; c <= GRID_COLS; c += 1) {
                    const cell = document.createElement('button');
                    cell.type = 'button';
                    cell.className = 'table-grid-cell';
                    cell.dataset.rows = r;
                    cell.dataset.cols = c;
                    cell.setAttribute('aria-label', `Tabel ${r} x ${c}`);
                    grid.appendChild(cell);
                }
            }

            const paintHover = (target) => {
                const rows = parseInt(target?.dataset.rows, 10) || 0;
                const cols = parseInt(target?.dataset.cols, 10) || 0;
                grid.querySelectorAll('.table-grid-cell').forEach((cell) => {
                    const on = parseInt(cell.dataset.rows, 10) <= rows &&
                        parseInt(cell.dataset.cols, 10) <= cols;
                    cell.classList.toggle('hovered', on);
                });
                if (label) {
                    label.textContent = rows > 0
                        ? `${rows} baris x ${cols} kolom`
                        : 'Sisipkan tabel';
                }
            };

            grid.addEventListener('mouseover', (e) => {
                const cell = e.target.closest('.table-grid-cell');
                if (cell) paintHover(cell);
            });
            grid.addEventListener('mouseleave', () => paintHover(null));

            grid.addEventListener('click', (e) => {
                const cell = e.target.closest('.table-grid-cell');
                if (!cell) return;
                insertTableAtSelection(parseInt(cell.dataset.rows, 10), parseInt(cell.dataset.cols, 10));
                closeTableMenu();
            });
        }

        const closeTableMenu = () => {
            menu?.classList.remove('open');
        };

        // Toggle dropdown — tombol utama punya data-cmd="table" yang no-op
        // di applyCmd, jadi aman dipasangi listener terpisah di sini.
        const mainBtn = tableDd.querySelector('[data-cmd="table"]');
        mainBtn?.addEventListener('click', () => {
            menu?.classList.toggle('open');
            // Disable aksi baris/kolom bila caret tidak di dalam tabel
            const inside = isInsideTable();
            menu?.querySelectorAll('[data-table-action]').forEach((btn) => {
                btn.classList.toggle('disabled', !inside);
            });
        });

        // Tutup dropdown saat klik di luar
        document.addEventListener('click', (e) => {
            if (!tableDd.contains(e.target)) closeTableMenu();
        });

        // Aksi baris/kolom/hapus tabel
        menu?.querySelectorAll('[data-table-action]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                if (btn.classList.contains('disabled')) {
                    e.preventDefault();
                    return;
                }
                runTableAction(btn.dataset.tableAction);
                closeTableMenu();
            });
        });
    }
};



const mirrorRegistry = { header: [], footer: [] };
let mirroringInProgress = false;

const registerMirror = (regionEl, q) => {
    const role = regionEl.dataset?.region;
    if ((role === 'header' || role === 'footer') &&
        !mirrorRegistry[role].some((m) => m.q === q)) {
        mirrorRegistry[role].push({ regionEl, q, role, lastCaret: 0 });
    }
};

const unregisterRegion = (regionEl) => {
    ['header', 'footer'].forEach((role) => {
        mirrorRegistry[role] = mirrorRegistry[role].filter((m) => m.regionEl !== regionEl);
    });
    quillsByRegion.delete(regionEl);
};

const syncMirrorsFrom = (sourceQ) => {
    if (mirroringInProgress) return;
    const entry = [...mirrorRegistry.header, ...mirrorRegistry.footer]
        .find((m) => m.q === sourceQ);
    if (!entry) return;

    mirroringInProgress = true;
    try {
        const html = sourceQ.root.innerHTML;
        mirrorRegistry[entry.role].forEach((m) => {
            if (m.q === sourceQ) return;
            const sel = m.q.getSelection();
            if (sel) m.lastCaret = sel.index;
            m.q.clipboard.dangerouslyPasteHTML(html);
            const maxIndex = Math.max(0, m.q.getLength() - 1);
            m.q.setSelection(Math.min(m.lastCaret, maxIndex), 'silent');
        });
    } finally {
        mirroringInProgress = false;
    }
};

const attachQuillToRegion = (regionEl) => {
    if (!regionEl || regionEl.dataset.quillReady === '1') {
        return quillsByRegion.get(regionEl) || null;
    }

    const existingHtml = regionEl.innerHTML;
    regionEl.innerHTML = '';

    // Host terpisah khusus untuk Quill
    const host = document.createElement('div');
    regionEl.appendChild(host);

    try {
        regionEl.dataset.quillReady = '1';

        const q = new Quill(host, {
            theme: 'snow',
            placeholder: '',
            modules: { toolbar: false, table: true },
            formats: ALLOWED_FORMATS,
        });

        if (existingHtml.trim()) {
            q.clipboard.dangerouslyPasteHTML(existingHtml);

            q.root.querySelectorAll('img').forEach((im) => {
                const st = im.getAttribute('style') || '';
                if (!/position\s*:\s*absolute/i.test(st)) return;
                const zi = parseInt(im.style.zIndex, 10);
                im.classList.add(
                    Number.isNaN(zi) || zi >= 0 ? 'doc-image-front' : 'doc-image-behind'
                );
                im.style.cursor = 'grab';
                regionEl.appendChild(im);
            });
        }

        // Zona header/footer mulai dalam keadaan INERT (ala Word):
        // hanya aktif saat sesi edit via double-click.
        const zoneRole = regionEl.dataset?.region;
        if (zoneRole === 'header' || zoneRole === 'footer') {
            q.enable(false);
            registerMirror(regionEl, q);
        }

        q.on('text-change', () => {
            notifyDirty();
            syncMirrorsFrom(q);
            if (typeof window.__docEditorSync === 'function') {
                window.__docEditorSync(q.root.innerHTML);
            }
        });

        // Paginasi otomatis: semua editor ISI (body) dipantau supaya konten
        // yang meluap dipindah ke kertas berikutnya secara otomatis.
        if (regionEl.dataset?.region === 'body') {
            bindPageOverflowWatch(q, regionEl);
        }

        q.on('selection-change', (range) => {
            if (range) {
                activeQuill = q;
                refreshToolbarStates();
            }
        });

        quillsByRegion.set(regionEl, q);
        return q;
    } catch (err) {
        console.error('[DocQuill] Gagal memasang editor pada region:', err);
        regionEl.dataset.quillReady = '';
        regionEl.innerHTML = existingHtml || '<p><br></p>';
        regionEl.setAttribute('contenteditable', 'true');
        regionEl.classList.add('ql-editor');
        return null;
    }
};


let autoPaginationApi = null;

const PAGE_FLOW_TOL = 4;        // toleransi ukur (px)
const PAGE_FLOW_MAX_STEPS = 24; // pengaman anti-loop per kali jalan
const PAGE_FLOW_MAX_REQUEUES = 200; // pengaman antre-ulang (dokumen panjang)

const pageFlowTimers = new WeakMap();
const pageFlowRequeues = new WeakMap(); // bodyEl -> jumlah antre-ulang aktif
const pageFlowQueue = Promise.resolve();

const __nextFrame = () => new Promise((r) => requestAnimationFrame(() => r()));
const __waitMs = (ms) => new Promise((r) => setTimeout(r, ms));

function __flowDeltaCtor(quill) {
    try {
        const sample = quill.getContents();
        if (sample && typeof sample.constructor === 'function') return sample.constructor;
    } catch (err) { /* coba jalur lain */ }
    try { return Quill.import('delta'); } catch (err) { /* kalah */ }
    return null;
}

// Pecah Delta menjadi blok per baris; op '\n' penutup membawa
// atribut blok (header/list dsb) agar bisa direkonstruksi utuh.
function __splitDeltaIntoBlocks(delta) {
    const blocks = [];
    let cur = [];
    const flush = () => { if (cur.length) { blocks.push(cur); cur = []; } };

    for (const op of (delta.ops || [])) {
        if (typeof op.insert === 'string' && op.insert.indexOf('\n') >= 0) {
            const segs = op.insert.split('\n');
            const attr = op.attributes || null;
            for (let i = 0; i < segs.length; i++) {
                if (segs[i] !== '') {
                    cur.push(attr ? { insert: segs[i], attributes: attr } : { insert: segs[i] });
                }
                if (i < segs.length - 1) {
                    cur.push(attr ? { insert: '\n', attributes: attr } : { insert: '\n' });
                    flush();
                }
            }
        } else {
            cur.push(op);
        }
    }
    flush();
    return blocks;
}

function __opLength(op) {
    if (op.insert == null) return 0;
    return (typeof op.insert === 'string') ? op.insert.length : 1;
}

function __deltaLength(delta) {
    return (delta.ops || []).reduce((n, op) => n + __opLength(op), 0);
}

function __cloneOps(ops) {
    return ops.map((o) => Object.assign({}, o));
}

function __isFloatingKid(kid) {
    try {
        const cs = getComputedStyle(kid);
        return cs.position === 'absolute' || cs.position === 'fixed';
    } catch (err) { return false; }
}

function __looksEmptyBody(bodyEl) {
    try {
        if (bodyEl.querySelector('img, iframe, video')) return false;
        const txt = (bodyEl.innerText || '').replace(/\u200b/g, '').trim();
        return txt === '';
    } catch (err) { return false; }
}

function __contentOverflowPx(quill, boxEl) {
    try {
        const inner = (quill.root && quill.root !== boxEl) ? Math.max(0, quill.root.scrollHeight) : 0;
        const outer = Math.max(boxEl.scrollHeight || 0, inner);
        return outer - (boxEl.clientHeight || 0);
    } catch (err) { return 0; }
}

// Anak (elemen blok) pertama yang melewati batas bawah kertas.
// Mengembalikan -1 kalau struktur tak bisa dipetakan aman.
function __firstOverflowIndex(quill, boxEl) {
    const root = quill.root;
    if (!root || !root.children || !root.children.length) return -1;
    const kids = Array.from(root.children);
    const boxRect = boxEl.getBoundingClientRect();
    const padT = parseFloat(getComputedStyle(boxEl).paddingTop || '0');
    const effBottom = boxRect.top + padT + boxEl.clientHeight - PAGE_FLOW_TOL;
    const baseTop = kids[0].getBoundingClientRect().top;
    for (let i = 0; i < kids.length; i++) {
        if (__isFloatingKid(kids[i])) return -1; // gambar melayang: jangan disentuh
        const bottom = kids[i].getBoundingClientRect().bottom;
        if (bottom - PAGE_FLOW_TOL > effBottom) return i;
    }
    return kids.length; // semua muat (harusnya tak terjadi saat dipanggil)
}

// ---------- MESIN UTAMA: pindah blok antar kertas ----------

let pageFlowChain = Promise.resolve();

function __sessionActive() {
    try {
        return !!document.querySelector('.zone-editing');
    } catch (err) { return false; }
}

function __blockRangeOf(quill, kidEl) {
    try {
        const blot = Quill.find(kidEl);
        if (!blot || typeof blot.length !== 'function') return null;
        const start = blot.offset(quill.scroll);
        const len = blot.length();
        if (!Number.isFinite(start) || !Number.isFinite(len) || len <= 0) return null;
        return { start, len };
    } catch (err) { return null; }
}

async function __resolveTargetBody(sheet, apiCreate) {
    // Kertas berikutnya yang sudah ada?
    const nextSheet = sheet.nextElementSibling;
    if (nextSheet && nextSheet.classList.contains('doc-sheet')) {
        const nb = nextSheet.querySelector('.doc-sheet-body[data-region="body"]');
        if (nb && quillsByRegion.has(nb)) return nb;
    }
    // Belum ada kertas di belakangnya -> minta Alpine membuat satu,
    // lalu tunggu sampai region-nya benar-benar terpasang Quill.
    const uid = sheet.dataset?.pageUid;
    if (!uid || typeof apiCreate !== 'function') return null;
    const created = await apiCreate(uid);
    if (!created) return null;
    for (let i = 0; i < 20; i++) {
        if (quillsByRegion.has(created)) return created;
        await __waitMs(25);
    }
    return quillsByRegion.has(created) ? created : null;
}

async function __flowPass(quill, bodyEl) {
    const sheet = bodyEl.closest('.doc-sheet');
    if (!sheet) return false;

    if (__contentOverflowPx(quill, bodyEl) <= PAGE_FLOW_TOL) return false;

    const idx = __firstOverflowIndex(quill, bodyEl);
    const kids = Array.from(quill.root.children || []);
    if (idx < 0 || idx >= kids.length) return false; // tak bisa dipetakan aman

    const targetBody = await __resolveTargetBody(sheet,
        autoPaginationApi && autoPaginationApi.createPageAfter);
    if (!targetBody || targetBody === bodyEl) return false;
    const targetQ = quillsByRegion.get(targetBody);
    if (!targetQ || !targetQ.isEnabled()) return false;

    const firstRange = __blockRangeOf(quill, kids[idx]);
    if (!firstRange) return false;

    const remaining = kids.slice(idx);
    const bulk = targetQ.getLength() <= 1
        && remaining.length > 0
        && !remaining.some(__isFloatingKid);

    const range = bulk
        ? {
            start: firstRange.start,
            len: Math.max(firstRange.len, quill.getLength() - firstRange.start),
        }
        : firstRange;
    if (!range) return false;

    const DeltaCtor = __flowDeltaCtor(quill);
    const removed = quill.getContents(range.start, range.len);
    if (!DeltaCtor || !removed || !(removed.ops || []).length) return false;

    // Amankan caret pengguna sebelum mutasi
    const selBefore = quill.getSelection();

    quill.deleteText(range.start, range.len, 'silent');

    // Sisipkan DI DEPAN isi kertas berikutnya agar urutan dokumen tetap benar.
    const chg = new DeltaCtor();
    for (const op of removed.ops) {
        chg.push(op.insert == null
            ? { retain: __opLength(op) }
            : JSON.parse(JSON.stringify(op)));
    }
    targetQ.updateContents(chg, 'silent');

    // BUGFIX caret: caret mengikuti kontennya HANYA bila caret/seleksi
    // berada DI DALAM blok yang mengalir (index >= range.start, termasuk
    // di awal blok -- kasus umum: baru menekan Enter lalu baris barunya
    // ikut pindah halaman). Di luar itu JANGAN sentuh seleksi sama sekali:
    // Quill sudah mentransformasi caret otomatis, dan pemangsaan
    // setSelection paksa justru merusak seleksi double-click serta
    // menggeser caret saat pengguna sedang mengetik. Panjang seleksi
    // tetap dipertahankan agar blok kata tidak collapse.
    if (selBefore && selBefore.index >= range.start
        && selBefore.index < range.start + range.len) {
        const off = Math.max(0, selBefore.index - range.start);
        targetQ.setSelection(off, selBefore.length || 0, 'silent');
    }

    // Lanjutkan aliran: kertas berikutnya sekarang bisa meluap juga,
    // jadwalkan pemeriksaannya agar konten merambat sampai muat semua.
    if (autoPaginationApi) __runFlow(targetQ, targetBody);

    notifyDirty();
    return true;
}

function __pullBackPass(quill, bodyEl) {
    const sheet = bodyEl.closest('.doc-sheet');
    if (!sheet) return false;


    if (sheet.dataset?.flowLock) return false;

    // Kertas ini masih meluap -> urusan arah maju, bukan balik.
    if (__contentOverflowPx(quill, bodyEl) > PAGE_FLOW_TOL) return false;

    // Cari kertas berikutnya yang sudah terpasang Quill.
    let nextBody = null;
    let s = sheet.nextElementSibling;
    while (s && s.classList.contains('doc-sheet')) {
        const nb = s.querySelector('.doc-sheet-body[data-region="body"]');
        if (nb && quillsByRegion.has(nb)) { nextBody = nb; break; }
        s = s.nextElementSibling;
    }
    if (!nextBody) return false;
    const nextQ = quillsByRegion.get(nextBody);
    if (!nextQ || !nextQ.isEnabled()) return false;

    const kids = Array.from(quill.root.children || []);
    const nkids = Array.from(nextQ.root.children || []);
    if (!nkids.length) return false;
    const k2 = nkids[0];
    if (__isFloatingKid(k2)) return false;

    // Blok kosong penutup Quill (<p><br></p>, panjang 1) tidak usah ditarik.
    let k2Len = 0;
    try {
        const b2 = Quill.find(k2);
        if (b2 && typeof b2.length === 'function') k2Len = b2.length();
    } catch (err) { k2Len = 0; }
    if (k2Len <= 1) return false;

    // Sisa ruang di bawah blok terakhir kertas ini (px, sudah termasuk toleransi).
    const boxRect = bodyEl.getBoundingClientRect();
    const padT = parseFloat(getComputedStyle(bodyEl).paddingTop || '0');
    const effBottom = boxRect.top + padT + bodyEl.clientHeight - PAGE_FLOW_TOL;
    let baseBottom = boxRect.top + padT;
    for (let i = kids.length - 1; i >= 0; i--) {
        if (__isFloatingKid(kids[i])) continue;
        baseBottom = Math.max(baseBottom, kids[i].getBoundingClientRect().bottom);
        break;
    }

    // Harus muat PENUH dengan margin ekstra supaya arah maju tidak
    // langsung mendorongnya balik (tidak ada bolak-balik).
    const h2 = k2.getBoundingClientRect().height;
    if (baseBottom + h2 > effBottom - PAGE_FLOW_TOL) return false;

    const range = __blockRangeOf(nextQ, k2);
    if (!range) return false;
    const DeltaCtor = __flowDeltaCtor(quill);
    const removed = nextQ.getContents(range.start, range.len);
    if (!DeltaCtor || !removed || !(removed.ops || []).length) return false;

    // Amankan caret pengguna: HANYA caret/seleksi yang berada DI DALAM blok
    // yang ditarik yang ikut naik ke kertas ini. Seleksi lain JANGAN disentuh
    // -- Quill sudah mentransformasi otomatis, dan setSelection paksa justru
    // merusak seleksi double-click / menggeser caret saat mengetik.
    const nextSelBefore = nextQ.getSelection();

    nextQ.deleteText(range.start, range.len, 'silent');

    const lenBefore = quill.getLength();
    const chg = new DeltaCtor();
    chg.retain(Math.max(0, quill.getLength()));
    for (const op of removed.ops) {
        chg.push(op.insert == null
            ? { retain: __opLength(op) }
            : JSON.parse(JSON.stringify(op)));
    }
    quill.updateContents(chg, 'silent');

    // Caret/seleksi di dalam blok yang ditarik -> ikut pindah ke ekor kertas
    // ini, dengan offset dan panjang seleksi yang sama.
    if (nextSelBefore && nextSelBefore.index >= range.start
        && nextSelBefore.index < range.start + range.len) {
        const ni = Math.max(0, Math.min(
            lenBefore + (nextSelBefore.index - range.start),
            Math.max(0, quill.getLength() - 1)
        ));
        quill.setSelection(ni, nextSelBefore.length || 0, 'silent');
    }

    notifyDirty();
    return true;
}

function __runFlow(quill, bodyEl) {
    if (!autoPaginationApi) return; // bridge belum siap
    const job = pageFlowChain
        .then(async () => {
            let movedAny = false;
            for (let step = 0; step < PAGE_FLOW_MAX_STEPS; step++) {
                if (__sessionActive()) break;
                if (!document.body.contains(bodyEl)) break;
                let moved = await __flowPass(quill, bodyEl);
                if (!moved) {
                    // Ruang longgar -> tarik blok atas kertas berikutnya ke sini.
                    moved = await __pullBackPass(quill, bodyEl);
                }
                if (!moved) break;
                movedAny = true;
                await __nextFrame();
            }

            const stillOverflowing = document.body.contains(bodyEl)
                && !__sessionActive()
                && __contentOverflowPx(quill, bodyEl) > PAGE_FLOW_TOL;

            if (stillOverflowing && movedAny) {
                const n = (pageFlowRequeues.get(bodyEl) || 0) + 1;
                if (n <= PAGE_FLOW_MAX_REQUEUES) {
                    pageFlowRequeues.set(bodyEl, n);
                    __runFlow(quill, bodyEl);
                    return;
                }
            }
            pageFlowRequeues.delete(bodyEl);
        })
        .catch((err) => console.warn('[DocQuill] Paginasi otomatis dilewati:', err));
    pageFlowChain = job;
}

function bindPageOverflowWatch(quill, regionEl) {
    if (!quill || regionEl.__pageFlowBound) return;
    regionEl.__pageFlowBound = true;

    let timer = 0;
    const schedule = () => {
        clearTimeout(timer);
        timer = setTimeout(() => __runFlow(quill, regionEl), 140);
    };

    quill.on('text-change', (_d, _o, source) => {
        if (source === 'silent') return;
        if (regionEl.dataset?.region !== 'body') return;
        schedule();
    });

    window.addEventListener('resize', () => schedule(), { passive: true });

    // Pemeriksaan awal saat kertas baru menyala / dokumen dibuka.
    setTimeout(() => __runFlow(quill, regionEl), 350);
}



window.initBodyEditor = function (rootSelector, onSync = null) {
    try {
        const root = document.querySelector(rootSelector);
        if (!root) {
            console.error('[DocQuill] Root tidak ditemukan:', rootSelector);
            return;
        }

        window.__docEditorSync = onSync;

        // Shim untuk sistem gambar
        const shim = makeEditorShim(root);
        if (!registeredImageToolEditors.some((ed) => ed.rootEl === root)) {
            registeredImageToolEditors.push(shim);
        }

        // Satu instance Quill per region
        const regions = root.querySelectorAll('.doc-sheet-body, .doc-sheet-header, .doc-sheet-footer');
        regions.forEach(attachQuillToRegion);

        // Klik gambar yang terlihat -> langsung buka mode edit gambar
        root.addEventListener('click', (e) => {
            if (e.target.nodeName !== 'IMG') return;
            if (e.target.closest('.doc-signature')) return;
            showImageTools(shim, e.target);
        });

        bindToolbar();

        const firstBody = root.querySelector('.doc-sheet-body[data-region="body"]');
        if (firstBody) {
            const firstQ = quillsByRegion.get(firstBody);
            if (firstQ) activeQuill = firstQ;
        }
        refreshToolbarStates();

        console.log('[DocQuill] Siap —', regions.length, 'region.',
            root.querySelectorAll('.ql-editor').length, 'editor aktif.');
    } catch (err) {
        console.error('[DocQuill] initBodyEditor gagal total:', err);
    }
};


// API PUBLIK UNTUK BLADE


window.DocQuill = {
    __version: 'hf-10-flow',

    attachRegion: attachQuillToRegion,

    getHtml: (regionEl) => {
        if (!regionEl) return '';
        const q = quillsByRegion.get(regionEl);
        let html = q ? q.root.innerHTML : regionEl.innerHTML;
        regionEl.querySelectorAll(':scope > img').forEach((im) => {
            html += im.outerHTML;
        });
        return html;
    },

    getActive: getActiveQuill,


    forgetRegion: unregisterRegion,

    syncAllMirrors: () => {
        ['header', 'footer'].forEach((role) => {
            const list = mirrorRegistry[role];
            if (list.length < 2) return;
            const html = list[0].q.root.innerHTML;
            mirroringInProgress = true;
            try {
                list.slice(1).forEach((m) => {
                    const sel = m.q.getSelection();
                    if (sel) m.lastCaret = sel.index;
                    m.q.clipboard.dangerouslyPasteHTML(html);
                    const maxIndex = Math.max(0, m.q.getLength() - 1);
                    m.q.setSelection(Math.min(m.lastCaret, maxIndex), 'silent');
                });
            } finally {
                mirroringInProgress = false;
            }
        });
    },

    setZonesEnabled: (role, enabled) => {
        mirrorRegistry[role]?.forEach((m) => {
            if (enabled) m.q.enable();
            else m.q.enable(false);
            m.regionEl.classList.toggle('zone-editing', !!enabled);
        });
    },

    // Fokus ke satu zona tertentu (mis. kursor di akhir konten)
    focusZone: (regionEl) => {
        const q = quillsByRegion.get(regionEl);
        if (!q) return;
        q.setSelection(Math.max(0, q.getLength() - 1));
    },

    // Aktifkan editor body lalu fokuskan caret di akhir konten.
    // Dipakai pasca-hapus halaman supaya caret pindah ke halaman sebelumnya.
    focusBodyEnd: (regionEl) => {
        const q = quillsByRegion.get(regionEl);
        if (!q) return;
        if (!q.isEnabled()) q.enable();
        q.setSelection(Math.max(0, q.getLength() - 1), 'silent');
        q.focus();
    },

    // Jaminan: semua isi dokumen (body) selalu bisa diketik di luar sesi.
    // Mengembalikan jumlah body yang sempat mati lalu dipulihkan.
    ensureBodyEditable: () => {
        let revived = 0;
        quillsByRegion.forEach((q, el) => {
            if (el.dataset?.region === 'body' && !q.isEnabled()) {
                q.enable();
                revived++;
                        }
        });
        return revived;
    },

    enableAutoPagination: (api) => {
        autoPaginationApi = api || null;
        quillsByRegion.forEach((qq, el) => {
            if (el.dataset?.region === 'body') {
                bindPageOverflowWatch(qq, el);
            }
        });
    },

    clickAndType: (regionEl, clientX, clientY) => {
        console.log('[clickAndType] dipanggil', { clientX, clientY });
        const q = quillsByRegion.get(regionEl);
        if (!q || !q.isEnabled()) {
            console.log('[clickAndType] BAIL: quill gak ketemu/gak aktif');
            return false;
        }

        const target = document.elementFromPoint(clientX, clientY);
        if (target && target.closest('img, table, td, th, hr, button, a')) return false;

        const rootRect = q.root.getBoundingClientRect();
        const endIndex = Math.max(0, q.getLength() - 1);

        const endBounds = q.getBounds(endIndex);
        if (!endBounds) {
            console.log('[clickAndType] BAIL: getBounds mengembalikan null');
            return false;
        }

        const endBottomPage = endBounds.bottom;
        const endLeftPage = endBounds.left;
        const lineHeight = endBounds.height || 24;

        let linesNeeded = 0;
        let spacesNeeded = 0;

        if (clientY < endBottomPage - lineHeight + 2) {
            console.log('[clickAndType] BAIL: klik masih di baris yang sama', {clientY, endBottomPage, lineHeight});
            return false;
        }

        if (clientY <= endBottomPage + 2) {
            spacesNeeded = Math.max(0, Math.round((clientX - endLeftPage) / 7));
        } else {
            linesNeeded = Math.max(1, Math.round((clientY - endBottomPage) / lineHeight));
            spacesNeeded = Math.max(0, Math.round((clientX - rootRect.left) / 7));
        }


        const insertText = '\n'.repeat(linesNeeded) + ' '.repeat(spacesNeeded);
        const finalIndex = endIndex + insertText.length;

        if (insertText.length > 0) {
            q.insertText(endIndex, insertText, 'user');
        }

        console.log('[clickAndType] EKSEKUSI', { linesNeeded, spacesNeeded, finalIndex });

        setTimeout(() => {
            try { window.getSelection()?.removeAllRanges(); } catch (err) { /* noop */ }
            q.focus();
            q.setSelection(finalIndex, 0, 'user');
            console.log('[clickAndType] Caret akhir di index', finalIndex, '/', q.getLength());
        }, 0);

        notifyDirty();
        return true;
    },
};