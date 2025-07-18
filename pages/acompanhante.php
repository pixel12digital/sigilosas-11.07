<?php
/**
 * Detalhes da Acompanhante - Site Público
 * Arquivo: pages/acompanhante.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Funções auxiliares para formatação
function formatarAltura($altura) {
    if (empty($altura)) return '';
    // Converte de cm para metros se necessário
    if ($altura > 10) { // Se for maior que 10, provavelmente está em cm
        return number_format($altura / 100, 2, ',', '.') . ' m';
    }
    return number_format($altura, 2, ',', '.') . ' m';
}

function formatarTexto($texto) {
    if (empty($texto)) return '';
    // Capitaliza primeira letra e corrige acentuação
    $texto = mb_strtolower($texto, 'UTF-8');
    $texto = mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
    
    // Correções específicas
    $correcoes = [
        'branca' => 'Branca',
        'medio' => 'Médio',
        'curto' => 'Curto',
        'longo' => 'Longo',
        'liso' => 'Liso',
        'ondulado' => 'Ondulado',
        'crespo' => 'Crespo',
        'azuis' => 'Azuis',
        'verdes' => 'Verdes',
        'castanhos' => 'Castanhos',
        'pretos' => 'Pretos',
        'loiro' => 'Loiro',
        'moreno' => 'Moreno',
        'ruivo' => 'Ruivo',
        'preto' => 'Preto'
    ];
    
    return $correcoes[$texto] ?? $texto;
}

function formatarSimNao($valor) {
    if (empty($valor) || $valor == '0') return 'Não';
    return 'Sim';
}

function formatarLocalAtendimento($local) {
    if (empty($local)) return '';
    
    // Remove colchetes e aspas duplas
    $local = str_replace(['[', ']', '"'], '', $local);
    
    // Se contém vírgulas, formata como lista
    if (strpos($local, ',') !== false) {
        $locais = array_map('trim', explode(',', $local));
        return implode(', ', $locais);
    }
    
    return $local;
}

$acompanhante_id = (int)$_GET['id'];
if (!$acompanhante_id) {
    header('Location: acompanhantes.php');
    exit;
}

$db = getDB();

// Buscar dados da acompanhante
$acompanhante = $db->fetch("
    SELECT a.*, c.nome as cidade_nome, e.nome as estado_nome, e.uf as estado_uf
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON c.estado_id = e.id
    WHERE a.id = ? AND a.status = 'aprovado' AND a.bloqueada = 0
", [$acompanhante_id]);

if (!$acompanhante) {
    header('Location: acompanhantes.php?error=Perfil não encontrado');
    exit;
}

// Buscar fotos da acompanhante
$fotos = $db->fetchAll("
    SELECT * FROM fotos 
    WHERE acompanhante_id = ? AND tipo = 'foto'
    ORDER BY id ASC
", [$acompanhante_id]);

// Buscar vídeos da acompanhante
$videos = $db->fetchAll("
    SELECT * FROM videos 
    WHERE acompanhante_id = ? AND tipo = 'video'
    ORDER BY id ASC
", [$acompanhante_id]);

// Buscar fotos da galeria da acompanhante (apenas aprovadas)
$fotos_galeria = $db->fetchAll("SELECT * FROM fotos WHERE acompanhante_id = ? AND tipo = 'galeria' AND aprovada = 1 ORDER BY id ASC", [$acompanhante_id]);

// Buscar vídeos públicos da acompanhante (apenas aprovados)
$videos_publicos = $db->fetchAll("SELECT * FROM videos_publicos WHERE acompanhante_id = ? AND status = 'aprovado' ORDER BY created_at DESC", [$acompanhante_id]);

// Processar formulário de contato
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'contato') {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $mensagem = trim($_POST['mensagem']);
        
        if (empty($nome) || empty($email) || empty($mensagem)) {
            $error_message = 'Por favor, preencha todos os campos obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Por favor, insira um email válido.';
        } else {
            // Aqui você pode implementar o envio de email ou salvar no banco
            $success_message = 'Mensagem enviada com sucesso! Entraremos em contato em breve.';
        }
    } elseif ($action === 'denuncia') {
        $motivo = trim($_POST['motivo']);
        $descricao = trim($_POST['descricao']);
        
        if (empty($motivo) || empty($descricao)) {
            $error_message = 'Por favor, preencha todos os campos da denúncia.';
        } else {
            // Salvar denúncia no banco
            $db->insert('denuncias', [
                'acompanhante_id' => $acompanhante_id,
                'tipo' => $motivo,
                'descricao' => $descricao,
                'status' => 'pendente',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $success_message = 'Denúncia enviada com sucesso. Nossa equipe irá analisar.';
        }
    }
}

$pageTitle = ($acompanhante['apelido'] ?? $acompanhante['nome']) . ' - Sigilosas';
$pageDescription = $acompanhante['descricao'] ? substr(strip_tags($acompanhante['descricao']), 0, 160) : 'Perfil de ' . ($acompanhante['apelido'] ?? $acompanhante['nome']);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/">Início</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($acompanhante['apelido'] ?? $acompanhante['nome']); ?></li>
        </ol>
    </div>
</nav>

<!-- Banner/topo visual -->
<div class="profile-banner" style="background: #3D263F; height:180px; position:relative;"></div>

<!-- Foto de perfil, nome, dados principais -->
<div class="container position-relative" style="margin-top:-90px; z-index:2;">
  <div class="d-flex flex-column align-items-center">
    <?php
    $foto_perfil = $db->fetch("SELECT url FROM fotos WHERE acompanhante_id = ? AND tipo = 'perfil' ORDER BY id ASC LIMIT 1", [$acompanhante_id]);
    $foto_perfil_url = !empty($foto_perfil['url']) ? SITE_URL . '/uploads/perfil/' . htmlspecialchars($foto_perfil['url']) : 'https://ui-avatars.com/api/?name=' . urlencode($acompanhante['apelido'] ?? $acompanhante['nome']) . '&size=256&background=6f42c1&color=fff';
    ?>
    <img src="<?php echo $foto_perfil_url; ?>" class="rounded-circle shadow" style="width:160px;height:160px;object-fit:cover;border:6px solid #F3EAC2; margin-top:-80px; background:#eee;">
    <h2 class="mt-3 mb-1 fw-bold text-center" style="color:#3D263F;"><?php echo htmlspecialchars($acompanhante['apelido'] ?? $acompanhante['nome']); ?></h2>
    <div class="mb-2 text-muted text-center" style="color:#3D263F;opacity:0.8;">
      <?php if (!empty($acompanhante['idade'])): ?><?php echo $acompanhante['idade']; ?> anos · <?php endif; ?>
      <?php echo htmlspecialchars($acompanhante['cidade_nome']); ?><?php if (!empty($acompanhante['bairro'])) echo ', ' . htmlspecialchars($acompanhante['bairro']); ?><?php if (!empty($acompanhante['estado_uf'])) echo ', ' . htmlspecialchars($acompanhante['estado_uf']); ?>
    </div>
    <?php if (!empty($acompanhante['verificado'])): ?><span class="badge mb-2" style="background:#3D263F;color:#F3EAC2;"><i class="fas fa-check-circle"></i> Verificada</span><?php endif; ?>
    <div class="d-flex gap-2 mb-3">
      <?php if (!empty($acompanhante['telefone'])): ?>
        <?php
          // Formatar número para padrão internacional (somente dígitos)
          $whats_number = preg_replace('/\D+/', '', $acompanhante['telefone']);
          if (strlen($whats_number) === 11) {
            $whats_number = '55' . $whats_number; // Adiciona DDI Brasil se não tiver
          } elseif (strlen($whats_number) === 13 && substr($whats_number, 0, 2) !== '55') {
            $whats_number = '55' . substr($whats_number, -11);
          }
          $whats_msg = urlencode('Olá, vi seu perfil no SigilosasVIP e gostaria de conversar!');
          $whats_link = 'https://wa.me/' . $whats_number . '?text=' . $whats_msg;
        ?>
        <a href="<?php echo $whats_link; ?>" class="btn" style="background:#3D263F;color:#F3EAC2;" target="_blank">
          <i class="fab fa-whatsapp"></i> Conversar Agora
        </a>
      <?php endif; ?>
      <button class="btn" style="background:#F3EAC2;color:#3D263F;border:1.5px solid #3D263F;" data-bs-toggle="modal" data-bs-target="#denunciaModal"><i class="fas fa-flag"></i> Denunciar</button>
      <?php if (!empty($acompanhante['instagram'])): ?><a href="<?php echo htmlspecialchars($acompanhante['instagram']); ?>" class="btn" style="background:#F3EAC2;color:#3D263F;border:1.5px solid #3D263F;" target="_blank"><i class="fab fa-instagram"></i></a><?php endif; ?>
      <?php if (!empty($acompanhante['tiktok'])): ?><a href="<?php echo htmlspecialchars($acompanhante['tiktok']); ?>" class="btn" style="background:#F3EAC2;color:#3D263F;border:1.5px solid #3D263F;" target="_blank"><i class="fab fa-tiktok"></i></a><?php endif; ?>
    </div>

    <!-- Galeria de imagens (apenas tipo galeria) -->
    <?php if (!empty($fotos_galeria)): ?>
      <!-- CSS da galeria (adicione no <head> ou aqui mesmo) -->
      <style>
      .gallery-thumb-link {
        display: inline-block;
        cursor: pointer;
        position: relative;
        z-index: 10;
      }
      .gallery-thumb {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 12px;
        border: 2px solid #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.10);
        cursor: pointer;
        transition: box-shadow .2s, border-color .2s, transform .2s;
        pointer-events: auto;
        user-select: none;
      }
      .gallery-thumb-link:hover .gallery-thumb {
        transform: scale(1.05);
      }
      .gallery-thumb-link:active .gallery-thumb {
        transform: scale(0.95);
      }

      /* Garante que o X do modal só aparece quando o modal está aberto */
      #galeriaModal:not(.show) .btn-close {
        display: none !important;
      }
      </style>

      <!-- Miniaturas da galeria (HTML limpo) -->
      <div class="gallery-thumbs row row-cols-auto g-3 justify-content-center mb-4 flex-nowrap flex-md-wrap overflow-auto pb-2" style="scrollbar-width:thin;">
        <?php foreach ($fotos_galeria as $index => $foto): ?>
          <div class="col-auto">
            <div class="gallery-thumb-link" data-index="<?php echo $index; ?>">
              <img src="<?php echo SITE_URL; ?>/uploads/galeria/<?php echo htmlspecialchars($foto['url']); ?>" class="img-thumbnail gallery-thumb" alt="Miniatura <?php echo $index+1; ?>">
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="text-center mb-3">
        <button type="button" class="btn btn-sm btn-outline-primary" id="btnTestarGaleria">
          <i class="fas fa-camera"></i> Ver Fotos
        </button>
      </div>
    <?php endif; ?>
  </div>

  <!-- Seção de Vídeos Públicos -->
  <?php if (!empty($videos_publicos)): ?>
    <div class="card shadow-sm mb-4" style="background:#fff;color:#3D263F;box-shadow:0 2px 12px rgba(61,38,63,0.08);">
      <div class="card-body">
        <div class="fw-bold mb-3" style="color:#3D263F;"><i class="fas fa-video"></i> Vídeos Públicos</div>
        <div class="row g-3">
          <?php foreach ($videos_publicos as $video): ?>
            <div class="col-md-4 col-6">
              <div class="text-center">
                <video src="<?php echo SITE_URL . '/uploads/videos_publicos/' . htmlspecialchars($video['url']); ?>" 
                       controls 
                       style="width:100%; max-width:200px; aspect-ratio:9/16; height:auto; max-height:300px; margin:auto; display:block; background:#000; object-fit:cover; border-radius:12px;">
                </video>
                <?php if (!empty($video['titulo'])): ?>
                  <div class="mt-2 fw-bold small"><?php echo htmlspecialchars($video['titulo']); ?></div>
                <?php endif; ?>
                <?php if (!empty($video['descricao'])): ?>
                  <div class="text-muted small"><?php echo htmlspecialchars($video['descricao']); ?></div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Card Valores -->
    <div class="col-md-6">
      <div class="card shadow-sm mb-3" style="background:#fff;color:#3D263F;box-shadow:0 2px 12px rgba(61,38,63,0.08);">
        <div class="card-body">
          <div class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-dollar-sign"></i> Valores</div>
          <?php
          $valores = $db->fetchAll("SELECT tempo, valor FROM valores_atendimento WHERE acompanhante_id = ? AND disponivel = 1 ORDER BY valor ASC", [$acompanhante_id]);
          ?>
          <?php if (!empty($valores)): ?>
            <table class="table table-sm mb-0">
              <tbody>
                <?php foreach ($valores as $v): ?>
                  <tr><td><?php echo htmlspecialchars($v['tempo']); ?></td><td>R$ <?php echo number_format($v['valor'],2,',','.'); ?></td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="text-muted">Valores não informados.</div>
          <?php endif; ?>
            </div>
  </div>
