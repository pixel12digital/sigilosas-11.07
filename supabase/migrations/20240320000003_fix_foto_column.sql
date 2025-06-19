-- Verifica se a coluna foto existe e cria se não existir
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_name = 'acompanhantes'
        AND column_name = 'foto'
    ) THEN
        ALTER TABLE acompanhantes ADD COLUMN foto VARCHAR(255);
    END IF;
END $$; 