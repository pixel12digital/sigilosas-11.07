const { createClient } = require('@supabase/supabase-js');
require('dotenv').config({ path: '.env.local' });

const [email, password] = process.argv.slice(2);

if (!email || !password) {
  console.error('❌ Por favor, forneça email e senha como argumentos.');
  console.log('Exemplo: node setup-admin.js admin@example.com suavez123');
  process.exit(1);
}

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseServiceKey) {
  console.error('As variáveis de ambiente do Supabase não estão configuradas.');
  process.exit(1);
}

const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey);

async function createAdminUser(email, password) {
  console.log('🔄 Iniciando configuração do admin...');
  const { data, error } = await supabaseAdmin.auth.admin.createUser({
    email: email,
    password: password,
    email_confirm: true,
  });

  if (error) {
    if (error.message.includes('User already exists')) {
        console.warn(`AVISO: O usuário com o email ${email} já existe na autenticação.`);
        // Tenta obter o usuário existente para continuar o processo
        const { data: { users }, error: listError } = await supabaseAdmin.auth.admin.listUsers();
        if (listError) {
          throw new Error(`Erro ao listar usuários: ${listError.message}`);
        }
        const existingUser = users.find(u => u.email === email);
        if (!existingUser) {
          throw new Error(`Não foi possível encontrar o usuário existente ${email} na lista de usuários.`);
        }
        console.log(`Encontrado usuário existente: ${existingUser.email} com ID: ${existingUser.id}`);
        return existingUser;
    }
    throw error;
  }
  console.log('✅ Usuário admin criado com sucesso na autenticação!');
  return data.user;
}

async function addToAdminsTable(user) {
    const { error } = await supabaseAdmin.from('admins').insert([{ 
        id: user.id
    }]);

    if (error) {
        if (error.code === '23505') { // Código de violação de unicidade
             console.warn(`AVISO: O usuário ${user.email} já existe na tabela 'admins'.`);
        } else {
            throw new Error(`Falha ao inserir o admin na tabela 'admins': ${error.message}`);
        }
    } else {
        console.log(`✅ Usuário ${user.email} inserido na tabela 'admins' com sucesso!`);
    }
}

async function main() {
  try {
    const user = await createAdminUser(email, password);
    if(user){
        await addToAdminsTable(user);
    } else {
        throw new Error("Não foi possível criar ou obter o usuário de autenticação.");
    }
    
    console.log('\n🎉 Configuração de admin concluída!');
    console.log('\nCredenciais de acesso:');
    console.log(`Email: ${email}`);
    console.log(`Senha: ${password}`);

  } catch (error) {
    console.error('\n❌ Erro durante a configuração do admin:', error.message);
  }
}

main(); 