-- Remove a restrição de verificação existente na coluna 'tipo' para que possa ser recriada.
-- O uso de IF EXISTS garante que o script não falhe se o nome da restrição for diferente.
ALTER TABLE public.fotos DROP CONSTRAINT IF EXISTS fotos_tipo_check;

-- Adiciona a nova restrição de verificação para incluir o tipo 'documento',
-- unificando o armazenamento de todas as imagens importantes na mesma tabela.
ALTER TABLE public.fotos
ADD CONSTRAINT fotos_tipo_check CHECK (tipo IN ('perfil', 'galeria', 'documento')); 