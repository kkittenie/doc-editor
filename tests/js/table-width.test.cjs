const fs = require('fs');
const path = require('path');
const assert = require('assert');
const test = require('node:test');

const src = fs.readFileSync(
  path.join(__dirname, '..', '..', 'resources', 'js', 'editor.js'),
  'utf8'
);

const start = src.indexOf('const normalizeTableGrid = (table) => {');
assert.ok(start >= 0, 'normalizeTableGrid helper not found');
const open = src.indexOf('{', start);
let depth = 0;
let end = -1;
for (let i = open; i < src.length; i++) {
  if (src[i] === '{') depth++;
  else if (src[i] === '}' && --depth === 0) {
    end = i + 1;
    break;
  }
}
assert.ok(end > 0, 'normalizeTableGrid helper is not closed');

class Fragment {
  constructor() { this.children = []; }
  appendChild(node) { this.children.push(node); }
}

class Cell {
  constructor(width) {
    this.tagName = 'TD';
    this.attributes = width ? { style: `width:${width}%` } : {};
    this.style = {};
    this.classList = { add() {} };
    this.innerHTML = 'cell';
  }
  getAttribute(name) { return this.attributes[name] || null; }
  setAttribute(name, value) { this.attributes[name] = value; }
}

class Row {
  constructor(cells) { this.children = cells; }
  get firstChild() { return this.children[0] || null; }
  removeChild(node) { this.children.splice(this.children.indexOf(node), 1); }
  appendChild(fragment) { this.children.push(...fragment.children); }
}

function createTable(widths) {
  const rows = [new Row(widths.map((width) => new Cell(width)))];
  return {
    rows,
    attributes: {},
    querySelector() { return null; },
    getAttribute(name) { return this.attributes[name] || null; },
    setAttribute(name, value) { this.attributes[name] = value; },
  };
}

const normalize = new Function(
  'document',
  `${src.slice(start, end)}; return normalizeTableGrid;`
)({
  createDocumentFragment: () => new Fragment(),
  createElement: () => new Cell(),
});

test('keeps inline proportional widths when normalizing a table without colgroup', () => {
  const table = createTable([7, 13, 22, 12, 14, 14, 18]);

  normalize(table);

  assert.deepStrictEqual(
    table.rows[0].children.map((cell) => cell.style.width),
    ['7.00%', '13.00%', '22.00%', '12.00%', '14.00%', '14.00%', '18.00%']
  );
});

test('uses equal widths when inline widths do not define a complete grid', () => {
  const table = createTable([null, null, null]);

  normalize(table);

  assert.deepStrictEqual(
    table.rows[0].children.map((cell) => cell.style.width),
    ['33.33%', '33.33%', '33.33%']
  );
});
