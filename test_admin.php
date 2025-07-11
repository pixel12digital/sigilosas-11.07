<?php
require_once 'config/database.php';

echo "=== Teste de Admin ===\n";

$db = getDB();

// Verificar se há admins
$admin = $db->fetch('SELECT * FROM admin WHERE ativo = 1 LIMIT 1');

if ($admin) {
    echo "Admin encontrado:\n";
    echo "ID: " . $admin['id'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Nome: " . $admin['nome'] . "\n";
    echo "Nível: " . $admin['nivel'] . "\n";
    echo "Ativo: " . $admin['ativo'] . "\n";
} else {
    echo "Nenhum admin ativo encontrado!\n";
    
    // Criar um admin padrão
    echo "Criando admin padrão...\n";
    
    $senha_hash = password_hash('admin123', PASSWORD_BCRYPT);
    
    $admin_data = [
        'email' => 'admin@sigilosas.com',
        'senha_hash' => $senha_hash,
        'nome' => 'Administrador',
        'nivel' => 'admin',
        'ativo' => 1
    ];
    
    $id = $db->insert('admin', $admin_data);
    
    if ($id) {
        echo "Admin criado com sucesso! ID: $id\n";
        echo "Email: admin@sigilosas.com\n";
        echo "Senha: admin123\n";
    } else {
        echo "Erro ao criar admin!\n";
    }
}

echo "\n=== Teste de Sessão ===\n";

// Testar sessão
session_name('sigilosas_admin_session');
session_start();

echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Status: " . session_status() . "\n";

if (isset($_SESSION['user_id'])) {
    echo "Usuário logado: " . $_SESSION['user_id'] . "\n";
} else {
    echo "Nenhum usuário logado\n";
}

echo "\n=== Fim do Teste ===\n";
?> 