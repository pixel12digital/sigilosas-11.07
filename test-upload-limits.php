<?php
/**
 * Teste de Limites de Upload
 * Arquivo: test-upload-limits.php
 */

echo "<h2>Configurações de Upload do PHP</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Configuração</th><th>Valor</th><th>Status</th></tr>";

// Verificar upload_max_filesize
$upload_max = ini_get('upload_max_filesize');
$upload_max_bytes = return_bytes($upload_max);
echo "<tr>";
echo "<td>upload_max_filesize</td>";
echo "<td>$upload_max ($upload_max_bytes bytes)</td>";
echo "<td>" . ($upload_max_bytes >= 52428800 ? "✅ OK" : "❌ Muito baixo") . "</td>";
echo "</tr>";

// Verificar post_max_size
$post_max = ini_get('post_max_size');
$post_max_bytes = return_bytes($post_max);
echo "<tr>";
echo "<td>post_max_size</td>";
echo "<td>$post_max ($post_max_bytes bytes)</td>";
echo "<td>" . ($post_max_bytes >= 52428800 ? "✅ OK" : "❌ Muito baixo") . "</td>";
echo "</tr>";

// Verificar max_execution_time
$max_exec = ini_get('max_execution_time');
echo "<tr>";
echo "<td>max_execution_time</td>";
echo "<td>$max_exec segundos</td>";
echo "<td>" . ($max_exec >= 300 || $max_exec == 0 ? "✅ OK" : "❌ Muito baixo") . "</td>";
echo "</tr>";

// Verificar max_input_time
$max_input = ini_get('max_input_time');
echo "<tr>";
echo "<td>max_input_time</td>";
echo "<td>$max_input segundos</td>";
echo "<td>" . ($max_input >= 300 || $max_input == -1 ? "✅ OK" : "❌ Muito baixo") . "</td>";
echo "</tr>";

// Verificar memory_limit
$memory_limit = ini_get('memory_limit');
$memory_bytes = return_bytes($memory_limit);
echo "<tr>";
echo "<td>memory_limit</td>";
echo "<td>$memory_limit ($memory_bytes bytes)</td>";
echo "<td>" . ($memory_bytes >= 134217728 ? "✅ OK" : "❌ Muito baixo") . "</td>";
echo "</tr>";

echo "</table>";

echo "<h3>Teste de Pasta de Upload</h3>";
$upload_dir = __DIR__ . '/uploads/videos_publicos/';
echo "<p>Pasta: $upload_dir</p>";
echo "<p>Existe: " . (is_dir($upload_dir) ? "✅ Sim" : "❌ Não") . "</p>";
echo "<p>Permissão de escrita: " . (is_writable($upload_dir) ? "✅ Sim" : "❌ Não") . "</p>";

echo "<h3>Teste de Sessão</h3>";
session_name('sigilosas_acompanhante_session');
session_start();
echo "<p>ID da sessão: " . session_id() . "</p>";
echo "<p>acompanhante_id: " . ($_SESSION['acompanhante_id'] ?? 'Não definido') . "</p>";

echo "<h3>Teste de Banco de Dados</h3>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = getDB();
    echo "<p>✅ Conexão com banco OK</p>";
    
    // Verificar se a tabela existe
    $result = $db->query("SHOW TABLES LIKE 'videos_publicos'");
    echo "<p>Tabela videos_publicos: " . ($result ? "✅ Existe" : "❌ Não existe") . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro no banco: " . $e->getMessage() . "</p>";
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}
?> 