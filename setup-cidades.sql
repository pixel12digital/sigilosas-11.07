-- Criar a tabela de cidades
CREATE TABLE IF NOT EXISTS public.cidades (
    id uuid NOT NULL DEFAULT gen_random_uuid(),
    nome text NOT NULL,
    estado character(2) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT cidades_pkey PRIMARY KEY (id),
    CONSTRAINT cidades_nome_estado_key UNIQUE (nome, estado)
);

-- Comentários nas colunas
COMMENT ON TABLE public.cidades IS 'Tabela para armazenar as cidades disponíveis no sistema';
COMMENT ON COLUMN public.cidades.id IS 'Identificador único da cidade';
COMMENT ON COLUMN public.cidades.nome IS 'Nome da cidade';
COMMENT ON COLUMN public.cidades.estado IS 'Sigla do estado (UF) com 2 caracteres';
COMMENT ON COLUMN public.cidades.created_at IS 'Data e hora de criação do registro';

-- Habilitar RLS (Row Level Security)
ALTER TABLE public.cidades ENABLE ROW LEVEL SECURITY;

-- Remover políticas existentes (se houver)
DROP POLICY IF EXISTS "Allow authenticated users to read cities" ON public.cidades;
DROP POLICY IF EXISTS "Allow admin users to insert cities" ON public.cidades;
DROP POLICY IF EXISTS "Allow admin users to delete cities" ON public.cidades;

-- Criar políticas de segurança
CREATE POLICY "Allow authenticated users to read cities"
ON public.cidades
FOR SELECT
TO authenticated
USING (true);

CREATE POLICY "Allow admin users to insert cities"
ON public.cidades
FOR INSERT
TO authenticated
WITH CHECK (
    auth.jwt() ->> 'role' = 'admin'
);

CREATE POLICY "Allow admin users to delete cities"
ON public.cidades
FOR DELETE
TO authenticated
USING (
    auth.jwt() ->> 'role' = 'admin'
);

-- Garantir que a função auth.role() existe
CREATE OR REPLACE FUNCTION auth.role()
RETURNS text
LANGUAGE sql
STABLE
AS $$
  SELECT coalesce(nullif(current_setting('request.jwt.claim.role', true), ''), 'authenticated')::text;
$$; 