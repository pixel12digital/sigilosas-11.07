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
        
        // Garantir que cidade_id nunca seja perdida
        if (empty($cidade_id)) {
            $cidade_atual = $db->fetch("SELECT cidade_id FROM acompanhantes WHERE id = ?", [$id]);
            if ($cidade_atual && $cidade_atual['cidade_id']) {
                $cidade_id = $cidade_atual['cidade_id'];
            }
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
            'estado_id' => !empty($_POST['estado_id']) ? (int)$_POST['estado_id'] : (!empty($_POST['estado_id_hidden']) ? (int)$_POST['estado_id_hidden'] : ($acompanhante['estado_id'] ?? null)),
            'cidade_id' => $cidade_id
        ];
        $db->update('acompanhantes', $data, 'id = ?', [$id]);
        
        // --- SALVAR HORÁRIOS DE ATENDIMENTO ---
        if (isset($_POST['horario_inicio'], $_POST['horario_fim'], $_POST['atende'])) {
            $dias_semana = [1,2,3,4,5,6,7];
            $db->query("DELETE FROM horarios_atendimento WHERE acompanhante_id = ?", [$id]);
            
            foreach ($dias_semana as $dia) {
                if (!isset($_POST['atende'][$dia])) {
                    continue;
                }
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
        
        // Garantir que cidade_id nunca seja perdida
        if (empty($cidade_id)) {
            $cidade_atual = $db->fetch("SELECT cidade_id FROM acompanhantes WHERE id = ?", [$id]);
            if ($cidade_atual && $cidade_atual['cidade_id']) {
                $cidade_id = $cidade_atual['cidade_id'];
            }
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
            'estado_id' => !empty($_POST['estado_id']) ? (int)$_POST['estado_id'] : null,
            'cidade_id' => $cidade_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $newId = $db->insert('acompanhantes', $data);
        header('Location: acompanhante-editar.php?id=' . $newId . '&success=Acompanhante cadastrada com sucesso');
        exit;
    } elseif ($id && $action === 'aprovar') {
        // Aprovar acompanhante e todas as mídias associadas (lógica unificada)
        $db->update('acompanhantes', ['status' => 'aprovado'], 'id = ?', [$id]);
        
        // Aprovar todas as fotos
        $db->query('UPDATE fotos SET aprovada = 1 WHERE acompanhante_id = ?', [$id]);
        
        // Aprovar todos os vídeos públicos
        $db->query('UPDATE videos_publicos SET status = "aprovado" WHERE acompanhante_id = ?', [$id]);
        
        // Verificar todos os documentos
        $db->query('UPDATE documentos_acompanhante SET verificado = 1 WHERE acompanhante_id = ?', [$id]);
        
        header('Location: acompanhante-editar.php?id=' . $id . '&success=Acompanhante e todas as mídias aprovadas com sucesso');
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
    } elseif ($id && $action === 'excluir') {
        // Exclusão completa da acompanhante e todos os dados relacionados
        try {
            // 1. Excluir fotos físicas e registros
            $fotos = $db->fetchAll('SELECT * FROM fotos WHERE acompanhante_id = ?', [$id]);
            foreach ($fotos as $foto) {
                if ($foto['url'] && file_exists($foto['url'])) {
                    unlink($foto['url']);
                }
            }
            $db->delete('fotos', 'acompanhante_id = ?', [$id]);

            // 2. Excluir vídeos de verificação físicos e registros
            $videos_verificacao = $db->fetchAll('SELECT * FROM videos_verificacao WHERE acompanhante_id = ?', [$id]);
            foreach ($videos_verificacao as $video) {
                if ($video['url'] && file_exists($video['url'])) {
                    unlink($video['url']);
                }
            }
            $db->delete('videos_verificacao', 'acompanhante_id = ?', [$id]);

            // 3. Excluir vídeos públicos físicos e registros
            $videos_publicos = $db->fetchAll('SELECT * FROM videos_publicos WHERE acompanhante_id = ?', [$id]);
            foreach ($videos_publicos as $video) {
                if ($video['url'] && file_exists($video['url'])) {
                    unlink($video['url']);
                }
            }
            $db->delete('videos_publicos', 'acompanhante_id = ?', [$id]);

            // 4. Excluir documentos físicos e registros
            $documentos = $db->fetchAll('SELECT * FROM documentos_acompanhante WHERE acompanhante_id = ?', [$id]);
            foreach ($documentos as $doc) {
                if ($doc['url'] && file_exists($doc['url'])) {
                    unlink($doc['url']);
                }
            }
            $db->delete('documentos_acompanhante', 'acompanhante_id = ?', [$id]);

            // 5. Excluir dados relacionados
            $db->delete('valores_atendimento', 'acompanhante_id = ?', [$id]);
            $db->delete('horarios_atendimento', 'acompanhante_id = ?', [$id]);
            $db->delete('avaliacoes', 'acompanhante_id = ?', [$id]);
            $db->delete('denuncias', 'acompanhante_id = ?', [$id]);

            // 6. Finalmente, excluir acompanhante
            $result = $db->delete('acompanhantes', 'id = ?', [$id]);
            
            if ($result) {
                header('Location: acompanhantes.php?success=Acompanhante e todos os dados relacionados excluídos com sucesso');
                exit;
            } else {
                $error = 'Erro ao excluir acompanhante do banco de dados.';
            }
        } catch (Exception $e) {
            $error = 'Erro ao excluir acompanhante: ' . $e->getMessage();
        }
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
                        
                        <!-- Dados Básicos -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nome" class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($acompanhante['nome'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="apelido" class="form-label">Apelido</label>
                                <input type="text" class="form-control" id="apelido" name="apelido" value="<?php echo htmlspecialchars($acompanhante['apelido'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($acompanhante['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="telefone" class="form-label">Telefone *</label>
                                <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($acompanhante['telefone'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="idade" class="form-label">Idade</label>
                                <input type="number" class="form-control" id="idade" name="idade" value="<?php echo htmlspecialchars($acompanhante['idade'] ?? ''); ?>" min="18" max="99">
                            </div>
                            <div class="col-md-4">
                                <label for="altura" class="form-label">Altura (m)</label>
                                <input type="number" class="form-control" id="altura" name="altura" value="<?php echo htmlspecialchars($acompanhante['altura'] ?? ''); ?>" step="0.01" min="1.00" max="2.50">
                            </div>
                            <div class="col-md-4">
                                <label for="peso" class="form-label">Peso (kg)</label>
                                <input type="number" class="form-control" id="peso" name="peso" value="<?php echo htmlspecialchars($acompanhante['peso'] ?? ''); ?>" step="0.1" min="30" max="200">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="medidas" class="form-label">Medidas</label>
                                <input type="text" class="form-control" id="medidas" name="medidas" value="<?php echo htmlspecialchars($acompanhante['medidas'] ?? ''); ?>" placeholder="Ex: 90-60-90">
                            </div>
                            <div class="col-md-6">
                                <label for="endereco" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo htmlspecialchars($acompanhante['endereco'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="valor_padrao" class="form-label">Valor Padrão (R$)</label>
                                <input type="number" class="form-control" id="valor_padrao" name="valor_padrao" value="<?php echo htmlspecialchars($acompanhante['valor_padrao'] ?? ''); ?>" step="0.01" min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="valor_promocional" class="form-label">Valor Promocional (R$)</label>
                                <input type="number" class="form-control" id="valor_promocional" name="valor_promocional" value="<?php echo htmlspecialchars($acompanhante['valor_promocional'] ?? ''); ?>" step="0.01" min="0">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo htmlspecialchars($acompanhante['descricao'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="pendente" <?php echo ($acompanhante['status'] ?? '') === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="aprovado" <?php echo ($acompanhante['status'] ?? '') === 'aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                                    <option value="rejeitado" <?php echo ($acompanhante['status'] ?? '') === 'rejeitado' ? 'selected' : ''; ?>>Rejeitado</option>
                                    <option value="bloqueado" <?php echo ($acompanhante['status'] ?? '') === 'bloqueado' ? 'selected' : ''; ?>>Bloqueado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="verificado" name="verificado" value="1" <?php echo ($acompanhante['verificado'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="verificado">
                                        Verificado
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="destaque" name="destaque" value="1" <?php echo ($acompanhante['destaque'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="destaque">
                                        Destaque
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- HORÁRIOS DE ATENDIMENTO - DENTRO DO FORMULÁRIO -->
                        <div class="row mb-3">
                            <div class="col-12">
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
</div>

<script>
// JavaScript para controlar a exibição dos campos de horário
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.atende-dia');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const dia = this.getAttribute('data-dia');
            const inicioInput = document.querySelector(`input[name="horario_inicio[${dia}]"]`);
            const fimInput = document.querySelector(`input[name="horario_fim[${dia}]"]`);
            
            if (this.checked) {
                inicioInput.style.display = 'block';
                fimInput.style.display = 'block';
            } else {
                inicioInput.style.display = 'none';
                fimInput.style.display = 'none';
            }
        });
    });
});
</script>

<?php include '../includes/admin-footer.php'; ?> 