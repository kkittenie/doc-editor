const fs = require('fs');
const sm = JSON.parse(fs.readFileSync('node_modules/quill/dist/quill.js.map', 'utf8'));
const src = sm.sources || [];
console.log('Total sources:', src.length);
console.log('\nTable-related sources:');
src.filter(s => /table/i.test(s)).forEach(s => console.log('  ' + s));
console.log('\nModule sources:');
src.filter(s => /modules/i.test(s) && !/node_modules/.test(s)).forEach(s => console.log('  ' + s));
