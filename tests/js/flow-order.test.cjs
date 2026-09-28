/* eslint-disable no-console */
// Tes tanpa dependensi (tidak ada jsdom di project) untuk helper urutan
// continuation di resources/js/editor.js.
//
// Helper diuji dengan DOM shim minimal: hanya bagian yang dipakai
// __isFlowContinuation / __flowContBoundary / __domFlowInsertContinuation.

const fs = require('fs');
const path = require('path');
const assert = require('assert');

const src = fs.readFileSync(
  path.join(__dirname, '..', '..', 'resources', 'js', 'editor.js'),
  'utf8'
);

// Helper dibungkus object literal supaya bisa dievaluasi terpisah dari
// sisa editor.js (yang butuh Alpine/Quill/browser).
function extract(block, names) {
  const start = src.indexOf(block);
  assert.ok(start >= 0, 'blok helper tidak ditemukan: ' + block);
  const open = src.indexOf('{', start);
  let depth = 0;
  let end = -1;
  for (let i = open; i < src.length; i++) {
    if (src[i] === '{') depth++;
    else if (src[i] === '}') {
      depth--;
      if (depth === 0) { end = i + 1; break; }
    }
  }
  assert.ok(end > 0, 'blok helper tidak tertutup: ' + block);
  return names.map((n) => src.slice(start, end)).join('\n');
}

const helperSource = [
  extract('function __isFlowContinuation(node) {', ['__isFlowContinuation']),
  extract('function __flowContBoundary(root) {', ['__flowContBoundary']),
  extract('function __domFlowInsertContinuation(targetBody, nodes) {', ['__domFlowInsertContinuation']),
  extract('function __flowContInsertPlan(targetQ) {', ['__flowContInsertPlan']),
  extract('function __flowContDelta(DeltaCtor, delta, plan) {', ['__flowContDelta']),
  extract('function __markFlowContinuationRun(targetQ, plan) {', ['__markFlowContinuationRun']),
  extract('function __isContractHeadingLine(el) {', ['__isContractHeadingLine']),
  extract('function __headingGroupStart(kids, idx) {', ['__headingGroupStart']),
  extract('function __headingGroupEnd(kids, idx) {', ['__headingGroupEnd']),
  extract('function __opLength(op) {', ['__opLength']),
].join('\n');

// --- DOM shim minimal ---------------------------------------------
class El {
  constructor(name) {
    this.tagName = name.toUpperCase();
    this.children = [];
    this.parent = null;
    this.dataset = {};
  }
  get firstChild() { return this.children[0] || null; }
  appendChild(n) { return this.insertBefore(n, null); }
  insertBefore(node, ref) {
    if (node.parent) {
      node.parent.children = node.parent.children.filter((c) => c !== node);
    }
    const at = ref ? this.children.indexOf(ref) : -1;
    if (at < 0) this.children.push(node);
    else this.children.splice(at, 0, node);
    node.parent = this;
    return node;
  }
  text() { return this.children.map((c) => c.text()).join(''); }
}

const oldA = new El('p'); oldA.text = () => 'LAMA-A';
const oldB = new El('p'); oldB.text = () => 'LAMA-B';
const body = new El('div');
body.insertBefore(oldA, null);
body.insertBefore(oldB, null);
const legacy = new El('p');
legacy.text = () => 'CONT-LAMA';
legacy.dataset.flowCont = '1';
body.insertBefore(legacy, body.children[0]);     // continuation lama

// shim document untuk helper yang parse HTML
global.document = {
  createElement: (n) => new El(n),
};

// eslint-disable-next-line no-new-func
const factory = new Function(
  'document',
  helperSource + '\nreturn { __domFlowInsertContinuation, __flowContBoundary,'
  + ' __flowContInsertPlan, __flowContDelta, __markFlowContinuationRun,'
  + ' __isContractHeadingLine, __headingGroupStart, __headingGroupEnd };'
);
const { __domFlowInsertContinuation, __flowContBoundary, __flowContInsertPlan,
  __flowContDelta, __markFlowContinuationRun,
  __isContractHeadingLine, __headingGroupStart, __headingGroupEnd } = factory(global.document);

