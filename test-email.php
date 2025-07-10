<?php
/**
 * Teste do Sistema de Email
 * Arquivo: test-email.php
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'core/Email.php';

echo "<h1>Teste do Sistema de Email - Sigilosas VIP</h1>";

// Teste 1: Verificar se a função mail() está disponível
echo "<h2>1. Verificação da função mail()</h2>";
if (function_exists('mail')) {
    echo "✅ Função mail() está disponível<br>";
} else {
    echo "❌ Função mail() não está disponível<br>";
}

// Teste 2: Verificar configurações do PHP
echo "<h2>2. Configurações do PHP</h2>";
echo "sendmail_path: " . ini_get('sendmail_path') . "<br>";
echo "SMTP: " . ini_get('SMTP') . "<br>";
echo "smtp_port: " . ini_get('smtp_port') . "<br>";

// Teste 3: Testar envio de email simples
echo "<h2>3. Teste de envio de email</h2>";
$testEmail = 'teste@localhost.com'; // Email de teste local

try {
    $result = Email::enviar($testEmail, 'Teste de Email - Sigilosas VIP', 
        'Este é um email de teste para verificar se o sistema está funcionando.');
    
    if ($result) {
        echo "✅ Email enviado com sucesso!<br>";
        echo "Verifique se o email chegou em: $testEmail<br>";
    } else {
        echo "❌ Falha ao enviar email<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

// Teste 4: Testar recuperação de senha
echo "<h2>4. Teste de recuperação de senha</h2>";
try {
    $result = Email::enviarRecuperacaoSenha($testEmail, 'Usuário Teste', 'token_teste_123');
    
    if ($result) {
        echo "✅ Email de recuperação enviado com sucesso!<br>";
    } else {
        echo "❌ Falha ao enviar email de recuperação<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

// Teste 5: Verificar validação de email
echo "<h2>5. Teste de validação de email</h2>";
$emails = [
    'teste@exemplo.com',
    'email_invalido',
    'teste@',
    '@exemplo.com'
];

foreach ($emails as $email) {
    $valido = Email::validarEmail($email);
    echo ($valido ? "✅" : "❌") . " $email<br>";
}

// Teste 6: Verificar configurações do site
echo "<h2>6. Configurações do site</h2>";
echo "SITE_URL: " . SITE_URL . "<br>";
echo "SITE_EMAIL: " . SITE_EMAIL . "<br>";
echo "SMTP_HOST: " . SMTP_HOST . "<br>";

echo "<hr>";
echo "<h3>Instruções para configurar email local:</h3>";
echo "<ol>";
echo "<li>Instale um servidor SMTP local como Mercury ou configure o XAMPP para usar SMTP</li>";
echo "<li>Configure o arquivo php.ini com as configurações SMTP corretas</li>";
echo "<li>Ou use um serviço de email como Gmail, Outlook, etc.</li>";
echo "</ol>";

echo "<h3>Para testar com Gmail:</h3>";
echo "<ol>";
echo "<li>Ative a verificação em duas etapas na sua conta Google</li>";
echo "<li>Gere uma senha de app</li>";
echo "<li>Configure no arquivo config.php:</li>";
echo "<pre>";
echo "define('SMTP_HOST', 'smtp.gmail.com');\n";
echo "define('SMTP_PORT', 587);\n";
echo "define('SMTP_USERNAME', 'seu_email@gmail.com');\n";
echo "define('SMTP_PASSWORD', 'sua_senha_de_app');\n";
echo "</pre>";
echo "</ol>";
?> 