-- Schema do Supabase para Sigilosas VIP
-- Adaptado de MySQL para PostgreSQL

-- Habilitar extensões necessárias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Tabela de cidades
CREATE TABLE cidades (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

-- Tabela principal de acompanhantes
CREATE TABLE acompanhantes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cidade_id INTEGER REFERENCES cidades(id) ON DELETE SET NULL,
    idade INTEGER,
    genero VARCHAR(10) CHECK (genero IN ('F', 'M', 'Outro')) DEFAULT 'F',
    valor DECIMAL(10,2),
    descricao TEXT,
    destaque BOOLEAN DEFAULT FALSE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) CHECK (status IN ('pendente', 'aprovado', 'rejeitado')) DEFAULT 'pendente',
    disponibilidade VARCHAR(80),
    verificado BOOLEAN DEFAULT FALSE,
    bairro VARCHAR(80),
    aceita_cartao BOOLEAN DEFAULT FALSE,
    atende_casal BOOLEAN DEFAULT FALSE,
    local_proprio BOOLEAN DEFAULT FALSE,
    aceita_pix BOOLEAN DEFAULT FALSE,
    genitalia VARCHAR(40),
    preferencia_sexual VARCHAR(80),
    peso VARCHAR(10),
    altura VARCHAR(10),
    etnia VARCHAR(20) CHECK (etnia IN ('Branca', 'Negra', 'Parda', 'Amarela', 'Indígena', 'Outro')),
    cor_olhos VARCHAR(40),
    estilo_cabelo VARCHAR(40),
    tamanho_cabelo VARCHAR(20),
    tamanho_pe VARCHAR(10),
    silicone BOOLEAN DEFAULT FALSE,
    tatuagens BOOLEAN DEFAULT FALSE,
    piercings BOOLEAN DEFAULT FALSE,
    fumante VARCHAR(10),
    idiomas VARCHAR(120),
    endereco VARCHAR(120),
    comodidades VARCHAR(120),
    bairros_atende VARCHAR(120),
    cidades_vizinhas VARCHAR(120),
    clientes_conjunto INTEGER DEFAULT 1,
    atende_genero VARCHAR(40),
    horario_expediente VARCHAR(120),
    formas_pagamento VARCHAR(120),
    seguidores INTEGER DEFAULT 0,
    favoritos INTEGER DEFAULT 0,
    penalidades BOOLEAN DEFAULT FALSE,
    contato_seguro BOOLEAN DEFAULT FALSE,
    data_criacao DATE DEFAULT CURRENT_DATE,
    foto VARCHAR(255),
    video_verificacao VARCHAR(255)
);

-- Tabela de fotos
CREATE TABLE fotos (
    id SERIAL PRIMARY KEY,
    acompanhante_id INTEGER REFERENCES acompanhantes(id) ON DELETE CASCADE,
    url VARCHAR(255),
    capa BOOLEAN DEFAULT FALSE,
    tipo VARCHAR(32)
);

-- Tabela de avaliações
CREATE TABLE avaliacoes (
    id SERIAL PRIMARY KEY,
    acompanhante_id INTEGER REFERENCES acompanhantes(id) ON DELETE CASCADE,
    nota SMALLINT CHECK (nota >= 1 AND nota <= 5),
    comentario TEXT,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) CHECK (status IN ('pendente', 'aprovado', 'rejeitado')) DEFAULT 'pendente'
);

-- Tabela de tags
CREATE TABLE tags (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
);

-- Tabela de relacionamento acompanhante-tag
CREATE TABLE acompanhante_tag (
    acompanhante_id INTEGER REFERENCES acompanhantes(id) ON DELETE CASCADE,
    tag_id INTEGER REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (acompanhante_id, tag_id)
);

-- Tabela de administradores
CREATE TABLE admin (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de usuários
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    email VARCHAR(120) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo VARCHAR(20) CHECK (tipo IN ('admin', 'editora')) DEFAULT 'editora',
    acompanhante_id INTEGER REFERENCES acompanhantes(id) ON DELETE SET NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de configurações
CREATE TABLE configuracoes (
    id SERIAL PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT
);

-- Tabela de serviços
CREATE TABLE servicos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(80) NOT NULL
);

