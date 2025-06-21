const { createClient } = require('@supabase/supabase-js');
require('dotenv').config({ path: '.env.local' });

const email = 'admin@admin.sigilosas.com.br';

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseServiceKey) {
  console.error('As variáveis de ambiente do Supabase não estão configuradas.');
  process.exit(1);
}

const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey);

async function getUserId(email) {
  const { data: { users }, error } = await supabaseAdmin.auth.admin.listUsers();
  if (error) {
    throw new Error(`Erro ao listar usuários: ${error.message}`);
  }
  const user = users.find(u => u.email === email);
  if (!user) {
    throw new Error(`Usuário com email ${email} não encontrado.`);
  }
  return user.id;
}

async function main() {
  try {
    const userId = await getUserId(email);
    console.log(`O ID do usuário é: ${userId}`);
    
    const { error } = await supabaseAdmin.from('admins').insert([{ id: userId }]);
    if (error) {
      if (error.code === '23505') {
        console.log('Usuário já existe na tabela admins.');
      } else {
        throw error;
      }
    } else {
      console.log('Usuário adicionado à tabela admins com sucesso!');
    }
  } catch (error) {
    console.error('Erro:', error.message);
  }
}

main(); 