<?php
/**
 * ВРЕМЕННЫЙ диагностический файл.
 * Открой в браузере: https://твой-сайт/mail-test.php
 * Покажет, отправила ли функция mail() письмо и с какой ошибкой (если была).
 *
 * ВАЖНО: удали этот файл с сервера после проверки — он не должен
 * оставаться на сайте постоянно.
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Диагностика почты</h2>";

echo "<p><b>PHP версия:</b> " . phpversion() . "</p>";

$mailFuncExists = function_exists('mail');
echo "<p><b>Функция mail() доступна:</b> " . ($mailFuncExists ? 'да' : 'НЕТ — хостинг отключил mail()') . "</p>";

$sendmailPath = ini_get('sendmail_path');
echo "<p><b>sendmail_path:</b> " . htmlspecialchars($sendmailPath ?: '(не задан)') . "</p>";

$smtpHost = ini_get('SMTP');
echo "<p><b>SMTP (php.ini):</b> " . htmlspecialchars($smtpHost ?: '(не задан)') . "</p>";

if ($mailFuncExists) {
    $to = 'korm@normaplus.ru';
    $subject = 'Тест почты с сайта normaplus.ru';
    $body = 'Если вы видите это письмо — mail() на хостинге работает и письмо доходит. Время отправки: ' . date('d.m.Y H:i:s');

    $fromDomain = $_SERVER['HTTP_HOST'] ?? 'normaplus.ru';
    $fromDomain = preg_replace('/^www\./', '', $fromDomain);
    $from = 'noreply@' . $fromDomain;

    $headers  = "From: НОРМАПЛЮС Тест <{$from}>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // включаем отображение ошибок mail() через error tracking
    $errorBefore = error_get_last();
    $result = @mail($to, $subject, $body, $headers);
    $errorAfter = error_get_last();

    echo "<p><b>Отправитель (From):</b> " . htmlspecialchars($from) . "</p>";
    echo "<p><b>Получатель (To):</b> " . htmlspecialchars($to) . "</p>";
    echo "<p><b>Результат mail():</b> " . ($result ? 'TRUE (PHP считает, что отправлено)' : 'FALSE (PHP сообщает об ошибке отправки)') . "</p>";

    if ($errorAfter && $errorAfter !== $errorBefore) {
        echo "<p><b>Последняя PHP-ошибка:</b> " . htmlspecialchars($errorAfter['message']) . "</p>";
    }

    echo "<hr><p><b>Важно понимать:</b> результат TRUE означает только, что почтовый сервер хостинга <i>принял</i> письмо на отправку — а не что оно гарантированно дошло до ящика. Проверьте:</p>";
    echo "<ul>";
    echo "<li>Папку «Спам» в korm@normaplus.ru</li>";
    echo "<li>Реально ли ящик korm@normaplus.ru находится на этом же хостинге (Eurobyte), а не на стороннем сервисе (Яндекс.Почта, Mail.ru для домена и т.п.) — если ящик внешний, mail() с хостинга может не долетать или помечаться как подозрительный</li>";
    echo "<li>Настроены ли для домена normaplus.ru записи SPF/DKIM — без них многие почтовые системы отклоняют или прячут в спам письма от noreply@normaplus.ru</li>";
    echo "</ul>";
} else {
    echo "<p style='color:red'>Хостинг отключил функцию mail() для этого тарифа. Нужно использовать SMTP (например, через PHPMailer) с реальными данными почтового сервера — логин/пароль ящика или SMTP-релея.</p>";
}