// Blok A = luapan pasal, Blok B = luapan lampiran, keduanya menyusul
// continuation lama. Keduanya harus tetap berurutan sesuai urutan sumber;
// perilaku lama (insertBefore firstChild) membalik urutan ini.
const pasal = new El('p'); pasal.text = () => 'PASAL-4';
const lampiran = new El('table'); lampiran.text = () => 'LAMPIRAN-1';

__domFlowInsertContinuation(body, pasal);
__domFlowInsertContinuation(body, lampiran);

const order = body.children.map((c) => c.text()).join(',');
console.log('urutan hasil:', order);

assert.strictEqual(
  order,
  'CONT-LAMA,PASAL-4,LAMPIRAN-1,LAMA-A,LAMA-B',
  'blok lanjutan harus menyusul continuation lama, lalu konten lama, '
  + 'sesuai urutan sumber (bukan saling membalik)'
);
assert.strictEqual(
  __flowContBoundary(body),
  oldA,
  'boundary = anak pertama yang bukan continuation'
);

// Dua blok disisipkan bersama (satu panggilan) tetap urut.
const body2 = new El('div');
const x = new El('p'); x.text = () => 'X';
const y = new El('p'); y.text = () => 'Y';
__domFlowInsertContinuation(body2, [x, y]);
assert.strictEqual(body2.children.map((c) => c.text()).join(','), 'X,Y');

// --- Regresi fallback marker+firstChild (bug pasal/lampiran acak) -------
//
// Dua situs fallback di editor.js dulu memakai pola:
//   marker di firstChild, lalu tiap node disisip SEBELUM marker.
// Untuk satu batch itu urut, tapi batch KEDUA mendarat paling depan dan
// membalik urutan batch pertama. Perbaikan: kedua situs memakai
// __domFlowInsertContinuation sehingga batch baru MENYUSUL run
// continuation yang sudah ada.
const body3 = new El('div');
const contLama3 = new El('p'); contLama3.text = () => 'CONT-LAMA';
contLama3.dataset.flowCont = '1';
const lama3 = new El('p'); lama3.text = () => 'LAMA';
body3.insertBefore(contLama3, null);
body3.insertBefore(lama3, null);

const batchA1 = new El('p'); batchA1.text = () => 'PASAL-4-JUDUL';
const batchA2 = new El('p'); batchA2.text = () => 'PASAL-4-ISI';
__domFlowInsertContinuation(body3, [batchA1, batchA2]);

const batchB1 = new El('p'); batchB1.text = () => 'LAMPIRAN-JUDUL';
const batchB2 = new El('table'); batchB2.text = () => 'LAMPIRAN-TABEL';
__domFlowInsertContinuation(body3, [batchB1, batchB2]);

assert.strictEqual(
  body3.children.map((c) => c.text()).join(','),
  'CONT-LAMA,PASAL-4-JUDUL,PASAL-4-ISI,LAMPIRAN-JUDUL,LAMPIRAN-TABEL,LAMA',
  'batch lanjutan susulan harus menyusul continuation lama sesuai urutan sumber'
);
assert.strictEqual(
  __flowContBoundary(body3),
  lama3,
  'boundary batch susulan = konten lama pertama'
);

// --- Jalur Quill (akar bug sebenarnya) ----------------------------
//
// Empat splitter Quill membuat Delta lalu updateContents(). Tanpa `retain`
// di depan, Quill SELALU menyisipkan di index 0 — sehingga tiap blok
// lanjutan yang menyusul mendahului blok sebelumnya. Dipatch dengan
// __flowContInsertPlan + __flowContDelta.

class FakeDelta {
  constructor() { this.ops = []; }
  retain(n) { this.ops.push({ retain: n }); return this; }
  push(op) { this.ops.push(op); return this; }
}

