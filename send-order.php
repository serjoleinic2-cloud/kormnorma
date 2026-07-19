<?php
/**
 * Принимает POST от формы заявки и отправляет письмо через mail() хостинга.
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

// honeypot — если заполнено, это бот
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
$breed = clean($_POST['Размер собаки'] ?? '');
$pack  = clean($_POST['Фасовка'] ?? '');
$qty   = clean($_POST['Количество упаковок'] ?? '');
$total = clean($_POST['Итоговая сумма'] ?? '');

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
$body .= "Фасовка: {$pack}\n";
$body .= "Количество упаковок: {$qty}\n";
$body .= "Итоговая сумма: {$total}\n";
$body .= "\nВремя заявки: " . date('d.m.Y H:i:s') . "\n";

$to   = 'a320b@yandex.ru';
$from = 'korm@normaplus.ru';

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

$headers  = "From: =?UTF-8?B?" . base64_encode('НОРМАПЛЮС') . "?= <{$from}>\r\n";
$headers .= "Reply-To: {$from}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "MIME-Version: 1.0\r\n";

$sent = @mail($to, $encodedSubject, $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'mail_failed']);
}
