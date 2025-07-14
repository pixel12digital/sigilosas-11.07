<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../core/Email.php';

// Verificar se já está logado
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /acompanhante/');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Por favor, insira um email válido.';
        $tipo_mensagem = 'erro';
    } else {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            // Verificar se o email existe na tabela de acompanhantes
            $stmt = $pdo->prepare("SELECT id, nome, email FROM acompanhantes WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Gerar token único e seguro
                $token = bin2hex(random_bytes(32));
                $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Salvar token no banco (ajustar para acompanhante_id se necessário)
                $stmt = $pdo->prepare("INSERT INTO recuperacao_senha (acompanhante_id, token, expira) VALUES (?, ?, ?)");
                $stmt->execute([$usuario['id'], $token, $expira]);
                
                // Enviar email de recuperação
                if (Email::enviarRecuperacaoSenha($usuario['email'], $usuario['nome'], $token)) {
                    $mensagem = 'Email de recuperação enviado! Verifique sua caixa de entrada.';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = 'Erro ao enviar email. Tente novamente ou entre em contato.';
                    $tipo_mensagem = 'erro';
                }
                
            } else {
                $mensagem = 'Email não encontrado em nossa base de dados.';
                $tipo_mensagem = 'erro';
            }
            
        } catch (Exception $e) {
            $mensagem = 'Erro interno: ' . $e->getMessage();
            $tipo_mensagem = 'erro';
            error_log('Recuperação de senha - Erro: ' . $e->getMessage());
        }
    }
}

// Definir variáveis da página
$pageTitle = 'Recuperar Senha - Sigilosas VIP';
$pageDescription = 'Recupere sua senha de forma segura.';
?>

<?php include_once __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - Sigilosas VIP</title>
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
                        <h1 class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-key me-2"></i> Recuperar Senha</h1>
                        <p class="lead text-muted">Digite seu email para receber o link de recuperação</p>
                    </div>
                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?php echo $tipo_mensagem; ?> text-center">
                            <?php echo $mensagem; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" class="mb-3">
                        <div class="mb-3">
                            <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" name="email" class="form-control form-control-lg" required placeholder="Digite seu email cadastrado" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="d-grid mb-2">
                            <button type="submit" class="btn btn-primary btn-lg" style="background:#3D263F; border-color:#3D263F;">
                                <i class="fas fa-paper-plane"></i> Enviar Email de Recuperação
                            </button>
                        </div>
                    </form>
                    <div class="d-flex justify-content-between">
                        <a href="login-acompanhante.php" class="link-secondary"><i class="fas fa-arrow-left"></i> Voltar ao Login</a>
                        <a href="cadastro-acompanhante.php" class="link-secondary"><i class="fas fa-user-plus"></i> Criar Conta</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html> 