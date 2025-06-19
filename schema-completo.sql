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
    whatsapp VARCHAR(15),
    telegram VARCHAR(50),
    
    -- Características principais
    genero genero_enum NOT NULL,
    genitalia genitalia_enum,
    preferencia_sexual preferencia_enum,
    
    -- Localização
    cidade VARCHAR(100) NOT NULL,
    estado CHAR(2) NOT NULL,
    bairro VARCHAR(100),
    endereco TEXT,
    cep VARCHAR(9),
    
    -- Características físicas
    peso DECIMAL(5,2),
    altura DECIMAL(3,2),
    manequim VARCHAR(10),
    etnia etnia_enum,
    cor_olhos cor_olhos_enum,
    cor_cabelo VARCHAR(50),
    estilo_cabelo estilo_cabelo_enum,
    tamanho_cabelo tamanho_cabelo_enum,
    tamanho_pe SMALLINT,
    
    -- Medidas
    busto SMALLINT,
    cintura SMALLINT,
    quadril SMALLINT,
    
    -- Características booleanas
    silicone BOOLEAN DEFAULT false,
    tatuagens BOOLEAN DEFAULT false,
    piercings BOOLEAN DEFAULT false,
    fumante BOOLEAN DEFAULT false,
    
    -- Informações de atendimento
    local_atendimento TEXT[], -- Array de locais: 'domicilio', 'motel', 'hotel', 'casa_propria', etc
    formas_pagamento TEXT[], -- Array com formas: 'dinheiro', 'pix', 'cartao', etc
    horario_atendimento JSONB, -- Estrutura com dias e horários
    valor_padrao DECIMAL(10,2),
    valor_promocional DECIMAL(10,2),
    
    -- Informações adicionais
    idiomas TEXT[],
    especialidades TEXT[],
    descricao TEXT,
    sobre_mim TEXT,
    
    -- Redes sociais
    instagram VARCHAR(100),
    twitter VARCHAR(100),
    tiktok VARCHAR(100),
    site VARCHAR(255),
    
    -- Status e controle
    status status_enum DEFAULT 'pendente',
    verificado BOOLEAN DEFAULT false,
    destaque BOOLEAN DEFAULT false,
    destaque_ate TIMESTAMP WITH TIME ZONE,
    bloqueado BOOLEAN DEFAULT false,
    motivo_bloqueio TEXT,
    motivo_rejeicao TEXT,
    revisado_por INTEGER REFERENCES admin(id),
    data_revisao TIMESTAMP WITH TIME ZONE,
    
    -- Cache de contagem de mídias
    total_fotos INTEGER DEFAULT 0,
    total_videos INTEGER DEFAULT 0,
    total_documentos INTEGER DEFAULT 0,
    
    -- Datas
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    ultimo_login TIMESTAMP WITH TIME ZONE,
    ultima_atualizacao TIMESTAMP WITH TIME ZONE
);

