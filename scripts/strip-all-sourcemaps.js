const fs = require('fs');
const path = require('path');

function stripDir(dir) {
  if (!fs.existsSync(dir)) return;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      stripDir(full);
    } else if (entry.name.endsWith('.js')) {
      let c = fs.readFileSync(full, 'utf8');
      const before = c.length;
      c = c.replace(/\n\/\/#\s*sourceMappingURL=\S+/g, '');
      if (c.length < before) {
        fs.writeFileSync(full, c);
        console.log('patched:', entry.name);
      }
    }
  }
}

const nm = path.join(__dirname, '..', 'node_modules');
stripDir(path.join(nm, 'pusher-js'));
stripDir(path.join(nm, 'laravel-echo'));
console.log('done');
