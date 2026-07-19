<?php
/**
 * Сканирует папку documents/ и отдаёт JSON со списком файлов.
 * Никакой настройки не требует — просто кладёте файлы в documents/
 * и они появляются на сайте при следующей загрузке страницы.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$docsDir = __DIR__ . '/documents';
$allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
$ignore = ['manifest.json', 'README.md', '.gitkeep', 'index.php'];

$result = [];

if (is_dir($docsDir)) {
    $files = scandir($docsDir);
    natcasesort($files);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (in_array($file, $ignore, true)) continue;
        if (is_dir($docsDir . '/' . $file)) continue;

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) continue;

        $title = pathinfo($file, PATHINFO_FILENAME);
        $title = str_replace(['-', '_'], ' ', $title);
        $title = mb_strtoupper(mb_substr($title, 0, 1)) . mb_substr($title, 1);

        $result[] = [
            'file'  => $file,
            'title' => $title,
            'type'  => strtoupper($ext),
        ];
    }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
