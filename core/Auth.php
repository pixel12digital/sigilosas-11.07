<?php
/**
 * Classe de Autenticação
 * Arquivo: core/Auth.php
 */

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
        // Removido initSession() para evitar conflitos de sessão
        // A sessão deve ser iniciada apenas pelos headers específicos de cada painel
    }
    
    /**
     * Login de usuário
     */
    public function login($email, $password) {
        // Tenta admin
        $user = $this->db->fetch(
            "SELECT * FROM admin WHERE email = ? AND ativo = 1",
            [$email]
        );
        if ($user && password_verify($password, $user['senha_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_nivel'] = $user['nivel'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            return [
                'success' => true,
                'user' => $user
            ];
        }

        // Tenta acompanhante
        $acomp = $this->db->fetch(
            "SELECT * FROM acompanhantes WHERE email = ? AND ativo = 1",
            [$email]
        );
        if ($acomp && password_verify($password, $acomp['senha_hash'])) {
            $_SESSION['acompanhante_id'] = $acomp['id'];
            $_SESSION['acompanhante_email'] = $acomp['email'];
            $_SESSION['acompanhante_nome'] = $acomp['nome'];
            $_SESSION['acompanhante_apelido'] = $acomp['apelido'] ?? '';
            $_SESSION['login_time'] = time();
            return [
                'success' => true,
                'user' => $acomp
            ];
        }

        return [
            'success' => false,
            'message' => 'Email ou senha inválidos'
        ];
    }
    
    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        return ['success' => true];
    }
    
    /**
     * Verificar se está logado
     */
    public function isLoggedIn() {
        // Admin
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_id'])) {
            if (time() - $_SESSION['login_time'] > SESSION_LIFETIME) {
                $this->logout();
                return false;
            }
            return true;
        }
        // Acompanhante
        if (isset($_SESSION['acompanhante_id'])) {
            // Opcional: checar expiração de sessão se houver campo de tempo para acompanhantes
            return true;
        }
        return false;
    }
    
    /**
     * Verificar se é admin
     */
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user_nivel'] === 'admin';
    }
    
    /**
     * Obter dados do usuário logado
     */
    public function getCurrentUser() {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_id'])) {
            // Admin
            return [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'nome' => $_SESSION['user_nome'],
                'nivel' => $_SESSION['user_nivel']
            ];
        } elseif (isset($_SESSION['acompanhante_id'])) {
            // Acompanhante
            return [
                'id' => $_SESSION['acompanhante_id'],
                'email' => $_SESSION['acompanhante_email'] ?? null,
                'nome' => $_SESSION['acompanhante_nome'] ?? null,
                'nivel' => 'acompanhante'
            ];
        }
        return null;
    }
    
    /**
     * Criar hash de senha
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
    }
    
    /**
     * Verificar se a senha é válida
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Criar usuário admin
     */
    public function createAdmin($email, $password, $nome = '') {
        // Verificar se já existe
        $existing = $this->db->fetch(
            "SELECT id FROM admin WHERE email = ?",
            [$email]
        );
        
        if ($existing) {
            return [
                'success' => false,
                'message' => 'Email já cadastrado'
            ];
        }
        
        $data = [
            'email' => $email,
            'senha_hash' => $this->hashPassword($password),
            'nome' => $nome,
            'nivel' => 'admin',
            'ativo' => 1
        ];
        
        $id = $this->db->insert('admin', $data);
        
        return [
            'success' => true,
            'id' => $id
        ];
    }
    
    /**
     * Requer autenticação
     */
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: /Sigilosas-MySQL/index.php?page=login');
            exit;
        }
    }
    
    /**
     * Requer admin
     */
    public function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            header('Location: /Sigilosas-MySQL/admin/');
            exit;
        }
    }
}

// Função helper para obter instância
function getAuth() {
    return new Auth();
}
?> 