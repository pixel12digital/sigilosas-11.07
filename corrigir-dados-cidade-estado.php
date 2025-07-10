<?php
/**
 * Script para verificar e corrigir dados com cidade_id ou estado_id NULL
 * Arquivo: corrigir-dados-cidade-estado.php
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

echo "<h2>Verificação e Correção de Dados - Cidade e Estado</h2>";

// 1. Verificar estatísticas gerais
echo "<h3>1. Estatísticas Gerais</h3>";
$stats = $db->fetch("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN cidade_id IS NULL THEN 1 END) as cidade_null,
        COUNT(CASE WHEN estado_id IS NULL THEN 1 END) as estado_null,
        COUNT(CASE WHEN cidade_id IS NOT NULL AND estado_id IS NOT NULL THEN 1 END) as ambos_preenchidos,
        COUNT(CASE WHEN cidade_id IS NOT NULL AND estado_id IS NULL THEN 1 END) as so_cidade,
        COUNT(CASE WHEN cidade_id IS NULL AND estado_id IS NOT NULL THEN 1 END) as so_estado
    FROM acompanhantes
");

echo "<p><strong>Total de acompanhantes:</strong> " . $stats['total'] . "</p>";
echo "<p><strong>Com cidade_id NULL:</strong> " . $stats['cidade_null'] . "</p>";
echo "<p><strong>Com estado_id NULL:</strong> " . $stats['estado_null'] . "</p>";
echo "<p><strong>Com ambos preenchidos:</strong> " . $stats['ambos_preenchidos'] . "</p>";
echo "<p><strong>Só cidade preenchida:</strong> " . $stats['so_cidade'] . "</p>";
echo "<p><strong>Só estado preenchido:</strong> " . $stats['so_estado'] . "</p>";

// 2. Verificar registros com cidade_id inválido
echo "<h3>2. Registros com cidade_id inválido</h3>";
$cidades_invalidas = $db->fetchAll("
    SELECT id, nome, cidade_id, estado_id, status, created_at
    FROM acompanhantes 
    WHERE cidade_id IS NOT NULL AND cidade_id NOT IN (SELECT id FROM cidades)
    ORDER BY created_at DESC
");

if (empty($cidades_invalidas)) {
    echo "<p style='color: green;'>✅ Nenhum registro com cidade_id inválido encontrado.</p>";
} else {
    echo "<p style='color: red;'>❌ Encontrados " . count($cidades_invalidas) . " registros com cidade_id inválido:</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Cidade ID</th><th>Estado ID</th><th>Status</th><th>Data Cadastro</th></tr>";
    foreach ($cidades_invalidas as $reg) {
        echo "<tr>";
        echo "<td>" . $reg['id'] . "</td>";
        echo "<td>" . htmlspecialchars($reg['nome']) . "</td>";
        echo "<td>" . $reg['cidade_id'] . "</td>";
        echo "<td>" . $reg['estado_id'] . "</td>";
        echo "<td>" . $reg['status'] . "</td>";
        echo "<td>" . $reg['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. Verificar registros com estado_id inválido
echo "<h3>3. Registros com estado_id inválido</h3>";
$estados_invalidos = $db->fetchAll("
    SELECT id, nome, cidade_id, estado_id, status, created_at
    FROM acompanhantes 
    WHERE estado_id IS NOT NULL AND estado_id NOT IN (SELECT id FROM estados)
    ORDER BY created_at DESC
");

if (empty($estados_invalidos)) {
    echo "<p style='color: green;'>✅ Nenhum registro com estado_id inválido encontrado.</p>";
} else {
    echo "<p style='color: red;'>❌ Encontrados " . count($estados_invalidos) . " registros com estado_id inválido:</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Cidade ID</th><th>Estado ID</th><th>Status</th><th>Data Cadastro</th></tr>";
    foreach ($estados_invalidos as $reg) {
        echo "<tr>";
        echo "<td>" . $reg['id'] . "</td>";
        echo "<td>" . htmlspecialchars($reg['nome']) . "</td>";
        echo "<td>" . $reg['cidade_id'] . "</td>";
        echo "<td>" . $reg['estado_id'] . "</td>";
        echo "<td>" . $reg['status'] . "</td>";
        echo "<td>" . $reg['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Verificar registros aprovados com cidade_id NULL
echo "<h3>4. Registros aprovados com cidade_id NULL</h3>";
$aprovados_sem_cidade = $db->fetchAll("
    SELECT id, nome, cidade_id, estado_id, status, created_at, updated_at
    FROM acompanhantes 
    WHERE status = 'aprovado' AND cidade_id IS NULL
    ORDER BY updated_at DESC
");

if (empty($aprovados_sem_cidade)) {
    echo "<p style='color: green;'>✅ Nenhum registro aprovado sem cidade encontrado.</p>";
} else {
    echo "<p style='color: red;'>❌ Encontrados " . count($aprovados_sem_cidade) . " registros aprovados sem cidade:</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Cidade ID</th><th>Estado ID</th><th>Status</th><th>Data Aprovação</th></tr>";
    foreach ($aprovados_sem_cidade as $reg) {
        echo "<tr>";
        echo "<td>" . $reg['id'] . "</td>";
        echo "<td>" . htmlspecialchars($reg['nome']) . "</td>";
        echo "<td>" . ($reg['cidade_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($reg['estado_id'] ?? 'NULL') . "</td>";
        echo "<td>" . $reg['status'] . "</td>";
        echo "<td>" . $reg['updated_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 5. Verificar se há cidades ou estados órfãos
echo "<h3>5. Cidades e Estados órfãos</h3>";
$cidades_orfas = $db->fetchAll("
    SELECT c.id, c.nome, e.uf as estado_uf
    FROM cidades c
    LEFT JOIN estados e ON c.estado_id = e.id
    WHERE NOT EXISTS (SELECT 1 FROM acompanhantes a WHERE a.cidade_id = c.id)
    ORDER BY c.nome
");

$estados_orfos = $db->fetchAll("
    SELECT id, nome, uf
    FROM estados
    WHERE NOT EXISTS (SELECT 1 FROM acompanhantes a WHERE a.estado_id = id)
    ORDER BY nome
");

echo "<p><strong>Cidades órfãs:</strong> " . count($cidades_orfas) . "</p>";
echo "<p><strong>Estados órfãos:</strong> " . count($estados_orfos) . "</p>";

// 6. Verificar foreign keys atuais
echo "<h3>6. Foreign Keys Atuais</h3>";
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

// 7. Sugestões de correção
echo "<h3>7. Sugestões de Correção</h3>";

if (!empty($cidades_invalidas) || !empty($estados_invalidos)) {
    echo "<p style='color: orange;'>⚠️ <strong>Ação necessária:</strong> Existem registros com IDs de cidade ou estado inválidos.</p>";
    echo "<p>Execute o script <code>corrigir-foreign-keys.sql</code> para corrigir as foreign keys.</p>";
}

if (!empty($aprovados_sem_cidade)) {
    echo "<p style='color: orange;'>⚠️ <strong>Ação necessária:</strong> Existem registros aprovados sem cidade.</p>";
    echo "<p>Isso pode indicar um problema no processo de aprovação.</p>";
}

if (empty($foreign_keys)) {
    echo "<p style='color: red;'>❌ <strong>Ação necessária:</strong> Não há foreign keys definidas.</p>";
    echo "<p>Execute o script <code>corrigir-foreign-keys.sql</code> para adicionar as foreign keys.</p>";
}

echo "<hr>";
echo "<p><strong>Verificação concluída.</strong></p>";
?> 