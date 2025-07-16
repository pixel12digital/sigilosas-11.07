<?php
// Configurações para upload
ini_set('max_execution_time', 300); // 5 minutos
ini_set('memory_limit', '256M');
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '100M');

require_once __DIR__ . '/../config/config.php';
session_name('sigilosas_acompanhante_session');
session_start();

if (!isset($_SESSION['acompanhante_id'])) {
    header('Location: ' . SITE_URL . '/pages/login-acompanhante.php');
    exit;
}

$page_title = 'Editar Perfil';
$page_description = 'Edite suas informações pessoais e profissionais';

// Certificar que $db está definido
if (!isset($db)) { 
    require_once __DIR__ . '/../config/database.php'; 
    $db = getDB(); 
}

// PROCESSAR EXCLUSÃO DE VÍDEO (ANTES DE QUALQUER HTML)
if (isset($_POST['excluir_video_id'])) {
    $vid = (int)$_POST['excluir_video_id'];
    
    // Verificar se o vídeo existe e pertence à acompanhante
    $video = $db->fetch("SELECT * FROM videos_publicos WHERE id = ? AND acompanhante_id = ?", [$vid, $_SESSION['acompanhante_id']]);
    if ($video) {
        // Excluir o arquivo físico (cada vídeo deve ter nome único)
        $file = __DIR__ . '/../uploads/videos_publicos/' . $video['url'];
        if (file_exists($file)) {
            unlink($file);
        }
        
        // Excluir o registro do banco
        $db->query("DELETE FROM videos_publicos WHERE id = ?", [$vid]);
        
        // Redirecionar para evitar repost
        header('Location: ' . $_SERVER['REQUEST_URI'] . '?video_deleted=1');
        exit;
    } else {
        // Redirecionar mesmo assim para evitar repost
        header('Location: ' . $_SERVER['REQUEST_URI'] . '?error=video_not_found');
        exit;
    }
}

// Preparar dados da foto de perfil para usar depois do header
$foto_perfil = $db->fetch("SELECT url FROM fotos WHERE acompanhante_id = ? AND tipo = 'perfil' AND principal = 1 ORDER BY created_at DESC LIMIT 1", [$_SESSION['acompanhante_id']]);
$foto_perfil_url = $foto_perfil['url'] ?? 'default-avatar.svg';
if ($foto_perfil_url !== 'default-avatar.svg') {
    $miniatura_path = SITE_URL . '/uploads/perfil/' . htmlspecialchars($foto_perfil_url);
} else {
    $miniatura_path = SITE_URL . '/assets/img/default-avatar.svg';
}



// Carregar dados da acompanhante ANTES do processamento do formulário
$acompanhante = $db->fetch("
    SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON c.estado_id = e.id
    WHERE a.id = ?
", [$_SESSION['acompanhante_id']]);

// Processar formulário
$success = '';
$error = '';

// Verificar se há mensagem de sucesso na URL
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = 'Perfil atualizado com sucesso!';
}

// Verificar se há mensagem de vídeo excluído
if (isset($_GET['video_deleted']) && $_GET['video_deleted'] == '1') {
    $success = 'Vídeo excluído com sucesso!';
}

