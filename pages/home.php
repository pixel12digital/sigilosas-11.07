<?php
/**
 * Página Inicial - Site Público
 * Arquivo: pages/home.php
 */

require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Sigilosas - Encontre Acompanhantes de Luxo';
$pageDescription = 'Encontre acompanhantes de luxo em sua cidade. Perfis verificados e seguros.';

$db = getDB();

// Buscar acompanhantes em destaque (aprovadas e verificadas)
$acompanhantes_destaque = $db->fetchAll("
    SELECT a.*, c.nome as cidade_nome, e.nome as estado_nome, e.uf as estado_uf,
           COALESCE((SELECT COUNT(*) FROM fotos f WHERE f.acompanhante_id = a.id), 0) as total_fotos
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON c.estado_id = e.id
    WHERE a.status = 'aprovado' AND a.bloqueado = 0
    ORDER BY a.verificado DESC, a.created_at DESC
    LIMIT 12
");

// Buscar estatísticas
$stats = [
    'total_acompanhantes' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE status = 'aprovado' AND bloqueado = 0")['total'] ?? 0,
    'total_cidades' => $db->fetch("SELECT COUNT(DISTINCT cidade_id) as total FROM acompanhantes WHERE status = 'aprovado' AND bloqueado = 0")['total'] ?? 0,
    'total_estados' => $db->fetch("SELECT COUNT(DISTINCT e.id) as total FROM acompanhantes a LEFT JOIN cidades c ON a.cidade_id = c.id LEFT JOIN estados e ON c.estado_id = e.id WHERE a.status = 'aprovado' AND a.bloqueado = 0")['total'] ?? 0
];

// Buscar estados para filtro
$estados = $db->fetchAll("
    SELECT e.*, COALESCE(COUNT(a.id), 0) as total_acompanhantes
    FROM estados e
    LEFT JOIN acompanhantes a ON e.id = a.estado_id AND a.status = 'aprovado' AND a.bloqueado = 0
    GROUP BY e.id, e.nome, e.uf
    HAVING total_acompanhantes > 0
    ORDER BY total_acompanhantes DESC
    LIMIT 10
");
?>

<!-- Banner Principal -->
<section class="hero-section position-relative">
    <div class="hero-bg" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../assets/img/imagem_banner.png') center/cover;">
        <div class="container">
            <div class="row min-vh-75 align-items-center">
                <div class="col-lg-8 col-md-10 mx-auto text-center text-white">
                    <h1 class="display-4 fw-bold mb-4">
                        Encontre Acompanhantes de Luxo
                    </h1>
                    <p class="lead mb-5">
                        Perfis verificados e seguros em sua cidade. 
                        Encontre a companhia perfeita para momentos especiais.
                    </p>
                    
                    <!-- Formulário de Busca -->
                    <div class="search-form bg-white bg-opacity-90 p-4 rounded-3">
                        <form action="acompanhantes.php" method="get" class="row g-3">
                            <div class="col-md-4">
                                <select name="estado" class="form-select" required>
                                    <option value="">Selecione o Estado</option>
                                    <?php foreach ($estados as $estado): ?>
                                        <option value="<?php echo $estado['id']; ?>">
                                            <?php echo htmlspecialchars($estado['nome']); ?> (<?php echo $estado['uf']; ?>) 
                                            - <?php echo $estado['total_acompanhantes']; ?> perfis
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="cidade" class="form-select" id="cidade-select">
                                    <option value="">Selecione a Cidade</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Estatísticas -->
<section class="stats-section py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="stat-item">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h3 class="fw-bold"><?php echo number_format($stats['total_acompanhantes']); ?></h3>
                    <p class="text-muted">Acompanhantes Verificadas</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stat-item">
                    <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                    <h3 class="fw-bold"><?php echo number_format($stats['total_cidades']); ?></h3>
                    <p class="text-muted">Cidades Atendidas</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stat-item">
                    <i class="fas fa-star fa-3x text-primary mb-3"></i>
                    <h3 class="fw-bold"><?php echo number_format($stats['total_estados']); ?></h3>
                    <p class="text-muted">Estados Brasileiros</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Acompanhantes em Destaque -->
<section class="featured-section py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="section-title">Acompanhantes em Destaque</h2>
                <p class="text-muted">Perfis verificados e selecionados especialmente para você</p>
            </div>
        </div>
        
        <div class="row">
            <?php if (empty($acompanhantes_destaque)): ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Nenhuma acompanhante disponível no momento.</p>
                </div>
            <?php else: ?>
                <?php foreach ($acompanhantes_destaque as $acompanhante): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card acompanhante-card h-100 shadow-sm">
                            <div class="card-img-top position-relative">
                                <?php if ($acompanhante['foto']): ?>
                                    <img src="../uploads/<?php echo $acompanhante['foto']; ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($acompanhante['nome']); ?>"
                                         style="height: 250px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary d-flex align-items-center justify-content-center" 
                                         style="height: 250px;">
                                        <i class="fas fa-user fa-3x text-white"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Badges -->
                                <div class="position-absolute top-0 start-0 p-2">
                                    <?php if ($acompanhante['verificado']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Verificada
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($acompanhante['total_fotos'] > 0): ?>
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-info">
                                            <i class="fas fa-images"></i> <?php echo $acompanhante['total_fotos']; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($acompanhante['nome']); ?></h5>
                                <p class="card-text text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($acompanhante['cidade_nome']); ?>, <?php echo $acompanhante['estado_uf']; ?>
                                </p>
                                
                                <?php if ($acompanhante['idade']): ?>
                                    <p class="card-text small mb-2">
                                        <i class="fas fa-birthday-cake"></i> <?php echo $acompanhante['idade']; ?> anos
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($acompanhante['tipo_servico']): ?>
                                    <p class="card-text small mb-3">
                                        <i class="fas fa-tag"></i> 
                                        <?php
                                        $tipos = [
                                            'massagem' => 'Massagem',
                                            'acompanhante' => 'Acompanhante',
                                            'ambos' => 'Massagem & Acompanhante'
                                        ];
                                        echo $tipos[$acompanhante['tipo_servico']] ?? 'Serviços Diversos';
                                        ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer bg-transparent border-0">
                                <a href="acompanhante.php?id=<?php echo $acompanhante['id']; ?>" 
                                   class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-eye"></i> Ver Perfil
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="acompanhantes.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-list"></i> Ver Todas as Acompanhantes
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Seção de Benefícios -->
<section class="benefits-section py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="section-title">Por que escolher a Sigilosas?</h2>
                <p class="text-muted">Segurança, qualidade e discrição garantidas</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="benefit-item text-center">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h5>Perfis Verificados</h5>
                    <p class="text-muted">Todas as acompanhantes passam por verificação rigorosa</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="benefit-item text-center">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-user-secret fa-3x text-primary"></i>
                    </div>
                    <h5>100% Discreto</h5>
                    <p class="text-muted">Seus dados e informações são totalmente confidenciais</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="benefit-item text-center">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-star fa-3x text-primary"></i>
                    </div>
                    <h5>Qualidade Premium</h5>
                    <p class="text-muted">Acompanhantes selecionadas com critérios rigorosos</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="benefit-item text-center">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-headset fa-3x text-primary"></i>
                    </div>
                    <h5>Suporte 24/7</h5>
                    <p class="text-muted">Atendimento disponível a qualquer momento</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-3">Pronto para encontrar sua acompanhante ideal?</h3>
                <p class="mb-0">Cadastre-se gratuitamente e tenha acesso a perfis exclusivos</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="cadastro.php" class="btn btn-light btn-lg">
                    <i class="fas fa-user-plus"></i> Cadastrar-se
                </a>
            </div>
        </div>
    </div>
</section>

<script>
// Carregar cidades quando estado for selecionado
document.querySelector('select[name="estado"]').addEventListener('change', function() {
    const estadoId = this.value;
    const cidadeSelect = document.getElementById('cidade-select');
    
    if (estadoId) {
        // Fazer requisição AJAX para buscar cidades
        fetch(`../api/cidades.php?estado_id=${estadoId}`)
            .then(response => response.json())
            .then(data => {
                cidadeSelect.innerHTML = '<option value="">Selecione a Cidade</option>';
                
                if (data.success && data.cidades) {
                    data.cidades.forEach(cidade => {
                        const option = document.createElement('option');
                        option.value = cidade.id;
                        option.textContent = cidade.nome;
                        cidadeSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao carregar cidades:', error);
            });
    } else {
        cidadeSelect.innerHTML = '<option value="">Selecione a Cidade</option>';
    }
});
</script>

<style>
.hero-section {
    background-color: #f8f9fa;
}

.hero-bg {
    min-height: 75vh;
    display: flex;
    align-items: center;
}

.min-vh-75 {
    min-height: 75vh;
}

.acompanhante-card {
    transition: transform 0.2s ease-in-out;
}

.acompanhante-card:hover {
    transform: translateY(-5px);
}

.benefit-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(13, 110, 253, 0.1);
    border-radius: 50%;
}

.section-title {
    font-weight: 700;
    margin-bottom: 1rem;
}

.stats-section .stat-item h3 {
    color: #0d6efd;
    font-size: 2.5rem;
}

@media (max-width: 768px) {
    .hero-bg {
        min-height: 60vh;
    }
    
    .search-form {
        margin: 0 1rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?> 