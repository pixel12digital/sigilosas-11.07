<?php
// Teste específico para upload de vídeo
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>TESTE DE UPLOAD DE VÍDEO</h1>";

// Verificar se o arquivo JavaScript existe
$js_file = __DIR__ . '/assets/js/video-upload.js';
if (file_exists($js_file)) {
    echo "✅ Arquivo video-upload.js existe<br>";
    echo "Tamanho: " . filesize($js_file) . " bytes<br>";
} else {
    echo "❌ Arquivo video-upload.js NÃO existe<br>";
}

// Verificar se a API existe
$api_file = __DIR__ . '/api/upload-video-publico.php';
if (file_exists($api_file)) {
    echo "✅ API upload-video-publico.php existe<br>";
} else {
    echo "❌ API upload-video-publico.php NÃO existe<br>";
}

// Verificar diretório de upload
$upload_dir = __DIR__ . '/uploads/videos_publicos/';
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

echo "<h2>Teste de Formulário</h2>";
?>
<form id="formVideoPublico" enctype="multipart/form-data" method="post" action="javascript:void(0);">
    <div>
        <label>Vídeo: <input type="file" id="video_publico" name="video_publico" accept="video/mp4,video/webm,video/quicktime"></label>
    </div>
    <br>
    <div>
        <label>Título: <input type="text" id="titulo_video" name="titulo_video" value="Teste de Upload"></label>
    </div>
    <br>
    <div>
        <label>Descrição: <input type="text" id="descricao_video" name="descricao_video" value="Teste de descrição"></label>
    </div>
    <br>
    <button type="submit" id="btnEnviarVideo">Enviar Vídeo</button>
</form>

<div id="msgVideoPublico"></div>

<script>
// Teste básico do JavaScript
console.log('=== TESTE DE UPLOAD DE VÍDEO ===');
console.log('Formulário encontrado:', document.getElementById('formVideoPublico'));
console.log('Botão encontrado:', document.getElementById('btnEnviarVideo'));

document.getElementById('formVideoPublico').addEventListener('submit', function(e) {
    console.log('=== SUBMIT DO FORMULÁRIO DE TESTE ===');
    e.preventDefault();
    console.log('Evento prevenido com sucesso');
    document.getElementById('msgVideoPublico').innerHTML = '<div style="color: green;">✅ JavaScript funcionando - submit prevenido</div>';
});
</script> 