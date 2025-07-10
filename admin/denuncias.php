<?php
/**
 * Gestão de Denúncias - Painel Admin
 * Arquivo: admin/denuncias.php
 */

require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Denúncias';
include '../includes/admin-header.php';

$db = getDB();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'marcar_resolvida':
            $denuncia_id = (int)$_POST['denuncia_id'];
            $resolucao = trim($_POST['resolucao']);
            
            if (empty($resolucao)) {
                header('Location: denuncias.php?error=Descrição da resolução é obrigatória');
                exit;
            }
            
            $data = [
                'status' => 'resolvida',
                'resolucao' => $resolucao,
                'resolvida_por' => $_SESSION['admin_id'],
                'resolvida_em' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $db->update('denuncias', $data, 'id = ?', [$denuncia_id]);
            header('Location: denuncias.php?success=Denúncia marcada como resolvida');
            exit;
            
        case 'marcar_invalida':
            $denuncia_id = (int)$_POST['denuncia_id'];
            $motivo = trim($_POST['motivo']);
            
            if (empty($motivo)) {
                header('Location: denuncias.php?error=Motivo da invalidação é obrigatório');
                exit;
            }
            
            $data = [
                'status' => 'invalida',
                'resolucao' => $motivo,
                'resolvida_por' => $_SESSION['admin_id'],
                'resolvida_em' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $db->update('denuncias', $data, 'id = ?', [$denuncia_id]);
            header('Location: denuncias.php?success=Denúncia marcada como inválida');
            exit;
            
        case 'bloquear_acompanhante':
            $denuncia_id = (int)$_POST['denuncia_id'];
            $acompanhante_id = (int)$_POST['acompanhante_id'];
            $motivo = trim($_POST['motivo']);
            
            if (empty($motivo)) {
                header('Location: denuncias.php?error=Motivo do bloqueio é obrigatório');
                exit;
            }
            
            // Bloquear acompanhante
            $db->update('acompanhantes', ['bloqueada' => 1, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$acompanhante_id]);
            
            // Marcar denúncia como resolvida
            $data = [
                'status' => 'resolvida',
                'resolucao' => "Acompanhante bloqueada. Motivo: $motivo",
                'resolvida_por' => $_SESSION['admin_id'],
                'resolvida_em' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $db->update('denuncias', $data, 'id = ?', [$denuncia_id]);
            header('Location: denuncias.php?success=Acompanhante bloqueada e denúncia resolvida');
            exit;
    }
}

// Filtros
$status = $_GET['status'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($status) {
    $where[] = "d.status = ?";
    $params[] = $status;
}
if ($tipo) {
    $where[] = "d.tipo = ?";
    $params[] = $tipo;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Buscar denúncias
$sql = "SELECT d.*, a.nome as acompanhante_nome, a.foto as acompanhante_foto,
               c.nome as cidade_nome, e.nome as estado_nome, e.uf as estado_uf
        FROM denuncias d
        LEFT JOIN acompanhantes a ON d.acompanhante_id = a.id
        LEFT JOIN cidades c ON a.cidade_id = c.id
        LEFT JOIN estados e ON c.estado_id = e.id
        $whereClause
        ORDER BY d.created_at DESC
        LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$denuncias = $db->fetchAll($sql, $params);

// Contar total
$countSql = "SELECT COUNT(*) as total FROM denuncias d
             LEFT JOIN acompanhantes a ON d.acompanhante_id = a.id
             LEFT JOIN cidades c ON a.cidade_id = c.id
             LEFT JOIN estados e ON c.estado_id = e.id
             $whereClause";
$total = $db->fetch($countSql, array_slice($params, 0, -2));
$totalPages = ceil($total['total'] / $limit);

// Estatísticas de denúncias
$stats = [
    'total' => $db->fetch("SELECT COUNT(*) as total FROM denuncias")['total'],
    'pendentes' => $db->fetch("SELECT COUNT(*) as total FROM denuncias WHERE status = 'pendente'")['total'],
    'resolvidas' => $db->fetch("SELECT COUNT(*) as total FROM denuncias WHERE status = 'resolvida'")['total'],
    'invalidas' => $db->fetch("SELECT COUNT(*) as total FROM denuncias WHERE status = 'invalida'")['total']
];

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h4 mb-0">
                <i class="fas fa-flag"></i> Gestão de Denúncias
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

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total de Denúncias
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pendentes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['pendentes']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Resolvidas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['resolvidas']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Inválidas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['invalidas']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-4">
                            <select name="status" class="form-select">
                                <option value="">Todos os Status</option>
                                <option value="pendente" <?php if ($status == 'pendente') echo 'selected'; ?>>Pendente</option>
                                <option value="resolvida" <?php if ($status == 'resolvida') echo 'selected'; ?>>Resolvida</option>
                                <option value="invalida" <?php if ($status == 'invalida') echo 'selected'; ?>>Inválida</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="tipo" class="form-select">
                                <option value="">Todos os Tipos</option>
                                <option value="inapropriado" <?php if ($tipo == 'inapropriado') echo 'selected'; ?>>Conteúdo Inapropriado</option>
                                <option value="fake" <?php if ($tipo == 'fake') echo 'selected'; ?>>Perfil Fake</option>
                                <option value="spam" <?php if ($tipo == 'spam') echo 'selected'; ?>>Spam</option>
                                <option value="outro" <?php if ($tipo == 'outro') echo 'selected'; ?>>Outro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="denuncias.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Denúncias -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Lista de Denúncias
        </div>
        <div class="card-body">
            <?php if (empty($denuncias)): ?>
                <p class="text-muted text-center">Nenhuma denúncia encontrada.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Acompanhante</th>
                                <th>Tipo</th>
                                <th>Denunciante</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($denuncias as $denuncia): ?>
                                <tr>
                                    <td><?php echo $denuncia['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($denuncia['acompanhante_foto']): ?>
                                                <img src="../uploads/<?php echo $denuncia['acompanhante_foto']; ?>" 
                                                     class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($denuncia['acompanhante_nome']); ?></div>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($denuncia['cidade_nome']); ?>, <?php echo $denuncia['estado_uf']; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $tipo_labels = [
                                            'inapropriado' => '<span class="badge bg-warning">Inapropriado</span>',
                                            'fake' => '<span class="badge bg-danger">Fake</span>',
                                            'spam' => '<span class="badge bg-info">Spam</span>',
                                            'outro' => '<span class="badge bg-secondary">Outro</span>'
                                        ];
                                        echo $tipo_labels[$denuncia['tipo']] ?? '<span class="badge bg-secondary">N/A</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($denuncia['denunciante_nome']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($denuncia['denunciante_email']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $status_labels = [
                                            'pendente' => '<span class="badge bg-warning">Pendente</span>',
                                            'resolvida' => '<span class="badge bg-success">Resolvida</span>',
                                            'invalida' => '<span class="badge bg-danger">Inválida</span>'
                                        ];
                                        echo $status_labels[$denuncia['status']] ?? '<span class="badge bg-secondary">N/A</span>';
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($denuncia['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="verDenuncia(<?php echo $denuncia['id']; ?>)"
                                                title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($denuncia['status'] == 'pendente'): ?>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="resolverDenuncia(<?php echo $denuncia['id']; ?>)"
                                                    title="Marcar como Resolvida">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="invalidarDenuncia(<?php echo $denuncia['id']; ?>)"
                                                    title="Marcar como Inválida">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            
                                            <button class="btn btn-sm btn-outline-warning" 
                                                    onclick="bloquearAcompanhante(<?php echo $denuncia['id']; ?>, <?php echo $denuncia['acompanhante_id']; ?>, '<?php echo htmlspecialchars($denuncia['acompanhante_nome']); ?>')"
                                                    title="Bloquear Acompanhante">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- Modal Ver Denúncia -->
<div class="modal fade" id="verDenunciaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-flag"></i> Detalhes da Denúncia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="denunciaDetalhes">
                <!-- Conteúdo carregado via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Resolver Denúncia -->
<div class="modal fade" id="resolverDenunciaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check"></i> Resolver Denúncia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="marcar_resolvida">
                <input type="hidden" name="denuncia_id" id="resolver_denuncia_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="resolucao" class="form-label">Descrição da Resolução *</label>
                        <textarea class="form-control" id="resolucao" name="resolucao" rows="4" required 
                                  placeholder="Descreva como a denúncia foi resolvida..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Marcar como Resolvida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Invalidar Denúncia -->
<div class="modal fade" id="invalidarDenunciaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-times"></i> Invalidar Denúncia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="marcar_invalida">
                <input type="hidden" name="denuncia_id" id="invalidar_denuncia_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo da Invalidação *</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="4" required 
                                  placeholder="Descreva o motivo da invalidação..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Marcar como Inválida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bloquear Acompanhante -->
<div class="modal fade" id="bloquearAcompanhanteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-ban"></i> Bloquear Acompanhante
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="bloquear_acompanhante">
                <input type="hidden" name="denuncia_id" id="bloquear_denuncia_id">
                <input type="hidden" name="acompanhante_id" id="bloquear_acompanhante_id">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atenção:</strong> Esta ação irá bloquear a acompanhante e marcar a denúncia como resolvida.
                    </div>
                    <div class="mb-3">
                        <label for="bloquear_motivo" class="form-label">Motivo do Bloqueio *</label>
                        <textarea class="form-control" id="bloquear_motivo" name="motivo" rows="4" required 
                                  placeholder="Descreva o motivo do bloqueio..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-ban"></i> Bloquear e Resolver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function verDenuncia(id) {
    // Aqui você pode implementar uma chamada AJAX para buscar os detalhes da denúncia
    // Por enquanto, vamos apenas abrir o modal
    new bootstrap.Modal(document.getElementById('verDenunciaModal')).show();
}

function resolverDenuncia(id) {
    document.getElementById('resolver_denuncia_id').value = id;
    new bootstrap.Modal(document.getElementById('resolverDenunciaModal')).show();
}

function invalidarDenuncia(id) {
    document.getElementById('invalidar_denuncia_id').value = id;
    new bootstrap.Modal(document.getElementById('invalidarDenunciaModal')).show();
}

function bloquearAcompanhante(denunciaId, acompanhanteId, nome) {
    document.getElementById('bloquear_denuncia_id').value = denunciaId;
    document.getElementById('bloquear_acompanhante_id').value = acompanhanteId;
    
    // Atualizar título do modal com o nome da acompanhante
    document.querySelector('#bloquearAcompanhanteModal .modal-title').innerHTML = 
        '<i class="fas fa-ban"></i> Bloquear Acompanhante: ' + nome;
    
    new bootstrap.Modal(document.getElementById('bloquearAcompanhanteModal')).show();
}
</script>

<?php include '../includes/admin-footer.php'; ?> 