-- Configuração do Supabase Storage para Sigilosas VIP

-- 1. Criar bucket para imagens
INSERT INTO storage.buckets (id, name, public) 
VALUES ('images', 'images', true)
ON CONFLICT (id) DO NOTHING;

-- 2. Criar bucket para documentos
INSERT INTO storage.buckets (id, name, public) 
VALUES ('documents', 'documents', false)
ON CONFLICT (id) DO NOTHING;

-- 3. Criar bucket para vídeos
INSERT INTO storage.buckets (id, name, public) 
VALUES ('videos', 'videos', false)
ON CONFLICT (id) DO NOTHING;

-- 4. Políticas para bucket de imagens (público)
CREATE POLICY "Public Access Images" ON storage.objects 
FOR SELECT USING (bucket_id = 'images');

CREATE POLICY "Authenticated users can upload images" ON storage.objects 
FOR INSERT WITH CHECK (bucket_id = 'images' AND auth.role() = 'authenticated');

CREATE POLICY "Users can update own images" ON storage.objects 
FOR UPDATE USING (bucket_id = 'images' AND auth.uid()::text = (storage.foldername(name))[1]);

CREATE POLICY "Users can delete own images" ON storage.objects 
FOR DELETE USING (bucket_id = 'images' AND auth.uid()::text = (storage.foldername(name))[1]);

-- 5. Políticas para bucket de documentos (privado)
CREATE POLICY "Authenticated users can upload documents" ON storage.objects 
FOR INSERT WITH CHECK (bucket_id = 'documents' AND auth.role() = 'authenticated');

CREATE POLICY "Users can view own documents" ON storage.objects 
FOR SELECT USING (bucket_id = 'documents' AND auth.uid()::text = (storage.foldername(name))[1]);

CREATE POLICY "Users can update own documents" ON storage.objects 
FOR UPDATE USING (bucket_id = 'documents' AND auth.uid()::text = (storage.foldername(name))[1]);

CREATE POLICY "Users can delete own documents" ON storage.objects 
FOR DELETE USING (bucket_id = 'documents' AND auth.uid()::text = (storage.foldername(name))[1]);

-- 6. Políticas para bucket de vídeos (privado)
CREATE POLICY "Authenticated users can upload videos" ON storage.objects 
FOR INSERT WITH CHECK (bucket_id = 'videos' AND auth.role() = 'authenticated');

CREATE POLICY "Users can view own videos" ON storage.objects 
FOR SELECT USING (bucket_id = 'videos' AND auth.uid()::text = (storage.foldername(name))[1]);

CREATE POLICY "Users can update own videos" ON storage.objects 
FOR UPDATE USING (bucket_id = 'videos' AND auth.uid()::text = (storage.foldername(name))[1]);

CREATE POLICY "Users can delete own videos" ON storage.objects 
FOR DELETE USING (bucket_id = 'videos' AND auth.uid()::text = (storage.foldername(name))[1]);

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