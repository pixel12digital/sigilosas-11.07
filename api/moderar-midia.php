<?php
require_once __DIR__ . '/../config/database.php';
session_name('sigilosas_admin_session');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_nivel'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$tipo = $_POST['tipo'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$acao = $_POST['acao'] ?? '';
if (!$id || !in_array($acao, ['aprovar', 'reprovar']) || !in_array($tipo, ['foto', 'documento', 'video_verificacao'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$db = getDB();
$status = $acao === 'aprovar' ? 'aprovado' : 'rejeitado';
$tabela = '';
switch ($tipo) {
    case 'foto':
        $tabela = 'fotos';
        break;
    case 'documento':
        $tabela = 'documentos_acompanhante';
        break;
    case 'video_verificacao':
        $tabela = 'videos_verificacao';
        break;
}
if (!$tabela) {
    echo json_encode(['success' => false, 'message' => 'Tipo de mídia inválido.']);
    exit;
}
try {
    $ok = $db->update($tabela, ['status' => $status], 'id = ?', [$id]);
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível atualizar o status.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
} 