// =========================================
// QUILL EDITOR — pengganti TinyMCE
// Satu instance Quill per region kertas
// (.doc-sheet-header / body / footer),
// dengan SATU toolbar bersama di atas.
// =========================================

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
SizeAttributor.whitelist = ['10px', '12px', '14px', '16px', '18px', '20px', '24px', '28px', '32px'];
Quill.register(SizeAttributor, true);

// ---- Gambar: pertahankan attribute style & class
//      (dibutuhkan fitur posisi gambar depan/belakang teks) ----
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
    'font', 'size', 'color', 'background',
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

// =========================================
// REGISTRY EDITOR (shim agar seluruh sistem
// gambar lama tetap bekerja tanpa TinyMCE)
// =========================================

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

// =========================================
// IMAGE LAYOUT TOOLS
// (bubble ⚓ + titik resize + posisi depan/belakang teks)
// =========================================

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

const applyImageLayout = (img, layout) => {
    const editor = activeEditor;
    if (!editor) return;

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

// =========================================
// DRAG ANGKAT & JATUHKAN UNTUK GAMBAR BIASA
// Tekan gambar + geser -> gambar "terangkat" mengikuti kursor.
// Lepas di atas kertas -> berhenti PERSIS di titik pelepasan
//                         (otomatis jadi gambar floating).
// Lepas di luar kertas -> kembali ke tempat & gaya semula.
// =========================================

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

// =========================================
// TITIK SISIP DI MANA SAJA (ala Microsoft Word):
// klik di mana pun pada kertas -> caret pindah ke
// baris terdekat yang bisa diketik.
// =========================================

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

// Tempatkan caret ke baris terdekat dari titik (x, y) layar.
const caretToNearestLine = (x, y) => {
    // 1) kumpulkan semua editor yang sedang bisa diketik
    const editors = [];
    quillsByRegion.forEach((q, regionEl) => {
        if (!q.isEnabled()) return;
        const er = regionEl.querySelector('.ql-editor');
        if (er) editors.push(er);
    });
    if (!editors.length) return false;

    // 2) pilih editor terdekat secara vertikal terhadap titik klik
    let target = null;
    let bestDy = Infinity;
    for (const er of editors) {
        const r = er.getBoundingClientRect();
        const dy = y < r.top ? r.top - y : (y > r.bottom ? y - r.bottom : 0);
        if (dy < bestDy) {
            bestDy = dy;
            target = er;
        }
    }
    if (!target) return false;

    const focusAndSet = (node, offset) => {
        target.focus({ preventScroll: false });
        const sel = window.getSelection();
        sel.removeAllRanges();
        const rg = document.createRange();
        rg.setStart(node, Math.min(offset, node.nodeValue ? node.nodeValue.length : 0));
        rg.collapse(true);
        sel.addRange(rg);
        return true;
    };

    // 3a) presisi: pakai jawaban browser HANYA bila ia menunjuk NODE TEKS
    //     nyata. Bila titik berada di area kosong, Chrome sering menjawab
    //     elemen kontainer dengan offset 0 (= awal dokumen/baris pertama!)
    //     — hasil seperti itu dibuang dan dipakai pencarian baris terdekat.
    let native = null;
    try {
        if (document.caretRangeFromPoint) {
            native = document.caretRangeFromPoint(x, y);
        } else if (document.caretPositionFromPoint) {
            const p = document.caretPositionFromPoint(x, y);
            if (p) {
                native = document.createRange();
                native.setStart(p.offsetNode, p.offset);
            }
        }
    } catch (_) {
        native = null;
    }
    if (
        native &&
        native.startContainer &&
        native.startContainer.nodeType === Node.TEXT_NODE &&
        target.contains(native.startContainer)
    ) {
        return focusAndSet(native.startContainer, native.startOffset);
    }

    // 3b) cadangan geometris: baris terdekat, caret di awal/akhir baris
    const line = nearestLine(target, x, y);
    if (!line) return false;
    const offset = x < line.left + 1 ? line.start : line.end;
    return focusAndSet(line.node, offset);
};

// =========================================
// LISTENER GLOBAL (dipasang sekali)
// =========================================

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
    // resolver ini (capture phase), sehingga caret SELALU mengikuti titik klik —
    // termasuk saat mengeklik baris kedua/ketiga dan area kosong sekitarnya.
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

            // Hitung & pasang caret tepat di titik klik (fallback: baris terdekat)
            if (caretToNearestLine(e.clientX, e.clientY)) {
                e.preventDefault(); // kita yang memasang caret
            }
        },
        true // capture: jalan paling awal, tidak bisa diganggu handler lain
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
            // Pulihkan seleksi teks yang dikunci saat resize dimulai.
            // Tanpa ini, user-select:none menempel selamanya dan mematikan
            // caret/ketikan di seluruh dokumen secara diam-diam.
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

// =========================================
// TOOLBAR QUILL (satu toolbar bersama untuk semua region)
// =========================================

let activeQuill = null;
const quillsByRegion = new Map();

let hiddenImageInput = null;

const getActiveQuill = () => {
    if (activeQuill && activeQuill.isEnabled()) return activeQuill;
    // Fallback: instance pertama yang MASIH AKTIF —
    // jangan pernah memilih zona terkunci (format() di editor mati = no-op diam)
    for (const q of quillsByRegion.values()) {
        if (q.isEnabled()) return q;
    }
    return null;
};

