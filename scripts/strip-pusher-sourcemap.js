const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..', 'node_modules');

const files = [
  path.join(root, 'pusher-js', 'dist', 'react-native', 'pusher.js'),
  path.join(root, 'laravel-echo', 'dist', 'echo.common.js'),
  path.join(root, 'laravel-echo', 'dist', 'echo.iife.js'),
  path.join(root, 'laravel-echo', 'dist', 'echo.js'),
];

files.forEach(file => {
  if (!fs.existsSync(file)) return;
  let content = fs.readFileSync(file, 'utf8');
  const before = content.length;
  content = content.replace(/\n\/\/#\s*sourceMappingURL=\S+/g, '');
  if (content.length < before) {
    fs.writeFileSync(file, content);
    console.log('Patched:', path.basename(file));
  }
});
