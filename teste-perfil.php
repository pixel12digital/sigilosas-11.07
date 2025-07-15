<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Iniciando teste do perfil<br>";

try {
    require_once __DIR__ . '/config/config.php';
    echo "Config carregado<br>";
    
    session_name('sigilosas_acompanhante_session');
    session_start();
    echo "Sessão iniciada<br>";
    
    if (!isset($_SESSION['acompanhante_id'])) {
        echo "Usuário não logado<br>";
        exit;
    }
    
    echo "Usuário logado: " . $_SESSION['acompanhante_id'] . "<br>";
    
    $page_title = 'Editar Perfil';
    $page_description = 'Edite suas informações pessoais e profissionais';
    
    echo "Incluindo header<br>";
    include __DIR__ . '/includes/header.php';
    echo "Header incluído<br>";
    
    echo "Teste concluído com sucesso!";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
}
?> 