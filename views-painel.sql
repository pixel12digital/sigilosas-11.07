-- View principal do painel de acompanhantes
CREATE OR REPLACE VIEW vw_painel_acompanhantes AS
SELECT 
    a.id,
    a.user_id,
    a.nome,
    a.email,
    a.telefone,
    a.idade,
    a.whatsapp,
    a.telegram,
    a.genero,
    a.genitalia,
    a.preferencia_sexual,
    c.nome as cidade,
    e.uf as estado,
    a.bairro,
    a.endereco,
    a.cep,
    a.peso,
    a.altura,
    a.manequim,
    a.etnia,
    a.cor_olhos,
    a.cor_cabelo,
    a.estilo_cabelo,
    a.tamanho_cabelo,
    a.tamanho_pe,
    a.busto,
    a.cintura,
    a.quadril,
    a.silicone,
    a.tatuagens,
    a.piercings,
    a.fumante,
    a.local_atendimento,
    a.formas_pagamento,
    a.horario_atendimento,
    a.valor_padrao,
    a.valor_promocional,
    a.idiomas,
    a.especialidades,
    a.descricao,
    a.sobre_mim,
    a.instagram,
    a.twitter,
    a.tiktok,
    a.site,
    a.status,
    a.verificado,
    a.destaque,
    a.destaque_ate,
    a.bloqueado,
    a.motivo_bloqueio,
    a.motivo_rejeicao,
    a.revisado_por,
    a.data_revisao,
    COALESCE(f.total_fotos, 0) as total_fotos,
    COALESCE(d.total_documentos, 0) as total_documentos,
    COALESCE(v.total_videos, 0) as total_videos,
    a.created_at,
    a.updated_at,
    a.ultimo_login,
    a.ultima_atualizacao,
    CASE 
        WHEN a.status = 'pendente' THEN true 
        ELSE false 
    END as requer_revisao,
    CASE
        WHEN a.destaque AND a.destaque_ate > NOW() THEN true
        ELSE false
    END as destaque_ativo
FROM acompanhantes a
LEFT JOIN cidades c ON a.cidade_id = c.id
LEFT JOIN estados e ON a.estado_id = e.id
LEFT JOIN (
    SELECT acompanhante_id, COUNT(*) as total_fotos 
    FROM fotos 
    GROUP BY acompanhante_id
) f ON f.acompanhante_id = a.id
LEFT JOIN (
    SELECT acompanhante_id, COUNT(*) as total_documentos 
    FROM documentos_acompanhante 
    GROUP BY acompanhante_id
) d ON d.acompanhante_id = a.id
LEFT JOIN (
    SELECT acompanhante_id, COUNT(*) as total_videos 
    FROM videos_verificacao 
    GROUP BY acompanhante_id
) v ON v.acompanhante_id = a.id
ORDER BY 
    CASE 
        WHEN a.status = 'pendente' THEN 0
        WHEN a.status = 'aprovado' AND a.destaque THEN 1
        WHEN a.status = 'aprovado' THEN 2
        ELSE 3
    END,
    a.created_at DESC;

-- View de estatísticas gerais
CREATE OR REPLACE VIEW vw_estatisticas_gerais AS
SELECT
    COUNT(*) as total_acompanhantes,
    COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
    COUNT(CASE WHEN status = 'aprovado' THEN 1 END) as aprovados,
    COUNT(CASE WHEN status = 'rejeitado' THEN 1 END) as rejeitados,
    COUNT(CASE WHEN status = 'bloqueado' THEN 1 END) as bloqueados,
    COUNT(CASE WHEN verificado THEN 1 END) as verificados,
    COUNT(CASE WHEN destaque AND destaque_ate > NOW() THEN 1 END) as em_destaque,
    COUNT(CASE WHEN created_at > NOW() - INTERVAL '24 hours' THEN 1 END) as novos_24h,
    COUNT(CASE WHEN ultimo_login > NOW() - INTERVAL '7 days' THEN 1 END) as ativos_7dias
FROM acompanhantes;

-- View de estatísticas por cidade
CREATE OR REPLACE VIEW vw_estatisticas_por_cidade AS
SELECT
    c.nome as cidade,
    e.uf as estado,
    COUNT(*) as total,
    COUNT(CASE WHEN a.status = 'aprovado' THEN 1 END) as aprovados,
    COUNT(CASE WHEN a.verificado THEN 1 END) as verificados,
    COUNT(CASE WHEN a.destaque AND a.destaque_ate > NOW() THEN 1 END) as em_destaque
FROM acompanhantes a
LEFT JOIN cidades c ON a.cidade_id = c.id
LEFT JOIN estados e ON a.estado_id = e.id
GROUP BY c.nome, e.uf
ORDER BY total DESC;

-- View de log de atividades
CREATE OR REPLACE VIEW vw_log_atividades AS
SELECT
    l.id,
    l.created_at,
    a.usuario as admin_usuario,
    ac.nome as acompanhante_nome,
    l.acao,
    l.detalhes
FROM admin_log l
JOIN admin a ON a.id = l.admin_id
LEFT JOIN acompanhantes ac ON ac.id = l.acompanhante_id
ORDER BY l.created_at DESC; 