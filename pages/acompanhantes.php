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
$where = ["a.status = 'aprovado'"];
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
    LEFT JOIN acompanhantes a ON e.id = a.estado_id AND a.status = 'aprovado'
    GROUP BY e.id
    HAVING total_acompanhantes > 0
    ORDER BY total_acompanhantes DESC
");

$cidades = [];
if ($estado_id) {
    $cidades = $db->fetchAll("
        SELECT c.*, COUNT(a.id) as total_acompanhantes
        FROM cidades c
        LEFT JOIN acompanhantes a ON c.id = a.cidade_id AND a.status = 'aprovado'
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
                <form id="filtro-form" class="row g-3">
                    <div class="col-md-5">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="">Selecione o Estado</option>
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado['id']; ?>" <?php if ($estado_id == $estado['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($estado['nome']); ?> (<?php echo $estado['uf']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="cidade" class="form-label">Cidade</label>
                        <select name="cidade" id="cidade" class="form-select" required>
                            <option value="">Selecione a Cidade</option>
                            <?php foreach ($cidades as $cidade): ?>
                                <option value="<?php echo $cidade['id']; ?>" <?php if ($cidade_id == $cidade['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cidade['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Resultados -->
<section class="results-section py-4">
    <div class="container">
        <div id="acompanhantes-result" class="row g-4"></div>
    </div>
</section>

<script>
// Carregar cidades via AJAX ao selecionar estado
const estadoSelect = document.getElementById('estado');
const cidadeSelect = document.getElementById('cidade');

estadoSelect.addEventListener('change', function() {
    const estadoId = this.value;
    cidadeSelect.innerHTML = '<option value="">Carregando...</option>';
    if (estadoId) {
        fetch(`/Sigilosas-MySQL/api/cidades.php?estado_id=${estadoId}`)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Selecione a Cidade</option>';
                data.forEach(cidade => {
                    options += `<option value="${cidade.id}">${cidade.nome}</option>`;
                });
                cidadeSelect.innerHTML = options;
            });
    } else {
        cidadeSelect.innerHTML = '<option value="">Selecione a Cidade</option>';
    }
});

// Busca AJAX de acompanhantes
const form = document.getElementById('filtro-form');
const resultDiv = document.getElementById('acompanhantes-result');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    const estadoId = estadoSelect.value;
    const cidadeId = cidadeSelect.value;
    if (!estadoId || !cidadeId) return;
    resultDiv.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border"></div></div>';
    fetch(`/Sigilosas-MySQL/api/busca-acompanhantes.php?estado_id=${estadoId}&cidade_id=${cidadeId}`)
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                resultDiv.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">Nenhuma acompanhante encontrada.</p></div>';
                return;
            }
            let html = '';
            data.forEach(a => {
                html += `<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card acompanhante-card h-100 shadow-sm">
                        <div class="card-img-top position-relative">
                            ${a.foto ? `<img src="../uploads/${a.foto}" class="card-img-top" alt="${a.nome}" style="height: 250px; object-fit: cover;">` : `<div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 250px;"><i class="fas fa-user fa-3x text-white"></i></div>`}
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-1">${a.nome}</h5>
                            <p class="card-text text-muted small mb-2"><i class="fas fa-map-marker-alt"></i> ${a.cidade_nome}, ${a.estado_uf}</p>
                            ${a.idade ? `<p class="card-text small mb-2"><i class="fas fa-birthday-cake"></i> ${a.idade} anos</p>` : ''}
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="acompanhante.php?id=${a.id}" class="btn btn-primary btn-sm w-100"><i class="fas fa-eye"></i> Ver Perfil</a>
                        </div>
                    </div>
                </div>`;
            });
            resultDiv.innerHTML = html;
        });
});
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