</div>


    <!-- Card Localização -->
    <div class="col-md-6">
      <div class="card shadow-sm mb-3" style="background:#fff;color:#3D263F;box-shadow:0 2px 12px rgba(61,38,63,0.08);">
        <div class="card-body">
          <div class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-map-marker-alt"></i> Localização</div>
          <div><?php echo htmlspecialchars($acompanhante['cidade_nome']); ?><?php if (!empty($acompanhante['bairro'])) echo ', ' . htmlspecialchars($acompanhante['bairro']); ?><?php if (!empty($acompanhante['estado_uf'])) echo ', ' . htmlspecialchars($acompanhante['estado_uf']); ?></div>
          <?php if (!empty($acompanhante['local_atendimento'])): ?>
            <div class="text-muted small mt-1"><i class="fas fa-home"></i> <?php echo formatarLocalAtendimento($acompanhante['local_atendimento']); ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Sobre Mim / Descrição -->
  <?php if (!empty($acompanhante['sobre_mim'])): ?>
    <div class="card shadow-sm mb-4" style="background:#fff;color:#3D263F;box-shadow:0 2px 12px rgba(61,38,63,0.08);">
      <div class="card-body">
        <div class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-user"></i> Sobre Mim</div>
        <div class="text-muted"><?php echo nl2br(htmlspecialchars($acompanhante['sobre_mim'])); ?></div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Aparência Física -->
  <div class="card shadow-sm mb-4" style="background:#fff;color:#3D263F;box-shadow:0 2px 12px rgba(61,38,63,0.08);">
    <div class="card-body">
      <div class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-heart"></i> Aparência Física</div>
      <div class="row">
        <div class="col-md-6">
          <ul class="list-unstyled mb-0">
            <li><b>Altura:</b> <?php echo formatarAltura($acompanhante['altura'] ?? ''); ?></li>
            <li><b>Peso:</b> <?php echo htmlspecialchars($acompanhante['peso'] ?? ''); ?> kg</li>
            <li><b>Etnia:</b> <?php echo formatarTexto($acompanhante['etnia'] ?? ''); ?></li>
          </ul>
        </div>
        <div class="col-md-6">
          <ul class="list-unstyled mb-0">
            <li><b>Cor dos Olhos:</b> <?php echo formatarTexto($acompanhante['cor_olhos'] ?? ''); ?></li>
            <li><b>Cor do Cabelo:</b> <?php echo formatarTexto($acompanhante['cor_cabelo'] ?? ''); ?></li>
            <li><b>Estilo do Cabelo:</b> <?php echo formatarTexto($acompanhante['estilo_cabelo'] ?? ''); ?></li>
            <li><b>Tamanho do Cabelo:</b> <?php echo formatarTexto($acompanhante['tamanho_cabelo'] ?? ''); ?></li>
            <li><b>Silicone:</b> <?php echo formatarSimNao($acompanhante['silicone'] ?? ''); ?></li>
            <li><b>Tatuagens:</b> <?php echo formatarSimNao($acompanhante['tatuagens'] ?? ''); ?></li>
            <li><b>Piercings:</b> <?php echo formatarSimNao($acompanhante['piercings'] ?? ''); ?></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Preferências e Serviços -->
  <div class="card shadow-sm mb-4" style="background:#fff;color:#3D263F;box-shadow:0 2px 12px rgba(61,38,63,0.08);">
    <div class="card-body">
      <div class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-cogs"></i> Preferências e Serviços</div>
      <ul class="list-unstyled mb-0">
        <li><b>Especialidades:</b> 
