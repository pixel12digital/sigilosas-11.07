<?php
/**
 * Página Inicial - Site Público
 * Arquivo: pages/home.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Sigilosas - Encontre Acompanhantes de Luxo';
$pageDescription = 'Encontre acompanhantes de luxo em sua cidade. Perfis verificados e seguros.';

$db = getDB();

// Buscar acompanhantes em destaque (aprovadas e destaque=1)
$acompanhantes_destaque = $db->fetchAll("
    SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON c.estado_id = e.id
    WHERE a.status = 'aprovado' AND a.destaque = 1
    ORDER BY a.created_at DESC
");

// Buscar estatísticas
$stats = [
    'total_acompanhantes' => $db->fetch("SELECT COUNT(*) as total FROM acompanhantes WHERE status = 'aprovado'")['total'] ?? 0,
    'total_cidades' => $db->fetch("SELECT COUNT(DISTINCT cidade_id) as total FROM acompanhantes WHERE status = 'aprovado'")['total'] ?? 0,
    'total_estados' => $db->fetch("SELECT COUNT(DISTINCT e.id) as total FROM acompanhantes a LEFT JOIN cidades c ON a.cidade_id = c.id LEFT JOIN estados e ON c.estado_id = e.id WHERE a.status = 'aprovado'")['total'] ?? 0
];

// Buscar estados para filtro
$estados = $db->fetchAll("
    SELECT e.*
    FROM estados e
    WHERE EXISTS (
        SELECT 1 FROM acompanhantes a WHERE a.estado_id = e.id AND a.status = 'aprovado'
    )
    ORDER BY e.nome ASC
");
?>

<!-- Banner Principal -->
<section class="hero-section position-relative">
    <div class="hero-bg">
        <div class="container position-relative">
            <div class="row min-vh-75 align-items-center">
                <div class="col-lg-10 col-md-12 mx-auto text-center text-white">
                    <!-- Headline e Subtítulo -->
                    <h1 class="hero-title mb-2">Sua nova referência em acompanhantes no Brasil</h1>
                    <h2 class="hero-subtitle mb-5">Sigiloso, seguro e exclusivo</h2>
                </div>
            </div>
            <!-- Filtro flutuante alinhado ao container -->
            <div class="search-form-wrapper-float mx-auto container px-3 px-md-4">
                <div class="search-form bg-white bg-opacity-90 p-4 rounded-3 shadow-lg">
                    <form id="filtro-form" class="search-flex-form align-items-center">
                        <select name="estado" class="form-select" id="estado" required aria-label="Selecione o Estado">
                            <option value="">Selecione o Estado</option>
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado['id']; ?>">
                                    <?php echo htmlspecialchars($estado['nome']); ?> (<?php echo $estado['uf']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="cidade" class="form-select" id="cidade-select" required aria-label="Selecione a Cidade">
                            <option value="">Selecione a Cidade</option>
                        </select>
                        <button type="submit" class="btn btn-primary w-100" aria-label="Buscar acompanhantes">
                            <span class="spinner-border spinner-busca" style="display:none;width:1.5em;height:1.5em;vertical-align:middle;"></span>
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Resultados da busca de acompanhantes -->
<section id="resultados-busca-section" class="py-4 bg-white">
    <div class="container">
        <div id="acompanhantes-result" class="row g-4"></div>
    </div>
</section>

<?php
// Buscar as 3 últimas acompanhantes aprovadas
$ultimas_acompanhantes = $db->fetchAll("SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf FROM acompanhantes a LEFT JOIN cidades c ON a.cidade_id = c.id LEFT JOIN estados e ON c.estado_id = e.id WHERE a.status = 'aprovado' ORDER BY a.created_at DESC LIMIT 3");

// Buscar os 3 posts mais recentes do blog
$posts_recentes = $db->fetchAll("
    SELECT id, titulo, resumo, imagem, data_publicacao, autor, visualizacoes
    FROM blog_posts 
    WHERE status = 'publicado' 
    ORDER BY data_publicacao DESC 
    LIMIT 3
");
?>

<!-- Seção Destaques -->
<section class="destaques-section py-4 bg-white destaque-mobile-spacing">
  <div class="container">
    <div class="row mb-3">
      <div class="col-12 text-center">
        <h3 class="section-title" style="font-size:1.5rem;">Destaques</h3>
        <div class="text-muted mb-2" style="font-size:1.1em;">As acompanhantes mais bem avaliadas e visualizadas da plataforma. Perfis verificados, imagens reais e uma performance que se destacou com elegância e confiança.</div>
      </div>
    </div>
    <div class="row justify-content-center" id="destaques-lista">
      <?php foreach ($acompanhantes_destaque as $i => $a): ?>
        <?php
          $foto_perfil = $db->fetch("SELECT url FROM fotos WHERE acompanhante_id = ? AND tipo = 'perfil' ORDER BY id ASC LIMIT 1", [$a['id']]);
          $foto_perfil_url = !empty($foto_perfil['url']) ? SITE_URL . '/uploads/perfil/' . htmlspecialchars($foto_perfil['url']) : null;
          
          // Buscar valores de atendimento
          $valores_atendimento = $db->fetchAll("SELECT tempo, valor FROM valores_atendimento WHERE acompanhante_id = ? ORDER BY valor ASC", [$a['id']]);
          
          // Calcular menor valor
          $menorValor = null;
          $tempoMenor = '';
          if (!empty($valores_atendimento)) {
              foreach ($valores_atendimento as $v) {
                  if ($v['valor'] && ($menorValor === null || floatval($v['valor']) < $menorValor)) {
                      $menorValor = floatval($v['valor']);
                      $tempoMenor = str_replace(['min', 'h', 'diaria', 'pernoite', 'diaria_viagem'], [' min', ' h', 'Diária', 'Pernoite', 'Diária Viagem'], $v['tempo']);
                  }
              }
          }
          
          // Preço HTML
          $precoHtml = '';
          if ($menorValor !== null) {
              $precoHtml = '<span class="fw-bold">R$ ' . number_format($menorValor, 2, ',', '.') . '</span> <span class="text-muted">- ' . $tempoMenor . '</span>';
          } else if (!empty($a['valor_promocional']) && $a['valor_promocional'] > 0) {
              $precoHtml = '<span class="text-danger fw-bold">R$ ' . number_format(floatval($a['valor_promocional']), 2, ',', '.') . '</span>';
          } else if (!empty($a['valor_padrao']) && $a['valor_padrao'] > 0) {
              $precoHtml = '<span class="fw-bold">R$ ' . number_format(floatval($a['valor_padrao']), 2, ',', '.') . '</span>';
          } else {
              $precoHtml = '<span class="text-muted">Não informado</span>';
          }
          
          // Tabela de valores
          $tabelaValores = '';
          if (!empty($valores_atendimento)) {
              $tabelaValores = '<table class="table table-sm mb-0"><tbody>';
              foreach ($valores_atendimento as $v) {
                  $tempo_formatado = str_replace(['min', 'h', 'diaria', 'pernoite', 'diaria_viagem'], [' min', ' h', 'Diária', 'Pernoite', 'Diária Viagem'], $v['tempo']);
                  $tabelaValores .= '<tr><td>' . $tempo_formatado . '</td><td>R$ ' . number_format(floatval($v['valor']), 2, ',', '.') . '</td></tr>';
              }
              $tabelaValores .= '</tbody></table>';
          }
          
          // Local atendimento
          $localHtml = '';
          if (!empty($a['local_atendimento'])) {
              $locais = json_decode($a['local_atendimento'], true);
              if (is_array($locais) && !empty($locais)) {
                  $locais_formatados = array_map(function($l) {
                      return ucfirst(str_replace('_', ' ', $l));
                  }, $locais);
                  $localHtml = '<div class="mb-1 text-center"><i class="fas fa-home"></i> ' . implode(', ', $locais_formatados) . '</div>';
              }
          }
        ?>
        <div class="col-lg-4 col-md-6 mb-4 d-flex align-items-stretch justify-content-center destaque-card" style="<?php echo $i >= 6 ? 'display:none;' : ''; ?>">
          <div class='card shadow-sm h-100 acompanhante-card w-100'>
            <div class="card-img-top position-relative w-100" style="padding:12px 12px 0 12px;">
              <?php if ($foto_perfil_url): ?>
                <img src="<?php echo $foto_perfil_url; ?>" class="card-img-top rounded-3" alt="<?php echo htmlspecialchars($a['apelido'] ?? $a['nome']); ?>" style="height: 210px; object-fit: cover; width:100%;">
              <?php else: ?>
                <div class="bg-secondary d-flex align-items-center justify-content-center rounded-3" style="height: 210px;"><i class="fas fa-user fa-3x text-white"></i></div>
              <?php endif; ?>
            </div>
            <div class="flex-grow-1 w-100 px-2 pt-2 pb-0 d-flex flex-column" style="min-height:140px;">
              <h5 class="card-title mb-1 text-center"><?php echo htmlspecialchars($a['apelido'] ?? $a['nome']); ?></h5>
              <div class="text-muted small mb-1 text-center">a partir de</div>
              <div class="d-flex align-items-center mb-2 justify-content-center">
                <span><?php echo $precoHtml; ?></span>
                <?php if (!empty($tabelaValores)): ?>
                  <button class='btn btn-link btn-sm ms-2 p-0' type='button' onclick='const tbl=this.parentNode.parentNode.querySelector(".tabela-valores");tbl.classList.toggle("d-none");'><i class='fas fa-chevron-down'></i></button>
                <?php endif; ?>
              </div>
              <?php if (!empty($tabelaValores)): ?>
                <div class='tabela-valores d-none w-100 mb-2'><?php echo $tabelaValores; ?></div>
              <?php endif; ?>
              <?php if (!empty($a['idade'])): ?>
                                            <div class='mb-1 text-center'><i class='fas fa-user'></i> <?php echo $a['idade']; ?> anos</div>
              <?php endif; ?>
              <?php echo $localHtml; ?>
              <?php 
                $localizacao_parts = array_filter([
                    !empty($a['bairro']) ? $a['bairro'] : null,
                    !empty($a['cidade_nome']) ? $a['cidade_nome'] : null,
                    !empty($a['estado_uf']) ? $a['estado_uf'] : null
                ]);
                if (!empty($localizacao_parts)):
              ?>
                <div class='mb-1 text-center'><i class='fas fa-map-marker-alt'></i> <?php echo implode(', ', $localizacao_parts); ?></div>
              <?php endif; ?>
              <?php if (!empty($a['sobre_mim'])): ?>
                <div class='fw-bold mb-1 mt-2'>Sobre Mim</div>
                <div class='text-muted small mb-2 px-2'><?php echo mb_strimwidth(strip_tags($a['sobre_mim']),0,180,'...'); ?></div>
              <?php endif; ?>
            </div>
            <div class="px-3 pb-3 pt-2 w-100">
              <a href="<?php echo SITE_URL; ?>/pages/acompanhante.php?id=<?php echo $a['id']; ?>" class='btn btn-danger btn-sm w-100' style='margin-bottom:4px; background:#3D263F; border-color:#3D263F; color:#F3EAC2;'><i class='fas fa-phone'></i> Ver telefone</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (count($acompanhantes_destaque) > 6): ?>
      <div class="row">
        <div class="col-12 text-center">
          <button id="btn-ver-mais-destaques" class="btn btn-primary mt-3" style="background:#3D263F; border-color:#3D263F; color:#F3EAC2;">Ver mais</button>
        </div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          let mostrados = 6;
          const total = <?php echo count($acompanhantes_destaque); ?>;
          const cards = document.querySelectorAll('.destaque-card');
          document.getElementById('btn-ver-mais-destaques').addEventListener('click', function() {
            let novos = 0;
            for (let i = mostrados; i < cards.length && novos < 6; i++, novos++) {
              cards[i].style.display = '';
            }
            mostrados += novos;
            if (mostrados >= total) this.style.display = 'none';
          });
        });
      </script>
    <?php endif; ?>
  </div>
</section>

<!-- Seção de Artigos do Blog -->
<?php if (!empty($posts_recentes)): ?>
<section class="blog-section py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="section-title" style="color: #3D263F;">
                    <i class="fas fa-blog me-2"></i>Últimos Artigos do Blog
                </h2>
                <p class="text-muted">Dicas, tendências e novidades sobre o mundo das acompanhantes de luxo</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($posts_recentes as $post): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 blog-card">
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
                                    <i class="fas fa-newspaper fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badge de categoria -->
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge" style="background-color: #3D263F; color: #F3EAC2;">Blog</span>
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
                                   class="text-decoration-none" style="color: #3D263F;">
                                    <?php echo htmlspecialchars($post['titulo']); ?>
                                </a>
                            </h5>
                            
                            <!-- Resumo -->
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo htmlspecialchars(substr($post['resumo'], 0, 120)) . '...'; ?>
                            </p>
                            
                            <!-- Botão ler mais -->
                            <div class="mt-auto">
                                <a href="index.php?page=post&id=<?php echo $post['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm" style="border-color: #3D263F; color: #3D263F;">
                                    <i class="fas fa-arrow-right"></i> Ler mais
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Botão ver todos os artigos -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="index.php?page=blog" class="btn btn-primary" style="background-color: #3D263F; border-color: #3D263F;">
                    <i class="fas fa-newspaper"></i> Ver Todos os Artigos
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

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
                <h3 class="mb-3">Pronta para se destacar? Cadastre seu perfil agora!</h3>
                <p class="mb-0">Cadastre-se gratuitamente e conquiste novos clientes com um perfil exclusivo e seguro.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?php echo SITE_URL; ?>/pages/cadastro-acompanhante.php" class="btn btn-light btn-lg d-flex align-items-center gap-2">
                    <i class="fas fa-user-plus" style="color:#3D263F;"></i> <span style="color:#3D263F;">Cadastrar Perfil</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Seção Institucional Sigilosas VIP -->
<section class="institucional-section py-5" style="background: #fff;">
  <div class="container">
    <div class="row justify-content-center mb-4">
      <div class="col-lg-8 text-center">
        <h2 class="fw-bold mb-3" style="color:#3D263F;"><i class="fas fa-gem me-2"></i>Sobre a Sigilosas VIP</h2>
        <p class="lead" style="color:#3D263F;">A Sigilosas VIP nasceu a partir de uma vivência real no mercado. Estamos há mais de 5 anos no ramo, conhecendo de perto os desafios, necessidades e sonhos de quem trabalha como acompanhante.</p>
        <p style="color:#3D263F;">Com toda essa experiência, decidimos criar algo diferente: uma plataforma exclusiva, segura e acolhedora, feita para quem busca mais do que apenas uma vitrine. <strong>Feita para quem quer ser valorizado, respeitado e crescer com liberdade.</strong></p>
      </div>
    </div>
    <div class="row g-4 justify-content-center mb-4">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 text-center py-4" style="background:#F3EAC2;">
          <div class="mb-3"><i class="fas fa-headset fa-2x" style="color:#3D263F;"></i></div>
          <h5 class="fw-bold mb-2" style="color:#3D263F;">Suporte 100% humanizado</h5>
          <p class="mb-0" style="color:#3D263F;">Nossa equipe está sempre pronta para te apoiar de verdade, com atendimento acolhedor e ágil.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 text-center py-4" style="background:#F3EAC2;">
          <div class="mb-3"><i class="fas fa-laptop-code fa-2x" style="color:#3D263F;"></i></div>
          <h5 class="fw-bold mb-2" style="color:#3D263F;">Tecnologia moderna e discreta</h5>
          <p class="mb-0" style="color:#3D263F;">Plataforma fácil de usar, com privacidade e segurança para você se destacar com tranquilidade.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 text-center py-4" style="background:#F3EAC2;">
          <div class="mb-3"><i class="fas fa-bullhorn fa-2x" style="color:#3D263F;"></i></div>
          <h5 class="fw-bold mb-2" style="color:#3D263F;">Visibilidade real e oportunidades</h5>
          <p class="mb-0" style="color:#3D263F;">Aqui você é vista de verdade, com oportunidades para crescer e conquistar novos clientes.</p>
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10 text-center">
        <p class="mb-2" style="color:#3D263F;font-size:1.1em;">Na Sigilosas VIP, você encontra segurança, privacidade e uma equipe pronta para te apoiar de verdade. Nossa missão é clara: conectar, empoderar e impulsionar cada profissional com seriedade e respeito.</p>
        <p class="mb-0 fw-bold" style="color:#3D263F;font-size:1.15em;">Seja você iniciante ou experiente, a Sigilosas VIP é o seu lugar.</p>
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
        fetch('<?php echo SITE_URL; ?>/api/cidades.php?estado_id=' + estadoId + '&public=1')
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
            fetch(`<?php echo SITE_URL; ?>/api/busca-acompanhantes.php?estado_id=${estadoId}&cidade_id=${cidadeId}`)
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
                html += `<div class='col-lg-4 col-md-6 mb-4 d-flex align-items-stretch justify-content-center'>
                    <div class='card shadow-sm h-100 acompanhante-card w-100'>
                        <div class="card-img-top position-relative w-100" style="padding:12px 12px 0 12px;">
                            ${a.foto ? `<img id="${imgId}" src="<?php echo SITE_URL; ?>/uploads/perfil/${a.foto}" class="card-img-top rounded-3" alt="${a.apelido||a.nome}" style="height: 210px; object-fit: cover; width:100%;">` : `<div class="bg-secondary d-flex align-items-center justify-content-center rounded-3" style="height: 210px;"><i class="fas fa-user fa-3x text-white"></i></div>`}
                        </div>
                        <div class="flex-grow-1 w-100 px-2 pt-2 pb-0 d-flex flex-column" style="min-height:140px;">
                            <h5 class="card-title mb-1 text-center">${a.apelido||a.nome}</h5>
                            <div class="text-muted small mb-1 text-center">a partir de</div>
                            <div class="d-flex align-items-center mb-2 justify-content-center">
                                <span>${precoHtml}</span>
                                ${tabelaValores ? `<button class='btn btn-link btn-sm ms-2 p-0' type='button' onclick='const tbl=this.parentNode.parentNode.querySelector(".tabela-valores");tbl.classList.toggle("d-none");'><i class='fas fa-chevron-down'></i></button>` : ''}
                            </div>
                            ${tabelaValores ? `<div class='tabela-valores d-none w-100 mb-2'>${tabelaValores}</div>` : ''}
                            ${a.idade ? `<div class='mb-1 text-center'><i class='fas fa-user'></i> ${a.idade} anos</div>` : ''}
                            ${localHtml ? `<div class='mb-1 text-center'>${localHtml}</div>` : ''}
                            ${(a.bairro||a.cidade_nome) ? `<div class='mb-1 text-center'><i class='fas fa-map-marker-alt'></i> ${[a.bairro,a.cidade_nome,a.estado_uf].filter(Boolean).join(', ')}</div>` : ''}
                            ${sobreMim}
                        </div>
                        <div class="px-3 pb-3 pt-2 w-100">
                            <a href="<?php echo SITE_URL; ?>/pages/acompanhante.php?id=${a.id}" class='btn btn-danger btn-sm w-100' style='margin-bottom:4px; background:#3D263F; border-color:#3D263F; color:#F3EAC2;'><i class='fas fa-phone'></i> Ver telefone</a>
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
    min-height: 45vh;
    display: flex;
    align-items: center;
    /* background-color removido para não sobrescrever a imagem */
    background: linear-gradient(rgba(61,38,63,0.18), rgba(61,38,63,0.18)), url('<?php echo SITE_URL; ?>/assets/img/Imagem-banner01.png') center/cover;
}

