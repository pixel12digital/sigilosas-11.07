<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Proteção de sessão admin
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Editar Perfil da Acompanhante';
require_once '../includes/admin-header.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo '<div class="alert alert-danger">ID de acompanhante inválido.</div>';
    require_once '../includes/admin-footer.php';
    exit;
}

// Processar edição e ações
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['acompanhante_id'])) {
        // Processar ações de aprovação
        $acompanhante_id = (int)$_POST['acompanhante_id'];
        $action = $_POST['action'];
        $motivo = trim($_POST['motivo'] ?? '');
        
        try {
            switch ($action) {
                case 'aprovar':
                    $db->update('acompanhantes', [
                        'status' => 'aprovado',
                        'revisado_por' => $_SESSION['user_id'],
                        'data_revisao' => date('Y-m-d H:i:s'),
                        'motivo_rejeicao' => null
                    ], 'id = ?', [$acompanhante_id]);
                    $success = 'Acompanhante aprovada com sucesso!';
                    break;
                    
                case 'rejeitar':
                    if (empty($motivo)) {
                        $error = 'Motivo da rejeição é obrigatório.';
                        break;
                    }
                    $db->update('acompanhantes', [
                        'status' => 'rejeitado',
                        'revisado_por' => $_SESSION['user_id'],
                        'data_revisao' => date('Y-m-d H:i:s'),
                        'motivo_rejeicao' => $motivo
                    ], 'id = ?', [$acompanhante_id]);
                    $success = 'Acompanhante rejeitada com sucesso!';
                    break;
                    
                case 'bloquear':
                    if (empty($motivo)) {
                        $error = 'Motivo do bloqueio é obrigatório.';
                        break;
                    }
                    $db->update('acompanhantes', [
                        'status' => 'bloqueado',
                        'bloqueado' => 1,
                        'motivo_bloqueio' => $motivo,
                        'revisado_por' => $_SESSION['user_id'],
                        'data_revisao' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$acompanhante_id]);
                    $success = 'Acompanhante bloqueada com sucesso!';
                    break;
                    
                case 'excluir':
                    // Buscar arquivos para excluir fisicamente
                    $fotos = $db->fetchAll("SELECT url FROM fotos WHERE acompanhante_id = ?", [$acompanhante_id]);
                    $videos_verificacao = $db->fetchAll("SELECT url FROM videos_verificacao WHERE acompanhante_id = ?", [$acompanhante_id]);
                    $videos_publicos = $db->fetchAll("SELECT url FROM videos_publicos WHERE acompanhante_id = ?", [$acompanhante_id]);
                    $documentos = $db->fetchAll("SELECT url FROM documentos_acompanhante WHERE acompanhante_id = ?", [$acompanhante_id]);
                    
                    // Excluir arquivos físicos
                    foreach ($fotos as $foto) {
                        if (!empty($foto['url'])) {
                            $arquivo = __DIR__ . '/../uploads/galeria/' . $foto['url'];
                            if (file_exists($arquivo)) unlink($arquivo);
                            $arquivo_perfil = __DIR__ . '/../uploads/perfil/' . $foto['url'];
                            if (file_exists($arquivo_perfil)) unlink($arquivo_perfil);
                        }
                    }
                    foreach ($videos_verificacao as $video) {
                        if (!empty($video['url'])) {
                            $arquivo = __DIR__ . '/../uploads/verificacao/' . $video['url'];
                            if (file_exists($arquivo)) unlink($arquivo);
                        }
                    }
                    foreach ($videos_publicos as $video) {
                        if (!empty($video['url'])) {
                            $arquivo = __DIR__ . '/../uploads/videos_publicos/' . $video['url'];
                            if (file_exists($arquivo)) unlink($arquivo);
                        }
                    }
                    foreach ($documentos as $doc) {
                        if (!empty($doc['url'])) {
                            $arquivo = __DIR__ . '/../uploads/documentos/' . $doc['url'];
                            if (file_exists($arquivo)) unlink($arquivo);
                        }
                    }
                    
                    // Excluir registros do banco (todas as tabelas relacionadas)
                    $db->delete('fotos', 'acompanhante_id = ?', [$acompanhante_id]);
                    $db->delete('videos_verificacao', 'acompanhante_id = ?', [$acompanhante_id]);
                    $db->delete('videos_publicos', 'acompanhante_id = ?', [$acompanhante_id]);
                    $db->delete('documentos_acompanhante', 'acompanhante_id = ?', [$acompanhante_id]);
                    $db->delete('valores_atendimento', 'acompanhante_id = ?', [$acompanhante_id]);
                    $db->delete('horarios_atendimento', 'acompanhante_id = ?', [$acompanhante_id]);
                    $db->delete('avaliacoes', 'acompanhante_id = ?', [$acompanhante_id]);
                    $db->delete('denuncias', 'acompanhante_id = ?', [$acompanhante_id]);
                    
                    // Excluir acompanhante
                    $db->delete('acompanhantes', 'id = ?', [$acompanhante_id]);
                    $success = 'Acompanhante e todos os dados relacionados excluídos com sucesso!';
                    // Redirecionar após exclusão
                    header('Location: aprovar-acompanhantes.php?success=' . urlencode($success));
                    exit;
                    break;
            }
        } catch (Exception $e) {
            $error = 'Erro ao processar ação: ' . $e->getMessage();
        }
    } else {
        // Processar edição normal
        $cidade_id = $_POST['cidade_id'] ?? $acompanhante['cidade_id'];
        if (empty($cidade_id) || !is_numeric($cidade_id)) {
            $cidade_id = $acompanhante['cidade_id'];
        }
        $data = [
            'nome' => trim($_POST['nome']),
            'apelido' => trim($_POST['apelido']),
            'email' => trim($_POST['email']),
            'telefone' => trim($_POST['telefone']),
            'idade' => !empty($_POST['idade']) ? (int)$_POST['idade'] : null,
            'altura' => !empty($_POST['altura']) ? (float)$_POST['altura'] : null,
            'peso' => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,
            'medidas' => $_POST['medidas'] ?? null,
            'endereco' => $_POST['endereco'] ?? null,
            'descricao' => $_POST['descricao'] ?? null,
            'status' => $_POST['status'],
            'verificado' => isset($_POST['verificado']) ? 1 : 0,
            'destaque' => isset($_POST['destaque']) ? 1 : 0,
            'cidade_id' => $cidade_id
        ];
        try {
            $db->update('acompanhantes', $data, 'id = ?', [$id]);
            $success = 'Dados atualizados com sucesso!';
        } catch (Exception $e) {
            $error = 'Erro ao atualizar: ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error) && !empty($success)) {
    echo '<script>window.location.href = "acompanhantes.php";</script>';
    exit;
}

// Buscar dados da acompanhante
$acompanhante = $db->fetch("
    SELECT a.*, c.nome as cidade_nome, c.estado_id as cidade_estado_id, e.uf as estado_uf, e.nome as estado_nome
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON c.estado_id = e.id
    WHERE a.id = ?
", [$id]);

// Processamento removido - agora usa apenas o botão principal de aprovação

// Processar exclusão de vídeo público (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_video_publico_id'])) {
    $video_id = (int)$_POST['excluir_video_publico_id'];
    
    // Buscar o vídeo para obter o nome do arquivo
    $video = $db->fetch("SELECT * FROM videos_publicos WHERE id = ? AND acompanhante_id = ?", [$video_id, $id]);
    
    if ($video) {
        // Excluir o arquivo físico
        $arquivo_video = __DIR__ . '/../uploads/videos_publicos/' . $video['url'];
        if (file_exists($arquivo_video)) {
            unlink($arquivo_video);
        }
        
        // Excluir o registro do banco
        $db->delete('videos_publicos', 'id = ? AND acompanhante_id = ?', [$video_id, $id]);
        
        // Resposta para AJAX (não redirecionar)
        http_response_code(200);
        exit;
    } else {
        // Erro para AJAX
        http_response_code(404);
        exit;
    }
}

if (!$acompanhante) {
    echo '<div class="alert alert-danger">Acompanhante não encontrada.</div>';
    require_once '../includes/admin-footer.php';
    exit;
}
// Buscar mídias
$fotos = $db->fetchAll("SELECT * FROM fotos WHERE acompanhante_id = ? ORDER BY created_at DESC", [$id]);
$videos = $db->fetchAll("SELECT * FROM videos_verificacao WHERE acompanhante_id = ? ORDER BY created_at DESC", [$id]);
// Buscar documentos já enviados
$documentos = $db->fetchAll("SELECT * FROM documentos_acompanhante WHERE acompanhante_id = ? AND tipo = 'rg' ORDER BY created_at DESC", [$id]);
// Buscar vídeos de verificação já enviados
$videos_verificacao = $db->fetchAll("SELECT * FROM videos_verificacao WHERE acompanhante_id = ? ORDER BY created_at DESC", [$id]);
// Buscar cidades para select
$cidades = $db->fetchAll("SELECT c.*, e.uf FROM cidades c LEFT JOIN estados e ON c.estado_id = e.id ORDER BY c.nome");

$locais = json_decode($acompanhante['local_atendimento'] ?? '[]', true) ?: [];
$especialidades = json_decode($acompanhante['especialidades'] ?? '[]', true) ?: [];
// Buscar fotos da galeria
$fotos_galeria = $db->fetchAll("SELECT * FROM fotos WHERE acompanhante_id = ? AND tipo = 'galeria' ORDER BY ordem, created_at", [$id]);
?>
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-user-edit"></i> Editar Perfil</h2>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <!-- FOTO DE PERFIL -->
        <div class="col-12 mt-2 mb-4 text-center">
            <h5>Foto de Perfil</h5>
            <?php
            $foto_perfil = $db->fetch("SELECT url FROM fotos WHERE acompanhante_id = ? AND tipo = 'perfil' AND principal = 1 ORDER BY created_at DESC LIMIT 1", [$id]);
            $foto_perfil_url = $foto_perfil['url'] ?? 'assets/img/default-avatar.svg';
            if ($foto_perfil_url !== 'assets/img/default-avatar.svg') {
                $miniatura_path = SITE_URL . '/uploads/perfil/' . htmlspecialchars($foto_perfil_url);
            } else {
                $miniatura_path = SITE_URL . '/assets/img/default-avatar.svg';
            }
            ?>
            <img id="fotoPerfilMiniatura"
                 src="<?php echo $miniatura_path; ?>"
                 alt="Foto de Perfil"
                 style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:2px solid #ccc;">
            <input type="file" id="inputFotoPerfil" name="foto" accept="image/*" style="max-width:200px; display:inline-block; margin-top:10px;">
        </div>
        <!-- DADOS PESSOAIS E APARÊNCIA -->
        <div class="col-12"><h5 class="mt-3">Dados Pessoais</h5></div>
        <div class="col-md-6">
            <label for="nome" class="form-label">Nome Completo *</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($acompanhante['nome'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="apelido" class="form-label">Apelido *</label>
            <input type="text" class="form-control" id="apelido" name="apelido" value="<?php echo htmlspecialchars($acompanhante['apelido'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">E-mail *</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($acompanhante['email'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="senha" class="form-label">Senha (deixe em branco para não alterar)</label>
            <input type="password" class="form-control" id="senha" name="senha" autocomplete="new-password">
        </div>
        <!-- Campo telefone oculto - valor será copiado do WhatsApp -->
        <input type="hidden" id="telefone" name="telefone" value="<?php echo htmlspecialchars($acompanhante['telefone'] ?? ''); ?>">
        
        <div class="col-md-6">
            <label for="whatsapp" class="form-label">WhatsApp *</label>
            <input type="tel" class="form-control" id="whatsapp" name="whatsapp" 
                   pattern="^\d{10,11}$" 
                   placeholder="DDD + número (ex: 41999999999)" 
                   value="<?php echo htmlspecialchars(preg_replace('/^\+55/', '', $acompanhante['telefone'] ?? '')); ?>" 
                   required>
            <div class="form-text">Digite apenas DDD e número, sem espaços ou traços. Ex: 41999999999</div>
        </div>
        <div class="col-md-4">
            <label for="idade" class="form-label">Idade *</label>
            <input type="number" class="form-control" id="idade" name="idade" min="18" max="99" value="<?php echo htmlspecialchars($acompanhante['idade'] ?? ''); ?>" required>
        </div>
        <div class="col-md-4">
            <label for="genero" class="form-label">Gênero *</label>
            <select class="form-select" id="genero" name="genero" required>
                <option value="">Selecione</option>
                <option value="feminino" <?php if(($acompanhante['genero'] ?? '')==='feminino') echo 'selected'; ?>>Feminino</option>
                <option value="masculino" <?php if(($acompanhante['genero'] ?? '')==='masculino') echo 'selected'; ?>>Masculino</option>
                <option value="trans" <?php if(($acompanhante['genero'] ?? '')==='trans') echo 'selected'; ?>>Trans</option>
                <option value="outro" <?php if(($acompanhante['genero'] ?? '')==='outro') echo 'selected'; ?>>Outro</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="preferencia_sexual" class="form-label">Preferência Sexual</label>
            <select class="form-select" id="preferencia_sexual" name="preferencia_sexual">
                <option value="">Selecione</option>
                <option value="homens" <?php if(($acompanhante['preferencia_sexual'] ?? '')==='homens') echo 'selected'; ?>>Homens</option>
                <option value="mulheres" <?php if(($acompanhante['preferencia_sexual'] ?? '')==='mulheres') echo 'selected'; ?>>Mulheres</option>
                <option value="todos" <?php if(($acompanhante['preferencia_sexual'] ?? '')==='todos') echo 'selected'; ?>>Todos</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Estado *</label>
            <div class="form-control-plaintext" style="font-weight:bold;">
                <?php echo htmlspecialchars($acompanhante['estado_nome'] ?? $acompanhante['estado_uf'] ?? ''); ?>
            </div>
            <div class="form-text">Estado de atendimento</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Cidade *</label>
            <div class="form-control-plaintext" style="font-weight:bold;">
                <?php echo htmlspecialchars($acompanhante['cidade_nome'] ?? ''); ?>
            </div>
            <div class="form-text">Cidade de atendimento</div>
        </div>
        <div class="col-md-4">
            <label for="bairro" class="form-label">Bairro</label>
            <input type="text" class="form-control" id="bairro" name="bairro" value="<?php echo htmlspecialchars($acompanhante['bairro'] ?? ''); ?>">
        </div>
        <div class="col-md-4">
            <label for="endereco" class="form-label">Endereço (Rua)</label>
            <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo htmlspecialchars($acompanhante['endereco'] ?? ''); ?>">
        </div>
        <div class="col-md-4">
            <label for="cep" class="form-label">CEP</label>
            <input type="text" class="form-control" id="cep" name="cep" value="<?php echo htmlspecialchars($acompanhante['cep'] ?? ''); ?>">
        </div>
        <!-- SEÇÃO SOBRE MIM -->
        <?php if (!empty($acompanhante['sobre_mim'])): ?>
        <div class="col-12">
            <label class="form-label" style="font-weight:bold;font-size:1.2em;">Sobre Mim</label>
            <div class="card mb-3">
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($acompanhante['sobre_mim'])); ?></p>
                </div>
            </div>
            <div class="form-text">Este texto foi preenchido pela acompanhante e é exibido no perfil público.</div>
        </div>
        <?php endif; ?>
        <!-- FIM SEÇÃO SOBRE MIM -->
        <!-- Aparência -->
        <div class="col-12"><h5 class="mt-4">Aparência</h5></div>
        <div class="row">
            <div class="col-md-2">
                <label for="altura" class="form-label">Altura (cm)</label>
                <input type="number" class="form-control" id="altura" name="altura" step="0.01" value="<?php echo htmlspecialchars($acompanhante['altura'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label for="peso" class="form-label">Peso (kg)</label>
                <input type="number" class="form-control" id="peso" name="peso" value="<?php echo htmlspecialchars($acompanhante['peso'] ?? ''); ?>">
            </div>

        </div>
        <div class="row mt-2">
            <div class="col-md-2">
                <label for="etnia" class="form-label">Etnia</label>
                <select class="form-select" id="etnia" name="etnia">
                    <option value="">Selecione</option>
                    <option value="branca" <?php if(($acompanhante['etnia'] ?? '')==='branca') echo 'selected'; ?>>Branca</option>
                    <option value="negra" <?php if(($acompanhante['etnia'] ?? '')==='negra') echo 'selected'; ?>>Negra</option>
                    <option value="parda" <?php if(($acompanhante['etnia'] ?? '')==='parda') echo 'selected'; ?>>Parda</option>
                    <option value="asiatica" <?php if(($acompanhante['etnia'] ?? '')==='asiatica') echo 'selected'; ?>>Asiática</option>
                    <option value="indigena" <?php if(($acompanhante['etnia'] ?? '')==='indigena') echo 'selected'; ?>>Indígena</option>
                    <option value="outra" <?php if(($acompanhante['etnia'] ?? '')==='outra') echo 'selected'; ?>>Outra</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="cor_olhos" class="form-label">Cor dos Olhos</label>
                <select class="form-select" id="cor_olhos" name="cor_olhos">
                    <option value="">Selecione</option>
                    <option value="castanhos" <?php if(($acompanhante['cor_olhos'] ?? '')==='castanhos') echo 'selected'; ?>>Castanhos</option>
                    <option value="azuis" <?php if(($acompanhante['cor_olhos'] ?? '')==='azuis') echo 'selected'; ?>>Azuis</option>
                    <option value="verdes" <?php if(($acompanhante['cor_olhos'] ?? '')==='verdes') echo 'selected'; ?>>Verdes</option>
                    <option value="pretos" <?php if(($acompanhante['cor_olhos'] ?? '')==='pretos') echo 'selected'; ?>>Pretos</option>
                    <option value="outros" <?php if(($acompanhante['cor_olhos'] ?? '')==='outros') echo 'selected'; ?>>Outros</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="cor_cabelo" class="form-label">Cor do Cabelo</label>
                <input type="text" class="form-control" id="cor_cabelo" name="cor_cabelo" value="<?php echo htmlspecialchars($acompanhante['cor_cabelo'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label for="estilo_cabelo" class="form-label">Estilo do Cabelo</label>
                <select class="form-select" id="estilo_cabelo" name="estilo_cabelo">
                    <option value="">Selecione</option>
                    <option value="liso" <?php if(($acompanhante['estilo_cabelo'] ?? '')==='liso') echo 'selected'; ?>>Liso</option>
                    <option value="ondulado" <?php if(($acompanhante['estilo_cabelo'] ?? '')==='ondulado') echo 'selected'; ?>>Ondulado</option>
                    <option value="cacheado" <?php if(($acompanhante['estilo_cabelo'] ?? '')==='cacheado') echo 'selected'; ?>>Cacheado</option>
                    <option value="crespo" <?php if(($acompanhante['estilo_cabelo'] ?? '')==='crespo') echo 'selected'; ?>>Crespo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="tamanho_cabelo" class="form-label">Tamanho do Cabelo</label>
                <select class="form-select" id="tamanho_cabelo" name="tamanho_cabelo">
                    <option value="">Selecione</option>
                    <option value="curto" <?php if(($acompanhante['tamanho_cabelo'] ?? '')==='curto') echo 'selected'; ?>>Curto</option>
                    <option value="medio" <?php if(($acompanhante['tamanho_cabelo'] ?? '')==='medio') echo 'selected'; ?>>Médio</option>
                    <option value="longo" <?php if(($acompanhante['tamanho_cabelo'] ?? '')==='longo') echo 'selected'; ?>>Longo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="silicone" class="form-label">Silicone</label>
                <select class="form-select" id="silicone" name="silicone">
                    <option value="0" <?php if(($acompanhante['silicone'] ?? 0)==0) echo 'selected'; ?>>Não</option>
                    <option value="1" <?php if(($acompanhante['silicone'] ?? 0)==1) echo 'selected'; ?>>Sim</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="tatuagens" class="form-label">Tatuagens</label>
                <select class="form-select" id="tatuagens" name="tatuagens">
                    <option value="0" <?php if(($acompanhante['tatuagens'] ?? 0)==0) echo 'selected'; ?>>Não</option>
                    <option value="1" <?php if(($acompanhante['tatuagens'] ?? 0)==1) echo 'selected'; ?>>Sim</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="piercings" class="form-label">Piercings</label>
                <select class="form-select" id="piercings" name="piercings">
                    <option value="0" <?php if(($acompanhante['piercings'] ?? 0)==0) echo 'selected'; ?>>Não</option>
                    <option value="1" <?php if(($acompanhante['piercings'] ?? 0)==1) echo 'selected'; ?>>Sim</option>
                </select>
            </div>
        </div>
        <!-- Preferências e Serviços -->
        <div class="col-12"><h5 class="mt-4">Preferências e Serviços</h5></div>
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Local de Atendimento</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="local_atendimento[]" value="domicilio" id="local_domicilio" <?php if(in_array('domicilio', $locais)) echo 'checked'; ?>>
                    <label class="form-check-label" for="local_domicilio">Domicílio</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="local_atendimento[]" value="motel" id="local_motel" <?php if(in_array('motel', $locais)) echo 'checked'; ?>>
                    <label class="form-check-label" for="local_motel">Motel</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="local_atendimento[]" value="hotel" id="local_hotel" <?php if(in_array('hotel', $locais)) echo 'checked'; ?>>
                    <label class="form-check-label" for="local_hotel">Hotel</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="local_atendimento[]" value="casa_propria" id="local_casa_propria" <?php if(in_array('casa_propria', $locais)) echo 'checked'; ?>>
                    <label class="form-check-label" for="local_casa_propria">Casa Própria</label>
                </div>
                <div class="form-text">Selecione um ou mais locais de atendimento.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Especialidades</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="especialidades[]" value="convencional" id="esp_convencional" <?php if(in_array('convencional', $especialidades)) echo 'checked'; ?>>
                    <label class="form-check-label" for="esp_convencional">Convencional</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="especialidades[]" value="fetiche" id="esp_fetiche" <?php if(in_array('fetiche', $especialidades)) echo 'checked'; ?>>
                    <label class="form-check-label" for="esp_fetiche">Fetiche</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="especialidades[]" value="massagem" id="esp_massagem" <?php if(in_array('massagem', $especialidades)) echo 'checked'; ?>>
                    <label class="form-check-label" for="esp_massagem">Massagem</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="especialidades[]" value="striptease" id="esp_stript" <?php if(in_array('striptease', $especialidades)) echo 'checked'; ?>>
                    <label class="form-check-label" for="esp_stript">Striptease</label>
                </div>
                <div class="form-text">Selecione uma ou mais especialidades.</div>
            </div>

        </div>
        <!-- Redes Sociais -->
        <div class="col-12"><h5 class="mt-4">Redes Sociais</h5></div>
        <div class="col-md-3">
            <label for="instagram" class="form-label">Instagram</label>
            <input type="text" class="form-control" id="instagram" name="instagram" value="<?php echo htmlspecialchars($acompanhante['instagram'] ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <label for="twitter" class="form-label">Twitter</label>
            <input type="text" class="form-control" id="twitter" name="twitter" value="<?php echo htmlspecialchars($acompanhante['twitter'] ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <label for="tiktok" class="form-label">TikTok</label>
            <input type="text" class="form-control" id="tiktok" name="tiktok" value="<?php echo htmlspecialchars($acompanhante['tiktok'] ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <label for="site" class="form-label">Site</label>
            <input type="text" class="form-control" id="site" name="site" value="<?php echo htmlspecialchars($acompanhante['site'] ?? ''); ?>">
        </div>
        <!-- BLOCO HORÁRIOS ATENDIMENTO POR DIA DA SEMANA -->
        <div class="col-12 mt-4">
            <h5 class="mb-3"><i class="fas fa-clock"></i> Horário de Atendimento</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center" style="max-width:600px;margin:auto;">
                    <thead>
                        <tr>
                            <th>Dia</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th>Atende?</th>
                        </tr>
                    </thead>
                    <tbody>
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
                        $horarios = $db->fetchAll("SELECT * FROM horarios_atendimento WHERE acompanhante_id = ?", [$id]);
                        $horarios_map = [];
                        foreach ($horarios as $h) {
                            $horarios_map[$h['dia_semana']] = $h;
                        }
                        foreach ($dias_semana as $num => $nome):
                            $inicio = $horarios_map[$num]['hora_inicio'] ?? '08:00';
                            $fim = $horarios_map[$num]['hora_fim'] ?? '23:59';
                            $atende = isset($horarios_map[$num]);
                        ?>
                        <tr>
                            <td><strong><?php echo $nome; ?></strong></td>
                            <td>
                                <input type="time" class="form-control" name="horario_inicio[<?php echo $num; ?>]" value="<?php echo $inicio; ?>" <?php if(!$atende) echo 'style=\"display:none\"'; ?> data-dia="<?php echo $num; ?>">
                            </td>
                            <td>
                                <input type="time" class="form-control" name="horario_fim[<?php echo $num; ?>]" value="<?php echo $fim; ?>" <?php if(!$atende) echo 'style=\"display:none\"'; ?> data-dia="<?php echo $num; ?>">
                            </td>
                            <td>
                                <input type="checkbox" class="form-check-input atende-dia" name="atende[<?php echo $num; ?>]" value="1" <?php if($atende) echo 'checked'; ?> data-dia="<?php echo $num; ?>">
                                <label class="form-check-label">Atende</label>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="form-text text-center mt-2">Desmarque os dias que não atende. A disponibilidade do anunciante não é garantida pelo seu horário de atendimento.</div>
        </div>
        <!-- BLOCO VALORES POR TEMPO DE SERVIÇO -->
        <div class="col-12 mt-4">
            <h5 class="mb-3"><i class="fas fa-dollar-sign"></i> Valores</h5>
            
            <!-- Preços Gerais -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label">Preço Padrão (R$)</label>
                    <input type="number" class="form-control" id="valor_padrao" name="valor_padrao" step="0.01" value="<?php echo htmlspecialchars($acompanhante['valor_padrao'] ?? ''); ?>" placeholder="Ex: 150.00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Preço Promocional (R$)</label>
                    <input type="number" class="form-control" id="valor_promocional" name="valor_promocional" step="0.01" value="<?php echo htmlspecialchars($acompanhante['valor_promocional'] ?? ''); ?>" placeholder="Ex: 120.00">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Idiomas</label>
                    <input type="text" class="form-control" id="idiomas" name="idiomas" value="<?php echo htmlspecialchars($acompanhante['idiomas'] ?? ''); ?>" placeholder="Ex: Português, Inglês, Espanhol">
                    <div class="form-text">Digite os idiomas separados por vírgula.</div>
                </div>
            </div>
            
            <!-- Valores por Tempo de Atendimento -->
            <h6 class="mb-3">Valores por Tempo de Atendimento</h6>
            <div class="row">
                <?php
                $tempos = [
                    '15min' => '15 minutos',
                    '30min' => '30 minutos',
                    '1h' => '1 hora',
                    '2h' => '2 horas',
                    '4h' => '4 horas',
                    'diaria' => 'Diária',
                    'pernoite' => 'Pernoite',
                    'diaria_viagem' => 'Diária de viagem'
                ];
                $valores_atendimento = [];
                $rows = $db->fetchAll("SELECT * FROM valores_atendimento WHERE acompanhante_id = ?", [$id]);
                foreach ($rows as $row) {
                    $valores_atendimento[$row['tempo']] = $row;
                }
                foreach ($tempos as $key => $label): ?>
                    <div class="col-md-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input valor-tempo-check" type="checkbox" id="tempo_<?php echo $key; ?>" name="valores[<?php echo $key; ?>][disponivel]" value="1"
                                <?php if(!empty($valores_atendimento[$key]['disponivel'])) echo 'checked'; ?>>
                            <label class="form-check-label" for="tempo_<?php echo $key; ?>"><?php echo $label; ?></label>
                        </div>
                        <input type="number" step="0.01" class="form-control mt-1 valor-tempo-input" name="valores[<?php echo $key; ?>][valor]"
                            placeholder="Valor (R$)" value="<?php echo htmlspecialchars($valores_atendimento[$key]['valor'] ?? ''); ?>"
                            <?php if(empty($valores_atendimento[$key]['disponivel'])) echo 'disabled'; ?>>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- SEÇÃO DE DOCUMENTOS DE IDENTIDADE (após valores) -->
        <div class="col-12 mt-5 text-center" id="secao-documentos">
            <h5 class="mb-3"><i class="fas fa-id-card"></i> Documento de Identidade (RG ou CNH)</h5>
            <div class="form-text mb-2">
                Envie a <b>frente e o verso</b> do documento de identidade (RG ou CNH) em um ou dois arquivos.<br>
                Pode ser foto ou PDF. Se possível, junte frente e verso em um único arquivo para maior praticidade.<br>
                <span style="color:#b94a48;font-size:13px;">Após excluir um documento, selecione um novo arquivo antes de salvar para adicionar outro documento.</span>
            </div>
            <h6 class="mt-3 mb-2">Documentos enviados</h6>
            <div class="d-flex justify-content-center gap-3 flex-wrap mt-2">
                <?php if (empty($documentos)): ?>
                    <div class="text-muted">Nenhum documento enviado.</div>
                <?php else: ?>
                    <?php foreach ($documentos as $doc): ?>
                        <?php $docPath = __DIR__ . '/../uploads/documentos/' . $doc['url']; if (!file_exists($docPath)) continue; ?>
                        <div class="d-inline-block position-relative" style="display:inline-block;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 doc-excluir-btn" style="z-index:2; border-radius:50%; width:24px; height:24px; padding:0; font-weight:bold; line-height:18px;" title="Excluir documento" onclick="excluirDocumento(<?php echo $doc['id']; ?>, this)">×</button>
                            <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $doc['url'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/documentos/<?php echo htmlspecialchars($doc['url']); ?>" style="width:100px; height:70px; object-fit:cover; border:1px solid #ccc; border-radius:6px;">
                            <?php elseif (preg_match('/\.pdf$/i', $doc['url'])): ?>
                                <a href="<?php echo SITE_URL; ?>/uploads/documentos/<?php echo htmlspecialchars($doc['url']); ?>" target="_blank">Ver PDF</a>
                            <?php endif; ?>
                            <div class="mt-1 text-center">
                                <span class="badge bg-<?php echo $doc['verificado'] ? 'success' : 'warning text-dark'; ?> small"><?php echo $doc['verificado'] ? 'Verificado' : 'Pendente'; ?></span>
                                <?php if (!$doc['verificado']): ?>
                                    <button type="button" class="btn btn-success btn-sm ms-1" data-midia-aprovar data-midia-tipo="documento" data-midia-id="<?php echo $doc['id']; ?>" title="Aprovar">✓</button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-danger btn-sm ms-1" data-midia-reprovar data-midia-tipo="documento" data-midia-id="<?php echo $doc['id']; ?>" title="Reprovar">✗</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- SEÇÃO DE VÍDEO DE VERIFICAÇÃO -->
        <div class="col-12 mt-4 text-center" id="secao-video-verificacao">
            <h5 class="mb-3"><i class="fas fa-video"></i> Vídeo de Verificação</h5>
            <div class="mb-2" style="max-width:400px;margin:auto;">
                <input type="file" id="inputVideoVerificacao" name="video_verificacao" accept="video/*" style="max-width:300px; display:inline-block;">
                <button type="button" class="btn btn-primary ms-2" id="btnUploadVideo">Enviar Vídeo</button>
            </div>
            <div id="videoVerificacaoMsg" class="mt-2"></div>
            <div class="mt-3">
                <?php if (!empty($videos_verificacao)): ?>
                    <h6>Vídeo enviado:</h6>
                    <div class="d-inline-block position-relative" style="display:inline-block;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 video-excluir-btn" style="z-index:2; border-radius:50%; width:24px; height:24px; padding:0; font-weight:bold; line-height:18px;" title="Excluir vídeo" onclick="excluirVideoVerificacao(<?php echo $videos_verificacao[0]['id']; ?>, this)">×</button>
                        <video width="180" height="320" controls style="border-radius:12px; border:1px solid #ccc; background:#000; display:block; margin:auto; object-fit:cover;">
                            <source src="<?php echo SITE_URL; ?>/uploads/verificacao/<?php echo htmlspecialchars($videos_verificacao[0]['url']); ?>" type="video/mp4">
                            Seu navegador não suporta vídeo.
                        </video>
                                                    <div class="mt-1 text-center">
                                <span class="badge bg-<?php echo $videos_verificacao[0]['verificado'] ? 'success' : 'warning text-dark'; ?> small"><?php echo $videos_verificacao[0]['verificado'] ? 'Verificado' : 'Pendente'; ?></span>
                            </div>
                    </div>
                <?php else: ?>
                    <div class="text-muted">Nenhum vídeo enviado.</div>
                <?php endif; ?>
            </div>
        </div>
        <!-- BLOCO GALERIA DE FOTOS -->
        <div class="col-12 mt-4 text-center">
            <h5><i class="fas fa-images"></i> Galeria de Fotos</h5>
            <div class="row justify-content-center" id="galeriaMiniaturas">
                <?php if (empty($fotos_galeria)): ?>
                    <div class="text-muted">Nenhuma foto na galeria.</div>
                <?php else: ?>
                    <?php foreach ($fotos_galeria as $foto): ?>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3 position-relative galeria-item" data-foto-id="<?php echo $foto['id']; ?>">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 galeria-excluir-btn" style="z-index:2; border-radius:50%; width:28px; height:28px; padding:0; font-weight:bold;" title="Excluir foto" onclick="excluirFotoGaleria(<?php echo $foto['id']; ?>, this)">×</button>
                            <img src="<?php echo SITE_URL; ?>/uploads/galeria/<?php echo htmlspecialchars($foto['url']); ?>"
                                 alt="Foto Galeria"
                                 style="width:100%;max-width:120px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #ccc;">
                            <div class="mt-1 text-center">
                                <span class="badge bg-<?php echo $foto['aprovada'] ? 'success' : 'warning text-dark'; ?> small"><?php echo $foto['aprovada'] ? 'Aprovada' : 'Pendente'; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="mt-3">
                <input type="file" id="inputGaleriaFotos" name="fotos_galeria[]" accept="image/*" multiple style="max-width:200px; display:inline-block;" onchange="previewGaleriaFotos(this)">
            </div>
            <div id="previewGaleria" class="d-flex justify-content-center gap-3 flex-wrap mt-2 mb-2"></div>
        </div>
        <!-- FIM SEÇÃO DE VÍDEO DE VERIFICAÇÃO -->

        <!-- SEÇÃO DE VÍDEOS PÚBLICOS (ADMIN) -->
        <div class="col-12 mt-4 text-center" id="secao-videos-publicos">
          <div class="d-flex justify-content-between align-items-center mb-2" style="max-width:900px;margin:auto;">
            <h5 class="mb-0"><i class="fas fa-video"></i> Vídeos Públicos</h5>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.location.reload()"><i class="fas fa-sync-alt"></i> Atualizar</button>
          </div>
          <div class="mb-2 text-muted">Vídeos enviados pela acompanhante para o perfil público. Apenas vídeos aprovados são exibidos no site.</div>
          <div id="listaVideosPublicos" class="row mt-4 g-3">
            <?php
            $videos_publicos = $db->fetchAll("SELECT * FROM videos_publicos WHERE acompanhante_id = ? ORDER BY created_at DESC", [$id]);
            if ($videos_publicos): ?>
              <?php foreach ($videos_publicos as $v): ?>
                <div class="col-md-4 col-6">
                  <div class="card h-100 shadow-sm position-relative">
                    <!-- Botão de exclusão -->
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                            style="z-index: 10; border-radius: 50%; width: 28px; height: 28px; padding: 0; font-weight: bold;" 
                            onclick="excluirVideoPublico(<?php echo $v['id']; ?>, this)" 
                            title="Excluir vídeo">×</button>
                    
                    <video src="<?php echo SITE_URL . '/uploads/videos_publicos/' . htmlspecialchars($v['url']); ?>" controls style="width:100%; max-width:140px; aspect-ratio:9/16; height:auto; max-height:250px; margin:auto; display:block; background:#000; object-fit:cover; border-radius:12px;"></video>
                    <div class="p-2">
                      <div class="fw-bold small mb-1"><?php echo htmlspecialchars($v['titulo'] ?? ''); ?></div>
                      <div class="text-muted small mb-1"><?php echo htmlspecialchars($v['descricao'] ?? ''); ?></div>
                      <div class="text-muted small">Enviado em: <?php echo date('d/m/Y', strtotime($v['created_at'])); ?></div>
                      <span class="badge bg-<?php
                        if ($v['status'] === 'aprovado') echo 'success';
                        elseif ($v['status'] === 'rejeitado') echo 'danger';
                        else echo 'warning text-dark';
                      ?> mt-1"><?php echo ucfirst($v['status']); ?></span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-muted">Nenhum vídeo público enviado.</div>
            <?php endif; ?>
          </div>
        </div>
        <!-- FIM SEÇÃO DE VÍDEOS PÚBLICOS (ADMIN) -->
        <?php
        $status = $acompanhante['status'] ?? 'pendente';
        function btnClass($current, $expected) {
            return $current === $expected ? 'btn btn-primary' : 'btn btn-outline-primary';
        }
        ?>
        <div class="col-12 text-center mt-4 mb-2">
            <div id="status-btn-group" class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary mx-1" data-status="aprovado">Aprovar</button>
                <button type="button" class="btn btn-outline-primary mx-1" data-status="rejeitado">Reprovar</button>
                <button type="button" class="btn btn-outline-primary mx-1" data-status="bloqueado">Bloquear</button>
            </div>
            <input type="hidden" id="status-input" name="status" value="<?php echo htmlspecialchars($acompanhante['status'] ?? 'pendente'); ?>">
        </div>
        <input type="hidden" name="cidade_id" value="<?php echo htmlspecialchars($acompanhante['cidade_id']); ?>">
        <button type="submit" class="btn btn-primary mt-4">
            <i class="fas fa-save"></i> Salvar Alterações
        </button>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.getElementById('estado_id');
    const cidadeSelect = document.getElementById('cidade_id');
    const cidadeId = "<?php echo htmlspecialchars($acompanhante['cidade_id'] ?? ''); ?>";

    function carregarCidades(estadoId, cidadeIdSelecionada) {
        if (!estadoId) {
            cidadeSelect.innerHTML = '<option value="">Selecione o estado primeiro</option>';
            return;
        }
        cidadeSelect.innerHTML = '<option>Carregando...</option>';
                    fetch(SITE_URL + '/api/cidades.php?estado_id=' + estadoId)
            .then(response => response.json())
            .then(data => {
                cidadeSelect.innerHTML = '<option value="">Selecione a cidade</option>';
                data.forEach(function(cidade) {
                    let selected = cidadeIdSelecionada == cidade.id ? 'selected' : '';
                    cidadeSelect.innerHTML += '<option value="' + cidade.id + '" ' + selected + '>' + cidade.nome + '</option>';
                });
            });
    }

    if (estadoSelect.value) {
        carregarCidades(estadoSelect.value, cidadeId);
    }

    estadoSelect.addEventListener('change', function() {
        carregarCidades(this.value, '');
    });
});

document.getElementById('btnUploadVideo').addEventListener('click', function() {
    var input = document.getElementById('inputVideoVerificacao');
    if (!input.files.length) {
        alert('Selecione um vídeo primeiro.');
        return;
    }
    var formData = new FormData();
    formData.append('video_verificacao', input.files[0]);
                fetch(SITE_URL + '/api/upload-video-verificacao.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        var msg = document.getElementById('videoVerificacaoMsg');
        if (data.success) {
            msg.innerHTML = '<span class="text-success">' + data.message + '</span>';
            if (data.filename && data.video_id) {
                var previewDiv = document.querySelector('#secao-video-verificacao .mt-3');
                previewDiv.innerHTML = `
                    <h6>Vídeo enviado:</h6>
                    <div class="d-inline-block position-relative" style="display:inline-block;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 video-excluir-btn" style="z-index:2; border-radius:50%; width:24px; height:24px; padding:0; font-weight:bold; line-height:18px;" title="Excluir vídeo" onclick="excluirVideoVerificacao(${data.video_id}, this)">×</button>
                        <video width="180" height="320" controls style="border-radius:12px; border:1px solid #ccc; background:#000; display:block; margin:auto; object-fit:cover;">
                            <source src="<?php echo SITE_URL; ?>/uploads/verificacao/${data.filename}" type="video/mp4">
                            Seu navegador não suporta vídeo.
                        </video>
                    </div>
                `;
            }
        } else {
            msg.innerHTML = '<span class="text-danger">' + data.message + '</span>';
        }
    })
    .catch(() => {
        document.getElementById('videoVerificacaoMsg').innerHTML = '<span class="text-danger">Erro ao enviar vídeo.</span>';
    });
});
function excluirVideoVerificacao(videoId, btn) {
    if (!confirm('Tem certeza que deseja excluir este vídeo?')) return;
    btn.disabled = true;
                fetch(SITE_URL + '/api/delete-video-verificacao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'video_id=' + encodeURIComponent(videoId)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Resposta exclusão vídeo:', data);
        if (data.success) {
            var item = btn.closest('.d-inline-block');
            if (item) item.remove();
            document.getElementById('videoVerificacaoMsg').innerHTML = '<span class="text-success">Vídeo excluído com sucesso.</span>';
        } else {
            alert(data.message || 'Erro ao excluir vídeo.');
            btn.disabled = false;
        }
    })
    .catch((err) => {
        alert('Erro ao excluir vídeo.');
        console.log('Erro fetch exclusão vídeo:', err);
        btn.disabled = false;
    });
}

