import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import QuillTableBetter, { ToolbarTable } from 'quill-table-better';
import 'quill-table-better/dist/quill-table-better.css';

QuillTableBetter.register();
const __guardBlotOptimize = (blotName) => {
    const BlotClass = Quill.import(blotName);
    if (!BlotClass || !BlotClass.prototype || typeof BlotClass.prototype.optimize !== 'function') return;
    if (BlotClass.prototype.__docQuillGuard) return;
    const __originalOptimize = BlotClass.prototype.optimize;
    BlotClass.prototype.optimize = function (context) {
        try {
            return __originalOptimize.call(this, context);
        } catch (err) {
            return undefined;
        }
    };
    BlotClass.prototype.__docQuillGuard = true;
};
['formats/table-cell', 'formats/table-th', 'formats/table-row', 'formats/table',
 'formats/table-container', 'formats/table-body', 'formats/list-container']
    .forEach(__guardBlotOptimize);

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

const ParaHeadMargin = new Parchment.StyleAttributor('phead', 'margin-top', {
    scope: Parchment.Scope.BLOCK,
    whitelist: null, 
});
Quill.register(ParaHeadMargin, true);

const ParaHeadBottomMargin = new Parchment.StyleAttributor('pbb', 'margin-bottom', {
    scope: Parchment.Scope.BLOCK,
    whitelist: null,
});
Quill.register(ParaHeadBottomMargin, true);

const ListStyleAttributor = new Parchment.ClassAttributor('liststyle', 'ql-liststyle', {
    scope: Parchment.Scope.BLOCK,
    whitelist: ['decimal', 'alpha', 'upperalpha', 'lowerroman', 'roman'],
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

// Garis pemisah kop surat 
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
    'font', 'size', 'color', 'background', 'lineheight', 'liststyle', 'phead', 'pbb',
    'table', 'table-header', 'table-cell', 'table-cell-block',
    'table-th', 'table-th-block', 'table-row', 'table-th-row',
    'table-body', 'table-thead', 'table-temporary', 'table-col',
    'table-colgroup', 'table-container', 'table-list', 'table-list-container',
];

//  Upload gambar ke server 
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

    const role = regionEl.dataset?.region;
    const isZone = role === 'header' || role === 'footer';
    const sheet = isZone ? regionEl.closest('.doc-sheet') : null;
    const sRect = sheet ? sheet.getBoundingClientRect() : null;

    let minX = 0, minY = 0;
    let maxX = r.width - w;
    let maxY = r.height - h;

    if (sRect) {
        const offX = r.left - sRect.left;
        const offY = r.top - sRect.top;
        minX = -offX;
        maxX = sRect.width - w - offX;

        if (role === 'header') {
            minY = -offY;
            const padB = parseFloat(getComputedStyle(regionEl).paddingBottom) || 0;
            const bottomMax = Math.max(0, r.height - padB - h);
            minY = Math.min(minY, bottomMax);
            maxY = bottomMax;
        } else if (role === 'footer') {
            maxY = sRect.height - h - offY;
            const padT = parseFloat(getComputedStyle(regionEl).paddingTop) || 0;
            const topBound = padT;
            minY = topBound;
            maxY = Math.max(maxY, topBound);
        } else {
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

    img.style.width = rect.width + 'px';
    img.style.height = rect.height + 'px';

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

    if (index == null) {
        index = quillIndexFromPoint(q, q.root, x, y);
    }
    if (index == null) return null;
    return Math.max(0, Math.min(index, Math.max(0, q.getLength() - 1)));
};

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
        
        e.preventDefault();
        
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
                while (s > 0 && __wordChar(text[s - 1])) s--;
                while (t < text.length && __wordChar(text[t])) t++;
            } else if (/\s/.test(text[idx] || '')) {
                while (s > 0 && /\s/.test(text[s - 1]) && !__wordChar(text[s - 1])) s--;
                while (t < text.length && /\s/.test(text[t]) && !__wordChar(text[t])) t++;
            } else {
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
        if (flowDragArmed && !isDraggingFlowImage && flowDragSourceImg) {
            if (e.buttons === 0) {
                flowDragArmed = false;
                flowDragSourceImg = null;
            } else if (Math.hypot(e.clientX - flowStartX, e.clientY - flowStartY) >= 4) {
                mulaiFlowDrag();
            }
        }

        if (isDraggingFlowImage && flowDragSourceImg) {
            e.preventDefault();
            flowDragSourceImg.style.left = (e.clientX - flowDragOffsetX) + 'px';
            flowDragSourceImg.style.top = (e.clientY - flowDragOffsetY) + 'px';
        }

        if (isResizingImage && activeImage) {
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

            const shiftLeft = resizeCorner === 'nw' || resizeCorner === 'sw';
            const shiftTop = resizeCorner === 'nw' || resizeCorner === 'ne';

            if (resizeIsFloating) {
                const region = activeImage.closest(FLOAT_REGION_SELECTOR);
                const regionW = region ? region.getBoundingClientRect().width : Infinity;

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

                if (shiftLeft) activeImage.style.left = (resizeStartLeft - dW2) + 'px';
                if (shiftTop) activeImage.style.top = (resizeStartTop - dH2) + 'px';

                const [cL, cT] = clampPosToRegion(
                    region,
                    parseFloat(activeImage.style.left) || 0,
                    parseFloat(activeImage.style.top) || 0,
                    activeImage.offsetWidth,
                    activeImage.offsetHeight
                );
                activeImage.style.left = cL + 'px';
                activeImage.style.top = cT + 'px';


                if (region) fitRegionToImage(region);
            } else {
                activeImage.style.width = newW + 'px';
                activeImage.style.height = newH + 'px';
            }

            positionImageTools();
        }

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
        if (floatingImg) {
            const draggedImg = floatingImg;
            const wasDragged = isDraggingFloating;
            floatingImg.style.cursor = 'grab';
            floatingImg = null;
            floatingRegion = null;
            floatingDragArmed = false;
            isDraggingFloating = false;

            if (wasDragged) {
                imgToggleGuard = true;
                document.body.style.userSelect = '';
                pindahkanFloatingKeRegion(draggedImg, e);
                notifyDirty();
            }
        }

        if (flowDragArmed || isDraggingFlowImage) {
            const wasDragging = isDraggingFlowImage;
            flowDragArmed = false;
            isDraggingFlowImage = false;

            if (wasDragging) {
                imgToggleGuard = true;
                document.body.style.userSelect = '';
                selesaiFlowDrag(e);
            } else {
                flowDragSourceImg = null; 
            }
        }

        if (isResizingImage) {
            const resizedImg = activeImage;
            if (resizedImg) {
                const region = resizedImg.closest(FLOAT_REGION_SELECTOR);
                if (region) fitRegionToImage(region);

                const bodyRegion = resizedImg.closest('.doc-sheet-body');
                if (bodyRegion && typeof bodyRegion.__scheduleFlow === 'function') bodyRegion.__scheduleFlow();
            }
            notifyDirty();
            document.body.style.userSelect = '';
        }
        isResizingImage = false;
    });

    __bind(document, 'scroll', () => positionImageTools(), true);
    __bind(window, 'resize', () => positionImageTools());

    __bind(document, 'mousedown', () => { imgToggleGuard = false; lastImgToggleGuard = false; }, true);

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

        if (stack[0] === img) return;

        if (img.closest('.doc-signature')) return;

        const editor = findEditorContaining(img);
        if (!editor) return;

        e.preventDefault();
        try { window.getSelection()?.removeAllRanges(); } catch (err) { /* noop */ }

        showImageTools(editor, img);
    });


let activeQuill = null;
const quillsByRegion = new Map();
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




