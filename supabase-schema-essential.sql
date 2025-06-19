-- Limpar todas as tabelas existentes
DROP TABLE IF EXISTS acompanhante_tag CASCADE;
DROP TABLE IF EXISTS acompanhante_servico CASCADE;
DROP TABLE IF EXISTS avaliacoes CASCADE;
DROP TABLE IF EXISTS visualizacoes CASCADE;
DROP TABLE IF EXISTS fotos CASCADE;
DROP TABLE IF EXISTS fotos_perfil CASCADE;
DROP TABLE IF EXISTS fotos_galeria CASCADE;
DROP TABLE IF EXISTS documentos CASCADE;
DROP TABLE IF EXISTS documentos_acompanhante CASCADE;
DROP TABLE IF EXISTS videos CASCADE;
DROP TABLE IF EXISTS videos_verificacao CASCADE;
DROP TABLE IF EXISTS acompanhantes CASCADE;
DROP TABLE IF EXISTS servicos CASCADE;
DROP TABLE IF EXISTS tags CASCADE;
DROP TABLE IF EXISTS cidades CASCADE;
DROP TABLE IF EXISTS configuracoes CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS admin CASCADE;
DROP TABLE IF EXISTS blog CASCADE;
DROP TABLE IF EXISTS denuncias CASCADE;

-- Remover tipos existentes
DROP TYPE IF EXISTS status_enum CASCADE;
DROP TYPE IF EXISTS genero_enum CASCADE;
DROP TYPE IF EXISTS etnia_enum CASCADE;
DROP TYPE IF EXISTS cor_olhos_enum CASCADE;
DROP TYPE IF EXISTS estilo_cabelo_enum CASCADE;
DROP TYPE IF EXISTS tamanho_cabelo_enum CASCADE;
DROP TYPE IF EXISTS foto_tipo_enum CASCADE;
DROP TYPE IF EXISTS documento_tipo_enum CASCADE;

-- Criar tipos enumerados essenciais
CREATE TYPE status_enum AS ENUM ('pendente', 'aprovado', 'rejeitado', 'bloqueado');
CREATE TYPE genero_enum AS ENUM ('feminino', 'masculino', 'trans', 'outro');
CREATE TYPE etnia_enum AS ENUM ('branca', 'negra', 'parda', 'asiatica', 'indigena', 'outra');
CREATE TYPE foto_tipo_enum AS ENUM ('perfil', 'galeria', 'verificacao');
CREATE TYPE documento_tipo_enum AS ENUM ('rg', 'cnh', 'selfie');

-- Criar extensões necessárias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Tabela de cidades
CREATE TABLE cidades (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(100) NOT NULL,
    estado CHAR(2) NOT NULL,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(nome, estado)
);

-- Tabela principal de acompanhantes
CREATE TABLE acompanhantes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES auth.users(id) ON DELETE SET NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(15) NOT NULL UNIQUE,
    idade SMALLINT CHECK (idade >= 18),
    cidade_id UUID REFERENCES cidades(id),
    genero genero_enum NOT NULL,
    descricao TEXT,
    status status_enum DEFAULT 'pendente',
    verificado BOOLEAN DEFAULT false,
    destaque BOOLEAN DEFAULT false,
    etnia etnia_enum,
    altura DECIMAL(3,2),
    peso DECIMAL(5,2),
    endereco TEXT,
    horario_atendimento TEXT,
    formas_pagamento TEXT,
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
    ativa BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(acompanhante_id, ordem)
);

-- Tabela de documentos
CREATE TABLE documentos_acompanhante (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    tipo documento_tipo_enum NOT NULL,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    verificado BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de vídeos de verificação
CREATE TABLE videos_verificacao (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    verificado BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Índices essenciais
CREATE INDEX idx_acompanhantes_cidade ON acompanhantes(cidade_id);
CREATE INDEX idx_acompanhantes_status ON acompanhantes(status);
CREATE INDEX idx_acompanhantes_verificado ON acompanhantes(verificado);
CREATE INDEX idx_fotos_acompanhante ON fotos(acompanhante_id);

-- Triggers para updated_at
CREATE OR REPLACE FUNCTION update_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_acompanhantes_updated_at
    BEFORE UPDATE ON acompanhantes
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER update_cidades_updated_at
    BEFORE UPDATE ON cidades
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

-- Políticas de segurança RLS
ALTER TABLE acompanhantes ENABLE ROW LEVEL SECURITY;
ALTER TABLE fotos ENABLE ROW LEVEL SECURITY;
ALTER TABLE documentos_acompanhante ENABLE ROW LEVEL SECURITY;
ALTER TABLE videos_verificacao ENABLE ROW LEVEL SECURITY;

-- Política para acompanhantes
CREATE POLICY "Público pode ver acompanhantes aprovados"
    ON acompanhantes
    FOR SELECT
    USING (status = 'aprovado');

CREATE POLICY "Acompanhantes podem editar seus próprios dados"
    ON acompanhantes
    FOR ALL
    USING (auth.uid()::text = user_id::text);

-- Política para fotos
CREATE POLICY "Público pode ver fotos de acompanhantes aprovados"
    ON fotos
    FOR SELECT
    USING (EXISTS (
        SELECT 1 FROM acompanhantes a 
        WHERE a.id = fotos.acompanhante_id 
        AND a.status = 'aprovado'
    ));

CREATE POLICY "Acompanhantes podem gerenciar suas fotos"
    ON fotos
    FOR ALL
    USING (EXISTS (
        SELECT 1 FROM acompanhantes a 
        WHERE a.id = fotos.acompanhante_id 
        AND a.user_id = auth.uid()
    ));

-- Política para documentos (privado)
CREATE POLICY "Acompanhantes podem ver seus documentos"
    ON documentos_acompanhante
    FOR SELECT
    USING (EXISTS (
        SELECT 1 FROM acompanhantes a 
        WHERE a.id = documentos_acompanhante.acompanhante_id 
        AND a.user_id = auth.uid()
    ));

CREATE POLICY "Acompanhantes podem gerenciar seus documentos"
    ON documentos_acompanhante
    FOR ALL
    USING (EXISTS (
        SELECT 1 FROM acompanhantes a 
        WHERE a.id = documentos_acompanhante.acompanhante_id 
        AND a.user_id = auth.uid()
    ));

-- Política para vídeos (privado)
CREATE POLICY "Acompanhantes podem ver seus vídeos"
    ON videos_verificacao
    FOR SELECT
    USING (EXISTS (
        SELECT 1 FROM acompanhantes a 
        WHERE a.id = videos_verificacao.acompanhante_id 
        AND a.user_id = auth.uid()
    ));

CREATE POLICY "Acompanhantes podem gerenciar seus vídeos"
    ON videos_verificacao
    FOR ALL
    USING (EXISTS (
        SELECT 1 FROM acompanhantes a 
        WHERE a.id = videos_verificacao.acompanhante_id 
        AND a.user_id = auth.uid()
    )); 