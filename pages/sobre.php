<?php
require_once __DIR__ . '/../config/config.php';
include_once __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sobre Nós - Sigilosas VIP</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
<?php
$pageTitle = 'Sobre Nós - Sigilosas VIP';
?>
<div class="container py-5">
  <h1 class="mb-4" style="color:#3D263F;">Sobre Nós</h1>
  <div class="accordion" id="faqSobreSigilosas">
    <div class="accordion-item mb-3">
      <h2 class="accordion-header" id="faq1">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
          Como surgiu a Sigilosas VIP?
        </button>
      </h2>
      <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="faq1" data-bs-parent="#faqSobreSigilosas">
        <div class="accordion-body">
          A Sigilosas VIP nasceu a partir de uma vivência real no mercado. Estamos há mais de 5 anos no ramo, conhecendo de perto os desafios, necessidades e sonhos de quem trabalha como acompanhante. Com toda essa experiência, decidimos criar algo diferente: uma plataforma exclusiva, segura e acolhedora, feita para quem busca mais do que apenas uma vitrine — feita para quem quer ser valorizado, respeitado e crescer com liberdade.
        </div>
      </div>
    </div>
    <div class="accordion-item mb-3">
      <h2 class="accordion-header" id="faq2">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
          O que é o suporte 100% humanizado?
        </button>
      </h2>
      <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqSobreSigilosas">
        <div class="accordion-body">
          Nossa equipe está sempre pronta para te apoiar de verdade, com atendimento acolhedor e ágil.
        </div>
      </div>
    </div>
    <div class="accordion-item mb-3">
      <h2 class="accordion-header" id="faq3">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
          Como é a tecnologia da plataforma?
        </button>
      </h2>
      <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqSobreSigilosas">
        <div class="accordion-body">
          Plataforma fácil de usar, com privacidade e segurança para você se destacar com tranquilidade.
        </div>
      </div>
    </div>
    <div class="accordion-item mb-3">
      <h2 class="accordion-header" id="faq4">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
          Terei visibilidade real e oportunidades?
        </button>
      </h2>
      <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="faq4" data-bs-parent="#faqSobreSigilosas">
        <div class="accordion-body">
          Aqui você é vista de verdade, com oportunidades para crescer e conquistar novos clientes.
        </div>
      </div>
    </div>
    <div class="accordion-item mb-3">
      <h2 class="accordion-header" id="faq5">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
          Qual é a missão da Sigilosas VIP?
        </button>
      </h2>
      <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="faq5" data-bs-parent="#faqSobreSigilosas">
        <div class="accordion-body">
          Na Sigilosas VIP, você encontra segurança, privacidade e uma equipe pronta para te apoiar de verdade. Nossa missão é clara: conectar, empoderar e impulsionar cada profissional com seriedade e respeito.
        </div>
      </div>
    </div>
    <div class="accordion-item mb-3">
      <h2 class="accordion-header" id="faq6">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
          Para quem é a Sigilosas VIP?
        </button>
      </h2>
      <div id="collapse6" class="accordion-collapse collapse" aria-labelledby="faq6" data-bs-parent="#faqSobreSigilosas">
        <div class="accordion-body">
          Seja você iniciante ou experiente, a Sigilosas VIP é o seu lugar.
        </div>
      </div>
    </div>
  </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html> 