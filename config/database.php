<?php
/**
 * Configuração do Banco de Dados Centralizado (Hostinger)
 * Arquivo: config/database.php
 */

// Configurações do banco de dados
define('DB_HOST', 'auth-db1067.hstgr.io');           // Host do MySQL na Hostinger
define('DB_NAME', 'u819562010_sigilosasvip');        // Nome do banco na Hostinger
define('DB_USER', 'u819562010_sigilosasvip');        // Usuário do banco na Hostinger
define('DB_PASS', 'Los@ngo_081081');                // Senha do banco na Hostinger
define('DB_CHARSET', 'utf8mb4');                    // Charset

// Configurações da aplicação
// REMOVIDO define('SITE_URL', ...) para evitar conflito
define('SITE_NAME', 'Sigilosas');         // Nome do site
define('ADMIN_EMAIL', 'admin@sigilosas.com'); // Email do admin

// Configurações de upload
define('UPLOAD_DIR', '../uploads/');      // Diretório de uploads
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOC_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);

// Configurações de sessão
define('SESSION_LIFETIME', 3600); // 1 hora

// Configurações de segurança
define('HASH_COST', 12); // Custo do bcrypt
define('JWT_SECRET', 'sua_chave_secreta_aqui'); // Chave JWT

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro (desabilitar em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Classe Database para conexão com MySQL
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            // Exibe erro detalhado para debug
            die("Erro de conexão com o banco: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Erro na query: " . $e->getMessage());
        }
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function insert($table, $data) {
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        
        $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $fields = array_keys($data);
        $set = implode(' = ?, ', $fields) . ' = ?';
        $sql = "UPDATE $table SET $set WHERE $where";
        
        $params = array_values($data);
        $params = array_merge($params, $whereParams);
        
        return $this->query($sql, $params)->rowCount();
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->query($sql, $params)->rowCount();
    }
}

// Função helper para obter conexão
function getDB() {
    return Database::getInstance();
}
?> 