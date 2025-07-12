<?php
/**
 * Logout Simples
 * Arquivo: api/logout-simple.php
 */

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Se desejar destruir a sessão completamente, apague também o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Redirecionar para a página inicial
require_once __DIR__ . '/../config/config.php';
header('Location: ' . SITE_URL . '/?success=Logout realizado com sucesso!');
exit;
?> 