function previewGaleriaFotos(input) {
    var preview = document.getElementById('previewGaleria');
    preview.innerHTML = '';
    if (input.files && input.files.length) {
        for (let i = 0; i < input.files.length; i++) {
            let file = input.files[i];
            if (!file.type.match('image.*')) continue;
            let reader = new FileReader();
            reader.onload = function(e) {
                let img = document.createElement('img');
                img.src = e.target.result;
                img.style = 'width:100px; height:70px; object-fit:cover; border:1px solid #ccc; border-radius:8px; margin:4px;';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    }
}
function excluirFotoGaleria(fotoId, btn) {
    if (!confirm('Tem certeza que deseja excluir esta foto?')) return;
    btn.disabled = true;
                fetch(SITE_URL + '/api/delete-foto-galeria.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'foto_id=' + encodeURIComponent(fotoId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var item = btn.closest('.galeria-item');
            if (item) item.remove();
        } else {
            alert(data.message || 'Erro ao excluir foto.');
            btn.disabled = false;
        }
    })
    .catch(() => {
        alert('Erro ao excluir foto.');
        btn.disabled = false;
    });
}

// Status inicial
const statusAtual = document.getElementById('status-input').value;
const btns = document.querySelectorAll('#status-btn-group button');
btns.forEach(btn => {
    if(btn.dataset.status === statusAtual) {
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary');
    }
    btn.addEventListener('click', function() {
        btns.forEach(b => b.classList.remove('btn-primary'));
        btns.forEach(b => b.classList.add('btn-outline-primary'));
        this.classList.remove('btn-outline-primary');
        this.classList.add('btn-primary');
        document.getElementById('status-input').value = this.dataset.status;
    });
});

