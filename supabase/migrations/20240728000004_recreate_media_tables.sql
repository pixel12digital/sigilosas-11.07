-- Este script substitui as tabelas relacionadas à mídia para garantir que tenham a estrutura correta.
-- Ele primeiro apaga as tabelas existentes se elas existirem e depois as recria do zero.
-- AVISO: Esta é uma operação destrutiva e removerá todos os dados existentes nessas tabelas.

DROP TABLE IF EXISTS public.fotos CASCADE;
DROP TABLE IF EXISTS public.videos_verificacao CASCADE;
DROP TABLE IF EXISTS public.documentos_acompanhante CASCADE;

-- Recriar a tabela 'fotos' com o esquema correto
CREATE TABLE public.fotos (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    acompanhante_id UUID NOT NULL,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    tipo TEXT NOT NULL CHECK (tipo IN ('perfil', 'galeria')),
    principal BOOLEAN DEFAULT FALSE,
    aprovada BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT fotos_acompanhante_id_fkey
        FOREIGN KEY(acompanhante_id)
        REFERENCES public.acompanhantes(id)
        ON DELETE CASCADE
);

-- Recriar a tabela 'videos_verificacao'
CREATE TABLE public.videos_verificacao (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    acompanhante_id UUID NOT NULL,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    verificado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT videos_verificacao_acompanhante_id_fkey
        FOREIGN KEY(acompanhante_id)
        REFERENCES public.acompanhantes(id)
        ON DELETE CASCADE
);

-- Recriar a tabela 'documentos_acompanhante'
CREATE TABLE public.documentos_acompanhante (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    acompanhante_id UUID NOT NULL,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    tipo TEXT NOT NULL,
    verificado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT documentos_acompanhante_id_fkey
        FOREIGN KEY(acompanhante_id)
        REFERENCES public.acompanhantes(id)
        ON DELETE CASCADE
);

-- Adicionar Políticas de Segurança em Nível de Linha (RLS)
ALTER TABLE public.fotos ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.videos_verificacao ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.documentos_acompanhante ENABLE ROW LEVEL SECURITY;

-- Políticas de administrador (com WITH CHECK para inserções/atualizações)
CREATE POLICY "Allow admin full access on fotos" ON public.fotos FOR ALL USING (public.is_admin()) WITH CHECK (public.is_admin());
CREATE POLICY "Allow admin full access on videos_verificacao" ON public.videos_verificacao FOR ALL USING (public.is_admin()) WITH CHECK (public.is_admin());
CREATE POLICY "Allow admin full access on documentos_acompanhante" ON public.documentos_acompanhante FOR ALL USING (public.is_admin()) WITH CHECK (public.is_admin());

-- Políticas de usuário para gerenciar suas próprias mídias (com WITH CHECK)
CREATE POLICY "Allow user to manage own media on fotos" ON public.fotos FOR ALL
USING (auth.uid() = (SELECT user_id FROM public.acompanhantes WHERE id = acompanhante_id))
WITH CHECK (auth.uid() = (SELECT user_id FROM public.acompanhantes WHERE id = acompanhante_id));

CREATE POLICY "Allow user to manage own media on videos_verificacao" ON public.videos_verificacao FOR ALL
USING (auth.uid() = (SELECT user_id FROM public.acompanhantes WHERE id = acompanhante_id))
WITH CHECK (auth.uid() = (SELECT user_id FROM public.acompanhantes WHERE id = acompanhante_id));

CREATE POLICY "Allow user to manage own media on documentos_acompanhante" ON public.documentos_acompanhante FOR ALL
USING (auth.uid() = (SELECT user_id FROM public.acompanhantes WHERE id = acompanhante_id))
WITH CHECK (auth.uid() = (SELECT user_id FROM public.acompanhantes WHERE id = acompanhante_id));

-- Política de acesso público para leitura de fotos aprovadas (somente SELECT)
CREATE POLICY "Allow public read access to approved photos" ON public.fotos FOR SELECT
USING (aprovada = TRUE OR principal = TRUE); 