// Processar formulário se foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Coletar dados do formulário
    $formData = [
        'nome' => trim($_POST['nome'] ?? ''),
        'apelido' => trim($_POST['apelido'] ?? ''),
        'telefone' => trim($_POST['whatsapp'] ?? ''),
        'idade' => (int)($_POST['idade'] ?? 0),
        'genero' => trim($_POST['genero'] ?? ''),
        'estado_id' => (int)($_POST['estado_id'] ?? 0),
        'cidade_id' => (int)($_POST['cidade_id'] ?? 0),
        'senha' => trim($_POST['senha'] ?? ''),
        'preferencia_sexual' => trim($_POST['preferencia_sexual'] ?? ''),
        'idiomas' => trim($_POST['idiomas'] ?? ''),
        'bairro' => trim($_POST['bairro'] ?? ''),
        'endereco' => trim($_POST['endereco'] ?? ''),
        'cep' => trim($_POST['cep'] ?? ''),
        'sobre_mim' => trim($_POST['sobre_mim'] ?? ''),
        'altura' => !empty($_POST['altura']) ? (float)$_POST['altura'] : null,
        'peso' => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,

        'etnia' => trim($_POST['etnia'] ?? ''),
        'cor_olhos' => trim($_POST['cor_olhos'] ?? ''),
        'cor_cabelo' => trim($_POST['cor_cabelo'] ?? ''),
        'estilo_cabelo' => trim($_POST['estilo_cabelo'] ?? ''),
        'tamanho_cabelo' => trim($_POST['tamanho_cabelo'] ?? ''),
        'silicone' => (int)($_POST['silicone'] ?? 0),
        'tatuagens' => trim($_POST['tatuagens'] ?? ''),
        'piercings' => trim($_POST['piercings'] ?? ''),
        'local_atendimento' => json_encode($_POST['local_atendimento'] ?? []),
        'especialidades' => json_encode($_POST['especialidades'] ?? [])
    ];
    

}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && (!isset($_POST['action']) || $_POST['action'] !== 'upload_video_publico')
) {

    $formData = [
        'nome' => trim($_POST['nome'] ?? ''),
        'apelido' => trim($_POST['apelido'] ?? ''),
        'telefone' => trim($_POST['whatsapp'] ?? ''),
        'idade' => (int)($_POST['idade'] ?? 0),
        'altura' => !empty($_POST['altura']) ? (float)$_POST['altura'] : null,
        'peso' => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,
        'medidas' => trim($_POST['medidas'] ?? ''),
        'endereco' => trim($_POST['endereco'] ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
        'sobre_mim' => trim($_POST['sobre_mim'] ?? ''),
        'instagram' => trim($_POST['instagram'] ?? ''),
        'twitter' => trim($_POST['twitter'] ?? ''),
        'tiktok' => trim($_POST['tiktok'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'genero' => trim($_POST['genero'] ?? ''),
        'cidade_id' => trim($_POST['cidade_id'] ?? ''),
        'estado_id' => !empty($_POST['estado_id']) ? (int)$_POST['estado_id'] : ($acompanhante['estado_id'] ?? null),
        'senha' => $_POST['senha'] ?? '',
        'preferencia_sexual' => trim($_POST['preferencia_sexual'] ?? ''),
        'bairro' => trim($_POST['bairro'] ?? ''),
        'cep' => trim($_POST['cep'] ?? ''),

        'etnia' => trim($_POST['etnia'] ?? ''),
        'cor_olhos' => trim($_POST['cor_olhos'] ?? ''),
        'cor_cabelo' => trim($_POST['cor_cabelo'] ?? ''),
        'estilo_cabelo' => trim($_POST['estilo_cabelo'] ?? ''),
        'tamanho_cabelo' => trim($_POST['tamanho_cabelo'] ?? ''),
        'silicone' => $_POST['silicone'] ?? 0,
        'tatuagens' => $_POST['tatuagens'] ?? 0,
        'piercings' => $_POST['piercings'] ?? 0,
        'local_atendimento' => isset($_POST['local_atendimento']) ? json_encode($_POST['local_atendimento']) : json_encode([]),
        'valor_padrao' => !empty($_POST['valor_padrao']) ? (float)$_POST['valor_padrao'] : null,
        'valor_promocional' => !empty($_POST['valor_promocional']) ? (float)$_POST['valor_promocional'] : null,
        'idiomas' => trim($_POST['idiomas'] ?? ''),
        'especialidades' => isset($_POST['especialidades']) ? json_encode($_POST['especialidades']) : json_encode([]),
        'site' => trim($_POST['site'] ?? '')
    ];

    // Validações
    $errors = [];

    // Nome
    if (empty($formData['nome'])) {
        $errors[] = 'Nome é obrigatório';
    } elseif (strlen($formData['nome']) < 2) {
        $errors[] = 'Nome deve ter pelo menos 2 caracteres';
    }

    // Apelido
    if (empty($formData['apelido'])) {
        $errors[] = 'Apelido é obrigatório';
    } elseif (strlen($formData['apelido']) < 2) {
        $errors[] = 'Apelido deve ter pelo menos 2 caracteres';
    } elseif (strlen($formData['apelido']) > 50) {
        $errors[] = 'Apelido deve ter no máximo 50 caracteres';
    }

    // WhatsApp/Telefone (agora obrigatório)
    if (empty($formData['telefone'])) {
        $errors[] = 'WhatsApp é obrigatório';
    } else {
        // Validar formato do WhatsApp - remover todos os caracteres não numéricos
        $whats = preg_replace('/\D+/', '', $formData['telefone']);
        
        // Se começar com 55, remover para validar apenas DDD+número
        if (strlen($whats) >= 12 && substr($whats, 0, 2) === '55') {
            $whats = substr($whats, 2);
        }
        
        // Validar se tem 10 ou 11 dígitos (DDD + número)
        if (!preg_match('/^\d{10,11}$/', $whats)) {
            $errors[] = 'WhatsApp deve conter apenas DDD e número, ex: 41999999999';
        } else {
            $formData['telefone'] = '+55' . $whats;
        }
    }

    // Idade
    if ($formData['idade'] < 18) {
        $errors[] = 'Você deve ter pelo menos 18 anos';
    }

    // Cidade - Garantir que sempre seja salva
    if (empty($formData['cidade_id']) || !is_numeric($formData['cidade_id']) || $formData['cidade_id'] <= 0) {
        // Tentar usar o valor do campo hidden primeiro
        if (!empty($_POST['cidade_id_fallback']) && is_numeric($_POST['cidade_id_fallback'])) {
            $formData['cidade_id'] = (int)$_POST['cidade_id_fallback'];
        } else {
            // Se não foi enviada cidade, manter a cidade atual
            $cidade_atual = $db->fetch("SELECT cidade_id FROM acompanhantes WHERE id = ?", [$_SESSION['acompanhante_id']]);
            if ($cidade_atual && $cidade_atual['cidade_id']) {
                $formData['cidade_id'] = $cidade_atual['cidade_id'];
            } else {
                $errors[] = 'Selecione uma cidade válida.';
            }
        }
    }
    



    
    // Se não há erros, salvar
    if (empty($errors)) {
        // Verificar se a sessão ainda é válida antes de salvar
        if (!isset($_SESSION['acompanhante_id'])) {
            $error = 'Sessão expirada durante o upload. Faça login novamente.';
            header('Location: ' . SITE_URL . '/pages/login-acompanhante.php');
            exit;
        }
        
        try {
            $formData['updated_at'] = date('Y-m-d H:i:s');
            
            if (!empty($formData['senha'])) {
                $formData['senha'] = password_hash($formData['senha'], PASSWORD_DEFAULT);
            } else {
                unset($formData['senha']);
            }
            
            // Se a acompanhante já está aprovada, só coloca em moderação se houver upload de mídia
            $colocarEmModeracao = false;
            $acompanhanteAtual = $db->fetch("SELECT status FROM acompanhantes WHERE id = ?", [$_SESSION['acompanhante_id']]);
            $jaAprovada = ($acompanhanteAtual && $acompanhanteAtual['status'] === 'aprovado');

            // Detectar upload de fotos da galeria
            $houveUploadGaleria = isset($_FILES['fotos_galeria']) && !empty($_FILES['fotos_galeria']['name'][0]);
            // Detectar upload de foto de perfil
            $houveUploadPerfil = isset($_FILES['foto']) && !empty($_FILES['foto']['name']);
            // Detectar upload de vídeo de verificação
            $houveUploadVideo = isset($_FILES['video_verificacao']) && !empty($_FILES['video_verificacao']['name']);

            if ($jaAprovada && ($houveUploadGaleria || $houveUploadPerfil || $houveUploadVideo)) {
                $formData['status'] = 'pendente';
            }
            // Se não está aprovada, mantém lógica anterior (pode manter status pendente)
            // Se quiser garantir que nunca "volte" para pendente por texto, só mídia, basta não setar status aqui
            
            // Garantir que cidade_id nunca seja perdida
            if (empty($formData['cidade_id'])) {
                $cidade_atual = $db->fetch("SELECT cidade_id FROM acompanhantes WHERE id = ?", [$_SESSION['acompanhante_id']]);
                if ($cidade_atual && $cidade_atual['cidade_id']) {
                    $formData['cidade_id'] = $cidade_atual['cidade_id'];
                }
            }
            
            // Garantir que estado_id nunca seja perdido
            if (empty($formData['estado_id'])) {
                $estado_atual = $db->fetch("SELECT estado_id FROM acompanhantes WHERE id = ?", [$_SESSION['acompanhante_id']]);
                if ($estado_atual && $estado_atual['estado_id']) {
                    $formData['estado_id'] = $estado_atual['estado_id'];
                }
            }
            

            $result = $db->update('acompanhantes', $formData, 'id = ?', [$_SESSION['acompanhante_id']]);
            
            // Atualizar dados da sessão
            
            // Atualizar dados da sessão
            $_SESSION['acompanhante_nome'] = $formData['nome'];
            $_SESSION['acompanhante_apelido'] = $formData['apelido'];
            
            // Salvar horários de atendimento
            // SALVAR HORÁRIOS DE ATENDIMENTO POR DIA DA SEMANA
            if (isset($_POST['horario_inicio'], $_POST['horario_fim'])) {


                
                $dias_semana = [1,2,3,4,5,6,7];
                
                // Deletar horários existentes
                $delete_result = $db->query("DELETE FROM horarios_atendimento WHERE acompanhante_id = ?", [$_SESSION['acompanhante_id']]);

                
                $horarios_salvos = 0;
                foreach ($dias_semana as $dia) {
                    // Só salvar se o dia estiver marcado como "atende"
                    if (isset($_POST['atende'][$dia])) {
                        $inicio = $_POST['horario_inicio'][$dia] ?? '08:00';
                        $fim = $_POST['horario_fim'][$dia] ?? '23:59';
                        

                        $insert_result = $db->insert('horarios_atendimento', [
                            'acompanhante_id' => $_SESSION['acompanhante_id'],
                            'dia_semana' => $dia,
                            'hora_inicio' => $inicio,
                            'hora_fim' => $fim
                        ]);
                        
                        if ($insert_result) {
                            $horarios_salvos++;
                        }
                    }
                }
            }
            
            // Após atualizar o perfil e recarregar os dados da acompanhante, salvar os valores de atendimento:
            if (isset($_POST['valores']) && is_array($_POST['valores'])) {
                $db->query("DELETE FROM valores_atendimento WHERE acompanhante_id = ?", [$_SESSION['acompanhante_id']]);
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
                foreach ($tempos as $key => $label) {
                    $disponivel = !empty($_POST['valores'][$key]['disponivel']) ? 1 : 0;
                    $valor = !empty($_POST['valores'][$key]['valor']) ? (float)$_POST['valores'][$key]['valor'] : null;
                    if ($disponivel && $valor !== null) {
                        $db->insert('valores_atendimento', [
                            'acompanhante_id' => $_SESSION['acompanhante_id'],
                            'tempo' => $key,
                            'valor' => $valor,
                            'disponivel' => 1
                        ]);
                    } elseif ($disponivel) {
                        $db->insert('valores_atendimento', [
                            'acompanhante_id' => $_SESSION['acompanhante_id'],
                            'tempo' => $key,
                            'valor' => null,
                            'disponivel' => 1
                        ]);
                    }
                }
            }
            
            // --- UPLOAD DE DOCUMENTOS (unificado, múltiplos arquivos) ---
            if (isset($_FILES['documento_identidade']) && !empty($_FILES['documento_identidade']['name'][0])) {
                // Não remover documentos antigos automaticamente! Apenas adicionar novos.
                $files = $_FILES['documento_identidade'];
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                        $filename = 'rg_' . uniqid('', true) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $dest = __DIR__ . '/../uploads/documentos/' . $filename;
                        if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                            if (!$db->insert('documentos_acompanhante', [
                                'acompanhante_id' => $_SESSION['acompanhante_id'],
                                'tipo' => 'rg',
                                'url' => $filename,
                                'storage_path' => $filename,
                                'tamanho' => $files['size'][$i],
                                'formato' => $ext,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ])) {
                                $error .= '<br>Falha ao inserir documento no banco: ' . htmlspecialchars($filename);
        
                            }
                        } else {
                            $error .= '<br>Falha ao salvar documento de identidade (' . htmlspecialchars($files['name'][$i]) . ').';
    
                        }
                    } else {
                        $error .= '<br>Falha ao enviar documento de identidade (' . htmlspecialchars($files['name'][$i]) . '): código de erro ' . $files['error'][$i];

                    }
                }
            }
            // --- FIM UPLOAD DOCUMENTOS ---

            // --- UPLOAD DE FOTOS DA GALERIA (múltiplos arquivos) ---
            if (isset($_FILES['fotos_galeria']) && !empty($_FILES['fotos_galeria']['name'][0])) {
                $files = $_FILES['fotos_galeria'];
                
                // Verificar se o diretório existe
                $galeria_dir = __DIR__ . '/../uploads/galeria/';
                if (!is_dir($galeria_dir)) {
                    if (!mkdir($galeria_dir, 0755, true)) {
                        $error .= '<br>Erro ao criar diretório de galeria.';
                    }
                }
                
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                        $filename = 'galeria_' . uniqid('', true) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $dest = __DIR__ . '/../uploads/galeria/' . $filename;
                        if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                            $db->insert('fotos', [
                                'acompanhante_id' => $_SESSION['acompanhante_id'],
                                'tipo' => 'galeria',
                                'url' => $filename,
                                'ordem' => 0,
                                'principal' => 0,
                                'aprovada' => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                        } else {
                            $error .= '<br>Falha ao salvar foto da galeria (' . htmlspecialchars($files['name'][$i]) . ').';
                        }
                    } else {
                        $error .= '<br>Falha ao enviar foto da galeria (' . htmlspecialchars($files['name'][$i]) . '): código de erro ' . $files['error'][$i];
                    }
                }
            }
            // --- FIM UPLOAD GALERIA ---
            
            // Se chegou até aqui, tudo foi salvo com sucesso
            $success = 'Perfil atualizado com sucesso!';
            
            // Verificar se a sessão ainda está válida antes de redirecionar
            if (!isset($_SESSION['acompanhante_id'])) {
                header('Location: ' . SITE_URL . '/pages/login-acompanhante.php?error=session_expired');
                exit;
            }
            
            // Redirecionar para o início da página com mensagem de sucesso
            header('Location: ' . SITE_URL . '/acompanhante/perfil.php?success=1#top');
            exit;
            
        } catch (Exception $e) {
            $error = 'Erro ao atualizar perfil. Tente novamente.';
        }
    } else {
        // Só definir erro se o formulário foi realmente enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = implode('<br>', $errors);
        }
    }
}