// Aprovação/reprovação de vídeo público via AJAX
function atualizarStatusVideo(videoId, acao, btn) {
    btn.disabled = true;
    fetch(SITE_URL + '/api/moderar-video-publico.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'video_id=' + encodeURIComponent(videoId) + '&acao=' + encodeURIComponent(acao)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualiza badge e botões
            const card = btn.closest('.card');
            const badge = card.querySelector('.badge');
            badge.className = 'badge mt-1 bg-' + (acao === 'aprovar' ? 'success' : 'danger');
            badge.textContent = acao === 'aprovar' ? 'Aprovado' : 'Rejeitado';
            // Remove botões de ação
            card.querySelectorAll('form.d-inline').forEach(f => f.remove());
        } else {
            alert(data.message || 'Erro ao atualizar status.');
            btn.disabled = false;
        }
    })
    .catch(() => {
        alert('Erro ao atualizar status.');
        btn.disabled = false;
    });
}


// JavaScript para aprovação individual de mídias removido - agora usa apenas aprovação completa

// Sincronizar WhatsApp com campo telefone oculto (igual ao perfil da acompanhante)
document.getElementById('whatsapp').addEventListener('input', function() {
    const whatsapp = this.value;
    // Remover caracteres não numéricos
    const whatsappLimpo = whatsapp.replace(/\D+/g, '');
    // Adicionar +55 se não começar com 55
    let telefoneFormatado = whatsappLimpo;
    if (telefoneFormatado.length >= 10 && !telefoneFormatado.startsWith('55')) {
        telefoneFormatado = '55' + telefoneFormatado;
    }
    if (telefoneFormatado.length >= 12) {
        telefoneFormatado = '+' + telefoneFormatado;
    }
    // Atualizar campo oculto
    document.getElementById('telefone').value = telefoneFormatado;
});

