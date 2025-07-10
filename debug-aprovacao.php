<?php
/**
 * Script de Debug Detalhado para Problema de Aprovação
 * Arquivo: debug-aprovacao.php
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

echo "<h2>Debug Detalhado - Problema de Aprovação</h2>";

// 1. Verificar se há triggers na tabela
echo "<h3>1. Verificando Triggers</h3>";
$triggers = $db->fetchAll("SHOW TRIGGERS WHERE `Table` = 'acompanhantes'");
if (empty($triggers)) {
    echo "<p style='color: green;'>✅ Nenhum trigger encontrado na tabela acompanhantes</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Triggers encontrados na tabela acompanhantes:</p>";
    foreach ($triggers as $trigger) {
        echo "<p>- " . $trigger['Trigger'] . " (" . $trigger['Timing'] . " " . $trigger['Event'] . ")</p>";
        echo "<p>Statement: " . $trigger['Statement'] . "</p>";
    }
}

// 2. Verificar foreign keys
echo "<h3>2. Verificando Foreign Keys</h3>";
$foreign_keys = $db->fetchAll("
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME,
        DELETE_RULE,
        UPDATE_RULE
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'acompanhantes' 
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

if (empty($foreign_keys)) {
    echo "<p style='color: red;'>❌ Nenhuma foreign key encontrada na tabela acompanhantes!</p>";
} else {
    echo "<p style='color: green;'>✅ Foreign keys encontradas:</p>";
    foreach ($foreign_keys as $fk) {
        echo "<p><strong>" . $fk['COLUMN_NAME'] . "</strong> → " . $fk['REFERENCED_TABLE_NAME'] . "." . $fk['REFERENCED_COLUMN_NAME'] . " (ON DELETE " . $fk['DELETE_RULE'] . ", ON UPDATE " . $fk['UPDATE_RULE'] . ")</p>";
    }
}

// 3. Buscar uma acompanhante pendente para teste
echo "<h3>3. Buscando Acompanhante para Teste</h3>";
$acompanhante_teste = $db->fetch("
    SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON a.estado_id = e.id
    WHERE a.status = 'pendente' AND a.cidade_id IS NOT NULL
    LIMIT 1
");

if (!$acompanhante_teste) {
    echo "<p style='color: red;'>Nenhuma acompanhante pendente encontrada para teste.</p>";
    exit;
}

echo "<p><strong>Acompanhante selecionada:</strong></p>";
echo "<p>ID: " . $acompanhante_teste['id'] . "</p>";
echo "<p>Nome: " . $acompanhante_teste['nome'] . "</p>";
echo "<p>Status: " . $acompanhante_teste['status'] . "</p>";
echo "<p>Cidade ID: " . $acompanhante_teste['cidade_id'] . "</p>";
echo "<p>Cidade Nome: " . $acompanhante_teste['cidade_nome'] . "</p>";
echo "<p>Estado ID: " . $acompanhante_teste['estado_id'] . "</p>";
echo "<p>Estado UF: " . $acompanhante_teste['estado_uf'] . "</p>";

// 4. Verificar se a cidade existe
echo "<h3>4. Verificando Existência da Cidade</h3>";
$cidade_existe = $db->fetch("SELECT id, nome FROM cidades WHERE id = ?", [$acompanhante_teste['cidade_id']]);
if ($cidade_existe) {
    echo "<p style='color: green;'>✅ Cidade existe: " . $cidade_existe['nome'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ PROBLEMA: Cidade com ID " . $acompanhante_teste['cidade_id'] . " não existe!</p>";
}

// 5. Verificar se o estado existe
echo "<h3>5. Verificando Existência do Estado</h3>";
$estado_existe = $db->fetch("SELECT id, nome FROM estados WHERE id = ?", [$acompanhante_teste['estado_id']]);
if ($estado_existe) {
    echo "<p style='color: green;'>✅ Estado existe: " . $estado_existe['nome'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ PROBLEMA: Estado com ID " . $acompanhante_teste['estado_id'] . " não existe!</p>";
}

// 6. Testar UPDATE direto no banco
echo "<h3>6. Testando UPDATE Direto</h3>";
try {
    // Primeiro, vamos ver a query que será executada
    $updateData = [
        'status' => 'aprovado',
        'revisado_por' => 1,
        'data_revisao' => date('Y-m-d H:i:s'),
        'motivo_rejeicao' => null,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $fields = array_keys($updateData);
    $set = implode(' = ?, ', $fields) . ' = ?';
    $sql = "UPDATE acompanhantes SET $set WHERE id = ?";
    
    echo "<p><strong>Query que será executada:</strong></p>";
    echo "<p><code>" . $sql . "</code></p>";
    echo "<p><strong>Parâmetros:</strong></p>";
    echo "<p><code>" . implode(', ', array_values($updateData)) . ", " . $acompanhante_teste['id'] . "</code></p>";
    
    // Executar o UPDATE
    $resultado = $db->update('acompanhantes', $updateData, 'id = ?', [$acompanhante_teste['id']]);
    
    echo "<p style='color: green;'>✅ UPDATE executado. Linhas afetadas: " . $resultado . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no UPDATE: " . $e->getMessage() . "</p>";
    exit;
}

// 7. Verificar dados após o UPDATE
echo "<h3>7. Verificando Dados Após UPDATE</h3>";
$acompanhante_apos = $db->fetch("
    SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON a.estado_id = e.id
    WHERE a.id = ?
", [$acompanhante_teste['id']]);

echo "<p><strong>Dados após UPDATE:</strong></p>";
echo "<p>ID: " . $acompanhante_apos['id'] . "</p>";
echo "<p>Nome: " . $acompanhante_apos['nome'] . "</p>";
echo "<p>Status: " . $acompanhante_apos['status'] . "</p>";
echo "<p>Cidade ID: " . ($acompanhante_apos['cidade_id'] ?? 'NULL') . "</p>";
echo "<p>Cidade Nome: " . ($acompanhante_apos['cidade_nome'] ?? 'NULL') . "</p>";
echo "<p>Estado ID: " . ($acompanhante_apos['estado_id'] ?? 'NULL') . "</p>";
echo "<p>Estado UF: " . ($acompanhante_apos['estado_uf'] ?? 'NULL') . "</p>";

// 8. Verificar se houve mudança
if ($acompanhante_teste['cidade_id'] != $acompanhante_apos['cidade_id']) {
    echo "<p style='color: red; font-weight: bold;'>❌ PROBLEMA DETECTADO: cidade_id mudou de " . $acompanhante_teste['cidade_id'] . " para " . ($acompanhante_apos['cidade_id'] ?? 'NULL') . "</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>✅ cidade_id permaneceu igual: " . $acompanhante_apos['cidade_id'] . "</p>";
}

if ($acompanhante_teste['estado_id'] != $acompanhante_apos['estado_id']) {
    echo "<p style='color: red; font-weight: bold;'>❌ PROBLEMA DETECTADO: estado_id mudou de " . $acompanhante_teste['estado_id'] . " para " . ($acompanhante_apos['estado_id'] ?? 'NULL') . "</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>✅ estado_id permaneceu igual: " . $acompanhante_apos['estado_id'] . "</p>";
}

// 9. Verificar se há algum problema com a foreign key
echo "<h3>9. Verificando Problemas com Foreign Key</h3>";
if ($acompanhante_apos['cidade_id'] && !$cidade_existe) {
    echo "<p style='color: red;'>❌ PROBLEMA: cidade_id aponta para cidade que não existe!</p>";
}

if ($acompanhante_apos['estado_id'] && !$estado_existe) {
    echo "<p style='color: red;'>❌ PROBLEMA: estado_id aponta para estado que não existe!</p>";
}

// 10. Verificar se há algum problema com a constraint
echo "<h3>10. Verificando Constraints</h3>";
$constraints = $db->fetchAll("
    SELECT 
        CONSTRAINT_NAME,
        CONSTRAINT_TYPE
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'acompanhantes'
");

foreach ($constraints as $constraint) {
    echo "<p><strong>" . $constraint['CONSTRAINT_NAME'] . "</strong> - " . $constraint['CONSTRAINT_TYPE'] . "</p>";
}

// 11. Verificar se há algum problema com o trigger de updated_at
echo "<h3>11. Verificando Trigger de updated_at</h3>";
$triggers_updated = $db->fetchAll("SHOW TRIGGERS WHERE `Table` = 'acompanhantes' AND `Trigger` LIKE '%updated%'");
if (empty($triggers_updated)) {
    echo "<p style='color: green;'>✅ Nenhum trigger de updated_at encontrado</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Triggers de updated_at encontrados:</p>";
    foreach ($triggers_updated as $trigger) {
        echo "<p>- " . $trigger['Trigger'] . " (" . $trigger['Timing'] . " " . $trigger['Event'] . ")</p>";
        echo "<p>Statement: " . $trigger['Statement'] . "</p>";
    }
}

echo "<hr>";
echo "<p><strong>Debug concluído.</strong></p>";
?> 