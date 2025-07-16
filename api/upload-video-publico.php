<?php
/**
 * API Upload de Vídeo Público
 * Arquivo: api/upload-video-publico.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar se está logada
session_name('sigilosas_acompanhante_session');
session_start();

if (!isset($_SESSION['acompanhante_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}



// Verificar se foi enviado um arquivo
if (!isset($_FILES['video_publico'])) {
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado (video_publico não encontrado)']);
    exit;
}

if ($_FILES['video_publico']['error'] !== UPLOAD_ERR_OK) {
    $error_code = $_FILES['video_publico']['error'];
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do servidor)',
        UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (limite do formulário)',
        UPLOAD_ERR_PARTIAL => 'Upload parcial',
        UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada',
        UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever arquivo',
        UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
    ];
    
    $message = $error_messages[$error_code] ?? 'Erro no upload: código ' . $error_code;
    error_log('DEBUG: Erro no upload - ' . $message);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$file = $_FILES['video_publico'];
$titulo = trim($_POST['titulo_video'] ?? '');
$descricao = trim($_POST['descricao_video'] ?? '');

// Debug: Informações do arquivo
error_log('DEBUG: Nome do arquivo: ' . $file['name']);
error_log('DEBUG: Tamanho: ' . $file['size'] . ' bytes');
error_log('DEBUG: Tipo: ' . $file['type']);
error_log('DEBUG: Título: ' . $titulo);
error_log('DEBUG: Descrição: ' . $descricao);

// Validações
$maxSize = 50 * 1024 * 1024; // 50MB
$allowedTypes = ['video/mp4', 'video/webm', 'video/quicktime'];

// Verificar tamanho
if ($file['size'] > $maxSize) {
    error_log('DEBUG: Arquivo muito grande - ' . $file['size'] . ' > ' . $maxSize);
    echo json_encode(['success' => false, 'message' => 'O vídeo excede o tamanho máximo permitido (50MB)']);
    exit;
}

// Verificar tipo
error_log('DEBUG: Verificando tipo - ' . $file['type'] . ' em ' . implode(', ', $allowedTypes));
if (!in_array($file['type'], $allowedTypes)) {
    error_log('DEBUG: Tipo não permitido - ' . $file['type']);
    echo json_encode(['success' => false, 'message' => 'Formato de vídeo não permitido: ' . $file['type']]);
    exit;
}

// Verificar extensão
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['mp4', 'webm', 'mov'];
if (!in_array($ext, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Extensão de arquivo não permitida: ' . $ext]);
    exit;
}

try {
    // Gerar nome único para o arquivo
    $filename = 'video_' . uniqid('', true) . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/videos_publicos/';
    $dest = $uploadDir . $filename;
    
    // Verificar se a pasta existe e tem permissão
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar pasta de upload']);
            exit;
        }
    }
    
    if (!is_writable($uploadDir)) {
        echo json_encode(['success' => false, 'message' => 'Pasta de destino não tem permissão de escrita']);
        exit;
    }
    
    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar o vídeo no servidor']);
        exit;
    }
    
    // Salvar no banco de dados
    $video_id = $db->insert('videos_publicos', [
        'acompanhante_id' => $_SESSION['acompanhante_id'],
        'url' => $filename,
        'titulo' => $titulo ?: null,
        'descricao' => $descricao ?: null,
        'status' => 'pendente',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    if (!$video_id) {
        // Se falhou ao salvar no banco, remover arquivo
        if (file_exists($dest)) {
            unlink($dest);
        }
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar informações do vídeo']);
        exit;
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Vídeo enviado com sucesso! Aguarde aprovação do admin.',
        'video' => [
            'id' => $video_id,
            'filename' => $filename,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'status' => 'pendente'
        ]
    ]);
    
} catch (Exception $e) {
    // Remover arquivo se foi criado
    if (isset($dest) && file_exists($dest)) {
        unlink($dest);
    }
    
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor. Tente novamente.']);
}
?> 