const TOOLBAR_TOGGLES = ['bold', 'italic', 'underline', 'strike'];

const refreshToolbarStates = () => {
    const q = activeQuill;
    if (!q) return;
    const sel = q.getSelection();
    if (!sel) return;

    const fmt = q.getFormat(sel.index, sel.length);

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
            btn.classList.toggle('active', fmt.list === 'ordered');
        }
    });

    document.querySelectorAll('#body-toolbar-container [data-align]').forEach((btn) => {
        btn.classList.toggle('active', (fmt.align || 'left') === btn.dataset.align);
    });

    const blockSel = document.getElementById('tb-block');
    if (blockSel) blockSel.value = fmt.header ? String(fmt.header) : '';
    const fontSel = document.getElementById('tb-font');
    if (fontSel) fontSel.value = fmt.font || '';
    const sizeSel = document.getElementById('tb-size');
    if (sizeSel) sizeSel.value = fmt.size || '';
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
        case 'undo':
            q.history.undo();
            break;
        case 'redo':
            q.history.redo();
            break;
        case 'bold':
        case 'italic':
        case 'underline':
        case 'strike':
            q.format(cmd, !q.getFormat(sel)[cmd]);
            break;
        case 'superscript':
        case 'subscript': {
            // Nama format Quill yang benar adalah 'script' ('super' | 'sub'),
            // bukan 'superscript'/'subscript'.
            const scriptKey = cmd === 'superscript' ? 'super' : 'sub';
            q.format('script', q.getFormat(sel).script === scriptKey ? false : scriptKey);
            break;
        }
        case 'bullist': {
            const cur = q.getFormat(sel).list === 'bullet';
            q.format('list', cur ? false : 'bullet');
            break;
        }
        case 'numlist': {
            const cur = q.getFormat(sel).list === 'ordered';
            q.format('list', cur ? false : 'ordered');
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

            // 'left' BUKAN nilai whitelist Quill ('center'|'right'|'justify').
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
    sizeSel?.addEventListener('change', () => {
        const q = getActiveQuill();
        if (!q) return;
        if (!q.getSelection(true)) return;
        q.format('size', sizeSel.value || false);
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
};

// =========================================
// HEADER/FOOTER MIRROR (ala Microsoft Word):
// semua zona header berbagi SATU konten,
// semua zona footer berbagi SATU konten.
// Edit di halaman mana pun -> halaman lain ikut.
// =========================================

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

// Pasang Quill pada satu region kertas.
// Pola aman: buat DIV HOST baru yang 100% dimiliki Quill,
// sehingga elemen region kertas TIDAK pernah dimutasi Quill.
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
            modules: { toolbar: false },
            formats: ALLOWED_FORMATS,
        });

        if (existingHtml.trim()) {
            q.clipboard.dangerouslyPasteHTML(existingHtml);
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

        q.on('selection-change', (range) => {
            if (range) {
                activeQuill = q;
                refreshToolbarStates();
            }
        });

        quillsByRegion.set(regionEl, q);
        return q;
    } catch (err) {
        // Kegagalan Quil TIDAK BOLEH membuat kertas hilang:
        // pulihkan konten asli + jadikan region biasa bisa diketik
        console.error('[DocQuill] Gagal memasang editor pada region:', err);
        regionEl.dataset.quillReady = '';
        regionEl.innerHTML = existingHtml || '<p><br></p>';
        regionEl.setAttribute('contenteditable', 'true');
        regionEl.classList.add('ql-editor');
        return null;
    }
};

// =========================================
// INIT UTAMA — dipanggil dari blade
// =========================================

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

        console.log('[DocQuill] Siap —', regions.length, 'region,',
            root.querySelectorAll('.ql-editor').length, 'editor aktif.');
    } catch (err) {
        console.error('[DocQuill] initBodyEditor gagal total:', err);
    }
};

// =========================================
// API PUBLIK UNTUK BLADE
// =========================================

window.DocQuill = {
    // Penanda versi untuk deteksi aset usang dari blade
    __version: 'hf-5',

    // Pasang editor pada region baru (misal saat tambah halaman)
    attachRegion: attachQuillToRegion,

    // Ambil HTML bersih dari sebuah region
    getHtml: (regionEl) => {
        if (!regionEl) return '';
        const q = quillsByRegion.get(regionEl);
        return q ? q.root.innerHTML : regionEl.innerHTML;
    },

    getActive: getActiveQuill,

    // ----- Header/Footer ala Word -----

    // Buang instance region yang halamannya dihapus
    forgetRegion: unregisterRegion,

    // Samakan semua zona header/footer dengan konten instance pertama
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

    // Aktif/nonaktifkan semua zona satu role (sesi edit double-click)
    setZonesEnabled: (role, enabled) => {
        mirrorRegistry[role]?.forEach((m) => {
            if (enabled) m.q.enable();
            else m.q.enable(false);
            // Kelas dipasang LANGSUNG di elemen zona agar garis pembatas
            // selalu tampil tanpa bergantung pada class induk mana pun.
            m.regionEl.classList.toggle('zone-editing', !!enabled);
        });
    },

    // Fokus ke satu zona tertentu (taruh kursor di akhir konten)
    focusZone: (regionEl) => {
        const q = quillsByRegion.get(regionEl);
        if (!q) return;
        q.setSelection(Math.max(0, q.getLength() - 1));
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
};