-- Tabela de fotos
CREATE TABLE fotos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    tipo foto_tipo_enum NOT NULL,
    ordem SMALLINT,
    principal BOOLEAN DEFAULT false,
    aprovada BOOLEAN DEFAULT false,
    motivo_rejeicao TEXT,
    tamanho BIGINT, -- tamanho em bytes
    formato VARCHAR(10), -- extensão do arquivo
    dimensoes JSONB, -- {width: X, height: Y}
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
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
    tamanho BIGINT,
    formato VARCHAR(10),
    data_verificacao TIMESTAMP WITH TIME ZONE,
    verificado_por INTEGER REFERENCES admin(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de vídeos
CREATE TABLE videos_verificacao (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    verificado BOOLEAN DEFAULT false,
    motivo_rejeicao TEXT,
    tamanho BIGINT,
    formato VARCHAR(10),
    duracao INTEGER, -- duração em segundos
    thumbnail_url TEXT,
    data_verificacao TIMESTAMP WITH TIME ZONE,
    verificado_por INTEGER REFERENCES admin(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
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

-- Triggers para atualizar contadores na tabela acompanhantes
CREATE OR REPLACE FUNCTION atualizar_contadores_midia()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_TABLE_NAME = 'fotos' THEN
        UPDATE acompanhantes 
        SET total_fotos = (SELECT COUNT(*) FROM fotos WHERE acompanhante_id = NEW.acompanhante_id)
        WHERE id = NEW.acompanhante_id;
    ELSIF TG_TABLE_NAME = 'videos_verificacao' THEN
        UPDATE acompanhantes 
        SET total_videos = (SELECT COUNT(*) FROM videos_verificacao WHERE acompanhante_id = NEW.acompanhante_id)
        WHERE id = NEW.acompanhante_id;
    ELSIF TG_TABLE_NAME = 'documentos_acompanhante' THEN
        UPDATE acompanhantes 
        SET total_documentos = (SELECT COUNT(*) FROM documentos_acompanhante WHERE acompanhante_id = NEW.acompanhante_id)
        WHERE id = NEW.acompanhante_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Criar triggers para cada tabela
CREATE TRIGGER atualizar_total_fotos
    AFTER INSERT OR DELETE ON fotos
    FOR EACH ROW
    EXECUTE FUNCTION atualizar_contadores_midia();

CREATE TRIGGER atualizar_total_videos
    AFTER INSERT OR DELETE ON videos_verificacao
    FOR EACH ROW
    EXECUTE FUNCTION atualizar_contadores_midia();

CREATE TRIGGER atualizar_total_documentos
    AFTER INSERT OR DELETE ON documentos_acompanhante
    FOR EACH ROW
    EXECUTE FUNCTION atualizar_contadores_midia();

-- Trigger para atualizar updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_acompanhantes_updated_at
    BEFORE UPDATE ON acompanhantes
    FOR EACH ROW
    EXECUTE PROCEDURE update_updated_at_column();

-- Views
CREATE OR REPLACE VIEW vw_painel_acompanhantes AS
SELECT 
    a.id,
    a.user_id,
    a.nome,
    a.email,
    a.telefone,
    a.idade,
    a.whatsapp,
    a.telegram,
    a.genero,
    a.genitalia,
    a.preferencia_sexual,
    a.cidade,
    a.estado,
    a.bairro,
    a.endereco,
    a.cep,
    a.peso,
    a.altura,
    a.manequim,
    a.etnia,
    a.cor_olhos,
    a.cor_cabelo,
    a.estilo_cabelo,
    a.tamanho_cabelo,
    a.tamanho_pe,
    a.busto,
    a.cintura,
    a.quadril,
    a.silicone,
    a.tatuagens,
    a.piercings,
    a.fumante,
    a.local_atendimento,
    a.formas_pagamento,
    a.horario_atendimento,
    a.valor_padrao,
    a.valor_promocional,
    a.idiomas,
    a.especialidades,
    a.descricao,
    a.sobre_mim,
    a.instagram,
    a.twitter,
    a.tiktok,
    a.site,
    a.status,
    a.verificado,
    a.destaque,
    a.destaque_ate,
    a.bloqueado,
    a.motivo_bloqueio,
    a.motivo_rejeicao,
    a.revisado_por,
    a.data_revisao,
    a.total_fotos,
    a.total_documentos,
    a.total_videos,
    a.created_at,
    a.updated_at,
    a.ultimo_login,
    a.ultima_atualizacao,
    CASE 
        WHEN a.status = 'pendente' THEN true 
        ELSE false 
    END as requer_revisao,
    CASE
        WHEN a.destaque AND a.destaque_ate > NOW() THEN true
        ELSE false
    END as destaque_ativo
FROM acompanhantes a
ORDER BY 
    CASE 
        WHEN a.status = 'pendente' THEN 0
        WHEN a.status = 'aprovado' AND a.destaque THEN 1
        WHEN a.status = 'aprovado' THEN 2
        ELSE 3
    END,
    a.created_at DESC;

-- View de estatísticas gerais
CREATE OR REPLACE VIEW vw_estatisticas_gerais AS
SELECT
    COUNT(*) as total_acompanhantes,
    COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
    COUNT(CASE WHEN status = 'aprovado' THEN 1 END) as aprovados,
    COUNT(CASE WHEN status = 'rejeitado' THEN 1 END) as rejeitados,
    COUNT(CASE WHEN status = 'bloqueado' THEN 1 END) as bloqueados,
    COUNT(CASE WHEN verificado THEN 1 END) as verificados,
    COUNT(CASE WHEN destaque AND destaque_ate > NOW() THEN 1 END) as em_destaque,
    COUNT(CASE WHEN created_at > NOW() - INTERVAL '24 hours' THEN 1 END) as novos_24h,
    COUNT(CASE WHEN ultimo_login > NOW() - INTERVAL '7 days' THEN 1 END) as ativos_7dias
FROM acompanhantes;

-- View de estatísticas por cidade
CREATE OR REPLACE VIEW vw_estatisticas_por_cidade AS
SELECT
    cidade,
    estado,
    COUNT(*) as total,
    COUNT(CASE WHEN status = 'aprovado' THEN 1 END) as aprovados,
    COUNT(CASE WHEN verificado THEN 1 END) as verificados,
    COUNT(CASE WHEN destaque AND destaque_ate > NOW() THEN 1 END) as em_destaque
FROM acompanhantes
GROUP BY cidade, estado
ORDER BY total DESC;

-- View de log de atividades
CREATE OR REPLACE VIEW vw_log_atividades AS
SELECT
    l.id,
    l.created_at,
    a.usuario as admin_usuario,
    ac.nome as acompanhante_nome,
    l.acao,
    l.detalhes
FROM admin_log l
JOIN admin a ON a.id = l.admin_id
LEFT JOIN acompanhantes ac ON ac.id = l.acompanhante_id
ORDER BY l.created_at DESC;

-- Índices para performance
CREATE INDEX idx_acompanhantes_status ON acompanhantes(status);
CREATE INDEX idx_acompanhantes_cidade ON acompanhantes(cidade);
CREATE INDEX idx_acompanhantes_estado ON acompanhantes(estado);
CREATE INDEX idx_acompanhantes_created ON acompanhantes(created_at);
CREATE INDEX idx_acompanhantes_destaque ON acompanhantes(destaque, destaque_ate);
CREATE INDEX idx_acompanhantes_verificado ON acompanhantes(verificado);
CREATE INDEX idx_fotos_acompanhante ON fotos(acompanhante_id);
CREATE INDEX idx_fotos_tipo ON fotos(tipo);
CREATE INDEX idx_documentos_acompanhante ON documentos_acompanhante(acompanhante_id);
CREATE INDEX idx_documentos_tipo ON documentos_acompanhante(tipo);
CREATE INDEX idx_videos_acompanhante ON videos_verificacao(acompanhante_id);

-- Políticas de segurança RLS
ALTER TABLE acompanhantes ENABLE ROW LEVEL SECURITY;
ALTER TABLE fotos ENABLE ROW LEVEL SECURITY;
ALTER TABLE documentos_acompanhante ENABLE ROW LEVEL SECURITY;
ALTER TABLE videos_verificacao ENABLE ROW LEVEL SECURITY;
ALTER TABLE admin_log ENABLE ROW LEVEL SECURITY;

-- Políticas para acompanhantes
CREATE POLICY "Acompanhantes podem ver seus próprios dados"
    ON acompanhantes FOR SELECT
    USING (user_id = auth.uid());

CREATE POLICY "Acompanhantes podem atualizar seus próprios dados"
    ON acompanhantes FOR UPDATE
    USING (user_id = auth.uid());

-- Políticas para fotos
CREATE POLICY "Acompanhantes podem ver suas próprias fotos"
    ON fotos FOR SELECT
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

CREATE POLICY "Acompanhantes podem gerenciar suas próprias fotos"
    ON fotos FOR ALL
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

-- Políticas para documentos
CREATE POLICY "Acompanhantes podem ver seus próprios documentos"
    ON documentos_acompanhante FOR SELECT
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

CREATE POLICY "Acompanhantes podem gerenciar seus próprios documentos"
    ON documentos_acompanhante FOR ALL
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

-- Políticas para vídeos
CREATE POLICY "Acompanhantes podem ver seus próprios vídeos"
    ON videos_verificacao FOR SELECT
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

CREATE POLICY "Acompanhantes podem gerenciar seus próprios vídeos"
    ON videos_verificacao FOR ALL
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

-- Políticas para admin
CREATE POLICY "Admins podem ver todos os registros"
    ON acompanhantes FOR SELECT
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.usuario = auth.email()
        )
    );

CREATE POLICY "Admins podem gerenciar todos os registros"
    ON acompanhantes FOR ALL
    USING (
        EXISTS (
            SELECT 1 FROM admin a 
            WHERE a.usuario = auth.email()
        )
    ); 