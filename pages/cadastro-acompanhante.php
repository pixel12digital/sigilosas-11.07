<?php
/**
 * Cadastro Público de Acompanhante
 * Arquivo: pages/cadastro-acompanhante.php
 */

// Iniciar sessão ANTES de qualquer saída
if (session_status() === PHP_SESSION_NONE) {
    session_name('sigilosas_acompanhante_session');
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Processar formulário ANTES de qualquer saída HTML
$success = '';
$error = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'nome' => trim($_POST['nome'] ?? ''),
        'apelido' => trim($_POST['apelido'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telefone' => trim($_POST['telefone'] ?? ''),
        'idade' => (int)($_POST['idade'] ?? 0),
        'cidade_id' => (int)($_POST['cidade_id'] ?? 0),
        'senha' => $_POST['senha'] ?? '',
        'confirmar_senha' => $_POST['confirmar_senha'] ?? ''
    ];

    // Padronizar telefone para formato internacional
    if (!empty($formData['telefone'])) {
        $telefone_limpo = preg_replace('/\D+/', '', $formData['telefone']);
        // Aceitar apenas se tiver 10 ou 11 dígitos (fixo ou celular)
        if (strlen($telefone_limpo) === 10 || strlen($telefone_limpo) === 11) {
            $telefone_padrao = '+55' . $telefone_limpo;
        } elseif (strpos($telefone_limpo, '55') === 0 && (strlen($telefone_limpo) === 12 || strlen($telefone_limpo) === 13)) {
            $telefone_padrao = '+' . $telefone_limpo;
        } else {
            $telefone_padrao = '';
        }
    } else {
        $telefone_padrao = '';
    }

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

    // Email
    if (empty($formData['email'])) {
        $errors[] = 'Email é obrigatório';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    } else {
        // Verificar se email já existe
        $existing = $db->fetch("SELECT id FROM acompanhantes WHERE email = ?", [$formData['email']]);
        if ($existing) {
            $errors[] = 'Este email já está cadastrado';
        }
    }

    // Telefone
    if (empty($telefone_padrao)) {
        $errors[] = 'Telefone inválido. Use DDD e número válido.';
    }

    // Idade
    if ($formData['idade'] < 18) {
        $errors[] = 'Você deve ter pelo menos 18 anos';
    }

    // Cidade
    if ($formData['cidade_id'] <= 0) {
        $errors[] = 'Selecione uma cidade';
    }

    // Senha
    if (empty($formData['senha'])) {
        $errors[] = 'Senha é obrigatória';
    } elseif (strlen($formData['senha']) < 6) {
        $errors[] = 'Senha deve ter pelo menos 6 caracteres';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $formData['senha'])) {
        $errors[] = 'Senha deve conter pelo menos uma letra maiúscula, uma minúscula e um número';
    }

    // Confirmar senha
    if ($formData['senha'] !== $formData['confirmar_senha']) {
        $errors[] = 'As senhas não coincidem';
    }

    // Se não há erros, salvar
    if (empty($errors)) {
        try {
            $data = [
                'nome' => $formData['nome'],
                'apelido' => $formData['apelido'],
                'email' => $formData['email'],
                'telefone' => $telefone_padrao,
                'idade' => $formData['idade'],
                'cidade_id' => $formData['cidade_id'],
                'senha' => password_hash($formData['senha'], PASSWORD_DEFAULT),
                'status' => 'pendente',
                'verificado' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $id = $db->insert('acompanhantes', $data);

            if ($id) {
                // Criar sessão para a acompanhante (sessão já iniciada)
                $_SESSION['acompanhante_id'] = $id;
                $_SESSION['acompanhante_nome'] = $formData['nome'];
                $_SESSION['acompanhante_apelido'] = $formData['apelido'];
                $_SESSION['acompanhante_email'] = $formData['email'];
                $_SESSION['acompanhante_status'] = 'pendente';
                $_SESSION['acompanhante_aprovada'] = 0; // Pendente = não aprovada
                $_SESSION['acompanhante_verificado'] = 0;

                // Redirecionar para painel (PHP)
                header('Location: ../acompanhante/?welcome=1');
                exit;
                // Fallback visual caso header não funcione
                echo '<script>setTimeout(function(){ window.location.href = "/Sigilosas-MySQL/acompanhante/?welcome=1"; }, 1500);</script>';
                exit;
            } else {
                $error = 'Erro ao cadastrar. Tente novamente.';
            }
        } catch (Exception $e) {
            $error = 'Erro interno. Tente novamente.';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// Buscar cidades para o select
$cidades = $db->fetchAll("
    SELECT c.*, e.uf 
    FROM cidades c 
    LEFT JOIN estados e ON c.estado_id = e.id 
    ORDER BY c.nome
");

// Definir variáveis da página ANTES de incluir o header
$pageTitle = 'Cadastro de Acompanhante - Sigilosas';
$pageDescription = 'Cadastre-se como acompanhante e comece a receber propostas.';

// AGORA incluir o header (depois de todo o processamento)
include '../includes/header.php';
?>

<!-- Header da Página -->
<section class="page-header py-5 bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-5 fw-bold mb-3">Cadastro de Acompanhante</h1>
                <p class="lead">Cadastre-se e comece a receber propostas de clientes</p>
            </div>
        </div>
    </div>
</section>

<!-- Formulário de Cadastro -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user-plus"></i> Dados Pessoais
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" id="cadastroForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nome" class="form-label">Nome Completo *</label>
                                        <input type="text" class="form-control" id="nome" name="nome" 
                                               value="<?php echo htmlspecialchars($formData['nome'] ?? ''); ?>" 
                                               required>
                                        <div class="form-text">Seu nome completo (privado)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="apelido" class="form-label">Apelido *</label>
                                        <input type="text" class="form-control" id="apelido" name="apelido" 
                                               value="<?php echo htmlspecialchars($formData['apelido'] ?? ''); ?>" 
                                               maxlength="50" required>
                                        <div class="form-text">Nome que aparecerá publicamente nos filtros</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                                               required>
                                        <div class="form-text">Será usado para login e contato</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefone" class="form-label">Telefone *</label>
                                        <input type="tel" class="form-control" id="telefone" name="telefone" 
                                               value="<?php echo htmlspecialchars($formData['telefone'] ?? ''); ?>" 
                                               placeholder="+55 11 99999-9999" required>
                                        <div class="form-text">WhatsApp preferencialmente</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="idade" class="form-label">Idade *</label>
                                        <input type="number" class="form-control" id="idade" name="idade" 
                                               value="<?php echo $formData['idade'] ?? ''; ?>" 
                                               min="18" max="99" required>
                                        <div class="form-text">Você deve ter pelo menos 18 anos</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="estado_id" class="form-label">Estado *</label>
                                        <select class="form-select" id="estado_id" name="estado_id" required>
                                            <option value="">Selecione um estado</option>
                                            <?php
                                            $estados = $db->fetchAll("SELECT id, nome, uf FROM estados WHERE ativo = 1 ORDER BY nome");
                                            foreach ($estados as $estado): ?>
                                                <option value="<?php echo $estado['id']; ?>" <?php if (($formData['estado_id'] ?? 0) == $estado['id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($estado['nome'] . ' (' . $estado['uf'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Selecione o estado de atendimento</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cidade_id" class="form-label">Cidade *</label>
                                        <select class="form-select" id="cidade_id" name="cidade_id" required>
                                            <option value="">Selecione o estado primeiro</option>
                                        </select>
                                        <div class="form-text">Sua cidade de atendimento</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="senha" class="form-label">Senha *</label>
                                        <input type="password" class="form-control" id="senha" name="senha" 
                                               required>
                                        <div class="form-text">Mínimo 6 caracteres, com letra maiúscula, minúscula e número</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirmar_senha" class="form-label">Confirmar Senha *</label>
                                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                                               required>
                                        <div class="form-text">Digite a mesma senha novamente</div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Importante:</strong> Após o cadastro, seu perfil ficará com status "pendente" 
                                até ser aprovado pela nossa equipe. Você poderá completar seus dados e adicionar fotos 
                                enquanto aguarda a aprovação.
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus"></i> Cadastrar
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-0">
                                Já tem cadastro? 
                                <a href="login-acompanhante.php" class="text-decoration-none">Faça login aqui</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BLOCO HORÁRIOS ATENDIMENTO POR DIA DA SEMANA (a ser implementado após criação da tabela) -->
<!-- Aqui será exibido o formulário de horários detalhados por dia da semana -->

<script>
// Validação de telefone brasileiro
document.getElementById('telefone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length >= 10) {
        value = value.replace(/(\d{2})(\d{4,5})(\d{4})/, '($1) $2-$3');
    }
    
    e.target.value = value;
});

// Validação de força da senha
document.getElementById('senha').addEventListener('input', function(e) {
    const senha = e.target.value;
    const confirmar = document.getElementById('confirmar_senha').value;
    
    // Verificar força da senha
    const temMaiuscula = /[A-Z]/.test(senha);
    const temMinuscula = /[a-z]/.test(senha);
    const temNumero = /\d/.test(senha);
    const temMinimo = senha.length >= 6;
    
    let forca = 0;
    if (temMaiuscula) forca++;
    if (temMinuscula) forca++;
    if (temNumero) forca++;
    if (temMinimo) forca++;
    
    // Atualizar indicador visual
    const formText = e.target.parentNode.querySelector('.form-text');
    if (forca >= 4) {
        formText.className = 'form-text text-success';
        formText.innerHTML = '<i class="fas fa-check"></i> Senha forte';
    } else if (forca >= 2) {
        formText.className = 'form-text text-warning';
        formText.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Senha média';
    } else {
        formText.className = 'form-text text-danger';
        formText.innerHTML = '<i class="fas fa-times"></i> Senha fraca';
    }
    
    // Verificar confirmação
    if (confirmar && senha !== confirmar) {
        document.getElementById('confirmar_senha').setCustomValidity('Senhas não coincidem');
    } else {
        document.getElementById('confirmar_senha').setCustomValidity('');
    }
});

// Validação de confirmação de senha
document.getElementById('confirmar_senha').addEventListener('input', function(e) {
    const senha = document.getElementById('senha').value;
    const confirmar = e.target.value;
    
    if (confirmar && senha !== confirmar) {
        e.target.setCustomValidity('Senhas não coincidem');
    } else {
        e.target.setCustomValidity('');
    }
});

// Carregar cidades dinamicamente ao selecionar estado
const estadoSelect = document.getElementById('estado_id');
const cidadeSelect = document.getElementById('cidade_id');

estadoSelect.addEventListener('change', function() {
    const estadoId = this.value;
    cidadeSelect.innerHTML = '<option>Carregando...</option>';
    if (!estadoId) {
        cidadeSelect.innerHTML = '<option value="">Selecione o estado primeiro</option>';
        return;
    }
    fetch('/Sigilosas-MySQL/api/cidades.php?estado_id=' + estadoId)
        .then(response => response.json())
        .then(data => {
            cidadeSelect.innerHTML = '<option value="">Selecione a cidade</option>';
            data.forEach(function(cidade) {
                cidadeSelect.innerHTML += '<option value="' + cidade.id + '">' + cidade.nome + '</option>';
            });
        });
});
</script>

<?php include '../includes/footer.php'; ?> 