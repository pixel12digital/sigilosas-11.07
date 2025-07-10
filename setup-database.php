<?php
/**
 * Script de Setup do Banco de Dados
 * Execute este arquivo para criar as tabelas necessárias
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Setup do Banco de Dados - Sigilosas VIP</h2>";

// Configurações do banco
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'sigilosas_vip';

try {
    // Conectar sem especificar banco
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Conexão com MySQL estabelecida</p>";
    
    // Criar banco de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✓ Banco de dados '$dbname' criado/verificado</p>";
    
    // Conectar ao banco específico
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ler e executar o arquivo SQL
    $sql = file_get_contents('setup-mysql.sql');
    
    // Remover a parte de criação do banco (já foi feita)
    $sql = preg_replace('/CREATE DATABASE.*?;/s', '', $sql);
    $sql = preg_replace('/USE.*?;/s', '', $sql);
    
    // Executar as queries
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $pdo->exec($query);
                echo "<p style='color: green;'>✓ Query executada com sucesso</p>";
            } catch (PDOException $e) {
                // Ignorar erros de tabela já existente
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "<p style='color: orange;'>⚠ " . $e->getMessage() . "</p>";
                }
            }
        }
    }
    
    echo "<h3>Verificação das Tabelas:</h3>";
    
    // Verificar se as tabelas foram criadas
    $tables = ['admin', 'estados', 'cidades', 'acompanhantes', 'fotos', 'documentos_acompanhante', 'videos_verificacao', 'admin_log'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Tabela '$table' existe</p>";
        } else {
            echo "<p style='color: red;'>✗ Tabela '$table' não existe</p>";
        }
    }
    
    // Verificar dados nas tabelas
    echo "<h3>Dados nas Tabelas:</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM estados");
    $estados = $stmt->fetch();
    echo "<p>Estados: " . $estados['total'] . "</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cidades");
    $cidades = $stmt->fetch();
    echo "<p>Cidades: " . $cidades['total'] . "</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM admin");
    $admin = $stmt->fetch();
    echo "<p>Administradores: " . $admin['total'] . "</p>";
    
    echo "<h3>Setup Concluído!</h3>";
    echo "<p>O banco de dados foi configurado com sucesso.</p>";
    echo "<p><a href='index.php'>Ir para o site</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Verifique se o MySQL está rodando no XAMPP.</p>";
}
?> 