const getTableModule = () => {
    const q = getActiveQuill();
    if (!q) return null;
    return q.getModule('table-better') || null;
};

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
            const fmt = q.getFormat(sel);
            if (fmt.underline || fmt.ul) {
                q.format('underline', false);
                q.formatText(sel.index, sel.length, 'ul', false);
            } else {
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

            if (!q.getSelection(true)) return;

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

    const tableDd = document.getElementById('tb-table-dd');
    if (tableDd) {
        const menu = tableDd.querySelector('.toolbar-dropdown-menu');
        const grid = tableDd.querySelector('.table-grid-picker');
        const label = tableDd.querySelector('.table-grid-label');

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

        const mainBtn = tableDd.querySelector('[data-cmd="table"]');
        mainBtn?.addEventListener('click', () => {
            menu?.classList.toggle('open');
            const inside = isInsideTable();
            menu?.querySelectorAll('[data-table-action]').forEach((btn) => {
                btn.classList.toggle('disabled', !inside);
            });
        });

        document.addEventListener('click', (e) => {
            if (!tableDd.contains(e.target)) closeTableMenu();
        });

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

    const underlineDd = document.getElementById('tb-underline-dd');
    if (underlineDd) {
        const underlineMenu = underlineDd.querySelector('.underline-menu');
        const underlineToggle = document.getElementById('tb-underline-toggle');

        const applyUnderlineStyle = (style) => {
            const q = getActiveQuill();
            if (!q) return;
            const sel = q.getSelection(true);
            if (!sel) return;

            q.format('underline', false);

            q.formatText(sel.index, sel.length, 'ul', false);
            q.formatText(sel.index, sel.length, 'ul', style);

            notifyDirty();
            refreshToolbarStates();
        };

        underlineToggle?.addEventListener('click', (e) => {
            e.stopPropagation();
            underlineMenu?.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!underlineDd.contains(e.target)) {
                underlineMenu?.classList.remove('open');
            }
        });

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
            }

            if (m.regionEl && entry.regionEl &&
                (m.regionEl.style.minHeight || entry.regionEl.style.minHeight)) {
                m.regionEl.style.minHeight = entry.regionEl.style.minHeight;
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
                    covered: true,
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

    if (colWidths.length < maxCols) {
        const inferredWidths = Array(maxCols).fill(null);
        grid.forEach((rowData) => {
            rowData.forEach((cell, ci) => {
                if (!cell || cell.covered || cell.colspan !== 1 || ci >= maxCols) return;
                const match = cell.style.match(/(?:^|;)\s*width\s*:\s*([\d.]+)\s*%/i);
                if (match) {
                    const width = parseFloat(match[1]);
                    if (width > 0) inferredWidths[ci] = width;
                }
            });
        });
        const inferredTotal = inferredWidths.reduce((sum, width) => sum + (width || 0), 0);
        if (inferredWidths.every((width) => width != null)
            && Math.abs(inferredTotal - 100) <= 1) {
            colWidths.splice(0, colWidths.length, ...inferredWidths);
        }
    }

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
            if (cd && cd.rowspan > 1) cell.setAttribute('rowspan', String(cd.rowspan));
            const html = (cd && cd.html) ? cd.html.trim() : '';
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

        try {
            if (table.querySelectorAll(':scope > tbody > tr, :scope > tr').length <= 1) {
                const src = table.outerHTML || '';
                if (src.indexOf('data-flow-atomic="1"') < 0) {
                    table.setAttribute('data-flow-atomic', '1');
                }
            }
        } catch (err) { /* penanda atomik opsional */ }

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

        {
            const ms = q.clipboard.matchers || [];
            const firstOf = (sel) => ms.find(([s]) => s === sel);
            const lastOf = (sel) => ms.filter(([s]) => s === sel).pop();
            q.clipboard.matchers = [
                ...ms.filter(([s]) =>
                    s !== 'tr' && s !== 'td, th' && s !== 'table' && s !== 'col'),
                firstOf('tr'),   
                lastOf('td, th'),
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

        {
            const __role = regionEl.dataset ? regionEl.dataset.region : null;
            const __nestedTable = /<(?:td|th)\b[^>]*>[\s\S]*?<table/i.test(existingHtml);
            const __zoneWithTable = (__role === 'header' || __role === 'footer')
                && /<table/i.test(existingHtml);
            if (existingHtml.trim() && (__nestedTable || __zoneWithTable)) {
                try { q.disable && q.disable(); } catch (err) { /* noop */ }
                try { q.off && q.off('text-change'); } catch (err) { /* noop */ }
                try { q.off && q.off('selection-change'); } catch (err) { /* noop */ }
                quillsByRegion.delete(regionEl);
                regionEl.dataset.quillReady = '';
                regionEl.innerHTML = existingHtml;
                regionEl.setAttribute('contenteditable', 'true');
                regionEl.classList.add('ql-editor');
                if (__role === 'body') {
                    regionEl.dataset.domFlow = '1';
                    bindDomPageOverflowWatch(regionEl);
                } else {
                    console.info('[DocQuill] Zona ber-tabel disimpan sebagai HTML asli (mode cermin).');
                }
                return null;
            }
        }

        if (existingHtml.trim()) {
    
            pasteHtmlSafely(q, existingHtml);

            if (/<table/i.test(existingHtml)
                && (!q.root.querySelector('table') || !q.root.querySelector('table tr'))) {
                const failedRole = regionEl.dataset?.region;
                if (failedRole === 'body') {
                    const tableCount = (existingHtml.match(/<table/gi) || []).length;
                    console.info(
                        `[DocQuill] Body ber-tabel (${tableCount}) dipertahankan sebagai HTML asli`
                        + ' — paginasi DOM aktif. Ini normal untuk dokumen dari template kontrak.'
                    );
                } else {
                    console.info('[DocQuill] Tabel zona header/footer dipertahankan sebagai HTML asli.');
                }
                try { q.disable?.(); } catch (err) { /* noop */ }
                try { q.off?.('text-change'); } catch (err) { /* noop */ }
                try { q.off?.('selection-change'); } catch (err) { /* noop */ }
                quillsByRegion.delete(regionEl);
                regionEl.dataset.quillReady = '';
                regionEl.innerHTML = existingHtml;
                regionEl.setAttribute('contenteditable', 'true');
                regionEl.classList.add('ql-editor');
                if (failedRole === 'body') {
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

const PAGE_FLOW_TOL = 4;        
const PAGE_FLOW_PULL_MARGIN = 10; 
const PAGE_FLOW_MAX_STEPS = 24; 
const PAGE_FLOW_MAX_REQUEUES = 200; 
const PAGE_FLOW_MAX_TOTAL    = 50; 
let   pageFlowTotalRuns      = 0;
let   pageFlowHalted         = false;
let   pageFlowJustPushed     = new WeakMap(); 
const pageFlowJustPushedSig  = new Map();

// Batas bawah AREA KONTEN yang benar-benar terlihat (px).
//
// clientHeight sebuah elemen SUDAH MEMUAT padding-top + padding-bottom, jadi
// menambahkan padT lagi (pola lama: boxRect.top + padT + clientHeight) membuat
// garis deteksi turun sebesar ±1 baris. Akibatnya blok yang meluap sedikit
// tidak pernah dianggap melintasi batas -> paginasi "tidak menemukan" blok
// untuk didorong -> teks meluap kecil tertinggal ter-clip di bawah footer
// (gejala "teks terkubur di footer/header"). Kurangi paddingBottom agar garis
// jatuh tepat di tepi bawah area isi yang terlihat.
function __flowEffBottom(boxEl) {
    try {
        const boxRect = boxEl.getBoundingClientRect();
        const padB = parseFloat(getComputedStyle(boxEl).paddingBottom || '0') || 0;
        return boxRect.top + (boxEl.clientHeight || 0) - padB - PAGE_FLOW_TOL;
    } catch (err) { return 0; }
} 

function __resetPushMemory() {
    pageFlowJustPushed = new WeakMap();
    pageFlowJustPushedSig.clear();
}

function __pushedSigKey(sheet) {
    try {
        return (sheet && sheet.dataset && sheet.dataset.pageUid)
            ? sheet.dataset.pageUid + '::sig'
            : '';
    } catch (err) { return ''; }
}

function __pushedSigFor(sheet) {
    const key = __pushedSigKey(sheet);
    return key ? (pageFlowJustPushedSig.get(key) || '') : '';
}

function __rememberPushedBlock(sheet, blockEl) {
    try { pageFlowJustPushed.set(sheet, blockEl); } catch (err) { /* noop */ }
    const key = __pushedSigKey(sheet);
    if (key) pageFlowJustPushedSig.set(key, __flowSig(blockEl));
}

function __rememberPushedGroup(sheet, sig) {
    const key = __pushedSigKey(sheet);
    if (key && sig) pageFlowJustPushedSig.set(key, sig);
}

const __FLOW_INTERNAL_ATTRS = [
    'data-flow-cont',
    'data-split-from',
    'data-split-id',
    'data-flow-atomic',
    'data-domflow-marker',
];

function __stripFlowInternalAttrs(html) {
    if (!html || typeof html !== 'string') return html || '';
    if (!__FLOW_INTERNAL_ATTRS.some((a) => html.indexOf(a) >= 0)) return html;

    try {
        const holder = document.createElement('div');
        holder.innerHTML = html;
        const touched = holder.querySelectorAll(
            __FLOW_INTERNAL_ATTRS.map((a) => '[' + a + ']').join(',')
        );
        touched.forEach((el) => {
            __FLOW_INTERNAL_ATTRS.forEach((a) => el.removeAttribute(a));
        });
        return holder.innerHTML;
    } catch (err) {
        return html;
    }
}

function __isFlowContinuation(node) {
    try {
        if (!node || !node.dataset) return false;
        return !!(node.dataset.splitFrom || node.dataset.flowCont);
    } catch (err) { return false; }
}

function __flowContBoundary(root) {
    try {
        const kids = Array.from(root.children || []);
        for (const kid of kids) {
            if (!__isFlowContinuation(kid)) return kid;
        }
    } catch (err) { /* noop */ }
    return null;
}

function __domFlowInsertContinuation(targetBody, nodes) {
    try {
        const list = Array.isArray(nodes) ? nodes : [nodes];
        const boundary = __flowContBoundary(targetBody);
        for (const node of list) {
            if (!node) continue;
            try { node.dataset.flowCont = '1'; } catch (err) { /* noop */ }
            targetBody.insertBefore(node, boundary);
        }
    } catch (err) { /* noop */ }
}

function __flowContInsertPlan(targetQ) {
    try {
        const boundary = __flowContBoundary(targetQ.root);
        if (!boundary) {
            let end = 0;
            try {
                const len = targetQ.getLength();
                if (typeof len === 'number' && len > 0) end = len;
            } catch (err) { end = 0; }
            return { index: end, boundary: null };
        }
        const at = targetQ.getIndex(boundary);
        return {
            index: (typeof at === 'number' && at > 0) ? at : 0,
            boundary: boundary,
        };
    } catch (err) {
        return { index: 0, boundary: null };
    }
}

function __flowContDelta(DeltaCtor, delta, plan) {
    const chg = new DeltaCtor();
    if (plan && plan.index > 0) chg.retain(plan.index);
    for (const op of (delta && delta.ops) || []) {
        chg.push(op.insert == null
            ? { retain: __opLength(op) }
            : JSON.parse(JSON.stringify(op)));
    }
    return chg;
}

function __markFlowContinuationRun(targetQ, plan) {
    try {
        const kids = Array.from(targetQ.root.children || []);
        const hasPlan = !!(plan && typeof plan === 'object');
        let boundary;
        if (hasPlan) {
            boundary = plan.boundary || undefined; // null => tandai semua
        } else {
            boundary = __flowContBoundary(targetQ.root) || undefined;
        }
        for (const kid of kids) {
            if (boundary && kid === boundary) break;
            try { kid.dataset.flowCont = '1'; } catch (err) { /* noop */ }
        }
    } catch (err) { /* noop */ }
}

function __flowDeltaLength(delta) {
    let total = 0;
    for (const op of (delta && delta.ops) || []) {
        if (op && op.insert != null) total += __opLength(op);
    }
    return total;
}

const pageFlowTimers = new WeakMap();
const pageFlowRequeues = new WeakMap(); 
const pageFlowQueue = Promise.resolve();

const __nextFrame = () => new Promise((r) => requestAnimationFrame(() => r()));
const __waitMs = (ms) => new Promise((r) => setTimeout(r, ms));

function __flowDeltaCtor(quill) {
    try {
        const sample = quill.getContents();
        if (sample && typeof sample.constructor === 'function') return sample.constructor;
    } catch (err) { }
    try { return Quill.import('delta'); } catch (err) {  }
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

function __isContractHeadingLine(el) {
    try {
        if (!el || el.tagName !== 'P') return false;
        const txt = (el.textContent || '').replace(/\u00a0/g, ' ').trim();
        if (!txt || txt.length > 64) return false;
        if (!el.querySelector('strong, b')) return false;
        const probe = el.cloneNode(true);
        probe.querySelectorAll('strong, b').forEach((s) => s.remove());
        if ((probe.textContent || '').replace(/\u00a0/g, ' ').trim()) return false;
        return true;
    } catch (err) { return false; }
}

function __headingGroupStart(kids, idx) {
    try {
        let start = idx;
        while (start > 0 && __isContractHeadingLine(kids[start - 1])) start--;
        return start;
    } catch (err) { return idx; }
}

function __headingGroupEnd(kids, idx) {
    try {
        if (!kids || idx < 0 || idx >= kids.length) return idx + 1;
        if (!__isContractHeadingLine(kids[idx])) return idx + 1;
        let end = idx + 1;
        while (end < kids.length && __isContractHeadingLine(kids[end])) end++;
        if (end < kids.length && !__isContractHeadingLine(kids[end])) end++;
        return Math.min(end, kids.length);
    } catch (err) { return idx + 1; }
}

function __isAtomicFlowKid(kid) {
    try {
        if (kid && kid.dataset && kid.dataset.flowAtomic === '1') return true;
        return __atomicProbeHtml(kid);
    } catch (err) { return false; }
}

function __atomicProbeHtml(kid) {
    try {
        if (kid && typeof kid.getAttribute === 'function'
            && kid.getAttribute('data-flow-atomic') === '1') return true;
        const html = (kid && kid.outerHTML) || '';
        if (html.indexOf('data-flow-atomic="1"') >= 0) return true;
        return false;
    } catch (err) { return false; }
}

function __flowSig(el) {
    try {
        if (!el || !el.tagName) return '';
        const txt = (el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 200);
        return el.tagName + '|' + (el.children ? el.children.length : 0) + '|' + txt;
    } catch (err) { return ''; }
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
        const cs = getComputedStyle(boxEl);
        const pad = (parseFloat(cs.paddingTop) || 0) + (parseFloat(cs.paddingBottom) || 0);
        const inner = (quill.root && quill.root !== boxEl) ? Math.max(0, quill.root.scrollHeight) + pad : 0;
        const outer = Math.max(boxEl.ScrollHeight || 0, inner);
        return outer - (boxEl.clientHeight || 0);
    } catch (err) { return 0; }
}

function __domSplitTable(tableEl, effBottom, targetBody) {
    try {
        if (__isAtomicFlowKid(tableEl)) return 0;

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
            let blocked = false;
            for (let r = 0; r < i; r++) {
                const cells = Array.from(rows[r].cells || []);
                for (const cell of cells) {
                    const rs = parseInt(cell.getAttribute('rowspan') || '1', 10) || 1;
                    if (rs > 1 && r + rs > i) { blocked = true; break; }
                }
                if (blocked) break;
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
            __domFlowInsertContinuation(targetBody, targetTable);
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

/* Lanjutan penomoran OL antar kertas: bila sheet N berakhir OL dan sheet
   N+1 diawali OL bertipe sama, OL lanjutan diberi start lanjutan. */
function __olTypeKey(ol) {
    try {
        let cls = ' ';
        try { cls += (ol.getAttribute('class') || '') + ' '; } catch (e) {}
        try {
            const li = ol.querySelector ? ol.querySelector(':scope > li') : null;
            if (li) cls += (li.getAttribute('class') || '') + ' ';
        } catch (e) {}
        const m = cls.match(/ql-liststyle-([a-z]+)/);
        if (m) return 'q:' + m[1].toLowerCase();
    } catch (err) {}
    try {
        const t = ((ol.getAttribute && ol.getAttribute('type')) || '').toString().trim().toLowerCase();
        if (t) return 't:' + t;
    } catch (err) {}
    try {
        const st = ((ol.getAttribute && ol.getAttribute('style')) || '').toString();
        const sm = st.match(/list-style-type\s*:\s*([^;]+)/i);
        if (sm) return 's:' + sm[1].trim().toLowerCase();
    } catch (err) {}
    return 'unknown';
}

function __olCanChain(a, b) {
    return a === b || a === 'unknown' || b === 'unknown';
}

function __olEffStart(ol) {
    try {
        if (ol.hasAttribute && ol.hasAttribute('start')) {
            const s = parseInt(ol.getAttribute('start') || '1', 10);
            if (!isNaN(s) && s > 0) return s;
        }
        const li = ol.querySelector ? ol.querySelector(':scope > li') : null;
        if (li && li.getAttribute) {
            const v = parseInt(li.getAttribute('value') || '', 10);
            if (!isNaN(v) && v > 0) return v;
        }
    } catch (err) {}
    return 1;
}

function __olCount(ol) {
    try {
        const kids = ol.children ? Array.from(ol.children) : [];
        let n = 0;
        for (const k of kids) { if (k && k.tagName === 'LI') n++; }
        return n;
    } catch (err) { return 0; }
}

function __flowKids(container) {
    const out = [];
    try {
        const kids = Array.from(container.children || []);
        for (const k of kids) {
            if (!k || !k.tagName) continue;
            if (k.tagName === 'SCRIPT' || k.tagName === 'STYLE') continue;
            let txt = '';
            try { txt = (k.textContent || '').replace(/ /g, ' ').trim(); } catch (e) { txt = ''; }
            if (txt !== '') { out.push(k); continue; }
            let has = false;
            try { has = !!(k.querySelector && k.querySelector('img, table, ol, ul, li, iframe, video')); } catch (e) {}
            if (has) out.push(k);
        }
    } catch (err) {}
    return out;
}

function __renumberContinuedListsAcrossSheets() {
    try {
        const root = document.getElementById('document-editor');
        if (!root) return 0;
        const sheets = Array.from(root.querySelectorAll('.doc-sheet[data-sheet-type="page"]'));
        if (!sheets.length) return 0;
        let prev = null;
        let fixed = 0;
        for (const sheet of sheets) {
            if (sheet.dataset && sheet.dataset.flowLock) { prev = null; continue; }
            const body = sheet.querySelector('.doc-sheet-body[data-region="body"]');
            if (!body) { prev = null; continue; }
            let container = body;
            try {
                const q = (typeof quillsByRegion !== 'undefined') ? quillsByRegion.get(body) : null;
                if (q && q.root) container = q.root;
            } catch (err) {}
            if (!container) { prev = null; continue; }
            const kids = __flowKids(container);
            if (!kids.length) { prev = null; continue; }
            const first = kids[0];
            const last = kids[kids.length - 1];
            if (first && first.tagName === 'OL' && prev && __olCanChain(prev.key, __olTypeKey(first))) {
                const want = prev.start + prev.count;
                if (__olEffStart(first) !== want || !(first.hasAttribute && first.hasAttribute('start'))) {
                    first.setAttribute('start', String(want));
                    try {
                        const lis = Array.from(first.children || []).filter((x) => x && x.tagName === 'LI');
                        if (lis.some((li) => li.hasAttribute && li.hasAttribute('value'))) {
                            lis.forEach((li, j) => { try { li.setAttribute('value', String(want + j)); } catch (e) {} });
                        }
                    } catch (err) {}
                    fixed++;
                }
                // Offset counter Quill (.ql-editor me-reset list-0..9 per
                // lembar): tanpa ini angka di LAYAR tetap 1 meski start benar.
                // counter-reset inline didukung Quill (StyleAttributor phead/
                // pbb membuktikan style inline lolos) & aman untuk PDF karena
                // PDF memakai list-style native (value/start), bukan counter.
                if (container && container.classList && container.classList.contains('ql-editor')) {
                    try {
                        let lv = 0;
                        try {
                            const fli = first.querySelector ? first.querySelector(':scope > li') : null;
                            if (fli) {
                                for (let d = 1; d <= 8; d++) {
                                    if (fli.classList && fli.classList.contains('ql-indent-' + d)) { lv = d; break; }
                                }
                            }
                        } catch (e) {}
                        const cur = (container.style && container.style.counterReset) || '';
                        const re = new RegExp('(?:^|\\s)list-' + lv + '(?:\\s+-?\\d+)?', 'g');
                        const kept = cur.replace(re, ' ').replace(/\s+/g, ' ').trim();
                        const next = ((kept ? kept + ' ' : '') + 'list-' + lv + ' ' + (want - 1)).trim();
                        if (cur !== next) {
                            container.style.counterReset = next;
                            fixed++;
                        }
                    } catch (err) {}
                }
            }
            if (last && last.tagName === 'OL') {
                prev = { start: __olEffStart(last), count: __olCount(last), key: __olTypeKey(last) };
            } else {
                prev = null;
            }
        }
        return fixed;
    } catch (err) { return 0; }
}

function __domSplitList(listEl, effBottom, targetBody) {
    try {
        const items = Array.from(listEl.children || []).filter((c) => c.tagName === 'LI');
        if (items.length < 2) return 0;
        let cut = -1;
        for (let i = 0; i < items.length; i++) {
            if (items[i].getBoundingClientRect().bottom - PAGE_FLOW_TOL > effBottom) {
                cut = i;
                break;
            }
        }
        if (cut <= 0 || cut >= items.length) return 0;
        if (!listEl.dataset.splitId) {
            listEl.dataset.splitId = 'l' + Date.now().toString(36) +
                Math.floor(Math.random() * 1e6).toString(36);
        }
        let targetList = null;
        const prevKids = Array.from(targetBody.children || []);
        for (const kid of prevKids) {
            if (kid.tagName === listEl.tagName
                && kid.dataset?.splitFrom === listEl.dataset.splitId) {
                targetList = kid;
                break;
            }
        }
        if (!targetList) {
            targetList = listEl.cloneNode(false);
            targetList.removeAttribute('id');
            targetList.dataset.splitFrom = listEl.dataset.splitId;
            if (listEl.tagName === 'OL') {
                const startBase = parseInt(listEl.getAttribute('start') || '1', 10) || 1;
                targetList.setAttribute('start', String(startBase + cut));
            }
            __domFlowInsertContinuation(targetBody, targetList);
        }
        let moved = 0;
        for (let i = cut; i < items.length; i++) {
            targetList.appendChild(items[i]);
            moved++;
        }
        return moved;
    } catch (err) { return 0; }
}

function __domSplitListToQuill(listEl, effBottom, targetQ) {
    try {
        const items = Array.from(listEl.children || []).filter((c) => c.tagName === 'LI');
        if (items.length < 2) return 0;
        let cut = -1;
        for (let i = 0; i < items.length; i++) {
            if (items[i].getBoundingClientRect().bottom - PAGE_FLOW_TOL > effBottom) {
                cut = i;
                break;
            }
        }
        if (cut <= 0 || cut >= items.length) return 0;
        const srcList = listEl.cloneNode(false);
        srcList.removeAttribute('id');
        for (let i = cut; i < items.length; i++) {
            srcList.appendChild(items[i].cloneNode(true));
        }
        let delta = null;
        try {
            delta = targetQ.clipboard.convert({ html: srcList.outerHTML, text: '\n' });
        } catch (err) { delta = null; }
        if (!delta || !(delta.ops || []).length) return 0;
        const DeltaCtor = __flowDeltaCtor(targetQ);
        if (!DeltaCtor) return 0;
        const liBefore = targetQ.root.querySelectorAll('li').length;
        const contPlan = __flowContInsertPlan(targetQ);
        targetQ.updateContents(__flowContDelta(DeltaCtor, delta, contPlan), 'silent');
        const liAfter = targetQ.root.querySelectorAll('li').length;
        if (!(liAfter > liBefore)) {
            console.warn('[DocQuill] Belah list DOM->Quill menelan isi — dibatalkan.');
            return 0;
        }
        __markFlowContinuationRun(targetQ, contPlan);
        let moved = 0;
        for (let i = cut; i < items.length; i++) {
            items[i].remove();
            moved++;
        }
        return moved;
    } catch (err) { return 0; }
}

function __takeoverBodyAsDom(bodyEl) {
    try {
        if (!bodyEl || !bodyEl.isConnected) return false;
        const q = quillsByRegion.get(bodyEl);
        let html = '';
        const floatImgs = Array.from(bodyEl.querySelectorAll(':scope > img'));
        if (q) {
            html = q.root ? q.root.innerHTML : '';
            try { q.off?.('text-change'); } catch (err) { /* noop */ }
            try { q.off?.('selection-change'); } catch (err) { /* noop */ }
            try { q.disable?.(); } catch (err) { /* noop */ }
            quillsByRegion.delete(bodyEl);
        }
        if (!html || !html.trim()) html = bodyEl.innerHTML;
        bodyEl.dataset.quillReady = '';
        bodyEl.innerHTML = html;
        floatImgs.forEach((im) => bodyEl.appendChild(im));
        bodyEl.setAttribute('contenteditable', 'true');
        bodyEl.classList.add('ql-editor');
        bodyEl.dataset.domFlow = '1';
        bindDomPageOverflowWatch(bodyEl);
        console.info('[DocQuill] Body diambil alih sebagai DOM — belah tabel baris-per-baris aktif.');
        return true;
    } catch (err) {
        console.warn('[DocQuill] Takeover DOM gagal:', err);
        return false;
    }
}

async function __domFlowPass(bodyEl) {
    const sheet = bodyEl ? bodyEl.closest('.doc-sheet') : null;
    if (!sheet) return false;

    if (sheet.dataset?.flowLock) return false;

    if (__domContentOverflowPx(bodyEl) <= PAGE_FLOW_TOL) return false;
    const kids = __domKids(bodyEl);
    if (!kids.length) return false;
    const effBottom = __flowEffBottom(bodyEl);
    let idx = -1;
    for (let i = 0; i < kids.length; i++) {
        if (kids[i].getBoundingClientRect().bottom - PAGE_FLOW_TOL > effBottom) {
            idx = i;
            break;
        }
    }
    if (idx < 0) return false;
    const overKid = kids[idx];
    if (__isAtomicFlowKid(overKid)) {
        const r = overKid.getBoundingClientRect();
        const tableH = r.bottom - r.top;
        const fitsFresh = tableH <= (bodyEl.clientHeight + PAGE_FLOW_TOL);
        if (!fitsFresh) return false;
        const overflow = r.bottom - effBottom;
        if (r.top + PAGE_FLOW_TOL <= effBottom && overflow <= 40) return false;
        // Luapan jelas -> jatuh ke logika grup di bawah (pindah utuh).
    }
    const targetBody = await __domResolveTargetBody(sheet);
    if (!targetBody || targetBody === bodyEl) return false;
    const targetQ = quillsByRegion.get(targetBody);
    const targetIsDom = !targetQ && targetBody.dataset?.domFlow === '1';
    if (!targetQ && !targetIsDom) return false;
    if (targetQ && !targetQ.isEnabled()) return false;
    if (overKid.tagName === 'TABLE') {
        const tableH = overKid.getBoundingClientRect().height;
        const fitsFreshPage = tableH <= (bodyEl.clientHeight + PAGE_FLOW_TOL);
        if (targetIsDom) {
            const movedRows = __domSplitTable(overKid, effBottom, targetBody);
            if (movedRows > 0) {
                __rememberPushedBlock(sheet, overKid);
                notifyDirty();
                __runDomFlow(targetBody);
                return true;
            }
            if (!fitsFreshPage) return false;
        } else {
            if (!fitsFreshPage) {
                if (__takeoverBodyAsDom(targetBody)) {
                    const movedRows = __domSplitTable(overKid, effBottom, targetBody);
                    if (movedRows > 0) {
                        __rememberPushedBlock(sheet, overKid);
                        notifyDirty();
                        __runDomFlow(targetBody);
                        return true;
                    }
                }
                return false;
            }
        }
    }
    if ((overKid.tagName === 'OL' || overKid.tagName === 'UL') && targetIsDom) {
        const movedItems = __domSplitList(overKid, effBottom, targetBody);
        if (movedItems > 0) {
            __rememberPushedBlock(sheet, overKid);
            try { __renumberContinuedListsAcrossSheets(); } catch (err) {}
            notifyDirty();
            __runDomFlow(targetBody);
            return true;
        }
    }
    if ((overKid.tagName === 'OL' || overKid.tagName === 'UL') && targetQ) {
        const movedItems = __domSplitListToQuill(overKid, effBottom, targetQ);
        if (movedItems > 0) {
            __rememberPushedBlock(sheet, overKid);
            try { __renumberContinuedListsAcrossSheets(); } catch (err) {}
            notifyDirty();
            __runFlow(targetQ, targetBody);
            return true;
        }
    }
    let grpStart = idx;
    if (idx > 0 && __isContractHeadingLine(kids[idx - 1])) {
        grpStart = __headingGroupStart(kids, idx);
        if (grpStart === 0) grpStart = idx;
    }
    let grpEnd = idx + 1;
    if (__isContractHeadingLine(overKid)) {
        const fwd = __headingGroupEnd(kids, idx);
        if (fwd > grpEnd) {
            let ok = true;
            for (let i = idx; i < fwd; i++) {
                const t = kids[i] && kids[i].tagName;
                if ((t === 'TABLE' || t === 'OL' || t === 'UL')
                    && kids[i].getBoundingClientRect().height > bodyEl.clientHeight + PAGE_FLOW_TOL) {
                    ok = false;
                    break;
                }
            }
            if (ok) grpEnd = fwd;
        }
    }
    const fwdOnly = __isContractHeadingLine(overKid) && grpStart === idx;
    const moving = fwdOnly ? kids.slice(grpStart, grpEnd) : kids.slice(grpStart);
    if (!moving.length) return false;
    if (overKid.tagName !== 'TABLE'
        && overKid.tagName !== 'OL'
        && overKid.tagName !== 'UL'
        && overKid.getBoundingClientRect().height > bodyEl.clientHeight - PAGE_FLOW_TOL) {
        console.warn('[DocQuill] Blok tunggal (DOM) lebih tinggi dari satu halaman dan tak bisa dibelah — paginasi blok ini dihentikan aman.');
        return false;
    }
    if (targetIsDom) {
        __domFlowInsertContinuation(targetBody, moving);
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
        const contPlan = __flowContInsertPlan(targetQ);
        targetQ.updateContents(__flowContDelta(DeltaCtor, delta, contPlan), 'silent');
        if (hadTable) {
            const trAfter = targetQ.root.querySelectorAll('table tr').length;
            if (!(trAfter > trBefore)) {
                console.warn('[DocQuill] Paginasi DOM->Quill menelan tabel — target diambil alih sebagai DOM.');
                if (__takeoverBodyAsDom(targetBody)) {
                    __domFlowInsertContinuation(targetBody, moving);
                    __runDomFlow(targetBody);
                    return true;
                }
                return false;
            }
        }
        moving.forEach((kid) => kid.remove());
        __markFlowContinuationRun(targetQ, contPlan);
    }
    __rememberPushedBlock(sheet, overKid);
    try { __renumberContinuedListsAcrossSheets(); } catch (err) {}
    notifyDirty();
    if (targetIsDom) __runDomFlow(targetBody);
    else if (targetQ) __runFlow(targetQ, targetBody);
    return true;
}

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
                pageFlowTotalRuns = 0;
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
            try { __renumberContinuedListsAcrossSheets(); } catch (err) { /* noop */ }
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
            __resetPushMemory();
            __runDomFlow(bodyEl);
        }, 140);
    };
    bodyEl.addEventListener('input', () => {
        if (bodyEl.dataset?.region !== 'body') return;
        notifyDirty();
        schedule();
    });
    window.addEventListener('resize', () => schedule(), { passive: true });

    if (bodyEl.dataset?.region === 'body') {
        bodyEl.__scheduleFlow = schedule;
        bodyEl.addEventListener('load', schedule, true);
        if ('ResizeObserver' in window) {
            let lastH = bodyEl.clientHeight;
            new ResizeObserver(() => {
                const h = bodyEl.clientHeight;
                if (Math.abs(h - lastH) < 2) return;
                lastH = h;
                schedule();
            }).observe(bodyEl);
        }
    }
    setTimeout(() => __runDomFlow(bodyEl), 350);
}

function __firstOverflowIndex(quill, boxEl) {
    const root = quill.root;
    if (!root || !root.children || !root.children.length) return -1;
    const kids = Array.from(root.children);
    const effBottom = __flowEffBottom(boxEl);
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
    const nextSheet = sheet.nextElementSibling;
    if (nextSheet && nextSheet.classList.contains('doc-sheet')) {
        const nb = nextSheet.querySelector('.doc-sheet-body[data-region="body"]');
        if (nb && (quillsByRegion.has(nb) || nb.dataset?.domFlow === '1')) return nb;
    }
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

    try { if (quill.root && !quill.root.isConnected) return false; } catch (err) { return false; }
    if (sheet.dataset?.flowLock) return false;

    if (__contentOverflowPx(quill, bodyEl) <= PAGE_FLOW_TOL) return false;

    const idx = __firstOverflowIndex(quill, bodyEl);
    const kids = Array.from(quill.root.children || []);
    if (idx < 0 || idx >= kids.length) return false; // tak bisa dipetakan aman
    if (!__hasMeaningfulTextNode(kids[idx])) return false;

    if (__isAtomicFlowKid(kids[idx])) {
        const effBottom = __flowEffBottom(bodyEl);
        const r = kids[idx].getBoundingClientRect();
        const tableH = r.bottom - r.top;
        if (tableH > bodyEl.clientHeight + PAGE_FLOW_TOL) return false;
        const overflow = r.bottom - effBottom;
        if (r.top + PAGE_FLOW_TOL <= effBottom && overflow <= 40) return false;
        // Luapan jelas -> lanjut ke logika grup (pindah utuh, bukan belah).
    }

    const targetBody = await __resolveTargetBody(sheet,
        autoPaginationApi && autoPaginationApi.createPageAfter);
    if (!targetBody || targetBody === bodyEl) return false;
    const targetQ = quillsByRegion.get(targetBody);
    if (targetQ && !targetQ.isEnabled()) return false;

    if (!targetQ) {
        if (targetBody.dataset?.domFlow !== '1') return false;
        if (kids[idx].tagName === 'TABLE') {
            const effBottom = __flowEffBottom(bodyEl);
            const movedRows = __domSplitTable(kids[idx], effBottom, targetBody);
            if (movedRows > 0) {
                __rememberPushedBlock(sheet, kids[idx]);
                notifyDirty();
                __runDomFlow(targetBody);
                return true;
            }
            const tooBig = kids[idx].getBoundingClientRect().height
                > (bodyEl.clientHeight + PAGE_FLOW_TOL);
            if (tooBig) return false; 
        }
        const srcKids = Array.from(quill.root.children || []);
        let grpStart = idx;
        if (idx > 0 && __isContractHeadingLine(srcKids[idx - 1])) {
            grpStart = __headingGroupStart(srcKids, idx);
            if (grpStart === 0) grpStart = idx; 
        }
        let grpEnd = idx + 1;
        if (__isContractHeadingLine(srcKids[idx])) {
            const fwd = __headingGroupEnd(srcKids, idx);
            if (fwd > grpEnd) {
                let ok = true;
                for (let i = idx; i < fwd; i++) {
                    const t = srcKids[i] && srcKids[i].tagName;
                    if ((t === 'TABLE' || t === 'OL' || t === 'UL')
                        && srcKids[i].getBoundingClientRect().height > bodyEl.clientHeight + PAGE_FLOW_TOL) {
                        ok = false;
                        break;
                    }
                }
                if (ok) grpEnd = fwd;
            }
        }
        const fwdOnlyQd = __isContractHeadingLine(srcKids[idx]) && grpStart === idx;
        const moving = fwdOnlyQd ? srcKids.slice(grpStart, grpEnd) : srcKids.slice(grpStart);
        const moveSet = new Set(moving);
        const freshNodes = [];
        srcKids.forEach((kid) => {
            if (!moveSet.has(kid)) return;
            const html = kid.outerHTML || '';
            if (!html) return;
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            const node = tmp.firstElementChild;
            if (node) freshNodes.push(node);
        });
        if (freshNodes.length) __domFlowInsertContinuation(targetBody, freshNodes);
        const firstRange = __blockRangeOf(quill, moving[0]);
        if (firstRange) {
            const lastRange = (moving.length > 1)
                ? (__blockRangeOf(quill, moving[moving.length - 1]) || firstRange)
                : firstRange;
            const delLen = fwdOnlyQd
                ? (lastRange.start - firstRange.start) + lastRange.len
                : Math.max(firstRange.len, quill.getLength() - firstRange.start);
            quill.deleteText(firstRange.start, delLen, 'silent');
        }
        __rememberPushedBlock(sheet, moving[0]);
        notifyDirty();
        __runDomFlow(targetBody);
        return true;
    }

    const firstRange = __blockRangeOf(quill, kids[idx]);
    if (!firstRange) return false;

    if (kids[idx].tagName === 'TABLE' && targetQ) {
        const effBottom = __flowEffBottom(bodyEl);
        const movedRows = __quillSplitTable(quill, kids[idx], effBottom, targetBody, targetQ);
        if (movedRows > 0) {
            __rememberPushedBlock(sheet, kids[idx]);
            notifyDirty();
            if (autoPaginationApi) __runFlow(targetQ, targetBody);
            return true;
        }

        const tableH = kids[idx].getBoundingClientRect().height;
        if (tableH > bodyEl.clientHeight + PAGE_FLOW_TOL) {
            if (__takeoverBodyAsDom(bodyEl)) {
                __domRunFlow(bodyEl);
                return true;
            }
            return false; 
        }
    }

    if ((kids[idx].tagName === 'OL' || kids[idx].tagName === 'UL') && targetQ) {
        const effBottom = __flowEffBottom(bodyEl);
        const movedItems = __quillSplitList(quill, kids[idx], effBottom, targetQ);
        if (movedItems > 0) {
            __rememberPushedBlock(sheet, kids[idx]);
            try { __renumberContinuedListsAcrossSheets(); } catch (err) {}
            notifyDirty();
            if (autoPaginationApi) __runFlow(targetQ, targetBody);
            return true;
        }
    }

    let grpStart = idx;
    if (idx > 0 && __isContractHeadingLine(kids[idx - 1])) {
        grpStart = __headingGroupStart(kids, idx);
        if (grpStart === 0) grpStart = idx;
    }
    let grpEnd = idx + 1;
    if (__isContractHeadingLine(kids[idx])) {
        const fwd = __headingGroupEnd(kids, idx);
        if (fwd > grpEnd) {
            let ok = true;
            for (let i = idx; i < fwd; i++) {
                const t = kids[i] && kids[i].tagName;
                if ((t === 'TABLE' || t === 'OL' || t === 'UL')
                    && kids[i].getBoundingClientRect().height > bodyEl.clientHeight + PAGE_FLOW_TOL) {
                    ok = false;
                    break;
                }
            }
            if (ok) grpEnd = fwd;
        }
    }

    const remaining = kids.slice(grpStart);
    const overTableTooBig = kids[idx].tagName === 'TABLE'
        && kids[idx].getBoundingClientRect().height > bodyEl.clientHeight + PAGE_FLOW_TOL;
    const grpLastIdx = Math.max(idx, grpEnd - 1);
    const atomicGroup = grpLastIdx !== idx;
    const bulk = !atomicGroup
        && targetQ.getLength() <= 1
        && remaining.length > 0
        && !remaining.some(__isFloatingKid)
        && !overTableTooBig;
    if (overTableTooBig) return false;

    if (bulk
        && kids[idx].tagName !== 'TABLE'
        && kids[idx].tagName !== 'OL'
        && kids[idx].tagName !== 'UL') {
        try {
            const bRect = bodyEl.getBoundingClientRect();
            if (kids[idx].getBoundingClientRect().height > bRect.height - PAGE_FLOW_TOL) {
                console.warn('[DocQuill] Blok tunggal lebih tinggi dari satu halaman dan tak bisa dibelah — paginasi blok ini dihentikan aman.');
                return false;
            }
        } catch (err) { /* lanjut jalur normal */ }
    }

    const groupFirstRange = (grpStart < idx)
        ? __blockRangeOf(quill, kids[grpStart])
        : firstRange;
    if (!groupFirstRange) return false;
    const groupLastRange = (grpLastIdx !== idx)
        ? __blockRangeOf(quill, kids[grpLastIdx])
        : firstRange;
    if (!groupLastRange) return false;
    const range = bulk
        ? {
            start: groupFirstRange.start,
            len: Math.max(groupFirstRange.len, quill.getLength() - groupFirstRange.start),
        }
        : ((grpStart < idx || grpLastIdx !== idx)
            ? {
                start: groupFirstRange.start,
                len: (groupLastRange.start - groupFirstRange.start) + groupLastRange.len,
            }
            : firstRange);
    if (!range) return false;

    const DeltaCtor = __flowDeltaCtor(quill);
    const removed = quill.getContents(range.start, range.len);
    if (!DeltaCtor || !removed || !(removed.ops || []).length) return false;

    const selBefore = quill.getSelection();

    quill.deleteText(range.start, range.len, 'silent');

    const grpProbeLast = (typeof grpLastIdx === 'number') ? grpLastIdx : idx;
    const probeTable = kids.slice(grpStart, grpProbeLast + 1)
        .some((k) => k && k.tagName === 'TABLE');
    const trBefore = probeTable ? targetQ.root.querySelectorAll('table tr').length : 0;
    const contPlan = __flowContInsertPlan(targetQ);
    targetQ.updateContents(__flowContDelta(DeltaCtor, removed, contPlan), 'silent');

    if (probeTable) {
        const trAfter = targetQ.root.querySelectorAll('table tr').length;
        if (!(trAfter > trBefore)) {
            console.warn('[DocQuill] Paginasi Quill menelan tabel — isi dikembalikan, body diambil alih sebagai DOM.');
            const back = new DeltaCtor();
            back.retain(range.start);
            for (const op of removed.ops) {
                back.push(op.insert == null
                    ? { retain: __opLength(op) }
                    : JSON.parse(JSON.stringify(op)));
            }
            quill.updateContents(back, 'silent');
            if (__takeoverBodyAsDom(bodyEl)) {
                __domRunFlow(bodyEl);
                return true;
            }
            return false;
        }
    }

    __markFlowContinuationRun(targetQ, contPlan);

    if (selBefore && selBefore.index >= range.start
        && selBefore.index < range.start + range.len) {
        const off = Math.max(0, selBefore.index - range.start);
        targetQ.setSelection(off, selBefore.length || 0, 'silent');
    }

    if (autoPaginationApi) __runFlow(targetQ, targetBody);

    if (grpLastIdx !== idx) {
        __rememberPushedBlock(sheet, kids[idx]);
        __rememberPushedGroup(sheet,
            kids.slice(grpStart, grpLastIdx + 1).map(__flowSig).join('||'));
    } else {
        __rememberPushedBlock(sheet, kids[idx]);
    }

    notifyDirty();
    return true;
}

function __pullBackPass(quill, bodyEl) {
    const sheet = bodyEl.closest('.doc-sheet');
    if (!sheet) return false;

    try { if (quill.root && !quill.root.isConnected) return false; } catch (err) { return false; }

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
    const pullEnd = __headingGroupEnd(nkids, 0);
    const pullKids = nkids.slice(0, pullEnd);
    if (!pullKids.length) return false;
    const k2 = pullKids[0];
    if (__isFloatingKid(k2) || !__hasMeaningfulTextNode(k2)) return false;
    for (const pk of pullKids) {
        if (__isFloatingKid(pk) || !__hasMeaningfulTextNode(pk)) return false;
    }
    const groupHasSplitTable = pullKids.some((pk) =>
        pk.tagName === 'TABLE' && pk.dataset && pk.dataset.splitFrom);
    if (groupHasSplitTable) return false;
    if (pullKids.length === 1 && k2.tagName === 'TABLE') return false;
    if (pullKids.some((pk) => (pk.tagName === 'OL' || pk.tagName === 'UL')
        && pk.getBoundingClientRect().height > bodyEl.clientHeight + PAGE_FLOW_TOL)) return false;

    const pushed = pageFlowJustPushed.get(sheet);
    const pushedSig = __pushedSigFor(sheet);
    if (pushed && pushed === k2) return false;
    const pullSig = pullKids.map(__flowSig).join('||');
    if (pushedSig && (pushedSig === __flowSig(k2) || pushedSig === pullSig)) return false;

    let k2Len = 0;
    try {
        const b2 = Quill.find(k2);
        if (b2 && typeof b2.length === 'function') k2Len = b2.length();
    } catch (err) { k2Len = 0; }
    if (k2Len <= 1) return false;

    const boxRect = bodyEl.getBoundingClientRect();
    const padT = parseFloat(getComputedStyle(bodyEl).paddingTop || '0');
    const effBottom = __flowEffBottom(bodyEl);
    let baseBottom = boxRect.top + padT;
    for (let i = 0; i < kids.length; i++) {
        if (__isFloatingKid(kids[i])) continue;
        const kbot = kids[i].getBoundingClientRect().bottom;
        if (kbot > baseBottom) baseBottom = kbot;
    }

    
    const h2 = pullKids.reduce((sum, pk) => {
        try { return sum + pk.getBoundingClientRect().height; }
        catch (err) { return sum; }
    }, 0);
    if (baseBottom + h2 > effBottom - PAGE_FLOW_TOL - PAGE_FLOW_PULL_MARGIN) return false;

    const pullRanges = pullKids.map((pk) => __blockRangeOf(nextQ, pk));
    if (pullRanges.some((r) => !r)) return false;
    const pullStart = Math.min.apply(null, pullRanges.map((r) => r.start));
    const pullEndIdx = Math.max.apply(null, pullRanges.map((r) => r.start + r.len));
    const range = { start: pullStart, len: pullEndIdx - pullStart };
    if (!(range.len > 0)) return false;
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

    try {
        const key = __pushedSigKey(sheet);
        if (key) pageFlowJustPushedSig.set(key, pullSig);
    } catch (err) { /* noop */ }

    notifyDirty();
    return true;
}

function __runFlow(quill, bodyEl) {
    if (!autoPaginationApi || pageFlowHalted) return; 
    try { if (quill && quill.root && !quill.root.isConnected) return; } catch (err) { /* noop */ }
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
                let moved = await __flowPass(quill, bodyEl);
                if (!moved) {
                    moved = await __pullBackPass(quill, bodyEl);
                }
                if (!moved) break;
                movedAny = true;
                pageFlowTotalRuns = 0;
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
            try { __renumberContinuedListsAcrossSheets(); } catch (err) { /* noop */ }
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
            __resetPushMemory();
            __runFlow(quill, regionEl);
        }, 140);
    };

    quill.on('text-change', (_d, _o, source) => {
        if (source === 'silent') return;
        if (regionEl.dataset?.region !== 'body') return;
        schedule();
    });

    window.addEventListener('resize', () => schedule(), { passive: true });

    if (regionEl.dataset?.region === 'body') {
        regionEl.__scheduleFlow = schedule;
        regionEl.addEventListener('load', schedule, true);
        if ('ResizeObserver' in window) {
            let lastH = regionEl.clientHeight;
            new ResizeObserver(() => {
                const h = regionEl.clientHeight;
                if (Math.abs(h - lastH) < 2) return;
                lastH = h;
                schedule();
            }).observe(regionEl);
        }
    }

   
    setTimeout(() => __runFlow(quill, regionEl), 350);
}

function __quillSplitTable(quill, tableEl, effBottom, targetBody, targetQ) {
    try {
        if (__isAtomicFlowKid(tableEl)) return 0;

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
            let blocked = false;
            for (let r = 0; r < i; r++) {
                const cells = Array.from(rows[r].cells || []);
                for (const cell of cells) {
                    const rs = parseInt(cell.getAttribute('rowspan') || '1', 10) || 1;
                    if (rs > 1 && r + rs > i) { blocked = true; break; }
                }
                if (blocked) break;
            }
            if (!blocked) { safeCut = i; break; }
            safeCut = i + 1;
        }
        if (safeCut >= rows.length) return 0;
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
        const trBefore = targetQ.root.querySelectorAll('table tr').length;
        const contPlan = __flowContInsertPlan(targetQ);
        targetQ.updateContents(__flowContDelta(DeltaCtor, delta, contPlan), 'silent');
        const trAfter = targetQ.root.querySelectorAll('table tr').length;
        if (!(trAfter > trBefore)) {
            console.warn('[DocQuill] Belah tabel Quill menelan isi — dibatalkan.');
            return 0;
        }
        __markFlowContinuationRun(targetQ, contPlan);
        quill.deleteText(start, end - start, 'silent');
        return rows.length - safeCut;
    } catch (err) { return 0; }
}

function __quillSplitList(quill, listEl, effBottom, targetQ) {
    try {
        const items = Array.from(listEl.children || []).filter((c) => c.tagName === 'LI');
        if (items.length < 2) return 0;
        let cut = -1;
        for (let i = 0; i < items.length; i++) {
            if (items[i].getBoundingClientRect().bottom - PAGE_FLOW_TOL > effBottom) {
                cut = i;
                break;
            }
        }
        if (cut <= 0 || cut >= items.length) return 0;
        const srcList = listEl.cloneNode(false);
        srcList.removeAttribute('id');
        for (let i = cut; i < items.length; i++) {
            srcList.appendChild(items[i].cloneNode(true));
        }
        let delta = null;
        try {
            delta = targetQ.clipboard.convert({ html: srcList.outerHTML, text: '\n' });
        } catch (err) { delta = null; }
        if (!delta || !(delta.ops || []).length) return 0;
        const DeltaCtor = __flowDeltaCtor(quill);
        if (!DeltaCtor) return 0;
        const itemRanges = [];
        for (let i = cut; i < items.length; i++) {
            const r = __blockRangeOf(quill, items[i]);
            if (r) itemRanges.push(r);
        }
        if (itemRanges.length !== items.length - cut) return 0;
        const start = Math.min.apply(null, itemRanges.map((r) => r.start));
        const end = Math.max.apply(null, itemRanges.map((r) => r.start + r.len));
        const removed = quill.getContents(start, end - start);
        if (!removed || !(removed.ops || []).length) return 0;
        const liBefore = targetQ.root.querySelectorAll('li').length;
        const contPlan = __flowContInsertPlan(targetQ);
        targetQ.updateContents(__flowContDelta(DeltaCtor, delta, contPlan), 'silent');
        const liAfter = targetQ.root.querySelectorAll('li').length;
        if (!(liAfter > liBefore)) {
            console.warn('[DocQuill] Belah list Quill menelan isi — dibatalkan.');
            return 0;
        }
        __markFlowContinuationRun(targetQ, contPlan);
        quill.deleteText(start, end - start, 'silent');
        return items.length - cut;
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

        const shim = makeEditorShim(root);
        if (!registeredImageToolEditors.some((ed) => ed.rootEl === root)) {
            registeredImageToolEditors.push(shim);
        }

        const regions = root.querySelectorAll('.doc-sheet-body, .doc-sheet-header, .doc-sheet-footer');
        regions.forEach(attachQuillToRegion);

        root.addEventListener('click', (e) => {
            if (e.target.nodeName !== 'IMG') return;
            if (e.target.closest('.doc-signature')) return;
            if (activeImage === e.target) {
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




window.DocQuill = {
    __version: 'hf-12-listcont',

    renumberContinuedLists: () => {
        try { return __renumberContinuedListsAcrossSheets(); } catch (err) { return 0; }
    },

    attachRegion: attachQuillToRegion,

    getHtml: (regionEl) => {
        if (!regionEl) return '';
        const q = quillsByRegion.get(regionEl);
        let html = q ? q.root.innerHTML : regionEl.innerHTML;
        regionEl.querySelectorAll(':scope > img').forEach((im) => {
            try {
                if (q && q.root && q.root.contains(im)) return;
                if (html && html.indexOf(im.outerHTML) >= 0) return;
            } catch (err) { /* lanjut sertakan */ }
            html += im.outerHTML;
        });
        return __stripFlowInternalAttrs(html);
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
        try {
            document.querySelectorAll('.doc-sheet-body[data-region="body"][data-dom-flow="1"]')
                .forEach((el) => bindDomPageOverflowWatch(el));
        } catch (err) { /* noop */ }
    },

    clickAndType: (regionEl, clientX, clientY) => {
        console.log('[clickAndType] dipanggil', { clientX, clientY });
        const q = quillsByRegion.get(regionEl);
        if (!q) {
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