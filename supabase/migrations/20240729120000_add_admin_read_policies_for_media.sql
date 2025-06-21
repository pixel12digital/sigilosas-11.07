-- Adiciona as políticas de RLS que permitem aos administradores ler todas as mídias.

-- Garante que o RLS esteja ativado nas tabelas.
-- Não causa erro se já estiverem ativas.
ALTER TABLE public.videos_verificacao ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.documentos_acompanhante ENABLE ROW LEVEL SECURITY;

-- Remove políticas antigas (se existirem) para evitar conflitos.
DROP POLICY IF EXISTS "Admins can select all verification videos" ON public.videos_verificacao;
DROP POLICY IF EXISTS "Admins can select all documents" ON public.documentos_acompanhante;

-- Cria as novas políticas de leitura para administradores.
-- A função is_admin() não recebe parâmetros e usa auth.uid() internamente.
CREATE POLICY "Admins can select all verification videos"
ON public.videos_verificacao FOR SELECT
TO authenticated
USING (is_admin());

CREATE POLICY "Admins can select all documents"
ON public.documentos_acompanhante FOR SELECT
TO authenticated
USING (is_admin()); 