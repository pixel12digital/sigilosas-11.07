<?php
/**
 * API Get Vídeos Públicos
 * Arquivo: api/get-videos-publicos.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Buscar vídeos da acompanhante
    $videos_publicos = $db->fetchAll("SELECT * FROM videos_publicos WHERE acompanhante_id = ? ORDER BY created_at DESC", [$_SESSION['acompanhante_id']]);
    
    $html = '';
    if ($videos_publicos) {
        foreach ($videos_publicos as $v) {
            $html .= '<div class="col-md-4 col-6">';
            $html .= '<div class="card h-100 shadow-sm">';
            $html .= '<video src="' . SITE_URL . '/uploads/videos_publicos/' . htmlspecialchars($v['url']) . '" controls style="width:100%; max-width:140px; aspect-ratio:9/16; height:auto; max-height:250px; margin:auto; display:block; background:#000; object-fit:cover; border-radius:12px;"></video>';
            $html .= '<div class="p-2">';
            $html .= '<div class="fw-bold small mb-1">' . htmlspecialchars($v['titulo'] ?? '') . '</div>';
            $html .= '<div class="text-muted small mb-1">' . htmlspecialchars($v['descricao'] ?? '') . '</div>';
            $html .= '<span class="badge bg-secondary">' . ucfirst($v['status']) . '</span>';
            $html .= '<form method="post" class="d-inline">';
            $html .= '<input type="hidden" name="excluir_video_id" value="' . $v['id'] . '">';
            $html .= '<button type="submit" class="btn btn-sm btn-danger ms-2" onclick="return confirm(\'Excluir este vídeo?\');"><i class="fas fa-trash"></i></button>';
            $html .= '</form>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
    } else {
        $html = '<div class="col-12 text-center text-muted">Nenhum vídeo enviado ainda.</div>';
    }
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao buscar vídeos públicos: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 