<?php
  $especialidades = $acompanhante['especialidades'] ?? '';
  if ($especialidades) {
    $espArr = json_decode($especialidades, true);
    if (is_array($espArr)) {
      foreach ($espArr as $esp) {
        echo '<span class="badge bg-secondary me-1" style="background:#F3EAC2;color:#3D263F;font-weight:500;font-size:1em;">' . htmlspecialchars($esp) . '</span>';
      }
    } else {
      echo htmlspecialchars($especialidades);
    }
  } else {
    echo '<span class="text-muted">Não informado</span>';
  }
?>
</li>
        <li><b>Idiomas:</b> <?php echo htmlspecialchars($acompanhante['idiomas'] ?? ''); ?></li>
      </ul>
    </div>
  </div>

  <!-- Horário de Atendimento -->
  <div class="card shadow-sm mb-4" style="background:#fff;color:#3D263F;box-shadow:0 2px 12px rgba(61,38,63,0.08);">
    <div class="card-body">
      <div class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-clock"></i> Horário de Atendimento</div>
      <?php
      $dias_semana = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        7 => 'Domingo'
      ];
      $horarios = $db->fetchAll("SELECT * FROM horarios_atendimento WHERE acompanhante_id = ?", [$acompanhante_id]);
      $horarios_map = [];
      foreach ($horarios as $h) {
        $horarios_map[$h['dia_semana']] = $h;
      }
      ?>
      <table class="table table-sm mb-0">
        <thead><tr><th>Dia</th><th>Início</th><th>Fim</th><th>Atende?</th></tr></thead>
        <tbody>
          <?php foreach ($dias_semana as $num => $nome): 
            $atende = isset($horarios_map[$num]);
            $inicio = $atende ? htmlspecialchars($horarios_map[$num]['hora_inicio']) : '—';
            $fim = $atende ? htmlspecialchars($horarios_map[$num]['hora_fim']) : '—';
          ?>
          <tr>
            <td><?php echo $nome; ?></td>
            <td><?php echo $inicio; ?></td>
            <td><?php echo $fim; ?></td>
            <td><?php echo $atende ? 'Sim' : 'Não'; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Avaliações -->
  <?php
  $avaliacoes = $db->fetchAll("SELECT nota, comentario, nome, created_at FROM avaliacoes WHERE acompanhante_id = ? AND aprovado = 1 ORDER BY created_at DESC LIMIT 5", [$acompanhante_id]);
  $media = $db->fetch("SELECT AVG(nota) as media, COUNT(*) as total FROM avaliacoes WHERE acompanhante_id = ? AND aprovado = 1", [$acompanhante_id]);
  ?>
  <div class="card shadow-sm mb-4" style="background:#fff;color:#3D263F;box-shadow:0 2px 12px rgba(61,38,63,0.08);">
    <div class="card-body">
      <div class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-star"></i> Avaliações</div>
      <?php if (!empty($media['total'])): ?>
        <div class="mb-2">Média: <span class="text-warning fw-bold"><?php echo number_format($media['media'],1,',','.'); ?> ★</span> (<?php echo $media['total']; ?> avaliações)</div>
      <?php endif; ?>
      <?php if (!empty($avaliacoes)): ?>
        <ul class="list-unstyled mb-0">
          <?php foreach ($avaliacoes as $av): ?>
            <li class="mb-2"><span class="text-warning">★ <?php echo $av['nota']; ?></span> - <span class="text-muted small"><?php echo date('d/m/Y', strtotime($av['created_at'])); ?></span><?php if (!empty($av['nome'])) echo ' - <b>' . htmlspecialchars($av['nome']) . '</b>'; ?><br><span><?php echo htmlspecialchars($av['comentario']); ?></span></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="text-muted small">Nenhuma avaliação ainda.</div>
      <?php endif; ?>

      <!-- Formulário de Avaliação (usuário público) -->
      <hr>
      <div class="mt-3">
        <form method="post" id="formAvaliacao" autocomplete="off">
          <input type="hidden" name="action" value="avaliacao">
          <div class="mb-2">
            <label for="nomeAvaliador" class="form-label">Seu nome (opcional)</label>
            <input type="text" class="form-control" id="nomeAvaliador" name="nomeAvaliador" maxlength="40">
          </div>
          <div class="mb-2">
            <label class="form-label">Nota *</label><br>
            <div id="estrelasAvaliacao" style="font-size:1.5rem; color:#e83e8c;">
              <?php for ($i=1; $i<=5; $i++): ?>
                <input type="radio" name="notaAvaliacao" id="estrela<?php echo $i; ?>" value="<?php echo $i; ?>" style="display:none;">
                <label for="estrela<?php echo $i; ?>" style="cursor:pointer;">&#9733;</label>
              <?php endfor; ?>
            </div>
          </div>
          <div class="mb-2">
            <label for="comentarioAvaliacao" class="form-label">Comentário *</label>
            <textarea class="form-control" id="comentarioAvaliacao" name="comentarioAvaliacao" rows="3" maxlength="500" required></textarea>
          </div>
          <button type="submit" class="btn" style="background:#3D263F;color:#F3EAC2;"><i class="fas fa-star"></i> Enviar Avaliação</button>
        </form>
        <?php if (isset($_POST['action']) && $_POST['action'] === 'avaliacao'): ?>
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success mt-3">Sua avaliação foi enviada e aguarda aprovação do administrador.</div>
          <?php elseif (!empty($error_message)): ?>
            <div class="alert alert-danger mt-3"><?php echo htmlspecialchars($error_message); ?></div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php
