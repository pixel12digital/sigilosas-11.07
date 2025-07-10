<?php
if (!defined('SIGILOSAS_CONFIG_LOADED')) {
    define('SIGILOSAS_CONFIG_LOADED', true);

    // ===== CONFIGURAÇÕES GERAIS =====

    // Configurações do site
    if (!defined('SITE_NAME')) define('SITE_NAME', 'Sigilosas VIP');
    if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/Sigilosas-MySQL');
    if (!defined('SITE_EMAIL')) define('SITE_EMAIL', 'contato@sigilosasvip.com');
    if (!defined('SITE_PHONE')) define('SITE_PHONE', '(11) 99999-9999');

    // Configurações de timezone
    date_default_timezone_set('America/Sao_Paulo');

    // Configurações de upload
    if (!defined('UPLOAD_MAX_SIZE')) define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
    if (!defined('UPLOAD_ALLOWED_TYPES')) define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', 'uploads/');

    // Configurações de paginação
    if (!defined('ITEMS_PER_PAGE')) define('ITEMS_PER_PAGE', 12);
    if (!defined('ADMIN_ITEMS_PER_PAGE')) define('ADMIN_ITEMS_PER_PAGE', 20);

    // Configurações de JWT
    if (!defined('JWT_SECRET')) define('JWT_SECRET', 'sua_chave_secreta_muito_segura_aqui');
    if (!defined('JWT_EXPIRY')) define('JWT_EXPIRY', 3600); // 1 hora

    // Configurações de email
    if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
    if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
    if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', 'seu_email@gmail.com');
    if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', 'sua_senha_de_app');
    if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'noreply@sigilosasvip.com');
    if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'Sigilosas VIP');

    // Configurações de cache
    if (!defined('CACHE_ENABLED')) define('CACHE_ENABLED', true);
    if (!defined('CACHE_DURATION')) define('CACHE_DURATION', 3600); // 1 hora

    // Configurações de debug
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
    if (!defined('LOG_ERRORS')) define('LOG_ERRORS', true);
    if (!defined('LOG_PATH')) define('LOG_PATH', 'logs/');

    // Configurações de segurança
    if (!defined('PASSWORD_MIN_LENGTH')) define('PASSWORD_MIN_LENGTH', 6);
    if (!defined('LOGIN_MAX_ATTEMPTS')) define('LOGIN_MAX_ATTEMPTS', 5);
    if (!defined('LOGIN_LOCKOUT_TIME')) define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos

    // Configurações de SEO
    if (!defined('META_DESCRIPTION')) define('META_DESCRIPTION', 'Encontre as melhores acompanhantes de luxo do Brasil. Perfis verificados e seguros.');
    if (!defined('META_KEYWORDS')) define('META_KEYWORDS', 'acompanhantes, luxo, Brasil, verificado, seguro');
    if (!defined('META_AUTHOR')) define('META_AUTHOR', 'Sigilosas VIP');

    // Configurações de analytics
    if (!defined('GOOGLE_ANALYTICS_ID')) define('GOOGLE_ANALYTICS_ID', 'GA_MEASUREMENT_ID');

    // Configurações de redes sociais
    if (!defined('FACEBOOK_URL')) define('FACEBOOK_URL', 'https://facebook.com/sigilosasvip');
    if (!defined('INSTAGRAM_URL')) define('INSTAGRAM_URL', 'https://instagram.com/sigilosasvip');
    if (!defined('TWITTER_URL')) define('TWITTER_URL', 'https://twitter.com/sigilosasvip');
    if (!defined('TELEGRAM_URL')) define('TELEGRAM_URL', 'https://t.me/sigilosasvip');

    // Configurações de sessão
    if (!defined('SESSION_NAME')) define('SESSION_NAME', 'sigilosas_session');
    if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 60 * 60 * 4); // 4 horas
    if (!defined('HASH_COST')) define('HASH_COST', 10);

    // ===== FUNÇÕES UTILITÁRIAS =====

    /**
     * Função para debug
     */
    function debug($data, $die = false) {
        if (DEBUG_MODE) {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
            if ($die) die();
        }
    }

    /**
     * Função para log de erros
     */
    function logError($message, $context = []) {
        if (LOG_ERRORS) {
            $logFile = LOG_PATH . 'error_' . date('Y-m-d') . '.log';
            $timestamp = date('Y-m-d H:i:s');
            $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
            $logMessage = "[$timestamp] $message$contextStr\n";
            
            if (!is_dir(LOG_PATH)) {
                mkdir(LOG_PATH, 0755, true);
            }
            
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Função para sanitizar input
     */
    function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map('sanitizeInput', $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Função para validar email
     */
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Função para gerar token seguro
     */
    function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    /**
     * Função para verificar se é HTTPS
     */
    function isHttps() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Função para obter IP do usuário
     */
    function getUserIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Função para formatar data
     */
    function formatDate($date, $format = 'd/m/Y H:i') {
        return date($format, strtotime($date));
    }

    /**
     * Função para formatar moeda
     */
    function formatCurrency($value) {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    /**
     * Função para formatar número
     */
    function formatNumber($number) {
        return number_format($number, 0, ',', '.');
    }

    /**
     * Função para limitar texto
     */
    function limitText($text, $limit = 100, $suffix = '...') {
        if (strlen($text) <= $limit) {
            return $text;
        }
        return substr($text, 0, $limit) . $suffix;
    }

    /**
     * Função para verificar se é admin
     */
    function isAdmin() {
        return isset($_SESSION['user_admin']) && $_SESSION['user_admin'] == 1;
    }

    /**
     * Função para verificar se está logado
     */
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Função para redirecionar
     */
    function redirect($url) {
        header("Location: $url");
        exit;
    }

    /**
     * Função para obter URL atual
     */
    function getCurrentUrl() {
        $protocol = isHttps() ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Função para obter base URL
     */
    function getBaseUrl() {
        $protocol = isHttps() ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
    }

    /**
     * Função para verificar se é mobile
     */
    function isMobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    /**
     * Função para obter slug de texto
     */
    function createSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Função para verificar se arquivo existe
     */
    function fileExists($path) {
        return file_exists($path) && is_file($path);
    }

    /**
     * Função para criar diretório se não existir
     */
    function createDirectory($path) {
        if (!is_dir($path)) {
            return mkdir($path, 0755, true);
        }
        return true;
    }

    /**
     * Função para obter extensão de arquivo
     */
    function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Função para verificar se extensão é permitida
     */
    function isAllowedExtension($filename, $allowedExtensions = null) {
        if ($allowedExtensions === null) {
            $allowedExtensions = UPLOAD_ALLOWED_TYPES;
        }
        
        $extension = getFileExtension($filename);
        return in_array($extension, $allowedExtensions);
    }

    /**
     * Função para obter tamanho de arquivo formatado
     */
    function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    // ===== CONFIGURAÇÕES DE ERRO =====

    if (DEBUG_MODE) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
    }

    // ===== CONFIGURAÇÕES DE SESSÃO =====

    // ===== CONFIGURAÇÕES DE TIMEZONE =====

    if (function_exists('date_default_timezone_set')) {
        date_default_timezone_set('America/Sao_Paulo');
    }

    // ===== CONFIGURAÇÕES DE LOCALE =====

    setlocale(LC_ALL, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil');

    // ===== CONFIGURAÇÕES DE CHARSET =====

    ini_set('default_charset', 'UTF-8');
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');

    // ===== CONFIGURAÇÕES DE MEMORY =====

    ini_set('memory_limit', '256M');
    ini_set('max_execution_time', 300);

    // ===== CONFIGURAÇÕES DE UPLOAD =====

    ini_set('upload_max_filesize', '10M');
    ini_set('post_max_size', '10M');
    ini_set('max_file_uploads', 20);

    // ===== CONFIGURAÇÕES DE SEGURANÇA =====

    // Headers de segurança
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    // ===== FIM DAS CONFIGURAÇÕES ===== 
} // fecha proteção SIGILOSAS_CONFIG_LOADED 