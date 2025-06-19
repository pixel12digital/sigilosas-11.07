-- Configuração do Supabase Storage para Sigilosas VIP

-- 1. Criar buckets
INSERT INTO storage.buckets (id, name, public)
VALUES 
('images', 'images', true),
('documents', 'documents', false),
('videos', 'videos', false)
ON CONFLICT (id) DO NOTHING;

-- 2. Políticas para bucket de imagens (público)
CREATE POLICY "Imagens são publicamente visíveis"
ON storage.objects FOR SELECT
USING ( bucket_id = 'images' );

CREATE POLICY "Usuários autenticados podem fazer upload de imagens"
ON storage.objects FOR INSERT
WITH CHECK ( 
    bucket_id = 'images' 
    AND auth.role() IN ('authenticated', 'admin')
);

CREATE POLICY "Usuários podem gerenciar suas próprias imagens"
ON storage.objects FOR UPDATE
USING ( 
    bucket_id = 'images'
    AND owner = auth.uid()
);

CREATE POLICY "Usuários podem deletar suas próprias imagens"
ON storage.objects FOR DELETE
USING ( 
    bucket_id = 'images'
    AND owner = auth.uid()
);

-- 3. Políticas para bucket de documentos (privado)
CREATE POLICY "Apenas admin pode ver documentos"
ON storage.objects FOR SELECT
USING ( 
    bucket_id = 'documents' 
    AND auth.role() = 'admin'
);

CREATE POLICY "Usuários autenticados podem fazer upload de documentos"
ON storage.objects FOR INSERT
WITH CHECK ( 
    bucket_id = 'documents' 
    AND auth.role() IN ('authenticated', 'admin')
);

CREATE POLICY "Apenas admin pode gerenciar documentos"
ON storage.objects FOR UPDATE
USING ( 
    bucket_id = 'documents'
    AND auth.role() = 'admin'
);

CREATE POLICY "Apenas admin pode deletar documentos"
ON storage.objects FOR DELETE
USING ( 
    bucket_id = 'documents'
    AND auth.role() = 'admin'
);

-- 4. Políticas para bucket de vídeos (privado)
CREATE POLICY "Apenas admin pode ver vídeos"
ON storage.objects FOR SELECT
USING ( 
    bucket_id = 'videos' 
    AND auth.role() = 'admin'
);

CREATE POLICY "Usuários autenticados podem fazer upload de vídeos"
ON storage.objects FOR INSERT
WITH CHECK ( 
    bucket_id = 'videos' 
    AND auth.role() IN ('authenticated', 'admin')
);

CREATE POLICY "Apenas admin pode gerenciar vídeos"
ON storage.objects FOR UPDATE
USING ( 
    bucket_id = 'videos'
    AND auth.role() = 'admin'
);

CREATE POLICY "Apenas admin pode deletar vídeos"
ON storage.objects FOR DELETE
USING ( 
    bucket_id = 'videos'
    AND auth.role() = 'admin'
);

-- 7. Função para gerar URLs de upload
CREATE OR REPLACE FUNCTION generate_upload_url(
  bucket_name text,
  file_path text,
  file_type text DEFAULT 'image/jpeg'
)
RETURNS text
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  upload_url text;
BEGIN
  -- Gerar URL de upload assinada
  SELECT storage.sign(
    'put',
    bucket_name,
    file_path,
    '3600', -- 1 hora de expiração
    '{"Content-Type": "' || file_type || '"}'
  ) INTO upload_url;
  
  RETURN upload_url;
END;
$$;

-- 8. Função para obter URL pública
CREATE OR REPLACE FUNCTION get_public_url(
  bucket_name text,
  file_path text
)
RETURNS text
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  RETURN storage.url(bucket_name, file_path);
END;
$$; 