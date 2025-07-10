    </main>

    <!-- Rodapé padrão -->
    <footer class="text-center py-4 bg-light">
        <small>&copy; <?php echo date('Y'); ?> Sigilosas VIP. Todos os direitos reservados.</small>
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