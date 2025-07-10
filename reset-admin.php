<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

$email = 'admin@sigilosas.com';
$senha = 'admin123';
$hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
$nome = 'Administrador';

try {
    $db = Database::getInstance()->getConnection();

    // Verifica se já existe admin com esse email
    $stmt = $db->prepare("SELECT id FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Atualiza senha e dados
        $stmt = $db->prepare("UPDATE admin SET senha_hash = ?, nome = ?, nivel = 'admin', ativo = 1 WHERE email = ?");
        $stmt->execute([$hash, $nome, $email]);
        echo "Senha do admin atualizada para admin123!";
    } else {
        // Cria novo admin
        $stmt = $db->prepare("INSERT INTO admin (nome, email, senha_hash, nivel, ativo) VALUES (?, ?, ?, 'admin', 1)");
        $stmt->execute([$nome, $email, $hash]);
        echo "Usuário admin criado com senha admin123!";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
} 