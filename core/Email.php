<?php
// Incluir PHPMailer manualmente
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email {
    /**
     * Envia email de recuperação de senha
     */
    public static function enviarRecuperacaoSenha($email, $nome, $token) {
        $assunto = "Recuperação de Senha - Sigilosas VIP";
        $link = SITE_URL . '/pages/redefinir-senha.php?token=' . $token;
        
        $mensagem = "<!DOCTYPE html>
<html lang=\"pt-br\">
<head>
    <meta charset=\"UTF-8\">
    <title>Recuperação de Senha - Sigilosas VIP</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #eee; padding: 32px; }
        .header { background: #3D263F; color: #F3EAC2; padding: 24px 0; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 24px 0; }
        .button { display: inline-block; padding: 14px 32px; background: #3D263F; color: #F3EAC2; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 24px 0; }
        .footer { font-size: 12px; color: #888; text-align: center; margin-top: 32px; }
    </style>
</head>
<body>
    <div class=\"container\">
        <div class=\"header\">
            <h1>Sigilosas VIP</h1>
            <p>Recuperação de Senha</p>
        </div>
        <div class=\"content\">
            <p>Olá, <strong>" . htmlspecialchars($nome) . "</strong>!</p>
            <p>Recebemos uma solicitação para redefinir sua senha.</p>
            <p>Clique no botão abaixo para criar uma nova senha. Este link expira em 1 hora:</p>
            <p style=\"text-align: center;\">
                <a href=\"$link\" class=\"button\">Redefinir Senha</a>
            </p>
            <p>Se o botão não funcionar, copie e cole este link no seu navegador:</p>
            <p style=\"word-break: break-all; color: #555;\">$link</p>
            <p>Se você não solicitou esta recuperação, ignore este e-mail.</p>
        </div>
        <div class=\"footer\">
            <p>Este é um e-mail automático, não responda a esta mensagem.</p>
            <p>&copy; " . date('Y') . " Sigilosas VIP. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>";
        
        return self::enviarSMTP($email, $assunto, $mensagem);
    }

    /**
     * Envia email usando PHPMailer via SMTP Hostinger
     */
    public static function enviarSMTP($para, $assunto, $mensagem) {
        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor SMTP Hostinger
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'recuperacao@sigilosasvip.com.br';
            $mail->Password = 'F3Uuj5=W'; // Troque pela senha real
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';

            // Remetente
            $mail->setFrom('recuperacao@sigilosasvip.com.br', 'Sigilosas VIP');
            $mail->addReplyTo('recuperacao@sigilosasvip.com.br', 'Sigilosas VIP');

            // Destinatário
            $mail->addAddress($para);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $mensagem;
            $mail->AltBody = strip_tags($mensagem);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Valida formato de email
     */
    public static function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Limpa tokens expirados
     */
    public static function limparTokensExpirados() {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM recuperacao_senha WHERE expira < NOW()");
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
} 