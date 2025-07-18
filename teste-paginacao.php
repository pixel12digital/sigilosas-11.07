<?php
/**
 * Teste de Paginação - Verificar se a API está retornando dados corretamente
 * Arquivo: teste-paginacao.php
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

// Testar com uma cidade específica (exemplo: São Paulo)
$estado_id = 25; // SP
$cidade_id = 3550308; // São Paulo

echo "<h2>Teste de Paginação - API busca-acompanhantes.php</h2>";

// Teste 1: Primeira página (6 resultados)
echo "<h3>Teste 1: Primeira página (limit=6, offset=0)</h3>";
$url1 = "http://localhost/Sigilosas-MySQL/api/busca-acompanhantes.php?estado_id={$estado_id}&cidade_id={$cidade_id}&limit=6&offset=0";
echo "<p>URL: <code>{$url1}</code></p>";

$response1 = file_get_contents($url1);
$data1 = json_decode($response1, true);

if ($data1) {
    echo "<p><strong>Total de acompanhantes:</strong> " . ($data1['pagination']['total'] ?? 'N/A') . "</p>";
    echo "<p><strong>Resultados retornados:</strong> " . count($data1['acompanhantes']) . "</p>";
    echo "<p><strong>Tem mais páginas:</strong> " . ($data1['pagination']['hasMore'] ? 'Sim' : 'Não') . "</p>";
    
    if (!empty($data1['acompanhantes'])) {
        echo "<p><strong>Primeiras 3 acompanhantes:</strong></p>";
        echo "<ul>";
        foreach (array_slice($data1['acompanhantes'], 0, 3) as $ac) {
            echo "<li>" . htmlspecialchars($ac['nome'] ?? $ac['apelido']) . " - " . ($ac['idade'] ?? 'N/A') . " anos</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>Erro ao carregar dados</p>";
}

// Teste 2: Segunda página (6 resultados)
echo "<h3>Teste 2: Segunda página (limit=6, offset=6)</h3>";
$url2 = "http://localhost/Sigilosas-MySQL/api/busca-acompanhantes.php?estado_id={$estado_id}&cidade_id={$cidade_id}&limit=6&offset=6";
echo "<p>URL: <code>{$url2}</code></p>";

$response2 = file_get_contents($url2);
$data2 = json_decode($response2, true);

if ($data2) {
    echo "<p><strong>Total de acompanhantes:</strong> " . ($data2['pagination']['total'] ?? 'N/A') . "</p>";
    echo "<p><strong>Resultados retornados:</strong> " . count($data2['acompanhantes']) . "</p>";
    echo "<p><strong>Tem mais páginas:</strong> " . ($data2['pagination']['hasMore'] ? 'Sim' : 'Não') . "</p>";
    
    if (!empty($data2['acompanhantes'])) {
        echo "<p><strong>Primeiras 3 acompanhantes da segunda página:</strong></p>";
        echo "<ul>";
        foreach (array_slice($data2['acompanhantes'], 0, 3) as $ac) {
            echo "<li>" . htmlspecialchars($ac['nome'] ?? $ac['apelido']) . " - " . ($ac['idade'] ?? 'N/A') . " anos</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>Erro ao carregar dados</p>";
}

// Teste 3: Verificar se não há duplicatas
echo "<h3>Teste 3: Verificar duplicatas entre páginas</h3>";
if ($data1 && $data2 && !empty($data1['acompanhantes']) && !empty($data2['acompanhantes'])) {
    $ids_pagina1 = array_column($data1['acompanhantes'], 'id');
    $ids_pagina2 = array_column($data2['acompanhantes'], 'id');
    $duplicatas = array_intersect($ids_pagina1, $ids_pagina2);
    
    if (empty($duplicatas)) {
        echo "<p style='color: green;'>✅ Não há duplicatas entre as páginas</p>";
    } else {
        echo "<p style='color: red;'>❌ Encontradas duplicatas: " . implode(', ', $duplicatas) . "</p>";
    }
}

// Teste 4: Verificar formato de resposta
echo "<h3>Teste 4: Estrutura da resposta</h3>";
if ($data1) {
    echo "<p><strong>Campos presentes na resposta:</strong></p>";
    echo "<ul>";
    echo "<li>acompanhantes: " . (isset($data1['acompanhantes']) ? '✅' : '❌') . "</li>";
    echo "<li>pagination: " . (isset($data1['pagination']) ? '✅' : '❌') . "</li>";
    echo "<li>pagination.total: " . (isset($data1['pagination']['total']) ? '✅' : '❌') . "</li>";
    echo "<li>pagination.limit: " . (isset($data1['pagination']['limit']) ? '✅' : '❌') . "</li>";
    echo "<li>pagination.offset: " . (isset($data1['pagination']['offset']) ? '✅' : '❌') . "</li>";
    echo "<li>pagination.hasMore: " . (isset($data1['pagination']['hasMore']) ? '✅' : '❌') . "</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Para testar no frontend:</strong></p>";
echo "<ol>";
echo "<li>Acesse a página inicial</li>";
echo "<li>Selecione um estado e cidade com muitas acompanhantes</li>";
echo "<li>Clique em 'Buscar'</li>";
echo "<li>Verifique se aparece o contador de resultados</li>";
echo "<li>Verifique se aparece o botão 'Ver mais' se houver mais de 6 resultados</li>";
echo "<li>Clique em 'Ver mais' para carregar mais 6 resultados</li>";
echo "</ol>";
?> 