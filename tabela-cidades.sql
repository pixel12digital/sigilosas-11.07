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

-- Alterar a tabela de acompanhantes para usar as referências
ALTER TABLE acompanhantes 
    DROP COLUMN cidade,
    DROP COLUMN estado;

ALTER TABLE acompanhantes
    ADD COLUMN cidade_id INTEGER REFERENCES cidades(id),
    ADD COLUMN estado_id INTEGER REFERENCES estados(id);

-- View para facilitar a seleção de cidades
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

-- Atualizar as views que usam cidade/estado
CREATE OR REPLACE VIEW vw_painel_acompanhantes AS
SELECT 
    a.*,
    c.nome as cidade_nome,
    e.uf as estado_uf,
    e.nome as estado_nome,
    c.nome || ' - ' || e.uf as cidade_estado,
    CASE 
        WHEN a.status = 'pendente' THEN true 
        ELSE false 
    END as requer_revisao,
    CASE
        WHEN a.destaque AND a.destaque_ate > NOW() THEN true
        ELSE false
    END as destaque_ativo
FROM acompanhantes a
LEFT JOIN cidades c ON c.id = a.cidade_id
LEFT JOIN estados e ON e.id = a.estado_id
ORDER BY 
    CASE 
        WHEN a.status = 'pendente' THEN 0
        WHEN a.status = 'aprovado' AND a.destaque THEN 1
        WHEN a.status = 'aprovado' THEN 2
        ELSE 3
    END,
    a.created_at DESC;

-- View de estatísticas por cidade atualizada
CREATE OR REPLACE VIEW vw_estatisticas_por_cidade AS
SELECT
    c.nome as cidade,
    e.uf as estado,
    COUNT(*) as total,
    COUNT(CASE WHEN a.status = 'aprovado' THEN 1 END) as aprovados,
    COUNT(CASE WHEN a.verificado THEN 1 END) as verificados,
    COUNT(CASE WHEN a.destaque AND a.destaque_ate > NOW() THEN 1 END) as em_destaque
FROM acompanhantes a
JOIN cidades c ON c.id = a.cidade_id
JOIN estados e ON e.id = a.estado_id
GROUP BY c.nome, e.uf
ORDER BY total DESC; 