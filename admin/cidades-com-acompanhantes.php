<?php
// Iniciar sessão admin padronizada
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}
require_once '../config/database.php';
$pageTitle = 'Cidades com Acompanhantes';
require_once '../includes/admin-header.php';

$db = getDB();

// Buscar cidades únicas com acompanhantes e o número de acompanhantes por cidade
$cidades = $db->fetchAll('
    SELECT c.id, c.nome as cidade_nome, e.nome as estado_nome, e.uf as estado_uf, COUNT(a.id) as total_acompanhantes
    FROM cidades c
    INNER JOIN acompanhantes a ON a.cidade_id = c.id
    INNER JOIN estados e ON c.estado_id = e.id
    GROUP BY c.id, c.nome, e.nome, e.uf
    ORDER BY total_acompanhantes DESC, c.nome ASC
');
?>
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-map-marker-alt"></i> Cidades com Acompanhantes</h2>
    <div class="card">
        <div class="card-header">
            <strong>Lista de cidades que possuem acompanhantes cadastradas</strong>
        </div>
        <div class="card-body">
            <?php if (empty($cidades)): ?>
                <p class="text-muted">Nenhuma cidade com acompanhantes cadastradas.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cidade</th>
                                <th>Estado</th>
                                <th>UF</th>
                                <th>Total de Acompanhantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cidades as $i => $cidade): ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo htmlspecialchars($cidade['cidade_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($cidade['estado_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($cidade['estado_uf']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $cidade['total_acompanhantes']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/admin-footer.php'; ?> 