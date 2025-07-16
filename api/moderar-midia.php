<?php
require_once __DIR__ . '/../config/database.php';

// Iniciar sessão admin se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}

header('Content-Type: application/json');

// Debug: Log da sessão
error_log('DEBUG MODERAR MIDIA: SESSION = ' . print_r($_SESSION, true));
error_log('DEBUG MODERAR MIDIA: POST = ' . print_r($_POST, true));

// Verificar se está logado como admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    error_log('DEBUG MODERAR MIDIA: Sessão inválida');
    echo json_encode(['success' => false, 'message' => 'Acesso negado - sessão inválida.']);
    exit;
}

$tipo = $_POST['tipo'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$acao = $_POST['acao'] ?? '';

error_log("DEBUG MODERAR MIDIA: tipo=$tipo, id=$id, acao=$acao");

if (!$id || !in_array($acao, ['aprovar', 'reprovar']) || !in_array($tipo, ['foto', 'documento', 'video_verificacao'])) {
    error_log('DEBUG MODERAR MIDIA: Dados inválidos');
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
    error_log('DEBUG MODERAR MIDIA: Tipo de mídia inválido');
    echo json_encode(['success' => false, 'message' => 'Tipo de mídia inválido.']);
    exit;
}

try {
    $data = [
        $campo => $valor,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($acao === 'reprovar') {
        $data['motivo_rejeicao'] = 'Reprovado pelo administrador';
    } else {
        // Limpar motivo de rejeição se for aprovação
        $data['motivo_rejeicao'] = null;
    }
    
    error_log("DEBUG MODERAR MIDIA: Tentando atualizar tabela $tabela, campo $campo = $valor para ID $id");
    
    $result = $db->update($tabela, $data, 'id = ?', [$id]);
    
    error_log("DEBUG MODERAR MIDIA: Resultado da atualização: " . var_export($result, true));
    
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível atualizar o status.']);
    }
} catch (Exception $e) {
    error_log('DEBUG MODERAR MIDIA: Erro na atualização: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
} 