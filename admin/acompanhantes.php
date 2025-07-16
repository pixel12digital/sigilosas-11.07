<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}

require_once '../config/database.php';
$pageTitle = 'Acompanhantes';
require_once '../includes/admin-header.php';

$db = getDB();

$acompanhantes = []; // inicializa para evitar warning

// Processar ações administrativas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $acompanhante_id = (int)($_POST['acompanhante_id'] ?? 0);
    
    if ($action === 'destaque' && isset($_POST['destaque'])) {
        $destaque = (int)$_POST['destaque'];
        $db->update('acompanhantes', ['destaque' => $destaque], 'id = ?', [$acompanhante_id]);
        header('Location: acompanhantes.php?success=1');
        exit;
    } elseif ($action === 'aprovar' && $acompanhante_id) {
        // Aprovar acompanhante e todas as mídias associadas (lógica unificada)
        $db->update('acompanhantes', ['status' => 'aprovado'], 'id = ?', [$acompanhante_id]);
        
        // Aprovar todas as fotos
        $db->query('UPDATE fotos SET aprovada = 1 WHERE acompanhante_id = ?', [$acompanhante_id]);
        
        // Aprovar todos os vídeos públicos
        $db->query('UPDATE videos_publicos SET status = "aprovado" WHERE acompanhante_id = ?', [$acompanhante_id]);
        
        // Verificar todos os documentos
        $db->query('UPDATE documentos_acompanhante SET verificado = 1 WHERE acompanhante_id = ?', [$acompanhante_id]);
        
        header('Location: acompanhantes.php?success=Acompanhante e todas as mídias aprovadas com sucesso');
        exit;
    } elseif ($action === 'bloquear' && $acompanhante_id) {
        $db->update('acompanhantes', ['status' => 'bloqueado'], 'id = ?', [$acompanhante_id]);
        header('Location: acompanhantes.php?success=Acompanhante bloqueada com sucesso');
        exit;
    }
}

// Filtros
$status_filter = $_GET['status'] ?? '';
if ($status_filter === 'ativo') {
    $status_filter = 'aprovado';
}
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if (!empty($status_filter)) {
    $where[] = "a.status = ?";
    $params[] = $status_filter;
}
if ($search) {
    $where[] = "(a.nome LIKE ? OR a.apelido LIKE ? OR a.email LIKE ? OR c.nome LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Buscar acompanhantes
$sql = "SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
        FROM acompanhantes a
        LEFT JOIN cidades c ON a.cidade_id = c.id
        LEFT JOIN estados e ON c.estado_id = e.id
        $whereClause
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$acompanhantes = $db->fetchAll($sql, $params) ?: [];

// Contar total
$countSql = "SELECT COUNT(*) as total FROM acompanhantes a
             LEFT JOIN cidades c ON a.cidade_id = c.id
             LEFT JOIN estados e ON c.estado_id = e.id
             $whereClause";
$total = $db->fetch($countSql, array_slice($params, 0, -2));
$totalPages = ceil($total['total'] / $limit);

// Mensagens de ação
$success = isset($_GET['success']) ? 'Destaque atualizado com sucesso!' : ($_GET['success'] ?? '');
$error = $_GET['error'] ?? '';
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">
                <i class="fas fa-users"></i> Acompanhantes
            </h1>
            <a href="acompanhante-editar.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Novo Acompanhante
            </a>
            <form class="d-flex" method="get" action="">
                <input type="text" name="search" class="form-control me-2" placeholder="Buscar por nome, email ou cidade" value="<?php echo htmlspecialchars($search); ?>">
                <select name="status" class="form-select me-2">
                    <option value="">Todos Status</option>
                    <option value="ativo" <?php if ($status_filter==='aprovado') echo 'selected'; ?>>Ativo</option>
                    <option value="pendente" <?php if ($status_filter==='pendente') echo 'selected'; ?>>Pendente</option>
                    <option value="bloqueado" <?php if ($status_filter==='bloqueado') echo 'selected'; ?>>Bloqueado</option>
                </select>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </form>
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

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-list"></i> Lista de Acompanhantes
        </div>
        <div class="card-body">
            <?php if (empty($acompanhantes)): ?>
                <p class="text-muted text-center">Nenhuma acompanhante encontrada.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Apelido</th>
                                <th>Cidade</th>
                                <th>Status</th>
                                <th>Destaque</th>
                                <th>Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($acompanhantes as $a): ?>
                                <tr>
                                    <td><?php echo $a['id']; ?></td>
                                    <td>
                                        <a href="acompanhante-editar.php?id=<?php echo $a['id']; ?>">
                                            <?php echo htmlspecialchars($a['nome']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($a['apelido'] ?? 'Não informado'); ?></td>
                                    <td><?php echo htmlspecialchars($a['cidade_nome'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $status = $a['status'];
                                        $status_map = [
                                            'pendente' => 'Pendente',
                                            'aprovado' => 'Aprovado',
                                            'bloqueado' => 'Bloqueado',
                                            'rejeitado' => 'Reprovado',
                                        ];
                                        $statusClass = '';
                                        switch ($status) {
                                            case 'aprovado':
                                                $statusClass = 'badge bg-success';
                                                break;
                                            case 'pendente':
                                                $statusClass = 'badge bg-warning';
                                                break;
                                            case 'bloqueado':
                                                $statusClass = 'badge bg-danger';
                                                break;
                                            case 'rejeitado':
                                                $statusClass = 'badge bg-secondary';
                                                break;
                                            default:
                                                $statusClass = 'badge bg-secondary';
                                        }
                                        ?>
                                        <span class="<?php echo $statusClass; ?>"><?php echo $status_map[$status] ?? 'Pendente'; ?></span>
                                    </td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="acompanhante_id" value="<?php echo $a['id']; ?>">
                                            <input type="hidden" name="action" value="destaque">
                                            <select name="destaque" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                <option value="0" <?php if(!$a['destaque']) echo 'selected'; ?>>Não</option>
                                                <option value="1" <?php if($a['destaque']) echo 'selected'; ?>>Sim</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($a['created_at'])); ?></td>
                                    <td>
                                        <a href="acompanhante-visualizar.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-primary" title="Visualizar"><i class="fas fa-edit"></i></a>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="aprovar">
                                            <input type="hidden" name="acompanhante_id" value="<?php echo $a['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Aprovar perfil e todas as mídias" onclick="return confirm('Aprovar este perfil e todas as mídias associadas?');">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="bloquear">
                                            <input type="hidden" name="acompanhante_id" value="<?php echo $a['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Bloquear" onclick="return confirm('Bloquear este perfil?');">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                        <a href="acompanhante-editar.php?id=<?php echo $a['id']; ?>&action=excluir" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Excluir este perfil? Esta ação não pode ser desfeita.');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Paginação -->
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
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?> 