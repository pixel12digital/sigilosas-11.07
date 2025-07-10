<?php
$page_title = "Contato";
$page_description = "Entre em contato conosco. Nossa equipe está pronta para ajudar você.";

// Processar formulário de contato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $assunto = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    
    // Validações
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = "Nome é obrigatório";
    }
    
    if (empty($email)) {
        $errors[] = "Email é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }
    
    if (empty($assunto)) {
        $errors[] = "Assunto é obrigatório";
    }
    
    if (empty($mensagem)) {
        $errors[] = "Mensagem é obrigatória";
    } elseif (strlen($mensagem) < 10) {
        $errors[] = "Mensagem deve ter pelo menos 10 caracteres";
    }
    
    if (empty($errors)) {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = getDB();
            $pdo = $db->getConnection();
            
            $data_envio = date('Y-m-d H:i:s');
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $stmt = $pdo->prepare("
                INSERT INTO contatos (nome, email, telefone, assunto, mensagem, data_envio, ip) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$nome, $email, $telefone, $assunto, $mensagem, $data_envio, $ip])) {
                $_SESSION['success'] = "Mensagem enviada com sucesso! Responderemos em breve.";
                
                // Limpar formulário
                $nome = $email = $assunto = $mensagem = $telefone = '';
            } else {
                $errors[] = "Erro ao enviar mensagem. Tente novamente.";
            }
        } catch (Exception $e) {
            $errors[] = "Erro interno. Tente novamente.";
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Informações de contato -->
        <div class="col-lg-4 mb-5">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-address-card"></i> Informações de Contato
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-map-marker-alt"></i> Endereço
                        </h6>
                        <p class="text-muted mb-0">
                            São Paulo, SP<br>
                            Brasil
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-envelope"></i> Email
                        </h6>
                        <p class="text-muted mb-0">
                            <a href="mailto:contato@sigilosasvip.com" class="text-decoration-none">
                                contato@sigilosasvip.com
                            </a>
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-phone"></i> Telefone
                        </h6>
                        <p class="text-muted mb-0">
                            <a href="tel:+5511999999999" class="text-decoration-none">
                                (11) 99999-9999
                            </a>
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-clock"></i> Horário de Atendimento
                        </h6>
                        <p class="text-muted mb-0">
                            Segunda a Sexta: 9h às 18h<br>
                            Sábado: 9h às 14h<br>
                            Domingo: Fechado
                        </p>
                    </div>
                    
                    <div>
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-share-alt"></i> Redes Sociais
                        </h6>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-telegram"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulário de contato -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-success text-white text-center py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope"></i> Envie sua Mensagem
                    </h5>
                    <p class="mb-0 small">Estamos aqui para ajudar você</p>
                </div>
                
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <!-- Nome -->
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">
                                    <i class="fas fa-user"></i> Nome Completo *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nome" 
                                       name="nome" 
                                       value="<?php echo htmlspecialchars($nome ?? ''); ?>"
                                       required>
                            </div>
                            
                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email *
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                       required>
                            </div>
                            
                            <!-- Telefone -->
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">
                                    <i class="fas fa-phone"></i> Telefone (opcional)
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="telefone" 
                                       name="telefone" 
                                       value="<?php echo htmlspecialchars($telefone ?? ''); ?>"
                                       placeholder="(11) 99999-9999">
                            </div>
                            
                            <!-- Assunto -->
                            <div class="col-md-6 mb-3">
                                <label for="assunto" class="form-label">
                                    <i class="fas fa-tag"></i> Assunto *
                                </label>
                                <select class="form-select" id="assunto" name="assunto" required>
                                    <option value="">Selecione um assunto</option>
                                    <option value="Dúvida Geral" <?php echo ($assunto ?? '') === 'Dúvida Geral' ? 'selected' : ''; ?>>Dúvida Geral</option>
                                    <option value="Suporte Técnico" <?php echo ($assunto ?? '') === 'Suporte Técnico' ? 'selected' : ''; ?>>Suporte Técnico</option>
                                    <option value="Denúncia" <?php echo ($assunto ?? '') === 'Denúncia' ? 'selected' : ''; ?>>Denúncia</option>
                                    <option value="Sugestão" <?php echo ($assunto ?? '') === 'Sugestão' ? 'selected' : ''; ?>>Sugestão</option>
                                    <option value="Parceria" <?php echo ($assunto ?? '') === 'Parceria' ? 'selected' : ''; ?>>Parceria</option>
                                    <option value="Outro" <?php echo ($assunto ?? '') === 'Outro' ? 'selected' : ''; ?>>Outro</option>
                                </select>
                            </div>
                            
                            <!-- Mensagem -->
                            <div class="col-12 mb-3">
                                <label for="mensagem" class="form-label">
                                    <i class="fas fa-comment"></i> Mensagem *
                                </label>
                                <textarea class="form-control" 
                                          id="mensagem" 
                                          name="mensagem" 
                                          rows="6" 
                                          placeholder="Digite sua mensagem aqui..."
                                          required><?php echo htmlspecialchars($mensagem ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Botão de envio -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane"></i> Enviar Mensagem
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- FAQ Rápido -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle"></i> Perguntas Frequentes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Como funciona o sistema de verificação?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Todas as acompanhantes passam por um processo rigoroso de verificação, incluindo verificação de identidade e entrevista pessoal.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Meus dados estão seguros?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Sim! Utilizamos criptografia de ponta a ponta e nunca compartilhamos suas informações pessoais com terceiros.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Como denunciar um perfil?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Use o formulário acima selecionando "Denúncia" como assunto, ou clique no botão "Denunciar" no perfil da acompanhante.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara para telefone
document.getElementById('telefone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        e.target.value = value;
    }
});
</script> 