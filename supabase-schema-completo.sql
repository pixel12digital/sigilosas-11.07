-- Limpar todas as tabelas existentes EXCETO admin
DO $$ 
DECLARE 
    r RECORD;
BEGIN
    FOR r IN (SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename != 'admin') LOOP
        EXECUTE 'DROP TABLE IF EXISTS ' || quote_ident(r.tablename) || ' CASCADE';
    END LOOP;
END $$;

-- Remover todos os tipos existentes
DROP TYPE IF EXISTS status_enum CASCADE;
DROP TYPE IF EXISTS genero_enum CASCADE;
DROP TYPE IF EXISTS genitalia_enum CASCADE;
DROP TYPE IF EXISTS preferencia_enum CASCADE;
DROP TYPE IF EXISTS etnia_enum CASCADE;
DROP TYPE IF EXISTS cor_olhos_enum CASCADE;
DROP TYPE IF EXISTS estilo_cabelo_enum CASCADE;
DROP TYPE IF EXISTS tamanho_cabelo_enum CASCADE;
DROP TYPE IF EXISTS foto_tipo_enum CASCADE;
DROP TYPE IF EXISTS documento_tipo_enum CASCADE;

-- Criar tipos enumerados
CREATE TYPE status_enum AS ENUM ('pendente', 'aprovado', 'rejeitado', 'bloqueado');
CREATE TYPE genero_enum AS ENUM ('feminino', 'masculino', 'trans', 'outro');
CREATE TYPE genitalia_enum AS ENUM ('feminina', 'masculina');
CREATE TYPE preferencia_enum AS ENUM ('homens', 'mulheres', 'todos');
CREATE TYPE etnia_enum AS ENUM ('branca', 'negra', 'parda', 'asiatica', 'indigena', 'outra');
CREATE TYPE cor_olhos_enum AS ENUM ('castanhos', 'azuis', 'verdes', 'pretos', 'outros');
CREATE TYPE estilo_cabelo_enum AS ENUM ('liso', 'ondulado', 'cacheado', 'crespo');
CREATE TYPE tamanho_cabelo_enum AS ENUM ('curto', 'medio', 'longo');
CREATE TYPE foto_tipo_enum AS ENUM ('perfil', 'galeria', 'verificacao');
CREATE TYPE documento_tipo_enum AS ENUM ('rg', 'cnh', 'selfie');

-- Criar extensões necessárias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Tabela principal de acompanhantes
CREATE TABLE acompanhantes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE SET NULL,
    
    -- Dados básicos (obrigatórios)
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(15) NOT NULL,
    idade SMALLINT CHECK (idade >= 18),
    
    -- Características principais
    genero genero_enum NOT NULL,
    genitalia genitalia_enum,
    preferencia_sexual preferencia_enum,
    
    -- Localização
    cidade VARCHAR(100) NOT NULL,
    estado CHAR(2) NOT NULL,
    endereco TEXT,
    
    -- Características físicas
    peso DECIMAL(5,2),
    altura DECIMAL(3,2),
    etnia etnia_enum,
    cor_olhos cor_olhos_enum,
    estilo_cabelo estilo_cabelo_enum,
    tamanho_cabelo tamanho_cabelo_enum,
    tamanho_pe SMALLINT,
    
    -- Características booleanas
    silicone BOOLEAN DEFAULT false,
    tatuagens BOOLEAN DEFAULT false,
    piercings BOOLEAN DEFAULT false,
    fumante BOOLEAN DEFAULT false,
    
    -- Informações adicionais
    idiomas TEXT,
    horario_atendimento TEXT,
    formas_pagamento TEXT,
    descricao TEXT,
    
    -- Status e controle
    status status_enum DEFAULT 'pendente',
    verificado BOOLEAN DEFAULT false,
    destaque BOOLEAN DEFAULT false,
    motivo_rejeicao TEXT,
    revisado_por INTEGER REFERENCES admin(id),
    data_revisao TIMESTAMP WITH TIME ZONE,
    
    -- Datas
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de fotos
CREATE TABLE fotos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    tipo foto_tipo_enum NOT NULL,
    ordem SMALLINT,
    aprovada BOOLEAN DEFAULT false,
    motivo_rejeicao TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de documentos
