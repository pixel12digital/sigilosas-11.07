<?php
/**
 * Aprovação de Acompanhantes - Painel Admin
 * Arquivo: admin/aprovar-acompanhantes.php
 */

// Proteção de sessão admin
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Aprovar Acompanhantes';
require_once '../includes/admin-header.php';

$db = getDB();

// Processar ações
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['acompanhante_id'])) {
    $acompanhante_id = (int)$_POST['acompanhante_id'];
    $action = $_POST['action'];
    $motivo = trim($_POST['motivo'] ?? '');
    
    // Debug log
    error_log("Ação solicitada: $action para acompanhante ID: $acompanhante_id");
    
    try {
        switch ($action) {
            case 'aprovar':
                // Aprovar a conta
                $db->update('acompanhantes', [
                    'status' => 'aprovado',
                    'revisado_por' => $_SESSION['user_id'],
                    'data_revisao' => date('Y-m-d H:i:s'),
                    'motivo_rejeicao' => null
                ], 'id = ?', [$acompanhante_id]);
                
                // Aprovar automaticamente todas as mídias
                $db->update('fotos', ['aprovada' => 1], 'acompanhante_id = ?', [$acompanhante_id]);
                $db->update('videos_publicos', ['status' => 'aprovado'], 'acompanhante_id = ?', [$acompanhante_id]);
                $db->update('documentos_acompanhante', ['verificado' => 1], 'acompanhante_id = ?', [$acompanhante_id]);
                
                $success = 'Acompanhante e todas as mídias aprovadas com sucesso!';
                break;
                
            case 'rejeitar':
                if (empty($motivo)) {
                    $error = 'Motivo da rejeição é obrigatório.';
                    break;
                }
                $db->update('acompanhantes', [
                    'status' => 'rejeitado',
                    'revisado_por' => $_SESSION['user_id'],
                    'data_revisao' => date('Y-m-d H:i:s'),
                    'motivo_rejeicao' => $motivo
                ], 'id = ?', [$acompanhante_id]);
                $success = 'Acompanhante rejeitada com sucesso!';
                break;
                
            case 'bloquear':
                if (empty($motivo)) {
                    $error = 'Motivo do bloqueio é obrigatório.';
                    break;
                }
                $db->update('acompanhantes', [
                    'status' => 'bloqueado',
                    'bloqueado' => 1,
                    'motivo_bloqueio' => $motivo,
                    'revisado_por' => $_SESSION['user_id'],
                    'data_revisao' => date('Y-m-d H:i:s')
                ], 'id = ?', [$acompanhante_id]);
                $success = 'Acompanhante bloqueada com sucesso!';
                break;
                
            case 'excluir':
                // Buscar arquivos para excluir fisicamente
                $fotos = $db->fetchAll("SELECT url FROM fotos WHERE acompanhante_id = ?", [$acompanhante_id]);
                $videos_verificacao = $db->fetchAll("SELECT url FROM videos_verificacao WHERE acompanhante_id = ?", [$acompanhante_id]);
                $videos_publicos = $db->fetchAll("SELECT url FROM videos_publicos WHERE acompanhante_id = ?", [$acompanhante_id]);
                $documentos = $db->fetchAll("SELECT url FROM documentos_acompanhante WHERE acompanhante_id = ?", [$acompanhante_id]);
                
                // Excluir arquivos físicos
                foreach ($fotos as $foto) {
                    if (!empty($foto['url'])) {
                        $arquivo = __DIR__ . '/../uploads/galeria/' . $foto['url'];
                        if (file_exists($arquivo)) unlink($arquivo);
                        $arquivo_perfil = __DIR__ . '/../uploads/perfil/' . $foto['url'];
                        if (file_exists($arquivo_perfil)) unlink($arquivo_perfil);
                    }
                }
                foreach ($videos_verificacao as $video) {
                    if (!empty($video['url'])) {
                        $arquivo = __DIR__ . '/../uploads/verificacao/' . $video['url'];
                        if (file_exists($arquivo)) unlink($arquivo);
                    }
                }
                foreach ($videos_publicos as $video) {
                    if (!empty($video['url'])) {
                        $arquivo = __DIR__ . '/../uploads/videos_publicos/' . $video['url'];
                        if (file_exists($arquivo)) unlink($arquivo);
                    }
                }
                foreach ($documentos as $doc) {
                    if (!empty($doc['url'])) {
                        $arquivo = __DIR__ . '/../uploads/documentos/' . $doc['url'];
                        if (file_exists($arquivo)) unlink($arquivo);
                    }
                }
                
                // Excluir registros do banco (todas as tabelas relacionadas)
                $db->delete('fotos', 'acompanhante_id = ?', [$acompanhante_id]);
                $db->delete('videos_verificacao', 'acompanhante_id = ?', [$acompanhante_id]);
                $db->delete('videos_publicos', 'acompanhante_id = ?', [$acompanhante_id]);
                $db->delete('documentos_acompanhante', 'acompanhante_id = ?', [$acompanhante_id]);
                $db->delete('valores_atendimento', 'acompanhante_id = ?', [$acompanhante_id]);
                $db->delete('horarios_atendimento', 'acompanhante_id = ?', [$acompanhante_id]);
                $db->delete('avaliacoes', 'acompanhante_id = ?', [$acompanhante_id]);
                $db->delete('denuncias', 'acompanhante_id = ?', [$acompanhante_id]);
                
                // Excluir acompanhante
                $result = $db->delete('acompanhantes', 'id = ?', [$acompanhante_id]);
                
                if ($result) {
                    $success = 'Acompanhante e todos os dados relacionados excluídos com sucesso!';
                    error_log("Acompanhante ID $acompanhante_id excluída com sucesso");
                } else {
                    $error = 'Erro ao excluir acompanhante do banco de dados.';
                    error_log("Falha ao excluir acompanhante ID $acompanhante_id");
                }
                break;
        }
    } catch (Exception $e) {
        $error = 'Erro ao processar ação: ' . $e->getMessage();
        error_log("Erro na exclusão de acompanhante ID $acompanhante_id: " . $e->getMessage());
    }
}

