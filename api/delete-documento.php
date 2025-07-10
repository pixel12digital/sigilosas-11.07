<?php
// Endpoint para excluir documento de identidade (frente/verso)
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_acompanhante_session');
    session_start();
}
require_once __DIR__ . '/../config/database.php';
$db = getDB();

if (!isset($_SESSION['acompanhante_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}
$acompanhante_id = $_SESSION['acompanhante_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['documento_id'])) {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit;
}
$documento_id = $_POST['documento_id'];

$doc = $db->fetch("SELECT * FROM documentos_acompanhante WHERE id = ? AND acompanhante_id = ?", [$documento_id, $acompanhante_id]);
if (!$doc) {
    echo json_encode(['success' => false, 'message' => 'Documento não encontrado.']);
    exit;
}
$filepath = __DIR__ . '/../uploads/documentos/' . $doc['url'];
if (file_exists($filepath)) {
    @unlink($filepath);
}
$db->delete('documentos_acompanhante', 'id = ? AND acompanhante_id = ?', [$documento_id, $acompanhante_id]);
echo json_encode(['success' => true, 'message' => 'Documento excluído com sucesso.']); 