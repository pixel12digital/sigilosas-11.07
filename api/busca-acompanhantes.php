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
    foreach ($acompanhantes as &$a) {
        $foto_perfil = $db->fetch("SELECT url FROM fotos WHERE acompanhante_id = ? AND tipo = 'perfil' AND aprovada = 1 ORDER BY id DESC LIMIT 1", [$a['id']]);
        $a['foto'] = $foto_perfil['url'] ?? null;
    }
    echo json_encode($acompanhantes);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro na query: ' . $e->getMessage()]);
    exit;
} 