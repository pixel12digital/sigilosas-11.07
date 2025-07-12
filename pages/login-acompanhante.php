<?php
require_once __DIR__ . '/../config/config.php';
echo '<!-- SITE_URL: ' . (defined('SITE_URL') ? SITE_URL : 'NÃO DEFINIDO') . ' -->';
/**
 * Login de Acompanhante
 * Arquivo: pages/login-acompanhante.php
 */
/**
 * Login de Acompanhante
 * Arquivo: pages/login-acompanhante.php
 */

require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Processar formulário ANTES de qualquer saída HTML
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Validações
    if (empty($email) || empty($senha)) {
        $error = 'Email e senha são obrigatórios';
    } else {
        // Buscar acompanhante
        $acompanhante = $db->fetch("
            SELECT id, nome, email, senha, status, verificado 
            FROM acompanhantes 
            WHERE email = ?
        ", [$email]);

        if ($acompanhante && password_verify($senha, $acompanhante['senha'])) {
            // Verificar se a conta não está bloqueada
            if ($acompanhante['status'] === 'bloqueado') {
                $error = 'Sua conta foi bloqueada. Entre em contato conosco.';
            } else {
                // Login bem-sucedido (sessão já iniciada)
                error_log("Login acompanhante - Login bem-sucedido para: " . $acompanhante['email']);
                $_SESSION['acompanhante_id'] = $acompanhante['id'];
                $_SESSION['acompanhante_nome'] = $acompanhante['nome'];
                $_SESSION['acompanhante_email'] = $acompanhante['email'];
                $_SESSION['acompanhante_status'] = $acompanhante['status'];
                $_SESSION['acompanhante_verificado'] = $acompanhante['verificado'];
                $_SESSION['acompanhante_aprovada'] = ($acompanhante['status'] === 'aprovado') ? 1 : 0;

                            // Atualizar último login
                $db->update('acompanhantes', 
                    ['ultimo_login' => date('Y-m-d H:i:s')], 
                    'id = ?', 
                    [$acompanhante['id']]
                );

                // Definir mensagem de sucesso
                $_SESSION['success'] = 'Login realizado com sucesso! Bem-vinda ao seu painel.';

                // Debug: verificar variáveis de sessão
                error_log("Login acompanhante - ID: " . $_SESSION['acompanhante_id']);
                error_log("Login acompanhante - Nome: " . $_SESSION['acompanhante_nome']);
                error_log("Login acompanhante - Status: " . $_SESSION['acompanhante_status']);
                error_log("Login acompanhante - Aprovada: " . $_SESSION['acompanhante_aprovada']);

                // Debug: verificar se o redirecionamento está sendo executado
                error_log("Login acompanhante - Tentando redirecionar para: " . SITE_URL . "/acompanhante/perfil.php");

                // Redirecionar para painel
                header('Location: ' . SITE_URL . '/acompanhante/perfil.php');
                exit;
                // Fallback visual caso header não funcione
                echo '<script>window.location.href="' . SITE_URL . '/acompanhante/perfil.php";</script>';
                exit;
            }
        } else {
            $error = 'Email ou senha incorretos';
        }
    }
}

// Definir variáveis da página ANTES de incluir o header
$pageTitle = 'Login de Acompanhante - Sigilosas';
$pageDescription = 'Acesse seu painel de acompanhante.';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset(
        $pageTitle) ? $pageTitle : 'Login de Acompanhante'; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <script src="<?php echo SITE_URL; ?>/assets/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<!-- Header da Página -->
<section class="page-header py-5" style="background: #3D263F; color: #F3EAC2;">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-5 fw-bold mb-3">Login de Acompanhante</h1>
                <p class="lead">Acesse seu painel e gerencie seu perfil</p>
            </div>
        </div>
    </div>
</section>

<!-- Formulário de Login -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header" style="background: #3D263F; color: #F3EAC2;">
                        <h4 class="mb-0">
                            <i class="fas fa-sign-in-alt"></i> Acessar Painel
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       required>
                                <div class="form-text">Email usado no cadastro</div>
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" 
                                       required>
                                <div class="form-text">
                                    <a href="#" class="text-decoration-none" onclick="alert('Entre em contato conosco para recuperar sua senha.')">
                                        Esqueceu sua senha?
                                    </a>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Entrar
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-0">
                                Ainda não tem cadastro? 
                                <a href="cadastro-acompanhante.php" class="text-decoration-none">Cadastre-se aqui</a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle text-info"></i> Informações
                        </h5>
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-success"></i> Acesso seguro ao seu perfil</li>
                            <li><i class="fas fa-check text-success"></i> Gerencie suas fotos e informações</li>
                            <li><i class="fas fa-check text-success"></i> Acompanhe o status de aprovação</li>
                            <li><i class="fas fa-check text-success"></i> Receba notificações de propostas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Foco automático no primeiro campo
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email').focus();
});

// Validação de email
document.getElementById('email').addEventListener('blur', function(e) {
    const email = e.target.value;
    if (email && !email.includes('@')) {
        e.target.setCustomValidity('Digite um email válido');
    } else {
        e.target.setCustomValidity('');
    }
});
</script> 