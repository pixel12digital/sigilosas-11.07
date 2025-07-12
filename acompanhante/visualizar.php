<?php
require_once __DIR__ . '/../config/config.php';
/**
 * Visualização do Perfil da Acompanhante
 * Arquivo: acompanhante/visualizar.php
 */

$page_title = 'Ver Perfil';
$page_description = 'Visualize como seu perfil aparece para o público';
include __DIR__ . '/../includes/header.php';

if (!isset($db)) {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();
}

// Buscar mídia
$fotos = $db->fetchAll("
    SELECT * FROM fotos 
    WHERE acompanhante_id = ? 
    ORDER BY ordem ASC, created_at DESC
", [$_SESSION['acompanhante_id']]);

$videos = $db->fetchAll("
    SELECT * FROM videos 
    WHERE acompanhante_id = ? 
    ORDER BY created_at DESC
", [$_SESSION['acompanhante_id']]);

$documentos = $db->fetchAll("
    SELECT * FROM documentos 
    WHERE acompanhante_id = ? 
    ORDER BY created_at DESC
", [$_SESSION['acompanhante_id']]);

// Status de visibilidade
$status_visibilidade = [
    'pendente' => [
        'icon' => 'fas fa-eye-slash',
        'class' => 'text-warning',
        'text' => 'Perfil não visível publicamente (aguardando aprovação)'
    ],
    'ativo' => [
        'icon' => 'fas fa-eye',
        'class' => 'text-success',
        'text' => 'Perfil visível publicamente'
    ],
    'bloqueado' => [
        'icon' => 'fas fa-ban',
        'class' => 'text-danger',
        'text' => 'Perfil bloqueado e não visível'
    ]
];

$visibilidade = $status_visibilidade[$acompanhante['status']] ?? $status_visibilidade['pendente'];

// Carregar valores de atendimento do banco
$tempos = [
    '15min' => '15 minutos',
    '30min' => '30 minutos',
    '1h' => '1 hora',
    '2h' => '2 horas',
    '4h' => '4 horas',
    'diaria' => 'Diária',
    'pernoite' => 'Pernoite',
    'diaria_viagem' => 'Diária de viagem'
];
$valores_atendimento = [];
$rows = $db->fetchAll("SELECT * FROM valores_atendimento WHERE acompanhante_id = ?", [$acompanhante['id']]);
foreach ($rows as $row) {
    $valores_atendimento[$row['tempo']] = $row;
}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-eye"></i> Visualizar Perfil</h2>
    <div>
        <a href="perfil.php" class="btn btn-primary me-2">
            <i class="fas fa-edit"></i> Editar Perfil
        </a>
        <a href="midia.php" class="btn btn-success">
            <i class="fas fa-images"></i> Gerenciar Mídia
        </a>
    </div>
</div>

<!-- Status de Visibilidade -->
<div class="alert alert-info">
    <i class="<?php echo $visibilidade['icon']; ?> <?php echo $visibilidade['class']; ?>"></i>
    <strong>Status de Visibilidade:</strong> <?php echo $visibilidade['text']; ?>
</div>

<!-- Perfil Principal -->
<div class="row">
    <div class="col-lg-8">
        <!-- Informações Básicas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user"></i> Informações Básicas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nome:</strong></td>
                                <td><?php echo htmlspecialchars($acompanhante['nome']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Apelido:</strong></td>
                                <td><?php echo htmlspecialchars($acompanhante['apelido'] ?? 'Não informado'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Idade:</strong></td>
                                <td><?php echo $acompanhante['idade'] ? $acompanhante['idade'] . ' anos' : 'Não informado'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Cidade:</strong></td>
                                <td><?php echo $acompanhante['cidade_nome'] ? $acompanhante['cidade_nome'] . ' - ' . $acompanhante['estado_uf'] : 'Não informado'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Altura:</strong></td>
                                <td><?php echo $acompanhante['altura'] ? $acompanhante['altura'] . ' cm' : 'Não informado'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Peso:</strong></td>
                                <td><?php echo $acompanhante['peso'] ? $acompanhante['peso'] . ' kg' : 'Não informado'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Medidas:</strong></td>
                                <td><?php echo htmlspecialchars($acompanhante['medidas'] ?? 'Não informado'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Verificado:</strong></td>
                                <td><?php echo $acompanhante['verificado'] ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descrição -->
        <?php if (!empty($acompanhante['descricao'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-comment"></i> Descrição
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($acompanhante['descricao'])); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sobre Mim -->
        <?php if (!empty($acompanhante['sobre_mim'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-heart"></i> Sobre Mim
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($acompanhante['sobre_mim'])); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Preços e Horários -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-dollar-sign"></i> Preços e Horários
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Preços</h6>
                        <ul class="list-unstyled">
                            <li><strong>Preço Padrão:</strong> 
                                <?php echo $acompanhante['valor_padrao'] ? 'R$ ' . number_format($acompanhante['valor_padrao'], 2, ',', '.') : 'Não informado'; ?>
                            </li>
                            <li><strong>Preço Promocional:</strong> 
                                <?php echo $acompanhante['valor_promocional'] ? 'R$ ' . number_format($acompanhante['valor_promocional'], 2, ',', '.') : 'Não informado'; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Redes Sociais -->
        <?php if (!empty($acompanhante['instagram']) || !empty($acompanhante['twitter']) || !empty($acompanhante['tiktok'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-share-alt"></i> Redes Sociais
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($acompanhante['instagram'])): ?>
                    <div class="col-md-4">
                        <a href="https://instagram.com/<?php echo htmlspecialchars($acompanhante['instagram']); ?>" 
                           target="_blank" class="btn btn-outline-danger w-100">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($acompanhante['twitter'])): ?>
                    <div class="col-md-4">
                        <a href="https://twitter.com/<?php echo htmlspecialchars($acompanhante['twitter']); ?>" 
                           target="_blank" class="btn btn-outline-primary w-100">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($acompanhante['tiktok'])): ?>
                    <div class="col-md-4">
                        <a href="https://tiktok.com/@<?php echo htmlspecialchars($acompanhante['tiktok']); ?>" 
                           target="_blank" class="btn btn-outline-dark w-100">
                            <i class="fab fa-tiktok"></i> TikTok
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Valores -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-dollar-sign"></i> Valores
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless align-middle">
                            <tbody>
                                <?php foreach ([['30min','15min','4h','diaria'],['1h','2h','pernoite','diaria_viagem']] as $col): ?>
                                    <?php foreach ($col as $i => $tempo): ?>
                                        <?php if ($i == 0): ?><tr><?php endif; ?>
                                        <td style="width:40%">
                                            <span style="<?php if($tempo=='15min'||$tempo=='4h'||$tempo=='diaria'||$tempo=='pernoite'||$tempo=='diaria_viagem') echo 'font-style:italic; text-decoration:line-through;'; ?>">
                                                <?php echo $tempos[$tempo]; ?>
                                            </span>
                                        </td>
                                        <td style="width:60%">
                                            <?php if(!empty($valores_atendimento[$tempo]['disponivel']) && $valores_atendimento[$tempo]['valor'] !== null): ?>
                                                R$ <?php echo number_format($valores_atendimento[$tempo]['valor'],2,',','.'); ?>
                                            <?php else: ?>
                                                <span style="font-style:italic;<?php if($tempo=='15min'||$tempo=='4h'||$tempo=='diaria'||$tempo=='pernoite'||$tempo=='diaria_viagem') echo 'text-decoration:line-through;'; ?>">Não realiza</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($i == 1): ?></tr><?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Foto Principal -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-image"></i> Foto Principal
                </h5>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($fotos)): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/fotos/<?php echo $fotos[0]['arquivo']; ?>" 
                         class="img-fluid rounded" alt="Foto Principal" style="max-height: 300px;">
                <?php else: ?>
                    <div class="py-5">
                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhuma foto adicionada</p>
                        <a href="upload-midia.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Adicionar Foto
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar"></i> Estatísticas
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h4 class="text-primary"><?php echo count($fotos); ?></h4>
                        <small class="text-muted">Fotos</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success"><?php echo count($videos); ?></h4>
                        <small class="text-muted">Vídeos</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info"><?php echo count($documentos); ?></h4>
                        <small class="text-muted">Documentos</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning">0</h4>
                        <small class="text-muted">Visualizações</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações de Contato (Privadas) -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-lock"></i> Informações Privadas
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    <strong>Nota:</strong> Estas informações não são exibidas publicamente.
                </div>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo htmlspecialchars($acompanhante['email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Telefone:</strong></td>
                        <td><?php echo htmlspecialchars($acompanhante['telefone']); ?></td>
                    </tr>
                    <?php if (!empty($acompanhante['whatsapp'])): ?>
                    <tr>
                        <td><strong>WhatsApp:</strong></td>
                        <td><?php echo htmlspecialchars($acompanhante['whatsapp']); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Galeria de Fotos -->
<?php if (!empty($fotos)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-images"></i> Galeria de Fotos
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($fotos as $index => $foto): ?>
                <div class="col-md-3 col-sm-6 mb-3">
                    <img src="<?php echo SITE_URL; ?>/uploads/fotos/<?php echo $foto['arquivo']; ?>" 
                         class="img-fluid rounded" alt="Foto <?php echo $index + 1; ?>"
                         style="height: 200px; object-fit: cover; width: 100%;">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Vídeos -->
<?php if (!empty($videos)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-video"></i> Vídeos
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($videos as $video): ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($video['titulo'] ?? 'Vídeo'); ?></h6>
                            <a href="<?php echo htmlspecialchars($video['url']); ?>" 
                               target="_blank" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-play"></i> Assistir
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Documentos (Apenas para acompanhante) -->
<?php if (!empty($documentos)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-file"></i> Documentos (Privados)
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Nota:</strong> Estes documentos são privados e não são exibidos publicamente.
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Nome</th>
                        <th>Adicionado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentos as $doc): ?>
                        <tr>
                            <td>
                                <span class="badge bg-info"><?php echo ucfirst($doc['tipo']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($doc['nome']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($doc['created_at'])); ?></td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/uploads/documentos/<?php echo $doc['arquivo']; ?>" 
                                   class="btn btn-outline-info btn-sm" target="_blank">
                                    <i class="fas fa-download"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Link Público -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-link"></i> Link Público
        </h5>
    </div>
    <div class="card-body">
        <?php if ($acompanhante['status'] === 'ativo'): ?>
            <div class="input-group">
                <input type="text" class="form-control" 
                       value="<?php echo SITE_URL . '/pages/acompanhante.php?id=' . $_SESSION['acompanhante_id']; ?>" 
                       readonly>
                <button class="btn btn-outline-primary" type="button" onclick="copiarLink()">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
            <div class="form-text">Este é o link público do seu perfil</div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Perfil não disponível publicamente.</strong> 
                Aguarde a aprovação para que seu perfil seja visível.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copiarLink() {
    const linkInput = document.querySelector('input[readonly]');
    linkInput.select();
    linkInput.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Feedback visual
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Copiado!';
    button.classList.remove('btn-outline-primary');
    button.classList.add('btn-success');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-primary');
    }, 2000);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?> 