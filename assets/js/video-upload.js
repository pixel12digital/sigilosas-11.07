// Upload de vídeo público via AJAX
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== CONFIGURANDO FORMULÁRIO DE VÍDEO ===');
    var formVideoPublico = document.getElementById('formVideoPublico');
    console.log('Formulário de vídeo encontrado:', formVideoPublico);
    
    if (formVideoPublico) {
        console.log('Adicionando event listener ao formulário de vídeo');
        formVideoPublico.addEventListener('submit', function(e) {
            console.log('=== SUBMIT DO FORMULÁRIO DE VÍDEO ===');
            console.log('Evento:', e);
            e.preventDefault();
            
            var input = document.getElementById('video_publico');
            var titulo = document.getElementById('titulo_video').value;
            var descricao = document.getElementById('descricao_video').value;
            var btn = document.getElementById('btnEnviarVideo');
            var msg = document.getElementById('msgVideoPublico');
            
            if (!input.files.length) {
                msg.innerHTML = '<div class="alert alert-warning">Selecione um vídeo primeiro.</div>';
                return;
            }
            
            var file = input.files[0];
            
            // Validação de tamanho no frontend
            if (file.size > 50 * 1024 * 1024) {
                msg.innerHTML = '<div class="alert alert-danger">O vídeo excede o tamanho máximo permitido (50MB).</div>';
                return;
            }
            
            // Validação de tipo
            var allowedTypes = ['video/mp4', 'video/webm', 'video/quicktime'];
            if (!allowedTypes.includes(file.type)) {
                msg.innerHTML = '<div class="alert alert-danger">Formato de vídeo não permitido. Use MP4, WebM ou MOV.</div>';
                return;
            }
            
            // Desabilitar botão e mostrar loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            msg.innerHTML = '<div class="alert alert-info">Enviando vídeo, aguarde...</div>';
            
            var formData = new FormData();
            formData.append('video_publico', file);
            formData.append('titulo_video', titulo);
            formData.append('descricao_video', descricao);
            formData.append('action', 'upload_video_publico');
            
            fetch(SITE_URL + '/api/upload-video-publico.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    msg.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    // Limpar formulário
                    document.getElementById('formVideoPublico').reset();
                    // Atualizar lista de vídeos dinamicamente
                    setTimeout(() => {
                        if (typeof atualizarListaVideos === 'function') {
                            atualizarListaVideos();
                        } else {
                            // Se a função não existe, recarregar a página
                            location.reload();
                        }
                    }, 1000);
                } else {
                    msg.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                msg.innerHTML = '<div class="alert alert-danger">Erro ao enviar vídeo. Tente novamente. Erro: ' + error.message + '</div>';
            })
            .finally(() => {
                // Reabilitar botão
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-upload"></i> Enviar';
            });
        });
    } else {
        console.log('ERRO: Formulário de vídeo não encontrado!');
    }
});

// Função para atualizar lista de vídeos dinamicamente
function atualizarListaVideos() {
    console.log('=== ATUALIZANDO LISTA DE VÍDEOS ===');
    fetch(SITE_URL + '/api/get-videos-publicos.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Dados recebidos da API:', data);
        if (data.success) {
            var container = document.getElementById('listaVideosPublicos');
            if (container) {
                console.log('Container encontrado, atualizando HTML...');
                container.innerHTML = data.html;
                console.log('Lista de vídeos atualizada com sucesso');
            } else {
                console.log('ERRO: Container listaVideosPublicos não encontrado');
            }
        } else {
            console.log('ERRO: API retornou success=false');
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar lista:', error);
    });
} 