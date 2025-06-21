require('dotenv').config({ path: '.env.local' });
const { createClient } = require('@supabase/supabase-js');

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseServiceKey) {
  console.error("Erro: As variáveis de ambiente do Supabase não estão configuradas.");
  process.exit(1);
}

const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey);

async function diagnoseAuthUsers() {
  console.log("Iniciando diagnóstico: Listando todos os usuários e seus telefones na tabela Auth...");

  try {
    const { data: { users }, error: listError } = await supabaseAdmin.auth.admin.listUsers({
      page: 1,
      perPage: 100, // Aumenta o limite para garantir que pegamos todos
    });

    if (listError) {
      throw new Error(`Erro ao listar usuários: ${listError.message}`);
    }

    if (users.length === 0) {
      console.log("\nNenhum usuário encontrado na tabela auth.users. A tabela está limpa.");
      return;
    }

    console.log(`\n--- INÍCIO DA LISTA DE USUÁRIOS (${users.length} encontrados) ---`);
    users.forEach(user => {
      console.log(`
-----------------------------------------
  E-mail:   ${user.email}
  Telefone: ${user.phone || 'Nenhum'}
  ID:       ${user.id}
-----------------------------------------`);
    });
    console.log("--- FIM DA LISTA DE USUÁRIOS ---");
    console.log("\nPor favor, verifique a lista acima. Se encontrar a conta que está bloqueando o número de telefone, podemos deletá-la usando o ID.");


  } catch (error) {
    console.error("\n❌ FALHA no processo de diagnóstico:", error.message);
  }
}

diagnoseAuthUsers(); 