// Função para excluir vídeo público
function excluirVideoPublico(videoId, btn) {
    if (!confirm('Tem certeza que deseja excluir este vídeo?\n\nEsta ação não pode ser desfeita!')) {
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Usar AJAX para evitar interferir com o formulário principal
    const formData = new FormData();
    formData.append('excluir_video_publico_id', videoId);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            // Remover o card do vídeo da interface
            const videoCard = btn.closest('.col-md-4, .col-6');
            if (videoCard) {
                videoCard.remove();
            }
            
            // Verificar se não há mais vídeos e mostrar mensagem
            const listaVideos = document.getElementById('listaVideosPublicos');
            const videosRestantes = listaVideos.querySelectorAll('.col-md-4, .col-6');
            if (videosRestantes.length === 0) {
                listaVideos.innerHTML = '<div class="text-muted">Nenhum vídeo público enviado.</div>';
            }
            
            // Mostrar mensagem de sucesso
            alert('Vídeo excluído com sucesso!');
        } else {
            alert('Erro ao excluir vídeo. Tente novamente.');
            btn.disabled = false;
            btn.innerHTML = '×';
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao excluir vídeo. Tente novamente.');
        btn.disabled = false;
        btn.innerHTML = '×';
    });
}
</script>
<?php require_once '../includes/admin-footer.php'; ?> 