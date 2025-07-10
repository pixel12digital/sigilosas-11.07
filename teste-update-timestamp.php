<?php
/**
 * Teste específico para ON UPDATE CURRENT_TIMESTAMP
 * Arquivo: teste-update-timestamp.php
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

echo "<h2>Teste ON UPDATE CURRENT_TIMESTAMP</h2>";

// 1. Buscar uma acompanhante pendente
$acompanhante_teste = $db->fetch("
    SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON a.estado_id = e.id
    WHERE a.status = 'pendente' AND a.cidade_id IS NOT NULL
    LIMIT 1
");

if (!$acompanhante_teste) {
    echo "<p style='color: red;'>Nenhuma acompanhante pendente encontrada.</p>";
    exit;
}

echo "<h3>Dados Antes do Teste:</h3>";
echo "<p>ID: " . $acompanhante_teste['id'] . "</p>";
echo "<p>Nome: " . $acompanhante_teste['nome'] . "</p>";
echo "<p>Status: " . $acompanhante_teste['status'] . "</p>";
echo "<p>Cidade ID: " . $acompanhante_teste['cidade_id'] . "</p>";
echo "<p>Cidade Nome: " . $acompanhante_teste['cidade_nome'] . "</p>";
echo "<p>Estado ID: " . $acompanhante_teste['estado_id'] . "</p>";
echo "<p>Estado UF: " . $acompanhante_teste['estado_uf'] . "</p>";
echo "<p>Updated At: " . $acompanhante_teste['updated_at'] . "</p>";

// 2. Teste 1: UPDATE sem updated_at
echo "<h3>Teste 1: UPDATE sem updated_at</h3>";
try {
    $resultado1 = $db->update('acompanhantes', [
        'status' => 'aprovado',
        'revisado_por' => 1,
        'data_revisao' => date('Y-m-d H:i:s'),
        'motivo_rejeicao' => null
    ], 'id = ?', [$acompanhante_teste['id']]);
    
    echo "<p style='color: green;'>✅ UPDATE sem updated_at executado. Linhas afetadas: " . $resultado1 . "</p>";
    
    // Verificar dados após
    $apos1 = $db->fetch("
        SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
        FROM acompanhantes a
        LEFT JOIN cidades c ON a.cidade_id = c.id
        LEFT JOIN estados e ON a.estado_id = e.id
        WHERE a.id = ?
    ", [$acompanhante_teste['id']]);
    
    echo "<p><strong>Dados após UPDATE sem updated_at:</strong></p>";
    echo "<p>Status: " . $apos1['status'] . "</p>";
    echo "<p>Cidade ID: " . ($apos1['cidade_id'] ?? 'NULL') . "</p>";
    echo "<p>Cidade Nome: " . ($apos1['cidade_nome'] ?? 'NULL') . "</p>";
    echo "<p>Estado ID: " . ($apos1['estado_id'] ?? 'NULL') . "</p>";
    echo "<p>Estado UF: " . ($apos1['estado_uf'] ?? 'NULL') . "</p>";
    echo "<p>Updated At: " . $apos1['updated_at'] . "</p>";
    
    if ($acompanhante_teste['cidade_id'] != $apos1['cidade_id']) {
        echo "<p style='color: red; font-weight: bold;'>❌ PROBLEMA: cidade_id mudou!</p>";
    } else {
        echo "<p style='color: green; font-weight: bold;'>✅ cidade_id permaneceu igual</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no UPDATE sem updated_at: " . $e->getMessage() . "</p>";
}

// 3. Voltar para pendente
echo "<h3>Voltando para pendente...</h3>";
$db->update('acompanhantes', [
    'status' => 'pendente',
    'revisado_por' => null,
    'data_revisao' => null,
    'motivo_rejeicao' => null
], 'id = ?', [$acompanhante_teste['id']]);

// 4. Teste 2: UPDATE com updated_at explícito
echo "<h3>Teste 2: UPDATE com updated_at explícito</h3>";
try {
    $resultado2 = $db->update('acompanhantes', [
        'status' => 'aprovado',
        'revisado_por' => 1,
        'data_revisao' => date('Y-m-d H:i:s'),
        'motivo_rejeicao' => null,
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$acompanhante_teste['id']]);
    
    echo "<p style='color: green;'>✅ UPDATE com updated_at executado. Linhas afetadas: " . $resultado2 . "</p>";
    
    // Verificar dados após
    $apos2 = $db->fetch("
        SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
        FROM acompanhantes a
        LEFT JOIN cidades c ON a.cidade_id = c.id
        LEFT JOIN estados e ON a.estado_id = e.id
        WHERE a.id = ?
    ", [$acompanhante_teste['id']]);
    
    echo "<p><strong>Dados após UPDATE com updated_at:</strong></p>";
    echo "<p>Status: " . $apos2['status'] . "</p>";
    echo "<p>Cidade ID: " . ($apos2['cidade_id'] ?? 'NULL') . "</p>";
    echo "<p>Cidade Nome: " . ($apos2['cidade_nome'] ?? 'NULL') . "</p>";
    echo "<p>Estado ID: " . ($apos2['estado_id'] ?? 'NULL') . "</p>";
    echo "<p>Estado UF: " . ($apos2['estado_uf'] ?? 'NULL') . "</p>";
    echo "<p>Updated At: " . $apos2['updated_at'] . "</p>";
    
    if ($acompanhante_teste['cidade_id'] != $apos2['cidade_id']) {
        echo "<p style='color: red; font-weight: bold;'>❌ PROBLEMA: cidade_id mudou!</p>";
    } else {
        echo "<p style='color: green; font-weight: bold;'>✅ cidade_id permaneceu igual</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no UPDATE com updated_at: " . $e->getMessage() . "</p>";
}

// 5. Voltar para pendente novamente
echo "<h3>Voltando para pendente novamente...</h3>";
$db->update('acompanhantes', [
    'status' => 'pendente',
    'revisado_por' => null,
    'data_revisao' => null,
    'motivo_rejeicao' => null
], 'id = ?', [$acompanhante_teste['id']]);

// 6. Teste 3: UPDATE direto via SQL
echo "<h3>Teste 3: UPDATE direto via SQL</h3>";
try {
    $sql = "UPDATE acompanhantes SET status = 'aprovado', revisado_por = 1, data_revisao = NOW(), motivo_rejeicao = NULL WHERE id = ?";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([$acompanhante_teste['id']]);
    
    echo "<p style='color: green;'>✅ UPDATE direto via SQL executado. Linhas afetadas: " . $stmt->rowCount() . "</p>";
    
    // Verificar dados após
    $apos3 = $db->fetch("
        SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
        FROM acompanhantes a
        LEFT JOIN cidades c ON a.cidade_id = c.id
        LEFT JOIN estados e ON a.estado_id = e.id
        WHERE a.id = ?
    ", [$acompanhante_teste['id']]);
    
    echo "<p><strong>Dados após UPDATE direto via SQL:</strong></p>";
    echo "<p>Status: " . $apos3['status'] . "</p>";
    echo "<p>Cidade ID: " . ($apos3['cidade_id'] ?? 'NULL') . "</p>";
    echo "<p>Cidade Nome: " . ($apos3['cidade_nome'] ?? 'NULL') . "</p>";
    echo "<p>Estado ID: " . ($apos3['estado_id'] ?? 'NULL') . "</p>";
    echo "<p>Estado UF: " . ($apos3['estado_uf'] ?? 'NULL') . "</p>";
    echo "<p>Updated At: " . $apos3['updated_at'] . "</p>";
    
    if ($acompanhante_teste['cidade_id'] != $apos3['cidade_id']) {
        echo "<p style='color: red; font-weight: bold;'>❌ PROBLEMA: cidade_id mudou!</p>";
    } else {
        echo "<p style='color: green; font-weight: bold;'>✅ cidade_id permaneceu igual</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no UPDATE direto via SQL: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Teste concluído.</strong></p>";
?> 