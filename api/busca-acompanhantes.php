<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$estado_id = $_GET['estado_id'] ?? null;
$cidade_id = $_GET['cidade_id'] ?? null;

if (!$estado_id || !$cidade_id) {
    echo json_encode(['error' => 'Parâmetros obrigatórios não informados.']);
    exit;
}

try {
    $db = getDB();
    $sql = "SELECT a.id, a.nome, a.apelido, a.idade, a.valor_padrao, a.valor_promocional, a.bairro, a.sobre_mim, a.local_atendimento, c.nome as cidade_nome, e.uf as estado_uf,
                   (SELECT f.url FROM fotos f WHERE f.acompanhante_id = a.id AND f.tipo = 'perfil' ORDER BY f.id ASC LIMIT 1) as foto
            FROM acompanhantes a
            LEFT JOIN cidades c ON a.cidade_id = c.id
            LEFT JOIN estados e ON c.estado_id = e.id
            WHERE a.status = 'aprovado' AND c.id = ? AND e.id = ?
            ORDER BY a.verificado DESC, a.created_at DESC";
    $acompanhantes = $db->fetchAll($sql, [$cidade_id, $estado_id]);
    // Buscar valores de atendimento para cada acompanhante
    foreach ($acompanhantes as &$ac) {
        $ac['valores_atendimento'] = $db->fetchAll("SELECT tempo, valor, disponivel FROM valores_atendimento WHERE acompanhante_id = ? AND disponivel = 1 ORDER BY FIELD(tempo, '15min','30min','1h','2h','4h','diaria','pernoite','diaria_viagem')", [$ac['id']]);
    }
    echo json_encode($acompanhantes);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro na query: ' . $e->getMessage()]);
    exit;
} 