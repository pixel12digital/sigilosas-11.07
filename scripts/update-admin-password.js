require('dotenv').config({ path: '.env.local' });
const { createClient } = require('@supabase/supabase-js');
const bcrypt = require('bcryptjs');

async function updateAdminPassword() {
  try {
    console.log('🔄 Atualizando senha do admin...');

    if (!process.env.NEXT_PUBLIC_SUPABASE_URL || !process.env.SUPABASE_SERVICE_ROLE_KEY) {
      console.error('❌ Variáveis de ambiente não configuradas!');
      console.log('\nCrie um arquivo .env.local com:');
      console.log('NEXT_PUBLIC_SUPABASE_URL=sua_url_do_supabase');
      console.log('NEXT_PUBLIC_SUPABASE_ANON_KEY=sua_chave_anonima_do_supabase');
      console.log('SUPABASE_SERVICE_ROLE_KEY=sua_chave_de_servico_do_supabase');
      return;
    }

    // Criar cliente Supabase
    const supabase = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL,
      process.env.SUPABASE_SERVICE_ROLE_KEY
    );

    // Gerar hash da senha
    const salt = await bcrypt.genSalt(10);
    const senhaHash = await bcrypt.hash('admin123', salt);

    // Atualizar senha do admin
    const { error: updateError } = await supabase
      .from('admin')
      .update({ senha: senhaHash })
      .eq('usuario', 'admin');

    if (updateError) {
      console.error('❌ Erro ao atualizar senha:', updateError);
      return;
    }

    console.log('✅ Senha atualizada com sucesso!');
    console.log('\nCredenciais de acesso:');
    console.log('Usuário: admin');
    console.log('Senha: admin123');
  } catch (error) {
    console.error('❌ Erro ao atualizar senha:', error);
  }
}

updateAdminPassword(); 