<?php
require_once __DIR__ . '/../config/config.php';
/**
 * Dashboard da Acompanhante
 * Arquivo: acompanhante/index.php
 */

$page_title = 'Dashboard';
$page_description = 'Painel de controle da acompanhante';

if (!isset($db)) {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();
}

header('Location: perfil.php');
exit; 