<?php
/**
 * API Upload de Documento
 * Arquivo: api/upload-documento.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_acompanhante_session');
    session_start();
}
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Verificar se está logada
if (!isset($_SESSION['acompanhante_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$db = getDB();
$acompanhante_id = $_SESSION['acompanhante_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$successUploads = [];
$errors = [];

$tipos = [
    'documento_frente' => 'rg',
    'documento_verso' => 'rg'
];

foreach ($tipos as $inputName => $tipoDoc) {
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        continue; // Não enviado, pula
    }
    $file = $_FILES[$inputName];
    // Validações
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
        $errors[] = 'Tipo de arquivo não permitido para ' . $tipoDoc . '. Use apenas JPG ou PNG.';
        continue;
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        $errors[] = 'Arquivo muito grande para ' . $tipoDoc . '. Máximo 10MB.';
        continue;
    }
    // Criar diretório se não existir
    $upload_dir = __DIR__ . '/../uploads/documentos/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    // Gerar nome único para o arquivo
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    $filename = $inputName === 'documento_frente'
        ? 'rg_frente_' . $timestamp . '_' . $random . '.' . $file_extension
        : 'rg_verso_' . $timestamp . '_' . $random . '.' . $file_extension;
    $filepath = $upload_dir . $filename;
    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        $errors[] = 'Erro ao salvar arquivo para ' . $tipoDoc;
        continue;
    }
    // Salvar no banco
    $db->insert('documentos_acompanhante', [
        'acompanhante_id' => $acompanhante_id,
        'tipo' => $tipoDoc, // sempre 'rg'
        'url' => $filename,
        'storage_path' => $filename,
        'tamanho' => $file['size'],
        'formato' => $file_extension,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    $successUploads[] = $tipoDoc;
}

if ($successUploads) {
    echo json_encode(['success' => true, 'message' => 'Documento(s) enviado(s) com sucesso!', 'tipos' => $successUploads]);
} else {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
}
?> 