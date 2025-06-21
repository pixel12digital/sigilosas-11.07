-- Adiciona uma política de segurança para permitir a leitura pública da tabela de estados.
-- Isso é necessário para que o formulário de cadastro possa listar os estados para seleção.
CREATE POLICY "Enable public read access to all states"
ON public.estados
FOR SELECT
TO anon, authenticated
USING (true);

-- Adiciona uma política de segurança para permitir a leitura pública da tabela de cidades.
-- Isso é necessário para que o formulário de cadastro possa listar as cidades para seleção.
CREATE POLICY "Enable public read access to all cities"
ON public.cidades
FOR SELECT
TO anon, authenticated
USING (true); 