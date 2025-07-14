<?php
/**
 * Teste Detalhado de Upload - Hostinger
 * Arquivo: test-upload-debug.php
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
    <title>Teste Detalhado de Upload</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        .warning { background: #fff3cd; color: #856404; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Teste Detalhado de Upload - Hostinger</h1>
    
    <h2>1. Informações do Servidor</h2>
    <div class="info">
        <strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?><br>
        <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
        <strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'; ?><br>
        <strong>Script Path:</strong> <?php echo __FILE__; ?>
    </div>

    <h2>2. Configurações de Upload</h2>
    <?php
    $upload_max = ini_get('upload_max_filesize');
    $post_max = ini_get('post_max_size');
    $max_input_time = ini_get('max_input_time');
    $max_execution_time = ini_get('max_execution_time');
    $memory_limit = ini_get('memory_limit');
    
    echo "<div class='info'>";
    echo "<strong>upload_max_filesize:</strong> $upload_max<br>";
    echo "<strong>post_max_size:</strong> $post_max<br>";
    echo "<strong>max_input_time:</strong> $max_input_time<br>";
    echo "<strong>max_execution_time:</strong> $max_execution_time<br>";
    echo "<strong>memory_limit:</strong> $memory_limit<br>";
    echo "</div>";
    ?>

    <h2>3. Teste de Pastas</h2>
    <?php
    $upload_dir = __DIR__ . '/uploads/videos_publicos/';
    $thumbnails_dir = $upload_dir . 'thumbnails/';
    
    echo "<div class='info'>";
    echo "<strong>Pasta de upload:</strong> $upload_dir<br>";
    echo "<strong>Existe:</strong> " . (is_dir($upload_dir) ? '✅ Sim' : '❌ Não') . "<br>";
    echo "<strong>Permissão de escrita:</strong> " . (is_writable($upload_dir) ? '✅ Sim' : '❌ Não') . "<br>";
    echo "<strong>Pasta thumbnails:</strong> $thumbnails_dir<br>";
    echo "<strong>Existe:</strong> " . (is_dir($thumbnails_dir) ? '✅ Sim' : '❌ Não') . "<br>";
    echo "<strong>Permissão de escrita:</strong> " . (is_writable($thumbnails_dir) ? '✅ Sim' : '❌ Não') . "<br>";
    echo "</div>";
    ?>

    <h2>4. Teste de Banco de Dados</h2>
    <?php
    try {
        $db = getDB();
        echo "<div class='success'>✅ Conexão com banco OK</div>";
        
        // Verificar se a tabela existe
        $result = $db->fetchAll("SHOW TABLES LIKE 'videos_publicos'");
        if ($result) {
            echo "<div class='success'>✅ Tabela videos_publicos existe</div>";
            
            // Verificar estrutura da tabela
            $columns = $db->fetchAll("DESCRIBE videos_publicos");
            echo "<div class='info'>";
            echo "<strong>Estrutura da tabela:</strong><br>";
            echo "<pre>";
            foreach ($columns as $col) {
                echo $col['Field'] . " - " . $col['Type'] . " - " . $col['Null'] . " - " . $col['Key'] . " - " . $col['Default'] . "\n";
            }
            echo "</pre>";
            echo "</div>";
        } else {
            echo "<div class='error'>❌ Tabela videos_publicos não existe</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro no banco: " . $e->getMessage() . "</div>";
    }
    ?>

    <h2>5. Teste de Sessão</h2>
    <div class="info">
        <strong>ID da sessão:</strong> <?php echo session_id(); ?><br>
        <strong>acompanhante_id:</strong> <?php echo $_SESSION['acompanhante_id'] ?? 'Não definido'; ?><br>
        <strong>Nome da sessão:</strong> <?php echo session_name(); ?>
    </div>

    <h2>6. Teste de Upload Simples</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="test_file" accept="video/*">
        <button type="submit" name="test_upload">Testar Upload</button>
    </form>

    <?php
    if (isset($_POST['test_upload']) && isset($_FILES['test_file'])) {
        echo "<h3>Resultado do Teste:</h3>";
        
        $file = $_FILES['test_file'];
        echo "<div class='info'>";
        echo "<strong>Nome:</strong> " . $file['name'] . "<br>";
        echo "<strong>Tamanho:</strong> " . $file['size'] . " bytes<br>";
        echo "<strong>Tipo:</strong> " . $file['type'] . "<br>";
        echo "<strong>Erro:</strong> " . $file['error'] . "<br>";
        echo "<strong>Temp:</strong> " . $file['tmp_name'] . "<br>";
        echo "</div>";
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            echo "<div class='success'>✅ Upload básico funcionou!</div>";
            
            // Tentar mover o arquivo
            $dest = $upload_dir . 'test_' . time() . '.mp4';
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                echo "<div class='success'>✅ Arquivo movido com sucesso para: $dest</div>";
                unlink($dest); // Remover arquivo de teste
            } else {
                echo "<div class='error'>❌ Erro ao mover arquivo</div>";
            }
        } else {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do servidor)',
                UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (limite do formulário)',
                UPLOAD_ERR_PARTIAL => 'Upload parcial',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada',
                UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever arquivo',
                UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
            ];
            echo "<div class='error'>❌ " . ($errors[$file['error']] ?? 'Erro desconhecido') . "</div>";
        }
    }
    ?>

    <h2>7. Teste da API de Upload</h2>
    <form id="apiTestForm" enctype="multipart/form-data">
        <input type="file" name="video_publico" accept="video/*" required>
        <input type="text" name="titulo_video" placeholder="Título (opcional)">
        <input type="text" name="descricao_video" placeholder="Descrição (opcional)">
        <button type="submit">Testar API</button>
    </form>
    <div id="apiResult"></div>

    <script>
    document.getElementById('apiTestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var resultDiv = document.getElementById('apiResult');
        
        resultDiv.innerHTML = '<div class="info">Enviando...</div>';
        
        fetch('api/upload-video-publico.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="success">✅ ' + data.message + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="error">❌ ' + data.message + '</div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="error">❌ Erro na requisição: ' + error.message + '</div>';
        });
    });
    </script>
</body>
</html> 