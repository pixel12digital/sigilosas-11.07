<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_admin_session');
    session_start();
}
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'admin') {
    header('Location: login.php');
    exit;
}
/**
 * Cadastro/Edição de Acompanhante - Painel Admin
 * Arquivo: admin/acompanhante-editar.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$pageTitle = isset($_GET['id']) ? 'Editar Acompanhante' : 'Cadastrar Acompanhante';
include '../includes/admin-header.php';

$db = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';

if ($id) {
    // Buscar dados da acompanhante
    $acompanhante = $db->fetch("
        SELECT a.*, c.nome as cidade_nome, e.nome as estado_nome, e.uf as estado_uf
        FROM acompanhantes a
        LEFT JOIN cidades c ON a.cidade_id = c.id
        LEFT JOIN estados e ON c.estado_id = e.id
        WHERE a.id = ?
    ", [$id]);
    if (!$acompanhante) {
        header('Location: acompanhantes.php?error=Acompanhante não encontrada');
        exit;
    }
    // Buscar mídia da acompanhante
    $fotos = $db->fetchAll("SELECT * FROM fotos WHERE acompanhante_id = ? ORDER BY ordem, created_at", [$id]);
    $videos = $db->fetchAll("SELECT * FROM videos WHERE acompanhante_id = ? ORDER BY created_at", [$id]);
    $documentos = $db->fetchAll("SELECT * FROM documentos_acompanhante WHERE acompanhante_id = ? ORDER BY created_at", [$id]);
} else {
    // Cadastro: inicializar variáveis vazias
    $acompanhante = [
        'nome' => '', 'apelido' => '', 'email' => '', 'telefone' => '', 'idade' => '', 'altura' => '', 'peso' => '',
        'medidas' => '', 'endereco' => '', 'descricao' => '', 'valor_padrao' => '', 'valor_promocional' => '',
        'status' => 'pendente', 'verificado' => 0, 'destaque' => 0, 'cidade_id' => ''
    ];
    $fotos = $videos = $documentos = [];
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($id && $action === 'editar') {
        // Atualizar acompanhante existente
        $cidade_id = $_POST['cidade_id'] ?? ($acompanhante['cidade_id'] ?? null);
        if (empty($cidade_id) || !is_numeric($cidade_id)) {
            $cidade_id = $acompanhante['cidade_id'] ?? null;
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
            'valor_padrao' => !empty($_POST['valor_padrao']) ? (float)$_POST['valor_padrao'] : null,
            'valor_promocional' => !empty($_POST['valor_promocional']) ? (float)$_POST['valor_promocional'] : null,
            'status' => $_POST['status'],
            'verificado' => isset($_POST['verificado']) ? 1 : 0,
            'destaque' => isset($_POST['destaque']) ? 1 : 0,
            'cidade_id' => $cidade_id
        ];
        $db->update('acompanhantes', $data, 'id = ?', [$id]);
        
        // --- SALVAR HORÁRIOS DE ATENDIMENTO ---
        if (isset($_POST['horario_inicio'], $_POST['horario_fim'], $_POST['atende'])) {
            $dias_semana = [1,2,3,4,5,6,7];
            $db->query("DELETE FROM horarios_atendimento WHERE acompanhante_id = ?", [$id]);
            foreach ($dias_semana as $dia) {
                if (!isset($_POST['atende'][$dia])) continue;
                $inicio = $_POST['horario_inicio'][$dia] ?? '08:00';
                $fim = $_POST['horario_fim'][$dia] ?? '23:59';
                $db->insert('horarios_atendimento', [
                    'acompanhante_id' => $id,
                    'dia_semana' => $dia,
                    'hora_inicio' => $inicio,
                    'hora_fim' => $fim
                ]);
            }
        }
        // --- FIM HORÁRIOS ---
        
        header('Location: acompanhante-editar.php?id=' . $id . '&success=Dados atualizados com sucesso');
        exit;
    } elseif (!$id && $action === 'cadastrar') {
        // Cadastro de nova acompanhante
        $cidade_id = $_POST['cidade_id'] ?? ($acompanhante['cidade_id'] ?? null);
        if (empty($cidade_id) || !is_numeric($cidade_id)) {
            $cidade_id = $acompanhante['cidade_id'] ?? null;
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
            'valor_padrao' => !empty($_POST['valor_padrao']) ? (float)$_POST['valor_padrao'] : null,
            'valor_promocional' => !empty($_POST['valor_promocional']) ? (float)$_POST['valor_promocional'] : null,
            'status' => $_POST['status'],
            'verificado' => isset($_POST['verificado']) ? 1 : 0,
            'destaque' => isset($_POST['destaque']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'cidade_id' => $cidade_id
        ];
        $newId = $db->insert('acompanhantes', $data);
        header('Location: acompanhante-editar.php?id=' . $newId . '&success=Acompanhante cadastrada com sucesso');
        exit;
    } elseif ($id && $action === 'aprovar') {
        // Aprovar acompanhante sem sobrescrever outros campos
        $db->update('acompanhantes', ['status' => 'aprovado'], 'id = ?', [$id]);
        header('Location: acompanhante-editar.php?id=' . $id . '&success=Acompanhante aprovada com sucesso');
        exit;
    } elseif ($id && $action === 'bloquear') {
        // Bloquear acompanhante sem sobrescrever outros campos
        $db->update('acompanhantes', ['status' => 'bloqueado'], 'id = ?', [$id]);
        header('Location: acompanhante-editar.php?id=' . $id . '&success=Acompanhante bloqueada com sucesso');
        exit;
    } elseif ($id && $action === 'rejeitar') {
        // Rejeitar acompanhante sem sobrescrever outros campos
        $db->update('acompanhantes', ['status' => 'rejeitado'], 'id = ?', [$id]);
        header('Location: acompanhante-editar.php?id=' . $id . '&success=Acompanhante reprovada com sucesso');
        exit;
    }
}

// Buscar cidades para o select
$cidades = $db->fetchAll("SELECT c.*, e.uf FROM cidades c LEFT JOIN estados e ON c.estado_id = e.id ORDER BY c.nome");

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">
                <i class="fas fa-user-edit"></i> <?php echo $id ? 'Editar Acompanhante' : 'Cadastrar Acompanhante'; ?>
            </h1>
            <a href="acompanhantes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($id): ?>
        <div class="mb-3">
            <label class="form-label">ID:</label>
            <div><?php echo htmlspecialchars($acompanhante['id'] ?? ''); ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Cidade:</label>
            <div>
                <?php echo htmlspecialchars($acompanhante['cidade_nome'] ?? ''); ?>
                <?php if (!empty($acompanhante['estado_uf'])): ?>
                    - <?php echo htmlspecialchars($acompanhante['estado_uf']); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Cadastro:</label>
            <div>
                <?php echo !empty($acompanhante['created_at']) ? date('d/m/Y H:i', strtotime($acompanhante['created_at'])) : '-'; ?>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Última atualização:</label>
            <div>
                <?php echo !empty($acompanhante['updated_at']) ? date('d/m/Y H:i', strtotime($acompanhante['updated_at'])) : 'Nunca'; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Ações Rápidas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tools"></i> Ações Administrativas
                </div>
                <div class="card-body">
                    <div class="btn-group" role="group">
                        <form method="post" style="display: inline;" onsubmit="return confirm('Aprovar esta acompanhante?');">
                            <input type="hidden" name="action" value="aprovar">
                            <button type="submit" class="btn btn-success me-2">
                                <i class="fas fa-check"></i> Aprovar
                            </button>
                        </form>
                        
                        <form method="post" style="display: inline;" onsubmit="return confirm('Bloquear esta acompanhante?');">
                            <input type="hidden" name="action" value="bloquear">
                            <button type="submit" class="btn btn-warning me-2">
                                <i class="fas fa-ban"></i> Bloquear
                            </button>
                        </form>
                        
                        <form method="post" style="display: inline;" onsubmit="return confirm('Excluir esta acompanhante? Esta ação não pode ser desfeita.');">
                            <input type="hidden" name="action" value="excluir">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Formulário de Cadastro/Edição -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit"></i> Dados da Acompanhante
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $id ? 'editar' : 'cadastrar'; ?>">
                        <div class="row g-3">
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
                            <div class="col-md-4">
                                <label for="telefone" class="form-label">Telefone *</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($acompanhante['telefone'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="whatsapp" class="form-label">WhatsApp</label>
                                <input type="tel" class="form-control" id="whatsapp" name="whatsapp" pattern="^\d{10,11}$" placeholder="DDD + número (ex: 41999999999)" value="<?php echo isset($acompanhante['whatsapp']) ? preg_replace('/^\+55/', '', $acompanhante['whatsapp']) : ''; ?>">
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
                                <label for="estado_id" class="form-label">Estado *</label>
                                <select class="form-select" id="estado_id" name="estado_id" required>
                                    <option value="">Selecione um estado</option>
                                    <?php
                                    $estados = $db->fetchAll("SELECT id, nome, uf FROM estados WHERE ativo = 1 ORDER BY nome");
                                    foreach ($estados as $estado): ?>
                                        <option value="<?php echo $estado['id']; ?>" <?php if (($acompanhante['estado_id'] ?? 0) == $estado['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($estado['nome'] . ' (' . $estado['uf'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Selecione o estado de atendimento</div>
                            </div>
                            <div class="col-md-6">
                                <label for="cidade_id" class="form-label">Cidade *</label>
                                <select class="form-select" id="cidade_id" name="cidade_id" required>
                                    <option value="">Selecione o estado primeiro</option>
                                </select>
                                <div class="form-text">Sua cidade de atendimento</div>
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
                            <div class="col-12"><h5 class="mt-4">Aparência</h5></div>
                            <div class="col-md-2">
                                <label for="altura" class="form-label">Altura (cm)</label>
                                <input type="number" class="form-control" id="altura" name="altura" step="0.01" value="<?php echo htmlspecialchars($acompanhante['altura'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="peso" class="form-label">Peso (kg)</label>
                                <input type="number" class="form-control" id="peso" name="peso" value="<?php echo htmlspecialchars($acompanhante['peso'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="manequim" class="form-label">Manequim</label>
                                <input type="text" class="form-control" id="manequim" name="manequim" value="<?php echo htmlspecialchars($acompanhante['manequim'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="busto" class="form-label">Busto</label>
                                <input type="number" class="form-control" id="busto" name="busto" value="<?php echo htmlspecialchars($acompanhante['busto'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="cintura" class="form-label">Cintura</label>
                                <input type="number" class="form-control" id="cintura" name="cintura" value="<?php echo htmlspecialchars($acompanhante['cintura'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="quadril" class="form-label">Quadril</label>
                                <input type="number" class="form-control" id="quadril" name="quadril" value="<?php echo htmlspecialchars($acompanhante['quadril'] ?? ''); ?>">
                            </div>
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
                            <div class="col-12"><h5 class="mt-4">Preferências e Serviços</h5></div>
                            <div class="col-md-4">
                                <label for="local_atendimento" class="form-label">Local de Atendimento</label>
                                <select class="form-select" id="local_atendimento" name="local_atendimento[]" multiple required>
                                    <option value="domicilio" <?php if(in_array('domicilio', $locais)) echo 'selected'; ?>>Domicílio</option>
                                    <option value="motel" <?php if(in_array('motel', $locais)) echo 'selected'; ?>>Motel</option>
                                    <option value="hotel" <?php if(in_array('hotel', $locais)) echo 'selected'; ?>>Hotel</option>
                                    <option value="casa_propria" <?php if(in_array('casa_propria', $locais)) echo 'selected'; ?>>Casa Própria</option>
                                </select>
                                <div class="form-text">Segure Ctrl (Windows) ou Command (Mac) para selecionar mais de um.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="idiomas" class="form-label">Idiomas</label>
                                <select class="form-select" id="idiomas" name="idiomas[]" multiple>
                                    <option value="portugues" <?php if(in_array('portugues', $idiomas)) echo 'selected'; ?>>Português</option>
                                    <option value="ingles" <?php if(in_array('ingles', $idiomas)) echo 'selected'; ?>>Inglês</option>
                                    <option value="espanhol" <?php if(in_array('espanhol', $idiomas)) echo 'selected'; ?>>Espanhol</option>
                                    <option value="frances" <?php if(in_array('frances', $idiomas)) echo 'selected'; ?>>Francês</option>
                                </select>
                                <div class="form-text">Segure Ctrl (Windows) ou Command (Mac) para selecionar mais de um.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="especialidades" class="form-label">Especialidades</label>
                                <select class="form-select" id="especialidades" name="especialidades[]" multiple>
                                    <option value="convencional" <?php if(in_array('convencional', $especialidades)) echo 'selected'; ?>>Convencional</option>
                                    <option value="fetiche" <?php if(in_array('fetiche', $especialidades)) echo 'selected'; ?>>Fetiche</option>
                                    <option value="massagem" <?php if(in_array('massagem', $especialidades)) echo 'selected'; ?>>Massagem</option>
                                    <option value="striptease" <?php if(in_array('striptease', $especialidades)) echo 'selected'; ?>>Striptease</option>
                                </select>
                                <div class="form-text">Segure Ctrl (Windows) ou Command (Mac) para selecionar mais de um.</div>
                            </div>
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
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informações e Mídia -->
        <?php if ($id): ?>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle"></i> Informações Gerais
                </div>
                <div class="card-body">
                    <div><strong>ID:</strong> <?php echo htmlspecialchars($acompanhante['id'] ?? ''); ?></div>
                    <div><strong>Cidade:</strong> <?php echo htmlspecialchars($acompanhante['cidade_nome'] ?? ''); ?><?php if (!empty($acompanhante['estado_uf'])): ?> - <?php echo htmlspecialchars($acompanhante['estado_uf']); ?><?php endif; ?></div>
                    <div><strong>Cadastro:</strong> <?php echo !empty($acompanhante['created_at']) ? date('d/m/Y H:i', strtotime($acompanhante['created_at'])) : '-'; ?></div>
                    <div><strong>Última atualização:</strong> <?php echo !empty($acompanhante['updated_at']) ? date('d/m/Y H:i', strtotime($acompanhante['updated_at'])) : 'Nunca'; ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fotos -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-images"></i> Fotos (<?php echo count($fotos); ?>)
                </div>
                <div class="card-body">
                    <?php if (empty($fotos)): ?>
                        <p class="text-muted">Nenhuma foto cadastrada.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($fotos as $foto): ?>
                                <div class="col-6 mb-2">
                                    <img src="<?php echo $foto['url']; ?>" class="img-fluid rounded" alt="Foto">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Vídeos -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-video"></i> Vídeos (<?php echo count($videos); ?>)
                </div>
                <div class="card-body">
                    <?php if (empty($videos)): ?>
                        <p class="text-muted">Nenhum vídeo cadastrado.</p>
                    <?php else: ?>
                        <?php foreach ($videos as $video): ?>
                            <div class="mb-2">
                                <video controls class="w-100">
                                    <source src="<?php echo $video['url']; ?>" type="video/mp4">
                                    Seu navegador não suporta vídeos.
                                </video>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Documentos -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt"></i> Documentos (<?php echo count($documentos); ?>)
                </div>
                <div class="card-body">
                    <?php if (empty($documentos)): ?>
                        <p class="text-muted">Nenhum documento cadastrado.</p>
                    <?php else: ?>
                        <?php foreach ($documentos as $doc): ?>
                            <div class="mb-2">
                                <a href="<?php echo $doc['url']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file"></i> <?php echo ucfirst($doc['tipo']); ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- FOTO DE PERFIL -->
    <div class="col-12 mt-5 mb-4 text-center">
        <h5>Foto de Perfil</h5>
        <?php
        $foto_perfil = $db->fetch("SELECT url FROM fotos WHERE acompanhante_id = ? AND tipo = 'perfil' AND principal = 1 ORDER BY created_at DESC LIMIT 1", [$id]);
        $foto_perfil_url = $foto_perfil['url'] ?? 'default-avatar.svg';
        if ($foto_perfil_url !== 'default-avatar.svg') {
            $miniatura_path = SITE_URL . '/uploads/perfil/' . htmlspecialchars($foto_perfil_url);
        } else {
            $miniatura_path = SITE_URL . '/assets/img/default-avatar.svg';
        }
        ?>
        <img id="fotoPerfilMiniatura"
             src="<?php echo $miniatura_path; ?>"
             alt="Foto de Perfil"
             style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:2px solid #ccc;">
        <form id="formUploadPerfil" enctype="multipart/form-data" method="post" style="display:inline-block; margin-top:10px;">
            <input type="file" id="inputFotoPerfil" name="foto" accept="image/*" style="max-width:200px; display:inline-block;">
            <button type="submit" class="btn btn-sm btn-primary">Enviar Nova Foto</button>
        </form>
        <div id="fotoPerfilMsg" class="mt-2"></div>
    </div>
    <!-- DOCUMENTOS -->
    <?php
    $documentos = $db->fetchAll("SELECT * FROM documentos_acompanhante WHERE acompanhante_id = ? AND tipo = 'rg' ORDER BY created_at DESC", [$id]);
    $doc_frente = null;
    $doc_verso = null;
    foreach ($documentos as $doc) {
        if (strpos($doc['url'], 'rg_frente_') === 0) $doc_frente = $doc;
        if (strpos($doc['url'], 'rg_verso_') === 0) $doc_verso = $doc;
    }
    ?>
    <div class="col-12 mt-5 mb-4 text-center">
        <h5>Documento de Identidade (RG ou CNH)</h5>
        <div style="display:flex; justify-content:center; gap:30px; flex-wrap:wrap;">
            <div>
                <label>Frente:</label><br>
                <input type="file" name="documento_frente" accept="image/*" style="max-width:200px; display:inline-block;">
                <?php if ($doc_frente): ?>
                    <div class="mt-2 d-inline-block" style="display:inline-block;">
                        <img src="<?php echo SITE_URL; ?>/uploads/documentos/<?php echo htmlspecialchars($doc_frente['url']); ?>" style="width:100px; height:70px; object-fit:cover; border:1px solid #ccc; border-radius:6px;">
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <label>Verso:</label><br>
                <input type="file" name="documento_verso" accept="image/*" style="max-width:200px; display:inline-block;">
                <?php if ($doc_verso): ?>
                    <div class="mt-2 d-inline-block" style="display:inline-block;">
                        <img src="<?php echo SITE_URL; ?>/uploads/documentos/<?php echo htmlspecialchars($doc_verso['url']); ?>" style="width:100px; height:70px; object-fit:cover; border:1px solid #ccc; border-radius:6px;">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- VÍDEO DE VERIFICAÇÃO -->
    <?php
    $video = $db->fetch("SELECT * FROM videos WHERE acompanhante_id = ? ORDER BY created_at DESC LIMIT 1", [$id]);
    ?>
    <div class="col-12 mt-5 mb-4 text-center">
        <h5>Vídeo de Verificação</h5>
        <input type="file" name="video_verificacao" accept="video/*" style="max-width:300px; display:inline-block;">
        <?php if ($video): ?>
            <div class="mt-2">
                <video controls style="width:200px;max-height:150px;">
                    <source src="<?php echo SITE_URL; ?>/uploads/videos/<?php echo htmlspecialchars($video['url']); ?>" type="video/mp4">
                    Seu navegador não suporta vídeos.
                </video>
            </div>
        <?php endif; ?>
    </div>
    <!-- GALERIA DE FOTOS -->
    <?php
    $fotos_galeria = $db->fetchAll("SELECT * FROM fotos WHERE acompanhante_id = ? AND tipo = 'galeria' ORDER BY ordem, created_at", [$id]);
    ?>
    <div class="col-12 mt-5 mb-4 text-center">
        <h5>Galeria de Fotos</h5>
        <input type="file" name="fotos_galeria[]" accept="image/*" multiple style="max-width:300px; display:inline-block;">
        <?php if (empty($fotos_galeria)): ?>
            <p class="text-muted">Nenhuma foto na galeria.</p>
        <?php else: ?>
            <div class="row justify-content-center">
                <?php foreach ($fotos_galeria as $foto): ?>
                    <div class="col-2 mb-2">
                        <img src="<?php echo SITE_URL; ?>/uploads/galeria/<?php echo htmlspecialchars($foto['url']); ?>" class="img-fluid rounded" alt="Foto">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- HORÁRIOS DE ATENDIMENTO -->
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
                            <input type="time" class="form-control" name="horario_inicio[<?php echo $num; ?>]" value="<?php echo $inicio; ?>" <?php if(!$atende) echo 'style="display:none"'; ?> data-dia="<?php echo $num; ?>">
                        </td>
                        <td>
                            <input type="time" class="form-control" name="horario_fim[<?php echo $num; ?>]" value="<?php echo $fim; ?>" <?php if(!$atende) echo 'style="display:none"'; ?> data-dia="<?php echo $num; ?>">
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
    <!-- VALORES POR TEMPO DE SERVIÇO -->
    <div class="col-12 mt-4">
        <h5 class="mb-3"><i class="fas fa-dollar-sign"></i> Valores</h5>
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
</div>

<?php include '../includes/admin-footer.php'; ?> 