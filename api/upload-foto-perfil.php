<?php
/**
 * API Upload de Foto de Perfil
 * Arquivo: api/upload-foto-perfil.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_acompanhante_session');
    session_start();
}
error_log('DEBUG SESSION: ' . print_r($_SESSION, true));
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();
require_once __DIR__ . '/../core/Auth.php';

header('Content-Type: application/json');

$auth = new Auth(); // Não passar $pdo, Auth já instancia o DB

// Pega o ID do usuário autenticado
$user = $auth->getCurrentUser();
if (!$user || empty($user['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}
$user_id = $user['id'];
$user_nivel = $user['nivel'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se foi enviado um arquivo
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo']);
    exit;
}

$file = $_FILES['foto'];

// Validações
$errors = [];

// Verificar tipo MIME
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowed_types)) {
    $errors[] = 'Tipo de arquivo não permitido. Use apenas JPG, PNG ou GIF.';
}

// Verificar extensão
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($file_extension, $allowed_extensions)) {
    $errors[] = 'Extensão de arquivo não permitida.';
}

// Verificar tamanho (máximo 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    $errors[] = 'Arquivo muito grande. Máximo 5MB.';
}

// Verificar se é realmente uma imagem
$image_info = getimagesize($file['tmp_name']);
if ($image_info === false) {
    $errors[] = 'Arquivo não é uma imagem válida.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    // Criar diretório se não existir
    $upload_dir = __DIR__ . '/../uploads/perfil/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Gerar nome único para o arquivo
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    $filename = 'perfil_' . $timestamp . '_' . $random . '.' . $file_extension;
    $filepath = $upload_dir . $filename;

    // Antes do move_uploaded_file, adicionar debug:
    error_log('DEBUG UPLOAD: tmp_name=' . $file['tmp_name'] . ' | destino=' . $filepath);
    if (!file_exists($file['tmp_name'])) {
        error_log('DEBUG UPLOAD: Arquivo temporário NÃO existe!');
    } else {
        error_log('DEBUG UPLOAD: Arquivo temporário existe.');
    }

    // Mover arquivo
    $move_result = move_uploaded_file($file['tmp_name'], $filepath);
    error_log('DEBUG UPLOAD: move_uploaded_file result: ' . var_export($move_result, true));
    if (!$move_result) {
        throw new Exception('Erro ao salvar arquivo');
    }

    // Redimensionar imagem para 300x300 (tamanho ideal para perfil)
    $max_width = 300;
    $max_height = 300;
    
    list($width, $height) = getimagesize($filepath);
    
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    $source_image = imagecreatefromstring(file_get_contents($filepath));
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // Preservar transparência para PNG e GIF
    if ($file_extension === 'png' || $file_extension === 'gif') {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefill($new_image, 0, 0, $transparent);
    }
    
    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Salvar imagem redimensionada
    switch ($file_extension) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($new_image, $filepath, 85);
            break;
        case 'png':
            imagepng($new_image, $filepath, 8);
            break;
        case 'gif':
            imagegif($new_image, $filepath);
            break;
    }
    
    imagedestroy($source_image);
    imagedestroy($new_image);

    // Salvar no banco de dados
    $formato = $file_extension;
    $tamanho = filesize($filepath);
    $storage_path = $filename;
    if ($user_nivel === 'admin') {
        // Atualizar foto do admin (mantém uso do PDO se necessário)
        $stmt = $pdo->prepare("UPDATE admins SET foto = ? WHERE id = ?");
        $stmt->execute([$filename, $user_id]);
    } else {
        // Marcar todas as fotos de perfil anteriores como principal = 0
        $db = getDB();
        $db->update('fotos', ['principal' => 0], 'acompanhante_id = ? AND tipo = ?', [$user_id, 'perfil']);
        // Inserir nova foto de perfil aguardando aprovação
        $db->insert('fotos', [
            'acompanhante_id' => $user_id,
            'url' => $filename,
            'storage_path' => $storage_path,
            'tipo' => 'perfil',
            'principal' => 1,
            'aprovada' => 0,
            'formato' => $formato,
            'tamanho' => $tamanho,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Foto atualizada com sucesso!', 'filename' => $filename]);

} catch (Exception $e) {
    // Remover arquivo se foi criado
    if (isset($filepath) && file_exists($filepath)) {
        unlink($filepath);
    }
    
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}
?> 