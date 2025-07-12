<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'core/Auth.php';

// Inicializar classes
$auth = new Auth();

// Processar mensagens de sucesso via GET
if (isset($_GET['success'])) {
    $_SESSION['success'] = $_GET['success'];
}

// Obter a página solicitada
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Definir páginas permitidas
$public_pages = [
    'home' => 'pages/home.php',
    'login' => 'pages/login.php',
    'cadastro' => 'pages/cadastro.php',
    'acompanhantes' => 'pages/acompanhantes.php',
    'acompanhante' => 'pages/acompanhante.php',
    'contato' => 'pages/contato.php',
    'sobre' => 'pages/sobre.php',
    'privacidade' => 'pages/privacidade.php',
    'termos' => 'pages/termos.php',
    'blog' => 'pages/blog.php',
    'post' => 'pages/post.php',
    'perfil' => 'pages/perfil.php'
];

// Verificar se a página existe
if (isset($public_pages[$page])) {
    $page_file = $public_pages[$page];
    
    // Verificar se a página precisa de autenticação
    $auth_required_pages = ['perfil'];
    if (in_array($page, $auth_required_pages) && !$auth->isLoggedIn()) {
        $_SESSION['error'] = 'Você precisa estar logado para acessar esta página.';
        header('Location: index.php?page=login');
        exit;
    }
} else {
    // Página não encontrada
    http_response_code(404);
    $page_file = 'pages/404.php';
}

// Páginas que precisam processar formulários antes do header
$pages_with_forms = ['login', 'cadastro'];

// Se a página tem formulários, deixar ela processar antes de incluir o header
if (in_array($page, $pages_with_forms)) {
    // Incluir a página diretamente (ela vai incluir o header)
    include $page_file;
} else {
    // Para outras páginas, incluir header primeiro
    include __DIR__ . '/includes/header.php';
    include $page_file;
    include 'includes/footer.php';
}
?> 