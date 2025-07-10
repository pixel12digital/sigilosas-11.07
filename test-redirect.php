<?php
// Teste de redirecionamento
echo "Testando redirecionamento...<br>";
echo "Caminho atual: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// Testar redirecionamento
echo "<br>Redirecionando em 3 segundos...<br>";
echo "<script>setTimeout(function() { window.location.href = '/Sigilosas-MySQL/acompanhante/'; }, 3000);</script>";
?> 