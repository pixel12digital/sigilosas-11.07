-- Remove o gatilho antigo se ele existir, para evitar duplicação.
DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;

-- Habilita a extensão pgcrypto, se ainda não estiver habilitada.
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Remove a função antiga que tentava criar um usuário manualmente.
DROP FUNCTION IF EXISTS public.handle_new_user_signup(text, text, text, text, int, text, int, text, text, text[], text);
DROP FUNCTION IF EXISTS public.handle_new_user_signup(text, text, text, text, int, text, uuid, text, text, text[], text);
DROP FUNCTION IF EXISTS public.handle_new_user_signup(); -- Remove a versão sem argumentos se existir

-- Cria a nova função que será usada como um gatilho (trigger).
CREATE OR REPLACE FUNCTION public.handle_new_user_signup()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_acompanhante_id uuid;
  v_cidade_id uuid;
  v_estado_id int;
  v_meta_data jsonb;
  v_foto_url_item text;
  v_sql_state text;
  v_message_text text;
  v_context text;
BEGIN
  -- Log inicial
  INSERT INTO public.trigger_debug_log (log_message) VALUES ('Iniciando gatilho handle_new_user_signup para o usuário: ' || NEW.email);

  v_acompanhante_id := NEW.id;
  v_meta_data := NEW.raw_user_meta_data;
  INSERT INTO public.trigger_debug_log (log_message) VALUES ('Metadados do usuário extraídos: ' || v_meta_data::text);

  v_cidade_id := (v_meta_data->>'cidade_id')::uuid;
  INSERT INTO public.trigger_debug_log (log_message) VALUES ('ID da cidade extraído: ' || v_cidade_id);

  SELECT estado_id INTO v_estado_id FROM public.cidades WHERE id = v_cidade_id;
  INSERT INTO public.trigger_debug_log (log_message) VALUES ('ID do estado buscado: ' || v_estado_id);

  -- Bloco de exceção para a inserção principal
  BEGIN
    INSERT INTO public.trigger_debug_log (log_message) VALUES ('Tentando inserir na tabela acompanhantes...');
    INSERT INTO public.acompanhantes (id, user_id, nome, email, telefone, idade, genero, cidade_id, estado_id, descricao, status)
    VALUES (
      v_acompanhante_id,
      NEW.id,
      v_meta_data->>'nome',
      NEW.email,
      NEW.phone,
      (v_meta_data->>'idade')::int,
      v_meta_data->>'genero',
      v_cidade_id,
      v_estado_id,
      v_meta_data->>'descricao',
      'pendente'
    );
    INSERT INTO public.trigger_debug_log (log_message) VALUES ('Inserção na tabela acompanhantes bem-sucedida.');
  EXCEPTION
    WHEN others THEN
      GET STACKED DIAGNOSTICS
        v_sql_state = RETURNED_SQLSTATE,
        v_message_text = MESSAGE_TEXT,
        v_context = PG_EXCEPTION_CONTEXT;
      INSERT INTO public.trigger_debug_log (log_message)
      VALUES (
        'ERRO AO INSERIR EM ACOMPANHANTES: ' ||
        'SQLSTATE: ' || v_sql_state || ' | ' ||
        'MENSAGEM: ' || v_message_text || ' | ' ||
        'CONTEXTO: ' || v_context
      );
      RAISE; -- Re-lança a exceção para que a transação seja desfeita
  END;

  IF v_meta_data->>'foto' IS NOT NULL THEN
    INSERT INTO public.trigger_debug_log (log_message) VALUES ('Tentando inserir foto de perfil...');
    INSERT INTO public.fotos (acompanhante_id, url, storage_path, tipo, principal, aprovada)
    VALUES (v_acompanhante_id, v_meta_data->>'foto', v_meta_data->>'foto', 'perfil', true, false);
    INSERT INTO public.trigger_debug_log (log_message) VALUES ('Foto de perfil inserida.');
  END IF;

  IF jsonb_array_length(v_meta_data->'galeria_fotos') > 0 THEN
    INSERT INTO public.trigger_debug_log (log_message) VALUES ('Tentando inserir fotos da galeria...');
    FOR v_foto_url_item IN SELECT jsonb_array_elements_text(v_meta_data->'galeria_fotos')
    LOOP
      INSERT INTO public.fotos (acompanhante_id, url, storage_path, tipo, principal, aprovada)
      VALUES (v_acompanhante_id, v_foto_url_item, v_foto_url_item, 'galeria', false, false);
    END LOOP;
    INSERT INTO public.trigger_debug_log (log_message) VALUES ('Fotos da galeria inseridas.');
  END IF;

  IF v_meta_data->>'video_url' IS NOT NULL THEN
     INSERT INTO public.trigger_debug_log (log_message) VALUES ('Tentando inserir vídeo...');
    INSERT INTO public.videos_verificacao (acompanhante_id, url, storage_path, verificado)
    VALUES (v_acompanhante_id, v_meta_data->>'video_url', v_meta_data->>'video_url', false);
     INSERT INTO public.trigger_debug_log (log_message) VALUES ('Vídeo inserido.');
  END IF;

  INSERT INTO public.trigger_debug_log (log_message) VALUES ('Gatilho concluído com sucesso.');
  RETURN NEW;
END;
$$;

-- Cria o gatilho que executa a função 'handle_new_user_signup'
-- sempre que um novo usuário é criado na tabela 'auth.users'.
CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION public.handle_new_user_signup();