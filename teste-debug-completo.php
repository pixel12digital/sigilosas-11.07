<?php
// Script de teste para verificar todos os debugs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>TESTE DE DEBUG COMPLETO</h1>";

// 1. Testar conexão com banco
echo "<h2>1. Teste de Conexão com Banco</h2>";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    echo "✅ Conexão com banco OK<br>";
    
    // Testar query simples
    $result = $db->fetch("SELECT COUNT(*) as total FROM acompanhantes");
    echo "✅ Query de teste OK - Total acompanhantes: " . $result['total'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
}

// 2. Testar sessão
echo "<h2>2. Teste de Sessão</h2>";
session_name('sigilosas_acompanhante_session');
session_start();

echo "Session ID: " . session_id() . "<br>";
echo "Session data: " . json_encode($_SESSION) . "<br>";

if (isset($_SESSION['acompanhante_id'])) {
    echo "✅ Acompanhante logada - ID: " . $_SESSION['acompanhante_id'] . "<br>";
} else {
    echo "❌ Nenhuma acompanhante logada<br>";
}

// 3. Testar tabelas
echo "<h2>3. Teste de Tabelas</h2>";

$tabelas = [
    'acompanhantes',
    'horarios_atendimento', 
    'videos_publicos',
    'videos_verificacao',
    'fotos',
    'documentos_acompanhante',
    'valores_atendimento'
];

foreach ($tabelas as $tabela) {
    try {
        $result = $db->query("SHOW TABLES LIKE '$tabela'");
        if ($result) {
            $count = $db->fetch("SELECT COUNT(*) as total FROM $tabela");
            echo "✅ Tabela $tabela existe - " . $count['total'] . " registros<br>";
        } else {
            echo "❌ Tabela $tabela NÃO existe<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erro ao verificar tabela $tabela: " . $e->getMessage() . "<br>";
    }
}

// 4. Testar diretórios de upload
echo "<h2>4. Teste de Diretórios</h2>";

$diretorios = [
    'uploads/',
    'uploads/videos_publicos/',
    'uploads/videos_verificacao/',
    'uploads/galeria/',
    'uploads/documentos/'
];

foreach ($diretorios as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        $writable = is_writable($path) ? 'gravável' : 'não gravável';
        echo "✅ Diretório $dir existe - $writable<br>";
    } else {
        echo "❌ Diretório $dir NÃO existe<br>";
    }
}

// 5. Testar APIs
echo "<h2>5. Teste de APIs</h2>";

$apis = [
    '/api/cidades.php',
    '/api/upload-video-publico.php',
    '/api/get-videos-publicos.php'
];

foreach ($apis as $api) {
    $path = __DIR__ . $api;
    if (file_exists($path)) {
        echo "✅ API $api existe<br>";
    } else {
        echo "❌ API $api NÃO existe<br>";
    }
}

// 6. Testar arquivo de log
echo "<h2>6. Teste de Log</h2>";
$log_file = ini_get('error_log');
if ($log_file) {
    echo "Arquivo de log: $log_file<br>";
    if (is_writable($log_file)) {
        echo "✅ Arquivo de log gravável<br>";
    } else {
        echo "❌ Arquivo de log não gravável<br>";
    }
} else {
    echo "❌ Arquivo de log não configurado<br>";
}

// 7. Testar se há acompanhantes no sistema
echo "<h2>7. Teste de Dados</h2>";
if (isset($_SESSION['acompanhante_id'])) {
    $acompanhante = $db->fetch("SELECT * FROM acompanhantes WHERE id = ?", [$_SESSION['acompanhante_id']]);
    if ($acompanhante) {
        echo "✅ Acompanhante encontrada no banco<br>";
        echo "Nome: " . $acompanhante['nome'] . "<br>";
        echo "Cidade ID: " . $acompanhante['cidade_id'] . "<br>";
        echo "Estado ID: " . $acompanhante['estado_id'] . "<br>";
    } else {
        echo "❌ Acompanhante não encontrada no banco<br>";
    }
}

// 8. Testar horários
if (isset($_SESSION['acompanhante_id'])) {
    $horarios = $db->fetchAll("SELECT * FROM horarios_atendimento WHERE acompanhante_id = ?", [$_SESSION['acompanhante_id']]);
    echo "Horários encontrados: " . count($horarios) . "<br>";
}

echo "<h2>FIM DO TESTE</h2>";
echo "<p>Verifique o arquivo de log do PHP para ver os debugs detalhados.</p>";
?> 