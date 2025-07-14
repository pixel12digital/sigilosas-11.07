<?php
/**
 * Teste Simples de Upload
 * Arquivo: test-upload-simple.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

session_name('sigilosas_acompanhante_session');
session_start();

// Simular login de acompanhante para teste
$_SESSION['acompanhante_id'] = 1;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste de Upload</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <h1>Teste de Upload de Vídeo</h1>
    
    <form id="uploadForm" enctype="multipart/form-data">
        <p>
            <label>Selecione um vídeo:</label><br>
            <input type="file" name="video_publico" accept="video/mp4,video/webm,video/quicktime" required>
        </p>
        <p>
            <label>Título (opcional):</label><br>
            <input type="text" name="titulo_video" maxlength="100">
        </p>
        <p>
            <label>Descrição (opcional):</label><br>
            <input type="text" name="descricao_video" maxlength="255">
        </p>
        <p>
            <button type="submit">Enviar Vídeo</button>
        </p>
    </form>
    
    <div id="result"></div>
    
    <script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var resultDiv = document.getElementById('result');
        
        resultDiv.innerHTML = '<div class="info">Enviando...</div>';
        
        fetch('api/upload-video-publico.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="success">✅ ' + data.message + '</div>';
                this.reset();
            } else {
                resultDiv.innerHTML = '<div class="error">❌ ' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            resultDiv.innerHTML = '<div class="error">❌ Erro na requisição: ' + error.message + '</div>';
        });
    });
    </script>
    
    <h2>Informações do Sistema</h2>
    <div class="info">
        <p><strong>Upload Max:</strong> <?php echo ini_get('upload_max_filesize'); ?></p>
        <p><strong>Post Max:</strong> <?php echo ini_get('post_max_size'); ?></p>
        <p><strong>Max Execution Time:</strong> <?php echo ini_get('max_execution_time'); ?>s</p>
        <p><strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?></p>
        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
        <p><strong>Acompanhante ID:</strong> <?php echo $_SESSION['acompanhante_id'] ?? 'Não definido'; ?></p>
    </div>
</body>
</html> 