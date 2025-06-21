require('dotenv').config({ path: '.env.local' });
const { createClient } = require('@supabase/supabase-js');

// Configuração do Supabase Admin
const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseServiceKey) {
  console.error("Erro: As variáveis de ambiente do Supabase não estão configuradas.");
  process.exit(1);
}

const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey);

async function testarCadastro() {
  const testEmail = `teste-${Date.now()}@sigilosas.test`;
  const testPassword = 'password123';
  const testNome = 'Usuária de Teste';
  let userId = null;

  console.log(`Iniciando teste de cadastro com o e-mail: ${testEmail}`);

  try {
    // 1. Obter um ID de cidade válido para o teste
    console.log("Buscando uma cidade válida para o teste...");
    const { data: cidade, error: cidadeError } = await supabaseAdmin
      .from('cidades')
      .select('id')
      .limit(1)
      .single(); // .single() para pegar apenas um objeto

    if (cidadeError || !cidade) {
      console.error("Erro ao buscar cidade para o teste:", cidadeError);
      throw new Error("Não foi possível encontrar uma cidade para usar no teste. O banco de dados está populado com cidades?");
    }
    console.log(`Usando cidade com ID: ${cidade.id}`);

    // 2. Tentar criar o usuário em auth.users
    console.log("Tentando criar o usuário no Supabase Auth...");
    const { data: authData, error: authError } = await supabaseAdmin.auth.admin.createUser({
      email: testEmail,
      password: testPassword,
      email_confirm: true,
      user_metadata: {
        nome: testNome,
        idade: 25,
        genero: 'feminino',
        cidade_id: cidade.id,
        descricao: 'Este é um perfil de teste gerado automaticamente.',
      },
    });

    if (authError) {
      console.error("Erro ao tentar criar o usuário no Auth:", authError);
      throw authError; // Lança o erro para o bloco catch
    }

    userId = authData.user.id;
    console.log(`Usuário criado com sucesso no Auth! ID: ${userId}`);

    // 3. Verificar se a trigger criou a entrada em 'acompanhantes'
    console.log("Verificando se o perfil foi criado na tabela 'acompanhantes'...");
    
    // Pequeno delay para dar tempo da trigger executar
    await new Promise(resolve => setTimeout(resolve, 2000)); 

    const { data: acompanhanteData, error: acompanhanteError } = await supabaseAdmin
      .from('acompanhantes')
      .select('*')
      .eq('user_id', userId)
      .single();

    if (acompanhanteError) {
      console.error("Erro ao buscar o perfil na tabela 'acompanhantes':", acompanhanteError.message);
      throw new Error(`O usuário foi criado no Auth, mas a trigger falhou em criar o perfil. Detalhes: ${acompanhanteError.message}`);
    }

    if (acompanhanteData) {
      console.log("\n✅ SUCESSO! O teste foi concluído.");
      console.log("O perfil foi criado corretamente na tabela 'acompanhantes':");
      console.log(acompanhanteData);
    } else {
      throw new Error("O usuário foi criado no Auth, mas o perfil correspondente não foi encontrado em 'acompanhantes'. A trigger pode não ter executado ou falhou silenciosamente.");
    }

  } catch (error) {
    console.error("\n❌ FALHA NO TESTE. Causa do erro:");
    console.error(error.message || error);
    return false; // Retorna falha
  } finally {
    // 4. Limpeza: Deletar o usuário de teste do Auth para não sujar o banco
    if (userId) {
      console.log(`\nIniciando limpeza... Deletando usuário de teste: ${userId}`);
      const { error: deleteError } = await supabaseAdmin.auth.admin.deleteUser(userId);
      if (deleteError) {
        console.error("ERRO na limpeza: Não foi possível deletar o usuário de teste do Auth.", deleteError);
      } else {
        console.log("Limpeza concluída com sucesso.");
      }
    }
  }
  return true; // Retorna sucesso
}

testarCadastro(); 