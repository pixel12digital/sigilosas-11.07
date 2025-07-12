<?php
require_once __DIR__ . '/../config/config.php';
echo '<!-- SITE_URL: ' . SITE_URL . ' -->'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Sigilosas VIP - Acompanhantes de Luxo</title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Encontre as melhores acompanhantes de luxo do Brasil. Perfis verificados e seguros.'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/img/favicon.ico">
    
    <!-- CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <style>
        /* Garantir que o dropdown apareça */
        .dropdown-menu {
            z-index: 1050 !important;
        }
        .navbar-nav .dropdown-menu {
            margin-top: 0;
        }
    </style>
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Sigilosas VIP">
    <meta property="og:description" content="<?php echo isset($page_description) ? $page_description : 'Encontre as melhores acompanhantes de luxo do Brasil.'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'GA_MEASUREMENT_ID');
    </script>
</head>
<body>
    <!-- Popup Aviso +18 -->
    <div id="aviso18" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Aviso Importante
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-user-shield fa-3x text-danger mb-3"></i>
                    <h4>Conteúdo para Maiores de 18 Anos</h4>
                    <p class="mb-3">Este site contém conteúdo adulto destinado apenas a pessoas maiores de 18 anos.</p>
                    <p class="mb-4">Ao continuar, você confirma que é maior de idade e concorda com nossos <a href="?page=termos">Termos de Uso</a>.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" onclick="window.close()">Sair</button>
                    <button type="button" class="btn btn-primary" onclick="aceitarAviso()">Confirmar e Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="<?php echo SITE_URL; ?>/assets/img/logo.png" alt="Sigilosas VIP" height="40">
            </a>
            
            <!-- Botão mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/" ><i class="fas fa-home"></i> Home</a>
                    </li>
                    <!-- Item 'Acompanhantes' removido -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=blog">
                            <i class="fas fa-blog"></i> Blog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=contato">
                            <i class="fas fa-envelope"></i> Contato
                        </a>
                    </li>
                </ul>
                
                <!-- Menu direito -->
                <ul class="navbar-nav">
                    <?php if (isset($auth) && $auth && $auth->isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i> Minha Conta
                            </button>
                            <ul class="dropdown-menu">
                                <?php if (isset($_SESSION['acompanhante_id'])): ?>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/acompanhante/index.php"><i class="fas fa-user-friends"></i> Painel Acompanhante</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="index.php?page=perfil"><i class="fas fa-user-edit"></i> Meu Perfil</a></li>
                                <li><a class="dropdown-item" href="api/logout-simple.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/login-acompanhante.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary btn-sm ms-2" href="<?php echo SITE_URL; ?>/pages/cadastro-acompanhante.php">
                                <i class="fas fa-user-plus"></i> Cadastrar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <!-- Conteúdo principal -->
    <main style="margin-top: 76px;">
        <!-- Loading Spinner -->
        <div id="loading" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
        </div>

        <!-- Mensagens de alerta -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['warning'])): ?>
            <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['warning']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['warning']); ?>
        <?php endif; ?>
    </main>

    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
        // Inicialização manual dos dropdowns do Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Bootstrap carregado:', typeof bootstrap !== 'undefined');
            
            // Inicializar dropdowns manualmente
            const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
            const dropdownList = [...dropdownElementList].map(dropdownToggleEl => {
                console.log('Inicializando dropdown:', dropdownToggleEl);
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
            
            console.log('Dropdowns inicializados:', dropdownList.length);
            
            // Adicionar evento de clique para debug
            dropdownElementList.forEach(function(element) {
                element.addEventListener('click', function(e) {
                    console.log('Dropdown clicado:', e.target);
                });
            });
        });
    </script>
</body>
</html> 