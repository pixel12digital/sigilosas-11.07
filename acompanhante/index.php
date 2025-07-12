<?php
require_once __DIR__ . '/../config/config.php';
/**
 * Dashboard da Acompanhante
 * Arquivo: acompanhante/index.php
 */

$page_title = 'Dashboard';
$page_description = 'Painel de controle da acompanhante';

include __DIR__ . '/../includes/header.php';

if (!isset($db)) {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();
}

header('Location: perfil.php');
exit;
?>
<main class="main-content">
    <h1>Painel da Acompanhante</h1>
    <!-- Adicione aqui os cards, tabelas ou informações do painel -->
</main>
                                    <?php 
include __DIR__ . '/includes/footer.php';
?> 