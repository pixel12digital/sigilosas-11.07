-- Script para corrigir e padronizar a estrutura do banco de dados
-- Este script garante que todas as tabelas estejam alinhadas com a lógica da aplicação

-- 1. Primeiro, vamos verificar e corrigir a estrutura da tabela cidades
-- Existem duas versões conflitantes: uma com UUID e estado CHAR(2), outra com SERIAL e estado_id

-- Verificar se a tabela cidades tem a estrutura correta
DO $$
BEGIN
    -- Se a tabela cidades não tem estado_id, vamos criar a estrutura correta
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'cidades' AND column_name = 'estado_id'
    ) THEN
        -- Criar tabela de estados se não existir
        CREATE TABLE IF NOT EXISTS estados (
            id SERIAL PRIMARY KEY,
            uf CHAR(2) NOT NULL UNIQUE,
            nome VARCHAR(50) NOT NULL,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
        );

        -- Inserir estados se a tabela estiver vazia
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
        ('TO', 'Tocantins')
        ON CONFLICT (uf) DO NOTHING;

        -- Criar tabela temporária para migrar dados
        CREATE TEMP TABLE cidades_temp AS 
        SELECT id, nome, estado FROM cidades;

        -- Dropar a tabela cidades atual
        DROP TABLE cidades;

        -- Recriar com a estrutura correta
        CREATE TABLE cidades (
            id SERIAL PRIMARY KEY,
            estado_id INTEGER REFERENCES estados(id),
            nome VARCHAR(100) NOT NULL,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
            UNIQUE(estado_id, nome)
        );

        -- Migrar dados
        INSERT INTO cidades (nome, estado_id)
        SELECT ct.nome, e.id
        FROM cidades_temp ct
        JOIN estados e ON e.uf = ct.estado;

        -- Criar índices
        CREATE INDEX idx_cidades_estado ON cidades(estado_id);
        CREATE INDEX idx_cidades_nome ON cidades(nome);

        RAISE NOTICE 'Tabela cidades migrada para estrutura com estado_id';
    END IF;
END $$;

-- 2.5. Habilitar RLS e criar políticas de leitura pública
DO $$
BEGIN
    -- Habilitar RLS se não estiverem habilitados
    ALTER TABLE public.estados ENABLE ROW LEVEL SECURITY;
    ALTER TABLE public.cidades ENABLE ROW LEVEL SECURITY;

    RAISE NOTICE 'RLS habilitado para estados e cidades.';

    -- Remover políticas antigas para garantir que as novas sejam aplicadas
    DROP POLICY IF EXISTS "Allow public read access to all states" ON public.estados;
    DROP POLICY IF EXISTS "Allow public read access to all cities" ON public.cidades;

    -- Remover políticas de autenticados se existirem para evitar conflitos
    DROP POLICY IF EXISTS "Allow authenticated users to read cities" ON public.cidades;

    -- Criar políticas de leitura pública (para anon e authenticated)
    CREATE POLICY "Allow public read access to all states" ON public.estados
    FOR SELECT
    TO public
    USING (true);

    CREATE POLICY "Allow public read access to all cities" ON public.cidades
    FOR SELECT
    TO public
    USING (true);

    RAISE NOTICE 'Políticas de leitura pública criadas para estados e cidades.';
END $$;

-- 2. Verificar e corrigir a tabela acompanhantes
-- Garantir que tenha cidade_id e estado_id

DO $$
BEGIN
    -- Se a tabela acompanhantes não tem cidade_id, vamos adicionar
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'acompanhantes' AND column_name = 'cidade_id'
    ) THEN
        -- Adicionar colunas se não existirem
        ALTER TABLE acompanhantes ADD COLUMN IF NOT EXISTS cidade_id INTEGER REFERENCES cidades(id);
        ALTER TABLE acompanhantes ADD COLUMN IF NOT EXISTS estado_id INTEGER REFERENCES estados(id);
        
        RAISE NOTICE 'Colunas cidade_id e estado_id adicionadas à tabela acompanhantes';
    END IF;
END $$;

-- 3. Verificar e corrigir a tabela fotos
-- Garantir que tenha os campos corretos

DO $$
BEGIN
    -- Se a tabela fotos não tem o campo principal, vamos adicionar
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'fotos' AND column_name = 'principal'
    ) THEN
        -- Adicionar coluna principal se não existir
        ALTER TABLE fotos ADD COLUMN IF NOT EXISTS principal BOOLEAN DEFAULT false;
        
        -- Se existe o campo capa, migrar dados
        IF EXISTS (
            SELECT 1 FROM information_schema.columns 
            WHERE table_name = 'fotos' AND column_name = 'capa'
        ) THEN
            UPDATE fotos SET principal = capa WHERE capa = true;
            ALTER TABLE fotos DROP COLUMN IF EXISTS capa;
        END IF;
        
        RAISE NOTICE 'Campo principal adicionado à tabela fotos';
    END IF;

    -- Garantir que o campo tipo existe
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'fotos' AND column_name = 'tipo'
    ) THEN
        ALTER TABLE fotos ADD COLUMN IF NOT EXISTS tipo foto_tipo_enum DEFAULT 'galeria';
        RAISE NOTICE 'Campo tipo adicionado à tabela fotos';
    END IF;

    -- Garantir que o campo aprovada existe
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'fotos' AND column_name = 'aprovada'
    ) THEN
        ALTER TABLE fotos ADD COLUMN IF NOT EXISTS aprovada BOOLEAN DEFAULT false;
        RAISE NOTICE 'Campo aprovada adicionado à tabela fotos';
    END IF;