// Processar avaliação enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'avaliacao') {
  $nome = trim($_POST['nomeAvaliador'] ?? '');
  $nota = (int)($_POST['notaAvaliacao'] ?? 0);
  $comentario = trim($_POST['comentarioAvaliacao'] ?? '');
  if ($nota < 1 || $nota > 5) {
    $error_message = 'Selecione uma nota de 1 a 5 estrelas.';
  } elseif (empty($comentario) || mb_strlen($comentario) < 5) {
    $error_message = 'O comentário deve ter pelo menos 5 caracteres.';
  } else {
    $db->insert('avaliacoes', [
      'acompanhante_id' => $acompanhante_id,
      'nota' => $nota,
      'comentario' => $comentario,
      'nome' => $nome,
      'aprovado' => 0,
      'created_at' => date('Y-m-d H:i:s')
    ]);
    $success_message = 'Sua avaliação foi enviada e aguarda aprovação do administrador.';
  }
}
?>

<script>var SITE_URL = '<?php echo SITE_URL; ?>';</script>
<script>
// JS para destacar estrelas ao passar/clicar
const estrelas = document.querySelectorAll('#estrelasAvaliacao label');
estrelas.forEach((estrela, idx) => {
  estrela.addEventListener('mouseenter', function() {
    for (let i = 0; i <= idx; i++) estrelas[i].style.color = '#ffc107';
    for (let i = idx+1; i < estrelas.length; i++) estrelas[i].style.color = '#e83e8c';
  });
  estrela.addEventListener('mouseleave', function() {
    const checked = document.querySelector('#estrelasAvaliacao input:checked');
    const val = checked ? parseInt(checked.value) : 0;
    for (let i = 0; i < estrelas.length; i++) estrelas[i].style.color = (i < val) ? '#ffc107' : '#e83e8c';
  });
  estrela.addEventListener('click', function() {
    document.getElementById('estrela'+(idx+1)).checked = true;
    for (let i = 0; i <= idx; i++) estrelas[i].style.color = '#ffc107';
    for (let i = idx+1; i < estrelas.length; i++) estrelas[i].style.color = '#e83e8c';
  });
});
// Inicializar cor das estrelas
(function(){
  const checked = document.querySelector('#estrelasAvaliacao input:checked');
  const val = checked ? parseInt(checked.value) : 0;
  for (let i = 0; i < estrelas.length; i++) estrelas[i].style.color = (i < val) ? '#ffc107' : '#e83e8c';
})();
</script>



  <!-- Segurança -->
  <div class="alert mt-4 mb-5" style="background:#3D263F;color:#F3EAC2;">
    <i class="fas fa-shield-alt"></i> Este perfil foi verificado e segue as diretrizes de segurança da plataforma. Denúncias são analisadas pela equipe Sigilosas VIP.
  </div>
