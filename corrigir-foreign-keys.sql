-- Script para corrigir as foreign keys da tabela acompanhantes
-- Arquivo: corrigir-foreign-keys.sql

-- 1. Remover as foreign keys existentes
ALTER TABLE acompanhantes 
DROP FOREIGN KEY IF EXISTS acompanhantes_ibfk_1,
DROP FOREIGN KEY IF EXISTS acompanhantes_ibfk_2;

-- 2. Adicionar as foreign keys com RESTRICT em vez de SET NULL
ALTER TABLE acompanhantes
ADD CONSTRAINT fk_acompanhantes_cidade 
FOREIGN KEY (cidade_id) REFERENCES cidades(id) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_acompanhantes_estado 
FOREIGN KEY (estado_id) REFERENCES estados(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- 3. Verificar se há registros com cidade_id ou estado_id NULL que não deveriam ter
SELECT 
    COUNT(*) as total_com_cidade_null,
    COUNT(CASE WHEN cidade_id IS NULL THEN 1 END) as cidade_null,
    COUNT(CASE WHEN estado_id IS NULL THEN 1 END) as estado_null
FROM acompanhantes;

-- 4. Verificar se há registros com cidade_id ou estado_id que não existem nas tabelas referenciadas
SELECT 
    COUNT(*) as total_invalidos,
    COUNT(CASE WHEN cidade_id IS NOT NULL AND cidade_id NOT IN (SELECT id FROM cidades) THEN 1 END) as cidade_invalida,
    COUNT(CASE WHEN estado_id IS NOT NULL AND estado_id NOT IN (SELECT id FROM estados) THEN 1 END) as estado_invalido
FROM acompanhantes;

-- 5. Mostrar registros com problemas
SELECT 
    id, nome, cidade_id, estado_id, status
FROM acompanhantes 
WHERE cidade_id IS NOT NULL AND cidade_id NOT IN (SELECT id FROM cidades)
   OR estado_id IS NOT NULL AND estado_id NOT IN (SELECT id FROM estados);

-- 6. Verificar se há cidades ou estados órfãos
SELECT 
    'Cidades órfãs' as tipo,
    COUNT(*) as total
FROM cidades c
WHERE NOT EXISTS (SELECT 1 FROM acompanhantes a WHERE a.cidade_id = c.id)
UNION ALL
SELECT 
    'Estados órfãos' as tipo,
    COUNT(*) as total
FROM estados e
WHERE NOT EXISTS (SELECT 1 FROM acompanhantes a WHERE a.estado_id = e.id);

-- 7. Criar índices para melhorar performance
CREATE INDEX IF NOT EXISTS idx_acompanhantes_cidade_status ON acompanhantes(cidade_id, status);
CREATE INDEX IF NOT EXISTS idx_acompanhantes_estado_status ON acompanhantes(estado_id, status);

-- 8. Verificar a estrutura final
DESCRIBE acompanhantes;

-- 9. Mostrar as foreign keys criadas
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
AND REFERENCED_TABLE_NAME IS NOT NULL; 