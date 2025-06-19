-- Criar tabela de administradores
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
CREATE TRIGGER update_admin_updated_at
    BEFORE UPDATE ON admin
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

-- Criar políticas de acesso
ALTER TABLE admin ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Administradores podem ver outros administradores" ON admin
    FOR SELECT USING (
        auth.role() = 'authenticated' AND 
        EXISTS (
            SELECT 1 FROM admin a
            WHERE a.usuario = auth.email()
            AND a.ativo = true
        )
    );

CREATE POLICY "Administradores podem atualizar outros administradores" ON admin
    FOR UPDATE USING (
        auth.role() = 'authenticated' AND 
        EXISTS (
            SELECT 1 FROM admin a
            WHERE a.usuario = auth.email()
            AND a.ativo = true
        )
    );

-- Inserir admin padrão (senha: admin123)
INSERT INTO admin (usuario, senha, email, nome)
VALUES ('admin', '$2a$10$X7VqG0sL5Ot5.YfRxG5H5eM5M.FZO5mGHH3tG1TrH5GY.JH4rH2Hy', 'admin@sigilosas.com.br', 'Administrador')
ON CONFLICT (usuario) DO NOTHING; 