<?php
// Endpoint para excluir foto da galeria
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['foto_id'])) {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit;
}
$foto_id = (int)$_POST['foto_id'];
$foto = $db->fetch("SELECT * FROM fotos WHERE id = ? AND acompanhante_id = ? AND tipo = 'galeria'", [$foto_id, $acompanhante_id]);
if (!$foto) {
    echo json_encode(['success' => false, 'message' => 'Foto não encontrada.']);
    exit;
}
$filepath = __DIR__ . '/../uploads/galeria/' . $foto['url'];
if (file_exists($filepath)) {
    @unlink($filepath);
}
$db->delete('fotos', 'id = ? AND acompanhante_id = ? AND tipo = ?', [$foto_id, $acompanhante_id, 'galeria']);
echo json_encode(['success' => true, 'message' => 'Foto excluída com sucesso.']); 