<?php
require_once '../config/config.php';
require_once '../core/Auth.php';

$auth = new Auth($pdo);

// Verificar se está logado
if (!$auth->isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

$user = null;
$userType = '';

// Buscar dados do usuário
if ($auth->isAdmin()) {
    $userType = 'admin';
    $stmt = $pdo->prepare("SELECT id, nome, email, foto FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} elseif (isset($_SESSION['acompanhante_id'])) {
    $userType = 'acompanhante';
    $stmt = $pdo->prepare("SELECT id, nome, email, foto, apelido, telefone FROM acompanhantes WHERE id = ?");
    $stmt->execute([$_SESSION['acompanhante_id']]);
    $user = $stmt->fetch();
}

if (!$user) {
    $_SESSION['error'] = 'Usuário não encontrado.';
    header('Location: index.php');
    exit;
}

// Processar formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'atualizar_perfil') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        
        if (empty($nome) || empty($email)) {
            $_SESSION['error'] = 'Nome e email são obrigatórios.';
        } else {
            try {
                if ($userType === 'admin') {
                    $stmt = $pdo->prepare("UPDATE admins SET nome = ?, email = ? WHERE id = ?");
                    $stmt->execute([$nome, $email, $user['id']]);
                } else {
                    $apelido = trim($_POST['apelido'] ?? '');
                    $stmt = $pdo->prepare("UPDATE acompanhantes SET nome = ?, email = ?, apelido = ?, telefone = ? WHERE id = ?");
                    $stmt->execute([$nome, $email, $apelido, $telefone, $user['id']]);
                }
                
                $_SESSION['success'] = 'Perfil atualizado com sucesso!';
                header('Location: index.php?page=perfil');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Erro ao atualizar perfil.';
            }
        }
    } elseif ($action === 'alterar_senha') {
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        
        if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
            $_SESSION['error'] = 'Todos os campos são obrigatórios.';
        } elseif ($nova_senha !== $confirmar_senha) {
            $_SESSION['error'] = 'As senhas não coincidem.';
        } elseif (strlen($nova_senha) < 6) {
            $_SESSION['error'] = 'A nova senha deve ter pelo menos 6 caracteres.';
        } else {
            // Verificar senha atual
            $senha_field = $userType === 'admin' ? 'senha' : 'senha';
            $stmt = $pdo->prepare("SELECT $senha_field FROM " . ($userType === 'admin' ? 'admins' : 'acompanhantes') . " WHERE id = ?");
            $stmt->execute([$user['id']]);
            $current_user = $stmt->fetch();
            
            if (password_verify($senha_atual, $current_user[$senha_field])) {
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("UPDATE " . ($userType === 'admin' ? 'admins' : 'acompanhantes') . " SET $senha_field = ? WHERE id = ?");
                $stmt->execute([$nova_senha_hash, $user['id']]);
                
                $_SESSION['success'] = 'Senha alterada com sucesso!';
                header('Location: index.php?page=perfil');
                exit;
            } else {
                $_SESSION['error'] = 'Senha atual incorreta.';
            }
        }
    }
}

$page_title = 'Meu Perfil';
$page_description = 'Gerencie suas informações pessoais e configurações de conta.';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-edit"></i> Meu Perfil
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Informações do Usuário -->
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <div class="position-relative d-inline-block">
                                <img src="<?php echo $user['foto'] ? SITE_URL . '/uploads/' . $user['foto'] : SITE_URL . '/assets/img/default-avatar.svg'; ?>" 
                                     alt="Foto do perfil" 
                                     class="rounded-circle img-thumbnail" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                                <button class="btn btn-sm btn-primary position-absolute bottom-0 end-0" 
                                        onclick="document.getElementById('uploadFoto').click()">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            <input type="file" id="uploadFoto" class="d-none" accept="image/*" onchange="uploadFoto(this)">
                            <p class="text-muted mt-2">Clique no ícone para alterar a foto</p>
                        </div>
                        <div class="col-md-8">
                            <h5><?php echo htmlspecialchars($user['nome']); ?></h5>
                            <p class="text-muted">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                            <?php if ($userType === 'acompanhante' && !empty($user['apelido'])): ?>
                                <p class="text-muted">
                                    <i class="fas fa-user-tag"></i> <?php echo htmlspecialchars($user['apelido']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($userType === 'acompanhante' && !empty($user['telefone'])): ?>
                                <p class="text-muted">
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['telefone']); ?>
                                </p>
                            <?php endif; ?>
                            <span class="badge bg-<?php echo $userType === 'admin' ? 'danger' : 'success'; ?>">
                                <?php echo $userType === 'admin' ? 'Administrador' : 'Acompanhante'; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Abas -->
                    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="perfil-tab" data-bs-toggle="tab" data-bs-target="#perfil" type="button" role="tab">
                                <i class="fas fa-user"></i> Informações Pessoais
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="senha-tab" data-bs-toggle="tab" data-bs-target="#senha" type="button" role="tab">
                                <i class="fas fa-lock"></i> Alterar Senha
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="profileTabsContent">
                        <!-- Aba Informações Pessoais -->
                        <div class="tab-pane fade show active" id="perfil" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="atualizar_perfil">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nome" class="form-label">Nome Completo *</label>
                                        <input type="text" class="form-control" id="nome" name="nome" 
                                               value="<?php echo htmlspecialchars($user['nome']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">E-mail *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>

                                <?php if ($userType === 'acompanhante'): ?>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="apelido" class="form-label">Apelido</label>
                                            <input type="text" class="form-control" id="apelido" name="apelido" 
                                                   value="<?php echo htmlspecialchars($user['apelido'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="telefone" class="form-label">Telefone</label>
                                            <input type="tel" class="form-control" id="telefone" name="telefone" 
                                                   value="<?php echo htmlspecialchars($user['telefone'] ?? ''); ?>">
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Salvar Alterações
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Aba Alterar Senha -->
                        <div class="tab-pane fade" id="senha" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="alterar_senha">
                                
                                <div class="mb-3">
                                    <label for="senha_atual" class="form-label">Senha Atual *</label>
                                    <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="nova_senha" class="form-label">Nova Senha *</label>
                                    <input type="password" class="form-control" id="nova_senha" name="nova_senha" 
                                           minlength="6" required>
                                    <div class="form-text">Mínimo 6 caracteres</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirmar_senha" class="form-label">Confirmar Nova Senha *</label>
                                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                                           minlength="6" required>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key"></i> Alterar Senha
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function uploadFoto(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('foto', input.files[0]);
        formData.append('tipo', '<?php echo $userType; ?>');
        formData.append('user_id', '<?php echo $user['id']; ?>');
        
        fetch('../api/upload-foto-perfil.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao fazer upload: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao fazer upload da foto');
        });
    }
}
</script> 