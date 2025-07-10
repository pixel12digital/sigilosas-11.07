<?php
/**
 * Detalhes da Acompanhante - Site Público
 * Arquivo: pages/acompanhante.php
 */

require_once __DIR__ . '/../config/database.php';

$acompanhante_id = (int)$_GET['id'];
if (!$acompanhante_id) {
    header('Location: acompanhantes.php');
    exit;
}

$db = getDB();

// Buscar dados da acompanhante
$acompanhante = $db->fetch("
    SELECT a.*, c.nome as cidade_nome, e.nome as estado_nome, e.uf as estado_uf
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON c.estado_id = e.id
    WHERE a.id = ? AND a.aprovada = 1 AND a.bloqueada = 0
", [$acompanhante_id]);

if (!$acompanhante) {
    header('Location: acompanhantes.php?error=Perfil não encontrado');
    exit;
}

// Buscar fotos da acompanhante
$fotos = $db->fetchAll("
    SELECT * FROM fotos 
    WHERE acompanhante_id = ? AND tipo = 'foto'
    ORDER BY ordem ASC, id ASC
", [$acompanhante_id]);

// Buscar vídeos da acompanhante
$videos = $db->fetchAll("
    SELECT * FROM videos 
    WHERE acompanhante_id = ? AND tipo = 'video'
    ORDER BY ordem ASC, id ASC
", [$acompanhante_id]);

// Processar formulário de contato
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'contato') {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $mensagem = trim($_POST['mensagem']);
        
        if (empty($nome) || empty($email) || empty($mensagem)) {
            $error_message = 'Por favor, preencha todos os campos obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Por favor, insira um email válido.';
        } else {
            // Aqui você pode implementar o envio de email ou salvar no banco
            $success_message = 'Mensagem enviada com sucesso! Entraremos em contato em breve.';
        }
    } elseif ($action === 'denuncia') {
        $motivo = trim($_POST['motivo']);
        $descricao = trim($_POST['descricao']);
        
        if (empty($motivo) || empty($descricao)) {
            $error_message = 'Por favor, preencha todos os campos da denúncia.';
        } else {
            // Salvar denúncia no banco
            $db->insert('denuncias', [
                'acompanhante_id' => $acompanhante_id,
                'tipo' => $motivo,
                'descricao' => $descricao,
                'status' => 'pendente',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $success_message = 'Denúncia enviada com sucesso. Nossa equipe irá analisar.';
        }
    }
}

$pageTitle = ($acompanhante['apelido'] ?? $acompanhante['nome']) . ' - Sigilosas';
$pageDescription = $acompanhante['descricao'] ? substr(strip_tags($acompanhante['descricao']), 0, 160) : 'Perfil de ' . ($acompanhante['apelido'] ?? $acompanhante['nome']);
include '../includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="home.php">Início</a></li>
            <li class="breadcrumb-item"><a href="acompanhantes.php">Acompanhantes</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($acompanhante['apelido'] ?? $acompanhante['nome']); ?></li>
        </ol>
    </div>
</nav>

<!-- Perfil da Acompanhante -->
<section class="profile-section py-5">
    <div class="container">
        <div class="row">
            <!-- Galeria de Fotos -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-body p-0">
                        <?php if (!empty($fotos)): ?>
                            <!-- Carousel de Fotos -->
                            <div id="fotoCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    <?php foreach ($fotos as $index => $foto): ?>
                                        <button type="button" data-bs-target="#fotoCarousel" 
                                                data-bs-slide-to="<?php echo $index; ?>" 
                                                <?php if ($index === 0) echo 'class="active"'; ?>>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="carousel-inner">
                                    <?php foreach ($fotos as $index => $foto): ?>
                                        <div class="carousel-item <?php if ($index === 0) echo 'active'; ?>">
                                            <img src="../uploads/<?php echo $foto['arquivo']; ?>" 
                                                 class="d-block w-100" 
                                                 style="height: 500px; object-fit: cover;"
                                                 alt="<?php echo htmlspecialchars($acompanhante['nome']); ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (count($fotos) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#fotoCarousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#fotoCarousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Thumbnails -->
                            <div class="p-3">
                                <div class="row">
                                    <?php foreach ($fotos as $index => $foto): ?>
                                        <div class="col-2 mb-2">
                                            <img src="../uploads/<?php echo $foto['arquivo']; ?>" 
                                                 class="img-thumbnail cursor-pointer" 
                                                 style="height: 80px; object-fit: cover;"
                                                 onclick="goToSlide(<?php echo $index; ?>)"
                                                 alt="Foto <?php echo $index + 1; ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Foto padrão -->
                            <div class="bg-secondary d-flex align-items-center justify-content-center" 
                                 style="height: 500px;">
                                <i class="fas fa-user fa-5x text-white"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Vídeos -->
                <?php if (!empty($videos)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-video"></i> Vídeos
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($videos as $video): ?>
                                    <div class="col-md-6 mb-3">
                                        <video controls class="w-100" style="max-height: 300px;">
                                            <source src="../uploads/<?php echo $video['arquivo']; ?>" type="video/mp4">
                                            Seu navegador não suporta vídeos.
                                        </video>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Informações da Acompanhante -->
            <div class="col-lg-4">
                <!-- Card Principal -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <h2 class="card-title mb-0"><?php echo htmlspecialchars($acompanhante['apelido'] ?? $acompanhante['nome']); ?></h2>
                            <?php if ($acompanhante['verificado']): ?>
                                <span class="badge bg-success ms-2">
                                    <i class="fas fa-check-circle"></i> Verificada
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($acompanhante['cidade_nome']); ?>, <?php echo $acompanhante['estado_uf']; ?>
                            </p>
                            
                            <?php if ($acompanhante['idade']): ?>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-birthday-cake"></i> <?php echo $acompanhante['idade']; ?> anos
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($acompanhante['tipo_servico']): ?>
                                <p class="mb-2">
                                    <span class="badge bg-primary">
                                        <i class="fas fa-tag"></i> 
                                        <?php
                                        $tipos = [
                                            'massagem' => 'Massagem',
                                            'acompanhante' => 'Acompanhante',
                                            'ambos' => 'Massagem & Acompanhante'
                                        ];
                                        echo $tipos[$acompanhante['tipo_servico']] ?? 'Serviços Diversos';
                                        ?>
                                    </span>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($acompanhante['descricao']): ?>
                            <div class="mb-3">
                                <h6>Descrição</h6>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($acompanhante['descricao'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($acompanhante['telefone']): ?>
                            <div class="mb-3">
                                <h6>Telefone</h6>
                                <p class="text-muted">
                                    <i class="fas fa-phone"></i> 
                                    <a href="tel:<?php echo $acompanhante['telefone']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($acompanhante['telefone']); ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($acompanhante['email']): ?>
                            <div class="mb-3">
                                <h6>Email</h6>
                                <p class="text-muted">
                                    <i class="fas fa-envelope"></i> 
                                    <a href="mailto:<?php echo $acompanhante['email']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($acompanhante['email']); ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contatoModal">
                                <i class="fas fa-envelope"></i> Enviar Mensagem
                            </button>
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#denunciaModal">
                                <i class="fas fa-flag"></i> Reportar Perfil
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Estatísticas -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar"></i> Estatísticas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stat-item">
                                    <h4 class="text-primary"><?php echo count($fotos); ?></h4>
                                    <small class="text-muted">Fotos</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item">
                                    <h4 class="text-primary"><?php echo count($videos); ?></h4>
                                    <small class="text-muted">Vídeos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de Contato -->
<div class="modal fade" id="contatoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-envelope"></i> Enviar Mensagem
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="contato">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Seu Nome *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Seu Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="telefone" name="telefone">
                    </div>
                    <div class="mb-3">
                        <label for="mensagem" class="form-label">Mensagem *</label>
                        <textarea class="form-control" id="mensagem" name="mensagem" rows="4" required 
                                  placeholder="Digite sua mensagem..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Denúncia -->
<div class="modal fade" id="denunciaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-flag"></i> Reportar Perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="denuncia">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Ajude-nos a manter a qualidade dos perfis. Sua denúncia será analisada pela nossa equipe.
                    </div>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo da Denúncia *</label>
                        <select class="form-select" id="motivo" name="motivo" required>
                            <option value="">Selecione o motivo</option>
                            <option value="inapropriado">Conteúdo Inapropriado</option>
                            <option value="fake">Perfil Fake</option>
                            <option value="spam">Spam</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição *</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4" required 
                                  placeholder="Descreva o motivo da denúncia..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-flag"></i> Enviar Denúncia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mensagens de Sucesso/Erro -->
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<script>
function goToSlide(index) {
    const carousel = new bootstrap.Carousel(document.getElementById('fotoCarousel'));
    carousel.to(index);
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}

.stat-item h4 {
    margin-bottom: 0.25rem;
}

.carousel-item img {
    border-radius: 0.375rem 0.375rem 0 0;
}

@media (max-width: 768px) {
    .carousel-item img {
        height: 300px !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?> 