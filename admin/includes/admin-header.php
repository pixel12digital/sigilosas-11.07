<?php
require_once __DIR__ . '/../../config/config.php';
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mude para 1 se usar HTTPS
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}
// Header do painel admin centralizado em relação à tela
?>
<header class="admin-header">
    <h4>
        <i class="fas fa-shield-alt"></i> Administração
    </h4>
    <div class="header-col-right">
        <a href="acompanhantes.php?novo=1" class="btn btn-primary" style="margin-right: 12px;">
            <i class="fas fa-user-plus"></i> Novo Acompanhante
        </a>
        <a href="../index.php" class="btn btn-outline-primary" target="_blank">
            <i class="fas fa-globe"></i> Ver Site
        </a>
    </div>
</header>
<?php 