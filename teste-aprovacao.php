<?php
/**
 * Script de teste para verificar o problema de cidade_id ficando NULL na aprovação
 * Arquivo: teste-aprovacao.php
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

echo "<h2>Teste de Aprovação de Acompanhantes</h2>";

// 1. Buscar uma acompanhante pendente para teste
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

echo "<h3>Dados da Acompanhante Antes da Aprovação:</h3>";
echo "<p><strong>ID:</strong> " . $acompanhante_teste['id'] . "</p>";
echo "<p><strong>Nome:</strong> " . $acompanhante_teste['nome'] . "</p>";
echo "<p><strong>Status:</strong> " . $acompanhante_teste['status'] . "</p>";
echo "<p><strong>Cidade ID:</strong> " . $acompanhante_teste['cidade_id'] . "</p>";
echo "<p><strong>Cidade Nome:</strong> " . $acompanhante_teste['cidade_nome'] . "</p>";
echo "<p><strong>Estado ID:</strong> " . $acompanhante_teste['estado_id'] . "</p>";
echo "<p><strong>Estado UF:</strong> " . $acompanhante_teste['estado_uf'] . "</p>";

// 2. Simular a aprovação
echo "<h3>Simulando Aprovação...</h3>";

try {
    $resultado = $db->update('acompanhantes', [
        'status' => 'aprovado',
        'revisado_por' => 1,
        'data_revisao' => date('Y-m-d H:i:s'),
        'motivo_rejeicao' => null,
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$acompanhante_teste['id']]);
    
    echo "<p style='color: green;'>Aprovação executada. Linhas afetadas: " . $resultado . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro na aprovação: " . $e->getMessage() . "</p>";
    exit;
}

// 3. Verificar os dados após a aprovação
$acompanhante_apos = $db->fetch("
    SELECT a.*, c.nome as cidade_nome, e.uf as estado_uf
    FROM acompanhantes a
    LEFT JOIN cidades c ON a.cidade_id = c.id
    LEFT JOIN estados e ON a.estado_id = e.id
    WHERE a.id = ?
", [$acompanhante_teste['id']]);

echo "<h3>Dados da Acompanhante Após a Aprovação:</h3>";
echo "<p><strong>ID:</strong> " . $acompanhante_apos['id'] . "</p>";
echo "<p><strong>Nome:</strong> " . $acompanhante_apos['nome'] . "</p>";
echo "<p><strong>Status:</strong> " . $acompanhante_apos['status'] . "</p>";
echo "<p><strong>Cidade ID:</strong> " . $acompanhante_apos['cidade_id'] . "</p>";
echo "<p><strong>Cidade Nome:</strong> " . $acompanhante_apos['cidade_nome'] . "</p>";
echo "<p><strong>Estado ID:</strong> " . $acompanhante_apos['estado_id'] . "</p>";
echo "<p><strong>Estado UF:</strong> " . $acompanhante_apos['estado_uf'] . "</p>";

// 4. Verificar se houve mudança
if ($acompanhante_teste['cidade_id'] != $acompanhante_apos['cidade_id']) {
    echo "<p style='color: red; font-weight: bold;'>❌ PROBLEMA DETECTADO: cidade_id mudou de " . $acompanhante_teste['cidade_id'] . " para " . $acompanhante_apos['cidade_id'] . "</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>✅ cidade_id permaneceu igual: " . $acompanhante_apos['cidade_id'] . "</p>";
}

if ($acompanhante_teste['estado_id'] != $acompanhante_apos['estado_id']) {
    echo "<p style='color: red; font-weight: bold;'>❌ PROBLEMA DETECTADO: estado_id mudou de " . $acompanhante_teste['estado_id'] . " para " . $acompanhante_apos['estado_id'] . "</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>✅ estado_id permaneceu igual: " . $acompanhante_apos['estado_id'] . "</p>";
}

// 5. Verificar se a cidade ainda existe
if ($acompanhante_apos['cidade_id']) {
    $cidade_existe = $db->fetch("SELECT id, nome FROM cidades WHERE id = ?", [$acompanhante_apos['cidade_id']]);
    if ($cidade_existe) {
        echo "<p style='color: green;'>✅ Cidade existe no banco: " . $cidade_existe['nome'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ PROBLEMA: Cidade com ID " . $acompanhante_apos['cidade_id'] . " não existe mais no banco!</p>";
    }
}

// 6. Verificar se o estado ainda existe
if ($acompanhante_apos['estado_id']) {
    $estado_existe = $db->fetch("SELECT id, nome FROM estados WHERE id = ?", [$acompanhante_apos['estado_id']]);
    if ($estado_existe) {
        echo "<p style='color: green;'>✅ Estado existe no banco: " . $estado_existe['nome'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ PROBLEMA: Estado com ID " . $acompanhante_apos['estado_id'] . " não existe mais no banco!</p>";
    }
}

// 7. Verificar se há triggers na tabela
echo "<h3>Verificando Triggers:</h3>";
$triggers = $db->fetchAll("SHOW TRIGGERS WHERE `Table` = 'acompanhantes'");
if (empty($triggers)) {
    echo "<p style='color: green;'>✅ Nenhum trigger encontrado na tabela acompanhantes</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Triggers encontrados na tabela acompanhantes:</p>";
    foreach ($triggers as $trigger) {
        echo "<p>- " . $trigger['Trigger'] . " (" . $trigger['Timing'] . " " . $trigger['Event'] . ")</p>";
    }
}

// 8. Verificar foreign keys
echo "<h3>Verificando Foreign Keys:</h3>";
$foreign_keys = $db->fetchAll("
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME,
        DELETE_RULE
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'acompanhantes' 
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

foreach ($foreign_keys as $fk) {
    echo "<p><strong>" . $fk['COLUMN_NAME'] . "</strong> → " . $fk['REFERENCED_TABLE_NAME'] . "." . $fk['REFERENCED_COLUMN_NAME'] . " (ON DELETE " . $fk['DELETE_RULE'] . ")</p>";
}

echo "<hr>";
echo "<p><strong>Teste concluído.</strong></p>";
?> 