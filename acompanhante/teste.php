<?php
// Iniciar sessão com o nome correto para o painel acompanhante
session_name('sigilosas_acompanhante_session');
session_start();
echo "<h1>Teste do Painel da Acompanhante</h1>";
echo "<p>Sessão ID: " . ($_SESSION['acompanhante_id'] ?? 'NÃO DEFINIDO') . "</p>";
echo "<p>Sessão Nome: " . ($_SESSION['acompanhante_nome'] ?? 'NÃO DEFINIDO') . "</p>";
echo "<p>Sessão Status: " . ($_SESSION['acompanhante_status'] ?? 'NÃO DEFINIDO') . "</p>";
echo "<p>Sessão Aprovada: " . ($_SESSION['acompanhante_aprovada'] ?? 'NÃO DEFINIDO') . "</p>";
echo "<p>URL Atual: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
?> 