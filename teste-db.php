<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Teste de conexão com banco de dados<br>";

try {
    require_once __DIR__ . '/config/config.php';
    echo "Config carregado<br>";
    
    require_once __DIR__ . '/config/database.php';
    echo "Database carregado<br>";
    
    $db = getDB();
    echo "Conexão obtida<br>";
    
    $result = $db->fetch("SELECT 1 as test");
    echo "Query executada: " . print_r($result, true) . "<br>";
    
    echo "Tudo funcionando!";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?> 