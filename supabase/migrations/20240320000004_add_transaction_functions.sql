-- Função para iniciar uma transação
CREATE OR REPLACE FUNCTION begin_transaction()
RETURNS void
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  -- Inicia uma nova transação
  BEGIN;
END;
$$;

-- Função para commit de uma transação
CREATE OR REPLACE FUNCTION commit_transaction()
RETURNS void
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  -- Commit da transação atual
  COMMIT;
END;
$$;

-- Função para rollback de uma transação
CREATE OR REPLACE FUNCTION rollback_transaction()
RETURNS void
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  -- Rollback da transação atual
  ROLLBACK;
END;
$$;

-- Função para limpar dados órfãos
CREATE OR REPLACE FUNCTION cleanup_orphaned_data()
RETURNS void
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  -- Deletar fotos sem acompanhante
  DELETE FROM storage.objects
  WHERE bucket_id = 'images'
  AND path LIKE 'acompanhantes/%'
  AND path NOT IN (
    SELECT f.url
    FROM fotos f
    JOIN acompanhantes a ON f.acompanhante_id = a.id
  );

  -- Deletar documentos sem acompanhante
  DELETE FROM storage.objects
  WHERE bucket_id = 'documents'
  AND path LIKE 'acompanhantes/%'
  AND path NOT IN (
    SELECT d.url
    FROM documentos_acompanhante d
    JOIN acompanhantes a ON d.acompanhante_id = a.id
  );

  -- Deletar vídeos sem acompanhante
  DELETE FROM storage.objects
  WHERE bucket_id = 'videos'
  AND path LIKE 'acompanhantes/%'
  AND path NOT IN (
    SELECT v.url
    FROM videos_verificacao v
    JOIN acompanhantes a ON v.acompanhante_id = a.id
  );

  -- Deletar fotos sem acompanhante
  DELETE FROM fotos
  WHERE acompanhante_id NOT IN (SELECT id FROM acompanhantes);

  -- Deletar acompanhantes sem usuário auth
  DELETE FROM acompanhantes
  WHERE user_id IS NOT NULL 
  AND user_id NOT IN (SELECT id::text FROM auth.users);

  -- Deletar documentos sem acompanhante
  DELETE FROM documentos_acompanhante
  WHERE acompanhante_id NOT IN (SELECT id FROM acompanhantes);

  -- Deletar vídeos sem acompanhante
  DELETE FROM videos_verificacao
  WHERE acompanhante_id NOT IN (SELECT id FROM acompanhantes);

  -- Deletar avaliações sem acompanhante
  DELETE FROM avaliacoes
  WHERE acompanhante_id NOT IN (SELECT id FROM acompanhantes);

  -- Deletar visualizações sem acompanhante
  DELETE FROM visualizacoes
  WHERE acompanhante_id NOT IN (SELECT id FROM acompanhantes);

  -- Deletar relações acompanhante-tag sem acompanhante
  DELETE FROM acompanhante_tag
  WHERE acompanhante_id NOT IN (SELECT id FROM acompanhantes);

  -- Deletar relações acompanhante-serviço sem acompanhante
  DELETE FROM acompanhante_servico
  WHERE acompanhante_id NOT IN (SELECT id FROM acompanhantes);
END;
$$;

-- Trigger para limpar dados órfãos automaticamente
CREATE OR REPLACE FUNCTION trigger_cleanup_orphaned_data()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  PERFORM cleanup_orphaned_data();
  RETURN NEW;
END;
$$;

-- Criar trigger para executar após deleção de acompanhante
DROP TRIGGER IF EXISTS cleanup_after_delete ON acompanhantes;
CREATE TRIGGER cleanup_after_delete
  AFTER DELETE ON acompanhantes
  FOR EACH STATEMENT
  EXECUTE FUNCTION trigger_cleanup_orphaned_data();

-- Criar trigger para executar após deleção de usuário auth
DROP TRIGGER IF EXISTS cleanup_after_auth_delete ON auth.users;
CREATE TRIGGER cleanup_after_auth_delete
  AFTER DELETE ON auth.users
  FOR EACH STATEMENT
  EXECUTE FUNCTION trigger_cleanup_orphaned_data(); 