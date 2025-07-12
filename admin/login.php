<?php
/**
 * Login do Painel Administrativo - Versão Simplificada
 * Arquivo: admin/login.php
 */

// Iniciar sessão específica para admin
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$error = '';
$success = '';

// Se já está logado como admin, redirecionar para dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header('Location: dashboard.php');
    exit;
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email e senha são obrigatórios';
    } else {
        // Buscar admin no banco
        $admin = $db->fetch(
            "SELECT * FROM admin WHERE email = ? AND ativo = 1",
            [$email]
        );
        
        if ($admin && password_verify($password, $admin['senha_hash'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_email'] = $admin['email'];
            $_SESSION['user_nome'] = $admin['nome'];
            $_SESSION['user_nivel'] = $admin['nivel'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Redirecionar para dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email ou senha incorretos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <link href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3D263F;
            --secondary-color: #F3EAC2;
        }
        body {
            background: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(61,38,63,0.2);
            overflow: hidden;
            max-width: 350px;
            width: 100%;
        }
        .login-header {
            background: var(--primary-color);
            color: var(--secondary-color);
            padding: 1.2rem 1rem 1rem 1rem;
            text-align: center;
        }
        .login-body {
            padding: 1.2rem 1rem 1.5rem 1rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(61,38,63,0.25);
        }
        .btn-login {
            background: var(--primary-color);
            color: var(--secondary-color);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(61,38,63,0.3);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .text-muted {
            color: var(--primary-color) !important;
        }
        .bg-light {
            background-color: rgba(243,234,194,0.3) !important;
        }
        @media (max-width: 500px) {
            .login-card {
                max-width: 98vw;
            }
            .login-header, .login-body {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h2><i class="fas fa-user-shield"></i></h2>
            <h4>Painel Administrativo</h4>
            <p class="mb-0">Faça login para continuar</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Senha
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/" class="text-muted">
                    <i class="fas fa-arrow-left"></i> Voltar ao site
                </a>
            </div>
            
        </div>
    </div>

    <script src="<?php echo SITE_URL; ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 