// Após o processamento do formulário, buscar as fotos da galeria novamente
$fotos_galeria = $db->fetchAll("SELECT * FROM fotos WHERE acompanhante_id = ? AND tipo = 'galeria' ORDER BY ordem, created_at", [$_SESSION['acompanhante_id']]);

// Buscar documentos já enviados (deve ser feito após o processamento do formulário)
$documentos = $db->fetchAll("SELECT * FROM documentos_acompanhante WHERE acompanhante_id = ? AND tipo = 'rg' ORDER BY created_at DESC", [$_SESSION['acompanhante_id']]);
$doc_frente = null;
$doc_verso = null;
foreach ($documentos as $doc) {
    if (strpos($doc['url'], 'rg_frente_') === 0) $doc_frente = $doc;
    if (strpos($doc['url'], 'rg_verso_') === 0) $doc_verso = $doc;
}

// Buscar vídeos de verificação já enviados
$videos_verificacao = $db->fetchAll("SELECT * FROM videos_verificacao WHERE acompanhante_id = ? ORDER BY created_at DESC", [$_SESSION['acompanhante_id']]);

// Verificar e criar diretório de vídeos de verificação se necessário
$video_dir = __DIR__ . '/../uploads/verificacao/';
if (!is_dir($video_dir)) {
    mkdir($video_dir, 0755, true);
}





