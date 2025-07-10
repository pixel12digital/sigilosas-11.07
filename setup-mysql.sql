-- Setup MySQL para Sigilosas VIP
-- Compatível com MySQL 8.0+

-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS sigilosas_vip CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sigilosas_vip;

-- Tabela de administradores
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    nivel ENUM('admin', 'moderador') DEFAULT 'admin',
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de estados
CREATE TABLE IF NOT EXISTS estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    uf CHAR(2) NOT NULL UNIQUE,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de cidades
CREATE TABLE IF NOT EXISTS cidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    estado_id INT NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estado_id) REFERENCES estados(id) ON DELETE CASCADE,
    INDEX idx_cidade_estado (estado_id),
    INDEX idx_cidade_nome (nome)
);

-- Tabela principal de acompanhantes
CREATE TABLE IF NOT EXISTS acompanhantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados básicos (obrigatórios)
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(15) NOT NULL,
    idade SMALLINT CHECK (idade >= 18),
    whatsapp VARCHAR(15),
    telegram VARCHAR(50),
    
    -- Características principais
    genero ENUM('feminino', 'masculino', 'trans', 'outro') NOT NULL,
    genitalia ENUM('feminina', 'masculina'),
    preferencia_sexual ENUM('homens', 'mulheres', 'todos'),
    
    -- Localização
    cidade_id INT,
    estado_id INT,
    bairro VARCHAR(100),
    endereco TEXT,
    cep VARCHAR(9),
    
    -- Características físicas
    peso DECIMAL(5,2),
    altura DECIMAL(3,2),
    manequim VARCHAR(10),
    etnia ENUM('branca', 'negra', 'parda', 'asiatica', 'indigena', 'outra'),
    cor_olhos ENUM('castanhos', 'azuis', 'verdes', 'pretos', 'outros'),
    cor_cabelo VARCHAR(50),
    estilo_cabelo ENUM('liso', 'ondulado', 'cacheado', 'crespo'),
    tamanho_cabelo ENUM('curto', 'medio', 'longo'),
    tamanho_pe SMALLINT,
    
    -- Medidas
    busto SMALLINT,
    cintura SMALLINT,
    quadril SMALLINT,
    
    -- Características booleanas
    silicone BOOLEAN DEFAULT FALSE,
    tatuagens BOOLEAN DEFAULT FALSE,
    piercings BOOLEAN DEFAULT FALSE,
    fumante BOOLEAN DEFAULT FALSE,
    
    -- Informações de atendimento
    local_atendimento JSON, -- Array de locais: 'domicilio', 'motel', 'hotel', 'casa_propria', etc
    formas_pagamento JSON, -- Array com formas: 'dinheiro', 'pix', 'cartao', etc
    horario_atendimento JSON, -- Estrutura com dias e horários
    valor_padrao DECIMAL(10,2),
    valor_promocional DECIMAL(10,2),
    
    -- Informações adicionais
    idiomas JSON,
    especialidades JSON,
    descricao TEXT,
    sobre_mim TEXT,
    
    -- Redes sociais
    instagram VARCHAR(100),
    twitter VARCHAR(100),
    tiktok VARCHAR(100),
    site VARCHAR(255),
    
    -- Status e controle
    status ENUM('pendente', 'aprovado', 'rejeitado', 'bloqueado') DEFAULT 'pendente',
    aprovada BOOLEAN DEFAULT FALSE,
    verificado BOOLEAN DEFAULT FALSE,
    destaque BOOLEAN DEFAULT FALSE,
    destaque_ate DATETIME,
    bloqueada BOOLEAN DEFAULT FALSE,
    motivo_bloqueio TEXT,
    motivo_rejeicao TEXT,
    revisado_por INT,
    data_revisao DATETIME,
    
    -- Cache de contagem de mídias
    total_fotos INT DEFAULT 0,
    total_videos INT DEFAULT 0,
    total_documentos INT DEFAULT 0,
    
    -- Datas
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ultimo_login DATETIME,
    ultima_atualizacao DATETIME,
    
    -- Chaves estrangeiras
    FOREIGN KEY (cidade_id) REFERENCES cidades(id) ON DELETE SET NULL,
    FOREIGN KEY (estado_id) REFERENCES estados(id) ON DELETE SET NULL,
    FOREIGN KEY (revisado_por) REFERENCES admin(id) ON DELETE SET NULL,
    
    -- Índices
    INDEX idx_acompanhantes_status (status),
    INDEX idx_acompanhantes_cidade (cidade_id),
    INDEX idx_acompanhantes_estado (estado_id),
    INDEX idx_acompanhantes_created (created_at),
    INDEX idx_acompanhantes_destaque (destaque, destaque_ate),
    INDEX idx_acompanhantes_verificado (verificado),
    INDEX idx_acompanhantes_aprovada (aprovada),
    INDEX idx_acompanhantes_bloqueada (bloqueada)
);