-- Tabela de relacionamento acompanhante-serviço
CREATE TABLE acompanhante_servico (
    acompanhante_id INTEGER REFERENCES acompanhantes(id) ON DELETE CASCADE,
    servico_id INTEGER REFERENCES servicos(id) ON DELETE CASCADE,
    PRIMARY KEY (acompanhante_id, servico_id)
);

-- Tabela de denúncias
CREATE TABLE denuncias (
    id SERIAL PRIMARY KEY,
    acompanhante_id INTEGER NOT NULL REFERENCES acompanhantes(id) ON DELETE CASCADE,
    motivo VARCHAR(120) NOT NULL,
    detalhes TEXT,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) CHECK (status IN ('pendente', 'analisada', 'ignorada')) DEFAULT 'pendente'
);

-- Tabela de documentos
CREATE TABLE documentos_acompanhante (
    id SERIAL PRIMARY KEY,
    acompanhante_id INTEGER NOT NULL REFERENCES acompanhantes(id) ON DELETE CASCADE,
    url VARCHAR(255) NOT NULL,
    tipo VARCHAR(32),
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de vídeos de verificação
CREATE TABLE videos_verificacao (
    id SERIAL PRIMARY KEY,
    acompanhante_id INTEGER NOT NULL REFERENCES acompanhantes(id) ON DELETE CASCADE,
    url VARCHAR(255) NOT NULL,
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de visualizações
CREATE TABLE visualizacoes (
    id SERIAL PRIMARY KEY,
    acompanhante_id INTEGER NOT NULL REFERENCES acompanhantes(id) ON DELETE CASCADE,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45)
);

-- Tabela de blog
CREATE TABLE blog (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(120) NOT NULL,
    resumo VARCHAR(200) NOT NULL,
    conteudo TEXT NOT NULL,
    img_capa VARCHAR(255),
    data TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Inserir dados iniciais
INSERT INTO cidades (nome) VALUES 
('Itapema'),
('Blumenau');

-- Inserir configurações padrão
INSERT INTO configuracoes (chave, valor) VALUES
('logo', '/assets/img/logo.png'),
('favicon', '/assets/img/favicon.ico'),
('banner', '/assets/img/banner.jpg'),
('banner_cadastro', ''),
('icone_painel', ''),
('icone_heart', ''),
('icone_search', ''),
('icone_map_marker', ''),
('icone_user', ''),
('icone_star', ''),
('icone_whatsapp', ''),
('icone_refresh', '');

-- Criar índices para performance
CREATE INDEX idx_acompanhantes_cidade ON acompanhantes(cidade_id);
CREATE INDEX idx_acompanhantes_status ON acompanhantes(status);
CREATE INDEX idx_acompanhantes_destaque ON acompanhantes(destaque);
CREATE INDEX idx_fotos_acompanhante ON fotos(acompanhante_id);
CREATE INDEX idx_avaliacoes_acompanhante ON avaliacoes(acompanhante_id);
CREATE INDEX idx_visualizacoes_acompanhante ON visualizacoes(acompanhante_id);
CREATE INDEX idx_visualizacoes_data ON visualizacoes(data);

-- Configurar RLS (Row Level Security) para Supabase
ALTER TABLE acompanhantes ENABLE ROW LEVEL SECURITY;
ALTER TABLE fotos ENABLE ROW LEVEL SECURITY;
ALTER TABLE avaliacoes ENABLE ROW LEVEL SECURITY;
ALTER TABLE usuarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE admin ENABLE ROW LEVEL SECURITY;

-- Políticas de segurança básicas
CREATE POLICY "Acompanhantes são visíveis publicamente" ON acompanhantes
    FOR SELECT USING (status = 'aprovado');

CREATE POLICY "Fotos são visíveis publicamente" ON fotos
    FOR SELECT USING (true);

CREATE POLICY "Avaliações são visíveis publicamente" ON avaliacoes
    FOR SELECT USING (status = 'aprovado');

-- MIGRAÇÃO: Restringir valores de etnia
ALTER TABLE acompanhantes
  ALTER COLUMN etnia TYPE VARCHAR(20),
  ADD CONSTRAINT acompanhantes_etnia_check CHECK (etnia IN ('Branca', 'Negra', 'Parda', 'Amarela', 'Indígena', 'Outro')); 