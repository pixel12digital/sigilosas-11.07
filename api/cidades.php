<?php
/**
 * API de Cidades
 * Arquivo: api/cidades.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Auth.php';

$db = getDB();
$auth = getAuth();

// Verificar autenticação para operações que precisam
$method = $_SERVER['REQUEST_METHOD'];
$needsAuth = in_array($method, ['POST', 'PUT', 'DELETE']);

if ($needsAuth) {
    if (!$auth->isAdmin()) {
        http_response_code(401);
        echo json_encode(['error' => 'Acesso negado']);
        exit;
    }
}

try {
    switch ($method) {
        case 'GET':
            // Listar cidades
            $estado_id = isset($_GET['estado_id']) ? (int)$_GET['estado_id'] : null;
            if ($estado_id) {
                $sql = "SELECT c.id, c.nome FROM cidades c WHERE c.estado_id = ? AND EXISTS (SELECT 1 FROM acompanhantes a WHERE a.cidade_id = c.id AND a.status = 'aprovado') ORDER BY c.nome ASC";
                $cidades = $db->fetchAll($sql, [$estado_id]);
                echo json_encode($cidades);
                break;
            }
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            
            $where = [];
            $params = [];
            
            if ($estado_id) {
                $where[] = "c.estado_id = ?";
                $params[] = $estado_id;
            }
            
            if ($search) {
                $where[] = "c.nome LIKE ?";
                $params[] = "%$search%";
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $sql = "SELECT c.*, e.nome as estado_nome, e.uf as estado_uf,
                           (SELECT COUNT(*) FROM acompanhantes a WHERE a.cidade_id = c.id) as total_acompanhantes
                    FROM cidades c
                    LEFT JOIN estados e ON c.estado_id = e.id
                    $whereClause
                    ORDER BY c.nome ASC";
            
            $cidades = $db->fetchAll($sql, $params);
            
            echo json_encode([
                'success' => true,
                'data' => $cidades
            ]);
            break;
            
        case 'POST':
            // Criar nova cidade
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $input = $_POST;
            }
            
            // Validar dados obrigatórios
            if (empty($input['nome']) || empty($input['estado_id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Nome e estado são obrigatórios'
                ]);
                exit;
            }
            
            // Verificar se estado existe
            $estado = $db->fetch(
                "SELECT id FROM estados WHERE id = ?",
                [(int)$input['estado_id']]
            );
            
            if (!$estado) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Estado não encontrado'
                ]);
                exit;
            }
            
            // Verificar se cidade já existe no estado
            $existing = $db->fetch(
                "SELECT id FROM cidades WHERE nome = ? AND estado_id = ?",
                [trim($input['nome']), (int)$input['estado_id']]
            );
            
            if ($existing) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Cidade já existe neste estado'
                ]);
                exit;
            }
            
            $data = [
                'nome' => trim($input['nome']),
                'estado_id' => (int)$input['estado_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $db->insert('cidades', $data);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cidade criada com sucesso',
                'id' => $id
            ]);
            break;
            
        case 'PUT':
            // Atualizar cidade
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID é obrigatório'
                ]);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $input = $_POST;
            }
            
            // Verificar se existe
            $existing = $db->fetch(
                "SELECT id FROM cidades WHERE id = ?",
                [$id]
            );
            
            if (!$existing) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Cidade não encontrada'
                ]);
                exit;
            }
            
            // Preparar dados para atualização
            $data = [];
            
            if (isset($input['nome'])) {
                $data['nome'] = trim($input['nome']);
            }
            
            if (isset($input['estado_id'])) {
                // Verificar se estado existe
                $estado = $db->fetch(
                    "SELECT id FROM estados WHERE id = ?",
                    [(int)$input['estado_id']]
                );
                
                if (!$estado) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Estado não encontrado'
                    ]);
                    exit;
                }
                
                $data['estado_id'] = (int)$input['estado_id'];
            }
            
            if (!empty($data)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $db->update('cidades', $data, 'id = ?', [$id]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Cidade atualizada com sucesso'
            ]);
            break;
            
        case 'DELETE':
            // Deletar cidade
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID é obrigatório'
                ]);
                exit;
            }
            
            // Verificar se existe
            $existing = $db->fetch(
                "SELECT id FROM cidades WHERE id = ?",
                [$id]
            );
            
            if (!$existing) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Cidade não encontrada'
                ]);
                exit;
            }
            
            // Verificar se há acompanhantes usando esta cidade
            $acompanhantes = $db->fetch(
                "SELECT COUNT(*) as total FROM acompanhantes WHERE cidade_id = ?",
                [$id]
            );
            
            if ($acompanhantes['total'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Não é possível deletar cidade que possui acompanhantes cadastradas'
                ]);
                exit;
            }
            
            // Deletar cidade
            $db->delete('cidades', 'id = ?', [$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cidade deletada com sucesso'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'debug' => $e->getMessage() // Remover em produção
    ]);
}
?> 