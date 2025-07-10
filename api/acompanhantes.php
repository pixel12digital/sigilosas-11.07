<?php
/**
 * API de Acompanhantes
 * Arquivo: api/acompanhantes.php
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
            // Listar acompanhantes
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            $cidade = isset($_GET['cidade']) ? $_GET['cidade'] : null;
            
            $offset = ($page - 1) * $limit;
            
            // Construir query
            $where = [];
            $params = [];
            
            if ($status) {
                $where[] = "a.status = ?";
                $params[] = $status;
            }
            
            if ($cidade) {
                $where[] = "c.nome LIKE ?";
                $params[] = "%$cidade%";
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            // Query principal
            $sql = "SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf,
                           (SELECT COUNT(*) FROM fotos f WHERE f.acompanhante_id = a.id) as total_fotos,
                           (SELECT COUNT(*) FROM videos v WHERE v.acompanhante_id = a.id) as total_videos
                    FROM acompanhantes a
                    LEFT JOIN cidades c ON a.cidade_id = c.id
                    LEFT JOIN estados e ON c.estado_id = e.id
                    $whereClause
                    ORDER BY a.created_at DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $acompanhantes = $db->fetchAll($sql, $params);
            
            // Contar total
            $countSql = "SELECT COUNT(*) as total FROM acompanhantes a
                        LEFT JOIN cidades c ON a.cidade_id = c.id
                        LEFT JOIN estados e ON c.estado_id = e.id
                        $whereClause";
            
            $total = $db->fetch($countSql, array_slice($params, 0, -2));
            
            echo json_encode([
                'success' => true,
                'data' => $acompanhantes,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total['total'],
                    'pages' => ceil($total['total'] / $limit)
                ]
            ]);
            break;
            
        case 'POST':
            // Criar nova acompanhante
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $input = $_POST;
            }
            
            // Validar dados obrigatórios
            $required = ['nome', 'email', 'telefone', 'cidade_id'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => "Campo '$field' é obrigatório"
                    ]);
                    exit;
                }
            }
            
            // Verificar se email já existe
            $existing = $db->fetch(
                "SELECT id FROM acompanhantes WHERE email = ?",
                [$input['email']]
            );
            
            if ($existing) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Email já cadastrado'
                ]);
                exit;
            }
            
            // Preparar dados
            $data = [
                'nome' => trim($input['nome']),
                'email' => trim($input['email']),
                'telefone' => trim($input['telefone']),
                'cidade_id' => (int)$input['cidade_id'],
                'idade' => isset($input['idade']) ? (int)$input['idade'] : null,
                'altura' => isset($input['altura']) ? (float)$input['altura'] : null,
                'peso' => isset($input['peso']) ? (float)$input['peso'] : null,
                'medidas' => isset($input['medidas']) ? $input['medidas'] : null,
                'endereco' => isset($input['endereco']) ? $input['endereco'] : null,
                'descricao' => isset($input['descricao']) ? $input['descricao'] : null,
                'local_atendimento' => isset($input['local_atendimento']) ? json_encode($input['local_atendimento']) : null,
                'formas_pagamento' => isset($input['formas_pagamento']) ? json_encode($input['formas_pagamento']) : null,
                'idiomas' => isset($input['idiomas']) ? json_encode($input['idiomas']) : null,
                'especialidades' => isset($input['especialidades']) ? json_encode($input['especialidades']) : null,
                'valor_padrao' => isset($input['valor_padrao']) ? (float)$input['valor_padrao'] : null,
                'valor_promocional' => isset($input['valor_promocional']) ? (float)$input['valor_promocional'] : null,
                'status' => 'pendente',
                'verificado' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $db->insert('acompanhantes', $data);
            
            echo json_encode([
                'success' => true,
                'message' => 'Acompanhante criada com sucesso',
                'id' => $id
            ]);
            break;
            
        case 'PUT':
            // Atualizar acompanhante
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
                "SELECT id FROM acompanhantes WHERE id = ?",
                [$id]
            );
            
            if (!$existing) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Acompanhante não encontrada'
                ]);
                exit;
            }
            
            // Preparar dados para atualização
            $data = [];
            $allowedFields = [
                'nome', 'telefone', 'cidade_id', 'idade', 'altura', 'peso',
                'medidas', 'endereco', 'descricao', 'local_atendimento',
                'formas_pagamento', 'idiomas', 'especialidades', 'valor_padrao',
                'valor_promocional', 'status', 'verificado'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    if (in_array($field, ['local_atendimento', 'formas_pagamento', 'idiomas', 'especialidades'])) {
                        $data[$field] = json_encode($input[$field]);
                    } else {
                        $data[$field] = $input[$field];
                    }
                }
            }
            
            if (!empty($data)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $db->update('acompanhantes', $data, 'id = ?', [$id]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Acompanhante atualizada com sucesso'
            ]);
            break;
            
        case 'DELETE':
            // Deletar acompanhante
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
                "SELECT id FROM acompanhantes WHERE id = ?",
                [$id]
            );
            
            if (!$existing) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Acompanhante não encontrada'
                ]);
                exit;
            }
            
            // Deletar (cascade irá deletar fotos, vídeos, documentos)
            $db->delete('acompanhantes', 'id = ?', [$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Acompanhante deletada com sucesso'
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