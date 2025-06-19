-- Add bloqueado status to acompanhantes table
ALTER TABLE acompanhantes DROP CONSTRAINT acompanhantes_status_check;
ALTER TABLE acompanhantes ADD CONSTRAINT acompanhantes_status_check CHECK (status IN ('pendente', 'aprovado', 'rejeitado', 'bloqueado')); 