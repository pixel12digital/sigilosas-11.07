-- Função para criar a tabela admin
CREATE OR REPLACE FUNCTION create_admin_table()
RETURNS void
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
    -- Criar tabela de administradores se não existir
    CREATE TABLE IF NOT EXISTS admin (
        id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
        usuario VARCHAR(50) NOT NULL UNIQUE,
        senha TEXT NOT NULL,
        email VARCHAR(255),
        nome VARCHAR(100),
        ativo BOOLEAN DEFAULT true,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
        updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
    );

    -- Criar índices
    CREATE INDEX IF NOT EXISTS idx_admin_usuario ON admin(usuario);
    CREATE INDEX IF NOT EXISTS idx_admin_email ON admin(email);

    -- Criar trigger para updated_at
    DROP TRIGGER IF EXISTS update_admin_updated_at ON admin;
    CREATE TRIGGER update_admin_updated_at
        BEFORE UPDATE ON admin
        FOR EACH ROW
        EXECUTE FUNCTION update_updated_at();

    -- Habilitar RLS
    ALTER TABLE admin ENABLE ROW LEVEL SECURITY;

    -- Criar políticas de acesso
    DROP POLICY IF EXISTS "Administradores podem ver outros administradores" ON admin;
    CREATE POLICY "Administradores podem ver outros administradores" ON admin
        FOR SELECT USING (
            auth.role() = 'authenticated' AND 
            EXISTS (
                SELECT 1 FROM admin a
                WHERE a.usuario = auth.email()
                AND a.ativo = true
            )
        );

    DROP POLICY IF EXISTS "Administradores podem atualizar outros administradores" ON admin;
    CREATE POLICY "Administradores podem atualizar outros administradores" ON admin
        FOR UPDATE USING (
            auth.role() = 'authenticated' AND 
            EXISTS (
                SELECT 1 FROM admin a
                WHERE a.usuario = auth.email()
                AND a.ativo = true
            )
        );
END;
$$; 