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
$isAdmin = isset($_SESSION['admin_id']);
$isAcompanhante = isset($_SESSION['acompanhante_id']);

if (!$isAdmin && !$isAcompanhante) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Determinar o acompanhante_id
if ($isAdmin) {
    if (empty($_POST['acompanhante_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID do acompanhante não informado.']);
        exit;
    }
    $acompanhante_id = (int)$_POST['acompanhante_id'];
    // Validar se o acompanhante existe
    $acompanhante = $db->fetch("SELECT id FROM acompanhantes WHERE id = ?", [$acompanhante_id]);
    if (!$acompanhante) {
        echo json_encode(['success' => false, 'message' => 'Acompanhante não encontrado.']);
        exit;
    }
} else {
    $acompanhante_id = $_SESSION['acompanhante_id'];
}

// Processar múltiplos arquivos enviados como documentos[]
$successUploads = [];
$errors = [];

if (isset($_FILES['documentos'])) {
    foreach ($_FILES['documentos']['tmp_name'] as $i => $tmpName) {
        if ($_FILES['documentos']['error'][$i] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erro no upload do arquivo ' . ($_FILES['documentos']['name'][$i] ?? '');
            continue;
        }
        $file = [
            'name' => $_FILES['documentos']['name'][$i],
            'type' => $_FILES['documentos']['type'][$i],
            'tmp_name' => $tmpName,
            'error' => $_FILES['documentos']['error'][$i],
            'size' => $_FILES['documentos']['size'][$i],
        ];
        // Copiar a lógica de validação e salvamento do arquivo único aqui:
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
            $errors[] = 'Tipo de arquivo não permitido para ' . $file['name'] . '. Use apenas JPG, PNG ou PDF.';
            continue;
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            $errors[] = 'Arquivo muito grande para ' . $file['name'] . '. Máximo 10MB.';
            continue;
        }
        $upload_dir = __DIR__ . '/../uploads/documentos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        $filename = 'rg_' . $timestamp . '_' . $random . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $errors[] = 'Erro ao salvar o arquivo ' . $file['name'];
            continue;
        }
        // Salvar no banco de dados
        $db->insert('documentos_acompanhante', [
            'acompanhante_id' => $acompanhante_id,
            'url' => $filename,
            'storage_path' => $filename,
            'tipo' => 'rg',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $successUploads[] = $filename;
    }
    // Redirecionar de volta para a página do admin após upload
    header('Location: /admin/acompanhante-visualizar.php?id=' . $acompanhante_id);
    exit;
}

if ($successUploads) {
    echo json_encode(['success' => true, 'message' => 'Documento(s) enviado(s) com sucesso!', 'tipos' => $successUploads]);
} else {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
}
?> 