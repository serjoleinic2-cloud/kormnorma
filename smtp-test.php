<?php
/**
 * ВРЕМЕННЫЙ диагностический файл для проверки SMTP.
 * Открой в браузере: https://твой-сайт/smtp-test.php
 * Требует, чтобы mail-config.php уже был создан на сервере (см.
 * mail-config.example.php).
 *
 * ВАЖНО: удали этот файл с сервера после проверки.
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Диагностика SMTP</h2>";

$configPath = __DIR__ . '/mail-config.php';

if (!file_exists($configPath)) {
    echo "<p style='color:red'>Файл mail-config.php не найден рядом с этим скриптом. Скопируй mail-config.example.php в mail-config.php и впиши реальные данные.</p>";
    exit;
}

require_once __DIR__ . '/smtp-mailer.php';
$cfg = require $configPath;

echo "<p><b>SMTP-сервер:</b> " . htmlspecialchars($cfg['smtp_host']) . ':' . htmlspecialchars((string)$cfg['smtp_port']) . "</p>";
echo "<p><b>Шифрование:</b> " . htmlspecialchars($cfg['smtp_secure']) . "</p>";
echo "<p><b>Логин:</b> " . htmlspecialchars($cfg['smtp_user']) . "</p>";
echo "<p><b>Получатель:</b> " . htmlspecialchars($cfg['to_email']) . "</p>";

$result = smtp_send_mail(
    $cfg,
    $cfg['to_email'],
    'Тест SMTP с сайта normaplus.ru',
    'Если вы видите это письмо — SMTP настроен верно. Время: ' . date('d.m.Y H:i:s')
);

echo "<hr>";
if ($result['ok']) {
    echo "<p style='color:green;font-weight:bold'>Успех! Письмо принято почтовым сервером через авторизованный SMTP.</p>";
} else {
    echo "<p style='color:red;font-weight:bold'>Ошибка отправки:</p>";
    echo "<pre>" . htmlspecialchars($result['error']) . "</pre>";
    echo "<p>Частые причины:</p><ul>";
    echo "<li>Неверный пароль — если в Яндекс-аккаунте включена двухфакторная аутентификация, нужен пароль приложения (Яндекс ID → Пароли и авторизация → Пароли приложений), а не обычный пароль</li>";
    echo "<li>Хостинг блокирует исходящие соединения на порт 465/587 (файрвол) — нужно уточнить у Eurobyte, разрешены ли исходящие SMTP-соединения на внешние сервера</li>";
    echo "<li>Неверно указан smtp_host/smtp_port/smtp_secure в mail-config.php</li>";
    echo "</ul>";
}
