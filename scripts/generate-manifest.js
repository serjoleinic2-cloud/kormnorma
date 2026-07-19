// Сканирует папку documents/ и строит documents/manifest.json
// Запускается GitHub Action-ом при каждом пуше.
const fs = require('fs');
const path = require('path');

const DOCS_DIR = path.join(__dirname, '..', 'documents');
const MANIFEST_PATH = path.join(DOCS_DIR, 'manifest.json');

const ALLOWED_EXT = ['.pdf', '.jpg', '.jpeg', '.png', '.webp'];
const IGNORE = new Set(['manifest.json', 'README.md', '.gitkeep']);

function titleFromFilename(filename) {
  const base = filename.replace(/\.[^.]+$/, '');
  const spaced = base.replace(/[-_]+/g, ' ').trim();
  return spaced.charAt(0).toUpperCase() + spaced.slice(1);
}

function build() {
  if (!fs.existsSync(DOCS_DIR)) {
    fs.mkdirSync(DOCS_DIR, { recursive: true });
  }

  const files = fs.readdirSync(DOCS_DIR).filter((f) => {
    if (IGNORE.has(f)) return false;
    const ext = path.extname(f).toLowerCase();
    return ALLOWED_EXT.includes(ext);
  });

  files.sort((a, b) => a.localeCompare(b, 'ru'));

  const manifest = files.map((file) => {
    const ext = path.extname(file).slice(1).toUpperCase();
    return {
      file,
      title: titleFromFilename(file),
      type: ext,
    };
  });

  fs.writeFileSync(MANIFEST_PATH, JSON.stringify(manifest, null, 2) + '\n', 'utf-8');
  console.log(`manifest.json: ${manifest.length} файл(ов)`);
}

build();
