    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <!-- Informações -->
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-heart text-danger"></i> Sigilosas VIP
                    </h5>
                    <p class="text-muted">
                        A plataforma mais segura e discreta para encontrar acompanhantes de luxo no Brasil.
                        Todos os perfis são verificados e aprovados por nossa equipe.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-telegram fa-lg"></i></a>
                    </div>
                </div>
                
                <!-- Links rápidos -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Links Rápidos</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="index.php?page=acompanhantes" class="text-muted text-decoration-none">Acompanhantes</a></li>
                        <li class="mb-2"><a href="index.php?page=blog" class="text-muted text-decoration-none">Blog</a></li>
                        <li class="mb-2"><a href="index.php?page=contato" class="text-muted text-decoration-none">Contato</a></li>
                    </ul>
                </div>
                
                <!-- Suporte -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Suporte</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php?page=ajuda" class="text-muted text-decoration-none">Central de Ajuda</a></li>
                        <li class="mb-2"><a href="index.php?page=faq" class="text-muted text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="index.php?page=contato" class="text-muted text-decoration-none">Fale Conosco</a></li>
                        <li class="mb-2"><a href="index.php?page=denuncias" class="text-muted text-decoration-none">Denunciar</a></li>
                    </ul>
                </div>
                
                <!-- Legal -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php?page=termos" class="text-muted text-decoration-none">Termos de Uso</a></li>
                        <li class="mb-2"><a href="index.php?page=privacidade" class="text-muted text-decoration-none">Privacidade</a></li>
                        <li class="mb-2"><a href="index.php?page=cookies" class="text-muted text-decoration-none">Cookies</a></li>
                        <li class="mb-2"><a href="index.php?page=sobre" class="text-muted text-decoration-none">Sobre Nós</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Newsletter</h6>
                    <p class="text-muted small mb-3">Receba novidades e ofertas especiais</p>
                    <form class="d-flex">
                        <input type="email" class="form-control form-control-sm me-2" placeholder="Seu email">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Copyright -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted small">
                        &copy; <?php echo date('Y'); ?> Sigilosas VIP. Todos os direitos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted small">
                        <i class="fas fa-shield-alt"></i> Site seguro | 
                        <i class="fas fa-user-shield"></i> Privacidade garantida
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="assets/js/main.js"></script>
    
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
</body>
</html> 