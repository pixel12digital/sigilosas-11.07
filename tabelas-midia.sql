-- Tipos para mídia
CREATE TYPE foto_tipo_enum AS ENUM ('perfil', 'galeria', 'verificacao');
CREATE TYPE documento_tipo_enum AS ENUM ('rg', 'cnh', 'selfie');

-- Tabela de fotos
CREATE TABLE fotos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    tipo foto_tipo_enum NOT NULL,
    ordem SMALLINT,
    principal BOOLEAN DEFAULT false,
    aprovada BOOLEAN DEFAULT false,
    motivo_rejeicao TEXT,
    tamanho BIGINT, -- tamanho em bytes
    formato VARCHAR(10), -- extensão do arquivo
    dimensoes JSONB, -- {width: X, height: Y}
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de documentos
CREATE TABLE documentos_acompanhante (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    tipo documento_tipo_enum NOT NULL,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    verificado BOOLEAN DEFAULT false,
    motivo_rejeicao TEXT,
    tamanho BIGINT,
    formato VARCHAR(10),
    data_verificacao TIMESTAMP WITH TIME ZONE,
    verificado_por INTEGER REFERENCES admin(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de vídeos
CREATE TABLE videos_verificacao (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    acompanhante_id UUID REFERENCES acompanhantes(id) ON DELETE CASCADE,
    url TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    verificado BOOLEAN DEFAULT false,
    motivo_rejeicao TEXT,
    tamanho BIGINT,
    formato VARCHAR(10),
    duracao INTEGER, -- duração em segundos
    thumbnail_url TEXT,
    data_verificacao TIMESTAMP WITH TIME ZONE,
    verificado_por INTEGER REFERENCES admin(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Triggers para atualizar contadores na tabela acompanhantes
CREATE OR REPLACE FUNCTION atualizar_contadores_midia()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_TABLE_NAME = 'fotos' THEN
        UPDATE acompanhantes 
        SET total_fotos = (SELECT COUNT(*) FROM fotos WHERE acompanhante_id = NEW.acompanhante_id)
        WHERE id = NEW.acompanhante_id;
    ELSIF TG_TABLE_NAME = 'videos_verificacao' THEN
        UPDATE acompanhantes 
        SET total_videos = (SELECT COUNT(*) FROM videos_verificacao WHERE acompanhante_id = NEW.acompanhante_id)
        WHERE id = NEW.acompanhante_id;
    ELSIF TG_TABLE_NAME = 'documentos_acompanhante' THEN
        UPDATE acompanhantes 
        SET total_documentos = (SELECT COUNT(*) FROM documentos_acompanhante WHERE acompanhante_id = NEW.acompanhante_id)
        WHERE id = NEW.acompanhante_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Criar triggers para cada tabela
CREATE TRIGGER atualizar_total_fotos
    AFTER INSERT OR DELETE ON fotos
    FOR EACH ROW
    EXECUTE FUNCTION atualizar_contadores_midia();

CREATE TRIGGER atualizar_total_videos
    AFTER INSERT OR DELETE ON videos_verificacao
    FOR EACH ROW
    EXECUTE FUNCTION atualizar_contadores_midia();

CREATE TRIGGER atualizar_total_documentos
    AFTER INSERT OR DELETE ON documentos_acompanhante
    FOR EACH ROW
    EXECUTE FUNCTION atualizar_contadores_midia();

-- Índices para performance
CREATE INDEX idx_fotos_acompanhante ON fotos(acompanhante_id);
CREATE INDEX idx_fotos_tipo ON fotos(tipo);
CREATE INDEX idx_documentos_acompanhante ON documentos_acompanhante(acompanhante_id);
CREATE INDEX idx_documentos_tipo ON documentos_acompanhante(tipo);
CREATE INDEX idx_videos_acompanhante ON videos_verificacao(acompanhante_id);

-- Políticas de segurança RLS
ALTER TABLE fotos ENABLE ROW LEVEL SECURITY;
ALTER TABLE documentos_acompanhante ENABLE ROW LEVEL SECURITY;
ALTER TABLE videos_verificacao ENABLE ROW LEVEL SECURITY;

-- Políticas para fotos
CREATE POLICY "Acompanhantes podem ver suas próprias fotos"
    ON fotos FOR SELECT
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

CREATE POLICY "Acompanhantes podem gerenciar suas próprias fotos"
    ON fotos FOR ALL
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

-- Políticas para documentos
CREATE POLICY "Acompanhantes podem ver seus próprios documentos"
    ON documentos_acompanhante FOR SELECT
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

CREATE POLICY "Acompanhantes podem gerenciar seus próprios documentos"
    ON documentos_acompanhante FOR ALL
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

-- Políticas para vídeos
CREATE POLICY "Acompanhantes podem ver seus próprios vídeos"
    ON videos_verificacao FOR SELECT
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    ));

CREATE POLICY "Acompanhantes podem gerenciar seus próprios vídeos"
    ON videos_verificacao FOR ALL
    USING (acompanhante_id IN (
        SELECT id FROM acompanhantes WHERE user_id = auth.uid()
    )); 