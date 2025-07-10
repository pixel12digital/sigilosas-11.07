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
    WHERE a.status = 'aprovado'
    ORDER BY a.verificado DESC, a.created_at DESC
    LIMIT 12
");

// Buscar estatísticas
$stats = [
    'total_acompanhantes' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE status = 'aprovado'")['total'] ?? 0,
    'total_cidades' => $db->fetch("SELECT COUNT(DISTINCT cidade_id) as total FROM acompanhantes WHERE status = 'aprovado'")['total'] ?? 0,
    'total_estados' => $db->fetch("SELECT COUNT(DISTINCT e.id) as total FROM acompanhantes a LEFT JOIN cidades c ON a.cidade_id = c.id LEFT JOIN estados e ON c.estado_id = e.id WHERE a.status = 'aprovado'")['total'] ?? 0
];

// Buscar estados para filtro
$estados = $db->fetchAll("
    SELECT e.*, COALESCE(COUNT(a.id), 0) as total_acompanhantes
    FROM estados e
    LEFT JOIN acompanhantes a ON e.id = a.estado_id AND a.status = 'aprovado'
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
                        <form id="filtro-form" class="row g-3">
                            <div class="col-md-4">
                                <select name="estado" class="form-select" id="estado" required>
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
                                <select name="cidade" class="form-select" id="cidade-select" required>
                                    <option value="">Selecione a Cidade</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <span class="spinner-border spinner-busca" style="display:none;width:1.5em;height:1.5em;vertical-align:middle;"></span>
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Resultados -->
                    <div class="container mt-4">
                        <div id="acompanhantes-result" class="row g-4"></div>
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
// Carregar cidades via AJAX ao selecionar estado
const estadoSelect = document.getElementById('estado');
const cidadeSelect = document.getElementById('cidade-select');

estadoSelect.addEventListener('change', function() {
    const estadoId = this.value;
    cidadeSelect.innerHTML = '<option value="">Carregando...</option>';
    if (estadoId) {
        fetch('/Sigilosas-MySQL/api/cidades.php?estado_id=' + estadoId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Selecione a Cidade</option>';
                data.forEach(cidade => {
                    options += `<option value="${cidade.id}">${cidade.nome}</option>`;
                });
                cidadeSelect.innerHTML = options;
            });
    } else {
        cidadeSelect.innerHTML = '<option value="">Selecione a Cidade</option>';
    }
});