// Filtros
$status = $_GET['status'] ?? 'pendente';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$where = ["a.status = ?"];
$params = [$status];

if ($search) {
    $where[] = "(a.nome LIKE ? OR a.email LIKE ? OR c.nome LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Buscar acompanhantes
$sql = "SELECT a.*, 
               c.nome as cidade_nome,
               e.uf as estado_uf,
               e.nome as estado_nome,
               (SELECT COUNT(*) FROM fotos WHERE acompanhante_id = a.id) as total_fotos,
               (SELECT COUNT(*) FROM videos_verificacao WHERE acompanhante_id = a.id) as total_videos,
               (SELECT COUNT(*) FROM documentos_acompanhante WHERE acompanhante_id = a.id) as total_documentos
        FROM acompanhantes a
        LEFT JOIN cidades c ON a.cidade_id = c.id
        LEFT JOIN estados e ON a.estado_id = e.id
        $whereClause
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$acompanhantes = $db->fetchAll($sql, $params);

// Contar total
$countSql = "SELECT COUNT(*) as total FROM acompanhantes a 
             LEFT JOIN cidades c ON a.cidade_id = c.id 
             LEFT JOIN estados e ON a.estado_id = e.id 
             $whereClause";
$total = $db->fetch($countSql, array_slice($params, 0, -2));
$totalPages = ceil($total['total'] / $limit);

// Estatísticas
$stats = [
    'pendentes' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE status = 'pendente'")['total'],
    'aprovadas' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE status = 'aprovado'")['total'],
    'rejeitadas' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE status = 'rejeitado'")['total'],
    'bloqueadas' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE status = 'bloqueado'")['total']
];
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">
                <i class="fas fa-user-check"></i> Aprovar Acompanhantes
            </h1>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
            </a>
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

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendentes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pendentes']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aprovadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['aprovadas']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejeitadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['rejeitadas']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-dark">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Bloqueadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['bloqueadas']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filtros
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="pendente" <?php if ($status === 'pendente') echo 'selected'; ?>>Pendentes</option>
                        <option value="aprovado" <?php if ($status === 'aprovado') echo 'selected'; ?>>Aprovadas</option>
                        <option value="rejeitado" <?php if ($status === 'rejeitado') echo 'selected'; ?>>Rejeitadas</option>
                        <option value="bloqueado" <?php if ($status === 'bloqueado') echo 'selected'; ?>>Bloqueadas</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Nome, email ou cidade" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Acompanhantes -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Lista de Acompanhantes (<?php echo ucfirst($status); ?>)
        </div>
        <div class="card-body">
            <?php if (empty($acompanhantes)): ?>
                <p class="text-muted text-center">Nenhuma acompanhante encontrada.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Cidade</th>
                                <th>Mídias</th>
                                <th>Cadastro</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($acompanhantes as $a): ?>
                                <tr>
                                    <td>
                                        <?php if ($a['foto_perfil']): ?>
                                            <img src="<?php echo htmlspecialchars($a['foto_perfil']); ?>" class="rounded-circle" width="50" height="50" alt="Foto">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($a['nome']); ?></strong>
                                        <?php if ($a['idade']): ?>
                                            <br><small class="text-muted"><?php echo $a['idade']; ?> anos</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($a['email']); ?></td>
                                    <td><?php echo htmlspecialchars($a['cidade_nome']); ?><?php if ($a['estado_uf']): ?> - <?php echo $a['estado_uf']; ?><?php endif; ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-info" title="Fotos">📷 <?php echo $a['total_fotos']; ?></span>
                                            <span class="badge bg-warning" title="Vídeos">🎥 <?php echo $a['total_videos']; ?></span>
                                            <span class="badge bg-secondary" title="Documentos">📄 <?php echo $a['total_documentos']; ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($a['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        $statusText = '';
                                        switch ($a['status']) {
                                            case 'pendente':
                                                $statusClass = 'badge bg-warning';
                                                $statusText = 'Pendente';
                                                break;
                                            case 'aprovado':
                                                $statusClass = 'badge bg-success';
                                                $statusText = 'Aprovado';
                                                break;
                                            case 'rejeitado':
                                                $statusClass = 'badge bg-danger';
                                                $statusText = 'Rejeitado';
                                                break;
                                            case 'bloqueado':
                                                $statusClass = 'badge bg-dark';
                                                $statusText = 'Bloqueado';
                                                break;
                                        }
                                        ?>
                                        <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="acompanhante-visualizar.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-primary" title="Visualizar/Editar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if ($a['status'] === 'pendente'): ?>
                                                <button type="button" class="btn btn-sm btn-success" title="Aprovar" 
                                                        onclick="confirmAction(<?php echo $a['id']; ?>, 'aprovar', 'Tem certeza que deseja aprovar esta acompanhante?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" title="Rejeitar" 
                                                        onclick="showRejectModal(<?php echo $a['id']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" title="Bloquear" 
                                                        onclick="showBlockModal(<?php echo $a['id']; ?>)">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" 
                                                    onclick="confirmAction(<?php echo $a['id']; ?>, 'excluir', 'Tem certeza que deseja excluir esta acompanhante? Esta ação não pode ser desfeita.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php if ($i === $page) echo 'active'; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Rejeição -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeitar Acompanhante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="rejeitar">
                    <input type="hidden" name="acompanhante_id" id="reject_acompanhante_id">
                    <div class="mb-3">
                        <label for="reject_motivo" class="form-label">Motivo da Rejeição *</label>
                        <textarea class="form-control" name="motivo" id="reject_motivo" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Rejeitar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Bloqueio -->
<div class="modal fade" id="blockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bloquear Acompanhante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="bloquear">
                    <input type="hidden" name="acompanhante_id" id="block_acompanhante_id">
                    <div class="mb-3">
                        <label for="block_motivo" class="form-label">Motivo do Bloqueio *</label>
                        <textarea class="form-control" name="motivo" id="block_motivo" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Bloquear</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulário oculto para ações -->
<form id="actionForm" method="post" style="display: none;">
    <input type="hidden" name="action" id="action_type">
    <input type="hidden" name="acompanhante_id" id="action_acompanhante_id">
</form>

<script>
function confirmAction(acompanhanteId, action, message) {
    if (confirm(message)) {
        document.getElementById('action_type').value = action;
        document.getElementById('action_acompanhante_id').value = acompanhanteId;
        document.getElementById('actionForm').submit();
    }
}

function showRejectModal(acompanhanteId) {
    document.getElementById('reject_acompanhante_id').value = acompanhanteId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showBlockModal(acompanhanteId) {
    document.getElementById('block_acompanhante_id').value = acompanhanteId;
    new bootstrap.Modal(document.getElementById('blockModal')).show();
}
</script>

<?php require_once '../includes/admin-footer.php'; ?> 