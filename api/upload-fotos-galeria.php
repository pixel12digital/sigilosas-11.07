<?php
// Endpoint para upload de fotos da galeria
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

if (!isset($_FILES['fotos_galeria']) || empty($_FILES['fotos_galeria']['name'][0])) {
    echo json_encode(['success' => false, 'message' => 'Nenhuma foto selecionada.']);
    exit;
}

// Log detalhado dos arquivos
error_log('=== UPLOAD FOTOS GALERIA API ===');
error_log('Quantidade de arquivos: ' . count($_FILES['fotos_galeria']['name']));

$files = $_FILES['fotos_galeria'];
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$uploaded_photos = [];
$errors = [];

// Verificar diretório
$upload_dir = __DIR__ . '/../uploads/galeria/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar diretório de upload.']);
        exit;
    }
}

// Processar cada arquivo
for ($i = 0; $i < count($files['name']); $i++) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erro no arquivo: ' . $files['name'][$i];
        continue;
    }
    
    $file_extension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
    $file_type = $files['type'][$i];
    
    // Validação de tipo
    if (!in_array($file_type, $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
        $errors[] = 'Formato não permitido: ' . $files['name'][$i];
        continue;
    }
    
    // Validação de tamanho (5MB)
    if ($files['size'][$i] > 5 * 1024 * 1024) {
        $errors[] = 'Arquivo muito grande: ' . $files['name'][$i] . ' (máx. 5MB)';
        continue;
    }
    
    // Gerar nome único
    $filename = 'galeria_' . uniqid('', true) . '_' . bin2hex(random_bytes(4)) . '.' . $file_extension;
    $filepath = $upload_dir . $filename;
    
    // Mover arquivo
    if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
        // Salvar no banco
        $foto_id = $db->insert('fotos', [
            'acompanhante_id' => $acompanhante_id,
            'tipo' => 'galeria',
            'url' => $filename,
            'ordem' => 0,
            'principal' => 0,
            'aprovada' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($foto_id) {
            $uploaded_photos[] = [
                'id' => $foto_id,
                'filename' => $filename,
                'original_name' => $files['name'][$i]
            ];
            error_log('✅ Foto salva: ' . $filename . ' (ID: ' . $foto_id . ')');
        } else {
            $errors[] = 'Erro ao salvar no banco: ' . $files['name'][$i];
        }
    } else {
        $errors[] = 'Erro ao salvar arquivo: ' . $files['name'][$i];
    }
}

// Resposta
if (!empty($uploaded_photos)) {
    $message = count($uploaded_photos) . ' foto(s) enviada(s) com sucesso!';
    if (!empty($errors)) {
        $message .= ' (' . count($errors) . ' erro(s))';
    }
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'photos' => $uploaded_photos,
        'errors' => $errors
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Nenhuma foto foi enviada. Erros: ' . implode(', ', $errors)
    ]);
} 