// shim Quill: root berupa elemen, getIndex() mengembalikan offset karakter
// anak, updateContents() benar-benar menyisipkan sesuai retain/insert.
function makeQuill(rootKids, docLen) {
  const q = {
    root: new El('div'),
    _inserted: 0,
  };
  rootKids.forEach((k) => q.root.insertBefore(k, null));
  q.getIndex = (node) => {
    const at = q.root.children.indexOf(node);
    if (at < 0) return 0;
    // offset kasar: satu blok = 1 "karakter" + pemisah newline
    return at * 2;
  };
  // Panjang dokumen kasar untuk plan append-di-akhir (boundary null).
  // Default: 2 per blok (= konsisten dengan getIndex di atas).
  q.getLength = () => (typeof docLen === 'number'
    ? docLen
    : q.root.children.length * 2);
  q.updateContents = (delta) => {
    let idx = 0;
    for (const op of delta.ops) {
      if (op.retain != null) { idx += op.retain; continue; }
      if (op.insert != null) {
        const el = new El(typeof op.insert === 'string' ? 'p' : 'span');
        el.text = () => String(op.insert);
        q.root.insertBefore(el, q.root.children[idx / 2] || null);
        idx += 2;
      }
    }
  };
  return q;
}

const qOldA = new El('p'); qOldA.text = () => 'Q-LAMA-A';
const qOldB = new El('p'); qOldB.text = () => 'Q-LAMA-B';
const q = makeQuill([qOldA, qOldB]);

// 1) Blok lanjutan pertama (pasal) -> harus mendahului konten lama.
const plan1 = __flowContInsertPlan(q);
assert.strictEqual(plan1.index, 0, 'halaman tanpa continuation: sisip di index 0');
q.updateContents(__flowContDelta(FakeDelta, { ops: [{ insert: 'Q-PASAL-4' }] }, plan1));
__markFlowContinuationRun(q, plan1);

// 2) Blok lanjutan kedua (lampiran) -> harus MENYUSUL continuation pertama.
const plan2 = __flowContInsertPlan(q);
q.updateContents(__flowContDelta(FakeDelta, { ops: [{ insert: 'Q-LAMPIRAN-1' }] }, plan2));
__markFlowContinuationRun(q, plan2);

const qOrder = q.root.children.map((c) => c.text()).join(',');
console.log('urutan hasil (Quill):', qOrder);

assert.strictEqual(
  qOrder,
  'Q-PASAL-4,Q-LAMPIRAN-1,Q-LAMA-A,Q-LAMA-B',
  'jalur Quill harus menyusul continuation sebelumnya, bukan menyisip di index 0'
);

// Penanda harus benar-benar terpasang pada blok lanjutan, dan TIDAK pada
// konten lama (kalau konten lama ikut ditandai, batasnya geser ke depan).
assert.strictEqual(q.root.children[0].dataset.flowCont, '1');
assert.strictEqual(q.root.children[1].dataset.flowCont, '1');
assert.strictEqual(q.root.children[2].dataset.flowCont, undefined);
assert.strictEqual(q.root.children[3].dataset.flowCont, undefined);

// Nilai yang sama harus ter-retain dengan benar oleh __flowContDelta.
const planIdx = __flowContInsertPlan(q).index;
assert.ok(planIdx > 0, 'setelah ada continuation, index harus > 0');

// --- Regresi boundary-null jalur Quill (pasal acak + lampiran di tengah) --
//
// Skenario SOHO nyata: halaman tujuan isinya SEMUA continuation (Pasal 4
// sudah mendarat), lalu batch susulan (isi Pasal 4, lalu tabel Lampiran)
// datang. `__flowContBoundary` = null. Jalur DOM append di akhir (benar),
// tapi jalur Quill lama `retain(0)` = sisip paling depan (salah) sehingga
// tiap batch membalik batch sebelumnya.
const qFullA = new El('p'); qFullA.text = () => 'Q-PASAL-4';
qFullA.dataset.flowCont = '1';
const qFullB = new El('p'); qFullB.text = () => 'Q-ISI-4';
qFullB.dataset.flowCont = '1';
const qFull = makeQuill([qFullA, qFullB]);

