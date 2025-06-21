require('dotenv').config({ path: '.env.local' });
const { createClient } = require('@supabase/supabase-js');

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseServiceKey) {
  console.error("Erro: As variáveis de ambiente do Supabase não estão configuradas.");
  process.exit(1);
}

const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey);

async function cleanupTestUsers() {
  console.log("Iniciando limpeza de usuários de teste...");

  try {
    // 1. Listar todos os usuários
    const { data: { users }, error: listError } = await supabaseAdmin.auth.admin.listUsers();

    if (listError) {
      throw new Error(`Erro ao listar usuários: ${listError.message}`);
    }

    // 2. Filtrar os usuários que são de teste
    const testUsers = users.filter(user => user.email.endsWith('@sigilosas.test'));

    if (testUsers.length === 0) {
      console.log("Nenhum usuário de teste encontrado para limpar. O banco de dados está limpo!");
      return;
    }

    console.log(`Encontrados ${testUsers.length} usuários de teste para deletar:`);
    testUsers.forEach(user => console.log(` - ${user.email} (ID: ${user.id})`));

    // 3. Deletar cada usuário de teste
    for (const user of testUsers) {
      console.log(`Deletando usuário: ${user.email}...`);
      const { error: deleteError } = await supabaseAdmin.auth.admin.deleteUser(user.id);
      if (deleteError) {
        console.warn(`  -> Falha ao deletar usuário ${user.email}: ${deleteError.message}`);
      } else {
        console.log(`  -> Usuário ${user.email} deletado com sucesso.`);
      }
    }

    console.log("\n✅ Limpeza concluída com sucesso!");

  } catch (error) {
    console.error("\n❌ FALHA no processo de limpeza:", error.message);
  }
}

cleanupTestUsers(); 