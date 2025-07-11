    </main>

    <!-- Rodapé padrão -->
    <footer class="text-center py-4" style="background: #D6D3D9 !important; color: #222;">
        <div class="container">
            <div class="row align-items-center justify-content-between gy-3">
                <div class="col-md-3 text-md-start mb-3 mb-md-0">
                    <img src="/assets/img/logo.png" alt="Sigilosas VIP" style="max-width: 140px; height: auto;">
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="fw-bold mb-2" style="color:#3D263F;">Contato</div>
                    <div>Email: <a href="mailto:contato@sigilosasvip.com.br" style="color:#3D263F; text-decoration:underline;">contato@sigilosasvip.com.br</a></div>
                    <div>Telefone: <a href="tel:+5547996829294" style="color:#3D263F; text-decoration:underline;">(47) 99682-9294</a></div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="fw-bold mb-2" style="color:#3D263F;">Institucional</div>
                    <ul class="list-unstyled mb-0">
                        <li><a href="/pages/sobre.php" style="color:#3D263F; text-decoration:underline;">Sobre Nós</a></li>
                        <li><a href="/pages/contato.php" style="color:#3D263F; text-decoration:underline;">Contato</a></li>
                        <li><a href="/pages/politica-privacidade.php" style="color:#3D263F; text-decoration:underline;">Política de Privacidade</a></li>
                        <li><a href="/pages/termos-uso.php" style="color:#3D263F; text-decoration:underline;">Termos de Uso</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="fw-bold mb-2" style="color:#3D263F;">Acesso Rápido</div>
                    <ul class="list-unstyled mb-0">
                        <li><a href="/" style="color:#3D263F; text-decoration:underline;">Home</a></li>
                        <li><a href="/pages/blog.php" style="color:#3D263F; text-decoration:underline;">Blog</a></li>
                        <li><a href="/pages/login-acompanhante.php" style="color:#3D263F; text-decoration:underline;">Área do Acompanhante</a></li>
                        <li><a href="/admin/login.php" style="color:#3D263F; text-decoration:underline;">Admin</a></li>
                    </ul>
                </div>
            </div>
            <hr style="background:#3D263F; opacity:0.2; margin:24px 0 12px 0;">
            <div class="row">
                <div class="col-12 small text-center" style="color:#3D263F; opacity:0.9;">
                    &copy; <?php echo date('Y'); ?> Sigilosas VIP. Todos os direitos reservados.
                </div>
            </div>
        </div>
    </footer>
    <style>
    footer a:hover { text-decoration: underline !important; color: #222 !important; }
    </style>

    <!-- Scripts -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="/assets/js/main.js"></script>
    
    <!-- Script para aviso +18 -->
    <script>
        // Verificar se já aceitou o aviso
        if (!localStorage.getItem('aviso18_aceito')) {
            // Mostrar popup após 2 segundos
            setTimeout(function() {
                new bootstrap.Modal(document.getElementById('aviso18')).show();
            }, 2000);
        }
        
        function aceitarAviso() {
            localStorage.setItem('aviso18_aceito', 'true');
            bootstrap.Modal.getInstance(document.getElementById('aviso18')).hide();
        }
        
        // Função para mostrar loading
        function showLoading() {
            document.getElementById('loading').classList.remove('d-none');
        }
        
        function hideLoading() {
            document.getElementById('loading').classList.add('d-none');
        }
        
        // Interceptar formulários para mostrar loading
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    showLoading();
                });
            });
            
            // Interceptar links para mostrar loading
            const links = document.querySelectorAll('a[href^="api/"]');
            links.forEach(link => {
                link.addEventListener('click', function() {
                    showLoading();
                });
            });
        });
    </script>
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
      border-radius: 50%;
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
      width: 52px;
      height: 52px;
      justify-content: center;
      font-size: 1rem;
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
      font-size: 1rem;
      margin: 0;
    }
    #suporte-label {
      position: fixed;
      bottom: 80px;
      right: 24px;
      z-index: 9999;
      background: none;
      color: #3D263F;
      border-radius: 0;
      padding: 0;
      font-weight: 500;
      font-size: 1rem;
      box-shadow: none;
      display: block;
      text-align: center;
      opacity: 0.7;
    }
    @media (max-width: 600px) {
      #whatsapp-suporte {
        bottom: 16px;
        right: 16px;
        width: 44px;
        height: 44px;
      }
      #suporte-label {
        bottom: 16px;
        right: 68px;
        font-size: 0.95rem;
        padding: 6px 12px;
      }
    }
    </style>
    <span id="suporte-label">Suporte</span>
    <a href="https://wa.me/5547996829294?text=Olá! Preciso de suporte no site Sigilosas." id="whatsapp-suporte" target="_blank" rel="noopener">
      <span class="wa-icon"><i class="fab fa-whatsapp"></i></span>
    </a>
    <!-- Certifique-se de que o FontAwesome está carregado para o ícone do WhatsApp -->
</body>
</html> 