<?php
/**
 * Header do Painel Administrativo
 * Arquivo: includes/admin-header.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Auth.php';

$auth = getAuth();

$auth->requireAdmin();

$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Administração'; ?> - Sigilosas</title>
    <link href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3D263F;
            --secondary-color: #F3EAC2;
            --sidebar-width: 250px;
        }
        body {
            background-color: #fff;
        }
        .admin-header {
            padding: 0.5rem 0;
            background: var(--primary-color);
            color: var(--secondary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            z-index: 900;
        }
        .admin-header h4 {
            margin: 0;
            color: #fff;
            font-weight: bold;
            font-size: 2rem;
            white-space: nowrap;
            display: inline-block;
        }
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        .admin-content {
            margin-left: var(--sidebar-width);
            padding: 0 !important;
            min-height: calc(100vh - 80px);
            position: relative;
        }
        .sidebar-header {
            background: var(--primary-color);
            color: var(--secondary-color);
            padding: 1.5rem;
            text-align: center;
        }
        .sidebar-menu {
            padding: 1rem 0;
        }
        .sidebar-menu .nav-link {
            color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            border: none;
            transition: all 0.3s ease;
        }
        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            background-color: var(--secondary-color);
            color: var(--primary-color);
            border-left: 3px solid var(--primary-color);
        }
        .sidebar-menu .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            margin: 1rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .btn-primary {
            background: var(--primary-color);
            color: var(--secondary-color);
            border: none;
            border-radius: 10px;
        }
        .btn-primary:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead th {
            background: var(--primary-color);
            color: var(--secondary-color);
            border: none;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .admin-sidebar.show {
                transform: translateX(0);
            }
            .admin-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <h4><i class="fas fa-user-shield"></i> Administração</h4>
    </header>

    <!-- Sidebar -->
    <nav class="admin-sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">Sigilosas</h5>
            <small>Administração</small>
        </div>
        
        <div class="user-info">
            <small>
                <i class="fas fa-user"></i>
                <?php echo htmlspecialchars($currentUser['email']); ?>
            </small>
        </div>
        
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/" target="_blank">
                        <i class="fas fa-globe"></i> Ver Site
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'acompanhantes.php' ? 'active' : ''; ?>" 
                       href="acompanhantes.php">
                        <i class="fas fa-users"></i> Acompanhantes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'cidades-com-acompanhantes.php' ? 'active' : ''; ?>" 
                       href="cidades-com-acompanhantes.php">
                        <i class="fas fa-map-marker-alt"></i> Cidades com Acompanhantes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'blog.php' ? 'active' : ''; ?>" 
                       href="blog.php">
                        <i class="fas fa-blog"></i> Blog
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'avaliacoes.php' ? 'active' : ''; ?>" 
                       href="avaliacoes.php">
                        <i class="fas fa-star"></i> Avaliações
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'denuncias.php' ? 'active' : ''; ?>" 
                       href="denuncias.php">
                        <i class="fas fa-exclamation-triangle"></i> Denúncias
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="<?php echo SITE_URL; ?>/admin/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Content -->
    <main class="admin-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?> 