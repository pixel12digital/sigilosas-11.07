<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Mude para 1 se usar HTTPS
    session_name('sigilosas_acompanhante_session');
    session_start();
}

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../core/Auth.php';

// Instanciar classes
$db = getDB();
$auth = new Auth();

// Verificar se a acompanhante está logada
if (!isset($_SESSION['acompanhante_id'])) {
    header('Location: ../pages/login-acompanhante.php');
    exit;
}

// Verificar se a conta está aprovada (permitir acesso mesmo se pendente)
if (!isset($_SESSION['acompanhante_aprovada'])) {
    header('Location: ../pages/login-acompanhante.php?error=sessao_invalida');
    exit;
}

// Se a conta está bloqueada, não permitir acesso
if (isset($_SESSION['acompanhante_status']) && $_SESSION['acompanhante_status'] === 'bloqueado') {
    header('Location: ../pages/login-acompanhante.php?error=conta_bloqueada');
    exit;
}

// Carregar dados da acompanhante para completar informações da sessão
$acompanhante_data = $db->fetch("
    SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON c.estado_id = e.id
    WHERE a.id = ?
", [$_SESSION['acompanhante_id']]);

// Completar variáveis de sessão se necessário
if ($acompanhante_data) {
    $_SESSION['acompanhante_cidade'] = $acompanhante_data['cidade_nome'] ?? '';
    $_SESSION['acompanhante_estado'] = $acompanhante_data['estado_uf'] ?? '';
    $_SESSION['acompanhante_foto'] = $acompanhante_data['foto_principal'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Painel da Acompanhante - Sigilosas VIP</title>
    <meta name="description" content="Painel administrativo da acompanhante - Sigilosas VIP">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/img/favicon.ico">
    
    <!-- CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
                <!-- Mensagens de alerta -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['warning'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['warning']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['warning']); ?>
                <?php endif; ?>

                <!-- Loading Spinner -->
                <div id="loading" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.5); z-index: 9999;">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 