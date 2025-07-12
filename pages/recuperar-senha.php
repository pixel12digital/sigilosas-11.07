<?php
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
                $stmt = $pdo->prepare("INSERT INTO recuperacao_senha (usuario_id, token, expira) VALUES (?, ?, ?)");
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
            $mensagem = 'Erro interno. Tente novamente.';
            $tipo_mensagem = 'erro';
        }
    }
}

// Definir variáveis da página
$pageTitle = 'Recuperar Senha - Sigilosas VIP';
$pageDescription = 'Recupere sua senha de forma segura.';
?>

<main class="container">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-key"></i> Recuperar Senha</h1>
                <p>Digite seu email para receber o link de recuperação</p>
            </div>

            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="Digite seu email cadastrado"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Enviar Email de Recuperação
                </button>
            </form>

            <div class="auth-links">
                <a href="login.php" class="link-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar ao Login
                </a>
                <a href="cadastro.php" class="link-secondary">
                    <i class="fas fa-user-plus"></i> Criar Conta
                </a>
            </div>
        </div>
    </div>
</main> 