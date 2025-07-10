<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

if (empty($_POST['video_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do vídeo não informado.']);
    exit;
}

$video_id = (int)$_POST['video_id'];
$db = getDB();
$video = $db->fetch("SELECT * FROM videos_verificacao WHERE id = ?", [$video_id]);

if (!$video) {
    echo json_encode(['success' => false, 'message' => 'Vídeo não encontrado.']);
    exit;
}

$file_path = __DIR__ . '/../uploads/verificacao/' . $video['url'];
if (file_exists($file_path)) {
    @unlink($file_path);
}

$db->delete('videos_verificacao', 'id = ?', [$video_id]);

echo json_encode(['success' => true]); 