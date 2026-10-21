<?php

declare(strict_types=1);

namespace App\Services;

use Config\MailConfig;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $config = MailConfig::get();

        // Se credenciais não estiverem preenchidas, gravar e-mail em log para ambiente local
        if (empty($config['username']) || empty($config['password'])) {
            $logDir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }
            $logContent = sprintf(
                "[%s] SIMULAÇÃO DE E-MAIL (SMTP não configurado)\nPara: %s <%s>\nAssunto: %s\nCorpo:\n%s\n----------------------------------------\n",
                date('Y-m-d H:i:s'),
                $toName,
                $toEmail,
                $subject,
                strip_tags($htmlBody)
            );
            file_put_contents($logDir . '/mail.log', $logContent, FILE_APPEND);
            return true;
        }

        require_once __DIR__ . '/PHPMailer/Exception.php';
        require_once __DIR__ . '/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $config['port'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($config['from_address'], $config['from_name']);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = !empty($textBody) ? $textBody : strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro PHPMailer: " . $mail->ErrorInfo);
            return false;
        }
    }
}