END $$;

-- 4. Verificar e corrigir a tabela videos_verificacao
-- Garantir que tenha os campos corretos

DO $$
BEGIN
    -- Garantir que o campo storage_path existe
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'videos_verificacao' AND column_name = 'storage_path'
    ) THEN
        ALTER TABLE videos_verificacao ADD COLUMN IF NOT EXISTS storage_path TEXT;
        RAISE NOTICE 'Campo storage_path adicionado à tabela videos_verificacao';
    END IF;
END $$;

-- 5. Criar ou atualizar a view vw_cidades_estados
CREATE OR REPLACE VIEW vw_cidades_estados AS
SELECT 
    c.id as cidade_id,
    c.nome as cidade,
    e.id as estado_id,
    TRIM(e.uf) as estado_uf,
    e.nome as estado_nome,
    c.nome || ' - ' || TRIM(e.uf) as cidade_estado
FROM cidades c
JOIN estados e ON e.id = c.estado_id
ORDER BY e.uf, c.nome;

-- 6. Atualizar a view vw_painel_acompanhantes
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
    c.nome as cidade,
    e.uf as estado,
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
    COALESCE(f.total_fotos, 0) as total_fotos,
    COALESCE(d.total_documentos, 0) as total_documentos,
    COALESCE(v.total_videos, 0) as total_videos,
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
LEFT JOIN cidades c ON c.id = a.cidade_id
LEFT JOIN estados e ON e.id = a.estado_id
LEFT JOIN (
    SELECT acompanhante_id, COUNT(*) as total_fotos 
    FROM fotos 
    GROUP BY acompanhante_id
) f ON f.acompanhante_id = a.id
LEFT JOIN (
    SELECT acompanhante_id, COUNT(*) as total_documentos 
    FROM documentos_acompanhante 
    GROUP BY acompanhante_id
) d ON d.acompanhante_id = a.id
LEFT JOIN (
    SELECT acompanhante_id, COUNT(*) as total_videos 
    FROM videos_verificacao 
    GROUP BY acompanhante_id
) v ON v.acompanhante_id = a.id
ORDER BY 
    CASE 
        WHEN a.status = 'pendente' THEN 0
        WHEN a.status = 'aprovado' AND a.destaque THEN 1
        WHEN a.status = 'aprovado' THEN 2
        ELSE 3
    END,
    a.created_at DESC;

-- 7. Garantir que a função handle_new_user_signup está correta
-- (Esta função já foi atualizada no arquivo supabase/functions/handle_new_user_signup.sql)

-- 8. Criar índices importantes se não existirem
CREATE INDEX IF NOT EXISTS idx_acompanhantes_cidade_id ON acompanhantes(cidade_id);
CREATE INDEX IF NOT EXISTS idx_acompanhantes_estado_id ON acompanhantes(estado_id);
CREATE INDEX IF NOT EXISTS idx_acompanhantes_status ON acompanhantes(status);
CREATE INDEX IF NOT EXISTS idx_acompanhantes_verificado ON acompanhantes(verificado);
CREATE INDEX IF NOT EXISTS idx_fotos_acompanhante_id ON fotos(acompanhante_id);
CREATE INDEX IF NOT EXISTS idx_videos_acompanhante_id ON videos_verificacao(acompanhante_id);

-- 9. Verificar se os tipos enumerados existem
DO $$
BEGIN
    -- Criar tipos se não existirem
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_enum') THEN
        CREATE TYPE status_enum AS ENUM ('pendente', 'aprovado', 'rejeitado', 'bloqueado');
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'genero_enum') THEN
        CREATE TYPE genero_enum AS ENUM ('feminino', 'masculino', 'trans', 'outro');
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'foto_tipo_enum') THEN
        CREATE TYPE foto_tipo_enum AS ENUM ('perfil', 'galeria', 'verificacao');
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'documento_tipo_enum') THEN
        CREATE TYPE documento_tipo_enum AS ENUM ('rg', 'cnh', 'selfie');
    END IF;
END $$;

-- 10. Verificar se as extensões necessárias estão habilitadas
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Mensagem de conclusão
SELECT 'Estrutura do banco de dados corrigida e padronizada com sucesso!' as status; 