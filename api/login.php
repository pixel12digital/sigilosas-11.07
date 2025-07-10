<?php
/**
 * API de Login
 * Arquivo: api/login.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../core/Auth.php';

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    // Obter dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST; // Fallback para form data
    }
    
    // Validar dados
    if (empty($input['email']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email e senha são obrigatórios'
        ]);
        exit;
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email inválido'
        ]);
        exit;
    }
    
    // Tentar login
    $auth = getAuth();
    $result = $auth->login($email, $password);
    
    if ($result['success']) {
        // Login bem-sucedido
        echo json_encode([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'user' => [
                'id' => $result['user']['id'],
                'email' => $result['user']['email'],
                'nome' => $result['user']['nome'],
                'nivel' => $result['user']['nivel']
            ]
        ]);
    } else {
        // Login falhou
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
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