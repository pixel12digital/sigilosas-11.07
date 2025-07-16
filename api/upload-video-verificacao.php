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

// Log detalhado do arquivo
error_log('=== UPLOAD VÍDEO VERIFICAÇÃO ===');
error_log('Nome: ' . $file['name']);
error_log('Tipo MIME: ' . $file['type']);
error_log('Tamanho: ' . $file['size']);
error_log('Erro: ' . $file['error']);

$allowed_types = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo'];
$allowed_extensions = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

error_log('Extensão detectada: ' . $file_extension);
error_log('Tipo permitido: ' . (in_array($file['type'], $allowed_types) ? 'SIM' : 'NÃO'));
error_log('Extensão permitida: ' . (in_array($file_extension, $allowed_extensions) ? 'SIM' : 'NÃO'));

if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
    error_log('❌ ARQUIVO REJEITADO - Tipo/extensão não permitida');
    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use apenas MP4, WEBM, OGG, MOV ou AVI.']);
    exit;
}
if ($file['size'] > 50 * 1024 * 1024) { // 50MB
    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 50MB.']);
    exit;
}

$upload_dir = __DIR__ . '/../uploads/verificacao/';
error_log('Diretório de upload: ' . $upload_dir);

if (!is_dir($upload_dir)) {
    error_log('Criando diretório...');
    if (!mkdir($upload_dir, 0755, true)) {
        error_log('❌ ERRO: Não foi possível criar diretório');
        echo json_encode(['success' => false, 'message' => 'Erro ao criar diretório de upload.']);
        exit;
    }
    error_log('✅ Diretório criado com sucesso');
}

$timestamp = time();
$random = bin2hex(random_bytes(8));
$filename = 'verificacao_' . $timestamp . '_' . $random . '.' . $file_extension;
$filepath = $upload_dir . $filename;

error_log('Arquivo temporário: ' . $file['tmp_name']);
error_log('Destino final: ' . $filepath);
error_log('Tentando mover arquivo...');

if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    error_log('❌ ERRO: Falha ao mover arquivo');
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar arquivo no servidor.']);
    exit;
}

error_log('✅ Arquivo movido com sucesso');
error_log('Verificando se arquivo existe: ' . (file_exists($filepath) ? 'SIM' : 'NÃO'));

// Salvar no banco (opcional: remover vídeo anterior)
error_log('Salvando no banco de dados...');
error_log('Acompanhante ID: ' . $acompanhante_id);

try {
    // Remover vídeo anterior
    $delete_result = $db->query("DELETE FROM videos_verificacao WHERE acompanhante_id = ?", [$acompanhante_id]);
    error_log('Vídeos anteriores removidos');
    
    // Inserir novo vídeo
    $video_data = [
        'acompanhante_id' => $acompanhante_id,
        'url' => $filename,
        'tamanho' => $file['size'],
        'formato' => $file_extension,
        'verificado' => 0, // Pendente de verificação
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    error_log('Dados para inserir: ' . json_encode($video_data));
    
    $video_id = $db->insert('videos_verificacao', $video_data);
    
    if ($video_id) {
        error_log('✅ Vídeo salvo no banco com ID: ' . $video_id);
        echo json_encode(['success' => true, 'message' => 'Vídeo enviado com sucesso!', 'filename' => $filename, 'video_id' => $video_id]);
    } else {
        error_log('❌ ERRO: Falha ao inserir no banco');
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar vídeo no banco de dados.']);
    }
} catch (Exception $e) {
    error_log('❌ ERRO EXCEPTION: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
} 