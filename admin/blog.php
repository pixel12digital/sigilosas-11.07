<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}
require_once '../config/database.php';
$pageTitle = 'Blog';
require_once '../includes/admin-header.php';

$db = getDB();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $titulo = trim($_POST['titulo']);
                $resumo = trim($_POST['resumo']);
                $conteudo = trim($_POST['conteudo']);
                $autor = trim($_POST['autor']);
                $status = $_POST['status'];
                
                // Upload da imagem
                $imagem = '';
                if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/blog/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $imagem = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $imagem;
                        
                        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
                            // Imagem enviada com sucesso
                        } else {
                            $error = 'Erro ao fazer upload da imagem.';
                        }
                    } else {
                        $error = 'Formato de imagem não permitido.';
                    }
                }
                
                if (empty($error)) {
                    $data = [
                        'titulo' => $titulo,
                        'resumo' => $resumo,
                        'conteudo' => $conteudo,
                        'autor' => $autor,
                        'imagem' => $imagem,
                        'status' => $status,
                        'data_publicacao' => date('Y-m-d H:i:s'),
                        'visualizacoes' => 0
                    ];
                    
                    $id = $db->insert('blog_posts', $data);
                    if ($id) {
                        $success = 'Post criado com sucesso!';
                    } else {
                        $error = 'Erro ao criar post.';
                    }
                }
                break;
                
            case 'update':
                $id = (int)$_POST['post_id'];
                $titulo = trim($_POST['titulo']);
                $resumo = trim($_POST['resumo']);
                $conteudo = trim($_POST['conteudo']);
                $autor = trim($_POST['autor']);
                $status = $_POST['status'];
                
                $data = [
                    'titulo' => $titulo,
                    'resumo' => $resumo,
                    'conteudo' => $conteudo,
                    'autor' => $autor,
                    'status' => $status
                ];
                
                // Upload da nova imagem se fornecida
                if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/blog/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $imagem = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $imagem;
                        
                        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
                            $data['imagem'] = $imagem;
                        }
                    }
                }
                
                if ($db->update('blog_posts', $data, 'id = ?', [$id])) {
                    $success = 'Post atualizado com sucesso!';
                } else {
                    $error = 'Erro ao atualizar post.';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['post_id'];
                if ($db->delete('blog_posts', 'id = ?', [$id])) {
                    $success = 'Post excluído com sucesso!';
                } else {
                    $error = 'Erro ao excluir post.';
                }
                break;
        }
    }
}

// Buscar posts
$posts = $db->fetchAll('
    SELECT id, titulo, resumo, autor, imagem, status, data_publicacao, visualizacoes
    FROM blog_posts 
    ORDER BY data_publicacao DESC
');

// Buscar post para edição
$edit_post = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_post = $db->fetch('SELECT * FROM blog_posts WHERE id = ?', [$_GET['edit']]);
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">
                <i class="fas fa-blog"></i> Gerenciar Blog
            </h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                <i class="fas fa-plus"></i> Novo Post
            </button>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Lista de Posts -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Posts do Blog
        </div>
        <div class="card-body">
            <?php if (empty($posts)): ?>
                <p class="text-muted text-center">Nenhum post encontrado.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagem</th>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Status</th>
                                <th>Visualizações</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?php echo $post['id']; ?></td>
                                    <td>
                                        <?php if ($post['imagem']): ?>
                                            <img src="../uploads/blog/<?php echo htmlspecialchars($post['imagem']); ?>" 
                                                 alt="Imagem" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px; border-radius: 5px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($post['titulo']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($post['resumo'], 0, 100)) . '...'; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($post['autor']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = $post['status'] === 'publicado' ? 'badge bg-success' : 'badge bg-warning';
                                        $statusText = $post['status'] === 'publicado' ? 'Publicado' : 'Rascunho';
                                        ?>
                                        <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <i class="fas fa-eye"></i> <?php echo number_format($post['visualizacoes']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($post['data_publicacao'])); ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../index.php?page=post&id=<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-outline-info" title="Visualizar" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este post?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Criar Post -->
<div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Novo Post
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="resumo" class="form-label">Resumo *</label>
                        <textarea class="form-control" id="resumo" name="resumo" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="conteudo" class="form-label">Conteúdo *</label>
                        <textarea class="form-control" id="conteudo" name="conteudo" rows="10" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="autor" class="form-label">Autor *</label>
                                <input type="text" class="form-control" id="autor" name="autor" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="rascunho">Rascunho</option>
                                    <option value="publicado">Publicado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="imagem" class="form-label">Imagem de Destaque</label>
                        <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
                        <small class="text-muted">Formatos: JPG, PNG, GIF, WebP. Tamanho máximo: 10MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Criar Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Post -->
<?php if ($edit_post): ?>
<div class="modal fade show" id="editPostModal" tabindex="-1" style="display: block;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Post
                </h5>
                <a href="blog.php" class="btn-close"></a>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="post_id" value="<?php echo $edit_post['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="edit_titulo" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="edit_titulo" name="titulo" 
                               value="<?php echo htmlspecialchars($edit_post['titulo']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_resumo" class="form-label">Resumo *</label>
                        <textarea class="form-control" id="edit_resumo" name="resumo" rows="3" required><?php echo htmlspecialchars($edit_post['resumo']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_conteudo" class="form-label">Conteúdo *</label>
                        <textarea class="form-control" id="edit_conteudo" name="conteudo" rows="10" required><?php echo htmlspecialchars($edit_post['conteudo']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_autor" class="form-label">Autor *</label>
                                <input type="text" class="form-control" id="edit_autor" name="autor" 
                                       value="<?php echo htmlspecialchars($edit_post['autor']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status">
                                    <option value="rascunho" <?php if($edit_post['status'] === 'rascunho') echo 'selected'; ?>>Rascunho</option>
                                    <option value="publicado" <?php if($edit_post['status'] === 'publicado') echo 'selected'; ?>>Publicado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_imagem" class="form-label">Nova Imagem de Destaque</label>
                        <input type="file" class="form-control" id="edit_imagem" name="imagem" accept="image/*">
                        <small class="text-muted">Deixe em branco para manter a imagem atual</small>
                        
                        <?php if ($edit_post['imagem']): ?>
                            <div class="mt-2">
                                <label class="form-label">Imagem Atual:</label>
                                <img src="../uploads/blog/<?php echo htmlspecialchars($edit_post['imagem']); ?>" 
                                     alt="Imagem atual" style="max-width: 200px; height: auto; border-radius: 5px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="blog.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show"></div>
<?php endif; ?>

<script>
// Fechar modal de edição ao clicar no backdrop
document.addEventListener('DOMContentLoaded', function() {
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.addEventListener('click', function() {
            window.location.href = 'blog.php';
        });
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?> 