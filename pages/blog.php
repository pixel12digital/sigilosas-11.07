<?php
$page_title = "Blog";
$page_description = "Leia artigos, dicas e novidades sobre o mundo das acompanhantes de luxo no nosso blog.";

// Obter posts do blog
try {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();
    $pdo = $db->getConnection();
    
    // Buscar posts publicados
    $stmt = $pdo->prepare("
        SELECT id, titulo, resumo, conteudo, imagem, data_publicacao, autor, visualizacoes 
        FROM blog_posts 
        WHERE status = 'publicado' 
        ORDER BY data_publicacao DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $posts = [];
    $error = "Erro ao carregar posts do blog.";
}
?>

<div class="container py-5">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold text-primary mb-3">
                <i class="fas fa-blog"></i> Blog Sigilosas VIP
            </h1>
            <p class="lead text-muted">
                Artigos, dicas e novidades sobre o mundo das acompanhantes de luxo. 
                Fique por dentro das últimas tendências e informações.
            </p>
        </div>
    </div>
    
    <!-- Filtros e Busca -->
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="busca-blog" 
                                       placeholder="Buscar artigos...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="categoria-filtro">
                                <option value="">Todas as categorias</option>
                                <option value="dicas">Dicas</option>
                                <option value="tendencias">Tendências</option>
                                <option value="seguranca">Segurança</option>
                                <option value="lifestyle">Lifestyle</option>
                                <option value="novidades">Novidades</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Posts do Blog -->
    <div class="row" id="posts-container">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-lg-4 col-md-6 mb-4 post-item">
                    <div class="card border-0 shadow-lg h-100">
                        <!-- Imagem do post -->
                        <div class="position-relative">
                            <?php if ($post['imagem']): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/blog/<?php echo htmlspecialchars($post['imagem']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($post['titulo']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badge de categoria -->
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-primary">Blog</span>
                            </div>
                            
                            <!-- Visualizações -->
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-dark">
                                    <i class="fas fa-eye"></i> <?php echo number_format($post['visualizacoes']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <!-- Data e autor -->
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?>
                                </small>
                                <span class="mx-2">•</span>
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> 
                                    <?php echo htmlspecialchars($post['autor']); ?>
                                </small>
                            </div>
                            
                            <!-- Título -->
                            <h5 class="card-title">
                                <a href="index.php?page=post&id=<?php echo $post['id']; ?>" 
                                   class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($post['titulo']); ?>
                                </a>
                            </h5>
                            
                            <!-- Resumo -->
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo htmlspecialchars($post['resumo']); ?>
                            </p>
                            
                            <!-- Botão ler mais -->
                            <div class="mt-auto">
                                <a href="index.php?page=post&id=<?php echo $post['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-arrow-right"></i> Ler mais
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Posts de exemplo (quando não há posts no banco) -->
            <div class="col-lg-4 col-md-6 mb-4 post-item">
                <div class="card border-0 shadow-lg h-100">
                    <div class="position-relative">
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="fas fa-shield-alt fa-3x text-primary"></i>
                        </div>
                        <div class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-success">Segurança</span>
                        </div>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-dark">
                                <i class="fas fa-eye"></i> 1.2k
                            </span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> 15/12/2024
                            </small>
                            <span class="mx-2">•</span>
                            <small class="text-muted">
                                <i class="fas fa-user"></i> Equipe Sigilosas
                            </small>
                        </div>
                        <h5 class="card-title">
                            <a href="#" class="text-decoration-none text-dark">
                                Como Garantir sua Segurança ao Contratar Acompanhantes
                            </a>
                        </h5>
                        <p class="card-text text-muted flex-grow-1">
                            Dicas essenciais para garantir uma experiência segura e tranquila 
                            ao contratar serviços de acompanhantes de luxo.
                        </p>
                        <div class="mt-auto">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-arrow-right"></i> Ler mais
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4 post-item">
                <div class="card border-0 shadow-lg h-100">
                    <div class="position-relative">
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="fas fa-star fa-3x text-warning"></i>
                        </div>
                        <div class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-info">Dicas</span>
                        </div>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-dark">
                                <i class="fas fa-eye"></i> 856
                            </span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> 12/12/2024
                            </small>
                            <span class="mx-2">•</span>
                            <small class="text-muted">
                                <i class="fas fa-user"></i> Maria Santos
                            </small>
                        </div>
                        <h5 class="card-title">
                            <a href="#" class="text-decoration-none text-dark">
                                10 Dicas para uma Experiência Inesquecível
                            </a>
                        </h5>
                        <p class="card-text text-muted flex-grow-1">
                            Descubra como tornar sua experiência com acompanhantes de luxo 
                            ainda mais especial e memorável.
                        </p>
                        <div class="mt-auto">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-arrow-right"></i> Ler mais
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4 post-item">
                <div class="card border-0 shadow-lg h-100">
                    <div class="position-relative">
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="fas fa-trending-up fa-3x text-success"></i>
                        </div>
                        <div class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-warning">Tendências</span>
                        </div>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-dark">
                                <i class="fas fa-eye"></i> 2.1k
                            </span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> 10/12/2024
                            </small>
                            <span class="mx-2">•</span>
                            <small class="text-muted">
                                <i class="fas fa-user"></i> João Silva
                            </small>
                        </div>
                        <h5 class="card-title">
                            <a href="#" class="text-decoration-none text-dark">
                                Tendências do Mercado de Acompanhantes em 2024
                            </a>
                        </h5>
                        <p class="card-text text-muted flex-grow-1">
                            Conheça as principais tendências e mudanças no mercado de 
                            acompanhantes de luxo para o próximo ano.
                        </p>
                        <div class="mt-auto">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-arrow-right"></i> Ler mais
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Paginação -->
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto">
            <nav aria-label="Navegação do blog">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">
                            Próxima <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    
    <!-- Newsletter -->
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-lg bg-primary text-white">
                <div class="card-body p-5 text-center">
                    <h4 class="mb-3">
                        <i class="fas fa-envelope"></i> Fique por Dentro
                    </h4>
                    <p class="lead mb-4">
                        Receba nossos artigos e novidades diretamente no seu email
                    </p>
                    <form class="row g-3 justify-content-center">
                        <div class="col-md-8">
                            <input type="email" 
                                   class="form-control form-control-lg" 
                                   placeholder="Seu email">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-light btn-lg w-100">
                                <i class="fas fa-paper-plane"></i> Inscrever
                            </button>
                        </div>
                    </form>
                    <small class="text-light opacity-75">
                        Não enviamos spam. Você pode cancelar a qualquer momento.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Busca e filtros do blog
document.addEventListener('DOMContentLoaded', function() {
    const buscaInput = document.getElementById('busca-blog');
    const categoriaSelect = document.getElementById('categoria-filtro');
    const postsContainer = document.getElementById('posts-container');
    const postItems = document.querySelectorAll('.post-item');
    
    function filtrarPosts() {
        const termoBusca = buscaInput.value.toLowerCase();
        const categoria = categoriaSelect.value.toLowerCase();
        
        postItems.forEach(item => {
            const titulo = item.querySelector('.card-title').textContent.toLowerCase();
            const resumo = item.querySelector('.card-text').textContent.toLowerCase();
            const categoriaBadge = item.querySelector('.badge').textContent.toLowerCase();
            
            const matchBusca = titulo.includes(termoBusca) || resumo.includes(termoBusca);
            const matchCategoria = !categoria || categoriaBadge.includes(categoria);
            
            if (matchBusca && matchCategoria) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    buscaInput.addEventListener('input', filtrarPosts);
    categoriaSelect.addEventListener('change', filtrarPosts);
});
</script> 