</div>

<!-- Modal Galeria de Fotos -->
<?php if (!empty($fotos_galeria)): ?>
<div class="modal fade" id="galeriaModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:90vw;">
    <div class="modal-content" style="background:#3D263F !important; color:#F3EAC2 !important; border-radius:18px;">
      <div class="modal-body p-0 position-relative" style="min-height:350px; background:#3D263F; border-radius:18px;">
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" tabindex="-1" style="filter: invert(0.8);"></button>
        <div class="d-flex justify-content-center align-items-center" style="min-height:60vh;">
          <a id="galeriaImgLink" href="#" target="_blank" style="display:block;">
            <img id="galeriaImg" src="" class="gallery-main-img shadow-lg" style="max-width:90vw; max-height:80vh; object-fit:contain; border-radius:12px; background:#222; display:block; margin:auto; transition:opacity .3s; cursor: zoom-in;">
          </a>
        </div>
        <div class="d-flex justify-content-between align-items-center px-4 pb-3 pt-2">
          <div id="galeriaContador" class="rounded-pill" style="background:#F3EAC2;color:#3D263F;font-weight:500;" ></div>
          <a id="galeriaOriginal" href="javascript:void(0)" class="btn" style="background:#F3EAC2;color:#3D263F;font-weight:600; border:1.5px solid #3D263F;"><i class="fas fa-external-link-alt"></i> Ver em tamanho original</a>
        </div>
        <button type="button" class="btn gallery-arrow position-absolute top-50 start-0 translate-middle-y" style="z-index:2; font-size:2.5rem; padding:0.5rem 1.2rem; opacity:0.90; border-radius:50%; background:#F3EAC2;color:#3D263F; border:1.5px solid #3D263F;" onclick="navegarGaleria(-1)" tabindex="0"><i class="fas fa-chevron-left"></i></button>
        <button type="button" class="btn gallery-arrow position-absolute top-50 end-0 translate-middle-y" style="z-index:2; font-size:2.5rem; padding:0.5rem 1.2rem; opacity:0.90; border-radius:50%; background:#F3EAC2;color:#3D263F; border:1.5px solid #3D263F;" onclick="navegarGaleria(1)" tabindex="0"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal de Contato -->