CREATE TABLE documentos_acompanhante (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    tipo documento_tipo_enum NOT NULL,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    verificado BOOLEAN DEFAULT false,
    motivo_rejeicao TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de vídeos
CREATE TABLE videos_verificacao (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    verificado BOOLEAN DEFAULT false,
    motivo_rejeicao TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Log de ações do admin
CREATE TABLE admin_log (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    admin_id INTEGER REFERENCES admin(id),
    acompanhante_id UUID REFERENCES acompanhantes(id),
    acao VARCHAR(50) NOT NULL,
    detalhes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- View para o painel de acompanhantes
CREATE OR REPLACE VIEW vw_painel_acompanhantes AS
SELECT 
    a.*,
    (SELECT COUNT(*) FROM fotos f WHERE f.acompanhante_id = a.id) as total_fotos,
    (SELECT COUNT(*) FROM documentos_acompanhante d WHERE d.acompanhante_id = a.id) as total_documentos,
    (SELECT COUNT(*) FROM videos_verificacao v WHERE v.acompanhante_id = a.id) as total_videos,
    CASE 
        WHEN a.status = 'pendente' THEN true 
        ELSE false 
    END as requer_revisao
FROM acompanhantes a
ORDER BY 
    CASE 
        WHEN a.status = 'pendente' THEN 0
        WHEN a.status = 'aprovado' THEN 1
        ELSE 2
    END,
    a.created_at DESC;

-- Função para aprovar acompanhante
CREATE OR REPLACE FUNCTION aprovar_acompanhante(
    p_acompanhante_id UUID,
    p_admin_id INTEGER
) RETURNS void AS $$
BEGIN
    UPDATE acompanhantes 
    SET status = 'aprovado',
        verificado = true,
        revisado_por = p_admin_id,
        data_revisao = NOW(),
        updated_at = NOW()
    WHERE id = p_acompanhante_id;

    INSERT INTO admin_log (admin_id, acompanhante_id, acao, detalhes)
    VALUES (p_admin_id, p_acompanhante_id, 'aprovar', 'Perfil aprovado');
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Função para rejeitar acompanhante
CREATE OR REPLACE FUNCTION rejeitar_acompanhante(
    p_acompanhante_id UUID,
    p_admin_id INTEGER,
    p_motivo TEXT
) RETURNS void AS $$
BEGIN
    UPDATE acompanhantes 
    SET status = 'rejeitado',
        motivo_rejeicao = p_motivo,
        revisado_por = p_admin_id,
        data_revisao = NOW(),
        updated_at = NOW()
    WHERE id = p_acompanhante_id;

    INSERT INTO admin_log (admin_id, acompanhante_id, acao, detalhes)
    VALUES (p_admin_id, p_acompanhante_id, 'rejeitar', p_motivo);
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Políticas de segurança
ALTER TABLE acompanhantes ENABLE ROW LEVEL SECURITY;
ALTER TABLE fotos ENABLE ROW LEVEL SECURITY;
ALTER TABLE documentos_acompanhante ENABLE ROW LEVEL SECURITY;
ALTER TABLE videos_verificacao ENABLE ROW LEVEL SECURITY;
ALTER TABLE admin_log ENABLE ROW LEVEL SECURITY;

-- Política para admin ver todos os acompanhantes
CREATE POLICY "Admin pode ver todos os acompanhantes"
    ON acompanhantes
    FOR SELECT
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.usuario = auth.email()
        )
    );

-- Política para admin gerenciar status
CREATE POLICY "Admin pode gerenciar status"
    ON acompanhantes
    FOR UPDATE
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.usuario = auth.email()
        )
    );

-- Política para fotos
CREATE POLICY "Admin pode ver todas as fotos"
    ON fotos
    FOR SELECT
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.usuario = auth.email()
        )
    );

-- Política para documentos
CREATE POLICY "Admin pode ver todos os documentos"
    ON documentos_acompanhante
    FOR SELECT
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.usuario = auth.email()
        )
    );

-- Política para vídeos
CREATE POLICY "Admin pode ver todos os vídeos"
    ON videos_verificacao
    FOR SELECT
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.usuario = auth.email()
        )
    );

-- Índices para performance
CREATE INDEX idx_acompanhantes_status ON acompanhantes(status);
CREATE INDEX idx_acompanhantes_created ON acompanhantes(created_at);
CREATE INDEX idx_fotos_acompanhante ON fotos(acompanhante_id);
CREATE INDEX idx_documentos_acompanhante ON documentos_acompanhante(acompanhante_id);
CREATE INDEX idx_videos_acompanhante ON videos_verificacao(acompanhante_id); 