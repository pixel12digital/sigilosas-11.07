-- Este script cria a tabela 'admins', essencial para a verificação de permissões de administrador.
CREATE TABLE public.admins (
    id UUID PRIMARY KEY,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT fk_admins_auth_users
        FOREIGN KEY(id)
        REFERENCES auth.users(id)
        ON DELETE CASCADE
);

-- Habilita a segurança em nível de linha para a nova tabela
ALTER TABLE public.admins ENABLE ROW LEVEL SECURITY;

-- Permite que administradores vejam outros administradores (opcional, mas bom para gerenciamento)
CREATE POLICY "Enable read access for admins"
ON public.admins
FOR SELECT
USING (public.is_admin()); 