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

class Col {
  constructor(width) {
    this.attributes = { width: `${width}%` };
    this.style = {};
  }
  getAttribute(name) { return this.attributes[name] || null; }
  setAttribute(name, value) { this.attributes[name] = value; }
}

class ColgroupMock {
  constructor(widths) {
    this.cols = widths.map((width) => new Col(width));
  }
  querySelectorAll() { return this.cols; }
  appendChild(col) { this.cols.push(col); }
  remove() { this.removed = true; }
}

class Row {
  constructor(cells) { this.children = cells; }
  get firstChild() { return this.children[0] || null; }
  removeChild(node) { this.children.splice(this.children.indexOf(node), 1); }
  appendChild(fragment) { this.children.push(...fragment.children); }
}

class Colgroup {
  constructor(widths) {
    this.cols = widths.map((width) => new Col(width));
  }
  querySelectorAll() { return this.cols; }
  appendChild(col) { this.cols.push(col); }
  remove() { this.removed = true; }
}

function createTable(widths, colgroup = null) {
  const rows = [new Row(widths.map((width) => new Cell(width)))];
  return {
    rows,
    colgroup,
    firstChild: null,
    attributes: {},
    querySelector(selector) { return selector === 'colgroup' ? this.colgroup : null; },
    getAttribute(name) { return this.attributes[name] || null; },
    setAttribute(name, value) { this.attributes[name] = value; },
    insertBefore(node) { this.colgroup = node; },
  };
}

const fakeDocument = {
  createDocumentFragment: () => new Fragment(),
  createElement: (tag) => (String(tag).toLowerCase() === 'colgroup' ? new Colgroup([]) : new Cell()),
};

const normalize = new Function(
  'document',
  `${src.slice(start, end)}; return normalizeTableGrid;`
)(fakeDocument);

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

test('retains proportional column groups when normalizing editor tables', () => {
  const colgroup = new Colgroup([7, 13, 22, 12, 14, 14, 18]);
  const table = createTable([null, null, null, null, null, null, null], colgroup);

  normalize(table);

  assert.deepStrictEqual(
    table.colgroup.cols.map((col) => col.getAttribute('width')),
    ['7.00%', '13.00%', '22.00%', '12.00%', '14.00%', '14.00%', '18.00%']
  );
  assert.deepStrictEqual(
    table.rows[0].children.map((cell) => cell.style.width),
    ['7.00%', '13.00%', '22.00%', '12.00%', '14.00%', '14.00%', '18.00%']
  );
});

test('locks colgroup widths for rowspan tables instead of inferring per-cell', () => {
  const colgroup = new Colgroup([7, 23, 20, 50]);
  const table = createTable([null, null, null, null], colgroup);

  normalize(table);

  assert.deepStrictEqual(
    table.colgroup.cols.map((col) => col.getAttribute('width')),
    ['7.00%', '23.00%', '20.00%', '50.00%']
  );
});
