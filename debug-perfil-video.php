<?php
// Script para debug do upload de vídeo no perfil
// Adicione este código no início do acompanhante/perfil.php para debug

echo "<div style='position:fixed; top:10px; right:10px; background:#fff; border:2px solid #007bff; padding:10px; z-index:9999; max-width:300px; font-size:12px;'>";
echo "<h4>Debug Video Upload</h4>";

// Verificar se o formulário existe
echo "<p><strong>Formulário:</strong> ";
if (isset($_GET['check_form'])) {
    $form = '<form id="formVideoPublico" enctype="multipart/form-data" style="margin-bottom:0;" method="post" action="javascript:void(0);">';
    echo "✅ Encontrado</p>";
    echo "<p><strong>Action:</strong> javascript:void(0);</p>";
    echo "<p><strong>Method:</strong> post</p>";
} else {
    echo "❓ Clique <a href='?check_form=1'>aqui</a> para verificar</p>";
}

// Verificar se o JavaScript está carregado
echo "<p><strong>video-upload.js:</strong> ";
$js_file = __DIR__ . '/assets/js/video-upload.js';
if (file_exists($js_file)) {
    echo "✅ Existe (" . filesize($js_file) . " bytes)</p>";
} else {
    echo "❌ Não existe</p>";
}

// Verificar se a API existe
echo "<p><strong>API:</strong> ";
$api_file = __DIR__ . '/api/upload-video-publico.php';
if (file_exists($api_file)) {
    echo "✅ Existe</p>";
} else {
    echo "❌ Não existe</p>";
}

// Verificar diretório de upload
echo "<p><strong>Upload dir:</strong> ";
$upload_dir = __DIR__ . '/uploads/videos_publicos/';
if (is_dir($upload_dir)) {
    if (is_writable($upload_dir)) {
        echo "✅ Existe e gravável</p>";
    } else {
        echo "⚠️ Existe mas não gravável</p>";
    }
} else {
    echo "❌ Não existe</p>";
}

// Status do JavaScript
echo "<p><strong>JS Status:</strong> <span id='js-status'>Carregando...</span></p>";

// Botão para testar
echo "<button onclick='testarVideoUpload()' style='background:#007bff; color:#fff; border:none; padding:5px 10px; border-radius:3px; cursor:pointer;'>Testar Upload</button>";

echo "<div id='test-result' style='margin-top:10px;'></div>";

echo "</div>";

// JavaScript para debug
echo "<script>
function testarVideoUpload() {
    console.log('=== TESTE VIDEO UPLOAD ===');
    
    const form = document.getElementById('formVideoPublico');
    const btn = document.getElementById('btnEnviarVideo');
    const result = document.getElementById('test-result');
    
    if (!form) {
        result.innerHTML = '<p style=\"color:red;\">❌ Formulário não encontrado!</p>';
        return;
    }
    
    if (!btn) {
        result.innerHTML = '<p style=\"color:red;\">❌ Botão não encontrado!</p>';
        return;
    }
    
    result.innerHTML = '<p style=\"color:green;\">✅ Formulário e botão encontrados</p>';
    
    // Verificar se o event listener está funcionando
    let submitPrevented = false;
    const originalSubmit = form.submit;
    
    form.submit = function() {
        submitPrevented = true;
        console.log('Submit interceptado!');
        result.innerHTML += '<p style=\"color:orange;\">⚠️ Submit interceptado</p>';
    };
    
    // Simular clique no botão
    setTimeout(() => {
        btn.click();
        setTimeout(() => {
            if (submitPrevented) {
                result.innerHTML += '<p style=\"color:green;\">✅ Submit prevenido com sucesso!</p>';
            } else {
                result.innerHTML += '<p style=\"color:red;\">❌ Submit não foi prevenido!</p>';
            }
            form.submit = originalSubmit;
        }, 100);
    }, 100);
}

// Verificar quando o JavaScript carrega
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('js-status').textContent = '✅ Carregado';
    console.log('Debug script carregado');
});
</script>";
?> 