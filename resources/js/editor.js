import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import QuillTableBetter, { ToolbarTable } from 'quill-table-better';
import 'quill-table-better/dist/quill-table-better.css';

QuillTableBetter.register();

const noop = () => {};
const tableBetterStub = {
    insertTable: noop, insertRowAbove: noop, insertRowBelow: noop,
    insertColumnLeft: noop, insertColumnRight: noop,
    deleteRow: noop, deleteColumn: noop, deleteTable: noop,
    mergeCells: noop, unmergeCells: noop,
};
const SafeQuillTableBetter = function(quill, options) {
    try {
        return new QuillTableBetter(quill, options);
    } catch (err) {
        console.warn('[TableBetter] Initialization failed, using fallback:', err);
        return tableBetterStub;
    }
};
SafeQuillTableBetter.keyboardBindings = QuillTableBetter.keyboardBindings;
Quill.register({ 'modules/table-better': SafeQuillTableBetter }, true);

const FontAttributor = Quill.import('attributors/style/font');
FontAttributor.whitelist = [
    'Arial', 'Georgia', 'Times New Roman', 'Courier New', 'Verdana',
];
Quill.register(FontAttributor, true);

const SizeAttributor = Quill.import('attributors/style/size');
SizeAttributor.whitelist = null;
Quill.register(SizeAttributor, true);


const Parchment = Quill.import('parchment');
const LineHeightStyle = new Parchment.StyleAttributor('lineheight', 'line-height', {
    scope: Parchment.Scope.BLOCK,
    whitelist: ['1', '1.15', '1.5', '2', '2.5'],
});
Quill.register(LineHeightStyle, true);

const ListStyleAttributor = new Parchment.ClassAttributor('liststyle', 'ql-liststyle', {
    scope: Parchment.Scope.BLOCK,
    whitelist: ['alpha'],
});
Quill.register(ListStyleAttributor, true);

const UNDERLINE_STYLE_WHITELIST = [
    'solid', 'double', 'thick',
    'dotted', 'dashed', 'dotdashed', 'dotdotdashed', 'wavy',
];
const UnderlineStyle = new Parchment.ClassAttributor('ul', 'ul', {
    scope: Parchment.Scope.INLINE,
    whitelist: UNDERLINE_STYLE_WHITELIST,
});
Quill.register(UnderlineStyle, true);

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

