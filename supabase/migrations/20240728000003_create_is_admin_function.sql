-- Este script cria a função is_admin() que é essencial para as políticas de segurança.
-- A função verifica se o ID do usuário autenticado existe na tabela de administradores.

CREATE OR REPLACE FUNCTION public.is_admin()
RETURNS boolean AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1
        FROM public.admins
        WHERE id = auth.uid()
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER; 