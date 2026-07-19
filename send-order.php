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

$to      = 'a320b@yandex.ru'; // ВРЕМЕННО для диагностики, вернуть на korm@normaplus.ru после теста
$subject = '=?UTF-8?B?' . base64_encode('Новая заявка с сайта НОРМАПЛЮС') . '?=';

$body  = "Новая заявка с сайта normaplus.ru\n\n";
$body .= "ФИО: {$name}\n";
$body .= "Телефон: {$phone}\n";
$body .= "Размер собаки: {$breed}\n";
$body .= "Фасовка: {$pack}\n";
$body .= "Количество упаковок: {$qty}\n";
$body .= "Итоговая сумма: {$total}\n";
$body .= "\nВремя заявки: " . date('d.m.Y H:i:s') . "\n";

// хостинг форсирует конверт-отправителя своим ящиком (см. sendmail_path в php.ini):
// используем тот же адрес и в заголовке From, чтобы не было расхождения
// конверта и заголовка (частая причина попадания в спам/отклонения)
$from = 'webmaster@weokday.ru';

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