-- Tabela de fotos
CREATE TABLE IF NOT EXISTS fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    acompanhante_id INT NOT NULL,
    url VARCHAR(500) NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    tipo ENUM('perfil', 'galeria', 'verificacao') NOT NULL,
    ordem SMALLINT,
    principal BOOLEAN DEFAULT FALSE,
    aprovada BOOLEAN DEFAULT FALSE,
    motivo_rejeicao TEXT,
    tamanho BIGINT, -- tamanho em bytes
    formato VARCHAR(10), -- extensão do arquivo
    dimensoes JSON, -- {width: X, height: Y}
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (acompanhante_id) REFERENCES acompanhantes(id) ON DELETE CASCADE,
    INDEX idx_fotos_acompanhante (acompanhante_id),
    INDEX idx_fotos_tipo (tipo)
);

-- Tabela de documentos
CREATE TABLE IF NOT EXISTS documentos_acompanhante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    acompanhante_id INT NOT NULL,
    tipo ENUM('rg', 'cnh', 'selfie') NOT NULL,
    url VARCHAR(500) NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    verificado BOOLEAN DEFAULT FALSE,
    motivo_rejeicao TEXT,
    tamanho BIGINT,
    formato VARCHAR(10),
    data_verificacao DATETIME,
    verificado_por INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (acompanhante_id) REFERENCES acompanhantes(id) ON DELETE CASCADE,
    FOREIGN KEY (verificado_por) REFERENCES admin(id) ON DELETE SET NULL,
    INDEX idx_documentos_acompanhante (acompanhante_id),
    INDEX idx_documentos_tipo (tipo)
);

-- Tabela de vídeos
CREATE TABLE IF NOT EXISTS videos_verificacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    acompanhante_id INT NOT NULL,
    url VARCHAR(500) NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    verificado BOOLEAN DEFAULT FALSE,
    motivo_rejeicao TEXT,
    tamanho BIGINT,
    formato VARCHAR(10),
    duracao INT, -- duração em segundos
    thumbnail_url VARCHAR(500),
    data_verificacao DATETIME,
    verificado_por INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (acompanhante_id) REFERENCES acompanhantes(id) ON DELETE CASCADE,
    FOREIGN KEY (verificado_por) REFERENCES admin(id) ON DELETE SET NULL,
    INDEX idx_videos_acompanhante (acompanhante_id)
);

-- Log de ações do admin
CREATE TABLE IF NOT EXISTS admin_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    acompanhante_id INT,
    acao VARCHAR(50) NOT NULL,
    detalhes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL,
    FOREIGN KEY (acompanhante_id) REFERENCES acompanhantes(id) ON DELETE SET NULL,
    INDEX idx_admin_log_admin (admin_id),
    INDEX idx_admin_log_acompanhante (acompanhante_id),
    INDEX idx_admin_log_created (created_at)
);

-- Inserir dados básicos de estados brasileiros
INSERT IGNORE INTO estados (nome, uf) VALUES
('Acre', 'AC'),
('Alagoas', 'AL'),
('Amapá', 'AP'),
('Amazonas', 'AM'),
('Bahia', 'BA'),
('Ceará', 'CE'),
('Distrito Federal', 'DF'),
('Espírito Santo', 'ES'),
('Goiás', 'GO'),
('Maranhão', 'MA'),
('Mato Grosso', 'MT'),
('Mato Grosso do Sul', 'MS'),
('Minas Gerais', 'MG'),
('Pará', 'PA'),
('Paraíba', 'PB'),
('Paraná', 'PR'),
('Pernambuco', 'PE'),
('Piauí', 'PI'),
('Rio de Janeiro', 'RJ'),
('Rio Grande do Norte', 'RN'),
('Rio Grande do Sul', 'RS'),
('Rondônia', 'RO'),
('Roraima', 'RR'),
('Santa Catarina', 'SC'),
('São Paulo', 'SP'),
('Sergipe', 'SE'),
('Tocantins', 'TO');

-- Inserir algumas cidades principais
INSERT IGNORE INTO cidades (nome, estado_id) VALUES
('São Paulo', (SELECT id FROM estados WHERE uf = 'SP')),
('Rio de Janeiro', (SELECT id FROM estados WHERE uf = 'RJ')),
('Belo Horizonte', (SELECT id FROM estados WHERE uf = 'MG')),
('Brasília', (SELECT id FROM estados WHERE uf = 'DF')),
('Salvador', (SELECT id FROM estados WHERE uf = 'BA')),
('Fortaleza', (SELECT id FROM estados WHERE uf = 'CE')),
('Curitiba', (SELECT id FROM estados WHERE uf = 'PR')),
('Recife', (SELECT id FROM estados WHERE uf = 'PE')),
('Porto Alegre', (SELECT id FROM estados WHERE uf = 'RS')),
('Manaus', (SELECT id FROM estados WHERE uf = 'AM'));

-- Criar usuário admin padrão (senha: admin123)
INSERT IGNORE INTO admin (nome, email, senha_hash, nivel) VALUES
('Administrador', 'admin@sigilosas.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); 