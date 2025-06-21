-- Drop a função se ela já existir, para facilitar o desenvolvimento
DROP FUNCTION IF EXISTS public.cadastrar_perfil_completo_de_acompanhante(uuid, text, text, text, integer, text, integer, text, text, text, numeric, numeric, text, text, text, text, text, boolean, boolean, boolean, boolean, text, text, text, text, text, text, text, text[], text, jsonb);

-- Criação da função transacional para cadastro completo
CREATE OR REPLACE FUNCTION public.cadastrar_perfil_completo_de_acompanhante(
    p_user_id uuid,
    p_email text,
    p_nome text,
    p_telefone text,
    p_idade integer,
    p_genero text,
    p_cidade_id uuid, -- <<-- CORRIGIDO de integer para uuid
    p_descricao text,
    p_genitalia text,
    p_preferencia_sexual text,
    p_peso numeric,
    p_altura numeric,
    p_etnia text,
    p_cor_dos_olhos text,
    p_estilo_cabelo text,
    p_tamanho_cabelo text,
    p_tamanho_pe text,
    p_fumante boolean,
    p_silicone boolean,
    p_tatuagens boolean,
    p_piercings boolean,
    p_idiomas text,
    p_endereco text,
    p_atende text,
    p_horario_expediente text,
    p_formas_pagamento text,
    p_clientes_em_conjunto text,
    p_foto_url text,
    p_galeria_fotos_urls text[],
    p_video_url text,
    p_documentos jsonb
)
RETURNS void AS $$
DECLARE
    v_estado_id integer; -- Mantido como integer, conforme schema de estados
    foto_url text;
    doc record;
BEGIN
    -- 1. Obter o estado_id a partir da cidade_id
    SELECT estado_id INTO v_estado_id FROM public.cidades WHERE id = p_cidade_id;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Cidade com id % não encontrada', p_cidade_id;
    END IF;

    -- 2. Inserir os dados na tabela principal 'acompanhantes'
    INSERT INTO public.acompanhantes (
        id, user_id, email, nome, telefone, idade, genero, cidade_id, estado_id,
        descricao, status, genitalia, preferencia_sexual, peso, altura, etnia,
        cor_dos_olhos, estilo_cabelo, tamanho_cabelo, tamanho_pe, fumante,
        silicone, tatuagens, piercings, idiomas, endereco, atende,
        horario_expediente, formas_pagamento, clientes_em_conjunto
    ) VALUES (
        p_user_id, p_user_id, p_email, p_nome, p_telefone, p_idade, p_genero, p_cidade_id, v_estado_id,
        p_descricao, 'pendente', p_genitalia, p_preferencia_sexual, p_peso, p_altura, p_etnia,
        p_cor_dos_olhos, p_estilo_cabelo, p_tamanho_cabelo, p_tamanho_pe, p_fumante,
        p_silicone, p_tatuagens, p_piercings, p_idiomas, p_endereco, p_atende,
        p_horario_expediente, p_formas_pagamento, p_clientes_em_conjunto
    );

    -- 3. Inserir a foto de perfil (se fornecida)
    IF p_foto_url IS NOT NULL AND p_foto_url <> '' THEN
        INSERT INTO public.fotos (acompanhante_id, url, storage_path, tipo, principal, aprovada)
        VALUES (p_user_id, p_foto_url, p_foto_url, 'perfil', true, false);
    END IF;

    -- 4. Inserir as fotos da galeria (se fornecidas)
    IF p_galeria_fotos_urls IS NOT NULL AND array_length(p_galeria_fotos_urls, 1) > 0 THEN
        FOREACH foto_url IN ARRAY p_galeria_fotos_urls
        LOOP
            INSERT INTO public.fotos (acompanhante_id, url, storage_path, tipo, principal, aprovada)
            VALUES (p_user_id, foto_url, foto_url, 'galeria', false, false);
        END LOOP;
    END IF;

    -- 5. Inserir o vídeo de verificação (se fornecido)
    IF p_video_url IS NOT NULL AND p_video_url <> '' THEN
        INSERT INTO public.videos_verificacao (acompanhante_id, url, storage_path, verificado)
        VALUES (p_user_id, p_video_url, p_video_url, false);
    END IF;

    -- 6. Inserir os documentos (se fornecidos)
    IF p_documentos IS NOT NULL AND jsonb_array_length(p_documentos) > 0 THEN
        FOR doc IN SELECT * FROM jsonb_to_recordset(p_documentos) AS x(path text, tipo text)
        LOOP
            INSERT INTO public.documentos_acompanhante (acompanhante_id, url, storage_path, tipo, verificado)
            VALUES (p_user_id, doc.path, doc.path, doc.tipo, false);
        END LOOP;
    END IF;

END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

COMMENT ON FUNCTION public.cadastrar_perfil_completo_de_acompanhante(uuid, text, text, text, integer, text, uuid, text, text, text, numeric, numeric, text, text, text, text, text, boolean, boolean, boolean, boolean, text, text, text, text, text, text, text, text[], text, jsonb)
IS 'Função transacional para realizar o cadastro completo de um perfil de acompanhante, incluindo dados principais e mídias. Garante atomicidade. O tipo de p_cidade_id foi corrigido para UUID.'; 