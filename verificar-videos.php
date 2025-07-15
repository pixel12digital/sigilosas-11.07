<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();

echo "<h2>Verificação de Vídeos Públicos</h2>";

// Verificar todos os vídeos
$videos = $db->fetchAll("SELECT * FROM videos_publicos ORDER BY acompanhante_id, created_at DESC");

echo "<h3>Total de vídeos: " . count($videos) . "</h3>";

if ($videos) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Acompanhante ID</th><th>URL</th><th>Título</th><th>Status</th><th>Created At</th></tr>";
    
    foreach ($videos as $video) {
        echo "<tr>";
        echo "<td>" . $video['id'] . "</td>";
        echo "<td>" . $video['acompanhante_id'] . "</td>";
        echo "<td>" . htmlspecialchars($video['url']) . "</td>";
        echo "<td>" . htmlspecialchars($video['titulo'] ?? '') . "</td>";
        echo "<td>" . $video['status'] . "</td>";
        echo "<td>" . $video['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar duplicatas por URL
    echo "<h3>Verificando duplicatas por URL:</h3>";
    $duplicates = $db->fetchAll("
        SELECT url, COUNT(*) as count, GROUP_CONCAT(id) as ids
        FROM videos_publicos 
        GROUP BY url 
        HAVING COUNT(*) > 1
    ");
    
    if ($duplicates) {
        echo "<p style='color: red;'>⚠️ ENCONTRADAS DUPLICATAS:</p>";
        foreach ($duplicates as $dup) {
            echo "<p>URL: " . htmlspecialchars($dup['url']) . " - " . $dup['count'] . " registros (IDs: " . $dup['ids'] . ")</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Nenhuma duplicata encontrada por URL</p>";
    }
    
    // Verificar duplicatas por acompanhante_id + URL
    echo "<h3>Verificando duplicatas por acompanhante + URL:</h3>";
    $duplicates_acompanhante = $db->fetchAll("
        SELECT acompanhante_id, url, COUNT(*) as count, GROUP_CONCAT(id) as ids
        FROM videos_publicos 
        GROUP BY acompanhante_id, url 
        HAVING COUNT(*) > 1
    ");
    
    if ($duplicates_acompanhante) {
        echo "<p style='color: red;'>⚠️ ENCONTRADAS DUPLICATAS POR ACOMPANHANTE:</p>";
        foreach ($duplicates_acompanhante as $dup) {
            echo "<p>Acompanhante " . $dup['acompanhante_id'] . " - URL: " . htmlspecialchars($dup['url']) . " - " . $dup['count'] . " registros (IDs: " . $dup['ids'] . ")</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Nenhuma duplicata encontrada por acompanhante + URL</p>";
    }
    
} else {
    echo "<p>Nenhum vídeo encontrado.</p>";
}

// Verificar estrutura da tabela
echo "<h3>Estrutura da tabela videos_publicos:</h3>";
$structure = $db->fetchAll("DESCRIBE videos_publicos");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
foreach ($structure as $field) {
    echo "<tr>";
    echo "<td>" . $field['Field'] . "</td>";
    echo "<td>" . $field['Type'] . "</td>";
    echo "<td>" . $field['Null'] . "</td>";
    echo "<td>" . $field['Key'] . "</td>";
    echo "<td>" . $field['Default'] . "</td>";
    echo "<td>" . $field['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?> 