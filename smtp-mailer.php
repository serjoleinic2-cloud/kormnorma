<?php
/**
 * Минимальный SMTP-клиент без внешних библиотек (composer не нужен).
 * Поддерживает AUTH LOGIN и SSL/STARTTLS — этого достаточно для Яндекс.Почты.
 */

function smtp_send_mail(array $cfg, string $toEmail, string $subject, string $body): array
{
    $host    = $cfg['smtp_host'];
    $port    = $cfg['smtp_port'];
    $secure  = $cfg['smtp_secure'] ?? 'ssl';
    $user    = $cfg['smtp_user'];
    $pass    = $cfg['smtp_pass'];
    $from    = $cfg['from_email'];
    $fromName = $cfg['from_name'] ?? '';

    $transport = $secure === 'ssl' ? 'ssl://' : '';
    $errno = 0; $errstr = '';

    $sock = @stream_socket_client(
        $transport . $host . ':' . $port,
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT
    );

    if (!$sock) {
        return ['ok' => false, 'error' => "connect_failed: {$errstr} ({$errno})"];
    }

    stream_set_timeout($sock, 15);

    $read = function () use ($sock) {
        $data = '';
        while ($line = fgets($sock, 515)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break; // конец многострочного ответа
        }
        return $data;
    };

    $write = function (string $cmd) use ($sock) {
        fwrite($sock, $cmd . "\r\n");
    };

    $expect = function (string $resp, string $codePrefix) {
        return strpos($resp, $codePrefix) === 0 || preg_match('/^' . preg_quote($codePrefix, '/') . '/m', $resp);
    };

    $banner = $read();

    $write('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $ehloResp = $read();

    if ($secure === 'tls') {
        $write('STARTTLS');
        $tlsResp = $read();
        if (!$expect($tlsResp, '220')) {
            fclose($sock);
            return ['ok' => false, 'error' => 'starttls_rejected: ' . $tlsResp];
        }
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($sock);
            return ['ok' => false, 'error' => 'tls_handshake_failed'];
        }
        $write('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $read();
    }

    $write('AUTH LOGIN');
    $authResp = $read();
    if (!$expect($authResp, '334')) {
        fclose($sock);
        return ['ok' => false, 'error' => 'auth_login_rejected: ' . $authResp];
    }

    $write(base64_encode($user));
    $userResp = $read();
    if (!$expect($userResp, '334')) {
        fclose($sock);
        return ['ok' => false, 'error' => 'auth_user_rejected: ' . $userResp];
    }

    $write(base64_encode($pass));
    $passResp = $read();
    if (!$expect($passResp, '235')) {
        fclose($sock);
        return ['ok' => false, 'error' => 'auth_failed: ' . $passResp];
    }

    $write('MAIL FROM:<' . $from . '>');
    $mailResp = $read();
    if (!$expect($mailResp, '250')) {
        fclose($sock);
        return ['ok' => false, 'error' => 'mail_from_rejected: ' . $mailResp];
    }

    $write('RCPT TO:<' . $toEmail . '>');
    $rcptResp = $read();
    if (!$expect($rcptResp, '250') && !$expect($rcptResp, '251')) {
        fclose($sock);
        return ['ok' => false, 'error' => 'rcpt_to_rejected: ' . $rcptResp];
    }

    $write('DATA');
    $dataResp = $read();
    if (!$expect($dataResp, '354')) {
        fclose($sock);
        return ['ok' => false, 'error' => 'data_rejected: ' . $dataResp];
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $fromHeader = $fromName !== '' ? "{$fromName} <{$from}>" : $from;

    $headers  = "From: {$fromHeader}\r\n";
    $headers .= "To: <{$toEmail}>\r\n";
    $headers .= "Subject: {$encodedSubject}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";

    // экранируем строки, начинающиеся с точки (SMTP dot-stuffing)
    $escapedBody = preg_replace('/^\./m', '..', $body);

    $write($headers . "\r\n" . $escapedBody . "\r\n.");
    $sendResp = $read();

    $write('QUIT');
    fclose($sock);

    if (!$expect($sendResp, '250')) {
        return ['ok' => false, 'error' => 'send_rejected: ' . $sendResp];
    }

    return ['ok' => true, 'error' => null];
}
