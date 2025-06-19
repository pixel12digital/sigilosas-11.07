require('dotenv').config({ path: '.env.local' });
const { createClient } = require('@supabase/supabase-js');

async function setupCidadePolicies() {
  try {
    console.log('🔄 Configurando políticas de segurança para cidades...');

    const supabase = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL,
      process.env.SUPABASE_SERVICE_ROLE_KEY
    );

    const { error } = await supabase.rpc('exec_sql', {
      sql: `
        -- Permitir que administradores possam inserir registros para cidades
        CREATE POLICY "Administradores podem inserir cidades" ON acompanhantes
          FOR INSERT 
          WITH CHECK (
            auth.role() = 'authenticated' AND 
            EXISTS (
              SELECT 1 FROM admin a
              WHERE a.usuario = auth.email()
              AND a.ativo = true
            )
          );

        -- Permitir que administradores possam ver todas as cidades
        CREATE POLICY "Administradores podem ver cidades" ON acompanhantes
          FOR SELECT 
          USING (
            auth.role() = 'authenticated' AND 
            EXISTS (
              SELECT 1 FROM admin a
              WHERE a.usuario = auth.email()
              AND a.ativo = true
            )
          );
      `
    });

    if (error) {
      console.error('❌ Erro ao configurar políticas:', error);
      return;
    }

    console.log('✅ Políticas de segurança configuradas com sucesso!');
  } catch (error) {
    console.error('❌ Erro ao executar script:', error);
  }
}

// Executar se chamado diretamente
if (require.main === module) {
  setupCidadePolicies();
}

module.exports = { setupCidadePolicies }; 