<div class="modal fade" id="contatoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#3D263F;color:#F3EAC2;">
            <div class="modal-header" style="background:#3D263F;color:#F3EAC2;">
                <h5 class="modal-title">
                    <i class="fas fa-envelope"></i> Enviar Mensagem
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(0.8);"></button>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="contato">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Seu Nome *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Seu Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="telefone" name="telefone">
                    </div>
                    <div class="mb-3">
                        <label for="mensagem" class="form-label">Mensagem *</label>
                        <textarea class="form-control" id="mensagem" name="mensagem" rows="4" required 
                                  placeholder="Digite sua mensagem..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:#F3EAC2;">
                    <button type="button" class="btn" style="background:#3D263F;color:#F3EAC2;" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background:#3D263F;color:#F3EAC2;">
                        <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Denúncia -->
<div class="modal fade" id="denunciaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#3D263F;color:#F3EAC2;">
            <div class="modal-header" style="background:#3D263F;color:#F3EAC2;">
                <h5 class="modal-title">
                    <i class="fas fa-flag"></i> Reportar Perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(0.8);"></button>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="denuncia">
                <div class="modal-body">
                    <div class="alert" style="background:#F3EAC2;color:#3D263F;">
                        <i class="fas fa-info-circle"></i>
                        Ajude-nos a manter a qualidade dos perfis. Sua denúncia será analisada pela nossa equipe.
                    </div>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo da Denúncia *</label>
                        <select class="form-select" id="motivo" name="motivo" required>
                            <option value="">Selecione o motivo</option>
                            <option value="inapropriado">Conteúdo Inapropriado</option>
                            <option value="fake">Perfil Fake</option>
                            <option value="spam">Spam</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição *</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4" required 
                                  placeholder="Descreva o motivo da denúncia..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:#F3EAC2;">
                    <button type="button" class="btn" style="background:#3D263F;color:#F3EAC2;" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background:#3D263F;color:#F3EAC2;">
                        <i class="fas fa-flag"></i> Enviar Denúncia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mensagens de Sucesso/Erro -->
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<!-- Botão flutuante do WhatsApp Suporte -->
<style>
#whatsapp-suporte {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  align-items: center;
  background: #25D366;
  color: #fff;
  border-radius: 32px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
  padding: 10px 18px 10px 12px;
  font-weight: bold;
  font-size: 1.1rem;
  text-decoration: none;
  transition: box-shadow 0.2s, background 0.2s;
}
#whatsapp-suporte:hover {
  background: #1ebe5d;
  color: #fff;
  box-shadow: 0 8px 24px rgba(0,0,0,0.18);
  text-decoration: none;
}
#whatsapp-suporte .wa-icon {
  font-size: 1.7rem;
  margin-right: 8px;
}
@media (max-width: 600px) {
  #whatsapp-suporte {
    bottom: 16px;
    right: 16px;
    font-size: 1rem;
    padding: 8px 14px 8px 10px;
  }
  #whatsapp-suporte .wa-icon {
    font-size: 1.3rem;
    margin-right: 6px;
  }
}
</style>
<a href="https://wa.me/5547996829294?text=Olá! Preciso de suporte no site Sigilosas." id="whatsapp-suporte" target="_blank" rel="noopener">
  <span class="wa-icon"><i class="fab fa-whatsapp"></i></span>
