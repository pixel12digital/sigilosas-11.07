<?php
/**
 * Listagem de Acompanhantes - Site Público
 * Arquivo: pages/acompanhantes.php
 */

require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Acompanhantes - Sigilosas';
$pageDescription = 'Encontre acompanhantes de luxo em sua cidade. Filtros avançados e perfis verificados.';
include '../includes/header.php';

$db = getDB();

// Filtros
$estado_id = $_GET['estado'] ?? '';
$cidade_id = $_GET['cidade'] ?? '';
$tipo_servico = $_GET['tipo_servico'] ?? '';
$idade_min = $_GET['idade_min'] ?? '';
$idade_max = $_GET['idade_max'] ?? '';
$verificada = $_GET['verificada'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Construir query com filtros
$where = ["a.status = 'aprovado'", "a.bloqueado = 0"];
$params = [];

if ($estado_id) {
    $where[] = "e.id = ?";
    $params[] = $estado_id;
}

if ($cidade_id) {
    $where[] = "c.id = ?";
    $params[] = $cidade_id;
}

if ($tipo_servico) {
    $where[] = "a.tipo_servico = ?";
    $params[] = $tipo_servico;
}

if ($idade_min) {
    $where[] = "a.idade >= ?";
    $params[] = $idade_min;
}

if ($idade_max) {
    $where[] = "a.idade <= ?";
    $params[] = $idade_max;
}

if ($verificada === '1') {
    $where[] = "a.verificado = 1";
}

if ($search) {
    $where[] = "(a.apelido LIKE ? OR a.nome LIKE ? OR a.descricao LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Buscar acompanhantes
$sql = "SELECT a.*, c.nome as cidade_nome, e.nome as estado_nome, e.uf as estado_uf,
               (SELECT COUNT(*) FROM fotos f WHERE f.acompanhante_id = a.id) as total_fotos
        FROM acompanhantes a
        LEFT JOIN cidades c ON a.cidade_id = c.id
        LEFT JOIN estados e ON c.estado_id = e.id
        $whereClause
        ORDER BY a.verificado DESC, a.created_at DESC
        LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$acompanhantes = $db->fetchAll($sql, $params);

// Contar total
$countSql = "SELECT COUNT(*) as total FROM acompanhantes a
             LEFT JOIN cidades c ON a.cidade_id = c.id
             LEFT JOIN estados e ON c.estado_id = e.id
             $whereClause";
$total = $db->fetch($countSql, array_slice($params, 0, -2));
$totalPages = ceil($total['total'] / $limit);

// Buscar estados e cidades para filtros
$estados = $db->fetchAll("
    SELECT e.*, COUNT(a.id) as total_acompanhantes
    FROM estados e
    LEFT JOIN acompanhantes a ON e.id = a.estado_id AND a.status = 'aprovado' AND a.bloqueado = 0
    GROUP BY e.id
    HAVING total_acompanhantes > 0
    ORDER BY total_acompanhantes DESC
");

$cidades = [];
if ($estado_id) {
    $cidades = $db->fetchAll("
        SELECT c.*, COUNT(a.id) as total_acompanhantes
        FROM cidades c
        LEFT JOIN acompanhantes a ON c.id = a.cidade_id AND a.status = 'aprovado' AND a.bloqueado = 0
        WHERE c.estado_id = ?
        GROUP BY c.id
        HAVING total_acompanhantes > 0
        ORDER BY total_acompanhantes DESC
    ", [$estado_id]);
}
?>

<!-- Header da Página -->
<section class="page-header py-5 bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-5 fw-bold mb-3">Acompanhantes</h1>
                <p class="lead">Encontre a companhia perfeita para momentos especiais</p>
            </div>
        </div>
    </div>
</section>

<!-- Filtros -->
<section class="filters-section py-4 bg-light">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos os Estados</option>
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado['id']; ?>" <?php if ($estado_id == $estado['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($estado['nome']); ?> (<?php echo $estado['uf']; ?>) 
                                    - <?php echo $estado['total_acompanhantes']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="cidade" class="form-label">Cidade</label>
                        <select name="cidade" id="cidade" class="form-select" onchange="this.form.submit()">
                            <option value="">Todas as Cidades</option>
                            <?php foreach ($cidades as $cidade): ?>
                                <option value="<?php echo $cidade['id']; ?>" <?php if ($cidade_id == $cidade['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cidade['nome']); ?> 
                                    - <?php echo $cidade['total_acompanhantes']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="tipo_servico" class="form-label">Tipo de Serviço</label>
                        <select name="tipo_servico" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="massagem" <?php if ($tipo_servico == 'massagem') echo 'selected'; ?>>Massagem</option>
                            <option value="acompanhante" <?php if ($tipo_servico == 'acompanhante') echo 'selected'; ?>>Acompanhante</option>
                            <option value="ambos" <?php if ($tipo_servico == 'ambos') echo 'selected'; ?>>Ambos</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="idade_min" class="form-label">Idade Mín.</label>
                        <input type="number" name="idade_min" id="idade_min" class="form-control" 
                               value="<?php echo htmlspecialchars($idade_min); ?>" min="18" max="80" 
                               onchange="this.form.submit()">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="idade_max" class="form-label">Idade Máx.</label>
                        <input type="number" name="idade_max" id="idade_max" class="form-control" 
                               value="<?php echo htmlspecialchars($idade_max); ?>" min="18" max="80" 
                               onchange="this.form.submit()">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nome ou descrição...">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="verificada" class="form-label">Verificada</label>
                        <select name="verificada" class="form-select" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            <option value="1" <?php if ($verificada === '1') echo 'selected'; ?>>Apenas Verificadas</option>
                        </select>
                    </div>
                    
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <a href="acompanhantes.php" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Resultados -->
<section class="results-section py-5">
    <div class="container">
        <!-- Informações dos Resultados -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <?php echo number_format($total['total']); ?> acompanhante<?php echo $total['total'] != 1 ? 's' : ''; ?> encontrada<?php echo $total['total'] != 1 ? 's' : ''; ?>
                </h5>
                <?php if ($total['total'] > 0): ?>
                    <small class="text-muted">
                        Página <?php echo $page; ?> de <?php echo $totalPages; ?>
                    </small>
                <?php endif; ?>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" onclick="setView('grid')">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="setView('list')">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Lista de Acompanhantes -->
        <?php if (empty($acompanhantes)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Nenhuma acompanhante encontrada</h4>
                <p class="text-muted">Tente ajustar os filtros de busca</p>
                <a href="acompanhantes.php" class="btn btn-primary">
                    <i class="fas fa-times"></i> Limpar Filtros
                </a>
            </div>
        <?php else: ?>
            <!-- Grid View -->
            <div id="grid-view" class="view-mode">
                <div class="row">
                    <?php foreach ($acompanhantes as $acompanhante): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card acompanhante-card h-100 shadow-sm">
                                <div class="card-img-top position-relative">
                                    <?php if ($acompanhante['foto']): ?>
                                        <img src="../uploads/<?php echo $acompanhante['foto']; ?>" 
                                             class="card-img-top" alt="<?php echo htmlspecialchars($acompanhante['nome']); ?>"
                                             style="height: 250px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary d-flex align-items-center justify-content-center" 
                                             style="height: 250px;">
                                            <i class="fas fa-user fa-3x text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Badges -->
                                    <div class="position-absolute top-0 start-0 p-2">
                                        <?php if ($acompanhante['verificado']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Verificada
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($acompanhante['total_fotos'] > 0): ?>
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <span class="badge bg-info">
                                                <i class="fas fa-images"></i> <?php echo $acompanhante['total_fotos']; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($acompanhante['apelido'] ?? $acompanhante['nome']); ?></h5>
                                    <p class="card-text text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($acompanhante['cidade_nome']); ?>, <?php echo $acompanhante['estado_uf']; ?>
                                    </p>
                                    
                                    <?php if ($acompanhante['idade']): ?>
                                        <p class="card-text small mb-2">
                                            <i class="fas fa-birthday-cake"></i> <?php echo $acompanhante['idade']; ?> anos
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($acompanhante['tipo_servico']): ?>
                                        <p class="card-text small mb-3">
                                            <i class="fas fa-tag"></i> 
                                            <?php
                                            $tipos = [
                                                'massagem' => 'Massagem',
                                                'acompanhante' => 'Acompanhante',
                                                'ambos' => 'Massagem & Acompanhante'
                                            ];
                                            echo $tipos[$acompanhante['tipo_servico']] ?? 'Serviços Diversos';
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($acompanhante['descricao']): ?>
                                        <p class="card-text small text-muted">
                                            <?php echo htmlspecialchars(substr($acompanhante['descricao'], 0, 100)); ?>
                                            <?php if (strlen($acompanhante['descricao']) > 100) echo '...'; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-footer bg-transparent border-0">
                                    <a href="acompanhante.php?id=<?php echo $acompanhante['id']; ?>" 
                                       class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-eye"></i> Ver Perfil
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- List View -->
            <div id="list-view" class="view-mode" style="display: none;">
                <div class="row">
                    <?php foreach ($acompanhantes as $acompanhante): ?>
                        <div class="col-12 mb-3">
                            <div class="card">
                                <div class="row g-0">
                                    <div class="col-md-3">
                                        <?php if ($acompanhante['foto']): ?>
                                            <img src="../uploads/<?php echo $acompanhante['foto']; ?>" 
                                                 class="img-fluid rounded-start h-100" 
                                                 style="object-fit: cover; height: 200px;"
                                                 alt="<?php echo htmlspecialchars($acompanhante['nome']); ?>">
                                        <?php else: ?>
                                            <div class="bg-secondary d-flex align-items-center justify-content-center h-100" 
                                                 style="height: 200px;">
                                                <i class="fas fa-user fa-3x text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="card-title mb-1">
                                                        <?php echo htmlspecialchars($acompanhante['apelido'] ?? $acompanhante['nome']); ?>
                                                        <?php if ($acompanhante['verificado']): ?>
                                                            <span class="badge bg-success ms-2">
                                                                <i class="fas fa-check-circle"></i> Verificada
                                                            </span>
                                                        <?php endif; ?>
                                                    </h5>
                                                    <p class="card-text text-muted mb-2">
                                                        <i class="fas fa-map-marker-alt"></i> 
                                                        <?php echo htmlspecialchars($acompanhante['cidade_nome']); ?>, <?php echo $acompanhante['estado_uf']; ?>
                                                        <?php if ($acompanhante['idade']): ?>
                                                            • <i class="fas fa-birthday-cake"></i> <?php echo $acompanhante['idade']; ?> anos
                                                        <?php endif; ?>
                                                    </p>
                                                    
                                                    <?php if ($acompanhante['tipo_servico']): ?>
                                                        <p class="card-text mb-2">
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
                                                    
                                                    <?php if ($acompanhante['descricao']): ?>
                                                        <p class="card-text">
                                                            <?php echo htmlspecialchars(substr($acompanhante['descricao'], 0, 200)); ?>
                                                            <?php if (strlen($acompanhante['descricao']) > 200) echo '...'; ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-end">
                                                    <?php if ($acompanhante['total_fotos'] > 0): ?>
                                                        <span class="badge bg-info mb-2">
                                                            <i class="fas fa-images"></i> <?php echo $acompanhante['total_fotos']; ?> fotos
                                                        </span>
                                                    <?php endif; ?>
                                                    <br>
                                                    <a href="acompanhante.php?id=<?php echo $acompanhante['id']; ?>" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-eye"></i> Ver Perfil
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    <i class="fas fa-chevron-left"></i> Anterior
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <li class="page-item <?php if ($i === $page) echo 'active'; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    Próxima <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<script>
// Carregar cidades quando estado for selecionado
document.getElementById('estado').addEventListener('change', function() {
    const estadoId = this.value;
    const cidadeSelect = document.getElementById('cidade');
    
    if (estadoId) {
        // Fazer requisição AJAX para buscar cidades
        fetch(`../api/cidades.php?estado_id=${estadoId}`)
            .then(response => response.json())
            .then(data => {
                cidadeSelect.innerHTML = '<option value="">Todas as Cidades</option>';
                
                if (data.success && data.cidades) {
                    data.cidades.forEach(cidade => {
                        const option = document.createElement('option');
                        option.value = cidade.id;
                        option.textContent = cidade.nome + ' - ' + cidade.total_acompanhantes;
                        cidadeSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao carregar cidades:', error);
            });
    } else {
        cidadeSelect.innerHTML = '<option value="">Todas as Cidades</option>';
    }
});

// Alternar entre grid e list view
function setView(view) {
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const buttons = document.querySelectorAll('.btn-group .btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    if (view === 'grid') {
        gridView.style.display = 'block';
        listView.style.display = 'none';
    } else {
        gridView.style.display = 'none';
        listView.style.display = 'block';
    }
}
</script>

<style>
.acompanhante-card {
    transition: transform 0.2s ease-in-out;
}

.acompanhante-card:hover {
    transform: translateY(-5px);
}

.page-header {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.filters-section .card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-group .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}
</style>

<?php include '../includes/footer.php'; ?> 