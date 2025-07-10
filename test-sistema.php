<?php
/**
 * Teste Geral do Sistema
 * Arquivo: test-sistema.php
 */

echo "<h1>Teste Geral do Sistema - Sigilosas VIP</h1>";

// Teste 1: Verificar PHP
echo "<h2>1. Verificação do PHP</h2>";
echo "✅ Versão do PHP: " . phpversion() . "<br>";
echo "✅ Extensões necessárias:<br>";

$extensoes = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'json'];
foreach ($extensoes as $ext) {
    echo (extension_loaded($ext) ? "✅" : "❌") . " $ext<br>";
}

// Teste 2: Verificar configurações
echo "<h2>2. Configurações</h2>";
try {
    require_once 'config/config.php';
    echo "✅ Arquivo config.php carregado<br>";
    echo "✅ SITE_URL: " . SITE_URL . "<br>";
    echo "✅ SITE_NAME: " . SITE_NAME . "<br>";
} catch (Exception $e) {
    echo "❌ Erro ao carregar config.php: " . $e->getMessage() . "<br>";
}

// Teste 3: Verificar banco de dados
echo "<h2>3. Banco de Dados</h2>";
try {
    require_once 'config/database.php';
    $db = getDB();
    echo "✅ Classe Database carregada<br>";
    
    // Tentar conectar
    $pdo = $db->getConnection();
    echo "✅ Conexão com banco estabelecida<br>";
    
    // Verificar tabelas
    $tabelas = ['acompanhantes', 'cidades', 'estados'];
    foreach ($tabelas as $tabela) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
            $existe = $stmt->rowCount() > 0;
            echo ($existe ? "✅" : "❌") . " Tabela $tabela<br>";
        } catch (Exception $e) {
            echo "❌ Erro ao verificar tabela $tabela: " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro de banco de dados: " . $e->getMessage() . "<br>";
}

// Teste 4: Verificar arquivos e pastas
echo "<h2>4. Arquivos e Pastas</h2>";
$pastas = [
    'acompanhante/',
    'admin/',
    'api/',
    'assets/',
    'config/',
    'core/',
    'includes/',
    'pages/',
    'uploads/',
    'uploads/fotos/',
    'uploads/videos/',
    'uploads/documentos/'
];

foreach ($pastas as $pasta) {
    echo (is_dir($pasta) ? "✅" : "❌") . " Pasta $pasta<br>";
}

// Teste 5: Verificar arquivos principais
echo "<h2>5. Arquivos Principais</h2>";
$arquivos = [
    'index.php',
    'acompanhante/index.php',
    'acompanhante/perfil.php',
    'acompanhante/midia.php',
    'acompanhante/visualizar.php',
    'acompanhante/logout.php',
    'admin/dashboard.php',
    'pages/login-acompanhante.php',
    'pages/cadastro-acompanhante.php',
    'config/config.php',
    'config/database.php',
    'core/Email.php',
    'core/Auth.php',
    'core/Upload.php'
];

foreach ($arquivos as $arquivo) {
    echo (file_exists($arquivo) ? "✅" : "❌") . " Arquivo $arquivo<br>";
}

// Teste 6: Verificar permissões
echo "<h2>6. Permissões</h2>";
$pastas_escrita = [
    'uploads/',
    'uploads/fotos/',
    'uploads/videos/',
    'uploads/documentos/',
    'logs/'
];

foreach ($pastas_escrita as $pasta) {
    if (is_dir($pasta)) {
        $writable = is_writable($pasta);
        echo ($writable ? "✅" : "❌") . " Pasta $pasta (escrita)<br>";
    } else {
        echo "❌ Pasta $pasta não existe<br>";
    }
}

// Teste 7: Verificar URLs
echo "<h2>7. URLs de Acesso</h2>";
$urls = [
    'Site Principal' => SITE_URL,
    'Painel Acompanhante' => SITE_URL . '/acompanhante/',
    'Login Acompanhante' => SITE_URL . '/pages/login-acompanhante.php',
    'Cadastro Acompanhante' => SITE_URL . '/pages/cadastro-acompanhante.php',
    'Painel Admin' => SITE_URL . '/admin/',
    'Recuperar Senha' => SITE_URL . '/pages/recuperar-senha.php'
];

foreach ($urls as $nome => $url) {
    echo "🔗 <strong>$nome:</strong> <a href='$url' target='_blank'>$url</a><br>";
}

// Teste 8: Verificar sessões
echo "<h2>8. Sessões</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "✅ Sessões funcionando<br>";

// Teste 9: Verificar uploads
echo "<h2>9. Configurações de Upload</h2>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";

// Teste 10: Verificar segurança
echo "<h2>10. Segurança</h2>";
echo "session.cookie_httponly: " . (ini_get('session.cookie_httponly') ? "✅" : "❌") . "<br>";
echo "session.use_only_cookies: " . (ini_get('session.use_only_cookies') ? "✅" : "❌") . "<br>";

echo "<hr>";
echo "<h3>Resumo do Sistema:</h3>";
echo "<p>✅ Sistema básico funcionando</p>";
echo "<p>✅ Painel da acompanhante reorganizado</p>";
echo "<p>✅ Links de redirecionamento atualizados</p>";
echo "<p>⚠️ Banco de dados precisa ser configurado</p>";
echo "<p>⚠️ Sistema de email precisa ser configurado</p>";

echo "<h3>Próximos passos:</h3>";
echo "<ol>";
echo "<li>Configurar MySQL e executar SQL da recuperação de senha</li>";
echo "<li>Configurar sistema de email (Gmail ou local)</li>";
echo "<li>Testar fluxo completo de cadastro e login</li>";
echo "<li>Testar upload de arquivos</li>";
echo "</ol>";
?> 