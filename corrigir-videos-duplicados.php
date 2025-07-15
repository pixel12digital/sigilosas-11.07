<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();

echo "<h2>Correção de Vídeos Duplicados</h2>";

// Verificar duplicatas por acompanhante_id + URL
$duplicates = $db->fetchAll("
    SELECT acompanhante_id, url, COUNT(*) as count, GROUP_CONCAT(id ORDER BY id) as ids
    FROM videos_publicos 
    GROUP BY acompanhante_id, url 
    HAVING COUNT(*) > 1
");

if ($duplicates) {
    echo "<h3>⚠️ Vídeos Duplicados Encontrados:</h3>";
    
    foreach ($duplicates as $dup) {
        echo "<div style='border: 1px solid #ff6b6b; padding: 10px; margin: 10px 0; background: #fff5f5;'>";
        echo "<p><strong>Acompanhante ID:</strong> " . $dup['acompanhante_id'] . "</p>";
        echo "<p><strong>URL:</strong> " . htmlspecialchars($dup['url']) . "</p>";
        echo "<p><strong>Quantidade:</strong> " . $dup['count'] . " registros</p>";
        echo "<p><strong>IDs:</strong> " . $dup['ids'] . "</p>";
        
        // Buscar detalhes dos registros duplicados
        $ids_array = explode(',', $dup['ids']);
        $details = $db->fetchAll("SELECT * FROM videos_publicos WHERE id IN (" . implode(',', $ids_array) . ") ORDER BY id");
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Título</th><th>Status</th><th>Created At</th><th>Ação</th></tr>";
        
        foreach ($details as $detail) {
            echo "<tr>";
            echo "<td>" . $detail['id'] . "</td>";
            echo "<td>" . htmlspecialchars($detail['titulo'] ?? '') . "</td>";
            echo "<td>" . $detail['status'] . "</td>";
            echo "<td>" . $detail['created_at'] . "</td>";
            echo "<td>";
            
            // Manter apenas o primeiro registro (mais antigo)
            if ($detail['id'] == min($ids_array)) {
                echo "<span style='color: green;'>✅ Manter (mais antigo)</span>";
            } else {
                echo "<a href='?action=delete&id=" . $detail['id'] . "' style='color: red;' onclick='return confirm(\"Excluir este registro duplicado?\")'>🗑️ Excluir</a>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
    // Processar exclusão se solicitado
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id_to_delete = (int)$_GET['id'];
        
        // Verificar se é realmente um duplicado
        $video = $db->fetch("SELECT * FROM videos_publicos WHERE id = ?", [$id_to_delete]);
        if ($video) {
            $duplicates_check = $db->fetchAll("
                SELECT COUNT(*) as count, GROUP_CONCAT(id ORDER BY id) as ids
                FROM videos_publicos 
                WHERE acompanhante_id = ? AND url = ?
                GROUP BY acompanhante_id, url 
                HAVING COUNT(*) > 1
            ", [$video['acompanhante_id'], $video['url']]);
            
            if ($duplicates_check) {
                $ids_array = explode(',', $duplicates_check[0]['ids']);
                // Só permitir excluir se não for o mais antigo
                if ($id_to_delete != min($ids_array)) {
                    $db->query("DELETE FROM videos_publicos WHERE id = ?", [$id_to_delete]);
                    echo "<div style='background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
                    echo "✅ Registro ID " . $id_to_delete . " excluído com sucesso!";
                    echo "</div>";
                    
                    // Redirecionar para evitar repost
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
                    echo "❌ Não é possível excluir o registro mais antigo!";
                    echo "</div>";
                }
            }
        }
    }
    
    echo "<h3>📋 Instruções:</h3>";
    echo "<p>1. Para cada grupo de duplicatas, o registro mais antigo (ID menor) será mantido</p>";
    echo "<p>2. Clique em '🗑️ Excluir' nos registros mais novos para removê-los</p>";
    echo "<p>3. Após corrigir, verifique novamente a página do perfil</p>";
    
} else {
    echo "<div style='background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
    echo "✅ Nenhum vídeo duplicado encontrado!";
    echo "</div>";
}

// Verificar se há vídeos órfãos (arquivo não existe)
echo "<h3>Verificando Vídeos Órfãos:</h3>";
$videos = $db->fetchAll("SELECT * FROM videos_publicos");
$orphans = [];

foreach ($videos as $video) {
    $file_path = __DIR__ . '/uploads/videos_publicos/' . $video['url'];
    if (!file_exists($file_path)) {
        $orphans[] = $video;
    }
}

if ($orphans) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
    echo "<p>⚠️ Encontrados " . count($orphans) . " vídeos órfãos (arquivo não existe):</p>";
    echo "<ul>";
    foreach ($orphans as $orphan) {
        echo "<li>ID: " . $orphan['id'] . " - URL: " . htmlspecialchars($orphan['url']) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
} else {
    echo "<p style='color: green;'>✅ Todos os vídeos têm arquivos correspondentes.</p>";
}

echo "<hr>";
echo "<p><a href='verificar-videos.php'>🔍 Verificar Novamente</a> | <a href='acompanhante/perfil.php'>📱 Voltar ao Perfil</a></p>";
?> 