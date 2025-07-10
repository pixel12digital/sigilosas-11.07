<?php

class Email {
    
    /**
     * Envia email de recuperação de senha
     */
    public static function enviarRecuperacaoSenha($email, $nome, $token) {
        $assunto = "Recuperação de Senha - Sigilosas VIP";
        $link = SITE_URL . '/pages/redefinir-senha.php?token=' . $token;
        
        $mensagem = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .button { display: inline-block; padding: 12px 24px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Sigilosas VIP</h1>
                    <p>Recuperação de Senha</p>
                </div>
                
                <div class='content'>
                    <h2>Olá, {$nome}!</h2>
                    
                    <p>Recebemos uma solicitação para redefinir sua senha.</p>
                    
                    <p>Clique no botão abaixo para criar uma nova senha:</p>
                    
                    <p style='text-align: center;'>
                        <a href='{$link}' class='button'>Redefinir Senha</a>
                    </p>
                    
                    <p><strong>Este link expira em 1 hora.</strong></p>
                    
                    <p>Se você não solicitou esta recuperação, ignore este email.</p>
                    
                    <p>Se o botão não funcionar, copie e cole este link no seu navegador:</p>
                    <p style='word-break: break-all;'>{$link}</p>
                </div>
                
                <div class='footer'>
                    <p>Este é um email automático, não responda a esta mensagem.</p>
                    <p>&copy; " . date('Y') . " Sigilosas VIP. Todos os direitos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
        
        return self::enviar($email, $assunto, $mensagem);
    }
    
    /**
     * Envia email usando função mail() do PHP
     */
    private static function enviar($para, $assunto, $mensagem) {
        $headers = array(
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: Sigilosas VIP <noreply@' . $_SERVER['HTTP_HOST'] . '>',
            'Reply-To: noreply@' . $_SERVER['HTTP_HOST'],
            'X-Mailer: PHP/' . phpversion()
        );
        
        return mail($para, $assunto, $mensagem, implode("\r\n", $headers));
    }
    
    /**
     * Envia email usando SMTP (requer configuração adicional)
     */
    public static function enviarSMTP($para, $assunto, $mensagem) {
        // TODO: Implementar envio via SMTP usando PHPMailer ou similar
        // Por enquanto, usa a função mail() padrão
        return self::enviar($para, $assunto, $mensagem);
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