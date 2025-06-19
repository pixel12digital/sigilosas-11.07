-- Tipos enumerados necessários
CREATE TYPE status_enum AS ENUM ('pendente', 'aprovado', 'rejeitado', 'bloqueado');
CREATE TYPE genero_enum AS ENUM ('feminino', 'masculino', 'trans', 'outro');
CREATE TYPE genitalia_enum AS ENUM ('feminina', 'masculina');
CREATE TYPE preferencia_enum AS ENUM ('homens', 'mulheres', 'todos');
CREATE TYPE etnia_enum AS ENUM ('branca', 'negra', 'parda', 'asiatica', 'indigena', 'outra');
CREATE TYPE cor_olhos_enum AS ENUM ('castanhos', 'azuis', 'verdes', 'pretos', 'outros');
CREATE TYPE estilo_cabelo_enum AS ENUM ('liso', 'ondulado', 'cacheado', 'crespo');
CREATE TYPE tamanho_cabelo_enum AS ENUM ('curto', 'medio', 'longo');

-- Tabela principal de acompanhantes
CREATE TABLE acompanhantes (
    -- Identificação
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

-- Índices para performance
CREATE INDEX idx_acompanhantes_status ON acompanhantes(status);
CREATE INDEX idx_acompanhantes_cidade ON acompanhantes(cidade);
CREATE INDEX idx_acompanhantes_estado ON acompanhantes(estado);
CREATE INDEX idx_acompanhantes_created ON acompanhantes(created_at);
CREATE INDEX idx_acompanhantes_destaque ON acompanhantes(destaque, destaque_ate);
CREATE INDEX idx_acompanhantes_verificado ON acompanhantes(verificado);

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