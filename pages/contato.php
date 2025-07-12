<?php
$pageTitle = 'Contato - Sigilosas VIP';
?>
<div class="container py-5">
  <h1 class="mb-4" style="color:#3D263F;">Contato</h1>
  <p>Entre em contato conosco pelo formulário abaixo ou pelos nossos canais oficiais:</p>
  <ul>
    <li>Email: <a href="mailto:contato@sigilosasvip.com.br">contato@sigilosasvip.com.br</a></li>
    <li>WhatsApp: <a href="https://wa.me/5547996829294" target="_blank">(47) 99682-9294</a></li>
  </ul>
  <form method="post" class="mt-4" style="max-width:500px;">
    <div class="mb-3">
      <label for="nome" class="form-label">Nome</label>
      <input type="text" class="form-control" id="nome" name="nome" required>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
      <label for="mensagem" class="form-label">Mensagem</label>
      <textarea class="form-control" id="mensagem" name="mensagem" rows="4" required></textarea>
    </div>
    <button type="submit" class="btn" style="background:#3D263F;color:#F3EAC2;">Enviar</button>
  </form>
</div> 