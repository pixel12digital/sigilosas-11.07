<?php
/**
 * Estatísticas e Relatórios - Painel Admin
 * Arquivo: admin/estatisticas.php
 */

require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Estatísticas';
include '../includes/admin-header.php';

$db = getDB();

// Filtros de período
$periodo = $_GET['periodo'] ?? '30'; // dias
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime("-{$periodo} days"));
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');

// Estatísticas gerais
$stats = [
    'total_acompanhantes' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes")['total'],
    'acompanhantes_aprovadas' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE aprovada = 1")['total'],
    'acompanhantes_pendentes' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE aprovada = 0")['total'],
    'acompanhantes_bloqueadas' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE bloqueada = 1")['total'],
    'total_cidades' => $db->fetch("SELECT COUNT(*) as total FROM cidades")['total'],
    'total_estados' => $db->fetch("SELECT COUNT(*) as total FROM estados")['total']
];

// Novos cadastros no período
$novos_cadastros = $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE DATE(created_at) BETWEEN ? AND ?", [$data_inicio, $data_fim])['total'];

// Top cidades com mais acompanhantes
$top_cidades = $db->fetchAll("
    SELECT c.nome as cidade, e.nome as estado, e.uf, COUNT(a.id) as total
    FROM cidades c
    LEFT JOIN estados e ON c.estado_id = e.id
    LEFT JOIN acompanhantes a ON c.id = a.cidade_id AND a.aprovada = 1
    GROUP BY c.id
    HAVING total > 0
    ORDER BY total DESC
    LIMIT 10
");

// Top estados com mais acompanhantes
$top_estados = $db->fetchAll("
    SELECT e.nome as estado, e.uf, COUNT(a.id) as total
    FROM estados e
    LEFT JOIN acompanhantes a ON e.id = a.estado_id AND a.aprovada = 1
    GROUP BY e.id
    HAVING total > 0
    ORDER BY total DESC
    LIMIT 10
");

// Cadastros por mês (últimos 12 meses)
$cadastros_por_mes = $db->fetchAll("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(*) as total
    FROM acompanhantes
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY mes ASC
");

// Cadastros por dia (últimos 30 dias)
$cadastros_por_dia = $db->fetchAll("
    SELECT DATE(created_at) as dia, COUNT(*) as total
    FROM acompanhantes
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY dia ASC
");

// Acompanhantes por faixa etária
$faixas_etarias = $db->fetchAll("
    SELECT 
        CASE 
            WHEN idade < 20 THEN '18-19 anos'
            WHEN idade BETWEEN 20 AND 25 THEN '20-25 anos'
            WHEN idade BETWEEN 26 AND 30 THEN '26-30 anos'
            WHEN idade BETWEEN 31 AND 35 THEN '31-35 anos'
            WHEN idade BETWEEN 36 AND 40 THEN '36-40 anos'
            WHEN idade > 40 THEN '40+ anos'
        END as faixa,
        COUNT(*) as total
    FROM acompanhantes
    WHERE aprovada = 1 AND idade IS NOT NULL
    GROUP BY faixa
    ORDER BY 
        CASE faixa
            WHEN '18-19 anos' THEN 1
            WHEN '20-25 anos' THEN 2
            WHEN '26-30 anos' THEN 3
            WHEN '31-35 anos' THEN 4
            WHEN '36-40 anos' THEN 5
            WHEN '40+ anos' THEN 6
        END
");

// Acompanhantes por tipo de serviço
$tipos_servico = $db->fetchAll("
    SELECT 
        CASE 
            WHEN tipo_servico = 'massagem' THEN 'Massagem'
            WHEN tipo_servico = 'acompanhante' THEN 'Acompanhante'
            WHEN tipo_servico = 'ambos' THEN 'Ambos'
            ELSE 'Não informado'
        END as tipo,
        COUNT(*) as total
    FROM acompanhantes
    WHERE aprovada = 1
    GROUP BY tipo_servico
    ORDER BY total DESC
");

// Preparar dados para gráficos
$meses_labels = [];
$meses_dados = [];
foreach ($cadastros_por_mes as $item) {
    $meses_labels[] = date('M/Y', strtotime($item['mes'] . '-01'));
    $meses_dados[] = $item['total'];
}

$dias_labels = [];
$dias_dados = [];
foreach ($cadastros_por_dia as $item) {
    $dias_labels[] = date('d/m', strtotime($item['dia']));
    $dias_dados[] = $item['total'];
}

$faixas_labels = [];
$faixas_dados = [];
foreach ($faixas_etarias as $item) {
    $faixas_labels[] = $item['faixa'];
    $faixas_dados[] = $item['total'];
}

$tipos_labels = [];
$tipos_dados = [];
foreach ($tipos_servico as $item) {
    $tipos_labels[] = $item['tipo'];
    $tipos_dados[] = $item['total'];
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">
                <i class="fas fa-chart-bar"></i> Estatísticas e Relatórios
            </h1>
            <div class="d-flex gap-2">
                <form method="get" class="d-flex gap-2">
                    <select name="periodo" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="7" <?php if ($periodo == '7') echo 'selected'; ?>>Últimos 7 dias</option>
                        <option value="30" <?php if ($periodo == '30') echo 'selected'; ?>>Últimos 30 dias</option>
                        <option value="90" <?php if ($periodo == '90') echo 'selected'; ?>>Últimos 90 dias</option>
                        <option value="365" <?php if ($periodo == '365') echo 'selected'; ?>>Último ano</option>
                    </select>
                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?php echo $data_inicio; ?>" onchange="this.form.submit()">
                    <input type="date" name="data_fim" class="form-control form-control-sm" value="<?php echo $data_fim; ?>" onchange="this.form.submit()">
                </form>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total de Acompanhantes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_acompanhantes']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Aprovadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['acompanhantes_aprovadas']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['acompanhantes_pendentes']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Novos Cadastros (<?php echo $periodo; ?>d)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($novos_cadastros); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plus-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <!-- Cadastros por Mês -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cadastros por Mês (Últimos 12 meses)</h6>
                </div>
                <div class="card-body">
                    <canvas id="cadastrosMensais" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Faixa Etária -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Faixa Etária</h6>
                </div>
                <div class="card-body">
                    <canvas id="faixaEtaria" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Cadastros por Dia -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cadastros por Dia (Últimos 30 dias)</h6>
                </div>
                <div class="card-body">
                    <canvas id="cadastrosDiarios" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Tipos de Serviço -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tipos de Serviço</h6>
                </div>
                <div class="card-body">
                    <canvas id="tiposServico" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas de Ranking -->
    <div class="row">
        <!-- Top Cidades -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Cidades</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cidade</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_cidades as $index => $cidade): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($cidade['cidade']); ?></td>
                                        <td><?php echo htmlspecialchars($cidade['estado']); ?> (<?php echo $cidade['uf']; ?>)</td>
                                        <td><span class="badge bg-primary"><?php echo $cidade['total']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Estados -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Estados</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_estados as $index => $estado): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($estado['estado']); ?> (<?php echo $estado['uf']; ?>)</td>
                                        <td><span class="badge bg-success"><?php echo $estado['total']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de Cadastros Mensais
const ctxMensais = document.getElementById('cadastrosMensais').getContext('2d');
new Chart(ctxMensais, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($meses_labels); ?>,
        datasets: [{
            label: 'Cadastros',
            data: <?php echo json_encode($meses_dados); ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gráfico de Faixa Etária
const ctxEtaria = document.getElementById('faixaEtaria').getContext('2d');
new Chart(ctxEtaria, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($faixas_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($faixas_dados); ?>,
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
            ]
        }]
    },
    options: {
        responsive: true
    }
});

// Gráfico de Cadastros Diários
const ctxDiarios = document.getElementById('cadastrosDiarios').getContext('2d');
new Chart(ctxDiarios, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($dias_labels); ?>,
        datasets: [{
            label: 'Cadastros',
            data: <?php echo json_encode($dias_dados); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgb(54, 162, 235)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gráfico de Tipos de Serviço
const ctxServico = document.getElementById('tiposServico').getContext('2d');
new Chart(ctxServico, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($tipos_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($tipos_dados); ?>,
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0'
            ]
        }]
    },
    options: {
        responsive: true
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?> 