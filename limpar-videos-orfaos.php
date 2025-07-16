<?php
require_once 'config/database.php';
$db = getDB();

echo "🧹 Limpeza de Vídeos Órfãos - Sistema Sigilosas\n";
echo "==============================================\n\n";

// Verificar vídeos públicos órfãos
echo "📹 Verificando vídeos públicos órfãos...\n";

$videos_publicos = $db->fetchAll('SELECT * FROM videos_publicos');
$removidos = 0;

foreach ($videos_publicos as $video) {
    $arquivo = __DIR__ . '/uploads/videos_publicos/' . $video['url'];
    
    if (!file_exists($arquivo)) {
        echo "🗑️  Removendo registro órfão: ID {$video['id']} - {$video['url']}\n";
        
        // Remover do banco
        $db->delete('videos_publicos', 'id = ?', [$video['id']]);
        $removidos++;
    }
}

if ($removidos === 0) {
    echo "✅ Nenhum vídeo órfão encontrado!\n";
} else {
    echo "\n✅ Limpeza concluída! $removidos registros órfãos removidos.\n";
}

// Verificar também vídeos de verificação órfãos
echo "\n📹 Verificando vídeos de verificação órfãos...\n";

$videos_verificacao = $db->fetchAll('SELECT * FROM videos_verificacao');
$removidos_verificacao = 0;

foreach ($videos_verificacao as $video) {
    $arquivo = __DIR__ . '/uploads/verificacao/' . $video['url'];
    
    if (!file_exists($arquivo)) {
        echo "🗑️  Removendo registro órfão: ID {$video['id']} - {$video['url']}\n";
        
        // Remover do banco
        $db->delete('videos_verificacao', 'id = ?', [$video['id']]);
        $removidos_verificacao++;
    }
}

if ($removidos_verificacao === 0) {
    echo "✅ Nenhum vídeo de verificação órfão encontrado!\n";
} else {
    echo "\n✅ Limpeza concluída! $removidos_verificacao registros órfãos de verificação removidos.\n";
}

// Verificar também fotos órfãs
echo "\n📸 Verificando fotos órfãs...\n";

$fotos = $db->fetchAll('SELECT * FROM fotos');
$removidas_fotos = 0;

foreach ($fotos as $foto) {
    $arquivo_galeria = __DIR__ . '/uploads/galeria/' . $foto['url'];
    $arquivo_perfil = __DIR__ . '/uploads/perfil/' . $foto['url'];
    
    // Se não existe nem na galeria nem no perfil
    if (!file_exists($arquivo_galeria) && !file_exists($arquivo_perfil)) {
        echo "🗑️  Removendo foto órfã: ID {$foto['id']} - {$foto['url']}\n";
        
        // Remover do banco
        $db->delete('fotos', 'id = ?', [$foto['id']]);
        $removidas_fotos++;
    }
}

if ($removidas_fotos === 0) {
    echo "✅ Nenhuma foto órfã encontrada!\n";
} else {
    echo "\n✅ Limpeza concluída! $removidas_fotos registros órfãos de fotos removidos.\n";
}

echo "\n🎯 Limpeza geral concluída!\n";
echo "   - Vídeos públicos órfãos removidos: $removidos\n";
echo "   - Vídeos verificação órfãos removidos: $removidos_verificacao\n";
echo "   - Fotos órfãs removidas: $removidas_fotos\n";

if ($removidos > 0 || $removidos_verificacao > 0 || $removidas_fotos > 0) {
    echo "\n🔄 Recomendação: Atualize a página do admin para ver as mudanças.\n";
}
?> 