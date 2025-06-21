-- Remove a política antiga se existir, para evitar conflitos.
DROP POLICY IF EXISTS "Enable full access for service_role" ON "public"."acompanhantes";

-- Adiciona uma nova política que concede acesso total à tabela 'acompanhantes'
-- para usuários autenticados com a 'service_role'.
-- Isso é essencial para que as operações do lado do servidor (como no painel de admin) funcionem.
CREATE POLICY "Enable full access for service_role"
ON "public"."acompanhantes"
FOR ALL
TO service_role
USING (true)
WITH CHECK (true); 