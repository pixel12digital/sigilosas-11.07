<?php
/**
 * Configurações do Sistema - Painel Admin
 * Arquivo: admin/configuracoes.php
 */

require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Configurações';
include '../includes/admin-header.php';

$db = getDB();

// Processar formulário de configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'salvar_configuracoes') {
        $configuracoes = [
            'site_nome' => trim($_POST['site_nome']),
            'site_descricao' => trim($_POST['site_descricao']),
            'site_email' => trim($_POST['site_email']),
            'site_telefone' => trim($_POST['site_telefone']),
            'site_endereco' => trim($_POST['site_endereco']),
            'max_upload_size' => (int)$_POST['max_upload_size'],
            'allowed_image_types' => trim($_POST['allowed_image_types']),
            'max_images_per_profile' => (int)$_POST['max_images_per_profile'],
            'auto_approve_profiles' => isset($_POST['auto_approve_profiles']) ? 1 : 0,
            'require_verification' => isset($_POST['require_verification']) ? 1 : 0,
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
            'maintenance_message' => trim($_POST['maintenance_message']),
            'google_analytics' => trim($_POST['google_analytics']),
            'facebook_pixel' => trim($_POST['facebook_pixel']),
            'meta_keywords' => trim($_POST['meta_keywords']),
            'meta_description' => trim($_POST['meta_description']),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Validar campos obrigatórios
        if (empty($configuracoes['site_nome'])) {
            $error = 'Nome do site é obrigatório';
        } elseif (empty($configuracoes['site_email'])) {
            $error = 'Email do site é obrigatório';
        } elseif (!filter_var($configuracoes['site_email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Email inválido';
        } else {
            // Salvar configurações
            foreach ($configuracoes as $key => $value) {
                $existing = $db->fetch("SELECT id FROM configuracoes WHERE chave = ?", [$key]);
                if ($existing) {
                    $db->update('configuracoes', ['valor' => $value, 'updated_at' => date('Y-m-d H:i:s')], 'chave = ?', [$key]);
                } else {
                    $db->insert('configuracoes', [
                        'chave' => $key,
                        'valor' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            $success = 'Configurações salvas com sucesso!';
        }
    }
}

// Buscar configurações atuais
$configs = $db->fetchAll("SELECT chave, valor FROM configuracoes");
$configuracoes = [];
foreach ($configs as $config) {
    $configuracoes[$config['chave']] = $config['valor'];
}

// Valores padrão se não existirem
$configuracoes = array_merge([
    'site_nome' => 'Sigilosas',
    'site_descricao' => 'Encontre acompanhantes de luxo',
    'site_email' => 'contato@sigilosas.com',
    'site_telefone' => '',
    'site_endereco' => '',
    'max_upload_size' => 5,
    'allowed_image_types' => 'jpg,jpeg,png,webp',
    'max_images_per_profile' => 10,
    'auto_approve_profiles' => 0,
    'require_verification' => 1,
    'maintenance_mode' => 0,
    'maintenance_message' => 'Site em manutenção. Volte em breve.',
    'google_analytics' => '',
    'facebook_pixel' => '',
    'meta_keywords' => 'acompanhantes, luxo, encontros',
    'meta_description' => 'Encontre acompanhantes de luxo em sua cidade'
], $configuracoes);

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h4 mb-0">
                <i class="fas fa-cog"></i> Configurações do Sistema
            </h1>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="action" value="salvar_configuracoes">
        
        <!-- Configurações Gerais -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> Configurações Gerais
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="site_nome" class="form-label">Nome do Site *</label>
                            <input type="text" class="form-control" id="site_nome" name="site_nome" 
                                   value="<?php echo htmlspecialchars($configuracoes['site_nome']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="site_email" class="form-label">Email do Site *</label>
                            <input type="email" class="form-control" id="site_email" name="site_email" 
                                   value="<?php echo htmlspecialchars($configuracoes['site_email']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="site_telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="site_telefone" name="site_telefone" 
                                   value="<?php echo htmlspecialchars($configuracoes['site_telefone']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="site_endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="site_endereco" name="site_endereco" 
                                   value="<?php echo htmlspecialchars($configuracoes['site_endereco']); ?>">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="site_descricao" class="form-label">Descrição do Site</label>
                    <textarea class="form-control" id="site_descricao" name="site_descricao" rows="3"><?php echo htmlspecialchars($configuracoes['site_descricao']); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Configurações de Upload -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-upload"></i> Configurações de Upload
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="max_upload_size" class="form-label">Tamanho Máximo (MB)</label>
                            <input type="number" class="form-control" id="max_upload_size" name="max_upload_size" 
                                   value="<?php echo $configuracoes['max_upload_size']; ?>" min="1" max="50">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="allowed_image_types" class="form-label">Tipos de Imagem Permitidos</label>
                            <input type="text" class="form-control" id="allowed_image_types" name="allowed_image_types" 
                                   value="<?php echo htmlspecialchars($configuracoes['allowed_image_types']); ?>" 
                                   placeholder="jpg,jpeg,png,webp">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="max_images_per_profile" class="form-label">Máximo de Imagens por Perfil</label>
                            <input type="number" class="form-control" id="max_images_per_profile" name="max_images_per_profile" 
                                   value="<?php echo $configuracoes['max_images_per_profile']; ?>" min="1" max="20">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações de Perfis -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user"></i> Configurações de Perfis
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="auto_approve_profiles" name="auto_approve_profiles" 
                                   <?php if ($configuracoes['auto_approve_profiles']) echo 'checked'; ?>>
                            <label class="form-check-label" for="auto_approve_profiles">
                                Aprovar perfis automaticamente
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="require_verification" name="require_verification" 
                                   <?php if ($configuracoes['require_verification']) echo 'checked'; ?>>
                            <label class="form-check-label" for="require_verification">
                                Exigir verificação de documentos
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modo Manutenção -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tools"></i> Modo Manutenção
                </h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                           <?php if ($configuracoes['maintenance_mode']) echo 'checked'; ?>>
                    <label class="form-check-label" for="maintenance_mode">
                        Ativar modo manutenção
                    </label>
                </div>
                <div class="mb-3">
                    <label for="maintenance_message" class="form-label">Mensagem de Manutenção</label>
                    <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="3"><?php echo htmlspecialchars($configuracoes['maintenance_message']); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Configurações de SEO -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-search"></i> Configurações de SEO
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?php echo htmlspecialchars($configuracoes['meta_description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                           value="<?php echo htmlspecialchars($configuracoes['meta_keywords']); ?>">
                </div>
            </div>
        </div>

        <!-- Configurações de Analytics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line"></i> Analytics e Tracking
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="google_analytics" class="form-label">Google Analytics (ID)</label>
                    <input type="text" class="form-control" id="google_analytics" name="google_analytics" 
                           value="<?php echo htmlspecialchars($configuracoes['google_analytics']); ?>" 
                           placeholder="G-XXXXXXXXXX">
                </div>
                <div class="mb-3">
                    <label for="facebook_pixel" class="form-label">Facebook Pixel (ID)</label>
                    <input type="text" class="form-control" id="facebook_pixel" name="facebook_pixel" 
                           value="<?php echo htmlspecialchars($configuracoes['facebook_pixel']); ?>" 
                           placeholder="XXXXXXXXXX">
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Configurações
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Preview da meta description
document.getElementById('meta_description').addEventListener('input', function() {
    const maxLength = 160;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    if (currentLength > maxLength) {
        this.style.borderColor = '#dc3545';
    } else if (currentLength > maxLength * 0.9) {
        this.style.borderColor = '#ffc107';
    } else {
        this.style.borderColor = '#198754';
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?> 