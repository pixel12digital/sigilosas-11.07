<?php
session_start();
if (!isset($_SESSION['teste'])) {
    $_SESSION['teste'] = 'funcionando';
    echo "Sessão criada. Recarregue a página.";
} else {
    echo "Sessão ativa: " . $_SESSION['teste'];
} 