</a>
<!-- Certifique-se de que o FontAwesome está carregado para o ícone do WhatsApp -->

<!-- JS final da galeria -->
<script>
const fotosGaleria = <?php echo json_encode(isset($fotos_galeria) ? array_column($fotos_galeria, 'url') : []); ?>;
let galeriaIndex = 0;

function abrirGaleria(index) {
  galeriaIndex = index;
  const img = document.getElementById('galeriaImg');
  const contador = document.getElementById('galeriaContador');
  const link = document.getElementById('galeriaImgLink');
  const original = document.getElementById('galeriaOriginal');
  if (img) {
    img.src = SITE_URL + '/uploads/galeria/' + fotosGaleria[index];
  }
  if (contador) {
    contador.textContent = `Foto ${index + 1} de ${fotosGaleria.length}`;
  }
  if (link && original) {
    const url = SITE_URL + '/uploads/galeria/' + fotosGaleria[index];
    link.href = url;
    original.href = url;
  }
  document.querySelectorAll('.gallery-thumb').forEach((el, i) => {
    el.style.borderColor = (i === galeriaIndex) ? '#e83e8c' : '#fff';
    el.style.boxShadow = (i === galeriaIndex) ? '0 0 0 4px #e83e8c55' : '0 4px 16px rgba(0,0,0,0.10)';
    el.style.transform = (i === galeriaIndex) ? 'scale(1.08)' : 'scale(1)';
  });
  const modal = document.getElementById('galeriaModal');
  if (modal && typeof bootstrap !== 'undefined') {
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
  }
}

function navegarGaleria(delta) {
  let novoIndex = galeriaIndex + delta;
  if (novoIndex < 0) novoIndex = fotosGaleria.length - 1;
  if (novoIndex >= fotosGaleria.length) novoIndex = 0;
  abrirGaleria(novoIndex);
}

