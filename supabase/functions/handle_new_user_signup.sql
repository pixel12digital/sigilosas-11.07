-- Habilita a extensão pgcrypto se ainda não estiver habilitada, necessária para o hash de senhas.
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Função para lidar com o cadastro de uma nova acompanhante.
-- Agrupa a criação do usuário, inserção na tabela 'acompanhantes' e inserção das fotos em uma única transação.
CREATE OR REPLACE FUNCTION public.handle_new_user_signup(
    -- Dados do formulário
    nome text,
    email text,
    senha text,
    telefone text,
    idade int,
    genero text,
    cidade_id uuid, -- Alterado de volta para uuid
    -- Adicione aqui todos os outros campos do formulário com seus respectivos tipos
    -- Exemplo: peso text, altura text, etnia text, etc.
    descricao text,
    foto text,
    galeria_fotos text[]
)
RETURNS uuid -- Retorna o ID do novo usuário criado
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  new_user_id uuid;
  new_acompanhante_id uuid;
  new_estado_id int;
BEGIN
  -- 0. Busca o estado_id correspondente à cidade_id
  SELECT estado_id INTO new_estado_id FROM public.cidades WHERE id = cidade_id;

  -- 1. Cria o usuário no sistema de autenticação do Supabase.
  -- O ID do novo usuário é armazenado na variável new_user_id.
  INSERT INTO auth.users (instance_id, id, aud, role, email, encrypted_password, email_confirmed_at, recovery_token, recovery_sent_at, last_sign_in_at, raw_app_meta_data, raw_user_meta_data, created_at, updated_at, phone, phone_confirmed_at, confirmation_token, confirmation_sent_at, email_change, email_change_sent_at)
  VALUES (current_setting('app.instance_id')::uuid, extensions.uuid_generate_v4(), 'authenticated', 'authenticated', email, crypt(senha, gen_salt('bf')), now(), '', null, null, '{"provider":"email","providers":["email"]}', '{}', now(), now(), telefone, now(), '', null, '', null)
  RETURNING id INTO new_user_id;

  -- 2. Insere os dados na tabela 'acompanhantes'.
  -- O ID da nova acompanhante é armazenado na variável new_acompanhante_id.
  INSERT INTO public.acompanhantes (id, user_id, nome, idade, genero, cidade_id, estado_id, descricao, foto, status)
  VALUES (extensions.uuid_generate_v4(), new_user_id, nome, idade, genero::genero_enum, cidade_id, new_estado_id, descricao, foto, 'pendente')
  RETURNING id INTO new_acompanhante_id;

  -- 3. Insere as fotos da galeria na tabela 'fotos'.
  -- Itera sobre o array de URLs de fotos e insere cada uma.
  IF array_length(galeria_fotos, 1) > 0 THEN
    FOR i IN 1..array_length(galeria_fotos, 1) LOOP
      INSERT INTO public.fotos (acompanhante_id, url, capa)
      VALUES (new_acompanhante_id, galeria_fotos[i], (i = 1)); -- Define a primeira foto como capa.
    END LOOP;
  END IF;
  
  -- Retorna o ID do usuário criado na tabela auth.users.
  RETURN new_user_id;
END;
$$; 