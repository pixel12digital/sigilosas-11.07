<?php
require_once __DIR__ . '/../config/config.php';
/**
 * Upload de Mídia da Acompanhante
 * Arquivo: acompanhante/midia.php
 */

$page_title = 'Fotos e Vídeos';
$page_description = 'Gerencie suas fotos, vídeos e documentos';
include __DIR__ . '/../includes/header.php';

if (!isset($db)) {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();
}

// Buscar mídia existente
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

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-images"></i> Fotos e Vídeos</h2>
    <div>
        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#uploadFotoModal">
            <i class="fas fa-plus"></i> Adicionar Foto
        </button>
        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#uploadVideoModal">
            <i class="fas fa-video"></i> Adicionar Vídeo
        </button>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#uploadDocumentoModal">
            <i class="fas fa-file"></i> Adicionar Documento
        </button>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary"><?php echo count($fotos); ?></h3>
                <p class="mb-0">Fotos</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success"><?php echo count($videos); ?></h3>
                <p class="mb-0">Vídeos</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info"><?php echo count($documentos); ?></h3>
                <p class="mb-0">Documentos</p>
            </div>
        </div>
    </div>
</div>

<!-- Fotos -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-image text-primary"></i> Fotos
        </h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadFotoModal">
            <i class="fas fa-plus"></i> Adicionar
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($fotos)): ?>
            <div class="text-center py-4">
                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                <p class="text-muted">Nenhuma foto adicionada ainda</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFotoModal">
                    <i class="fas fa-plus"></i> Adicionar Primeira Foto
                </button>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($fotos as $foto): ?>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100">
                            <img src="../uploads/fotos/<?php echo $foto['arquivo']; ?>" 
                                 class="card-img-top" alt="Foto" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <p class="card-text small">
                                    <strong>Ordem:</strong> <?php echo $foto['ordem']; ?><br>
                                    <strong>Adicionada:</strong> <?php echo date('d/m/Y', strtotime($foto['created_at'])); ?>
                                </p>
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                            onclick="editarFoto(<?php echo $foto['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="excluirFoto(<?php echo $foto['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Vídeos -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-video text-success"></i> Vídeos
        </h5>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadVideoModal">
            <i class="fas fa-plus"></i> Adicionar
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($videos)): ?>
            <div class="text-center py-4">
                <i class="fas fa-video fa-3x text-muted mb-3"></i>
                <p class="text-muted">Nenhum vídeo adicionado ainda</p>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadVideoModal">
                    <i class="fas fa-plus"></i> Adicionar Primeiro Vídeo
                </button>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($videos as $video): ?>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($video['titulo'] ?? 'Vídeo'); ?></h6>
                                <p class="card-text small">
                                    <strong>URL:</strong> <?php echo htmlspecialchars($video['url']); ?><br>
                                    <strong>Adicionado:</strong> <?php echo date('d/m/Y', strtotime($video['created_at'])); ?>
                                </p>
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-success btn-sm" 
                                            onclick="visualizarVideo('<?php echo htmlspecialchars($video['url']); ?>')">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="excluirVideo(<?php echo $video['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Documentos -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file text-info"></i> Documentos
        </h5>
        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocumentoModal">
            <i class="fas fa-plus"></i> Adicionar
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($documentos)): ?>
            <div class="text-center py-4">
                <i class="fas fa-file fa-3x text-muted mb-3"></i>
                <p class="text-muted">Nenhum documento adicionado ainda</p>
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#uploadDocumentoModal">
                    <i class="fas fa-plus"></i> Adicionar Primeiro Documento
                </button>
            </div>
        <?php else: ?>
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
                                    <div class="btn-group" role="group">
                                        <a href="../uploads/documentos/<?php echo $doc['arquivo']; ?>" 
                                           class="btn btn-outline-info btn-sm" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="excluirDocumento(<?php echo $doc['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Upload Foto -->
<div class="modal fade" id="uploadFotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-image"></i> Adicionar Foto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/upload-foto.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="foto" class="form-label">Selecionar Foto *</label>
                        <input type="file" class="form-control" id="foto" name="foto" 
                               accept="image/*" required>
                        <div class="form-text">Formatos: JPG, PNG, GIF. Máximo: 5MB</div>
                    </div>
                    <div class="mb-3">
                        <label for="ordem" class="form-label">Ordem</label>
                        <input type="number" class="form-control" id="ordem" name="ordem" 
                               value="<?php echo count($fotos) + 1; ?>" min="1">
                        <div class="form-text">Ordem de exibição (1 = primeira)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload Vídeo -->
<div class="modal fade" id="uploadVideoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-video"></i> Adicionar Vídeo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/upload-video.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               placeholder="Título do vídeo">
                    </div>
                    <div class="mb-3">
                        <label for="url" class="form-label">URL do Vídeo *</label>
                        <input type="url" class="form-control" id="url" name="url" 
                               placeholder="https://www.youtube.com/watch?v=..." required>
                        <div class="form-text">YouTube, Vimeo ou link direto</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload Documento -->
<div class="modal fade" id="uploadDocumentoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file"></i> Adicionar Documento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/upload-documento.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de Documento *</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Selecione...</option>
                            <option value="identidade">Identidade</option>
                            <option value="cpf">CPF</option>
                            <option value="comprovante">Comprovante de Residência</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Documento</label>
                        <input type="text" class="form-control" id="nome" name="nome" 
                               placeholder="Ex: RG - Frente">
                    </div>
                    <div class="mb-3">
                        <label for="documento" class="form-label">Arquivo *</label>
                        <input type="file" class="form-control" id="documento" name="documento" 
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text">Formatos: PDF, JPG, PNG. Máximo: 10MB</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-upload"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Visualizar Vídeo -->
<div class="modal fade" id="visualizarVideoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-play"></i> Visualizar Vídeo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="videoContainer" class="ratio ratio-16x9">
                    <!-- Vídeo será inserido aqui -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funções para exclusão
function excluirFoto(id) {
    if (confirm('Tem certeza que deseja excluir esta foto?')) {
        window.location.href = `api/excluir-foto.php?id=${id}`;
    }
}

function excluirVideo(id) {
    if (confirm('Tem certeza que deseja excluir este vídeo?')) {
        window.location.href = `api/excluir-video.php?id=${id}`;
    }
}

function excluirDocumento(id) {
    if (confirm('Tem certeza que deseja excluir este documento?')) {
        window.location.href = `api/excluir-documento.php?id=${id}`;
    }
}

// Função para visualizar vídeo
function visualizarVideo(url) {
    const modal = new bootstrap.Modal(document.getElementById('visualizarVideoModal'));
    const container = document.getElementById('videoContainer');
    
    // Limpar container
    container.innerHTML = '';
    
    // Criar iframe para o vídeo
    const iframe = document.createElement('iframe');
    iframe.src = url;
    iframe.allowFullscreen = true;
    iframe.className = 'w-100 h-100';
    
    container.appendChild(iframe);
    modal.show();
}

// Preview de imagem
document.getElementById('foto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('Arquivo muito grande. Máximo 5MB.');
            e.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            // Aqui você pode adicionar preview se desejar
        };
        reader.readAsDataURL(file);
    }
});

// Preview de documento
document.getElementById('documento').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 10 * 1024 * 1024) {
            alert('Arquivo muito grande. Máximo 10MB.');
            e.target.value = '';
            return;
        }
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?> 