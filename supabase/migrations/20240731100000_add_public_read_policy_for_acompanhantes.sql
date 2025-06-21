-- Grant public read access to approved profiles in the 'acompanhantes' table.
-- This policy allows any user (anonymous or authenticated) to view profiles
-- that have been approved by an administrator, which is essential for the main
-- functionality of the public-facing site.
CREATE POLICY "Permitir acesso público para perfis aprovados"
ON public.acompanhantes
FOR SELECT
TO anon, authenticated
USING (status = 'aprovado'); 