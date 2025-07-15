<?php
require_once __DIR__ . '/config/config.php';
session_name('sigilosas_acompanhante_session');
session_start();

// Simular login para teste
$_SESSION['acompanhante_id'] = 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Video Upload - Perfil</title>
    <link href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Teste Video Upload - Perfil</h1>
        
        <?php
        // Verificar arquivos necessários
        $js_file = __DIR__ . '/assets/js/video-upload.js';
        $api_file = __DIR__ . '/api/upload-video-publico.php';
        $upload_dir = __DIR__ . '/uploads/videos_publicos/';
        
        echo "<h3>Verificações:</h3>";
        if (file_exists($js_file)) {
            echo "✅ Arquivo video-upload.js existe<br>";
        } else {
            echo "❌ Arquivo video-upload.js NÃO existe<br>";
        }
        
        if (file_exists($api_file)) {
            echo "✅ API upload-video-publico.php existe<br>";
        } else {
            echo "❌ API upload-video-publico.php NÃO existe<br>";
        }
        
        if (is_dir($upload_dir)) {
            echo "✅ Diretório uploads/videos_publicos/ existe<br>";
            if (is_writable($upload_dir)) {
                echo "✅ Diretório é gravável<br>";
            } else {
                echo "❌ Diretório NÃO é gravável<br>";
            }
        } else {
            echo "❌ Diretório uploads/videos_publicos/ NÃO existe<br>";
        }
        ?>
        
        <h3>Formulário de Teste (igual ao perfil):</h3>
        
        <!-- SEÇÃO DE VÍDEOS PÚBLICOS -->
        <div class="card shadow-sm mb-4" style="background:#fff;color:#3D263F;box-shadow:0 2px 12px rgba(61,38,63,0.08);">
          <div class="card-body">
            <div class="fw-bold mb-2" style="color:#3D263F;"><i class="fas fa-video"></i> Vídeos Públicos</div>
            <div class="mb-2 text-muted">Adicione vídeos curtos para seu perfil público. Apenas vídeos aprovados serão exibidos no site. (Máx. 50MB, formatos: mp4, webm, mov)</div>
            <form id="formVideoPublico" enctype="multipart/form-data" style="margin-bottom:0;" method="post" action="javascript:void(0);">
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
                  <button type="submit" id="btnEnviarVideo" class="btn btn-primary w-100"><i class="fas fa-upload"></i> Enviar</button>
                </div>
              </div>
            </form>
            <div id="msgVideoPublico" class="mt-2"></div>
            <div id="listaVideosPublicos" class="row mt-4 g-3">
              <div class="col-12 text-center text-muted">Nenhum vídeo enviado ainda.</div>
            </div>
          </div>
        </div>
        
        <div id="status" class="alert alert-info">
            Status: Aguardando teste...
        </div>
    </div>

    <script>
    // Definir SITE_URL para o JavaScript
    const SITE_URL = '<?php echo SITE_URL; ?>';
    
    console.log('=== TESTE PERFIL VIDEO UPLOAD ===');
    console.log('SITE_URL:', SITE_URL);
    
    // Verificar se o formulário existe
    const form = document.getElementById('formVideoPublico');
    console.log('Formulário encontrado:', form);
    
    if (form) {
        console.log('Action:', form.action);
        console.log('Method:', form.method);
        console.log('Enctype:', form.enctype);
    }
    
    // Verificar se o botão existe
    const btn = document.getElementById('btnEnviarVideo');
    console.log('Botão encontrado:', btn);
    
    // Adicionar event listener para detectar se o formulário está sendo submetido
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('=== SUBMIT DETECTADO ===');
            console.log('Evento:', e);
            console.log('Default prevented:', e.defaultPrevented);
            
            // Atualizar status
            document.getElementById('status').innerHTML = 
                '<div class="alert alert-warning">Submit detectado - verificando se foi prevenido...</div>';
            
            // Se não foi prevenido, mostrar erro
            setTimeout(() => {
                if (!e.defaultPrevented) {
                    document.getElementById('status').innerHTML = 
                        '<div class="alert alert-danger">ERRO: Submit não foi prevenido! Página pode recarregar.</div>';
                } else {
                    document.getElementById('status').innerHTML = 
                        '<div class="alert alert-success">✅ Submit prevenido com sucesso!</div>';
                }
            }, 100);
        });
    }
    </script>

    <!-- Script para upload de vídeo público -->
    <script src="<?php echo SITE_URL; ?>/assets/js/video-upload.js"></script>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 