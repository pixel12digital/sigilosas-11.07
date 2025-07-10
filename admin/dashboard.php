<?php
/**
 * Dashboard do Painel Administrativo - Versão Simplificada
 * Arquivo: admin/dashboard.php
 */

// Iniciar sessão específica para admin
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}

require_once '../config/database.php';
$pageTitle = 'Dashboard';
include '../includes/admin-header.php';

// Verificar se está logado como admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

$db = getDB();

// Buscar estatísticas
$stats = [];

try {
    // Total de acompanhantes
    $stats['total_acompanhantes'] = $db->fetch("SELECT COUNT(*) as total FROM acompanhantes")['total'];

    // Acompanhantes por status
    $stats['pendentes'] = $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE status = 'pendente'")['total'];
    $stats['aprovadas'] = $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE status = 'aprovado'")['total'];
    $stats['bloqueadas'] = $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE status = 'bloqueado'")['total'];

    // Total de cidades
    $stats['total_cidades'] = $db->fetch("SELECT COUNT(*) as total FROM cidades")['total'];

    // Total de admins
    $stats['total_admins'] = $db->fetch("SELECT COUNT(*) as total FROM admin WHERE ativo = 1")['total'];

    // Acompanhantes verificadas
    $stats['verificadas'] = $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE verificado = 1")['total'];

    // Acompanhantes em destaque
    $stats['destaque'] = $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE destaque = 1")['total'];

    // Últimas acompanhantes cadastradas
    $ultimas_acompanhantes = $db->fetchAll("
        SELECT a.*, c.nome as cidade_nome 
        FROM acompanhantes a 
        LEFT JOIN cidades c ON a.cidade_id = c.id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");

    // Cidades com mais acompanhantes
    $cidades_populares = $db->fetchAll("
        SELECT c.nome, COUNT(a.id) as total_acompanhantes
        FROM cidades c
        LEFT JOIN acompanhantes a ON c.id = a.cidade_id
        GROUP BY c.id, c.nome
        HAVING total_acompanhantes > 0
        ORDER BY total_acompanhantes DESC
        LIMIT 5
    ");

    // Buscar total de cidades únicas com acompanhantes
    $stats['cidades_com_acompanhantes'] = $db->fetch("SELECT COUNT(DISTINCT cidade_id) as total FROM acompanhantes WHERE cidade_id IS NOT NULL")['total'];
} catch (Exception $e) {
    $error_db = "Erro ao conectar com o banco: " . $e->getMessage();
}
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </h1>
                <p class="text-muted">Visão geral do sistema</p>
            </div>

            <?php if (isset($error_db)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_db; ?>
                </div>
            <?php endif; ?>

            <!-- Cards de Estatísticas -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6 mb-4"> <!-- Aumenta largura do primeiro card -->
                    <div class="card border-left-primary py-2">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-auto w-100">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1 d-flex align-items-center justify-content-between" style="gap: 8px;">
                                        <span>Total de Acompanhantes</span>
                                        <i class="fas fa-users fa-lg text-gray-300"></i>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($stats['total_acompanhantes'] ?? 0); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-success py-2">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-auto">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Aprovadas
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($stats['aprovadas'] ?? 0); ?>
                                    </div>
                                </div>
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-warning py-2">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-auto">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pendentes
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($stats['pendentes'] ?? 0); ?>
                                    </div>
                                </div>
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Removido o card de cidades -->
            </div>

            <!-- Menu de Navegação -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-cogs"></i> Gerenciamento
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Remover o botão Aprovar Acompanhantes e ajustar grid para ocupar uma linha -->
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="acompanhantes.php" class="btn btn-secondary w-100">
                                        <i class="fas fa-users"></i> Gerenciar Acompanhantes
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="cidades-com-acompanhantes.php" class="btn btn-info w-100">
                                        <i class="fas fa-map-marked-alt"></i> Cidades com Acompanhantes
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="denuncias.php" class="btn btn-warning w-100">
                                        <i class="fas fa-exclamation-triangle"></i> Denúncias
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="avaliacoes.php" class="btn btn-secondary w-100">
                                        <i class="fas fa-star"></i> Avaliações
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimas Acompanhantes -->
            <?php if (!empty($ultimas_acompanhantes)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-clock"></i> Últimas Acompanhantes Cadastradas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Cidade</th>
                                            <th>Status</th>
                                            <th>Data</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (
                                            $ultimas_acompanhantes as $acomp): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($acomp['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($acomp['cidade_nome'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php
                                                $status = $acomp['status'];
                                                $status_map = [
                                                    'pendente' => 'Pendente',
                                                    'aprovado' => 'Aprovada',
                                                    'bloqueado' => 'Bloqueada',
                                                    'rejeitado' => 'Reprovada',
                                                ];
                                                echo $status_map[$status] ?? 'Pendente';
                                                ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($acomp['created_at'])); ?></td>
                                            <td>
                                                <a href="acompanhante-visualizar.php?id=<?php echo $acomp['id']; ?>" class="btn btn-sm btn-primary" title="Visualizar Perfil"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?> 