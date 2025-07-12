<?php
/**
 * Logout de Acompanhante
 * Arquivo: acompanhante/logout.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_acompanhante_session');
    session_start();
}

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se desejar destruir a sessão completamente, apague também o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir a sessão
session_destroy();

// Redirecionar para página de login
require_once __DIR__ . '/../config/config.php';
header('Location: ' . SITE_URL . '/pages/login-acompanhante.php?logout=1');
exit;
?> 