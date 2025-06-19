-- Tabela de estados
CREATE TABLE estados (
    id SERIAL PRIMARY KEY,
    uf CHAR(2) NOT NULL UNIQUE,
    nome VARCHAR(50) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de cidades
CREATE TABLE cidades (
    id SERIAL PRIMARY KEY,
    estado_id INTEGER REFERENCES estados(id),
    nome VARCHAR(100) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(estado_id, nome)
);

-- Criar índices
CREATE INDEX idx_cidades_estado ON cidades(estado_id);
CREATE INDEX idx_cidades_nome ON cidades(nome);

-- Criar view para facilitar a seleção de cidades
CREATE OR REPLACE VIEW vw_cidades_estados AS
SELECT 
    c.id as cidade_id,
    c.nome as cidade,
    e.id as estado_id,
    e.uf as estado_uf,
    e.nome as estado_nome,
    c.nome || ' - ' || e.uf as cidade_estado
FROM cidades c
JOIN estados e ON e.id = c.estado_id
ORDER BY e.uf, c.nome;

-- Inserir estados
INSERT INTO estados (uf, nome) VALUES
('AC', 'Acre'),
('AL', 'Alagoas'),
('AP', 'Amapá'),
('AM', 'Amazonas'),
('BA', 'Bahia'),
('CE', 'Ceará'),
('DF', 'Distrito Federal'),
('ES', 'Espírito Santo'),
('GO', 'Goiás'),
('MA', 'Maranhão'),
('MT', 'Mato Grosso'),
('MS', 'Mato Grosso do Sul'),
('MG', 'Minas Gerais'),
('PA', 'Pará'),
('PB', 'Paraíba'),
('PR', 'Paraná'),
('PE', 'Pernambuco'),
('PI', 'Piauí'),
('RJ', 'Rio de Janeiro'),
('RN', 'Rio Grande do Norte'),
('RS', 'Rio Grande do Sul'),
('RO', 'Rondônia'),
('RR', 'Roraima'),
('SC', 'Santa Catarina'),
('SP', 'São Paulo'),
('SE', 'Sergipe'),
('TO', 'Tocantins'); 