-- Permitir que administradores possam inserir registros para cidades
CREATE POLICY "Administradores podem inserir cidades" ON acompanhantes
  FOR INSERT 
  WITH CHECK (
    auth.role() = 'authenticated' AND 
    EXISTS (
      SELECT 1 FROM admin a
      WHERE a.usuario = auth.email()
      AND a.ativo = true
    )
  );

-- Permitir que administradores possam ver todas as cidades
CREATE POLICY "Administradores podem ver cidades" ON acompanhantes
  FOR SELECT 
  USING (
    auth.role() = 'authenticated' AND 
    EXISTS (
      SELECT 1 FROM admin a
      WHERE a.usuario = auth.email()
      AND a.ativo = true
    )
  ); 