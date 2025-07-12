<?php
require_once '../config/config.php';
require_once '../config/database.php';

$mensagem = '';
$tipo_mensagem = '';
$token_valido = false;
$usuario_id = null;

// Verificar se já está logado
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /acompanhante/');
    exit;
}

// Verificar token
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

if (!$token) {
    $mensagem = 'Token de recuperação inválido.';
    $tipo_mensagem = 'erro';
} else {
    try {
        $pdo = Database::getConnection();
        
        // Verificar se o token existe e não expirou
        $stmt = $pdo->prepare("
            SELECT r.usuario_id, r.usado, r.expira, u.nome 
            FROM recuperacao_senha r 
            JOIN usuarios u ON r.usuario_id = u.id 
            WHERE r.token = ? AND r.usado = 0 AND r.expira > NOW()
        ");
        $stmt->execute([$token]);
        $recuperacao = $stmt->fetch();
        
        if ($recuperacao) {
            $token_valido = true;
            $usuario_id = $recuperacao['usuario_id'];
        } else {
            $mensagem = 'Token inválido ou expirado. Solicite um novo link de recuperação.';
            $tipo_mensagem = 'erro';
        }
        
    } catch (Exception $e) {
        $mensagem = 'Erro interno. Tente novamente.';
        $tipo_mensagem = 'erro';
    }
}

// Processar redefinição de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    // Validações
    if (strlen($senha) < 6) {
        $mensagem = 'A senha deve ter pelo menos 6 caracteres.';
        $tipo_mensagem = 'erro';
    } elseif ($senha !== $confirmar_senha) {
        $mensagem = 'As senhas não coincidem.';
        $tipo_mensagem = 'erro';
    } else {
        try {
            $pdo = Database::getConnection();
            
            // Atualizar senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt->execute([$senha_hash, $usuario_id]);
            
            // Marcar token como usado
            $stmt = $pdo->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            $mensagem = 'Senha alterada com sucesso! Você pode fazer login com sua nova senha.';
            $tipo_mensagem = 'sucesso';
            $token_valido = false; // Não mostrar formulário novamente
            
        } catch (Exception $e) {
            $mensagem = 'Erro ao alterar senha. Tente novamente.';
            $tipo_mensagem = 'erro';
        }
    }
}

// Definir variáveis da página
$pageTitle = 'Redefinir Senha - Sigilosas VIP';
$pageDescription = 'Altere sua senha de forma segura.';
?>

<main class="container">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-lock"></i> Redefinir Senha</h1>
                <?php if ($token_valido): ?>
                    <p>Digite sua nova senha</p>
                <?php else: ?>
                    <p>Link de recuperação</p>
                <?php endif; ?>
            </div>

            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <?php if ($token_valido): ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="senha">
                            <i class="fas fa-lock"></i> Nova Senha
                        </label>
                        <input 
                            type="password" 
                            id="senha" 
                            name="senha" 
                            required 
                            placeholder="Digite sua nova senha"
                            minlength="6"
                        >
                        <small class="form-text">Mínimo 6 caracteres</small>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">
                            <i class="fas fa-lock"></i> Confirmar Nova Senha
                        </label>
                        <input 
                            type="password" 
                            id="confirmar_senha" 
                            name="confirmar_senha" 
                            required 
                            placeholder="Confirme sua nova senha"
                            minlength="6"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Alterar Senha
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-links">
                <a href="login.php" class="link-secondary">
                    <i class="fas fa-sign-in-alt"></i> Fazer Login
                </a>
                <a href="recuperar-senha.php" class="link-secondary">
                    <i class="fas fa-key"></i> Solicitar Novo Link
                </a>
            </div>
        </div>
    </div>
</main> 