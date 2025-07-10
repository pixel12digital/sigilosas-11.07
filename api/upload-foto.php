<?php
/**
 * API Upload de Foto
 * Arquivo: api/upload-foto.php
 */

require_once __DIR__ . '/../config/database.php';

// Verificar se está logada
session_start();
if (!isset($_SESSION['acompanhante_id'])) {
    header('Location: ../pages/login-acompanhante.php');
    exit;
}

$db = getDB();
$acompanhante_id = $_SESSION['acompanhante_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../acompanhante/midia.php?error=Método não permitido');
    exit;
}

// Verificar se foi enviado um arquivo
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    header('Location: ../acompanhante/midia.php?error=Erro no upload do arquivo');
    exit;
}

$file = $_FILES['foto'];
$ordem = (int)($_POST['ordem'] ?? 1);

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
            header('Location: ../acompanhante/midia.php?error=' . urlencode(implode(' ', $errors)));
    exit;
}

try {
    // Criar diretório se não existir
    $upload_dir = __DIR__ . '/../uploads/galeria/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Gerar nome único para o arquivo
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    $filename = $timestamp . '_' . $random . '.' . $file_extension;
    $filepath = $upload_dir . $filename;

    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Erro ao salvar arquivo');
    }

    // Redimensionar imagem se necessário
    $max_width = 1200;
    $max_height = 1200;
    
    list($width, $height) = getimagesize($filepath);
    
    if ($width > $max_width || $height > $max_height) {
        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = round($width * $ratio);
        $new_height = round($height * $ratio);
        
        $source_image = imagecreatefromstring(file_get_contents($filepath));
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
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
    }

    // Salvar no banco de dados
    $data = [
        'acompanhante_id' => $acompanhante_id,
        'arquivo' => $filename,
        'tipo' => 'foto',
        'ordem' => $ordem,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $db->insert('fotos', $data);

            header('Location: ../acompanhante/midia.php?success=Foto enviada com sucesso!');
    exit;

} catch (Exception $e) {
    // Remover arquivo se foi criado
    if (isset($filepath) && file_exists($filepath)) {
        unlink($filepath);
    }
    
            header('Location: ../acompanhante/midia.php?error=Erro interno: ' . $e->getMessage());
    exit;
}
?> 