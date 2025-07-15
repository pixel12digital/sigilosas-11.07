<?php
require_once __DIR__ . '/../config/database.php';
session_name('sigilosas_admin_session');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_nivel'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$video_id = isset($_POST['video_id']) ? (int)$_POST['video_id'] : 0;
$acao = $_POST['acao'] ?? '';
if (!$video_id || !in_array($acao, ['aprovar', 'reprovar'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$db = getDB();
$status = $acao === 'aprovar' ? 'aprovado' : 'rejeitado';
try {
    $ok = $db->update('videos_publicos', ['status' => $status], 'id = ?', [$video_id]);
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível atualizar o status.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
} 