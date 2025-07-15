<?php
// Endpoint de upload de fotos da galeria
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

if (!isset($_FILES['fotos_galeria'])) {
    echo json_encode(['success' => false, 'message' => 'Nenhuma foto enviada.']);
    exit;
}

$files = $_FILES['fotos_galeria'];
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$max_size = 5 * 1024 * 1024; // 5MB
$upload_dir = __DIR__ . '/../uploads/galeria/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
$success_count = 0;
$success_files = [];
$errors = [];
for ($i = 0; $i < count($files['name']); $i++) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erro no upload da foto ' . ($i+1);
        continue;
    }
    $file_extension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
    if (!in_array($files['type'][$i], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
        $errors[] = 'Tipo de arquivo não permitido na foto ' . ($i+1);
        continue;
    }
    if ($files['size'][$i] > $max_size) {
        $errors[] = 'Arquivo muito grande na foto ' . ($i+1) . '. Máximo 5MB.';
        continue;
    }
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    $filename = 'galeria_' . $timestamp . '_' . $random . '.' . $file_extension;
    $filepath = $upload_dir . $filename;
    if (!move_uploaded_file($files['tmp_name'][$i], $filepath)) {
        $errors[] = 'Erro ao salvar a foto ' . ($i+1);
        continue;
    }
    $db->insert('fotos', [
        'acompanhante_id' => $acompanhante_id,
        'url' => $filename,
        'tipo' => 'galeria',
        'tamanho' => $files['size'][$i],
        'formato' => $file_extension,
        'status' => 'pendente',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    $success_count++;
    $success_files[] = $filename;
}
if ($success_count > 0) {
    echo json_encode([
        'success' => true,
        'message' => "$success_count foto(s) enviada(s) com sucesso!" . (count($errors) ? ' Alguns erros: ' . implode(' ', $errors) : ''),
        'filenames' => $success_files
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Nenhuma foto enviada. ' . implode(' ', $errors)]);
} 