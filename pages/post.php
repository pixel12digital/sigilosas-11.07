<?php
$post_id = (int)($_GET['id'] ?? 0);

if (!$post_id) {
    header('Location: index.php?page=blog');
    exit;
}

try {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();
    $pdo = $db->getConnection();
    
    // Buscar post específico
    $stmt = $pdo->prepare("
        SELECT id, titulo, resumo, conteudo, imagem, data_publicacao, autor, visualizacoes, status
        FROM blog_posts 
        WHERE id = ? AND status = 'publicado'
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header('Location: index.php?page=blog');
        exit;
    }
    
    // Incrementar visualizações
    $stmt = $pdo->prepare("UPDATE blog_posts SET visualizacoes = visualizacoes + 1 WHERE id = ?");
    $stmt->execute([$post_id]);
    
    $page_title = $post['titulo'];
    $page_description = $post['resumo'];
    
} catch (Exception $e) {
    header('Location: index.php?page=blog');
    exit;
}
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=blog" class="text-decoration-none">Blog</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($post['titulo']); ?></li>
        </ol>
    </nav>

    <!-- Post -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <article class="card border-0 shadow-lg">
                <!-- Imagem do post -->
                <?php if ($post['imagem']): ?>
                    <img src="uploads/blog/<?php echo htmlspecialchars($post['imagem']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($post['titulo']); ?>"
                         style="max-height: 400px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body p-4">
                    <!-- Meta informações -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary me-2">Blog</span>
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('d/m/Y H:i', strtotime($post['data_publicacao'])); ?>
                            </small>
                        </div>
                        <div class="d-flex align-items-center">
                            <small class="text-muted me-3">
                                <i class="fas fa-user"></i> 
                                <?php echo htmlspecialchars($post['autor']); ?>
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-eye"></i> 
                                <?php echo number_format($post['visualizacoes'] + 1); ?> visualizações
                            </small>
                        </div>
                    </div>
                    
                    <!-- Título -->
                    <h1 class="card-title h2 mb-3">
                        <?php echo htmlspecialchars($post['titulo']); ?>
                    </h1>
                    
                    <!-- Resumo -->
                    <div class="lead text-muted mb-4">
                        <?php echo htmlspecialchars($post['resumo']); ?>
                    </div>
                    
                    <!-- Conteúdo -->
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post['conteudo'])); ?>
                    </div>
                    
                    <!-- Separador -->
                    <hr class="my-4">
                    
                    <!-- Compartilhar -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Compartilhar:</small>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['titulo']); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-info ms-1">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode($post['titulo'] . ' - ' . $_SERVER['REQUEST_URI']); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-success ms-1">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                        
                        <a href="index.php?page=blog" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Voltar ao Blog
                        </a>
                    </div>
                </div>
            </article>
            
            <!-- Posts relacionados (opcional) -->
            <div class="mt-5">
                <h3 class="h5 mb-3">Posts Relacionados</h3>
                <div class="row">
                    <?php
                    // Buscar outros posts publicados
                    $stmt = $pdo->prepare("
                        SELECT id, titulo, resumo, imagem, data_publicacao, autor
                        FROM blog_posts 
                        WHERE status = 'publicado' AND id != ?
                        ORDER BY data_publicacao DESC
                        LIMIT 3
                    ");
                    $stmt->execute([$post_id]);
                    $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($related_posts as $related): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <?php if ($related['imagem']): ?>
                                    <img src="uploads/blog/<?php echo htmlspecialchars($related['imagem']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($related['titulo']); ?>"
                                         style="height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 150px;">
                                        <i class="fas fa-image fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="index.php?page=post&id=<?php echo $related['id']; ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($related['titulo']); ?>
                                        </a>
                                    </h6>
                                    <p class="card-text small text-muted">
                                        <?php echo htmlspecialchars(substr($related['resumo'], 0, 80)) . '...'; ?>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($related['autor']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.post-content {
    line-height: 1.8;
    font-size: 1.1rem;
}

.post-content p {
    margin-bottom: 1.5rem;
}

.post-content h2, .post-content h3, .post-content h4 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.post-content ul, .post-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.post-content blockquote {
    border-left: 4px solid var(--primary-color);
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #666;
}

@media (max-width: 768px) {
    .post-content {
        font-size: 1rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
}
</style> 