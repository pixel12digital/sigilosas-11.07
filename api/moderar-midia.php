<?php
require_once __DIR__ . '/../config/database.php';
session_name('sigilosas_admin_session');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_nivel'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$tipo = $_POST['tipo'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$acao = $_POST['acao'] ?? '';
if (!$id || !in_array($acao, ['aprovar', 'reprovar']) || !in_array($tipo, ['foto', 'documento', 'video_verificacao'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$db = getDB();
$tabela = '';
$campo = '';
$valor = $acao === 'aprovar' ? 1 : 0;

switch ($tipo) {
    case 'foto':
        $tabela = 'fotos';
        $campo = 'aprovada';
        break;
    case 'documento':
        $tabela = 'documentos_acompanhante';
        $campo = 'verificado';
        break;
    case 'video_verificacao':
        $tabela = 'videos_verificacao';
        $campo = 'verificado';
        break;
}

if (!$tabela || !$campo) {
    echo json_encode(['success' => false, 'message' => 'Tipo de mídia inválido.']);
    exit;
}

try {
    $data = [$campo => $valor];
    if ($acao === 'reprovar') {
        $data['motivo_rejeicao'] = 'Reprovado pelo administrador';
    }
    
    $ok = $db->update($tabela, $data, 'id = ?', [$id]);
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível atualizar o status.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
} 