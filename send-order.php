<?php
/**
 * Принимает POST от формы заявки и отправляет письмо на korm@normaplus.ru
 * Работает через встроенный mail() хостинга — без сторонних сервисов.
 */

header('Content-Type: application/json; charset=utf-8');

// разрешаем только POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

// honeypot-поле — если заполнено, это бот
if (!empty($_POST['_honey'])) {
    echo json_encode(['ok' => true]); // молча "успех" для бота
    exit;
}

function clean($value) {
    $value = trim((string) $value);
    // защита от header injection в письме
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

$to      = 'korm@normaplus.ru';
$subject = '=?UTF-8?B?' . base64_encode('Новая заявка с сайта НОРМАПЛЮС') . '?=';

$body  = "Новая заявка с сайта normaplus.ru\n\n";
$body .= "ФИО: {$name}\n";
$body .= "Телефон: {$phone}\n";
$body .= "Размер собаки: {$breed}\n";
$body .= "Фасовка: {$pack}\n";
$body .= "Количество упаковок: {$qty}\n";
$body .= "Итоговая сумма: {$total}\n";
$body .= "\nВремя заявки: " . date('d.m.Y H:i:s') . "\n";

// письмо отправляется от имени того же домена, на который стоит сайт —
// это снижает риск, что письмо попадёт в спам у большинства почтовых серверов
$fromDomain = $_SERVER['HTTP_HOST'] ?? 'normaplus.ru';
$fromDomain = preg_replace('/^www\./', '', $fromDomain);
$from = 'noreply@' . $fromDomain;

$headers  = "From: НОРМАПЛЮС <{$from}>\r\n";
$headers .= "Reply-To: {$from}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "MIME-Version: 1.0\r\n";

$sent = @mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'mail_failed']);
}
