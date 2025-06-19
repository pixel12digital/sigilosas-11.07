-- Adicionar políticas para administradores
CREATE POLICY "Administradores podem atualizar acompanhantes" ON acompanhantes
    FOR UPDATE USING (
        EXISTS (
            SELECT 1 FROM usuarios
            WHERE usuarios.id = auth.uid()
            AND usuarios.tipo = 'admin'
        )
    );

CREATE POLICY "Administradores podem excluir acompanhantes" ON acompanhantes
    FOR DELETE USING (
        EXISTS (
            SELECT 1 FROM usuarios
            WHERE usuarios.id = auth.uid()
            AND usuarios.tipo = 'admin'
        )
    );

-- Permitir que administradores vejam todos os registros
CREATE POLICY "Administradores podem ver todos os acompanhantes" ON acompanhantes
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM usuarios
            WHERE usuarios.id = auth.uid()
            AND usuarios.tipo = 'admin'
        )
    );

-- Permitir cadastro inicial
CREATE POLICY "Permitir cadastro inicial de acompanhantes" ON acompanhantes
    FOR INSERT WITH CHECK (true);

-- Permitir que acompanhantes vejam e editem seus próprios dados
CREATE POLICY "Acompanhantes podem ver seus próprios dados" ON acompanhantes
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Acompanhantes podem editar seus próprios dados" ON acompanhantes
    FOR UPDATE USING (auth.uid() = user_id);

-- Políticas para storage buckets
CREATE POLICY "Administradores podem gerenciar arquivos" ON storage.objects
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM auth.users
            JOIN public.usuarios ON usuarios.id = auth.users.id
            WHERE auth.users.id = auth.uid()
            AND usuarios.tipo = 'admin'
        )
    );

-- Permitir upload inicial de arquivos
CREATE POLICY "Permitir upload inicial de arquivos" ON storage.objects
    FOR INSERT WITH CHECK (true);

-- Permitir que acompanhantes gerenciem seus próprios arquivos
CREATE POLICY "Acompanhantes podem gerenciar seus arquivos" ON storage.objects
    FOR ALL USING (
        bucket_id IN ('perfil', 'documentos', 'videos-verificacao', 'galeria') AND
        (EXISTS (
            SELECT 1 FROM acompanhantes
            WHERE acompanhantes.user_id = auth.uid()
        ))
    ); 