@media (min-width: 992px) {
  .hero-bg {
    background: linear-gradient(rgba(61,38,63,0.18), rgba(61,38,63,0.18)), url('<?php echo SITE_URL; ?>/pages/banner herro.png') center/cover !important;
  }
}

.min-vh-75 {
    min-height: 45vh;
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

.search-form-wrapper {
  max-width: 1400px;
  margin: 0 auto;
}
@media (max-width: 1200px) {
  .search-form-wrapper {
    max-width: 98vw;
  }
}

.search-form {
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}
.search-form .row {
  width: 100%;
  margin: 0;
}
@media (min-width: 992px) {
  .search-flex-form {
    display: flex;
    gap: 16px;
    width: 100%;
  }
  .search-flex-form .form-select {
    flex: 1 1 0;
    min-width: 0;
    font-size: 1.1em;
    height: 56px;
    padding: 0.75rem 1.25rem;
  }
  .search-flex-form button {
    flex: 0 0 140px;
    height: 56px;
    font-size: 1.1em;
    padding: 0.75rem 1.25rem;
    white-space: nowrap;
  }
}

@media (max-width: 991.98px) {
  .search-flex-form {
    flex-direction: column;
    gap: 16px;
  }
  .search-flex-form .form-select,
  .search-flex-form button {
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
  }
}

@media (max-width: 768px) {
    .hero-bg {
        min-height: 30vh;
    }
    
    .search-form {
        margin: 0;
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

#acompanhantes-result {
    margin-top: 120px;
}

/* Estilos para a seção de blog */
.blog-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
}

.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(61, 38, 63, 0.15) !important;
}

