<?php
/**
 * API Upload de Vídeo
 * Arquivo: api/upload-video.php
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

$titulo = trim($_POST['titulo'] ?? '');
$url = trim($_POST['url'] ?? '');

// Validações
$errors = [];

if (empty($url)) {
    $errors[] = 'URL do vídeo é obrigatória';
} elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
    $errors[] = 'URL inválida';
} else {
    // Verificar se é uma URL de vídeo válida
    $allowed_domains = [
        'youtube.com',
        'www.youtube.com',
        'youtu.be',
        'vimeo.com',
        'www.vimeo.com',
        'dailymotion.com',
        'www.dailymotion.com'
    ];
    
    $parsed_url = parse_url($url);
    $domain = $parsed_url['host'] ?? '';
    
    $is_valid_domain = false;
    foreach ($allowed_domains as $allowed_domain) {
        if (strpos($domain, $allowed_domain) !== false) {
            $is_valid_domain = true;
            break;
        }
    }
    
    if (!$is_valid_domain) {
        $errors[] = 'URL deve ser do YouTube, Vimeo ou Dailymotion';
    }
}

if (!empty($errors)) {
            header('Location: ../acompanhante/midia.php?error=' . urlencode(implode(' ', $errors)));
    exit;
}

try {
    // Salvar no banco de dados
    $data = [
        'acompanhante_id' => $acompanhante_id,
        'titulo' => $titulo ?: 'Vídeo',
        'url' => $url,
        'tipo' => 'video',
        'created_at' => date('Y-m-d H:i:s')
    ];

    $db->insert('videos', $data);

            header('Location: ../acompanhante/midia.php?success=Vídeo adicionado com sucesso!');
    exit;

} catch (Exception $e) {
            header('Location: ../acompanhante/midia.php?error=Erro interno: ' . $e->getMessage());
    exit;
}
?> 