// Buscar cidades para o select
$cidades = $db->fetchAll("
    SELECT c.*, e.uf 
    FROM cidades c 
    LEFT JOIN estados e ON c.estado_id = e.id 
    ORDER BY c.nome
");

$estado_id = $acompanhante['estado_id'] ?? '';
$cidade_id = $acompanhante['cidade_id'] ?? '';

// Antes de exibir o formulário, preparar o array de locais selecionados
$locais = [];
if (!empty($acompanhante['local_atendimento'])) {
    if (is_array($acompanhante['local_atendimento'])) {
        $locais = $acompanhante['local_atendimento'];
    } else {
        // Tenta decodificar JSON ou array serializado
        $json = json_decode($acompanhante['local_atendimento'], true);
        if (is_array($json)) {
            $locais = $json;
        } else {
            $locais = [$acompanhante['local_atendimento']];
        }
    }
}

// Carregar valores de atendimento do banco
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
$rows = $db->fetchAll("SELECT * FROM valores_atendimento WHERE acompanhante_id = ?", [$acompanhante['id']]);
foreach ($rows as $row) {
    $valores_atendimento[$row['tempo']] = $row;
}

// Antes de exibir o formulário, preparar o array de idiomas selecionados
$idiomas_disponiveis = ['portugues' => 'Português', 'ingles' => 'Inglês', 'espanhol' => 'Espanhol', 'frances' => 'Francês'];
$idiomas = [];
if (!empty($acompanhante['idiomas'])) {
    if (is_array($acompanhante['idiomas'])) {
        $idiomas = $acompanhante['idiomas'];
    } else {
        $json = json_decode($acompanhante['idiomas'], true);
        if (is_array($json)) {
            $idiomas = $json;
        } else {
            $idiomas = [$acompanhante['idiomas']];
        }
    }
}

// Antes de exibir o formulário, preparar o array de especialidades selecionadas
$especialidades_disponiveis = ['convencional' => 'Convencional', 'fetiche' => 'Fetiche', 'massagem' => 'Massagem', 'striptease' => 'Striptease'];
$especialidades = [];
if (!empty($acompanhante['especialidades'])) {
    if (is_array($acompanhante['especialidades'])) {
        $especialidades = $acompanhante['especialidades'];
    } else {
        $json = json_decode($acompanhante['especialidades'], true);
        if (is_array($json)) {
            $especialidades = $json;
        } else {
            $especialidades = [$acompanhante['especialidades']];
        }
    }
}

// Agora incluir o header após todo o processamento do formulário
include __DIR__ . '/../includes/header.php';
?>

<!-- MENU RESPONSIVO DA ACOMPANHANTE -->
<!-- (Removido bloco duplicado) -->
<!-- FIM MENU RESPONSIVO -->

<!-- ACESSOS RÁPIDOS DA ACOMPANHANTE -->
<div class="container py-2">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2" style="background:#3D263F;border-radius:12px;padding:12px 18px;">
    <div class="d-flex align-items-center gap-2">
      <img src="<?php echo $miniatura_path; ?>" alt="Avatar" style="width:44px;height:44px;object-fit:cover;border-radius:50%;border:2px solid #F3EAC2;">
      <div class="d-flex flex-column">
        <span style="color:#F3EAC2;font-weight:bold;font-size:1.1em;">
          <?php echo htmlspecialchars($_SESSION['acompanhante_apelido'] ?? $_SESSION['acompanhante_nome'] ?? 'Minha Conta'); ?>
        </span>
        <span class="badge ms-1" style="background:<?php echo ($_SESSION['acompanhante_aprovada']??0)?'#28a745':'#ffc107'; ?>;color:#3D263F;font-weight:600;">
          <?php echo ($_SESSION['acompanhante_aprovada']??0)?'Aprovada':'Pendente'; ?>
        </span>
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="<?php echo SITE_URL; ?>/pages/acompanhante.php?id=<?php echo $_SESSION['acompanhante_id']; ?>" target="_blank" class="btn btn-outline-primary" style="background:#F3EAC2;color:#3D263F;border-color:#F3EAC2;min-width:140px;"><i class="fas fa-eye"></i> Ver Perfil Público</a>
      <a href="<?php echo SITE_URL; ?>/" class="btn btn-outline-primary text-danger" style="background:#F3EAC2;color:#3D263F;border-color:#F3EAC2;min-width:100px;"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
  </div>
</div>
<!-- FIM ACESSOS RÁPIDOS -->

<main class="main-content">
    <div id="top"></div>
    <div class="container py-4">
        <div class="card-header" style="background: #3D263F; color: #F3EAC2;">
            <h4 class="mb-0">Editar Perfil</h4>
        </div>
        


                <form method="post" enctype="multipart/form-data" id="editarPerfilForm">

                    
                    <!-- ESTILO FORÇADO PARA LAYOUT MULTICOLUNA -->
                    <style>
                    /* Layout multicoluna forçado */
                    .form-row {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 15px;
                        margin-bottom: 20px;
                    }
                    
                    .form-field {
                        flex: 0 0 calc(25% - 12px);
                        min-width: 250px;
                    }
                    
                    .form-field.wide {
                        flex: 0 0 calc(33.333% - 10px);
                    }
                    
                    .form-field.full {
                        flex: 0 0 100%;
                    }
                    
                    .form-control, .form-select {
                        width: 100%;
                        padding: 0.75rem 1rem;
                        font-size: 0.95rem;
                        border-radius: 8px;
                        border: 2px solid #e9ecef;
                        box-sizing: border-box;
                    }
                    
                    .form-label {
                        font-weight: 600;
                        color: #3D263F;
                        margin-bottom: 0.5rem;
                        font-size: 0.9rem;
                        display: block;
                    }
                    
                    .medidas-fisicas {
                        text-align: center;
                        font-weight: 600;
                        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                        border: 2px solid #dee2e6;
                    }
                    
                    .section-title {
                        color: #3D263F;
                        font-weight: 700;
                        margin: 2rem 0 1rem 0;
                        padding-bottom: 0.5rem;
                        border-bottom: 2px solid #F3EAC2;
                        position: relative;
                    }
                    
                    .section-title::after {
                        content: '';
                        position: absolute;
                        bottom: -2px;
                        left: 0;
                        width: 50px;
                        height: 2px;
                        background: #3D263F;
                    }
                    
                    @media (max-width: 768px) {
                        .form-field {
                            flex: 0 0 100%;
                        }
                    }
                    </style>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Bloco de upload de foto de perfil -->
            <div class="col-12 mt-3 mb-4 text-center">
                <h5 style="color: #3D263F;">Foto de Perfil</h5>
                <img id="fotoPerfilMiniatura"
                     src="<?php echo $miniatura_path; ?>"
                     alt="Foto de Perfil"
                     style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:2px solid #3D263F;">
                <div style="display:inline-block; margin-top:10px;">
                    <input type="file" id="inputFotoPerfil" name="foto" accept="image/*" style="max-width:200px; display:inline-block;">
                    <button type="button" class="btn btn-sm btn-primary" onclick="enviarFotoPerfil()">Enviar Nova Foto</button>
                </div>
                <div id="fotoPerfilMsg" class="mt-2"></div>
            </div>
            <script>
            function enviarFotoPerfil() {
                var input = document.getElementById('inputFotoPerfil');
                if (!input.files.length) {
                    alert('Selecione uma foto primeiro.');
                    return;
                }
                
                var formData = new FormData();
                formData.append('foto', input.files[0]);
                
                fetch(SITE_URL + '/api/upload-foto-perfil.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    var msg = document.getElementById('fotoPerfilMsg');
                    if (data.success) {
                        document.getElementById('fotoPerfilMiniatura').src = SITE_URL + '/uploads/perfil/' + data.filename + '?' + Date.now();
                        msg.innerHTML = '<span class="text-success">' + data.message + '</span>';
                    } else {
                        msg.innerHTML = '<span class="text-danger">' + data.message + '</span>';
                    }
                })
                .catch(() => {
                    document.getElementById('fotoPerfilMsg').innerHTML = '<span class="text-danger">Erro ao enviar foto.</span>';
                });
            }
            </script>
            
            <script>
            // Função para enviar vídeo público
            function enviarVideoPublico() {
                const btn = document.getElementById('btnEnviarVideo');
                const videoFile = document.getElementById('video_publico').files[0];
                const titulo = document.getElementById('titulo_video').value;
                const descricao = document.getElementById('descricao_video').value;
                
                if (!videoFile) {
                    alert('Selecione um vídeo primeiro.');
                    return;
                }
                
                // Prevenir múltiplos cliques
                if (btn.disabled) {
                    return;
                }
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                
                const formData = new FormData();
                formData.append('video_publico', videoFile);
                formData.append('titulo_video', titulo);
                formData.append('descricao_video', descricao);
                
                fetch(SITE_URL + '/api/upload-video-publico.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    const msg = document.getElementById('msgVideoPublico');
                    if (data.success) {
                        msg.innerHTML = '<span class="text-success">' + data.message + '</span>';
                        // Limpar campos
                        document.getElementById('video_publico').value = '';
                        document.getElementById('titulo_video').value = '';
                        document.getElementById('descricao_video').value = '';
                        
                        // Adicionar vídeo à lista sem recarregar página
                        if (data.video) {
                            setTimeout(() => {
                                atualizarListaVideosPublicos(data.video);
                            }, 200);
                        }
                        
                        // Mostrar confirmação
                        setTimeout(() => {
                            alert('✅ Vídeo enviado com sucesso!\n\nO vídeo aparecerá na lista acima.');
                        }, 500);
                    } else {
                        msg.innerHTML = '<span class="text-danger">' + data.message + '</span>';
                    }
                })
                .catch(() => {
                    document.getElementById('msgVideoPublico').innerHTML = '<span class="text-danger">Erro ao enviar vídeo.</span>';
                })
                .finally(() => {
                    // Reabilitar o botão
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-upload"></i> Enviar';
                });
            }
            
            // Função para atualizar lista de vídeos públicos dinamicamente
            function atualizarListaVideosPublicos(video) {
                const SITE_URL = '<?php echo SITE_URL; ?>';
                const listaContainer = document.getElementById('listaVideosPublicos');
                
                // Verificar se o vídeo já existe na lista (evitar duplicação)
                const existingVideo = listaContainer.querySelector(`[data-video-id="${video.id}"]`);
                if (existingVideo) {
                    return; // Vídeo já existe, não adicionar novamente
                }
                
                // Se não há vídeos, remover mensagem "Nenhum vídeo enviado"
                const emptyMsg = listaContainer.querySelector('.text-muted');
                if (emptyMsg && emptyMsg.textContent.includes('Nenhum vídeo')) {
                    emptyMsg.remove();
                }
                
                // Criar HTML do novo vídeo
                const videoHTML = `
                    <div class="col-md-4 col-6" data-video-container="${video.id}">
                        <div class="card h-100 shadow-sm">
                            <video src="${SITE_URL}/uploads/videos_publicos/${video.filename}" controls 
                                   style="width:100%; max-width:140px; aspect-ratio:9/16; height:auto; max-height:250px; margin:auto; display:block; background:#000; object-fit:cover; border-radius:12px;"></video>
                            <div class="p-2">
                                <div class="fw-bold small mb-1">${video.titulo || ''}</div>
                                <div class="text-muted small mb-1">${video.descricao || ''}</div>
                                <span class="badge bg-secondary">Pendente</span>
                                <button type="button" class="btn btn-sm btn-danger ms-2 video-excluir-btn" data-video-id="${video.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                // Adicionar o novo vídeo no início da lista
                listaContainer.insertAdjacentHTML('afterbegin', videoHTML);
            }
            
            // Função para excluir vídeo
            function excluirVideo(videoId) {
                if (!confirm('Excluir este vídeo?')) {
                    return;
                }
                
                const formData = new FormData();
                formData.append('excluir_video_id', videoId);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(() => {
                    // Remover o vídeo da lista sem recarregar página
                    const videoContainer = document.querySelector(`[data-video-container="${videoId}"]`);
                    if (videoContainer) {
                        videoContainer.remove();
                        
                        // Se não há mais vídeos, mostrar mensagem
                        const listaContainer = document.getElementById('listaVideosPublicos');
                        if (!listaContainer.querySelector('[data-video-container]')) {
                            listaContainer.innerHTML = '<div class="col-12 text-center text-muted">Nenhum vídeo enviado ainda.</div>';
                        }
                    }
                })
                .catch(() => {
                    alert('Erro ao excluir vídeo.');
                });
            }
            </script>
            
            <div class="section-title">Dados Pessoais</div>
            
            <!-- Primeira linha: Nome, Apelido, E-mail, Senha -->
            <div class="form-row">
                <div class="form-field">
                    <label for="nome" class="form-label">Nome Completo *</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($acompanhante['nome'] ?? ''); ?>" required>
                </div>
                <div class="form-field">
                    <label for="apelido" class="form-label">Apelido *</label>
                    <input type="text" class="form-control" id="apelido" name="apelido" value="<?php echo htmlspecialchars($acompanhante['apelido'] ?? ''); ?>" required>
                </div>
                <div class="form-field">
                    <label for="email" class="form-label">E-mail *</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($acompanhante['email'] ?? ''); ?>" readonly>
                </div>
                <div class="form-field">
                    <label for="senha" class="form-label">Senha (deixe em branco para não alterar)</label>
                    <input type="password" class="form-control" id="senha" name="senha" autocomplete="new-password">
                </div>
            </div>
            
            <!-- Segunda linha: WhatsApp, Idade, Gênero -->
            <div class="form-row">
                <div class="form-field">
                    <label for="whatsapp" class="form-label">WhatsApp *</label>
                    <input type="tel" class="form-control" id="whatsapp" name="whatsapp"
                           pattern="^\d{10,11}$"
                           placeholder="DDD + número (ex: 41999999999)"
                           value="<?php echo htmlspecialchars($acompanhante['telefone'] ?? ''); ?>" required>
                    <div class="form-text">Digite apenas DDD e número, sem espaços ou traços. Ex: 41999999999</div>
                </div>
                <div class="form-field">
                    <label for="idade" class="form-label">Idade *</label>
                    <input type="number" class="form-control" id="idade" name="idade" min="18" max="99" value="<?php echo htmlspecialchars($acompanhante['idade'] ?? ''); ?>" required>
                </div>
                <div class="form-field">
                    <label for="genero" class="form-label">Gênero *</label>
                    <select class="form-select" id="genero" name="genero" required>
                        <option value="">Selecione</option>
                        <option value="feminino" <?php if(($acompanhante['genero'] ?? '')==='feminino') echo 'selected'; ?>>Feminino</option>
                        <option value="masculino" <?php if(($acompanhante['genero'] ?? '')==='masculino') echo 'selected'; ?>>Masculino</option>
                        <option value="trans" <?php if(($acompanhante['genero'] ?? '')==='trans') echo 'selected'; ?>>Trans</option>
                        <option value="outro" <?php if(($acompanhante['genero'] ?? '')==='outro') echo 'selected'; ?>>Outro</option>
                    </select>
                </div>
            </div>
            
            <!-- Terceira linha: Preferência Sexual, Estado, Cidade, Idiomas -->
            <div class="form-row">
                <div class="form-field">
                    <label for="preferencia_sexual" class="form-label">Preferência Sexual</label>
                    <select class="form-select" id="preferencia_sexual" name="preferencia_sexual">
                        <option value="">Selecione</option>
                        <option value="homens" <?php if(($acompanhante['preferencia_sexual'] ?? '')==='homens') echo 'selected'; ?>>Homens</option>
                        <option value="mulheres" <?php if(($acompanhante['preferencia_sexual'] ?? '')==='mulheres') echo 'selected'; ?>>Mulheres</option>
                        <option value="todos" <?php if(($acompanhante['preferencia_sexual'] ?? '')==='todos') echo 'selected'; ?>>Todos</option>
                    </select>
                </div>
                <div class="form-field">
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
                <div class="form-field">
                    <label for="cidade_id" class="form-label">Cidade *</label>
                    <select class="form-select" id="cidade_id" name="cidade_id" required>
                        <option value="">Selecione o estado primeiro</option>
                    </select>
                    <!-- Campo hidden para garantir que o valor da cidade seja sempre enviado -->
                    <input type="hidden" name="cidade_id_fallback" value="<?php echo htmlspecialchars($acompanhante['cidade_id'] ?? ''); ?>">
                    <div class="form-text">Sua cidade de atendimento</div>
                </div>
                <div class="form-field">
                    <label for="idiomas" class="form-label">Idiomas</label>
                    <input type="text" class="form-control" id="idiomas" name="idiomas" value="<?php echo htmlspecialchars($acompanhante['idiomas'] ?? ''); ?>" placeholder="Ex: Português, Inglês, Espanhol">
                    <div class="form-text">Digite os idiomas separados por vírgula.</div>
                </div>
            </div>
            
            <!-- Quarta linha: Bairro, Endereço, CEP -->
            <div class="form-row">
                <div class="form-field wide">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro" value="<?php echo htmlspecialchars($acompanhante['bairro'] ?? ''); ?>">
                </div>
                <div class="form-field wide">
                    <label for="endereco" class="form-label">Endereço (Rua)</label>
                    <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo htmlspecialchars($acompanhante['endereco'] ?? ''); ?>">
                </div>
                <div class="form-field wide">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" class="form-control" id="cep" name="cep" value="<?php echo htmlspecialchars($acompanhante['cep'] ?? ''); ?>">
                </div>
            </div>
            <!-- Endereço -->
            <!-- SEÇÃO SOBRE MIM -->
            <div class="col-12">
                <label for="sobre_mim" class="form-label" style="font-weight:bold;font-size:1.2em;">Sobre Mim</label>
                <textarea class="form-control" id="sobre_mim" name="sobre_mim" rows="4" maxlength="1000" placeholder="Conte um pouco sobre você, sua personalidade, experiências, diferenciais, etc."><?php echo htmlspecialchars($acompanhante['sobre_mim'] ?? ''); ?></textarea>
                <div class="form-text">Este texto será exibido no seu perfil público. Máximo de 1000 caracteres.</div>
            </div>
            <!-- Aparência -->
            <div class="section-title">Aparência</div>
            
            <!-- Primeira linha: Altura, Peso, Etnia, Cor dos Olhos -->
            <div class="form-row">
                <div class="form-field">
                    <label for="altura" class="form-label">Altura (cm)</label>
                    <input type="number" class="form-control medidas-fisicas" id="altura" name="altura" step="0.01" value="<?php echo htmlspecialchars($acompanhante['altura'] ?? ''); ?>">
                </div>
                <div class="form-field">
                    <label for="peso" class="form-label">Peso (kg)</label>
                    <input type="number" class="form-control medidas-fisicas" id="peso" name="peso" value="<?php echo htmlspecialchars($acompanhante['peso'] ?? ''); ?>">
                </div>
                <div class="form-field">
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
                <div class="form-field">
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
            </div>
            
            <!-- Terceira linha: Cor do Cabelo, Estilo do Cabelo, Tamanho do Cabelo, Silicone -->
            <div class="form-row">
                <div class="form-field">
                    <label for="cor_cabelo" class="form-label">Cor do Cabelo</label>
                    <input type="text" class="form-control" id="cor_cabelo" name="cor_cabelo" value="<?php echo htmlspecialchars($acompanhante['cor_cabelo'] ?? ''); ?>">
                </div>
                <div class="form-field">
                    <label for="estilo_cabelo" class="form-label">Estilo do Cabelo</label>
                    <select class="form-select" id="estilo_cabelo" name="estilo_cabelo">
                        <option value="">Selecione</option>
                        <option value="liso" <?php if(($acompanhante['estilo_cabelo'] ?? '')==='liso') echo 'selected'; ?>>Liso</option>
                        <option value="ondulado" <?php if(($acompanhante['estilo_cabelo'] ?? '')==='ondulado') echo 'selected'; ?>>Ondulado</option>
                        <option value="cacheado" <?php if(($acompanhante['estilo_cabelo'] ?? '')==='cacheado') echo 'selected'; ?>>Cacheado</option>
                        <option value="crespo" <?php if(($acompanhante['estilo_cabelo'] ?? '')==='crespo') echo 'selected'; ?>>Crespo</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="tamanho_cabelo" class="form-label">Tamanho do Cabelo</label>
                    <select class="form-select" id="tamanho_cabelo" name="tamanho_cabelo">
                        <option value="">Selecione</option>
                        <option value="curto" <?php if(($acompanhante['tamanho_cabelo'] ?? '')==='curto') echo 'selected'; ?>>Curto</option>
                        <option value="medio" <?php if(($acompanhante['tamanho_cabelo'] ?? '')==='medio') echo 'selected'; ?>>Médio</option>
                        <option value="longo" <?php if(($acompanhante['tamanho_cabelo'] ?? '')==='longo') echo 'selected'; ?>>Longo</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="silicone" class="form-label">Silicone</label>
                    <select class="form-select" id="silicone" name="silicone">
                        <option value="0" <?php if(($acompanhante['silicone'] ?? 0)==0) echo 'selected'; ?>>Não</option>
                        <option value="1" <?php if(($acompanhante['silicone'] ?? 0)==1) echo 'selected'; ?>>Sim</option>
                    </select>
                </div>
            </div>
            
            <!-- Quarta linha: Tatuagens, Piercings -->
            <div class="form-row">
                <div class="form-field">
                    <label for="tatuagens" class="form-label">Tatuagens</label>
                    <select class="form-select" id="tatuagens" name="tatuagens">
                        <option value="0" <?php if(($acompanhante['tatuagens'] ?? 0)==0) echo 'selected'; ?>>Não</option>
                        <option value="1" <?php if(($acompanhante['tatuagens'] ?? 0)==1) echo 'selected'; ?>>Sim</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="piercings" class="form-label">Piercings</label>
                    <select class="form-select" id="piercings" name="piercings">
                        <option value="0" <?php if(($acompanhante['piercings'] ?? 0)==0) echo 'selected'; ?>>Não</option>
                        <option value="1" <?php if(($acompanhante['piercings'] ?? 0)==1) echo 'selected'; ?>>Sim</option>
                    </select>
                </div>
            </div>
            <!-- Preferências e Serviços -->
            <div class="col-12"><h5 class="mt-4">Preferências e Serviços</div>
            <div class="col-md-6">
                <label for="local_atendimento" class="form-label">Local de Atendimento</label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="local_atendimento[]" value="domicilio" id="local_domicilio" <?php if(in_array('domicilio', $locais)) echo 'checked'; ?>>
                            <label class="form-check-label" for="local_domicilio">Domicílio</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="local_atendimento[]" value="motel" id="local_motel" <?php if(in_array('motel', $locais)) echo 'checked'; ?>>
                            <label class="form-check-label" for="local_motel">Motel</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="local_atendimento[]" value="hotel" id="local_hotel" <?php if(in_array('hotel', $locais)) echo 'checked'; ?>>
                            <label class="form-check-label" for="local_hotel">Hotel</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="local_atendimento[]" value="casa_propria" id="local_casa_propria" <?php if(in_array('casa_propria', $locais)) echo 'checked'; ?>>
                            <label class="form-check-label" for="local_casa_propria">Casa Própria</label>
                        </div>
                    </div>
                </div>
                <div class="form-text">Selecione um ou mais locais de atendimento.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Especialidades</label>
                <div class="row">
                    <div class="col-md-6">
                        <?php 
                        $especialidades_array = array_values($especialidades_disponiveis);
                        $half = ceil(count($especialidades_array) / 2);
                        for ($i = 0; $i < $half; $i++): 
                            $key = array_keys($especialidades_disponiveis)[$i];
                            $label = $especialidades_disponiveis[$key];
                        ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="especialidade_<?php echo $key; ?>" name="especialidades[]" value="<?php echo $key; ?>" <?php if(in_array($key, $especialidades)) echo 'checked'; ?>>
                                <label class="form-check-label" for="especialidade_<?php echo $key; ?>"><?php echo $label; ?></label>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="col-md-6">
                        <?php 
                        for ($i = $half; $i < count($especialidades_array); $i++): 
                            $key = array_keys($especialidades_disponiveis)[$i];
                            $label = $especialidades_disponiveis[$key];
                        ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="especialidade_<?php echo $key; ?>" name="especialidades[]" value="<?php echo $key; ?>" <?php if(in_array($key, $especialidades)) echo 'checked'; ?>>
                                <label class="form-check-label" for="especialidade_<?php echo $key; ?>"><?php echo $label; ?></label>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="form-text">Selecione uma ou mais especialidades.</div>
            </div>
            <!-- Seção de Valores -->
            <div class="col-12 mt-4">
                <h5 class="mb-3"><i class="fas fa-dollar-sign"></i> Valores</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="valor_padrao" class="form-label">Preço Padrão (R$)</label>
                        <input type="number" class="form-control" id="valor_padrao" name="valor_padrao" step="0.01" value="<?php echo htmlspecialchars($acompanhante['valor_padrao'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="valor_promocional" class="form-label">Preço Promocional (R$)</label>
                        <input type="number" class="form-control" id="valor_promocional" name="valor_promocional" step="0.01" value="<?php echo htmlspecialchars($acompanhante['valor_promocional'] ?? ''); ?>">
                    </div>
                </div>
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
                            $horarios = $db->fetchAll("SELECT * FROM horarios_atendimento WHERE acompanhante_id = ?", [$acompanhante['id']]);
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
            <!-- BLOCO VALORES POR TEMPO DE SERVIÇO -->
            <!-- SEÇÃO DE DOCUMENTOS DE IDENTIDADE (antes da galeria) -->
            <div class="col-12 mt-5 text-center" id="secao-documentos">
                <h5 class="mb-3"><i class="fas fa-id-card"></i> Documento de Identidade (RG ou CNH)</h5>
                <div class="mb-2" style="max-width:400px;margin:auto;">
                    <input type="file" name="documento_identidade[]" accept="image/*,application/pdf" multiple style="max-width:300px; display:inline-block;" onchange="previewDocumentosSelecionados(this)">
                </div>
                <div id="previewDocumentosSelecionados" class="d-flex justify-content-center gap-3 flex-wrap mt-2"></div>
                <div class="form-text mb-2">
                    Envie a <b>frente e o verso</b> do seu documento de identidade (RG ou CNH) em um ou dois arquivos.<br>
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
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- FIM SEÇÃO DE DOCUMENTOS -->
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
                        </div>
                    <?php else: ?>
                        <div class="text-muted">Nenhum vídeo enviado.</div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- FIM SEÇÃO DE VÍDEO DE VERIFICAÇÃO -->
            <!-- BLOCO GALERIA DE FOTOS -->
            <div class="col-12 mt-4 text-center">
                <h5><i class="fas fa-images"></i> Galeria de Fotos</h5>
                <div class="row justify-content-center" id="galeriaMiniaturas">
                    <?php if (empty($fotos_galeria)): ?>
                        <div class="text-muted">Nenhuma foto na galeria.</div>
                    <?php else: ?>
                        <?php foreach ($fotos_galeria as $foto): ?>
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3 position-relative galeria-item" data-foto-id="<?php echo $foto['id']; ?>">
                                <button type="button" class="btn btn-sm btn-danger position-absolute galeria-excluir-btn" style="z-index:1000; border-radius:50%; width:28px; height:28px; padding:0; font-weight:bold; background-color: #dc3545 !important; color: white !important; border: none !important; top: 8px; right: 8px; display: block !important; opacity: 1 !important;" title="Excluir foto" data-foto-id="<?php echo $foto['id']; ?>">×</button>
                                <img src="<?php echo SITE_URL; ?>/uploads/galeria/<?php echo htmlspecialchars($foto['url']); ?>"
                                     alt="Foto Galeria"
                                     style="width:100%;max-width:120px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #ccc;">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="mt-3">
                    <input type="file" id="inputGaleriaFotos" name="fotos_galeria[]" accept="image/*" multiple style="max-width:200px; display:inline-block;" onchange="previewGaleriaFotos(this)">
                    <button type="button" class="btn btn-primary ms-2" id="btnUploadGaleria">Enviar Fotos</button>
                </div>
                <div id="previewGaleria" class="d-flex justify-content-center gap-3 flex-wrap mt-2 mb-2"></div>
                <div id="galeriaMsg" class="mt-2"></div>
            </div>

            <!-- SEÇÃO DE VÍDEOS PÚBLICOS -->
            <div class="card shadow-sm mb-4" style="background:#fff;color:#3D263F;box-shadow:0 2px 12px rgba(61,38,63,0.08);">
              <div class="card-body">
                <div class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-video"></i> Vídeos Públicos</div>
                <div class="mb-2 text-muted">Adicione vídeos curtos para seu perfil público. Apenas vídeos aprovados serão exibidos no site. (Máx. 50MB, formatos: mp4, webm, mov)</div>
                <div class="row g-2 align-items-end">
                  <div class="col-md-4">
                    <label for="video_publico" class="form-label">Selecione o vídeo</label>
                    <input type="file" class="form-control" id="video_publico" name="video_publico" accept="video/mp4,video/webm,video/quicktime">
                  </div>
                  <div class="col-md-3">
                    <label for="titulo_video" class="form-label">Título (opcional)</label>
                    <input type="text" class="form-control" id="titulo_video" name="titulo_video" maxlength="100">
                  </div>
                  <div class="col-md-3">
                    <label for="descricao_video" class="form-label">Descrição (opcional)</label>
                    <input type="text" class="form-control" id="descricao_video" name="descricao_video" maxlength="255">
                  </div>
                  <div class="col-md-2">
                    <button type="button" id="btnEnviarVideo" class="btn btn-primary w-100" onclick="enviarVideoPublico()"><i class="fas fa-upload"></i> Enviar</button>
                  </div>
                </div>
                <div id="msgVideoPublico" class="mt-2"></div>
                <?php
                // Listar vídeos já enviados
                $videos_publicos = $db->fetchAll("SELECT * FROM videos_publicos WHERE acompanhante_id = ? ORDER BY created_at DESC", [$_SESSION['acompanhante_id']]);
                
                if ($videos_publicos): ?>
                <div id="listaVideosPublicos" class="row mt-4 g-3">
                  <?php foreach ($videos_publicos as $v): ?>
                    <div class="col-md-4 col-6" data-video-container="<?php echo $v['id']; ?>">
                      <div class="card h-100 shadow-sm">
                        <video src="<?php echo SITE_URL . '/uploads/videos_publicos/' . htmlspecialchars($v['url']); ?>" controls style="width:100%; max-width:140px; aspect-ratio:9/16; height:auto; max-height:250px; margin:auto; display:block; background:#000; object-fit:cover; border-radius:12px;"></video>
                        <div class="p-2">
                          <div class="fw-bold small mb-1"><?php echo htmlspecialchars($v['titulo'] ?? ''); ?></div>
                          <div class="text-muted small mb-1"><?php echo htmlspecialchars($v['descricao'] ?? ''); ?></div>
                          <span class="badge bg-secondary"><?php echo ucfirst($v['status']); ?></span>
                          <button type="button" class="btn btn-sm btn-danger ms-2 video-excluir-btn" data-video-id="<?php echo $v['id']; ?>"><i class="fas fa-trash"></i></button>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div id="listaVideosPublicos" class="row mt-4 g-3">
                  <div class="col-12 text-center text-muted">Nenhum vídeo enviado ainda.</div>
                </div>
                <?php endif; ?>
              </div>
            </div>


            <!-- Botão Salvar Alterações e Sair sem salvar (MOVIDO) -->
            <div class="col-12 text-center mt-4 mb-5 d-flex flex-wrap justify-content-center gap-3">
                <button type="button" class="btn btn-save px-4 py-2" onclick="enviarFormulario()">
                    <i class="fas fa-save me-2"></i>Salvar Alterações
                </button>
                <a href="<?php echo SITE_URL; ?>/acompanhante/" class="btn btn-outline-primary px-4 py-2">Sair sem salvar</a>
            </div>
            <div style="height:40px;"></div>
        </form>
    </div>
</main>

<!-- BLOCO ÚNICO DE MANIPULAÇÃO DE ESTADO E CIDADE -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.getElementById('estado_id');
    const cidadeSelect = document.getElementById('cidade_id');
    // Garantir que cidadeId seja string (ou vazio), nunca undefined
    const cidadeId = String(<?php echo json_encode($acompanhante['cidade_id'] ?? ''); ?>);
    


    function carregarCidades(estadoId, cidadeIdSelecionada) {
        
        cidadeSelect.innerHTML = '<option>Carregando...</option>';
        fetch(SITE_URL + '/api/cidades.php?estado_id=' + encodeURIComponent(estadoId))
            .then(response => response.json())
            .then(cidades => {

                if (!Array.isArray(cidades) || cidades.length === 0) {
                    cidadeSelect.innerHTML = '<option value="">Nenhuma cidade encontrada</option>';
                    return;
                }
                cidadeSelect.innerHTML = '<option value="">Selecione a cidade</option>';
                cidades.forEach(function(cidade) {
                    let selected = String(cidadeIdSelecionada) === String(cidade.id) ? 'selected' : '';
                    cidadeSelect.innerHTML += '<option value="' + cidade.id + '" ' + selected + '>' + cidade.nome + '</option>';
                });

                

            })
            .catch((error) => {
                console.error('Erro ao carregar cidades:', error);
                cidadeSelect.innerHTML = '<option value="">Erro ao carregar cidades</option>';
            });
    }

    // Carregar cidades ao abrir a página, se já houver estado selecionado
    if (estadoSelect.value) {
        carregarCidades(estadoSelect.value, cidadeId);
    } else if (cidadeId) {
        // Se não há estado selecionado mas há cidade, buscar o estado da cidade
        fetch(SITE_URL + '/api/cidades.php?cidade_id=' + encodeURIComponent(cidadeId))
            .then(response => response.json())
            .then(data => {
                if (data.estado_id) {
                    estadoSelect.value = data.estado_id;
                    carregarCidades(data.estado_id, cidadeId);
                }
            })
            .catch(() => {
    
            });
    }

    estadoSelect.addEventListener('change', function() {
        if (this.value) {
            carregarCidades(this.value, '');
        } else {
            cidadeSelect.innerHTML = '<option value="">Selecione o estado primeiro</option>';
        }
    });
    

});
</script>

<!-- BLOCO HORÁRIOS ATENDIMENTO POR DIA DA SEMANA (a ser implementado após criação da tabela) -->
<!-- Aqui será exibido o formulário de horários detalhados por dia da semana -->

<script>
// Verificar se existem elementos antes de adicionar event listeners
const atendeDiaElements = document.querySelectorAll('.atende-dia');
if (atendeDiaElements.length > 0) {
    atendeDiaElements.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var dia = this.getAttribute('data-dia');
            var inputs = document.querySelectorAll('input[data-dia="' + dia + '"]');
            inputs.forEach(function(input) {
                if (input.type === 'time') {
                    input.style.display = checkbox.checked ? '' : 'none';
                }
            });
        });
    });
}
</script>

<script>
// Verificar se existem elementos antes de adicionar event listeners
const valorTempoElements = document.querySelectorAll('.valor-tempo-check');
if (valorTempoElements.length > 0) {
    valorTempoElements.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var input = this.closest('.form-check').parentNode.querySelector('input[type=number]');
            if (input) {
                input.disabled = !this.checked;
                if (!this.checked) input.value = '';
            }
        });
    });
}
</script>

<script>
// Funções de preview e exclusão
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

function previewDocumentosSelecionados(input) {
    var preview = document.getElementById('previewDocumentosSelecionados');
    preview.innerHTML = '';
    if (input.files && input.files.length) {
        for (let i = 0; i < input.files.length; i++) {
            let file = input.files[i];
            if (file.type.match('image.*')) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let img = document.createElement('img');
                    img.src = e.target.result;
                    img.style = 'width:100px; height:70px; object-fit:cover; border:1px solid #ccc; border-radius:6px; margin:4px;';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                let link = document.createElement('a');
                link.href = URL.createObjectURL(file);
                link.target = '_blank';
                link.textContent = 'Ver PDF (' + file.name + ')';
                link.style = 'display:inline-block;margin:8px;';
                preview.appendChild(link);
            }
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

function excluirDocumento(docId, btn) {
    if (!confirm('Tem certeza que deseja excluir este documento?')) return;
    btn.disabled = true;
    fetch(SITE_URL + '/api/delete-documento.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'documento_id=' + encodeURIComponent(docId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var item = btn.closest('.d-inline-block');
            if (item) item.remove();
        } else {
            alert(data.message || 'Erro ao excluir documento.');
            btn.disabled = false;
        }
    })
    .catch(() => {
        alert('Erro ao excluir documento.');
        btn.disabled = false;
    });
}

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

        btn.disabled = false;
    });
}

// Função para validar formulário antes do envio
function validarFormulario() {
    const form = document.getElementById('editarPerfilForm');
    
    if (!form) {
        return false;
    }
    
    // Verificar campos obrigatórios
    const nome = document.getElementById('nome');
    const apelido = document.getElementById('apelido');
    const whatsapp = document.getElementById('whatsapp');
    const idade = document.getElementById('idade');
    const genero = document.getElementById('genero');
    const cidadeSelect = document.getElementById('cidade_id');
    const estadoSelect = document.getElementById('estado_id');
    
    let hasEmptyRequired = false;
    let emptyFields = [];
    
    // Verificar cada campo obrigatório
    if (!nome || !nome.value.trim()) {
        emptyFields.push('nome');
        hasEmptyRequired = true;
    }
    
    if (!apelido || !apelido.value.trim()) {
        emptyFields.push('apelido');
        hasEmptyRequired = true;
    }
    
    if (!whatsapp || !whatsapp.value.trim()) {
        emptyFields.push('whatsapp');
        hasEmptyRequired = true;
    }
    
    if (!idade || !idade.value.trim() || parseInt(idade.value) < 18) {
        emptyFields.push('idade');
        hasEmptyRequired = true;
    }
    
    if (!genero || !genero.value.trim()) {
        emptyFields.push('genero');
        hasEmptyRequired = true;
    }
    
    if (!estadoSelect || !estadoSelect.value.trim()) {
        emptyFields.push('estado');
        hasEmptyRequired = true;
    }
    
    // Verificar cidade com tratamento especial para carregamento AJAX
    if (!cidadeSelect || !cidadeSelect.value.trim() || cidadeSelect.value === 'Carregando...') {
        // Se está carregando, aguardar um pouco e tentar novamente
        if (cidadeSelect && cidadeSelect.value === 'Carregando...') {
            setTimeout(() => {
                if (validarFormulario()) {
                    document.getElementById('editarPerfilForm').submit();
                }
            }, 1000);
            return false;
        }
        
        emptyFields.push('cidade');
        hasEmptyRequired = true;
    }
    
    if (hasEmptyRequired) {
        alert('Por favor, preencha todos os campos obrigatórios: ' + emptyFields.join(', '));
        return false;
    }
    
    return true;
}


// Função para enviar o formulário
function enviarFormulario() {
    const form = document.getElementById('editarPerfilForm');
    if (!form) {
        alert('Erro: Formulário não encontrado!');
        return;
    }
    
    if (validarFormulario()) {
        form.submit();
    } else {
        alert('Por favor, corrija os erros antes de salvar.');
    }
}

// Event delegation para botões de exclusão 
document.addEventListener('DOMContentLoaded', function() {
    // Event delegation para botões de exclusão 
    // Isso permite que botões adicionados dinamicamente funcionem
    document.addEventListener('click', function(e) {
        // Botões de exclusão da galeria
        if (e.target.classList.contains('galeria-excluir-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            const fotoId = e.target.getAttribute('data-foto-id');
            if (fotoId) {
                excluirFotoGaleria(fotoId, e.target);
            }
        }
        
        // Botões de exclusão de vídeos
        if (e.target.classList.contains('video-excluir-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            const videoId = e.target.getAttribute('data-video-id');
            if (videoId) {
                excluirVideo(videoId);
            }
        }
        
        // Event delegation para botões de exclusão de documentos
        if (e.target.classList.contains('doc-excluir-btn')) {
            const docId = e.target.getAttribute('onclick');
            if (docId) {
                // Extrair o ID do atributo onclick
                const match = docId.match(/excluirDocumento\((\d+)/);
                if (match) {
                    excluirDocumento(match[1], e.target);
                }
            }
        }
    });
});
</script>

<style>
/* Estilos para otimização do layout desktop */
@media (min-width: 768px) {
    .form-label {
        font-weight: 600;
        color: #3D263F;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #ddd;
        transition: border-color 0.2s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3D263F;
        box-shadow: 0 0 0 0.2rem rgba(61, 38, 63, 0.25);
    }
    
    /* Melhor espaçamento entre seções */
    .col-12 h5 {
        color: #3D263F;
        border-bottom: 2px solid #F3EAC2;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    /* Cards para agrupar campos relacionados */
    .field-group {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
    }
    
    /* Melhor visualização dos checkboxes */
    .form-check {
        margin-bottom: 0.5rem;
    }
    
    .form-check-input:checked {
        background-color: #3D263F;
        border-color: #3D263F;
    }
    
    /* Responsividade melhorada */
    .row.g-3 > [class*="col-"] {
        padding-right: 1rem;
        padding-left: 1rem;
    }
    
    /* Espaçamento otimizado para campos pequenos */
    .col-md-3 .form-control,
    .col-md-3 .form-select {
        font-size: 0.9rem;
    }
    
    /* Melhor visualização da tabela de horários */
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .table th {
        background-color: #3D263F;
        color: #F3EAC2;
        border-color: #3D263F;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Script para upload de vídeo público -->
<script src="<?php echo SITE_URL; ?>/assets/js/video-upload.js"></script>

<!-- Script para upload de vídeo de verificação -->
<script>
// Upload de vídeo de verificação
document.addEventListener('DOMContentLoaded', function() {
    
    
    const btnUploadVideo = document.getElementById('btnUploadVideo');
    const inputVideoVerificacao = document.getElementById('inputVideoVerificacao');
    const videoMsg = document.getElementById('videoVerificacaoMsg');
    
    
    
    if (btnUploadVideo && inputVideoVerificacao) {
        btnUploadVideo.addEventListener('click', function() {

            
            if (!inputVideoVerificacao.files.length) {
                alert('Por favor, selecione um vídeo primeiro.');
                return;
            }
            
            const file = inputVideoVerificacao.files[0];

            
            // Validação de tamanho (50MB)
            if (file.size > 50 * 1024 * 1024) {
                alert('O vídeo excede o tamanho máximo permitido (50MB).');
                return;
            }
            
                         // Validação de tipo
             const allowedTypes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/ogg'];
             const allowedExtensions = ['mp4', 'webm', 'mov', 'avi', 'ogg'];
             const fileExtension = file.name.split('.').pop().toLowerCase();
             
             
             
             if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
                 alert('Formato de vídeo não permitido. Use MP4, WebM, MOV, AVI ou OGG.');
                 return;
             }
            
            // Desabilitar botão e mostrar loading
            btnUploadVideo.disabled = true;
            btnUploadVideo.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            
            if (videoMsg) {
                videoMsg.innerHTML = '<div class="text-info">Enviando vídeo, aguarde...</div>';
            }
            
            // Criar FormData
            const formData = new FormData();
            formData.append('video_verificacao', file);
            

            
            // Enviar para API
            fetch('<?php echo SITE_URL; ?>/api/upload-video-verificacao.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if (data.success) {
                     if (videoMsg) {
                         videoMsg.innerHTML = '<div class="text-success">' + data.message + '</div>';
                     }
                     // Limpar o input de arquivo
                     inputVideoVerificacao.value = '';
                     
                     // Atualizar a exibição do vídeo dinamicamente
                     setTimeout(() => {
                         atualizarVideoVerificacao(data.filename, data.video_id);
                         if (videoMsg) {
                             videoMsg.innerHTML = '<div class="text-success"><i class="fas fa-check-circle"></i> Vídeo enviado com sucesso!</div>';
                         }
                     }, 1000);
                 } else {
                    if (videoMsg) {
                        videoMsg.innerHTML = '<div class="text-danger">' + data.message + '</div>';
                    }
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                if (videoMsg) {
                    videoMsg.innerHTML = '<div class="text-danger">Erro ao enviar vídeo. Tente novamente.</div>';
                }
            })
            .finally(() => {
                // Reabilitar botão
                btnUploadVideo.disabled = false;
                btnUploadVideo.innerHTML = 'Enviar Vídeo';
            });
        });
        
        // Preview do arquivo selecionado
        inputVideoVerificacao.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
    
                
                if (videoMsg) {
                    const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                    videoMsg.innerHTML = `<div class="text-info">Arquivo selecionado: ${file.name} (${sizeMB}MB)</div>`;
                }
            }
        });
    } else {

    }
});

// Upload de fotos da galeria
document.addEventListener('DOMContentLoaded', function() {
    const btnUploadGaleria = document.getElementById('btnUploadGaleria');
    const inputGaleriaFotos = document.getElementById('inputGaleriaFotos');
    const galeriaMsg = document.getElementById('galeriaMsg');
    
    if (btnUploadGaleria && inputGaleriaFotos) {
        btnUploadGaleria.addEventListener('click', function() {
    
            
            if (!inputGaleriaFotos.files.length) {
                alert('Por favor, selecione pelo menos uma foto.');
                return;
            }
            
            // Validar arquivos
            const files = inputGaleriaFotos.files;
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                // Validação de tamanho (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert(`Arquivo muito grande: ${file.name} (máx. 5MB)`);
                    return;
                }
                
                // Validação de tipo
                if (!allowedTypes.includes(file.type)) {
                    alert(`Formato não permitido: ${file.name}. Use JPG, PNG, GIF ou WebP.`);
                    return;
                }
            }
            
            // Desabilitar botão e mostrar loading
            btnUploadGaleria.disabled = true;
            btnUploadGaleria.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            
            if (galeriaMsg) {
                galeriaMsg.innerHTML = '<div class="text-info">Enviando fotos, aguarde...</div>';
            }
            
            // Criar FormData
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('fotos_galeria[]', files[i]);
            }
            

            
            // Enviar para API
            fetch('<?php echo SITE_URL; ?>/api/upload-fotos-galeria.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (galeriaMsg) {
                        galeriaMsg.innerHTML = '<div class="text-success">' + data.message + '</div>';
                    }
                    // Limpar input para evitar reenvio no formulário principal
                    inputGaleriaFotos.value = '';
                    inputGaleriaFotos.files = null; // Garantir que não há arquivos selecionados
                    // Limpar preview
                    const previewGaleria = document.getElementById('previewGaleria');
                    if (previewGaleria) {
                        previewGaleria.innerHTML = '';
                    }
                    
                    // Adicionar fotos à galeria
                    if (data.photos && data.photos.length > 0) {
                        atualizarGaleriaFotos(data.photos);
                        
                        // Confirmar sucesso
                        setTimeout(() => {
                            alert(`✅ ${data.photos.length} foto(s) adicionada(s)!\n\nClique nos botões X vermelhos para excluir.`);
                        }, 500);
                    }
                } else {
                    if (galeriaMsg) {
                        galeriaMsg.innerHTML = '<div class="text-danger">' + data.message + '</div>';
                    }
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                if (galeriaMsg) {
                    galeriaMsg.innerHTML = '<div class="text-danger">Erro ao enviar fotos. Tente novamente.</div>';
                }
            })
            .finally(() => {
                // Reabilitar botão
                btnUploadGaleria.disabled = false;
                btnUploadGaleria.innerHTML = 'Enviar Fotos';
            });
        });
    }
});

// Função para atualizar a galeria de fotos dinamicamente
function atualizarGaleriaFotos(photos) {
    const SITE_URL = '<?php echo SITE_URL; ?>';
    const galeriaContainer = document.getElementById('galeriaMiniaturas');
    
    if (galeriaContainer) {
        // Se não há fotos, remover mensagem "Nenhuma foto na galeria"
        const emptyMsg = galeriaContainer.querySelector('.text-muted');
        if (emptyMsg) {
            emptyMsg.remove();
        }
        
        // Adicionar cada nova foto
        photos.forEach(photo => {
            const fotoHTML = `
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3 position-relative galeria-item" data-foto-id="${photo.id}">
                    <button type="button" class="btn btn-sm btn-danger position-absolute galeria-excluir-btn" 
                            style="z-index:1000; border-radius:50%; width:28px; height:28px; padding:0; font-weight:bold; background-color: #dc3545 !important; color: white !important; border: none !important; top: 8px; right: 8px; display: block !important; opacity: 1 !important;" 
                            title="Excluir foto" data-foto-id="${photo.id}">×</button>
                    <img src="${SITE_URL}/uploads/galeria/${photo.filename}"
                         alt="Foto Galeria"
                         style="width:100%;max-width:120px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #ccc;">
                </div>
            `;
            
            galeriaContainer.insertAdjacentHTML('beforeend', fotoHTML);
        });
    }
}

// Função para atualizar a exibição do vídeo de verificação
function atualizarVideoVerificacao(filename, videoId) {
    
    
    // URL base do site
    const SITE_URL = '<?php echo SITE_URL; ?>';
    
    // Encontrar a seção onde o vídeo é exibido
    const videoSection = document.querySelector('#secao-video-verificacao .mt-3');
    
    if (videoSection && filename) {
        // Criar HTML do vídeo
        const videoHTML = `
            <h6>Vídeo enviado:</h6>
            <div class="d-inline-block position-relative" style="display:inline-block;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 video-excluir-btn" 
                        style="z-index:2; border-radius:50%; width:24px; height:24px; padding:0; font-weight:bold; line-height:18px;" 
                        title="Excluir vídeo" onclick="excluirVideoVerificacao(${videoId}, this)">×</button>
                <video width="180" height="320" controls style="border-radius:12px; border:1px solid #ccc; background:#000; display:block; margin:auto; object-fit:cover;">
                     <source src="${SITE_URL}/uploads/verificacao/${filename}" type="video/mp4">
                     Seu navegador não suporta vídeo.
                 </video>
            </div>
        `;
        
        // Atualizar o conteúdo
        videoSection.innerHTML = videoHTML;
        
    } else {
        
    }
}
</script>

