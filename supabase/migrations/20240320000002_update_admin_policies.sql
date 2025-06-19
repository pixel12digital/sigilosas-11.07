-- Remover políticas existentes
DROP POLICY IF EXISTS "Permitir atualização por administradores" ON acompanhantes;

-- Criar nova política para permitir que administradores atualizem o status
CREATE POLICY "Permitir atualização por administradores"
ON acompanhantes
FOR UPDATE
USING (
    EXISTS (
        SELECT 1 FROM usuarios
        WHERE usuarios.id = auth.uid()
        AND usuarios.tipo = 'admin'
    )
)
WITH CHECK (
    EXISTS (
        SELECT 1 FROM usuarios
        WHERE usuarios.id = auth.uid()
        AND usuarios.tipo = 'admin'
    )
);

-- Garantir que a tabela está habilitada para RLS
ALTER TABLE acompanhantes ENABLE ROW LEVEL SECURITY;

-- Permitir que administradores vejam todos os registros
CREATE POLICY "Permitir visualização por administradores"
ON acompanhantes
FOR SELECT
USING (
    EXISTS (
        SELECT 1 FROM usuarios
        WHERE usuarios.id = auth.uid()
        AND usuarios.tipo = 'admin'
    )
); 