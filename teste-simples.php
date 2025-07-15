<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Teste simples funcionando!";
echo "<br>PHP version: " . phpversion();
echo "<br>Erro display: " . (ini_get('display_errors') ? 'ON' : 'OFF');
?> 