<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

if (!empty($_POST['_honey'])) {
    echo json_encode(['ok' => true]);
    exit;
}

function clean($value) {
    $value = trim((string) $value);
    $value = str_replace(["\r", "\n"], ' ', $value);
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$name  = clean($_POST['ФИО'] ?? '');
$phone = clean($_POST['Телефон'] ?? '');
$breed   = clean($_POST['Размер собаки'] ?? '');
$granule = clean($_POST['Размер гранулы'] ?? '');
$pack    = clean($_POST['Фасовка'] ?? '');
$qty     = clean($_POST['Количество упаковок'] ?? '');
$total   = clean($_POST['Итоговая сумма'] ?? '');

if ($name === '' || $phone === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'missing_required_fields']);
    exit;
}

$subject = 'Новая заявка с сайта НОРМАПЛЮС';

$body  = "Новая заявка с сайта normaplus.ru\n\n";
$body .= "ФИО: {$name}\n";
$body .= "Телефон: {$phone}\n";
$body .= "Размер собаки: {$breed}\n";
$body .= "Размер гранулы: {$granule}\n";
$body .= "Фасовка: {$pack}\n";
$body .= "Количество упаковок: {$qty}\n";
$body .= "Итоговая сумма: {$total}\n";
$body .= "\nВремя заявки: " . date('d.m.Y H:i:s') . "\n";

require_once __DIR__ . '/smtp-mailer.php';
$cfg = require __DIR__ . '/mail-config.php';

$result = smtp_send_mail($cfg, $cfg['to_email'], $subject, $body);

if ($result['ok']) {
    echo json_encode(['ok' => true]);
} else {
    error_log('SMTP send failed: ' . $result['error']);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $result['error']]);
}
