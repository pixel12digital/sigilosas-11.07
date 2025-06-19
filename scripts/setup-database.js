require('dotenv').config({ path: '.env.local' });
const fs = require('fs');
const path = require('path');
const https = require('https');
const { createClient } = require('@supabase/supabase-js');
const bcrypt = require('bcryptjs');

// Configurações do Supabase
const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseServiceKey) {
  console.error('❌ Variáveis de ambiente NEXT_PUBLIC_SUPABASE_URL e SUPABASE_SERVICE_ROLE_KEY são obrigatórias');
  process.exit(1);
}

async function executeSQLFile(sql) {
  return new Promise((resolve, reject) => {
    const options = {
      hostname: new URL(supabaseUrl).hostname,
      port: 443,
      path: '/rest/v1/rpc/exec_sql',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'apikey': supabaseServiceKey,
        'Authorization': `Bearer ${supabaseServiceKey}`
      }
    };

    const req = https.request(options, (res) => {
      let data = '';
      res.on('data', (chunk) => data += chunk);
      res.on('end', () => {
        if (res.statusCode >= 200 && res.statusCode < 300) {
          resolve(data);
        } else {
          reject(new Error(`HTTP Status ${res.statusCode}: ${data}`));
        }
      });
    });

    req.on('error', reject);
    req.write(JSON.stringify({ sql }));
    req.end();
  });
}

async function setupDatabase() {
  try {
    console.log('🔄 Iniciando configuração do banco de dados...');

    // Criar cliente Supabase
    const supabase = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL,
      process.env.SUPABASE_SERVICE_ROLE_KEY
    );

    // Criar tabela admin
    const { error: createTableError } = await supabase.rpc('exec_sql', {
      sql: `
        CREATE TABLE IF NOT EXISTS admin (
          id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
          usuario VARCHAR(50) NOT NULL UNIQUE,
          senha TEXT NOT NULL,
          email VARCHAR(255),
          nome VARCHAR(100),
          ativo BOOLEAN DEFAULT true,
          created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
          updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
        );

        CREATE INDEX IF NOT EXISTS idx_admin_usuario ON admin(usuario);
        CREATE INDEX IF NOT EXISTS idx_admin_email ON admin(email);

        ALTER TABLE admin ENABLE ROW LEVEL SECURITY;

        DROP POLICY IF EXISTS "Administradores podem ver outros administradores" ON admin;
        CREATE POLICY "Administradores podem ver outros administradores" ON admin
          FOR SELECT USING (
            auth.role() = 'authenticated' AND 
            EXISTS (
              SELECT 1 FROM admin a
              WHERE a.usuario = auth.email()
              AND a.ativo = true
            )
          );

        DROP POLICY IF EXISTS "Administradores podem atualizar outros administradores" ON admin;
        CREATE POLICY "Administradores podem atualizar outros administradores" ON admin
          FOR UPDATE USING (
            auth.role() = 'authenticated' AND 
            EXISTS (
              SELECT 1 FROM admin a
              WHERE a.usuario = auth.email()
              AND a.ativo = true
            )
          );
      `
    });

    if (createTableError) {
      console.error('Erro ao criar tabela admin:', createTableError);
      return;
    }

    console.log('✅ Tabela admin criada com sucesso!');

    // Gerar hash da senha
    const salt = await bcrypt.genSalt(10);
    const senhaHash = await bcrypt.hash('admin123', salt);

    // Inserir admin padrão
    const { error: insertError } = await supabase
      .from('admin')
      .upsert({
        usuario: 'admin',
        senha: senhaHash,
        email: 'admin@sigilosas.com.br',
        nome: 'Administrador',
        ativo: true
      }, { onConflict: 'usuario' });

    if (insertError) {
      console.error('Erro ao inserir admin:', insertError);
      return;
    }

    console.log('✅ Admin padrão criado com sucesso!');

    // Criar usuário no Auth
    const { data: authUser, error: authError } = await supabase.auth.admin.createUser({
      email: 'admin@sigilosas.com.br',
      password: 'admin123',
      email_confirm: true,
      user_metadata: {
        role: 'admin',
        username: 'admin'
      }
    });

    if (authError) {
      console.error('Erro ao criar usuário no Auth:', authError);
      return;
    }

    console.log('✅ Usuário Auth criado com sucesso!');
    console.log('🎉 Configuração do banco de dados concluída!');
  } catch (error) {
    console.error('Erro ao configurar banco de dados:', error);
  }
}

// Executar se chamado diretamente
if (require.main === module) {
  setupDatabase();
}

module.exports = { setupDatabase }; 