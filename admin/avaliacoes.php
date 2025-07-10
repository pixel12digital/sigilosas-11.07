<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}
require_once '../config/database.php';
$pageTitle = 'Avaliações';
require_once '../includes/admin-header.php';

$db = getDB();

// Aprovar ou recusar avaliação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avaliacao_id'], $_POST['acao'])) {
    $id = (int)$_POST['avaliacao_id'];
    if ($_POST['acao'] === 'aprovar') {
        $db->update('avaliacoes', ['aprovado' => 1], 'id = ?', [$id]);
        $msg = 'Avaliação aprovada!';
    } elseif ($_POST['acao'] === 'recusar') {
        $db->update('avaliacoes', ['aprovado' => -1], 'id = ?', [$id]);
        $msg = 'Avaliação recusada!';
    }
}

// Buscar avaliações pendentes
$pendentes = $db->fetchAll('
    SELECT v.id, v.nota, v.comentario, v.nome, v.created_at, a.nome as acompanhante_nome, a.id as acompanhante_id
    FROM avaliacoes v
    JOIN acompanhantes a ON v.acompanhante_id = a.id
    WHERE v.aprovado = 0
    ORDER BY v.created_at DESC
');
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-star"></i> Avaliações Pendentes
                </h1>
                <p class="text-muted">Gerencie aqui as avaliações enviadas pelos visitantes do site.</p>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-star"></i> Avaliações Pendentes
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-success"> <?php echo htmlspecialchars($msg); ?> </div>
                    <?php endif; ?>
                    <?php if (empty($pendentes)): ?>
                        <div class="alert alert-info">Nenhuma avaliação pendente.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mt-3">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Acompanhante</th>
                                        <th>Nome</th>
                                        <th>Nota</th>
                                        <th>Comentário</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($pendentes as $av): ?>
                                    <tr>
                                        <td><?php echo $av['id']; ?></td>
                                        <td><a href="acompanhante-visualizar.php?id=<?php echo $av['acompanhante_id']; ?>" target="_blank"><?php echo htmlspecialchars($av['acompanhante_nome']); ?></a></td>
                                        <td><?php echo htmlspecialchars($av['nome']); ?></td>
                                        <td><span class="text-warning">★ <?php echo $av['nota']; ?></span></td>
                                        <td><?php echo nl2br(htmlspecialchars($av['comentario'])); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($av['created_at'])); ?></td>
                                        <td>
                                            <form method="post" style="display:inline-block">
                                                <input type="hidden" name="avaliacao_id" value="<?php echo $av['id']; ?>">
                                                <button type="submit" name="acao" value="aprovar" class="btn btn-success btn-sm" title="Aprovar"><i class="fas fa-check"></i></button>
                                                <button type="submit" name="acao" value="recusar" class="btn btn-danger btn-sm ms-1" title="Recusar"><i class="fas fa-times"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/admin-footer.php'; ?> 