.blog-card .card-title a:hover {
    color: #F3EAC2 !important;
}

.blog-card .btn-outline-primary:hover {
    background-color: #3D263F !important;
    border-color: #3D263F !important;
    color: #F3EAC2 !important;
}

.blog-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.blog-section .section-title {
    font-weight: 700;
    margin-bottom: 1rem;
}

.blog-card .card-img-top {
    transition: transform 0.3s ease;
}

.blog-card:hover .card-img-top {
    transform: scale(1.05);
}
.btn-ver-telefone-paleta {
  background: #3D263F !important;
  border-color: #3D263F !important;
  color: #F3EAC2 !important;
  font-weight: 500;
  font-size: 1.1em;
  transition: background 0.2s, color 0.2s;
}
.btn-ver-telefone-paleta:hover, .btn-ver-telefone-paleta:focus {
  background: #F3EAC2 !important;
  color: #3D263F !important;
  border-color: #3D263F !important;
}

.hero-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 2px 8px rgba(61,38,63,0.18);
}
.hero-subtitle {
    font-size: 1.25rem;
    font-weight: 400;
    color: #fff;
    text-shadow: 0 1px 4px rgba(61,38,63,0.12);
}

.search-form-wrapper-float {
    position: absolute;
    left: 50%;
    transform: translate(-50%, 50%);
    bottom: 0;
    width: 100%;
    z-index: 10;
    padding-left: 0;
    padding-right: 0;
}
@media (min-width: 992px) {
    .search-form-wrapper-float {
        padding-left: 0;
        padding-right: 0;
    }
}
@media (max-width: 991.98px) {
    .search-form-wrapper-float {
        padding-left: 0;
        padding-right: 0;
    }
}
@media (max-width: 768px) {
    .hero-title {
        font-size: 1.2rem;
    }
    .hero-subtitle {
        font-size: 1rem;
        margin-bottom: 80px !important;
    }
    .search-form-wrapper-float {
        padding-left: 0;
        padding-right: 0;
    }
    .destaque-mobile-spacing {
        margin-top: 80px;
    }
    #acompanhantes-result {
        margin-top: 120px;
    }
}
</style> 