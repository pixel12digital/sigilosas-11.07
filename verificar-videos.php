<?php
require_once 'config/database.php';
$db = getDB();

echo "🔍 Verificando vídeos da acompanhante ID 2...\n\n";

$videos = $db->fetchAll('SELECT id, acompanhante_id, url, titulo, status, created_at FROM videos_publicos WHERE acompanhante_id = 2 ORDER BY created_at DESC');

if (empty($videos)) {
    echo "❌ Nenhum vídeo encontrado para a acompanhante ID 2\n";
} else {
    echo "📹 Vídeos encontrados:\n";
    foreach ($videos as $v) {
        echo "   ID: {$v['id']} - URL: {$v['url']} - Status: {$v['status']} - Data: {$v['created_at']}\n";
        
        // Verificar se o arquivo físico existe
        $arquivo = __DIR__ . '/uploads/videos_publicos/' . $v['url'];
        $existe = file_exists($arquivo) ? "✅ Existe" : "❌ Não existe";
        echo "   Arquivo físico: $existe\n\n";
    }
}

// Verificar se há duplicatas pela URL
echo "\n🔍 Verificando duplicatas...\n";
$urls = array_column($videos, 'url');
$duplicatas = array_count_values($urls);

foreach ($duplicatas as $url => $count) {
    if ($count > 1) {
        echo "⚠️  URL duplicada encontrada: $url ($count vezes)\n";
        
        // Mostrar quais IDs têm essa URL
        foreach ($videos as $v) {
            if ($v['url'] === $url) {
                echo "   - ID {$v['id']} (Status: {$v['status']})\n";
            }
        }
    }
}

echo "\n✅ Verificação completa!\n";
?> 