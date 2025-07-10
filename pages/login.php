<?php
// Iniciar sessão e carregar dependências ANTES de qualquer saída
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Se já está logado como admin, redirecionar para admin
if (
    isset($_SESSION['admin_id']) &&
    isset($_SESSION['user_id']) &&
    isset($_SESSION['logged_in']) && $_SESSION['logged_in']
) {
    header('Location: /Sigilosas-MySQL/admin/');
    exit;
}
// Se já está logado como acompanhante, redirecionar para painel acompanhante
if (isset($_SESSION['acompanhante_id'])) {
    header('Location: /Sigilosas-MySQL/acompanhante/');
    exit;
}

// Processar login ANTES de qualquer saída HTML
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email)) {
        $errors[] = "Email é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }
    
    if (empty($senha)) {
        $errors[] = "Senha é obrigatória";
    }
    
    if (empty($errors)) {
        try {
            $pdo = $db->getConnection();
            // 1. Tentar admin
            $stmt = $pdo->prepare("SELECT id, nome, email, senha_hash FROM admin WHERE email = ? AND ativo = 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($admin && password_verify($senha, $admin['senha_hash'])) {
                // Login admin
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nome'] = $admin['nome'];
                $_SESSION['admin_email'] = $admin['email'];
                // Variáveis esperadas pelo painel admin
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_email'] = $admin['email'];
                $_SESSION['user_nome'] = $admin['nome'];
                $_SESSION['user_nivel'] = 'admin';
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['success'] = "Login realizado com sucesso!";
                header('Location: /Sigilosas-MySQL/admin/');
                exit;
            }
            // 2. Tentar acompanhante
            $stmt = $pdo->prepare("SELECT id, nome, email, senha, status, verificado FROM acompanhantes WHERE email = ?");
            $stmt->execute([$email]);
            $acompanhante = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($acompanhante && password_verify($senha, $acompanhante['senha'])) {
                if ($acompanhante['status'] === 'bloqueado') {
                    $errors[] = 'Sua conta foi bloqueada. Entre em contato conosco.';
                } else {
                    $_SESSION['acompanhante_id'] = $acompanhante['id'];
                    $_SESSION['acompanhante_nome'] = $acompanhante['nome'];
                    $_SESSION['acompanhante_email'] = $acompanhante['email'];
                    $_SESSION['acompanhante_status'] = $acompanhante['status'];
                    $_SESSION['acompanhante_verificado'] = $acompanhante['verificado'];
                    $_SESSION['acompanhante_aprovada'] = ($acompanhante['status'] === 'aprovado') ? 1 : 0;
                    $_SESSION['success'] = 'Login realizado com sucesso! Bem-vinda ao seu painel.';
                    // Atualizar último login
                    $stmt = $pdo->prepare("UPDATE acompanhantes SET ultimo_login = ? WHERE id = ?");
                    $stmt->execute([date('Y-m-d H:i:s'), $acompanhante['id']]);
                    header('Location: /Sigilosas-MySQL/acompanhante/');
                    exit;
                }
            }
            // 3. Se não encontrou
            $errors[] = "Email ou senha incorretos";
        } catch (Exception $e) {
            $errors[] = "Erro interno: " . $e->getMessage();
        }
    }
}

// Definir variáveis da página ANTES de incluir o header
$page_title = "Login";
$page_description = "Faça login como acompanhante ou administrador.";

// AGORA incluir o header (depois de todo o processamento)
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </h4>
                    <p class="mb-0 small">Acesse seu painel de acompanhante ou admin</p>
                </div>
                
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                   required>
                        </div>
                        
                        <!-- Senha -->
                        <div class="mb-3">
                            <label for="senha" class="form-label">
                                <i class="fas fa-lock"></i> Senha
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="senha" 
                                       name="senha" 
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('senha')">
                                    <i class="fas fa-eye" id="senha-icon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-end mb-3">
                            <a href="/Sigilosas-MySQL/pages/recuperar-senha.php" class="link-secondary">
                                Esqueceu a senha?
                            </a>
                        </div>
                        
                        <!-- Botão de login -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Entrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Informações de segurança -->
            <div class="card mt-4 border-info">
                <div class="card-body text-center">
                    <i class="fas fa-shield-alt text-info fa-2x mb-2"></i>
                    <h6 class="card-title">Sua Segurança é Nossa Prioridade</h6>
                    <p class="card-text small text-muted">
                        Todos os dados são criptografados e protegidos. 
                        Nunca compartilhamos suas informações pessoais.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?> 
<?php include __DIR__ . '/../includes/footer.php'; ?> 
</script> 