const ALLOWED_FORMATS = [
    'header', 'bold', 'italic', 'underline', 'strike',
    'ul',
    'script', 'list', 'align', 'indent',
    'blockquote', 'link', 'image', 'hr',
    'font', 'size', 'color', 'background', 'lineheight', 'liststyle',
    'table', 'table-header', 'table-cell', 'table-cell-block',
    'table-th', 'table-th-block', 'table-row', 'table-th-row',
    'table-body', 'table-thead', 'table-temporary', 'table-col',
    'table-colgroup', 'table-container', 'table-list', 'table-list-container',
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


let registeredImageToolEditors = [];

window.__docEditorDirty = null;

const notifyDirty = () => {
    if (typeof window.__docEditorDirty === 'function') {
        window.__docEditorDirty();
    }
};


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
let resizeAnchorX = 0, resizeAnchorY = 0;      
let resizeBaseVecX = 0, resizeBaseVecY = 0;    
let resizeStartMarginLeft = 0, resizeStartMarginTop = 0;
let watchTimer = null;
let flowDragArmed = false;
let isDraggingFlowImage = false;
let flowStartX = 0, flowStartY = 0;
let flowDragOffsetX = 0, flowDragOffsetY = 0;
let flowImgOriginalCssText = '';
let flowImgOriginalParent = null;
let flowImgOriginalNext = null;
let flowImgOriginalRegion = null;
let flowDragSourceImg = null;
let floatingImg = null;
let floatingRegion = null;
let floatingDragArmed = false;
let isDraggingFloating = false;
let floatStartX = 0, floatStartY = 0;
let floatBaseLeft = 0, floatBaseTop = 0;
let imgToggleGuard = false;
let lastImgToggleGuard = false;

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

const clampPosToRegion = (regionEl, left, top, w, h) => {
    const r = regionEl?.getBoundingClientRect?.();
    if (!r || !r.width || !r.height) return [Math.max(0, left), Math.max(0, top)];

    // Header & footer boleh "menjelajah" seluruh kertas agar gambar bisa
    // ditarik ke margin atas/bawah dan pojok kertas (seperti di halaman
    // biasa). Body tetap dikunci di dalam zona karena tubuh halaman
    // memakai overflow:hidden.
    const role = regionEl.dataset?.region;
    const isZone = role === 'header' || role === 'footer';
    const sheet = isZone ? regionEl.closest('.doc-sheet') : null;
    const sRect = sheet ? sheet.getBoundingClientRect() : null;

    let minX = 0, minY = 0;
    let maxX = r.width - w;
    let maxY = r.height - h;

    if (sRect) {
        // Offset posisi region relatif terhadap kertas (jarak margin).
        const offX = r.left - sRect.left;
        const offY = r.top - sRect.top;
        minX = -offX;
        maxX = sRect.width - w - offX;

        if (role === 'header') {
            // Atas tetap bebas ke margin/pojok kertas (fix sebelumnya).
            minY = -offY;
            // Batas bawah = tidak boleh melewati garis pemisah header->body.
            // Sertakan padding-bottom header (8px) agar berhenti di atas garis.
            const padB = parseFloat(getComputedStyle(regionEl).paddingBottom) || 0;
            const bottomMax = r.height - padB - h;
            // Kalau gambar terlalu tinggi untuk header, biarkan overflow ke
            // ATAS (terpotong di tepi kertas), BUKAN bleber ke body.
            minY = Math.min(minY, bottomMax);
            maxY = bottomMax;
        } else if (role === 'footer') {
            // Bawah tetap bebas ke margin bawah kertas.
            maxY = sRect.height - h - offY;
            // Batas atas = tidak boleh melewati garis pemisah footer<-body.
            const padT = parseFloat(getComputedStyle(regionEl).paddingTop) || 0;
            const topBound = padT;
            // Kalau terlalu tinggi, overflow ke BAWAH (terpotong di tepi
            // kertas), BUKAN bleber ke body.
            minY = topBound;
            maxY = Math.max(maxY, topBound);
        } else {
            // Body: tetap terkunci di dalam zona (overflow:hidden di kertas).
            minY = -offY;
            maxY = sRect.height - h - offY;
        }
    }

    const cx = Math.min(Math.max(left, minX), Math.max(minX, maxX));
    const cy = Math.min(Math.max(top, minY), Math.max(minY, maxY));
    return [cx, cy];
};

const fitRegionToImage = (region) => {
    if (!region || region.nodeType !== 1) return;
    try {
        const isSection =
            region.classList?.contains('doc-sheet-header') ||
            region.classList?.contains('doc-sheet-body') ||
            region.classList?.contains('doc-sheet-footer');
        if (!isSection) return;

        const imgs = Array.from(region.querySelectorAll(':scope > img'))
            .filter((im) => getComputedStyle(im).position === 'absolute');
        if (!imgs.length) return;

        const padB = parseFloat(getComputedStyle(region).paddingBottom) || 0;
        const rRect = region.getBoundingClientRect();
        let maxBottom = 0;
        imgs.forEach((im) => {
            const iRect = im.getBoundingClientRect();
            maxBottom = Math.max(maxBottom, iRect.bottom - rRect.top);
        });
        const want = Math.ceil(maxBottom + padB);
        const prevMin = region.style.minHeight;
        region.style.minHeight = '';
        const natural = region.offsetHeight;
        region.style.minHeight = prevMin || '';

        const target = Math.max(natural, want);
        if (target !== region.offsetHeight) {
            region.style.minHeight = target + 'px';
        }
    } catch (err) { /* noop */ }
};

const removeImageTools = () => {
    cancelAnimationFrame(watchTimer);
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
    floatingImg = null;
    floatingRegion = null;
    floatingDragArmed = false;
    isDraggingFloating = false;
    isResizingImage = false;
};

const positionImageTools = () => {
    if (!activeImage) return;

    if (!activeImage.isConnected) return;

    const rect = activeImage.getBoundingClientRect();

    if (!rect.width && !rect.height) return;

    if (bubbleEl) {
        bubbleEl.style.left = (rect.right - 12) + 'px';
        bubbleEl.style.top = (rect.top - 12) + 'px';
    }

    if (removeBtnEl) {
        removeBtnEl.style.left = (rect.right - 12 - 32) + 'px';
        removeBtnEl.style.top = (rect.top - 12) + 'px';
    }

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

    const prevLayout = getImageLayout(img);
    const prevFloat =
        prevLayout === 'square' ? getComputedStyle(img).cssFloat : 'none';

    const oldParent = img.parentElement;

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
        const imgRect = img.getBoundingClientRect();
        const regionRect = region.getBoundingClientRect();
        const [freeLeft, freeTop] = clampPosToRegion(
            region,
            imgRect.left - regionRect.left,
            imgRect.top - regionRect.top,
            imgRect.width,
            imgRect.height
        );
        const left = Math.round(freeLeft);
        const top = Math.round(freeTop);

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

        fitRegionToImage(region);
    } else if (layout === 'inline' || layout === 'square' || layout === 'topbottom') {
        returnImageToFlow(editor, img);

        if (layout === 'inline') {
            img.style.display = 'inline';
            img.style.verticalAlign = 'middle';
        } else if (layout === 'square') {
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

    const cs = getComputedStyle(activeImage);
    resizeStartMarginLeft = parseFloat(cs.marginLeft) || 0;
    resizeStartMarginTop = parseFloat(cs.marginTop) || 0;

    document.body.style.userSelect = 'none';
};

const showImageTools = (editor, img) => {
    if (activeImage !== img) lastImgToggleGuard = true;
    if (activeImage === img) return;
    removeImageTools();

    activeImage = img;
    activeEditor = editor;

    bubbleEl = document.createElement('div');
    bubbleEl.innerHTML =
        '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" ' +
        'viewBox="0 0 16 16" style="display:block">' +
        '<path fill-rule="evenodd" d="M2 12.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5m0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5m0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5m0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>' +
        '</svg>';
    bubbleEl.title = 'Layout Options';
    bubbleEl.style.cssText =
        'position:fixed;z-index:999999;width:26px;height:26px;border-radius:6px;background:#1B2A4A;' +
        'color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;' +
        'box-shadow:0 2px 6px rgba(0,0,0,.25);padding:0;line-height:0;';
    document.body.appendChild(bubbleEl);

    bubbleEl.addEventListener('click', (e) => {
        e.stopPropagation();
        panelEl ? (panelEl.remove(), panelEl = null) : buildPanel();
    });

    // Tombol hapus cepat di samping anchor
    removeBtnEl = document.createElement('div');
    removeBtnEl.title = 'Hapus gambar';
    removeBtnEl.style.cssText =
        'position:fixed;z-index:999999;width:26px;height:26px;border-radius:6px;background:#dc2626;' +
        'color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;' +
        'box-shadow:0 2px 6px rgba(0,0,0,.25);padding:0;line-height:0;';
    removeBtnEl.innerHTML =
        '<svg width="12" height="12" viewBox="0 0 12 12" style="display:block" ' +
        'stroke="#fff" stroke-width="2" stroke-linecap="round">' +
        '<line x1="1.5" y1="1.5" x2="10.5" y2="10.5"/>' +
        '<line x1="10.5" y1="1.5" x2="1.5" y2="10.5"/>' +
        '</svg>';
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

    let disconnectedSince = 0;

    const loop = () => {
        if (!activeImage) return;


        if (!activeImage.isConnected) {
            if (!disconnectedSince) disconnectedSince = Date.now();
            if (Date.now() - disconnectedSince > 3000) {
                removeImageTools();
                return;
            }
        } else {
            disconnectedSince = 0;
            positionImageTools();
        }

        watchTimer = requestAnimationFrame(loop);
    };
    watchTimer = requestAnimationFrame(loop);
};

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

    const origRegion = (flowImgOriginalRegion && flowImgOriginalRegion.isConnected)
        ? flowImgOriginalRegion
        : document.querySelector('#document-editor .doc-sheet-body');
    if (!origRegion) return;

    const mount = origRegion.querySelector('.ql-editor') || origRegion;
    mount.insertBefore(img, mount.firstChild);
};

const regionTerdekat = (sheet, x, y) => {
    let best = null;
    let bestDist = Infinity;
    sheet.querySelectorAll(FLOAT_REGION_SELECTOR).forEach((reg) => {
        const r = reg.getBoundingClientRect();
        const dx = Math.max(r.left - x, 0, x - r.right);
        const dy = Math.max(r.top - y, 0, y - r.bottom);
        const dist = dx * dx + dy * dy;
        if (dist < bestDist) {
            bestDist = dist;
            best = reg;
        }
    });
    return best;
};

const cariRegionTitik = (x, y, skipEl = null) => {
    try {
        const stack = document.elementsFromPoint(x, y) || [];
        for (const el of stack) {
            if (el === skipEl) continue;
            if (!el.closest) continue;
            const region = el.closest(FLOAT_REGION_SELECTOR);
            if (region) return region;
            const sheet = el.closest('.doc-sheet');
            if (sheet) return regionTerdekat(sheet, x, y);
        }
    } catch (err) {
        /* noop */
    }
    return null;
};


const cariRegionUntukGambar = (img, e) => {
    let best = null;
    let bestArea = 0;
    try {
        const iRect = img.getBoundingClientRect();

        const topCenter = { x: iRect.left + iRect.width / 2, y: iRect.top + 1 };
        const bottomCenter = { x: iRect.left + iRect.width / 2, y: iRect.bottom - 1 };

        for (const reg of document.querySelectorAll(FLOAT_REGION_SELECTOR)) {
            if (!reg.isConnected) continue;
            const role = reg.dataset?.region;
            const probe = role === 'header' ? topCenter
                : role === 'footer' ? bottomCenter : null;
            if (!probe) continue;
            const rRect = reg.getBoundingClientRect();
            if (probe.x >= rRect.left && probe.x <= rRect.right &&
                probe.y >= rRect.top && probe.y <= rRect.bottom) {
                return reg;
            }
        }

        document.querySelectorAll(FLOAT_REGION_SELECTOR).forEach((reg) => {
            if (!reg.isConnected) return;
            const rRect = reg.getBoundingClientRect();
            const w = Math.min(iRect.right, rRect.right) -
                Math.max(iRect.left, rRect.left);
            const h = Math.min(iRect.bottom, rRect.bottom) -
                Math.max(iRect.top, rRect.top);
            if (w <= 0 || h <= 0) return;
            const area = w * h;
            if (area > bestArea) {
                bestArea = area;
                best = reg;
            }
        });
    } catch (err) {
        best = null;
    }
    return best || cariRegionTitik(e.clientX, e.clientY, img);
};

const selesaiFlowDrag = (e) => {
    const img = flowDragSourceImg;
    flowDragSourceImg = null;
    if (!img) return;

    const ghostLeft = e.clientX - flowDragOffsetX;
    const ghostTop = e.clientY - flowDragOffsetY;
    const ghostW = img.offsetWidth;
    const ghostH = img.offsetHeight;

    const region = cariRegionUntukGambar(img, e);

    img.style.position = '';
    img.style.margin = '';
    img.style.zIndex = '';
    img.style.pointerEvents = '';
    img.style.cursor = '';
    img.style.maxWidth = '';

    if (region && region.isConnected) {
        const rRect = region.getBoundingClientRect();
        const [left, top] = clampPosToRegion(
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
        img.style.left = '';
        img.style.top = '';
        kembalikanKePosisiSemula(img);
    }

    notifyDirty();
    positionImageTools();
};

const pindahkanFloatingKeRegion = (img, e) => {
    const target = cariRegionTitik(e.clientX, e.clientY, img);

    if (!target || !target.isConnected) return;
    if (target === img.closest(FLOAT_REGION_SELECTOR)) return;

    const iRect = img.getBoundingClientRect();
    const tRect = target.getBoundingClientRect();
    const [left, top] = clampPosToRegion(
        target,
        iRect.left - tRect.left,
        iRect.top - tRect.top,
        img.offsetWidth,
        img.offsetHeight
    );

    img.style.left = left + 'px';
    img.style.top = top + 'px';
    target.appendChild(img);
};

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

const SLOT_SELECTOR = 'p, h1, h2, h3, h4, h5, h6, li, blockquote, pre, hr, img';

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

    if (!(el.textContent || '').trim()) return base;
    const line = nearestLine(el, x, y);
    if (line) {
        const idx = domPosToQuillIndex(
            q, line.node,
            x < line.left + 1 ? line.start : line.end
        );
        if (idx != null) return idx;
    }

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

    if (y < first.top) return 0;
    if (y > last.bottom) return Math.max(0, q.getLength() - 1);

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

// HMR/eval-ulang aman: handler lama dilepas dulu, lalu dipasang ulang.
if (typeof window.__imageToolsUnbind === 'function') window.__imageToolsUnbind();
const __imageToolsUnbinds = [];
window.__imageToolsUnbind = () => {
    while (__imageToolsUnbinds.length) {
        try { __imageToolsUnbinds.pop()(); } catch (err) { /* noop */ }
    }
};
const __bind = (target, type, fn, opts) => {
    target.addEventListener(type, fn, opts);
    __imageToolsUnbinds.push(() => target.removeEventListener(type, fn, opts));
};

    __bind(document, 'mousedown', (e) => {
        if (e.target?.nodeName !== 'IMG') return;
        const img = e.target;
        if (!isFloatingImage(img)) return;

        if (e.button !== 0) return;

        const region = img.closest(FLOAT_REGION_SELECTOR) || img.closest('.doc-sheet');
        if (!region) return;
        
        // preventDefault: cegah seleksi teks & drag bawaan browser.
        e.preventDefault();
        
        // Tampilkan tools (drag surface + resize handles) untuk gambar floating.
        // Bila press ini MENSELEKSI (bukan sudah terpilih), click berikutnya
        // tidak boleh langsung melepas (guard).
        const wasSelectedBefore = activeImage === img;
        showImageTools(activeEditor || findEditorContaining(region), img);
        if (!wasSelectedBefore) imgToggleGuard = true;
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
    __bind(document, 'mousedown', (e) => {
        if (e.target?.nodeName !== 'IMG') return;
        if (e.button !== 0) return;
        const img = e.target;
        if (isFloatingImage(img)) return;          
        if (!img.closest('.doc-sheet')) return;    

                                        const zone = img.closest('.doc-sheet-header, .doc-sheet-footer');
                if (zone) {
            const zq = quillsByRegion.get(zone);
            if (!zq || !zq.isEnabled()) {
    
                const wasSelectedBefore = activeImage === img;
                showImageTools(activeEditor || findEditorContaining(zone), img);
                if (!wasSelectedBefore) imgToggleGuard = true;
                return;
            }
        }

        flowDragSourceImg = img;
        flowDragArmed = true;
        isDraggingFlowImage = false;
        flowStartX = e.clientX;
        flowStartY = e.clientY;
        flowImgOriginalCssText = img.getAttribute('style') || '';
        flowImgOriginalParent = img.parentElement;
        flowImgOriginalNext = img.nextSibling;
        flowImgOriginalRegion = img.closest(FLOAT_REGION_SELECTOR);
    });


    __bind(document, 'dragstart', (e) => {
        const img = e.target;
        if (img?.nodeName !== 'IMG') return;
        if (img.closest('.doc-signature')) return;
        if (!img.closest('.doc-sheet')) return;
        e.preventDefault();
    });

    // TITIK SISIP DI MANA SAJA: SEMUA klik kiri di kertas dihitung lewat
    __bind(document,
        'mousedown',
        (e) => {
            if (e.button !== 0) return;

            const sheet = e.target?.closest?.('.doc-sheet');
            if (!sheet) return; // hanya di dalam kertas

            if (e.target.closest?.('.doc-signature')) return;
            if (e.target.closest?.('img')) return;
            if (e.target.closest?.('button, a, input, select, textarea')) return;

            const zone = e.target.closest?.('.doc-sheet-header, .doc-sheet-footer');
            if (zone) {
                const zq = quillsByRegion.get(zone);
                if (!zq || !zq.isEnabled()) return;
            }


            if (e.detail >= 2) return;


            const nativeRange = caretRangeAtPoint(e.clientX, e.clientY);
            const overText = !!(
                nativeRange &&
                nativeRange.startContainer &&
                nativeRange.startContainer.nodeType === Node.TEXT_NODE &&
                sheet.contains(nativeRange.startContainer)
            );

            if (placeCaretAtPoint(e.clientX, e.clientY)) {
                if (!overText) {
                    e.preventDefault(); // area kosong: kita yang pasang caret
                }
            }
        },
        true // capture: jalan paling awal, tidak bisa diganggu handler lain
    );

    const __wordChar = (ch) => /[\w\u00C0-\u024F\u1E00-\u1EFF]/.test(ch || '');

    __bind(document,
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

    __bind(document, 'mousemove', (e) => {
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

            if (resizeIsFloating) {
                const region = activeImage.closest(FLOAT_REGION_SELECTOR);
                const regionW = region ? region.getBoundingClientRect().width : Infinity;

                // Lebar dibatasi selebar section (kertas tidak ikut melebar).
                let fW = newW;
                let fH = newH;
                if (regionW > 0 && fW > regionW) {
                    const ratio = Math.max(0.02, regionW / resizeStartW);
                    fW = Math.max(24, Math.round(resizeStartW * ratio));
                    fH = Math.max(24, Math.round(resizeStartH * ratio));
                }
                const dW2 = fW - resizeStartW;
                const dH2 = fH - resizeStartH;

                activeImage.style.width = fW + 'px';
                activeImage.style.height = fH + 'px';

                // Anchor diam: geser left/top hanya bila sisi kiri/atas yang bergerak
                if (shiftLeft) activeImage.style.left = (resizeStartLeft - dW2) + 'px';
                if (shiftTop) activeImage.style.top = (resizeStartTop - dH2) + 'px';

                // Saat resize: kunci gambar tetap di dalam kertas (header/footer
                // boleh masuk margin & pojok, body tetap di dalam zona).
                const [cL, cT] = clampPosToRegion(
                    region,
                    parseFloat(activeImage.style.left) || 0,
                    parseFloat(activeImage.style.top) || 0,
                    activeImage.offsetWidth,
                    activeImage.offsetHeight
                );
                activeImage.style.left = cL + 'px';
                activeImage.style.top = cT + 'px';

                // Garis batas bawah section mengikuti ukuran gambar
                if (region) fitRegionToImage(region);
            } else {
                activeImage.style.width = newW + 'px';
                activeImage.style.height = newH + 'px';

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

        // Gambar dikunci di dalam kotak section-nya: tidak boleh
        // melewati garis section saat di-drag.
        const [nextLeft, nextTop] = clampPosToRegion(
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

    __bind(document, 'mouseup', (e) => {
        // Selesaikan drag floating
        if (floatingImg) {
            const draggedImg = floatingImg;
            const wasDragged = isDraggingFloating;
            floatingImg.style.cursor = 'grab';
            floatingImg = null;
            floatingRegion = null;
            floatingDragArmed = false;
            isDraggingFloating = false;

            if (wasDragged) {
                // Drag sungguhan: click berikutnya tidak boleh melepas.
                imgToggleGuard = true;
                document.body.style.userSelect = '';
                // Lepas di atas region lain (body <-> kop/footer) ->
                // pindahkan induk gambarnya, bukan sekadar geser left/top.
                pindahkanFloatingKeRegion(draggedImg, e);
                notifyDirty();
            }
        }

        // Selesaikan drag angkat & jatuhkan gambar biasa
        if (flowDragArmed || isDraggingFlowImage) {
            const wasDragging = isDraggingFlowImage;
            flowDragArmed = false;
            isDraggingFlowImage = false;

            if (wasDragging) {
                // Drag sungguhan: click berikutnya tidak boleh melepas.
                imgToggleGuard = true;
                document.body.style.userSelect = '';
                selesaiFlowDrag(e);
            } else {
                flowDragSourceImg = null; // hanya klik biasa, batal saja
            }
        }

        if (isResizingImage) {
            // Finalisasi: tinggi section disesuaikan dengan ukuran akhir
            const resizedImg = activeImage;
            if (resizedImg) {
                const region = resizedImg.closest(FLOAT_REGION_SELECTOR);
                if (region) fitRegionToImage(region);
            }
            notifyDirty();
            document.body.style.userSelect = '';
        }
        isResizingImage = false;
    });

    __bind(document, 'scroll', () => positionImageTools(), true);
    __bind(window, 'resize', () => positionImageTools());

    // Reset guard per-press: setiap mousedown baru memulai siklus seleksi.
    // lastImgToggleGuard juga direset agar tidak lekat ke press berikutnya.
    __bind(document, 'mousedown', () => { imgToggleGuard = false; lastImgToggleGuard = false; }, true);

    // Klik di luar gambar & alatnya -> tutup mode edit gambar.
    // Klik SINGKAT pada gambar yang sedang terpilih (termasuk lewat
    // permukaan drag gambar floating) = toggle-lepas; press yang baru
    // saja menseleksi / drag sungguhan dipertahankan.
    __bind(document, 'click', (e) => {
        if (e.target === bubbleEl || bubbleEl?.contains(e.target)) return;
        if (e.target === removeBtnEl || removeBtnEl?.contains(e.target)) return;
        if (e.target === panelEl || panelEl?.contains(e.target)) return;
        if (handleEls.includes(e.target)) return;
        if (e.target === dragSurfaceEl || dragSurfaceEl?.contains(e.target)) {
            if (imgToggleGuard || lastImgToggleGuard) {
                imgToggleGuard = false;
                lastImgToggleGuard = false;
                return;
            }
            removeImageTools();
            return;
        }
        if (e.target.nodeName === 'IMG') return;
        // Press yang baru menseleksi gambar via mousedown (floating/zona
        // terkunci) bisa menembakkan click ke BODY: target mouseup adalah
        // drag-surface yang baru dibuat, sehingga click jatuh ke common
        // ancestor (BODY). Itu BUKAN klik-luar - jangan tutup tools.
        if (imgToggleGuard || lastImgToggleGuard) {
            imgToggleGuard = false;
            lastImgToggleGuard = false;
            return;
        }
        removeImageTools();
    });

    
    __bind(document, 'dblclick', (e) => {
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

// TOOLBAR QUILL (satu toolbar bersama untuk semua region)

let activeQuill = null;
const quillsByRegion = new Map();
// BODY fallback DOM (tanpa Quill): dideklarasikan di sini supaya
// unregisterRegion di bawah bisa mengaksesnya tanpa TDZ.
const domFlowBodies = new Set();

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
            if (cmd === 'underline') {
                // Underline is active when any underline style is applied
                const hasUnderline = !!fmt.underline || !!fmt.ul;
                btn.classList.toggle('active', hasUnderline);
            } else {
                btn.classList.toggle('active', !!fmt[cmd]);
            }
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
    if (sizeSel) sizeSel.value = fmt.size || '12px';
    const lineHeightSel = document.getElementById('tb-lineheight');
    if (lineHeightSel) lineHeightSel.value = fmt.lineheight || '1.5';
};


// TOOL TABEL (modul quill-table-better)


const getTableModule = () => {
    const q = getActiveQuill();
    if (!q) return null;
    return q.getModule('table-better') || null;
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
    const table = q.getModule('table-better');
    if (!table) { console.warn('[TableBetter] table-better module not found'); return; }

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
    const table = q.getModule('table-better');
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
        case 'strike':
            q.format(cmd, !q.getFormat(sel)[cmd]);
            break;
        case 'underline': {
            // Toggle default solid underline. Juga bersihkan variasi kelas (ul-*)
            // bila sedang aktif.
            const fmt = q.getFormat(sel);
            if (fmt.underline || fmt.ul) {
                // Remove all underline formatting (default <u> + variasi kelas)
                q.format('underline', false);
                q.formatText(sel.index, sel.length, 'ul', false);
            } else {
                // Apply solid underline (default)
                q.format('underline', true);
            }
            break;
        }
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
            const currentPx = parseInt(q.getFormat(sel).size, 10) || 12;
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

    // UNDERLINE DROPDOWN
    const underlineDd = document.getElementById('tb-underline-dd');
    if (underlineDd) {
        const underlineMenu = underlineDd.querySelector('.underline-menu');
        const underlineToggle = document.getElementById('tb-underline-toggle');

        // Apply underline style
        const applyUnderlineStyle = (style) => {
            const q = getActiveQuill();
            if (!q) return;
            const sel = q.getSelection(true);
            if (!sel) return;

            // Hapus underline bawaan (<u> blot) terlebih dahulu
            q.format('underline', false);

            // Hapus variasi garis bawah yang sedang aktif, lalu terapkan
            // variasi baru. ClassAttributor 'ul' (prefix 'ul') menghasilkan
            // kelas ul-<style> yang cocok dengan CSS yang ada.
            q.formatText(sel.index, sel.length, 'ul', false);
            q.formatText(sel.index, sel.length, 'ul', style);

            notifyDirty();
            refreshToolbarStates();
        };

        // Toggle dropdown
        underlineToggle?.addEventListener('click', (e) => {
            e.stopPropagation();
            underlineMenu?.classList.toggle('open');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!underlineDd.contains(e.target)) {
                underlineMenu?.classList.remove('open');
            }
        });

        // Handle underline style selection
        underlineMenu?.querySelectorAll('.underline-dd-item').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const style = btn.dataset.underlineStyle;
                if (style) {
                    applyUnderlineStyle(style);
                }
                underlineMenu?.classList.remove('open');
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
    domFlowBodies.delete(regionEl);
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
            pasteHtmlSafely(m.q, html);
            try {
                const sig = (im) =>
                    (im.getAttribute('src') || '') + '|' +
                    (im.getAttribute('style') || '');
                const srcImgs = [...(entry.regionEl?.querySelectorAll(':scope > img') || [])];
                const dstImgs = [...(m.regionEl?.querySelectorAll(':scope > img') || [])];
                const same = srcImgs.length === dstImgs.length &&
                    srcImgs.every((im, i) => sig(im) === sig(dstImgs[i]));
                if (!same) {
                    dstImgs.forEach((im) => im.remove());
                    srcImgs.forEach((im) => m.regionEl.appendChild(im.cloneNode(true)));
                }
            } catch (err) {
                /* noop */
            }

            // Salin juga tinggi minimum region (garis section yang
            // "mengikuti" gambar floating) ke semua halaman konsisten.
            if (m.regionEl && entry.regionEl &&
                (m.regionEl.style.minHeight || entry.regionEl.style.minHeight)) {
                m.regionEl.style.minHeight = entry.regionEl.style.minHeight;
            }


            if (!sel) return;
            try {
                const maxIndex = Math.max(0, m.q.getLength() - 1);
                m.q.setSelection(Math.min(m.lastCaret, maxIndex), 'silent');
            } catch (err) {
                /* noop: caret basi tidak wajib dipulihkan */
            }
        });
    } finally {
        mirroringInProgress = false;
    }
};

const normalizeTableGrid = (table) => {
    const rows = Array.from(table.rows);
    if (!rows.length) return;
    const colgroup = table.querySelector('colgroup');
    const colWidths = [];
    if (colgroup) {
        colgroup.querySelectorAll('col').forEach((col) => {
            const w = col.style.width || col.getAttribute('width') || '';
            const pct = parseFloat(w);
            if (!isNaN(pct) && pct > 0) colWidths.push(pct);
        });
    }

    
    const grid = [];
    const occupied = {};
    rows.forEach((tr, ri) => {
        grid[ri] = [];
        if (!occupied[ri]) occupied[ri] = new Set();
        const cells = Array.from(tr.children).filter(
            (el) => el.tagName === 'TD' || el.tagName === 'TH'
        );
        let ci = 0;
        cells.forEach((cell) => {
            while (occupied[ri].has(ci)) {
                grid[ri][ci] = null;
                ci++;
            }
            const colspan = Math.max(1, parseInt(cell.getAttribute('colspan') || '1', 10) || 1);
            const rowspan = Math.max(1, parseInt(cell.getAttribute('rowspan') || '1', 10) || 1);
            grid[ri][ci] = {
                html: cell.innerHTML,
                style: cell.getAttribute('style') || '',
                tag: cell.tagName,
                colspan,
                rowspan,
            };
            for (let c = 1; c < colspan; c++) {
                grid[ri][ci + c] = {
                    html: '',
                    style: cell.getAttribute('style') || '',
                    tag: cell.tagName,
                    colspan: 1,
                    rowspan: 1,
                };
            }
            for (let r = 1; r < rowspan; r++) {
                const tRi = ri + r;
                if (!occupied[tRi]) occupied[tRi] = new Set();
                for (let c = 0; c < colspan; c++) occupied[tRi].add(ci + c);
            }
            ci += colspan;
        });
    });

    let maxCols = 0;
    grid.forEach((rd) => {
        maxCols = Math.max(maxCols, rd.length);
    });
    if (maxCols < 1) maxCols = 1;

    // Hitung lebar setiap sel berdasarkan posisi kolom & colspan.
    // Jika <colgroup> ada, jumlahkan lebar kolom yang dicakup sel.
    // Jika tidak, gunakan lebar seragam (fallback).
    const getCellWidth = (ci, colspan) => {
        if (colWidths.length >= maxCols) {
            let total = 0;
            for (let c = ci; c < ci + colspan && c < colWidths.length; c++) {
                total += colWidths[c];
            }
            return total.toFixed(2) + '%';
        }
        return ((colspan * 100) / maxCols).toFixed(2) + '%';
    };

    rows.forEach((tr, ri) => {
        const rowData = grid[ri] || [];
        const frag = document.createDocumentFragment();
        for (let ci = 0; ci < maxCols; ci++) {
            const cd = rowData[ci];

            if (cd === null) continue;
            const cell = document.createElement(cd ? cd.tag : 'td');
            cell.classList.add('contract-table-cell');
            if (cd && cd.style) cell.setAttribute('style', cd.style);
            const colspan = (cd && cd.colspan) || 1;
            cell.style.width = getCellWidth(ci, colspan);
            // Pertahankan rowspan agar kolom kanan tampil sebagai satu cell menyatu.
            if (cd && cd.rowspan > 1) cell.setAttribute('rowspan', String(cd.rowspan));
            const html = (cd && cd.html) ? cd.html.trim() : '';
            // Sel kosong (baris pendek / colspan lanjutan) diberi <br>: quill-table-better
            // membuang style sel tanpa konten.
            cell.innerHTML = html || '<br>';
            frag.appendChild(cell);
        }
        while (tr.firstChild) tr.removeChild(tr.firstChild);
        tr.appendChild(frag);
    });

    if (colgroup) colgroup.remove();

    let tblStyle = table.getAttribute('style') || '';
    if (!/table-layout\s*:/i.test(tblStyle)) {
        tblStyle += (tblStyle && !/;\s*$/.test(tblStyle) ? ';' : '') + ' table-layout:fixed;';
    }
    if (!/(^|;)\s*width\s*:/i.test(tblStyle)) {
        tblStyle += ' width:100%;';
    }
    table.setAttribute('style', tblStyle.trim());
};

const preprocessContractTables = (html) => {
    if (!html || !html.includes('<table')) return html;

    const container = document.createElement('div');
    try {
        container.innerHTML = html.trim();
    } catch (e) {
        return html;
    }

    const tables = container.querySelectorAll('table');
    if (!tables.length) return html;

    tables.forEach((table) => {
        normalizeTableGrid(table);
        table.querySelectorAll('caption').forEach((el) => el.remove());

        const allCells = Array.from(table.querySelectorAll('td, th'));


        const isUnbordered = allCells.some((cell) => {
            return /border:\s*none/i.test(cell.getAttribute('style') || '');
        });
        table.classList.add(
            isUnbordered ? 'contract-table-unstyled' : 'contract-table-bordered'
        );

        allCells.forEach((cell) => {
            cell.classList.add('contract-table-cell');
            if (!cell.innerHTML.trim()) {
                cell.innerHTML = '<br>';
            }
        });
    });

    return container.innerHTML;
};

const pasteHtmlSafely = (q, html) => {
    if (!html || !html.trim()) return;
    const processedHtml = preprocessContractTables(html);
    const delta = q.clipboard.convert({ html: processedHtml, text: '\n' });
    q.setContents(delta, Quill.sources.SILENT);
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

    const hiddenToolbar = document.createElement('div');
    hiddenToolbar.style.display = 'none';
    regionEl.appendChild(hiddenToolbar);

    try {
        regionEl.dataset.quillReady = '1';

        let q;
        try {
            // Zona header/footer TIDAK memakai modul table-better: matchers-nya
            // mengubah tabel polos menjadi cangkang <temporary> kosong (tinggi
            // 0px, konten hilang) pada quill 2.0.1 + quill-table-better 1.2.3.
            // Dengan table blots bawaan Quill core, tabel polos footer/header
            // terkonversi utuh dan tetap bisa diedit sebagai teks.
            const zoneRole = regionEl.dataset?.region;
            const isZone = zoneRole === 'header' || zoneRole === 'footer';
            q = new Quill(host, {
                theme: 'snow',
                placeholder: '',
                modules: isZone
                    ? { toolbar: hiddenToolbar, table: false }
                    : {
                        toolbar: hiddenToolbar,
                        table: false,
                        'table-better': {
                            language: 'en_US',
                            menus: ['column', 'row', 'merge', 'table', 'cell', 'wrap', 'copy', 'delete'],
                            toolbarTable: false
                        },
                        keyboard: { bindings: QuillTableBetter.keyboardBindings },
                    },
                formats: ALLOWED_FORMATS,
            });
        } catch (err) {
            console.error('[DocQuill] Failed to initialize Quill:', err);
            regionEl.dataset.quillReady = '';
            regionEl.innerHTML = existingHtml || '<p><br></p>';
            regionEl.setAttribute('contenteditable', 'true');
            regionEl.classList.add('ql-editor');
            return null;
        }

        // TableClipboard (modules/clipboard milik quill-table-better yang
        // terdaftar GLOBAL via QuillTableBetter.register()) menyuntikkan
        // matchers tabelnya ke SETIAP instance Quill. Delta hasil campuran
        // format table-better ('table-cell'/'table-cell-block') dengan
        // format 'table' bawaan core GAGAL dimaterialisasi setContents:
        // isi sel hilang dan tabel menjadi cangkang tinggi 0px.
        // Dedupe: sisakan matcher core pertama per selector + matcher
        // 'td, th' milik editor ini (yang ditambahkan paling akhir),
        // buang matcher table-better ('table' & 'col' dan duplikatnya).
        {
            const ms = q.clipboard.matchers || [];
            const firstOf = (sel) => ms.find(([s]) => s === sel);
            const lastOf = (sel) => ms.filter(([s]) => s === sel).pop();
            q.clipboard.matchers = [
                ...ms.filter(([s]) =>
                    s !== 'tr' && s !== 'td, th' && s !== 'table' && s !== 'col'),
                firstOf('tr'),   // matcher core: format 'table' (nomor baris)
                lastOf('td, th'), // matcher editor: align/bold/rowspan sel
            ].filter(Boolean);
        }

        q.clipboard.addMatcher('td, th', (node, delta) => {
            const style = node.getAttribute('style') || '';
            const alignMatch = style.match(/text-align:\s*([^;]+)/i);
            if (alignMatch && delta.ops && delta.ops.length) {
                const align = alignMatch[1].trim();
                delta.ops.forEach((op) => {
                    if (op.attributes) {
                        if (op.attributes.align === undefined) {
                            op.attributes.align = align;
                        }
                    } else {
                        op.attributes = { align };
                    }
                });
            }
            // Pertahankan font-weight:bold untuk <th> header cells
            if (node.tagName === 'TH' && /font-weight:\s*bold/i.test(style)) {
                if (delta.ops && delta.ops.length) {
                    delta.ops.forEach((op) => {
                        if (op.attributes) {
                            if (op.attributes.bold === undefined) {
                                op.attributes.bold = true;
                            }
                        } else {
                            op.attributes = { bold: true };
                        }
                    });
                }
            }
    
            const rowspanAttr = node.getAttribute('rowspan');
            const colspanAttr = node.getAttribute('colspan');
            if (delta.ops && delta.ops.length
                && ((rowspanAttr && rowspanAttr !== '1') || (colspanAttr && colspanAttr !== '1'))) {
                delta.ops.forEach((op) => {
                    if (!op.attributes) return;
                    const cellFmt = op.attributes['table-cell'] || op.attributes['table-th'];
                    if (cellFmt && typeof cellFmt === 'object') {
                        if (rowspanAttr && rowspanAttr !== '1') {
                            cellFmt.rowspan = parseInt(rowspanAttr, 10);
                        }
                        if (colspanAttr && colspanAttr !== '1') {
                            cellFmt.colspan = parseInt(colspanAttr, 10);
                        }
                    }
                });
            }
            return delta;
        });

        if (existingHtml.trim()) {
    
            pasteHtmlSafely(q, existingHtml);

            // Guard anti-hilang: clipboard.convert bisa GAGAL SENYAP (delta
            // kosong / tabel ditelan) tanpa melempar error apa pun. Zona
            // header/footer ber-tabel wajib selamat — kalau tabelnya hilang
            // setelah konversi, pulihkan HTML asli dan lepas region dari
            // Quill (getHtml() tetap membaca innerHTML region sehingga
            // simpanan tidak kehilangan tabel).
            // CATATAN: bindDomPageOverflowWatch hoisted — aman dipanggil
            // dari sini walau definisinya di bawah (function declaration).
            if (/<table/i.test(existingHtml)
                && (!q.root.querySelector('table') || !q.root.querySelector('table tr'))) {
                console.warn('[DocQuill] Konversi tabel gagal senyap — region dipulihkan ke HTML asli.');
                const failedRole = regionEl.dataset?.region;
                try { q.disable?.(); } catch (err) { /* noop */ }
                try { q.off?.('text-change'); } catch (err) { /* noop */ }
                try { q.off?.('selection-change'); } catch (err) { /* noop */ }
                quillsByRegion.delete(regionEl);
                regionEl.dataset.quillReady = '';
                regionEl.innerHTML = existingHtml;
                regionEl.setAttribute('contenteditable', 'true');
                regionEl.classList.add('ql-editor');
                if (failedRole === 'body') {
                    // BODY fallback (tanpa Quill) TETAP ikut paginasi otomatis
                    // lewat alur DOM — tanpa ini isi template ber-tabel besar
                    // tidak pernah dipindah ke kertas berikut dan tumpah
                    // keluar kertas secara visual.
                    regionEl.dataset.domFlow = '1';
                    bindDomPageOverflowWatch(regionEl);
                }
                return null;
            }

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

        const seenFloatImgs = new Set();
        regionEl.querySelectorAll(':scope > img').forEach((im) => {
            const key = (im.getAttribute('src') || '') + '|' +
                (im.getAttribute('style') || '');
            if (seenFloatImgs.has(key)) {
                im.remove();
                return;
            }
            seenFloatImgs.add(key);
        });

        // Pulihkan tinggi section yang "mengikuti" gambar floating
        // (garis batas bawah section tetap rapi setelah reload).
        fitRegionToImage(regionEl);

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
        const failedRole = regionEl.dataset?.region;
        regionEl.dataset.quillReady = '';
        regionEl.innerHTML = existingHtml || '<p><br></p>';
        regionEl.setAttribute('contenteditable', 'true');
        regionEl.classList.add('ql-editor');
        if (failedRole === 'body') {
            regionEl.dataset.domFlow = '1';
            bindDomPageOverflowWatch(regionEl);
        }
        return null;
    }
};

let autoPaginationApi = null;

const PAGE_FLOW_TOL = 4;        // toleransi ukur (px)
const PAGE_FLOW_MAX_STEPS = 24; // pengaman anti-loop per kali jalan
const PAGE_FLOW_MAX_REQUEUES = 200; // pengaman antre-ulang (dokumen panjang)
const PAGE_FLOW_MAX_TOTAL    = 50;  // batas total iterasi paginasi per siklus
let   pageFlowTotalRuns      = 0;
let   pageFlowHalted         = false; // kunci keras: berhenti total sampai sesi paginasi baru
const pageFlowJustPushed     = new WeakMap(); // sheet -> blok yang barusan didorong ke kertas berikut

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

function __hasMeaningfulTextNode(el) {
    try {
        if (!el) return false;
        const txt = (el.textContent || '')
            .replace(/\u200b/g, '')
            .replace(/\u00a0/g, '')
            .trim();
        if (txt !== '') return true;
        const nested = Array.from(el.querySelectorAll('p, li, td, th, div'))
            .reduce((sum, node) => sum + (node.textContent || ''), '');
        return nested.replace(/\u200b/g, '').replace(/\u00a0/g, '').trim() !== '';
    } catch (err) {
        return false;
    }
}

function __looksEmptyBody(bodyEl) {
    try {
        if (bodyEl.querySelector('img, iframe, video')) return false;
        const txt = (bodyEl.innerText || '').replace(/\u200b/g, '').trim();
        return txt === '';
    } catch (err) { return false; }
}

// ─── PAGINASI DOM (untuk BODY fallback tanpa Quill) ───
// Dipakai saat konversi tabel template gagal senyap: region BODY berisi
// HTML mentah + contenteditable (tanpa instance Quill), sehingga alur
// paginasi Quill (delta/blot) tidak bisa dipakai. Alur ini memindahkan
// node DOM tingkat-atas ke kertas berikut, termasuk MEMBELAH <table>
// besar baris-per-baris (<tr>) supaya tabel spesifikasi bisa mengalir
// ke halaman 2, 3, dst.

function __domKids(bodyEl) {
    try {
        return Array.from(bodyEl ? bodyEl.children : []).filter((kid) =>
            !__isFloatingKid(kid) && __hasMeaningfulTextNode(kid));
    } catch (err) { return []; }
}

function __domContentOverflowPx(bodyEl) {
    try {
        if (!bodyEl || __looksEmptyBody(bodyEl)) return 0;
        return Math.max(0, (bodyEl.scrollHeight || 0) - (bodyEl.clientHeight || 0));
    } catch (err) { return 0; }
}

async function __domResolveTargetBody(sheet) {
    const nextSheet = sheet ? sheet.nextElementSibling : null;
    if (nextSheet && nextSheet.classList.contains('doc-sheet')) {
        const nb = nextSheet.querySelector('.doc-sheet-body[data-region="body"]');
        if (nb && (quillsByRegion.has(nb) || nb.dataset?.domFlow === '1')) return nb;
    }
    // Kertas berikut ada tapi body-nya belum terinisialisasi (mis. baru
    // dibuat createPageAfter lalu attachRegion gagal sebelum bind): pakai
    // langsung sebagai target DOM + pasang watcher agar alirannya jalan.
    if (nextSheet && nextSheet.classList.contains('doc-sheet')) {
        const nb = nextSheet.querySelector('.doc-sheet-body[data-region="body"]');
        if (nb && !quillsByRegion.has(nb)) {
            nb.dataset.domFlow = '1';
            bindDomPageOverflowWatch(nb);
            return nb;
        }
    }
    const uid = sheet && sheet.dataset ? sheet.dataset.pageUid : null;
    const apiCreate = autoPaginationApi && autoPaginationApi.createPageAfter;
    if (!uid || typeof apiCreate !== 'function') return null;
    const created = await apiCreate(uid);
    if (!created) return null;
    for (let i = 0; i < 20; i++) {
        if (quillsByRegion.has(created) || created.dataset?.domFlow === '1') return created;
        await __waitMs(25);
    }
    // createPageAfter membuat body Quill kosong; kalau attach-nya belum
    // terdaftar saat kita cek, jadikan target DOM sementara supaya flow
    // tidak mati (nanti Quill attach saat rebuild / watcher jalan).
    if (created && !quillsByRegion.has(created)) {
        created.dataset.domFlow = '1';
        bindDomPageOverflowWatch(created);
        return created;
    }
    return (quillsByRegion.has(created) || created.dataset?.domFlow === '1') ? created : null;
}

function __contentOverflowPx(quill, boxEl) {
    try {
        if (!boxEl || __looksEmptyBody(boxEl)) return 0;
        const inner = (quill.root && quill.root !== boxEl) ? Math.max(0, quill.root.scrollHeight) : 0;
        const outer = Math.max(boxEl.scrollHeight || 0, inner);
        return outer - (boxEl.clientHeight || 0);
    } catch (err) { return 0; }
}

// Belah <table> besar: pindahkan baris (<tr>) yang meluap ke tabel baru
// di kertas berikut. Baris yang "digantung" rowspan dari baris sebelumnya
// tidak ikut dipindah supaya struktur kolom tidak rusak.
function __domSplitTable(tableEl, effBottom, targetBody) {
    try {
        const rows = Array.from(tableEl.querySelectorAll(':scope > tbody > tr, :scope > tr'));
        if (rows.length < 2) return 0;
        let cut = -1;
        for (let i = 0; i < rows.length; i++) {
            if (rows[i].getBoundingClientRect().bottom - PAGE_FLOW_TOL > effBottom) {
                cut = i;
                break;
            }
        }
        if (cut <= 0) return 0;
        let safeCut = cut;
        for (let i = cut; i < rows.length; i++) {
            const prevCells = rows[i - 1] ? Array.from(rows[i - 1].cells || []) : [];
            let blocked = false;
            for (const cell of prevCells) {
                const rs = parseInt(cell.getAttribute('rowspan') || '1', 10) || 1;
                if (rs > 1 && (i - 1) + rs > i) { blocked = true; break; }
            }
            if (!blocked) { safeCut = i; break; }
            safeCut = i + 1;
        }
        if (safeCut >= rows.length) return 0;
        if (!tableEl.dataset.splitId) {
            tableEl.dataset.splitId = 't' + Date.now().toString(36) +
                Math.floor(Math.random() * 1e6).toString(36);
        }
        let targetTable = null;
        // Cari tabel lanjutan yang sudah ada (bisa di posisi mana pun karena
        // tiap siklus menyisipkan di depan): cocokkan penanda splitFrom.
        const prevKids = Array.from(targetBody.children || []);
        for (const kid of prevKids) {
            if (kid.tagName === 'TABLE' && kid.dataset?.splitFrom === tableEl.dataset.splitId) {
                targetTable = kid;
                break;
            }
        }
        if (!targetTable) {
            targetTable = document.createElement('table');
            const srcStyle = tableEl.getAttribute('style') || '';
            if (srcStyle) targetTable.setAttribute('style', srcStyle);
            if (tableEl.className) targetTable.className = tableEl.className;
            const colgroup = tableEl.querySelector(':scope > colgroup');
            if (colgroup) targetTable.appendChild(colgroup.cloneNode(true));
            const thead = tableEl.querySelector(':scope > thead');
            if (thead) targetTable.appendChild(thead.cloneNode(true));
            const tbody = document.createElement('tbody');
            targetTable.appendChild(tbody);
            targetTable.dataset.splitFrom = tableEl.dataset.splitId;
            // Sisipkan sebagai blok PERTAMA di target (isi lanjutan) supaya
            // urutan dokumen tetap benar: baris pindahan selalu di depan
            // konten lama halaman berikut.
            targetBody.insertBefore(targetTable, targetBody.firstChild);
        }
        else {
            // Tabel lanjutan sudah ada: angkat ke posisi paling depan supaya
            // baris pindahan (append di bawah) tetap berurutan benar.
            targetBody.insertBefore(targetTable, targetBody.firstChild);
        }
        let targetTbody = targetTable.querySelector(':scope > tbody');
        if (!targetTbody) {
            targetTbody = document.createElement('tbody');
            targetTable.appendChild(targetTbody);
        }
        let moved = 0;
        for (let i = safeCut; i < rows.length; i++) {
            targetTbody.appendChild(rows[i]);
            moved++;
        }
        return moved;
    } catch (err) { return 0; }
}

async function __domFlowPass(bodyEl) {
    const sheet = bodyEl ? bodyEl.closest('.doc-sheet') : null;
    if (!sheet) return false;
    if (__domContentOverflowPx(bodyEl) <= PAGE_FLOW_TOL) return false;
    const kids = __domKids(bodyEl);
    if (!kids.length) return false;
    const boxRect = bodyEl.getBoundingClientRect();
    const padT = parseFloat(getComputedStyle(bodyEl).paddingTop || '0');
    const effBottom = boxRect.top + padT + bodyEl.clientHeight - PAGE_FLOW_TOL;
    let idx = -1;
    for (let i = 0; i < kids.length; i++) {
        if (kids[i].getBoundingClientRect().bottom - PAGE_FLOW_TOL > effBottom) {
            idx = i;
            break;
        }
    }
    if (idx < 0) return false;
    const overKid = kids[idx];
    const targetBody = await __domResolveTargetBody(sheet);
    if (!targetBody || targetBody === bodyEl) return false;
    const targetQ = quillsByRegion.get(targetBody);
    const targetIsDom = !targetQ && targetBody.dataset?.domFlow === '1';
    if (!targetQ && !targetIsDom) return false;
    if (targetQ && !targetQ.isEnabled()) return false;
    if (overKid.tagName === 'TABLE') {
        // Target Quill: JANGAN panggil __domSplitTable (itu untuk target DOM —
        // ia menyisipkan <table> mentah ke Quill dan merusak blot). Untuk
        // target Quill, belah hanya bila __quillSplitTable tersedia; kalau
        // tidak, jatuh ke pindah blok utuh di bawah.
        // NOTE: __quillSplitTable didefinisikan di bawah (function
        // declaration -> hoisted, aman dipanggil dari sini).
        if (targetIsDom) {
            const movedRows = __domSplitTable(overKid, effBottom, targetBody);
            if (movedRows > 0) {
                pageFlowJustPushed.set(sheet, overKid);
                notifyDirty();
                __runDomFlow(targetBody);
                return true;
            }
        }
    }
    const moving = kids.slice(idx);
    if (!moving.length) return false;
    if (targetIsDom) {
        const marker = document.createElement('span');
        marker.setAttribute('data-domflow-marker', '1');
        marker.style.display = 'none';
        targetBody.insertBefore(marker, targetBody.firstChild);
        moving.forEach((kid) => targetBody.insertBefore(kid, marker));
        marker.remove();
    } else {
        const wrap = document.createElement('div');
        moving.forEach((kid) => wrap.appendChild(kid.cloneNode(true)));
        let delta = null;
        try {
            delta = targetQ.clipboard.convert({ html: wrap.innerHTML, text: '\n' });
        } catch (err) { delta = null; }
        if (!delta || !(delta.ops || []).length) return false;
        const DeltaCtor = __flowDeltaCtor(targetQ);
        if (!DeltaCtor) return false;
        const probe = document.createElement('div');
        probe.innerHTML = wrap.innerHTML;
        const hadTable = !!probe.querySelector('table tr');
        const trBefore = targetQ.root.querySelectorAll('table tr').length;
        const chg = new DeltaCtor();
        for (const op of delta.ops) {
            chg.push(op.insert == null
                ? { retain: __opLength(op) }
                : JSON.parse(JSON.stringify(op)));
        }
        targetQ.updateContents(chg, 'silent');
        if (hadTable) {
            const trAfter = targetQ.root.querySelectorAll('table tr').length;
            if (!(trAfter > trBefore)) {
                console.warn('[DocQuill] Paginasi DOM->Quill menelan tabel — dibatalkan.');
                return false;
            }
        }
        moving.forEach((kid) => kid.remove());
    }
    pageFlowJustPushed.set(sheet, overKid);
    notifyDirty();
    if (targetIsDom) __runDomFlow(targetBody);
    else if (targetQ) __runFlow(targetQ, targetBody);
    return true;
}

// Wrapper hoisted: __runDomFlow dipakai lebih awal (attachQuillToRegion),
// implementasi engine ada di __domRunFlow di bawah.
function __runDomFlow(bodyEl) {
    __domRunFlow(bodyEl);
}

function __domRunFlow(bodyEl) {
    if (!autoPaginationApi || pageFlowHalted) return;
    pageFlowTotalRuns++;
    if (pageFlowTotalRuns > PAGE_FLOW_MAX_TOTAL) {
        if (!pageFlowHalted) {
            console.warn('[DocQuill] Paginasi dihentikan: terlalu banyak iterasi (' + PAGE_FLOW_MAX_TOTAL + ').');
            pageFlowHalted = true;
        }
        return;
    }
    const job = pageFlowChain
        .then(async () => {
            let movedAny = false;
            for (let step = 0; step < PAGE_FLOW_MAX_STEPS; step++) {
                if (__sessionActive()) break;
                if (!document.body.contains(bodyEl)) break;
                const moved = await __domFlowPass(bodyEl);
                if (!moved) break;
                movedAny = true;
                await __nextFrame();
            }
            const stillOverflowing = document.body.contains(bodyEl)
                && !__sessionActive()
                && __domContentOverflowPx(bodyEl) > PAGE_FLOW_TOL;
            if (stillOverflowing && movedAny) {
                const n = (pageFlowRequeues.get(bodyEl) || 0) + 1;
                if (n <= PAGE_FLOW_MAX_REQUEUES) {
                    pageFlowRequeues.set(bodyEl, n);
                    __runDomFlow(bodyEl);
                    return;
                }
            }
            pageFlowRequeues.delete(bodyEl);
        })
        .catch((err) => console.warn('[DocQuill] Paginasi otomatis dilewati:', err));
    pageFlowChain = job;
}

function bindDomPageOverflowWatch(bodyEl) {
    if (!bodyEl || bodyEl.__domFlowBound) return;
    bodyEl.__domFlowBound = true;
    domFlowBodies.add(bodyEl);
    let timer = 0;
    const schedule = () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            pageFlowHalted = false;
            pageFlowTotalRuns = 0;
            __runDomFlow(bodyEl);
        }, 140);
    };
    bodyEl.addEventListener('input', () => {
        if (bodyEl.dataset?.region !== 'body') return;
        notifyDirty();
        schedule();
    });
    window.addEventListener('resize', () => schedule(), { passive: true });
    setTimeout(() => __runDomFlow(bodyEl), 350);
}

// ─── Paginasi Quill: hanya untuk BODY ber-Quill ───
function __firstOverflowIndex(quill, boxEl) {
    const root = quill.root;
    if (!root || !root.children || !root.children.length) return -1;
    const kids = Array.from(root.children);
    const boxRect = boxEl.getBoundingClientRect();
    const padT = parseFloat(getComputedStyle(boxEl).paddingTop || '0');
    const effBottom = boxRect.top + padT + boxEl.clientHeight - PAGE_FLOW_TOL;
    for (let i = 0; i < kids.length; i++) {
        if (__isFloatingKid(kids[i])) continue;
        if (!__hasMeaningfulTextNode(kids[i])) continue;
        const bottom = kids[i].getBoundingClientRect().bottom;
        if (bottom - PAGE_FLOW_TOL > effBottom) return i;
    }
    return kids.length;
}



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
        if (nb && (quillsByRegion.has(nb) || nb.dataset?.domFlow === '1')) return nb;
    }
    // Kertas berikut ada tapi body-nya belum terdaftar (mis. attach Quill
    // gagal / tertunda): daftarkan sebagai target DOM sementara supaya flow
    // tidak mati.
    if (nextSheet && nextSheet.classList.contains('doc-sheet')) {
        const nb = nextSheet.querySelector('.doc-sheet-body[data-region="body"]');
        if (nb && !quillsByRegion.has(nb)) {
            nb.dataset.domFlow = '1';
            bindDomPageOverflowWatch(nb);
            return nb;
        }
    }
    const uid = sheet.dataset?.pageUid;
    if (!uid || typeof apiCreate !== 'function') return null;
    const created = await apiCreate(uid);
    if (!created) return null;
    for (let i = 0; i < 20; i++) {
        if (quillsByRegion.has(created) || created.dataset?.domFlow === '1') return created;
        await __waitMs(25);
    }
    if (created && !quillsByRegion.has(created)) {
        created.dataset.domFlow = '1';
        bindDomPageOverflowWatch(created);
        return created;
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
    if (!__hasMeaningfulTextNode(kids[idx])) return false;

    const targetBody = await __resolveTargetBody(sheet,
        autoPaginationApi && autoPaginationApi.createPageAfter);
    if (!targetBody || targetBody === bodyEl) return false;
    const targetQ = quillsByRegion.get(targetBody);
    if (targetQ && !targetQ.isEnabled()) return false;

    // Target body-DOM (fallback tanpa Quill): pindahkan node DOM mentah.
    if (!targetQ) {
        if (targetBody.dataset?.domFlow !== '1') return false;
        const moving = Array.from(quill.root.children || []).slice(idx);
        // Petakan blok Quill -> node DOM: pindahkan berdasar urutan dengan
        // menandai node asal supaya tidak salah bila ada kembaran isi.
        const srcKids = Array.from(quill.root.children || []);
        const moveSet = new Set(moving);
        const marker = document.createElement('span');
        marker.setAttribute('data-domflow-marker', '1');
        marker.style.display = 'none';
        targetBody.insertBefore(marker, targetBody.firstChild);
        srcKids.forEach((kid) => {
            if (!moveSet.has(kid)) return;
            const html = kid.outerHTML || '';
            if (!html) return;
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            const node = tmp.firstElementChild;
            if (node) targetBody.insertBefore(node, marker);
        });
        marker.remove();
        // Hapus dari Quill asal via delta (tetap sinkron dengan blot).
        const firstRange = __blockRangeOf(quill, moving[0]);
        if (firstRange) {
            quill.deleteText(firstRange.start,
                Math.max(firstRange.len, quill.getLength() - firstRange.start), 'silent');
        }
        pageFlowJustPushed.set(sheet, moving[0]);
        notifyDirty();
        __runDomFlow(targetBody);
        return true;
    }

    const firstRange = __blockRangeOf(quill, kids[idx]);
    if (!firstRange) return false;

    // Tabel raksasa yang meluap: belah baris-per-baris, JANGAN pindah utuh
    // (pindah utuh tidak akan pernah muat -> overflow permanen di kertas).
    if (kids[idx].tagName === 'TABLE' && targetQ) {
        const boxRect = bodyEl.getBoundingClientRect();
        const padT = parseFloat(getComputedStyle(bodyEl).paddingTop || '0');
        const effBottom = boxRect.top + padT + bodyEl.clientHeight - PAGE_FLOW_TOL;
        const movedRows = __quillSplitTable(quill, kids[idx], effBottom, targetBody, targetQ);
        if (movedRows > 0) {
            pageFlowJustPushed.set(sheet, kids[idx]);
            notifyDirty();
            if (autoPaginationApi) __runFlow(targetQ, targetBody);
            return true;
        }
        // Tak bisa dibelah (mis. 1 baris raksasa): jatuh ke pindah utuh di
        // bawah; kalau tetap tak muat, flow berhenti aman (terpotong rapi).
    }

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
    // Guard anti-hilang: verifikasi delta benar-benar mendarat di target
    // (khusus blok tabel: jumlah <tr> target harus bertambah). Kalau
    // konversi menelan tabel, KEMBALIKAN isi ke asal (tidak ada data hilang).
    const probeTable = kids[idx].tagName === 'TABLE';
    const trBefore = probeTable ? targetQ.root.querySelectorAll('table tr').length : 0;
    const chg = new DeltaCtor();
    for (const op of removed.ops) {
        chg.push(op.insert == null
            ? { retain: __opLength(op) }
            : JSON.parse(JSON.stringify(op)));
    }
    targetQ.updateContents(chg, 'silent');

    if (probeTable) {
        const trAfter = targetQ.root.querySelectorAll('table tr').length;
        if (!(trAfter > trBefore)) {
            console.warn('[DocQuill] Paginasi Quill menelan tabel — dibatalkan, isi dikembalikan.');
            const back = new DeltaCtor();
            back.retain(range.start);
            for (const op of removed.ops) {
                back.push(op.insert == null
                    ? { retain: __opLength(op) }
                    : JSON.parse(JSON.stringify(op)));
            }
            quill.updateContents(back, 'silent');
            return false;
        }
    }

    if (selBefore && selBefore.index >= range.start
        && selBefore.index < range.start + range.len) {
        const off = Math.max(0, selBefore.index - range.start);
        targetQ.setSelection(off, selBefore.length || 0, 'silent');
    }

    if (autoPaginationApi) __runFlow(targetQ, targetBody);

    // Catat blok yang barusan didorong ke kertas berikutnya; dipakai
    // __pullBackPass untuk menolak menariknya balik (anti-flip A<->B).
    pageFlowJustPushed.set(sheet, kids[idx]);

    notifyDirty();
    return true;
}

function __pullBackPass(quill, bodyEl) {
    const sheet = bodyEl.closest('.doc-sheet');
    if (!sheet) return false;


    if (sheet.dataset?.flowLock) return false;

    if (__contentOverflowPx(quill, bodyEl) > PAGE_FLOW_TOL) return false;

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
    if (__isFloatingKid(k2) || !__hasMeaningfulTextNode(k2)) return false;

    // Anti-flip: jangan langsung menarik balik blok yang barusan didorong
    // oleh __flowPass di siklus yang sama - memicu getar abadi (push -> pull
    // -> push -> ...) yang membuat kertas terasa "berjalan sendiri".
    if (pageFlowJustPushed.get(sheet) === k2) return false;

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
    // Batas bawah = maksimum di SEMUA blok non-mengambang (bukan hanya blok
    // terakhir). Bila hanya memakai blok terakhir, ruang kosong bisa terhitung
    // terlalu longgar -> ada blok ditarik balik ke kertas yang sebenarnya penuh
    // -> tabrakan -> terus mengalir tanpa henti.
    let baseBottom = boxRect.top + padT;
    for (let i = 0; i < kids.length; i++) {
        if (__isFloatingKid(kids[i])) continue;
        const kbot = kids[i].getBoundingClientRect().bottom;
        if (kbot > baseBottom) baseBottom = kbot;
    }

    
    const h2 = k2.getBoundingClientRect().height;
    if (baseBottom + h2 > effBottom - PAGE_FLOW_TOL) return false;

    const range = __blockRangeOf(nextQ, k2);
    if (!range) return false;
    const DeltaCtor = __flowDeltaCtor(quill);
    const removed = nextQ.getContents(range.start, range.len);
    if (!DeltaCtor || !removed || !(removed.ops || []).length) return false;

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
    if (!autoPaginationApi || pageFlowHalted) return; // bridge belum siap / sudah terkunci latch
    pageFlowTotalRuns++;
    if (pageFlowTotalRuns > PAGE_FLOW_MAX_TOTAL) {
        // Latch keras (BUKAN reset): jika di-reset di sini, aliran paginasi
        // start-ulang tiap 50 iterasi dan tak pernah berhenti - persis gejala
        // "kertas terus berjalan sendiri" pada template colocation. Kunci ini
        // baru dibuka oleh schedule() saat ada edit user / resize.
        if (!pageFlowHalted) {
            console.warn('[DocQuill] Paginasi dihentikan: terlalu banyak iterasi (' + PAGE_FLOW_MAX_TOTAL + ').');
            pageFlowHalted = true;
        }
        return;
    }
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
        timer = setTimeout(() => {
            pageFlowHalted = false; // sesi paginasi baru (edit/resize) -> buka latch
            pageFlowTotalRuns = 0;
            __runFlow(quill, regionEl);
        }, 140);
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

// Belah <table> besar milik Quill: pindahkan baris (<tr>) yang meluap ke
// tabel baru di kertas berikut. Dipakai saat blok meluap adalah SATU tabel
// raksasa (kasus template: tabel spesifikasi colocation dsb.) yang tidak
// bisa dipindah utuh karena tingginya melebihi sisa ruang kertas.
function __quillSplitTable(quill, tableEl, effBottom, targetBody, targetQ) {
    try {
        const rows = Array.from(tableEl.querySelectorAll(':scope > tbody > tr, :scope > tr'));
        if (rows.length < 2) return 0;
        let cut = -1;
        for (let i = 0; i < rows.length; i++) {
            if (rows[i].getBoundingClientRect().bottom - PAGE_FLOW_TOL > effBottom) {
                cut = i;
                break;
            }
        }
        if (cut <= 0) return 0;
        let safeCut = cut;
        for (let i = cut; i < rows.length; i++) {
            const prevCells = rows[i - 1] ? Array.from(rows[i - 1].cells || []) : [];
            let blocked = false;
            for (const cell of prevCells) {
                const rs = parseInt(cell.getAttribute('rowspan') || '1', 10) || 1;
                if (rs > 1 && (i - 1) + rs > i) { blocked = true; break; }
            }
            if (!blocked) { safeCut = i; break; }
            safeCut = i + 1;
        }
        if (safeCut >= rows.length) return 0;
        // Bangun tabel lanjutan dari HTML baris yang pindah, lalu sisipkan
        // lewat clipboard target supaya tetap sinkron dengan blot Quill.
        const srcTable = tableEl.cloneNode(false);
        srcTable.removeAttribute('id');
        const colgroup = tableEl.querySelector(':scope > colgroup');
        if (colgroup) srcTable.appendChild(colgroup.cloneNode(true));
        const thead = tableEl.querySelector(':scope > thead');
        if (thead) srcTable.appendChild(thead.cloneNode(true));
        const tbody = document.createElement('tbody');
        for (let i = safeCut; i < rows.length; i++) {
            tbody.appendChild(rows[i].cloneNode(true));
        }
        srcTable.appendChild(tbody);
        let delta = null;
        try {
            delta = targetQ.clipboard.convert({ html: srcTable.outerHTML, text: '\n' });
        } catch (err) { delta = null; }
        if (!delta || !(delta.ops || []).length) return 0;
        // Hapus baris asal dari Quill via delta (tetap sinkron blot).
        const DeltaCtor = __flowDeltaCtor(quill);
        if (!DeltaCtor) return 0;
        const rowRanges = [];
        for (let i = safeCut; i < rows.length; i++) {
            const r = __blockRangeOf(quill, rows[i]);
            if (r) rowRanges.push(r);
        }
        if (!rowRanges.length) return 0;
        const start = Math.min.apply(null, rowRanges.map((r) => r.start));
        const end = Math.max.apply(null, rowRanges.map((r) => r.start + r.len));
        const removed = quill.getContents(start, end - start);
        if (!removed || !(removed.ops || []).length) return 0;
        // Sisipkan ke target DULU, verifikasi tabel benar-benar mendarat,
        // BARU hapus baris asal — kalau konversi menelan tabel, isi asal
        // tetap utuh (tidak ada data yang hilang).
        const trBefore = targetQ.root.querySelectorAll('table tr').length;
        const chg = new DeltaCtor();
        for (const op of delta.ops) {
            chg.push(op.insert == null
                ? { retain: __opLength(op) }
                : JSON.parse(JSON.stringify(op)));
        }
        targetQ.updateContents(chg, 'silent');
        // Guard: jumlah baris tabel target HARUS bertambah (mencegah kasus
        // tabel lanjutan menyatu/merge dengan tabel lama tetap dihitung ok,
        // tapi konversi yang menelan isi pasti tertolak).
        const trAfter = targetQ.root.querySelectorAll('table tr').length;
        if (!(trAfter > trBefore)) {
            console.warn('[DocQuill] Belah tabel Quill menelan isi — dibatalkan.');
            return 0;
        }
        quill.deleteText(start, end - start, 'silent');
        return rows.length - safeCut;
    } catch (err) { return 0; }
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

        // Klik gambar yang terlihat -> langsung buka mode edit gambar.
        // Klik ULANG gambar yang sedang terpilih = lepas (toggle), supaya
        // siklus pilih -> lepas -> pilih lagi selalu selesai dalam 1 klik.
        // Press yang baru saja menseleksi via mousedown dilindungi guard.
        root.addEventListener('click', (e) => {
            if (e.target.nodeName !== 'IMG') return;
            if (e.target.closest('.doc-signature')) return;
            if (activeImage === e.target) {
                // Press yang baru saja MENSELEKSI gambar ini (seleksi baru via
                // showImageTools) atau drag sungguhan: click berikutnya tidak
                // boleh langsung melepas (toggle-off) — tanpa ini, gambar yang
                // baru diseleksi berkedip dan alatnya mustahil diklik.
                if (imgToggleGuard || lastImgToggleGuard) {
                    imgToggleGuard = false;
                    lastImgToggleGuard = false;
                    return;
                }
                removeImageTools();
                return;
            }
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
    __version: 'hf-11-domflow',

    attachRegion: attachQuillToRegion,

    getHtml: (regionEl) => {
        if (!regionEl) return '';
        const q = quillsByRegion.get(regionEl);
        let html = q ? q.root.innerHTML : regionEl.innerHTML;
        // Gambar floating hidup sebagai anak langsung region (di luar
        // q.root / alur konten): sertakan, tapi JANGAN gandakan gambar
        // yang sudah termasuk di dalam html (kasus body-DOM fallback di
        // mana q.root === region itu sendiri / tidak ada host terpisah).
        regionEl.querySelectorAll(':scope > img').forEach((im) => {
            try {
                if (q && q.root && q.root.contains(im)) return;
                if (html && html.indexOf(im.outerHTML) >= 0) return;
            } catch (err) { /* lanjut sertakan */ }
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
                    pasteHtmlSafely(m.q, html);


                    try {
                        const sig = (im) =>
                            (im.getAttribute('src') || '') + '|' +
                            (im.getAttribute('style') || '');
                        const srcImgs = [...(list[0].regionEl?.querySelectorAll(':scope > img') || [])];
                        const dstImgs = [...(m.regionEl?.querySelectorAll(':scope > img') || [])];
                        const same = srcImgs.length === dstImgs.length &&
                            srcImgs.every((im, i) => sig(im) === sig(dstImgs[i]));
                        if (!same) {
                            dstImgs.forEach((im) => im.remove());
                            srcImgs.forEach((im) => m.regionEl.appendChild(im.cloneNode(true)));
                        }
                    } catch (err) {
    
                    }


                    if (m.regionEl && list[0].regionEl &&
                        (m.regionEl.style.minHeight || list[0].regionEl.style.minHeight)) {
                        m.regionEl.style.minHeight = list[0].regionEl.style.minHeight;
                    }

                    if (!sel) return;
                    try {
                        const maxIndex = Math.max(0, m.q.getLength() - 1);
                        m.q.setSelection(Math.min(m.lastCaret, maxIndex), 'silent');
                    } catch (err) {
                
                    }
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

    focusZone: (regionEl) => {
        const q = quillsByRegion.get(regionEl);
        if (!q) return;
        q.setSelection(Math.max(0, q.getLength() - 1));
    },

    focusBodyEnd: (regionEl) => {
        if (!regionEl) return;
        const q = quillsByRegion.get(regionEl);
        if (q) {
            if (!q.isEnabled()) q.enable();
            q.setSelection(Math.max(0, q.getLength() - 1), 'silent');
            q.focus();
            return;
        }
        // Body-DOM fallback: fokuskan caret ke akhir konten mentah.
        if (regionEl.dataset?.domFlow === '1') {
            try {
                regionEl.focus?.();
                const range = document.createRange();
                range.selectNodeContents(regionEl);
                range.collapse(false);
                const sel = window.getSelection();
                sel?.removeAllRanges();
                sel?.addRange(range);
            } catch (err) { /* noop */ }
        }
    },

    ensureBodyEditable: () => {
        let revived = 0;
        quillsByRegion.forEach((q, el) => {
            if (el.dataset?.region === 'body' && !q.isEnabled()) {
                q.enable();
                revived++;
            }
        });
        // Body-DOM fallback selalu editable via contenteditable mentah.
        domFlowBodies.forEach((el) => {
            if (el.isConnected && el.getAttribute('contenteditable') !== 'true') {
                el.setAttribute('contenteditable', 'true');
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
        // BODY fallback DOM (tanpa Quill, mis. template ber-tabel yang
        // gagal konversi): ikutkan ke paginasi lewat alur DOM.
        try {
            document.querySelectorAll('.doc-sheet-body[data-region="body"][data-dom-flow="1"]')
                .forEach((el) => bindDomPageOverflowWatch(el));
        } catch (err) { /* noop */ }
    },

    clickAndType: (regionEl, clientX, clientY) => {
        console.log('[clickAndType] dipanggil', { clientX, clientY });
        const q = quillsByRegion.get(regionEl);
        if (!q) {
            // Body-DOM fallback: biarkan caret native contenteditable bekerja.
            if (regionEl?.dataset?.domFlow === '1') return false;
            console.log('[clickAndType] BAIL: quill gak ketemu/gak aktif');
            return false;
        }
        if (!q.isEnabled()) {
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