// Plan boundary-null harus append di akhir (index = panjang dokumen),
// bukan 0.
const planFull1 = __flowContInsertPlan(qFull);
assert.ok(planFull1.index > 0, 'boundary null: plan harus append di akhir, bukan index 0');
assert.strictEqual(planFull1.boundary, null, 'boundary null tetap null');
qFull.updateContents(__flowContDelta(FakeDelta, { ops: [{ insert: 'Q-PASAL-5' }] }, planFull1));
__markFlowContinuationRun(qFull, planFull1);

// Batch susulan kedua (tabel lampiran) harus MENYUSUL, bukan membalik.
const planFull2 = __flowContInsertPlan(qFull);
qFull.updateContents(__flowContDelta(FakeDelta, { ops: [{ insert: 'Q-LAMPIRAN-A' }] }, planFull2));
__markFlowContinuationRun(qFull, planFull2);

assert.strictEqual(
  qFull.root.children.map((c) => c.text()).join(','),
  'Q-PASAL-4,Q-ISI-4,Q-PASAL-5,Q-LAMPIRAN-A',
  'batch susulan di halaman penuh-continuation harus append berurutan (Quill sama dengan DOM)'
);

// --- Grup heading atomik dua arah (SOHO) ---------------------------
//
// Shim heading: <p> yang seluruh teksnya bold. El minimal tidak punya
// textContent/querySelector/cloneNode, jadi bungkus dengan properti yang
// dipakai __isContractHeadingLine.
function headEl(text) {
  const el = new El('p');
  el.text = () => text;
  el.textContent = text;
  el.querySelector = (sel) => (sel === 'strong, b' ? {} : null);
  el.querySelectorAll = () => [];
  el.cloneNode = () => ({ querySelectorAll: () => [], textContent: '' });
  return el;
}
function bodyEl(text) {
  const el = new El('p');
  el.text = () => text;
  el.textContent = text;
  el.querySelector = () => null;
  return el;
}

// 1) Mundur: blok isi yang meluap menyerap PASAL N + judul di atasnya.
const hPasal = headEl('PASAL 4');
const hJudul = headEl('JANGKA WAKTU');
const isi4 = bodyEl('ISI-4');
assert.ok(__isContractHeadingLine(hPasal), 'PASAL N terdeteksi heading');
assert.ok(__isContractHeadingLine(hJudul), 'judul pasal terdeteksi heading');
assert.ok(!__isContractHeadingLine(isi4), 'isi bukan heading');
assert.strictEqual(
  __headingGroupStart([hPasal, hJudul, isi4], 2),
  0,
  'grup mundur mencakup PASAL N + judul'
);

// 2) Maju (anti-yatim): heading yang meluap sendiri menyerap 1 konten di
// bawahnya — PASAL + judul + isi pindah sebagai satu grup utuh.
assert.strictEqual(
  __headingGroupEnd([hPasal, hJudul, isi4], 0),
  3,
  'grup maju heading mencakup judul + 1 isi'
);

// 3) Bukan heading: grup hanya 1 blok (perilaku lama tidak berubah).
assert.strictEqual(
  __headingGroupEnd([isi4, bodyEl('ISI-5')], 0),
  1,
  'blok isi biasa tidak menyerap blok berikutnya'
);

// 4) LAMPIRAN A + tabel: heading lampiran menyerap tabel kecil menempel
// (keep-with-table) supaya judul tidak tertinggal tanpa tabelnya.
const hLamp = headEl('LAMPIRAN A');
const tblLamp = new El('table'); tblLamp.text = () => 'TABEL-LAMPIRAN-A';
assert.strictEqual(
  __headingGroupEnd([hLamp, tblLamp], 0),
  2,
  'heading LAMPIRAN menyerap tabel kecil menempel'
);

console.log('OK: grup heading atomik dua arah utuh (mundur + maju)');
console.log('\nOK: helper urutan continuation berperilaku benar (DOM + Quill)');
