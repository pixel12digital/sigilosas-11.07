<?php
$page_title = "Página Não Encontrada";
$page_description = "A página que você está procurando não foi encontrada.";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <!-- Ícone de erro -->
            <div class="mb-4">
                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 6rem;"></i>
            </div>
            
            <!-- Título -->
            <h1 class="display-1 fw-bold text-primary mb-3">404</h1>
            <h2 class="h3 text-muted mb-4">Página Não Encontrada</h2>
            
            <!-- Mensagem -->
            <p class="lead text-muted mb-5">
                Desculpe, a página que você está procurando não existe ou foi movida. 
                Verifique o endereço ou navegue pelas opções abaixo.
            </p>
            
            <!-- Botões de navegação -->
            <div class="d-grid gap-3 d-md-flex justify-content-md-center mb-5">
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-home"></i> Voltar ao Início
                </a>
                <a href="index.php?page=acompanhantes" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-users"></i> Ver Acompanhantes
                </a>
            </div>
            
            <!-- Links úteis -->
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-compass"></i> Páginas Populares
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="index.php?page=acompanhantes" class="text-decoration-none">
                                <div class="d-flex align-items-center p-2 rounded hover-bg-light">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <span>Acompanhantes</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="index.php?page=blog" class="text-decoration-none">
                                <div class="d-flex align-items-center p-2 rounded hover-bg-light">
                                    <i class="fas fa-blog text-success me-2"></i>
                                    <span>Blog</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="index.php?page=contato" class="text-decoration-none">
                                <div class="d-flex align-items-center p-2 rounded hover-bg-light">
                                    <i class="fas fa-envelope text-info me-2"></i>
                                    <span>Contato</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="index.php?page=sobre" class="text-decoration-none">
                                <div class="d-flex align-items-center p-2 rounded hover-bg-light">
                                    <i class="fas fa-info-circle text-warning me-2"></i>
                                    <span>Sobre Nós</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Busca -->
            <div class="card border-0 shadow-lg mt-4">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-search"></i> Procurar no Site
                    </h6>
                    <form action="index.php?page=acompanhantes" method="GET" class="d-flex">
                        <input type="hidden" name="page" value="acompanhantes">
                        <input type="text" 
                               class="form-control me-2" 
                               name="busca" 
                               placeholder="Digite sua busca...">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-bg-light:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s;
}
</style> 