const { createClient } = require('@supabase/supabase-js');
require('dotenv').config({ path: '.env.local' });

async function setupAdmin() {
  try {
    console.log('🔄 Iniciando configuração do admin...');
    
    if (!process.env.NEXT_PUBLIC_SUPABASE_URL || !process.env.SUPABASE_SERVICE_ROLE_KEY) {
      console.error('❌ Variáveis de ambiente não encontradas!');
      console.log('Certifique-se que o arquivo .env.local existe com:');
      console.log('NEXT_PUBLIC_SUPABASE_URL=https://qfxnenbanimljqjfybva.supabase.co');
      console.log('SUPABASE_SERVICE_ROLE_KEY=sua_chave_aqui');
      return;
    }

    // Criar cliente Supabase
    const supabase = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL,
      process.env.SUPABASE_SERVICE_ROLE_KEY
    );

    // Criar usuário admin no Auth
    const { data: authUser, error: authError } = await supabase.auth.admin.createUser({
      email: 'admin@admin.sigilosas.com.br',
      password: 'admin123',
      email_confirm: true
    });

    if (authError) {
      console.error('Erro ao criar usuário no Auth:', authError);
      return;
    }

    console.log('✅ Usuário admin criado com sucesso!');
    console.log('🎉 Configuração concluída!');
    console.log('\nCredenciais de acesso:');
    console.log('Email: admin@admin.sigilosas.com.br');
    console.log('Senha: admin123');
  } catch (error) {
    console.error('Erro ao configurar admin:', error);
  }
}

setupAdmin(); 