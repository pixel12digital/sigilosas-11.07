-- Tabela de administradores
CREATE TABLE admin (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    nivel_acesso VARCHAR(20) DEFAULT 'admin',
    ativo BOOLEAN DEFAULT true,
    ultimo_acesso TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de log de ações do admin
CREATE TABLE admin_log (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    admin_id UUID REFERENCES admin(id),
    acao VARCHAR(50) NOT NULL,
    entidade VARCHAR(50) NOT NULL,
    entidade_id UUID NOT NULL,
    detalhes JSONB,
    ip_address TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de denúncias
CREATE TABLE denuncias (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    tipo_denuncia VARCHAR(50) NOT NULL,
    descricao TEXT,
    status VARCHAR(20) DEFAULT 'pendente',
    resolvido_por UUID REFERENCES admin(id),
    resolucao TEXT,
    ip_denunciante TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de notas administrativas
CREATE TABLE notas_admin (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    admin_id UUID REFERENCES admin(id),
    nota TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Funções administrativas
CREATE OR REPLACE FUNCTION aprovar_acompanhante(
    acompanhante_id UUID,
    admin_id UUID
) RETURNS void AS $$
BEGIN
    -- Atualiza status do acompanhante
    UPDATE acompanhantes 
    SET status = 'aprovado',
        updated_at = NOW()
    WHERE id = acompanhante_id;

    -- Registra ação no log
    INSERT INTO admin_log (admin_id, acao, entidade, entidade_id, detalhes)
    VALUES (
        admin_id,
        'aprovar',
        'acompanhante',
        acompanhante_id,
        jsonb_build_object('status', 'aprovado')
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

CREATE OR REPLACE FUNCTION rejeitar_acompanhante(
    acompanhante_id UUID,
    admin_id UUID,
    motivo TEXT
) RETURNS void AS $$
BEGIN
    -- Atualiza status do acompanhante
    UPDATE acompanhantes 
    SET status = 'rejeitado',
        updated_at = NOW()
    WHERE id = acompanhante_id;

    -- Registra ação no log
    INSERT INTO admin_log (admin_id, acao, entidade, entidade_id, detalhes)
    VALUES (
        admin_id,
        'rejeitar',
        'acompanhante',
        acompanhante_id,
        jsonb_build_object(
            'status', 'rejeitado',
            'motivo', motivo
        )
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

CREATE OR REPLACE FUNCTION bloquear_acompanhante(
    acompanhante_id UUID,
    admin_id UUID,
    motivo TEXT
) RETURNS void AS $$
BEGIN
    -- Atualiza status do acompanhante
    UPDATE acompanhantes 
    SET status = 'bloqueado',
        updated_at = NOW()
    WHERE id = acompanhante_id;

    -- Registra ação no log
    INSERT INTO admin_log (admin_id, acao, entidade, entidade_id, detalhes)
    VALUES (
        admin_id,
        'bloquear',
        'acompanhante',
        acompanhante_id,
        jsonb_build_object(
            'status', 'bloqueado',
            'motivo', motivo
        )
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Views para o painel administrativo
CREATE OR REPLACE VIEW vw_acompanhantes_pendentes AS
SELECT 
    a.*,
    c.nome as cidade_nome,
    c.estado as cidade_estado,
    (SELECT COUNT(*) FROM fotos f WHERE f.acompanhante_id = a.id) as total_fotos,
    (SELECT COUNT(*) FROM documentos_acompanhante d WHERE d.acompanhante_id = a.id) as total_documentos,
    (SELECT COUNT(*) FROM videos_verificacao v WHERE v.acompanhante_id = a.id) as total_videos
FROM acompanhantes a
LEFT JOIN cidades c ON c.id = a.cidade_id
WHERE a.status = 'pendente'
ORDER BY a.created_at DESC;

CREATE OR REPLACE VIEW vw_acompanhantes_ativos AS
SELECT 
    a.*,
    c.nome as cidade_nome,
    c.estado as cidade_estado,
    (SELECT COUNT(*) FROM fotos f WHERE f.acompanhante_id = a.id) as total_fotos,
    (SELECT COUNT(*) FROM documentos_acompanhante d WHERE d.acompanhante_id = a.id) as total_documentos,
    (SELECT COUNT(*) FROM videos_verificacao v WHERE v.acompanhante_id = a.id) as total_videos,
    (SELECT COUNT(*) FROM denuncias d WHERE d.acompanhante_id = a.id AND d.status = 'pendente') as denuncias_pendentes
FROM acompanhantes a
LEFT JOIN cidades c ON c.id = a.cidade_id
WHERE a.status = 'aprovado'
ORDER BY a.updated_at DESC;

-- Políticas de segurança para o painel
ALTER TABLE admin ENABLE ROW LEVEL SECURITY;
ALTER TABLE admin_log ENABLE ROW LEVEL SECURITY;
ALTER TABLE denuncias ENABLE ROW LEVEL SECURITY;
ALTER TABLE notas_admin ENABLE ROW LEVEL SECURITY;

-- Política para administradores
CREATE POLICY "Apenas super_admin pode gerenciar admins"
    ON admin
    FOR ALL
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.user_id = auth.uid() 
            AND a.nivel_acesso = 'super_admin'
        )
    );

CREATE POLICY "Admins podem ver logs"
    ON admin_log
    FOR SELECT
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.user_id = auth.uid()
        )
    );

CREATE POLICY "Admins podem gerenciar denúncias"
    ON denuncias
    FOR ALL
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.user_id = auth.uid()
        )
    );

CREATE POLICY "Admins podem gerenciar notas"
    ON notas_admin
    FOR ALL
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.user_id = auth.uid()
        )
    );

-- Índices para performance
CREATE INDEX idx_acompanhantes_status ON acompanhantes(status);
CREATE INDEX idx_denuncias_status ON denuncias(status);
CREATE INDEX idx_admin_log_created_at ON admin_log(created_at);
CREATE INDEX idx_admin_log_admin_id ON admin_log(admin_id);
CREATE INDEX idx_denuncias_acompanhante ON denuncias(acompanhante_id);
CREATE INDEX idx_notas_admin_acompanhante ON notas_admin(acompanhante_id); 