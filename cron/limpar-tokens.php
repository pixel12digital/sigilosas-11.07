<?php
/**
 * Script para limpar tokens de recuperação expirados
 * Executar via cron: 0 */6 * * * php /caminho/para/cron/limpar-tokens.php
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../core/Email.php';

// Log do início da execução
$log_file = '../logs/cron-tokens-' . date('Y-m-d') . '.log';
$timestamp = date('Y-m-d H:i:s');

file_put_contents($log_file, "[{$timestamp}] Iniciando limpeza de tokens expirados\n", FILE_APPEND);

try {
    // Limpar tokens expirados
    $pdo = Database::getConnection();
    
    // Contar tokens antes da limpeza
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM recuperacao_senha WHERE expira < NOW()");
    $stmt->execute();
    $tokens_expirados = $stmt->fetchColumn();
    
    // Deletar tokens expirados
    $stmt = $pdo->prepare("DELETE FROM recuperacao_senha WHERE expira < NOW()");
    $stmt->execute();
    $tokens_deletados = $stmt->rowCount();
    
    // Log do resultado
    $mensagem = "[{$timestamp}] Limpeza concluída: {$tokens_expirados} tokens expirados encontrados, {$tokens_deletados} deletados\n";
    file_put_contents($log_file, $mensagem, FILE_APPEND);
    
    // Verificar se há tokens válidos restantes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM recuperacao_senha WHERE expira > NOW() AND usado = 0");
    $stmt->execute();
    $tokens_validos = $stmt->fetchColumn();
    
    $mensagem = "[{$timestamp}] Tokens válidos restantes: {$tokens_validos}\n";
    file_put_contents($log_file, $mensagem, FILE_APPEND);
    
    echo "Limpeza concluída com sucesso!\n";
    echo "Tokens expirados deletados: {$tokens_deletados}\n";
    echo "Tokens válidos restantes: {$tokens_validos}\n";
    
} catch (Exception $e) {
    $erro = "[{$timestamp}] ERRO: " . $e->getMessage() . "\n";
    file_put_contents($log_file, $erro, FILE_APPEND);
    
    echo "Erro durante a limpeza: " . $e->getMessage() . "\n";
    exit(1);
}

// Log do fim da execução
$mensagem = "[{$timestamp}] Script finalizado\n";
file_put_contents($log_file, $mensagem, FILE_APPEND); 