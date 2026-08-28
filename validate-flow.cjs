// Behavioral check of the pagination engine ordering fix.
// Mirrors Quill's real semantics: updateContents() applies a delta
// starting at index 0 (pure inserts PREPEND; retain(N)+insert appends).
const Delta = require('quill-delta');

function makeDoc(blocks) {
    const d = {
        text: blocks.join(''),
        kids: blocks,
        applyDelta(delta) {
            let pos = 0;
            for (const op of delta.ops || []) {
                if (op.retain != null) pos += op.retain;
                else if (typeof op.insert === 'string') {
                    d.text = d.text.slice(0, pos) + op.insert + d.text.slice(pos);
                    pos += op.insert.length;
                }
            }
            d.kids = d.text.split('\n').filter((s, i, a) => i < a.length - 1 || s !== '').map((s) => s + '\n');
        },
        deleteText(start, len) { d.text = d.text.slice(0, start) + d.text.slice(start + len); d.sync(); },
        sync() {
            const parts = d.text.split('\n');
            parts.pop();
            d.kids = parts.length ? parts.map((s) => s + '\n') : ['\n'];
        },
        getLength() { return d.text.length; },
        blockRange(i) {
            let start = 0;
            for (let k = 0; k < i; k++) start += d.kids[k].length;
            return { start, len: d.kids[i].length };
        },
        getContents(start, len) { return new Delta().insert(d.text.substr(start, len)); },
        // OLD buggy pullBack insertion:
        updateContentsOLD(delta) { d.applyDelta(delta); },
    };
    d.sync();
    return d;
}

function pullBackOLD(cur, next) {
    const r = { start: 0, len: next.kids[0].length };
    const removed = next.getContents(r.start, r.len);
    next.deleteText(r.start, r.len);
    const chg = new Delta();
    for (const op of removed.ops) chg.push(op);
    cur.updateContentsOLD(chg); // inserts at index 0 -> SCRAMBLE
}

function pullBackFIXED(cur, next) {
    const r = { start: 0, len: next.kids[0].length };
    const removed = next.getContents(r.start, r.len);
    next.deleteText(r.start, r.len);
    const chg = new Delta();
    chg.retain(Math.max(0, cur.getLength())); // append at END
    for (const op of removed.ops) chg.push(op);
    cur.updateContentsOLD(chg);
}

// Scenario: full page [C1..C5], next page [P1..P10]; pull P1..P3 back one by one.
const p = ['C1\n', 'C2\n', 'C3\n', 'C4\n', 'C5\n'];
const n = Array.from({ length: 10 }, (_, i) => 'P' + (i + 1) + '\n');

const oldCur = makeDoc([...p]); const oldNext = makeDoc([...n]);
for (let i = 0; i < 3; i++) pullBackOLD(oldCur, oldNext);
console.log('OLD  page1:', oldCur.kids.map((b) => b.trim()).join(' '));

const fixCur = makeDoc([...p]); const fixNext = makeDoc([...n]);
for (let i = 0; i < 3; i++) pullBackFIXED(fixCur, fixNext);
console.log('FIX  page1:', fixCur.kids.map((b) => b.trim()).join(' '));
console.log('FIX  page2:', fixNext.kids.map((b) => b.trim()).join(' '));

const okFix = fixCur.kids.map((b) => b.trim()).join(',') === 'C1,C2,C3,C4,C5,P1,P2,P3'
    && fixNext.kids.map((b) => b.trim()).join(',') === 'P4,P5,P6,P7,P8,P9,P10';
const badOld = oldCur.kids[0].trim() === 'P1';
console.log('FIX preserves document order:', okFix ? 'YES' : 'NO');
console.log('OLD scrambled order (bug reproduced):', badOld ? 'YES' : 'NO');
process.exit(okFix ? 0 : 1);
