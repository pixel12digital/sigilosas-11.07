<?php
/**
 * Teste do Painel Administrativo
 * Arquivo: admin/teste-admin.php
 */

// Iniciar sessão específica para admin
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}

require_once '../config/database.php';

echo "<h1>Teste do Painel Administrativo</h1>";
echo "<p><strong>Sessão ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Sessão Nome:</strong> " . session_name() . "</p>";
echo "<p><strong>Admin ID:</strong> " . ($_SESSION['user_id'] ?? 'NÃO DEFINIDO') . "</p>";
echo "<p><strong>Admin Nome:</strong> " . ($_SESSION['user_nome'] ?? 'NÃO DEFINIDO') . "</p>";
echo "<p><strong>Admin Email:</strong> " . ($_SESSION['user_email'] ?? 'NÃO DEFINIDO') . "</p>";
echo "<p><strong>Logado:</strong> " . ($_SESSION['logged_in'] ?? 'NÃO') . "</p>";
echo "<p><strong>Login Time:</strong> " . ($_SESSION['login_time'] ?? 'NÃO DEFINIDO') . "</p>";

echo "<hr>";

// Testar conexão com banco
try {
    $db = getDB();
    echo "<h2>Teste de Conexão com Banco</h2>";
    echo "<p><strong>Status:</strong> <span style='color: green;'>CONECTADO ✓</span></p>";
    
    // Testar query simples
    $result = $db->fetch("SELECT COUNT(*) as total FROM admin WHERE ativo = 1");
    echo "<p><strong>Total de Admins Ativos:</strong> " . $result['total'] . "</p>";
    
    // Listar admins
    $admins = $db->fetchAll("SELECT id, nome, email, ativo FROM admin");
    echo "<h3>Admins no Banco:</h3>";
    echo "<ul>";
    foreach ($admins as $admin) {
        echo "<li>ID: {$admin['id']} - Nome: {$admin['nome']} - Email: {$admin['email']} - Ativo: " . ($admin['ativo'] ? 'SIM' : 'NÃO') . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p><strong>Status:</strong> <span style='color: red;'>ERRO ✗</span></p>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

echo "<h2>Links de Teste</h2>";
echo "<p><a href='login.php'>Login Admin</a></p>";
echo "<p><a href='dashboard.php'>Dashboard</a></p>";
echo "<p><a href='logout.php'>Logout</a></p>";
echo "<p><a href='../index.php'>Site Principal</a></p>";

echo "<hr>";

echo "<h2>Informações do Sistema</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
?> 