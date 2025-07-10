<?php
// Endpoint de upload de vídeo de verificação
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

if (!isset($_FILES['video_verificacao']) || $_FILES['video_verificacao']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Nenhum vídeo enviado ou erro no upload.']);
    exit;
}

$file = $_FILES['video_verificacao'];
$allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
$allowed_extensions = ['mp4', 'webm', 'ogg'];
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use apenas MP4, WEBM ou OGG.']);
    exit;
}
if ($file['size'] > 50 * 1024 * 1024) { // 50MB
    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 50MB.']);
    exit;
}

$upload_dir = __DIR__ . '/../uploads/verificacao/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
$timestamp = time();
$random = bin2hex(random_bytes(8));
$filename = 'verificacao_' . $timestamp . '_' . $random . '.' . $file_extension;
$filepath = $upload_dir . $filename;

if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar arquivo.']);
    exit;
}

// Salvar no banco (opcional: remover vídeo anterior)
$db->query("DELETE FROM videos_verificacao WHERE acompanhante_id = ?", [$acompanhante_id]);
$video_id = $db->insert('videos_verificacao', [
    'acompanhante_id' => $acompanhante_id,
    'url' => $filename,
    'tamanho' => $file['size'],
    'formato' => $file_extension,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);

echo json_encode(['success' => true, 'message' => 'Vídeo enviado com sucesso!', 'filename' => $filename, 'video_id' => $video_id]); 