// Busca AJAX de acompanhantes
const form = document.getElementById('filtro-form');
const resultDiv = document.getElementById('acompanhantes-result');
const buscarBtn = form.querySelector('button[type="submit"]');
const spinnerBusca = buscarBtn.querySelector('.spinner-busca');
console.log('[DEBUG] spinnerBusca:', spinnerBusca);
form.addEventListener('submit', function(e) {
    e.preventDefault();
    const estadoId = estadoSelect.value;
    const cidadeId = cidadeSelect.value;
    if (!estadoId || !cidadeId) return;
    buscarBtn.disabled = true;
    if (spinnerBusca) spinnerBusca.style.display = 'inline-block';
    resultDiv.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border"></div></div>';
    fetch(`/Sigilosas-MySQL/api/busca-acompanhantes.php?estado_id=${estadoId}&cidade_id=${cidadeId}`)
        .then(res => res.json())
        .then(data => {
            buscarBtn.disabled = false;
            // Debug: quantidade de spinners
            const spinners = document.querySelectorAll('.spinner-busca');
            console.log('[DEBUG] Spinners encontrados:', spinners.length);
            spinners.forEach(function(sp, idx) {
                console.log(`[DEBUG] Spinner #${idx} display antes:`, sp.style.display);
                sp.style.display = 'none';
                console.log(`[DEBUG] Spinner #${idx} display depois:`, sp.style.display);
            });
            // Remover spinners residuais fora do botão e dos cards
            document.querySelectorAll('.spinner-border').forEach(function(sp) {
                if (!sp.closest('button') && !sp.closest('.img-loader')) {
                    sp.remove();
                    console.log('[DEBUG] Spinner residual removido do DOM:', sp);
                }
            });
            // Remover overlays/backdrops e restaurar rolagem
            document.querySelectorAll('#loading, .modal-backdrop, .backdrop, .overlay').forEach(e => e.remove());
            document.body.style.overflow = '';
            console.log('[DEBUG] HTML do botão Buscar:', buscarBtn.outerHTML);
            console.log('[DEBUG] Botão Buscar disabled:', buscarBtn.disabled);
            if (!Array.isArray(data) || data.length === 0) {
                resultDiv.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">Nenhuma acompanhante encontrada.</p></div>';
                return;
            }
            let html = '';
            data.forEach(a => {
                const imgId = `img-perfil-${a.id}`;
                // Menor valor e tempo
                let menorValor = null, tempoMenor = '';
                if (Array.isArray(a.valores_atendimento) && a.valores_atendimento.length > 0) {
                    a.valores_atendimento.forEach(v => {
                        if (v.valor && (menorValor === null || parseFloat(v.valor) < menorValor)) {
                            menorValor = parseFloat(v.valor);
                            tempoMenor = v.tempo.replace('min',' min').replace('h',' h').replace('diaria','Diária').replace('pernoite','Pernoite').replace('diaria_viagem','Diária Viagem');
                        }
                    });
                }
                // Preço destaque
                let precoHtml = '';
                if (menorValor !== null) {
                    precoHtml = `<span class='fw-bold'>R$ ${menorValor.toLocaleString('pt-BR', {minimumFractionDigits:2})}</span> <span class='text-muted'>- ${tempoMenor}</span>`;
                } else if (a.valor_promocional && a.valor_promocional > 0) {
                    precoHtml = `<span class='text-danger fw-bold'>R$ ${parseFloat(a.valor_promocional).toLocaleString('pt-BR', {minimumFractionDigits:2})}</span>`;
                } else if (a.valor_padrao && a.valor_padrao > 0) {
                    precoHtml = `<span class='fw-bold'>R$ ${parseFloat(a.valor_padrao).toLocaleString('pt-BR', {minimumFractionDigits:2})}</span>`;
                } else {
                    precoHtml = `<span class='text-muted'>Não informado</span>`;
                }
                // Tabela de valores (expande bloco, não sobrepõe)
                let tabelaValores = '';
                if (Array.isArray(a.valores_atendimento) && a.valores_atendimento.length > 0) {
                    tabelaValores = `<table class='table table-sm mb-0'><tbody>`;
                    a.valores_atendimento.forEach(v => {
                        tabelaValores += `<tr><td>${v.tempo.replace('min',' min').replace('h',' h').replace('diaria','Diária').replace('pernoite','Pernoite').replace('diaria_viagem','Diária Viagem')}</td><td>R$ ${parseFloat(v.valor).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td></tr>`;
                    });
                    tabelaValores += `</tbody></table>`;
                }
                // Sobre mim (primeiras 180 caracteres)
                let sobreMim = '';
                if (a.sobre_mim) {
                    sobreMim = `<div class='fw-bold mb-1 mt-2'>Sobre Mim</div><div class='text-muted small mb-2 px-2'>${a.sobre_mim.substring(0,180)}${a.sobre_mim.length>180?'...':''}</div>`;
                }
                // Local atendimento
                let localHtml = '';
                if (a.local_atendimento) {
                    try {
                        const locais = JSON.parse(a.local_atendimento);
                        if (Array.isArray(locais) && locais.length > 0) {
                            localHtml = `<div class='mb-1'><i class='fas fa-home'></i> ${locais.map(l => l.charAt(0).toUpperCase()+l.slice(1).replace('_',' ')).join(', ')}</div>`;
                        }
                    } catch(e) {}
                }
                // Card layout (tudo em uma coluna, sem barra)
                html += `<div class="col-lg-6 col-md-6 mb-4 d-flex">
                    <div class="card shadow-sm h-100 flex-grow-1 d-flex flex-column p-0" style="min-height:340px;">
                        <div class="card-img-top position-relative w-100" style="padding:12px 12px 0 12px;">
                            ${a.foto ? `<img id="${imgId}" src="/Sigilosas-MySQL/uploads/perfil/${a.foto}" class="card-img-top rounded-3" alt="${a.apelido||a.nome}" style="height: 210px; object-fit: cover; width:100%;">` : `<div class="bg-secondary d-flex align-items-center justify-content-center rounded-3" style="height: 210px;"><i class="fas fa-user fa-3x text-white"></i></div>`}
                        </div>
                        <div class="flex-grow-1 w-100 px-2 pt-2 pb-0 d-flex flex-column" style="min-height:140px;">
                            <h5 class="card-title mb-1 text-center">${a.apelido||a.nome}</h5>
                            <div class="text-muted small mb-1 text-center">a partir de</div>
                            <div class="d-flex align-items-center mb-2 justify-content-center">
                                <span>${precoHtml}</span>
                                ${tabelaValores ? `<button class='btn btn-link btn-sm ms-2 p-0' type='button' onclick='const tbl=this.parentNode.parentNode.querySelector(".tabela-valores");tbl.classList.toggle("d-none");'><i class='fas fa-chevron-down'></i></button>` : ''}
                            </div>
                            ${tabelaValores ? `<div class='tabela-valores d-none w-100 mb-2'>${tabelaValores}</div>` : ''}
                            ${a.idade ? `<div class='mb-1 text-center'><i class='fas fa-birthday-cake'></i> ${a.idade} anos</div>` : ''}
                            ${localHtml ? `<div class='mb-1 text-center'>${localHtml}</div>` : ''}
                            ${(a.bairro||a.cidade_nome) ? `<div class='mb-1 text-center'><i class='fas fa-map-marker-alt'></i> ${[a.bairro,a.cidade_nome,a.estado_uf].filter(Boolean).join(', ')}</div>` : ''}
                            ${sobreMim}
                        </div>
                        <div class="px-3 pb-3 pt-2 w-100">
                            <a href="/Sigilosas-MySQL/pages/acompanhante.php?id=${a.id}" class='btn btn-danger btn-sm w-100' style='margin-bottom:4px;'><i class='fas fa-phone'></i> Ver telefone</a>
                        </div>
                    </div>
                </div>`;
            });
            resultDiv.innerHTML = html;
            // Garantir que o loader do card suma mesmo se a imagem já estiver em cache
            document.querySelectorAll('.card-img-top img').forEach(function(img) {
                if (img.complete) {
                    img.style.display = 'block';
                    if (img.previousElementSibling && img.previousElementSibling.classList.contains('img-loader')) {
                        img.previousElementSibling.style.display = 'none';
                    }
                }
                // Timeout para garantir sumiço do loader após 2s
                setTimeout(function() {
                    if (img.previousElementSibling && img.previousElementSibling.classList.contains('img-loader')) {
                        img.previousElementSibling.style.display = 'none';
                        img.style.display = 'block';
                        console.log('[DEBUG] Timeout forçou sumiço do loader para', img.id);
                    }
                }, 2000);
            });
        })
        .catch(() => {
            buscarBtn.disabled = false;
            // Debug: quantidade de spinners
            const spinners = document.querySelectorAll('.spinner-busca');
            console.log('[DEBUG] Spinners encontrados:', spinners.length);
            spinners.forEach(function(sp, idx) {
                console.log(`[DEBUG] Spinner #${idx} display antes:`, sp.style.display);
                sp.style.display = 'none';
                console.log(`[DEBUG] Spinner #${idx} display depois:`, sp.style.display);
            });
            // Remover spinners residuais fora do botão e dos cards
            document.querySelectorAll('.spinner-border').forEach(function(sp) {
                if (!sp.closest('button') && !sp.closest('.img-loader')) {
                    sp.remove();
                    console.log('[DEBUG] Spinner residual removido do DOM:', sp);
                }
            });
            // Remover overlays/backdrops e restaurar rolagem
            document.querySelectorAll('#loading, .modal-backdrop, .backdrop, .overlay').forEach(e => e.remove());
            document.body.style.overflow = '';
            console.log('[DEBUG] HTML do botão Buscar:', buscarBtn.outerHTML);
            console.log('[DEBUG] Botão Buscar disabled:', buscarBtn.disabled);
            resultDiv.innerHTML = '<div class="col-12 text-center py-5"><p class="text-danger">Erro ao buscar acompanhantes.</p></div>';
        });
});

/* CSS extra para garantir 2 cards por linha e layout fiel ao modelo */
const style = document.createElement('style');
style.innerHTML = `
#acompanhantes-result { display: flex; flex-wrap: wrap; }
#acompanhantes-result > .col-lg-6 { max-width: 50%; flex: 0 0 50%; }
@media (max-width: 991.98px) { #acompanhantes-result > .col-lg-6 { max-width: 100%; flex: 0 0 100%; } }
.card-img-top img, .card-img-top .bg-secondary { border-radius: 12px; }
.card .btn-danger { font-weight: 500; font-size: 1.1em; }
.card { margin: 0 8px 24px 8px; }
`;
document.head.appendChild(style);
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
    overflow: hidden;
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

.img-loader {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%,-50%);
    z-index: 1;
    pointer-events: none;
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

<?php include_once '../includes/footer.php'; ?> 