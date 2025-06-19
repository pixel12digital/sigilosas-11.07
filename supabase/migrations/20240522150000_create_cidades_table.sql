CREATE TABLE public.cidades (
    id uuid NOT NULL DEFAULT gen_random_uuid(),
    nome text NOT NULL,
    estado character(2) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT cidades_pkey PRIMARY KEY (id),
    CONSTRAINT cidades_nome_estado_key UNIQUE (nome, estado)
);

ALTER TABLE public.cidades ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Allow authenticated users to read cities"
ON public.cidades
FOR SELECT
TO authenticated
USING (true);

CREATE POLICY "Allow admin users to insert cities"
ON public.cidades
FOR INSERT
TO authenticated
WITH CHECK (
  (get_my_claim('user_role'::text) = '"admin"'::jsonb)
); 