<?php
require_once '../config/config.php';
require_once '../config/database.php';
include_once __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha - Sigilosas VIP</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h1 class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-lock me-2"></i> Redefinir Senha</h1>
                        <?php if ($token_valido): ?>
                            <p class="lead text-muted">Digite sua nova senha</p>
                        <?php else: ?>
                            <p class="lead text-muted">Link de recuperação</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?php echo $tipo_mensagem; ?> text-center">
                            <?php echo $mensagem; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!$token_valido && $mensagem && $tipo_mensagem === 'sucesso'): ?>
                        <div class="alert alert-success text-center mb-4">
                            <?php echo $mensagem; ?>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="login-acompanhante.php" class="link-secondary"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
                            <a href="recuperar-senha.php" class="link-secondary"><i class="fas fa-key"></i> Solicitar Novo Link</a>
                        </div>
                    <?php endif; ?>
                    <?php if ($token_valido): ?>
                        <form method="POST" class="mb-3">
                            <div class="mb-3">
                                <label for="senha" class="form-label"><i class="fas fa-lock"></i> Nova Senha</label>
                                <input type="password" id="senha" name="senha" class="form-control form-control-lg" required placeholder="Digite sua nova senha" minlength="6">
                                <small class="form-text">Mínimo 6 caracteres</small>
                            </div>
                            <div class="mb-3">
                                <label for="confirmar_senha" class="form-label"><i class="fas fa-lock"></i> Confirmar Nova Senha</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control form-control-lg" required placeholder="Confirme sua nova senha" minlength="6">
                            </div>
                            <div class="d-grid mb-2">
                                <button type="submit" class="btn btn-primary btn-lg" style="background:#3D263F; border-color:#3D263F;">
                                    <i class="fas fa-save"></i> Alterar Senha
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between">
                        <a href="login-acompanhante.php" class="link-secondary"><i class="fas fa-sign-in-alt"></i> Fazer Login</a>
                        <a href="recuperar-senha.php" class="link-secondary"><i class="fas fa-key"></i> Solicitar Novo Link</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html> 