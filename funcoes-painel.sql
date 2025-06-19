-- Função para aprovar acompanhante
CREATE OR REPLACE FUNCTION aprovar_acompanhante(
    p_acompanhante_id UUID,
    p_admin_id INTEGER
) RETURNS void AS $$
BEGIN
    UPDATE acompanhantes 
    SET status = 'aprovado',
        verificado = true,
        revisado_por = p_admin_id,
        data_revisao = NOW(),
        updated_at = NOW()
    WHERE id = p_acompanhante_id;

    INSERT INTO admin_log (admin_id, acompanhante_id, acao, detalhes)
    VALUES (p_admin_id, p_acompanhante_id, 'aprovar', 'Perfil aprovado');
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Função para rejeitar acompanhante
CREATE OR REPLACE FUNCTION rejeitar_acompanhante(
    p_acompanhante_id UUID,
    p_admin_id INTEGER,
    p_motivo TEXT
) RETURNS void AS $$
BEGIN
    UPDATE acompanhantes 
    SET status = 'rejeitado',
        motivo_rejeicao = p_motivo,
        revisado_por = p_admin_id,
        data_revisao = NOW(),
        updated_at = NOW()
    WHERE id = p_acompanhante_id;

    INSERT INTO admin_log (admin_id, acompanhante_id, acao, detalhes)
    VALUES (p_admin_id, p_acompanhante_id, 'rejeitar', p_motivo);
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Função para bloquear acompanhante
CREATE OR REPLACE FUNCTION bloquear_acompanhante(
    p_acompanhante_id UUID,
    p_admin_id INTEGER,
    p_motivo TEXT
) RETURNS void AS $$
BEGIN
    UPDATE acompanhantes 
    SET status = 'bloqueado',
        bloqueado = true,
        motivo_bloqueio = p_motivo,
        revisado_por = p_admin_id,
        data_revisao = NOW(),
        updated_at = NOW()
    WHERE id = p_acompanhante_id;

    INSERT INTO admin_log (admin_id, acompanhante_id, acao, detalhes)
    VALUES (p_admin_id, p_acompanhante_id, 'bloquear', p_motivo);
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Função para destacar acompanhante
CREATE OR REPLACE FUNCTION destacar_acompanhante(
    p_acompanhante_id UUID,
    p_admin_id INTEGER,
    p_dias INTEGER
) RETURNS void AS $$
BEGIN
    UPDATE acompanhantes 
    SET destaque = true,
        destaque_ate = NOW() + (p_dias || ' days')::INTERVAL,
        updated_at = NOW()
    WHERE id = p_acompanhante_id;

    INSERT INTO admin_log (admin_id, acompanhante_id, acao, detalhes)
    VALUES (p_admin_id, p_acompanhante_id, 'destacar', 'Destaque ativado por ' || p_dias || ' dias');
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Função para verificar documento
CREATE OR REPLACE FUNCTION verificar_documento(
    p_documento_id UUID,
    p_admin_id INTEGER,
    p_aprovado BOOLEAN,
    p_motivo TEXT DEFAULT NULL
) RETURNS void AS $$
BEGIN
    UPDATE documentos_acompanhante
    SET verificado = p_aprovado,
        motivo_rejeicao = CASE WHEN NOT p_aprovado THEN p_motivo ELSE NULL END,
        verificado_por = p_admin_id,
        data_verificacao = NOW(),
        updated_at = NOW()
    WHERE id = p_documento_id;

    INSERT INTO admin_log (
        admin_id, 
        acompanhante_id,
        acao, 
        detalhes
    )
    SELECT 
        p_admin_id,
        acompanhante_id,
        CASE WHEN p_aprovado THEN 'aprovar_documento' ELSE 'rejeitar_documento' END,
        CASE WHEN p_aprovado THEN 'Documento aprovado' ELSE 'Documento rejeitado: ' || p_motivo END
    FROM documentos_acompanhante
    WHERE id = p_documento_id;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Função para verificar vídeo
CREATE OR REPLACE FUNCTION verificar_video(
    p_video_id UUID,
    p_admin_id INTEGER,
    p_aprovado BOOLEAN,
    p_motivo TEXT DEFAULT NULL
) RETURNS void AS $$
BEGIN
    UPDATE videos_verificacao
    SET verificado = p_aprovado,
        motivo_rejeicao = CASE WHEN NOT p_aprovado THEN p_motivo ELSE NULL END,
        verificado_por = p_admin_id,
        data_verificacao = NOW(),
        updated_at = NOW()
    WHERE id = p_video_id;

    INSERT INTO admin_log (
        admin_id, 
        acompanhante_id,
        acao, 
        detalhes
    )
    SELECT 
        p_admin_id,
        acompanhante_id,
        CASE WHEN p_aprovado THEN 'aprovar_video' ELSE 'rejeitar_video' END,
        CASE WHEN p_aprovado THEN 'Vídeo aprovado' ELSE 'Vídeo rejeitado: ' || p_motivo END
    FROM videos_verificacao
    WHERE id = p_video_id;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER; 