-- PASSO 1: Remover as views que dependem da tabela 'acompanhantes'.
DROP VIEW IF EXISTS public.vw_log_atividades;
DROP VIEW IF EXISTS public.vw_painel_acompanhantes;

-- PASSO 2: Remover as Políticas de Segurança (RLS) que dependem da tabela.
DROP POLICY IF EXISTS "Acompanhantes podem ver suas próprias fotos" ON public.fotos;
DROP POLICY IF EXISTS "Acompanhantes podem gerenciar suas próprias fotos" ON public.fotos;
DROP POLICY IF EXISTS "Acompanhantes podem ver seus próprios documentos" ON public.documentos_acompanhante;
DROP POLICY IF EXISTS "Acompanhantes podem gerenciar seus próprios documentos" ON public.documentos_acompanhante;
DROP POLICY IF EXISTS "Acompanhantes podem ver seus próprios vídeos" ON public.videos_verificacao;
DROP POLICY IF EXISTS "Acompanhantes podem gerenciar seus próprios vídeos" ON public.videos_verificacao;


-- PASSO 3: Renomear a tabela 'acompanhantes' existente para preservar os dados.
ALTER TABLE public.acompanhantes RENAME TO acompanhantes_old;


-- PASSO 4: Criar a nova tabela 'acompanhantes' com a estrutura 100% correta.
CREATE TABLE public.acompanhantes (
    id UUID PRIMARY KEY,
    user_id UUID REFERENCES auth.users(id) ON DELETE SET NULL,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    idade SMALLINT NOT NULL CHECK (idade >= 18),
    genero TEXT,
    cidade_id UUID REFERENCES public.cidades(id),
    estado_id UUID, -- Referência a estados.id, que também deve ser UUID
    descricao TEXT,
    status VARCHAR(50) DEFAULT 'pendente',
    destaque BOOLEAN DEFAULT false,
    nota_media NUMERIC(3, 2) DEFAULT 0.00,
    total_avaliacoes INTEGER DEFAULT 0,
    disponivel BOOLEAN DEFAULT true,
    data_criacao TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    data_atualizacao TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    genitalia TEXT,
    preferencia_sexual TEXT,
    peso NUMERIC(5, 2),
    altura NUMERIC(5, 2),
    etnia TEXT,
    cor_dos_olhos TEXT,
    estilo_cabelo TEXT,
    tamanho_cabelo TEXT,
    tamanho_pe VARCHAR(10),
    fumante BOOLEAN,
    silicone BOOLEAN,
    tatuagens BOOLEAN,
    piercings BOOLEAN,
    idiomas TEXT,
    endereco TEXT,
    atende TEXT,
    horario_expediente TEXT,
    formas_pagamento TEXT,
    clientes_em_conjunto TEXT
);

COMMENT ON COLUMN public.acompanhantes.id IS 'Chave primária UUID, sincronizada com o auth.users.id do usuário correspondente.';


-- PASSO 5: Recriar a view 'vw_painel_acompanhantes' para apontar para a nova tabela.
CREATE OR REPLACE VIEW public.vw_painel_acompanhantes AS
SELECT 
    a.id,
    a.nome,
    a.idade,
    a.status,
    a.destaque,
    c.nome as cidade,
    e.uf as estado,
    (SELECT url FROM fotos f WHERE f.acompanhante_id = a.id AND f.principal = true LIMIT 1) as foto_principal
FROM 
    public.acompanhantes a
JOIN 
    public.cidades c ON a.cidade_id = c.id
JOIN 
    public.estados e ON a.estado_id = e.id;


-- PASSO 6: Recriar as Políticas de Segurança (RLS).
CREATE POLICY "Acompanhantes podem ver suas próprias fotos" ON public.fotos FOR SELECT USING (acompanhante_id IN (SELECT id FROM public.acompanhantes WHERE user_id = auth.uid()));
CREATE POLICY "Acompanhantes podem gerenciar suas próprias fotos" ON public.fotos FOR ALL USING (acompanhante_id IN (SELECT id FROM public.acompanhantes WHERE user_id = auth.uid()));
CREATE POLICY "Acompanhantes podem ver seus próprios documentos" ON public.documentos_acompanhante FOR SELECT USING (acompanhante_id IN (SELECT id FROM public.acompanhantes WHERE user_id = auth.uid()));
CREATE POLICY "Acompanhantes podem gerenciar seus próprios documentos" ON public.documentos_acompanhante FOR ALL USING (acompanhante_id IN (SELECT id FROM public.acompanhantes WHERE user_id = auth.uid()));
CREATE POLICY "Acompanhantes podem ver seus próprios vídeos" ON public.videos_verificacao FOR SELECT USING (acompanhante_id IN (SELECT id FROM public.acompanhantes WHERE user_id = auth.uid()));
CREATE POLICY "Acompanhantes podem gerenciar seus próprios vídeos" ON public.videos_verificacao FOR ALL USING (acompanhante_id IN (SELECT id FROM public.acompanhantes WHERE user_id = auth.uid()));


-- PASSO 7: A view 'vw_log_atividades' não será recriada pois sua tabela base não existe.
-- Fica comentado para referência futura.
-- CREATE OR REPLACE VIEW public.vw_log_atividades AS ... 