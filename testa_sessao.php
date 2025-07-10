<?php
session_start();
if (!isset($_SESSION['teste'])) {
    $_SESSION['teste'] = rand(1000,9999);
}
echo 'Sessão: ' . $_SESSION['teste']; 