document.querySelectorAll('.gallery-thumb-link').forEach(function(miniatura, index) {
  miniatura.onclick = function(e) {
    e.preventDefault();
    e.stopPropagation();
    abrirGaleria(index);
  };
});

var btnTestarGaleria = document.getElementById('btnTestarGaleria');
if (btnTestarGaleria) {
  btnTestarGaleria.addEventListener('click', function() {
    abrirGaleria(0);
  });
}

document.addEventListener('keydown', function(e) {
  const modal = document.getElementById('galeriaModal');
  if (modal && modal.classList.contains('show')) {
    if (e.key === 'ArrowLeft') navegarGaleria(-1);
    if (e.key === 'ArrowRight') navegarGaleria(1);
    if (e.key === 'Escape') {
      const bsModal = bootstrap.Modal.getInstance(modal);
      if (bsModal) bsModal.hide();
    }
  }
});

window.abrirGaleria = abrirGaleria;
window.navegarGaleria = navegarGaleria;

// Limpar backdrops quando o modal for fechado
document.addEventListener('DOMContentLoaded', function() {
  const galeriaModal = document.getElementById('galeriaModal');
  if (galeriaModal) {
    galeriaModal.addEventListener('hidden.bs.modal', function() {
      // Remove backdrops residuais
      document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
    });
  }
});
</script>

<!-- Exibir imagem em tamanho original dentro do modal, com X grande para fechar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const btnVerOriginal = document.getElementById('galeriaOriginal');
  if (btnVerOriginal) {
    btnVerOriginal.removeAttribute('target');
    btnVerOriginal.addEventListener('click', function(e) {
      e.preventDefault();
      // Fecha o modal da galeria
      const galeriaModal = document.getElementById('galeriaModal');
      if (galeriaModal && typeof bootstrap !== 'undefined') {
        const bsModal = bootstrap.Modal.getInstance(galeriaModal);
        if (bsModal) bsModal.hide();
      }
      // Cria overlay
      const url = SITE_URL + '/uploads/galeria/' + fotosGaleria[galeriaIndex];
      let old = document.getElementById('overlay-original-galeria');
      if (old) old.remove();
      let overlay = document.createElement('div');
      overlay.id = 'overlay-original-galeria';
      overlay.style.position = 'fixed';
      overlay.style.top = 0;
      overlay.style.left = 0;
      overlay.style.width = '100vw';
      overlay.style.height = '100vh';
      overlay.style.background = 'rgba(0,0,0,0.98)';
      overlay.style.zIndex = 999999;
      overlay.style.display = 'flex';
      overlay.style.alignItems = 'center';
      overlay.style.justifyContent = 'center';
      overlay.style.overflow = 'auto';
      overlay.innerHTML = `
        <img src="${url}" style="display:block;margin:auto;">
        <button id="fecharOriginalGaleria" style="
          position:fixed;
          top:32px;
          right:40px;
          font-size:3rem;
          background:rgba(255,255,255,0.95);
          color:#e83e8c;
          border:none;
          border-radius:50%;
          width:64px;
          height:64px;
          box-shadow:0 2px 12px #0003;
          cursor:pointer;
          z-index:100000;
          display:flex;
          align-items:center;
          justify-content:center;
          transition:background .2s;
        " aria-label="Fechar imagem em tamanho original">&times;</button>
      `;
      document.body.appendChild(overlay);
      overlay.tabIndex = 0;
      overlay.focus();
      document.getElementById('fecharOriginalGaleria').onclick = function() {
        overlay.remove();
        // Remove backdrops residuais
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        setTimeout(function() {
          if (galeriaModal && typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(galeriaModal);
            bsModal.show();
          }
        }, 10);
      };
      overlay.addEventListener('keydown', function(ev) {
        if (ev.key === 'Escape') {
          overlay.remove();
          // Remove backdrops residuais
          document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
          document.body.classList.remove('modal-open');
          document.body.style.overflow = '';
          setTimeout(function() {
            if (galeriaModal && typeof bootstrap !== 'undefined') {
              const bsModal = new bootstrap.Modal(galeriaModal);
              bsModal.show();
            }
          }, 10);
        }
      });
      overlay.addEventListener('click', function(ev) {
        if (ev.target === overlay) {
          overlay.remove();
          // Remove backdrops residuais
          document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
          document.body.classList.remove('modal-open');
          document.body.style.overflow = '';
          setTimeout(function() {
            if (galeriaModal && typeof bootstrap !== 'undefined') {
              const bsModal = new bootstrap.Modal(galeriaModal);
              bsModal.show();
            }
          }, 10);
        }
      });
    });
  }
});
</script>
</body>
</html> 